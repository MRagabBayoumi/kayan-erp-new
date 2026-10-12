@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="نواقص وتنبيهات المخزون" :breadcrumbs="['المخزون والمنتجات' => route('inventory.index'), 'نواقص المخزون' => route('inventory.low-stock')]">
        <x-slot:actions>
            <a href="{{ route('inventory.index') }}" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>تسجيل إيداع مخزون</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-left-right" size="16" />
                <span>سجل الحركات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-warning font-numeric" style="padding: 4px 10px; font-size: 12px;">
                <span>أصناف تتطلب إعادة الطلب:</span>
                <strong>{{ $products->total() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Alert Notice -->
    <div class="erp-alert erp-alert-warning" role="alert" style="margin-bottom: 16px;">
        <x-icon name="alert-triangle" size="18" />
        <div>
            <strong>تنبيه إعادة الطلب:</strong> تُعرض هنا كافة الأصناف التي وصل رصيدها الفعلي في جميع المستودعات إلى حد الأمان المحدد للصنف (الحد الأدنى للطلب) أو نفدت تماماً.
        </div>
    </div>

    <!-- Category Filter -->
    <div class="erp-card" style="margin-bottom: 16px; padding: 12px 16px;">
        <form action="{{ route('inventory.low-stock') }}" method="GET" class="erp-filter-form">
            <div style="min-width: 200px;">
                <select name="category_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">جميع التصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedCategoryId)
                <a href="{{ route('inventory.low-stock') }}" class="erp-btn erp-btn-secondary" title="إلغاء الفلتر">
                    ✕ عرض الكل
                </a>
            @endif
        </form>
    </div>

    <!-- Low Stock Table -->
    <div class="erp-card" style="overflow-x: auto; margin-bottom: 20px;">
        <table class="erp-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>كود الصنف (SKU)</th>
                    <th>اسم الصنف</th>
                    <th>التصنيف</th>
                    <th class="text-center">الرصيد المتبقي</th>
                    <th class="text-center">الحد الأدنى للطلب</th>
                    <th class="text-center">العجز / المطلوب</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-left">سعر التكلفة</th>
                    <th style="width: 120px; text-align: center;">إجراء سريع</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $currentStock = (float) ($product->stock_balances_sum_quantity ?? 0);
                        $minAlert = (int) $product->min_stock_alert;
                        $deficit = max(0, $minAlert - $currentStock);
                        $isOut = $currentStock <= 0;
                    @endphp
                    <tr>
                        <td class="font-numeric" style="color: var(--text-muted);">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td>
                            <span class="font-numeric" style="font-weight: 700; color: var(--text-main);">{{ $product->sku }}</span>
                            @if($product->barcode)
                                <div class="font-numeric" style="font-size: 11px; color: var(--text-muted);">{{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);">{{ $product->name }}</div>
                            <span style="font-size: 11px; color: var(--text-muted);">الوحدة: {{ $product->unit }}</span>
                        </td>
                        <td>
                            <span class="erp-badge erp-badge-neutral">{{ $product->category?->name ?? 'بدون تصنيف' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="font-numeric" style="font-size: 15px; font-weight: 800; color: {{ $isOut ? 'var(--danger, #ef4444)' : 'var(--warning, #f59e0b)' }};">
                                {{ number_format($currentStock) }}
                            </span>
                        </td>
                        <td class="text-center font-numeric" style="font-size: 14px; color: var(--text-secondary);">
                            {{ $minAlert }}
                        </td>
                        <td class="text-center">
                            <span class="font-numeric" style="font-weight: 700; color: var(--danger, #ef4444);">
                                +{{ number_format($deficit > 0 ? $deficit : 1) }} قطعة
                            </span>
                        </td>
                        <td class="text-center">
                            @if($isOut)
                                <span class="erp-badge erp-badge-danger">نفد بالكامل</span>
                            @else
                                <span class="erp-badge erp-badge-warning">أوشك على النفاد</span>
                            @endif
                        </td>
                        <td class="text-left font-numeric">
                            {{ number_format($product->cost_price, 2) }} ج.م
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('inventory.index') }}?search={{ urlencode($product->sku) }}" class="erp-btn erp-btn-sm erp-btn-secondary">
                                <x-icon name="plus" size="13" />
                                <span>إيداع</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <x-icon name="check-circle" size="36" style="color: var(--success, #10b981); margin-bottom: 8px;" />
                            <div style="font-size: 15px; font-weight: 600; color: var(--text-main);">ممتاز! لا توجد نواقص في المخزون حالياً</div>
                            <div style="font-size: 13px; margin-top: 4px;">كافة الأصناف تتجاوز الحد الأدنى للأمان.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div style="margin-top: 16px;">
            {{ $products->links() }}
        </div>
    @endif
@endsection
