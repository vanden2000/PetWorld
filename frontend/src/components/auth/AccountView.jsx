"use client";

import Link from "next/link";
import { useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { useRouter } from "next/navigation";
import { deleteAddress, getAddresses, getServerUserSnapshot, getUserSnapshot, logout, onAuthChange, parseUser, saveAddress, updateAvatar, updatePassword, updateProfile } from "@/lib/auth";
import { toastError, toastSuccess } from "@/lib/toast";
import AddressLocationFields from "@/components/auth/AddressLocationFields";

const iconPaths = {
  user: <><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></>,
  history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></>,
  truck: <><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></>,
  heart: <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z"/>,
  shield: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>,
  logout: <><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></>,
  badge: <><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M9 5V3h6v2M8 11h8M8 15h5"/></>,
  pin: <><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/></>,
  home: <><path d="M3 11l9-8 9 8"/><path d="M5 10v11h14V10M9 21v-7h6v7"/></>,
  camera: <><path d="M4 7h3l2-3h6l2 3h3a2 2 0 0 1 2 2v10H2V9a2 2 0 0 1 2-2z"/><circle cx="12" cy="13" r="4"/></>,
};
const Icon = ({ name }) => <svg className="profile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{iconPaths[name]}</svg>;
const emptyAddress = { recipient_name: "", recipient_phone: "", address_line: "", ward: "", district: "", province: "", is_default: false };

export default function AccountView() {
  const router = useRouter();
  const raw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(raw), [raw]);
  const [profile, setProfile] = useState({ name: "", email: "", phone: "", date_of_birth: "" });
  const [addresses, setAddresses] = useState([]);
  const [busy, setBusy] = useState(false);
  const [avatarBusy, setAvatarBusy] = useState(false);
  const [addressForm, setAddressForm] = useState(null);
  const [passwordOpen, setPasswordOpen] = useState(false);
  const [passwords, setPasswords] = useState({ current_password: "", password: "", password_confirmation: "" });

  useEffect(() => {
    if (!user) return;
    let active = true;
    Promise.resolve().then(() => {
      if (active) setProfile({ name: user.name || "", email: user.email || "", phone: user.phone || "", date_of_birth: user.date_of_birth || "" });
    });
    getAddresses().then((result) => active && result.ok && setAddresses(result.data.addresses || []));
    return () => { active = false; };
  }, [user]);

  if (!user) return <section className="profile-guest"><Icon name="user" /><h1>Hồ sơ của bạn</h1><p>Đăng nhập để quản lý thông tin cá nhân, địa chỉ giao hàng và bảo mật tài khoản.</p><div><Link href="/login" className="profile-primary-btn">Đăng nhập</Link><Link href="/register" className="profile-secondary-btn">Tạo tài khoản</Link></div></section>;

  const showError = (result) => toastError(Object.values(result.errors || {})[0]?.[0] || result.message);
  const submitProfile = async (event) => { event.preventDefault(); setBusy(true); const result = await updateProfile(profile); setBusy(false); result.ok ? toastSuccess("Đã lưu thông tin cá nhân.") : showError(result); };
  const submitAddress = async (event) => { event.preventDefault(); if (!addressForm.province || !addressForm.ward || !addressForm.district) return toastError("Vui lòng chọn đầy đủ địa chỉ hành chính."); setBusy(true); const result = await saveAddress(addressForm, addressForm.id); setBusy(false); if (!result.ok) return showError(result); const refreshed = await getAddresses(); if (refreshed.ok) setAddresses(refreshed.data.addresses || []); setAddressForm(null); toastSuccess(result.data.message); };
  const removeAddress = async (id) => { if (!window.confirm("Bạn muốn xóa địa chỉ này?")) return; const result = await deleteAddress(id); if (!result.ok) return showError(result); const refreshed = await getAddresses(); if (refreshed.ok) setAddresses(refreshed.data.addresses || []); toastSuccess(result.data.message); };
  const submitPassword = async (event) => { event.preventDefault(); setBusy(true); const result = await updatePassword(passwords); setBusy(false); if (!result.ok) return showError(result); setPasswords({ current_password: "", password: "", password_confirmation: "" }); setPasswordOpen(false); toastSuccess(result.data.message); };
  const uploadAvatar = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = "";
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return toastError("Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.");
    if (file.size > 2 * 1024 * 1024) return toastError("Ảnh đại diện không được lớn hơn 2 MB.");
    setAvatarBusy(true);
    const result = await updateAvatar(file);
    setAvatarBusy(false);
    result.ok ? toastSuccess(result.data.message) : showError(result);
  };

  return <div className="profile-shell">
    <aside className="profile-sidebar">
      <div className="profile-identity"><label className={`profile-avatar-wrap ${avatarBusy ? "loading" : ""}`} title="Thay ảnh đại diện"><span className="profile-avatar" style={user.avatar ? { backgroundImage: `url(${user.avatar})` } : undefined}>{user.avatar ? null : user.name.charAt(0).toUpperCase()}</span><span className="profile-avatar-edit"><Icon name="camera" /></span><input type="file" accept="image/jpeg,image/png,image/webp" onChange={uploadAvatar} disabled={avatarBusy} /><span className="sr-only">Chọn ảnh đại diện</span></label><strong>{user.name}</strong><span>{avatarBusy ? "Đang tải ảnh..." : "Thành viên PetWorld"}</span></div>
      <nav aria-label="Quản lý tài khoản">
        <a href="#personal" className="active"><Icon name="user" />Hồ sơ</a>
        <Link href="/account/orders"><Icon name="history" />Lịch sử đơn hàng</Link>
        <Link href="/account/wishlist"><Icon name="heart" />Sản phẩm yêu thích</Link>
      </nav>
      <button className="profile-logout" onClick={async () => { await logout(); router.push("/"); router.refresh(); }}><Icon name="logout" />Đăng xuất</button>
    </aside>

    <div className="profile-content">
      <header className="profile-heading"><span>Tài khoản của tôi</span><h1>Hồ sơ cá nhân</h1><p>Quản lý thông tin cá nhân, địa chỉ giao hàng và bảo mật tài khoản.</p></header>
      <section className="profile-panel" id="personal"><div className="profile-panel-title"><div><Icon name="badge" /><h2>Thông tin cá nhân</h2></div><span className="profile-status">Đã xác thực</span></div>
        <form className="profile-form-grid" onSubmit={submitProfile}>
          <label>Họ và tên<input value={profile.name} onChange={(e) => setProfile({ ...profile, name: e.target.value })} required /></label>
          <label>Địa chỉ email<input type="email" value={profile.email} onChange={(e) => setProfile({ ...profile, email: e.target.value })} required /></label>
          <label>Số điện thoại<input type="tel" value={profile.phone} onChange={(e) => setProfile({ ...profile, phone: e.target.value.replace(/\s/g, "") })} pattern="(0|\+84)(3|5|7|8|9)[0-9]{8}" title="Số điện thoại Việt Nam hợp lệ, ví dụ 0912345678 hoặc +84912345678" placeholder="0912345678" /></label>
          <label>Ngày sinh<input type="date" value={profile.date_of_birth} max={new Date().toISOString().slice(0, 10)} onChange={(e) => setProfile({ ...profile, date_of_birth: e.target.value })} /></label>
          <div className="profile-form-actions"><button className="profile-primary-btn" disabled={busy}>{busy ? "Đang lưu..." : "Lưu thay đổi"}</button></div>
        </form>
      </section>

      <section className="profile-panel" id="addresses"><div className="profile-panel-title"><div><Icon name="pin" /><h2>Địa chỉ giao hàng</h2></div><button className="profile-text-btn" onClick={() => setAddressForm({ ...emptyAddress, recipient_name: user.name, recipient_phone: user.phone || "" })}>+ Thêm địa chỉ</button></div>
        <div className="profile-address-list">{addresses.length === 0 ? <div className="profile-empty"><Icon name="pin" /><strong>Chưa có địa chỉ giao hàng</strong><span>Thêm địa chỉ để thanh toán nhanh hơn.</span></div> : addresses.map((address) => <article className={address.is_default ? "default" : ""} key={address.id}><Icon name={address.is_default ? "home" : "pin"} /><div><strong>{address.recipient_name} {address.is_default && <em>Mặc định</em>}</strong><p>{address.recipient_phone}</p><span>{address.address_line}, {address.ward}, {address.district}, {address.province}</span></div><div className="profile-address-actions"><button onClick={() => setAddressForm({ ...address })}>Sửa</button><button className="danger" onClick={() => removeAddress(address.id)}>Xóa</button></div></article>)}</div>
      </section>

      <section className="profile-panel" id="security"><div className="profile-panel-title"><div><Icon name="shield" /><h2>Bảo mật tài khoản</h2></div></div><div className="profile-security-row"><div><strong>Đổi mật khẩu</strong><span>Nên sử dụng mật khẩu mạnh và không dùng lại ở nơi khác.</span></div><button className="profile-secondary-btn" onClick={() => setPasswordOpen(!passwordOpen)}>{passwordOpen ? "Đóng" : "Cập nhật"}</button></div>
        {passwordOpen && <form className="profile-password-form" onSubmit={submitPassword}><label>Mật khẩu hiện tại<input type="password" value={passwords.current_password} onChange={(e) => setPasswords({ ...passwords, current_password: e.target.value })} required /></label><label>Mật khẩu mới<input type="password" minLength="6" value={passwords.password} onChange={(e) => setPasswords({ ...passwords, password: e.target.value })} required /></label><label>Xác nhận mật khẩu<input type="password" minLength="6" value={passwords.password_confirmation} onChange={(e) => setPasswords({ ...passwords, password_confirmation: e.target.value })} required /></label><button className="profile-primary-btn" disabled={busy}>Đổi mật khẩu</button></form>}
      </section>
    </div>

    {addressForm && <div className="profile-modal-backdrop" onMouseDown={(e) => e.target === e.currentTarget && setAddressForm(null)}><div className="profile-modal" role="dialog" aria-modal="true" aria-labelledby="address-title"><div className="profile-modal-head"><h2 id="address-title">{addressForm.id ? "Chỉnh sửa địa chỉ" : "Thêm địa chỉ mới"}</h2><button onClick={() => setAddressForm(null)} aria-label="Đóng">×</button></div><form className="profile-address-form" onSubmit={submitAddress}><label>Người nhận<input value={addressForm.recipient_name} onChange={(e) => setAddressForm({ ...addressForm, recipient_name: e.target.value })} required /></label><label>Số điện thoại<input type="tel" value={addressForm.recipient_phone} onChange={(e) => setAddressForm({ ...addressForm, recipient_phone: e.target.value.replace(/\s/g, "") })} pattern="(0|\+84)(3|5|7|8|9)[0-9]{8}" title="Số điện thoại Việt Nam hợp lệ" required /></label><label className="wide">Số nhà, tên đường<input value={addressForm.address_line} onChange={(e) => setAddressForm({ ...addressForm, address_line: e.target.value })} placeholder="Ví dụ: 123 Nguyễn Trãi" required /></label><AddressLocationFields value={addressForm} onChange={setAddressForm} /><label className="profile-checkbox wide"><input type="checkbox" checked={Boolean(addressForm.is_default)} onChange={(e) => setAddressForm({ ...addressForm, is_default: e.target.checked })} />Đặt làm địa chỉ mặc định</label><div className="profile-modal-actions wide"><button type="button" className="profile-secondary-btn" onClick={() => setAddressForm(null)}>Hủy</button><button className="profile-primary-btn" disabled={busy}>{busy ? "Đang lưu..." : "Lưu địa chỉ"}</button></div></form></div></div>}
  </div>;
}
