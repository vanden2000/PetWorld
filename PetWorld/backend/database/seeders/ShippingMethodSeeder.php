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
