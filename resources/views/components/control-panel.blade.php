@props([
    'title' => 'لوحة التحكم',
    'breadcrumbs' => [],
    'showSearch' => false,
    'searchPlaceholder' => 'بحث وتصفية...',
])

<section class="erp-control-panel">
    <div class="erp-cp-top">
        <!-- Breadcrumb & Title -->
        <nav class="erp-breadcrumb" aria-label="شريط المسار">
            <span class="erp-breadcrumb-item">
                <a href="{{ url('/') }}">Kayan ERP</a>
            </span>
            @foreach($breadcrumbs as $name => $link)
                <span class="erp-breadcrumb-sep">/</span>
                <span class="erp-breadcrumb-item">
                    @if($link)
                        <a href="{{ $link }}">{{ $name }}</a>
                    @else
                        <span>{{ $name }}</span>
                    @endif
                </span>
            @endforeach
            <span class="erp-breadcrumb-sep">/</span>
            <span class="erp-breadcrumb-item">
                <span>{{ $title }}</span>
            </span>
        </nav>

        <!-- View Switchers (Odoo Style) -->
        <div class="erp-view-switchers">
            <button type="button" class="erp-view-btn active" title="عرض القائمة (List View)">☰</button>
            <button type="button" class="erp-view-btn" title="عرض البطاقات (Kanban View)">▦</button>
            <button type="button" class="erp-view-btn" title="عرض الرسم البياني (Graph View)">📈</button>
        </div>
    </div>

    <div class="erp-cp-bottom">
        <!-- Actions slot -->
        <div class="erp-actions-bar">
            {{ $actions ?? '' }}
        </div>

        <!-- Search / Filter slot -->
        @if($showSearch)
            <div class="erp-cp-search">
                <div class="erp-search-box">
                    <input type="search" class="erp-search-input" placeholder="{{ $searchPlaceholder }}">
                    <span class="erp-search-icon">🔍</span>
                </div>
                {{ $filters ?? '' }}
            </div>
        @else
            <div>
                {{ $extra ?? '' }}
            </div>
        @endif
    </div>
</section>
