"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { API_BASE_URL } from "@/lib/api";
import { resolveProductImage } from "@/lib/format";

/**
 * Hàng "Đã xem gần đây": nhãn + dải thumbnail tròn của sản phẩm.
 */
export default function RecentlyViewed() {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    let slugs = [];

    try {
      const stored = JSON.parse(localStorage.getItem("recent_product_slugs") || "[]");
      if (Array.isArray(stored)) slugs = stored;
    } catch {
      localStorage.removeItem("recent_product_slugs");
    }

    if (!Array.isArray(slugs) || slugs.length === 0) return;

    const controller = new AbortController();

    fetch(`${API_BASE_URL}/api/products/recent?slugs=${encodeURIComponent(slugs.join(","))}`, {
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) throw new Error(`Recent products API returned ${response.status}`);
        return response.json();
      })
      .then((json) => setProducts(json?.data ?? []))
      .catch((error) => {
        if (error.name !== "AbortError") console.error("Cannot load recently viewed products:", error);
      });

    return () => controller.abort();
  }, []);

  if (products.length === 0) return null;

  return (
    <div className="recent-viewed-row">
      <span className="recent-viewed-label">Đã xem gần đây</span>
      <div className="recent-viewed-list">
        {products.slice(0, 12).map((product) => (
          <Link
            key={product.id}
            href={`/shop/${product.slug}`}
            title={product.name}
            className="recent-viewed-thumb-link"
          >
            <img
              src={resolveProductImage(product.image)}
              alt={product.name}
              className="recent-viewed-thumb"
            />
          </Link>
        ))}
      </div>
    </div>
  );
}
