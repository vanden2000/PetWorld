import "../globals.css";

export const metadata = {
  title: "PetWorld - Siêu Thị Thú Cưng Hàng Đầu | Thức Ăn & Phụ Kiện Chính Hãng",
  description: "PetWorld - Hệ thống siêu thị thú cưng uy tín hàng đầu Việt Nam. Cung cấp sỉ lẻ thức ăn, pate dinh dưỡng, cát vệ sinh, vòng cổ và phụ kiện đồ chơi chó mèo chính hãng.",
  keywords: "petworld, siêu thị thú cưng, thức ăn chó mèo, phụ kiện thú cưng, cát vệ sinh, pate mèo",
};

export default function UserLayout({ children }) {
  return (
    <html lang="vi">
      <body>
        <header className="header-wrapper">
          {/* Top Header Bar */}
          <div className="top-bar">
            <div className="top-bar-left">
              <a href="tel:+0123456789" className="top-bar-item">
                <span className="top-bar-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                  </svg>
                </span>
                +01 234 567 89
              </a>
              <a href="mailto:rgarton@outlook.com" className="top-bar-item">
                <span className="top-bar-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                  </svg>
                </span>
                rgarton@outlook.com
              </a>
            </div>
            <div className="top-bar-right">
              <div className="top-bar-item">
                <span className="top-bar-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>
                </span>
                113, thủ đô prompenh, vương quốc campuchia
              </div>
            </div>
          </div>

          {/* Floating Navigation Bar */}
          <div className="navbar-container">
            <nav className="navbar">
              <a href="#" className="logo-link" id="logo">
                <img src="/image/Special_Offer_1-removebg-preview.png" alt="PetWorld Logo" className="logo-img" />
              </a>

              <ul className="nav-menu">
                <li className="nav-item">
                  <a href="#" className="nav-link active" id="nav-home">Trang Chủ</a>
                </li>
                <li className="nav-item">
                  <a href="#" className="nav-link" id="nav-shop">Cửa Hàng</a>
                </li>
                <li className="nav-item">
                  <a href="#" className="nav-link" id="nav-services">Dịch Vụ</a>
                </li>
                <li className="nav-item">
                  <a href="#" className="nav-link" id="nav-news">Tin Tức</a>
                </li>
                <li className="nav-item">
                  <a href="#" className="nav-link" id="nav-contact">Liên hệ</a>
                </li>
              </ul>

              <div className="search-container">
                <input type="text" className="search-input" placeholder="Sen muốn tìm gì?...." aria-label="Tìm kiếm" />
                <button className="search-button" aria-label="Tìm kiếm nút">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                  </svg>
                </button>
              </div>

              <div className="nav-actions">
                <button className="action-item" id="wishlist-btn" aria-label="Danh sách yêu thích">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                  </svg>
                  <span className="action-badge">0</span>
                </button>

                <button className="action-item" id="profile-btn" aria-label="Tài khoản cá nhân">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </button>

                <button className="action-item" id="cart-btn" aria-label="Giỏ hàng">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                  </svg>
                  <span className="action-badge">0</span>
                </button>
              </div>
            </nav>
          </div>
        </header>

        {children}

        {/* Footer Section */}
        <footer className="footer-wrapper">
          {/* Paw Prints Background */}
          <div className="paw-bg paw-1">
            <svg viewBox="0 0 24 24" width="75" height="75" fill="currentColor">
              <path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 4 3 4s3-2 3-4c0-1.66-1.34-3-3-3zm-4.5-3c-.83 0-1.5-.67-1.5-1.5S6.67 8 7.5 8 9 8.67 9 9.5 8.33 11 7.5 11zm9 0c-.83 0-1.5-.67-1.5-1.5S15.67 8 16.5 8s1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm-12-3.5C3.67 7.5 3 6.83 3 6s.67-1.5 1.5-1.5S6 5.17 6 6s-.67 1.5-1.5 1.5zm15 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
            </svg>
          </div>
          <div className="paw-bg paw-2">
            <svg viewBox="0 0 24 24" width="55" height="55" fill="currentColor">
              <path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 4 3 4s3-2 3-4c0-1.66-1.34-3-3-3zm-4.5-3c-.83 0-1.5-.67-1.5-1.5S6.67 8 7.5 8 9 8.67 9 9.5 8.33 11 7.5 11zm9 0c-.83 0-1.5-.67-1.5-1.5S15.67 8 16.5 8s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
            </svg>
          </div>
          <div className="paw-bg paw-3">
            <svg viewBox="0 0 24 24" width="68" height="68" fill="currentColor">
              <path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 4 3 4s3-2 3-4c0-1.66-1.34-3-3-3zm-4.5-3c-.83 0-1.5-.67-1.5-1.5S6.67 8 7.5 8 9 8.67 9 9.5 8.33 11 7.5 11zm9 0c-.83 0-1.5-.67-1.5-1.5S15.67 8 16.5 8s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
            </svg>
          </div>
          <div className="paw-bg paw-4">
            <svg viewBox="0 0 24 24" width="85" height="85" fill="currentColor">
              <path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 4 3 4s3-2 3-4c0-1.66-1.34-3-3-3zm-4.5-3c-.83 0-1.5-.67-1.5-1.5S6.67 8 7.5 8 9 8.67 9 9.5 8.33 11 7.5 11zm9 0c-.83 0-1.5-.67-1.5-1.5S15.67 8 16.5 8s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
            </svg>
          </div>

          <div className="footer-main">
            <div className="footer-col">
              <h3 className="footer-title">Về PetWorld</h3>
              <p className="footer-text">
                Hệ thống siêu thị thú cưng uy tín hàng đầu Việt Nam. Cung cấp sỉ lẻ thức ăn, pate dinh dưỡng, cát vệ sinh, vòng cổ và phụ kiện đồ chơi chó mèo chính hãng.
              </p>
              <form className="subscribe-form">
                <div className="subscribe-input-wrapper">
                  <input type="email" className="subscribe-input" placeholder="Nhập email của bạn..." />
                </div>
                <button type="submit" className="subscribe-btn">Đăng ký</button>
              </form>
            </div>

            <div className="footer-col">
              <h3 className="footer-title">Liên Kết Nhanh</h3>
              <ul className="footer-links-list">
                <li><a href="#" className="footer-link">Trang Chủ</a></li>
                <li><a href="#" className="footer-link">Cửa Hàng</a></li>
                <li><a href="#" className="footer-link">Dịch Vụ</a></li>
                <li><a href="#" className="footer-link">Tin Tức</a></li>
                <li><a href="#" className="footer-link">Liên Hệ</a></li>
              </ul>
            </div>

            <div className="footer-col">
              <h3 className="footer-title">Dịch Vụ nổi bật</h3>
              <ul className="footer-links-list">
                <li><a href="#" className="footer-link">Chăm Sóc Thú Cưng</a></li>
                <li><a href="#" className="footer-link">Spa & Làm Đẹp</a></li>
                <li><a href="#" className="footer-link">Khách Sạn Thú Cưng</a></li>
                <li><a href="#" className="footer-link">Tư Vấn Dinh Dưỡng</a></li>
                <li><a href="#" className="footer-link">Bác Sĩ Thú Y</a></li>
              </ul>
            </div>

            <div className="footer-col">
              <h3 className="footer-title">Thông Tin Liên Hệ</h3>
              <ul className="footer-contact-list">
                <li>📍 113, thủ đô prompenh, vương quốc campuchia</li>
                <li>📞 +01 234 567 89</li>
                <li>✉️ rgarton@outlook.com</li>
              </ul>
            </div>
          </div>

          <div className="footer-divider-row">
            <div className="footer-divider-inner">
              <div className="footer-payments">
                <span className="payment-text">Thành viên liên kết:</span>
                <span>Visa, Mastercard, MoMo, VNPay</span>
              </div>
              <div className="footer-logo">
                <img src="/image/Special_Offer_1-removebg-preview.png" alt="PetWorld Logo" className="logo-img" />
              </div>
              <div className="footer-spacer"></div>
            </div>
          </div>

          <div className="footer-bottom">
            &copy; {new Date().getFullYear()} PetWorld - Siêu Thị Thú Cưng Hàng Đầu. All rights reserved.
          </div>
        </footer>
      </body>
    </html>
  );
}
