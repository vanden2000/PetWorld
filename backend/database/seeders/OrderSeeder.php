<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = [
            [
                'email' => 'mai.nguyen@petworld.test',
                'voucher_code' => 'PETWELCOME',
                'shipping_method' => 'Giao hàng nhanh',
                'payment_method' => 'Ví điện tử',
                'order_status' => 'completed',
                'payment_status' => 'paid',
                'note' => 'Giao giờ hành chính.',
                'items' => [
                    ['variant' => 'royal-canin-mini-adult', 'quantity' => 1],
                    ['variant' => 'day-dat-trixie-premium', 'quantity' => 1],
                ],
            ],
            [
                'email' => 'minh.tran@petworld.test',
                'voucher_code' => 'FREESHIP99',
                'shipping_method' => 'Giao hàng tiêu chuẩn',
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'completed',
                'payment_status' => 'paid',
                'note' => null,
                'items' => [
                    ['variant' => 'whiskas-adult-vi-ca-bien', 'quantity' => 2],
                    ['variant' => 'pate-me-o-ca-ngu', 'quantity' => 6],
                ],
            ],
            [
                'email' => 'lan.le@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng tiêu chuẩn',
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'shipping',
                'payment_status' => 'paid',
                'note' => 'Gọi trước khi giao.',
                'items' => [
                    ['variant' => 'smartheart-creamy-treat', 'quantity' => 2],
                    ['variant' => 'sua-tam-bioline', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $user = User::where('email', $orderData['email'])->firstOrFail();
            $address = Address::where('user_id', $user->id)->firstOrFail();
            $shippingMethod = ShippingMethod::where('name', $orderData['shipping_method'])->firstOrFail();
            $paymentMethod = PaymentMethod::where('name', $orderData['payment_method'])->firstOrFail();
            $existingOrder = Order::where('user_id', $user->id)->first();

            $lineTotal = 0;
            $items = collect($orderData['items'])
                ->map(function (array $item) use (&$lineTotal): array {
                    $variant = ProductVariant::with('product')
                        ->whereHas('product', fn ($query) => $query->where('slug', $item['variant']))
                        ->where('status', 'active')
                        ->orderBy('id')
                        ->firstOrFail();

                    $price = $variant->effectivePrice();
                    $lineTotal += $price * $item['quantity'];

                    return [
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name.' - '.$variant->variant_name,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                    ];
                });

            $voucher = $orderData['voucher_code']
                ? Voucher::where('code', $orderData['voucher_code'])->first()
                : null;

            if (! $voucher?->canBeApplied($lineTotal, ignoreOrderId: $existingOrder?->id)) {
                $voucher = null;
            }

            $discountAmount = $voucher ? min((float) $voucher->discount_value, $lineTotal) : 0;
            $shippingFee = (float) $shippingMethod->shipping_fee;
            $totalAmount = max($lineTotal + $shippingFee - $discountAmount, 0);

            // Mỗi tài khoản mẫu chỉ có một đơn seed, nên user_id là khóa ổn định khi đổi nội dung tiếng Việt.
            $order = Order::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'voucher_id' => $voucher?->id,
                    'shipping_method_id' => $shippingMethod->id,
                    'payment_method_id' => $paymentMethod->id,
                    'address_id' => $address->id,
                    'recipient_name' => $address->recipient_name,
                    'recipient_phone' => $address->recipient_phone,
                    'recipient_address' => "{$address->address_line}, {$address->ward}, {$address->district}, {$address->province}",
                    'delivery_area' => $address->province,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $discountAmount,
                    'order_status' => $orderData['order_status'],
                    'total_amount' => $totalAmount,
                    'payment_status' => $orderData['payment_status'],
                    'note' => $orderData['note'],
                ],
            );

            // Đồng bộ đúng cấu hình seed, không giữ lại item đã bị xóa khỏi mảng $orders.
            $variantIds = $items->pluck('product_variant_id');
            $order->items()->whereNotIn('product_variant_id', $variantIds)->delete();

            foreach ($items as $item) {
                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_variant_id' => $item['product_variant_id'],
                    ],
                    [
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ],
                );
            }
        }
    }
}
