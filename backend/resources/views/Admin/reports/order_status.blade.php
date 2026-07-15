@extends('admin.layouts.app')

@section('title', 'Trạng thái Đơn hàng')

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
    .status-bar-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 10px;
    }
    .status-bar-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .status-bar-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }
    .status-progress-track {
        height: 12px;
        background-color: var(--border-color);
        border-radius: 6px;
        overflow: hidden;
        width: 100%;
    }
    .status-progress-fill {
        height: 100%;
        border-radius: 6px;
        transition: width 0.6s ease;
    }
    .grid-order-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }
    @media (max-width: 768px) {
        .grid-order-split {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Trạng thái Đơn hàng</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Thống kê chi tiết tỷ lệ chuyển đổi đơn hàng, tốc độ giao nhận và phân bố trạng thái đơn hàng.</p>
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
            <div class="stat-icon-wrapper icon-orders">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="stat-trend trend-up" id="orders-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+8.2%</span>
            </div>
        </div>
        <div class="stat-label">Tổng đơn hàng</div>
        <div class="stat-value" id="orders-val">3,452</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue" style="background-color: var(--success-light); color: var(--success);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-trend trend-up" id="completed-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+12.4%</span>
            </div>
        </div>
        <div class="stat-label">Đơn hoàn tất</div>
        <div class="stat-value" id="completed-val">3,120</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov" style="background-color: var(--warning-light); color: var(--warning);">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div class="stat-trend trend-down" id="pending-trend">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>-5.4%</span>
            </div>
        </div>
        <div class="stat-label">Đang xử lý / Đang giao</div>
        <div class="stat-value" id="pending-val">242</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-conversion" style="background-color: var(--danger-light); color: var(--danger);">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div class="stat-trend trend-down" id="cancelled-trend">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>-2.1%</span>
            </div>
        </div>
        <div class="stat-label">Đơn đã hủy</div>
        <div class="stat-value" id="cancelled-val">90</div>
    </div>
</div>

<div class="grid-order-split">
    <!-- Visual Distribution Chart -->
    <div class="dashboard-card" style="margin-top: 0;">
        <div class="card-header-styled">
            <span class="card-title-styled">Biểu đồ phân bố trạng thái</span>
        </div>
        <div class="status-bar-container" id="status-bar-list">
            <!-- Loaded dynamically via JS -->
        </div>
    </div>

    <!-- Details Table -->
    <div class="dashboard-card" style="margin-top: 0;">
        <div class="card-header-styled">
            <span class="card-title-styled">Báo cáo chi tiết</span>
        </div>
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>TRẠNG THÁI</th>
                        <th>SỐ LƯỢNG</th>
                        <th>TỶ LỆ</th>
                        <th>XU HƯỚNG</th>
                    </tr>
                </thead>
                <tbody id="status-table-body">
                    <!-- Loaded dynamically via JS -->
                </tbody>
            </table>
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
                total: "102 đơn",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+14.2%</span>",
                completed: "95 đơn",
                completedTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+16.5%</span>",
                pending: "5 đơn",
                pendingTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-2.1%</span>",
                cancelled: "2 đơn",
                cancelledTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-1.5%</span>",
                statuses: [
                    { name: "Hoàn tất (Completed)", count: 95, percentage: "93.1%", color: "var(--success)" },
                    { name: "Đang giao (Shipping)", count: 3, percentage: "2.9%", color: "var(--info)" },
                    { name: "Chờ xử lý (Pending)", count: 2, percentage: "2.0%", color: "var(--warning)" },
                    { name: "Đã hủy (Cancelled)", count: 2, percentage: "2.0%", color: "var(--danger)" }
                ]
            },
            "7days": {
                total: "732 đơn",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+9.8%</span>",
                completed: "680 đơn",
                completedTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+11.2%</span>",
                pending: "35 đơn",
                pendingTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-4.5%</span>",
                cancelled: "17 đơn",
                cancelledTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-0.8%</span>",
                statuses: [
                    { name: "Hoàn tất (Completed)", count: 680, percentage: "92.9%", color: "var(--success)" },
                    { name: "Đang giao (Shipping)", count: 20, percentage: "2.7%", color: "var(--info)" },
                    { name: "Chờ xử lý (Pending)", count: 15, percentage: "2.1%", color: "var(--warning)" },
                    { name: "Đã hủy (Cancelled)", count: 17, percentage: "2.3%", color: "var(--danger)" }
                ]
            },
            "30days": {
                total: "3,452 đơn",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+8.2%</span>",
                completed: "3,120 đơn",
                completedTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+12.4%</span>",
                pending: "242 đơn",
                pendingTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-5.4%</span>",
                cancelled: "90 đơn",
                cancelledTrend: "<i class='fa-solid fa-arrow-trend-down'></i> <span>-2.1%</span>",
                statuses: [
                    { name: "Hoàn tất (Completed)", count: 3120, percentage: "90.4%", color: "var(--success)" },
                    { name: "Đang giao (Shipping)", count: 150, percentage: "4.3%", color: "var(--info)" },
                    { name: "Chờ xử lý (Pending)", count: 92, percentage: "2.7%", color: "var(--warning)" },
                    { name: "Đã hủy (Cancelled)", count: 90, percentage: "2.6%", color: "var(--danger)" }
                ]
            }
        };

        // Render page function
        function updatePage(filter) {
            const data = mockData[filter];

            // Update stats
            document.getElementById('orders-val').innerText = data.total;
            document.getElementById('orders-trend').innerHTML = data.totalTrend;
            document.getElementById('completed-val').innerText = data.completed;
            document.getElementById('completed-trend').innerHTML = data.completedTrend;
            document.getElementById('pending-val').innerText = data.pending;
            document.getElementById('pending-trend').innerHTML = data.pendingTrend;
            document.getElementById('cancelled-val').innerText = data.cancelled;
            document.getElementById('cancelled-trend').innerHTML = data.cancelledTrend;

            // Render status bar list
            const barList = document.getElementById('status-bar-list');
            barList.innerHTML = '';
            data.statuses.forEach(status => {
                barList.innerHTML += `
                    <div class="status-bar-item">
                        <div class="status-bar-header">
                            <span>${status.name}</span>
                            <span>${status.count} đơn (${status.percentage})</span>
                        </div>
                        <div class="status-progress-track">
                            <div class="status-progress-fill" style="width: ${status.percentage}; background-color: ${status.color}"></div>
                        </div>
                    </div>
                `;
            });

            // Render table
            const tableBody = document.getElementById('status-table-body');
            tableBody.innerHTML = '';
            data.statuses.forEach(status => {
                let badgeClass = 'status-completed';
                if (status.name.includes('Pending')) badgeClass = 'status-pending';
                if (status.name.includes('Cancelled')) badgeClass = 'status-cancelled';
                if (status.name.includes('Shipping')) badgeClass = 'status-pending'; // using pending style for shipping as general loading

                tableBody.innerHTML += `
                    <tr>
                        <td><span class="badge-status ${badgeClass}">${status.name.split(' (')[0].toUpperCase()}</span></td>
                        <td style="font-weight: 700;">${status.count}</td>
                        <td style="font-weight: 600; color: var(--primary);">${status.percentage}</td>
                        <td style="color: var(--success); font-weight: 500;"><i class="fa-solid fa-circle-arrow-up"></i> Tăng trưởng tốt</td>
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
