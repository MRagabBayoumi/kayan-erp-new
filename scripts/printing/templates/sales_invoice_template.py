"""
Sales Invoice Thermal Receipt Template (80mm POS).
Renders structured customer sales receipt with item lines, discounts, and payment totals.
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


class SalesInvoiceTemplate(BasePrintTemplate):
    """
    Thermal 80mm receipt template for Sales Invoices (POS).
    """

    @property
    def template_id(self) -> str:
        return "sales_invoice_receipt"

    @property
    def document_type(self) -> str:
        return "sales_invoice"

    @property
    def description(self) -> str:
        return "80mm Thermal Receipt for Sales Invoices & POS."

    @property
    def required_fields(self) -> List[str]:
        return ["invoice_number", "items", "total_amount"]

    def render(self, data: Dict[str, Any]) -> List[Image.Image]:
        self.validate(data)

        width_mm = float(data.get("width_mm") or 80)
        dpi = int(data.get("dpi") or 203)
        width_px = mm_to_px(width_mm, dpi)

        company_name = str(data.get("company_name") or "شركة كيان التجارية")
        company_phone = str(data.get("company_phone") or "")
        invoice_number = str(data.get("invoice_number"))
        customer_name = str(data.get("customer_name") or "عميل نقدي")
        cashier_name = str(data.get("cashier_name") or "كاشير")
        invoice_date = str(data.get("date") or "")
        items = data.get("items") or []
        subtotal = float(data.get("subtotal") or data.get("total_amount") or 0.0)
        discount_amount = float(data.get("discount_amount") or 0.0)
        total_amount = float(data.get("total_amount") or 0.0)
        paid_amount = float(data.get("paid_amount") or total_amount)
        change_amount = float(data.get("change_amount") or 0.0)

        font_header = get_font('arabic_bold', 20)
        font_sub = get_font('arabic_bold', 14)
        font_body = get_font('arabic_regular', 13)
        font_bold = get_font('arabic_bold', 13)
        font_small = get_font('arabic_regular', 11)

        # Estimate receipt height based on lines
        line_count = len(items)
        estimated_height = 250 + (line_count * 34) + 180

        image = Image.new("RGB", (width_px, estimated_height), "white")
        draw = ImageDraw.Draw(image)

        y = 15

        # Header: Company Name
        comp_text = shape_arabic(company_name)
        bbox = draw.textbbox((0, 0), comp_text, font=font_header)
        draw.text(((width_px - (bbox[2] - bbox[0])) // 2, y), comp_text, fill="black", font=font_header)
        y += (bbox[3] - bbox[1]) + 6

        if company_phone:
            phone_text = shape_arabic(f"هاتف: {company_phone}")
            bbox = draw.textbbox((0, 0), phone_text, font=font_small)
            draw.text(((width_px - (bbox[2] - bbox[0])) // 2, y), phone_text, fill="black", font=font_small)
            y += (bbox[3] - bbox[1]) + 6

        # Document Subtitle
        title_text = shape_arabic("فاتورة مبيعات نقدية (POS)")
        bbox = draw.textbbox((0, 0), title_text, font=font_sub)
        draw.text(((width_px - (bbox[2] - bbox[0])) // 2, y), title_text, fill="black", font=font_sub)
        y += (bbox[3] - bbox[1]) + 10

        # Separator Line
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=2)
        y += 8

        # Invoice Metadata
        inv_line = f"رقم الفاتورة: {invoice_number}"
        cust_line = f"العميل: {customer_name}"
        cashier_line = f"الكاشير: {cashier_name}"

        draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(inv_line), font=font_body)[2], y), shape_arabic(inv_line), fill="black", font=font_bold)
        y += 20
        draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(cust_line), font=font_body)[2], y), shape_arabic(cust_line), fill="black", font=font_body)
        y += 20
        draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(cashier_line), font=font_body)[2], y), shape_arabic(cashier_line), fill="black", font=font_body)
        y += 20

        if invoice_date:
            date_line = f"التاريخ: {invoice_date}"
            draw.text((width_px - 20 - draw.textbbox((0, 0), shape_arabic(date_line), font=font_body)[2], y), shape_arabic(date_line), fill="black", font=font_body)
            y += 20

        # Separator Line
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=1)
        y += 8

        # Items Table Header
        header_text = shape_arabic("الصنف                        الكمية   السعر    الإجمالي")
        draw.text((20, y), header_text, fill="black", font=font_bold)
        y += 20
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=1)
        y += 8

        # Items List
        for it in items:
            name = str(it.get("name") or it.get("product_name") or "")[:18]
            qty = it.get("quantity") or 1
            price = float(it.get("unit_price") or it.get("price") or 0.0)
            total = float(it.get("total") or it.get("subtotal") or (float(qty) * price))

            line_str = f"{name:<18} {qty:>4} {price:>6.1f} {total:>8.1f}"
            draw.text((20, y), shape_arabic(line_str), fill="black", font=font_body)
            y += 20

        # Separator Line
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=2)
        y += 10

        # Totals Breakdown
        if discount_amount > 0:
            sub_text = f"المجموع الفرعي: {subtotal:,.2f} ج.م"
            disc_text = f"الخصم: -{discount_amount:,.2f} ج.م"
            draw.text((width_px - 25 - draw.textbbox((0, 0), shape_arabic(sub_text), font=font_body)[2], y), shape_arabic(sub_text), fill="black", font=font_body)
            y += 20
            draw.text((width_px - 25 - draw.textbbox((0, 0), shape_arabic(disc_text), font=font_body)[2], y), shape_arabic(disc_text), fill="black", font=font_body)
            y += 22

        # Final Total
        tot_text = f"الإجمالي النهائي: {total_amount:,.2f} ج.م"
        draw.text((width_px - 25 - draw.textbbox((0, 0), shape_arabic(tot_text), font=font_header)[2], y), shape_arabic(tot_text), fill="black", font=font_header)
        y += 32

        # Paid & Change
        paid_text = f"المدفوع: {paid_amount:,.2f} ج.م"
        draw.text((width_px - 25 - draw.textbbox((0, 0), shape_arabic(paid_text), font=font_bold)[2], y), shape_arabic(paid_text), fill="black", font=font_bold)
        y += 20

        if change_amount > 0:
            change_text = f"الباقي: {change_amount:,.2f} ج.م"
            draw.text((width_px - 25 - draw.textbbox((0, 0), shape_arabic(change_text), font=font_bold)[2], y), shape_arabic(change_text), fill="black", font=font_bold)
            y += 22

        # Thank You Footer
        y += 10
        draw.line([(15, y), (width_px - 15, y)], fill="black", width=1)
        y += 8
        ty_text = shape_arabic("شكراً لزيارتكم ونسعد بخدمتكم دائماً")
        bbox = draw.textbbox((0, 0), ty_text, font=font_small)
        draw.text(((width_px - (bbox[2] - bbox[0])) // 2, y), ty_text, fill="black", font=font_small)
        y += 24

        # Crop to actual used height
        final_height = max(100, y + 20)
        final_image = image.crop((0, 0, width_px, final_height))
        return [final_image]
