"use client";

import { useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { formatPrice, resolveProductImage } from "@/lib/format";
import { addToCart, checkQuantity } from "@/lib/cart";
import { toastSuccess, toastError, toastInfo } from "@/lib/toast";
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

  const variants = useMemo(() => product.variants ?? [], [product.variants]);
  // Mặc định chọn biến thể có giá hiệu lực thấp nhất (đồng nhất với cách card hiển thị giá).
  const defaultVariant = useMemo(
    () => [...variants].sort((a, b) => a.effective_price - b.effective_price)[0] ?? null,
    [variants],
  );

  const [activeImage, setActiveImage] = useState(gallery[0]);
  const [selectedOptions, setSelectedOptions] = useState(() =>
    Object.fromEntries(
      (defaultVariant?.options ?? []).map((option) => [option.type_id, option.value]),
    ),
  );
  const [quantity, setQuantity] = useState(1);

  const selectedVariant = variants.find((variant) =>
    variant.options?.length === Object.keys(selectedOptions).length
      && variant.options.every(
        (option) => selectedOptions[option.type_id] === option.value,
      ),
  ) ?? null;

  const currentPrice = selectedVariant ? selectedVariant.effective_price : product.price?.min;
  const oldPrice = selectedVariant?.sale_price ? selectedVariant.price : null;
  const discount = oldPrice ? Math.round(((oldPrice - currentPrice) / oldPrice) * 100) : 0;
  const inStock = (selectedVariant?.quantity ?? 0) > 0;

  // Gom các giá trị của mọi SKU theo loại để khách chọn từng phần của tổ hợp.
  const variantGroups = useMemo(() => {
    const groups = new Map();
    for (const variant of variants) {
      for (const option of variant.options ?? []) {
        if (!groups.has(option.type_id)) {
          groups.set(option.type_id, {
            id: option.type_id,
            name: option.type_name,
            values: new Set(),
          });
        }
        groups.get(option.type_id).values.add(option.value);
      }
    }
    return [...groups.values()].map((group) => ({
      ...group,
      values: [...group.values],
    }));
  }, [variants]);

  const optionIsAvailable = (typeId, value) => variants.some((variant) => {
    if (variant.quantity <= 0) return false;

    const options = Object.fromEntries(
      (variant.options ?? []).map((option) => [option.type_id, option.value]),
    );

    if (options[typeId] !== value) return false;

    return Object.entries(selectedOptions).every(([selectedTypeId, selectedValue]) =>
      Number(selectedTypeId) === Number(typeId)
        || options[selectedTypeId] === selectedValue,
    );
  });

  const selectOption = (typeId, value) => {
    setSelectedOptions((current) => ({ ...current, [typeId]: value }));
    setQuantity(1);
  };

  const handleAddToCart = () => {
    if (!selectedVariant || !inStock) {
      toastError("Vui lòng chọn đầy đủ phân loại còn hàng.");
      return false;
    }
    if (!checkQuantity(product.id, selectedVariant.id, quantity, selectedVariant.quantity)) {
      toastError(`Trong giỏ đã có sản phẩm này. Bạn chỉ có thể mua tối đa ${selectedVariant.quantity}.`);
      return false;
    }

    const added = addToCart(
      {
        productId: product.id,
        slug: product.slug,
        name: product.name,
        image: product.image,
        variantId: selectedVariant.id,
        variantName: selectedVariant.name,
        price: selectedVariant.effective_price,
        oldPrice: selectedVariant?.sale_price ? selectedVariant.price : null,
        stockQuantity: selectedVariant.quantity,
      },
      quantity,
    );
    if (!added) return false;
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

        <div className="pd-price-block">
          <div className="pd-price">
            <span className="pd-price-current">{formatPrice(currentPrice)}</span>
            {oldPrice && <span className="pd-price-old">{formatPrice(oldPrice)}</span>}
          </div>

          {product.short_description && (
            <p className="pd-short-description">{product.short_description}</p>
          )}
        </div>

        {variantGroups.map((group) => (
          <div className="pd-variant-group" key={group.id}>
            <span className="pd-variant-label">{group.name}:</span>
            <div className="pd-variant-options">
              {group.values.map((value) => (
                <button
                  key={value}
                  type="button"
                  className={`pd-variant-btn ${selectedOptions[group.id] === value ? "active" : ""}`}
                  onClick={() => selectOption(group.id, value)}
                  disabled={!optionIsAvailable(group.id, value)}
                >
                  {value}
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
            <button
              type="button"
              onClick={() => {
                if (!selectedVariant) return;
                if (quantity >= selectedVariant.quantity) {
                  toastInfo(`Biến thể đã vượt quá số lượng trong kho.`);
                  return;
                }
                setQuantity((q) => q + 1);
              }}
              disabled={!selectedVariant}
              aria-label="Tăng"
            >
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
