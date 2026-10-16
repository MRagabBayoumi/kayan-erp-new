@extends('layouts.app')

@section('control_panel')
    <x-control-panel :title="$supplier->name" :breadcrumbs="['العملاء والموردون' => route('suppliers.index'), 'دليل الموردين' => route('suppliers.index'), $supplier->name => route('suppliers.show', $supplier)]">
        <x-slot:actions>
            <a href="{{ route('suppliers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>العودة للموردين</span>
            </a>
            <a href="{{ route('suppliers.statement', $supplier) }}" class="erp-btn erp-btn-primary">
                <x-icon name="file-text" size="16" />
                <span>كشف الحساب المالي</span>
            </a>

            <!-- Compact Print Dropdown -->
            <div class="erp-dropdown">
                <button type="button" class="erp-btn erp-btn-secondary" data-dropdown-toggle="supplierShowPrintMenu">
                    <x-icon name="printer" size="16" />
                    <span>طباعة</span>
                    <x-icon name="chevron-down" size="14" />
                </button>
                <div id="supplierShowPrintMenu" class="erp-dropdown-menu">
                    <a href="{{ route('suppliers.statement.print', [$supplier, 'format' => 'a4']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="file-text" size="16" />
                        <span>كشف حساب A4 قياسي</span>
                    </a>
                    <a href="{{ route('suppliers.statement.print', [$supplier, 'format' => 'a5']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="file" size="16" />
                        <span>كشف حساب A5 وسط</span>
                    </a>
                    <a href="{{ route('suppliers.statement.print', [$supplier, 'format' => 'thermal_80mm']) }}" target="_blank" class="erp-dropdown-item">
                        <x-icon name="printer" size="16" />
                        <span>كشف حساب 80 مم حراري</span>
                    </a>
                </div>
            </div>

            @if($supplier->phone)
                <a href="tel:{{ $supplier->phone }}" class="erp-btn erp-btn-secondary">
                    <x-icon name="phone" size="16" />
                    <span>اتصال</span>
                </a>
            @endif
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $supplier->is_active ? 'erp-badge-success' : 'erp-badge-danger' }} text-xs">
                {{ $supplier->is_active ? 'حساب نشط' : 'حساب معطل' }}
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="supplier-show">
        @if(session('success'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check" size="16" />
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Supplier Dossier Card -->
        <div class="supplier-profile-card">
            <div class="supplier-profile-header">
                <div>
                    <h2 class="supplier-profile-title">{{ $supplier->name }}</h2>
                    <div class="supplier-profile-sub flex items-center gap-2">
                        <span>كود المورد: #{{ $supplier->id }}</span>
                        <span>•</span>
                        @if($supplier->supplier_type === 'china_factory')
                            <span class="erp-badge erp-badge-danger text-xs">مصنع بالصين</span>
                        @elseif($supplier->supplier_type === 'shipping_agent')
                            <span class="erp-badge erp-badge-warning text-xs">وكيل شحن وتخليص</span>
                        @else
                            <span class="erp-badge erp-badge-info text-xs">مورد محلي</span>
                        @endif
                        <span>•</span>
                        <span class="font-bold text-[var(--text-main)]">{{ $supplier->country ?: 'China' }}</span>
                    </div>
                </div>

                <div class="text-start md:text-end">
                    <div class="text-xs text-[var(--text-muted)] font-semibold mb-1">الرصيد المالي المعلق</div>
                    <div class="font-numeric font-black text-2xl">
                        @if($supplier->current_balance != 0)
                            <span class="{{ $supplier->current_balance > 0 ? 'text-[var(--warning)]' : 'text-[var(--success)]' }}">
                                {{ number_format($supplier->current_balance, 2) }} {{ $supplier->currency }}
                            </span>
                        @else
                            <span class="text-[var(--text-muted)]">0.00 {{ $supplier->currency }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="supplier-details-grid">
                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">المسؤول للتواصل</div>
                    <div class="supplier-detail-val">
                        {{ $supplier->contact_person ?: 'غير محدد' }}
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">معرف WeChat</div>
                    <div class="supplier-detail-val font-numeric">
                        @if($supplier->wechat_id)
                            <span class="supplier-wechat-tag">
                                <x-icon name="smartphone" size="12" />
                                <span>{{ $supplier->wechat_id }}</span>
                            </span>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">الهاتف / الواتساب</div>
                    <div class="supplier-detail-val font-numeric">
                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" class="text-[var(--primary)] hover:underline direction-ltr inline-block">
                                {{ $supplier->phone }}
                            </a>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">البريد الإلكتروني</div>
                    <div class="supplier-detail-val font-numeric text-xs">
                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="text-[var(--primary)] hover:underline direction-ltr inline-block">
                                {{ $supplier->email }}
                            </a>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">المدينة / المقاطعة</div>
                    <div class="supplier-detail-val">
                        {{ $supplier->city ?: '—' }}
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">عملة الحساب</div>
                    <div class="supplier-detail-val font-numeric">
                        {{ $supplier->currency }}
                    </div>
                </div>
            </div>

            @if($supplier->address)
                <div class="mt-4 p-3 bg-[var(--bg-surface-alt)] border border-[var(--border-light)] rounded">
                    <div class="text-xs font-bold text-[var(--text-secondary)] mb-1">العنوان التفصيلي وموقع المصنع:</div>
                    <div class="text-sm text-[var(--text-main)]">{{ $supplier->address }}</div>
                </div>
            @endif

            @if($supplier->notes)
                <div class="mt-3 p-3 bg-[var(--bg-surface-alt)] border border-[var(--border-light)] rounded">
                    <div class="text-xs font-bold text-[var(--text-secondary)] mb-1">ملاحظات وشروط التعامل:</div>
                    <div class="text-sm text-[var(--text-main)]">{{ $supplier->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Supplier Invoices & Payables Ledger -->
        <div class="erp-card mt-6">
            <div class="erp-card-header flex items-center justify-between">
                <div class="erp-card-title flex items-center gap-2">
                    <x-icon name="shopping-bag" size="18" class="text-[var(--primary)]" />
                    <span>سجل فواتير الشراء وحركات الآجل للمورد (سجل أوامر الشراء والاستيراد من الصين)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="erp-badge erp-badge-neutral text-xs">
                        إجمالي الفواتير: {{ $purchaseInvoices->count() }}
                    </span>
                    <a href="{{ route('purchases.create') }}" class="erp-btn erp-btn-primary erp-btn-sm">
                        <x-icon name="plus" size="14" />
                        <span>فاتورة شراء جديدة</span>
                    </a>
                </div>
            </div>

            <!-- Supplier Financial KPI Row -->
            <div class="p-4 border-b border-[var(--border-color)] bg-[var(--bg-surface-alt)]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">إجمالي المشتريات والتوريدات</div>
                        <div class="font-numeric font-bold text-lg text-[var(--text-main)]">
                            {{ number_format($purchaseInvoices->where('status', 'posted')->sum('total_amount'), 2) }} {{ $supplier->currency }}
                        </div>
                    </div>
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">إجمالي المسدد للمورد</div>
                        <div class="font-numeric font-bold text-lg text-[var(--success)]">
                            {{ number_format($purchaseInvoices->where('status', 'posted')->sum('paid_amount'), 2) }} {{ $supplier->currency }}
                        </div>
                    </div>
                    <div class="p-3 bg-[var(--bg-surface)] rounded border border-[var(--border-color)]">
                        <div class="text-xs text-[var(--text-muted)] font-medium mb-1">الرصيد المعلق المستحق للمورد</div>
                        <div class="font-numeric font-bold text-lg text-[var(--warning)]">
                            {{ number_format($supplier->current_balance, 2) }} {{ $supplier->currency }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="erp-card-body p-0">
                @if($purchaseInvoices->isEmpty())
                    <div class="text-center py-10">
                        <x-icon name="truck" size="40" class="text-[var(--text-muted)] mb-2 inline-block" />
                        <h4 class="font-bold text-base m-0 mb-1">لا توجد فواتير شراء مسجلة لهذا المورد حتى الآن</h4>
                        <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto m-0 mb-4">
                            يمكنك تسجيل فاتورة توريد وشراء جديدة سواء نقدية أو آجلة وسيتم تحديث رصيد المورد ومخزون الأصناف مباشرة.
                        </p>
                        <a href="{{ route('purchases.create') }}" class="erp-btn erp-btn-primary erp-btn-sm inline-flex">
                            <x-icon name="plus" size="14" />
                            <span>تسجيل فاتورة شراء</span>
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
                                    <th>مستودع الاستلام</th>
                                    <th class="text-center">طريقة السداد</th>
                                    <th class="text-center">إجمالي الفاتورة</th>
                                    <th class="text-center">المدفوع نقداً</th>
                                    <th class="text-center">المتبقي الآجل</th>
                                    <th class="text-center">تاريخ الاستحقاق</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="w-24 text-center">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if((float) $supplier->opening_balance != 0.0)
                                    <tr class="bg-[var(--bg-surface-alt)] font-semibold">
                                        <td class="text-center font-numeric text-xs">—</td>
                                        <td class="font-numeric font-bold text-xs">OPENING</td>
                                        <td class="text-xs">—</td>
                                        <td class="text-xs">الرصيد الافتتاحي للمورد</td>
                                        <td class="text-center text-xs">—</td>
                                        <td class="text-center font-numeric font-bold text-xs">{{ number_format($supplier->opening_balance, 2) }} {{ $supplier->currency }}</td>
                                        <td class="text-center text-xs font-numeric">0.00 {{ $supplier->currency }}</td>
                                        <td class="text-center font-numeric font-bold text-xs text-[var(--danger)]">{{ number_format($supplier->opening_balance, 2) }} {{ $supplier->currency }}</td>
                                        <td class="text-center text-xs">—</td>
                                        <td class="text-center"><span class="erp-badge erp-badge-neutral text-xs">افتتاحي</span></td>
                                        <td class="text-center">—</td>
                                    </tr>
                                @endif
                                @foreach($purchaseInvoices as $index => $invoice)
                                    <tr>
                                        <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="font-numeric font-bold">
                                            <a href="{{ route('purchases.show', $invoice) }}" class="text-[var(--primary)] hover:underline">
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
                                            {{ number_format($invoice->total_amount, 2) }} {{ $supplier->currency }}
                                        </td>
                                        <td class="text-center font-numeric text-xs text-[var(--success)] font-semibold">
                                            {{ number_format($invoice->paid_amount, 2) }} {{ $supplier->currency }}
                                        </td>
                                        <td class="text-center font-numeric text-xs {{ $invoice->remaining_amount > 0 ? 'text-[var(--danger)] font-bold' : 'text-[var(--text-muted)]' }}">
                                            {{ number_format($invoice->remaining_amount, 2) }} {{ $supplier->currency }}
                                        </td>
                                        <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                            {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="erp-badge {{ $invoice->status_badge_class }} text-xs">
                                                {{ $invoice->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('purchases.show', $invoice) }}" class="erp-btn erp-btn-ghost erp-btn-xs" title="عرض الفاتورة">
                                                    <x-icon name="eye" size="14" />
                                                </a>
                                                <a href="{{ route('purchases.print', $invoice) }}" target="_blank" class="erp-btn erp-btn-ghost erp-btn-xs" title="طباعة الفاتورة">
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
