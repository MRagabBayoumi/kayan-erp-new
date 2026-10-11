@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="إعدادات الشركة والنظام" :breadcrumbs="['الإدارة' => '#', $activeTab === 'barcode' ? 'إعدادات الباركود' : 'بيانات الشركة' => '#']">
        <x-slot:actions>
            @if($activeTab === 'barcode')
                <button type="submit" form="barcodeSettingsForm" class="erp-btn erp-btn-primary">
                    <span>💾</span>
                    <span>حفظ إعدادات الباركود</span>
                </button>
            @else
                <button type="submit" form="settingsForm" class="erp-btn erp-btn-primary">
                    <span>💾</span>
                    <span>حفظ الإعدادات</span>
                </button>
            @endif
            <a href="{{ url('/') }}" class="erp-btn erp-btn-secondary">
                <span>←</span>
                <span>العودة للرئيسية</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            @if($settings->isMaintenance())
                <span class="erp-badge erp-badge-warning" style="padding: 4px 10px; font-size: 12px;">
                    <span>⚠️</span>
                    <span>حالة البرنامج: صيانة</span>
                </span>
            @else
                <span class="erp-badge erp-badge-success" style="padding: 4px 10px; font-size: 12px;">
                    <span>✓</span>
                    <span>حالة البرنامج: نشط</span>
                </span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Flash Messages -->
    @if(session('status') === 'success')
        <div class="erp-alert erp-alert-success" role="alert" style="margin-bottom: 16px;">
            <span style="font-size: 16px;">✓</span>
            <div>{{ session('message', 'تم حفظ الإعدادات بنجاح.') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 16px;">
            <span style="font-size: 16px;">⚠️</span>
            <div>
                <strong>يرجى تصحيح الأخطاء التالية قبل المتابعة:</strong>
                <ul style="margin: 6px 20px 0; font-size: 12px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Settings Navigation Tabs (Matching Desktop App Tabs Layout) -->
    <div class="erp-card" style="padding: 6px; margin-bottom: 20px; background: var(--bg-surface-alt); border-bottom: 2px solid var(--border-color);">
        <nav class="erp-settings-tab-bar" style="display: flex; gap: 6px; flex-wrap: wrap;">
            <!-- Tab 1: Company Info -->
            <a href="{{ route('settings.index', ['tab' => 'company']) }}" 
               class="erp-tab-link {{ $activeTab === 'company' ? 'active' : '' }}">
                <span>🏢</span>
                <span>بيانات وهوية الشركة</span>
            </a>

            <!-- Tab 2: Barcode Settings -->
            <a href="{{ route('settings.index', ['tab' => 'barcode']) }}" 
               class="erp-tab-link {{ $activeTab === 'barcode' ? 'active' : '' }}">
                <span>🏷️</span>
                <span>إعدادات الباركود</span>
            </a>

            <!-- Future Tabs from Legacy Software -->
            <span class="erp-tab-link disabled" title="مرحلة قادمة في المخطط">
                <span>🧾</span>
                <span>إعدادات الفاتورة</span>
                <span class="erp-badge erp-badge-neutral" style="font-size: 10px; padding: 2px 6px;">قريباً</span>
            </span>

            <span class="erp-tab-link disabled" title="مرحلة قادمة في المخطط">
                <span>🖨️</span>
                <span>إعدادات الفاتورة - الطابعة الثانية</span>
                <span class="erp-badge erp-badge-neutral" style="font-size: 10px; padding: 2px 6px;">قريباً</span>
            </span>

            <span class="erp-tab-link disabled" title="مرحلة قادمة في المخطط">
                <span>📊</span>
                <span>إعدادات التقارير</span>
                <span class="erp-badge erp-badge-neutral" style="font-size: 10px; padding: 2px 6px;">قريباً</span>
            </span>

            <span class="erp-tab-link disabled" title="مرحلة قادمة في المخطط">
                <span>🔒</span>
                <span>صلاحيات الكاشير</span>
                <span class="erp-badge erp-badge-neutral" style="font-size: 10px; padding: 2px 6px;">قريباً</span>
            </span>
        </nav>
    </div>

    @if($activeTab === 'barcode')
        <!-- =========================================================
             TAB 2: BARCODE PRINTER SETTINGS
             ========================================================= -->
        <div style="display: grid; grid-template-columns: minmax(400px, 1fr) 340px; gap: 20px; align-items: start;">
            <!-- Form Left / Main Column -->
            <form id="barcodeSettingsForm" action="{{ route('settings.barcode.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Card 1: Printer Hardware & Dimension Specifications -->
                <div class="erp-card" style="margin-bottom: 20px;">
                    <div class="erp-card-header">
                        <div class="erp-card-title">
                            <span>🖨️</span>
                            <span>أبعاد وملفات طابعة الباركود الحرارية</span>
                        </div>
                        <small style="color: var(--text-muted);">تطابق إعدادات طابعة Xprinter XP-233B وبكرات الاستيكر</small>
                    </div>
                    <div class="erp-card-body">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            <!-- 1. Barcode Prefix -->
                            <div class="erp-form-group">
                                <label for="barcode_prefix" class="erp-form-label">
                                    <span>بادئة الباركود</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="barcode_prefix"
                                    name="barcode_prefix"
                                    class="erp-input font-numeric @error('barcode_prefix') is-invalid @enderror"
                                    value="{{ old('barcode_prefix', $barcodeSettings->barcode_prefix) }}"
                                    placeholder="02"
                                    maxlength="10"
                                    required
                                >
                                <span class="erp-form-hint">الرقم التعريفي الذي تبدأ به الأكواد المولدة آلياً (مثل 02).</span>
                                @error('barcode_prefix')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 2. Printer Name -->
                            <div class="erp-form-group">
                                <label for="printer_name" class="erp-form-label">
                                    <span>اسم الطابعة</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="printer_name"
                                    name="printer_name"
                                    class="erp-input @error('printer_name') is-invalid @enderror"
                                    value="{{ old('printer_name', $barcodeSettings->printer_name) }}"
                                    placeholder="Xprinter XP-233B"
                                    required
                                >
                                <span class="erp-form-hint">اسم الطابعة الحرارية المعرفة على الجهاز.</span>
                                @error('printer_name')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 3. Sticker Width (mm) -->
                            <div class="erp-form-group">
                                <label for="label_width_mm" class="erp-form-label">
                                    <span>عرض الاستيكر (مم)</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="label_width_mm"
                                    name="label_width_mm"
                                    class="erp-input font-numeric @error('label_width_mm') is-invalid @enderror"
                                    value="{{ old('label_width_mm', $barcodeSettings->label_width_mm) }}"
                                    min="10"
                                    max="150"
                                    required
                                >
                                <span class="erp-form-hint">العرض الفعلي للملصق (المقاس الشائع: 38 مم).</span>
                                @error('label_width_mm')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 4. Sticker Height (mm) -->
                            <div class="erp-form-group">
                                <label for="label_height_mm" class="erp-form-label">
                                    <span>ارتفاع الاستيكر (مم)</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="label_height_mm"
                                    name="label_height_mm"
                                    class="erp-input font-numeric @error('label_height_mm') is-invalid @enderror"
                                    value="{{ old('label_height_mm', $barcodeSettings->label_height_mm) }}"
                                    min="10"
                                    max="150"
                                    required
                                >
                                <span class="erp-form-hint">طول/ارتفاع الملصق باتجاه السحب (25 مم أو 15 مم).</span>
                                @error('label_height_mm')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 5. Barcode Width (px) -->
                            <div class="erp-form-group">
                                <label for="barcode_width_px" class="erp-form-label">
                                    <span>عرض صورة الباركود (بكسل)</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="barcode_width_px"
                                    name="barcode_width_px"
                                    class="erp-input font-numeric @error('barcode_width_px') is-invalid @enderror"
                                    value="{{ old('barcode_width_px', $barcodeSettings->barcode_width_px) }}"
                                    min="50"
                                    max="1000"
                                    required
                                >
                                <span class="erp-form-hint">العرض الأقصى للباركود (المثالي: 260 بكسل).</span>
                                @error('barcode_width_px')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 6. Barcode Height (px) -->
                            <div class="erp-form-group">
                                <label for="barcode_height_px" class="erp-form-label">
                                    <span>ارتفاع صورة الباركود (بكسل)</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="barcode_height_px"
                                    name="barcode_height_px"
                                    class="erp-input font-numeric @error('barcode_height_px') is-invalid @enderror"
                                    value="{{ old('barcode_height_px', $barcodeSettings->barcode_height_px) }}"
                                    min="15"
                                    max="300"
                                    required
                                >
                                <span class="erp-form-hint">ارتفاع خطوط الباركود (المثالي: 80 لـ 25مم، أو 30 لـ 15مم).</span>
                                @error('barcode_height_px')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 7. Product Font Size -->
                            <div class="erp-form-group">
                                <label for="product_font_size" class="erp-form-label">
                                    <span>حجم خط اسم المنتج</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="product_font_size"
                                    name="product_font_size"
                                    class="erp-input font-numeric @error('product_font_size') is-invalid @enderror"
                                    value="{{ old('product_font_size', $barcodeSettings->product_font_size) }}"
                                    min="8"
                                    max="30"
                                    required
                                >
                                <span class="erp-form-hint">حجم خط اسم الصنف على الملصق (المثالي: 14 إلى 17).</span>
                                @error('product_font_size')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 8. Details Font Size -->
                            <div class="erp-form-group">
                                <label for="details_font_size" class="erp-form-label">
                                    <span>حجم خط تفاصيل الباركود</span>
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="details_font_size"
                                    name="details_font_size"
                                    class="erp-input font-numeric @error('details_font_size') is-invalid @enderror"
                                    value="{{ old('details_font_size', $barcodeSettings->details_font_size) }}"
                                    min="6"
                                    max="24"
                                    required
                                >
                                <span class="erp-form-hint">حجم خط كود الصنف والسعر واسم الشركة (المثالي: 8 إلى 11).</span>
                                @error('details_font_size')
                                    <span class="erp-error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Presets Quick Bar -->
                        <div style="margin-top: 16px; padding: 12px; background: var(--bg-surface-alt); border-radius: var(--border-radius-sm); border: 1px dashed var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">⚡ قوالب جاهزة سريعة للأبعاد:</span>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="applyPreset(38, 25, 260, 80, 14, 8)" class="erp-btn erp-btn-secondary" style="font-size: 11.5px; padding: 4px 10px;">
                                    <span>بكرة 38×25 مم (الافتراضي)</span>
                                </button>
                                <button type="button" onclick="applyPreset(38, 15, 260, 40, 12, 7)" class="erp-btn erp-btn-secondary" style="font-size: 11.5px; padding: 4px 10px;">
                                    <span>بكرة 38×15 مم (صغير)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Checkbox Display Elements on the Sticker -->
                <div class="erp-card" style="margin-bottom: 20px;">
                    <div class="erp-card-header">
                        <div class="erp-card-title">
                            <span>☑️</span>
                            <span>خيارات عناصر الطباعة الظاهرة على الاستيكر</span>
                        </div>
                        <small style="color: var(--text-muted);">حدد ما ترغب في إظهاره أو إخفائه على ملصق الباركود</small>
                    </div>
                    <div class="erp-card-body">
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <!-- Checkbox 1: Company Name -->
                            <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: var(--text-main);">
                                    <input
                                        type="checkbox"
                                        id="show_company_name"
                                        name="show_company_name"
                                        value="1"
                                        {{ old('show_company_name', $barcodeSettings->show_company_name) ? 'checked' : '' }}
                                        style="width: 18px; height: 18px; accent-color: var(--primary);"
                                    >
                                    <span>إظهار اسم الشركة أعلى الاستيكر</span>
                                </label>
                                <div style="margin-top: 8px; margin-inline-start: 28px;">
                                    <input
                                        type="text"
                                        id="company_name_override"
                                        name="company_name_override"
                                        class="erp-input"
                                        value="{{ old('company_name_override', $barcodeSettings->company_name_override ?? $settings->company_name) }}"
                                        placeholder="شركة كيان للتجارة والتوزيع"
                                        style="font-size: 12.5px;"
                                    >
                                    <span class="erp-form-hint">الاسم المطبوع على الاستيكر (افتراضياً: اسم الشركة من الإعدادات العامة).</span>
                                </div>
                            </div>

                            <!-- Checkbox 2: Product Name -->
                            <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: var(--text-main);">
                                    <input
                                        type="checkbox"
                                        id="show_product_name"
                                        name="show_product_name"
                                        value="1"
                                        {{ old('show_product_name', $barcodeSettings->show_product_name) ? 'checked' : '' }}
                                        style="width: 18px; height: 18px; accent-color: var(--primary);"
                                    >
                                    <span>إظهار اسم الصنف / المنتج على الاستيكر</span>
                                </label>
                                <small style="display: block; margin-inline-start: 28px; color: var(--text-muted); font-size: 11.5px; margin-top: 4px;">
                                    يطبع اسم الصنف (مثل: فرشاة شعر / طقم لؤلؤ) بالخط العربي العريض.
                                </small>
                            </div>

                            <!-- Checkbox 3: Barcode Bars -->
                            <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: var(--text-main);">
                                    <input
                                        type="checkbox"
                                        id="show_barcode_bars"
                                        name="show_barcode_bars"
                                        value="1"
                                        {{ old('show_barcode_bars', $barcodeSettings->show_barcode_bars) ? 'checked' : '' }}
                                        style="width: 18px; height: 18px; accent-color: var(--primary);"
                                    >
                                    <span>إظهار خطوط الباركود (Code 128)</span>
                                </label>
                                <small style="display: block; margin-inline-start: 28px; color: var(--text-muted); font-size: 11.5px; margin-top: 4px;">
                                    الخطوط الرأسية القابلة للمسح الضوئي بأجهزة قراءة الباركود والكاميرا.
                                </small>
                            </div>

                            <!-- Checkbox 4: Item SKU -->
                            <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: var(--text-main);">
                                    <input
                                        type="checkbox"
                                        id="show_item_sku"
                                        name="show_item_sku"
                                        value="1"
                                        {{ old('show_item_sku', $barcodeSettings->show_item_sku) ? 'checked' : '' }}
                                        style="width: 18px; height: 18px; accent-color: var(--primary);"
                                    >
                                    <span>إظهار كود ورقم الصنف (ITEM-NO / SKU)</span>
                                </label>
                                <small style="display: block; margin-inline-start: 28px; color: var(--text-muted); font-size: 11.5px; margin-top: 4px;">
                                    يطبع كود الصنف أسفل الباركود (مثل: HB-262 أو KY-NCK-001).
                                </small>
                            </div>

                            <!-- Price Option: Wholesale vs Retail -->
                            <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                <strong style="display: block; color: var(--text-main); font-size: 13.5px; margin-bottom: 8px;">
                                    💰 خيار طباعة سعر البيع (جملة ولا تجزئة):
                                </strong>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                                    <!-- None -->
                                    <label class="erp-radio-pill">
                                        <input type="radio" name="print_price_type" value="none" {{ old('print_price_type', $barcodeSettings->print_price_type) === 'none' ? 'checked' : '' }}>
                                        <span>بدون إظهار السعر</span>
                                    </label>

                                    <!-- Retail -->
                                    <label class="erp-radio-pill">
                                        <input type="radio" name="print_price_type" value="retail" {{ old('print_price_type', $barcodeSettings->print_price_type) === 'retail' ? 'checked' : '' }}>
                                        <span>سعر بيع التجزئة (قطاعي)</span>
                                    </label>

                                    <!-- Wholesale -->
                                    <label class="erp-radio-pill">
                                        <input type="radio" name="print_price_type" value="wholesale" {{ old('print_price_type', $barcodeSettings->print_price_type) === 'wholesale' ? 'checked' : '' }}>
                                        <span>سعر بيع الجملة</span>
                                    </label>

                                    <!-- Both -->
                                    <label class="erp-radio-pill">
                                        <input type="radio" name="print_price_type" value="both" {{ old('print_price_type', $barcodeSettings->print_price_type) === 'both' ? 'checked' : '' }}>
                                        <span>كلاهما (جملة وتجزئة)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Bar -->
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 10px;">
                    <button type="submit" class="erp-btn erp-btn-primary" style="padding: 10px 24px; font-size: 14px;">
                        <span>💾</span>
                        <span>حفظ إعدادات الباركود</span>
                    </button>
                    <a href="{{ url('/') }}" class="erp-btn erp-btn-secondary" style="padding: 10px 20px;">
                        <span>إلغاء</span>
                    </a>
                </div>
            </form>

            <!-- Column 2: Live Interactive Sticker Preview -->
            <div style="position: sticky; top: 70px;">
                <div class="erp-card" style="padding: 16px; text-align: center;">
                    <div class="erp-card-header" style="padding: 0 0 12px; border-bottom: 1px solid var(--border-color); margin-bottom: 14px;">
                        <div class="erp-card-title" style="font-size: 14px;">
                            <span>👁️</span>
                            <span>معاينة حية للملصق</span>
                        </div>
                        <span id="previewDimensionsBadge" class="erp-badge erp-badge-neutral font-numeric" style="font-size: 11px;">
                            {{ $barcodeSettings->label_width_mm }}×{{ $barcodeSettings->label_height_mm }} مم
                        </span>
                    </div>

                    <!-- Thermal Sticker Live Display Box -->
                    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 220px;">
                        <div id="liveStickerFrame" style="background: #ffffff; border: 1px solid #94a3b8; border-radius: 3px; box-shadow: 0 4px 10px rgba(0,0,0,0.12); padding: 4px; box-sizing: border-box; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: space-between; transition: all 0.2s ease;">
                            <!-- Top: Company Name -->
                            <div id="previewCompanyText" style="font-size: 10px; font-weight: 700; color: #1e293b; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                {{ $barcodeSettings->getEffectiveCompanyName() }}
                            </div>

                            <!-- Middle Top: Product Name -->
                            <div id="previewProductText" style="font-size: 14px; font-weight: 800; color: #000000; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                فرشاة شعر كوري راقية
                            </div>

                            <!-- Barcode Bars -->
                            <div id="previewBarcodeBars" style="width: 100%; display: flex; justify-content: center; margin: 4px 0;">
                                <svg id="previewBarcodeSvg" viewBox="0 0 200 40" width="85%" height="35" preserveAspectRatio="none">
                                    <g fill="#000000">
                                        <!-- Realistic Code 128 Bars Simulation -->
                                        <rect x="0" y="0" width="3" height="40"/>
                                        <rect x="5" y="0" width="2" height="40"/>
                                        <rect x="10" y="0" width="4" height="40"/>
                                        <rect x="16" y="0" width="2" height="40"/>
                                        <rect x="20" y="0" width="6" height="40"/>
                                        <rect x="28" y="0" width="3" height="40"/>
                                        <rect x="33" y="0" width="2" height="40"/>
                                        <rect x="38" y="0" width="5" height="40"/>
                                        <rect x="45" y="0" width="3" height="40"/>
                                        <rect x="50" y="0" width="2" height="40"/>
                                        <rect x="55" y="0" width="4" height="40"/>
                                        <rect x="62" y="0" width="6" height="40"/>
                                        <rect x="70" y="0" width="2" height="40"/>
                                        <rect x="75" y="0" width="4" height="40"/>
                                        <rect x="82" y="0" width="3" height="40"/>
                                        <rect x="88" y="0" width="5" height="40"/>
                                        <rect x="95" y="0" width="2" height="40"/>
                                        <rect x="100" y="0" width="6" height="40"/>
                                        <rect x="108" y="0" width="3" height="40"/>
                                        <rect x="114" y="0" width="4" height="40"/>
                                        <rect x="120" y="0" width="2" height="40"/>
                                        <rect x="125" y="0" width="5" height="40"/>
                                        <rect x="133" y="0" width="3" height="40"/>
                                        <rect x="138" y="0" width="2" height="40"/>
                                        <rect x="143" y="0" width="6" height="40"/>
                                        <rect x="151" y="0" width="4" height="40"/>
                                        <rect x="158" y="0" width="2" height="40"/>
                                        <rect x="163" y="0" width="5" height="40"/>
                                        <rect x="171" y="0" width="3" height="40"/>
                                        <rect x="176" y="0" width="2" height="40"/>
                                        <rect x="181" y="0" width="6" height="40"/>
                                        <rect x="190" y="0" width="3" height="40"/>
                                        <rect x="196" y="0" width="4" height="40"/>
                                    </g>
                                </svg>
                            </div>

                            <!-- Bottom: SKU & Price -->
                            <div id="previewBottomDetails" style="font-size: 8px; font-weight: 700; color: #000000; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                <span id="previewSkuSpan">ITEM-NO: HB-262</span>
                                <span id="previewPriceSpan" style="margin-inline-start: 6px;"></span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 12px; font-size: 11px; color: var(--text-muted);">
                        💡 تتغير المعاينة أعلاه تلقائياً بمجرد تعديل أي مقاس أو خيار.
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- =========================================================
             TAB 1: COMPANY INFORMATION & SYSTEM STATUS
             ========================================================= -->
        <form id="settingsForm" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Section A: Company Information -->
            <div class="erp-card" style="margin-bottom: 20px;">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <span>🏢</span>
                        <span>بيانات وهوية الشركة</span>
                    </div>
                    <small style="color: var(--text-muted);">المعلومات الرسمية التي تظهر في الترويسة والقوائم</small>
                </div>
                <div class="erp-card-body">
                    <div class="erp-form-grid">
                        <!-- Company Name -->
                        <div class="erp-form-group">
                            <label for="company_name" class="erp-form-label">
                                <span>اسم الشركة</span>
                                <span class="required" title="حقل إجباري">*</span>
                            </label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                class="erp-input @error('company_name') is-invalid @enderror"
                                value="{{ old('company_name', $settings->company_name) }}"
                                placeholder="مثال: Kayan Fashion Accessories"
                                required
                            >
                            <span class="erp-form-hint">يظهر اسم الشركة في الشريط الجانبي وأعلى فواتير ومستندات النظام.</span>
                            @error('company_name')
                                <span class="erp-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Company Phone Number -->
                        <div class="erp-form-group">
                            <label for="company_phone" class="erp-form-label">
                                <span>رقم الهاتف / واتساب الشركة</span>
                            </label>
                            <input
                                type="text"
                                id="company_phone"
                                name="company_phone"
                                class="erp-input font-numeric @error('company_phone') is-invalid @enderror"
                                value="{{ old('company_phone', $settings->company_phone) }}"
                                placeholder="مثال: +20 100 123 4567"
                                dir="ltr"
                                style="text-align: right;"
                            >
                            <span class="erp-form-hint">يقبل علامة + الدولية والأرقام للتواصل والمراسلات.</span>
                            @error('company_phone')
                                <span class="erp-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Company Logo Upload -->
                        <div class="erp-form-group erp-form-full">
                            <label class="erp-form-label">
                                <span>شعار الشركة (Logo)</span>
                            </label>
                            <div class="erp-logo-upload-box">
                                <!-- Preview Frame -->
                                <div class="erp-logo-preview-frame" id="logoPreviewContainer">
                                    @if($settings->logo_url)
                                        <img src="{{ $settings->logo_url }}" id="logoPreviewImg" alt="شعار الشركة">
                                    @else
                                        <span id="logoPlaceholder" style="font-size: 24px; font-weight: bold; color: var(--primary);">
                                            {{ mb_substr($settings->company_name, 0, 1) }}
                                        </span>
                                        <img src="" id="logoPreviewImg" alt="معاينة الشعار" style="display: none;">
                                    @endif
                                </div>

                                <!-- Upload Inputs & Guidelines -->
                                <div style="flex: 1;">
                                    <input
                                        type="file"
                                        id="companyLogoInput"
                                        name="company_logo"
                                        accept="image/png,image/jpeg,image/webp"
                                        class="erp-input @error('company_logo') is-invalid @enderror"
                                        style="padding: 6px 12px; height: auto;"
                                    >
                                    <div class="erp-form-hint" style="margin-top: 6px;">
                                        الصيغ المدعومة: <strong>PNG, JPG, JPEG, WebP</strong>. الحد الأقصى للحجم: <strong>2 ميجابايت</strong>.
                                        يتم تخزين الشعار بأمان في مسار التخزين العام للنظام.
                                    </div>
                                    @error('company_logo')
                                        <span class="erp-error-msg">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section B: Program Status -->
            <div class="erp-card" style="margin-bottom: 20px;">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <span>⚡</span>
                        <span>حالة وتشغيل البرنامج (Program Status)</span>
                    </div>
                    <small style="color: var(--text-muted);">التحكم في وسم وتنبيه وضع تشغيل النظام</small>
                </div>
                <div class="erp-card-body">
                    <div class="erp-form-group">
                        <label class="erp-form-label">
                            <span>اختر الحالة التشغيلية الحالية:</span>
                        </label>

                        <div class="erp-radio-cards-grid">
                            <!-- Option 1: Active -->
                            <label class="erp-radio-card {{ old('program_status', $settings->program_status) === 'active' ? 'selected' : '' }}" id="cardStatusActive">
                                <input
                                    type="radio"
                                    name="program_status"
                                    value="active"
                                    {{ old('program_status', $settings->program_status) === 'active' ? 'checked' : '' }}
                                    onchange="handleStatusChange('active')"
                                >
                                <div class="erp-radio-card-body">
                                    <div class="erp-radio-card-title">
                                        <span class="erp-badge erp-badge-success">نشط (Active)</span>
                                    </div>
                                    <div class="erp-radio-card-desc">
                                        البرنامج في وضع التشغيل الكامل اليومي، وتظهر علامة النظام النشط باللون الأخضر.
                                    </div>
                                </div>
                            </label>

                            <!-- Option 2: Maintenance -->
                            <label class="erp-radio-card {{ old('program_status', $settings->program_status) === 'maintenance' ? 'selected' : '' }}" id="cardStatusMaintenance">
                                <input
                                    type="radio"
                                    name="program_status"
                                    value="maintenance"
                                    {{ old('program_status', $settings->program_status) === 'maintenance' ? 'checked' : '' }}
                                    onchange="handleStatusChange('maintenance')"
                                >
                                <div class="erp-radio-card-body">
                                    <div class="erp-radio-card-title">
                                        <span class="erp-badge erp-badge-warning">صيانة (Maintenance)</span>
                                    </div>
                                    <div class="erp-radio-card-desc">
                                        تفعيل شارة الصيانة التوضيحية دون حظر دخول المستخدمين أو تعطيل أي من وظائف النظام.
                                    </div>
                                </div>
                            </label>
                        </div>

                        @error('program_status')
                            <span class="erp-error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Submit Bar -->
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 12px; margin-top: 10px;">
                <button type="submit" class="erp-btn erp-btn-primary" style="padding: 10px 24px; font-size: 14px;">
                    <span>💾</span>
                    <span>حفظ الإعدادات</span>
                </button>
                <a href="{{ url('/') }}" class="erp-btn erp-btn-secondary" style="padding: 10px 20px;">
                    <span>إلغاء</span>
                </a>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script>
    // Tab styling & Live preview scripts
    document.addEventListener('DOMContentLoaded', function() {
        // Live Logo Image Preview (Company Tab)
        const fileInput = document.getElementById('companyLogoInput');
        const previewImg = document.getElementById('logoPreviewImg');
        const placeholder = document.getElementById('logoPlaceholder');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (file) {
                    const objectUrl = URL.createObjectURL(file);
                    if (previewImg) {
                        previewImg.src = objectUrl;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
            });
        }

        // Live Barcode Sticker Preview (Barcode Tab)
        if (document.getElementById('barcodeSettingsForm')) {
            initBarcodeLivePreview();
        }
    });

    // Toggle card selected styling on radio change
    function handleStatusChange(status) {
        const activeCard = document.getElementById('cardStatusActive');
        const maintenanceCard = document.getElementById('cardStatusMaintenance');

        if (status === 'active') {
            activeCard?.classList.add('selected');
            maintenanceCard?.classList.remove('selected');
        } else {
            maintenanceCard?.classList.add('selected');
            activeCard?.classList.remove('selected');
        }
    }

    // Apply quick dimensions preset
    function applyPreset(width, height, barW, barH, prodF, detF) {
        setValue('label_width_mm', width);
        setValue('label_height_mm', height);
        setValue('barcode_width_px', barW);
        setValue('barcode_height_px', barH);
        setValue('product_font_size', prodF);
        setValue('details_font_size', detF);
        updateLiveSticker();
    }

    function setValue(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    }

    // Live Sticker Sync
    function initBarcodeLivePreview() {
        const inputs = [
            'label_width_mm', 'label_height_mm', 'barcode_width_px', 'barcode_height_px',
            'product_font_size', 'details_font_size', 'company_name_override',
            'show_company_name', 'show_product_name', 'show_barcode_bars', 'show_item_sku'
        ];

        inputs.forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateLiveSticker);
                el.addEventListener('change', updateLiveSticker);
            }
        });

        document.querySelectorAll('input[name="print_price_type"]').forEach(function(r) {
            r.addEventListener('change', updateLiveSticker);
        });

        updateLiveSticker();
    }

    function updateLiveSticker() {
        const frame = document.getElementById('liveStickerFrame');
        if (!frame) return;

        const widthMm = parseInt(document.getElementById('label_width_mm')?.value || 38, 10);
        const heightMm = parseInt(document.getElementById('label_height_mm')?.value || 25, 10);
        const prodFont = parseInt(document.getElementById('product_font_size')?.value || 14, 10);
        const detFont = parseInt(document.getElementById('details_font_size')?.value || 8, 10);

        const showCompany = document.getElementById('show_company_name')?.checked ?? true;
        const companyName = document.getElementById('company_name_override')?.value || 'شركة كيان للتجارة والتوزيع';
        const showProd = document.getElementById('show_product_name')?.checked ?? true;
        const showBars = document.getElementById('show_barcode_bars')?.checked ?? true;
        const showSku = document.getElementById('show_item_sku')?.checked ?? true;
        
        let priceType = 'none';
        const priceChecked = document.querySelector('input[name="print_price_type"]:checked');
        if (priceChecked) priceType = priceChecked.value;

        // Dimensions preview
        const badge = document.getElementById('previewDimensionsBadge');
        if (badge) badge.textContent = widthMm + '×' + heightMm + ' مم';

        // Scale frame to realistic screen aspect ratio (base width 240px)
        const frameWidthPx = 240;
        const frameHeightPx = Math.round((heightMm / widthMm) * frameWidthPx);
        frame.style.width = frameWidthPx + 'px';
        frame.style.height = Math.max(120, frameHeightPx) + 'px';

        // Company text
        const compEl = document.getElementById('previewCompanyText');
        if (compEl) {
            compEl.style.display = showCompany ? 'block' : 'none';
            compEl.textContent = companyName;
            compEl.style.fontSize = Math.max(9, Math.round(detFont * 1.15)) + 'px';
        }

        // Product text
        const prodEl = document.getElementById('previewProductText');
        if (prodEl) {
            prodEl.style.display = showProd ? 'block' : 'none';
            prodEl.style.fontSize = Math.max(10, prodFont) + 'px';
        }

        // Barcode bars
        const barsEl = document.getElementById('previewBarcodeBars');
        if (barsEl) {
            barsEl.style.display = showBars ? 'flex' : 'none';
        }

        // Bottom SKU & Price
        const skuSpan = document.getElementById('previewSkuSpan');
        if (skuSpan) {
            skuSpan.style.display = showSku ? 'inline' : 'none';
        }

        const priceSpan = document.getElementById('previewPriceSpan');
        if (priceSpan) {
            if (priceType === 'retail') {
                priceSpan.textContent = (showSku ? ' | ' : '') + 'السعر: 35.00 ج.م';
                priceSpan.style.display = 'inline';
            } else if (priceType === 'wholesale') {
                priceSpan.textContent = (showSku ? ' | ' : '') + 'جملة: 22.00 ج.م';
                priceSpan.style.display = 'inline';
            } else if (priceType === 'both') {
                priceSpan.textContent = (showSku ? ' | ' : '') + 'تجزئة 35 | جملة 22';
                priceSpan.style.display = 'inline';
            } else {
                priceSpan.textContent = '';
                priceSpan.style.display = 'none';
            }
        }

        const bottomEl = document.getElementById('previewBottomDetails');
        if (bottomEl) {
            bottomEl.style.display = (showSku || priceType !== 'none') ? 'block' : 'none';
            bottomEl.style.fontSize = detFont + 'px';
        }
    }
</script>

<style>
    /* Tabs Navigation Styling */
    .erp-tab-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: var(--border-radius-sm);
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        color: var(--text-secondary);
        background: transparent;
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }
    .erp-tab-link:hover:not(.disabled) {
        background: var(--bg-hover);
        color: var(--text-main);
    }
    .erp-tab-link.active {
        background: var(--bg-surface);
        color: var(--primary);
        border-color: var(--border-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .erp-tab-link.disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* Radio Pill Options */
    .erp-radio-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-main);
        transition: all 0.15s ease;
    }
    .erp-radio-pill:has(input:checked) {
        border-color: var(--primary);
        background: var(--primary-soft);
        color: var(--primary);
    }
    .erp-radio-pill input {
        accent-color: var(--primary);
    }
</style>
@endpush
