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
    // Kiểm tra mã cần backend (chưa có API voucher) — báo cho người dùng biết.
    toastInfo("Tính năng mã giảm giá cần kết nối máy chủ. Vui lòng thử lại sau.");
  };

  const subtotal = items.reduce((sum, line) => sum + line.price * line.quantity, 0);
  const shipping = items.length ? SHIPPING_FEE : 0;
  const total = subtotal + shipping;

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
                  <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                </svg>
              </button>
              <span className="cart-item-price">{formatPrice(line.price * line.quantity)}</span>
            </div>
          </div>
        ))}

        <Link href={ROUTES.shop} className="cart-continue">← Tiếp tục mua sắm</Link>
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

        <Link href={ROUTES.checkout} className="cart-checkout-btn">Tiến hành thanh toán</Link>
        <p className="cart-secure">🔒 Thanh toán an toàn 100%</p>
      </aside>
    </div>
  );
}
