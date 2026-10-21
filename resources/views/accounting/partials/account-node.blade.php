@props(['account', 'level' => 1])

@php
    $hasChildren = $account->children && $account->children->isNotEmpty();
    $indentPadding = ($account->level - 1) * 24;
    $balance = (float) $account->current_balance;
    $isPositive = $balance > 0;
    $isNegative = $balance < 0;
@endphp

<div class="account-row-wrapper border-b border-border/40 hover:bg-surface-hover/50 transition-colors" data-account-id="{{ $account->id }}" data-account-code="{{ $account->code }}" data-account-name="{{ $account->name }}" data-account-type="{{ $account->type }}">
    <div class="flex items-center justify-between py-2.5 px-4 text-sm" style="padding-right: {{ max(16, $indentPadding + 16) }}px;">
        <!-- Left details: Toggle, Code, Name, System Tag -->
        <div class="flex items-center gap-3 min-w-0">
            @if($hasChildren)
                <button type="button" class="node-toggle-btn w-6 h-6 flex items-center justify-center rounded hover:bg-surface-active text-muted transition-transform" onclick="toggleAccountNode(this, 'children-{{ $account->id }}')">
                    <x-icon name="chevron-down" size="14" class="transform transition-transform duration-200" />
                </button>
            @else
                <span class="w-6 h-6 flex items-center justify-center text-muted/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-border"></span>
                </span>
            @endif

            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-surface border border-border text-foreground">
                {{ $account->code }}
            </span>

            <div class="flex items-center gap-2 truncate">
                <span class="font-semibold text-foreground truncate {{ $account->level <= 2 ? 'text-base font-bold text-primary' : '' }}">
                    {{ $account->name }}
                </span>
                @if($account->name_en)
                    <span class="text-xs text-muted font-sans hidden md:inline">({{ $account->name_en }})</span>
                @endif
                @if($account->is_system)
                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-muted/15 text-muted border border-border/50 font-medium">نظامي</span>
                @endif
            </div>
        </div>

        <!-- Right details: Nature, Type, Balance, Actions -->
        <div class="flex items-center gap-4 flex-shrink-0">
            <!-- Type badge -->
            <span class="erp-badge {{ $account->type_badge_class }} text-[11px] hidden sm:inline-flex">
                {{ $account->type_label }}
            </span>

            <!-- Nature -->
            <span class="text-xs text-muted hidden lg:inline font-mono">
                {{ $account->nature === 'debit' ? 'مدين (Dr)' : 'دائن (Cr)' }}
            </span>

            <!-- Current Balance -->
            <div class="text-left font-numeric font-bold min-w-[120px] {{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : ($isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-muted') }}">
                {{ number_format(abs($balance), 2) }}
                <span class="text-[10px] text-muted font-normal mr-0.5">ج.م</span>
                @if($isNegative)
                    <span class="text-xs font-bold">(عجز)</span>
                @endif
            </div>

            <!-- Quick Add Child Button -->
            @if($account->level < 4)
                <button type="button" 
                        class="p-1 rounded hover:bg-primary/10 text-primary transition-colors" 
                        title="إضافة حساب فرعي تحت هذا الحساب"
                        onclick="openAddSubAccountModal('{{ $account->id }}', '{{ $account->code }} - {{ $account->name }}')">
                    <x-icon name="plus" size="14" />
                </button>
            @endif
        </div>
    </div>
</div>

@if($hasChildren)
    <div id="children-{{ $account->id }}" class="account-children">
        @foreach($account->children as $child)
            @include('accounting.partials.account-node', ['account' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
