"use client";

import React from "react";
import Link from "next/link";

export default function SmartSearchFlyout({
  isOpen,
  keyword,
  loading,
  data,
  onClose,
}) {
  if (!isOpen) return null;

  const { products = [], total_products = 0 } = data || {};
  const trimmed = keyword.trim();

  return (
    <div
      className="smart-search-flyout google-style-flyout"
      role="listbox"
      aria-label="Gợi ý tìm kiếm"
      onMouseDown={(e) => {
        // Tránh mất focus ô tìm kiếm khi click chuột vào dropdown
        e.stopPropagation();
      }}
    >
      {loading && (
        <div className="google-search-loading">
          <svg
            className="google-search-spinner"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2.5"
          >
            <circle cx="12" cy="12" r="10" strokeDasharray="32" strokeDashoffset="12" />
          </svg>
          <span>Đang tìm kiếm...</span>
        </div>
      )}

      {!loading && products.length === 0 && trimmed && (
        <div className="google-search-empty">
          <span>Không tìm thấy kết quả phù hợp cho &ldquo;{trimmed}&rdquo;</span>
        </div>
      )}

      {!loading && products.length > 0 && (
        <div className="google-search-list">
          {products.map((product) => (
            <Link
              key={product.id}
              href={`/shop/${product.slug}`}
              className="google-search-item"
              onClick={onClose}
            >
              <span className="google-search-icon">
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2.2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                >
                  <circle cx="11" cy="11" r="8" />
                  <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
              </span>
              <span className="google-search-text">{product.name}</span>
            </Link>
          ))}
        </div>
      )}

      {trimmed && (
        <Link
          href={`/shop?search=${encodeURIComponent(trimmed)}`}
          className="google-search-item google-search-all-link"
          onClick={onClose}
        >
          <span className="google-search-icon">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2.2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <circle cx="11" cy="11" r="8" />
              <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
          </span>
          <span className="google-search-text">
            Tìm kiếm &ldquo;<strong>{trimmed}</strong>&rdquo; trong cửa hàng
          </span>
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2.5"
            strokeLinecap="round"
            strokeLinejoin="round"
            style={{ marginLeft: "auto", opacity: 0.6 }}
          >
            <polyline points="9 18 15 12 9 6" />
          </svg>
        </Link>
      )}
    </div>
  );
}
