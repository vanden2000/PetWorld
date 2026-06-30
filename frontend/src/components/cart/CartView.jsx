"use client";

import Link from "next/link";
import { useMemo, useState, useSyncExternalStore } from "react";
import { formatPrice, resolveImage } from "@/lib/format";
import { ROUTES } from "@/lib/routes";
import { toastSuccess, toastError, toastInfo } from "@/lib/toast";
import {
  getCartSnapshot,
  getServerCartSnapshot,
  parseCart,
  updateQuantity,
  removeFromCart,
  onCartChange,
} from "@/lib/cart";

const SHIPPING_FEE = 30000;

export default function CartView() {
  // Đăng ký giỏ hàng (localStorage) qua external store để tránh hydration mismatch.
  const raw = useSyncExternalStore(onCartChange, getCartSnapshot, getServerCartSnapshot);
  const items = useMemo(() => parseCart(raw), [raw]);

  const [coupon, setCoupon] = useState("");
  const [discount, setDiscount] = useState(0);

  const handleRemove = (line) => {
    removeFromCart(line.key);
    toastSuccess(`Đã xoá "${line.name}" khỏi giỏ hàng`);
  };

  const handleApplyCoupon = () => {
    const code = coupon.trim();
    if (!code) {
      toastError("Vui lòng nhập mã giảm giá.");
      return;
    }
    // Áp dụng voucher 50k client-side cho đúng mockup
    setDiscount(50000);
    toastSuccess("Đã áp dụng mã giảm giá thành công! Giảm 50.000đ.");
  };

  const subtotal = items.reduce((sum, line) => sum + line.price * line.quantity, 0);
  const shipping = items.length ? SHIPPING_FEE : 0;
  const total = Math.max(0, subtotal + shipping - discount);

  if (items.length === 0) {
    return (
      <div className="cart-empty">
        <p>Giỏ hàng của bạn đang trống.</p>
        <Link href={ROUTES.shop} className="cart-empty-btn">Tiếp tục mua sắm</Link>
      </div>
    );
  }

  return (
    <div className="cart-layout">
      <div className="cart-items">
        {items.map((line) => (
          <div className="cart-item" key={line.key}>
            <Link href={`/shop/${line.slug}`} className="cart-item-img">
              <img src={resolveImage(line.image)} alt={line.name} />
            </Link>
            <div className="cart-item-info">
              <Link href={`/shop/${line.slug}`} className="cart-item-name">
                {line.name}
              </Link>
              {line.variantName && <span className="cart-item-variant">Biến thể: {line.variantName}</span>}
              <div className="cart-qty">
                <button type="button" onClick={() => updateQuantity(line.key, line.quantity - 1)} aria-label="Giảm">
                  −
                </button>
                <span>{line.quantity}</span>
                <button type="button" onClick={() => updateQuantity(line.key, line.quantity + 1)} aria-label="Tăng">
                  +
                </button>
              </div>
            </div>
            <div className="cart-item-right">
              <button type="button" className="cart-remove" onClick={() => handleRemove(line)} aria-label="Xoá">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="3 6 5 6 21 6" />
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  <line x1="10" y1="11" x2="10" y2="17" />
                  <line x1="14" y1="11" x2="14" y2="17" />
                </svg>
              </button>
              <div className="cart-item-prices">
                {line.oldPrice && (
                  <span className="cart-item-price-old">{formatPrice(line.oldPrice * line.quantity)}</span>
                )}
                <span className="cart-item-price">{formatPrice(line.price * line.quantity)}</span>
              </div>
            </div>
          </div>
        ))}

        <Link href={ROUTES.shop} className="cart-continue">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: "6px" }}>
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
          </svg>
          Tiếp tục mua sắm
        </Link>
      </div>

      <aside className="cart-summary">
        <h2 className="cart-summary-title">Tóm tắt đơn hàng</h2>
        <div className="cart-summary-row">
          <span>Tạm tính</span>
          <span>{formatPrice(subtotal)}</span>
        </div>
        <div className="cart-summary-row">
          <span>Phí vận chuyển</span>
          <span>{formatPrice(shipping)}</span>
        </div>
        {discount > 0 && (
          <div className="cart-summary-row discount">
            <span>Giảm giá</span>
            <span>-{formatPrice(discount)}</span>
          </div>
        )}
        <div className="cart-summary-total">
          <span>Tổng cộng</span>
          <span>{formatPrice(total)}</span>
        </div>

        <div className="cart-coupon">
          <label htmlFor="cart-coupon-input">Mã giảm giá</label>
          <div className="cart-coupon-row">
            <input
              id="cart-coupon-input"
              type="text"
              placeholder="Nhập mã..."
              value={coupon}
              onChange={(event) => setCoupon(event.target.value)}
            />
            <button type="button" onClick={handleApplyCoupon}>Áp dụng</button>
          </div>
        </div>

        <Link href={ROUTES.checkout} className="cart-checkout-btn">
          Tiến hành thanh toán
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginLeft: "8px", display: "inline-block", verticalAlign: "middle" }}>
            <rect x="2" y="5" width="20" height="14" rx="2" />
            <line x1="2" y1="10" x2="22" y2="10" />
          </svg>
        </Link>
        <p className="cart-secure">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: "6px", display: "inline-block", verticalAlign: "middle" }}>
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            <polyline points="9 11 11 13 15 9" />
          </svg>
          Thanh toán an toàn 100%
        </p>
      </aside>
    </div>
  );
}
