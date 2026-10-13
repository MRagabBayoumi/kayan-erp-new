/**
 * Kayan ERP — Products Index Page Interactions
 * Bulk selection, direct thermal printing via iframe, batch print modal
 */

let toastTimeout = null;

export function initProductsIndex() {
    const page = document.querySelector('[data-page="products-index"]');
    if (!page) return;

    const selectAllCb = page.querySelector('#selectAllCheckbox');
    const rowCheckboxes = page.querySelectorAll('.product-select-cb');
    const actionBar = page.querySelector('#batchActionBar');
    const topBatchBtn = page.querySelector('#topBatchPrintBtn');
    const selectedCountEl = page.querySelector('#selectedProductsCount');
    const topBatchCountEl = page.querySelector('#topBatchCount');

    function getSelectedCheckboxes() {
        return Array.from(page.querySelectorAll('.product-select-cb:checked'));
    }

    function onRowSelectionChange() {
        const selected = getSelectedCheckboxes();
        const count = selected.length;

        if (selectedCountEl) selectedCountEl.textContent = count;
        if (topBatchCountEl) topBatchCountEl.textContent = count;

        if (count > 0) {
            if (actionBar) {
                actionBar.classList.remove('hidden');
                actionBar.style.display = 'flex';
            }
            if (topBatchBtn) {
                topBatchBtn.classList.remove('hidden');
                topBatchBtn.style.display = 'inline-flex';
            }
        } else {
            if (actionBar) {
                actionBar.classList.add('hidden');
                actionBar.style.display = 'none';
            }
            if (topBatchBtn) {
                topBatchBtn.classList.add('hidden');
                topBatchBtn.style.display = 'none';
            }
        }

        if (selectAllCb && rowCheckboxes.length > 0) {
            selectAllCb.checked = (selected.length === rowCheckboxes.length);
            selectAllCb.indeterminate = (selected.length > 0 && selected.length < rowCheckboxes.length);
        }
    }

    // Toggle select all
    if (selectAllCb) {
        selectAllCb.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => {
                cb.checked = selectAllCb.checked;
            });
            onRowSelectionChange();
        });
    }

    // Row checkbox change
    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', onRowSelectionChange);
    });

    // Clear selections button
    page.querySelector('[data-action="clear-selections"]')?.addEventListener('click', () => {
        rowCheckboxes.forEach(cb => {
            cb.checked = false;
        });
        if (selectAllCb) {
            selectAllCb.checked = false;
            selectAllCb.indeterminate = false;
        }
        onRowSelectionChange();
    });

    // Batch Print Modal handling
    const batchModal = page.querySelector('#batchPrintModal');
    const openBatchBtns = page.querySelectorAll('[data-action="open-batch-modal"]');
    const closeBatchBtns = page.querySelectorAll('[data-action="close-batch-modal"]');

    function openBatchPrintModal() {
        const selected = getSelectedCheckboxes();
        if (selected.length === 0) {
            alert('يرجى تحديد صنف واحد على الأقل للطباعة.');
            return;
        }

        const tbody = page.querySelector('#batchModalTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        selected.forEach((cb, index) => {
            const id = cb.dataset.id;
            const name = cb.dataset.name;
            const sku = cb.dataset.sku;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center font-muted font-sm">${index + 1}</td>
                <td><strong class="text-main font-sm">${name}</strong></td>
                <td><span class="font-numeric text-primary font-bold font-sm">${sku}</span></td>
                <td class="text-center">
                    <div class="batch-qty-control">
                        <button type="button" class="erp-btn erp-btn-secondary font-numeric" data-action="adjust-item-qty" data-id="${id}" data-delta="-1">−</button>
                        <input type="number" id="batch_qty_${id}" class="erp-input font-numeric batch-qty-input" data-id="${id}" value="1" min="1" max="500">
                        <button type="button" class="erp-btn erp-btn-secondary font-numeric" data-action="adjust-item-qty" data-id="${id}" data-delta="1">+</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        recalculateBatchTotal();

        if (batchModal) {
            batchModal.classList.add('open');
            batchModal.style.display = 'flex';
        }
    }

    function closeBatchPrintModal() {
        if (batchModal) {
            batchModal.classList.remove('open');
            batchModal.style.display = 'none';
        }
    }

    openBatchBtns.forEach(btn => btn.addEventListener('click', openBatchPrintModal));
    closeBatchBtns.forEach(btn => btn.addEventListener('click', closeBatchPrintModal));

    if (batchModal) {
        batchModal.addEventListener('click', (e) => {
            if (e.target === batchModal) closeBatchPrintModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && batchModal.classList.contains('open')) {
                closeBatchPrintModal();
            }
        });
    }

    // Batch quantity adjust handlers inside modal
    page.addEventListener('click', (e) => {
        const adjustBtn = e.target.closest('[data-action="adjust-item-qty"]');
        if (adjustBtn) {
            const id = adjustBtn.dataset.id;
            const delta = parseInt(adjustBtn.dataset.delta, 10) || 0;
            const input = page.querySelector('#batch_qty_' + id);
            if (input) {
                const current = parseInt(input.value, 10) || 1;
                input.value = Math.max(1, Math.min(500, current + delta));
                recalculateBatchTotal();
            }
        }
    });

    page.addEventListener('input', (e) => {
        if (e.target.classList.contains('batch-qty-input')) {
            recalculateBatchTotal();
        }
    });

    // Preset quantity buttons (1, 2, 5, 10, 20)
    page.querySelectorAll('[data-preset-qty]').forEach(btn => {
        btn.addEventListener('click', () => {
            const val = parseInt(btn.dataset.presetQty, 10) || 1;
            page.querySelectorAll('.batch-qty-input').forEach(inp => {
                inp.value = val;
            });
            recalculateBatchTotal();
        });
    });

    function recalculateBatchTotal() {
        const inputs = page.querySelectorAll('.batch-qty-input');
        let totalLabels = 0;
        inputs.forEach(inp => {
            totalLabels += (parseInt(inp.value, 10) || 1);
        });

        const countEl = page.querySelector('#batchSummaryProductsCount');
        const totalEl = page.querySelector('#batchSummaryTotalLabels');
        if (countEl) countEl.textContent = inputs.length;
        if (totalEl) totalEl.textContent = totalLabels;
    }

    // Submit batch print form
    page.querySelectorAll('[data-action="submit-batch-print"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const autoPrint = btn.dataset.autoprint === 'true';
            const form = page.querySelector('#batchPrintForm');
            const container = page.querySelector('#batchFormHiddenInputs');
            const autoprintInput = page.querySelector('#batchFormAutoprint');
            if (!form || !container) return;

            if (autoprintInput) autoprintInput.value = autoPrint ? '1' : '0';
            container.innerHTML = '';

            const inputs = page.querySelectorAll('.batch-qty-input');
            if (inputs.length === 0) {
                alert('لا توجد أصناف محددة.');
                return;
            }

            inputs.forEach(inp => {
                const id = inp.dataset.id;
                const qty = parseInt(inp.value, 10) || 1;
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `products[${id}]`;
                hidden.value = qty;
                container.appendChild(hidden);
            });

            form.submit();
            closeBatchPrintModal();
        });
    });

    // Direct Printing from index row
    page.addEventListener('click', (e) => {
        const printBtn = e.target.closest('[data-action="direct-print-row"]');
        if (printBtn) {
            e.preventDefault();
            e.stopPropagation();
            const productId = printBtn.dataset.productId;
            if (!productId) return;

            showPrintToast('جاري إرسال ملصق الصنف إلى الطابعة...');
            const iframe = getOrCreatePrintIframe();
            iframe.src = `/products/${productId}/print-label?quantity=1&autoprint=1`;
        }
    });

    // Direct Batch Print button
    page.querySelectorAll('[data-action="direct-print-batch"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const selected = getSelectedCheckboxes();
            if (selected.length === 0) {
                alert('يرجى تحديد صنف واحد على الأقل للطباعة.');
                return;
            }

            const ids = selected.map(cb => cb.dataset.id).filter(Boolean);
            showPrintToast(`جاري إرسال ${ids.length} أصناف للطباعة الفورية...`);

            const iframe = getOrCreatePrintIframe();
            const params = new URLSearchParams();
            params.set('autoprint', '1');
            ids.forEach(id => {
                params.set(`products[${id}]`, '1');
            });

            iframe.src = '/products/print-labels/batch?' + params.toString();
        });
    });

    // Row Click Navigation (Odoo List View Behavior: click anywhere on row to view product details)
    const tableBody = page.querySelector('.erp-table tbody');
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            // Ignore if clicking on interactive controls (checkbox, button, modal trigger, delete form, direct link)
            if (e.target.closest('input, button, select, textarea, form, .erp-btn, label, a')) {
                return;
            }

            const row = e.target.closest('tr.product-row-clickable');
            if (row && row.dataset.url) {
                window.location.href = row.dataset.url;
            }
        });
    }
}

function getOrCreatePrintIframe() {
    let iframe = document.getElementById('directPrintIframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'directPrintIframe';
        iframe.name = 'directPrintIframe';
        iframe.classList.add('direct-print-iframe');
        document.body.appendChild(iframe);
    }
    return iframe;
}

export function showPrintToast(msg) {
    const toast = document.getElementById('printNotificationToast');
    const msgEl = document.getElementById('printToastMessage');
    if (toast && msgEl) {
        msgEl.textContent = msg;
        toast.classList.remove('hidden');
        toast.style.display = 'flex';
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.add('hidden');
            toast.style.display = 'none';
        }, 2800);
    }
}
