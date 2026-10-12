@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="تعديل المنتج: {{ $product->name }}" :breadcrumbs="['المنتجات والأصناف' => route('products.index'), $product->name => route('products.show', $product), 'تعديل' => '#']">
        <x-slot:actions>
            <button type="submit" form="productEditForm" class="erp-btn erp-btn-primary">
                <x-icon name="save" size="16" />
                <span>حفظ التعديلات</span>
            </button>
            <button type="button" onclick="directPrintFromEdit()" class="erp-btn erp-btn-secondary" title="طباعة فورية للملصق (38×25 مم) بدون نوافذ">
                <x-icon name="printer" size="16" />
                <span>طباعة الباركود</span>
            </button>
            <a href="{{ route('products.show', $product) }}" class="erp-btn erp-btn-secondary">
                <x-icon name="x" size="16" />
                <span>إلغاء</span>
            </a>
        </x-slot:actions>
    </x-control-panel>
@endsection

@section('content')
    <!-- Validation Errors Alert -->
    @if($errors->any())
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 20px;">
            <x-icon name="alert-triangle" size="16" />
            <div>
                <strong>يرجى تصحيح الأخطاء التالية قبل حفظ التعديلات:</strong>
                <ul style="margin: 6px 20px 0; font-size: 12px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="productEditForm" action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="erp-form-card-grid">
            <!-- Panel 1: Main Product Details -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="package" size="18" />
                        <span>بيانات الصنف الأساسية</span>
                    </div>
                </div>
                <div class="erp-card-body" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Product Name -->
                    <div class="erp-form-group">
                        <label for="productName" class="erp-form-label">
                            اسم المنتج <span style="color: var(--danger);">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="productName" 
                            name="name" 
                            value="{{ old('name', $product->name) }}" 
                            class="erp-input @error('name') erp-input-error @enderror" 
                            required
                        >
                    </div>

                    <!-- SKU & Category Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="erp-form-group">
                            <label for="productSku" class="erp-form-label">
                                كود الصنف (SKU) <span style="color: var(--danger);">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="productSku" 
                                name="sku" 
                                value="{{ old('sku', $product->sku) }}" 
                                class="erp-input font-numeric @error('sku') erp-input-error @enderror" 
                                required
                            >
                        </div>

                        <div class="erp-form-group">
                            <label for="productCategory" class="erp-form-label">
                                التصنيف <span style="color: var(--danger);">*</span>
                            </label>
                            <select 
                                id="productCategory" 
                                name="category_id" 
                                class="erp-input @error('category_id') erp-input-error @enderror" 
                                required
                            >
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string)old('category_id', $product->category_id) === (string)$cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Unit of Measure -->
                    <div class="erp-form-group">
                        <label for="productUnit" class="erp-form-label">
                            وحدة القياس <span style="color: var(--danger);">*</span>
                        </label>
                        <select id="productUnit" name="unit" class="erp-input">
                            <option value="قطعة" {{ old('unit', $product->unit) === 'قطعة' ? 'selected' : '' }}>قطعة (Piece)</option>
                            <option value="دستة" {{ old('unit', $product->unit) === 'دستة' ? 'selected' : '' }}>دستة (Dozen - 12 قطعة)</option>
                            <option value="كرتونة" {{ old('unit', $product->unit) === 'كرتونة' ? 'selected' : '' }}>كرتونة (Carton)</option>
                            <option value="طقم" {{ old('unit', $product->unit) === 'طقم' ? 'selected' : '' }}>طقم (Set)</option>
                            <option value="باكت" {{ old('unit', $product->unit) === 'باكت' ? 'selected' : '' }}>باكت (Pack)</option>
                        </select>
                    </div>

                    <!-- Barcode Section -->
                    <div class="erp-form-group">
                        <label for="productBarcode" class="erp-form-label flex items-center justify-between">
                            <span>الباركود (Barcode)</span>
                            <button type="button" id="btnGenBarcode" class="erp-btn erp-btn-ghost font-numeric" style="font-size: 11px; padding: 2px 8px; color: var(--primary); display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="zap" size="13" />
                                <span>توليد كود جديد</span>
                            </button>
                        </label>
                        <input 
                            type="text" 
                            id="productBarcode" 
                            name="barcode" 
                            value="{{ old('barcode', $product->barcode) }}" 
                            class="erp-input font-numeric @error('barcode') erp-input-error @enderror" 
                            placeholder="اتركه فارغاً إذا لم يتوفر باركود"
                        >
                    </div>
                </div>
            </div>

            <!-- Panel 2: Pricing & Inventory Control -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="banknote" size="18" />
                        <span>التسعير وإعدادات المخزون</span>
                    </div>
                </div>
                <div class="erp-card-body" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Prices 3-Column Grid -->
                    <div class="erp-pricing-grid">
                        <div class="erp-form-group">
                            <label for="costPrice" class="erp-form-label">
                                سعر التكلفة <span style="color: var(--danger);">*</span>
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                id="costPrice" 
                                name="cost_price" 
                                value="{{ old('cost_price', $product->cost_price) }}" 
                                class="erp-input font-numeric @error('cost_price') erp-input-error @enderror" 
                                required
                            >
                        </div>

                        <div class="erp-form-group">
                            <label for="wholesalePrice" class="erp-form-label">
                                سعر الجملة <span style="color: var(--danger);">*</span>
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                id="wholesalePrice" 
                                name="wholesale_price" 
                                value="{{ old('wholesale_price', $product->wholesale_price) }}" 
                                class="erp-input font-numeric @error('wholesale_price') erp-input-error @enderror" 
                                required
                            >
                        </div>

                        <div class="erp-form-group">
                            <label for="retailPrice" class="erp-form-label">
                                سعر التجزئة <span style="color: var(--danger);">*</span>
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                id="retailPrice" 
                                name="retail_price" 
                                value="{{ old('retail_price', $product->retail_price) }}" 
                                class="erp-input font-numeric @error('retail_price') erp-input-error @enderror" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Min Stock Alert -->
                    <div class="erp-form-group">
                        <label for="minStockAlert" class="erp-form-label">
                            حد الطلب الأدنى للتنبيه <span style="color: var(--danger);">*</span>
                        </label>
                        <input 
                            type="number" 
                            min="0" 
                            id="minStockAlert" 
                            name="min_stock_alert" 
                            value="{{ old('min_stock_alert', $product->min_stock_alert) }}" 
                            class="erp-input font-numeric @error('min_stock_alert') erp-input-error @enderror" 
                            required
                        >
                    </div>

                    <!-- Current & New Image -->
                    <div class="erp-form-group">
                        <label for="productImage" class="erp-form-label">صورة المنتج</label>
                        @if($product->image_url)
                            <div style="margin-bottom: 8px;">
                                <small style="display: block; color: var(--text-muted); margin-bottom: 4px;">الصورة الحالية:</small>
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-height: 80px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                            </div>
                        @endif
                        <input 
                            type="file" 
                            id="productImage" 
                            name="product_image" 
                            accept="image/png,image/jpeg,image/webp" 
                            class="erp-input @error('product_image') erp-input-error @enderror"
                        >
                        <div id="imagePreviewContainer" style="margin-top: 10px; display: none;">
                            <small style="display: block; color: var(--text-muted); margin-bottom: 4px;">معاينة الصورة الجديدة:</small>
                            <img id="imagePreview" src="" alt="معاينة جديدة" style="max-height: 80px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); object-fit: contain;">
                        </div>
                    </div>

                    <!-- Active Status Toggle -->
                    <div style="margin-top: 8px;">
                        <label class="flex items-center gap-2" style="cursor: pointer;">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }} 
                                style="width: 18px; height: 18px; accent-color: var(--primary);"
                            >
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-main);">تفعيل الصنف وإتاحته في النظام</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const genBtn = document.getElementById('btnGenBarcode');
        const barcodeInput = document.getElementById('productBarcode');

        if (genBtn && barcodeInput) {
            const originalHtml = genBtn.innerHTML;
            genBtn.addEventListener('click', async () => {
                genBtn.disabled = true;
                genBtn.innerText = 'جاري التوليد...';

                try {
                    const response = await fetch('{{ route("products.generate-barcode") }}');
                    const data = await response.json();
                    if (data && data.barcode) {
                        barcodeInput.value = data.barcode;
                    }
                } catch (e) {
                    const random = Math.floor(1000000000 + Math.random() * 9000000000);
                    barcodeInput.value = '20' + random;
                } finally {
                    genBtn.disabled = false;
                    genBtn.innerHTML = originalHtml;
                }
            });
        }

        const imgInput = document.getElementById('productImage');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImg = document.getElementById('imagePreview');

        if (imgInput && previewImg) {
            imgInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (file) {
                    previewImg.src = URL.createObjectURL(file);
                    previewContainer.style.display = 'block';
                }
            });
        }
    });

    function directPrintFromEdit() {
        let iframe = document.getElementById('editPrintIframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'editPrintIframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = '{{ route("products.print-label", $product) }}?quantity=1&autoprint=1';
    }
</script>
<iframe id="editPrintIframe" style="display: none; position: fixed; width: 0; height: 0; border: 0; opacity: 0; pointer-events: none;"></iframe>
@endpush
