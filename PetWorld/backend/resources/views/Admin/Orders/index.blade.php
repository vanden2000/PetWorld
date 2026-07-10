@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('styles')
    <!-- CSS riêng nếu cần -->
@endsection

@section('content')
<div class="dashboard-header">
    <div class="header-title-block">
        <h1>Quản lý đơn hàng</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Theo dõi, thực hiện và quản lý giao dịch của khách hàng trên tất cả các kênh.</p>
    </div>
    <div class="header-actions">
        <button class="btn-dark-slate">
            <i class="fa-solid fa-download"></i>
            <span>Xuất dữ liệu</span>
        </button>
    </div>
</div>

<!-- Filters Panel -->
<div class="filters-card">
    <div class="filter-col">
        <label class="filter-label">Khoảng thời gian</label>
        <div class="filter-input-wrapper">
            <i class="fa-regular fa-calendar filter-input-icon"></i>
            <input type="text" class="filter-input" placeholder="Chọn khoảng thời gian..." value="">
        </div>
    </div>
    <div class="filter-col">
        <label class="filter-label">Trạng thái thanh toán</label>
        <select class="filter-select">
            <option>Tất cả trạng thái</option>
            <option>Đã thanh toán</option>
            <option>Chờ thanh toán</option>
            <option>Đã hoàn tiền</option>
        </select>
    </div>
    <div class="filter-col">
        <label class="filter-label">Trạng thái giao hàng</label>
        <select class="filter-select">
            <option>Tất cả trạng thái</option>
            <option>Đã giao hàng</option>
            <option>Đang xử lý</option>
            <option>Đang giao hàng</option>
            <option>Đã hủy</option>
        </select>
    </div>
    <div class="filter-col">
        <button class="btn-clear-filters">Xóa bộ lọc</button>
    </div>
</div>

