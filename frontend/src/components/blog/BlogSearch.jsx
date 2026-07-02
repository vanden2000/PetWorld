"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";

export default function BlogSearch({ initialSearch = "", query = {} }) {
  const router = useRouter();
  const [keyword, setKeyword] = useState(initialSearch);

  const handleSubmit = (event) => {
    event.preventDefault();
    const params = new URLSearchParams();
    
    // Giữ lại các bộ lọc hiện tại trừ search và page
    for (const [key, val] of Object.entries(query)) {
      if (key === "search" || key === "page" || !val) continue;
      params.set(key, val);
    }
    
    if (keyword.trim()) {
      params.set("search", keyword.trim());
    }
    
    const qs = params.toString();
    router.push(qs ? `/news?${qs}` : "/news");
  };

  return (
    <form className="search-container" onSubmit={handleSubmit} style={{ width: "240px", height: "38px" }}>
      <input
        type="text"
        className="search-input"
        placeholder="Tìm kiếm bài viết..."
        aria-label="Tìm kiếm"
        value={keyword}
        onChange={(event) => setKeyword(event.target.value)}
        style={{ fontSize: "13.5px" }}
      />
      <button type="submit" className="search-button" aria-label="Tìm kiếm nút" style={{ width: "28px", height: "28px" }}>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </button>
    </form>
  );
}
