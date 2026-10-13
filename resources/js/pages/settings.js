/**
 * Kayan ERP — Settings Page Interactions
 * Logo upload live preview, barcode sticker live preview engine, preset buttons
 */

export function initSettingsPage() {
    const page = document.querySelector('[data-page="settings"]');
    if (!page) return;

    // 1. Live Logo Image Preview (Company Tab)
    const fileInput = page.querySelector('#companyLogoInput');
    const previewImg = page.querySelector('#logoPreviewImg');
    const placeholder = page.querySelector('#logoPlaceholder');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                const objectUrl = URL.createObjectURL(file);
                if (previewImg) {
                    previewImg.src = objectUrl;
                    previewImg.classList.remove('hidden');
                    previewImg.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                    placeholder.style.display = 'none';
                }
            }
        });
    }

    // 2. Program Status Radio Card Styling
    const statusInputs = page.querySelectorAll('input[name="program_status"]');
    const activeCard = page.querySelector('#cardStatusActive');
    const maintCard = page.querySelector('#cardStatusMaintenance');

    function updateStatusCards(val) {
        if (val === 'maintenance') {
            activeCard?.classList.remove('selected');
            maintCard?.classList.add('selected');
        } else {
            activeCard?.classList.add('selected');
            maintCard?.classList.remove('selected');
        }
    }

    statusInputs.forEach(input => {
        input.addEventListener('change', () => {
            updateStatusCards(input.value);
        });
    });

    // 3. Live Barcode Sticker Preview Engine (Barcode Tab)
    if (page.querySelector('#barcodeSettingsForm')) {
        const inputWidth = page.querySelector('#label_width_mm') || page.querySelector('#labelWidth');
        const inputHeight = page.querySelector('#label_height_mm') || page.querySelector('#labelHeight');
        const inputBarcodeW = page.querySelector('#barcode_width_px');
        const inputBarcodeH = page.querySelector('#barcode_height_px') || page.querySelector('#barcodeHeight');
        const inputProdFont = page.querySelector('#product_font_size') || page.querySelector('#labelFontSize');
        const inputDetFont = page.querySelector('#details_font_size');

        const cbCompany = page.querySelector('#show_company_name') || page.querySelector('#showCompanyName');
        const companyNameOverride = page.querySelector('#company_name_override');
        const cbProdName = page.querySelector('#show_product_name') || page.querySelector('#showProductName');
        const cbBarcodeBars = page.querySelector('#show_barcode_bars');
        const cbSku = page.querySelector('#show_item_sku') || page.querySelector('#showItemCode');

        const frame = page.querySelector('#liveStickerFrame');
        const badge = page.querySelector('#previewDimensionsBadge');
        const compEl = page.querySelector('#previewCompanyText') || page.querySelector('#stickerPreviewCompany');
        const prodEl = page.querySelector('#previewProductText') || page.querySelector('#stickerPreviewProductName');
        const barsEl = page.querySelector('#previewBarcodeBars') || page.querySelector('#stickerPreviewBarcodeSvg');
        const skuSpan = page.querySelector('#previewSkuSpan') || page.querySelector('#stickerPreviewItemCode');
        const priceSpan = page.querySelector('#previewPriceSpan') || page.querySelector('#stickerPreviewPrice');
        const bottomEl = page.querySelector('#previewBottomDetails');

        function updateLiveSticker() {
            if (!frame) return;

            const widthMm = parseInt(inputWidth?.value || 38, 10);
            const heightMm = parseInt(inputHeight?.value || 25, 10);
            const prodFont = parseInt(inputProdFont?.value || 14, 10);
            const detFont = parseInt(inputDetFont?.value || 8, 10);

            const showCompany = cbCompany?.checked ?? true;
            const companyName = companyNameOverride?.value || 'شركة كيان للتجارة والتوزيع';
            const showProd = cbProdName?.checked ?? true;
            const showBars = cbBarcodeBars?.checked ?? true;
            const showSku = cbSku?.checked ?? true;

            let priceType = 'none';
            const priceChecked = page.querySelector('input[name="print_price_type"]:checked');
            if (priceChecked) priceType = priceChecked.value;

            if (badge) badge.textContent = `${widthMm}×${heightMm} مم`;

            const frameWidthPx = 240;
            const frameHeightPx = Math.round((heightMm / widthMm) * frameWidthPx);
            frame.style.width = frameWidthPx + 'px';
            frame.style.height = Math.max(120, frameHeightPx) + 'px';

            if (compEl) {
                compEl.style.display = showCompany ? 'block' : 'none';
                compEl.textContent = companyName;
                compEl.style.fontSize = Math.max(9, Math.round(detFont * 1.15)) + 'px';
            }

            if (prodEl) {
                prodEl.style.display = showProd ? 'block' : 'none';
                prodEl.style.fontSize = Math.max(10, prodFont) + 'px';
            }

            if (barsEl) {
                barsEl.style.display = showBars ? 'flex' : 'none';
            }

            if (skuSpan) {
                skuSpan.style.display = showSku ? 'inline' : 'none';
            }

            if (priceSpan) {
                if (priceType === 'retail') {
                    priceSpan.textContent = (showSku ? ' | ' : '') + 'السعر: 35.00 ج.م';
                    priceSpan.style.display = 'inline';
                } else if (priceType === 'wholesale') {
                    priceSpan.textContent = (showSku ? ' | ' : '') + 'جملة: 22.00 ج.م';
                    priceSpan.style.display = 'inline';
                } else if (priceType === 'both') {
                    priceSpan.textContent = (showSku ? ' | ' : '') + 'تجزئة 35 | جملة 22';
                    priceSpan.style.display = 'inline';
                } else {
                    priceSpan.textContent = '';
                    priceSpan.style.display = 'none';
                }
            }

            if (bottomEl) {
                bottomEl.style.display = (showSku || priceType !== 'none') ? 'block' : 'none';
                bottomEl.style.fontSize = detFont + 'px';
            }
        }

        const previewFields = [
            inputWidth, inputHeight, inputBarcodeW, inputBarcodeH,
            inputProdFont, inputDetFont, companyNameOverride,
            cbCompany, cbProdName, cbBarcodeBars, cbSku
        ];

        previewFields.forEach(el => {
            el?.addEventListener('input', updateLiveSticker);
            el?.addEventListener('change', updateLiveSticker);
        });

        page.querySelectorAll('input[name="print_price_type"]').forEach(r => {
            r.addEventListener('change', updateLiveSticker);
        });

        // Barcode Presets
        page.querySelectorAll('[data-action="apply-barcode-preset"]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (inputWidth) inputWidth.value = btn.dataset.width || 38;
                if (inputHeight) inputHeight.value = btn.dataset.height || 25;
                if (inputBarcodeW) inputBarcodeW.value = btn.dataset.barW || 260;
                if (inputBarcodeH) inputBarcodeH.value = btn.dataset.barH || 80;
                if (inputProdFont) inputProdFont.value = btn.dataset.prodFont || 14;
                if (inputDetFont) inputDetFont.value = btn.dataset.detFont || 8;
                updateLiveSticker();
            });
        });

        updateLiveSticker();
    }
}
