@extends('admin.layouts.app')

@section('title', 'Quan ly don hang')

@section('styles')
<style>
    .orders-table th { white-space: nowrap !important; }
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

    /* Thanh đối soát hàng loạt */
    .bulk-reconcile-bar {
        position: sticky;
        top: 10px;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 20px;
        margin-bottom: 16px;
        background: #1e293b;
        color: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        animation: fadeInDown 0.25s ease-out;
    }
    .bulk-reconcile-bar.show { display: flex; }
    .bulk-reconcile-left { display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 0.9rem; }
    .bulk-reconcile-actions { display: flex; align-items: center; gap: 10px; }
    .btn-bulk-action {
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-bulk-reconciling { background: #ea580c; color: #fff; }
    .btn-bulk-reconciling:hover { background: #c2410c; }
    .btn-bulk-paid { background: #059669; color: #fff; }
    .btn-bulk-paid:hover { background: #047857; }
    .btn-bulk-clear { background: transparent; color: #94a3b8; border: 1px solid #475569; }
    .btn-bulk-clear:hover { background: #334155; color: #fff; }

    /* Modal styles */
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
        max-width: 420px;
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
    .quick-status-item {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 8px 12px;
        font-size: 0.78rem;
        color: var(--text-main);
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .quick-status-item:hover {
        background: #f8fafc;
        color: var(--primary);
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
    <div class="filter-col">
        <label class="filter-label">Vận chuyển</label>
        <select name="shipping_method_id" class="filter-select">
            <option value="">Tất cả</option>
            @foreach($shippingMethods as $method)
                <option value="{{ $method->id }}" @selected((string) ($filters['shipping_method_id'] ?? '') === (string) $method->id)>{{ $method->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-col orders-filter-actions">
        <a href="{{ route('admin.orders') }}"  style="text-decoration:none"class="btn-clear-filters">Xoá Bộ Lọc</a>
    </div>
</form>

<!-- Thanh công cụ Đối soát hàng loạt -->
<div class="bulk-reconcile-bar" id="bulk-reconcile-bar">
    <div class="bulk-reconcile-left">
        <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
        <span>Đã chọn <strong id="selected-orders-count">0</strong> đơn hàng</span>
    </div>
    <div class="bulk-reconcile-actions">
        <button type="button" class="btn-bulk-action btn-bulk-reconciling" onclick="triggerBulkReconcile('reconciling')">
            <i class="fa-solid fa-clock-rotate-left"></i> Chuyển sang: Đang đối soát
        </button>
        <button type="button" class="btn-bulk-action btn-bulk-paid" onclick="triggerBulkReconcile('paid')">
            <i class="fa-solid fa-circle-check"></i> Xác nhận: Shop đã nhận tiền
        </button>
        <button type="button" class="btn-bulk-action btn-bulk-clear" id="btn-clear-selection">
            Bỏ chọn
        </button>
    </div>
</div>

<form id="bulk-reconcile-form" method="POST" action="{{ route('admin.orders.bulk-reconcile') }}" style="display: none;">
    @csrf
    <input type="hidden" name="target_status" id="bulk-target-status" value="">
    <div id="bulk-order-ids-container"></div>
</form>

<div class="table-card">
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="select-all-orders" title="Chọn tất cả" style="cursor: pointer; width: 16px; height: 16px;">
                    </th>
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
                        $isCancelled = $order->order_status === 'cancelled';
                        $isReturned = $order->order_status === 'returned';
                        $isShipping = $order->order_status === 'shipping';
                        $shipmentStatus = $order->shipment?->status;
                        $isReadyToPick = $isShipping && ($shipmentStatus === 'ready_to_pick' || empty($shipmentStatus));
                        $isFailed = $isShipping && in_array($shipmentStatus, ['delivery_fail', 'waiting_to_return', 'return', 'returning', 'return_transporting', 'damage', 'lost'], true);
                        $isCod = $order->isCod();

                        // Trạng thái đơn hàng tiếp theo (chỉ cho phép khi ở pending hoặc confirmed, hoặc khi failed)
                        $nextOrderStatuses = match ($order->order_status) {
                            'pending' => ['confirmed', 'cancelled'],
                            'confirmed' => ['shipping', 'cancelled'],
                            'shipping' => ($isFailed ? ['returned'] : []),
                            default => [],
                        };

                        // Khóa thanh toán đơn COD khi đang vận chuyển (chưa giao thành công)
                        $isCodUnpaidInTransit = $isCod && in_array($order->order_status, ['pending', 'confirmed', 'shipping'], true) && $order->payment_status === 'unpaid';
                        // Đơn COD đã hủy hoặc hoàn về kho: không phát sinh thu tiền
                        $isCodUnpaidTerminal = $isCod && in_array($order->order_status, ['returned', 'cancelled'], true) && $order->payment_status === 'unpaid';

                        // Quy trình đối soát COD 2 bước chặt chẽ
                        $nextPaymentStatuses = ($isCodUnpaidInTransit || $isCodUnpaidTerminal) ? [] : match ($order->payment_status) {
                            'unpaid' => ($isCod ? [] : ['paid', 'failed']),
                            'customer_paid' => ['reconciling'],
                            'reconciling' => ['paid', 'discrepancy'],
                            'discrepancy' => ['reconciling', 'paid', 'failed'],
                            'paid' => ($order->order_status === 'completed' ? [] : ['refunded']),
                            'failed' => ['unpaid', 'customer_paid', 'paid'],
                            default => [],
                        };

                        if ($isCancelled || $isReturned) {
                            $nextOrderStatuses = ($isFailed && $order->order_status === 'shipping') ? ['returned'] : [];
                            if ($order->payment_status !== 'paid') {
                                $nextPaymentStatuses = [];
                            }
                        }

                        $orderCode = $order->payment_code ?: 'PW' . $order->id;
                        $latestTransaction = $order->sepayTransactions->first();
                        $transactionCode = $latestTransaction?->sepay_id;

                        // Cấu hình nhãn và modal xác nhận cho Đơn hàng
                        $orderActionConfig = [
                            'confirmed' => [
                                'label' => 'Xác nhận đơn hàng',
                                'icon' => 'fa-check',
                                'icon_color' => '#16a34a',
                                'confirm_title' => 'Xác nhận đơn hàng',
                                'confirm_msg' => 'Bạn có chắc chắn muốn duyệt và chuẩn bị đóng gói kiện hàng này?',
                                'confirm_icon' => 'fa-clipboard-check',
                                'confirm_type' => 'primary',
                            ],
                            'shipping' => [
                                'label' => ($order->shipping_method_code === 'ghn_express' ? 'Gửi đơn sang GHN (Lấy mã vận đơn)' : 'Bàn giao giao hàng'),
                                'icon' => ($order->shipping_method_code === 'ghn_express' ? 'fa-paper-plane' : 'fa-truck-fast'),
                                'icon_color' => '#2563eb',
                                'confirm_title' => ($order->shipping_method_code === 'ghn_express' ? 'Gửi yêu cầu lấy hàng sang GHN' : 'Bàn giao giao hàng'),
                                'confirm_msg' => ($order->shipping_method_code === 'ghn_express' ? 'Hệ thống sẽ tạo mã vận đơn GHN và chuyển sang bước Chờ bưu tá GHN đến lấy hàng?' : 'Bàn giao kiện hàng cho nhân viên giao hàng của shop?'),
                                'confirm_icon' => ($order->shipping_method_code === 'ghn_express' ? 'fa-paper-plane' : 'fa-truck-fast'),
                                'confirm_type' => 'primary',
                            ],
                            'returned' => [
                                'label' => 'Xác nhận đã nhận lại hàng hoàn',
                                'icon' => 'fa-box-archive',
                                'icon_color' => '#ea580c',
                                'is_return' => true,
                            ],
                            'completed' => [
                                'label' => 'Hoàn thành đơn hàng',
                                'icon' => 'fa-circle-check',
                                'icon_color' => '#16a34a',
                                'confirm_title' => 'Hoàn thành đơn hàng',
                                'confirm_msg' => 'Xác nhận đơn hàng đã giao thành công và khách hàng đã nhận được kiện hàng?',
                                'confirm_icon' => 'fa-circle-check',
                                'confirm_type' => 'success',
                            ],
                            'cancelled' => [
                                'label' => 'Hủy đơn hàng này',
                                'icon' => 'fa-ban',
                                'icon_color' => '#dc2626',
                                'confirm_title' => 'Hủy đơn hàng này',
                                'confirm_msg' => 'Bạn có chắc chắn muốn hủy đơn hàng này? Số lượng tồn kho sẽ được tự động hoàn lại.',
                                'confirm_icon' => 'fa-ban',
                                'confirm_type' => 'danger',
                            ],
                        ];

                        // Cấu hình nhãn và modal xác nhận cho Thanh toán
                        $paymentActionConfig = [
                            'customer_paid' => [
                                'label' => 'Khách đã trả tiền mặt (Shipper đã thu)',
                                'icon' => 'fa-hand-holding-dollar',
                                'icon_color' => '#2563eb',
                                'confirm_title' => 'Khách đã trả tiền mặt',
                                'confirm_msg' => 'Xác nhận Shipper đã thu đủ tiền mặt từ khách hàng cho đơn này?',
                                'confirm_icon' => 'fa-hand-holding-dollar',
                                'confirm_type' => 'primary',
                            ],
                            'reconciling' => [
                                'label' => 'Chuyển sang Đang đối soát',
                                'icon' => 'fa-arrows-rotate',
                                'icon_color' => '#ea580c',
                                'confirm_title' => 'Bắt đầu đối soát',
                                'confirm_msg' => 'Chuyển đơn hàng vào kỳ đối soát bảng kê với đơn vị vận chuyển?',
                                'confirm_icon' => 'fa-arrows-rotate',
                                'confirm_type' => 'warning',
                            ],
                            'paid' => [
                                'label' => ($isCod ? 'Xác nhận: Đã nhận tiền từ ĐVVC (Hoàn tất)' : 'Xác nhận đã nhận chuyển khoản'),
                                'icon' => 'fa-circle-check',
                                'icon_color' => '#16a34a',
                                'confirm_title' => ($isCod ? 'Xác nhận đã nhận tiền từ ĐVVC' : 'Xác nhận đã nhận tiền'),
                                'confirm_msg' => ($isCod ? 'Xác nhận tiền thu hộ (COD) từ đơn vị vận chuyển đã chuyển về tài khoản ngân hàng của Shop và kết thúc toàn bộ quy trình đơn hàng?' : 'Xác nhận khách hàng đã thanh toán chuyển khoản thành công cho đơn hàng này?'),
                                'confirm_icon' => 'fa-circle-check',
                                'confirm_type' => 'success',
                            ],
                            'discrepancy' => [
                                'label' => 'Báo có chênh lệch tiền',
                                'icon' => 'fa-triangle-exclamation',
                                'icon_color' => '#dc2626',
                                'confirm_title' => 'Báo có chênh lệch tiền',
                                'confirm_msg' => 'Đánh dấu đơn hàng có sai lệch tiền thu hộ để tiếp tục khiếu nại ĐVVC?',
                                'confirm_icon' => 'fa-triangle-exclamation',
                                'confirm_type' => 'danger',
                            ],
                            'failed' => [
                                'label' => 'Đánh dấu thanh toán lỗi',
                                'icon' => 'fa-circle-xmark',
                                'icon_color' => '#dc2626',
                                'confirm_title' => 'Đánh dấu thanh toán lỗi',
                                'confirm_msg' => 'Xác nhận giao dịch thanh toán của khách hàng bị thất bại?',
                                'confirm_icon' => 'fa-circle-xmark',
                                'confirm_type' => 'danger',
                            ],
                            'refunded' => [
                                'label' => 'Xác nhận hoàn tiền',
                                'icon' => 'fa-rotate-left',
                                'icon_color' => '#9333ea',
                                'confirm_title' => 'Xác nhận hoàn tiền',
                                'confirm_msg' => 'Xác nhận bạn đã chuyển khoản hoàn tiền lại cho khách hàng thành công?',
                                'confirm_icon' => 'fa-rotate-left',
                                'confirm_type' => 'warning',
                            ],
                        ];
                    @endphp
                    <tr @class(['order-row-cancelled' => $isCancelled])>
                        <td style="text-align: center;">
                            @if(! $isCancelled)
                                <input type="checkbox" class="order-select-checkbox" value="{{ $order->id }}" style="cursor: pointer; width: 16px; height: 16px;">
                            @endif
                        </td>
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
                        <td style="color: var(--text-muted); white-space: nowrap;">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="col-total" style="white-space: nowrap;">{{ number_format((float) $order->total_amount, 0, ',', '.') }}&nbsp;đ</td>
                        <td class="col-status">
                            <div class="quick-status-wrapper">
                                @if($isCodUnpaidTerminal)
                                    <span class="quick-status-trigger badge-payment" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600;" aria-disabled="true" title="Đơn đã hủy hoặc hoàn về kho, không phát sinh thu tiền">
                                        <i class="fa-solid fa-ban" style="font-size: 0.65rem;"></i>
                                        <span>{{ $isReturned ? 'Không thu tiền (Đơn hoàn)' : 'Không thu tiền (Đã hủy)' }}</span>
                                    </span>
                                @elseif($isCodUnpaidInTransit)
                                    <span class="quick-status-trigger badge-payment cod" aria-disabled="true" title="Đơn COD: Tiền do Shipper thu khi giao hàng">
                                        <i class="fa-solid fa-hand-holding-dollar" style="font-size: 0.65rem;"></i>
                                        <span>Thu hộ COD</span>
                                    </span>
                                @else
                                    <span class="quick-status-trigger badge-payment {{ $paymentStatusClasses[$order->payment_status] ?? 'pending' }}" aria-disabled="{{ $nextPaymentStatuses === [] ? 'true' : 'false' }}">
                                        <span>{{ $paymentStatuses[$order->payment_status] ?? $order->payment_status }}</span>
                                        @if($nextPaymentStatuses !== [])
                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                                        @endif
                                    </span>
                                    @if($nextPaymentStatuses !== [])
                                        <div class="quick-status-menu">
                                            @foreach($nextPaymentStatuses as $status)
                                                @php
                                                    $pBtn = $paymentActionConfig[$status] ?? [
                                                        'label' => $paymentStatuses[$status] ?? $status,
                                                        'icon' => 'fa-circle-check',
                                                        'icon_color' => '#16a34a',
                                                        'confirm_title' => 'Cập nhật thanh toán',
                                                        'confirm_msg' => 'Bạn có chắc chắn muốn chuyển sang trạng thái này?',
                                                        'confirm_icon' => 'fa-clipboard-check',
                                                        'confirm_type' => 'primary',
                                                    ];
                                                @endphp
                                                @if($status === 'refunded')
                                                    <button type="button" 
                                                            class="quick-status-item js-open-index-refund-modal"
                                                            data-action-url="{{ route('admin.orders.update-status', $order) }}"
                                                            data-order-id="{{ $order->id }}"
                                                            data-order-code="{{ $orderCode }}"
                                                            data-customer-name="{{ $order->recipient_name }}"
                                                            data-customer-phone="{{ $order->recipient_phone }}"
                                                            data-total-amount="{{ number_format((float) $order->total_amount, 0, ',', '.') }}">
                                                        <i class="fa-solid fa-money-bill-transfer" style="color: #9333ea;"></i>
                                                        <span>{{ $pBtn['label'] }}</span>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            class="quick-status-item js-open-index-confirm-modal"
                                                            data-action-url="{{ route('admin.orders.update-status', $order) }}"
                                                            data-field-name="payment_status"
                                                            data-field-value="{{ $status }}"
                                                            data-order-code="{{ $orderCode }}"
                                                            data-confirm-title="{{ $pBtn['confirm_title'] }}"
                                                            data-confirm-msg="{{ $pBtn['confirm_msg'] }}"
                                                            data-confirm-icon="{{ $pBtn['confirm_icon'] }}"
                                                            data-confirm-type="{{ $pBtn['confirm_type'] }}">
                                                        <i class="fa-solid {{ $pBtn['icon'] }}" style="color: {{ $pBtn['icon_color'] }};"></i>
                                                        <span>{{ $pBtn['label'] }}</span>
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="orders-method">
                                {{ $order->isBankTransfer() ? 'Bank' : 'COD' }}
                            </div>
                        </td>
                        <td class="col-status">
                            <div class="quick-status-wrapper">
                                @if($isReturned)
                                    <span class="quick-status-trigger badge-fulfillment returned" aria-disabled="true">
                                        <i class="fa-solid fa-box-archive" style="font-size: 0.65rem;"></i>
                                        <span>Đã hoàn về kho</span>
                                    </span>
                                    @if($order->returned_at)
                                        <div style="font-size: 0.68rem; color: #9a3412; margin-top: 2px;">{{ $order->returned_at->format('d/m/Y') }}</div>
                                    @endif
                                @elseif($isFailed)
                                    <span class="quick-status-trigger badge-fulfillment failed" aria-disabled="false" title="Bấm để mở xác nhận nhận lại hàng hoàn">
                                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.65rem;"></i>
                                        <span>Giao thất bại</span>
                                        <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                                    </span>
                                    <div class="quick-status-menu">
                                        <button type="button" 
                                                class="quick-status-item js-open-index-return-modal"
                                                data-order-id="{{ $order->id }}"
                                                data-order-code="{{ $orderCode }}"
                                                data-tracking-code="{{ $order->shipment?->tracking_code ?? 'Chưa có' }}"
                                                data-total-quantity="{{ $order->items_count }}"
                                                data-action-url="{{ route('admin.orders.update-status', $order) }}">
                                            <i class="fa-solid fa-box-archive" style="color: #ea580c;"></i>
                                            <span>Xác nhận đã nhận lại hàng hoàn</span>
                                        </button>
                                    </div>
                                    <button type="button" 
                                            class="js-open-index-return-modal"
                                            style="display: block; font-size: 0.68rem; color: #ea580c; font-weight: 600; text-decoration: underline; margin-top: 2px; background: none; border: none; padding: 0; cursor: pointer;"
                                            data-order-id="{{ $order->id }}"
                                            data-order-code="{{ $orderCode }}"
                                            data-tracking-code="{{ $order->shipment?->tracking_code ?? 'Chưa có' }}"
                                            data-total-quantity="{{ $order->items_count }}"
                                            data-action-url="{{ route('admin.orders.update-status', $order) }}"
                                            title="Nhập lý do, tải ảnh biên bản và nhập kho ngay tại đây">
                                        Nhận hoàn hàng ➔
                                    </button>
                                @elseif($isReadyToPick)
                                    <span class="quick-status-trigger badge-fulfillment ready_to_pick" aria-disabled="true">
                                        <i class="fa-solid fa-clock" style="font-size: 0.65rem;"></i>
                                        <span>Chờ lấy hàng</span>
                                    </span>
                                    <div style="font-size: 0.68rem; color: #b45309; margin-top: 2px;">Chờ bưu tá GHN</div>
                                @elseif($isShipping)
                                    <span class="quick-status-trigger badge-fulfillment shipping" aria-disabled="true">
                                        <i class="fa-solid fa-truck-fast" style="font-size: 0.65rem;"></i>
                                        <span>Đang giao hàng</span>
                                    </span>
                                    <div style="font-size: 0.68rem; color: #0d9488; margin-top: 2px;">Vận chuyển GHN</div>
                                @else
                                    <span class="quick-status-trigger badge-fulfillment {{ $orderStatusClasses[$order->order_status] ?? 'pending' }}" aria-disabled="{{ $nextOrderStatuses === [] ? 'true' : 'false' }}">
                                        <span>{{ $orderStatuses[$order->order_status] ?? $order->order_status }}</span>
                                        @if($nextOrderStatuses !== [])
                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                                        @endif
                                    </span>
                                    @if($nextOrderStatuses !== [])
                                        <div class="quick-status-menu">
                                            @foreach($nextOrderStatuses as $status)
                                                @php
                                                    $oBtn = $orderActionConfig[$status] ?? [
                                                        'label' => $orderStatuses[$status] ?? $status,
                                                        'icon' => 'fa-arrow-right',
                                                        'icon_color' => '#2563eb',
                                                        'confirm_title' => 'Cập nhật đơn hàng',
                                                        'confirm_msg' => 'Bạn có chắc chắn muốn chuyển đơn sang trạng thái này?',
                                                        'confirm_icon' => 'fa-clipboard-check',
                                                        'confirm_type' => 'primary',
                                                    ];
                                                @endphp
                                                @if(!empty($oBtn['is_return']))
                                                    <button type="button" 
                                                            class="quick-status-item js-open-index-return-modal"
                                                            data-order-id="{{ $order->id }}"
                                                            data-order-code="{{ $orderCode }}"
                                                            data-tracking-code="{{ $order->shipment?->tracking_code ?? 'Chưa có' }}"
                                                            data-total-quantity="{{ $order->items_count }}"
                                                            data-action-url="{{ route('admin.orders.update-status', $order) }}">
                                                        <i class="fa-solid {{ $oBtn['icon'] }}" style="color: {{ $oBtn['icon_color'] }};"></i>
                                                        <span>{{ $oBtn['label'] }}</span>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            class="quick-status-item js-open-index-confirm-modal"
                                                            data-action-url="{{ route('admin.orders.update-status', $order) }}"
                                                            data-field-name="order_status"
                                                            data-field-value="{{ $status }}"
                                                            data-order-code="{{ $orderCode }}"
                                                            data-confirm-title="{{ $oBtn['confirm_title'] }}"
                                                            data-confirm-msg="{{ $oBtn['confirm_msg'] }}"
                                                            data-confirm-icon="{{ $oBtn['confirm_icon'] }}"
                                                            data-confirm-type="{{ $oBtn['confirm_type'] }}">
                                                        <i class="fa-solid {{ $oBtn['icon'] }}" style="color: {{ $oBtn['icon_color'] }};"></i>
                                                        <span>{{ $oBtn['label'] }}</span>
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
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
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 32px;">
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
        <div>
            {{ $orders->links('admin.layouts.pagination') }}
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
            <input type="hidden" name="shipping_method_id" value="{{ $filters['shipping_method_id'] ?? '' }}">
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
<!-- Form ngầm để gửi xác nhận đổi trạng thái trên index -->
<form id="indexStatusActionForm" method="POST" action="" style="display: none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="" id="indexActionFieldInput" value="">
</form>

<!-- Modern Custom Confirmation Modal cho Trang Danh Sách -->
<div class="custom-confirm-backdrop" id="indexStatusConfirmModal" aria-hidden="true">
    <div class="custom-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="indexConfirmModalTitle">
        <div class="custom-confirm-icon-wrapper primary" id="indexConfirmModalIconWrapper">
            <i class="fa-solid fa-clipboard-check" id="indexConfirmModalIcon"></i>
        </div>
        <h3 class="custom-confirm-title" id="indexConfirmModalTitle">Xác nhận thao tác</h3>
        <p class="custom-confirm-message" id="indexConfirmModalMessage">Bạn có chắc chắn muốn thực hiện hành động này?</p>
        <div class="custom-confirm-badge-code">
            <i class="fa-solid fa-receipt"></i> Đơn hàng: <strong id="indexConfirmModalOrderCode">PW...</strong>
        </div>
        
        <div class="custom-confirm-actions">
            <button type="button" class="btn-confirm-cancel" id="btnIndexConfirmCancel">
                <i class="fa-solid fa-xmark"></i> Hủy bỏ
            </button>
            <button type="button" class="btn-confirm-accept primary" id="btnIndexConfirmAccept">
                <i class="fa-solid fa-check"></i> Xác nhận
            </button>
        </div>
    </div>
</div>

<!-- Modal Biên bản nhận lại hàng hoàn (Kèm lý do & Upload ảnh chứng minh) -->
<div class="custom-confirm-backdrop" id="indexReturnProofModal" aria-hidden="true">
    <div class="custom-confirm-dialog return-modal-dialog" role="dialog" aria-modal="true">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #fff7ed; border: 1px solid #fed7aa; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a;">Biên bản nhận lại hàng hoàn</h3>
                    <span style="font-size: 0.72rem; color: #64748b;">Đơn hàng #<strong id="indexReturnModalOrderCode">PW...</strong> · Vận đơn: <strong id="indexReturnModalTrackingCode">---</strong></span>
                </div>
            </div>
            <button type="button" id="btnIndexCloseReturnModal" style="background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 4px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" id="indexReturnProofForm">
            @csrf
            @method('PATCH')
            <input type="hidden" name="order_status" value="returned">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    1. Lý do hoàn hàng <span style="color: #dc2626;">*</span>
                </label>
                <!-- Quick Tags -->
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                    <button type="button" class="btn-quick-reason" data-reason="Khách không nghe máy / Thuê bao (Giao thất bại 3 lần)">📞 Khách không nghe máy (3 lần)</button>
                    <button type="button" class="btn-quick-reason" data-reason="Khách từ chối nhận hàng (Bom hàng / Đổi ý)">🚫 Khách từ chối nhận (Bom hàng)</button>
                    <button type="button" class="btn-quick-reason" data-reason="Sai thông tin địa chỉ / số điện thoại người nhận">📍 Sai địa chỉ / SĐT</button>
                    <button type="button" class="btn-quick-reason" data-reason="Kiện hàng bị móp méo / vỡ hỏng trong lúc vận chuyển">📦 Hàng móp méo / vỡ hỏng</button>
                </div>
                <textarea name="return_reason" id="indexReturnReasonInput" rows="3" required
                          placeholder="Nhập chi tiết lý do bưu tá GHN chuyển hoàn kiện hàng về kho..."
                          style="width: 100%; padding: 10px 12px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 8px; resize: vertical; box-sizing: border-box;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                    2. Hình ảnh chứng minh nhận kiện hàng <span style="color: #dc2626;">*</span>
                </label>
                <p style="margin: 0 0 8px 0; font-size: 0.7rem; color: #64748b;">
                    Chụp ảnh kiện hàng khi nhận lại từ bưu tá (rõ tem phiếu GHN, lý do shipper dán trên gói hàng hoặc tình trạng vỏ hộp).
                </p>

                <input type="file" name="return_proof_image" id="indexReturnProofImageInput" accept="image/*" required style="display: none;">
                
                <div id="indexReturnUploadZone" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 18px 12px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s ease;">
                    <i class="fa-solid fa-camera" style="font-size: 1.8rem; color: #ea580c; margin-bottom: 6px;"></i>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">Bấm để tải ảnh lên hoặc kéo thả ảnh vào đây</div>
                    <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 3px;">Hỗ trợ: JPG, PNG, WEBP (Tối đa 5MB)</div>
                </div>

                <div id="indexReturnPreviewBox" style="display: none; margin-top: 8px; position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; text-align: center; padding: 6px;">
                    <img id="indexReturnPreviewImg" src="" alt="Proof Preview" style="max-height: 180px; max-width: 100%; border-radius: 6px; display: inline-block;">
                    <button type="button" id="btnIndexRemoveProofImage" title="Xóa ảnh" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.65); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 18px; padding: 10px 12px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; font-size: 0.74rem; color: #b45309; line-height: 1.4;">
                <i class="fa-solid fa-boxes-stacked" style="margin-right: 4px;"></i>
                Khi xác nhận, hệ thống sẽ <strong>tự động nhập lại <span id="indexReturnModalQuantity">0</span> sản phẩm</strong> vào kho và lưu bằng chứng kiểm kê.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                <button type="button" class="btn-confirm-cancel" id="btnIndexCancelReturnModal" style="padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #475569; cursor: pointer;">
                    Hủy bỏ
                </button>
                <button type="submit" id="btnIndexSubmitReturnProof" style="padding: 8px 18px; font-size: 0.82rem; font-weight: 700; border-radius: 8px; border: none; background: #ea580c; color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(234, 88, 12, 0.3);">
                    <i class="fa-solid fa-box-archive"></i> Xác nhận nhận hàng & Nhập kho
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL XÁC NHẬN HOÀN TIỀN CHO KHÁCH HÀNG (TRANG DANH SÁCH) --}}
<div class="custom-confirm-backdrop" id="indexRefundProofModal" aria-hidden="true">
    <div class="custom-confirm-dialog return-modal-dialog" role="dialog" aria-modal="true" style="max-width: 540px; text-align: left;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <div style="display: flex; gap: 12px; align-items: center;">
                <div style="width: 42px; height: 42px; border-radius: 10px; background: #faf5ff; border: 1px solid #e9d5ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a;">Xác nhận hoàn tiền cho khách</h3>
                    <span style="font-size: 0.72rem; color: #64748b;">Đơn hàng #<strong id="indexRefundModalOrderCode">PW...</strong> · Khách hàng: <strong id="indexRefundModalCustomerName">...</strong></span>
                </div>
            </div>
            <button type="button" id="btnIndexCloseRefundModal" style="background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 4px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" id="indexRefundProofForm">
            @csrf
            @method('PATCH')
            <input type="hidden" name="payment_status" value="refunded">

            <!-- Card Tóm tắt số tiền hoàn & SĐT khách -->
            <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.72rem; color: #6b7280; display: block;">Tổng tiền cần hoàn:</span>
                    <strong style="font-size: 1.15rem; color: #7e22ce;"><span id="indexRefundModalAmount">0</span> đ</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.72rem; color: #6b7280; display: block;">SĐT liên hệ khách:</span>
                    <strong style="font-size: 0.85rem; color: #1e293b;" id="indexRefundModalPhone">...</strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Ngân hàng nhận <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="refund_bank_name" id="indexRefundBankNameInput" required
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
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Số tài khoản nhận <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="refund_account_number" id="indexRefundAccountNumberInput" required
                           placeholder="Ví dụ: 0901234567"
                           style="width: 100%; padding: 8px 10px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Tên chủ tài khoản <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="refund_account_name" id="indexRefundAccountNameInput" required
                           placeholder="Ví dụ: NGUYEN VAN A"
                           style="width: 100%; padding: 8px 10px; font-size: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; text-transform: uppercase;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Số tiền hoàn (VNĐ) <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="refund_amount" id="indexRefundAmountInput" required
                           style="width: 100%; padding: 8px 10px; font-size: 0.8rem; font-weight: 700; color: #7e22ce; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                    Ảnh biên lai / Bill chuyển khoản hoàn tiền <span style="color: #dc2626;">*</span>
                </label>
                <p style="margin: 0 0 6px 0; font-size: 0.69rem; color: #64748b;">
                    Tải lên ảnh chụp màn hình chuyển khoản thành công từ app ngân hàng hoặc ủy nhiệm chi.
                </p>

                <input type="file" name="refund_proof_image" id="indexRefundProofImageInput" accept="image/*" required style="display: none;">
                
                <div id="indexRefundUploadZone" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 16px 12px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s ease;">
                    <i class="fa-solid fa-file-invoice-dollar" style="font-size: 1.8rem; color: #9333ea; margin-bottom: 6px;"></i>
                    <div style="font-size: 0.78rem; font-weight: 600; color: #1e293b;">Bấm để tải ảnh bill chuyển khoản hoặc kéo thả vào đây</div>
                    <div style="font-size: 0.68rem; color: #94a3b8; margin-top: 2px;">Định dạng: JPG, PNG, WEBP (Tối đa 5MB)</div>
                </div>

                <div id="indexRefundPreviewBox" style="display: none; margin-top: 8px; position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; text-align: center; padding: 6px;">
                    <img id="indexRefundPreviewImg" src="" alt="Refund Proof Preview" style="max-height: 180px; max-width: 100%; border-radius: 6px; display: inline-block;">
                    <button type="button" id="btnIndexRemoveRefundProofImage" title="Xóa ảnh" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.65); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px;">
                    Ghi chú hoàn tiền <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="refund_note" id="indexRefundNoteInput" rows="2" required
                          placeholder="Bắt buộc nhập ghi chú hoàn tiền (Ví dụ: Đã liên hệ khách và chuyển khoản hoàn tiền đơn bom thành công)..."
                          style="width: 100%; padding: 8px 10px; font-size: 0.78rem; border: 1px solid #cbd5e1; border-radius: 6px; resize: vertical; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                <button type="button" id="btnIndexCancelRefundModal" style="padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #475569; cursor: pointer;">
                    Hủy bỏ
                </button>
                <button type="submit" id="btnIndexSubmitRefundProof" style="padding: 8px 18px; font-size: 0.82rem; font-weight: 700; border-radius: 8px; border: none; background: #9333ea; color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(147, 51, 234, 0.3);">
                    <i class="fa-solid fa-check"></i> Xác nhận & Hoàn tất hoàn tiền
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function triggerBulkReconcile(targetStatus) {
        const checked = document.querySelectorAll('.order-select-checkbox:checked');
        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một đơn hàng để đối soát.');
            return;
        }

        const label = targetStatus === 'paid' ? 'Shop đã nhận tiền' : (targetStatus === 'reconciling' ? 'Đang đối soát' : targetStatus);
        if (!confirm(`Bạn có chắc muốn chuyển ${checked.length} đơn hàng đã chọn sang trạng thái "${label}"?`)) {
            return;
        }

        const form = document.getElementById('bulk-reconcile-form');
        const container = document.getElementById('bulk-order-ids-container');
        document.getElementById('bulk-target-status').value = targetStatus;
        container.innerHTML = '';

        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        form.submit();
    }

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

        const selectAll = document.getElementById('select-all-orders');
        const checkboxes = document.querySelectorAll('.order-select-checkbox');
        const bulkBar = document.getElementById('bulk-reconcile-bar');
        const countSpan = document.getElementById('selected-orders-count');
        const clearBtn = document.getElementById('btn-clear-selection');

        function updateBulkBar() {
            const checkedCount = document.querySelectorAll('.order-select-checkbox:checked').length;
            if (countSpan) countSpan.textContent = checkedCount;
            if (bulkBar) {
                if (checkedCount > 0) {
                    bulkBar.classList.add('show');
                } else {
                    bulkBar.classList.remove('show');
                }
            }
            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
            }
        }

        selectAll?.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkBar();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });

        clearBtn?.addEventListener('click', function () {
            if (selectAll) selectAll.checked = false;
            checkboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });

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

        // ==========================================
        // INDEX CONFIRM MODAL LOGIC
        // ==========================================
        const confirmModal = document.getElementById('indexStatusConfirmModal');
        const confirmIconWrapper = document.getElementById('indexConfirmModalIconWrapper');
        const confirmIcon = document.getElementById('indexConfirmModalIcon');
        const confirmTitle = document.getElementById('indexConfirmModalTitle');
        const confirmMessage = document.getElementById('indexConfirmModalMessage');
        const confirmOrderCode = document.getElementById('indexConfirmModalOrderCode');
        const btnConfirmCancel = document.getElementById('btnIndexConfirmCancel');
        const btnConfirmAccept = document.getElementById('btnIndexConfirmAccept');
        const indexActionForm = document.getElementById('indexStatusActionForm');
        const indexActionFieldInput = document.getElementById('indexActionFieldInput');

        let currentConfirmData = null;

        function openIndexConfirmModal(data) {
            currentConfirmData = data;
            confirmTitle.textContent = data.title || 'Xác nhận thao tác';
            confirmMessage.textContent = data.msg || 'Bạn có chắc chắn muốn thực hiện hành động này?';
            confirmOrderCode.textContent = data.orderCode || '';

            // Reset classes
            confirmIconWrapper.className = 'custom-confirm-icon-wrapper ' + (data.type || 'primary');
            btnConfirmAccept.className = 'btn-confirm-accept ' + (data.type || 'primary');
            confirmIcon.className = 'fa-solid ' + (data.icon || 'fa-circle-question');

            btnConfirmAccept.disabled = false;
            btnConfirmAccept.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận';

            confirmModal.classList.add('active');
            confirmModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeIndexConfirmModal() {
            if (!confirmModal) return;
            confirmModal.classList.remove('active');
            confirmModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            currentConfirmData = null;
        }

        document.querySelectorAll('.js-open-index-confirm-modal').forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Close dropdown menu
                document.querySelectorAll('.quick-status-menu').forEach((menu) => menu.classList.remove('show'));

                openIndexConfirmModal({
                    actionUrl: this.dataset.actionUrl,
                    fieldName: this.dataset.fieldName,
                    fieldValue: this.dataset.fieldValue,
                    orderCode: this.dataset.orderCode,
                    title: this.dataset.confirmTitle,
                    msg: this.dataset.confirmMsg,
                    icon: this.dataset.confirmIcon,
                    type: this.dataset.confirmType
                });
            });
        });

        if (btnConfirmCancel) btnConfirmCancel.addEventListener('click', closeIndexConfirmModal);
        if (confirmModal) {
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) closeIndexConfirmModal();
            });
        }

        if (btnConfirmAccept) {
            btnConfirmAccept.addEventListener('click', function() {
                if (!currentConfirmData || !indexActionForm) {
                    closeIndexConfirmModal();
                    return;
                }

                indexActionForm.action = currentConfirmData.actionUrl;
                indexActionFieldInput.name = currentConfirmData.fieldName;
                indexActionFieldInput.value = currentConfirmData.fieldValue;

                btnConfirmAccept.disabled = true;
                btnConfirmAccept.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

                indexActionForm.submit();
            });
        }

        // ==========================================
        // INDEX RETURN PROOF MODAL LOGIC
        // ==========================================
        const returnModal = document.getElementById('indexReturnProofModal');
        const btnCloseReturn = document.getElementById('btnIndexCloseReturnModal');
        const btnCancelReturn = document.getElementById('btnIndexCancelReturnModal');
        const returnForm = document.getElementById('indexReturnProofForm');
        const returnReasonInput = document.getElementById('indexReturnReasonInput');
        const returnImageInput = document.getElementById('indexReturnProofImageInput');
        const returnUploadZone = document.getElementById('indexReturnUploadZone');
        const returnPreviewBox = document.getElementById('indexReturnPreviewBox');
        const returnPreviewImg = document.getElementById('indexReturnPreviewImg');
        const btnRemoveProofImage = document.getElementById('btnIndexRemoveProofImage');
        const btnSubmitReturnProof = document.getElementById('btnIndexSubmitReturnProof');
        const returnOrderCodeElem = document.getElementById('indexReturnModalOrderCode');
        const returnTrackingCodeElem = document.getElementById('indexReturnModalTrackingCode');
        const returnQuantityElem = document.getElementById('indexReturnModalQuantity');

        function openIndexReturnModal(data) {
            if (!returnModal) return;
            returnForm.action = data.actionUrl;
            returnOrderCodeElem.textContent = data.orderCode;
            returnTrackingCodeElem.textContent = data.trackingCode || 'Chưa có';
            returnQuantityElem.textContent = data.totalQuantity || '0';

            // Reset inputs
            returnReasonInput.value = '';
            returnImageInput.value = '';
            if (returnPreviewBox) returnPreviewBox.style.display = 'none';
            if (returnUploadZone) returnUploadZone.style.display = 'block';
            if (returnPreviewImg) returnPreviewImg.src = '';
            if (btnSubmitReturnProof) {
                btnSubmitReturnProof.disabled = false;
                btnSubmitReturnProof.innerHTML = '<i class="fa-solid fa-box-archive"></i> Xác nhận nhận hàng & Nhập kho';
            }

            returnModal.classList.add('active');
            returnModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeIndexReturnModal() {
            if (!returnModal) return;
            returnModal.classList.remove('active');
            returnModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.js-open-index-return-modal').forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                document.querySelectorAll('.quick-status-menu').forEach((menu) => menu.classList.remove('show'));

                openIndexReturnModal({
                    orderId: this.dataset.orderId,
                    orderCode: this.dataset.orderCode,
                    trackingCode: this.dataset.trackingCode,
                    totalQuantity: this.dataset.totalQuantity,
                    actionUrl: this.dataset.actionUrl,
                });
            });
        });

        if (btnCloseReturn) btnCloseReturn.addEventListener('click', closeIndexReturnModal);
        if (btnCancelReturn) btnCancelReturn.addEventListener('click', closeIndexReturnModal);
        if (returnModal) {
            returnModal.addEventListener('click', function(e) {
                if (e.target === returnModal) closeIndexReturnModal();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (confirmModal && confirmModal.classList.contains('active')) closeIndexConfirmModal();
                if (returnModal && returnModal.classList.contains('active')) closeIndexReturnModal();
            }
        });

        // Quick tags for return reason
        document.querySelectorAll('.btn-quick-reason').forEach((btn) => {
            btn.addEventListener('click', function() {
                if (returnReasonInput) {
                    returnReasonInput.value = this.dataset.reason;
                    returnReasonInput.focus();
                }
            });
        });

        // Upload zone interactions
        if (returnUploadZone && returnImageInput) {
            returnUploadZone.addEventListener('click', function() {
                returnImageInput.click();
            });

            returnUploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = '#ea580c';
                this.style.background = '#fff7ed';
            });

            returnUploadZone.addEventListener('dragleave', function() {
                this.style.borderColor = '#cbd5e1';
                this.style.background = '#f8fafc';
            });

            returnUploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderColor = '#cbd5e1';
                this.style.background = '#f8fafc';
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    returnImageInput.files = e.dataTransfer.files;
                    handleIndexImageSelected(e.dataTransfer.files[0]);
                }
            });

            returnImageInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    handleIndexImageSelected(this.files[0]);
                }
            });
        }

        function handleIndexImageSelected(file) {
            if (!file || !file.type.startsWith('image/')) {
                alert('Vui lòng chọn file hình ảnh (JPG, PNG, WEBP).');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Dung lượng ảnh tối đa là 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                if (returnPreviewImg) returnPreviewImg.src = e.target.result;
                if (returnPreviewBox) returnPreviewBox.style.display = 'block';
                if (returnUploadZone) returnUploadZone.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        if (btnRemoveProofImage) {
            btnRemoveProofImage.addEventListener('click', function() {
                if (returnImageInput) returnImageInput.value = '';
                if (returnPreviewImg) returnPreviewImg.src = '';
                if (returnPreviewBox) returnPreviewBox.style.display = 'none';
                if (returnUploadZone) returnUploadZone.style.display = 'block';
            });
        }

        if (returnForm) {
            returnForm.addEventListener('submit', function(e) {
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
        // INDEX REFUND PROOF MODAL LOGIC
        // ==========================================
        const indexRefundModal = document.getElementById('indexRefundProofModal');
        const btnIndexCloseRefund = document.getElementById('btnIndexCloseRefundModal');
        const btnIndexCancelRefund = document.getElementById('btnIndexCancelRefundModal');
        const indexRefundForm = document.getElementById('indexRefundProofForm');
        const indexRefundBankSelect = document.getElementById('indexRefundBankNameInput');
        const indexRefundAccountInput = document.getElementById('indexRefundAccountNumberInput');
        const indexRefundNameInput = document.getElementById('indexRefundAccountNameInput');
        const indexRefundAmountInput = document.getElementById('indexRefundAmountInput');
        const indexRefundImageInput = document.getElementById('indexRefundProofImageInput');
        const indexRefundUploadZone = document.getElementById('indexRefundUploadZone');
        const indexRefundPreviewBox = document.getElementById('indexRefundPreviewBox');
        const indexRefundPreviewImg = document.getElementById('indexRefundPreviewImg');
        const btnIndexRemoveRefundProofImage = document.getElementById('btnIndexRemoveRefundProofImage');
        const btnIndexSubmitRefundProof = document.getElementById('btnIndexSubmitRefundProof');
        const indexRefundOrderCodeElem = document.getElementById('indexRefundModalOrderCode');
        const indexRefundCustomerNameElem = document.getElementById('indexRefundModalCustomerName');
        const indexRefundAmountElem = document.getElementById('indexRefundModalAmount');
        const indexRefundPhoneElem = document.getElementById('indexRefundModalPhone');

        function openIndexRefundModal(data) {
            if (!indexRefundModal) return;
            indexRefundForm.action = data.actionUrl;
            if (indexRefundOrderCodeElem) indexRefundOrderCodeElem.textContent = data.orderCode;
            if (indexRefundCustomerNameElem) indexRefundCustomerNameElem.textContent = data.customerName;
            if (indexRefundAmountElem) indexRefundAmountElem.textContent = data.totalAmount;
            if (indexRefundPhoneElem) indexRefundPhoneElem.textContent = data.customerPhone || 'Chưa có SĐT';

            // Prefill inputs
            if (indexRefundBankSelect) indexRefundBankSelect.value = '';
            if (indexRefundAccountInput) indexRefundAccountInput.value = '';
            if (indexRefundNameInput) indexRefundNameInput.value = (data.customerName || '').toUpperCase();
            if (indexRefundAmountInput) indexRefundAmountInput.value = data.totalAmount || '0';
            if (indexRefundImageInput) indexRefundImageInput.value = '';
            const noteInput = document.getElementById('indexRefundNoteInput');
            if (noteInput) noteInput.value = '';

            if (indexRefundPreviewBox) indexRefundPreviewBox.style.display = 'none';
            if (indexRefundUploadZone) indexRefundUploadZone.style.display = 'block';
            if (indexRefundPreviewImg) indexRefundPreviewImg.src = '';
            if (btnIndexSubmitRefundProof) {
                btnIndexSubmitRefundProof.disabled = false;
                btnIndexSubmitRefundProof.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận & Hoàn tất hoàn tiền';
            }

            indexRefundModal.classList.add('active');
            indexRefundModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeIndexRefundModal() {
            if (!indexRefundModal) return;
            indexRefundModal.classList.remove('active');
            indexRefundModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.js-open-index-refund-modal').forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                document.querySelectorAll('.quick-status-menu').forEach((menu) => menu.classList.remove('show'));

                openIndexRefundModal({
                    orderId: this.dataset.orderId,
                    orderCode: this.dataset.orderCode,
                    customerName: this.dataset.customerName,
                    customerPhone: this.dataset.customerPhone,
                    totalAmount: this.dataset.totalAmount,
                    actionUrl: this.dataset.actionUrl,
                });
            });
        });

        if (btnIndexCloseRefund) btnIndexCloseRefund.addEventListener('click', closeIndexRefundModal);
        if (btnIndexCancelRefund) btnIndexCancelRefund.addEventListener('click', closeIndexRefundModal);
        if (indexRefundModal) {
            indexRefundModal.addEventListener('click', function(e) {
                if (e.target === indexRefundModal) closeIndexRefundModal();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (indexRefundModal && indexRefundModal.classList.contains('active')) closeIndexRefundModal();
            }
        });

        // Upload zone interactions for refund bill
        if (indexRefundUploadZone && indexRefundImageInput) {
            indexRefundUploadZone.addEventListener('click', function() {
                indexRefundImageInput.click();
            });

            indexRefundUploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = '#9333ea';
                this.style.background = '#faf5ff';
            });

            indexRefundUploadZone.addEventListener('dragleave', function() {
                this.style.borderColor = '#cbd5e1';
                this.style.background = '#f8fafc';
            });

            indexRefundUploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderColor = '#cbd5e1';
                this.style.background = '#f8fafc';
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    indexRefundImageInput.files = e.dataTransfer.files;
                    handleIndexRefundImageSelected(e.dataTransfer.files[0]);
                }
            });

            indexRefundImageInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    handleIndexRefundImageSelected(this.files[0]);
                }
            });
        }

        function handleIndexRefundImageSelected(file) {
            if (!file || !file.type.startsWith('image/')) {
                alert('Vui lòng chọn file hình ảnh (JPG, PNG, WEBP).');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Dung lượng ảnh tối đa là 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                if (indexRefundPreviewImg) indexRefundPreviewImg.src = e.target.result;
                if (indexRefundPreviewBox) indexRefundPreviewBox.style.display = 'block';
                if (indexRefundUploadZone) indexRefundUploadZone.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        if (btnIndexRemoveRefundProofImage) {
            btnIndexRemoveRefundProofImage.addEventListener('click', function() {
                if (indexRefundImageInput) indexRefundImageInput.value = '';
                if (indexRefundPreviewImg) indexRefundPreviewImg.src = '';
                if (indexRefundPreviewBox) indexRefundPreviewBox.style.display = 'none';
                if (indexRefundUploadZone) indexRefundUploadZone.style.display = 'block';
            });
        }

        if (indexRefundForm) {
            indexRefundForm.addEventListener('submit', function(e) {
                if (!indexRefundBankSelect || !indexRefundBankSelect.value) {
                    e.preventDefault();
                    alert('Vui lòng chọn ngân hàng nhận tiền của khách.');
                    if (indexRefundBankSelect) indexRefundBankSelect.focus();
                    return;
                }

                if (!indexRefundAccountInput || !indexRefundAccountInput.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng nhập số tài khoản ngân hàng của khách.');
                    if (indexRefundAccountInput) indexRefundAccountInput.focus();
                    return;
                }

                if (!indexRefundNameInput || !indexRefundNameInput.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng nhập tên chủ tài khoản người nhận (bắt buộc).');
                    if (indexRefundNameInput) indexRefundNameInput.focus();
                    return;
                }

                if (!indexRefundAmountInput || !indexRefundAmountInput.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng nhập số tiền hoàn (bắt buộc).');
                    if (indexRefundAmountInput) indexRefundAmountInput.focus();
                    return;
                }

                if (!indexRefundImageInput || !indexRefundImageInput.files || indexRefundImageInput.files.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng tải lên ảnh chụp bill / biên lai chuyển khoản hoàn tiền (bắt buộc).');
                    return;
                }

                const indexRefundNoteInput = document.getElementById('indexRefundNoteInput');
                if (!indexRefundNoteInput || !indexRefundNoteInput.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng nhập ghi chú hoàn tiền (bắt buộc).');
                    if (indexRefundNoteInput) indexRefundNoteInput.focus();
                    return;
                }

                if (btnIndexSubmitRefundProof) {
                    btnIndexSubmitRefundProof.disabled = true;
                    btnIndexSubmitRefundProof.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu thông tin & cập nhật hoàn tiền...';
                }
            });
        }
    });
</script>
@endsection
