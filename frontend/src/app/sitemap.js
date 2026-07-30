import { API_BASE_URL } from "@/lib/api";
import { resolveBlogImage } from "@/lib/format";

const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000").replace(/\/$/, "");

export default async function sitemap() {
  const staticPages = [
    { url: `${SITE_URL}/`, changeFrequency: "weekly", priority: 1 },
    { url: `${SITE_URL}/shop`, changeFrequency: "daily", priority: 0.9 },
    { url: `${SITE_URL}/news`, changeFrequency: "daily", priority: 0.8 },
    { url: `${SITE_URL}/about-us`, changeFrequency: "monthly", priority: 0.5 },
    { url: `${SITE_URL}/contact`, changeFrequency: "monthly", priority: 0.5 },
  ];

  try {
    const response = await fetch(`${API_BASE_URL}/api/blogs/sitemap`, {
      next: { revalidate: 3600 },
    });
    if (!response.ok) return staticPages;

    const data = await response.json();
    const blogs = data?.data?.blogs ?? [];
    const blogPages = blogs.map((blog) => ({
      url: `${SITE_URL}/news/${encodeURIComponent(blog.slug)}`,
      lastModified: blog.updated_at || blog.created_at,
      changeFrequency: "weekly",
      priority: 0.7,
      images: blog.image ? [resolveBlogImage(blog.image)] : [],
    }));

    return [...staticPages, ...blogPages];
  } catch (error) {
    console.error("[sitemap] Không lấy được danh sách bài viết:", error);
    return staticPages;
  }
}
