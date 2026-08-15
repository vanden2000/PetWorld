import Link from "next/link";

function formatDate(value) {
  if (!value) return "";
  return new Date(value).toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

/**
 * Hiển thị một bài kiến thức (chính sách/hướng dẫn) ra trang public.
 * Cùng nguồn `knowledge_articles` do admin quản lý ở "Kiến thức chatbot".
 * Khung dùng lại CSS `.policy-*` (globals.css), nội dung HTML render qua
 * `.news-article-body` — đồng nhất với cách trang bài viết (news) render.
 */
export default function KnowledgeArticleView({ article, related = [] }) {
  return (
    <main className="main-content">
      <div className="homepage-container policy-container">
        <nav className="shop-breadcrumb" aria-label="Đường dẫn trang">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <Link href="/chinh-sach">Chính sách &amp; hướng dẫn</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">{article.category_label}</span>
        </nav>

        <h1 className="policy-title">{article.title}</h1>
        {article.published_at && (
          <p className="policy-meta">Cập nhật lần cuối: {formatDate(article.published_at)}</p>
        )}

        {article.summary && <blockquote className="policy-intro">{article.summary}</blockquote>}

        {article.content && (
          <div
            className="news-article-body"
            dangerouslySetInnerHTML={{ __html: article.content }}
          />
        )}

        <section className="policy-cta">
          <h2>Bạn cần làm rõ thêm?</h2>
          <p>Đội ngũ PetWorld luôn sẵn sàng tiếp nhận và giải đáp câu hỏi của bạn.</p>
          <div className="policy-cta-actions">
            <Link href="/contact" className="policy-cta-btn">Liên hệ chúng tôi</Link>
            <Link href="/chinh-sach" className="policy-cta-btn outline">Xem tất cả chính sách</Link>
          </div>
        </section>

        {related.length > 0 && (
          <section className="policy-section" style={{ marginTop: 8 }}>
            <h2 className="policy-heading">
              <span className="policy-num">★</span> Bài liên quan
            </h2>
            <ul
              style={{
                listStyle: "none",
                padding: 0,
                margin: 0,
                display: "flex",
                flexDirection: "column",
                gap: 10,
              }}
            >
              {related.map((item) => (
                <li key={item.id}>
                  <Link
                    href={item.url}
                    style={{ color: "var(--primary)", fontWeight: 600, textDecoration: "none" }}
                  >
                    {item.title}
                  </Link>
                </li>
              ))}
            </ul>
          </section>
        )}
      </div>
    </main>
  );
}