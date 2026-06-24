<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'image' => 'banners/petworld-hero.jpg',
                'link' => '/products',
                'description' => 'Khám phá sản phẩm tốt cho thú cưng.',
            ],
            [
                'image' => 'banners/pet-food-sale.jpg',
                'link' => '/products?category=thuc-an-hat',
                'description' => 'Ưu đãi thức ăn hạt dành cho thú cưng.',
            ],
            [
                'image' => 'banners/pet-care.jpg',
                'link' => '/products?category=ve-sinh-va-cham-soc',
                'description' => 'Chăm sóc thú cưng mỗi ngày.',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['image' => $banner['image']],
                $banner,
            );
        }
    }
}
