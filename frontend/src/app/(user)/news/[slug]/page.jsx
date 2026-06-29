import Link from "next/link";
import { notFound } from "next/navigation";
import { getBlogDetail, getBlogs } from "@/lib/api";
import { resolveImage } from "@/lib/format";
import BlogComments from "@/components/blog/BlogComments";

export async function generateMetadata({ params }) {
  const { slug } = await params;
  const data = await getBlogDetail(slug);
  return { title: `${data?.blog?.title ?? slug} - PetWorld` };
}

function formatDate(value) {
  if (!value) return "";
  return new Date(value).toLocaleDateString("vi-VN", { day: "2-digit", month: "long", year: "numeric" });
}

export default async function BlogDetailPage({ params }) {
  const { slug } = await params;
  const [data, listData] = await Promise.all([getBlogDetail(slug), getBlogs({ per_page: 5 })]);

  if (!data?.blog) {
    notFound();
  }

  const { blog, related_blogs = [] } = data;
  const recentBlogs = (listData.blogs ?? []).filter((item) => item.slug !== slug).slice(0, 4);
  const categories = listData.categories ?? [];

  return (
    <main className="main-content">
      <div className="homepage-container">
        {/* Breadcrumb */}
        <nav className="shop-breadcrumb" style={{ marginBottom: 24 }}>
          <Link href="/">Trang Chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <Link href="/news">Tin Tức</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">{blog.category?.name ?? "Bài viết"}</span>
        </nav>

        <div className="news-detail-layout">
          {/* Nội dung bài viết */}
          <article className="news-article">
            {/* Category badge + meta */}
            <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 14, flexWrap: "wrap" }}>
              <Link
                href={`/news?category=${blog.category?.slug}`}
                className="blog-tag"
                style={{ textDecoration: "none", margin: 0 }}
              >
                {blog.category?.name ?? "Tin tức"}
              </Link>
              <span style={{ color: "#bbb", fontSize: 13 }}>•</span>
              <span style={{ fontSize: 13, color: "var(--text-muted)" }}>{formatDate(blog.created_at)}</span>
              {blog.view_count > 0 && (
                <>
                  <span style={{ color: "#bbb", fontSize: 13 }}>•</span>
                  <span style={{ fontSize: 13, color: "var(--text-muted)" }}>👁 {blog.view_count} lượt xem</span>
                </>
              )}
            </div>

            {/* Tiêu đề */}
            <h1 className="news-article-title">{blog.title}</h1>

            {/* Tác giả */}
            <div className="news-author" style={{ paddingBottom: 18, marginBottom: 20 }}>
              <span className="news-author-avatar">
                {(blog.author?.name ?? "P").charAt(0).toUpperCase()}
              </span>
              <div>
                <strong style={{ fontSize: 14 }}>{blog.author?.name ?? "PetWorld"}</strong>
                <span style={{ fontSize: 13 }}>Chuyên gia @ PetWorld</span>
              </div>
            </div>

            {/* Ảnh bìa thu gọn - max-height 360px */}
            {blog.image && (
              <img
                src={resolveImage(blog.image)}
                alt={blog.title}
                className="news-article-cover"
              />
            )}

            {/* Mô tả ngắn nổi bật */}
            {blog.description && (
              <p
                className="news-article-lead"
                style={{
                  background: "#fff8f3",
                  borderLeft: "4px solid var(--primary-orange)",
                  padding: "14px 18px",
                  borderRadius: 8,
                  fontSize: 15,
                }}
              >
                {blog.description}
              </p>
            )}

            {/* Nội dung bài viết (HTML từ backend) */}
            {blog.content && (
              <div
                className="news-article-body"
                dangerouslySetInnerHTML={{ __html: blog.content }}
              />
            )}

            {/* Bình luận */}
            <BlogComments blogSlug={slug} initialComments={blog.comments ?? []} />
          </article>

          {/* Sidebar */}
          <aside className="news-sidebar">
            <div className="news-widget">
              <h3 className="news-widget-title">Bài viết mới nhất</h3>
              <ul className="news-recent-list">
                {recentBlogs.map((item) => (
                  <li key={item.id}>
                    <Link href={`/news/${item.slug}`}>{item.title}</Link>
                    <span>{formatDate(item.created_at)}</span>
                  </li>
                ))}
              </ul>
            </div>

            {categories.length > 0 && (
              <div className="news-widget">
                <h3 className="news-widget-title">Danh mục</h3>
                <ul className="news-cat-list">
                  {categories.map((category) => (
                    <li key={category.id}>
                      <Link href={`/news?category=${category.slug}`}>{category.name}</Link>
                      <span>{category.blog_count ?? 0}</span>
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </aside>
        </div>

        {/* Bài viết liên quan */}
        {related_blogs.length > 0 && (
          <section
            className="homepage-section"
            style={{ marginTop: 48, paddingTop: 36, borderTop: "1px solid #eef1f5" }}
          >
            <div className="section-header">
              <h2 className="section-title">Bài viết liên quan</h2>
              <Link href="/news" className="view-all-link">xem tất cả ➔</Link>
            </div>
            <div className="blog-grid">
              {related_blogs.map((item) => (
                <article className="blog-card" key={item.id}>
                  <Link href={`/news/${item.slug}`} className="blog-img-wrapper">
                    <img src={resolveImage(item.image)} alt={item.title} className="blog-img" />
                  </Link>
                  <div className="blog-content">
                    <span className="blog-tag">{item.category?.name ?? "Tin tức"}</span>
                    <Link href={`/news/${item.slug}`} className="blog-title">{item.title}</Link>
                    <p className="blog-excerpt">{item.description}</p>
                    <Link href={`/news/${item.slug}`} className="blog-link">Đọc Tiếp →</Link>
                  </div>
                </article>
              ))}
            </div>
          </section>
        )}
      </div>
    </main>
  );
}
