"use client";

import { useSyncExternalStore } from "react";
import {
  getWishlistSnapshot,
  getServerWishlistSnapshot,
  parseWishlist,
  toggleWishlist,
  onWishlistChange,
} from "@/lib/wishlist";

/**
 * Nút trái tim "Yêu thích" trên mỗi sản phẩm.
 * Trạng thái đọc từ localStorage qua external store để đồng bộ giữa các nút và badge Header.
 */
export default function WishlistButton({ product, className = "product-wishlist-btn" }) {
  const raw = useSyncExternalStore(onWishlistChange, getWishlistSnapshot, getServerWishlistSnapshot);
  const active = parseWishlist(raw).some((item) => item.id === product.id);

  const handleClick = (event) => {
    // Tránh kích hoạt link bao quanh card khi bấm nút.
    event.preventDefault();
    event.stopPropagation();
    toggleWishlist(product);
  };

  return (
    <button
      type="button"
      className={`${className} ${active ? "active" : ""}`}
      aria-label={active ? "Bỏ khỏi yêu thích" : "Thêm vào yêu thích"}
      aria-pressed={active}
      onClick={handleClick}
    >
      <svg width="16" height="16" viewBox="0 0 24 24" fill={active ? "currentColor" : "none"} stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
      </svg>
    </button>
  );
}
