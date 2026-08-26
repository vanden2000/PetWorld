<?php

namespace App\Services;

use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\Voucher;
use Illuminate\Validation\ValidationException;

class ShippingQuoteService
{
    public function __construct(private readonly GhnService $ghn)
    {
    }

    /** @param array<int, int> $quantities */
    public function quote(ShippingMethod $method, Address $address, array $quantities, ?int $shippingVoucherId = null): array
    {
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
                throw ValidationException::withMessages(['items' => ['Sản phẩm trong giỏ không còn được bán.']]);
            }
            if ((int) $variant->weight_grams <= 0) {
                throw ValidationException::withMessages(['items' => ["SKU {$variant->sku} chưa có cân nặng vận chuyển."]]);
            }
            $weightGrams += (int) $variant->weight_grams * $quantity;
            $orderValue += $variant->effectivePrice() * $quantity;
        }

        $quote = match ($method->code) {
            'standard' => $this->standardQuote($weightGrams),
            'ghn_express' => $this->ghnQuote($address, $weightGrams, $orderValue),
            default => throw ValidationException::withMessages(['shipping_method' => ['Phương thức giao hàng chưa được hỗ trợ.']]),
        };

        return $this->applyShippingPromotion($quote, $orderValue, $method->code, $shippingVoucherId);
    }

    private function standardQuote(int $weightGrams): array
    {
        $rule = config('shipping.standard');
        $extraWeight = max(0, $weightGrams - (int) $rule['base_weight_grams']);
        $steps = (int) ceil($extraWeight / (int) $rule['extra_weight_grams']);
        $fee = (int) $rule['base_fee'] + $steps * (int) $rule['extra_fee'];

        return ['method_code' => 'standard', 'weight_grams' => $weightGrams, 'shipping_fee_original' => $fee, 'shipping_discount' => 0, 'shipping_fee' => $fee, 'description' => '2–5 ngày · Phí theo cân nặng'];
    }

    private function ghnQuote(Address $address, int $weightGrams, float $orderValue): array
    {
        if (! $address->ghn_district_id || ! $address->ghn_ward_code) {
            throw ValidationException::withMessages(['address' => ['Địa chỉ cần cập nhật Quận/Huyện và Phường/Xã để dùng GHN.']]);
        }
        $fee = $this->ghn->quote([
            'service_type_id' => config('services.ghn.service_type_id'),
            'from_district_id' => (int) config('services.ghn.from_district_id'),
            'from_ward_code' => config('services.ghn.from_ward_code'),
            'to_district_id' => $address->ghn_district_id,
            'to_ward_code' => $address->ghn_ward_code,
            'weight' => $weightGrams,
            'length' => config('services.ghn.length'),
            'width' => config('services.ghn.width'),
            'height' => config('services.ghn.height'),
            'insurance_value' => (int) round($orderValue),
        ]);
        $total = (int) ($fee['total'] ?? $fee['service_fee'] ?? 0);
        return ['method_code' => 'ghn_express', 'weight_grams' => $weightGrams, 'shipping_fee_original' => $total, 'shipping_discount' => 0, 'shipping_fee' => $total, 'description' => '1–2 ngày · Phí theo địa chỉ GHN'];
    }

    private function applyShippingPromotion(array $quote, float $orderValue, string $methodCode, ?int $shippingVoucherId): array
    {
        if ($shippingVoucherId !== null) {
            $promotion = Voucher::query()
                ->whereKey($shippingVoucherId)
                ->where('applies_to', 'shipping')
                ->where('is_automatic', false)
                ->first();

            if ($promotion === null || ! $this->matchesShippingMethod($promotion, $methodCode) || ! $promotion->canBeApplied($orderValue)) {
                throw ValidationException::withMessages(['shipping_voucher_id' => ['Mã giảm phí vận chuyển không hợp lệ hoặc không đủ điều kiện.']]);
            }

            return $this->applyPromotion($quote, $promotion);
        }

        $promotion = Voucher::query()
            ->where('status', 'active')
            ->where('applies_to', 'shipping')
            ->where('is_automatic', true)
            ->where(fn ($query) => $query->whereNull('shipping_method_code')->orWhere('shipping_method_code', $methodCode))
            ->where('min_order_value', '<=', $orderValue)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('discount_value')
            ->get()
            ->first(fn (Voucher $voucher) => $voucher->canBeApplied($orderValue));

        if ($promotion === null) {
            return array_merge($quote, ['shipping_promotion' => null, 'shipping_voucher_id' => null]);
        }

        return $this->applyPromotion($quote, $promotion);
    }

    private function matchesShippingMethod(Voucher $voucher, string $methodCode): bool
    {
        return $voucher->shipping_method_code === null || $voucher->shipping_method_code === $methodCode;
    }

    private function applyPromotion(array $quote, Voucher $promotion): array
    {
        $cap = $promotion->max_shipping_discount !== null
            ? (float) $promotion->max_shipping_discount
            : (float) $promotion->discount_value;
        $discount = min((float) $quote['shipping_fee_original'], (float) $promotion->discount_value, $cap);

        return array_merge($quote, [
            'shipping_discount' => $discount,
            'shipping_fee' => max(0, (float) $quote['shipping_fee_original'] - $discount),
            'shipping_promotion' => [
                'id' => $promotion->id,
                'code' => $promotion->code,
                'name' => $promotion->description ?: $promotion->code,
            ],
            'shipping_voucher_id' => $promotion->id,
        ]);
    }
}
