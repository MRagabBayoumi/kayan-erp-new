@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="تفاصيل الوردية: {{ $shift->shift_number }}" :breadcrumbs="['المبيعات' => route('sales.index'), 'ورديات الكاشير' => route('shifts.index'), $shift->shift_number => '#']">
        <x-slot:actions>
            <a href="{{ route('shifts.print', $shift) }}" target="_blank" class="erp-btn erp-btn-secondary">
                <x-icon name="printer" size="16" />
                <span>طباعة التقرير</span>
            </a>
            @if($shift->status === 'open' && (auth()->id() === $shift->user_id || auth()->user()->isAdmin()))
                <button type="button" id="closeThisShiftBtn" class="erp-btn erp-btn-danger">
                    <x-icon name="lock" size="16" />
                    <span>إغلاق الوردية وجرد الدرج</span>
                </button>
            @endif
            <a href="{{ route('shifts.index') }}" class="erp-btn erp-btn-ghost">
                <x-icon name="arrow-right" size="16" />
                <span>العودة للورديات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            @if($shift->status === 'open')
                <span class="shift-status-badge open">وردية نشطة (مفتوحة)</span>
            @else
                <span class="shift-status-badge closed">وردية مغلقة</span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="shift-show">
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

        <!-- 1. Key Metrics Cards -->
        <div class="shift-summary-grid">
            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-blue-500/10 text-blue-500">
                    <x-icon name="credit-card" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">رصيد بداية العهدة</div>
                    <div class="shift-kpi-value font-numeric">{{ number_format($shift->opening_balance, 2) }} <span class="text-xs font-normal">ج.م</span></div>
                </div>
            </div>

            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-emerald-500/10 text-emerald-500">
                    <x-icon name="shopping-cart" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">إجمالي المبيعات النقدية</div>
                    <div class="shift-kpi-value font-numeric text-emerald-500">{{ number_format($shift->cash_sales_amount, 2) }} <span class="text-xs font-normal">ج.م</span></div>
                </div>
            </div>

            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-purple-500/10 text-purple-500">
                    <x-icon name="calculator" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">الرصيد الدفتري المتوقع</div>
                    <div class="shift-kpi-value font-numeric">{{ number_format($shift->expected_closing_balance, 2) }} <span class="text-xs font-normal">ج.م</span></div>
                </div>
            </div>

            <div class="shift-kpi-card">
                <div class="shift-kpi-icon {{ $shift->difference < 0 ? 'bg-red-500/10 text-red-500' : ($shift->difference > 0 ? 'bg-blue-500/10 text-blue-500' : 'bg-emerald-500/10 text-emerald-500') }}">
                    <x-icon name="scale" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">جرد الدرج الفعلي / الفارق</div>
                    <div class="shift-kpi-value font-numeric">
                        @if($shift->actual_closing_balance !== null)
                            {{ number_format($shift->actual_closing_balance, 2) }} <span class="text-xs font-normal">ج.م</span>
                            <div class="text-xs mt-1">
                                @if($shift->difference == 0)
                                    <span class="text-emerald-500 font-bold">مطابق تماماً (0)</span>
                                @elseif($shift->difference > 0)
                                    <span class="text-blue-500 font-bold">زيادة: +{{ number_format($shift->difference, 2) }} ج.م</span>
                                @else
                                    <span class="text-red-500 font-bold">عجز: {{ number_format($shift->difference, 2) }} ج.م</span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-[var(--text-muted)] font-normal">الوردية لا تزال مفتوحة</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <!-- Shift Info Card -->
            <div class="erp-card p-4">
                <h3 class="font-bold text-sm text-[var(--text-main)] mb-3 pb-2 border-b border-[var(--border-color)] flex items-center gap-2">
                    <x-icon name="info" size="16" class="text-[var(--primary)]" />
                    <span>بيانات الوردية</span>
                </h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-[var(--border-color)]">
                        <span class="text-[var(--text-muted)]">الكاشير:</span>
                        <strong class="text-[var(--text-main)]">{{ $shift->user?->name }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[var(--border-color)]">
                        <span class="text-[var(--text-muted)]">المستودع / الفرع:</span>
                        <strong class="text-[var(--text-main)]">{{ $shift->warehouse?->name }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[var(--border-color)]">
                        <span class="text-[var(--text-muted)]">تاريخ ووقت الفتح:</span>
                        <span class="font-numeric">{{ $shift->opened_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[var(--border-color)]">
                        <span class="text-[var(--text-muted)]">تاريخ ووقت الإغلاق:</span>
                        <span class="font-numeric">{{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : 'لا تزال مفتوحة' }}</span>
                    </div>
                    @if($shift->closedBy)
                        <div class="flex justify-between py-1 border-b border-[var(--border-color)]">
                            <span class="text-[var(--text-muted)]">أغلق بواسطة:</span>
                            <span class="font-bold text-[var(--text-main)]">{{ $shift->closedBy->name }}</span>
                        </div>
                    @endif
                    @if($shift->opening_notes)
                        <div class="py-1">
                            <span class="text-[var(--text-muted)] block mb-1">ملاحظات الافتتاح:</span>
                            <p class="bg-[var(--surface-muted)] p-2 rounded text-[11px] text-[var(--text-main)] m-0">{{ $shift->opening_notes }}</p>
                        </div>
                    @endif
                    @if($shift->closing_notes)
                        <div class="py-1">
                            <span class="text-[var(--text-muted)] block mb-1">ملاحظات الإغلاق:</span>
                            <p class="bg-[var(--surface-muted)] p-2 rounded text-[11px] text-[var(--text-main)] m-0">{{ $shift->closing_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Calculation Breakdown -->
            <div class="erp-card p-4 lg:col-span-2">
                <h3 class="font-bold text-sm text-[var(--text-main)] mb-3 pb-2 border-b border-[var(--border-color)] flex items-center gap-2">
                    <x-icon name="dollar-sign" size="16" class="text-emerald-500" />
                    <span>المعادلة الحسابية للدرج (Cash Drawer Balance)</span>
                </h3>

                <div class="shift-calc-box mt-0">
                    <div class="shift-calc-row">
                        <span>(+) رصيد بداية العهدة النقدية (Opening Cash)</span>
                        <span class="font-numeric font-bold">{{ number_format($shift->opening_balance, 2) }} ج.م</span>
                    </div>
                    <div class="shift-calc-row text-emerald-500">
                        <span>(+) إجمالي المبيعات النقدية الصادرة بالوردية (Cash Sales)</span>
                        <span class="font-numeric font-bold">+{{ number_format($shift->cash_sales_amount, 2) }} ج.م</span>
                    </div>
                    <div class="shift-calc-row text-blue-500">
                        <span>(+) إيداعات نقدية إضافية بالدرج (Cash Additions)</span>
                        <span class="font-numeric font-bold">+{{ number_format($shift->cash_additions, 2) }} ج.م</span>
                    </div>
                    <div class="shift-calc-row text-amber-500">
                        <span>(−) مرتجعات نقدية منصرفة من الدرج (Cash Returns)</span>
                        <span class="font-numeric font-bold">−{{ number_format($shift->cash_returns_amount, 2) }} ج.م</span>
                    </div>
                    <div class="shift-calc-row text-red-500">
                        <span>(−) مسحوبات ومصروفات نثرية منصرفة (Cash Expenses)</span>
                        <span class="font-numeric font-bold">−{{ number_format($shift->cash_expenses, 2) }} ج.م</span>
                    </div>
                    <div class="shift-calc-row text-[var(--text-main)]">
                        <span>(=) الرصيد الدفتري المتوقع بالدرج (Expected Cash)</span>
                        <span class="font-numeric text-emerald-500">{{ number_format($shift->expected_closing_balance, 2) }} ج.م</span>
                    </div>
                    @if($shift->actual_closing_balance !== null)
                        <div class="shift-calc-row text-[var(--text-main)]">
                            <span>الجرد الفعلي المحسوب عند الإغلاق (Actual Cash)</span>
                            <span class="font-numeric font-bold">{{ number_format($shift->actual_closing_balance, 2) }} ج.م</span>
                        </div>
                        <div class="shift-calc-row {{ $shift->difference < 0 ? 'text-red-500' : ($shift->difference > 0 ? 'text-blue-500' : 'text-emerald-500') }}">
                            <span>صافي الفارق (الزيادة / العجز)</span>
                            <span class="font-numeric font-bold">
                                {{ $shift->difference > 0 ? '+' : '' }}{{ number_format($shift->difference, 2) }} ج.م
                                ({{ $shift->difference == 0 ? 'متطابق' : ($shift->difference > 0 ? 'فائض' : 'عجز') }})
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Cash Drawer Movements Timeline -->
        <div class="erp-card mb-4 overflow-hidden">
            <div class="p-3 bg-[var(--surface-muted)] border-b border-[var(--border-color)] flex items-center justify-between">
                <h3 class="font-bold text-sm text-[var(--text-main)] flex items-center gap-2 m-0">
                    <x-icon name="list" size="16" class="text-blue-500" />
                    <span>سجل حركات الخزينة والدرج للوردية (Cash Movements Log)</span>
                </h3>
                <span class="erp-badge erp-badge-neutral font-numeric">{{ $shift->cashMovements->count() }} حركة</span>
            </div>
            <div class="table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">#</th>
                            <th class="w-32">نوع الحركة</th>
                            <th class="text-center w-28">المبلغ (ج.م)</th>
                            <th>البيان والملاحظات</th>
                            <th class="w-36">المستخدم</th>
                            <th class="w-36 text-center">التوقيت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shift->cashMovements as $mov)
                            <tr>
                                <td class="text-center font-numeric text-xs">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="shift-diff-badge {{ in_array($mov->movement_type, ['opening', 'sale', 'cash_in']) ? 'surplus' : ($mov->movement_type === 'closing' ? 'matched' : 'deficit') }}">
                                        {{ $mov->movement_type_label }}
                                    </span>
                                </td>
                                <td class="text-center font-numeric font-bold text-xs">
                                    {{ number_format($mov->amount, 2) }}
                                </td>
                                <td class="text-xs">
                                    {{ $mov->notes ?? '—' }}
                                </td>
                                <td class="text-xs font-bold text-[var(--text-main)]">
                                    {{ $mov->user?->name ?? '—' }}
                                </td>
                                <td class="text-center font-numeric text-xs">
                                    {{ $mov->created_at->format('Y-m-d H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-[var(--text-muted)]">
                                    لا توجد حركات مسجلة بالدرج حتى الآن.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Sales Invoices Issued in this Shift -->
        <div class="erp-card overflow-hidden">
            <div class="p-3 bg-[var(--surface-muted)] border-b border-[var(--border-color)] flex items-center justify-between">
                <h3 class="font-bold text-sm text-[var(--text-main)] flex items-center gap-2 m-0">
                    <x-icon name="shopping-cart" size="16" class="text-emerald-500" />
                    <span>فواتير المبيعات الصادرة في هذه الوردية</span>
                </h3>
                <span class="erp-badge erp-badge-neutral font-numeric">{{ $shift->salesInvoices->count() }} فاتورة</span>
            </div>
            <div class="table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th class="w-36">رقم الفاتورة</th>
                            <th>العميل</th>
                            <th class="text-center">فئة السعر</th>
                            <th class="text-center">إجمالي الفاتورة</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">التاريخ</th>
                            <th class="text-center w-20">معاينة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shift->salesInvoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $inv) }}" class="font-numeric font-bold text-[var(--primary)] hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $inv->customer?->name ?? 'عميل نقدي' }}</td>
                                <td class="text-center text-xs">
                                    <span class="erp-badge erp-badge-neutral">{{ $inv->price_tier }}</span>
                                </td>
                                <td class="text-center font-numeric font-bold text-xs">
                                    {{ number_format($inv->total_amount, 2) }} ج.م
                                </td>
                                <td class="text-center">
                                    <span class="erp-badge {{ $inv->status === 'posted' ? 'erp-badge-success' : 'erp-badge-warning' }}">
                                        {{ $inv->status === 'posted' ? 'مرحلة ومحصلة' : 'مسودة' }}
                                    </span>
                                </td>
                                <td class="text-center font-numeric text-xs">
                                    {{ $inv->invoice_date->format('Y-m-d') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('sales.show', $inv) }}" class="erp-btn erp-btn-ghost erp-btn-sm" title="عرض الفاتورة">
                                        <x-icon name="eye" size="14" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-[var(--text-muted)]">
                                    لم يتم إصدار فواتير مبيعات مرتبطة بهذه الوردية بعد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
