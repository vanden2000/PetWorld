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
                'description' => 'Giam 50.000d cho don hang dau tien tu 300.000d.',
                'min_order_value' => 300000,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addMonths(2),
                'status' => 'active',
            ],
            [
                'code' => 'FREESHIP99',
                'usage_limit' => 200,
                'discount_value' => 30000,
                'description' => 'Ho tro phi giao hang cho don tu 199.000d.',
                'min_order_value' => 199000,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addMonth(),
                'status' => 'active',
            ],
            [
                'code' => 'PETCARE10',
                'usage_limit' => 50,
                'discount_value' => 100000,
                'description' => 'Uu dai cham soc thu cung cho don tu 900.000d.',
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
