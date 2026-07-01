import Link from "next/link";
import { getBlogs } from "@/lib/api";
import { resolveBlogImage } from "@/lib/format";
import BlogSort from "@/components/blog/BlogSort";
import BlogSearch from "@/components/blog/BlogSearch";

function formatDate(value) {
  if (!value) return "";
  return new Date(value).toLocaleDateString("vi-VN", { day: "2-digit", month: "long", year: "numeric" });
}

export const metadata = {
  title: "Tin Tức - PetWorld",
};

export default async function NewsPage({ searchParams }) {
  // Next 16: searchParams là promise, phải await trước khi đọc.
  const { category = "", search = "", sort = "newest", page = "1" } = await searchParams;

  const data = await getBlogs({ category, search, sort, page });

  const {
    title = "Bài viết mới nhất",
    total = 0,
    blogs = [],
    categories = [], // Trích xuất categories từ API backend
    sort_options = [], // Trích xuất sort_options từ API backend
    pagination = {},
  } = data;

  const currentPage = pagination.current_page ?? 1;
  const lastPage = pagination.last_page ?? 1;

  // Href cho bộ lọc danh mục
  const categoryHref = (catSlug) => {
    const query = new URLSearchParams();
    if (catSlug) query.set("category", catSlug);
    if (search) query.set("search", search);
    if (sort) query.set("sort", sort);
    // Reset về trang 1 khi chuyển đổi danh mục
    return `/news?${query.toString()}`;
  };

  // Href cho phân trang
  const pageHref = (targetPage) => {
    const query = new URLSearchParams();
    if (category) query.set("category", category);
    if (search) query.set("search", search);
    if (sort) query.set("sort", sort);
    query.set("page", String(targetPage));
    return `/news?${query.toString()}`;
  };

  return (
    <main className="main-content">
      <div className="homepage-container">
        {/* Breadcrumb */}
        <nav style={{ marginBottom: 20, fontSize: 14, color: "#666" }}>
          <Link href="/" style={{ color: "#666", textDecoration: "none" }}>
            Trang chủ
          </Link>{" "}
          / <span style={{ color: "var(--primary-orange)" }}>Tin tức</span>
        </nav>

        {/* Banner theo thiết kế Figma */}
        <div
          className="news-banner"
          style={{ backgroundImage: `url('/image/banners/blog-banner.jpg')` }}
        >
          <h1 className="news-banner-title">Cộng đồng yêu thú cưng</h1>
        </div>

        {/* Bộ lọc và sắp xếp */}
        <div style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          marginBottom: 30,
          gap: 15,
          flexWrap: "wrap"
        }}>
          {/* Danh sách nút phân loại (Category Pills) */}
          <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
            <Link
              href={categoryHref("")}
              className={`tab-btn ${!category ? "active" : ""}`}
              style={{ textDecoration: "none" }}
            >
              Tất cả bài viết
            </Link>
            {categories.map((cat) => (
              <Link
                key={cat.id}
                href={categoryHref(cat.slug)}
                className={`tab-btn ${category === cat.slug ? "active" : ""}`}
                style={{ textDecoration: "none" }}
              >
                {cat.name}
              </Link>
            ))}
          </div>

          {/* Tìm kiếm và sắp xếp */}
          <div style={{ display: "flex", alignItems: "center", gap: 15, flexWrap: "wrap" }}>
            <BlogSearch
              initialSearch={search}
              query={{ category, sort }}
            />
            <BlogSort
              options={sort_options}
              value={sort}
              query={{ category, search }}
            />
          </div>
        </div>

        {blogs.length > 0 ? (
          <>
            {/* Grid 3 cột theo mockup Figma */}
            <div className="blog-grid">
              {blogs.map((blog) => (
                <article className="blog-card" key={blog.id}>
                  <Link href={`/news/${blog.slug}`} className="blog-img-wrapper" style={{ borderRadius: "20px 20px 0 0" }}>
                    <img src={resolveBlogImage(blog.image)} alt={blog.title} className="blog-img" />
                  </Link>
                  <div className="blog-content">
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 10 }}>
                      <span className="blog-tag" style={{ margin: 0 }}>{blog.category?.name ?? "Tin tức"}</span>
                      <div style={{ display: "flex", gap: 8, fontSize: "12px", color: "var(--text-muted)", alignItems: "center" }}>
                        <span>{formatDate(blog.created_at)}</span>
                        <span>•</span>
                        <span>👁 {blog.view_count ?? 0}</span>
                      </div>
                    </div>
                    <Link href={`/news/${blog.slug}`} className="blog-title" style={{ fontSize: "16px", minHeight: "44px", display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }}>
                      {blog.title}
                    </Link>
                    <p className="blog-excerpt" style={{ fontSize: "14px", display: "-webkit-box", WebkitLineClamp: 3, WebkitBoxOrient: "vertical", overflow: "hidden", minHeight: "65px", margin: "10px 0 15px 0" }}>
                      {blog.description}
                    </p>
                    <Link href={`/news/${blog.slug}`} className="blog-link" style={{ color: "var(--primary-orange)", fontWeight: "bold" }}>
                      Đọc thêm →
                    </Link>
                  </div>
                </article>
              ))}
            </div>

            {/* Phân trang đồng bộ CSS với shop */}
            {lastPage > 1 && (
              <div className="shop-pagination" style={{ marginTop: 40 }}>
                <Link
                  href={pageHref(Math.max(1, currentPage - 1))}
                  className={`shop-page-btn arrow ${currentPage <= 1 ? "disabled" : ""}`}
                  aria-label="Trang trước"
                >
                  ‹
                </Link>
                {Array.from({ length: lastPage }).map((_, index) => {
                  const targetPage = index + 1;
                  return (
                    <Link
                      key={targetPage}
                      href={pageHref(targetPage)}
                      className={`shop-page-btn ${targetPage === currentPage ? "active" : ""}`}
                    >
                      {targetPage}
                    </Link>
                  );
                })}
                <Link
                  href={pageHref(Math.min(lastPage, currentPage + 1))}
                  className={`shop-page-btn arrow ${currentPage >= lastPage ? "disabled" : ""}`}
                  aria-label="Trang sau"
                >
                  ›
                </Link>
              </div>
            )}
          </>
        ) : (
          <div style={{ textAlign: "center", padding: "60px 20px", color: "#666", fontStyle: "italic" }}>
            Chưa có bài viết nào trong danh mục này.
          </div>
        )}
      </div>
    </main>
  );
}
