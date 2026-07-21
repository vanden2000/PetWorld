<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SepayTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SepayPaymentReconciler
{
    public function hasApiCredentials(): bool
    {
        return (bool) config('services.sepay.api_token')
            && (bool) config('services.sepay.api_base_url');
    }

    public function reconcile(Order $order, bool $requireConfigured = false): Order
    {
        if (! $this->hasApiCredentials()) {
            if ($requireConfigured) {
                throw new RuntimeException('Chua cau hinh SEPAY_API_TOKEN de kiem tra giao dich SePay.');
            }

            return $order;
        }

        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status === 'paid') {
                return $lockedOrder;
            }

            if ($lockedOrder->order_status !== 'pending' || $lockedOrder->payment_status !== 'unpaid') {
                return $lockedOrder;
            }

            if ($lockedOrder->expires_at !== null && $lockedOrder->expires_at->isPast()) {
                $lockedOrder->restockAndMarkCancelled();
                return $lockedOrder->refresh();
            }

            $transaction = $this->findTransactionForOrder($lockedOrder);

            if ($transaction === null) {
                return $lockedOrder;
            }

            $this->storeTransaction($lockedOrder, $transaction);

            $lockedOrder->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
            ]);

            return $lockedOrder->refresh();
        });
    }

    private function findTransactionForOrder(Order $order): ?array
    {
        $baseUrl = config('services.sepay.api_base_url');

        $response = Http::acceptJson()
            ->withToken(config('services.sepay.api_token'))
            ->timeout(10)
            ->get($baseUrl.'/transactions', [
                'q' => $order->payment_code,
                'transfer_type' => 'in',
                'amount_in_min' => (int) ceil((float) $order->total_amount),
                'per_page' => 20,
                'timestamp_format' => 'iso8601',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Khong kiem tra duoc giao dich tu SePay.');
        }

        $paymentCode = strtoupper((string) $order->payment_code);

        foreach ($response->json('data', []) as $transaction) {
            $content = strtoupper((string) ($transaction['transaction_content'] ?? ''));
            $code = strtoupper((string) ($transaction['code'] ?? ''));
            $reference = strtoupper((string) ($transaction['reference_number'] ?? ''));
            $amount = (float) ($transaction['amount_in'] ?? 0);
            $transferType = (string) ($transaction['transfer_type'] ?? '');

            if (
                $transferType === 'in'
                && $amount >= (float) $order->total_amount
                && (
                    str_contains($content, $paymentCode)
                    || str_contains($code, $paymentCode)
                    || str_contains($reference, $paymentCode)
                )
            ) {
                return $transaction;
            }
        }

        return null;
    }

    private function storeTransaction(Order $order, array $transaction): void
    {
        $rawId = (string) ($transaction['id'] ?? $transaction['reference_number'] ?? uniqid('', true));
        $numericId = abs((int) sprintf('%u', crc32($rawId)));

        if (SepayTransaction::query()->where('sepay_id', $numericId)->exists()) {
            return;
        }

        SepayTransaction::create([
            'sepay_id' => $numericId,
            'order_id' => $order->id,
            'gateway' => $transaction['bank_brand_name'] ?? 'SEPAY_API',
            'transaction_date' => $transaction['transaction_date'] ?? now(),
            'account_number' => $transaction['account_number'] ?? null,
            'transfer_type' => $transaction['transfer_type'] ?? 'in',
            'amount' => (float) ($transaction['amount_in'] ?? 0),
            'content' => $transaction['transaction_content'] ?? null,
            'reference_code' => $transaction['reference_number'] ?? $transaction['id'] ?? null,
            'raw_payload' => $transaction,
        ]);
    }
}
