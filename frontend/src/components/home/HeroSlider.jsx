"use client";

import { useState, useEffect, useCallback, useRef } from "react";
import Image from "next/image";
import { resolveImage } from "@/lib/format";

export default function HeroSlider({ banners = [] }) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  const timeoutRef = useRef(null);

  const resetTimeout = useCallback(() => {
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }
  }, []);

  const goToSlide = useCallback(
    (slideIndex) => {
      resetTimeout();
      setCurrentIndex(slideIndex);
    },
    [resetTimeout],
  );

  const goToNext = useCallback(() => {
    if (banners.length === 0) return;
    goToSlide((currentIndex + 1) % banners.length);
  }, [banners.length, currentIndex, goToSlide]);

  const goToPrev = useCallback(() => {
    if (banners.length === 0) return;
    goToSlide((currentIndex - 1 + banners.length) % banners.length);
  }, [banners.length, currentIndex, goToSlide]);

  useEffect(() => {
    if (isPaused || banners.length <= 1) {
      return;
    }
    resetTimeout();
    timeoutRef.current = setTimeout(goToNext, 5000);

    // eslint-disable-next-line consistent-return
    return () => resetTimeout();
  }, [currentIndex, isPaused, banners.length, goToNext, resetTimeout]);

  if (banners.length === 0) return null;
  const activeIndex = Math.min(currentIndex, banners.length - 1);

  return (
    <section className="homepage-section">
      <div
        className="hero-slider"
        onMouseEnter={() => setIsPaused(true)}
        onMouseLeave={() => setIsPaused(false)}
        onFocusCapture={() => setIsPaused(true)}
        onBlurCapture={(event) => {
          if (!event.currentTarget.contains(event.relatedTarget)) setIsPaused(false);
        }}
        aria-roledescription="carousel"
        aria-label="Khuyến mãi nổi bật"
      >
        <div className="sr-only" aria-live="polite" aria-atomic="true">
          Đang hiển thị banner {activeIndex + 1} trên {banners.length}
        </div>

        <div className="slider-wrapper" aria-live="polite">
          {banners.map((slide, index) => (
            <div
              key={slide.id || index}
              className={`hero-slide ${index === activeIndex ? "active" : ""}`}
              aria-hidden={index !== activeIndex}
            >
              <a href={slide.link || "/shop"} className="slide-link" tabIndex={index === activeIndex ? 0 : -1}>
                <Image
                  src={resolveImage(slide.image)}
                  alt={slide.description || "PetWorld Banner"}
                  className="slide-img"
                  fill
                  priority={index === 0}
                  sizes="(max-width: 1340px) calc(100vw - 40px), 1300px"
                />
              </a>
            </div>
          ))}
        </div>

        {banners.length > 1 && (
          <>
            <button type="button" className="slider-arrow prev" onClick={goToPrev} aria-label="Banner trước">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <polyline points="15 18 9 12 15 6" />
              </svg>
            </button>
            <button type="button" className="slider-arrow next" onClick={goToNext} aria-label="Banner tiếp theo">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </button>

            <div className="slider-dots" aria-label="Chọn banner">
              {banners.map((slide, index) => (
                <button
                  type="button"
                  key={slide.id ?? index}
                  className={`slider-dot ${index === activeIndex ? "active" : ""}`}
                  onClick={() => goToSlide(index)}
                  aria-label={`Xem banner ${index + 1}`}
                  aria-current={index === activeIndex ? "true" : undefined}
                />
              ))}
            </div>
          </>
        )}
      </div>
    </section>
  );
}
