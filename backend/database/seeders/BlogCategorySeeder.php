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
            ['name' => 'Chăm sóc thú cưng', 'slug' => 'cham-soc-thu-cung'],
            ['name' => 'Dinh dưỡng', 'slug' => 'dinh-duong'],
            ['name' => 'Kinh nghiệm mua sắm', 'slug' => 'kinh-nghiem-mua-sam'],
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
