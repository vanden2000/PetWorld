"use client";

import { useRef, useState } from "react";
import ProductCard from "@/components/product/ProductCard";
import RecentlyViewed from "@/components/home/RecentlyViewed";
import Link from "next/link";
import { resolveBackendImage } from "@/lib/format";

/**
 * Khối "Phụ Kiện Cho Pet": banner promo cam bên trái + cột phải gồm lưới
 * 4 sản phẩm phụ kiện và hàng "Đã xem gần đây" ngay bên dưới (theo mockup).
 */
export default function AccessoriesPromo({ products = [] }) {
  const sliderRef = useRef(null);
  const [isDragging, setIsDragging] = useState(false);
  const [activeTab, setActiveTab] = useState("all");
  const dragStart = useRef({ x: 0, scrollLeft: 0 });
  const activePointerId = useRef(null);
  const didDrag = useRef(false);

  if (products.length === 0) return null;

  const filteredProducts = products
    .filter((product) => {
      if (activeTab === "phu-kien") return product.category?.slug === "phu-kien";
      if (activeTab === "do-choi") return product.category?.slug === "do-choi";
      return true;
    })
    .slice(0, 10);

  const handleTabChange = (tabSlug) => {
    setActiveTab(tabSlug);
    if (sliderRef.current) {
      sliderRef.current.scrollLeft = 0;
    }
  };

  const scrollByCard = (direction) => {
    const slider = sliderRef.current;
    const item = slider?.querySelector(".new-products-slider-item");
    if (!slider || !item) return;

    const gap = Number.parseFloat(getComputedStyle(slider.firstElementChild).gap) || 20;
    slider.scrollBy({ left: direction * (item.clientWidth + gap), behavior: "smooth" });
  };

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

  const currentCategorySlug = activeTab === "do-choi" ? "do-choi" : "phu-kien";

  return (
    <section className="promo-split-section">
      <div className="promo-card">
        <div className="promo-card-content">
          <span className="promo-tag">🔥 Phụ Kiện & Đồ Chơi</span>
          <h3 className="promo-title">Mua ngay, kẻo lỡ</h3>
          <Link href={`/shop?category=${currentCategorySlug}`} className="promo-btn">
            Ghé Shop Ngay
          </Link>
        </div>
        <img src={resolveBackendImage("storage/promo/accessories.png")} alt="Phụ kiện & đồ chơi cho pet" className="promo-img" />
      </div>

      <div className="promo-right">
        <div className="accessories-promo-header">
          <div className="accessories-promo-tabs">
            <button
              type="button"
              className={`tab-btn ${activeTab === "all" ? "active" : ""}`}
              onClick={() => handleTabChange("all")}
            >
              Tất cả
            </button>
            <button
              type="button"
              className={`tab-btn ${activeTab === "phu-kien" ? "active" : ""}`}
              onClick={() => handleTabChange("phu-kien")}
            >
              Phụ kiện
            </button>
            <button
              type="button"
              className={`tab-btn ${activeTab === "do-choi" ? "active" : ""}`}
              onClick={() => handleTabChange("do-choi")}
            >
              Đồ chơi
            </button>
          </div>
          <Link href={`/shop?category=${currentCategorySlug}`} className="accessories-view-all-link">
            Xem tất cả <span className="arrow">&rarr;</span>
          </Link>
        </div>

        <div className="accessories-slider-container">
          {filteredProducts.length > 4 && (
            <button
              type="button"
              className="slider-nav-btn slider-nav-prev"
              onClick={() => scrollByCard(-1)}
              aria-label="Lướt sang trái"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                <path d="M15 18l-6-6 6-6" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </button>
          )}

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
              {filteredProducts.map((product) => (
                <div className="new-products-slider-item" key={product.id}>
                  <ProductCard product={product} />
                </div>
              ))}
            </div>
          </div>

          {filteredProducts.length > 4 && (
            <button
              type="button"
              className="slider-nav-btn slider-nav-next"
              onClick={() => scrollByCard(1)}
              aria-label="Lướt sang phải"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                <path d="M9 5l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </button>
          )}
        </div>

        <RecentlyViewed />
      </div>
    </section>
  );
}
