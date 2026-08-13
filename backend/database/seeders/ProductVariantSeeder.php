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
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '1kg', 'Quy cách đóng gói' => 'Bao'], 'price' => 230000, 'sale_price' => 209000, 'quantity' => 50],
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '1kg', 'Quy cách đóng gói' => 'Hộp'], 'price' => 245000, 'sale_price' => 219000, 'quantity' => 25],
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Bao'], 'price' => 620000, 'sale_price' => 579000, 'quantity' => 30],
            ['product_slug' => 'royal-canin-mini-adult', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Hộp'], 'price' => 645000, 'sale_price' => 599000, 'quantity' => 15],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '1.2kg', 'Quy cách đóng gói' => 'Bao'], 'price' => 145000, 'sale_price' => 129000, 'quantity' => 60],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '1.2kg', 'Quy cách đóng gói' => 'Hộp'], 'price' => 159000, 'sale_price' => 139000, 'quantity' => 35],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Bao'], 'price' => 335000, 'sale_price' => 319000, 'quantity' => 30],
            ['product_slug' => 'whiskas-adult-vi-ca-bien', 'options' => ['Trọng lượng' => '3kg', 'Quy cách đóng gói' => 'Hộp'], 'price' => 359000, 'sale_price' => 339000, 'quantity' => 20],
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
            ['product_slug' => 'vong-co-chuong-trixie', 'type' => 'Màu sắc', 'name' => 'Hồng', 'price' => 65000, 'sale_price' => 55000, 'quantity' => 40],
            ['product_slug' => 'vong-co-chuong-trixie', 'type' => 'Màu sắc', 'name' => 'Xanh', 'price' => 65000, 'sale_price' => null, 'quantity' => 30],
            ['product_slug' => 'tui-van-chuyen-phi-hanh-gia', 'type' => 'Màu sắc', 'name' => 'Vàng', 'price' => 350000, 'sale_price' => 299000, 'quantity' => 15],
            ['product_slug' => 'tui-van-chuyen-phi-hanh-gia', 'type' => 'Màu sắc', 'name' => 'Xám', 'price' => 350000, 'sale_price' => null, 'quantity' => 20],
            ['product_slug' => 'luoc-chai-long-tu-dong-trixie', 'type' => 'Kích thước', 'name' => 'Tiêu chuẩn', 'price' => 120000, 'sale_price' => 99000, 'quantity' => 35],
            ['product_slug' => 'can-cau-long-vu-meo', 'type' => 'Màu sắc', 'name' => 'Đa sắc', 'price' => 45000, 'sale_price' => 39000, 'quantity' => 50],
            ['product_slug' => 'xuong-gam-cao-su-trixie', 'type' => 'Kích thước', 'name' => 'M', 'price' => 110000, 'sale_price' => 89000, 'quantity' => 25],
            ['product_slug' => 'chuot-do-choi-len-cot', 'type' => 'Màu sắc', 'name' => 'Xám', 'price' => 35000, 'sale_price' => 29000, 'quantity' => 45],
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
            ['product_slug' => 'luoc-chai-long-tu-dong-trixie', 'value' => 'Lớn', 'price' => 150000, 'sale_price' => 129000, 'quantity' => 20, 'weight_grams' => 150],
            ['product_slug' => 'can-cau-long-vu-meo', 'value' => 'Hồng', 'price' => 45000, 'sale_price' => 39000, 'quantity' => 40, 'weight_grams' => 60],
            ['product_slug' => 'xuong-gam-cao-su-trixie', 'value' => 'L', 'price' => 150000, 'sale_price' => 129000, 'quantity' => 18, 'weight_grams' => 190],
            ['product_slug' => 'chuot-do-choi-len-cot', 'value' => 'Trắng', 'price' => 35000, 'sale_price' => 29000, 'quantity' => 40, 'weight_grams' => 50],
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
