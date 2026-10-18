@extends('layouts.portal', ['title' => 'تسجيل عميل جديد'])

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <!-- Header Card (Kayan ERP Odoo Card) -->
    <div class="erp-card mb-0 p-4 flex items-center justify-between">
        <div>
            <h1 class="text-sm sm:text-base font-bold text-[var(--text-main)] flex items-center gap-2">
                <x-icon name="user-plus" size="18" class="text-[var(--primary)]" />
                <span>تسجيل عميل جديد من الميدان</span>
            </h1>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">سيتم ربط العميل بحسابك الميداني لاحتساب عمولاتك تلقائياً</p>
        </div>
        <a href="{{ route('portal.customers.index') }}" class="erp-btn erp-btn-secondary erp-btn-sm text-xs">
            <x-icon name="arrow-right" size="13" />
            <span>رجوع للعملاء</span>
        </a>
    </div>

    <!-- Registration Form -->
    <form method="POST" action="{{ route('portal.customers.store') }}" class="erp-card mb-0 p-4 sm:p-6 space-y-4">
        @csrf

        <!-- Name & Company -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">
                    اسم العميل / المحل <span class="required">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="مثال: مؤسسة النور للتجارة أو الأستاذ محمد علي"
                    class="erp-input text-xs sm:text-sm">
            </div>

            <div>
                <label class="erp-form-label font-bold text-xs mb-1">الاسم التجاري / الشركة (اختياري)</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="اسم المعرض أو المتجر"
                    class="erp-input text-xs sm:text-sm">
            </div>
        </div>

        <!-- Phone & Phone Alt -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">
                    رقم الهاتف الأساسي <span class="required">*</span>
                </label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="مثال: 01012345678"
                    class="erp-input text-xs sm:text-sm font-numeric">
            </div>
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">هاتف إضافي / واتساب</label>
                <input type="tel" name="phone_alt" value="{{ old('phone_alt') }}" placeholder="مثال: 01223456789"
                    class="erp-input text-xs sm:text-sm font-numeric">
            </div>
        </div>

        <!-- Pricing Tier -->
        <div>
            <label class="erp-form-label font-bold text-xs mb-1.5">
                فئة السعر المعتمدة للعميل <span class="required">*</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
                <label class="flex flex-col items-center justify-center p-2.5 rounded border border-[var(--border-color)] bg-[var(--bg-hover)] hover:bg-white cursor-pointer transition-all text-center select-none has-checked:border-[var(--primary)] has-checked:bg-[var(--primary-soft)] has-checked:text-[var(--primary)]">
                    <input type="radio" name="pricing_tier" value="retail" class="sr-only" {{ old('pricing_tier', 'retail') === 'retail' ? 'checked' : '' }}>
                    <span class="text-xs font-bold">تجزئة</span>
                    <span class="text-[10px] text-[var(--text-muted)]">قطاعي عادي</span>
                </label>
                <label class="flex flex-col items-center justify-center p-2.5 rounded border border-[var(--border-color)] bg-[var(--bg-hover)] hover:bg-white cursor-pointer transition-all text-center select-none has-checked:border-[var(--primary)] has-checked:bg-[var(--primary-soft)] has-checked:text-[var(--primary)]">
                    <input type="radio" name="pricing_tier" value="wholesale" class="sr-only" {{ old('pricing_tier') === 'wholesale' ? 'checked' : '' }}>
                    <span class="text-xs font-bold">جملة</span>
                    <span class="text-[10px] text-[var(--text-muted)]">سعر تجار</span>
                </label>
                <label class="flex flex-col items-center justify-center p-2.5 rounded border border-[var(--border-color)] bg-[var(--bg-hover)] hover:bg-white cursor-pointer transition-all text-center select-none has-checked:border-[var(--primary)] has-checked:bg-[var(--primary-soft)] has-checked:text-[var(--primary)]">
                    <input type="radio" name="pricing_tier" value="distributor" class="sr-only" {{ old('pricing_tier') === 'distributor' ? 'checked' : '' }}>
                    <span class="text-xs font-bold">موزع</span>
                    <span class="text-[10px] text-[var(--text-muted)]">كبار الموزعين</span>
                </label>
            </div>
            <input type="hidden" name="customer_type" id="customerTypeInput" value="{{ old('pricing_tier', 'retail') }}">
        </div>

        <!-- City & Credit Limit -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">المدينة / المنطقة</label>
                <input type="text" name="city" value="{{ old('city') }}" placeholder="مثال: القاهرة / الجيزة / طنطا"
                    class="erp-input text-xs sm:text-sm">
            </div>
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">حد الائتمان (الآجل)</label>
                <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', 0) }}" placeholder="0.00"
                    class="erp-input text-xs sm:text-sm font-numeric">
            </div>
        </div>

        <div>
            <label class="erp-form-label font-bold text-xs mb-1">العنوان التفصيلي / معالم مميزة</label>
            <input type="text" name="address" value="{{ old('address') }}" placeholder="الشارع، رقم المبنى، علامة مميزة"
                class="erp-input text-xs sm:text-sm">
        </div>

        <!-- Geolocation GPS Button -->
        <div class="p-3.5 rounded bg-[var(--bg-hover)] border border-[var(--border-color)] space-y-2">
            <div class="flex items-center justify-between">
                <span class="erp-form-label font-bold text-xs flex items-center gap-1.5 mb-0">
                    <x-icon name="map-pin" size="14" class="text-rose-500" />
                    <span>الموقع الجغرافي للعميل (GPS)</span>
                </span>
                <span id="gpsStatusText" class="text-[10px] text-[var(--text-muted)] font-medium">غير محدد</span>
            </div>

            <button type="button" id="gpsBtn" class="erp-btn erp-btn-secondary w-full py-2 text-xs font-bold justify-center">
                <x-icon name="navigation" size="14" class="text-[var(--primary)]" />
                <span>التقاط موقع العميل الحالي من الهاتف</span>
            </button>

            <input type="hidden" name="latitude" id="latitudeInput" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitudeInput" value="{{ old('longitude') }}">
        </div>

        <div>
            <label class="erp-form-label font-bold text-xs mb-1">ملاحظات إضافية عن العميل</label>
            <textarea name="notes" rows="2" placeholder="مواعيد التواجد، تفضيلات البضاعة، طريقة السداد..."
                class="erp-textarea text-xs sm:text-sm">{{ old('notes') }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="erp-btn erp-btn-primary w-full py-3 text-sm font-bold justify-center shadow-xs">
                <x-icon name="check-circle" size="16" />
                <span>حفظ بيانات العميل والبدء في الطلب</span>
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gpsBtn = document.getElementById('gpsBtn');
        const latInput = document.getElementById('latitudeInput');
        const lngInput = document.getElementById('longitudeInput');
        const statusText = document.getElementById('gpsStatusText');

        if (latInput && latInput.value && lngInput && lngInput.value) {
            statusText.textContent = 'تم تحديد الإحداثيات سابقاً (' + Number(latInput.value).toFixed(4) + ', ' + Number(lngInput.value).toFixed(4) + ')';
            statusText.className = 'text-[10px] text-emerald-700 font-bold';
        }

        if (gpsBtn) {
            gpsBtn.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    alert('عذراً، متصفحك لا يدعم تحديد الموقع الجغرافي.');
                    return;
                }

                statusText.textContent = 'جارٍ تحديد موقع الهاتف...';
                statusText.className = 'text-[10px] text-[var(--primary)] font-bold';
                gpsBtn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        latInput.value = lat;
                        lngInput.value = lng;
                        statusText.textContent = 'تم التقاط الموقع بنجاح (' + lat.toFixed(4) + ', ' + lng.toFixed(4) + ')';
                        statusText.className = 'text-[10px] text-emerald-700 font-bold';
                        gpsBtn.disabled = false;
                    },
                    function(error) {
                        statusText.textContent = 'تعذر التقاط الموقع: ' + (error.message || 'يرجى تفعيل صلاحية الـ GPS');
                        statusText.className = 'text-[10px] text-rose-600 font-bold';
                        gpsBtn.disabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });
        }
    });
</script>
@endpush
@endsection
