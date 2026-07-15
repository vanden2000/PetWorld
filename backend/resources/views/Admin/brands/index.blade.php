@extends('admin.layouts.app')

@section('title', 'Quản lý Thương hiệu')

@section('styles')
<style>
    .brand-admin-header h1 { color: var(--text-main); font-size: 1.9rem; font-weight: 800; letter-spacing: 0; }
    .brand-admin-header p { color: var(--text-muted); margin-top: 6px; font-size: 0.92rem; }
    .brand-admin-table-card { border-radius: 10px; overflow: hidden; }
    .brand-admin-logo { width: 44px; height: 44px; border: 1px solid var(--border-color); border-radius: 6px; background: #fff; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; }
    .brand-admin-logo img { width: 100%; height: 100%; object-fit: contain; padding: 6px; }
    .brand-admin-logo-fallback { color: var(--primary); background: rgba(255, 120, 45, 0.08); font-weight: 800; font-size: 0.95rem; }
    .brand-admin-name { display: flex; flex-direction: column; gap: 4px; }
    .brand-admin-name strong { color: var(--text-main); font-size: 0.92rem; }
    .brand-admin-name span { color: var(--text-muted); font-size: 0.76rem; }
    .brand-admin-action { width: 34px; height: 34px; padding: 0; justify-content: center; text-decoration: none; }
    .brand-admin-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-top: 24px; }
    .brand-admin-stat { min-height: 116px; border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-subtle); }
    .brand-admin-stat:nth-child(1) { background: #fff4f0; }
    .brand-admin-stat:nth-child(2) { background: #eef8f4; }
    .brand-admin-stat:nth-child(3) { background: #edf4ff; }
    .brand-admin-stat-label { color: var(--text-muted); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; }
    .brand-admin-stat-value { color: var(--text-main); font-size: 1.55rem; font-weight: 800; line-height: 1; }
    .brand-admin-stat:nth-child(1) .brand-admin-stat-value { color: var(--primary); }
    .brand-admin-stat:nth-child(2) .brand-admin-stat-value { color: var(--success); }
    .brand-admin-stat-note { color: var(--text-muted); font-size: 0.78rem; font-weight: 600; }
    .brand-admin-detail-card { min-height: 116px; border: 1px solid #d7e5ff; border-radius: 10px; padding: 18px; background: #eaf2ff; display: flex; flex-direction: column; justify-content: center; gap: 12px; text-decoration: none; color: var(--text-main); box-shadow: var(--shadow-subtle); transition: var(--transition); }
    .brand-admin-detail-card:hover { border-color: var(--primary); transform: translateY(-1px); }
    .brand-admin-detail-card i { color: var(--primary); font-size: 1.2rem; }
    .brand-admin-detail-card strong { font-size: 0.92rem; }
    .brand-alert {
        margin-bottom: 20px;
        padding: 12px 16px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .brand-alert-success {
        background: #e6f4ea;
        border: 1px solid #ceead6;
        color: #137333;
    }
    .brand-alert-error {
        background: #fff1f1;
        border: 1px solid #ffd1d1;
        color: var(--danger);
    }
    @media (max-width: 1100px) { .brand-admin-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) {
        .brand-admin-stats { grid-template-columns: 1fr; }
        .categories-filter-bar { align-items: flex-start; gap: 14px; }
    }
</style>
@endsection

@section('content')
@php
    $totalBrands = $brands->count();
    $activeBrands = $brands->where('status', 'active')->count();
    $newBrands = $brands->filter(fn ($brand) => optional($brand->created_at)->gte(now()->subDays(30)))->count();
    $topBrand = $brands->sortByDesc('products_count')->first();
@endphp

<div class="brand-admin-page">
    @if(session('success'))
        <div class="brand-alert brand-alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="brand-alert brand-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="dashboard-header brand-admin-header" style="margin-bottom: 24px;">
        <div class="header-title-block">
            <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Quản lý / Thương hiệu</div>
            <h1>Thương hiệu</h1>
            <p>Quản lý danh sách đối tác và các thương hiệu sản phẩm của hệ thống PetWorld.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.brands.create') }}" class="categories-add-btn">
                <i class="fa-solid fa-plus" style="font-size: 0.95rem;"></i>
                <span>Thêm thương hiệu mới</span>
            </a>
        </div>
    </div>

    <div class="categories-filter-bar">
        <div class="categories-filter-left">
            <button class="btn-filter-action" type="button"><i class="fa-solid fa-filter"></i><span>Bộ lọc</span></button>
            <button class="btn-filter-action" type="button"><i class="fa-solid fa-arrow-down-wide-short"></i><span>Sắp xếp: Mới nhất</span></button>
        </div>
        <div class="categories-filter-right">
            <span class="categories-display-text">Hiển thị {{ $totalBrands }} thương hiệu</span>
            <div class="categories-layout-toggles">
                <button class="categories-layout-btn active" type="button" title="Dạng danh sách"><i class="fa-solid fa-table-list"></i></button>
                <button class="categories-layout-btn" type="button" title="Dạng lưới"><i class="fa-solid fa-grip"></i></button>
            </div>
        </div>
    </div>

    <div class="table-card brand-admin-table-card">
        <div class="table-container">
            <table class="category-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Logo</th>
                        <th>Tên thương hiệu</th>
                        <th>SLUG</th>
                        <th>Website</th>
                        <th>Sản phẩm</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $index => $brand)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($brand->image)
                                    <span class="brand-admin-logo"><img src="{{ filter_var($brand->image, FILTER_VALIDATE_URL) ? $brand->image : (str_starts_with($brand->image, 'storage/') ? asset($brand->image) : asset('storage/' . $brand->image)) }}" alt="{{ $brand->name }}"></span>
                                @else
                                    <span class="brand-admin-logo brand-admin-logo-fallback">{{ mb_substr($brand->name, 0, 1) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="brand-admin-name">
                                    <strong>{{ $brand->name }}</strong>
                                    <span>{{ \Illuminate\Support\Str::limit(strip_tags($brand->description ?: 'Chưa có mô tả chi tiết'), 54) }}</span>
                                </div>
                            </td>
                            <td><span class="slug-text">{{ $brand->slug }}</span></td>
                            <td>
                                @if($brand->website)
                                    <a href="{{ $brand->website }}" target="_blank" rel="noopener" style="color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">{{ parse_url($brand->website, PHP_URL_HOST) ?: $brand->website }}</a>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                                @endif
                            </td>
                            <td><span class="badge-count">{{ $brand->products_count ?? 0 }}</span></td>
                            <td>
                                @if($brand->status === 'active')
                                    <span class="badge-status active"><span style="font-size: 0.9rem; line-height: 1;">•</span> Active</span>
                                @else
                                    <span class="badge-status draft"><span style="font-size: 0.9rem; line-height: 1;">•</span> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn-filter-action brand-admin-action" title="Xem chi tiết"><i class="fa-solid fa-chart-simple" style="font-size: 0.78rem;"></i></a>
                                    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn-filter-action brand-admin-action" title="Chỉnh sửa"><i class="fa-solid fa-pen" style="font-size: 0.78rem;"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 34px;">Chưa có thương hiệu nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Showing <strong style="color: var(--text-main); font-weight: 700;">1 to {{ $totalBrands }}</strong> of <strong style="color: var(--text-main); font-weight: 700;">{{ $totalBrands }}</strong> brands
            </div>
            <div class="pagination-buttons">
                <button class="pagination-btn" type="button" title="Previous Page"><i class="fa-solid fa-angle-left"></i></button>
                <button class="pagination-btn active" type="button">1</button>
                <button class="pagination-btn" type="button" title="Next Page"><i class="fa-solid fa-angle-right"></i></button>
            </div>
        </div>
    </div>

    <div class="brand-admin-stats">
        <div class="brand-admin-stat">
            <span class="brand-admin-stat-label">Tổng cộng</span>
            <strong class="brand-admin-stat-value">{{ str_pad($totalBrands, 2, '0', STR_PAD_LEFT) }}</strong>
            <span class="brand-admin-stat-note">Thương hiệu hiện có</span>
        </div>
        <div class="brand-admin-stat">
            <span class="brand-admin-stat-label">Đang hoạt động</span>
            <strong class="brand-admin-stat-value">{{ str_pad($activeBrands, 2, '0', STR_PAD_LEFT) }}</strong>
            <span class="brand-admin-stat-note">Sẵn sàng hiển thị</span>
        </div>
        <div class="brand-admin-stat">
            <span class="brand-admin-stat-label">Thêm mới</span>
            <strong class="brand-admin-stat-value">+{{ $newBrands }}</strong>
            <span class="brand-admin-stat-note">Trong 30 ngày qua</span>
        </div>
        <a class="brand-admin-detail-card" href="{{ $topBrand ? route('admin.brands.edit', $topBrand->id) : route('admin.brands.create') }}">
            <i class="fa-solid fa-square-poll-vertical"></i>
            <strong>Xem báo cáo chi tiết</strong>
            <span class="brand-admin-stat-note">{{ $topBrand ? 'Nổi bật: '.$topBrand->name : 'Tạo thương hiệu đầu tiên' }}</span>
        </a>
    </div>
</div>
@endsection
