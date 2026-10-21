@extends('layouts.app')

@section('control_panel')
    <x-control-panel title="سند قيد يومية: {{ $entry->entry_number }}" :breadcrumbs="['المالية والحسابات' => route('treasuries.index'), 'قيود اليومية' => route('accounting.journal-entries'), $entry->entry_number => route('accounting.journal-entries.show', $entry)]">
        <x-slot:actions>
            <a href="{{ route('accounting.journal-entries.print', $entry) }}" target="_blank" class="erp-btn erp-btn-primary">
                <x-icon name="printer" size="16" />
                <span>طباعة سند القيد</span>
            </a>
            @if($entry->source_document_url)
                <a href="{{ $entry->source_document_url }}" class="erp-btn erp-btn-secondary" title="الانتقال للمستند المصدر المنشئ للقيد">
                    <x-icon name="file-text" size="16" />
                    <span>عرض {{ $entry->reference_type_label }} الأصلية</span>
                </a>
            @endif
            <a href="{{ route('accounting.journal-entries') }}" class="erp-btn erp-btn-secondary">
                <x-icon name="arrow-right" size="16" />
                <span>العودة لدفتر القيود</span>
            </a>
        </x-slot:actions>

        <x-slot:extra>
            <span class="erp-badge {{ $entry->status_badge_class }} font-numeric px-3 py-1 text-xs">
                <span>الحالة:</span>
                <strong class="mr-1">{{ $entry->status_label }}</strong>
            </span>
        </x-slot:extra>
    </x-control-panel>
@endsection

