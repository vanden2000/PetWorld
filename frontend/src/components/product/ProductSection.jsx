import Link from "next/link";
import ProductCard from "@/components/product/ProductCard";

const GRID_CLASS = {
  3: "products-grid-3",
  4: "products-grid-4",
  5: "products-grid-5",
};

/**
 * Khối hiển thị một danh sách sản phẩm: tiêu đề + link "xem tất cả" + lưới card.
 * Dùng lại cho "Sản Phẩm Được Tuyển Chọn", "Best Selling"...
 */
export default function ProductSection({
  title,
  products = [],
  columns = 5,
  viewAllHref = "/shop",
  badge,
}) {
  if (!products.length) return null;

  return (
    <section className="homepage-section">
      <div className="section-header">
        <h2 className="section-title">{title}</h2>
        <Link href={viewAllHref} className="view-all-link">
          xem tất cả ➔
        </Link>
      </div>

      <div className={GRID_CLASS[columns] || GRID_CLASS[5]}>
        {products.map((product) => (
          <ProductCard key={product.id} product={product} badge={badge} />
        ))}
      </div>
    </section>
  );
}
