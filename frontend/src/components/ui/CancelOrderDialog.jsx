"use client";

import { useEffect } from "react";

export default function CancelOrderDialog({ open, loading, onClose, onConfirm }) {
  useEffect(() => {
    if (!open) return undefined;

    const handleKeyDown = (event) => {
      if (event.key === "Escape" && !loading) onClose();
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [loading, onClose, open]);

  if (!open) return null;

  return (
    <div
      className="cancel-order-backdrop"
      onMouseDown={(event) => event.target === event.currentTarget && !loading && onClose()}
    >
      <div
        className="cancel-order-dialog"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="cancel-order-title"
        aria-describedby="cancel-order-description"
      >
        <div className="cancel-order-icon" aria-hidden="true">!</div>
        <h2 id="cancel-order-title">Bạn có chắc muốn hủy đơn?</h2>
        <p id="cancel-order-description">
          Thao tác này không thể hoàn tác. Sản phẩm trong đơn sẽ được hoàn lại kho.
        </p>
        <div className="cancel-order-actions">
          <button type="button" className="cancel-order-confirm" onClick={onConfirm} disabled={loading} autoFocus>
            {loading ? "Đang hủy..." : "Có, hủy đơn"}
          </button>
          <button type="button" className="cancel-order-dismiss" onClick={onClose} disabled={loading}>
            Không
          </button>
        </div>
      </div>
    </div>
  );
}
