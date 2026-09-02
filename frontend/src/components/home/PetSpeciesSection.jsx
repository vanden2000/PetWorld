import Link from "next/link";
import Image from "next/image";
import { resolveBackendImage } from "@/lib/format";

const SPECIES_THEMES = {
  cat: {
    fallbackImage: "/images/pet-species/cat-home.png",
    badge: "Dành Cho Mèo",
    subtext: "Thức ăn, phụ kiện & chăm sóc",
    themeClass: "theme-cat",
  },
  dog: {
    fallbackImage: "/images/pet-species/dog-home.png",
    badge: "Dành Cho Chó",
    subtext: "Dinh dưỡng & đồ dùng cao cấp",
    themeClass: "theme-dog",
  },
};

export default function PetSpeciesSection({ species = [] }) {
  if (species.length === 0) return null;

  return (
    <section className="homepage-section pet-species-section" aria-labelledby="pet-species-title">
      <h2 id="pet-species-title" className="sr-only">Sản phẩm theo loài thú cưng</h2>

      <div className="pet-species-grid">
        {species.map((item) => {
          const isCat = item.slug?.toLowerCase().includes("cat") || item.name?.toLowerCase().includes("mèo");
          const isDog = item.slug?.toLowerCase().includes("dog") || item.name?.toLowerCase().includes("chó");

          const themeKey = isCat ? "cat" : isDog ? "dog" : "default";
          const theme = SPECIES_THEMES[themeKey] || {
            fallbackImage: "/images/pet-species/dog-home.png",
            badge: "🐾 Danh Mục",
            subtext: "Khám phá sản phẩm chất lượng",
            themeClass: "theme-default",
          };

          const image = item.image
            ? resolveBackendImage(item.image)
            : theme.fallbackImage;

          return (
            <Link
              key={item.id}
              href={`/shop?pet=${encodeURIComponent(item.slug)}`}
              className={`pet-species-card ${theme.themeClass}`}
            >
              {/* Background ambient decorative glow & paw watermark */}
              <div className="pet-species-bg-glow" aria-hidden="true" />
              <div className="pet-species-paw-watermark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C10.5 2 9.5 3.5 10 5C10.5 6.5 12 7 12 7C12 7 13.5 6.5 14 5C14.5 3.5 13.5 2 12 2Z" />
                  <path d="M6.5 4.5C5.2 4.5 4.3 5.8 4.8 7.1C5.3 8.4 6.8 8.8 6.8 8.8C6.8 8.8 8.1 8.3 8.5 7C8.9 5.7 7.8 4.5 6.5 4.5Z" />
                  <path d="M17.5 4.5C16.2 4.5 15.1 5.7 15.5 7C15.9 8.3 17.2 8.8 17.2 8.8C17.2 8.8 18.7 8.4 19.2 7.1C19.7 5.8 18.8 4.5 17.5 4.5Z" />
                  <path d="M3.5 9.5C2.4 9.8 1.8 11.2 2.3 12.4C2.8 13.6 4.2 13.8 4.2 13.8C4.2 13.8 5.3 13.1 5.5 11.8C5.7 10.5 4.6 9.2 3.5 9.5Z" />
                  <path d="M20.5 9.5C19.4 9.2 18.3 10.5 18.5 11.8C18.7 13.1 19.8 13.8 19.8 13.8C19.8 13.8 21.2 13.6 21.7 12.4C22.2 11.2 21.6 9.8 20.5 9.5Z" />
                  <path d="M12 9C9 9 6.5 11.2 6.5 14C6.5 16.5 8.5 20.5 12 21.5C15.5 20.5 17.5 16.5 17.5 14C17.5 11.2 15 9 12 9Z" />
                </svg>
              </div>

              <div className="pet-species-content">
                <div className="pet-species-header">
                  <span className="pet-species-badge">{theme.badge}</span>
                  <span className="pet-species-label">Mua sắm cho</span>
                  <h3 className="pet-species-title">{item.name}</h3>
                  <p className="pet-species-subtext">{theme.subtext}</p>
                </div>

                <div className="pet-species-action">
                  <span className="pet-species-cta">
                    <span>Xem sản phẩm phù hợp</span>
                    <span className="pet-species-cta-icon" aria-hidden="true">
                      <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </span>
                  </span>
                </div>
              </div>

              <div className="pet-species-media-wrapper">
                <div
                  className="pet-species-media"
                  style={{ backgroundColor: item.background_color || undefined }}
                >
                  <Image
                    src={image}
                    alt={item.name || "Pet species"}
                    className="pet-species-image"
                    fill
                    sizes="(max-width: 768px) 45vw, 25vw"
                    priority
                  />
                </div>
              </div>
            </Link>
          );
        })}
      </div>
    </section>
  );
}

