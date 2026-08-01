@extends('admin.layouts.app')

@section('title', 'Quan ly don hang')

@section('styles')
<style>
    .order-action-icons { display: inline-flex; align-items: center; gap: 8px; }
    .order-action-icons .action-view-details,
    .order-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        color: var(--text-main);
        text-decoration: none;
        transition: var(--transition);
    }
    .order-action-icons .action-view-details span { display: none; }
    .order-action-icons .action-view-details:hover,
    .order-action-icon:hover { border-color: var(--primary); background: var(--primary-light); color: var(--primary); transform: translateY(-1px); }
    .order-export-modal[hidden] { display: none; }
    .order-export-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 20px; background: rgba(15, 23, 42, .48); }
    .order-export-card { width: min(480px, 100%); padding: 24px; border-radius: 12px; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .22); }
    .order-export-card h3 { margin: 0 0 10px; color: var(--text-main); }
    .order-export-card p { margin: 0; color: var(--text-muted); line-height: 1.5; }
    .order-export-options { display: grid; gap: 10px; margin-top: 18px; }
    .order-export-option { display: flex; gap: 9px; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; }
    .order-export-option:has(input:checked) { border-color: var(--primary); background: var(--primary-light); }
    .order-export-option input { margin-top: 3px; accent-color: var(--primary); }
    .order-export-option strong, .order-export-option small { display: block; }
    .order-export-option small { margin-top: 3px; color: var(--text-muted); }
    .order-export-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
    .order-export-actions button { border: 0; border-radius: 8px; padding: 10px 16px; font-weight: 700; cursor: pointer; }
    .order-export-cancel { background: var(--bg-color); color: var(--text-main); }
    .order-export-submit { background: var(--primary); color: #fff; }
    .order-export-submit:disabled { cursor: wait; opacity: .7; }

    /* Nút "Xuất Dữ liệu Excel" ở header dùng màu chủ đạo (cam).
       Bộ lọc, bảng và badge trạng thái đã dùng chung trong css/style.css. */
    #open-order-export-modal.btn-dark-slate {
        background-color: var(--primary);
        box-shadow: 0 2px 6px rgba(255, 120, 45, 0.22);
    }
    #open-order-export-modal.btn-dark-slate:hover {
        background-color: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(255, 120, 45, 0.28);
    }
    .orders-filter-card.is-filtering { opacity: .65; pointer-events: none; }
</style>
@endsection

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
        <button class="btn-dark-slate" type="button" id="open-order-export-modal">
            <i class="fa-solid fa-download"></i>
            <span>Xuất Dữ liệu Excel</span>
        </button>
    </div>
</div>

<form class="filters-card orders-filter-card" method="GET" action="{{ route('admin.orders') }}" id="orders-filter-form">
    <div class="filter-col orders-filter-search">
        <label class="filter-label">Tìm kiếm</label>
        <div class="filter-input-wrapper">
            <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
            <input type="text" name="search" class="filter-input" placeholder="Mã đơn, mã giao dịch, tên, SĐT, email..." value="{{ $filters['search'] ?? '' }}">
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
        <a href="{{ route('admin.orders') }}"  style="text-decoration:none"class="btn-clear-filters">Xoá Bộ Lọc</a>
    </div>
</form>

