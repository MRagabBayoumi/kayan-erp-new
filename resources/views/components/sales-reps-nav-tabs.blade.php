@props([
    'active' => 'reps', // 'reps', 'customer-requests', 'attendance', 'visits'
    'pendingRequestsCount' => null,
])

@php
    if ($pendingRequestsCount === null) {
        $pendingRequestsCount = \App\Models\Customer::whereNotNull('sales_rep_id')
            ->where('approval_status', \App\Models\Customer::APPROVAL_PENDING)
            ->count();
    }
@endphp

<!-- Enterprise Sub-Navigation Tabs Bar for Sales Reps Management -->
<nav class="erp-card mb-4 p-2 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-xl shadow-xs" aria-label="أقسام إدارة المناديب">
    <div class="flex items-center gap-2 overflow-x-auto scrollbar-none py-0.5">
        <!-- 1. Reps Team & Commissions -->
        <a href="{{ route('sales-reps.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold transition-all shrink-0 whitespace-nowrap {{ $active === 'reps' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--text-secondary)] hover:text-[var(--text-main)] hover:bg-[var(--bg-hover)]' }}">
            <x-icon name="users" size="17" class="{{ $active === 'reps' ? 'text-white' : 'text-[var(--primary)]' }}" />
            <span>فريق المناديب والعمولات</span>
        </a>

        <!-- 2. Customer Approval Requests -->
        <a href="{{ route('sales-reps.customer-requests.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold transition-all shrink-0 whitespace-nowrap {{ $active === 'customer-requests' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--text-secondary)] hover:text-[var(--text-main)] hover:bg-[var(--bg-hover)]' }}">
            <x-icon name="user-check" size="17" class="{{ $active === 'customer-requests' ? 'text-white' : 'text-amber-500' }}" />
            <span>طلبات اعتماد العملاء</span>
            @if($pendingRequestsCount > 0)
                <span class="mr-1 px-2 py-0.5 rounded-full text-xs font-numeric font-black {{ $active === 'customer-requests' ? 'bg-amber-400 text-amber-950' : 'bg-amber-500 text-white' }}">
                    {{ $pendingRequestsCount }}
                </span>
            @endif
        </a>

        <!-- 3. Field Attendance & Departure (GPS) -->
        <a href="{{ route('sales-reps.attendance.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold transition-all shrink-0 whitespace-nowrap {{ $active === 'attendance' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--text-secondary)] hover:text-[var(--text-main)] hover:bg-[var(--bg-hover)]' }}">
            <x-icon name="clock" size="17" class="{{ $active === 'attendance' ? 'text-white' : 'text-emerald-500' }}" />
            <span>الحضور والانصراف (GPS)</span>
        </a>

        <!-- 4. Field Visits Tracker (GPS) -->
        <a href="{{ route('sales-reps.visits.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold transition-all shrink-0 whitespace-nowrap {{ $active === 'visits' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--text-secondary)] hover:text-[var(--text-main)] hover:bg-[var(--bg-hover)]' }}">
            <x-icon name="map-pin" size="17" class="{{ $active === 'visits' ? 'text-white' : 'text-rose-500' }}" />
            <span>الزيارات الميدانية (GPS)</span>
        </a>
    </div>
</nav>
