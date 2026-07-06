const STORAGE_KEY = "petworld_buy_now";
const BUY_NOW_EVENT = "petworld:buy-now-updated";

export function getBuyNowSnapshot() {
  if (typeof window === "undefined") return "null";
  return sessionStorage.getItem(STORAGE_KEY) || "null";
}

export function getServerBuyNowSnapshot() {
  return "null";
}

export function parseBuyNow(raw) {
  try {
    const item = JSON.parse(raw);
    return item && typeof item === "object" ? item : null;
  } catch {
    return null;
  }
}

export function setBuyNow(item) {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify(item));
  window.dispatchEvent(new CustomEvent(BUY_NOW_EVENT));
}

export function clearBuyNow() {
  sessionStorage.removeItem(STORAGE_KEY);
  window.dispatchEvent(new CustomEvent(BUY_NOW_EVENT));
}

export function onBuyNowChange(callback) {
  if (typeof window === "undefined") return () => {};
  window.addEventListener(BUY_NOW_EVENT, callback);
  return () => window.removeEventListener(BUY_NOW_EVENT, callback);
}
