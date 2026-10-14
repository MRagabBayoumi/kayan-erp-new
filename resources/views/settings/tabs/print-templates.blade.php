<div data-page="settings-print-templates" 
     data-initial-template="{{ json_encode($currentTemplate) }}" 
     data-initial-sample="{{ json_encode($sampleData) }}">

    <!-- Split View: Controls on Right, Live Simulator on Left -->
    <div class="print-designer-layout">

        <!-- Controls Column -->
        <div class="designer-controls-card">
            
            <!-- Document Type Selector -->
            <div class="designer-selector-group">
                <label class="block text-xs font-semibold text-slate-500 mb-2">نوع المستند</label>
                <div class="designer-nav-pills">
                    @foreach($docTypeLabels as $typeKey => $typeLabel)
                        <a href="{{ route('settings.index', ['tab' => 'print_templates', 'doc_type' => $typeKey, 'paper_format' => $currentPaperFormat]) }}" 
                           class="designer-pill-btn {{ $currentDocType === $typeKey ? 'active' : '' }}">
                            <span>{{ $typeLabel }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Paper Format Selector (A4, A5, Thermal 80mm) -->
            <div class="designer-selector-group">
                <label class="block text-xs font-semibold text-slate-500 mb-2">مقاس الورق ونوع الطابعة</label>
                <div class="designer-format-pills">
                    <a href="{{ route('settings.index', ['tab' => 'print_templates', 'doc_type' => $currentDocType, 'paper_format' => 'a4']) }}" 
                       class="designer-format-btn {{ $currentPaperFormat === 'a4' ? 'active' : '' }}">
                        <span>A4 قياسي</span>
                        <span class="format-subtext">210 × 297 مم</span>
                    </a>
                    <a href="{{ route('settings.index', ['tab' => 'print_templates', 'doc_type' => $currentDocType, 'paper_format' => 'a5']) }}" 
                       class="designer-format-btn {{ $currentPaperFormat === 'a5' ? 'active' : '' }}">
                        <span>A5 قياسي</span>
                        <span class="format-subtext">148 × 210 مم</span>
                    </a>
                    <a href="{{ route('settings.index', ['tab' => 'print_templates', 'doc_type' => $currentDocType, 'paper_format' => 'thermal_80mm']) }}" 
                       class="designer-format-btn {{ $currentPaperFormat === 'thermal_80mm' ? 'active' : '' }}">
                        <span>حراري 80 مم</span>
                        <span class="format-subtext">شريط إيصالات</span>
                    </a>
                </div>
            </div>

            <!-- Template Select / Switcher -->
            @if($templatesList->count() > 1)
                <div class="designer-selector-group bg-slate-50">
                    <label for="templateSelector" class="block text-xs font-semibold text-slate-600 mb-1.5">القالب النشط</label>
                    <div class="flex gap-2">
                        <select id="templateSelector" class="erp-input flex-1 text-xs">
                            @foreach($templatesList as $t)
                                <option value="{{ $t->id }}" {{ $currentTemplate->id === $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} {{ $t->is_default ? '(الافتراضي)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <!-- Template Settings Form -->
            <form id="printTemplateForm" action="{{ route('settings.print-templates.update', $currentTemplate->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Template Metadata Header -->
                <div class="p-4 border-b border-slate-200 bg-white">
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">اسم القالب</label>
                        <input type="text" name="template_name" value="{{ $currentTemplate->name }}" class="erp-input text-xs" required />
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700">
                        <input type="checkbox" name="template_is_default" value="1" {{ $currentTemplate->is_default ? 'checked' : '' }} class="rounded border-slate-300 text-slate-800" />
                        <span>تعيين كقالب افتراضي لهذا المقاس ونوع المستند</span>
                    </label>
                </div>

                <!-- Accordion 1: Branding & Identity -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="building-2" size="16" />
                            <span>الهوية والشعار وبيانات المنشأة</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div class="designer-toggle-row">
                            <label for="sw_show_logo">إظهار شعار الشركة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_show_logo" name="settings[general][show_logo]" value="1" {{ $currentTemplate->getSetting('general.show_logo') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <label class="block text-slate-600 mb-1">حجم الشعار</label>
                                <select name="settings[general][logo_size]" class="erp-input text-xs">
                                    <option value="sm" {{ $currentTemplate->getSetting('general.logo_size') === 'sm' ? 'selected' : '' }}>صغير (25 مم)</option>
                                    <option value="md" {{ $currentTemplate->getSetting('general.logo_size') === 'md' ? 'selected' : '' }}>متوسط (35 مم)</option>
                                    <option value="lg" {{ $currentTemplate->getSetting('general.logo_size') === 'lg' ? 'selected' : '' }}>كبير (45 مم)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-1">حجم الخط العام</label>
                                <select name="settings[general][font_size]" class="erp-input text-xs">
                                    <option value="sm" {{ $currentTemplate->getSetting('general.font_size') === 'sm' ? 'selected' : '' }}>صغير (Compact)</option>
                                    <option value="md" {{ $currentTemplate->getSetting('general.font_size') === 'md' ? 'selected' : '' }}>متوسط (Normal)</option>
                                    <option value="lg" {{ $currentTemplate->getSetting('general.font_size') === 'lg' ? 'selected' : '' }}>كبير (Large)</option>
                                </select>
                            </div>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_show_company_name">إظهار اسم الشركة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_show_company_name" name="settings[general][show_company_name]" value="1" {{ $currentTemplate->getSetting('general.show_company_name') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_show_company_phone">إظهار رقم الهاتف</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_show_company_phone" name="settings[general][show_company_phone]" value="1" {{ $currentTemplate->getSetting('general.show_company_phone') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_show_company_address">إظهار العنوان والمقر</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_show_company_address" name="settings[general][show_company_address]" value="1" {{ $currentTemplate->getSetting('general.show_company_address') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">عنوان المستند المخصص</label>
                            <input type="text" name="settings[general][custom_title]" value="{{ $currentTemplate->getSetting('general.custom_title') }}" class="erp-input text-xs" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">العنوان الفرعي</label>
                            <input type="text" name="settings[general][custom_subtitle]" value="{{ $currentTemplate->getSetting('general.custom_subtitle') }}" class="erp-input text-xs" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">اللون الرئيسي المميز للقالب (Hex)</label>
                            <input type="text" name="settings[general][primary_color]" value="{{ $currentTemplate->getSetting('general.primary_color') }}" class="erp-input text-xs" placeholder="#1e293b" />
                        </div>
                    </div>
                </div>

                <!-- Accordion 2: Document Meta & Barcode -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="file-text" size="16" />
                            <span>بيانات المستند والباركود</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div class="designer-toggle-row">
                            <label for="sw_doc_num">إظهار رقم المستند / الفاتورة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_num" name="settings[document_meta][show_doc_number]" value="1" {{ $currentTemplate->getSetting('document_meta.show_doc_number') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_doc_date">إظهار تاريخ التحرير</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_date" name="settings[document_meta][show_doc_date]" value="1" {{ $currentTemplate->getSetting('document_meta.show_doc_date') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_doc_due">إظهار تاريخ الاستحقاق</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_due" name="settings[document_meta][show_due_date]" value="1" {{ $currentTemplate->getSetting('document_meta.show_due_date') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_doc_wh">إظهار المستودع / الفرع</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_wh" name="settings[document_meta][show_warehouse]" value="1" {{ $currentTemplate->getSetting('document_meta.show_warehouse') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_doc_badge">إظهار شارة الحالة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_badge" name="settings[document_meta][show_status_badge]" value="1" {{ $currentTemplate->getSetting('document_meta.show_status_badge') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_doc_barcode">إظهار باركود المستند (Code 128)</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_barcode" name="settings[document_meta][show_barcode]" value="1" {{ $currentTemplate->getSetting('document_meta.show_barcode') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_doc_qr">إظهار رمز الاستجابة السريعة (QR Code)</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_doc_qr" name="settings[document_meta][show_qr_code]" value="1" {{ $currentTemplate->getSetting('document_meta.show_qr_code') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Accordion 3: Counterparty Info -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="users" size="16" />
                            <span>بيانات الطرف المقابل (العميل / المورد)</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">مسمى الطرف (العميل / المورد / الجهة)</label>
                            <input type="text" name="settings[party_meta][party_type_label]" value="{{ $currentTemplate->getSetting('party_meta.party_type_label') }}" class="erp-input text-xs" />
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_party_name">إظهار اسم الطرف</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_party_name" name="settings[party_meta][show_party_name]" value="1" {{ $currentTemplate->getSetting('party_meta.show_party_name') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_party_phone">إظهار الهاتف والتواصل</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_party_phone" name="settings[party_meta][show_party_phone]" value="1" {{ $currentTemplate->getSetting('party_meta.show_party_phone') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_party_address">إظهار العنوان</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_party_address" name="settings[party_meta][show_party_address]" value="1" {{ $currentTemplate->getSetting('party_meta.show_party_address') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_party_tax">إظهار الرقم الضريبي / السجل التجاري</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_party_tax" name="settings[party_meta][show_party_tax_no]" value="1" {{ $currentTemplate->getSetting('party_meta.show_party_tax_no') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Accordion 4: Table Columns -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="table" size="16" />
                            <span>أعمدة جدول البنود والأسعار</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">كثافة الجدول والمسافات</label>
                            <select name="settings[table_columns][density]" class="erp-input text-xs">
                                <option value="comfortable" {{ $currentTemplate->getSetting('table_columns.density') === 'comfortable' ? 'selected' : '' }}>مريح (Comfortable)</option>
                                <option value="compact" {{ $currentTemplate->getSetting('table_columns.density') === 'compact' ? 'selected' : '' }}>مضغوط (Compact - للأوراق الصغيرة والحرارية)</option>
                            </select>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_idx">مسلسل (#)</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_idx" name="settings[table_columns][show_index]" value="1" {{ $currentTemplate->getSetting('table_columns.show_index') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_sku">كود الصنف (SKU)</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_sku" name="settings[table_columns][show_sku]" value="1" {{ $currentTemplate->getSetting('table_columns.show_sku') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_unit">الوحدة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_unit" name="settings[table_columns][show_unit]" value="1" {{ $currentTemplate->getSetting('table_columns.show_unit') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_price">سعر الوحدة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_price" name="settings[table_columns][show_price]" value="1" {{ $currentTemplate->getSetting('table_columns.show_price') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_discount">الخصم</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_discount" name="settings[table_columns][show_discount]" value="1" {{ $currentTemplate->getSetting('table_columns.show_discount') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_tax">الضريبة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_tax" name="settings[table_columns][show_tax]" value="1" {{ $currentTemplate->getSetting('table_columns.show_tax') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_total">الإجمالي</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_total" name="settings[table_columns][show_total]" value="1" {{ $currentTemplate->getSetting('table_columns.show_total') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_col_notes">ملاحظات البند</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_col_notes" name="settings[table_columns][show_notes]" value="1" {{ $currentTemplate->getSetting('table_columns.show_notes') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Accordion 5: Financial Summary -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="dollar-sign" size="16" />
                            <span>الملخص المالي ومجاميع الفاتورة</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div class="designer-toggle-row">
                            <label for="sw_fin_subtotal">المجموع الفرعي</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_subtotal" name="settings[financial_summary][show_subtotal]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_subtotal') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_fin_discount">الخصم الإجمالي</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_discount" name="settings[financial_summary][show_discount]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_discount') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_fin_shipping">تكلفة الشحن والنقل</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_shipping" name="settings[financial_summary][show_shipping]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_shipping') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_fin_extra">مصاريف إضافية (خاص بالمشتريات)</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_extra" name="settings[financial_summary][show_extra_expenses]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_extra_expenses') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_fin_net">الصافي الإجمالي النهائي</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_net" name="settings[financial_summary][show_net_total]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_net_total') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_fin_paid">المدفوع نقداً</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_paid" name="settings[financial_summary][show_paid]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_paid') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_fin_remaining">المتبقي / الآجل</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_fin_remaining" name="settings[financial_summary][show_remaining]" value="1" {{ $currentTemplate->getSetting('financial_summary.show_remaining') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Accordion 6: Notes & Terms -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="help-circle" size="16" />
                            <span>الملاحظات والشروط والأحكام</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div class="designer-toggle-row">
                            <label for="sw_notes">إظهار صندوق الملاحظات</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_notes" name="settings[notes_and_terms][show_notes]" value="1" {{ $currentTemplate->getSetting('notes_and_terms.show_notes') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">نص الملاحظة الافتراضية</label>
                            <textarea name="settings[notes_and_terms][notes_text]" rows="2" class="erp-input text-xs">{{ $currentTemplate->getSetting('notes_and_terms.notes_text') }}</textarea>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_terms">إظهار الشروط والأحكام القانونية</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_terms" name="settings[notes_and_terms][show_terms]" value="1" {{ $currentTemplate->getSetting('notes_and_terms.show_terms') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">نص الشروط والأحكام</label>
                            <textarea name="settings[notes_and_terms][terms_text]" rows="3" class="erp-input text-xs">{{ $currentTemplate->getSetting('notes_and_terms.terms_text') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Accordion 7: Signatures & Stamp -->
                <div class="designer-accordion-section">
                    <button type="button" class="designer-accordion-header">
                        <span class="flex items-center gap-2">
                            <x-icon name="check-circle-2" size="16" />
                            <span>التوقيعات والختم الإداري</span>
                        </span>
                        <x-icon name="chevron-down" size="16" class="accordion-chevron transition-transform" />
                    </button>
                    <div class="designer-accordion-body">
                        <div class="designer-toggle-row">
                            <label for="sw_sigs">إظهار خانات التوقيعات</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_sigs" name="settings[signatures][show_signatures]" value="1" {{ $currentTemplate->getSetting('signatures.show_signatures') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">مسمى الطرف الأول</label>
                                <input type="text" name="settings[signatures][first_party_title]" value="{{ $currentTemplate->getSetting('signatures.first_party_title') }}" class="erp-input text-xs" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">مسمى الطرف الثاني</label>
                                <input type="text" name="settings[signatures][second_party_title]" value="{{ $currentTemplate->getSetting('signatures.second_party_title') }}" class="erp-input text-xs" />
                            </div>
                        </div>
                        <div class="designer-toggle-row">
                            <label for="sw_stamp">إظهار موضع ختم المنشأة</label>
                            <label class="designer-switch">
                                <input type="checkbox" id="sw_stamp" name="settings[signatures][show_stamp_box]" value="1" {{ $currentTemplate->getSetting('signatures.show_stamp_box') ? 'checked' : '' }}>
                                <span class="designer-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        <x-icon name="save" size="16" />
                        <span>حفظ إعدادات القالب</span>
                    </button>

                    <button type="button" id="btnResetTemplate" data-reset-url="{{ route('settings.print-templates.reset', $currentTemplate->id) }}" class="erp-btn erp-btn-secondary text-xs">
                        <x-icon name="rotate-ccw" size="14" />
                        <span>استعادة الافتراضي</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview Simulator Column -->
        <div class="designer-preview-column">
            <div class="designer-preview-toolbar">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-700">معاينة تفاعلية حية (Live Preview)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="erp-badge erp-badge-neutral text-xs">
                        {{ $currentPaperFormat === 'a4' ? 'ورق A4' : ($currentPaperFormat === 'a5' ? 'ورق A5' : 'حراري 80 مم') }}
                    </span>
                </div>
            </div>

            <!-- Paper Viewport Container -->
            <div class="preview-viewport-container">
                <div id="paperMockupSheet" class="paper-sheet" data-format="{{ $currentPaperFormat }}">
                    <!-- Sample Watermark Ribbon -->
                    <div class="paper-watermark-ribbon">
                        {{ $sampleData['watermark'] ?? 'معاينة تجريبية' }}
                    </div>

                    <!-- Mockup Header -->
                    <div class="doc-mockup-header">
                        <div class="preview-header-brand">
                            <div class="preview-logo doc-mockup-logo-md mb-2">
                                <div class="w-12 h-12 rounded bg-slate-800 text-white font-bold flex items-center justify-center text-sm">
                                    LOGO
                                </div>
                            </div>
                            <div class="preview-company-name font-bold text-base text-slate-800">
                                {{ $sampleData['company']['name'] ?? 'شركة كيان للتجارة والتوزيع' }}
                            </div>
                            <div class="preview-company-phone text-xs text-slate-600">
                                هاتف: {{ $sampleData['company']['phone'] ?? '01012345678' }}
                            </div>
                            <div class="preview-company-address text-xs text-slate-500">
                                {{ $sampleData['company']['address'] ?? 'المنصورة، مصر' }}
                            </div>
                        </div>

                        <div class="preview-header-doc text-left">
                            <div class="preview-doc-title doc-mockup-title">
                                {{ $currentTemplate->getSetting('general.custom_title', $sampleData['document']['type_label']) }}
                            </div>
                            <div class="preview-doc-subtitle text-xs text-slate-500">
                                {{ $currentTemplate->getSetting('general.custom_subtitle', '') }}
                            </div>
                        </div>
                    </div>

                    <!-- Mockup Document Metadata -->
                    <div class="doc-mockup-meta-grid">
                        <div>
                            <span class="preview-doc-number font-semibold text-slate-800 ml-3">
                                رقم المستند: {{ $sampleData['document']['number'] ?? 'INV-001' }}
                            </span>
                            <span class="preview-doc-date text-slate-600 ml-3">
                                التاريخ: {{ $sampleData['document']['date'] ?? '2026-10-14' }}
                            </span>
                            <span class="preview-doc-due-date text-slate-500 ml-3">
                                الاستحقاق: {{ $sampleData['document']['due_date'] ?? '2026-10-21' }}
                            </span>
                        </div>
                        <div class="text-left">
                            <span class="preview-doc-warehouse text-slate-600 ml-3">
                                المستودع: {{ $sampleData['document']['warehouse'] ?? 'المخزن الرئيسي' }}
                            </span>
                            <span class="preview-doc-badge erp-badge erp-badge-success text-xs">
                                {{ $sampleData['document']['status'] ?? 'مؤكدة' }}
                            </span>
                        </div>
                        <div class="col-span-2 flex justify-between items-center pt-2">
                            <div class="preview-doc-barcode text-xs font-mono text-slate-700">
                                ||| | |||| | ||| {{ $sampleData['document']['barcode'] ?? '123456' }}
                            </div>
                            <div class="preview-doc-qr text-xs font-mono text-slate-600">
                                [ QR Code ]
                            </div>
                        </div>
                    </div>

                    <!-- Mockup Party Metadata -->
                    <div class="doc-mockup-parties">
                        <div class="p-2.5 rounded bg-slate-50 border border-slate-200">
                            <span class="preview-party-label font-bold text-xs text-slate-700 ml-2">
                                {{ $currentTemplate->getSetting('party_meta.party_type_label', 'الطرف المقابل') }}:
                            </span>
                            <span class="preview-party-name font-semibold text-slate-800">
                                {{ $sampleData['party']['name'] ?? 'محل الأناقة' }}
                            </span>
                            <div class="preview-party-phone text-xs text-slate-600 mt-1">
                                هاتف: {{ $sampleData['party']['phone'] ?? '01123456789' }}
                            </div>
                            <div class="preview-party-address text-xs text-slate-500">
                                {{ $sampleData['party']['address'] ?? 'شارع الجمهورية' }}
                            </div>
                        </div>
                    </div>

                    <!-- Mockup Items Table (For Invoices) -->
                    @if(isset($sampleData['items']))
                        <table class="doc-mockup-table">
                            <thead>
                                <tr>
                                    <th class="col-index">#</th>
                                    <th class="col-name text-right">الصنف والبيان</th>
                                    <th class="col-sku text-center">الكود</th>
                                    <th class="col-unit text-center">الوحدة</th>
                                    <th class="col-qty text-center">الكمية</th>
                                    <th class="col-price text-left">السعر</th>
                                    <th class="col-discount text-left">الخصم</th>
                                    <th class="col-total text-left">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sampleData['items'] as $item)
                                    <tr>
                                        <td class="col-index text-center">{{ $item['index'] }}</td>
                                        <td class="col-name font-medium">
                                            {{ $item['name'] }}
                                            <div class="col-notes text-[10px] text-slate-500">{{ $item['notes'] }}</div>
                                        </td>
                                        <td class="col-sku text-center font-mono text-xs">{{ $item['sku'] }}</td>
                                        <td class="col-unit text-center text-xs">{{ $item['unit'] }}</td>
                                        <td class="col-qty text-center font-semibold">{{ number_format($item['quantity'], 2) }}</td>
                                        <td class="col-price text-left font-mono">{{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="col-discount text-left font-mono">{{ number_format($item['discount'], 2) }}</td>
                                        <td class="col-total text-left font-mono font-bold">{{ number_format($item['subtotal'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Mockup Transactions Table (For Statements) -->
                    @if(isset($sampleData['transactions']))
                        <table class="doc-mockup-table">
                            <thead>
                                <tr>
                                    <th class="text-center">التاريخ</th>
                                    <th class="text-center">رقم المرجع</th>
                                    <th class="text-right">البيان والحركة</th>
                                    <th class="text-left">مدين</th>
                                    <th class="text-left">دائن</th>
                                    <th class="text-left">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sampleData['transactions'] as $tx)
                                    <tr>
                                        <td class="text-center text-xs font-mono">{{ $tx['date'] }}</td>
                                        <td class="text-center text-xs font-mono">{{ $tx['ref_number'] }}</td>
                                        <td class="text-xs">{{ $tx['description'] }}</td>
                                        <td class="text-left text-xs font-mono">{{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '-' }}</td>
                                        <td class="text-left text-xs font-mono">{{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '-' }}</td>
                                        <td class="text-left text-xs font-mono font-bold">{{ number_format($tx['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Mockup Financial Totals Box -->
                    @if(isset($sampleData['financial']))
                        <div class="doc-mockup-totals-box">
                            <table class="doc-mockup-totals-table">
                                <tr class="row-subtotal">
                                    <td class="text-slate-600">المجموع الفرعي:</td>
                                    <td class="text-left font-mono font-semibold">{{ number_format($sampleData['financial']['subtotal'], 2) }} ج.م</td>
                                </tr>
                                <tr class="row-discount">
                                    <td class="text-slate-600">الخصم:</td>
                                    <td class="text-left font-mono text-rose-600">{{ number_format($sampleData['financial']['discount'], 2) }} ج.م</td>
                                </tr>
                                <tr class="row-shipping">
                                    <td class="text-slate-600">الشحن:</td>
                                    <td class="text-left font-mono">{{ number_format($sampleData['financial']['shipping'], 2) }} ج.م</td>
                                </tr>
                                <tr class="row-net-total grand-total-row">
                                    <td>الصافي النهائي:</td>
                                    <td class="text-left font-mono">{{ number_format($sampleData['financial']['net_total'], 2) }} ج.م</td>
                                </tr>
                                <tr class="row-paid">
                                    <td class="text-slate-600">المدفوع:</td>
                                    <td class="text-left font-mono text-emerald-700">{{ number_format($sampleData['financial']['paid'], 2) }} ج.م</td>
                                </tr>
                                <tr class="row-remaining">
                                    <td class="text-slate-600">المتبقي:</td>
                                    <td class="text-left font-mono font-bold text-amber-700">{{ number_format($sampleData['financial']['remaining'], 2) }} ج.م</td>
                                </tr>
                            </table>
                        </div>
                    @endif

                    <!-- Mockup Notes and Terms -->
                    <div class="preview-notes-box doc-mockup-notes">
                        <span class="font-bold">ملاحظات:</span>
                        <span class="notes-content">{{ $currentTemplate->getSetting('notes_and_terms.notes_text') }}</span>
                    </div>

                    <div class="preview-terms-box text-xs text-slate-500 mb-4 p-2 bg-slate-50 rounded border border-slate-200">
                        <span class="font-semibold block mb-0.5">الشروط والأحكام:</span>
                        <span class="terms-content">{{ $currentTemplate->getSetting('notes_and_terms.terms_text') }}</span>
                    </div>

                    <!-- Mockup Signatures & Stamp -->
                    <div class="doc-mockup-signatures">
                        <div class="sig-box">
                            <span class="first-party-title font-semibold">{{ $currentTemplate->getSetting('signatures.first_party_title', 'الطرف الأول') }}</span>
                            <div class="sig-line"></div>
                        </div>

                        <div class="doc-mockup-stamp-box">
                            <span>موضع الختم</span>
                        </div>

                        <div class="sig-box">
                            <span class="second-party-title font-semibold">{{ $currentTemplate->getSetting('signatures.second_party_title', 'المسؤول') }}</span>
                            <div class="sig-line"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
