@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="كشف حركة الصنف: {{ $product->name }}" :breadcrumbs="['المنتجات والأصناف' => route('products.index'), $product->name => route('products.show', $product), 'كشف الحركة' => '#']">
        <x-slot:actions>
            <button type="button" data-action="print-window" class="erp-btn erp-btn-secondary">
                <x-icon name="printer" size="16" />
                <span>طباعة كشف الحركة</span>
            </button>
            <a href="{{ route('products.show', $product) }}" class="erp-btn erp-btn-primary">
                <x-icon name="eye" size="16" />
                <span>تفاصيل الصنف</span>
            </a>
            <a href="{{ route('products.index') }}" class="erp-btn erp-btn-ghost">
                <x-icon name="arrow-left" size="16" />
                <span>العودة للأصناف</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric text-xs">
                <span>SKU: {{ $product->sku }}</span>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="product-ledger" class="flex flex-col gap-5">
        <!-- Product Header Overview Card -->
        <div class="erp-card p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($product->image_path)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-14 h-14 rounded object-cover border border-[var(--border-color)]">
                    @else
                        <div class="w-14 h-14 rounded bg-[var(--surface-muted)] flex items-center justify-center text-[var(--text-muted)] border border-[var(--border-color)]">
                            <x-icon name="package" size="24" />
                        </div>
                    @endif
                    <div>
                        <h2 class="text-base font-bold text-[var(--text-main)] m-0">{{ $product->name }}</h2>
                        <div class="flex items-center gap-2 mt-1 text-xs text-[var(--text-muted)]">
                            <span>التصنيف: <strong class="text-[var(--text-main)]">{{ $product->category->name ?? 'غير محدد' }}</strong></span>
                            <span>•</span>
                            <span>الوحدة: <strong class="text-[var(--text-main)]">{{ $product->unit }}</strong></span>
                            @if($product->barcode)
                                <span>•</span>
                                <span class="font-numeric">باركود: {{ $product->barcode }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Live Balance Highlight -->
                <div class="flex items-center gap-6 font-numeric">
                    <div class="text-center p-2 rounded bg-[var(--surface-muted)] border border-[var(--border-color)]">
                        <small class="block text-[11px] text-[var(--text-muted)]">الرصيد الحالي المتوفر</small>
                        <span class="text-lg font-bold text-[var(--primary)]">{{ number_format($product->total_stock, 2) }}</span>
                        <small class="text-xs text-[var(--text-muted)] mr-0.5">{{ $product->unit }}</small>
                    </div>

                    <div class="text-xs flex flex-col gap-1 text-[var(--text-muted)]">
                        <div>سعر التكلفة: <strong class="text-[var(--text-main)]">{{ number_format((float)$product->cost_price, 2) }} ج.م</strong></div>
                        <div>سعر القطاعي: <strong class="text-[var(--text-main)]">{{ number_format((float)$product->retail_price, 2) }} ج.م</strong></div>
                        <div>سعر الجملة: <strong class="text-[var(--text-main)]">{{ number_format((float)$product->wholesale_price, 2) }} ج.م</strong></div>
                    </div>
                </div>
            </div>

            <!-- Warehouse Distribution Pills -->
            @if($product->stockBalances->isNotEmpty())
                <div class="mt-3 pt-3 border-t border-[var(--border-color)] flex flex-wrap items-center gap-2">
                    <span class="text-xs text-[var(--text-muted)]">توزيع الرصيد بالمستودعات:</span>
                    @foreach($product->stockBalances as $sb)
                        <span class="erp-badge erp-badge-neutral text-xs font-numeric">
                            {{ $sb->warehouse->name ?? 'مستودع' }}: <strong>{{ number_format((float)$sb->quantity, 2) }} {{ $product->unit }}</strong>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Filter Bar -->
        <div class="erp-card p-3">
            <form action="{{ route('products.ledger', $product) }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="w-48">
                    <label for="ledgerWarehouse" class="block text-[11px] text-[var(--text-muted)] mb-1">المستودع:</label>
                    <select id="ledgerWarehouse" name="warehouse_id" class="erp-input w-full text-xs">
                        <option value="">-- جميع المستودعات --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$currentWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-40">
                    <label for="ledgerType" class="block text-[11px] text-[var(--text-muted)] mb-1">نوع الحركة:</label>
                    <select id="ledgerType" name="type" class="erp-input w-full text-xs">
                        <option value="">-- جميع الحركات --</option>
                        <option value="in" {{ $currentType === 'in' ? 'selected' : '' }}>وارد / إيداع</option>
                        <option value="out" {{ $currentType === 'out' ? 'selected' : '' }}>منصرف / مبيعات</option>
                        <option value="transfer" {{ $currentType === 'transfer' ? 'selected' : '' }}>تحويل مخزني</option>
                        <option value="adjustment" {{ $currentType === 'adjustment' ? 'selected' : '' }}>تسوية جردية</option>
                    </select>
                </div>

                <div class="w-36">
                    <label for="dateFrom" class="block text-[11px] text-[var(--text-muted)] mb-1">من تاريخ:</label>
                    <input type="date" id="dateFrom" name="date_from" value="{{ $currentDateFrom }}" class="erp-input w-full text-xs font-numeric">
                </div>

                <div class="w-36">
                    <label for="dateTo" class="block text-[11px] text-[var(--text-muted)] mb-1">إلى تاريخ:</label>
                    <input type="date" id="dateTo" name="date_to" value="{{ $currentDateTo }}" class="erp-input w-full text-xs font-numeric">
                </div>

                <div class="flex items-center gap-2 self-end">
                    <button type="submit" class="erp-btn erp-btn-secondary text-xs">
                        <x-icon name="filter" size="14" />
                        <span>تطبيق الفلتر</span>
                    </button>

                    @if($currentWarehouseId || $currentType || $currentDateFrom || $currentDateTo)
                        <a href="{{ route('products.ledger', $product) }}" class="erp-btn erp-btn-ghost text-xs text-[var(--danger)]">
                            <x-icon name="x" size="14" />
                            <span>إلغاء</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Detailed Movement Table -->
        <div class="erp-card overflow-hidden">
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>التاريخ والوقت</th>
                            <th>رقم الحركة</th>
                            <th>نوع الحركة</th>
                            <th>المستودع (من / إلى)</th>
                            <th>الطرف (المورد / العميل)</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-center">تكلفة الوحدة</th>
                            <th class="text-center">الرصيد التراكمي</th>
                            <th>المرجع والبيان</th>
                            <th>المستخدم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                            <tr>
                                <td class="font-numeric text-xs">
                                    {{ $m->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <span class="font-numeric font-bold text-xs text-[var(--text-main)]">{{ $m->movement_number }}</span>
                                </td>
                                <td>
                                    @if($m->reference_type === 'opening_balance')
                                        <span class="erp-badge erp-badge-info text-[11px]">رصيد افتتاحي</span>
                                    @else
                                        <span class="erp-badge {{ $m->type_badge_class }} text-[11px]">
                                            {{ $m->type_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-xs">
                                    @if($m->type === 'transfer')
                                        <span>{{ $m->fromWarehouse->name ?? '—' }} ⬅ {{ $m->toWarehouse->name ?? '—' }}</span>
                                    @elseif($m->toWarehouse)
                                        <span>{{ $m->toWarehouse->name }}</span>
                                    @elseif($m->fromWarehouse)
                                        <span>{{ $m->fromWarehouse->name }}</span>
                                    @else
                                        <span>—</span>
                                    @endif
                                </td>
                                <td class="text-xs">
                                    @if($m->supplier)
                                        <span class="text-[var(--primary)] font-semibold">{{ $m->supplier->name }} (مورد)</span>
                                    @elseif($m->customer)
                                        <span class="text-[var(--success-text)] font-semibold">{{ $m->customer->name }} (عميل)</span>
                                    @else
                                        <span class="text-[var(--text-muted)]">—</span>
                                    @endif
                                </td>
                                <td class="font-numeric text-center font-bold text-xs">
                                    @if($m->type === 'in' || $m->reference_type === 'opening_balance')
                                        <span class="text-[var(--success-text)]">+{{ number_format((float)$m->quantity, 2) }}</span>
                                    @elseif($m->type === 'out')
                                        <span class="text-[var(--danger)]">-{{ number_format((float)$m->quantity, 2) }}</span>
                                    @else
                                        <span>{{ number_format((float)$m->quantity, 2) }}</span>
                                    @endif
                                    <small class="text-[10px] text-[var(--text-muted)]">{{ $product->unit }}</small>
                                </td>
                                <td class="font-numeric text-center text-xs">
                                    {{ number_format((float)$m->unit_cost, 2) }} ج.م
                                </td>
                                <td class="font-numeric text-center font-bold text-xs text-[var(--primary)] bg-[var(--surface-muted)]">
                                    {{ $m->running_balance !== null ? number_format((float)$m->running_balance, 2) : '—' }}
                                </td>
                                <td class="text-xs text-[var(--text-muted)]">
                                    <div>{{ $m->notes ?? '—' }}</div>
                                    @if($m->reference_type)
                                        <small class="text-[10px] text-[var(--text-muted)] block font-numeric">{{ $m->reference_type }}</small>
                                    @endif
                                </td>
                                <td class="text-xs text-[var(--text-muted)]">
                                    {{ $m->user->name ?? 'النظام' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-10 text-[var(--text-muted)]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-icon name="activity" size="32" class="opacity-40" />
                                        <p class="m-0 text-sm font-semibold">لا توجد حركات مخزنية مسجلة لهذا الصنف حتى الآن.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="p-3 border-t border-[var(--border-color)]">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
