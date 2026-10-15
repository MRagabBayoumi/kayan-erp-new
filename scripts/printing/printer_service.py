"""
Printer Service for Windows GDI and TSPL RAW Spooler Integration.
Handles printer discovery, hardware protocol detection (TSPL vs GDI),
and document spooling for single and multi-product batch sequences.
Distinguishes spooler submission from physical verification.
"""

import sys
from typing import Any, Dict, List, Optional, Tuple, Union
from PIL import Image

try:
    import win32print
    import win32ui
    import win32con
    import win32gui
    from PIL import ImageWin
    HAS_WIN32 = True
except ImportError:
    HAS_WIN32 = False


class PrinterNotFoundError(Exception):
    """Raised when the specified printer name does not exist on the system."""
    pass


class PrinterSpoolerError(Exception):
    """Raised when Windows Spooler / Driver fails during printing."""
    pass


class PrinterService:
    """
    Manages interaction with Windows printer spooler via RAW TSPL or GDI Device Context.
    Supports atomic multi-product batch printing without blank label insertions.
    """

    TSPL_KEYWORDS = [
        "xprinter",
        "xp-",
        "tsc",
        "gprinter",
        "barcode",
        "label",
        "sticker",
    ]

    @staticmethod
    def get_available_printers() -> List[str]:
        """Enumerate all local and network printers configured on this Windows machine."""
        if not HAS_WIN32:
            return []

        try:
            flags = win32print.PRINTER_ENUM_LOCAL | win32print.PRINTER_ENUM_CONNECTIONS
            printers = win32print.EnumPrinters(flags)
            return [p[2] for p in printers]
        except Exception as e:
            sys.stderr.write(f"Warning: Failed to enumerate printers: {e}\n")
            return []

    @classmethod
    def validate_printer(cls, printer_name: str) -> None:
        """Verify that the requested printer exists in Windows devices."""
        if not HAS_WIN32:
            return

        printers = cls.get_available_printers()
        if not printers:
            return

        if printer_name not in printers:
            available_list = ", ".join(f"'{p}'" for p in printers)
            raise PrinterNotFoundError(
                f"Printer '{printer_name}' was not found on this system. Available printers: [{available_list}]."
            )

    @classmethod
    def is_tspl_compatible(cls, printer_name: str) -> bool:
        """
        Detect whether the printer uses TSPL (TSC) command language.
        Thermal label printers like Xprinter XP-233B accept direct TSPL in RAW mode,
        bypassing Windows GDI page size limitations and driver feed quirks.
        """
        name_lower = printer_name.lower()
        if any(keyword in name_lower for keyword in cls.TSPL_KEYWORDS):
            # XP-80 receipt printers are ESC/POS, not TSPL
            if "xp-80" in name_lower or "pos-80" in name_lower:
                return False
            return True

        if HAS_WIN32:
            try:
                hprinter = win32print.OpenPrinter(printer_name)
                try:
                    info = win32print.GetPrinter(hprinter, 2)
                    driver_lower = str(info.get("pDriverName", "")).lower()
                    if any(keyword in driver_lower for keyword in cls.TSPL_KEYWORDS):
                        if "xp-80" in driver_lower or "pos-80" in driver_lower:
                            return False
                        return True
                finally:
                    win32print.ClosePrinter(hprinter)
            except Exception:
                pass

        return False

    @classmethod
    def build_batch_tspl_payload(
        cls,
        items: List[Dict[str, Any]],
        default_width_mm: float = 38.0,
        default_height_mm: float = 13.0,
        gap_mm: float = 2.0,
        direction: int = 1,
        include_direction: bool = True,
    ) -> Tuple[bytes, List[Dict[str, Any]]]:
        """
        Convert a sequence of product label items into a single, continuous TSPL byte stream.

        Protocol Rules for Multi-Product Sequences:
        1. Media setup (SIZE, GAP, DIRECTION, REFERENCE) is emitted strictly ONCE at job start.
           Re-issuing SIZE or GAP between labels causes printer firmware to recalibrate paper length,
           which feeds an extra blank label.
        2. For each unique product image:
           - CLS clears the printer's RAM buffer.
           - BITMAP loads the monochrome bitmap at (0, 0).
           - PRINT 1,{qty} commands the hardware to print exactly {qty} copies of THIS image.
        3. No page-boundary or driver-reset commands are inserted between products.

        Args:
            include_direction: When True (default / production), emits the DIRECTION command in
                the job header. Set to False ONLY for controlled diagnostic testing to determine
                whether DIRECTION causes a blank-label calibration feed on this firmware.
                Does not affect any other command (CLS, SIZE, GAP, BITMAP, PRINT, REFERENCE).

        Returns:
            Tuple of (tspl_bytes, items_diagnostic_summary)
        """
        if not items:
            raise ValueError("No items provided for TSPL batch payload.")

        # Determine nominal dimensions from first item or defaults
        first_img = items[0]["image"]
        width_px_first, height_px_first = first_img.size
        nominal_width_mm = round((width_px_first / 203.0) * 25.4, 1) or default_width_mm
        nominal_height_mm = round((height_px_first / 203.0) * 25.4, 1) or default_height_mm

        # 1. Job Header (emitted ONCE at the start of the job)
        # DIAGNOSTIC NOTE: include_direction=False omits DIRECTION to test H2 hypothesis.
        direction_line = f"DIRECTION {direction}\r\n" if include_direction else ""
        job_header = (
            f"SIZE {nominal_width_mm:g} mm, {nominal_height_mm:g} mm\r\n"
            f"GAP {gap_mm:g} mm, 0 mm\r\n"
            f"{direction_line}"
            f"REFERENCE 0,0\r\n"
        ).encode("ascii")

        sys.stderr.write(
            f"[TSPL-HEADER] include_direction={include_direction} | "
            f"SIZE {nominal_width_mm:g}x{nominal_height_mm:g} mm | "
            f"GAP {gap_mm:g} mm | "
            f"{'DIRECTION ' + str(direction) if include_direction else 'DIRECTION omitted (diagnostic)'}\n"
        )
        sys.stderr.flush()

        payload = bytearray(job_header)
        items_summary = []

        # 2. Iterate through each product item
        for idx, item in enumerate(items):
            img: Image.Image = item["image"]
            qty = max(1, min(500, int(item.get("copies") or item.get("quantity") or 1)))

            width_px, height_px = img.size
            width_mm = round((width_px / 203.0) * 25.4, 1)
            height_mm = round((height_px / 203.0) * 25.4, 1)
            dpi = int(item.get("dpi") or 203)

            # TSPL BITMAP requires width in bytes (each byte = 8 pixels, MSB first)
            width_bytes = (width_px + 7) // 8
            padded_width_px = width_bytes * 8

            # Ensure pure monochrome 1-bit bitmap padded with white (1s) to byte boundary
            gray_img = img.convert("L")
            bw_img = gray_img.point(lambda p: 255 if p > 128 else 0, mode="1")

            padded_img = Image.new("1", (padded_width_px, height_px), 1)
            padded_img.paste(bw_img, (0, 0))
            raw_bitmap = padded_img.tobytes()

            expected_len = width_bytes * height_px
            if len(raw_bitmap) != expected_len:
                raise ValueError(
                    f"Bitmap byte length mismatch for item {idx + 1}: "
                    f"got {len(raw_bitmap)} bytes, expected {expected_len} ({width_bytes} x {height_px})"
                )

            # TSPL per-product command block: CLS -> BITMAP -> PRINT 1,{qty}
            cmd_header = f"CLS\r\nBITMAP 0,0,{width_bytes},{height_px},0,".encode("ascii")
            cmd_footer = f"\r\nPRINT 1,{qty}\r\n".encode("ascii")

            payload.extend(cmd_header)
            payload.extend(raw_bitmap)
            payload.extend(cmd_footer)

            # Detailed diagnostic metadata (excluding raw binary data)
            item_diag = {
                "item_index": idx + 1,
                "product_id": str(item.get("product_id") or item.get("id") or item.get("sku") or f"item_{idx + 1}"),
                "product_name": str(item.get("product_name") or ""),
                "sku": str(item.get("sku") or ""),
                "requested_quantity": qty,
                "image_width_px": width_px,
                "image_height_px": height_px,
                "image_width_mm": width_mm,
                "image_height_mm": height_mm,
                "printer_dpi": dpi,
                "bitmap_width_bytes": width_bytes,
                "bitmap_payload_length": len(raw_bitmap),
                "configured_label_height_mm": height_mm,
                "configured_gap_mm": gap_mm,
                "tspl_command": f"CLS -> BITMAP 0,0,{width_bytes},{height_px},0,[{len(raw_bitmap)} bytes] -> PRINT 1,{qty}",
            }
            items_summary.append(item_diag)

            # Log to stderr cleanly without binary data
            sys.stderr.write(
                f"[TSPL-BATCH] [{idx + 1}/{len(items)}] ID={item_diag['product_id']} | "
                f"Name='{item_diag['product_name']}' | Qty={qty} | "
                f"Size={width_px}x{height_px}px ({width_mm}x{height_mm}mm, {dpi}DPI) | "
                f"Bitmap={width_bytes}B x {height_px}dots = {len(raw_bitmap)}B | "
                f"Gap={gap_mm}mm | Cmd: {item_diag['tspl_command']}\n"
            )
            sys.stderr.flush()

        return bytes(payload), items_summary

    @classmethod
    def submit_batch_job_raw_tspl(
        cls,
        printer_name: str,
        items: List[Dict[str, Any]],
        doc_name: str = "Kayan ERP TSPL Batch",
        default_width_mm: float = 38.0,
        default_height_mm: float = 13.0,
        gap_mm: float = 2.0,
        direction: int = 1,
        include_direction: bool = True,
        dry_run: bool = False
    ) -> Dict[str, Any]:
        """
        Send a sequence of product labels as a single continuous RAW TSPL job.
        Bypasses Windows GDI page boundaries and driver stock defaults.
        CRITICAL: Does NOT call StartPagePrinter/EndPagePrinter, preventing Windows
        winprint.dll from injecting unwanted Form Feed (0x0C) characters between products.

        Args:
            include_direction: When True (default), DIRECTION is included in the job header.
                Set False only for controlled diagnostic testing (H2 hypothesis).
        """
        payload, items_summary = cls.build_batch_tspl_payload(
            items=items,
            default_width_mm=default_width_mm,
            default_height_mm=default_height_mm,
            gap_mm=gap_mm,
            direction=direction,
            include_direction=include_direction,
        )


        total_labels = sum(item["requested_quantity"] for item in items_summary)

        if dry_run or not HAS_WIN32:
            return {
                "success": True,
                "status": "DRY_RUN_RENDERED",
                "mode": "RAW_TSPL",
                "printer": printer_name,
                "total_products": len(items),
                "total_labels": total_labels,
                "copies": total_labels,
                "payload_bytes": len(payload),
                "items_summary": items_summary,
                "message": (
                    f"Dry-run TSPL batch simulation succeeded for '{printer_name}' "
                    f"({len(items)} products, {total_labels} total labels, {len(payload)} bytes)."
                )
            }

        cls.validate_printer(printer_name)

        hprinter = None
        try:
            hprinter = win32print.OpenPrinter(printer_name)
            # StartDocPrinter in RAW mode
            job_id = win32print.StartDocPrinter(hprinter, 1, (doc_name, None, "RAW"))
            try:
                # Direct WritePrinter without StartPagePrinter / EndPagePrinter
                # to prevent winprint from appending 0x0C Form Feed between labels
                bytes_written = win32print.WritePrinter(hprinter, payload)
                if bytes_written != len(payload):
                    raise PrinterSpoolerError(
                        f"Truncated write to '{printer_name}': wrote {bytes_written} of {len(payload)} bytes."
                    )
            finally:
                win32print.EndDocPrinter(hprinter)

            return {
                "success": True,
                "status": "SPOOLED",
                "mode": "RAW_TSPL",
                "printer": printer_name,
                "total_products": len(items),
                "total_labels": total_labels,
                "copies": total_labels,
                "payload_bytes": len(payload),
                "items_summary": items_summary,
                "message": (
                    f"Successfully sent batch of {len(items)} product(s) ({total_labels} total labels) "
                    f"via native TSPL RAW to '{printer_name}'."
                )
            }
        except Exception as e:
            raise PrinterSpoolerError(
                f"Failed to submit RAW TSPL batch print job to '{printer_name}': {str(e)}"
            ) from e
        finally:
            if hprinter is not None:
                try:
                    win32print.ClosePrinter(hprinter)
                except Exception:
                    pass

    @classmethod
    def submit_batch_job_gdi(
        cls,
        printer_name: str,
        items: List[Dict[str, Any]],
        doc_name: str = "Kayan ERP GDI Batch",
        dry_run: bool = False
    ) -> Dict[str, Any]:
        """
        Submit a sequence of product labels via Windows GDI Device Context.
        Maintains all labels within a single GDI print document.
        """
        total_labels = sum(max(1, int(item.get("copies") or 1)) for item in items)

        if dry_run or not HAS_WIN32:
            return {
                "success": True,
                "status": "DRY_RUN_RENDERED",
                "mode": "GDI",
                "printer": printer_name,
                "total_products": len(items),
                "total_labels": total_labels,
                "copies": total_labels,
                "message": f"Dry-run simulation succeeded for printer '{printer_name}' ({total_labels} pages rendered, not sent to spooler)."
            }

        cls.validate_printer(printer_name)

        dc = None
        try:
            dc = win32ui.CreateDC()
            dc.CreatePrinterDC(printer_name)

            dpi_x = dc.GetDeviceCaps(win32con.LOGPIXELSX) or 203
            dpi_y = dc.GetDeviceCaps(win32con.LOGPIXELSY) or 203
            horz_res = dc.GetDeviceCaps(win32con.HORZRES) or 0
            vert_res = dc.GetDeviceCaps(win32con.VERTRES) or 0

            dc.StartDoc(doc_name)

            for item in items:
                img = item["image"]
                copies = max(1, int(item.get("copies") or 1))
                img_dpi = int(item.get("dpi") or 203)
                w, h = img.size
                dib = ImageWin.Dib(img)

                # Compute scaled target dimensions on device DC
                scale_x = float(dpi_x) / float(img_dpi)
                scale_y = float(dpi_y) / float(img_dpi)
                dst_w = int(round(w * scale_x))
                dst_h = int(round(h * scale_y))

                # If document is an office page (e.g. A4/A5 on 600 DPI printer),
                # clamp proportionally to printable area if needed
                if horz_res > 1000 and dst_w > horz_res:
                    ratio = float(horz_res) / float(dst_w)
                    dst_w = horz_res
                    dst_h = int(round(dst_h * ratio))
                if vert_res > 1000 and dst_h > vert_res:
                    ratio = float(vert_res) / float(dst_h)
                    dst_h = vert_res
                    dst_w = int(round(dst_w * ratio))

                for _ in range(copies):
                    dc.StartPage()
                    dib.draw(dc.GetHandleOutput(), (0, 0, dst_w, dst_h))
                    dc.EndPage()

            dc.EndDoc()

            return {
                "success": True,
                "status": "SPOOLED",
                "mode": "GDI",
                "printer": printer_name,
                "total_products": len(items),
                "total_labels": total_labels,
                "copies": total_labels,
                "message": f"Successfully spooled {total_labels} page(s) via GDI to '{printer_name}'."
            }
        except Exception as e:
            raise PrinterSpoolerError(
                f"Failed to submit GDI batch print job to printer '{printer_name}': {str(e)}"
            ) from e
        finally:
            if dc is not None:
                try:
                    dc.DeleteDC()
                except Exception:
                    pass

    @classmethod
    def submit_batch_job(
        cls,
        printer_name: str,
        items: List[Dict[str, Any]],
        doc_name: str = "Kayan ERP Print Job",
        gap_mm: float = 2.0,
        direction: int = 0,
        include_direction: bool = True,
        print_mode: str = "auto",
        dry_run: bool = False
    ) -> Dict[str, Any]:
        """
        Submit a batch sequence of product labels to the Windows print spooler.
        Supports print_mode: 'auto' (default), 'raw_tspl', or 'gdi'.
        """
        if not items:
            raise ValueError("No items provided for printing.")

        mode_req = str(print_mode or "auto").lower().strip()

        if mode_req == "raw_tspl" or (mode_req == "auto" and cls.is_tspl_compatible(printer_name)):
            try:
                return cls.submit_batch_job_raw_tspl(
                    printer_name=printer_name,
                    items=items,
                    doc_name=doc_name,
                    gap_mm=gap_mm,
                    direction=direction,
                    include_direction=include_direction,
                    dry_run=dry_run,
                )
            except Exception as e:
                if mode_req == "raw_tspl":
                    raise
                sys.stderr.write(
                    f"Warning: TSPL RAW batch mode failed for '{printer_name}', falling back to GDI: {e}\n"
                )

        return cls.submit_batch_job_gdi(
            printer_name=printer_name,
            items=items,
            doc_name=doc_name,
            dry_run=dry_run
        )


    @classmethod
    def submit_job(
        cls,
        printer_name: str,
        images: List[Image.Image],
        doc_name: str = "Kayan ERP Print Job",
        copies: int = 1,
        gap_mm: float = 2.0,
        direction: int = 1,
        dry_run: bool = False
    ) -> Dict[str, Any]:
        """
        Backward-compatible submission for single-item or uniform image lists.
        Wraps images into standardized batch items and routes to submit_batch_job.
        """
        if not images:
            raise ValueError("No images provided for printing.")

        copies = max(1, min(500, int(copies)))

        # Wrap images as batch items
        # If multiple images are passed with a single copy count, each image gets `copies`
        batch_items = [
            {
                "image": img,
                "copies": copies,
                "product_id": f"page_{idx + 1}",
                "product_name": f"Label Page {idx + 1}",
            }
            for idx, img in enumerate(images)
        ]

        result = cls.submit_batch_job(
            printer_name=printer_name,
            items=batch_items,
            doc_name=doc_name,
            gap_mm=gap_mm,
            direction=direction,
            dry_run=dry_run
        )

        # Retain backward-compatible top-level keys
        result["pages"] = len(images)
        result["copies"] = copies
        result["spooled_total"] = len(images) * copies
        return result
