import Link from "next/link";
import { getKnowledgeList } from "@/lib/api";

export const metadata = {
  title: "Chính sách & hướng dẫn - PetWorld",
  description:
    "Tổng hợp chính sách và hướng dẫn của PetWorld: giao hàng, thanh toán, đổi trả, voucher, điều khoản sử dụng và chính sách bảo mật.",
};

export default async function KnowledgeListPage({ searchParams }) {
  const { category = "" } = await searchParams;
  const data = await getKnowledgeList({ category });
  const articles = data.articles ?? [];
  const categories = data.categories ?? [];

  return (
    <main className="main-content">
      <div className="homepage-container">
        <nav className="shop-breadcrumb" aria-label="Đường dẫn trang" style={{ marginBottom: 24 }}>
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">Chính sách &amp; hướng dẫn</span>
        </nav>

        <h1 className="policy-title">Chính sách &amp; hướng dẫn</h1>
        <p className="policy-meta">
          Các quy định và hướng dẫn khi mua sắm tại PetWorld. Bạn cũng có thể hỏi chatbot để được hỗ trợ nhanh.
        </p>

        {categories.length > 0 && (
          <div
            style={{
              display: "flex",
              flexWrap: "wrap",
              gap: 10,
              marginBottom: 28,
            }}
          >
            <Link
              href="/chinh-sach"
              className="badge-category"
              style={{
                opacity: category ? 0.6 : 1,
                textDecoration: "none",
              }}
            >
              Tất cả
            </Link>
            {categories.map((item) => (
              <Link
                key={item.value}
                href={`/chinh-sach?category=${item.value}`}
                className="badge-category"
                style={{
                  opacity: category === item.value ? 1 : 0.6,
                  textDecoration: "none",
                }}
              >
                {item.label} ({item.count})
              </Link>
            ))}
          </div>
        )}

        {articles.length === 0 ? (
          <p style={{ color: "var(--text-muted)", fontSize: 15 }}>
            Chưa có bài viết nào trong nhóm này.
          </p>
        ) : (
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fill, minmax(300px, 1fr))",
              gap: 20,
            }}
          >
            {articles.map((article) => (
              <article
                key={article.id}
                style={{
                  border: "1px solid var(--border-color)",
                  borderRadius: 12,
                  padding: 20,
                  background: "var(--surface-color)",
                  display: "flex",
                  flexDirection: "column",
                  gap: 10,
                }}
              >
                <span className="badge-category" style={{ alignSelf: "flex-start" }}>
                  {article.category_label}
                </span>
                <Link
                  href={article.url}
                  style={{
                    color: "var(--text-main)",
                    fontSize: "1.05rem",
                    fontWeight: 700,
                    textDecoration: "none",
                    lineHeight: 1.4,
                  }}
                >
                  {article.title}
                </Link>
                {article.summary && (
                  <p style={{ color: "var(--text-muted)", fontSize: 14, margin: 0, lineHeight: 1.6 }}>
                    {article.summary}
                  </p>
                )}
                <Link
                  href={article.url}
                  style={{ color: "var(--primary)", fontWeight: 700, textDecoration: "none", marginTop: "auto" }}
                >
                  Xem chi tiết →
                </Link>
              </article>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}