"use client";

import { useState, useCallback } from "react";
import Image from "next/image";
import { resolveImage } from "@/lib/format";

const CTA_CONFIG = {
  "banners/petworld-hero.jpg": { label: "Khám phá ngay" },
  "banners/pet-food-sale.jpg": { label: "Xem ưu đãi" },
  "banners/pet-care.jpg": { label: "Mua sắm ngay" },
  default: { label: "Khám phá ngay" },
};

function imageSource(path, version) {
  const source = resolveImage(path);
  if (!version) return source;

  return `${source}${source.includes("?") ? "&" : "?"}v=${version}`;
}

export default function HeroSlider({ banners = [] }) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const goToSlide = useCallback(
    (slideIndex) => {
      setCurrentIndex(slideIndex);
    },
    [],
  );

  const goToNext = useCallback(() => {
    if (banners.length === 0) return;
    goToSlide((currentIndex + 1) % banners.length);
  }, [banners.length, currentIndex, goToSlide]);

  const goToPrev = useCallback(() => {
    if (banners.length === 0) return;
    goToSlide((currentIndex - 1 + banners.length) % banners.length);
  }, [banners.length, currentIndex, goToSlide]);

  if (banners.length === 0) return null;
  const activeIndex = Math.min(currentIndex, banners.length - 1);

  return (
    <section className="homepage-section hero-slider-section">
      <div
        className="hero-slider"
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
              <div className="slide-image-frame">
                <Image
                  src={imageSource(slide.image, slide.image_version)}
                  alt={slide.description || "PetWorld Banner"}
                  className="slide-img"
                  fill
                  priority={index === 0}
                  sizes="(min-width: 1320px) 1280px, calc(100vw - 40px)"
                />
              </div>
              {slide.link && (
                <a
                  href={slide.link}
                  className="slide-cta"
                  tabIndex={index === activeIndex ? 0 : -1}
                  aria-label={CTA_CONFIG[slide.image]?.label || CTA_CONFIG.default.label}
                  data-cta-label={CTA_CONFIG[slide.image]?.label || CTA_CONFIG.default.label}
                >
                  Xem ưu đãi
                </a>
              )}
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
