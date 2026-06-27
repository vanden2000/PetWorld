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
      // Cache 60s rồi làm mới, tránh gọi API mỗi request.
      next: { revalidate: 60 },
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
