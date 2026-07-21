"use client";

import Link from "next/link";
import { useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { formatPrice, resolveProductImage } from "@/lib/format";
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
  renewPayment,
  buildSepayQrUrl,
  getAvailableVouchers,
} from "@/lib/checkout";
import AddressLocationFields from "@/components/auth/AddressLocationFields";
import { toastError } from "@/lib/toast";
import CheckoutSuccessView from "@/components/checkout/CheckoutSuccessView";
import {
  clearBuyNow,
  getBuyNowSnapshot,
  getServerBuyNowSnapshot,
  onBuyNowChange,
  parseBuyNow,
} from "@/lib/buyNow";

// Mã QR hết hạn sau 15 phút, sau đó khách bấm tạo lại.
const QR_TTL_SECONDS = 15 * 60;

// Phiên thanh toán đang chờ được lưu localStorage để khôi phục khi rớt mạng / tải lại trang.
const PENDING_PAYMENT_KEY = "petworld_pending_payment";

function savePendingPayment(session) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(PENDING_PAYMENT_KEY, JSON.stringify(session));
  } catch {
    // localStorage đầy/không dùng được: bỏ qua, chỉ mất khả năng khôi phục.
  }
}

function readPendingPayment() {
  if (typeof window === "undefined") return null;
  try {
    const raw = localStorage.getItem(PENDING_PAYMENT_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function clearPendingPayment() {
  if (typeof window === "undefined") return;
  try {
    localStorage.removeItem(PENDING_PAYMENT_KEY);
  } catch {
    // bỏ qua
  }
}

// Thông tin ngân hàng hiển thị (chuyển khoản thủ công) — cấu hình qua env.
const BANK_INFO = {
  name: process.env.NEXT_PUBLIC_BANK_NAME || "MBBank",
  account: process.env.NEXT_PUBLIC_BANK_ACCOUNT_NUMBER || "VQRQAKCIT7920",
  holder: process.env.NEXT_PUBLIC_BANK_ACCOUNT_HOLDER || "LE TRAN PHAT",
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
  const cartItems = useMemo(() => parseCart(raw), [raw]);
  const buyNowRaw = useSyncExternalStore(onBuyNowChange, getBuyNowSnapshot, getServerBuyNowSnapshot);
  const buyNowItem = useMemo(() => parseBuyNow(buyNowRaw), [buyNowRaw]);
  const items = useMemo(() => buyNowItem ? [buyNowItem] : cartItems, [buyNowItem, cartItems]);

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
  const [fullOrderDetails, setFullOrderDetails] = useState(null);
  // Lưu MỐC hết hạn tuyệt đối (ms epoch) thay vì đếm giây, để sống sót khi tải lại/rớt mạng.
  const [paymentExpiresAt, setPaymentExpiresAt] = useState(null);
  const [nowMs, setNowMs] = useState(() => Date.now());
  const qrSecondsLeft = paymentExpiresAt ? Math.max(0, Math.round((paymentExpiresAt - nowMs) / 1000)) : 0;
  // Chỉ coi là hết hiệu lực khi đã có mốc và đã qua mốc đó.
  const qrExpired = paymentExpiresAt !== null && nowMs >= paymentExpiresAt;

  const [appliedVoucher, setAppliedVoucher] = useState(null);
  const [vouchers, setVouchers] = useState([]);
  const [showVoucherModal, setShowVoucherModal] = useState(false);
  const [loadingVouchers, setLoadingVouchers] = useState(false);

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
      if (list.length === 0) {
        setAddressForm((current) => ({
          ...current,
          recipient_name: current.recipient_name || user?.name || "",
          recipient_phone: current.recipient_phone || user?.phone || "",
        }));
      }
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

  // Nhịp đồng hồ mỗi giây để tính lại thời gian còn lại từ mốc hết hạn tuyệt đối.
  useEffect(() => {
    if (!placedOrder || !placedOrder.is_bank || orderPaid || qrExpired) return;
    const timer = setInterval(() => setNowMs(Date.now()), 1000);
    return () => clearInterval(timer);
  }, [placedOrder, orderPaid, qrExpired]);

  // Khi thanh toán thành công: lưu cờ paid vào localStorage để reload vẫn ra trang thành công.
  useEffect(() => {
    if (!orderPaid || !placedOrder?.is_bank) return;
    const sess = readPendingPayment();
    if (sess?.orderId === placedOrder.id) {
      savePendingPayment({ ...sess, paid: true });
    }
  }, [orderPaid, placedOrder]);

  // Khôi phục phiên thanh toán đang chờ khi vào lại trang (rớt mạng/tải lại) — chỉ chạy khi mount.
  useEffect(() => {
    const sess = readPendingPayment();
    if (!sess?.orderId) return;
    const exp = sess.expiresAt ?? 0;

    // Đã ra ngoài cửa sổ hiệu lực QR: dọn phiên, đơn tra cứu ở /account/orders.
    if (exp && Date.now() > exp) {
      clearPendingPayment();
      return;
    }

    let cancelled = false;

    // Khôi phục ngay từ cache để hiển thị được cả khi đang offline.
    if (sess.snapshot) {
      setPlacedOrder(sess.snapshot);
      setPaymentExpiresAt(exp || null);
      setNowMs(Date.now());
      if (sess.paid) setOrderPaid(true);
    }

    // Đối chiếu với server: paid -> trang thành công; cancelled -> dọn; còn chờ -> tiếp tục.
    (async () => {
      const fresh = await getOrder(sess.orderId);
      if (cancelled || !fresh) return; // offline hoặc lỗi: giữ theo cache đã khôi phục
      if (fresh.payment_status === "paid") {
        setOrderPaid(true);
        savePendingPayment({ ...sess, paid: true });
        return;
      }
      if (fresh.status === "cancelled") {
        clearPendingPayment();
        setPlacedOrder(null);
        setPaymentExpiresAt(null);
        toastError("Đơn thanh toán trước đó đã hết hạn. Vui lòng đặt lại.");
        return;
      }
      if (fresh.expires_at) {
        setPaymentExpiresAt(Date.parse(fresh.expires_at));
        setNowMs(Date.now());
      }
    })();

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Tải chi tiết đơn hàng đầy đủ khi đặt hàng hoặc thanh toán thành công
  useEffect(() => {
    if (!placedOrder) return;
    if (!placedOrder.is_bank || orderPaid) {
      getOrder(placedOrder.id).then((data) => {
        if (data) {
          setFullOrderDetails(data);
        }
      });
    }
  }, [placedOrder, orderPaid]);

  const subtotal = items.reduce((sum, line) => sum + line.price * line.quantity, 0);
  const shippingMethod = options.shipping_methods.find((m) => m.id === shippingMethodId);
  const shipping = items.length ? shippingMethod?.shipping_fee ?? 0 : 0;

  const voucherKey = buyNowItem ? "petworld_buynow_applied_voucher" : "petworld_cart_applied_voucher";

  const discount = appliedVoucher ? Math.min(parseFloat(appliedVoucher.discount_value), subtotal) : 0;
  const total = Math.max(0, subtotal + shipping - discount);

  // Đọc và kiểm tra tính hợp lệ của voucher từ localStorage
  useEffect(() => {
    if (typeof window !== "undefined") {
      try {
        const stored = localStorage.getItem(voucherKey);
        if (stored) {
          const parsed = JSON.parse(stored);
          getAvailableVouchers(subtotal).then((list) => {
            const found = list.find((v) => v.id === parsed.id && v.can_apply);
            if (found) {
              setAppliedVoucher(parsed);
            } else {
              localStorage.removeItem(voucherKey);
              setAppliedVoucher(null);
              toastError("Mã giảm giá đã lưu không còn hiệu lực hoặc đã hết lượt sử dụng.");
            }
          });
        } else {
          setTimeout(() => {
            setAppliedVoucher(null);
          }, 0);
        }
      } catch (e) {
        console.error("[CheckoutView] Lỗi đọc voucher:", e);
      }
    }
  }, [subtotal, voucherKey]);

  // Tự động gỡ voucher nếu subtotal không đủ điều kiện tối thiểu
  useEffect(() => {
    if (appliedVoucher && subtotal < parseFloat(appliedVoucher.min_order_value)) {
      const oldVoucher = appliedVoucher;
      setTimeout(() => {
        setAppliedVoucher(null);
      }, 0);
      if (typeof window !== "undefined") {
        localStorage.removeItem(voucherKey);
      }
      toastError(`Mã giảm giá ${oldVoucher.code} đã bị gỡ bỏ do tổng tiền đơn hàng chưa đạt tối thiểu ${formatPrice(oldVoucher.min_order_value)}.`);
    }
  }, [subtotal, appliedVoucher, voucherKey]);

  const handleOpenVoucherModal = async () => {
    setLoadingVouchers(true);
    setShowVoucherModal(true);
    try {
      const list = await getAvailableVouchers(subtotal);
      setVouchers(list);
    } catch (error) {
      console.error("[getAvailableVouchers] Lỗi khi tải voucher:", error);
      toastError("Không thể tải danh sách mã giảm giá.");
    } finally {
      setLoadingVouchers(false);
    }
  };

  const handleApplyVoucher = (voucher) => {
    if (!voucher.can_apply) return;
    setAppliedVoucher(voucher);
    if (typeof window !== "undefined") {
      localStorage.setItem(voucherKey, JSON.stringify(voucher));
    }
    setShowVoucherModal(false);
  };

  const handleRemoveVoucher = () => {
    setAppliedVoucher(null);
    if (typeof window !== "undefined") {
      localStorage.removeItem(voucherKey);
    }
  };

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
      toastError(result.message ?? "Không lưu được địa chỉ.");
      return;
    }

    setAddressForm(EMPTY_ADDRESS);
    setShowAddressForm(false);
    setSelectedAddressId(result.data.address.id);
    loadAddresses();
  };

  const handlePlaceOrder = async () => {
    if (!selectedAddressId) {
      toastError("Vui lòng chọn hoặc thêm địa chỉ giao hàng.");
      return;
    }
    // Mọi dòng giỏ phải có variant id (giỏ cũ có thể thiếu) để khớp sản phẩm khi đặt.
    if (items.some((line) => !line.variantId)) {
      toastError("Có sản phẩm trong giỏ thiếu thông tin phân loại. Vui lòng xoá và thêm lại sản phẩm.");
      return;
    }

    setSubmitting(true);
    const voucherSnapshot = appliedVoucher
      ? {
          id: appliedVoucher.id,
          code: appliedVoucher.code,
          discount_value: appliedVoucher.discount_value,
        }
      : null;
    const result = await createOrder({
      address_id: selectedAddressId,
      shipping_method_id: shippingMethodId,
      payment_method_id: paymentMethodId,
      voucher_id: appliedVoucher?.id,
      note: "",
      items: items.map((line) => ({ variant_id: line.variantId, quantity: line.quantity })),
    });
    setSubmitting(false);

    if (!result.ok) {
      toastError(result.message ?? "Đặt hàng không thành công, vui lòng thử lại.");
      if (result.errors && result.errors.voucher_id) {
        setAppliedVoucher(null);
        if (typeof window !== "undefined") {
          localStorage.removeItem(voucherKey);
        }
      }
      return;
    }

    if (buyNowItem) {
      clearBuyNow();
    } else {
      clearCart();
    }
    setAppliedVoucher(null);
    if (typeof window !== "undefined") {
      localStorage.removeItem(voucherKey);
    }
    setOrderPaid(false);
    const snapshot = { ...result.data, is_bank: isBankTransfer, voucher: voucherSnapshot };
    setPlacedOrder(snapshot);

    if (isBankTransfer) {
      // Ưu tiên hạn do server trả (đồng bộ với scheduler tự hủy), fallback 15 phút phía client.
      const exp = result.data?.expires_at
        ? Date.parse(result.data.expires_at)
        : Date.now() + QR_TTL_SECONDS * 1000;
      setPaymentExpiresAt(exp);
      setNowMs(Date.now());
      savePendingPayment({ orderId: result.data.id, snapshot, expiresAt: exp, paid: false });
    } else {
      setPaymentExpiresAt(null);
    }
  };

  // Tạo lại mã QR: giữ nguyên mã PW{id}, gọi API gia hạn expires_at ở server rồi poll tiếp.
  const handleRegenerateQr = async () => {
    if (!placedOrder) return;
    const res = await renewPayment(placedOrder.id);
    if (!res.ok) {
      // Đơn đã bị hủy (hết hạn) không mở lại được -> dọn phiên, khách đặt đơn mới.
      toastError(res.message ?? "Không thể tạo lại mã QR. Vui lòng đặt lại đơn.");
      clearPendingPayment();
      return;
    }
    const exp = res.data?.order?.expires_at
      ? Date.parse(res.data.order.expires_at)
      : Date.now() + QR_TTL_SECONDS * 1000;
    setPaymentExpiresAt(exp);
    setNowMs(Date.now());
    const sess = readPendingPayment();
    if (sess) savePendingPayment({ ...sess, expiresAt: exp });
  };

  const copyText = async (value) => {
    if (!value || typeof navigator === "undefined" || !navigator.clipboard) return;
    try {
      await navigator.clipboard.writeText(String(value));
    } catch {
      // Clipboard is best-effort only; checkout flow must not depend on it.
    }
  };

  // --- Các trạng thái màn hình ---

  if (placedOrder) {
    // Đơn COD hoặc đơn chuyển khoản đã thanh toán thành công
    if (!placedOrder.is_bank || orderPaid) {
      return <CheckoutSuccessView order={fullOrderDetails || placedOrder} />;
    }

    // Đơn chuyển khoản: màn hướng dẫn thanh toán (QR + chuyển khoản thủ công) + tóm tắt đơn.
    const qrUrl = buildSepayQrUrl(placedOrder.payment_code, placedOrder.total_amount);
    const mm = String(Math.floor(qrSecondsLeft / 60)).padStart(2, "0");
    const ss = String(qrSecondsLeft % 60).padStart(2, "0");
    const orderItems = placedOrder.items ?? [];
    const placedDiscount = Number(placedOrder.discount_amount ?? 0);

    return (
      <div className="co-result">
        <div className="co-result-hero">
          <div className="co-result-success">
            <span className="co-result-check">✓</span>
            <div>
              <h2 className="co-result-title">Đặt hàng thành công!</h2>
              <p className="co-result-subtitle">Cảm ơn bạn đã mua sắm tại PetWorld</p>
              <p className="co-result-code">
                Mã đơn hàng: <strong>{placedOrder.payment_code}</strong>
                <button type="button" onClick={() => copyText(placedOrder.payment_code)} aria-label="Sao chép mã đơn hàng">⧉</button>
              </p>
            </div>
          </div>
          <div className="co-secure-note">
            <span>🔒</span>
            <div>
              <strong>Thanh toán bảo mật</strong>
              <small>SSL 256-bit</small>
            </div>
          </div>
        </div>

        <div className="co-result-timer">
          <span>Vui lòng thanh toán trong vòng</span>
          <strong>{mm}</strong>
          <em>Phút</em>
          <strong>{ss}</strong>
          <em>Giây</em>
          <span>để hệ thống xác nhận đơn hàng.</span>
        </div>

        <div className="co-result-layout">
          <div className="co-result-main">
            <div className="co-pay-wrap">
              <div className="co-pay-body">
                <section className="co-pay-section">
                  <div className="co-pay-section-head">
                    <h3>1. Thanh toán nhanh bằng QR Code</h3>
                  </div>
                  <p className="co-pay-desc">Quét mã QR bằng ứng dụng ngân hàng hoặc ví điện tử</p>

                  {orderPaid ? (
                    <div className="co-pay-paid">✓ Đã thanh toán</div>
                  ) : qrExpired ? (
                    <div className="co-qr-expired">
                      <p>Mã QR đã hết hạn sau 15 phút.</p>
                      <button type="button" className="co-btn-solid" onClick={handleRegenerateQr}>
                        Tạo lại mã QR
                      </button>
                    </div>
                  ) : (
                    <div className="co-qr-panel">
                      <img
                        src={qrUrl}
                        alt="QR thanh toán - Ngân hàng TMCP Quân Đội - VQRQAKCIT7920 - LE TRAN PHAT"
                        className="co-qr-img"
                        width="300"
                      />
                    </div>
                  )}

                  <div className="co-payment-logos" aria-label="Ứng dụng hỗ trợ quét QR">
                    <span>Vietcombank</span>
                    <span>BIDV</span>
                    <span>MB Bank</span>
                    <span>Momo</span>
                    <span>ZaloPay</span>
                    <span>VNPay</span>
                  </div>
                </section>

                <div className="co-divider"><span>Hoặc</span></div>

                <section className="co-pay-section co-pay-section-manual">
                  <div className="co-pay-section-head">
                    <h3>2. Chuyển khoản thủ công</h3>
                  </div>
                  <p className="co-pay-desc">Sử dụng thông tin bên dưới để chuyển khoản qua ứng dụng ngân hàng</p>
                  <div className="co-bank-info">
                    {[
                      ["Ngân hàng", BANK_INFO.name],
                      ["Chủ tài khoản", BANK_INFO.holder],
                      ["Số tài khoản", BANK_INFO.account],
                      ["Số tiền", formatPrice(placedOrder.total_amount)],
                      ["Nội dung chuyển khoản", placedOrder.payment_code],
                    ].map(([label, value]) => (
                      <div className="co-bank-row" key={label}>
                        <span>{label}</span>
                        <strong className={label === "Số tiền" || label.includes("Nội dung") ? "co-bank-accent" : ""}>{value}</strong>
                        <button type="button" onClick={() => copyText(value)}>Sao chép</button>
                      </div>
                    ))}
                  </div>
                  <p className="co-pay-warning">
                    Lưu ý quan trọng: Chuyển khoản đúng số tiền và nội dung để đơn hàng được xác nhận nhanh chóng.
                  </p>
                </section>
              </div>
            </div>
          </div>

          {/* Tóm tắt đơn hàng */}
          <aside className="co-summary co-result-summary">
            <h3 className="co-summary-title">Thông tin đơn hàng</h3>
            <div className="co-summary-items">
              {orderItems.map((item) => (
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
            {placedDiscount > 0 && (
              <div className="co-summary-row co-summary-discount">
                <span>{placedOrder.voucher?.code ? `Voucher (${placedOrder.voucher.code})` : "Voucher"}</span>
                <span className="co-discount-amount">-{formatPrice(placedDiscount)}</span>
              </div>
            )}
            <div className="co-summary-total">
              <span>Tổng thanh toán</span>
              <span>{formatPrice(placedOrder.total_amount)}</span>
            </div>
            <p className="co-vat-note">(Đã bao gồm VAT)</p>
            <div className="co-result-commitments">
              <strong>Cam kết từ PetWorld</strong>
              <span>100% sản phẩm chính hãng</span>
              <span>Đổi trả miễn phí trong 7 ngày</span>
              <span>Giao hàng nhanh toàn quốc</span>
              <span>Đóng gói cẩn thận, thân thiện</span>
            </div>
            <div className="co-result-support">
              <strong>Cần hỗ trợ thanh toán?</strong>
              <span>Chat với chúng tôi</span>
              <span>Gọi hotline 1900 1234</span>
              <span>Email support@petworld.vn</span>
            </div>
            <Link href={`/account/orders/${placedOrder.id}`} className="co-detail-btn">
              Xem chi tiết đơn hàng
            </Link>
            <Link href="/shop" className="co-continue-btn">Tiếp tục mua sắm →</Link>
          </aside>
        </div>

        <div className="co-result-footer">
          <span>🔒 Thanh toán an toàn</span>
          <span>🎧 Hỗ trợ nhanh chóng</span>
          <span>🚚 Giao hàng toàn quốc</span>
          <span>🔁 Đổi trả dễ dàng</span>
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
                  <img className="co-summary-thumb" src={resolveProductImage(line.image)} alt={line.name} />
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

          {/* Voucher trigger */}
          <div className="co-voucher-section">
            {appliedVoucher ? (
              <div className="co-voucher-applied">
                <div className="co-voucher-applied-info">
                  <span className="co-voucher-tag-icon">🎟</span>
                  <span className="co-voucher-code-name"><strong>{appliedVoucher.code}</strong></span>
                  <span className="co-voucher-discount-text">(-{formatPrice(discount)})</span>
                </div>
                <button type="button" className="co-voucher-remove-btn" onClick={handleRemoveVoucher}>Gỡ</button>
              </div>
            ) : (
              <button type="button" className="co-voucher-select-trigger" onClick={handleOpenVoucherModal}>
                <span className="co-voucher-tag-icon">🎟</span>
                <span>Chọn mã giảm giá</span>
                <span className="co-voucher-arrow">➔</span>
              </button>
            )}
          </div>

          {discount > 0 && (
            <div className="co-summary-row co-summary-discount">
              <span>Giảm giá</span>
              <span className="co-discount-amount">-{formatPrice(discount)}</span>
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

      {/* Voucher Modal */}
      {showVoucherModal && (
        <div className="co-modal-overlay" onClick={() => setShowVoucherModal(false)}>
          <div className="co-modal-card" onClick={(e) => e.stopPropagation()}>
            <div className="co-modal-header">
              <h3>Chọn Voucher giảm giá</h3>
              <button type="button" className="co-modal-close" onClick={() => setShowVoucherModal(false)}>×</button>
            </div>
            <div className="co-modal-body">
              {loadingVouchers ? (
                <div className="co-loading-wrap">
                  <span className="co-spinner" aria-hidden="true"></span>
                  <p>Đang tải danh sách voucher...</p>
                </div>
              ) : vouchers.length === 0 ? (
                <p className="co-no-vouchers">Hiện tại không có voucher nào khả dụng.</p>
              ) : (
                <div className="co-voucher-list">
                  {vouchers.map((voucher) => {
                    const formattedMin = formatPrice(voucher.min_order_value);
                    const formattedDiscount = formatPrice(voucher.discount_value);
                    const startDateStr = new Date(voucher.start_date).toLocaleDateString('vi-VN');
                    const endDateStr = new Date(voucher.end_date).toLocaleDateString('vi-VN');
                    
                    return (
                      <div 
                        key={voucher.id} 
                        className={`co-voucher-item ${!voucher.can_apply ? 'disabled' : ''} ${appliedVoucher?.id === voucher.id ? 'selected' : ''}`}
                      >
                        <div className="co-voucher-card-left">
                          <div className="co-voucher-discount-val">-{formattedDiscount}</div>
                          <div className="co-voucher-badge-code">{voucher.code}</div>
                        </div>
                        <div className="co-voucher-card-right">
                          <h4 className="co-voucher-title">{voucher.description || `Giảm ngay ${formattedDiscount}`}</h4>
                          <p className="co-voucher-condition">Đơn tối thiểu: <strong>{formattedMin}</strong></p>
                          <p className="co-voucher-duration">Hạn dùng: {startDateStr} - {endDateStr}</p>
                          
                          {!voucher.can_apply && (
                            <p className="co-voucher-warning-text">
                              Mua thêm <strong>{formatPrice(voucher.missing_amount)}</strong> để sử dụng mã này
                            </p>
                          )}
                          
                          <div className="co-voucher-action-btn-wrap">
                            {voucher.can_apply ? (
                              appliedVoucher?.id === voucher.id ? (
                                <button 
                                  type="button" 
                                  className="co-voucher-btn selected"
                                  onClick={handleRemoveVoucher}
                                >
                                  Đang chọn ✓
                                </button>
                              ) : (
                                <button 
                                  type="button" 
                                  className="co-voucher-btn active"
                                  onClick={() => handleApplyVoucher(voucher)}
                                >
                                  Áp dụng
                                </button>
                              )
                            ) : (
                              <button type="button" className="co-voucher-btn" disabled>
                                Chưa đủ điều kiện
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
