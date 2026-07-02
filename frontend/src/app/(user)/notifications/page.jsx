import Link from "next/link";

export const metadata = { title: "Thông báo - PetWorld" };

export default function NotificationsPage() {
  return <main className="main-content notifications-page"><section className="notifications-coming"><div className="notifications-bell" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg></div><span>Tính năng mới</span><h1>Trung tâm thông báo<br/>đang được xây dựng</h1><p>PetWorld đang hoàn thiện nơi cập nhật đơn hàng, ưu đãi và những tin tức dành riêng cho bạn.</p><div><Link href="/account/orders">Xem đơn hàng</Link><Link href="/" className="outline">Về trang chủ</Link></div></section></main>;
}
