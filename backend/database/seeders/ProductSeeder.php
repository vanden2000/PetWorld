<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

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

            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'view_count' => 0,
                    'status' => true,
                ],
            );
        }
    }
}
