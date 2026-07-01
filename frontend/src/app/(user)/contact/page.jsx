export const metadata = {
  title: "Liên hệ - PetWorld",
};

export default function ContactPage() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <header className="contact-hero">
          <h1>Liên hệ với chúng tôi</h1>
          <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn chăm sóc những người bạn bốn chân một cách tốt nhất.</p>
        </header>

        <div className="contact-cards">
          <div className="contact-card">
            <span className="contact-card-icon phone">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.74-1.27a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z" />
              </svg>
            </span>
            <h3>Điện thoại</h3>
            <p>Gọi cho chúng tôi để được tư vấn nhanh nhất.</p>
            <strong>+84 123 456 789</strong>
          </div>

          <div className="contact-card">
            <span className="contact-card-icon mail">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 7-10 5L2 7" />
              </svg>
            </span>
            <h3>Email</h3>
            <p>Gửi thắc mắc của bạn qua hòm thư điện tử.</p>
            <strong>hello@petworld.vn</strong>
          </div>

          <div className="contact-card">
            <span className="contact-card-icon store">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
            </span>
            <h3>Cửa hàng</h3>
            <p>Ghé thăm showroom trưng bày sản phẩm.</p>
            <strong>123 Đường Pet, Quận 1, TP. HCM</strong>
          </div>
        </div>

        <div className="contact-body">
          <section className="contact-form-card">
            <h2>Gửi lời nhắn cho chúng tôi</h2>
            <p className="contact-form-sub">
              Điền vào mẫu dưới đây và đội ngũ chuyên gia của PetWorld sẽ phản hồi bạn trong vòng 24 giờ.
            </p>
            <form className="contact-form">
              <div className="contact-form-row">
                <div className="contact-field">
                  <label htmlFor="contact-name">Họ và Tên</label>
                  <input id="contact-name" type="text" placeholder="Nguyễn Văn A" />
                </div>
                <div className="contact-field">
                  <label htmlFor="contact-email">Email</label>
                  <input id="contact-email" type="email" placeholder="email@example.com" />
                </div>
              </div>
              <div className="contact-field">
                <label htmlFor="contact-subject">Chủ đề</label>
                <select id="contact-subject" defaultValue="Tư vấn dinh dưỡng">
                  <option>Tư vấn dinh dưỡng</option>
                  <option>Hỗ trợ đơn hàng</option>
                  <option>Hợp tác kinh doanh</option>
                  <option>Khác</option>
                </select>
              </div>
              <div className="contact-field">
                <label htmlFor="contact-message">Lời nhắn</label>
                <textarea id="contact-message" rows={5} placeholder="Chúng tôi có thể giúp gì cho bạn?" />
              </div>
              <button type="button" className="contact-submit">Gửi tin nhắn ➤</button>
            </form>
          </section>

          <aside className="contact-illustration">
            <span className="contact-illustration-art">
              <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
              </svg>
            </span>
            <div className="contact-store-box">
              <span className="contact-store-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M3 9h18v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z" />
                  <path d="M3 9 5 3h14l2 6" />
                </svg>
              </span>
              <div>
                <strong>PetWorld Flagship Store</strong>
                <span>Mở cửa: 08:00 - 21:00 hàng ngày</span>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </main>
  );
}
