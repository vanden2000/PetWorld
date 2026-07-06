"use client";

import useSWR from "swr";
import { getHomeData, getProductDetail, getProducts } from "@/lib/api";
import { getOrder, getOrders } from "@/lib/auth";

export const swrOptions = {
  revalidateOnFocus: true,
  revalidateOnReconnect: true,
  dedupingInterval: 5000,
  keepPreviousData: true,
};

function stableParams(params = {}) {
  return Object.fromEntries(
    Object.entries(params)
      .filter(([, value]) => value !== undefined && value !== null && value !== "")
      .sort(([left], [right]) => left.localeCompare(right)),
  );
}

export function useHomeData(fallbackData) {
  return useSWR("/api/home", getHomeData, { ...swrOptions, fallbackData });
}

export function useProducts(params = {}, fallbackData) {
  const normalized = stableParams(params);
  return useSWR(["/api/products", normalized], ([, query]) => getProducts(query), {
    ...swrOptions,
    fallbackData,
  });
}

export function useProductDetail(slug, fallbackData) {
  return useSWR(slug ? ["/api/products/detail", slug] : null, ([, value]) => getProductDetail(value), {
    ...swrOptions,
    fallbackData,
  });
}

async function unwrap(result, fallbackMessage) {
  if (!result.ok) throw new Error(result.message || fallbackMessage);
  return result.data;
}

export function useOrders(user, params = {}) {
  const normalized = stableParams(params);
  return useSWR(user ? ["/api/orders", normalized] : null, async ([, query]) => (
    unwrap(await getOrders(query), "Không thể tải danh sách đơn hàng.")
  ), swrOptions);
}

export function useOrder(user, orderId) {
  return useSWR(user && orderId ? ["/api/orders/detail", String(orderId)] : null, async ([, id]) => (
    unwrap(await getOrder(id), "Không tìm thấy đơn hàng.")
  ), swrOptions);
}
