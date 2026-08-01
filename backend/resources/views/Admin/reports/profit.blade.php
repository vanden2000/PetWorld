@extends('admin.layouts.app')

@section('title', 'Thống kê Lợi nhuận')

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
        background: #3b82f6;
        border-radius: 4px 0 0 4px;
    }
    .stat-card:nth-child(2)::before { background: #f59e0b; }
    .stat-card:nth-child(3)::before { background: #10b981; }
    .stat-card:nth-child(4)::before { background: #8b5cf6; }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    .stat-card:first-child:hover { background: #ebf5ff; border-color: rgba(59, 130, 246, 0.2); }
    .stat-card:nth-child(2):hover { background: #fffbeb; border-color: rgba(245, 158, 11, 0.2); }
    .stat-card:nth-child(3):hover { background: #f6fcf9; border-color: rgba(16, 185, 129, 0.2); }
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
    .icon-revenue { background: #ebf5ff; color: #3b82f6; }
    .icon-cost { background: #fffbeb; color: #f59e0b; }
    .icon-profit { background: #e6f7ed; color: #10b981; }
    .icon-margin { background: #f3e8ff; color: #8b5cf6; }

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
    .revenue-detail-grid {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr;
        gap: 24px;
        margin-top: 24px;
    }
    @media (max-width: 1100px) {
        .revenue-detail-grid {
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

    /* Category list items */
    .category-legend-list {
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
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1 style="font-size: 1.8rem; font-weight: 800;">Thống kê <span style="color: var(--primary);">Lợi nhuận</span></h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Xem báo cáo doanh thu, ước tính giá vốn nhập hàng, lợi nhuận gộp và tỷ suất biên lợi nhuận của PetWorld.</p>
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
                <span>+0.0%</span>
            </div>
        </div>
        <div class="stat-label">Tổng doanh thu thực tế</div>
        <div class="stat-value" id="revenue-val">0đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-cost">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-trend trend-up" id="cost-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+0.0%</span>
            </div>
        </div>
        <div class="stat-label">Giá vốn ước tính</div>
        <div class="stat-value" id="cost-val">0đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-profit">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
            <div class="stat-trend trend-up" id="profit-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+0.0%</span>
            </div>
        </div>
        <div class="stat-label">Lợi nhuận gộp thực tế</div>
        <div class="stat-value" id="profit-val">0đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-margin">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div class="stat-trend trend-up" id="margin-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+0.0%</span>
            </div>
        </div>
        <div class="stat-label">Biên lợi nhuận gộp</div>
        <div class="stat-value" id="margin-val">0%</div>
    </div>
</div>

<div class="revenue-detail-grid">
    <!-- Revenue & Profit Comparison Double Line Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Tương quan Doanh thu & Lợi nhuận</span>
        </div>
        <div class="chart-container-time">
            <canvas id="profit-comparison-chart"></canvas>
            <div id="time-chart-empty" style="display:none; position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.9rem;">
                Chưa có dữ liệu thanh toán trong khoảng thời gian này.
            </div>
        </div>
    </div>

    <!-- Profit Contribution by Category Doughnut Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Cơ cấu lợi nhuận theo danh mục</span>
        </div>
        <div class="chart-container-category">
            <div class="category-canvas-wrapper">
                <canvas id="category-profit-chart"></canvas>
                <div id="category-chart-empty" style="display:none; position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.85rem; text-align:center;">
                    Chưa có dữ liệu.
                </div>
            </div>
            <div class="category-legend-list" id="category-list">
                <!-- Dynamically loaded legend -->
            </div>
        </div>
    </div>
</div>

<!-- Detailed Data Table (No extra white space) -->
<div class="detail-table-card">
    <div class="card-header-styled">
        <span class="card-title-styled"><i class="fa-solid fa-file-invoice-dollar" style="color: var(--primary); margin-right: 6px;"></i> Chi tiết dòng tiền & Hiệu quả kinh doanh</span>
    </div>
    <div class="pl-table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Thời gian</th>
                    <th style="width: 20%;">Doanh thu thực tế</th>
                    <th style="width: 20%;">Giá vốn ước tính</th>
                    <th style="width: 20%;">Lợi nhuận gộp</th>
                    <th style="width: 20%;">Biên lợi nhuận gộp</th>
                </tr>
            </thead>
            <tbody id="detail-table-body">
                <!-- Dynamically populated -->
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

        // Real Data from Controller
        const periodData = @json($periods);

        let comparisonChartInstance = null;
        let categoryChartInstance = null;

        function trendHtml(t) {
            const icon = t.up ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
            return `<i class="fa-solid ${icon}"></i> <span>${t.pct}</span>`;
        }

        function setTrend(id, t) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('trend-up', 'trend-down');
            el.classList.add(t.up ? 'trend-up' : 'trend-down');
            el.innerHTML = trendHtml(t);
        }

        function formatMoney(n) {
            return Number(n || 0).toLocaleString('vi-VN') + 'đ';
        }

        // Render double line comparison chart
        function renderComparisonChart(chartData) {
            const canvas = document.getElementById('profit-comparison-chart');
            const emptyEl = document.getElementById('time-chart-empty');
            
            if (comparisonChartInstance) {
                comparisonChartInstance.destroy();
            }

            if (!chartData || chartData.length === 0) {
                canvas.style.display = 'none';
                emptyEl.style.display = 'flex';
                return;
            }

            canvas.style.display = 'block';
            emptyEl.style.display = 'none';

            const ctx = canvas.getContext('2d');
            const labels = chartData.map(d => d.label);
            const revenues = chartData.map(d => d.revenue);
            const profits = chartData.map(d => d.profit);

            comparisonChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Doanh thu',
                            data: revenues,
                            borderColor: '#ff782d',
                            borderWidth: 3,
                            backgroundColor: 'transparent',
                            tension: 0.35,
                            pointBackgroundColor: '#ff782d',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Lợi nhuận gộp',
                            data: profits,
                            borderColor: '#10b981',
                            borderWidth: 3,
                            backgroundColor: 'transparent',
                            tension: 0.35,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                font: { family: 'Outfit, Inter, sans-serif', size: 11, weight: 'bold' },
                                color: '#475569',
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { family: 'Outfit, Inter, sans-serif', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + formatMoney(context.raw);
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
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000) + 'Tr';
                                    } else if (value >= 1000) {
                                        return (value / 1000) + 'k';
                                    }
                                    return value + 'đ';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render category profit doughnut chart
        function renderCategoryChart(categories) {
            const canvas = document.getElementById('category-profit-chart');
            const emptyEl = document.getElementById('category-chart-empty');
            
            if (categoryChartInstance) {
                categoryChartInstance.destroy();
            }

            if (!categories || categories.length === 0) {
                canvas.style.display = 'none';
                emptyEl.style.display = 'flex';
                return;
            }

            canvas.style.display = 'block';
            emptyEl.style.display = 'none';

            const ctx = canvas.getContext('2d');
            const labels = categories.map(c => c.name);
            const values = categories.map(c => {
                return parseFloat(c.profit.replace(/[^\d]/g, '')) || 0;
            });
            const colors = categories.map(c => c.color);

            categoryChartInstance = new Chart(ctx, {
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
                                    return ' Lợi nhuận ' + context.label + ': ' + formatMoney(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render page data
        function updatePage(filter) {
            const data = periodData[filter];
            if (!data) return;
            
            // 1. Update text values
            document.getElementById('revenue-val').innerText = data.revenue;
            document.getElementById('cost-val').innerText = data.cost;
            document.getElementById('profit-val').innerText = data.profit;
            document.getElementById('margin-val').innerText = data.margin;

            // 2. Update trends
            setTrend('revenue-trend', data.trends.revenue);
            setTrend('cost-trend', data.trends.cost);
            setTrend('profit-trend', data.trends.profit);
            setTrend('margin-trend', data.trends.margin);

            // 3. Render category profit legend list
            const categoryList = document.getElementById('category-list');
            categoryList.innerHTML = '';
            if (!data.categories.length) {
                categoryList.innerHTML = '<div style="color: var(--text-muted); font-size: 0.85rem; text-align:center; padding:10px 0;">Chưa có dữ liệu danh mục trong kỳ này.</div>';
            } else {
                data.categories.forEach(cat => {
                    categoryList.innerHTML += `
                        <div class="legend-item">
                            <div class="legend-left">
                                <span class="legend-color" style="background-color: ${cat.color};"></span>
                                <span class="legend-name">${cat.name}</span>
                                <span class="legend-percentage">${cat.percentage}</span>
                            </div>
                            <span class="legend-value" title="Doanh thu: ${cat.revenue} | Giá vốn: ${cat.cost}">${cat.profit}</span>
                        </div>
                    `;
                });
            }

            // 4. Render ChartJS comparison chart
            renderComparisonChart(data.chart);

            // 5. Render ChartJS doughnut chart
            renderCategoryChart(data.categories);

            // 6. Render Detailed Data Table
            const tableBody = document.getElementById('detail-table-body');
            tableBody.innerHTML = '';
            if (!data.table || data.table.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 32px; color: var(--text-muted);">Không có dữ liệu chi tiết trong khoảng thời gian này.</td></tr>';
            } else {
                data.table.forEach(row => {
                    tableBody.innerHTML += `
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);">${row.time}</td>
                            <td style="color: #64748b; font-weight: 500;">${row.revenue}</td>
                            <td style="color: #f59e0b; font-weight: 500;">${row.cost}</td>
                            <td style="font-weight: 700; color: #10b981;">${row.profit}</td>
                            <td style="font-weight: 700; color: var(--primary);">${row.margin}</td>
                        </tr>
                    `;
                });
            }
        }

        // Handle dropdown selection clicks
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

        // Trigger initial page render
        updatePage('30days');
    });
</script>
@endsection
