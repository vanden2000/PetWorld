"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";

/**
 * Chặn rời khỏi màn chờ thanh toán chuyển khoản.
 *
 * Ba đường thoát được xử lý riêng:
 *  - Bấm link nội bộ (menu, logo...): bắt ở tầng document rồi hiện hộp xác nhận.
 *  - Nút Back của trình duyệt: chặn bằng popstate, đẩy lại history rồi hiện hộp xác nhận.
 *  - Đóng tab / tải lại: chỉ hiện cảnh báo mặc định của trình duyệt (không hủy đơn,
 *    xem ghi chú ở handleBeforeUnload).
 *
 * `active` chỉ nên bật khi đơn là chuyển khoản, chưa thanh toán và QR còn hiệu lực.
 */
export default function PaymentLeaveGuard({ active, minutesLeft, onConfirmLeave }) {
  const router = useRouter();
  const [pendingHref, setPendingHref] = useState(null);
  const [leaving, setLeaving] = useState(false);
  const [error, setError] = useState("");
  const activeRef = useRef(active);

  useEffect(() => {
    activeRef.current = active;
    if (!active) {
      setPendingHref(null);
      setError("");
    }
  }, [active]);

  // --- Đóng tab / tải lại trang ---
  useEffect(() => {
    if (!active) return;

    // Trình duyệt không cho tùy biến nội dung hộp thoại này, và cũng không chạy
    // được request hủy đơn một cách đáng tin cậy tại đây. Đơn chưa trả sẽ do
    // `orders:expire-unpaid` dọn khi hết hạn, nên chỉ cảnh báo là đủ.
    const handleBeforeUnload = (event) => {
      event.preventDefault();
      event.returnValue = "";
    };

    window.addEventListener("beforeunload", handleBeforeUnload);
    return () => window.removeEventListener("beforeunload", handleBeforeUnload);
  }, [active]);

  // --- Bấm link nội bộ ---
  useEffect(() => {
    if (!active) return;

    const handleClick = (event) => {
      if (!activeRef.current) return;
      if (event.defaultPrevented) return;
      // Chuột giữa / Ctrl+click: mở tab mới, không rời trang hiện tại.
      if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

      const anchor = event.target instanceof Element ? event.target.closest("a") : null;
      if (!anchor) return;

      const href = anchor.getAttribute("href");
      if (!href || anchor.target === "_blank" || anchor.hasAttribute("download")) return;
      if (href.startsWith("#") || href.startsWith("tel:") || href.startsWith("mailto:")) return;

      const target = new URL(anchor.href, window.location.href);
      if (target.origin !== window.location.origin) return;
      // Cùng đúng trang đang đứng thì không tính là rời đi.
      if (target.pathname === window.location.pathname && target.search === window.location.search) return;

      event.preventDefault();
      event.stopPropagation();
      setPendingHref(target.pathname + target.search);
    };

    document.addEventListener("click", handleClick, true);
    return () => document.removeEventListener("click", handleClick, true);
  }, [active]);

  // --- Nút Back của trình duyệt ---
  useEffect(() => {
    if (!active) return;

    // Chèn một mốc history để lần Back đầu tiên rơi vào đây thay vì rời trang.
    window.history.pushState(null, "", window.location.href);

    const handlePopState = () => {
      if (!activeRef.current) return;
      window.history.pushState(null, "", window.location.href);
      setPendingHref("/");
    };

    window.addEventListener("popstate", handlePopState);
    return () => window.removeEventListener("popstate", handlePopState);
  }, [active]);

  const stay = useCallback(() => {
    setPendingHref(null);
    setError("");
  }, []);

  const leave = useCallback(async () => {
    if (leaving) return;
    setLeaving(true);
    setError("");

    const result = await onConfirmLeave();

    if (!result?.ok) {
      setLeaving(false);
      setError(result?.message || "Không hủy được đơn hàng. Vui lòng thử lại.");
      return;
    }

    // Đơn vừa được trả tiền ngay lúc bấm: ở lại để màn thành công hiện ra.
    if (result.paid) {
      setLeaving(false);
      setPendingHref(null);
      return;
    }

    activeRef.current = false;
    const href = pendingHref || "/";
    setPendingHref(null);
    router.push(href);
  }, [leaving, onConfirmLeave, pendingHref, router]);

  if (!active || !pendingHref) return null;

  return (
    <div className="co-leave-backdrop" role="presentation" onClick={leaving ? undefined : stay}>
      <div
        className="co-leave-modal"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="co-leave-title"
        aria-describedby="co-leave-desc"
        onClick={(event) => event.stopPropagation()}
      >
        <div className="co-leave-icon" aria-hidden="true">!</div>

        <h3 className="co-leave-title" id="co-leave-title">Rời khỏi trang thanh toán?</h3>

        <div className="co-leave-desc" id="co-leave-desc">
          <p>Đơn hàng của bạn <strong>chưa được thanh toán</strong>.</p>
          <p>Nếu rời khỏi bây giờ, hệ thống sẽ <strong>hủy đơn</strong> và lưu sản phẩm lại vào giỏ hàng.</p>
        </div>

        <ol className="co-leave-notes">
          <li>Bạn vẫn còn <strong>{minutesLeft} phút</strong> để hoàn tất thanh toán.</li>
          <li>Đã chuyển khoản rồi thì đừng rời trang — chờ hệ thống xác nhận.</li>
        </ol>

        {error ? <p className="co-leave-error">{error}</p> : null}

        <div className="co-leave-actions">
          <button type="button" className="co-leave-stay" onClick={stay} disabled={leaving} autoFocus>
            Tiếp tục thanh toán
          </button>
          <button type="button" className="co-leave-go" onClick={leave} disabled={leaving}>
            {leaving ? "Đang hủy đơn..." : "Rời khỏi & hủy đơn"}
          </button>
        </div>
      </div>
    </div>
  );
}
