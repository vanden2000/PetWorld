@extends('admin.layouts.app')

@section('title', 'Tổng quan Thống kê')

@section('styles')

@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header">
    <div class="header-title-block">
        <h1>Tổng quan Thống kê</h1>
        <p>Chào mừng trở lại, đây là hiệu suất kinh doanh của PetWorld hôm nay.</p>
    </div>
    
    <div class="header-actions">
        <button class="filter-dropdown">
            <i class="fa-regular fa-calendar-days"></i>
            <span>30 NGÀY QUA</span>
            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
        </button>
        <button class="btn-export">
            <i class="fa-solid fa-download"></i>
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <!-- Stat 1: Revenue -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="stat-trend trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+12.5%</span>
            </div>
        </div>
        <div class="stat-label">Tổng doanh thu</div>
        <div class="stat-value">1.284.000.000đ</div>
    </div>

    <!-- Stat 2: Orders -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-orders">
                <i class="fa-solid fa-truck"></i>
            </div>
            <div class="stat-trend trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+8.2%</span>
            </div>
        </div>
        <div class="stat-label">Tổng đơn hàng</div>
        <div class="stat-value">3,452</div>
    </div>

    <!-- Stat 3: Average Order Value -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div class="stat-trend trend-down">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>-2.4%</span>
            </div>
        </div>
        <div class="stat-label">Giá trị đơn hàng TB</div>
        <div class="stat-value">372.000đ</div>
    </div>

    <!-- Stat 4: Conversion Rate -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-conversion">
                <i class="fa-solid fa-rotate"></i>
            </div>
            <div class="stat-trend trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+1.1%</span>
            </div>
        </div>
        <div class="stat-label">Tỷ lệ chuyển đổi</div>
        <div class="stat-value">4.28%</div>
    </div>
</div>

