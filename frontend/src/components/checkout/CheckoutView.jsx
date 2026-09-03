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
  restoreToCart,
  onCartChange,
} from "@/lib/cart";
import {
  getCheckoutOptions,
  getAddresses,
  createAddress,
  createOrder,
  getOrder,
  getPaymentStatus,
  checkSepayPayment,
  renewPayment,
  buildSepayQrUrl,
  getAvailableVouchers,
  getAutomaticProductVoucher,
  getEligibleShippingPromotions,
  getShippingQuote,
} from "@/lib/checkout";
import { cancelOrder } from "@/lib/auth";
import AddressLocationFields from "@/components/auth/AddressLocationFields";
import { toastError } from "@/lib/toast";
import CheckoutSuccessView from "@/components/checkout/CheckoutSuccessView";
import PaymentLeaveGuard from "@/components/checkout/PaymentLeaveGuard";
import PaymentQrModal from "@/components/checkout/PaymentQrModal";
import {
  clearBuyNow,
  getBuyNowSnapshot,
  getServerBuyNowSnapshot,
  onBuyNowChange,
  parseBuyNow,
} from "@/lib/buyNow";

// Mã QR hết hạn sau 15 phút, sau đó khách bấm tạo lại.
const QR_TTL_SECONDS = 15 * 60;

// Nhịp đọc trạng thái trong DB (nhanh) và nhịp đối soát với API SePay (chậm).
const PAYMENT_POLL_MS = 4000;
const PAYMENT_RECONCILE_MS = 30000;

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
  account: process.env.NEXT_PUBLIC_BANK_ACCOUNT_NUMBER || "0865130622",
  holder: process.env.NEXT_PUBLIC_BANK_ACCOUNT_HOLDER || "LE TRAN PHAT",
};

