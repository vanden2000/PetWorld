import { API_BASE_URL } from "@/lib/api";
import { authHeaders, clearAuth, getToken } from "@/lib/auth";

// Backend là nguồn dữ liệu wishlist duy nhất. Store trong bộ nhớ giúp các nút tim,
// badge Header và trang /wishlist cập nhật cùng lúc qua useSyncExternalStore.
const LEGACY_STORAGE_KEY = "petworld_wishlist";
const EMPTY_SNAPSHOT = "[]";
const listeners = new Set();

let items = [];
let snapshot = EMPTY_SNAPSHOT;
let loadedToken = null;
let pendingLoad = null;
let pendingToken = null;

function emit() {
  for (const listener of listeners) listener();
}

function publish(nextItems) {
  items = Array.isArray(nextItems) ? nextItems : [];
  snapshot = JSON.stringify(items);
  emit();
}

function removeLegacyWishlist() {
  if (typeof window !== "undefined") {
    localStorage.removeItem(LEGACY_STORAGE_KEY);
  }
}

async function jsonResponse(response) {
  return response.json().catch(() => ({}));
}

function unauthenticatedResult(message = "Vui lòng đăng nhập để yêu thích sản phẩm.") {
  return { ok: false, requiresLogin: true, message };
}

function handleUnauthenticated(json) {
  clearAuth();
  resetWishlist();

  return unauthenticatedResult(
    json?.message === "Bạn chưa đăng nhập."
      ? "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại."
      : json?.message,
  );
}

export function getWishlist() {
  return items;
}

export function getWishlistSnapshot() {
  return snapshot;
}

export function getServerWishlistSnapshot() {
  return EMPTY_SNAPSHOT;
}

export function parseWishlist(raw) {
  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

export function isWishlisted(productId) {
  return items.some((item) => item.id === productId);
}

export function resetWishlist() {
  loadedToken = null;
  removeLegacyWishlist();

  if (snapshot !== EMPTY_SNAPSHOT) {
    publish([]);
  }
}

export async function refreshWishlist({ force = false } = {}) {
  const token = getToken();

  if (!token) {
    resetWishlist();
    return unauthenticatedResult();
  }

  removeLegacyWishlist();

  if (!force && loadedToken === token) {
    return { ok: true, items };
  }

  if (pendingLoad && pendingToken === token) {
    return pendingLoad;
  }

  pendingToken = token;
  pendingLoad = (async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/wishlist`, {
        headers: { Accept: "application/json", ...authHeaders() },
        cache: "no-store",
      });
      const json = await jsonResponse(response);

      if (response.status === 401) {
        return handleUnauthenticated(json);
      }

      if (!response.ok) {
        return {
          ok: false,
          message: json?.message ?? "Không thể tải danh sách yêu thích.",
        };
      }

      // Bỏ qua response cũ nếu user đã đổi tài khoản trong lúc request đang chạy.
      if (getToken() === token) {
        loadedToken = token;
        publish(json?.data ?? []);
      }

      return { ok: true, items: json?.data ?? [] };
    } catch {
      return {
        ok: false,
        message: "Không kết nối được máy chủ. Vui lòng thử lại.",
      };
    } finally {
      if (pendingToken === token) {
        pendingLoad = null;
        pendingToken = null;
      }
    }
  })();

  return pendingLoad;
}

async function updateWishlist(method, product) {
  const token = getToken();

  if (!token) {
    return unauthenticatedResult();
  }

  try {
    const response = await fetch(`${API_BASE_URL}/api/wishlist/${product.id}`, {
      method,
      headers: { Accept: "application/json", ...authHeaders() },
    });
    const json = await jsonResponse(response);

    if (response.status === 401) {
      return handleUnauthenticated(json);
    }

    if (!response.ok) {
      return {
        ok: false,
        message: json?.message ?? "Không thể cập nhật danh sách yêu thích.",
      };
    }

    loadedToken = token;
    publish(
      method === "POST"
        ? [...items.filter((item) => item.id !== product.id), product]
        : items.filter((item) => item.id !== product.id),
    );

    return { ok: true, isWishlisted: method === "POST" };
  } catch {
    return {
      ok: false,
      message: "Không kết nối được máy chủ. Vui lòng thử lại.",
    };
  }
}

export function addToWishlist(product) {
  return updateWishlist("POST", product);
}

export function removeFromWishlist(product) {
  return updateWishlist("DELETE", product);
}

export function onWishlistChange(callback) {
  listeners.add(callback);
  return () => listeners.delete(callback);
}
