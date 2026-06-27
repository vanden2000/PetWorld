"use client";

import { useState } from "react";
import ProductCard from "@/components/product/ProductCard";

/**
 * Khối sản phẩm bán chạy với tab lọc theo danh mục.
 * `groups` = mảng products_by_categories từ API ({ category, products }).
 */
export default function BestSellingTabs({ groups = [] }) {
  const tabs = groups.filter((group) => group.products?.length > 0);
  const [activeIndex, setActiveIndex] = useState(0);

  if (tabs.length === 0) return null;

  const current = tabs[activeIndex] ?? tabs[0];

  return (
    <section className="homepage-section">
      <div className="best-selling-header">
        <h2 className="section-title">Sản Phẩm Bán Chạy</h2>
        <div className="best-selling-tabs">
          {tabs.map((group, index) => (
            <button
              key={group.category.id}
              type="button"
              className={`tab-btn ${index === activeIndex ? "active" : ""}`}
              onClick={() => setActiveIndex(index)}
            >
              {group.category.name}
            </button>
          ))}
        </div>
      </div>

      <div className="products-grid-5">
        {current.products.slice(0, 5).map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    </section>
  );
}
