<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:completed,processing,cancelled'],
            'search' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orders = $request->user()->orders()
            ->withCount('items')
            ->when(($data['status'] ?? null) === 'completed', fn ($query) => $query->where('order_status', 'completed'))
            ->when(($data['status'] ?? null) === 'processing', fn ($query) => $query->whereIn('order_status', ['pending', 'confirmed', 'shipping']))
            ->when(($data['status'] ?? null) === 'cancelled', fn ($query) => $query->where('order_status', 'cancelled'))
            ->when($data['search'] ?? null, function ($query, string $search): void {
                $numericId = preg_replace('/\D/', '', $search);
                $query->where(function ($nested) use ($search, $numericId): void {
                    if ($numericId !== '') {
                        $nested->where('id', (int) $numericId);
                    }
                    $nested->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(6);

        return response()->json(['data' => [
            'orders' => collect($orders->items())->map(fn ($order) => [
                'id' => $order->id,
                'code' => 'PW'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'created_at' => $order->created_at?->toIso8601String(),
                'status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'total_amount' => (float) $order->total_amount,
                'items_count' => $order->items_count,
            ])->values(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $order->load([
            'shippingMethod:id,name',
            'paymentMethod:id,name',
            'items.productVariant.product.primaryImage',
        ]);

        $subtotal = $order->items->sum(fn ($item) => (float) $item->price * $item->quantity);

        return response()->json(['data' => ['order' => [
            'id' => $order->id,
            'code' => 'PW'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
            'recipient' => [
                'name' => $order->recipient_name,
                'phone' => $order->recipient_phone,
                'address' => $order->recipient_address,
            ],
            'shipping' => [
                'method' => $order->shippingMethod?->name,
                'fee' => (float) $order->shipping_fee,
                'tracking_code' => 'PW-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            ],
            'payment' => [
                'method' => $order->paymentMethod?->name,
                'subtotal' => $subtotal,
                'discount' => (float) $order->discount_amount,
                'total' => (float) $order->total_amount,
            ],
            'note' => $order->note,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->product_name,
                'variant' => $item->productVariant?->variant_name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'slug' => $item->productVariant?->product?->slug,
                'image' => $item->productVariant?->product?->primaryImage?->image_url,
            ])->values(),
        ]]]);
    }
}
