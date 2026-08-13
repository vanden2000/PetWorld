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
            ['name' => 'Giao hàng tiêu chuẩn', 'legacy_name' => 'Giao Hàng Tiêu Chuẩn', 'shipping_fee' => 30000],
            ['name' => 'Giao hàng nhanh', 'legacy_name' => 'Giao Hàng Nhanh', 'shipping_fee' => 45000],
           
        ];

        foreach ($methods as $index => $method) {
            // Tìm cả tên cũ không dấu để chạy lại seeder không tạo phương thức trùng.
            $shippingMethod = ShippingMethod::query()
                ->whereIn('name', [$method['name'], $method['legacy_name']])
                ->first() ?? new ShippingMethod;

            $shippingMethod->fill([
                'name' => $method['name'],
                'code' => $index === 0 ? 'standard' : 'ghn_express',
                'provider' => $index === 0 ? 'petworld' : 'ghn',
                'fee_mode' => $index === 0 ? 'weight_rule' : 'live_quote',
                'description' => $index === 0 ? '2–5 ngày · Phí theo cân nặng' : '1–2 ngày · Phí theo địa chỉ GHN',
                'shipping_fee' => $index === 0 ? 30000 : 0,
                'status' => 'active',
            ])->save();
        }
    }
}
