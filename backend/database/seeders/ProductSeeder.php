<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PetSpecies;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = require database_path('seeders/data/products.php');

        // TODO(catalog): Hồ sơ AI mẫu. Xác nhận lại với nhãn/nhà sản xuất trước khi đưa sản phẩm thật lên bán.
        // Các mã hợp lệ: cat, dog | kitten, puppy, adult, senior, all_life_stages.
        $aiProfiles = [
            'royal-canin-mini-adult' => ['pet_types' => ['dog'], 'life_stages' => ['adult'], 'product_types' => ['dry_food'], 'needs' => ['daily_nutrition']],
            'whiskas-adult-vi-ca-bien' => ['pet_types' => ['cat'], 'life_stages' => ['adult'], 'product_types' => ['dry_food'], 'needs' => ['daily_nutrition']],
            'pate-royal-canin-mini-puppy' => ['pet_types' => ['dog'], 'life_stages' => ['puppy'], 'product_types' => ['wet_food'], 'needs' => ['daily_nutrition']],
            'pate-me-o-ca-ngu' => ['pet_types' => ['cat'], 'life_stages' => ['adult'], 'product_types' => ['wet_food'], 'needs' => ['picky_eater']],
            'pedigree-dentastix' => ['pet_types' => ['dog'], 'life_stages' => ['adult'], 'product_types' => ['treat'], 'needs' => ['dental']],
            'smartheart-creamy-treat' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['adult'], 'product_types' => ['treat'], 'needs' => ['picky_eater']],
            'day-dat-trixie-premium' => ['pet_types' => ['dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => []],
            'bat-an-inox-trixie' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => []],
            'kong-classic' => ['pet_types' => ['dog'], 'life_stages' => ['adult'], 'product_types' => ['toy'], 'needs' => []],
            'bong-trixie-denta-fun' => ['pet_types' => ['dog'], 'life_stages' => ['adult'], 'product_types' => ['toy'], 'needs' => ['dental']],
            'xit-khu-mui-bioline' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => ['indoor']],
            'sua-tam-bioline' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => ['skin_coat']],
            'vong-co-chuong-trixie' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => []],
            'tui-van-chuyen-phi-hanh-gia' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => []],
            'luoc-chai-long-tu-dong-trixie' => ['pet_types' => ['cat', 'dog'], 'life_stages' => ['all_life_stages'], 'product_types' => ['accessory'], 'needs' => ['skin_coat']],
            'can-cau-long-vu-meo' => ['pet_types' => ['cat'], 'life_stages' => ['all_life_stages'], 'product_types' => ['toy'], 'needs' => ['indoor']],
            'xuong-gam-cao-su-trixie' => ['pet_types' => ['dog'], 'life_stages' => ['adult'], 'product_types' => ['toy'], 'needs' => ['dental']],
            'chuot-do-choi-len-cot' => ['pet_types' => ['cat'], 'life_stages' => ['all_life_stages'], 'product_types' => ['toy'], 'needs' => ['indoor']],
        ];

        foreach ($products as $product) {
            $category = Category::where('slug', $product['category_slug'])->firstOrFail();
            $brand = Brand::where('slug', $product['brand_slug'])->firstOrFail();
            preg_match('/<p>(.*?)<\/p>/s', $product['description'], $paragraph);
            $shortDescription = Str::limit(
                trim(strip_tags($paragraph[1] ?? $product['description'])),
                180,
            );

            $profile = $aiProfiles[$product['slug']] ?? null;
            if ($profile === null) {
                throw new \LogicException("Thiếu hồ sơ AI cho {$product['slug']}.");
            }

            $seededProduct = Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'short_description' => $shortDescription,
                    // TODO(catalog): Thay metadata mẫu này bằng nội dung SEO đã được duyệt khi sản phẩm bán chính thức.
                    'focus_keyword' => $product['focus_keyword'] ?? $product['name'],
                    'seo_title' => $product['seo_title'] ?? Str::limit($product['name'].' chính hãng | PetWorld', 70, ''),
                    'seo_description' => $product['seo_description'] ?? Str::limit($shortDescription, 180, ''),
                    'advice_attributes' => $profile,
                    'view_count' => 0,
                    'status' => 'active',
                ],
            );

            $speciesIds = PetSpecies::query()
                ->whereIn('slug', $profile['pet_types'])
                ->pluck('id')
                ->all();

            if (count($speciesIds) !== count($profile['pet_types'])) {
                throw new \LogicException("Thiếu dữ liệu phân loại chó/mèo cho {$product['slug']}.");
            }

            $seededProduct->petSpecies()->sync($speciesIds);
        }

        // Sau khi toàn bộ sản phẩm seed đã được gán sang brand mới,
        // xóa các brand seed cũ không còn sản phẩm tham chiếu.
        Brand::query()
            ->whereNotIn('slug', array_column(BrandSeeder::BRANDS, 'slug'))
            ->whereDoesntHave('products')
            ->delete();
    }
}
