"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { formatPrice } from "@/lib/format";

/**
 * Modal chờ thanh toán chuyển khoản.
 *
 * Mở đè lên trang checkout (không điều hướng) ngay khi đơn chuyển khoản được tạo,
 * và chỉ đóng khi: thanh toán thành công, hoặc khách chủ động hủy đơn.
 *
 * Modal không tự đóng được bằng Esc / click ra ngoài — rời khỏi màn này đồng nghĩa
 * với hủy đơn nên phải đi qua nút "Rời khỏi & hủy đơn" có bước xác nhận.
 */
export default function PaymentQrModal({
  order,
  qrUrl,
  bankInfo,
  secondsLeft,
  expired,
  paid,
  onRegenerate,
  onCancelOrder,
  onCopy,
}) {
  // Bước xác nhận trước khi hủy: hành động không hoàn tác được.
  const [confirming, setConfirming] = useState(false);
  const [cancelling, setCancelling] = useState(false);
  const [error, setError] = useState("");
  const [regenerating, setRegenerating] = useState(false);
  const [copiedKey, setCopiedKey] = useState("");
  const dialogRef = useRef(null);

  const mm = String(Math.floor(secondsLeft / 60)).padStart(2, "0");
  const ss = String(secondsLeft % 60).padStart(2, "0");
  const orderItems = order.items ?? [];
  const discount = Number(order.discount_amount ?? 0);

  // Khóa cuộn nền khi modal mở để trang checkout phía sau không trôi theo.
  useEffect(() => {
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = previous;
    };
  }, []);

  // Đưa focus vào modal để người dùng bàn phím / screen reader vào đúng ngữ cảnh.
  useEffect(() => {
    dialogRef.current?.focus();
  }, []);

  // Chặn Tab thoát ra nền và Esc đóng modal (đóng = hủy đơn, phải bấm nút).
  const handleKeyDown = useCallback((event) => {
    if (event.key === "Escape") {
      event.preventDefault();
      return;
    }
    if (event.key !== "Tab") return;

    const focusables = dialogRef.current?.querySelectorAll(
      'button:not([disabled]), a[href], input, [tabindex]:not([tabindex="-1"])'
    );
    if (!focusables?.length) return;

    const first = focusables[0];
    const last = focusables[focusables.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }, []);

  const copyValue = async (value, key) => {
    await onCopy(value);
    setCopiedKey(key);
    setTimeout(() => setCopiedKey((current) => (current === key ? "" : current)), 2000);
  };

  const handleRegenerate = async () => {
    setRegenerating(true);
    try {
      await onRegenerate();
    } finally {
      setRegenerating(false);
    }
  };

  const handleCancel = async () => {
    if (cancelling) return;
    setCancelling(true);
    setError("");

    const result = await onCancelOrder();

    // Đơn vừa được trả tiền đúng lúc bấm hủy: ở lại để màn thành công hiện ra.
    if (result?.paid) {
      setCancelling(false);
      setConfirming(false);
      return;
    }

    if (!result?.ok) {
      setCancelling(false);
      setError(result?.message || "Không hủy được đơn hàng. Vui lòng thử lại.");
      return;
    }
    // Hủy thành công: component cha gỡ modal khỏi cây, không cần dọn state ở đây.
  };

  return (
    <div className="co-qrm-backdrop" role="presentation">
      <div
        className="co-qrm-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="co-qrm-title"
        tabIndex={-1}
        ref={dialogRef}
        onKeyDown={handleKeyDown}
      >
        {/* Đầu modal: mã đơn + đồng hồ đếm ngược */}
        <header className="co-qrm-head">
          <div className="co-qrm-head-main">
            <h2 className="co-qrm-title" id="co-qrm-title">
              Thanh toán đơn hàng
            </h2>
            <p className="co-qrm-code">
              Mã đơn hàng: <strong>{order.payment_code}</strong>
              <button
                type="button"
                className="co-qrm-copy-inline"
                onClick={() => copyValue(order.payment_code, "code")}
                aria-label="Sao chép mã đơn hàng"
              >
                {copiedKey === "code" ? "Đã chép" : "⧉"}
              </button>
            </p>
          </div>

          {!paid && !expired && (
            <div className="co-qrm-timer" aria-live="off">
              <span className="co-qrm-timer-label">Còn lại</span>
              <span className="co-qrm-timer-value">
                {mm}:{ss}
              </span>
            </div>
          )}
        </header>

        <div className="co-qrm-body">
          <div className="co-qrm-pay">
            {/* 1. QR */}
            <section className="co-qrm-section">
              <h3 className="co-qrm-section-title">1. Thanh toán nhanh bằng QR Code</h3>
              <p className="co-qrm-desc">Quét mã QR bằng ứng dụng ngân hàng hoặc ví điện tử</p>

              {paid ? (
                <div className="co-pay-paid">✓ Đã thanh toán</div>
              ) : expired ? (
                <div className="co-qr-expired">
                  <p>Mã QR đã hết hạn sau 15 phút.</p>
                  <button
                    type="button"
                    className="co-btn-solid"
                    onClick={handleRegenerate}
                    disabled={regenerating}
                  >
                    {regenerating ? "Đang tạo lại..." : "Tạo lại mã QR"}
                  </button>
                </div>
              ) : (
                <div className="co-qr-panel">
                  <img
                    src={qrUrl}
                    alt={`QR thanh toán ${bankInfo.name} - ${bankInfo.account} - ${bankInfo.holder}`}
                    className="co-qr-img"
                    width="260"
                  />
                </div>
              )}

              <div className="co-qrm-waiting" aria-live="polite">
                {paid ? null : expired ? null : (
                  <>
                    <span className="co-spinner" aria-hidden="true" />
                    <span>Đang chờ xác nhận thanh toán từ ngân hàng...</span>
                  </>
                )}
              </div>
            </section>

            {/* 2. Chuyển khoản thủ công */}
            <section className="co-qrm-section">
              <h3 className="co-qrm-section-title">2. Chuyển khoản thủ công</h3>
              <p className="co-qrm-desc">
                Sử dụng thông tin bên dưới để chuyển khoản qua ứng dụng ngân hàng
              </p>
              <div className="co-bank-info">
                {[
                  ["Ngân hàng", bankInfo.name],
                  ["Chủ tài khoản", bankInfo.holder],
                  ["Số tài khoản", bankInfo.account],
                  ["Số tiền", formatPrice(order.total_amount)],
                  ["Nội dung chuyển khoản", order.payment_code],
                ].map(([label, value]) => (
                  <div className="co-bank-row" key={label}>
                    <span>{label}</span>
                    <strong
                      className={
                        label === "Số tiền" || label.includes("Nội dung") ? "co-bank-accent" : ""
                      }
                    >
                      {value}
                    </strong>
                    <button type="button" onClick={() => copyValue(value, label)}>
                      {copiedKey === label ? "Đã chép" : "Sao chép"}
                    </button>
                  </div>
                ))}
              </div>
              <p className="co-pay-warning">
                Lưu ý quan trọng: Chuyển khoản đúng số tiền và nội dung để đơn hàng được xác nhận
                nhanh chóng.
              </p>
            </section>
          </div>

          {/* Tóm tắt đơn hàng */}
          <aside className="co-qrm-summary">
            <h3 className="co-qrm-section-title">Thông tin đơn hàng</h3>
            <div className="co-summary-items">
              {orderItems.map((item) => (
                <div className="co-summary-item" key={item.id}>
                  <span className="co-summary-item-name">
                    {item.product_name} <em>× {item.quantity}</em>
                  </span>
                  <span>{formatPrice(item.price * item.quantity)}</span>
                </div>
              ))}
            </div>
            <div className="co-summary-row">
              <span>Phí vận chuyển</span>
              <span>{formatPrice(order.shipping_fee)}</span>
            </div>
            {discount > 0 && (
              <div className="co-summary-row co-summary-discount">
                <span>{order.voucher?.code ? `Voucher (${order.voucher.code})` : "Voucher"}</span>
                <span className="co-discount-amount">-{formatPrice(discount)}</span>
              </div>
            )}
            <div className="co-summary-total">
              <span>Tổng thanh toán</span>
              <span>{formatPrice(order.total_amount)}</span>
            </div>
            <p className="co-vat-note">(Đã bao gồm VAT)</p>

            <div className="co-qrm-commitments">
              <strong>Cam kết từ PetWorld</strong>
              <span>100% sản phẩm chính hãng</span>
              <span>Đổi trả miễn phí trong 7 ngày</span>
              <span>Giao hàng nhanh toàn quốc</span>
            </div>
          </aside>
        </div>

        {/* Chân modal: ghi chú + hai nút hành động */}
        <footer className="co-qrm-foot">
          {confirming ? (
            <div className="co-qrm-confirm">
              <p className="co-qrm-confirm-text">
                Chắc chắn hủy đơn <strong>{order.payment_code}</strong>? Sản phẩm sẽ được lưu lại
                vào giỏ hàng.
              </p>
              {error ? <p className="co-leave-error">{error}</p> : null}
              <div className="co-qrm-actions">
                <button
                  type="button"
                  className="co-leave-stay"
                  onClick={() => {
                    setConfirming(false);
                    setError("");
                  }}
                  disabled={cancelling}
                  autoFocus
                >
                  Không, quay lại
                </button>
                <button
                  type="button"
                  className="co-leave-go"
                  onClick={handleCancel}
                  disabled={cancelling}
                >
                  {cancelling ? "Đang hủy đơn..." : "Hủy đơn"}
                </button>
              </div>
            </div>
          ) : (
            <>
              <ol className="co-leave-notes">
                <li>
                  Đơn hàng của bạn <strong>chưa được thanh toán</strong>.
                </li>
                <li>Đã chuyển khoản rồi thì đừng đóng — chờ hệ thống xác nhận.</li>
              </ol>
              <div className="co-qrm-actions">
                <button type="button" className="co-leave-go" onClick={() => setConfirming(true)}>
                  Hủy đơn
                </button>
              </div>
            </>
          )}
        </footer>
      </div>
    </div>
  );
}
