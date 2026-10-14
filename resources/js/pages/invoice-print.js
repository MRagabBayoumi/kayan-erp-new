/**
 * BayoSoft ERP — Invoice Print Controller
 * Handles Print Now, Close Window, and Automatic Trigger
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Print Now Button
    const printNowBtn = document.querySelector('[data-action="print-now"]');
    if (printNowBtn) {
        printNowBtn.addEventListener('click', () => {
            window.print();
        });
    }

    // 2. Close Window Button
    const closeBtn = document.querySelector('[data-action="close-window"]');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (window.opener || window.history.length <= 1) {
                window.close();
            } else {
                window.history.back();
            }
        });
    }

    // 3. Auto-print if data-auto-print is enabled
    const body = document.body;
    if (body && body.dataset.autoPrint === 'true') {
        setTimeout(() => {
            window.print();
        }, 350);
    }
});
