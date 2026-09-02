"use client";

import { useState } from "react";
import ProductCard from "@/components/product/ProductCard";

/**
 * Khối sản phẩm bán chạy với tab lọc theo danh mục.
 * `groups` = mảng products_by_categories từ API ({ category, products }).
 */
export default function BestSellingTabs({ groups = [], title = "Sản Phẩm Khuyến Mãi" }) {
  const tabs = groups.filter((group) => group.products?.length > 0);
  const [activeCategoryId, setActiveCategoryId] = useState(null);
  const [expandedCategoryId, setExpandedCategoryId] = useState(null);

  if (tabs.length === 0) return null;

  const current = tabs.find((group) => group.category.id === activeCategoryId) ?? tabs[0];
  const isExpanded = expandedCategoryId === current.category.id;
  const visibleProducts = isExpanded ? current.products : current.products.slice(0, 5);

  return (
    <section className="homepage-section">
      <div className="best-selling-header">
        <h2 className="section-title">{title}</h2>
        <div className="best-selling-tabs">
          {tabs.map((group) => (
            <button
              key={group.category.id}
              type="button"
              className={`tab-btn ${group.category.id === current.category.id ? "active" : ""}`}
              onClick={() => setActiveCategoryId(group.category.id)}
              aria-pressed={group.category.id === current.category.id}
            >
              {group.category.name}
            </button>
          ))}
        </div>
      </div>

      <div className="products-grid-5">
        {visibleProducts.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

      {current.products.length > 5 && (
        <div className="load-more-container">
          <button
            type="button"
            className="load-more-btn"
            onClick={() => setExpandedCategoryId(isExpanded ? null : current.category.id)}
          >
            {isExpanded ? "Thu gọn" : `Xem thêm (${current.products.length - 5})`}
          </button>
        </div>
      )}
    </section>
  );
}
