@extends('layouts.portal', ['title' => 'لوحة المندوب'])

@section('content')
<div class="space-y-4">

    <!-- Welcome & Rep Identity Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 via-sky-700 to-indigo-800 text-white p-4 sm:p-5 shadow-sm">
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <div class="text-xs text-sky-100 font-medium mb-1">مرحباً بك، {{ $user->name }}</div>
                <h1 class="text-lg sm:text-xl font-bold tracking-tight">لوحة مبيعات المندوب الميداني</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur text-white font-semibold">
                        <svg class="w-3.5 h-3.5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        نسبة العمولة: {{ (float) ($user->commission_rate ?? 0) }}{{ $user->commission_type === 'percentage' ? '%' : ' ج.م' }}
                    </span>
                    <span class="text-sky-100 text-[11px]">{{ now()->translatedFormat('l، d F Y') }}</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-sky-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Quick Action Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('portal.orders.create') }}" class="flex flex-col items-center justify-center p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-sky-300 hover:shadow-sm hover:bg-sky-50/40 transition-all text-center group">
            <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <span class="text-xs sm:text-sm font-bold text-slate-800">طلب جديد</span>
            <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5">فاتورة بيع ميدانية</span>
        </a>

        <a href="{{ route('portal.customers.create') }}" class="flex flex-col items-center justify-center p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-indigo-300 hover:shadow-sm hover:bg-indigo-50/40 transition-all text-center group">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <span class="text-xs sm:text-sm font-bold text-slate-800">تسجيل عميل</span>
            <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5">إضافة عميل مع الـ GPS</span>
        </a>

        <a href="{{ route('portal.catalog') }}" class="flex flex-col items-center justify-center p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-sm hover:bg-emerald-50/40 transition-all text-center group">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <span class="text-xs sm:text-sm font-bold text-slate-800">كتالوج المعرض</span>
            <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5">الأسعار والمخزون الحي</span>
        </a>

        <a href="{{ route('portal.orders.index') }}" class="flex flex-col items-center justify-center p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-amber-300 hover:shadow-sm hover:bg-amber-50/40 transition-all text-center group">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <span class="text-xs sm:text-sm font-bold text-slate-800">سجل طلباتي</span>
            <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5">متابعة الفواتير السابقة</span>
        </a>
    </div>

    <!-- Rep KPIs & Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Today's Sales -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs sm:text-sm font-semibold">مبيعات اليوم</span>
                <span class="px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700 text-[10px] sm:text-xs font-bold">{{ $todaySalesCount }} طلب</span>
            </div>
            <div class="text-lg sm:text-2xl font-extrabold text-slate-800 font-numeric">
                {{ number_format($todaySalesTotal, 2) }} <span class="text-xs font-medium text-slate-400">ج.م</span>
            </div>
        </div>

        <!-- Month's Commissions -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs sm:text-sm font-semibold">عمولات هذا الشهر</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-lg sm:text-2xl font-extrabold text-emerald-600 font-numeric">
                {{ number_format($monthCommissions, 2) }} <span class="text-xs font-medium text-emerald-400">ج.م</span>
            </div>
        </div>

        <!-- Pending Commissions -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs sm:text-sm font-semibold">عمولات معلقة</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-lg sm:text-2xl font-extrabold text-amber-600 font-numeric">
                {{ number_format($pendingCommissions, 2) }} <span class="text-xs font-medium text-amber-400">ج.م</span>
            </div>
        </div>

        <!-- My Customers -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs sm:text-sm font-semibold">عملائي المربوطين بي</span>
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="text-lg sm:text-2xl font-extrabold text-indigo-600 font-numeric">
                {{ $myCustomersCount }} <span class="text-xs font-medium text-indigo-400">عميل</span>
            </div>
        </div>
    </div>

    <!-- Responsive Desktop Split Grid: Recent Orders & Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
        <!-- Recent Orders Section -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span>أحدث الطلبات والفواتير</span>
                </h2>
                <a href="{{ route('portal.orders.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700">عرض الكل &larr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentOrders as $order)
                    <a href="{{ route('portal.orders.show', $order) }}" class="p-3.5 flex items-center justify-between hover:bg-slate-50/80 transition-colors block">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs sm:text-sm font-bold text-slate-800 truncate">{{ $order->customer?->name ?? 'عميل نقدي' }}</span>
                                @if($order->status === 'posted')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">مرحّلة</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">مسودة</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-400 font-numeric">
                                <span>#{{ $order->invoice_number }}</span>
                                <span>·</span>
                                <span>{{ $order->invoice_date->format('Y/m/d') }}</span>
                            </div>
                        </div>
                        <div class="text-left shrink-0">
                            <div class="text-sm sm:text-base font-extrabold text-slate-800 font-numeric">
                                {{ number_format($order->total_amount, 2) }} ج.م
                            </div>
                            @if($order->commission)
                                <div class="text-[11px] text-emerald-600 font-semibold font-numeric">
                                    عمولة: +{{ number_format($order->commission->commission_amount, 2) }} ج.م
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        لم تقم بإنشاء أي طلبات حتى الآن. اضغط على «طلب جديد» للبدء.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Registered Customers -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>أحدث العملاء المسجلين بواسطتك</span>
                </h2>
                <a href="{{ route('portal.customers.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">عرض الكل &larr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentCustomers as $c)
                    <div class="p-3.5 flex items-center justify-between">
                        <div>
                            <div class="text-xs sm:text-sm font-bold text-slate-800">{{ $c->name }}</div>
                            <div class="text-[11px] text-slate-400 font-numeric flex items-center gap-2 mt-0.5">
                                <span>{{ $c->phone }}</span>
                                @if($c->city)
                                    <span>· {{ $c->city }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($c->phone)
                                <a href="tel:{{ $c->phone }}" class="p-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </a>
                            @endif
                            <a href="{{ route('portal.orders.create', ['customer_id' => $c->id]) }}" class="px-3 py-1.5 rounded-xl bg-sky-50 text-sky-600 text-xs font-bold hover:bg-sky-100 transition-colors">
                                طلب جديد
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        لم تسجل عملاء حتى الآن. اضغط على «تسجيل عميل» لإضافة أول عميل.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
