"use client";

import { useState, useEffect, useCallback } from "react";
import { resolveImage } from "@/lib/format";

export default function HeroSlider({ banners = [] }) {
  const [currentIndex, setCurrentIndex] = useState(0);

  const nextSlide = useCallback(() => {
    if (banners.length === 0) return;
    setCurrentIndex((prev) => (prev + 1) % banners.length);
  }, [banners.length]);

  const prevSlide = () => {
    if (banners.length === 0) return;
    setCurrentIndex((prev) => (prev - 1 + banners.length) % banners.length);
  };

  useEffect(() => {
    if (banners.length === 0) return;
    const timer = setInterval(nextSlide, 5000);
    return () => clearInterval(timer);
  }, [nextSlide, banners.length]);

  if (banners.length === 0) return null;

  return (
    <section className="homepage-section">
      <div className="hero-slider">
        <div className="slider-wrapper">
          {banners.map((slide, index) => (
            <div
              key={slide.id || index}
              className={`hero-slide ${index === currentIndex ? "active" : ""}`}
              style={{
                opacity: index === currentIndex ? 1 : 0,
                visibility: index === currentIndex ? "visible" : "hidden",
                transition: "opacity 0.6s ease-in-out, visibility 0.6s ease-in-out",
              }}
            >
              <a href={slide.link || "#"} className="slide-link">
                <img
                  src={resolveImage(slide.image)}
                  alt={slide.description || "PetWorld Banner"}
                  className="slide-img"
                />
              </a>
            </div>
          ))}
        </div>

        <button className="slider-arrow prev" onClick={prevSlide} aria-label="Previous slide">
          &#10094;
        </button>
        <button className="slider-arrow next" onClick={nextSlide} aria-label="Next slide">
          &#10095;
        </button>

        <div className="slider-dots">
          {banners.map((_, index) => (
            <span
              key={index}
              className={`slider-dot ${index === currentIndex ? "active" : ""}`}
              onClick={() => setCurrentIndex(index)}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
