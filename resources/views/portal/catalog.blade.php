@extends('layouts.portal', ['title' => 'كتالوج المنتجات'])

@section('content')
<div class="space-y-4">

    <!-- Header & Search Controls (Kayan ERP Card) -->
    <div class="erp-card mb-0 p-4">
        <div class="flex items-center justify-between mb-3 pb-3 border-b border-[var(--border-light)]">
            <h1 class="text-sm sm:text-base font-bold text-[var(--text-main)] flex items-center gap-2">
                <x-icon name="package" size="18" class="text-[var(--primary)]" />
                <span>كتالوج منتجات المعرض والمخازن</span>
            </h1>
            <span class="erp-badge erp-badge-neutral font-numeric text-xs">
                {{ $products->total() }} صنف
            </span>
        </div>

        <form method="GET" action="{{ route('portal.catalog') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
            <!-- Search Bar with integrated camera scanner -->
            <div class="relative sm:col-span-6 lg:col-span-6">
                <input type="search" id="catalogSearchInput" name="search" value="{{ $currentSearch }}" placeholder="ابحث باسم المنتج، الكود، أو الباركود..."
                    class="erp-input w-full pr-9 pl-10 text-xs sm:text-sm">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none">
                    <x-icon name="search" size="15" />
                </div>
                <button type="button" data-action="open-barcode-scanner" data-target-input="catalogSearchInput" title="مسح الباركود بالكاميرا" 
                    class="absolute left-1.5 top-1/2 -translate-y-1/2 p-1.5 text-[var(--primary)] hover:bg-[var(--primary-soft)] rounded transition-colors flex items-center justify-center">
                    <x-icon name="camera" size="16" />
                </button>
            </div>

            <!-- Category Filter -->
            <div class="sm:col-span-3 lg:col-span-3">
                <select name="category_id" class="portal-auto-submit erp-select text-xs">
                    <option value="">جميع الأقسام والتصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$currentCategory === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- In Stock Checkbox & Submit -->
            <div class="flex items-center gap-2 sm:col-span-3 lg:col-span-3">
                <label class="flex items-center gap-2 px-3 py-2 bg-[var(--bg-hover)] border border-[var(--border-color)] rounded text-xs text-[var(--text-main)] cursor-pointer select-none flex-1 justify-center h-[38px]">
                    <input type="checkbox" name="in_stock_only" value="1" {{ $inStockOnly ? 'checked' : '' }} class="portal-auto-submit rounded border-[var(--border-color)] text-[var(--primary)] focus:ring-[var(--primary)]">
                    <span class="font-medium">المتوفر فقط</span>
                </label>

                <button type="submit" class="erp-btn erp-btn-primary shrink-0 text-xs h-[38px] px-4">
                    <x-icon name="filter" size="13" />
                    <span>تصفية</span>
                </button>

                @if($currentSearch || $currentCategory || $inStockOnly)
                    <a href="{{ route('portal.catalog') }}" class="erp-btn erp-btn-secondary shrink-0 text-xs h-[38px] px-3" title="إعادة تعيين">
                        <x-icon name="rotate-ccw" size="13" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Product Cards Responsive Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($products as $product)
            @php
                $totalStock = (float) $product->stockBalances->sum('quantity');
            @endphp
            <div class="erp-card mb-0 p-4 hover:border-[var(--primary-border)] hover:shadow-xs transition-all flex flex-col justify-between">
                <div>
                    <!-- Header: Name & Category -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-bold text-[var(--text-main)] leading-snug line-clamp-2">{{ $product->name }}</h2>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--text-muted)] mt-1 font-numeric">
                                @if($product->sku)
                                    <span>كود: <strong class="text-[var(--text-secondary)]">{{ $product->sku }}</strong></span>
                                @endif
                                @if($product->barcode)
                                    <span>باركود: <strong class="text-[var(--text-secondary)]">{{ $product->barcode }}</strong></span>
                                @endif
                            </div>
                        </div>

                        @if($product->category)
                            <span class="erp-badge erp-badge-neutral text-[10px] shrink-0">
                                {{ $product->category->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Pricing Matrix (Retail, Wholesale, Distributor) -->
                    <div class="grid grid-cols-3 gap-1.5 p-2 rounded bg-[var(--bg-hover)] border border-[var(--border-light)] my-3">
                        <div class="text-center">
                            <span class="text-[10px] text-[var(--text-muted)] block">قطاعي</span>
                            <span class="text-xs font-bold text-[var(--text-main)] font-numeric">{{ number_format($product->retail_price, 2) }}</span>
                            <span class="text-[9px] text-[var(--text-muted)] block">ج.م</span>
                        </div>
                        <div class="text-center border-x border-[var(--border-color)]">
                            <span class="text-[10px] text-[var(--primary)] font-semibold block">جملة</span>
                            <span class="text-xs font-bold text-[var(--primary)] font-numeric">{{ number_format($product->wholesale_price, 2) }}</span>
                            <span class="text-[9px] text-[var(--primary)]/70 block">ج.م</span>
                        </div>
                        <div class="text-center">
                            <span class="text-[10px] text-emerald-700 font-semibold block">موزع</span>
                            <span class="text-xs font-bold text-emerald-700 font-numeric">{{ number_format($product->distributor_price, 2) }}</span>
                            <span class="text-[9px] text-emerald-600 block">ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- Footer: Stock Balance & Quick Action -->
                <div class="flex items-center justify-between pt-2.5 border-t border-[var(--border-light)] mt-1">
                    <div class="flex items-center gap-1.5">
                        @if($totalStock > 0)
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-semibold text-emerald-700 font-numeric">
                                متاح {{ $totalStock }} {{ $product->unit ?? 'قطعة' }}
                            </span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="text-xs font-semibold text-rose-600">غير متوفر</span>
                        @endif
                    </div>

                    <a href="{{ route('portal.orders.create') }}" class="erp-btn erp-btn-sm erp-btn-secondary text-[11px] py-1 px-2.5">
                        <x-icon name="plus" size="12" />
                        <span>إضافة لطلب</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full erp-card p-12 text-center">
                <x-icon name="inbox" size="36" class="text-slate-300 mx-auto mb-2" />
                <div class="text-sm font-bold text-[var(--text-main)]">لا توجد منتجات مطابقة للبحث</div>
                <div class="text-xs text-[var(--text-muted)] mt-1">جرب تغيير مصطلح البحث أو إزالة التصفية</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="erp-card p-3 mb-0">
            {{ $products->links() }}
        </div>
    @endif

</div>
@endsection
