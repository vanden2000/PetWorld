import ProductCard from "@/components/product/ProductCard";

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
    <section className="split-section">
      <aside className="category-sidebar">
        <h3 className="sidebar-title">Sản Phẩm mới</h3>
        <ul className="sidebar-menu">
          {SIDEBAR_LINKS.map((label) => (
            <li key={label}>
              <a href="/shop" className="sidebar-link">
                {label}
              </a>
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
    </section>
  );
}
