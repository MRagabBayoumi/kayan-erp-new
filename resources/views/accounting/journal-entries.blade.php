@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="دفتر قيود اليومية العامة (General Journal)" :breadcrumbs="['المالية والحسابات' => route('treasuries.index'), 'شجرة الحسابات' => route('accounting.chart-of-accounts'), 'دفتر القيود' => route('accounting.journal-entries')]">
        <x-slot:actions>
            <a href="{{ route('accounting.chart-of-accounts') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="list" size="16" />
                <span>شجرة الحسابات</span>
            </a>
            <form action="{{ route('accounting.recalculate-balances') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="erp-btn erp-btn-secondary" title="إعادة احتساب وتحديث مجاميع الأرصدة في الشجرة بالكامل">
                    <x-icon name="refresh-cw" size="16" />
                    <span>تحديث أرصدة الشجرة</span>
                </button>
            </form>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-success font-numeric px-3 py-1 text-xs">
                <span>إجمالي القيود المرحلة:</span>
                <strong class="mr-1">{{ $metrics['total_posted'] }} قيد</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="journal-entries">
        @include('accounting.nav-tabs')

        @if(session('message'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check-circle" size="18" />
                <div>{{ session('message') }}</div>
            </div>
        @endif

        <!-- Summary KPIs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mb-5">
            <div class="erp-card p-3.5 border-r-4 border-r-primary hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1">إجمالي القيود المسجلة</div>
                <div class="font-numeric text-xl font-black text-foreground">{{ number_format($metrics['total_entries']) }}</div>
            </div>

            <div class="erp-card p-3.5 border-r-4 border-r-emerald-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1">إجمالي حركة المدين (Debit)</div>
                <div class="font-numeric text-xl font-black text-emerald-600 dark:text-emerald-400">
                    {{ number_format($metrics['sum_debit'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <div class="erp-card p-3.5 border-r-4 border-r-cyan-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1">إجمالي حركة الدائن (Credit)</div>
                <div class="font-numeric text-xl font-black text-cyan-600 dark:text-cyan-400">
                    {{ number_format($metrics['sum_credit'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <div class="erp-card p-3.5 border-r-4 {{ abs($metrics['sum_debit'] - $metrics['sum_credit']) < 0.01 ? 'border-r-emerald-600' : 'border-r-rose-600' }} hover:shadow-md transition-shadow bg-surface-alt/40">
                <div class="text-[11px] font-bold text-muted mb-1">التوازن المحاسبي العام</div>
                <div class="flex items-center gap-2 mt-1">
                    @if(abs($metrics['sum_debit'] - $metrics['sum_credit']) < 0.01)
                        <span class="erp-badge erp-badge-success text-xs font-bold py-1 px-2.5">
                            متزن 100% (توازن تام)
                        </span>
                    @else
                        <span class="erp-badge erp-badge-danger text-xs font-bold py-1 px-2.5">
                            فارق: {{ number_format(abs($metrics['sum_debit'] - $metrics['sum_credit']), 2) }} ج.م
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Search & Filter Card -->
        <div class="erp-card mb-5 p-4 shadow-sm">
            <form action="{{ route('accounting.journal-entries') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <!-- Search term -->
                <div>
                    <label class="erp-label text-xs">بحث بالقيد أو الوصف أو الحساب</label>
                    <input type="text" name="search" value="{{ $currentSearch }}" placeholder="رقم القيد، البيان، اسم الحساب..." class="erp-input text-xs w-full">
                </div>

                <!-- Reference Type -->
                <div>
                    <label class="erp-label text-xs">نوع المستند المصدري</label>
                    <select name="reference_type" class="erp-input text-xs w-full">
                        <option value="">-- كافة المستندات --</option>
                        <option value="sales_invoice" {{ $currentRefType === 'sales_invoice' ? 'selected' : '' }}>فاتورة مبيعات</option>
                        <option value="sales_return" {{ $currentRefType === 'sales_return' ? 'selected' : '' }}>مردود مبيعات</option>
                        <option value="purchase_invoice" {{ $currentRefType === 'purchase_invoice' ? 'selected' : '' }}>فاتورة مشتريات</option>
                        <option value="purchase_return" {{ $currentRefType === 'purchase_return' ? 'selected' : '' }}>مردود مشتريات</option>
                        <option value="customer_payment" {{ $currentRefType === 'customer_payment' ? 'selected' : '' }}>سند قبض عميل</option>
                        <option value="supplier_payment" {{ $currentRefType === 'supplier_payment' ? 'selected' : '' }}>سند صرف مورد</option>
                        <option value="expense" {{ $currentRefType === 'expense' ? 'selected' : '' }}>سند مصروفات</option>
                        <option value="opening_balance" {{ $currentRefType === 'opening_balance' ? 'selected' : '' }}>رصيد افتتاحي</option>
                        <option value="manual" {{ $currentRefType === 'manual' ? 'selected' : '' }}>قيد تسوية / يدوي</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="erp-label text-xs">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $currentDateFrom }}" class="erp-input text-xs w-full">
                </div>

                <!-- Date To -->
                <div>
                    <label class="erp-label text-xs">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $currentDateTo }}" class="erp-input text-xs w-full">
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="erp-btn erp-btn-primary text-xs flex-1">
                        <x-icon name="search" size="14" />
                        <span>تصفية</span>
                    </button>
                    <a href="{{ route('accounting.journal-entries') }}" class="erp-btn erp-btn-secondary text-xs" title="إلغاء التصفية">
                        <x-icon name="x" size="14" />
                    </a>
                </div>
            </form>
        </div>

        <!-- Journal Entries Table -->
        <div class="erp-card overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-right text-xs">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">#</th>
                            <th class="w-32">رقم القيد</th>
                            <th class="w-28">التاريخ</th>
                            <th class="w-36">المستند المصدري</th>
                            <th>البيان والتفاصيل</th>
                            <th class="text-left font-numeric w-32">إجمالي المدين</th>
                            <th class="text-left font-numeric w-32">إجمالي الدائن</th>
                            <th class="text-center w-28">الحالة</th>
                            <th class="text-center w-16">السطور</th>
                            <th class="w-36 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40">
                        @forelse($entries as $index => $entry)
                            <tr class="hover:bg-surface-hover/60 transition-colors">
                                <td class="text-center text-muted font-numeric">{{ $entries->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('accounting.journal-entries.show', $entry) }}" 
                                       class="font-mono font-bold text-foreground bg-surface px-2.5 py-1 rounded border border-border hover:border-primary hover:text-primary transition-colors inline-block">
                                        {{ $entry->entry_number }}
                                    </a>
                                </td>
                                <td class="font-numeric text-muted whitespace-nowrap">
                                    {{ $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '—' }}
                                </td>
                                <td>
                                    <span class="erp-badge erp-badge-neutral text-[11px] font-medium">
                                        {{ $entry->reference_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-medium text-foreground text-xs leading-relaxed max-w-md">
                                        {{ $entry->description }}
                                    </div>
                                    @if($entry->creator)
                                        <div class="text-[10px] text-muted mt-0.5">بواسطة: {{ $entry->creator->name }}</div>
                                    @endif
                                </td>
                                <td class="text-left font-numeric font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                    {{ number_format((float) $entry->total_debit, 2) }}
                                </td>
                                <td class="text-left font-numeric font-bold text-cyan-600 dark:text-cyan-400 text-xs">
                                    {{ number_format((float) $entry->total_credit, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="erp-badge {{ $entry->status_badge_class }} text-[10px]">
                                        {{ $entry->status_label }}
                                    </span>
                                </td>
                                <td class="text-center font-numeric">
                                    <span class="px-2 py-0.5 rounded-full bg-surface border border-border text-[11px] font-bold text-muted">
                                        {{ $entry->lines->count() }}
                                    </span>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- View Full Page -->
                                        <a href="{{ route('accounting.journal-entries.show', $entry) }}" 
                                           class="erp-btn erp-btn-secondary text-xs px-2.5 py-1" 
                                           title="عرض سند القيد في صفحة مستقلة">
                                            <x-icon name="eye" size="14" />
                                            <span>عرض</span>
                                        </a>

                                        <!-- Print Voucher Directly -->
                                        <a href="{{ route('accounting.journal-entries.print', $entry) }}" 
                                           target="_blank"
                                           class="erp-btn erp-btn-secondary text-xs px-2 py-1 text-primary hover:bg-primary/10" 
                                           title="طباعة سند القيد">
                                            <x-icon name="printer" size="14" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-12 text-muted">
                                    <x-icon name="file-text" size="40" class="mx-auto mb-2 opacity-30" />
                                    <p class="text-sm font-semibold">لا توجد قيود يومية مسجلة مطابقة لمعايير البحث الحالية.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($entries->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
