"use client";

import { useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import RecentlyViewed from "@/components/home/RecentlyViewed";
import useResponsiveProductCount from "@/components/home/useResponsiveProductCount";

const TABS = [
  { slug: "all", label: "Tất cả" },
  { slug: "phu-kien", label: "Phụ kiện" },
  { slug: "do-choi", label: "Đồ chơi" },
];

/**
 * Khối "Phụ Kiện & Đồ Chơi": tiêu đề + tabs lọc danh mục trên đầu, bên dưới là
 * lưới 5 sản phẩm mỗi hàng và hàng "Đã xem gần đây".
 * Danh sách mở rộng ngay tại chỗ bằng nút "Xem thêm" thay vì rời sang trang shop.
 */
export default function AccessoriesPromo({ products = [] }) {
  const [activeTab, setActiveTab] = useState("all");
  const productDisplayCount = useResponsiveProductCount();
  const [loadedBatches, setLoadedBatches] = useState(1);

  if (products.length === 0) return null;

  const matchesTab = (product, tabSlug) =>
    tabSlug === "all" || product.category?.slug === tabSlug;

  // Chỉ giữ tab thật sự có hàng để không hiện tab rỗng.
  const tabs = TABS.filter((tab) =>
    products.some((product) => matchesTab(product, tab.slug)),
  );

  const filteredProducts = products.filter((product) => matchesTab(product, activeTab));
  const visibleProducts = filteredProducts.slice(0, loadedBatches * productDisplayCount);
  const remaining = Math.max(0, filteredProducts.length - visibleProducts.length);
  const isExpanded = loadedBatches > 1;

  const handleTabChange = (tabSlug) => {
    setActiveTab(tabSlug);
    // Đổi tab thì thu về số lượng ban đầu để lưới không nhảy chiều cao.
    setLoadedBatches(1);
  };

  return (
    <section className="homepage-section accessories-promo-section">
      <div className="promo-right">
        <div className="accessories-promo-header">
          <h2 className="section-title">Phụ Kiện &amp; Đồ Chơi</h2>
          <div className="accessories-promo-tabs">
            {tabs.map((tab) => (
              <button
                key={tab.slug}
                type="button"
                className={`tab-btn ${activeTab === tab.slug ? "active" : ""}`}
                onClick={() => handleTabChange(tab.slug)}
                aria-pressed={activeTab === tab.slug}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        <div className="products-grid-5 accessories-grid">
          {visibleProducts.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>

        {(remaining > 0 || isExpanded) && (
          <div className="load-more-container">
            <button
              type="button"
              className="load-more-btn"
              onClick={() =>
                setLoadedBatches((batches) => (remaining > 0 ? batches + 1 : 1))
              }
            >
              {remaining > 0 ? `Xem thêm (${remaining})` : "Thu gọn"}
              <span className="load-more-icon">{remaining > 0 ? "▾" : "▴"}</span>
            </button>
          </div>
        )}

        <RecentlyViewed />
      </div>
    </section>
  );
}
