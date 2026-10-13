/**
 * Kayan ERP — Common Global UI Helpers
 * Auto-submit selects, confirmation dialogs, modal escape listener
 */

export function initCommonUI() {
    // 1. Auto-submit on change
    document.addEventListener('change', (e) => {
        if (e.target.matches('[data-action="auto-submit"]')) {
            e.target.form?.submit();
        }
    });

    // 2. Data confirmation dialog
    document.addEventListener('submit', (e) => {
        const form = e.target;
        const confirmMsg = form.getAttribute('data-confirm');
        if (confirmMsg) {
            if (!window.confirm(confirmMsg)) {
                e.preventDefault();
            }
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-confirm]');
        if (btn && btn.type !== 'submit') {
            const confirmMsg = btn.getAttribute('data-confirm');
            if (confirmMsg) {
                if (!window.confirm(confirmMsg)) {
                    e.preventDefault();
                }
            }
        }
    });
}
