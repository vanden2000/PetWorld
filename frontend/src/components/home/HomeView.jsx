"use client";

import { useHomeData } from "@/lib/swr";
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

export default function HomeView({ initialData }) {
  const { data = initialData } = useHomeData(initialData);
  const {
    banners = [], categories = [], featured_products = [], new_products = [],
    new_accessories = [], products_by_categories = [], latest_blogs = [], brands = [],
  } = data || {};
  const newProducts = new_products.length ? new_products : featured_products;
  // Không lấy sản phẩm danh mục khác làm fallback khi danh mục phụ kiện bị tắt.
  const accessoryProducts = new_accessories;

  return <main className="main-content"><div className="homepage-container">
    <HeroSlider banners={banners} />
    <CategorySection categories={categories} />
    <ProductSection title="Sản Phẩm Bán Chạy" products={featured_products} columns={5} limit={8} isSlider showSoldCount showSale={false} />
    <TrustBadges />
    <NewProductsSplit products={newProducts} />
    <AccessoriesPromo products={accessoryProducts} />
    <ShopCtaBanner />
    <BestSellingTabs groups={products_by_categories} title="Sản Phẩm Khuyến Mãi" />
    <BlogSection blogs={latest_blogs} />
    <BrandSection brands={brands} />
  </div></main>;
}
