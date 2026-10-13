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
        <div class="erp-alert erp-alert-danger mb-5" role="alert">
            <x-icon name="alert-triangle" size="16" />
            <div>
                <strong>يرجى تصحيح الأخطاء التالية قبل حفظ المنتج:</strong>
                <ul class="my-1.5 mx-5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="productForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" data-product-form>
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
                <div class="erp-card-body flex flex-col gap-4">
                    <!-- Product Name -->
                    <div class="erp-form-group">
                        <label for="productName" class="erp-form-label">
                            اسم المنتج <span class="text-[var(--danger)]">*</span>
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
                                كود الصنف (SKU) <span class="text-[var(--danger)]">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="productSku" 
                                name="sku" 
                                value="{{ old('sku', $suggestedSku) }}" 
                                class="erp-input font-numeric @error('sku') erp-input-error @enderror" 
                                required
                            >
                            <small class="text-[11px] text-[var(--text-muted)]">كود فريد لتمييز الموديل</small>
                        </div>

                        <div class="erp-form-group">
                            <label for="productCategory" class="erp-form-label">
                                التصنيف <span class="text-[var(--danger)]">*</span>
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
                            وحدة القياس <span class="text-[var(--danger)]">*</span>
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
                        <div class="erp-form-label flex items-center justify-between flex-wrap gap-1.5">
                            <label for="productBarcode" class="m-0 cursor-pointer">الباركود (Barcode)</label>
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" data-action="open-barcode-scanner" data-target-input="productBarcode" class="erp-btn erp-btn-secondary text-[11px] py-1 px-2 text-[var(--primary)] inline-flex items-center gap-1" title="تشغيل الكاميرا لمسح باركود الصنف مباشرة">
                                    <x-icon name="camera" size="14" />
                                    <span>مسح بالكاميرا</span>
                                </button>
                                <button type="button" data-action="generate-barcode" class="erp-btn erp-btn-ghost font-numeric text-[11px] py-0.5 px-2 text-[var(--text-muted)] inline-flex items-center gap-1">
                                    <x-icon name="zap" size="13" />
                                    <span>توليد كود</span>
                                </button>
                            </div>
                        </div>
                        <div class="relative flex items-center">
                            <input 
                                type="text" 
                                id="productBarcode" 
                                name="barcode" 
                                value="{{ old('barcode') }}" 
                                class="erp-input font-numeric pl-11 @error('barcode') erp-input-error @enderror" 
                                placeholder="امسح بالكاميرا أو اكتب الباركود..."
                            >
                            <button 
                                type="button" 
                                data-action="open-barcode-scanner"
                                data-target-input="productBarcode"
                                title="تشغيل الكاميرا لمسح الباركود"
                                class="absolute left-1.5 h-7 w-8 border-0 bg-[var(--primary-soft)] text-[var(--primary)] rounded inline-flex items-center justify-center cursor-pointer transition-all">
                                <x-icon name="camera" size="16" />
                            </button>
                        </div>
                        <small class="text-[11px] text-[var(--text-muted)]">
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
                <div class="erp-card-body flex flex-col gap-4">
                    <!-- Prices 3-Column Grid -->
                    <div class="erp-pricing-grid">
                        <div class="erp-form-group">
                            <label for="costPrice" class="erp-form-label">
                                سعر التكلفة <span class="text-[var(--danger)]">*</span>
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
                            <small class="text-[10px] text-[var(--text-muted)]">تكلفة الصين والشحن</small>
                        </div>

                        <div class="erp-form-group">
                            <label for="wholesalePrice" class="erp-form-label">
                                سعر الجملة <span class="text-[var(--danger)]">*</span>
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
                            <small class="text-[10px] text-[var(--text-muted)]">سعر بيع التجار</small>
                        </div>

                        <div class="erp-form-group">
                            <label for="retailPrice" class="erp-form-label">
                                سعر التجزئة <span class="text-[var(--danger)]">*</span>
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
                            <small class="text-[10px] text-[var(--text-muted)]">المقترح للقطاعي</small>
                        </div>
                    </div>

                    <!-- Min Stock Alert -->
                    <div class="erp-form-group">
                        <label for="minStockAlert" class="erp-form-label">
                            حد الطلب الأدنى (تنبيه النواقص) <span class="text-[var(--danger)]">*</span>
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
                        <small class="text-[11px] text-[var(--text-muted)]">
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
                        <div id="imagePreviewContainer" class="mt-2.5 hidden">
                            <img id="imagePreview" src="" alt="معاينة الصورة" class="max-h-[90px] rounded border border-[var(--border-color)] object-contain">
                        </div>
                    </div>

                    <!-- Active Status Toggle -->
                    <div class="mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', '1') ? 'checked' : '' }} 
                                class="w-4 h-4 accent-[var(--primary)]"
                            >
                            <span class="text-xs font-semibold text-[var(--text-main)]">تفعيل الصنف وإتاحته في النظام وقوائم المبيعات</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <x-barcode-scanner-modal target-input-id="productBarcode" />
@endsection
