"use client";

import { useEffect, useState } from "react";

const DESKTOP_COUNT = 5;
const MOBILE_COUNT = 6;

export default function useResponsiveProductCount() {
  const [count, setCount] = useState(DESKTOP_COUNT);

  useEffect(() => {
    const media = window.matchMedia("(max-width: 640px)");
    const updateCount = () => setCount(media.matches ? MOBILE_COUNT : DESKTOP_COUNT);

    updateCount();
    media.addEventListener("change", updateCount);
    return () => media.removeEventListener("change", updateCount);
  }, []);

  return count;
}
