@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="دليل الحسابات الشجري (Chart of Accounts)" :breadcrumbs="['المالية والحسابات' => route('treasuries.index'), 'شجرة الحسابات' => route('accounting.chart-of-accounts')]">
        <x-slot:actions>
            <button type="button" onclick="document.getElementById('newAccountModal').style.display='flex'" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>إضافة حساب فرعي جديد</span>
            </button>
            <a href="{{ route('accounting.journal-entries') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="file-text" size="16" />
                <span>دفتر قيود اليومية</span>
            </a>
            <form action="{{ route('accounting.recalculate-balances') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="erp-btn erp-btn-secondary" title="إعادة احتساب وتحديث مجاميع الأرصدة في الشجرة بالكامل">
                    <x-icon name="refresh-cw" size="16" />
                    <span>تحديث أرصدة الشجرة</span>
                </button>
            </form>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-info font-numeric px-3 py-1 text-xs">
                <span>إجمالي الحسابات:</span>
                <strong class="mr-1">{{ $metrics['total_accounts_count'] }} حساب</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="chart-of-accounts">
        @if(session('message'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check-circle" size="18" />
                <div>{{ session('message') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="erp-alert erp-alert-danger mb-4" role="alert">
                <x-icon name="alert-triangle" size="18" />
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Financial Position & Overview KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <!-- 1. Assets -->
            <div class="erp-card p-3.5 border-t-4 border-t-cyan-500 flex flex-col justify-between">
                <div class="text-xs text-muted mb-1 flex items-center justify-between">
                    <span>الأصول (1)</span>
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                </div>
                <div class="font-numeric text-lg font-black text-foreground">
                    {{ number_format($metrics['total_assets'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 2. Liabilities -->
            <div class="erp-card p-3.5 border-t-4 border-t-amber-500 flex flex-col justify-between">
                <div class="text-xs text-muted mb-1 flex items-center justify-between">
                    <span>الخصوم (2)</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="font-numeric text-lg font-black text-foreground">
                    {{ number_format($metrics['total_liabilities'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 3. Equity -->
            <div class="erp-card p-3.5 border-t-4 border-t-indigo-500 flex flex-col justify-between">
                <div class="text-xs text-muted mb-1 flex items-center justify-between">
                    <span>حقوق الملكية (3)</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <div class="font-numeric text-lg font-black text-foreground">
                    {{ number_format($metrics['total_equity'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 4. Revenues -->
            <div class="erp-card p-3.5 border-t-4 border-t-emerald-500 flex flex-col justify-between">
                <div class="text-xs text-muted mb-1 flex items-center justify-between">
                    <span>الإيرادات (4)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="font-numeric text-lg font-black text-emerald-600 dark:text-emerald-400">
                    {{ number_format($metrics['total_revenues'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 5. Expenses -->
            <div class="erp-card p-3.5 border-t-4 border-t-rose-500 flex flex-col justify-between">
                <div class="text-xs text-muted mb-1 flex items-center justify-between">
                    <span>المصروفات (5)</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="font-numeric text-lg font-black text-rose-600 dark:text-rose-400">
                    {{ number_format($metrics['total_expenses'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 6. Net Profit / Income -->
            <div class="erp-card p-3.5 border-t-4 {{ $metrics['net_income'] >= 0 ? 'border-t-teal-500' : 'border-t-rose-600' }} flex flex-col justify-between bg-surface/50">
                <div class="text-xs text-muted mb-1 flex items-center justify-between">
                    <span>صافي النشاط الحالي</span>
                    <x-icon name="trending-up" size="12" class="{{ $metrics['net_income'] >= 0 ? 'text-teal-500' : 'text-rose-500' }}" />
                </div>
                <div class="font-numeric text-lg font-black {{ $metrics['net_income'] >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ number_format($metrics['net_income'], 2) }}
                    <span class="text-[11px] text-muted font-normal">ج.م</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="erp-card mb-6 p-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Tabs for Account Types -->
                <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
                    <a href="{{ route('accounting.chart-of-accounts') }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ empty($currentType) ? 'bg-primary text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        الكل (الشجرة كاملة)
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'asset']) }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $currentType === 'asset' ? 'bg-cyan-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        الأصول
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'liability']) }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $currentType === 'liability' ? 'bg-amber-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        الخصوم
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'equity']) }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $currentType === 'equity' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        حقوق الملكية
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'revenue']) }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $currentType === 'revenue' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        الإيرادات
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'expense']) }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $currentType === 'expense' ? 'bg-rose-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        المصروفات
                    </a>
                </div>

                <!-- Instant Search Input & Tree Controls -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <input type="text" id="accountTreeSearch" placeholder="بحث باسم أو كود الحساب..." class="erp-input text-xs pr-8 w-full" onkeyup="filterAccountTree(this.value)">
                        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted">
                            <x-icon name="search" size="14" />
                        </div>
                    </div>
                    <button type="button" class="erp-btn erp-btn-secondary text-xs px-2.5" onclick="expandAllNodes()" title="توسيع الكل">
                        <x-icon name="plus" size="14" />
                    </button>
                    <button type="button" class="erp-btn erp-btn-secondary text-xs px-2.5" onclick="collapseAllNodes()" title="طي الكل">
                        <x-icon name="x" size="14" />
                    </button>
                </div>
            </div>
        </div>

        <!-- The Chart of Accounts Tree Card -->
        <div class="erp-card overflow-hidden shadow-sm">
            <div class="bg-surface border-b border-border py-3 px-4 flex items-center justify-between text-xs font-bold text-muted uppercase tracking-wider">
                <div class="flex items-center gap-2">
                    <x-icon name="list" size="16" />
                    <span>هيكل الحسابات والأدلة المالية (Tree View)</span>
                </div>
                <div class="flex items-center gap-6">
                    <span class="hidden sm:inline">النوع</span>
                    <span class="hidden lg:inline">الطبيعة</span>
                    <span class="min-w-[120px] text-left">الرصيد الدفتري</span>
                    <span class="w-6"></span>
                </div>
            </div>

            <div id="accountsTreeContainer" class="divide-y divide-border/20">
                @forelse($rootAccounts as $root)
                    @include('accounting.partials.account-node', ['account' => $root, 'level' => 1])
                @empty
                    <div class="p-8 text-center text-muted">
                        <x-icon name="alert-circle" size="36" class="mx-auto mb-2 opacity-40" />
                        <p>لا توجد حسابات مطابقة للتصفية الحالية.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal: Add Sub-Account -->
    <div id="newAccountModal" class="erp-modal-backdrop" style="display: none;">
        <div class="erp-modal-box max-w-lg">
            <div class="erp-modal-header">
                <h3 class="erp-modal-title">إضافة حساب فرعي جديد إلى الدليل الشجري</h3>
                <button type="button" onclick="document.getElementById('newAccountModal').style.display='none'" class="erp-modal-close">
                    <x-icon name="x" size="18" />
                </button>
            </div>
            <form action="{{ route('accounting.accounts.store') }}" method="POST">
                @csrf
                <div class="erp-modal-body space-y-4">
                    <!-- Parent Account -->
                    <div>
                        <label for="parent_id" class="erp-label required">الحساب الرئيسي (الأب)</label>
                        <select name="parent_id" id="modalParentSelect" required class="erp-input text-sm">
                            <option value="">-- اختر الحساب الأب --</option>
                            @foreach($eligibleParents as $p)
                                <option value="{{ $p->id }}" data-code="{{ $p->code }}">
                                    {{ str_repeat('— ', $p->level - 1) }}{{ $p->code }} - {{ $p->name }} ({{ $p->type_label }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Account Name Arabic -->
                    <div>
                        <label for="name" class="erp-label required">اسم الحساب بالعربية</label>
                        <input type="text" name="name" id="name" required class="erp-input text-sm" placeholder="مثال: خزينة فرع المنصورة / بنك مصر حساب جاري">
                    </div>

                    <!-- Account Name English -->
                    <div>
                        <label for="name_en" class="erp-label">اسم الحساب بالإنجليزية (اختياري)</label>
                        <input type="text" name="name_en" id="name_en" class="erp-input text-sm" placeholder="e.g. Mansoura Branch Safe">
                    </div>

                    <!-- Custom Code (Optional) -->
                    <div>
                        <label for="code" class="erp-label">كود الحساب (اختياري - يترك فارغاً للترقيم الآلي)</label>
                        <input type="text" name="code" id="code" class="erp-input text-sm font-mono" placeholder="يولد آلياً بالتسلسل في حال تركه فارغاً">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="erp-label">ملاحظات ووصف الحساب</label>
                        <textarea name="description" id="description" rows="2" class="erp-input text-sm" placeholder="أية ملاحظات إضافية حول الحساب واستخداماته..."></textarea>
                    </div>
                </div>

                <div class="erp-modal-footer">
                    <button type="button" onclick="document.getElementById('newAccountModal').style.display='none'" class="erp-btn erp-btn-secondary">إلغاء</button>
                    <button type="submit" class="erp-btn erp-btn-primary">
                        <x-icon name="plus" size="16" />
                        <span>حفظ الحساب الجديد</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tree Interaction Scripts -->
    <script>
        function toggleAccountNode(btn, childrenId) {
            const container = document.getElementById(childrenId);
            if (!container) return;
            const icon = btn.querySelector('.erp-icon');
            if (container.style.display === 'none') {
                container.style.display = 'block';
                if (icon) icon.classList.remove('-rotate-90');
            } else {
                container.style.display = 'none';
                if (icon) icon.classList.add('-rotate-90');
            }
        }

        function expandAllNodes() {
            document.querySelectorAll('.account-children').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.node-toggle-btn .erp-icon').forEach(icon => icon.classList.remove('-rotate-90'));
        }

        function collapseAllNodes() {
            document.querySelectorAll('.account-children').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.node-toggle-btn .erp-icon').forEach(icon => icon.classList.add('-rotate-90'));
        }

        function openAddSubAccountModal(parentId, parentLabel) {
            const modal = document.getElementById('newAccountModal');
            const select = document.getElementById('modalParentSelect');
            if (select) {
                select.value = parentId;
            }
            modal.style.display = 'flex';
        }

        function filterAccountTree(query) {
            query = query.trim().toLowerCase();
            const rows = document.querySelectorAll('.account-row-wrapper');
            
            if (!query) {
                rows.forEach(r => r.style.display = 'block');
                document.querySelectorAll('.account-children').forEach(c => c.style.display = 'block');
                return;
            }

            rows.forEach(row => {
                const name = (row.dataset.accountName || '').toLowerCase();
                const code = (row.dataset.accountCode || '').toLowerCase();
                if (name.includes(query) || code.includes(query)) {
                    row.style.display = 'block';
                    // Show parent containers
                    let p = row.closest('.account-children');
                    while (p) {
                        p.style.display = 'block';
                        p = p.parentElement?.closest('.account-children');
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
@endsection
