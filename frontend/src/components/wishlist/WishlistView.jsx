"use client";

import Link from "next/link";
import { useMemo, useSyncExternalStore } from "react";
import ProductCard from "@/components/product/ProductCard";
import {
  getWishlistSnapshot,
  getServerWishlistSnapshot,
  parseWishlist,
  onWishlistChange,
} from "@/lib/wishlist";

export default function WishlistView() {
  const raw = useSyncExternalStore(onWishlistChange, getWishlistSnapshot, getServerWishlistSnapshot);
  const items = useMemo(() => parseWishlist(raw), [raw]);

  if (items.length === 0) {
    return (
      <div className="cart-empty">
        <p>Bạn chưa có sản phẩm yêu thích nào.</p>
        <Link href="/shop" className="cart-empty-btn">Khám phá sản phẩm</Link>
      </div>
    );
  }

  return (
    <>
      <p className="wishlist-count">{items.length} sản phẩm trong danh sách yêu thích</p>
      <div className="products-grid-4">
        {items.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    </>
  );
}
