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
export function checkQuantity(productId, variantId, quantity, stockQuantity) {
  const key = lineKey(productId, variantId);
  const existing = getCart().find((line) => line.key === key);
  const currentQuantity = Math.max(0, Number(existing?.quantity) || 0);
  const requestedQuantity = Math.max(1, Math.trunc(Number(quantity) || 1));
  const maxQuantity = Math.max(0, Math.trunc(Number(stockQuantity) || 0));

  return currentQuantity + requestedQuantity <= maxQuantity;
}

export function addToCart(item, quantity = 1) {
  const items = getCart();
  const key = lineKey(item.productId, item.variantId);
  const existing = items.find((line) => line.key === key);
  const qtyNum = Math.max(1, Math.trunc(Number(quantity) || 1));

  if (Number.isFinite(Number(item.stockQuantity))
      && !checkQuantity(item.productId, item.variantId, qtyNum, item.stockQuantity)) {
    return false;
  }

  if (existing) {
    existing.quantity += qtyNum;
    existing.stockQuantity = item.stockQuantity;
  } else {
    items.push({ ...item, key, quantity: qtyNum });
  }

  saveCart(items);
  return true;
}

/**
 * Trả các dòng đã thanh toán dở về giỏ (khách hủy đơn chuyển khoản giữa chừng).
 * Giỏ đã bị dọn lúc đặt đơn nên thường là rỗng, nhưng vẫn cộng dồn phòng khi
 * khách đã thêm hàng mới, và không vượt quá tồn kho đã biết.
 * Trả về số dòng khôi phục được.
 */
export function restoreToCart(lines) {
  if (typeof window === "undefined") return 0;
  if (!Array.isArray(lines) || lines.length === 0) return 0;

  const items = getCart();
  let restored = 0;

  lines.forEach((line) => {
    if (!line?.productId) return;

    const key = line.key || lineKey(line.productId, line.variantId);
    const qtyNum = Math.max(1, Math.trunc(Number(line.quantity) || 1));
    const stockQuantity = Number(line.stockQuantity);
    const capped = (value) => Number.isFinite(stockQuantity)
      ? Math.min(value, Math.max(1, Math.trunc(stockQuantity)))
      : value;

    const existing = items.find((item) => item.key === key);
    if (existing) {
      existing.quantity = capped(Math.max(1, Number(existing.quantity) || 0) + qtyNum);
    } else {
      items.push({ ...line, key, quantity: capped(qtyNum) });
    }
    restored += 1;
  });

  if (restored > 0) saveCart(items);
  return restored;
}

export function updateQuantity(key, quantity) {
  const qtyNum = Math.trunc(Number(quantity) || 0);
  const items = getCart()
    .map((line) => {
      if (line.key !== key) return line;
      const stockQuantity = Number(line.stockQuantity);
      const nextQuantity = Number.isFinite(stockQuantity)
        ? Math.min(qtyNum, Math.max(0, stockQuantity))
        : qtyNum;
      return { ...line, quantity: nextQuantity };
    })
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
