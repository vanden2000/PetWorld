@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa thương hiệu')

@section('styles')
<style>
    /* ==== Brand Edit (Kinetic Enterprise) – scoped, reuses design tokens ==== */
    .be-page { font-family: var(--font-main); }

    /* Header */
    .be-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .be-breadcrumb i { font-size: 0.6rem; opacity: 0.6; }
    .be-breadcrumb .be-crumb-current { color: var(--primary); }
    .be-title { color: var(--text-main); font-weight: 800; font-size: 1.75rem; letter-spacing: -0.01em; }
    .be-title span { color: var(--primary); }

    /* Two column layout */
    .be-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 24px;
        align-items: start;
    }
    .be-main { min-width: 0; display: flex; flex-direction: column; gap: 24px; }
    .be-side { display: flex; flex-direction: column; gap: 24px; }
    .be-main .form-card,
    .be-side .form-card { margin-bottom: 0; padding: 24px; }
    .form-card-title { margin-bottom: 20px; }

    /* Slug with prefix */
    .be-slug { display: flex; }
    .be-slug-prefix {
        display: inline-flex;
        align-items: center;
        padding: 0 12px;
        border: 1px solid var(--border-color);
        border-right: none;
        border-radius: 8px 0 0 8px;
        background: var(--bg-color);
        color: var(--text-muted);
        font-size: 0.78rem;
        white-space: nowrap;
    }
    .be-slug .form-control { border-radius: 0 8px 8px 0; }

    /* Images card */
    .be-images { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 24px; }
    .be-logo-box {
        position: relative;
        width: 128px; height: 128px;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        background: var(--bg-color);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; cursor: pointer;
    }
    .be-logo-box img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
    .be-logo-box .be-logo-placeholder { color: var(--primary); font-size: 2.4rem; font-weight: 800; }
    .be-logo-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,0.42);
        color: #fff;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
        opacity: 0; transition: var(--transition);
        font-size: 0.62rem; font-weight: 800; letter-spacing: 0.04em;
    }
    .be-logo-box:hover .be-logo-overlay { opacity: 1; }
    .be-hint { font-size: 0.7rem; color: var(--text-muted); margin-top: 10px; line-height: 1.5; }

    .be-cover {
        position: relative;
        height: 128px;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        background: var(--bg-color);
        overflow: hidden;
        cursor: pointer;
    }
    .be-cover-img { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0.6; }
    .be-cover-btn {
        position: absolute; inset: 0; margin: auto;
        width: fit-content; height: fit-content;
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,0.92);
        color: var(--text-main);
        border: none; border-radius: 8px;
        padding: 8px 16px; font-weight: 700; font-size: 0.8rem; cursor: pointer;
        box-shadow: var(--shadow-subtle);
    }

    /* Side card head (compact) */
    .be-card-head {
        display: flex; align-items: center; gap: 10px;
        font-size: 1rem; font-weight: 700; color: var(--text-main);
        margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border-color);
    }
    .be-card-head i { color: var(--primary); font-size: 1.1rem; }

    .be-field { margin-bottom: 20px; }
    .be-field:last-child { margin-bottom: 0; }
    .be-label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }

    /* Status segmented control */
    .be-segment { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .be-seg-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 12px; border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--surface-color);
        color: var(--text-muted);
        font-family: var(--font-main); font-weight: 700; font-size: 0.82rem;
        cursor: pointer; transition: var(--transition);
    }
    .be-seg-btn:hover { background: var(--bg-color); }
    .be-seg-btn.is-active[data-status="active"] {
        background: var(--success-light); border-color: var(--success); color: var(--success);
    }
    .be-seg-btn.is-active[data-status="draft"] {
        background: #fff7ed; border-color: #d97706; color: #d97706;
    }

    /* Tier tags */
    .be-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .be-tag {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 10px; border-radius: 999px;
        background: var(--primary-light); color: var(--primary);
        font-size: 0.72rem; font-weight: 700;
    }
    .be-tag:nth-child(n+2) { background: var(--bg-color); color: var(--text-muted); }
    .be-tag button { background: none; border: none; color: inherit; cursor: pointer; font-size: 0.72rem; padding: 0; display: inline-flex; }
    .be-tag-add {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 10px; border-radius: 999px;
        border: 1px dashed var(--border-color); background: none;
        color: var(--text-muted); font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: var(--transition);
    }
    .be-tag-add:hover { border-color: var(--primary); color: var(--primary); }

    /* Toggle rows */
    .be-toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border-color); }
    .be-toggle-row:first-of-type { padding-top: 0; }
    .be-toggle-row strong { display: block; color: var(--text-main); font-size: 0.84rem; }
    .be-toggle-row span { color: var(--text-muted); font-size: 0.72rem; }
    .be-switch { position: relative; width: 44px; height: 24px; flex: 0 0 auto; border: none; border-radius: 999px; background: #d7ddd9; cursor: pointer; transition: var(--transition); }
    .be-switch::after { content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.2); transition: var(--transition); }
    .be-switch.is-on { background: var(--primary); }
    .be-switch.is-on::after { left: 22px; }

    /* Priority slider */
    .be-slider-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .be-slider-value { padding: 2px 10px; border-radius: 6px; background: var(--primary-light); color: var(--primary); font-size: 0.75rem; font-weight: 800; }
    .be-slider { width: 100%; height: 8px; border-radius: 999px; background: #e7ece9; appearance: none; outline: none; cursor: pointer; }
    .be-slider::-webkit-slider-thumb { appearance: none; width: 18px; height: 18px; border-radius: 50%; background: var(--primary); border: 3px solid #fff; box-shadow: 0 0 0 1px var(--border-color); cursor: pointer; }
    .be-slider::-moz-range-thumb { width: 18px; height: 18px; border-radius: 50%; background: var(--primary); border: 3px solid #fff; cursor: pointer; }
    .be-slider-scale { display: flex; justify-content: space-between; margin-top: 8px; color: var(--text-muted); font-size: 0.62rem; font-weight: 700; text-transform: uppercase; }

    /* SEO */
    .be-count { float: right; color: var(--text-muted); font-size: 0.7rem; font-weight: 600; }
    .be-seo-preview { margin-top: 16px; padding: 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-color); }
    .be-seo-preview .be-pv-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
    .be-seo-preview .be-pv-title { color: #1a0dab; font-size: 1rem; line-height: 1.3; margin-bottom: 2px; }
    .be-seo-preview .be-pv-url { color: #006621; font-size: 0.78rem; margin-bottom: 3px; }
    .be-seo-preview .be-pv-desc { color: #4d5156; font-size: 0.78rem; line-height: 1.4; }

    /* Tip card */
    .be-tip { position: relative; overflow: hidden; border-radius: 12px; padding: 22px; background: var(--primary); color: #fff; }
    .be-tip i.be-tip-bg { position: absolute; right: -12px; bottom: -18px; font-size: 6rem; opacity: 0.12; }
    .be-tip h4 { font-size: 1rem; font-weight: 800; margin-bottom: 8px; position: relative; }
    .be-tip p { font-size: 0.8rem; line-height: 1.55; opacity: 0.92; margin-bottom: 14px; position: relative; }
    .be-tip a { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; padding: 7px 12px; border-radius: 8px; font-size: 0.76rem; font-weight: 700; position: relative; transition: var(--transition); }
    .be-tip a:hover { background: rgba(255,255,255,0.32); }

    /* Products detail table */
    .be-products-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 4px; }
    .be-products-head h3 { color: var(--text-main); font-size: 1.05rem; font-weight: 800; }
    .be-products-head p { color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .be-product-table { width: 100%; border-collapse: collapse; }
    .be-product-table th { padding: 12px 10px; text-align: left; color: var(--text-muted); background: var(--bg-color); border-bottom: 1px solid var(--border-color); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .be-product-table td { padding: 14px 10px; border-bottom: 1px solid var(--border-color); color: var(--text-main); font-size: 0.84rem; vertical-align: middle; }
    .be-product-table tbody tr:last-child td { border-bottom: none; }
    .be-product-name { display: flex; align-items: center; gap: 12px; min-width: 220px; }
    .be-product-thumb { width: 44px; height: 44px; border-radius: 8px; border: 1px solid var(--border-color); background: #fff; object-fit: cover; flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .be-product-name strong { font-size: 0.86rem; color: var(--text-main); }
    .be-product-name small { display: block; color: var(--text-muted); font-size: 0.72rem; margin-top: 2px; }

    /* Revenue chart */
    .be-chart-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 20px; }
    .be-chart-head h3 { color: var(--text-main); font-size: 1.05rem; font-weight: 800; }
    .be-chart-head p { color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }
    .be-period { display: flex; gap: 4px; padding: 4px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); flex: 0 0 auto; }
    .be-period button { border: none; background: none; padding: 6px 12px; border-radius: 6px; font-family: var(--font-main); font-weight: 700; font-size: 0.74rem; color: var(--text-muted); cursor: pointer; transition: var(--transition); }
    .be-period button.is-active { background: var(--surface-color); color: var(--primary); box-shadow: var(--shadow-subtle); }
    .be-chart { position: relative; }
    .be-chart svg { width: 100%; height: 200px; overflow: visible; }
    .be-chart-x { display: flex; justify-content: space-between; padding-top: 12px; margin-top: 6px; border-top: 1px solid var(--border-color); }
    .be-chart-x span { color: var(--text-muted); font-size: 0.62rem; font-weight: 700; text-transform: uppercase; }
    .be-hide { display: none !important; }

    /* Product filter bar */
    .be-filter-bar { display: flex; flex-wrap: wrap; gap: 12px; margin: 16px 0; }
    .be-filter-search { position: relative; flex: 1; min-width: 220px; }
    .be-filter-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; }
    .be-filter-search input { width: 100%; padding: 11px 14px 11px 38px; border: 1px solid var(--border-color); border-radius: 8px; font-family: var(--font-main); font-size: 0.85rem; color: var(--text-main); outline: none; transition: var(--transition); background: var(--surface-color); }
    .be-filter-search input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.08); }
    .be-filter-bar select.form-control { min-width: 170px; width: auto; }

    @media (max-width: 1200px) {
        .be-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 760px) {
        .be-images { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $products = $brand->products ?? collect();
    $statusVal = old('status', $brand->status ?? 'active');
    $hasImage = !empty($brand->image);
    $slugValue = old('slug', $brand->slug);
    $seoTitle = $brand->name . ' | PetWorld';
    $seoDesc = \Illuminate\Support\Str::limit(strip_tags($brand->description ?: ''), 155);
    $productCategories = $products->map(fn ($p) => optional($p->category)->name)->filter()->unique()->values();
@endphp

<form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" class="be-page" id="brandEditForm">
    @csrf
    @method('PUT')

    <!-- Header -->
    <div class="dashboard-header" style="margin-bottom: 24px; align-items: flex-start;">
        <div class="header-title-block">
            <div class="be-breadcrumb">
                <span>Quản lý</span>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Thương hiệu</span>
                <i class="fa-solid fa-chevron-right"></i>
                <span class="be-crumb-current">Chỉnh sửa thương hiệu</span>
            </div>
            <h1 class="be-title">Chỉnh sửa thương hiệu: <span>{{ $brand->name }}</span></h1>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.brands') }}" class="btn-cancel">Hủy</a>
            <button type="submit" class="btn-save" id="brandSaveBtn">
                <i class="fa-solid fa-check" style="margin-right: 8px;"></i>Lưu thay đổi
            </button>
        </div>
    </div>

    <div class="be-grid">
        <!-- LEFT COLUMN -->
        <div class="be-main">
            <!-- Basic info -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Thông tin cơ bản</span>
                </div>

                <div class="form-group-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="name">Tên thương hiệu <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $brand->name) }}" required placeholder="VD: Royal Canin">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="slug">Slug</label>
                        <div class="be-slug">
                            <span class="be-slug-prefix">petworld.com/brand/</span>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ $slugValue }}" placeholder="brand-slug">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="website">Website URL</label>
                    <input type="url" class="form-control" id="website" name="website" value="{{ old('website', $brand->website) }}" placeholder="https://example.com">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Mô tả chi tiết</label>
                    <div class="editor-wrapper">
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" data-editor="bold" title="Đậm"><i class="fa-solid fa-bold"></i></button>
                            <button type="button" class="editor-btn" data-editor="italic" title="Nghiêng"><i class="fa-solid fa-italic"></i></button>
                            <button type="button" class="editor-btn" data-editor="list" title="Danh sách"><i class="fa-solid fa-list-ul"></i></button>
                            <button type="button" class="editor-btn" data-editor="link" title="Chèn liên kết"><i class="fa-solid fa-link"></i></button>
                        </div>
                        <textarea class="editor-textarea" id="description" name="description" rows="6" placeholder="Nhập mô tả về câu chuyện thương hiệu của bạn...">{{ old('description', $brand->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-image"></i>
                    <span>Hình ảnh thương hiệu</span>
                </div>
                <div class="be-images">
                    <div>
                        <label class="be-label">Logo thương hiệu</label>
                        <div class="be-logo-box" id="logoBox" data-initial="{{ mb_substr($brand->name, 0, 1) }}">
                            @if($hasImage)
                                <img src="{{ asset($brand->image) }}" alt="{{ $brand->name }}" id="logoPreview">
                            @else
                                <span class="be-logo-placeholder" id="logoPreview">{{ mb_substr($brand->name, 0, 1) }}</span>
                            @endif
                            <div class="be-logo-overlay">
                                <i class="fa-solid fa-pen" style="font-size: 1rem;"></i>
                                <span>THAY ĐỔI</span>
                            </div>
                        </div>
                        <input type="file" id="brand_logo" name="image" style="display: none;" accept="image/*">
                        @if($hasImage)
                            <input type="hidden" name="image_prefilled" value="yes" id="imagePrefilled">
                        @endif
                        <p class="be-hint">Định dạng: PNG, JPG (Max 5MB). Khuyên dùng: 200x200px.
                            <a href="#" id="logoRemove" style="color: var(--danger); font-weight: 700;">Xóa logo</a>
                        </p>
                    </div>
                    <div>
                        <label class="be-label">Ảnh bìa (Cover Image)</label>
                        <div class="be-cover" id="coverBox">
                            <div class="be-cover-img" id="coverPreview"></div>
                            <button type="button" class="be-cover-btn">
                                <i class="fa-solid fa-upload"></i> Tải ảnh lên
                            </button>
                        </div>
                        <input type="file" id="cover_image" style="display: none;" accept="image/*">
                        <p class="be-hint">Tỷ lệ khuyên dùng 16:9. Ảnh sẽ hiển thị tại trang chi tiết thương hiệu trên Storefront.</p>
                    </div>
                </div>
            </div>

            <!-- Revenue chart -->
            <div class="form-card">
                <div class="be-chart-head">
                    <div>
                        <h3>Phân tích doanh thu</h3>
                        <p>Theo dõi biến động doanh thu của thương hiệu theo thời gian.</p>
                    </div>
                    <div class="be-period" id="chartPeriod">
                        <button type="button" class="is-active" data-period="year">Năm nay</button>
                        <button type="button" data-period="6m">6 tháng qua</button>
                    </div>
                </div>
                <div class="be-chart">
                    <svg viewBox="0 0 720 190" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="brandChartFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ff782d" stop-opacity="0.28" />
                                <stop offset="100%" stop-color="#ff782d" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <g class="be-chart-series" data-series="year">
                            <path d="M0 154 L60 136 L120 145 L180 118 L240 126 L300 91 L360 98 L420 62 L480 74 L540 48 L600 58 L660 30 L720 42 L720 190 L0 190 Z" fill="url(#brandChartFill)" />
                            <path d="M0 154 L60 136 L120 145 L180 118 L240 126 L300 91 L360 98 L420 62 L480 74 L540 48 L600 58 L660 30 L720 42" fill="none" stroke="#ff782d" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <g class="be-chart-series be-hide" data-series="6m">
                            <path d="M0 120 L144 96 L288 132 L432 70 L576 88 L720 44 L720 190 L0 190 Z" fill="url(#brandChartFill)" />
                            <path d="M0 120 L144 96 L288 132 L432 70 L576 88 L720 44" fill="none" stroke="#ff782d" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                    </svg>
                    <div class="be-chart-x" data-labels="year">
                        <span>Th.1</span><span>Th.2</span><span>Th.3</span><span>Th.4</span><span>Th.5</span><span>Th.6</span><span>Th.7</span><span>Th.8</span><span>Th.9</span><span>Th.10</span><span>Th.11</span><span>Th.12</span>
                    </div>
                    <div class="be-chart-x be-hide" data-labels="6m">
                        <span>Th.2</span><span>Th.3</span><span>Th.4</span><span>Th.5</span><span>Th.6</span><span>Th.7</span>
                    </div>
                </div>
            </div>

            <!-- Products (real data) -->
            <div class="form-card">
                <div class="be-products-head">
                    <div>
                        <h3>Danh sách sản phẩm</h3>
                        <p>Các sản phẩm đang gắn với thương hiệu này.</p>
                    </div>
                    <button class="btn-filter-action" type="button" id="prodFilterToggle" title="Ẩn/hiện bộ lọc"><i class="fa-solid fa-filter"></i></button>
                </div>

                <div class="be-filter-bar" id="prodFilterBar">
                    <div class="be-filter-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="prodSearch" placeholder="Tìm tên sản phẩm hoặc SKU...">
                    </div>
                    <select class="form-control" id="prodCategory">
                        <option value="">Tất cả danh mục</option>
                        @foreach($productCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    <select class="form-control" id="prodStatus">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Đang bán</option>
                        <option value="draft">Tạm dừng</option>
                    </select>
                </div>

                <div class="table-container">
                    <table class="be-product-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá bán</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php
                                    $variant = $product->variants->first();
                                    $price = $variant ? number_format((float) $variant->effectivePrice(), 0, ',', '.') . ' đ' : '—';
                                @endphp
                                <tr class="be-prow"
                                    data-name="{{ strtolower($product->name.' '.($product->sku ?? 'sp-'.$product->id)) }}"
                                    data-category="{{ optional($product->category)->name ?: '' }}"
                                    data-status="{{ $product->status === 'active' ? 'active' : 'draft' }}">
                                    <td>
                                        <div class="be-product-name">
                                            @if($product->primaryImage)
                                                <img class="be-product-thumb" src="{{ asset($product->primaryImage->image_url) }}" alt="{{ $product->name }}">
                                            @else
                                                <span class="be-product-thumb"><i class="fa-solid fa-box-open"></i></span>
                                            @endif
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                <small>SKU: {{ $product->sku ?? 'SP-'.$product->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge-count">{{ optional($product->category)->name ?: '—' }}</span></td>
                                    <td><strong>{{ $price }}</strong></td>
                                    <td><span class="badge-status {{ $product->status === 'active' ? 'active' : 'draft' }}">{{ $product->status === 'active' ? 'In Stock' : 'Low Stock' }}</span></td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-filter-action brand-admin-action" title="Chỉnh sửa" style="text-decoration: none;"><i class="fa-solid fa-pen" style="font-size: 0.78rem;"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 28px;">Chưa có sản phẩm thuộc thương hiệu này.</td>
                                </tr>
                            @endforelse
                            <tr id="prodNoResult" style="display: none;">
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 28px;">Không tìm thấy sản phẩm phù hợp với bộ lọc.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <aside class="be-side">
            <!-- Business settings -->
            <div class="form-card">
                <div class="be-card-head">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Cài đặt kinh doanh</span>
                </div>

                <div class="be-field">
                    <label class="be-label" for="main_category">Danh mục chính</label>
                    <select class="form-control" id="main_category">
                        @php $cats = ['Thức ăn cho Mèo', 'Thức ăn cho Chó', 'Phụ kiện', 'Chăm sóc sức khỏe']; @endphp
                        @foreach($cats as $cat)
                            <option>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="be-field">
                    <label class="be-label">Trạng thái</label>
                    <input type="hidden" name="status" id="statusInput" value="{{ $statusVal }}">
                    <div class="be-segment">
                        <button type="button" class="be-seg-btn {{ $statusVal === 'active' ? 'is-active' : '' }}" data-status="active">
                            <i class="fa-solid fa-circle-check"></i> Hoạt động
                        </button>
                        <button type="button" class="be-seg-btn {{ $statusVal === 'draft' ? 'is-active' : '' }}" data-status="draft">
                            <i class="fa-solid fa-circle-xmark"></i> Tạm dừng
                        </button>
                    </div>
                </div>

                <div class="be-field">
                    <label class="be-label">Phân hạng (Tiers)</label>
                    <div class="be-tags" id="tierTags">
                        <span class="be-tag">Premium <button type="button" title="Xóa"><i class="fa-solid fa-xmark"></i></button></span>
                        <span class="be-tag">Gourmet <button type="button" title="Xóa"><i class="fa-solid fa-xmark"></i></button></span>
                        <button type="button" class="be-tag-add" id="tierAdd"><i class="fa-solid fa-plus"></i> Thêm tag</button>
                    </div>
                </div>
            </div>

            <!-- Display config -->
            <div class="form-card">
                <div class="be-card-head">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Cấu hình hiển thị</span>
                </div>

                <div class="be-toggle-row">
                    <div>
                        <strong>Hiển thị trên Storefront</strong>
                        <span>Khách hàng có thể thấy thương hiệu này</span>
                    </div>
                    <button type="button" class="be-switch {{ $statusVal === 'active' ? 'is-on' : '' }}" id="toggleStorefront" aria-label="Hiển thị trên Storefront"></button>
                </div>
                <div class="be-toggle-row">
                    <div>
                        <strong>Thương hiệu nổi bật</strong>
                        <span>Ghim ở trang chủ và menu nhanh</span>
                    </div>
                    <button type="button" class="be-switch" id="toggleFeatured" aria-label="Thương hiệu nổi bật"></button>
                </div>

                <div style="padding-top: 18px;">
                    <div class="be-slider-head">
                        <label class="be-label" style="margin-bottom: 0;">Thứ tự ưu tiên</label>
                        <span class="be-slider-value" id="priorityValue">12</span>
                    </div>
                    <input type="range" class="be-slider" id="prioritySlider" min="1" max="100" value="12">
                    <div class="be-slider-scale">
                        <span>Thấp (1)</span>
                        <span>Cao (100)</span>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="form-card">
                <div class="be-card-head">
                    <i class="fa-solid fa-globe"></i>
                    <span>Quản lý SEO</span>
                </div>

                <div class="be-field">
                    <label class="be-label" for="seo_title">SEO Title <span class="be-count"><span id="seoTitleCount">0</span>/70</span></label>
                    <input type="text" class="form-control" id="seo_title" maxlength="70" value="{{ $seoTitle }}" placeholder="Tiêu đề hiển thị trên Google">
                </div>
                <div class="be-field">
                    <label class="be-label" for="seo_desc">Meta Description <span class="be-count"><span id="seoDescCount">0</span>/160</span></label>
                    <textarea class="form-control" id="seo_desc" maxlength="160" rows="3" placeholder="Mô tả ngắn gọn về thương hiệu...">{{ $seoDesc }}</textarea>
                </div>
                <div class="be-field">
                    <label class="be-label" for="seo_keywords">SEO Keywords</label>
                    <input type="text" class="form-control" id="seo_keywords" placeholder="VD: thức ăn mèo, {{ $brand->slug }}">
                </div>

                <div class="be-seo-preview">
                    <div class="be-pv-label">Xem trước kết quả tìm kiếm</div>
                    <div class="be-pv-title" id="pvTitle">{{ $seoTitle }}</div>
                    <div class="be-pv-url">petworld.com › brand › {{ $brand->slug }}</div>
                    <div class="be-pv-desc" id="pvDesc">{{ $seoDesc ?: 'Mô tả ngắn gọn về thương hiệu sẽ hiển thị ở đây.' }}</div>
                </div>
            </div>

            <!-- Tip -->
            <div class="be-tip">
                <i class="fa-solid fa-bolt be-tip-bg"></i>
                <h4>Mẹo quản trị</h4>
                <p>Thương hiệu có hình ảnh sắc nét và mô tả trên 300 từ thường có tỷ lệ chuyển đổi cao hơn 25%.</p>
                <a href="#"><span>Xem hướng dẫn SEO</span> <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
        </aside>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---- Slug auto-generate from name ----
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                let slug = nameInput.value.toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[đĐ]/g, 'd')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/[\s-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            });
        }

        // ---- Status segmented control + storefront toggle sync ----
        const statusInput = document.getElementById('statusInput');
        const segBtns = document.querySelectorAll('.be-seg-btn');
        const toggleStorefront = document.getElementById('toggleStorefront');
        segBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const value = btn.dataset.status;
                statusInput.value = value;
                segBtns.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                if (toggleStorefront) toggleStorefront.classList.toggle('is-on', value === 'active');
            });
        });

        // ---- Generic switch toggles ----
        document.querySelectorAll('.be-switch').forEach(function (sw) {
            sw.addEventListener('click', function () {
                sw.classList.toggle('is-on');
                // Storefront toggle also drives status for consistency
                if (sw.id === 'toggleStorefront' && statusInput) {
                    const on = sw.classList.contains('is-on');
                    statusInput.value = on ? 'active' : 'draft';
                    segBtns.forEach(b => b.classList.toggle('is-active', b.dataset.status === statusInput.value));
                }
            });
        });

        // ---- Priority slider ----
        const slider = document.getElementById('prioritySlider');
        const sliderVal = document.getElementById('priorityValue');
        if (slider && sliderVal) {
            slider.addEventListener('input', function () { sliderVal.textContent = slider.value; });
        }

        // ---- Tier tags ----
        const tierTags = document.getElementById('tierTags');
        const tierAdd = document.getElementById('tierAdd');
        if (tierTags) {
            tierTags.addEventListener('click', function (e) {
                const rm = e.target.closest('.be-tag button');
                if (rm) rm.closest('.be-tag').remove();
            });
        }
        if (tierAdd) {
            tierAdd.addEventListener('click', function () {
                const name = (prompt('Tên phân hạng mới:') || '').trim();
                if (!name) return;
                const tag = document.createElement('span');
                tag.className = 'be-tag';
                tag.innerHTML = name + ' <button type="button" title="Xóa"><i class="fa-solid fa-xmark"></i></button>';
                tierTags.insertBefore(tag, tierAdd);
            });
        }

        // ---- Rich text toolbar (wraps selection in the textarea) ----
        const descArea = document.getElementById('description');
        function wrapSelection(before, after) {
            if (!descArea) return;
            const start = descArea.selectionStart, end = descArea.selectionEnd;
            const sel = descArea.value.substring(start, end) || 'text';
            descArea.value = descArea.value.substring(0, start) + before + sel + after + descArea.value.substring(end);
            descArea.focus();
            descArea.selectionStart = start + before.length;
            descArea.selectionEnd = start + before.length + sel.length;
        }
        document.querySelectorAll('.editor-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switch (btn.dataset.editor) {
                    case 'bold': wrapSelection('**', '**'); break;
                    case 'italic': wrapSelection('_', '_'); break;
                    case 'list': wrapSelection('\n- ', ''); break;
                    case 'link': wrapSelection('[', '](https://)'); break;
                }
            });
        });

        // ---- Logo upload / preview / remove ----
        const logoBox = document.getElementById('logoBox');
        const logoInput = document.getElementById('brand_logo');
        const logoPreview = document.getElementById('logoPreview');
        const logoRemove = document.getElementById('logoRemove');
        function dropPrefilled() {
            const pf = document.getElementById('imagePrefilled');
            if (pf) pf.remove();
        }
        if (logoBox && logoInput) {
            logoBox.addEventListener('click', () => logoInput.click());
            logoInput.addEventListener('change', function () {
                if (!logoInput.files || !logoInput.files[0]) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (logoPreview.tagName === 'IMG') {
                        logoPreview.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.id = 'logoPreview';
                        img.alt = 'Logo';
                        img.src = e.target.result;
                        logoPreview.replaceWith(img);
                    }
                };
                reader.readAsDataURL(logoInput.files[0]);
                dropPrefilled(); // force controller to save the new file
            });
        }
        if (logoRemove) {
            logoRemove.addEventListener('click', function (e) {
                e.preventDefault();
                if (logoInput) logoInput.value = '';
                dropPrefilled(); // controller will null the image on save
                if (logoPreview && logoPreview.tagName === 'IMG') {
                    const span = document.createElement('span');
                    span.className = 'be-logo-placeholder';
                    span.id = 'logoPreview';
                    span.textContent = logoBox ? (logoBox.dataset.initial || '?') : '?';
                    logoPreview.replaceWith(span);
                }
            });
        }

        // ---- Cover image (visual preview only, not persisted) ----
        const coverBox = document.getElementById('coverBox');
        const coverInput = document.getElementById('cover_image');
        const coverPreview = document.getElementById('coverPreview');
        if (coverBox && coverInput) {
            coverBox.addEventListener('click', () => coverInput.click());
            coverInput.addEventListener('change', function () {
                if (!coverInput.files || !coverInput.files[0]) return;
                const reader = new FileReader();
                reader.onload = e => { coverPreview.style.backgroundImage = `url('${e.target.result}')`; };
                reader.readAsDataURL(coverInput.files[0]);
            });
        }

        // ---- SEO counters + live preview ----
        const seoTitle = document.getElementById('seo_title');
        const seoDesc = document.getElementById('seo_desc');
        const seoTitleCount = document.getElementById('seoTitleCount');
        const seoDescCount = document.getElementById('seoDescCount');
        const pvTitle = document.getElementById('pvTitle');
        const pvDesc = document.getElementById('pvDesc');
        function syncSeo() {
            if (seoTitle && seoTitleCount) { seoTitleCount.textContent = seoTitle.value.length; if (pvTitle) pvTitle.textContent = seoTitle.value || 'Tiêu đề thương hiệu'; }
            if (seoDesc && seoDescCount) { seoDescCount.textContent = seoDesc.value.length; if (pvDesc) pvDesc.textContent = seoDesc.value || 'Mô tả ngắn gọn về thương hiệu sẽ hiển thị ở đây.'; }
        }
        if (seoTitle) seoTitle.addEventListener('input', syncSeo);
        if (seoDesc) seoDesc.addEventListener('input', syncSeo);
        syncSeo();

        // ---- Revenue chart period toggle ----
        const chartPeriod = document.getElementById('chartPeriod');
        if (chartPeriod) {
            chartPeriod.querySelectorAll('button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const period = btn.dataset.period;
                    chartPeriod.querySelectorAll('button').forEach(b => b.classList.toggle('is-active', b === btn));
                    document.querySelectorAll('.be-chart-series').forEach(g => g.classList.toggle('be-hide', g.dataset.series !== period));
                    document.querySelectorAll('.be-chart-x').forEach(x => x.classList.toggle('be-hide', x.dataset.labels !== period));
                });
            });
        }

        // ---- Product table filters (search + category + status) ----
        const prodSearch = document.getElementById('prodSearch');
        const prodCategory = document.getElementById('prodCategory');
        const prodStatus = document.getElementById('prodStatus');
        const prodRows = document.querySelectorAll('.be-prow');
        const prodNoResult = document.getElementById('prodNoResult');
        function applyProductFilters() {
            const q = (prodSearch ? prodSearch.value : '').trim().toLowerCase();
            const cat = prodCategory ? prodCategory.value : '';
            const st = prodStatus ? prodStatus.value : '';
            let visible = 0;
            prodRows.forEach(function (row) {
                const matchName = !q || (row.dataset.name || '').indexOf(q) !== -1;
                const matchCat = !cat || row.dataset.category === cat;
                const matchStatus = !st || row.dataset.status === st;
                const show = matchName && matchCat && matchStatus;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (prodNoResult) prodNoResult.style.display = (prodRows.length && visible === 0) ? '' : 'none';
        }
        if (prodSearch) prodSearch.addEventListener('input', applyProductFilters);
        if (prodCategory) prodCategory.addEventListener('change', applyProductFilters);
        if (prodStatus) prodStatus.addEventListener('change', applyProductFilters);

        // ---- Toggle filter bar visibility ----
        const prodFilterToggle = document.getElementById('prodFilterToggle');
        const prodFilterBar = document.getElementById('prodFilterBar');
        if (prodFilterToggle && prodFilterBar) {
            prodFilterToggle.addEventListener('click', function () {
                prodFilterBar.classList.toggle('be-hide');
            });
        }

        // ---- Save button feedback ----
        const form = document.getElementById('brandEditForm');
        const saveBtn = document.getElementById('brandSaveBtn');
        if (form && saveBtn) {
            form.addEventListener('submit', function () {
                saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i>Đang lưu...';
                saveBtn.disabled = true;
            });
        }
    });
</script>
@endsection
