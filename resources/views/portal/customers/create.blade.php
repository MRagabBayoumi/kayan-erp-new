@extends('layouts.portal', ['title' => 'تسجيل عميل جديد'])

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>تسجيل عميل جديد من الميدان</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">سيتم ربط العميل بحسابك لاحتساب عمولاتك تلقائياً</p>
        </div>
        <a href="{{ route('portal.customers.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </a>
    </div>

    <!-- Registration Form -->
    <form method="POST" action="{{ route('portal.customers.store') }}" class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
        @csrf

        <!-- Name & Company -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">اسم العميل / المحل <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="مثال: مؤسسة النور للتجارة أو الأستاذ محمد علي"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">الاسم التجاري / الشركة (اختياري)</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="اسم المعرض أو المتجر"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
            </div>
        </div>

        <!-- Phone & Phone Alt -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهاتف الأساسي <span class="text-rose-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="مثال: 01012345678"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-numeric focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">هاتف إضافي / واتساب</label>
                <input type="tel" name="phone_alt" value="{{ old('phone_alt') }}" placeholder="مثال: 01223456789"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-numeric focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
            </div>
        </div>

        <!-- Pricing Tier -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">فئة السعر المعتمدة للعميل <span class="text-rose-500">*</span></label>
            <div class="grid grid-cols-3 gap-2">
                <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white cursor-pointer has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-900 transition-all text-center">
                    <input type="radio" name="pricing_tier" value="retail" class="sr-only" {{ old('pricing_tier', 'retail') === 'retail' ? 'checked' : '' }}>
                    <span class="text-xs font-bold">تجزئة</span>
                    <span class="text-[10px] text-slate-400">قطاعي عادي</span>
                </label>
                <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white cursor-pointer has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-900 transition-all text-center">
                    <input type="radio" name="pricing_tier" value="wholesale" class="sr-only" {{ old('pricing_tier') === 'wholesale' ? 'checked' : '' }}>
                    <span class="text-xs font-bold">جملة</span>
                    <span class="text-[10px] text-slate-400">سعر تجار</span>
                </label>
                <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white cursor-pointer has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-900 transition-all text-center">
                    <input type="radio" name="pricing_tier" value="distributor" class="sr-only" {{ old('pricing_tier') === 'distributor' ? 'checked' : '' }}>
                    <span class="text-xs font-bold">موزع</span>
                    <span class="text-[10px] text-slate-400">كبار الموزعين</span>
                </label>
            </div>
            <input type="hidden" name="customer_type" id="customerTypeInput" value="{{ old('pricing_tier', 'retail') }}">
        </div>

        <!-- City & Address -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">المدينة / المحافظة</label>
                <input type="text" name="city" value="{{ old('city') }}" placeholder="مثال: القاهرة / الجيزة / طنطا"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">حد الائتمان (الآجل)</label>
                <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', 0) }}" placeholder="0.00"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-numeric focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">العنوان التفصيلي / معالم مميزة</label>
            <input type="text" name="address" value="{{ old('address') }}" placeholder="الشارع، رقم المبنى، علامة مميزة"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
        </div>

        <!-- Geolocation GPS Button -->
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>الموقع الجغرافي للعميل (GPS)</span>
                </span>
                <span id="gpsStatusText" class="text-[10px] text-slate-400 font-medium">غير محدد</span>
            </div>

            <button type="button" id="gpsBtn" class="w-full py-2.5 px-3 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 flex items-center justify-center gap-2 transition-all">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>التقاط موقع العميل الحالي من الموبايل</span>
            </button>

            <input type="hidden" name="latitude" id="latitudeInput" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitudeInput" value="{{ old('longitude') }}">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات إضافية عن العميل</label>
            <textarea name="notes" rows="2" placeholder="مواعيد التواجد، تفضيلات البضاعة، طريقة السداد..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">{{ old('notes') }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-700 hover:to-sky-700 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>حفظ العميل والبدء في الطلب</span>
            </button>
        </div>
    </form>

</div>
@endsection
