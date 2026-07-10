<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SepayTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SepayWebhookController extends Controller
{
    /**
     * Webhook SePay gọi về khi có tiền vào tài khoản. Xác thực bằng API key,
     * khớp mã đơn trong nội dung chuyển khoản rồi đánh dấu đơn đã thanh toán.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $expectedKey = config('services.sepay.webhook_api_key');

        // Chỉ chấp nhận request mang đúng API key (header "Authorization: Apikey <key>").
        if (! $expectedKey || $request->header('Authorization') !== 'Apikey '.$expectedKey) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $sepayId = $request->input('id');

        if (! $sepayId) {
            return response()->json(['success' => false, 'message' => 'Thiếu id giao dịch.'], 422);
        }

        // Chống xử lý trùng: SePay có thể gửi lại cùng một giao dịch.
        if (SepayTransaction::query()->where('sepay_id', $sepayId)->exists()) {
            return response()->json(['success' => true, 'message' => 'Giao dịch đã được xử lý.']);
        }

        $transferType = $request->input('transferType');
        $amount = (float) $request->input('transferAmount', 0);
        $content = (string) $request->input('content', '');

        // Chỉ tiền vào mới đối soát; tìm mã đơn dạng PW123 trong nội dung chuyển khoản.
        $order = null;
        if ($transferType === 'in' && preg_match('/PW\d+/i', $content, $matches)) {
            $order = Order::query()
                ->where('payment_code', strtoupper($matches[0]))
                ->where('payment_status', 'unpaid')
                ->first();
        }

        // Luôn ghi log để đối soát/audit, kể cả khi không khớp đơn nào.
        SepayTransaction::create([
            'sepay_id' => $sepayId,
            'order_id' => $order?->id,
            'gateway' => $request->input('gateway'),
            'transaction_date' => $request->input('transactionDate'),
            'account_number' => $request->input('accountNumber'),
            'transfer_type' => $transferType,
            'amount' => $amount,
            'content' => $content,
            'reference_code' => $request->input('referenceCode'),
            'raw_payload' => $request->all(),
        ]);

        // Đủ tiền thì xác nhận đơn; thiếu tiền giữ nguyên unpaid để xử lý thủ công.
        if ($order && $amount >= (float) $order->total_amount) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
            ]);
        }

        return response()->json(['success' => true]);
    }
}
