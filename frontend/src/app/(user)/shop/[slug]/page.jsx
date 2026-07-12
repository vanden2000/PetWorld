import Link from "next/link";
import { notFound, permanentRedirect } from "next/navigation";
import { getProductDetail } from "@/lib/api";
import ProductDetail from "@/components/product/ProductDetail";
import ProductSection from "@/components/product/ProductSection";
import TrackRecentlyViewed from "@/components/product/TrackRecentlyViewed";
import { resolveBackendImage } from "@/lib/format";

const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000").replace(/\/$/, "");

function absoluteUrl(path = "/") {
  return new URL(path, `${SITE_URL}/`).toString();
}

export async function generateMetadata({ params }) {
  const { slug } = await params;
  const data = await getProductDetail(slug);
  const product = data?.product;
  const seo = data?.seo;
  const title = seo?.title || `${product?.name ?? slug} | PetWorld`;
  const description = seo?.description || product?.short_description || "";
  const canonical = absoluteUrl(seo?.canonical_url || `/shop/${product?.slug || slug}`);
  const image = product?.image ? resolveBackendImage(product.image) : null;

  return {
    title,
    description,
    alternates: { canonical },
    openGraph: {
      type: "website",
      title,
      description,
      url: canonical,
      images: image ? [{ url: image, alt: product?.name || title }] : [],
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
      images: image ? [image] : [],
    },
  };
}

function ReviewStars({ value = 0 }) {
  return (
    <span className="pd-review-stars">
      {Array.from({ length: 5 }).map((_, index) => (
        <svg key={index} width="14" height="14" viewBox="0 0 24 24" fill={index < value ? "currentColor" : "none"} stroke="currentColor" strokeWidth="1.5">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
      ))}
    </span>
  );
}

export default async function ProductDetailPage({ params }) {
  const { slug } = await params;
  const data = await getProductDetail(slug);

  if (data?.redirect_slug && data.redirect_slug !== slug) {
    permanentRedirect(`/shop/${data.redirect_slug}`);
  }

  if (!data?.product) {
    notFound();
  }

  const { product, reviews = [], related_products = [] } = data;
  const canonicalUrl = absoluteUrl(data.seo?.canonical_url || `/shop/${product.slug}`);
  const productImages = (product.images || [])
    .map((image) => resolveBackendImage(image.image_url))
    .filter(Boolean);
  const minPrice = product.price?.min;
  const maxPrice = product.price?.max;
  const offer = minPrice === maxPrice
    ? {
        "@type": "Offer",
        priceCurrency: "VND",
        price: minPrice,
        availability: product.stock_quantity > 0
          ? "https://schema.org/InStock"
          : "https://schema.org/OutOfStock",
        url: canonicalUrl,
      }
    : {
        "@type": "AggregateOffer",
        priceCurrency: "VND",
        lowPrice: minPrice,
        highPrice: maxPrice,
        offerCount: product.variants?.length || 0,
        availability: product.stock_quantity > 0
          ? "https://schema.org/InStock"
          : "https://schema.org/OutOfStock",
        url: canonicalUrl,
      };
  const productJsonLd = {
    "@context": "https://schema.org",
    "@type": "Product",
    name: product.name,
    description: data.seo?.description || product.short_description || "",
    sku: product.variants?.[0]?.sku,
    brand: product.brand?.name
      ? { "@type": "Brand", name: product.brand.name }
      : undefined,
    image: productImages,
    url: canonicalUrl,
    offers: offer,
    aggregateRating: product.rating?.count > 0
      ? {
          "@type": "AggregateRating",
          ratingValue: product.rating.average,
          reviewCount: product.rating.count,
        }
      : undefined,
  };

  return (
    <main className="main-content">
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify(productJsonLd).replace(/</g, "\\u003c"),
        }}
      />
      <TrackRecentlyViewed slug={product.slug} />
      <div className="homepage-container">
        <nav className="shop-breadcrumb">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <Link href="/shop">Cửa Hàng</Link>
          {product.category?.name && (
            <>
              <span className="shop-breadcrumb-sep">›</span>
              <span className="shop-breadcrumb-current">{product.category.name}</span>
            </>
          )}
        </nav>

        <ProductDetail product={product} />

        {/* Chi tiết + Tại sao chọn PetWorld */}
        <div className="pd-detail-row">
          <section className="pd-card">
            <h2 className="pd-card-title">Chi tiết sản phẩm</h2>
            {product.description ? (
              // description là HTML do backend dựng sẵn (article/h2/ul...), render nguyên khối.
              <div
                className="pd-description"
                dangerouslySetInnerHTML={{ __html: product.description }}
              />
            ) : (
              <p className="pd-description">Đang cập nhật mô tả sản phẩm.</p>
            )}
            <div className="pd-highlights">
              <div className="pd-highlight">
                <strong>100% Nguyên liệu chọn lọc</strong>
                <span>Không chất bảo quản nhân tạo hay hương liệu hoá học.</span>
              </div>
              <div className="pd-highlight">
                <strong>An toàn cho thú cưng</strong>
                <span>Được kiểm định chất lượng trước khi đến tay khách hàng.</span>
              </div>
            </div>
          </section>

          <aside className="pd-card pd-why">
            <h2 className="pd-card-title">Tại sao chọn PetWorld?</h2>
            <ul className="pd-why-list">
              <li>Cam kết 100% hàng chính hãng</li>
              <li>Miễn phí vận chuyển đơn hàng từ 500k</li>
              <li>Đổi trả dễ dàng trong vòng 7 ngày</li>
              <li>Hỗ trợ tư vấn thú cưng 24/7</li>
            </ul>
            <p className="pd-why-quote">
              &ldquo;Chúng tôi luôn chọn lọc những sản phẩm tốt nhất cho sức khoẻ của người bạn bốn chân của bạn.&rdquo;
            </p>
          </aside>
        </div>

        {/* Đánh giá khách hàng */}
        <section className="pd-card pd-reviews">
          <h2 className="pd-card-title">Đánh giá từ khách hàng</h2>
          {reviews.length > 0 ? (
            <div className="pd-review-list">
              {reviews.map((review) => (
                <div className="pd-review" key={review.id}>
                  <div className="pd-review-head">
                    <div className="pd-review-user">
                      <span className="pd-review-avatar">
                        {(review.user?.name ?? "?").charAt(0).toUpperCase()}
                      </span>
                      <div>
                        <strong>{review.user?.name ?? "Khách hàng"}</strong>
                        <ReviewStars value={review.rating} />
                      </div>
                    </div>
                    {review.created_at && (
                      <span className="pd-review-date">
                        {new Date(review.created_at).toLocaleDateString("vi-VN")}
                      </span>
                    )}
                  </div>
                  <p className="pd-review-comment">{review.comment}</p>
                </div>
              ))}
            </div>
          ) : (
            <p className="pd-description">Chưa có đánh giá nào cho sản phẩm này.</p>
          )}
        </section>

        <ProductSection title="Sản phẩm tương tự" products={related_products} columns={5} />
      </div>
    </main>
  );
}
