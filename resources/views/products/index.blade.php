@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="المنتجات والأصناف" :breadcrumbs="['المخزون والمنتجات' => route('products.index')]">
        <x-slot:actions>
            <a href="{{ route('products.create') }}" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>إضافة منتج جديد</span>
            </a>
            <button type="button" id="topBatchPrintBtn" data-action="direct-print-batch" class="erp-btn erp-btn-secondary hidden text-[var(--primary)]" title="طباعة فورية للأصناف المحددة بدون نوافذ">
                <x-icon name="printer" size="16" />
                <span>طباعة ملصقات (<span id="topBatchCount" class="font-numeric">0</span>)</span>
            </button>
            <a href="{{ route('products.import.form') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="upload" size="16" />
                <span>استيراد من إكسيل</span>
            </a>
            <a href="{{ route('categories.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="tag" size="16" />
                <span>إدارة التصنيفات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric px-2.5 py-1 text-xs">
                <span>إجمالي الأصناف:</span>
                <strong>{{ $products->total() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="products-index">
        <!-- Flash Messages -->
        @if(session('status') === 'success')
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check" size="16" />
                <div>{{ session('message', 'تمت العملية بنجاح.') }}</div>
            </div>
        @endif

        @if(session('status') === 'error')
            <div class="erp-alert erp-alert-danger mb-4" role="alert">
                <x-icon name="alert-triangle" size="16" />
                <div>{{ session('message', 'حدث خطأ أثناء تنفيذ العملية.') }}</div>
            </div>
        @endif

        <!-- Search & Filter Bar -->
        <div class="erp-card mb-4 p-3">
            <form action="{{ route('products.index') }}" method="GET" class="erp-filter-form">
                <!-- Search Input with Camera Barcode Scanner -->
                <div class="flex-1 min-w-[220px] relative flex items-center">
                    <input 
                        type="search" 
                        id="productSearchInput"
                        name="search" 
                        value="{{ $currentSearch }}" 
                        class="erp-input font-numeric w-full pr-9 pl-9" 
                        placeholder="بحث باسم المنتج، كود SKU، أو الباركود..."
                    >
                    <span class="absolute right-2.5 opacity-60 flex items-center pointer-events-none">
                        <x-icon name="search" size="16" />
                    </span>
                    <button 
                        type="button" 
                        data-action="open-barcode-scanner"
                        data-target-input="productSearchInput"
                        title="مسح الباركود بالكاميرا للبحث السريع"
                        class="absolute left-1 h-7 w-7 border-0 bg-[var(--primary-soft)] text-[var(--primary)] rounded inline-flex items-center justify-center cursor-pointer">
                        <x-icon name="camera" size="15" />
                    </button>
                </div>

                <!-- Category Filter -->
                <div class="min-w-[180px]">
                    <select name="category_id" class="erp-input w-full">
                        <option value="">-- كل التصنيفات --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string)$currentCategoryId === (string)$category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="min-w-[130px]">
                    <select name="status" class="erp-input w-full">
                        <option value="">-- كل الحالات --</option>
                        <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>نشط فقط</option>
                        <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>معطل فقط</option>
                    </select>
                </div>

                <!-- Actions -->
                <button type="submit" class="erp-btn erp-btn-primary py-1.5 px-4">
                    <span>تصفية</span>
                </button>

                @if($currentSearch || $currentCategoryId || $currentStatus)
                    <a href="{{ route('products.index') }}" class="erp-btn erp-btn-secondary py-1.5 px-3" title="إلغاء التصفية">
                        <x-icon name="x" size="14" />
                        <span>إلغاء الفلترة</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Products Table Card -->
        <div class="erp-card p-0 overflow-hidden">
            @if($products->isEmpty())
                <div class="text-center py-12 px-5">
                    <div class="mb-3 opacity-40 flex justify-center">
                        <x-icon name="package" size="48" />
                    </div>
                    <h3 class="text-base font-bold mb-1.5 text-[var(--text-main)]">لم يتم العثور على أي منتجات</h3>
                    <p class="text-xs text-[var(--text-muted)] mb-4">
                        @if($currentSearch || $currentCategoryId || $currentStatus)
                            لا توجد أصناف تطابق معايير البحث الحالية. جرب تغيير كلمات البحث أو إلغاء الفلترة.
                        @else
                            ابدأ بتسجيل أول صنف في مخزون إكسسوارات كيان.
                        @endif
                    </p>
                    @if(!($currentSearch || $currentCategoryId || $currentStatus))
                        <a href="{{ route('products.create') }}" class="erp-btn erp-btn-primary">
                            <x-icon name="plus" size="16" />
                            <span>إضافة أول منتج</span>
                        </a>
                    @endif
                </div>
            @else
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="w-10 text-center">
                                    <input type="checkbox" id="selectAllCheckbox" title="تحديد جميع الأصناف" class="cursor-pointer w-4 h-4">
                                </th>
                                <th class="w-[50px] text-center">الصورة</th>
                                <th class="w-[130px]">كود الصنف (SKU)</th>
                                <th>اسم المنتج</th>
                                <th class="w-[140px]">التصنيف</th>
                                <th class="w-[150px]">الباركود</th>
                                <th class="w-[110px] text-left">سعر الجملة</th>
                                <th class="w-[110px] text-left">سعر التجزئة</th>
                                <th class="w-20 text-center">الوحدة</th>
                                <th class="w-20 text-center">الحالة</th>
                                <th class="w-[140px] text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr id="product-row-{{ $product->id }}" class="product-row-clickable" data-url="{{ route('products.show', $product) }}" title="عرض تفاصيل الصنف: {{ $product->name }}">
                                    <!-- Checkbox -->
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               class="product-select-cb cursor-pointer w-4 h-4" 
                                               value="{{ $product->id }}" 
                                               data-id="{{ $product->id }}" 
                                               data-name="{{ $product->name }}" 
                                               data-sku="{{ $product->sku }}" 
                                               data-barcode="{{ $product->barcode ?: $product->sku }}">
                                    </td>

                                    <!-- Image Thumbnail -->
                                    <td class="text-center">
                                        <a href="{{ route('products.show', $product) }}" class="inline-block" title="عرض تفاصيل الصنف: {{ $product->name }}">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-9 h-9 object-cover rounded-sm border border-[var(--border-color)] inline-block">
                                            @else
                                                <div class="w-9 h-9 bg-[var(--bg-surface-alt)] rounded-sm inline-flex items-center justify-center opacity-60 border border-dashed border-[var(--border-color)]">
                                                    <x-icon name="package" size="18" />
                                                </div>
                                            @endif
                                        </a>
                                    </td>

                                    <!-- SKU -->
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="font-numeric font-bold text-[var(--primary)] hover:underline inline-block" title="عرض تفاصيل الصنف">
                                            {{ $product->sku }}
                                        </a>
                                    </td>

                                    <!-- Name -->
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="font-semibold text-[var(--text-main)] hover:text-[var(--primary)] block" title="عرض تفاصيل الصنف">
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
                                            <span class="font-numeric font-mono bg-[var(--bg-surface-alt)] py-0.5 px-1.5 rounded text-xs border border-[var(--border-color)]">
                                                {{ $product->barcode }}
                                            </span>
                                        @else
                                            <span class="text-[var(--text-light)] text-[11px]">بدون باركود</span>
                                        @endif
                                    </td>

                                    <!-- Wholesale Price -->
                                    <td class="text-left font-numeric">
                                        <strong class="text-[var(--success-text)]">{{ number_format((float)$product->wholesale_price, 2) }}</strong>
                                        <small class="text-[var(--text-muted)] text-[10px]">ج.م</small>
                                    </td>

                                    <!-- Retail Price -->
                                    <td class="text-left font-numeric">
                                        <span>{{ number_format((float)$product->retail_price, 2) }}</span>
                                        <small class="text-[var(--text-muted)] text-[10px]">ج.م</small>
                                    </td>

                                    <!-- Unit -->
                                    <td class="text-center">
                                        <span class="text-xs text-[var(--text-muted)]">{{ $product->unit }}</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="text-center">
                                        @if($product->is_active)
                                            <span class="erp-badge erp-badge-success text-[11px]">نشط</span>
                                        @else
                                            <span class="erp-badge erp-badge-danger text-[11px]">معطل</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" class="erp-btn erp-btn-ghost erp-btn-icon" title="طباعة فورية للملصق (38×15 مم) بدون نوافذ" data-action="direct-print-row" data-product-id="{{ $product->id }}">
                                                <x-icon name="printer" size="15" />
                                            </button>
                                            <a href="{{ route('products.show', $product) }}" class="erp-btn erp-btn-ghost erp-btn-icon" title="عرض تفاصيل الصنف">
                                                <x-icon name="eye" size="15" />
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" class="erp-btn erp-btn-ghost erp-btn-icon" title="تعديل">
                                                <x-icon name="pencil" size="15" />
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" data-confirm="هل أنت متأكد من حذف المنتج: {{ $product->name }}؟" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="erp-btn erp-btn-ghost erp-btn-icon text-[var(--danger)]" title="حذف">
                                                    <x-icon name="trash" size="15" />
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
                    <div class="py-3 px-4 border-t border-[var(--border-color)] flex items-center justify-between">
                        <div class="text-xs text-[var(--text-muted)] font-numeric">
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
        <div id="batchActionBar" class="erp-batch-action-bar hidden">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="erp-badge erp-badge-primary text-xs py-1.5 px-3">
                    <span>تم تحديد:</span>
                    <strong id="selectedProductsCount" class="font-numeric mx-1">0</strong>
                    <span>صنف</span>
                </span>
                <span class="text-xs text-[var(--text-muted)]">
                    جاهز لطباعة ملصقات الباركود على طابعة <strong>Xprinter XP-233B</strong> (38×15 مم)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-action="direct-print-batch" class="erp-btn erp-btn-primary" title="طباعة فورية للملصقات مباشرة بدون نوافذ">
                    <x-icon name="printer" size="16" />
                    <span>طباعة فورية للمحددة</span>
                </button>
                <button type="button" data-action="open-batch-modal" class="erp-btn erp-btn-secondary" title="تخصيص عدد النسخ لكل صنف">
                    <x-icon name="sliders" size="16" />
                    <span>تخصيص الكميات</span>
                </button>
                <button type="button" data-action="clear-selections" class="erp-btn erp-btn-secondary">
                    <x-icon name="x" size="14" />
                    <span>إلغاء التحديد</span>
                </button>
            </div>
        </div>

        <!-- Batch Barcode Print Modal -->
        <div id="batchPrintModal" class="erp-modal-overlay hidden">
            <div class="erp-card erp-modal-container max-w-[650px]">
                <!-- Modal Header -->
                <div class="erp-card-header erp-modal-header">
                    <div class="erp-card-title erp-modal-title">
                        <x-icon name="printer" size="18" />
                        <span>طباعة ملصقات الباركود للمنتجات المحددة (38×15 مم)</span>
                    </div>
                    <button type="button" data-action="close-batch-modal" class="erp-btn erp-btn-ghost erp-btn-icon erp-modal-close" title="إغلاق">
                        <x-icon name="x" size="16" />
                    </button>
                </div>

                <!-- Modal Sub-header: Global Quantity Controls -->
                <div class="bg-[var(--bg-surface)] py-3 px-4.5 border-b border-[var(--border-color)] flex items-center justify-between flex-wrap gap-2.5 shrink-0">
                    <div class="text-xs font-semibold text-[var(--text-main)]">
                        تعيين كمية موحدة للجميع:
                    </div>
                    <div class="flex gap-1.5">
                        <button type="button" data-preset-qty="1" class="erp-btn erp-btn-ghost font-numeric py-1 px-2.5 text-xs">1 ملصق</button>
                        <button type="button" data-preset-qty="2" class="erp-btn erp-btn-ghost font-numeric py-1 px-2.5 text-xs">2 ملصق</button>
                        <button type="button" data-preset-qty="5" class="erp-btn erp-btn-ghost font-numeric py-1 px-2.5 text-xs">5 ملصقات</button>
                        <button type="button" data-preset-qty="10" class="erp-btn erp-btn-ghost font-numeric py-1 px-2.5 text-xs">10 ملصقات</button>
                        <button type="button" data-preset-qty="20" class="erp-btn erp-btn-ghost font-numeric py-1 px-2.5 text-xs">20 ملصق</button>
                    </div>
                </div>

                <!-- Modal Body: Scrollable Selected Items Table -->
                <div class="p-0 overflow-y-auto flex-1">
                    <form id="batchPrintForm" action="{{ route('products.print-batch') }}" method="POST" target="_blank">
                        @csrf
                        <input type="hidden" name="autoprint" id="batchFormAutoprint" value="0">
                        <div id="batchFormHiddenInputs"></div>

                        <table class="erp-table m-0">
                            <thead>
                                <tr class="bg-[var(--bg-surface-alt)] sticky top-0 z-10">
                                    <th class="w-[50px] text-center">#</th>
                                    <th>اسم المنتج</th>
                                    <th class="w-[140px]">كود الصنف (SKU)</th>
                                    <th class="w-[160px] text-center">عدد الملصقات</th>
                                </tr>
                            </thead>
                            <tbody id="batchModalTableBody">
                                <!-- Populated via JavaScript -->
                            </tbody>
                        </table>
                    </form>
                </div>

                <!-- Modal Footer: Summary & Actions -->
                <div class="erp-card-footer bg-[var(--bg-surface-alt)] border-t border-[var(--border-color)] py-3 px-4.5 flex items-center justify-between flex-wrap gap-3 shrink-0">
                    <div class="text-xs">
                        <span>إجمالي الأصناف: <strong id="batchSummaryProductsCount" class="font-numeric">0</strong></span>
                        <span class="mx-2 text-[var(--border-color)]">|</span>
                        <span>إجمالي الملصقات: <strong id="batchSummaryTotalLabels" class="font-numeric text-[var(--primary)]">0</strong> ملصق</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-action="close-batch-modal" class="erp-btn erp-btn-secondary">
                            <span>إلغاء</span>
                        </button>
                        <button type="button" data-action="submit-batch-print" data-autoprint="false" class="erp-btn erp-btn-secondary" title="فتح المعاينة لجميع الملصقات">
                            <x-icon name="eye" size="16" />
                            <span>معاينة الطباعة</span>
                        </button>
                        <button type="button" data-action="submit-batch-print" data-autoprint="true" class="erp-btn erp-btn-primary">
                            <x-icon name="printer" size="16" />
                            <span>طباعة الكل الآن</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Iframe for Direct Silent Printing -->
        <iframe id="directPrintIframe" name="directPrintIframe" class="hidden"></iframe>

        <x-barcode-scanner-modal target-input-id="productSearchInput" />

        <!-- Toast Notification for Direct Print Feedback -->
        <div id="printNotificationToast" class="erp-toast hidden">
            <span class="erp-toast-icon"><x-icon name="printer" size="18" /></span>
            <span id="printToastMessage" class="erp-toast-message">جاري إرسال الملصق للطباعة...</span>
        </div>
    </div>
@endsection
