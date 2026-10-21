@props(['account', 'level' => 1])

@php
    $hasChildren = $account->children && $account->children->isNotEmpty();
    $balance = (float) $account->current_balance;
    $isPositive = $balance > 0;
    $isNegative = $balance < 0;
    $isZero = abs($balance) < 0.001;
    
    // Type styling
    $typeBorderClass = match ($account->type) {
        'asset' => 'coa-type-asset',
        'liability' => 'coa-type-liability',
        'equity' => 'coa-type-equity',
        'revenue' => 'coa-type-revenue',
        'expense' => 'coa-type-expense',
        default => '',
    };

    $levelClasses = match ($account->level) {
        1 => 'level-1 py-3 bg-surface/70',
        2 => 'level-2 py-2.5 bg-surface/30 font-semibold',
        3 => 'level-3 py-2',
        default => 'level-4 py-2 font-normal',
    };
@endphp

<div class="coa-node-row {{ $levelClasses }} {{ $account->level === 1 ? $typeBorderClass : '' }} account-row-wrapper" 
     id="account-node-{{ $account->id }}"
     data-account-id="{{ $account->id }}" 
     data-account-code="{{ $account->code }}" 
     data-account-name="{{ $account->name }}" 
     data-account-type="{{ $account->type }}"
     data-level="{{ $account->level }}">
    
    <div class="coa-node-content" style="padding-right: {{ max(16, ($account->level - 1) * 20 + 16) }}px;">
        
        <!-- Left Column: Expand Button, Code, Name, System Tag, Subcount -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            @if($hasChildren)
                <button type="button" 
                        class="coa-toggle-btn node-toggle-btn" 
                        aria-expanded="true"
                        title="طي / توسيع الفرع"
                        onclick="toggleAccountNode(this, 'children-{{ $account->id }}')">
                    <x-icon name="chevron-down" size="13" class="transform transition-transform duration-150" />
                </button>
            @else
                <span class="w-6 h-6 flex items-center justify-center text-muted/30 flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-border"></span>
                </span>
            @endif

            <!-- Code Badge with Quick-Copy -->
            <button type="button" 
                    class="coa-code-pill font-numeric" 
                    title="انقر لنسخ كود الحساب ({{ $account->code }})"
                    onclick="copyAccountCode('{{ $account->code }}', event)">
                <span>{{ $account->code }}</span>
                <x-icon name="copy" size="10" class="opacity-50 hover:opacity-100" />
            </button>

            <!-- Node Icon -->
            @if($account->level === 1)
                <span class="text-primary hidden sm:inline-flex flex-shrink-0">
                    <x-icon name="folder-open" size="17" />
                </span>
            @elseif($hasChildren)
                <span class="text-amber-500/80 hidden sm:inline-flex flex-shrink-0">
                    <x-icon name="folder" size="15" />
                </span>
            @else
                <span class="text-emerald-500/70 hidden sm:inline-flex flex-shrink-0">
                    <x-icon name="tag" size="13" />
                </span>
            @endif

            <!-- Account Name Arabic + English -->
            <div class="flex items-center gap-2 truncate min-w-0">
                <span class="text-foreground truncate {{ $account->level === 1 ? 'text-base font-bold text-foreground' : ($account->level === 2 ? 'text-sm font-semibold' : 'text-xs text-foreground/90') }}">
                    {{ $account->name }}
                </span>
                
                @if($account->name_en)
                    <span class="text-[11px] text-muted font-sans hidden md:inline truncate">
                        {{ $account->name_en }}
                    </span>
                @endif

                @if($hasChildren)
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-surface border border-border text-muted font-numeric">
                        {{ $account->children->count() }}
                    </span>
                @endif

                @if($account->is_system)
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-muted/15 text-muted/80 font-medium hidden lg:inline">
                        نظامي
                    </span>
                @endif
            </div>
        </div>

        <!-- Right Column: Nature, Type, Balance, Actions -->
        <div class="flex items-center gap-3 md:gap-5 flex-shrink-0">
            <!-- Account Nature (Dr/Cr) -->
            <span class="text-[11px] font-mono px-2 py-0.5 rounded border border-border/60 bg-surface/50 text-muted hidden xl:inline-block">
                {{ $account->nature === 'debit' ? 'مدين (Dr)' : 'دائن (Cr)' }}
            </span>

            <!-- Account Type Badge -->
            <span class="erp-badge {{ $account->type_badge_class }} text-[10px] py-0.5 px-2 hidden sm:inline-flex">
                {{ $account->type_label }}
            </span>

            <!-- Balance Value -->
            <div class="text-left font-numeric font-bold text-xs sm:text-sm min-w-[110px] md:min-w-[130px] flex items-center justify-end gap-1">
                @if($isZero)
                    <span class="text-muted/60 font-normal">0.00</span>
                    <span class="text-[10px] text-muted/50 font-normal">ج.م</span>
                @elseif($isPositive)
                    <span class="text-emerald-600 dark:text-emerald-400 font-black">
                        {{ number_format($balance, 2) }}
                    </span>
                    <span class="text-[10px] text-muted font-normal">ج.م</span>
                @else
                    <span class="text-rose-600 dark:text-rose-400 font-black">
                        ({{ number_format(abs($balance), 2) }})
                    </span>
                    <span class="text-[10px] text-rose-500 font-bold">عجز</span>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-1">
                <!-- View Account Entries in Journal -->
                <a href="{{ route('accounting.journal-entries', ['search' => $account->code]) }}" 
                   class="p-1 rounded text-muted hover:text-primary hover:bg-surface-active transition-colors" 
                   title="عرض دفتر قيود وسجل حركات الحساب [{{ $account->code }}]">
                    <x-icon name="file-text" size="14" />
                </a>

                <!-- Add Sub-Account Button -->
                @if($account->level < 4)
                    <button type="button" 
                            class="p-1 rounded text-primary hover:bg-primary/10 transition-colors" 
                            title="إضافة حساب فرعي متفرع من [{{ $account->name }}]"
                            onclick="openAddSubAccountModal('{{ $account->id }}', '{{ $account->code }} - {{ $account->name }}')">
                        <x-icon name="plus" size="14" />
                    </button>
                @else
                    <span class="w-[22px]"></span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($hasChildren)
    <div id="children-{{ $account->id }}" class="coa-children account-children">
        @foreach($account->children as $child)
            @include('accounting.partials.account-node', ['account' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
