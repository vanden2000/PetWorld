const ASSET_BASE_URL = (
  process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
).replace(/\/$/, "");

const FALLBACK_PATH = "Special_Offer_1-removebg-preview.png";

function toStorageUrl(path) {
  return `${ASSET_BASE_URL}/storage/${path}`;
}

export function resolveBackendImage(path, folder = "") {
  if (!path) return toStorageUrl(`logo/${FALLBACK_PATH}`);

  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  const normalized = path.replace(/^\/+/, "");
  const normalizedFolder = folder.replace(/^\/+|\/+$/g, "");

  if (normalized.startsWith("image/")) {
    return toStorageUrl(normalized.substring("image/".length));
  }

  if (normalized.startsWith("storage/")) {
    return toStorageUrl(normalized.substring("storage/".length));
  }

  if (normalized.startsWith("uploads/")) {
    return `${ASSET_BASE_URL}/${normalized}`;
  }

  if (normalizedFolder) {
    if (normalized === normalizedFolder || normalized.startsWith(`${normalizedFolder}/`)) {
      return toStorageUrl(normalized);
    }

    return toStorageUrl(`${normalizedFolder}/${normalized}`);
  }

  return toStorageUrl(normalized);
}

export function formatPrice(value) {
  if (value === null || value === undefined || value === "") return "";
  const number = typeof value === "number" ? value : Number(value);
  if (Number.isNaN(number)) return "";
  return `${new Intl.NumberFormat("vi-VN").format(number)}\u0111`;
}

export function resolveImage(path) {
  return resolveBackendImage(path, "banners");
}

export function resolveCategoryImage(path) {
  return resolveBackendImage(path, "categories");
}

export function useImageFallback(event) {
  event.currentTarget.onerror = null;
  event.currentTarget.src = resolveBackendImage(null);
}

export function resolveBrandImage(path) {
  return resolveBackendImage(path, "brands");
}

export function resolveProductImage(path) {
  return resolveBackendImage(path, "products");
}

export function resolveBlogImage(path) {
  return resolveBackendImage(path, "blogs");
}
