"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { formatPrice, resolveProductImage } from "@/lib/format";
import { ROUTES } from "@/lib/routes";
import { toastSuccess, toastError, toastInfo } from "@/lib/toast";
import LoginRequiredDialog from "@/components/ui/LoginRequiredDialog";
import { clearBuyNow } from "@/lib/buyNow";
import { getAvailableVouchers } from "@/lib/checkout";
import {
  getUserSnapshot,
  getServerUserSnapshot,
  parseUser,
  onAuthChange,
} from "@/lib/auth";
import {
  getCartSnapshot,
  getServerCartSnapshot,
  parseCart,
  updateQuantity,
  removeFromCart,
  onCartChange,
} from "@/lib/cart";

const SHIPPING_FEE = 30000; // Thay đổi từ 7000000 thành 30000 cho khớp phí vận chuyển thực tế

export default function CartView() {
  const router = useRouter();
  const userRaw = useSyncExternalStore(onAuthChange, getUserSnapshot, getServerUserSnapshot);
  const user = useMemo(() => parseUser(userRaw), [userRaw]);

  // Đăng ký giỏ hàng (localStorage) qua external store để tránh hydration mismatch.
  const raw = useSyncExternalStore(onCartChange, getCartSnapshot, getServerCartSnapshot);
  const items = useMemo(() => parseCart(raw), [raw]);

  const [appliedVoucher, setAppliedVoucher] = useState(null);
  const [vouchers, setVouchers] = useState([]);
  const [showVoucherModal, setShowVoucherModal] = useState(false);
  const [loadingVouchers, setLoadingVouchers] = useState(false);
  const [showLoginDialog, setShowLoginDialog] = useState(false);

  const subtotal = items.reduce((sum, line) => sum + line.price * line.quantity, 0);
  const shipping = items.length ? SHIPPING_FEE : 0;
  const discount = appliedVoucher ? Math.min(parseFloat(appliedVoucher.discount_value), subtotal) : 0;
  const total = Math.max(0, subtotal + shipping - discount);

  // Đọc và kiểm tra tính hợp lệ của voucher từ localStorage
  useEffect(() => {
    if (typeof window !== "undefined") {
      try {
        const stored = localStorage.getItem("petworld_cart_applied_voucher");
        if (stored) {
          const parsed = JSON.parse(stored);
          getAvailableVouchers(subtotal).then((list) => {
            const found = list.find((v) => v.id === parsed.id && v.can_apply);
            if (found) {
              setAppliedVoucher(parsed);
            } else {
              localStorage.removeItem("petworld_cart_applied_voucher");
              setAppliedVoucher(null);
              toastError("Mã giảm giá đã áp dụng trước đó không còn hiệu lực hoặc đã hết lượt sử dụng.");
            }
          });
        }
      } catch (e) {
        console.error("[CartView] Lỗi đọc voucher:", e);
      }
    }
  }, [subtotal]);

  // Xóa voucher nếu user đăng xuất
  useEffect(() => {
    if (!user) {
      setTimeout(() => {
        setAppliedVoucher(null);
      }, 0);
      if (typeof window !== "undefined") {
        localStorage.removeItem("petworld_cart_applied_voucher");
      }
    }
  }, [user]);

  // Tự động gỡ voucher nếu subtotal không đủ điều kiện tối thiểu
  useEffect(() => {
    if (appliedVoucher && subtotal < parseFloat(appliedVoucher.min_order_value)) {
      const oldVoucher = appliedVoucher;
      setTimeout(() => {
        setAppliedVoucher(null);
      }, 0);
      if (typeof window !== "undefined") {
        localStorage.removeItem("petworld_cart_applied_voucher");
      }
      toastError(`Mã giảm giá ${oldVoucher.code} đã bị gỡ bỏ do tổng tiền đơn hàng chưa đạt tối thiểu ${formatPrice(oldVoucher.min_order_value)}.`);
    }
  }, [subtotal, appliedVoucher]);

  const handleRemove = (line) => {
    removeFromCart(line.key);
    toastSuccess(`Đã xoá "${line.name}" khỏi giỏ hàng`);
  };

  const handleOpenVoucherModal = async () => {
    setLoadingVouchers(true);
    setShowVoucherModal(true);
    try {
      const list = await getAvailableVouchers(subtotal);
      setVouchers(list);
    } catch (error) {
      console.error("[getAvailableVouchers] Lỗi khi tải voucher:", error);
      toastError("Không thể tải danh sách mã giảm giá.");
    } finally {
      setLoadingVouchers(false);
    }
  };

  const handleApplyVoucher = (voucher) => {
    if (!voucher.can_apply) return;
    setAppliedVoucher(voucher);
    if (typeof window !== "undefined") {
      localStorage.setItem("petworld_cart_applied_voucher", JSON.stringify(voucher));
    }
    setShowVoucherModal(false);
    toastSuccess(`Đã áp dụng mã giảm giá ${voucher.code} thành công!`);
  };

  const handleRemoveVoucher = () => {
    setAppliedVoucher(null);
    if (typeof window !== "undefined") {
      localStorage.removeItem("petworld_cart_applied_voucher");
    }
    toastSuccess("Đã gỡ mã giảm giá.");
  };

  const handleCheckout = (event) => {
    clearBuyNow();
    if (user) return;

    event.preventDefault();
    setShowLoginDialog(true);
  };

  const handleLogin = () => {
    setShowLoginDialog(false);
    router.push(`${ROUTES.login}?redirect=${encodeURIComponent(ROUTES.checkout)}`);
  };

  if (items.length === 0) {
    return (
      <div className="cart-empty">
        <p>Giỏ hàng của bạn đang trống.</p>
        <Link href={ROUTES.shop} className="cart-empty-btn">Tiếp tục mua sắm</Link>
      </div>
    );
  }

  return (
    <div className="cart-layout">
      <LoginRequiredDialog
        open={showLoginDialog}
        onClose={() => setShowLoginDialog(false)}
        onConfirm={handleLogin}
      />
      <div className="cart-items">
        {items.map((line) => (
          <div className="cart-item" key={line.key}>
            <Link href={`/shop/${line.slug}`} className="cart-item-img">
              <img src={resolveProductImage(line.image)} alt={line.name} />
            </Link>
            <div className="cart-item-info">
              <Link href={`/shop/${line.slug}`} className="cart-item-name">
                {line.name}
              </Link>
              {line.variantName && <span className="cart-item-variant">Biến thể: {line.variantName}</span>}
              <div className="cart-qty">
                <button type="button" onClick={() => updateQuantity(line.key, line.quantity - 1)} aria-label="Giảm">
                  −
                </button>
                <span>{line.quantity}</span>
                <button
                  type="button"
                  onClick={() => {
                    if (Number.isFinite(Number(line.stockQuantity)) && line.quantity >= line.stockQuantity) {
                      toastInfo(`Sản phẩm đã vượt quá số lượng trong kho.`);
                      return;
                    }
                    updateQuantity(line.key, line.quantity + 1);
                  }}
                  aria-label="Tăng"
                >
                  +
                </button>
              </div>
            </div>
            <div className="cart-item-right">
              <button type="button" className="cart-remove" onClick={() => handleRemove(line)} aria-label="Xoá">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="3 6 5 6 21 6" />
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  <line x1="10" y1="11" x2="10" y2="17" />
                  <line x1="14" y1="11" x2="14" y2="17" />
                </svg>
              </button>
              <div className="cart-item-prices">
                {line.oldPrice && (
                  <span className="cart-item-price-old">{formatPrice(line.oldPrice * line.quantity)}</span>
                )}
                <span className="cart-item-price">{formatPrice(line.price * line.quantity)}</span>
              </div>
            </div>
          </div>
        ))}

        <Link href={ROUTES.shop} className="cart-continue">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: "6px" }}>
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
          </svg>
          Tiếp tục mua sắm
        </Link>
      </div>

      <aside className="cart-summary">
        <h2 className="cart-summary-title">Tóm tắt đơn hàng</h2>
        <div className="cart-summary-row">
          <span>Tạm tính</span>
          <span>{formatPrice(subtotal)}</span>
        </div>
        <div className="cart-summary-row">
          <span>Phí vận chuyển</span>
          <span>{formatPrice(shipping)}</span>
        </div>
        {discount > 0 && (
          <div className="cart-summary-row discount">
            <span>Giảm giá</span>
            <span>-{formatPrice(discount)}</span>
          </div>
        )}
        <div className="cart-summary-total">
          <span>Tổng cộng</span>
          <span>{formatPrice(total)}</span>
        </div>

        {/* Voucher trigger */}
        <div className="co-voucher-section" style={{ borderTop: "none", marginTop: "10px" }}>
          {user ? (
            appliedVoucher ? (
              <div className="co-voucher-applied">
                <div className="co-voucher-applied-info">
                  <span className="co-voucher-tag-icon">🎟</span>
                  <span className="co-voucher-code-name"><strong>{appliedVoucher.code}</strong></span>
                  <span className="co-voucher-discount-text">(-{formatPrice(discount)})</span>
                </div>
                <button type="button" className="co-voucher-remove-btn" onClick={handleRemoveVoucher}>Gỡ</button>
              </div>
            ) : (
              <button type="button" className="co-voucher-select-trigger" onClick={handleOpenVoucherModal}>
                <span className="co-voucher-tag-icon">🎟</span>
                <span>Chọn mã giảm giá</span>
                <span className="co-voucher-arrow">➔</span>
              </button>
            )
          ) : (
            <div className="co-voucher-login-notice" style={{ padding: "12px", backgroundColor: "#f8f9fa", borderRadius: "10px", border: "1px solid #e9ecef", textAlign: "center" }}>
              <p style={{ fontSize: "13.5px", color: "#6c757d", marginBottom: "6px" }}>Đăng nhập để xem danh sách voucher giảm giá của bạn.</p>
              <Link href={`${ROUTES.login}?redirect=${encodeURIComponent(ROUTES.cart)}`} style={{ fontSize: "13.5px", color: "var(--primary-orange)", fontWeight: "700", textDecoration: "underline" }}>
                Đăng nhập ngay
              </Link>
            </div>
          )}
        </div>

        {/* kiểm tra đăng nhập */}
        <Link href={ROUTES.checkout} className="cart-checkout-btn" onClick={handleCheckout}>
          Tiến hành thanh toán
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginLeft: "8px", display: "inline-block", verticalAlign: "middle" }}>
            <rect x="2" y="5" width="20" height="14" rx="2" />
            <line x1="2" y1="10" x2="22" y2="10" />
          </svg>
        </Link>
        <p className="cart-secure">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: "6px", display: "inline-block", verticalAlign: "middle" }}>
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            <polyline points="9 11 11 13 15 9" />
          </svg>
          Thanh toán an toàn 100%
        </p>
      </aside>

      {/* Voucher Modal */}
      {showVoucherModal && (
        <div className="co-modal-overlay" onClick={() => setShowVoucherModal(false)}>
          <div className="co-modal-card" onClick={(e) => e.stopPropagation()}>
            <div className="co-modal-header">
              <h3>Chọn Voucher giảm giá</h3>
              <button type="button" className="co-modal-close" onClick={() => setShowVoucherModal(false)}>×</button>
            </div>
            <div className="co-modal-body">
              {loadingVouchers ? (
                <div className="co-loading-wrap">
                  <span className="co-spinner" aria-hidden="true"></span>
                  <p>Đang tải danh sách voucher...</p>
                </div>
              ) : vouchers.length === 0 ? (
                <p className="co-no-vouchers">Hiện tại không có voucher nào khả dụng.</p>
              ) : (
                <div className="co-voucher-list">
                  {vouchers.map((voucher) => {
                    const formattedMin = formatPrice(voucher.min_order_value);
                    const formattedDiscount = formatPrice(voucher.discount_value);
                    const startDateStr = new Date(voucher.start_date).toLocaleDateString('vi-VN');
                    const endDateStr = new Date(voucher.end_date).toLocaleDateString('vi-VN');
                    
                    return (
                      <div 
                        key={voucher.id} 
                        className={`co-voucher-item ${!voucher.can_apply ? 'disabled' : ''} ${appliedVoucher?.id === voucher.id ? 'selected' : ''}`}
                      >
                        <div className="co-voucher-card-left">
                          <div className="co-voucher-discount-val">-{formattedDiscount}</div>
                          <div className="co-voucher-badge-code">{voucher.code}</div>
                        </div>
                        <div className="co-voucher-card-right">
                          <h4 className="co-voucher-title">{voucher.description || `Giảm ngay ${formattedDiscount}`}</h4>
                          <p className="co-voucher-condition">Đơn tối thiểu: <strong>{formattedMin}</strong></p>
                          <p className="co-voucher-duration">Hạn dùng: {startDateStr} - {endDateStr}</p>
                          
                          {!voucher.can_apply && (
                            <p className="co-voucher-warning-text">
                              Mua thêm <strong>{formatPrice(voucher.missing_amount)}</strong> để sử dụng mã này
                            </p>
                          )}
                          
                          <div className="co-voucher-action-btn-wrap">
                            {voucher.can_apply ? (
                              appliedVoucher?.id === voucher.id ? (
                                <button 
                                  type="button" 
                                  className="co-voucher-btn selected"
                                  onClick={handleRemoveVoucher}
                                >
                                  Đang chọn ✓
                                </button>
                              ) : (
                                <button 
                                  type="button" 
                                  className="co-voucher-btn active"
                                  onClick={() => handleApplyVoucher(voucher)}
                                >
                                  Áp dụng
                                </button>
                              )
                            ) : (
                              <button type="button" className="co-voucher-btn" disabled>
                                Chưa đủ điều kiện
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
