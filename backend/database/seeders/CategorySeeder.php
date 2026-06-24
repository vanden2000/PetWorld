<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Thức ăn hạt', 'slug' => 'thuc-an-hat', 'image' => 'thuc-an-hat.jpg'],
            ['name' => 'Pate', 'slug' => 'pate', 'image' => 'pate.jpg'],
            ['name' => 'Snack', 'slug' => 'snack', 'image' => 'snack.jpg'],
            ['name' => 'Phụ kiện', 'slug' => 'phu-kien', 'image' => 'phu-kien.jpg'],
            ['name' => 'Đồ chơi', 'slug' => 'do-choi', 'image' => 'do-choi.jpg'],
            ['name' => 'Vệ sinh và chăm sóc', 'slug' => 've-sinh-va-cham-soc', 'image' => 've-sinh-va-cham-soc.jpg'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
