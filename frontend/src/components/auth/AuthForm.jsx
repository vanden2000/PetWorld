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

  const update = (field) => (event) => setForm({ ...form, [field]: event.target.value });

  const handleSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);
    setErrors({});
    setMessage("");

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

  return (
    <div className="auth-card">
      <h1 className="auth-title">{isLogin ? "Đăng nhập" : "Tạo tài khoản"}</h1>
      <p className="auth-sub">
        {isLogin
          ? "Chào mừng bạn quay lại PetWorld!"
          : "Tham gia PetWorld để mua sắm dễ dàng hơn."}
      </p>

      {message && <div className="auth-alert">{message}</div>}

      <form className="auth-form" onSubmit={handleSubmit} noValidate>
        {!isLogin && (
          <div className="auth-field">
            <label htmlFor="auth-name">Họ và tên</label>
            <input id="auth-name" type="text" placeholder="Nguyễn Văn A" value={form.name} onChange={update("name")} />
            {errors.name && <span className="auth-error">{errors.name[0]}</span>}
          </div>
        )}

        <div className="auth-field">
          <label htmlFor="auth-email">Email</label>
          <input id="auth-email" type="email" placeholder="email@example.com" value={form.email} onChange={update("email")} />
          {errors.email && <span className="auth-error">{errors.email[0]}</span>}
        </div>

        {!isLogin && (
          <div className="auth-field">
            <label htmlFor="auth-phone">Số điện thoại (tùy chọn)</label>
            <input id="auth-phone" type="tel" placeholder="0912345678" value={form.phone} onChange={update("phone")} />
            {errors.phone && <span className="auth-error">{errors.phone[0]}</span>}
          </div>
        )}

        <div className="auth-field">
          <label htmlFor="auth-password">Mật khẩu</label>
          <input id="auth-password" type="password" placeholder="••••••••" value={form.password} onChange={update("password")} />
          {errors.password && <span className="auth-error">{errors.password[0]}</span>}
        </div>

        {!isLogin && (
          <div className="auth-field">
            <label htmlFor="auth-password2">Xác nhận mật khẩu</label>
            <input
              id="auth-password2"
              type="password"
              placeholder="••••••••"
              value={form.password_confirmation}
              onChange={update("password_confirmation")}
            />
          </div>
        )}

        <button type="submit" className="auth-submit" disabled={loading}>
          {loading ? "Đang xử lý..." : isLogin ? "Đăng nhập" : "Đăng ký"}
        </button>
      </form>

      <p className="auth-switch">
        {isLogin ? (
          <>
            Chưa có tài khoản? <Link href="/register">Đăng ký ngay</Link>
          </>
        ) : (
          <>
            Đã có tài khoản? <Link href="/login">Đăng nhập</Link>
          </>
        )}
      </p>
    </div>
  );
}
