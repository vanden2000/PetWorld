"use client";

import Link from "next/link";
import { useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { formatPrice, resolveImage } from "@/lib/format";
import {
  getUserSnapshot,
  getServerUserSnapshot,
  parseUser,
  onAuthChange,
} from "@/lib/auth";
import {
  getCartSnapshot,
  getServerCartSnapshot,
  parseCart,
  clearCart,
  onCartChange,
} from "@/lib/cart";
import {
  getCheckoutOptions,
  getAddresses,
  createAddress,
  createOrder,
  getOrder,
  buildSepayQrUrl,
} from "@/lib/checkout";
import AddressLocationFields from "@/components/auth/AddressLocationFields";

// Mã QR hết hạn sau 15 phút, sau đó khách bấm tạo lại.
const QR_TTL_SECONDS = 15 * 60;

// Thông tin ngân hàng hiển thị (chuyển khoản thủ công) — cấu hình qua env.
const BANK_INFO = {
  name: process.env.NEXT_PUBLIC_BANK_NAME,
  account: process.env.NEXT_PUBLIC_BANK_ACCOUNT_NUMBER,
  holder: process.env.NEXT_PUBLIC_BANK_ACCOUNT_HOLDER,
};

const EMPTY_ADDRESS = {
  recipient_name: "",
  recipient_phone: "",
  address_line: "",
  ward: "",
  district: "",
  province: "",
  is_default: false,
};

export default function CheckoutView() {
  // Phải đăng nhập mới đặt được hàng (đơn gắn user_id + sổ địa chỉ).
  const userRaw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(userRaw), [userRaw]);

  const raw = useSyncExternalStore(onCartChange, getCartSnapshot, getServerCartSnapshot);
  const items = useMemo(() => parseCart(raw), [raw]);

  const [options, setOptions] = useState({ shipping_methods: [], payment_methods: [] });
  const [shippingMethodId, setShippingMethodId] = useState(null);
  const [paymentMethodId, setPaymentMethodId] = useState(null);

  const [addresses, setAddresses] = useState([]);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [addressForm, setAddressForm] = useState(EMPTY_ADDRESS);
  const [savingAddress, setSavingAddress] = useState(false);

  const [submitting, setSubmitting] = useState(false);
  const [placedOrder, setPlacedOrder] = useState(null);
  const [orderPaid, setOrderPaid] = useState(false);
  const [qrSecondsLeft, setQrSecondsLeft] = useState(QR_TTL_SECONDS);
  // Hết hiệu lực khi đếm ngược về 0 (suy ra từ state, không cần state riêng).
  const qrExpired = qrSecondsLeft <= 0;

  // Tải phương thức giao/thanh toán (public).
  useEffect(() => {
    let active = true;
    getCheckoutOptions().then((data) => {
      if (!active) return;
      setOptions(data);
      setShippingMethodId((prev) => prev ?? data.shipping_methods[0]?.id ?? null);
      setPaymentMethodId((prev) => prev ?? data.payment_methods[0]?.id ?? null);
    });
    return () => {
      active = false;
    };
  }, []);

  // Tải sổ địa chỉ khi đã đăng nhập.
  const loadAddresses = () => {
    getAddresses().then((list) => {
      setAddresses(list);
      setSelectedAddressId((prev) => prev ?? list.find((a) => a.is_default)?.id ?? list[0]?.id ?? null);
      setShowAddressForm(list.length === 0);
    });
  };

  useEffect(() => {
    if (user) loadAddresses();
  }, [user]);

  // Sau khi đặt đơn chuyển khoản, hỏi server mỗi 4s tới khi SePay xác nhận (dừng khi đã trả/hết hạn).
  useEffect(() => {
    if (!placedOrder || !placedOrder.is_bank || orderPaid || qrExpired) return;
    const timer = setInterval(async () => {
      const fresh = await getOrder(placedOrder.id);
      if (fresh?.payment_status === "paid") {
        setOrderPaid(true);
      }
    }, 4000);
    return () => clearInterval(timer);
  }, [placedOrder, orderPaid, qrExpired]);

  // Đếm ngược hiệu lực mã QR; tự dừng khi về 0 (qrExpired = true).
  useEffect(() => {
    if (!placedOrder || !placedOrder.is_bank || orderPaid || qrExpired) return;
    const timer = setInterval(() => {
      setQrSecondsLeft((s) => Math.max(0, s - 1));
    }, 1000);
    return () => clearInterval(timer);
  }, [placedOrder, orderPaid, qrExpired]);

  const subtotal = items.reduce((sum, line) => sum + line.price * line.quantity, 0);
  const shippingMethod = options.shipping_methods.find((m) => m.id === shippingMethodId);
  const shipping = items.length ? shippingMethod?.shipping_fee ?? 0 : 0;
  const total = subtotal + shipping;

  const selectedPayment = options.payment_methods.find((m) => m.id === paymentMethodId);
  const isBankTransfer = (selectedPayment?.name ?? "").toLowerCase().includes("chuyển khoản");

  const updateAddressField = (field) => (event) => {
    const value = event.target.type === "checkbox" ? event.target.checked : event.target.value;
    setAddressForm({ ...addressForm, [field]: value });
  };

  const handleSaveAddress = async () => {
    const { recipient_name, recipient_phone, address_line, ward, province } = addressForm;
    if (!recipient_name || !recipient_phone || !address_line || !ward || !province) {
      alert("Vui lòng nhập đầy đủ thông tin địa chỉ.");
      return;
    }
    setSavingAddress(true);
    const result = await createAddress(addressForm);
    setSavingAddress(false);

    if (!result.ok) {
      alert(result.message ?? "Không lưu được địa chỉ.");
      return;
    }

    setAddressForm(EMPTY_ADDRESS);
    setShowAddressForm(false);
    setSelectedAddressId(result.data.address.id);
    loadAddresses();
  };

  const handlePlaceOrder = async () => {
    if (!selectedAddressId) {
      alert("Vui lòng chọn hoặc thêm địa chỉ giao hàng.");
      return;
    }
    // Mọi dòng giỏ phải có variant id (giỏ cũ có thể thiếu) để khớp sản phẩm khi đặt.
    if (items.some((line) => !line.variantId)) {
      alert("Có sản phẩm trong giỏ thiếu thông tin phân loại. Vui lòng xoá và thêm lại sản phẩm.");
      return;
    }

    setSubmitting(true);
    const result = await createOrder({
      address_id: selectedAddressId,
      shipping_method_id: shippingMethodId,
      payment_method_id: paymentMethodId,
      note: "",
      items: items.map((line) => ({ variant_id: line.variantId, quantity: line.quantity })),
    });
    setSubmitting(false);

    if (!result.ok) {
      alert(result.message ?? "Đặt hàng không thành công, vui lòng thử lại.");
      return;
    }

    clearCart();
    setOrderPaid(false);
    setQrSecondsLeft(QR_TTL_SECONDS);
    setPlacedOrder({ ...result.data, is_bank: isBankTransfer });
  };

  // Tạo lại mã QR: cùng đơn/nội dung CK, chỉ làm mới thời hạn và tiếp tục poll.
  const handleRegenerateQr = () => {
    setQrSecondsLeft(QR_TTL_SECONDS);
  };

  // --- Các trạng thái màn hình ---

  if (placedOrder) {
    // Đơn COD: chỉ cần báo thành công.
    if (!placedOrder.is_bank) {
      return (
        <div className="cart-empty">
          <p>🎉 Đặt hàng thành công! Mã đơn của bạn: <strong>{placedOrder.payment_code}</strong></p>
          <Link href="/shop" className="cart-empty-btn">Tiếp tục mua sắm</Link>
        </div>
      );
    }

    // Đơn chuyển khoản: màn hướng dẫn thanh toán (QR + chuyển khoản thủ công) + tóm tắt đơn.
    const qrUrl = buildSepayQrUrl(placedOrder.payment_code, placedOrder.total_amount);
    const mm = String(Math.floor(qrSecondsLeft / 60)).padStart(2, "0");
    const ss = String(qrSecondsLeft % 60).padStart(2, "0");

    return (
      <div className="co-result">
        <div className="co-result-head">
          <span className="co-result-check">✔</span>
          <h2 className="co-result-title">Đặt hàng thành công</h2>
          <p className="co-result-code">Mã đơn hàng #{placedOrder.payment_code}</p>
        </div>

        <div className="co-result-layout">
          <div className="co-result-main">
            <div className="co-pay-wrap">
              <div className="co-pay-header">Hướng dẫn thanh toán qua chuyển khoản ngân hàng</div>

              <div className="co-pay-body">
                <div className="co-pay-methods">
                  {/* Cách 1: QR */}
                  <div className="co-pay-method">
                    <h3 className="co-pay-method-title">Cách 1: Quét mã QR</h3>

                    {orderPaid ? (
                      <div className="co-pay-paid">✅ Đã thanh toán</div>
                    ) : qrExpired ? (
                      <div className="co-qr-expired">
                        <p>Mã QR đã hết hạn (15 phút).</p>
                        <button type="button" className="co-btn-solid" onClick={handleRegenerateQr}>
                          Tạo lại mã QR
                        </button>
                      </div>
                    ) : qrUrl ? (
                      <>
                        <img src={qrUrl} alt="Mã QR thanh toán" className="co-qr-img" />
                        <p className="co-qr-ttl">Mã QR còn hiệu lực: <strong>{mm}:{ss}</strong></p>
                      </>
                    ) : (
                      <p className="co-pay-note">Chưa cấu hình mã QR (NEXT_PUBLIC_SEPAY_QR_BASE).</p>
                    )}

                    <p className="co-qr-status">
                      {orderPaid ? (
                        <>Trạng thái: <strong>Đã thanh toán ✓</strong></>
                      ) : (
                        <>Trạng thái: Chờ thanh toán... <span className="co-spinner" aria-hidden="true" /></>
                      )}
                    </p>
                  </div>

                  {/* Cách 2: Chuyển khoản thủ công */}
                  <div className="co-pay-method co-pay-method-manual">
                    <h3 className="co-pay-method-title">Cách 2: Chuyển khoản thủ công</h3>
                    <div className="co-bank-info">
                      <div className="co-bank-row"><span>Ngân hàng</span><strong className="co-bank-accent">{BANK_INFO.name ?? "—"}</strong></div>
                      <div className="co-bank-row"><span>Chủ tài khoản</span><strong>{BANK_INFO.holder ?? "—"}</strong></div>
                      <div className="co-bank-row"><span>Số tài khoản</span><strong>{BANK_INFO.account ?? "—"}</strong></div>
                      <div className="co-bank-row"><span>Số tiền</span><strong>{formatPrice(placedOrder.total_amount)}</strong></div>
                      <div className="co-bank-row co-bank-row-last"><span>Nội dung CK</span><strong className="co-bank-accent">{placedOrder.payment_code}</strong></div>
                    </div>
                    <p className="co-pay-warning">
                      Lưu ý: Vui lòng giữ nguyên nội dung chuyển khoản <strong>{placedOrder.payment_code}</strong> để
                      hệ thống tự động xác nhận thanh toán.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <Link href="/shop" className="co-continue-btn">🛍 Tiếp tục mua sắm</Link>
          </div>

          {/* Tóm tắt đơn hàng */}
          <aside className="co-summary">
            <h3 className="co-summary-title">Đơn hàng của bạn</h3>
            <div className="co-summary-items">
              {(placedOrder.items ?? []).map((item) => (
                <div className="co-summary-item" key={item.id}>
                  <span className="co-summary-item-name">
                    {item.product_name} <em>× {item.quantity}</em>
                  </span>
                  <span>{formatPrice(item.price * item.quantity)}</span>
                </div>
              ))}
            </div>
            <div className="co-summary-row">
              <span>Phí vận chuyển</span>
              <span>{formatPrice(placedOrder.shipping_fee)}</span>
            </div>
            <div className="co-summary-total">
              <span>Tổng cộng</span>
              <span>{formatPrice(placedOrder.total_amount)}</span>
            </div>
            <Link href={`/account/orders/${placedOrder.id}`} className="co-detail-btn">
              Xem chi tiết đơn hàng
            </Link>
          </aside>
        </div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="cart-empty">
        <p>Bạn cần đăng nhập để thanh toán và quản lý đơn hàng.</p>
        <Link href="/login" className="cart-empty-btn">Đăng nhập</Link>
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
      <h2 className="co-heading">Thanh toán đơn hàng</h2>

      <div className="co-layout">
        {/* Cột trái: địa chỉ · vận chuyển · thanh toán */}
        <div className="co-main">
          <section className="co-card">
            <div className="co-card-head">
              <span className="co-icon-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" /></svg>
              </span>
              <h3 className="co-card-title">Địa chỉ giao hàng</h3>
            </div>

            {addresses.length > 0 && (
              <div className="co-addr-grid">
                {addresses.map((address) => (
                  <label key={address.id} className={`co-addr-card ${selectedAddressId === address.id ? "active" : ""}`}>
                    <input type="radio" name="address" checked={selectedAddressId === address.id} onChange={() => setSelectedAddressId(address.id)} />
                    <div className="co-addr-top">
                      {address.is_default ? <span className="co-addr-badge">Mặc định</span> : <span />}
                      {selectedAddressId === address.id && (
                        <svg className="co-addr-check" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.9-4.9 1.4 1.4-6.3 6.3Z" /></svg>
                      )}
                    </div>
                    <p className="co-addr-name">{address.recipient_name} · {address.recipient_phone}</p>
                    <p className="co-addr-detail">{[address.address_line, address.ward, address.district, address.province].filter(Boolean).join(", ")}</p>
                  </label>
                ))}

                {!showAddressForm && (
                  <button type="button" className="co-addr-add" onClick={() => setShowAddressForm(true)}>
                    <span className="co-addr-add-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M12 5v14M5 12h14" /></svg>
                    </span>
                    <span>Thêm địa chỉ mới</span>
                  </button>
                )}
              </div>
            )}

            {showAddressForm && (
              <div className="co-address-form">
                <div className="co-field">
                  <label>Họ và tên người nhận</label>
                  <input type="text" placeholder="Nhập đầy đủ họ tên" value={addressForm.recipient_name} onChange={updateAddressField("recipient_name")} />
                </div>
                <div className="co-field">
                  <label>Số điện thoại</label>
                  <input type="tel" placeholder="Ví dụ: 0912345678" value={addressForm.recipient_phone} onChange={updateAddressField("recipient_phone")} />
                </div>
                {/* Tỉnh/Thành phố · Quận/Huyện · Phường/Xã: chọn từ dữ liệu có sẵn */}
                <div className="profile-address-form co-address-location">
                  <AddressLocationFields value={addressForm} onChange={setAddressForm} />
                </div>
                <div className="co-field">
                  <label>Địa chỉ chi tiết</label>
                  <input type="text" placeholder="Số nhà, tên đường..." value={addressForm.address_line} onChange={updateAddressField("address_line")} />
                </div>
                <label className="co-checkbox">
                  <input type="checkbox" checked={addressForm.is_default} onChange={updateAddressField("is_default")} />
                  <span>Đặt làm địa chỉ mặc định</span>
                </label>
                <div className="co-cart-actions">
                  {addresses.length > 0 && (
                    <button type="button" className="co-btn-outline" onClick={() => { setShowAddressForm(false); setAddressForm(EMPTY_ADDRESS); }}>
                      Huỷ
                    </button>
                  )}
                  <button type="button" className="co-btn-solid" onClick={handleSaveAddress} disabled={savingAddress}>
                    {savingAddress ? "Đang lưu..." : "Lưu địa chỉ"}
                  </button>
                </div>
              </div>
            )}
          </section>

          <section className="co-card">
            <div className="co-card-head">
              <span className="co-icon-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z" /><circle cx="7" cy="18" r="2" /><circle cx="17" cy="18" r="2" /></svg>
              </span>
              <h3 className="co-card-title">Phương thức vận chuyển</h3>
            </div>
            {options.shipping_methods.map((method) => (
              <label key={method.id} className={`co-ship-option ${shippingMethodId === method.id ? "active" : ""}`}>
                <input type="radio" name="shipping" checked={shippingMethodId === method.id} onChange={() => setShippingMethodId(method.id)} />
                <div>
                  <strong>{method.name}</strong>
                </div>
                <span className="co-ship-fee">{formatPrice(method.shipping_fee)}</span>
              </label>
            ))}
          </section>

          <section className="co-card">
            <div className="co-card-head">
              <span className="co-icon-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" /></svg>
              </span>
              <h3 className="co-card-title">Phương thức thanh toán</h3>
            </div>
            <div className="co-pay-grid">
              {options.payment_methods.map((method) => (
                <label key={method.id} className={`co-pay-card ${paymentMethodId === method.id ? "active" : ""}`}>
                  <span className="co-pay-card-label">{method.name}</span>
                  <input type="radio" name="payment" checked={paymentMethodId === method.id} onChange={() => setPaymentMethodId(method.id)} />
                </label>
              ))}
            </div>

            {isBankTransfer && (
              <p className="co-pay-note">
                Thực hiện thanh toán vào ngay tài khoản ngân hàng của chúng tôi. Vui lòng sử dụng Mã đơn hàng của bạn
                trong phần Nội dung thanh toán. Đơn hàng sẽ được giao sau khi tiền đã chuyển.
              </p>
            )}

            <div className="co-trust">
              <span className="co-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 3 4 6v6c0 5 3.5 7.5 8 9 4.5-1.5 8-4 8-9V6l-8-3Z" /></svg>Thanh toán an toàn</span>
              <span className="co-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7" /><path d="M21 4v5h-5" /></svg>Đổi trả trong 7 ngày</span>
              <span className="co-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M4 18v-6a8 8 0 0 1 16 0v6" /><path d="M5 13h2v5H6a2 2 0 0 1-2-2v-1a2 2 0 0 1 1-2Zm14 0a2 2 0 0 1 1 2v1a2 2 0 0 1-2 2h-1v-5Z" /></svg>Hỗ trợ 24/7</span>
            </div>
          </section>
        </div>

        {/* Cột phải: chi tiết đơn hàng */}
        <aside className="co-summary">
          <h3 className="co-summary-title">Đơn hàng của bạn</h3>
          <div className="co-summary-items">
            {items.map((line) => (
              <div className="co-summary-item" key={line.key}>
                <div className="co-summary-item-media">
                  <img className="co-summary-thumb" src={resolveImage(line.image)} alt={line.name} />
                  <span className="co-summary-item-name">
                    {line.name}
                    {line.variantName && <em> · {line.variantName}</em>}
                    <em> × {line.quantity}</em>
                  </span>
                </div>
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
          {items.length > 0 && shipping === 0 && (
            <div className="co-freeship-badge">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.9-4.9 1.4 1.4-6.3 6.3Z" /></svg>
              Đã áp dụng miễn phí vận chuyển
            </div>
          )}
          <div className="co-summary-total">
            <span>Tổng thanh toán</span>
            <span>{formatPrice(total)}</span>
          </div>
          <p className="co-vat-note">(Đã bao gồm thuế VAT)</p>

          <button type="button" className="co-place-btn" onClick={handlePlaceOrder} disabled={submitting}>
            {submitting ? "Đang đặt hàng..." : "Đặt hàng ngay"}
          </button>

          <p className="co-terms">
            Bằng cách đặt hàng, bạn xác nhận đã đọc và đồng ý với
            <br />
            <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách bảo mật</a> của PetWorld.
          </p>
        </aside>
      </div>
    </>
  );
}
