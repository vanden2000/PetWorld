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
    .filter-grid-form.is-filtering { opacity: .65; pointer-events: none; }

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

    .badge-qty-low-stock {
        background-color: #fff1f0;
        color: #c2410c;
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

    .alert-error-box {
        color: var(--theme-danger);
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
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

    /* Ecommerce admin polish */
    .product-header-container {
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 18px;
    }

    .product-header-container h1 {
        font-size: 1.55rem;
        letter-spacing: 0;
        line-height: 1.2;
    }

    .header-action-buttons {
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-export-data,
    .btn-add-product-new,
    .btn-filter-submit {
        min-height: 40px;
        white-space: nowrap;
    }

    .filter-card-wrapper,
    .products-table-card {
        border-radius: 8px;
        box-shadow: none;
    }

    .filter-card-wrapper {
        padding: 16px;
        margin-bottom: 18px;
    }

    .filter-grid-form {
        grid-template-columns: minmax(260px, 1.8fr) minmax(180px, 1fr) minmax(180px, 1fr) auto;
        gap: 12px;
    }

    .filter-input-element {
        gap: 6px;
    }

    .field-input-box,
    .field-select-box {
        min-height: 40px;
    }

    .table-container {
        overflow-x: auto;
    }

    .tbl-products {
        min-width: 1200px;
    }

    .tbl-products th {
        white-space: nowrap;
    }

    .tbl-products td {
        padding: 13px 14px;
    }

    .product-thumbnail-img {
        width: 56px;
        height: 56px;
    }

    .product-title-bold {
        font-size: 0.92rem;
    }

    .product-desc-muted,
    .sku-badge-variants,
    .variant-sku-gray {
        line-height: 1.35;
    }

    .category-pill-badge,
    .variant-qty-badge {
        border-radius: 6px;
        line-height: 1.45;
    }

    .status-badge-cell {
        border-radius: 999px;
        background: #f9fafb;
        width: fit-content;
    }

    .status-success-text {
        background: rgba(22, 163, 74, 0.1);
    }

    .status-warning-text {
        background: rgba(245, 158, 11, 0.12);
    }

    .status-danger-text {
        background: rgba(239, 68, 68, 0.1);
    }

    .action-icon-cell {
        gap: 8px;
    }

    .btn-action-tool {
        width: 34px;
        height: 34px;
        border: 1px solid transparent;
        border-radius: 6px;
    }

    .btn-action-tool:hover {
        background: var(--theme-primary-light);
        border-color: rgba(255, 120, 45, 0.18);
        color: var(--theme-primary);
        transform: none;
    }

    .btn-action-tool-delete:hover {
        background: #fef2f2;
        border-color: rgba(239, 68, 68, 0.18);
        color: var(--theme-danger);
    }

    .variant-drawer-row {
        background: #fbfcfd;
    }

    .variant-drawer-inner {
        padding: 14px 18px 18px;
        border-top: 1px solid var(--theme-border);
        border-bottom: 1px solid var(--theme-border);
    }

    .variants-grid-list {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }

    .variant-item-box {
        align-items: flex-start;
        border-radius: 8px;
        gap: 12px;
        min-height: 74px;
    }

    .variant-options-label {
        line-height: 1.35;
    }

    .products-pagination-container {
        padding: 14px 16px;
    }

    @media (max-width: 960px) {
        .product-header-container {
            flex-direction: column;
        }

        .header-action-buttons {
            justify-content: flex-start;
            width: 100%;
        }

        .filter-grid-form {
            grid-template-columns: 1fr;
        }

        .btn-filter-submit {
            grid-column: auto;
        }

        .products-pagination-container {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }
    }
    .product-main-row .js-toggle-drawer { cursor: pointer; }
    .product-main-row .js-toggle-drawer:focus-visible { outline: 2px solid var(--theme-primary); outline-offset: -2px; }
    .product-main-row.is-inactive > td { background: rgba(255, 120, 45, .16); }
    .product-main-row.is-inactive > td:first-child { border-left: 4px solid var(--theme-primary); }
    .product-main-row.is-inactive:hover > td { background: rgba(255, 120, 45, .23); }
    .product-main-row.has-low-stock > td { background: #fff8f7; }
    .product-main-row.has-low-stock > td:first-child { border-left: 4px solid #ef4444; }
    .product-main-row.has-low-stock:hover > td { background: #fff0ee; }
    .product-main-row.is-out-of-stock > td { background: #fef2f2; }
    .product-main-row.is-out-of-stock > td:first-child { border-left: 4px solid var(--theme-danger); }
    .product-main-row.is-out-of-stock:hover > td { background: #fee2e2; }
    .variant-item-box.is-low-stock { border-color: #f87171; background: #fff8f7; }
    .variant-item-box.is-out-of-stock { border-color: var(--theme-danger); background: #fef2f2; }
    .stock-state-chip { display: inline-flex; align-items: center; gap: 5px; margin-top: 5px; padding: 4px 8px; border-radius: 999px; font-size: .72rem; font-weight: 800; }
    .stock-state-chip.low { color: #c2410c; background: #fff1f0; }
    .stock-state-chip.out { color: var(--theme-danger); background: #fee2e2; }
    .status-danger-text { color: var(--theme-danger); background: #fef2f2; }
    .variant-status-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: 4px 8px; font-size: .75rem; font-weight: 700; }
    .variant-status-chip.active { color: var(--theme-success); background: rgba(34, 197, 94, .1); }
    .variant-status-chip.inactive { color: var(--theme-text-gray); background: var(--theme-gray-light); }
    .advice-list-status { display: grid; gap: 5px; min-width: 150px; }
    .advice-list-status strong { font-size: .78rem; }
    .advice-list-status small { color: var(--theme-text-gray); font-size: .72rem; line-height: 1.35; }
    .advice-list-status .status-ready { color: #237343; }
    .advice-list-status .status-missing { color: #a45b14; }
    .btn-action-tool-hide { color: var(--theme-warning); }
    .btn-action-tool-show { color: var(--theme-success); }
    .confirm-modal[hidden] { display: none; }
    .confirm-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 20px; background: rgba(15, 23, 42, .48); }
    .confirm-modal-card { width: min(440px, 100%); padding: 24px; border-radius: 16px; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .22); }
    .confirm-modal-card h3 { margin: 0 0 10px; color: var(--theme-text-dark); }
    .confirm-modal-card p { margin: 0; color: var(--theme-text-gray); line-height: 1.6; }
    .confirm-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
    .confirm-modal-button { border: 0; border-radius: 9px; padding: 10px 16px; font-weight: 700; cursor: pointer; }
    .confirm-modal-cancel { color: var(--theme-text-dark); background: var(--theme-gray-light); }
    .confirm-modal-submit { color: #fff; background: var(--theme-primary); }
    .export-modal-card { width: min(560px, 100%); }
    .export-options { display: grid; gap: 18px; margin-top: 20px; }
    .export-option-group { display: grid; gap: 10px; }
    .export-option-group > span { color: var(--theme-text-dark); font-size: .85rem; font-weight: 800; }
    .export-option-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .export-option { display: flex; align-items: flex-start; gap: 9px; padding: 12px; border: 1px solid var(--theme-border); border-radius: 10px; cursor: pointer; }
    .export-option:has(input:checked) { border-color: var(--theme-primary); background: var(--theme-primary-light); }
    .export-option input { margin-top: 3px; accent-color: var(--theme-primary); }
    .export-option strong { display: block; color: var(--theme-text-dark); font-size: .86rem; }
    .export-option small { display: block; margin-top: 3px; color: var(--theme-text-gray); line-height: 1.35; }
    .confirm-modal-submit:disabled { cursor: wait; opacity: .7; }

    @media (max-width: 600px) {
        .export-option-list { grid-template-columns: 1fr; }
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

    @if(session('error'))
        <div class="alert-panel alert-error-box">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Header Block -->
    <div class="product-header-container">
        <div class="header-title-block">
            <h1>Quản lý Sản phẩm</h1>
            <p>Tổng cộng {{ number_format($totalCount, 0, ',', '.') }} sản phẩm đang được niêm yết.</p>
        </div>
        <div class="header-action-buttons">
            <button type="button" class="btn-export-data" id="open-export-modal">
                <i class="fa-solid fa-download"></i> Xuất dữ liệu
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn-add-product-new">
                <i class="fa-solid fa-plus"></i> Thêm Sản phẩm mới
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="filter-card-wrapper">
        <form action="{{ route('admin.products') }}" method="GET" class="filter-grid-form" id="product-filter-form">
            
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
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Đang hiển thị</option>
                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Đã ẩn</option>
                </select>
            </div>

            <a href="{{ route('admin.products') }}" class="btn-filter-submit" style="text-decoration: none;">Xóa bộ lọc</a>

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
                        <th>Hồ sơ AI</th>
                        <th>SLUG</th>
                        <th>DANH MỤC</th>
                        <th>GIÁ</th>
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
                                // Find price range
                                $prices = $product->variants->map(function($v) {
                                    return $v->hasValidSalePrice() ? (float)$v->sale_price : (float)$v->price;
                                })->filter()->toArray();

                                $minPrice = count($prices) ? min($prices) : 0;
                                $maxPrice = count($prices) ? max($prices) : 0;
                                $lowStockThreshold = 10;
                                $visibleVariants = $product->variants->where('status', 'active');
                                $isOutOfStock = $visibleVariants->isNotEmpty() && $visibleVariants->every(fn ($variant) => (int) $variant->quantity === 0);
                                $hasLowStock = ! $isOutOfStock && $visibleVariants->contains(fn ($variant) => (int) $variant->quantity > 0 && (int) $variant->quantity < $lowStockThreshold);
                                $advice = $product->advice_attributes ?? [];
                                $hasSpecies = $product->petSpecies->isNotEmpty();
                                $hasAdvice = !empty($advice['life_stages']) || !empty($advice['needs']);
                                $adviceNames = [
                                    'kitten' => 'Mèo con', 'puppy' => 'Chó con',
                                    'adult' => 'Trưởng thành', 'senior' => 'Lớn tuổi', 'all_life_stages' => 'Mọi độ tuổi',
                                    'skin_coat' => 'Da & lông', 'picky_eater' => 'Kén ăn', 'dental' => 'Răng miệng', 'weight_control' => 'Kiểm soát cân nặng', 'indoor' => 'Nuôi trong nhà', 'daily_nutrition' => 'Dinh dưỡng hằng ngày',
                                ];
                                $adviceSummary = $product->petSpecies->pluck('name')
                                    ->merge(collect(['life_stages', 'needs'])
                                    ->flatMap(fn ($key) => $advice[$key] ?? [])
                                    ->map(fn ($value) => $adviceNames[$value] ?? null)
                                    ->filter()
                                    ->take(3))
                                    ->take(4)
                                    ->implode(' · ');
                            @endphp
                            <!-- Main Product Row -->
                            <tr class="product-main-row {{ $product->status === 'inactive' ? 'is-inactive' : ($isOutOfStock ? 'is-out-of-stock' : ($hasLowStock ? 'has-low-stock' : '')) }}" data-product-id="{{ $product->id }}">
                                <td class="col-expand-btn js-toggle-drawer" title="Chi tiết biến thể">
                                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                                </td>
                                <td class="js-toggle-drawer" tabindex="0" role="button" aria-expanded="false" aria-controls="drawer-{{ $product->id }}">
                                    <div class="product-info-cell">
                                        @php
                                            $fallbackImageUrl = asset('image/logo/logo.png');
                                            $imageUrl = $fallbackImageUrl;
                                            $image = $product->primaryImage ?? $product->images->first();
                                            $imageAlt = $image?->alt_text ?: $product->name;

                                            if ($image?->image_url) {
                                                $path = ltrim($image->image_url, '/');

                                                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                                                    $imageUrl = $path;
                                                } elseif (str_starts_with($path, 'storage/') || str_starts_with($path, 'image/')) {
                                                    $imageUrl = asset($path);
                                                } elseif (str_starts_with($path, 'products/')) {
                                                    $imageUrl = asset('storage/'.$path);
                                                } else {
                                                    // Compatibility for legacy product images stored without a directory prefix.
                                                    $imageUrl = asset('storage/'.$path);
                                                }
                                            }
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="product-thumbnail-img"
                                             onerror="this.onerror=null;this.src='{{ $fallbackImageUrl }}';">
                                        <div class="product-text-details">
                                            <span class="product-title-bold">{{ $product->name }}</span>
                                             @if($product->variants->count() > 1)
                                        <span class="sku-badge-variants">+{{ $product->variants->count() - 1 }} biến thể</span>
                                              @endif
                                        </div>
                                    </div>
                                </td>
                                 <td>
                                    <div class="advice-list-status">
                                        @if($hasSpecies && $hasAdvice)
                                            <strong class="status-ready"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Đủ thông tin</strong>
                                            <small>{{ $adviceSummary }}</small>
                                        @else
                                            <strong class="status-missing"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Cần bổ sung</strong>
                                            <small>{{ !$hasSpecies ? 'Chưa chọn loài phù hợp' : 'Chưa chọn độ tuổi hoặc nhu cầu hỗ trợ' }}</small>
                                        @endif
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
                                    @if($product->status === 'inactive')
                                        <div class="status-badge-cell status-warning-text">                              
                                            <span>Đã ẩn</span>
                                        </div>
                                    @elseif($isOutOfStock)
                                        <div class="status-badge-cell status-danger-text">
                                            <span>Hết hàng</span>
                                        </div>
                                    @elseif($hasLowStock)
                                        <div class="status-badge-cell status-warning-text">
                                            <span>Sắp hết hàng</span>
                                        </div>
                                    @else
                                        <div class="status-badge-cell status-success-text">
                                            <span>Đang hiển thị</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-icon-cell" style="justify-content: flex-end; padding-right: 8px;">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-action-tool" title="Sửa sản phẩm">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.products.status.update', $product) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $product->status === 'active' ? 'inactive' : 'active' }}">
                                            @if($product->status === 'active')
                                                <button type="button" class="btn-action-tool btn-action-tool-hide js-open-hide-modal" title="Ẩn sản phẩm" data-product-name="{{ $product->name }}">
                                                    <i class="fa-solid fa-eye-slash"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="btn-action-tool btn-action-tool-show" title="Hiện lại sản phẩm">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            @endif
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
                                                    @php
                                                        $isVariantOutOfStock = $variant->status === 'active' && (int) $variant->quantity === 0;
                                                        $isVariantLowStock = $variant->status === 'active' && (int) $variant->quantity > 0 && (int) $variant->quantity < $lowStockThreshold;
                                                    @endphp
                                                    <div class="variant-item-box {{ $isVariantOutOfStock ? 'is-out-of-stock' : ($isVariantLowStock ? 'is-low-stock' : '') }}">
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
                                                                <span class="variant-qty-badge {{ $isVariantOutOfStock ? 'badge-qty-out-stock' : ($isVariantLowStock ? 'badge-qty-low-stock' : 'badge-qty-in-stock') }}">
                                                                    Còn lại: {{ $variant->quantity }}
                                                                </span>
                                                                <span class="variant-status-chip {{ $variant->status }}">
                                                                    {{ $variant->status === 'active' ? 'Đang hiển thị' : 'Đã ẩn' }}
                                                                </span>
                                                                @if($isVariantOutOfStock)
                                                                    <span class="stock-state-chip out"><i class="fa-solid fa-circle-xmark"></i> Hết hàng</span>
                                                                @elseif($isVariantLowStock)
                                                                    <span class="stock-state-chip low"><i class="fa-solid fa-triangle-exclamation"></i> Sắp hết hàng</span>
                                                                @endif
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

    <div class="confirm-modal" id="export-product-modal" hidden aria-hidden="true">
        <div class="confirm-modal-card export-modal-card" role="dialog" aria-modal="true" aria-labelledby="export-product-title">
            <h3 id="export-product-title">Xuất dữ liệu sản phẩm</h3>
            <p>Chọn phạm vi và nội dung cần đưa vào file Excel.</p>

            <form action="{{ route('admin.products.export') }}" method="GET" id="product-export-form">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="category_id" value="{{ $categoryId }}">
                <input type="hidden" name="status" value="{{ $status }}">

                <div class="export-options">
                    <div class="export-option-group">
                        <span>Phạm vi xuất</span>
                        <div class="export-option-list">
                            <label class="export-option">
                                <input type="radio" name="scope" value="filtered" checked>
                                <span><strong>Theo bộ lọc hiện tại</strong><small>Xuất toàn bộ kết quả phù hợp, không giới hạn trang.</small></span>
                            </label>
                            <label class="export-option">
                                <input type="radio" name="scope" value="all">
                                <span><strong>Tất cả sản phẩm</strong><small>Bỏ qua các bộ lọc đang chọn.</small></span>
                            </label>
                            <label class="export-option">
                                <input type="radio" name="scope" value="active">
                                <span><strong>Đang hiển thị</strong><small>Chỉ sản phẩm đang hiển thị.</small></span>
                            </label>
                            <label class="export-option">
                                <input type="radio" name="scope" value="inactive">
                                <span><strong>Đã ẩn</strong><small>Chỉ sản phẩm đang bị ẩn.</small></span>
                            </label>
                        </div>
                    </div>

                    <div class="export-option-group">
                        <span>Nội dung file</span>
                        <div class="export-option-list">
                            <label class="export-option">
                                <input type="radio" name="include_variants" value="1" checked>
                                <span><strong>Sản phẩm và biến thể</strong><small>Tạo hai sheet trong cùng file.</small></span>
                            </label>
                            <label class="export-option">
                                <input type="radio" name="include_variants" value="0">
                                <span><strong>Chỉ sản phẩm</strong><small>Chỉ tạo sheet thông tin sản phẩm.</small></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="confirm-modal-actions">
                    <button type="button" class="confirm-modal-button confirm-modal-cancel" id="cancel-product-export">Hủy</button>
                    <button type="submit" class="confirm-modal-button confirm-modal-submit" id="submit-product-export">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Xuất Excel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="confirm-modal" id="hide-product-modal" hidden aria-hidden="true">
        <div class="confirm-modal-card" role="dialog" aria-modal="true" aria-labelledby="hide-product-title">
            <h3 id="hide-product-title">Ẩn sản phẩm?</h3>
            <p>
                Sản phẩm “<strong id="hide-product-name"></strong>” và các biến thể sẽ không còn hiển thị cho khách hàng.
                Dữ liệu sản phẩm vẫn được giữ lại.
            </p>
            <div class="confirm-modal-actions">
                <button type="button" class="confirm-modal-button confirm-modal-cancel" id="cancel-hide-product">Hủy</button>
                <button type="button" class="confirm-modal-button confirm-modal-submit" id="confirm-hide-product">Xác nhận ẩn</button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglers = document.querySelectorAll('.js-toggle-drawer');

        function closeDrawers(exceptId = null) {
            document.querySelectorAll('.variant-drawer-row').forEach(drawer => {
                if (drawer.id !== exceptId) drawer.style.display = 'none';
            });

            document.querySelectorAll('.product-main-row').forEach(row => {
                if ('drawer-' + row.dataset.productId !== exceptId) {
                    row.querySelectorAll('.js-toggle-drawer').forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
                    const arrow = row.querySelector('.arrow-icon');
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                }
            });
        }

        function toggleDrawer(toggle) {
            const parentRow = toggle.closest('tr');
            const drawerId = 'drawer-' + parentRow.dataset.productId;
            const drawerRow = document.getElementById(drawerId);
            const arrowIcon = parentRow.querySelector('.arrow-icon');

            if (!drawerRow) return;

            const isVisible = window.getComputedStyle(drawerRow).display !== 'none';
            closeDrawers(isVisible ? null : drawerId);
            drawerRow.style.display = isVisible ? 'none' : 'table-row';
            parentRow.querySelectorAll('.js-toggle-drawer').forEach(item => item.setAttribute('aria-expanded', String(!isVisible)));
            if (arrowIcon) arrowIcon.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(90deg)';
        }

        togglers.forEach(btn => {
            btn.addEventListener('click', function() {
                toggleDrawer(this);
            });

            btn.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleDrawer(this);
                }
            });
        });

        const productFilterForm = document.getElementById('product-filter-form');
        const productSearchInput = document.getElementById('search');
        const productFilterSelects = productFilterForm?.querySelectorAll('select');
        let productSearchTimer;
        const submitProductFilters = () => {
            if (!productFilterForm) return;
            productFilterForm.classList.add('is-filtering');
            productFilterForm.submit();
        };
        productSearchInput?.addEventListener('input', () => {
            clearTimeout(productSearchTimer);
            productSearchTimer = setTimeout(submitProductFilters, 350);
        });
        productFilterSelects?.forEach((select) => select.addEventListener('change', submitProductFilters));

        const exportModal = document.getElementById('export-product-modal');
        const openExportButton = document.getElementById('open-export-modal');
        const cancelExportButton = document.getElementById('cancel-product-export');
        const exportForm = document.getElementById('product-export-form');
        const submitExportButton = document.getElementById('submit-product-export');
        const submitExportLabel = submitExportButton.querySelector('span');

        function closeExportModal() {
            exportModal.hidden = true;
            exportModal.setAttribute('aria-hidden', 'true');
            openExportButton.focus();
        }

        openExportButton.addEventListener('click', function() {
            exportModal.hidden = false;
            exportModal.setAttribute('aria-hidden', 'false');
            cancelExportButton.focus();
        });

        cancelExportButton.addEventListener('click', closeExportModal);
        exportModal.addEventListener('click', function(event) {
            if (event.target === exportModal) closeExportModal();
        });

        exportForm.addEventListener('submit', function() {
            submitExportButton.disabled = true;
            submitExportLabel.textContent = 'Đang tạo file...';

            window.setTimeout(function() {
                submitExportButton.disabled = false;
                submitExportLabel.textContent = 'Xuất Excel';
                closeExportModal();
            }, 2500);
        });

        const modal = document.getElementById('hide-product-modal');
        const productName = document.getElementById('hide-product-name');
        const cancelButton = document.getElementById('cancel-hide-product');
        const confirmButton = document.getElementById('confirm-hide-product');
        let pendingForm = null;

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            pendingForm = null;
        }

        document.querySelectorAll('.js-open-hide-modal').forEach(button => {
            button.addEventListener('click', function() {
                pendingForm = this.closest('form');
                productName.textContent = this.dataset.productName;
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                cancelButton.focus();
            });
        });

        cancelButton.addEventListener('click', closeModal);
        confirmButton.addEventListener('click', function() {
            if (pendingForm) pendingForm.submit();
        });
        modal.addEventListener('click', function(event) {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !exportModal.hidden) closeExportModal();
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
    });
</script>
@endsection
