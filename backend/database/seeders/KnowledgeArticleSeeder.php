<?php

namespace Database\Seeders;

use App\Models\KnowledgeArticle;
use Illuminate\Database\Seeder;

class KnowledgeArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'slug' => 'huong-dan-giao-hang-petworld',
                'title' => 'Hướng dẫn giao hàng và theo dõi đơn hàng',
                'category' => 'shipping',
                'summary' => 'Khách chọn phương thức giao hàng tại bước thanh toán. Phí, thời gian dự kiến và tình trạng đơn được hiển thị theo đơn hàng cụ thể.',
                'questions' => [
                    'Bao lâu thì tôi nhận được đơn hàng?',
                    'Tôi có thể theo dõi tình trạng đơn ở đâu?',
                    'Phí giao hàng được tính như thế nào?',
                    'Đơn hàng của tôi đang giao thì khi nào nhận được?',
                ],
                'content' => <<<'TEXT'
Phạm vi áp dụng
Hướng dẫn này áp dụng cho các đơn hàng được đặt thành công trên PetWorld.

Chọn phương thức giao hàng
Tại bước thanh toán, khách hàng chọn phương thức giao hàng đang khả dụng cho địa chỉ nhận. Phí giao hàng và thông tin dự kiến được hiển thị trước khi xác nhận đơn. Các thông tin này có thể thay đổi theo khu vực nhận hàng, phương thức đã chọn và trạng thái phục vụ tại thời điểm đặt đơn.

Theo dõi đơn hàng
Sau khi đơn được tạo, khách hàng có thể đăng nhập để xem danh sách đơn hàng và tình trạng xử lý. Trạng thái đơn thường thể hiện theo các bước: chờ xác nhận, đã xác nhận, đang giao hàng, hoàn thành hoặc đã hủy. Khi cần hỗ trợ, hãy chuẩn bị mã đơn hàng để PetWorld kiểm tra nhanh hơn.

Lưu ý khi nhận hàng
Khách hàng nên kiểm tra thông tin người nhận, địa chỉ và số điện thoại trước khi xác nhận đơn. Nếu cần thay đổi thông tin giao nhận, hãy liên hệ PetWorld sớm nhất có thể; khả năng hỗ trợ phụ thuộc vào việc đơn hàng đã được xử lý hoặc bàn giao cho đơn vị vận chuyển hay chưa.

