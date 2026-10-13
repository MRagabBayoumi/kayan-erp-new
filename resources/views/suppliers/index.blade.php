@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="دليل الموردين ومصانع الصين" :breadcrumbs="['العملاء والموردون' => route('suppliers.index'), 'الموردون' => route('suppliers.index')]">
        <x-slot:actions>
            <button type="button" data-action="open-add-supplier" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>إضافة مورد جديد</span>
            </button>
            <a href="{{ route('customers.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="users" size="16" />
                <span>دليل العملاء</span>
            </a>
            <a href="{{ route('shipping-offices.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="navigation" size="16" />
                <span>مكاتب الشحن</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric px-2.5 py-1 text-xs">
                <span>إجمالي الموردين:</span>
                <strong>{{ $suppliers->total() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="suppliers">
        @if(session('success'))
            <div class="erp-alert erp-alert-success mb-4" role="alert">
                <x-icon name="check" size="16" />
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="erp-alert erp-alert-danger mb-4" role="alert">
                <x-icon name="alert-triangle" size="16" />
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="erp-alert erp-alert-danger mb-4" role="alert">
                <x-icon name="alert-triangle" size="16" />
                <div>
                    <strong>يوجد خطأ في إدخال البيانات:</strong>
                    <ul class="m-0 mt-1 pr-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="supplier-kpi-grid">
            <div class="supplier-kpi-card">
                <div class="supplier-kpi-icon primary">
                    <x-icon name="truck" size="22" />
                </div>
                <div class="supplier-kpi-info">
                    <div class="supplier-kpi-label">إجمالي الموردين والجهات</div>
                    <div class="supplier-kpi-value font-numeric">{{ number_format($metrics['total_suppliers']) }}</div>
                </div>
            </div>

            <div class="supplier-kpi-card">
                <div class="supplier-kpi-icon danger">
                    <x-icon name="home" size="22" />
                </div>
                <div class="supplier-kpi-info">
                    <div class="supplier-kpi-label">مصانع وموردي الصين</div>
                    <div class="supplier-kpi-value font-numeric">{{ number_format($metrics['china_factories_count']) }}</div>
                </div>
            </div>

            <div class="supplier-kpi-card">
                <div class="supplier-kpi-icon info">
                    <x-icon name="package" size="22" />
                </div>
                <div class="supplier-kpi-info">
                    <div class="supplier-kpi-label">الموردون المحليون (مصر)</div>
                    <div class="supplier-kpi-value font-numeric">{{ number_format($metrics['local_suppliers_count']) }}</div>
                </div>
            </div>

            <div class="supplier-kpi-card">
                <div class="supplier-kpi-icon warning">
                    <x-icon name="navigation" size="22" />
                </div>
                <div class="supplier-kpi-info">
                    <div class="supplier-kpi-label">وكلاء الشحن والتخليص</div>
                    <div class="supplier-kpi-value font-numeric">{{ number_format($metrics['shipping_agents_count']) }}</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="supplier-filter-bar">
            <form action="{{ route('suppliers.index') }}" method="GET" class="supplier-filter-inputs">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    class="supplier-filter-input" 
                    placeholder="ابحث باسم المصنع، المسؤول، الهاتف، أو WeChat...">

                <select name="type" class="supplier-filter-input">
                    <option value="">جميع أنواع الموردين</option>
                    <option value="china_factory" @selected(request('type') === 'china_factory')>مصنع في الصين</option>
                    <option value="local_supplier" @selected(request('type') === 'local_supplier')>مورد محلي</option>
                    <option value="shipping_agent" @selected(request('type') === 'shipping_agent')>وكيل شحن / تخليص</option>
                </select>

                @if($countries->isNotEmpty())
                    <select name="country" class="supplier-filter-input">
                        <option value="">جميع الدول</option>
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt }}" @selected(request('country') === $cnt)>{{ $cnt }}</option>
                        @endforeach
                    </select>
                @endif

                <button type="submit" class="erp-btn erp-btn-sm erp-btn-primary">
                    <x-icon name="search" size="14" />
                    <span>تصفية</span>
                </button>

                @if(request()->hasAny(['search', 'type', 'country']))
                    <a href="{{ route('suppliers.index') }}" class="erp-btn erp-btn-sm erp-btn-secondary" title="إلغاء التصفية">
                        <x-icon name="refresh" size="14" />
                        <span>إلغاء</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Suppliers Table -->
        <div class="erp-card overflow-hidden">
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">#</th>
                            <th>المورد / المصنع</th>
                            <th>التصنيف</th>
                            <th>الدولة / المدينة</th>
                            <th>المسؤول للتواصل</th>
                            <th>الهاتف / الاتصال</th>
                            <th>حساب WeChat</th>
                            <th class="text-center">عملة الحساب</th>
                            <th class="text-end">الرصيد المعلق</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center w-28">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="text-center font-numeric text-xs text-[var(--text-muted)]">
                                    {{ $supplier->id }}
                                </td>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="font-bold text-[var(--text-main)] hover:text-[var(--primary)] block">
                                        {{ $supplier->name }}
                                    </a>
                                    @if($supplier->address)
                                        <div class="text-[11px] text-[var(--text-muted)] truncate max-w-xs">
                                            {{ $supplier->address }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->supplier_type === 'china_factory')
                                        <span class="erp-badge erp-badge-danger text-xs">مصنع بالصين</span>
                                    @elseif($supplier->supplier_type === 'shipping_agent')
                                        <span class="erp-badge erp-badge-warning text-xs">وكيل شحن</span>
                                    @else
                                        <span class="erp-badge erp-badge-info text-xs">مورد محلي</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs font-semibold text-[var(--text-main)]">
                                        {{ $supplier->country ?: 'China' }}
                                    </div>
                                    @if($supplier->city)
                                        <div class="text-[11px] text-[var(--text-muted)]">
                                            {{ $supplier->city }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs text-[var(--text-main)]">
                                        {{ $supplier->contact_person ?: '—' }}
                                    </div>
                                    @if($supplier->email)
                                        <div class="text-[11px] text-[var(--text-muted)] direction-ltr">
                                            {{ $supplier->email }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->phone)
                                        <a href="tel:{{ $supplier->phone }}" class="font-numeric text-xs text-[var(--text-main)] hover:text-[var(--primary)] direction-ltr inline-block">
                                            {{ $supplier->phone }}
                                        </a>
                                    @else
                                        <span class="text-xs text-[var(--text-muted)]">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->wechat_id)
                                        <span class="supplier-wechat-tag font-numeric">
                                            <x-icon name="smartphone" size="12" />
                                            <span>{{ $supplier->wechat_id }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-[var(--text-muted)]">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-numeric font-bold text-xs">
                                    <span class="erp-badge erp-badge-neutral">
                                        {{ $supplier->currency }}
                                    </span>
                                </td>
                                <td class="text-end font-numeric font-bold text-xs">
                                    @if($supplier->current_balance != 0)
                                        <span class="{{ $supplier->current_balance > 0 ? 'text-[var(--warning)]' : 'text-[var(--success)]' }}">
                                            {{ number_format($supplier->current_balance, 2) }} {{ $supplier->currency }}
                                        </span>
                                    @else
                                        <span class="text-[var(--text-muted)]">0.00 {{ $supplier->currency }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="erp-badge {{ $supplier->is_active ? 'erp-badge-neutral' : 'erp-badge-danger' }} text-xs">
                                        {{ $supplier->is_active ? 'نشط' : 'معطل' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="erp-btn erp-btn-sm erp-btn-secondary" title="ملف المورد">
                                            <x-icon name="truck" size="14" />
                                        </a>
                                        <button 
                                            type="button" 
                                            data-action="open-edit-supplier"
                                            data-supplier="{{ json_encode($supplier) }}"
                                            class="erp-btn erp-btn-sm erp-btn-secondary"
                                            title="تعديل">
                                            <x-icon name="edit" size="14" />
                                        </button>
                                        @if((float) $supplier->current_balance == 0.0)
                                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" data-confirm="هل أنت متأكد من حذف المورد {{ $supplier->name }}؟" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="erp-btn erp-btn-sm erp-btn-danger" title="حذف">
                                                    <x-icon name="trash" size="14" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-10 text-[var(--text-muted)]">
                                    <x-icon name="truck" size="40" class="mb-2 inline-block text-[var(--text-muted)]" />
                                    <div class="font-bold text-base mb-1">لا يوجد موردون مسجلون مطابقون</div>
                                    <div class="text-xs mb-3">سجل مصانع الاستيراد أو الموردين المحليين لتسهيل إصدار أوامر الشراء.</div>
                                    <button type="button" data-action="open-add-supplier" class="erp-btn erp-btn-sm erp-btn-primary">
                                        <x-icon name="plus" size="14" />
                                        <span>إضافة مورد جديد</span>
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="p-4 border-t border-[var(--border-color)]">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Add Supplier -->
        <div id="addSupplierModal" class="erp-modal-overlay">
            <div class="erp-modal-container max-w-xl">
                <div class="erp-modal-header">
                    <div class="erp-modal-title">
                        <x-icon name="truck" size="18" />
                        <span>إضافة مورد / مصنع جديد</span>
                    </div>
                    <button type="button" data-action="close-add-supplier" class="erp-modal-close" title="إغلاق">✕</button>
                </div>

                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="erp-modal-body p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">اسم المورد / المصنع *</label>
                                <input type="text" name="name" class="erp-input w-full" placeholder="مثال: Guangzhou Bag Factory" required>
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">نوع المورد *</label>
                                <select name="supplier_type" class="erp-input w-full" required>
                                    <option value="china_factory">مصنع في الصين (استيراد)</option>
                                    <option value="local_supplier">مورد محلي (مصر)</option>
                                    <option value="shipping_agent">وكيل شحن وتخليص جمركي</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">المسؤول أو جهة الاتصال</label>
                                <input type="text" name="contact_person" class="erp-input w-full" placeholder="مثال: Mr. Wang / أ. أحمد">
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">معرّف وي شات (WeChat ID)</label>
                                <input type="text" name="wechat_id" class="erp-input font-numeric w-full" placeholder="WeChat ID لمصانع الصين">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">رقم الهاتف / الواتساب</label>
                                <input type="text" name="phone" class="erp-input font-numeric w-full" placeholder="+86 xxxxxxxxx / 01xxxxxxxxx">
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">البريد الإلكتروني</label>
                                <input type="email" name="email" class="erp-input font-numeric w-full" placeholder="supplier@domain.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">الدولة</label>
                                <input type="text" name="country" value="China" class="erp-input w-full" placeholder="China / مصر">
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">المدينة أو المقاطعة</label>
                                <input type="text" name="city" class="erp-input w-full" placeholder="Guangzhou, Yiwu, القاهرة">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">عملة الحساب المعتمدة *</label>
                                <select name="currency" class="erp-input w-full" required>
                                    <option value="CNY">اليوان الصيني (CNY ¥)</option>
                                    <option value="USD">الدولار الأمريكي (USD $)</option>
                                    <option value="EGP">الجنيه المصري (EGP ج.م)</option>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">الرصيد المالي الافتتاحي</label>
                                <input type="number" step="0.01" name="current_balance" value="0.00" class="erp-input font-numeric w-full">
                            </div>
                        </div>

                        <div class="mb-3.5">
                            <label class="erp-label block mb-1.5 text-xs font-semibold">العنوان التفصيلي وموقع المصنع</label>
                            <input type="text" name="address" class="erp-input w-full" placeholder="عنوان المصنع، رقم المستودع، أو المحل">
                        </div>

                        <div class="mb-3.5">
                            <label class="erp-label block mb-1.5 text-xs font-semibold">ملاحظات إضافية</label>
                            <textarea name="notes" class="erp-input w-full" rows="2" placeholder="ملاحظات حول جودة البضائع، شروط الدفع، موانئ الشحن المفضلة..."></textarea>
                        </div>

                        <div>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-[var(--text-main)]">
                                <input type="checkbox" name="is_active" value="1" checked>
                                <span>حساب المورد نشط</span>
                            </label>
                        </div>
                    </div>

                    <div class="erp-modal-footer p-4 flex justify-end gap-2.5 bg-[var(--bg-surface-alt)] border-t border-[var(--border-light)]">
                        <button type="button" data-action="close-add-supplier" class="erp-btn erp-btn-secondary">إلغاء</button>
                        <button type="submit" class="erp-btn erp-btn-primary">حفظ المورد</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Edit Supplier -->
        <div id="editSupplierModal" class="erp-modal-overlay">
            <div class="erp-modal-container max-w-xl">
                <div class="erp-modal-header">
                    <div class="erp-modal-title">
                        <x-icon name="edit" size="18" />
                        <span>تعديل بيانات المورد</span>
                    </div>
                    <button type="button" data-action="close-edit-supplier" class="erp-modal-close" title="إغلاق">✕</button>
                </div>

                <form id="editSupplierForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="erp-modal-body p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">اسم المورد / المصنع *</label>
                                <input type="text" id="edit_sup_name" name="name" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">نوع المورد *</label>
                                <select id="edit_sup_supplier_type" name="supplier_type" class="erp-input w-full" required>
                                    <option value="china_factory">مصنع في الصين (استيراد)</option>
                                    <option value="local_supplier">مورد محلي (مصر)</option>
                                    <option value="shipping_agent">وكيل شحن وتخليص جمركي</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">المسؤول أو جهة الاتصال</label>
                                <input type="text" id="edit_sup_contact_person" name="contact_person" class="erp-input w-full">
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">معرّف وي شات (WeChat ID)</label>
                                <input type="text" id="edit_sup_wechat_id" name="wechat_id" class="erp-input font-numeric w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">رقم الهاتف / الواتساب</label>
                                <input type="text" id="edit_sup_phone" name="phone" class="erp-input font-numeric w-full">
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">البريد الإلكتروني</label>
                                <input type="email" id="edit_sup_email" name="email" class="erp-input font-numeric w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">الدولة</label>
                                <input type="text" id="edit_sup_country" name="country" class="erp-input w-full">
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">المدينة أو المقاطعة</label>
                                <input type="text" id="edit_sup_city" name="city" class="erp-input w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 mb-3.5">
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">عملة الحساب المعتمدة *</label>
                                <select id="edit_sup_currency" name="currency" class="erp-input w-full" required>
                                    <option value="CNY">اليوان الصيني (CNY ¥)</option>
                                    <option value="USD">الدولار الأمريكي (USD $)</option>
                                    <option value="EGP">الجنيه المصري (EGP ج.م)</option>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label block mb-1.5 text-xs font-semibold">الرصيد المالي الحالي</label>
                                <input type="number" step="0.01" id="edit_sup_current_balance" name="current_balance" class="erp-input font-numeric w-full">
                            </div>
                        </div>

                        <div class="mb-3.5">
                            <label class="erp-label block mb-1.5 text-xs font-semibold">العنوان التفصيلي وموقع المصنع</label>
                            <input type="text" id="edit_sup_address" name="address" class="erp-input w-full">
                        </div>

                        <div class="mb-3.5">
                            <label class="erp-label block mb-1.5 text-xs font-semibold">ملاحظات إضافية</label>
                            <textarea id="edit_sup_notes" name="notes" class="erp-input w-full" rows="2"></textarea>
                        </div>

                        <div>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-[var(--text-main)]">
                                <input type="checkbox" id="edit_sup_is_active" name="is_active" value="1">
                                <span>حساب المورد نشط</span>
                            </label>
                        </div>
                    </div>

                    <div class="erp-modal-footer p-4 flex justify-end gap-2.5 bg-[var(--bg-surface-alt)] border-t border-[var(--border-light)]">
                        <button type="button" data-action="close-edit-supplier" class="erp-btn erp-btn-secondary">إلغاء</button>
                        <button type="submit" class="erp-btn erp-btn-primary">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
