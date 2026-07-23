"use client";

import { useEffect, useState, useCallback, useRef } from "react";

export default function TableOfContents({ selector = ".news-article-body" }) {
  const [tocItems, setTocItems] = useState([]);
  const [activeId, setActiveId] = useState("");
  const [isOpenMobile, setIsOpenMobile] = useState(true);
  const isClickingRef = useRef(false);
  const scrollTimerRef = useRef(null);

  const buildToc = useCallback(() => {
    const items = [];

    // 1. Giới thiệu (nếu có mô tả ngắn)
    const introEl = document.getElementById("news-intro");
    if (introEl) {
      items.push({
        id: "news-intro",
        text: "Giới thiệu",
        level: 2,
        numberStr: "",
      });
    }

    // 2. Lấy tất cả các thẻ h1, h2, h3 trong bài viết
    const container = document.querySelector(selector);
    if (container) {
      const headings = Array.from(container.querySelectorAll("h1, h2, h3, h4"));
      let mainIndex = 0;

      headings.forEach((heading, idx) => {
        let id = heading.id;
        if (!id) {
          id = `toc-heading-${idx}`;
          heading.id = id;
        }

        const tag = heading.tagName.toLowerCase();
        const level = parseInt(tag.replace("h", ""), 10);

        let rawText = heading.innerText.trim();
        let text = rawText;

        if (level === 2 || level === 1) {
          const match = rawText.match(/^(\d+)\.\s*(.*)/);
          if (match) {
            const num = match[1];
            const rest = match[2];
            if (!heading.querySelector(".heading-num-badge")) {
              heading.innerHTML = `<span class="heading-num-badge">${num}</span><span>${rest}</span>`;
            }
            text = `${num}. ${rest}`;
          } else {
            mainIndex++;
            if (!heading.querySelector(".heading-num-badge")) {
              heading.innerHTML = `<span class="heading-num-badge">${mainIndex}</span><span>${rawText}</span>`;
            }
            text = `${mainIndex}. ${rawText}`;
          }
        }

        items.push({
          id,
          text,
          level,
          numberStr: "",
        });
      });
    }

    // 3. Xem thêm (nếu có bài viết liên quan)
    const relatedEl = document.getElementById("bai-viet-lien-quan");
    if (relatedEl) {
      items.push({
        id: "bai-viet-lien-quan",
        text: "Xem thêm",
        level: 2,
        numberStr: "",
      });
    }

    // 4. Bình luận (nếu có phần bình luận)
    const commentsEl = document.getElementById("blog-comments-section");
    if (commentsEl) {
      items.push({
        id: "blog-comments-section",
        text: "Bình luận",
        level: 2,
        numberStr: "",
      });
    }

    setTocItems(items);
  }, [selector]);

  useEffect(() => {
    const timer = setTimeout(() => {
      buildToc();
    }, 100);

    return () => clearTimeout(timer);
  }, [buildToc]);

  // Cập nhật mục active chính xác theo vị trí cuộn
  useEffect(() => {
    if (tocItems.length === 0) return;

    const handleScroll = () => {
      // Khi vừa click, tạm khóa lắng nghe cuộn tự động để không nhảy sai mục
      if (isClickingRef.current) return;

      const headerOffset = 110;
      const scrollPosition = window.scrollY + headerOffset;

      let currentId = "";

      for (let i = tocItems.length - 1; i >= 0; i--) {
        const item = tocItems[i];
        const el = document.getElementById(item.id);
        if (el) {
          const top = el.getBoundingClientRect().top + window.pageYOffset;
          if (scrollPosition >= top - 25) {
            currentId = item.id;
            break;
          }
        }
      }

      if (currentId) {
        setActiveId(currentId);
      }
    };

    window.addEventListener("scroll", handleScroll, { passive: true });
    handleScroll();

    return () => window.removeEventListener("scroll", handleScroll);
  }, [tocItems]);

  const scrollToId = (e, id) => {
    e.preventDefault();
    const element = document.getElementById(id);
    if (element) {
      // Khóa lắng nghe cuộn tự động trong lúc hiệu ứng cuộn đang diễn ra
      isClickingRef.current = true;
      setActiveId(id);

      const headerOffset = 95;
      const elementPosition = element.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

      window.scrollTo({
        top: offsetPosition,
        behavior: "smooth",
      });

      if (scrollTimerRef.current) clearTimeout(scrollTimerRef.current);
      scrollTimerRef.current = setTimeout(() => {
        isClickingRef.current = false;
      }, 800);
    }
  };

  if (tocItems.length === 0) {
    return null;
  }

  return (
    <nav className="toc-wrapper" aria-label="Mục lục bài viết">
      <div className="toc-card">
        <div className="toc-header">
          <span className="toc-title">MỤC LỤC</span>
          <button
            type="button"
            className="toc-toggle-btn"
            onClick={() => setIsOpenMobile((prev) => !prev)}
            aria-label="Ẩn/Hiện mục lục"
          >
            {isOpenMobile ? "▲" : "▼"}
          </button>
        </div>

        {isOpenMobile && (
          <ul className="toc-list">
            {tocItems.map((item) => (
              <li
                key={item.id}
                className={`toc-item level-${item.level} ${
                  activeId === item.id ? "active" : ""
                }`}
              >
                <a
                  href={`#${item.id}`}
                  className={`toc-link ${activeId === item.id ? "active" : ""}`}
                  onClick={(e) => scrollToId(e, item.id)}
                >
                  {item.numberStr && (
                    <span className="toc-number">{item.numberStr}</span>
                  )}
                  <span className="toc-text">{item.text}</span>
                </a>
              </li>
            ))}
          </ul>
        )}
      </div>
    </nav>
  );
}
