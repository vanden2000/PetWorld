"use client";

import Link from "next/link";
import { useCallback, useEffect, useMemo, useState, useSyncExternalStore } from "react";
import ProductCard from "@/components/product/ProductCard";
import {
  getServerUserSnapshot,
  getToken,
  getUserSnapshot,
  onAuthChange,
} from "@/lib/auth";
import {
  getWishlistSnapshot,
  getServerWishlistSnapshot,
  parseWishlist,
  refreshWishlist,
  resetWishlist,
  onWishlistChange,
} from "@/lib/wishlist";

export default function WishlistView() {
  const raw = useSyncExternalStore(onWishlistChange, getWishlistSnapshot, getServerWishlistSnapshot);
  const userRaw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const items = useMemo(() => parseWishlist(raw), [raw]);
  const [loading, setLoading] = useState(true);
  const [requiresLogin, setRequiresLogin] = useState(false);
  const [error, setError] = useState("");

  const load = useCallback(async () => {
    // Giữ các cập nhật state sau một async boundary khi load được gọi từ effect.
    await Promise.resolve();

    if (!getToken()) {
      resetWishlist();
      setRequiresLogin(true);
      setError("");
      setLoading(false);
      return;
    }

    setLoading(true);
    setRequiresLogin(false);
    setError("");

    const result = await refreshWishlist({ force: true });
    setRequiresLogin(Boolean(result.requiresLogin));
    setError(result.ok || result.requiresLogin ? "" : result.message);
    setLoading(false);
  }, []);

  useEffect(() => {
    let cancelled = false;

    Promise.resolve().then(async () => {
      if (cancelled) return;

      setLoading(true);
      const result = await refreshWishlist({ force: true });

      if (cancelled) return;

      setRequiresLogin(Boolean(result.requiresLogin));
      setError(result.ok || result.requiresLogin ? "" : result.message);
      setLoading(false);
    });

    return () => {
      cancelled = true;
    };
  }, [userRaw]);

  if (loading) {
    return <div className="cart-empty"><p>Đang tải danh sách yêu thích...</p></div>;
  }

  if (requiresLogin) {
    return (
      <div className="cart-empty">
        <p>Vui lòng đăng nhập để xem danh sách yêu thích.</p>
        <Link href="/login" className="cart-empty-btn">Đăng nhập</Link>
      </div>
    );
  }

  if (error) {
    return (
      <div className="cart-empty">
        <p>{error}</p>
        <button type="button" className="cart-empty-btn" onClick={() => void load()}>Thử lại</button>
      </div>
    );
  }

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
