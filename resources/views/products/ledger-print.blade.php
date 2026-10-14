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
                <span>طباعة كشف حركة الصنف: {{ $product->name }}</span>
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
                {{ $movementsCount }} حركات مسجلة · الرصيد الحالي: {{ number_format((float)$product->total_stock, 2) }} {{ $product->unit }}
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
                                {{ $company?->company_name ?: 'شركة كيان للتجارة والتوزيع' }}
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
                        كشف حركة الصنف المخزنية
                    </h2>
                    <div class="doc-subtitle">
                        سجل الحركات والأرصدة التراكمية للمنتج
                    </div>
                    <div class="doc-company-meta font-numeric">
                        تاريخ التقرير: {{ now()->format('Y-m-d') }}
                    </div>
                </div>
            </header>

            <!-- 2. Product Dossier & Filter Parameters -->
            <section class="doc-meta-section">
                <!-- Product Specification Panel -->
                <div class="doc-panel">
                    <div class="doc-panel-heading">بيانات الصنف</div>
                    <div class="doc-info-list">
                        <div class="doc-info-row">
                            <span class="doc-info-label">اسم المنتج:</span>
                            <span class="doc-info-val">{{ $product->name }}</span>
                        </div>
                        <div class="doc-info-row">
                            <span class="doc-info-label">كود الصنف (SKU):</span>
                            <span class="doc-info-val font-numeric">{{ $product->sku }}</span>
                        </div>
                        @if(!empty($product->barcode))
                            <div class="doc-info-row">
                                <span class="doc-info-label">الباركود الدولي:</span>
                                <span class="doc-info-val font-numeric">{{ $product->barcode }}</span>
                            </div>
                        @endif
                        <div class="doc-info-row">
                            <span class="doc-info-label">التصنيف والوحدة:</span>
                            <span class="doc-info-val">{{ $product->category->name ?? 'غير محدد' }} ({{ $product->unit }})</span>
                        </div>
                    </div>
                </div>

                <!-- Report Context & Filters Panel -->
                <div class="doc-panel">
                    <div class="doc-panel-heading">نطاق التقرير والفلاتر</div>
                    <div class="doc-info-list">
                        <div class="doc-info-row">
                            <span class="doc-info-label">المستودع:</span>
                            <span class="doc-info-val">{{ $selectedWarehouse?->name ?: 'كافة المستودعات والفروع' }}</span>
                        </div>
                        <div class="doc-info-row">
                            <span class="doc-info-label">نوع الحركة:</span>
                            <span class="doc-info-val">
                                @if($currentType === 'in')
                                    وارد / إيداع فقط
                                @elseif($currentType === 'out')
                                    منصرف / مبيعات فقط
                                @elseif($currentType === 'transfer')
                                    تحويلات فقط
                                @elseif($currentType === 'adjustment')
                                    تسويات جردية فقط
                                @else
                                    جميع أنواع الحركات
                                @endif
                            </span>
                        </div>
                        <div class="doc-info-row">
                            <span class="doc-info-label">الفترة الزمنية:</span>
                            <span class="doc-info-val font-numeric">
                                @if($currentDateFrom && $currentDateTo)
                                    من {{ $currentDateFrom }} إلى {{ $currentDateTo }}
                                @elseif($currentDateFrom)
                                    من {{ $currentDateFrom }} حتى تاريخه
                                @elseif($currentDateTo)
                                    حتى تاريخ {{ $currentDateTo }}
                                @else
                                    كامل السجل التاريخي
                                @endif
                            </span>
                        </div>
                        <div class="doc-info-row">
                            <span class="doc-info-label">الرصيد الفعلي الحالي:</span>
                            <span class="doc-info-val font-numeric text-in">
                                {{ number_format((float)$product->total_stock, 2) }} {{ $product->unit }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. KPI Statistics Grid -->
            <section class="doc-kpi-summary-grid">
                <div class="doc-kpi-card">
                    <div class="doc-kpi-val text-in font-numeric">+{{ number_format($totalIn, 2) }}</div>
                    <div class="doc-kpi-label">إجمالي الوارد بالفترة</div>
                </div>
                <div class="doc-kpi-card">
                    <div class="doc-kpi-val text-out font-numeric">-{{ number_format($totalOut, 2) }}</div>
                    <div class="doc-kpi-label">إجمالي المنصرف بالفترة</div>
                </div>
                <div class="doc-kpi-card">
                    <div class="doc-kpi-val text-transfer font-numeric">{{ number_format($totalTransfer, 2) }}</div>
                    <div class="doc-kpi-label">إجمالي التحويلات</div>
                </div>
                <div class="doc-kpi-card">
                    <div class="doc-kpi-val font-numeric">{{ $movementsCount }}</div>
                    <div class="doc-kpi-label">عدد الحركات المطبوعة</div>
                </div>
            </section>

            <!-- 4. Granular Stock Movements Ledger Table -->
            <table class="doc-table">
                <thead>
                    <tr>
                        <th class="doc-table-num">#</th>
                        <th>التاريخ والوقت</th>
                        @if($format !== 'thermal_80mm')
                            <th>رقم الحركة</th>
                        @endif
                        <th>نوع الحركة</th>
                        <th>المستودع</th>
                        @if($format !== 'thermal_80mm')
                            <th>الطرف</th>
                        @endif
                        <th class="doc-table-num">الكمية</th>
                        <th class="doc-table-price">الرصيد بعد الحركة</th>
                        @if($format !== 'thermal_80mm')
                            <th>البيان / المرجع</th>
                            <th>المستخدم</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $index => $m)
                        <tr>
                            <td class="doc-table-num font-numeric">{{ $index + 1 }}</td>
                            <td class="font-numeric">
                                {{ $m->created_at->format('Y-m-d H:i') }}
                            </td>
                            @if($format !== 'thermal_80mm')
                                <td class="font-numeric font-bold">
                                    {{ $m->movement_number }}
                                </td>
                            @endif
                            <td>
                                @if($m->reference_type === 'opening_balance')
                                    <span class="doc-badge-pill doc-badge-info">رصيد افتتاحي</span>
                                @elseif($m->type === 'in')
                                    <span class="doc-badge-pill doc-badge-in">وارد</span>
                                @elseif($m->type === 'out')
                                    <span class="doc-badge-pill doc-badge-out">منصرف</span>
                                @elseif($m->type === 'transfer')
                                    <span class="doc-badge-pill doc-badge-transfer">تحويل</span>
                                @else
                                    <span class="doc-badge-pill doc-badge-adjustment">تسوية</span>
                                @endif
                            </td>
                            <td>
                                @if($m->type === 'transfer')
                                    <span>{{ $m->fromWarehouse->name ?? '—' }} ⬅ {{ $m->toWarehouse->name ?? '—' }}</span>
                                @elseif($m->toWarehouse)
                                    <span>{{ $m->toWarehouse->name }}</span>
                                @elseif($m->fromWarehouse)
                                    <span>{{ $m->fromWarehouse->name }}</span>
                                @else
                                    <span>—</span>
                                @endif
                            </td>
                            @if($format !== 'thermal_80mm')
                                <td>
                                    @if($m->supplier)
                                        <span>{{ $m->supplier->name }}</span>
                                    @elseif($m->customer)
                                        <span>{{ $m->customer->name }}</span>
                                    @else
                                        <span>—</span>
                                    @endif
                                </td>
                            @endif
                            <td class="doc-table-num font-numeric">
                                @if($m->type === 'in' || $m->reference_type === 'opening_balance')
                                    <span class="text-in">+{{ number_format((float)$m->quantity, 2) }}</span>
                                @elseif($m->type === 'out')
                                    <span class="text-out">-{{ number_format((float)$m->quantity, 2) }}</span>
                                @else
                                    <span>{{ number_format((float)$m->quantity, 2) }}</span>
                                @endif
                            </td>
                            <td class="doc-table-price font-numeric font-bold">
                                {{ $m->running_balance !== null ? number_format((float)$m->running_balance, 2) : '—' }}
                            </td>
                            @if($format !== 'thermal_80mm')
                                <td>
                                    <div>{{ $m->notes ?: '—' }}</div>
                                    @if($m->reference_type)
                                        <small class="item-sku font-numeric">{{ $m->reference_type }} #{{ $m->reference_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $m->user->name ?? 'النظام' }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $format === 'thermal_80mm' ? 6 : 10 }}" class="text-center py-6 text-[var(--text-muted)]">
                                لا توجد حركات مخزنية مسجلة لهذا الصنف وفق محددات البحث المختارة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- 5. Formal Signatures & Stamp -->
            @if($format !== 'thermal_80mm')
                <footer class="doc-signatures-wrap">
                    <div class="sig-box">
                        <div>أمين المخزن المسئول</div>
                        <div class="sig-line">التوقيع والاعتماد</div>
                    </div>

                    <div class="stamp-box">
                        <span>ختم المخزن</span>
                    </div>

                    <div class="sig-box">
                        <div>مسؤول مراقبة المخزون والحسابات</div>
                        <div class="sig-line">التوقيع والاعتماد</div>
                    </div>
                </footer>
            @endif

            <div class="doc-footer-note font-numeric">
                تم استخراج هذا التقرير عبر نظام BayoSoft ERP في {{ now()->format('Y-m-d H:i') }} · مستند فحص مخزني رسمي
            </div>
        </article>
    </main>

</body>
</html>
