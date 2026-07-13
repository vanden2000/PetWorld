@extends('admin.layouts.app')

@section('title', 'Đơn hàng mới nhất')

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
    .btn-detail-action {
        background-color: var(--primary-light);
        color: var(--primary);
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
    }
    .btn-detail-action:hover {
        background-color: var(--primary);
        color: #ffffff;
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Các đơn hàng mới nhất</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Danh sách đơn hàng vừa phát sinh trong hệ thống, trạng thái thanh toán và thông tin giao nhận ban đầu.</p>
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
                <i class="fa-solid fa-cart-arrow-down"></i>
            </div>
            <div class="stat-trend trend-up" id="orders-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+12.4%</span>
            </div>
        </div>
        <div class="stat-label">Tổng đơn mới phát sinh</div>
        <div class="stat-value" id="orders-val">452 đơn</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue" style="background-color: var(--success-light); color: var(--success);">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="stat-trend trend-up" id="revenue-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+15.2%</span>
            </div>
        </div>
        <div class="stat-label">Doanh thu từ đơn mới</div>
        <div class="stat-value" id="revenue-val">168.000.000đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
        <div class="stat-label">Giá trị trung bình đơn mới</div>
        <div class="stat-value" id="aov-val">372.000đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-conversion" style="background-color: var(--warning-light); color: var(--warning);">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
        <div class="stat-label">Đơn hàng chờ xử lý</div>
        <div class="stat-value" id="pending-val">92 đơn</div>
    </div>
</div>

<!-- Table Card -->
<div class="dashboard-card" style="margin-top: 24px;">
    <div class="card-header-styled">
        <span class="card-title-styled">Danh sách đơn hàng mới nhất</span>
    </div>
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 15%">ID ĐƠN</th>
                    <th style="width: 20%">KHÁCH HÀNG</th>
                    <th style="width: 25%">SẢN PHẨM MUA</th>
                    <th style="width: 15%">TỔNG TIỀN</th>
                    <th style="width: 13%">TRẠNG THÁI</th>
                    <th style="width: 12%">HÀNH ĐỘNG</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                <!-- Loaded dynamically via JS -->
            </tbody>
        </table>
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
                total: "15 đơn",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+4.2%</span>",
                revenue: "5.600.000đ",
                revenueTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+6.8%</span>",
                aov: "373.000đ",
                pending: "2 đơn",
                orders: [
                    { id: "#PW-8291", customer: "Nguyễn Văn A", items: "Royal Canin Adult 5kg", total: "1.250.000đ", status: "HOÀN TẤT", statusClass: "status-completed" },
                    { id: "#PW-8292", customer: "Trần Thị B", items: "Dây dắt tự động LED", total: "450.000đ", status: "CHỜ XỬ LÝ", statusClass: "status-pending" }
                ]
            },
            "7days": {
                total: "95 đơn",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+9.5%</span>",
                revenue: "36.200.000đ",
                revenueTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+10.4%</span>",
                aov: "381.000đ",
                pending: "15 đơn",
                orders: [
                    { id: "#PW-8291", customer: "Nguyễn Văn A", items: "Royal Canin Adult 5kg", total: "1.250.000đ", status: "HOÀN TẤT", statusClass: "status-completed" },
                    { id: "#PW-8292", customer: "Trần Thị B", items: "Dây dắt tự động LED", total: "450.000đ", status: "CHỜ XỬ LÝ", statusClass: "status-pending" },
                    { id: "#PW-8293", customer: "Lê Hoàng C", items: "Pate Whiskas 12 gói", total: "180.000đ", status: "HOÀN TẤT", statusClass: "status-completed" }
                ]
            },
            "30days": {
                total: "452 đơn",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+12.4%</span>",
                revenue: "168.000.000đ",
                revenueTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+15.2%</span>",
                aov: "372.000đ",
                pending: "92 đơn",
                orders: [
                    { id: "#PW-8291", customer: "Nguyễn Văn A", items: "Royal Canin Adult 5kg", total: "1.250.000đ", status: "HOÀN TẤT", statusClass: "status-completed" },
                    { id: "#PW-8292", customer: "Trần Thị B", items: "Dây dắt tự động LED", total: "450.000đ", status: "CHỜ XỬ LÝ", statusClass: "status-pending" },
                    { id: "#PW-8293", customer: "Lê Hoàng C", items: "Pate Whiskas 12 gói", total: "180.000đ", status: "HOÀN TẤT", statusClass: "status-completed" },
                    { id: "#PW-8294", customer: "Phạm Minh D", items: "Chuồng chó inox 304", total: "2.800.000đ", status: "ĐẠ HỦY", statusClass: "status-cancelled" }
                ]
            }
        };

        // Render page function
        function updatePage(filter) {
            const data = mockData[filter];

            // Update stats
            document.getElementById('orders-val').innerText = data.total;
            document.getElementById('orders-trend').innerHTML = data.totalTrend;
            document.getElementById('revenue-val').innerText = data.revenue;
            document.getElementById('revenue-trend').innerHTML = data.revenueTrend;
            document.getElementById('aov-val').innerText = data.aov;
            document.getElementById('pending-val').innerText = data.pending;

            // Render table
            const tableBody = document.getElementById('orders-table-body');
            tableBody.innerHTML = '';
            data.orders.forEach(order => {
                tableBody.innerHTML += `
                    <tr>
                        <td class="col-order-id">${order.id}</td>
                        <td class="col-customer">${order.customer}</td>
                        <td style="font-weight: 500;">${order.items}</td>
                        <td class="col-total" style="font-weight: 700;">${order.total}</td>
                        <td><span class="badge-status ${order.statusClass}">${order.status}</span></td>
                        <td>
                            <a href="#" class="btn-detail-action" onclick="alert('Xem chi tiết đơn hàng ${order.id}')">Chi tiết</a>
                        </td>
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