<!-- Charts Row -->
<div class="dashboard-row">
    <!-- Revenue Over Time Line/Bar Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Doanh thu theo thời gian</span>
            <ul class="chart-legend-list">
                <li class="legend-item">
                    <span class="legend-dot dot-this-month"></span>
                    <span>Tháng này</span>
                </li>
                <li class="legend-item">
                    <span class="legend-dot dot-last-month"></span>
                    <span>Tháng trước</span>
                </li>
            </ul>
        </div>
        
        <!-- Interactive Native CSS Bar Representation -->
        <div class="revenue-bar-chart">
            <div class="bar-group-container">
                <div class="bar-pair">
                    <div class="bar-element bar-last-month" style="--h-last: 32%;" data-value="120M"></div>
                    <div class="bar-element bar-this-month" style="--h-this: 46%;" data-value="172M"></div>
                </div>
            </div>
            <div class="bar-group-container">
                <div class="bar-pair">
                    <div class="bar-element bar-last-month" style="--h-last: 40%;" data-value="150M"></div>
                    <div class="bar-element bar-this-month" style="--h-this: 58%;" data-value="210M"></div>
                </div>
            </div>
            <div class="bar-group-container">
                <div class="bar-pair">
                    <div class="bar-element bar-last-month" style="--h-last: 35%;" data-value="132M"></div>
                    <div class="bar-element bar-this-month" style="--h-this: 74%;" data-value="280M"></div>
                </div>
            </div>
            <div class="bar-group-container">
                <div class="bar-pair">
                    <div class="bar-element bar-last-month" style="--h-last: 56%;" data-value="210M"></div>
                    <div class="bar-element bar-this-month" style="--h-this: 68%;" data-value="254M"></div>
                </div>
            </div>
        </div>
        
        <div class="chart-labels-x">
            <div class="x-label-item">Tuần 1</div>
            <div class="x-label-item">Tuần 2</div>
            <div class="x-label-item">Tuần 3</div>
            <div class="x-label-item">Tuần 4</div>
        </div>
    </div>

    <!-- Product Structure Donut Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Cơ cấu sản phẩm</span>
        </div>
        
        <div class="doughnut-wrapper">
            <!-- Native SVG circular chart -->
            <div class="donut-chart-container">
                <svg width="160" height="160" viewBox="0 0 100 100" style="transform: rotate(-90deg);">
                    <!-- Base background circle -->
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="12"></circle>
                    
                    <!-- Toys (10%) - Gray -->
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#bdc7c2" stroke-width="12"
                            stroke-dasharray="251.3" stroke-dashoffset="-226.2" stroke-linecap="round"></circle>
                    
                    <!-- Accessories (20%) - Brown -->
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#825736" stroke-width="12"
                            stroke-dasharray="251.3" stroke-dashoffset="-175.9" stroke-linecap="round"></circle>
                    
                    <!-- Wet Food (25%) - Gray Blue -->
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#4b6b60" stroke-width="12"
                            stroke-dasharray="251.3" stroke-dashoffset="-113.1" stroke-linecap="round"></circle>
                    
                    <!-- Dry Food (45%) - Dark Green -->
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#ff782d" stroke-width="12"
                            stroke-dasharray="251.3" stroke-dashoffset="0" stroke-linecap="round"></circle>
                </svg>
                
                <div class="donut-inner-label">
                    <span class="donut-percentage">100%</span>
                    <span class="donut-label-sub">Tổng cộng</span>
                </div>
            </div>  

            <!-- Legend rows -->
            <div class="doughnut-legend-grid">
                <div class="legend-row">
                    <div class="legend-row-left">
                        <span class="legend-color-indicator color-dry-food"></span>
                        <span>Dry Food</span>
                    </div>
                    <span class="legend-value-percentage">45%</span>
                </div>
                <div class="legend-row">
                    <div class="legend-row-left">
                        <span class="legend-color-indicator color-wet-food"></span>
                        <span>Wet Food</span>
                    </div>
                    <span class="legend-value-percentage">25%</span>
                </div>
                <div class="legend-row">
                    <div class="legend-row-left">
                        <span class="legend-color-indicator color-accessories"></span>
                        <span>Accessories</span>
                    </div>
                    <span class="legend-value-percentage">20%</span>
                </div>
                <div class="legend-row">
                    <div class="legend-row-left">
                        <span class="legend-color-indicator color-toys"></span>
                        <span>Toys</span>
                    </div>
                    <span class="legend-value-percentage">10%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders & Selling Items Row -->
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
                        <th style="width: 15%">ID ĐƠN</th>
                        <th style="width: 25%">KHÁCH HÀNG</th>
                        <th style="width: 27%">SẢN PHẨM</th>
                        <th style="width: 16%">TỔNG TIỀN</th>
                        <th style="width: 14%">TRẠNG THÁI</th>
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
                        <tr @class(['order-row-cancelled' => $order->order_status === 'cancelled'])>
                            <td class="col-order-id">{{ $orderCode }}</td>
                            <td class="col-customer">{{ $order->recipient_name }}</td>
                            <td>
                                {{ $firstItem?->product_name ?? 'Không có sản phẩm' }}
                                @if($otherItems > 0)
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">+{{ $otherItems }}</span>
                                @endif
                            </td>
                            <td class="col-total">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</td>
                            <td>
                                <span class="badge-status {{ $orderStatusClasses[$order->order_status] ?? 'status-pending' }}">
                                    {{ $orderStatusLabels[$order->order_status] ?? $order->order_status }}
                                </span>
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

    <!-- Best Selling Items -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Sản phẩm bán chạy nhất</span>
        </div>
        
        <ul class="best-sellers-list">
            <li class="seller-item">
                <div class="seller-item-left">
                    <img src="{{ asset('image/categories/thuc-an-hat.jpg') }}" alt="Royal Canin" class="seller-thumb">
                    <div class="seller-info">
                        <span class="seller-title" title="Royal Canin Mother & Babycat">Royal Canin Mother & Babycat</span>
                        <span class="seller-count">612 lượt bán</span>
                    </div>
                </div>
                <div class="seller-item-right">
                    <span class="seller-price">1.450.000đ</span>
                    <span class="seller-trend" style="color: var(--success);">+15%</span>
                </div>
            </li>
            
            <li class="seller-item">
                <div class="seller-item-left">
                    <!-- Standard clean backup pattern or dynamic svg if missing -->
                    <img src="{{ asset('image/categories/phu-kien.jpg') }}" alt="Máy lọc nước" class="seller-thumb">
                    <div class="seller-info">
                        <span class="seller-title" title="Máy lọc nước tự động PETKIT">Máy lọc nước tự động PETKIT</span>
                        <span class="seller-count">612 lượt bán</span>
                    </div>
                </div>
                <div class="seller-item-right">
                    <span class="seller-price">890.000đ</span>
                    <span class="seller-trend" style="color: var(--success);">+5%</span>
                </div>
            </li>

            <li class="seller-item">
                <div class="seller-item-left">
                    <img src="{{ asset('image/logo/logo.png') }}" alt="Xương gặm KONG" class="seller-thumb">
                    <div class="seller-info">
                        <span class="seller-title" title="Xương gặm KONG Classic Red">Xương gặm KONG Classic Red</span>
                        <span class="seller-count">549 lượt bán</span>
                    </div>
                </div>
                <div class="seller-item-right">
                    <span class="seller-price">320.000đ</span>
                    <span class="seller-trend" style="color: var(--danger);">-2%</span>
                </div>
            </li>

            <li class="seller-item">
                <div class="seller-item-left">
                    <!-- Fallback premium dog/cat bed placeholder -->
                    <img src="{{ asset('image/logo/logo.png') }}" alt="Đệm nằm nhung" class="seller-thumb">
                    <div class="seller-info">
                        <span class="seller-title" title="Đệm nằm nhung cao cấp">Đệm nằm nhung cao cấp</span>
                        <span class="seller-count">420 lượt bán</span>
                    </div>
                </div>
                <div class="seller-item-right">
                    <span class="seller-price">1.200.000đ</span>
                    <span class="seller-trend" style="color: var(--success);">+22%</span>
                </div>
            </li>
        </ul>

        <a href="{{ route('admin.reports.best-sellers') }}" class="btn-detailed-report">BÁO CÁO CHI TIẾT</a>
    </div>
