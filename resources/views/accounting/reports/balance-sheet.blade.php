@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="الميزانية العمومية والمركز المالي (Balance Sheet)" :breadcrumbs="['المالية والحسابات' => route('accounting.chart-of-accounts'), 'الميزانية العمومية' => route('accounting.reports.balance-sheet')]">
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="erp-card p-4 border-r-4 border-r-blue-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>إجمالي الأصول (Assets)</span>
                    <x-icon name="layers" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-blue-600 dark:text-blue-400">
                    {{ number_format($report['total_assets'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    أصول متداولة: {{ number_format($report['total_current_assets'], 2) }} ج.م
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-rose-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>إجمالي الخصوم والالتزامات</span>
                    <x-icon name="credit-card" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-rose-600 dark:text-rose-400">
                    {{ number_format($report['total_liabilities'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    مستحقات الموردين والالتزامات
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-purple-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>إجمالي حقوق الملكية (Equity)</span>
                    <x-icon name="shield" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-purple-600 dark:text-purple-400">
                    {{ number_format($report['total_equity'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    تشمل ربح الفترة: {{ number_format($report['current_period_net_profit'], 2) }} ج.م
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 {{ $report['is_balanced'] ? 'border-r-emerald-600 bg-emerald-50/20 dark:bg-emerald-950/20' : 'border-r-rose-600 bg-rose-50/20 dark:bg-rose-950/20' }}">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>معادلة الميزانية (توازن المركز المالي)</span>
                    <x-icon name="{{ $report['is_balanced'] ? 'check-circle' : 'alert-octagon' }}" size="16" />
                </div>
                <div class="text-lg font-bold font-numeric {{ $report['is_balanced'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                    {{ $report['is_balanced'] ? 'الأصول = الخصوم + الملكية' : 'فارق غير متوازن: '.number_format($report['diff'], 2).' ج.م' }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    {{ $report['is_balanced'] ? 'متطابقة بنسبة 100%' : 'يرجى مراجعة قيود اليومية' }}
                </div>
            </div>
        </div>

        <!-- Date Filter Toolbar -->
        <div class="erp-card p-4 mb-6">
            <form method="GET" action="{{ route('accounting.reports.balance-sheet') }}" class="flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-64">
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">حتى تاريخ (As of Date)</label>
                    <input type="date" name="as_of_date" value="{{ $currentAsOfDate }}" class="erp-input w-full font-numeric text-xs">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- RIGHT COLUMN: ASSETS (الأصول) -->
            <div class="erp-card overflow-hidden">
                <div class="p-4 bg-blue-50/50 dark:bg-blue-950/20 border-b border-[var(--border-color)] flex justify-between items-center">
                    <div class="font-bold text-sm text-blue-700 dark:text-blue-400 flex items-center gap-2">
                        <x-icon name="briefcase" size="16" />
                        <span>الأصول (Assets)</span>
                    </div>
                    <span class="text-xs font-numeric font-semibold text-blue-600 dark:text-blue-400">
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
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                    <a href="{{ route('accounting.accounts.ledger', $asset['id']) }}" class="text-[var(--text-muted)] hover:text-[var(--primary)] hover:underline">
                                        {{ $asset['name'] }} [{{ $asset['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold">{{ number_format($asset['balance'], 2) }} ج.م</span>
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
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                    <a href="{{ route('accounting.accounts.ledger', $asset['id']) }}" class="text-[var(--text-muted)] hover:text-[var(--primary)] hover:underline">
                                        {{ $asset['name'] }} [{{ $asset['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold">{{ number_format($asset['balance'], 2) }} ج.م</span>
                                </div>
                            @empty
                                <div class="text-[var(--text-muted)] italic py-1">لا توجد أصول ثابتة مسجلة حالياً.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-blue-600 text-white flex justify-between items-center font-bold text-sm">
                    <span>إجمالي الأصول (Total Assets):</span>
                    <span class="font-numeric text-base">{{ number_format($report['total_assets'], 2) }} ج.م</span>
                </div>
            </div>

            <!-- LEFT COLUMN: LIABILITIES & EQUITY (الخصوم وحقوق الملكية) -->
            <div class="erp-card overflow-hidden">
                <div class="p-4 bg-rose-50/50 dark:bg-rose-950/20 border-b border-[var(--border-color)] flex justify-between items-center">
                    <div class="font-bold text-sm text-rose-700 dark:text-rose-400 flex items-center gap-2">
                        <x-icon name="shield" size="16" />
                        <span>الخصوم وحقوق الملكية (Liabilities & Equity)</span>
                    </div>
                    <span class="text-xs font-numeric font-semibold text-rose-600 dark:text-rose-400">
                        {{ number_format($report['total_liabilities_and_equity'], 2) }} ج.م
                    </span>
                </div>

                <div class="p-4 space-y-5 text-xs">
                    <!-- Current Liabilities -->
                    <div>
                        <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                            <span>أولاً: الخصوم المتداولة (Current Liabilities)</span>
                            <span class="font-numeric font-bold text-rose-600">{{ number_format($report['total_current_liabilities'], 2) }} ج.م</span>
                        </div>
                        <div class="space-y-1.5 pr-3">
                            @forelse($report['current_liabilities'] as $liab)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                    <a href="{{ route('accounting.accounts.ledger', $liab['id']) }}" class="text-[var(--text-muted)] hover:text-[var(--primary)] hover:underline">
                                        {{ $liab['name'] }} [{{ $liab['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold text-rose-600">{{ number_format($liab['balance'], 2) }} ج.م</span>
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
                                <span class="font-numeric font-bold text-rose-600">{{ number_format($report['total_non_current_liabilities'], 2) }} ج.م</span>
                            </div>
                            <div class="space-y-1.5 pr-3">
                                @foreach($report['non_current_liabilities'] as $liab)
                                    <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                        <span>{{ $liab['name'] }} [{{ $liab['code'] }}]</span>
                                        <span class="font-numeric font-semibold text-rose-600">{{ number_format($liab['balance'], 2) }} ج.م</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Equity -->
                    <div>
                        <div class="font-bold text-xs text-[var(--text-main)] mb-2 flex justify-between border-b pb-1 border-[var(--border-color)]">
                            <span>ثالثاً: حقوق الملكية (Owners' Equity)</span>
                            <span class="font-numeric font-bold text-purple-600">{{ number_format($report['total_equity'], 2) }} ج.م</span>
                        </div>
                        <div class="space-y-1.5 pr-3">
                            @foreach($report['equity_items'] as $eq)
                                <div class="flex justify-between items-center py-1 border-b border-dashed border-[var(--border-color)]">
                                    <a href="{{ route('accounting.accounts.ledger', $eq['id']) }}" class="text-[var(--text-muted)] hover:text-[var(--primary)] hover:underline">
                                        {{ $eq['name'] }} [{{ $eq['code'] }}]
                                    </a>
                                    <span class="font-numeric font-semibold">{{ number_format($eq['balance'], 2) }} ج.م</span>
                                </div>
                            @endforeach

                            <!-- Current Period Net Income -->
                            <div class="flex justify-between items-center py-1.5 bg-emerald-50/50 dark:bg-emerald-950/20 px-2 rounded border border-emerald-200/50 font-bold">
                                <span class="text-emerald-700 dark:text-emerald-400">صافي أرباح الفترة الحالية (من قائمة الدخل):</span>
                                <span class="font-numeric text-emerald-700 dark:text-emerald-400">{{ number_format($report['current_period_net_profit'], 2) }} ج.م</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-900 text-white flex justify-between items-center font-bold text-sm">
                    <span>إجمالي الخصوم وحقوق الملكية:</span>
                    <span class="font-numeric text-base">{{ number_format($report['total_liabilities_and_equity'], 2) }} ج.م</span>
                </div>
            </div>
        </div>
    </div>
@endsection
