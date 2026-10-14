@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="فواتير الشراء والتوريد" :breadcrumbs="['المشتريات والموردين' => '#', 'فواتير الشراء' => '#']">
        <x-slot:actions>
            <a href="{{ route('purchases.create') }}" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>فاتورة شراء جديدة</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="factory" size="16" />
                <span>دليل الموردين</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric">
                <x-icon name="shopping-bag" size="14" />
                <span>إجمالي الفواتير: {{ $metrics['total_count'] }}</span>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="purchases-index" class="flex flex-col gap-5">
        <!-- Status Alerts -->
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

        <!-- Summary KPI Cards -->
        <div class="erp-kpi-grid">
            <div class="erp-kpi-card">
                <div class="erp-kpi-header">
                    <span class="erp-kpi-title">إجمالي فواتير الشراء</span>
                    <span class="erp-kpi-icon-wrap"><x-icon name="shopping-bag" size="18" /></span>
                </div>
                <div class="erp-kpi-value font-numeric">{{ $metrics['total_count'] }}</div>
                <div class="erp-kpi-subtitle">جميع الفواتير المسجلة</div>
            </div>

            <div class="erp-kpi-card">
                <div class="erp-kpi-header">
                    <span class="erp-kpi-title">مشتريات مُرحلة للمخازن</span>
                    <span class="erp-kpi-icon-wrap text-[var(--success)]"><x-icon name="check-circle" size="18" /></span>
                </div>
                <div class="erp-kpi-value font-numeric text-[var(--success)]">{{ number_format($metrics['posted_total'], 2) }} ج.م</div>
                <div class="erp-kpi-subtitle">تم إيداعها وتوليد دفعات FIFO</div>
            </div>

            <div class="erp-kpi-card">
                <div class="erp-kpi-header">
                    <span class="erp-kpi-title">مسودات قيد الإعداد</span>
                    <span class="erp-kpi-icon-wrap text-[var(--warning)]"><x-icon name="file-text" size="18" /></span>
                </div>
                <div class="erp-kpi-value font-numeric text-[var(--warning)]">{{ $metrics['draft_count'] }}</div>
                <div class="erp-kpi-subtitle">تتطلب مراجعة وترحيل</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="erp-card p-3">
            <form action="{{ route('purchases.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $currentSearch }}" 
                            class="erp-input w-full pr-8" 
                            placeholder="بحث برقم الفاتورة أو اسم المورد..."
                        >
                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none">
                            <x-icon name="search" size="15" />
                        </span>
                    </div>
                </div>

                <div class="w-40">
                    <select name="status" class="erp-input w-full">
                        <option value="">-- كل الحالات --</option>
                        <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="posted" {{ $currentStatus === 'posted' ? 'selected' : '' }}>مرحلة للمخازن</option>
                        <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                    </select>
                </div>

                <div class="w-48">
                    <select name="warehouse_id" class="erp-input w-full">
                        <option value="">-- كل المستودعات --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$currentWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-48">
                    <select name="supplier_id" class="erp-input w-full">
                        <option value="">-- كل الموردين --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ (string)$currentSupplierId === (string)$sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="erp-btn erp-btn-secondary">
                    <x-icon name="filter" size="15" />
                    <span>تصفية</span>
                </button>

                @if($currentSearch || $currentStatus || $currentWarehouseId || $currentSupplierId)
                    <a href="{{ route('purchases.index') }}" class="erp-btn erp-btn-ghost text-xs text-[var(--danger)]">
                        <x-icon name="x" size="14" />
                        <span>إلغاء التصفية</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Invoices Data Table -->
        <div class="erp-card overflow-hidden">
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المورد</th>
                            <th>تاريخ الفاتورة</th>
                            <th>مستودع الاستلام</th>
                            <th>عدد البنود</th>
                            <th>مصاريف الشحن</th>
                            <th>إجمالي الفاتورة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('purchases.show', $inv) }}" class="font-numeric font-bold text-[var(--primary)] hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-semibold text-xs text-[var(--text-main)]">{{ $inv->supplier->name ?? 'غير محدد' }}</div>
                                    <small class="text-[11px] text-[var(--text-muted)]">{{ $inv->supplier->country ?? '' }}</small>
                                </td>
                                <td class="font-numeric text-xs">
                                    {{ $inv->invoice_date->format('Y-m-d') }}
                                </td>
                                <td>
                                    <span class="text-xs">{{ $inv->warehouse->name ?? '—' }}</span>
                                </td>
                                <td class="font-numeric text-center text-xs">
                                    {{ $inv->items->count() }}
                                </td>
                                <td class="font-numeric text-xs">
                                    {{ number_format((float)$inv->shipping_cost, 2) }} ج.م
                                </td>
                                <td class="font-numeric font-bold text-xs text-[var(--text-main)]">
                                    {{ number_format((float)$inv->total_amount, 2) }} ج.م
                                </td>
                                <td>
                                    <span class="erp-badge {{ $inv->status_badge_class }}">
                                        {{ $inv->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('purchases.show', $inv) }}" class="erp-btn erp-btn-ghost erp-btn-icon" title="عرض التفاصيل">
                                            <x-icon name="eye" size="14" />
                                        </a>

                                        @if($inv->status === 'draft')
                                            <form action="{{ route('purchases.post', $inv) }}" method="POST" data-confirm="هل أنت متأكد من ترحيل هذه الفاتورة وإيداع البضاعة في المستودع؟">
                                                @csrf
                                                <button type="submit" class="erp-btn erp-btn-ghost erp-btn-icon text-[var(--success)]" title="ترحيل للمخازن">
                                                    <x-icon name="check-circle" size="14" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-[var(--text-muted)]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-icon name="shopping-bag" size="36" class="opacity-40" />
                                        <p class="m-0 text-sm font-semibold">لا توجد فواتير شراء مسجلة تطابق معايير البحث.</p>
                                        <a href="{{ route('purchases.create') }}" class="erp-btn erp-btn-primary text-xs mt-1">
                                            <x-icon name="plus" size="14" />
                                            <span>إنشاء فاتورة شراء جديدة</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-3 border-t border-[var(--border-color)]">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
