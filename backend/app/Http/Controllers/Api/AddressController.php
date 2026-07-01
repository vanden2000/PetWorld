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
        $addresses = $request->user()
            ->addresses()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $addresses->map(fn (Address $address): array => $this->format($address))->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);

        $address = $request->user()->addresses()->create($data + ['status' => 'active']);

        return response()->json([
            'message' => 'Đã thêm địa chỉ giao hàng.',
            'data' => $this->format($address),
        ], 201);
    }

    public function update(Request $request, int $address): JsonResponse
    {
        // Chỉ cho sửa địa chỉ thuộc về chính user đang đăng nhập.
        $model = $request->user()->addresses()->findOrFail($address);

        $model->update($this->validateData($request));

        return response()->json([
            'message' => 'Đã cập nhật địa chỉ.',
            'data' => $this->format($model->fresh()),
        ]);
    }

    public function destroy(Request $request, int $address): JsonResponse
    {
        $model = $request->user()->addresses()->findOrFail($address);

        // Đơn hàng tham chiếu address_id (restrictOnDelete) nên không xoá cứng;
        // chuyển sang inactive để ẩn khỏi sổ địa chỉ mà vẫn giữ lịch sử đơn.
        $model->update(['status' => 'inactive', 'is_default' => false]);

        return response()->json([
            'message' => 'Đã xoá địa chỉ.',
            'data' => ['id' => $model->id],
        ]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ], [
            'recipient_name.required' => 'Vui lòng nhập họ tên người nhận.',
            'recipient_phone.required' => 'Vui lòng nhập số điện thoại.',
            'address_line.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'ward.required' => 'Vui lòng nhập phường/xã.',
            'district.required' => 'Vui lòng nhập quận/huyện.',
            'province.required' => 'Vui lòng nhập tỉnh/thành phố.',
        ]);
    }

    private function format(Address $address): array
    {
        return [
            'id' => $address->id,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->recipient_phone,
            'address_line' => $address->address_line,
            'ward' => $address->ward,
            'district' => $address->district,
            'province' => $address->province,
            'is_default' => (bool) $address->is_default,
        ];
    }
}