<div class="table-card">
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Mã giao dịch</th>
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
                        $latestTransaction = $order->sepayTransactions->first();
                        $transactionCode = $latestTransaction?->sepay_id;
                    @endphp
                    <tr @class(['order-row-cancelled' => $isCancelled])>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="col-order-link">{{ $orderCode }}</a>
                            <div class="orders-code-meta">
                                <span>{{ $order->items_count }} sản phẩm</span>
                                    <span>{{ $order->shippingMethod?->name ?? 'Chưa rõ vận chuyển' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($transactionCode)
                                <span class="sku-text">{{ $transactionCode }}</span>
                            @else
                                <span class="orders-customer-sub">Chưa có</span>
                            @endif
                        </td>
                        <td class="col-customer">
                            {{ $order->recipient_name }}
                            <div class="orders-customer-sub">{{ $order->recipient_phone }}</div>
                        </td>
                        <td style="color: var(--text-muted);">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="col-total">{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</td>
                        <td class="col-status">
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
                        <td class="col-status">
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
                            <div class="order-action-icons">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="action-view-details" title="Xem chi tiết đơn hàng" aria-label="Xem chi tiết đơn hàng">
                                <span>Xem chi tiết</span>
                                <i class="fa-solid fa-eye" style="font-size: 0.85rem;"></i>
                            </a>
                            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="order-action-icon" target="_blank" rel="noopener" title="In hóa đơn" aria-label="In hóa đơn">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">
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

<div class="order-export-modal" id="order-export-modal" hidden aria-hidden="true">
    <div class="order-export-card" role="dialog" aria-modal="true" aria-labelledby="order-export-title">
        <h3 id="order-export-title">Xuất dữ liệu đơn hàng</h3>
        <p>File Excel gồm hai sheet: tổng hợp đơn hàng và chi tiết sản phẩm.</p>
        <form action="{{ route('admin.orders.export') }}" method="GET" id="order-export-form">
            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
            <input type="hidden" name="payment_status" value="{{ $filters['payment_status'] ?? '' }}">
            <input type="hidden" name="order_status" value="{{ $filters['order_status'] ?? '' }}">
            <input type="hidden" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            <input type="hidden" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            <div class="order-export-options">
                <label class="order-export-option">
                    <input type="radio" name="scope" value="filtered" checked>
                    <span><strong>Theo bộ lọc hiện tại</strong><small>Xuất toàn bộ kết quả phù hợp, không chỉ trang đang xem.</small></span>
                </label>
                <label class="order-export-option">
                    <input type="radio" name="scope" value="all">
                    <span><strong>Tất cả đơn hàng</strong><small>Bỏ qua bộ lọc đang chọn.</small></span>
                </label>
            </div>
            <div class="order-export-actions">
                <button type="button" class="order-export-cancel" id="cancel-order-export">Hủy</button>
                <button type="submit" class="order-export-submit" id="submit-order-export"><i class="fa-solid fa-file-excel"></i> <span>Xuất Excel</span></button>
            </div>
        </form>
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

        const ordersFilterForm = document.getElementById('orders-filter-form');
        const ordersSearchInput = ordersFilterForm?.querySelector('input[name="search"]');
        let ordersSearchTimer;
        const submitOrderFilters = () => {
            if (!ordersFilterForm) return;
            ordersFilterForm.classList.add('is-filtering');
            ordersFilterForm.submit();
        };
        ordersSearchInput?.addEventListener('input', () => {
            clearTimeout(ordersSearchTimer);
            ordersSearchTimer = setTimeout(submitOrderFilters, 350);
        });
        ordersFilterForm?.querySelectorAll('select').forEach((select) => select.addEventListener('change', submitOrderFilters));

        const exportModal = document.getElementById('order-export-modal');
        const openExportButton = document.getElementById('open-order-export-modal');
        const cancelExportButton = document.getElementById('cancel-order-export');
        const exportForm = document.getElementById('order-export-form');
        const submitExportButton = document.getElementById('submit-order-export');

        function closeExportModal() {
            exportModal.hidden = true;
            exportModal.setAttribute('aria-hidden', 'true');
            openExportButton.focus();
        }

        openExportButton.addEventListener('click', function () {
            exportModal.hidden = false;
            exportModal.setAttribute('aria-hidden', 'false');
            cancelExportButton.focus();
        });

        cancelExportButton.addEventListener('click', closeExportModal);
        exportModal.addEventListener('click', function (event) {
            if (event.target === exportModal) closeExportModal();
        });
        exportForm.addEventListener('submit', function () {
            submitExportButton.disabled = true;
            submitExportButton.querySelector('span').textContent = 'Đang tạo file...';
            window.setTimeout(function () { submitExportButton.disabled = false; }, 2500);
        });
    });
</script>
@endsection
