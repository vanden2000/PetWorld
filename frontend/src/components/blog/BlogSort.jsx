"use client";

import { useRouter } from "next/navigation";

/**
 * Dropdown "Sắp xếp theo" của trang Tin tức.
 * Giữ nguyên các bộ lọc hiện tại, chỉ đổi tham số sort trên URL.
 */
export default function BlogSort({ options = [], value = "newest", query = {} }) {
  const router = useRouter();

  const handleChange = (event) => {
    const params = new URLSearchParams();
    for (const [key, val] of Object.entries(query)) {
      if (key === "sort" || key === "page" || !val) continue;
      params.set(key, val);
    }
    params.set("sort", event.target.value);
    const qs = params.toString();
    router.push(qs ? `/news?${qs}` : "/news");
  };

  return (
    <div className="shop-sort">
      <span className="shop-sort-label">Sắp xếp theo:</span>
      <select className="shop-sort-select" value={value} onChange={handleChange}>
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
}
