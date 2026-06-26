import Link from "next/link";

export async function generateMetadata({ params }) {
  const { slug } = await params;
  return { title: `${slug} - PetWorld` };
}

export default async function BlogDetailPage({ params }) {
  const { slug } = await params;

  return (
    <main className="main-content">
      <div className="homepage-container">
        <nav style={{ marginBottom: 20, fontSize: 14, color: "#666" }}>
          <Link href="/" style={{ color: "#666", textDecoration: "none" }}>
            Trang chủ
          </Link>{" "}
          /{" "}
          <Link href="/news" style={{ color: "#666", textDecoration: "none" }}>
            Tin tức
          </Link>{" "}
          / <span style={{ color: "var(--primary-orange)" }}>{slug}</span>
        </nav>

        <h1 className="section-title">Chi tiết bài viết</h1>
        <p style={{ marginTop: 16, color: "#666" }}>
          Nội dung bài viết <strong>{slug}</strong> đang được phát triển.
        </p>
      </div>
    </main>
  );
}
