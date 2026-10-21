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
        @include('accounting.nav-tabs')

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
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
            <!-- 1. Assets -->
            <div class="erp-card p-3 border-r-4 border-r-sky-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1 flex items-center justify-between">
                    <span>1. الأصول (Assets)</span>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <div class="font-numeric text-base lg:text-lg font-black text-sky-700 dark:text-sky-300">
                    {{ number_format($metrics['total_assets'], 2) }}
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 2. Liabilities -->
            <div class="erp-card p-3 border-r-4 border-r-amber-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1 flex items-center justify-between">
                    <span>2. الخصوم (Liabilities)</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="font-numeric text-base lg:text-lg font-black text-amber-700 dark:text-amber-300">
                    {{ number_format($metrics['total_liabilities'], 2) }}
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 3. Equity -->
            <div class="erp-card p-3 border-r-4 border-r-indigo-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1 flex items-center justify-between">
                    <span>3. حقوق الملكية (Equity)</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <div class="font-numeric text-base lg:text-lg font-black text-indigo-700 dark:text-indigo-300">
                    {{ number_format($metrics['total_equity'], 2) }}
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 4. Revenues -->
            <div class="erp-card p-3 border-r-4 border-r-emerald-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1 flex items-center justify-between">
                    <span>4. الإيرادات (Revenue)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="font-numeric text-base lg:text-lg font-black text-emerald-700 dark:text-emerald-300">
                    {{ number_format($metrics['total_revenues'], 2) }}
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 5. Expenses -->
            <div class="erp-card p-3 border-r-4 border-r-rose-500 hover:shadow-md transition-shadow">
                <div class="text-[11px] font-bold text-muted mb-1 flex items-center justify-between">
                    <span>5. المصروفات (Expenses)</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="font-numeric text-base lg:text-lg font-black text-rose-700 dark:text-rose-300">
                    {{ number_format($metrics['total_expenses'], 2) }}
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                </div>
            </div>

            <!-- 6. Net Profit / Income -->
            <div class="erp-card p-3 border-r-4 {{ $metrics['net_income'] >= 0 ? 'border-r-teal-500' : 'border-r-red-600' }} hover:shadow-md transition-shadow bg-surface-alt/40">
                <div class="text-[11px] font-bold text-muted mb-1 flex items-center justify-between">
                    <span>صافي النشاط التشغيلي</span>
                    <x-icon name="trending-up" size="12" class="{{ $metrics['net_income'] >= 0 ? 'text-teal-500' : 'text-red-500' }}" />
                </div>
                <div class="font-numeric text-base lg:text-lg font-black {{ $metrics['net_income'] >= 0 ? 'text-teal-700 dark:text-teal-300' : 'text-red-700 dark:text-red-300' }}">
                    {{ number_format($metrics['net_income'], 2) }}
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                </div>
            </div>
        </div>

        <!-- Filter, Search & Level Controls Card -->
        <div class="erp-card mb-4 p-3.5 shadow-sm">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-3">
                <!-- Type Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 w-full lg:w-auto">
                    <a href="{{ route('accounting.chart-of-accounts') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ empty($currentType) ? 'bg-primary text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        <span>كافة الحسابات</span>
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'asset']) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentType === 'asset' ? 'bg-sky-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        <span class="w-2 h-2 rounded-full inline-block bg-sky-500 mr-1"></span>
                        <span>الأصول</span>
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'liability']) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentType === 'liability' ? 'bg-amber-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        <span class="w-2 h-2 rounded-full inline-block bg-amber-500 mr-1"></span>
                        <span>الخصوم</span>
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'equity']) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentType === 'equity' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        <span class="w-2 h-2 rounded-full inline-block bg-indigo-500 mr-1"></span>
                        <span>حقوق الملكية</span>
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'revenue']) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentType === 'revenue' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        <span class="w-2 h-2 rounded-full inline-block bg-emerald-500 mr-1"></span>
                        <span>الإيرادات</span>
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts', ['type' => 'expense']) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentType === 'expense' ? 'bg-rose-600 text-white shadow-sm' : 'bg-surface hover:bg-surface-hover text-muted border border-border' }}">
                        <span class="w-2 h-2 rounded-full inline-block bg-rose-500 mr-1"></span>
                        <span>المصروفات</span>
                    </a>
                </div>

                <!-- Instant Search & Quick Expansion Depth Controls -->
                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-between lg:justify-end">
                    <!-- Live Search -->
                    <div class="relative w-full sm:w-64">
                        <input type="text" 
                               id="accountTreeSearch" 
                               placeholder="بحث بكود أو اسم الحساب..." 
                               class="erp-input text-xs pr-8 pl-6 w-full" 
                               onkeyup="filterAccountTree(this.value)">
                        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted">
                            <x-icon name="search" size="14" />
                        </div>
                        <button type="button" 
                                id="clearSearchBtn"
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted hover:text-foreground hidden"
                                onclick="clearSearch()">
                            <x-icon name="x" size="12" />
                        </button>
                    </div>

                    <!-- Expansion Level Controls -->
                    <div class="inline-flex rounded-lg border border-border bg-surface p-0.5 text-xs font-medium text-muted">
                        <button type="button" class="px-2 py-1 rounded hover:bg-surface-active hover:text-foreground transition-colors" onclick="expandToLevel(1)" title="طي لكافة الحسابات الرئيسية (المستوى 1)">
                            <span>مستوى 1</span>
                        </button>
                        <button type="button" class="px-2 py-1 rounded hover:bg-surface-active hover:text-foreground transition-colors" onclick="expandToLevel(2)" title="توسيع الحسابات العامة (المستوى 2)">
                            <span>مستوى 2</span>
                        </button>
                        <button type="button" class="px-2 py-1 rounded hover:bg-surface-active hover:text-foreground transition-colors" onclick="expandAllNodes()" title="توسيع كافة الفروع والحسابات">
                            <span>توسيع الكل</span>
                        </button>
                        <button type="button" class="px-2 py-1 rounded hover:bg-surface-active hover:text-foreground transition-colors" onclick="collapseAllNodes()" title="طي الشجرة بالكامل">
                            <span>طي الكل</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- The Chart of Accounts Tree Card -->
        <div class="coa-tree-container">
            <!-- Header Bar -->
            <div class="coa-header-row">
                <div class="flex items-center gap-2">
                    <x-icon name="list" size="15" />
                    <span>هيكل الحسابات والأدلة المحاسبية العامة</span>
                </div>
                <div class="flex items-center gap-6">
                    <span class="hidden xl:inline">طبيعة الحساب</span>
                    <span class="hidden sm:inline">تصنيف القائمة</span>
                    <span class="min-w-[130px] text-left">الرصيد الدفتري الحالي</span>
                    <span class="w-[52px] text-center">إجراءات</span>
                </div>
            </div>

            <!-- Tree Nodes Root -->
            <div id="accountsTreeContainer">
                @forelse($rootAccounts as $root)
                    @include('accounting.partials.account-node', ['account' => $root, 'level' => 1])
                @empty
                    <div class="p-12 text-center text-muted">
                        <x-icon name="alert-circle" size="40" class="mx-auto mb-2 opacity-30" />
                        <p class="font-bold text-sm">لا توجد حسابات مسجلة مطابقة لمعايير البحث.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal: Add Sub-Account -->
    <div id="newAccountModal" class="erp-modal-backdrop" style="display: none;">
        <div class="erp-modal-box max-w-lg">
            <div class="erp-modal-header">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <x-icon name="plus" size="16" />
                    </span>
                    <h3 class="erp-modal-title">إضافة حساب فرعي جديد إلى الدليل الشجري</h3>
                </div>
                <button type="button" onclick="document.getElementById('newAccountModal').style.display='none'" class="erp-modal-close">
                    <x-icon name="x" size="18" />
                </button>
            </div>

            <form action="{{ route('accounting.accounts.store') }}" method="POST">
                @csrf
                <div class="erp-modal-body space-y-4">
                    <!-- Parent Account Selection -->
                    <div>
                        <label for="modalParentSelect" class="erp-label required">الحساب الرئيسي (الأب / المتفرع منه)</label>
                        <select name="parent_id" id="modalParentSelect" required class="erp-input text-sm font-sans" onchange="updateSuggestedCode(this)">
                            <option value="">-- اختر الحساب الأب --</option>
                            @foreach($eligibleParents as $p)
                                <option value="{{ $p->id }}" data-code="{{ $p->code }}" data-type="{{ $p->type_label }}" data-nature="{{ $p->nature_label }}">
                                    {{ str_repeat('— ', $p->level - 1) }}{{ $p->code }} - {{ $p->name }} ({{ $p->type_label }})
                                </option>
                            @endforeach
                        </select>
                        <div id="parentMetaHint" class="text-[11px] text-muted mt-1 hidden">
                            <span>النوع: <strong id="hintType" class="text-foreground"></strong></span> |
                            <span>الطبيعة: <strong id="hintNature" class="text-foreground"></strong></span>
                        </div>
                    </div>

                    <!-- Account Name Arabic -->
                    <div>
                        <label for="name" class="erp-label required">اسم الحساب بالعربية</label>
                        <input type="text" name="name" id="name" required class="erp-input text-sm" placeholder="مثال: خزينة فرع المنصورة / بنك مصر حساب جاري">
                    </div>

                    <!-- Account Name English -->
                    <div>
                        <label for="name_en" class="erp-label">اسم الحساب بالإنجليزية (اختياري)</label>
                        <input type="text" name="name_en" id="name_en" class="erp-input text-sm font-sans" placeholder="e.g. Mansoura Branch Cash Safe">
                    </div>

                    <!-- Custom Code (Optional) -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="code" class="erp-label">كود الحساب الشجري</label>
                            <span class="text-[11px] text-muted">يترك فارغاً للترقيم التلقائي المتسلسل</span>
                        </div>
                        <input type="text" name="code" id="code" class="erp-input text-sm font-mono" placeholder="اتركه فارغاً للتوليد الآلي">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="erp-label">بيان أو ملاحظات على استخدام الحساب</label>
                        <textarea name="description" id="description" rows="2" class="erp-input text-sm" placeholder="أية ملاحظات إضافية أو استخدامات خاصة بهذا الحساب..."></textarea>
                    </div>
                </div>

                <div class="erp-modal-footer">
                    <button type="button" onclick="document.getElementById('newAccountModal').style.display='none'" class="erp-btn erp-btn-secondary">إلغاء</button>
                    <button type="submit" class="erp-btn erp-btn-primary">
                        <x-icon name="plus" size="16" />
                        <span>تأكيد وحفظ الحساب</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Floating Toast for Copied Code -->
    <div id="copyToast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-foreground text-background text-xs font-bold py-2 px-4 rounded-full shadow-lg z-50 opacity-0 pointer-events-none transition-opacity duration-200">
        تم نسخ كود الحساب إلى الحافظة!
    </div>

    <!-- Tree Interaction Scripts -->
    <script>
        function toggleAccountNode(btn, childrenId) {
            const container = document.getElementById(childrenId);
            if (!container) return;
            const icon = btn.querySelector('.erp-icon');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                btn.classList.remove('is-collapsed');
                if (icon) icon.classList.remove('-rotate-90');
            } else {
                container.style.display = 'none';
                btn.classList.add('is-collapsed');
                if (icon) icon.classList.add('-rotate-90');
            }
        }

        function expandAllNodes() {
            document.querySelectorAll('.account-children').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.node-toggle-btn').forEach(btn => {
                btn.classList.remove('is-collapsed');
                const icon = btn.querySelector('.erp-icon');
                if (icon) icon.classList.remove('-rotate-90');
            });
        }

        function collapseAllNodes() {
            document.querySelectorAll('.account-children').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.node-toggle-btn').forEach(btn => {
                btn.classList.add('is-collapsed');
                const icon = btn.querySelector('.erp-icon');
                if (icon) icon.classList.add('-rotate-90');
            });
        }

        function expandToLevel(targetLevel) {
            collapseAllNodes();
            document.querySelectorAll('.account-row-wrapper').forEach(row => {
                const lvl = parseInt(row.dataset.level || '1', 10);
                if (lvl <= targetLevel) {
                    const id = row.dataset.accountId;
                    const childrenContainer = document.getElementById('children-' + id);
                    if (childrenContainer) {
                        childrenContainer.style.display = 'block';
                        const toggleBtn = row.querySelector('.node-toggle-btn');
                        if (toggleBtn) {
                            toggleBtn.classList.remove('is-collapsed');
                            const icon = toggleBtn.querySelector('.erp-icon');
                            if (icon) icon.classList.remove('-rotate-90');
                        }
                    }
                }
            });
        }

        function openAddSubAccountModal(parentId, parentLabel) {
            const modal = document.getElementById('newAccountModal');
            const select = document.getElementById('modalParentSelect');
            if (select) {
                select.value = parentId;
                updateSuggestedCode(select);
            }
            modal.style.display = 'flex';
        }

        function updateSuggestedCode(select) {
            const selectedOpt = select.options[select.selectedIndex];
            const hintDiv = document.getElementById('parentMetaHint');
            if (!selectedOpt || !selectedOpt.value) {
                if (hintDiv) hintDiv.classList.add('hidden');
                return;
            }

            if (hintDiv) {
                document.getElementById('hintType').textContent = selectedOpt.dataset.type || '—';
                document.getElementById('hintNature').textContent = selectedOpt.dataset.nature || '—';
                hintDiv.classList.remove('hidden');
            }
        }

        function filterAccountTree(query) {
            query = query.trim().toLowerCase();
            const clearBtn = document.getElementById('clearSearchBtn');
            const rows = document.querySelectorAll('.account-row-wrapper');
            
            if (clearBtn) {
                clearBtn.style.display = query ? 'block' : 'none';
            }

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
                    
                    // Uncollapse and show all ancestor parent containers
                    let p = row.closest('.account-children');
                    while (p) {
                        p.style.display = 'block';
                        const parentRow = p.previousElementSibling;
                        if (parentRow && parentRow.classList.contains('account-row-wrapper')) {
                            parentRow.style.display = 'block';
                            const btn = parentRow.querySelector('.node-toggle-btn');
                            if (btn) {
                                btn.classList.remove('is-collapsed');
                                const icon = btn.querySelector('.erp-icon');
                                if (icon) icon.classList.remove('-rotate-90');
                            }
                        }
                        p = p.parentElement?.closest('.account-children');
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearSearch() {
            const input = document.getElementById('accountTreeSearch');
            if (input) {
                input.value = '';
                filterAccountTree('');
                input.focus();
            }
        }

        function copyAccountCode(code, event) {
            event.stopPropagation();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(() => showToast('تم نسخ كود الحساب [' + code + ']'));
            } else {
                showToast('كود الحساب: ' + code);
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('copyToast');
            if (!toast) return;
            toast.textContent = msg;
            toast.style.opacity = '1';
            setTimeout(() => {
                toast.style.opacity = '0';
            }, 2000);
        }
    </script>
@endsection
