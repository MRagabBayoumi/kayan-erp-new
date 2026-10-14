@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="فاتورة شراء: {{ $invoice->invoice_number }}" :breadcrumbs="['المشتريات والموردين' => route('purchases.index'), $invoice->invoice_number => '#']">
        <x-slot:actions>
            @if($invoice->status === 'draft')
                <form action="{{ route('purchases.post', $invoice) }}" method="POST" data-confirm="هل تريد ترحيل هذه الفاتورة إلى المخازن وتوليد دفعات FIFO آلياً؟">
                    @csrf
                    <button type="submit" class="erp-btn erp-btn-primary">
                        <x-icon name="check-circle" size="16" />
                        <span>ترحيل للمخازن (إيداع FIFO)</span>
                    </button>
                </form>

                <form action="{{ route('purchases.cancel', $invoice) }}" method="POST" data-confirm="تأكيد إلغاء هذه المسودة؟">
                    @csrf
                    <button type="submit" class="erp-btn erp-btn-secondary text-[var(--danger)]">
                        <x-icon name="x-circle" size="16" />
                        <span>إلغاء الفاتورة</span>
                    </button>
                </form>
            @endif

            <div class="inline-flex items-center gap-1">
                <a href="{{ route('purchases.print', [$invoice, 'format' => 'a4']) }}" target="_blank" class="erp-btn erp-btn-secondary" title="فتح نافذة طباعة رسمية على ورق A4">
                    <x-icon name="printer" size="16" />
                    <span>طباعة الفاتورة (A4)</span>
                </a>
                <a href="{{ route('purchases.print', [$invoice, 'format' => 'a5']) }}" target="_blank" class="erp-btn erp-btn-ghost text-xs px-2" title="طباعة مقاس A5">
                    <span>A5</span>
                </a>
                <a href="{{ route('purchases.print', [$invoice, 'format' => 'thermal_80mm']) }}" target="_blank" class="erp-btn erp-btn-ghost text-xs px-2" title="طباعة إيصال حراري 80 مم">
                    <span>80 مم</span>
                </a>
            </div>

            <a href="{{ route('purchases.index') }}" class="erp-btn erp-btn-ghost">
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
    <div data-page="purchases-show" class="flex flex-col gap-5">
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

        <!-- Invoice Details Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Supplier Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="factory" size="18" />
                        <span>بيانات المورد والمصدر</span>
                    </div>
                </div>
                <div class="erp-card-body flex flex-col gap-2 text-xs">
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">اسم المورد / المصنع:</span>
                        <strong class="text-sm text-[var(--text-main)]">{{ $invoice->supplier->name ?? 'غير محدد' }}</strong>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">الدولة والمدينة:</span>
                        <span>{{ $invoice->supplier->country ?? '—' }} ({{ $invoice->supplier->city ?? '—' }})</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">رقم الهاتف / ويشات:</span>
                        <span class="font-numeric">{{ $invoice->supplier->phone ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Warehouse & Delivery Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="home" size="18" />
                        <span>مستودع وتاريخ الاستلام</span>
                    </div>
                </div>
                <div class="erp-card-body flex flex-col gap-2 text-xs">
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">المستودع المستلم:</span>
                        <strong class="text-sm text-[var(--primary)]">{{ $invoice->warehouse->name ?? 'غير محدد' }}</strong>
                        <span class="text-[11px] text-[var(--text-muted)]">({{ $invoice->warehouse->code ?? '' }})</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">تاريخ الفاتورة:</span>
                        <span class="font-numeric font-bold text-xs">{{ $invoice->invoice_date->format('Y-m-d') }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-muted)] block mb-0.5">تاريخ الترحيل للمخزن:</span>
                        <span class="font-numeric">{{ $invoice->posted_at ? $invoice->posted_at->format('Y-m-d H:i') : 'لم يتم الترحيل بعد' }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="banknote" size="18" />
                        <span>الملخص المالي للفاتورة</span>
                    </div>
                </div>
                <div class="erp-card-body flex flex-col gap-2 text-xs font-numeric">
                    <div class="flex justify-between items-center py-1 border-b border-[var(--border-color)]">
                        <span class="text-[var(--text-muted)]">مجموع الأصناف:</span>
                        <span class="font-bold">{{ number_format((float)$invoice->subtotal, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-[var(--border-color)]">
                        <span class="text-[var(--text-muted)]">الشحن والتخليص:</span>
                        <span>{{ number_format((float)$invoice->shipping_cost, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between items-center pt-1 text-sm font-bold text-[var(--primary)]">
                        <span>إجمالي الفاتورة:</span>
                        <span class="text-base">{{ number_format((float)$invoice->total_amount, 2) }} ج.م</span>
                    </div>
                </div>
            </div>
        </div>

        @if($invoice->notes)
            <div class="erp-card p-3 text-xs bg-[var(--surface-muted)]">
                <strong class="text-[var(--text-main)] block mb-1">ملاحظات الفاتورة:</strong>
                <p class="m-0 text-[var(--text-muted)] leading-relaxed">{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Items Table Card -->
        <div class="erp-card overflow-hidden">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="package" size="18" />
                    <span>أصناف الفاتورة ({{ $invoice->items->count() }} صنف)</span>
                </div>
            </div>
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>كود الصنف</th>
                            <th>اسم المنتج</th>
                            <th>التصنيف</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-center">سعر التكلفة</th>
                            <th class="text-center">الإجمالي</th>
                            <th>ملاحظات البند</th>
                            <th class="text-center">كشف الحركة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="font-numeric text-center text-xs text-[var(--text-muted)]">{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-numeric font-bold text-xs text-[var(--primary)]">{{ $item->product->sku ?? '—' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $item->product) }}" class="font-semibold text-xs text-[var(--text-main)] hover:underline">
                                        {{ $item->product->name ?? '—' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="erp-badge erp-badge-neutral text-[11px]">{{ $item->product->category->name ?? '—' }}</span>
                                </td>
                                <td class="font-numeric text-center font-bold text-xs">
                                    {{ number_format((float)$item->quantity, 2) }} {{ $item->product->unit ?? 'قطعة' }}
                                </td>
                                <td class="font-numeric text-center text-xs">
                                    {{ number_format((float)$item->unit_cost, 2) }} ج.م
                                </td>
                                <td class="font-numeric text-center font-bold text-xs text-[var(--text-main)]">
                                    {{ number_format((float)$item->subtotal, 2) }} ج.م
                                </td>
                                <td class="text-xs text-[var(--text-muted)]">
                                    {{ $item->notes ?? '—' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('products.ledger', $item->product) }}" class="erp-btn erp-btn-ghost erp-btn-icon text-xs text-[var(--primary)]" title="كشف حركة الصنف">
                                        <x-icon name="activity" size="14" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Generated FIFO Batches (If Posted) -->
        @if($invoice->status === 'posted' && $invoice->batches->isNotEmpty())
            <div class="erp-card overflow-hidden">
                <div class="erp-card-header flex items-center justify-between">
                    <div class="erp-card-title">
                        <x-icon name="layers" size="18" />
                        <span>دفعات المخزون الناتجة عن هذه الفاتورة (FIFO Batches)</span>
                    </div>
                    <span class="erp-badge erp-badge-success text-xs">مُسجلة بالمخازن</span>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>رقم الدفعة</th>
                                <th>الصنف</th>
                                <th>المستودع</th>
                                <th class="text-center">الكمية الأولية</th>
                                <th class="text-center">الكمية المتبقية</th>
                                <th class="text-center">تكلفة الوحدة</th>
                                <th class="text-center">حالة الصرف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->batches as $batch)
                                <tr>
                                    <td>
                                        <span class="font-numeric font-bold text-xs text-[var(--primary)]">{{ $batch->batch_number }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $batch->product) }}" class="font-semibold text-xs hover:underline">
                                            {{ $batch->product->name ?? '—' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="text-xs">{{ $batch->warehouse->name ?? '—' }}</span>
                                    </td>
                                    <td class="font-numeric text-center text-xs">
                                        {{ number_format((float)$batch->initial_quantity, 2) }}
                                    </td>
                                    <td class="font-numeric text-center font-bold text-xs {{ $batch->remaining_quantity > 0 ? 'text-[var(--success-text)]' : 'text-[var(--danger)]' }}">
                                        {{ number_format((float)$batch->remaining_quantity, 2) }}
                                    </td>
                                    <td class="font-numeric text-center text-xs">
                                        {{ number_format((float)$batch->cost_price, 2) }} ج.م
                                    </td>
                                    <td class="text-center">
                                        @if($batch->is_exhausted)
                                            <span class="erp-badge erp-badge-neutral text-[10px]">نَفِدت بالكامل</span>
                                        @else
                                            <span class="erp-badge erp-badge-success text-[10px]">متاح منها للصرف</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
