<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light" data-density="comfortable">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'الرئيسية' }} | Kayan ERP</title>

    <!-- Google Cairo Font for Arabic Typography -->
    <link rel="icon" type="image/x-icon" href="{{ $globalCompanySetting?->logo_url }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Pre-render Theme & Density Initializer (Prevents Theme Flicker) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('kayan_theme');
                if (savedTheme) {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }

                const savedDensity = localStorage.getItem('kayan_density');
                if (savedDensity) {
                    document.documentElement.setAttribute('data-density', savedDensity);
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="erp-app-wrapper">
    <!-- Top Navigation Bar -->
    <x-topbar :module-name="$moduleName ?? 'لوحة التحكم'" />

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
