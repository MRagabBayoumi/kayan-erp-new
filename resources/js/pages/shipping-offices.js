/**
 * Kayan ERP — Shipping Offices Page Client Logic
 * Add/Edit shipping office modal management, pre-population, and keyboard dismiss
 */

export function initShippingOfficesPage() {
    const page = document.querySelector('[data-page="shipping-offices"]');
    if (!page) return;

    const addModal = page.querySelector('#addShippingModal');
    const editModal = page.querySelector('#editShippingModal');

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

    function openEditModal(office) {
        const form = page.querySelector('#editShippingForm');
        if (!form) return;

        form.action = `/shipping-offices/${office.id}`;

        const setVal = (id, val) => {
            const el = page.querySelector(id);
            if (el) el.value = val ?? '';
        };

        setVal('#edit_ship_name', office.name);
        setVal('#edit_ship_shipping_type', office.shipping_type || 'domestic_egypt');
        setVal('#edit_ship_contact_person', office.contact_person);
        setVal('#edit_ship_phone', office.phone);
        setVal('#edit_ship_phone_alt', office.phone_alt);
        setVal('#edit_ship_rate_per_cbm_or_kg', office.rate_per_cbm_or_kg || '');
        setVal('#edit_ship_address', office.address);
        setVal('#edit_ship_notes', office.notes);

        const activeCb = page.querySelector('#edit_ship_is_active');
        if (activeCb) {
            activeCb.checked = Boolean(office.is_active);
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

    page.querySelectorAll('[data-action="open-add-shipping"]').forEach(btn => {
        btn.addEventListener('click', openAddModal);
    });

    page.querySelectorAll('[data-action="close-add-shipping"]').forEach(btn => {
        btn.addEventListener('click', closeAddModal);
    });

    page.querySelectorAll('[data-action="open-edit-shipping"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const raw = btn.dataset.shipping;
            if (raw) {
                try {
                    const data = JSON.parse(raw);
                    openEditModal(data);
                } catch (e) {
                    console.error('Failed to parse shipping office data JSON', e);
                }
            }
        });
    });

    page.querySelectorAll('[data-action="close-edit-shipping"]').forEach(btn => {
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
