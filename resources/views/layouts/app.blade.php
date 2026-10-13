<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light" data-density="comfortable">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'الرئيسية' }} | Kayan ERP</title>

    @if(!empty($globalCompanySetting?->logo_url))
        <link rel="icon" href="{{ $globalCompanySetting->logo_url }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23714B67'/><text x='50%' y='55%' dominant-baseline='central' text-anchor='middle' fill='white' font-size='60' font-family='sans-serif' font-weight='bold'>K</text></svg>">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/js/theme-init.js', 'resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="erp-app-wrapper">
    <!-- Dynamic Module Name Resolver -->
    @php
        $activeModuleName = $moduleName ?? (trim($__env->yieldContent('module_name')) ?: null);
        if (!$activeModuleName) {
            if (request()->is('inventory/movements*')) {
                $activeModuleName = 'سجل حركات المخزون';
            } elseif (request()->is('inventory*') || request()->is('warehouses*')) {
                $activeModuleName = 'المخزون والمستودعات';
            } elseif (request()->is('products*') || request()->is('categories*')) {
                $activeModuleName = 'المنتجات والأصناف';
            } elseif (request()->is('settings*')) {
                $activeModuleName = 'إعدادات النظام';
            } elseif (request()->is('customers*')) {
                $activeModuleName = 'المبيعات والعملاء';
            } elseif (request()->is('suppliers*')) {
                $activeModuleName = 'المشتريات والتوريدات';
            } elseif (request()->is('expenses*') || request()->is('payments*')) {
                $activeModuleName = 'المالية والحسابات';
            } else {
                $activeModuleName = 'لوحة التحكم';
            }
        }
    @endphp

    <!-- Top Navigation Bar -->
    <x-topbar :module-name="$activeModuleName" />

    <!-- Application Body (Sidebar + Main View) -->
    <div class="erp-body-layout">
        <!-- Sidebar Navigation -->
        <x-sidebar />
        <div id="sidebarOverlay" class="erp-sidebar-overlay" aria-hidden="true"></div>

        <!-- Main Work Area -->
        <main class="erp-main">
            @yield('control_panel')

            <div class="erp-content">
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="erp-footer flex items-center gap-1 justify-center text-lg">
                <div><a href="#">BayoSoft</a> © {{ date('Y') }}</div>
                <div class="font-numeric">الإصدار 1.0 </div>
            </footer>
        </main>
    </div>

    <!-- Odoo 3x3 App Launcher Drawer -->
    <x-app-launcher />
</div>

@stack('scripts')

</body>
</html>
