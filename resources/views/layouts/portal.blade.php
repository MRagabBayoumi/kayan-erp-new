<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light" style="overflow-y: auto !important; height: auto !important;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#0284c7">

    <title>{{ $title ?? 'بوابة المندوب' }} | {{ $globalCompanySetting?->company_name ?? config('app.name', 'Kayan ERP') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/js/theme-init.js', 'resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased selection:bg-sky-500 selection:text-white" style="overflow-y: auto !important; height: auto !important; min-height: 100vh; font-family: 'Cairo', system-ui, -apple-system, sans-serif !important;">

    <!-- Top App Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs safe-top">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
            <!-- Brand & Portal Name -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('portal.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center text-white shadow-sm font-bold text-lg group-hover:scale-105 transition-transform">
                        ك
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-800 leading-tight">بوابة المندوب الميداني</div>
                        <div class="text-[11px] text-slate-500 font-medium">{{ $globalCompanySetting?->company_name ?? 'Kayan ERP' }}</div>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation Links (hidden on mobile, visible on desktop) -->
            <nav class="hidden md:flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl text-xs font-bold text-slate-600">
                <a href="{{ route('portal.index') }}" class="px-3 py-1.5 rounded-lg transition-all {{ request()->routeIs('portal.index') ? 'bg-white text-sky-600 shadow-xs' : 'hover:text-slate-900 hover:bg-slate-200/60' }}">
                    الرئيسية
                </a>
                <a href="{{ route('portal.catalog') }}" class="px-3 py-1.5 rounded-lg transition-all {{ request()->routeIs('portal.catalog') ? 'bg-white text-sky-600 shadow-xs' : 'hover:text-slate-900 hover:bg-slate-200/60' }}">
                    الكتالوج
                </a>
                <a href="{{ route('portal.customers.index') }}" class="px-3 py-1.5 rounded-lg transition-all {{ request()->routeIs('portal.customers.*') ? 'bg-white text-sky-600 shadow-xs' : 'hover:text-slate-900 hover:bg-slate-200/60' }}">
                    عملائي
                </a>
                <a href="{{ route('portal.orders.index') }}" class="px-3 py-1.5 rounded-lg transition-all {{ request()->routeIs('portal.orders.index') || request()->routeIs('portal.orders.show') ? 'bg-white text-sky-600 shadow-xs' : 'hover:text-slate-900 hover:bg-slate-200/60' }}">
                    سجل الطلبيات
                </a>
                <a href="{{ route('portal.commissions') }}" class="px-3 py-1.5 rounded-lg transition-all {{ request()->routeIs('portal.commissions') ? 'bg-white text-sky-600 shadow-xs' : 'hover:text-slate-900 hover:bg-slate-200/60' }}">
                    العمولات
                </a>
            </nav>

            <!-- User Info & Action -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Desktop New Order Button -->
                <a href="{{ route('portal.orders.create') }}" class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white text-xs font-bold shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>طلب جديد</span>
                </a>

                <div class="hidden sm:flex flex-col text-left border-s border-slate-200 ps-2.5">
                    <span class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] text-emerald-600 font-semibold font-numeric">
                        عمولة: {{ (float) (auth()->user()->commission_rate ?? 0) }}{{ auth()->user()->commission_type === 'percentage' ? '%' : ' ج.م' }}
                    </span>
                </div>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('dashboard') }}" title="العودة للإدارة العامة" class="px-2.5 py-1.5 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span class="hidden lg:inline">لوحة الإدارة</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="تسجيل الخروج" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-3">
        @if (session('status') === 'success' || session('message'))
            <div class="p-3.5 mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <div class="flex-1">{{ session('message') ?? 'تم تنفيذ العملية بنجاح.' }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 mb-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <div class="flex-1">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 mb-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs sm:text-sm font-medium shadow-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span>يرجى مراجعة الأخطاء التالية:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Work Area with bottom spacing on mobile only -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pb-24 md:pb-12 pt-2">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar (Hidden on Desktop) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg safe-bottom">
        <div class="max-w-md mx-auto grid grid-cols-5 h-16 items-center px-1">
            <!-- 1. Dashboard -->
            <a href="{{ route('portal.index') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.index') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('portal.index') ? 'stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-[10px]">الرئيسية</span>
            </a>

            <!-- 2. Catalog -->
            <a href="{{ route('portal.catalog') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.catalog') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('portal.catalog') ? 'stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="text-[10px]">الكتالوج</span>
            </a>

            <!-- 3. New Order (Elevated Button) -->
            <div class="flex items-center justify-center -mt-5">
                <a href="{{ route('portal.orders.create') }}" class="w-12 h-12 rounded-full bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/30 hover:scale-105 active:scale-95 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                </a>
            </div>

            <!-- 4. Customers -->
            <a href="{{ route('portal.customers.index') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.customers.*') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('portal.customers.*') ? 'stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-[10px]">عملائي</span>
            </a>

            <!-- 5. Commissions -->
            <a href="{{ route('portal.commissions') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.commissions') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('portal.commissions') ? 'stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-[10px]">عمولاتي</span>
            </a>
        </div>
    </nav>

    <!-- Camera Barcode Scanner Modal for Portal -->
    <x-barcode-scanner-modal />

    @stack('scripts')
</body>
</html>
