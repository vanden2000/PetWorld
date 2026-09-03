@extends('admin.layouts.app')

@php
    $orderCode = $order->payment_code ?: 'PW' . $order->id;
    $subtotal = $order->items->sum(fn($item) => (float) $item->price * $item->quantity);
    $orderClass = $orderStatusClasses[$order->order_status] ?? 'pending';
    $paymentClass = $paymentStatusClasses[$order->payment_status] ?? 'pending';
    $isCancelled = $order->order_status === 'cancelled';
    $isReturned = $order->order_status === 'returned';

    if (($isCancelled || $isReturned) && $order->payment_status === 'unpaid') {
        $paymentClass = 'cancelled';
        $displayPaymentStatus = 'Hủy thu tiền (Không phát sinh)';
    } else {
        $displayPaymentStatus = $paymentStatuses[$order->payment_status] ?? $order->payment_status;
    }

    $hasShipped = !empty($order->shipment?->tracking_code);

    if ($isCancelled) {
        $nextOrderStatuses = $hasShipped ? ['returned'] : [];
        $nextPaymentStatuses = $order->payment_status === 'paid' ? ['refunded'] : [];
    } elseif ($isReturned) {
        $nextOrderStatuses = [];
        $nextPaymentStatuses = $order->payment_status === 'paid' ? ['refunded'] : [];
    }

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

    $orderActionButtons = [
        'confirmed' => [
            'label' => 'Xác nhận đơn hàng',
            'icon' => 'fa-check',
            'class' => 'btn-step-confirm',
            'confirm_title' => 'Xác nhận đơn hàng',
            'confirm_msg' => 'Bạn có chắc chắn muốn duyệt và chuẩn bị đóng gói kiện hàng này?',
            'confirm_icon' => 'fa-clipboard-check',
            'confirm_type' => 'primary',
        ],
        'shipping' => [
            'label' => $order->shipping_method_code === 'ghn_express' ? 'Gửi đơn sang GHN (Chờ lấy hàng)' : 'Bàn giao giao hàng',
            'icon' => $order->shipping_method_code === 'ghn_express' ? 'fa-paper-plane' : 'fa-truck-fast',
            'class' => 'btn-step-shipping',
            'confirm_title' => $order->shipping_method_code === 'ghn_express' ? 'Gửi yêu cầu lấy hàng sang GHN' : 'Bàn giao giao hàng',
            'confirm_msg' => $order->shipping_method_code === 'ghn_express'
                ? 'Hệ thống sẽ tạo mã vận đơn GHN và chuyển đơn sang bước 3: Chờ bưu tá GHN đến lấy hàng?'
                : 'Bàn giao kiện hàng cho nhân viên giao hàng của shop?',
            'confirm_icon' => $order->shipping_method_code === 'ghn_express' ? 'fa-paper-plane' : 'fa-truck-fast',
            'confirm_type' => 'primary',
        ],
        'completed' => [
            'label' => 'Hoàn thành đơn hàng',
            'icon' => 'fa-circle-check',
            'class' => 'btn-step-complete',
            'confirm_title' => 'Hoàn thành đơn hàng',
            'confirm_msg' => 'Xác nhận đơn hàng đã giao thành công và khách hàng đã nhận được kiện hàng?',
            'confirm_icon' => 'fa-circle-check',
            'confirm_type' => 'success',
        ],
        'returned' => [
            'label' => 'Xác nhận đã nhận lại hàng hoàn',
            'icon' => 'fa-box-archive',
            'class' => 'btn-step-returned',
            'confirm_title' => 'Nhận lại hàng hoàn',
            'confirm_msg' => 'Xác nhận bạn đã nhận lại kiện hàng từ ĐVVC và tự động nhập lại kho?',
            'confirm_icon' => 'fa-box-archive',
            'confirm_type' => 'warning',
        ],
        'cancelled' => [
            'label' => 'Hủy đơn hàng này',
            'icon' => 'fa-ban',
            'class' => 'btn-step-cancel',
            'confirm_title' => 'Hủy đơn hàng này',
            'confirm_msg' => 'Bạn có chắc chắn muốn hủy đơn hàng này? Số lượng tồn kho sẽ được tự động hoàn lại.',
            'confirm_icon' => 'fa-ban',
            'confirm_type' => 'danger',
        ],
    ];

    $paymentActionButtons = [
        'customer_paid' => [
            'label' => 'Khách đã trả tiền mặt',
            'icon' => 'fa-hand-holding-dollar',
            'class' => 'btn-step-customer-paid',
            'confirm_title' => 'Khách đã trả tiền mặt',
            'confirm_msg' => 'Xác nhận Shipper đã thu đủ tiền mặt từ khách hàng cho đơn này?',
            'confirm_icon' => 'fa-hand-holding-dollar',
            'confirm_type' => 'primary',
        ],
        'reconciling' => [
            'label' => 'Chuyển sang Đang đối soát',
            'icon' => 'fa-arrows-rotate',
            'class' => 'btn-step-reconciling',
            'confirm_title' => 'Bắt đầu đối soát',
            'confirm_msg' => 'Chuyển đơn hàng vào kỳ đối soát bảng kê với đơn vị vận chuyển?',
            'confirm_icon' => 'fa-arrows-rotate',
            'confirm_type' => 'warning',
        ],
        'paid' => [
            'label' => $order->isCod() ? 'Xác nhận: Đã nhận tiền từ ĐVVC (Hoàn tất)' : 'Xác nhận đã nhận chuyển khoản',
            'icon' => 'fa-circle-check',
            'class' => 'btn-step-paid',
            'confirm_title' => $order->isCod() ? 'Xác nhận đã nhận tiền từ ĐVVC' : 'Xác nhận đã nhận chuyển khoản',
            'confirm_msg' => $order->isCod()
                ? 'Xác nhận tiền thu hộ (COD) từ đơn vị vận chuyển đã chuyển về tài khoản ngân hàng của Shop và kết thúc toàn bộ quy trình đơn hàng?'
                : 'Xác nhận khách hàng đã thanh toán chuyển khoản thành công cho đơn hàng này?',
            'confirm_icon' => 'fa-circle-check',
            'confirm_type' => 'success',
        ],
        'discrepancy' => [
            'label' => 'Báo có chênh lệch tiền',
            'icon' => 'fa-triangle-exclamation',
            'class' => 'btn-step-discrepancy',
            'confirm_title' => 'Báo có chênh lệch tiền',
            'confirm_msg' => 'Đánh dấu đơn hàng có sai lệch tiền thu hộ để tiếp tục khiếu nại ĐVVC?',
            'confirm_icon' => 'fa-triangle-exclamation',
            'confirm_type' => 'danger',
        ],
        'refunded' => [
            'label' => 'Xác nhận hoàn tiền',
            'icon' => 'fa-rotate-left',
            'class' => 'btn-step-refund',
            'confirm_title' => 'Xác nhận hoàn tiền',
            'confirm_msg' => 'Xác nhận bạn đã chuyển khoản hoàn tiền lại cho khách hàng thành công?',
            'confirm_icon' => 'fa-rotate-left',
            'confirm_type' => 'warning',
        ],
        'failed' => [
            'label' => 'Đánh dấu thanh toán lỗi',
            'icon' => 'fa-circle-xmark',
            'class' => 'btn-step-failed',
            'confirm_title' => 'Đánh dấu thanh toán lỗi',
            'confirm_msg' => 'Xác nhận đánh dấu giao dịch thanh toán này bị lỗi / thất bại?',
            'confirm_icon' => 'fa-circle-xmark',
            'confirm_type' => 'danger',
        ],
    ];
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

        /* Modern Next-Step Action Buttons */
        .action-steps-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
        }

        .btn-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 0.83rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: #ea580c;
            text-align: center;
        }

        .btn-step:hover,
        .btn-step-confirm:hover,
        .btn-step-shipping:hover,
        .btn-step-complete:hover,
        .btn-step-returned:hover,
        .btn-step-cancel:hover,
        .btn-step-customer-paid:hover,
        .btn-step-reconciling:hover,
        .btn-step-paid:hover,
        .btn-step-discrepancy:hover,
        .btn-step-refund:hover,
        .btn-step-failed:hover,
        .btn-step-note:hover {
            background: #ffedd5 !important;
            border-color: #fdba74 !important;
            color: #c2410c !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.12);
        }

        .btn-step:active {
            transform: translateY(0);
        }

        .btn-step-confirm,
        .btn-step-shipping,
        .btn-step-complete,
        .btn-step-returned,
        .btn-step-cancel,
        .btn-step-customer-paid,
        .btn-step-reconciling,
        .btn-step-paid,
        .btn-step-discrepancy,
        .btn-step-refund,
        .btn-step-failed {
            background: #fff7ed;
            color: #ea580c;
            border-color: #fed7aa;
        }

        .badge-fulfillment.returned {
            background-color: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
        }

        .badge-payment.cancelled {
            background-color: #f1f5f9;
            color: #64748b;
            border-color: #cbd5e1;
        }

        .btn-step-note {
            background: #f8fafc;
            color: var(--text-muted);
            border-color: #cbd5e1;
            font-size: 0.78rem;
            padding: 6px 12px;
        }

        .btn-step-note:hover {
            background: #e2e8f0;
            color: var(--text-main);
        }

        .step-completed-notice {
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* Modern Custom Confirmation Modal */
        .custom-confirm-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.22s;
        }

        .custom-confirm-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        .custom-confirm-dialog {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
            width: 90%;
            max-width: 410px;
            padding: 26px 22px 20px;
            text-align: center;
            transform: scale(0.92) translateY(10px);
            transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .custom-confirm-backdrop.active .custom-confirm-dialog {
            transform: scale(1) translateY(0);
        }

        .custom-confirm-dialog.return-modal-dialog {
            max-width: 520px;
            text-align: left;
            padding: 24px 22px 20px;
        }

        .btn-quick-reason {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.72rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-quick-reason:hover {
            background: #fff7ed;
            border-color: #fdba74;
            color: #ea580c;
        }

        .custom-confirm-icon-wrapper {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            margin-bottom: 14px;
            transition: all 0.25s;
        }

        .custom-confirm-icon-wrapper.primary {
            background: #fff7ed;
            color: #ea580c;
            border: 2px solid #fed7aa;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.18);
        }

        .custom-confirm-icon-wrapper.success {
            background: #f0fdf4;
            color: #16a34a;
            border: 2px solid #bbf7d0;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.18);
        }

        .custom-confirm-icon-wrapper.warning {
            background: #fffbeb;
            color: #d97706;
            border: 2px solid #fde68a;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.18);
        }

        .custom-confirm-icon-wrapper.danger {
            background: #fef2f2;
            color: #dc2626;
            border: 2px solid #fecaca;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.18);
        }

        .custom-confirm-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px 0;
            letter-spacing: -0.3px;
        }

        .custom-confirm-message {
            font-size: 0.86rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0 0 12px 0;
        }

        .custom-confirm-badge-code {
            display: inline-block;
            background: #f8fafc;
            color: #475569;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 20px;
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
        }

        .custom-confirm-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-confirm-cancel {
            padding: 9px 14px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.83rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-confirm-cancel:hover {
            background: #e2e8f0;
            color: #1e293b;
            border-color: #94a3b8;
        }

        .btn-confirm-accept {
            padding: 9px 14px;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.83rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-confirm-accept.primary {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);
        }

        .btn-confirm-accept.primary:hover {
            background: linear-gradient(135deg, #c2410c 0%, #9a3412 100%);
            box-shadow: 0 6px 16px rgba(234, 88, 12, 0.35);
            transform: translateY(-1px);
        }

        .btn-confirm-accept.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .btn-confirm-accept.success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
            transform: translateY(-1px);
        }

        .btn-confirm-accept.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        }

        .btn-confirm-accept.warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
            transform: translateY(-1px);
        }

        .btn-confirm-accept.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }

        .btn-confirm-accept.danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
            transform: translateY(-1px);
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
            <a class="btn-outline-print" href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank"
                rel="noopener">
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
                        <p class="info-value" style="font-weight: 700; color: var(--primary);">
                            {{ $order->shipment->tracking_code }}</p>

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
                <div class="order-details-card-title"
                    style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-basket-shopping" style="color: var(--primary); font-size: 1.1rem;"></i>
                        <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">Sản phẩm đã đặt</span>
                    </div>
                    <span
                        style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); background-color: var(--bg-color); padding: 4px 10px; border-radius: 4px;">
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
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}"
                                                class="product-cell-image">
                                            <div class="product-cell-text">
                                                <span class="product-cell-title">{{ $item->product_name }}</span>
                                                <span
                                                    class="product-cell-meta">{{ $item->productVariant?->display_name ?: 'Không có phân loại' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="sku-text">{{ $item->productVariant?->sku ?? 'N/A' }}</span></td>
                                    <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                                    <td style="text-align: right;">{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                                    <td style="padding-right: 0; text-align: right; font-weight: 700;">
                                        {{ number_format((float) $item->price * $item->quantity, 0, ',', '.') }}đ</td>
                                </tr>

                                @if($item->review)
                                    <tr class="item-review-row">
                                        <td colspan="5" style="padding-left: 0; padding-right: 0;">
                                            <div class="order-item-review-horizontal">
                                                <div class="review-h-header">
                                                    <div class="review-h-left">
                                                        <span class="review-h-tag"><i class="fa-solid fa-comment-dots"
                                                                style="color: var(--primary);"></i> Đánh giá của khách:</span>
                                                        <div class="review-stars-display"
                                                            aria-label="{{ $item->review->rating }} sao">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa-solid fa-star"
                                                                    style="color: {{ $i <= $item->review->rating ? '#f59e0b' : '#e2e8f0' }}; font-size: 0.85rem;"></i>
                                                            @endfor
                                                        </div>
                                                        <strong
                                                            style="color: var(--text-main); font-size: 0.82rem;">{{ $item->review->rating }}/5
                                                            sao</strong>
                                                    </div>

                                                    <div class="review-h-right">
                                                        <span class="review-h-time">
                                                            <i class="fa-regular fa-clock"></i>
                                                            {{ $item->review->created_at?->format('d/m/Y H:i') }}
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
                                                            <form method="POST"
                                                                action="{{ route('admin.reviews.status.update', $item->review) }}"
                                                                style="display: inline;">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="approved">
                                                                <button type="submit" class="btn-review-quick approve"
                                                                    title="Duyệt đánh giá này">
                                                                    <i class="fa-solid fa-check"></i> Duyệt
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($item->review->status !== 'hidden')
                                                            <form method="POST"
                                                                action="{{ route('admin.reviews.status.update', $item->review) }}"
                                                                style="display: inline;">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="hidden">
                                                                <button type="submit" class="btn-review-quick hide"
                                                                    title="Ẩn đánh giá này">
                                                                    <i class="fa-solid fa-eye-slash"></i> Ẩn
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($item->review->comment)
                                                    <div class="review-h-comment">
                                                        <i class="fa-solid fa-quote-left"
                                                            style="color: var(--primary); font-size: 0.72rem; margin-right: 6px; opacity: 0.7;"></i>
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
                                        <td>{{ $transaction->transaction_date?->format('d/m/Y H:i') ?? $transaction->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td style="font-weight: 700;">
                                            {{ number_format((float) $transaction->amount, 0, ',', '.') }}đ</td>
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
                    @php
                        $isGhn = ($order->shipment?->provider === 'ghn') || ($order->shipping_method_code === 'ghn_express');
                        $isReadyToPick = $order->order_status === 'shipping' && ($order->shipment?->status === 'ready_to_pick' || empty($order->shipment?->status));
                        $shipmentStatus = $order->shipment?->status;
                        $isFailed = in_array($shipmentStatus, ['delivery_fail', 'waiting_to_return', 'return', 'returning', 'return_transporting', 'damage', 'lost'], true);
                        $stepNumber = match ($order->order_status) {
                            'pending' => 1,
                            'confirmed' => 2,
                            'shipping' => ($isReadyToPick ? 3 : 4),
                            'completed', 'returned' => 5,
                            default => 0,
                        };
                    @endphp

                    @if(!$isCancelled)
                        <div class="fulfillment-stepper"
                            style="margin-bottom: 14px; padding: 12px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                            <div
                                style="font-size: 0.72rem; font-weight: 700; color: #475569; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                <span
                                    style="display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; text-transform: uppercase; font-size: 0.68rem; color: #64748b;">
                                    <i class="fa-solid fa-route"
                                        style="color: {{ ($isFailed || $isReturned) ? '#ea580c' : '#2563eb' }};"></i> Tiến trình
                                    đơn hàng:
                                </span>
                                @if($isReturned)
                                    <span
                                        style="color: #c2410c; font-weight: 700; background: #ffedd5; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; white-space: nowrap;">Đã
                                        hoàn về kho</span>
                                @elseif($isFailed)
                                    <span
                                        style="color: #c2410c; font-weight: 700; background: #ffedd5; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; white-space: nowrap;">Bước
                                        4/5 · Thất bại</span>
                                @else
                                    <span
                                        style="color: #0284c7; font-weight: 700; background: #e0f2fe; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; white-space: nowrap;">Bước
                                        {{ $stepNumber }}/5</span>
                                @endif
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; position: relative;">
                                <div
                                    style="position: absolute; top: 12px; left: 18px; right: 18px; height: 3px; background: #e2e8f0; z-index: 1;">
                                </div>
                                <div
                                    style="position: absolute; top: 12px; left: 18px; width: {{ max(0, min(92, ($stepNumber - 1) * 25)) }}%; height: 3px; background: {{ ($isFailed || $isReturned) ? 'linear-gradient(to right, #10b981 50%, #ea580c 100%)' : '#10b981' }}; z-index: 2; transition: width 0.4s ease;">
                                </div>

                                <div style="position: relative; z-index: 3; text-align: center; width: 18%;">
                                    <div
                                        style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; {{ $stepNumber >= 1 ? ($stepNumber === 1 ? 'background: #3b82f6; color: #fff; box-shadow: 0 0 0 3px #dbeafe;' : 'background: #10b981; color: #fff;') : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;' }}">
                                        @if($stepNumber > 1)<i class="fa-solid fa-check" style="font-size: 0.6rem;"></i>@else 1
                                        @endif
                                    </div>
                                    <span
                                        style="display: block; font-size: 0.62rem; font-weight: {{ $stepNumber === 1 ? '700' : '600' }}; color: {{ $stepNumber === 1 ? '#1e40af' : ($stepNumber > 1 ? '#059669' : '#64748b') }}; margin-top: 4px; line-height: 1.15;">Chờ
                                        xác nhận</span>
                                </div>

                                <div style="position: relative; z-index: 3; text-align: center; width: 18%;">
                                    <div
                                        style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; {{ $stepNumber >= 2 ? ($stepNumber === 2 ? 'background: #3b82f6; color: #fff; box-shadow: 0 0 0 3px #dbeafe;' : 'background: #10b981; color: #fff;') : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;' }}">
                                        @if($stepNumber > 2)<i class="fa-solid fa-check" style="font-size: 0.6rem;"></i>@else 2
                                        @endif
                                    </div>
                                    <span
                                        style="display: block; font-size: 0.62rem; font-weight: {{ $stepNumber === 2 ? '700' : '600' }}; color: {{ $stepNumber === 2 ? '#1e40af' : ($stepNumber > 2 ? '#059669' : '#64748b') }}; margin-top: 4px; line-height: 1.15;">Đã
                                        xác nhận</span>
                                </div>

                                <div style="position: relative; z-index: 3; text-align: center; width: 18%;">
                                    <div
                                        style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; {{ $stepNumber >= 3 ? ($stepNumber === 3 ? 'background: #f59e0b; color: #fff; box-shadow: 0 0 0 3px #fef3c7;' : 'background: #10b981; color: #fff;') : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;' }}">
                                        @if($stepNumber > 3)<i class="fa-solid fa-check" style="font-size: 0.6rem;"></i>@else 3
                                        @endif
                                    </div>
                                    <span
                                        style="display: block; font-size: 0.62rem; font-weight: {{ $stepNumber === 3 ? '700' : '600' }}; color: {{ $stepNumber === 3 ? '#b45309' : ($stepNumber > 3 ? '#059669' : '#64748b') }}; margin-top: 4px; line-height: 1.15;">Chờ
                                        lấy hàng</span>
                                </div>

                                <div style="position: relative; z-index: 3; text-align: center; width: 18%;">
                                    @if($isFailed || $isReturned)
                                        <div
                                            style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; background: #ea580c; color: #fff; box-shadow: 0 0 0 3px #ffedd5;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.6rem;"></i>
                                        </div>
                                        <span
                                            style="display: block; font-size: 0.62rem; font-weight: 700; color: #c2410c; margin-top: 4px; line-height: 1.15;">Giao
                                            thất bại</span>
                                    @else
                                        <div
                                            style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; {{ $stepNumber >= 4 ? ($stepNumber === 4 ? 'background: #3b82f6; color: #fff; box-shadow: 0 0 0 3px #dbeafe;' : 'background: #10b981; color: #fff;') : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;' }}">
                                            @if($stepNumber > 4)<i class="fa-solid fa-check" style="font-size: 0.6rem;"></i>@else 4
                                            @endif
                                        </div>
                                        <span
                                            style="display: block; font-size: 0.62rem; font-weight: {{ $stepNumber === 4 ? '700' : '600' }}; color: {{ $stepNumber === 4 ? '#1e40af' : ($stepNumber > 4 ? '#059669' : '#64748b') }}; margin-top: 4px; line-height: 1.15;">Đang
                                            giao hàng</span>
                                    @endif
                                </div>

                                <div style="position: relative; z-index: 3; text-align: center; width: 18%;">
                                    @if($isReturned)
                                        <div
                                            style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; background: #c2410c; color: #fff; box-shadow: 0 0 0 3px #fed7aa;">
                                            <i class="fa-solid fa-box-archive" style="font-size: 0.6rem;"></i>
                                        </div>
                                        <span
                                            style="display: block; font-size: 0.62rem; font-weight: 700; color: #c2410c; margin-top: 4px; line-height: 1.15;">Đã
                                            hoàn về kho</span>
                                    @elseif($isFailed)
                                        <div
                                            style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; background: #f1f5f9; color: #94a3b8; border: 2px solid #fed7aa;">
                                            5
                                        </div>
                                        <span
                                            style="display: block; font-size: 0.62rem; font-weight: 600; color: #c2410c; margin-top: 4px; line-height: 1.15;">Thất
                                            bại / Hoàn</span>
                                    @else
                                        <div
                                            style="width: 24px; height: 24px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; {{ $stepNumber >= 5 ? 'background: #10b981; color: #fff; box-shadow: 0 0 0 3px #d1fae5;' : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;' }}">
                                            @if($stepNumber >= 5)<i class="fa-solid fa-check" style="font-size: 0.6rem;"></i>@else 5
                                            @endif
                                        </div>
                                        <span
                                            style="display: block; font-size: 0.62rem; font-weight: {{ $stepNumber === 5 ? '700' : '600' }}; color: {{ $stepNumber === 5 ? '#059669' : '#64748b' }}; margin-top: 4px; line-height: 1.15;">Hoàn
                                            thành</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h4 class="info-label">Trạng thái đơn hàng</h4>
                        @if($isReturned)
                            <span class="quick-status-trigger"
                                style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 0.76rem; font-weight: 700; background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;">
                                <i class="fa-solid fa-box-archive"></i> ĐÃ HOÀN VỀ KHO (GIAO THẤT BẠI)
                            </span>
                        @elseif($isFailed && $order->order_status === 'shipping')
                            <span class="quick-status-trigger"
                                style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 0.76rem; font-weight: 700; background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;">
                                <i class="fa-solid fa-triangle-exclamation"></i> GIAO THẤT BẠI (CHỜ HOÀN VỀ SHOP)
                            </span>
                        @elseif($isReadyToPick)
                            <span class="quick-status-trigger"
                                style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 0.76rem; font-weight: 700; background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                                <i class="fa-solid fa-clock"></i> CHỜ BÀN GIAO GHN (CHỜ LẤY HÀNG)
                            </span>
                        @else
                            <span class="quick-status-trigger badge-fulfillment {{ $orderClass }}">
                                <span>{{ $orderStatuses[$order->order_status] ?? $order->order_status }}</span>
                            </span>
                        @endif
                    </div>

                    @if($order->order_status === 'shipping')
                        @php
                            $shipmentStatus = $order->shipment?->status;
                            $isFailed = in_array($shipmentStatus, ['delivery_fail', 'waiting_to_return', 'return', 'returning', 'return_transporting', 'damage', 'lost'], true);
                        @endphp
                        <div class="shipping-live-signal"
                            style="margin-top: 10px; margin-bottom: 8px; padding: 10px 12px; @if($isFailed) background: #fff7ed; border: 1px solid #fed7aa; @else background: #eff6ff; border: 1px solid #bfdbfe; @endif border-radius: 8px;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span
                                    style="font-size: 0.75rem; font-weight: 700; @if($isFailed) color: #c2410c; @else color: #1e40af; @endif display: flex; align-items: center; gap: 5px;">
                                    <i class="fa-solid {{ $isGhn ? 'fa-satellite-dish' : 'fa-truck-fast' }}"
                                        style="@if($isFailed) color: #ea580c; @else color: #2563eb; @endif"></i>
                                    {{ $isGhn ? 'Tín hiệu từ GHN Express:' : 'Vận chuyển tiêu chuẩn (PetWorld):' }}
                                </span>
                                @if($order->shipment?->tracking_code)
                                    <span
                                        style="font-size: 0.7rem; font-weight: 600; @if($isFailed) color: #c2410c; background: #ffedd5; @else color: #2563eb; background: #dbeafe; @endif padding: 2px 6px; border-radius: 4px;">
                                        {{ $order->shipment->tracking_code }}
                                    </span>
                                @endif
                            </div>
                            <p
                                style="font-size: 0.82rem; font-weight: 700; @if($isFailed) color: #c2410c; @else color: #1d4ed8; @endif margin: 0; line-height: 1.3;">
                                @if($isGhn)
                                    @if($isFailed)
                                        <i class="fa-solid fa-triangle-exclamation" style="color: #ea580c; margin-right: 4px;"></i>
                                        Giao hàng không thành công (Khách bom / Đang chuyển hoàn)
                                    @elseif($isReadyToPick)
                                        <i class="fa-solid fa-box-archive" style="color: #f59e0b; margin-right: 4px;"></i>
                                        Chờ bưu tá GHN đến lấy hàng (Chờ bàn giao)
                                    @else
                                        <i class="fa-solid fa-truck-fast" style="color: #2563eb; margin-right: 4px;"></i>
                                        {{ $ghnStatusLabels[$order->shipment?->status ?? 'delivering'] ?? 'Đang giao cho người nhận' }}
                                    @endif
                                @else
                                    <i class="fa-solid fa-truck" style="color: #2563eb; margin-right: 4px;"></i>
                                    Đang giao hàng đến địa chỉ người nhận
                                @endif
                            </p>
                            <p style="font-size: 0.72rem; color: #64748b; margin: 5px 0 0 0; line-height: 1.35;">
                                @if($isGhn)
                                    @if($isFailed)
                                        <i class="fa-solid fa-box-archive" style="color: #ea580c;"></i> Kiện hàng đang được GHN chuyển
                                        hoàn về shop. Khi bưu tá trả lại kiện hàng tận tay, vui lòng bấm nút <strong>"Xác nhận đã nhận
                                            lại hàng hoàn"</strong> bên dưới để kiểm kê và nhập lại kho.
                                    @elseif($isReadyToPick)
                                        <i class="fa-solid fa-warehouse" style="color: #f59e0b;"></i> Kiện hàng đã đóng gói sẵn tại kho.
                                        <strong>Đang chờ bưu tá GHN đến tiếp nhận</strong> để chuyển sang bước giao hàng.
                                    @else
                                        <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Đơn sẽ <strong>tự động chuyển Hoàn
                                            thành</strong> ngay khi bưu tá GHN giao thành công.
                                    @endif
                                @else
                                    <i class="fa-solid fa-circle-info" style="color: #2563eb;"></i> Kiện hàng đang được nhân viên
                                    giao hàng theo tuyến tiêu chuẩn.
                                @endif
                            </p>
                        </div>
                    @endif

                    @if(!empty($nextOrderStatuses))
                        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}"
                            class="action-steps-group" id="order-status-form">
                            @csrf
                            @method('PATCH')
                            <div
                                style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">
                                <i class="fa-solid fa-angles-right"></i>
                                {{ in_array('returned', $nextOrderStatuses, true) ? 'Xử lý kiện hàng hoàn về:' : 'Bước đơn hàng tiếp theo:' }}
                            </div>
                            @foreach($nextOrderStatuses as $status)
                                @php
                                    $btn = $orderActionButtons[$status] ?? [
                                        'label' => $orderStatuses[$status] ?? $status,
                                        'icon' => 'fa-arrow-right',
                                        'class' => 'btn-step-confirm',
                                        'confirm_title' => 'Xác nhận thao tác',
                                        'confirm_msg' => 'Bạn có chắc chắn muốn thực hiện hành động này?',
                                        'confirm_icon' => 'fa-circle-question',
                                        'confirm_type' => 'primary',
                                    ];
                                @endphp
                                @if($status === 'returned')
                                    <button type="button" class="btn-step {{ $btn['class'] }} js-open-return-modal"
                                        id="btnTriggerReturnModal">
                                        <i class="fa-solid {{ $btn['icon'] }}"></i>
                                        <span>{{ $btn['label'] }}</span>
                                    </button>
                                    <span
                                        style="font-size: 0.69rem; color: #b45309; display: block; margin-top: -4px; margin-bottom: 6px; line-height: 1.25; padding-left: 2px;">
                                        * Bấm để nhập lý do hoàn hàng, tải ảnh bằng chứng và tự động nhập lại kho.
                                    </span>
                                @else
                                    <button type="button" class="btn-step {{ $btn['class'] }} js-open-confirm-modal"
                                        data-form-id="order-status-form" data-field-name="order_status" data-field-value="{{ $status }}"
                                        data-confirm-title="{{ $btn['confirm_title'] ?? 'Xác nhận thao tác' }}"
                                        data-confirm-msg="{{ $btn['confirm_msg'] ?? 'Bạn có chắc chắn muốn thực hiện hành động này?' }}"
                                        data-confirm-icon="{{ $btn['confirm_icon'] ?? 'fa-circle-question' }}"
                                        data-confirm-type="{{ $btn['confirm_type'] ?? 'primary' }}">
                                        <i class="fa-solid {{ $btn['icon'] }}"></i>
                                        <span>{{ $btn['label'] }}</span>
                                    </button>
                                @endif
                            @endforeach
                        </form>
                    @elseif($order->order_status !== 'shipping')
                        <div class="step-completed-notice"
                            style="margin-top: 8px; @if($isCancelled) background: #fef2f2; border-color: #fecaca; color: #dc2626; @elseif($isReturned) background: #fff7ed; border-color: #ffedd5; color: #c2410c; @endif">
                            @if($isCancelled)
                                <i class="fa-solid fa-ban"></i> Đơn hàng đã hủy tại kho & đã hoàn tồn kho
                            @elseif($isReturned)
                                <i class="fa-solid fa-box-archive"></i> Đơn hàng đã hoàn về kho & đã nhập lại tồn kho
                            @else
                                <i class="fa-solid fa-flag-checkered"></i> Đã hoàn tất quy trình giao vận
                            @endif
                        </div>
                        @if($isReturned)
                            <div
                                style="margin-top: 10px; padding: 12px; background: #fffaf0; border: 1px dashed #fed7aa; border-radius: 8px; font-size: 0.76rem; color: #7c2d12;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <strong><i class="fa-solid fa-file-lines" style="color: #ea580c;"></i> Lý do hoàn hàng:</strong>
                                    @if($order->returned_at)
                                        <span
                                            style="font-size: 0.7rem; color: #9a3412;">{{ $order->returned_at->format('H:i d/m/Y') }}</span>
                                    @endif
                                </div>
                                <p style="margin: 0 0 8px 0; color: #431407; line-height: 1.4;">
                                    {{ $order->return_reason ?: 'Giao không thành công (Khách bom / chuyển hoàn)' }}
                                </p>
                                @if($order->return_proof_image)
                                    <div>
                                        <strong style="display: block; margin-bottom: 5px;"><i class="fa-solid fa-image"
                                                style="color: #ea580c;"></i> Ảnh chứng minh nhận kiện hàng:</strong>
                                        <a href="{{ asset('storage/' . $order->return_proof_image) }}" target="_blank"
                                            title="Bấm để xem ảnh phóng to">
                                            <img src="{{ asset('storage/' . $order->return_proof_image) }}"
                                                alt="Bằng chứng nhận hàng hoàn"
                                                style="max-height: 140px; max-width: 100%; border-radius: 6px; border: 1px solid #fed7aa; box-shadow: 0 2px 4px rgba(0,0,0,0.06); cursor: zoom-in;">
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    <div style="border-top: 1px dashed var(--border-color); padding-top: 14px; margin-top: 14px;">
                        <h4 class="info-label">Trạng thái thanh toán & Đối soát</h4>
                        @if(!($order->isCod() && $order->payment_status === 'unpaid'))
                            <span class="quick-status-trigger badge-payment {{ $paymentClass }}">
                                <span>{{ $displayPaymentStatus }}</span>
                            </span>
                        @endif
                        @if($isReturned)
                            <p style="font-size: 0.76rem; color: #c2410c; margin: 6px 0 0 0; line-height: 1.4;">
                                <i class="fa-solid fa-box-archive"></i> Đơn hàng đã hoàn về kho & đã nhập lại tồn kho thành
                                công.
                            </p>
                        @elseif($isCancelled)
                            @if($order->payment_status === 'paid')
                                <p style="font-size: 0.76rem; color: #7c3aed; margin: 6px 0 0 0; line-height: 1.4;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Đơn đã hủy nhưng đã thanh toán
                                        trước:</strong> Vui lòng chuyển khoản hoàn tiền lại cho khách và bấm nút <em>Xác nhận hoàn
                                        tiền</em> bên dưới.
                                </p>
                            @elseif($order->payment_status === 'refunded')
                                <p style="font-size: 0.76rem; color: #059669; margin: 6px 0 0 0; line-height: 1.4;">
                                    <i class="fa-solid fa-circle-check"></i> Đơn đã hủy và đã hoàn tất hoàn tiền cho khách.
                                </p>
                            @else
                                @if($hasShipped)
                                    <p style="font-size: 0.76rem; color: var(--text-muted); margin: 6px 0 0 0; line-height: 1.4;">
                                        <i class="fa-solid fa-truck-ramp-box"></i> Đơn hàng đã hủy khi đang vận chuyển. Bấm nút <em>Xác
                                            nhận đã nhận lại hàng hoàn</em> ở trên khi nhận kiện hàng từ ĐVVC.
                                    </p>
                                @else
                                    <p style="font-size: 0.76rem; color: #059669; margin: 6px 0 0 0; line-height: 1.4;">
                                        <i class="fa-solid fa-circle-check"></i> Đơn hàng đã hủy khi chưa xuất kho. Sản phẩm đã được tự
                                        động hoàn lại đầy đủ vào tồn kho.
                                    </p>
                                @endif
                            @endif
                        @elseif($order->payment_status === 'unpaid')
                            @if($order->isCod())
                                <p style="font-size: 0.76rem; color: #0369a1; margin: 4px 0 0 0; line-height: 1.4;">
                                    <i class="fa-solid fa-truck-fast"></i> <strong>Thanh toán khi nhận hàng (COD):</strong> Tiền thu
                                    hộ ({{ number_format($order->total_amount, 0, ',', '.') }}đ) sẽ do Shipper thu khi giao thành
                                    công.
                                </p>
                            @else
                                <p style="font-size: 0.76rem; color: #7c3aed; margin: 6px 0 0 0; line-height: 1.4;">
                                    <i class="fa-solid fa-qrcode"></i> <strong>Đơn chuyển khoản:</strong> Hệ thống tự động xác nhận
                                    qua SePay khi tiền vào tài khoản (hoặc Admin xác nhận thủ công bên dưới).
                                </p>
                            @endif
                        @elseif($order->payment_status === 'customer_paid')
                            <p style="font-size: 0.76rem; color: #0284c7; margin: 6px 0 0 0; line-height: 1.4;">
                                <i class="fa-solid fa-hand-holding-dollar"></i> Shipper đã thu tiền mặt của khách. Đang chờ gom
                                đơn đối soát với ĐVVC.
                            </p>
                        @elseif($order->payment_status === 'reconciling')
                            <p style="font-size: 0.76rem; color: #d97706; margin: 6px 0 0 0; line-height: 1.4;">
                                <i class="fa-solid fa-arrows-rotate"></i> Đang trong kỳ đối soát bảng kê với đơn vị vận chuyển.
                            </p>
                        @elseif($order->payment_status === 'paid')
                            <p style="font-size: 0.76rem; color: #16a34a; margin: 6px 0 0 0; line-height: 1.4;">
                                <i class="fa-solid fa-circle-check"></i> Tiền đã về tài khoản ngân hàng của Shop (Doanh thu thực
                                thu).
                            </p>
                        @elseif($order->payment_status === 'discrepancy')
                            <p style="font-size: 0.76rem; color: #dc2626; margin: 6px 0 0 0; line-height: 1.4;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Có chênh lệch tiền thu hộ / cần khiếu nại ĐVVC.
                            </p>
                        @endif
                    </div>

                    @if(!empty($nextPaymentStatuses))
                        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}"
                            class="action-steps-group" id="payment-status-form" style="margin-top: 10px;">
                            @csrf
                            @method('PATCH')
                            <div
                                style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-top: 4px;">
                                <i class="fa-solid fa-angles-right"></i> Bước thanh toán tiếp theo:
                            </div>
                            @foreach($nextPaymentStatuses as $status)
                                @php
                                    $btn = $paymentActionButtons[$status] ?? [
                                        'label' => $paymentStatuses[$status] ?? $status,
                                        'icon' => 'fa-arrow-right',
                                        'class' => 'btn-step-customer-paid',
                                        'confirm_title' => 'Xác nhận thao tác',
                                        'confirm_msg' => 'Bạn có chắc chắn muốn thực hiện hành động này?',
                                        'confirm_icon' => 'fa-circle-question',
                                        'confirm_type' => 'primary',
                                    ];
                                @endphp
                                @if($status === 'refunded')
                                    <button type="button" class="btn-step js-open-refund-modal" id="btnTriggerRefundModal"
                                        style="background: #9333ea !important; border-color: #7e22ce !important; color: #ffffff !important;">
                                        <i class="fa-solid fa-money-bill-transfer"
                                            style="color: #ffffff !important; margin-right: 4px;"></i>
                                        <span style="color: #ffffff !important; font-weight: 700;">Xác nhận hoàn tiền cho khách</span>
                                    </button>
                                    <span
                                        style="font-size: 0.69rem; color: #7c3aed; display: block; margin-top: -4px; margin-bottom: 6px; line-height: 1.25; padding-left: 2px;">
                                        * Bấm để điền STK khách và đính kèm bill chuyển khoản hoàn tiền.
                                    </span>
                                @else
                                    <button type="button" class="btn-step {{ $btn['class'] }} js-open-confirm-modal"
                                        data-form-id="payment-status-form" data-field-name="payment_status"
                                        data-field-value="{{ $status }}"
                                        data-confirm-title="{{ $btn['confirm_title'] ?? 'Xác nhận thao tác' }}"
                                        data-confirm-msg="{{ $btn['confirm_msg'] ?? 'Bạn có chắc chắn muốn thực hiện hành động này?' }}"
                                        data-confirm-icon="{{ $btn['confirm_icon'] ?? 'fa-circle-question' }}"
                                        data-confirm-type="{{ $btn['confirm_type'] ?? 'primary' }}">
                                        <i class="fa-solid {{ $btn['icon'] }}"></i>
                                        <span>{{ $btn['label'] }}</span>
                                    </button>
                                @endif
                            @endforeach
                        </form>
                    @else
                        @if($order->order_status === 'completed' && $order->payment_status === 'paid')
                            <div class="step-completed-notice"
                                style="margin-top: 8px; background: #ecfdf5; border-color: #a7f3d0; color: #059669; font-weight: 700;">
                                <i class="fa-solid fa-circle-check"></i> Đã hoàn tất 100% quy trình đơn hàng & đối soát thành công
                            </div>
                        @elseif($order->payment_status === 'refunded')
                            <div class="step-completed-notice"
                                style="margin-top: 8px; background: #faf5ff; border-color: #e9d5ff; color: #7e22ce; font-weight: 700;">
                                <i class="fa-solid fa-check-double"></i> Đã hoàn tiền cho khách hàng thành công
                            </div>
                        @elseif(in_array($order->order_status, ['cancelled', 'returned'], true))
                            <div class="step-completed-notice"
                                style="margin-top: 8px; background: #fef2f2; border-color: #fecaca; color: #dc2626; font-weight: 600;">
                                <i class="fa-solid fa-ban"></i> Đơn đã hủy · Không phát sinh thu tiền
                            </div>
                        @elseif(!$order->isCod())
                            <div class="step-completed-notice" style="margin-top: 8px;">
                                <i class="fa-solid fa-shield-check"></i> Đã hoàn tất chu kỳ đối soát
                            </div>
                        @endif
                    @endif

                    @if($order->payment_status === 'refunded' || $order->refund_proof_image || $order->refund_account_number)
                        <div class="refund-info-box"
                            style="margin-top: 14px; padding: 12px; background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 10px;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 0.78rem; font-weight: 700; color: #7e22ce; text-transform: uppercase;">
                                    <i class="fa-solid fa-money-bill-transfer" style="margin-right: 4px;"></i> Thông tin hoàn
                                    tiền cho khách
                                </span>
                                @if($order->refunded_at)
                                    <span style="font-size: 0.68rem; color: #9333ea; font-weight: 600;">
                                        {{ $order->refunded_at->format('H:i d/m/Y') }}
                                    </span>
                                @endif
                            </div>

                            <div
                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.76rem; margin-bottom: 8px;">
                                <div>
                                    <span style="color: #6b7280;">Ngân hàng:</span>
                                    <div style="font-weight: 700; color: #1f2937;">{{ $order->refund_bank_name ?: 'Chưa ghi' }}
                                    </div>
                                </div>
                                <div>
                                    <span style="color: #6b7280;">Số tài khoản:</span>
                                    <div style="font-weight: 700; color: #7e22ce;">
                                        {{ $order->refund_account_number ?: 'Chưa ghi' }}</div>
                                </div>
                                <div>
                                    <span style="color: #6b7280;">Chủ tài khoản:</span>
                                    <div style="font-weight: 700; color: #1f2937; text-transform: uppercase;">
                                        {{ $order->refund_account_name ?: $order->recipient_name }}</div>
                                </div>
                                <div>
                                    <span style="color: #6b7280;">Số tiền đã hoàn:</span>
                                    <div style="font-weight: 700; color: #dc2626;">
                                        {{ number_format((float) ($order->refund_amount ?? $order->total_amount), 0, ',', '.') }}
                                        đ</div>
                                </div>
                            </div>

                            @if($order->refund_note)
                                <div
                                    style="font-size: 0.74rem; color: #4b5563; margin-bottom: 8px; background: #fff; padding: 6px 8px; border-radius: 6px; border: 1px solid #f3e8ff;">
                                    <strong>Ghi chú:</strong> {{ $order->refund_note }}
                                </div>
                            @endif

                            @if($order->refund_proof_image)
                                <div>
                                    <div style="font-size: 0.72rem; font-weight: 600; color: #6b7280; margin-bottom: 4px;">Biên lai
                                        / Bill chuyển khoản:</div>
                                    <a href="{{ asset('storage/' . $order->refund_proof_image) }}" target="_blank"
                                        style="display: inline-block; border-radius: 6px; overflow: hidden; border: 1px solid #d8b4fe; max-width: 160px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                        <img src="{{ asset('storage/' . $order->refund_proof_image) }}" alt="Bill hoàn tiền"
                                            style="width: 100%; height: auto; display: block;">
                                    </a>
                                    <div style="font-size: 0.68rem; color: #9333ea; margin-top: 2px;">(Bấm để xem ảnh gốc kích thước
                                        lớn)</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="order-details-card">
                <h3 class="order-details-card-title">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Tóm tắt thanh toán</span>
                </h3>

                <div class="summary-row">
                    <span>Tạm tính</span>
                    <span
                        style="font-weight: 600; color: var(--text-main);">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <span
                        style="font-weight: 600; color: var(--text-main);">{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}đ</span>
                </div>
                <div class="summary-row">
                    <span>Giảm giá {{ $order->voucher?->code ? '(' . $order->voucher->code . ')' : '' }}</span>
                    <span
                        style="font-weight: 600; color: #c5221f;">-{{ number_format((float) $order->discount_amount, 0, ',', '.') }}đ</span>
                </div>
                @if((float) $order->shipping_discount > 0)
                    <div class="summary-row">
                        <span>Ưu đãi vận
                            chuyển{{ $order->shippingVoucher?->code ? ' (' . $order->shippingVoucher->code . ')' : '' }}</span>
                        <span
                            style="font-weight: 600; color: #c5221f;">-{{ number_format((float) $order->shipping_discount, 0, ',', '.') }}đ</span>
                    </div>
                @endif
                <div class="summary-row total-row">
                    <span>Tổng tiền</span>
                    <span
                        style="color: #0d9488; font-size: 1.15rem;">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</span>
                </div>

                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed var(--border-color);">
                    <h4 class="info-label" style="margin-bottom: 10px;">Phương thức thanh toán</h4>
                    <div
                        style="display: flex; align-items: center; gap: 12px; background-color: var(--bg-color); padding: 12px; border-radius: 8px;">
                        <i class="fa-solid fa-credit-card" style="font-size: 1.5rem; color: var(--primary);"></i>
                        <div style="display: flex; flex-direction: column;">
                            <span
                                style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $order->isBankTransfer() ? 'Bank' : 'COD' }}</span>
                            <span style="font-size: 0.75rem; font-weight: 800; margin-top: 2px;"
                                class="badge-payment {{ $paymentClass }}">{{ $displayPaymentStatus }}</span>
                        </div>
                    </div>

                    @if($order->reconciliation_note)
                        <div
                            style="margin-top: 10px; padding: 10px 12px; background: #fefce8; border: 1px solid #fef08a; border-radius: 6px; font-size: 0.82rem; color: #854d0e;">
                            <strong><i class="fa-solid fa-note-sticky"></i> Ghi chú đối soát:</strong>
                            {{ $order->reconciliation_note }}
                        </div>
                    @endif

                    @if($order->reconciled_at)
                        <div
                            style="margin-top: 8px; font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-circle-check" style="color: #059669;"></i>
                            <span>Shop xác nhận đã nhận tiền:
                                <strong>{{ $order->reconciled_at->format('d/m/Y H:i') }}</strong></span>
                        </div>
                    @endif
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
                    @elseif($order->order_status === 'returned')
                        <li class="timeline-item">
                            <span class="timeline-icon-dot active" style="background: #ea580c; color: white;"><i
                                    class="fa-solid fa-box-archive" style="font-size: 0.65rem;"></i></span>
                            <div class="timeline-item-title" style="color: #c2410c; font-weight: 700;">Đã nhận lại hàng hoàn &
                                Nhập kho</div>
                            <div class="timeline-item-time">
                                {{ $order->returned_at?->format('d/m/Y H:i') ?? $order->updated_at?->format('d/m/Y H:i') }}
                            </div>
                        </li>
                    @elseif($order->order_status === 'cancelled')
                        <li class="timeline-item">
                            <span class="timeline-icon-dot pending"><i class="fa-solid fa-xmark"
                                    style="font-size: 0.65rem;"></i></span>
                            <div class="timeline-item-title" style="color: var(--text-muted);">Đơn hàng đã hủy</div>
                            <div class="timeline-item-time" style="color: var(--text-muted);">
                                {{ $order->updated_at?->format('d/m/Y H:i') }}</div>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="order-details-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <span class="info-label" style="margin: 0; font-size: 0.72rem; font-weight: 700;">GHI CHÚ KHÁCH
                        HÀNG</span>
                </div>

                <div class="note-box">
                    {{ $order->note ?: 'Đơn hàng không có ghi chú.' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Custom Confirmation Modal -->
    <div class="custom-confirm-backdrop" id="statusConfirmModal" aria-hidden="true">
        <div class="custom-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
            <div class="custom-confirm-icon-wrapper primary" id="confirmModalIconWrapper">
                <i class="fa-solid fa-clipboard-check" id="confirmModalIcon"></i>
            </div>
            <h3 class="custom-confirm-title" id="confirmModalTitle">Xác nhận thao tác</h3>
            <p class="custom-confirm-message" id="confirmModalMessage">Bạn có chắc chắn muốn thực hiện hành động này?</p>
            <div class="custom-confirm-badge-code">
                <i class="fa-solid fa-receipt"></i> Đơn hàng: <strong>{{ $orderCode }}</strong>
            </div>

            <div class="custom-confirm-actions">
                <button type="button" class="btn-confirm-cancel" id="btnConfirmCancel">
                    <i class="fa-solid fa-xmark"></i> Hủy bỏ
                </button>
                <button type="button" class="btn-confirm-accept primary" id="btnConfirmAccept">
                    <i class="fa-solid fa-check"></i> Xác nhận
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Xác nhận nhận lại hàng hoàn kèm lý do và hình ảnh -->
    <div class="custom-confirm-backdrop" id="returnProofModal" aria-hidden="true">
        <div class="custom-confirm-dialog return-modal-dialog" role="dialog" aria-modal="true">
            <div
                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 40px; height: 40px; border-radius: 10px; background: #fff7ed; border: 1px solid #fed7aa; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a;">Biên bản nhận lại hàng
                            hoàn</h3>
                        <span style="font-size: 0.72rem; color: #64748b;">Đơn hàng #{{ $orderCode }} · Vận đơn GHN:
                            <strong>{{ $order->shipment?->tracking_code }}</strong></span>
                    </div>
                </div>
                <button type="button" id="btnCloseReturnModal"
                    style="background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 4px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" enctype="multipart/form-data"
                id="returnProofForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="order_status" value="returned">

                <div style="margin-bottom: 14px;">
                    <label
                        style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        1. Lý do hoàn hàng <span style="color: #dc2626;">*</span>
                    </label>
                    <!-- Quick Tags -->
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                        <button type="button" class="btn-quick-reason"
                            data-reason="Khách không nghe máy / Thuê bao (Giao thất bại 3 lần)">📞 Khách không nghe máy (3
                            lần)</button>
                        <button type="button" class="btn-quick-reason"
                            data-reason="Khách từ chối nhận hàng (Bom hàng / Đổi ý)">🚫 Khách từ chối nhận (Bom
                            hàng)</button>
                        <button type="button" class="btn-quick-reason"
                            data-reason="Sai thông tin địa chỉ / số điện thoại người nhận">📍 Sai địa chỉ / SĐT</button>
                        <button type="button" class="btn-quick-reason"
                            data-reason="Kiện hàng bị móp méo / vỡ hỏng trong lúc vận chuyển">📦 Hàng móp méo / vỡ
                            hỏng</button>
                    </div>
                    <textarea name="return_reason" id="returnReasonInput" rows="3" required
                        placeholder="Nhập chi tiết lý do bưu tá GHN chuyển hoàn kiện hàng về kho..."
                        style="width: 100%; padding: 10px 12px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 8px; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-bottom: 14px;">
                    <label
                        style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        2. Hình ảnh chứng minh nhận kiện hàng <span style="color: #dc2626;">*</span>
                    </label>
                    <p style="margin: 0 0 8px 0; font-size: 0.7rem; color: #64748b;">
                        Chụp ảnh kiện hàng khi nhận lại từ bưu tá (rõ tem phiếu GHN, lý do shipper dán trên gói hàng hoặc
                        tình trạng vỏ hộp).
                    </p>

                    <input type="file" name="return_proof_image" id="returnProofImageInput" accept="image/*" required
                        style="display: none;">

                    <div id="returnUploadZone"
                        style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 18px 12px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s ease;">
                        <i class="fa-solid fa-camera" style="font-size: 1.8rem; color: #ea580c; margin-bottom: 6px;"></i>
                        <div style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">Bấm để tải ảnh lên hoặc kéo thả
                            ảnh vào đây</div>
                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 3px;">Hỗ trợ: JPG, PNG, WEBP (Tối đa 5MB)
                        </div>
                    </div>

                    <div id="returnPreviewBox"
                        style="display: none; margin-top: 8px; position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; text-align: center; padding: 6px;">
                        <img id="returnPreviewImg" src="" alt="Proof Preview"
                            style="max-height: 180px; max-width: 100%; border-radius: 6px; display: inline-block;">
                        <button type="button" id="btnRemoveProofImage" title="Xóa ảnh"
                            style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.65); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div
                    style="margin-bottom: 18px; padding: 10px 12px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; font-size: 0.74rem; color: #b45309; line-height: 1.4;">
                    <i class="fa-solid fa-boxes-stacked" style="margin-right: 4px;"></i>
                    Khi xác nhận, hệ thống sẽ <strong>tự động nhập lại {{ $order->items->sum('quantity') }} sản
                        phẩm</strong> vào kho và lưu bằng chứng kiểm kê.
                </div>

                <div
                    style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                    <button type="button" class="btn-confirm-cancel" id="btnCancelReturnModal"
                        style="padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #475569; cursor: pointer;">
                        Hủy bỏ
                    </button>
                    <button type="submit" id="btnSubmitReturnProof"
                        style="padding: 8px 18px; font-size: 0.82rem; font-weight: 700; border-radius: 8px; border: none; background: #ea580c; color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(234, 88, 12, 0.3);">
                        <i class="fa-solid fa-box-archive"></i> Xác nhận nhận hàng & Nhập kho
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL XÁC NHẬN HOÀN TIỀN VỚI THÔNG TIN STK & BILL --}}
    <div class="custom-confirm-backdrop" id="refundProofModal" aria-hidden="true">
        <div class="custom-confirm-dialog return-modal-dialog" role="dialog" aria-modal="true"
            style="max-width: 540px; text-align: left;">
            <div
                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div
                        style="width: 42px; height: 42px; border-radius: 10px; background: #faf5ff; border: 1px solid #e9d5ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a;">Xác nhận hoàn tiền cho
                            khách</h3>
                        <span style="font-size: 0.72rem; color: #64748b;">Đơn hàng #{{ $orderCode }} · Khách hàng:
                            <strong>{{ $order->recipient_name }}</strong></span>
                    </div>
                </div>
                <button type="button" id="btnCloseRefundModal"
                    style="background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 4px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" enctype="multipart/form-data"
                id="refundProofForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="payment_status" value="refunded">

                <!-- Card Tóm tắt số tiền hoàn & SĐT khách -->
                <div
                    style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.72rem; color: #6b7280; display: block;">Tổng tiền cần hoàn:</span>
                        <strong
                            style="font-size: 1.15rem; color: #7e22ce;">{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            đ</strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.72rem; color: #6b7280; display: block;">SĐT liên hệ khách:</span>
                        <strong style="font-size: 0.85rem; color: #1e293b;">{{ $order->recipient_phone }}</strong>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label
                            style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Ngân hàng nhận <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="refund_bank_name" id="refundBankNameInput" required
                            style="width: 100%; padding: 8px 10px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; background: #fff;">
                            <option value="">-- Chọn ngân hàng --</option>
                            <option value="Vietcombank">Vietcombank</option>
                            <option value="MBBank">MBBank (Quân đội)</option>
                            <option value="Techcombank">Techcombank</option>
                            <option value="VietinBank">VietinBank</option>
                            <option value="BIDV">BIDV</option>
                            <option value="ACB">ACB</option>
                            <option value="VPBank">VPBank</option>
                            <option value="TPBank">TPBank</option>
                            <option value="Sacombank">Sacombank</option>
                            <option value="HDBank">HDBank</option>
                            <option value="Agribank">Agribank</option>
                            <option value="MSB">MSB</option>
                            <option value="VIB">VIB</option>
                            <option value="OCB">OCB</option>
                            <option value="SHB">SHB</option>
                            <option value="SeABank">SeABank</option>
                            <option value="Khác">Ngân hàng khác</option>
                        </select>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Số tài khoản nhận <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="refund_account_number" id="refundAccountNumberInput" required
                            placeholder="Ví dụ: 0901234567"
                            style="width: 100%; padding: 8px 10px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label
                            style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Tên chủ tài khoản <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="refund_account_name" id="refundAccountNameInput" required
                            placeholder="Ví dụ: NGUYEN VAN A" value="{{ mb_strtoupper($order->recipient_name, 'UTF-8') }}"
                            style="width: 100%; padding: 8px 10px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; text-transform: uppercase;">
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Số tiền hoàn (VNĐ) <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="refund_amount" id="refundAmountInput" required
                            value="{{ number_format((float) $order->total_amount, 0, ',', '.') }}"
                            style="width: 100%; padding: 8px 10px; font-size: 0.8rem; font-weight: 700; color: #7e22ce; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label
                        style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Ảnh biên lai / Bill chuyển khoản hoàn tiền <span style="color: #dc2626;">*</span>
                    </label>
                    <p style="margin: 0 0 6px 0; font-size: 0.69rem; color: #64748b;">
                        Tải lên ảnh chụp màn hình chuyển khoản thành công từ app ngân hàng hoặc ủy nhiệm chi.
                    </p>

                    <input type="file" name="refund_proof_image" id="refundProofImageInput" accept="image/*" required
                        style="display: none;">

                    <div id="refundUploadZone"
                        style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 16px 12px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s ease;">
                        <i class="fa-solid fa-file-invoice-dollar"
                            style="font-size: 1.8rem; color: #9333ea; margin-bottom: 6px;"></i>
                        <div style="font-size: 0.78rem; font-weight: 600; color: #1e293b;">Bấm để tải ảnh bill chuyển khoản
                            hoặc kéo thả vào đây</div>
                        <div style="font-size: 0.68rem; color: #94a3b8; margin-top: 2px;">Định dạng: JPG, PNG, WEBP (Tối đa
                            5MB)</div>
                    </div>

                    <div id="refundPreviewBox"
                        style="display: none; margin-top: 8px; position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; text-align: center; padding: 6px;">
                        <img id="refundPreviewImg" src="" alt="Refund Proof Preview"
                            style="max-height: 180px; max-width: 100%; border-radius: 6px; display: inline-block;">
                        <button type="button" id="btnRemoveRefundProofImage" title="Xóa ảnh"
                            style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.65); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label
                        style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Ghi chú hoàn tiền <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="refund_note" id="refundNoteInput" rows="2" required
                        placeholder="Bắt buộc nhập ghi chú hoàn tiền (Ví dụ: Đã liên hệ khách và chuyển khoản hoàn tiền đơn bom thành công)..."
                        style="width: 100%; padding: 8px 10px; font-size: 0.78rem; border: 1px solid #cbd5e1; border-radius: 6px; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <div
                    style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                    <button type="button" id="btnCancelRefundModal"
                        style="padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #475569; cursor: pointer;">
                        Hủy bỏ
                    </button>
                    <button type="submit" id="btnSubmitRefundProof"
                        style="padding: 8px 18px; font-size: 0.82rem; font-weight: 700; border-radius: 8px; border: none; background: #9333ea; color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(147, 51, 234, 0.3);">
                        <i class="fa-solid fa-check"></i> Xác nhận & Hoàn tất hoàn tiền
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('statusConfirmModal');
            const iconWrapper = document.getElementById('confirmModalIconWrapper');
            const iconElem = document.getElementById('confirmModalIcon');
            const titleElem = document.getElementById('confirmModalTitle');
            const messageElem = document.getElementById('confirmModalMessage');
            const btnCancel = document.getElementById('btnConfirmCancel');
            const btnAccept = document.getElementById('btnConfirmAccept');

            let currentTriggerData = null;

            function openModal(data) {
                currentTriggerData = data;
                titleElem.textContent = data.title || 'Xác nhận thao tác';
                messageElem.textContent = data.msg || 'Bạn có chắc chắn muốn thực hiện hành động này?';

                // Reset classes
                iconWrapper.className = 'custom-confirm-icon-wrapper ' + (data.type || 'primary');
                btnAccept.className = 'btn-confirm-accept ' + (data.type || 'primary');

                // Icon
                iconElem.className = 'fa-solid ' + (data.icon || 'fa-circle-question');

                btnAccept.disabled = false;
                btnAccept.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận';

                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                currentTriggerData = null;
            }

            document.querySelectorAll('.js-open-confirm-modal').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    openModal({
                        formId: this.dataset.formId,
                        fieldName: this.dataset.fieldName,
                        fieldValue: this.dataset.fieldValue,
                        title: this.dataset.confirmTitle,
                        msg: this.dataset.confirmMsg,
                        icon: this.dataset.confirmIcon,
                        type: this.dataset.confirmType
                    });
                });
            });

            if (btnCancel) {
                btnCancel.addEventListener('click', closeModal);
            }

            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (modal && modal.classList.contains('active')) {
                        closeModal();
                    }
                    if (returnModal && returnModal.classList.contains('active')) {
                        closeReturnModal();
                    }
                }
            });

            if (btnAccept) {
                btnAccept.addEventListener('click', function () {
                    if (!currentTriggerData || !currentTriggerData.formId) {
                        closeModal();
                        return;
                    }

                    const form = document.getElementById(currentTriggerData.formId);
                    if (!form) {
                        closeModal();
                        return;
                    }

                    if (currentTriggerData.fieldName && currentTriggerData.fieldValue) {
                        // Remove existing hidden input with same name if any
                        const existingInput = form.querySelector(`input[name="${currentTriggerData.fieldName}"]`);
                        if (existingInput) {
                            existingInput.remove();
                        }

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = currentTriggerData.fieldName;
                        hiddenInput.value = currentTriggerData.fieldValue;
                        form.appendChild(hiddenInput);
                    }

                    btnAccept.disabled = true;
                    btnAccept.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

                    form.submit();
                });
            }

            // ==========================================
            // RETURN PROOF MODAL LOGIC
            // ==========================================
            const returnModal = document.getElementById('returnProofModal');
            const btnOpenReturn = document.querySelectorAll('.js-open-return-modal');
            const btnCloseReturn = document.getElementById('btnCloseReturnModal');
            const btnCancelReturn = document.getElementById('btnCancelReturnModal');
            const returnForm = document.getElementById('returnProofForm');
            const returnReasonInput = document.getElementById('returnReasonInput');
            const returnImageInput = document.getElementById('returnProofImageInput');
            const returnUploadZone = document.getElementById('returnUploadZone');
            const returnPreviewBox = document.getElementById('returnPreviewBox');
            const returnPreviewImg = document.getElementById('returnPreviewImg');
            const btnRemoveProofImage = document.getElementById('btnRemoveProofImage');
            const btnSubmitReturnProof = document.getElementById('btnSubmitReturnProof');

            function openReturnModal() {
                if (!returnModal) return;
                returnModal.classList.add('active');
                returnModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeReturnModal() {
                if (!returnModal) return;
                returnModal.classList.remove('active');
                returnModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            btnOpenReturn.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openReturnModal();
                });
            });

            if (btnCloseReturn) btnCloseReturn.addEventListener('click', closeReturnModal);
            if (btnCancelReturn) btnCancelReturn.addEventListener('click', closeReturnModal);
            if (returnModal) {
                returnModal.addEventListener('click', function (e) {
                    if (e.target === returnModal) closeReturnModal();
                });
            }

            // Quick tags
            document.querySelectorAll('.btn-quick-reason').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (returnReasonInput) {
                        returnReasonInput.value = this.dataset.reason;
                        returnReasonInput.focus();
                    }
                });
            });

            // Upload zone interactions
            if (returnUploadZone && returnImageInput) {
                returnUploadZone.addEventListener('click', function () {
                    returnImageInput.click();
                });

                returnUploadZone.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '#ea580c';
                    this.style.background = '#fff7ed';
                });

                returnUploadZone.addEventListener('dragleave', function () {
                    this.style.borderColor = '#cbd5e1';
                    this.style.background = '#f8fafc';
                });

                returnUploadZone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '#cbd5e1';
                    this.style.background = '#f8fafc';
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        returnImageInput.files = e.dataTransfer.files;
                        handleImageSelected(e.dataTransfer.files[0]);
                    }
                });

                returnImageInput.addEventListener('change', function () {
                    if (this.files && this.files.length > 0) {
                        handleImageSelected(this.files[0]);
                    }
                });
            }

            function handleImageSelected(file) {
                if (!file || !file.type.startsWith('image/')) {
                    alert('Vui lòng chọn file hình ảnh (JPG, PNG, WEBP).');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Dung lượng ảnh tối đa là 5MB.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (returnPreviewImg) returnPreviewImg.src = e.target.result;
                    if (returnPreviewBox) returnPreviewBox.style.display = 'block';
                    if (returnUploadZone) returnUploadZone.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }

            if (btnRemoveProofImage) {
                btnRemoveProofImage.addEventListener('click', function () {
                    if (returnImageInput) returnImageInput.value = '';
                    if (returnPreviewImg) returnPreviewImg.src = '';
                    if (returnPreviewBox) returnPreviewBox.style.display = 'none';
                    if (returnUploadZone) returnUploadZone.style.display = 'block';
                });
            }

            // Submit handler
            if (returnForm) {
                returnForm.addEventListener('submit', function (e) {
                    if (!returnReasonInput || !returnReasonInput.value.trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập lý do hoàn hàng.');
                        if (returnReasonInput) returnReasonInput.focus();
                        return;
                    }

                    if (!returnImageInput || !returnImageInput.files || returnImageInput.files.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng tải lên hình ảnh chứng minh nhận lại kiện hàng hoàn.');
                        return;
                    }

                    if (btnSubmitReturnProof) {
                        btnSubmitReturnProof.disabled = true;
                        btnSubmitReturnProof.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu biên bản & nhập kho...';
                    }
                });
            }

            // ==========================================
            // REFUND PROOF MODAL LOGIC
            // ==========================================
            const refundModal = document.getElementById('refundProofModal');
            const btnOpenRefund = document.querySelectorAll('.js-open-refund-modal');
            const btnCloseRefund = document.getElementById('btnCloseRefundModal');
            const btnCancelRefund = document.getElementById('btnCancelRefundModal');
            const refundForm = document.getElementById('refundProofForm');
            const refundBankSelect = document.getElementById('refundBankNameInput');
            const refundAccountInput = document.getElementById('refundAccountNumberInput');
            const refundNameInput = document.getElementById('refundAccountNameInput');
            const refundAmountInput = document.getElementById('refundAmountInput');
            const refundImageInput = document.getElementById('refundProofImageInput');
            const refundUploadZone = document.getElementById('refundUploadZone');
            const refundPreviewBox = document.getElementById('refundPreviewBox');
            const refundPreviewImg = document.getElementById('refundPreviewImg');
            const btnRemoveRefundProofImage = document.getElementById('btnRemoveRefundProofImage');
            const btnSubmitRefundProof = document.getElementById('btnSubmitRefundProof');

            function openRefundModal() {
                if (!refundModal) return;
                refundModal.classList.add('active');
                refundModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeRefundModal() {
                if (!refundModal) return;
                refundModal.classList.remove('active');
                refundModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            btnOpenRefund.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openRefundModal();
                });
            });

            if (btnCloseRefund) btnCloseRefund.addEventListener('click', closeRefundModal);
            if (btnCancelRefund) btnCancelRefund.addEventListener('click', closeRefundModal);
            if (refundModal) {
                refundModal.addEventListener('click', function (e) {
                    if (e.target === refundModal) closeRefundModal();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (refundModal && refundModal.classList.contains('active')) closeRefundModal();
                }
            });

            // Upload zone interactions for refund bill
            if (refundUploadZone && refundImageInput) {
                refundUploadZone.addEventListener('click', function () {
                    refundImageInput.click();
                });

                refundUploadZone.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '#9333ea';
                    this.style.background = '#faf5ff';
                });

                refundUploadZone.addEventListener('dragleave', function () {
                    this.style.borderColor = '#cbd5e1';
                    this.style.background = '#f8fafc';
                });

                refundUploadZone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '#cbd5e1';
                    this.style.background = '#f8fafc';
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        refundImageInput.files = e.dataTransfer.files;
                        handleRefundImageSelected(e.dataTransfer.files[0]);
                    }
                });

                refundImageInput.addEventListener('change', function () {
                    if (this.files && this.files.length > 0) {
                        handleRefundImageSelected(this.files[0]);
                    }
                });
            }

            function handleRefundImageSelected(file) {
                if (!file || !file.type.startsWith('image/')) {
                    alert('Vui lòng chọn file hình ảnh (JPG, PNG, WEBP).');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Dung lượng ảnh tối đa là 5MB.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (refundPreviewImg) refundPreviewImg.src = e.target.result;
                    if (refundPreviewBox) refundPreviewBox.style.display = 'block';
                    if (refundUploadZone) refundUploadZone.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }

            if (btnRemoveRefundProofImage) {
                btnRemoveRefundProofImage.addEventListener('click', function () {
                    if (refundImageInput) refundImageInput.value = '';
                    if (refundPreviewImg) refundPreviewImg.src = '';
                    if (refundPreviewBox) refundPreviewBox.style.display = 'none';
                    if (refundUploadZone) refundUploadZone.style.display = 'block';
                });
            }

            // Submit handler for refund form
            if (refundForm) {
                refundForm.addEventListener('submit', function (e) {
                    if (!refundBankSelect || !refundBankSelect.value) {
                        e.preventDefault();
                        alert('Vui lòng chọn ngân hàng nhận tiền của khách.');
                        if (refundBankSelect) refundBankSelect.focus();
                        return;
                    }

                    if (!refundAccountInput || !refundAccountInput.value.trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập số tài khoản ngân hàng của khách.');
                        if (refundAccountInput) refundAccountInput.focus();
                        return;
                    }

                    if (!refundNameInput || !refundNameInput.value.trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập tên chủ tài khoản người nhận (bắt buộc).');
                        if (refundNameInput) refundNameInput.focus();
                        return;
                    }

                    if (!refundAmountInput || !refundAmountInput.value.trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập số tiền hoàn (bắt buộc).');
                        if (refundAmountInput) refundAmountInput.focus();
                        return;
                    }

                    if (!refundImageInput || !refundImageInput.files || refundImageInput.files.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng tải lên ảnh chụp bill / biên lai chuyển khoản hoàn tiền (bắt buộc).');
                        return;
                    }

                    const refundNoteInput = document.getElementById('refundNoteInput');
                    if (!refundNoteInput || !refundNoteInput.value.trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập ghi chú hoàn tiền (bắt buộc).');
                        if (refundNoteInput) refundNoteInput.focus();
                        return;
                    }

                    if (btnSubmitRefundProof) {
                        btnSubmitRefundProof.disabled = true;
                        btnSubmitRefundProof.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu thông tin & cập nhật hoàn tiền...';
                    }
                });
            }
        });
    </script>
@endsection