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
                'image' => 'petworld-hero.jpg',
                'legacy_image' => 'banners/petworld-hero.jpg',
                'link' => '/shop',
                'description' => 'Khám phá sản phẩm tốt cho thú cưng.',
            ],
            [
                'image' => 'pet-food-sale.jpg',
                'legacy_image' => 'banners/pet-food-sale.jpg',
                'link' => '/shop?category=thuc-an-hat',
                'description' => 'Ưu đãi thức ăn hạt dành cho thú cưng.',
            ],
            [
                'image' => 'pet-care.jpg',
                'legacy_image' => 'banners/pet-care.jpg',
                'link' => '/shop?category=ve-sinh-va-cham-soc',
                'description' => 'Chăm sóc thú cưng mỗi ngày.',
            ],
        ];

        foreach ($banners as $banner) {
            // Database chỉ lưu tên file; tìm cả đường dẫn cũ để seed lại không tạo banner trùng.
            $model = Banner::query()
                ->whereIn('image', [$banner['image'], $banner['legacy_image']])
                ->first() ?? new Banner;

            $model->fill([
                'image' => $banner['image'],
                'link' => $banner['link'],
                'description' => $banner['description'],
            ])->save();
        }
    }
}
