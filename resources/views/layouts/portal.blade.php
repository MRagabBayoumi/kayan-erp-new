<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light" style="overflow-y: auto !important; height: auto !important;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#714B67">

    <title>{{ $title ?? 'بوابة المندوب' }} | {{ $globalCompanySetting?->company_name ?? config('app.name', 'Kayan ERP') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/js/theme-init.js', 'resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--bg-app)] text-[var(--text-main)] min-h-screen flex flex-col antialiased selection:bg-[var(--primary)] selection:text-white" style="overflow-y: auto !important; height: auto !important; min-height: 100vh; font-family: 'Cairo', system-ui, -apple-system, sans-serif !important;">

    <!-- Kayan ERP Enterprise Topbar for Field Reps -->
    <header class="erp-topbar sticky top-0 z-40">
        <div class="max-w-7xl mx-auto w-full px-3 sm:px-6 flex items-center justify-between gap-4">
            <!-- Brand & Module Badge -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('portal.index') }}" class="erp-brand group">
                    @if(!empty($globalCompanySetting?->logo_url))
                        <img src="{{ $globalCompanySetting->logo_url }}" alt="Logo" class="h-6 w-6 object-contain rounded bg-white p-0.5">
                    @else
                        <span class="erp-brand-badge">K</span>
                    @endif
                    <span class="text-sm sm:text-base font-bold text-white">{{ $globalCompanySetting?->company_name ?? config('app.name', 'Kayan ERP') }}</span>
                </a>

                <div class="hidden sm:block w-px h-4 bg-white/20"></div>

                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-semibold bg-white/15 text-white/95">
                    <x-icon name="smartphone" size="13" />
                    <span>بوابة المندوب الميداني</span>
                </span>
            </div>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1 text-xs font-medium">
                <a href="{{ route('portal.index') }}" class="px-3 py-1.5 rounded transition-all {{ request()->routeIs('portal.index') ? 'bg-white/25 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    الرئيسية
                </a>
                <a href="{{ route('portal.catalog') }}" class="px-3 py-1.5 rounded transition-all {{ request()->routeIs('portal.catalog') ? 'bg-white/25 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    الكتالوج
                </a>
                <a href="{{ route('portal.customers.index') }}" class="px-3 py-1.5 rounded transition-all {{ request()->routeIs('portal.customers.*') ? 'bg-white/25 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    عملائي
                </a>
                <a href="{{ route('portal.orders.index') }}" class="px-3 py-1.5 rounded transition-all {{ request()->routeIs('portal.orders.index') || request()->routeIs('portal.orders.show') ? 'bg-white/25 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    سجل الطلبيات
                </a>
                <a href="{{ route('portal.commissions') }}" class="px-3 py-1.5 rounded transition-all {{ request()->routeIs('portal.commissions') ? 'bg-white/25 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    العمولات
                </a>
            </nav>

            <!-- User Info & Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- New Order Button -->
                <a href="{{ route('portal.orders.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-white text-[var(--primary)] hover:bg-slate-100 font-bold text-xs shadow-xs transition-all active:scale-95">
                    <x-icon name="plus" size="13" />
                    <span>طلب جديد</span>
                </a>

                <!-- User & Commission Badge -->
                <div class="hidden lg:flex items-center gap-2 px-2.5 py-1 rounded bg-white/10 border border-white/15 text-xs text-white">
                    <span class="font-bold">{{ auth()->user()->name }}</span>
                    <span class="text-white/50">|</span>
                    <span class="text-emerald-300 font-numeric font-semibold">
                        عمولة: {{ (float) (auth()->user()->commission_rate ?? 0) }}{{ auth()->user()->commission_type === 'percentage' ? '%' : ' ج.م' }}
                    </span>
                </div>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('dashboard') }}" title="لوحة الإدارة العامة" class="p-1.5 rounded text-white/80 hover:text-white hover:bg-white/10 transition-colors flex items-center gap-1 text-xs">
                        <x-icon name="grid" size="15" />
                        <span class="hidden xl:inline">لوحة الإدارة</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="تسجيل الخروج" class="p-1.5 rounded text-white/80 hover:text-rose-200 hover:bg-white/10 transition-colors">
                        <x-icon name="log-out" size="16" />
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-3">
        @if (session('status') === 'success' || session('message'))
            <div class="erp-alert erp-alert-success mb-3 shadow-xs" role="alert">
                <x-icon name="check-circle" size="16" />
                <div>{{ session('message') ?? 'تم تنفيذ العملية بنجاح.' }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="erp-alert erp-alert-danger mb-3 shadow-xs" role="alert">
                <x-icon name="alert-triangle" size="16" />
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="erp-alert erp-alert-danger mb-3 shadow-xs" role="alert">
                <div class="font-bold mb-1 flex items-center gap-1.5">
                    <x-icon name="alert-circle" size="16" />
                    <span>يرجى مراجعة الأخطاء التالية:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Work Area -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pb-24 md:pb-12 pt-3">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar (Faithful to Kayan ERP) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-[var(--border-color)] shadow-lg safe-bottom">
        <div class="max-w-md mx-auto grid grid-cols-5 h-16 items-center px-1">
            <!-- 1. Dashboard -->
            <a href="{{ route('portal.index') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.index') ? 'text-[var(--primary)] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <x-icon name="home" size="18" />
                <span class="text-[10px]">الرئيسية</span>
            </a>

            <!-- 2. Catalog -->
            <a href="{{ route('portal.catalog') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.catalog') ? 'text-[var(--primary)] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <x-icon name="package" size="18" />
                <span class="text-[10px]">الكتالوج</span>
            </a>

            <!-- 3. New Order (Elevated Kayan Primary Button) -->
            <div class="flex items-center justify-center -mt-5">
                <a href="{{ route('portal.orders.create') }}" class="w-12 h-12 rounded-full bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white flex items-center justify-center shadow-lg shadow-[var(--primary)]/30 hover:scale-105 active:scale-95 transition-all">
                    <x-icon name="plus" size="22" />
                </a>
            </div>

            <!-- 4. Customers -->
            <a href="{{ route('portal.customers.index') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.customers.*') ? 'text-[var(--primary)] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <x-icon name="users" size="18" />
                <span class="text-[10px]">عملائي</span>
            </a>

            <!-- 5. Commissions -->
            <a href="{{ route('portal.commissions') }}" class="flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('portal.commissions') ? 'text-[var(--primary)] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <x-icon name="dollar-sign" size="18" />
                <span class="text-[10px]">عمولاتي</span>
            </a>
        </div>
    </nav>

    <!-- Camera Barcode Scanner Modal for Portal -->
    <x-barcode-scanner-modal />

    @stack('scripts')
</body>
</html>
