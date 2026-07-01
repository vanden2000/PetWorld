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
                'royal-canin-mini-adult-2.jpg',
                'royal-canin-mini-adult-3.jpg',
                'royal-canin-mini-adult-4.jpg',
            ],
            'whiskas-adult-vi-ca-bien' => [
                'whiskas-adult-vi-ca-bien.jpg',
                'whiskas-adult-vi-ca-bien-2.jpg',
                'whiskas-adult-vi-ca-bien-3.jpg',
                'whiskas-adult-vi-ca-bien-4.jpg',
            ],
            'pate-royal-canin-mini-puppy' => [
                'pate-royal-canin-mini-puppy.jpg',
                'pate-royal-canin-mini-puppy-2.jpg',
                'pate-royal-canin-mini-puppy-3.jpg',
                'pate-royal-canin-mini-puppy-4.jpg',
            ],
            'pate-me-o-ca-ngu' => [
                'pate-me-o-ca-ngu.jpg',
                'pate-me-o-ca-ngu-2.jpg',
                'pate-me-o-ca-ngu-3.jpg',
                'pate-me-o-ca-ngu-4.jpg',
            ],
            'pedigree-dentastix' => [
                'pedigree-dentastix.jpg',
                'pedigree-dentastix-2.jpg',
                'pedigree-dentastix-3.jpg',
                'pedigree-dentastix-4.jpg',
            ],
            'smartheart-creamy-treat' => [
                'smartheart-creamy-treat.jpg',
                'smartheart-creamy-treat-2.jpg',
                'smartheart-creamy-treat-3.jpg',
                'smartheart-creamy-treat-4.jpg',
            ],
            'day-dat-trixie-premium' => [
                'day-dat-trixie-premium.jpg',
                'day-dat-trixie-premium-2.jpg',
                'day-dat-trixie-premium-3.jpg',
                'day-dat-trixie-premium-4.jpg',
            ],
            'bat-an-inox-trixie' => [
                'bat-an-inox-trixie.jpg',
                'bat-an-inox-trixie-2.jpg',
                'bat-an-inox-trixie-3.jpg',
                'bat-an-inox-trixie-4.jpg',
            ],
            'kong-classic' => [
                'kong-classic.jpg',
                'kong-classic-2.jpg',
                'kong-classic-3.jpg',
                'kong-classic-4.jpg',
            ],
            'bong-trixie-denta-fun' => [
                'bong-trixie-denta-fun.jpg',
                'bong-trixie-denta-fun-2.jpg',
                'bong-trixie-denta-fun-3.jpg',
                'bong-trixie-denta-fun-4.jpg',
            ],
            'xit-khu-mui-bioline' => [
                'xit-khu-mui-bioline.jpg',
                'xit-khu-mui-bioline-2.jpg',
                'xit-khu-mui-bioline-3.jpg',
                'xit-khu-mui-bioline-4.jpg',
            ],
            'sua-tam-bioline' => [
                'sua-tam-bioline.jpg',
                'sua-tam-bioline-2.jpg',
                'sua-tam-bioline-3.jpg',
                'sua-tam-bioline-4.jpg',
            ],
        ];

        foreach ($images as $productSlug => $imageUrls) {
            $product = Product::where('slug', $productSlug)->firstOrFail();

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
