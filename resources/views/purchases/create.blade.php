@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="فاتورة شراء جديدة" :breadcrumbs="['المشتريات والموردين' => route('purchases.index'), 'فاتورة جديدة' => '#']">
        <x-slot:actions>
            <button type="submit" form="purchaseForm" name="action" value="draft" class="erp-btn erp-btn-secondary">
                <x-icon name="file-text" size="16" />
                <span>حفظ كمسودة</span>
            </button>
            <button type="submit" form="purchaseForm" name="action" value="post" class="erp-btn erp-btn-primary">
                <x-icon name="check-circle" size="16" />
                <span>حفظ وترحيل للمخازن مباشرة</span>
            </button>
            <a href="{{ route('purchases.index') }}" class="erp-btn erp-btn-ghost">
                <x-icon name="x" size="16" />
                <span>إلغاء وعودة</span>
            </a>
        </x-slot:actions>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="purchases-create">
        <!-- Validation Errors Alert -->
        @if($errors->any())
            <div class="erp-alert erp-alert-danger mb-5" role="alert">
                <x-icon name="alert-triangle" size="16" />
                <div>
                    <strong>يرجى تصحيح الأخطاء التالية قبل حفظ الفاتورة:</strong>
                    <ul class="my-1.5 mx-5 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="purchaseForm" action="{{ route('purchases.store') }}" method="POST" class="flex flex-col gap-5">
            @csrf

            <!-- Header Card: Invoice Meta -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="shopping-bag" size="18" />
                        <span>بيانات فاتورة الشراء الأساسية</span>
                    </div>
                    <span class="font-numeric font-bold text-xs text-[var(--primary)]">
                        رقم مقترح: {{ $suggestedNumber }}
                    </span>
                </div>
                <div class="erp-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Supplier -->
                        <div class="erp-form-group">
                            <label for="supplierSelect" class="erp-form-label">
                                المورد <span class="text-[var(--danger)]">*</span>
                            </label>
                            <select id="supplierSelect" name="supplier_id" class="erp-input @error('supplier_id') erp-input-error @enderror" required>
                                <option value="">-- اختر المورد --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ (string)old('supplier_id') === (string)$sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }} ({{ $sup->country ?? 'مورد' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Warehouse (Default Pre-selected) -->
                        <div class="erp-form-group">
                            <label for="warehouseSelect" class="erp-form-label">
                                مستودع الاستلام <span class="text-[var(--danger)]">*</span>
                            </label>
                            <select id="warehouseSelect" name="warehouse_id" class="erp-input @error('warehouse_id') erp-input-error @enderror" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)old('warehouse_id', $defaultWarehouse->id ?? '') === (string)$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} {{ $wh->is_default ? '(الافتراضي)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Invoice Date -->
                        <div class="erp-form-group">
                            <label for="invoiceDate" class="erp-form-label">
                                تاريخ الفاتورة <span class="text-[var(--danger)]">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="invoiceDate" 
                                name="invoice_date" 
                                value="{{ old('invoice_date', date('Y-m-d')) }}" 
                                class="erp-input font-numeric @error('invoice_date') erp-input-error @enderror" 
                                required
                            >
                        </div>

                        <!-- Shipping Cost -->
                        <div class="erp-form-group">
                            <label for="shippingCost" class="erp-form-label">
                                مصاريف الشحن / التخليص
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                id="shippingCost" 
                                name="shipping_cost" 
                                value="{{ old('shipping_cost', '0.00') }}" 
                                class="erp-input font-numeric @error('shipping_cost') erp-input-error @enderror" 
                                placeholder="0.00"
                            >
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="erp-form-group mt-3">
                        <label for="purchaseNotes" class="erp-form-label">ملاحظات الفاتورة</label>
                        <input 
                            type="text" 
                            id="purchaseNotes" 
                            name="notes" 
                            value="{{ old('notes') }}" 
                            class="erp-input" 
                            placeholder="مثال: شحنة إكسسوارات كوانزو — كود الحاوية / بوليصة الشحن"
                        >
                    </div>
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="erp-card">
                <div class="erp-card-header flex items-center justify-between">
                    <div class="erp-card-title">
                        <x-icon name="list" size="18" />
                        <span>بنود وأصناف الفاتورة</span>
                    </div>
                    <button type="button" id="addPurchaseItemBtn" class="erp-btn erp-btn-secondary text-xs">
                        <x-icon name="plus" size="14" />
                        <span>إضافة صنف للفاتورة</span>
                    </button>
                </div>
                <div class="erp-card-body p-0">
                    <div class="erp-table-responsive">
                        <table class="erp-table" id="purchaseItemsTable">
                            <thead>
                                <tr>
                                    <th class="w-2/5">الصنف</th>
                                    <th class="w-28 text-center">الكمية</th>
                                    <th class="w-36 text-center">سعر التكلفة (ج.م)</th>
                                    <th class="w-36 text-center">الإجمالي (ج.م)</th>
                                    <th>ملاحظات البند</th>
                                    <th class="w-12 text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseItemsBody">
                                <!-- First default row -->
                                <tr class="purchase-item-row" data-row-index="0">
                                    <td>
                                        <select name="items[0][product_id]" class="erp-input purchase-product-select w-full" required>
                                            <option value="">-- اختر الصنف المطلوب --</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" data-unit="{{ $p->unit }}">
                                                    {{ $p->name }} ({{ $p->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="items[0][quantity]" value="1" class="erp-input font-numeric text-center purchase-qty-input w-full" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[0][unit_cost]" value="0.00" class="erp-input font-numeric text-center purchase-cost-input w-full" required>
                                    </td>
                                    <td class="font-numeric font-bold text-center text-xs text-[var(--text-main)] purchase-line-subtotal">
                                        0.00
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][notes]" class="erp-input text-xs w-full" placeholder="ملاحظات...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="erp-btn erp-btn-ghost erp-btn-icon text-[var(--danger)] remove-row-btn" title="حذف السطر">
                                            <x-icon name="trash" size="14" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div class="erp-card-footer p-4 bg-[var(--surface-muted)] flex flex-wrap items-center justify-between gap-4">
                    <div class="text-xs text-[var(--text-muted)]">
                        * سيتم احتساب تكلفة كل دفعة مخزنية بالجنيه المصري وتسجيلها بدفاتر الوارد أولاً يصرف أولاً (FIFO).
                    </div>
                    <div class="flex items-center gap-6 font-numeric">
                        <div class="text-xs">
                            <span class="text-[var(--text-muted)]">المجموع الفرعي:</span>
                            <span id="purchaseSubtotalText" class="font-bold text-sm text-[var(--text-main)] mr-1">0.00</span> ج.م
                        </div>
                        <div class="text-xs">
                            <span class="text-[var(--text-muted)]">الشحن:</span>
                            <span id="purchaseShippingText" class="font-bold text-sm text-[var(--text-main)] mr-1">0.00</span> ج.م
                        </div>
                        <div class="text-sm font-bold text-[var(--primary)] border-r border-[var(--border-color)] pr-4">
                            <span>إجمالي الفاتورة:</span>
                            <span id="purchaseGrandTotalText" class="text-base mr-1">0.00</span> ج.م
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Hidden Template for Dynamic Row Addition -->
    <template id="purchaseItemRowTemplate">
        <tr class="purchase-item-row" data-row-index="__INDEX__">
            <td>
                <select name="items[__INDEX__][product_id]" class="erp-input purchase-product-select w-full" required>
                    <option value="">-- اختر الصنف المطلوب --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" data-unit="{{ $p->unit }}">
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" class="erp-input font-numeric text-center purchase-qty-input w-full" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[__INDEX__][unit_cost]" value="0.00" class="erp-input font-numeric text-center purchase-cost-input w-full" required>
            </td>
            <td class="font-numeric font-bold text-center text-xs text-[var(--text-main)] purchase-line-subtotal">
                0.00
            </td>
            <td>
                <input type="text" name="items[__INDEX__][notes]" class="erp-input text-xs w-full" placeholder="ملاحظات...">
            </td>
            <td class="text-center">
                <button type="button" class="erp-btn erp-btn-ghost erp-btn-icon text-[var(--danger)] remove-row-btn" title="حذف السطر">
                    <x-icon name="trash" size="14" />
                </button>
            </td>
        </tr>
    </template>
@endsection
