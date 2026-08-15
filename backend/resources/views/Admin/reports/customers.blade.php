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
        background-color: #fff4ec;
        color: var(--primary);
    }
    .filter-option.active {
        background-color: #fff4ec;
        color: var(--primary);
        font-weight: 600;
    }

    /* Stats Cards Premium Style */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 560px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow-subtle);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #3b82f6;
        border-radius: 4px 0 0 4px;
    }
    .stat-card:nth-child(2)::before { background: #10b981; }
    .stat-card:nth-child(3)::before { background: #f59e0b; }
    .stat-card:nth-child(4)::before { background: #8b5cf6; }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    .stat-card:first-child:hover { background: #ebf5ff; border-color: rgba(59, 130, 246, 0.2); }
    .stat-card:nth-child(2):hover { background: #f6fcf9; border-color: rgba(16, 185, 129, 0.2); }
    .stat-card:nth-child(3):hover { background: #fffbeb; border-color: rgba(245, 158, 11, 0.2); }
    .stat-card:nth-child(4):hover { background: #faf5ff; border-color: rgba(139, 92, 246, 0.2); }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .stat-icon-wrapper {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
        transition: all 0.25s ease;
    }
    .icon-total { background: #ebf5ff; color: #3b82f6; }
    .icon-new { background: #e6f7ed; color: #10b981; }
    .icon-returning { background: #fffbeb; color: #f59e0b; }
    .icon-spent { background: #f3e8ff; color: #8b5cf6; }

    .stat-card:hover .stat-icon-wrapper {
        transform: scale(1.1);
    }

    .stat-trend {
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 20px;
    }
    .stat-trend.trend-up {
        background: #e6f7ed;
        color: #10b981;
    }
    .stat-trend.trend-down {
        background: #fee2e2;
        color: #ef4444;
    }
    .stat-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-main);
        margin-top: 6px;
        line-height: 1.2;
    }

    .dashboard-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        padding: 24px;
        margin-top: 24px;
    }
    .card-header-styled {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 16px;
        margin-bottom: 20px;
    }
    .card-title-styled {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .chart-container-time {
        position: relative;
        height: 280px;
        width: 100%;
    }

    /* Detailed Table */
    .detail-table-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        padding: 24px;
        margin-top: 24px;
        overflow: hidden;
    }
    .pl-table-scroll {
        overflow-x: auto;
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    .orders-table th {
        background: #fafbfc;
        padding: 12px 16px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        text-align: left;
    }
    .orders-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        color: var(--text-main);
        vertical-align: middle;
    }
    .orders-table tbody tr:last-child td {
        border-bottom: none;
    }
    .orders-table tbody tr {
        transition: all 0.15s ease;
    }
    .orders-table tbody tr:hover {
        background: #fff8f3;
    }

    .rank-position {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .rank-position.is-top {
        background: #fff4ec;
        color: var(--primary);
        border: 1px solid rgba(255, 120, 45, 0.25);
    }

    /* Tỷ trọng chi tiêu: thanh bar so với khách đứng đầu + số % trên tổng kỳ */
    .share-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .share-track {
        flex: 1;
        height: 6px;
        min-width: 48px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }
    .share-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--primary);
    }
    .share-text {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-muted);
        min-width: 42px;
        text-align: right;
        white-space: nowrap;
    }

    /* Pagination Styles */
    .pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 12px;
    }
    .pagination-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pagination-buttons .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-main);
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .pagination-buttons .page-btn:hover:not(:disabled) {
        border-color: var(--primary);
        color: var(--primary);
        background: #fff4ec;
    }
    .pagination-buttons .page-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }
    .pagination-buttons .page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1 style="font-size: 1.8rem; font-weight: 800;">Thống kê <span style="color: var(--primary);">Khách hàng</span></h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Xem thông tin tăng trưởng số lượng tài khoản và danh sách thống kê khách hàng.</p>
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
        <a class="btn-export" id="export-btn" href="{{ route('admin.reports.export', 'customers') }}?period=30days"
           title="Xuất báo cáo ra Excel">
            <i class="fa-solid fa-file-excel"></i>
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-total">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="stat-label">Tổng khách hàng đăng ký</div>
        <div class="stat-value" id="customers-val">0</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-new">
                <i class="fa-solid fa-user-plus"></i>
            </div>
        </div>
        <div class="stat-label">Khách hàng mới</div>
        <div class="stat-value" id="new-val">0</div>
    </div>
</div>

<!-- Customer Registration Trend Chart -->
<div class="dashboard-card">
    <div class="card-header-styled">
        <span class="card-title-styled">Biểu đồ tăng trưởng khách hàng</span>
    </div>
    <div class="chart-container-time">
        <canvas id="customer-trend-chart"></canvas>
    </div>
</div>

<!-- Thống kê khách hàng -->
<div class="detail-table-card">
    <div class="card-header-styled">
        <span class="card-title-styled"><i class="fa-solid fa-users" style="color: var(--primary); margin-right: 6px;"></i> Thống kê khách hàng</span>
        <span id="customer-total-count" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;"></span>
    </div>
    <div class="pl-table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 32%;">KHÁCH HÀNG</th>
                    <th style="width: 30%;">EMAIL</th>
                    <th style="width: 15%;">SỐ ĐƠN MUA</th>
                    <th style="width: 15%;">TỔNG CHI TIÊU</th>
                </tr>
            </thead>
            <tbody id="customers-table-body">
                <!-- Loaded dynamically via JS -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="pagination-footer">
        <div id="pagination-info" style="font-size: 0.88rem; color: var(--text-muted); font-weight: 500;">
            Hiển thị 0 trên 0 khách hàng
        </div>
        <div id="pagination-controls" class="pagination-buttons">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Real data from database passed from controller
        const mockData = @json($periods);

        let trendChartInstance = null;

        function setTrend(id, trend) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('trend-up', 'trend-down');
            el.classList.add(trend.up ? 'trend-up' : 'trend-down');
            const icon = trend.up ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
            el.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${trend.pct}</span>`;
        }

        // Render customer trend chart
        function renderTrendChart(chartData) {
            const ctx = document.getElementById('customer-trend-chart').getContext('2d');
            if (trendChartInstance) {
                trendChartInstance.destroy();
            }

            const labels = chartData.map(d => d.label);
            const values = chartData.map(d => d.value);

            const gradient = ctx.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.005)');

            trendChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Khách đăng ký mới',
                        data: values,
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { family: 'Outfit, Inter, sans-serif', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' Đăng ký: ' + Number(context.raw).toLocaleString('vi-VN') + ' thành viên';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Outfit, Inter, sans-serif', size: 10, weight: '500' },
                                color: '#64748b'
                            }
                        },
                        y: {
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                font: { family: 'Outfit, Inter, sans-serif', size: 10, weight: '500' },
                                color: '#64748b',
                                stepSize: 1,
                                callback: function(value) {
                                    return Number(value).toLocaleString('vi-VN');
                                }
                            }
                        }
                    }
                }
            });
        }

        let currentFilterCustomers = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        function renderTablePage(page) {
            currentPage = page;
            const tableBody = document.getElementById('customers-table-body');
            const totalCountEl = document.getElementById('customer-total-count');
            const infoEl = document.getElementById('pagination-info');
            const controlsEl = document.getElementById('pagination-controls');

            tableBody.innerHTML = '';

            const total = currentFilterCustomers.length;
            if (totalCountEl) totalCountEl.innerText = `(Tổng ${total} khách hàng)`;

            if (total === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                            Chưa có dữ liệu khách hàng trong kỳ này.
                        </td>
                    </tr>
                `;
                if (infoEl) infoEl.innerHTML = 'Hiển thị <strong>0</strong> trên <strong>0</strong> khách hàng';
                if (controlsEl) controlsEl.innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(total / itemsPerPage);
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, total);
            const pageItems = currentFilterCustomers.slice(startIndex, endIndex);

            pageItems.forEach(cust => {
                const isNoOrders = cust.count === 0;
                tableBody.innerHTML += `
                    <tr>
                        <td><span class="rank-position ${cust.position <= 3 && cust.totalSpentRaw > 0 ? 'is-top' : ''}">${cust.position}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background-color: #fff4ec; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid rgba(255, 120, 45, 0.15);">
                                    ${(cust.name || 'C').charAt(0).toUpperCase()}
                                </div>
                                <strong style="color: var(--text-main); font-weight: 700;">${cust.name}</strong>
                            </div>
                        </td>
                        <td style="color: var(--text-muted);">${cust.email || 'N/A'}</td>
                        <td style="font-weight: 600; color: ${isNoOrders ? 'var(--text-muted)' : 'var(--text-main)'};">${cust.count} đơn</td>
                        <td style="font-weight: 700; color: ${isNoOrders ? 'var(--text-muted)' : 'var(--primary)'};">${cust.totalSpent}</td>
                    </tr>
                `;
            });

            // Update Pagination Info
            if (infoEl) infoEl.innerHTML = `Hiển thị <strong>${startIndex + 1} - ${endIndex}</strong> trên <strong>${total}</strong> khách hàng`;

            // Render Pagination Buttons
            if (controlsEl) {
                let buttonsHtml = '';
                
                // Previous Button
                buttonsHtml += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="window.goToCustomerPage(${currentPage - 1})"><i class="fa-solid fa-chevron-left"></i></button>`;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (totalPages <= 7 || i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
                        buttonsHtml += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="window.goToCustomerPage(${i})">${i}</button>`;
                    } else if (i === 2 && currentPage > 3) {
                        buttonsHtml += `<span style="padding: 0 4px; align-self: center; color: var(--text-muted);">...</span>`;
                    } else if (i === totalPages - 1 && currentPage < totalPages - 2) {
                        buttonsHtml += `<span style="padding: 0 4px; align-self: center; color: var(--text-muted);">...</span>`;
                    }
                }

                // Next Button
                buttonsHtml += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="window.goToCustomerPage(${currentPage + 1})"><i class="fa-solid fa-chevron-right"></i></button>`;

                controlsEl.innerHTML = buttonsHtml;
            }
        }

        window.goToCustomerPage = function(page) {
            renderTablePage(page);
        };

        // Render page elements
        function updatePage(filter) {
            const data = mockData[filter];
            if (!data) return;

            // 1. Update text values
            document.getElementById('customers-val').innerText = data.total;
            document.getElementById('new-val').innerText = data.new;

            // 2. Render customer growth chart
            renderTrendChart(data.chart);

            // 4. Render customers table with pagination
            currentFilterCustomers = data.customers || [];
            renderTablePage(1);
        }

        // Handle filter dropdown clicks
        filterOptions.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                filterOptions.forEach(o => o.classList.remove('active'));
                this.classList.add('active');

                const val = this.getAttribute('data-value');
                filterLabel.innerText = this.innerText.toUpperCase();
                updatePage(val);
                syncExportLink(val);
                filterMenu.style.display = 'none';
            });
        });

        // Trigger initial page render
        // Nút xuất Excel luôn theo đúng kỳ đang xem.
        function syncExportLink(period) {
            const btn = document.getElementById('export-btn');
            if (btn) btn.href = btn.href.split('?')[0] + '?period=' + period;
        }

        updatePage('30days');
        syncExportLink('30days');
    });
</script>
@endsection
