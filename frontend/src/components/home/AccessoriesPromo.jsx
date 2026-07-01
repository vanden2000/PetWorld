"use client";

import { useRef, useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import RecentlyViewed from "@/components/home/RecentlyViewed";
import Link from "next/link";

/**
 * Khối "Phụ Kiện Cho Pet": banner promo cam bên trái + cột phải gồm lưới
 * 4 sản phẩm phụ kiện và hàng "Đã xem gần đây" ngay bên dưới (theo mockup).
 */
export default function AccessoriesPromo({ products = [] }) {
  const sliderRef = useRef(null);
  const [isDragging, setIsDragging] = useState(false);
  const dragStart = useRef({ x: 0, scrollLeft: 0 });
  const activePointerId = useRef(null);
  const didDrag = useRef(false);

  if (products.length === 0) return null;

  const handlePointerDown = (event) => {
    const slider = sliderRef.current;
    if (!slider) return;

    activePointerId.current = event.pointerId;
    didDrag.current = false;
    dragStart.current = {
      x: event.clientX,
      scrollLeft: slider.scrollLeft,
    };
    setIsDragging(false);
  };

  const handlePointerMove = (event) => {
    const slider = sliderRef.current;
    if (!slider || activePointerId.current !== event.pointerId) return;

    const distance = event.clientX - dragStart.current.x;
    if (Math.abs(distance) <= 5) return;

    if (!slider.hasPointerCapture(event.pointerId)) {
      slider.setPointerCapture(event.pointerId);
    }
    didDrag.current = true;
    setIsDragging(true);
    slider.scrollLeft = dragStart.current.scrollLeft - distance;
  };

  const handlePointerUp = (event) => {
    const slider = sliderRef.current;
    if (slider?.hasPointerCapture(event.pointerId)) {
      slider.releasePointerCapture(event.pointerId);
    }
    activePointerId.current = null;
    window.setTimeout(() => {
      didDrag.current = false;
      setIsDragging(false);
    }, 0);
  };

  const preventClickWhileDragging = (event) => {
    if (!didDrag.current) return;
    event.preventDefault();
    event.stopPropagation();
  };

  return (
    <section className="promo-split-section">
      <div className="promo-card">
        <div className="promo-card-content">
          <span className="promo-tag">🔥 Phụ Kiện Cho Pet</span>
          <h3 className="promo-title">Mua ngay, kẻo lỡ</h3>
          <Link href="/shop?category=phu-kien" className="promo-btn">
            Ghé Shop Ngay
          </Link>
        </div>
        <img src="/image/promo/accessories.png" alt="Phụ kiện cho pet" className="promo-img" />
      </div>

      <div className="promo-right">
        <div
          ref={sliderRef}
          className={`new-products-slider${isDragging ? " is-dragging" : ""}`}
          onPointerDown={handlePointerDown}
          onPointerMove={handlePointerMove}
          onPointerUp={handlePointerUp}
          onPointerCancel={handlePointerUp}
          onClickCapture={preventClickWhileDragging}
        >
          <div className="new-products-slider-track">
            {products.map((product) => (
              <div className="new-products-slider-item" key={product.id}>
                <ProductCard product={product} />
              </div>
            ))}
          </div>
        </div>
        <RecentlyViewed />
      </div>
    </section>
  );
}
