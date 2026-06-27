<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['name' => 'Thanh toán khi nhận hàng', 'legacy_name' => 'Thanh toan khi nhan hang'],
            ['name' => 'Chuyển khoản ngân hàng', 'legacy_name' => 'Chuyen khoan ngan hang'],
            ['name' => 'Ví điện tử', 'legacy_name' => 'Vi dien tu'],
        ];

        foreach ($methods as $method) {
            // Tìm cả tên cũ không dấu để chạy lại seeder không tạo phương thức trùng.
            $paymentMethod = PaymentMethod::query()
                ->whereIn('name', [$method['name'], $method['legacy_name']])
                ->first() ?? new PaymentMethod;

            $paymentMethod->fill([
                'name' => $method['name'],
                'status' => 'active',
            ])->save();
        }
    }
}
