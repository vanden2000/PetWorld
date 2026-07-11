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
        color: var(--variant-text);
        font-size: 1.8rem;
        font-weight: 800;
    }

    .variant-page-head p {
        color: var(--variant-muted);
        font-size: 0.92rem;
        margin-top: 4px;
    }

    .variant-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .variant-stat-box,
    .variant-panel,
    .variant-type-card {
        background: #ffffff;
        border: 1px solid var(--variant-border);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .variant-stat-box {
        padding: 16px;
    }

    .variant-stat-box span {
        color: var(--variant-muted);
        display: block;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .variant-stat-box strong {
        color: var(--variant-text);
        display: block;
        font-size: 1.55rem;
        font-weight: 800;
        margin-top: 6px;
    }

    .variant-grid {
        display: grid;
        grid-template-columns: minmax(300px, 0.9fr) minmax(0, 1.7fr);
        gap: 20px;
        align-items: start;
    }

    .variant-panel {
        padding: 20px;
    }

    .variant-panel-title {
        color: var(--variant-text);
        font-size: 1.05rem;
        font-weight: 800;
        margin-bottom: 16px;
    }

    .variant-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin-bottom: 14px;
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
        border-radius: 6px;
        color: var(--variant-text);
        font-family: inherit;
        font-size: 0.9rem;
        outline: none;
        padding: 10px 12px;
        width: 100%;
    }

    .variant-input:focus,
    .variant-select:focus {
        border-color: var(--variant-primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
    }

    .variant-btn {
        align-items: center;
        border: 0;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        font-size: 0.86rem;
        font-weight: 800;
        gap: 8px;
        justify-content: center;
        padding: 10px 14px;
        text-decoration: none;
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

    .variant-btn-icon {
        height: 36px;
        padding: 0;
        width: 36px;
    }

    .variant-filter {
        display: grid;
        grid-template-columns: 1fr 160px auto;
        gap: 12px;
        margin-bottom: 18px;
    }

    .variant-type-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .variant-type-card {
        overflow: hidden;
    }

    .variant-type-head {
        align-items: center;
        background: #ffffff;
        border-bottom: 1px solid var(--variant-border);
        display: grid;
        grid-template-columns: 1fr 150px auto;
        gap: 12px;
        padding: 16px;
    }

    .variant-type-name {
        align-items: center;
        display: flex;
        gap: 10px;
        min-width: 0;
    }

    .variant-type-icon {
        align-items: center;
        background: var(--variant-primary-light);
        border-radius: 6px;
        color: var(--variant-primary);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        width: 36px;
    }

    .variant-type-name strong {
        color: var(--variant-text);
        display: block;
        font-size: 1rem;
        font-weight: 800;
    }

    .variant-type-name span {
        color: var(--variant-muted);
        display: block;
        font-size: 0.78rem;
        margin-top: 2px;
    }

    .variant-status {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 800;
        justify-content: center;
        padding: 5px 10px;
    }

    .variant-status.active {
        background: rgba(22, 163, 74, 0.1);
        color: var(--variant-success);
    }

    .variant-status.inactive {
        background: rgba(245, 158, 11, 0.12);
        color: var(--variant-warning);
    }

    .variant-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .variant-values {
        padding: 16px;
    }

    .variant-value-row {
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        display: grid;
        grid-template-columns: 1fr 130px auto auto;
        gap: 10px;
        padding: 10px 0;
    }

    .variant-value-row:first-child {
        padding-top: 0;
    }

    .variant-value-row:last-child {
        border-bottom: 0;
    }

    .variant-value-used {
        color: var(--variant-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .variant-add-value {
        align-items: center;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        margin-top: 14px;
    }

    .variant-empty {
        color: var(--variant-muted);
        padding: 26px;
        text-align: center;
    }

    .variant-alert {
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 16px;
        padding: 12px 14px;
    }

    .variant-alert.success {
        background: var(--variant-primary-light);
        border: 1px solid rgba(255, 120, 45, 0.18);
        color: var(--variant-primary);
    }

    .variant-alert.error {
        background: #fef2f2;
        border: 1px solid rgba(239, 68, 68, 0.18);
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
        margin-top: 18px;
    }

    .variant-page-links {
        display: flex;
        gap: 6px;
    }

    .variant-page-link {
        align-items: center;
        border: 1px solid var(--variant-border);
        border-radius: 4px;
        color: var(--variant-muted);
        display: flex;
        font-size: 0.84rem;
        font-weight: 800;
        height: 34px;
        justify-content: center;
        text-decoration: none;
        width: 34px;
    }

    .variant-page-link.active {
        background: var(--variant-primary);
        border-color: var(--variant-primary);
        color: #ffffff;
    }

    @media (max-width: 1060px) {
        .variant-grid,
        .variant-stats {
            grid-template-columns: 1fr;
        }

        .variant-filter,
        .variant-type-head,
        .variant-value-row {
            grid-template-columns: 1fr;
        }

        .variant-actions {
            justify-content: flex-start;
        }
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

    <div class="variant-stats">
        <div class="variant-stat-box">
            <span>Thuộc tính</span>
            <strong>{{ number_format($totalTypes, 0, ',', '.') }}</strong>
        </div>
        <div class="variant-stat-box">
            <span>Giá trị</span>
            <strong>{{ number_format($totalValues, 0, ',', '.') }}</strong>
        </div>
        <div class="variant-stat-box">
            <span>Đang dùng</span>
            <strong>{{ number_format($usedValues, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="variant-grid">
        <div class="variant-panel">
            <div class="variant-panel-title">Thêm thuộc tính</div>
            <form action="{{ route('admin.products.variants.types.store') }}" method="POST">
                @csrf

                <div class="variant-field">
                    <label for="name">Tên thuộc tính</label>
                    <input id="name" name="name" class="variant-input" value="{{ old('name') }}" placeholder="Ví dụ: Khối lượng" required>
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
                    <input id="values" name="values" class="variant-input" value="{{ old('values') }}" placeholder="1kg, 2kg, 5kg">
                </div>

                <button type="submit" class="variant-btn variant-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm thuộc tính
                </button>
            </form>
        </div>

        <div>
            <div class="variant-panel" style="margin-bottom: 14px;">
                <form action="{{ route('admin.products.variants') }}" method="GET" class="variant-filter">
                    <input name="search" value="{{ $search }}" class="variant-input" placeholder="Tìm theo thuộc tính hoặc giá trị">
                    <select name="status" class="variant-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Đang dùng</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                    <button type="submit" class="variant-btn variant-btn-soft">
                        <i class="fa-solid fa-sliders"></i>
                        Lọc
                    </button>
                </form>
            </div>

            <div class="variant-type-list">
                @forelse($variantTypes as $variantType)
                    @php
                        $usedTypeValues = $variantType->values->filter(fn ($value) => $value->productVariants->isNotEmpty())->count();
                    @endphp

                    <div class="variant-type-card">
                        <div class="variant-type-head">
                            <div class="variant-type-name">
                                <span class="variant-type-icon"><i class="fa-solid fa-layer-group"></i></span>
                                <div>
                                    <strong>{{ $variantType->name }}</strong>
                                    <span>{{ $variantType->values_count }} giá trị, {{ $usedTypeValues }} đang dùng</span>
                                </div>
                            </div>

                            <span class="variant-status {{ $variantType->status }}">
                                {{ $variantType->status === 'active' ? 'Đang dùng' : 'Ẩn' }}
                            </span>

                            <div class="variant-actions">
                                <form action="{{ route('admin.products.variants.types.update', $variantType) }}" method="POST" style="display: contents;">
                                    @csrf
                                    @method('PUT')
                                    <input name="name" class="variant-input" value="{{ $variantType->name }}" required>
                                    <select name="status" class="variant-select" required>
                                        <option value="active" {{ $variantType->status === 'active' ? 'selected' : '' }}>Đang dùng</option>
                                        <option value="inactive" {{ $variantType->status === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                                    </select>
                                    <button type="submit" class="variant-btn variant-btn-soft variant-btn-icon" title="Lưu thuộc tính">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.products.variants.types.destroy', $variantType) }}" method="POST" onsubmit="return confirm('Xóa thuộc tính này? Các giá trị chưa dùng cũng sẽ bị xóa.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="variant-btn variant-btn-danger variant-btn-icon" title="Xóa thuộc tính">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="variant-values">
                            @forelse($variantType->values->sortBy('value') as $value)
                                <div class="variant-value-row">
                                    <form action="{{ route('admin.products.variants.values.update', $value) }}" method="POST" style="display: contents;">
                                        @csrf
                                        @method('PUT')
                                        <input name="value" class="variant-input" value="{{ $value->value }}" required>
                                        <span class="variant-value-used">
                                            {{ $value->productVariants->count() }} SKU
                                        </span>
                                        <button type="submit" class="variant-btn variant-btn-soft variant-btn-icon" title="Lưu giá trị">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.products.variants.values.destroy', $value) }}" method="POST" onsubmit="return confirm('Xóa giá trị biến thể này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="variant-btn variant-btn-danger variant-btn-icon" title="Xóa giá trị" {{ $value->productVariants->isNotEmpty() ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="variant-empty">Chưa có giá trị cho thuộc tính này.</div>
                            @endforelse

                            <form action="{{ route('admin.products.variants.values.store', $variantType) }}" method="POST" class="variant-add-value">
                                @csrf
                                <input name="value" class="variant-input" placeholder="Thêm giá trị mới, ví dụ: 10kg" required>
                                <button type="submit" class="variant-btn variant-btn-primary">
                                    <i class="fa-solid fa-plus"></i>
                                    Thêm
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="variant-panel variant-empty">
                        Không tìm thấy thuộc tính biến thể nào.
                    </div>
                @endforelse
            </div>

            @if($variantTypes->hasPages())
                <div class="variant-pagination">
                    <span class="variant-value-used">
                        Hiển thị {{ $variantTypes->firstItem() }}-{{ $variantTypes->lastItem() }} trên {{ $variantTypes->total() }}
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
@endsection
