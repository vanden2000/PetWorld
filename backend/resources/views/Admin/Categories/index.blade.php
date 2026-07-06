@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục')

@section('styles')
    <!-- CSS riêng nếu cần -->
@endsection

@section('content')

<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1 style="header-title-block ">Danh mục</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Quản lý phân cấp danh mục sản phẩm để tăng khả năng tìm kiếm và tối ưu hiệu suất SEO của cửa hàng.</p>
    </div>
    <div class="header-actions">
        <button class="categories-add-btn">
            <i class="fa-solid fa-plus" style="font-size: 0.95rem;"></i>
            <span>Thêm danh mục</span>
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="categories-filter-bar">
    <div class="categories-filter-left">
        <button class="btn-filter-action">
            <i class="fa-solid fa-filter"></i>
            <span>Bộ lọc</span>
        </button>
        <button class="btn-filter-action">
            <i class="fa-solid fa-sort"></i>
            <span>Sắp xếp: Mới nhất</span>
        </button>
    </div>
    <div class="categories-filter-right">
        <span class="categories-display-text">Hiển thị 12 danh mục</span>
        <div class="categories-layout-toggles">
            <button class="categories-layout-btn active" title="Dạng danh sách">
                <i class="fa-solid fa-list"></i>
            </button>
            <button class="categories-layout-btn" title="Dạng lưới">
                <i class="fa-solid fa-grip"></i>
            </button>
        </div>
    </div>
</div>

<!-- Category Hierarchy Table -->
<div class="table-card">
    <div class="table-container">
        <table class="category-table">
            <thead>
                <tr>
                    <th>STT</th>    
                    <th>Ảnh</th>
                    <th>TÊN DANH MỤC</th>   
                    <th>SLUG</th>
                    <th>SẢN PHẨM</th>
                    <th>TRẠNG THÁI</th>
                    <th>HÀNH ĐỘNG</th>
                </tr>
            </thead>
            <tbody>
                <!-- Parent Row 1 -->
                <tr>
                    <td>1</td>
                    <td>
                        <img src="{{ asset('images/category/dog.png') }}" alt="Dog" class="category-image">
                    </td>
                    <td>Phụ kiện cho chó</td>
                    <td><span class="slug-text">phu-kien-cho-cho</span></td>
                    <td>
                        <span class="badge-count">452</span>
                    </td>
                    <td>
                        <span class="badge-status active">
                            <span style="font-size: 0.9rem; color: #10b981; line-height: 1;">•</span> Active
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px;" title="Chỉnh sửa"><i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i></button>
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; color: var(--text-muted);" title="Ẩn"><i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>
                        <img src="{{ asset('images/category/dog.png') }}" alt="Dog" class="category-image">
                    </td>
                    <td>Phụ kiện cho chó</td>
                    <td><span class="slug-text">phu-kien-cho-cho</span></td>
                    <td>
                        <span class="badge-count">452</span>
                    </td>
                    <td>
                        <span class="badge-status active">
                            <span style="font-size: 0.9rem; color: #10b981; line-height: 1;">•</span> Active
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px;" title="Chỉnh sửa"><i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i></button>
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; color: var(--text-muted);" title="Ẩn"><i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>
                        <img src="{{ asset('images/category/dog.png') }}" alt="Dog" class="category-image">
                    </td>
                    <td>Phụ kiện cho chó</td>
                    <td><span class="slug-text">phu-kien-cho-cho</span></td>
                    <td>
                        <span class="badge-count">452</span>
                    </td>
                    <td>
                        <span class="badge-status active">
                            <span style="font-size: 0.9rem; color: #10b981; line-height: 1;">•</span> Active
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px;" title="Chỉnh sửa"><i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i></button>
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; color: var(--text-muted);" title="Ẩn"><i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>
                        <img src="{{ asset('images/category/dog.png') }}" alt="Dog" class="category-image">
                    </td>
                    <td>Phụ kiện cho chó</td>
                    <td><span class="slug-text">phu-kien-cho-cho</span></td>
                    <td>
                        <span class="badge-count">452</span>
                    </td>
                    <td>
                        <span class="badge-status active">
                            <span style="font-size: 0.9rem; color: #10b981; line-height: 1;">•</span> Active
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px;" title="Chỉnh sửa"><i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i></button>
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; color: var(--text-muted);" title="Ẩn"><i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Child Row 2.1 -->
                <tr>
                    <td>1</td>
                    <td>
                        <img src="{{ asset('images/category/dog.png') }}" alt="Dog" class="category-image">
                    </td>
                    <td>
                      
                           Cat Litte
                        
                    </td>
                    <td><span class="slug-text">cat-litter</span></td>
                    <td>
                        <span class="badge-count" style="background-color: var(--bg-color); color: var(--text-muted);">56</span>
                    </td>
                   
                    <td>
                        <span class="badge-status draft">
                            <span style="font-size: 0.9rem; color: #d97706; line-height: 1;">•</span> Draft
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px;" title="Chỉnh sửa"><i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i></button>
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; color: var(--primary);" title="Hiện"><i class="fa-solid fa-eye" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="pagination-container">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Showing <strong style="color: var(--text-main); font-weight: 600;">1 to 6</strong> of <strong style="color: var(--text-main); font-weight: 600;">12</strong> categories
        </div>
        <div class="pagination-buttons">
            <button class="pagination-btn" title="Previous Page">
                <i class="fa-solid fa-angle-left"></i>
            </button>
            <button class="pagination-btn active" style="background-color: #004b38; color: #ffffff; border-color: #004b38;">1</button>
            <button class="pagination-btn">2</button>
            <button class="pagination-btn" title="Next Page">
                <i class="fa-solid fa-angle-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection