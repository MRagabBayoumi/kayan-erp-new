@extends('layouts.portal', ['title' => 'تفاصيل الطلب #' . $invoice->invoice_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    <!-- Header & Status Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs sm:text-sm font-bold text-slate-400 font-numeric">#{{ $invoice->invoice_number }}</span>
                @if($invoice->status === 'posted')
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        مرحّلة ومعتمدة
                    </span>
                @elseif($invoice->status === 'draft')
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                        مسودة طلب معلقة
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800">
                        ملغاة
                    </span>
                @endif
            </div>
            <h1 class="text-base sm:text-lg font-bold text-slate-800 mt-1">{{ $invoice->customer?->name ?? 'عميل نقدي' }}</h1>
        </div>

        <a href="{{ route('portal.orders.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </a>
    </div>

    <!-- Order Metadata (4 Columns on Desktop) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div>
            <span class="text-slate-400 block text-[11px]">تاريخ الفاتورة</span>
            <span class="font-bold text-slate-800 font-numeric">{{ $invoice->invoice_date->format('Y/m/d') }}</span>
        </div>
        <div>
            <span class="text-slate-400 block text-[11px]">المستودع</span>
            <span class="font-bold text-slate-800">{{ $invoice->warehouse?->name ?? 'المستودع الرئيسي' }}</span>
        </div>
        <div>
            <span class="text-slate-400 block text-[11px]">طريقة السداد</span>
            <span class="font-bold text-slate-800">
                {{ match($invoice->payment_type) {
                    'credit' => 'آجل بالكامل',
                    'partial' => 'دفع جزئي',
                    default => 'نقدي فوري'
                } }}
            </span>
        </div>
        <div>
            <span class="text-slate-400 block text-[11px]">فئة السعر</span>
            <span class="font-bold text-slate-800">
                {{ match($invoice->price_tier) {
                    'wholesale' => 'جملة',
                    'distributor' => 'موزع',
                    default => 'تجزئة'
                } }}
            </span>
        </div>
    </div>

    <!-- Items Breakdown -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <h2 class="text-xs sm:text-sm font-bold text-slate-800">الأصناف والبنود</h2>

        <div class="divide-y divide-slate-100">
            @foreach($invoice->items as $item)
                <div class="py-2.5 flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-900 truncate">{{ $item->product?->name ?? 'صنف غير محدد' }}</div>
                        <div class="text-[11px] text-slate-400 font-numeric mt-0.5">
                            {{ (float)$item->quantity }} {{ $item->unit?->name ?? ($item->product?->unit ?? 'قطعة') }} &times; {{ number_format($item->unit_price, 2) }} ج.م
                            @if((float)$item->line_discount > 0)
                                <span class="text-rose-500 font-medium"> (خصم: {{ number_format($item->line_discount, 2) }})</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-left font-numeric font-extrabold text-xs text-slate-800 shrink-0">
                        {{ number_format($item->subtotal, 2) }} ج.م
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Financial Summary Box -->
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5 pt-3">
            <div class="flex items-center justify-between text-xs text-slate-600">
                <span>إجمالي الطلب:</span>
                <span class="font-extrabold text-slate-900 text-sm font-numeric">{{ number_format($invoice->total_amount, 2) }} ج.م</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>المدفوع:</span>
                <span class="font-bold text-emerald-600 font-numeric">{{ number_format($invoice->paid_amount, 2) }} ج.م</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>المتبقي (آجل):</span>
                <span class="font-bold text-rose-600 font-numeric">{{ number_format($invoice->remaining_amount, 2) }} ج.م</span>
            </div>
        </div>
    </div>

    <!-- Sales Rep Commission Card -->
    @if($invoice->commission)
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl p-4 border border-emerald-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-700 block">عمولة المندوب عن هذا الطلب</span>
                <div class="text-lg font-extrabold text-emerald-800 font-numeric mt-0.5">
                    +{{ number_format($invoice->commission->commission_amount, 2) }} <span class="text-xs font-semibold">ج.م</span>
                </div>
                <span class="text-[10px] text-emerald-600 font-medium">
                    نسبة: {{ $invoice->commission->commission_rate }}%
                </span>
            </div>

            <div>
                @php
                    $commStatusBadge = match($invoice->commission->status) {
                        'paid' => 'bg-emerald-200 text-emerald-900',
                        'approved' => 'bg-teal-200 text-teal-900',
                        default => 'bg-amber-100 text-amber-900',
                    };
                    $commStatusLabel = match($invoice->commission->status) {
                        'paid' => 'تم الصرف',
                        'approved' => 'معتمدة',
                        default => 'قيد الانتظار',
                    };
                @endphp
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $commStatusBadge }}">
                    {{ $commStatusLabel }}
                </span>
            </div>
        </div>
    @endif

    <!-- Action Toolbar (WhatsApp share, Print invoice) -->
    <div class="grid grid-cols-2 gap-2.5 pt-1">
        @php
            $cleanPhone = preg_replace('/[^0-9]/', '', $invoice->customer?->phone ?? '');
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '2' . $cleanPhone;
            }
            $waText = urlencode("مرحباً بك عميلنا العزيز {$invoice->customer?->name}،\nتفاصيل طلبك رقم {$invoice->invoice_number}:\nالإجمالي: {$invoice->total_amount} ج.م\nالمدفوع: {$invoice->paid_amount} ج.م\nالمتبقي: {$invoice->remaining_amount} ج.م\nشكراً لتعاملك معنا!");
        @endphp
        @if($cleanPhone)
            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank"
                class="py-3 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-[0.99] transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <span>مشاركة بالواتساب</span>
            </a>
        @endif

        <a href="{{ route('sales.print', ['sale' => $invoice->id, 'type' => 'thermal']) }}" target="_blank"
            class="py-3 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-xs active:scale-[0.99] transition-all flex items-center justify-center gap-1.5 {{ empty($cleanPhone) ? 'col-span-2' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>طباعة الإيصال الحراري</span>
        </a>
    </div>

</div>
@endsection
