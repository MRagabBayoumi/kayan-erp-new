@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="{{ $product->name }}" :breadcrumbs="['المنتجات والأصناف' => route('products.index'), $product->sku => '#']">
        <x-slot:actions>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" onclick="directPrintFromShow()" class="erp-btn erp-btn-secondary" style="color: var(--primary);" title="طباعة فورية للملصق (38×25 مم) بدون نوافذ">
                    <span>🖨️</span>
                    <span>طباعة ملصق الباركود</span>
                </button>
                <div style="display: inline-flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 2px;" title="عدد النسخ المطلوبة">
                    <button type="button" onclick="adjustShowQty(-1)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 2px 8px; font-weight: bold; font-size: 14px;">−</button>
                    <input type="number" id="showLabelQty" value="1" min="1" max="500" class="font-numeric" style="width: 44px; text-align: center; padding: 2px; font-weight: bold; border: none; background: transparent; color: var(--text-main);" title="عدد النسخ">
                    <button type="button" onclick="adjustShowQty(1)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 2px 8px; font-weight: bold; font-size: 14px;">+</button>
                </div>
            </div>
            <a href="{{ route('products.edit', $product) }}" class="erp-btn erp-btn-primary">
                <span>✏️</span>
                <span>تعديل الصنف</span>
            </a>
            <a href="{{ route('products.index') }}" class="erp-btn erp-btn-secondary">
                <span>←</span>
                <span>العودة للقائمة</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            @if($product->is_active)
                <span class="erp-badge erp-badge-success" style="padding: 4px 10px; font-size: 12px;">
                    <span>✓</span>
                    <span>الحالة: نشط</span>
                </span>
            @else
                <span class="erp-badge erp-badge-danger" style="padding: 4px 10px; font-size: 12px;">
                    <span>✕</span>
                    <span>الحالة: معطل</span>
                </span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 20px;">
        <!-- Left Column: Product Image & Barcode Card -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="erp-card" style="text-align: center; padding: 20px;">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-width: 100%; max-height: 240px; object-fit: contain; margin: 0 auto; border-radius: var(--border-radius-sm);">
                @else
                    <div style="width: 100%; height: 180px; background-color: var(--bg-surface-alt); border-radius: var(--border-radius-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px dashed var(--border-color);">
                        <span style="font-size: 48px; opacity: 0.4;">💍</span>
                        <small style="color: var(--text-muted); margin-top: 8px;">لا توجد صورة مسجلة</small>
                    </div>
                @endif

                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <small style="display: block; color: var(--text-muted); margin-bottom: 8px;">ملصق الباركود الحراري (38×25 مم):</small>
                    <div style="display: inline-block; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); padding: 4px; max-width: 100%;">
                        {!! $completeLabelSvg !!}
                    </div>

                    <div style="margin-top: 12px;">
                        <button type="button" onclick="directPrintFromShow()" class="erp-btn erp-btn-primary" style="width: 100%; justify-content: center; font-size: 13px;" title="طباعة فورية على الطابعة بدون نوافذ">
                            <span>🖨️ طباعة فورية على الطابعة</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Delete Action Card -->
            <div class="erp-card" style="padding: 16px;">
                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('تأكيد حذف المنتج نهائياً من النظام؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="erp-btn erp-btn-ghost" style="width: 100%; color: var(--danger); justify-content: center;">
                        <span>🗑️ حذف هذا المنتج</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Product Specifications & Commercial Data -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Specs Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <span>📋</span>
                        <span>المواصفات والبيانات التجارية</span>
                    </div>
                    <span class="font-numeric" style="font-weight: 700; color: var(--primary); font-size: 14px;">
                        {{ $product->sku }}
                    </span>
                </div>
                <div class="erp-card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div>
                            <small style="color: var(--text-muted); display: block; font-size: 11px;">اسم المنتج:</small>
                            <strong style="color: var(--text-main); font-size: 15px;">{{ $product->name }}</strong>
                        </div>

                        <div>
                            <small style="color: var(--text-muted); display: block; font-size: 11px;">التصنيف:</small>
                            <span class="erp-badge erp-badge-neutral" style="margin-top: 4px;">
                                {{ $product->category?->name ?? 'غير محدد' }}
                            </span>
                        </div>

                        <div>
                            <small style="color: var(--text-muted); display: block; font-size: 11px;">وحدة القياس:</small>
                            <strong style="color: var(--text-main); font-size: 14px;">{{ $product->unit }}</strong>
                        </div>

                        <div>
                            <small style="color: var(--text-muted); display: block; font-size: 11px;">حد تنبيه النواقص:</small>
                            <span class="font-numeric" style="font-weight: 700; color: var(--warning);">{{ $product->min_stock_alert }} {{ $product->unit }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Profitability Breakdown -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <span>💰</span>
                        <span>هيكل التسعير وهامش الربح</span>
                    </div>
                </div>
                <div class="erp-card-body">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center;">
                        <!-- Cost Price -->
                        <div style="padding: 14px; background: var(--bg-surface-alt); border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                            <small style="color: var(--text-muted); display: block; font-size: 11px; margin-bottom: 4px;">سعر التكلفة (استيراد الصين)</small>
                            <div class="font-numeric" style="font-size: 18px; font-weight: 700; color: var(--text-main);">
                                {{ number_format((float)$product->cost_price, 2) }}
                                <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">ج.م</span>
                            </div>
                        </div>

                        <!-- Wholesale Price -->
                        <div style="padding: 14px; background: var(--success-soft); border-radius: var(--border-radius-sm); border: 1px solid var(--success-border);">
                            <small style="color: var(--success-text); display: block; font-size: 11px; margin-bottom: 4px;">سعر الجملة لتجار الإكسسوارات</small>
                            <div class="font-numeric" style="font-size: 20px; font-weight: 800; color: var(--success-text);">
                                {{ number_format((float)$product->wholesale_price, 2) }}
                                <span style="font-size: 11px; font-weight: normal;">ج.م</span>
                            </div>
                            @php
                                $wholesaleProfit = (float)$product->wholesale_price - (float)$product->cost_price;
                                $wholesaleMargin = (float)$product->cost_price > 0 ? ($wholesaleProfit / (float)$product->cost_price) * 100 : 0;
                            @endphp
                            <small class="font-numeric" style="display: block; margin-top: 4px; font-size: 11px; color: var(--success-text);">
                                هامش الربح: +{{ number_format($wholesaleProfit, 2) }} ج.م ({{ number_format($wholesaleMargin, 1) }}%)
                            </small>
                        </div>

                        <!-- Retail Price -->
                        <div style="padding: 14px; background: var(--bg-surface-alt); border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                            <small style="color: var(--text-muted); display: block; font-size: 11px; margin-bottom: 4px;">سعر بيع التجزئة المقترح</small>
                            <div class="font-numeric" style="font-size: 18px; font-weight: 700; color: var(--text-main);">
                                {{ number_format((float)$product->retail_price, 2) }}
                                <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">ج.م</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Barcode Modal (38x15mm Xprinter XP-233B) -->
    <div id="printBarcodeModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); align-items: center; justify-content: center; padding: 16px;">
        <div class="erp-card" style="width: 100%; max-width: 480px; padding: 0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <!-- Modal Header -->
            <div class="erp-card-header" style="background: var(--bg-surface-alt); border-bottom: 1px solid var(--border-color); padding: 12px 18px;">
                <div class="erp-card-title" style="font-size: 15px;">
                    <span>🖨️</span>
                    <span>طباعة ملصق الباركود الحراري</span>
                </div>
                <button type="button" onclick="closePrintModal()" class="erp-btn erp-btn-ghost erp-btn-icon" style="color: var(--text-muted);" title="إغلاق">
                    <span>✕</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="erp-card-body" style="padding: 20px;">
                <!-- Physical Label Live Preview (38x15mm ratio) -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="display: inline-block; background: #f8fafc; border: 1px dashed var(--border-color); border-radius: 8px; padding: 16px;">
                        <small style="display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">
                            معاينة الملصق بمقاس <strong>38×15 مم</strong> (Xprinter XP-233B):
                        </small>
                        <div style="width: 260px; height: 102px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); padding: 6px 10px 4px 10px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; margin: 0 auto;">
                            <!-- Product Name -->
                            <div style="font-size: 11px; font-weight: 700; color: #000000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: center; direction: rtl; font-family: 'Cairo', sans-serif;">
                                {{ $product->name }}
                            </div>
                            <!-- Barcode SVG -->
                            <div style="width: 90%; height: 50px; display: flex; align-items: center; justify-content: center; margin: 2px 0;">
                                {!! $barcodeSvg !!}
                            </div>
                            <!-- ITEM-NO: SKU -->
                            <div style="font-size: 9.5px; font-weight: 700; color: #000000; letter-spacing: 0.5px; font-family: monospace; text-align: center;">
                                ITEM-NO: {{ $product->sku }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quantity Selection -->
                <div style="margin-bottom: 16px;">
                    <label for="labelQuantity" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">
                        عدد الملصقات المطلوب طباعتها:
                    </label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" onclick="adjustQty(-1)" class="erp-btn erp-btn-secondary" style="padding: 6px 14px; font-size: 16px; font-weight: bold;">−</button>
                        <input type="number" id="labelQuantity" value="1" min="1" max="500" class="erp-input font-numeric" style="text-align: center; font-size: 16px; font-weight: 700; width: 100px;">
                        <button type="button" onclick="adjustQty(1)" class="erp-btn erp-btn-secondary" style="padding: 6px 14px; font-size: 16px; font-weight: bold;">+</button>
                        
                        <!-- Quick Quantity Pills -->
                        <div style="display: flex; gap: 4px; margin-right: 8px; flex-wrap: wrap;">
                            <button type="button" onclick="setQty(1)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 8px; font-size: 11px;">1</button>
                            <button type="button" onclick="setQty(5)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 8px; font-size: 11px;">5</button>
                            <button type="button" onclick="setQty(10)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 8px; font-size: 11px;">10</button>
                            <button type="button" onclick="setQty(20)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 8px; font-size: 11px;">20</button>
                            <button type="button" onclick="setQty(50)" class="erp-btn erp-btn-ghost font-numeric" style="padding: 4px 8px; font-size: 11px;">50</button>
                        </div>
                    </div>
                </div>

                <!-- Notice -->
                <div style="font-size: 11.5px; color: var(--text-muted); background: var(--bg-surface-alt); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                    ℹ️ يتم إرسال أمر الطباعة بمقاس 38×15 مم المخصص لطابعات الباركود الحرارية (Xprinter XP-233B).
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closePrintModal()" class="erp-btn erp-btn-secondary">
                        <span>إلغاء</span>
                    </button>
                    <button type="button" onclick="openPrintPreview(false)" class="erp-btn erp-btn-secondary" title="فتح صفحة المعاينة بدون طباعة فورية">
                        <span>🔍 معاينة</span>
                    </button>
                    <button type="button" onclick="openPrintPreview(true)" class="erp-btn erp-btn-primary">
                        <span>🖨️ طباعة الآن</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden iframe for silent printing if preferred -->
    <iframe id="printFrame" style="display: none;"></iframe>

    <script>
        function openPrintModal() {
            var modal = document.getElementById('printBarcodeModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closePrintModal() {
            var modal = document.getElementById('printBarcodeModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function adjustShowQty(delta) {
            var input = document.getElementById('showLabelQty');
            if (input) {
                var current = parseInt(input.value, 10) || 1;
                input.value = Math.max(1, Math.min(500, current + delta));
            }
        }

        function showPrintToast(msg) {
            var toast = document.getElementById('printNotificationToast');
            var msgEl = document.getElementById('printToastMessage');
            if (toast && msgEl) {
                msgEl.innerText = msg;
                toast.style.display = 'flex';
                clearTimeout(window.__toastTimeout);
                window.__toastTimeout = setTimeout(function() {
                    toast.style.display = 'none';
                }, 2800);
            }
        }

        function directPrintFromShow() {
            var qtyInput = document.getElementById('showLabelQty');
            var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
            showPrintToast('جاري إرسال الملصق مباشرة إلى طابعة الباركود...');
            var iframe = document.getElementById('directPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'directPrintIframe';
                iframe.name = 'directPrintIframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = '{{ route("products.print-label", $product) }}?quantity=' + qty + '&autoprint=1';
        }

        function adjustQty(delta) {
            var input = document.getElementById('labelQuantity');
            if (input) {
                var current = parseInt(input.value, 10) || 1;
                var updated = Math.max(1, Math.min(500, current + delta));
                input.value = updated;
            }
        }

        function setQty(val) {
            var input = document.getElementById('labelQuantity');
            if (input) {
                input.value = val;
            }
        }

        function openPrintPreview(autoPrint) {
            var qtyInput = document.getElementById('labelQuantity');
            var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
            var url = '{{ route("products.print-label", $product) }}?quantity=' + qty;
            if (autoPrint) {
                url += '&autoprint=1';
            }
            window.open(url, '_blank');
            closePrintModal();
        }

        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePrintModal();
            }
        });
    </script>

    <!-- Hidden Iframe for Direct Silent Printing -->
    <iframe id="directPrintIframe" name="directPrintIframe" style="display: none; position: fixed; width: 0; height: 0; border: 0; opacity: 0; pointer-events: none;"></iframe>

    <!-- Toast Notification for Direct Print Feedback -->
    <div id="printNotificationToast" style="display: none; position: fixed; bottom: 24px; left: 24px; z-index: 99999; background: #0f172a; color: #ffffff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); font-size: 13.5px; font-weight: 600; align-items: center; gap: 10px;">
        <span style="font-size: 18px;">🖨️</span>
        <span id="printToastMessage">جاري إرسال الملصق للطباعة...</span>
    </div>
@endsection

