"use client";

import { useEffect, useState } from "react";

const SHOW_AFTER_SCROLL = 500;

export default function BackToTopButton() {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const updateVisibility = () => setIsVisible(window.scrollY > SHOW_AFTER_SCROLL);

    updateVisibility();
    window.addEventListener("scroll", updateVisibility, { passive: true });

    return () => window.removeEventListener("scroll", updateVisibility);
  }, []);

  const scrollToTop = () => window.scrollTo({ top: 0, behavior: "smooth" });

  return (
    <button
      type="button"
      className={`back-to-top${isVisible ? " is-visible" : ""}`}
      onClick={scrollToTop}
      aria-label="Lên đầu trang"
      title="Lên đầu trang"
      tabIndex={isVisible ? 0 : -1}
    >
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="m6 14 6-6 6 6" />
      </svg>
    </button>
  );
}
