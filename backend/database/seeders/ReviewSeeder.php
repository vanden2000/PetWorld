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
            ['rating' => 5, 'comment' => 'Sản phẩm đóng gói kỹ, đúng mô tả và cún nhà mình cực kỳ thích luôn!'],
            ['rating' => 5, 'comment' => 'Giao hàng siêu nhanh, hàng chính hãng chất lượng 10/10, sẽ tiếp tục ủng hộ PetWorld dài lâu.'],
            ['rating' => 5, 'comment' => 'Dùng bé mèo rất hợp vị, ăn ngon miệng hẳn ra. Rất hài lòng về cách chăm sóc khách hàng.'],
            ['rating' => 5, 'comment' => 'Bé cún nhà mình mê tít món này, hạt thơm tho và HSD xa. Giao nhanh trong 2h!'],
            ['rating' => 4, 'comment' => 'Chất liệu cứng cáp, phối màu đẹp đúng chuẩn ảnh mẫu. Đáng tiền lắm nha mọi người.'],
            ['rating' => 5, 'comment' => 'Hàng xịn sò chuẩn Trixie, dây dắt chắc chắn êm tay lắm. Rất đáng trải nghiệm.'],
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
