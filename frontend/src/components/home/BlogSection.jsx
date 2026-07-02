import Link from "next/link";
import { resolveBlogImage } from "@/lib/format";

export default function BlogSection({ blogs = [] }) {
  if (blogs.length === 0) return null;

  return (
    <section className="homepage-section">
      <div className="section-header">
        <h2 className="section-title">Bài Viết Mới Nhất</h2>
        <Link href="/news" className="view-all-link">
          xem tất cả ➔
        </Link>
      </div>

      <div className="blog-grid">
        {blogs.map((blog) => (
          <article className="blog-card" key={blog.id}>
            <Link href={`/news/${blog.slug}`} className="blog-img-wrapper">
              <img src={resolveBlogImage(blog.image)} alt={blog.title} className="blog-img" />
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
    </section>
  );
}
