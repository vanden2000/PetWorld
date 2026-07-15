"use client";

import { useState, useSyncExternalStore, useMemo } from "react";
import Link from "next/link";
import { postBlogComment } from "@/lib/api";
import {
  getUserSnapshot,
  getServerUserSnapshot,
  onAuthChange,
} from "@/lib/auth";

function formatDate(value) {
  if (!value) return "";
  return new Date(value).toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

export default function BlogComments({ blogSlug, initialComments = [] }) {
  const [comments, setComments] = useState(initialComments);
  const [content, setContent] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  // Lấy trạng thái đăng nhập từ auth store
  const userRaw = useSyncExternalStore(
    onAuthChange,
    getUserSnapshot,
    getServerUserSnapshot
  );

  const user = useMemo(() => {
    try {
      return userRaw ? JSON.parse(userRaw) : null;
    } catch {
      return null;
    }
  }, [userRaw]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    if (!content.trim()) return;

    setLoading(true);
    setError("");
    setSuccess("");

    try {
      const newComment = await postBlogComment(blogSlug, content.trim());
      if (newComment) {
        setComments((prev) => [newComment, ...prev]);
        setContent("");
        setSuccess("Gửi bình luận thành công!");
        // Tự động tắt thông báo sau 3s
        setTimeout(() => setSuccess(""), 3000);
      }
    } catch (err) {
      setError(err.message || "Không thể gửi bình luận. Vui lòng thử lại!");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="blog-comments-section" style={{ marginTop: 40, borderTop: "1px solid #eef1f5", paddingTop: 30 }}>
      <h3 style={{ fontSize: 18, fontWeight: 800, marginBottom: 20 }}>
        Bình luận ({comments.length})
      </h3>

      {/* Thông báo lỗi / thành công */}
      {error && (
        <div style={{ color: "#d32f2f", backgroundColor: "#ffebee", padding: "10px 14px", borderRadius: "8px", fontSize: "14px", marginBottom: "15px" }}>
          {error}
        </div>
      )}
      {success && (
        <div style={{ color: "#2e7d32", backgroundColor: "#e8f5e9", padding: "10px 14px", borderRadius: "8px", fontSize: "14px", marginBottom: "15px" }}>
          {success}
        </div>
      )}

      {/* Kiểm tra phân quyền đăng nhập */}
      {user ? (
        /* Form nhập bình luận (Đã đăng nhập) */
        <form onSubmit={handleSubmit} style={{ display: "flex", gap: 12, marginBottom: 30 }}>
          <div className="news-author-avatar" style={{ margin: 0, width: 36, height: 36, fontSize: 13, backgroundColor: "#fff0e6", color: "var(--primary-orange)" }}>
            {(user.name ?? "U").charAt(0).toUpperCase()}
          </div>
          <div style={{ flex: 1 }}>
            <textarea
              placeholder="Viết bình luận của bạn..."
              value={content}
              onChange={(e) => setContent(e.target.value)}
              disabled={loading}
              required
              style={{
                width: "100%",
                padding: "12px",
                borderRadius: "12px",
                border: "1px solid #eef0f3",
                outline: "none",
                fontSize: "14px",
                resize: "none",
                height: "80px",
                fontFamily: "inherit",
              }}
            />
            <button
              type="submit"
              disabled={loading || !content.trim()}
              style={{
                marginTop: "8px",
                backgroundColor: loading || !content.trim() ? "#ccc" : "var(--primary-orange)",
                color: "#fff",
                border: "none",
                padding: "8px 20px",
                borderRadius: "20px",
                fontSize: "13px",
                fontWeight: "bold",
                cursor: loading || !content.trim() ? "not-allowed" : "pointer",
                transition: "background-color 0.2s",
              }}
            >
              {loading ? "Đang gửi..." : "Gửi bình luận"}
            </button>
          </div>
        </form>
      ) : (
        /* Thông báo yêu cầu đăng nhập */
        <div style={{
          backgroundColor: "#f8f9fa",
          border: "1px dashed #ccc",
          borderRadius: "12px",
          padding: "24px",
          textAlign: "center",
          marginBottom: 30,
        }}>
          <p style={{ margin: "0 0 12px 0", color: "#666", fontSize: "14px" }}>
            Vui lòng đăng nhập tài khoản của bạn để viết bình luận.
          </p>
          <Link
            href={`/login?redirect=/news/${blogSlug}`}
            style={{
              display: "inline-block",
              backgroundColor: "var(--primary-orange)",
              color: "#fff",
              textDecoration: "none",
              padding: "8px 24px",
              borderRadius: "20px",
              fontSize: "13.5px",
              fontWeight: "bold",
              transition: "transform 0.2s",
            }}
            onMouseOver={(e) => (e.currentTarget.style.transform = "scale(1.03)")}
            onMouseOut={(e) => (e.currentTarget.style.transform = "scale(1)")}
          >
            Đăng nhập ngay
          </Link>
        </div>
      )}

      {/* Danh sách bình luận */}
      {comments.length > 0 ? (
        <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
          {comments.map((comment) => (
            <div key={comment.id} style={{ display: "flex", gap: 12 }}>
              <div className="news-author-avatar" style={{ margin: 0, width: 36, height: 36, fontSize: 13, backgroundColor: "#fff0e6", color: "var(--primary-orange)" }}>
                {(comment.user?.name ?? "U").charAt(0).toUpperCase()}
              </div>
              <div style={{ flex: 1, backgroundColor: "#f8f9fa", padding: "12px 16px", borderRadius: "14px" }}>
                <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 6, flexWrap: "wrap", gap: 8 }}>
                  <strong style={{ fontSize: 14, color: "var(--text-dark)" }}>{comment.user?.name ?? "Khách"}</strong>
                  <span style={{ fontSize: 12, color: "var(--text-muted)" }}>{formatDate(comment.created_at)}</span>
                </div>
                <p style={{ fontSize: 14, color: "#444", margin: 0, lineHeight: 1.5 }}>{comment.content}</p>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <p style={{ fontSize: 14, color: "var(--text-muted)", fontStyle: "italic" }}>
          Chưa có bình luận nào cho bài viết này. Hãy là người đầu tiên bình luận!
        </p>
      )}
    </div>
  );
}
