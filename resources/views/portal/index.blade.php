@extends('layouts.portal', ['title' => 'لوحة المندوب'])

@section('content')
<div class="space-y-4">

    <!-- Rep Identity & Performance Banner (Kayan ERP Odoo Theme) -->
    <div class="rounded-lg text-white p-4 sm:p-5 shadow-xs border border-[var(--primary-hover)]"
         style="background: linear-gradient(135deg, #714B67 0%, #52314b 60%, #3e2238 100%);">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="text-xs text-white/80 font-medium mb-1">مرحباً بك، {{ $user->name }} 👋</div>
                <h1 class="text-base sm:text-xl font-bold tracking-tight">بوابة المندوب الميداني — لوحة التحكم</h1>
                <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-white/15 border border-white/20 text-white font-semibold">
                        <x-icon name="percent" size="13" class="text-emerald-300" />
                        <span>نسبة العمولة: {{ (float) ($user->commission_rate ?? 0) }}{{ $user->commission_type === 'percentage' ? '%' : ' ج.م' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-white/10 text-white/90 text-xs">
                        <x-icon name="calendar" size="13" />
                        <span>{{ now()->translatedFormat('l، d F Y') }}</span>
                    </span>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 self-start sm:self-center shrink-0">
                @php $hasActiveSession = $user->hasActiveAttendanceSession(); @endphp
                @if($hasActiveSession)
                    <a href="{{ route('portal.orders.create') }}" class="erp-btn-pill-white px-3.5 py-2 text-xs">
                        <x-icon name="plus" size="14" />
                        <span>إنشاء فاتورة جديدة</span>
                    </a>
                @else
                    <a href="{{ route('portal.attendance.index') }}" class="erp-btn-pill-white px-3.5 py-2 text-xs bg-amber-500/20 border-amber-300/40 text-amber-100 hover:bg-amber-500/30">
                        <x-icon name="map-pin" size="14" />
                        <span>فتح جلسة العمل (GPS)</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @php
        $hasActiveSession = $user->hasActiveAttendanceSession();
    @endphp

    <!-- Daily GPS Attendance Status Banner -->
    <div class="erp-card mb-0 p-3.5 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 {{ ! $hasActiveSession ? 'border-amber-400 bg-amber-50/90 shadow-xs' : 'border-emerald-300 bg-emerald-50/80 shadow-xs' }}">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ ! $hasActiveSession ? 'bg-amber-100 text-amber-800 ring-2 ring-amber-300' : 'bg-emerald-100 text-emerald-800 ring-2 ring-emerald-300' }}">
                <x-icon name="{{ ! $hasActiveSession ? 'map-pin' : 'check-circle' }}" size="22" />
            </div>
            <div>
                @if(! $todayAttendance)
                    <div class="text-xs sm:text-sm font-black text-amber-950 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>جلسة العمل الميداني مغلقة — الحضور بالـ GPS إلزامي</span>
                    </div>
                    <div class="text-[11px] text-amber-800 mt-0.5 font-medium leading-relaxed">
                        يجب تسجيل حضورك الميداني وتوثيق موقعك (GPS) لفتح جلسة العمل والبدء في تسجيل الطلبات، الزيارات، وإضافة العملاء.
                    </div>
                @elseif($hasActiveSession)
                    <div class="text-xs sm:text-sm font-black text-emerald-950 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>جلسة العمل الميداني نشطة وموثقة بالـ GPS</span>
                    </div>
                    <div class="text-[11px] text-emerald-800 font-numeric mt-0.5">
                        وقت بدء الجلسة: {{ $todayAttendance->check_in_time?->format('h:i A') }} — أنت جاهز الآن لتسجيل الطلبات والزيارات والعملاء.
                    </div>
                @else
                    <div class="text-xs sm:text-sm font-black text-blue-950 flex items-center gap-1.5">
                        <x-icon name="check-circle" size="14" class="text-blue-600" />
                        <span>تم إنهاء جلسة العمل لليوم (انصراف موثق)</span>
                    </div>
                    <div class="text-[11px] text-blue-800 font-numeric mt-0.5">
                        إجمالي وقت العمل اليوم: {{ $todayAttendance->formatted_work_duration }} — يتطلب تسجيل أي طلبات إضافية إعادة فتح الجلسة.
                    </div>
                @endif
            </div>
        </div>

        <a href="{{ route('portal.attendance.index') }}" class="erp-btn {{ ! $hasActiveSession ? 'bg-amber-600 hover:bg-amber-700 text-white font-black shadow-xs' : 'bg-emerald-700 hover:bg-emerald-800 text-white font-bold' }} text-xs py-2 px-4 self-stretch sm:self-auto justify-center shrink-0">
            <x-icon name="{{ ! $hasActiveSession ? 'log-in' : 'clock' }}" size="15" />
            <span>{{ ! $todayAttendance ? 'تسجيل الحضور وفتح الجلسة' : ($hasActiveSession ? 'سجل الحضور والانصراف' : 'عرض سجل الدوام') }}</span>
        </a>
    </div>

    <!-- Quick Action Cards (Kayan ERP Compact Mobile 3-Col Grid) -->
    <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
        <!-- New Order Card -->
        <a href="{{ $hasActiveSession ? route('portal.orders.create') : route('portal.attendance.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-2.5 sm:p-3 flex flex-col items-center justify-center text-center group mb-0 relative">
            @if(! $hasActiveSession)
                <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-bold flex items-center gap-0.5" title="يتطلب فتح جلسة العمل أولاً">
                    <x-icon name="lock" size="10" />
                </span>
            @endif
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-[var(--primary-soft)] text-[var(--primary)] flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                <x-icon name="plus-circle" size="17" />
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-[var(--text-main)] truncate max-w-full">طلب جديد</span>
            <span class="text-[9px] sm:text-[10px] text-[var(--text-muted)] mt-0.5">{{ $hasActiveSession ? 'تسجيل فاتورة' : 'يتطلب حضور' }}</span>
        </a>

        <!-- Field Visit Card -->
        <a href="{{ $hasActiveSession ? route('portal.visits.create') : route('portal.attendance.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-2.5 sm:p-3 flex flex-col items-center justify-center text-center group mb-0 relative">
            @if(! $hasActiveSession)
                <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-bold flex items-center gap-0.5" title="يتطلب فتح جلسة العمل أولاً">
                    <x-icon name="lock" size="10" />
                </span>
            @endif
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                <x-icon name="map-pin" size="17" />
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-[var(--text-main)] truncate max-w-full">زيارة ميدانية</span>
            <span class="text-[9px] sm:text-[10px] text-[var(--text-muted)] mt-0.5">{{ $hasActiveSession ? 'توثيق GPS' : 'يتطلب حضور' }}</span>
        </a>

        <!-- Attendance Card -->
        <a href="{{ route('portal.attendance.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-2.5 sm:p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-sky-50 text-sky-600 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                <x-icon name="clock" size="17" />
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-[var(--text-main)] truncate max-w-full">جلسة العمل</span>
            <span class="text-[9px] sm:text-[10px] text-[var(--text-muted)] mt-0.5">{{ $hasActiveSession ? 'دوام نشط' : 'تسجيل GPS' }}</span>
        </a>

        <!-- Customer Register Card -->
        <a href="{{ $hasActiveSession ? route('portal.customers.create') : route('portal.attendance.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-2.5 sm:p-3 flex flex-col items-center justify-center text-center group mb-0 relative">
            @if(! $hasActiveSession)
                <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-bold flex items-center gap-0.5" title="يتطلب فتح جلسة العمل أولاً">
                    <x-icon name="lock" size="10" />
                </span>
            @endif
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                <x-icon name="user-plus" size="17" />
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-[var(--text-main)] truncate max-w-full">تسجيل عميل</span>
            <span class="text-[9px] sm:text-[10px] text-[var(--text-muted)] mt-0.5">{{ $hasActiveSession ? 'إضافة عميل' : 'يتطلب حضور' }}</span>
        </a>

        <a href="{{ route('portal.catalog') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-2.5 sm:p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                <x-icon name="package" size="17" />
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-[var(--text-main)] truncate max-w-full">الكتالوج</span>
            <span class="text-[9px] sm:text-[10px] text-[var(--text-muted)] mt-0.5">الأسعار والمخزون</span>
        </a>

        <a href="{{ route('portal.orders.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-2.5 sm:p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                <x-icon name="file-text" size="17" />
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-[var(--text-main)] truncate max-w-full">سجل الطلبات</span>
            <span class="text-[9px] sm:text-[10px] text-[var(--text-muted)] mt-0.5">متابعة الفواتير</span>
        </a>
    </div>

    <!-- Rep KPIs & Financial Stats (Faithful to Kayan ERP Design System) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Today's Sales -->
        <div class="kpi-card flex flex-col justify-between">
            <div class="kpi-header mb-1">
                <span class="kpi-label">مبيعات اليوم</span>
                <span class="erp-badge erp-badge-neutral font-numeric text-[10px] shrink-0">{{ $todaySalesCount }} طلب</span>
            </div>
            <div class="kpi-value font-numeric text-base sm:text-xl font-black text-[var(--text-main)]">
                {{ number_format($todaySalesTotal, 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
            </div>
            <div class="kpi-note font-numeric text-xs text-[var(--text-muted)]">
                <span>فواتير منجزة اليوم</span>
            </div>
        </div>

        <!-- Month's Commissions -->
        <div class="kpi-card flex flex-col justify-between border-emerald-200 bg-emerald-50/30">
            <div class="kpi-header mb-1">
                <span class="kpi-label text-emerald-900 font-bold">عمولات الشهر</span>
                <span class="kpi-icon text-emerald-600"><x-icon name="dollar-sign" size="16" /></span>
            </div>
            <div class="kpi-value font-numeric text-base sm:text-xl font-black text-emerald-800">
                {{ number_format($monthCommissions, 2) }} <span class="text-xs font-bold text-emerald-700">ج.م</span>
            </div>
            <div class="kpi-note text-emerald-700 text-xs font-medium">
                <span>أرباحك المكتسبة</span>
            </div>
        </div>

        <!-- Pending Commissions -->
        <div class="kpi-card flex flex-col justify-between border-amber-200 bg-amber-50/30">
            <div class="kpi-header mb-1">
                <span class="kpi-label text-amber-900 font-bold">عمولات معلقة</span>
                <span class="kpi-icon text-amber-600"><x-icon name="clock" size="16" /></span>
            </div>
            <div class="kpi-value font-numeric text-base sm:text-xl font-black text-amber-800">
                {{ number_format($pendingCommissions, 2) }} <span class="text-xs font-bold text-amber-700">ج.م</span>
            </div>
            <div class="kpi-note text-amber-700 text-xs font-medium">
                <span>قيد الصرف والاعتماد</span>
            </div>
        </div>

        <!-- My Customers -->
        <div class="kpi-card flex flex-col justify-between">
            <div class="kpi-header mb-1">
                <span class="kpi-label">عملائي المربوطين</span>
                <span class="kpi-icon text-[var(--primary)]"><x-icon name="users" size="16" /></span>
            </div>
            <div class="kpi-value font-numeric text-base sm:text-xl font-black text-[var(--primary)]">
                {{ $myCustomersCount }} <span class="text-xs font-normal text-[var(--text-muted)]">عميل</span>
            </div>
            <div class="kpi-note text-xs text-[var(--text-muted)]">
                <span>في نطاقك الميداني</span>
            </div>
        </div>
    </div>

    <!-- Responsive Desktop Split Grid: Recent Orders & Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
        <!-- Recent Orders Section -->
        <div class="erp-card mb-0">
            <div class="erp-card-header">
                <h2 class="erp-card-title">
                    <x-icon name="shopping-bag" size="16" class="text-[var(--primary)]" />
                    <span>أحدث الطلبات والفواتير</span>
                </h2>
                <a href="{{ route('portal.orders.index') }}" class="text-xs font-semibold text-[var(--primary)] hover:underline flex items-center gap-1">
                    <span>عرض الكل</span>
                    <x-icon name="arrow-left" size="12" />
                </a>
            </div>

            <div class="divide-y divide-[var(--border-light)]">
                @forelse($recentOrders as $order)
                    <a href="{{ route('portal.orders.show', $order) }}" class="p-3 sm:p-3.5 flex items-center justify-between hover:bg-[var(--bg-hover)] transition-colors block">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs sm:text-sm font-bold text-[var(--text-main)] truncate">{{ $order->customer?->name ?? 'عميل نقدي' }}</span>
                                @if($order->status === 'posted')
                                    <span class="erp-badge erp-badge-success text-[10px]">مرحّلة</span>
                                @else
                                    <span class="erp-badge erp-badge-warning text-[10px]">مسودة</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-[var(--text-muted)] font-numeric">
                                <span>#{{ $order->invoice_number }}</span>
                                <span>·</span>
                                <span>{{ $order->invoice_date->format('Y/m/d') }}</span>
                            </div>
                        </div>
                        <div class="text-left shrink-0">
                            <div class="text-xs sm:text-sm font-extrabold text-[var(--text-main)] font-numeric">
                                {{ number_format($order->total_amount, 2) }} ج.م
                            </div>
                            @if($order->commission)
                                <div class="text-[11px] text-emerald-700 font-semibold font-numeric">
                                    عمولة: +{{ number_format($order->commission->commission_amount, 2) }} ج.م
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-[var(--text-muted)] text-xs">
                        لم تقم بإنشاء أي طلبات حتى الآن. اضغط على «طلب بيع جديد» للبدء.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Registered Customers -->
        <div class="erp-card mb-0">
            <div class="erp-card-header">
                <h2 class="erp-card-title">
                    <x-icon name="user-check" size="16" class="text-[var(--primary)]" />
                    <span>أحدث العملاء المسجلين</span>
                </h2>
                <a href="{{ route('portal.customers.index') }}" class="text-xs font-semibold text-[var(--primary)] hover:underline flex items-center gap-1">
                    <span>عرض الكل</span>
                    <x-icon name="arrow-left" size="12" />
                </a>
            </div>

            <div class="divide-y divide-[var(--border-light)]">
                @forelse($recentCustomers as $c)
                    <div class="p-3 sm:p-3.5 flex items-center justify-between">
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-bold text-[var(--text-main)] truncate">{{ $c->name }}</div>
                            <div class="text-[11px] text-[var(--text-muted)] font-numeric flex items-center gap-2 mt-0.5">
                                <span>{{ $c->phone }}</span>
                                @if($c->city)
                                    <span>· {{ $c->city }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($c->phone)
                                <a href="tel:{{ $c->phone }}" class="p-1.5 rounded border border-[var(--border-color)] text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-colors" title="اتصال">
                                    <x-icon name="phone" size="14" />
                                </a>
                            @endif
                            <a href="{{ route('portal.orders.create', ['customer_id' => $c->id]) }}" class="erp-btn erp-btn-sm erp-btn-secondary text-[11px] py-1 px-2.5">
                                <x-icon name="plus" size="12" />
                                <span>طلب</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-[var(--text-muted)] text-xs">
                        لم تسجل عملاء حتى الآن. اضغط على «تسجيل عميل» لإضافة أول عميل.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
