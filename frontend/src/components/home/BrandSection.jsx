// Dải logo "Các Thương Hiệu Nổi Bật" chỉ mang tính trang trí nên dùng danh
// sách cố định 7 thương hiệu theo mockup. Lưu ảnh vào public/image/brands/.
const FEATURED_BRANDS = [
  { name: "Pedigree", src: "/image/brands/pedigree.png" },
  { name: "Butcher's", src: "/image/brands/butchers.png" },
  { name: "Good Boy", src: "/image/brands/good-boy.png" },
  { name: "Felix", src: "/image/brands/felix.png" },
  { name: "Bakers", src: "/image/brands/bakers.png" },
  { name: "Whiskas", src: "/image/brands/whiskas.png" },
  { name: "Sheba", src: "/image/brands/sheba.png" },
];
export default function BrandSection({ brands = FEATURED_BRANDS }) {
  const items = brands.length ? brands : FEATURED_BRANDS;

  return (
    <section className="homepage-section">
      <div className="section-header">
        <h2 className="section-title">Các Thương Hiệu Nổi Bật</h2>
      </div>

      <div className="brands-row">
        {items.map((brand) => (
          <img
            key={brand.name}
            src={brand.src}
            alt={brand.name}
            title={brand.name}
            className="brand-logo-img"
          />
        ))}
      </div>
    </section>
  );
}
