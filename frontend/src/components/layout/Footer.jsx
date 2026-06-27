import Link from "next/link";

// Dấu chân thú cưng dùng làm hoa văn nền mờ phía sau footer.
const PAW_PATH =
  "M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C34.9 480 14 459.1 14 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM310.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z";

const PAW_CLASSES = ["paw-1", "paw-2", "paw-3", "paw-4", "paw-5", "paw-6", "paw-7", "paw-8"];

const CUSTOMER_CARE_LINKS = [
  "Chính sách trả",
  "Hướng dẫn mua hàng",
  "Điều khoản bảo mật",
  "Tra cứu đơn hàng",
];

const ABOUT_LINKS = [
  { label: "Về Chúng Tôi", href: "#" },
  { label: "Tuyển dụng", href: "#" },
  { label: "Liên hệ", href: "/contact" },
];

// Logo phương thức thanh toán. Lưu file ảnh vào public/image/payments/.
const PAYMENTS = [
  { label: "Visa", src: "/image/payments/visa.png" },
  { label: "Mastercard", src: "/image/payments/mastercard.png" },
  { label: "PayPal", src: "/image/payments/paypal.png" },
  { label: "Apple Pay", src: "/image/payments/apple-pay.png" },
  { label: "Google Pay", src: "/image/payments/google-pay.png" },
];

function Paw({ className }) {
  return (
    <span className={`paw-bg ${className}`} aria-hidden="true">
      <svg viewBox="0 0 512 512" fill="currentColor">
        <path d={PAW_PATH} />
      </svg>
    </span>
  );
}

function PaymentChip({ label, src }) {
  return <img src={src} alt={label} title={label} className="payment-logo" />;
}

export default function Footer() {
  return (
    <footer className="footer-wrapper">
      {PAW_CLASSES.map((className) => (
        <Paw key={className} className={className} />
      ))}

      <div className="footer-main">
        {/* Cột 1: Đăng ký nhận tin */}
        <div className="footer-col">
          <h3 className="footer-title">Đăng ký nhận tin</h3>
          <p className="footer-text">
            Hãy là người đầu tiên nhận được những tin tức mới nhất và ưu đãi, các
            chương trình khuyến mãi và nhiều hơn nữa!
          </p>
          <form className="subscribe-form">
            <div className="subscribe-input-wrapper">
              <span className="envelope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a0a0a0" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                  <polyline points="22,6 12,13 2,6" />
                </svg>
              </span>
              <input type="email" className="subscribe-input" placeholder="Nhập địa chỉ email của bạn" aria-label="Email" />
            </div>
            <button type="button" className="subscribe-btn">
              Gửi
            </button>
          </form>
          <p className="footer-policy">
            Bằng cách đăng ký bạn chấp nhận điều khoản{" "}
            <Link href="#" className="policy-link">
              Chính sách bảo mật
            </Link>
          </p>
          <div className="footer-socials">
            <a href="#" className="social-link" aria-label="Facebook">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
              </svg>
            </a>
            <a href="#" className="social-link" aria-label="Twitter">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
              </svg>
            </a>
            <a href="#" className="social-link" aria-label="Instagram">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
              </svg>
            </a>
            <a href="#" className="social-link" aria-label="YouTube">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23 12s0-3.5-.46-5.17a2.78 2.78 0 0 0-1.94-2C18.88 4.46 12 4.46 12 4.46s-6.88 0-8.6.37a2.78 2.78 0 0 0-1.94 2C1 8.5 1 12 1 12s0 3.5.46 5.17a2.78 2.78 0 0 0 1.94 2c1.72.37 8.6.37 8.6.37s6.88 0 8.6-.37a2.78 2.78 0 0 0 1.94-2C23 15.5 23 12 23 12zM9.75 15.02V8.98L15.5 12z" />
              </svg>
            </a>
          </div>
        </div>

        {/* Cột 2: Liên hệ */}
        <div className="footer-col">
          <h3 className="footer-title">Liên hệ với chúng tôi</h3>
          <ul className="footer-contact-list">
            <li>175 Elizabeth Ave Str, San Jose, CA 95035</li>
            <li>+0982 561 41 24</li>
            <li>contact@Swoopers.com</li>
          </ul>
        </div>

        {/* Cột 3: Chăm sóc khách hàng */}
        <div className="footer-col">
          <h3 className="footer-title">Chăm Sóc Khách Hàng</h3>
          <ul className="footer-links-list">
            {CUSTOMER_CARE_LINKS.map((label) => (
              <li key={label}>
                <Link href="#" className="footer-link">
                  {label}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        {/* Cột 4: Về Petworld */}
        <div className="footer-col">
          <h3 className="footer-title">Về Petworld</h3>
          <ul className="footer-links-list">
            {ABOUT_LINKS.map((item) => (
              <li key={item.label}>
                <Link href={item.href} className="footer-link">
                  {item.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </div>

      {/* Hàng phương thức thanh toán + logo */}
      <div className="footer-divider-row">
        <div className="footer-divider-inner">
          <div className="footer-payments">
            <span className="payment-text">Accept for:</span>
            {PAYMENTS.map((payment) => (
              <PaymentChip key={payment.label} label={payment.label} src={payment.src} />
            ))}
          </div>
          <div className="footer-logo">
            <img src="/image/Special_Offer_1-removebg-preview.png" alt="PetWorld" className="logo-img" />
          </div>
          <div className="footer-spacer" />
        </div>
      </div>

      <div className="footer-bottom">© 2026 PetWorld . All Rights Reserved.</div>
    </footer>
  );
}
