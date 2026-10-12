<header class="erp-topbar">
    <div class="erp-topbar-start">
        <!-- Sidebar Toggle (Mobile Off-canvas Drawer Trigger) -->
        <button class="erp-top-btn erp-sidebar-toggle-btn" id="sidebarToggleBtn" type="button" title="القائمة الجانبية" aria-label="تبديل القائمة الجانبية">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Odoo 3x3 App Launcher Trigger -->
        <button class="erp-app-launcher-btn" type="button" data-action="open-app-launcher" title="قائمة التطبيقات">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="3" width="4" height="4" rx="1"/>
                <rect x="10" y="3" width="4" height="4" rx="1"/>
                <rect x="17" y="3" width="4" height="4" rx="1"/>
                <rect x="3" y="10" width="4" height="4" rx="1"/>
                <rect x="10" y="10" width="4" height="4" rx="1"/>
                <rect x="17" y="10" width="4" height="4" rx="1"/>
                <rect x="3" y="17" width="4" height="4" rx="1"/>
                <rect x="10" y="17" width="4" height="4" rx="1"/>
                <rect x="17" y="17" width="4" height="4" rx="1"/>
            </svg>
        </button>

        <!-- Brand Name -->
        <a href="{{ url('/') }}" class="erp-brand">
            <span class="erp-brand-badge">K</span>
            <span>Kayan ERP</span>
        </a>

        <div class="erp-topbar-divider"></div>

        <span class="erp-module-badge">{{ $moduleName ?? 'لوحة التحكم' }}</span>
    </div>

    <div class="erp-topbar-end">
        <!-- Global Search Input with Shortcut -->
        <div class="erp-global-search">
            <input type="search" id="globalSearchInput" class="erp-global-search-input" placeholder="بحث شامل في النظام...">
            <span class="erp-global-search-icon"><x-icon name="search" size="14" /></span>
            <span class="erp-search-kbd">Ctrl+K</span>
        </div>

        <!-- Density Switcher (Comfortable vs Enterprise Table View) -->
        <button class="erp-top-btn" id="kayanDensityToggle" type="button" title="تبديل كثافة العرض (مؤسسي / افتراضي)">
            <x-icon name="sliders-horizontal" size="14" />
            <span id="densityLabel" style="font-size: 11px;">افتراضي مريح</span>
        </button>

        <!-- Theme Switcher (Light / Dark) -->
        <button class="erp-top-btn" id="kayanThemeToggle" type="button" title="تبديل الوضع الليلي / النهاري">
            <span id="themeSunIcon" style="display: none;"><x-icon name="sun" size="16" /></span>
            <span id="themeMoonIcon" style="display: inline-flex;"><x-icon name="moon" size="16" /></span>
        </button>

        <!-- Notifications -->
        <div class="erp-dropdown">
            <button class="erp-top-btn" type="button" data-dropdown-toggle="notificationsMenu" title="الإشعارات والأنشطة">
                <x-icon name="bell" size="16" />
                <span class="erp-top-badge">3</span>
            </button>
            <div id="notificationsMenu" class="erp-dropdown-menu" style="width: 280px; left: 0;">
                <div class="erp-dropdown-header">
                    <strong style="font-size: 12px; color: var(--text-main);">الإشعارات والأنشطة</strong>
                </div>
                <a href="#" class="erp-dropdown-item">
                    <span style="color: var(--warning); display: inline-flex; align-items: center;"><x-icon name="alert-triangle" size="15" /></span>
                    <div>
                        <div style="font-weight: 600;">نواقص مخزون إكسسوارات</div>
                        <small style="color: var(--text-muted); font-size: 10px;">5 أصناف بلغت الحد الأدنى</small>
                    </div>
                </a>
                <a href="#" class="erp-dropdown-item">
                    <span style="color: var(--info); display: inline-flex; align-items: center;"><x-icon name="package" size="15" /></span>
                    <div>
                        <div style="font-weight: 600;">شحنة واردة من الصين</div>
                        <small style="color: var(--text-muted); font-size: 10px;">في مرحلة التخليص الجمركي</small>
                    </div>
                </a>
                <a href="#" class="erp-dropdown-item">
                    <span style="color: var(--success); display: inline-flex; align-items: center;"><x-icon name="check" size="15" /></span>
                    <div>
                        <div style="font-weight: 600;">قاعدة البيانات جاهزة</div>
                        <small style="color: var(--text-muted); font-size: 10px;">متصل بنجاح مع kayan_erp</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="erp-dropdown">
            <button class="erp-user-dropdown-btn" type="button" data-dropdown-toggle="userProfileMenu">
                <span class="erp-avatar">ك</span>
                <span>مدير النظام</span>
                <x-icon name="chevron-down" size="12" style="opacity: 0.8;" />
            </button>
            <div id="userProfileMenu" class="erp-dropdown-menu">
                <div class="erp-dropdown-header">
                    <div style="font-weight: 700; color: var(--text-main);">مدير النظام</div>
                    <small style="color: var(--text-muted);">مسؤول إدارة كيان ERP</small>
                </div>
                <a href="#" class="erp-dropdown-item">
                    <x-icon name="user" size="14" />
                    <span>الملف الشخصي</span>
                </a>
                <a href="#" class="erp-dropdown-item">
                    <x-icon name="settings" size="14" />
                    <span>إعدادات الحساب</span>
                </a>
                <div class="erp-dropdown-divider"></div>
                <a href="#" class="erp-dropdown-item" style="color: var(--danger);">
                    <x-icon name="log-out" size="14" />
                    <span>تسجيل الخروج</span>
                </a>
            </div>
        </div>
    </div>
</header>