</div>

<!-- Customer Statistics Row -->
<div class="dashboard-row" style="margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Customer Metrics Card -->
    <div class="dashboard-card" style="margin-top: 0; width: 100%;">
        <div class="card-header-styled">
            <span class="card-title-styled">Thống kê Khách hàng</span>
            <a href="{{ route('admin.reports.customers') }}" class="support-link" style="font-size: 0.8rem; font-weight: 600;">CHI TIẾT</a>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
            <div style="background-color: var(--primary-light); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">TỔNG KHÁCH HÀNG</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-top: 4px;">1,350</div>
                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 2px;"><i class="fa-solid fa-arrow-trend-up"></i> +6.2%</div>
            </div>
            <div style="background-color: var(--info-light); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">KHÁCH HÀNG MỚI</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--info); margin-top: 4px;">120</div>
                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 2px;"><i class="fa-solid fa-arrow-trend-up"></i> +15.4%</div>
            </div>
            <div style="background-color: var(--success-light); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">TỶ LỆ QUAY LẠI</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success); margin-top: 4px;">68.2%</div>
                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 2px;"><i class="fa-solid fa-arrow-trend-up"></i> +4.2%</div>
            </div>
            <div style="background-color: var(--purple-light); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">TRUNG BÌNH/KHÁCH</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--purple); margin-top: 4px;">998kđ</div>
                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 2px;"><i class="fa-solid fa-arrow-trend-up"></i> +3.2%</div>
            </div>
        </div>
    </div>

    <!-- Top VIP Customers Card -->
    <div class="dashboard-card" style="margin-top: 0; width: 100%;">
        <div class="card-header-styled">
            <span class="card-title-styled">Top Khách hàng mua nhiều nhất</span>
        </div>
        
        <div class="table-container" style="margin-top: 8px;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>TÊN</th>
                        <th>ĐƠN MUA</th>
                        <th>CHI TIÊU</th>
                        <th>HẠNG</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">P</div>
                                <strong style="color: var(--text-main); font-size: 0.85rem;">Phạm Minh D</strong>
                            </div>
                        </td>
                        <td style="font-size: 0.85rem;">18 đơn</td>
                        <td style="font-weight: 700; color: var(--success); font-size: 0.85rem;">32.400.000đ</td>
                        <td><span style="padding: 2px 6px; background-color: #fef3c7; color: #d97706; border: 1px solid #fcd34d; font-size: 0.7rem; font-weight: 700; border-radius: 4px;">VIP</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">N</div>
                                <strong style="color: var(--text-main); font-size: 0.85rem;">Nguyễn Văn A</strong>
                            </div>
                        </td>
                        <td style="font-size: 0.85rem;">12 đơn</td>
                        <td style="font-weight: 700; color: var(--success); font-size: 0.85rem;">15.300.000đ</td>
                        <td><span style="padding: 2px 6px; background-color: #fffbeb; color: #b45309; border: 1px solid #fef3c7; font-size: 0.7rem; font-weight: 700; border-radius: 4px;">GOLD</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">T</div>
                                <strong style="color: var(--text-main); font-size: 0.85rem;">Trần Thị B</strong>
                            </div>
                        </td>
                        <td style="font-size: 0.85rem;">9 đơn</td>
                        <td style="font-weight: 700; color: var(--success); font-size: 0.85rem;">11.200.000đ</td>
                        <td><span style="padding: 2px 6px; background-color: #fffbeb; color: #b45309; border: 1px solid #fef3c7; font-size: 0.7rem; font-weight: 700; border-radius: 4px;">GOLD</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Low Stock Products Row -->
