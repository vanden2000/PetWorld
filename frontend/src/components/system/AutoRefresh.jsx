"use client";

import { useEffect } from "react";

export default function AutoRefresh() {
  useEffect(() => {
    let lastRefresh = Date.now();

    const refresh = () => {
      if (document.visibilityState !== "visible") return;

      const now = Date.now();

      // Tránh focus và visibilitychange gọi reload hai lần liên tiếp.
      if (now - lastRefresh < 1000) return;

      lastRefresh = now;
      window.location.reload();
    };

    window.addEventListener("focus", refresh);
    document.addEventListener("visibilitychange", refresh);

    return () => {
      window.removeEventListener("focus", refresh);
      document.removeEventListener("visibilitychange", refresh);
    };
  }, []);

  return null;
}