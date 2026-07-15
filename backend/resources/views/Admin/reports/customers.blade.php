@extends('admin.layouts.app')

@section('title', 'Thống kê Khách hàng')

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
    .badge-member {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
    }
    .member-vip {
        background-color: #fef3c7;
        color: #d97706;
        border: 1px solid #fcd34d;
    }
    .member-gold {
        background-color: #fffbeb;
        color: #b45309;
        border: 1px solid #fef3c7;
    }
    .member-silver {
        background-color: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Thống kê Khách hàng</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Xem thông tin tăng trưởng số lượng tài khoản, tỷ lệ giữ chân khách hàng và danh sách khách hàng VIP.</p>
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
            <div class="stat-icon-wrapper icon-conversion" style="background-color: var(--primary-light); color: var(--primary);">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-trend trend-up" id="customers-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+6.2%</span>
            </div>
        </div>
        <div class="stat-label">Tổng khách hàng đăng ký</div>
        <div class="stat-value" id="customers-val">1,250</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-orders" style="background-color: var(--info-light); color: var(--info);">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="stat-trend trend-up" id="new-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+15.4%</span>
            </div>
        </div>
        <div class="stat-label">Khách hàng mới</div>
        <div class="stat-value" id="new-val">120</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov" style="background-color: var(--success-light); color: var(--success);">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="stat-trend trend-up" id="returning-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+4.2%</span>
            </div>
        </div>
        <div class="stat-label">Tỷ lệ khách hàng quay lại</div>
        <div class="stat-value" id="returning-val">68.2%</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue" style="background-color: var(--purple-light); color: var(--purple);">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="stat-trend trend-up" id="spent-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+3.2%</span>
            </div>
        </div>
        <div class="stat-label">Chi tiêu trung bình/khách</div>
        <div class="stat-value" id="spent-val">980.000đ</div>
    </div>
</div>

<!-- Top Spending Customers Table -->
<div class="dashboard-card" style="margin-top: 24px;">
    <div class="card-header-styled">
        <span class="card-title-styled">Danh sách khách hàng VIP chi tiêu nhiều nhất</span>
    </div>
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>KHÁCH HÀNG</th>
                    <th>EMAIL</th>
                    <th>SỐ ĐƠN MUA</th>
                    <th>TỔNG CHI TIÊU</th>
                    <th>HẠNG THÀNH VIÊN</th>
                </tr>
            </thead>
            <tbody id="customers-table-body">
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
                total: "1,250",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+0.2%</span>",
                new: "5 thành viên",
                newTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+2.4%</span>",
                returning: "64.2%",
                returningTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+0.5%</span>",
                spent: "982.000đ",
                spentTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+0.1%</span>",
                customers: [
                    { name: "Nguyễn Văn A", email: "vana@gmail.com", count: 2, totalSpent: "2.500.000đ", rank: "Gold", rankClass: "member-gold" },
                    { name: "Trần Thị B", email: "thib@gmail.com", count: 1, totalSpent: "1.200.000đ", rank: "Silver", rankClass: "member-silver" },
                    { name: "Lê Văn C", email: "vanc@gmail.com", count: 1, totalSpent: "850.000đ", rank: "Silver", rankClass: "member-silver" }
                ]
            },
            "7days": {
                total: "1,268",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+1.5%</span>",
                new: "28 thành viên",
                newTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+5.8%</span>",
                returning: "66.5%",
                returningTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+1.2%</span>",
                spent: "986.000đ",
                spentTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+1.0%</span>",
                customers: [
                    { name: "Phạm Minh D", email: "minhd@gmail.com", count: 5, totalSpent: "8.400.000đ", rank: "VIP", rankClass: "member-vip" },
                    { name: "Nguyễn Văn A", email: "vana@gmail.com", count: 4, totalSpent: "5.100.000đ", rank: "Gold", rankClass: "member-gold" },
                    { name: "Trần Thị B", email: "thib@gmail.com", count: 3, totalSpent: "3.200.000đ", rank: "Gold", rankClass: "member-gold" },
                    { name: "Lê Văn C", email: "vanc@gmail.com", count: 2, totalSpent: "1.700.000đ", rank: "Silver", rankClass: "member-silver" }
                ]
            },
            "30days": {
                total: "1,350",
                totalTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+6.2%</span>",
                new: "120 thành viên",
                newTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+15.4%</span>",
                returning: "68.2%",
                returningTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+4.2%</span>",
                spent: "998.000đ",
                spentTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+3.2%</span>",
                customers: [
                    { name: "Phạm Minh D", email: "minhd@gmail.com", count: 18, totalSpent: "32.400.000đ", rank: "VIP", rankClass: "member-vip" },
                    { name: "Nguyễn Văn A", email: "vana@gmail.com", count: 12, totalSpent: "15.300.000đ", rank: "Gold", rankClass: "member-gold" },
                    { name: "Trần Thị B", email: "thib@gmail.com", count: 9, totalSpent: "11.200.000đ", rank: "Gold", rankClass: "member-gold" },
                    { name: "Lê Hoàng C", email: "hoangc@gmail.com", count: 6, totalSpent: "8.900.000đ", rank: "Silver", rankClass: "member-silver" },
                    { name: "Vũ Văn E", email: "vane@gmail.com", count: 5, totalSpent: "5.500.000đ", rank: "Silver", rankClass: "member-silver" }
                ]
            }
        };

        // Render page function
        function updatePage(filter) {
            const data = mockData[filter];

            // Update stats
            document.getElementById('customers-val').innerText = data.total;
            document.getElementById('customers-trend').innerHTML = data.totalTrend;
            document.getElementById('new-val').innerText = data.new;
            document.getElementById('new-trend').innerHTML = data.newTrend;
            document.getElementById('returning-val').innerText = data.returning;
            document.getElementById('returning-trend').innerHTML = data.returningTrend;
            document.getElementById('spent-val').innerText = data.spent;
            document.getElementById('spent-trend').innerHTML = data.spentTrend;

            // Render table
            const tableBody = document.getElementById('customers-table-body');
            tableBody.innerHTML = '';
            data.customers.forEach(cust => {
                tableBody.innerHTML += `
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;">
                                    ${cust.name.charAt(0).toUpperCase()}
                                </div>
                                <strong style="color: var(--text-main);">${cust.name}</strong>
                            </div>
                        </td>
                        <td>${cust.email}</td>
                        <td style="font-weight: 600;">${cust.count} đơn</td>
                        <td style="font-weight: 700; color: var(--success);">${cust.totalSpent}</td>
                        <td><span class="badge-member ${cust.rankClass}">${cust.rank}</span></td>
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
