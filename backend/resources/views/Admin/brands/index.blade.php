@extends('admin.layouts.app')

@section('title', 'Quản lý Thương hiệu')

@section('styles')
<style>
    .brand-admin-header h1 { color: var(--primary); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.5px; }
    .brand-admin-header p { color: var(--text-muted); margin-top: 4px; font-size: 0.95rem; }
    .brand-admin-table-card { border-radius: 10px; overflow: hidden; }
    .brand-admin-logo { width: 44px; height: 44px; border: 1px solid var(--border-color); border-radius: 6px; background: #fff; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; }
    .brand-admin-logo img { width: 100%; height: 100%; object-fit: contain; padding: 6px; }
    .brand-admin-logo-fallback { color: var(--primary); background: rgba(255, 120, 45, 0.08); font-weight: 800; font-size: 0.95rem; }
    .brand-admin-name { display: flex; flex-direction: column; gap: 4px; }
    .brand-admin-name strong { color: var(--text-main); font-size: 0.92rem; }
    .brand-admin-name span { color: var(--text-muted); font-size: 0.76rem; }
    .brand-admin-action { width: 34px; height: 34px; padding: 0; justify-content: center; text-decoration: none; }
    .brand-admin-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-top: 0; margin-bottom: 24px; }
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
    
    /* Reset and set specific column alignments for Brands Table */
    .brand-admin-table-card .category-table th,
    .brand-admin-table-card .category-table td {
        text-align: left !important;
    }

    .brand-admin-table-card .category-table th:nth-child(1),
    .brand-admin-table-card .category-table td:nth-child(1) {
        text-align: center !important;
        width: 60px;
        padding-left: 12px !important;
    }

    .brand-admin-table-card .category-table th:nth-child(2),
    .brand-admin-table-card .category-table td:nth-child(2) {
        text-align: center !important;
        width: 80px;
    }

    .brand-admin-table-card .category-table th:nth-child(3),
    .brand-admin-table-card .category-table td:nth-child(3) {
        text-align: left !important;
    }

    .brand-admin-table-card .category-table th:nth-child(5),
    .brand-admin-table-card .category-table td:nth-child(5) {
        text-align: center !important;
        width: 110px;
    }

    .brand-admin-table-card .category-table th:nth-child(6),
    .brand-admin-table-card .category-table td:nth-child(6) {
        text-align: center !important;
        width: 180px;
    }

    .brand-admin-table-card .category-table th:nth-child(7),
    .brand-admin-table-card .category-table td:nth-child(7) {
        text-align: right !important;
        width: 120px;
        padding-right: 20px !important;
    }

    .brand-admin-table-card .badge-status {
        justify-content: center;
        display: inline-flex;
    }

    /* Custom Select Dropdowns in Admin Filters */
    .custom-admin-select-container {
        position: relative;
        width: 100%;
    }

    .custom-admin-select-trigger {
        height: 38px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0 14px;
        background-color: #ffffff;
        font-size: 0.9rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: var(--transition);
        user-select: none;
    }

    .custom-admin-select-trigger:hover,
    .custom-admin-select-container.open .custom-admin-select-trigger {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
    }

    .custom-admin-select-trigger i {
        font-size: 0.75rem;
        color: #9ca3af;
        transition: transform 0.2s ease;
    }

    .custom-admin-select-container.open .custom-admin-select-trigger i {
        transform: rotate(180deg);
        color: var(--primary);
    }

    .custom-admin-select-options {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: #ffffff;
        border: 1px solid #ebdcd0;
        border-radius: 8px;
        padding: 4px;
        margin: 0;
        list-style: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
        z-index: 99;
        display: none;
        flex-direction: column;
        gap: 2px;
    }

    .custom-admin-select-container.open .custom-admin-select-options {
        display: flex;
    }

    .custom-admin-select-option {
        padding: 8px 12px;
        font-size: 0.88rem;
        font-weight: 500;
        color: #4b5563;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: left !important;
    }

    .custom-admin-select-option:hover {
        background-color: #fff4ec;
        color: var(--primary);
    }

    .custom-admin-select-option.selected {
        background-color: var(--primary);
        color: #ffffff;
    }

    /* 4-column filter grid */
    .brand-filters-grid-custom {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr 1.2fr minmax(180px, 1.2fr) !important;
        gap: 16px !important;
        align-items: flex-end !important;
        width: 100%;
        background-color: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: var(--shadow-subtle);
    }

    @media (max-width: 1100px) { .brand-admin-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 900px) {
        .brand-filters-grid-custom {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    @media (max-width: 640px) {
        .brand-admin-stats { grid-template-columns: 1fr; }
        .brand-filters-grid-custom {
            grid-template-columns: 1fr !important;
        }
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

    <form class="brand-filters-grid-custom" method="GET" action="{{ route('admin.brands') }}" style="margin-bottom: 24px;">
        <!-- Search -->
        <div class="filter-col">
            <label class="filter-label">Tìm kiếm thương hiệu</label>
            <div class="filter-input-wrapper">
                <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
                <input class="filter-input" name="search" value="{{ request('search') }}" placeholder="Tên hoặc mô tả...">
            </div>
        </div>

        <!-- Trạng thái -->
        <div class="filter-col">
            <label class="filter-label">Trạng thái</label>
            <div class="filter-input-wrapper">
                <div class="custom-admin-select-container">
                    <div class="custom-admin-select-trigger">
                        <span>Tất cả trạng thái</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <div class="custom-admin-select-options">
                        <div class="custom-admin-select-option" data-value="">Tất cả trạng thái</div>
                        <div class="custom-admin-select-option" data-value="active">Thương hiệu đang hoạt động</div>
                        <div class="custom-admin-select-option" data-value="draft">Thương hiệu bị ẩn</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sắp xếp -->
        <div class="filter-col">
            <label class="filter-label">Sắp xếp</label>
            <div class="filter-input-wrapper">
                <div class="custom-admin-select-container">
                    <div class="custom-admin-select-trigger">
                        <span>Mới nhất</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">
                    <div class="custom-admin-select-options">
                        <div class="custom-admin-select-option" data-value="newest">Mới nhất</div>
                        <div class="custom-admin-select-option" data-value="oldest">Cũ nhất</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="filter-col orders-filter-actions" style="display: flex; gap: 10px; margin-top: auto; padding-bottom: 2px;">
            <button class="btn-dark-slate" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer;">
                <i class="fa-solid fa-filter"></i>
                <span>Lọc</span>
            </button>
            <a href="{{ route('admin.brands') }}" class="btn-clear-filters" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 7px; box-sizing: border-box; padding: 0;">
                Xóa lọc
            </a>
        </div>
    </form>

    <div class="table-card brand-admin-table-card">
        <div class="table-container">
            <table class="category-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Logo</th>
                        <th>Tên thương hiệu</th>
                        <th>SLUG</th>
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
                                    @php
                                        $brandImagePath = $brand->image;
                                        if (filter_var($brandImagePath, FILTER_VALIDATE_URL)) {
                                            $brandImageUrl = $brandImagePath;
                                        } elseif (str_starts_with($brandImagePath, 'uploads/') || str_starts_with($brandImagePath, 'image/')) {
                                            $brandImageUrl = asset($brandImagePath);
                                        } elseif (str_starts_with($brandImagePath, 'storage/')) {
                                            $brandImageUrl = asset($brandImagePath);
                                        } else {
                                            $brandImageUrl = asset('storage/' . $brandImagePath);
                                        }
                                    @endphp
                                    <span class="brand-admin-logo"><img src="{{ $brandImageUrl }}" alt="{{ $brand->name }}"></span>
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
                            <td><span class="badge-count">{{ $brand->products_count ?? 0 }}</span></td>
                            <td>
                                @if($brand->status === 'active')
                                    <span class="badge-status active"><span style="font-size: 0.9rem; line-height: 1;"></span> Đang hoạt động</span>
                                @else
                                    <span class="badge-status draft"><span style="font-size: 0.9rem; line-height: 1;"></span> Đang ẩn</span>
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdowns = document.querySelectorAll('.custom-admin-select-container');

        dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.custom-admin-select-trigger');
            const triggerText = trigger.querySelector('span');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const options = dropdown.querySelectorAll('.custom-admin-select-option');

            // Set initial state based on hidden input value
            const initialValue = hiddenInput.value;
            let matchedOption = Array.from(options).find(opt => opt.getAttribute('data-value') === initialValue);
            if (matchedOption) {
                triggerText.textContent = matchedOption.textContent;
                options.forEach(opt => opt.classList.remove('selected'));
                matchedOption.classList.add('selected');
            }

            // Toggle Open/Close
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                // Close other dropdowns
                dropdowns.forEach(other => {
                    if (other !== dropdown) other.classList.remove('open');
                });
                dropdown.classList.toggle('open');
            });

            // Handle option selection
            options.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const val = option.getAttribute('data-value');
                    const text = option.textContent;

                    // Update hidden input and trigger text
                    hiddenInput.value = val;
                    triggerText.textContent = text;

                    // Update selected class
                    options.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');

                    // Close dropdown
                    dropdown.classList.remove('open');

                    // Submit form automatically
                    const form = dropdown.closest('form');
                    if (form) {
                        form.submit();
                    }
                });
            });
        });

        // Close on click outside
        document.addEventListener('click', function() {
            dropdowns.forEach(dropdown => dropdown.classList.remove('open'));
        });
    });
</script>
@endsection
