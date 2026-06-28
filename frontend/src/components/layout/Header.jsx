"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useMemo, useState, useSyncExternalStore } from "react";
import {
  getCartSnapshot,
  getServerCartSnapshot,
  parseCart,
  onCartChange,
} from "@/lib/cart";
import {
  getWishlistSnapshot,
  getServerWishlistSnapshot,
  parseWishlist,
  onWishlistChange,
} from "@/lib/wishlist";
import {
  getUserSnapshot,
  getServerUserSnapshot,
  parseUser,
  onAuthChange,
} from "@/lib/auth";
import { ROUTES, MAIN_NAV } from "@/lib/routes";

export default function Header() {
  const pathname = usePathname();
  const router = useRouter();
  const [keyword, setKeyword] = useState("");

  // Số lượng trong giỏ (localStorage) cho badge, cập nhật realtime khi giỏ đổi.
  const cartRaw = useSyncExternalStore(onCartChange, getCartSnapshot, getServerCartSnapshot);
  const cartCount = useMemo(
    () => parseCart(cartRaw).reduce((sum, line) => sum + line.quantity, 0),
    [cartRaw],
  );

  // Số sản phẩm yêu thích cho badge.
  const wishlistRaw = useSyncExternalStore(onWishlistChange, getWishlistSnapshot, getServerWishlistSnapshot);
  const wishlistCount = useMemo(() => parseWishlist(wishlistRaw).length, [wishlistRaw]);

  // Trạng thái đăng nhập: icon tài khoản trỏ /account khi đã đăng nhập, ngược lại /login.
  const userRaw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(userRaw), [userRaw]);

  const handleSearch = (event) => {
    event.preventDefault();
    const query = keyword.trim();
    router.push(query ? `${ROUTES.shop}?search=${encodeURIComponent(query)}` : ROUTES.shop);
  };

  return (
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
          <Link href={ROUTES.home} className="logo-link" id="logo">
            <img src="/image/Special_Offer_1-removebg-preview.png" alt="PetWorld Logo" className="logo-img" />
          </Link>

          <ul className="nav-menu">
            {MAIN_NAV.map((item) => {
              const isActive =
                item.href === "/"
                  ? pathname === "/"
                  : pathname.startsWith(item.href);
              return (
                <li className="nav-item" key={item.href}>
                  <Link
                    href={item.href}
                    className={`nav-link ${isActive ? "active" : ""}`}
                  >
                    {item.label}
                  </Link>
                </li>
              );
            })}
          </ul>

          <form className="search-container" onSubmit={handleSearch}>
            <input
              type="text"
              className="search-input"
              placeholder="Sen muốn tìm gì?...."
              aria-label="Tìm kiếm"
              value={keyword}
              onChange={(event) => setKeyword(event.target.value)}
            />
            <button type="submit" className="search-button" aria-label="Tìm kiếm nút">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
            </button>
          </form>

          <div className="nav-actions">
            <Link href={ROUTES.wishlist} className="action-item" id="wishlist-btn" aria-label="Danh sách yêu thích">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
              </svg>
              <span className="action-badge">{wishlistCount}</span>
            </Link>

            <Link
              href={user ? ROUTES.account : ROUTES.login}
              className="action-item"
              id="profile-btn"
              aria-label={user ? `Tài khoản: ${user.name}` : "Đăng nhập"}
              title={user ? user.name : "Đăng nhập"}
            >
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              {user && <span className="action-badge dot" />}
            </Link>

            <Link href={ROUTES.cart} className="action-item" id="cart-btn" aria-label="Giỏ hàng">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="9" cy="21" r="1" />
                <circle cx="20" cy="21" r="1" />
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
              </svg>
              <span className="action-badge">{cartCount}</span>
            </Link>
          </div>
        </nav>
      </div>
    </header>
  );
}
