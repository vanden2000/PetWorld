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

    /* Biểu đồ cột doanh thu theo thời gian — 1 chuỗi, dùng màu chủ đạo (cam) */
    .rvbar { display: flex; align-items: flex-end; gap: 14px; min-height: 262px; padding: 20px 4px 0; overflow-x: auto; }
    .rvbar-col { flex: 1 1 0; min-width: 56px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; cursor: default; }
    .rvbar-value { font-size: 0.72rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px; white-space: nowrap; }
    .rvbar-fill { width: 100%; max-width: 48px; background: var(--primary); border-radius: 5px 5px 0 0; min-height: 4px; transition: height .35s ease, background-color .2s ease; }
    .rvbar-col:hover .rvbar-fill { background: var(--primary-hover); }
    .rvbar-label { margin-top: 10px; font-size: 0.7rem; color: var(--text-muted); text-align: center; white-space: nowrap; }
    .rvbar-empty { margin: auto; color: var(--text-muted); font-size: 0.9rem; padding: 40px 0; }
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
        <div class="stat-label">Tỷ lệ giảm giá TB</div>
        <div class="stat-value" id="margin-val">0%</div>
    </div>
</div>

<div class="revenue-detail-grid">
    <!-- Revenue Table -->
    <div class="dashboard-card" style="margin-top: 0;">
        <div class="card-header-styled">
            <span class="card-title-styled">Doanh thu theo thời gian</span>
        </div>
        <div class="rvbar" id="revenue-bar-chart">
            <!-- Cột doanh thu được vẽ động qua JS -->
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

        // Dữ liệu thật do ReportController tính sẵn cho 3 mốc thời gian.
        const periodData = @json($periods);

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

        // Render function
        function updatePage(filter) {
            const data = periodData[filter];
            if (!data) return;
            
            // Update stats
            document.getElementById('revenue-val').innerText = data.revenue;
            document.getElementById('orders-val').innerText = data.orders;
            document.getElementById('aov-val').innerText = data.aov;
            document.getElementById('margin-val').innerText = data.discountRate;
            setTrend('revenue-trend', data.trends.revenue);
            setTrend('orders-trend', data.trends.orders);
            setTrend('aov-trend', data.trends.aov);
            setTrend('margin-trend', data.trends.discount);

            // Render categories list
            const categoryList = document.getElementById('category-list');
            categoryList.innerHTML = '';
            if (!data.categories.length) {
                categoryList.innerHTML = '<div style="color: var(--text-muted); font-size: 0.9rem;">Chưa có doanh thu theo danh mục trong kỳ này.</div>';
            }
            data.categories.forEach(cat => {
                categoryList.innerHTML += `
                    <div class="legend-row" style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <div class="legend-row-left" style="display: flex; align-items: center; gap: 10px;">
                            <span class="legend-color-indicator" style="width: 12px; height: 12px; border-radius: 50%; background-color: ${cat.color}; display: inline-block;"></span>
                            <span style="font-weight: 600;">${cat.name} (${cat.percentage})</span>
                        </div>
                        <span class="legend-value-percentage" style="font-weight: 700; color: var(--primary);">${cat.val}</span>
                    </div>
                `;
            });

            // Render bar chart (doanh thu thực tế theo thời gian)
            const barChart = document.getElementById('revenue-bar-chart');
            barChart.innerHTML = '';
            if (!data.chart.length) {
                barChart.innerHTML = '<div class="rvbar-empty">Chưa có đơn đã thanh toán trong khoảng thời gian này.</div>';
            } else {
                const maxVal = Math.max(...data.chart.map(d => d.value), 1);
                data.chart.forEach(d => {
                    const h = Math.max(4, Math.round(d.value / maxVal * 190));
                    barChart.innerHTML += `
                        <div class="rvbar-col" title="${d.label}: ${formatMoney(d.value)}">
                            <div class="rvbar-value">${formatMoney(d.value)}</div>
                            <div class="rvbar-fill" style="height: ${h}px;"></div>
                            <div class="rvbar-label">${d.label}</div>
                        </div>
                    `;
                });
            }
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
