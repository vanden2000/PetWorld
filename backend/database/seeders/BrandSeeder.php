<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Royal Canin', 'slug' => 'royal-canin', 'image' => 'royal-canin.jpg'],
            ['name' => 'Pedigree', 'slug' => 'pedigree', 'image' => 'pedigree.jpg'],
            ['name' => 'Whiskas', 'slug' => 'whiskas', 'image' => 'whiskas.jpg'],
            ['name' => 'SmartHeart', 'slug' => 'smartheart', 'image' => 'smartheart.jpg'],
            ['name' => 'Me-O', 'slug' => 'me-o', 'image' => 'me-o.jpg'],
            ['name' => 'Ganador', 'slug' => 'ganador', 'image' => 'ganador.jpg'],
            ['name' => 'KONG', 'slug' => 'kong', 'image' => 'kong.jpg'],
            ['name' => 'Trixie', 'slug' => 'trixie', 'image' => 'trixie.jpg'],
            ['name' => 'Bioline', 'slug' => 'bioline', 'image' => 'bioline.jpg'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                $brand,
            );
        }
    }
}
