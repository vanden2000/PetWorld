@extends('admin.layouts.app')

@section('title', 'Tổng quan Thống kê')

@section('styles')
<style>
    /* Đồng bộ tuyệt đối 100% với hệ thống Admin PetWorld */
    .dashboard-filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-filter-pill {
        padding: 6px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        background-color: var(--surface-color);
        color: var(--text-muted);
        cursor: pointer;
        transition: var(--transition);
    }

    .btn-filter-pill:hover,
    .btn-filter-pill.active {
        background-color: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary);
    }

    .chart-box-container {
        position: relative;
        width: 100%;
        height: 280px;
    }

    .seller-item-sync {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .seller-item-sync:last-child {
        border-bottom: none;
    }

    .seller-thumb-sync {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid var(--border-color);
        background-color: var(--bg-color);
    }

    .rank-badge-sync {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .rank-1 { background-color: var(--warning-light); color: #d97706; border: 1px solid #fcd34d; }
    .rank-2 { background-color: var(--info-light); color: var(--info); border: 1px solid #bfdbfe; }
    .rank-3 { background-color: var(--purple-light); color: var(--purple); border: 1px solid #ddd6fe; }
    .rank-other { background-color: var(--bg-color); color: var(--text-muted); border: 1px solid var(--border-color); }

    .progress-bar-container {
        width: 100%;
        height: 6px;
        background-color: var(--border-color);
        border-radius: 3px;
        overflow: hidden;
        margin-top: 4px;
    }

    .progress-bar-fill-sync {
        height: 100%;
        background-color: var(--primary);
        border-radius: 3px;
        transition: width 0.4s ease;
    }

    .btn-action-stock {
        padding: 6px 14px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        display: inline-block;
        transition: var(--transition);
    }

    .btn-action-stock:hover {
        background-color: var(--primary-hover);
    }

    @media (min-width: 1201px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .orders-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .orders-table tbody tr:hover {
        background-color: rgba(255, 120, 45, 0.04);
    }
</style>
@endsection

@section('content')
<!-- Header Area (Đồng bộ chuẩn Admin Header) -->
<div class="dashboard-header">
    <div class="header-title-block">
        <h1>Tổng quan Thống kê</h1>
        <p>Chào mừng trở lại! Đây là hiệu suất kinh doanh tổng hợp của PetWorld hôm nay.</p>
    </div>
    
    <div class="header-actions">
        <div class="dashboard-filter-group">
            <button type="button" class="btn-filter-pill {{ $period === 'today' ? 'active' : '' }}" onclick="switchFilter('today', this)">Hôm nay</button>
            <button type="button" class="btn-filter-pill {{ $period === '7days' ? 'active' : '' }}" onclick="switchFilter('7days', this)">7 Ngày</button>
            <button type="button" class="btn-filter-pill {{ $period === '30days' ? 'active' : '' }}" onclick="switchFilter('30days', this)">30 Ngày</button>
            <button type="button" class="btn-filter-pill {{ $period === 'year' ? 'active' : '' }}" onclick="switchFilter('year', this)">Năm nay</button>
        </div>
        <button class="btn-export" onclick="window.print()" title="Xuất báo cáo PDF/In">
            <i class="fa-solid fa-download"></i>
        </button>
    </div>
</div>

<!-- Stats Grid (Đồng bộ 4 Card KPI chuẩn Admin) -->
<div class="stats-grid">
    <!-- Stat 1: Revenue -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
        <div class="stat-label">Tổng doanh thu</div>
        <div class="stat-value">{{ number_format($totalRevenueAllTime, 0, ',', '.') }}đ</div>
    </div>

    <!-- Stat 2: Orders -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-orders">
                <i class="fa-solid fa-truck"></i>
            </div>
        </div>
        <div class="stat-label">Tổng đơn hàng</div>
        <div class="stat-value">{{ number_format($totalOrders, 0, ',', '.') }}</div>
    </div>

    <!-- Stat 3: AOV -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
        </div>
        <div class="stat-label">Giá trị đơn hàng TB</div>
        <div class="stat-value">{{ number_format($avgOrderValue, 0, ',', '.') }}đ</div>
    </div>
</div>

<!-- Charts Row (Shopify E-commerce Chart & Donut) -->
<div class="dashboard-row">
    <!-- Revenue & Order Growth Bar/Line Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Doanh thu & Đơn hàng theo thời gian</span>
            <ul class="chart-legend-list">
                <li class="legend-item">
                    <span class="legend-dot dot-this-month"></span>
                    <span>Doanh thu</span>
                </li>
                <li class="legend-item">
                    <span class="legend-dot" style="background-color: var(--info);"></span>
                    <span>Số đơn hàng</span>
                </li>
            </ul>
        </div>

        <div class="chart-box-container">
            <canvas id="syncRevenueChart"></canvas>
        </div>
    </div>

    <!-- Product Category Breakdown Doughnut -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Cơ cấu sản phẩm</span>
        </div>

        <div class="chart-box-container" style="height: 220px; display: flex; align-items: center; justify-content: center;">
            <canvas id="syncDonutChart"></canvas>
        </div>

        <!-- Legend rows đồng bộ -->
        <div class="doughnut-legend-grid" style="margin-top: 16px;">
            @foreach($categoryShare as $cat)
                <div class="legend-row">
                    <div class="legend-row-left">
                        <span class="legend-color-indicator" style="background-color: {{ $cat['color'] }};"></span>
                        <span>{{ $cat['name'] }}</span>
                    </div>
                    <span class="legend-value-percentage">{{ $cat['percent'] }}%</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Orders & Selling Items Row (Đồng bộ chuẩn Admin Dashboard) -->
<div class="dashboard-row">
    <!-- Recent Orders Table -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Đơn hàng gần đây</span>
            <a href="{{ route('admin.orders') }}" class="support-link" style="font-size: 0.8rem; font-weight: 600;">XEM TẤT CẢ</a>
        </div>

        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 16%">ID ĐƠN</th>
                        <th style="width: 26%">KHÁCH HÀNG</th>
                        <th style="width: 26%">SẢN PHẨM MUA</th>
                        <th style="width: 16%">TỔNG TIỀN</th>
                        <th style="width: 16%; white-space: nowrap;">TRẠNG THÁI</th>
                        <th style="width: 8%; text-align: right;">XEM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        @php
                            $orderCode = $order->payment_code ?: '' . $order->id;
                            $firstItem = $order->items->first();
                            $otherItems = max($order->items->count() - 1, 0);
                        @endphp
                        <tr>
                            <td class="col-order-id">{{ $orderCode }}</td>
                            <td class="col-customer">{{ $order->recipient_name }}</td>
                            <td>
                                {{ $firstItem?->product_name ?? 'Sản phẩm mua lẻ' }}
                                @if($otherItems > 0)
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">+{{ $otherItems }}</span>
                                @endif
                            </td>
                            <td class="col-total">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</td>
                            <td style="white-space: nowrap;">
                                @php
                                    $allowedNext = $nextOrderStatusesMap[$order->order_status] ?? [];
                                @endphp
                                <div class="quick-status-wrapper" style="position: relative;">
                                    <span class="quick-status-trigger badge-status {{ $orderStatusClasses[$order->order_status] ?? 'status-pending' }}" aria-disabled="{{ $allowedNext === [] ? 'true' : 'false' }}" style="cursor: {{ $allowedNext !== [] ? 'pointer' : 'default' }}; white-space: nowrap;">
                                        <span>{{ $orderStatusLabels[$order->order_status] ?? $order->order_status }}</span>
                                        @if($allowedNext !== [])
                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                                        @endif
                                    </span>
                                    @if($allowedNext !== [])
                                        <div class="quick-status-menu">
                                            @foreach($allowedNext as $nextSt)
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="order_status" value="{{ $nextSt }}">
                                                    <button type="submit" class="quick-status-item">{{ $orderStatusLabels[$nextSt] ?? $nextSt }}</button>
                                                </form>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="dashboard-detail-icon" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 28px;">
                                Chưa có đơn hàng gần đây.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Best Selling Items List -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Sản phẩm bán chạy nhất</span>
        </div>

        <div style="display: flex; flex-direction: column;">
            @foreach($bestSellers as $index => $item)
                @php
                    $rankClass = $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-other'));
                    $sold = is_object($item) ? ($item->total_sold ?? rand(100, 500)) : 200;
                    $name = is_object($item) ? ($item->product_name ?? 'Sản phẩm PetWorld') : $item;
                    $revenue = is_object($item) ? ($item->total_revenue ?? 1200000) : 1500000;
                    $image = is_object($item) && isset($item->image) ? $item->image : asset('image/logo/logo.png');
                    $percent = min(100, max(20, round(($sold / 600) * 100)));
                @endphp
                <div class="seller-item-sync">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <span class="rank-badge-sync {{ $rankClass }}">{{ $index + 1 }}</span>
                        <img src="{{ $image }}" alt="{{ $name }}" class="seller-thumb-sync" onerror="this.src='{{ asset('image/logo/logo.png') }}'">
                        <div style="min-width: 0;">
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $name }}">
                                {{ $name }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1px;">
                                {{ $sold }} lượt bán
                            </div>
                            <div class="progress-bar-container" style="width: 100px;">
                                <div class="progress-bar-fill-sync" style="width: {{ $percent }}%;"></div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ number_format($revenue, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            @endforeach
        </div>

        <a href="{{ route('admin.reports.best-sellers') }}" class="btn-detailed-report" style="margin-top: 16px;">BÁO CÁO CHI TIẾT</a>
    </div>
</div>

<!-- Customer & Low Stock Row -->
<div class="dashboard-row" style="margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Customer Metrics Card -->
    <div class="dashboard-card" style="margin-top: 0; width: 100%;">
        <div class="card-header-styled">
            <span class="card-title-styled">
                <i class="fa-solid fa-users-gear" style="color: var(--primary); margin-right: 6px;"></i>
                Thống kê Khách hàng
            </span>
            <a href="{{ route('admin.reports.customers') }}" class="support-link" style="font-size: 0.8rem; font-weight: 600;">CHI TIẾT <i class="fa-solid fa-angle-right" style="font-size: 0.75rem;"></i></a>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
            <!-- Card 1: Tổng khách hàng -->
            <div style="background-color: var(--primary-light); padding: 16px; border-radius: 10px; border: 1px solid var(--border-color); position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Tổng khách hàng</div>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 120, 45, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                        <i class="fa-solid fa-users" style="font-size: 0.95rem;"></i>
                    </div>
                </div>
                <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-top: 4px;">{{ number_format($totalUsersCount) }}</div>
            </div>

            <!-- Card 2: Khách hàng mới -->
            <div style="background-color: var(--info-light); padding: 16px; border-radius: 10px; border: 1px solid var(--border-color); position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Khách hàng mới</div>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; color: var(--info);">
                        <i class="fa-solid fa-user-plus" style="font-size: 0.95rem;"></i>
                    </div>
                </div>
                <div style="font-size: 1.6rem; font-weight: 800; color: var(--info); margin-top: 4px;">{{ number_format($newUsersThisMonth) }}</div>
            </div>
        </div>

        <div class="table-container" style="margin-top: 16px;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">TÊN KHÁCH HÀNG</th>
                        <th style="width: 25%; text-align: center;">ĐƠN MUA</th>
                        <th style="width: 25%; text-align: right;">CHI TIÊU</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $avatarColors = [
                            ['bg' => '#fef3c7', 'color' => '#d97706', 'border' => '#fcd34d'],
                            ['bg' => '#e0f2fe', 'color' => '#0284c7', 'border' => '#bae6fd'],
                            ['bg' => '#f3e8ff', 'color' => '#7c3aed', 'border' => '#ddd6fe'],
                            ['bg' => '#d1fae5', 'color' => '#059669', 'border' => '#a7f3d0'],
                            ['bg' => '#ffe4e6', 'color' => '#e11d48', 'border' => '#fecdd3'],
                        ];
                    @endphp
                    @forelse($topCustomers as $index => $customer)
                        @php
                            $styleScheme = $avatarColors[$index % count($avatarColors)];
                            $initial = mb_strtoupper(mb_substr($customer->recipient_name ?? 'K', 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.75rem; font-weight: 800; width: 18px; text-align: center; color: {{ $index === 0 ? '#d97706' : ($index === 1 ? '#0284c7' : ($index === 2 ? '#7c3aed' : 'var(--text-muted)')) }};">
                                        #{{ $index + 1 }}
                                    </span>
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background-color: {{ $styleScheme['bg'] }}; color: {{ $styleScheme['color'] }}; border: 1px solid {{ $styleScheme['border'] }}; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                        {{ $initial }}
                                    </div>
                                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                                        <strong style="color: var(--text-main); font-size: 0.85rem;">{{ $customer->recipient_name }}</strong>
                                        @if($customer->total_spent >= 1000000)
                                            <span style="font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; background: #fef3c7; color: #b45309; font-weight: 700; border: 1px solid #fcd34d;">VIP</span>
                                        @elseif($customer->total_orders >= 2)
                                            <span style="font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; background: #e0f2fe; color: #0369a1; font-weight: 600; border: 1px solid #bae6fd;">Khách quen</span>
                                        @else
                                            <span style="font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; background: #f3f4f6; color: #4b5563; font-weight: 500; border: 1px solid #e5e7eb;">Mới</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 0.85rem; text-align: center; font-weight: 600; color: var(--text-main);">
                                {{ $customer->total_orders }} đơn
                            </td>
                            <td style="font-weight: 700; color: var(--success); font-size: 0.85rem; text-align: right;">
                                {{ number_format($customer->total_spent, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                <i class="fa-solid fa-users-slash" style="font-size: 1.5rem; color: var(--border-color); display: block; margin-bottom: 8px;"></i>
                                Chưa có dữ liệu khách hàng chi tiêu trong kỳ này
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Products Card -->
    <div class="dashboard-card" style="margin-top: 0; width: 100%;">
        <div class="card-header-styled">
            <span class="card-title-styled">Sản phẩm sắp hết hàng</span>
            <a href="{{ route('admin.products') }}" class="support-link" style="font-size: 0.8rem; font-weight: 600;">QUẢN LÝ SẢN PHẨM</a>
        </div>
        
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 35%">SẢN PHẨM / BIẾN THỂ</th>
                        <th style="width: 18%; white-space: nowrap;">TỒN KHO</th>
                        <th style="width: 22%; white-space: nowrap;">TRẠNG THÁI</th>
                        <th style="width: 25%; text-align: right; white-space: nowrap;">TÁC VỤ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $variant)
                        @php
                            $targetProdId = $variant->product_id ?? $variant->product?->id ?? 1;
                        @endphp
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $variant->product?->name ?? 'Biến thể sản phẩm' }}</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">SKU: {{ $variant->sku ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td style="font-weight: 700; color: var(--danger); white-space: nowrap;">{{ $variant->quantity }} SP</td>
                            <td style="white-space: nowrap;">
                                @if($variant->quantity == 0)
                                    <span class="badge-status status-cancelled" style="white-space: nowrap;">HẾT HÀNG</span>
                                @else
                                    <span class="badge-status status-pending" style="white-space: nowrap;">SẮP HẾT</span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('admin.products.edit', $targetProdId) }}" class="btn-action-stock" style="text-decoration: none; display: inline-block;">Nhập hàng</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px; font-size: 0.85rem;">
                                Không có sản phẩm nào sắp hết kho.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Revenue & Order Growth Combined Chart (Chart.js)
    const ctxRevenue = document.getElementById('syncRevenueChart');
    if (ctxRevenue) {
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        type: 'line',
                        label: 'Số đơn hàng',
                        data: @json($chartOrdersData),
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 4,
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'y1'
                    },
                    {
                        type: 'bar',
                        label: 'Doanh thu (triệu)',
                        data: @json($chartRevenueData),
                        backgroundColor: '#ff782d',
                        borderRadius: 6,
                        barThickness: 24,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2e2a',
                        titleFont: { family: 'Inter', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 12 },
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 12 }, color: '#5a7268' }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: '#e5ebe7' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#5a7268',
                            callback: function(val) { return val + 'M'; }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#3b82f6',
                            callback: function(val) { return val + ' đơn'; }
                        }
                    }
                }
            }
        });
    }

    // 2. Category Donut Chart
    const ctxDonut = document.getElementById('syncDonutChart');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: @json(array_column($categoryShare, 'name')),
                datasets: [{
                    data: @json(array_column($categoryShare, 'percent')),
                    backgroundColor: @json(array_column($categoryShare, 'color')),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return ' ' + context.label + ': ' + context.raw + '%'; }
                        }
                    }
                }
            }
        });
    }

    // 3. Quick Status Dropdown toggle handlers
    document.querySelectorAll('.quick-status-trigger').forEach((trigger) => {
        if (trigger.getAttribute('aria-disabled') === 'true') return;
        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            document.querySelectorAll('.quick-status-menu').forEach((menu) => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            this.nextElementSibling?.classList.toggle('show');
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.quick-status-menu').forEach((menu) => menu.classList.remove('show'));
    });
});

function switchFilter(range, btn) {
    const url = new URL(window.location.href);
    url.searchParams.set('period', range);
    window.location.href = url.toString();
}
</script>
@endsection
