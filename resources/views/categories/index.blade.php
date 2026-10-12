@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="تصنيفات المنتجات" :breadcrumbs="['المنتجات والأصناف' => route('products.index'), 'التصنيفات' => '#']">
        <x-slot:actions>
            <a href="{{ route('products.create') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="plus" size="16" />
                <span>إضافة منتج جديد</span>
            </a>
            <a href="{{ route('products.index') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="package" size="16" />
                <span>قائمة المنتجات</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge erp-badge-neutral font-numeric" style="padding: 4px 10px; font-size: 12px;">
                <span>إجمالي التصنيفات:</span>
                <strong>{{ $categories->count() }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Flash Messages -->
    @if(session('status') === 'success')
        <div class="erp-alert erp-alert-success" role="alert" style="margin-bottom: 16px;">
            <x-icon name="check" size="16" />
            <div>{{ session('message', 'تمت العملية بنجاح.') }}</div>
        </div>
    @endif

    @if(session('status') === 'error')
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 16px;">
            <x-icon name="alert-triangle" size="16" />
            <div>{{ session('message', 'حدث خطأ أثناء تنفيذ العملية.') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 16px;">
            <x-icon name="alert-triangle" size="16" />
            <div>
                <strong>يرجى تصحيح الأخطاء التالية:</strong>
                <ul style="margin: 6px 20px 0; font-size: 12px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 340px 1fr; gap: 20px;">
        <!-- Left: Quick Add Category Form -->
        <div class="erp-card">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <x-icon name="plus" size="18" />
                    <span>إضافة تصنيف جديد</span>
                </div>
            </div>
            <div class="erp-card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf

                    <div class="erp-form-group" style="margin-bottom: 14px;">
                        <label for="catName" class="erp-form-label">
                            اسم التصنيف <span style="color: var(--danger);">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="catName" 
                            name="name" 
                            value="{{ old('name') }}" 
                            class="erp-input @error('name') erp-input-error @enderror" 
                            placeholder="مثال: توك شعر، بكلات، أطواق" 
                            required
                        >
                    </div>

                    <div class="erp-form-group" style="margin-bottom: 14px;">
                        <label for="catParent" class="erp-form-label">التصنيف الرئيسي (اختياري)</label>
                        <select id="catParent" name="parent_id" class="erp-input">
                            <option value="">-- بدون (تصنيف رئيسي) --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)old('parent_id') === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="erp-form-group" style="margin-bottom: 14px;">
                        <label for="catDesc" class="erp-form-label">وصف التصنيف</label>
                        <textarea id="catDesc" name="description" rows="3" class="erp-input" placeholder="وصف محتويات هذا التصنيف من الإكسسوارات...">{{ old('description') }}</textarea>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="flex items-center gap-2" style="cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" checked style="width: 16px; height: 16px; accent-color: var(--primary);" />
                            <span style="font-size: 13px; color: var(--text-main);">تفعيل التصنيف</span>
                        </label>
                    </div>

                    <button type="submit" class="erp-btn erp-btn-primary" style="width: 100%; justify-content: center; display: inline-flex; align-items: center; gap: 6px;">
                        <x-icon name="save" size="16" />
                        <span>حفظ التصنيف</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Categories Table -->
        <div class="erp-card" style="padding: 0; overflow: hidden;">
            <div class="erp-card-header" style="padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                <div class="erp-card-title">
                    <x-icon name="tag" size="18" />
                    <span>قائمة التصنيفات الحالية</span>
                </div>
            </div>

            @if($categories->isEmpty())
                <div style="text-align: center; padding: 40px 20px;">
                    <div style="margin-bottom: 12px; opacity: 0.4; display: flex; justify-content: center;">
                        <x-icon name="tag" size="42" />
                    </div>
                    <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main);">لا توجد تصنيفات مسجلة حتى الآن</h3>
                    <p style="font-size: 12px; color: var(--text-muted);">استخدم النموذج على اليمين لإضافة أول تصنيف لمخزونك.</p>
                </div>
            @else
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>اسم التصنيف</th>
                                <th style="width: 150px;">التصنيف الرئيسي</th>
                                <th style="width: 120px; text-align: center;">عدد الأصناف</th>
                                <th style="width: 90px; text-align: center;">الحالة</th>
                                <th style="width: 100px; text-align: center;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>
                                        <strong style="color: var(--text-main); font-size: 13px;">{{ $category->name }}</strong>
                                        @if($category->description)
                                            <small style="display: block; color: var(--text-muted); font-size: 11px;">{{ Str::limit($category->description, 60) }}</small>
                                        @endif
                                    </td>

                                    <td>
                                        @if($category->parent)
                                            <span class="erp-badge erp-badge-neutral">{{ $category->parent->name }}</span>
                                        @else
                                            <span style="color: var(--text-light); font-size: 11px;">تصنيف رئيسي</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;">
                                        <span class="erp-badge erp-badge-info font-numeric">
                                            {{ $category->products_count }} صنف
                                        </span>
                                    </td>

                                    <td style="text-align: center;">
                                        @if($category->is_active)
                                            <span class="erp-badge erp-badge-success" style="font-size: 11px;">نشط</span>
                                        @else
                                            <span class="erp-badge erp-badge-danger" style="font-size: 11px;">معطل</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;">
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف التصنيف: {{ $category->name }}؟');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="erp-btn erp-btn-ghost erp-btn-icon" 
                                                style="color: var(--danger);" 
                                                title="{{ $category->products_count > 0 ? 'لا يمكن حذفه لاحتوائه على منتجات' : 'حذف التصنيف' }}"
                                                {{ $category->products_count > 0 ? 'disabled' : '' }}
                                            >
                                                <x-icon name="trash" size="15" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
