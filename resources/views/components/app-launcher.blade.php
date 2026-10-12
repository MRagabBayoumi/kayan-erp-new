<!-- Odoo-Style App Launcher Modal -->
<div id="appLauncherBackdrop" class="erp-app-launcher-backdrop" aria-hidden="true">
    <div class="erp-app-launcher-card" role="dialog" aria-modal="true" aria-labelledby="launcherTitle">
        <div class="erp-launcher-header">
            <div class="erp-launcher-title" id="launcherTitle">
                <x-icon name="layout-grid" size="18" />
                <span>تطبيقات Kayan ERP</span>
            </div>
            <button id="appLauncherClose" type="button" class="erp-btn erp-btn-ghost erp-btn-icon" title="إغلاق (Esc)">
                <x-icon name="x" size="16" />
            </button>
        </div>

        <div class="erp-launcher-grid">
            <!-- Dashboard -->
            <a href="{{ url('/') }}" class="erp-launcher-item">
                <div class="erp-launcher-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                    <x-icon name="layout-dashboard" size="26" strokeWidth="1.75" />
                </div>
                <div class="erp-launcher-label">لوحة التحكم</div>
                <div class="erp-launcher-desc">المؤشرات العامة وملخص النشاط التجاري</div>
            </a>

            <!-- Products & Inventory -->
            <a href="{{ url('/products') }}" class="erp-launcher-item">
                <div class="erp-launcher-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                    <x-icon name="package" size="26" strokeWidth="1.75" />
                </div>
                <div class="erp-launcher-label">المخزون والمنتجات</div>
                <div class="erp-launcher-desc">إكسسوارات حريمي، توك شعر، باركود، وجرد</div>
            </a>

            <!-- Sales & Customers -->
            <a href="#" class="erp-launcher-item">
                <div class="erp-launcher-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                    <x-icon name="shopping-cart" size="26" strokeWidth="1.75" />
                </div>
                <div class="erp-launcher-label">المبيعات وتجار الجملة</div>
                <div class="erp-launcher-desc">عروض أسعار، فواتير بيع، وحسابات العملاء</div>
            </a>

            <!-- Purchases & Imports -->
            <a href="#" class="erp-launcher-item">
                <div class="erp-launcher-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                    <x-icon name="shopping-bag" size="26" strokeWidth="1.75" />
                </div>
                <div class="erp-launcher-label">المشتريات والاستيراد</div>
                <div class="erp-launcher-desc">طلبيات الصين، الشحن، الجمارك، والموردون</div>
            </a>

            <!-- Finance & Cash -->
            <a href="#" class="erp-launcher-item">
                <div class="erp-launcher-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                    <x-icon name="banknote" size="26" strokeWidth="1.75" />
                </div>
                <div class="erp-launcher-label">الخزينة والمالية</div>
                <div class="erp-launcher-desc">المقبوضات، المدفوعات، والمصروفات والأرباح</div>
            </a>

            <!-- Settings -->
            <a href="{{ route('settings.index') }}" class="erp-launcher-item">
                <div class="erp-launcher-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                    <x-icon name="settings" size="26" strokeWidth="1.75" />
                </div>
                <div class="erp-launcher-label">الإعدادات والصلاحيات</div>
                <div class="erp-launcher-desc">بيانات الشركة، الفروع، والمستخدمين</div>
            </a>
        </div>
    </div>
</div>
