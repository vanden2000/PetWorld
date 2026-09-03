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
                'legacy_image' => 'banners/petworld-hero.jpg',
                'link' => '/news/bi-quyet-chon-hat-kiem-soat-can-nang-cho-thu-cung',
                'description' => 'Giữ dáng khỏe - Nâng lượng trọn ngày: Bí quyết chọn hạt kiểm soát cân nặng',
            ],
            [
                'image' => 'banners/pet-food-sale.jpg',
                'legacy_image' => 'banners/pet-food-sale.jpg',
                'link' => '/shop?sort=sale',
                'description' => 'Giá tốt - Ưu đãi mỗi ngày: Thức ăn và dinh dưỡng chất lượng cho chó mèo',
            ],
            [
                'image' => 'banners/pet-care.jpg',
                'legacy_image' => 'banners/pet-care.jpg',
                'link' => '/shop',
                'description' => 'Dinh dưỡng chuẩn - Yêu thương trọn vẹn: Khám phá toàn bộ sản phẩm PetWorld',
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
