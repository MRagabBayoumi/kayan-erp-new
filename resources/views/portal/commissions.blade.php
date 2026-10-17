@extends('layouts.portal', ['title' => 'كشف العمولات'])

@section('content')
<div class="space-y-4">

    <!-- Header & Rep Rate Info -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>كشف حساب عمولات المندوب</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">تفاصيل العمولات المستحقة والمدفوعة عن فواتيرك</p>
            </div>

            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60 font-numeric">
                نسبتك: {{ (float) ($user->commission_rate ?? 0) }}{{ $user->commission_type === 'percentage' ? '%' : ' ج.م' }}
            </span>
        </div>
    </div>

    <!-- Financial KPIs -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4">
        <!-- Total Earned -->
        <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs text-slate-400 block font-medium">إجمالي الأرباح</span>
            <div class="text-base sm:text-2xl font-extrabold text-slate-800 font-numeric mt-1">
                {{ number_format($totalEarned, 2) }}
            </div>
            <span class="text-[10px] text-slate-400">ج.م</span>
        </div>

        <!-- Pending -->
        <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs text-amber-600 block font-medium">قيد الانتظار</span>
            <div class="text-base sm:text-2xl font-extrabold text-amber-600 font-numeric mt-1">
                {{ number_format($pendingAmount, 2) }}
            </div>
            <span class="text-[10px] text-amber-400">ج.م</span>
        </div>

        <!-- Paid -->
        <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs text-center">
            <span class="text-xs text-emerald-600 block font-medium">تم صرفها</span>
            <div class="text-base sm:text-2xl font-extrabold text-emerald-600 font-numeric mt-1">
                {{ number_format($paidAmount, 2) }}
            </div>
            <span class="text-[10px] text-emerald-400">ج.م</span>
        </div>
    </div>

    <!-- Commissions Responsive Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($commissions as $comm)
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-800">{{ $comm->customer?->name ?? 'عميل نقدي' }}</span>
                            @php
                                $badgeClass = match($comm->status) {
                                    'paid' => 'bg-emerald-100 text-emerald-800',
                                    'approved' => 'bg-teal-100 text-teal-800',
                                    'canceled' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-amber-100 text-amber-800',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $badgeClass }}">
                                {{ $comm->status_label }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-numeric mt-0.5 flex items-center gap-2">
                            @if($comm->salesInvoice)
                                <a href="{{ route('portal.orders.show', $comm->salesInvoice) }}" class="text-sky-600 hover:underline">
                                    فاتورة #{{ $comm->salesInvoice->invoice_number }}
                                </a>
                                <span>·</span>
                            @endif
                            <span>{{ $comm->created_at->format('Y/m/d') }}</span>
                        </div>
                    </div>

                    <div class="text-left font-numeric shrink-0">
                        <div class="text-sm sm:text-base font-extrabold text-emerald-600">
                            +{{ number_format($comm->commission_amount, 2) }} <span class="text-xs">ج.م</span>
                        </div>
                        <span class="text-[10px] text-slate-400">
                            (نسبة {{ $comm->commission_rate }}%)
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-100 font-numeric">
                    <span>قيمة الفاتورة: {{ number_format($comm->invoice_total, 2) }} ج.م</span>
                    @if($comm->paid_at)
                        <span class="text-emerald-600 font-medium">تاريخ الصرف: {{ $comm->paid_at->format('Y/m/d') }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200/80">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-sm font-bold text-slate-700">لا توجد عمولات مسجلة بعد</div>
                <div class="text-xs text-slate-400 mt-1">عند ترحيل فواتير عملائك ستظهر عمولاتك تلقائياً هنا</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $commissions->links() }}
    </div>

</div>
@endsection
