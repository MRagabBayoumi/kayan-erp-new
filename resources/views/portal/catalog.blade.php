@extends('layouts.portal', ['title' => 'كتالوج المنتجات'])

@section('content')
<div class="space-y-4">

    <!-- Header & Search Controls -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>كتالوج منتجات المعرض</span>
            </h1>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-numeric">
                {{ $products->total() }} صنف
            </span>
        </div>

        <form method="GET" action="{{ route('portal.catalog') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
            <!-- Search Bar -->
            <div class="relative sm:col-span-6 lg:col-span-7">
                <input type="search" id="catalogSearchInput" name="search" value="{{ $currentSearch }}" placeholder="ابحث باسم المنتج، الكود، أو الباركود..."
                    class="w-full pl-12 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition-all">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="button" data-action="open-barcode-scanner" data-target-input="catalogSearchInput" title="مسح الباركود بالكاميرا" class="absolute left-2 top-1/2 -translate-y-1/2 p-1.5 text-sky-600 hover:text-sky-800 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors flex items-center gap-1">
                    <x-icon name="camera" size="15" />
                </button>
            </div>

            <!-- Category Filter -->
            <div class="sm:col-span-3 lg:col-span-3">
                <select name="category_id" class="portal-auto-submit w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">جميع الأقسام والتصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$currentCategory === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- In Stock Checkbox & Filter Submit -->
            <div class="flex items-center gap-2 sm:col-span-3 lg:col-span-2">
                <label class="flex items-center gap-1.5 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 cursor-pointer select-none flex-1 justify-center">
                    <input type="checkbox" name="in_stock_only" value="1" {{ $inStockOnly ? 'checked' : '' }} class="portal-auto-submit rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <span>المتوفر</span>
                </label>

                <button type="submit" class="px-3.5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shrink-0 transition-colors">
                    تصفية
                </button>
            </div>
        </form>
    </div>

    <!-- Product Cards Responsive Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($products as $product)
            @php
                $totalStock = (float) $product->stockBalances->sum('quantity');
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                <div>
                    <!-- Header: Name & Category -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-900 leading-snug">{{ $product->name }}</h2>
                            <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                @if($product->sku)
                                    <span>كود: <strong class="text-slate-600 font-numeric">{{ $product->sku }}</strong></span>
                                @endif
                                @if($product->barcode)
                                    <span>باركود: <strong class="text-slate-600 font-numeric">{{ $product->barcode }}</strong></span>
                                @endif
                            </div>
                        </div>

                        @if($product->category)
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold shrink-0">
                                {{ $product->category->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Pricing Matrix (Retail, Wholesale, Distributor) -->
                    <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-100 my-3">
                        <div class="text-center">
                            <span class="text-[10px] text-slate-500 block">قطاعي</span>
                            <span class="text-xs font-bold text-slate-800 font-numeric">{{ number_format($product->retail_price, 2) }}</span>
                            <span class="text-[9px] text-slate-400 block">ج.م</span>
                        </div>
                        <div class="text-center border-x border-slate-200">
                            <span class="text-[10px] text-indigo-600 font-semibold block">جملة</span>
                            <span class="text-xs font-bold text-indigo-700 font-numeric">{{ number_format($product->wholesale_price, 2) }}</span>
                            <span class="text-[9px] text-indigo-400 block">ج.م</span>
                        </div>
                        <div class="text-center">
                            <span class="text-[10px] text-emerald-600 font-semibold block">موزع</span>
                            <span class="text-xs font-bold text-emerald-700 font-numeric">{{ number_format($product->distributor_price, 2) }}</span>
                            <span class="text-[9px] text-emerald-400 block">ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- Footer: Stock Balance & Quick Action -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-1.5">
                        @if($totalStock > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-semibold text-emerald-700 font-numeric">
                                متاح {{ $totalStock }} {{ $product->unit ?? 'قطعة' }}
                            </span>
                        @else
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span class="text-xs font-semibold text-rose-600">غير متوفر حالياً بالمخزن</span>
                        @endif
                    </div>

                    <a href="{{ route('portal.orders.create') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-600 hover:bg-sky-600 hover:text-white font-bold text-xs transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>إضافة لطلب</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200/80">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-sm font-bold text-slate-700">لا توجد منتجات مطابقة للبحث</div>
                <div class="text-xs text-slate-400 mt-1">جرب تغيير مصطلح البحث أو إزالة التصفية</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $products->links() }}
    </div>

</div>
@endsection
