@extends('layouts.portal', ['title' => 'سجل طلباتي'])

@section('content')
<div class="space-y-4">

    <!-- Top Action & Filter -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>سجل طلبات وفواتير البيع</span>
            </h1>

            <a href="{{ route('portal.orders.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>طلب جديد</span>
            </a>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
            <a href="{{ route('portal.orders.index') }}" class="flex-1 py-1.5 text-center rounded-lg transition-all {{ empty($currentStatus) ? 'bg-white text-slate-800 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                الكل
            </a>
            <a href="{{ route('portal.orders.index', ['status' => 'posted']) }}" class="flex-1 py-1.5 text-center rounded-lg transition-all {{ $currentStatus === 'posted' ? 'bg-white text-emerald-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                مرحلة ومكتملة
            </a>
            <a href="{{ route('portal.orders.index', ['status' => 'draft']) }}" class="flex-1 py-1.5 text-center rounded-lg transition-all {{ $currentStatus === 'draft' ? 'bg-white text-amber-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                مسودات معلقة
            </a>
        </div>
    </div>

    <!-- Orders Cards Responsive Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($orders as $order)
            <a href="{{ route('portal.orders.show', $order) }}" class="block bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400 font-numeric">#{{ $order->invoice_number }}</span>
                            @if($order->status === 'posted')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    معتمدة ومرحّلة
                                </span>
                            @elseif($order->status === 'draft')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                                    مسودة قيد الاعتماد
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800">
                                    ملغاة
                                </span>
                            @endif
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 mt-1">{{ $order->customer?->name ?? 'عميل نقدي' }}</h2>
                    </div>

                    <div class="text-left shrink-0">
                        <div class="text-sm sm:text-base font-extrabold text-slate-800 font-numeric">
                            {{ number_format($order->total_amount, 2) }} <span class="text-xs font-medium text-slate-400">ج.م</span>
                        </div>
                        @if($order->commission)
                            <div class="text-[11px] text-emerald-600 font-bold font-numeric mt-0.5">
                                عمولة: +{{ number_format($order->commission->commission_amount, 2) }} ج.م
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span>{{ $order->invoice_date->format('Y/m/d') }}</span>
                        @if($order->warehouse)
                            <span>· مستودع: {{ $order->warehouse->name }}</span>
                        @endif
                    </div>

                    <span class="text-sky-600 font-bold text-xs flex items-center gap-0.5">
                        <span>التفاصيل</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </span>
                </div>
            </a>
        @empty
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200/80">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <div class="text-sm font-bold text-slate-700">لا توجد طلبات مسجلة</div>
                <div class="text-xs text-slate-400 mt-1">اضغط على «طلب جديد» لإنشاء أول فاتورة أو مسودة طلب</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $orders->links() }}
    </div>

</div>
@endsection
