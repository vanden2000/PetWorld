@extends('admin.layouts.app')

@section('title', 'Quản lý biến thể')

@section('styles')
<style>
    :root {
        --variant-primary: var(--primary);
        --variant-primary-hover: var(--primary-hover);
        --variant-primary-light: rgba(255, 120, 45, 0.08);
        --variant-border: var(--border-color);
        --variant-bg: var(--bg-color);
        --variant-text: var(--text-main);
        --variant-muted: var(--text-muted);
        --variant-danger: var(--danger);
        --variant-success: var(--success);
        --variant-warning: var(--warning);
    }

    .variant-page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .variant-page-head h1 {
        color: var(--primary);
        font-size: 1.8rem;
        font-weight: 800;
    }

    .variant-page-head p {
        color: var(--variant-muted);
        font-size: 0.92rem;
        margin-top: 4px;
    }

    /* Metric Cards */
    .variant-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .variant-stat-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    
    .variant-stat-box::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--variant-primary);
        border-radius: 4px 0 0 4px;
    }
    .variant-stat-box:nth-child(2)::before { background: #0284c7; }
    .variant-stat-box:nth-child(3)::before { background: #16734a; }

    .variant-stat-box:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    .variant-stat-box:first-child:hover { background: #fffdfa; border-color: rgba(255, 120, 45, 0.2); }
    .variant-stat-box:nth-child(2):hover { background: #f0f9ff; border-color: rgba(2, 132, 199, 0.2); }
    .variant-stat-box:nth-child(3):hover { background: #f6fcf9; border-color: rgba(22, 115, 74, 0.2); }

    .variant-stat-info { display: flex; flex-direction: column; }
    .variant-stat-label {
        display: block;
        color: var(--variant-muted);
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .variant-stat-value {
        display: block;
        color: var(--variant-text);
        font-size: 1.6rem;
        line-height: 1.15;
        font-weight: 800;
        margin-top: 6px;
    }
    .variant-stat-icon {
        font-size: 1.8rem;
        opacity: 0.15;
        transition: all 0.25s ease;
        color: var(--variant-primary);
    }
    .variant-stat-box:nth-child(2) .variant-stat-icon { color: #0284c7; }
    .variant-stat-box:nth-child(3) .variant-stat-icon { color: #16734a; }

    .variant-stat-box:hover .variant-stat-icon {
        opacity: 0.95;
        transform: scale(1.18);
    }

    @media (max-width: 768px) {
        .variant-stats {
            grid-template-columns: 1fr;
        }
    }

    /* 2 Column Grid */
    .variant-grid {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 24px;
        align-items: stretch;
    }
    @media (max-width: 1024px) {
        .variant-grid {
            grid-template-columns: 1fr;
            align-items: start;
        }
    }

    .variant-panel {
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        padding: 24px;
        height: 100%;
        box-sizing: border-box;
    }

    .variant-panel-title {
        color: var(--variant-text);
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .variant-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin-bottom: 16px;
    }

    .variant-field label {
        color: var(--variant-muted);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .variant-input,
    .variant-select {
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 10px;
        color: var(--variant-text);
        font-family: inherit;
        font-size: 0.88rem;
        outline: none;
        padding: 10px 14px;
        width: 100%;
        box-sizing: border-box;
        transition: var(--transition);
        height: 38px;
    }

    .variant-input:focus,
    .variant-select:focus {
        border-color: var(--variant-primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
    }

    .variant-btn {
        align-items: center;
        border: 0;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        font-size: 0.86rem;
        font-weight: 700;
        gap: 8px;
        justify-content: center;
        padding: 10px 16px;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .variant-btn-primary {
        background: var(--variant-primary);
        color: #ffffff;
    }

    .variant-btn-primary:hover {
        background: var(--variant-primary-hover);
    }

    .variant-btn-soft {
        background: var(--variant-primary-light);
        color: var(--variant-primary);
    }

    .variant-btn-danger {
        background: #fef2f2;
        color: var(--variant-danger);
    }

    /* Filter Bar */
    .variant-filter {
        display: grid;
        grid-template-columns: 1fr 150px auto auto;
        gap: 12px;
        align-items: center;
    }
    @media (max-width: 768px) {
        .variant-filter {
            grid-template-columns: 1fr;
        }
    }

    .variant-filter-wrap {
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: var(--shadow-subtle);
        margin-bottom: 20px;
    }

    .pl-search-wrap { position: relative; width: 100%; }
    .pl-search-wrap i {
        position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
        color: var(--variant-muted); font-size: 0.85rem; pointer-events: none;
    }
    .pl-search-wrap .variant-input { padding-left: 36px; }

    /* Custom Table Styles */
    .variant-table-container {
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow-subtle);
    }

    .variant-table {
        width: 100%;
        border-collapse: collapse;
    }

    .variant-table th {
        background: #fafbfc;
        padding: 14px 18px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--variant-muted);
        border-bottom: 1px solid var(--variant-border);
        text-align: left;
    }

    .variant-table td {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.88rem;
        color: var(--variant-text);
    }

    .variant-table tbody tr.js-variant-table-row {
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .variant-table tbody tr.js-variant-table-row:hover {
        background: #fff8f3;
    }

    .variant-table tbody tr.js-variant-table-row.is-active-row {
        background: #fff8f3;
    }

    .variant-status {
        align-items: center;
        border-radius: 20px;
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 700;
        justify-content: center;
        padding: 4px 10px;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .variant-status.active {
        background: rgba(22, 163, 74, 0.08);
        color: var(--variant-success);
        border-color: rgba(22, 163, 74, 0.15);
    }

    .variant-status.inactive {
        background: rgba(107, 114, 128, 0.08);
        color: var(--variant-muted);
        border-color: rgba(107, 114, 128, 0.15);
    }

    /* Drawer values manager inside table */
    .variant-drawer td {
        background: #fafbfc;
        padding: 24px 30px !important;
        border-bottom: 1px solid var(--variant-border);
    }

    .variant-drawer[hidden] {
        display: none;
    }

    .value-manager-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .value-manager-title {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--variant-text);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .value-manager-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .value-item-card {
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 8px;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }

    .value-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }

    .value-item-card.is-editing {
        border-color: var(--variant-primary);
        background: #fffdfb;
    }

    .value-text {
        font-weight: 600;
        color: var(--variant-text);
        font-size: 0.86rem;
    }

    .value-sku {
        font-size: 0.76rem;
        color: var(--variant-muted);
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }

    .value-display-mode {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-edit-value, .btn-delete-value, .btn-save-value, .btn-cancel-value {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.78rem;
        padding: 4px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .btn-edit-value { color: #0284c7; }
    .btn-edit-value:hover { background: #e0f2fe; }
    .btn-delete-value { color: #ef4444; }
    .btn-delete-value:hover { background: #fee2e2; }
    .btn-delete-value.is-disabled {
        color: #94a3b8;
        cursor: not-allowed;
    }
    .btn-delete-value.is-disabled:hover { background: none; }

    .value-edit-mode-form {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .value-edit-input {
        border: 1px solid var(--variant-border);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 0.82rem;
        color: var(--variant-text);
        width: 100px;
        outline: none;
        box-sizing: border-box;
    }

    .value-edit-input:focus {
        border-color: var(--variant-primary);
    }

    .btn-save-value { color: #16a34a; }
    .btn-save-value:hover { background: #dcfce7; }
    .btn-cancel-value { color: #64748b; }
    .btn-cancel-value:hover { background: #f1f5f9; }

    .value-manager-add-form {
        max-width: 480px;
        margin-top: 8px;
    }

    .value-manager-add-form .input-group {
        display: flex;
        gap: 8px;
    }

    .value-manager-add-form .pl-input {
        flex: 1;
        height: 38px;
        box-sizing: border-box;
        border: 1px solid var(--variant-border);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 0.88rem;
        outline: none;
    }
    .value-manager-add-form .pl-input:focus {
        border-color: var(--variant-primary);
    }

    /* Modal */
    .pl-modal {
        position: fixed; inset: 0; z-index: 999;
        display: none; align-items: center; justify-content: center;
        padding: 20px;
        background: rgba(15, 30, 25, 0.55);
        backdrop-filter: blur(3px);
    }
    .pl-modal.is-open { display: flex; }
    .pl-modal-box {
        width: min(400px, 100%);
        background: #ffffff;
        border-radius: 16px;
        padding: 26px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    }
    .pl-modal-box h3 { font-size: 1.15rem; font-weight: 800; color: var(--variant-text); margin-bottom: 8px; }
    .pl-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }

    .pl-btn-primary {
        background: var(--variant-primary);
        color: #ffffff;
    }
    .pl-btn-primary:hover {
        background: var(--variant-primary-hover);
    }
    .pl-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid var(--variant-border);
        font-size: 0.86rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .variant-alert {
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 20px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .variant-alert.success {
        background: rgba(22, 163, 74, 0.08);
        border: 1px solid rgba(22, 163, 74, 0.15);
        color: var(--variant-success);
    }

    .variant-alert.error {
        background: #fef2f2;
        border: 1px solid rgba(239, 68, 68, 0.15);
        color: var(--variant-danger);
    }

    .variant-errors {
        margin: 0;
        padding-left: 18px;
    }

    .variant-pagination {
        align-items: center;
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .variant-page-links {
        display: flex;
        gap: 6px;
    }

    .variant-page-link {
        align-items: center;
        border: 1px solid var(--variant-border);
        border-radius: 6px;
        color: var(--variant-muted);
        display: flex;
        font-size: 0.84rem;
        font-weight: 700;
        height: 34px;
        justify-content: center;
        text-decoration: none;
        width: 34px;
        background: #ffffff;
        transition: all 0.2s;
    }

    .variant-page-link.active {
        background: var(--variant-primary);
        border-color: var(--variant-primary);
        color: #ffffff;
    }
    .variant-page-link:hover:not(.active) {
        border-color: var(--variant-primary);
        color: var(--variant-primary);
    }
</style>
@endsection

@section('content')
    <div class="variant-page-head">
        <div>
            <h1>Quản lý biến thể</h1>
            <p>Quản lý thuộc tính và giá trị dùng để tạo SKU cho sản phẩm.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="variant-alert success">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="variant-alert error">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="variant-alert error">
            <ul class="variant-errors">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stats cards --}}
    <div class="variant-stats">
        <div class="variant-stat-box">
            <div class="variant-stat-info">
                <div class="variant-stat-value">{{ number_format($totalTypes, 0, ',', '.') }}</div>
                <div class="variant-stat-label">Thuộc tính</div>
            </div>
            <div class="variant-stat-icon"><i class="fa-solid fa-layer-group"></i></div>
        </div>
        <div class="variant-stat-box">
            <div class="variant-stat-info">
                <div class="variant-stat-value">{{ number_format($totalValues, 0, ',', '.') }}</div>
                <div class="variant-stat-label">Giá trị</div>
            </div>
            <div class="variant-stat-icon"><i class="fa-solid fa-tags"></i></div>
        </div>
        <div class="variant-stat-box">
            <div class="variant-stat-info">
                <div class="variant-stat-value">{{ number_format($usedValues, 0, ',', '.') }}</div>
                <div class="variant-stat-label">Đang dùng</div>
            </div>
            <div class="variant-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        </div>
    </div>

    <div class="variant-grid">
        {{-- Left column: add new form --}}
        <div class="variant-panel">
            <div class="variant-panel-title">Thêm thuộc tính mới</div>
            <form action="{{ route('admin.products.variants.types.store') }}" method="POST">
                @csrf

                <div class="variant-field">
                    <label for="name">Tên thuộc tính</label>
                    <input id="name" name="name" class="variant-input" value="{{ old('name') }}" placeholder="Ví dụ: Kích thước, Màu sắc..." required>
                </div>

                <div class="variant-field">
                    <label for="status-new">Trạng thái</label>
                    <select id="status-new" name="status" class="variant-select" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Đang dùng</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>

                <div class="variant-field">
                    <label for="values">Giá trị ban đầu</label>
                    <input id="values" name="values" class="variant-input" value="{{ old('values') }}" placeholder="Ví dụ: Đỏ, Xanh, Vàng">
                    <span style="font-size: 0.72rem; color: var(--variant-muted); margin-top: 3px; display: block;">Các giá trị cách nhau bằng dấu phẩy.</span>
                </div>

                <button type="submit" class="variant-btn variant-btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fa-solid fa-plus"></i>
                    Thêm thuộc tính
                </button>
            </form>
        </div>

        {{-- Right column: filters + table list --}}
        <div>
            <div class="variant-filter-wrap">
                <form action="{{ route('admin.products.variants') }}" method="GET" class="variant-filter">
                    <div class="pl-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input name="search" value="{{ $search }}" class="variant-input" placeholder="Tìm theo thuộc tính hoặc giá trị...">
                    </div>
                    <select name="status" class="variant-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Đang dùng</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                    <button type="submit" class="variant-btn variant-btn-primary">
                        <i class="fa-solid fa-filter"></i>
                        Lọc
                    </button>
                    <a href="{{ route('admin.products.variants') }}" class="variant-btn variant-btn-soft">
                        <i class="fa-solid fa-rotate-left"></i>
                        Xóa lọc
                    </a>
                </form>
            </div>

            <div class="variant-table-container">
                <table class="variant-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Thuộc tính</th>
                            <th style="width: 35%;">Giá trị</th>
                            <th style="width: 15%;">Liên kết</th>
                            <th style="width: 13%;">Trạng thái</th>
                            <th style="width: 12%; text-align: right;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($variantTypes as $variantType)
                        @php($used = $variantType->values->sum(fn($value) => $value->productVariants->count()))
                        <tr class="js-variant-table-row" data-drawer="drawer-{{ $variantType->id }}">
                            <td>
                                <strong style="color: var(--variant-text); font-weight: 700; font-size: 0.95rem;">{{ $variantType->name }}</strong>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                    @forelse($variantType->values->sortBy('value')->take(5) as $val)
                                        <span style="font-size: 0.76rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 12px; font-weight: 500;">
                                            {{ $val->value }}
                                        </span>
                                    @empty
                                        <span style="font-size: 0.78rem; color: var(--variant-muted); font-style: italic;">Chưa có giá trị</span>
                                    @endforelse
                                    @if($variantType->values->count() > 5)
                                        <span style="font-size: 0.76rem; background: var(--variant-primary-light); color: var(--variant-primary); padding: 2px 8px; border-radius: 12px; font-weight: 700;">
                                            +{{ $variantType->values->count() - 5 }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; color: #475569; font-weight: 600;">
                                    {{ $used }} SKU
                                </span>
                            </td>
                            <td>
                                <span class="variant-status {{ $variantType->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ $variantType->status === 'active' ? 'Đang dùng' : 'Ẩn' }}
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: flex-end; width: 100%;">
                                    <!-- Toggle values drawer -->
                                    <button type="button" class="variant-btn variant-btn-soft variant-btn-icon js-toggle-drawer" 
                                            data-drawer="drawer-{{ $variantType->id }}" title="Quản lý giá trị"
                                            style="width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--variant-border); background: #ffffff;">
                                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; transition: transform 0.2s; color: var(--variant-muted);"></i>
                                    </button>

                                    <!-- Edit attribute -->
                                    <button type="button" class="variant-btn variant-btn-soft variant-btn-icon" 
                                            title="Sửa thuộc tính" onclick="openEditAttrModal({{ $variantType->id }}, '{{ addslashes($variantType->name) }}', '{{ $variantType->status }}')"
                                            style="width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--variant-border); background: #ffffff; color: var(--variant-primary);">
                                        <i class="fa-solid fa-pen-to-square" style="font-size: 0.85rem;"></i>
                                    </button>

                                    <!-- Delete attribute -->
                                    <form action="{{ route('admin.products.variants.types.destroy', $variantType) }}" method="POST" 
                                          onsubmit="return confirm('Xóa thuộc tính này? Tất cả các giá trị của thuộc tính cũng sẽ bị xóa.');"
                                          style="display:inline-block; margin: 0; padding: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="variant-btn variant-btn-danger variant-btn-icon" 
                                                title="Xóa thuộc tính"
                                                style="width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #ebdcd0; background: #ffffff; color: var(--variant-danger);"
                                                {{ $used > 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                                            <i class="fa-solid fa-trash-can" style="font-size: 0.85rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        {{-- Drawer Row --}}
                        <tr id="drawer-{{ $variantType->id }}" class="variant-drawer" hidden>
                            <td colspan="5">
                                <div class="value-manager-container">
                                    <h4 class="value-manager-title">
                                        <i class="fa-solid fa-tags" style="color: var(--variant-primary);"></i>
                                        Danh sách giá trị của thuộc tính "{{ $variantType->name }}" ({{ $variantType->values->count() }})
                                    </h4>
                                    
                                    <div class="value-manager-list">
                                        @forelse($variantType->values->sortBy('value') as $value)
                                            <div class="value-item-card" id="value-card-{{ $value->id }}">
                                                <!-- Display Mode -->
                                                <div class="value-display-mode">
                                                    <span class="value-text">{{ $value->value }}</span>
                                                    <span class="value-sku">{{ $value->productVariants->count() }} SKU</span>
                                                    <button type="button" class="btn-edit-value" title="Sửa tên" onclick="startEditValue({{ $value->id }})">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    @if($value->productVariants->isEmpty())
                                                        <form action="{{ route('admin.products.variants.values.destroy', $value) }}" method="POST" style="display:inline-block; margin: 0; padding: 0;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-delete-value" title="Xóa giá trị" onclick="return confirm('Bạn có chắc chắn muốn xóa giá trị này?')">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="btn-delete-value is-disabled" title="Đang liên kết với sản phẩm (không thể xóa)"><i class="fa-solid fa-lock"></i></span>
                                                    @endif
                                                </div>
                                                <!-- Edit Mode -->
                                                <form action="{{ route('admin.products.variants.values.update', $value) }}" method="POST" class="value-edit-mode-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="value" class="value-edit-input" value="{{ $value->value }}" required>
                                                    <button type="submit" class="btn-save-value" title="Lưu"><i class="fa-solid fa-check"></i></button>
                                                    <button type="button" class="btn-cancel-value" title="Hủy" onclick="cancelEditValue({{ $value->id }})"><i class="fa-solid fa-xmark"></i></button>
                                                </form>
                                            </div>
                                        @empty
                                            <div style="font-size: 0.86rem; color: var(--variant-muted); font-style: italic;">Chưa có giá trị nào. Hãy thêm giá trị mới ở dưới.</div>
                                        @endforelse
                                    </div>
                                    
                                    <!-- Add value form -->
                                    <form action="{{ route('admin.products.variants.values.store', $variantType) }}" method="POST" class="value-manager-add-form">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" name="value" class="pl-input" placeholder="Nhập giá trị mới (ví dụ: XL, Nhỏ, Đỏ)..." required>
                                            <button type="submit" class="pl-btn pl-btn-primary">
                                                <i class="fa-solid fa-plus"></i> Thêm giá trị
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--variant-muted);">
                                <i class="fa-regular fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                                Không tìm thấy thuộc tính biến thể nào.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($variantTypes->hasPages())
                <div class="variant-pagination">
                    <span style="font-size: 0.82rem; color: var(--variant-muted); font-weight: 500;">
                        Hiển thị <strong style="color: var(--variant-text);">{{ $variantTypes->firstItem() }}-{{ $variantTypes->lastItem() }}</strong> trên <strong style="color: var(--variant-text);">{{ $variantTypes->total() }}</strong> thuộc tính
                    </span>
                    <div class="variant-page-links">
                        @foreach($variantTypes->getUrlRange(1, $variantTypes->lastPage()) as $page => $url)
                            <a href="{{ $url }}" class="variant-page-link {{ $variantTypes->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Edit Attribute Modal --}}
    <div class="pl-modal" id="edit-attribute-modal" role="dialog" aria-modal="true" aria-labelledby="edit-attr-heading">
        <div class="pl-modal-box">
            <h3 id="edit-attr-heading">Chỉnh sửa thuộc tính</h3>
            <form method="POST" id="edit-attribute-form">
                @csrf
                @method('PUT')
                
                <div class="variant-field" style="margin-top: 15px;">
                    <label for="edit-attr-name">Tên thuộc tính</label>
                    <input type="text" id="edit-attr-name" name="name" class="variant-input" required>
                </div>
                
                <div class="variant-field" style="margin-top: 15px;">
                    <label for="edit-attr-status">Trạng thái</label>
                    <select id="edit-attr-status" name="status" class="variant-select" required>
                        <option value="active">Đang dùng</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>

                <div class="pl-modal-actions" style="margin-top: 25px;">
                    <button type="button" class="pl-btn" id="edit-attr-cancel">Hủy</button>
                    <button type="submit" class="pl-btn pl-btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle drawers
    document.querySelectorAll('.js-toggle-drawer').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const drawerId = button.getAttribute('data-drawer');
            const drawer = document.getElementById(drawerId);
            const row = button.closest('tr');
            const icon = button.querySelector('i');
            
            const isHidden = drawer.hidden;
            
            // Close other drawers
            document.querySelectorAll('.variant-drawer').forEach(item => {
                if (item !== drawer) {
                    item.hidden = true;
                    const otherBtn = document.querySelector(`[data-drawer="${item.id}"]`);
                    if (otherBtn) {
                        otherBtn.querySelector('i').style.transform = '';
                    }
                    item.previousElementSibling.classList.remove('is-active-row');
                }
            });
            
            if (isHidden) {
                drawer.hidden = false;
                icon.style.transform = 'rotate(180deg)';
                row.classList.add('is-active-row');
            } else {
                drawer.hidden = true;
                icon.style.transform = '';
                row.classList.remove('is-active-row');
            }
        });
    });

    // Make table row clickable to open drawer
    document.querySelectorAll('.js-variant-table-row').forEach(row => {
        row.addEventListener('click', (e) => {
            if (e.target.closest('button, a, form, input, select')) return;
            const toggleBtn = row.querySelector('.js-toggle-drawer');
            if (toggleBtn) {
                toggleBtn.click();
            }
        });
    });

    // Edit Attribute Modal handlers
    const editModal = document.getElementById('edit-attribute-modal');
    const editForm = document.getElementById('edit-attribute-form');
    const editNameInput = document.getElementById('edit-attr-name');
    const editStatusSelect = document.getElementById('edit-attr-status');

    window.openEditAttrModal = function(id, name, status) {
        editForm.action = `/admin/products/variants/types/${id}`;
        editNameInput.value = name;
        editStatusSelect.value = status;
        editModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    document.getElementById('edit-attr-cancel').addEventListener('click', () => {
        editModal.classList.remove('is-open');
        document.body.style.overflow = '';
    });

    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            editModal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    });

    // Inline edit value cards handlers
    window.startEditValue = function(id) {
        const card = document.getElementById(`value-card-${id}`);
        if (card) {
            // Cancel other edits
            document.querySelectorAll('.value-item-card.is-editing').forEach(c => {
                c.classList.remove('is-editing');
            });
            card.classList.add('is-editing');
            card.querySelector('.value-edit-input').focus();
        }
    };

    window.cancelEditValue = function(id) {
        const card = document.getElementById(`value-card-${id}`);
        if (card) {
            card.classList.remove('is-editing');
        }
    };
});
</script>
@endsection
