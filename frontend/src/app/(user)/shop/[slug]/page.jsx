import Link from "next/link";
import TrackRecentlyViewed from "@/components/product/TrackRecentlyViewed";

export async function generateMetadata({ params }) {
  const { slug } = await params;
  return { title: `${slug} - PetWorld` };
}

export default async function ProductDetailPage({ params }) {
  const { slug } = await params;

  return (
    <main className="main-content">
      <TrackRecentlyViewed slug={slug} />
      <div className="homepage-container">
        <nav style={{ marginBottom: 20, fontSize: 14, color: "#666" }}>
          <Link href="/" style={{ color: "#666", textDecoration: "none" }}>
            Trang chủ
          </Link>{" "}
          /{" "}
          <Link href="/shop" style={{ color: "#666", textDecoration: "none" }}>
            Cửa hàng
          </Link>{" "}
          / <span style={{ color: "var(--primary-orange)" }}>{slug}</span>
        </nav>

        <h1 className="section-title">Chi tiết sản phẩm</h1>
        <p style={{ marginTop: 16, color: "#666" }}>
          Trang chi tiết cho sản phẩm <strong>{slug}</strong> đang được phát triển.
        </p>
      </div>
    </main>
  );
}
