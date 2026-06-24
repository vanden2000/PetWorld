<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantType;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = [
            ['product_slug' => 'royal-canin-mini-adult', 'type' => 'Trọng lượng', 'name' => '1kg', 'price' => 230000, 'sale_price' => 209000, 'quantity' => 50],
            ['product_slug' => 'royal-canin-mini-adult', 'type' => 'Trọng lượng', 'name' => '3kg', 'price' => 620000, 'sale_price' => 579000, 'quantity' => 30],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'type' => 'Trọng lượng', 'name' => '1.2kg', 'price' => 145000, 'sale_price' => 129000, 'quantity' => 60],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'type' => 'Trọng lượng', 'name' => '3kg', 'price' => 335000, 'sale_price' => 319000, 'quantity' => 30],
            ['product_slug' => 'pate-royal-canin-mini-puppy', 'type' => 'Quy cách đóng gói', 'name' => 'Lon 195g', 'price' => 75000, 'sale_price' => null, 'quantity' => 80],
            ['product_slug' => 'pate-royal-canin-mini-puppy', 'type' => 'Quy cách đóng gói', 'name' => 'Thùng 12 lon', 'price' => 850000, 'sale_price' => 799000, 'quantity' => 20],
            ['product_slug' => 'pate-me-o-ca-ngu', 'type' => 'Quy cách đóng gói', 'name' => 'Túi 80g', 'price' => 18000, 'sale_price' => 15000, 'quantity' => 100],
            ['product_slug' => 'pate-me-o-ca-ngu', 'type' => 'Quy cách đóng gói', 'name' => 'Lốc 12 túi', 'price' => 195000, 'sale_price' => 179000, 'quantity' => 35],
            ['product_slug' => 'pedigree-dentastix', 'type' => 'Quy cách đóng gói', 'name' => 'Gói 7 thanh', 'price' => 72000, 'sale_price' => 65000, 'quantity' => 50],
            ['product_slug' => 'pedigree-dentastix', 'type' => 'Quy cách đóng gói', 'name' => 'Gói 28 thanh', 'price' => 245000, 'sale_price' => 225000, 'quantity' => 25],
            ['product_slug' => 'smartheart-creamy-treat', 'type' => 'Quy cách đóng gói', 'name' => 'Gói 4 thanh', 'price' => 45000, 'sale_price' => null, 'quantity' => 70],
            ['product_slug' => 'smartheart-creamy-treat', 'type' => 'Quy cách đóng gói', 'name' => 'Hộp 20 thanh', 'price' => 210000, 'sale_price' => 189000, 'quantity' => 30],
            ['product_slug' => 'day-dat-trixie-premium', 'type' => 'Kích thước', 'name' => 'S', 'price' => 180000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'day-dat-trixie-premium', 'type' => 'Kích thước', 'name' => 'M', 'price' => 220000, 'sale_price' => 199000, 'quantity' => 20],
            ['product_slug' => 'bat-an-inox-trixie', 'type' => 'Kích thước', 'name' => 'S', 'price' => 90000, 'sale_price' => null, 'quantity' => 25],
            ['product_slug' => 'bat-an-inox-trixie', 'type' => 'Kích thước', 'name' => 'M', 'price' => 130000, 'sale_price' => 119000, 'quantity' => 25],
            ['product_slug' => 'kong-classic', 'type' => 'Kích thước', 'name' => 'S', 'price' => 210000, 'sale_price' => 189000, 'quantity' => 20],
            ['product_slug' => 'kong-classic', 'type' => 'Kích thước', 'name' => 'M', 'price' => 290000, 'sale_price' => 259000, 'quantity' => 15],
            ['product_slug' => 'bong-trixie-denta-fun', 'type' => 'Màu sắc', 'name' => 'Đỏ', 'price' => 140000, 'sale_price' => 125000, 'quantity' => 20],
            ['product_slug' => 'bong-trixie-denta-fun', 'type' => 'Màu sắc', 'name' => 'Xanh', 'price' => 140000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'xit-khu-mui-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 300ml', 'price' => 115000, 'sale_price' => 99000, 'quantity' => 30],
            ['product_slug' => 'xit-khu-mui-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 500ml', 'price' => 165000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'sua-tam-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 300ml', 'price' => 135000, 'sale_price' => 119000, 'quantity' => 30],
            ['product_slug' => 'sua-tam-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 500ml', 'price' => 195000, 'sale_price' => 179000, 'quantity' => 20],
        ];

        foreach ($variants as $variant) {
            $product = Product::where('slug', $variant['product_slug'])->firstOrFail();
            $variantType = VariantType::where('name', $variant['type'])->firstOrFail();

            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_type_id' => $variantType->id,
                    'variant_name' => $variant['name'],
                ],
                [
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'],
                    'quantity' => $variant['quantity'],
                    'status' => true,
                ],
            );
        }
    }
}
