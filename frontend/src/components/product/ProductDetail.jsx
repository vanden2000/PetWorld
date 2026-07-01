"use client";

import { useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { formatPrice, resolveProductImage } from "@/lib/format";
import { addToCart } from "@/lib/cart";
import { toastSuccess, toastError } from "@/lib/toast";
import { ROUTES } from "@/lib/routes";
import WishlistButton from "@/components/product/WishlistButton";

function Stars({ value = 0 }) {
  return (
    <span className="pd-stars">
      {Array.from({ length: 5 }).map((_, index) => (
        <svg key={index} width="16" height="16" viewBox="0 0 24 24" fill={index < value ? "currentColor" : "none"} stroke="currentColor" strokeWidth="1.5">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
      ))}
    </span>
  );
}

/**
 * Khu vực chính của trang chi tiết: gallery ảnh + thông tin + chọn biến thể + thêm giỏ.
 * Dữ liệu lấy từ getProductDetail() (key `price`, `variants`, `images`...).
 */
export default function ProductDetail({ product }) {
  const router = useRouter();

  const gallery = product.images?.length
    ? product.images.map((image) => image.image_url)
    : [product.image];

  const variants = product.variants ?? [];
  // Mặc định chọn biến thể có giá hiệu lực thấp nhất (đồng nhất với cách card hiển thị giá).
  const defaultVariant = useMemo(
    () => [...variants].sort((a, b) => a.effective_price - b.effective_price)[0] ?? null,
    [variants],
  );

  const [activeImage, setActiveImage] = useState(gallery[0]);
  const [selectedVariantId, setSelectedVariantId] = useState(defaultVariant?.id ?? null);
  const [quantity, setQuantity] = useState(1);

  const selectedVariant = variants.find((variant) => variant.id === selectedVariantId) ?? defaultVariant;

  const currentPrice = selectedVariant ? selectedVariant.effective_price : product.price?.min;
  const oldPrice = selectedVariant?.sale_price ? selectedVariant.price : null;
  const discount = oldPrice ? Math.round(((oldPrice - currentPrice) / oldPrice) * 100) : 0;
  const inStock = (selectedVariant?.quantity ?? product.stock_quantity ?? 0) > 0;

  // Gom biến thể theo loại (Kích thước, Loại bao bì...) để render thành từng nhóm như mockup.
  const variantGroups = useMemo(() => {
    const groups = new Map();
    for (const variant of variants) {
      const typeName = variant.type?.name ?? "Phân loại";
      if (!groups.has(typeName)) groups.set(typeName, []);
      groups.get(typeName).push(variant);
    }
    return [...groups.entries()];
  }, [variants]);

  const handleAddToCart = () => {
    if (!selectedVariant || !inStock) {
      toastError("Sản phẩm tạm hết hàng, vui lòng chọn phân loại khác.");
      return false;
    }
    addToCart(
      {
        productId: product.id,
        slug: product.slug,
        name: product.name,
        image: product.image,
        variantId: selectedVariant.id,
        variantName: selectedVariant.name,
        price: selectedVariant.effective_price,
        oldPrice: selectedVariant?.sale_price ? selectedVariant.price : null,
      },
      quantity,
    );
    toastSuccess(`Đã thêm "${product.name}" vào giỏ hàng`);
    return true;
  };

  const handleBuyNow = () => {
    if (handleAddToCart()) {
      router.push(ROUTES.cart);
    }
  };

  return (
    <div className="pd-top">
      {/* Gallery */}
      <div className="pd-gallery">
        <div className="pd-main-image">
          {discount > 0 && <span className="pd-badge-discount">-{discount}%</span>}
          {product.category?.name && <span className="pd-badge-cat">{product.category.name}</span>}
          <img src={resolveProductImage(activeImage)} alt={product.name} />
        </div>
        {gallery.length > 1 && (
          <div className="pd-thumbs">
            {gallery.map((image, index) => (
              <button
                key={index}
                type="button"
                className={`pd-thumb ${activeImage === image ? "active" : ""}`}
                onClick={() => setActiveImage(image)}
              >
                <img src={resolveProductImage(image)} alt={`${product.name} ${index + 1}`} />
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Thông tin & mua hàng */}
      <div className="pd-info">
        <h1 className="pd-title">{product.name}</h1>

        <div className="pd-meta">
          <span className="pd-rating">
            <Stars value={Math.round(product.rating?.average ?? 0)} />
            <strong>{product.rating?.average ?? 0}</strong>
            <span>({product.rating?.count ?? 0} đánh giá)</span>
          </span>
          <span className={`pd-stock ${inStock ? "in" : "out"}`}>
            {inStock ? "Còn hàng" : "Hết hàng"}
          </span>
        </div>

        <div className="pd-price">
          <span className="pd-price-current">{formatPrice(currentPrice)}</span>
          {oldPrice && <span className="pd-price-old">{formatPrice(oldPrice)}</span>}
        </div>

        {variantGroups.map(([typeName, groupVariants]) => (
          <div className="pd-variant-group" key={typeName}>
            <span className="pd-variant-label">{typeName}:</span>
            <div className="pd-variant-options">
              {groupVariants.map((variant) => (
                <button
                  key={variant.id}
                  type="button"
                  className={`pd-variant-btn ${selectedVariantId === variant.id ? "active" : ""}`}
                  onClick={() => setSelectedVariantId(variant.id)}
                  disabled={variant.quantity <= 0}
                >
                  {variant.name}
                </button>
              ))}
            </div>
          </div>
        ))}

        <div className="pd-actions">
          <div className="pd-qty">
            <button type="button" onClick={() => setQuantity((q) => Math.max(1, q - 1))} aria-label="Giảm">
              −
            </button>
            <span>{quantity}</span>
            <button type="button" onClick={() => setQuantity((q) => q + 1)} aria-label="Tăng">
              +
            </button>
          </div>
          <button type="button" className="pd-add-btn" onClick={handleAddToCart} disabled={!inStock}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="9" cy="21" r="1" />
              <circle cx="20" cy="21" r="1" />
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
            </svg>
            Thêm giỏ hàng
          </button>
          <WishlistButton product={product} className="pd-wishlist-btn" />
        </div>

        <button type="button" className="pd-buy-btn" onClick={handleBuyNow} disabled={!inStock}>
          Mua ngay
        </button>

        <div className="pd-attrs">
          {product.brand?.name && (
            <p>
              <span>Thương hiệu:</span> {product.brand.name}
            </p>
          )}
          {product.category?.name && (
            <p>
              <span>Danh mục:</span> {product.category.name}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
