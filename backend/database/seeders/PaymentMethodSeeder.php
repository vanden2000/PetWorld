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
        foreach (['Thanh toan khi nhan hang', 'Chuyen khoan ngan hang', 'Vi dien tu'] as $name) {
            PaymentMethod::updateOrCreate(
                ['name' => $name],
                ['status' => 'active'],
            );
        }
    }
}
