@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="نظرة عامة على المخزون" :breadcrumbs="['المخزون والمنتجات' => route('inventory.index'), 'الأرصدة والمخازن' => route('inventory.index')]">
        <x-slot:actions>
            <button type="button" onclick="openStockMovementModal()" class="erp-btn erp-btn-primary">
                <x-icon name="arrow-left-right" size="16" />
                <span>تسجيل حركة مخزون جديدة</span>
            </button>
            <a href="{{ route('inventory.movements') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="file-text" size="16" />
                <span>سجل الحركات</span>
            </a>
            <a href="{{ route('inventory.low-stock') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="alert-triangle" size="16" />
                <span>النواقص ({{ $metrics['low_stock_count'] + $metrics['out_of_stock_count'] }})</span>
            </a>
            <a href="{{ route('warehouses.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="home" size="16" />
                <span>إدارة المستودعات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric" style="padding: 4px 10px; font-size: 12px;">
                <span>إجمالي الأصناف:</span>
                <strong>{{ $metrics['total_products'] }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="erp-alert erp-alert-success" role="alert" style="margin-bottom: 16px;">
            <x-icon name="check" size="16" />
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 16px;">
            <x-icon name="alert-triangle" size="16" />
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <!-- Total Stock Units -->
        <div class="erp-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(13, 148, 136, 0.1); color: var(--primary, #0d9488); display: flex; align-items: center; justify-content: center;">
                <x-icon name="package" size="22" />
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">إجمالي وحدات المخزون</div>
                <div class="font-numeric" style="font-size: 20px; font-weight: 800; color: var(--text-main); margin-top: 2px;">
                    {{ number_format($metrics['total_stock_units']) }} <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">قطعة</span>
                </div>
            </div>
        </div>

        <!-- FIFO Inventory Valuation -->
        <div class="erp-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                <x-icon name="banknote" size="22" />
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">تقييم المخزون بالتكلفة (FIFO)</div>
                <div class="font-numeric" style="font-size: 20px; font-weight: 800; color: var(--text-main); margin-top: 2px;">
                    {{ number_format($metrics['total_inventory_value'], 2) }} <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">ج.م</span>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="erp-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                <x-icon name="alert-triangle" size="22" />
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">أصناف تحت حد الطلب</div>
                <div class="font-numeric" style="font-size: 20px; font-weight: 800; color: #f59e0b; margin-top: 2px;">
                    {{ $metrics['low_stock_count'] }} <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">صنف</span>
                </div>
            </div>
        </div>

        <!-- Out of Stock Items -->
        <div class="erp-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                <x-icon name="x" size="22" />
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">أصناف نفدت تماماً</div>
                <div class="font-numeric" style="font-size: 20px; font-weight: 800; color: #ef4444; margin-top: 2px;">
                    {{ $metrics['out_of_stock_count'] }} <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">صنف</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="erp-card" style="margin-bottom: 16px; padding: 12px 16px;">
        <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div style="flex: 1; min-width: 220px;">
                <input 
                    type="search" 
                    name="search" 
                    value="{{ $searchTerm }}" 
                    class="erp-input font-numeric" 
                    placeholder="بحث باسم الصنف، كود SKU، أو الباركود..."
                    style="width: 100%;">
            </div>

            <!-- Warehouse Filter -->
            <div style="min-width: 180px;">
                <select name="warehouse_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">جميع المستودعات</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_default ? '(الافتراضي)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div style="min-width: 180px;">
                <select name="category_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">جميع التصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="erp-btn erp-btn-secondary">
                <x-icon name="search" size="14" />
                <span>تصفية</span>
            </button>

            @if($searchTerm || $selectedWarehouseId || $selectedCategoryId)
                <a href="{{ route('inventory.index') }}" class="erp-btn erp-btn-secondary" title="إلغاء الفلاتر">
                    ✕ إعادة ضبط
                </a>
            @endif
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="erp-card" style="overflow-x: auto; margin-bottom: 20px;">
        <table class="erp-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>كود الصنف (SKU)</th>
                    <th>اسم الصنف</th>
                    <th>التصنيف</th>
                    <th class="text-left">سعر التكلفة</th>
                    <th class="text-center">الرصيد الكلي</th>
                    <th>توزيع المخازن</th>
                    <th class="text-center">حالة الرصيد</th>
                    <th class="text-left">قيمة المخزون</th>
                    <th style="width: 130px; text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $totalStock = (float) ($product->stock_balances_sum_quantity ?? 0);
                        $isOutOfStock = $totalStock <= 0;
                        $isLowStock = !$isOutOfStock && $totalStock <= $product->min_stock_alert;
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
                        <td class="text-left font-numeric">
                            {{ number_format($product->cost_price, 2) }} ج.م
                        </td>
                        <td class="text-center">
                            <span class="font-numeric" style="font-size: 15px; font-weight: 800; color: {{ $isOutOfStock ? 'var(--danger, #ef4444)' : ($isLowStock ? 'var(--warning, #f59e0b)' : 'var(--text-main)') }};">
                                {{ number_format($totalStock) }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                @forelse($product->stockBalances as $sb)
                                    @if($sb->quantity > 0)
                                        <div style="font-size: 11px; display: flex; justify-content: space-between; gap: 8px;">
                                            <span style="color: var(--text-secondary);">{{ $sb->warehouse->name }}:</span>
                                            <strong class="font-numeric" style="color: var(--text-main);">{{ number_format($sb->quantity) }}</strong>
                                        </div>
                                    @endif
                                @empty
                                    <span style="font-size: 11px; color: var(--text-muted);">— لا يوجد رصيد —</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-center">
                            @if($isOutOfStock)
                                <span class="erp-badge erp-badge-danger">نفد الرصيد</span>
                            @elseif($isLowStock)
                                <span class="erp-badge erp-badge-warning">منخفض (حد: {{ $product->min_stock_alert }})</span>
                            @else
                                <span class="erp-badge erp-badge-success">متوفر</span>
                            @endif
                        </td>
                        <td class="text-left font-numeric" style="font-weight: 700; color: var(--text-main);">
                            {{ number_format($totalStock * (float) $product->cost_price, 2) }} ج.م
                        </td>
                        <td style="text-align: center;">
                            <button 
                                type="button" 
                                onclick="openQuickMovementModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku }}', {{ (float) $product->cost_price }})"
                                class="erp-btn erp-btn-sm erp-btn-secondary"
                                title="إجراء حركة أو تسوية">
                                <x-icon name="arrow-left-right" size="13" />
                                <span>حركة</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 36px; color: var(--text-muted);">
                            <x-icon name="package" size="36" style="margin-bottom: 8px; opacity: 0.5;" />
                            <div>لا توجد أصناف مطابقة للبحث أو الفلتر المحدد</div>
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

    <!-- Modal: Stock Movement (Dynamic In / Out / Transfer / Adjustment) -->
    <div id="stockMovementModal" class="erp-modal-overlay">
        <div class="erp-modal-container" style="max-width: 540px;">
            <div class="erp-modal-header">
                <div>
                    <h3 class="erp-modal-title" style="margin: 0;">
                        <x-icon name="arrow-left-right" size="18" />
                        <span>تسجيل حركة مخزون</span>
                    </h3>
                    <div id="modal_product_subtitle" style="font-size: 12px; color: var(--text-muted); margin-top: 2px;"></div>
                </div>
                <button type="button" onclick="closeStockMovementModal()" class="erp-modal-close" title="إغلاق">✕</button>
            </div>

            <form action="{{ route('inventory.movements.store') }}" method="POST">
                @csrf
                <div class="erp-modal-body">
                    <!-- Movement Type Selector -->
                    <div style="margin-bottom: 16px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">نوع الحركة *</label>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <label style="border: 1px solid var(--border-light); padding: 8px; border-radius: 6px; text-align: center; cursor: pointer; font-size: 12px; font-weight: 600;" id="type_label_in">
                                <input type="radio" name="movement_type" value="in" checked onchange="updateMovementFields()" style="display: none;">
                                إيداع / وارد
                            </label>
                            <label style="border: 1px solid var(--border-light); padding: 8px; border-radius: 6px; text-align: center; cursor: pointer; font-size: 12px; font-weight: 600;" id="type_label_out">
                                <input type="radio" name="movement_type" value="out" onchange="updateMovementFields()" style="display: none;">
                                صرف (FIFO)
                            </label>
                            <label style="border: 1px solid var(--border-light); padding: 8px; border-radius: 6px; text-align: center; cursor: pointer; font-size: 12px; font-weight: 600;" id="type_label_transfer">
                                <input type="radio" name="movement_type" value="transfer" onchange="updateMovementFields()" style="display: none;">
                                تحويل مخزني
                            </label>
                            <label style="border: 1px solid var(--border-light); padding: 8px; border-radius: 6px; text-align: center; cursor: pointer; font-size: 12px; font-weight: 600;" id="type_label_adjustment">
                                <input type="radio" name="movement_type" value="adjustment" onchange="updateMovementFields()" style="display: none;">
                                تسوية جردية
                            </label>
                        </div>
                    </div>

                    <!-- Product Selection -->
                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">الصنف *</label>
                        <select id="modal_product_id" name="product_id" class="erp-input" required style="width: 100%;">
                            <option value="">-- اختر الصنف --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}">
                                    [{{ $prod->sku }}] {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Standard Warehouse Selection (For In, Out, Adjustment) -->
                    <div id="single_warehouse_box" style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">المستودع *</label>
                        <select name="warehouse_id" class="erp-input" style="width: 100%;">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $wh->is_default ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_default ? '(الافتراضي)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Transfer Warehouses Selection (From -> To) -->
                    <div id="transfer_warehouses_box" style="display: none; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                        <div>
                            <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">من مستودع *</label>
                            <select name="from_warehouse_id" class="erp-input" style="width: 100%;">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">إلى مستودع *</label>
                            <select name="to_warehouse_id" class="erp-input" style="width: 100%;">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ !$loop->first ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Quantity & Unit Cost -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                        <div>
                            <label id="qty_label" class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">الكمية *</label>
                            <input type="number" step="0.01" min="0.01" name="quantity" class="erp-input font-numeric" placeholder="مثال: 50" required style="width: 100%;">
                        </div>
                        <div id="cost_box">
                            <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">سعر التكلفة (ج.م)</label>
                            <input type="number" step="0.01" min="0" id="modal_unit_cost" name="unit_cost" class="erp-input font-numeric" placeholder="التكلفة..." style="width: 100%;">
                        </div>
                    </div>

                    <!-- Notes / Reason -->
                    <div style="margin-bottom: 10px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">ملاحظات / سبب الحركة</label>
                        <textarea name="notes" class="erp-input" rows="2" placeholder="رقم الفاتورة، أو سبب التسوية أو التحويل..." style="width: 100%;"></textarea>
                    </div>
                </div>

                <div class="erp-modal-footer">
                    <button type="button" onclick="closeStockMovementModal()" class="erp-btn erp-btn-secondary">إلغاء</button>
                    <button type="submit" class="erp-btn erp-btn-primary">تأكيد وتنفيذ الحركة</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStockMovementModal() {
            const m = document.getElementById('stockMovementModal');
            if (m) {
                document.getElementById('modal_product_subtitle').innerText = '';
                m.classList.add('open');
                m.style.display = 'flex';
                updateMovementFields();
            }
        }

        function closeStockMovementModal() {
            const m = document.getElementById('stockMovementModal');
            if (m) {
                m.classList.remove('open');
                m.style.display = 'none';
            }
        }

        function openQuickMovementModal(productId, productName, sku, cost) {
            const select = document.getElementById('modal_product_id');
            select.value = productId;
            document.getElementById('modal_unit_cost').value = cost;
            document.getElementById('modal_product_subtitle').innerText = `الصنف المحدد: [${sku}] ${productName}`;
            
            const m = document.getElementById('stockMovementModal');
            if (m) {
                m.classList.add('open');
                m.style.display = 'flex';
                updateMovementFields();
            }
        }

        function updateMovementFields() {
            const type = document.querySelector('input[name="movement_type"]:checked').value;
            const singleWh = document.getElementById('single_warehouse_box');
            const transferWh = document.getElementById('transfer_warehouses_box');
            const costBox = document.getElementById('cost_box');
            const qtyLabel = document.getElementById('qty_label');

            ['in', 'out', 'transfer', 'adjustment'].forEach(t => {
                const el = document.getElementById(`type_label_${t}`);
                if (t === type) {
                    el.style.background = 'var(--primary, #0d9488)';
                    el.style.color = '#fff';
                    el.style.borderColor = 'var(--primary, #0d9488)';
                } else {
                    el.style.background = 'transparent';
                    el.style.color = 'var(--text-main)';
                    el.style.borderColor = 'var(--border-light)';
                }
            });

            if (type === 'transfer') {
                singleWh.style.display = 'none';
                transferWh.style.display = 'grid';
                costBox.style.display = 'none';
                qtyLabel.innerText = 'الكمية المحولة *';
            } else if (type === 'adjustment') {
                singleWh.style.display = 'block';
                transferWh.style.display = 'none';
                costBox.style.display = 'none';
                qtyLabel.innerText = 'الرصيد الفعلي بعد الجرد *';
            } else if (type === 'out') {
                singleWh.style.display = 'block';
                transferWh.style.display = 'none';
                costBox.style.display = 'none';
                qtyLabel.innerText = 'الكمية المنصرفة (FIFO) *';
            } else {
                singleWh.style.display = 'block';
                transferWh.style.display = 'none';
                costBox.style.display = 'block';
                qtyLabel.innerText = 'الكمية المضافة *';
            }
        }

        document.getElementById('stockMovementModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockMovementModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeStockMovementModal();
            }
        });
    </script>
@endsection
