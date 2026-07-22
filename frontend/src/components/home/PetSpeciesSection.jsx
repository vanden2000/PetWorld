import Link from "next/link";
import Image from "next/image";
import { resolveBackendImage } from "@/lib/format";

const FALLBACK_IMAGES = {
  cat: "/images/pet-species/cat-home.png",
  dog: "/images/pet-species/dog-home.png",
};

export default function PetSpeciesSection({ species = [] }) {
  if (species.length === 0) return null;

  return (
    <section className="homepage-section pet-species-section" aria-labelledby="pet-species-title">
      <h2 id="pet-species-title" className="sr-only">Sản phẩm theo loài thú cưng</h2>

      <div className="pet-species-grid">
        {species.map((item) => {
          const image = item.image
            ? resolveBackendImage(item.image)
            : FALLBACK_IMAGES[item.slug] || FALLBACK_IMAGES.dog;

          return (
            <Link
              key={item.id}
              href={`/shop?pet=${encodeURIComponent(item.slug)}`}
              className="pet-species-card"
            >
              <span className="pet-species-content">
                <span className="pet-species-label">Mua sắm cho</span>
                <strong>{item.name}</strong>
                <span className="pet-species-link">Xem sản phẩm phù hợp <span aria-hidden="true">→</span></span>
              </span>
              <span className="pet-species-media" style={{ backgroundColor: item.background_color || "#f1ece6" }}>
                <Image src={image} alt="" className="pet-species-image" fill sizes="(max-width: 768px) 42vw, 22vw" />
              </span>
            </Link>
          );
        })}
      </div>
    </section>
  );
}
