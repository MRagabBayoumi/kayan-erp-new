/**
 * Kayan ERP — Print Label Page Module
 * Direct thermal printing (single & batch), auto-print triggering, print controls
 */

export function initPrintLabelPage() {
    const isAutoPrint = document.body.dataset.autoPrint === 'true';
    const widthMm = document.body.dataset.widthMm;
    const heightMm = document.body.dataset.heightMm;

    if (widthMm && heightMm) {
        document.documentElement.style.setProperty('--label-width', `${widthMm}mm`);
        document.documentElement.style.setProperty('--label-height', `${heightMm}mm`);

        const pageStyle = document.createElement('style');
        pageStyle.id = 'dynamicPageSize';
        pageStyle.textContent = `@page { size: ${widthMm}mm ${heightMm}mm !important; margin: 0mm !important; } .thermal-label, .thermal-label svg { width: ${widthMm}mm !important; height: ${heightMm}mm !important; }`;
        document.head.appendChild(pageStyle);
    }

    function showToast(msg) {
        const toast = document.getElementById('printNotificationToast');
        const msgEl = document.getElementById('printToastMessage');
        if (toast && msgEl) {
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            toast.style.display = 'flex';
            clearTimeout(window.__labelToastTimeout);
            window.__labelToastTimeout = setTimeout(() => {
                toast.classList.add('hidden');
                toast.style.display = 'none';
            }, 3500);
        }
    }

    async function triggerDirectPrint(btn) {
        if (!btn || btn.disabled) return;
        const url = btn.dataset.url;
        const isBatch = btn.dataset.isBatch === '1';
        const quantity = parseInt(btn.dataset.quantity || '1', 10);
        const size = btn.dataset.size || '38x25';
        
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>جاري الإرسال للطابعة...</span>';
        showToast('جاري إرسال الملصق مباشرة إلى طابعة الباركود...');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let payload = {};
            if (isBatch) {
                const items = JSON.parse(btn.dataset.items || '[]');
                payload = { items: items, size: size };
            } else {
                payload = { quantity: quantity, size: size };
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (response.ok && data.success) {
                showToast(`تم إرسال الملصقات بنجاح إلى (${data.printer || 'طابعة الباركود'}).`);
            } else {
                showToast(`تعذر إرسال أمر الطباعة: ${data.message || 'خطأ غير محدد'}`);
            }
        } catch (err) {
            showToast('حدث خطأ في الاتصال بالخادم أثناء إرسال أمر الطباعة.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    // Direct thermal print button via Python engine
    const directBtn = document.getElementById('directPrintButton') || document.querySelector('[data-action="direct-thermal-print"]');
    if (directBtn) {
        directBtn.addEventListener('click', () => triggerDirectPrint(directBtn));
    }

    // Print button (browser manual fallback)
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

    // Auto-print directly to thermal printer if requested
    if (isAutoPrint && directBtn) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                triggerDirectPrint(directBtn);
            }, 300);
        });
    }

    // Intercept Ctrl+P to trigger direct thermal print instead of browser dialog
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            if (directBtn) {
                triggerDirectPrint(directBtn);
            }
        }
    });
}

// Auto initialize if on print-label page
if (document.querySelector('.roll-stage')) {
    initPrintLabelPage();
}
