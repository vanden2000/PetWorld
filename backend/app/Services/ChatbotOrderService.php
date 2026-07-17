<?php

namespace App\Services;

use App\Models\Order;

class ChatbotOrderService
{
    /** The user ID always comes from Sanctum, never from an AI tool argument. */
    public function search(?int $userId, ?string $orderCode = null): array
    {
        if (! $userId) return ['requires_login' => true, 'orders' => []];

        $number = preg_replace('/\D/', '', (string) $orderCode);
        $orders = Order::query()->where('user_id', $userId)->withCount('items')
            ->when($number !== '', fn ($query) => $query->whereKey((int) $number))
            ->latest()->limit($number === '' ? 3 : 1)->get();

        return ['requires_login' => false, 'orders' => $orders->map(fn (Order $order) => [
            'id' => $order->id,
            'code' => 'PW' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at?->toIso8601String(),
            'total_amount' => (float) $order->total_amount,
            'items_count' => $order->items_count,
            'url' => '/account/orders/' . $order->id,
        ])->all()];
    }
}
