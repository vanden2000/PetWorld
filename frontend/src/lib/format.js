// Ảnh dùng tạm khi sản phẩm/danh mục chưa có ảnh thật.
const ASSET_BASE_URL = (
  process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
).replace(/\/$/, "");
const FALLBACK_PATH = "Special_Offer_1-removebg-preview.png";

export function resolveBackendImage(path) {
  if (!path) return FALLBACK_PATH;

  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  const normalized = path.replace(/^\/+/, "");

  if (normalized.startsWith("storage/")) {
    return `${ASSET_BASE_URL}/${normalized}`;
  }

  return `${ASSET_BASE_URL}/storage/${normalized}`;
}

/**
 * Định dạng giá theo tiền Việt: 200000 -> "200.000đ".
 */
export function formatPrice(value) {
  if (value === null || value === undefined || value === "") return "";
  const number = typeof value === "number" ? value : Number(value);
  if (Number.isNaN(number)) return "";
  return `${new Intl.NumberFormat("vi-VN").format(number)}đ`;
}

/**
 * Chuẩn hoá đường dẫn ảnh trả về từ API.
 * - URL tuyệt đối (http/https) giữ nguyên.
 * - Đường dẫn bắt đầu bằng "/" giữ nguyên.
 * - Còn lại được đặt trong thư mục /image của public.
 */
export function resolveImage(path) {
  return resolveBackendImage(path, "banners");
}
// đường dẫn ảnh brands
export function resolveBrandImage(path) {
  return resolveBackendImage(path, "brands");
}
export function resolveProductImage(path) {
  return resolveBackendImage(path, "products");
}

// đường dẫn ảnh blogs
export function resolveBlogImage(path) {
  return resolveBackendImage(path, "blogs");
}
