/**
 * Kayan ERP — Suppliers Page Client Logic
 * Add/Edit supplier modal management, pre-population, and keyboard dismiss
 */

export function initSuppliersPage() {
    const page = document.querySelector('[data-page="suppliers"]');
    if (!page) return;

    const addModal = page.querySelector('#addSupplierModal');
    const editModal = page.querySelector('#editSupplierModal');

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

    function openEditModal(supplier) {
        const form = page.querySelector('#editSupplierForm');
        if (!form) return;

        form.action = `/suppliers/${supplier.id}`;

        const setVal = (id, val) => {
            const el = page.querySelector(id);
            if (el) el.value = val ?? '';
        };

        setVal('#edit_sup_name', supplier.name);
        setVal('#edit_sup_supplier_type', supplier.supplier_type || 'china_factory');
        setVal('#edit_sup_contact_person', supplier.contact_person);
        setVal('#edit_sup_wechat_id', supplier.wechat_id);
        setVal('#edit_sup_phone', supplier.phone);
        setVal('#edit_sup_email', supplier.email);
        setVal('#edit_sup_country', supplier.country || 'China');
        setVal('#edit_sup_city', supplier.city);
        setVal('#edit_sup_currency', supplier.currency || 'CNY');
        setVal('#edit_sup_current_balance', supplier.current_balance || 0);
        setVal('#edit_sup_address', supplier.address);
        setVal('#edit_sup_notes', supplier.notes);

        const activeCb = page.querySelector('#edit_sup_is_active');
        if (activeCb) {
            activeCb.checked = Boolean(supplier.is_active);
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

    page.querySelectorAll('[data-action="open-add-supplier"]').forEach(btn => {
        btn.addEventListener('click', openAddModal);
    });

    page.querySelectorAll('[data-action="close-add-supplier"]').forEach(btn => {
        btn.addEventListener('click', closeAddModal);
    });

    page.querySelectorAll('[data-action="open-edit-supplier"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const raw = btn.dataset.supplier;
            if (raw) {
                try {
                    const data = JSON.parse(raw);
                    openEditModal(data);
                } catch (e) {
                    console.error('Failed to parse supplier data JSON', e);
                }
            }
        });
    });

    page.querySelectorAll('[data-action="close-edit-supplier"]').forEach(btn => {
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
