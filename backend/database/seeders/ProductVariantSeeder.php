<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantType;
use App\Models\VariantValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = [
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '1kg', 'Quy cách đóng gói' => 'Bao'], 'image' => 'products/royal-canin-mini-adult.jpg', 'price' => 230000, 'sale_price' => 209000, 'quantity' => 50],
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '1kg', 'Quy cách đóng gói' => 'Hộp'], 'image' => 'products/royal-canin-mini-adult-2.jpg', 'price' => 245000, 'sale_price' => 219000, 'quantity' => 25],
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Bao'], 'image' => 'products/royal-canin-mini-adult-3.jpg', 'price' => 620000, 'sale_price' => 579000, 'quantity' => 30],
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Hộp'], 'image' => 'products/royal-canin-mini-adult-4.jpg', 'price' => 645000, 'sale_price' => 599000, 'quantity' => 15],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '1.2kg', 'Quy cách đóng gói' => 'Bao'], 'image' => 'products/whiskas-adult-vi-ca-bien.jpg', 'price' => 145000, 'sale_price' => 129000, 'quantity' => 60],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '1.2kg', 'Quy cách đóng gói' => 'Hộp'], 'image' => 'products/whiskas-adult-vi-ca-bien-2.jpg', 'price' => 159000, 'sale_price' => 139000, 'quantity' => 35],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Bao'], 'image' => 'products/whiskas-adult-vi-ca-bien-3.jpg', 'price' => 335000, 'sale_price' => 319000, 'quantity' => 30],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Hộp'], 'image' => 'products/whiskas-adult-vi-ca-bien-4.jpg', 'price' => 359000, 'sale_price' => 339000, 'quantity' => 20],
            ['product_slug' => 'pate-royal-canin-mini-puppy', 'type' => 'Quy cách đóng gói', 'name' => 'Lon 195g', 'image' => 'products/pate-royal-canin-mini-puppy.jpg', 'price' => 75000, 'sale_price' => null, 'quantity' => 80],
            ['product_slug' => 'pate-royal-canin-mini-puppy', 'type' => 'Quy cách đóng gói', 'name' => 'Thùng 12 lon', 'image' => 'products/pate-royal-canin-mini-puppy-thung.jpg', 'price' => 850000, 'sale_price' => 799000, 'quantity' => 20],
            ['product_slug' => 'pate-me-o-ca-ngu', 'type' => 'Quy cách đóng gói', 'name' => 'Túi 80g', 'image' => 'products/pate-me-o-ca-ngu.jpg', 'price' => 18000, 'sale_price' => 15000, 'quantity' => 100],
            ['product_slug' => 'pate-me-o-ca-ngu', 'type' => 'Quy cách đóng gói', 'name' => 'Lốc 12 túi', 'image' => 'products/pate-me-o-ca-ngu-loc.jpg', 'price' => 195000, 'sale_price' => 179000, 'quantity' => 35],
            ['product_slug' => 'pedigree-dentastix', 'type' => 'Quy cách đóng gói', 'name' => 'Gói 7 thanh', 'image' => 'products/pedigree-dentastix.jpg', 'price' => 72000, 'sale_price' => 65000, 'quantity' => 50],
            ['product_slug' => 'pedigree-dentastix', 'type' => 'Quy cách đóng gói', 'name' => 'Gói 28 thanh', 'image' => 'products/pedigree-dentastix-28-thanh.jpg', 'price' => 245000, 'sale_price' => 225000, 'quantity' => 25],
            ['product_slug' => 'smartheart-creamy-treat', 'type' => 'Quy cách đóng gói', 'name' => 'Gói 4 thanh', 'image' => 'products/smartheart-creamy-treat.jpg', 'price' => 45000, 'sale_price' => null, 'quantity' => 70],
            ['product_slug' => 'smartheart-creamy-treat', 'type' => 'Quy cách đóng gói', 'name' => 'Hộp 20 thanh', 'image' => 'products/smartheart-creamy-treat-hop.jpg', 'price' => 210000, 'sale_price' => 189000, 'quantity' => 30],
            ['product_slug' => 'day-dat-trixie-premium', 'type' => 'Kích thước', 'name' => 'S', 'image' => 'products/day-dat-trixie-premium.jpg', 'price' => 180000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'day-dat-trixie-premium', 'type' => 'Kích thước', 'name' => 'M', 'image' => 'products/day-dat-trixie-premium.jpg', 'price' => 220000, 'sale_price' => 199000, 'quantity' => 20],
            ['product_slug' => 'bat-an-inox-trixie', 'type' => 'Kích thước', 'name' => 'S', 'image' => 'products/bat-an-inox-trixie.jpg', 'price' => 90000, 'sale_price' => null, 'quantity' => 25],
            ['product_slug' => 'bat-an-inox-trixie', 'type' => 'Kích thước', 'name' => 'M', 'image' => 'products/bat-an-inox-trixie.jpg', 'price' => 130000, 'sale_price' => 119000, 'quantity' => 25],
            ['product_slug' => 'kong-classic', 'type' => 'Kích thước', 'name' => 'S', 'image' => 'products/kong-classic.jpg', 'price' => 210000, 'sale_price' => 189000, 'quantity' => 20],
            ['product_slug' => 'kong-classic', 'type' => 'Kích thước', 'name' => 'M', 'image' => 'products/kong-classic-2.jpg', 'price' => 290000, 'sale_price' => 259000, 'quantity' => 15],
            ['product_slug' => 'bong-trixie-denta-fun', 'type' => 'Màu sắc', 'name' => 'Đỏ', 'image' => 'products/bong-trixie-denta-fun-do.jpg', 'price' => 140000, 'sale_price' => 125000, 'quantity' => 20],
            ['product_slug' => 'bong-trixie-denta-fun', 'type' => 'Màu sắc', 'name' => 'Xanh', 'image' => 'products/bong-trixie-denta-fun.jpg', 'price' => 140000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'xit-khu-mui-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 300ml', 'image' => 'products/xit-khu-mui-bioline-300ml.jpg', 'price' => 115000, 'sale_price' => 99000, 'quantity' => 30],
            ['product_slug' => 'xit-khu-mui-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 500ml', 'image' => 'products/xit-khu-mui-bioline.jpg', 'price' => 165000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'sua-tam-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 300ml', 'image' => 'products/sua-tam-bioline.jpg', 'price' => 135000, 'sale_price' => 119000, 'quantity' => 30],
            ['product_slug' => 'sua-tam-bioline', 'type' => 'Quy cách đóng gói', 'name' => 'Chai 500ml', 'image' => 'products/sua-tam-bioline-500ml.jpg', 'price' => 195000, 'sale_price' => 179000, 'quantity' => 20],
            ['product_slug' => 'vong-co-chuong-trixie', 'type' => 'Màu sắc', 'name' => 'Hồng', 'image' => 'products/vong-co-chuong-trixie-hong.jpg', 'price' => 65000, 'sale_price' => 55000, 'quantity' => 40],
            ['product_slug' => 'vong-co-chuong-trixie', 'type' => 'Màu sắc', 'name' => 'Xanh', 'image' => 'products/vong-co-chuong-trixie-xanh.jpg', 'price' => 65000, 'sale_price' => null, 'quantity' => 30],
            ['product_slug' => 'tui-van-chuyen-phi-hanh-gia', 'type' => 'Màu sắc', 'name' => 'Vàng', 'image' => 'products/tui-van-chuyen-phi-hanh-gia-vang.jpg', 'price' => 350000, 'sale_price' => 299000, 'quantity' => 15],
            ['product_slug' => 'tui-van-chuyen-phi-hanh-gia', 'type' => 'Màu sắc', 'name' => 'Xám', 'image' => 'products/tui-van-chuyen-phi-hanh-gia-xam.jpg', 'price' => 350000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'luoc-chai-long-tu-dong-trixie', 'type' => 'Kích thước', 'name' => 'Tiêu chuẩn', 'image' => 'products/luoc-chai-long-tu-dong-trixie.jpg', 'price' => 120000, 'sale_price' => 99000, 'quantity' => 35],
            ['product_slug' => 'can-cau-long-vu-meo', 'type' => 'Màu sắc', 'name' => 'Đa sắc', 'image' => 'products/can-cau-long-vu-meo.jpg', 'price' => 45000, 'sale_price' => 39000, 'quantity' => 50],
            ['product_slug' => 'xuong-gam-cao-su-trixie', 'type' => 'Kích thước', 'name' => 'M', 'image' => 'products/xuong-gam-cao-su-trixie.jpg', 'price' => 110000, 'sale_price' => 89000, 'quantity' => 25],
            ['product_slug' => 'chuot-do-choi-len-cot', 'type' => 'Màu sắc', 'name' => 'Xám', 'image' => 'products/chuot-do-choi-len-cot.jpg', 'price' => 35000, 'sale_price' => 29000, 'quantity' => 45],
        ];

        // TODO(catalog): Trọng lượng vận chuyển (gram) là số ước tính gồm bao bì.
        // Cần thay bằng số cân thực tế trước khi dùng dữ liệu này cho GHN/GHTK.
        $shippingWeights = [
            'royal-canin-mini-adult' => [1050, 1100, 3100, 3150],
            'whiskas-adult-vi-ca-bien' => [1250, 1300, 3100, 3150],
            'pate-royal-canin-mini-puppy' => [230, 2500],
            'pate-me-o-ca-ngu' => [100, 1200],
            'pedigree-dentastix' => [120, 420],
            'smartheart-creamy-treat' => [80, 450],
            'day-dat-trixie-premium' => [180, 230],
            'bat-an-inox-trixie' => [250, 350],
            'kong-classic' => [130, 200],
            'bong-trixie-denta-fun' => [100, 100],
            'xit-khu-mui-bioline' => [350, 550],
            'sua-tam-bioline' => [350, 550],
            'vong-co-chuong-trixie' => [80, 80],
            'tui-van-chuyen-phi-hanh-gia' => [1200, 1200],
            'luoc-chai-long-tu-dong-trixie' => [120],
            'can-cau-long-vu-meo' => [60],
            'xuong-gam-cao-su-trixie' => [140],
            'chuot-do-choi-len-cot' => [50],
        ];
        $variantIndexes = [];

        foreach ($variants as $variant) {
            $product = Product::where('slug', $variant['product_slug'])->firstOrFail();
            $index = $variantIndexes[$variant['product_slug']] ?? 0;
            $weightGrams = $shippingWeights[$variant['product_slug']][$index] ?? null;
            if ($weightGrams === null || $weightGrams <= 0) {
                throw new \LogicException("Thiếu trọng lượng vận chuyển cho {$variant['product_slug']}.");
            }
            $variantIndexes[$variant['product_slug']] = $index + 1;
            $options = $variant['options'] ?? [$variant['type'] => $variant['name']];
            $sku = Str::upper(Str::slug(
                $variant['product_slug'].'-'.implode('-', array_values($options)),
            ));

            $productVariant = ProductVariant::where('sku', $sku)->first();

            $productVariant ??= ProductVariant::firstOrNew(['sku' => $sku]);
            $productVariant->fill([
                'product_id' => $product->id,
                'sku' => $sku,
                'image' => $variant['image'] ?? null,
                'price' => $variant['price'],
                'sale_price' => $variant['sale_price'],
                'quantity' => $variant['quantity'],
                'weight_grams' => $weightGrams,
                'status' => 'active',
            ])->save();

            $valueIds = collect($options)
                ->map(function (string $value, string $typeName): int {
                    $type = VariantType::where('name', $typeName)->firstOrFail();
                    $variantValue = VariantValue::firstOrCreate(
                        ['variant_type_id' => $type->id, 'value' => $value],
                    );

                    return $variantValue->id;
                })
                ->all();

            $productVariant->syncVariantValues($valueIds);
        }

        // Bốn sản phẩm này trước đây chỉ có một biến thể. Bổ sung biến thể thứ hai
        // để catalog mẫu luôn đạt chuẩn 2–5 biến thể/sản phẩm.
        // TODO(catalog): Rà lại tên biến thể, giá, tồn kho và trọng lượng khi có hàng thật.
        $supplementalVariants = [
            ['product_slug' => 'luoc-chai-long-tu-dong-trixie', 'value' => 'Lớn', 'image' => 'products/luoc-chai-long-tu-dong-trixie-1.jpg', 'price' => 150000, 'sale_price' => 129000, 'quantity' => 20, 'weight_grams' => 150],
            ['product_slug' => 'can-cau-long-vu-meo', 'value' => 'Hồng', 'image' => 'products/can-cau-long-vu-meo-hong.jpg', 'price' => 45000, 'sale_price' => 39000, 'quantity' => 40, 'weight_grams' => 60],
            ['product_slug' => 'xuong-gam-cao-su-trixie', 'value' => 'L', 'image' => 'products/xuong-gam-cao-su-trixie-1.jpg', 'price' => 150000, 'sale_price' => 129000, 'quantity' => 18, 'weight_grams' => 190],
            ['product_slug' => 'chuot-do-choi-len-cot', 'value' => 'Trắng', 'image' => 'products/chuot-do-choi-len-cot-trang.jpg', 'price' => 35000, 'sale_price' => 29000, 'quantity' => 40, 'weight_grams' => 50],
        ];

        foreach ($supplementalVariants as $variant) {
            $product = Product::where('slug', $variant['product_slug'])->firstOrFail();
            $typeName = $product->variants()
                ->with('variantValues.variantType')
                ->first()?->variantValues->first()?->variantType?->name;

            if ($typeName === null) {
                throw new \LogicException("Không tìm thấy loại biến thể cho {$variant['product_slug']}.");
            }

            $sku = Str::upper(Str::slug($variant['product_slug'].'-'.$variant['value']));
            $productVariant = ProductVariant::firstOrNew(['sku' => $sku]);
            $productVariant->fill([
                'product_id' => $product->id,
                'sku' => $sku,
                'image' => $variant['image'] ?? null,
                'price' => $variant['price'],
                'sale_price' => $variant['sale_price'],
                'quantity' => $variant['quantity'],
                'weight_grams' => $variant['weight_grams'],
                'status' => 'active',
            ])->save();

            $type = VariantType::where('name', $typeName)->firstOrFail();
            $value = VariantValue::firstOrCreate(['variant_type_id' => $type->id, 'value' => $variant['value']]);
            $productVariant->syncVariantValues([$value->id]);
        }

        $invalidProduct = Product::query()
            ->whereIn('slug', array_unique(array_column($variants, 'product_slug')))
            ->withCount('variants')
            ->get()
            ->first(fn (Product $product): bool => $product->variants_count < 2 || $product->variants_count > 5);

        if ($invalidProduct !== null) {
            throw new \LogicException("{$invalidProduct->slug} phải có từ 2 đến 5 biến thể.");
        }
    }
}
