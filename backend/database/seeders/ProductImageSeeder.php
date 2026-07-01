<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'royal-canin-mini-adult' => [
                'royal-canin-mini-adult.jpg',
                // 'royal-canin-mini-adult-2.jpg',
                // 'royal-canin-mini-adult-3.jpg',
            ],
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

        foreach ($images as $productSlug => $imageUrls) {
            $product = Product::where('slug', $productSlug)->firstOrFail();
            $imageUrls = is_array($imageUrls) ? $imageUrls : [$imageUrls];

            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);

            foreach ($imageUrls as $index => $imageUrl) {
                $image = ProductImage::query()
                    ->where('product_id', $product->id)
                    ->whereIn('image_url', [$imageUrl, 'products/'.$imageUrl])
                    ->first() ?? new ProductImage(['product_id' => $product->id]);

                $image->fill([
                    'image_url' => $imageUrl,
                    'is_primary' => $index === 0,
                ])->save();
            }
        }
    }
}
