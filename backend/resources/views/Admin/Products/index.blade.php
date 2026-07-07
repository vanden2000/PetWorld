@extends('admin.layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('styles')
<style>
    /* Styling variables scoped to this page */
    :root {
        --theme-primary: var(--primary); /* #ff782d */
        --theme-primary-hover: var(--primary-hover); /* #e9661c */
        --theme-primary-light: rgba(255, 120, 45, 0.08); /* light orange variant matching primary */
        --theme-gray-light: var(--bg-color); /* #f7faf8 */
        --theme-border: var(--border-color); /* #e5ebe7 */
        --theme-text-gray: var(--text-muted); /* #5a7268 */
        --theme-text-dark: var(--text-main); /* #1f2e2a */
        --theme-success: var(--success);
        --theme-warning: var(--warning);
        --theme-danger: var(--danger);
    }

    /* Product Header Area */
    .product-header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
    }

    .product-header-container h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--theme-text-dark);
        letter-spacing: -0.5px;
    }

    .product-header-container p {
        font-size: 0.95rem;
        color: var(--theme-text-gray);
        margin-top: 4px;
    }

    .header-action-buttons {
        display: flex;
        gap: 12px;
    }

    .btn-export-data {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: #ffffff;
        color: var(--theme-primary);
        border: 1px solid var(--theme-primary);
        padding: 10px 18px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-export-data:hover {
        background-color: var(--theme-primary-light);
    }

    .btn-add-product-new {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: var(--theme-primary);
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(255, 120, 45, 0.15);
    }

    .btn-add-product-new:hover {
        background-color: var(--theme-primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 12px -2px rgba(255, 120, 45, 0.25);
    }

    /* Filter Card Styling */
    .filter-card-wrapper {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .filter-grid-form {
        display: grid;
        grid-template-columns: 2fr 1.25fr 1.25fr auto;
        gap: 16px;
        align-items: flex-end;
    }

    .filter-input-element {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-input-element label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--theme-text-gray);
    }

    .search-input-field-wrap {
        position: relative;
        width: 100%;
    }

    .search-input-field-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.95rem;
    }

    .field-input-box {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: 1px solid var(--theme-border);
        background-color: #ffffff;
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-dark);
        outline: none;
        transition: all 0.15s ease;
    }

    .field-input-box:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(0, 92, 78, 0.08);
    }

    .field-select-box {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--theme-border);
        background-color: #ffffff;
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-dark);
        outline: none;
        cursor: pointer;
        transition: all 0.15s ease;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 10px center;
        background-repeat: no-repeat;
        background-size: 20px;
        padding-right: 32px;
    }

    .field-select-box:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(0, 92, 78, 0.08);
    }

    .btn-filter-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: #e5e7eb;
        color: #374151;
        border: none;
        padding: 11px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-filter-submit:hover {
        background-color: #d1d5db;
    }

    /* Products Table Layout */
    .products-table-card {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .tbl-products {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .tbl-products th {
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--theme-text-gray);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 14px 16px;
        border-bottom: 1px solid var(--theme-border);
        background-color: var(--theme-gray-light);
    }

    .tbl-products td {
        padding: 16px;
        border-bottom: 1px solid var(--theme-border);
        font-size: 0.9rem;
        color: var(--theme-text-dark);
        vertical-align: middle;
    }

    .tbl-products tbody tr {
        transition: background-color 0.15s ease;
    }

    .tbl-products tbody tr:hover {
        background-color: var(--theme-gray-light);
    }

    /* Expand Button Column */
    .col-expand-btn {
        width: 48px;
        text-align: center;
        color: #9ca3af;
        cursor: pointer;
        font-size: 0.8rem;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .tbl-products tbody tr:hover .col-expand-btn {
        color: var(--theme-text-dark);
    }

    /* Product Item Cell formatting */
    .product-info-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .product-thumbnail-img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--theme-border);
        background-color: #ffffff;
    }

    .product-text-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .product-title-bold {
        font-weight: 700;
        color: var(--theme-text-dark);
        font-size: 0.9rem;
        line-height: 1.3;
    }

    .product-desc-muted {
        font-size: 0.78rem;
        color: var(--theme-text-gray);
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Column Specs */
    .sku-code-box {
        font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.8rem;
        color: #374151;
        font-weight: 600;
    }

    .sku-badge-variants {
        display: inline-block;
        font-size: 0.72rem;
        color: var(--theme-text-gray);
        background-color: #f3f4f6;
        padding: 1px 5px;
        border-radius: 4px;
        margin-top: 4px;
    }

    /* Category Pill */
    .category-pill-badge {
        display: inline-block;
        background-color: #f3f4f6;
        color: #4b5563;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Price tag styling */
    .price-text-style {
        font-weight: 700;
        color: var(--theme-text-dark);
    }

    /* Stock count styling */
    .stock-count-style {
        font-weight: 600;
    }

    .stock-critical {
        color: var(--theme-danger);
    }

    /* Status badge layouts */
    .status-badge-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .status-dot-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-success-dot {
        background-color: var(--theme-success);
    }
    .status-success-text {
        color: var(--theme-success);
    }

    .status-warning-dot {
        background-color: var(--theme-warning);
    }
    .status-warning-text {
        color: var(--theme-warning);
    }

    .status-danger-dot {
        background-color: var(--theme-danger);
    }
    .status-danger-text {
        color: var(--theme-danger);
    }

    /* Action Buttons cell */
    .action-icon-cell {
        display: flex;
        gap: 12px;
    }

    .btn-action-tool {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        font-size: 0.95rem;
        transition: color 0.15s ease, transform 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .btn-action-tool:hover {
        color: var(--theme-text-dark);
        transform: scale(1.08);
    }

    .btn-action-tool-delete:hover {
        color: var(--theme-danger);
    }

    /* Product row variants drawer style */
    .variant-drawer-row {
        background-color: #fafbfc;
        display: none; /* Dynamic visibility toggled by JS */
    }

    .variant-drawer-inner {
        padding: 12px 24px;
        border-top: 1px dashed var(--theme-border);
        border-bottom: 1px dashed var(--theme-border);
    }

    .variants-grid-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }

    .variant-item-box {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .variant-options-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--theme-text-dark);
    }

    .variant-sku-gray {
        font-size: 0.72rem;
        color: var(--theme-text-gray);
        margin-top: 2px;
    }

    .variant-price-details {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--theme-text-dark);
        text-align: right;
    }

    .variant-qty-badge {
        font-size: 0.72rem;
        padding: 1px 5px;
        border-radius: 4px;
        font-weight: 600;
        margin-top: 2px;
        display: inline-block;
    }

    .badge-qty-in-stock {
        background-color: var(--theme-primary-light);
        color: var(--theme-primary);
    }

    .badge-qty-out-stock {
        background-color: #fef2f2;
        color: var(--theme-danger);
    }

    /* Pagination Footer Container */
    .products-pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 16px;
        border-top: 1px solid var(--theme-border);
        background-color: #ffffff;
    }

    .pag-display-info {
        font-size: 0.85rem;
        color: var(--theme-text-gray);
    }

    .pag-list-links {
        display: flex;
        gap: 6px;
        list-style: none;
    }

    .pag-btn-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid var(--theme-border);
        background-color: #ffffff;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--theme-text-gray);
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .pag-btn-link:hover:not(.disabled):not(.active) {
        border-color: var(--theme-primary);
        color: var(--theme-primary);
        background-color: var(--theme-primary-light);
    }

    .pag-btn-link.active {
        background-color: var(--theme-primary);
        color: #ffffff;
        border-color: var(--theme-primary);
    }

    .pag-btn-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f3f4f6;
    }

    /* Alerts styling */
    .alert-panel {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .alert-success-box {
        background-color: var(--theme-primary-light);
        color: var(--theme-primary);
        border: 1px solid rgba(0, 92, 78, 0.15);
    }

    /* Responsive scaling */
    @media (max-width: 960px) {
        .filter-grid-form {
            grid-template-columns: 1fr 1fr;
        }
        .btn-filter-submit {
            grid-column: span 2;
            width: 100%;
        }
    }
</style>
@endsection

@section('content')

    <!-- Success Message Panel -->
    @if(session('success'))
        <div class="alert-panel alert-success-box">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Header Block -->
    <div class="product-header-container">
        <div class="header-title-block">
            <h1>Quản lý Sản phẩm</h1>
            <p>Tổng cộng {{ number_format($totalCount, 0, ',', '.') }} sản phẩm đang được niêm yết.</p>
        </div>
        <div class="header-action-buttons">
            <a href="#" class="btn-export-data">
                <i class="fa-solid fa-download"></i> Xuất dữ liệu
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn-add-product-new">
                <i class="fa-solid fa-plus"></i> Thêm Sản phẩm mới
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="filter-card-wrapper">
        <form action="{{ route('admin.products') }}" method="GET" class="filter-grid-form">
            
            <div class="filter-input-element">
                <label for="search">Tìm kiếm theo tên/SKU</label>
                <div class="search-input-field-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search" name="search" class="field-input-box" 
                           placeholder="Ví dụ: Thức ăn hạt cho mèo..." value="{{ $search }}">
                </div>
            </div>

            <div class="filter-input-element">
                <label for="category_id">Danh mục</label>
                <select id="category_id" name="category_id" class="field-select-box">
                    <option value="all" {{ $categoryId == 'all' ? 'selected' : '' }}>Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-input-element">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" class="field-select-box">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Đã ẩn</option>
                    <option value="out_of_stock" {{ $status == 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
                </select>
            </div>

            <button type="submit" class="btn-filter-submit">
                <i class="fa-solid fa-sliders"></i> Lọc kết quả
            </button>

        </form>
    </div>

    <!-- Products List Card -->
    <div class="products-table-card">
        <div class="table-container">
            <table class="tbl-products">
                <thead>
                    <tr>
                        <th style="width: 48px;"></th>
                        <th>SẢN PHẨM</th>
                        <th>SLUG</th>
                        <th>DANH MỤC</th>
                        <th>GIÁ</th>
                        <th>TỒN KHO</th>
                        <th>TRẠNG THÁI</th>
                        <th style="text-align: right; padding-right: 24px;">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @if($products->isEmpty())
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--theme-text-gray);">
                                <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 8px;"></i>
                                <p>Không tìm thấy sản phẩm nào khớp với tìm kiếm.</p>
                            </td>
                        </tr>
                    @else
                        @foreach($products as $product)
                            @php
                                // Accumulate variants quantity
                                $totalQuantity = $product->variants->sum('quantity');

                                // Find price range
                                $prices = $product->variants->map(function($v) {
                                    return $v->hasValidSalePrice() ? (float)$v->sale_price : (float)$v->price;
                                })->filter()->toArray();

                                $minPrice = count($prices) ? min($prices) : 0;
                                $maxPrice = count($prices) ? max($prices) : 0;
                            @endphp
                            <!-- Main Product Row -->
                            <tr data-product-id="{{ $product->id }}">
                                <td class="col-expand-btn js-toggle-drawer" title="Chi tiết biến thể">
                                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                                </td>
                                <td>
                                    <div class="product-info-cell">
                                        <!-- Primary Image resolution -->
                                        @php
                                            $imageUrl = 'https://placehold.co/80x80?text=PetWorld';
                                            if ($product->primaryImage) {
                                                $imageUrl = str_contains($product->primaryImage->image_url, '://') 
                                                    ? $product->primaryImage->image_url 
                                                    : asset('storage/' . $product->primaryImage->image_url);
                                            } elseif ($product->images->first()) {
                                                $imageUrl = str_contains($product->images->first()->image_url, '://')
                                                    ? $product->images->first()->image_url
                                                    : asset('storage/' . $product->images->first()->image_url);
                                            }
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="product-thumbnail-img">
                                        <div class="product-text-details">
                                            <span class="product-title-bold">{{ $product->name }}</span>
                                             @if($product->variants->count() > 1)
                                        <span class="sku-badge-variants">+{{ $product->variants->count() - 1 }} biến thể</span>
                                              @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="sku-code-box">
                                       
                                            <span style="color: #9ca3af; font-weight: normal;">{{ $product->slug }}</span>
                                    </div>
                                   
                                </td>
                                <td>
                                    <span class="category-pill-badge">
                                        {{ $product->category ? $product->category->name : 'Chưa phân loại' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="price-text-style">
                                        @if($minPrice === 0)
                                            Chưa định giá
                                        @elseif($minPrice === $maxPrice)
                                            {{ number_format($minPrice, 0, ',', '.') }}đ
                                        @else
                                            {{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="stock-count-style {{ $totalQuantity == 0 ? 'stock-critical' : '' }}">
                                        {{ $totalQuantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->status === 'inactive')
                                        <div class="status-badge-cell status-warning-text">                              
                                            <span>Đã ẩn</span>
                                        </div>
                                    @elseif($totalQuantity <= 0)
                                        <div class="status-badge-cell status-danger-text">
                                            <span>Hết hàng</span>
                                        </div>
                                    @else
                                        <div class="status-badge-cell status-success-text">
                                            <span>Hoạt động</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-icon-cell" style="justify-content: flex-end; padding-right: 8px;">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-action-tool" title="Xem/Sửa">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-tool btn-action-tool-delete" title="Xóa">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Variants Drawer Sub-Row -->
                            @if($product->variants->isNotEmpty())
                                <tr class="variant-drawer-row" id="drawer-{{ $product->id }}">
                                    <td colspan="8" style="padding: 0;">
                                        <div class="variant-drawer-inner">
                                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--theme-text-gray); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                Chi Tiết Các Biến Thể của Sản Phẩm ({{ $product->variants->count() }})
                                            </div>
                                            <div class="variants-grid-list">
                                                @foreach($product->variants as $variant)
                                                    <div class="variant-item-box">
                                                        <div>
                                                            <div class="variant-options-label">
                                                                {{ $variant->display_name ?: 'Biến thể mặc định' }}
                                                            </div>
                                                            <div class="variant-sku-gray">
                                                                SKU: {{ $variant->sku }}
                                                            </div>
                                                        </div>
                                                        <div class="variant-price-details">
                                                            <div>
                                                                @if($variant->hasValidSalePrice())
                                                                    <span style="text-decoration: line-through; color: #9ca3af; font-size: 0.78rem; margin-right: 4px;">
                                                                        {{ number_format($variant->price, 0, ',', '.') }}đ
                                                                    </span>
                                                                    <span style="color: var(--theme-danger);">
                                                                        {{ number_format($variant->sale_price, 0, ',', '.') }}đ
                                                                    </span>
                                                                @else
                                                                    <span>{{ number_format($variant->price, 0, ',', '.') }}đ</span>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <span class="variant-qty-badge {{ $variant->quantity > 0 ? 'badge-qty-in-stock' : 'badge-qty-out-stock' }}">
                                                                    Còn lại: {{ $variant->quantity }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination Footer -->
        @if(!$products->isEmpty())
            <div class="products-pagination-container">
                <div class="pag-display-info">
                    Hiển thị 
                    <strong>{{ $products->firstItem() }}-{{ $products->lastItem() }}</strong> 
                    trên 
                    <strong>{{ $products->total() }}</strong> sản phẩm
                </div>

                <div class="pag-wrapper">
                    <ul class="pag-list-links">
                        <!-- Previous Page Link -->
                        @if ($products->onFirstPage())
                            <li class="pag-btn-link disabled">
                                <i class="fa-solid fa-angle-left"></i>
                            </li>
                        @else
                            <li>
                                <a href="{{ $products->previousPageUrl() }}" class="pag-btn-link">
                                    <i class="fa-solid fa-angle-left"></i>
                                </a>
                            </li>
                        @endif

                        <!-- Pagination Pages Link -->
                        @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                            @if ($page == $products->currentPage())
                                <li class="pag-btn-link active">{{ $page }}</li>
                            @else
                                <li>
                                    <a href="{{ $url }}" class="pag-btn-link">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        <!-- Next Page Link -->
                        @if ($products->hasMorePages())
                            <li>
                                <a href="{{ $products->nextPageUrl() }}" class="pag-btn-link">
                                    <i class="fa-solid fa-angle-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="pag-btn-link disabled">
                                <i class="fa-solid fa-angle-right"></i>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        @endif
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Variants Drawer Sub-Row Handler
        const togglers = document.querySelectorAll('.js-toggle-drawer');
        
        togglers.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const parentRow = this.closest('tr');
                const productId = parentRow.getAttribute('data-product-id');
                const drawerRow = document.getElementById('drawer-' + productId);
                const arrowIcon = this.querySelector('.arrow-icon');
                
                if (drawerRow) {
                    const isVisible = window.getComputedStyle(drawerRow).display !== 'none';
                    
                    if (isVisible) {
                        drawerRow.style.display = 'none';
                        arrowIcon.style.transform = 'rotate(0deg)';
                    } else {
                        drawerRow.style.display = 'table-row';
                        arrowIcon.style.transform = 'rotate(90deg)';
                    }
                }
            });
        });
    });
</script>
@endsection