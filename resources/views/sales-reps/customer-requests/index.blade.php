@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="طلبات اعتماد العملاء الجدد من الميدان" :breadcrumbs="[
        'المبيعات' => route('sales.index'),
        'مناديب المبيعات' => route('sales-reps.index'),
        'طلبات اعتماد العملاء' => '#'
    ]">
        <x-slot:actions>
            <a href="{{ route('sales-reps.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>رجوع للمناديب</span>
            </a>
            <a href="{{ route('sales-reps.attendance.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="clock" size="16" />
                <span>سجل الحضور والغياب</span>
            </a>
            <a href="{{ route('sales-reps.visits.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="map-pin" size="16" />
                <span>سجل الزيارات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-warning font-numeric px-2.5 py-1 text-xs">
                <span>طلبات قيد المراجعة:</span>
                <strong>{{ $pendingCount }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
<div class="space-y-4">

    <!-- Top Sub-Navigation Tabs -->
    <div class="erp-card mb-0 p-2 flex items-center gap-2 overflow-x-auto text-xs">
        <a href="{{ route('sales-reps.index') }}" class="px-3 py-1.5 rounded font-bold border border-transparent text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors">
            <x-icon name="users" size="14" class="inline ml-1" />
            <span>فريق المناديب والعمولات</span>
        </a>
        <a href="{{ route('sales-reps.customer-requests.index') }}" class="px-3 py-1.5 rounded font-bold bg-[var(--primary)] text-white shadow-xs">
            <x-icon name="user-check" size="14" class="inline ml-1" />
            <span>طلبات اعتماد العملاء</span>
            @if($pendingCount > 0)
                <span class="mr-1.5 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-numeric">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('sales-reps.attendance.index') }}" class="px-3 py-1.5 rounded font-bold border border-transparent text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors">
            <x-icon name="clock" size="14" class="inline ml-1" />
            <span>الحضور والانصراف (GPS)</span>
        </a>
        <a href="{{ route('sales-reps.visits.index') }}" class="px-3 py-1.5 rounded font-bold border border-transparent text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors">
            <x-icon name="map-pin" size="14" class="inline ml-1" />
            <span>الزيارات الميدانية (GPS)</span>
        </a>
    </div>

    @if(session('message'))
        <div class="erp-alert erp-alert-{{ session('status') ?? 'info' }}" role="alert">
            <x-icon name="info" size="18" />
            <div>{{ session('message') }}</div>
        </div>
    @endif

    <!-- Status Tabs & Filter Card -->
    <div class="erp-card mb-0 p-4 space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs">
                <a href="{{ route('sales-reps.customer-requests.index', array_filter(['status' => 'pending', 'sales_rep_id' => $currentRep, 'search' => $currentSearch])) }}"
                    class="px-3 py-1.5 rounded-md font-bold text-xs border transition-colors shrink-0 {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white border-amber-600' : 'bg-[var(--bg-hover)] text-amber-700 border-[var(--border-color)]' }}">
                    <x-icon name="clock" size="13" class="inline ml-1" />
                    <span>قيد المراجعة ({{ $pendingCount }})</span>
                </a>
                <a href="{{ route('sales-reps.customer-requests.index', array_filter(['status' => 'approved', 'sales_rep_id' => $currentRep, 'search' => $currentSearch])) }}"
                    class="px-3 py-1.5 rounded-md font-bold text-xs border transition-colors shrink-0 {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-[var(--bg-hover)] text-emerald-700 border-[var(--border-color)]' }}">
                    <x-icon name="check-circle" size="13" class="inline ml-1" />
                    <span>المعتمدة ({{ $approvedCount }})</span>
                </a>
                <a href="{{ route('sales-reps.customer-requests.index', array_filter(['status' => 'rejected', 'sales_rep_id' => $currentRep, 'search' => $currentSearch])) }}"
                    class="px-3 py-1.5 rounded-md font-bold text-xs border transition-colors shrink-0 {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white border-rose-600' : 'bg-[var(--bg-hover)] text-rose-700 border-[var(--border-color)]' }}">
                    <x-icon name="x-circle" size="13" class="inline ml-1" />
                    <span>المرفوضة ({{ $rejectedCount }})</span>
                </a>
                <a href="{{ route('sales-reps.customer-requests.index', array_filter(['status' => 'all', 'sales_rep_id' => $currentRep, 'search' => $currentSearch])) }}"
                    class="px-3 py-1.5 rounded-md font-bold text-xs border transition-colors shrink-0 {{ $currentStatus === 'all' ? 'bg-[var(--primary)] text-white border-[var(--primary)]' : 'bg-[var(--bg-hover)] text-[var(--text-secondary)] border-[var(--border-color)]' }}">
                    <span>الكل ({{ $pendingCount + $approvedCount + $rejectedCount }})</span>
                </a>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('sales-reps.customer-requests.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <select name="sales_rep_id" onchange="this.form.submit()" class="erp-input text-xs w-44">
                    <option value="">كل المناديب</option>
                    @foreach($salesReps as $rep)
                        <option value="{{ $rep->id }}" {{ (string)$currentRep === (string)$rep->id ? 'selected' : '' }}>
                            {{ $rep->name }}
                        </option>
                    @endforeach
                </select>

                <div class="relative w-52 sm:w-64">
                    <input type="text" name="search" value="{{ $currentSearch }}" placeholder="بحث بالاسم أو الهاتف..."
                        class="erp-input pr-8 text-xs">
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none">
                        <x-icon name="search" size="13" />
                    </div>
                </div>

                @if($currentSearch || $currentRep)
                    <a href="{{ route('sales-reps.customer-requests.index', ['status' => $currentStatus]) }}" class="erp-btn erp-btn-secondary erp-btn-sm text-xs">
                        إلغاء
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Requests Table Card -->
    <div class="erp-card mb-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-xs">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>بيانات العميل والمتجر</th>
                        <th>المندوب المسجل</th>
                        <th>تاريخ الطلب</th>
                        <th>صور المكان</th>
                        <th>الموقع (GPS)</th>
                        <th>الحالة الحالية</th>
                        <th>الحد الائتماني / الفئة</th>
                        <th class="text-center w-36">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-light)]">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-[var(--bg-hover)] transition-colors">
                            <td class="text-center font-numeric text-[var(--text-muted)]">{{ $customer->id }}</td>
                            <td>
                                <div class="font-bold text-[var(--text-main)] text-sm">{{ $customer->name }}</div>
                                @if($customer->company_name)
                                    <div class="text-[11px] text-[var(--text-muted)]">{{ $customer->company_name }}</div>
                                @endif
                                <div class="text-[11px] text-[var(--text-secondary)] font-numeric flex items-center gap-1 mt-0.5">
                                    <x-icon name="phone" size="11" class="text-slate-400" />
                                    <span>{{ $customer->phone }}</span>
                                    @if($customer->phone_alt)
                                        <span class="text-[var(--text-muted)]">/ {{ $customer->phone_alt }}</span>
                                    @endif
                                </div>
                                @if($customer->city || $customer->address)
                                    <div class="text-[10px] text-[var(--text-muted)] truncate max-w-xs mt-0.5">
                                        {{ implode(' - ', array_filter([$customer->city, $customer->address])) }}
                                    </div>
                                @endif
                                @if($customer->notes)
                                    <div class="text-[10px] text-amber-700 bg-amber-50 rounded px-1.5 py-0.5 mt-1 max-w-xs border border-amber-200">
                                        <span class="font-bold">ملاحظة:</span> {{ $customer->notes }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($customer->salesRep)
                                    <div class="font-bold text-[var(--text-main)]">{{ $customer->salesRep->name }}</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-numeric">{{ $customer->salesRep->phone }}</div>
                                @else
                                    <span class="text-[var(--text-muted)]">-</span>
                                @endif
                            </td>
                            <td class="font-numeric text-[var(--text-secondary)] text-[11px]">
                                {{ $customer->created_at->format('Y-m-d') }}
                                <div class="text-[10px] text-[var(--text-muted)]">{{ $customer->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                @php $photos = $customer->store_image_urls; @endphp
                                @if(count($photos) > 0)
                                    <div class="flex items-center gap-1.5">
                                        @foreach($photos as $photo)
                                            <a href="{{ $photo }}" target="_blank" title="اضغط لفتح الصورة مكبرة"
                                                class="w-10 h-10 rounded border border-[var(--border-color)] overflow-hidden shrink-0 bg-slate-100 hover:opacity-80 transition-opacity">
                                                <img src="{{ $photo }}" alt="صورة المتجر" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[11px] text-[var(--text-muted)]">لا توجد صور</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->latitude && $customer->longitude)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[11px] text-sky-600 hover:text-sky-800 font-medium bg-sky-50 px-2 py-1 rounded border border-sky-200">
                                        <x-icon name="map-pin" size="12" class="text-rose-500" />
                                        <span>عرض بالخريطة</span>
                                    </a>
                                @else
                                    <span class="text-[11px] text-[var(--text-muted)]">غير محدد</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->isApproved())
                                    <span class="erp-badge erp-badge-success text-[10px]">
                                        <x-icon name="check" size="10" />
                                        <span>معتمد</span>
                                    </span>
                                    @if($customer->approver)
                                        <div class="text-[10px] text-[var(--text-muted)] mt-0.5">بواسطة: {{ $customer->approver->name }}</div>
                                    @endif
                                @elseif($customer->isPending())
                                    <span class="erp-badge erp-badge-warning text-[10px]">
                                        <x-icon name="clock" size="10" />
                                        <span>قيد المراجعة</span>
                                    </span>
                                @else
                                    <span class="erp-badge erp-badge-danger text-[10px]">
                                        <x-icon name="x" size="10" />
                                        <span>مرفوض</span>
                                    </span>
                                    @if($customer->rejection_reason)
                                        <div class="text-[10px] text-rose-600 mt-0.5 max-w-xs truncate" title="{{ $customer->rejection_reason }}">
                                            {{ $customer->rejection_reason }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @php
                                    $tierLabel = match($customer->pricing_tier) {
                                        'distributor' => 'موزع',
                                        'wholesale' => 'جملة',
                                        default => 'قطاعي',
                                    };
                                @endphp
                                <div class="font-bold text-[var(--text-main)] text-[11px]">فئة: {{ $tierLabel }}</div>
                                <div class="font-numeric text-[11px] text-[var(--text-muted)]">
                                    الحد: {{ number_format($customer->credit_limit, 2) }} ج.م
                                </div>
                            </td>
                            <td class="text-center">
                                @if($customer->isPending())
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openApproveModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ $customer->pricing_tier }}', {{ (float)$customer->credit_limit }})"
                                            class="erp-btn erp-btn-sm erp-btn-primary text-[11px] py-1 px-2.5" title="اعتماد وتحديد الحد الائتماني">
                                            <x-icon name="check" size="12" />
                                            <span>اعتماد</span>
                                        </button>
                                        <button type="button" onclick="openRejectModal({{ $customer->id }}, '{{ addslashes($customer->name) }}')"
                                            class="erp-btn erp-btn-sm erp-btn-danger text-[11px] py-1 px-2" title="رفض الطلب مع ذكر السبب">
                                            <x-icon name="x" size="12" />
                                            <span>رفض</span>
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick="openApproveModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ $customer->pricing_tier }}', {{ (float)$customer->credit_limit }})"
                                            class="p-1 rounded text-slate-500 hover:text-[var(--primary)] hover:bg-slate-100" title="تعديل الفئة والحد الائتماني">
                                            <x-icon name="edit" size="13" />
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-[var(--text-muted)]">
                                <x-icon name="inbox" size="32" class="text-slate-300 mx-auto mb-2" />
                                <div class="font-bold text-sm">لا توجد طلبات عملاء مطابقة للبحث</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-3 border-t border-[var(--border-light)]">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Approve Modal -->
<div id="approveModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="erp-card w-full max-w-md p-5 bg-white dark:bg-gray-800 shadow-xl rounded-lg space-y-4">
        <div class="flex items-center justify-between border-b pb-3 border-[var(--border-light)]">
            <h3 class="font-bold text-sm text-[var(--text-main)] flex items-center gap-1.5">
                <x-icon name="check-circle" size="16" class="text-emerald-600" />
                <span>اعتماد وتفعيل حساب العميل</span>
            </h3>
            <button type="button" onclick="closeApproveModal()" class="text-slate-400 hover:text-slate-600">
                <x-icon name="x" size="16" />
            </button>
        </div>

        <form id="approveForm" method="POST" action="" class="space-y-3">
            @csrf
            <div>
                <span class="text-xs text-[var(--text-muted)]">العميل المراد اعتماده:</span>
                <div id="approveCustomerName" class="font-bold text-sm text-[var(--text-main)] mt-0.5"></div>
            </div>

            <!-- Pricing Tier -->
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">فئة التسعير المعتمدة <span class="required">*</span></label>
                <select name="pricing_tier" id="approvePricingTier" required class="erp-input text-xs sm:text-sm">
                    <option value="wholesale">سعر الجملة (الافتراضي للمناديب)</option>
                    <option value="distributor">سعر الموزع (كبار الموزعين)</option>
                    <option value="retail">سعر التجزئة (قطاعي)</option>
                </select>
                <span class="text-[10px] text-[var(--text-muted)]">المندوب مسجل بسعر الجملة، ويمكنك تغييره هنا لموزع أو قطاعي.</span>
            </div>

            <!-- Credit Limit -->
            <div>
                <label class="erp-form-label font-bold text-xs mb-1">الحد الائتماني للعميل (ج.م) <span class="required">*</span></label>
                <input type="number" step="100" min="0" name="credit_limit" id="approveCreditLimit" value="0" required class="erp-input text-xs sm:text-sm font-numeric">
                <span class="text-[10px] text-[var(--text-muted)]">أدخل أقصى رصيد آجل مسموح به لهذا العميل (0 للتعامل النقدي فقط).</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--border-light)]">
                <button type="button" onclick="closeApproveModal()" class="erp-btn erp-btn-secondary text-xs">إلغاء</button>
                <button type="submit" class="erp-btn erp-btn-primary text-xs">
                    <x-icon name="check" size="14" />
                    <span>تأكيد الاعتماد وتفعيل الحساب</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="erp-card w-full max-w-md p-5 bg-white dark:bg-gray-800 shadow-xl rounded-lg space-y-4">
        <div class="flex items-center justify-between border-b pb-3 border-[var(--border-light)]">
            <h3 class="font-bold text-sm text-rose-600 flex items-center gap-1.5">
                <x-icon name="x-circle" size="16" class="text-rose-600" />
                <span>رفض اعتماد طلب العميل</span>
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600">
                <x-icon name="x" size="16" />
            </button>
        </div>

        <form id="rejectForm" method="POST" action="" class="space-y-3">
            @csrf
            <div>
                <span class="text-xs text-[var(--text-muted)]">العميل:</span>
                <div id="rejectCustomerName" class="font-bold text-sm text-[var(--text-main)] mt-0.5"></div>
            </div>

            <div>
                <label class="erp-form-label font-bold text-xs mb-1 text-rose-700">سبب الرفض <span class="required">*</span></label>
                <textarea name="rejection_reason" rows="3" required placeholder="اكتب سبب الرفض هنا ليظهر للمندوب (مثل: الصور غير واضحة، المكان مغلق، الموقع غير مطابق...)"
                    class="erp-textarea text-xs sm:text-sm"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--border-light)]">
                <button type="button" onclick="closeRejectModal()" class="erp-btn erp-btn-secondary text-xs">إلغاء</button>
                <button type="submit" class="erp-btn erp-btn-danger text-xs">
                    <x-icon name="x" size="14" />
                    <span>تأكيد الرفض</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openApproveModal(customerId, name, pricingTier, creditLimit) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        const nameEl = document.getElementById('approveCustomerName');
        const tierSelect = document.getElementById('approvePricingTier');
        const creditInput = document.getElementById('approveCreditLimit');

        form.action = `/sales-reps/customer-requests/${customerId}/approve`;
        nameEl.textContent = name;
        tierSelect.value = pricingTier || 'wholesale';
        creditInput.value = creditLimit || 0;

        modal.classList.remove('hidden');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function openRejectModal(customerId, name) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const nameEl = document.getElementById('rejectCustomerName');

        form.action = `/sales-reps/customer-requests/${customerId}/reject`;
        nameEl.textContent = name;

        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
