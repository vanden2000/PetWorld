"use client";

import { useEffect } from "react";

export default function LoginRequiredDialog({ open, onClose, onConfirm }) {
  useEffect(() => {
    if (!open) return undefined;

    const handleKeyDown = (event) => {
      if (event.key === "Escape") onClose();
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div
      className="login-required-backdrop"
      onMouseDown={(event) => event.target === event.currentTarget && onClose()}
    >
      <div
        className="login-required-dialog"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="login-required-title"
        aria-describedby="login-required-description"
      >
        <div className="login-required-icon" aria-hidden="true">!</div>
        <h2 id="login-required-title">Bạn chưa đăng nhập</h2>
        <p id="login-required-description">
          Vui lòng đăng nhập để tiếp tục thanh toán đơn hàng.
        </p>
        <div className="login-required-actions">
          <button type="button" className="login-required-confirm" onClick={onConfirm} autoFocus>
            Đăng nhập
          </button>
          <button type="button" className="login-required-cancel" onClick={onClose}>
            Hủy
          </button>
        </div>
      </div>
    </div>
  );
}
