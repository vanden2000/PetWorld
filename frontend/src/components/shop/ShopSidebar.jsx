"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { formatPrice } from "@/lib/format";

const PRICE_STEP = 50000;

// Icon bàn tay mèo đồng bộ cho toàn bộ bộ lọc
function CatIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="filter-icon">
      <circle cx="11" cy="4" r="2" />
      <circle cx="18" cy="8" r="2" />
      <circle cx="20" cy="16" r="2" />
      <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z" />
    </svg>
  );
}
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="filter-icon">
      <circle cx="11" cy="4" r="2" />
      <circle cx="18" cy="8" r="2" />
      <circle cx="20" cy="16" r="2" />
      <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z" />
    </svg>
  );
}

/**
 * Sidebar bộ lọc của trang Cửa Hàng.
 */
export default function ShopSidebar({
  categories = [],
  petSpecies = [],
  brands = [],
  priceMax = 2000000,
  selectedCategory = "",
  selectedPet = "",
  selectedBrands = [],
  minPrice = "",
  maxPrice = "",
}) {
  const router = useRouter();
  const initialCategorySlugs = selectedCategory
    ? selectedCategory.split(",").map((s) => s.trim()).filter(Boolean)
    : [];
  const [categorySet, setCategorySet] = useState(new Set(initialCategorySlugs));
  const [brandSet, setBrandSet] = useState(new Set(selectedBrands));
  const [min, setMin] = useState(minPrice ? Number(minPrice) : 0);
  const [max, setMax] = useState(maxPrice ? Number(maxPrice) : priceMax);
  const [isOpen, setIsOpen] = useState(false);

  const navigate = ({ categoryValues, pet, brandsValue, minValue, maxValue }) => {
    const query = new URLSearchParams();
    const catList = categoryValues !== undefined ? categoryValues : [...categorySet];
    if (catList.length) query.set("category", catList.join(","));

    const petValue = pet !== undefined ? pet : selectedPet;
    if (petValue) query.set("pet", petValue);

    const brandList = brandsValue !== undefined ? brandsValue : [...brandSet];
    if (brandList.length) query.set("brand", brandList.join(","));

    const minV = minValue !== undefined ? minValue : min;
    const maxV = maxValue !== undefined ? maxValue : max;
    if (minV > 0) query.set("min_price", String(minV));
    if (maxV < priceMax) query.set("max_price", String(maxV));

    const qs = query.toString();
    router.push(qs ? `/shop?${qs}` : "/shop");
    setIsOpen(false);
  };

  const toggleCategory = (slug) => {
    const next = new Set(categorySet);
    next.has(slug) ? next.delete(slug) : next.add(slug);
    setCategorySet(next);
    navigate({ categoryValues: [...next] });
  };

  const toggleBrand = (slug) => {
    const next = new Set(brandSet);
    next.has(slug) ? next.delete(slug) : next.add(slug);
    setBrandSet(next);
    navigate({ brandsValue: [...next] });
  };

  const handlePriceRelease = () => {
    navigate({ minValue: min, maxValue: max });
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
          <CatIcon />
          {isOpen ? "Ẩn bộ lọc tìm kiếm" : "Bộ lọc & Tìm kiếm"}
        </span>
        <span className="shop-sidebar-toggle-arrow">{isOpen ? "▲" : "▼"}</span>
      </button>

      <div className="shop-sidebar-content">
        {/* LOÀI THÚ CƯNG */}
        {petSpecies.length > 0 && (
          <div className="shop-filter-group">
            <h3 className="shop-filter-label">Loài thú cưng</h3>
            <ul className="shop-cat-list">
              <li>
                <button
                  type="button"
                  className={`shop-cat-item ${!selectedPet ? "active" : ""}`}
                  onClick={() => navigate({ pet: "" })}
                >
                  <CatIcon />
                  <span>Tất cả loài</span>
                </button>
              </li>
              {petSpecies.map((species) => (
                <li key={species.id}>
                  <button
                    type="button"
                    className={`shop-cat-item ${selectedPet === species.slug ? "active" : ""}`}
                    onClick={() => navigate({ pet: species.slug })}
                  >
                    <CatIcon />
                    <span>{species.name}</span>
                    {species.product_count !== undefined && (
                      <small className="shop-filter-count">{species.product_count}</small>
                    )}
                  </button>
                </li>
              ))}
            </ul>
          </div>
        )}

        <div className="shop-sidebar-head">
          <h2 className="shop-sidebar-title">Bộ lọc tìm kiếm</h2>
          <p className="shop-sidebar-sub">Tối ưu lựa chọn cho Pet cưng</p>
        </div>

        {/* DANH MỤC */}
        <div className="shop-filter-group">
          <h3 className="shop-filter-label">Danh mục</h3>
          <ul className="shop-cat-list">
            <li>
              <button
                type="button"
                className={`shop-cat-item ${categorySet.size === 0 ? "active" : ""}`}
                onClick={() => {
                  setCategorySet(new Set());
                  navigate({ categoryValues: [] });
                }}
              >
                <CatIcon />
                <span>Tất cả sản phẩm</span>
              </button>
            </li>
            {categories.map((category) => {
              const isSelected = categorySet.has(category.slug);
              return (
                <li key={category.id}>
                  <button
                    type="button"
                    className={`shop-cat-item ${isSelected ? "active" : ""}`}
                    onClick={() => toggleCategory(category.slug)}
                  >
                    <CatIcon />
                    <span>{category.name}</span>
                  </button>
                </li>
              );
            })}
          </ul>
        </div>

        {/* THƯƠNG HIỆU */}
        {brands.length > 0 && (
          <div className="shop-filter-group">
            <h3 className="shop-filter-label">Thương hiệu</h3>
            <ul className="shop-cat-list">
              {brands.map((brand) => {
                const isSelected = brandSet.has(brand.slug);
                return (
                  <li key={brand.id}>
                    <button
                      type="button"
                      className={`shop-cat-item ${isSelected ? "active" : ""}`}
                      onClick={() => toggleBrand(brand.slug)}
                    >
                      <CatIcon />
                      <span>{brand.name}</span>
                    </button>
                  </li>
                );
              })}
            </ul>
          </div>
        )}

        {/* KHOẢNG GIÁ */}
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
            <input
              type="range"
              className="shop-price-thumb"
              min={0}
              max={priceMax}
              step={PRICE_STEP}
              value={min}
              aria-label="Giá tối thiểu"
              onChange={(event) => setMin(Math.min(Number(event.target.value), max - PRICE_STEP))}
              onMouseUp={handlePriceRelease}
              onTouchEnd={handlePriceRelease}
            />
            <input
              type="range"
              className="shop-price-thumb"
              min={0}
              max={priceMax}
              step={PRICE_STEP}
              value={max}
              aria-label="Giá tối đa"
              onChange={(event) => setMax(Math.max(Number(event.target.value), min + PRICE_STEP))}
              onMouseUp={handlePriceRelease}
              onTouchEnd={handlePriceRelease}
            />
          </div>
        </div>

        {/* NÚT ÁP DỤNG BỘ LỌC */}
        <button type="button" className="shop-apply-btn" onClick={() => navigate({})}>
          Áp dụng bộ lọc
        </button>
      </div>
    </aside>
  );
}


