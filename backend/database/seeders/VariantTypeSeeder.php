<?php

namespace Database\Seeders;

use App\Models\VariantType;
use Illuminate\Database\Seeder;

class VariantTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variantTypes = [
            ['name' => 'Trọng lượng', 'status' => 'active'],
            ['name' => 'Kích thước', 'status' => 'active'],
            ['name' => 'Màu sắc', 'status' => 'active'],
            ['name' => 'Quy cách đóng gói', 'status' => 'active'],
        ];

        foreach ($variantTypes as $variantType) {
            VariantType::updateOrCreate(
                ['name' => $variantType['name']],
                $variantType,
            );
        }
    }
}
