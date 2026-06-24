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
            ['name' => 'Trọng lượng', 'status' => true],
            ['name' => 'Kích thước', 'status' => true],
            ['name' => 'Màu sắc', 'status' => true],
            ['name' => 'Quy cách đóng gói', 'status' => true],
        ];

        foreach ($variantTypes as $variantType) {
            VariantType::updateOrCreate(
                ['name' => $variantType['name']],
                $variantType,
            );
        }
    }
}
