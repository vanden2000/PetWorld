import Link from "next/link";

export const metadata = {
  title: "Chính sách Bảo mật - PetWorld",
};

function Check() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="10" />
      <path d="m9 12 2 2 4-4" />
    </svg>
  );
}

export default function PrivacyPolicyPage() {
  return (
    <main className="main-content">
      <div className="homepage-container policy-container">
        <nav className="shop-breadcrumb">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">Chính sách Bảo mật</span>
        </nav>

        <h1 className="policy-title">Chính sách Bảo mật</h1>
        <p className="policy-meta">
          🗓 Cập nhật lần cuối: 24 Tháng 5, 2024 &nbsp;•&nbsp; PetWorld Compliance Team
        </p>

        <blockquote className="policy-intro">
          Tại PetWorld, chúng tôi cam kết bảo vệ quyền riêng tư và dữ liệu cá nhân của bạn. Chính sách này giải
          thích cách chúng tôi xử lý thông tin để mang lại trải nghiệm tốt nhất cho thú cưng của bạn.
        </blockquote>

        <section className="policy-section">
          <h2 className="policy-heading">
            <span className="policy-num">1</span> Thu thập thông tin
          </h2>
          <p className="policy-text">
            Chúng tôi thu thập các loại thông tin sau để phục vụ nhu cầu dinh dưỡng và chăm sóc thú cưng của bạn:
          </p>
          <ul className="policy-checklist">
            <li>
              <Check />
              <span>
                <strong>Thông tin cá nhân:</strong> Họ tên, địa chỉ email, số điện thoại và địa chỉ giao hàng.
              </span>
            </li>
            <li>
              <Check />
              <span>
                <strong>Thông tin thú cưng:</strong> Giống loài, tuổi, cân nặng và các yêu cầu dinh dưỡng đặc biệt.
              </span>
            </li>
            <li>
              <Check />
              <span>
                <strong>Dữ liệu giao dịch:</strong> Lịch sử mua hàng và thông tin thanh toán (được mã hóa).
              </span>
            </li>
          </ul>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading">
            <span className="policy-num">2</span> Sử dụng thông tin
          </h2>
          <p className="policy-text">Thông tin của bạn được sử dụng một cách minh bạch cho các mục đích:</p>
          <div className="policy-cards">
            <div className="policy-card">
              <strong>🛒 Xử lý đơn hàng</strong>
              <span>Đảm bảo sản phẩm được giao đúng hạn và đúng địa chỉ.</span>
            </div>
            <div className="policy-card">
              <strong>🎯 Cá nhân hóa</strong>
              <span>Gợi ý sản phẩm phù hợp với nhu cầu riêng của từng thú cưng.</span>
            </div>
          </div>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading">
            <span className="policy-num">3</span> Bảo vệ dữ liệu
          </h2>
          <div className="policy-darkbox">
            <p className="policy-darkbox-quote">
              &ldquo;An toàn dữ liệu của khách hàng là ưu tiên hàng đầu tại PetWorld.&rdquo;
            </p>
            <p>
              Chúng tôi áp dụng các công nghệ mã hóa SSL tiêu chuẩn ngành và hệ thống tường lửa đa lớp. Chỉ những
              nhân viên có thẩm quyền mới được tiếp cận thông tin cá nhân của khách hàng trong phạm vi công việc
              cần thiết.
            </p>
          </div>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading">
            <span className="policy-num">4</span> Quyền của người dùng
          </h2>
          <p className="policy-text">Bạn có toàn quyền kiểm soát dữ liệu của mình:</p>
          <details className="policy-accordion">
            <summary>Quyền truy cập và chỉnh sửa</summary>
            <p>
              Bạn có thể yêu cầu xem, cập nhật hoặc chỉnh sửa thông tin cá nhân của mình bất kỳ lúc nào thông qua
              trang tài khoản hoặc liên hệ bộ phận hỗ trợ.
            </p>
          </details>
          <details className="policy-accordion">
            <summary>Quyền yêu cầu xóa dữ liệu</summary>
            <p>
              Bạn có quyền yêu cầu xóa toàn bộ dữ liệu cá nhân khỏi hệ thống của chúng tôi, trừ những thông tin
              bắt buộc phải lưu giữ theo quy định pháp luật.
            </p>
          </details>
        </section>

        <section className="policy-section">
          <h2 className="policy-heading">
            <span className="policy-num">5</span> Cookie
          </h2>
          <p className="policy-text">
            Chúng tôi sử dụng Cookie để ghi nhớ tùy chọn của bạn và phân tích lưu lượng truy cập. Bạn có thể tắt
            cookie trong phần cài đặt trình duyệt, nhưng điều này có thể ảnh hưởng đến một số tính năng của trang web.
          </p>
          <button type="button" className="policy-cookie-btn">⚙ Quản lý Cookie</button>
        </section>

        <section className="policy-cta">
          <h2>Bạn có câu hỏi nào không?</h2>
          <p>Nếu bạn cần giải thích rõ hơn về cách chúng tôi xử lý dữ liệu, đội ngũ hỗ trợ của chúng tôi luôn sẵn sàng giúp đỡ.</p>
          <div className="policy-cta-actions">
            <Link href="/contact" className="policy-cta-btn">Liên hệ chúng tôi</Link>
            <Link href="#" className="policy-cta-btn outline">Xem Điều khoản</Link>
          </div>
        </section>
      </div>
    </main>
  );
}
