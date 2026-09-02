"use client";

import { useState } from "react";
import Link from "next/link";
import { formatPrice, resolveProductImage } from "@/lib/format";

const normalizeOrder = (order) => {
  if (!order) return null;
  
  // Trường hợp dữ liệu đầy đủ từ API getOrder
  if (order.recipient && typeof order.recipient === "object") {
    return {
      id: order.id,
      code: order.code || `PW${String(order.id).padStart(6, "0")}`,
      payment_code: order.payment_code,
      status: order.status,
      payment_status: order.payment_status,
      created_at: order.created_at,
      recipientName: order.recipient.name,
      recipientPhone: order.recipient.phone,
      recipientAddress: order.recipient.address,
      shippingMethod: order.shipping?.method || "Giao hàng tiêu chuẩn",
      shippingFee: order.shipping?.fee ?? 0,
      shippingDiscount: order.shipping?.discount ?? 0,
      shippingPromotion: order.shipping?.promotion?.name || order.shipping?.promotion?.code || null,
      paymentMethod: order.payment?.method || "Chuyển khoản ngân hàng",
      subtotal: order.payment?.subtotal ?? 0,
      discountAmount: order.payment?.discount ?? 0,
      totalAmount: order.payment?.total ?? 0,
      note: order.note,
      items: (order.items || []).map(item => ({
        id: item.id,
        name: item.name,
        variant: item.variant,
        quantity: item.quantity,
        price: item.price,
        slug: item.slug,
        image: item.image,
      }))
    };
  }

  // Trường hợp dữ liệu sơ bộ trả về trực tiếp sau khi POST đặt hàng
  const subtotal = (order.items || []).reduce((sum, item) => sum + (item.price * item.quantity), 0);
  return {
    id: order.id,
    code: order.code || `PW${String(order.id).padStart(6, "0")}`,
    payment_code: order.payment_code,
    status: order.order_status || "pending",
    payment_status: order.payment_status || "unpaid",
    created_at: order.created_at,
    recipientName: order.recipient_name,
    recipientPhone: order.recipient_phone,
    recipientAddress: order.recipient_address,
    shippingMethod: order.shipping_method_name || "Giao hàng tiêu chuẩn",
    shippingFee: Number(order.shipping_fee ?? 0),
    shippingDiscount: Number(order.shipping_discount ?? 0),
    shippingPromotion: null,
    discountAmount: Number(order.discount_amount ?? 0),
    totalAmount: Number(order.total_amount ?? 0),
    subtotal: subtotal,
    paymentMethod: order.is_bank ? "Chuyển khoản qua ngân hàng" : "Thanh toán khi nhận hàng (COD)",
    note: order.note,
    items: (order.items || []).map(item => ({
      id: item.id,
      name: item.product_name || item.name,
      variant: item.product_variant_name || item.variant,
      quantity: item.quantity,
      price: item.price,
      slug: null,
      image: null,
    }))
  };
};

