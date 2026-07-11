"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { formatPrice } from "@/lib/format";

// Bước nhảy của thanh trượt giá (đồng bộ với UI cũ).
const PRICE_STEP = 50000;

// Icon đơn giản đứng trước mỗi danh mục cho giống mockup.
function CatIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="11" cy="4" r="2" />
      <circle cx="18" cy="8" r="2" />
      <circle cx="20" cy="16" r="2" />
      <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z" />
    </svg>
  );
}

/**
 * Sidebar bộ lọc của trang Cửa Hàng.
 * Đẩy lựa chọn lên URL (?category=&brand=&min_price=&max_price=) để trang server fetch lại.
 */
export default function ShopSidebar({
  categories = [],
  brands = [],
  priceMax = 2000000,
  selectedCategory = "",
  selectedBrands = [],
  minPrice = "",
  maxPrice = "",
}) {
  const router = useRouter();
  const [brandSet, setBrandSet] = useState(new Set(selectedBrands));
  // Giá là số để hai tay kéo min/max hoạt động: mặc định min = 0, max = trần giá.
  const [min, setMin] = useState(minPrice ? Number(minPrice) : 0);
  const [max, setMax] = useState(maxPrice ? Number(maxPrice) : priceMax);
  const [isOpen, setIsOpen] = useState(false);

  // Dựng URL mới từ các lựa chọn rồi điều hướng (luôn về trang 1).
  const navigate = ({ category, brandsValue, minValue, maxValue }) => {
    const query = new URLSearchParams();
    const cat = category !== undefined ? category : selectedCategory;
    if (cat) query.set("category", cat);

    const brandList = brandsValue !== undefined ? brandsValue : [...brandSet];
    if (brandList.length) query.set("brand", brandList.join(","));

    const minV = minValue !== undefined ? minValue : min;
    const maxV = maxValue !== undefined ? maxValue : max;
    // Chỉ đẩy lên URL khi thực sự thu hẹp khoảng (min > 0, max < trần giá).
    if (minV > 0) query.set("min_price", String(minV));
    if (maxV < priceMax) query.set("max_price", String(maxV));

    const qs = query.toString();
    router.push(qs ? `/shop?${qs}` : "/shop");
    setIsOpen(false);
  };

  const toggleBrand = (slug) => {
    const next = new Set(brandSet);
    next.has(slug) ? next.delete(slug) : next.add(slug);
    setBrandSet(next);
  };

  return (
    <aside className={`shop-sidebar ${isOpen ? "is-open" : ""}`}>
      {/* Mobile Toggle Bar */}
      <button
        type="button"
        className="shop-sidebar-toggle"
        onClick={() => setIsOpen(!isOpen)}
        aria-expanded={isOpen}
      >
        <span className="shop-sidebar-toggle-text">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ marginRight: 8 }}>
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
          </svg>
          {isOpen ? "Ẩn bộ lọc tìm kiếm" : "Bộ lọc & Tìm kiếm"}
        </span>
        <span className="shop-sidebar-toggle-arrow">{isOpen ? "▲" : "▼"}</span>
      </button>

      <div className="shop-sidebar-content">
        <div className="shop-sidebar-head">
          <h2 className="shop-sidebar-title">Bộ lọc tìm kiếm</h2>
          <p className="shop-sidebar-sub">Tối ưu lựa chọn cho Pet cưng</p>
        </div>

        <div className="shop-filter-group">
          <h3 className="shop-filter-label">Danh mục</h3>
          <ul className="shop-cat-list">
            <li>
              <button
                type="button"
                className={`shop-cat-item ${selectedCategory ? "" : "active"}`}
                onClick={() => navigate({ category: "" })}
              >
                <CatIcon />
                <span>Tất cả sản phẩm</span>
              </button>
            </li>
            {categories.map((category) => (
              <li key={category.id}>
                <button
                  type="button"
                  className={`shop-cat-item ${selectedCategory === category.slug ? "active" : ""}`}
                  onClick={() => navigate({ category: category.slug })}
                >
                  <CatIcon />
                  <span>{category.name}</span>
                </button>
              </li>
            ))}
          </ul>
        </div>

        {brands.length > 0 && (
          <div className="shop-filter-group">
            <h3 className="shop-filter-label">Thương hiệu</h3>
            <ul className="shop-brand-list">
              {brands.map((brand) => (
                <li key={brand.id}>
                  <label className="shop-brand-item">
                    <input
                      type="checkbox"
                      checked={brandSet.has(brand.slug)}
                      onChange={() => toggleBrand(brand.slug)}
                    />
                    <span>{brand.name}</span>
                  </label>
                </li>
              ))}
            </ul>
          </div>
        )}

        <div className="shop-filter-group">
          <h3 className="shop-filter-label">Khoảng giá (VNĐ)</h3>
          <div className="shop-price-row">
            <span>{formatPrice(min)}</span>
            <span>{formatPrice(max)}</span>
          </div>
          <div className="shop-price-slider">
            <div className="shop-price-track" />
            <div
              className="shop-price-track-fill"
              style={{
                left: `${(min / priceMax) * 100}%`,
                right: `${100 - (max / priceMax) * 100}%`,
              }}
            />
            {/* Tay kéo giá tối thiểu: không vượt quá (max - 1 bước) */}
            <input
              type="range"
              className="shop-price-thumb"
              min={0}
              max={priceMax}
              step={PRICE_STEP}
              value={min}
              aria-label="Giá tối thiểu"
              onChange={(event) => setMin(Math.min(Number(event.target.value), max - PRICE_STEP))}
            />
            {/* Tay kéo giá tối đa: không nhỏ hơn (min + 1 bước) */}
            <input
              type="range"
              className="shop-price-thumb"
              min={0}
              max={priceMax}
              step={PRICE_STEP}
              value={max}
              aria-label="Giá tối đa"
              onChange={(event) => setMax(Math.max(Number(event.target.value), min + PRICE_STEP))}
            />
          </div>
        </div>

        <button type="button" className="shop-apply-btn" onClick={() => navigate({})}>
          Áp dụng bộ lọc
        </button>
      </div>
    </aside>
  );
}
