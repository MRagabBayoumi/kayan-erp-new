@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="فاتورة مبيعات: {{ $invoice->invoice_number }}" :breadcrumbs="['المبيعات والعملاء' => route('sales.index'), $invoice->invoice_number => '#']">
        <x-slot:actions>
            @if($invoice->status === 'draft')
                <a href="{{ route('sales.edit', $invoice) }}" class="erp-btn erp-btn-warning">
                    <x-icon name="edit-3" size="16" />
                    <span>تعديل الفاتورة</span>
                </a>
                @can('post-sales-invoice')
                <form action="{{ route('sales.post', $invoice) }}" method="POST" data-confirm="هل تريد ترحيل هذه الفاتورة وصرف الكميات بنظام FIFO آلياً؟">
                    @csrf
                    <button type="submit" class="erp-btn erp-btn-primary">
                        <x-icon name="check-circle" size="16" />
                        <span>ترحيل وصرف FIFO</span>
                    </button>
                </form>
                @endcan
            @endif

            @if($invoice->status === 'posted')
                <form action="{{ route('sales.unpost-and-edit', $invoice) }}" method="POST" data-confirm="سيتم فك ترحيل الفاتورة واسترجاع أرصدة المخزون مؤقتاً لتعديل بنودها في جدول البيع. هل تريد المتابعة؟">
                    @csrf
                    <button type="submit" class="erp-btn erp-btn-warning">
                        <x-icon name="edit-3" size="16" />
                        <span>تعديل الفاتورة (إرجاع للرصيد)</span>
                    </button>
                </form>

                @can('void-sales-invoice')
                <form action="{{ route('sales.void', $invoice) }}" method="POST" data-confirm="تحذير محاسبي: هل أنت متأكد من إلغاء هذه الفاتورة وإعادة الكميات المسحوبة للدفعات المخزنية؟">
                    @csrf
                    <input type="hidden" name="void_reason" value="إلغاء الفاتورة من قبل الإدارة">
                    <button type="submit" class="erp-btn erp-btn-secondary text-[var(--danger)]">
                        <x-icon name="x-circle" size="16" />
                        <span>إلغاء واسترجاع المخزون</span>
                    </button>
                </form>
                @endcan
            @endif

            <button type="button" data-action="python-direct-print" data-url="{{ route('sales.direct-print', $invoice) }}" class="erp-btn erp-btn-primary" title="طباعة مباشرة بطابعة الإيصالات عبر بايثون">
                <x-icon name="printer" size="16" />
                <span>طباعة بايثون المباشرة</span>
            </button>

            <div class="inline-flex items-center gap-1">
                <a href="{{ route('sales.print', [$invoice, 'format' => 'a4']) }}" target="_blank" class="erp-btn erp-btn-secondary" title="طباعة A4">
                    <x-icon name="printer" size="16" />
                    <span>طباعة الفاتورة (A4)</span>
                </a>
                <a href="{{ route('sales.print', [$invoice, 'format' => 'a5']) }}" target="_blank" class="erp-btn erp-btn-ghost text-xs px-2" title="طباعة A5">
                    <span>A5</span>
                </a>
                <a href="{{ route('sales.print', [$invoice, 'format' => 'thermal_80mm']) }}" target="_blank" class="erp-btn erp-btn-ghost text-xs px-2" title="طباعة إيصال حراري 80 مم">
                    <span>80 مم</span>
                </a>
            </div>

            <a href="{{ route('sales.index') }}" class="erp-btn erp-btn-ghost">
                <x-icon name="arrow-left" size="16" />
                <span>العودة للقائمة</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $invoice->status_badge_class }} py-1 px-2.5 text-xs font-semibold">
                {{ $invoice->status_label }}
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="sales-show" class="flex flex-col gap-5">
        <!-- Status Messages -->
        @if(session('status') === 'success')
            <div class="erp-alert erp-alert-success" role="alert">
                <x-icon name="check-circle" size="18" />
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="erp-alert erp-alert-danger" role="alert">
                <x-icon name="alert-triangle" size="18" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($invoice->status === 'voided')
            <div class="erp-alert erp-alert-warning" role="alert">
                <x-icon name="alert-triangle" size="18" />
                <div>
                    <strong>فاتورة مبيعات ملغاة:</strong>
                    <span>تم إلغاء هذه الفاتورة وإرجاع جميع الكميات المصروفة إلى دفعاتها الأصلية مع تسجيل حركات تسوية عكسية موثقة. سبب الإلغاء: {{ $invoice->void_reason ?? 'غير محدد' }}</span>
                </div>
            </div>
        @endif

        <!-- Invoice Details Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Customer Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="user" size="18" />
                        <span>بيانات العميل</span>
                    </div>
                </div>
                <div class="erp-card-body flex flex-col gap-2 text-xs">
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">اسم العميل:</span>
                        <strong class="text-sm text-[var(--text-main)]">{{ $invoice->customer->name ?? 'عميل نقدي' }}</strong>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">رقم الهاتف:</span>
                        <span class="font-numeric">{{ $invoice->customer->phone ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">العنوان:</span>
                        <span>{{ $invoice->customer->address ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Warehouse & Delivery Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="home" size="18" />
                        <span>مستودع وتاريخ الصرف</span>
                    </div>
                </div>
                <div class="erp-card-body flex flex-col gap-2 text-xs">
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">المستودع المصروف منه:</span>
                        <strong class="text-sm text-[var(--text-main)]">{{ $invoice->warehouse->name ?? 'المخزن الرئيسي' }}</strong>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">تاريخ الفاتورة:</span>
                        <span class="font-numeric">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">فئة التسعير المطبقة:</span>
                        @if($invoice->price_tier === 'distributor')
                            <span class="erp-badge erp-badge-neutral text-xs">سعر موزع (Distributor)</span>
                        @elseif($invoice->price_tier === 'wholesale')
                            <span class="erp-badge erp-badge-neutral text-xs">سعر جملة (Wholesale)</span>
                        @else
                            <span class="erp-badge erp-badge-neutral text-xs">سعر قطاعي (Retail)</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Audit Trail & Financial Meta Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="shield-check" size="18" />
                        <span>سجل التدقيق والمستخدمين</span>
                    </div>
                </div>
                <div class="erp-card-body flex flex-col gap-2 text-xs">
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">مسجل الفاتورة:</span>
                        <span class="font-semibold">{{ $invoice->creator->name ?? 'مدير النظام' }}</span>
                        <span class="text-[var(--text-muted)] text-[10px] block font-numeric">{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @if($invoice->poster)
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">المُرحِل ومسؤول الصرف:</span>
                        <span class="font-semibold text-[var(--success)]">{{ $invoice->poster->name }}</span>
                        <span class="text-[var(--text-muted)] text-[10px] block font-numeric">{{ $invoice->posted_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                    @if($invoice->voider)
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">المُلغي:</span>
                        <span class="font-semibold text-[var(--danger)]">{{ $invoice->voider->name }}</span>
                        <span class="text-[var(--text-muted)] text-[10px] block font-numeric">{{ $invoice->voided_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Consolidated Line Items Table -->
        <div class="erp-card">
            <div class="erp-card-header flex items-center justify-between">
                <div class="erp-card-title">
                    <x-icon name="list" size="18" />
                    <span>بنود الفاتورة المجمعة (Consolidated Sales Items)</span>
                </div>
                <span class="text-xs text-[var(--text-muted)] bg-[var(--surface-muted)] px-3 py-1 rounded border border-[var(--border-color)]">
                    تظهر البنود مجمعة للعميل، مع حفظ تفاصيل الدفعات والتكلفة محاسبياً
                </span>
            </div>

            <div class="erp-card-body p-0">
                <div class="table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">#</th>
                                <th>الصنف والوصف</th>
                                <th class="w-28 text-center">وحدة البيع</th>
                                <th class="w-24 text-center">الكمية المباعة</th>
                                <th class="w-28 text-center">الكمية الأساسية</th>
                                <th class="w-28 text-center">سعر الوحدة</th>
                                <th class="w-24 text-center">خصم البند</th>
                                <th class="w-28 text-center">صافي البيع</th>
                                @if($canViewCogs)
                                <th class="w-28 text-center">تكلفة FIFO</th>
                                <th class="w-28 text-center">الربح المحقق</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="text-center font-numeric">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="font-bold text-[var(--text-main)]">{{ $item->product->name ?? 'صنف غير معروف' }}</div>
                                        <div class="text-[11px] text-[var(--text-muted)] font-numeric">
                                            كود: {{ $item->product->sku ?? '—' }}
                                            @if($item->product->barcode)
                                                · باركود: {{ $item->product->barcode }}
                                            @endif
                                        </div>
                                        @if($item->notes)
                                            <div class="text-[11px] text-amber-700 mt-0.5">ملاحظة: {{ $item->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center text-xs">
                                        {{ $item->unit->name ?? 'قطعة' }}
                                        @if((float)$item->conversion_factor != 1.0)
                                            <div class="text-[10px] text-[var(--text-muted)] font-numeric">معامل: {{ (float)$item->conversion_factor }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center font-numeric font-semibold">
                                        {{ (float)$item->quantity }}
                                    </td>
                                    <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                        {{ (float)$item->base_quantity }} {{ $item->product->unit ?? 'قطعة' }}
                                    </td>
                                    <td class="text-center font-numeric">
                                        {{ number_format((float)$item->unit_price, 2) }} ج.م
                                    </td>
                                    <td class="text-center font-numeric text-xs text-amber-600">
                                        {{ (float)$item->line_discount > 0 ? number_format((float)$item->line_discount, 2) . ' ج.م' : '—' }}
                                    </td>
                                    <td class="text-center font-numeric font-bold text-[var(--text-main)]">
                                        {{ number_format((float)$item->subtotal, 2) }} ج.م
                                    </td>
                                    @if($canViewCogs)
                                    <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                        {{ number_format((float)$item->total_cogs, 2) }} ج.م
                                    </td>
                                    <td class="text-center font-numeric text-xs font-bold {{ (float)$item->total_profit >= 0 ? 'text-[var(--success)]' : 'text-[var(--danger)]' }}">
                                        {{ number_format((float)$item->total_profit, 2) }} ج.م
                                    </td>
                                    @endif
                                </tr>

                                @if($invoice->status === 'posted' && $item->batchAllocations->isNotEmpty())
                                    <tr class="bg-[var(--surface-muted)]">
                                        <td colspan="{{ $canViewCogs ? 10 : 8 }}" class="py-2.5 px-6">
                                            <div class="text-xs font-semibold text-[var(--primary)] mb-1 flex items-center gap-1">
                                                <x-icon name="layers" size="14" />
                                                <span>تفاصيل توزيع دفعات المخزن (FIFO Batch Allocation):</span>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="erp-table text-xs border border-[var(--border-color)]">
                                                    <thead>
                                                        <tr class="bg-white">
                                                            <th>رقم الدفعة</th>
                                                            <th>المستودع</th>
                                                            <th>الكمية المنصرفة</th>
                                                            <th>سعر البيع المطبق</th>
                                                            @if($canViewCogs)
                                                            <th>تكلفة القطعة (Base Cost)</th>
                                                            <th>إجمالي التكلفة (COGS)</th>
                                                            <th>هامش الربح</th>
                                                            @endif
                                                            <th>حالة التخصيص</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($item->batchAllocations as $alloc)
                                                            <tr class="bg-white">
                                                                <td class="font-numeric font-bold text-[var(--primary)]">
                                                                    {{ $alloc->batch->batch_number ?? '#' . $alloc->inventory_batch_id }}
                                                                </td>
                                                                <td>{{ $alloc->batch->warehouse->name ?? 'المخزن' }}</td>
                                                                <td class="font-numeric font-semibold">{{ (float)$alloc->quantity }} {{ $item->product->unit ?? 'قطعة' }}</td>
                                                                <td class="font-numeric">{{ number_format((float)$alloc->unit_price, 2) }} ج.م</td>
                                                                @if($canViewCogs)
                                                                <td class="font-numeric text-[var(--text-muted)]">{{ number_format((float)$alloc->unit_cost, 4) }} ج.م</td>
                                                                <td class="font-numeric">{{ number_format((float)$alloc->total_cogs, 2) }} ج.م</td>
                                                                <td class="font-numeric font-bold {{ (float)$alloc->profit >= 0 ? 'text-[var(--success)]' : 'text-[var(--danger)]' }}">
                                                                    {{ number_format((float)$alloc->profit, 2) }} ج.م
                                                                </td>
                                                                @endif
                                                                <td>
                                                                    @if($alloc->is_reversed)
                                                                        <span class="erp-badge erp-badge-danger text-[10px]">مسترجع / معكوس</span>
                                                                    @else
                                                                        <span class="erp-badge erp-badge-success text-[10px]">سارٍ ومصروف</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Invoice Totals & Summary Card -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Notes Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="file-text" size="18" />
                        <span>ملاحظات الفاتورة</span>
                    </div>
                </div>
                <div class="erp-card-body text-xs text-[var(--text-muted)]">
                    {{ $invoice->notes ?: 'لا توجد ملاحظات إضافية مسجلة لهذه الفاتورة.' }}
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="calculator" size="18" />
                        <span>الملخص المالي للفاتورة</span>
                    </div>
                </div>
                <div class="erp-card-body p-0">
                    <table class="erp-table text-xs">
                        <tbody>
                            <tr>
                                <td class="text-[var(--text-muted)] font-semibold">إجمالي بنود المبيعات:</td>
                                <td class="text-left font-numeric font-bold">{{ number_format((float)$invoice->subtotal, 2) }} ج.م</td>
                            </tr>
                            @if((float)$invoice->discount_amount > 0)
                            <tr>
                                <td class="text-amber-600 font-semibold">
                                    خصم الفاتورة 
                                    @if($invoice->discount_type === 'percentage')
                                        ({{ (float)$invoice->discount_value }}%):
                                    @else
                                        (قيمة نقدية):
                                    @endif
                                </td>
                                <td class="text-left font-numeric font-bold text-amber-600">- {{ number_format((float)$invoice->discount_amount, 2) }} ج.م</td>
                            </tr>
                            @endif
                            @if((float)$invoice->shipping_cost > 0)
                            <tr>
                                <td class="text-[var(--text-muted)] font-semibold">مصاريف الشحن / التوصيل:</td>
                                <td class="text-left font-numeric font-bold">+ {{ number_format((float)$invoice->shipping_cost, 2) }} ج.م</td>
                            </tr>
                            @endif
                            <tr class="bg-[var(--surface-muted)] font-bold text-sm">
                                <td class="text-[var(--text-main)]">صافي إجمالي الفاتورة:</td>
                                <td class="text-left font-numeric text-[var(--primary)] text-base">{{ number_format((float)$invoice->total_amount, 2) }} ج.م</td>
                            </tr>
                            @if($canViewCogs && $invoice->status === 'posted')
                            <tr>
                                <td class="text-[var(--text-muted)] font-semibold">إجمالي تكلفة البضاعة المباعة (COGS):</td>
                                <td class="text-left font-numeric">{{ number_format((float)$invoice->total_cogs, 2) }} ج.م</td>
                            </tr>
                            <tr class="bg-emerald-50 text-emerald-900 font-bold">
                                <td>صافي ربح الفاتورة:</td>
                                <td class="text-left font-numeric">{{ number_format((float)$invoice->total_profit, 2) }} ج.م</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
