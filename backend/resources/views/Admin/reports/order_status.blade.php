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
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
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
        background: var(--primary);
        border-radius: 4px 0 0 4px;
    }
    .stat-card:nth-child(2)::before { background: #10b981; }
    .stat-card:nth-child(3)::before { background: #f59e0b; }
    .stat-card:nth-child(4)::before { background: #ef4444; }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    .stat-card:first-child:hover { background: #fffdfa; border-color: rgba(255, 120, 45, 0.2); }
    .stat-card:nth-child(2):hover { background: #f6fcf9; border-color: rgba(16, 185, 129, 0.2); }
    .stat-card:nth-child(3):hover { background: #fffbeb; border-color: rgba(245, 158, 11, 0.2); }
    .stat-card:nth-child(4):hover { background: #fdf2f2; border-color: rgba(239, 68, 68, 0.2); }

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
    .icon-total { background: #fff4ec; color: var(--primary); }
    .icon-completed { background: #e6f7ed; color: #10b981; }
    .icon-pending { background: #fffbeb; color: #f59e0b; }
    .icon-cancelled { background: #fdf2f2; color: #ef4444; }

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

    /* Grid layout for charts */
    .grid-order-split {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr;
        gap: 24px;
        margin-top: 24px;
    }
    @media (max-width: 1100px) {
        .grid-order-split {
            grid-template-columns: 1fr;
        }
    }

    .dashboard-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        padding: 24px;
        margin-top: 0;
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

    .chart-container-category {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }
    .category-canvas-wrapper {
        position: relative;
        height: 180px;
        width: 180px;
    }

    /* Status indicators list */
    .status-legend-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }
    .legend-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 8px;
        border-bottom: 1px dashed var(--border-color);
    }
    .legend-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .legend-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .legend-color {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .legend-name {
        font-weight: 600;
        font-size: 0.86rem;
        color: var(--text-main);
    }
    .legend-percentage {
        font-size: 0.72rem;
        color: var(--text-muted);
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
    }
    .legend-value {
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--text-main);
        white-space: nowrap;
    }

    /* Detail Table */
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

    .badge-status {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: inline-block;
        white-space: nowrap;
    }
    .badge-completed { background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.15); }
    .badge-shipping { background: rgba(59, 130, 246, 0.08); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.15); }
    .badge-pending { background: rgba(245, 158, 11, 0.08); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.15); }
    .badge-cancelled { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15); }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1 style="font-size: 1.8rem; font-weight: 800;">Trạng thái <span style="color: var(--primary);">Đơn hàng</span></h1>
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
            <div class="stat-icon-wrapper icon-total">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="stat-trend trend-up" id="orders-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+0.0%</span>
            </div>
        </div>
        <div class="stat-label">Tổng đơn hàng</div>
        <div class="stat-value" id="orders-val">0</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-completed">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-trend trend-up" id="completed-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+0.0%</span>
            </div>
        </div>
        <div class="stat-label">Đơn hoàn tất</div>
        <div class="stat-value" id="completed-val">0</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-pending">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div class="stat-trend trend-down" id="pending-trend">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>-0.0%</span>
            </div>
        </div>
        <div class="stat-label">Đang xử lý / Đang giao</div>
        <div class="stat-value" id="pending-val">0</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-cancelled">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div class="stat-trend trend-down" id="cancelled-trend">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>-0.0%</span>
            </div>
        </div>
        <div class="stat-label">Đơn đã hủy</div>
        <div class="stat-value" id="cancelled-val">0</div>
    </div>
</div>

<div class="grid-order-split">
    <!-- Order Trend Line Chart (Visualizes order volume over time) -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Biểu đồ xu hướng đơn hàng</span>
        </div>
        <div class="chart-container-time">
            <canvas id="order-trend-chart"></canvas>
        </div>
    </div>

    <!-- Doughnut Chart showing Status Distribution -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Biểu đồ phân bố trạng thái</span>
        </div>
        <div class="chart-container-category">
            <div class="category-canvas-wrapper">
                <canvas id="order-status-chart"></canvas>
            </div>
            <div class="status-legend-list" id="status-legend-list">
                <!-- Dynanically populated legend -->
            </div>
        </div>
    </div>
</div>

<!-- Details Table (Displays structured evaluation data) -->
<div class="detail-table-card">
    <div class="card-header-styled">
        <span class="card-title-styled"><i class="fa-solid fa-file-invoice-dollar" style="color: var(--primary); margin-right: 6px;"></i> Chi tiết tỷ lệ hoàn thành & Tốc độ xử lý</span>
    </div>
    <div class="pl-table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 25%;">TRẠNG THÁI đơn hàng</th>
                    <th style="width: 20%;">SỐ LƯỢNG ĐƠN</th>
                    <th style="width: 20%;">TỶ LỆ PHẦN TRĂM</th>
                    <th style="width: 35%;">ĐÁNH GIÁ xu hướng</th>
                </tr>
            </thead>
            <tbody id="status-table-body">
                <!-- Loaded dynamically via JS -->
            </tbody>
        </table>
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

        // Mock data dictionary with charts coordinate values
        // Số liệu thật do ReportController tính, khóa theo kỳ: today / 7days / 30days
        const periodData = @json($periods);

        let trendChartInstance = null;
        let statusChartInstance = null;

        function setTrend(id, trend) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('trend-up', 'trend-down');
            el.classList.add(trend.up ? 'trend-up' : 'trend-down');
            const icon = trend.up ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
            el.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${trend.pct}</span>`;
        }

        // Render line trend chart
        function renderTrendChart(chartData) {
            const ctx = document.getElementById('order-trend-chart').getContext('2d');
            if (trendChartInstance) {
                trendChartInstance.destroy();
            }

            const labels = chartData.map(d => d.label);
            const values = chartData.map(d => d.value);

            const gradient = ctx.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(255, 120, 45, 0.25)');
            gradient.addColorStop(1, 'rgba(255, 120, 45, 0.005)');

            trendChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số lượng đơn hàng',
                        data: values,
                        backgroundColor: '#ff782d',
                        borderColor: '#ff782d',
                        borderRadius: 6,
                        maxBarThickness: 46,
                        borderWidth: 0
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
                                    return ' Đơn hàng: ' + Number(context.raw).toLocaleString('vi-VN') + ' đơn';
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

        // Render Doughnut distribution chart
        function renderStatusChart(statuses) {
            const ctx = document.getElementById('order-status-chart').getContext('2d');
            if (statusChartInstance) {
                statusChartInstance.destroy();
            }

            const labels = statuses.map(s => s.name);
            const values = statuses.map(s => s.count);
            const colors = statuses.map(s => s.color);

            statusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { family: 'Outfit, Inter, sans-serif', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + Number(context.raw).toLocaleString('vi-VN') + ' đơn';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Update page elements dynamically
        function updatePage(filter) {
            const data = periodData[filter];
            if (!data) return;

            // 1. Update stats text values
            document.getElementById('orders-val').innerText = data.total;
            document.getElementById('completed-val').innerText = data.completed;
            document.getElementById('pending-val').innerText = data.pending;
            document.getElementById('cancelled-val').innerText = data.cancelled;

            // 2. Update trend elements
            setTrend('orders-trend', data.totalTrend);
            setTrend('completed-trend', data.completedTrend);
            setTrend('pending-trend', data.pendingTrend);
            setTrend('cancelled-trend', data.cancelledTrend);

            // 3. Render status legend list
            const legendList = document.getElementById('status-legend-list');
            legendList.innerHTML = '';
            data.statuses.forEach(status => {
                legendList.innerHTML += `
                    <div class="legend-item">
                        <div class="legend-left">
                            <span class="legend-color" style="background-color: ${status.color};"></span>
                            <span class="legend-name">${status.name}</span>
                            <span class="legend-percentage">${status.percentage}</span>
                        </div>
                        <span class="legend-value">${status.count} đơn</span>
                    </div>
                `;
            });

            // 4. Render ChartJS Line Chart
            renderTrendChart(data.chart);

            // 5. Render ChartJS Doughnut Chart
            renderStatusChart(data.statuses);

            // 6. Render Details Table Rows
            const tableBody = document.getElementById('status-table-body');
            tableBody.innerHTML = '';
            data.statuses.forEach(status => {
                const noteColor = status.noteUp ? '#10b981' : '#ef4444';
                const noteIcon = status.noteUp ? 'fa-circle-arrow-up' : 'fa-circle-exclamation';

                tableBody.innerHTML += `
                    <tr>
                        <td><span class="badge-status ${status.badge}">${status.name.toUpperCase()}</span></td>
                        <td style="font-weight: 700; color: var(--text-main);">${Number(status.count).toLocaleString('vi-VN')} đơn</td>
                        <td style="font-weight: 700; color: var(--primary);">${status.percentage}</td>
                        <td style="color: ${noteColor}; font-weight: 600; font-size: 0.86rem;">
                            <i class="fa-solid ${noteIcon}" style="margin-right: 4px;"></i> ${status.note}
                        </td>
                    </tr>
                `;
            });
        }

        // Handle dropdown selection click
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

        // Trigger initial page render (30 Days default)
        updatePage('30days');
    });
</script>
@endsection
