@extends('layouts.app')

@section('control_panel')
    <x-control-panel :title="$customer->name" :breadcrumbs="['العملاء والموردون' => route('customers.index'), 'دليل العملاء' => route('customers.index'), $customer->name => route('customers.show', $customer)]">
        <x-slot:actions>
            <a href="{{ route('customers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>العودة لقائمة العملاء</span>
            </a>
            <a href="tel:{{ $customer->phone }}" class="erp-btn erp-btn-secondary">
                <x-icon name="phone" size="16" />
                <span>اتصال بالعميل</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $customer->is_active ? 'erp-badge-success' : 'erp-badge-danger' }} text-xs">
                {{ $customer->is_active ? 'حساب نشط' : 'حساب معطل' }}
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="customer-show">
        @if(session('success'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check" size="16" />
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Customer Dossier Card -->
        <div class="customer-profile-card">
            <div class="customer-profile-header">
                <div>
                    <h2 class="customer-profile-title">{{ $customer->name }}</h2>
                    <div class="customer-profile-sub flex items-center gap-2">
                        @if($customer->company_name)
                            <span class="font-medium text-[var(--text-main)]">{{ $customer->company_name }}</span>
                            <span>•</span>
                        @endif
                        <span>كود العميل: #{{ $customer->id }}</span>
                        <span>•</span>
                        @if($customer->customer_type === 'wholesale')
                            <span class="erp-badge erp-badge-success text-xs">تاجر جملة</span>
                        @elseif($customer->customer_type === 'distributor')
                            <span class="erp-badge erp-badge-warning text-xs">موزع</span>
                        @else
                            <span class="erp-badge erp-badge-info text-xs">قطاعي</span>
                        @endif
                    </div>
                </div>

                <div class="text-start md:text-end">
                    <div class="text-xs text-[var(--text-muted)] font-semibold mb-1">الرصيد المالي الحالي</div>
                    <div class="font-numeric font-black text-2xl">
                        @if($customer->current_balance > 0)
                            <span class="text-[var(--danger)]">
                                {{ number_format($customer->current_balance, 2) }} ج.م
                                <span class="text-xs font-normal block text-[var(--text-muted)]">(مستحق على العميل)</span>
                            </span>
                        @elseif($customer->current_balance < 0)
                            <span class="text-[var(--success)]">
                                {{ number_format(abs($customer->current_balance), 2) }} ج.م
                                <span class="text-xs font-normal block text-[var(--text-muted)]">(رصيد دائن لصالح العميل)</span>
                            </span>
                        @else
                            <span class="text-[var(--text-muted)]">0.00 ج.م</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="customer-details-grid">
                <div class="customer-detail-item">
                    <div class="customer-detail-label">الهاتف الأساسي</div>
                    <div class="customer-detail-val font-numeric">
                        <a href="tel:{{ $customer->phone }}" class="text-[var(--primary)] hover:underline direction-ltr inline-block">
                            {{ $customer->phone }}
                        </a>
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">الهاتف البديل</div>
                    <div class="customer-detail-val font-numeric">
                        {{ $customer->phone_alt ?: 'غير مسجل' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">المحافظة / المدينة</div>
                    <div class="customer-detail-val">
                        {{ $customer->city ?: 'غير محدد' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">فئة التسعير</div>
                    <div class="customer-detail-val">
                        {{ $customer->pricing_tier === 'wholesale' ? 'سعر الجملة' : 'سعر القطاعي' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">حد الائتمان المالي</div>
                    <div class="customer-detail-val font-numeric">
                        {{ $customer->credit_limit > 0 ? number_format($customer->credit_limit, 2) . ' ج.م' : 'بدون سقف ائتماني' }}
                    </div>
                </div>

                <div class="customer-detail-item">
                    <div class="customer-detail-label">العنوان التفصيلي</div>
                    <div class="customer-detail-val text-xs">
                        {{ $customer->address ?: 'لا يوجد عنوان مسجل' }}
                    </div>
                </div>
            </div>

            @if($customer->notes)
                <div class="mt-4 p-3 bg-[var(--bg-surface-alt)] border border-[var(--border-light)] rounded">
                    <div class="text-xs font-bold text-[var(--text-secondary)] mb-1">ملاحظات العميل:</div>
                    <div class="text-sm text-[var(--text-main)]">{{ $customer->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Ledger & Future Transactions Tab / Card -->
        <div class="erp-card">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="calculator" size="18" />
                    <span>كشف حساب وحركات الفواتير (جاهزية المرحلة السابعة)</span>
                </div>
                <span class="erp-badge erp-badge-neutral text-xs">مبيعات وفواتير</span>
            </div>
            <div class="erp-card-body text-center py-8">
                <x-icon name="shopping-bag" size="40" class="text-[var(--text-muted)] mb-2 inline-block" />
                <h4 class="font-bold text-base m-0 mb-1">كشف الحساب وسجل المبيعات</h4>
                <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto m-0">
                    سيتم إدراج فواتير المبيعات، سندات القبض والدفع، ومرتجعات المبيعات تلقائياً فور تشغيل وحدة فواتير الجملة والباركود.
                </p>
            </div>
        </div>
    </div>
@endsection
