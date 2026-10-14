<!DOCTYPE html>
<html lang="ar" dir="rtl" class="{{ $colorClass }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/print/invoice-document.css', 'resources/js/pages/invoice-print.js'])
</head>
<body data-format="{{ $format }}" data-auto-print="{{ $autoPrint ? 'true' : 'false' }}">

    <!-- Screen-Only Navigation & Print Actions Toolbar -->
    <header class="print-toolbar no-print">
        <div class="toolbar-info">
            <span class="toolbar-title">
                <x-icon name="printer" size="18" />
                <span>طباعة فاتورة شراء: {{ $invoice->invoice_number }}</span>
            </span>

            <div class="toolbar-formats">
                <a href="{{ request()->fullUrlWithQuery(['format' => 'a4']) }}" class="toolbar-format-btn {{ $format === 'a4' ? 'active' : '' }}">
                    A4 قياسي
                </a>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'a5']) }}" class="toolbar-format-btn {{ $format === 'a5' ? 'active' : '' }}">
                    A5 وسط
                </a>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'thermal_80mm']) }}" class="toolbar-format-btn {{ $format === 'thermal_80mm' ? 'active' : '' }}">
                    80 مم حراري
                </a>
            </div>

            <span class="toolbar-badge primary font-numeric">
                {{ $invoice->items->count() }} بنود · {{ number_format((float)$invoice->total_amount, 2) }} ج.م
            </span>
        </div>

        <div class="toolbar-actions">
            <button type="button" data-action="print-now" class="toolbar-btn toolbar-btn-primary">
                <x-icon name="printer" size="16" />
                <span>طباعة الآن</span>
            </button>
            <button type="button" data-action="close-window" class="toolbar-btn toolbar-btn-secondary">
                <x-icon name="x" size="14" />
                <span>إغلاق</span>
            </button>
        </div>
    </header>

    <!-- Document Paper Sheet Container -->
    <main class="invoice-page-container">
        <article 
            class="invoice-sheet" 
            data-format="{{ $format }}" 
            data-density="{{ $template->getSetting('table_columns.density', 'comfortable') }}"
            data-font-size="{{ $template->getSetting('general.font_size', 'md') }}"
        >
            <!-- 1. Document Header -->
            <header class="doc-header">
                <div class="doc-brand">
                    @if($template->getSetting('general.show_logo', true) && !empty($company?->logo_url))
                        <div class="doc-logo-wrap logo-size-{{ $template->getSetting('general.logo_size', 'md') }}">
                            <img src="{{ $company->logo_url }}" alt="{{ $company->company_name ?? 'الشركة' }}">
                        </div>
                    @endif

                    <div>
                        @if($template->getSetting('general.show_company_name', true))
                            <h1 class="doc-company-title">
                                {{ $company?->company_name ?: 'شركة كيان التجارية' }}
                            </h1>
                        @endif

                        <div class="doc-company-meta font-numeric">
                            @if($template->getSetting('general.show_company_phone', true) && !empty($company?->company_phone))
                                <div>هاتف: {{ $company->company_phone }}</div>
                            @endif
                            @if($template->getSetting('general.show_company_address', true) && !empty($company?->company_address))
                                <div>{{ $company->company_address }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="doc-title-block">
                    <h2 class="doc-main-title">
                        {{ $template->getSetting('general.custom_title') ?: 'فاتورة شراء وتوريد' }}
                    </h2>
                    @if($template->getSetting('general.custom_subtitle'))
                        <div class="doc-subtitle">
                            {{ $template->getSetting('general.custom_subtitle') }}
                        </div>
                    @endif

                    @if($template->getSetting('document_meta.show_status_badge', true))
                        <div>
                            <span class="doc-status-badge status-{{ $invoice->status }}">
                                {{ $invoice->status_label }}
                            </span>
                        </div>
                    @endif
                </div>
            </header>

            <!-- 2. Invoice Metadata & Party Information -->
            <section class="doc-meta-section">
                <!-- Meta Info Panel -->
                <div class="doc-panel">
                    <div class="doc-panel-heading">بيانات المستند</div>
                    <div class="doc-info-list">
                        @if($template->getSetting('document_meta.show_doc_number', true))
                            <div class="doc-info-row">
                                <span class="doc-info-label">رقم الفاتورة:</span>
                                <span class="doc-info-val font-numeric">{{ $invoice->invoice_number }}</span>
                            </div>
                        @endif

                        @if($template->getSetting('document_meta.show_doc_date', true))
                            <div class="doc-info-row">
                                <span class="doc-info-label">تاريخ الفاتورة:</span>
                                <span class="doc-info-val font-numeric">{{ $invoice->invoice_date->format('Y-m-d') }}</span>
                            </div>
                        @endif

                        @if($template->getSetting('document_meta.show_warehouse', true))
                            <div class="doc-info-row">
                                <span class="doc-info-label">مستودع الاستلام:</span>
                                <span class="doc-info-val">{{ $invoice->warehouse->name ?? 'المستودع الرئيسي' }}</span>
                            </div>
                        @endif

                        @if(!empty($invoice->creator))
                            <div class="doc-info-row">
                                <span class="doc-info-label">المسؤول:</span>
                                <span class="doc-info-val">{{ $invoice->creator->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Supplier Info Panel -->
                <div class="doc-panel">
                    <div class="doc-panel-heading">
                        {{ $template->getSetting('party_meta.party_type_label') ?: 'بيانات المورد' }}
                    </div>
                    <div class="doc-info-list">
                        @if($template->getSetting('party_meta.show_party_name', true))
                            <div class="doc-info-row">
                                <span class="doc-info-label">اسم المورد:</span>
                                <span class="doc-info-val">{{ $invoice->supplier->name ?? 'غير محدد' }}</span>
                            </div>
                        @endif

                        @if($template->getSetting('party_meta.show_party_phone', true) && !empty($invoice->supplier?->phone))
                            <div class="doc-info-row">
                                <span class="doc-info-label">الهاتف / ويشات:</span>
                                <span class="doc-info-val font-numeric">{{ $invoice->supplier->phone }}</span>
                            </div>
                        @endif

                        @if($template->getSetting('party_meta.show_party_address', true) && (!empty($invoice->supplier?->country) || !empty($invoice->supplier?->city)))
                            <div class="doc-info-row">
                                <span class="doc-info-label">الدولة والمدينة:</span>
                                <span class="doc-info-val">
                                    {{ $invoice->supplier->country ?? '' }}
                                    @if(!empty($invoice->supplier?->city))
                                        ({{ $invoice->supplier->city }})
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- 3. Invoiced Line Items Table -->
            <table class="doc-table">
                <thead>
                    <tr>
                        @if($template->getSetting('table_columns.show_index', true))
                            <th class="doc-table-num">#</th>
                        @endif
                        <th>الصنف والوصف</th>
                        @if($template->getSetting('table_columns.show_sku', true) && $format !== 'thermal_80mm')
                            <th>كود SKU</th>
                        @endif
                        @if($template->getSetting('table_columns.show_unit', true))
                            <th class="doc-table-num">الوحدة</th>
                        @endif
                        <th class="doc-table-num">الكمية</th>
                        @if($template->getSetting('table_columns.show_price', true))
                            <th class="doc-table-price">سعر التكلفة</th>
                        @endif
                        @if($template->getSetting('table_columns.show_total', true))
                            <th class="doc-table-price">الإجمالي</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            @if($template->getSetting('table_columns.show_index', true))
                                <td class="doc-table-num font-numeric">{{ $index + 1 }}</td>
                            @endif
                            <td>
                                <strong>{{ $item->product->name ?? 'صنف غير محدد' }}</strong>
                                @if($format === 'thermal_80mm' && !empty($item->product?->sku))
                                    <span class="item-sku font-numeric">{{ $item->product->sku }}</span>
                                @endif
                            </td>
                            @if($template->getSetting('table_columns.show_sku', true) && $format !== 'thermal_80mm')
                                <td class="font-numeric">{{ $item->product->sku ?? '—' }}</td>
                            @endif
                            @if($template->getSetting('table_columns.show_unit', true))
                                <td class="doc-table-num">{{ $item->product->unit ?? 'قطعة' }}</td>
                            @endif
                            <td class="doc-table-num font-numeric">
                                {{ number_format((float)$item->quantity, (fmod((float)$item->quantity, 1) === 0.0 ? 0 : 2)) }}
                            </td>
                            @if($template->getSetting('table_columns.show_price', true))
                                <td class="doc-table-price font-numeric">
                                    {{ number_format((float)$item->unit_cost, 2) }} ج.م
                                </td>
                            @endif
                            @if($template->getSetting('table_columns.show_total', true))
                                <td class="doc-table-price font-numeric">
                                    {{ number_format((float)$item->subtotal, 2) }} ج.م
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- 4. Financial Summary Section -->
            <section class="doc-summary-section">
                <table class="doc-summary-table font-numeric">
                    @if($template->getSetting('financial_summary.show_subtotal', true))
                        <tr>
                            <td class="doc-summary-label">المجموع الفرعي:</td>
                            <td class="doc-summary-val">{{ number_format((float)$invoice->subtotal, 2) }} ج.م</td>
                        </tr>
                    @endif

                    @if($template->getSetting('financial_summary.show_shipping', true) && (float)$invoice->shipping_cost > 0)
                        <tr>
                            <td class="doc-summary-label">تكلفة الشحن والتوصيل:</td>
                            <td class="doc-summary-val">{{ number_format((float)$invoice->shipping_cost, 2) }} ج.م</td>
                        </tr>
                    @endif

                    @if($template->getSetting('financial_summary.show_net_total', true))
                        <tr class="doc-grand-total">
                            <td class="doc-summary-label">إجمالي الفاتورة:</td>
                            <td class="doc-summary-val">{{ number_format((float)$invoice->total_amount, 2) }} ج.م</td>
                        </tr>
                    @endif
                </table>
            </section>

            <!-- 5. Notes & Commercial Terms -->
            @if(!empty($invoice->notes) || !empty($template->getSetting('notes_and_terms.terms_text')))
                <section class="doc-notes-box">
                    @if(!empty($invoice->notes))
                        <div><strong>ملاحظات الفاتورة:</strong> {{ $invoice->notes }}</div>
                    @endif
                    @if(!empty($template->getSetting('notes_and_terms.terms_text')))
                        <div class="mt-1">{{ $template->getSetting('notes_and_terms.terms_text') }}</div>
                    @endif
                </section>
            @endif

            <!-- 6. Formal Signatures & Stamp -->
            @if($template->getSetting('notes_and_terms.show_signatures', true) && $format !== 'thermal_80mm')
                <footer class="doc-signatures-wrap">
                    <div class="sig-box">
                        <div>المستلم / أمين المخزن</div>
                        <div class="sig-line">التوقيع والاعتماد</div>
                    </div>

                    @if($template->getSetting('notes_and_terms.show_stamp', true))
                        <div class="stamp-box">
                            <span>ختم الشركة</span>
                        </div>
                    @endif

                    <div class="sig-box">
                        <div>مسؤول المشتريات والحسابات</div>
                        <div class="sig-line">التوقيع والاعتماد</div>
                    </div>
                </footer>
            @endif

            <div class="doc-footer-note font-numeric">
                تم إصدار هذا المستند عبر نظام BayoSoft ERP في {{ now()->format('Y-m-d H:i') }}
            </div>
        </article>
    </main>

</body>
</html>
