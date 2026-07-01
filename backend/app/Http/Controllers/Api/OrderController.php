<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items', 'paymentMethod'])
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $orders->map(fn (Order $order): array => $this->format($order))->all(),
        ]);
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $model = $request->user()->orders()->with('items')->findOrFail($order);

        return response()->json(['data' => $this->format($model)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
            'shipping_method_id.required' => 'Vui lòng chọn phương thức vận chuyển.',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'items.required' => 'Giỏ hàng đang trống.',
        ]);

        // Địa chỉ phải thuộc về user và còn hiệu lực.
        $address = $request->user()->addresses()
            ->where('status', 'active')
            ->findOrFail($data['address_id']);

        $shippingMethod = ShippingMethod::query()
            ->where('status', 'active')
            ->findOrFail($data['shipping_method_id']);

        $order = DB::transaction(function () use ($request, $data, $address, $shippingMethod): Order {
            $quantities = [];
            foreach ($data['items'] as $item) {
                // Gộp số lượng nếu client gửi trùng variant.
                $quantities[$item['variant_id']] = ($quantities[$item['variant_id']] ?? 0) + $item['quantity'];
            }

            // Khoá biến thể để vừa kiểm tra vừa trừ kho an toàn khi nhiều đơn cùng lúc.
            $variants = ProductVariant::query()
                ->with('product')
                ->where('status', 'active')
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $orderItems = [];

            foreach ($quantities as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if ($variant === null) {
                    throw ValidationException::withMessages([
                        'items' => ['Một sản phẩm trong giỏ không còn được bán.'],
                    ]);
                }

                if ($variant->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Sản phẩm \"{$variant->product->name}\" chỉ còn {$variant->quantity} trong kho."],
                    ]);
                }

                // Giá luôn lấy lại từ DB, không tin giá client gửi lên.
                $price = $variant->effectivePrice();
                $subtotal += $price * $quantity;

                $variant->decrement('quantity', $quantity);

                $name = $variant->variant_name
                    ? "{$variant->product->name} - {$variant->variant_name}"
                    : $variant->product->name;

                $orderItems[] = [
                    'product_variant_id' => $variant->id,
                    'product_name' => $name,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $shippingFee = (float) $shippingMethod->shipping_fee;

            $order = $request->user()->orders()->create([
                'shipping_method_id' => $shippingMethod->id,
                'payment_method_id' => $data['payment_method_id'],
                'address_id' => $address->id,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'recipient_address' => $this->composeAddress($address),
                'delivery_area' => $address->province,
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'order_status' => 'pending',
                'total_amount' => $subtotal + $shippingFee,
                'payment_status' => 'unpaid',
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->createMany($orderItems);

            // Mã đối soát chuyển khoản, sinh sau khi có id để đảm bảo duy nhất.
            $order->update(['payment_code' => 'PW'.$order->id]);

            return $order;
        });

        return response()->json([
            'message' => 'Đặt hàng thành công.',
            'data' => $this->format($order->load('items')),
        ], 201);
    }

    private function composeAddress(\App\Models\Address $address): string
    {
        return implode(', ', array_filter([
            $address->address_line,
            $address->ward,
            $address->district,
            $address->province,
        ]));
    }

    private function format(Order $order): array
    {
        return [
            'id' => $order->id,
            'payment_code' => $order->payment_code,
            'recipient_name' => $order->recipient_name,
            'recipient_phone' => $order->recipient_phone,
            'recipient_address' => $order->recipient_address,
            'shipping_fee' => (float) $order->shipping_fee,
            'discount_amount' => (float) $order->discount_amount,
            'total_amount' => (float) $order->total_amount,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'note' => $order->note,
            'created_at' => $order->created_at?->toDateTimeString(),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ])->all(),
        ];
    }
}
