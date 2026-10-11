<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'طباعة ملصقات الباركود - كيان ERP' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* =========================================================
           SCREEN PREVIEW STYLES
           ========================================================= */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', Tahoma, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            direction: rtl;
        }

        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .preview-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .preview-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: #e2e8f0;
            color: #334155;
        }

        .preview-badge-primary {
            background: #e0e7ff;
            color: #4338ca;
        }

        .preview-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-secondary:hover {
            background: #f8fafc;
        }

        .preview-tips {
            max-width: 900px;
            margin: 16px auto 0;
            padding: 10px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            font-size: 12px;
            color: #1e40af;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .roll-stage {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 16px;
        }

        .roll-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
            padding: 24px 0;
        }

        .label-card-preview {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .label-card-preview .card-index {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #334155;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Thermal label display in browser preview (scaled up slightly for easy readability) */
        .thermal-label {
            width: {{ $widthMm ?? 38 }}mm;
            height: {{ $heightMm ?? 25 }}mm;
            min-width: {{ $widthMm ?? 38 }}mm;
            max-width: {{ $widthMm ?? 38 }}mm;
            min-height: {{ $heightMm ?? 25 }}mm;
            max-height: {{ $heightMm ?? 25 }}mm;
            background: #ffffff;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .thermal-label svg {
            width: {{ $widthMm ?? 38 }}mm;
            height: {{ $heightMm ?? 25 }}mm;
            display: block;
        }

        /* =========================================================
           PRINT MEDIA QUERIES (Exact dynamic size for Xprinter XP-233B)
           ========================================================= */
        @page {
            size: {{ $widthMm ?? 38 }}mm {{ $heightMm ?? 25 }}mm;
            margin: 0mm !important;
        }

        @media print {
            *, *::before, *::after {
                box-sizing: border-box !important;
            }

            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                width: {{ $widthMm ?? 38 }}mm !important;
                height: {{ $heightMm ?? 25 }}mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .roll-stage {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .roll-grid {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .label-card-preview {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                display: block !important;
            }

            .label-card-preview .card-index {
                display: none !important;
            }

            .thermal-label {
                width: {{ $widthMm ?? 38 }}mm !important;
                height: {{ $heightMm ?? 25 }}mm !important;
                max-width: {{ $widthMm ?? 38 }}mm !important;
                max-height: {{ $heightMm ?? 25 }}mm !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: #ffffff !important;
            }

            .thermal-label svg {
                width: {{ $widthMm ?? 38 }}mm !important;
                height: {{ $heightMm ?? 25 }}mm !important;
                max-width: {{ $widthMm ?? 38 }}mm !important;
                max-height: {{ $heightMm ?? 25 }}mm !important;
                display: block !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN-ONLY CONTROL TOOLBAR -->
    <header class="preview-toolbar no-print">
        <div class="preview-info">
            <span class="preview-title">
                <span>🖨️</span>
                <span>طباعة ملصقات الباركود</span>
            </span>
            <span class="preview-badge preview-badge-primary">
                <span>المقاس: {{ $widthMm ?? 38 }}×{{ $heightMm ?? 25 }} مم (Xprinter XP-233B)</span>
            </span>
            <div style="display: flex; gap: 4px;">
                <a href="{{ request()->fullUrlWithQuery(['size' => '38x25']) }}" class="btn {{ ($size ?? '38x25') === '38x25' ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11.5px; text-decoration: none;">
                    38×25 مم (الافتراضي)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['size' => '38x15']) }}" class="btn {{ ($size ?? '38x25') === '38x15' ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11.5px; text-decoration: none;">
                    38×15 مم
                </a>
                <a href="{{ route('settings.index', ['tab' => 'barcode']) }}" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11.5px; text-decoration: none;" title="تعديل مقاسات وإعدادات الباركود">
                    ⚙️ إعدادات المقاسات
                </a>
            </div>
            <span class="preview-badge">
                <span>الأصناف: {{ count($items) }}</span>
            </span>
            <span class="preview-badge" style="font-weight: 700; color: #166534; background: #dcfce7;">
                <span>الملصقات: {{ $totalQuantity }} ملصق</span>
            </span>
        </div>

        <div class="preview-actions">
            <button onclick="window.print()" class="btn btn-primary" id="printButton">
                <span>🖨️ طباعة الآن</span>
            </button>
            <button onclick="window.close(); if(!window.closed) { window.history.back(); }" class="btn btn-secondary">
                <span>✕ إغلاق</span>
            </button>
        </div>
    </header>

    <!-- SCREEN-ONLY INSTRUCTIONS FOR THERMAL PRINTER SETUP -->
    <div class="preview-tips no-print">
        <div>
            <strong>💡 نصيحة للطباعة:</strong>
            في نافذة الطابعة، اختر طابعة <strong>Xprinter XP-233B</strong> بمقاس <strong>{{ $widthMm ?? 38 }}x{{ $heightMm ?? 25 }}mm</strong> والهوامش على <strong>None (بدون هوامش)</strong>.
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:#1e40af;">✕</button>
    </div>

    <!-- MAIN LABELS CONTAINER -->
    <main class="roll-stage">
        <div class="roll-grid">
            @php $labelIndex = 1; @endphp
            @foreach($items as $item)
                @for($copy = 1; $copy <= $item['quantity']; $copy++)
                    <div class="label-card-preview">
                        <span class="card-index">{{ $labelIndex }}</span>
                        <div class="thermal-label">
                            {!! $item['label_svg'] !!}
                        </div>
                    </div>
                    @php $labelIndex++; @endphp
                @endfor
            @endforeach
        </div>
    </main>

    <script>
        var isPrinted = false;
        function triggerPrint() {
            if (isPrinted) return;
            isPrinted = true;
            window.focus();
            window.print();
        }

        // Auto-print if requested via parameter
        @if(!empty($autoPrint))
            window.addEventListener('load', function() {
                setTimeout(triggerPrint, 150);
            });
        @endif
    </script>
</body>
</html>
