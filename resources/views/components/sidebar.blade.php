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
        <!-- Overview -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">نظرة عامة</div>
            <a href="{{ url('/') }}" class="erp-nav-item {{ request()->is('/') ? 'active' : '' }}" title="لوحة التحكم">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="layout-dashboard" size="18" /></span>
                    <span class="erp-nav-label">لوحة التحكم</span>
                </div>
            </a>
            <a href="#" class="erp-nav-item" title="الأنشطة والمهام">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="zap" size="18" /></span>
                    <span class="erp-nav-label">الأنشطة والمهام</span>
                </div>
                <span class="erp-nav-badge">3</span>
            </a>
        </div>

        <!-- Sales -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المبيعات</div>
            <a href="#" class="erp-nav-item" title="عروض الأسعار">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="file-text" size="18" /></span>
                    <span class="erp-nav-label">عروض الأسعار</span>
                </div>
            </a>
            <a href="#" class="erp-nav-item" title="أوامر البيع">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="shopping-cart" size="18" /></span>
                    <span class="erp-nav-label">أوامر البيع</span>
                </div>
            </a>
            <a href="{{ route('customers.index') }}" class="erp-nav-item {{ request()->is('customers*') ? 'active' : '' }}" title="العملاء وتجار الجملة">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="users" size="18" /></span>
                    <span class="erp-nav-label">العملاء</span>
                </div>
            </a>
        </div>

        <!-- Purchases -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المشتريات والواردات</div>
            <a href="#" class="erp-nav-item" title="أوامر الشراء (الصين)">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="shopping-bag" size="18" /></span>
                    <span class="erp-nav-label">أوامر الشراء (الصين)</span>
                </div>
            </a>
            <a href="{{ route('suppliers.index') }}" class="erp-nav-item {{ request()->is('suppliers*') || request()->is('shipping-offices*') ? 'active' : '' }}" title="الموردون ومكاتب الشحن">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="factory" size="18" /></span>
                    <span class="erp-nav-label">الموردون ومكاتب الشحن</span>
                </div>
            </a>
        </div>

        <!-- Inventory -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المخزون والمنتجات</div>
            <a href="{{ url('/products') }}" class="erp-nav-item {{ request()->is('products*') ? 'active' : '' }}" title="المنتجات والأصناف">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="package" size="18" /></span>
                    <span class="erp-nav-label">المنتجات والأصناف</span>
                </div>
            </a>
            <a href="{{ route('categories.index') }}" class="erp-nav-item {{ request()->is('categories*') ? 'active' : '' }}" title="تصنيفات المنتجات">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="tag" size="18" /></span>
                    <span class="erp-nav-label">تصنيفات المنتجات</span>
                </div>
            </a>
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
            <a href="{{ route('inventory.low-stock') }}" class="erp-nav-item {{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}" title="نواقص المخزون">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="alert-triangle" size="18" /></span>
                    <span class="erp-nav-label">نواقص المخزون</span>
                </div>
                <span class="erp-nav-badge warning">تنبيه</span>
            </a>
        </div>

        <!-- Finance -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">المالية والحسابات</div>
            <a href="#" class="erp-nav-item">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="banknote" size="18" /></span>
                    <span class="erp-nav-label">مدفوعات العملاء</span>
                </div>
            </a>
            <a href="#" class="erp-nav-item">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="credit-card" size="18" /></span>
                    <span class="erp-nav-label">مدفوعات الموردين</span>
                </div>
            </a>
            <a href="#" class="erp-nav-item">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="receipt" size="18" /></span>
                    <span class="erp-nav-label">المصروفات الجارية</span>
                </div>
            </a>
            <a href="#" class="erp-nav-item">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="trending-up" size="18" /></span>
                    <span class="erp-nav-label">التقارير المالية والأرباح</span>
                </div>
            </a>
        </div>

        <!-- Administration -->
        <div class="erp-nav-section">
            <div class="erp-nav-section-title">الإدارة والنظام</div>
            <a href="#" class="erp-nav-item">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="shield" size="18" /></span>
                    <span class="erp-nav-label">المستخدمون والصلاحيات</span>
                </div>
            </a>
            <a href="{{ route('settings.index') }}" class="erp-nav-item {{ request()->is('settings*') ? 'active' : '' }}">
                <div class="erp-nav-content">
                    <span class="erp-nav-icon"><x-icon name="settings" size="18" /></span>
                    <span class="erp-nav-label">إعدادات الشركة والنظام</span>
                </div>
            </a>
        </div>
    </nav>
</aside>
