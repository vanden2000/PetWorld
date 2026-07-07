import Link from "next/link";

export const metadata = {
  title: "Điều khoản sử dụng - PetWorld",
};

export default function TermsPage() {
  return (
    <main className="main-content">
      <div className="homepage-container policy-container">
        <nav className="shop-breadcrumb" aria-label="Đường dẫn trang">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">Điều khoản sử dụng</span>
        </nav>

        <h1 className="policy-title">Điều khoản sử dụng</h1>
        <p className="policy-meta">Cập nhật lần cuối: 07 tháng 07, 2026</p>

        <blockquote className="policy-intro">
          Khi truy cập và mua sắm tại PetWorld, bạn đồng ý tuân thủ các điều khoản dưới đây.
          Vui lòng đọc kỹ trước khi đặt hàng hoặc sử dụng dịch vụ.
        </blockquote>

        <section className="policy-section">
          <h2 className="policy-heading"><span className="policy-num">1</span> Tài khoản khách hàng</h2>
          <p className="policy-text">
            Bạn chịu trách nhiệm cung cấp thông tin chính xác, bảo mật thông tin đăng nhập và thông báo
            cho PetWorld khi phát hiện hoạt động bất thường trên tài khoản.
          </p>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading"><span className="policy-num">2</span> Đặt hàng và thanh toán</h2>
          <p className="policy-text">
            Đơn hàng chỉ được xác nhận sau khi hệ thống ghi nhận đầy đủ thông tin giao hàng và phương thức
            thanh toán. PetWorld có thể liên hệ để xác minh trước khi xử lý đơn.
          </p>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading"><span className="policy-num">3</span> Giá và thông tin sản phẩm</h2>
          <p className="policy-text">
            Chúng tôi cố gắng đảm bảo giá, hình ảnh và mô tả sản phẩm chính xác. Khi có sai sót rõ ràng,
            PetWorld sẽ thông báo cho khách hàng trước khi tiếp tục thực hiện đơn hàng.
          </p>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading"><span className="policy-num">4</span> Trách nhiệm sử dụng</h2>
          <p className="policy-text">
            Người dùng không được can thiệp trái phép vào hệ thống, sử dụng nội dung cho mục đích gian lận
            hoặc thực hiện hành vi gây ảnh hưởng đến khách hàng khác.
          </p>
        </section>

        <section className="policy-cta">
          <h2>Bạn cần làm rõ điều khoản?</h2>
          <p>Đội ngũ PetWorld luôn sẵn sàng tiếp nhận và giải đáp câu hỏi của bạn.</p>
          <div className="policy-cta-actions">
            <Link href="/contact" className="policy-cta-btn">Liên hệ chúng tôi</Link>
            <Link href="/chinh-sach-bao-mat" className="policy-cta-btn outline">Xem chính sách</Link>
          </div>
        </section>
      </div>
    </main>
  );
}
