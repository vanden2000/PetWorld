import Link from "next/link";
import { getProducts } from "@/lib/api";
import ProductCard from "@/components/product/ProductCard";
import ShopSidebar from "@/components/shop/ShopSidebar";
import ShopSort from "@/components/shop/ShopSort";

export const metadata = {
  title: "Cửa Hàng - PetWorld",
};

export default async function ShopPage({ searchParams }) {
  // Next 16: searchParams là promise, phải await trước khi đọc.
  const { category = "", pet = "", brand = "", search, sort = "newest", min_price = "", max_price = "", page } =
    await searchParams;

  const data = await getProducts({ category, pet, brand, search, sort, min_price, max_price, page });

  const {
    title = "Tất cả sản phẩm",
    total = 0,
    products = [],
    pagination = {},
    filters = {},
    sort_options = [],
  } = data;

  const currentPage = pagination.current_page ?? 1;
  const lastPage = pagination.last_page ?? 1;
  const selectedBrands = brand ? brand.split(",").filter(Boolean) : [];

  // Giữ nguyên bộ lọc hiện tại khi đổi trang.
  const pageHref = (targetPage) => {
    const query = new URLSearchParams();
    if (category) query.set("category", category);
    if (pet) query.set("pet", pet);
    if (brand) query.set("brand", brand);
    if (search) query.set("search", search);
    if (sort) query.set("sort", sort);
    if (min_price) query.set("min_price", min_price);
    if (max_price) query.set("max_price", max_price);
    query.set("page", String(targetPage));
    return `/shop?${query.toString()}`;
  };

  return (
    <main className="main-content">
      <div className="homepage-container">
        <nav className="shop-breadcrumb">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">Cửa Hàng</span>
        </nav>

        <div className="shop-layout">
          <ShopSidebar
            categories={filters.categories ?? []}
            petSpecies={filters.pet_species ?? []}
            brands={filters.brands ?? []}
            priceMax={filters.price?.max ?? 2000000}
            selectedCategory={category}
            selectedPet={pet}
            selectedBrands={selectedBrands}
            search={search ?? ""}
            sort={sort}
            minPrice={min_price}
            maxPrice={max_price}
          />

          <div className="shop-main">
            <div className="shop-toolbar">
              <h1 className="shop-result-title">
                {search ? `Kết quả cho “${search}”` : title} <span>({total})</span>
              </h1>
              <ShopSort
                options={sort_options}
                value={sort}
                query={{ category, pet, brand, search, min_price, max_price }}
              />
            </div>

            {products.length > 0 ? (
              <>
                <div className="products-grid-4">
                  {products.map((product) => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>

                {lastPage > 1 && (
                  <div className="shop-pagination">
                    <Link
                      href={pageHref(Math.max(1, currentPage - 1))}
                      className={`shop-page-btn arrow ${currentPage <= 1 ? "disabled" : ""}`}
                      aria-label="Trang trước"
                    >
                      ‹
                    </Link>
                    {Array.from({ length: lastPage }).map((_, index) => {
                      const targetPage = index + 1;
                      return (
                        <Link
                          key={targetPage}
                          href={pageHref(targetPage)}
                          className={`shop-page-btn ${targetPage === currentPage ? "active" : ""}`}
                        >
                          {targetPage}
                        </Link>
                      );
                    })}
                    <Link
                      href={pageHref(Math.min(lastPage, currentPage + 1))}
                      className={`shop-page-btn arrow ${currentPage >= lastPage ? "disabled" : ""}`}
                      aria-label="Trang sau"
                    >
                      ›
                    </Link>
                  </div>
                )}
              </>
            ) : (
              <div className="shop-empty-state">
                <div className="shop-empty-icon">
                  <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    <line x1="8" y1="11" x2="14" y2="11" />
                  </svg>
                </div>
                <h3 className="shop-empty-title">Không tìm thấy sản phẩm phù hợp</h3>
                <p className="shop-empty-sub">
                  Rất tiếc, không có sản phẩm nào khớp với lựa chọn của bạn. Thử tìm kiếm với từ khóa khác hoặc xóa tất cả bộ lọc.
                </p>
                <Link href="/shop" className="shop-empty-reset-btn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <polyline points="1 4 1 10 7 10" />
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                  </svg>
                  <span>Xóa tất cả bộ lọc</span>
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </main>
  );
}
