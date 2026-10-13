@extends('layouts.app')

@section('control_panel')
    <x-control-panel :title="$supplier->name" :breadcrumbs="['العملاء والموردون' => route('suppliers.index'), 'دليل الموردين' => route('suppliers.index'), $supplier->name => route('suppliers.show', $supplier)]">
        <x-slot:actions>
            <a href="{{ route('suppliers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>العودة لقائمة الموردين</span>
            </a>
            @if($supplier->phone)
                <a href="tel:{{ $supplier->phone }}" class="erp-btn erp-btn-secondary">
                    <x-icon name="phone" size="16" />
                    <span>اتصال</span>
                </a>
            @endif
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $supplier->is_active ? 'erp-badge-success' : 'erp-badge-danger' }} text-xs">
                {{ $supplier->is_active ? 'حساب نشط' : 'حساب معطل' }}
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="supplier-show">
        @if(session('success'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check" size="16" />
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Supplier Dossier Card -->
        <div class="supplier-profile-card">
            <div class="supplier-profile-header">
                <div>
                    <h2 class="supplier-profile-title">{{ $supplier->name }}</h2>
                    <div class="supplier-profile-sub flex items-center gap-2">
                        <span>كود المورد: #{{ $supplier->id }}</span>
                        <span>•</span>
                        @if($supplier->supplier_type === 'china_factory')
                            <span class="erp-badge erp-badge-danger text-xs">مصنع بالصين</span>
                        @elseif($supplier->supplier_type === 'shipping_agent')
                            <span class="erp-badge erp-badge-warning text-xs">وكيل شحن وتخليص</span>
                        @else
                            <span class="erp-badge erp-badge-info text-xs">مورد محلي</span>
                        @endif
                        <span>•</span>
                        <span class="font-bold text-[var(--text-main)]">{{ $supplier->country ?: 'China' }}</span>
                    </div>
                </div>

                <div class="text-start md:text-end">
                    <div class="text-xs text-[var(--text-muted)] font-semibold mb-1">الرصيد المالي المعلق</div>
                    <div class="font-numeric font-black text-2xl">
                        @if($supplier->current_balance != 0)
                            <span class="{{ $supplier->current_balance > 0 ? 'text-[var(--warning)]' : 'text-[var(--success)]' }}">
                                {{ number_format($supplier->current_balance, 2) }} {{ $supplier->currency }}
                            </span>
                        @else
                            <span class="text-[var(--text-muted)]">0.00 {{ $supplier->currency }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="supplier-details-grid">
                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">المسؤول للتواصل</div>
                    <div class="supplier-detail-val">
                        {{ $supplier->contact_person ?: 'غير محدد' }}
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">معرف WeChat</div>
                    <div class="supplier-detail-val font-numeric">
                        @if($supplier->wechat_id)
                            <span class="supplier-wechat-tag">
                                <x-icon name="smartphone" size="12" />
                                <span>{{ $supplier->wechat_id }}</span>
                            </span>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">الهاتف / الواتساب</div>
                    <div class="supplier-detail-val font-numeric">
                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" class="text-[var(--primary)] hover:underline direction-ltr inline-block">
                                {{ $supplier->phone }}
                            </a>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">البريد الإلكتروني</div>
                    <div class="supplier-detail-val font-numeric text-xs">
                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="text-[var(--primary)] hover:underline direction-ltr inline-block">
                                {{ $supplier->email }}
                            </a>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">المدينة / المقاطعة</div>
                    <div class="supplier-detail-val">
                        {{ $supplier->city ?: '—' }}
                    </div>
                </div>

                <div class="supplier-detail-item">
                    <div class="supplier-detail-label">عملة الحساب</div>
                    <div class="supplier-detail-val font-numeric">
                        {{ $supplier->currency }}
                    </div>
                </div>
            </div>

            @if($supplier->address)
                <div class="mt-4 p-3 bg-[var(--bg-surface-alt)] border border-[var(--border-light)] rounded">
                    <div class="text-xs font-bold text-[var(--text-secondary)] mb-1">العنوان التفصيلي وموقع المصنع:</div>
                    <div class="text-sm text-[var(--text-main)]">{{ $supplier->address }}</div>
                </div>
            @endif

            @if($supplier->notes)
                <div class="mt-3 p-3 bg-[var(--bg-surface-alt)] border border-[var(--border-light)] rounded">
                    <div class="text-xs font-bold text-[var(--text-secondary)] mb-1">ملاحظات وشروط التعامل:</div>
                    <div class="text-sm text-[var(--text-main)]">{{ $supplier->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Purchase Orders History Placeholder (Phase 8 Readiness) -->
        <div class="erp-card">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="shopping-bag" size="18" />
                    <span>سجل أوامر الشراء والاستيراد من الصين (جاهزية المرحلة الثامنة)</span>
                </div>
                <span class="erp-badge erp-badge-neutral text-xs">أوامر التوريد</span>
            </div>
            <div class="erp-card-body text-center py-8">
                <x-icon name="truck" size="40" class="text-[var(--text-muted)] mb-2 inline-block" />
                <h4 class="font-bold text-base m-0 mb-1">أوامر الشراء والحاويات البحرية</h4>
                <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto m-0">
                    سيتم تتبع تكاليف الحاويات باليوان/الدولار مع مصاريف الشحن والجمارك وتوزيع التكلفة على المنتجات فور تشغيل موديول المشتريات.
                </p>
            </div>
        </div>
    </div>
@endsection
