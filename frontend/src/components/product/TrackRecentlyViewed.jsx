"use client";

import { useEffect } from "react";

const STORAGE_KEY = "recent_product_slugs";
const MAX_RECENT_PRODUCTS = 12;

export default function TrackRecentlyViewed({ slug }) {
  useEffect(() => {
    if (!slug) return;

    let current = [];

    try {
      const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
      if (Array.isArray(stored)) current = stored;
    } catch {
      current = [];
    }

    const next = [slug, ...current.filter((item) => item !== slug)].slice(0, MAX_RECENT_PRODUCTS);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
  }, [slug]);

  return null;
}
