@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="الميزانية العمومية والمركز المالي (Balance Sheet)" :breadcrumbs="['المالية والحسابات' => route('treasuries.index'), 'شجرة الحسابات' => route('accounting.chart-of-accounts'), 'الميزانية العمومية' => route('accounting.reports.balance-sheet')]">
        <x-slot:actions>
            <a href="{{ route('accounting.reports.balance-sheet.print', request()->query()) }}" target="_blank" class="erp-btn erp-btn-secondary">
                <x-icon name="printer" size="16" />
                <span>طباعة الميزانية</span>
            </a>
            <a href="{{ route('accounting.reports.income-statement') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="trending-up" size="16" />
                <span>قائمة الدخل</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            @if($report['is_balanced'])
                <span class="erp-badge erp-badge-success font-numeric px-3 py-1 text-xs">
                    <x-icon name="check-circle" size="13" />
                    <span class="mr-1">الميزانية متوازنة ومطابقة تماماً (0.00 ج.م)</span>
                </span>
            @else
                <span class="erp-badge erp-badge-danger font-numeric px-3 py-1 text-xs">
                    <x-icon name="alert-triangle" size="13" />
                    <span class="mr-1">يوجد فارق غير متوازن: {{ number_format($report['diff'], 2) }} ج.م</span>
                </span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="balance-sheet">
        @include('accounting.nav-tabs')

        <!-- KPI Summary Cards -->
        <div class="finance-kpi-grid">
            <div class="finance-kpi-card">
                <div class="finance-kpi-icon info">
                    <x-icon name="layers" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">إجمالي الأصول (Assets)</div>
                    <div class="finance-kpi-value font-numeric text-[var(--info-text)]">
                        {{ number_format($report['total_assets'], 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
                    </div>
                    <div class="finance-kpi-sub">أصول متداولة: {{ number_format($report['total_current_assets'], 2) }} ج.م</div>
                </div>
            </div>

            <div class="finance-kpi-card">
                <div class="finance-kpi-icon warning">
                    <x-icon name="credit-card" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">إجمالي الخصوم والالتزامات</div>
                    <div class="finance-kpi-value font-numeric text-[var(--warning-text)]">
                        {{ number_format($report['total_liabilities'], 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
                    </div>
                    <div class="finance-kpi-sub">مستحقات الموردين والالتزامات</div>
                </div>
            </div>

            <div class="finance-kpi-card">
                <div class="finance-kpi-icon primary">
                    <x-icon name="shield" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">إجمالي حقوق الملكية (Equity)</div>
                    <div class="finance-kpi-value font-numeric text-[var(--primary)]">
                        {{ number_format($report['total_equity'], 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
                    </div>
                    <div class="finance-kpi-sub">تشمل ربح الفترة: {{ number_format($report['current_period_net_profit'], 2) }} ج.م</div>
                </div>
            </div>

            <div class="finance-kpi-card">
                <div class="finance-kpi-icon {{ $report['is_balanced'] ? 'success' : 'danger' }}">
                    <x-icon name="{{ $report['is_balanced'] ? 'check-circle' : 'alert-octagon' }}" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">توازن المركز المالي</div>
                    <div class="finance-kpi-value font-numeric {{ $report['is_balanced'] ? 'text-[var(--success-text)]' : 'text-[var(--danger)]' }}">
                        {{ $report['is_balanced'] ? 'الأصول = الخصوم + الملكية' : 'فارق: '.number_format($report['diff'], 2).' ج.م' }}
                    </div>
                    <div class="finance-kpi-sub">
                        {{ $report['is_balanced'] ? 'المعادلة متطابقة 100%' : 'تتطلب مراجعة قيود التسوية' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter Toolbar -->
        <div class="finance-filter-bar mb-4">
            <form method="GET" action="{{ route('accounting.reports.balance-sheet') }}" class="flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-64">
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">حتى تاريخ (As of Date)</label>
                    <input type="date" name="as_of_date" value="{{ $currentAsOfDate }}" class="finance-filter-input w-full font-numeric text-xs">
                </div>

                <button type="submit" class="erp-btn erp-btn-primary text-xs">
                    <x-icon name="filter" size="14" />
                    <span>تحديث الميزانية</span>
                </button>
                <a href="{{ route('accounting.reports.balance-sheet') }}" class="erp-btn erp-btn-secondary text-xs" title="اليوم">
                    <span>اليوم الحالي</span>
                </a>
            </form>
        </div>

        <!-- Balance Sheet Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
            <!-- RIGHT COLUMN: ASSETS (الأصول) -->
            <div class="erp-card overflow-hidden">
                <div class="p-4 bg-[var(--info-soft)] border-b border-[var(--border-color)] flex justify-between items-center">
                    <div class="font-bold text-sm text-[var(--info-text)] flex items-center gap-2">
                        <x-icon name="briefcase" size="16" />
                        <span>جانب الأصول (Assets)</span>
                    </div>
                    <span class="text-xs font-numeric font-bold text-[var(--info-text)]">
                        {{ number_format($report['total_assets'], 2) }} ج.م
                    </span>
                </div>

                <div class="p-4 space-y-5 text-xs">
                    <!-- Current Assets -->
                    <div>
                        <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                            <span>أولاً: الأصول المتداولة (Current Assets)</span>
                            <span class="font-numeric font-bold">{{ number_format($report['total_current_assets'], 2) }} ج.م</span>
                        </div>
                        <div class="space-y-1.5 pr-3">
                            @forelse($report['current_assets'] as $asset)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-light)]">
                                    <a href="{{ route('accounting.accounts.ledger', $asset['id']) }}" class="text-[var(--text-secondary)] hover:text-[var(--primary)] font-medium">
                                        {{ $asset['name'] }} [{{ $asset['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold text-[var(--text-main)]">{{ number_format($asset['balance'], 2) }} ج.م</span>
                                </div>
                            @empty
                                <div class="text-[var(--text-muted)] italic py-1">لا توجد أصول متداولة مسجلة.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Non-Current Assets -->
                    <div>
                        <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                            <span>ثانياً: الأصول الثابتة وغير المتداولة (Fixed Assets)</span>
                            <span class="font-numeric font-bold">{{ number_format($report['total_non_current_assets'], 2) }} ج.م</span>
                        </div>
                        <div class="space-y-1.5 pr-3">
                            @forelse($report['non_current_assets'] as $asset)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-light)]">
                                    <a href="{{ route('accounting.accounts.ledger', $asset['id']) }}" class="text-[var(--text-secondary)] hover:text-[var(--primary)] font-medium">
                                        {{ $asset['name'] }} [{{ $asset['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold text-[var(--text-main)]">{{ number_format($asset['balance'], 2) }} ج.م</span>
                                </div>
                            @empty
                                <div class="text-[var(--text-muted)] italic py-1">لا توجد أصول ثابتة مسجلة حالياً.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-[var(--info)] text-white flex justify-between items-center font-bold text-sm">
                    <span>إجمالي الأصول (Total Assets):</span>
                    <span class="font-numeric text-base">{{ number_format($report['total_assets'], 2) }} ج.م</span>
                </div>
            </div>

            <!-- LEFT COLUMN: LIABILITIES & EQUITY (الخصوم وحقوق الملكية) -->
            <div class="erp-card overflow-hidden">
                <div class="p-4 bg-[var(--warning-soft)] border-b border-[var(--border-color)] flex justify-between items-center">
                    <div class="font-bold text-sm text-[var(--warning-text)] flex items-center gap-2">
                        <x-icon name="shield" size="16" />
                        <span>جانب الخصوم وحقوق الملكية (Liabilities & Equity)</span>
                    </div>
                    <span class="text-xs font-numeric font-bold text-[var(--warning-text)]">
                        {{ number_format($report['total_liabilities_and_equity'], 2) }} ج.م
                    </span>
                </div>

                <div class="p-4 space-y-5 text-xs">
                    <!-- Current Liabilities -->
                    <div>
                        <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                            <span>أولاً: الخصوم المتداولة (Current Liabilities)</span>
                            <span class="font-numeric font-bold text-[var(--danger)]">{{ number_format($report['total_current_liabilities'], 2) }} ج.م</span>
                        </div>
                        <div class="space-y-1.5 pr-3">
                            @forelse($report['current_liabilities'] as $liab)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-light)]">
                                    <a href="{{ route('accounting.accounts.ledger', $liab['id']) }}" class="text-[var(--text-secondary)] hover:text-[var(--primary)] font-medium">
                                        {{ $liab['name'] }} [{{ $liab['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold text-[var(--danger)]">{{ number_format($liab['balance'], 2) }} ج.م</span>
                                </div>
                            @empty
                                <div class="text-[var(--text-muted)] italic py-1">لا توجد التزامات متداولة.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Non-Current Liabilities -->
                    @if($report['total_non_current_liabilities'] > 0)
                        <div>
                            <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                                <span>ثانياً: الخصوم طويلة الأجل</span>
                                <span class="font-numeric font-bold text-[var(--danger)]">{{ number_format($report['total_non_current_liabilities'], 2) }} ج.م</span>
                            </div>
                            <div class="space-y-1.5 pr-3">
                                @foreach($report['non_current_liabilities'] as $liab)
                                    <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-light)]">
                                        <span class="text-[var(--text-secondary)]">{{ $liab['name'] }} [{{ $liab['code'] }}]</span>
                                        <span class="font-numeric font-semibold text-[var(--danger)]">{{ number_format($liab['balance'], 2) }} ج.م</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Equity -->
                    <div>
                        <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                            <span>ثالثاً: حقوق الملكية (Owners' Equity)</span>
                            <span class="font-numeric font-bold text-[var(--primary)]">{{ number_format($report['total_equity'], 2) }} ج.م</span>
                        </div>
                        <div class="space-y-1.5 pr-3">
                            @foreach($report['equity_items'] as $eq)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-light)]">
                                    <a href="{{ route('accounting.accounts.ledger', $eq['id']) }}" class="text-[var(--text-secondary)] hover:text-[var(--primary)] font-medium">
                                        {{ $eq['name'] }} [{{ $eq['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold text-[var(--text-main)]">{{ number_format($eq['balance'], 2) }} ج.م</span>
                                </div>
                            @endforeach

                            <!-- Current Period Net Income -->
                            <div class="flex justify-between items-center py-1.5 bg-[var(--success-soft)] px-2.5 rounded border border-[var(--success-border)] font-bold">
                                <span class="text-[var(--success-text)]">صافي أرباح الفترة الحالية (من قائمة الدخل):</span>
                                <span class="font-numeric text-[var(--success-text)]">{{ number_format($report['current_period_net_profit'], 2) }} ج.م</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-[var(--primary)] text-white flex justify-between items-center font-bold text-sm">
                    <span>إجمالي الخصوم وحقوق الملكية:</span>
                    <span class="font-numeric text-base">{{ number_format($report['total_liabilities_and_equity'], 2) }} ج.م</span>
                </div>
            </div>
        </div>
    </div>
@endsection
