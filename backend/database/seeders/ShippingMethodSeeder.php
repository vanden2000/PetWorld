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
            ['name' => 'Giao hang tieu chuan', 'shipping_fee' => 30000],
            ['name' => 'Giao hang nhanh', 'shipping_fee' => 45000],
            ['name' => 'Nhan tai cua hang', 'shipping_fee' => 0],
        ];

        foreach ($methods as $method) {
            ShippingMethod::updateOrCreate(
                ['name' => $method['name']],
                [
                    'shipping_fee' => $method['shipping_fee'],
                    'status' => 'active',
                ],
            );
        }
    }
}
