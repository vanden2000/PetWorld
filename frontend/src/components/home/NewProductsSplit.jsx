"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import Link from "next/link";
import { resolveBackendImage } from "@/lib/format";

const SIDEBAR_LINKS = [
  "Ưu đãi",
  "Ưu đãi chớp chợ",
  "Được chọn lọc kỹ lưỡng",
  "Sản phẩm tuyển chọn",
  "Mới được thêm gần đây",
];

/**
 * Khối "Sản Phẩm mới": sidebar tối bên trái + lưới 4 sản phẩm bên phải.
 */
export default function NewProductsSplit({ products = [] }) {
  const sliderRef = useRef(null);
  const [isDragging, setIsDragging] = useState(false);
  const dragStart = useRef({ x: 0, scrollLeft: 0 });
  const activePointerId = useRef(null);
  const didDrag = useRef(false);
  const [canScrollPrev, setCanScrollPrev] = useState(false);
  const [canScrollNext, setCanScrollNext] = useState(false);

  const updateScrollControls = useCallback(() => {
    const slider = sliderRef.current;
    if (!slider) return;

    const maxScroll = slider.scrollWidth - slider.clientWidth;
    setCanScrollPrev(slider.scrollLeft > 4);
    setCanScrollNext(slider.scrollLeft < maxScroll - 4);
  }, []);

  useEffect(() => {
    const slider = sliderRef.current;
    if (!slider) return undefined;

    updateScrollControls();
    slider.addEventListener("scroll", updateScrollControls, { passive: true });
    window.addEventListener("resize", updateScrollControls);

    return () => {
      slider.removeEventListener("scroll", updateScrollControls);
      window.removeEventListener("resize", updateScrollControls);
    };
  }, [products.length, updateScrollControls]);

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

  const scrollByCard = (direction) => {
    const slider = sliderRef.current;
    const item = slider?.querySelector(".new-products-slider-item");
    if (!slider || !item) return;

    const gap = Number.parseFloat(getComputedStyle(slider.firstElementChild).gap) || 20;
    slider.scrollBy({ left: direction * (item.clientWidth + gap), behavior: "smooth" });
  };

  return (
    <section className="homepage-section new-products-section">
      <div className="section-header">
        <h2 className="section-title">Sản Phẩm Mới</h2>
        <Link href="/shop" className="view-all-link">
          xem tất cả ➔
        </Link>
      </div>

      <div className="split-section split-section--in-section">
      <aside className="category-sidebar">
        <ul className="sidebar-menu">
          {SIDEBAR_LINKS.map((label) => (
            <li key={label}>
              <Link href="/shop" className="sidebar-link">
                {label}
              </Link>
            </li>
          ))}
        </ul>
        <img src={resolveBackendImage("storage/promo/sidebar-pets.png")} alt="" className="sidebar-illustration" aria-hidden="true" />
      </aside>

      <button
        type="button"
        className="new-products-slider-arrow new-products-slider-arrow-prev"
        onClick={() => scrollByCard(-1)}
        disabled={!canScrollPrev}
        aria-label="Sản phẩm trước"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <polyline points="15 18 9 12 15 6" />
        </svg>
      </button>

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
              <ProductCard product={product} badge="New" />
            </div>
          ))}
        </div>
      </div>
      <button
        type="button"
        className="new-products-slider-arrow new-products-slider-arrow-next"
        onClick={() => scrollByCard(1)}
        disabled={!canScrollNext}
        aria-label="Sản phẩm tiếp theo"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <polyline points="9 18 15 12 9 6" />
        </svg>
      </button>
      </div>
    </section>
  );
}
