"use client";

import { useRef, useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import Link from "next/link";

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

  if (products.length === 0) return null;

  const handlePointerDown = (event) => {
    const slider = sliderRef.current;
    if (!slider) return;

    slider.setPointerCapture(event.pointerId);
    dragStart.current = {
      x: event.clientX,
      scrollLeft: slider.scrollLeft,
    };
    setIsDragging(false);
  };

  const handlePointerMove = (event) => {
    const slider = sliderRef.current;
    if (!slider || !slider.hasPointerCapture(event.pointerId)) return;

    const distance = event.clientX - dragStart.current.x;
    if (Math.abs(distance) > 5) setIsDragging(true);
    slider.scrollLeft = dragStart.current.scrollLeft - distance;
  };

  const handlePointerUp = (event) => {
    const slider = sliderRef.current;
    if (slider?.hasPointerCapture(event.pointerId)) {
      slider.releasePointerCapture(event.pointerId);
    }
    window.setTimeout(() => setIsDragging(false), 0);
  };

  const preventClickWhileDragging = (event) => {
    if (!isDragging) return;
    event.preventDefault();
    event.stopPropagation();
  };

  return (
    <section className="homepage-section">
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
        <img src="/image/promo/sidebar-pets.png" alt="" className="sidebar-illustration" aria-hidden="true" />
      </aside>

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
      </div>
    </section>
  );
}
