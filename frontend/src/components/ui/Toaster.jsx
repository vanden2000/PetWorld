"use client";

import { useSyncExternalStore } from "react";
import {
  subscribeToast,
  getToastSnapshot,
  getServerToastSnapshot,
  dismissToast,
} from "@/lib/toast";

const ICONS = {
  success: "✓",
  error: "!",
  info: "i",
};

export default function Toaster() {
  const toasts = useSyncExternalStore(subscribeToast, getToastSnapshot, getServerToastSnapshot);

  if (!toasts.length) return null;

  return (
    <div className="toast-stack" role="status" aria-live="polite">
      {toasts.map((toast) => (
        <button
          type="button"
          key={toast.id}
          className={`toast toast-${toast.type}`}
          onClick={() => dismissToast(toast.id)}
        >
          <span className="toast-icon">{ICONS[toast.type] ?? ICONS.info}</span>
          <span className="toast-msg">{toast.message}</span>
        </button>
      ))}
    </div>
  );
}
