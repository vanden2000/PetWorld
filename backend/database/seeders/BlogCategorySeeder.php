<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Cham soc thu cung', 'slug' => 'cham-soc-thu-cung'],
            ['name' => 'Dinh duong', 'slug' => 'dinh-duong'],
            ['name' => 'Kinh nghiem mua sam', 'slug' => 'kinh-nghiem-mua-sam'],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'status' => 'active',
                ],
            );
        }
    }
}
