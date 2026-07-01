<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;

class CheckoutOptionController extends Controller
{
    /**
     * Phương thức vận chuyển + thanh toán cho trang checkout (lấy từ DB,
     * tránh hard-code phí/tên ở frontend).
     */
    public function __invoke(): JsonResponse
    {
        $shippingMethods = ShippingMethod::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'name', 'shipping_fee'])
            ->map(fn (ShippingMethod $method): array => [
                'id' => $method->id,
                'name' => $method->name,
                'shipping_fee' => (float) $method->shipping_fee,
            ])
            ->all();

        $paymentMethods = PaymentMethod::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn (PaymentMethod $method): array => [
                'id' => $method->id,
                'name' => $method->name,
            ])
            ->all();

        return response()->json([
            'data' => [
                'shipping_methods' => $shippingMethods,
                'payment_methods' => $paymentMethods,
            ],
        ]);
    }
}
