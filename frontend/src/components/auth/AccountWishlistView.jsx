"use client";

import Link from "next/link";
import { useMemo, useSyncExternalStore } from "react";
import { useRouter } from "next/navigation";
import WishlistView from "@/components/wishlist/WishlistView";
import { getServerUserSnapshot, getUserSnapshot, logout, onAuthChange, parseUser } from "@/lib/auth";

const icons = {
  user: <><path d="M20 21a8 8 0 0 0-16 0" /><circle cx="12" cy="7" r="4" /></>, history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8" /><path d="M3 3v5h5M12 7v5l3 2" /></>, truck: <><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z" /><circle cx="7" cy="18" r="2" /><circle cx="17" cy="18" r="2" /></>, heart: <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z" />, shield: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />, logout: <><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" /></>,
};
const Icon = ({ name }) => <svg className="profile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{icons[name]}</svg>;

export default function AccountWishlistView() {
  const router = useRouter();
  const raw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(raw), [raw]);

  if (!user) return <div className="account-wishlist-guest"><WishlistView /></div>;

  return <div className="profile-shell account-wishlist-shell"><aside className="profile-sidebar"><div className="profile-identity"><div className="profile-avatar" style={user.avatar ? { backgroundImage: `url(${user.avatar})` } : undefined}>{user.avatar ? null : user.name.charAt(0).toUpperCase()}</div><strong>{user.name}</strong><span>Thành viên PetWorld</span></div><nav aria-label="Quản lý tài khoản"><Link href="/account"><Icon name="user" />Hồ sơ</Link><Link href="/account/orders"><Icon name="history" />Lịch sử đơn hàng</Link><Link href="/account/wishlist" className="active"><Icon name="heart" />Sản phẩm yêu thích</Link></nav><button className="profile-logout" onClick={async () => { await logout(); router.push("/"); router.refresh(); }}><Icon name="logout" />Đăng xuất</button></aside><div className="profile-content account-wishlist-content"><header className="profile-heading"><span>Tài khoản của tôi</span><h1>Sản phẩm yêu thích</h1><p>Lưu lại những món đồ bạn muốn dành cho người bạn nhỏ của mình.</p></header><section className="account-wishlist-panel"><WishlistView /></section></div></div>;
}
