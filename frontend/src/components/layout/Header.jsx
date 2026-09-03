"use client";

import Link from "next/link";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useCallback, useMemo, useState, useSyncExternalStore, useEffect, useRef } from "react";
import {
  getCartSnapshot,
  getServerCartSnapshot,
  parseCart,
  onCartChange,
} from "@/lib/cart";
import {
  getUserSnapshot,
  getServerUserSnapshot,
  parseUser,
  onAuthChange,
  logout,
  getNotifications,
  getUnreadNotificationsCount,
  markNotificationAsRead,
  markAllNotificationsAsRead,
} from "@/lib/auth";
import { ROUTES, MAIN_NAV } from "@/lib/routes";
import { resolveBackendImage } from "@/lib/format";
import SmartSearchFlyout from "@/components/layout/SmartSearchFlyout";
import { getSmartSearchResults } from "@/lib/search";

export default function Header() {
  const pathname = usePathname();
  const router = useRouter();
  const searchParams = useSearchParams();
  const urlKeyword = pathname === ROUTES.shop ? (searchParams.get("search") ?? "") : "";
  const [keyword, setKeyword] = useState(urlKeyword);
  const [previousUrlKeyword, setPreviousUrlKeyword] = useState(urlKeyword);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isMobileSearchOpen, setIsMobileSearchOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);

  // Trạng thái tìm kiếm thông minh
  const [smartSearchData, setSmartSearchData] = useState(null);
  const [isSmartSearchLoading, setIsSmartSearchLoading] = useState(false);
  const [isSmartSearchOpen, setIsSmartSearchOpen] = useState(false);
  const searchContainerRef = useRef(null);

  // Debounce gọi API tìm kiếm thông minh khi người dùng gõ từ khóa
  useEffect(() => {
    const trimmed = keyword.trim();
    if (!trimmed) {
      setSmartSearchData(null);
      setIsSmartSearchLoading(false);
      return;
    }

    setIsSmartSearchLoading(true);
    const timer = setTimeout(async () => {
      const results = await getSmartSearchResults(trimmed);
      setSmartSearchData(results);
      setIsSmartSearchLoading(false);
    }, 280);

    return () => clearTimeout(timer);
  }, [keyword]);

  // Đóng bảng tìm kiếm thông minh khi click ra ngoài hoặc bấm ESC
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (searchContainerRef.current && !searchContainerRef.current.contains(e.target)) {
        setIsSmartSearchOpen(false);
      }
    };
    const handleKeyDown = (e) => {
      if (e.key === "Escape") {
        setIsSmartSearchOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, []);

  // Đồng bộ khi người dùng đi bằng back/forward hoặc mở URL tìm kiếm trực tiếp.
  // Điều chỉnh state ngay trong render để không tạo thêm một render từ Effect.
  if (urlKeyword !== previousUrlKeyword) {
    setPreviousUrlKeyword(urlKeyword);
    setKeyword(urlKeyword);
  }

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY;
      if (scrollY > 120) {
        setIsScrolled(true);
      } else if (scrollY < 20) {
        setIsScrolled(false);
      }
    };
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  // Số lượng trong giỏ (localStorage) cho badge, cập nhật realtime khi giỏ đổi.
  const cartRaw = useSyncExternalStore(onCartChange, getCartSnapshot, getServerCartSnapshot);
  const cartCount = useMemo(
    () => parseCart(cartRaw).reduce((sum, line) => sum + line.quantity, 0),
    [cartRaw],
  );

  // Số sản phẩm yêu thích cho badge.

  const userRaw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(userRaw), [userRaw]);

  // Notifications State & Logic
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);

  const timeAgo = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return "Vừa xong";
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} phút trước`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} giờ trước`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} ngày trước`;

    return date.toLocaleDateString("vi-VN", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const fetchNotifications = useCallback(async () => {
    if (!user) {
      setNotifications([]);
      setUnreadCount(0);
      return;
    }
    const countRes = await getUnreadNotificationsCount();
    if (countRes.ok) {
      setUnreadCount(countRes.data.unread_count || 0);
    }
    const listRes = await getNotifications({ page: 1 });
    if (listRes.ok) {
      setNotifications(listRes.data.notifications || []);
    }
  }, [user]);

  useEffect(() => {
    let cancelled = false;
    queueMicrotask(() => {
      if (!cancelled) fetchNotifications();
    });
    const interval = setInterval(fetchNotifications, 30000);
    return () => {
      cancelled = true;
      clearInterval(interval);
    };
  }, [fetchNotifications]);

  const handleMarkAsRead = async (id, actionUrl) => {
    await markNotificationAsRead(id);
    setNotifications(prev => prev.map(n => n.id === id ? { ...n, read_at: new Date().toISOString() } : n));
    setUnreadCount(prev => Math.max(0, prev - 1));
    if (actionUrl) {
      router.push(actionUrl);
    }
  };

  const handleMarkAllAsRead = async () => {
    const res = await markAllNotificationsAsRead();
    if (res.ok) {
      setNotifications(prev => prev.map(n => ({ ...n, read_at: new Date().toISOString() })));
      setUnreadCount(0);
    }
  };

  const handleSearch = (event) => {
    event.preventDefault();
    const query = keyword.trim();
    setIsSmartSearchOpen(false);
    router.push(query ? `${ROUTES.shop}?search=${encodeURIComponent(query)}` : ROUTES.shop);
    setIsMobileMenuOpen(false);
  };

  const handleLogout = async () => {
    await logout();
    router.push(ROUTES.home);
    router.refresh();
  };

  return (
    <header className={`header-wrapper ${isScrolled ? "scrolled" : ""}`}>
      {/* Top Header Bar */}
      <div className="top-bar">
        <div className="top-bar-left">
          <a href="tel:0332477689" className="top-bar-item">
            <span className="top-bar-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
            </span>
            0332 477 689
          </a>
          <a href="mailto:petworldshopvv@gmail.com" className="top-bar-item">
            <span className="top-bar-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
            </span>
            petworldshopvv@gmail.com
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
            137 Bình Long, Bình Trị Đông, Hồ Chí Minh
          </div>

          {/* Menu pháp lý mở bằng hover trên desktop và focus/click khi dùng bàn phím hoặc mobile. */}
          <div className="top-legal-menu">
            <button
              type="button"
              className="top-legal-trigger"
              aria-haspopup="menu"
              aria-label="Mở menu điều khoản và chính sách"
            >
              Điều khoản &amp; chính sách
              <span className="top-legal-chevron" aria-hidden="true" />
            </button>
            <div className="top-legal-dropdown" role="menu">
              <Link href={ROUTES.terms} className="top-legal-link" role="menuitem">
                Điều khoản
              </Link>
              <Link href={ROUTES.privacy} className="top-legal-link" role="menuitem">
                Chính sách
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Floating Navigation Bar */}
      <div className="navbar-container">
        <nav className="navbar">
          {/* Hamburger Menu button */}
          <button
            type="button"
            className="hamburger-btn"
            onClick={() => setIsMobileMenuOpen(true)}
            aria-label="Mở menu điều hướng"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>

          <Link href={ROUTES.home} className="logo-link" id="logo">
            <img src={resolveBackendImage("logo/Special_Offer_1-removebg-preview.png")} alt="PetWorld Logo" className="logo-img" />
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

          <div className="search-container-wrapper" ref={searchContainerRef} style={{ position: "relative" }}>
            <form className="search-container" onSubmit={handleSearch}>
              <input
                type="text"
                className="search-input"
                placeholder="Sen muốn tìm gì?..."
                aria-label="Tìm kiếm"
                value={keyword}
                onChange={(event) => {
                  setKeyword(event.target.value);
                  setIsSmartSearchOpen(true);
                }}
                onFocus={() => {
                  if (keyword.trim() || smartSearchData) {
                    setIsSmartSearchOpen(true);
                  }
                }}
              />
              <button type="submit" className="search-button" aria-label="Tìm kiếm nút">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="11" cy="11" r="8" />
                  <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
              </button>
            </form>

            <SmartSearchFlyout
              isOpen={isSmartSearchOpen && (keyword.trim().length > 0 || isSmartSearchLoading)}
              keyword={keyword}
              loading={isSmartSearchLoading}
              data={smartSearchData}
              onClose={() => setIsSmartSearchOpen(false)}
              onSelectKeyword={(kw) => {
                setKeyword(kw);
                setIsSmartSearchOpen(true);
              }}
            />
          </div>

          <div className="nav-actions">
            {/* Mobile Search Toggle */}
            <button
              type="button"
              className="action-item mobile-search-toggle"
              onClick={() => setIsMobileSearchOpen((prev) => !prev)}
              aria-label="Tìm kiếm di động"
            >
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
            </button>

            <div className="notification-menu">
              <div className="action-item desktop-only" id="notifications-btn" aria-label="Thông báo" style={{ cursor: "default" }}>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                  <path d="M10 21h4" />
                </svg>
                {unreadCount > 0 && <span className="noti-badge">{unreadCount}</span>}
              </div>

              {user && (
                <div className="notification-dropdown">
                  <div className="noti-header">
                    <h3>Thông báo mới nhận</h3>
                    {unreadCount > 0 && (
                      <button onClick={handleMarkAllAsRead} className="noti-mark-all-btn">
                        Đọc tất cả
                      </button>
                    )}
                  </div>
                  <div className="noti-list">
                    {notifications.length === 0 ? (
                      <div className="noti-empty">
                        <div className="noti-empty-icon">🔔</div>
                        <p>Bạn không có thông báo mới nào</p>
                      </div>
                    ) : (
                      notifications.map((noti) => (
                        <div
                          key={noti.id}
                          className={`noti-item ${!noti.read_at ? "unread" : ""}`}
                          onClick={() => handleMarkAsRead(noti.id, noti.action_url)}
                        >
                          <div className={`noti-icon-box ${noti.icon || "info"}`}>
                            {/* {noti.icon === "gift" && "🎁"}
                            {noti.icon === "truck" && "🚚"}
                            {noti.icon === "check-circle" && "✅"}
                            {noti.icon === "x-circle" && "❌"}
                            {noti.icon === "clock" && "🕒"}
                            {noti.icon === "smile" && "😊"} */}
                            {(!noti.icon || noti.icon === "info") && "ℹ️"} 
                          </div>
                          <div className="noti-content">
                            <div className="noti-title">{noti.title}</div>
                            <div className="noti-message">{noti.message}</div>
                            <div className="noti-time">{timeAgo(noti.created_at)}</div>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}
            </div>

            <div className="profile-menu">
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

              {user && (
                <div className="profile-dropdown">
                  <div className="profile-dropdown-user">
                    <strong>{user.name}</strong>
                    <span>{user.email}</span>
                  </div>
                  <Link href={ROUTES.account} className="profile-dropdown-item">
                    Thông tin cá nhân
                  </Link> 
                  <Link href={ROUTES.wishlist} className="profile-dropdown-item">
                    Sản phẩm yêu thích
                  </Link>
                  <Link href={ROUTES.orders} className="profile-dropdown-item">
                    Đơn hàng
                  </Link>
                 
                  <button type="button" className="profile-dropdown-item logout" onClick={handleLogout}>
                    Đăng xuất
                  </button>
                </div>
              )}
            </div>

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

        {/* Dropdown Mobile Search Bar */}
        {isMobileSearchOpen && (
          <div style={{ position: "relative" }}>
            <form className="mobile-search-row" onSubmit={(e) => { handleSearch(e); setIsMobileSearchOpen(false); }}>
              <input
                type="text"
                className="mobile-search-row-input"
                placeholder="Sen muốn tìm gì?...."
                value={keyword}
                onChange={(event) => {
                  setKeyword(event.target.value);
                  setIsSmartSearchOpen(true);
                }}
                onFocus={() => {
                  if (keyword.trim() || smartSearchData) {
                    setIsSmartSearchOpen(true);
                  }
                }}
                autoFocus
              />
              <button type="submit" className="mobile-search-row-button">
                Tìm kiếm
              </button>
            </form>
            <SmartSearchFlyout
              isOpen={isSmartSearchOpen && (keyword.trim().length > 0 || isSmartSearchLoading)}
              keyword={keyword}
              loading={isSmartSearchLoading}
              data={smartSearchData}
              onClose={() => {
                setIsSmartSearchOpen(false);
                setIsMobileSearchOpen(false);
              }}
              onSelectKeyword={(kw) => {
                setKeyword(kw);
                setIsSmartSearchOpen(true);
              }}
            />
          </div>
        )}
      </div>

      {/* Mobile Menu Drawer Overlay & Content */}
      <div className={`mobile-drawer ${isMobileMenuOpen ? "open" : ""}`}>
        <div className="mobile-drawer-overlay" onClick={() => setIsMobileMenuOpen(false)} />
        <div className="mobile-drawer-content">
          <div className="mobile-drawer-header">
            <Link href={ROUTES.home} onClick={() => setIsMobileMenuOpen(false)}>
              <img src={resolveBackendImage("logo/Special_Offer_1-removebg-preview.png")} alt="PetWorld Logo" className="mobile-drawer-logo" />
            </Link>
            <button
              type="button"
              className="mobile-drawer-close"
              onClick={() => setIsMobileMenuOpen(false)}
              aria-label="Đóng menu"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <div className="mobile-drawer-body">
            {/* Search within Drawer */}
            <form className="mobile-drawer-search" onSubmit={(e) => { handleSearch(e); setIsMobileMenuOpen(false); }}>
              <input
                type="text"
                className="mobile-drawer-search-input"
                placeholder="Sen muốn tìm gì?...."
                value={keyword}
                onChange={(event) => setKeyword(event.target.value)}
              />
              <button type="submit" className="mobile-drawer-search-btn" aria-label="Tìm kiếm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="11" cy="11" r="8" />
                  <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
              </button>
            </form>

            {/* Navigation links */}
            <ul className="mobile-drawer-links">
              {MAIN_NAV.map((item) => {
                const isActive =
                  item.href === "/"
                    ? pathname === "/"
                    : pathname.startsWith(item.href);
                return (
                  <li key={item.href}>
                    <Link
                      href={item.href}
                      className={`mobile-drawer-link ${isActive ? "active" : ""}`}
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      {item.label}
                    </Link>
                  </li>
                );
              })}
            </ul>
          </div>

          {/* Account Section in Drawer Footer */}
          <div className="mobile-drawer-footer">
            {user ? (
              <div className="mobile-drawer-user">
                <div className="user-info">
                  <strong className="user-name">{user.name}</strong>
                  <span className="user-email">{user.email}</span>
                </div>
                <Link href={ROUTES.account} className="drawer-btn" onClick={() => setIsMobileMenuOpen(false)}>
                  Thông tin cá nhân
                </Link>
                <Link href={ROUTES.wishlist} className="drawer-btn" onClick={() => setIsMobileMenuOpen(false)}>
                  Sản phẩm yêu thích
                </Link>
                <Link href={ROUTES.orders} className="drawer-btn" onClick={() => setIsMobileMenuOpen(false)}>
                  Đơn hàng
                </Link>
                <button type="button" className="drawer-btn logout" onClick={() => { handleLogout(); setIsMobileMenuOpen(false); }}>
                  Đăng xuất
                </button>
              </div>
            ) : (
              <div className="mobile-drawer-auth">
                <Link href={ROUTES.login} className="drawer-auth-btn login" onClick={() => setIsMobileMenuOpen(false)}>
                  Đăng nhập
                </Link>
                <Link href={ROUTES.register} className="drawer-auth-btn register" onClick={() => setIsMobileMenuOpen(false)}>
                  Đăng ký
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>

    </header>
  );
}
