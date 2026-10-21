@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="ميزان المراجعة (Trial Balance)" :breadcrumbs="['المالية والحسابات' => route('accounting.chart-of-accounts'), 'ميزان المراجعة' => route('accounting.reports.trial-balance')]">
        <x-slot:actions>
            <a href="{{ route('accounting.reports.trial-balance.print', request()->query()) }}" target="_blank" class="erp-btn erp-btn-secondary">
                <x-icon name="printer" size="16" />
                <span>طباعة التقرير</span>
            </a>
            <a href="{{ route('accounting.chart-of-accounts') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="list" size="16" />
                <span>دليل الحسابات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            @if($report['totals']['is_balanced'])
                <span class="erp-badge erp-badge-success font-numeric px-3 py-1 text-xs">
                    <x-icon name="check-circle" size="13" />
                    <span class="mr-1">الميزان متوازن ومطابق بنسبة 100%</span>
                </span>
            @else
                <span class="erp-badge erp-badge-danger font-numeric px-3 py-1 text-xs">
                    <x-icon name="alert-triangle" size="13" />
                    <span class="mr-1">يوجد فارق غير متوازن: {{ number_format($report['totals']['diff'], 2) }} ج.م</span>
                </span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="trial-balance">
        @include('accounting.nav-tabs')

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="erp-card p-4 border-r-4 border-r-[var(--primary)]">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>حركات الفترة (مدين)</span>
                    <x-icon name="trending-up" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-[var(--text-main)]">
                    {{ number_format($report['totals']['period_debit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-emerald-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>حركات الفترة (دائن)</span>
                    <x-icon name="trending-down" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-emerald-600 dark:text-emerald-400">
                    {{ number_format($report['totals']['period_credit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-blue-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>إجمالي الأرصدة الختامية</span>
                    <x-icon name="layers" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-blue-600 dark:text-blue-400">
                    {{ number_format($report['totals']['closing_debit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 {{ $report['totals']['is_balanced'] ? 'border-r-emerald-600 bg-emerald-50/30 dark:bg-emerald-950/20' : 'border-r-rose-600 bg-rose-50/30 dark:bg-rose-950/20' }}">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>حالة التوازن المحاسبي</span>
                    <x-icon name="{{ $report['totals']['is_balanced'] ? 'shield' : 'alert-octagon' }}" size="16" />
                </div>
                <div class="text-lg font-bold font-numeric {{ $report['totals']['is_balanced'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                    {{ $report['totals']['is_balanced'] ? 'متوازن تماماً (0.00)' : 'فارق: '.number_format($report['totals']['diff'], 2).' ج.م' }}
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="erp-card p-4 mb-6">
            <form method="GET" action="{{ route('accounting.reports.trial-balance') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $currentDateFrom }}" class="erp-input w-full font-numeric text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $currentDateTo }}" class="erp-input w-full font-numeric text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-[var(--text-muted)]">مستوى العرض</label>
                    <select name="level" class="erp-input w-full text-xs">
                        <option value="">كافة المستويات (تفصيلي)</option>
                        <option value="1" {{ $currentLevel === 1 ? 'selected' : '' }}>المستوى الأول (الحسابات الرئيسية)</option>
                        <option value="2" {{ $currentLevel === 2 ? 'selected' : '' }}>المستوى الثاني (المجموعات)</option>
                        <option value="3" {{ $currentLevel === 3 ? 'selected' : '' }}>المستوى الثالث (الحسابات العامة)</option>
                    </select>
                </div>

                <div class="flex items-center pb-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold select-none">
                        <input type="checkbox" name="show_zero" value="1" {{ $currentShowZero ? 'checked' : '' }} class="rounded border-gray-300 text-[var(--primary)] focus:ring-[var(--primary)]">
                        <span>إظهار الحسابات الصفرية</span>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="erp-btn erp-btn-primary flex-1 justify-center text-xs">
                        <x-icon name="filter" size="14" />
                        <span>تطبيق الفلترة</span>
                    </button>
                    <a href="{{ route('accounting.reports.trial-balance') }}" class="erp-btn erp-btn-secondary text-xs" title="إعادة ضبط">
                        <x-icon name="rotate-ccw" size="14" />
                    </a>
                </div>
            </form>
        </div>

        <!-- Trial Balance Table -->
        <div class="erp-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-xs">
                    <thead>
                        <tr class="bg-[var(--bg-surface-alt)] border-b border-[var(--border-color)]">
                            <th rowspan="2" class="p-3 text-right font-bold w-28">كود الحساب</th>
                            <th rowspan="2" class="p-3 text-right font-bold">اسم الحساب</th>
                            <th rowspan="2" class="p-3 text-center font-bold w-24">النوع / الطبيعة</th>
                            <th colspan="2" class="p-2 text-center font-bold border-l border-r border-[var(--border-color)] bg-black/5 dark:bg-white/5">الرصيد الافتتاحي</th>
                            <th colspan="2" class="p-2 text-center font-bold border-r border-[var(--border-color)] bg-blue-50/50 dark:bg-blue-950/20">حركات الفترة</th>
                            <th colspan="2" class="p-2 text-center font-bold border-r border-[var(--border-color)] bg-emerald-50/50 dark:bg-emerald-950/20">الرصيد الختامي</th>
                            <th rowspan="2" class="p-3 text-center font-bold w-24">كشف الأستاذ</th>
                        </tr>
                        <tr class="bg-[var(--bg-surface-alt)] border-b border-[var(--border-color)] text-[11px] text-[var(--text-muted)]">
                            <th class="p-2 text-center border-l border-[var(--border-color)]">مدين</th>
                            <th class="p-2 text-center border-r border-[var(--border-color)]">دائن</th>
                            <th class="p-2 text-center">مدين</th>
                            <th class="p-2 text-center border-r border-[var(--border-color)]">دائن</th>
                            <th class="p-2 text-center">مدين</th>
                            <th class="p-2 text-center border-r border-[var(--border-color)]">دائن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-color)]">
                        @forelse($report['accounts'] as $item)
                            <tr class="hover:bg-[var(--bg-surface-alt)] transition-colors {{ $item['level'] === 1 ? 'font-bold bg-black/[0.02] dark:bg-white/[0.02]' : ($item['level'] === 2 ? 'font-semibold' : '') }}">
                                <td class="p-3 font-numeric text-[var(--text-muted)]">
                                    <span class="inline-block px-1.5 py-0.5 rounded {{ $item['level'] === 1 ? 'bg-[var(--primary)]/10 text-[var(--primary)] font-bold' : '' }}">
                                        {{ $item['code'] }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div style="padding-right: {{ ($item['level'] - 1) * 16 }}px;" class="flex items-center gap-1.5">
                                        @if($item['level'] > 1)
                                            <span class="text-[var(--text-muted)] text-[10px]">↳</span>
                                        @endif
                                        <a href="{{ route('accounting.accounts.ledger', $item['id']) }}" class="hover:text-[var(--primary)] hover:underline">
                                            {{ $item['name'] }}
                                        </a>
                                        @if(! $item['is_leaf'])
                                            <span class="text-[10px] text-[var(--text-muted)] font-normal">(رئيسي)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="erp-badge text-[10px] py-0.5 px-2 {{ $item['nature'] === 'debit' ? 'erp-badge-info' : 'erp-badge-success' }}">
                                        {{ $item['nature'] === 'debit' ? 'مدين' : 'دائن' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center font-numeric border-l border-[var(--border-color)]">
                                    {{ $item['opening_debit'] > 0 ? number_format($item['opening_debit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric border-r border-[var(--border-color)]">
                                    {{ $item['opening_credit'] > 0 ? number_format($item['opening_credit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric text-blue-600 dark:text-blue-400">
                                    {{ $item['period_debit'] > 0 ? number_format($item['period_debit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric text-blue-600 dark:text-blue-400 border-r border-[var(--border-color)]">
                                    {{ $item['period_credit'] > 0 ? number_format($item['period_credit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $item['closing_debit'] > 0 ? number_format($item['closing_debit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric font-bold text-emerald-600 dark:text-emerald-400 border-r border-[var(--border-color)]">
                                    {{ $item['closing_credit'] > 0 ? number_format($item['closing_credit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('accounting.accounts.ledger', $item['id']) }}" class="erp-btn erp-btn-ghost text-xs py-0.5 px-2" title="عرض كشف حركة الحساب في الأستاذ">
                                        <x-icon name="eye" size="14" />
                                        <span>كشف</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-[var(--text-muted)]">
                                    لا توجد حسابات مطابقة لمعايير البحث الحالية.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-black/5 dark:bg-white/5 font-bold border-t-2 border-[var(--border-color)] text-xs font-numeric">
                            <td colspan="3" class="p-3 text-right">
                                الإجمالي العام المتوازن (مستوى الحسابات الرئيسية):
                            </td>
                            <td class="p-3 text-center border-l border-[var(--border-color)]">
                                {{ number_format($report['totals']['opening_debit'], 2) }}
                            </td>
                            <td class="p-3 text-center border-r border-[var(--border-color)]">
                                {{ number_format($report['totals']['opening_credit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-blue-600 dark:text-blue-400">
                                {{ number_format($report['totals']['period_debit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-blue-600 dark:text-blue-400 border-r border-[var(--border-color)]">
                                {{ number_format($report['totals']['period_credit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-emerald-600 dark:text-emerald-400">
                                {{ number_format($report['totals']['closing_debit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-emerald-600 dark:text-emerald-400 border-r border-[var(--border-color)]">
                                {{ number_format($report['totals']['closing_credit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-[10px] {{ $report['totals']['is_balanced'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $report['totals']['is_balanced'] ? 'متوازن 100%' : 'فارق: '.number_format($report['totals']['diff'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
