@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="المنتجات والأصناف" :breadcrumbs="['المخزون والمنتجات' => route('products.index')]">
        <x-slot:actions>
            <a href="{{ route('products.create') }}" class="erp-btn erp-btn-primary">
                <span>➕</span>
                <span>إضافة منتج جديد</span>
            </a>
            <button type="button" id="topBatchPrintBtn" onclick="directPrintBatch(event)" class="erp-btn erp-btn-secondary" style="display: none; color: var(--primary);" title="طباعة فورية للأصناف المحددة بدون نوافذ">
                <span>🖨️</span>
                <span>طباعة ملصقات (<span id="topBatchCount" class="font-numeric">0</span>)</span>
            </button>
            <a href="{{ route('products.import.form') }}" class="erp-btn erp-btn-secondary">
                <span>📥</span>
                <span>استيراد من إكسيل</span>
            </a>
            <a href="{{ route('categories.index') }}" class="erp-btn erp-btn-secondary">
                <span>🏷️</span>
                <span>إدارة التصنيفات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric" style="padding: 4px 10px; font-size: 12px;">
                <span>إجمالي الأصناف:</span>
                <strong>{{ $products->total() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Flash Messages -->
    @if(session('status') === 'success')
        <div class="erp-alert erp-alert-success" role="alert" style="margin-bottom: 16px;">
            <span style="font-size: 16px;">✓</span>
            <div>{{ session('message', 'تمت العملية بنجاح.') }}</div>
        </div>
    @endif

    @if(session('status') === 'error')
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 16px;">
            <span style="font-size: 16px;">⚠️</span>
            <div>{{ session('message', 'حدث خطأ أثناء تنفيذ العملية.') }}</div>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="erp-card" style="margin-bottom: 16px; padding: 12px 16px;">
        <form action="{{ route('products.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Search Input -->
            <div style="flex: 1; min-width: 220px; position: relative;">
                <input 
                    type="search" 
                    name="search" 
                    value="{{ $currentSearch }}" 
                    class="erp-input font-numeric" 
                    placeholder="بحث باسم المنتج، كود SKU، أو الباركود..."
                    style="width: 100%; padding-right: 36px;"
                >
                <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); opacity: 0.6;">🔍</span>
            </div>

            <!-- Category Filter -->
            <div style="min-width: 180px;">
                <select name="category_id" class="erp-input" style="width: 100%;">
                    <option value="">-- كل التصنيفات --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string)$currentCategoryId === (string)$category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div style="min-width: 130px;">
                <select name="status" class="erp-input" style="width: 100%;">
                    <option value="">-- كل الحالات --</option>
                    <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>نشط فقط</option>
                    <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>معطل فقط</option>
                </select>
            </div>

            <!-- Actions -->
            <button type="submit" class="erp-btn erp-btn-primary" style="padding: 7px 16px;">
                <span>تصفية</span>
            </button>

            @if($currentSearch || $currentCategoryId || $currentStatus)
                <a href="{{ route('products.index') }}" class="erp-btn erp-btn-secondary" style="padding: 7px 12px;" title="إلغاء التصفية">
                    <span>✕</span>
                    <span>إلغاء الفلترة</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Products Table Card -->
    <div class="erp-card" style="padding: 0; overflow: hidden;">
        @if($products->isEmpty())
            <div style="text-align: center; padding: 48px 20px;">
                <div style="font-size: 42px; margin-bottom: 12px; opacity: 0.5;">💍</div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: var(--text-main);">لم يتم العثور على أي منتجات</h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                    @if($currentSearch || $currentCategoryId || $currentStatus)
                        لا توجد أصناف تطابق معايير البحث الحالية. جرب تغيير كلمات البحث أو إلغاء الفلترة.
                    @else
                        ابدأ بتسجيل أول صنف في مخزون إكسسوارات كيان.
                    @endif
                </p>
                @if(!($currentSearch || $currentCategoryId || $currentStatus))
                    <a href="{{ route('products.create') }}" class="erp-btn erp-btn-primary">
                        <span>➕ إضافة أول منتج</span>
                    </a>
                @endif
            </div>
        @else
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" title="تحديد جميع الأصناف" onchange="toggleSelectAll(this)" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th style="width: 50px; text-align: center;">الصورة</th>
                            <th style="width: 130px;">كود الصنف (SKU)</th>
                            <th>اسم المنتج</th>
                            <th style="width: 140px;">التصنيف</th>
                            <th style="width: 150px;">الباركود</th>
                            <th style="width: 110px; text-align: left;">سعر الجملة</th>
                            <th style="width: 110px; text-align: left;">سعر التجزئة</th>
                            <th style="width: 80px; text-align: center;">الوحدة</th>
                            <th style="width: 80px; text-align: center;">الحالة</th>
                            <th style="width: 140px; text-align: center;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr id="product-row-{{ $product->id }}" class="product-row-clickable" data-url="{{ route('products.show', $product) }}" title="انقر نقراً مزدوجاً (Double Click) لفتح تفاصيل الصنف">
                                <!-- Checkbox -->
                                <td style="text-align: center;">
                                    <input type="checkbox" 
                                           class="product-select-cb" 
                                           value="{{ $product->id }}" 
                                           data-id="{{ $product->id }}" 
                                           data-name="{{ $product->name }}" 
                                           data-sku="{{ $product->sku }}" 
                                           data-barcode="{{ $product->barcode ?: $product->sku }}" 
                                           onchange="onRowSelectionChange()"
                                           style="cursor: pointer; width: 16px; height: 16px;">
                                </td>

                                <!-- Image Thumbnail -->
                                <td style="text-align: center;">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 36px; height: 36px; object-fit: cover; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); display: inline-block;">
                                    @else
                                        <div style="width: 36px; height: 36px; background-color: var(--bg-surface-alt); border-radius: var(--border-radius-sm); display: inline-flex; align-items: center; justify-content: center; font-size: 16px; opacity: 0.6; border: 1px dashed var(--border-color);">
                                            💍
                                        </div>
                                    @endif
                                </td>

                                <!-- SKU -->
                                <td>
                                    <span class="font-numeric" style="font-weight: 700; color: var(--primary);">
                                        {{ $product->sku }}
                                    </span>
                                </td>

                                <!-- Name -->
                                <td>
                                    <a href="{{ route('products.show', $product) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                                        {{ $product->name }}
                                    </a>
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="erp-badge erp-badge-neutral">
                                        {{ $product->category?->name ?? 'غير محدد' }}
                                    </span>
                                </td>

                                <!-- Barcode -->
                                <td>
                                    @if($product->barcode)
                                        <span class="font-numeric" style="font-family: monospace; background: var(--bg-surface-alt); padding: 2px 6px; border-radius: 4px; font-size: 12px; border: 1px solid var(--border-color);">
                                            {{ $product->barcode }}
                                        </span>
                                    @else
                                        <span style="color: var(--text-light); font-size: 11px;">بدون باركود</span>
                                    @endif
                                </td>

                                <!-- Wholesale Price -->
                                <td style="text-align: left;" class="font-numeric">
                                    <strong style="color: var(--success-text);">{{ number_format((float)$product->wholesale_price, 2) }}</strong>
                                    <small style="color: var(--text-muted); font-size: 10px;">ج.م</small>
                                </td>

                                <!-- Retail Price -->
                                <td style="text-align: left;" class="font-numeric">
                                    <span>{{ number_format((float)$product->retail_price, 2) }}</span>
                                    <small style="color: var(--text-muted); font-size: 10px;">ج.م</small>
                                </td>

                                <!-- Unit -->
                                <td style="text-align: center;">
                                    <span style="font-size: 12px; color: var(--text-muted);">{{ $product->unit }}</span>
                                </td>

                                <!-- Status -->
                                <td style="text-align: center;">
                                    @if($product->is_active)
                                        <span class="erp-badge erp-badge-success" style="font-size: 11px;">نشط</span>
                                    @else
                                        <span class="erp-badge erp-badge-danger" style="font-size: 11px;">معطل</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td style="text-align: center;">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" class="erp-btn erp-btn-ghost erp-btn-icon" title="طباعة فورية للملصق (38×15 مم) بدون نوافذ" onclick="directPrintProduct({{ $product->id }}, event)">
                                            <span>🖨️</span>
                                        </button>
                                        <a href="{{ route('products.show', $product) }}" class="erp-btn erp-btn-ghost erp-btn-icon" title="عرض تفاصيل الصنف">
                                            <span>👁️</span>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="erp-btn erp-btn-ghost erp-btn-icon" title="تعديل">
                                            <span>✏️</span>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف المنتج: {{ $product->name }}؟');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-btn erp-btn-ghost erp-btn-icon" style="color: var(--danger);" title="حذف">
                                                <span>🗑️</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div style="padding: 12px 16px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: var(--text-muted);" class="font-numeric">
                        عرض {{ $products->firstItem() }} إلى {{ $products->lastItem() }} من إجمالي {{ $products->total() }} صنف
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Floating Batch Actions Bar -->
    <div id="batchActionBar" style="display: none; position: sticky; bottom: 16px; z-index: 40; background: var(--bg-surface); border: 2px solid var(--primary); border-radius: var(--border-radius-md); box-shadow: 0 10px 25px rgba(0,0,0,0.15); padding: 12px 20px; margin-top: 16px; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span class="erp-badge erp-badge-primary" style="font-size: 13px; padding: 6px 12px;">
                <span>تم تحديد:</span>
                <strong id="selectedProductsCount" class="font-numeric" style="margin: 0 4px;">0</strong>
                <span>صنف</span>
            </span>
            <span style="font-size: 13px; color: var(--text-muted);">
                جاهز لطباعة ملصقات الباركود على طابعة <strong>Xprinter XP-233B</strong> (38×15 مم)
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" onclick="directPrintBatch(event)" class="erp-btn erp-btn-primary" title="طباعة فورية للملصقات مباشرة بدون نوافذ">
                <span>🖨️</span>
                <span>طباعة فورية للمحددة</span>
            </button>
            <button type="button" onclick="openBatchPrintModal()" class="erp-btn erp-btn-secondary" title="تخصيص عدد النسخ لكل صنف">
                <span>⚙️</span>
                <span>تخصيص الكميات</span>
            </button>
            <button type="button" onclick="clearAllSelections()" class="erp-btn erp-btn-secondary">
                <span>✕ إلغاء التحديد</span>
            </button>
        </div>
    </div>

    <!-- Batch Barcode Print Modal -->
    <div id="batchPrintModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); align-items: center; justify-content: center; padding: 16px;">
        <div class="erp-card" style="width: 100%; max-width: 650px; max-height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <!-- Modal Header -->
            <div class="erp-card-header" style="background: var(--bg-surface-alt); border-bottom: 1px solid var(--border-color); padding: 12px 18px; flex-shrink: 0;">
                <div class="erp-card-title" style="font-size: 15px;">
                    <span>🖨️</span>
                    <span>طباعة ملصقات الباركود للمنتجات المحددة (38×15 مم)</span>
                </div>
                <button type="button" onclick="closeBatchPrintModal()" class="erp-btn erp-btn-ghost erp-btn-icon" style="color: var(--text-muted);" title="إغلاق">
                    <span>✕</span>
                </button>
            </div>

            <!-- Modal Sub-header: Global Quantity Controls -->
            <div style="background: var(--bg-surface); padding: 12px 18px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; flex-shrink: 0;">
                <div style="font-size: 12.5px; font-weight: 600; color: var(--text-main);">
                    تعيين كمية موحدة للجميع:
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="button" onclick="setAllBatchQty(1)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 10px; font-size: 12px;">1 ملصق</button>
                    <button type="button" onclick="setAllBatchQty(2)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 10px; font-size: 12px;">2 ملصق</button>
                    <button type="button" onclick="setAllBatchQty(5)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 10px; font-size: 12px;">5 ملصقات</button>
                    <button type="button" onclick="setAllBatchQty(10)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 10px; font-size: 12px;">10 ملصقات</button>
                    <button type="button" onclick="setAllBatchQty(20)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 10px; font-size: 12px;">20 ملصق</button>
                </div>
            </div>

            <!-- Modal Body: Scrollable Selected Items Table -->
            <div style="padding: 0; overflow-y: auto; flex: 1;">
                <form id="batchPrintForm" action="{{ route('products.print-batch') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="autoprint" id="batchFormAutoprint" value="0">
                    <div id="batchFormHiddenInputs"></div>

                    <table class="erp-table" style="margin: 0;">
                        <thead>
                            <tr style="background: var(--bg-surface-alt); position: sticky; top: 0; z-index: 10;">
                                <th style="width: 50px; text-align: center;">#</th>
                                <th>اسم المنتج</th>
                                <th style="width: 140px;">كود الصنف (SKU)</th>
                                <th style="width: 160px; text-align: center;">عدد الملصقات</th>
                            </tr>
                        </thead>
                        <tbody id="batchModalTableBody">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </form>
            </div>

            <!-- Modal Footer: Summary & Actions -->
            <div class="erp-card-footer" style="background: var(--bg-surface-alt); border-top: 1px solid var(--border-color); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; flex-shrink: 0;">
                <div style="font-size: 13px;">
                    <span>إجمالي الأصناف: <strong id="batchSummaryProductsCount" class="font-numeric">0</strong></span>
                    <span style="margin: 0 8px; color: var(--border-color);">|</span>
                    <span>إجمالي الملصقات: <strong id="batchSummaryTotalLabels" class="font-numeric" style="color: var(--primary);">0</strong> ملصق</span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeBatchPrintModal()" class="erp-btn erp-btn-secondary">
                        <span>إلغاء</span>
                    </button>
                    <button type="button" onclick="submitBatchPrint(false)" class="erp-btn erp-btn-secondary" title="فتح المعاينة لجميع الملصقات">
                        <span>🔍 معاينة الطباعة</span>
                    </button>
                    <button type="button" onclick="submitBatchPrint(true)" class="erp-btn erp-btn-primary">
                        <span>🖨️ طباعة الكل الآن</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Batch Printing and Selection -->
    <script>
        function getSelectedCheckboxes() {
            return Array.from(document.querySelectorAll('.product-select-cb:checked'));
        }

        function onRowSelectionChange() {
            var selected = getSelectedCheckboxes();
            var count = selected.length;
            var bar = document.getElementById('batchActionBar');
            var topBtn = document.getElementById('topBatchPrintBtn');
            var countEl = document.getElementById('selectedProductsCount');
            var topCountEl = document.getElementById('topBatchCount');
            var selectAllCb = document.getElementById('selectAllCheckbox');

            if (countEl) countEl.innerText = count;
            if (topCountEl) topCountEl.innerText = count;

            if (count > 0) {
                if (bar) bar.style.display = 'flex';
                if (topBtn) topBtn.style.display = 'inline-flex';
            } else {
                if (bar) bar.style.display = 'none';
                if (topBtn) topBtn.style.display = 'none';
            }

            var allBoxes = document.querySelectorAll('.product-select-cb');
            if (selectAllCb && allBoxes.length > 0) {
                selectAllCb.checked = (selected.length === allBoxes.length);
                selectAllCb.indeterminate = (selected.length > 0 && selected.length < allBoxes.length);
            }
        }

        function toggleSelectAll(masterCb) {
            var checkboxes = document.querySelectorAll('.product-select-cb');
            checkboxes.forEach(function(cb) {
                cb.checked = masterCb.checked;
            });
            onRowSelectionChange();
        }

        function clearAllSelections() {
            var checkboxes = document.querySelectorAll('.product-select-cb');
            checkboxes.forEach(function(cb) {
                cb.checked = false;
            });
            var masterCb = document.getElementById('selectAllCheckbox');
            if (masterCb) {
                masterCb.checked = false;
                masterCb.indeterminate = false;
            }
            onRowSelectionChange();
        }

        function openBatchPrintModal() {
            var selected = getSelectedCheckboxes();
            if (selected.length === 0) {
                alert('يرجى تحديد صنف واحد على الأقل للطباعة.');
                return;
            }

            var tbody = document.getElementById('batchModalTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            selected.forEach(function(cb, index) {
                var id = cb.dataset.id;
                var name = cb.dataset.name;
                var sku = cb.dataset.sku;

                var tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="text-align: center; color: var(--text-muted); font-size: 12px;">${index + 1}</td>
                    <td>
                        <strong style="color: var(--text-main); font-size: 13px;">${name}</strong>
                    </td>
                    <td>
                        <span class="font-numeric" style="font-weight: 700; color: var(--primary); font-size: 12px;">${sku}</span>
                    </td>
                    <td style="text-align: center;">
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <button type="button" onclick="adjustBatchItemQty(${id}, -1)" class="erp-btn erp-btn-secondary font-numeric" style="padding: 2px 8px; font-weight: bold; font-size: 14px;">−</button>
                            <input type="number" id="batch_qty_${id}" class="erp-input font-numeric batch-qty-input" data-id="${id}" value="1" min="1" max="500" onchange="recalculateBatchTotal()" style="width: 60px; text-align: center; padding: 4px; font-weight: bold;">
                            <button type="button" onclick="adjustBatchItemQty(${id}, 1)" class="erp-btn erp-btn-secondary font-numeric" style="padding: 2px 8px; font-weight: bold; font-size: 14px;">+</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            recalculateBatchTotal();

            var modal = document.getElementById('batchPrintModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeBatchPrintModal() {
            var modal = document.getElementById('batchPrintModal');
            if (modal) modal.style.display = 'none';
        }

        function adjustBatchItemQty(id, delta) {
            var input = document.getElementById('batch_qty_' + id);
            if (input) {
                var current = parseInt(input.value, 10) || 1;
                input.value = Math.max(1, Math.min(500, current + delta));
                recalculateBatchTotal();
            }
        }

        function setAllBatchQty(val) {
            var inputs = document.querySelectorAll('.batch-qty-input');
            inputs.forEach(function(inp) {
                inp.value = val;
            });
            recalculateBatchTotal();
        }

        function recalculateBatchTotal() {
            var inputs = document.querySelectorAll('.batch-qty-input');
            var totalLabels = 0;
            inputs.forEach(function(inp) {
                totalLabels += (parseInt(inp.value, 10) || 1);
            });

            var countEl = document.getElementById('batchSummaryProductsCount');
            var totalEl = document.getElementById('batchSummaryTotalLabels');
            if (countEl) countEl.innerText = inputs.length;
            if (totalEl) totalEl.innerText = totalLabels;
        }

        function submitBatchPrint(autoPrint) {
            var form = document.getElementById('batchPrintForm');
            var container = document.getElementById('batchFormHiddenInputs');
            var autoprintInput = document.getElementById('batchFormAutoprint');
            if (!form || !container) return;

            autoprintInput.value = autoPrint ? '1' : '0';
            container.innerHTML = '';

            var inputs = document.querySelectorAll('.batch-qty-input');
            if (inputs.length === 0) {
                alert('لا توجد أصناف محددة.');
                return;
            }

            inputs.forEach(function(inp) {
                var id = inp.dataset.id;
                var qty = parseInt(inp.value, 10) || 1;

                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `products[${id}]`;
                hidden.value = qty;
                container.appendChild(hidden);
            });

            form.submit();
            closeBatchPrintModal();
        }

        // =========================================================
        // DIRECT PRINTING (NO EXTRA WINDOWS OR MODALS)
        // =========================================================
        function showPrintToast(msg) {
            var toast = document.getElementById('printNotificationToast');
            var msgEl = document.getElementById('printToastMessage');
            if (toast && msgEl) {
                msgEl.innerText = msg;
                toast.style.display = 'flex';
                clearTimeout(window.__toastTimeout);
                window.__toastTimeout = setTimeout(function() {
                    toast.style.display = 'none';
                }, 2800);
            }
        }

        function getOrCreatePrintIframe() {
            var iframe = document.getElementById('directPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'directPrintIframe';
                iframe.name = 'directPrintIframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.opacity = '0';
                iframe.style.pointerEvents = 'none';
                document.body.appendChild(iframe);
            }
            return iframe;
        }

        function directPrintProduct(productId, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            showPrintToast('جاري إرسال الملصق مباشرة إلى طابعة الباركود...');
            var iframe = getOrCreatePrintIframe();
            iframe.src = '{{ url("/products") }}/' + productId + '/print-label?quantity=1&autoprint=1';
        }

        function directPrintBatch(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            var selected = getSelectedCheckboxes();
            if (selected.length === 0) {
                alert('يرجى تحديد صنف واحد على الأقل للطباعة.');
                return;
            }

            showPrintToast('جاري إرسال ' + selected.length + ' ملصق مباشرة إلى طابعة الباركود...');
            var iframe = getOrCreatePrintIframe();

            var form = document.getElementById('directBatchHiddenForm');
            if (!form) {
                form = document.createElement('form');
                form.id = 'directBatchHiddenForm';
                form.method = 'POST';
                form.action = '{{ route("products.print-batch") }}';
                form.target = 'directPrintIframe';
                form.style.display = 'none';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                var autoprint = document.createElement('input');
                autoprint.type = 'hidden';
                autoprint.name = 'autoprint';
                autoprint.value = '1';
                form.appendChild(autoprint);

                document.body.appendChild(form);
            }

            // Remove previous items
            form.querySelectorAll('.batch-item-hidden').forEach(function(el) { el.remove(); });

            // Append current selected IDs
            selected.forEach(function(cb) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.className = 'batch-item-hidden';
                hidden.name = 'products[' + cb.value + ']';
                hidden.value = '1';
                form.appendChild(hidden);
            });

            form.submit();
        }

        // =========================================================
        // ROW DOUBLE-CLICK TO OPEN PRODUCT DETAILS
        // =========================================================
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.product-row-clickable').forEach(function(row) {
                row.addEventListener('dblclick', function(e) {
                    // Ignore clicks on checkboxes, links, or buttons
                    if (e.target.closest('input, button, a, form, label')) {
                        return;
                    }
                    var url = row.getAttribute('data-url');
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBatchPrintModal();
            }
        });
    </script>

    <!-- Hidden Iframe for Direct Silent Printing -->
    <iframe id="directPrintIframe" name="directPrintIframe" style="display: none; position: fixed; width: 0; height: 0; border: 0; opacity: 0; pointer-events: none;"></iframe>

    <!-- Toast Notification for Direct Print Feedback -->
    <div id="printNotificationToast" style="display: none; position: fixed; bottom: 24px; left: 24px; z-index: 99999; background: #0f172a; color: #ffffff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); font-size: 13.5px; font-weight: 600; align-items: center; gap: 10px;">
        <span style="font-size: 18px;">🖨️</span>
        <span id="printToastMessage">جاري إرسال الملصق للطباعة...</span>
    </div>

    <!-- Inline Style for Clickable Rows -->
    <style>
        .product-row-clickable {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.12s ease;
        }
        .product-row-clickable:hover {
            background-color: var(--bg-surface-alt) !important;
        }
    </style>
@endsection

