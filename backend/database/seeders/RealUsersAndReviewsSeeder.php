<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RealUsersAndReviewsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo 5 tài khoản người dùng thực tế
        $realUsersData = [
            [
                'name' => 'Nguyễn Minh Thư',
                'email' => 'minhthu.nguyen88@gmail.com',
                'phone' => '0903124567',
                'date_of_birth' => '1994-05-12',
                'address' => [
                    'recipient_name' => 'Nguyễn Minh Thư',
                    'recipient_phone' => '0903124567',
                    'address_line' => '128/4 Lê Thánh Tôn',
                    'ward' => 'Phường Bến Thành',
                    'district' => 'Quận 1',
                    'province' => 'Hồ Chí Minh',
                ],
            ],
            [
                'name' => 'Trần Hoàng Long',
                'email' => 'hoanglong.tran92@gmail.com',
                'phone' => '0918765432',
                'date_of_birth' => '1992-09-20',
                'address' => [
                    'recipient_name' => 'Trần Hoàng Long',
                    'recipient_phone' => '0918765432',
                    'address_line' => '45 Ngõ 165 Cầu Giấy',
                    'ward' => 'Phường Dịch Vọng',
                    'district' => 'Quận Cầu Giấy',
                    'province' => 'Hà Nội',
                ],
            ],
            [
                'name' => 'Lê Bảo Ngọc',
                'email' => 'baongoc.le95@gmail.com',
                'phone' => '0989234567',
                'date_of_birth' => '1995-11-03',
                'address' => [
                    'recipient_name' => 'Lê Bảo Ngọc',
                    'recipient_phone' => '0989234567',
                    'address_line' => '72/18 Điện Biên Phủ',
                    'ward' => 'Phường 15',
                    'district' => 'Quận Bình Thạnh',
                    'province' => 'Hồ Chí Minh',
                ],
            ],
            [
                'name' => 'Phạm Đức Anh',
                'email' => 'ducanh.pham90@gmail.com',
                'phone' => '0934567890',
                'date_of_birth' => '1990-03-18',
                'address' => [
                    'recipient_name' => 'Phạm Đức Anh',
                    'recipient_phone' => '0934567890',
                    'address_line' => '18/6 Thái Hà',
                    'ward' => 'Phường Trung Liệt',
                    'district' => 'Quận Đống Đa',
                    'province' => 'Hà Nội',
                ],
            ],
            [
                'name' => 'Vũ Hải Yến',
                'email' => 'haiyen.vu96@gmail.com',
                'phone' => '0978123456',
                'date_of_birth' => '1996-08-27',
                'address' => [
                    'recipient_name' => 'Vũ Hải Yến',
                    'recipient_phone' => '0978123456',
                    'address_line' => '102 Đường số 79',
                    'ward' => 'Phường Tân Quy',
                    'district' => 'Quận 7',
                    'province' => 'Hồ Chí Minh',
                ],
            ],
        ];

        $users = [];
        foreach ($realUsersData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'date_of_birth' => $data['date_of_birth'],
                    'password' => Hash::make('Petworld@2026'),
                    'email_verified_at' => now(),
                    'role' => 'user',
                    'status' => 'active',
                ]
            );

            Address::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'address_line' => $data['address']['address_line'],
                ],
                array_merge($data['address'], [
                    'user_id' => $user->id,
                    'is_default' => true,
                    'status' => 'active',
                ])
            );

            $users[] = $user;
        }

        // Lấy phương thức vận chuyển và thanh toán mặc định
        $shippingMethod = ShippingMethod::first();
        $paymentMethod = PaymentMethod::first();

        // 2. Kho nhận xét phong phú theo từng loại sản phẩm
        $categoryComments = [
            'thuc-an-hat' => [
                5 => [
                    'Hạt thơm giòn, bé ăn ngon miệng và tiêu hóa tốt, phân khuôn đẹp không bị tanh.',
                    'Đóng gói rất kỹ, túi có khóa zip tiện lợi, hạt có kích thước vừa miệng cún cưng.',
                    'Giao hàng nhanh trong ngày, hạn sử dụng còn rất xa. Bé nhà mình ghiền hạt này lắm.',
                    'Hàng chính hãng chuẩn xịn, mua lần 2 tại PetWorld rồi vẫn ưng ý như lần đầu.',
                    'Thành phần dinh dưỡng tốt, bé ăn được 2 tuần thấy lông mượt và hoạt bát hơn hẳn.',
                ],
                4 => [
                    'Chất lượng hạt tốt, bé ăn hợp vị. Giao hàng hơi trễ 1 chút do trời mưa nhưng bù lại đóng gói kỹ.',
                    'Hạt thơm, vừa miệng cún con. Giá dịp này khuyến mãi khá tốt.',
                ],
            ],
            'pate' => [
                5 => [
                    'Pate tươi ngon ngập sốt, mùi thơm tự nhiên, trộn với hạt là bé vét sạch đĩa.',
                    'Thịt cá xé sợi hấp dẫn, boss nhà mình kén ăn mà gặp món này là ăn hết veo trong 1 nốt nhạc.',
                    'Mua cả thùng/lốc tiết kiệm hơn nhiều, lon nguyên vẹn không bị móp méo chút nào.',
                    'Độ ẩm cao bổ sung nước rất tốt cho bé mèo ít chịu uống nước. Cho 5 sao chất lượng.',
                    'Pate mềm mịn, mùi thơm dịu dễ chịu, bé ăn xong cứ liếm mép đòi thêm.',
                ],
                4 => [
                    'Pate chất lượng ổn áp, bé thích ăn. Đóng gói cẩn thận bọc chống sốc từng lon.',
                    'Hàng chuẩn chính hãng, date xa. Sẽ tiếp tục mua thêm khi bé ăn hết.',
                ],
            ],
            'snack' => [
                5 => [
                    'Bánh xương chăm sóc răng rất tốt, cún gặm xong hơi thở thơm tho hẳn, bớt mảng bám.',
                    'Súp thưởng béo ngậy thơm ngon, dùng để dụ bé uống thuốc hay thưởng lúc huấn luyện cực nhạy.',
                    'Gói lớn tiết kiệm, thanh bánh mềm vừa phải không làm tổn thương nướu của cún con.',
                    'Món khoái khẩu của cả hai bé nhà mình, thấy cầm gói snack là mừng tíu tít.',
                    'Hàng mới tinh, thơm phức, date mới sản xuất. Mua ở PetWorld lúc nào cũng yên tâm.',
                ],
                4 => [
                    'Bé ăn rất thích, thanh bánh giòn vừa. Giá cả hợp lý so với chất lượng.',
                    'Bánh thơm và sạch sẽ, cún thích gặm lúc buồn miệng.',
                ],
            ],
            'phu-kien' => [
                5 => [
                    'Chất liệu cao cấp, đường may tỉ mỉ chắc chắn. Dùng rất bền và êm ái cho thú cưng.',
                    'Màu sắc y như hình chụp, mẫu mã sang xịn mịn. Rất đáng đồng tiền bát gạo.',
                    'Thiết kế thông minh, kích thước vừa vặn và dễ dàng vệ sinh sạch sẽ.',
                    'Hàng chuẩn Trixie xịn sò, khóa kim loại dày dặn không lo gỉ sét hay tuột chốt.',
                    'Rất hài lòng, giao hàng bọc hộp carton cứng cáp không bị trầy xước.',
                ],
                4 => [
                    'Phụ kiện hoàn thiện đẹp, cầm chắc tay. Dùng cho cún cưng rất vừa vặn.',
                    'Chất lượng tốt, đúng mô tả. Shipper giao hàng thân thiện và nhiệt tình.',
                ],
            ],
            'do-choi' => [
                5 => [
                    'Đồ chơi siêu bền, cao su đúc dẻo dai, cún nhà mình gặm cả tháng chưa hề bị rách hay sứt mẻ.',
                    'Màu sắc nổi bật, kích thích bé vận động chạy nhảy cả ngày đỡ buồn chán cắn phá đồ.',
                    'Chất liệu an toàn không có mùi nhựa hắc, bé cưng ôm ngủ suốt luôn.',
                    'Độ nảy tốt, chơi tương tác ném bắt ngoài sân rất vui. Cho 5 sao không ngần ngại.',
                    'Bé mèo vờn say sưa cả buổi chiều, đồ chơi chuyển động linh hoạt dễ thương lắm.',
                ],
                4 => [
                    'Đồ chơi đẹp, chắc chắn. Cún rất thích vờn mỗi khi chủ đi làm về.',
                    'Chất liệu cao su tốt, mềm vừa phải, bảo vệ răng cún con.',
                ],
            ],
            've-sinh-va-cham-soc' => [
                5 => [
                    'Sản phẩm khử mùi cực kỳ hiệu quả, mùi hương thảo mộc dịu nhẹ không nồng gắt.',
                    'Lông mềm mượt và lưu hương lâu suốt cả tuần, không bị ngứa da hay kích ứng.',
                    'Dung tích lớn dùng rất dôi, vòi xịt phun sương đều và mịn, cực kỳ tiện lợi.',
                    'Lược chải bấm nhả lông siêu tiện, lấy sạch lông rụng mà bé nằm lim dim tận hưởng.',
                    'Chất lượng vượt mong đợi, từ ngày dùng thấy nhà cửa sạch sẽ và thơm tho hẳn.',
                ],
                4 => [
                    'Mùi thơm dễ chịu, lành tính cho thú cưng. Chai xịt chắc chắn.',
                    'Dùng tốt, khử sạch mùi chuồng và khay cát của mèo. Rất hài lòng.',
                ],
            ],
        ];

        // 3. Duyệt toàn bộ 18 sản phẩm và tạo 6 - 10 đánh giá cho mỗi sản phẩm
        $products = Product::with(['variants' => fn($q) => $q->where('status', 'active'), 'category'])->get();

        $reviewOrderCount = 0;

        foreach ($products as $product) {
            $variants = $product->variants;
            if ($variants->isEmpty()) {
                continue;
            }

            // Xác định nhóm nhận xét phù hợp theo danh mục
            $catSlug = $product->category?->slug ?? '';
            $commentPool = $categoryComments['phu-kien'];
            if (str_contains($catSlug, 'hat') || str_contains($catSlug, 'thuc-an')) {
                $commentPool = $categoryComments['thuc-an-hat'];
            } elseif (str_contains($catSlug, 'pate')) {
                $commentPool = $categoryComments['pate'];
            } elseif (str_contains($catSlug, 'snack') || str_contains($catSlug, 'banh-thuong')) {
                $commentPool = $categoryComments['snack'];
            } elseif (str_contains($catSlug, 'do-choi')) {
                $commentPool = $categoryComments['do-choi'];
            } elseif (str_contains($catSlug, 've-sinh') || str_contains($catSlug, 'cham-soc')) {
                $commentPool = $categoryComments['ve-sinh-va-cham-soc'];
            }

            // Ngẫu nhiên từ 6 đến 10 đánh giá cho mỗi sản phẩm
            $numReviews = mt_rand(6, 10);

            for ($i = 0; $i < $numReviews; $i++) {
                $user = $users[$i % count($users)];
                $variant = $variants[$i % $variants->count()];
                $rating = (mt_rand(1, 100) <= 80) ? 5 : 4; // 80% là 5 sao, 20% là 4 sao
                $pool = $commentPool[$rating] ?? $commentPool[5];
                $commentText = $pool[$i % count($pool)];

                $daysAgo = mt_rand(2, 45);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(mt_rand(1, 23))->subMinutes(mt_rand(1, 59));

                $address = $user->addresses()->first();

                // Tạo đơn hàng completed hợp lệ
                $reviewOrderCount++;
                $orderCode = 'PW' . $createdAt->format('ymd') . sprintf('%04d', $reviewOrderCount);

                $order = Order::create([
                    'payment_code' => $orderCode,
                    'shipping_method_id' => $shippingMethod?->id,
                    'shipping_method_code' => $shippingMethod?->code ?? 'ghn_standard',
                    'payment_method_id' => $paymentMethod?->id,
                    'address_id' => $address?->id,
                    'user_id' => $user->id,
                    'recipient_name' => $address?->recipient_name ?? $user->name,
                    'recipient_phone' => $address?->recipient_phone ?? $user->phone,
                    'recipient_address' => ($address?->address_line ?? '') . ', ' . ($address?->ward ?? '') . ', ' . ($address?->district ?? '') . ', ' . ($address?->province ?? ''),
                    'delivery_area' => 'inner_city',
                    'shipping_fee' => 25000,
                    'shipping_weight_grams' => $variant->weight_grams ?? 500,
                    'shipping_fee_original' => 25000,
                    'shipping_discount' => 0,
                    'discount_amount' => 0,
                    'order_status' => 'completed',
                    'payment_status' => 'paid',
                    'total_amount' => (float) $variant->effectivePrice() + 25000,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addDays(2),
                ]);

                // Tạo OrderItem cho biến thể này
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name . ($variant->display_name ? ' (' . $variant->display_name . ')' : ''),
                    'quantity' => 1,
                    'price' => (float) $variant->effectivePrice(),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Tạo Review hợp lệ
                Review::create([
                    'user_id' => $user->id,
                    'order_item_id' => $orderItem->id,
                    'rating' => $rating,
                    'comment' => $commentText,
                    'status' => 'approved',
                    'created_at' => $createdAt->copy()->addDays(2)->addHours(mt_rand(1, 12)),
                    'updated_at' => $createdAt->copy()->addDays(2)->addHours(mt_rand(1, 12)),
                ]);
            }
        }
    }
}