<div class="dashboard-row" style="margin-top: 24px;">
    <div class="dashboard-card" style="width: 100%;">
        <div class="card-header-styled">
            <span class="card-title-styled">Sản phẩm sắp hết hàng</span>
            <a href="{{ route('admin.reports.low-stock') }}" class="support-link" style="font-size: 0.8rem; font-weight: 600;">XEM CHI TIẾT BÁO CÁO</a>
        </div>
        
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 35%">SẢN PHẨM / PHÂN LOẠI</th>
                        <th style="width: 20%">MÃ SKU</th>
                        <th style="width: 15%">TỒN KHO CÒN</th>
                        <th style="width: 15%">TRẠNG THÁI</th>
                        <th style="width: 15%">TÁC VỤ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div>
                                <strong>Pate Royal Canin Mini Puppy</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Lon 195g</span>
                            </div>
                        </td>
                        <td><span class="slug-text">RC-PUP-LON-195G</span></td>
                        <td style="font-weight: 700; color: var(--danger);">0 SP</td>
                        <td><span class="badge-status status-cancelled">HẾT HÀNG</span></td>
                        <td>
                            <button class="btn-action-stock" style="padding: 6px 12px; background-color: var(--primary); color: white; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: var(--transition);" onclick="alert('Đã gửi yêu cầu nhập thêm sản phẩm RC-PUP-LON-195G')">Nhập hàng</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <strong>Pate Me-O Cá Ngừ</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Lốc 12 túi</span>
                            </div>
                        </td>
                        <td><span class="slug-text">ME-O-CANGU-12</span></td>
                        <td style="font-weight: 700; color: var(--danger);">0 SP</td>
                        <td><span class="badge-status status-cancelled">HẾT HÀNG</span></td>
                        <td>
                            <button class="btn-action-stock" style="padding: 6px 12px; background-color: var(--primary); color: white; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: var(--transition);" onclick="alert('Đã gửi yêu cầu nhập thêm sản phẩm ME-O-CANGU-12')">Nhập hàng</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <strong>Royal Canin Mini Adult</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Bao 3kg</span>
                            </div>
                        </td>
                        <td><span class="slug-text">RC-MA-BAO-3KG</span></td>
                        <td style="font-weight: 700; color: var(--warning);">1 SP</td>
                        <td><span class="badge-status status-pending">SẮP HẾT</span></td>
                        <td>
                            <button class="btn-action-stock" style="padding: 6px 12px; background-color: var(--primary); color: white; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: var(--transition);" onclick="alert('Đã gửi yêu cầu nhập thêm sản phẩm RC-MA-BAO-3KG')">Nhập hàng</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <strong>Dây dắt Trixie Premium</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Kích thước S</span>
                            </div>
                        </td>
                        <td><span class="slug-text">TX-DD-PRE-S</span></td>
                        <td style="font-weight: 700; color: var(--warning);">3 SP</td>
                        <td><span class="badge-status status-pending">SẮP HẾT</span></td>
                        <td>
                            <button class="btn-action-stock" style="padding: 6px 12px; background-color: var(--primary); color: white; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: var(--transition);" onclick="alert('Đã gửi yêu cầu nhập thêm sản phẩm TX-DD-PRE-S')">Nhập hàng</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
