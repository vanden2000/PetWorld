"use client";

import Link from "next/link";
import { useEffect } from "react";
import { ROUTES } from "@/lib/routes";

// Error Boundary cho nhóm (user): bắt lỗi render/tải dữ liệu của các trang con.
// Next 16 dùng prop `unstable_retry` (thay cho `reset` ở các bản cũ) để thử lại segment.
export default function Error({ error, unstable_retry }) {
  useEffect(() => {
    console.error("[route error]", error);
  }, [error]);

  return (
    <main className="main-content">
      <div className="homepage-container">
        <div className="route-error" role="alert">
          <span className="route-error-icon" aria-hidden="true">⚠️</span>
          <h1>Đã xảy ra lỗi</h1>
          <p>Không tải được dữ liệu. Có thể máy chủ (backend) chưa được bật hoặc đang gián đoạn.</p>
          <div className="route-error-actions">
            <button type="button" className="route-error-retry" onClick={() => unstable_retry()}>
              Thử lại
            </button>
            <Link href={ROUTES.home} className="route-error-home">
              Về trang chủ
            </Link>
          </div>
        </div>
      </div>
    </main>
  );
}
