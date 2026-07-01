<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public const BRANDS = [
        ['name' => 'Sheba', 'slug' => 'sheba', 'image' => 'sheba.png'],
        ['name' => 'Whiskas', 'slug' => 'whiskas', 'image' => 'whiskas.jpg'],
        ['name' => 'Bakers', 'slug' => 'bakers', 'image' => 'bakers.png'],
        ['name' => 'Felix', 'slug' => 'felix', 'image' => 'felix.png'],
        ['name' => 'Good Boy', 'slug' => 'good-boy', 'image' => 'good-boy.png'],
        ['name' => "Butcher's", 'slug' => 'butchers', 'image' => 'butchers.png'],
        ['name' => 'Pedigree', 'slug' => 'pedigree', 'image' => 'pedigree.jpg'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::BRANDS as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                $brand,
            );
        }
    }
}
