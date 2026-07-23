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
            ['email' => 'nguyenthediem2004@gmail.com'],
            [
                'name' => 'PetWorld Admin',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'status' => 'active',
            ],
        );

        $blogs = [
            [
                'category_slug' => 'cham-soc-thu-cung',
                'title' => 'Cách chăm sóc chó con mới về nhà trong 7 ngày đầu',
                'slug' => 'cach-cham-soc-cho-con-moi-ve-nha-trong-7-ngay-dau',
                'description' => 'Hướng dẫn chi tiết chăm sóc chó con mới về nhà trong 7 ngày đầu: chuẩn bị không gian nghỉ ngơi, xây dựng lịch ăn khoa học, vệ sinh cá nhân, theo dõi sức khỏe và giúp bé hòa nhập gia đình mới.',
                'image' => 'cham-soc-cho-con-moi-ve-nha.jpg',
                'sections' => [
                    [
                        'title' => '1. Chuẩn bị không gian nghỉ ngơi yên tĩnh và an toàn',
                        'paragraphs' => [
                            'Bảy ngày đầu tiên khi mới chuyển về nhà mới là giai đoạn chó con gặp nhiều bỡ ngỡ và căng thẳng nhất do thay đổi môi trường sống đột ngột. Bé phải rời xa chó mẹ và các anh chị em cùng đàn, đến một không gian lạ lẫm với nhiều mùi hương và âm thanh mới.',
                            'Bạn hãy chuẩn bị sẵn một khu vực yên tĩnh, thoáng mát vào mùa hè và ấm áp vào mùa đông. Đặt một chiếc đệm nằm êm ái, bát nước sạch riêng và vài món đồ chơi gặm an toàn. Hạn chế để nhiều người lạ đến xem hoặc làm phiền bé trong những ngày đầu để giúp chó con bớt sợ hãi.',
                        ],
                    ],
                    [
                        'title' => '2. Xây dựng lịch ăn uống định kỳ và dinh dưỡng phù hợp',
                        'paragraphs' => [
                            'Trong những ngày đầu, tuyệt đối không nên thay đổi loại thức ăn đột ngột vì hệ tiêu hóa của chó con còn rất non nớt và nhạy cảm. Bạn nên duy trì loại thức ăn khô hoặc pate mà chó con đang quen dùng từ chủ cũ hoặc trại giống.',
                            'Chia nhỏ khẩu phần thành 3-4 bữa ăn trong ngày vào các khung giờ cố định. Việc này giúp dạ dày bé tiêu hóa dễ dàng hơn, tránh tình trạng hạ đường huyết hoặc chướng bụng. Đảm bảo luôn có sẵn nước sạch mát cạnh bát ăn của bé.',
                        ],
                    ],
                    [
                        'title' => '3. Hướng dẫn đi vệ sinh đúng chỗ ngay từ ngày đầu',
                        'paragraphs' => [
                            'Tập thói quen đi vệ sinh đúng chỗ ngay từ khi bé vừa bước chân về nhà sẽ giúp bạn tiết kiệm rất nhiều thời gian làm sạch sau này. Hãy đặt khay đi vệ sinh hoặc tã lót ở góc cố định, đưa bé đến đó sau khi thức dậy, sau bữa ăn khoảng 15-20 phút hoặc sau khi chơi đùa.',
                            'Khi bé đi vệ sinh đúng nơi quy định, hãy lập tức khen ngợi bằng giọng điệu vui vẻ và thưởng một chút hạt hoặc bánh thưởng (treat). Nếu bé lỡ đi sai chỗ, hãy dọn dẹp sạch sẽ và khử mùi hôi mà không nên la mắng hay đánh phạt làm bé hoảng sợ.',
                        ],
                    ],
                    [
                        'title' => '4. Theo dõi sát sao tình trạng sức khỏe và biểu hiện tâm lý',
                        'paragraphs' => [
                            'Hãy quan sát các biểu hiện hàng ngày của chó con như mức độ năng động, thèm ăn, chất lượng phân và nhịp thở. Chó con mới về nhà có thể sủa hoặc khóc vào đêm đầu tiên do nhớ nhà cũ.',
                            'Mẹo nhỏ là bạn hãy đặt một chiếc khăn quen mùi hoặc bình nước ấm bọc trong chăn cạnh chỗ ngủ để mô phỏng hơi ấm của chó mẹ, giúp bé cảm thấy an tâm và dễ đi vào giấc ngủ hơn.',
                        ],
                    ],
                    [
                        'title' => '5. Lịch tiêm phòng, tẩy giun và kiểm tra sức khỏe tại thú y',
                        'paragraphs' => [
                            'Sau khoảng 2-3 ngày khi chó con đã quen dần với nhà mới và ăn uống ổn định, hãy đưa bé đến phòng khám thú y uy tín để kiểm tra sức khỏe tổng quát.',
                            'Bác sĩ sẽ kiểm tra tai, mắt, mũi, nghe tim phổi và tư vấn chi tiết lịch tiêm vắc xin phòng bệnh truyền nhiễm (như Care, Parvo), lịch tẩy giun định kỳ và lập sổ theo dõi sức khỏe cá nhân cho bé.',
                        ],
                    ],
                    [
                        'title' => '6. Tạo môi trường thân thiện và cho bé làm quen với thành viên',
                        'paragraphs' => [
                            'Hãy giới thiệu các thành viên trong gia đình với chó con một cách nhẹ nhàng, không làm bé giật mình hay hoảng sợ. Hãy hướng dẫn trẻ nhỏ cách bế và vuốt ve bé nhẹ nhàng, tránh la hóc hoặc rượt đuổi khi bé đang ăn hoặc ngủ.',
                        ],
                    ],
                    [
                        'title' => '7. Tổng kết và lời khuyên từ chuyên gia PetWorld',
                        'paragraphs' => [
                            'Sự kiên nhẫn, tình yêu thương và sự chuẩn bị chu đáo chính là chìa khóa giúp chú chó con của bạn vượt qua tuần đầu tiên một cách êm đẹp. Đội ngũ PetWorld luôn sẵn sàng đồng hành cùng bạn trên hành trình chăm sóc và nuôi dưỡng bé cưng phát triển khỏe mạnh!',
                        ],
                    ],
                ],
                'view_count' => 42,
            ],
            [
                'category_slug' => 'dinh-duong',
                'title' => 'Chọn thức ăn hạt cho mèo trưởng thành theo nhu cầu dinh dưỡng',
                'slug' => 'chon-thuc-an-hat-cho-meo-truong-thanh-theo-nhu-cau-dinh-duong',
                'description' => 'Kinh nghiệm chọn thức ăn hạt cho mèo trưởng thành dựa trên độ tuổi, cân nặng, mức độ vận động, tình trạng lông da và khả năng tiêu hóa.',
                'image' => 'chon-thuc-an-hat-cho-meo-truong-thanh.jpg',
                'sections' => [
                    [
                        'title' => '1. Hiểu rõ nhu cầu dinh dưỡng đặc thù của mèo trưởng thành',
                        'paragraphs' => [
                            'Mèo là động vật ăn thịt bắt buộc, cần nguồn đạm động vật chất lượng cao để duy trì cơ bắp, hệ miễn dịch và cung cấp năng lượng sống hàng ngày. Khi bước vào giai đoạn trưởng thành (từ 12 tháng tuổi trở lên), nhu cầu năng lượng của mèo ổn định hơn so với lúc nhỏ.',
                            'Khẩu phần ăn của mèo trưởng thành cần đảm bảo sự cân bằng giữa đạm, chất béo, taurine, omega-3, omega-6 cùng các vitamin khoáng chất thiết yếu để nuôi dưỡng làn da và bộ lông bóng mượt.',
                        ],
                    ],
                    [
                        'title' => '2. Phân loại hạt theo lối sống và thể trạng của mèo',
                        'paragraphs' => [
                            'Không phải loại hạt nào cũng phù hợp với mọi chú mèo. Mèo nuôi hoàn toàn trong nhà (indoor) ít vận động cần công thức kiểm soát calo và tăng cường chất xơ tự nhiên giúp đẩy búi lông ra ngoài qua đường tiêu hóa.',
                            'Trong khi đó, mèo năng động hay di chuyển hoặc mèo đực đã triệt sản sẽ cần các dòng hạt chuyên biệt giúp bảo vệ hệ tiết niệu, kiểm soát pH nước tiểu và duy trì vóc dáng cân đối.',
                        ],
                    ],
                    [
                        'title' => '3. Cách đọc và phân tích bảng thành phần ghi trên bao bì',
                        'paragraphs' => [
                            'Hãy luôn ưu tiên các loại hạt có thành phần đứng đầu danh sách là nguồn thịt tươi nguyên chất (như thịt gà, thịt bò, cá hồi...) thay vì các phụ phẩm chế biến hoặc ngũ cốc rẻ tiền như ngô, đậu nành.',
                            'Ngoài ra, hãy chú ý đến hàm lượng đạm tối thiểu (thường từ 30% đến 38% cho mèo trưởng thành), hàm lượng tro thô dưới 8% và các dưỡng chất bổ sung hữu ích như probiotics hỗ trợ đường ruột.',
                        ],
                    ],
                    [
                        'title' => '4. Tính toán khẩu phần ăn hàng ngày tránh béo phì',
                        'paragraphs' => [
                            'Béo phì là nguyên nhân hàng đầu dẫn đến các bệnh lý nguy hiểm về khớp, tiểu đường và tim mạch ở mèo nuôi trong nhà. Hãy tham khảo bảng hướng dẫn cho ăn theo cân nặng ghi trên bao bì sản phẩm.',
                            'Kết hợp sử dụng chén đong hoặc cân tiểu ly điện tử để cân lượng hạt chính xác cho từng bữa ăn thay vì ước lượng cảm tính bằng tay.',
                        ],
                    ],
                    [
                        'title' => '5. Phương pháp chuyển đổi thức ăn hạt an toàn trong 10 ngày',
                        'paragraphs' => [
                            'Dạ dày mèo rất nhạy cảm với sự thay đổi thức ăn đột ngột. Để tránh tình trạng nôn mửa, tiêu chảy hoặc bỏ ăn, bạn hãy áp dụng quy tắc chuyển đổi từ từ trong 7 đến 10 ngày.',
                            'Trộn hạt mới vào hạt cũ theo tỷ lệ: Ngày 1-3 (25% hạt mới), Ngày 4-6 (50% hạt mới), Ngày 7-9 (75% hạt mới) và chính thức chuyển sang 100% hạt mới vào Ngày thứ 10.',
                        ],
                    ],
                    [
                        'title' => '6. Bổ sung nước và kết hợp dinh dưỡng đa dạng',
                        'paragraphs' => [
                            'Do thức ăn hạt có độ ẩm rất thấp (chỉ khoảng 8-10%), bạn hãy luôn bố trí đài phun nước hoặc bát nước sạch ở nhiều góc trong nhà để kích thích mèo uống nước nhiều hơn.',
                            'Bạn cũng có thể kết hợp thêm thức ăn ướt (pate) hoặc nước hầm xương không gia vị vào các bữa phụ để bảo vệ sức khỏe hệ tiết niệu và thận cho bé.',
                        ],
                    ],
                    [
                        'title' => '7. Khuyến nghị và dặn dò từ chuyên gia PetWorld',
                        'paragraphs' => [
                            'Việc đầu tư một sản phẩm thức ăn hạt chất lượng cao ngay từ đầu sẽ giúp bé mèo của bạn luôn tràn đầy năng lượng, sở hữu bộ lông bóng mượt và hạn chế tối đa các chi phí y tế không mong muốn trong tương lai.',
                        ],
                    ],
                ],
                'view_count' => 38,
            ],
            [
                'category_slug' => 'kinh-nghiem-mua-sam',
                'title' => 'Kinh nghiệm mua phụ kiện an toàn cho chó mèo',
                'slug' => 'kinh-nghiem-mua-phu-kien-an-toan-cho-cho-meo',
                'description' => 'Checklist mua phụ kiện cho chó mèo: chất liệu, kích thước, độ bền, cách vệ sinh và các dấu hiệu cần thay mới để đảm bảo an toàn tuyệt đối.',
                'image' => 'kinh-nghiem-mua-phu-kien-an-toan.jpg',
                'sections' => [
                    [
                        'title' => '1. Đo đạc kích thước cơ thể chuẩn xác trước khi mua sắm',
                        'paragraphs' => [
                            'Dù là chọn mua vòng cổ, yếm dắt, quần áo hay balo vận chuyển, bước đầu tiên và quan trọng nhất là đo chính xác vòng cổ, vòng ngực và chiều dài lưng của thú cưng.',
                            'Một món phụ kiện quá chật sẽ gây siết cổ, trầy xước da và khó thở, trong khi đồ quá rộng có thể khiến bé bị tuột và gặp nguy hiểm khi đi dạo ngoài đường.',
                        ],
                    ],
                    [
                        'title' => '2. Ưu tiên chất liệu cao cấp, không độc hại và dễ làm sạch',
                        'paragraphs' => [
                            'Hãy chọn bát ăn bằng inox 304, gốm sứ tráng men hoặc nhựa cao cấp không chứa chất BPA độc hại. Bát đĩa kém chất lượng có thể gây dị ứng cằm (mụn đen ở cằm mèo).',
                            'Với đồ chơi và dây dắt, chất liệu cao su tự nhiên, vải nylon chịu lực hoặc da thật luôn là sự lựa chọn hàng đầu nhờ độ bền cao và an toàn khi thú cưng gặm nhấm.',
                        ],
                    ],
                    [
                        'title' => '3. Kiểm tra kỹ lưỡng các chi tiết khóa cài và đường may',
                        'paragraphs' => [
                            'Trước khi quyết định mua dây dắt hay balo, bạn hãy kiểm tra độ chắc chắn của móc khóa kim loại, độ trơn chỉnh của dây kéo và độ dày của các đường chỉ may chịu lực.',
                            'Những chú chó thích kéo hoặc nhảy mạnh cần các loại yếm dắt bọc đệm êm và móc khóa kép an toàn để phân bổ lực đều khắp ngực.',
                        ],
                    ],
                    [
                        'title' => '4. Chọn đồ chơi phù hợp với lực cắn và tính cách của thú cưng',
                        'paragraphs' => [
                            'Những bé chó ở tuổi ngứa răng cần đồ chơi cao su dẻo dai để gặm nhấm mà không bị vỡ vụn gây hóc. Mèo cưng lại mê các dòng đồ chơi tương tác như gậy câu mèo, chuột nhồi bông chứa cỏ mèo (catnip) giúp giải tỏa căng thẳng hiệu quả.',
                        ],
                    ],
                    [
                        'title' => '5. Vệ sinh và bảo quản phụ kiện định kỳ đúng cách',
                        'paragraphs' => [
                            'Bát ăn uống cần được rửa sạch mỗi ngày để tránh vi khuẩn tích tụ. Khăn trải, đệm nằm và dây dắt nên được giặt sạch bằng xà phòng dịu nhẹ hàng tuần và phơi dưới nắng mặt trời để khử trùng tự nhiên.',
                        ],
                    ],
                    [
                        'title' => '6. Những lưu ý an toàn từ cửa hàng PetWorld',
                        'paragraphs' => [
                            'Thường xuyên kiểm tra tình trạng phụ kiện và thay mới ngay khi phát hiện dấu hiệu sờn đứt, nứt vỡ hoặc chốt khóa bị lỏng. PetWorld luôn sẵn sàng tư vấn giúp bạn chọn lựa những phụ kiện phù hợp nhất cho bé yêu!',
                        ],
                    ],
                ],
                'view_count' => 34,
            ],
            [
                'category_slug' => 'cham-soc-thu-cung',
                'title' => 'Lịch tắm và vệ sinh lông cho thú cưng tại nhà',
                'slug' => 'lich-tam-va-ve-sinh-long-cho-thu-cung-tai-nha',
                'description' => 'Gợi ý lịch tắm, chải lông, vệ sinh tai móng và chăm sóc da lông cho thú cưng tại nhà theo từng nhu cầu sinh hoạt chuẩn spa.',
                'image' => 'lich-tam-va-ve-sinh-long-thu-cung.jpg',
                'sections' => [
                    [
                        'title' => '1. Xác định tần suất tắm phù hợp theo chủng loại và môi trường',
                        'paragraphs' => [
                            'Chó vận động nhiều ngoài trời có thể tắm 1-2 tuần/lần, trong khi mèo có thói quen tự chải chuốt chỉ cần tắm 1-2 tháng/lần hoặc khi lông bị bẩn.',
                            'Tắm quá nhiều sẽ làm mất đi lớp dầu tự nhiên bảo vệ da, khiến da bị khô, ngứa và rụng lông nhiều hơn.',
                        ],
                    ],
                    [
                        'title' => '2. Chuẩn bị đầy đủ dụng cụ vệ sinh trước khi bắt đầu tắm',
                        'paragraphs' => [
                            'Hãy chuẩn bị sẵn sữa tắm chuyên dụng cho thú cưng, khăn tắm hút nước tốt, máy sấy lông, lược chải rụng, bông lau tai và kìm cắt móng.',
                            'Đảm bảo phòng tắm kín gió, nhiệt độ nước ấm vừa phải dễ chịu để thú cưng không bị giật mình hoặc cảm lạnh.',
                        ],
                    ],
                    [
                        'title' => '3. Các bước tắm nhẹ nhàng giúp bé không bị hoảng sợ',
                        'paragraphs' => [
                            'Xả nước nhẹ nhàng từ chân lên thân bé, tránh xịt nước trực tiếp vào mắt, mũi và tai. Bôi sữa tắm đã pha loãng, massage nhẹ nhàng toàn thân trong 5-7 phút rồi xả lại bằng nước sạch cho đến khi hết bọt hẳn.',
                        ],
                    ],
                    [
                        'title' => '4. Sấy khô lông và chải lông loại bỏ lông thừa',
                        'paragraphs' => [
                            'Lau bớt nước bằng khăn bông, sau đó dùng máy sấy ở chế độ ấm vừa hoặc mát để sấy khô hoàn toàn tận chân lông. Dùng lược chải chuyên dụng chải theo chiều lông mọc để gỡ rối và loại bỏ sợi lông rụng còn sót lại.',
                        ],
                    ],
                    [
                        'title' => '5. Vệ sinh tai, cắt móng và làm sạch kẽ chân',
                        'paragraphs' => [
                            'Dùng dung dịch rửa tai chuyên dụng nhỏ vào và lau sạch bằng bông gạc mềm. Cắt móng cẩn thận tránh phạm vào vạch máu màu hồng. Cuối cùng, thoa một chút kem dưỡng ẩm kẽ chân nếu thấy da chân bé bị khô ráp.',
                        ],
                    ],
                    [
                        'title' => '6. Lời khuyên chăm sóc da lông chuyên sâu từ PetWorld',
                        'paragraphs' => [
                            'Kết hợp chải lông hàng ngày và bổ sung dầu cá Salmon Oil vào khẩu phần ăn sẽ giúp bộ lông của thú cưng luôn mềm mượt, bớt rụng và khỏe mạnh rực rỡ.',
                        ],
                    ],
                ],
                'view_count' => 26,
            ],
            [
                'category_slug' => 'dinh-duong',
                'title' => 'Cách kết hợp pate và thức ăn hạt trong bữa ăn hằng ngày',
                'slug' => 'cach-ket-hop-pate-va-thuc-an-hat-trong-bua-an-hang-ngay',
                'description' => 'Hướng dẫn kết hợp pate và thức ăn hạt để tăng độ ngon miệng, bổ sung độ ẩm và vẫn kiểm soát tổng năng lượng mỗi ngày.',
                'image' => 'ket-hop-pate-va-thuc-an-hat.jpg',
                'sections' => [
                    [
                        'title' => '1. Vì sao nên kết hợp thức ăn ướt (Pate) và thức ăn khô (Hạt)?',
                        'paragraphs' => [
                            'Việc kết hợp pate và hạt (Mix Feeding) mang lại lợi ích kép tuyệt vời cho sức khỏe chó mèo. Thức ăn hạt khô giúp rèn luyện cơ hàm, làm sạch mảng bám trên răng và tiện lợi bảo quản.',
                            'Trong khi đó, pate tươi lại bổ sung hàm lượng nước dồi dào, mang đến hương vị thơm ngon hấp dẫn khó cưỡng và giúp phòng ngừa hiệu quả các bệnh về sỏi thận, viêm đường tiết niệu.',
                        ],
                    ],
                    [
                        'title' => '2. Tỷ lệ vàng khi trộn Pate và Hạt cho từng bữa ăn',
                        'paragraphs' => [
                            'Tỷ lệ khuyến nghị phổ biến nhất là 70% lượng calo từ hạt và 30% calo từ pate.',
                            'Bạn có thể trộn trực tiếp pate vào hạt cho thơm ngon, hoặc cho ăn riêng hạt vào buổi sáng và pate vào buổi tối để làm phong phú thực đơn hàng ngày.',
                        ],
                    ],
                    [
                        'title' => '3. Các nguyên tắc vệ sinh và bảo quản Pate sau khi mở hộp',
                        'paragraphs' => [
                            'Pate đã mở hộp cần được đậy nắp kín hoặc bọc màng bọc thực phẩm và bảo quản trong ngăn mát tủ lạnh từ 2-3 ngày.',
                            'Trước khi cho bé ăn, hãy lấy pate ra ngoài phòng khoảng 15-20 phút cho bớt lạnh hoặc quay vi sóng nhẹ 5 giây để kích thích mùi thơm.',
                        ],
                    ],
                    [
                        'title' => '4. Giải quyết tình trạng thú cưng kén ăn hoặc bỏ bữa',
                        'paragraphs' => [
                            'Nếu bé mèo hoặc chó cưng lười ăn hạt, bạn có thể trộn một thìa pate ấm cùng chút nước ấm vào bát hạt. Mùi thơm hấp dẫn từ pate ấm sẽ kích thích khứu giác khiến bé thưởng thức bữa ăn ngon lành.',
                        ],
                    ],
                    [
                        'title' => '5. Những sai lầm phổ biến chủ nuôi thường gặp phải',
                        'paragraphs' => [
                            'Sai lầm lớn nhất là bổ sung pate nhưng giữ nguyên lượng hạt, dẫn đến tổng năng lượng nạp vào quá cao gây tăng cân béo phì. Ngoài ra, việc để pate ngoài nhiệt độ phòng quá 4 tiếng có thể làm ôi thiu và nảy sinh vi khuẩn.',
                        ],
                    ],
                    [
                        'title' => '6. Tổng kết dinh dưỡng từ chuyên gia PetWorld',
                        'paragraphs' => [
                            'Một chế độ ăn kết hợp khoa học sẽ giúp chó mèo luôn hào hứng với mỗi bữa ăn, phát triển cân đối và duy trì thể trạng sung mãn suốt nhiều năm đồng hành cùng bạn!',
                        ],
                    ],
                ],
                'view_count' => 22,
            ],
        ];

        foreach ($blogs as $blog) {
            $category = BlogCategory::where('slug', $blog['category_slug'])->firstOrFail();

            $contentHtml = $this->buildArticleHtml($blog['sections']);

            Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                [
                    'blog_category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $blog['title'],
                    'description' => $blog['description'],
                    'content' => $contentHtml,
                    'view_count' => $blog['view_count'],
                    'image' => 'blogs/'.$blog['image'],
                    'status' => 'active',
                ],
            );
        }
    }

    private function buildArticleHtml(array $sections): string
    {
        $html = '<article class="article-content-wrapper">';

        foreach ($sections as $section) {
            $rawTitle = htmlspecialchars($section['title']);

            if (preg_match('/^(\d+)\.\s*(.*)$/', $rawTitle, $matches)) {
                $num = $matches[1];
                $titleText = $matches[2];
                $titleHtml = '<span class="heading-num-badge">'.$num.'</span><span>'.$titleText.'</span>';
            } else {
                $titleHtml = '<span>'.$rawTitle.'</span>';
            }

            $html .= '<div class="article-section-block" style="margin-bottom: 24px;">';
            $html .= '<h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 28px; margin-bottom: 12px; line-height: 1.4; display: flex; align-items: center;">'.$titleHtml.'</h2>';

            foreach ($section['paragraphs'] as $p) {
                $content = htmlspecialchars($p);
                $html .= '<p style="font-size: 15.5px; line-height: 1.75; color: #334155; margin-top: 0; margin-bottom: 12px;">'.$content.'</p>';
            }

            $html .= '</div>';
        }

        $html .= '</article>';

        return $html;
    }
}
