import Link from "next/link";
import { getBlogs } from "@/lib/api";
import { resolveBackendImage, resolveBlogImage } from "@/lib/format";
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
  const { category = "", search = "", sort = "newest", page = "1" } = await searchParams;

  const data = await getBlogs({ category, search, sort, page, per_page: 3 });

  const {
    total = 0,
    blogs = [],
    categories = [],
    sort_options = [],
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

  // Chỉ hiển thị bài viết tiêu điểm (Hero Featured Card) ở trang đầu tiên khi không có bộ lọc tìm kiếm/chuyên mục
  const showFeatured = currentPage === 1 && !category && !search && blogs.length > 0;
  const featuredBlog = showFeatured ? blogs[0] : null;
  const remainingBlogs = showFeatured ? blogs.slice(1) : blogs;

  return (
    <main className="main-content">
      <div className="homepage-container">
        {/* Breadcrumb */}
        <nav className="shop-breadcrumb">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">Tin tức</span>
        </nav>

        {/* Tiêu đề trang tối giản đồng bộ với cửa hàng */}
        <div className="news-header" style={{ marginBottom: 36, marginTop: 10 }}>
          <h1 style={{ fontSize: "28px", fontWeight: "800", color: "var(--text-dark)", marginBottom: 8 }}>
            Cộng Đồng Tin Tức & Chia Sẻ
          </h1>
          <p style={{ fontSize: "14.5px", color: "var(--text-muted)", margin: 0 }}>
            Nơi chia sẻ kinh nghiệm chăm sóc, dinh dưỡng và lối sống lành mạnh cho các bé cưng từ các chuyên gia PetWorld.
          </p>
        </div>

        {/* Bố cục 2 cột Premium */}
        <div className="news-main-layout">
          {/* Cột Trái: Danh sách bài viết */}
          <div className="news-content-area">
            {/* 1. Bài viết tiêu điểm nổi bật (Featured Post) */}
            {featuredBlog && (
              <div className="featured-blog-card">
                <Link href={`/news/${featuredBlog.slug}`} className="featured-blog-img-wrapper">
                  <img
                    src={resolveBlogImage(featuredBlog.image)}
                    alt={featuredBlog.cover_alt || featuredBlog.title}
                    className="featured-blog-img"
                  />
                </Link>
                <div className="featured-blog-content">
                  <div style={{ display: "flex", gap: 10, alignItems: "center", marginBottom: 12 }}>
                    <span className="blog-tag" style={{ margin: 0 }}>
                      {featuredBlog.category?.name ?? "Tin tức"}
                    </span>
                    <span style={{ fontSize: "12px", color: "var(--text-muted)" }}>
                      {formatDate(featuredBlog.created_at)}
                    </span>
                  </div>
                  <Link href={`/news/${featuredBlog.slug}`} className="featured-blog-title">
                    {featuredBlog.title}
                  </Link>
                  <p className="featured-blog-excerpt">{featuredBlog.description}</p>
                  <Link
                    href={`/news/${featuredBlog.slug}`}
                    className="blog-link"
                    style={{ color: "var(--primary-orange)", fontWeight: "bold" }}
                  >
                    Đọc bài viết →
                  </Link>
                </div>
              </div>
            )}

            {/* 2. Lưới bài viết Grid 2 cột */}
            {remainingBlogs.length > 0 ? (
              <div className="blog-grid-2col">
                {remainingBlogs.map((blog) => (
                  <article className="blog-card" key={blog.id}>
                    <Link href={`/news/${blog.slug}`} className="blog-img-wrapper" style={{ borderRadius: "20px 20px 0 0" }}>
                      <img src={resolveBlogImage(blog.image)} alt={blog.cover_alt || blog.title} className="blog-img" />
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
            ) : (
              !featuredBlog && (
                <div style={{ textAlign: "center", padding: "60px 20px", color: "#666", fontStyle: "italic" }}>
                  Chưa có bài viết nào trong danh mục này.
                </div>
              )
            )}

            {/* Phân trang */}
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
          </div>

          {/* Cột Phải: Sidebar tiện ích */}
          <aside className="blog-sidebar">
            {/* Hộp 1: Bộ lọc Tìm kiếm và Sắp xếp */}
            <div className="sidebar-widget">
              <h3 className="sidebar-widget-title">Tìm kiếm & Lọc</h3>
              <div style={{ display: "flex", flexDirection: "column", gap: 14 }}>
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

            {/* Hộp 2: Danh sách Chuyên mục (Sidebar với active state và đếm số) */}
            <div className="sidebar-widget">
              <h3 className="sidebar-widget-title">Danh mục bài viết</h3>
              <ul className="sidebar-cat-list">
                <li className={`sidebar-cat-item ${!category ? "active" : ""}`}>
                  <Link href={categoryHref("")}>
                    <span>Tất cả bài viết</span>
                    <span className="sidebar-cat-count">{total}</span>
                  </Link>
                </li>
                {categories.map((cat) => (
                  <li key={cat.id} className={`sidebar-cat-item ${category === cat.slug ? "active" : ""}`}>
                    <Link href={categoryHref(cat.slug)}>
                      <span>{cat.name}</span>
                      <span className="sidebar-cat-count">{cat.blog_count ?? 0}</span>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            {/* Hộp 3: Bài viết xem nhiều (Sắp xếp theo view_count cao nhất) */}
            {blogs.length > 0 && (
              <div className="sidebar-widget">
                <h3 className="sidebar-widget-title">Xem nhiều nhất</h3>
                <ul className="sidebar-popular-list">
                  {[...blogs]
                    .sort((a, b) => (b.view_count ?? 0) - (a.view_count ?? 0))
                    .slice(0, 4)
                    .map((popBlog) => (
                      <li key={popBlog.id} className="sidebar-popular-item">
                        <Link href={`/news/${popBlog.slug}`} className="sidebar-popular-img-wrapper">
                          <img
                            src={resolveBlogImage(popBlog.image)}
                            alt={popBlog.cover_alt || popBlog.title}
                            className="sidebar-popular-img"
                          />
                        </Link>
                        <div className="sidebar-popular-info">
                          <Link href={`/news/${popBlog.slug}`} className="sidebar-popular-title">
                            {popBlog.title}
                          </Link>
                          <span className="sidebar-popular-date">{formatDate(popBlog.created_at)}</span>
                        </div>
                      </li>
                    ))}
                </ul>
              </div>
            )}
          </aside>
        </div>
      </div>
    </main>
  );
}
