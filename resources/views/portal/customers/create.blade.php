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
    <form method="POST" action="{{ route('portal.customers.store') }}" enctype="multipart/form-data" class="erp-card mb-0 p-4 sm:p-6 space-y-4">
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

        <!-- Pricing Tier & Manager Approval Notice -->
        <div class="p-3 rounded-lg border border-blue-200 bg-blue-50/70 dark:border-blue-900/40 dark:bg-blue-950/20 text-xs text-blue-900 dark:text-blue-300 space-y-1">
            <div class="flex items-center justify-between">
                <span class="font-bold flex items-center gap-1.5">
                    <x-icon name="tag" size="14" class="text-blue-600 dark:text-blue-400" />
                    <span>فئة التسعير التلقائية للمندوب:</span>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white">سعر الجملة</span>
            </div>
            <p class="text-[11px] text-blue-700 dark:text-blue-400">يتم إرسال الطلب لإدارة النظام لاعتماده وتحديد الحد الائتماني أو تعديل فئة التسعير إن رغب المدير في ذلك.</p>
        </div>

        <!-- City & Address -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">المدينة / المنطقة</label>
                <input type="text" name="city" value="{{ old('city') }}" placeholder="مثال: القاهرة / الجيزة / طنطا"
                    class="erp-input text-xs sm:text-sm">
            </div>
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">العنوان التفصيلي / معالم مميزة</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="الشارع، رقم المبنى، علامة مميزة"
                    class="erp-input text-xs sm:text-sm">
            </div>
        </div>

        <!-- Store Images Upload (Required/Recommended with camera) -->
        <div class="p-3.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-hover)] space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="erp-form-label font-bold text-xs flex items-center gap-1.5 mb-0 text-[var(--text-main)]">
                    <x-icon name="camera" size="14" class="text-[var(--primary)]" />
                    <span>صور مكان / واجهة المحل للمراجعة</span>
                </span>
                <span class="text-[10px] text-[var(--text-muted)]">التقاط أو رفع حتى 5 صور</span>
            </div>

            <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-[var(--border-color)] rounded-lg bg-white dark:bg-gray-900 cursor-pointer hover:border-[var(--primary)] transition-all text-center">
                <x-icon name="upload-cloud" size="24" class="text-[var(--primary)] mb-1" />
                <span class="text-xs font-bold text-[var(--text-main)]">اضغط لالتقاط صور من الكاميرا أو اختيار ملفات</span>
                <span class="text-[10px] text-[var(--text-muted)] mt-0.5">JPG, PNG, WebP (بحد أقصى 5 ميجابايت للصورة)</span>
                <input type="file" name="store_images[]" id="storeImagesInput" multiple accept="image/*" class="sr-only">
            </label>

            <!-- Preview Container -->
            <div id="imagePreviewContainer" class="hidden grid grid-cols-3 sm:grid-cols-5 gap-2 pt-2"></div>
        </div>

        <!-- Geolocation GPS Button -->
        <div class="p-3.5 rounded-lg bg-[var(--bg-hover)] border border-[var(--border-color)] space-y-2">
            <div class="flex items-center justify-between">
                <span class="erp-form-label font-bold text-xs flex items-center gap-1.5 mb-0">
                    <x-icon name="map-pin" size="14" class="text-rose-500" />
                    <span>الموقع الجغرافي للمحل (GPS)</span>
                </span>
                <span id="gpsStatusText" class="text-[10px] text-[var(--text-muted)] font-medium">غير محدد</span>
            </div>

            <button type="button" id="gpsBtn" class="erp-btn erp-btn-secondary w-full py-2 text-xs font-bold justify-center">
                <x-icon name="navigation" size="14" class="text-[var(--primary)]" />
                <span>التقاط موقع المحل الحالي من الـ GPS</span>
            </button>

            <input type="hidden" name="latitude" id="latitudeInput" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitudeInput" value="{{ old('longitude') }}">
        </div>

        <div>
            <label class="erp-form-label font-bold text-xs mb-1">ملاحظات إضافية عن العميل والمكان</label>
            <textarea name="notes" rows="2" placeholder="مواعيد التواجد، تفضيلات البضاعة، طريقة السداد، ملاحظات المندوب..."
                class="erp-textarea text-xs sm:text-sm">{{ old('notes') }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="erp-btn erp-btn-primary w-full py-3 text-sm font-bold justify-center shadow-xs">
                <x-icon name="send" size="16" />
                <span>إرسال طلب تسجيل العميل للاعتماد</span>
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

        // Store Images Preview
        const storeImagesInput = document.getElementById('storeImagesInput');
        const previewContainer = document.getElementById('imagePreviewContainer');

        if (storeImagesInput && previewContainer) {
            storeImagesInput.addEventListener('change', function(e) {
                previewContainer.innerHTML = '';
                const files = Array.from(e.target.files);

                if (files.length === 0) {
                    previewContainer.classList.add('hidden');
                    return;
                }

                previewContainer.classList.remove('hidden');

                files.forEach((file, index) => {
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        const div = document.createElement('div');
                        div.className = 'relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 shadow-xs group';
                        div.innerHTML = `
                            <img src="${evt.target.result}" alt="صورة ${index + 1}" class="w-full h-full object-cover">
                            <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-black/60 text-white">${index + 1}</span>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }
    });
</script>
@endpush
@endsection
