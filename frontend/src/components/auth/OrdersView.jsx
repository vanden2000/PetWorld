"use client";

import Link from "next/link";
import { useCallback, useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { useRouter } from "next/navigation";
import { getOrders, getServerUserSnapshot, getUserSnapshot, logout, onAuthChange, parseUser } from "@/lib/auth";

const icons = {
  user: <><path d="M20 21a8 8 0 0 0-16 0" /><circle cx="12" cy="7" r="4" /></>,
  history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8" /><path d="M3 3v5h5M12 7v5l3 2" /></>,
  truck: <><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z" /><circle cx="7" cy="18" r="2" /><circle cx="17" cy="18" r="2" /></>,
  heart: <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z" />,
  shield: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />,
  logout: <><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" /></>,
  search: <><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" /></>,
  box: <><path d="m3 7 9-4 9 4-9 4zM3 7v10l9 4 9-4V7M12 11v10" /></>,
};
const Icon = ({ name }) => <svg className="profile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{icons[name]}</svg>;
const tabs = [{ value: "", label: "Tất cả" }, { value: "completed", label: "Đã giao" }, { value: "processing", label: "Đang xử lý" }, { value: "cancelled", label: "Đã hủy" }];
const statusLabels = { pending: "Chờ xác nhận", confirmed: "Đã xác nhận", shipping: "Đang giao", completed: "Đã giao", cancelled: "Đã hủy" };
const formatMoney = (value) => `${new Intl.NumberFormat("vi-VN").format(value)}đ`;
const formatDate = (value) => new Intl.DateTimeFormat("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" }).format(new Date(value));

export default function OrdersView() {
  const router = useRouter();
  const raw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(raw), [raw]);
  const [orders, setOrders] = useState([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });
  const [status, setStatus] = useState("");
  const [searchInput, setSearchInput] = useState("");
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const loadOrders = useCallback(async () => {
    if (!user) return;
    setLoading(true); setError("");
    const result = await getOrders({ status, search, page });
    if (result.ok) { setOrders(result.data.orders || []); setPagination(result.data.pagination || {}); }
    else setError(result.message || "Không thể tải danh sách đơn hàng.");
    setLoading(false);
  }, [page, search, status, user]);

  useEffect(() => {
    Promise.resolve().then(loadOrders);
  }, [loadOrders]);

  if (!user) return <section className="profile-guest"><Icon name="box" /><h1>Đơn hàng của bạn</h1><p>Vui lòng đăng nhập để xem và theo dõi lịch sử đơn hàng.</p><div><Link href="/login?redirect=/account/orders" className="profile-primary-btn">Đăng nhập</Link></div></section>;

  return <div className="profile-shell">
    <aside className="profile-sidebar order-sidebar">
      <div className="profile-identity"><div className="profile-avatar" style={user.avatar ? { backgroundImage: `url(${user.avatar})` } : undefined}>{user.avatar ? null : user.name.charAt(0).toUpperCase()}</div><strong>{user.name}</strong><span>Thành viên PetWorld</span></div>
      <nav aria-label="Quản lý tài khoản"><Link href="/account"><Icon name="user" />Hồ sơ</Link><Link href="/account/orders" className="active"><Icon name="history" />Lịch sử đơn hàng</Link><Link href="/account/wishlist"><Icon name="heart" />Sản phẩm yêu thích</Link></nav>
      <button className="profile-logout" onClick={async () => { await logout(); router.push("/"); router.refresh(); }}><Icon name="logout" />Đăng xuất</button>
    </aside>

    <div className="profile-content orders-content">
      <header className="profile-heading"><span>Tài khoản của tôi</span><h1>Lịch sử đơn hàng</h1><p>Quản lý và theo dõi tất cả đơn hàng của bạn tại đây.</p></header>
      <section className="orders-toolbar">
        <div className="orders-tabs" role="tablist" aria-label="Lọc theo trạng thái">{tabs.map((tab) => <button key={tab.value || "all"} className={status === tab.value ? "active" : ""} onClick={() => { setStatus(tab.value); setPage(1); }} role="tab" aria-selected={status === tab.value}>{tab.label}</button>)}</div>
        <form className="orders-search" onSubmit={(event) => { event.preventDefault(); setSearch(searchInput.trim()); setPage(1); }}><Icon name="search" /><input value={searchInput} onChange={(event) => setSearchInput(event.target.value)} placeholder="Tìm theo mã đơn hàng..." aria-label="Tìm đơn hàng" /><button className="sr-only">Tìm kiếm</button></form>
      </section>

      <section className="orders-table-wrap" aria-live="polite">
        <div className="orders-table-head"><span>Mã đơn hàng</span><span>Ngày đặt</span><span>Trạng thái</span><span>Tổng tiền</span><span>Thao tác</span></div>
        {loading ? <div className="orders-loading">{[1, 2, 3].map((item) => <span key={item} />)}</div> : error ? <div className="orders-message error"><strong>Không tải được đơn hàng</strong><span>{error}</span><button onClick={loadOrders}>Thử lại</button></div> : orders.length === 0 ? <div className="orders-message"><Icon name="box" /><strong>Chưa tìm thấy đơn hàng</strong><span>Thử thay đổi bộ lọc hoặc bắt đầu mua sắm tại PetWorld.</span><Link href="/shop">Khám phá sản phẩm</Link></div> : <div className="orders-list">{orders.map((order) => <article className="order-row" key={order.id}><div data-label="Mã đơn hàng"><strong>#{order.code}</strong><small>{order.items_count} sản phẩm</small></div><div data-label="Ngày đặt">{formatDate(order.created_at)}</div><div data-label="Trạng thái"><span className={`order-status ${order.status}`}><i />{statusLabels[order.status] || order.status}</span></div><div data-label="Tổng tiền"><strong>{formatMoney(order.total_amount)}</strong></div><div data-label="Thao tác"><Link href={`/account/orders/${order.id}`} className={order.status === "shipping" ? "track" : ""}>{order.status === "shipping" ? "Theo dõi" : "Xem chi tiết"}</Link></div></article>)}</div>}
      </section>

      {!loading && !error && pagination.total > 0 && <footer className="orders-pagination"><p>Hiển thị {pagination.from} - {pagination.to} trong {pagination.total} đơn hàng</p><div><button disabled={pagination.current_page <= 1} onClick={() => setPage((value) => value - 1)} aria-label="Trang trước">‹</button>{Array.from({ length: pagination.last_page }, (_, index) => index + 1).slice(Math.max(0, page - 2), Math.max(3, page + 1)).map((number) => <button key={number} className={number === page ? "active" : ""} onClick={() => setPage(number)}>{number}</button>)}<button disabled={pagination.current_page >= pagination.last_page} onClick={() => setPage((value) => value + 1)} aria-label="Trang sau">›</button></div></footer>}
    </div>
  </div>;
}
