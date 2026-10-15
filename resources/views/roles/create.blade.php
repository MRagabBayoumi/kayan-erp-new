@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="إضافة دور جديد وتحديد صلاحياته" :breadcrumbs="['الإدارة والنظام' => route('settings.index'), 'الأدوار' => route('roles.index'), 'إضافة دور' => route('roles.create')]">
        <x-slot:actions>
            <a href="{{ route('roles.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>العودة للأدوار</span>
            </a>
        </x-slot:actions>
    </x-control-panel>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        @if($errors->any())
            <div class="erp-alert erp-alert-danger mb-4" role="alert">
                <x-icon name="alert-triangle" size="16" />
                <div>
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="my-1 mx-4 text-xs list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <!-- Basic Info Card -->
            <div class="erp-card mb-4">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="shield" size="18" />
                        <span>بيانات الدور الأساسية</span>
                    </div>
                </div>
                <div class="erp-card-body">
                    <div class="erp-form-group mb-3">
                        <label for="roleName" class="erp-form-label">
                            اسم الدور الوظيفي <span class="text-[var(--danger)]">*</span>
                        </label>
                        <input
                            type="text"
                            id="roleName"
                            name="name"
                            value="{{ old('name') }}"
                            class="erp-input @error('name') erp-input-error @enderror"
                            placeholder="مثال: مشرف مبيعات الجملة، أمين مخزن فرعي، محاسب توريدات"
                            required
                            autofocus
                        >
                    </div>

                    <div class="erp-form-group">
                        <label for="roleDescription" class="erp-form-label">الوصف والمهام الوظيفية</label>
                        <textarea
                            id="roleDescription"
                            name="description"
                            rows="2"
                            class="erp-input"
                            placeholder="وصف مختصر لمسؤوليات هذا الدور في النظام..."
                        >{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Permissions Matrix by Module -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <x-icon name="check-square" size="18" />
                        <span>مصفوفة الصلاحيات الممنوحة لهذا الدور</span>
                    </div>
                </div>
                <div class="erp-card-body">
                    <div class="role-matrix-container">
                        @foreach($groupedPermissions as $moduleName => $permissions)
                            <div class="role-matrix-module">
                                <div class="role-matrix-header">
                                    <div class="role-matrix-title-group">
                                        <x-icon name="folder" size="16" class="text-[var(--primary)]" />
                                        <span>{{ $moduleName }}</span>
                                        <span class="erp-badge erp-badge-neutral text-[10px]">{{ $permissions->count() }} صلاحية</span>
                                    </div>
                                    <button type="button" class="erp-btn erp-btn-sm erp-btn-ghost" data-action="toggle-module-permissions" title="تحديد / إلغاء تحديد الكل لهذا القسم">
                                        <span>تحديد الكل</span>
                                    </button>
                                </div>

                                <div class="role-matrix-grid">
                                    @foreach($permissions as $perm)
                                        <label class="role-perm-card">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $perm->id }}"
                                                {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}
                                                class="role-perm-checkbox"
                                            >
                                            <div class="role-perm-content">
                                                <span class="role-perm-name">{{ $perm->name }}</span>
                                                @if($perm->description)
                                                    <span class="role-perm-desc">{{ $perm->description }}</span>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="role-form-actions">
                    <a href="{{ route('roles.index') }}" class="erp-btn erp-btn-secondary">إلغاء</a>
                    <button type="submit" class="erp-btn erp-btn-primary">
                        <x-icon name="check" size="16" />
                        <span>حفظ الدور وصلاحياته</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
