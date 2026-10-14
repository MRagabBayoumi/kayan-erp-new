<?php

namespace App\Http\Controllers;

use App\Models\BarcodeSetting;
use App\Models\CompanySetting;
use App\Models\PrintTemplate;
use App\Services\PrintTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanySettingController extends Controller
{
    /**
     * Show the company, system, barcode printer, and print template settings.
     */
    public function index(): View
    {
        $settings = CompanySetting::getSettings();
        $barcodeSettings = BarcodeSetting::getSettings();
        $activeTab = request('tab', 'company');

        $printTemplateData = [];
        if ($activeTab === 'print_templates') {
            $service = app(PrintTemplateService::class);
            $docType = request('doc_type', PrintTemplate::DOC_SALES_INVOICE);
            $paperFormat = request('paper_format', PrintTemplate::FORMAT_A4);
            $templateId = request('template_id') ? (int) request('template_id') : null;

            if (! in_array($docType, PrintTemplateService::SUPPORTED_DOCUMENTS, true)) {
                $docType = PrintTemplate::DOC_SALES_INVOICE;
            }
            if (! in_array($paperFormat, PrintTemplateService::SUPPORTED_FORMATS, true)) {
                $paperFormat = PrintTemplate::FORMAT_A4;
            }

            $printTemplateData = [
                'currentDocType' => $docType,
                'currentPaperFormat' => $paperFormat,
                'currentTemplate' => $service->getEffectiveTemplate($docType, $paperFormat, $templateId),
                'sampleData' => $service->getPreviewSampleData($docType, $paperFormat),
                'defaults' => $service->getDefaultSettings($docType, $paperFormat),
                'templatesList' => PrintTemplate::forDocument($docType)
                    ->forFormat($paperFormat)
                    ->orderByDesc('is_default')
                    ->orderBy('name')
                    ->get(),
                'docTypeLabels' => PrintTemplate::getDocumentTypeLabels(),
                'paperFormatLabels' => PrintTemplate::getPaperFormatLabels(),
            ];
        }

        return view('settings.index', array_merge([
            'title' => 'إعدادات الشركة والنظام',
            'moduleName' => 'الإعدادات',
            'settings' => $settings,
            'barcodeSettings' => $barcodeSettings,
            'activeTab' => $activeTab,
        ], $printTemplateData));
    }

    /**
     * Update the company and system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $settings = CompanySetting::getSettings();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:35', 'regex:/^[0-9+\s\-()]+$/'],
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'program_status' => ['required', 'string', 'in:active,maintenance'],
        ], [
            'company_name.required' => 'اسم الشركة مطلوب ولا يمكن تركه فارغاً.',
            'company_name.string' => 'اسم الشركة يجب أن يكون نصاً صالحاً.',
            'company_name.max' => 'اسم الشركة يجب ألا يتجاوز 255 حرفاً.',
            'company_phone.max' => 'رقم الهاتف يجب ألا يتجاوز 35 حرفاً.',
            'company_phone.regex' => 'رقم الهاتف يحتوي على أحرف غير صالحة. يرجى إدخال أرقام صحيحة وعلامة + الدولية.',
            'company_logo.image' => 'الملف المرفوع يجب أن يكون صورة صالحة.',
            'company_logo.mimes' => 'صيغ الصور المدعومة هي: PNG, JPG, JPEG, WebP فقط.',
            'company_logo.max' => 'الحد الأقصى لحجم شعار الشركة هو 2 ميجابايت.',
            'program_status.required' => 'حالة البرنامج مطلوبة.',
            'program_status.in' => 'قيمة حالة البرنامج غير صالحة.',
        ]);

        // Handle logo upload safely
        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $extension = $file->getClientOriginalExtension();
            $filename = 'company_'.Str::random(24).'.'.$extension;

            // Store with public disk
            $storedPath = $file->storeAs('company', $filename, 'public');

            // Delete previous logo if a new one was saved successfully
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $settings->logo_path = $storedPath;
        }

        // Update fields
        $settings->company_name = $validated['company_name'];
        $settings->company_phone = $validated['company_phone'] ?? null;
        $settings->program_status = $validated['program_status'];
        $settings->save();

        return redirect()->route('settings.index')
            ->with('status', 'success')
            ->with('message', 'تم حفظ إعدادات الشركة والنظام بنجاح.');
    }

    /**
     * Update the barcode printer settings.
     */
    public function updateBarcode(Request $request): RedirectResponse
    {
        $barcodeSettings = BarcodeSetting::getSettings();

        $validated = $request->validate([
            'barcode_prefix' => ['required', 'string', 'max:10'],
            'printer_name' => ['required', 'string', 'max:100'],
            'label_width_mm' => ['required', 'integer', 'min:10', 'max:150'],
            'label_height_mm' => ['required', 'integer', 'min:10', 'max:150'],
            'barcode_width_px' => ['required', 'integer', 'min:50', 'max:1000'],
            'barcode_height_px' => ['required', 'integer', 'min:15', 'max:300'],
            'product_font_size' => ['required', 'integer', 'min:8', 'max:36'],
            'details_font_size' => ['required', 'integer', 'min:6', 'max:28'],
            'company_name_override' => ['nullable', 'string', 'max:255'],
            'print_price_type' => ['required', 'string', 'in:none,retail,wholesale,both'],
        ], [
            'barcode_prefix.required' => 'بادئة الباركود مطلوبة.',
            'printer_name.required' => 'اسم الطابعة مطلوب.',
            'label_width_mm.required' => 'عرض الاستيكر مطلوب بالملليمتر.',
            'label_width_mm.integer' => 'عرض الاستيكر يجب أن يكون رقماً صحيحاً.',
            'label_height_mm.required' => 'ارتفاع الاستيكر مطلوب بالملليمتر.',
            'label_height_mm.integer' => 'ارتفاع الاستيكر يجب أن يكون رقماً صحيحاً.',
            'barcode_width_px.required' => 'عرض صورة الباركود مطلوب بالبكسل.',
            'barcode_height_px.required' => 'ارتفاع صورة الباركود مطلوب بالبكسل.',
            'product_font_size.required' => 'حجم خط اسم المنتج مطلوب.',
            'details_font_size.required' => 'حجم خط تفاصيل الباركود مطلوب.',
            'print_price_type.in' => 'خيار طباعة السعر غير صالح.',
        ]);

        $validated['show_company_name'] = $request->boolean('show_company_name');
        $validated['show_product_name'] = $request->boolean('show_product_name');
        $validated['show_barcode_bars'] = $request->boolean('show_barcode_bars');
        $validated['show_item_sku'] = $request->boolean('show_item_sku');

        $barcodeSettings->update($validated);

        return redirect()->route('settings.index', ['tab' => 'barcode'])
            ->with('status', 'success')
            ->with('message', 'تم حفظ إعدادات طابعة الباركود بنجاح.');
    }
}
