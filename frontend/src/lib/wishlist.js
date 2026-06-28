// Danh sách yêu thích lưu phía client bằng localStorage (backend chưa có API wishlist).
// Lưu nguyên object product để trang Yêu thích render lại bằng ProductCard.

const STORAGE_KEY = "petworld_wishlist";
const WISHLIST_EVENT = "petworld:wishlist-updated";

export function getWishlist() {
  if (typeof window === "undefined") return [];
  return parseWishlist(localStorage.getItem(STORAGE_KEY) || "[]");
}

// Snapshot dạng chuỗi (ổn định) cho useSyncExternalStore; component tự parse.
export function getWishlistSnapshot() {
  if (typeof window === "undefined") return "[]";
  return localStorage.getItem(STORAGE_KEY) || "[]";
}

export function getServerWishlistSnapshot() {
  return "[]";
}

export function parseWishlist(raw) {
  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function saveWishlist(items) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  window.dispatchEvent(new CustomEvent(WISHLIST_EVENT));
}

export function isWishlisted(productId) {
  return getWishlist().some((item) => item.id === productId);
}

/**
 * Bật/tắt yêu thích cho một sản phẩm; trả về true nếu sau thao tác đang được yêu thích.
 */
export function toggleWishlist(product) {
  const items = getWishlist();
  const exists = items.some((item) => item.id === product.id);
  const next = exists ? items.filter((item) => item.id !== product.id) : [...items, product];
  saveWishlist(next);
  return !exists;
}

export function removeFromWishlist(productId) {
  saveWishlist(getWishlist().filter((item) => item.id !== productId));
}

export function clearWishlist() {
  saveWishlist([]);
}

// Cho component đăng ký lắng nghe thay đổi (trả về hàm huỷ đăng ký).
export function onWishlistChange(callback) {
  if (typeof window === "undefined") return () => {};
  window.addEventListener(WISHLIST_EVENT, callback);
  window.addEventListener("storage", callback);
  return () => {
    window.removeEventListener(WISHLIST_EVENT, callback);
    window.removeEventListener("storage", callback);
  };
}
