@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn hàng ' . $id)

@section('styles')
    <!-- CSS riêng cho trang này nếu cần -->
@endsection

@section('content')
<div class="dashboard-header" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 16px;">
        <a href="{{ route('admin.orders') }}" class="btn-outline-back">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Quay lại</span>
        </a>
        <span style="color: var(--border-color); font-size: 1.5rem; font-weight: 300;">|</span>
        <h1 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">
            Đơn hàng #{{ $id ?? 'PW-94021' }}
        </h1>
    </div>
    <div class="header-actions">
        <button class="btn-outline-print">
            <i class="fa-solid fa-print"></i>
            <span>IN HÓA ĐƠN</span>
        </button>
    </div>
</div>

<div class="order-details-grid">
    <!-- Cột trái (Thông tin vận chuyển & Sản phẩm) -->
    <div class="details-left">
        <!-- Khách hàng và Địa chỉ nhận hàng -->
        <div class="address-details-row">
            <!-- Thông tin khách hàng Card -->
            <div class="order-details-card">
                <h3 class="order-details-card-title">
                    <i class="fa-solid fa-circle-user"></i>
                    <span>Thông tin khách hàng</span>
                </h3>
                
                <h4 class="info-label">Họ và tên</h4>
                <p class="info-value info-value-large">Jonathan H. Wick</p>
                
                <h4 class="info-label">Email</h4>
                <p class="info-value">
                    <a href="mailto:j.wick@continental.com" class="info-value-link">j.wick@continental.com</a>
                </p>
                
                <h4 class="info-label">Số điện thoại</h4>
                <p class="info-value">+1 (555) 012-3456</p>
            </div>
            
            <!-- Địa chỉ giao nhận Card -->
            <div class="order-details-card">
                <h3 class="order-details-card-title">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Địa chỉ giao hàng</span>
                </h3>
                
                <h4 class="info-label">Địa chỉ nhận hàng</h4>
                <p class="info-value" style="font-weight: 500;">
                    101 Beaver Street, Suite 4B<br>
                    Manhattan, NY 10005<br>
                    United States
                </p>
                
                <h4 class="info-label">Phương thức vận chuyển</h4>
                <p class="info-value" style="font-weight: 700;">
                    <i class="fa-solid fa-bolt" style="color: var(--primary); margin-right: 4px;"></i>
                    Hỏa tốc qua đêm (Priority Overnight)
                </p>
            </div>
        </div>
        
        <!-- Sản phẩm đã đặt Card -->
        <div class="order-details-card">
            <div class="order-details-card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-basket-shopping" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">Sản phẩm đã đặt</span>
                </div>
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); background-color: var(--bg-color); padding: 4px 10px; border-radius: 4px;">
                    Tổng cộng 4 sản phẩm
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
                        <tr>
                            <td style="padding-left: 0;">
                                <div class="product-cell-detail">
                                    <img src="https://images.unsplash.com/photo-1544568100-847a948585b9?auto=format&fit=crop&w=120&q=80" alt="Harness" class="product-cell-image">
                                    <div class="product-cell-text">
                                        <span class="product-cell-title">Elite Leather Comfort Harness</span>
                                        <span class="product-cell-meta">Kích thước: Lớn | Màu sắc: Xanh lá thợ săn</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="sku-text">PW-HRN-001</span></td>
                            <td style="text-align: center; font-weight: 600;">1</td>
                            <td style="text-align: right;">$89.00</td>
                            <td style="padding-right: 0; text-align: right; font-weight: 700;">$89.00</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 0;">
                                <div class="product-cell-detail">
                                    <img src="https://images.unsplash.com/photo-1568640342990-9b7d5b887a2e?auto=format&fit=crop&w=120&q=80" alt="Kibble" class="product-cell-image">
                                    <div class="product-cell-text">
                                        <span class="product-cell-title">Wild Salmon Grain-Free Kibble</span>
                                        <span class="product-cell-meta">Trọng lượng: 12kg</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="sku-text">PW-FOD-942</span></td>
                            <td style="text-align: center; font-weight: 600;">2</td>
                            <td style="text-align: right;">$64.50</td>
                            <td style="padding-right: 0; text-align: right; font-weight: 700;">$129.00</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 0;">
                                <div class="product-cell-detail">
                                    <img src="https://images.unsplash.com/photo-1576201836106-db1758fd1c97?auto=format&fit=crop&w=120&q=80" alt="Teaser" class="product-cell-image">
                                    <div class="product-cell-text">
                                        <span class="product-cell-title">SmartPaws Interactive Teaser</span>
                                        <span class="product-cell-meta">Phiên bản: 2024 V2</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="sku-text">PW-TOY-881</span></td>
                            <td style="text-align: center; font-weight: 600;">1</td>
                            <td style="text-align: right;">$45.00</td>
                            <td style="padding-right: 0; text-align: right; font-weight: 700;">$45.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cột phải -->
    <div class="details-right-sidebar">
        <!-- Tóm tắt thanh toán Card -->
        <div class="order-details-card">
            <h3 class="order-details-card-title">
                <i class="fa-solid fa-receipt"></i>
                <span>Tóm tắt thanh toán</span>
            </h3>
            
            <div class="summary-row">
                <span>Tạm tính</span>
                <span style="font-weight: 600; color: var(--text-main);">$263.00</span>
            </div>
            
            <div class="summary-row">
                <span>Phí vận chuyển (Hỏa tốc)</span>
                <span style="font-weight: 600; color: var(--text-main);">$15.00</span>
            </div>
            
            <div class="summary-row">
                <span>Thuế (VAT 8%)</span>
                <span style="font-weight: 600; color: var(--text-main);">$21.04</span>
            </div>
            
            <div class="summary-row total-row">
                <span>Tổng tiền</span>
                <span style="color: #0d9488; font-size: 1.15rem;">$299.04</span>
            </div>
            
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
                <h4 class="info-label" style="margin-bottom: 12px;">Phương thức thanh toán</h4>
                <div style="display: flex; align-items: center; gap: 12px; background-color: var(--bg-color); padding: 12px; border-radius: 8px;">
                    <i class="fa-brands fa-cc-visa" style="font-size: 1.75rem; color: #1e3a8a;"></i>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Visa liên kết kết thúc bằng •••• 4242</span>
                        <span style="font-size: 0.7rem; font-weight: 800; color: #10b981; margin-top: 2px;">ĐÃ XÁC THỰC (AUTHORIZED)</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lịch sử hành trình Card -->
        <div class="order-details-card">
            <h3 class="order-details-card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Lịch sử hành trình</span>
            </h3>
            
            <ul class="timeline-list">
                <li class="timeline-item">
                    <span class="timeline-icon-dot completed">
                        <i class="fa-solid fa-check"></i>
                    </span>
                    <div class="timeline-item-title">Đặt đơn hàng thành công</div>
                    <div class="timeline-item-time">24 Tháng 10, 2023 - 09:14 AM</div>
                </li>
                
                <li class="timeline-item">
                    <span class="timeline-icon-dot completed">
                        <i class="fa-solid fa-check"></i>
                    </span>
                    <div class="timeline-item-title">Thanh toán được xác nhận</div>
                    <div class="timeline-item-time">24 Tháng 10, 2023 - 09:16 AM</div>
                    <div class="timeline-item-meta" style="color: var(--text-muted); font-size: 0.72rem;">
                        Mã giao dịch: <span style="font-family: monospace; font-weight: 600;">TXN_8810294</span>
                    </div>
                </li>
                
                <li class="timeline-item">
                    <span class="timeline-icon-dot active">
                        <i class="fa-solid fa-truck" style="font-size: 0.65rem;"></i>
                    </span>
                    <div class="timeline-item-title">Đang vận chuyển</div>
                    <div class="timeline-item-time">25 Tháng 10, 2023 - 02:45 PM</div>
                    
                    <div class="timeline-tracking-box">
                        <span style="color: var(--text-muted); font-weight: 600; font-size: 0.72rem;">MÃ VẬN ĐƠN</span>
                        <a href="#" class="timeline-tracking-link">
                            <span>FEDEX-7712039401</span>
                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.65rem;"></i>
                        </a>
                    </div>
                </li>
                
                <li class="timeline-item">
                    <span class="timeline-icon-dot pending">
                        <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i>
                    </span>
                    <div class="timeline-item-title" style="color: var(--text-muted);">Đang giao hàng</div>
                    <div class="timeline-item-time" style="color: var(--text-muted);">Dự kiến: 27 Tháng 10, 2023</div>
                </li>
            </ul>
        </div>
        
        <!-- Ghi chú nội bộ Card -->
        <div class="order-details-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span class="info-label" style="margin: 0; font-size: 0.72rem; font-weight: 700;">GHI CHÚ NỘI BỘ</span>
                <a href="#" style="background: none; border: none; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-decoration: none; transition: var(--transition);">
                    Chỉnh sửa
                </a>
            </div>
            
            <div class="note-box">
                "Khách hàng yêu cầu giao trước cửa nhà. Hãy gọi khi đến nơi do trong khuôn viên có chú chó lớn giống Great Dane tên Atlas."
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('statusDropdownBtn');
        const menu = document.getElementById('statusDropdownMenu');
        
        if (btn && menu) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('show');
            });
            
            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
            
            const items = menu.querySelectorAll('.status-dropdown-item');
            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const status = this.getAttribute('data-status');
                    const text = this.textContent.trim();
                    alert('Đã cập nhật đơn hàng thành trạng thái: ' + text);
                    menu.classList.remove('show');
                });
            });
        }
    });
</script>
@endsection
