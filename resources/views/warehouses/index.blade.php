@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="المستودعات والمخازن" :breadcrumbs="['المخزون والمنتجات' => route('inventory.index'), 'المستودعات' => route('warehouses.index')]">
        <x-slot:actions>
            <button type="button" onclick="openAddWarehouseModal()" class="erp-btn erp-btn-primary">
                <x-icon name="plus" size="16" />
                <span>إضافة مستودع جديد</span>
            </button>
            <a href="{{ route('inventory.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="warehouse" size="16" />
                <span>نظرة عامة على المخزون</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric" style="padding: 4px 10px; font-size: 12px;">
                <span>عدد المستودعات:</span>
                <strong>{{ $warehouses->count() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="erp-alert erp-alert-success" role="alert" style="margin-bottom: 16px;">
            <x-icon name="check" size="16" />
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 16px;">
            <x-icon name="alert-triangle" size="16" />
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Warehouses Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
        @forelse($warehouses as $warehouse)
            <div class="erp-card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 3px solid {{ $warehouse->is_default ? 'var(--primary)' : 'var(--border-color)' }};">
                <div style="padding: 16px;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">
                                    {{ $warehouse->name }}
                                </h3>
                                @if($warehouse->is_default)
                                    <span class="erp-badge erp-badge-success" style="font-size: 11px; padding: 2px 6px;">افتراضي</span>
                                @endif
                            </div>
                            <div class="font-numeric" style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                                {{ $warehouse->code }}
                            </div>
                        </div>
                        <span class="erp-badge {{ $warehouse->is_active ? 'erp-badge-neutral' : 'erp-badge-danger' }}">
                            {{ $warehouse->is_active ? 'نشط' : 'معطل' }}
                        </span>
                    </div>

                    <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <x-icon name="map-pin" size="14" />
                        <span>{{ $warehouse->location ?: 'لم يحدد الموقع بعد' }}</span>
                    </div>

                    @if($warehouse->notes)
                        <p style="font-size: 12px; color: var(--text-muted); margin: 0 0 12px 0; background: var(--bg-surface-alt); border: 1px solid var(--border-light); padding: 8px 10px; border-radius: 6px;">
                            {{ $warehouse->notes }}
                        </p>
                    @endif

                    <div style="background: var(--bg-surface-alt); border: 1px solid var(--border-light); padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; color: var(--text-secondary); font-weight: 500;">عدد الأصناف المسجلة:</span>
                        <span class="font-numeric" style="font-weight: 800; font-size: 15px; color: var(--text-main);">
                            {{ $warehouse->stock_balances_count }}
                        </span>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-light); padding: 10px 16px; display: flex; justify-content: flex-end; gap: 8px; background: var(--bg-surface-alt);">
                    <button 
                        type="button" 
                        onclick="openEditWarehouseModal({{ json_encode($warehouse) }})"
                        class="erp-btn erp-btn-sm erp-btn-secondary"
                        title="تعديل">
                        <x-icon name="edit" size="14" />
                        <span>تعديل</span>
                    </button>

                    @if(!$warehouse->is_default)
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف المستودع {{ $warehouse->name }}؟');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="erp-btn erp-btn-sm erp-btn-danger" title="حذف">
                                <x-icon name="trash" size="14" />
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="erp-card" style="grid-column: 1 / -1; padding: 40px; text-align: center;">
                <x-icon name="warehouse" size="48" style="color: var(--text-muted); margin-bottom: 12px;" />
                <h3 style="margin: 0 0 8px 0; font-size: 16px;">لا توجد مستودعات مسجلة حتى الآن</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">قم بإنشاء المستودع الرئيسي أو الفروع لتوزيع وإدارة المخزون بدقة.</p>
                <button type="button" onclick="openAddWarehouseModal()" class="erp-btn erp-btn-primary">
                    <x-icon name="plus" size="16" />
                    <span>إضافة أول مستودع</span>
                </button>
            </div>
        @endforelse
    </div>

    <!-- Modal: Add Warehouse -->
    <div id="addWarehouseModal" class="erp-modal-overlay">
        <div class="erp-modal-container">
            <div class="erp-modal-header">
                <div class="erp-modal-title">
                    <x-icon name="home" size="18" />
                    <span>إضافة مستودع جديد</span>
                </div>
                <button type="button" onclick="closeAddWarehouseModal()" class="erp-modal-close" title="إغلاق">✕</button>
            </div>

            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf
                <div class="erp-modal-body" style="padding: 20px;">
                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">كود المستودع *</label>
                        <input type="text" name="code" class="erp-input font-numeric" placeholder="مثال: WH-MAIN, WH-BRANCH-1" required style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">اسم المستودع *</label>
                        <input type="text" name="name" class="erp-input" placeholder="مثال: المستودع الرئيسي - العتبة" required style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">الموقع / العنوان</label>
                        <input type="text" name="location" class="erp-input" placeholder="مثال: شارع عبد العزيز، العتبة، القاهرة" style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">ملاحظات</label>
                        <textarea name="notes" class="erp-input" rows="2" placeholder="ملاحظات إضافية عن المستودع..." style="width: 100%;"></textarea>
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-main);">
                            <input type="checkbox" name="is_default" value="1">
                            <span>تعيين كمستودع افتراضي للنظام</span>
                        </label>
                    </div>
                </div>

                <div class="erp-modal-footer" style="padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px; background: var(--bg-surface-alt); border-top: 1px solid var(--border-light);">
                    <button type="button" onclick="closeAddWarehouseModal()" class="erp-btn erp-btn-secondary">إلغاء</button>
                    <button type="submit" class="erp-btn erp-btn-primary">حفظ المستودع</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Warehouse -->
    <div id="editWarehouseModal" class="erp-modal-overlay">
        <div class="erp-modal-container">
            <div class="erp-modal-header">
                <div class="erp-modal-title">
                    <x-icon name="edit" size="18" />
                    <span>تعديل بيانات المستودع</span>
                </div>
                <button type="button" onclick="closeEditWarehouseModal()" class="erp-modal-close" title="إغلاق">✕</button>
            </div>

            <form id="editWarehouseForm" method="POST">
                @csrf
                @method('PUT')
                <div class="erp-modal-body" style="padding: 20px;">
                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">كود المستودع *</label>
                        <input type="text" id="edit_code" name="code" class="erp-input font-numeric" required style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">اسم المستودع *</label>
                        <input type="text" id="edit_name" name="name" class="erp-input" required style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">الموقع / العنوان</label>
                        <input type="text" id="edit_location" name="location" class="erp-input" style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="erp-label" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">ملاحظات</label>
                        <textarea id="edit_notes" name="notes" class="erp-input" rows="2" style="width: 100%;"></textarea>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-main);">
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <span>مستودع نشط</span>
                        </label>
                    </div>

                    <div style="margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-main);">
                            <input type="checkbox" id="edit_is_default" name="is_default" value="1">
                            <span>تعيين كمستودع افتراضي للنظام</span>
                        </label>
                    </div>
                </div>

                <div class="erp-modal-footer" style="padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px; background: var(--bg-surface-alt); border-top: 1px solid var(--border-light);">
                    <button type="button" onclick="closeEditWarehouseModal()" class="erp-btn erp-btn-secondary">إلغاء</button>
                    <button type="submit" class="erp-btn erp-btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddWarehouseModal() {
            const m = document.getElementById('addWarehouseModal');
            if (m) {
                m.classList.add('open');
                m.style.display = 'flex';
            }
        }
        function closeAddWarehouseModal() {
            const m = document.getElementById('addWarehouseModal');
            if (m) {
                m.classList.remove('open');
                m.style.display = 'none';
            }
        }

        function openEditWarehouseModal(warehouse) {
            const form = document.getElementById('editWarehouseForm');
            form.action = `/warehouses/${warehouse.id}`;
            document.getElementById('edit_code').value = warehouse.code;
            document.getElementById('edit_name').value = warehouse.name;
            document.getElementById('edit_location').value = warehouse.location || '';
            document.getElementById('edit_notes').value = warehouse.notes || '';
            document.getElementById('edit_is_active').checked = !!warehouse.is_active;
            document.getElementById('edit_is_default').checked = !!warehouse.is_default;

            const m = document.getElementById('editWarehouseModal');
            if (m) {
                m.classList.add('open');
                m.style.display = 'flex';
            }
        }
        function closeEditWarehouseModal() {
            const m = document.getElementById('editWarehouseModal');
            if (m) {
                m.classList.remove('open');
                m.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            ['addWarehouseModal', 'editWarehouseModal'].forEach(id => {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            modal.classList.remove('open');
                            modal.style.display = 'none';
                        }
                    });
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddWarehouseModal();
                    closeEditWarehouseModal();
                }
            });
        });
    </script>
@endsection
