"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState, useRef, useEffect } from "react";
import { forgotPasswordSendOtp, forgotPasswordVerifyOtp, forgotPasswordReset } from "@/lib/auth";
import { toastSuccess } from "@/lib/toast";
import { resolveBackendImage } from "@/lib/format";

export default function ForgotPasswordForm() {
  const router = useRouter();
  
  // Steps: 1 (Email), 2 (OTP), 3 (Reset Password)
  const [step, setStep] = useState(1);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState("");
  
  // Data State
  const [email, setEmail] = useState("");
  const [otp, setOtp] = useState(["", "", "", "", "", ""]);
  const [resetToken, setResetToken] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  
  // UI State
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [countdown, setCountdown] = useState(0);
  const inputRefs = useRef([]);

  // Timer cho Gửi lại mã
  useEffect(() => {
    let timer;
    if (countdown > 0 && step === 2) {
      timer = setInterval(() => {
        setCountdown((prev) => prev - 1);
      }, 1000);
    }
    return () => clearInterval(timer);
  }, [countdown, step]);

  // Submit Bước 1: Gửi OTP
  const handleSendOtp = async (e) => {
    e?.preventDefault();
    setLoading(true);
    setErrors({});
    setMessage("");

    const result = await forgotPasswordSendOtp(email);
    if (result.ok) {
      toastSuccess(result.message || "Đã gửi mã OTP!");
      setStep(2);
      setCountdown(60); // 60s cooldown
    } else {
      setErrors(result.errors || {});
      setMessage(result.message || "Có lỗi xảy ra.");
    }
    setLoading(false);
  };

  // Logic nhập OTP
  const handleOtpChange = (index, value) => {
    if (!/^\d*$/.test(value)) return;
    const newOtp = [...otp];
    newOtp[index] = value.substring(value.length - 1);
    setOtp(newOtp);

    // Auto focus next
    if (value && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleOtpKeyDown = (index, e) => {
    if (e.key === "Backspace" && !otp[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const handleOtpPaste = (e) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData("text").replace(/\D/g, "").slice(0, 6);
    if (pastedData) {
      const newOtp = [...otp];
      for (let i = 0; i < pastedData.length; i++) {
        newOtp[i] = pastedData[i];
      }
      setOtp(newOtp);
      const nextIndex = Math.min(pastedData.length, 5);
      inputRefs.current[nextIndex]?.focus();
    }
  };

  // Submit Bước 2: Xác minh OTP
  const handleVerifyOtp = async (e) => {
    e.preventDefault();
    const otpString = otp.join("");
    if (otpString.length < 6) {
      setErrors({ otp: ["Vui lòng nhập đủ 6 số OTP."] });
      return;
    }

    setLoading(true);
    setErrors({});
    setMessage("");

    const result = await forgotPasswordVerifyOtp(email, otpString);
    if (result.ok) {
      setResetToken(result.data?.reset_token || result.reset_token);
      toastSuccess("Xác minh thành công!");
      setStep(3);
    } else {
      setErrors(result.errors || {});
      setMessage(result.message || "Mã OTP không hợp lệ.");
    }
    setLoading(false);
  };

  // Submit Bước 3: Đổi mật khẩu
  const handleResetPassword = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setMessage("");

    const result = await forgotPasswordReset(email, resetToken, password, passwordConfirmation);
    if (result.ok) {
      toastSuccess("Mật khẩu đã được đặt lại thành công!");
      router.push("/login");
    } else {
      setErrors(result.errors || {});
      setMessage(result.message || "Có lỗi xảy ra.");
    }
    setLoading(false);
  };

  return (
    <div className="login-layout-container">
      {/* Cột trái: Hình ảnh */}
      <div className="login-left-col">
        <h1 className="login-welcome-title">Quên mật khẩu?</h1>
        <p className="login-welcome-sub">
          Đừng lo lắng! Hãy nhập email của bạn và chúng tôi sẽ giúp bạn khôi phục quyền truy cập vào tài khoản PetWorld.
        </p>
        <div className="login-img-wrapper">
          <img src={resolveBackendImage("promo/register-pets.png")} alt="Khôi phục tài khoản" className="login-img" />
          <div className="login-badge-floating">
            <svg width="15" height="15" viewBox="0 0 512 512" fill="currentColor" style={{ marginRight: '4px' }}>
              <path d="M256 0c141.4 0 256 114.6 256 256s-114.6 256-256 256S0 397.4 0 256 114.6 0 256 0zM256 464c114.9 0 208-93.1 208-208S370.9 48 256 48 48 141.1 48 256 141.1 464 256 464zM224 160h64v128h-64zM224 320h64v64h-64z"/>
            </svg>
            <span>Hỗ trợ nhanh chóng</span>
          </div>
        </div>
      </div>

      {/* Cột phải: Multi-step Form */}
      <div className="login-form-card forgot-pw-card">
        {/* Progress Indicator */}
        <div className="fpw-progress">
          <div className={`fpw-step ${step >= 1 ? 'active' : ''}`}>
            <div className="fpw-step-circle">1</div>
            <span className="fpw-step-label">Email</span>
          </div>
          <div className={`fpw-step-line ${step >= 2 ? 'active' : ''}`}></div>
          <div className={`fpw-step ${step >= 2 ? 'active' : ''}`}>
            <div className="fpw-step-circle">2</div>
            <span className="fpw-step-label">OTP</span>
          </div>
          <div className={`fpw-step-line ${step >= 3 ? 'active' : ''}`}></div>
          <div className={`fpw-step ${step >= 3 ? 'active' : ''}`}>
            <div className="fpw-step-circle">3</div>
            <span className="fpw-step-label">Mật khẩu</span>
          </div>
        </div>

        {message && <div className="auth-alert-box">{message}</div>}

        {/* STEP 1: NHẬP EMAIL */}
        {step === 1 && (
          <>
            <div className="auth-header">
              <h2 className="auth-form-title">Khôi phục mật khẩu</h2>
              <p className="auth-form-sub">Nhập địa chỉ email bạn đã đăng ký để nhận mã xác nhận.</p>
            </div>
            <form className="auth-form" onSubmit={handleSendOtp} noValidate>
              <div className="auth-field">
                <label htmlFor="fpw-email">Email của bạn</label>
                <div className="auth-input-wrapper">
                  <span className="auth-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                      <polyline points="22,6 12,13 2,6" />
                    </svg>
                  </span>
                  <input
                    id="fpw-email"
                    type="email"
                    className="auth-input"
                    placeholder="email@example.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                  />
                </div>
                {errors.email && <span className="auth-error-msg">{errors.email[0]}</span>}
              </div>

              <button type="submit" className="auth-submit-btn uppercase-btn" disabled={loading || !email}>
                {loading ? "Đang gửi..." : "Gửi mã OTP"}
              </button>
            </form>
          </>
        )}

        {/* STEP 2: NHẬP OTP */}
        {step === 2 && (
          <>
            <div className="auth-header">
              <h2 className="auth-form-title">Nhập mã xác nhận</h2>
              <p className="auth-form-sub">
                Mã OTP 6 số đã được gửi tới <strong>{email}</strong>. Mã này có hiệu lực trong 10 phút.
              </p>
            </div>
            <form className="auth-form" onSubmit={handleVerifyOtp}>
              <div className="fpw-otp-container" onPaste={handleOtpPaste}>
                {otp.map((digit, index) => (
                  <input
                    key={index}
                    type="text"
                    inputMode="numeric"
                    maxLength={1}
                    className="fpw-otp-input"
                    value={digit}
                    onChange={(e) => handleOtpChange(index, e.target.value)}
                    onKeyDown={(e) => handleOtpKeyDown(index, e)}
                    ref={(el) => (inputRefs.current[index] = el)}
                  />
                ))}
              </div>
              {errors.otp && <span className="auth-error-msg" style={{textAlign: 'center'}}>{errors.otp[0]}</span>}

              <button type="submit" className="auth-submit-btn uppercase-btn fpw-mt" disabled={loading}>
                {loading ? "Đang xác minh..." : "Xác minh OTP"}
              </button>
              
              <div className="fpw-resend-box">
                <span className="fpw-resend-text">Chưa nhận được mã? </span>
                {countdown > 0 ? (
                  <span className="fpw-countdown">Gửi lại sau {countdown}s</span>
                ) : (
                  <button type="button" className="fpw-resend-btn" onClick={handleSendOtp} disabled={loading}>
                    Gửi lại mã
                  </button>
                )}
              </div>
            </form>
          </>
        )}

        {/* STEP 3: MẬT KHẨU MỚI */}
        {step === 3 && (
          <>
            <div className="auth-header">
              <h2 className="auth-form-title">Tạo mật khẩu mới</h2>
              <p className="auth-form-sub">Vui lòng nhập mật khẩu mới cho tài khoản của bạn.</p>
            </div>
            <form className="auth-form" onSubmit={handleResetPassword}>
              <div className="auth-field">
                <label htmlFor="fpw-password">Mật khẩu mới</label>
                <div className="auth-input-wrapper">
                  <span className="auth-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                  </span>
                  <input
                    id="fpw-password"
                    type={showPassword ? "text" : "password"}
                    className="auth-input"
                    placeholder="••••••"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                  />
                  <button
                    type="button"
                    className="auth-password-toggle"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? (
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" /><line x1="1" y1="1" x2="23" y2="23" /></svg>
                    ) : (
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                    )}
                  </button>
                </div>
                {errors.password && <span className="auth-error-msg">{errors.password[0]}</span>}
              </div>

              <div className="auth-field">
                <label htmlFor="fpw-password-confirm">Xác nhận mật khẩu mới</label>
                <div className="auth-input-wrapper">
                  <span className="auth-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67" />
                    </svg>
                  </span>
                  <input
                    id="fpw-password-confirm"
                    type={showConfirmPassword ? "text" : "password"}
                    className="auth-input"
                    placeholder="••••••"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                  />
                  <button
                    type="button"
                    className="auth-password-toggle"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  >
                    {showConfirmPassword ? (
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" /><line x1="1" y1="1" x2="23" y2="23" /></svg>
                    ) : (
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                    )}
                  </button>
                </div>
              </div>

              <button type="submit" className="auth-submit-btn uppercase-btn fpw-mt" disabled={loading}>
                {loading ? "Đang xử lý..." : "Đặt lại mật khẩu"}
              </button>
            </form>
          </>
        )}

        <p className="auth-switch-text">
          Đã nhớ lại mật khẩu? <Link href="/login">Trở về đăng nhập</Link>
        </p>
      </div>
    </div>
  );
}
