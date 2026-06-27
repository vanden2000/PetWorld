import ProductCard from "@/components/product/ProductCard";
import RecentlyViewed from "@/components/home/RecentlyViewed";
import Link from "next/link";

/**
 * Khối "Phụ Kiện Cho Pet": banner promo cam bên trái + cột phải gồm lưới
 * 4 sản phẩm phụ kiện và hàng "Đã xem gần đây" ngay bên dưới (theo mockup).
 */
export default function AccessoriesPromo({ products = [] }) {
  if (products.length === 0) return null;

  return (
    <section className="promo-split-section">
      <div className="promo-card">
        <div className="promo-card-content">
          <span className="promo-tag">🔥 Phụ Kiện Cho Pet</span>
          <h3 className="promo-title">Mua ngay, kẻo lỡ</h3>
          <Link href="/shop?category=phu-kien" className="promo-btn">
            Ghé Shop Ngay
          </Link>
        </div>
        <img src="/image/promo/accessories.png" alt="Phụ kiện cho pet" className="promo-img" />
      </div>

      <div className="promo-right">
        <div className="products-grid-4">
          {products.slice(0, 4).map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
        <RecentlyViewed />
      </div>
    </section>
  );
}
