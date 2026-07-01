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
    return json?.data ?? [];
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
    return json?.data ?? null;
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
  const base = process.env.NEXT_PUBLIC_SEPAY_QR_BASE;
  if (!base) return null;
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
