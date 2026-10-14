"""
Purchase Invoice Thermal Receipt Template (80mm POS).
Renders structured supplier receipt with item lines, tax, discounts, and totals.
"""

from typing import Any, Dict, List
from PIL import Image, ImageDraw

try:
    from ..common.units import mm_to_px
    from ..common.arabic_helper import shape_arabic
    from ..common.font_helper import get_font
    from .base_template import BasePrintTemplate
except (ImportError, ValueError):
    from common.units import mm_to_px
    from common.arabic_helper import shape_arabic
    from common.font_helper import get_font
    from templates.base_template import BasePrintTemplate


class PurchaseInvoiceTemplate(BasePrintTemplate):
    """
    Thermal 80mm receipt template for Purchase Invoices.
    """

    @property
    def template_id(self) -> str:
        return "purchase_invoice_receipt"

    @property
    def document_type(self) -> str:
        return "purchase_invoice"

    @property
    def description(self) -> str:
        return "80mm Thermal Receipt for Purchase Invoices."

    @property
    def required_fields(self) -> List[str]:
        return ["invoice_number", "supplier_name", "items", "total_amount"]

    def render(self, data: Dict[str, Any]) -> List[Image.Image]:
        self.validate(data)

        width_mm = float(data.get("width_mm") or 80)
        dpi = int(data.get("dpi") or 203)
        width_px = mm_to_px(width_mm, dpi)

        company_name = str(data.get("company_name") or "شركة كيان للتجارة والتوزيع")
        invoice_number = str(data.get("invoice_number"))
        supplier_name = str(data.get("supplier_name"))
        invoice_date = str(data.get("date") or "")
        items = data.get("items") or []
        total_amount = float(data.get("total_amount") or 0.0)

        font_header = get_font('arabic_bold', 20)
        font_sub = get_font('arabic_bold', 14)
        font_body = get_font('arabic_regular', 13)
        font_bold = get_font('arabic_bold', 13)

        # Estimate receipt height based on lines
        line_count = len(items)
        estimated_height = 200 + (line_count * 30) + 120

        image = Image.new("RGB", (width_px, estimated_height), "white")
        draw = ImageDraw.Draw(image)

        y = 15

        # Header: Company Name
        comp_text = shape_arabic(company_name)
        bbox = draw.textbbox((0, 0), comp_text, font=font_header)
        draw.text(((width_px - (bbox[2] - bbox[0])) // 2, y), comp_text, fill="black", font=font_header)
        y += (bbox[3] - bbox[1]) + 8

        # Document Title
        title_text = shape_arabic("فاتورة مشتريات واردة")
        bbox = draw.textbbox((0, 0), title_text, font=font_sub)
        draw.text(((width_px - (bbox[2] - bbox[0])) // 2, y), title_text, fill="black", font=font_sub)
        y += (bbox[3] - bbox[1]) + 12

        # Separator Line
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=2)
        y += 8

        # Invoice Metadata
        inv_line = f"رقم الفاتورة: {invoice_number}"
        supp_line = f"المورد: {supplier_name}"
        draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(inv_line), font=font_body)[2], y), shape_arabic(inv_line), fill="black", font=font_body)
        y += 22
        draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(supp_line), font=font_body)[2], y), shape_arabic(supp_line), fill="black", font=font_body)
        y += 22

        if invoice_date:
            date_line = f"التاريخ: {invoice_date}"
            draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(date_line), font=font_body)[2], y), shape_arabic(date_line), fill="black", font=font_body)
            y += 22

        # Separator Line
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=1)
        y += 8

        # Items Table Header
        header_text = shape_arabic("الصنف                          الكمية     السعر     الإجمالي")
        draw.text((20, y), header_text, fill="black", font=font_bold)
        y += 22
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=1)
        y += 8

        # Items List
        for it in items:
            name = str(it.get("name") or it.get("product_name") or "")[:20]
            qty = it.get("quantity") or 1
            price = float(it.get("unit_price") or it.get("cost_price") or 0.0)
            total = float(it.get("total") or it.get("total_price") or (float(qty) * price))

            line_str = f"{name:<20} {qty:>4} {price:>7.1f} {total:>8.1f}"
            draw.text((20, y), shape_arabic(line_str), fill="black", font=font_body)
            y += 22

        # Separator Line
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=2)
        y += 10

        # Total Line
        tot_text = f"الإجمالي النهائي: {total_amount:,.2f} ج.م"
        draw.text((width_px - 25 - draw.textbbox((0, 0), shape_arabic(tot_text), font=font_header)[2], y), shape_arabic(tot_text), fill="black", font=font_header)
        y += 35

        # Crop to actual used height
        final_height = max(100, y + 20)
        final_image = image.crop((0, 0, width_px, final_height))
        return [final_image]
