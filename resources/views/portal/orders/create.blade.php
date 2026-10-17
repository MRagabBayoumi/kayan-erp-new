@extends('layouts.portal', ['title' => 'طلب بيع جديد'])

@section('content')
<div class="space-y-4" 
     id="portalOrderApp"
     data-products="{{ $productsJson }}"
     data-selected-customer="{{ $selectedCustomerId ?? '' }}"
     data-commission-rate="{{ (float) (auth()->user()->commission_rate ?? 0) }}"
     data-commission-type="{{ auth()->user()->commission_type ?? 'percentage' }}"
     x-data="portalOrderForm()">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>إنشاء طلب بيع ميداني</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">تسجيل فاتورة أو مسودة طلب لعميلك واحتساب العمولة</p>
        </div>
        <a href="{{ route('portal.orders.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </a>
    </div>

    <form method="POST" action="{{ route('portal.orders.store') }}" @submit="validateAndSubmit($event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            <!-- Left 2 Columns on Desktop: Customer Info & Items List -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Customer & Warehouse Card -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">العميل <span class="text-rose-500">*</span></label>
                        <a href="{{ route('portal.customers.create') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>تسجيل عميل جديد</span>
                        </a>
                    </div>

                    <select name="customer_id" x-model="customerId" @change="onCustomerChange()" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition-all">
                        <option value="">-- اختر العميل --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-tier="{{ $c->pricing_tier }}" {{ (string)$selectedCustomerId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }})
                            </option>
                        @endforeach
                    </select>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">المستودع <span class="text-rose-500">*</span></label>
                            <select name="warehouse_id" required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الفاتورة</label>
                            <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-numeric focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white">
                        </div>
                    </div>

                    <!-- Price Tier Switcher -->
                    <div class="pt-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">فئة السعر المطبقة</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer text-center"
                                :class="priceTier === 'retail' ? 'bg-sky-50 border-sky-500 text-sky-700' : 'bg-slate-50 text-slate-600'">
                                <input type="radio" name="price_tier" value="retail" x-model="priceTier" @change="recalculateItemPrices()" class="sr-only">
                                <span>قطاعي</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer text-center"
                                :class="priceTier === 'wholesale' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'bg-slate-50 text-slate-600'">
                                <input type="radio" name="price_tier" value="wholesale" x-model="priceTier" @change="recalculateItemPrices()" class="sr-only">
                                <span>جملة</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer text-center"
                                :class="priceTier === 'distributor' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'bg-slate-50 text-slate-600'">
                                <input type="radio" name="price_tier" value="distributor" x-model="priceTier" @change="recalculateItemPrices()" class="sr-only">
                                <span>موزع</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Add Items Section -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>بنود الطلب</span>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-numeric" x-text="items.length + ' أصناف'"></span>
                        </h2>

                        <div class="flex items-center gap-1.5">
                            <!-- Hidden input receiving the scanned barcode -->
                            <input type="hidden" id="portalOrderBarcodeInput">

                            <button type="button" data-action="open-barcode-scanner" data-target-input="portalOrderBarcodeInput" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1 shadow-xs transition-colors">
                                <x-icon name="camera" size="14" class="text-sky-600" />
                                <span>مسح باركود</span>
                            </button>

                            <button type="button" @click="showProductPicker = true" class="px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs flex items-center gap-1 shadow-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>إضافة منتج</span>
                            </button>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-2.5">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-3 sm:p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2.5">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="item.name"></div>
                                        <div class="text-[10px] text-slate-400 font-numeric" x-text="'متاح: ' + item.available_stock + ' ' + (item.unit || 'قطعة')"></div>
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="p-1 text-slate-400 hover:text-rose-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>

                                <!-- Quantity & Price controls -->
                                <div class="grid grid-cols-12 gap-2.5 items-center">
                                    <!-- Quantity with +/- buttons -->
                                    <div class="col-span-5 sm:col-span-4 flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden">
                                        <button type="button" @click="decrementQty(index)" class="px-2.5 py-1.5 text-slate-500 hover:bg-slate-100 font-bold">-</button>
                                        <input type="number" step="1" min="0.001" x-model.number="item.quantity" @input="calculateTotals()"
                                            class="w-full text-center py-1 text-xs sm:text-sm font-bold font-numeric focus:outline-none border-x border-slate-200">
                                        <button type="button" @click="incrementQty(index)" class="px-2.5 py-1.5 text-slate-500 hover:bg-slate-100 font-bold">+</button>
                                    </div>

                                    <!-- Unit Price input -->
                                    <div class="col-span-4 sm:col-span-4">
                                        <input type="number" step="0.01" min="0" x-model.number="item.unit_price" @input="calculateTotals()"
                                            placeholder="السعر" class="w-full px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs sm:text-sm font-bold font-numeric text-center">
                                    </div>

                                    <!-- Line Total -->
                                    <div class="col-span-3 sm:col-span-4 text-left font-numeric">
                                        <span class="text-xs sm:text-sm font-extrabold text-slate-800" x-text="formatCurrency((item.quantity * item.unit_price) - (item.line_discount || 0))"></span>
                                        <span class="text-[10px] text-slate-400">ج.م</span>
                                    </div>
                                </div>

                                <!-- Hidden Inputs for Form Submission -->
                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                                <input type="hidden" :name="'items[' + index + '][line_discount]'" :value="item.line_discount || 0">
                            </div>
                        </template>

                        <div x-show="items.length === 0" class="p-8 text-center text-slate-400 text-xs border-2 border-dashed border-slate-200 rounded-xl">
                            لم تتم إضافة أصناف بعد. اضغط على «إضافة منتج» لاختيار أصناف الطلب.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Column on Desktop: Summary, Payment & Submission -->
            <div class="lg:col-span-1 space-y-4 lg:sticky lg:top-20">
                <!-- Payment & Commission Summary -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800">تفاصيل السداد والعمولة</h2>

                    <!-- Payment Type -->
                    <div class="grid grid-cols-3 gap-1.5">
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer text-center"
                            :class="paymentType === 'cash' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_type" value="cash" x-model="paymentType" class="sr-only">
                            <span>نقدي</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer text-center"
                            :class="paymentType === 'credit' ? 'bg-amber-50 border-amber-500 text-amber-700' : 'bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_type" value="credit" x-model="paymentType" class="sr-only">
                            <span>آجل</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer text-center"
                            :class="paymentType === 'partial' ? 'bg-sky-50 border-sky-500 text-sky-700' : 'bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_type" value="partial" x-model="paymentType" class="sr-only">
                            <span>جزئي</span>
                        </label>
                    </div>

                    <div x-show="paymentType === 'partial'" class="pt-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ المدفوع مقدماً</label>
                        <input type="number" step="0.01" min="0" name="paid_amount" x-model.number="paidAmount" placeholder="0.00"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-numeric focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات الطلب</label>
                        <input type="text" name="notes" placeholder="موعد التوصيل، تعليمات خاصة..."
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white">
                    </div>

                    <!-- Order Financial Breakdown -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>إجمالي الطلب:</span>
                            <span class="font-extrabold text-slate-900 text-base font-numeric" x-text="formatCurrency(subtotal) + ' ج.م'"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-emerald-600 font-bold border-t border-slate-200/60 pt-2">
                            <span class="flex items-center gap-1">
                                <span>عمولتك المتوقعة:</span>
                                <span class="text-[10px] text-emerald-500 font-medium">({{ auth()->user()->commission_rate ?? 0 }}{{ auth()->user()->commission_type === 'percentage' ? '%' : ' ج.م' }})</span>
                            </span>
                            <span class="font-numeric text-sm font-extrabold" x-text="'+ ' + formatCurrency(estimatedCommission) + ' ج.م'"></span>
                        </div>
                    </div>
                </div>

                <!-- Action Submission Buttons -->
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="submit" name="action" value="draft"
                        class="py-3 px-3 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold shadow-xs active:scale-[0.99] transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        <span>مسودة</span>
                    </button>

                    <button type="submit" name="action" value="post"
                        class="py-3 px-3 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-sky-500/20 active:scale-[0.99] transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>ترحيل الطلب</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Product Picker Modal (Mobile Bottom Sheet / Drawer) -->
    <div x-show="showProductPicker" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showProductPicker = false"></div>

        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div class="relative transform overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white text-right shadow-xl transition-all w-full max-w-lg max-h-[85vh] flex flex-col">
                <!-- Modal Header -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">اختر المنتج لإضافته للطلب</h3>
                    <button type="button" @click="showProductPicker = false" class="p-1 text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Product Search Inside Modal -->
                <div class="p-3 border-b border-slate-100 bg-slate-50 flex items-center gap-2">
                    <input type="text" x-model="productSearch" placeholder="ابحث باسم المنتج أو الكود أو الباركود..."
                        class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <button type="button" data-action="open-barcode-scanner" data-target-input="portalOrderBarcodeInput" class="p-2 bg-white hover:bg-slate-100 text-sky-600 rounded-xl border border-slate-200 transition-colors shrink-0" title="مسح الباركود بالكاميرا">
                        <x-icon name="camera" size="16" />
                    </button>
                </div>

                <!-- Product List in Modal -->
                <div class="overflow-y-auto p-3 space-y-2 flex-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="p-3 rounded-xl border border-slate-200/80 hover:border-sky-300 hover:bg-sky-50/30 transition-all flex items-center justify-between cursor-pointer"
                            @click="selectProduct(product)">
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-800" x-text="product.name"></div>
                                <div class="text-[11px] text-slate-400 font-numeric mt-0.5" x-text="'كود: ' + (product.sku || '---') + ' · متاح: ' + product.available_stock"></div>
                            </div>
                            <div class="text-left shrink-0">
                                <div class="text-xs font-extrabold text-sky-600 font-numeric" x-text="formatCurrency(getProductPrice(product)) + ' ج.م'"></div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700">+ إضافة</span>
                            </div>
                        </div>
                    </template>

                    <div x-show="filteredProducts.length === 0" class="p-8 text-center text-slate-400 text-xs">
                        لا توجد أصناف مطابقة للبحث
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
