/**
 * Kayan ERP — Customers Page Client Logic
 * Add/Edit customer modal management, form pre-population, and keyboard dismiss
 */

export function initCustomersPage() {
    const page = document.querySelector('[data-page="customers"]');
    if (!page) return;

    const addModal = page.querySelector('#addCustomerModal');
    const editModal = page.querySelector('#editCustomerModal');

    function openAddModal() {
        if (addModal) {
            addModal.classList.add('open');
            addModal.style.display = 'flex';
        }
    }

    function closeAddModal() {
        if (addModal) {
            addModal.classList.remove('open');
            addModal.style.display = 'none';
        }
    }

    function openEditModal(customer) {
        const form = page.querySelector('#editCustomerForm');
        if (!form) return;

        form.action = `/customers/${customer.id}`;

        const setVal = (id, val) => {
            const el = page.querySelector(id);
            if (el) el.value = val ?? '';
        };

        setVal('#edit_cust_name', customer.name);
        setVal('#edit_cust_company_name', customer.company_name);
        setVal('#edit_cust_phone', customer.phone);
        setVal('#edit_cust_phone_alt', customer.phone_alt);
        setVal('#edit_cust_customer_type', customer.customer_type || 'wholesale');
        setVal('#edit_cust_pricing_tier', customer.pricing_tier || 'wholesale');
        setVal('#edit_cust_city', customer.city);
        setVal('#edit_cust_address', customer.address);
        setVal('#edit_cust_credit_limit', customer.credit_limit || 0);
        setVal('#edit_cust_current_balance', customer.current_balance || 0);
        setVal('#edit_cust_notes', customer.notes);

        const activeCb = page.querySelector('#edit_cust_is_active');
        if (activeCb) {
            activeCb.checked = Boolean(customer.is_active);
        }

        if (editModal) {
            editModal.classList.add('open');
            editModal.style.display = 'flex';
        }
    }

    function closeEditModal() {
        if (editModal) {
            editModal.classList.remove('open');
            editModal.style.display = 'none';
        }
    }

    page.querySelectorAll('[data-action="open-add-customer"]').forEach(btn => {
        btn.addEventListener('click', openAddModal);
    });

    page.querySelectorAll('[data-action="close-add-customer"]').forEach(btn => {
        btn.addEventListener('click', closeAddModal);
    });

    page.querySelectorAll('[data-action="open-edit-customer"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const raw = btn.dataset.customer;
            if (raw) {
                try {
                    const data = JSON.parse(raw);
                    openEditModal(data);
                } catch (e) {
                    console.error('Failed to parse customer data JSON', e);
                }
            }
        });
    });

    page.querySelectorAll('[data-action="close-edit-customer"]').forEach(btn => {
        btn.addEventListener('click', closeEditModal);
    });

    [addModal, editModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('open');
                    modal.style.display = 'none';
                }
            });
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
}
