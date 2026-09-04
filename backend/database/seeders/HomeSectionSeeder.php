<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'key' => 'hero_slider',
                'name' => 'Banner & Slider Chính',
                'custom_title' => null,
                'order' => 1,
                'is_active' => true,
                'limit' => null,
            ],
            [
                'key' => 'category_section',
                'name' => 'Danh Mục Loại Sản Phẩm',
                'custom_title' => null,
                'order' => 2,
                'is_active' => true,
                'limit' => null,
            ],
            [
                'key' => 'featured_products',
                'name' => 'Sản Phẩm Bán Chạy',
                'custom_title' => 'Sản Phẩm Bán Chạy',
                'order' => 3,
                'is_active' => true,
                'limit' => 15,
            ],
            [
                'key' => 'trust_badges',
                'name' => 'Cam Kết & Uy Tín Store',
                'custom_title' => null,
                'order' => 4,
                'is_active' => true,
                'limit' => null,
            ],
            [
                'key' => 'pet_species',
                'name' => 'Danh Mục Loài Thú Cưng',
                'custom_title' => null,
                'order' => 5,
                'is_active' => true,
                'limit' => null,
            ],
            [
                'key' => 'new_products',
                'name' => 'Sản Phẩm Mới Nhất',
                'custom_title' => 'Sản Phẩm Mới',
                'order' => 6,
                'is_active' => true,
                'limit' => 8,
            ],
            [
                'key' => 'accessories_promo',
                'name' => 'Phụ Kiện Thú Cưng Khuyến Mãi',
                'custom_title' => 'Phụ Kiện Nổi Bật',
                'order' => 7,
                'is_active' => true,
                'limit' => 20,
            ],
            [
                'key' => 'shop_cta_banner',
                'name' => 'Banner Kêu Gọi Mua Sắm (CTA)',
                'custom_title' => null,
                'order' => 8,
                'is_active' => true,
                'limit' => null,
            ],
            [
                'key' => 'sale_products_tabs',
                'name' => 'Sản Phẩm Khuyến Mãi Theo Tab',
                'custom_title' => 'Sản Phẩm Khuyến Mãi',
                'order' => 9,
                'is_active' => true,
                'limit' => 8,
            ],
            [
                'key' => 'testimonials',
                'name' => 'Đánh Giá Khách Hàng (Reviews)',
                'custom_title' => 'Khách Hàng Nói Gì Về PetWorld',
                'order' => 10,
                'is_active' => true,
                'limit' => 6,
            ],
            [
                'key' => 'latest_blogs',
                'name' => 'Bài Viết & Cẩm Nang Thú Cưng',
                'custom_title' => 'Kiến Thức & Tin Tức Thú Cưng',
                'order' => 11,
                'is_active' => true,
                'limit' => 3,
            ],
            [
                'key' => 'brands',
                'name' => 'Thương Hiệu Nổi Bật',
                'custom_title' => null,
                'order' => 12,
                'is_active' => true,
                'limit' => 12,
            ],
        ];

        foreach ($sections as $section) {
            HomeSection::updateOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}
