import Link from "next/link";
import { resolveBrandImage } from "@/lib/format";

export default function BrandSection({ brands = [] }) {
  return (
    <section className="homepage-section">
      <div className="section-header">
        <h2 className="section-title">Các Thương Hiệu Nổi Bật</h2>
      </div>

      <div className="brands-row">
        {brands.length > 0 ? (
          brands.map((brand) => (
            <Link
              href={`/shop?brand=${brand.slug}`}
              className="brand-card-figma"
              key={brand.id}
            >
              <img
                src={resolveBrandImage(brand.image)}
                alt={brand.name}
                title={brand.name}
                className="brand-logo-img"
              />
            </Link>
          ))
        ) : (
          <p>Không có thương hiệu nào để hiển thị.</p>
        )}
      </div>
    </section>
  );
}