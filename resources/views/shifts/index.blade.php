@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="ورديات وخزينة الكاشير" :breadcrumbs="['المبيعات' => route('sales.index'), 'ورديات الكاشير' => '#']">
        <x-slot:actions>
            @if(!$currentActiveShift)
                <button type="button" id="openShiftModalBtn" class="erp-btn erp-btn-primary">
                    <x-icon name="plus" size="16" />
                    <span>فتح وردية جديدة</span>
                </button>
            @else
                <button type="button" id="recordMovementModalBtn" class="erp-btn erp-btn-secondary">
                    <x-icon name="dollar-sign" size="16" />
                    <span>حركة نقدية (سحب/إيداع)</span>
                </button>
                <button type="button" id="closeActiveShiftModalBtn" class="erp-btn erp-btn-danger close-shift-trigger-btn"
                    data-shift-id="{{ $currentActiveShift->id }}"
                    data-shift-number="{{ $currentActiveShift->shift_number }}"
                    data-expected="{{ $currentActiveShift->expected_closing_balance }}"
                    data-opening="{{ $currentActiveShift->opening_balance }}"
                    data-sales="{{ $currentActiveShift->cash_sales_amount }}"
                    data-additions="{{ $currentActiveShift->cash_additions }}"
                    data-expenses="{{ $currentActiveShift->cash_expenses }}"
                    data-cashier="{{ $currentActiveShift->user?->name }}"
                    data-warehouse="{{ $currentActiveShift->warehouse?->name }}">
                    <x-icon name="lock" size="16" />
                    <span>إغلاق الوردية الحالية</span>
                </button>
            @endif
            <a href="{{ route('sales.create') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="shopping-cart" size="16" />
                <span>شاشة الكاشير (POS)</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            @if($currentActiveShift)
                <span class="erp-badge erp-badge-success font-numeric">
                    <x-icon name="check-circle" size="14" />
                    <span>وردية نشطة: {{ $currentActiveShift->shift_number }}</span>
                </span>
            @else
                <span class="erp-badge erp-badge-warning">
                    <x-icon name="alert-circle" size="14" />
                    <span>لا توجد وردية مفتوحة لك حالياً</span>
                </span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="shifts-index">
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

        <!-- Active Shift Live Widget (If Cashier has active shift) -->
        @if($currentActiveShift)
            <div class="pos-shift-banner mb-4">
                <div class="pos-shift-info">
                    <div class="flex items-center gap-2">
                        <span class="shift-status-badge open">نشطة الآن</span>
                        <strong class="font-numeric text-base text-[var(--text-main)]">{{ $currentActiveShift->shift_number }}</strong>
                    </div>
                    <div class="text-xs text-[var(--text-muted)]">
                        الكاشير: <span class="font-bold text-[var(--text-main)]">{{ $currentActiveShift->user?->name }}</span> · 
                        المستودع: <span class="font-bold text-[var(--text-main)]">{{ $currentActiveShift->warehouse?->name }}</span> · 
                        وقت الفتح: <span class="font-numeric">{{ $currentActiveShift->opened_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="text-xs font-numeric bg-[var(--surface-muted)] px-3 py-1 rounded border border-[var(--border-color)]">
                        رصيد الدرج المتوقع: <strong class="text-emerald-500 text-sm">{{ number_format($currentActiveShift->expected_closing_balance, 2) }} ج.م</strong>
                        (بداية: {{ number_format($currentActiveShift->opening_balance, 2) }} | مبيعات: {{ number_format($currentActiveShift->cash_sales_amount, 2) }})
                    </div>
                </div>
                <div class="pos-shift-actions">
                    <button type="button" class="erp-btn erp-btn-sm erp-btn-secondary record-movement-trigger-btn" data-shift-id="{{ $currentActiveShift->id }}">
                        <x-icon name="plus-circle" size="14" />
                        <span>سحب / إيداع</span>
                    </button>
                    <button type="button" class="erp-btn erp-btn-sm erp-btn-danger close-shift-trigger-btn"
                        data-shift-id="{{ $currentActiveShift->id }}"
                        data-shift-number="{{ $currentActiveShift->shift_number }}"
                        data-expected="{{ $currentActiveShift->expected_closing_balance }}"
                        data-opening="{{ $currentActiveShift->opening_balance }}"
                        data-sales="{{ $currentActiveShift->cash_sales_amount }}"
                        data-additions="{{ $currentActiveShift->cash_additions }}"
                        data-expenses="{{ $currentActiveShift->cash_expenses }}"
                        data-cashier="{{ $currentActiveShift->user?->name }}"
                        data-warehouse="{{ $currentActiveShift->warehouse?->name }}">
                        <x-icon name="lock" size="14" />
                        <span>قفل الشيفت</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="shift-summary-grid">
            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-emerald-500/10 text-emerald-500">
                    <x-icon name="clock" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">الورديات المفتوحة حالياً</div>
                    <div class="shift-kpi-value font-numeric text-emerald-500">{{ $metrics['open_shifts_count'] }}</div>
                </div>
            </div>

            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-blue-500/10 text-blue-500">
                    <x-icon name="credit-card" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">نقدية الأدراج المفتوحة</div>
                    <div class="shift-kpi-value font-numeric">{{ number_format($metrics['total_open_expected_cash'], 2) }} <span class="text-xs font-normal">ج.م</span></div>
                </div>
            </div>

            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-purple-500/10 text-purple-500">
                    <x-icon name="shopping-cart" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">مبيعات ورديات اليوم</div>
                    <div class="shift-kpi-value font-numeric">{{ number_format($metrics['total_sales_today'], 2) }} <span class="text-xs font-normal">ج.م</span></div>
                </div>
            </div>

            <div class="shift-kpi-card">
                <div class="shift-kpi-icon bg-amber-500/10 text-amber-500">
                    <x-icon name="alert-triangle" size="22" />
                </div>
                <div>
                    <div class="shift-kpi-label">صافي فروقات الجرد (اليوم)</div>
                    <div class="shift-kpi-value font-numeric {{ $metrics['total_difference'] < 0 ? 'text-red-500' : ($metrics['total_difference'] > 0 ? 'text-blue-500' : 'text-emerald-500') }}">
                        {{ number_format($metrics['total_difference'], 2) }} <span class="text-xs font-normal">ج.م</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="erp-card mb-4 p-4">
            <form method="GET" action="{{ route('shifts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div class="erp-form-group mb-0">
                    <label class="erp-form-label text-xs">الحالة</label>
                    <select name="status" class="erp-input text-xs">
                        <option value="">جميع الحالات</option>
                        <option value="open" {{ $currentStatus === 'open' ? 'selected' : '' }}>نشطة (مفتوحة)</option>
                        <option value="closed" {{ $currentStatus === 'closed' ? 'selected' : '' }}>مغلقة</option>
                    </select>
                </div>

                <div class="erp-form-group mb-0">
                    <label class="erp-form-label text-xs">المستودع / الفرع</label>
                    <select name="warehouse_id" class="erp-input text-xs">
                        <option value="">جميع المستودعات</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$currentWarehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="erp-form-group mb-0">
                    <label class="erp-form-label text-xs">الكاشير / الموظف</label>
                    <select name="user_id" class="erp-input text-xs">
                        <option value="">جميع الموظفين</option>
                        @foreach($cashiers as $c)
                            <option value="{{ $c->id }}" {{ (string)$currentUserId === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="erp-btn erp-btn-secondary text-xs flex-1 justify-center">
                        <x-icon name="filter" size="14" />
                        <span>تصفية</span>
                    </button>
                    <a href="{{ route('shifts.index') }}" class="erp-btn erp-btn-ghost text-xs" title="إعادة تعيين">
                        <x-icon name="rotate-ccw" size="14" />
                    </a>
                </div>
            </form>
        </div>

        <!-- Shifts Table -->
        <div class="erp-card overflow-hidden">
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th class="w-32">رقم الوردية</th>
                            <th>الكاشير</th>
                            <th>المستودع</th>
                            <th>توقيت الفتح</th>
                            <th>توقيت الإغلاق</th>
                            <th class="text-center">رصيد البداية</th>
                            <th class="text-center">المبيعات النقدية</th>
                            <th class="text-center">الرصيد المتوقع</th>
                            <th class="text-center">الجرد الفعلي</th>
                            <th class="text-center">الفارق</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center w-28">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            <tr>
                                <td>
                                    <a href="{{ route('shifts.show', $shift) }}" class="font-numeric font-bold text-[var(--primary)] hover:underline">
                                        {{ $shift->shift_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-bold text-[var(--text-main)]">{{ $shift->user?->name ?? '—' }}</div>
                                    @if($shift->opened_by && $shift->opened_by !== $shift->user_id)
                                        <div class="text-[10px] text-[var(--text-muted)] font-semibold">بواسطة: {{ $shift->openedBy?->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-xs">{{ $shift->warehouse?->name ?? '—' }}</span>
                                </td>
                                <td class="font-numeric text-xs">
                                    {{ $shift->opened_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="font-numeric text-xs">
                                    {{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="text-center font-numeric font-bold text-xs">
                                    {{ number_format($shift->opening_balance, 2) }}
                                </td>
                                <td class="text-center font-numeric font-bold text-xs text-emerald-500">
                                    {{ number_format($shift->cash_sales_amount, 2) }}
                                </td>
                                <td class="text-center font-numeric font-bold text-xs">
                                    {{ number_format($shift->expected_closing_balance, 2) }}
                                </td>
                                <td class="text-center font-numeric font-bold text-xs">
                                    {{ $shift->actual_closing_balance !== null ? number_format($shift->actual_closing_balance, 2) : '—' }}
                                </td>
                                <td class="text-center font-numeric text-xs">
                                    @if($shift->status === 'closed')
                                        @if($shift->difference == 0)
                                            <span class="shift-diff-badge matched">مطابق (0)</span>
                                        @elseif($shift->difference > 0)
                                            <span class="shift-diff-badge surplus">+{{ number_format($shift->difference, 2) }}</span>
                                        @else
                                            <span class="shift-diff-badge deficit">{{ number_format($shift->difference, 2) }}</span>
                                        @endif
                                    @else
                                        <span class="text-[var(--text-muted)]">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($shift->status === 'open')
                                        <span class="shift-status-badge open">نشطة</span>
                                    @else
                                        <span class="shift-status-badge closed">مغلقة</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('shifts.show', $shift) }}" class="erp-btn erp-btn-ghost erp-btn-sm" title="تفاصيل الوردية">
                                            <x-icon name="eye" size="14" />
                                        </a>
                                        <!-- Print Combobox Dropdown -->
                                        <x-print-dropdown 
                                            size="sm"
                                            title="طباعة"
                                            code="{{ $shift->shift_number }}"
                                            urlThermal="{{ route('shifts.print', [$shift, 'format' => 'thermal_80mm']) }}"
                                            directThermal="{{ route('shifts.direct-print', [$shift, 'format' => 'thermal_80mm']) }}"
                                            urlA4="{{ route('shifts.print', [$shift, 'format' => 'a4']) }}"
                                            urlA5="{{ route('shifts.print', [$shift, 'format' => 'a5']) }}"
                                        />
                                        @if($shift->status === 'open' && (auth()->id() === $shift->user_id || auth()->user()->isAdmin()))
                                            <button type="button" class="erp-btn erp-btn-ghost erp-btn-sm text-[var(--danger)] close-shift-trigger-btn"
                                                data-shift-id="{{ $shift->id }}"
                                                data-shift-number="{{ $shift->shift_number }}"
                                                data-expected="{{ $shift->expected_closing_balance }}"
                                                data-opening="{{ $shift->opening_balance }}"
                                                data-sales="{{ $shift->cash_sales_amount }}"
                                                data-additions="{{ $shift->cash_additions }}"
                                                data-expenses="{{ $shift->cash_expenses }}"
                                                data-cashier="{{ $shift->user?->name }}"
                                                data-warehouse="{{ $shift->warehouse?->name }}"
                                                title="إغلاق الوردية">
                                                <x-icon name="lock" size="14" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-8 text-[var(--text-muted)]">
                                    <x-icon name="clock" size="32" class="mx-auto mb-2 opacity-50" />
                                    <p>لا توجد ورديات مسجلة تطابق معايير البحث.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-[var(--border-color)]">
                {{ $shifts->links() }}
            </div>
        </div>

        <!-- 1. Open Shift Modal -->
        <div id="openShiftModal" class="erp-modal-overlay hidden" role="dialog" aria-modal="true">
            <div class="erp-modal-container">
                <div class="erp-modal-header">
                    <div class="erp-modal-title">
                        <x-icon name="clock" size="18" class="text-emerald-500" />
                        <span>فتح وردية كاشير جديدة</span>
                    </div>
                    <button type="button" class="erp-modal-close close-modal-btn" aria-label="إغلاق">
                        <x-icon name="x" size="16" />
                    </button>
                </div>
                <form method="POST" action="{{ route('shifts.store') }}">
                    @csrf
                    <div class="erp-modal-body">
                        @if($isManager)
                            <div class="erp-form-group mb-3">
                                <label for="shiftCashierSelect" class="erp-form-label text-xs">
                                    الكاشير المسؤول عن الوردية <span class="text-[var(--danger)]">*</span>
                                </label>
                                <select id="shiftCashierSelect" name="user_id" class="erp-input text-xs" required>
                                    @foreach($eligibleCashiers as $c)
                                        <option value="{{ $c->id }}" {{ $c->id === auth()->id() ? 'selected' : '' }}>
                                            {{ $c->name }} ({{ $c->role_label }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-[11px] text-[var(--text-muted)]">بصفتك مديراً/مشرفاً، يمكنك فتح الوردية وتعيين الكاشير المسؤول عنها.</span>
                            </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                        @endif

                        <div class="erp-form-group mb-3">
                            <label for="shiftWarehouseSelect" class="erp-form-label text-xs">
                                المستودع / الفرع <span class="text-[var(--danger)]">*</span>
                            </label>
                            <select id="shiftWarehouseSelect" name="warehouse_id" class="erp-input text-xs" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (auth()->user()->warehouse_id == $wh->id) ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="erp-form-group mb-3">
                            <label for="shiftOpeningBalance" class="erp-form-label text-xs">
                                رصيد بداية العهدة النقدية في الدرج (ج.م) <span class="text-[var(--danger)]">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" id="shiftOpeningBalance" name="opening_balance" value="0.00" class="erp-input font-numeric text-base font-bold" required>
                            <span class="text-[11px] text-[var(--text-muted)]">المبلغ النقدي المتواجد فعلياً في درج الكاشير قبل بدء المبيعات (الفكة).</span>
                        </div>

                        <div class="erp-form-group mb-0">
                            <label for="shiftOpeningNotes" class="erp-form-label text-xs">ملاحظات افتتاح الوردية</label>
                            <textarea id="shiftOpeningNotes" name="opening_notes" rows="2" class="erp-input text-xs" placeholder="أي ملاحظات حول العهدة أو استلام الدرج..."></textarea>
                        </div>
                    </div>
                    <div class="erp-modal-footer">
                        <button type="button" class="erp-btn erp-btn-secondary close-modal-btn">إلغاء</button>
                        <button type="submit" class="erp-btn erp-btn-primary">
                            <x-icon name="check" size="16" />
                            <span>تأكيد وفتح الوردية</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Close Shift Modal -->
        <div id="closeShiftModal" class="erp-modal-overlay hidden" role="dialog" aria-modal="true">
            <div class="erp-modal-container">
                <div class="erp-modal-header">
                    <div class="erp-modal-title">
                        <x-icon name="lock" size="18" class="text-[var(--danger)]" />
                        <span>إغلاق وردية الكاشير وجرد الدرج</span>
                    </div>
                    <button type="button" class="erp-modal-close close-modal-btn" aria-label="إغلاق">
                        <x-icon name="x" size="16" />
                    </button>
                </div>
                <form id="closeShiftForm" method="POST" action="">
                    @csrf
                    <div class="erp-modal-body">
                        <div class="text-xs text-[var(--text-muted)] mb-3">
                            رقم الوردية: <strong id="closeShiftNumberSpan" class="text-[var(--text-main)] font-numeric"></strong>
                        </div>

                        <div class="shift-calc-box mt-0 mb-3">
                            <div class="shift-calc-row">
                                <span>كاشير الوردية:</span>
                                <strong id="closeShiftCashierSpan" class="text-[var(--text-main)]"></strong>
                            </div>
                            <div class="shift-calc-row">
                                <span>المستودع / الفرع:</span>
                                <strong id="closeShiftWarehouseSpan" class="text-[var(--text-main)]"></strong>
                            </div>
                            <div class="shift-calc-row text-xs text-[var(--text-muted)]">
                                <span>(+) رصيد بداية العهدة:</span>
                                <span id="closeShiftOpeningSpan" class="font-numeric">0.00 ج.م</span>
                            </div>
                            <div class="shift-calc-row text-xs text-emerald-600">
                                <span>(+) إجمالي المبيعات النقدية:</span>
                                <span id="closeShiftSalesSpan" class="font-numeric">0.00 ج.م</span>
                            </div>
                            <div class="shift-calc-row text-xs text-blue-600">
                                <span>(+) إيداعات نقدية إضافية:</span>
                                <span id="closeShiftAdditionsSpan" class="font-numeric">0.00 ج.م</span>
                            </div>
                            <div class="shift-calc-row text-xs text-amber-600">
                                <span>(-) سحوبات ومصروفات:</span>
                                <span id="closeShiftExpensesSpan" class="font-numeric">0.00 ج.م</span>
                            </div>
                            <div class="shift-calc-row pt-2 border-t border-[var(--border-color)]">
                                <span class="font-bold text-sm">الرصيد الدفتري المتوقع بالدرج:</span>
                                <strong id="closeShiftExpectedSpan" class="font-numeric text-base text-emerald-500">0.00 ج.م</strong>
                            </div>
                        </div>

                        <div class="erp-form-group mb-3">
                            <label for="closeShiftActualBalance" class="erp-form-label text-xs">
                                الجرد النقدي الفعلي في الدرج (ج.م) <span class="text-[var(--danger)]">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" id="closeShiftActualBalance" name="actual_closing_balance" class="erp-input font-numeric text-base font-bold" placeholder="0.00" required>
                            <span class="text-[11px] text-[var(--text-muted)]">قم بعد النقدية الموجودة في الدرج بالكامل وإدخالها هنا.</span>
                        </div>

                        <div id="shiftDiffResultBox" class="erp-alert erp-alert-info mb-3 hidden">
                            <x-icon name="info" size="16" />
                            <span id="shiftDiffResultText"></span>
                        </div>

                        @cannot('close-shifts')
                            <div class="erp-alert erp-alert-warning mb-3">
                                <x-icon name="shield-alert" size="16" />
                                <span class="text-xs">إغلاق الوردية يتطلب اعتماد مدير النظام أو المشرف المباشر.</span>
                            </div>

                            <div class="erp-form-group mb-3">
                                <label for="closeSupervisorEmail" class="erp-form-label text-xs">
                                    بريد المشرف / المدير <span class="text-[var(--danger)]">*</span>
                                </label>
                                <input type="email" id="closeSupervisorEmail" name="supervisor_email" class="erp-input text-xs" placeholder="supervisor@example.com" required>
                            </div>

                            <div class="erp-form-group mb-3">
                                <label for="closeSupervisorPassword" class="erp-form-label text-xs">
                                    كلمة مرور المشرف / المدير <span class="text-[var(--danger)]">*</span>
                                </label>
                                <input type="password" id="closeSupervisorPassword" name="supervisor_password" class="erp-input text-xs" placeholder="••••••••" required>
                            </div>
                        @endcannot

                        <div class="erp-form-group mb-0">
                            <label for="closeShiftNotes" class="erp-form-label text-xs">ملاحظات الإغلاق والجرد</label>
                            <textarea id="closeShiftNotes" name="closing_notes" rows="2" class="erp-input text-xs" placeholder="سبب العجز أو الزيادة إن وجد، أو أي ملاحظات تسليم..."></textarea>
                        </div>
                    </div>
                    <div class="erp-modal-footer">
                        <button type="button" class="erp-btn erp-btn-secondary close-modal-btn">إلغاء</button>
                        <button type="submit" class="erp-btn erp-btn-danger">
                            <x-icon name="lock" size="16" />
                            <span>تأكيد الإغلاق النهائي</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Record Movement Modal (Cash In / Out) -->
        <div id="recordMovementModal" class="erp-modal-overlay hidden" role="dialog" aria-modal="true">
            <div class="erp-modal-container">
                <div class="erp-modal-header">
                    <div class="erp-modal-title">
                        <x-icon name="dollar-sign" size="18" class="text-blue-500" />
                        <span>تسجيل حركة نقدية في درج الكاشير</span>
                    </div>
                    <button type="button" class="erp-modal-close close-modal-btn" aria-label="إغلاق">
                        <x-icon name="x" size="16" />
                    </button>
                </div>
                <form id="recordMovementForm" method="POST" action="">
                    @csrf
                    <div class="erp-modal-body">
                        <div class="erp-form-group mb-3">
                            <label for="movementTypeSelect" class="erp-form-label text-xs">نوع الحركة</label>
                            <select id="movementTypeSelect" name="movement_type" class="erp-input text-xs" required>
                                <option value="cash_in">إيداع نقدية إضافية في الدرج (سلفة / عهدة)</option>
                                <option value="cash_out">سحب نقدية من الدرج (مصروفات نثرية / توريد خزينة)</option>
                            </select>
                        </div>

                        <div class="erp-form-group mb-3">
                            <label for="movementAmount" class="erp-form-label text-xs">
                                المبلغ (ج.م) <span class="text-[var(--danger)]">*</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" id="movementAmount" name="amount" class="erp-input font-numeric font-bold" placeholder="0.00" required>
                        </div>

                        <div class="erp-form-group mb-0">
                            <label for="movementNotes" class="erp-form-label text-xs">
                                بيان / سبب الحركة <span class="text-[var(--danger)]">*</span>
                            </label>
                            <input type="text" id="movementNotes" name="notes" class="erp-input text-xs" placeholder="مثال: شراء أدوات نظافة / سحب توريد للمدير" required>
                        </div>
                    </div>
                    <div class="erp-modal-footer">
                        <button type="button" class="erp-btn erp-btn-secondary close-modal-btn">إلغاء</button>
                        <button type="submit" class="erp-btn erp-btn-primary">
                            <x-icon name="check" size="16" />
                            <span>تسجيل الحركة</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
