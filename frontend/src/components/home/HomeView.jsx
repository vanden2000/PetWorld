"use client";

import { useHomeData } from "@/lib/swr";
import HeroSlider from "@/components/home/HeroSlider";
import CategorySection from "@/components/home/CategorySection";
import PetSpeciesSection from "@/components/home/PetSpeciesSection";
import TrustBadges from "@/components/home/TrustBadges";
import ProductSection from "@/components/product/ProductSection";
import NewProductsSplit from "@/components/home/NewProductsSplit";
import AccessoriesPromo from "@/components/home/AccessoriesPromo";
import ShopCtaBanner from "@/components/home/ShopCtaBanner";
import BestSellingTabs from "@/components/home/BestSellingTabs";
import TestimonialsSection from "@/components/home/TestimonialsSection";
import BlogSection from "@/components/home/BlogSection";
import BrandSection from "@/components/home/BrandSection";

export default function HomeView({ initialData }) {
  const { data = initialData } = useHomeData(initialData);
  const {
    sections = [],
    banners = [], categories = [], featured_products = [], new_products = [],
    new_accessories = [], products_by_categories = [], latest_blogs = [], brands = [], pet_species = [],
    top_reviews = [], active_vouchers = [],
  } = data || {};
  const newProducts = new_products.length ? new_products : featured_products;
  // Không lấy sản phẩm danh mục khác làm fallback khi danh mục phụ kiện bị tắt.
  const accessoryProducts = new_accessories;

  // Lọc các khối đang hoạt động (is_active) và sắp xếp theo thuộc tính order
  const activeSections = sections && sections.length > 0
    ? [...sections].filter(s => s.is_active !== false).sort((a, b) => a.order - b.order)
    : [
        { key: 'hero_slider' },
        { key: 'category_section' },
        { key: 'featured_products' },
        { key: 'trust_badges' },
        { key: 'pet_species' },
        { key: 'new_products' },
        { key: 'accessories_promo' },
        { key: 'shop_cta_banner' },
        { key: 'sale_products_tabs' },
        { key: 'testimonials' },
        { key: 'latest_blogs' },
        { key: 'brands' },
      ];

  const renderSection = (sec) => {
    const key = sec.key;
    const customTitle = sec.custom_title;

    switch (key) {
      case "hero_slider":
        return <HeroSlider key={key} banners={banners} />;
      case "category_section":
        return <CategorySection key={key} categories={categories} />;
      case "featured_products":
        return <ProductSection key={key} title={customTitle || "Sản Phẩm Bán Chạy"} products={featured_products} columns={5} hasLoadMore showSoldCount showSale={false} initialCount={5} loadMoreStep={10} />;
      case "trust_badges":
        return <TrustBadges key={key} />;
      case "pet_species":
        return <PetSpeciesSection key={key} species={pet_species} />;
      case "new_products":
        return <NewProductsSplit key={key} products={newProducts} />;
      case "accessories_promo":
        return <AccessoriesPromo key={key} products={accessoryProducts} />;
      case "shop_cta_banner":
        return <ShopCtaBanner key={key} vouchers={active_vouchers} reviews={top_reviews} />;
      case "sale_products_tabs":
        return <BestSellingTabs key={key} groups={products_by_categories} title={customTitle || "Sản Phẩm Khuyến Mãi"} />;
      case "testimonials":
        return <TestimonialsSection key={key} reviews={top_reviews} />;
      case "latest_blogs":
        return <BlogSection key={key} blogs={latest_blogs} />;
      case "brands":
        return <BrandSection key={key} brands={brands} />;
      default:
        return null;
    }
  };

  return <main className="main-content homepage-main">
    <div className="homepage-container">
      {activeSections.map((sec) => renderSection(sec))}
    </div>
  </main>;
}