Trường hợp giao chậm
Thời gian giao thực tế có thể bị ảnh hưởng bởi thời tiết, ngày lễ, khu vực nhận hàng hoặc sự cố vận hành của đơn vị vận chuyển. PetWorld sẽ hỗ trợ kiểm tra trạng thái đơn khi khách hàng cung cấp mã đơn và thông tin liên hệ phù hợp.
TEXT,
            ],
            [
                'slug' => 'huong-dan-thanh-toan-petworld',
                'title' => 'Hướng dẫn thanh toán đơn hàng',
                'category' => 'payment',
                'summary' => 'Phương thức thanh toán khả dụng được hiển thị ở bước thanh toán. Với chuyển khoản, khách hàng cần dùng đúng nội dung thanh toán của đơn để hệ thống đối soát.',
                'questions' => [
                    'PetWorld hỗ trợ những cách thanh toán nào?',
                    'Tôi đã chuyển khoản nhưng đơn chưa cập nhật thanh toán thì sao?',
                    'Nội dung chuyển khoản của đơn hàng là gì?',
                    'Tôi có thể thanh toán khi nhận hàng không?',
                ],
                'content' => <<<'TEXT'
Phương thức thanh toán
PetWorld chỉ hiển thị các phương thức thanh toán đang khả dụng tại bước thanh toán. Khách hàng cần chọn một phương thức trước khi đặt đơn và kiểm tra kỹ tổng tiền, phí vận chuyển, giảm giá cùng thông tin người nhận.

Thanh toán khi nhận hàng
Nếu phương thức thanh toán khi nhận hàng được hiển thị, khách hàng thanh toán theo số tiền của đơn tại thời điểm nhận hàng. Trước khi thanh toán, hãy kiểm tra mã đơn và thông tin đơn hàng để bảo đảm giao dịch đúng yêu cầu.

Chuyển khoản ngân hàng
Khi chọn chuyển khoản, hệ thống cung cấp thông tin cần thiết cho đơn hàng. Khách hàng nên chuyển đúng số tiền và sử dụng chính xác nội dung thanh toán được hiển thị để hệ thống có thể đối soát. Không gửi thông tin thanh toán qua các kênh không chính thức.

Đơn chưa được xác nhận thanh toán
Sau khi thanh toán, trạng thái đơn có thể cần thời gian để cập nhật. Nếu trạng thái chưa thay đổi sau khoảng thời gian hợp lý, khách hàng hãy liên hệ PetWorld và cung cấp mã đơn, thời điểm thanh toán cùng thông tin giao dịch cần thiết. Không tạo nhiều đơn giống nhau chỉ vì trạng thái thanh toán chưa cập nhật.
TEXT,
            ],
            [
                'slug' => 'quy-trinh-yeu-cau-doi-tra',
                'title' => 'Quy trình gửi yêu cầu đổi trả và hỗ trợ sau mua',
                'category' => 'returns',
                'summary' => 'Khi cần đổi trả hoặc hỗ trợ sau mua, khách hàng nên liên hệ PetWorld kèm mã đơn, hình ảnh thực tế và mô tả vấn đề để được kiểm tra theo chính sách áp dụng.',
                'questions' => [
                    'Tôi muốn đổi hoặc trả sản phẩm thì phải làm sao?',
                    'Cần chuẩn bị thông tin gì khi yêu cầu đổi trả?',
                    'Sản phẩm bị lỗi khi nhận hàng thì xử lý như thế nào?',
                    'Khi nào tôi nhận được phản hồi về yêu cầu đổi trả?',
                ],
                'content' => <<<'TEXT'
Khi nào cần gửi yêu cầu hỗ trợ
Khách hàng nên liên hệ PetWorld ngay khi phát hiện sản phẩm giao thiếu, hư hỏng, không đúng thông tin đơn hàng hoặc có vấn đề cần làm rõ sau mua. Việc liên hệ sớm giúp đội ngũ kiểm tra đơn hàng và hướng dẫn phương án phù hợp.

Thông tin cần chuẩn bị
Để yêu cầu được xử lý nhanh, khách hàng cần cung cấp mã đơn hàng, tên hoặc số điện thoại đặt hàng, mô tả rõ tình trạng sản phẩm và hình ảnh hoặc video liên quan nếu có. Không tự ý gửi trả sản phẩm khi chưa nhận được hướng dẫn từ PetWorld.

Quy trình xử lý
PetWorld tiếp nhận thông tin, đối chiếu đơn hàng và kiểm tra điều kiện áp dụng của trường hợp cụ thể. Khi cần, đội ngũ sẽ trao đổi thêm để xác minh tình trạng sản phẩm, hướng dẫn cách bàn giao hoặc đề xuất phương án hỗ trợ. Kết quả xử lý phụ thuộc vào tình trạng thực tế và chính sách được xác nhận tại thời điểm mua hàng.

Lưu ý
Giữ lại bao bì, phụ kiện, quà tặng kèm và chứng từ liên quan nếu có. Đây là bài mẫu; quản trị viên cần bổ sung thời hạn, điều kiện đổi trả và phương thức hoàn tiền chính thức trước khi xuất bản.
TEXT,
            ],
            [
                'slug' => 'huong-dan-su-dung-voucher',
                'title' => 'Hướng dẫn áp dụng voucher và ưu đãi',
                'category' => 'voucher',
                'summary' => 'Voucher được nhập tại bước thanh toán và chỉ áp dụng khi đáp ứng điều kiện của chương trình, bao gồm thời hạn, giá trị đơn tối thiểu, số lượt dùng và phạm vi áp dụng.',
                'questions' => [
                    'Tôi nhập mã voucher ở đâu?',
                    'Vì sao voucher của tôi không áp dụng được?',
                    'Voucher có dùng chung với khuyến mãi khác không?',
                    'Tôi đã dùng voucher nhưng hủy đơn thì mã có được hoàn lại không?',
                ],
                'content' => <<<'TEXT'
Cách áp dụng voucher
Tại bước thanh toán, khách hàng nhập mã voucher vào khu vực áp dụng ưu đãi rồi kiểm tra lại số tiền giảm trước khi xác nhận đơn. Một voucher chỉ được áp dụng khi hệ thống thông báo thành công; việc nhập mã không đồng nghĩa voucher đã được giữ chỗ cho đơn hàng.

Điều kiện của voucher
Mỗi voucher có điều kiện riêng như thời gian hiệu lực, giá trị đơn tối thiểu, số lượt sử dụng, danh mục hoặc sản phẩm được áp dụng. Khách hàng cần đọc thông tin ưu đãi hiển thị cùng mã voucher và kiểm tra tổng tiền đơn hàng sau khi áp dụng.

Trường hợp không áp dụng được
Voucher có thể không hợp lệ vì đã hết hạn, chưa đến thời gian sử dụng, không đạt giá trị đơn tối thiểu, đã hết lượt dùng, không áp dụng cho sản phẩm trong giỏ hoặc đã được dùng trước đó. Nếu mã vẫn không hoạt động dù đủ điều kiện hiển thị, hãy liên hệ PetWorld kèm mã voucher và ảnh màn hình tại bước thanh toán.

Lưu ý
Không chia sẻ mã hoặc thông tin đơn hàng cho người lạ. Việc hoàn lại voucher khi đơn bị hủy phụ thuộc vào điều kiện của chương trình và trạng thái xử lý đơn; quản trị viên cần xác nhận chính sách chính thức trước khi xuất bản bài này.
TEXT,
            ],
            [
                'slug' => 'huong-dan-lien-he-ho-tro-petworld',
                'title' => 'Hướng dẫn liên hệ hỗ trợ PetWorld',
                'category' => 'contact',
                'summary' => 'Khi liên hệ hỗ trợ, khách hàng nên cung cấp mã đơn hoặc thông tin tài khoản cùng mô tả ngắn gọn để PetWorld chuyển yêu cầu đến đúng bộ phận.',
                'questions' => [
                    'Tôi cần liên hệ PetWorld bằng cách nào?',
                    'Tôi cần cung cấp gì khi hỏi về đơn hàng?',
                    'Khi nào PetWorld phản hồi yêu cầu của tôi?',
                    'Tôi muốn báo lỗi sản phẩm hoặc thanh toán thì liên hệ ở đâu?',
                ],
                'content' => <<<'TEXT'
Khi nào nên liên hệ hỗ trợ
Khách hàng có thể liên hệ PetWorld khi cần hỗ trợ về đơn hàng, thanh toán, vận chuyển, voucher, sản phẩm hoặc vấn đề sau mua. Hãy sử dụng các kênh liên hệ chính thức được công bố trên website hoặc trong thông tin đơn hàng.

Thông tin nên cung cấp
Với các yêu cầu liên quan đến đơn hàng, hãy chuẩn bị mã đơn, tên người nhận, số điện thoại hoặc email đã dùng khi đặt hàng. Với vấn đề sản phẩm, mô tả rõ tình trạng và gửi kèm hình ảnh hoặc video nếu có. Không gửi mật khẩu, mã OTP hoặc thông tin thanh toán nhạy cảm cho bất kỳ ai.

Giúp yêu cầu được xử lý nhanh hơn
Nêu ngắn gọn vấn đề, thời điểm xảy ra và kết quả mong muốn. Nếu đã trao đổi trước đó, hãy giữ lại thông tin liên quan để đội ngũ hỗ trợ có thể tiếp tục xử lý mà không phải hỏi lại từ đầu.

Lưu ý trước khi xuất bản
Quản trị viên cần bổ sung hotline, email, giờ hỗ trợ và liên kết kênh liên hệ chính thức trước khi xuất bản. Không công bố thông tin liên hệ chưa được xác thực.
TEXT,
            ],
        ];

        foreach ($articles as $article) {
            KnowledgeArticle::firstOrCreate(
                ['slug' => $article['slug']],
                [...$article, 'status' => 'draft', 'version' => 1, 'published_at' => null],
            );
        }
    }
}
