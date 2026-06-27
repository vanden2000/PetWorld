<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'PETWELCOME',
                'usage_limit' => 100,
                'discount_value' => 50000,
                'description' => 'Giảm 50.000đ cho đơn hàng đầu tiên từ 300.000đ.',
                'min_order_value' => 300000,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addMonths(2),
                'status' => 'active',
            ],
            [
                'code' => 'FREESHIP99',
                'usage_limit' => 200,
                'discount_value' => 30000,
                'description' => 'Hỗ trợ phí giao hàng cho đơn từ 199.000đ.',
                'min_order_value' => 199000,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addMonth(),
                'status' => 'active',
            ],
            [
                'code' => 'PETCARE10',
                'usage_limit' => 50,
                'discount_value' => 100000,
                'description' => 'Ưu đãi chăm sóc thú cưng cho đơn từ 900.000đ.',
                'min_order_value' => 900000,
                'start_date' => now()->subMonth(),
                'end_date' => now()->subDay(),
                'status' => 'expired',
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(
                ['code' => $voucher['code']],
                $voucher,
            );
        }
    }
}
