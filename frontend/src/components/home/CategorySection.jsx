import Link from "next/link";
import { resolveCategoryImage, useImageFallback } from "@/lib/format";

export default function CategorySection({ categories = [] }) {
  return (
    <section className="homepage-section">
      <div className="section-header">
        <h2 className="section-title">Danh mục Sản Phẩm</h2>
        <Link href="/shop" className="view-all-link">
          xem tất cả ➔
        </Link>
      </div>

      {categories.length > 0 ? (
        <div className="categories-grid-6">
          {categories.slice(0, 6).map((category) => (
            <Link
              href={`/shop?category=${category.slug}`}
              className="category-card-figma"
              key={category.id}
            >
              <div className="category-img-box">
                <img
                  src={resolveCategoryImage(category.image)}
                  alt={category.name}
                  className="category-figma-img"
                  loading="lazy"
                  onError={useImageFallback}
                />
              </div>
              <span className="category-figma-name">{category.name}</span>
            </Link>
          ))}
        </div>
      ) : (
        <div style={{ textAlign: "center", padding: "40px", color: "#666", fontStyle: "italic" }}>
          Không có danh mục nào được tìm thấy. Vui lòng bật API Laravel.
        </div>
      )}

      <div className="users-counter-banner">
        Hơn 12.000 người dùng hoạt động mỗi ngày
      </div>
    </section>
  );
}
