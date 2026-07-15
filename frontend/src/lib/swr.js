"use client";

import useSWR from "swr";
import { getHomeData, getProductDetail, getProducts } from "@/lib/api";
import { getOrder, getOrders } from "@/lib/auth";

export const swrOptions = {
  // Khi người dùng quay lại tab, chỉ lấy dữ liệu mới ở nền thay vì tải lại cả trang.
  revalidateOnFocus: true,
  revalidateOnReconnect: true,
  // Không tự gọi lại API ngay khi dữ liệu do Server Component truyền xuống vẫn còn dùng được.
  revalidateIfStale: false,
  // Gộp các yêu cầu trùng nhau trong 30 giây để giảm tải cho Laravel và trình duyệt.
  dedupingInterval: 30_000,
  // Giữ nội dung hiện tại trong lúc cập nhật để giao diện không nhấp nháy.
  keepPreviousData: true,
};

function optionsWithFallback(fallbackData) {
  return {
    ...swrOptions,
    fallbackData,
    // Nếu server đã cấp dữ liệu ban đầu thì không fetch lại ngay sau khi hydrate.
    revalidateOnMount: fallbackData === undefined,
  };
}

function stableParams(params = {}) {
  return Object.fromEntries(
    Object.entries(params)
      .filter(([, value]) => value !== undefined && value !== null && value !== "")
      .sort(([left], [right]) => left.localeCompare(right)),
  );
}

export function useHomeData(fallbackData) {
  return useSWR("/api/home", getHomeData, optionsWithFallback(fallbackData));
}

export function useProducts(params = {}, fallbackData) {
  const normalized = stableParams(params);
  return useSWR(["/api/products", normalized], ([, query]) => getProducts(query), {
    ...optionsWithFallback(fallbackData),
  });
}

export function useProductDetail(slug, fallbackData) {
  return useSWR(slug ? ["/api/products/detail", slug] : null, ([, value]) => getProductDetail(value), {
    ...optionsWithFallback(fallbackData),
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
