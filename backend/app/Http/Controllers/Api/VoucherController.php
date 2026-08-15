<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Lấy danh sách voucher đang active kèm trạng thái có thể áp dụng hay không.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'subtotal' => ['nullable', 'numeric', 'min:0'],
        ]);

        $subtotal = (float) ($request->input('subtotal') ?? 0);

        // Lấy tất cả các voucher đang hoạt động
        $vouchers = Voucher::query()
            ->where('status', 'active')
            ->orderBy('min_order_value', 'asc')
            ->get();

        $data = $vouchers->map(function (Voucher $voucher) use ($subtotal): array {
            $canApply = $voucher->canBeApplied($subtotal);
            $minOrderValue = (float) $voucher->min_order_value;
            $missingAmount = max(0.0, $minOrderValue - $subtotal);
            $selectable = $voucher->applies_to === 'product' && ! $voucher->is_automatic;
            $availabilityMessage = $voucher->is_automatic
                ? 'Mã tự động áp dụng khi đủ điều kiện.'
                : ($voucher->applies_to === 'shipping'
                    ? 'Ưu đãi vận chuyển được hệ thống tự áp khi đủ điều kiện.'
                    : ($canApply
                        ? 'Có thể áp dụng cho đơn hàng này.'
                        : ($missingAmount > 0
                            ? 'Chưa đạt giá trị đơn tối thiểu.'
                            : 'Mã chưa trong thời gian hiệu lực hoặc đã hết lượt dùng.')));

            return [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'usage_limit' => (int) $voucher->usage_limit,
                'discount_value' => (float) $voucher->discount_value,
                'description' => $voucher->description,
                'applies_to' => $voucher->applies_to,
                'is_automatic' => (bool) $voucher->is_automatic,
                'selectable' => $selectable,
                'availability_message' => $availabilityMessage,
                'min_order_value' => $minOrderValue,
                'start_date' => $voucher->start_date->toIso8601String(),
                'end_date' => $voucher->end_date->toIso8601String(),
                'can_apply' => $canApply,
                'missing_amount' => $missingAmount,
            ];
        });

        $automaticVoucher = Voucher::query()
            ->where('status', 'active')
            ->where('applies_to', 'product')
            ->where('is_automatic', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('min_order_value', '<=', $subtotal)
            ->orderByDesc('discount_value')
            ->get()
            ->first(fn (Voucher $voucher) => $voucher->canBeApplied($subtotal));

        $eligibleShippingPromotions = Voucher::query()
            ->where('status', 'active')
            ->where('applies_to', 'shipping')
            ->where('is_automatic', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('min_order_value', '<=', $subtotal)
            ->orderByDesc('discount_value')
            ->get()
            ->filter(fn (Voucher $voucher) => $voucher->canBeApplied($subtotal))
            ->values()
            ->map(fn (Voucher $voucher): array => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'description' => $voucher->description,
                'discount_value' => (float) $voucher->discount_value,
                'max_shipping_discount' => (float) ($voucher->max_shipping_discount ?? $voucher->discount_value),
                'min_order_value' => (float) $voucher->min_order_value,
                'shipping_method_code' => $voucher->shipping_method_code,
            ]);

        return response()->json([
            'data' => [
                'vouchers' => $data,
                'automatic_voucher' => $automaticVoucher ? [
                    'id' => $automaticVoucher->id,
                    'code' => $automaticVoucher->code,
                    'description' => $automaticVoucher->description,
                    'discount_value' => (float) $automaticVoucher->discount_value,
                    'min_order_value' => (float) $automaticVoucher->min_order_value,
                ] : null,
                'eligible_shipping_promotions' => $eligibleShippingPromotions,
            ],
        ]);
    }
}
