<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::updateOrCreate(
            ['email' => 'admin@petworld.test'],
            [
                'name' => 'PetWorld Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ],
        );

        $blogs = [
            [
                'category_slug' => 'cham-soc-thu-cung',
                'title' => 'Cách chăm sóc chó con mới về nhà trong 7 ngày đầu',
                'slug' => 'cach-cham-soc-cho-con-moi-ve-nha-trong-7-ngay-dau',
                'description' => 'Hướng dẫn chăm sóc chó con mới về nhà: không gian nghỉ ngơi, lịch ăn, vệ sinh, theo dõi sức khỏe và cách giúp bé làm quen với gia đình.',
                'image' => 'cham-soc-cho-con-moi-ve-nha.jpg',
                'content' => $this->article(
                    'Cách chăm sóc chó con mới về nhà trong 7 ngày đầu',
                    'Bảy ngày đầu tiên là giai đoạn quan trọng để chó con làm quen với mùi, âm thanh và lịch sinh hoạt mới. Chủ nuôi nên chuẩn bị một khu vực yên tĩnh, có đệm nằm, bát nước riêng và đồ chơi an toàn.',
                    [
                        'Giữ lịch ăn ổn định, không đổi thức ăn đột ngột trong những ngày đầu.',
                        'Đặt chỗ ngủ ở nơi thoáng, ấm vừa phải và tránh tiếng ồn lớn.',
                        'Theo dõi phân, mức năng lượng, hô hấp và biểu hiện bỏ ăn.',
                    ],
                    'Nếu bé có dấu hiệu nôn liên tục, tiêu chảy, mệt mỏi hoặc bỏ ăn hơn 24 giờ, hãy liên hệ bác sĩ thú y. Việc tiêm phòng và tẩy giun cần được sắp xếp theo lịch tư vấn chuyên môn.',
                ),
                'view_count' => 42,
            ],
            [
                'category_slug' => 'dinh-duong',
                'title' => 'Chọn thức ăn hạt cho mèo trưởng thành theo nhu cầu dinh dưỡng',
                'slug' => 'chon-thuc-an-hat-cho-meo-truong-thanh-theo-nhu-cau-dinh-duong',
                'description' => 'Kinh nghiệm chọn thức ăn hạt cho mèo trưởng thành dựa trên độ tuổi, cân nặng, mức vận động, tình trạng lông da và khả năng tiêu hóa.',
                'image' => 'chon-thuc-an-hat-cho-meo-truong-thanh.jpg',
                'content' => $this->article(
                    'Chọn thức ăn hạt cho mèo trưởng thành theo nhu cầu dinh dưỡng',
                    'Mèo trưởng thành cần khẩu phần cân bằng giữa đạm, chất béo, vitamin, khoáng chất và nước. Khi chọn thức ăn hạt, chủ nuôi nên đọc bảng thành phần, độ đạm, năng lượng và khuyến nghị khẩu phần trên bao bì.',
                    [
                        'Chọn công thức phù hợp với mèo trong nhà, mèo năng động hoặc mèo cần kiểm soát cân nặng.',
                        'Ưu tiên sản phẩm có thông tin thành phần rõ ràng và hạn sử dụng đầy đủ.',
                        'Đổi thức ăn từ từ trong 7 đến 10 ngày để hệ tiêu hóa thích nghi.',
                    ],
                    'Luôn đặt nước sạch gần khu vực ăn vì thức ăn hạt có độ ẩm thấp. Nếu mèo có tiền sử sỏi tiết niệu, dị ứng hoặc bệnh mãn tính, cần hỏi ý kiến bác sĩ thú y trước khi đổi khẩu phần.',
                ),
                'view_count' => 38,
            ],
            [
                'category_slug' => 'kinh-nghiem-mua-sam',
                'title' => 'Kinh nghiệm mua phụ kiện an toàn cho chó mèo',
                'slug' => 'kinh-nghiem-mua-phu-kien-an-toan-cho-cho-meo',
                'description' => 'Checklist mua phụ kiện cho chó mèo: chất liệu, kích thước, độ bền, cách vệ sinh và các dấu hiệu cần thay mới để đảm bảo an toàn.',
                'image' => 'kinh-nghiem-mua-phu-kien-an-toan.jpg',
                'content' => $this->article(
                    'Kinh nghiệm mua phụ kiện an toàn cho chó mèo',
                    'Phụ kiện như dây dắt, vòng cổ, bát ăn, đồ chơi và nhà vệ sinh cần phù hợp với kích thước, thói quen và mức độ vận động của thú cưng. Một món đồ dùng tốt phải dễ vệ sinh, không có cạnh sắc và không gây khó chịu khi sử dụng lâu.',
                    [
                        'Đo kích thước cổ, ngực hoặc khu vực sử dụng trước khi mua.',
                        'Kiểm tra đường may, khóa cài, mùi vật liệu và độ chắc của sản phẩm.',
                        'Thay mới khi phụ kiện bị nứt, sờn, biến dạng hoặc khó làm sạch.',
                    ],
                    'Không nên mua phụ kiện chỉ dựa trên màu sắc. Hãy ưu tiên an toàn, độ vừa vặn và khả năng bảo trì hằng ngày. Với đồ chơi, nên quan sát thú cưng trong những lần sử dụng đầu.',
                ),
                'view_count' => 34,
            ],
            [
                'category_slug' => 'cham-soc-thu-cung',
                'title' => 'Lịch tắm và vệ sinh lông cho thú cưng tại nhà',
                'slug' => 'lich-tam-va-ve-sinh-long-cho-thu-cung-tai-nha',
                'description' => 'Gợi ý lịch tắm, chải lông, vệ sinh tai móng và chăm sóc da lông cho thú cưng tại nhà theo từng nhu cầu sinh hoạt.',
                'image' => 'lich-tam-va-ve-sinh-long-thu-cung.jpg',
                'content' => $this->article(
                    'Lịch tắm và vệ sinh lông cho thú cưng tại nhà',
                    'Lịch tắm của thú cưng không nên cố định giống nhau cho mọi bé. Giống loài, độ dài lông, môi trường sống và tình trạng da sẽ quyết định tần suất chăm sóc phù hợp.',
                    [
                        'Chải lông định kỳ giúp giảm rối lông và phát hiện ve, vết thương sớm.',
                        'Dùng sữa tắm dành riêng cho thú cưng, không dùng dầu gội của người.',
                        'Lau khô tai và kẽ chân sau khi tắm để hạn chế ẩm ướt.',
                    ],
                    'Nếu da bị đỏ, có mùi bất thường hoặc thú cưng gặp phản ứng sau khi tắm, nên ngừng sản phẩm đang dùng và tham khảo bác sĩ thú y.',
                ),
                'view_count' => 26,
            ],
            [
                'category_slug' => 'dinh-duong',
                'title' => 'Cách kết hợp pate và thức ăn hạt trong bữa ăn hằng ngày',
                'slug' => 'cach-ket-hop-pate-va-thuc-an-hat-trong-bua-an-hang-ngay',
                'description' => 'Hướng dẫn kết hợp pate và thức ăn hạt để tăng độ ngon miệng, bổ sung độ ẩm và vẫn kiểm soát tổng năng lượng mỗi ngày.',
                'image' => 'ket-hop-pate-va-thuc-an-hat.jpg',
                'content' => $this->article(
                    'Cách kết hợp pate và thức ăn hạt trong bữa ăn hằng ngày',
                    'Kết hợp pate với thức ăn hạt có thể giúp bữa ăn hấp dẫn hơn và tăng độ ẩm cho khẩu phần. Tuy vậy, chủ nuôi cần tính tổng năng lượng để tránh cho ăn quá mức.',
                    [
                        'Bắt đầu với một lượng pate nhỏ để theo dõi khả năng tiêu hóa.',
                        'Giảm bớt lượng hạt nếu đã bổ sung pate trong cùng bữa ăn.',
                        'Không để pate ngoài nhiệt độ phòng quá lâu sau khi mở.',
                    ],
                    'Nên chọn pate và hạt phù hợp cùng độ tuổi, loài thú cưng và tình trạng sức khỏe. Với bé có dạ dày nhạy cảm, hãy đổi thực đơn chậm và ghi lại phản ứng sau mỗi bữa.',
                ),
                'view_count' => 22,
            ],
        ];

        foreach ($blogs as $blog) {
            $category = BlogCategory::where('slug', $blog['category_slug'])->firstOrFail();

            Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                [
                    'blog_category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $blog['title'],
                    'description' => $blog['description'],
                    'content' => $blog['content'],
                    'view_count' => $blog['view_count'],
                    'image' => $blog['image'],
                    'status' => 'active',
                ],
            );
        }
    }

    private function article(string $title, string $intro, array $highlights, string $note): string
    {
        $items = implode('', array_map(
            static fn (string $highlight): string => "<li>{$highlight}</li>",
            $highlights,
        ));

        return <<<HTML
<article>
    <h2>{$title}</h2>
    <p>{$intro}</p>
    <h3>Điểm chính cần nhớ</h3>
    <ul>{$items}</ul>
    <h3>Lưu ý từ PetWorld</h3>
    <p>{$note}</p>
</article>
HTML;
    }
}
