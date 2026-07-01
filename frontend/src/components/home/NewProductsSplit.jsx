import ProductCard from "@/components/product/ProductCard";
import Link from "next/link";

const SIDEBAR_LINKS = [
  "Ưu đãi",
  "Ưu đãi chớp chợ",
  "Được chọn lọc kỹ lưỡng",
  "Sản phẩm tuyển chọn",
  "Mới được thêm gần đây",
];

/**
 * Khối "Sản Phẩm mới": sidebar tối bên trái + lưới 4 sản phẩm bên phải.
 */
export default function NewProductsSplit({ products = [] }) {
  if (products.length === 0) return null;

  return (
    <section className="homepage-section">
      <div className="section-header">
        <h2 className="section-title">Sản Phẩm Mới</h2>
        <Link href="/shop" className="view-all-link">
          xem tất cả ➔
        </Link>
      </div>

      <div className="split-section split-section--in-section">
      <aside className="category-sidebar">
        <ul className="sidebar-menu">
          {SIDEBAR_LINKS.map((label) => (
            <li key={label}>
              <Link href="/shop" className="sidebar-link">
                {label}
              </Link>
            </li>
          ))}
        </ul>
        <img src="/image/promo/sidebar-pets.png" alt="" className="sidebar-illustration" aria-hidden="true" />
      </aside>

      <div className="products-grid-4">
        {products.slice(0, 4).map((product) => (
          <ProductCard key={product.id} product={product} badge="New" />
        ))}
      </div>
      </div>
    </section>
  );
}
