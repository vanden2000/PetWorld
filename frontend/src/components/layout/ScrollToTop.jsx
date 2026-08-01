"use client";

import { useEffect } from "react";

export default function ScrollToTop({ routeKey }) {
  useEffect(() => {
    window.scrollTo({ top: 0, left: 0, behavior: "auto" });
  }, [routeKey]);

  return null;
}
