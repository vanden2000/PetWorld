"use client";

import { useState } from "react";
import { API_BASE_URL } from "@/lib/api";

const REQUEST_TYPES = [
  "Tư vấn sản phẩm & dinh dưỡng",
  "Hỗ trợ đơn hàng & vận chuyển",
  "Bảo hành & đổi trả",
  "Góp ý & phản hồi dịch vụ",
  "Khác",
];

const INITIAL_FORM = {
  name: "",
  email: "",
  type: REQUEST_TYPES[0],
  message: "",
};

export default function ContactClient() {
  const [form, setForm] = useState(INITIAL_FORM);
  const [status, setStatus] = useState("idle"); // idle | sending | success | error
  const [feedback, setFeedback] = useState("");

  const update = (field) => (event) =>
    setForm((prev) => ({ ...prev, [field]: event.target.value }));

  async function handleSubmit(event) {
    event.preventDefault();
    if (status === "sending") return;

    setStatus("sending");
    setFeedback("");

    try {
      const res = await fetch(`${API_BASE_URL}/api/contact`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(form),
      });

      const json = await res.json().catch(() => ({}));

      if (!res.ok) {
        const firstError = json?.errors
          ? Object.values(json.errors)[0]?.[0]
          : null;
        throw new Error(firstError || json?.message || "Gửi yêu cầu thất bại.");
      }

      setStatus("success");
      setFeedback(
        json?.data?.message ||
          "Đã gửi liên hệ thành công. PetWorld sẽ phản hồi bạn qua email/SĐT sớm nhất!"
      );
      setForm(INITIAL_FORM);
    } catch (error) {
      setStatus("error");
      setFeedback(error.message || "Có lỗi xảy ra, vui lòng thử lại.");
    }
  }

  return (
    <main className="main-content">
      <div className="homepage-container sp-support">
        {/* Background decorations */}
        <div className="sp-blob sp-blob-1" />
        <div className="sp-blob sp-blob-2" />

        {/* HERO TITLE */}
        <div className="sp-hero-section">
          <div className="sp-eyebrow">Liên hệ với chúng tôi</div>
          <h1 className="sp-title">
            Chúng tôi luôn lắng nghe <span className="sp-hi">bạn</span>
          </h1>
          <p className="sp-sub">
            Quý khách hàng có thể liên hệ trực tiếp qua hotline, email hoặc điền vào form bên dưới để gửi phản hồi đến PetWorld.
          </p>
        </div>

        {/* MAIN STAGE */}
        <div className="sp-stage">
          {/* LEFT: BRAND PANEL (CONTACT INFO) */}
          <div className="sp-brand-panel" style={{ justifyContent: "space-between" }}>
            <div>
              <h2 style={{ fontSize: "24px", fontWeight: "800", marginBottom: "16px" }}>Thông tin liên hệ</h2>
              <p className="sp-desc" style={{ color: "rgba(255, 255, 255, 0.7)", marginBottom: "32px", fontSize: "14px", lineHeight: "1.6" }}>
                Nếu bạn có bất kỳ thắc mắc nào về sản phẩm, dịch vụ hoặc đơn hàng, hãy kết nối với chúng tôi qua các kênh sau.
              </p>

              <div style={{ display: "flex", flexDirection: "column", gap: "24px" }}>
                {/* Phone */}
                <div style={{ display: "flex", gap: "16px", alignItems: "flex-start" }}>
                  <div style={{
                    width: "40px",
                    height: "40px",
                    borderRadius: "10px",
                    background: "rgba(255, 120, 45, 0.15)",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    flexShrink: 0
                  }}>
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ff782d" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ width: "18px", height: "18px" }}>
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                  </div>
                  <div>
                    <h4 style={{ margin: 0, fontSize: "12px", color: "rgba(255,255,255,0.5)", textTransform: "uppercase", letterSpacing: "0.05em" }}>Hotline</h4>
                    <p style={{ margin: "4px 0 0", fontSize: "16px", fontWeight: "700" }}>
                      <a href="tel:0332477689" style={{ color: "#fff", textDecoration: "none" }}>0332 477 689</a>
                    </p>
                  </div>
                </div>

                {/* Email */}
                <div style={{ display: "flex", gap: "16px", alignItems: "flex-start" }}>
                  <div style={{
                    width: "40px",
                    height: "40px",
                    borderRadius: "10px",
                    background: "rgba(47, 111, 237, 0.15)",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    flexShrink: 0
                  }}>
                    <svg viewBox="0 0 24 24" fill="none" stroke="#2f6fed" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ width: "18px", height: "18px" }}>
                      <path d="M22 6l-10 7L2 6" />
                      <rect x="2" y="4" width="20" height="16" rx="2" />
                    </svg>
                  </div>
                  <div>
                    <h4 style={{ margin: 0, fontSize: "12px", color: "rgba(255,255,255,0.5)", textTransform: "uppercase", letterSpacing: "0.05em" }}>Email hỗ trợ</h4>
                    <p style={{ margin: "4px 0 0", fontSize: "16px", fontWeight: "700" }}>
                      <a href="mailto:petworldshopvv@gmail.com" style={{ color: "#fff", textDecoration: "none" }}>petworldshopvv@gmail.com</a>
                    </p>
                  </div>
                </div>

                {/* Address */}
                <div style={{ display: "flex", gap: "16px", alignItems: "flex-start" }}>
                  <div style={{
                    width: "40px",
                    height: "40px",
                    borderRadius: "10px",
                    background: "rgba(255,255,255,0.1)",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    flexShrink: 0
                  }}>
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ width: "18px", height: "18px" }}>
                      <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z" />
                      <circle cx="12" cy="10" r="3" />
                    </svg>
                  </div>
                  <div>
                    <h4 style={{ margin: 0, fontSize: "12px", color: "rgba(255,255,255,0.5)", textTransform: "uppercase", letterSpacing: "0.05em" }}>Địa chỉ shop</h4>
                    <p style={{ margin: "4px 0 0", fontSize: "14px", fontWeight: "700", lineHeight: "1.5" }}>
                      137 Bình Long, Phường Bình Trị Đông,<br />Quận Bình Tân, TP. Hồ Chí Minh
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div className="sp-sla-box" style={{ marginTop: "40px" }}>
              <div className="sp-sla-ic" style={{ background: "rgba(47, 174, 115, 0.2)" }}>
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
              </div>
              <div>
                <p className="sp-sla-tt" style={{ color: "rgba(255,255,255,0.5)" }}>Cam kết phản hồi</p>
                <p className="sp-sla-vv" style={{ color: "rgba(255,255,255,0.9)", fontSize: "13px" }}>Chúng tôi sẽ kiểm tra thư và liên hệ lại với bạn trong vòng 24 giờ làm việc.</p>
              </div>
            </div>
          </div>

          {/* RIGHT: FORM PANEL */}
          <div className="sp-form-panel">
            <div className="sp-form-head">
              <div>
                <h3>Gửi lời nhắn cho cửa hàng</h3>
                <p>
                  Hãy điền đầy đủ các thông tin dưới đây, chúng tôi sẽ nhận được qua email và phản hồi bạn sớm nhất có thể.
                </p>
              </div>
            </div>

            <form className="sp-form" onSubmit={handleSubmit}>
              <div className="sp-field-row">
                <div className="sp-field">
                  <label htmlFor="sp-name">
                    Họ và tên của bạn <span className="sp-req">*</span>
                  </label>
                  <input
                    id="sp-name"
                    type="text"
                    placeholder="Ví dụ: Nguyễn Văn A"
                    value={form.name}
                    onChange={update("name")}
                    required
                  />
                </div>
                <div className="sp-field">
                  <label htmlFor="sp-email">
                    Số điện thoại hoặc Email <span className="sp-req">*</span>
                  </label>
                  <input
                    id="sp-email"
                    type="text"
                    placeholder="Ví dụ: 0901234567 hoặc mail@cua-ban.com"
                    value={form.email}
                    onChange={update("email")}
                    required
                  />
                </div>
              </div>

              <div className="sp-field" style={{ marginBottom: "20px" }}>
                <label htmlFor="sp-type">
                  Bạn cần hỗ trợ về chủ đề nào? <span className="sp-req">*</span>
                </label>
                <select id="sp-type" value={form.type} onChange={update("type")}>
                  {REQUEST_TYPES.map((t) => (
                    <option key={t}>{t}</option>
                  ))}
                </select>
              </div>

              <div className="sp-field" style={{ marginBottom: "20px" }}>
                <label htmlFor="sp-message">
                  Nội dung chi tiết câu hỏi <span className="sp-req">*</span>
                </label>
                <textarea
                  id="sp-message"
                  placeholder="Mô tả thắc mắc, phản hồi hoặc thông báo của bạn..."
                  value={form.message}
                  onChange={update("message")}
                  required
                />
              </div>

              {feedback && (
                <p className={`sp-feedback ${status}`}>{feedback}</p>
              )}

              <button
                type="submit"
                className="sp-submit-btn"
                disabled={status === "sending"}
              >
                {status === "sending" ? "Đang gửi đi..." : "Gửi lời nhắn ngay"}
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13" />
                  <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
              </button>
            </form>
          </div>
        </div>

        {/* LOCATION SECTION */}
        <div className="sp-location">
          <div className="sp-loc-info">
            <div className="sp-tag">Địa chỉ cửa hàng</div>
            <h3>Ghé thăm cửa hàng PetWorld</h3>
            <p className="sp-loc-desc">
              Bạn có thể đến trực tiếp cửa hàng để nhận tư vấn trực tiếp từ các bạn chăm sóc thú cưng cũng như trải nghiệm các dịch vụ chất lượng tại đây.
            </p>

            <div className="sp-loc-item">
              <div className="sp-loc-ic orange">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
              </div>
              <div>
                <p className="sp-loc-tt">Địa chỉ</p>
                <p className="sp-loc-vv">
                  137 Bình Long, Phường Bình Trị Đông,
                  <br />
                  Quận Bình Tân, TP. Hồ Chí Minh
                </p>
              </div>
            </div>

            <div className="sp-loc-item">
              <div className="sp-loc-ic blue">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
              </div>
              <div>
                <p className="sp-loc-tt">Thời gian mở cửa</p>
                <p className="sp-loc-vv">08:00 – 21:00 (Mở cửa tất cả các ngày trong tuần)</p>
              </div>
            </div>

            <a
              className="sp-directions-btn"
              href="https://www.google.com/maps/search/?api=1&query=137+B%C3%ACnh+Long%2C+B%C3%ACnh+Tr%E1%BB%8B+%C4%90%C3%B4ng%2C+H%E1%BB%93+Ch%C3%AD+Minh+70000%2C+Vi%E1%BB%87t+Nam"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg viewBox="0 0 24 24" fill="none" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <polygon points="3 11 22 2 13 21 11 13 3 11" />
              </svg>
              Xem đường đi trên Google Maps
            </a>
          </div>

          <div className="sp-map">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.6917763138865!2d106.60467777598816!3d10.758218189352222!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752dd49d68d18b%3A0xe961ad1591f48651!2s137%20B%C3%ACnh%20Long%2C%20B%C3%ACnh%20Tr%E1%BB%8B%20%C4%90%C3%B4ng%20A%2C%20B%C3%ACnh%20T%C3%A2n%2C%20H%E1%BB%93%20Ch%C3%AD%20Minh%2C%20Vietnam!5e0!3m2!1sen!2s!4v1710000000000!5m2!1sen!2s"
              width="100%"
              height="100%"
              style={{ border: 0 }}
              allowFullScreen=""
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              title="Vị trí cửa hàng PetWorld"
            ></iframe>
          </div>
        </div>
      </div>
    </main>
  );
}