@section('content')
    <div data-page="journal-entry-show" class="max-w-5xl mx-auto space-y-6">
        
        <!-- Voucher Document Sheet Card -->
        <div class="erp-card p-6 md:p-8 bg-surface shadow-sm border border-border">
            
            <!-- Document Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b-2 border-border/70">
                <div>
                    <h2 class="text-xl font-black text-foreground">{{ $company->company_name ?? 'شركة كيان للحلول والأنظمة' }}</h2>
                    <p class="text-xs text-muted mt-1">الإدارة المالية والحسابات العامة · دفتر اليومية المركزية</p>
                    @if(!empty($company?->company_phone))
                        <p class="text-[11px] text-muted font-numeric">هاتف: {{ $company->company_phone }}</p>
                    @endif
                </div>

                <div class="text-left flex flex-col items-start sm:items-end">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary/10 border border-primary/20 rounded-lg text-primary">
                        <x-icon name="file-text" size="18" />
                        <span class="text-sm font-black">سند قيد يومية عامة</span>
                    </div>
                    <div class="mt-2 font-mono text-base font-black text-foreground tracking-wider bg-surface-alt px-3 py-1 rounded border border-border">
                        {{ $entry->entry_number }}
                    </div>
                </div>
            </div>

            <!-- Metadata Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-b border-border text-xs">
                <div>
                    <span class="text-muted block mb-1">تاريخ استحقاق القيد:</span>
                    <strong class="font-numeric text-sm text-foreground">
                        {{ $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '—' }}
                    </strong>
                </div>

                <div>
                    <span class="text-muted block mb-1">المستند المصدري:</span>
                    <span class="erp-badge erp-badge-neutral text-xs font-semibold">
                        {{ $entry->reference_type_label }}
                    </span>
                    @if($entry->reference_id)
                        <span class="font-mono text-xs text-muted mr-1">#{{ $entry->reference_id }}</span>
                    @endif
                </div>

                <div>
                    <span class="text-muted block mb-1">تاريخ ووقت الترحيل:</span>
                    <span class="font-numeric text-xs text-foreground font-medium">
                        {{ $entry->posted_at ? $entry->posted_at->format('Y-m-d H:i') : 'مسودة' }}
                    </span>
                </div>

                <div>
                    <span class="text-muted block mb-1">المحاسب المنشئ:</span>
                    <strong class="text-xs text-foreground font-bold">
                        {{ $entry->creator?->name ?? 'النظام الآلي' }}
                    </strong>
                </div>
            </div>

            <!-- General Description / Memo -->
            <div class="my-4 p-3.5 bg-surface-alt/70 rounded-lg border border-border">
                <span class="text-xs text-muted font-bold block mb-1">البيان والشرح العام للقيد:</span>
                <p class="text-sm font-semibold text-foreground leading-relaxed">
                    {{ $entry->description ?: 'لا يوجد بيان إضافي مسجل.' }}
                </p>
            </div>

            <!-- Journal Lines Table -->
            <div class="overflow-x-auto rounded-lg border border-border my-6">
                <table class="w-full text-right text-xs">
                    <thead class="bg-surface-alt border-b border-border text-muted font-bold uppercase tracking-wider">
                        <tr>
                            <th class="p-3 w-10 text-center">#</th>
                            <th class="p-3 w-28">كود الحساب</th>
                            <th class="p-3">اسم الحساب</th>
                            <th class="p-3 text-left font-numeric w-32">مدين (Dr)</th>
                            <th class="p-3 text-left font-numeric w-32">دائن (Cr)</th>
                            <th class="p-3">البيان / ملاحظات السطر</th>
                            <th class="p-3 w-40">الطرف المسجل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40">
                        @foreach($entry->lines as $idx => $line)
                            @php
                                $debit = (float) $line->debit;
                                $credit = (float) $line->credit;
                                $partyName = $line->customer ? ('عميل: ' . $line->customer->name) : ($line->supplier ? ('مورد: ' . $line->supplier->name) : '—');
                            @endphp
                            <tr class="hover:bg-surface-hover/60 transition-colors">
                                <td class="p-3 text-center text-muted font-numeric">{{ $idx + 1 }}</td>
                                <td class="p-3">
                                    <span class="font-mono font-bold text-foreground bg-surface px-2 py-0.5 rounded border border-border">
                                        {{ $line->account?->code ?? '—' }}
                                    </span>
                                </td>
                                <td class="p-3 font-semibold text-foreground">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ $line->account?->name ?? 'حساب غير محدد' }}</span>
                                        @if($line->account?->nature)
                                            <span class="text-[10px] text-muted font-normal">({{ $line->account->nature === 'debit' ? 'مدين' : 'دائن' }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 text-left font-numeric {{ $debit > 0 ? 'font-bold text-emerald-600 dark:text-emerald-400 text-sm' : 'text-muted' }}">
                                    {{ $debit > 0 ? number_format($debit, 2) : '—' }}
                                </td>
                                <td class="p-3 text-left font-numeric {{ $credit > 0 ? 'font-bold text-cyan-600 dark:text-cyan-400 text-sm' : 'text-muted' }}">
                                    {{ $credit > 0 ? number_format($credit, 2) : '—' }}
                                </td>
                                <td class="p-3 text-muted">
                                    {{ $line->description ?: '—' }}
                                </td>
                                <td class="p-3 text-xs text-foreground font-medium">
                                    {{ $partyName }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface-alt font-black border-t-2 border-border text-sm">
                        <tr>
                            <td colspan="3" class="p-3 text-left">إجمالي القيد:</td>
                            <td class="p-3 text-left font-numeric text-emerald-600 dark:text-emerald-400">
                                {{ number_format((float) $entry->total_debit, 2) }} <span class="text-xs font-normal text-muted">ج.م</span>
                            </td>
                            <td class="p-3 text-left font-numeric text-cyan-600 dark:text-cyan-400">
                                {{ number_format((float) $entry->total_credit, 2) }} <span class="text-xs font-normal text-muted">ج.م</span>
                            </td>
                            <td colspan="2" class="p-3 text-center">
                                @if($entry->isBalanced())
                                    <span class="erp-badge erp-badge-success text-xs font-bold px-3 py-1">
                                        متزن محاسبياً بنسبة 100%
                                    </span>
                                @else
                                    <span class="erp-badge erp-badge-danger text-xs font-bold px-3 py-1">
                                        غير متزن (فارق: {{ number_format(abs((float)$entry->total_debit - (float)$entry->total_credit), 2) }} ج.م)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Signatures Section -->
            <div class="grid grid-cols-3 gap-6 pt-8 mt-6 border-t-2 border-border text-center text-xs">
                <div class="space-y-12">
                    <div class="font-bold text-muted">إعداد (المحاسب المسؤول)</div>
                    <div class="border-t border-border pt-1 font-semibold text-foreground">
                        {{ $entry->creator?->name ?? 'أمين الحسابات' }}
                    </div>
                </div>

                <div class="space-y-12">
                    <div class="font-bold text-muted">المراجعة والتدقيق الداخلي</div>
                    <div class="border-t border-border pt-1 text-muted">التوقيع والتاريخ</div>
                </div>

                <div class="space-y-12">
                    <div class="font-bold text-muted">اعتماد الإدارة المالية</div>
                    <div class="border-t border-border pt-1 text-muted">الختم / التوقيع</div>
                </div>
            </div>

        </div>
    </div>
@endsection
