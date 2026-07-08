@extends('admin.layouts.app')

@section('title', 'Quản lý Voucher')

@section('styles')
    <style>
        .badge-status.expired {
            background-color: #fce8e6;
            color: #c5221f;
            border: 1px solid #fad2cf;
        }
        .badge-status.inactive {
            background-color: #f1f3f4;
            color: #5f6368;
            border: 1px solid #dadce0;
        }
        .btn-delete-voucher {
            padding: 6px 10px;
            border-radius: 6px;
            color: #d93025;
            background: none;
            border: 1px solid #fad2cf;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-delete-voucher:hover {
            background-color: #fce8e6;
        }
    </style>
@endsection

@section('content')

    <!-- Success Message Panel -->
    @if(session('success'))
        <div class="alert-panel alert-success-box" style="margin-bottom: 20px; padding: 12px 20px; background-color: #e6f4ea; color: #137333; border: 1px solid #ceead6; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-header" style="margin-bottom: 24px;">
        <div class="header-title-block">
            <h1>Khuyến Mãi & Voucher</h1>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
                Quản lý các mã giảm giá cho khách hàng khi thực hiện thanh toán hóa đơn.
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.vouchers.create') }}" class="categories-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm Voucher</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="categories-filter-bar" style="margin-bottom: 20px;">
        <div class="categories-filter-left">
            <span style="font-weight: 600; color: var(--text-main);">Danh sách mã giảm giá</span>
        </div>
        <div class="categories-filter-right">
            <span class="categories-display-text">Hiển thị {{ $vouchers->count() }} voucher</span>
        </div>
    </div>

    <!-- Voucher Table -->
    <div class="table-card">
        <div class="table-container">
            <table class="category-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>MÃ VOUCHER</th>
                        <th>MÔ TẢ</th>
                        <th>MỨC GIẢM</th>
                        <th>ĐƠN TỐI THIỂU</th>
                        <th>LƯỢT SỬ DỤNG TỐI ĐA</th>
                        <th>THỜI GIAN ÁP DỤNG</th>
                        <th>TRẠNG THÁI</th>
                        <th style="width: 120px; text-align: center;">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $index => $voucher)
                        <tr>
                            <td>{{ ($vouchers->currentPage() - 1) * $vouchers->perPage() + $index + 1 }}</td>
                            <td>
                                <strong style="color: var(--primary); font-size: 1.05rem; letter-spacing: 0.5px;">{{ $voucher->code }}</strong>
                            </td>
                            <td>
                                <span style="font-size: 0.9rem; color: var(--text-main);">{{ $voucher->description ?? 'Không có mô tả' }}</span>
                            </td>
                            <td>
                                <strong style="color: #137333;">{{ number_format($voucher->discount_value, 0, ',', '.') }}đ</strong>
                            </td>
                            <td>
                                <span style="color: var(--text-muted);">{{ number_format($voucher->min_order_value, 0, ',', '.') }}đ</span>
                            </td>
                            <td>
                                @if($voucher->usage_limit === 0)
                                    <span style="color: var(--text-muted); font-style: italic;">Vô hạn</span>
                                @else
                                    <span>{{ $voucher->usage_limit }} lượt</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; line-height: 1.4;">
                                    <div><span style="color: #6c757d; font-weight: 500;">Từ:</span> {{ $voucher->start_date->format('d/m/Y H:i') }}</div>
                                    <div><span style="color: #6c757d; font-weight: 500;">Đến:</span> {{ $voucher->end_date->format('d/m/Y H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                @if($voucher->status == 'active')
                                    <span class="badge-status active">
                                        <span style="font-size: 0.9rem; color: var(--success); line-height: 1;">•</span> Active
                                    </span>
                                @elseif($voucher->status == 'expired')
                                    <span class="badge-status expired">
                                        <span style="font-size: 0.9rem; color: var(--danger); line-height: 1;">•</span> Expired
                                    </span>
                                @else
                                    <span class="badge-status inactive">
                                        <span style="font-size: 0.9rem; color: #5f6368; line-height: 1;">•</span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="btn-filter-action"
                                        style="padding: 6px 10px; border-radius: 6px; text-decoration: none;" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i>
                                    </a>
                                    <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này không?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-voucher" title="Xóa">
                                            <i class="fa-solid fa-trash" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                Chưa có voucher nào được tạo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($vouchers->hasPages())
            <div class="pagination-container" style="padding: 16px 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    Hiển thị từ <strong style="color: var(--text-main); font-weight: 600;">{{ ($vouchers->currentPage() - 1) * $vouchers->perPage() + 1 }}</strong> đến <strong style="color: var(--text-main); font-weight: 600;">{{ min($vouchers->currentPage() * $vouchers->perPage(), $vouchers->total()) }}</strong> của <strong style="color: var(--text-main); font-weight: 600;">{{ $vouchers->total() }}</strong> voucher
                </div>
                <div>
                    {{ $vouchers->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
@endsection