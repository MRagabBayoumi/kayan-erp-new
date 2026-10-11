@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="{{ !empty($reviewData) ? 'مراجعة وتدقيق بيانات الاستيراد' : 'استيراد المنتجات من إكسيل / CSV' }}" :breadcrumbs="['المنتجات والأصناف' => route('products.index'), 'استيراد' => '#']">
        <x-slot:actions>
            @if(!empty($reviewData))
                <button type="submit" form="confirmImportForm" class="erp-btn erp-btn-primary">
                    <span>✓</span>
                    <span>تأكيد الاستيراد الفعلي وحفظ البيانات</span>
                </button>
                <a href="{{ route('products.import.cancel') }}" class="erp-btn erp-btn-secondary">
                    <span>✕</span>
                    <span>إلغاء ورفع ملف آخر</span>
                </a>
            @else
                <a href="{{ route('products.import.template') }}" class="erp-btn erp-btn-secondary" style="border-color: var(--primary); color: var(--primary);">
                    <span>📥</span>
                    <span>تحميل قالب إكسيل الجاهز (اختياري)</span>
                </a>
                <a href="{{ route('products.index') }}" class="erp-btn erp-btn-secondary">
                    <span>←</span>
                    <span>العودة لقائمة المنتجات</span>
                </a>
            @endif
        </x-slot:actions>

        <x-slot:extra>
            @if(!empty($reviewData))
                <span class="erp-badge erp-badge-info font-numeric" style="padding: 4px 10px; font-size: 12px;">
                    <span>إجمالي السطور المفحوصة:</span>
                    <strong>{{ $reviewData['stats']['total'] }}</strong>
                </span>
            @endif
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <!-- Flash Messages -->
    @if(session('status') === 'error')
        <div class="erp-alert erp-alert-danger" role="alert" style="margin-bottom: 20px;">
            <span style="font-size: 16px;">⚠️</span>
            <div>{{ session('message', 'حدث خطأ أثناء معالجة الملف.') }}</div>
        </div>
    @endif

    @if(empty($reviewData))
        <!-- =========================================================================
             STEP 1: UPLOAD FILE & TEMPLATE DOWNLOAD
             ========================================================================= -->
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px;">
            <!-- Upload Box -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <span>📤</span>
                        <span>رفع ملف البيانات للمراجعة والتدقيق</span>
                    </div>
                </div>
                <div class="erp-card-body">
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                        ارفع ملف إكسيل (<strong>.xlsx</strong>) أو ملف قيم مفصولة بفواصل (<strong>.csv</strong>). 
                        سيقوم النظام بفحص محتويات الملف سطر بسطر ومقارنتها بقاعدة البيانات الحالية <code>kayan_erp</code> لاكتشاف الأصناف الجديدة، والتحقق من الأسعار، وتمييز الأصناف الموجودة مسبقاً قبل تنفيذ أي عملية حفظ.
                    </p>

                    <form action="{{ route('products.import.review') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="erp-form-group" style="margin-bottom: 20px;">
                            <label for="importFile" class="erp-form-label" style="font-weight: 700; margin-bottom: 8px;">
                                اختر ملف الإكسيل أو الـ CSV من جهازك:
                            </label>
                            <input 
                                type="file" 
                                id="importFile" 
                                name="import_file" 
                                accept=".csv,.xlsx,.xls,text/csv" 
                                class="erp-input @error('import_file') erp-input-error @enderror" 
                                required
                                style="padding: 10px;"
                            >
                            @error('import_file')
                                <small style="color: var(--danger); display: block; margin-top: 4px;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 16px;">
                            <button type="submit" class="erp-btn erp-btn-primary" style="padding: 10px 24px; font-weight: 700;">
                                <span>🔍</span>
                                <span>فحص ومراجعة محتويات الملف</span>
                            </button>
                            <small style="color: var(--text-muted); font-size: 11px;">الحد الأقصى لحجم الملف: 10MB</small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Optional Template Info Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <span>📥</span>
                        <span>قالب البيانات الجاهز</span>
                    </div>
                </div>
                <div class="erp-card-body" style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="erp-alert erp-alert-info" style="margin: 0; padding: 10px 12px; font-size: 12px;">
                        <span>💡</span>
                        <div>تنزيل القالب <strong>اختياري (Option)</strong> وليس فرضاً، والغرض منه مساعدتك في معرفة التنسيق الأمثل للأعمدة.</div>
                    </div>

                    <a href="{{ route('products.import.template') }}" class="erp-btn erp-btn-secondary" style="width: 100%; justify-content: center; padding: 10px; font-weight: 700; border-color: var(--primary); color: var(--primary);">
                        <span>📥 تحميل القالب المنسق (CSV)</span>
                    </a>

                    <div>
                        <strong style="display: block; font-size: 12px; color: var(--text-main); margin-bottom: 6px;">الأعمدة المدعومة في الملف:</strong>
                        <ul style="font-size: 11px; color: var(--text-muted); margin: 0 16px; line-height: 1.8;">
                            <li><strong>كود الصنف (SKU):</strong> إلزامي وفريد.</li>
                            <li><strong>اسم المنتج:</strong> إلزامي بالعربية.</li>
                            <li><strong>التصنيف:</strong> اختياري (يُنشأ تلقائياً إن لم يوجد).</li>
                            <li><strong>الباركود:</strong> اختياري (يدعم ماسحات الباركود).</li>
                            <li><strong>الأسعار:</strong> سعر التكلفة، سعر الجملة، وسعر التجزئة.</li>
                            <li><strong>وحدة القياس:</strong> قطعة، دستة، كرتونة، ...</li>
                            <li><strong>حد الطلب الأدنى:</strong> رقم صحيح غير سالب.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- =========================================================================
             STEP 2: PRE-IMPORT REVIEW & COMPARISON AGAINST DATABASE
             ========================================================================= -->
        <!-- KPI Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">
            <!-- Total Rows -->
            <div class="kpi-card" style="padding: 14px;">
                <div class="kpi-header">
                    <span class="kpi-label">إجمالي السطور المقروءة</span>
                    <span class="kpi-icon">📊</span>
                </div>
                <div class="kpi-value font-numeric">{{ $reviewData['stats']['total'] }}</div>
                <div class="kpi-note font-numeric">سطر في الملف المرفوع</div>
            </div>

            <!-- New Items -->
            <div class="kpi-card" style="padding: 14px; border-top: 3px solid var(--success);">
                <div class="kpi-header">
                    <span class="kpi-label" style="color: var(--success-text);">أصناف جديدة للإدراج</span>
                    <span class="kpi-icon">✨</span>
                </div>
                <div class="kpi-value font-numeric" style="color: var(--success-text);">{{ $reviewData['stats']['new_count'] }}</div>
                <div class="kpi-note font-numeric">غير مسجلة بقاعدة البيانات</div>
            </div>

            <!-- Existing Items (Update) -->
            <div class="kpi-card" style="padding: 14px; border-top: 3px solid var(--warning);">
                <div class="kpi-header">
                    <span class="kpi-label" style="color: var(--warning);">أصناف قائمة للتحديث</span>
                    <span class="kpi-icon">🔄</span>
                </div>
                <div class="kpi-value font-numeric" style="color: var(--warning);">{{ $reviewData['stats']['update_count'] }}</div>
                <div class="kpi-note font-numeric">موجودة بالكود أو الباركود</div>
            </div>

            <!-- Invalid Rows -->
            <div class="kpi-card" style="padding: 14px; border-top: 3px solid var(--danger);">
                <div class="kpi-header">
                    <span class="kpi-label" style="color: var(--danger);">سطور تحتوي على أخطاء</span>
                    <span class="kpi-icon">⚠️</span>
                </div>
                <div class="kpi-value font-numeric" style="color: var(--danger);">{{ $reviewData['stats']['invalid_count'] }}</div>
                <div class="kpi-note font-numeric">سيتم تخطيها أو تصحيحها</div>
            </div>
        </div>

        <!-- Confirmation Form & Options -->
        <form id="confirmImportForm" action="{{ route('products.import.confirm') }}" method="POST" class="erp-card" style="margin-bottom: 20px; padding: 14px 18px;">
            @csrf

            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 20px;">
                    <label class="flex items-center gap-2" style="cursor: pointer; font-size: 13px; font-weight: 600; color: var(--text-main);">
                        <input type="checkbox" name="update_existing" value="1" checked style="width: 17px; height: 17px; accent-color: var(--primary);">
                        <span>تحديث أسعار وبيانات الأصناف الموجودة مسبقاً</span>
                    </label>

                    <label class="flex items-center gap-2" style="cursor: pointer; font-size: 13px; font-weight: 600; color: var(--text-main);">
                        <input type="checkbox" name="create_categories" value="1" checked style="width: 17px; height: 17px; accent-color: var(--primary);">
                        <span>إنشاء التصنيفات الجديدة تلقائياً إذا لم تكن موجودة</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="erp-btn erp-btn-primary" style="padding: 9px 22px; font-weight: 700;">
                        <span>✓</span>
                        <span>تأكيد الاستيراد الفعلي وحفظ البيانات</span>
                    </button>
                    <a href="{{ route('products.import.cancel') }}" class="erp-btn erp-btn-secondary" style="padding: 9px 16px;">
                        <span>✕</span>
                        <span>إلغاء</span>
                    </a>
                </div>
            </div>
        </form>

        <!-- Comparison Table -->
        <div class="erp-card" style="padding: 0; overflow: hidden;">
            <div class="erp-card-header" style="padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                <div class="erp-card-title">
                    <span>📋</span>
                    <span>جدول معاينة وتدقيق السطور ومقارنتها بقاعدة البيانات</span>
                </div>
            </div>

            <div class="erp-table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="erp-table">
                    <thead>
                        <tr style="position: sticky; top: 0; z-index: 2;">
                            <th style="width: 45px; text-align: center;">#</th>
                            <th style="width: 100px; text-align: center;">حالة السطر</th>
                            <th style="width: 130px;">كود الصنف (SKU)</th>
                            <th>اسم المنتج</th>
                            <th style="width: 120px;">التصنيف</th>
                            <th style="width: 140px;">الباركود</th>
                            <th style="width: 90px; text-align: left;">التكلفة</th>
                            <th style="width: 130px; text-align: left;">سعر الجملة</th>
                            <th style="width: 90px; text-align: left;">التجزئة</th>
                            <th style="width: 70px; text-align: center;">الوحدة</th>
                            <th>المطابقة وملاحظات التدقيق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviewData['rows'] as $row)
                            <tr style="{{ $row['action'] === 'invalid' ? 'background-color: rgba(239, 68, 68, 0.05);' : ($row['action'] === 'update' ? 'background-color: rgba(245, 158, 11, 0.05);' : '') }}">
                                <td style="text-align: center;" class="font-numeric">{{ $row['line'] }}</td>

                                <!-- Status Badge -->
                                <td style="text-align: center;">
                                    @if($row['action'] === 'new')
                                        <span class="erp-badge erp-badge-success" style="font-size: 11px;">✨ جديد</span>
                                    @elseif($row['action'] === 'update')
                                        <span class="erp-badge erp-badge-warning" style="font-size: 11px;">🔄 تحديث</span>
                                    @else
                                        <span class="erp-badge erp-badge-danger" style="font-size: 11px;">⚠️ خطأ</span>
                                    @endif
                                </td>

                                <!-- SKU -->
                                <td>
                                    <span class="font-numeric" style="font-weight: 700; color: var(--primary);">
                                        {{ $row['sku'] ?: '—' }}
                                    </span>
                                </td>

                                <!-- Name -->
                                <td>
                                    <strong style="color: var(--text-main);">{{ $row['name'] ?: '—' }}</strong>
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="erp-badge erp-badge-neutral">{{ $row['category_name'] }}</span>
                                </td>

                                <!-- Barcode -->
                                <td>
                                    @if($row['barcode'])
                                        <span class="font-numeric" style="font-family: monospace; font-size: 11px; background: var(--bg-surface-alt); padding: 2px 5px; border-radius: 4px; border: 1px solid var(--border-color);">
                                            {{ $row['barcode'] }}
                                        </span>
                                    @else
                                        <span style="color: var(--text-light); font-size: 11px;">—</span>
                                    @endif
                                </td>

                                <!-- Cost Price -->
                                <td style="text-align: left;" class="font-numeric">
                                    {{ number_format((float)$row['cost_price'], 2) }}
                                </td>

                                <!-- Wholesale Price with Comparison -->
                                <td style="text-align: left;" class="font-numeric">
                                    <strong style="color: var(--success-text);">{{ number_format((float)$row['wholesale_price'], 2) }}</strong>
                                    @if($row['existing_product'])
                                        <small style="display: block; font-size: 10px; color: var(--text-muted);">
                                            السابق: {{ number_format($row['existing_product']['old_wholesale_price'], 2) }}
                                        </small>
                                    @endif
                                </td>

                                <!-- Retail Price -->
                                <td style="text-align: left;" class="font-numeric">
                                    {{ number_format((float)$row['retail_price'], 2) }}
                                </td>

                                <!-- Unit -->
                                <td style="text-align: center;">
                                    <span style="font-size: 11px; color: var(--text-muted);">{{ $row['unit'] }}</span>
                                </td>

                                <!-- Audit Notes -->
                                <td>
                                    @if($row['action'] === 'new')
                                        <span style="color: var(--success-text); font-size: 12px;">صنف جديد سيتم إدراجه في قاعدة البيانات.</span>
                                    @elseif($row['action'] === 'update')
                                        <span style="color: var(--warning); font-size: 12px;">
                                            صنف مسجل مسبقاً (<strong>{{ $row['existing_product']['name'] }}</strong>) — سيتم تحديث الأسعار والمواصفات.
                                        </span>
                                    @else
                                        <div style="color: var(--danger); font-size: 11px;">
                                            @foreach($row['errors'] as $err)
                                                <div>• {{ $err }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
