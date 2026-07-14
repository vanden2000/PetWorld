@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục')

@section('styles')
    <style>
        /* Modern styling variables and enhancements */
        .categories-add-btn {
            background-color: var(--primary);
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(255, 120, 45, 0.15);
            border: none;
        }

        .categories-add-btn:hover {
            background-color: var(--primary-hover);
            box-shadow: 0 6px 16px rgba(255, 120, 45, 0.25);
            transform: translateY(-1px);
        }

        /* Filter Bar */
        .categories-filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            background: var(--surface-color);
            padding: 16px 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
            box-shadow: var(--shadow-subtle);
        }

        .categories-filter-left, .categories-filter-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Search Box */
        .search-wrapper {
            position: relative;
            min-width: 280px;
        }

        .search-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .search-input-field {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: var(--font-main);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-input-field:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
        }

        /* Status Filter Dropdown */
        .filter-select-wrapper {
            position: relative;
        }

        .status-filter-select {
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            color: var(--text-main);
            font-family: var(--font-main);
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            min-width: 160px;
            appearance: none;
            -webkit-appearance: none;
            padding-right: 36px;
        }

        .filter-select-wrapper::after {
            content: "\f078";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 0.75rem;
            color: var(--text-muted);
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .status-filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
        }

        /* Layout Buttons */
        .categories-layout-toggles {
            display: flex;
            gap: 6px;
            background: var(--bg-color);
            padding: 4px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .categories-layout-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            background-color: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
        }

        .categories-layout-btn:hover {
            color: var(--primary);
            background-color: #ffffff;
        }

        .categories-layout-btn.active {
            background-color: #ffffff;
            color: var(--primary);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            font-weight: 700;
        }

        .categories-display-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Table Card and layout */
        .table-card {
            background: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-subtle);
            overflow: hidden;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .category-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .category-table th {
            background-color: #f8faf9;
            padding: 16px 24px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .category-table td {
            padding: 16px 24px;
            font-size: 0.9rem;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: var(--transition);
        }

        .category-table tbody tr {
            transition: var(--transition);
        }

        .category-table tbody tr:hover td {
            background-color: #fcfdfe;
        }

        /* Hierarchy visual indent link */
        .indent-line {
            display: inline-flex;
            align-items: center;
            color: #cbd5e1;
            margin-right: 8px;
            font-weight: 400;
        }

        .category-image-container {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .category-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .category-image-container:hover img {
            transform: scale(1.12);
        }

        .category-initial-avatar {
            width: 44px;
            height: 44px;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .category-initial-avatar:hover {
            background-color: var(--primary);
            color: #ffffff;
        }

        /* Badges status */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            line-height: 1;
        }

        .badge-status.active {
            background-color: #e6f7ec;
            color: #10b981;
        }

        .badge-status.active .dot {
            width: 6px;
            height: 6px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            animation: pulse-green 2.5s infinite;
        }

        .badge-status.draft {
            background-color: #f1f3f5;
            color: #6c757d;
        }

        .badge-status.draft .dot {
            width: 6px;
            height: 6px;
            background-color: #6c757d;
            border-radius: 50%;
            display: inline-block;
        }

        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .badge-count {
            display: inline-block;
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 6px;
        }

        /* Actions button layout */
        .btn-action-edit {
            color: var(--primary);
            background-color: var(--primary-light);
            border: 1px solid transparent;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-action-edit:hover {
            background-color: var(--primary);
            color: #ffffff !important;
        }

        .btn-action-hide {
            color: var(--text-muted);
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .btn-action-hide:hover {
            color: var(--danger);
            background-color: var(--danger-light);
            border-color: var(--danger-light);
        }

        /* Grid View Cards styles */
        .categories-grid-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .category-card {
            background: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-subtle);
            padding: 24px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.05);
            border-color: rgba(255, 120, 45, 0.25);
        }

        .card-top {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .card-img-wrap {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-color);
        }

        .card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .category-card:hover .card-img-wrap img {
            transform: scale(1.1);
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
            justify-content: center;
        }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
        }

        .card-parent {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .card-slug {
            font-size: 0.75rem;
            background-color: var(--bg-color);
            padding: 2px 8px;
            border-radius: 4px;
            color: var(--text-muted);
            font-family: monospace;
            word-break: break-all;
            display: inline-block;
            margin-top: 4px;
        }

        .card-body {
            margin-bottom: 16px;
            flex-grow: 1;
        }

        .card-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 12px;
            height: 2.6rem;
        }

        .card-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px dashed var(--border-color);
            padding-top: 12px;
        }

        .card-stats-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .card-stats-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1px solid var(--border-color);
            padding-top: 16px;
            margin-top: 16px;
        }

        /* Empty State */
        .empty-state-container {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            background: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-subtle);
            text-align: center;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .empty-state-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Pagination overrides */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-top: none;
            background-color: #ffffff;
        }

        .pagination-btn {
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            color: var(--text-muted);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .pagination-btn:hover:not(:disabled) {
            color: var(--primary);
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .pagination-btn.active {
            background-color: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')
    <!-- Header Block -->
    <div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div class="header-title-block">
            <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem; margin: 0;">Danh mục</h1>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">Quản lý phân cấp danh mục sản phẩm để
                tăng khả năng tìm kiếm và tối ưu hiệu suất SEO của cửa hàng.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.categories.create') }}" class="categories-add-btn">
                <i class="fa-solid fa-plus" style="font-size: 0.95rem;"></i>
                <span>Thêm danh mục</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="categories-filter-bar">
        <div class="categories-filter-left">
            <!-- Client side search box -->
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="categorySearch" class="search-input-field" placeholder="Tìm kiếm danh mục theo tên, slug...">
            </div>

            <!-- Status filter dropdown -->
            <div class="filter-select-wrapper">
                <select id="statusFilter" class="status-filter-select">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="draft">Đang ẩn (Draft)</option>
                </select>
            </div>
        </div>
        
        <div class="categories-filter-right">
            <span class="categories-display-text" id="displayCountText">Hiển thị {{ $categories->count() }} danh mục</span>
            <div class="categories-layout-toggles">
                <button class="categories-layout-btn" id="btnListView" title="Dạng danh sách">
                    <i class="fa-solid fa-list"></i>
                </button>
                <button class="categories-layout-btn" id="btnGridView" title="Dạng lưới">
                    <i class="fa-solid fa-grip"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div class="empty-state-container" id="emptyState">
        <div class="empty-state-icon">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div class="empty-state-title">Không tìm thấy danh mục nào</div>
        <div class="empty-state-subtitle">Hãy thử thay đổi từ khóa tìm kiếm hoặc bộ lọc trạng thái.</div>
    </div>

    <!-- Views Container -->
    <div id="categoriesViewsContainer">
        <!-- 1. List View (Table Card) -->
        <div class="table-card" id="listViewContainer">
            <div class="table-container" style="overflow-x: auto;">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">STT</th>
                            <th style="width: 80px; text-align: center;">Ảnh</th>
                            <th>Tên danh mục</th>
                            <th>Danh mục cha</th>
                            <th>Slug</th>
                            <th style="text-align: center; width: 110px;">Sản phẩm</th>
                            <th style="width: 130px;">Trạng thái</th>
                            <th style="text-align: right; width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        @foreach($categories as $index => $category)
                            <!-- Hidden toggle status form -->
                            <form id="status-form-{{ $category->id }}" action="{{ route('admin.categories.update', $category->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $category->name }}">
                                <input type="hidden" name="slug" value="{{ $category->slug }}">
                                <input type="hidden" name="status" value="{{ $category->status == 'active' ? 'draft' : 'active' }}">
                            </form>

                            <tr class="category-row-item" 
                                data-id="{{ $category->id }}"
                                data-name="{{ strtolower($category->name) }}"
                                data-slug="{{ strtolower($category->slug) }}"
                                data-status="{{ $category->status }}">
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $index + 1 }}</td>
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center;">
                                        @if($category->image)
                                            <div class="category-image-container">
                                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                                            </div>
                                        @else
                                            <div class="category-initial-avatar">
                                                {{ substr($category->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        @if($category->parent_id)
                                            <span class="indent-line" title="Danh mục con"><i class="fa-solid fa-turn-up fa-rotate-90"></i></span>
                                        @endif
                                        <strong style="color: var(--text-main); font-weight: 600;">{{ $category->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    @if($category->parent)
                                        <span class="badge-count" style="background-color: var(--primary-light); color: var(--primary); font-size: 0.75rem;">
                                            <i class="fa-solid fa-folder" style="margin-right: 4px; font-size: 0.7rem;"></i>{{ $category->parent->name }}
                                        </span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">Danh mục gốc</span>
                                    @endif
                                </td>
                                <td><span style="font-family: monospace; font-size: 0.8rem; background-color: var(--bg-color); padding: 4px 8px; border-radius: 4px; color: var(--text-muted);">{{ $category->slug }}</span></td>
                                <td style="text-align: center;">
                                    <span class="badge-count">{{ $category->products ? $category->products->count() : 0 }}</span>
                                </td>
                                <td>
                                    @if($category->status == 'active')
                                        <span class="badge-status active">
                                            <span class="dot"></span> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge-status draft">
                                            <span class="dot"></span> Đang ẩn
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn-action-edit" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action-hide" title="{{ $category->status == 'active' ? 'Ẩn danh mục' : 'Hiển thị danh mục' }}" onclick="confirmToggleStatus('{{ $category->id }}', '{{ $category->name }}', '{{ $category->status }}')">
                                            <i class="fa-solid {{ $category->status == 'active' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Grid View (Cards Container) -->
        <div class="categories-grid-view" id="gridViewContainer" style="display: none;">
            @foreach($categories as $category)
                <div class="category-card category-card-item" 
                     data-id="{{ $category->id }}"
                     data-name="{{ strtolower($category->name) }}"
                     data-slug="{{ strtolower($category->slug) }}"
                     data-status="{{ $category->status }}">
                    <div>
                        <div class="card-top">
                            <div class="card-img-wrap">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                                @else
                                    <span style="font-weight: 700; color: var(--primary); font-size: 1.5rem;">
                                        {{ substr($category->name, 0, 1) }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-info">
                                <h3 class="card-title" title="{{ $category->name }}">{{ $category->name }}</h3>
                                <div class="card-parent">
                                    @if($category->parent)
                                        <i class="fa-solid fa-folder-tree" style="font-size: 0.75rem;"></i>
                                        <span>Con của: <strong>{{ $category->parent->name }}</strong></span>
                                    @else
                                        <i class="fa-solid fa-folder" style="font-size: 0.75rem;"></i>
                                        <span style="font-style: italic;">Danh mục gốc</span>
                                    @endif
                                </div>
                                <span class="card-slug">{{ $category->slug }}</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <p class="card-desc" title="{{ $category->description ?? 'Không có mô tả cho danh mục này.' }}">
                                {{ $category->description ?? 'Không có mô tả cho danh mục này.' }}
                            </p>
                            
                            <div class="card-stats">
                                <div class="card-stats-item">
                                    <span class="card-stats-label">Sản phẩm</span>
                                    <span class="badge-count" style="align-self: flex-start;">
                                        {{ $category->products ? $category->products->count() : 0 }}
                                    </span>
                                </div>
                                <div class="card-stats-item" style="text-align: right;">
                                    <span class="card-stats-label">Trạng thái</span>
                                    @if($category->status == 'active')
                                        <span class="badge-status active">
                                            <span class="dot"></span> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge-status draft">
                                            <span class="dot"></span> Đang ẩn
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn-action-edit" title="Chỉnh sửa" style="flex-grow: 1;">
                            <i class="fa-solid fa-pen" style="margin-right: 6px;"></i>Chỉnh sửa
                        </a>
                        <button type="button" class="btn-action-hide" title="{{ $category->status == 'active' ? 'Ẩn danh mục' : 'Hiển thị danh mục' }}" onclick="confirmToggleStatus('{{ $category->id }}', '{{ $category->name }}', '{{ $category->status }}')" style="width: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid {{ $category->status == 'active' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Global Dynamic Client-side Pagination Card -->
        <div class="table-card" id="paginationCard" style="border-top: none;">
            <div class="pagination-container">
                <div style="font-size: 0.85rem; color: var(--text-muted);" id="paginationInfo">
                    Hiển thị <strong style="color: var(--text-main); font-weight: 600;" id="pageStart">1</strong> đến <strong style="color: var(--text-main); font-weight: 600;" id="pageEnd">6</strong> trong số <strong style="color: var(--text-main); font-weight: 600;" id="pageTotal">12</strong> danh mục
                </div>
                <div class="pagination-buttons" id="paginationButtons">
                    <!-- Pagination buttons dynamically rendered via JS -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Global function to trigger confirm and status change submission
        function confirmToggleStatus(id, name, currentStatus) {
            const actionText = currentStatus === 'active' ? 'ẨN' : 'HIỂN THỊ';
            if (confirm(`Bạn có chắc chắn muốn ${actionText} danh mục "${name}" không?`)) {
                const form = document.getElementById('status-form-' + id);
                if (form) {
                    form.submit();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const searchInput = document.getElementById('categorySearch');
            const statusFilter = document.getElementById('statusFilter');
            const btnListView = document.getElementById('btnListView');
            const btnGridView = document.getElementById('btnGridView');
            const listViewContainer = document.getElementById('listViewContainer');
            const gridViewContainer = document.getElementById('gridViewContainer');
            const emptyState = document.getElementById('emptyState');
            const displayCountText = document.getElementById('displayCountText');
            
            const pageStartEl = document.getElementById('pageStart');
            const pageEndEl = document.getElementById('pageEnd');
            const pageTotalEl = document.getElementById('pageTotal');
            const paginationButtons = document.getElementById('paginationButtons');
            const paginationCard = document.getElementById('paginationCard');

            // View and pagination state settings
            let currentView = localStorage.getItem('category-admin-view') || 'list';
            let currentPage = 1;
            const itemsPerPage = 6;
            let filteredItems = [];

            // Raw categories elements loaded directly from the DOM
            const rawItems = [];
            const rows = document.querySelectorAll('.category-row-item');
            rows.forEach((row, index) => {
                const id = row.getAttribute('data-id');
                rawItems.push({
                    id: id,
                    name: row.getAttribute('data-name'),
                    slug: row.getAttribute('data-slug'),
                    status: row.getAttribute('data-status'),
                    rowIndex: index,
                    rowElement: row,
                    cardElement: document.querySelector(`.category-card-item[data-id="${id}"]`)
                });
            });

            // Set current view with persistence
            function setView(view) {
                currentView = view;
                localStorage.setItem('category-admin-view', view);
                
                if (view === 'grid') {
                    btnListView.classList.remove('active');
                    btnGridView.classList.add('active');
                    listViewContainer.style.display = 'none';
                    gridViewContainer.style.display = 'grid';
                } else {
                    btnGridView.classList.remove('active');
                    btnListView.classList.add('active');
                    gridViewContainer.style.display = 'none';
                    listViewContainer.style.display = 'block';
                }
                render();
            }

            btnListView.addEventListener('click', () => setView('list'));
            btnGridView.addEventListener('click', () => setView('grid'));

            // Core filtering / search handler
            function updateFilteredItems() {
                const query = searchInput.value.toLowerCase().trim();
                const status = statusFilter.value;

                filteredItems = rawItems.filter(item => {
                    const matchesSearch = item.name.includes(query) || item.slug.includes(query);
                    const matchesStatus = (status === 'all') || (item.status === status);
                    return matchesSearch && matchesStatus;
                });

                currentPage = 1; // Reset to first page after filters change
                render();
            }

            searchInput.addEventListener('input', updateFilteredItems);
            statusFilter.addEventListener('change', updateFilteredItems);

            // Re-render display based on layout and active page
            function render() {
                const totalItems = filteredItems.length;
                
                // If no items match, display empty state
                if (totalItems === 0) {
                    listViewContainer.style.display = 'none';
                    gridViewContainer.style.display = 'none';
                    paginationCard.style.display = 'none';
                    emptyState.style.display = 'flex';
                    if (displayCountText) displayCountText.textContent = 'Hiển thị 0 danh mục';
                    return;
                }

                emptyState.style.display = 'none';
                paginationCard.style.display = 'flex';
                
                // Show view based on layout toggled
                if (currentView === 'grid') {
                    listViewContainer.style.display = 'none';
                    gridViewContainer.style.display = 'grid';
                } else {
                    gridViewContainer.style.display = 'none';
                    listViewContainer.style.display = 'block';
                }

                const totalPages = Math.ceil(totalItems / itemsPerPage);
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

                // Hide all rows and cards initially
                rawItems.forEach(item => {
                    if (item.rowElement) item.rowElement.style.display = 'none';
                    if (item.cardElement) item.cardElement.style.display = 'none';
                });

                // Display only elements within current page index range
                for (let i = startIndex; i < endIndex; i++) {
                    const item = filteredItems[i];
                    if (currentView === 'grid') {
                        if (item.cardElement) item.cardElement.style.display = 'flex';
                    } else {
                        if (item.rowElement) item.rowElement.style.display = '';
                    }
                }

                // Update text stats
                if (pageStartEl) pageStartEl.textContent = totalItems === 0 ? 0 : startIndex + 1;
                if (pageEndEl) pageEndEl.textContent = endIndex;
                if (pageTotalEl) pageTotalEl.textContent = totalItems;
                if (displayCountText) displayCountText.textContent = `Hiển thị ${totalItems} danh mục`;

                // Render dynamic pagination buttons
                renderPagination(totalPages);
            }

            function renderPagination(totalPages) {
                paginationButtons.innerHTML = '';
                if (totalPages <= 1) {
                    paginationCard.style.display = 'none';
                    return;
                }
                paginationCard.style.display = 'flex';

                // Previous page button
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'pagination-btn';
                prevBtn.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
                prevBtn.disabled = currentPage === 1;
                prevBtn.title = 'Trang trước';
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        render();
                    }
                });
                paginationButtons.appendChild(prevBtn);

                // Page indexes
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.type = 'button';
                    pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
                    pageBtn.textContent = i;
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        render();
                    });
                    paginationButtons.appendChild(pageBtn);
                }

                // Next page button
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'pagination-btn';
                nextBtn.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.title = 'Trang sau';
                nextBtn.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        render();
                    }
                });
                paginationButtons.appendChild(nextBtn);
            }

            // Run initial load filter
            filteredItems = [...rawItems];
            setView(currentView);
        });
    </script>
@endsection