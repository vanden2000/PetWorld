"use client";

import { useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import { resolveBackendImage } from "@/lib/format";
import useResponsiveProductCount from "@/components/home/useResponsiveProductCount";

/**
 * Khối "Sản Phẩm Mới" - Ý TƯỞNG 2 (Cập nhật):
 * - Tiêu đề "Sản Phẩm Mới" trên đầu.
 * - Khung nâu Gradient cao cấp.
 * - Nút "Xem thêm sản phẩm ▼" / "Thu gọn sản phẩm ▲" nằm CHÍNH GIỮA (Không background, tối giản tinh tế).
 * - Hình 2 bé thú cưng ló đầu góc dưới bên phải.
 */
export default function NewProductsSplit({ products = [] }) {
  const productDisplayCount = useResponsiveProductCount();
  const [loadedBatches, setLoadedBatches] = useState(1);

  if (products.length === 0) return null;

  const visibleProducts = products.slice(0, loadedBatches * productDisplayCount);
  const remaining = Math.max(0, products.length - visibleProducts.length);
  const hasMoreThanInitial = products.length > productDisplayCount;

  const toggleExpand = () => {
    setLoadedBatches((batches) => (remaining > 0 ? batches + 1 : 1));
  };

  return (
    <section className="homepage-section new-products-section">
      {/* Tiêu đề viết ngang nằm trên đầu */}
      <div className="section-header">
        <h2 className="section-title">Sản Phẩm Mới</h2>
      </div>

      {/* Khung màu nâu Ý tưởng 2 */}
      <div className="new-products-brown-box idea2-container">
        <div className="new-products-grid-5">
          {visibleProducts.map((product) => (
            <ProductCard key={product.id} product={product} badge={product.is_new ? "Mới" : undefined} showDate />
          ))}
        </div>

        {/* Footer Ý tưởng 2: Nút bấm nằm CHÍNH GIỮA (Bỏ background) + Hình thú cưng góc phải */}
        <div className="idea2-footer">
          {hasMoreThanInitial && (
            <div className="idea2-center-btn-wrapper">
              <button
                type="button"
                className="idea2-expand-btn idea2-btn-no-bg"
                onClick={toggleExpand}
              >
                <span>{remaining > 0 ? `Xem thêm (${remaining})` : "Thu gọn sản phẩm"}</span>
                <span className="idea2-arrow-icon">{remaining > 0 ? "▼" : "▲"}</span>
              </button>
            </div>
          )}

          <div className="idea2-pets-corner">
            <img
              src={resolveBackendImage("storage/promo/pets-only.png")}
              alt="Pets Illustration"
              className="idea2-pets-img"
            />
          </div>
        </div>
      </div>
    </section>
  );
}








