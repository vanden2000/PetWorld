"use client";

import { useRef, useState } from "react";
import Link from "next/link";
import { resolveProductImage, useImageFallback } from "@/lib/format";

function StarRating({ value = 5 }) {
  return (
    <div className="review-stars-gold" aria-label={`Đánh giá ${value} trên 5 sao`}>
      {Array.from({ length: 5 }).map((_, i) => (
        <span key={i} className={`star ${i < value ? "filled" : ""}`}>
          ★
        </span>
      ))}
    </div>
  );
}

export default function TestimonialsSection({ reviews = [] }) {
  const sliderRef = useRef(null);
  const [isDragging, setIsDragging] = useState(false);
  const dragStart = useRef({ x: 0, scrollLeft: 0 });
  const activePointerId = useRef(null);
  const didDrag = useRef(false);

  if (!reviews || reviews.length === 0) return null;

  const scrollLeft = () => {
    if (sliderRef.current) {
      sliderRef.current.scrollBy({ left: -360, behavior: "smooth" });
    }
  };

  const scrollRight = () => {
    if (sliderRef.current) {
      sliderRef.current.scrollBy({ left: 360, behavior: "smooth" });
    }
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

  return (
    <section className="homepage-section testimonials-section">
      <div className="section-header-center">
        <h2 className="section-title">Khách Hàng Nói Gì Về PetWorld</h2>
        <p className="section-subtitle">
          100% đánh giá từ các Sen đã trực tiếp mua hàng và trải nghiệm sản phẩm
        </p>
      </div>

      <div className="testimonials-slider-wrapper">
        {reviews.length > 3 && (
          <button
            type="button"
            className="slider-nav-btn slider-nav-prev"
            onClick={scrollLeft}
            aria-label="Xem đánh giá trước"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M15 18l-6-6 6-6" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
        )}

        <div
          ref={sliderRef}
          className={`testimonials-slider${isDragging ? " is-dragging" : ""}`}
          onPointerDown={handlePointerDown}
          onPointerMove={handlePointerMove}
          onPointerUp={handlePointerUp}
          onPointerCancel={handlePointerUp}
          onClickCapture={preventClickWhileDragging}
        >
          <div className="testimonials-track">
            {reviews.map((review) => {
              const userInitial = (review.user_name || "K").charAt(0).toUpperCase();
              const product = review.product;

              return (
                <div className="testimonial-card" key={review.id}>
                  <div className="testimonial-card-header">
                    <div className="reviewer-info">
                      <div className="reviewer-avatar">{userInitial}</div>
                      <div>
                        <h4 className="reviewer-name">{review.user_name}</h4>
                        <div className="verified-buyer-badge">
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                          </svg>
                          Đã mua hàng
                        </div>
                      </div>
                    </div>
                    <StarRating value={review.rating} />
                  </div>

                  <div className="testimonial-body">
                    <span className="quote-mark">&ldquo;</span>
                    <p className="testimonial-comment">{review.comment}</p>
                  </div>

                  {product && (
                    <Link href={`/shop/${product.slug}`} className="testimonial-product-preview">
                      <img
                        src={resolveProductImage(product.image)}
                        alt={product.name}
                        className="testimonial-product-img"
                        onError={useImageFallback}
                      />
                      <div className="testimonial-product-details">
                        <span className="product-label">Sản phẩm đã mua</span>
                        <h5 className="product-title">{product.name}</h5>
                      </div>
                    </Link>
                  )}
                </div>
              );
            })}
          </div>
        </div>

        {reviews.length > 3 && (
          <button
            type="button"
            className="slider-nav-btn slider-nav-next"
            onClick={scrollRight}
            aria-label="Xem đánh giá tiếp"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M9 5l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
        )}
      </div>
    </section>
  );
}
