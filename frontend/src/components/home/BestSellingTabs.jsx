"use client";

import { useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import useResponsiveProductCount from "@/components/home/useResponsiveProductCount";

/**
 * Khối sản phẩm bán chạy với tab lọc theo danh mục.
 * `groups` = mảng products_by_categories từ API ({ category, products }).
 */
export default function BestSellingTabs({ groups = [], title = "Sản Phẩm Khuyến Mãi" }) {
  const tabs = groups.filter((group) => group.products?.length > 0);
  const [activeCategoryId, setActiveCategoryId] = useState(null);
  const productDisplayCount = useResponsiveProductCount();
  const [loadedBatches, setLoadedBatches] = useState(1);

  if (tabs.length === 0) return null;

  const current = tabs.find((group) => group.category.id === activeCategoryId) ?? tabs[0];
  const visibleProducts = current.products.slice(0, loadedBatches * productDisplayCount);
  const remaining = Math.max(0, current.products.length - visibleProducts.length);

  const handleTabChange = (categoryId) => {
    setActiveCategoryId(categoryId);
    setLoadedBatches(1);
  };

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
              onClick={() => handleTabChange(group.category.id)}
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

      {current.products.length > productDisplayCount && (
        <div className="load-more-container">
          <button
            type="button"
            className="load-more-btn"
            onClick={() => setLoadedBatches((batches) => (remaining > 0 ? batches + 1 : 1))}
          >
            {remaining > 0 ? `Xem thêm (${remaining})` : "Thu gọn"}
          </button>
        </div>
      )}
    </section>
  );
}
