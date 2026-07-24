"use client";

import { useRouter } from "next/navigation";
import { useState, useRef, useEffect } from "react";

/**
 * Dropdown "Sắp xếp theo" tùy chỉnh (Custom Dropdown) của trang Tin tức.
 * Giúp đồng bộ giao diện bo góc, màu sắc cam và hover option của thương hiệu PetWorld.
 */
export default function BlogSort({ options = [], value = "newest", query = {} }) {
  const router = useRouter();
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef(null);

  const activeOption = options.find((opt) => opt.value === value) || options[0] || { label: "Mới nhất", value: "newest" };

  const handleSelect = (sortVal) => {
    const params = new URLSearchParams();
    for (const [key, val] of Object.entries(query)) {
      if (key === "sort" || key === "page" || !val) continue;
      params.set(key, val);
    }
    params.set("sort", sortVal);
    const qs = params.toString();
    router.push(qs ? `/news?${qs}` : "/news");
    setIsOpen(false);
  };

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <div className="blog-sort-custom" ref={dropdownRef}>
      <span className="blog-sort-label">Sắp xếp theo:</span>
      <div className="custom-select-wrapper">
        <button
          type="button"
          className={`custom-select-trigger ${isOpen ? "open" : ""}`}
          onClick={() => setIsOpen((prev) => !prev)}
        >
          <span>{activeOption.label}</span>
          <svg
            className={`custom-select-arrow ${isOpen ? "open" : ""}`}
            width="10"
            height="6"
            viewBox="0 0 10 6"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <polyline points="1 1 5 5 9 1" />
          </svg>
        </button>

        {isOpen && (
          <ul className="custom-select-options">
            {options.map((opt) => (
              <li
                key={opt.value}
                className={`custom-select-option ${opt.value === value ? "selected" : ""}`}
                onClick={() => handleSelect(opt.value)}
              >
                {opt.label}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
