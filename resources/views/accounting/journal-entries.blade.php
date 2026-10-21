@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="دفتر قيود اليومية العامة (General Journal)" :breadcrumbs="['المالية والحسابات' => route('treasuries.index'), 'شجرة الحسابات' => route('accounting.chart-of-accounts'), 'قيود اليومية' => route('accounting.journal-entries')]">
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
        @if(session('message'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check-circle" size="18" />
                <div>{{ session('message') }}</div>
            </div>
        @endif

        <!-- Summary KPIs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="erp-card p-4 border-r-4 border-r-primary">
                <div class="text-xs text-muted mb-1">إجمالي القيود المسجلة</div>
                <div class="font-numeric text-2xl font-black text-foreground">{{ number_format($metrics['total_entries']) }}</div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-emerald-500">
                <div class="text-xs text-muted mb-1">إجمالي المدين (Debits)</div>
                <div class="font-numeric text-xl font-black text-emerald-600 dark:text-emerald-400">
                    {{ number_format($metrics['sum_debit'], 2) }}
                    <span class="text-xs text-muted font-normal">ج.م</span>
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 border-r-cyan-500">
                <div class="text-xs text-muted mb-1">إجمالي الدائن (Credits)</div>
                <div class="font-numeric text-xl font-black text-cyan-600 dark:text-cyan-400">
                    {{ number_format($metrics['sum_credit'], 2) }}
                    <span class="text-xs text-muted font-normal">ج.م</span>
                </div>
            </div>

            <div class="erp-card p-4 border-r-4 {{ abs($metrics['sum_debit'] - $metrics['sum_credit']) < 0.01 ? 'border-r-emerald-600' : 'border-r-rose-600' }}">
                <div class="text-xs text-muted mb-1">حالة التوازن المحاسبي العام</div>
                <div class="flex items-center gap-2 mt-1">
                    @if(abs($metrics['sum_debit'] - $metrics['sum_credit']) < 0.01)
                        <span class="erp-badge erp-badge-success text-xs font-bold">متزن 100% (توازن تام)</span>
                    @else
                        <span class="erp-badge erp-badge-danger text-xs font-bold">فارق: {{ number_format(abs($metrics['sum_debit'] - $metrics['sum_credit']), 2) }} ج.م</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Search & Filter Card -->
        <div class="erp-card mb-6 p-4">
            <form action="{{ route('accounting.journal-entries') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <!-- Search term -->
                <div>
                    <label class="erp-label text-xs">بحث بالقيد أو الوصف أو الحساب</label>
                    <input type="text" name="search" value="{{ $currentSearch }}" placeholder="رقم القيد، البيان، اسم الحساب..." class="erp-input text-xs w-full">
                </div>

                <!-- Reference Type -->
                <div>
                    <label class="erp-label text-xs">نوع المستند المصدر</label>
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
        <div class="erp-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-right text-xs">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">#</th>
                            <th>رقم القيد</th>
                            <th>التاريخ</th>
                            <th>المستند المصدري</th>
                            <th>البيان والتفاصيل</th>
                            <th class="text-left font-numeric">إجمالي المدين</th>
                            <th class="text-left font-numeric">إجمالي الدائن</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">السطور</th>
                            <th class="w-20 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $index => $entry)
                            <tr class="hover:bg-surface-hover/60 transition-colors">
                                <td class="text-center text-muted font-numeric">{{ $entries->firstItem() + $index }}</td>
                                <td>
                                    <span class="font-mono font-bold text-foreground bg-surface px-2 py-0.5 rounded border border-border">
                                        {{ $entry->entry_number }}
                                    </span>
                                </td>
                                <td class="font-numeric text-muted whitespace-nowrap">
                                    {{ $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '—' }}
                                </td>
                                <td>
                                    <span class="erp-badge erp-badge-neutral text-[11px] font-medium">
                                        {{ $entry->reference_type_label }}
                                    </span>
                                </td>
                                <td class="max-w-xs truncate" title="{{ $entry->description }}">
                                    <span class="text-foreground font-medium">{{ $entry->description }}</span>
                                    @if($entry->creator)
                                        <div class="text-[10px] text-muted truncate">بواسطة: {{ $entry->creator->name }}</div>
                                    @endif
                                </td>
                                <td class="text-left font-numeric font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format((float) $entry->total_debit, 2) }}
                                </td>
                                <td class="text-left font-numeric font-bold text-cyan-600 dark:text-cyan-400">
                                    {{ number_format((float) $entry->total_credit, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="erp-badge {{ $entry->status_badge_class }} text-[10px]">
                                        {{ $entry->status_label }}
                                    </span>
                                </td>
                                <td class="text-center font-numeric">
                                    <span class="px-2 py-0.5 rounded-full bg-surface border border-border text-[11px] font-bold">
                                        {{ $entry->lines->count() }}
                                    </span>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <button type="button" 
                                            class="erp-btn erp-btn-secondary text-xs px-2 py-1" 
                                            title="عرض سطور وحسابات القيد"
                                            onclick="viewEntryDetails('{{ $entry->id }}')">
                                        <x-icon name="eye" size="14" />
                                        <span>عرض</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-10 text-muted">
                                    <x-icon name="file-text" size="36" class="mx-auto mb-2 opacity-30" />
                                    <p class="text-sm">لا توجد قيود يومية مطابقة للمعايير المحددة.</p>
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

    <!-- Modal: View Journal Entry Details -->
    <div id="entryDetailModal" class="erp-modal-backdrop" style="display: none;">
        <div class="erp-modal-box max-w-3xl">
            <div class="erp-modal-header">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-base font-bold bg-primary/10 text-primary px-2.5 py-0.5 rounded" id="modalEntryNumber">JV-000000</span>
                    <h3 class="erp-modal-title" id="modalEntryTitle">تفاصيل سطور قيد اليومية</h3>
                </div>
                <button type="button" onclick="document.getElementById('entryDetailModal').style.display='none'" class="erp-modal-close">
                    <x-icon name="x" size="18" />
                </button>
            </div>

            <div class="erp-modal-body space-y-4">
                <!-- Meta strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-surface rounded-lg border border-border text-xs">
                    <div>
                        <span class="text-muted block">تاريخ القيد:</span>
                        <strong class="font-numeric text-foreground" id="modalEntryDate">—</strong>
                    </div>
                    <div>
                        <span class="text-muted block">المستند المرجعي:</span>
                        <strong class="text-foreground" id="modalEntryRef">—</strong>
                    </div>
                    <div>
                        <span class="text-muted block">الحالة:</span>
                        <strong id="modalEntryStatus">—</strong>
                    </div>
                    <div>
                        <span class="text-muted block">منشئ القيد:</span>
                        <strong class="text-foreground" id="modalEntryCreator">—</strong>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-muted block mb-1">البيان العام:</span>
                    <p class="text-xs font-semibold text-foreground bg-surface/50 p-2 rounded border border-border" id="modalEntryDesc">—</p>
                </div>

                <!-- Lines Table -->
                <div class="overflow-x-auto border border-border rounded-lg">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-surface border-b border-border text-muted">
                            <tr>
                                <th class="p-2.5 w-8 text-center">#</th>
                                <th class="p-2.5">كود الحساب</th>
                                <th class="p-2.5">اسم الحساب</th>
                                <th class="p-2.5 text-left font-numeric">مدين (Dr)</th>
                                <th class="p-2.5 text-left font-numeric">دائن (Cr)</th>
                                <th class="p-2.5">البيان / ملاحظات السطر</th>
                                <th class="p-2.5">الطرف المسجل</th>
                            </tr>
                        </thead>
                        <tbody id="modalLinesBody" class="divide-y divide-border/40">
                            <!-- Injected dynamically -->
                        </tbody>
                        <tfoot class="bg-surface font-bold border-t-2 border-border">
                            <tr>
                                <td colspan="3" class="p-2.5 text-left">الإجمالي:</td>
                                <td class="p-2.5 text-left font-numeric text-emerald-600 dark:text-emerald-400" id="modalTotalDebit">0.00</td>
                                <td class="p-2.5 text-left font-numeric text-cyan-600 dark:text-cyan-400" id="modalTotalCredit">0.00</td>
                                <td colspan="2" class="p-2.5 text-center" id="modalBalanceCheck">
                                    <span class="erp-badge erp-badge-success text-[10px]">متزن</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="erp-modal-footer">
                <button type="button" onclick="document.getElementById('entryDetailModal').style.display='none'" class="erp-btn erp-btn-secondary">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- Script to load modal -->
    <script>
        function viewEntryDetails(entryId) {
            const modal = document.getElementById('entryDetailModal');
            const tbody = document.getElementById('modalLinesBody');
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-muted">جاري تحميل بيانات القيد...</td></tr>';
            modal.style.display = 'flex';

            fetch(`/accounting/journal-entries/${entryId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-rose-500">حدث خطأ أثناء تحميل القيد.</td></tr>';
                        return;
                    }

                    const entry = data.entry;
                    document.getElementById('modalEntryNumber').textContent = entry.entry_number;
                    document.getElementById('modalEntryDate').textContent = entry.entry_date ? entry.entry_date.split('T')[0] : '—';
                    document.getElementById('modalEntryRef').textContent = entry.reference_type;
                    document.getElementById('modalEntryStatus').textContent = entry.status === 'posted' ? 'مُرحل ومعتمد' : entry.status;
                    document.getElementById('modalEntryCreator').textContent = entry.creator ? entry.creator.name : 'النظام';
                    document.getElementById('modalEntryDesc').textContent = entry.description || '—';

                    document.getElementById('modalTotalDebit').textContent = Number(entry.total_debit).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' ج.م';
                    document.getElementById('modalTotalCredit').textContent = Number(entry.total_credit).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' ج.م';

                    const isBalanced = Math.abs(Number(entry.total_debit) - Number(entry.total_credit)) < 0.01;
                    document.getElementById('modalBalanceCheck').innerHTML = isBalanced 
                        ? '<span class="erp-badge erp-badge-success text-[10px]">متزن تماماً</span>'
                        : '<span class="erp-badge erp-badge-danger text-[10px]">غير متزن</span>';

                    tbody.innerHTML = '';
                    if (!entry.lines || entry.lines.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-muted">لا توجد سطور مسجلة لهذا القيد.</td></tr>';
                        return;
                    }

                    entry.lines.forEach((line, idx) => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-surface-hover/40';

                        const debit = Number(line.debit);
                        const credit = Number(line.credit);
                        const party = line.customer ? ('عميل: ' + line.customer.name) : (line.supplier ? ('مورد: ' + line.supplier.name) : '—');

                        tr.innerHTML = `
                            <td class="p-2.5 text-center text-muted font-numeric">${idx + 1}</td>
                            <td class="p-2.5 font-mono font-bold">${line.account ? line.account.code : '—'}</td>
                            <td class="p-2.5 font-semibold text-foreground">${line.account ? line.account.name : '—'}</td>
                            <td class="p-2.5 text-left font-numeric ${debit > 0 ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-muted'}">${debit > 0 ? debit.toLocaleString(undefined, {minimumFractionDigits: 2}) : '—'}</td>
                            <td class="p-2.5 text-left font-numeric ${credit > 0 ? 'font-bold text-cyan-600 dark:text-cyan-400' : 'text-muted'}">${credit > 0 ? credit.toLocaleString(undefined, {minimumFractionDigits: 2}) : '—'}</td>
                            <td class="p-2.5 text-muted">${line.description || '—'}</td>
                            <td class="p-2.5 text-xs text-foreground">${party}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-rose-500">فشل الاتصال بالخادم.</td></tr>';
                });
        }
    </script>
@endsection
