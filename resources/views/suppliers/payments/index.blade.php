@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="مدفوعات وسندات صرف الموردين" :breadcrumbs="['المالية والحسابات' => route('treasuries.index'), 'مدفوعات الموردين' => route('supplier-payments.index')]">
        <x-slot:actions>
            <a href="{{ route('suppliers.index') }}" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>دليل الموردين لتسجيل دفعة</span>
            </a>
            <a href="{{ route('purchases.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="shopping-bag" size="16" />
                <span>فواتير المشتريات الآجلة</span>
            </a>
            <a href="{{ route('treasuries.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="vault" size="16" />
                <span>الخزائن والبنوك</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric px-3 py-1 text-xs">
                <span>إجمالي ما تم سداده:</span>
                <strong class="text-[var(--danger)] mr-1">{{ number_format($metrics['total_payments'], 2) }} ج.م</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="supplier-payments">
        @if(session('success'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check-circle" size="18" />
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- KPI Cards Grid -->
        <div class="finance-kpi-grid">
            <div class="finance-kpi-card">
                <div class="finance-kpi-icon danger">
                    <x-icon name="credit-card" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">إجمالي المدفوعات المسددة للموردين</div>
                    <div class="finance-kpi-value font-numeric text-[var(--danger)]">
                        {{ number_format($metrics['total_payments'], 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
                    </div>
                    <div class="finance-kpi-sub">حسب نتائج التصفية الحالية</div>
                </div>
            </div>

            <div class="finance-kpi-card">
                <div class="finance-kpi-icon primary">
                    <x-icon name="receipt" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">عدد سندات الصرف المسجلة</div>
                    <div class="finance-kpi-value font-numeric">
                        {{ $metrics['payments_count'] }}
                    </div>
                    <div class="finance-kpi-sub">سند صرف مسجل في النظام</div>
                </div>
            </div>

            <div class="finance-kpi-card">
                <div class="finance-kpi-icon warning">
                    <x-icon name="clock" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">إجمالي المديونيات المتبقية للموردين</div>
                    <div class="finance-kpi-value font-numeric text-[var(--warning-text)]">
                        {{ number_format($metrics['total_payables'], 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
                    </div>
                    <div class="finance-kpi-sub">مستحقات واجبة السداد</div>
                </div>
            </div>

            <div class="finance-kpi-card">
                <div class="finance-kpi-icon info">
                    <x-icon name="factory" size="22" />
                </div>
                <div class="finance-kpi-info">
                    <div class="finance-kpi-label">إجمالي الموردين والجهات</div>
                    <div class="finance-kpi-value font-numeric">
                        {{ $metrics['suppliers_count'] }}
                    </div>
                    <div class="finance-kpi-sub">مصانع وموردين ومكاتب شحن</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="finance-filter-bar">
            <form method="GET" action="{{ route('supplier-payments.index') }}" class="finance-filter-inputs">
                <select name="supplier_id" class="finance-filter-input">
                    <option value="">-- كل الموردين --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>

                <select name="payment_method" class="finance-filter-input">
                    <option value="">-- كل طرق السداد --</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>نقداً</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                    <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>شيك</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>بطاقة</option>
                </select>

                <input type="date" name="from_date" value="{{ request('from_date') }}" class="finance-filter-input font-numeric" placeholder="من تاريخ">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="finance-filter-input font-numeric" placeholder="إلى تاريخ">

                <button type="submit" class="erp-btn erp-btn-primary">
                    <x-icon name="filter" size="14" />
                    <span>تصفية</span>
                </button>

                @if(request()->hasAny(['supplier_id', 'payment_method', 'from_date', 'to_date']))
                    <a href="{{ route('supplier-payments.index') }}" class="erp-btn erp-btn-secondary" title="إلغاء التصفية">
                        <x-icon name="x" size="14" />
                    </a>
                @endif
            </form>
        </div>

        <!-- Payments Table Card -->
        <div class="erp-card">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="receipt" size="18" />
                    <span>سجل سندات الصرف للموردين</span>
                </div>
                <span class="text-xs text-[var(--text-muted)] font-numeric">إجمالي النتائج: {{ $payments->total() }}</span>
            </div>

            @if($payments->isEmpty())
                <div class="text-center py-12 text-[var(--text-muted)] text-sm">
                    لا توجد سندات صرف مسجلة مطابقة لخيارات البحث.
                </div>
            @else
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>رقم السند</th>
                                <th>التاريخ</th>
                                <th>المورد</th>
                                <th>الفاتورة المرتبطة</th>
                                <th>الخزينة / المصدر</th>
                                <th>طريقة الصرف</th>
                                <th>المبلغ المصروف</th>
                                <th>المرجع / ملاحظات</th>
                                <th>المسؤول</th>
                                <th style="text-align: center;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $pay)
                                <tr class="erp-table-row">
                                    <td class="font-mono font-medium text-[var(--text-main)]">{{ $pay->payment_number }}</td>
                                    <td class="font-numeric text-[var(--text-secondary)]">{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('Y-m-d') : '—' }}</td>
                                    <td class="font-bold">
                                        <a href="{{ route('suppliers.show', $pay->supplier_id) }}" class="text-[var(--primary)] hover:underline">
                                            {{ $pay->supplier?->name ?? '—' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($pay->purchaseInvoice)
                                            <a href="{{ route('purchases.show', $pay->purchase_invoice_id) }}" class="font-mono text-[var(--primary)] hover:underline font-bold">
                                                {{ $pay->purchaseInvoice->invoice_number }}
                                            </a>
                                        @else
                                            <span class="text-[var(--text-muted)]">دفعة لحساب المورد</span>
                                        @endif
                                    </td>
                                    <td class="text-[var(--text-secondary)]">
                                        {{ $pay->treasury?->name ?? 'درج الكاشير' }}
                                    </td>
                                    <td>
                                        <span class="erp-badge {{ $pay->payment_method === 'cash' ? 'erp-badge-success' : ($pay->payment_method === 'bank_transfer' ? 'erp-badge-info' : 'erp-badge-warning') }}">
                                            {{ $pay->payment_method_label }}
                                        </span>
                                    </td>
                                    <td class="font-bold font-numeric text-[var(--danger)]">
                                        {{ number_format((float)$pay->amount, 2) }} ج.م
                                    </td>
                                    <td class="text-[var(--text-secondary)] max-w-xs truncate">
                                        {{ $pay->reference_number ? "مرجع: {$pay->reference_number}" : '' }}
                                        {{ $pay->notes ? "({$pay->notes})" : '' }}
                                        @if(!$pay->reference_number && !$pay->notes)
                                            —
                                        @endif
                                    </td>
                                    <td class="text-[var(--text-muted)]">{{ $pay->creator?->name ?? 'النظام' }}</td>
                                    <td style="text-align: center;">
                                        <div style="display: inline-flex; align-items: center; gap: 6px;">
                                            <a href="{{ route('supplier-payments.receipt', $pay) }}" target="_blank" class="erp-btn erp-btn-secondary text-xs py-1 px-2" title="طباعة سند الصرف">
                                                <x-icon name="printer" size="14" />
                                            </a>
                                            <form action="{{ route('supplier-payments.destroy', $pay) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إلغاء سند الصرف رقم {{ $pay->payment_number }}؟ سيتم استرجاع المبلغ للخزينة وإعادة المديونية للمورد.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="erp-btn erp-btn-danger text-xs py-1 px-2" title="إلغاء واسترداد">
                                                    <x-icon name="trash-2" size="14" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                    <div style="padding: 12px 16px; border-top: 1px solid var(--border-light);">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
