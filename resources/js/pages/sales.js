/**
 * Kayan ERP — High-Speed Sales & Point of Sale (POS) Client
 * Ergonomic Barcode Scanning, Instant Item Merging, Steppers, and Dynamic Tier Pricing.
 */

export function initSales() {
    const createPage = document.querySelector('[data-page="sales-create"]');
    if (createPage) {
        initSalesPOS();
    }

    const showPage = document.querySelector('[data-page="sales-show"]');
    if (showPage) {
        initSalesShow();
    }
}

function initSalesPOS() {
    const createPage = document.querySelector('[data-page="sales-create"]');
    if (!createPage) return;

    // Elements
    const salesForm = document.getElementById('salesForm');
    const tableBody = document.getElementById('salesItemsBody');
    const manualAddBtn = document.getElementById('addSalesItemBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const posSubmitBtn = document.getElementById('posSubmitBtn');

    // Meta Elements
    const customerSelect = document.getElementById('customerSelect');
    const customerTierHint = document.getElementById('customerTierHint');
    const warehouseSelect = document.getElementById('warehouseSelect');
    const priceTierSelect = document.getElementById('priceTierSelect');
    const shippingInput = document.getElementById('shippingCost');

    // Quick Entry Bar
    const searchModeSelect = document.getElementById('searchModeSelect');
    const fastScanToggle = document.getElementById('fastScanToggle');
    const fastScanBadge = document.getElementById('fastScanBadge');
    const quickProductInput = document.getElementById('quickProductInput');
    const quickSearchInputLabel = document.getElementById('quickSearchInputLabel');
    const quickSearchResults = document.getElementById('quickSearchResults');
    const quickProductId = document.getElementById('quickProductId');
    const quickStockBadge = document.getElementById('quickStockBadge');
    const quickUnitSelect = document.getElementById('quickUnitSelect');
    const quickQtyInput = document.getElementById('quickQtyInput');
    const quickQtyMinus = document.getElementById('quickQtyMinus');
    const quickQtyPlus = document.getElementById('quickQtyPlus');
    const quickPriceInput = document.getElementById('quickPriceInput');
    const quickLineDiscountInput = document.getElementById('quickLineDiscountInput');
    const quickAddBtn = document.getElementById('quickAddBtn');

    // Discount Elements
    const discountTypeSelect = document.getElementById('invoiceDiscountType');
    const discountValueInput = document.getElementById('invoiceDiscountValue');
    const discountUnitLabel = document.getElementById('discountUnitLabel');

    // Summary Elements
    const summaryItemsCount = document.getElementById('summaryItemsCount');
    const summaryTotalUnits = document.getElementById('summaryTotalUnits');
    const summaryItemsSubtotal = document.getElementById('summaryItemsSubtotal');
    const summaryDiscountDisplay = document.getElementById('summaryDiscountDisplay');
    const summaryDiscountHint = document.getElementById('summaryDiscountHint');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');

    // Parse Data attributes
    let productsData = [];
    let unitsData = [];
    let customersData = [];
    const canEditPrice = createPage.dataset.canEditPrice === 'true';

    try {
        productsData = JSON.parse(createPage.dataset.products || '[]');
    } catch (e) {
        productsData = [];
    }

    try {
        unitsData = JSON.parse(createPage.dataset.units || '[]');
    } catch (e) {
        unitsData = [];
    }

    try {
        customersData = JSON.parse(createPage.dataset.customers || '[]');
    } catch (e) {
        customersData = [];
    }

    let activeSuggestionIndex = -1;
    let isSubmitting = false;

    // Helper: Get product price based on selected tier
    function getProductPriceForTier(product, tier = null) {
        if (!product) return 0.00;
        const currentTier = tier || (priceTierSelect ? priceTierSelect.value : 'retail');
        if (currentTier === 'distributor') {
            return parseFloat(product.distributor_price ?? product.wholesale_price ?? product.retail_price ?? 0);
        }
        if (currentTier === 'wholesale') {
            return parseFloat(product.wholesale_price ?? product.retail_price ?? 0);
        }
        return parseFloat(product.retail_price ?? 0);
    }

    // Helper: Get available warehouse stock
    function getProductWarehouseStock(product, warehouseId = null) {
        if (!product) return 0;
        const whId = warehouseId || (warehouseSelect ? parseInt(warehouseSelect.value, 10) : null);
        if (whId && product.stocks && product.stocks[whId] !== undefined) {
            return parseFloat(product.stocks[whId]) || 0;
        }
        return parseFloat(product.total_stock) || 0;
    }

    // Update Quick Stock Badge
    function updateStockBadge(product) {
        if (!quickStockBadge) return;
        if (!product) {
            quickStockBadge.classList.add('hidden');
            return;
        }

        const stock = getProductWarehouseStock(product);
        const unitName = product.unit || 'قطعة';

        quickStockBadge.classList.remove('hidden', 'in-stock', 'low-stock', 'out-of-stock');

        if (stock <= 0) {
            quickStockBadge.classList.add('out-of-stock');
            quickStockBadge.textContent = `نفد من المخزن (0 ${unitName})`;
        } else if (stock <= 5) {
            quickStockBadge.classList.add('low-stock');
            quickStockBadge.textContent = `رصيد منخفض: ${stock} ${unitName}`;
        } else {
            quickStockBadge.classList.add('in-stock');
            quickStockBadge.textContent = `رصيد متاح: ${stock} ${unitName}`;
        }
    }

    // Customer Change Handler: Auto-sets price tier
    if (customerSelect) {
        customerSelect.addEventListener('change', () => {
            const custId = parseInt(customerSelect.value, 10);
            const customer = customersData.find(c => c.id === custId);
            if (customer && priceTierSelect) {
                const tier = customer.pricing_tier || 'retail';
                priceTierSelect.value = tier;
                if (customerTierHint) {
                    const label = tier === 'distributor' ? 'فئة: موزع' : (tier === 'wholesale' ? 'فئة: جملة' : 'فئة: تجزئة');
                    customerTierHint.textContent = label;
                }
                // Refresh price of selected product if any
                const pId = quickProductId ? parseInt(quickProductId.value, 10) : null;
                if (pId) {
                    const prod = productsData.find(p => p.id === pId);
                    if (prod && quickPriceInput) {
                        quickPriceInput.value = getProductPriceForTier(prod, tier).toFixed(2);
                    }
                }
            }
        });
    }

    // Warehouse Change Handler: Updates stock badge
    if (warehouseSelect) {
        warehouseSelect.addEventListener('change', () => {
            const pId = quickProductId ? parseInt(quickProductId.value, 10) : null;
            if (pId) {
                const prod = productsData.find(p => p.id === pId);
                if (prod) updateStockBadge(prod);
            }
        });
    }

    // Fast-Scan Mode Toggle
    if (fastScanToggle && fastScanBadge) {
        fastScanToggle.addEventListener('change', () => {
            fastScanBadge.classList.toggle('active', fastScanToggle.checked);
            const label = fastScanBadge.querySelector('span');
            if (label) {
                label.textContent = fastScanToggle.checked ? 'المسح المباشر السريع (مفعل)' : 'المسح المباشر السريع (معطل)';
            }
            if (quickProductInput) quickProductInput.focus();
        });
    }

    // Steppers in Quick Bar
    if (quickQtyMinus && quickQtyInput) {
        quickQtyMinus.addEventListener('click', (e) => {
            e.preventDefault();
            let v = parseFloat(quickQtyInput.value) || 1;
            if (v > 1) {
                quickQtyInput.value = (v - 1).toFixed(0);
            }
        });
    }
    if (quickQtyPlus && quickQtyInput) {
        quickQtyPlus.addEventListener('click', (e) => {
            e.preventDefault();
            let v = parseFloat(quickQtyInput.value) || 1;
            quickQtyInput.value = (v + 1).toFixed(0);
        });
    }

    // Tier Change Handler
    if (priceTierSelect) {
        priceTierSelect.addEventListener('change', () => {
            const pId = quickProductId ? parseInt(quickProductId.value, 10) : null;
            if (pId) {
                const prod = productsData.find(p => p.id === pId);
                if (prod && quickPriceInput) {
                    quickPriceInput.value = getProductPriceForTier(prod).toFixed(2);
                }
            }
        });
    }

    // Search Mode Change Handler
    if (searchModeSelect) {
        searchModeSelect.addEventListener('change', () => {
            const mode = searchModeSelect.value;
            if (mode === 'barcode') {
                if (quickSearchInputLabel) quickSearchInputLabel.textContent = 'مربع الباركود (مسدس الباركود)';
                if (quickProductInput) quickProductInput.placeholder = 'اضرب الباركود بمسدس الباركود (F2)...';
            } else if (mode === 'sku') {
                if (quickSearchInputLabel) quickSearchInputLabel.textContent = 'كود الصنف (SKU)';
                if (quickProductInput) quickProductInput.placeholder = 'اكتب كود الصنف واضغط Enter...';
            } else {
                if (quickSearchInputLabel) quickSearchInputLabel.textContent = 'اسم الصنف';
                if (quickProductInput) quickProductInput.placeholder = 'اكتب اسم الصنف للبحث...';
            }
            if (quickProductInput) quickProductInput.focus();
        });
    }

    // Discounts & Shipping handlers
    function updateDiscountLabel() {
        if (!discountTypeSelect || !discountUnitLabel) return;
        discountUnitLabel.textContent = discountTypeSelect.value === 'percentage' ? '%' : 'ج.م';
        recalculateTotals();
    }

    if (discountTypeSelect) discountTypeSelect.addEventListener('change', updateDiscountLabel);
    if (discountValueInput) discountValueInput.addEventListener('input', recalculateTotals);
    if (shippingInput) shippingInput.addEventListener('input', recalculateTotals);

    // Product Lookup logic
    function findProductByTerm(term, mode = 'barcode') {
        const clean = (term || '').trim().toLowerCase();
        if (!clean) return null;

        if (mode === 'barcode') {
            const exactBarcode = productsData.find(p => (p.barcode || '').toLowerCase() === clean);
            if (exactBarcode) return exactBarcode;
            const exactSku = productsData.find(p => (p.sku || '').toLowerCase() === clean);
            if (exactSku) return exactSku;
        } else if (mode === 'sku') {
            const exactSku = productsData.find(p => (p.sku || '').toLowerCase() === clean);
            if (exactSku) return exactSku;
        }

        return productsData.find(p => 
            (p.barcode || '').toLowerCase() === clean ||
            (p.sku || '').toLowerCase() === clean ||
            (p.name || '').toLowerCase().includes(clean)
        ) || null;
    }

    function renderSuggestions(query) {
        if (!quickSearchResults) return;
        const q = (query || '').trim().toLowerCase();
        if (!q || q.length < 1) {
            quickSearchResults.classList.add('hidden');
            quickSearchResults.innerHTML = '';
            activeSuggestionIndex = -1;
            return;
        }

        const mode = searchModeSelect ? searchModeSelect.value : 'barcode';
        const matches = productsData.filter(p => {
            if (mode === 'barcode') {
                return (p.barcode || '').toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q);
            } else if (mode === 'sku') {
                return (p.sku || '').toLowerCase().includes(q);
            }
            return (p.name || '').toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q);
        }).slice(0, 8);

        if (matches.length === 0) {
            quickSearchResults.innerHTML = `
                <div class="px-3 py-2 text-xs text-[var(--text-muted)] text-center">
                    لا يوجد صنف مطابق لـ "${query}"
                </div>
            `;
            quickSearchResults.classList.remove('hidden');
            activeSuggestionIndex = -1;
            return;
        }

        quickSearchResults.innerHTML = matches.map((p, idx) => {
            const price = getProductPriceForTier(p);
            const stock = getProductWarehouseStock(p);
            return `
                <div class="suggestion-item px-3 py-2 cursor-pointer hover:bg-[var(--surface-muted)] flex items-center justify-between text-xs border-b border-[var(--border-color)] last:border-b-0" data-index="${idx}" data-id="${p.id}">
                    <div class="flex flex-col">
                        <span class="font-bold text-[var(--text-main)]">${p.name}</span>
                        <div class="flex items-center gap-2 text-[10px] text-[var(--text-muted)] font-mono">
                            <span>كود: ${p.sku}</span>
                            ${p.barcode ? '<span>• باركود: ' + p.barcode + '</span>' : ''}
                            <span class="${stock <= 0 ? 'text-red-600 font-bold' : 'text-emerald-700'}">• رصيد: ${stock} ${p.unit}</span>
                        </div>
                    </div>
                    <div class="text-left font-numeric">
                        <span class="font-bold text-[var(--primary)] text-sm">${price.toFixed(2)}</span>
                        <span class="text-[10px] text-[var(--text-muted)]">ج.م / ${p.unit || 'قطعة'}</span>
                    </div>
                </div>
            `;
        }).join('');

        quickSearchResults.querySelectorAll('.suggestion-item').forEach(el => {
            el.addEventListener('mousedown', (e) => {
                e.preventDefault();
                const pId = parseInt(el.dataset.id, 10);
                const found = productsData.find(item => item.id === pId);
                if (found) {
                    selectProduct(found, false);
                }
            });
        });

        quickSearchResults.classList.remove('hidden');
        activeSuggestionIndex = -1;
    }

    function selectProduct(product, autoCommitIfFast = false) {
        if (!product) return;

        if (quickProductId) quickProductId.value = product.id;
        if (quickProductInput) quickProductInput.value = `${product.name} (${product.sku})`;

        const tierPrice = getProductPriceForTier(product);
        if (quickPriceInput) quickPriceInput.value = tierPrice.toFixed(2);
        updateStockBadge(product);

        // Select matching unit
        if (quickUnitSelect) {
            const productUnitName = (product.unit || 'قطعة').trim();
            let matchedOption = false;
            for (let i = 0; i < quickUnitSelect.options.length; i++) {
                const opt = quickUnitSelect.options[i];
                if (opt.dataset.name === productUnitName || opt.text.includes(productUnitName)) {
                    quickUnitSelect.selectedIndex = i;
                    matchedOption = true;
                    break;
                }
            }
            if (!matchedOption && quickUnitSelect.options.length > 0) {
                quickUnitSelect.selectedIndex = 0;
            }
        }

        // Hide suggestions
        if (quickSearchResults) {
            quickSearchResults.classList.add('hidden');
            quickSearchResults.innerHTML = '';
        }

        // If Fast-Scan Mode is enabled: IMMEDIATELY ADD/INCREMENT TO TABLE!
        if (autoCommitIfFast && fastScanToggle && fastScanToggle.checked) {
            commitQuickAdd();
            return;
        }

        // Otherwise follow POS keyboard flow:
        if (quickQtyInput) {
            quickQtyInput.focus();
            quickQtyInput.select();
        }
    }

    // Search Input Keydown & Input
    if (quickProductInput) {
        quickProductInput.addEventListener('input', (e) => {
            const term = e.target.value.trim();
            if (quickProductId && quickProductId.value) {
                quickProductId.value = '';
                if (quickStockBadge) quickStockBadge.classList.add('hidden');
            }
            renderSuggestions(term);
        });

        quickProductInput.addEventListener('keydown', (e) => {
            const mode = searchModeSelect ? searchModeSelect.value : 'barcode';

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const items = quickSearchResults.querySelectorAll('.suggestion-item');
                if (items.length > 0) {
                    activeSuggestionIndex = (activeSuggestionIndex + 1) % items.length;
                    items.forEach((it, i) => it.classList.toggle('bg-[var(--surface-muted)]', i === activeSuggestionIndex));
                }
                return;
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                const items = quickSearchResults.querySelectorAll('.suggestion-item');
                if (items.length > 0) {
                    activeSuggestionIndex = (activeSuggestionIndex - 1 + items.length) % items.length;
                    items.forEach((it, i) => it.classList.toggle('bg-[var(--surface-muted)]', i === activeSuggestionIndex));
                }
                return;
            }

            if (e.key === 'Enter') {
                e.preventDefault(); // Stop main form submission!

                // 1. If an item in dropdown is actively selected with arrow keys
                const items = quickSearchResults.querySelectorAll('.suggestion-item');
                if (items.length > 0 && activeSuggestionIndex >= 0 && items[activeSuggestionIndex]) {
                    const pId = parseInt(items[activeSuggestionIndex].dataset.id, 10);
                    const found = productsData.find(item => item.id === pId);
                    if (found) {
                        selectProduct(found, true);
                        return;
                    }
                }

                // 2. Barcode scan match
                const term = quickProductInput.value.trim();
                const matched = findProductByTerm(term, mode);
                if (matched) {
                    selectProduct(matched, true);
                } else if (items.length > 0) {
                    const firstId = parseInt(items[0].dataset.id, 10);
                    const found = productsData.find(item => item.id === firstId);
                    if (found) selectProduct(found, true);
                } else {
                    alert('لم يتم العثور على صنف مطابق لهذا الباركود أو الكود.');
                    quickProductInput.select();
                }
            }

            if (e.key === 'Escape') {
                if (quickSearchResults) quickSearchResults.classList.add('hidden');
            }
        });

        // Hide suggestions on outside click
        document.addEventListener('click', (e) => {
            if (quickSearchResults && !quickSearchResults.contains(e.target) && e.target !== quickProductInput) {
                quickSearchResults.classList.add('hidden');
            }
        });
    }

    // Keyboard Flow: Unit -> Tab -> Qty
    if (quickUnitSelect) {
        quickUnitSelect.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                if (quickQtyInput) {
                    quickQtyInput.focus();
                    quickQtyInput.select();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                commitQuickAdd();
            }
        });
    }

    // Keyboard Flow: Quantity -> Tab -> (Price if editable, else Discount)
    if (quickQtyInput) {
        quickQtyInput.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                if (canEditPrice && quickPriceInput) {
                    quickPriceInput.focus();
                    quickPriceInput.select();
                } else if (quickLineDiscountInput) {
                    quickLineDiscountInput.focus();
                    quickLineDiscountInput.select();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                commitQuickAdd();
            }
        });
    }

    // Keyboard Flow: Price -> Tab -> Line Discount
    if (quickPriceInput) {
        quickPriceInput.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                if (quickLineDiscountInput) {
                    quickLineDiscountInput.focus();
                    quickLineDiscountInput.select();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                commitQuickAdd();
            }
        });
    }

    // Keyboard Flow: Line Discount -> Enter -> Commit & Refocus
    if (quickLineDiscountInput) {
        quickLineDiscountInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                commitQuickAdd();
            }
        });
    }

    if (quickAddBtn) {
        quickAddBtn.addEventListener('click', (e) => {
            e.preventDefault();
            commitQuickAdd();
        });
    }

    // COMMIT FUNCTION: Adds item or increments existing row
    function commitQuickAdd() {
        let pId = quickProductId ? parseInt(quickProductId.value, 10) : null;
        let product = productsData.find(p => p.id === pId);

        if (!product) {
            const term = (quickProductInput ? quickProductInput.value : '').trim();
            product = findProductByTerm(term, searchModeSelect ? searchModeSelect.value : 'barcode');
        }

        if (!product) {
            alert('يرجى اختيار صنف صحيح من القائمة أو ضرب باركود مسجل أولاً.');
            if (quickProductInput) quickProductInput.focus();
            return;
        }

        const qty = parseFloat(quickQtyInput ? quickQtyInput.value : 1) || 1;
        if (qty <= 0) {
            alert('الكمية يجب أن تكون أكبر من الصفر.');
            if (quickQtyInput) quickQtyInput.focus();
            return;
        }

        const price = parseFloat(quickPriceInput ? quickPriceInput.value : 0) || 0;
        if (price < 0) {
            alert('سعر البيع لا يمكن أن يكون سالباً.');
            if (quickPriceInput) quickPriceInput.focus();
            return;
        }

        const lineDisc = parseFloat(quickLineDiscountInput ? quickLineDiscountInput.value : 0) || 0;
        if (lineDisc < 0) {
            alert('خصم البند لا يمكن أن يكون سالباً.');
            if (quickLineDiscountInput) quickLineDiscountInput.focus();
            return;
        }

        const selectedUnitId = quickUnitSelect ? parseInt(quickUnitSelect.value, 10) : null;
        const selectedUnit = unitsData.find(u => u.id === selectedUnitId);
        const conversionFactor = selectedUnit ? (parseFloat(selectedUnit.factor) || 1.0) : 1.0;

        // CHECK DUPLICATE MERGING IN EXISTING TABLE
        // If an existing row has identical product_id, unit_id, unit_price, and line_discount:
        // INCREMENT QUANTITY!
        const existingRow = findMatchingRow(product.id, selectedUnitId, price, lineDisc);

        if (existingRow) {
            const qtyInput = existingRow.querySelector('.row-qty');
            if (qtyInput) {
                const currentQty = parseFloat(qtyInput.value) || 0;
                const newQty = currentQty + qty;
                qtyInput.value = (newQty % 1 === 0 ? newQty.toFixed(0) : newQty.toFixed(2));

                // Trigger row subtotal update
                const subtotalText = existingRow.querySelector('.row-subtotal-text');
                const lineSub = Math.max(0, (newQty * price) - lineDisc);
                if (subtotalText) subtotalText.textContent = lineSub.toFixed(2);

                // Flash animation feedback
                existingRow.classList.remove('item-updated');
                void existingRow.offsetWidth; // trigger reflow
                existingRow.classList.add('item-updated');
            }
        } else {
            // Insert New Row at TOP
            insertRowAtTop({
                productId: product.id,
                productName: product.name,
                productSku: product.sku,
                productBarcode: product.barcode,
                unitId: selectedUnitId,
                conversionFactor: conversionFactor,
                quantity: qty,
                unitPrice: price,
                lineDiscount: lineDisc,
                notes: ''
            });
        }

        // Reset quick input state
        if (quickProductInput) quickProductInput.value = '';
        if (quickProductId) quickProductId.value = '';
        if (quickStockBadge) quickStockBadge.classList.add('hidden');
        if (quickQtyInput) quickQtyInput.value = '1';
        if (quickPriceInput) quickPriceInput.value = '0.00';
        if (quickLineDiscountInput) quickLineDiscountInput.value = '0.00';
        if (quickSearchResults) quickSearchResults.classList.add('hidden');

        // ALWAYS RETURN FOCUS TO SEARCH INPUT FOR NEXT ITEM
        if (quickProductInput) {
            quickProductInput.focus();
        }

        recalculateTotals();
    }

    // Match existing row for merging
    function findMatchingRow(productId, unitId, price, lineDisc) {
        if (!tableBody) return null;
        const rows = tableBody.querySelectorAll('.sales-item-row');
        for (const row of rows) {
            const rowPid = parseInt(row.getAttribute('data-product-id') || row.querySelector('.row-product-id')?.value, 10);
            const rowUnitId = parseInt(row.querySelector('.row-unit-select')?.value, 10);
            const rowPrice = parseFloat(row.querySelector('.row-price')?.value || 0);
            const rowDisc = parseFloat(row.querySelector('.row-discount')?.value || 0);

            if (rowPid === productId && rowUnitId === unitId && Math.abs(rowPrice - price) < 0.001 && Math.abs(rowDisc - lineDisc) < 0.001) {
                return row;
            }
        }
        return null;
    }

    function insertRowAtTop(data) {
        if (!tableBody) return;

        const rowIdx = Date.now() + Math.floor(Math.random() * 1000);
        const tr = document.createElement('tr');
        tr.className = 'sales-item-row item-updated';
        tr.dataset.rowIdx = rowIdx;
        tr.dataset.productId = data.productId;

        // Build Unit Options HTML
        let unitOptionsHtml = '';
        if (unitsData.length > 0) {
            unitsData.forEach(u => {
                const isSel = (u.id === data.unitId) ? 'selected' : '';
                unitOptionsHtml += `<option value="${u.id}" data-factor="${u.factor}" ${isSel}>${u.name}</option>`;
            });
        } else {
            unitOptionsHtml = `<option value="1" data-factor="1.0" selected>قطعة</option>`;
        }

        const lineSubtotal = Math.max(0, (data.quantity * data.unitPrice) - data.lineDiscount);
        const priceLockAttrs = canEditPrice ? '' : 'readonly tabindex="-1"';
        const priceLockClass = canEditPrice ? '' : 'bg-[var(--surface-muted)] cursor-not-allowed';

        tr.innerHTML = `
            <td class="text-center font-numeric row-counter">1</td>
            <td>
                <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.productId}" class="row-product-id">
                <span class="font-bold text-[var(--text-main)]">${data.productName}</span>
                <div class="text-[11px] text-[var(--text-muted)] font-numeric">كود: ${data.productSku || '—'} ${data.productBarcode ? '· باركود: ' + data.productBarcode : ''}</div>
            </td>
            <td>
                <select name="items[${rowIdx}][unit_id]" class="erp-input text-xs row-unit-select">
                    ${unitOptionsHtml}
                </select>
                <input type="hidden" name="items[${rowIdx}][conversion_factor]" value="${data.conversionFactor}" class="row-conversion-factor">
            </td>
            <td>
                <div class="pos-qty-stepper mx-auto">
                    <button type="button" class="pos-qty-btn row-qty-minus" tabindex="-1">−</button>
                    <input type="number" step="0.01" min="0.01" name="items[${rowIdx}][quantity]" value="${data.quantity}" class="pos-qty-input font-numeric row-qty">
                    <button type="button" class="pos-qty-btn row-qty-plus" tabindex="-1">+</button>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIdx}][unit_price]" value="${data.unitPrice.toFixed(2)}" class="erp-input font-numeric text-center text-xs row-price ${priceLockClass}" ${priceLockAttrs}>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIdx}][line_discount]" value="${data.lineDiscount.toFixed(2)}" class="erp-input font-numeric text-center text-xs row-discount">
            </td>
            <td class="text-center font-numeric font-bold text-xs row-subtotal-text">
                ${lineSubtotal.toFixed(2)}
            </td>
            <td>
                <input type="text" name="items[${rowIdx}][notes]" value="${data.notes || ''}" class="erp-input text-xs" placeholder="ملاحظة...">
            </td>
            <td class="text-center">
                <button type="button" class="erp-btn erp-btn-ghost erp-btn-sm text-[var(--danger)] remove-row-btn" title="حذف البند">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </td>
        `;

        // PREPEND TO TOP OF TABLE BODY
        tableBody.prepend(tr);
        bindRowEvents(tr);
        updateRowCounters();
    }

    function bindRowEvents(row) {
        const qtyInput = row.querySelector('.row-qty');
        const minusBtn = row.querySelector('.row-qty-minus');
        const plusBtn = row.querySelector('.row-qty-plus');
        const priceInput = row.querySelector('.row-price');
        const discountInput = row.querySelector('.row-discount');
        const unitSelect = row.querySelector('.row-unit-select');
        const factorHidden = row.querySelector('.row-conversion-factor');
        const subtotalText = row.querySelector('.row-subtotal-text');
        const removeBtn = row.querySelector('.remove-row-btn');

        function updateRowSubtotal() {
            const q = parseFloat(qtyInput?.value || 0);
            const p = parseFloat(priceInput?.value || 0);
            const d = parseFloat(discountInput?.value || 0);
            const sub = Math.max(0, (q * p) - d);
            if (subtotalText) {
                subtotalText.textContent = sub.toFixed(2);
            }
            recalculateTotals();
        }

        if (minusBtn && qtyInput) {
            minusBtn.addEventListener('click', (e) => {
                e.preventDefault();
                let q = parseFloat(qtyInput.value) || 1;
                if (q > 1) {
                    qtyInput.value = (q - 1).toFixed(0);
                    updateRowSubtotal();
                }
            });
        }

        if (plusBtn && qtyInput) {
            plusBtn.addEventListener('click', (e) => {
                e.preventDefault();
                let q = parseFloat(qtyInput.value) || 1;
                qtyInput.value = (q + 1).toFixed(0);
                updateRowSubtotal();
            });
        }

        if (qtyInput) qtyInput.addEventListener('input', updateRowSubtotal);
        if (priceInput) priceInput.addEventListener('input', updateRowSubtotal);
        if (discountInput) discountInput.addEventListener('input', updateRowSubtotal);

        if (unitSelect && factorHidden) {
            unitSelect.addEventListener('change', () => {
                const opt = unitSelect.options[unitSelect.selectedIndex];
                const factor = parseFloat(opt?.dataset.factor || 1.0) || 1.0;
                factorHidden.value = factor;
                recalculateTotals();
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                row.remove();
                updateRowCounters();
                recalculateTotals();
                if (quickProductInput) quickProductInput.focus();
            });
        }
    }

    function updateRowCounters() {
        if (!tableBody) return;
        const rows = tableBody.querySelectorAll('.sales-item-row');
        rows.forEach((r, idx) => {
            const counter = r.querySelector('.row-counter');
            if (counter) counter.textContent = idx + 1;
        });
    }

    // Clear Cart Button
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (tableBody && tableBody.children.length > 0) {
                if (confirm('هل أنت متأكد من تفريغ كافة الأصناف في سلة المبيعات؟')) {
                    tableBody.innerHTML = '';
                    recalculateTotals();
                    if (quickProductInput) quickProductInput.focus();
                }
            }
        });
    }

    // Manual Add Button
    if (manualAddBtn) {
        manualAddBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const firstProd = productsData[0];
            if (!firstProd) {
                alert('لا توجد منتجات مسجلة في النظام.');
                return;
            }
            const firstUnit = unitsData[0];
            insertRowAtTop({
                productId: firstProd.id,
                productName: firstProd.name,
                productSku: firstProd.sku,
                productBarcode: firstProd.barcode,
                unitId: firstUnit?.id || 1,
                conversionFactor: firstUnit ? parseFloat(firstUnit.factor) : 1.0,
                quantity: 1,
                unitPrice: getProductPriceForTier(firstProd),
                lineDiscount: 0,
                notes: ''
            });
            recalculateTotals();
        });
    }

    // Bind existing rows (e.g. from validation error back)
    if (tableBody) {
        tableBody.querySelectorAll('.sales-item-row').forEach(row => {
            bindRowEvents(row);
        });
    }

    function recalculateTotals() {
        if (!tableBody) return;

        let itemsCount = 0;
        let totalUnits = 0;
        let itemsSubtotal = 0;
        let totalLineDiscounts = 0;

        const rows = tableBody.querySelectorAll('.sales-item-row');
        rows.forEach(r => {
            const q = parseFloat(r.querySelector('.row-qty')?.value || 0);
            const p = parseFloat(r.querySelector('.row-price')?.value || 0);
            const d = parseFloat(r.querySelector('.row-discount')?.value || 0);

            if (q > 0) {
                itemsCount++;
                totalUnits += q;
                itemsSubtotal += (q * p);
                totalLineDiscounts += d;
            }
        });

        // Invoice Level Discount
        const discType = discountTypeSelect ? discountTypeSelect.value : 'fixed';
        const discVal = parseFloat(discountValueInput ? discountValueInput.value : 0) || 0;
        let invoiceDiscount = 0;

        const subtotalAfterLines = Math.max(0, itemsSubtotal - totalLineDiscounts);

        if (discVal > 0) {
            if (discType === 'percentage') {
                invoiceDiscount = (subtotalAfterLines * Math.min(100, discVal)) / 100;
            } else {
                invoiceDiscount = Math.min(subtotalAfterLines, discVal);
            }
        }

        const totalDiscount = totalLineDiscounts + invoiceDiscount;
        const shipping = parseFloat(shippingInput ? shippingInput.value : 0) || 0;
        const grandTotal = Math.max(0, itemsSubtotal - totalDiscount + shipping);

        // Update Summary Cards
        if (summaryItemsCount) summaryItemsCount.textContent = itemsCount;
        if (summaryTotalUnits) summaryTotalUnits.textContent = totalUnits.toFixed(fmod(totalUnits, 1) === 0 ? 0 : 2);
        if (summaryItemsSubtotal) summaryItemsSubtotal.textContent = `${itemsSubtotal.toFixed(2)} ج.م`;
        if (summaryDiscountDisplay) summaryDiscountDisplay.textContent = `${totalDiscount.toFixed(2)} ج.م`;
        if (summaryDiscountHint) {
            if (totalDiscount > 0) {
                summaryDiscountHint.textContent = `خصم بنود: ${totalLineDiscounts.toFixed(2)} + خصم فاتورة: ${invoiceDiscount.toFixed(2)}`;
            } else {
                summaryDiscountHint.textContent = 'لا يوجد خصم مطبق';
            }
        }
        if (summaryGrandTotal) summaryGrandTotal.textContent = `${grandTotal.toFixed(2)} ج.م`;
    }

    function fmod(a, b) {
        return Number((a - (Math.floor(a / b) * b)).toPrecision(8));
    }

    // POS Keyboard Shortcuts (F2: Search, F9: Submit/Post, Esc: Close)
    window.addEventListener('keydown', (e) => {
        // F2: Focus Search Bar immediately
        if (e.key === 'F2') {
            e.preventDefault();
            if (quickProductInput) {
                quickProductInput.focus();
                quickProductInput.select();
            }
            return;
        }

        // F9: Fast Checkout & Post
        if (e.key === 'F9') {
            e.preventDefault();
            if (posSubmitBtn && !isSubmitting) {
                posSubmitBtn.click();
            }
            return;
        }
    });

    // Guard Against Double Submissions
    if (salesForm) {
        salesForm.addEventListener('submit', (e) => {
            const rows = tableBody ? tableBody.querySelectorAll('.sales-item-row') : [];
            if (rows.length === 0) {
                e.preventDefault();
                alert('سلة المبيعات فارغة! يرجى إضافة صنف واحد على الأقل قبل حفظ الفاتورة.');
                if (quickProductInput) quickProductInput.focus();
                return;
            }

            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
        });
    }

    // Quick Add Customer Modal Logic
    const quickAddCustomerBtn = document.getElementById('quickAddCustomerBtn');
    const quickCustomerModal = document.getElementById('quickCustomerModal');
    const closeQuickCustomerModalBtn = document.getElementById('closeQuickCustomerModalBtn');
    const cancelQuickCustomerBtn = document.getElementById('cancelQuickCustomerBtn');
    const quickCustomerForm = document.getElementById('quickCustomerForm');
    const quickCustomerAlert = document.getElementById('quickCustomerAlert');
    const quickCustomerAlertMsg = document.getElementById('quickCustomerAlertMsg');
    const saveQuickCustomerBtn = document.getElementById('saveQuickCustomerBtn');

    let previousCustomerValue = customerSelect ? customerSelect.value : '';

    function openCustomerModal() {
        if (!quickCustomerModal) return;
        if (quickCustomerForm) quickCustomerForm.reset();
        if (quickCustomerAlert) quickCustomerAlert.classList.add('hidden');
        quickCustomerModal.classList.add('open');
        const nameInput = document.getElementById('quickCustName');
        if (nameInput) setTimeout(() => nameInput.focus(), 120);
    }

    function closeCustomerModal() {
        if (!quickCustomerModal) return;
        quickCustomerModal.classList.remove('open');
        if (customerSelect && customerSelect.value === '__add_new__') {
            customerSelect.value = previousCustomerValue;
        }
    }

    if (quickAddCustomerBtn) {
        quickAddCustomerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openCustomerModal();
        });
    }

    if (closeQuickCustomerModalBtn) {
        closeQuickCustomerModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeCustomerModal();
        });
    }

    if (cancelQuickCustomerBtn) {
        cancelQuickCustomerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeCustomerModal();
        });
    }

    if (quickCustomerModal) {
        quickCustomerModal.addEventListener('click', (e) => {
            if (e.target === quickCustomerModal) {
                closeCustomerModal();
            }
        });
    }

    if (customerSelect) {
        customerSelect.addEventListener('focus', () => {
            if (customerSelect.value !== '__add_new__') {
                previousCustomerValue = customerSelect.value;
            }
        });

        customerSelect.addEventListener('change', () => {
            if (customerSelect.value === '__add_new__') {
                openCustomerModal();
            } else {
                previousCustomerValue = customerSelect.value;
                const selectedOpt = customerSelect.options[customerSelect.selectedIndex];
                const tier = selectedOpt ? selectedOpt.dataset.tier : 'retail';
                if (customerTierHint) {
                    const label = tier === 'wholesale' ? 'جملة' : (tier === 'distributor' ? 'موزع' : 'تجزئة');
                    customerTierHint.textContent = `فئة: ${label}`;
                }
                if (priceTierSelect && tier) {
                    priceTierSelect.value = tier;
                }
            }
        });
    }

    if (quickCustomerForm) {
        quickCustomerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (quickCustomerAlert) quickCustomerAlert.classList.add('hidden');
            if (saveQuickCustomerBtn) saveQuickCustomerBtn.disabled = true;

            const formData = new FormData(quickCustomerForm);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const response = await fetch(quickCustomerForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    let errMsg = result.message || 'حدث خطأ أثناء حفظ العميل.';
                    if (result.errors) {
                        const firstErr = Object.values(result.errors)[0];
                        if (Array.isArray(firstErr) && firstErr.length > 0) errMsg = firstErr[0];
                    }
                    if (quickCustomerAlert && quickCustomerAlertMsg) {
                        quickCustomerAlertMsg.textContent = errMsg;
                        quickCustomerAlert.classList.remove('hidden');
                    } else {
                        alert(errMsg);
                    }
                    if (saveQuickCustomerBtn) saveQuickCustomerBtn.disabled = false;
                    return;
                }

                const newCust = result.customer;
                if (customerSelect && newCust) {
                    const opt = document.createElement('option');
                    opt.value = newCust.id;
                    opt.dataset.tier = newCust.pricing_tier || 'retail';
                    opt.textContent = `${newCust.name} (${newCust.phone})`;
                    customerSelect.appendChild(opt);

                    customerSelect.value = newCust.id;
                    previousCustomerValue = newCust.id;

                    if (customerTierHint) {
                        const label = newCust.pricing_tier === 'wholesale' ? 'جملة' : 'تجزئة';
                        customerTierHint.textContent = `فئة: ${label}`;
                    }
                    if (priceTierSelect && newCust.pricing_tier) {
                        priceTierSelect.value = newCust.pricing_tier;
                    }
                }

                closeCustomerModal();
            } catch (err) {
                if (quickCustomerAlert && quickCustomerAlertMsg) {
                    quickCustomerAlertMsg.textContent = 'تعذر الاتصال بالخادم. يرجى المحاولة مرة أخرى.';
                    quickCustomerAlert.classList.remove('hidden');
                } else {
                    alert('تعذر الاتصال بالخادم.');
                }
            } finally {
                if (saveQuickCustomerBtn) saveQuickCustomerBtn.disabled = false;
            }
        });
    }

    // POS Quick Open Shift Modal
    const posOpenShiftTriggerBtn = document.getElementById('posOpenShiftTriggerBtn');
    const posOpenShiftModal = document.getElementById('posOpenShiftModal');
    const closePosOpenShiftModalBtn = document.getElementById('closePosOpenShiftModalBtn');
    const cancelPosOpenShiftBtn = document.getElementById('cancelPosOpenShiftBtn');

    if (posOpenShiftTriggerBtn && posOpenShiftModal) {
        posOpenShiftTriggerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            posOpenShiftModal.classList.remove('hidden');
            posOpenShiftModal.classList.add('open');
            const obInput = document.getElementById('posShiftOpeningBalance');
            if (obInput) setTimeout(() => obInput.focus(), 100);
        });
    }

    const closeOpenShift = () => {
        if (posOpenShiftModal) {
            posOpenShiftModal.classList.remove('open');
            posOpenShiftModal.classList.add('hidden');
        }
    };
    if (closePosOpenShiftModalBtn) closePosOpenShiftModalBtn.addEventListener('click', closeOpenShift);
    if (cancelPosOpenShiftBtn) cancelPosOpenShiftBtn.addEventListener('click', closeOpenShift);

    // POS Quick Close Shift Modal
    const posCloseShiftTriggerBtn = document.getElementById('posCloseShiftTriggerBtn');
    const posCloseShiftModal = document.getElementById('posCloseShiftModal');
    const closePosCloseShiftModalBtn = document.getElementById('closePosCloseShiftModalBtn');
    const cancelPosCloseShiftBtn = document.getElementById('cancelPosCloseShiftBtn');

    if (posCloseShiftTriggerBtn && posCloseShiftModal) {
        posCloseShiftTriggerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            posCloseShiftModal.classList.remove('hidden');
            posCloseShiftModal.classList.add('open');
            const abInput = document.getElementById('posShiftActualBalance');
            if (abInput) setTimeout(() => abInput.focus(), 100);
        });
    }

    const closeCloseShift = () => {
        if (posCloseShiftModal) {
            posCloseShiftModal.classList.remove('open');
            posCloseShiftModal.classList.add('hidden');
        }
    };
    if (closePosCloseShiftModalBtn) closePosCloseShiftModalBtn.addEventListener('click', closeCloseShift);
    if (cancelPosCloseShiftBtn) cancelPosCloseShiftBtn.addEventListener('click', closeCloseShift);

    // Initial calculation
    recalculateTotals();
    updateDiscountLabel();

    // Auto-focus search input on page load
    if (quickProductInput) {
        setTimeout(() => {
            quickProductInput.focus();
        }, 100);
    }
}

function initSalesShow() {
    // Show view confirmation handlers handled via data-confirm in common.js
}
