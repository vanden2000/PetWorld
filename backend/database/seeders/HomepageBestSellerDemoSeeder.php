<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates demo-only completed orders and approved reviews for the homepage.
 *
 * Run with: php artisan db:seed --class=HomepageBestSellerDemoSeeder
 */
class HomepageBestSellerDemoSeeder extends Seeder
{
    private const ORDER_PREFIX = 'DEMO-HOME-';

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->removePreviousDemoData();

            $this->ensureExtraProducts();

            $products = DB::table('products')
                ->where('status', 'active')
                ->orderBy('id')
                ->limit(20)
                ->get(['id', 'name']);

            if ($products->count() !== 20) {
                throw new \RuntimeException('Không thể chuẩn bị đủ 20 sản phẩm demo.');
            }

            $demoCustomers = $this->ensureDemoCustomers();
            $shippingMethod = DB::table('shipping_methods')->where('status', 'active')->first();
            $paymentMethod = DB::table('payment_methods')->where('status', 'active')->first();

            if ($shippingMethod === null || $paymentMethod === null) {
                throw new \RuntimeException('Cần có phương thức vận chuyển và thanh toán đang hoạt động.');
            }

            // Giảm dần để danh sách "bán chạy" nhìn tự nhiên và luôn vượt ngưỡng > 10 của API.
            $soldTargets = [328, 295, 267, 241, 226, 213, 198, 185, 173, 162, 151, 143, 137, 129, 118, 106, 97, 88, 76, 68];
            $ratings = [[5, 5, 4], [5, 4, 5], [5, 5, 5], [4, 5, 5]];
            $comments = [
                'Sản phẩm đúng mô tả, bé nhà mình dùng rất hợp.',
                'Đóng gói cẩn thận, giao nhanh. Sẽ tiếp tục ủng hộ.',
                'Chất lượng tốt, giá hợp lý và thú cưng rất thích.',
            ];

