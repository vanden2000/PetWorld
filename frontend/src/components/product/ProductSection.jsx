"use client";

import { useState, useEffect, useRef } from "react";
import Link from "next/link";
import ProductCard from "@/components/product/ProductCard";

const GRID_CLASS = {
  3: "products-grid-3",
  4: "products-grid-4",
  5: "products-grid-5",
};

/**
 * Khối hiển thị một danh sách sản phẩm: tiêu đề + link "xem tất cả" + lưới card hoặc slider.
 * Dùng lại cho "Sản Phẩm Được Tuyển Chọn", "Best Selling"...
 */
export default function ProductSection({
  title,
  products = [],
  columns = 5,
  viewAllHref = "/shop",
  badge,
  // Mỗi khối ở trang chủ chỉ hiển thị 1 hàng (mặc định = số cột); xem đầy đủ ở trang Cửa Hàng.
  limit = columns,
  isSlider = false,
}) {
  if (!products.length) return null;

  const visibleProducts = products.slice(0, limit);

  if (!isSlider) {
    return (
      <section className="homepage-section">
        <div className="section-header">
          <h2 className="section-title">{title}</h2>
          <Link href={viewAllHref} className="view-all-link">
            xem tất cả ➔
          </Link>
        </div>

        <div className={GRID_CLASS[columns] || GRID_CLASS[5]}>
          {visibleProducts.map((product) => (
            <ProductCard key={product.id} product={product} badge={badge} />
          ))}
        </div>
      </section>
    );
  }

  return (
    <ProductSliderSection
      title={title}
      products={visibleProducts}
      viewAllHref={viewAllHref}
      badge={badge}
    />
  );
}

function ProductSliderSection({ title, products, viewAllHref, badge }) {
  const scrollRef = useRef(null);
  const [isDown, setIsDown] = useState(false);
  const [startX, setStartX] = useState(0);
  const [scrollLeftState, setScrollLeftState] = useState(0);
  const [isHovered, setIsHovered] = useState(false);
  const [isDragging, setIsDragging] = useState(false);

  // Auto scroll effect
  useEffect(() => {
    if (isDown || isHovered) return;

    const interval = setInterval(() => {
      const container = scrollRef.current;
      if (!container) return;

      const item = container.querySelector(".product-slider-item");
      if (!item) return;

      const itemWidth = item.clientWidth;
      const gap = 20; // gap 20px
      const step = itemWidth + gap;

      const maxScroll = container.scrollWidth - container.clientWidth;
      // Tránh sai số nhỏ khi cuộn
      if (container.scrollLeft >= maxScroll - 10) {
        container.scrollTo({ left: 0, behavior: "smooth" });
      } else {
        container.scrollTo({ left: container.scrollLeft + step, behavior: "smooth" });
      }
    }, 4000); // Tự động trượt sau mỗi 4 giây

    return () => clearInterval(interval);
  }, [isDown, isHovered]);

  const handleMouseDown = (e) => {
    setIsDown(true);
    setIsDragging(false);
    setStartX(e.pageX - scrollRef.current.offsetLeft);
    setScrollLeftState(scrollRef.current.scrollLeft);
  };

  const handleMouseLeave = () => {
    setIsDown(false);
  };

  const handleMouseUp = () => {
    setIsDown(false);
    // Trì hoãn việc tắt isDragging một chút để sự kiện click được chụp
    setTimeout(() => {
      setIsDragging(false);
    }, 50);
  };

  const handleMouseMove = (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - scrollRef.current.offsetLeft;
    const walk = (x - startX) * 1.5;
    if (Math.abs(walk) > 5) {
      setIsDragging(true);
    }
    scrollRef.current.scrollLeft = scrollLeftState - walk;
  };

  const handleDragStart = (e) => {
    e.preventDefault();
  };

  const handleClickCapture = (e) => {
    if (isDragging) {
      e.preventDefault();
      e.stopPropagation();
    }
  };

  return (
    <section
      className="homepage-section"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      <div className="section-header">
        <h2 className="section-title">{title}</h2>
        <Link href={viewAllHref} className="view-all-link">
          xem tất cả ➔
        </Link>
      </div>

      <div
        ref={scrollRef}
        className="product-slider-container"
        onMouseDown={handleMouseDown}
        onMouseLeave={handleMouseLeave}
        onMouseUp={handleMouseUp}
        onMouseMove={handleMouseMove}
        onDragStart={handleDragStart}
        onClickCapture={handleClickCapture}
        style={{
          cursor: isDown ? "grabbing" : "grab",
        }}
      >
        <div className="product-slider-track">
          {products.map((product) => (
            <div key={product.id} className="product-slider-item">
              <ProductCard product={product} badge={badge} />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
