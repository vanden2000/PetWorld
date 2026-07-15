@extends('admin.layouts.app')

@section('title', 'Sản phẩm Bán chạy')

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
    .best-seller-rank {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .rank-1 { background-color: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
    .rank-2 { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
    .rank-3 { background-color: #ffebd8; color: #ff782d; border: 1px solid #ffd0a8; }
    .rank-other { background-color: #ffffff; color: var(--text-muted); border: 1px solid var(--border-color); }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Sản phẩm Bán chạy</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Danh sách các sản phẩm bán chạy nhất, sản lượng bán ra và tổng giá trị mang lại cho cửa hàng.</p>
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
                <i class="fa-solid fa-cubes"></i>
            </div>
            <div class="stat-trend trend-up" id="sold-trend">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>+10.2%</span>
            </div>
        </div>
        <div class="stat-label">Tổng sản phẩm đã bán</div>
        <div class="stat-value" id="sold-val">5,820 SP</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue" style="background-color: var(--success-light); color: var(--success);">
                <i class="fa-solid fa-crown"></i>
            </div>
        </div>
        <div class="stat-label">Sản phẩm đầu bảng</div>
        <div class="stat-value" id="top-product-val" style="font-size: 1.1rem; line-height: 1.4; font-weight: 700; margin-top: 10px;">Royal Canin Mother & Babycat</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-aov" style="background-color: var(--warning-light); color: var(--warning);">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>
        <div class="stat-label">Danh mục bán chạy nhất</div>
        <div class="stat-value" id="top-category-val">Thức ăn hạt</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-conversion" style="background-color: var(--purple-light); color: var(--purple);">
                <i class="fa-solid fa-copyright"></i>
            </div>
        </div>
        <div class="stat-label">Thương hiệu bán chạy nhất</div>
        <div class="stat-value" id="top-brand-val">Royal Canin</div>
    </div>
</div>

<!-- Table Card -->
<div class="dashboard-card" style="margin-top: 24px;">
    <div class="card-header-styled">
        <span class="card-title-styled">Danh sách sản phẩm bán chạy nhất</span>
    </div>
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 5%">HẠNG</th>
                    <th style="width: 35%">SẢN PHẨM</th>
                    <th style="width: 15%">DANH MỤC</th>
                    <th style="width: 15%">THƯƠNG HIỆU</th>
                    <th style="width: 12%">SỐ LƯỢNG BÁN</th>
                    <th style="width: 18%">TỔNG DOANH THU</th>
                </tr>
            </thead>
            <tbody id="sellers-table-body">
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
                totalSold: "185 sản phẩm",
                soldTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+14.2%</span>",
                topProduct: "Pate Whiskas 12 gói",
                topCategory: "Pate / Thức ăn ướt",
                topBrand: "Whiskas",
                sellers: [
                    { rank: 1, name: "Pate Whiskas 12 gói", cat: "Pate / Thức ăn ướt", brand: "Whiskas", units: 48, revenue: "720.000đ", image: "snack.jpg" },
                    { rank: 2, name: "Royal Canin Mother & Babycat", cat: "Thức ăn hạt", brand: "Royal Canin", units: 32, revenue: "14.500.000đ", image: "thuc-an-hat.jpg" },
                    { rank: 3, name: "Xương gặm KONG Classic Red", cat: "Đồ chơi", brand: "KONG", units: 15, revenue: "4.800.000đ", image: "do-choi.jpg" }
                ]
            },
            "7days": {
                totalSold: "1,240 sản phẩm",
                soldTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+9.8%</span>",
                topProduct: "Royal Canin Mother & Babycat",
                topCategory: "Thức ăn hạt",
                topBrand: "Royal Canin",
                sellers: [
                    { rank: 1, name: "Royal Canin Mother & Babycat", cat: "Thức ăn hạt", brand: "Royal Canin", units: 148, revenue: "64.800.000đ", image: "thuc-an-hat.jpg" },
                    { rank: 2, name: "Máy lọc nước tự động PETKIT", cat: "Phụ kiện", brand: "PETKIT", units: 82, revenue: "72.980.000đ", image: "phu-kien.jpg" },
                    { rank: 3, name: "Pate Whiskas 12 gói", cat: "Pate / Thức ăn ướt", brand: "Whiskas", units: 120, revenue: "1.800.000đ", image: "snack.jpg" },
                    { rank: 4, name: "Xương gặm KONG Classic Red", cat: "Đồ chơi", brand: "KONG", units: 45, revenue: "14.400.000đ", image: "do-choi.jpg" }
                ]
            },
            "30days": {
                totalSold: "5,820 sản phẩm",
                soldTrend: "<i class='fa-solid fa-arrow-trend-up'></i> <span>+10.2%</span>",
                topProduct: "Royal Canin Mother & Babycat",
                topCategory: "Thức ăn hạt",
                topBrand: "Royal Canin",
                sellers: [
                    { rank: 1, name: "Royal Canin Mother & Babycat", cat: "Thức ăn hạt", brand: "Royal Canin", units: 612, revenue: "275.400.000đ", image: "thuc-an-hat.jpg" },
                    { rank: 2, name: "Máy lọc nước tự động PETKIT", cat: "Phụ kiện", brand: "PETKIT", units: 312, revenue: "277.680.000đ", image: "phu-kien.jpg" },
                    { rank: 3, name: "Xương gặm KONG Classic Red", cat: "Đồ chơi", brand: "KONG", units: 549, revenue: "175.680.000đ", image: "do-choi.jpg" },
                    { rank: 4, name: "Đệm nằm nhung cao cấp", cat: "Snack / Vật dụng", brand: "Khác", units: 420, revenue: "504.000.000đ", image: "snack.jpg" }
                ]
            }
        };

        // Render page function
        function updatePage(filter) {
            const data = mockData[filter];

            // Update stats
            document.getElementById('sold-val').innerText = data.totalSold;
            document.getElementById('sold-trend').innerHTML = data.soldTrend;
            document.getElementById('top-product-val').innerText = data.topProduct;
            document.getElementById('top-category-val').innerText = data.topCategory;
            document.getElementById('top-brand-val').innerText = data.topBrand;

            // Render table
            const tableBody = document.getElementById('sellers-table-body');
            tableBody.innerHTML = '';
            data.sellers.forEach(prod => {
                let rankClass = 'rank-other';
                if (prod.rank === 1) rankClass = 'rank-1';
                if (prod.rank === 2) rankClass = 'rank-2';
                if (prod.rank === 3) rankClass = 'rank-3';

                tableBody.innerHTML += `
                    <tr>
                        <td>
                            <div class="best-seller-rank ${rankClass}">${prod.rank}</div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="/image/${prod.image}" alt="${prod.name}" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover; border: 1px solid var(--border-color);" onerror="this.src='https://images.unsplash.com/photo-1583337130417-3346a1be7dee?q=80&w=120&auto=format&fit=crop'">
                                <strong style="color: var(--text-main);">${prod.name}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="badge-count" style="background-color: var(--primary-light); color: var(--primary); padding: 4px 8px; border-radius: 4px;">${prod.cat}</span>
                        </td>
                        <td><span style="font-weight: 500;">${prod.brand}</span></td>
                        <td style="font-weight: 700; color: var(--text-main);">${prod.units} SP</td>
                        <td style="font-weight: 700; color: var(--success);">${prod.revenue}</td>
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
