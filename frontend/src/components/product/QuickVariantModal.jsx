"use client";

import { useState, useEffect, useMemo } from "react";
import { createPortal } from "react-dom";
import { resolveImage, formatPrice } from "@/lib/format";
import { addToCart } from "@/lib/cart";
import { toastSuccess, toastError } from "@/lib/toast";

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
  const [selectedVariantId, setSelectedVariantId] = useState(defaultVariant?.id ?? null);
  const [quantity, setQuantity] = useState(1);

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

  const selectedVariant = variants.find((v) => v.id === selectedVariantId) ?? defaultVariant;
  const currentPrice = selectedVariant ? selectedVariant.effective_price : (product.price?.min ?? 0);
  const oldPrice = selectedVariant?.sale_price ? selectedVariant.price : null;
  const inStock = (selectedVariant?.quantity ?? product.stock_quantity ?? 0) > 0;

  // Gom các biến thể theo loại
  const variantGroups = (() => {
    const groups = new Map();
    for (const v of variants) {
      const typeName = v.type?.name ?? "Phân loại";
      if (!groups.has(typeName)) {
        groups.set(typeName, []);
      }
      groups.get(typeName).push(v);
    }
    return [...groups.entries()];
  })();

  const handleAddToCart = () => {
    if (variants.length > 0 && (!selectedVariant || !inStock)) {
      toastError("Vui lòng chọn phân loại còn hàng.");
      return;
    }

    addToCart(
      {
        productId: product.id,
        slug: product.slug,
        name: product.name,
        image: product.image,
        variantId: selectedVariant ? selectedVariant.id : null,
        variantName: selectedVariant ? selectedVariant.name : null,
        price: currentPrice,
        oldPrice,
      },
      quantity
    );

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
          <img src={resolveImage(product.image)} alt={product.name} className="qvm-prod-img" />
          <div className="qvm-prod-meta">
            <h4 className="qvm-prod-name">{product.name}</h4>
            <div className="qvm-price-row">
              {oldPrice ? <span className="qvm-price-old">{formatPrice(oldPrice)}</span> : null}
              <span className="qvm-price-current">{formatPrice(currentPrice)}</span>
            </div>
            <span className={`qvm-stock-status ${!inStock ? "out-of-stock" : ""}`}>
              {inStock ? "Còn hàng" : "Hết hàng"}
            </span>
          </div>
        </div>

        <div className="qvm-body">
          {variantGroups.map(([typeName, groupVariants]) => (
            <div className="qvm-variant-group" key={typeName}>
              <span className="qvm-group-title">{typeName}:</span>
              <div className="qvm-options-list">
                {groupVariants.map((v) => {
                  const isOptStock = v.quantity > 0;
                  return (
                    <button
                      key={v.id}
                      type="button"
                      className={`qvm-option-chip ${selectedVariantId === v.id ? "active" : ""}`}
                      onClick={() => setSelectedVariantId(v.id)}
                      disabled={!isOptStock}
                      title={!isOptStock ? "Hết hàng" : ""}
                    >
                      {v.name}
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
                setQuantity((prev) => (prev < maxStock ? prev + 1 : prev));
              }}
              disabled={quantity >= (selectedVariant?.quantity ?? product.stock_quantity ?? 99)}
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