<!-- Orders Table Card -->
<div class="table-card">
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Tên khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán (Sửa nhanh)</th>
                    <th>Giao hàng (Sửa nhanh)</th>
                    <th style="text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row 1 -->
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', 'PW-9021') }}" class="col-order-link">#PW-9021</a>
                    </td>
                    <td class="col-customer">Eleanor Shellstrop</td>
                    <td style="color: var(--text-muted);">Oct 24, 2023</td>
                    <td class="col-total">$124.50</td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-payment paid" data-type="payment" data-status="paid">
                                <span>Đã thanh toán</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="paid" data-text="Đã thanh toán" data-class="paid">Đã thanh toán</a>
                                <a class="quick-status-item" data-status="pending" data-text="Chờ thanh toán" data-class="pending">Chờ thanh toán</a>
                                <a class="quick-status-item" data-status="refunded" data-text="Đã hoàn tiền" data-class="refunded">Đã hoàn tiền</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-fulfillment delivered" data-type="fulfillment" data-status="delivered">
                                <span>Đã giao hàng</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="pending" data-text="Chờ xác nhận" data-class="pending">Chờ xác nhận</a>
                                <a class="quick-status-item" data-status="processing" data-text="Đang xử lý" data-class="processing">Đang xử lý</a>
                                <a class="quick-status-item" data-status="shipping" data-text="Đang giao hàng" data-class="shipping">Đang giao hàng</a>
                                <a class="quick-status-item" data-status="delivered" data-text="Đã giao hàng" data-class="delivered">Đã giao hàng</a>
                                <a class="quick-status-item" data-status="cancelled" data-text="Đã hủy" data-class="cancelled">Đã hủy</a>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('admin.orders.show', 'PW-9021') }}" class="action-view-details">
                            <span>Xem chi tiết</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </td>
                </tr>
                
                <!-- Row 2 -->
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', 'PW-9020') }}" class="col-order-link">#PW-9020</a>
                    </td>
                    <td class="col-customer">Chidi Anagonye</td>
                    <td style="color: var(--text-muted);">Oct 24, 2023</td>
                    <td class="col-total">$45.00</td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-payment pending" data-type="payment" data-status="pending">
                                <span>Chờ thanh toán</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="paid" data-text="Đã thanh toán" data-class="paid">Đã thanh toán</a>
                                <a class="quick-status-item" data-status="pending" data-text="Chờ thanh toán" data-class="pending">Chờ thanh toán</a>
                                <a class="quick-status-item" data-status="refunded" data-text="Đã hoàn tiền" data-class="refunded">Đã hoàn tiền</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-fulfillment processing" data-type="fulfillment" data-status="processing">
                                <span>Đang xử lý</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="pending" data-text="Chờ xác nhận" data-class="pending">Chờ xác nhận</a>
                                <a class="quick-status-item" data-status="processing" data-text="Đang xử lý" data-class="processing">Đang xử lý</a>
                                <a class="quick-status-item" data-status="shipping" data-text="Đang giao hàng" data-class="shipping">Đang giao hàng</a>
                                <a class="quick-status-item" data-status="delivered" data-text="Đã giao hàng" data-class="delivered">Đã giao hàng</a>
                                <a class="quick-status-item" data-status="cancelled" data-text="Đã hủy" data-class="cancelled">Đã hủy</a>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('admin.orders.show', 'PW-9020') }}" class="action-view-details">
                            <span>Xem chi tiết</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </td>
                </tr>

                <!-- Row 3 -->
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', 'PW-9019') }}" class="col-order-link">#PW-9019</a>
                    </td>
                    <td class="col-customer">Tahani Al-Jamil</td>
                    <td style="color: var(--text-muted);">Oct 23, 2023</td>
                    <td class="col-total">$890.20</td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-payment paid" data-type="payment" data-status="paid">
                                <span>Đã thanh toán</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="paid" data-text="Đã thanh toán" data-class="paid">Đã thanh toán</a>
                                <a class="quick-status-item" data-status="pending" data-text="Chờ thanh toán" data-class="pending">Chờ thanh toán</a>
                                <a class="quick-status-item" data-status="refunded" data-text="Đã hoàn tiền" data-class="refunded">Đã hoàn tiền</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-fulfillment shipping" data-type="fulfillment" data-status="shipping">
                                <span>Đang giao hàng</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="pending" data-text="Chờ xác nhận" data-class="pending">Chờ xác nhận</a>
                                <a class="quick-status-item" data-status="processing" data-text="Đang xử lý" data-class="processing">Đang xử lý</a>
                                <a class="quick-status-item" data-status="shipping" data-text="Đang giao hàng" data-class="shipping">Đang giao hàng</a>
                                <a class="quick-status-item" data-status="delivered" data-text="Đã giao hàng" data-class="delivered">Đã giao hàng</a>
                                <a class="quick-status-item" data-status="cancelled" data-text="Đã hủy" data-class="cancelled">Đã hủy</a>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('admin.orders.show', 'PW-9019') }}" class="action-view-details">
                            <span>Xem chi tiết</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </td>
                </tr>

                <!-- Row 4 -->
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', 'PW-9018') }}" class="col-order-link">#PW-9018</a>
                    </td>
                    <td class="col-customer">Jason Mendoza</td>
                    <td style="color: var(--text-muted);">Oct 23, 2023</td>
                    <td class="col-total">$12.99</td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-payment refunded" data-type="payment" data-status="refunded">
                                <span>Đã hoàn tiền</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="paid" data-text="Đã thanh toán" data-class="paid">Đã thanh toán</a>
                                <a class="quick-status-item" data-status="pending" data-text="Chờ thanh toán" data-class="pending">Chờ thanh toán</a>
                                <a class="quick-status-item" data-status="refunded" data-text="Đã hoàn tiền" data-class="refunded">Đã hoàn tiền</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-fulfillment cancelled" data-type="fulfillment" data-status="cancelled">
                                <span>Đã hủy</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="pending" data-text="Chờ xác nhận" data-class="pending">Chờ xác nhận</a>
                                <a class="quick-status-item" data-status="processing" data-text="Đang xử lý" data-class="processing">Đang xử lý</a>
                                <a class="quick-status-item" data-status="shipping" data-text="Đang giao hàng" data-class="shipping">Đang giao hàng</a>
                                <a class="quick-status-item" data-status="delivered" data-text="Đã giao hàng" data-class="delivered">Đã giao hàng</a>
                                <a class="quick-status-item" data-status="cancelled" data-text="Đã hủy" data-class="cancelled">Đã hủy</a>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('admin.orders.show', 'PW-9018') }}" class="action-view-details">
                            <span>Xem chi tiết</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </td>
                </tr>

                <!-- Row 5 -->
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', 'PW-9017') }}" class="col-order-link">#PW-9017</a>
                    </td>
                    <td class="col-customer">Michael Realman</td>
                    <td style="color: var(--text-muted);">Oct 22, 2023</td>
                    <td class="col-total">$315.00</td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-payment paid" data-type="payment" data-status="paid">
                                <span>Đã thanh toán</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="paid" data-text="Đã thanh toán" data-class="paid">Đã thanh toán</a>
                                <a class="quick-status-item" data-status="pending" data-text="Chờ thanh toán" data-class="pending">Chờ thanh toán</a>
                                <a class="quick-status-item" data-status="refunded" data-text="Đã hoàn tiền" data-class="refunded">Đã hoàn tiền</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="quick-status-wrapper">
                            <span class="quick-status-trigger badge-fulfillment delivered" data-type="fulfillment" data-status="delivered">
                                <span>Đã giao hàng</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px;"></i>
                            </span>
                            <div class="quick-status-menu">
                                <a class="quick-status-item" data-status="pending" data-text="Chờ xác nhận" data-class="pending">Chờ xác nhận</a>
                                <a class="quick-status-item" data-status="processing" data-text="Đang xử lý" data-class="processing">Đang xử lý</a>
                                <a class="quick-status-item" data-status="shipping" data-text="Đang giao hàng" data-class="shipping">Đang giao hàng</a>
                                <a class="quick-status-item" data-status="delivered" data-text="Đã giao hàng" data-class="delivered">Đã giao hàng</a>
                                <a class="quick-status-item" data-status="cancelled" data-text="Đã hủy" data-class="cancelled">Đã hủy</a>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('admin.orders.show', 'PW-9017') }}" class="action-view-details">
                            <span>Xem chi tiết</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="pagination-container">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Xem <strong style="color: var(--text-main); font-weight: 600;">5</strong> trên <strong style="color: var(--text-main); font-weight: 600;">1,248</strong> đơn hàng
        </div>
        <div class="pagination-buttons">
            <button class="pagination-btn" title="Trang đầu">
                <i class="fa-solid fa-angles-left"></i>
            </button>
            <button class="pagination-btn" title="Trang trước">
                <i class="fa-solid fa-angle-left"></i>
            </button>
            <span class="pagination-info">1 / 250</span>
            <button class="pagination-btn" title="Trang sau">
                <i class="fa-solid fa-angle-right"></i>
            </button>
            <button class="pagination-btn" title="Trang cuối">
                <i class="fa-solid fa-angles-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const triggers = document.querySelectorAll('.quick-status-trigger');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Đóng tất cả các menu đang mở khác
                document.querySelectorAll('.quick-status-menu').forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.remove('show');
                    }
                });
                
                const menu = this.nextElementSibling;
                if (menu) {
                    menu.classList.toggle('show');
                }
            });
        });
        
        // Đóng dropdown khi nhấp ra ngoài
        document.addEventListener('click', function() {
            document.querySelectorAll('.quick-status-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });
        
        // Thay đổi trạng thái động
        const items = document.querySelectorAll('.quick-status-item');
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const targetMenu = this.closest('.quick-status-menu');
                const trigger = targetMenu.previousElementSibling;
                const newStatusText = this.getAttribute('data-text');
                const newClass = this.getAttribute('data-class');
                const prevStatus = trigger.getAttribute('data-status');
                const type = trigger.getAttribute('data-type') || 'fulfillment';
                
                // Cập nhật text và attribute
                trigger.querySelector('span').textContent = newStatusText;
                trigger.setAttribute('data-status', this.getAttribute('data-status'));
                
                // Thay đổi class màu sắc badge tương ứng
                const badgeBaseClass = type === 'payment' ? 'badge-payment' : 'badge-fulfillment';
                trigger.className = 'quick-status-trigger ' + badgeBaseClass + ' ' + newClass;
                
                // Đóng menu
                targetMenu.classList.remove('show');
                
                console.log('Thay đổi trạng thái của ' + type + ' từ ' + prevStatus + ' sang ' + this.getAttribute('data-status'));
            });
        });
    });
</script>
@endsection