"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

export default function AutoRefresh() {
  const router = useRouter();

  useEffect(() => {
    let lastRefresh = Date.now();

    const refresh = () => {
      if (document.visibilityState !== "visible") return;

      const now = Date.now();

      // Tránh focus và visibilitychange gọi refresh hai lần liên tiếp.
      if (now - lastRefresh < 3000) return; // tăng lên 3 giây để tránh request quá liên tục

      lastRefresh = now;
      router.refresh();
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