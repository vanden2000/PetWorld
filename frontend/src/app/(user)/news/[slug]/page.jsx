import Link from "next/link";
import { notFound } from "next/navigation";
import { getBlogDetail, getBlogs } from "@/lib/api";
import { resolveImage } from "@/lib/format";

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
  // Lấy chi tiết bài + danh sách bài/danh mục cho sidebar cùng lúc.
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
        <nav className="shop-breadcrumb">
          <Link href="/">Trang Chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <Link href="/news">Tin Tức</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">{blog.category?.name ?? "Bài viết"}</span>
        </nav>

        <div className="news-detail-layout">
          {/* Nội dung bài viết */}
          <article className="news-article">
            <h1 className="news-article-title">{blog.title}</h1>

            <div className="news-author">
              <span className="news-author-avatar">
                {(blog.author?.name ?? "P").charAt(0).toUpperCase()}
              </span>
              <div>
                <strong>{blog.author?.name ?? "PetWorld"}</strong>
                <span>Chuyên gia Dinh dưỡng Thú cưng @ PetWorld</span>
              </div>
            </div>

            {blog.image && (
              <img src={resolveImage(blog.image)} alt={blog.title} className="news-article-cover" />
            )}

            {blog.description && <p className="news-article-lead">{blog.description}</p>}

            <div className="news-article-body">{blog.content}</div>

            {/* Bình luận */}
            <section className="news-comments">
              <h2 className="news-section-title">Bình luận</h2>
              <form className="news-comment-form">
                <label htmlFor="news-comment">Lời nhắn của bạn</label>
                <textarea id="news-comment" rows={4} placeholder="Chia sẻ suy nghĩ của bạn..." />
                <button type="button">Gửi bình luận</button>
              </form>
              <p className="news-comment-empty">Hãy là người đầu tiên bình luận về bài viết này.</p>
            </section>

            <div className="news-share">
              <span>Chia sẻ:</span>
              <a href="#" aria-label="Facebook" className="news-share-btn">f</a>
              <a href="#" aria-label="Twitter" className="news-share-btn">t</a>
              <a href="#" aria-label="LinkedIn" className="news-share-btn">in</a>
            </div>
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

        {/* CTA đăng ký */}
        <section className="news-cta">
          <h2>Nhận bí quyết chăm sóc thú cưng</h2>
          <p>Đừng bỏ lỡ các kiến thức dinh dưỡng mới nhất từ chuyên gia PetWorld mỗi tuần.</p>
          <form className="news-cta-form">
            <input type="email" placeholder="Địa chỉ email của bạn" />
            <button type="button">Đăng ký ngay</button>
          </form>
        </section>

        {/* Bài viết liên quan */}
        {related_blogs.length > 0 && (
          <section className="homepage-section" style={{ marginTop: 48 }}>
            <div className="section-header">
              <h2 className="section-title">Bài viết liên quan</h2>
              <Link href="/news" className="view-all-link">
                xem tất cả ➔
              </Link>
            </div>
            <div className="blog-grid">
              {related_blogs.map((item) => (
                <article className="blog-card" key={item.id}>
                  <Link href={`/news/${item.slug}`} className="blog-img-wrapper">
                    <img src={resolveImage(item.image)} alt={item.title} className="blog-img" />
                  </Link>
                  <div className="blog-content">
                    <span className="blog-tag">{item.category?.name ?? "Pet knowledge"}</span>
                    <Link href={`/news/${item.slug}`} className="blog-title">
                      {item.title}
                    </Link>
                    <p className="blog-excerpt">{item.description}</p>
                    <Link href={`/news/${item.slug}`} className="blog-link">
                      Đọc Tiếp →
                    </Link>
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
