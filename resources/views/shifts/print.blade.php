<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير إغلاق وردية كاشير - {{ $shift->shift_number }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/print/shift-receipt.css'])
</head>
<body class="shift-print-body">
    <div class="shift-receipt-container">
        <div class="shift-receipt-header">
            <h1 class="shift-receipt-title">{{ config('app.name', 'BayoSoft ERP') }}</h1>
            <div class="shift-receipt-meta">تقرير إغلاق وجرد وردية كاشير</div>
            <div class="shift-receipt-meta font-numeric">{{ $shift->shift_number }}</div>
        </div>

        <div class="shift-receipt-row">
            <span>الكاشير:</span>
            <strong>{{ $shift->user?->name }}</strong>
        </div>

        <div class="shift-receipt-row">
            <span>الفرع / المستودع:</span>
            <strong>{{ $shift->warehouse?->name }}</strong>
        </div>

        <div class="shift-receipt-row">
            <span>وقت الافتتاح:</span>
            <span class="font-numeric">{{ $shift->opened_at->format('Y-m-d H:i') }}</span>
        </div>

        <div class="shift-receipt-row">
            <span>وقت الإغلاق:</span>
            <span class="font-numeric">{{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : 'نشطة' }}</span>
        </div>

        <div class="shift-receipt-row bold">
            <span>(+) رصيد البداية:</span>
            <span class="font-numeric">{{ number_format($shift->opening_balance, 2) }} ج.م</span>
        </div>

        <div class="shift-receipt-row">
            <span>(+) المبيعات النقدية ({{ $shift->salesInvoices->count() }} فاتورة):</span>
            <span class="font-numeric">+{{ number_format($shift->cash_sales_amount, 2) }} ج.م</span>
        </div>

        <div class="shift-receipt-row">
            <span>(+) إيداعات الدرج:</span>
            <span class="font-numeric">+{{ number_format($shift->cash_additions, 2) }} ج.م</span>
        </div>

        <div class="shift-receipt-row">
            <span>(−) مسحوبات ومصروفات:</span>
            <span class="font-numeric">−{{ number_format($shift->cash_expenses, 2) }} ج.م</span>
        </div>

        <div class="shift-receipt-row">
            <span>(−) مرتجعات نقدية:</span>
            <span class="font-numeric">−{{ number_format($shift->cash_returns_amount, 2) }} ج.م</span>
        </div>

        <div class="shift-receipt-row bold">
            <span>(=) الرصيد الدفتري المتوقع:</span>
            <span class="font-numeric">{{ number_format($shift->expected_closing_balance, 2) }} ج.م</span>
        </div>

        @if($shift->actual_closing_balance !== null)
            <div class="shift-receipt-row grand">
                <span>الجرد الفعلي للنقدية:</span>
                <span class="font-numeric">{{ number_format($shift->actual_closing_balance, 2) }} ج.م</span>
            </div>

            <div class="shift-receipt-row bold">
                <span>فارق الجرد:</span>
                <span class="font-numeric">
                    {{ $shift->difference > 0 ? '+' : '' }}{{ number_format($shift->difference, 2) }} ج.م
                    ({{ $shift->difference == 0 ? 'مطابق' : ($shift->difference > 0 ? 'زيادة' : 'عجز') }})
                </span>
            </div>
        @endif

        @if($shift->closing_notes)
            <div class="shift-receipt-meta mt-2">
                <span>ملاحظات:</span> {{ $shift->closing_notes }}
            </div>
        @endif

        <div class="shift-receipt-footer">
            <div>طُبع بتاريخ: {{ now()->format('Y-m-d H:i:s') }}</div>
            <div>توقيع الكاشير: ........................</div>
        </div>
    </div>
</body>
</html>
