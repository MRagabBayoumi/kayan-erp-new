/**
 * Kayan ERP — Print Label Page Module
 * Auto-print triggering, print controls, tip dismissal
 */

export function initPrintLabelPage() {
    const isAutoPrint = document.body.dataset.autoPrint === 'true';

    // Print button
    document.querySelectorAll('[data-action="print-now"]').forEach(btn => {
        btn.addEventListener('click', () => {
            window.print();
        });
    });

    // Close button
    document.querySelectorAll('[data-action="close-window"]').forEach(btn => {
        btn.addEventListener('click', () => {
            window.close();
            if (!window.closed) {
                window.history.back();
            }
        });
    });

    // Dismiss tip button
    document.querySelectorAll('[data-action="dismiss-tip"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const tipBox = btn.closest('.preview-tips');
            if (tipBox) {
                tipBox.style.display = 'none';
            }
        });
    });

    // Auto-print if requested
    if (isAutoPrint) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.focus();
                window.print();
            }, 180);
        });
    }
}

// Auto initialize if on print-label page
if (document.querySelector('.roll-stage')) {
    initPrintLabelPage();
}