            foreach ($products->values() as $index => $product) {
                $variant = DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->first(['id', 'price', 'sale_price', 'weight_grams']);

                if ($variant === null) {
                    throw new \RuntimeException("Sản phẩm #{$product->id} chưa có biến thể active.");
                }

                $target = $soldTargets[$index];
                $quantities = [intdiv($target, 3), intdiv($target, 3), $target - (2 * intdiv($target, 3))];
                $price = $variant->sale_price !== null && (float) $variant->sale_price > 0
                    ? (float) $variant->sale_price
                    : (float) $variant->price;

                foreach ($quantities as $reviewIndex => $quantity) {
                    $customer = $demoCustomers[($index + $reviewIndex) % count($demoCustomers)];
                    $now = now()->subDays(($index * 2) + $reviewIndex + 1);
                    $orderId = DB::table('orders')->insertGetId([
                        'payment_code' => self::ORDER_PREFIX.sprintf('%02d-%d', $index + 1, $reviewIndex + 1),
                        'shipping_method_id' => $shippingMethod->id,
                        'shipping_method_code' => $shippingMethod->code,
                        'payment_method_id' => $paymentMethod->id,
                        'address_id' => $customer['address_id'],
                        'user_id' => $customer['user_id'],
                        'recipient_name' => $customer['name'],
                        'recipient_phone' => $customer['phone'],
                        'recipient_address' => $customer['address'],
                        'delivery_area' => 'Hồ Chí Minh',
                        'shipping_fee' => 0,
                        'shipping_weight_grams' => (int) $variant->weight_grams * $quantity,
                        'shipping_fee_original' => 0,
                        'shipping_discount' => 0,
                        'discount_amount' => 0,
                        'order_status' => 'completed',
                        'total_amount' => $price * $quantity,
                        'payment_status' => 'paid',
                        'note' => 'Dữ liệu demo trang chủ.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $orderItemId = DB::table('order_items')->insertGetId([
                        'order_id' => $orderId,
                        'product_variant_id' => $variant->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('reviews')->insert([
                        'user_id' => $customer['user_id'],
                        'order_item_id' => $orderItemId,
                        'rating' => $ratings[$index % count($ratings)][$reviewIndex],
                        'comment' => $comments[$reviewIndex],
                        'status' => 'approved',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });

        Cache::forget('api.home.sections.v1');
        $this->command?->info('Đã tạo 20 sản phẩm bán chạy demo, 60 đơn hoàn tất và 60 đánh giá được duyệt.');
    }

    private function removePreviousDemoData(): void
    {
        $orderIds = DB::table('orders')
            ->where('payment_code', 'like', self::ORDER_PREFIX.'%')
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return;
        }

        $itemIds = DB::table('order_items')->whereIn('order_id', $orderIds)->pluck('id');
        DB::table('reviews')->whereIn('order_item_id', $itemIds)->delete();
        DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
        DB::table('orders')->whereIn('id', $orderIds)->delete();
    }

    private function ensureExtraProducts(): void
    {
        $brandId = DB::table('brands')->where('status', 'active')->value('id');

        foreach ([
            ['name' => 'Cát vệ sinh mèo hương Lavender', 'slug' => 'cat-ve-sinh-meo-lavender-demo', 'category_slug' => 've-sinh-va-cham-soc', 'price' => 145000, 'sale_price' => 119000, 'image' => 'products/pate-me-o-ca-ngu.jpg'],
            ['name' => 'Bàn cào móng cho mèo', 'slug' => 'ban-cao-mong-cho-meo-demo', 'category_slug' => 'do-choi', 'price' => 189000, 'sale_price' => 149000, 'image' => 'products/kong-classic.jpg'],
        ] as $item) {
            $categoryId = DB::table('categories')
                ->where('slug', $item['category_slug'])
                ->where('status', 'active')
                ->value('id');

            if ($categoryId === null) {
                throw new \RuntimeException("Không tìm thấy danh mục demo {$item['category_slug']}.");
            }

            $productId = DB::table('products')->where('slug', $item['slug'])->value('id');

            if ($productId === null) {
                $productId = DB::table('products')->insertGetId([
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'description' => 'Sản phẩm demo dùng để hiển thị trang chủ.',
                    'short_description' => 'Sản phẩm demo trang chủ.',
                    'view_count' => 250,
                    'status' => 'active',
                ]);
            } else {
                DB::table('products')->where('id', $productId)->update([
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'status' => 'active',
                ]);
            }

            $variantId = DB::table('product_variants')->where('product_id', $productId)->value('id');
            if ($variantId === null) {
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'sku' => 'DEMO-HOME-'.$productId,
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'quantity' => 120,
                    'weight_grams' => 500,
                    'status' => 'active',
                ]);
            } else {
                DB::table('product_variants')->where('id', $variantId)->update([
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'status' => 'active',
                ]);
            }

            if (!DB::table('images')->where('product_id', $productId)->where('is_primary', true)->exists()) {
                DB::table('images')->insert([
                    'product_id' => $productId,
                    'image_url' => $item['image'],
                    'sort_order' => 0,
                    'is_primary' => true,
                    'alt_text' => $item['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /** @return array<int, array{user_id: int, address_id: int, name: string, phone: string, address: string}> */
    private function ensureDemoCustomers(): array
    {
        $customers = [];

        for ($number = 1; $number <= 5; $number++) {
            $email = "demo.home.customer{$number}@petworld.test";
            $name = "Khách hàng demo {$number}";
            $phone = '09090000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $userId = DB::table('users')->where('email', $email)->value('id');

            if ($userId === null) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make('demo-homepage-only'),
                    'role' => 'user',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $addressId = DB::table('addresses')->where('user_id', $userId)->value('id');
            if ($addressId === null) {
                $addressId = DB::table('addresses')->insertGetId([
                    'user_id' => $userId,
                    'recipient_name' => $name,
                    'recipient_phone' => $phone,
                    'address_line' => '123 Đường Demo',
                    'ward' => 'Phường Bến Nghé',
                    'district' => 'Quận 1',
                    'province' => 'Hồ Chí Minh',
                    'is_default' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $customers[] = [
                'user_id' => $userId,
                'address_id' => $addressId,
                'name' => $name,
                'phone' => $phone,
                'address' => '123 Đường Demo, Phường Bến Nghé, Quận 1, Hồ Chí Minh',
            ];
        }

        return $customers;
    }
}
