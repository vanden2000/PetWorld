"use client";
import Image from "next/image";
import Link from "next/link";
import { formatPrice, resolveProductImage, useImageFallback } from "@/lib/format";
import WishlistButton from "@/components/product/WishlistButton";
import AddToCartButton from "@/components/product/AddToCartButton";

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

// Ngày đăng dạng dd/mm/yyyy; trả null nếu backend chưa có mốc thời gian.
function formatPostedDate(value) {
  if (!value) return null;
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return null;

  return date.toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

export default function ProductCard({
  product,
  badge,
  showSoldCount = false,
  showSale = true,
  showDate = false,
  salePresentation = false,
}) {
  // Trang chủ trả về `price_range`, còn /api/products trả về `price`; nhận cả hai.
  const priceRange = product.price_range || product.price || {};
  const hasSale = priceRange.has_sale;
  const currentPrice = showSale
    ? priceRange.display ?? (hasSale ? priceRange.sale_min : priceRange.min)
    : priceRange.min;
  // Giá gạch ưu tiên `compare_at` (đã ghép đúng biến thể hiển thị). Chỉ hiển thị khi
  // THỰC SỰ cao hơn giá bán — tránh trường hợp biến thể rẻ nhất không giảm mà vẫn bị
  // gạch giá bằng chính nó (do có biến thể khác đang sale).
  const rawOldPrice = showSale
    ? priceRange.compare_at ?? (hasSale ? priceRange.regular_min : null) ?? null
    : null;
  const oldPrice =
    rawOldPrice != null && Number(rawOldPrice) > Number(currentPrice) ? rawOldPrice : null;
  const discountPercent = oldPrice
    ? Math.round(((Number(oldPrice) - Number(currentPrice)) / Number(oldPrice)) * 100)
    : null;
  const ratingCount = product.rating_count ?? product.rating?.count ?? 0;
  const ratingValue = Math.round(product.rating_average ?? product.rating?.average ?? 0);
  const href = `/shop/${product.slug}`;
  // Badge "Sale" bám theo giá gạch thật của biến thể hiển thị, không theo has_sale chung.
  const badgeLabel = badge ?? (
    salePresentation && discountPercent
      ? `-${discountPercent}%`
      : (showSale && oldPrice != null ? "Sale" : null)
  );
  const soldQuantity = product.sold_quantity ?? product.soldQuantity ?? 0;
  const postedDate = showDate ? formatPostedDate(product.created_at) : null;

  return (
    <div className="product-card">
      {/* Nhãn "New" dạng ruy-băng xéo ở góc; các nhãn khác (Sale, -20%) giữ dạng thẻ vuông. */}
      {badgeLabel && (
        postedDate
          ? <span className="product-ribbon">{badgeLabel}</span>
          : <span className="product-badge">{badgeLabel}</span>
      )}

      <WishlistButton product={product} />

      <Link href={href} className="product-img-wrapper">
        <Image
          src={resolveProductImage(product.image)}
          alt={product.image_alt || product.name}
          className="product-img"
          fill
          sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 20vw"
          quality={75}
          // Card ảnh không cần tải ngay; Next sẽ tạo srcset đúng theo kích thước màn hình.
          loading="lazy"
          decoding="async"
          onError={useImageFallback}
        />
      </Link>

      <div className="product-rating">
        <Stars count={ratingValue} />
        <span className="rating-count">({ratingCount})</span>
        {postedDate && <span className="product-date-chip">{postedDate}</span>}
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
          <span className="price-current">
            {formatPrice(currentPrice)}
          </span>
          {showSoldCount ? (
            <span className="product-sold-count">Đã bán {soldQuantity}</span>
          ) : null}
        </div>
        <AddToCartButton product={product} />
      </div>
    </div>
  );
}
