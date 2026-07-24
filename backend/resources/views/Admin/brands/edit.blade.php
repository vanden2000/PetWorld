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
    .be-status-notice {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        background: #eef8f4;
        border: 1px solid #ceead6;
        color: var(--success);
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1.45;
    }
    .be-status-notice.is-draft {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #d97706;
    }
    .be-status-toast {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1200;
        max-width: 360px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #eef8f4;
        border: 1px solid #ceead6;
        color: var(--success);
        box-shadow: 0 16px 36px rgba(49, 38, 30, 0.14);
        font-size: 0.86rem;
        font-weight: 800;
        line-height: 1.45;
        opacity: 0;
        pointer-events: none;
        transform: translateY(12px);
        transition: opacity 180ms ease, transform 180ms ease;
    }
    .be-status-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    .be-status-toast.is-draft {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #d97706;
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
    /* Biểu đồ cột doanh thu theo tháng (1 chuỗi — màu chủ đạo cam) */
    .be-bars { display: flex; align-items: flex-end; gap: 10px; height: 212px; padding-top: 14px; overflow-x: auto; }
    .be-bar-col { flex: 1 1 0; min-width: 34px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; cursor: default; }
    .be-bar-val { font-size: 0.6rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px; white-space: nowrap; opacity: 0; transition: opacity .15s ease; }
    .be-bar-col:hover .be-bar-val { opacity: 1; }
    .be-bar-fill { width: 100%; max-width: 30px; background: var(--primary); border-radius: 4px 4px 0 0; min-height: 3px; transition: height .35s ease, background-color .2s ease; }
    .be-bar-col:hover .be-bar-fill { background: var(--primary-hover); }
    .be-bar-label { margin-top: 8px; font-size: 0.6rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
    .be-bars-empty { margin: auto; color: var(--text-muted); font-size: 0.85rem; }
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
    .brand-alert { margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; display: flex; align-items: flex-start; gap: 10px; font-size: 0.9rem; font-weight: 700; }
    .brand-alert-error { background: #fff1f1; border: 1px solid #ffd1d1; color: var(--danger); }
    .brand-alert ul { margin: 4px 0 0; padding-left: 18px; font-weight: 600; }

    /* Button remove logo */
    .btn-remove-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin-top: 14px;
        padding: 9px 16px;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--danger);
        background-color: #fff1f1;
        border: 1px solid #ffd1d1;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
    }
    .btn-remove-logo:hover {
        background-color: var(--danger);
        color: #ffffff;
        border-color: var(--danger);
    }
</style>
@endsection

@section('content')
@php
    $statusVal = old('status', $brand->status ?? 'active');
    $hasImage = !empty($brand->image);
    $slugValue = old('slug', $brand->slug);
@endphp

@if($errors->any())
    <div class="brand-alert brand-alert-error">
        <i class="fa-solid fa-circle-exclamation" style="margin-top: 2px;"></i>
        <div>
            <div>Vui lòng kiểm tra lại thông tin thương hiệu.</div>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" class="be-page" id="brandEditForm" onsubmit="return confirm('Bạn có chắc chắn muốn cập nhật thương hiệu này không?')">
    @csrf
    @method('PUT')
    <div class="be-status-toast {{ $statusVal === 'draft' ? 'is-draft' : '' }}" id="brandStatusToast" role="status" aria-live="polite"></div>

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
                        <input type="text" class="form-control" id="slug" name="slug" value="{{ $slugValue }}" placeholder="brand-slug">
                    </div>
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
                    <div class="be-status-notice {{ $statusVal === 'draft' ? 'is-draft' : '' }}" id="brandStatusNotice" role="status" aria-live="polite">
                        {{ $statusVal === 'active' ? 'Thương hiệu đang hoạt động và được hiển thị trên Storefront.' : 'Thương hiệu đang tạm dừng và sẽ được ẩn khỏi Storefront.' }}
                    </div>
                </div>

            <!-- Images -->
            <div class="form-card">
                <div class="be-card-head">
                    <i class="fa-solid fa-image"></i>
                    <span>Logo thương hiệu</span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <div class="be-logo-box" id="logoBox" data-initial="{{ mb_substr($brand->name, 0, 1) }}" style="margin-bottom: 12px;">
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
                    <p class="be-hint" style="margin-top: 0; text-align: center; line-height: 1.5; margin-bottom: 0;">Định dạng: PNG, JPG (Max 5MB).
                        <br>Khuyên dùng: 200x200px.
                    </p>
                    <a href="#" id="logoRemove" class="btn-remove-logo">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Xóa logo</span>
                    </a>
                </div>
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
                // Slug là URL công khai của thương hiệu — giữ ổn định khi sửa, chỉ tự điền nếu đang trống.
                if (slugInput.value.trim() !== '') return;
                let slug = nameInput.value.toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[đĐ]/g, 'd')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/[\s-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            });
        }

        // ---- Status segmented control ----
        const statusInput = document.getElementById('statusInput');
        const segBtns = document.querySelectorAll('.be-seg-btn');
        const statusNotice = document.getElementById('brandStatusNotice');
        const statusToast = document.getElementById('brandStatusToast');
        let statusToastTimeout;
        function showStatusToast(message, isActive) {
            if (!statusToast) return;
            clearTimeout(statusToastTimeout);
            statusToast.classList.toggle('is-draft', !isActive);
            statusToast.textContent = message;
            statusToast.classList.add('is-visible');
            statusToastTimeout = setTimeout(function () {
                statusToast.classList.remove('is-visible');
            }, 2600);
        }
        function updateStatusNotice(value) {
            if (!statusNotice) return;
            const isActive = value === 'active';
            statusNotice.classList.toggle('is-draft', !isActive);
            statusNotice.textContent = isActive
                ? 'Thương hiệu đang hoạt động và được hiển thị trên Storefront. Bấm Lưu thay đổi để áp dụng.'
                : 'Thương hiệu đang tạm dừng và sẽ được ẩn khỏi Storefront. Bấm Lưu thay đổi để áp dụng.';
            showStatusToast(
                isActive
                    ? 'Đã chọn Hoạt động. Bấm Lưu thay đổi để áp dụng.'
                    : 'Đã chọn Tạm dừng. Bấm Lưu thay đổi để áp dụng.',
                isActive
            );
        }
        segBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const value = btn.dataset.status;
                statusInput.value = value;
                segBtns.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                updateStatusNotice(value);
            });
        });

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
