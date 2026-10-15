/**
 * Kayan ERP — Purchases Module Interactions
 * Ergonomic POS/ERP barcode scanning, keyboard-driven navigation, and dynamic table entry.
 */

export function initPurchases() {
    const createPage = document.querySelector('[data-page="purchases-create"]');
    if (createPage) {
        initPurchaseCreate();
    }

    const showPage = document.querySelector('[data-page="purchases-show"]');
    if (showPage) {
        initPurchaseShow();
    }

    const ledgerPage = document.querySelector('[data-page="product-ledger"]');
    if (ledgerPage) {
        initProductLedger();
    }
}

function initPurchaseCreate() {
    const tableBody = document.getElementById('purchaseItemsBody');
    const template = document.getElementById('purchaseItemRowTemplate');
    const manualAddBtn = document.getElementById('addPurchaseItemBtn');
    const shippingInput = document.getElementById('shippingCost');

    // Upper Input Panel Elements
    const searchModeSelect = document.getElementById('searchModeSelect');
    const searchModeHint = document.getElementById('searchModeHint');
    const quickProductInput = document.getElementById('quickProductInput');
    const quickSearchInputLabel = document.getElementById('quickSearchInputLabel');
    const quickSearchResults = document.getElementById('quickSearchResults');
    const quickProductId = document.getElementById('quickProductId');
    const selectedProductBadge = document.getElementById('selectedProductBadge');
    const quickUnitSelect = document.getElementById('quickUnitSelect');
    const quickQtyInput = document.getElementById('quickQtyInput');
    const quickCostInput = document.getElementById('quickCostInput');
    const quickAddBtn = document.getElementById('quickAddBtn');

    // Discount Elements
    const discountTypeSelect = document.getElementById('invoiceDiscountType');
    const discountValueInput = document.getElementById('invoiceDiscountValue');
    const discountUnitLabel = document.getElementById('discountUnitLabel');

    // Summary Elements
    const itemsCountText = document.getElementById('purchaseItemsCountText');
    const totalQtyText = document.getElementById('purchaseTotalQtyText');
    const subtotalText = document.getElementById('purchaseSubtotalText');
    const discountText = document.getElementById('purchaseDiscountText');
    const shippingText = document.getElementById('purchaseShippingText');
    const grandTotalText = document.getElementById('purchaseGrandTotalText');

    const createPage = document.querySelector('[data-page="purchases-create"]');
    let productsData = [];
    let unitsData = [];

    if (createPage && createPage.dataset.products) {
        try {
            productsData = JSON.parse(createPage.dataset.products);
        } catch (e) {
            productsData = [];
        }
    }
    if (!productsData.length && Array.isArray(window.kayanProductsData)) {
        productsData = window.kayanProductsData;
    }

    if (createPage && createPage.dataset.units) {
        try {
            unitsData = JSON.parse(createPage.dataset.units);
        } catch (e) {
            unitsData = [];
        }
    }
    if (!unitsData.length && Array.isArray(window.kayanUnitsData)) {
        unitsData = window.kayanUnitsData;
    }

    // Fallback if data attribute was empty: read from select options
    if (!productsData.length) {
        const dummySelect = document.querySelector('.purchase-product-select');
        if (dummySelect) {
            Array.from(dummySelect.options).forEach(opt => {
                if (opt.value) {
                    productsData.push({
                        id: parseInt(opt.value, 10),
                        name: opt.text.split('(')[0].trim(),
                        sku: (opt.text.match(/\(([^)]+)\)/) || [])[1] || '',
                        barcode: '',
                        cost_price: parseFloat(opt.dataset.cost || 0),
                        unit: opt.dataset.unit || 'قطعة'
                    });
                }
            });
        }
    }

    let activeSuggestionIndex = -1;

    // Search Mode Change Handler
    if (searchModeSelect) {
        searchModeSelect.addEventListener('change', () => {
            const mode = searchModeSelect.value;
            if (mode === 'barcode') {
                if (quickSearchInputLabel) quickSearchInputLabel.textContent = 'مربع الباركود (مسدس الباركود)';
                if (quickProductInput) quickProductInput.placeholder = 'اضرب الباركود بمسدس الباركود...';
                if (searchModeHint) searchModeHint.textContent = 'اضرب الباركود بالمسدس (ينتقل للوحدة تلقائياً)';
            } else if (mode === 'sku') {
                if (quickSearchInputLabel) quickSearchInputLabel.textContent = 'كود الصنف (SKU)';
                if (quickProductInput) quickProductInput.placeholder = 'اكتب كود الصنف واضغط Enter...';
                if (searchModeHint) searchModeHint.textContent = 'اكتب كود الصنف أو اختر من القائمة';
            } else {
                if (quickSearchInputLabel) quickSearchInputLabel.textContent = 'اسم الصنف';
                if (quickProductInput) quickProductInput.placeholder = 'اكتب اسم الصنف للبحث...';
                if (searchModeHint) searchModeHint.textContent = 'ابحث بالاسم واختر الصنف المطلوب';
            }
            if (quickProductInput) quickProductInput.focus();
        });
    }

    // Discount Type & Value Change Handlers
    function updateDiscountLabel() {
        if (!discountTypeSelect || !discountUnitLabel) return;
        discountUnitLabel.textContent = discountTypeSelect.value === 'percentage' ? '%' : 'ج.م';
        recalculateTotals();
    }

    if (discountTypeSelect) {
        discountTypeSelect.addEventListener('change', updateDiscountLabel);
    }
    if (discountValueInput) {
        discountValueInput.addEventListener('input', recalculateTotals);
    }

    function normalizeArabic(text) {
        if (!text) return '';
        return text.toString().trim().toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/[\u064B-\u065F]/g, '');
    }

    // Product Autocomplete and Barcode Lookup
    function findProductByTerm(term, mode = 'barcode') {
        const clean = (term || '').trim().toLowerCase();
        if (!clean) return null;
        const norm = normalizeArabic(clean);

        // First check exact barcode match
        const exactBarcode = productsData.find(p => (p.barcode || '').trim().toLowerCase() === clean);
        if (exactBarcode) return exactBarcode;

        // Fallback to SKU
        const exactSku = productsData.find(p => (p.sku || '').trim().toLowerCase() === clean);
        if (exactSku) return exactSku;

        // Exact Name match (normalized)
        const exactName = productsData.find(p => normalizeArabic(p.name) === norm);
        if (exactName) return exactName;

        // Partial match
        return productsData.find(p => 
            (p.barcode || '').toLowerCase().includes(clean) ||
            (p.sku || '').toLowerCase().includes(clean) ||
            normalizeArabic(p.name).includes(norm)
        ) || null;
    }

    function renderSuggestions(query) {
        if (!quickSearchResults) return;
        const q = (query || '').trim();
        if (!q || q.length < 1) {
            quickSearchResults.classList.add('hidden');
            quickSearchResults.innerHTML = '';
            activeSuggestionIndex = -1;
            return;
        }

        const cleanQ = q.toLowerCase();
        const normQ = normalizeArabic(q);
        const mode = searchModeSelect ? searchModeSelect.value : 'barcode';

        const matches = productsData.filter(p => {
            const pBarcode = (p.barcode || '').toLowerCase();
            const pSku = (p.sku || '').toLowerCase();
            const pNameNorm = normalizeArabic(p.name);

            if (mode === 'sku') {
                return pSku.includes(cleanQ) || pNameNorm.includes(normQ);
            }
            if (mode === 'name') {
                return pNameNorm.includes(normQ) || pSku.includes(cleanQ) || pBarcode.includes(cleanQ);
            }
            return pBarcode.includes(cleanQ) || pSku.includes(cleanQ) || pNameNorm.includes(normQ);
        }).slice(0, 10);

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

        quickSearchResults.innerHTML = matches.map((p, idx) => `
            <div class="suggestion-item px-3 py-2 cursor-pointer hover:bg-[var(--surface-muted)] flex items-center justify-between text-xs border-b border-[var(--border-color)] last:border-b-0" data-index="${idx}" data-id="${p.id}">
                <div class="flex flex-col">
                    <span class="font-bold text-[var(--text-main)]">${p.name}</span>
                    <span class="text-[10px] text-[var(--text-muted)] font-mono">كود: ${p.sku} ${p.barcode ? '• باركود: ' + p.barcode : ''}</span>
                </div>
                <div class="text-left font-numeric">
                    <span class="font-bold text-[var(--primary)]">${p.cost_price.toFixed(2)}</span>
                    <span class="text-[10px] text-[var(--text-muted)]">ج.م / ${p.unit || 'قطعة'}</span>
                </div>
            </div>
        `).join('');

        quickSearchResults.querySelectorAll('.suggestion-item').forEach(el => {
            el.addEventListener('mousedown', (e) => {
                e.preventDefault();
                const pId = parseInt(el.dataset.id, 10);
                const found = productsData.find(item => item.id === pId);
                if (found) {
                    selectProduct(found);
                }
            });
        });

        quickSearchResults.classList.remove('hidden');
        activeSuggestionIndex = -1;
    }

    function selectProduct(product) {
        if (!product) return;

        if (quickProductId) quickProductId.value = product.id;
        if (quickProductInput) quickProductInput.value = `${product.name} (${product.sku})`;
        if (quickCostInput) quickCostInput.value = (product.cost_price || 0).toFixed(2);
        if (selectedProductBadge) selectedProductBadge.classList.remove('hidden');

        // Select unit matching product's unit
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

        // KEYBOARD WORKFLOW: Immediately transfer focus to Unit of Measurement
        if (quickUnitSelect) {
            quickUnitSelect.focus();
        } else if (quickQtyInput) {
            quickQtyInput.focus();
            quickQtyInput.select();
        }
    }

    // Search Input Events
    if (quickProductInput) {
        quickProductInput.addEventListener('input', (e) => {
            const term = e.target.value.trim();
            if (quickProductId && quickProductId.value) {
                // If user changes text after selecting, clear the ID
                quickProductId.value = '';
                if (selectedProductBadge) selectedProductBadge.classList.add('hidden');
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
                e.preventDefault();
                // 1. If an item in dropdown is active
                const items = quickSearchResults.querySelectorAll('.suggestion-item');
                if (items.length > 0 && activeSuggestionIndex >= 0 && items[activeSuggestionIndex]) {
                    const pId = parseInt(items[activeSuggestionIndex].dataset.id, 10);
                    const found = productsData.find(item => item.id === pId);
                    if (found) {
                        selectProduct(found);
                        return;
                    }
                }

                // 2. Exact match check (Barcode scanner sends Enter!)
                const term = quickProductInput.value.trim();
                const matched = findProductByTerm(term, mode);
                if (matched) {
                    selectProduct(matched);
                } else if (items.length > 0) {
                    // Pick first suggestion if available
                    const firstId = parseInt(items[0].dataset.id, 10);
                    const found = productsData.find(item => item.id === firstId);
                    if (found) selectProduct(found);
                } else {
                    alert('لم يتم العثور على صنف مطابق لهذا الباركود أو الكود.');
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

    // Keyboard Flow: Unit -> Tab -> Quantity
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
                if (quickQtyInput) {
                    quickQtyInput.focus();
                    quickQtyInput.select();
                }
            }
        });
    }

    // Keyboard Flow: Quantity -> Tab -> Cost
    if (quickQtyInput) {
        quickQtyInput.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                if (quickCostInput) {
                    quickCostInput.focus();
                    quickCostInput.select();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (quickCostInput) {
                    quickCostInput.focus();
                    quickCostInput.select();
                }
            }
        });
    }

    // Keyboard Flow: Cost -> Enter -> Commit to Table & Return to Search
    if (quickCostInput) {
        quickCostInput.addEventListener('keydown', (e) => {
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

    // COMMIT QUICK ADD FUNCTION: Inserts item into the TOP ROW of the table
    function commitQuickAdd() {
        let pId = quickProductId ? parseInt(quickProductId.value, 10) : null;
        let product = productsData.find(p => p.id === pId);

        if (!product) {
            // Try matching whatever is in quickProductInput
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

        const cost = parseFloat(quickCostInput ? quickCostInput.value : 0) || 0;
        if (cost < 0) {
            alert('سعر التكلفة لا يمكن أن يكون سالباً.');
            if (quickCostInput) quickCostInput.focus();
            return;
        }

        const selectedUnitName = quickUnitSelect ? quickUnitSelect.options[quickUnitSelect.selectedIndex]?.dataset.name || quickUnitSelect.options[quickUnitSelect.selectedIndex]?.text || 'قطعة' : 'قطعة';

        // Add to TOP of Table
        insertRowAtTop({
            productId: product.id,
            quantity: qty,
            unitCost: cost,
            unitName: selectedUnitName,
            notes: ''
        });

        // Reset Input Fields
        if (quickProductInput) quickProductInput.value = '';
        if (quickProductId) quickProductId.value = '';
        if (selectedProductBadge) selectedProductBadge.classList.add('hidden');
        if (quickQtyInput) quickQtyInput.value = '1';
        if (quickCostInput) quickCostInput.value = '0.00';
        if (quickSearchResults) quickSearchResults.classList.add('hidden');

        // RETURN FOCUS TO SEARCH INPUT FOR NEXT ITEM
        if (quickProductInput) {
            quickProductInput.focus();
        }

        recalculateTotals();
    }

    function insertRowAtTop(data) {
        if (!tableBody || !template) return;

        // Check if there is an empty initial placeholder row with no product selected
        const existingRows = tableBody.querySelectorAll('.purchase-item-row');
        if (existingRows.length === 1) {
            const firstSelect = existingRows[0].querySelector('.purchase-product-select');
            if (firstSelect && !firstSelect.value) {
                existingRows[0].remove();
            }
        }

        const tempIndex = Date.now();
        let html = template.innerHTML
            .replace(/__INDEX__/g, tempIndex.toString())
            .replace(/__NUM__/g, '1')
            .replace(/__UNIT__/g, data.unitName || 'قطعة')
            .replace(/__QTY__/g, data.quantity.toString())
            .replace(/__COST__/g, data.unitCost.toFixed(2))
            .replace(/__SUBTOTAL__/g, (data.quantity * data.unitCost).toFixed(2));

        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = html.trim();
        const newRow = tempContainer.firstElementChild;

        // Set selected product in the row select
        const prodSelect = newRow.querySelector('.purchase-product-select');
        if (prodSelect) {
            prodSelect.value = data.productId.toString();
        }

        // PREPEND TO TABLE: Newly scanned/entered item is ALWAYS AT THE TOP ROW
        if (tableBody.firstChild) {
            tableBody.insertBefore(newRow, tableBody.firstChild);
        } else {
            tableBody.appendChild(newRow);
        }

        // Visual flash effect on new top row
        newRow.classList.add('bg-emerald-50', 'dark:bg-emerald-950/40', 'transition-colors', 'duration-700');
        setTimeout(() => {
            newRow.classList.remove('bg-emerald-50', 'dark:bg-emerald-950/40');
        }, 800);

        attachRowEvents(newRow);
        reindexRows();
    }

    function reindexRows() {
        const rows = tableBody.querySelectorAll('.purchase-item-row');
        rows.forEach((row, idx) => {
            row.setAttribute('data-row-index', idx.toString());

            const numCell = row.querySelector('.row-num-cell');
            if (numCell) numCell.textContent = (idx + 1).toString();

            const select = row.querySelector('.purchase-product-select');
            if (select) select.name = `items[${idx}][product_id]`;

            const unitInput = row.querySelector('.purchase-unit-input');
            if (unitInput) unitInput.name = `items[${idx}][unit_name]`;

            const qty = row.querySelector('.purchase-qty-input');
            if (qty) qty.name = `items[${idx}][quantity]`;

            const cost = row.querySelector('.purchase-cost-input');
            if (cost) cost.name = `items[${idx}][unit_cost]`;

            const notes = row.querySelector('input[type="text"][name*="notes"]');
            if (notes) notes.name = `items[${idx}][notes]`;
        });
    }

    function recalculateTotals() {
        let subtotal = 0;
        let totalQty = 0;
        const rows = tableBody ? tableBody.querySelectorAll('.purchase-item-row') : [];

        rows.forEach(row => {
            const qtyInput = row.querySelector('.purchase-qty-input');
            const costInput = row.querySelector('.purchase-cost-input');
            const subtotalCell = row.querySelector('.purchase-line-subtotal');

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const cost = parseFloat(costInput ? costInput.value : 0) || 0;
            const lineTotal = qty * cost;

            if (subtotalCell) {
                subtotalCell.textContent = lineTotal.toFixed(2);
            }
            subtotal += lineTotal;
            totalQty += qty;
        });

        // Discount calculation
        const discType = discountTypeSelect ? discountTypeSelect.value : 'fixed';
        const discVal = parseFloat(discountValueInput ? discountValueInput.value : 0) || 0;
        let discAmount = 0;
        if (discVal > 0) {
            if (discType === 'percentage') {
                discAmount = (subtotal * Math.min(100, discVal)) / 100;
            } else {
                discAmount = Math.min(subtotal, discVal);
            }
        }

        const shipping = parseFloat(shippingInput ? shippingInput.value : 0) || 0;
        const grandTotal = Math.max(0, subtotal - discAmount + shipping);

        // Update Summary Cards
        if (itemsCountText) itemsCountText.textContent = rows.length.toString();
        if (totalQtyText) totalQtyText.textContent = totalQty.toFixed(2);
        if (subtotalText) subtotalText.textContent = subtotal.toFixed(2);
        if (discountText) discountText.textContent = discAmount.toFixed(2);
        if (shippingText) shippingText.textContent = shipping.toFixed(2);
        if (grandTotalText) grandTotalText.textContent = grandTotal.toFixed(2);
    }

    function attachRowEvents(row) {
        const productSelect = row.querySelector('.purchase-product-select');
        const qtyInput = row.querySelector('.purchase-qty-input');
        const costInput = row.querySelector('.purchase-cost-input');
        const removeBtn = row.querySelector('.remove-row-btn');
        const unitBadge = row.querySelector('.purchase-unit-badge');
        const unitInput = row.querySelector('.purchase-unit-input');

        if (productSelect) {
            productSelect.addEventListener('change', () => {
                const selectedOpt = productSelect.options[productSelect.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.cost) {
                    const costVal = parseFloat(selectedOpt.dataset.cost) || 0;
                    if (costInput && (costInput.value === '0.00' || costInput.value === '0' || !costInput.value)) {
                        costInput.value = costVal.toFixed(2);
                    }
                }
                if (selectedOpt && selectedOpt.dataset.unit) {
                    if (unitBadge) unitBadge.textContent = selectedOpt.dataset.unit;
                    if (unitInput) unitInput.value = selectedOpt.dataset.unit;
                }
                recalculateTotals();
            });
        }

        if (qtyInput) {
            qtyInput.addEventListener('input', recalculateTotals);
        }

        if (costInput) {
            costInput.addEventListener('input', recalculateTotals);
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                const totalRows = tableBody.querySelectorAll('.purchase-item-row').length;
                if (totalRows <= 1) {
                    alert('يجب الإبقاء على صنف واحد على الأقل في فاتورة الشراء.');
                    return;
                }
                row.remove();
                reindexRows();
                recalculateTotals();
            });
        }
    }

    // Attach to existing initial rows
    if (tableBody) {
        tableBody.querySelectorAll('.purchase-item-row').forEach(row => attachRowEvents(row));
    }

    // Manual add button (adds to bottom if clicked)
    if (manualAddBtn && template && tableBody) {
        manualAddBtn.addEventListener('click', () => {
            const tempIndex = Date.now();
            const html = template.innerHTML
                .replace(/__INDEX__/g, tempIndex.toString())
                .replace(/__NUM__/g, (tableBody.querySelectorAll('.purchase-item-row').length + 1).toString())
                .replace(/__UNIT__/g, 'قطعة')
                .replace(/__QTY__/g, '1')
                .replace(/__COST__/g, '0.00')
                .replace(/__SUBTOTAL__/g, '0.00');

            const tempContainer = document.createElement('tbody');
            tempContainer.innerHTML = html.trim();
            const newRow = tempContainer.firstElementChild;

            tableBody.appendChild(newRow);
            attachRowEvents(newRow);
            reindexRows();
            recalculateTotals();
        });
    }

    if (shippingInput) {
        shippingInput.addEventListener('input', recalculateTotals);
    }

    reindexRows();
    recalculateTotals();

    // Initial focus on search input
    if (quickProductInput) {
        setTimeout(() => quickProductInput.focus(), 100);
    }
}

function initPurchaseShow() {
    const printBtn = document.querySelector('[data-action="print-window"]');
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }
}

function initProductLedger() {
    const printBtn = document.querySelector('[data-action="print-window"]');
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }
}
