// URL gốc của backend Laravel. Lấy từ biến môi trường, có giá trị mặc định cho local.
export const API_BASE_URL = (
  process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
).replace(/\/$/, "");

/**
 * Lấy toàn bộ dữ liệu cho trang chủ từ `GET /api/home`.
 * Trả về object rỗng nếu backend chưa bật để trang vẫn render được.
 */
export async function getHomeData() {
  try {
    const res = await fetch(`${API_BASE_URL}/api/home`, {
      // Luôn lấy mới để dữ liệu backend cập nhật ngay (kết hợp AutoRefresh khi focus).
      cache: "no-store",
    });

    if (!res.ok) {
      throw new Error(`Home API trả về ${res.status}`);
    }

    const json = await res.json();
    return json?.data ?? {};
  } catch (error) {
    console.error("[getHomeData] Không lấy được dữ liệu trang chủ:", error);
    return {};
  }
}

/**
 * Dựng query string từ một object, bỏ qua các giá trị rỗng/undefined.
 * Ví dụ: { category: "cho", page: 2 } -> "category=cho&page=2".
 */
function buildQuery(params = {}) {
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === "") continue;
    search.set(key, String(value));
  }
  const query = search.toString();
  return query ? `?${query}` : "";
}

/**
 * Lấy danh sách sản phẩm từ `GET /api/products`.
 * `params` nhận trực tiếp searchParams của trang (category, search, sort, page...).
 * Trả về object data (products, pagination, filters, total...) hoặc fallback rỗng.
 */
export async function getProducts(params = {}) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/products${buildQuery(params)}`, {
      // Luôn lấy mới, đồng nhất với getHomeData.
      cache: "no-store",
    });

    if (!res.ok) {
      throw new Error(`Products API trả về ${res.status}`);
    }

    const json = await res.json();
    return json?.data ?? {};
  } catch (error) {
    console.error("[getProducts] Không lấy được danh sách sản phẩm:", error);
    return {};
  }
}

/**
 * Lấy chi tiết một sản phẩm từ `GET /api/products/{slug}`.
 * Trả về null nếu không tìm thấy hoặc lỗi để trang gọi notFound().
 */
export async function getProductDetail(slug) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/products/${encodeURIComponent(slug)}`, {
      cache: "no-store",
    });

    if (!res.ok) {
      throw new Error(`Product detail API trả về ${res.status}`);
    }

    const json = await res.json();
    return json?.data ?? null;
  } catch (error) {
    console.error(`[getProductDetail] Không lấy được sản phẩm "${slug}":`, error);
    return null;
  }
}

/**
 * Lấy danh sách bài viết từ `GET /api/blogs`.
 * `params` nhận searchParams của trang (category, search, sort, page...).
 */
export async function getBlogs(params = {}) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/blogs${buildQuery(params)}`, {
      cache: "no-store",
    });

    if (!res.ok) {
      throw new Error(`Blogs API trả về ${res.status}`);
    }

    const json = await res.json();
    return json?.data ?? {};
  } catch (error) {
    console.error("[getBlogs] Không lấy được danh sách bài viết:", error);
    return {};
  }
}

/**
 * Lấy chi tiết một bài viết từ `GET /api/blogs/{slug}`.
 * Trả về null nếu không tìm thấy hoặc lỗi để trang gọi notFound().
 */
export async function getBlogDetail(slug) {
  try {
    const res = await fetch(`${API_BASE_URL}/api/blogs/${encodeURIComponent(slug)}`, {
      cache: "no-store",
    });

    if (!res.ok) {
      throw new Error(`Blog detail API trả về ${res.status}`);
    }

    const json = await res.json();
    return json?.data ?? null;
  } catch (error) {
    console.error(`[getBlogDetail] Không lấy được bài viết "${slug}":`, error);
    return null;
  }
}
