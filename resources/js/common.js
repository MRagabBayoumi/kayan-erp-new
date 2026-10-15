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

    // 3. Password visibility toggle
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-action="toggle-password"]');
        if (!toggleBtn) return;

        const targetId = toggleBtn.getAttribute('data-target');
        const input = targetId ? document.getElementById(targetId) : null;
        if (input) {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            toggleBtn.classList.toggle('is-visible', isPassword);
        }
    });

    // 4. Toggle Module Permissions in Roles Matrix
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="toggle-module-permissions"]');
        if (!btn) return;

        const moduleCard = btn.closest('.role-matrix-module');
        if (!moduleCard) return;

        const checkboxes = moduleCard.querySelectorAll('input[type="checkbox"][name="permissions[]"]');
        if (!checkboxes.length) return;

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });

        const span = btn.querySelector('span');
        if (span) {
            span.textContent = allChecked ? 'تحديد الكل' : 'إلغاء التحديد';
        }
    });
}

