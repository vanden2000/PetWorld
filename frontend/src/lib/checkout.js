// Lớp gọi API cho luồng đặt hàng: phương thức giao/thanh toán, sổ địa chỉ, tạo đơn.
// Các endpoint cần đăng nhập dùng Bearer token qua authHeaders().
import { API_BASE_URL } from "@/lib/api";
import { authHeaders } from "@/lib/auth";

const JSON_HEADERS = { "Content-Type": "application/json", Accept: "application/json" };

/**
 * Phương thức vận chuyển + thanh toán (public). Trả về { shipping_methods, payment_methods }.
 */
export async function getCheckoutOptions() {
  try {
    const res = await fetch(`${API_BASE_URL}/api/checkout-options`, { cache: "no-store" });
    if (!res.ok) throw new Error(`Checkout options API trả về ${res.status}`);
    const json = await res.json();
    return json?.data ?? { shipping_methods: [], payment_methods: [] };
  } catch (error) {
    console.error("[getCheckoutOptions] Không lấy được tuỳ chọn thanh toán:", error);
    return { shipping_methods: [], payment_methods: [] };
  }
}

/**
 * Danh sách địa chỉ giao hàng của user đang đăng nhập.
 */
export async function getAddresses() {
  try {
    const res = await fetch(`${API_BASE_URL}/api/addresses`, {
      cache: "no-store",
      headers: { Accept: "application/json", ...authHeaders() },
    });
    if (!res.ok) throw new Error(`Addresses API trả về ${res.status}`);
    const json = await res.json();
    return json?.data?.addresses ?? [];
  } catch (error) {
    console.error("[getAddresses] Không lấy được địa chỉ:", error);
    return [];
  }
}

/**
 * Thêm địa chỉ mới. Trả về { ok, data?, message?, errors? }.
 */
export async function createAddress(payload) {
  return postJson("/api/addresses", payload);
}

/**
 * Tạo đơn hàng. Trả về { ok, data?, message?, errors? }.
 */
export async function createOrder(payload) {
  return postJson("/api/orders", payload);
}

export async function getShippingQuote(payload) {
  return postJson("/api/shipping/quote", payload);
}

/**
 * Lấy danh sách voucher khả dụng kèm theo trạng thái áp dụng dựa trên subtotal.
 */
export async function getAvailableVouchers(subtotal) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/vouchers?subtotal=${subtotal}`, {
      cache: "no-store",
      headers: { Accept: "application/json", ...authHeaders() },
    });
    if (!res.ok) throw new Error(`Vouchers API trả về ${res.status}`);
    const json = await res.json();
    return json?.data?.vouchers ?? [];
  } catch (error) {
    console.error("[getAvailableVouchers] Không lấy được danh sách voucher:", error);
    return [];
  }
}

export async function getAutomaticProductVoucher(subtotal) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/vouchers?subtotal=${subtotal}`, {
      cache: "no-store",
      headers: { Accept: "application/json", ...authHeaders() },
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json?.data?.automatic_voucher ?? null;
  } catch {
    return null;
  }
}

export async function getEligibleShippingPromotions(subtotal) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/vouchers?subtotal=${subtotal}`, {
      cache: "no-store",
      headers: { Accept: "application/json", ...authHeaders() },
    });
    if (!res.ok) return [];
    const json = await res.json();
    return json?.data?.eligible_shipping_promotions ?? [];
  } catch {
    return [];
  }
}

/**
 * Gia hạn thời gian thanh toán (tạo lại QR) cho đơn chuyển khoản còn đang chờ.
 * Giữ nguyên mã PW{id}, chỉ đẩy lại expires_at. Trả về { ok, data?, message? }.
 */
export async function renewPayment(id) {
  return postJson(`/api/orders/${id}/renew-payment`, {});
}

/**
 * Đọc nhanh trạng thái thanh toán từ DB (không gọi API SePay) — dùng cho vòng
 * poll dày. Webhook SePay là đường xác nhận chính nên trạng thái ở đây đã đủ mới.
 */
export async function getPaymentStatus(id) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/orders/${id}/payment-status`, {
      cache: "no-store",
      headers: { Accept: "application/json", ...authHeaders() },
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json?.data?.order ?? null;
  } catch {
    return null;
  }
}

/**
 * Đối soát với API SePay (chậm, 8-10 giây). Chỉ gọi thưa để vá trường hợp
 * webhook không tới, đừng dùng cho vòng poll dày.
 */
export async function checkSepayPayment(id) {
  const res = await postJson(`/api/orders/${id}/check-sepay-payment`, {});
  return res.ok ? res.data?.order ?? null : null;
}

/**
 * Lấy chi tiết một đơn của user (dùng để poll trạng thái thanh toán). Trả về data hoặc null.
 */
export async function getOrder(id) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/orders/${id}`, {
      cache: "no-store",
      headers: { Accept: "application/json", ...authHeaders() },
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json?.data?.order ?? null;
  } catch {
    return null;
  }
}

/**
 * Dựng URL ảnh VietQR từ base cấu hình (NEXT_PUBLIC_SEPAY_QR_BASE) + số tiền + nội dung CK.
 * Base là link QR lấy từ trang SePay, ví dụ:
 *   https://vietqr.app/img?bank=MBBank&acc=0865130622
 * Trả về null nếu chưa cấu hình base.
 */
export function buildSepayQrUrl(paymentCode, amount) {
  const base = process.env.NEXT_PUBLIC_SEPAY_QR_BASE
    || "https://vietqr.app/img?bank=MBBank&acc=0865130622&template=compact&showinfo=true&fullacc=true&holder=LE%20TRAN%20PHAT";
  const sep = base.includes("?") ? "&" : "?";
  return `${base}${sep}amount=${Math.round(amount)}&des=${encodeURIComponent(paymentCode)}`;
}

async function postJson(path, payload) {
  try {
    const res = await fetch(`${API_BASE_URL}${path}`, {
      method: "POST",
      headers: { ...JSON_HEADERS, ...authHeaders() },
      body: JSON.stringify(payload),
    });

    const json = await res.json().catch(() => ({}));

    if (!res.ok) {
      return {
        ok: false,
        message: json?.message ?? "Đã có lỗi xảy ra, vui lòng thử lại.",
        errors: json?.errors ?? {},
      };
    }

    return { ok: true, data: json?.data, message: json?.message };
  } catch {
    return { ok: false, message: "Không kết nối được máy chủ. Vui lòng kiểm tra API.", errors: {} };
  }
}
