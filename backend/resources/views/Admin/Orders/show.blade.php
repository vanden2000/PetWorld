@extends('admin.layouts.app')

@php
    $orderCode = $order->payment_code ?: 'PW' . $order->id;
    $subtotal = $order->items->sum(fn($item) => (float) $item->price * $item->quantity);
    $orderClass = $orderStatusClasses[$order->order_status] ?? 'pending';
    $paymentClass = $paymentStatusClasses[$order->payment_status] ?? 'pending';
    $isCancelled = $order->order_status === 'cancelled';

    if ($isCancelled) {
        $nextOrderStatuses = [];
        $nextPaymentStatuses = [];
    }
@endphp

@section('title', 'Chi tiết đơn hàng ' . $orderCode)

@section('styles')
<style>
    .order-item-review-horizontal {
        padding: 10px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }
    .order-item-review-horizontal:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    .review-h-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .review-h-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .review-h-tag {
        font-weight: 700;
        color: var(--text-muted);
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .review-h-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .review-h-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .review-h-comment {
        margin-top: 8px;
        padding: 8px 12px;
        background: #ffffff;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        color: var(--text-main);
        line-height: 1.5;
        font-size: 0.83rem;
    }
    .review-h-comment.empty {
        background: transparent;
        border: none;
        padding: 4px 0 0 0;
        color: var(--text-muted);
        font-size: 0.78rem;
    }
    .review-stars-display {
        color: #f59e0b;
        font-size: 0.88rem;
        display: inline-flex;
        gap: 2px;
    }
    .review-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.72rem;
    }
    .review-badge-status.approved {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .review-badge-status.pending {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .review-badge-status.hidden {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    .btn-review-quick {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-review-quick.approve {
        border: 1px solid #bbf7d0;
        background: #ffffff;
        color: #16a34a;
    }
    .btn-review-quick.approve:hover {
        background: #f0fdf4;
        border-color: #86efac;
    }
    .btn-review-quick.hide {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
    }
    .btn-review-quick.hide:hover {
        background: #f1f5f9;
        color: #ef4444;
        border-color: #fecaca;
    }
    .items-table tr.has-review td {
        border-bottom: none !important;
        padding-bottom: 6px;
    }
    .items-table tr.item-review-row td {
        padding-top: 0;
        padding-bottom: 14px;
    }
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

<div class="dashboard-header" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 16px;">
        <a href="{{ route('admin.orders') }}" class="btn-outline-back">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Quay lại</span>
        </a>
        <span style="color: var(--border-color); font-size: 1.5rem; font-weight: 300;">|</span>
        <h1 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">
            Đơn hàng {{ $orderCode }}
        </h1>
    </div>
    <div class="header-actions">
        <a class="btn-outline-print" href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" rel="noopener">
            <i class="fa-solid fa-print"></i>
            <span>IN HÓA ĐƠN</span>
        </a>
    </div>
</div>

<div class="order-details-grid">
    <div class="details-left">
        <div class="address-details-row">
            <div class="order-details-card">
                <h3 class="order-details-card-title">
                    <i class="fa-solid fa-circle-user"></i>
                    <span>Thông tin khách hàng</span>
                </h3>

                <h4 class="info-label">Họ và tên</h4>
                <p class="info-value info-value-large">{{ $order->recipient_name }}</p>

                <h4 class="info-label">Email</h4>
                <p class="info-value">
                    @if($order->user?->email)
                        <a href="mailto:{{ $order->user->email }}" class="info-value-link">{{ $order->user->email }}</a>
                    @else
                        <span>Chưa có email</span>
                    @endif
                </p>

                <h4 class="info-label">Số điện thoại</h4>
                <p class="info-value">{{ $order->recipient_phone }}</p>
            </div>

            <div class="order-details-card">
                <h3 class="order-details-card-title">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Địa chỉ giao hàng</span>
                </h3>

                <h4 class="info-label">Địa chỉ nhận hàng</h4>
                <p class="info-value" style="font-weight: 500;">{{ $order->recipient_address }}</p>

                <h4 class="info-label">Khu vực giao hàng</h4>
                <p class="info-value">{{ $order->delivery_area }}</p>

                <h4 class="info-label">Phương thức vận chuyển</h4>
                <p class="info-value" style="font-weight: 700;">
                    <i class="fa-solid fa-truck-fast" style="color: var(--primary); margin-right: 4px;"></i>
                    {{ $order->shippingMethod?->name ?? 'Chưa xác định' }}
                </p>
                @if($order->shipment?->tracking_code)
                    <h4 class="info-label">Mã vận đơn GHN</h4>
                    <p class="info-value" style="font-weight: 700; color: var(--primary);">{{ $order->shipment->tracking_code }}</p>

                    @php
                        $ghnStatusLabels = [
                            'ready_to_pick' => 'Chờ lấy hàng',
                            'picking' => 'Đang lấy hàng',
                            'picked' => 'Đã lấy hàng',
                            'storing' => 'Đang ở kho GHN',
                            'transporting' => 'Đang luân chuyển',
                            'sorting' => 'Đang phân loại',
                            'delivering' => 'Đang giao cho người nhận',
                            'delivered' => 'Đã giao thành công',
                            'delivery_fail' => 'Giao hàng thất bại',
                            'cancel' => 'Đã hủy',
                            'returned' => 'Đã hoàn hàng',
                        ];
                        $ghnStatus = (string) $order->shipment->status;
                    @endphp
                    @if($ghnStatus !== '')
                        <h4 class="info-label">GHN báo</h4>
                        <p class="info-value" style="font-weight: 700; color: #2563eb;">
                            <i class="fa-solid fa-satellite-dish" style="margin-right: 4px;"></i>
                            {{ $ghnStatusLabels[$ghnStatus] ?? str_replace('_', ' ', $ghnStatus) }}
                        </p>
                    @endif
                @endif
            </div>
        </div>

        <div class="order-details-card">
            <div class="order-details-card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-basket-shopping" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">Sản phẩm đã đặt</span>
                </div>
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); background-color: var(--bg-color); padding: 4px 10px; border-radius: 4px;">
                    Tổng cộng {{ $order->items->count() }} dòng sản phẩm
                </span>
            </div>

            <div class="table-container">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="padding-left: 0;">Sản phẩm</th>
                            <th>SKU</th>
                            <th style="text-align: center;">Số lượng</th>
                            <th style="text-align: right;">Đơn giá</th>
                            <th style="padding-right: 0; text-align: right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                         @php
   $image = $item->productVariant?->product?->images?->firstWhere('is_primary', 1)?->image_url;
    $imageUrl = $image
        ? asset('storage/' . ltrim($image, '/'))
        : asset('storage/logo/logo.png');
@endphp
                            <tr class="{{ $item->review ? 'has-review' : '' }}">
                                <td style="padding-left: 0;">
                                    <div class="product-cell-detail">
                                        <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="product-cell-image">
                                        <div class="product-cell-text">
                                            <span class="product-cell-title">{{ $item->product_name }}</span>
                                            <span class="product-cell-meta">{{ $item->productVariant?->display_name ?: 'Không có phân loại' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="sku-text">{{ $item->productVariant?->sku ?? 'N/A' }}</span></td>
                                <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                                <td style="text-align: right;">{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                                <td style="padding-right: 0; text-align: right; font-weight: 700;">{{ number_format((float) $item->price * $item->quantity, 0, ',', '.') }}đ</td>
                            </tr>

                            @if($item->review)
                                <tr class="item-review-row">
                                    <td colspan="5" style="padding-left: 0; padding-right: 0;">
                                        <div class="order-item-review-horizontal">
                                            <div class="review-h-header">
                                                <div class="review-h-left">
                                                    <span class="review-h-tag"><i class="fa-solid fa-comment-dots" style="color: var(--primary);"></i> Đánh giá của khách:</span>
                                                    <div class="review-stars-display" aria-label="{{ $item->review->rating }} sao">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa-solid fa-star" style="color: {{ $i <= $item->review->rating ? '#f59e0b' : '#e2e8f0' }}; font-size: 0.85rem;"></i>
                                                        @endfor
                                                    </div>
                                                    <strong style="color: var(--text-main); font-size: 0.82rem;">{{ $item->review->rating }}/5 sao</strong>
                                                </div>

                                                <div class="review-h-right">
                                                    <span class="review-h-time">
                                                        <i class="fa-regular fa-clock"></i> {{ $item->review->created_at?->format('d/m/Y H:i') }}
                                                    </span>

                                                    @if($item->review->status === 'approved')
                                                        <span class="review-badge-status approved">
                                                            <i class="fa-solid fa-circle-check"></i> Đã duyệt
                                                        </span>
                                                    @elseif($item->review->status === 'pending')
                                                        <span class="review-badge-status pending">
                                                            <i class="fa-solid fa-clock"></i> Chờ duyệt
                                                        </span>
                                                    @else
                                                        <span class="review-badge-status hidden">
                                                            <i class="fa-solid fa-eye-slash"></i> Đã ẩn
                                                        </span>
                                                    @endif

                                                    @if($item->review->status !== 'approved')
                                                        <form method="POST" action="{{ route('admin.reviews.status.update', $item->review) }}" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="btn-review-quick approve" title="Duyệt đánh giá này">
                                                                <i class="fa-solid fa-check"></i> Duyệt
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($item->review->status !== 'hidden')
                                                        <form method="POST" action="{{ route('admin.reviews.status.update', $item->review) }}" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="hidden">
                                                            <button type="submit" class="btn-review-quick hide" title="Ẩn đánh giá này">
                                                                <i class="fa-solid fa-eye-slash"></i> Ẩn
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($item->review->comment)
                                                <div class="review-h-comment">
                                                    <i class="fa-solid fa-quote-left" style="color: var(--primary); font-size: 0.72rem; margin-right: 6px; opacity: 0.7;"></i>
                                                    <span>{{ $item->review->comment }}</span>
                                                </div>
                                            @else
                                                <div class="review-h-comment empty">
                                                    <em>(Khách hàng không để lại nhận xét bằng chữ)</em>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="order-details-card">
            <h3 class="order-details-card-title">
                <i class="fa-solid fa-building-columns"></i>
                <span>Đối soát SePay</span>
            </h3>

            <div class="summary-row">
                <span>Mã chuyển khoản</span>
                <span style="font-weight: 800; color: var(--primary);">{{ $orderCode }}</span>
            </div>
            <div class="summary-row">
                <span>Số giao dịch đã ghi nhận</span>
                <span style="font-weight: 700;">{{ $order->sepayTransactions->count() }}</span>
            </div>

            @if($order->sepayTransactions->isNotEmpty())
                <div class="table-container" style="margin-top: 14px;">
                    <table class="sepay-log-table">
                        <thead>
                            <tr>
                                <th>Mã GD</th>
                                <th>Thời gian</th>
                                <th>Số tiền</th>
                                <th>Nội dung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->sepayTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->sepay_id }}</td>
                                    <td>{{ $transaction->transaction_date?->format('d/m/Y H:i') ?? $transaction->created_at?->format('d/m/Y H:i') }}</td>
                                    <td style="font-weight: 700;">{{ number_format((float) $transaction->amount, 0, ',', '.') }}đ</td>
                                    <td>{{ $transaction->content }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="info-value" style="margin-top: 12px;">Chưa có giao dịch SePay nào khớp với đơn này.</p>
            @endif
        </div>
    </div>

    <div class="details-right-sidebar">
        <div class="order-details-card">
            <h3 class="order-details-card-title">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Cập nhật trạng thái</span>
            </h3>

            <div class="status-panel">
                <div>
                    <h4 class="info-label">Trạng thái đơn hàng</h4>
                    <span class="quick-status-trigger badge-fulfillment {{ $orderClass }}">
                        <span>{{ $orderStatuses[$order->order_status] ?? $order->order_status }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <select name="order_status" @disabled($nextOrderStatuses === [])>
                        @if($nextOrderStatuses === [])
                            <option>Không còn bước tiếp</option>
                        @else
                            <option value="">Chọn bước tiếp</option>
                            @foreach($nextOrderStatuses as $status)
                                <option value="{{ $status }}">{{ $orderStatuses[$status] }}</option>
                            @endforeach
                        @endif
                    </select>
                    <button type="submit" @disabled($nextOrderStatuses === [])>Cập nhật đơn hàng</button>
                </form>

                <div style="border-top: 1px dashed var(--border-color); padding-top: 12px;">
                    <h4 class="info-label">Trạng thái thanh toán</h4>
                    <span class="quick-status-trigger badge-payment {{ $paymentClass }}">
                        <span>{{ $paymentStatuses[$order->payment_status] ?? $order->payment_status }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <select name="payment_status" @disabled($nextPaymentStatuses === [])>
                        @if($nextPaymentStatuses === [])
                            <option>Không còn bước tiếp</option>
                        @else
                            <option value="">Chọn bước tiếp</option>
                            @foreach($nextPaymentStatuses as $status)
                                <option value="{{ $status }}">{{ $paymentStatuses[$status] }}</option>
                            @endforeach
                        @endif
                    </select>
                    <button type="submit" @disabled($nextPaymentStatuses === [])>Cập nhật thanh toán</button>
                </form>
            </div>
        </div>

        <div class="order-details-card">
            <h3 class="order-details-card-title">
                <i class="fa-solid fa-receipt"></i>
                <span>Tóm tắt thanh toán</span>
            </h3>

            <div class="summary-row">
                <span>Tạm tính</span>
                <span style="font-weight: 600; color: var(--text-main);">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển</span>
                <span style="font-weight: 600; color: var(--text-main);">{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}đ</span>
            </div>
            <div class="summary-row">
                <span>Giảm giá {{ $order->voucher?->code ? '(' . $order->voucher->code . ')' : '' }}</span>
                <span style="font-weight: 600; color: #c5221f;">-{{ number_format((float) $order->discount_amount, 0, ',', '.') }}đ</span>
            </div>
            @if((float) $order->shipping_discount > 0)
                <div class="summary-row">
                    <span>Ưu đãi vận chuyển{{ $order->shippingVoucher?->code ? ' (' . $order->shippingVoucher->code . ')' : '' }}</span>
                    <span style="font-weight: 600; color: #c5221f;">-{{ number_format((float) $order->shipping_discount, 0, ',', '.') }}đ</span>
                </div>
            @endif
            <div class="summary-row total-row">
                <span>Tổng tiền</span>
                <span style="color: #0d9488; font-size: 1.15rem;">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</span>
            </div>

            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
                <h4 class="info-label" style="margin-bottom: 12px;">Phương thức thanh toán</h4>
                <div style="display: flex; align-items: center; gap: 12px; background-color: var(--bg-color); padding: 12px; border-radius: 8px;">
                    <i class="fa-solid fa-credit-card" style="font-size: 1.5rem; color: var(--primary);"></i>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $order->paymentMethod?->name ?? 'Chưa xác định' }}</span>
                        <span style="font-size: 0.7rem; font-weight: 800; color: #10b981; margin-top: 2px;">{{ $paymentStatuses[$order->payment_status] ?? $order->payment_status }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-details-card">
            <h3 class="order-details-card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Lịch sử hành trình</span>
            </h3>

            <ul class="timeline-list">
                <li class="timeline-item">
                    <span class="timeline-icon-dot completed"><i class="fa-solid fa-check"></i></span>
                    <div class="timeline-item-title">Đặt đơn hàng thành công</div>
                    <div class="timeline-item-time">{{ $order->created_at?->format('d/m/Y H:i') }}</div>
                </li>
                @if(in_array($order->order_status, ['confirmed', 'shipping', 'completed'], true))
                    <li class="timeline-item">
                        <span class="timeline-icon-dot completed"><i class="fa-solid fa-check"></i></span>
                        <div class="timeline-item-title">Đơn hàng đã được xác nhận</div>
                        <div class="timeline-item-time">{{ $order->updated_at?->format('d/m/Y H:i') }}</div>
                    </li>
                @endif
                @if(in_array($order->order_status, ['shipping', 'completed'], true))
                    <li class="timeline-item">
                        <span class="timeline-icon-dot completed"><i class="fa-solid fa-check"></i></span>
                        <div class="timeline-item-title">Đơn hàng đang giao</div>
                        <div class="timeline-item-time">{{ $order->updated_at?->format('d/m/Y H:i') }}</div>
                    </li>
                @endif
                @if($order->order_status === 'completed')
                    <li class="timeline-item">
                        <span class="timeline-icon-dot completed"><i class="fa-solid fa-check"></i></span>
                        <div class="timeline-item-title">Đơn hàng hoàn thành</div>
                        <div class="timeline-item-time">{{ $order->updated_at?->format('d/m/Y H:i') }}</div>
                    </li>
                @elseif($order->order_status === 'cancelled')
                    <li class="timeline-item">
                        <span class="timeline-icon-dot pending"><i class="fa-solid fa-xmark" style="font-size: 0.65rem;"></i></span>
                        <div class="timeline-item-title" style="color: var(--text-muted);">Đơn hàng đã hủy</div>
                        <div class="timeline-item-time" style="color: var(--text-muted);">{{ $order->updated_at?->format('d/m/Y H:i') }}</div>
                    </li>
                @endif
            </ul>
        </div>

        <div class="order-details-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span class="info-label" style="margin: 0; font-size: 0.72rem; font-weight: 700;">GHI CHÚ KHÁCH HÀNG</span>
            </div>

            <div class="note-box">
                {{ $order->note ?: 'Đơn hàng không có ghi chú.' }}
            </div>
        </div>
    </div>
</div>
@endsection
