// Ảnh dùng tạm khi sản phẩm/danh mục chưa có ảnh thật.
const FALLBACK_IMAGE = "/image/Special_Offer_1-removebg-preview.png";

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
  if (!path) return FALLBACK_IMAGE;
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  if (path.startsWith("/")) return path;
  return `/image/banners/${path}`;
}
// đường dẫn ảnh brands
export function resolveBrandImage(path) {
  if (!path) return FALLBACK_IMAGE;
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  if (path.startsWith("/")) return path;
  return `/image/brands/${path}`;
}
export function resolveProductImage(path){
  if (!path) return FALLBACK_IMAGE;
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  if (path.startsWith("/")) return path;
  return `/image/products/${path}`;
}