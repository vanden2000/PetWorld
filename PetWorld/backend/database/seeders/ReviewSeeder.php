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
            ['rating' => 5, 'comment' => 'Sản phẩm đóng gói kỹ, đúng mô tả và thú cưng nhà mình rất thích.'],
            ['rating' => 5, 'comment' => 'Giao hàng nhanh, chất lượng tốt, sẽ tiếp tục ủng hộ PetWorld.'],
            ['rating' => 4, 'comment' => 'Giá hợp lý, sản phẩm ổn. Nếu có thêm ảnh hướng dẫn thì sẽ tốt hơn.'],
            ['rating' => 5, 'comment' => 'Bé nhà mình dùng hợp, bao bì còn mới và hạn sử dụng xa.'],
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
