<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class GhnShipmentService
{
    public function __construct(private readonly GhnService $ghn)
    {
    }

    /** @return array{tracking_code: string, fee: float, cod_amount: float, payload: array<string, mixed>} */
    public function create(Order $order): array
    {
        $order->loadMissing([
            'address',
            'paymentMethod:id,name',
            'items.productVariant:id,sku,weight_grams',
        ]);

        if ($order->address === null || ! $order->address->ghn_district_id || ! $order->address->ghn_ward_code) {
            throw ValidationException::withMessages([
                'shipping' => ['Địa chỉ nhận chưa có mã Quận/Huyện và Phường/Xã GHN.'],
            ]);
        }

        $this->ensurePickupConfiguration();

        $weight = max(1, (int) $order->shipping_weight_grams);
        $codAmount = $order->isBankTransfer() ? 0 : (float) $order->total_amount;
        $items = $order->items->map(function ($item): array {
            $variant = $item->productVariant;

            return [
                'name' => $item->product_name,
                'code' => $variant?->sku,
                'quantity' => (int) $item->quantity,
                'price' => (int) round((float) $item->price),
                'weight' => max(1, (int) ($variant?->weight_grams ?? 1)),
            ];
        })->values()->all();

        $payload = [
            'payment_type_id' => (int) config('services.ghn.payment_type_id'),
            'required_note' => (string) config('services.ghn.required_note'),
            'from_name' => (string) config('services.ghn.from_name'),
            'from_phone' => (string) config('services.ghn.from_phone'),
            'from_address' => (string) config('services.ghn.from_address'),
            'from_ward_name' => (string) config('services.ghn.from_ward_name'),
            'from_district_name' => (string) config('services.ghn.from_district_name'),
            'from_province_name' => (string) config('services.ghn.from_province_name'),
            'return_phone' => (string) config('services.ghn.from_phone'),
            'return_address' => (string) config('services.ghn.from_address'),
            'return_district_id' => (int) config('services.ghn.from_district_id'),
            'return_ward_code' => (string) config('services.ghn.from_ward_code'),
            'client_order_code' => $order->payment_code ?: ('PW-' . $order->id),
            'to_name' => $order->recipient_name,
            'to_phone' => $order->recipient_phone,
            'to_address' => $order->recipient_address,
            'to_district_id' => (int) $order->address->ghn_district_id,
            'to_ward_code' => (string) $order->address->ghn_ward_code,
            'cod_amount' => (int) round($codAmount),
            'content' => 'Đơn PetWorld #' . $order->id,
            'note' => $order->note,
            'weight' => $weight,
            'length' => (int) config('services.ghn.length'),
            'width' => (int) config('services.ghn.width'),
            'height' => (int) config('services.ghn.height'),
            'insurance_value' => min(5000000, (int) round($order->total_amount)),
            'service_type_id' => (int) config('services.ghn.service_type_id'),
            'items' => $items,
        ];

        $response = $this->ghn->createOrder($payload);

        return [
            'tracking_code' => (string) $response['order_code'],
            'fee' => (float) ($response['total_fee'] ?? $order->shipping_fee),
            'cod_amount' => $codAmount,
            'payload' => $response,
        ];
    }

    private function ensurePickupConfiguration(): void
    {
        foreach ([
            'from_name', 'from_phone', 'from_address', 'from_ward_name',
            'from_district_name', 'from_province_name', 'from_district_id', 'from_ward_code',
        ] as $key) {
            if (blank(config("services.ghn.{$key}"))) {
                throw ValidationException::withMessages([
                    'shipping' => ['Thiếu cấu hình GHN điểm lấy hàng: ' . strtoupper($key) . '.'],
                ]);
            }
        }
    }
}
