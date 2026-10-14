/**
 * Kayan ERP — Product Show Details Page Interactions
 * Direct thermal printing, quantity adjusting, barcode modal preview
 */

import { showPrintToast } from './products-index.js';

export function initProductShow() {
    const page = document.querySelector('[data-page="products-show"]');
    if (!page) return;

    const productId = page.dataset.productId;
    const showQtyInput = page.querySelector('#showLabelQty');
    const modalQtyInput = page.querySelector('#labelQuantity');
    const printModal = page.querySelector('#printBarcodeModal');

    // 1. Direct Print from Show (handles both control-panel and card buttons)
    document.querySelectorAll('[data-action="direct-print-show"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const qty = parseInt(showQtyInput ? showQtyInput.value : 1, 10) || 1;
            showPrintToast('جاري إرسال الملصق مباشرة إلى طابعة الباركود...');

            let iframe = document.getElementById('directPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'directPrintIframe';
                iframe.name = 'directPrintIframe';
                iframe.classList.add('direct-print-iframe');
                document.body.appendChild(iframe);
            }
            iframe.src = `/products/${productId}/print-label?quantity=${qty}&autoprint=1`;
        });
    });

    // 2. Adjust Show Qty
    document.querySelectorAll('[data-action="adjust-show-qty"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const delta = parseInt(btn.dataset.delta, 10) || 0;
            if (showQtyInput) {
                const current = parseInt(showQtyInput.value, 10) || 1;
                showQtyInput.value = Math.max(1, Math.min(500, current + delta));
            }
        });
    });

    // 3. Print Modal Handlers
    function openPrintModal() {
        if (printModal) {
            printModal.classList.add('open');
            printModal.style.display = 'flex';
        }
    }

    function closePrintModal() {
        if (printModal) {
            printModal.classList.remove('open');
            printModal.style.display = 'none';
        }
    }

    page.querySelectorAll('[data-action="open-print-modal"]').forEach(btn => {
        btn.addEventListener('click', openPrintModal);
    });

    page.querySelectorAll('[data-action="close-print-modal"]').forEach(btn => {
        btn.addEventListener('click', closePrintModal);
    });

    if (printModal) {
        printModal.addEventListener('click', (e) => {
            if (e.target === printModal) closePrintModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && printModal.classList.contains('open')) {
                closePrintModal();
            }
        });
    }

    // Modal Qty Adjust
    page.querySelectorAll('[data-action="adjust-modal-qty"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const delta = parseInt(btn.dataset.delta, 10) || 0;
            if (modalQtyInput) {
                const current = parseInt(modalQtyInput.value, 10) || 1;
                modalQtyInput.value = Math.max(1, Math.min(500, current + delta));
            }
        });
    });

    // Modal Preset Qty (1, 5, 10, 20, 50)
    page.querySelectorAll('[data-action="set-modal-qty"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const val = parseInt(btn.dataset.qty, 10) || 1;
            if (modalQtyInput) {
                modalQtyInput.value = val;
            }
        });
    });

    // Open Print Preview / Window
    page.querySelectorAll('[data-action="open-print-preview"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const autoPrint = btn.dataset.autoprint === 'true';
            const qty = parseInt(modalQtyInput ? modalQtyInput.value : 1, 10) || 1;
            let url = `/products/${productId}/print-label?quantity=${qty}`;
            if (autoPrint) {
                url += '&autoprint=1';
            }
            window.open(url, '_blank');
            closePrintModal();
        });
    });
}
