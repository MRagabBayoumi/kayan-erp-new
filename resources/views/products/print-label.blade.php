<!DOCTYPE html>
<html lang="ar" dir="rtl" data-size="{{ $size ?? '38x25' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'طباعة ملصقات الباركود - كيان ERP' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/print/thermal-label.css', 'resources/js/pages/print-label.js'])
</head>
<body data-size="{{ $size ?? '38x25' }}" data-auto-print="{{ !empty($autoPrint) ? 'true' : 'false' }}">

    <!-- SCREEN-ONLY CONTROL TOOLBAR -->
    <header class="preview-toolbar no-print">
        <div class="preview-info">
            <span class="preview-title preview-title-wrap">
                <x-icon name="printer" size="16" />
                <span>طباعة ملصقات الباركود</span>
            </span>
            <span class="preview-badge preview-badge-primary">
                <span>المقاس: {{ $widthMm ?? 38 }}×{{ $heightMm ?? 25 }} مم (Xprinter XP-233B)</span>
            </span>
            <div class="preview-btn-group">
                <a href="{{ request()->fullUrlWithQuery(['size' => '38x25']) }}" class="btn {{ ($size ?? '38x25') === '38x25' ? 'btn-primary' : 'btn-secondary' }} btn-preset-size">
                    38×25 مم (الافتراضي)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['size' => '38x15']) }}" class="btn {{ ($size ?? '38x25') === '38x15' ? 'btn-primary' : 'btn-secondary' }} btn-preset-size">
                    38×15 مم
                </a>
                <a href="{{ route('settings.index', ['tab' => 'barcode']) }}" class="btn btn-secondary btn-preset-settings" title="تعديل مقاسات وإعدادات الباركود">
                    <x-icon name="settings" size="13" />
                    <span>إعدادات المقاسات</span>
                </a>
            </div>
            <span class="preview-badge">
                <span>الأصناف: {{ count($items) }}</span>
            </span>
            <span class="preview-badge preview-badge-qty">
                <span>الملصقات: {{ $totalQuantity }} ملصق</span>
            </span>
        </div>

        <div class="preview-actions">
            <button type="button" data-action="print-now" class="btn btn-primary btn-inline-icon" id="printButton">
                <x-icon name="printer" size="16" />
                <span>طباعة الآن</span>
            </button>
            <button type="button" data-action="close-window" class="btn btn-secondary btn-inline-icon">
                <x-icon name="x" size="14" />
                <span>إغلاق</span>
            </button>
        </div>
    </header>

    <!-- SCREEN-ONLY INSTRUCTIONS FOR THERMAL PRINTER SETUP -->
    <div class="preview-tips no-print">
        <div class="preview-tips-content">
            <span class="preview-tips-title">
                <x-icon name="info" size="14" />
                <span>نصيحة للطباعة:</span>
            </span>
            <span>في نافذة الطابعة، اختر طابعة <strong>Xprinter XP-233B</strong> بمقاس <strong>{{ $widthMm ?? 38 }}x{{ $heightMm ?? 25 }}mm</strong> والهوامش على <strong>None (بدون هوامش)</strong>.</span>
        </div>
        <button type="button" data-action="dismiss-tip" class="preview-tips-close" title="إغلاق التنبيه">
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

</body>
</html>
