"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

/**
 * Làm mới dữ liệu Server Component khi tab được focus trở lại, để dữ liệu mới
 * thêm ở backend hiện ra mà không cần F5 thủ công.
 *
 * router.refresh() chạy lại các Server Component của route hiện tại và fetch lại
 * (kết hợp với fetch không cache trong lib/api.js).
 */
export default function AutoRefresh() {
  const router = useRouter();

  useEffect(() => {
    const refresh = () => {
      if (document.visibilityState === "visible") {
        router.refresh();
      }
    };

    window.addEventListener("focus", refresh);
    document.addEventListener("visibilitychange", refresh);

    return () => {
      window.removeEventListener("focus", refresh);
      document.removeEventListener("visibilitychange", refresh);
    };
  }, [router]);

  return null;
}
