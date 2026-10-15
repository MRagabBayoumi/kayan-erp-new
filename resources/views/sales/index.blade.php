@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="فواتير المبيعات والعملاء" :breadcrumbs="['المبيعات' => '#', 'فواتير المبيعات' => '#']">
        <x-slot:actions>
            <a href="{{ route('sales.create') }}" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>فاتورة بيع جديدة</span>
            </a>
            @can('manage-purchases')
            <a href="{{ route('customers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="users" size="16" />
                <span>دليل العملاء</span>
            </a>
            @endcan
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric">
                <x-icon name="shopping-cart" size="14" />
                <span>إجمالي الفواتير: {{ $metrics['total_count'] }}</span>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="sales-index">
        <!-- Status Alerts -->
        @if(session('status') === 'success')
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check-circle" size="18" />
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="erp-alert erp-alert-danger mb-4" role="alert">
                <x-icon name="alert-triangle" size="18" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Summary KPI Card -->
        <div class="erp-card mb-4 p-4">
            <div class="purchases-kpi-row">
                <div class="purchases-kpi-stat">
                    <div class="purchases-kpi-icon-wrap">
                        <x-icon name="shopping-cart" size="22" />
                    </div>
                    <div class="purchases-kpi-info">
                        <div class="purchases-kpi-label">إجمالي فواتير المبيعات</div>
                        <div class="purchases-kpi-value font-numeric">{{ $metrics['total_count'] }}</div>
                        <div class="purchases-kpi-desc">جميع فواتير البيع المسجلة</div>
                    </div>
                </div>

                <div class="purchases-kpi-stat">
                    <div class="purchases-kpi-icon-wrap success">
                        <x-icon name="check-circle" size="22" />
                    </div>
                    <div class="purchases-kpi-info">
                        <div class="purchases-kpi-label">مبيعات مُرحلة (إيرادات مؤكدة)</div>
                        <div class="purchases-kpi-value font-numeric text-[var(--success)]">{{ number_format($metrics['posted_total'], 2) }} ج.م</div>
                        <div class="purchases-kpi-desc">تم صرفها وفق تسعير وتكلفة FIFO</div>
                    </div>
                </div>

                @can('view-cogs')
                <div class="purchases-kpi-stat">
                    <div class="purchases-kpi-icon-wrap">
                        <x-icon name="trending-up" size="22" />
                    </div>
                    <div class="purchases-kpi-info">
                        <div class="purchases-kpi-label">إجمالي الأرباح المحققة</div>
                        <div class="purchases-kpi-value font-numeric text-[var(--primary)]">{{ number_format($metrics['total_profit'], 2) }} ج.م</div>
                        <div class="purchases-kpi-desc">الإيرادات مطروحاً منها تكلفة البضاعة</div>
                    </div>
                </div>
                @else
                <div class="purchases-kpi-stat">
                    <div class="purchases-kpi-icon-wrap warning">
                        <x-icon name="file-text" size="22" />
                    </div>
                    <div class="purchases-kpi-info">
                        <div class="purchases-kpi-label">مسودات قيد المراجعة</div>
                        <div class="purchases-kpi-value font-numeric text-[var(--warning)]">{{ $metrics['draft_count'] }}</div>
                        <div class="purchases-kpi-desc">فواتير لم يتم صرفها من المخزن</div>
                    </div>
                </div>
                @endcan
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="erp-card mb-4 p-3">
            <form action="{{ route('sales.index') }}" method="GET" class="erp-filter-form">
                <div class="flex-1 min-w-[220px]">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $currentSearch }}" 
                            class="erp-input w-full pr-8" 
                            placeholder="بحث برقم الفاتورة أو اسم العميل..."
                        >
                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none">
                            <x-icon name="search" size="15" />
                        </span>
                    </div>
                </div>

                <div class="w-40 min-w-[140px]">
                    <select name="status" class="erp-input w-full">
                        <option value="">-- كل الحالات --</option>
                        <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="posted" {{ $currentStatus === 'posted' ? 'selected' : '' }}>مرحلة (تم الصرف)</option>
                        <option value="voided" {{ $currentStatus === 'voided' ? 'selected' : '' }}>ملغاة ومسترجعة</option>
                    </select>
                </div>

                <div class="w-40 min-w-[140px]">
                    <select name="price_tier" class="erp-input w-full">
                        <option value="">-- فئة التسعير --</option>
                        <option value="retail" {{ $currentPriceTier === 'retail' ? 'selected' : '' }}>قطاعي</option>
                        <option value="wholesale" {{ $currentPriceTier === 'wholesale' ? 'selected' : '' }}>جملة</option>
                        <option value="distributor" {{ $currentPriceTier === 'distributor' ? 'selected' : '' }}>موزع / كبار العملاء</option>
                    </select>
                </div>

                <div class="w-48 min-w-[160px]">
                    <select name="warehouse_id" class="erp-input w-full">
                        <option value="">-- كل المخازن --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$currentWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-48 min-w-[160px]">
                    <select name="customer_id" class="erp-input w-full">
                        <option value="">-- كل العملاء --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (string)$currentCustomerId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="erp-btn erp-btn-secondary">
                        <x-icon name="filter" size="14" />
                        <span>تصفية</span>
                    </button>
                    @if($currentSearch || $currentStatus || $currentWarehouseId || $currentCustomerId || $currentPriceTier)
                        <a href="{{ route('sales.index') }}" class="erp-btn erp-btn-ghost text-xs" title="إلغاء الفلاتر">
                            <x-icon name="x" size="14" />
                            <span>إعادة تعيين</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="erp-card">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>تاريخ الفاتورة</th>
                                <th>العميل</th>
                                <th>المخزن المنصرف منه</th>
                                <th>فئة السعر</th>
                                <th>عدد البنود</th>
                                <th>إجمالي الفاتورة</th>
                                @can('view-cogs')
                                <th>التكلفة (COGS)</th>
                                <th>صافي الربح</th>
                                @endcan
                                <th>الحالة</th>
                                <th class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('sales.show', $inv) }}" class="font-numeric font-bold text-[var(--primary)] hover:underline">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="font-numeric text-xs">
                                        {{ \Carbon\Carbon::parse($inv->invoice_date)->format('Y-m-d') }}
                                    </td>
                                    <td>
                                        <div class="font-semibold text-[var(--text-main)]">{{ $inv->customer->name ?? 'غير محدد' }}</div>
                                        @if(!empty($inv->customer->phone))
                                            <div class="text-[11px] text-[var(--text-muted)] font-numeric">{{ $inv->customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-xs">{{ $inv->warehouse->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($inv->price_tier === 'distributor')
                                            <span class="erp-badge erp-badge-neutral text-xs">موزع</span>
                                        @elseif($inv->price_tier === 'wholesale')
                                            <span class="erp-badge erp-badge-neutral text-xs">جملة</span>
                                        @else
                                            <span class="erp-badge erp-badge-neutral text-xs">قطاعي</span>
                                        @endif
                                    </td>
                                    <td class="font-numeric text-center">
                                        {{ $inv->items->count() }}
                                    </td>
                                    <td class="font-numeric font-bold text-[var(--text-main)]">
                                        {{ number_format((float)$inv->total_amount, 2) }} ج.م
                                    </td>
                                    @can('view-cogs')
                                    <td class="font-numeric text-xs text-[var(--text-muted)]">
                                        {{ number_format((float)$inv->total_cogs, 2) }} ج.م
                                    </td>
                                    <td class="font-numeric text-xs font-bold {{ $inv->total_profit >= 0 ? 'text-[var(--success)]' : 'text-[var(--danger)]' }}">
                                        {{ number_format((float)$inv->total_profit, 2) }} ج.م
                                    </td>
                                    @endcan
                                    <td>
                                        <span class="erp-badge {{ $inv->status_badge_class }}">
                                            {{ $inv->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('sales.show', $inv) }}" class="erp-btn erp-btn-ghost erp-btn-sm" title="عرض التفاصيل">
                                                <x-icon name="eye" size="14" />
                                            </a>
                                            <a href="{{ route('sales.print', [$inv, 'format' => 'a4']) }}" target="_blank" class="erp-btn erp-btn-ghost erp-btn-sm" title="طباعة A4">
                                                <x-icon name="printer" size="14" />
                                            </a>
                                            <a href="{{ route('sales.print', [$inv, 'format' => 'thermal_80mm']) }}" target="_blank" class="erp-btn erp-btn-ghost erp-btn-sm" title="طباعة 80 مم">
                                                <x-icon name="file-text" size="14" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($invoices->hasPages())
                    <div class="p-3 border-t border-[var(--border-light)]">
                        {{ $invoices->links() }}
                    </div>
                @endif
            @else
                <div class="erp-empty-state p-10 text-center">
                    <div class="erp-empty-icon mb-3 text-[var(--text-muted)]">
                        <x-icon name="shopping-cart" size="48" />
                    </div>
                    <div class="font-bold text-base text-[var(--text-main)] mb-1">لا توجد فواتير مبيعات مسجلة</div>
                    <p class="text-xs text-[var(--text-muted)] mb-4">ابدأ بإنشاء أول فاتورة مبيعات لصرف المنتجات وتطبيق تسعير ودفعات FIFO آلياً.</p>
                    <a href="{{ route('sales.create') }}" class="erp-btn erp-btn-primary">
                        <x-icon name="plus" size="16" />
                        <span>إنشاء فاتورة مبيعات جديدة</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
