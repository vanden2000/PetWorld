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
        background-color: var(--bg-color);
        color: var(--primary);
    }
    .filter-option.active {
        background-color: var(--primary-light);
        color: var(--primary);
        font-weight: 600;
    }
    .btn-action-stock {
        padding: 6px 12px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }
    .btn-action-stock:hover {
        background-color: var(--primary-hover);
    }
</style>
@endsection

@section('content')
<!-- Header Area -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Sản phẩm Sắp hết hàng</h1>
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
            <div class="stat-icon-wrapper icon-aov" style="background-color: var(--warning-light); color: var(--warning);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
        <div class="stat-label">Sản phẩm sắp hết hàng (< 10)</div>
        <div class="stat-value" id="low-stock-val">8 sản phẩm</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-conversion" style="background-color: var(--danger-light); color: var(--danger);">
                <i class="fa-solid fa-skull-crossbones"></i>
            </div>
        </div>
        <div class="stat-label">Sản phẩm hết hàng (0)</div>
        <div class="stat-value" id="out-of-stock-val">2 sản phẩm</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-orders" style="background-color: var(--info-light); color: var(--info);">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
        </div>
        <div class="stat-label">Tỷ lệ tồn kho an toàn</div>
        <div class="stat-value" id="safety-rate-val">94.8%</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrapper icon-revenue" style="background-color: var(--success-light); color: var(--success);">
                <i class="fa-solid fa-warehouse"></i>
            </div>
        </div>
        <div class="stat-label">Tổng tồn kho hệ thống</div>
        <div class="stat-value" id="total-stock-val">1,825 đơn vị</div>
    </div>
</div>

<!-- Table Card -->
<div class="dashboard-card" style="margin-top: 24px;">
    <div class="card-header-styled">
        <span class="card-title-styled">Danh sách cảnh báo tồn kho</span>
    </div>
    <div class="table-container">
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
                lowStock: "10 sản phẩm",
                outOfStock: "3 sản phẩm",
                safety: "92.1%",
                total: "1,790 đơn vị",
                items: [
                    { name: "Pate Royal Canin Mini Puppy", variant: "Lon 195g", sku: "RC-PUP-LON-195G", cat: "Pate / Thức ăn ướt", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Pate Me-O Cá Ngừ", variant: "Lốc 12 túi", sku: "ME-O-CANGU-12", cat: "Pate / Thức ăn ướt", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Xịt khử mùi Bioline", variant: "Chai 300ml", sku: "BIO-XIT-300ML", cat: "Vệ sinh & chăm sóc", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Royal Canin Mini Adult", variant: "Bao 3kg", sku: "RC-MA-BAO-3KG", cat: "Thức ăn hạt", stock: 3, status: "SẮP HẾT", statusClass: "status-pending" },
                    { name: "Dây dắt Trixie Premium", variant: "Kích thước S", sku: "TX-DD-PRE-S", cat: "Phụ kiện", stock: 5, status: "SẮP HẾT", statusClass: "status-pending" },
                    { name: "Bóng Trixie Denta Fun", variant: "Màu Đỏ", sku: "TX-BONG-DF-RD", cat: "Đồ chơi", stock: 8, status: "SẮP HẾT", statusClass: "status-pending" }
                ]
            },
            "7days": {
                lowStock: "9 sản phẩm",
                outOfStock: "2 sản phẩm",
                safety: "93.9%",
                total: "1,810 đơn vị",
                items: [
                    { name: "Pate Royal Canin Mini Puppy", variant: "Lon 195g", sku: "RC-PUP-LON-195G", cat: "Pate / Thức ăn ướt", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Pate Me-O Cá Ngừ", variant: "Lốc 12 túi", sku: "ME-O-CANGU-12", cat: "Pate / Thức ăn ướt", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Royal Canin Mini Adult", variant: "Bao 3kg", sku: "RC-MA-BAO-3KG", cat: "Thức ăn hạt", stock: 2, status: "SẮP HẾT", statusClass: "status-pending" },
                    { name: "Dây dắt Trixie Premium", variant: "Kích thước S", sku: "TX-DD-PRE-S", cat: "Phụ kiện", stock: 4, status: "SẮP HẾT", statusClass: "status-pending" },
                    { name: "Bóng Trixie Denta Fun", variant: "Màu Đỏ", sku: "TX-BONG-DF-RD", cat: "Đồ chơi", stock: 7, status: "SẮP HẾT", statusClass: "status-pending" }
                ]
            },
            "30days": {
                lowStock: "8 sản phẩm",
                outOfStock: "2 sản phẩm",
                safety: "94.8%",
                total: "1,825 đơn vị",
                items: [
                    { name: "Pate Royal Canin Mini Puppy", variant: "Lon 195g", sku: "RC-PUP-LON-195G", cat: "Pate / Thức ăn ướt", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Pate Me-O Cá Ngừ", variant: "Lốc 12 túi", sku: "ME-O-CANGU-12", cat: "Pate / Thức ăn ướt", stock: 0, status: "HẾT HÀNG", statusClass: "status-cancelled" },
                    { name: "Royal Canin Mini Adult", variant: "Bao 3kg", sku: "RC-MA-BAO-3KG", cat: "Thức ăn hạt", stock: 1, status: "SẮP HẾT", statusClass: "status-pending" },
                    { name: "Dây dắt Trixie Premium", variant: "Kích thước S", sku: "TX-DD-PRE-S", cat: "Phụ kiện", stock: 3, status: "SẮP HẾT", statusClass: "status-pending" },
                    { name: "Bóng Trixie Denta Fun", variant: "Màu Đỏ", sku: "TX-BONG-DF-RD", cat: "Đồ chơi", stock: 5, status: "SẮP HẾT", statusClass: "status-pending" }
                ]
            }
        };

        // Render page function
        function updatePage(filter) {
            const data = mockData[filter];

            // Update stats
            document.getElementById('low-stock-val').innerText = data.lowStock;
            document.getElementById('out-of-stock-val').innerText = data.outOfStock;
            document.getElementById('safety-rate-val').innerText = data.safety;
            document.getElementById('total-stock-val').innerText = data.total;

            // Render table
            const tableBody = document.getElementById('stock-table-body');
            tableBody.innerHTML = '';
            data.items.forEach(item => {
                tableBody.innerHTML += `
                    <tr>
                        <td>
                            <div>
                                <strong style="color: var(--text-main); display: block;">${item.name}</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">${item.variant}</span>
                            </div>
                        </td>
                        <td><span class="slug-text">${item.sku}</span></td>
                        <td>
                            <span class="badge-count" style="background-color: var(--primary-light); color: var(--primary); padding: 4px 8px; border-radius: 4px;">${item.cat}</span>
                        </td>
                        <td style="font-weight: 700; color: ${item.stock === 0 ? 'var(--danger)' : 'var(--warning)'};">${item.stock} SP</td>
                        <td><span class="badge-status ${item.statusClass}">${item.status}</span></td>
                        <td>
                            <button class="btn-action-stock" onclick="alert('Đã gửi yêu cầu nhập thêm sản phẩm ${item.sku}')">Nhập hàng</button>
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
