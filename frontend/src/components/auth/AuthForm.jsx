"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useState } from "react";
import { login, register } from "@/lib/auth";

const EMPTY_LOGIN = { email: "", password: "" };
const EMPTY_REGISTER = { name: "", email: "", phone: "", password: "", password_confirmation: "" };

export default function AuthForm({ mode = "login" }) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const isLogin = mode === "login";

  const [form, setForm] = useState(isLogin ? EMPTY_LOGIN : EMPTY_REGISTER);
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  
  // Custom states for password visibility toggles
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  
  // Checkbox agreement state
  const [agree, setAgree] = useState(false);

  const update = (field) => (event) => setForm({ ...form, [field]: event.target.value });

  const handleSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);
    setErrors({});
    setMessage("");

    // Simple agreement validation for register mode
    if (!isLogin && !agree) {
      setErrors({ agree: ["Bạn cần đồng ý với các điều khoản dịch vụ và chính sách bảo mật."] });
      setLoading(false);
      return;
    }

    const result = isLogin ? await login(form) : await register(form);

    if (result.ok) {
      const redirect = searchParams.get("redirect") || "/account";
      router.push(redirect);
      router.refresh();
      return;
    }

    setErrors(result.errors ?? {});
    setMessage(result.message ?? "");
    setLoading(false);
  };

  // --- 1. LAYOUT CHO TRANG ĐĂNG NHẬP (LOGIN) ---
  if (isLogin) {
    return (
      <div className="login-layout-container">
        {/* Cột trái: Tiêu đề chào mừng và ảnh bo tròn có badge */}
        <div className="login-left-col">
          <h1 className="login-welcome-title">Chào mừng trở lại!</h1>
          <p className="login-welcome-sub">
            Hãy đăng nhập để tiếp tục hành trình chăm sóc những người bạn bốn chân của bạn cùng Petworld.
          </p>
          <div className="login-img-wrapper">
            <img src="/image/promo/register-pets.png" 
              alt="Chào mừng trở lại!" 
              className="login-img"/>
            <div className="login-badge-floating">
              {/* Paw SVG Icon */}
              <svg width="15" height="15" viewBox="0 0 512 512" fill="currentColor" style={{ marginRight: '4px' }}>
                <path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C34.9 480 14 459.1 14 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM310.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z" />
              </svg>
              <span>Thành viên hạnh phúc</span>
            </div>
          </div>
        </div>

        {/* Cột phải: Form Đăng nhập nằm gọn trong Card trắng riêng */}
        <div className="login-form-card">
          <div className="auth-header">
            <h2 className="auth-form-title">Đăng nhập</h2>
            <p className="auth-form-sub">Nhập thông tin tài khoản của bạn để truy cập</p>
          </div>

          {message && <div className="auth-alert-box">{message}</div>}

          <form className="auth-form" onSubmit={handleSubmit} noValidate>
            {/* Email / Số điện thoại */}
            <div className="auth-field">
              <label htmlFor="auth-email">Số điện thoại / Email</label>
              <div className="auth-input-wrapper">
                <span className="auth-input-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </span>
                <input 
                  id="auth-email" 
                  type="email" 
                  className="auth-input" 
                  placeholder="example@email.com" 
                  value={form.email} 
                  onChange={update("email")} 
                />
              </div>
              {errors.email && <span className="auth-error-msg">{errors.email[0]}</span>}
            </div>

            {/* Mật khẩu & Quên mật khẩu */}
            <div className="auth-field">
              <div className="auth-label-row-login">
                <label htmlFor="auth-password">Mật khẩu</label>
                <Link href="/forgot-password" className="auth-forgot-link">Quên mật khẩu?</Link>
              </div>
              <div className="auth-input-wrapper">
                <span className="auth-input-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                  </svg>
                </span>
                <input 
                  id="auth-password" 
                  type={showPassword ? "text" : "password"} 
                  className="auth-input" 
                  placeholder="••••••••" 
                  value={form.password} 
                  onChange={update("password")} 
                />
                <button 
                  type="button" 
                  className="auth-password-toggle" 
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label={showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                >
                  {showPassword ? (
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                      <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                  ) : (
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  )}
                </button>
              </div>
              {errors.password && <span className="auth-error-msg">{errors.password[0]}</span>}
            </div>

            {/* Submit Button */}
            <button type="submit" className="auth-submit-btn uppercase-btn" disabled={loading}>
              {loading ? "Đang xử lý..." : "Đăng nhập"}
            </button>
          </form>

          {/* Social login option */}
          <div className="auth-social-divider">
            Hoặc đăng nhập bằng
          </div>
          <div className="auth-social-grid">
            <button type="button" className="auth-social-btn" onClick={() => console.log('Google login clicked')}>
              <svg className="auth-social-icon" viewBox="0 0 24 24" width="18" height="18">
                <path fill="#EA4335" d="M12 5.04c1.66 0 3.2.57 4.38 1.69l3.27-3.27C17.68 1.54 14.98 0 12 0 7.35 0 3.37 2.67 1.48 6.56l3.86 3C6.26 6.94 8.92 5.04 12 5.04z" />
                <path fill="#4285F4" d="M23.49 12.27c0-.81-.07-1.59-.2-2.36H12v4.51h6.43c-.28 1.44-1.09 2.67-2.3 3.48l3.57 2.77c2.08-1.92 3.29-4.75 3.29-8.4z" />
                <path fill="#FBBC05" d="M5.34 14.29c-.24-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29L1.48 6.71C.53 8.6.01 10.74.01 13c0 2.26.52 4.4 1.47 6.29l3.86-3z" />
                <path fill="#34A853" d="M12 24c3.24 0 5.97-1.08 7.96-2.91l-3.57-2.77c-.99.66-2.26 1.06-3.86 1.06-3.08 0-5.74-1.9-6.66-4.52l-3.86 3C3.37 21.33 7.35 24 12 24z" />
              </svg>
              <span>Google</span>
            </button>
            <button type="button" className="auth-social-btn" onClick={() => console.log('Facebook login clicked')}>
              <svg className="auth-social-icon" fill="#1877F2" viewBox="0 0 24 24" width="18" height="18">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
              </svg>
              <span>Facebook</span>
            </button>
          </div>

          {/* Form switcher */}
          <p className="auth-switch-text">
            Chưa có tài khoản? <Link href="/register">Đăng ký ngay</Link>
          </p>
        </div>
      </div>
    );
  }

  // --- 2. LAYOUT CHO TRANG ĐĂNG KÝ (REGISTER) ---
  return (
    <div className="auth-card">
      {/* Left Column: Image Banner with text overlay */}
      <div className="auth-banner">
        <img src="/image/promo/register-pets.png" alt="Chào mừng bạn đến với Ngôi nhà Petworld!" className="auth-banner-img" />
        <div className="auth-banner-overlay">
          <h2>Chào mừng bạn đến với Ngôi nhà Petworld!</h2>
          <p>Gia nhập cộng đồng yêu thú cưng để nhận những ưu đãi đặc biệt và lộ trình chăm sóc sức khỏe tốt nhất cho người bạn bốn chân của bạn.</p>
        </div>
      </div>

      {/* Right Column: Auth Form */}
      <div className="auth-form-container">
        <div className="auth-header">
          <h1 className="auth-form-title">Đăng ký tài khoản</h1>
          <p className="auth-form-sub">Bắt đầu hành trình chăm sóc thú cưng cùng Petworld ngay hôm nay.</p>
        </div>

        {message && <div className="auth-alert-box">{message}</div>}

        <form className="auth-form" onSubmit={handleSubmit} noValidate>
          {/* Họ và tên */}
          <div className="auth-field">
            <label htmlFor="auth-name">Họ và tên</label>
            <div className="auth-input-wrapper">
              <span className="auth-input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
              </span>
              <input 
                id="auth-name" 
                type="text" 
                className="auth-input" 
                placeholder="Nguyễn Văn A" 
                value={form.name} 
                onChange={update("name")} 
              />
            </div>
            {errors.name && <span className="auth-error-msg">{errors.name[0]}</span>}
          </div>

          {/* Số điện thoại */}
          <div className="auth-field">
            <label htmlFor="auth-phone">Số điện thoại</label>
            <div className="auth-input-wrapper">
              <span className="auth-input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
              </span>
              <input 
                id="auth-phone" 
                type="tel" 
                className="auth-input" 
                placeholder="090x xxx xxx" 
                value={form.phone} 
                onChange={update("phone")} 
              />
            </div>
            {errors.phone && <span className="auth-error-msg">{errors.phone[0]}</span>}
          </div>

          {/* Email */}
          <div className="auth-field">
            <label htmlFor="auth-email">Email</label>
            <div className="auth-input-wrapper">
              <span className="auth-input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                  <polyline points="22,6 12,13 2,6" />
                </svg>
              </span>
              <input 
                id="auth-email" 
                type="email" 
                className="auth-input" 
                placeholder="email@example.com" 
                value={form.email} 
                onChange={update("email")} 
              />
            </div>
            {errors.email && <span className="auth-error-msg">{errors.email[0]}</span>}
          </div>

          {/* Mật khẩu */}
          <div className="auth-field">
            <label htmlFor="auth-password">Mật khẩu</label>
            <div className="auth-input-wrapper">
              <span className="auth-input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
              </span>
              <input 
                id="auth-password" 
                type={showPassword ? "text" : "password"} 
                className="auth-input" 
                placeholder="••••••" 
                value={form.password} 
                onChange={update("password")} 
              />
              <button 
                type="button" 
                className="auth-password-toggle" 
                onClick={() => setShowPassword(!showPassword)}
                aria-label={showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
              >
                {showPassword ? (
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                ) : (
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                )}
              </button>
            </div>
            {errors.password && <span className="auth-error-msg">{errors.password[0]}</span>}
          </div>

          {/* Nhập lại mật khẩu */}
          <div className="auth-field">
            <label htmlFor="auth-password-confirmation">Nhập lại mật khẩu</label>
            <div className="auth-input-wrapper">
              <span className="auth-input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67" />
                </svg>
              </span>
              <input 
                id="auth-password-confirmation" 
                type={showConfirmPassword ? "text" : "password"} 
                className="auth-input" 
                placeholder="••••••" 
                value={form.password_confirmation} 
                onChange={update("password_confirmation")} 
              />
              <button 
                type="button" 
                className="auth-password-toggle" 
                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                aria-label={showConfirmPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
              >
                {showConfirmPassword ? (
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                ) : (
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                )}
              </button>
            </div>
          </div>

          {/* Agreement Checkbox */}
          <div className="auth-field">
            <label className="auth-agreement">
              <input 
                type="checkbox" 
                checked={agree} 
                onChange={(e) => setAgree(e.target.checked)} 
              />
              <span>
                Tôi đồng ý với các <Link href="/terms">Điều khoản dịch vụ</Link> và <Link href="/privacy">Chính sách bảo mật</Link> của Petworld.
              </span>
            </label>
            {errors.agree && <span className="auth-error-msg">{errors.agree[0]}</span>}
          </div>

          {/* Submit Button */}
          <button type="submit" className="auth-submit-btn" disabled={loading}>
            {loading ? "Đang xử lý..." : "Đăng ký ngay"}
          </button>
        </form>

        {/* Form switcher */}
        <p className="auth-switch-text">
          Đã có tài khoản? <Link href="/login">Đăng nhập</Link>
        </p>

        {/* Social login option */}
        <div className="auth-social-divider">
          HOẶC ĐĂNG KÝ BẰNG
        </div>
        <div className="auth-social-grid">
          <button type="button" className="auth-social-btn" onClick={() => console.log('Google login clicked')}>
            <svg className="auth-social-icon" viewBox="0 0 24 24" width="18" height="18">
              <path fill="#EA4335" d="M12 5.04c1.66 0 3.2.57 4.38 1.69l3.27-3.27C17.68 1.54 14.98 0 12 0 7.35 0 3.37 2.67 1.48 6.56l3.86 3C6.26 6.94 8.92 5.04 12 5.04z" />
              <path fill="#4285F4" d="M23.49 12.27c0-.81-.07-1.59-.2-2.36H12v4.51h6.43c-.28 1.44-1.09 2.67-2.3 3.48l3.57 2.77c2.08-1.92 3.29-4.75 3.29-8.4z" />
              <path fill="#FBBC05" d="M5.34 14.29c-.24-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29L1.48 6.71C.53 8.6.01 10.74.01 13c0 2.26.52 4.4 1.47 6.29l3.86-3z" />
              <path fill="#34A853" d="M12 24c3.24 0 5.97-1.08 7.96-2.91l-3.57-2.77c-.99.66-2.26 1.06-3.86 1.06-3.08 0-5.74-1.9-6.66-4.52l-3.86 3C3.37 21.33 7.35 24 12 24z" />
            </svg>
            <span>Google</span>
          </button>
          <button type="button" className="auth-social-btn" onClick={() => console.log('Facebook login clicked')}>
            <svg className="auth-social-icon" fill="#1877F2" viewBox="0 0 24 24" width="18" height="18">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
            </svg>
            <span>Facebook</span>
          </button>
        </div>
      </div>
    </div>
  );
}
