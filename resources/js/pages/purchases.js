/**
 * Kayan ERP — Purchases Module Interactions
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
    const addBtn = document.getElementById('addPurchaseItemBtn');
    const shippingInput = document.getElementById('shippingCost');
    const subtotalText = document.getElementById('purchaseSubtotalText');
    const shippingText = document.getElementById('purchaseShippingText');
    const grandTotalText = document.getElementById('purchaseGrandTotalText');

    let nextIndex = 1;

    function recalculateTotals() {
        let subtotal = 0;
        const rows = tableBody.querySelectorAll('.purchase-item-row');

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
        });

        const shipping = parseFloat(shippingInput ? shippingInput.value : 0) || 0;
        const grandTotal = subtotal + shipping;

        if (subtotalText) subtotalText.textContent = subtotal.toFixed(2);
        if (shippingText) shippingText.textContent = shipping.toFixed(2);
        if (grandTotalText) grandTotalText.textContent = grandTotal.toFixed(2);
    }

    function attachRowEvents(row) {
        const productSelect = row.querySelector('.purchase-product-select');
        const qtyInput = row.querySelector('.purchase-qty-input');
        const costInput = row.querySelector('.purchase-cost-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        if (productSelect) {
            productSelect.addEventListener('change', () => {
                const selectedOpt = productSelect.options[productSelect.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.cost) {
                    const costVal = parseFloat(selectedOpt.dataset.cost) || 0;
                    if (costInput && (costInput.value === '0.00' || costInput.value === '0' || !costInput.value)) {
                        costInput.value = costVal.toFixed(2);
                    }
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
                recalculateTotals();
            });
        }
    }

    // Attach to existing initial rows
    if (tableBody) {
        tableBody.querySelectorAll('.purchase-item-row').forEach(row => attachRowEvents(row));
    }

    // Add new row from template
    if (addBtn && template && tableBody) {
        addBtn.addEventListener('click', () => {
            const html = template.innerHTML.replace(/__INDEX__/g, nextIndex.toString());
            nextIndex++;

            const tempContainer = document.createElement('tbody');
            tempContainer.innerHTML = html.trim();
            const newRow = tempContainer.firstElementChild;

            tableBody.appendChild(newRow);
            attachRowEvents(newRow);
            recalculateTotals();
        });
    }

    if (shippingInput) {
        shippingInput.addEventListener('input', recalculateTotals);
    }

    recalculateTotals();
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
