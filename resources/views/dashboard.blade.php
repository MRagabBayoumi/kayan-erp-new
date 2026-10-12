@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="لوحة التحكم" :breadcrumbs="[]">
        <x-slot:actions>
            <a href="{{ url('/products') }}" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>إضافة منتج جديد</span>
            </a>
            <button type="button" class="erp-btn erp-btn-secondary">
                <x-icon name="shopping-cart" size="16" />
                <span>فاتورة بيع جديدة</span>
            </button>
            <button type="button" class="erp-btn erp-btn-secondary">
                <x-icon name="shopping-bag" size="16" />
                <span>أمر شراء (الصين)</span>
            </button>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric">
                <x-icon name="calendar" size="14" />
                <span>{{ date('Y-m-d') }}</span>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Welcome Hero Banner -->
    <section class="welcome-banner">
        <div>
            <h2>مرحبًا بك في Kayan ERP</h2>
            <p>
                نظام إدارة العمليات المتكامل لشركة كيان — استيراد وتوزيع إكسسوارات حريمي، توك شعر، مستلزمات زينة وهدايا من الصين إلى السوق المحلي.
            </p>
        </div>
        <div class="welcome-banner-symbol">
            <x-icon name="trending-up" size="48" />
        </div>
    </section>

    <!-- KPI Section Heading -->
    <div class="section-title-bar">
        <h3>مؤشرات الأداء الرئيسية (KPIs)</h3>
        <small>البيانات الحية ستظهر فور تسجيل المعاملات في الوحدات</small>
    </div>

    <!-- 4 KPI Summary Cards -->
    <section class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">مبيعات اليوم</span>
                <span class="kpi-icon"><x-icon name="banknote" size="20" /></span>
            </div>
            <div class="kpi-value font-numeric">— <span style="font-size: 14px; font-weight: normal; color: var(--text-muted);">ج.م</span></div>
            <div class="kpi-note font-numeric">
                <span style="color: var(--text-muted);">إجمالي الفواتير الصادرة</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">مشتريات واستيراد</span>
                <span class="kpi-icon"><x-icon name="shopping-bag" size="20" /></span>
            </div>
            <div class="kpi-value font-numeric">— <span style="font-size: 14px; font-weight: normal; color: var(--text-muted);">ج.م</span></div>
            <div class="kpi-note font-numeric">
                <span style="color: var(--text-muted);">طلبيات الصين والموردين</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">الأصناف والمنتجات</span>
                <span class="kpi-icon"><x-icon name="package" size="20" /></span>
            </div>
            <div class="kpi-value font-numeric">— <span style="font-size: 14px; font-weight: normal; color: var(--text-muted);">صنف</span></div>
            <div class="kpi-note">
                <a href="{{ url('/products') }}" style="color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    <span>استعراض شاشة المنتجات</span>
                    <x-icon name="arrow-left" size="14" />
                </a>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">تجار الجملة والعملاء</span>
                <span class="kpi-icon"><x-icon name="users" size="20" /></span>
            </div>
            <div class="kpi-value font-numeric">— <span style="font-size: 14px; font-weight: normal; color: var(--text-muted);">عميل</span></div>
            <div class="kpi-note">
                <span style="color: var(--text-muted);">حسابات العملاء النشطة</span>
            </div>
        </div>
    </section>

    <!-- Operational Follow-up & Shortcuts -->
    <div class="dashboard-lower-grid">
        <!-- System Updates & Readiness Panel -->
        <div class="erp-card">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="zap" size="18" />
                    <span>جاهزية وتحديثات النظام</span>
                </div>
                <span class="erp-badge erp-badge-success">قيد التشغيل</span>
            </div>
            <div class="erp-card-body">
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-item-icon" style="background-color: var(--success-soft); color: var(--success);">
                            <x-icon name="check" size="14" />
                        </div>
                        <div>
                            <div class="activity-item-title">قاعدة البيانات الرسمية نشطة</div>
                            <div class="activity-item-desc">الاتصال مفعل ومؤكد على قاعدة البيانات <code>kayan_erp</code> (MySQL 8).</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-item-icon" style="background-color: var(--primary-soft); color: var(--primary);">
                            <x-icon name="sliders" size="14" />
                        </div>
                        <div>
                            <div class="activity-item-title">واجهة Odoo Web Client التفاعلية</div>
                            <div class="activity-item-desc">تم تفعيل السمات الفاتحة والداكنة، ومطلق التطبيقات 3×3، ووضع الكثافة المؤسسي.</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-item-icon" style="background-color: var(--info-soft); color: var(--info);">
                            <x-icon name="package" size="14" />
                        </div>
                        <div>
                            <div class="activity-item-title">المرحلة القادمة: شاشة المنتجات والمخزون</div>
                            <div class="activity-item-desc">عرض الأصناف بالباركود، كود الموديل، سعر التكلفة والبيع، ومستويات حد الطلب.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Business Actions Panel -->
        <div class="erp-card">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="zap" size="18" />
                    <span>اختصارات العمل السريع</span>
                </div>
            </div>
            <div class="erp-card-body">
                <a href="{{ url('/products') }}" class="quick-action-link">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <x-icon name="package" size="16" />
                        <span>إدارة قائمة المنتجات والأصناف</span>
                    </span>
                    <x-icon name="arrow-left" size="14" />
                </a>

                <a href="#" class="quick-action-link">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <x-icon name="shopping-cart" size="16" />
                        <span>تسجيل فاتورة بيع جملة</span>
                    </span>
                    <x-icon name="arrow-left" size="14" />
                </a>

                <a href="#" class="quick-action-link">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <x-icon name="shopping-bag" size="16" />
                        <span>تسجيل أمر شراء وشحن من الصين</span>
                    </span>
                    <x-icon name="arrow-left" size="14" />
                </a>

                <a href="#" class="quick-action-link">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <x-icon name="banknote" size="16" />
                        <span>تسجيل سند قبض من عميل</span>
                    </span>
                    <x-icon name="arrow-left" size="14" />
                </a>

                <a href="#" class="quick-action-link">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <x-icon name="alert-triangle" size="16" />
                        <span>تقرير نواقص المخزون وحد الطلب</span>
                    </span>
                    <x-icon name="arrow-left" size="14" />
                </a>
            </div>
        </div>
    </div>
@endsection
