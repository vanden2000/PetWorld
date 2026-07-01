"use client";

import Link from "next/link";
import { useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { useRouter } from "next/navigation";
import { getOrder, getServerUserSnapshot, getUserSnapshot, logout, onAuthChange, parseUser } from "@/lib/auth";
import { resolveProductImage } from "@/lib/format";

const paths = {
  user: <><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></>, history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></>, truck: <><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></>, heart: <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z"/>, shield: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>, logout: <><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></>, box: <><path d="m3 7 9-4 9 4-9 4zM3 7v10l9 4 9-4V7M12 11v10"/></>, pin: <><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/></>, card: <><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></>, check: <path d="m5 12 4 4L19 6"/>, receipt: <><path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/><path d="M9 7h6M9 11h6M9 15h4"/></>, help: <><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.4 2.4 0 1 1 3.2 2.3c-.7.3-1 1-1 1.7M12 17h.01"/></>,
};
const Icon = ({ name }) => <svg className="profile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name]}</svg>;
const money = (value) => `${new Intl.NumberFormat("vi-VN").format(value)}đ`;
const dateTime = (value) => new Intl.DateTimeFormat("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" }).format(new Date(value));
const steps = [{ key: "pending", label: "Chờ xác nhận", icon: "check" }, { key: "confirmed", label: "Đang lấy hàng", icon: "box" }, { key: "shipping", label: "Đang giao", icon: "truck" }, { key: "completed", label: "Đã giao", icon: "box" }];
const stage = { pending: 0, confirmed: 1, shipping: 2, completed: 3 };

export default function OrderTrackingView({ orderId }) {
  const router = useRouter();
  const raw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(raw), [raw]);
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!user) return;
    let active = true;
    getOrder(orderId).then((result) => { if (!active) return; if (result.ok) setOrder(result.data.order); else setError(result.message || "Không tìm thấy đơn hàng."); setLoading(false); });
    return () => { active = false; };
  }, [orderId, user]);

  if (!user) return <section className="profile-guest"><Icon name="box"/><h1>Theo dõi đơn hàng</h1><p>Vui lòng đăng nhập để xem trạng thái đơn hàng.</p><div><Link className="profile-primary-btn" href={`/login?redirect=/account/orders/${orderId}`}>Đăng nhập</Link></div></section>;
  if (loading) return <div className="tracking-loading"><span/><span/><span/></div>;
  if (error || !order) return <section className="profile-guest"><Icon name="box"/><h1>Không tìm thấy đơn hàng</h1><p>{error}</p><div><Link className="profile-primary-btn" href="/account/orders">Quay lại đơn hàng</Link></div></section>;

  const currentStage = stage[order.status] ?? -1;
  return <div className="profile-shell">
    <aside className="profile-sidebar tracking-sidebar"><div className="profile-identity"><div className="profile-avatar" style={user.avatar ? { backgroundImage: `url(${user.avatar})` } : undefined}>{user.avatar ? null : user.name.charAt(0).toUpperCase()}</div><strong>{user.name}</strong><span>Thành viên PetWorld</span></div><nav><Link href="/account"><Icon name="user"/>Hồ sơ</Link><Link href="/account/orders"><Icon name="history"/>Lịch sử đơn hàng</Link><Link href="/account/wishlist"><Icon name="heart"/>Sản phẩm yêu thích</Link></nav><button className="profile-logout" onClick={async () => { await logout(); router.push("/"); router.refresh(); }}><Icon name="logout"/>Đăng xuất</button></aside>
    <div className="profile-content tracking-content">
      <div className="tracking-breadcrumb"><Link href="/account/orders">Đơn hàng</Link><span>›</span><strong>#{order.code}</strong></div>
      <header className="tracking-header"><div><h1>Theo dõi đơn hàng</h1><p>Cập nhật hành trình và thông tin đơn hàng của bạn.</p></div><div><button type="button" onClick={() => window.print()}><Icon name="receipt"/>Xuất hóa đơn</button><Link href="/contact"><Icon name="help"/>Hỗ trợ</Link></div></header>

      {order.status === "cancelled" ? <section className="tracking-cancelled"><Icon name="box"/><div><strong>Đơn hàng đã bị hủy</strong><span>Liên hệ PetWorld nếu bạn cần thêm thông tin về đơn hàng này.</span></div></section> : <section className="tracking-timeline" style={{ "--tracking-progress": `${(currentStage / 3) * 100}%` }}><div className="tracking-line"><i/></div>{steps.map((step, index) => <div className={`tracking-step ${index < currentStage ? "done" : ""} ${index === currentStage ? "current" : ""}`} key={step.key}><span><Icon name={step.icon}/></span><strong>{step.label}</strong><small>{index <= currentStage ? (step.key === "shipping" && order.status === "shipping" ? "Đang trên đường" : dateTime(step.key === "completed" ? order.updated_at : order.created_at)) : "Đang chờ"}</small></div>)}</section>}

      <div className="tracking-info-grid"><section className="tracking-card tracking-receiver"><h2><Icon name="pin"/>Thông tin nhận hàng</h2><dl><div><dt>Người nhận</dt><dd>{order.recipient.name} · {order.recipient.phone}</dd></div><div><dt>Địa chỉ</dt><dd>{order.recipient.address}</dd></div></dl><div className="tracking-shipping"><div><span>Đơn vị vận chuyển</span><strong>{order.shipping.method}</strong></div><div><span>Mã vận đơn</span><strong>{order.shipping.tracking_code}</strong></div></div>{order.note && <p>Ghi chú: {order.note}</p>}</section><section className="tracking-card tracking-payment"><h2><Icon name="card"/>Thanh toán</h2><dl><div><dt>Tạm tính</dt><dd>{money(order.payment.subtotal)}</dd></div><div><dt>Phí vận chuyển</dt><dd>{order.shipping.fee ? money(order.shipping.fee) : "Miễn phí"}</dd></div><div className="discount"><dt>Giảm giá</dt><dd>-{money(order.payment.discount)}</dd></div><div className="total"><dt>Tổng cộng</dt><dd>{money(order.payment.total)}</dd></div></dl><p>{order.payment_status === "paid" ? `Đã thanh toán qua ${order.payment.method}` : `Thanh toán: ${order.payment.method}`}</p></section></div>

      <section className="tracking-products"><header><h2>Chi tiết sản phẩm ({order.items.length})</h2></header>{order.items.map((item) => <article key={item.id}><div className="tracking-product-image" style={{ backgroundImage: `url(${resolveProductImage(item.image)})` }}/><div><strong>{item.name}</strong>{item.variant && <span>Phân loại: {item.variant}</span>}<small>Số lượng: {item.quantity}</small>{item.slug && <Link href={`/shop/${item.slug}`}>Xem sản phẩm</Link>}</div><b>{money(item.price * item.quantity)}</b></article>)}</section>

      {order.status === "completed" && <section className="tracking-delivered"><span><Icon name="check"/></span><div><strong>Đơn hàng đã giao thành công</strong><p>Đơn hàng #{order.code} đã được giao đến bạn vào {dateTime(order.updated_at)}. Cảm ơn bạn đã mua sắm tại PetWorld.</p></div><Link href="/shop">Tiếp tục mua sắm</Link></section>}
    </div>
  </div>;
}
