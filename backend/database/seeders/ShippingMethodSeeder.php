<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['name' => 'Giao hàng tiêu chuẩn', 'legacy_name' => 'Giao hang tieu chuan', 'shipping_fee' => 30000],
            ['name' => 'Giao hàng nhanh', 'legacy_name' => 'Giao hang nhanh', 'shipping_fee' => 45000],
            ['name' => 'Nhận tại cửa hàng', 'legacy_name' => 'Nhan tai cua hang', 'shipping_fee' => 0],
        ];

        foreach ($methods as $method) {
            // Tìm cả tên cũ không dấu để chạy lại seeder không tạo phương thức trùng.
            $shippingMethod = ShippingMethod::query()
                ->whereIn('name', [$method['name'], $method['legacy_name']])
                ->first() ?? new ShippingMethod;

            $shippingMethod->fill([
                'name' => $method['name'],
                'shipping_fee' => $method['shipping_fee'],
                'status' => 'active',
            ])->save();
        }
    }
}
