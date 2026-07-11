@extends('admin.layouts.app')

@section('title', 'Quan ly don hang')

@section('content')
@if(session('success'))
    <div class="alert-panel alert-success-box">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error') || $errors->any())
    <div class="alert-panel alert-error-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>{{ session('error') ?? $errors->first() }}</span>
    </div>
@endif

<div class="dashboard-header">
    <div class="header-title-block">
        <h1>Quản Lý Đơn Hàng</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Theo dõi đơn hàng, thanh toán và trạng thái giao hàng từ dữ liệu thực tế.</p>
    </div>
    <div class="header-actions">
        <button class="btn-dark-slate" type="button">
            <i class="fa-solid fa-download"></i>
            <span>Xuất Dữ liệu Excel</span>
        </button>
    </div>
</div>

<form class="filters-card orders-filter-card" method="GET" action="{{ route('admin.orders') }}">
    <div class="filter-col orders-filter-search">
        <label class="filter-label">Tìm kiếm</label>
        <div class="filter-input-wrapper">
            <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
            <input type="text" name="search" class="filter-input" placeholder="Mã đơn, tên, SĐT, email..." value="{{ $filters['search'] ?? '' }}">
        </div>
    </div>
    <div class="filter-col">
        <label class="filter-label">Thanh Toán</label>
        <select name="payment_status" class="filter-select">
            <option value="">Tất Cả</option>
            @foreach($paymentStatuses as $value => $label)
                <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-col">
        <label class="filter-label">Đơn Hàng</label>
        <select name="order_status" class="filter-select">
            <option value="">Tất cả</option>
            @foreach($orderStatuses as $value => $label)
                <option value="{{ $value }}" @selected(($filters['order_status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-col orders-filter-actions">
        <button class="btn-dark-slate" type="submit">
            <i class="fa-solid fa-filter"></i>
            <span>Lọc</span>
        </button>
        <a href="{{ route('admin.orders') }}"  style="text-decoration:none"class="btn-clear-filters">Xoá Bộ Lọc</a>
    </div>
</form>

<div class="table-card">
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Đơn hàng</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $nextOrderStatuses = match ($order->order_status) {
                            'pending' => ['confirmed', 'cancelled'],
                            'confirmed' => ['shipping', 'cancelled'],
                            'shipping' => ['completed'],
                            default => [],
                        };
                        $nextPaymentStatuses = match ($order->payment_status) {
                            'unpaid' => ['paid', 'failed'],
                            'paid' => ['refunded'],
                            default => [],
                        };
                        $isCancelled = $order->order_status === 'cancelled';

                        if ($isCancelled) {
                            $nextOrderStatuses = [];
                            $nextPaymentStatuses = [];
                        }

                        $orderCode = $order->payment_code ?: 'PW' . $order->id;
                    @endphp
                    <tr @class(['order-row-cancelled' => $isCancelled])>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="col-order-link">{{ $orderCode }}</a>
                            <div class="orders-code-meta">
                                <span>{{ $order->items_count }} sản phẩm</span>
                                    <span>{{ $order->shippingMethod?->name ?? 'Chưa rõ vận chuyển' }}</span>
                            </div>
                        </td>
                        <td class="col-customer">
                            {{ $order->recipient_name }}
                            <div class="orders-customer-sub">{{ $order->recipient_phone }}</div>
                        </td>
                        <td style="color: var(--text-muted);">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="col-total">{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</td>
                        <td>
                            <div class="quick-status-wrapper">
                                <span class="quick-status-trigger badge-payment {{ $paymentStatusClasses[$order->payment_status] ?? 'pending' }}" aria-disabled="{{ $nextPaymentStatuses === [] ? 'true' : 'false' }}">
                                    <span>{{ $paymentStatuses[$order->payment_status] ?? $order->payment_status }}</span>
                                    @if($nextPaymentStatuses !== [])
                                        <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                                    @endif
                                </span>
                                @if($nextPaymentStatuses !== [])
                                    <div class="quick-status-menu">
                                        @foreach($nextPaymentStatuses as $status)
                                            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="payment_status" value="{{ $status }}">
                                                <button type="submit" class="quick-status-item">{{ $paymentStatuses[$status] }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="orders-method">{{ $order->paymentMethod?->name ?? 'Chưa xác định' }}</div>
                        </td>
                        <td>
                            <div class="quick-status-wrapper">
                                <span class="quick-status-trigger badge-fulfillment {{ $orderStatusClasses[$order->order_status] ?? 'pending' }}" aria-disabled="{{ $nextOrderStatuses === [] ? 'true' : 'false' }}">
                                    <span>{{ $orderStatuses[$order->order_status] ?? $order->order_status }}</span>
                                    @if($nextOrderStatuses !== [])
                                        <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                                    @endif
                                </span>
                                @if($nextOrderStatuses !== [])
                                    <div class="quick-status-menu">
                                        @foreach($nextOrderStatuses as $status)
                                            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="order_status" value="{{ $status }}">
                                                <button type="submit" class="quick-status-item">{{ $orderStatuses[$status] }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="action-view-details">
                                <span>Xem chi tiết</span>
                                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                            chưa có đơn hàng phù hợp với bộ lọc này.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            @if($orders->total() > 0)
                Hiển thị <strong style="color: var(--text-main); font-weight: 600;">{{ $orders->firstItem() }} đến {{ $orders->lastItem() }}</strong>
                của <strong style="color: var(--text-main); font-weight: 600;">{{ $orders->total() }}</strong> đơn hàng
            @else
                Hiển thị <strong style="color: var(--text-main); font-weight: 600;">0</strong> đơn hàng
            @endif
        </div>
        <div class="pagination-buttons">
            @if($orders->onFirstPage())
                <span class="pagination-btn disabled" title="Trang trước">
                    <i class="fa-solid fa-angle-left"></i>
                </span>
            @else
                <a href="{{ $orders->previousPageUrl() }}" class="pagination-btn" title="Trang trước">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            @endif

            @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                @if($page === $orders->currentPage())
                    <span class="pagination-btn active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="pagination-btn" title="Trang sau">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            @else
                <span class="pagination-btn disabled" title="Trang sau">
                    <i class="fa-solid fa-angle-right"></i>
                </span>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.quick-status-trigger[aria-disabled="false"]').forEach((trigger) => {
            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
                document.querySelectorAll('.quick-status-menu').forEach((menu) => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.remove('show');
                    }
                });
                this.nextElementSibling?.classList.toggle('show');
            });
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.quick-status-menu').forEach((menu) => menu.classList.remove('show'));
        });
    });
</script>
@endsection
