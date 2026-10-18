<aside id="erpSidebar" class="erp-sidebar">
    <!-- Company Box -->
    <div class="erp-sidebar-header">
        <div class="erp-company-card">
            @if(!empty($globalCompanySetting?->logo_url))
                <img src="{{ $globalCompanySetting->logo_url }}" alt="{{ $globalCompanySetting?->company_name }}" class="erp-company-logo erp-company-logo-img">
            @else
                <div class="erp-company-logo">K</div>
            @endif
            <div class="erp-company-info">
                <div class="erp-company-name" title="{{ $globalCompanySetting?->company_name ?? 'Kayan Fashion Accessories' }}">
                    {{ $globalCompanySetting?->company_name ?? 'Kayan Fashion Accessories' }}
                </div>
                <div class="erp-company-sub">
                    @if(!empty($globalCompanySetting) && $globalCompanySetting->isMaintenance())
                        <span class="erp-badge erp-badge-warning text-[10px] px-1.5 py-0.5">وضع الصيانة</span>
                    @else
                        <span>استيراد وتوزيع إكسسوارات حريمي</span>
                    @endif
                </div>
            </div>
        </div>
        <button id="sidebarDesktopToggle" class="erp-sidebar-toggle-btn" type="button" title="طي / توسيع القائمة" aria-label="طي أو توسيع القائمة الجانبية">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
    </div>

    <!-- Navigation List -->
    <nav class="erp-sidebar-nav">
        <!-- Overview & Applications -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">التطبيقات العامة</div>
            <a href="{{ route('home') }}" class="erp-nav-item {{ request()->routeIs('home') ? 'active' : '' }}" title="تطبيقات {{ $globalCompanySetting?->company_name ?? config('app.name', 'Kayan ERP') }}">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="grid" size="18" /></span>
                    <span class="erp-nav-label">تطبيقات النظام</span>
                </div>
            </a>
            @can('access-dashboard')
            <a href="{{ route('dashboard') }}" class="erp-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="لوحة التحكم والمؤشرات">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="layout-dashboard" size="18" /></span>
                    <span class="erp-nav-label">لوحة التحكم العامة</span>
                </div>
            </a>
            @endcan
        </div>

        <!-- Sales & POS -->
        @can('access-sales')
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المبيعات ونقاط البيع</div>
            <a href="{{ route('sales.create') }}" class="erp-nav-item {{ request()->routeIs('sales.create') ? 'active' : '' }}" title="نقطة البيع (الكاشير)">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="zap" size="18" class="text-amber-500" /></span>
                    <span class="erp-nav-label font-bold">نقطة البيع (الكاشير)</span>
                </div>
            </a>
            <a href="{{ route('sales.index') }}" class="erp-nav-item {{ request()->is('sales') || (request()->is('sales/*') && !request()->routeIs('sales.create')) ? 'active' : '' }}" title="فواتير المبيعات">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="shopping-cart" size="18" /></span>
                    <span class="erp-nav-label">فواتير المبيعات</span>
                </div>
            </a>
            @can('manage-shifts')
            <a href="{{ route('shifts.index') }}" class="erp-nav-item {{ request()->is('shifts*') ? 'active' : '' }}" title="ورديات وخزينة الكاشير">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="clock" size="18" /></span>
                    <span class="erp-nav-label">ورديات وخزينة الكاشير</span>
                </div>
            </a>
            @endcan
            @can('manage-customers')
            <a href="{{ route('customers.index') }}" class="erp-nav-item {{ request()->is('customers*') ? 'active' : '' }}" title="العملاء وتجار الجملة">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="users" size="18" /></span>
                    <span class="erp-nav-label">العملاء</span>
                </div>
            </a>
            @endcan
        </div>
        @endcan

        <!-- Sales Representatives Field Portal & Analytics -->
        @if(auth()->user()->isAdmin() || auth()->user()->isSalesRep())
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">مندوبو المبيعات</div>
            @can('manage-sales-reps')
            <a href="{{ route('sales-reps.index') }}" class="erp-nav-item {{ request()->is('sales-reps*') ? 'active' : '' }}" title="إدارة وتحليل أداء المناديب">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="user-check" size="18" class="text-indigo-600" /></span>
                    <span class="erp-nav-label font-bold">إدارة وتحليل المناديب</span>
                </div>
            </a>
            @endcan
            @can('access-sales-portal')
            <a href="{{ route('portal.index') }}" class="erp-nav-item {{ request()->is('portal*') ? 'active' : '' }}" title="بوابة المندوب الميداني">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="smartphone" size="18" class="text-sky-500" /></span>
                    <span class="erp-nav-label font-bold text-sky-600">بوابة المندوب (الموبايل)</span>
                </div>
            </a>
            @endcan
        </div>
        @endif

        <!-- Purchases -->
        @can('access-purchases')
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المشتريات والواردات</div>
            <a href="{{ route('purchases.index') }}" class="erp-nav-item {{ request()->is('purchases*') ? 'active' : '' }}" title="فواتير الشراء والتوريد">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="shopping-bag" size="18" /></span>
                    <span class="erp-nav-label">فواتير الشراء والتوريد</span>
                </div>
            </a>
            <a href="{{ route('purchase-returns.index') }}" class="erp-nav-item {{ request()->is('purchase-returns*') ? 'active' : '' }}" title="مرتجعات المشتريات">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="rotate-ccw" size="18" /></span>
                    <span class="erp-nav-label">مرتجعات المشتريات</span>
                </div>
            </a>
            <a href="{{ route('suppliers.index') }}" class="erp-nav-item {{ request()->is('suppliers*') || request()->is('shipping-offices*') ? 'active' : '' }}" title="الموردون ومكاتب الشحن">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="factory" size="18" /></span>
                    <span class="erp-nav-label">الموردون ومكاتب الشحن</span>
                </div>
            </a>
        </div>
        @endcan

        <!-- Inventory -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المخزون والمنتجات</div>
            @can('view-products')
            <a href="{{ url('/products') }}" class="erp-nav-item {{ request()->is('products*') ? 'active' : '' }}" title="المنتجات والأصناف">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="package" size="18" /></span>
                    <span class="erp-nav-label">المنتجات والأصناف</span>
                </div>
            </a>
            @endcan
            @can('manage-products')
            <a href="{{ route('categories.index') }}" class="erp-nav-item {{ request()->is('categories*') ? 'active' : '' }}" title="تصنيفات المنتجات">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="tag" size="18" /></span>
                    <span class="erp-nav-label">تصنيفات المنتجات</span>
                </div>
            </a>
            @endcan
            @can('access-inventory')
            <a href="{{ route('inventory.index') }}" class="erp-nav-item {{ request()->routeIs('inventory.index') ? 'active' : '' }}" title="نظرة عامة على المخزون">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="warehouse" size="18" /></span>
                    <span class="erp-nav-label">نظرة عامة على المخزون</span>
                </div>
            </a>
            <a href="{{ route('warehouses.index') }}" class="erp-nav-item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}" title="المستودعات والمخازن">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="home" size="18" /></span>
                    <span class="erp-nav-label">المستودعات والمخازن</span>
                </div>
            </a>
            <a href="{{ route('inventory.movements') }}" class="erp-nav-item {{ request()->routeIs('inventory.movements') ? 'active' : '' }}" title="حركات المخزون">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="arrow-left-right" size="18" /></span>
                    <span class="erp-nav-label">حركات المخزون</span>
                </div>
            </a>
            <a href="{{ route('inventory.stocktake') }}" class="erp-nav-item {{ request()->routeIs('inventory.stocktake*') ? 'active' : '' }}" title="جرد المخزون والتسوية">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="clipboard-check" size="18" /></span>
                    <span class="erp-nav-label">جرد المخزون والتسوية</span>
                </div>
            </a>
            <a href="{{ route('inventory.low-stock') }}" class="erp-nav-item {{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}" title="نواقص المخزون">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="alert-triangle" size="18" /></span>
                    <span class="erp-nav-label">نواقص المخزون</span>
                </div>
                <span class="erp-nav-badge warning">تنبيه</span>
            </a>
            @endcan
        </div>

        <!-- Finance -->
        @can('manage-company-settings')
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المالية والحسابات</div>
            <a href="{{ route('treasuries.index') }}" class="erp-nav-item {{ request()->is('treasuries*') ? 'active' : '' }}" title="الخزائن والحسابات البنكية">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="landmark" size="18" /></span>
                    <span class="erp-nav-label">الخزائن والبنوك</span>
                </div>
            </a>
            <a href="{{ route('sales.payments.index') }}" class="erp-nav-item {{ request()->is('sales-payments*') ? 'active' : '' }}" title="سندات قبض ومتحصلات العملاء">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="banknote" size="18" /></span>
                    <span class="erp-nav-label">مقبوضات العملاء</span>
                </div>
            </a>
            <a href="{{ route('supplier-payments.index') }}" class="erp-nav-item {{ request()->is('supplier-payments*') ? 'active' : '' }}" title="مدفوعات وسندات صرف الموردين">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="credit-card" size="18" /></span>
                    <span class="erp-nav-label">مدفوعات الموردين</span>
                </div>
            </a>
            <a href="{{ route('expenses.index') }}" class="erp-nav-item {{ request()->is('expenses*') ? 'active' : '' }}" title="المصروفات التشغيلية والعمومية">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="receipt" size="18" /></span>
                    <span class="erp-nav-label">المصروفات الجارية</span>
                </div>
            </a>
            <a href="#" class="erp-nav-item opacity-60 cursor-not-allowed" title="قريباً: التقارير المالية والأرباح">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="trending-up" size="18" /></span>
                    <span class="erp-nav-label">التقارير المالية والأرباح</span>
                </div>
            </a>
        </div>
        @endcan

        <!-- Administration -->
        @can('manage-company-settings')
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">الإدارة والنظام</div>
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="erp-nav-item {{ request()->is('users*') ? 'active' : '' }}" title="إدارة المستخدمين">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="users" size="18" /></span>
                    <span class="erp-nav-label">المستخدمون وحسابات الدخول</span>
                </div>
            </a>
            @endcan
            @can('manage-roles')
            <a href="{{ route('roles.index') }}" class="erp-nav-item {{ request()->is('roles*') ? 'active' : '' }}" title="إدارة الأدوار ومصفوفة الصلاحيات">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="shield" size="18" /></span>
                    <span class="erp-nav-label">الأدوار ومصفوفة الصلاحيات</span>
                </div>
            </a>
            @endcan
            @can('manage-units')
            <a href="{{ route('units.index') }}" class="erp-nav-item {{ request()->is('units*') ? 'active' : '' }}" title="إدارة وحدات القياس">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="layers" size="18" /></span>
                    <span class="erp-nav-label">وحدات القياس والتحويل</span>
                </div>
            </a>
            @endcan
            <a href="{{ route('settings.index') }}" class="erp-nav-item {{ request()->is('settings*') ? 'active' : '' }}">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="settings" size="18" /></span>
                    <span class="erp-nav-label">إعدادات الشركة والنظام</span>
                </div>
            </a>
        </div>
        @endcan
    </nav>
</aside>
