@extends('layouts.app')

@section('control_panel')
    <x-control-panel :title="$customer->name" :breadcrumbs="['العملاء والموردون' => route('customers.index'), 'دليل العملاء' => route('customers.index'), $customer->name => route('customers.show', $customer)]">
        <x-slot:actions>
            <a href="{{ route('customers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>العودة للعملاء</span>
            </a>
            <a href="{{ route('customers.statement', $customer) }}" class="erp-btn erp-btn-primary">
                <x-icon name="file-text" size="16" />
                <span>كشف الحساب المالي</span>
            </a>
            <a href="{{ route('customers.purchased-items', $customer) }}" class="erp-btn erp-btn-secondary">
                <x-icon name="shopping-bag" size="16" />
                <span>مسحوبات الأصناف</span>
            </a>
            <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="erp-btn erp-btn-success" title="مشاركة كشف الحساب عبر واتساب">
                <x-icon name="message-circle" size="16" />
                <span>واتساب</span>
            </a>

            <!-- Compact Print Dropdown -->
            <div class="erp-dropdown">
                <button type="button" class="erp-btn erp-btn-secondary" data-dropdown-toggle="customerShowPrintMenu">
                    <x-icon name="printer" size="16" />
                    <span>طباعة</span>
                    <x-icon name="chevron-down" size="14" />
                </button>
                <div id="customerShowPrintMenu" class="erp-dropdown-menu">
                    <a href="{{ route('customers.statement.print', [$customer, 'format' => 'a4']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="file-text" size="16" />
                        <span>كشف حساب A4 قياسي</span>
                    </a>
                    <a href="{{ route('customers.statement.print', [$customer, 'format' => 'a5']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="file" size="16" />
                        <span>كشف حساب A5 وسط</span>
                    </a>
                    <a href="{{ route('customers.statement.print', [$customer, 'format' => 'thermal_80mm']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="printer" size="16" />
                        <span>كشف حساب 80 مم حراري</span>
                    </a>
                    <div class="erp-dropdown-divider"></div>
                    <a href="{{ route('customers.purchased-items.print', [$customer, 'format' => 'a4']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="shopping-bag" size="16" />
                        <span>تقرير مسحوبات الأصناف A4</span>
                    </a>
                </div>
            </div>

            <a href="tel:{{ $customer->phone }}" class="erp-btn erp-btn-secondary" title="اتصال">
                <x-icon name="phone" size="16" />
                <span>اتصال</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $customer->is_active ? 'erp-badge-success' : 'erp-badge-danger' }} text-xs">
                {{ $customer->is_active ? 'حساب نشط' : 'حساب معطل' }}
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="customer-show">
        @if(session('success'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check" size="16" />
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Customer Dossier Card -->
        <div class="customer-profile-card">
            <div class="customer-profile-header">
                <div>
                    <h2 class="customer-profile-title">{{ $customer->name }}</h2>
                    <div class="customer-profile-sub flex items-center gap-2">
                        @if($customer->company_name)
                            <span class="font-medium text-[var(--text-main)]">{{ $customer->company_name }}</span>
                            <span>•</span>
                        @endif
                        <span>كود العميل: #{{ $customer->id }}</span>
                        <span>•</span>
                        @if($customer->customer_type === 'wholesale')
                            <span class="erp-badge erp-badge-success text-xs">تاجر جملة</span>
                        @elseif($customer->customer_type === 'distributor')
                            <span class="erp-badge erp-badge-warning text-xs">موزع</span>
                        @else
                            <span class="erp-badge erp-badge-info text-xs">قطاعي</span>
                        @endif
                    </div>
                </div>

                <div class="text-start md:text-end">
                    <div class="text-xs text-[var(--text-muted)] font-semibold mb-1">الرصيد المالي الحالي</div>
                    <div class="font-numeric font-black text-2xl">
                        @if($customer->current_balance > 0)
                            <span class="text-[var(--danger)]">
                                {{ number_format($customer->current_balance, 2) }} ج.م
                                <span class="text-xs font-normal block text-[var(--text-muted)]">(مستحق على العميل)</span>
                            </span>
                        @elseif($customer->current_balance < 0)
                            <span class="text-[var(--success)]">
                                {{ number_format(abs($customer->current_balance), 2) }} ج.م
                                <span class="text-xs font-normal block text-[var(--text-muted)]">(رصيد دائن لصالح العميل)</span>
                            </span>
                        @else
                            <span class="text-[var(--text-muted)]">0.00 ج.م</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="customer-details-grid">
                <div class="customer-detail-item">
                    <div class="customer-detail-label">الهاتف الأساسي</div>
                    <div class="customer-detail-val font-numeric">
                        <a href="tel:{{ $customer->phone }}" class="text-[var(--primary)] hover:underline direction-ltr inline-block">
                            {{ $customer->phone }}
                        </a>
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">الهاتف البديل</div>
                    <div class="customer-detail-val font-numeric">
                        {{ $customer->phone_alt ?: 'غير مسجل' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">المحافظة / المدينة</div>
                    <div class="customer-detail-val">
                        {{ $customer->city ?: 'غير محدد' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">فئة التسعير</div>
                    <div class="customer-detail-val">
                        {{ $customer->pricing_tier === 'wholesale' ? 'سعر الجملة' : 'سعر القطاعي' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">حد الائتمان المالي</div>
                    <div class="customer-detail-val font-numeric">
                        {{ $customer->credit_limit > 0 ? number_format($customer->credit_limit, 2) . ' ج.م' : 'بدون سقف ائتماني' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">العنوان التفصيلي</div>
                    <div class="customer-detail-val text-xs">
                        {{ $customer->address ?: 'لا يوجد عنوان مسجل' }}
                    </div>
                </div>
            </div>

            @if($customer->notes)
                <div class="mt-4 p-3 bg-[var(--bg-surface-alt)] border border-[var(--border-light)] rounded">
                    <div class="text-xs font-bold text-[var(--text-secondary)] mb-1">ملاحظات العميل:</div>
                    <div class="text-sm text-[var(--text-main)]">{{ $customer->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Customer Invoices & Credit Ledger -->
        <div class="erp-card mt-6">
            <div class="erp-card-header flex items-center justify-between">
                <div class="erp-card-title flex items-center gap-2">
                    <x-icon name="calculator" size="18" class="text-[var(--primary)]" />
                    <span>سجل فواتير المبيعات وحركات الآجل للعميل (كشف حساب وحركات الفواتير)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="erp-badge erp-badge-neutral text-xs">
                        إجمالي الفواتير: {{ $salesInvoices->count() }}
                    </span>
                    <a href="{{ route('sales.create') }}" class="erp-btn erp-btn-primary erp-btn-sm">
                        <x-icon name="plus" size="14" />
                        <span>فاتورة مبيعات جديدة</span>
                    </a>
                </div>
            </div>

            <!-- Customer Financial KPI Row -->
            <div class="p-4 border-b border-[var(--border-color)] bg-[var(--bg-surface-alt)]">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">إجمالي المشتريات</div>
                        <div class="font-numeric font-bold text-lg text-[var(--text-main)]">
                            {{ number_format($salesInvoices->where('status', 'posted')->sum('total_amount'), 2) }} ج.م
                        </div>
                    </div>
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">إجمالي المسدد نقداً</div>
                        <div class="font-numeric font-bold text-lg text-[var(--success)]">
                            {{ number_format($salesInvoices->where('status', 'posted')->sum('paid_amount'), 2) }} ج.م
                        </div>
                    </div>
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">الرصيد الآجل المعلق</div>
                        <div class="font-numeric font-bold text-lg text-[var(--danger)]">
                            {{ number_format($customer->current_balance, 2) }} ج.م
                        </div>
                    </div>
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">المتاح من حد الائتمان</div>
                        <div class="font-numeric font-bold text-lg {{ ($customer->credit_limit > 0 && ($customer->credit_limit - $customer->current_balance) <= 0) ? 'text-[var(--danger)]' : 'text-[var(--primary)]' }}">
                            @if($customer->credit_limit > 0)
                                {{ number_format(max(0, $customer->credit_limit - $customer->current_balance), 2) }} ج.م
                            @else
                                <span class="text-xs text-[var(--text-muted)]">مفتوح (بدون سقف)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="erp-card-body p-0">
                @if($salesInvoices->isEmpty())
                    <div class="text-center py-10">
                        <x-icon name="shopping-bag" size="40" class="text-[var(--text-muted)] mb-2 inline-block" />
                        <h4 class="font-bold text-base m-0 mb-1">لا توجد فواتير مبيعات مسجلة لهذا العميل حتى الآن</h4>
                        <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto m-0 mb-4">
                            يمكنك البدء بإنشاء فاتورة مبيعات نقدية أو آجلة وسيتم قيدها مباشرة على كشف حساب العميل ورصيده.
                        </p>
                        <a href="{{ route('sales.create') }}" class="erp-btn erp-btn-primary erp-btn-sm inline-flex">
                            <x-icon name="plus" size="14" />
                            <span>إنشاء فاتورة الآن</span>
                        </a>
                    </div>
                @else
                    <div class="erp-table-responsive">
                        <table class="erp-table">
                            <thead>
                                <tr>
                                    <th class="w-12 text-center">#</th>
                                    <th>رقم الفاتورة</th>
                                    <th>تاريخ الفاتورة</th>
                                    <th>مستودع الصرف</th>
                                    <th class="text-center">طريقة السداد</th>
                                    <th class="text-center">إجمالي الفاتورة</th>
                                    <th class="text-center">المدفوع نقداً</th>
                                    <th class="text-center">المتبقي الآجل</th>
                                    <th class="text-center">استحقاق الآجل</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="w-24 text-center">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if((float) $customer->opening_balance != 0.0)
                                    <tr class="bg-[var(--bg-surface-alt)] font-semibold">
                                        <td class="text-center font-numeric text-xs">—</td>
                                        <td class="font-numeric font-bold text-xs">OPENING</td>
                                        <td class="text-xs">—</td>
                                        <td class="text-xs">الرصيد الافتتاحي للعميل</td>
                                        <td class="text-center text-xs">—</td>
                                        <td class="text-center font-numeric font-bold text-xs">{{ number_format($customer->opening_balance, 2) }} ج.م</td>
                                        <td class="text-center text-xs font-numeric">0.00 ج.م</td>
                                        <td class="text-center font-numeric font-bold text-xs {{ $customer->opening_balance > 0 ? 'text-[var(--danger)]' : 'text-[var(--success)]' }}">{{ number_format($customer->opening_balance, 2) }} ج.م</td>
                                        <td class="text-center text-xs">—</td>
                                        <td class="text-center"><span class="erp-badge erp-badge-neutral text-xs">افتتاحي</span></td>
                                        <td class="text-center">—</td>
                                    </tr>
                                @endif
                                @foreach($salesInvoices as $index => $invoice)
                                    <tr>
                                        <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="font-numeric font-bold">
                                            <a href="{{ route('sales.show', $invoice) }}" class="text-[var(--primary)] hover:underline">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td class="text-xs font-numeric">
                                            {{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="text-xs">
                                            {{ $invoice->warehouse?->name ?? '—' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="erp-badge {{ $invoice->payment_type_badge_class }} text-xs">
                                                {{ $invoice->payment_type_label }}
                                            </span>
                                        </td>
                                        <td class="text-center font-numeric font-bold text-xs">
                                            {{ number_format($invoice->total_amount, 2) }} ج.م
                                        </td>
                                        <td class="text-center font-numeric text-xs text-[var(--success)] font-semibold">
                                            {{ number_format($invoice->paid_amount, 2) }} ج.م
                                        </td>
                                        <td class="text-center font-numeric text-xs {{ $invoice->remaining_amount > 0 ? 'text-[var(--danger)] font-bold' : 'text-[var(--text-muted)]' }}">
                                            {{ number_format($invoice->remaining_amount, 2) }} ج.م
                                        </td>
                                        <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                            {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="text-center">
                                            @if($invoice->status === \App\Models\SalesInvoice::STATUS_POSTED)
                                                <span class="erp-badge erp-badge-success text-xs">مرحلة للمخزن</span>
                                            @elseif($invoice->status === \App\Models\SalesInvoice::STATUS_VOIDED)
                                                <span class="erp-badge erp-badge-danger text-xs">ملغاة</span>
                                            @else
                                                <span class="erp-badge erp-badge-warning text-xs">مسودة</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('sales.show', $invoice) }}" class="erp-btn erp-btn-ghost erp-btn-xs" title="عرض الفاتورة">
                                                    <x-icon name="eye" size="14" />
                                                </a>
                                                <a href="{{ route('sales.print', $invoice) }}" target="_blank" class="erp-btn erp-btn-ghost erp-btn-xs" title="طباعة الفاتورة">
                                                    <x-icon name="printer" size="14" />
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
