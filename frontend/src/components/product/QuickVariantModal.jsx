"use client";

import { useState, useEffect, useMemo } from "react";
import { createPortal } from "react-dom";
import { resolveProductImage, formatPrice } from "@/lib/format";
import { addToCart, checkQuantity } from "@/lib/cart";
import { toastSuccess, toastError, toastInfo } from "@/lib/toast";

export default function QuickVariantModal({ isOpen, onClose, product }) {
  const [mounted, setMounted] = useState(false);

  // Mount logic for Portal SSR safety (bypassing synchronous setState in effect)
  useEffect(() => {
    let active = true;
    setTimeout(() => {
      if (active) setMounted(true);
    }, 0);
    return () => {
      active = false;
    };
  }, []);

  const variants = useMemo(() => product?.variants ?? [], [product]);

  // Mặc định chọn biến thể đầu tiên có sẵn hàng hoặc giá thấp nhất
  const defaultVariant = useMemo(() => {
    if (!variants.length) return null;
    return [...variants].sort((a, b) => a.effective_price - b.effective_price)[0] ?? null;
  }, [variants]);

  // Trạng thái được khởi tạo ngay lúc mount nhờ key reset ngoài AddToCartButton
  const [selectedOptions, setSelectedOptions] = useState(() =>
    Object.fromEntries(
      (defaultVariant?.options ?? []).map((option) => [option.type_id, option.value]),
    ),
  );
  const [quantity, setQuantity] = useState(1);

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

  // Đóng modal khi nhấn phím Escape
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === "Escape") onClose();
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [onClose]);

  // Bỏ toàn bộ early return xuống dưới cùng sau khi đã khai báo các Hook
  if (!isOpen || !product || !mounted) return null;

  const selectedVariant = variants.find((variant) =>
    variant.options?.length === Object.keys(selectedOptions).length
      && variant.options.every(
        (option) => selectedOptions[option.type_id] === option.value,
      ),
  ) ?? null;
  const currentPrice = selectedVariant ? selectedVariant.effective_price : (product.price?.min ?? 0);
  const oldPrice = selectedVariant?.sale_price ? selectedVariant.price : null;
  const inStock = (selectedVariant?.quantity ?? 0) > 0;

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

  const handleAddToCart = () => {
    if (!selectedVariant || !inStock) {
      toastError("Vui lòng chọn phân loại còn hàng.");
      return;
    }

    if (!checkQuantity(product.id, selectedVariant.id, quantity, selectedVariant.quantity)) {
      toastError(`Trong giỏ đã có sản phẩm này. Bạn chỉ có thể mua tối đa ${selectedVariant.quantity}.`);
      return;
    }

    const added = addToCart(
      {
        productId: product.id,
        slug: product.slug,
        name: product.name,
        image: product.image,
        variantId: selectedVariant ? selectedVariant.id : null,
        variantName: selectedVariant ? selectedVariant.name : null,
        price: currentPrice,
        oldPrice,
        stockQuantity: selectedVariant.quantity,
      },
      quantity
    );

    if (!added) return;

    toastSuccess(`Đã thêm ${quantity} x "${product.name}${selectedVariant ? ` (${selectedVariant.name})` : ""}" vào giỏ hàng`);
    onClose();
  };

  return createPortal(
    <div className="qvm-overlay" onClick={onClose}>
      <div className="qvm-modal" onClick={(e) => e.stopPropagation()}>
        <button className="qvm-close-btn" onClick={onClose} aria-label="Đóng">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>

        <div className="qvm-prod-info">
          <img src={resolveProductImage(product.image)} alt={product.name} className="qvm-prod-img" />
          <div className="qvm-prod-meta">
            <h4 className="qvm-prod-name">{product.name}</h4>
            <div className="qvm-price-row">
              {oldPrice ? <span className="qvm-price-old">{formatPrice(oldPrice)}</span> : null}
              <span className="qvm-price-current">{formatPrice(currentPrice)}</span>
            </div>
            <span className={`qvm-stock-status ${!inStock ? "out-of-stock" : ""}`}>
              {inStock
                ? `Còn ${selectedVariant.quantity} sản phẩm`
                : "Hết hàng"}
            </span>
            {product.short_description && (
              <p className="qvm-short-description">{product.short_description}</p>
            )}
          </div>
        </div>

        <div className="qvm-body">
          {variantGroups.map((group) => (
            <div className="qvm-variant-group" key={group.id}>
              <span className="qvm-group-title">{group.name}:</span>
              <div className="qvm-options-list">
                {group.values.map((value) => {
                  const isOptStock = optionIsAvailable(group.id, value);
                  return (
                    <button
                      key={value}
                      type="button"
                      className={`qvm-option-chip ${selectedOptions[group.id] === value ? "active" : ""}`}
                      onClick={() => {
                        setSelectedOptions((current) => ({ ...current, [group.id]: value }));
                        setQuantity(1);
                      }}
                      disabled={!isOptStock}
                      title={!isOptStock ? "Hết hàng" : ""}
                    >
                      {value}
                    </button>
                  );
                })}
              </div>
            </div>
          ))}
        </div>

        <div className="qvm-footer">
          <div className="qvm-qty-selector">
            <button
              type="button"
              className="qvm-qty-btn"
              onClick={() => setQuantity((prev) => Math.max(1, prev - 1))}
              disabled={quantity <= 1}
            >
              -
            </button>
            <span className="qvm-qty-val">{quantity}</span>
            <button
              type="button"
              className="qvm-qty-btn"
              onClick={() => {
                const maxStock = selectedVariant?.quantity ?? product.stock_quantity ?? 99;
                if (quantity >= maxStock) {
                  toastInfo(`Biến thể này chỉ còn ${maxStock} sản phẩm trong kho.`);
                  return;
                }
                setQuantity((prev) => prev + 1);
              }}
              disabled={!selectedVariant}
            >
              +
            </button>
          </div>

          <button
            type="button"
            className="qvm-add-btn"
            onClick={handleAddToCart}
            disabled={!inStock}
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="9" cy="21" r="1" />
              <circle cx="20" cy="21" r="1" />
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
            </svg>
            Thêm vào giỏ
          </button>
        </div>
      </div>
    </div>,
    document.body
  );
}
