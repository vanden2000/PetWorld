import Link from "next/link";
import { formatPrice, resolveImage } from "@/lib/format";
import WishlistButton from "@/components/product/WishlistButton";
import AddToCartButton from "@/components/product/AddToCartButton";
import { resolveProductImage } from "@/lib/format";

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

export default function ProductCard({
  product,
  badge,
  showSoldCount = false,
  showSale = true,
}) {
  // Trang chủ trả về `price_range`, còn /api/products trả về `price`; nhận cả hai.
  const priceRange = product.price_range || product.price || {};
  const hasSale = priceRange.has_sale;
  const currentPrice = showSale
    ? priceRange.display ?? (hasSale ? priceRange.sale_min : priceRange.min)
    : priceRange.min;
  const oldPrice = showSale
    ? priceRange.compare_at ?? (hasSale ? priceRange.regular_min : null) ?? null
    : null;
  const ratingCount = product.rating_count ?? product.rating?.count ?? 0;
  const ratingValue = Math.round(product.rating_average ?? product.rating?.average ?? 0);
  const href = `/shop/${product.slug}`;
  // Ưu tiên badge truyền vào, nếu không thì tự gắn "Sale" khi có giá khuyến mãi.
  const badgeLabel = badge ?? (showSale && hasSale ? "Sale" : null);
  const soldQuantity = product.sold_quantity ?? product.soldQuantity ?? 0;

  return (
    <div className="product-card">
      {badgeLabel && <span className="product-badge">{badgeLabel}</span>}

      <WishlistButton product={product} />

      <Link href={href} className="product-img-wrapper">
        <img src={resolveProductImage(product.image)} alt={product.name} className="product-img" />
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
          {showSoldCount ? (
            <span className="product-sold-count">Đã bán {soldQuantity}</span>
          ) : null}
        </div>
        <AddToCartButton product={product} />
      </div>
    </div>
  );
}
