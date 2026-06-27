import Link from "next/link";
import { formatPrice, resolveImage } from "@/lib/format";

// 5 ngôi sao đánh giá (mặc định hiển thị đầy theo mockup).
function Stars({ count = 5 }) {
  return (
    <>
      {Array.from({ length: 5 }).map((_, index) => (
        <span className="star-filled" key={index}>
          <svg width="14" height="14" viewBox="0 0 24 24" fill={index < count ? "currentColor" : "none"} stroke="currentColor" strokeWidth="1.5">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
          </svg>
        </span>
      ))}
    </>
  );
}

export default function ProductCard({ product, badge }) {
  const priceRange = product.price_range || {};
  const hasSale = priceRange.has_sale;
  const currentPrice = priceRange.display ?? (hasSale ? priceRange.sale_min : priceRange.min);
  const oldPrice = priceRange.compare_at ?? null;
  const ratingCount = product.rating_count ?? product.rating?.count ?? 0;
  const ratingValue = Math.round(product.rating_average ?? product.rating?.average ?? 0);
  const href = `/shop/${product.slug}`;
  // Ưu tiên badge truyền vào, nếu không thì tự gắn "Sale" khi có giá khuyến mãi.
  const badgeLabel = badge ?? (hasSale ? "Sale" : null);

  return (
    <div className="product-card">
      {badgeLabel && <span className="product-badge">{badgeLabel}</span>}

      <button className="product-wishlist-btn" type="button" aria-label="Thêm vào yêu thích">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
        </svg>
      </button>

      <Link href={href} className="product-img-wrapper">
        <img src={resolveImage(product.image)} alt={product.name} className="product-img" />
      </Link>

      <div className="product-rating">
        <Stars count={ratingValue} />
        <span className="rating-count">({ratingCount})</span>
      </div>

      <Link href={href} className="product-title">
        {product.name}
      </Link>

      <p className="product-category">
        Danh mục: <span>{product.category?.name ?? "Đang cập nhật"}</span>
      </p>

      <div className="product-footer">
        <div className="product-price">
          {oldPrice ? <span className="price-old">{formatPrice(oldPrice)}</span> : null}
          <span className="price-current">{formatPrice(currentPrice)}</span>
        </div>
        <button className="product-add-btn" type="button" aria-label="Thêm vào giỏ hàng">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="9" cy="21" r="1" />
            <circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
          </svg>
        </button>
      </div>
    </div>
  );
}
