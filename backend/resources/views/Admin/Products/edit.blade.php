@extends('admin.layouts.app')

@section('title', 'Sửa Sản phẩm')

@section('styles')
<style>
    /* Scoped variables for orange theme matching style.css */
    :root {
        --theme-primary: var(--primary);
        --theme-primary-hover: var(--primary-hover);
        --theme-primary-light: rgba(255, 120, 45, 0.08);
        --theme-border: var(--border-color);
        --theme-text-main: var(--text-main);
        --theme-text-gray: var(--text-muted);
        --theme-bg: var(--bg-color);
    }

    /* Page Header */
    .listing-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        position: sticky;
        top: 0;
        background-color: var(--theme-bg);
        padding: 10px 0;
        z-index: 10;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }

    .listing-title h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--theme-text-main);
    }

    .action-header-buttons {
        display: flex;
        gap: 12px;
    }

    .btn-action-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        color: var(--theme-text-main);
        border: 1px solid var(--theme-border);
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 50ms ease;
    }

    .btn-action-cancel:hover {
        background-color: #f9f9f9;
        border-color: #ccc;
    }

    .btn-action-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: var(--theme-primary);
        color: #ffffff;
        border: 1px solid var(--theme-primary);
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 50ms ease;
        box-shadow: 0 4px 6px -1px rgba(255, 120, 45, 0.15);
    }

    .btn-action-save:hover {
        background-color: var(--theme-primary-hover);
        border-color: var(--theme-primary-hover);
    }

    /* Core column structure */
    .create-listing-wrapper {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        align-items: start;
    }

    .layout-column-main, .layout-column-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Card styling styling */
    .form-card {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .form-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--theme-text-main);
        margin-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 12px;
    }

    .form-card-title i {
        color: var(--theme-primary);
        font-size: 1.15rem;
    }

    /* Form control structures */
    .form-control-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .form-control-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }

    .form-control-group.no-margin {
        margin-bottom: 0;
    }

    .form-field-label {
        font-size: 0.76rem;
        font-weight: 800;
        color: var(--theme-text-gray);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .input-text-field {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-main);
        outline: none;
        background-color: #ffffff;
        transition: all 0.1s ease;
    }

    .input-text-field:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.08);
    }

    .input-select-field {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-main);
        outline: none;
        background-color: #ffffff;
        cursor: pointer;
        transition: all 0.1s ease;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%235A7268' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
    }

    .input-select-field:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.08);
    }

    /* Rich text editor area mockup styling */
    .editor-wrapper {
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        overflow: hidden;
    }

    .editor-toolbar {
        display: flex;
        gap: 4px;
        background-color: #f9fafb;
        border-bottom: 1px solid var(--theme-border);
        padding: 8px 12px;
    }

    .btn-editor-tool {
        background: none;
        border: none;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: var(--theme-text-gray);
        cursor: pointer;
        font-size: 0.85rem;
    }

    .btn-editor-tool:hover {
        background-color: #e5e7eb;
        color: var(--theme-text-main);
    }

    .editor-textarea {
        width: 100%;
        min-height: 180px;
        padding: 14px;
        border: none;
        outline: none;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-main);
        resize: vertical;
    }

    /* Media Uploader Box layout */
    .upload-zone-wrapper {
        border: 2px dashed var(--theme-border);
        border-radius: 6px;
        padding: 32px 16px;
        text-align: center;
        cursor: pointer;
        background-color: #fdfdfd;
        transition: all 0.15s ease;
    }

    .upload-zone-wrapper:hover {
        background-color: #fafafa;
        border-color: var(--theme-primary);
    }

    .upload-zone-icon {
        font-size: 1.8rem;
        color: var(--theme-primary);
        margin-bottom: 12px;
    }

    .upload-zone-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--theme-text-main);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .upload-zone-sub {
        font-size: 0.72rem;
        color: var(--theme-text-gray);
        margin-top: 4px;
    }

    /* Upload thumbnails styling */
    .thumbnails-wrap-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .thumbnail-img-box {
        position: relative;
        width: 60px;
        height: 60px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--theme-border);
    }

    .thumbnail-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn-delete-thumb {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 16px;
        height: 16px;
        background-color: rgba(0, 0, 0, 0.6);
        color: #ffffff;
        border: none;
        border-radius: 50%;
        font-size: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn-delete-thumb:hover {
        background-color: var(--theme-danger);
    }

    .thumbnail-btn-add {
        width: 60px;
        height: 60px;
        border-radius: 6px;
        border: 1px dashed var(--theme-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--theme-text-gray);
        cursor: pointer;
        font-size: 1.1rem;
        transition: all 0.15s ease;
    }

    .thumbnail-btn-add:hover {
        border-color: var(--theme-primary);
        color: var(--theme-primary);
        background-color: var(--theme-primary-light);
    }

    /* Tags Styling */
    .tags-input-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        padding: 8px 10px;
        background-color: #ffffff;
    }

    .tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #f3f4f6;
        color: var(--theme-text-main);
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tag-pill-btn-remove {
        border: none;
        background: none;
        color: var(--theme-text-gray);
        cursor: pointer;
        font-size: 0.7rem;
    }

    .tag-pill-btn-remove:hover {
        color: var(--theme-danger);
    }

    .tag-field-input {
        border: none;
        outline: none;
        font-family: inherit;
        font-size: 0.85rem;
        color: var(--theme-text-main);
        flex-grow: 1;
        min-width: 60px;
        padding: 2px;
    }

    /* Product Variants Config styling */
    .variants-card-headline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 12px;
    }

    .variants-card-headline h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--theme-text-main);
    }

    .variants-card-headline i {
        color: var(--theme-primary);
    }

    .btn-add-attribute-rule {
        background: none;
        border: none;
        color: var(--theme-primary);
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.1s ease;
    }

    .btn-add-attribute-rule:hover {
        color: var(--theme-primary-hover);
        text-decoration: underline;
    }

    .attribute-group-card {
        background-color: #f9fafb;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: 1fr 2fr auto;
        gap: 16px;
        align-items: flex-end;
    }

    .btn-delete-attribute-row {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        color: var(--theme-danger);
        width: 38px;
        height: 38px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.15s ease;
        margin-bottom: 10px;
    }

    .btn-delete-attribute-row:hover {
        background-color: #fef2f2;
        border-color: rgba(239, 68, 68, 0.2);
    }

    /* Variants table styling */
    .variants-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 14px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
    }

    .variants-list-table th {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--theme-text-gray);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f9fafb;
        padding: 10px 14px;
        border-bottom: 1px solid var(--theme-border);
        text-align: left;
    }

    .variants-list-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--theme-border);
        font-size: 0.88rem;
        color: var(--theme-text-main);
        vertical-align: middle;
    }

    .cell-input-small {
        padding: 6px 10px;
        border: 1px solid var(--theme-border);
        border-radius: 4px;
        font-family: inherit;
        font-size: 0.85rem;
        color: var(--theme-text-main);
        outline: none;
        width: 100%;
    }

    .cell-input-small:focus {
        border-color: var(--theme-primary);
    }

    .variant-builder-tools {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .variant-builder-note {
        color: var(--theme-text-gray);
        font-size: 0.82rem;
    }

    .variant-option-picker {
        display: grid;
        grid-template-columns: minmax(120px, 0.9fr) minmax(120px, 1fr) auto;
        gap: 8px;
        margin-bottom: 8px;
    }

    .variant-option-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 28px;
    }

    .variant-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--theme-primary-light);
        color: var(--theme-primary);
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.75rem;
        font-weight: 800;
    }

    .variant-chip button {
        border: 0;
        background: none;
        color: inherit;
        cursor: pointer;
        font-size: 0.8rem;
        padding: 0;
    }

    .btn-add-variant-row {
        background: var(--theme-primary);
        border: 0;
        border-radius: 6px;
        color: #ffffff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 9px 12px;
    }

    .btn-variant-mini {
        border: 1px solid var(--theme-border);
        background: #ffffff;
        border-radius: 4px;
        cursor: pointer;
        color: var(--theme-text-main);
        height: 34px;
        min-width: 34px;
    }

    /* Fixed Actions Bottom Bar */
    .bottom-fixed-actions-bar {
        position: fixed;
        bottom: 0;
        left: 260px; /* Aligns perfectly with layout sidebar width */
        right: 0;
        background-color: #ffffff;
        border-top: 1px solid var(--theme-border);
        padding: 16px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 100;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.03);
    }

    .last-edited-text {
        font-size: 0.78rem;
        color: var(--theme-text-gray);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .last-edited-text i {
        font-size: 0.9rem;
    }

    /* Hide sidebar gap on responsive view */
    @media (max-width: 900px) {
        .create-listing-wrapper {
            grid-template-columns: 1fr;
        }
        .bottom-fixed-actions-bar {
            left: 0;
        }
    }

    /* Ecommerce admin polish */
    .listing-header {
        top: 0;
        margin: -8px 0 18px;
        padding: 14px 0;
        background: color-mix(in srgb, var(--theme-bg) 92%, #ffffff);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--theme-border);
    }

    .listing-title h1 {
        font-size: 1.45rem;
        letter-spacing: 0;
        line-height: 1.2;
    }

    .create-listing-wrapper {
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 18px;
    }

    .layout-column-main,
    .layout-column-sidebar {
        gap: 18px;
        min-width: 0;
    }

    .form-card {
        border-radius: 8px;
        padding: 18px;
        box-shadow: none;
    }

    .form-card-title,
    .variants-card-headline {
        margin-bottom: 16px;
        padding-bottom: 10px;
    }

    .form-card-title {
        font-size: 1rem;
    }

    .form-control-row {
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 14px;
    }

    .input-text-field,
    .input-select-field,
    .cell-input-small {
        min-height: 40px;
    }

    .editor-textarea {
        min-height: 150px;
        line-height: 1.55;
    }

    .upload-zone-wrapper {
        padding: 24px 14px;
    }

    .thumbnails-wrap-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
    }

    .thumbnail-img-box,
    .thumbnail-btn-add {
        width: 100%;
        aspect-ratio: 1;
        height: auto;
    }

    .variants-list-table {
        min-width: 980px;
        table-layout: fixed;
        overflow: hidden;
    }

    .variants-list-table th,
    .variants-list-table td {
        padding: 10px;
    }

    .variant-builder-tools {
        align-items: center;
        background: #f9fafb;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        padding: 10px;
    }

    .variant-option-picker {
        grid-template-columns: minmax(120px, 1fr) minmax(130px, 1fr) 36px;
        gap: 6px;
    }

    .variant-chip {
        max-width: 100%;
        word-break: break-word;
    }

    .btn-action-cancel,
    .btn-action-save,
    .btn-add-variant-row {
        min-height: 40px;
        white-space: nowrap;
    }

    .bottom-fixed-actions-bar {
        padding: 12px 28px;
        box-shadow: 0 -10px 24px rgba(31, 46, 42, 0.08);
    }

    @media (max-width: 1180px) {
        .create-listing-wrapper {
            grid-template-columns: 1fr;
        }

        .layout-column-sidebar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .listing-header,
        .variant-builder-tools,
        .bottom-fixed-actions-bar {
            align-items: stretch;
            flex-direction: column;
        }

        .action-header-buttons {
            width: 100%;
        }

        .action-header-buttons > * {
            flex: 1;
        }

        .layout-column-sidebar {
            display: flex;
        }

        .form-card {
            padding: 14px;
        }
    }
</style>
@endsection

@section('content')

    <!-- Sticky Header Row -->
    <div class="listing-header">
        <div class="listing-title">
            <h1>Sửa Sản Phẩm</h1>
        </div>
        <div class="action-header-buttons">
            <a href="{{ route('admin.products') }}" class="btn-action-cancel">Hủy</a>
            <button type="submit" form="product-edit-form" class="btn-action-save">Lưu thay đổi</button>
        </div>
    </div>

    <!-- Main Form Grid wrapper -->
    <form id="product-edit-form" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Bạn có chắc chắn muốn cập nhật sản phẩm này không?')">
        @csrf
        @method('PUT')
        
        <div class="create-listing-wrapper">
            
            <!-- Left Side main information column -->
            <div class="layout-column-main">
                
                <!-- General Info Card -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-regular fa-circle-question"></i>
                        <span>Thông Tin Chung</span>
                    </div>

                    <div class="form-control-group">
                        <label for="name" class="form-field-label">Tên Sản Phẩm</label>
                        <input type="text" id="name" name="name" class="input-text-field" required
                               placeholder="Ví dụ: Thức ăn Hạt Cao Cấp Royal Canin cho chó con"
                               value="{{ $product->name }}">
                    </div>

                    <div class="form-control-row">
                        <div class="form-control-group no-margin">
                            <label for="sku" class="form-field-label">Mã SKU Gốc</label>
                            <input type="text" id="sku" name="sku" class="input-text-field" required
                                   placeholder="Ví dụ: RC-ADULT-5KG"
                                   value="{{ $product->slug }}">
                        </div>
                        <div class="form-control-group no-margin">
                            <label for="category_id" class="form-field-label">Danh Mục</label>
                            <select id="category_id" name="category_id" class="input-select-field" required>
                                <option value="" disabled>Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-control-group" style="margin-top: 10px;">
                        <label for="description" class="form-field-label">Mô Tả Sản Phẩm</label>
                        <div class="editor-wrapper">
                            <div class="editor-toolbar">
                                <button type="button" class="btn-editor-tool" title="Bold"><i class="fa-solid fa-bold"></i></button>
                                <button type="button" class="btn-editor-tool" title="Italic"><i class="fa-solid fa-italic"></i></button>
                                <button type="button" class="btn-editor-tool" title="List"><i class="fa-solid fa-list-ul"></i></button>
                                <button type="button" class="btn-editor-tool" title="Link"><i class="fa-solid fa-link"></i></button>
                            </div>
                            <textarea id="description" name="description" class="editor-textarea" 
                                      placeholder="Mô tả chi tiết các thông số sản phẩm, hướng dẫn sử dụng...">{{ $product->description }}</textarea>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Right Side media and organization column -->
            <div class="layout-column-sidebar">
                
                <!-- Media Card -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-regular fa-image"></i>
                        <span>Hình Ảnh</span>
                    </div>

                    <div class="upload-zone-wrapper" onclick="document.getElementById('product-images-input').click();">
                        <i class="fa-solid fa-cloud-arrow-up upload-zone-icon"></i>
                        <div class="upload-zone-title">Nhấn để tải lên</div>
                        <div class="upload-zone-sub">Hỗ trợ PNG, JPG. Kích thước tối đa 5MB.</div>
                        <input type="file" id="product-images-input" name="images[]" multiple accept="image/*" style="display: none;">
                    </div>

                    <div class="thumbnails-wrap-row" id="upload-thumbnails-preview">
                        @foreach($product->images as $img)
                            @php
                                $imgSrc = str_contains($img->image_url, '://')
    ? $img->image_url
    : asset('storage/' . ltrim($img->image_url, '/'));
                            @endphp
                            <div class="thumbnail-img-box">
                                <img src="{{ $imgSrc }}" alt="Pet food photo preview">
                                <button type="button" class="btn-delete-thumb" onclick="this.closest('.thumbnail-img-box').remove();">&times;</button>
                            </div>
                        @endforeach
                        <div class="thumbnail-btn-add" onclick="document.getElementById('product-images-input').click();">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                    </div>
                </div>

                <!-- Organization & Branding Card -->
                <div class="form-card">
                    <div class="form-card-title" style="border: none; margin-bottom: 0;">
                        <i class="fa-solid fa-sitemap"></i>
                        <span>Phân loại</span>
                    </div>

                    <div class="form-control-group">
                        <label for="brand_id" class="form-field-label">Thương Hiệu</label>
                        <select id="brand_id" name="brand_id" class="input-select-field" required>
                            <option value="" disabled>Chọn thương hiệu</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control-group">
                        <label for="tags_input" class="form-field-label">Thẻ</label>
                        <div class="tags-input-container">
                            <span class="tag-pill">
                                Hữu cơ
                                <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>
                            </span>
                            <span class="tag-pill">
                                Chó con
                                <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>
                            </span>
                            <input type="text" id="tags_input" placeholder="Thêm thẻ..." class="tag-field-input">
                        </div>
                    </div>
                </div>

            </div>

        </div>
         <!-- Variants Attributes Config Card -->
                <div class="form-card">
                    <div class="variants-card-headline">
                        <h3>
                            <i class="fa-solid fa-sliders"></i>
                            <span>Biến Thể Sản Phẩm</span>
                        </h3>
                        <button type="button" id="btn-add-variant-row" class="btn-add-attribute-rule">
                            <i class="fa-solid fa-plus"></i> Thêm biến thể
                        </button>
                    </div>

                    <div class="variant-builder-tools">
                        <span class="variant-builder-note">Chọn loại biến thể rồi chọn giá trị tương ứng cho từng SKU.</span>
                        <button type="button" id="btn-add-variant-row-inline" class="btn-add-variant-row">
                            <i class="fa-solid fa-plus"></i> Thêm dòng
                        </button>
                    </div>

                    <div id="variants-table-wrapper" style="overflow-x: auto; margin-top: 10px;">
                        <table class="variants-list-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 360px;">Tùy chọn</th>
                                    <th style="min-width: 150px;">SKU</th>
                                    <th style="min-width: 120px;">Giá bán</th>
                                    <th style="min-width: 120px;">Giảm giá</th>
                                    <th style="min-width: 100px;">Tồn kho</th>
                                    <th style="text-align: center; width: 80px;">Hiển thị</th>
                                    <th style="width: 52px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variants-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>

        <!-- Fixed Bottom Drawer Action Bar -->
        <div class="bottom-fixed-actions-bar">
            <div class="last-edited-text">
                <i class="fa-solid fa-history"></i>
                <span>Chỉnh sửa lần cuối bởi Admin: Vừa xong</span>
            </div>
            <div class="action-header-buttons">
                <a href="{{ route('admin.products') }}" class="btn-action-cancel">Hủy</a>
                <button type="submit" class="btn-action-save">Lưu sản phẩm</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tag management
        const tagsInput = document.getElementById('tags_input');
        const tagsContainer = tagsInput.closest('.tags-input-container');
        
        tagsInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const text = this.value.trim();
                if (text) {
                    const pill = document.createElement('span');
                    pill.className = 'tag-pill';
                    pill.innerHTML = `${text} <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>`;
                    this.parentNode.insertBefore(pill, this);
                    this.value = '';
                }
            }
        });

        const variantTypes = @json($variantTypeOptions);

        const initialVariants = @json($productVariantRows);

        const btnAddVariant = document.getElementById('btn-add-variant-row');
        const btnAddVariantInline = document.getElementById('btn-add-variant-row-inline');
        const variantsTableBody = document.getElementById('variants-table-body');
        const baseSkuInput = document.getElementById('sku');
        const basePriceInput = document.getElementById('price');
        const baseSalePriceInput = document.getElementById('sale_price');
        const baseQtyInput = document.getElementById('quantity');

        let variantIndex = 0;

        function typeOptions() {
            return variantTypes
                .map(type => `<option value="${type.id}">${type.name}</option>`)
                .join('');
        }

        function valueOptions(typeId, selectedIds = []) {
            const type = variantTypes.find(item => String(item.id) === String(typeId)) || variantTypes[0];
            if (!type) return '';

            return type.values
                .map(value => `<option value="${value.id}" ${selectedIds.includes(Number(value.id)) ? 'selected' : ''}>${value.value}</option>`)
                .join('');
        }

        function findValue(valueId) {
            for (const type of variantTypes) {
                const value = type.values.find(item => Number(item.id) === Number(valueId));
                if (value) return { type, value };
            }

            return null;
        }

        function renderChips(row, selectedIds) {
            const chips = row.querySelector('.variant-option-chips');
            const hidden = row.querySelector('.variant-hidden-values');
            chips.innerHTML = '';
            hidden.innerHTML = '';

            selectedIds.forEach(valueId => {
                const found = findValue(valueId);
                if (!found) return;

                const chip = document.createElement('span');
                chip.className = 'variant-chip';
                chip.innerHTML = `${found.type.name}: ${found.value.value} <button type="button" data-value-id="${valueId}">&times;</button>`;
                chips.appendChild(chip);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `variants[${row.dataset.index}][value_ids][]`;
                input.value = valueId;
                hidden.appendChild(input);
            });
        }

        function addVariantRow(initial = {}) {
            const index = variantIndex++;
            const selectedIds = (initial.value_ids || []).map(Number);
            const firstTypeId = variantTypes[0]?.id || '';
            const row = document.createElement('tr');
            row.dataset.index = index;
            row.dataset.selectedIds = JSON.stringify(selectedIds);

            const sku = initial.sku || (baseSkuInput.value ? `${baseSkuInput.value.toUpperCase()}-${index + 1}` : '');
            const price = initial.price ?? basePriceInput.value ?? '';
            const salePrice = initial.sale_price ?? baseSalePriceInput.value ?? '';
            const quantity = initial.quantity ?? baseQtyInput.value ?? '';
            const active = initial.status ? initial.status === 'active' : true;

            row.innerHTML = `
                <td>
                    <input type="hidden" name="variants[${index}][id]" value="${initial.id || ''}">
                    <div class="variant-option-picker">
                        <select class="cell-input-small js-variant-type">${typeOptions()}</select>
                        <select class="cell-input-small js-variant-value">${valueOptions(firstTypeId)}</select>
                        <button type="button" class="btn-variant-mini js-add-option" title="Thêm tùy chọn"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <div class="variant-option-chips"></div>
                    <div class="variant-hidden-values"></div>
                </td>
                <td><input type="text" name="variants[${index}][sku]" value="${sku}" class="cell-input-small" required></td>
                <td><input type="number" name="variants[${index}][price]" value="${price}" class="cell-input-small" step="1000" min="0" required></td>
                <td><input type="number" name="variants[${index}][sale_price]" value="${salePrice || ''}" class="cell-input-small" step="1000" min="0"></td>
                <td><input type="number" name="variants[${index}][quantity]" value="${quantity}" class="cell-input-small" min="0" required></td>
                <td style="text-align: center;"><input type="checkbox" name="variants[${index}][visible]" value="1" ${active ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer;"></td>
                <td><button type="button" class="btn-variant-mini js-remove-variant" title="Xóa dòng"><i class="fa-solid fa-trash-can"></i></button></td>
            `;

            variantsTableBody.appendChild(row);
            renderChips(row, selectedIds);
        }

        function selectedIdsFor(row) {
            return JSON.parse(row.dataset.selectedIds || '[]');
        }

        variantsTableBody.addEventListener('change', function(event) {
            if (!event.target.classList.contains('js-variant-type')) return;

            const row = event.target.closest('tr');
            const valueSelect = row.querySelector('.js-variant-value');
            valueSelect.innerHTML = valueOptions(event.target.value, selectedIdsFor(row));
        });

        variantsTableBody.addEventListener('click', function(event) {
            const row = event.target.closest('tr');
            if (!row) return;

            if (event.target.closest('.js-add-option')) {
                const valueId = Number(row.querySelector('.js-variant-value').value);
                const ids = selectedIdsFor(row);

                if (valueId && !ids.includes(valueId)) {
                    ids.push(valueId);
                    row.dataset.selectedIds = JSON.stringify(ids);
                    renderChips(row, ids);
                }
            }

            if (event.target.closest('.variant-chip button')) {
                const valueId = Number(event.target.closest('button').dataset.valueId);
                const ids = selectedIdsFor(row).filter(id => id !== valueId);
                row.dataset.selectedIds = JSON.stringify(ids);
                renderChips(row, ids);
            }

            if (event.target.closest('.js-remove-variant')) {
                row.remove();
            }
        });

        btnAddVariant.addEventListener('click', () => addVariantRow());
        btnAddVariantInline.addEventListener('click', () => addVariantRow());

        if (initialVariants.length > 0) {
            initialVariants.forEach(variant => addVariantRow(variant));
        } else if (variantTypes.length > 0) {
            addVariantRow();
        }

        // Preview images upload
        const imagesInput = document.getElementById('product-images-input');
        const thumbnailsPreview = document.getElementById('upload-thumbnails-preview');

        imagesInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const box = document.createElement('div');
                    box.className = 'thumbnail-img-box';
                    box.innerHTML = `
                        <img src="${e.target.result}" alt="preview">
                        <button type="button" class="btn-delete-thumb" onclick="this.closest('.thumbnail-img-box').remove();">&times;</button>
                    `;
                    thumbnailsPreview.insertBefore(box, thumbnailsPreview.querySelector('.thumbnail-btn-add'));
                }
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endsection
