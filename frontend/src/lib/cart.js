// Giỏ hàng lưu phía client bằng localStorage (backend chưa có API giỏ hàng).
// Mỗi dòng giỏ hàng được định danh bằng cặp sản phẩm + biến thể.

const STORAGE_KEY = "petworld_cart";
const CART_EVENT = "petworld:cart-updated";

// Khoá duy nhất cho một dòng giỏ hàng (cùng SP nhưng khác biến thể là 2 dòng).
export function lineKey(productId, variantId) {
  return `${productId}:${variantId ?? "default"}`;
}

export function getCart() {
  if (typeof window === "undefined") return [];
  try {
    const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
    return Array.isArray(stored) ? stored : [];
  } catch {
    return [];
  }
}

// Snapshot dạng chuỗi (ổn định) cho useSyncExternalStore; component tự parse.
export function getCartSnapshot() {
  if (typeof window === "undefined") return "[]";
  return localStorage.getItem(STORAGE_KEY) || "[]";
}

export function getServerCartSnapshot() {
  return "[]";
}

export function parseCart(raw) {
  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function saveCart(items) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  // Phát sự kiện để badge trên Header và các trang khác cập nhật ngay.
  window.dispatchEvent(new CustomEvent(CART_EVENT));
}

/**
 * Thêm một dòng vào giỏ; nếu đã có cùng SP + biến thể thì cộng dồn số lượng.
 */
export function addToCart(item, quantity = 1) {
  const items = getCart();
  const key = lineKey(item.productId, item.variantId);
  const existing = items.find((line) => line.key === key);

  if (existing) {
    existing.quantity += quantity;
  } else {
    items.push({ ...item, key, quantity });
  }

  saveCart(items);
}

export function updateQuantity(key, quantity) {
  const items = getCart()
    .map((line) => (line.key === key ? { ...line, quantity } : line))
    .filter((line) => line.quantity > 0);
  saveCart(items);
}

export function removeFromCart(key) {
  saveCart(getCart().filter((line) => line.key !== key));
}

export function clearCart() {
  saveCart([]);
}

export function cartCount() {
  return getCart().reduce((sum, line) => sum + line.quantity, 0);
}

export function cartSubtotal() {
  return getCart().reduce((sum, line) => sum + line.price * line.quantity, 0);
}

// Cho component đăng ký lắng nghe thay đổi giỏ (trả về hàm huỷ đăng ký).
export function onCartChange(callback) {
  if (typeof window === "undefined") return () => {};
  window.addEventListener(CART_EVENT, callback);
  window.addEventListener("storage", callback);
  return () => {
    window.removeEventListener(CART_EVENT, callback);
    window.removeEventListener("storage", callback);
  };
}
