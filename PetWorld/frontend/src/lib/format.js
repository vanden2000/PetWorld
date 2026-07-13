// Ảnh dùng tạm khi sản phẩm/danh mục chưa có ảnh thật.
const ASSET_BASE_URL = (
  process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
).replace(/\/$/, "");
const FALLBACK_PATH = "Special_Offer_1-removebg-preview.png";

export function resolveBackendImage(path) {
  if (!path) return `${ASSET_BASE_URL}/image/logo/${FALLBACK_PATH}`;

  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  const normalized = path.replace(/^\/+/, "");

  // Nếu đường dẫn chỉ định rõ bắt đầu bằng image/ hoặc storage/ hoặc uploads/
  if (
    normalized.startsWith("storage/") ||
    normalized.startsWith("image/") ||
    normalized.startsWith("products/") ||
    normalized.startsWith("uploads/")
  ) {
    // Trường hợp đặc biệt: Nếu trỏ tới storage/logo hoặc storage/avatars nhưng thực tế ở thư mục static /image
    if (normalized.startsWith("storage/logo/")) {
      return `${ASSET_BASE_URL}/image/${normalized.substring("storage/".length)}`;
    }
    if (normalized.startsWith("storage/avatars/")) {
      return `${ASSET_BASE_URL}/image/${normalized.substring("storage/".length)}`;
    }
    return `${ASSET_BASE_URL}/${normalized}`;
  }

  // Chuyển hướng các đường dẫn tĩnh logo và avatars sang thư mục /image/ tương ứng trên backend
  if (normalized.startsWith("logo/")) {
    return `${ASSET_BASE_URL}/image/${normalized}`;
  }
  if (normalized.startsWith("avatars/")) {
    return `${ASSET_BASE_URL}/image/${normalized}`;
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

// Ảnh danh mục mới nằm trên public disk của Laravel: /storage/categories/{tên-file}.
// Các tiền tố cũ vẫn được nhận diện để dữ liệu chưa migrate không làm vỡ giao diện.
export function resolveCategoryImage(path) {
  if (path?.startsWith("http://") || path?.startsWith("https://")) return path;
  if (!path) return resolveBackendImage(null);

  const normalized = path.replace(/^\/+/, "");
  const filename = normalized.split("/").pop();

  if (
    normalized.startsWith("storage/image/categories/") ||
    normalized.startsWith("image/categories/")
  ) {
    return `${ASSET_BASE_URL}/storage/categories/${filename}`;
  }

  if (normalized.startsWith("storage/") || normalized.startsWith("image/")) {
    return `${ASSET_BASE_URL}/${normalized}`;
  }

  const storagePath = normalized.startsWith("categories/")
    ? normalized
    : `categories/${normalized}`;

  return `${ASSET_BASE_URL}/storage/${storagePath}`;
}

// Khi ảnh lỗi, thay bằng logo cục bộ và vô hiệu handler để tránh lặp vô hạn.
export function useImageFallback(event) {
  event.currentTarget.onerror = null;
  event.currentTarget.src = resolveBackendImage(null);
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
