/**
 * Kayan ERP — Product Form Interactions (Create & Edit)
 * Barcode generation, live profit margin calculation, warehouse accordion, image preview
 */

export function initProductForm() {
    const form = document.querySelector('[data-product-form]');
    if (!form) return;

    // 1. Barcode Generator
    const genBtn = form.querySelector('[data-action="generate-barcode"]');
    const barcodeInput = form.querySelector('#productBarcode');

    if (genBtn && barcodeInput) {
        genBtn.addEventListener('click', async () => {
            const originalHtml = genBtn.innerHTML;
            genBtn.disabled = true;
            genBtn.textContent = 'جاري التوليد...';

            try {
                const response = await fetch('/products/generate-barcode');
                const data = await response.json();
                if (data && data.barcode) {
                    barcodeInput.value = data.barcode;
                }
            } catch (e) {
                const random = Math.floor(1000000000 + Math.random() * 9000000000);
                barcodeInput.value = '20' + random;
            } finally {
                genBtn.disabled = false;
                genBtn.innerHTML = originalHtml;
            }
        });
    }

    // 2. Profit Margin Live Calculation
    const costInput = form.querySelector('#costPrice');
    const wholesaleInput = form.querySelector('#wholesalePrice');
    const retailInput = form.querySelector('#retailPrice');
    const marginBadge = form.querySelector('#profitMarginBadge');
    const marginPercent = form.querySelector('#profitMarginPercent');

    function calculateProfitMargin() {
        if (!costInput || !wholesaleInput) return;
        const cost = parseFloat(costInput.value) || 0;
        const wholesale = parseFloat(wholesaleInput.value) || 0;

        if (cost > 0 && wholesale >= cost) {
            const profit = wholesale - cost;
            const percent = ((profit / cost) * 100).toFixed(1);
            if (marginBadge) marginBadge.textContent = '+' + profit.toFixed(2) + ' ج.م';
            if (marginPercent) marginPercent.textContent = '(' + percent + '%)';
        } else if (cost > 0 && wholesale < cost && wholesale > 0) {
            const loss = cost - wholesale;
            if (marginBadge) marginBadge.textContent = '-' + loss.toFixed(2) + ' ج.م';
            if (marginPercent) marginPercent.textContent = '(خسارة)';
        } else {
            if (marginBadge) marginBadge.textContent = '+0.00 ج.م';
            if (marginPercent) marginPercent.textContent = '(0%)';
        }
    }

    costInput?.addEventListener('input', calculateProfitMargin);
    wholesaleInput?.addEventListener('input', calculateProfitMargin);
    calculateProfitMargin();

    // 3. Custom Quantities Accordion Toggle
    const toggleBtn = form.querySelector('[data-action="toggle-custom-quantities"]');
    const customSection = form.querySelector('#customQuantitiesSection');
    const initialQtyInput = form.querySelector('#initialStockQty');

    if (toggleBtn && customSection) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = customSection.classList.contains('hidden') || customSection.style.display === 'none';
            if (isHidden) {
                customSection.classList.remove('hidden');
                customSection.style.display = 'block';
                toggleBtn.setAttribute('aria-expanded', 'true');
            } else {
                customSection.classList.add('hidden');
                customSection.style.display = 'none';
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 4. Warehouse custom inputs summing into initial stock
    const warehouseInputs = form.querySelectorAll('.warehouse-qty-input');
    if (warehouseInputs.length > 0 && initialQtyInput) {
        warehouseInputs.forEach(input => {
            input.addEventListener('input', () => {
                let sum = 0;
                warehouseInputs.forEach(inp => {
                    sum += (parseFloat(inp.value) || 0);
                });
                initialQtyInput.value = sum;
            });
        });
    }

    // 5. Live Product Image Preview
    const imgInput = form.querySelector('#productImage');
    const previewContainer = form.querySelector('#imagePreviewContainer');
    const previewImg = form.querySelector('#imagePreview');

    if (imgInput && previewImg) {
        imgInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                previewImg.src = URL.createObjectURL(file);
                if (previewContainer) {
                    previewContainer.classList.remove('hidden');
                }
            }
        });
    }

    // 6. Direct Print Handler via Centralized Python Engine
    const printBtn = document.querySelector('[data-action="direct-print-edit"]');
    if (printBtn) {
        printBtn.addEventListener('click', async () => {
            const productId = printBtn.dataset.productId;
            if (!productId) return;

            const originalHtml = printBtn.innerHTML;
            printBtn.disabled = true;
            printBtn.innerHTML = '<span>جاري الإرسال للطابعة...</span>';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch(`/products/${productId}/direct-print`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity: 1 }),
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    alert(`تم إرسال الملصق بنجاح إلى (${data.printer || 'طابعة الباركود'}).`);
                } else {
                    alert(`تعذر إرسال أمر الطباعة: ${data.message || 'خطأ غير محدد'}`);
                }
            } catch (err) {
                alert('حدث خطأ في الاتصال بالخادم أثناء إرسال أمر الطباعة.');
            } finally {
                printBtn.disabled = false;
                printBtn.innerHTML = originalHtml;
            }
        });
    }

    // 5. Unit of Measure Auto-fill for pieces_per_package
    const unitSelect = form.querySelector('#productUnit');
    const piecesInput = form.querySelector('#piecesPerPackage');
    if (unitSelect && piecesInput) {
        unitSelect.addEventListener('change', () => {
            const selectedOpt = unitSelect.options[unitSelect.selectedIndex];
            const factor = selectedOpt ? selectedOpt.getAttribute('data-factor') : null;
            if (factor && parseInt(factor, 10) > 1) {
                piecesInput.value = factor;
            } else if (factor && parseInt(factor, 10) === 1 && (!piecesInput.value || piecesInput.value === '12' || piecesInput.value === '24' || piecesInput.value === '10')) {
                piecesInput.value = '1';
            }
        });
    }
}

