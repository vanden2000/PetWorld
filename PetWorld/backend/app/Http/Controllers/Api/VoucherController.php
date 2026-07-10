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
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('min_order_value', 'asc')
            ->get();

        $data = $vouchers->map(function (Voucher $voucher) use ($subtotal): array {
            $canApply = $voucher->canBeApplied($subtotal);
            $minOrderValue = (float) $voucher->min_order_value;
            $missingAmount = max(0.0, $minOrderValue - $subtotal);

            return [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'usage_limit' => (int) $voucher->usage_limit,
                'discount_value' => (float) $voucher->discount_value,
                'description' => $voucher->description,
                'min_order_value' => $minOrderValue,
                'start_date' => $voucher->start_date->toIso8601String(),
                'end_date' => $voucher->end_date->toIso8601String(),
                'can_apply' => $canApply,
                'missing_amount' => $missingAmount,
            ];
        });

        return response()->json([
            'data' => [
                'vouchers' => $data,
            ],
        ]);
    }
}