export default function CheckoutSuccessView({ order }) {
  const [copied, setCopied] = useState(false);
  const normalized = normalizeOrder(order);

  if (!normalized) return null;

  const handleCopyCode = async () => {
    if (typeof navigator !== "undefined" && navigator.clipboard) {
      try {
        await navigator.clipboard.writeText(normalized.payment_code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
      } catch (err) {
        console.error("Failed to copy code", err);
      }
    }
  };

  const isPaid = ["paid", "customer_paid", "reconciling"].includes(normalized.payment_status);

  return (
    <div className="co-success-container">
      <div className="co-success-card">
        {/* Header với Icon check và Tiêu đề */}
        <div className="co-success-header">
          <div className="co-success-icon-wrapper">
            <div className="co-success-icon-circle">
              <svg className="co-success-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <div className="co-success-ripple"></div>
          </div>

          <h1 className="co-success-title">
            {isPaid ? "Thanh Toán Thành Công!" : "Đặt Hàng Thành Công!"}
          </h1>
          <p className="co-success-subtitle">
            {isPaid 
              ? "Cảm ơn bạn! Đơn hàng đã được thanh toán hoàn tất. Hệ thống đang tiến hành đóng gói và giao hàng trong thời gian sớm nhất."
              : "Cảm ơn bạn! Đơn hàng của bạn đã được ghi nhận thành công và đang được chuẩn bị để giao đến bạn."}
          </p>

          <div className="co-success-code-box">
            <span className="co-success-code-label">Mã đơn hàng của bạn</span>
            <div className="co-success-code-value-wrap">
              <strong className="co-success-code-value">{normalized.payment_code}</strong>
              <button 
                type="button" 
                className={`co-success-copy-btn ${copied ? "copied" : ""}`} 
                onClick={handleCopyCode}
                title="Sao chép mã đơn hàng"
              >
                {copied ? (
                  <>
                    <svg className="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12" /></svg>
                    <span>Đã sao chép</span>
                  </>
                ) : (
                  <>
                    <svg className="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" /></svg>
                    <span>Sao chép</span>
                  </>
                )}
              </button>
            </div>
          </div>
        </div>

        {/* Nội dung chi tiết đơn hàng */}
        <div className="co-success-content-grid">
          {/* Cột trái: Thông tin nhận hàng và Vận chuyển */}
          <div className="co-success-info-section">
            <h2 className="co-success-section-title">
              <svg className="section-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" /></svg>
              Thông tin giao nhận
            </h2>
            
            <div className="co-success-info-list">
              <div className="co-success-info-item">
                <span className="info-label">Người nhận hàng</span>
                <span className="info-value highlight-text">{normalized.recipientName}</span>
              </div>
              <div className="co-success-info-item">
                <span className="info-label">Số điện thoại</span>
                <span className="info-value">{normalized.recipientPhone}</span>
              </div>
              <div className="co-success-info-item">
                <span className="info-label">Địa chỉ giao hàng</span>
                <span className="info-value">{normalized.recipientAddress}</span>
              </div>
              <div className="co-success-info-item">
                <span className="info-label">Đơn vị vận chuyển</span>
                <span className="info-value">{normalized.shippingMethod}</span>
              </div>
              <div className="co-success-info-item">
                <span className="info-label">Thời gian giao dự kiến</span>
                <span className="info-value highlight-green">2 - 3 ngày làm việc</span>
              </div>
              <div className="co-success-info-item">
                <span className="info-label">Hình thức thanh toán</span>
                <div className="payment-status-badge-wrap">
                  <span className="info-value">{normalized.paymentMethod}</span>
                  <span className={`payment-badge ${isPaid ? "paid" : "unpaid"}`}>
                    {isPaid ? "Đã thanh toán" : "Thanh toán khi nhận hàng (COD)"}
                  </span>
                </div>
              </div>
            </div>
            
            <div className="co-success-trust-banner">
              <div className="trust-item">
                <span className="trust-icon">🛡️</span>
                <span>Chính hãng 100%</span>
              </div>
              <div className="trust-item">
                <span className="trust-icon">📦</span>
                <span>Đóng gói cẩn thận</span>
              </div>
              <div className="trust-item">
                <span className="trust-icon">🔁</span>
                <span>Đổi trả 7 ngày</span>
              </div>
            </div>
          </div>

          {/* Cột phải: Danh sách sản phẩm mua và Hóa đơn */}
          <div className="co-success-summary-section">
            <h2 className="co-success-section-title">
              <svg className="section-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" /><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" /></svg>
              Chi tiết sản phẩm
            </h2>
            
            <div className="co-success-products">
              {normalized.items.map((item, idx) => (
                <div className="co-success-product-item" key={item.id || idx}>
                  <div className="product-image-container">
                    {item.image ? (
                      <img 
                        src={resolveProductImage(item.image)} 
                        alt={item.name} 
                        className="product-img" 
                           />
                    ) : (
                      <div className="product-img-fallback">🐾</div>
                    )}
                    <span className="product-qty-badge">{item.quantity}</span>
                  </div>
                  <div className="product-details">
                    <span className="product-name" title={item.name}>
                      {item.slug ? (
                        <Link href={`/shop/${item.slug}`} className="product-link">
                          {item.name}
                        </Link>
                      ) : (
                        item.name
                      )}
                    </span>
                    {item.variant && <span className="product-variant">Phân loại: {item.variant}</span>}
                  </div>
                  <div className="product-price">
                    {formatPrice(item.price * item.quantity)}
                  </div>
                </div>
              ))}
            </div>

            <div className="co-success-pricing-summary">
              <div className="pricing-row">
                <span>Tạm tính</span>
                <span>{formatPrice(normalized.subtotal)}</span>
              </div>
              <div className="pricing-row">
                <span>Phí vận chuyển</span>
                <span>{formatPrice(normalized.shippingFee)}</span>
              </div>
              {normalized.shippingDiscount > 0 && (
                <div className="pricing-row discount">
                  <span>Ưu đãi vận chuyển{normalized.shippingPromotion ? ` (${normalized.shippingPromotion})` : ""}</span>
                  <span>-{formatPrice(normalized.shippingDiscount)}</span>
                </div>
              )}
              {normalized.discountAmount > 0 && (
                <div className="pricing-row discount">
                  <span>Giảm giá voucher</span>
                  <span>-{formatPrice(normalized.discountAmount)}</span>
                </div>
              )}
              <div className="pricing-row total">
                <span>Tổng tiền thanh toán</span>
                <span className="total-price">{formatPrice(normalized.totalAmount)}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Các nút điều hướng */}
        <div className="co-success-footer-actions">
          <Link href={`/account/orders/${normalized.id}`} className="btn-solid-success">
            Xem chi tiết đơn hàng
          </Link>
          <Link href="/shop" className="btn-outline-success">
            Tiếp tục mua sắm
          </Link>
        </div>
      </div>
    </div>
  );
}
