"use client";

import { useEffect, useRef, useState, useSyncExternalStore } from "react";
import { getToken } from "@/lib/auth";
import { toastError, toastInfo, toastSuccess } from "@/lib/toast";
import {
  addToWishlist,
  getWishlistSnapshot,
  getServerWishlistSnapshot,
  parseWishlist,
  refreshWishlist,
  removeFromWishlist,
  onWishlistChange,
} from "@/lib/wishlist";

/**
 * Nút trái tim "Yêu thích" trên mỗi sản phẩm.
 * Trạng thái đọc từ localStorage qua external store để đồng bộ giữa các nút và badge Header.
 */
export default function WishlistButton({ product, className = "product-wishlist-btn" }) {
  const raw = useSyncExternalStore(onWishlistChange, getWishlistSnapshot, getServerWishlistSnapshot);
  const active = parseWishlist(raw).some((item) => item.id === product.id);
  const [loading, setLoading] = useState(false);
  const pendingRef = useRef(false);

  useEffect(() => {
    if (getToken()) void refreshWishlist();
  }, []);

  const handleClick = async (event) => {
    // Tránh kích hoạt link bao quanh card khi bấm nút.
    event.preventDefault();
    event.stopPropagation();

    if (pendingRef.current) return;

    if (!getToken()) {
      toastInfo("Vui lòng đăng nhập để yêu thích sản phẩm.");
      return;
    }

    pendingRef.current = true;
    setLoading(true);

    try {
      const result = active
        ? await removeFromWishlist(product)
        : await addToWishlist(product);

      if (!result.ok) {
        if (result.requiresLogin) toastInfo(result.message);
        else toastError(result.message);
        return;
      }

      toastSuccess(
        active
          ? `Đã xóa "${product.name}" khỏi danh sách yêu thích.`
          : `Đã thêm "${product.name}" vào danh sách yêu thích.`,
      );
    } finally {
      pendingRef.current = false;
      setLoading(false);
    }
  };

  return (
    <button
      type="button"
      className={`${className} ${active ? "active" : ""}`}
      aria-label={loading ? "Đang cập nhật yêu thích" : active ? "Bỏ khỏi yêu thích" : "Thêm vào yêu thích"}
      aria-pressed={active}
      aria-busy={loading}
      disabled={loading}
      onClick={handleClick}
    >
      {loading ? (
        <span className="wishlist-spinner" aria-hidden="true" />
      ) : (
        <svg width="16" height="16" viewBox="0 0 24 24" fill={active ? "currentColor" : "none"} stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
        </svg>
      )}
    </button>
  );
}
