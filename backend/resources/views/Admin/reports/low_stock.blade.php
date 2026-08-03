@extends('admin.layouts.app')

@section('title', 'Sản phẩm Sắp hết hàng')

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
        background: #f59e0b;
        border-radius: 4px 0 0 4px;
    }
    .stat-card:nth-child(2)::before { background: #ef4444; }
    .stat-card:nth-child(3)::before { background: #3b82f6; }
    .stat-card:nth-child(4)::before { background: #10b981; }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    .stat-card:first-child:hover { background: #fffbeb; border-color: rgba(245, 158, 11, 0.2); }
    .stat-card:nth-child(2):hover { background: #fdf2f2; border-color: rgba(239, 68, 68, 0.2); }
    .stat-card:nth-child(3):hover { background: #ebf5ff; border-color: rgba(59, 130, 246, 0.2); }
    .stat-card:nth-child(4):hover { background: #f6fcf9; border-color: rgba(16, 185, 129, 0.2); }

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
    .icon-warning { background: #fffbeb; color: #f59e0b; }
    .icon-danger { background: #fdf2f2; color: #ef4444; }
    .icon-info { background: #ebf5ff; color: #3b82f6; }
    .icon-success { background: #e6f7ed; color: #10b981; }

    .stat-card:hover .stat-icon-wrapper {
        transform: scale(1.1);
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
        grid-template-columns: 1.2fr 1.8fr;
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
    .badge-cancelled { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15); }
    .badge-pending { background: rgba(245, 158, 11, 0.08); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.15); }
    .badge-completed { background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.15); }

    .btn-action-stock {
        padding: 8px 14px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-action-stock:hover {
        background-color: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(255, 120, 45, 0.2);
    }
    .slug-text {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        color: #475569;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.76rem;
    }

    /* Nhãn biến thể dưới tên sản phẩm ("S / Đỏ", "Hộp / 3kg"…) */
    .variant-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.76rem;
        font-weight: 600;
        background: #fff4ec;
        color: var(--primary);
        border: 1px solid rgba(255, 120, 45, 0.2);
    }
    .variant-chip i {
        font-size: 0.68rem;
    }
    .variant-chip.is-default {
        background: #f1f5f9;
        color: var(--text-muted);
        border-color: var(--border-color);
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1 style="font-size: 1.8rem; font-weight: 800;">Sản phẩm <span style="color: var(--primary);">Sắp hết hàng</span></h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Theo dõi lượng hàng tồn kho của sản phẩm, nhận cảnh báo hết hàng và quản lý nhập kho nhanh chóng.</p>
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
            <div class="stat-icon-wrapper icon-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
        <div class="stat-label">Sản phẩm sắp hết hàng (< 10)</div>
        <div class="stat-value" id="low-stock-val">0 sản phẩm</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-danger">
                <i class="fa-solid fa-skull-crossbones"></i>
            </div>
        </div>
        <div class="stat-label">Sản phẩm hết hàng (0)</div>
        <div class="stat-value" id="out-of-stock-val">0 sản phẩm</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-info">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
        </div>
        <div class="stat-label">Tỷ lệ tồn kho an toàn</div>
        <div class="stat-value" id="safety-rate-val">0%</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-success">
                <i class="fa-solid fa-warehouse"></i>
            </div>
        </div>
        <div class="stat-label">Tổng tồn kho hệ thống</div>
        <div class="stat-value" id="total-stock-val">0 đơn vị</div>
    </div>
</div>

<div class="grid-order-split">
    <!-- Stock Status Breakdown Doughnut Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Tình trạng khả dụng kho hàng</span>
        </div>
        <div class="chart-container-category">
            <div class="category-canvas-wrapper">
                <canvas id="stock-status-chart"></canvas>
            </div>
            <div class="status-legend-list" id="status-legend-list">
                <!-- Dynamically loaded legend -->
            </div>
        </div>
    </div>

    <!-- Low Stock by Category Bar Chart -->
    <div class="dashboard-card">
        <div class="card-header-styled">
            <span class="card-title-styled">Cảnh báo tồn kho theo danh mục</span>
        </div>
        <div class="chart-container-time">
            <canvas id="category-alerts-chart"></canvas>
        </div>
    </div>
</div>

<!-- Detailed Low Stock List (No extra white space) -->
<div class="detail-table-card">
    <div class="card-header-styled">
        <span class="card-title-styled"><i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b; margin-right: 6px;"></i> Danh sách cảnh báo tồn kho</span>
    </div>
    <div class="pl-table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 35%">SẢN PHẨM / PHÂN LOẠI</th>
                    <th style="width: 15%">MÃ SKU</th>
                    <th style="width: 15%">DANH MỤC</th>
                    <th style="width: 12%">TỒN KHO CÒN</th>
                    <th style="width: 13%">TRẠNG THÁI</th>
                    <th style="width: 10%">HÀNH ĐỘNG</th>
                </tr>
            </thead>
            <tbody id="stock-table-body">
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

        // Real data from database passed from controller
        const mockData = @json($periods);

        let statusChartInstance = null;
        let categoryChartInstance = null;

        // Render stock status doughnut chart
        function renderStatusChart(statusBreakdown) {
            const ctx = document.getElementById('stock-status-chart').getContext('2d');
            if (statusChartInstance) {
                statusChartInstance.destroy();
            }

            const labels = statusBreakdown.map(s => s.name);
            const values = statusBreakdown.map(s => s.count);
            const colors = statusBreakdown.map(s => s.color);

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
                                    return ' ' + context.label + ': ' + Number(context.raw).toLocaleString('vi-VN') + ' sản phẩm';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render bar chart for low stock by category
        function renderCategoryChart(categoriesBreakdown) {
            const ctx = document.getElementById('category-alerts-chart').getContext('2d');
            if (categoryChartInstance) {
                categoryChartInstance.destroy();
            }

            categoryChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: categoriesBreakdown.labels,
                    datasets: [{
                        label: 'Số lượng sản phẩm',
                        data: categoriesBreakdown.values,
                        backgroundColor: 'rgba(245, 158, 11, 0.75)',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        hoverBackgroundColor: '#f59e0b'
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
                                    return ' Cảnh báo: ' + Number(context.raw).toLocaleString('vi-VN') + ' SP';
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
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Render page components
        function updatePage(filter) {
            const data = mockData[filter];
            if (!data) return;

            // 1. Update text values
            document.getElementById('low-stock-val').innerText = data.lowStock;
            document.getElementById('out-of-stock-val').innerText = data.outOfStock;
            document.getElementById('safety-rate-val').innerText = data.safety;
            document.getElementById('total-stock-val').innerText = data.total;

            // 2. Render legend list
            const legendList = document.getElementById('status-legend-list');
            legendList.innerHTML = '';
            data.statusBreakdown.forEach(status => {
                legendList.innerHTML += `
                    <div class="legend-item">
                        <div class="legend-left">
                            <span class="legend-color" style="background-color: ${status.color};"></span>
                            <span class="legend-name">${status.name}</span>
                            <span class="legend-percentage">${status.percentage}</span>
                        </div>
                        <span class="legend-value">${status.count} SP</span>
                    </div>
                `;
            });

            // 3. Render ChartJS Doughnut Chart
            renderStatusChart(data.statusBreakdown);

            // 4. Render ChartJS Bar Chart
            renderCategoryChart(data.categoriesBreakdown);

            // 5. Render low stock products table
            const tableBody = document.getElementById('stock-table-body');
            tableBody.innerHTML = '';

            if (!data.items || data.items.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted); font-weight: 500;">
                            Không có sản phẩm nào sắp hết hoặc đã hết hàng.
                        </td>
                    </tr>`;
                return;
            }

            data.items.forEach(item => {
                tableBody.innerHTML += `
                    <tr>
                        <td>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-weight: 700;">${item.name}</strong>
                                <span class="variant-chip ${item.hasVariant ? '' : 'is-default'}">
                                    ${item.hasVariant ? '<i class="fa-solid fa-layer-group"></i>' : ''} ${item.variant}
                                </span>
                            </div>
                        </td>
                        <td><span class="slug-text">${item.sku}</span></td>
                        <td>
                            <span class="badge-category ${item.stock === 0 ? 'badge-pate' : 'badge-food'}">${item.cat}</span>
                        </td>
                        <td style="font-weight: 700; color: ${item.stock === 0 ? '#ef4444' : '#f59e0b'};">${item.stock} SP</td>
                        <td><span class="badge-status ${item.statusClass}">${item.status}</span></td>
                        <td>
                            <button class="btn-action-stock" onclick="alert('Đã gửi yêu cầu nhập thêm sản phẩm ${item.sku}')">Nhập hàng</button>
                        </td>
                    </tr>
                `;
            });
        }

        // Handle dropdown filter click
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
