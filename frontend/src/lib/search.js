import { API_BASE_URL } from "@/lib/api";

/**
 * Gọi API tìm kiếm thông minh: nhận diện loài thú cưng (Mèo/Chó),
 * gợi ý danh mục liên quan, sản phẩm tiêu biểu và bài viết cẩm nang.
 *
 * @param {string} query Từ khóa tìm kiếm
 * @returns {Promise<Object>} { query, detected_species, categories, products, blogs, total_products }
 */
export async function getSmartSearchResults(query) {
  const trimmed = (query || "").trim();
  try {
    const res = await fetch(`${API_BASE_URL}/api/search/smart?q=${encodeURIComponent(trimmed)}`, {
      cache: "no-store",
      headers: { Accept: "application/json" },
    });

    if (!res.ok) {
      throw new Error(`Search API trả về mã ${res.status}`);
    }

    const json = await res.json();
    return json?.data ?? {
      query: trimmed,
      detected_species: null,
      categories: [],
      products: [],
      blogs: [],
      total_products: 0,
    };
  } catch (error) {
    console.error("[getSmartSearchResults] Lỗi khi gọi API tìm kiếm thông minh:", error);
    return {
      query: trimmed,
      detected_species: null,
      categories: [],
      products: [],
      blogs: [],
      total_products: 0,
    };
  }
}
