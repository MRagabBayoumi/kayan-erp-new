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

    @vite(['resources/css/print/thermal-label.css'])

    <style>
        /* Dynamic dimensions configured per user / label specification */
        .thermal-label {
            width: {{ $widthMm ?? 38 }}mm;
            height: {{ $heightMm ?? 25 }}mm;
            min-width: {{ $widthMm ?? 38 }}mm;
            max-width: {{ $widthMm ?? 38 }}mm;
            min-height: {{ $heightMm ?? 25 }}mm;
            max-height: {{ $heightMm ?? 25 }}mm;
        }

        .thermal-label svg {
            width: {{ $widthMm ?? 38 }}mm;
            height: {{ $heightMm ?? 25 }}mm;
        }

        @page {
            size: {{ $widthMm ?? 38 }}mm {{ $heightMm ?? 25 }}mm;
            margin: 0mm !important;
        }

        @media print {
            html, body {
                width: {{ $widthMm ?? 38 }}mm !important;
                height: {{ $heightMm ?? 25 }}mm !important;
            }

            .thermal-label {
                width: {{ $widthMm ?? 38 }}mm !important;
                height: {{ $heightMm ?? 25 }}mm !important;
                max-width: {{ $widthMm ?? 38 }}mm !important;
                max-height: {{ $heightMm ?? 25 }}mm !important;
            }

            .thermal-label svg {
                width: {{ $widthMm ?? 38 }}mm !important;
                height: {{ $heightMm ?? 25 }}mm !important;
                max-width: {{ $widthMm ?? 38 }}mm !important;
                max-height: {{ $heightMm ?? 25 }}mm !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN-ONLY CONTROL TOOLBAR -->
    <header class="preview-toolbar no-print">
        <div class="preview-info">
            <span class="preview-title" style="display: inline-flex; align-items: center; gap: 6px;">
                <x-icon name="printer" size="16" />
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
                <a href="{{ route('settings.index', ['tab' => 'barcode']) }}" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="تعديل مقاسات وإعدادات الباركود">
                    <x-icon name="settings" size="13" />
                    <span>إعدادات المقاسات</span>
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
            <button onclick="window.print()" class="btn btn-primary" id="printButton" style="display: inline-flex; align-items: center; gap: 6px;">
                <x-icon name="printer" size="16" />
                <span>طباعة الآن</span>
            </button>
            <button onclick="window.close(); if(!window.closed) { window.history.back(); }" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <x-icon name="x" size="14" />
                <span>إغلاق</span>
            </button>
        </div>
    </header>

    <!-- SCREEN-ONLY INSTRUCTIONS FOR THERMAL PRINTER SETUP -->
    <div class="preview-tips no-print">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                <x-icon name="info" size="14" />
                <span>نصيحة للطباعة:</span>
            </span>
            <span>في نافذة الطابعة، اختر طابعة <strong>Xprinter XP-233B</strong> بمقاس <strong>{{ $widthMm ?? 38 }}x{{ $heightMm ?? 25 }}mm</strong> والهوامش على <strong>None (بدون هوامش)</strong>.</span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; display:inline-flex; align-items:center; color:#1e40af;" title="إغلاق التنبيه">
            <x-icon name="x" size="14" />
        </button>
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
