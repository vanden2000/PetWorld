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
        grid-template-columns: 1fr;
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
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .variant-type-card {
        overflow: hidden;
    }

    .variant-type-head {
        align-items: center;
        background: #ffffff;
        border-bottom: 1px solid var(--variant-border);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        padding: 16px;
        cursor: pointer;
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
        display: none;
    }

    .variant-type-card.is-open .variant-values { display: block; }
    .variant-type-card.is-open .variant-type-head { background: var(--variant-primary-light); }
    .variant-type-card .variant-type-head::after { content: '\f078'; font-family: "Font Awesome 6 Free"; font-weight: 900; color: var(--variant-muted); }
    .variant-type-card.is-open .variant-type-head::after { transform: rotate(180deg); }
    .variant-panel:first-child { max-width: 760px; }

    .variant-values-list { display: grid; gap: 6px; }

    .variant-value-row {
        align-items: center;
        border: 1px solid #edf0ee;
        border-radius: 8px;
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 8px;
        padding: 9px 10px;
        background: #fff;
    }

    .variant-value-name { color: var(--variant-text); font-weight: 750; }
    .variant-value-edit-form { display: none; grid-column: 1 / -1; grid-template-columns: 1fr auto; gap: 8px; padding-top: 8px; border-top: 1px solid #edf0ee; }
    .variant-value-row.is-editing .variant-value-edit-form { display: grid; }
    .variant-value-row .variant-input { border: 0; background: transparent; padding: 2px; width: 92px; }
    .variant-value-row .variant-btn-icon { height: 28px; width: 28px; }

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
        .variant-type-head {
            grid-template-columns: 1fr;
        }

        .variant-type-list { grid-template-columns: 1fr; }

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
                            <div class="variant-values-list">
                            @forelse($variantType->values->sortBy('value') as $value)
                                <div class="variant-value-row">
                                    <span class="variant-value-name">{{ $value->value }}</span>
                                    <span class="variant-value-used">{{ $value->productVariants->count() }} SKU</span>
                                    <button type="button" class="variant-btn variant-btn-soft variant-btn-icon js-toggle-value-edit" title="Sửa giá trị"><i class="fa-solid fa-pen"></i></button>

                                    <form action="{{ route('admin.products.variants.values.destroy', $value) }}" method="POST" onsubmit="return confirm('Xóa giá trị biến thể này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="variant-btn variant-btn-danger variant-btn-icon" title="Xóa giá trị" {{ $value->productVariants->isNotEmpty() ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.products.variants.values.update', $value) }}" method="POST" class="variant-value-edit-form">
                                        @csrf
                                        @method('PUT')
                                        <input name="value" class="variant-input" value="{{ $value->value }}" required>
                                        <button type="submit" class="variant-btn variant-btn-primary">Lưu</button>
                                    </form>
                                </div>
                            @empty
                                <div class="variant-empty">Chưa có giá trị cho thuộc tính này.</div>
                            @endforelse
                            </div>

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

            <style>
                .variant-type-list { display:none; }
                .variant-table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border:1px solid var(--variant-border); border-radius:10px; overflow:hidden; }
                .variant-table th,.variant-table td { padding:16px; border-bottom:1px solid #eef0ef; text-align:left; }
                .variant-table th { background:#f6f8f7; color:var(--variant-muted); font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }
                .js-variant-table-row { cursor:pointer; transition:background .15s; }.js-variant-table-row:hover{background:#fff7f2}.js-variant-table-row td:first-child{font-size:.95rem;color:var(--variant-text)}
                .variant-drawer[hidden]{display:none}.variant-drawer td{background:#fffaf6;border-left:3px solid var(--variant-primary);padding:18px 22px}.variant-drawer-values{display:grid;gap:0;max-width:620px}
                .variant-drawer-value{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f0e5dc}.variant-drawer-value span:last-child{font-size:.78rem;font-weight:800;color:var(--variant-primary);background:var(--variant-primary-light);padding:3px 8px;border-radius:999px}
            </style>
            <table class="variant-table"><thead><tr><th>Thuộc tính</th><th>Giá trị</th><th>Đang dùng</th><th>Trạng thái</th><th>Hành động</th></tr></thead><tbody>
            @foreach($variantTypes as $variantType)
                @php($used = $variantType->values->sum(fn($value) => $value->productVariants->count()))
                <tr class="js-variant-table-row" data-drawer="drawer-{{ $variantType->id }}"><td><strong>{{ $variantType->name }}</strong></td><td>{{ $variantType->values_count }}</td><td>{{ $used }} SKU</td><td>{{ $variantType->status === 'active' ? 'Đang dùng' : 'Ẩn' }}</td><td>Bấm để xem</td></tr>
                <tr id="drawer-{{ $variantType->id }}" class="variant-drawer" hidden><td colspan="5"><div class="variant-drawer-values">@foreach($variantType->values as $value)<div class="variant-drawer-value"><span>{{ $value->value }}</span><span>{{ $value->productVariants->count() }} SKU</span></div>@endforeach</div><form action="{{ route('admin.products.variants.values.store', $variantType) }}" method="POST" class="variant-add-value">@csrf<input name="value" class="variant-input" placeholder="Thêm giá trị mới" required><button class="variant-btn variant-btn-primary">+ Thêm</button></form></td></tr>
            @endforeach
            </tbody></table>

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

@section('scripts')
<script>
document.querySelectorAll('.js-toggle-value-edit').forEach(button => {
    button.addEventListener('click', () => {
        const row = button.closest('.variant-value-row');
        document.querySelectorAll('.variant-value-row.is-editing').forEach(item => {
            if (item !== row) item.classList.remove('is-editing');
        });
        row.classList.toggle('is-editing');
        row.querySelector('.variant-value-edit-form input')?.focus();
    });
});

document.querySelectorAll('.variant-type-head').forEach(head => {
    head.addEventListener('click', event => {
        if (event.target.closest('input, select, button, form')) return;
        const card = head.closest('.variant-type-card');
        document.querySelectorAll('.variant-type-card.is-open').forEach(item => {
            if (item !== card) item.classList.remove('is-open');
        });
        card.classList.toggle('is-open');
    });
});
document.querySelectorAll('.js-variant-table-row').forEach(row => row.addEventListener('click', () => {
    const drawer = document.getElementById(row.dataset.drawer);
    document.querySelectorAll('.variant-drawer').forEach(item => { if (item !== drawer) item.hidden = true; });
    drawer.hidden = !drawer.hidden;
}));
</script>
@endsection
