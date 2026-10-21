<div class="erp-filter-nav mb-5 flex flex-wrap items-center gap-2 border-b border-[var(--border-color)] pb-3">
    <a href="{{ route('accounting.chart-of-accounts') }}" 
       class="erp-btn {{ request()->routeIs('accounting.chart-of-accounts') ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-xs font-semibold">
        <x-icon name="list" size="14" />
        <span>دليل الحسابات الشجري</span>
    </a>
    <a href="{{ route('accounting.journal-entries') }}" 
       class="erp-btn {{ request()->routeIs('accounting.journal-entries*') ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-xs font-semibold">
        <x-icon name="file-text" size="14" />
        <span>دفتر قيود اليومية</span>
    </a>
    <a href="{{ route('accounting.reports.trial-balance') }}" 
       class="erp-btn {{ request()->routeIs('accounting.reports.trial-balance*') ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-xs font-semibold">
        <x-icon name="layers" size="14" />
        <span>ميزان المراجعة (Trial Balance)</span>
    </a>
    <a href="{{ route('accounting.reports.income-statement') }}" 
       class="erp-btn {{ request()->routeIs('accounting.reports.income-statement*') ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-xs font-semibold">
        <x-icon name="trending-up" size="14" />
        <span>قائمة الدخل والأرباح (P&L)</span>
    </a>
    <a href="{{ route('accounting.reports.balance-sheet') }}" 
       class="erp-btn {{ request()->routeIs('accounting.reports.balance-sheet*') ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-xs font-semibold">
        <x-icon name="pie-chart" size="14" />
        <span>الميزانية والمركز المالي (Balance Sheet)</span>
    </a>
</div>
