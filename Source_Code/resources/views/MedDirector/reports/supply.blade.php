@extends('layouts.app')

@section('content')
<!-- Include Custom Supply Reports Layout Styling -->
<link rel="stylesheet" href="{{ asset('css/meddirector-supply-reports.css') }}">

<div class="meddirector-system-wrapper">
    
    <!-- Component Header Section Box Container -->
    <div class="meddirector-header-card">
        <h2 class="meddirector-header-title">Usage By Ward</h2>
        <p class="meddirector-header-subtitle">Real-time dynamic monitoring of medical inventories and ward clinical consumption tracing summaries.</p>
    </div>

    <!-- Core 3-Column Split Masonry Matrix Layout Grid -->
    <div class="md-supply-masonry-grid">
        @forelse($supplyUsageData ?? [] as $row)
            <div class="md-supply-block-card">
                
                <!-- Card Header Area with custom color presets matches wireframe icons shapes -->
                <div class="md-supply-card-top">
                    <span class="md-supply-icon-box bg-theme-{{ $row['theme'] }}">
                        {!! $row['icon_svg'] !!}
                    </span>
                    <h3 class="md-supply-ward-title">{{ $row['ward_name'] }}</h3>
                </div>

                <!-- Structured Lines Content List Matrix -->
                <div class="md-supply-metrics-list">
                    <div class="md-supply-line-item">
                        <span class="md-supply-item-label">Gloves used</span>
                        <span class="md-supply-item-value">{{ $row['gloves'] }} pairs</span>
                    </div>
                    <div class="md-supply-line-item">
                        <span class="md-supply-item-label">{{ $row['label_two'] }}</span>
                        <span class="md-supply-item-value">{{ $row['count_two'] }} units</span>
                    </div>
                    <div class="md-supply-line-item">
                        <span class="md-supply-item-label">IV bags</span>
                        <span class="md-supply-item-value">{{ $row['iv_bags'] }} bags</span>
                    </div>
                    <div class="md-supply-line-item border-none">
                        <span class="md-supply-item-label">Medications</span>
                        <span class="md-supply-item-value">{{ $row['medications'] }} doses</span>
                    </div>
                </div>

            </div>
        @empty
            <div class="md-supply-empty-fallback-box">
                No active physical wards configured in database schemas records layer.
            </div>
        @endforelse
    </div>

    <!-- LOW STOCK & OUT OF STOCK ALERTS CARD CONTAINER BLOCK -->
    <div class="md-stock-alerts-card">
        <div class="md-stock-card-title-header">
            <h3 class="md-stock-main-heading">Low stock & out of stock alerts</h3>
        </div>

        @if(isset($lowStockAlertsCollection) && count($lowStockAlertsCollection) > 0)
            <!-- Alert Status Banner Display Info Header -->
            <div class="md-stock-notice-banner-row">
                <div class="md-notice-banner-left">
                    <span class="md-notice-banner-exclamation-icon">&#9888;</span>
                    <span class="md-notice-banner-bold-text">Requires immediate reorder</span>
                </div>
                <span class="md-notice-count-badge">{{ count($lowStockAlertsCollection) }} items</span>
            </div>

            <!-- Database List Grid Group -->
            <div class="md-stock-items-vertical-list">
                @foreach($lowStockAlertsCollection as $item)
                    <div class="md-stock-item-row-line">
                        
                        <!-- Left Component: Status Context Dot and Meta String Fields -->
                        <div class="md-stock-item-meta-left">
                            <span class="md-stock-context-dot dot-state-{{ $item['status_class'] }}">&bull;</span>
                            <div class="md-stock-text-details-wrapper">
                                <h4 class="md-stock-item-name-main">{{ $item['item_name'] }}</h4>
                                <p class="md-stock-item-meta-subline">{{ $item['category_label'] }} &middot; Last restock: {{ $item['last_restock_date'] }}</p>
                            </div>
                        </div>

                        <!-- Center Component: Remaining Quantities and Target Allocated Wards -->
                        <div class="md-stock-quantifier-center-block">
                            <div class="md-stock-remaining-qtytext text-state-{{ $item['status_class'] }}">
                                {{ $item['qty_left'] }} {{ $item['unit_label'] }} left
                            </div>
                            <div class="md-stock-allocated-wards-text">{{ $item['ward_allocations'] }}</div>
                        </div>

                        <!-- Right Component: Badge State Flag Pill Context Wrapper -->
                        <div class="md-stock-pill-right-block">
                            <span class="md-stock-status-pill badge-state-{{ $item['status_class'] }}">
                                {{ $item['status_label'] }}
                            </span>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty Database State Fallback Message Display Canvas -->
            <div class="md-stock-empty-fallback-state">
                <p class="md-empty-state-message">No low-stock items detected inside your database inventory metrics.</p>
            </div>
        @endif
    </div>

</div>
@endsection
