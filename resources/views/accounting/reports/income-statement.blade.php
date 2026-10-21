@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="قائمة الدخل والأرباح والخسائر (Income Statement / P&L)" :breadcrumbs="['المالية والحسابات' => route('accounting.chart-of-accounts'), 'قائمة الدخل' => route('accounting.reports.income-statement')]">
        <x-slot:actions>
            <a href="{{ route('accounting.reports.income-statement.print', request()->query()) }}" target="_blank" class="erp-btn erp-btn-secondary">
                <x-icon name="printer" size="16" />
                <span>طباعة التقرير</span>
            </a>
            <a href="{{ route('accounting.reports.balance-sheet') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="pie-chart" size="16" />
                <span>الميزانية العمومية</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $report['net_profit'] >= 0 ? 'erp-badge-success' : 'erp-badge-danger' }} font-numeric px-3 py-1 text-xs">
                <span>صافي الربح:</span>
                <strong class="mr-1">{{ number_format($report['net_profit'], 2) }} ج.م</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="income-statement">
        @include('accounting.nav-tabs')

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="erp-card p-4 border-r-4 border-r-blue-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>صافي الإيرادات</span>
                    <x-icon name="trending-up" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-blue-600 dark:text-blue-400">
                    {{ number_format($report['net_revenue'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    إجمالي المبيعات: {{ number_format($report['total_gross_sales'], 2) }} ج.م
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-amber-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>تكلفة البضاعة المباعة (FIFO)</span>
                    <x-icon name="package" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-amber-600 dark:text-amber-400">
                    {{ number_format($report['total_cogs'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    تكلفة المخزون الفعلي المصروف
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-emerald-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>مجمل الربح التجاري (Gross Profit)</span>
                    <x-icon name="percent" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-emerald-600 dark:text-emerald-400">
                    {{ number_format($report['gross_profit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold mt-1">
                    هامش الربح: {{ $report['gross_margin_pct'] }}%
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 {{ $report['net_profit'] >= 0 ? 'border-r-emerald-600 bg-emerald-50/20 dark:bg-emerald-950/20' : 'border-r-rose-600 bg-rose-50/20 dark:bg-rose-950/20' }}">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>صافي الربح / الخسارة النهائي</span>
                    <x-icon name="{{ $report['net_profit'] >= 0 ? 'award' : 'alert-circle' }}" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric {{ $report['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                    {{ number_format($report['net_profit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    المصروفات: {{ number_format($report['total_operating_expenses'], 2) }} ج.م
                </div>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="erp-card p-4 mb-6">
            <form method="GET" action="{{ route('accounting.reports.income-statement') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $currentDateFrom }}" class="erp-input w-full font-numeric text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $currentDateTo }}" class="erp-input w-full font-numeric text-xs">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="erp-btn erp-btn-primary flex-1 justify-center text-xs">
                        <x-icon name="filter" size="14" />
                        <span>تحديث القائمة</span>
                    </button>
                    <a href="{{ route('accounting.reports.income-statement') }}" class="erp-btn erp-btn-secondary text-xs" title="إعادة ضبط">
                        <x-icon name="rotate-ccw" size="14" />
                    </a>
                </div>
            </form>
        </div>

        <!-- Income Statement Financial Document -->
        <div class="erp-card overflow-hidden">
            <div class="p-4 bg-[var(--bg-surface-alt)] border-b border-[var(--border-color)] flex justify-between items-center">
                <h3 class="font-bold text-sm">بيان الدخل والمركز التشغيلي للفترة</h3>
                <span class="text-xs text-[var(--text-muted)]">
                    الفترة: من {{ $currentDateFrom ?: 'بداية النشاط' }} إلى {{ $currentDateTo }}
                </span>
            </div>

            <div class="divide-y divide-[var(--border-color)] text-xs">
                <!-- Section 1: Operating Revenues -->
                <div class="p-4">
                    <div class="font-bold text-sm text-[var(--primary)] mb-3 flex items-center justify-between">
                        <span>1. إيرادات النشاط والمبيعات (Operating Revenues)</span>
                        <span class="font-numeric text-xs text-[var(--text-muted)]">حسابات الفئة (4)</span>
                    </div>

                    <div class="space-y-2 pr-4">
                        @foreach($report['gross_sales'] as $sale)
                            <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                <span class="text-[var(--text-muted)]">{{ $sale['name'] }} [{{ $sale['code'] }}]</span>
                                <span class="font-numeric font-semibold">{{ number_format($sale['amount'], 2) }} ج.م</span>
                            </div>
                        @endforeach

                        @if(count($report['sales_deductions']) > 0)
                            <div class="pt-2 text-[var(--text-muted)] font-semibold text-[11px]">يخصم: المردودات والخصومات المسموحة:</div>
                            @foreach($report['sales_deductions'] as $ded)
                                <div class="flex justify-between items-center py-1 text-rose-600 dark:text-rose-400 border-b border-dashed border-[var(--border-color)]">
                                    <span>(-) {{ $ded['name'] }} [{{ $ded['code'] }}]</span>
                                    <span class="font-numeric">({{ number_format($ded['amount'], 2) }}) ج.م</span>
                                </div>
                            @endforeach
                        @endif

                        <div class="flex justify-between items-center pt-2 font-bold text-sm text-blue-600 dark:text-blue-400">
                            <span>صافي الإيرادات (Net Revenue):</span>
                            <span class="font-numeric">{{ number_format($report['net_revenue'], 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Cost of Goods Sold -->
                <div class="p-4 bg-amber-50/10 dark:bg-amber-950/10">
                    <div class="font-bold text-sm text-amber-700 dark:text-amber-400 mb-3 flex items-center justify-between">
                        <span>2. تكلفة المبيعات والبضاعة المباعة (Cost of Goods Sold - FIFO)</span>
                        <span class="font-numeric text-xs text-[var(--text-muted)]">حسابات الفئة (51)</span>
                    </div>

                    <div class="space-y-2 pr-4">
                        @forelse($report['cogs'] as $cg)
                            <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                <span class="text-[var(--text-muted)]">{{ $cg['name'] }} [{{ $cg['code'] }}]</span>
                                <span class="font-numeric font-semibold">(-) {{ number_format($cg['amount'], 2) }} ج.م</span>
                            </div>
                        @empty
                            <div class="text-[var(--text-muted)] italic">لا توجد تكاليف بضاعة مسجلة خلال الفترة.</div>
                        @endforelse

                        <div class="flex justify-between items-center pt-3 font-bold text-sm text-emerald-700 dark:text-emerald-400 border-t border-[var(--border-color)]">
                            <div>
                                <span>مجمل الربح التجاري (Gross Profit):</span>
                                <span class="text-xs font-normal text-[var(--text-muted)] mr-2">(هامش {{ $report['gross_margin_pct'] }}%)</span>
                            </div>
                            <span class="font-numeric text-base">{{ number_format($report['gross_profit'], 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Operating Expenses -->
                <div class="p-4">
                    <div class="font-bold text-sm text-[var(--text-main)] mb-3 flex items-center justify-between">
                        <span>3. المصروفات التشغيلية والعمومية والتسويقية (Operating Expenses)</span>
                        <span class="font-numeric text-xs text-[var(--text-muted)]">حسابات الفئة (52, 53)</span>
                    </div>

                    <div class="space-y-2 pr-4">
                        @forelse($report['operating_expenses'] as $exp)
                            <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                <span class="text-[var(--text-muted)]">{{ $exp['name'] }} [{{ $exp['code'] }}]</span>
                                <span class="font-numeric font-semibold">(-) {{ number_format($exp['amount'], 2) }} ج.م</span>
                            </div>
                        @empty
                            <div class="text-[var(--text-muted)] italic py-1">لا توجد مصروفات عمومية مسجلة في هذه الفترة.</div>
                        @endforelse

                        <div class="flex justify-between items-center pt-2 font-bold text-xs text-[var(--text-muted)]">
                            <span>إجمالي المصروفات التشغيلية:</span>
                            <span class="font-numeric">(-) {{ number_format($report['total_operating_expenses'], 2) }} ج.م</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 font-bold text-sm text-blue-600 dark:text-blue-400 border-t border-[var(--border-color)]">
                            <span>صافي الربح التشغيلي (Operating Profit):</span>
                            <span class="font-numeric">{{ number_format($report['operating_profit'], 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Other Revenues/Expenses -->
                @if(count($report['other_items']) > 0)
                    <div class="p-4 bg-[var(--bg-surface-alt)]/50">
                        <div class="font-bold text-sm text-[var(--text-main)] mb-3">
                            4. إيرادات وتكاليف أخرى (Other Income / Expenses)
                        </div>

                        <div class="space-y-2 pr-4">
                            @foreach($report['other_items'] as $other)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                    <span class="text-[var(--text-muted)]">{{ $other['name'] }} [{{ $other['code'] }}]</span>
                                    <span class="font-numeric font-semibold text-emerald-600 dark:text-emerald-400">(+) {{ number_format($other['amount'], 2) }} ج.م</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Final Net Profit / Loss Banner -->
                <div class="p-5 {{ $report['net_profit'] >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div>
                            <div class="text-base font-extrabold flex items-center gap-2">
                                <x-icon name="{{ $report['net_profit'] >= 0 ? 'award' : 'alert-octagon' }}" size="20" />
                                <span>صافي الربح / الخسارة النهائي للفترة (Net Profit / Loss):</span>
                            </div>
                            <div class="text-xs opacity-90 mt-0.5">
                                يمثل النتيجة الصافية بعد كافة التكاليف والمصروفات، ويُرحل إلى حقوق الملكية بالميزانية.
                            </div>
                        </div>
                        <div class="text-2xl font-black font-numeric">
                            {{ number_format($report['net_profit'], 2) }} <span class="text-sm font-normal">ج.م</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
