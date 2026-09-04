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
                'pate-royal-canin-mini-puppy-thung.jpg',
                'pate-royal-canin-mini-puppy.jpg',
                'pate-royal-canin-mini-puppy-2.jpg',
                'pate-royal-canin-mini-puppy-3.jpg',
                'pate-royal-canin-mini-puppy-4.jpg',
            ],
            'pate-me-o-ca-ngu' => [
                'pate-me-o-ca-ngu.jpg',
                'pate-me-o-ca-ngu-loc.jpg',
                'pate-me-o-ca-ngu-2.jpg',
                'pate-me-o-ca-ngu-3.jpg',
                'pate-me-o-ca-ngu-4.jpg',
            ],
            'pedigree-dentastix' => [
                'pedigree-dentastix.jpg',
                'pedigree-dentastix-28-thanh.jpg',
                'pedigree-dentastix-2.jpg',
                'pedigree-dentastix-3.jpg',
                'pedigree-dentastix-4.jpg',
            ],
            'smartheart-creamy-treat' => [
                'smartheart-creamy-treat-hop.jpg',
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
                'bong-trixie-denta-fun-do.jpg',
                'bong-trixie-denta-fun.jpg',
                'bong-trixie-denta-fun-2.jpg',
                'bong-trixie-denta-fun-3.jpg',
                'bong-trixie-denta-fun-4.jpg',
            ],
            'xit-khu-mui-bioline' => [
                'xit-khu-mui-bioline-300ml.jpg',
                'xit-khu-mui-bioline.jpg',
                'xit-khu-mui-bioline-2.jpg',
                'xit-khu-mui-bioline-3.jpg',
                'xit-khu-mui-bioline-4.jpg',
            ],
            'sua-tam-bioline' => [
                'sua-tam-bioline.jpg',
                'sua-tam-bioline-500ml.jpg',
                'sua-tam-bioline-2.jpg',
                'sua-tam-bioline-3.jpg',
                'sua-tam-bioline-4.jpg',
            ],
            'vong-co-chuong-trixie' => [
                'vong-co-chuong-trixie-hong.jpg',
                'vong-co-chuong-trixie-xanh.jpg',
                'vong-co-chuong-trixie.jpg',
                'vong-co-chuong-trixie-1.jpg',
                'vong-co-chuong-trixie-2.jpg',
                'vong-co-chuong-trixie-3.jpg',
            ],
            'tui-van-chuyen-phi-hanh-gia' => [
                'tui-van-chuyen-phi-hanh-gia-vang.jpg',
                'tui-van-chuyen-phi-hanh-gia-xam.jpg',
                'tui-van-chuyen-phi-hanh-gia.jpg',
                'tui-van-chuyen-phi-hanh-gia-1.jpg',
                'tui-van-chuyen-phi-hanh-gia-2.jpg',
                'tui-van-chuyen-phi-hanh-gia-3.jpg',
            ],
            'luoc-chai-long-tu-dong-trixie' => [
                'luoc-chai-long-tu-dong-trixie.jpg',
                'luoc-chai-long-tu-dong-trixie-1.jpg',
                'luoc-chai-long-tu-dong-trixie-2.jpg',
                'luoc-chai-long-tu-dong-trixie-3.jpg',
            ],
            'can-cau-long-vu-meo' => [
                'can-cau-long-vu-meo.jpg',
                'can-cau-long-vu-meo-hong.jpg',
                'can-cau-long-vu-meo-1.jpg',
                'can-cau-long-vu-meo-2.jpg',
                'can-cau-long-vu-meo-3.jpg',
            ],
            'xuong-gam-cao-su-trixie' => [
                'xuong-gam-cao-su-trixie.jpg',
                'xuong-gam-cao-su-trixie-1.jpg',
                'xuong-gam-cao-su-trixie-2.jpg',
                'xuong-gam-cao-su-trixie-3.jpg',
            ],
            'chuot-do-choi-len-cot' => [
                'chuot-do-choi-len-cot.jpg',
                'chuot-do-choi-len-cot-trang.jpg',
                'chuot-do-choi-len-cot-1.jpg',
                'chuot-do-choi-len-cot-2.jpg',
                'chuot-do-choi-len-cot-3.jpg',
            ],
        ];

        // Các ảnh từng gán nhầm cho 6 sản phẩm bên dưới. Chỉ xoá đúng các bản ghi này
        // để gallery không còn ảnh trùng/sai sau khi seed lại.
        $legacyImages = [
            'vong-co-chuong-trixie' => ['products/day-dat-trixie-premium.jpg'],
            'tui-van-chuyen-phi-hanh-gia' => ['products/bat-an-inox-trixie-2.jpg'],
            'luoc-chai-long-tu-dong-trixie' => ['products/bat-an-inox-trixie.jpg'],
            'can-cau-long-vu-meo' => ['products/bong-trixie-denta-fun.jpg'],
            'xuong-gam-cao-su-trixie' => ['products/kong-classic.jpg'],
            'chuot-do-choi-len-cot' => ['products/kong-classic-2.jpg'],
        ];

        foreach ($images as $productSlug => $imageUrls) {
            $product = Product::where('slug', $productSlug)->firstOrFail();

            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);

            foreach ($imageUrls as $index => $imageUrl) {
                $imagePath = 'products/'.$imageUrl;
                $image = ProductImage::query()
                    ->where('product_id', $product->id)
                    ->whereIn('image_url', [$imageUrl, 'products/'.$imageUrl, $imagePath])
                    ->first() ?? new ProductImage(['product_id' => $product->id]);

                $image->fill([
                    'image_url' => $imagePath,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                    // TODO(catalog): Có thể thay bằng alt mô tả cụ thể góc chụp/bao bì nếu ảnh được cập nhật.
                    'alt_text' => $product->name . ($index === 0 ? ' - ảnh sản phẩm' : ' - hình ảnh ' . ($index + 1)),
                ])->save();
            }

            ProductImage::where('product_id', $product->id)
                ->whereIn('image_url', $legacyImages[$productSlug] ?? [])
                ->delete();
        }
    }
}
