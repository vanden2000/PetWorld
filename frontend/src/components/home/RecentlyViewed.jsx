import Link from "next/link";
import { resolveImage } from "@/lib/format";

/**
 * Hàng "Đã xem gần đây": nhãn + dải thumbnail tròn của sản phẩm.
 */
export default function RecentlyViewed({ products = [] }) {
  if (products.length === 0) return null;

  return (
    <div className="recent-viewed-row">
      <span className="recent-viewed-label">Đã xem gần đây</span>
      <div className="recent-viewed-list">
        {products.slice(0, 12).map((product) => (
          <Link
            key={product.id}
            href={`/shop/${product.slug}`}
            title={product.name}
            className="recent-viewed-thumb-link"
          >
            <img
              src={resolveImage(product.image)}
              alt={product.name}
              className="recent-viewed-thumb"
            />
          </Link>
        ))}
      </div>
    </div>
  );
}
