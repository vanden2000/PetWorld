import Link from "next/link";
import { ROUTES } from "@/lib/routes";

// Hiển thị khi trang gọi notFound() (vd sản phẩm/bài viết không tồn tại) hoặc sai URL.
export default function NotFound() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <div className="route-error" role="alert">
          <span className="route-error-icon" aria-hidden="true">🔍</span>
          <h1>404 — Không tìm thấy trang</h1>
          <p>Trang hoặc sản phẩm bạn tìm không tồn tại hoặc đã bị gỡ.</p>
          <div className="route-error-actions">
            <Link href={ROUTES.home} className="route-error-home">
              Về trang chủ
            </Link>
            <Link href={ROUTES.shop} className="route-error-retry">
              Đến cửa hàng
            </Link>
          </div>
        </div>
      </div>
    </main>
  );
}
