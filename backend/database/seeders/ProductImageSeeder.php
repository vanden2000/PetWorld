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
            'royal-canin-mini-adult' => 'products/royal-canin-mini-adult.jpg',
            'whiskas-adult-vi-ca-bien' => 'products/whiskas-adult-vi-ca-bien.jpg',
            'pate-royal-canin-mini-puppy' => 'products/pate-royal-canin-mini-puppy.jpg',
            'pate-me-o-ca-ngu' => 'products/pate-me-o-ca-ngu.jpg',
            'pedigree-dentastix' => 'products/pedigree-dentastix.jpg',
            'smartheart-creamy-treat' => 'products/smartheart-creamy-treat.jpg',
            'day-dat-trixie-premium' => 'products/day-dat-trixie-premium.jpg',
            'bat-an-inox-trixie' => 'products/bat-an-inox-trixie.jpg',
            'kong-classic' => 'products/kong-classic.jpg',
            'bong-trixie-denta-fun' => 'products/bong-trixie-denta-fun.jpg',
            'xit-khu-mui-bioline' => 'products/xit-khu-mui-bioline.jpg',
            'sua-tam-bioline' => 'products/sua-tam-bioline.jpg',
        ];

        foreach ($images as $productSlug => $imageUrl) {
            $product = Product::where('slug', $productSlug)->firstOrFail();

            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'image_url' => $imageUrl,
                ],
                ['is_primary' => true],
            );
        }
    }
}
