"use client";

import { addToCart } from "@/lib/cart";
import { toastSuccess } from "@/lib/toast";

// Nút "thêm vào giỏ" trên ProductCard (Server Component) — tách client riêng,
// cùng cách làm như WishlistButton.
export default function AddToCartButton({ product }) {
  const handleAdd = () => {
    // Trang chủ trả `price_range`, /api/products trả `price`; lấy giá hiệu lực giống ProductCard.
    const priceRange = product.price_range || product.price || {};
    const price =
      priceRange.display ?? (priceRange.has_sale ? priceRange.sale_min : priceRange.min) ?? 0;
    const oldPrice =
      priceRange.compare_at ?? (priceRange.has_sale ? priceRange.regular_min : null) ?? null;

    addToCart({
      productId: product.id,
      slug: product.slug,
      name: product.name,
      image: product.image,
      variantId: null,
      variantName: null,
      price,
      oldPrice,
    });

    toastSuccess(`Đã thêm "${product.name}" vào giỏ hàng`);
  };

  return (
    <button className="product-add-btn" type="button" onClick={handleAdd} aria-label="Thêm vào giỏ hàng">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="9" cy="21" r="1" />
        <circle cx="20" cy="21" r="1" />
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
      </svg>
    </button>
  );
}
