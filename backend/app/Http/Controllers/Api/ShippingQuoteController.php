<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use App\Services\ShippingQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingQuoteController extends Controller
{
    public function __invoke(Request $request, ShippingQuoteService $shipping): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'shipping_method_code' => ['required', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $address = $request->user()->addresses()
            ->where('status', 'active')
            ->findOrFail($data['address_id']);

        $method = ShippingMethod::query()
            ->where('status', 'active')
            ->where('code', $data['shipping_method_code'])
            ->firstOrFail();

        $quantities = [];
        foreach ($data['items'] as $item) {
            $quantities[$item['variant_id']] = ($quantities[$item['variant_id']] ?? 0) + $item['quantity'];
        }

        return response()->json([
            'data' => [
                'quote' => $shipping->quote($method, $address, $quantities),
            ],
        ]);
    }
}
