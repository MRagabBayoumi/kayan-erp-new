@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="إضافة منتج جديد" :breadcrumbs="['المنتجات والأصناف' => route('products.index'), 'إضافة منتج' => '#']">
        <x-slot:actions>
            <button type="submit" form="productForm" class="erp-btn erp-btn-primary">
                <x-icon name="save" size="16" />
                <span>حفظ المنتج</span>
            </button>
            <a href="{{ route('products.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="x" size="16" />
                <span>إلغاء وعودة</span>
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
                <strong>يرجى تصحيح الأخطاء التالية قبل حفظ المنتج:</strong>
                <ul style="margin: 6px 20px 0; font-size: 12px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="productForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                            value="{{ old('name') }}" 
                            class="erp-input @error('name') erp-input-error @enderror" 
                            placeholder="مثال: توكة شعر لؤلؤ أبيض كوري فاخر" 
                            required
                        >
                    </div>

                    <!-- SKU & Category Row -->
                    <div class="erp-form-row-2col">
                        <div class="erp-form-group">
                            <label for="productSku" class="erp-form-label">
                                كود الصنف (SKU) <span style="color: var(--danger);">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="productSku" 
                                name="sku" 
                                value="{{ old('sku', $suggestedSku) }}" 
                                class="erp-input font-numeric @error('sku') erp-input-error @enderror" 
                                required
                            >
                            <small style="font-size: 11px; color: var(--text-muted);">كود فريد لتمييز الموديل</small>
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
                                <option value="">-- اختر التصنيف --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string)old('category_id') === (string)$cat->id ? 'selected' : '' }}>
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
                            <option value="قطعة" {{ old('unit', 'قطعة') === 'قطعة' ? 'selected' : '' }}>قطعة (Piece)</option>
                            <option value="دستة" {{ old('unit') === 'دستة' ? 'selected' : '' }}>دستة (Dozen - 12 قطعة)</option>
                            <option value="كرتونة" {{ old('unit') === 'كرتونة' ? 'selected' : '' }}>كرتونة (Carton)</option>
                            <option value="طقم" {{ old('unit') === 'طقم' ? 'selected' : '' }}>طقم (Set)</option>
                            <option value="باكت" {{ old('unit') === 'باكت' ? 'selected' : '' }}>باكت (Pack)</option>
                        </select>
                    </div>

                    <!-- Barcode Section with Camera Scanner Integration -->
                    <div class="erp-form-group">
                        <div class="erp-form-label flex items-center justify-between" style="flex-wrap: wrap; gap: 6px;">
                            <label for="productBarcode" style="margin: 0; cursor: pointer;">الباركود (Barcode)</label>
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <button type="button" onclick="openKayanBarcodeScanner('productBarcode')" class="erp-btn erp-btn-secondary" style="font-size: 11px; padding: 3px 8px; color: var(--primary); display: inline-flex; align-items: center; gap: 4px;" title="تشغيل الكاميرا لمسح باركود الصنف مباشرة">
                                    <x-icon name="camera" size="14" />
                                    <span>مسح بالكاميرا</span>
                                </button>
                                <button type="button" id="btnGenBarcode" class="erp-btn erp-btn-ghost font-numeric" style="font-size: 11px; padding: 2px 8px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                    <x-icon name="zap" size="13" />
                                    <span>توليد كود</span>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input 
                                type="text" 
                                id="productBarcode" 
                                name="barcode" 
                                value="{{ old('barcode') }}" 
                                class="erp-input font-numeric @error('barcode') erp-input-error @enderror" 
                                placeholder="امسح بالكاميرا أو اكتب الباركود..."
                                style="padding-left: 42px;"
                            >
                            <button 
                                type="button" 
                                onclick="openKayanBarcodeScanner('productBarcode')" 
                                title="تشغيل الكاميرا لمسح الباركود"
                                style="position: absolute; left: 5px; height: 30px; width: 32px; border: none; background: var(--primary-soft); color: var(--primary); border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;">
                                <x-icon name="camera" size="16" />
                            </button>
                        </div>
                        <small style="font-size: 11px; color: var(--text-muted);">
                            يمكنك توجيه كاميرا الهاتف لمسح باركود الصنف مباشرة، أو توليد كود داخلي خاص بكيان.
                        </small>
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
                                value="{{ old('cost_price', '0.00') }}" 
                                class="erp-input font-numeric @error('cost_price') erp-input-error @enderror" 
                                required
                            >
                            <small style="font-size: 10px; color: var(--text-muted);">تكلفة الصين والشحن</small>
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
                                value="{{ old('wholesale_price', '0.00') }}" 
                                class="erp-input font-numeric @error('wholesale_price') erp-input-error @enderror" 
                                required
                            >
                            <small style="font-size: 10px; color: var(--text-muted);">سعر بيع التجار</small>
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
                                value="{{ old('retail_price', '0.00') }}" 
                                class="erp-input font-numeric @error('retail_price') erp-input-error @enderror" 
                                required
                            >
                            <small style="font-size: 10px; color: var(--text-muted);">المقترح للقطاعي</small>
                        </div>
                    </div>

                    <!-- Min Stock Alert -->
                    <div class="erp-form-group">
                        <label for="minStockAlert" class="erp-form-label">
                            حد الطلب الأدنى (تنبيه النواقص) <span style="color: var(--danger);">*</span>
                        </label>
                        <input 
                            type="number" 
                            min="0" 
                            id="minStockAlert" 
                            name="min_stock_alert" 
                            value="{{ old('min_stock_alert', 5) }}" 
                            class="erp-input font-numeric @error('min_stock_alert') erp-input-error @enderror" 
                            required
                        >
                        <small style="font-size: 11px; color: var(--text-muted);">
                            يتم إظهار تنبيه عندما يقل الرصيد المخزني عن هذا الحد لتجهيز طلبية استيراد جديدة من الصين.
                        </small>
                    </div>

                    <!-- Product Image -->
                    <div class="erp-form-group">
                        <label for="productImage" class="erp-form-label">صورة المنتج</label>
                        <input 
                            type="file" 
                            id="productImage" 
                            name="product_image" 
                            accept="image/png,image/jpeg,image/webp" 
                            class="erp-input @error('product_image') erp-input-error @enderror"
                        >
                        <div id="imagePreviewContainer" style="margin-top: 10px; display: none;">
                            <img id="imagePreview" src="" alt="معاينة الصورة" style="max-height: 90px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); object-fit: contain;">
                        </div>
                    </div>

                    <!-- Active Status Toggle -->
                    <div style="margin-top: 8px;">
                        <label class="flex items-center gap-2" style="cursor: pointer;">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', '1') ? 'checked' : '' }} 
                                style="width: 18px; height: 18px; accent-color: var(--primary);"
                            >
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-main);">تفعيل الصنف وإتاحته في النظام وقوائم المبيعات</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <x-barcode-scanner-modal target-input-id="productBarcode" />
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Barcode Generation Handler
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
                    // Fallback client generation
                    const random = Math.floor(1000000000 + Math.random() * 9000000000);
                    barcodeInput.value = '20' + random;
                } finally {
                    genBtn.disabled = false;
                    genBtn.innerHTML = originalHtml;
                }
            });
        }

        // Live Image Preview
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
</script>
@endpush
