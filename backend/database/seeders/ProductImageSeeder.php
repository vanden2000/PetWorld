<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = [
            'royal-canin-mini-adult' => 'royal-canin-mini-adult.jpg',
            'whiskas-adult-vi-ca-bien' => 'whiskas-adult-vi-ca-bien.jpg',
            'pate-royal-canin-mini-puppy' => 'pate-royal-canin-mini-puppy.jpg',
            'pate-me-o-ca-ngu' => 'pate-me-o-ca-ngu.jpg',
            'pedigree-dentastix' => 'pedigree-dentastix.jpg',
            'smartheart-creamy-treat' => 'smartheart-creamy-treat.jpg',
            'day-dat-trixie-premium' => 'day-dat-trixie-premium.jpg',
            'bat-an-inox-trixie' => 'bat-an-inox-trixie.jpg',
            'kong-classic' => 'kong-classic.jpg',
            'bong-trixie-denta-fun' => 'bong-trixie-denta-fun.jpg',
            'xit-khu-mui-bioline' => 'xit-khu-mui-bioline.jpg',
            'sua-tam-bioline' => 'sua-tam-bioline.jpg',
        ];

        foreach ($images as $productSlug => $imageUrl) {
            $product = Product::where('slug', $productSlug)->firstOrFail();

            // Database chỉ lưu tên file; tìm cả đường dẫn cũ để seed lại không tạo ảnh trùng.
            $image = ProductImage::query()
                ->where('product_id', $product->id)
                ->whereIn('image_url', [$imageUrl, 'products/'.$imageUrl])
                ->first() ?? new ProductImage(['product_id' => $product->id]);

            $image->fill([
                'image_url' => $imageUrl,
                'is_primary' => true,
            ])->save();
        }
    }
}
