"use client";

import Link from "next/link";
import { useMemo, useState, useSyncExternalStore } from "react";
import { formatPrice, resolveImage } from "@/lib/format";
import {
  getCartSnapshot,
  getServerCartSnapshot,
  parseCart,
  updateQuantity,
  removeFromCart,
  clearCart,
  onCartChange,
} from "@/lib/cart";

const SHIPPING_METHODS = [
  { id: "fast", label: "Giao hàng nhanh", desc: "Dự kiến nhận hàng trong 1-2 ngày", fee: 30000 },
  { id: "saving", label: "Giao hàng tiết kiệm", desc: "Dự kiến nhận hàng trong 3-5 ngày", fee: 20000 },
];

const PROVINCES = ["TP. Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Cần Thơ", "Hải Phòng"];

export default function CheckoutView() {
  const [shippingId, setShippingId] = useState("fast");
  const [payment, setPayment] = useState("cod");
  const [placed, setPlaced] = useState(false);
  const [form, setForm] = useState({ name: "", phone: "", province: "", address: "", note: "" });

  // Đăng ký giỏ hàng (localStorage) qua external store để tránh hydration mismatch.
  const raw = useSyncExternalStore(onCartChange, getCartSnapshot, getServerCartSnapshot);
  const items = useMemo(() => parseCart(raw), [raw]);

  const subtotal = items.reduce((sum, line) => sum + line.price * line.quantity, 0);
  const shipping = SHIPPING_METHODS.find((m) => m.id === shippingId)?.fee ?? 0;
  const total = subtotal + (items.length ? shipping : 0);

  const updateForm = (field) => (event) => setForm({ ...form, [field]: event.target.value });

  const handlePlaceOrder = () => {
    if (!form.name || !form.phone || !form.address) {
      alert("Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ giao hàng.");
      return;
    }
    clearCart();
    setPlaced(true);
  };

  if (placed) {
    return (
      <div className="cart-empty">
        <p>🎉 Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại PetWorld.</p>
        <Link href="/shop" className="cart-empty-btn">Tiếp tục mua sắm</Link>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="cart-empty">
        <p>Giỏ hàng của bạn đang trống, chưa thể thanh toán.</p>
        <Link href="/shop" className="cart-empty-btn">Tiếp tục mua sắm</Link>
      </div>
    );
  }

  return (
    <>
      {/* Bảng giỏ hàng */}
      <div className="co-cart-table">
        <div className="co-cart-head">
          <span>Sản phẩm</span>
          <span>Giá</span>
          <span>Số lượng</span>
          <span>Tổng</span>
        </div>
        {items.map((line) => (
          <div className="co-cart-row" key={line.key}>
            <div className="co-cart-product">
              <button type="button" className="co-cart-remove" onClick={() => removeFromCart(line.key)} aria-label="Xoá">
                ×
              </button>
              <img src={resolveImage(line.image)} alt={line.name} />
              <div>
                <Link href={`/shop/${line.slug}`} className="co-cart-name">{line.name}</Link>
                {line.variantName && <span className="co-cart-variant">Phân loại: {line.variantName}</span>}
              </div>
            </div>
            <span className="co-cart-price">{formatPrice(line.price)}</span>
            <div className="cart-qty">
              <button type="button" onClick={() => updateQuantity(line.key, line.quantity - 1)} aria-label="Giảm">−</button>
              <span>{line.quantity}</span>
              <button type="button" onClick={() => updateQuantity(line.key, line.quantity + 1)} aria-label="Tăng">+</button>
            </div>
            <span className="co-cart-total">{formatPrice(line.price * line.quantity)}</span>
          </div>
        ))}
        <div className="co-cart-actions">
          <Link href="/shop" className="co-btn-outline">Tiếp tục xem sản phẩm</Link>
          <Link href="/cart" className="co-btn-solid">Cập nhật giỏ hàng</Link>
        </div>
      </div>

      <h2 className="co-heading">Thanh toán đơn hàng</h2>

      <div className="co-layout">
        {/* Form giao hàng + vận chuyển */}
        <div className="co-main">
          <section className="co-card">
            <h3 className="co-card-title">🚚 Thông tin giao hàng</h3>
            <div className="co-field">
              <label>Họ và tên</label>
              <input type="text" placeholder="Nhập đầy đủ họ tên" value={form.name} onChange={updateForm("name")} />
            </div>
            <div className="co-field-row">
              <div className="co-field">
                <label>Số điện thoại</label>
                <input type="tel" placeholder="Ví dụ: 0912345678" value={form.phone} onChange={updateForm("phone")} />
              </div>
              <div className="co-field">
                <label>Tỉnh / Thành phố</label>
                <select value={form.province} onChange={updateForm("province")}>
                  <option value="">Chọn tỉnh / thành phố</option>
                  {PROVINCES.map((province) => (
                    <option key={province} value={province}>{province}</option>
                  ))}
                </select>
              </div>
            </div>
            <div className="co-field">
              <label>Địa chỉ chi tiết</label>
              <input type="text" placeholder="Số nhà, tên đường, phường/xã..." value={form.address} onChange={updateForm("address")} />
            </div>
            <div className="co-field">
              <label>Ghi chú (Tùy chọn)</label>
              <textarea rows={3} placeholder="Lưu ý cho người giao hàng..." value={form.note} onChange={updateForm("note")} />
            </div>
          </section>

          <section className="co-card">
            <h3 className="co-card-title">🚀 Phương thức vận chuyển</h3>
            {SHIPPING_METHODS.map((method) => (
              <label key={method.id} className={`co-ship-option ${shippingId === method.id ? "active" : ""}`}>
                <input
                  type="radio"
                  name="shipping"
                  checked={shippingId === method.id}
                  onChange={() => setShippingId(method.id)}
                />
                <div>
                  <strong>{method.label}</strong>
                  <span>{method.desc}</span>
                </div>
                <span className="co-ship-fee">{formatPrice(method.fee)}</span>
              </label>
            ))}
          </section>
        </div>

        {/* Đơn hàng của bạn */}
        <aside className="co-summary">
          <h3 className="co-summary-title">Đơn hàng của bạn</h3>
          <div className="co-summary-head">
            <span>Sản phẩm</span>
            <span>Tổng</span>
          </div>
          <div className="co-summary-items">
            {items.map((line) => (
              <div className="co-summary-item" key={line.key}>
                <span className="co-summary-item-name">
                  {line.name} <em>× {line.quantity}</em>
                </span>
                <span>{formatPrice(line.price * line.quantity)}</span>
              </div>
            ))}
          </div>
          <div className="co-summary-row">
            <span>Tổng phụ</span>
            <span>{formatPrice(subtotal)}</span>
          </div>
          <div className="co-summary-row">
            <span>Phí vận chuyển</span>
            <span>{formatPrice(shipping)}</span>
          </div>
          <div className="co-summary-total">
            <span>Tổng</span>
            <span>{formatPrice(total)}</span>
          </div>

          <div className="co-payment">
            <label className={`co-pay-option ${payment === "bank" ? "active" : ""}`}>
              <input type="radio" name="payment" checked={payment === "bank"} onChange={() => setPayment("bank")} />
              <span>Chuyển khoản ngân hàng</span>
            </label>
            <label className={`co-pay-option ${payment === "cod" ? "active" : ""}`}>
              <input type="radio" name="payment" checked={payment === "cod"} onChange={() => setPayment("cod")} />
              <span>Thanh toán khi nhận hàng</span>
            </label>
          </div>

          {payment === "bank" && (
            <p className="co-pay-note">
              Thực hiện thanh toán vào ngay tài khoản ngân hàng của chúng tôi. Vui lòng sử dụng Mã đơn hàng của bạn
              trong phần Nội dung thanh toán. Đơn hàng sẽ được giao sau khi tiền đã chuyển.
            </p>
          )}

          <button type="button" className="co-place-btn" onClick={handlePlaceOrder}>Đặt hàng</button>
        </aside>
      </div>
    </>
  );
}
