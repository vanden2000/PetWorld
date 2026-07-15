"use client";

import { useEffect, useState } from "react";

export default function ReviewDialog({ item, loading, onClose, onSubmit }) {
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState("");

  useEffect(() => {
    if (!item) return undefined;
    const handleKeyDown = (event) => {
      if (event.key === "Escape" && !loading) onClose();
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [item, loading, onClose]);

  if (!item) return null;

  return (
    <div className="review-dialog-backdrop" onMouseDown={(event) => event.target === event.currentTarget && !loading && onClose()}>
      <form className="review-dialog" onSubmit={(event) => { event.preventDefault(); onSubmit({ rating, comment: comment.trim() }); }}>
        <button type="button" className="review-dialog-close" onClick={onClose} disabled={loading} aria-label="Đóng">×</button>
        <span className="review-dialog-eyebrow">Đánh giá sản phẩm</span>
        <h2>{item.name}</h2>
        <p>Trải nghiệm của bạn với sản phẩm này như thế nào?</p>
        <div className="review-stars" role="radiogroup" aria-label="Số sao đánh giá">
          {[1, 2, 3, 4, 5].map((value) => (
            <button key={value} type="button" className={value <= rating ? "active" : ""} onClick={() => setRating(value)} role="radio" aria-checked={rating === value} aria-label={`${value} sao`}>★</button>
          ))}
        </div>
        <strong className="review-rating-label">{rating}/5 sao</strong>
        <label>
          Nhận xét của bạn
          <textarea value={comment} onChange={(event) => setComment(event.target.value)} maxLength={2000} rows={5} placeholder="Chia sẻ cảm nhận về chất lượng sản phẩm..." />
        </label>
        <div className="review-dialog-actions">
          <button type="button" onClick={onClose} disabled={loading}>Để sau</button>
          <button type="submit" disabled={loading}>{loading ? "Đang gửi..." : "Gửi đánh giá"}</button>
        </div>
      </form>
    </div>
  );
}
