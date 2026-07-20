"use client";

import Image from "next/image";
import { useState } from "react";
import { API_BASE_URL } from "@/lib/api";
import { resolveBackendImage } from "@/lib/format";

const REQUEST_TYPES = [
  "Hỗ trợ đơn hàng",
  "Tư vấn sản phẩm",
  "Bảo hành & đổi trả",
  "Tài khoản & thanh toán",
  "Khác",
];

// Dấu chân thú cưng — dùng lại đúng hoa văn ở Footer để trang trí panel đội ngũ.
const PAW_PATH =
  "M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C34.9 480 14 459.1 14 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM310.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z";

const INITIAL_FORM = {
  name: "",
  email: "",
  order_code: "",
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
        // Lấy thông báo lỗi validate đầu tiên nếu có.
        const firstError = json?.errors
          ? Object.values(json.errors)[0]?.[0]
          : null;
        throw new Error(firstError || json?.message || "Gửi yêu cầu thất bại.");
      }

      setStatus("success");
      setFeedback(
        json?.data?.message ||
          "Đã gửi yêu cầu hỗ trợ. PetWorld sẽ phản hồi bạn sớm nhất!"
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
        <div className="sp-blob sp-blob-1" />
        <div className="sp-blob sp-blob-2" />

        {/* HERO */}
        <div className="sp-eyebrow">Trung tâm hỗ trợ</div>
        <h1 className="sp-title">
          Chúng tôi luôn sẵn sàng <span className="sp-hi">giải đáp</span>
        </h1>
        <p className="sp-sub">
          Dù bạn cần hỗ trợ đơn hàng, tư vấn sản phẩm hay xử lý sự cố — đội ngũ
          PetWorld đồng hành cùng bạn mọi lúc.
        </p>

        {/* QUICK CHANNELS */}
        <div className="sp-channels">
          <div className="sp-chan-card">
            <div className="sp-chan-ic orange">
              <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
            </div>
            <p className="sp-chan-ttl">Gọi hotline</p>
            <p className="sp-chan-dd">+84 123 456 789 · 08:00 đến 21:00</p>
          </div>

          <div className="sp-chan-card">
            <span className="sp-live-dot">Online</span>
            <div className="sp-chan-ic green">
              <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
              </svg>
            </div>
            <p className="sp-chan-ttl">Chat trực tuyến</p>
            <p className="sp-chan-dd">Trung bình phản hồi dưới 2 phút</p>
          </div>

          <div className="sp-chan-card">
            <span className="sp-note-dot">Phản hồi 24h</span>
            <div className="sp-chan-ic blue">
              <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M22 6l-10 7L2 6" />
                <rect x="2" y="4" width="20" height="16" rx="2" />
              </svg>
            </div>
            <p className="sp-chan-ttl">Email hỗ trợ</p>
            <p className="sp-chan-dd">thegioipetworld@gmail.com</p>
          </div>

          <div className="sp-chan-card">
            <div className="sp-chan-ic ink">
              <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 2-3 4" />
                <path d="M12 17h.01" />
              </svg>
            </div>
            <p className="sp-chan-ttl">Câu hỏi thường gặp</p>
            <p className="sp-chan-dd">Tự tra cứu hơn 80 chủ đề hỗ trợ</p>
          </div>
        </div>

        {/* STAGE */}
        <div className="sp-stage">
          {/* LEFT: BRAND PANEL */}
          <div className="sp-brand-panel">
            <span className="sp-paw sp-paw-1" aria-hidden="true">
              <svg viewBox="0 0 512 512" fill="currentColor">
                <path d={PAW_PATH} />
              </svg>
            </span>
            <span className="sp-paw sp-paw-2" aria-hidden="true">
              <svg viewBox="0 0 512 512" fill="currentColor">
                <path d={PAW_PATH} />
              </svg>
            </span>

            <div className="sp-avatars">
              <div className="sp-av o">TĐ</div>
              <div className="sp-av b">VM</div>
              <div className="sp-av g">CT</div>
              <div className="sp-av g">TP</div>
            </div>

            <h2>Đội ngũ tư vấn luôn sẵn sàng</h2>
            <p className="sp-desc">
              4 chuyên viên hỗ trợ am hiểu sản phẩm và dinh dưỡng về codex, claude code, antigravity sẵn
              sàng đồng hành cùng bạn từ lúc đặt hàng đến khi nhận sản phẩm.
            </p>

            <div className="sp-stat-grid">
              <div className="sp-stat">
                <div className="sp-num">4.9/5</div>
                <div className="sp-lab">Hài lòng sau hỗ trợ</div>
              </div>
              <div className="sp-stat">
                <div className="sp-num">&lt;2h</div>
                <div className="sp-lab">Thời gian phản hồi trung bình</div>
              </div>
            </div>

            <p className="sp-cat-title">Chủ đề hỗ trợ phổ biến</p>
            <div className="sp-cat-list">
              <span className="sp-cat-chip">Đơn hàng &amp; vận chuyển</span>
              <span className="sp-cat-chip">Sản phẩm &amp; dinh dưỡng</span>
              <span className="sp-cat-chip">Bảo hành &amp; đổi trả</span>
              <span className="sp-cat-chip">Tài khoản &amp; thanh toán</span>
            </div>

            <div className="sp-sla-box">
              <div className="sp-sla-ic">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M9 11l3 3L22 4" />
                  <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                </svg>
              </div>
              <div>
                <p className="sp-sla-tt">Cam kết hỗ trợ</p>
                <p className="sp-sla-vv">Phản hồi mọi yêu cầu trong 24 giờ</p>
              </div>
            </div>
          </div>

          {/* RIGHT: TICKET FORM */}
          <div className="sp-form-panel">
            <div className="sp-form-head">
              <div>
                <h3>Tạo yêu cầu hỗ trợ</h3>
                <p>
                  Mô tả vấn đề bạn gặp phải, chuyên viên phù hợp sẽ liên hệ lại
                  trong thời gian sớm nhất.
                </p>
              </div>
              <div className="sp-response-pill">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
                Phản hồi nhanh
              </div>
            </div>

            <form className="sp-form" onSubmit={handleSubmit}>
              <div className="sp-field-row">
                <div className="sp-field">
                  <label htmlFor="sp-name">
                    Họ và tên <span className="sp-req">*</span>
                  </label>
                  <input
                    id="sp-name"
                    type="text"
                    placeholder="Nguyễn Văn A"
                    value={form.name}
                    onChange={update("name")}
                    required
                  />
                </div>
                <div className="sp-field">
                  <label htmlFor="sp-email">
                    Email hoặc SĐT <span className="sp-req">*</span>
                  </label>
                  <input
                    id="sp-email"
                    type="text"
                    inputMode="text"
                    placeholder="ban@email.com hoặc 0901234567"
                    value={form.email}
                    onChange={update("email")}
                    required
                  />
                </div>
              </div>

              <div className="sp-field-row">
                <div className="sp-field">
                  <label htmlFor="sp-order">
                    Mã đơn hàng <span className="sp-opt">(nếu có)</span>
                  </label>
                  <input
                    id="sp-order"
                    type="text"
                    placeholder="PW-2026-00123"
                    value={form.order_code}
                    onChange={update("order_code")}
                  />
                </div>
                <div className="sp-field">
                  <label htmlFor="sp-type">
                    Loại yêu cầu <span className="sp-req">*</span>
                  </label>
                  <select id="sp-type" value={form.type} onChange={update("type")}>
                    {REQUEST_TYPES.map((t) => (
                      <option key={t}>{t}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="sp-field">
                <label htmlFor="sp-message">
                  Mô tả chi tiết <span className="sp-req">*</span>
                </label>
                <textarea
                  id="sp-message"
                  placeholder="Mô tả vấn đề bạn đang gặp phải, PetWorld sẽ hỗ trợ nhanh nhất..."
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
                {status === "sending" ? "Đang gửi..." : "Gửi yêu cầu hỗ trợ"}
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </button>

              <div className="sp-trust-row">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                <span>Thông tin của bạn được bảo mật tuyệt đối</span>
              </div>
            </form>
          </div>
        </div>

        {/* LOCATION */}
        <div className="sp-location">
          <div className="sp-loc-info">
            <div className="sp-tag">Hỗ trợ trực tiếp</div>
            <h3>Đến văn phòng hỗ trợ PetWorld</h3>
            <p className="sp-loc-desc">
              Nếu vấn đề cần xử lý trực tiếp, đội ngũ hỗ trợ tại văn phòng luôn
              sẵn sàng tiếp đón bạn.
            </p>

            <div className="sp-loc-item">
              <div className="sp-loc-ic orange">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
              </div>
              <div>
                <p className="sp-loc-tt">Địa chỉ</p>
                <p className="sp-loc-vv">
                  137 Bình Long, Bình Trị Đông,
                  <br />
                  Bình Tân, TP.HCM 70000
                </p>
              </div>
            </div>

            <div className="sp-loc-item">
              <div className="sp-loc-ic blue">
                <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <path d="M12 6v6l4 2" />
                </svg>
              </div>
              <div>
                <p className="sp-loc-tt">Giờ đón tiếp</p>
                <p className="sp-loc-vv">08:00 – 21:00, tất cả các ngày</p>
              </div>
            </div>

            <a
              className="sp-directions-btn"
              href="https://www.google.com/maps/search/?api=1&query=137+B%C3%ACnh+Long%2C+B%C3%ACnh+Tr%E1%BB%8B+%C4%90%C3%B4ng%2C+H%E1%BB%93+Ch%C3%AD+Minh+70000%2C+Vi%E1%BB%87t+Nam"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M7 17 17 7M8 7h9v9" />
              </svg>
              Chỉ đường trên Google Maps
            </a>
          </div>

          <div className="sp-map">
            <Image
              src={resolveBackendImage("contact/store.jpg")}
              alt="Không gian cửa hàng PetWorld"
              fill
              sizes="(max-width: 900px) 100vw, 55vw"
              style={{ objectFit: "cover" }}
            />
          </div>
        </div>

        {/* FAQ */}
        <div className="sp-faq">
          <div className="sp-faq-top">
            <div>
              <div className="sp-tag">Trước khi gửi yêu cầu</div>
              <h3>Câu hỏi thường gặp</h3>
            </div>
          </div>
          <div className="sp-faq-grid">
            <div className="sp-faq-item">
              <div className="sp-faq-qic">1</div>
              <div>
                <p className="sp-faq-qt">Làm sao để theo dõi đơn hàng?</p>
                <p className="sp-faq-qa">
                  Vào mục &quot;Đơn hàng của tôi&quot; trong tài khoản, hoặc dùng
                  mã đơn hàng để tra cứu trực tiếp.
                </p>
              </div>
            </div>
            <div className="sp-faq-item">
              <div className="sp-faq-qic">2</div>
              <div>
                <p className="sp-faq-qt">Chính sách đổi trả như thế nào?</p>
                <p className="sp-faq-qa">
                  Đổi trả miễn phí trong 7 ngày với sản phẩm còn nguyên bao bì,
                  chưa qua sử dụng.
                </p>
              </div>
            </div>
            <div className="sp-faq-item">
              <div className="sp-faq-qic">3</div>
              <div>
                <p className="sp-faq-qt">Thời gian giao hàng bao lâu?</p>
                <p className="sp-faq-qa">
                  2 đến 4 ngày làm việc trong nội thành, 4 đến 7 ngày với khu vực
                  tỉnh xa.
                </p>
              </div>
            </div>
            <div className="sp-faq-item">
              <div className="sp-faq-qic">4</div>
              <div>
                <p className="sp-faq-qt">Có tư vấn dinh dưỡng theo bé không?</p>
                <p className="sp-faq-qa">
                  Có, chọn &quot;Tư vấn sản phẩm&quot; trong form và mô tả giống
                  loài, cân nặng để được hỗ trợ chính xác.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}
