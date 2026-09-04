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
  const [activeIndex, setActiveIndex] = useState(0);
  const dragStart = useRef({ x: 0, scrollLeft: 0 });
  const activePointerId = useRef(null);
  const didDrag = useRef(false);

  if (!reviews || reviews.length === 0) return null;

  const scrollByCard = (direction) => {
    const slider = sliderRef.current;
    const card = slider?.querySelector(".testimonial-card");
    const track = slider?.querySelector(".testimonials-track");
    if (!slider || !card || !track) return;

    const gap = Number.parseFloat(getComputedStyle(track).gap) || 20;
    slider.scrollBy({ left: direction * (card.offsetWidth + gap), behavior: "smooth" });
  };

  const scrollToReview = (index) => {
    const slider = sliderRef.current;
    const cards = slider?.querySelectorAll(".testimonial-card");
    const card = cards?.[index];
    if (!slider || !card) return;

    slider.scrollTo({ left: card.offsetLeft, behavior: "smooth" });
  };

  const handleScroll = (event) => {
    const slider = event.currentTarget;
    const card = slider.querySelector(".testimonial-card");
    const track = slider.querySelector(".testimonials-track");
    if (!card || !track) return;

    const gap = Number.parseFloat(getComputedStyle(track).gap) || 20;
    const nextIndex = Math.min(
      reviews.length - 1,
      Math.max(0, Math.round(slider.scrollLeft / (card.offsetWidth + gap))),
    );
    setActiveIndex((currentIndex) => (currentIndex === nextIndex ? currentIndex : nextIndex));
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
        {reviews.length > 2 && (
          <button
            type="button"
            className="slider-nav-btn slider-nav-prev"
            onClick={() => scrollByCard(-1)}
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
          onScroll={handleScroll}
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

        {reviews.length > 2 && (
          <button
            type="button"
            className="slider-nav-btn slider-nav-next"
            onClick={() => scrollByCard(1)}
            aria-label="Xem đánh giá tiếp"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M9 5l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
        )}
      </div>

      {reviews.length > 1 && (
        <div className="testimonials-pagination" aria-label="Chọn đánh giá">
          {reviews.map((review, index) => (
            <button
              key={review.id}
              type="button"
              className={`testimonials-pagination-dot${index === activeIndex ? " is-active" : ""}`}
              onClick={() => scrollToReview(index)}
              aria-label={`Xem đánh giá ${index + 1}`}
              aria-current={index === activeIndex ? "true" : undefined}
            />
          ))}
        </div>
      )}
    </section>
  );
}
