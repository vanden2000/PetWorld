import Link from "next/link";
import { getBlogs } from "@/lib/api";
import { resolveImage } from "@/lib/format";

export const metadata = {
  title: "Tin Tức - PetWorld",
};

export default async function NewsPage({ searchParams }) {
  // Next 16: searchParams là promise, phải await trước khi đọc.
  const { category, search, sort, page } = await searchParams;

  const data = await getBlogs({ category, search, sort, page });

  const {
    title = "Bài viết mới nhất",
    total = 0,
    blogs = [],
    pagination = {},
  } = data;

  const currentPage = pagination.current_page ?? 1;
  const lastPage = pagination.last_page ?? 1;

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
        <nav style={{ marginBottom: 20, fontSize: 14, color: "#666" }}>
          <Link href="/" style={{ color: "#666", textDecoration: "none" }}>
            Trang chủ
          </Link>{" "}
          / <span style={{ color: "var(--primary-orange)" }}>Tin tức</span>
        </nav>

        <div className="section-header">
          <h1 className="section-title">{title}</h1>
          <span style={{ color: "#666", fontSize: 14 }}>{total} bài viết</span>
        </div>

        {blogs.length > 0 ? (
          <>
            <div className="blog-grid">
              {blogs.map((blog) => (
                <article className="blog-card" key={blog.id}>
                  <Link href={`/news/${blog.slug}`} className="blog-img-wrapper">
                    <img src={resolveImage(blog.image)} alt={blog.title} className="blog-img" />
                  </Link>
                  <div className="blog-content">
                    <span className="blog-tag">{blog.category?.name ?? "Pet knowledge"}</span>
                    <Link href={`/news/${blog.slug}`} className="blog-title">
                      {blog.title}
                    </Link>
                    <p className="blog-excerpt">{blog.description}</p>
                    <Link href={`/news/${blog.slug}`} className="blog-link">
                      Đọc Tiếp →
                    </Link>
                  </div>
                </article>
              ))}
            </div>

            {lastPage > 1 && (
              <div
                style={{
                  display: "flex",
                  justifyContent: "center",
                  gap: 8,
                  marginTop: 32,
                }}
              >
                {Array.from({ length: lastPage }).map((_, index) => {
                  const targetPage = index + 1;
                  const isActive = targetPage === currentPage;
                  return (
                    <Link
                      key={targetPage}
                      href={pageHref(targetPage)}
                      style={{
                        padding: "8px 14px",
                        borderRadius: 8,
                        textDecoration: "none",
                        border: "1px solid #e0e0e0",
                        background: isActive ? "var(--primary-orange)" : "#fff",
                        color: isActive ? "#fff" : "#333",
                      }}
                    >
                      {targetPage}
                    </Link>
                  );
                })}
              </div>
            )}
          </>
        ) : (
          <div style={{ textAlign: "center", padding: "60px 20px", color: "#666", fontStyle: "italic" }}>
            Chưa có bài viết nào. Vui lòng bật API Laravel.
          </div>
        )}
      </div>
    </main>
  );
}
