
@extends('admin.layouts.app')

@section('title', 'Quản lý Thương hiệu')

@section('styles')

@endsection

@section('content')

<div class="dashboard-header" style="margin-bottom: 24px;">
    <div class="header-title-block">
        <h1>Thương hiệu</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Quản lý danh sách đối tác và các thương hiệu sản phẩm của hệ thống PetWorld.</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.brands.create') }}" class="categories-add-btn">
            <i class="fa-solid fa-plus" style="font-size: 0.95rem;"></i>
            <span>Thêm thương hiệu</span>
        </a>
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
        <span class="categories-display-text">Hiển thị {{ $brands->count() }} thương hiệu</span>
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

<!-- Brand Table -->
<div class="table-card">
    <div class="table-container">
        <table class="category-table">
            <thead>
                <tr>
                    <th>STT</th>    
                    <th>Logo</th>
                    <th>TÊN THƯƠNG HIỆU</th>   
                    <th>SLUG</th>
                    <th>WEBSITE</th>
                    <th>TRẠNG THÁI</th>
                    <th>HÀNH ĐỘNG</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $index => $brand)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($brand->image)
                            <img src="{{ asset($brand->image) }}" alt="{{ $brand->name }}" class="category-image" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; border: 1px solid var(--border-color);">
                        @else
                            <div style="width: 40px; height: 40px; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 4px; border: 1px solid var(--border-color); font-size: 0.8rem;">
                                {{ substr($brand->name, 0, 1) }}
                            </div>
                        @endif
                    </td>
                    <td><strong style="color: var(--text-main);">{{ $brand->name }}</strong></td>
                    <td><span class="slug-text">{{ $brand->slug }}</span></td>
                    <td>
                        @if($brand->website)
                            <a href="{{ $brand->website }}" target="_blank" style="color: var(--primary); text-decoration: none; font-size: 0.85rem;">
                                {{ parse_url($brand->website, PHP_URL_HOST) ?: $brand->website }} <i class="fa-solid fa-square-arrow-up-right" style="font-size: 0.75rem;"></i>
                            </a>
                        @else
                            <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($brand->status == 'active')
                            <span class="badge-status active">
                                <span style="font-size: 0.9rem; color: var(--success); line-height: 1;">•</span> Active
                            </span>
                        @else
                            <span class="badge-status draft">
                                <span style="font-size: 0.9rem; color: var(--warning); line-height: 1;">•</span> Inactive
                            </span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; text-decoration: none;" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i>
                            </a>
                            <button class="btn-filter-action" style="padding: 6px 10px; border-radius: 6px; color: var(--text-muted); background: none; border: 1px solid var(--border-color); cursor: pointer;" title="Ẩn">
                                <i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
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
            <button class="pagination-btn active">1</button>
            <button class="pagination-btn">2</button>
            <button class="pagination-btn" title="Next Page">
                <i class="fa-solid fa-angle-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection