"use client";

import { useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import { resolveBackendImage } from "@/lib/format";

/**
 * Khối "Sản Phẩm Mới" - Ý TƯỞNG 2 (Cập nhật):
 * - Tiêu đề "Sản Phẩm Mới" trên đầu.
 * - Khung nâu Gradient cao cấp.
 * - Nút "Xem thêm sản phẩm ▼" / "Thu gọn sản phẩm ▲" nằm CHÍNH GIỮA (Không background, tối giản tinh tế).
 * - Hình 2 bé thú cưng ló đầu góc dưới bên phải.
 */
export default function NewProductsSplit({ products = [] }) {
  const INITIAL_COUNT = 5;
  // Mở rộng hiển thị tối đa 10 sản phẩm (2 hàng lưới 5 cột). Backend trả bao nhiêu
  // thì hiện bấy nhiêu — tăng limit của khối trong Admin là tự có thêm, không cần sửa code.
  const MAX_COUNT = 10;
  const [isExpanded, setIsExpanded] = useState(false);

  if (products.length === 0) return null;

  const displayProducts = products.slice(0, MAX_COUNT);
  const visibleProducts = isExpanded ? displayProducts : displayProducts.slice(0, INITIAL_COUNT);
  const hasMoreThanInitial = displayProducts.length > INITIAL_COUNT;

  const toggleExpand = () => {
    setIsExpanded((prev) => !prev);
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
            <ProductCard key={product.id} product={product} badge="New" showDate />
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
                <span>{isExpanded ? "Thu gọn sản phẩm" : "Xem thêm sản phẩm"}</span>
                <span className="idea2-arrow-icon">{isExpanded ? "▲" : "▼"}</span>
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








