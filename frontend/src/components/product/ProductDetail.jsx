"use client";

import { useMemo, useState, useSyncExternalStore } from "react";
import { useRouter } from "next/navigation";
import { formatPrice, resolveProductImage } from "@/lib/format";
import { addToCart, checkQuantity } from "@/lib/cart";
import { toastSuccess, toastError, toastInfo } from "@/lib/toast";
import { ROUTES } from "@/lib/routes";
import WishlistButton from "@/components/product/WishlistButton";
import LoginRequiredDialog from "@/components/ui/LoginRequiredDialog";
import { getUserSnapshot, getServerUserSnapshot, parseUser, onAuthChange } from "@/lib/auth";
import { setBuyNow } from "@/lib/buyNow";

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
  const userRaw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(userRaw), [userRaw]);

  const gallery = product.images?.length
    ? product.images
    : [{ image_url: product.image, alt_text: product.name }];

  const variants = useMemo(() => product.variants ?? [], [product.variants]);
  // Mặc định chọn biến thể có giá hiệu lực thấp nhất (đồng nhất với cách card hiển thị giá).
  const defaultVariant = useMemo(
    () => [...variants]
      .filter((variant) => variant.quantity > 0)
      .sort((a, b) => a.effective_price - b.effective_price)[0]
      ?? [...variants].sort((a, b) => a.effective_price - b.effective_price)[0]
      ?? null,
    [variants],
  );

  const [activeImage, setActiveImage] = useState(gallery[0]);
  const [selectedOptions, setSelectedOptions] = useState(() =>
    Object.fromEntries(
      (defaultVariant?.options ?? []).map((option) => [option.type_id, option.value]),
    ),
  );
  const [quantity, setQuantity] = useState(1);
  const [showLoginDialog, setShowLoginDialog] = useState(false);

  const selectedVariant = variants.find((variant) =>
    variant.options?.length === Object.keys(selectedOptions).length
    && variant.options.every(
      (option) => selectedOptions[option.type_id] === option.value,
    ),
  ) ?? null;

  const currentPrice = selectedVariant ? selectedVariant.effective_price : product.price?.min;
  const oldPrice = selectedVariant?.sale_price ? selectedVariant.price : null;
  const discount = oldPrice ? Math.round(((oldPrice - currentPrice) / oldPrice) * 100) : 0;
  const availableQuantity = selectedVariant?.quantity ?? product.stock_quantity ?? 0;
  const inStock = availableQuantity > 0;

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

  const optionIsAvailable = (typeId, value) => variants.some((variant) =>
    variant.quantity > 0
    && variant.options?.some((option) =>
      Number(option.type_id) === Number(typeId) && option.value === value,
    ),
  );

  const selectOption = (typeId, value) => {
    setSelectedOptions((current) => {
      const matchingVariants = variants.filter((variant) =>
        variant.quantity > 0
        && variant.options?.some((option) =>
          Number(option.type_id) === Number(typeId) && option.value === value,
        ),
      );

      const nextVariant = matchingVariants.find((variant) =>
        variant.options?.every((option) =>
          Number(option.type_id) === Number(typeId)
          || current[option.type_id] === option.value,
        ),
      ) ?? matchingVariants[0];

      return Object.fromEntries(
        (nextVariant?.options ?? []).map((option) => [option.type_id, option.value]),
      );
    });
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
    if (!selectedVariant || !inStock) {
      toastError("Vui lòng chọn đầy đủ phân loại còn hàng.");
      return;
    }
    if (quantity > selectedVariant.quantity) {
      toastError(`Bạn chỉ có thể mua tối đa ${selectedVariant.quantity}.`);
      return;
    }

    setBuyNow({
      key: `${product.id}:${selectedVariant.id}`,
      productId: product.id,
      slug: product.slug,
      name: product.name,
      image: product.image,
      variantId: selectedVariant.id,
      variantName: selectedVariant.name,
      price: selectedVariant.effective_price,
      oldPrice: selectedVariant?.sale_price ? selectedVariant.price : null,
      stockQuantity: selectedVariant.quantity,
      quantity,
    });

    if (!user) {
      setShowLoginDialog(true);
      return;
    }

    router.push(ROUTES.checkout);
  };

  const handleLogin = () => {
    setShowLoginDialog(false);
    router.push(`${ROUTES.login}?redirect=${encodeURIComponent(ROUTES.checkout)}`);
  };

  return (
    <div className="pd-top">
      <LoginRequiredDialog
        open={showLoginDialog}
        onClose={() => setShowLoginDialog(false)}
        onConfirm={handleLogin}
      />
      {/* Gallery */}
      <div className="pd-gallery">
        <div className="pd-main-image">
          {discount > 0 && <span className="pd-badge-discount">-{discount}%</span>}
          {product.category?.name && <span className="pd-badge-cat">{product.category.name}</span>}
          <img src={resolveProductImage(activeImage?.image_url)} alt={activeImage?.alt_text || product.name} />
        </div>
        {gallery.length > 1 && (
          <div className="pd-thumbs">
            {gallery.map((image, index) => (
              <button
                key={`${image.id || image.image_url}-${index}`}
                type="button"
                className={`pd-thumb ${activeImage?.image_url === image.image_url ? "active" : ""}`}
                onClick={() => setActiveImage(image)}
              >
                <img src={resolveProductImage(image.image_url)} alt={image.alt_text || `${product.name} - ảnh ${index + 1}`} />
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
          <span className={`pd-stock ${inStock ? "in" : "out"}`} aria-live="polite">
            {inStock ? `Còn ${availableQuantity} sản phẩm` : "Hết hàng"}
          </span>
        </div>
        <div className="pd-price">
          <span className="pd-price-current">{formatPrice(currentPrice)}</span>
          {oldPrice && <span className="pd-price-old">{formatPrice(oldPrice)}</span>}
          <WishlistButton product={product} className="pd-wishlist-btn" />
        </div>
        <div className="pd-desc">
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

        <div className="pd-socials">
          <a href="#" className="pd-social-link" aria-label="Twitter">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
            </svg>
          </a>
          <a href="#" className="pd-social-link" aria-label="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
            </svg>
          </a>
          <a href="#" className="pd-social-link" aria-label="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
            </svg>
          </a>
          <a href="#" className="pd-social-link" aria-label="YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M23 12s0-3.5-.46-5.17a2.78 2.78 0 0 0-1.94-2C18.88 4.46 12 4.46 12 4.46s-6.88 0-8.6.37a2.78 2.78 0 0 0-1.94 2C1 8.5 1 12 1 12s0 3.5.46 5.17a2.78 2.78 0 0 0 1.94 2c1.72.37 8.6.37 8.6.37s6.88 0 8.6-.37a2.78 2.78 0 0 0 1.94-2C23 15.5 23 12 23 12zM9.75 15.02V8.98L15.5 12z" />
            </svg>
          </a>
          <a href="#" className="pd-social-link" aria-label="Dribbble">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="12" cy="12" r="10" />
              <path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32" />
            </svg>
          </a>
        </div>
      </div>
    </div>
  );
}
