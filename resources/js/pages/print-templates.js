/**
 * BayoSoft ERP — Print Template Designer Engine
 * Real-time DOM-reactive preview, paper format switching (A4, A5, Thermal 80mm),
 * and AJAX configuration persistence.
 */

export function initPrintTemplatesPage() {
    const page = document.querySelector('[data-page="settings-print-templates"]');
    if (!page) return;

    const form = page.querySelector('#printTemplateForm');
    const paperSheet = page.querySelector('#paperMockupSheet');
    const resetBtn = page.querySelector('#btnResetTemplate');
    const duplicateBtn = page.querySelector('#btnDuplicateTemplate');
    const templateSelect = page.querySelector('#templateSelector');

    // Parse initial data stored in data attributes
    let templateData = null;
    let sampleData = null;

    try {
        const rawTemplate = page.getAttribute('data-initial-template');
        const rawSample = page.getAttribute('data-initial-sample');
        if (rawTemplate) templateData = JSON.parse(rawTemplate);
        if (rawSample) sampleData = JSON.parse(rawSample);
    } catch (e) {
        console.error('Error parsing print template data:', e);
    }

    if (!templateData || !templateData.settings) {
        return;
    }

    let settings = JSON.parse(JSON.stringify(templateData.settings));

    /**
     * Accordion toggle interaction
     */
    const accordionHeaders = page.querySelectorAll('.designer-accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const body = header.nextElementSibling;
            if (body && body.classList.contains('designer-accordion-body')) {
                body.classList.toggle('collapsed');
                const icon = header.querySelector('.accordion-chevron');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            }
        });
    });

    /**
     * Bind form controls to settings object
     */
    function syncFormToSettings() {
        if (!form) return;

        // General
        settings.general = settings.general || {};
        settings.general.show_logo = form.querySelector('[name="settings[general][show_logo]"]')?.checked ?? true;
        settings.general.logo_size = form.querySelector('[name="settings[general][logo_size]"]')?.value ?? 'md';
        settings.general.show_company_name = form.querySelector('[name="settings[general][show_company_name]"]')?.checked ?? true;
        settings.general.show_company_phone = form.querySelector('[name="settings[general][show_company_phone]"]')?.checked ?? true;
        settings.general.show_company_address = form.querySelector('[name="settings[general][show_company_address]"]')?.checked ?? true;
        settings.general.custom_title = form.querySelector('[name="settings[general][custom_title]"]')?.value ?? '';
        settings.general.custom_subtitle = form.querySelector('[name="settings[general][custom_subtitle]"]')?.value ?? '';
        settings.general.primary_color = form.querySelector('[name="settings[general][primary_color]"]')?.value ?? '#1e293b';
        settings.general.font_size = form.querySelector('[name="settings[general][font_size]"]')?.value ?? 'md';

        // Document Meta
        settings.document_meta = settings.document_meta || {};
        settings.document_meta.show_doc_number = form.querySelector('[name="settings[document_meta][show_doc_number]"]')?.checked ?? true;
        settings.document_meta.show_doc_date = form.querySelector('[name="settings[document_meta][show_doc_date]"]')?.checked ?? true;
        settings.document_meta.show_due_date = form.querySelector('[name="settings[document_meta][show_due_date]"]')?.checked ?? false;
        settings.document_meta.show_warehouse = form.querySelector('[name="settings[document_meta][show_warehouse]"]')?.checked ?? true;
        settings.document_meta.show_status_badge = form.querySelector('[name="settings[document_meta][show_status_badge]"]')?.checked ?? true;
        settings.document_meta.show_barcode = form.querySelector('[name="settings[document_meta][show_barcode]"]')?.checked ?? true;
        settings.document_meta.show_qr_code = form.querySelector('[name="settings[document_meta][show_qr_code]"]')?.checked ?? true;

        // Party Meta
        settings.party_meta = settings.party_meta || {};
        settings.party_meta.party_type_label = form.querySelector('[name="settings[party_meta][party_type_label]"]')?.value ?? '';
        settings.party_meta.show_party_name = form.querySelector('[name="settings[party_meta][show_party_name]"]')?.checked ?? true;
        settings.party_meta.show_party_phone = form.querySelector('[name="settings[party_meta][show_party_phone]"]')?.checked ?? true;
        settings.party_meta.show_party_address = form.querySelector('[name="settings[party_meta][show_party_address]"]')?.checked ?? true;
        settings.party_meta.show_party_tax_no = form.querySelector('[name="settings[party_meta][show_party_tax_no]"]')?.checked ?? false;

        // Table Columns
        settings.table_columns = settings.table_columns || {};
        settings.table_columns.show_index = form.querySelector('[name="settings[table_columns][show_index]"]')?.checked ?? true;
        settings.table_columns.show_sku = form.querySelector('[name="settings[table_columns][show_sku]"]')?.checked ?? true;
        settings.table_columns.show_barcode = form.querySelector('[name="settings[table_columns][show_barcode]"]')?.checked ?? false;
        settings.table_columns.show_unit = form.querySelector('[name="settings[table_columns][show_unit]"]')?.checked ?? true;
        settings.table_columns.show_warehouse = form.querySelector('[name="settings[table_columns][show_warehouse]"]')?.checked ?? true;
        settings.table_columns.show_price = form.querySelector('[name="settings[table_columns][show_price]"]')?.checked ?? true;
        settings.table_columns.show_discount = form.querySelector('[name="settings[table_columns][show_discount]"]')?.checked ?? true;
        settings.table_columns.show_tax = form.querySelector('[name="settings[table_columns][show_tax]"]')?.checked ?? false;
        settings.table_columns.show_total = form.querySelector('[name="settings[table_columns][show_total]"]')?.checked ?? true;
        settings.table_columns.show_notes = form.querySelector('[name="settings[table_columns][show_notes]"]')?.checked ?? true;
        settings.table_columns.density = form.querySelector('[name="settings[table_columns][density]"]')?.value ?? 'comfortable';

        // Financial Summary
        settings.financial_summary = settings.financial_summary || {};
        settings.financial_summary.show_subtotal = form.querySelector('[name="settings[financial_summary][show_subtotal]"]')?.checked ?? true;
        settings.financial_summary.show_discount = form.querySelector('[name="settings[financial_summary][show_discount]"]')?.checked ?? true;
        settings.financial_summary.show_shipping = form.querySelector('[name="settings[financial_summary][show_shipping]"]')?.checked ?? true;
        settings.financial_summary.show_extra_expenses = form.querySelector('[name="settings[financial_summary][show_extra_expenses]"]')?.checked ?? false;
        settings.financial_summary.show_tax = form.querySelector('[name="settings[financial_summary][show_tax]"]')?.checked ?? false;
        settings.financial_summary.show_net_total = form.querySelector('[name="settings[financial_summary][show_net_total]"]')?.checked ?? true;
        settings.financial_summary.show_paid = form.querySelector('[name="settings[financial_summary][show_paid]"]')?.checked ?? true;
        settings.financial_summary.show_remaining = form.querySelector('[name="settings[financial_summary][show_remaining]"]')?.checked ?? true;

        // Statement Options
        settings.statement_options = settings.statement_options || {};
        settings.statement_options.show_date_range = form.querySelector('[name="settings[statement_options][show_date_range]"]')?.checked ?? true;
        settings.statement_options.show_opening_balance = form.querySelector('[name="settings[statement_options][show_opening_balance]"]')?.checked ?? true;
        settings.statement_options.show_debit_credit = form.querySelector('[name="settings[statement_options][show_debit_credit]"]')?.checked ?? true;
        settings.statement_options.show_running_balance = form.querySelector('[name="settings[statement_options][show_running_balance]"]')?.checked ?? true;
        settings.statement_options.show_summary_cards = form.querySelector('[name="settings[statement_options][show_summary_cards]"]')?.checked ?? true;

        // Notes and Terms
        settings.notes_and_terms = settings.notes_and_terms || {};
        settings.notes_and_terms.show_notes = form.querySelector('[name="settings[notes_and_terms][show_notes]"]')?.checked ?? true;
        settings.notes_and_terms.notes_text = form.querySelector('[name="settings[notes_and_terms][notes_text]"]')?.value ?? '';
        settings.notes_and_terms.show_terms = form.querySelector('[name="settings[notes_and_terms][show_terms]"]')?.checked ?? true;
        settings.notes_and_terms.terms_text = form.querySelector('[name="settings[notes_and_terms][terms_text]"]')?.value ?? '';

        // Signatures
        settings.signatures = settings.signatures || {};
        settings.signatures.show_signatures = form.querySelector('[name="settings[signatures][show_signatures]"]')?.checked ?? true;
        settings.signatures.first_party_title = form.querySelector('[name="settings[signatures][first_party_title]"]')?.value ?? '';
        settings.signatures.second_party_title = form.querySelector('[name="settings[signatures][second_party_title]"]')?.value ?? '';
        settings.signatures.show_stamp_box = form.querySelector('[name="settings[signatures][show_stamp_box]"]')?.checked ?? true;
    }

    /**
     * Render the live paper mockup
     */
    function renderMockup() {
        if (!paperSheet || !sampleData) return;

        const paperFormat = templateData.paper_format || 'a4';
        paperSheet.setAttribute('data-format', paperFormat);

        // Primary color
        const primaryColor = settings.general?.primary_color || '#1e293b';
        paperSheet.style.setProperty('--mockup-primary', primaryColor);

        // General
        const logoEl = paperSheet.querySelector('.preview-logo');
        if (logoEl) {
            logoEl.style.display = settings.general?.show_logo ? 'block' : 'none';
            logoEl.className = `preview-logo doc-mockup-logo-${settings.general?.logo_size || 'md'}`;
        }

        const compNameEl = paperSheet.querySelector('.preview-company-name');
        if (compNameEl) compNameEl.style.display = settings.general?.show_company_name ? 'block' : 'none';

        const compPhoneEl = paperSheet.querySelector('.preview-company-phone');
        if (compPhoneEl) compPhoneEl.style.display = settings.general?.show_company_phone ? 'block' : 'none';

        const compAddrEl = paperSheet.querySelector('.preview-company-address');
        if (compAddrEl) compAddrEl.style.display = settings.general?.show_company_address ? 'block' : 'none';

        const titleEl = paperSheet.querySelector('.preview-doc-title');
        if (titleEl) titleEl.textContent = settings.general?.custom_title || sampleData.document?.type_label || '';

        const subtitleEl = paperSheet.querySelector('.preview-doc-subtitle');
        if (subtitleEl) {
            subtitleEl.textContent = settings.general?.custom_subtitle || '';
            subtitleEl.style.display = settings.general?.custom_subtitle ? 'block' : 'none';
        }

        // Meta
        const docNumEl = paperSheet.querySelector('.preview-doc-number');
        if (docNumEl) docNumEl.style.display = settings.document_meta?.show_doc_number ? 'inline-block' : 'none';
        const docNumRow = paperSheet.querySelector('.preview-doc-number-row');
        if (docNumRow) docNumRow.style.display = settings.document_meta?.show_doc_number ? 'flex' : 'none';

        const docDateEl = paperSheet.querySelector('.preview-doc-date');
        if (docDateEl) docDateEl.style.display = settings.document_meta?.show_doc_date ? 'inline-block' : 'none';
        const docDateRow = paperSheet.querySelector('.preview-doc-date-row');
        if (docDateRow) docDateRow.style.display = settings.document_meta?.show_doc_date ? 'flex' : 'none';

        const docDueDateEl = paperSheet.querySelector('.preview-doc-due-date');
        if (docDueDateEl) docDueDateEl.style.display = settings.document_meta?.show_due_date ? 'inline-block' : 'none';

        const docWhEl = paperSheet.querySelector('.preview-doc-warehouse');
        if (docWhEl) docWhEl.style.display = settings.document_meta?.show_warehouse ? 'inline-block' : 'none';

        const docBadgeEl = paperSheet.querySelector('.preview-doc-badge');
        if (docBadgeEl) docBadgeEl.style.display = settings.document_meta?.show_status_badge ? 'inline-block' : 'none';

        const docBarcodeEl = paperSheet.querySelector('.preview-doc-barcode');
        if (docBarcodeEl) docBarcodeEl.style.display = settings.document_meta?.show_barcode ? 'block' : 'none';

        const docQrEl = paperSheet.querySelector('.preview-doc-qr');
        if (docQrEl) docQrEl.style.display = settings.document_meta?.show_qr_code ? 'block' : 'none';

        // Party
        const partyLabelEl = paperSheet.querySelector('.preview-party-label');
        if (partyLabelEl) {
            const rawLabel = settings.party_meta?.party_type_label || 'الطرف المقابل';
            partyLabelEl.textContent = partyLabelEl.textContent.includes(':') ? `${rawLabel} :` : rawLabel;
        }

        const partyNameEl = paperSheet.querySelector('.preview-party-name');
        if (partyNameEl) partyNameEl.style.display = settings.party_meta?.show_party_name ? 'block' : 'none';
        const partyNameRow = paperSheet.querySelector('.preview-party-name-row');
        if (partyNameRow) partyNameRow.style.display = settings.party_meta?.show_party_name ? 'flex' : 'none';

        const partyPhoneEl = paperSheet.querySelector('.preview-party-phone');
        if (partyPhoneEl) partyPhoneEl.style.display = settings.party_meta?.show_party_phone ? 'block' : 'none';
        const partyPhoneRow = paperSheet.querySelector('.preview-party-phone-row');
        if (partyPhoneRow) partyPhoneRow.style.display = settings.party_meta?.show_party_phone ? 'flex' : 'none';

        const partyAddrEl = paperSheet.querySelector('.preview-party-address');
        if (partyAddrEl) partyAddrEl.style.display = settings.party_meta?.show_party_address ? 'block' : 'none';
        const partyAddrRow = paperSheet.querySelector('.preview-party-address-row');
        if (partyAddrRow) partyAddrRow.style.display = settings.party_meta?.show_party_address ? 'flex' : 'none';

        const partyTaxEl = paperSheet.querySelector('.preview-party-tax-no');
        if (partyTaxEl) partyTaxEl.style.display = settings.party_meta?.show_party_tax_no ? 'block' : 'none';

        // Table Columns
        const table = paperSheet.querySelector('.doc-mockup-table');
        if (table) {
            table.classList.toggle('density-compact', settings.table_columns?.density === 'compact');
            
            paperSheet.querySelectorAll('.col-index').forEach(el => el.style.display = settings.table_columns?.show_index ? '' : 'none');
            paperSheet.querySelectorAll('.col-sku').forEach(el => el.style.display = settings.table_columns?.show_sku ? '' : 'none');
            paperSheet.querySelectorAll('.col-barcode').forEach(el => el.style.display = settings.table_columns?.show_barcode ? '' : 'none');
            paperSheet.querySelectorAll('.col-unit').forEach(el => el.style.display = settings.table_columns?.show_unit ? '' : 'none');
            paperSheet.querySelectorAll('.col-warehouse, .col-wh').forEach(el => el.style.display = settings.table_columns?.show_warehouse ? '' : 'none');
            paperSheet.querySelectorAll('.col-price').forEach(el => el.style.display = settings.table_columns?.show_price ? '' : 'none');
            paperSheet.querySelectorAll('.col-discount').forEach(el => el.style.display = settings.table_columns?.show_discount ? '' : 'none');
            paperSheet.querySelectorAll('.col-tax').forEach(el => el.style.display = settings.table_columns?.show_tax ? '' : 'none');
            paperSheet.querySelectorAll('.col-total').forEach(el => el.style.display = settings.table_columns?.show_total ? '' : 'none');
            paperSheet.querySelectorAll('.col-notes').forEach(el => el.style.display = settings.table_columns?.show_notes ? '' : 'none');
        }

        // Financial Summary
        paperSheet.querySelectorAll('.row-subtotal').forEach(el => el.style.display = settings.financial_summary?.show_subtotal ? '' : 'none');
        paperSheet.querySelectorAll('.row-discount').forEach(el => el.style.display = settings.financial_summary?.show_discount ? '' : 'none');
        paperSheet.querySelectorAll('.row-shipping').forEach(el => el.style.display = settings.financial_summary?.show_shipping ? '' : 'none');
        paperSheet.querySelectorAll('.row-extra-expenses').forEach(el => el.style.display = settings.financial_summary?.show_extra_expenses ? '' : 'none');
        paperSheet.querySelectorAll('.row-tax').forEach(el => el.style.display = settings.financial_summary?.show_tax ? '' : 'none');
        paperSheet.querySelectorAll('.row-net-total').forEach(el => el.style.display = settings.financial_summary?.show_net_total ? '' : 'none');
        paperSheet.querySelectorAll('.row-paid').forEach(el => el.style.display = settings.financial_summary?.show_paid ? '' : 'none');
        paperSheet.querySelectorAll('.row-remaining').forEach(el => el.style.display = settings.financial_summary?.show_remaining ? '' : 'none');

        // Notes and Terms
        const notesBox = paperSheet.querySelector('.preview-notes-box');
        if (notesBox) {
            notesBox.style.display = settings.notes_and_terms?.show_notes ? 'block' : 'none';
            const notesTextEl = notesBox.querySelector('.notes-content');
            if (notesTextEl) notesTextEl.textContent = settings.notes_and_terms?.notes_text || '';
        }

        const termsBox = paperSheet.querySelector('.preview-terms-box');
        if (termsBox) {
            termsBox.style.display = settings.notes_and_terms?.show_terms ? 'block' : 'none';
            const termsTextEl = termsBox.querySelector('.terms-content');
            if (termsTextEl) termsTextEl.textContent = settings.notes_and_terms?.terms_text || '';
        }

        // Signatures
        const sigContainer = paperSheet.querySelector('.doc-mockup-signatures');
        if (sigContainer) {
            sigContainer.style.display = settings.signatures?.show_signatures ? 'flex' : 'none';
            const firstPartyLabel = sigContainer.querySelector('.first-party-title');
            if (firstPartyLabel) firstPartyLabel.textContent = settings.signatures?.first_party_title || 'الطرف الأول';

            const secondPartyLabel = sigContainer.querySelector('.second-party-title');
            if (secondPartyLabel) secondPartyLabel.textContent = settings.signatures?.second_party_title || 'المسؤول';

            const stampBox = sigContainer.querySelector('.doc-mockup-stamp-box');
            if (stampBox) stampBox.style.display = settings.signatures?.show_stamp_box ? 'flex' : 'none';
        }
    }

    // Attach listener to all inputs in the form
    if (form) {
        form.addEventListener('input', () => {
            syncFormToSettings();
            renderMockup();
        });

        form.addEventListener('change', () => {
            syncFormToSettings();
            renderMockup();
        });

        // Form submit via AJAX
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            syncFormToSettings();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'جاري الحفظ...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        name: form.querySelector('[name="template_name"]')?.value || templateData.name,
                        is_default: form.querySelector('[name="template_is_default"]')?.checked ?? templateData.is_default,
                        settings: settings,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message || 'تم حفظ القالب بنجاح', 'success');
                } else {
                    showToast(result.message || 'حدث خطأ أثناء حفظ القالب', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('فشل الاتصال بالخادم أثناء الحفظ', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // Reset button handler
    if (resetBtn) {
        resetBtn.addEventListener('click', async () => {
            if (!confirm('هل أنت متأكد من استعادة الإعدادات الافتراضية لهذا القالب؟ ستفقد التعديلات المخصصة.')) {
                return;
            }

            const resetUrl = resetBtn.getAttribute('data-reset-url');
            if (!resetUrl) return;

            try {
                const response = await fetch(resetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();
                if (result.success && result.template) {
                    showToast(result.message, 'success');
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                showToast('تعذر استعادة الإعدادات الافتراضية', 'error');
            }
        });
    }

    // Template selector change
    if (templateSelect) {
        templateSelect.addEventListener('change', () => {
            const selectedId = templateSelect.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('template_id', selectedId);
            window.location.href = currentUrl.toString();
        });
    }

    // Initial render
    renderMockup();
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `erp-toast erp-toast-${type}`;
    toast.innerHTML = `<span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3500);
}
