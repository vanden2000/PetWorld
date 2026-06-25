<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = [
            ['rating' => 5, 'comment' => 'San pham dong goi ky, dung mo ta va thu cung nha minh rat thich.'],
            ['rating' => 5, 'comment' => 'Giao hang nhanh, chat luong tot, se tiep tuc ung ho PetWorld.'],
            ['rating' => 4, 'comment' => 'Gia hop ly, san pham on. Neu co them anh huong dan thi se tot hon.'],
            ['rating' => 5, 'comment' => 'Be nha minh dung hop, bao bi con moi va han su dung xa.'],
        ];

        $completedItems = OrderItem::query()
            ->with('order')
            ->whereHas('order', fn ($query) => $query->where('order_status', 'completed'))
            ->orderBy('id')
            ->get();

        foreach ($completedItems as $index => $item) {
            $review = $comments[$index % count($comments)];

            Review::updateOrCreate(
                [
                    'user_id' => $item->order->user_id,
                    'order_item_id' => $item->id,
                ],
                [
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'status' => 'approved',
                ],
            );
        }
    }
}
