"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { formatPrice } from "@/lib/format";

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
  const [min, setMin] = useState(minPrice);
  const [max, setMax] = useState(maxPrice);

  // Dựng URL mới từ các lựa chọn rồi điều hướng (luôn về trang 1).
  const navigate = ({ category, brandsValue, minValue, maxValue }) => {
    const query = new URLSearchParams();
    const cat = category !== undefined ? category : selectedCategory;
    if (cat) query.set("category", cat);

    const brandList = brandsValue !== undefined ? brandsValue : [...brandSet];
    if (brandList.length) query.set("brand", brandList.join(","));

    const minV = minValue !== undefined ? minValue : min;
    const maxV = maxValue !== undefined ? maxValue : max;
    if (minV) query.set("min_price", String(minV));
    if (maxV) query.set("max_price", String(maxV));

    const qs = query.toString();
    router.push(qs ? `/shop?${qs}` : "/shop");
  };

  const toggleBrand = (slug) => {
    const next = new Set(brandSet);
    next.has(slug) ? next.delete(slug) : next.add(slug);
    setBrandSet(next);
  };

  return (
    <aside className="shop-sidebar">
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
          <span>{formatPrice(min || 0)}</span>
          <span>{formatPrice(max || priceMax)}</span>
        </div>
        <input
          type="range"
          className="shop-price-range"
          min={0}
          max={priceMax}
          step={50000}
          value={max || priceMax}
          onChange={(event) => setMax(event.target.value)}
        />
      </div>

      <button type="button" className="shop-apply-btn" onClick={() => navigate({})}>
        Áp dụng bộ lọc
      </button>
    </aside>
  );
}
