<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
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

        foreach ($products as $product) {
            $category = Category::where('slug', $product['category_slug'])->firstOrFail();
            $brand = Brand::where('slug', $product['brand_slug'])->firstOrFail();
            preg_match('/<p>(.*?)<\/p>/s', $product['description'], $paragraph);
            $shortDescription = Str::limit(
                trim(strip_tags($paragraph[1] ?? $product['description'])),
                180,
            );

            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'short_description' => $shortDescription,
                    'view_count' => 0,
                    'status' => 'active',
                ],
            );
        }

        // Sau khi toàn bộ sản phẩm seed đã được gán sang brand mới,
        // xóa các brand seed cũ không còn sản phẩm tham chiếu.
        Brand::query()
            ->whereNotIn('slug', array_column(BrandSeeder::BRANDS, 'slug'))
            ->whereDoesntHave('products')
            ->delete();
    }
}
