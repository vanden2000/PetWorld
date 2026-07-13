@extends('admin.layouts.app')

@section('title', 'Thống kê Doanh thu')

@section('styles')
<style>
    .dropdown-filter-container {
        position: relative;
        display: inline-block;
    }
    .filter-dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        z-index: 100;
        min-width: 160px;
        margin-top: 8px;
        overflow: hidden;
    }
    .filter-option {
        display: block;
        padding: 10px 16px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: var(--transition);
        text-align: left;
    }
    .filter-option:hover {
        background-color: var(--bg-color);
        color: var(--primary);
    }
    .filter-option.active {
        background-color: var(--primary-light);
        color: var(--primary);
        font-weight: 600;
    }
    .revenue-detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }
    @media (max-width: 1024px) {
        .revenue-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Thống kê Doanh thu</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Xem báo cáo doanh thu chi tiết, tỷ suất lợi nhuận và doanh số bán hàng theo danh mục.</p>
    </div>
    
    <div class="header-actions">
        <div class="dropdown-filter-container">
            <button class="filter-dropdown" id="filter-btn">
                <i class="fa-regular fa-calendar-days"></i>
                <span id="filter-label">30 NGÀY QUA</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <div class="filter-dropdown-menu" id="filter-menu">
                <a href="#" class="filter-option" data-value="today">Hôm nay</a>
                <a href="#" class="filter-option" data-value="7days">7 ngày trước</a>
                <a href="#" class="filter-option active" data-value="30days">30 ngày trước</a>
            </div>
        </div>
        <button class="btn-export" onclick="alert('Đang tải xuống báo cáo...')">
            <i class="fa-solid fa-download"></i>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="stat-trend trend-up" id="revenue-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+12.5%</span>
            </div>
        </div>
        <div class="stat-label">Tổng doanh thu</div>
        <div class="stat-value" id="revenue-val">1.284.000.000đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-orders">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-trend trend-up" id="orders-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+8.2%</span>
            </div>
        </div>
        <div class="stat-label">Đơn hàng thành công</div>
        <div class="stat-value" id="orders-val">3,120 đơn</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div class="stat-trend trend-down" id="aov-trend">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>-2.4%</span>
            </div>
        </div>
        <div class="stat-label">Giá trị trung bình đơn</div>
        <div class="stat-value" id="aov-val">372.000đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-conversion" style="background-color: var(--purple-light); color: var(--purple);">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div class="stat-trend trend-up" id="margin-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+1.5%</span>
            </div>
        </div>
        <div class="stat-label">Tỷ suất lợi nhuận TB</div>
        <div class="stat-value" id="margin-val">35.4%</div>
    </div>
</div>

<div class="revenue-detail-grid">
    <!-- Revenue Table -->
    <div class="dashboard-card" style="margin-top: 0;">
        <div class="card-header-styled">
            <span class="card-title-styled">Chi tiết doanh thu theo thời gian</span>
        </div>
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>THỜI GIAN</th>
                        <th>SỐ ĐƠN HÀNG</th>
                        <th>DOANH THU THÔ</th>
                        <th>CHIẾT KHẤU / GIẢM GIÁ</th>
                        <th>DOANH THU THỰC TẾ</th>
                    </tr>
                </thead>
                <tbody id="revenue-table-body">
                    <!-- Loaded dynamically via js -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue by category list -->
    <div class="dashboard-card" style="margin-top: 0;">
        <div class="card-header-styled">
            <span class="card-title-styled">Doanh thu theo danh mục</span>
        </div>
        <div class="doughnut-wrapper" style="flex-direction: column; gap: 20px;">
            <div class="doughnut-legend-grid" style="width: 100%; display: flex; flex-direction: column; gap: 14px;" id="category-list">
                <!-- Loaded dynamically via js -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtn = document.getElementById('filter-btn');
        const filterMenu = document.getElementById('filter-menu');
        const filterLabel = document.getElementById('filter-label');
        const filterOptions = document.querySelectorAll('.filter-option');

        // Toggle Filter Dropdown
        filterBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isDisplayed = filterMenu.style.display === 'block';
            filterMenu.style.display = isDisplayed ? 'none' : 'block';
        });

        document.addEventListener('click', function() {
            filterMenu.style.display = 'none';
        });

        // Mock data dictionary
        const mockData = {
            today: {
                revenue: "38.200.000đ",
                revenueTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+14.2%</span>",
                orders: "95 đơn",
                ordersTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+6.5%</span>",
                aov: "402.000đ",
                aovTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+2.1%</span>",
                margin: "36.8%",
                marginTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+0.8%</span>",
                categories: [
                    { name: "Dry Food", percentage: "48%", val: "18.336.000đ", class: "color-dry-food", color: "#ff782d" },
                    { name: "Wet Food", percentage: "24%", val: "9.168.000đ", class: "color-wet-food", color: "#4b6b60" },
                    { name: "Accessories", percentage: "18%", val: "6.876.000đ", class: "color-accessories", color: "#825736" },
                    { name: "Toys", percentage: "10%", val: "3.820.000đ", class: "color-toys", color: "#bdc7c2" }
                ],
                table: [
                    { time: "08:00 - 10:00", count: 18, gross: "7.500.000đ", discount: "320.000đ", net: "7.180.000đ" },
                    { time: "10:00 - 12:00", count: 28, gross: "11.200.000đ", discount: "540.000đ", net: "10.660.000đ" },
                    { time: "12:00 - 14:00", count: 14, gross: "5.600.000đ", discount: "180.000đ", net: "5.420.000đ" },
                    { time: "14:00 - 16:00", count: 22, gross: "8.900.000đ", discount: "410.000đ", net: "8.490.000đ" },
                    { time: "16:00 - 18:00", count: 13, gross: "6.800.000đ", discount: "350.000đ", net: "6.450.000đ" }
                ]
            },
            "7days": {
                revenue: "268.400.000đ",
                revenueTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+9.8%</span>",
                orders: "680 đơn",
                ordersTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+7.2%</span>",
                aov: "394.000đ",
                aovTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-1.2%</span>",
                margin: "35.9%",
                marginTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+1.1%</span>",
                categories: [
                    { name: "Dry Food", percentage: "46%", val: "123.464.000đ", class: "color-dry-food", color: "#ff782d" },
                    { name: "Wet Food", percentage: "25%", val: "67.100.000đ", class: "color-wet-food", color: "#4b6b60" },
                    { name: "Accessories", percentage: "19%", val: "50.996.000đ", class: "color-accessories", color: "#825736" },
                    { name: "Toys", percentage: "10%", val: "26.840.000đ", class: "color-toys", color: "#bdc7c2" }
                ],
                table: [
                    { time: "Hôm qua", count: 90, gross: "36.500.000đ", discount: "1.450.000đ", net: "35.050.000đ" },
                    { time: "2 ngày trước", count: 102, gross: "41.200.000đ", discount: "2.100.000đ", net: "39.100.000đ" },
                    { time: "3 ngày trước", count: 96, gross: "38.700.000đ", discount: "1.800.000đ", net: "36.900.000đ" },
                    { time: "4 ngày trước", count: 110, gross: "44.100.000đ", discount: "2.500.000đ", net: "41.600.000đ" },
                    { time: "5 ngày trước", count: 94, gross: "37.800.000đ", discount: "1.650.000đ", net: "36.150.000đ" },
                    { time: "6 ngày trước", count: 88, gross: "35.200.000đ", discount: "1.300.000đ", net: "33.900.000đ" }
                ]
            },
            "30days": {
                revenue: "1.284.000.000đ",
                revenueTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+12.5%</span>",
                orders: "3,120 đơn",
                ordersTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+8.2%</span>",
                aov: "372.000đ",
                aovTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-2.4%</span>",
                margin: "35.4%",
                marginTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+1.5%</span>",
                categories: [
                    { name: "Dry Food", percentage: "45%", val: "577.800.000đ", class: "color-dry-food", color: "#ff782d" },
                    { name: "Wet Food", percentage: "25%", val: "321.000.000đ", class: "color-wet-food", color: "#4b6b60" },
                    { name: "Accessories", percentage: "20%", val: "256.800.000đ", class: "color-accessories", color: "#825736" },
                    { name: "Toys", percentage: "10%", val: "128.400.000đ", class: "color-toys", color: "#bdc7c2" }
                ],
                table: [
                    { time: "Tuần 1", count: 680, gross: "275.000.000đ", discount: "11.200.000đ", net: "263.800.000đ" },
                    { time: "Tuần 2", count: 742, gross: "305.000.000đ", discount: "14.500.000đ", net: "290.500.000đ" },
                    { time: "Tuần 3", count: 815, gross: "348.000.000đ", discount: "15.800.000đ", net: "332.200.000đ" },
                    { time: "Tuần 4", count: 883, gross: "385.000.000đ", discount: "17.500.000đ", net: "367.500.000đ" }
                ]
            }
        };

        // Render function
        function updatePage(filter) {
            const data = mockData[filter];
            
            // Update stats
            document.getElementById('revenue-val').innerText = data.revenue;
            document.getElementById('revenue-trend').innerHTML = data.revenueTrend;
            document.getElementById('orders-val').innerText = data.orders;
            document.getElementById('orders-trend').innerHTML = data.ordersTrend;
            document.getElementById('aov-val').innerText = data.aov;
            document.getElementById('aov-trend').innerHTML = data.aovTrend;
            document.getElementById('margin-val').innerText = data.margin;
            document.getElementById('margin-trend').innerHTML = data.marginTrend;

            // Render categories list
            const categoryList = document.getElementById('category-list');
            categoryList.innerHTML = '';
            data.categories.forEach(cat => {
                categoryList.innerHTML += `
                    <div class="legend-row" style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <div class="legend-row-left" style="display: flex; align-items: center; gap: 10px;">
                            <span class="legend-color-indicator ${cat.class}" style="width: 12px; height: 12px; border-radius: 50%; background-color: ${cat.color}; display: inline-block;"></span>
                            <span style="font-weight: 600;">${cat.name} (${cat.percentage})</span>
                        </div>
                        <span class="legend-value-percentage" style="font-weight: 700; color: var(--primary);">${cat.val}</span>
                    </div>
                `;
            });

            // Render table
            const tableBody = document.getElementById('revenue-table-body');
            tableBody.innerHTML = '';
            data.table.forEach(row => {
                tableBody.innerHTML += `
                    <tr>
                        <td style="font-weight: 700;">${row.time}</td>
                        <td>${row.count}</td>
                        <td>${row.gross}</td>
                        <td style="color: var(--danger); font-weight: 500;">-${row.discount}</td>
                        <td class="col-total" style="font-weight: 700; color: var(--success);">${row.net}</td>
                    </tr>
                `;
            });
        }

        // Handle Filter Option click
        filterOptions.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                filterOptions.forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                
                const val = this.getAttribute('data-value');
                filterLabel.innerText = this.innerText.toUpperCase();
                updatePage(val);
                filterMenu.style.display = 'none';
            });
        });

        // Initial render
        updatePage('30days');
    });
</script>
@endsection
