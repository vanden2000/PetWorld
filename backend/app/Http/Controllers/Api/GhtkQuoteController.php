<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\GhtkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GhtkQuoteController extends Controller
{
    public function __invoke(Request $request, GhtkService $ghtk): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $address = $request->user()->addresses()
            ->where('status', 'active')
            ->findOrFail($data['address_id']);

        $quantities = [];
        foreach ($data['items'] as $item) {
            $quantities[$item['variant_id']] = ($quantities[$item['variant_id']] ?? 0) + $item['quantity'];
        }

        $variants = ProductVariant::query()
            ->with('product:id,status')
            ->whereIn('id', array_keys($quantities))
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $weightGrams = 0;
        $orderValue = 0.0;
        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            if ($variant === null || $variant->product?->status !== 'active') {
                throw ValidationException::withMessages([
                    'items' => ['Một sản phẩm trong giỏ không còn được bán.'],
                ]);
            }

            if ((int) $variant->weight_grams <= 0) {
                throw ValidationException::withMessages([
                    'items' => ["SKU {$variant->sku} chưa có cân nặng vận chuyển."],
                ]);
            }

            $weightGrams += (int) $variant->weight_grams * $quantity;
            $orderValue += $variant->effectivePrice() * $quantity;
        }

        $pickupAddressId = (string) config('services.ghtk.pickup_address_id');
        if ($pickupAddressId === '') {
            throw ValidationException::withMessages([
                'shipping' => ['Shop chưa cấu hình điểm lấy hàng GHTK.'],
            ]);
        }

        $fee = $ghtk->quote([
            'pick_address_id' => $pickupAddressId,
            'province' => $address->province,
            'district' => $address->district,
            'ward' => $address->ward,
            'address' => $address->address_line,
            'weight' => $weightGrams,
            'value' => (int) round($orderValue),
        ]);

        if (! ($fee['delivery'] ?? false)) {
            throw ValidationException::withMessages([
                'shipping' => ['GHTK chưa hỗ trợ giao đến địa chỉ này.'],
            ]);
        }

        return response()->json([
            'data' => [
                'provider' => 'ghtk',
                'weight_grams' => $weightGrams,
                'shipping_fee' => (int) ($fee['fee'] ?? 0),
                'insurance_fee' => (int) ($fee['insurance_fee'] ?? 0),
                'service' => $fee['name'] ?? null,
                'surcharges' => $fee['extFees'] ?? [],
            ],
        ]);
    }
}
