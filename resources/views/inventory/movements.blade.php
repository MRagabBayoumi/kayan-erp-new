@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="سجل حركات المخزون" :breadcrumbs="['المخزون والمنتجات' => route('inventory.index'), 'سجل الحركات' => route('inventory.movements')]">
        <x-slot:actions>
            <a href="{{ route('inventory.index') }}" class="erp-btn erp-btn-primary">
                <x-icon name="arrow-left-right" size="16" />
                <span>نظرة عامة وتسجيل حركة</span>
            </a>
            <a href="{{ route('inventory.low-stock') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="alert-triangle" size="16" />
                <span>نواقص المخزون</span>
            </a>
            <a href="{{ route('warehouses.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="home" size="16" />
                <span>المستودعات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric" style="padding: 4px 10px; font-size: 12px;">
                <span>إجمالي الحركات:</span>
                <strong>{{ $movements->total() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Search & Filters Bar -->
    <div class="erp-card" style="margin-bottom: 16px; padding: 12px 16px;">
        <form action="{{ route('inventory.movements') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div style="flex: 1; min-width: 220px;">
                <input 
                    type="search" 
                    name="search" 
                    value="{{ $searchTerm }}" 
                    class="erp-input font-numeric" 
                    placeholder="بحث برقم الحركة، كود الصنف، أو اسم الصنف..."
                    style="width: 100%;">
            </div>

            <!-- Type Filter -->
            <div style="min-width: 160px;">
                <select name="type" class="erp-input" onchange="this.form.submit()">
                    <option value="">جميع أنواع الحركات</option>
                    <option value="in" {{ $selectedType === 'in' ? 'selected' : '' }}>إيداع / وارد</option>
                    <option value="out" {{ $selectedType === 'out' ? 'selected' : '' }}>صرف / منصرف</option>
                    <option value="transfer" {{ $selectedType === 'transfer' ? 'selected' : '' }}>تحويل مخزني</option>
                    <option value="adjustment" {{ $selectedType === 'adjustment' ? 'selected' : '' }}>تسوية جردية</option>
                </select>
            </div>

            <!-- Warehouse Filter -->
            <div style="min-width: 180px;">
                <select name="warehouse_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">جميع المستودعات</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="erp-btn erp-btn-secondary">
                <x-icon name="search" size="14" />
                <span>تصفية</span>
            </button>

            @if($searchTerm || $selectedType || $selectedWarehouseId)
                <a href="{{ route('inventory.movements') }}" class="erp-btn erp-btn-secondary" title="إلغاء الفلاتر">
                    ✕ إعادة ضبط
                </a>
            @endif
        </form>
    </div>

    <!-- Movements Table -->
    <div class="erp-card" style="overflow-x: auto; margin-bottom: 20px;">
        <table class="erp-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>رقم الحركة</th>
                    <th>التاريخ والوقت</th>
                    <th class="text-center">نوع الحركة</th>
                    <th>الصنف</th>
                    <th>المستودع المصدر / الوجهة</th>
                    <th class="text-center">الكمية</th>
                    <th class="text-left">سعر الوحدة</th>
                    <th>المرجع / ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td class="font-numeric" style="color: var(--text-muted);">{{ $loop->iteration + ($movements->currentPage() - 1) * $movements->perPage() }}</td>
                        <td>
                            <span class="font-numeric" style="font-weight: 700; color: var(--text-main);">{{ $m->movement_number }}</span>
                        </td>
                        <td class="font-numeric" style="font-size: 12px; color: var(--text-secondary);">
                            {{ $m->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="text-center">
                            <span class="erp-badge {{ $m->type_badge_class }}">
                                {{ $m->type_label }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);">{{ $m->product?->name }}</div>
                            <span class="font-numeric" style="font-size: 11px; color: var(--text-muted);">[{{ $m->product?->sku }}]</span>
                        </td>
                        <td>
                            @if($m->type === 'transfer')
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                                    <span>{{ $m->fromWarehouse?->name ?: '—' }}</span>
                                    <span style="color: var(--text-muted);">←</span>
                                    <strong>{{ $m->toWarehouse?->name ?: '—' }}</strong>
                                </div>
                            @elseif($m->type === 'in')
                                <span style="color: var(--primary, #0d9488); font-weight: 600;">{{ $m->toWarehouse?->name ?: '—' }}</span>
                            @else
                                <span style="color: var(--danger, #ef4444); font-weight: 600;">{{ $m->fromWarehouse?->name ?: '—' }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="font-numeric" style="font-size: 14px; font-weight: 800; color: {{ $m->type === 'in' ? 'var(--primary, #0d9488)' : ($m->type === 'out' ? 'var(--danger, #ef4444)' : 'var(--text-main)') }};">
                                {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '-' : '') }}{{ number_format($m->quantity) }}
                            </span>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $m->product?->unit }}</span>
                        </td>
                        <td class="text-left font-numeric" style="font-size: 13px;">
                            {{ number_format($m->unit_cost, 2) }} ج.م
                        </td>
                        <td style="font-size: 12px; color: var(--text-secondary); max-width: 200px;">
                            {{ $m->notes ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                            <x-icon name="arrow-left-right" size="36" style="margin-bottom: 8px; opacity: 0.5;" />
                            <div>لا توجد حركات مخزنية مسجلة حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($movements->hasPages())
        <div style="margin-top: 16px;">
            {{ $movements->links() }}
        </div>
    @endif
@endsection
