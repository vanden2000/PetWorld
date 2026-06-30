import { getHomeData } from "@/lib/api";
import HeroSlider from "@/components/home/HeroSlider";
import CategorySection from "@/components/home/CategorySection";
import TrustBadges from "@/components/home/TrustBadges";
import ProductSection from "@/components/product/ProductSection";
import NewProductsSplit from "@/components/home/NewProductsSplit";
import AccessoriesPromo from "@/components/home/AccessoriesPromo";
import ShopCtaBanner from "@/components/home/ShopCtaBanner";
import BestSellingTabs from "@/components/home/BestSellingTabs";
import BlogSection from "@/components/home/BlogSection";
import BrandSection from "@/components/home/BrandSection";

export default async function Homepage() {
  const data = await getHomeData();

  const {
    banners = [],
    categories = [],
    featured_products = [],
    sale_products = [],
    new_products = [],
    new_accessories = [],
    products_by_categories = [],
    latest_blogs = [],
    brands = [],
  } = data;

  // Một số khối cần fallback khi danh sách tương ứng rỗng.
  const newProducts = new_products.length ? new_products : featured_products;
  const accessoryProducts = new_accessories.length ? new_accessories : featured_products;

  return (
    <main className="main-content">
      <div className="homepage-container">
        <HeroSlider banners={banners} />
        <CategorySection categories={categories} />
        <ProductSection title="Sản Phẩm Được Tuyển Chọn" products={featured_products} columns={5} />
        <TrustBadges />
        <NewProductsSplit products={newProducts} />
        <AccessoriesPromo products={accessoryProducts} />
        <ShopCtaBanner />
        <BestSellingTabs groups={products_by_categories} />
        <BlogSection blogs={latest_blogs} />
        <BrandSection brands={brands}/>
      </div>
    </main>
  );
}
