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
        // Xóa phương thức Ví điện tử khỏi cơ sở dữ liệu nếu có
        $walletMethod = PaymentMethod::query()
            ->whereIn('name', ['Ví điện tử', 'Vi Dien Tu'])
            ->first();

        if ($walletMethod) {
            // Cập nhật lại các đơn hàng cũ (nếu có) sang Chuyển khoản ngân hàng trước khi xóa
            $fallbackMethod = PaymentMethod::query()->where('name', 'Chuyển khoản ngân hàng')->first();
            if ($fallbackMethod) {
                \App\Models\Order::where('payment_method_id', $walletMethod->id)
                    ->update(['payment_method_id' => $fallbackMethod->id]);
            }
            $walletMethod->delete();
        }

        $methods = [
            ['name' => 'Thanh toán khi nhận hàng', 'legacy_name' => 'Thanh Toán Khi Nhận Hàng'],
            ['name' => 'Chuyển khoản ngân hàng', 'legacy_name' => 'Chuyen Khoan Ngan Hang'],
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
