<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GhnWebhookController extends Controller
{
    /**
     * Receives GHN order callbacks. GHN can retry the same callback, so the
     * shipment update is intentionally idempotent.
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

        $shipment->update([
            'status' => $status,
            'provider_status_code' => $status,
            'provider_payload' => $request->all(),
        ]);

        return response()->json(['success' => true]);
    }
}
