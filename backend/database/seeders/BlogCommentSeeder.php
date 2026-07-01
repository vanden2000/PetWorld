<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use App\Models\BlogComment;
use Illuminate\Database\Seeder;

class BlogCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('email', [
            'mai.nguyen@petworld.test',
            'minh.tran@petworld.test',
            'lan.le@petworld.test',
        ])->get();

        if ($users->isEmpty()) {
            return;
        }

        $blogs = Blog::all();

        $commentsPool = [
            'Bài viết rất hữu ích, cảm ơn PetWorld!',
            'Mình đã áp dụng thử cho bé cưng nhà mình và thấy hiệu quả rõ rệt.',
            'Thông tin chi tiết và rất khoa học, mong admin ra thêm nhiều bài viết như thế này nữa.',
            'Bé nhà mình cũng gặp tình trạng tương tự, đọc bài này xong thấy yên tâm hơn hẳn.',
            'Sản phẩm và dịch vụ của PetWorld lúc nào cũng làm mình hài lòng.',
            'Chia sẻ rất thực tế, bổ ích cho những người mới nuôi thú cưng như mình.',
            'Cảm ơn bài viết rất nhiều, các mẹo chải lông rất hiệu quả.',
            'Thức ăn này chất lượng lắm, mình cũng hay mua hạt Royal Canin ở PetWorld.',
        ];

        foreach ($blogs as $blog) {
            // Cho mỗi bài viết có từ 2 đến 3 bình luận ngẫu nhiên
            $numberOfComments = rand(2, 3);
            $shuffledUsers = $users->shuffle();

            for ($i = 0; $i < $numberOfComments; $i++) {
                if ($shuffledUsers->offsetExists($i)) {
                    $user = $shuffledUsers->get($i);
                    $content = $commentsPool[array_rand($commentsPool)];

                    BlogComment::create([
                        'blog_id' => $blog->id,
                        'user_id' => $user->id,
                        'content' => $content,
                        'created_at' => now()->subDays(rand(1, 10))->subHours(rand(1, 23)),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
