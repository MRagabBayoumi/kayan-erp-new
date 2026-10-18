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
            <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                <a href="{{ route('portal.orders.create') }}" class="erp-btn-pill-white px-3.5 py-2 text-xs">
                    <x-icon name="plus" size="14" />
                    <span>إنشاء فاتورة جديدة</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Daily GPS Attendance Status Banner -->
    <div class="erp-card mb-0 p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 {{ ! $todayAttendance ? 'border-amber-300 bg-amber-50/70' : ($todayAttendance->status === 'present' ? 'border-emerald-300 bg-emerald-50/70' : 'border-blue-300 bg-blue-50/70') }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ ! $todayAttendance ? 'bg-amber-100 text-amber-700' : ($todayAttendance->status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700') }}">
                <x-icon name="clock" size="20" />
            </div>
            <div>
                @if(! $todayAttendance)
                    <div class="text-xs sm:text-sm font-bold text-amber-900">لم تقم بتسجيل حضورك الميداني اليوم بعد</div>
                    <div class="text-[11px] text-amber-700">سجل حضورك الآن عبر الـ GPS لاحتساب ساعات عملك الميدانية</div>
                @elseif($todayAttendance->status === 'present')
                    <div class="text-xs sm:text-sm font-bold text-emerald-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>أنت مسجل حضورك الميداني اليوم</span>
                    </div>
                    <div class="text-[11px] text-emerald-700 font-numeric">وقت الحضور: {{ $todayAttendance->check_in_time->format('h:i A') }} (مدة الدوام جارية)</div>
                @else
                    <div class="text-xs sm:text-sm font-bold text-blue-900">تم إنهاء يوم العمل بنجاح (انصراف مكتمل)</div>
                    <div class="text-[11px] text-blue-700 font-numeric">إجمالي وقت العمل اليوم: {{ $todayAttendance->formatted_work_duration }}</div>
                @endif
            </div>
        </div>

        <a href="{{ route('portal.attendance.index') }}" class="erp-btn {{ ! $todayAttendance ? 'erp-btn-warning' : ($todayAttendance->status === 'present' ? 'erp-btn-danger' : 'erp-btn-secondary') }} text-xs py-1.5 px-3 self-stretch sm:self-auto justify-center">
            <x-icon name="{{ ! $todayAttendance ? 'log-in' : ($todayAttendance->status === 'present' ? 'log-out' : 'eye') }}" size="14" />
            <span>{{ ! $todayAttendance ? 'تسجيل الحضور الآن' : ($todayAttendance->status === 'present' ? 'شاشة الحضور / انصراف' : 'عرض سجل الدوام') }}</span>
        </a>
    </div>

    <!-- Quick Action Cards (Kayan ERP Card Style) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
        <a href="{{ route('portal.orders.create') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-9 h-9 rounded-md bg-[var(--primary-soft)] text-[var(--primary)] flex items-center justify-center mb-1.5 group-hover:scale-105 transition-transform">
                <x-icon name="plus-circle" size="18" />
            </div>
            <span class="text-xs font-bold text-[var(--text-main)]">طلب بيع جديد</span>
            <span class="text-[10px] text-[var(--text-muted)] mt-0.5">تسجيل فاتورة</span>
        </a>

        <a href="{{ route('portal.visits.create') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-9 h-9 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center mb-1.5 group-hover:scale-105 transition-transform">
                <x-icon name="map-pin" size="18" />
            </div>
            <span class="text-xs font-bold text-[var(--text-main)]">زيارة ميدانية</span>
            <span class="text-[10px] text-[var(--text-muted)] mt-0.5">توثيق وصول GPS</span>
        </a>

        <a href="{{ route('portal.attendance.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-9 h-9 rounded-md bg-sky-50 text-sky-600 flex items-center justify-center mb-1.5 group-hover:scale-105 transition-transform">
                <x-icon name="clock" size="18" />
            </div>
            <span class="text-xs font-bold text-[var(--text-main)]">دوام العمل (GPS)</span>
            <span class="text-[10px] text-[var(--text-muted)] mt-0.5">حضور وانصراف</span>
        </a>

        <a href="{{ route('portal.customers.create') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-9 h-9 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center mb-1.5 group-hover:scale-105 transition-transform">
                <x-icon name="user-plus" size="18" />
            </div>
            <span class="text-xs font-bold text-[var(--text-main)]">تسجيل عميل</span>
            <span class="text-[10px] text-[var(--text-muted)] mt-0.5">بيانات وصور المحل</span>
        </a>

        <a href="{{ route('portal.catalog') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-9 h-9 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center mb-1.5 group-hover:scale-105 transition-transform">
                <x-icon name="package" size="18" />
            </div>
            <span class="text-xs font-bold text-[var(--text-main)]">كتالوج الأصناف</span>
            <span class="text-[10px] text-[var(--text-muted)] mt-0.5">سعر الجملة والمخزون</span>
        </a>

        <a href="{{ route('portal.orders.index') }}" class="erp-card hover:border-[var(--primary)] hover:shadow-sm transition-all p-3 flex flex-col items-center justify-center text-center group mb-0">
            <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center mb-1.5 group-hover:scale-105 transition-transform">
                <x-icon name="file-text" size="18" />
            </div>
            <span class="text-xs font-bold text-[var(--text-main)]">سجل الطلبيات</span>
            <span class="text-[10px] text-[var(--text-muted)] mt-0.5">متابعة الفواتير</span>
        </a>
    </div>

    <!-- Rep KPIs & Financial Stats (Odoo ERP Cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Today's Sales -->
        <div class="erp-card p-3.5 sm:p-4 mb-0 flex flex-col justify-between">
            <div class="flex items-center justify-between text-[var(--text-muted)] mb-2">
                <span class="text-xs font-semibold">مبيعات اليوم</span>
                <span class="erp-badge erp-badge-neutral font-numeric text-[10px]">{{ $todaySalesCount }} طلب</span>
            </div>
            <div class="text-base sm:text-xl font-extrabold text-[var(--text-main)] font-numeric">
                {{ number_format($todaySalesTotal, 2) }} <span class="text-xs font-normal text-[var(--text-muted)]">ج.م</span>
            </div>
        </div>

        <!-- Month's Commissions -->
        <div class="erp-card p-3.5 sm:p-4 mb-0 flex flex-col justify-between">
            <div class="flex items-center justify-between text-[var(--text-muted)] mb-2">
                <span class="text-xs font-semibold">عمولات هذا الشهر</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-base sm:text-xl font-extrabold text-emerald-700 font-numeric">
                {{ number_format($monthCommissions, 2) }} <span class="text-xs font-normal text-emerald-600">ج.م</span>
            </div>
        </div>

        <!-- Pending Commissions -->
        <div class="erp-card p-3.5 sm:p-4 mb-0 flex flex-col justify-between">
            <div class="flex items-center justify-between text-[var(--text-muted)] mb-2">
                <span class="text-xs font-semibold">عمولات معلقة</span>
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-base sm:text-xl font-extrabold text-amber-700 font-numeric">
                {{ number_format($pendingCommissions, 2) }} <span class="text-xs font-normal text-amber-600">ج.م</span>
            </div>
        </div>

        <!-- My Customers -->
        <div class="erp-card p-3.5 sm:p-4 mb-0 flex flex-col justify-between">
            <div class="flex items-center justify-between text-[var(--text-muted)] mb-2">
                <span class="text-xs font-semibold">عملائي المربوطين بي</span>
                <x-icon name="users" size="14" class="text-[var(--primary)]" />
            </div>
            <div class="text-base sm:text-xl font-extrabold text-[var(--primary)] font-numeric">
                {{ $myCustomersCount }} <span class="text-xs font-normal text-[var(--text-muted)]">عميل</span>
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
