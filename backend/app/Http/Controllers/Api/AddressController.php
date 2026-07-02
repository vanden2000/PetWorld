<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json(['data' => ['addresses' => $addresses]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        if (! $request->user()->addresses()->where('status', 'active')->exists()) {
            $data['is_default'] = true;
        }
        $address = $request->user()->addresses()->create($data + ['status' => 'active']);

        return response()->json(['data' => ['address' => $address, 'message' => 'Đã thêm địa chỉ mới.']], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwner($request, $address);
        $address->update($this->validated($request));

        return response()->json(['data' => ['address' => $address->fresh(), 'message' => 'Đã cập nhật địa chỉ.']]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwner($request, $address);
        $wasDefault = $address->is_default;
        $address->update(['status' => 'inactive', 'is_default' => false]);

        if ($wasDefault) {
            $request->user()->addresses()->where('status', 'active')->latest()->first()?->update(['is_default' => true]);
        }

        return response()->json(['data' => ['message' => 'Đã xóa địa chỉ.']]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:20', 'regex:/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/'],
            'address_line' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }

    private function authorizeOwner(Request $request, Address $address): void
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
    }
}
