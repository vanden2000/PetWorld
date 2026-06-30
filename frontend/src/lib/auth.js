import { API_BASE_URL } from "@/lib/api";

// Lưu token + thông tin user phía client; đọc reactive qua useSyncExternalStore.
const TOKEN_KEY = "petworld_token";
const USER_KEY = "petworld_user";
const AUTH_EVENT = "petworld:auth-updated";

export function getToken() {
  if (typeof window === "undefined") return null;
  return localStorage.getItem(TOKEN_KEY);
}

// Snapshot user (chuỗi ổn định) cho useSyncExternalStore.
export function getUserSnapshot() {
  if (typeof window === "undefined") return "null";
  return localStorage.getItem(USER_KEY) || "null";
}

export function getServerUserSnapshot() {
  return "null";
}

export function parseUser(raw) {
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

export function getUser() {
  return parseUser(getUserSnapshot());
}

function saveAuth(user, token) {
  localStorage.setItem(TOKEN_KEY, token);
  localStorage.setItem(USER_KEY, JSON.stringify(user));
  window.dispatchEvent(new CustomEvent(AUTH_EVENT));
}

export function clearAuth() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  window.dispatchEvent(new CustomEvent(AUTH_EVENT));
}

// Header Authorization cho các request cần đăng nhập.
export function authHeaders() {
  const token = getToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
}

// Gọi API auth, chuẩn hoá kết quả thành { ok, user?, message?, errors? }.
async function postAuth(path, payload) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/${path}`, {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
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

    const { user, token } = json.data;
    saveAuth(user, token);
    return { ok: true, user };
  } catch {
    return { ok: false, message: "Không kết nối được máy chủ. Vui lòng kiểm tra API.", errors: {} };
  }
}

export function register(payload) {
  return postAuth("register", payload);
}

export function login(payload) {
  return postAuth("login", payload);
}

export async function logout() {
  const token = getToken();
  if (token) {
    // Thu hồi token phía server; bỏ qua lỗi mạng để vẫn đăng xuất phía client.
    try {
      await fetch(`${API_BASE_URL}/api/logout`, {
        method: "POST",
        headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
      });
    } catch {
      /* noop */
    }
  }
  clearAuth();
}

export function onAuthChange(callback) {
  if (typeof window === "undefined") return () => {};
  window.addEventListener(AUTH_EVENT, callback);
  window.addEventListener("storage", callback);
  return () => {
    window.removeEventListener(AUTH_EVENT, callback);
    window.removeEventListener("storage", callback);
  };
}
