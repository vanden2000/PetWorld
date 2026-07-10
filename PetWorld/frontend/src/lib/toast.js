// Hệ thống thông báo (toast) đơn giản, không thư viện ngoài.
// Lưu state trong bộ nhớ + phát cho component qua useSyncExternalStore,
// cùng pattern external-store như lib/cart.js.

const EMPTY = [];
const listeners = new Set();

let toasts = EMPTY;
let counter = 0;

function emit() {
  for (const listener of listeners) listener();
}

export function subscribeToast(callback) {
  listeners.add(callback);
  return () => listeners.delete(callback);
}

// Trả về cùng tham chiếu khi không đổi để useSyncExternalStore không render thừa.
export function getToastSnapshot() {
  return toasts;
}

export function getServerToastSnapshot() {
  return EMPTY;
}

export function dismissToast(id) {
  toasts = toasts.filter((toast) => toast.id !== id);
  emit();
}

/**
 * Hiện một thông báo.
 * @param {string} message Nội dung hiển thị.
 * @param {"success"|"error"|"info"} type Loại thông báo (đổi màu/icon).
 * @param {number} duration Thời gian tự ẩn (ms); 0 = không tự ẩn.
 */
export function notify(message, type = "success", duration = 3000) {
  const id = ++counter;
  toasts = [...toasts, { id, message, type }];
  emit();

  if (duration > 0) {
    setTimeout(() => dismissToast(id), duration);
  }

  return id;
}

export const toastSuccess = (message, duration) => notify(message, "success", duration);
export const toastError = (message, duration) => notify(message, "error", duration);
export const toastInfo = (message, duration) => notify(message, "info", duration);