const EMPTY_ADDRESS = {
  recipient_name: "",
  recipient_phone: "",
  address_line: "",
  ward: "",
  district: "",
  province: "",
  ghn_district_id: "",
  ghn_ward_code: "",
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
  const [shippingQuote, setShippingQuote] = useState(null);
  const [shippingQuoteLoading, setShippingQuoteLoading] = useState(false);
  const [shippingQuoteError, setShippingQuoteError] = useState("");
  const [paymentMethodId, setPaymentMethodId] = useState(null);

  const [addresses, setAddresses] = useState([]);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [addressForm, setAddressForm] = useState(EMPTY_ADDRESS);
  const [savingAddress, setSavingAddress] = useState(false);

  const [submitting, setSubmitting] = useState(false);
  const [placedOrder, setPlacedOrder] = useState(null);
  const [orderPaid, setOrderPaid] = useState(false);
  // Đang gọi API hủy đơn: tạm dừng poll để request không xếp hàng chồng lên nhau.
  const [cancelling, setCancelling] = useState(false);
  const [fullOrderDetails, setFullOrderDetails] = useState(null);
  // Lưu MỐC hết hạn tuyệt đối (ms epoch) thay vì đếm giây, để sống sót khi tải lại/rớt mạng.
  const [paymentExpiresAt, setPaymentExpiresAt] = useState(null);
  const [nowMs, setNowMs] = useState(() => Date.now());
  const qrSecondsLeft = paymentExpiresAt ? Math.max(0, Math.round((paymentExpiresAt - nowMs) / 1000)) : 0;
  // Chỉ coi là hết hiệu lực khi đã có mốc và đã qua mốc đó.
  const qrExpired = paymentExpiresAt !== null && nowMs >= paymentExpiresAt;

  const [appliedVoucher, setAppliedVoucher] = useState(null);
  const [appliedShippingVoucher, setAppliedShippingVoucher] = useState(null);
  const [automaticVoucher, setAutomaticVoucher] = useState(null);
  const [eligibleShippingPromotions, setEligibleShippingPromotions] = useState([]);
  const [vouchers, setVouchers] = useState([]);
  const [showVoucherModal, setShowVoucherModal] = useState(false);
  const [loadingVouchers, setLoadingVouchers] = useState(false);
  const [voucherCodeInput, setVoucherCodeInput] = useState("");
  // Người dùng bấm ô nào thì nhóm đó được làm nổi trong modal.
  const [voucherFocusType, setVoucherFocusType] = useState("product");

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
    if (!placedOrder || !placedOrder.is_bank || orderPaid || qrExpired || cancelling) return;

    // Nhịp dày: chỉ đọc trạng thái trong DB, rất nhanh. Webhook SePay đã cập nhật
    // sẵn khi tiền về nên đây là đường nhận biết chính.
    const statusTimer = setInterval(async () => {
      const fresh = await getPaymentStatus(placedOrder.id);
      if (fresh?.payment_status === "paid") setOrderPaid(true);
    }, PAYMENT_POLL_MS);

    // Nhịp thưa: đối soát thẳng với API SePay để vá trường hợp webhook không tới
    // (ngrok tắt, SePay lỗi). Request này mất 8-10 giây nên không gọi dày được.
    const reconcileTimer = setInterval(async () => {
      const fresh = await checkSepayPayment(placedOrder.id);
      if (fresh?.payment_status === "paid") setOrderPaid(true);
    }, PAYMENT_RECONCILE_MS);

    return () => {
      clearInterval(statusTimer);
      clearInterval(reconcileTimer);
    };
  }, [placedOrder, orderPaid, qrExpired, cancelling]);

  // Nhịp đồng hồ mỗi giây để tính lại thời gian còn lại từ mốc hết hạn tuyệt đối.
  useEffect(() => {
    if (!placedOrder || !placedOrder.is_bank || orderPaid || qrExpired) return;
    const timer = setInterval(() => setNowMs(Date.now()), 1000);
    return () => clearInterval(timer);
  }, [placedOrder, orderPaid, qrExpired]);

  // Khi thanh toán thành công: lưu cờ paid vào localStorage để reload vẫn ra trang thành công.
  useEffect(() => {
    if (!orderPaid || !placedOrder?.is_bank) return;
    clearPendingPayment();
  }, [orderPaid, placedOrder]);

  // Khôi phục phiên thanh toán đang chờ khi vào lại trang (rớt mạng/tải lại) — chỉ chạy khi mount.
  useEffect(() => {
    const sess = readPendingPayment();
    if (!sess?.orderId) return;
    if (sess.paid) {
      clearPendingPayment();
      return;
    }
    const exp = sess.expiresAt ?? 0;

    // Đã ra ngoài cửa sổ hiệu lực QR: dọn phiên, đơn tra cứu ở /account/orders.
    if (exp && Date.now() > exp) {
      clearPendingPayment();
      return;
    }

    let cancelled = false;

    // Khôi phục ngay từ cache để hiển thị được cả khi đang offline.
    if (sess.snapshot) {
      queueMicrotask(() => {
        if (cancelled) return;
        setPlacedOrder(sess.snapshot);
        setPaymentExpiresAt(exp || null);
        setNowMs(Date.now());
      });
    }

    // Đối chiếu với server: paid -> trang thành công; cancelled -> dọn; còn chờ -> tiếp tục.
    (async () => {
      const fresh = await checkSepayPayment(sess.orderId);
      if (cancelled || !fresh) return; // offline hoặc lỗi: giữ theo cache đã khôi phục
      if (fresh.payment_status === "paid") {
        setOrderPaid(true);
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
  // Phí gốc hiển thị ở ô phương thức vận chuyển và ở dòng "Phí vận chuyển":
  // không trừ voucher, phần giảm tách riêng thành một dòng cho dễ đối chiếu.
  const shippingOriginal = items.length
    ? shippingQuote?.shipping_fee_original ?? shippingQuote?.shipping_fee ?? 0
    : 0;
  const shippingDiscount = items.length ? shippingQuote?.shipping_discount ?? 0 : 0;
  const shipping = items.length ? shippingQuote?.shipping_fee ?? 0 : 0;

  useEffect(() => {
    if (!selectedAddressId || !shippingMethod?.code || !items.length || items.some((line) => !line.variantId)) {
      return;
    }
    let active = true;
    queueMicrotask(() => {
      if (!active) return;
      setShippingQuoteLoading(true);
      setShippingQuote(null);
      setShippingQuoteError("");
      getShippingQuote({ address_id: selectedAddressId, shipping_method_code: shippingMethod.code, shipping_voucher_id: appliedShippingVoucher?.id, items: items.map((line) => ({ variant_id: line.variantId, quantity: line.quantity })) })
        .then((result) => {
          if (!active) return;
          if (result.ok && result.data?.quote) {
            setShippingQuote(result.data.quote);
            return;
          }
          const detail = Object.values(result.errors ?? {}).flat().find(Boolean);
          setShippingQuoteError(detail || result.message || "Chưa thể tính phí vận chuyển.");
        })
        .finally(() => { if (active) setShippingQuoteLoading(false); });
    });
    return () => { active = false; };
  }, [selectedAddressId, shippingMethod?.code, items, appliedShippingVoucher?.id]);

  const voucherKey = buyNowItem ? "petworld_buynow_applied_voucher" : "petworld_cart_applied_voucher";

  const activeVoucher = appliedVoucher || automaticVoucher;
  const discount = activeVoucher ? Math.min(parseFloat(activeVoucher.discount_value), subtotal) : 0;
  const total = Math.max(0, subtotal + shipping - discount);

  useEffect(() => {
    let active = true;
    getAutomaticProductVoucher(subtotal).then((voucher) => {
      if (active) setAutomaticVoucher(voucher);
    });
    return () => { active = false; };
  }, [subtotal]);

  useEffect(() => {
    let active = true;
    getEligibleShippingPromotions(subtotal).then((promotions) => {
      if (active) setEligibleShippingPromotions(promotions || []);
    });
    return () => { active = false; };
  }, [subtotal]);

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

  // `focusType` ('product' | 'shipping') quyết định nhóm nào được làm nổi khi
  // mở modal, tùy người dùng bấm vào ô nào.
  const handleOpenVoucherModal = async (focusType = "product") => {
    setVoucherFocusType(focusType);
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
    if (!voucher.can_apply || !voucher.selectable) return;
    if (voucher.applies_to === "shipping") {
      setAppliedShippingVoucher(voucher);
    } else {
      setAppliedVoucher(voucher);
      if (typeof window !== "undefined") {
        localStorage.setItem(voucherKey, JSON.stringify(voucher));
      }
    }
    setVoucherCodeInput("");
    setShowVoucherModal(false);
  };

  const handleApplyVoucherCode = () => {
    const code = voucherCodeInput.trim().toUpperCase();
    const voucher = vouchers.find((item) => item.code.toUpperCase() === code);
    if (!voucher) {
      toastError("Mã voucher không tồn tại hoặc đã hết hiệu lực.");
      return;
    }
    if (!voucher.can_apply || !voucher.selectable) {
      toastError(voucher.availability_message || "Mã voucher chưa đủ điều kiện sử dụng.");
      return;
    }
    handleApplyVoucher(voucher);
  };

  const handleRemoveVoucher = () => {
    setAppliedVoucher(null);
    if (typeof window !== "undefined") {
      localStorage.removeItem(voucherKey);
    }
  };

  const handleRemoveShippingVoucher = () => setAppliedShippingVoucher(null);

  // Chia mã thành 2 nhóm; nhóm người dùng vừa bấm được đưa lên đầu danh sách.
  const voucherGroups = (() => {
    const groups = [
      {
        type: "product",
        label: "Mã giảm giá sản phẩm",
        icon: "🎟",
        items: vouchers.filter((v) => v.applies_to !== "shipping"),
      },
      {
        type: "shipping",
        label: "Mã miễn phí vận chuyển",
        icon: "🚚",
        items: vouchers.filter((v) => v.applies_to === "shipping"),
      },
    ];
    return voucherFocusType === "shipping" ? [groups[1], groups[0]] : groups;
  })();

  const selectedPayment = options.payment_methods.find((m) => m.id === paymentMethodId);
  const isBankTransfer = (selectedPayment?.name ?? "").toLowerCase().includes("chuyển khoản");

  const updateAddressField = (field) => (event) => {
    const value = event.target.type === "checkbox" ? event.target.checked : event.target.value;
    setAddressForm({ ...addressForm, [field]: value });
  };

  const handleSaveAddress = async () => {
    const { recipient_name, recipient_phone, address_line, ward, province } = addressForm;
    if (!recipient_name || !recipient_phone || !address_line || !ward || !province) {
      toastError("Vui lòng chọn đầy đủ Tỉnh/Thành, Quận/Huyện và Phường/Xã trước khi lưu địa chỉ.");
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

    if (!shippingMethodId || shippingQuoteLoading || !shippingQuote) {
      toastError(shippingQuoteError || "Vui lòng chờ tính xong phí vận chuyển trước khi đặt hàng.");
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
      shipping_voucher_id: appliedShippingVoucher?.id,
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
      if (result.errors && result.errors.shipping_voucher_id) {
        setAppliedShippingVoucher(null);
      }
      return;
    }

    if (buyNowItem) {
      clearBuyNow();
    } else {
      clearCart();
    }
    setAppliedVoucher(null);
    setAppliedShippingVoucher(null);
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
      // Giữ lại đúng các dòng vừa thanh toán để trả về giỏ nếu khách hủy giữa chừng.
      savePendingPayment({ orderId: result.data.id, snapshot, expiresAt: exp, paid: false, cartLines: items });
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

  /**
   * Khách chọn "Rời khỏi & hủy đơn" trong hộp cảnh báo.
   *
   * Gọi thẳng API hủy thay vì kiểm tra thanh toán trước: server đã khóa dòng đơn
   * trong transaction và từ chối đơn đã trả tiền, nên vừa tránh khe hở giữa hai
   * lần gọi vừa bớt được một vòng request (server dev chỉ chạy đơn tiến trình).
   */
  const handleConfirmLeave = async () => {
    if (!placedOrder) return { ok: true };

    setCancelling(true);
    try {
      const res = await cancelOrder(placedOrder.id);
      if (res.ok) {
        restoreCancelledOrderToCart();
        return { ok: true };
      }

      // Hủy bị từ chối: xem trong DB đơn đã được đánh dấu trả tiền chưa. Đọc DB là
      // đủ vì server chỉ từ chối hủy khi đã ghi nhận thanh toán.
      const fresh = await getPaymentStatus(placedOrder.id);
      if (fresh?.payment_status === "paid") {
        setOrderPaid(true);
        clearPendingPayment();
        return { ok: true, paid: true };
      }

      // Đơn đã bị hủy sẵn (hết hạn) thì coi như xong, chỉ dọn phiên rồi cho đi.
      if (fresh?.status === "cancelled") {
        restoreCancelledOrderToCart();
        return { ok: true };
      }

      return { ok: false, message: res.message };
    } finally {
      setCancelling(false);
    }
  };

  /**
   * Sau khi đơn bị hủy: đưa lại hàng vào giỏ để khách mua tiếp không phải chọn lại,
   * rồi dọn phiên thanh toán đang chờ.
   */
  const restoreCancelledOrderToCart = () => {
    const sess = readPendingPayment();
    restoreToCart(sess?.cartLines ?? []);
    clearPendingPayment();
    setPlacedOrder(null);
    setPaymentExpiresAt(null);
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

  // Đơn COD hoặc đơn chuyển khoản đã thanh toán thành công -> màn thành công.
  // Đơn chuyển khoản chưa trả tiền KHÔNG đổi màn: QR mở dạng modal đè lên form checkout.
  if (placedOrder && (!placedOrder.is_bank || orderPaid)) {
    return <CheckoutSuccessView order={fullOrderDetails || placedOrder} />;
  }

  const awaitingPayment = Boolean(placedOrder?.is_bank) && !orderPaid;

  // Modal chờ thanh toán: dựng sẵn để chèn vào mọi nhánh render bên dưới.
  const paymentModal = awaitingPayment ? (
    <>
      <PaymentLeaveGuard
        active={!qrExpired}
        minutesLeft={Math.max(1, Math.ceil(qrSecondsLeft / 60))}
        onConfirmLeave={handleConfirmLeave}
      />
      <PaymentQrModal
        order={placedOrder}
        qrUrl={buildSepayQrUrl(placedOrder.payment_code, placedOrder.total_amount)}
        bankInfo={BANK_INFO}
        secondsLeft={qrSecondsLeft}
        expired={qrExpired}
        paid={orderPaid}
        onRegenerate={handleRegenerateQr}
        onCancelOrder={handleConfirmLeave}
        onCopy={copyText}
      />
    </>
  ) : null;

  if (!user) {
    return (
      <div className="cart-empty">
        <p>Bạn cần đăng nhập để thanh toán và quản lý đơn hàng.</p>
        <Link href="/login" className="cart-empty-btn">Đăng nhập</Link>
      </div>
    );
  }

  // Đặt hàng xong thì giỏ đã được dọn, nên nhánh "giỏ trống" cũng phải giữ modal
  // chờ thanh toán — nếu không, modal sẽ biến mất ngay sau khi tạo đơn.
  if (items.length === 0) {
    return (
      <>
        {paymentModal}
        <div className="cart-empty">
          <p>Giỏ hàng của bạn đang trống, chưa thể thanh toán.</p>
          <Link href="/shop" className="cart-empty-btn">Tiếp tục mua sắm</Link>
        </div>
      </>
    );
  }

  return (
    <>
      {paymentModal}

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
                  <strong>{method.name}</strong>{method.description && <small>{method.description}</small>}
                </div>
                {/* Luôn hiện phí gốc, không trừ voucher — phần giảm nằm ở dòng
                    riêng trong tóm tắt đơn để người mua đối chiếu được. */}
                <span className="co-ship-fee">{shippingMethodId === method.id && shippingQuoteLoading ? "Đang tính..." : shippingMethodId === method.id && shippingQuote ? formatPrice(shippingQuote.shipping_fee_original ?? shippingQuote.shipping_fee) : shippingMethodId === method.id && shippingQuoteError ? "Chưa tính được" : method.fee_mode === "live_quote" ? "Theo địa chỉ" : "Chọn để tính"}</span>
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
          {shippingQuoteError && <p className="co-quote-error">{shippingQuoteError}</p>}
          {/* Giữ khối gợi ý ưu đãi vận chuyển. Dòng hiển thị số tiền giảm phí
              ship mà develop đặt ở đây đã được chuyển xuống cạnh dòng giảm giá
              sản phẩm, nên không lấy lại để tránh hiện trùng hai lần. */}
          {eligibleShippingPromotions.length > 0 && (
            <div className="co-auto-shipping-promotions">
              <div className="co-auto-shipping-promotions-title">🎁 Ưu đãi vận chuyển</div>
              {eligibleShippingPromotions.map((promotion) => (
                <div className="co-auto-shipping-promotion" key={promotion.id}>
                  <div>
                    <strong>{promotion.code}</strong>
                    <span>{promotion.description || `Hỗ trợ phí ship tối đa ${formatPrice(promotion.max_shipping_discount)}`}</span>
                  </div>
                  <em>Đủ điều kiện · sẽ tự áp dụng</em>
                </div>
              ))}
            </div>
          )}
          {items.length > 0 && shippingQuote && shipping === 0 && (
            <div className="co-freeship-badge">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.9-4.9 1.4 1.4-6.3 6.3Z" /></svg>
              Đã áp dụng miễn phí vận chuyển
            </div>
          )}

          {/* Mặc định chỉ một ô "Chọn mã giảm giá". Mỗi loại mã khi được áp sẽ
              hiện thành một dòng riêng (sản phẩm nền cam, vận chuyển nền xanh),
              nên áp cả hai thì mới thấy hai dòng. */}
          <div className="co-voucher-section">
            {/* --- Mã giảm giá sản phẩm --- */}
            {appliedVoucher ? (
              <div className="co-voucher-applied is-product">
                <button
                  type="button"
                  className="co-voucher-applied-info is-clickable"
                  onClick={() => handleOpenVoucherModal("product")}
                  title="Bấm để đổi mã giảm giá sản phẩm"
                >
                  <span className="co-voucher-tag-icon">🎟</span>
                  <span className="co-voucher-code-name"><strong>{appliedVoucher.code}</strong></span>
                  <span className="co-voucher-discount-text">(-{formatPrice(discount)})</span>
                </button>
                <button type="button" className="co-voucher-remove-btn" onClick={handleRemoveVoucher}>Gỡ</button>
              </div>
            ) : automaticVoucher ? (
              <div className="co-voucher-applied is-product">
                {/* Mã tự động cũng cho bấm, phòng khi có mã khác lợi hơn. */}
                <button
                  type="button"
                  className="co-voucher-applied-info is-clickable"
                  onClick={() => handleOpenVoucherModal("product")}
                  title="Bấm để chọn mã giảm giá khác"
                >
                  <span className="co-voucher-tag-icon">✨</span>
                  <span className="co-voucher-code-name"><strong>{automaticVoucher.code}</strong> · Tự động áp dụng</span>
                  <span className="co-voucher-discount-text">(-{formatPrice(discount)})</span>
                </button>
              </div>
            ) : null}

            {/* --- Mã miễn phí vận chuyển --- */}
            {appliedShippingVoucher && (
              <div className="co-voucher-applied is-shipping">
                <button
                  type="button"
                  className="co-voucher-applied-info is-clickable"
                  onClick={() => handleOpenVoucherModal("shipping")}
                  title="Bấm để đổi mã miễn phí vận chuyển"
                >
                  <span className="co-voucher-tag-icon">🚚</span>
                  <span className="co-voucher-code-name"><strong>{appliedShippingVoucher.code}</strong></span>
                  {shippingDiscount > 0 && (
                    <span className="co-voucher-discount-text">(-{formatPrice(shippingDiscount)})</span>
                  )}
                </button>
                <button type="button" className="co-voucher-remove-btn" onClick={handleRemoveShippingVoucher}>Gỡ</button>
              </div>
            )}

            {/* Một nút chung, chỉ còn hiện khi vẫn có loại mã chưa được áp. */}
            {(!appliedVoucher && !automaticVoucher) || !appliedShippingVoucher ? (
              <button
                type="button"
                className="co-voucher-select-trigger"
                onClick={() => handleOpenVoucherModal("product")}
              >
                <span className="co-voucher-tag-icon">🎟</span>
                <span>Chọn mã giảm giá</span>
                <span className="co-voucher-arrow">➔</span>
              </button>
            ) : null}
          </div>

          {/* Khối tính tiền: tổng phụ và phí ship đặt ngay trên các dòng giảm
              giá để người mua đọc liền mạch xuống tổng thanh toán. */}
          <div className="co-summary-row">
            <span>Tổng phụ</span>
            <span>{formatPrice(subtotal)}</span>
          </div>
          <div className="co-summary-row">
            <span>Phí vận chuyển</span>
            <span>{shippingQuoteLoading ? "Đang tính..." : shippingQuote ? formatPrice(shippingOriginal) : "Chưa tính"}</span>
          </div>

          {discount > 0 && (
            <div className="co-summary-row co-summary-discount">
              <span>
                Giảm giá sản phẩm
                {activeVoucher?.code && <em className="co-discount-code">{activeVoucher.code}</em>}
              </span>
              <span className="co-discount-amount">-{formatPrice(discount)}</span>
            </div>
          )}
          {shippingDiscount > 0 && (
            <div className="co-summary-row co-summary-discount">
              <span>
                Giảm phí vận chuyển
                {(appliedShippingVoucher?.code || shippingQuote?.shipping_promotion?.code) && (
                  <em className="co-discount-code">
                    {appliedShippingVoucher?.code || shippingQuote.shipping_promotion.code}
                  </em>
                )}
              </span>
              <span className="co-discount-amount">-{formatPrice(shippingDiscount)}</span>
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
              <div style={{ display: "flex", gap: 8, marginBottom: 16 }}>
                <input
                  type="text"
                  value={voucherCodeInput}
                  onChange={(event) => setVoucherCodeInput(event.target.value.toUpperCase())}
                  onKeyDown={(event) => { if (event.key === "Enter") { event.preventDefault(); handleApplyVoucherCode(); } }}
                  placeholder="Nhập mã voucher"
                  aria-label="Mã voucher"
                  style={{ flex: 1, minWidth: 0, height: 40, padding: "0 12px", border: "1px solid var(--border-color)", borderRadius: 8, font: "inherit" }}
                />
                <button type="button" className="co-voucher-btn active" onClick={handleApplyVoucherCode}>Áp dụng</button>
              </div>
              {loadingVouchers ? (
                <div className="co-loading-wrap">
                  <span className="co-spinner" aria-hidden="true"></span>
                  <p>Đang tải danh sách voucher...</p>
                </div>
              ) : vouchers.length === 0 ? (
                <p className="co-no-vouchers">Hiện tại không có voucher nào khả dụng.</p>
              ) : (
                <>
                  {voucherGroups.map((group) => (
                    <section
                      key={group.type}
                      className={`co-voucher-group is-${group.type} ${voucherFocusType === group.type ? "is-focused" : ""}`}
                    >
                      <h4 className="co-voucher-group-title">
                        <span className="co-voucher-tag-icon">{group.icon}</span>
                        {group.label}
                        <small>Chọn tối đa 1 mã</small>
                      </h4>
                      {group.items.length === 0 ? (
                        <p className="co-no-vouchers">Chưa có mã nào thuộc loại này.</p>
                      ) : (
                        <div className="co-voucher-list">
                          {group.items.map((voucher) => {
                            const formattedMin = formatPrice(voucher.min_order_value);
                            const formattedDiscount = formatPrice(voucher.discount_value);
                            const startDateStr = new Date(voucher.start_date).toLocaleDateString('vi-VN');
                            const endDateStr = new Date(voucher.end_date).toLocaleDateString('vi-VN');
                            const canSelect = voucher.selectable && voucher.can_apply;
                            // Mỗi nhóm so với mã đang áp của chính nhóm đó.
                            const isApplied = group.type === "shipping"
                              ? appliedShippingVoucher?.id === voucher.id
                              : appliedVoucher?.id === voucher.id;

                            return (
                              <div
                                key={voucher.id}
                                className={`co-voucher-item is-${group.type} ${!canSelect ? 'disabled' : ''} ${isApplied ? 'selected' : ''}`}
                              >
                                <div className="co-voucher-card-left">
                                  <div className="co-voucher-discount-val">-{formattedDiscount}</div>
                                  <div className="co-voucher-badge-code">{voucher.code}</div>
                                </div>
                                <div className="co-voucher-card-right">
                                  <h4 className="co-voucher-title">{voucher.description || `Giảm ngay ${formattedDiscount}`}</h4>
                                  <p className="co-voucher-condition">Đơn tối thiểu: <strong>{formattedMin}</strong></p>
                                  <p className="co-voucher-duration">Hạn dùng: {startDateStr} - {endDateStr}</p>

                                  {!canSelect && (
                                    <p className="co-voucher-warning-text">
                                      {voucher.availability_message}{voucher.selectable && voucher.missing_amount > 0 ? <> Mua thêm <strong>{formatPrice(voucher.missing_amount)}</strong> để dùng mã này.</> : null}
                                    </p>
                                  )}

                                  <div className="co-voucher-action-btn-wrap">
                                    {canSelect ? (
                                      isApplied ? (
                                        <button
                                          type="button"
                                          className="co-voucher-btn selected"
                                          onClick={group.type === "shipping" ? handleRemoveShippingVoucher : handleRemoveVoucher}
                                        >
                                          Đang chọn
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
                                        {voucher.is_automatic ? "Tự động áp dụng" : "Chưa đủ điều kiện"}
                                      </button>
                                    )}
                                  </div>
                                </div>
                              </div>
                            );
                          })}
                        </div>
                      )}
                    </section>
                  ))}
                </>
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
