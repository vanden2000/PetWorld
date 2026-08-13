<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GhnWebhookController extends Controller
{
    /**
     * Nhận các callback về đơn hàng từ GHN. GHN có thể gửi lại cùng một callback, do đó
     * việc cập nhật lô hàng được thiết kế có tính chất lũy đẳng (idempotent).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $expectedSecret = (string) config('services.ghn.webhook_secret');
        $providedSecret = (string) ($request->query('token') ?? $request->header('X-GHN-Webhook-Secret'));

        if ($expectedSecret === '' || ! hash_equals($expectedSecret, $providedSecret)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        // GHN documents callback keys in PascalCase; lowercase keys are also
        // accepted to make local/Postman testing convenient.
        $trackingCode = trim((string) ($request->input('OrderCode') ?? $request->input('order_code')));
        $status = trim((string) ($request->input('Status') ?? $request->input('status')));

        if ($trackingCode === '' || $status === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing OrderCode or Status.',
            ], 422);
        }

        if (! in_array($status, Shipment::STATUSES, true)) {
            Log::warning('GHN webhook received an unsupported shipment status.', [
                'tracking_code' => $trackingCode,
                'status' => $status,
            ]);

            return response()->json(['success' => true, 'message' => 'Unsupported status ignored.']);
        }

        $shipment = Shipment::query()
            ->where('provider', 'ghn')
            ->where('tracking_code', $trackingCode)
            ->first();

        if (! $shipment) {
            Log::warning('GHN webhook received for an unknown tracking code.', [
                'tracking_code' => $trackingCode,
                'type' => $request->input('Type') ?? $request->input('type'),
            ]);

            // Return 200 to prevent retries for an event that cannot be matched.
            return response()->json(['success' => true, 'message' => 'Shipment not found.']);
        }

        DB::transaction(function () use ($shipment, $status, $request): void {
            $shipment = Shipment::query()
                ->with('order:id,order_status,payment_status')
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            $shipment->update([
                'status' => $status,
                'provider_status_code' => $status,
                // The URL token authenticates the callback; never store it in the payload.
                'provider_payload' => $request->except('token'),
            ]);

            // GHN is authoritative only once delivery succeeds. Intermediate
            // provider states stay visible on the shipment without changing the order.
            if ($status === 'delivered' && $shipment->order !== null) {
                $orderUpdates = [];
                $canFinalizeOrder = in_array($shipment->order->order_status, ['shipping', 'completed'], true);

                if ($shipment->order->order_status === 'shipping') {
                    $orderUpdates['order_status'] = 'completed';
                }

                // A positive COD amount means the customer paid the courier on delivery.
                // Prepaid orders have cod_amount = 0 and keep their payment status.
                if ($canFinalizeOrder
                    && (float) $shipment->cod_amount > 0
                    && $shipment->order->payment_status === 'unpaid') {
                    $orderUpdates['payment_status'] = 'paid';
                }

                if ($orderUpdates !== []) {
                    $shipment->order->update($orderUpdates);
                }
            }
        });

        return response()->json(['success' => true]);
    }
}
