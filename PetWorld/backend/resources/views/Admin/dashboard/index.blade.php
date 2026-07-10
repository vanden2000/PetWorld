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
            <a href="#" class="support-link" style="font-size: 0.8rem; font-weight: 600;">XEM TẤT CẢ</a>
        </div>
        
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 15%">ID ĐƠN</th>
                        <th style="width: 25%">KHÁCH HÀNG</th>
                        <th style="width: 30%">SẢN PHẨM</th>
                        <th style="width: 16%">TỔNG TIỀN</th>
                        <th style="width: 14%">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-order-id">#PW-8291</td>
                        <td class="col-customer">Nguyễn Văn A</td>
                        <td>Royal Canin Adult 5kg</td>
                        <td class="col-total">1.250.000đ</td>
                        <td><span class="badge-status status-completed">HOÀN TẤT</span></td>
                    </tr>
                    <tr>
                        <td class="col-order-id">#PW-8292</td>
                        <td class="col-customer">Trần Thị B</td>
                        <td>Dây dắt tự động LED</td>
                        <td class="col-total">450.000đ</td>
                        <td><span class="badge-status status-pending">CHỜ XỬ LÝ</span></td>
                    </tr>
                    <tr>
                        <td class="col-order-id">#PW-8293</td>
                        <td class="col-customer">Lê Hoàng C</td>
                        <td>Pate Whiskas 12 gói</td>
                        <td class="col-total">180.000đ</td>
                        <td><span class="badge-status status-completed">HOÀN TẤT</span></td>
                    </tr>
                    <tr>
                        <td class="col-order-id">#PW-8294</td>
                        <td class="col-customer">Phạm Minh D</td>
                        <td>Chuồng chó inox 304</td>
                        <td class="col-total">2.800.000đ</td>
                        <td><span class="badge-status status-cancelled">ĐÃ HỦY</span></td>
                    </tr>
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
                    <img src="{{ asset('image/thuc-an-hat.jpg') }}" alt="Royal Canin" class="seller-thumb" onerror="this.src='https://images.unsplash.com/photo-1583337130417-3346a1be7dee?q=80&w=120&auto=format&fit=crop'">
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
                    <img src="{{ asset('image/phu-kien.jpg') }}" alt="Máy lọc nước" class="seller-thumb" onerror="this.src='https://images.unsplash.com/photo-1601758228041-f3b2795255f1?q=80&w=120&auto=format&fit=crop'">
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
                    <img src="{{ asset('image/do-choi.jpg') }}" alt="Xương gặm KONG" class="seller-thumb" onerror="this.src='https://images.unsplash.com/photo-1576201836106-db1758fd1c97?q=80&w=120&auto=format&fit=crop'">
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
                    <img src="{{ asset('image/snack.jpg') }}" alt="Đệm nằm nhung" class="seller-thumb" onerror="this.src='https://images.unsplash.com/photo-1541599540903-216a46ca1ad0?q=80&w=120&auto=format&fit=crop'">
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

        <a href="#" class="btn-detailed-report">BÁO CÁO CHI TIẾT</a>
    </div>
</div>
@endsection