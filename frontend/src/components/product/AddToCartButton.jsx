"use client";

import { useState } from "react";
import { addToCart } from "@/lib/cart";
import { toastSuccess, toastError } from "@/lib/toast";
import { getProductDetail } from "@/lib/api";
import QuickVariantModal from "@/components/product/QuickVariantModal";

// Nút "thêm vào giỏ" trên ProductCard (Server Component) — tách client riêng,
// cùng cách làm như WishlistButton.
export default function AddToCartButton({ product }) {
  const [isLoading, setIsLoading] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalProduct, setModalProduct] = useState(null);

  const handleAdd = async (e) => {
    e.preventDefault();
    e.stopPropagation();

    if (isLoading) return;
    setIsLoading(true);

    try {
      const data = await getProductDetail(product.slug);
      if (!data || !data.product) {
        toastError("Không thể tải thông tin sản phẩm.");
        setIsLoading(false);
        return;
      }

      const fetchedProduct = data.product;
      const variants = fetchedProduct.variants ?? [];

      if (variants.length === 0) {
        // Không có biến thể, thêm thẳng vào giỏ
        const priceRange = fetchedProduct.price || {};
        const price =
          priceRange.display ?? (priceRange.has_sale ? priceRange.sale_min : priceRange.min) ?? 0;
        const oldPrice =
          priceRange.compare_at ?? (priceRange.has_sale ? priceRange.regular_min : null) ?? null;

        addToCart({
          productId: fetchedProduct.id,
          slug: fetchedProduct.slug,
          name: fetchedProduct.name,
          image: fetchedProduct.image,
          variantId: null,
          variantName: null,
          price,
          oldPrice,
        });

        toastSuccess(`Đã thêm "${fetchedProduct.name}" vào giỏ hàng`);
      } else {
        // Có biến thể, mở modal cho chọn
        setModalProduct(fetchedProduct);
        setIsModalOpen(true);
      }
    } catch (error) {
      console.error(error);
      toastError("Có lỗi xảy ra khi thêm vào giỏ hàng.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <button
        className="product-add-btn"
        type="button"
        onClick={handleAdd}
        disabled={isLoading}
        aria-label="Thêm vào giỏ hàng"
      >
        {isLoading ? (
          <svg className="spinner-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
            <circle cx="12" cy="12" r="10" strokeDasharray="32" strokeDashoffset="12" />
          </svg>
        ) : (
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="9" cy="21" r="1" />
            <circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
          </svg>
        )}
      </button>

      <QuickVariantModal
        key={`${modalProduct?.id || "empty"}-${isModalOpen}`}
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        product={modalProduct}
      />
    </>
  );
}
