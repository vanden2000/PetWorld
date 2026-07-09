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
        margin-bottom: 80px;
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
</style>
@endsection

@section('content')

    <!-- Sticky Header Row -->
    <div class="listing-header">
        <div class="listing-title">
            <h1>Sửa Sản Phẩm</h1>
        </div>
        <div class="action-header-buttons">
            <a href="{{ route('admin.products') }}" class="btn-action-cancel">CANCEL</a>
            <button type="submit" form="product-edit-form" class="btn-action-save">SAVE CHANGES</button>
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
                                   value="{{ $product->variants->isNotEmpty() ? $product->variants->first()->sku : '' }}">
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

                <!-- Pricing & Inventory Card -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-solid fa-tags"></i>
                        <span>Giá Cả & Kho Hàng</span>
                    </div>

                    <div class="form-control-row" style="margin-bottom: 0;">
                        <div class="form-control-group no-margin">
                            <label for="price" class="form-field-label">Giá Bán (đ)</label>
                            <input type="number" id="price" name="price" class="input-text-field" required step="1000" min="0"
                                   placeholder="0"
                                   value="{{ $product->variants->isNotEmpty() ? (float)$product->variants->first()->price : '' }}">
                        </div>
                        <div class="form-control-group no-margin">
                            <label for="cost_price" class="form-field-label">Giá Khuyến Mãi (đ)</label>
                            <input type="number" id="cost_price" name="cost_price" class="input-text-field" step="1000" min="0"
                                   placeholder="Không có"
                                   value="{{ $product->variants->isNotEmpty() ? (float)$product->variants->first()->sale_price : '' }}">
                        </div>
                        <div class="form-control-group no-margin">
                            <label for="quantity" class="form-field-label">Số Lượng Kho Hiện Tại</label>
                            <input type="number" id="quantity" name="quantity" class="input-text-field" required min="0"
                                   placeholder="0"
                                   value="{{ $product->variants->isNotEmpty() ? $product->variants->first()->quantity : '' }}">
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
                        <button type="button" id="btn-add-attribute" class="btn-add-attribute-rule">
                            <i class="fa-solid fa-plus"></i> Add Attribute
                        </button>
                    </div>

                    <div id="attributes-container">
                        <!-- Instantiated by JS: Attributes settings rows -->
                    </div>

                    <div id="variants-table-wrapper" style="display: {{ $product->variants->isNotEmpty() ? 'block' : 'none' }}; overflow-x: auto; margin-top: 20px;">
                        <span class="form-field-label" style="display: block; margin-bottom: 10px;">Danh sách biến thể của sản phẩm</span>
                        <table class="variants-list-table">
                            <thead>
                                <tr>
                                    <th>Biến thể</th>
                                    <th>Mã SKU</th>
                                    <th>Giá Bán (đ)</th>
                                    <th>Tồn Kho</th>
                                    <th style="text-align: center; width: 80px;">Visible</th>
                                </tr>
                            </thead>
                            <tbody id="variants-table-body">
                                @foreach($product->variants as $index => $variant)
                                    <tr>
                                        <td style="font-weight: 700;">{{ $variant->display_name ?: 'Biến thể mặc định' }}</td>
                                        <td><input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}" class="cell-input-small"></td>
                                        <td><input type="number" name="variants[{{ $index }}][price]" value="{{ (float)$variant->price }}" class="cell-input-small" step="1000" min="0"></td>
                                        <td><input type="number" name="variants[{{ $index }}][quantity]" value="{{ $variant->quantity }}" class="cell-input-small" min="0"></td>
                                        <td style="text-align: center;">
                                            <input type="checkbox" name="variants[{{ $index }}][visible]" value="1" {{ $variant->status === 'active' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                        <div class="upload-zone-title">CLICK TO UPLOAD</div>
                        <div class="upload-zone-sub">Support for PNG, JPG. Maximum file size 5MB.</div>
                        <input type="file" id="product-images-input" name="images[]" multiple accept="image/*" style="display: none;">
                    </div>

                    <div class="thumbnails-wrap-row" id="upload-thumbnails-preview">
                        @foreach($product->images as $img)
                            @php
                                $imgSrc = str_contains($img->image_url, '://') ? $img->image_url : asset('storage/' . $img->image_url);
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
                        <span>ORGANIZATION</span>
                    </div>

                    <div class="form-control-group">
                        <label for="brand_id" class="form-field-label">Thương Hiệu</label>
                        <select id="brand_id" name="brand_id" class="input-select-field">
                            <option value="">Không có thương hiệu</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control-group">
                        <label for="tags_input" class="form-field-label">Tags</label>
                        <div class="tags-input-container">
                            <span class="tag-pill">
                                Organic
                                <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>
                            </span>
                            <span class="tag-pill">
                                Puppy
                                <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>
                            </span>
                            <input type="text" id="tags_input" placeholder="Add tags..." class="tag-field-input">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Fixed Bottom Drawer Action Bar -->
        <div class="bottom-fixed-actions-bar">
            <div class="last-edited-text">
                <i class="fa-solid fa-history"></i>
                <span>Last edited by Admin: Just now</span>
            </div>
            <div class="action-header-buttons">
                <a href="{{ route('admin.products') }}" class="btn-action-cancel">CANCEL</a>
                <button type="submit" class="btn-action-save">SAVE PRODUCT</button>
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

        // Dynamic Attributes Generation
        const attributesContainer = document.getElementById('attributes-container');
        const btnAddAttribute = document.getElementById('btn-add-attribute');
        const variantsTableWrapper = document.getElementById('variants-table-wrapper');
        const variantsTableBody = document.getElementById('variants-table-body');
        
        const baseSkuInput = document.getElementById('sku');
        const basePriceInput = document.getElementById('price');
        const baseQtyInput = document.getElementById('quantity');

        let attributeIndex = 0;

        function updateVariantsTable() {
            const rows = attributesContainer.querySelectorAll('.attribute-group-card');
            
            if (rows.length === 0) {
                // If there are raw db variants loaded originally, keep them visible
                const initialCount = variantsTableBody.querySelectorAll('tr').length;
                if (initialCount > 0) {
                    variantsTableWrapper.style.display = 'block';
                } else {
                    variantsTableWrapper.style.display = 'none';
                }
                return;
            }

            // Gather attributes rules
            let sets = [];
            rows.forEach(card => {
                const type = card.querySelector('.input-select-field').value;
                const optionsString = card.querySelector('.input-text-field').value;
                const options = optionsString.split(',')
                    .map(o => o.trim())
                    .filter(o => o.length > 0);
                
                if (options.length > 0) {
                    sets.push({ type, options });
                }
            });

            if (sets.length === 0) {
                return;
            }

            // Cartesian product function to combine arrays
            function cartesian(arrays) {
                return arrays.reduce((acc, curr) => {
                    return acc.flatMap(d => curr.options.map(e => ([...d, { type: curr.type, value: e }])));
                }, [[]]);
            }

            const combinations = cartesian(sets);
            variantsTableBody.innerHTML = '';
            
            const baseSku = (baseSkuInput.value || 'PW-FOOD').toUpperCase();
            const basePrice = basePriceInput.value || '49.99';
            const baseQty = baseQtyInput.value || '150';

            combinations.forEach((combo, index) => {
                const label = combo.map(c => c.value).join(' - ');
                const codeSuffix = combo.map(c => c.value.substring(0, 3).toUpperCase()).join('-');
                const skuCode = `${baseSku}-${codeSuffix}`;
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight: 700;">${label}</td>
                    <td><input type="text" name="variants[${index}][sku]" value="${skuCode}" class="cell-input-small"></td>
                    <td><input type="number" name="variants[${index}][price]" value="${basePrice}" class="cell-input-small" step="1000" min="0"></td>
                    <td><input type="number" name="variants[${index}][quantity]" value="${baseQty}" class="cell-input-small" min="0"></td>
                    <td style="text-align: center;">
                        <input type="checkbox" name="variants[${index}][visible]" value="1" checked style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer;">
                    </td>
                `;
                variantsTableBody.appendChild(tr);
            });

            variantsTableWrapper.style.display = combosCount() > 0 ? 'block' : 'none';
        }

        function combosCount() {
            const rows = attributesContainer.querySelectorAll('.attribute-group-card');
            let count = 0;
            rows.forEach(card => {
                const optLength = card.querySelector('.input-text-field').value.split(',').map(o => o.trim()).filter(o => o.length > 0).length;
                if (optLength > 0) count++;
            });
            return count;
        }

        // Add Attribute listener
        btnAddAttribute.addEventListener('click', function() {
            const idx = attributeIndex++;
            const card = document.createElement('div');
            card.className = 'attribute-group-card';
            card.innerHTML = `
                <div class="form-control-group no-margin">
                    <label class="form-field-label">Attribute Type</label>
                    <select class="input-select-field js-attr-type">
                        <option value="Size">Size (Kích thước)</option>
                        <option value="Color">Color (Màu sắc)</option>
                        <option value="Packing">Quy cách (Packing)</option>
                        <option value="Flavor">Hương vị (Flavor)</option>
                    </select>
                </div>
                <div class="form-control-group no-margin">
                    <label class="form-field-label">Options (Comma separated)</label>
                    <input type="text" class="input-text-field js-attr-options" placeholder="e.g. Small, Medium, Large">
                </div>
                <button type="button" class="btn-delete-attribute-row js-btn-delete-row" title="Delete attribute">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            `;
            
            attributesContainer.appendChild(card);
            
            // Wire listeners to regenerate table on change
            card.querySelector('.js-attr-type').addEventListener('change', updateVariantsTable);
            card.querySelector('.js-attr-options').addEventListener('input', updateVariantsTable);
            
            card.querySelector('.js-btn-delete-row').addEventListener('click', function() {
                card.remove();
                updateVariantsTable();
            });

            updateVariantsTable();
        });

        // Regenerate on base specs inputs
        baseSkuInput.addEventListener('input', updateVariantsTable);
        basePriceInput.addEventListener('input', updateVariantsTable);
        baseQtyInput.addEventListener('input', updateVariantsTable);

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
