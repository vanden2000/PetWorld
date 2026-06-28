"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useMemo, useSyncExternalStore } from "react";
import {
  getUserSnapshot,
  getServerUserSnapshot,
  parseUser,
  onAuthChange,
  logout,
} from "@/lib/auth";

export default function AccountView() {
  const router = useRouter();
  const raw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(raw), [raw]);

  const handleLogout = async () => {
    await logout();
    router.push("/");
    router.refresh();
  };

  if (!user) {
    return (
      <div className="account-guest">
        <p>Bạn chưa đăng nhập. Đăng nhập để quản lý đơn hàng và thông tin cá nhân.</p>
        <div className="account-guest-actions">
          <Link href="/login" className="account-btn">Đăng nhập</Link>
          <Link href="/register" className="account-btn outline">Đăng ký</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="account-card">
      <div className="account-head">
        <span className="account-avatar">{(user.name ?? "?").charAt(0).toUpperCase()}</span>
        <div>
          <h2>{user.name}</h2>
          <span>{user.email}</span>
        </div>
      </div>

      <ul className="account-info">
        <li>
          <span>Họ và tên</span>
          <strong>{user.name}</strong>
        </li>
        <li>
          <span>Email</span>
          <strong>{user.email}</strong>
        </li>
        <li>
          <span>Số điện thoại</span>
          <strong>{user.phone || "Chưa cập nhật"}</strong>
        </li>
      </ul>

      <div className="account-links">
        <Link href="/wishlist" className="account-link">❤️ Sản phẩm yêu thích</Link>
        <Link href="/cart" className="account-link">🛒 Giỏ hàng của tôi</Link>
      </div>

      <button type="button" className="account-logout" onClick={handleLogout}>
        Đăng xuất
      </button>
    </div>
  );
}
