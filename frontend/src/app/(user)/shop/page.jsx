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
  const { category = "", brand = "", search, sort = "newest", min_price = "", max_price = "", page } =
    await searchParams;

  const data = await getProducts({ category, brand, search, sort, min_price, max_price, page });

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
            brands={filters.brands ?? []}
            priceMax={filters.price?.max ?? 2000000}
            selectedCategory={category}
            selectedBrands={selectedBrands}
            minPrice={min_price}
            maxPrice={max_price}
          />

          <div className="shop-main">
            <div className="shop-toolbar">
              <h1 className="shop-result-title">
                {title} <span>({total})</span>
              </h1>
              <ShopSort
                options={sort_options}
                value={sort}
                query={{ category, brand, search, min_price, max_price }}
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
              <div className="shop-empty">
                Không tìm thấy sản phẩm nào. Vui lòng bật API Laravel hoặc thử bộ lọc khác.
              </div>
            )}
          </div>
        </div>
      </div>
    </main>
  );
}
