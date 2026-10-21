@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="كشف حساب الأستاذ العام: {{ $account->name }} [{{ $account->code }}]" :breadcrumbs="['المالية والحسابات' => route('accounting.chart-of-accounts'), 'دليل الحسابات' => route('accounting.chart-of-accounts'), $account->name => '#']">
        <x-slot:actions>
            <a href="{{ route('accounting.accounts.ledger.print', array_merge(['account' => $account->id], request()->query())) }}" target="_blank" class="erp-btn erp-btn-secondary">
                <x-icon name="printer" size="16" />
                <span>طباعة كشف الحساب</span>
            </a>
            <a href="{{ route('accounting.chart-of-accounts') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="list" size="16" />
                <span>شجرة الحسابات</span>
            </a>
            <a href="{{ route('accounting.reports.trial-balance') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="layers" size="16" />
                <span>ميزان المراجعة</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge font-numeric px-3 py-1 text-xs {{ $account->nature === 'debit' ? 'erp-badge-info' : 'erp-badge-success' }}">
                <span>طبيعة الحساب:</span>
                <strong class="mr-1">{{ $account->nature_label }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="account-ledger">
        @include('accounting.nav-tabs')

        <!-- Account Info & KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="erp-card p-4 border-r-4 border-r-slate-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>الرصيد الافتتاحي (قبل الفترة)</span>
                    <x-icon name="calendar" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-[var(--text-main)]">
                    {{ number_format($ledger['opening_balance'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    {{ $ledger['opening_balance'] >= 0 ? ($account->nature === 'debit' ? 'مدين' : 'دائن') : 'عكس الطبيعة' }}
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-blue-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>إجمالي حركات المدين (+)</span>
                    <x-icon name="trending-up" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-blue-600 dark:text-blue-400">
                    {{ number_format($ledger['total_debit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    إجمالي الجانب المدين
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-emerald-500">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>إجمالي حركات الدائن (-)</span>
                    <x-icon name="trending-down" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-emerald-600 dark:text-emerald-400">
                    {{ number_format($ledger['total_credit'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    إجمالي الجانب الدائن
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-[var(--primary)] bg-[var(--primary)]/5">
                <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mb-1">
                    <span>الرصيد الختامي التراكمي</span>
                    <x-icon name="award" size="16" />
                </div>
                <div class="text-xl font-bold font-numeric text-[var(--primary)]">
                    {{ number_format($ledger['closing_balance'], 2) }} <small class="text-xs font-normal">ج.م</small>
                </div>
                <div class="text-[11px] text-[var(--text-muted)] mt-1">
                    {{ $ledger['closing_balance'] >= 0 ? ($account->nature === 'debit' ? 'رصيد مدين' : 'رصيد دائن') : 'رصيد شاذ (عكس الطبيعة)' }}
                </div>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="erp-card p-4 mb-6">
            <form method="GET" action="{{ route('accounting.accounts.ledger', $account) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
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
                        <span>تحديث الكشف</span>
                    </button>
                    <a href="{{ route('accounting.accounts.ledger', $account) }}" class="erp-btn erp-btn-secondary text-xs" title="إعادة ضبط">
                        <x-icon name="rotate-ccw" size="14" />
                    </a>
                </div>
            </form>
        </div>

        <!-- General Ledger Movements Table -->
        <div class="erp-card overflow-hidden">
            <div class="p-4 bg-[var(--bg-surface-alt)] border-b border-[var(--border-color)] flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-sm">حركات حساب الأستاذ العام</h3>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">
                        {{ $account->name }} &bull; كود: {{ $account->code }} &bull; النوع: {{ $account->type_label }}
                    </p>
                </div>
                <span class="text-xs font-numeric text-[var(--text-muted)]">
                    عدد الحركات: {{ count($ledger['lines']) }} حركة
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="erp-table w-full text-xs">
                    <thead>
                        <tr class="bg-[var(--bg-surface-alt)] border-b border-[var(--border-color)]">
                            <th class="p-3 text-right font-bold w-28">التاريخ</th>
                            <th class="p-3 text-right font-bold w-36">رقم القيد / المرجع</th>
                            <th class="p-3 text-right font-bold">البيان والشرح</th>
                            <th class="p-3 text-right font-bold w-36">الطرف المقابل</th>
                            <th class="p-3 text-center font-bold w-28 text-blue-600 dark:text-blue-400">مدين (+)</th>
                            <th class="p-3 text-center font-bold w-28 text-emerald-600 dark:text-emerald-400">دائن (-)</th>
                            <th class="p-3 text-center font-bold w-32">الرصيد التراكمي</th>
                            <th class="p-3 text-center font-bold w-20">معاينة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-color)]">
                        <!-- Opening balance row if filtered -->
                        @if($currentDateFrom && $ledger['opening_balance'] != 0)
                            <tr class="bg-black/[0.02] dark:bg-white/[0.02] font-semibold italic text-[var(--text-muted)]">
                                <td class="p-3 font-numeric">{{ $currentDateFrom }}</td>
                                <td class="p-3 font-numeric">-</td>
                                <td class="p-3" colspan="2">الرصيد الافتتاحي المنقول قبل تاريخ {{ $currentDateFrom }}</td>
                                <td class="p-3 text-center font-numeric">{{ $account->nature === 'debit' && $ledger['opening_balance'] > 0 ? number_format($ledger['opening_balance'], 2) : '-' }}</td>
                                <td class="p-3 text-center font-numeric">{{ $account->nature === 'credit' && $ledger['opening_balance'] > 0 ? number_format($ledger['opening_balance'], 2) : '-' }}</td>
                                <td class="p-3 text-center font-numeric font-bold text-[var(--text-main)]">{{ number_format($ledger['opening_balance'], 2) }}</td>
                                <td class="p-3 text-center">-</td>
                            </tr>
                        @endif

                        @forelse($ledger['lines'] as $line)
                            <tr class="hover:bg-[var(--bg-surface-alt)] transition-colors">
                                <td class="p-3 font-numeric text-[var(--text-muted)]">{{ $line['entry_date'] }}</td>
                                <td class="p-3 font-numeric font-semibold">
                                    <a href="{{ route('accounting.journal-entries.show', $line['entry_id']) }}" class="hover:text-[var(--primary)] hover:underline flex items-center gap-1">
                                        <x-icon name="file-text" size="13" />
                                        <span>{{ $line['entry_number'] }}</span>
                                    </a>
                                </td>
                                <td class="p-3">
                                    <div class="font-medium text-[var(--text-main)]">{{ $line['description'] }}</div>
                                    @if($line['reference_number'])
                                        <div class="text-[11px] text-[var(--text-muted)] font-numeric">مرجع: {{ $line['reference_number'] }}</div>
                                    @endif
                                </td>
                                <td class="p-3 text-[var(--text-muted)]">
                                    {{ $line['party_name'] ?: '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric text-blue-600 dark:text-blue-400 font-semibold">
                                    {{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric text-emerald-600 dark:text-emerald-400 font-semibold">
                                    {{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-center font-numeric font-bold text-[var(--text-main)] bg-black/[0.01] dark:bg-white/[0.01]">
                                    {{ number_format($line['running_balance'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('accounting.journal-entries.show', $line['entry_id']) }}" class="erp-btn erp-btn-ghost text-xs p-1" title="معاينة سند القيد">
                                        <x-icon name="eye" size="14" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-[var(--text-muted)]">
                                    لا توجد حركات مسجلة لهذا الحساب خلال الفترة المحددة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-black/5 dark:bg-white/5 font-bold border-t-2 border-[var(--border-color)] text-xs font-numeric">
                            <td colspan="4" class="p-3 text-right">
                                إجمالي الحركات والرصيد الختامي:
                            </td>
                            <td class="p-3 text-center text-blue-600 dark:text-blue-400">
                                {{ number_format($ledger['total_debit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-emerald-600 dark:text-emerald-400">
                                {{ number_format($ledger['total_credit'], 2) }}
                            </td>
                            <td class="p-3 text-center text-base text-[var(--primary)] font-extrabold">
                                {{ number_format($ledger['closing_balance'], 2) }}
                            </td>
                            <td class="p-3 text-center text-[10px] text-[var(--text-muted)]">
                                {{ $ledger['closing_nature'] === 'debit' ? 'مدين' : 'دائن' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
