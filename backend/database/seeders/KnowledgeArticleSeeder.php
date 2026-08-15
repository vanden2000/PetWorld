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
                'content' => <<<'HTML'
<h2>Phạm vi áp dụng</h2>
<p>Hướng dẫn này áp dụng cho các đơn hàng được đặt thành công trên PetWorld.</p>

<h2>Chọn phương thức giao hàng</h2>
<p>Tại bước thanh toán, khách hàng chọn phương thức giao hàng đang khả dụng cho địa chỉ nhận. Phí giao hàng và thông tin dự kiến được hiển thị trước khi xác nhận đơn. Các thông tin này có thể thay đổi theo khu vực nhận hàng, phương thức đã chọn và trạng thái phục vụ tại thời điểm đặt đơn.</p>

<h2>Theo dõi đơn hàng</h2>
<p>Sau khi đơn được tạo, khách hàng có thể đăng nhập để xem danh sách đơn hàng và tình trạng xử lý. Trạng thái đơn thường thể hiện theo các bước: chờ xác nhận, đã xác nhận, đang giao hàng, hoàn thành hoặc đã hủy. Khi cần hỗ trợ, hãy chuẩn bị mã đơn hàng để PetWorld kiểm tra nhanh hơn.</p>

<h2>Lưu ý khi nhận hàng</h2>
<p>Khách hàng nên kiểm tra thông tin người nhận, địa chỉ và số điện thoại trước khi xác nhận đơn. Nếu cần thay đổi thông tin giao nhận, hãy liên hệ PetWorld sớm nhất có thể; khả năng hỗ trợ phụ thuộc vào việc đơn hàng đã được xử lý hoặc bàn giao cho đơn vị vận chuyển hay chưa.</p>

<h2>Trường hợp giao chậm</h2>
<p>Thời gian giao thực tế có thể bị ảnh hưởng bởi thời tiết, ngày lễ, khu vực nhận hàng hoặc sự cố vận hành của đơn vị vận chuyển. PetWorld sẽ hỗ trợ kiểm tra trạng thái đơn khi khách hàng cung cấp mã đơn và thông tin liên hệ phù hợp.</p>
HTML,
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
                'content' => <<<'HTML'
<h2>Phương thức thanh toán</h2>
<p>PetWorld chỉ hiển thị các phương thức thanh toán đang khả dụng tại bước thanh toán. Khách hàng cần chọn một phương thức trước khi đặt đơn và kiểm tra kỹ tổng tiền, phí vận chuyển, giảm giá cùng thông tin người nhận.</p>

<h2>Thanh toán khi nhận hàng</h2>
<p>Nếu phương thức thanh toán khi nhận hàng được hiển thị, khách hàng thanh toán theo số tiền của đơn tại thời điểm nhận hàng. Trước khi thanh toán, hãy kiểm tra mã đơn và thông tin đơn hàng để bảo đảm giao dịch đúng yêu cầu.</p>

<h2>Chuyển khoản ngân hàng</h2>
<p>Khi chọn chuyển khoản, hệ thống cung cấp thông tin cần thiết cho đơn hàng. Khách hàng nên chuyển đúng số tiền và sử dụng chính xác nội dung thanh toán được hiển thị để hệ thống có thể đối soát. Không gửi thông tin thanh toán qua các kênh không chính thức.</p>

<h2>Đơn chưa được xác nhận thanh toán</h2>
<p>Sau khi thanh toán, trạng thái đơn có thể cần thời gian để cập nhật. Nếu trạng thái chưa thay đổi sau khoảng thời gian hợp lý, khách hàng hãy liên hệ PetWorld và cung cấp mã đơn, thời điểm thanh toán cùng thông tin giao dịch cần thiết. Không tạo nhiều đơn giống nhau chỉ vì trạng thái thanh toán chưa cập nhật.</p>
HTML,
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
                'content' => <<<'HTML'
<h2>Khi nào cần gửi yêu cầu hỗ trợ</h2>
<p>Khách hàng nên liên hệ PetWorld ngay khi phát hiện sản phẩm giao thiếu, hư hỏng, không đúng thông tin đơn hàng hoặc có vấn đề cần làm rõ sau mua. Việc liên hệ sớm giúp đội ngũ kiểm tra đơn hàng và hướng dẫn phương án phù hợp.</p>

<h2>Thông tin cần chuẩn bị</h2>
<p>Để yêu cầu được xử lý nhanh, khách hàng cần cung cấp:</p>
<ul>
    <li>Mã đơn hàng và tên hoặc số điện thoại đã dùng khi đặt hàng.</li>
    <li>Mô tả rõ tình trạng sản phẩm.</li>
    <li>Hình ảnh hoặc video liên quan nếu có.</li>
</ul>
<p>Không tự ý gửi trả sản phẩm khi chưa nhận được hướng dẫn từ PetWorld.</p>

<h2>Quy trình xử lý</h2>
<p>PetWorld tiếp nhận thông tin, đối chiếu đơn hàng và kiểm tra điều kiện áp dụng của trường hợp cụ thể. Khi cần, đội ngũ sẽ trao đổi thêm để xác minh tình trạng sản phẩm, hướng dẫn cách bàn giao hoặc đề xuất phương án hỗ trợ. Kết quả xử lý phụ thuộc vào tình trạng thực tế và chính sách được xác nhận tại thời điểm mua hàng.</p>

<h2>Lưu ý</h2>
<p>Giữ lại bao bì, phụ kiện, quà tặng kèm và chứng từ liên quan nếu có. Đây là bài mẫu; quản trị viên cần bổ sung thời hạn, điều kiện đổi trả và phương thức hoàn tiền chính thức trước khi xuất bản.</p>
HTML,
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
                'content' => <<<'HTML'
<h2>Cách áp dụng voucher</h2>
<p>Tại bước thanh toán, khách hàng nhập mã voucher vào khu vực áp dụng ưu đãi rồi kiểm tra lại số tiền giảm trước khi xác nhận đơn. Một voucher chỉ được áp dụng khi hệ thống thông báo thành công; việc nhập mã không đồng nghĩa voucher đã được giữ chỗ cho đơn hàng.</p>

<h2>Điều kiện của voucher</h2>
<p>Mỗi voucher có điều kiện riêng như thời gian hiệu lực, giá trị đơn tối thiểu, số lượt sử dụng, danh mục hoặc sản phẩm được áp dụng. Khách hàng cần đọc thông tin ưu đãi hiển thị cùng mã voucher và kiểm tra tổng tiền đơn hàng sau khi áp dụng.</p>

<h2>Trường hợp không áp dụng được</h2>
<p>Voucher có thể không hợp lệ vì:</p>
<ul>
    <li>Đã hết hạn hoặc chưa đến thời gian sử dụng.</li>
    <li>Không đạt giá trị đơn tối thiểu.</li>
    <li>Đã hết lượt dùng hoặc đã được dùng trước đó.</li>
    <li>Không áp dụng cho sản phẩm trong giỏ.</li>
</ul>
<p>Nếu mã vẫn không hoạt động dù đủ điều kiện hiển thị, hãy liên hệ PetWorld kèm mã voucher và ảnh màn hình tại bước thanh toán.</p>

<h2>Lưu ý</h2>
<p>Không chia sẻ mã hoặc thông tin đơn hàng cho người lạ. Việc hoàn lại voucher khi đơn bị hủy phụ thuộc vào điều kiện của chương trình và trạng thái xử lý đơn; quản trị viên cần xác nhận chính sách chính thức trước khi xuất bản bài này.</p>
HTML,
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
                'content' => <<<'HTML'
<h2>Khi nào nên liên hệ hỗ trợ</h2>
<p>Khách hàng có thể liên hệ PetWorld khi cần hỗ trợ về đơn hàng, thanh toán, vận chuyển, voucher, sản phẩm hoặc vấn đề sau mua. Hãy sử dụng các kênh liên hệ chính thức được công bố trên website hoặc trong thông tin đơn hàng.</p>

<h2>Thông tin nên cung cấp</h2>
<p>Với các yêu cầu liên quan đến đơn hàng, hãy chuẩn bị mã đơn, tên người nhận, số điện thoại hoặc email đã dùng khi đặt hàng. Với vấn đề sản phẩm, mô tả rõ tình trạng và gửi kèm hình ảnh hoặc video nếu có. Không gửi mật khẩu, mã OTP hoặc thông tin thanh toán nhạy cảm cho bất kỳ ai.</p>

<h2>Giúp yêu cầu được xử lý nhanh hơn</h2>
<p>Nêu ngắn gọn vấn đề, thời điểm xảy ra và kết quả mong muốn. Nếu đã trao đổi trước đó, hãy giữ lại thông tin liên quan để đội ngũ hỗ trợ có thể tiếp tục xử lý mà không phải hỏi lại từ đầu.</p>

<h2>Lưu ý trước khi xuất bản</h2>
<p>Quản trị viên cần bổ sung hotline, email, giờ hỗ trợ và liên kết kênh liên hệ chính thức trước khi xuất bản. Không công bố thông tin liên hệ chưa được xác thực.</p>
HTML,
            ],
            [
                'slug' => 'dieu-khoan-su-dung',
                'title' => 'Điều khoản sử dụng',
                'category' => 'terms',
                'summary' => 'Khi truy cập và mua sắm tại PetWorld, bạn đồng ý tuân thủ các điều khoản về tài khoản, đặt hàng, giá và trách nhiệm sử dụng. Vui lòng đọc kỹ trước khi đặt hàng.',
                'questions' => [
                    'Điều khoản sử dụng của PetWorld là gì?',
                    'Tôi có trách nhiệm gì với tài khoản của mình?',
                    'Khi nào đơn hàng được xác nhận?',
                    'Nếu thông tin sản phẩm có sai sót thì sao?',
                ],
                'content' => <<<'HTML'
<p>Khi truy cập và mua sắm tại PetWorld, bạn đồng ý tuân thủ các điều khoản dưới đây. Vui lòng đọc kỹ trước khi đặt hàng hoặc sử dụng dịch vụ.</p>

<h2>Tài khoản khách hàng</h2>
<p>Bạn chịu trách nhiệm cung cấp thông tin chính xác, bảo mật thông tin đăng nhập và thông báo cho PetWorld khi phát hiện hoạt động bất thường trên tài khoản.</p>

<h2>Đặt hàng và thanh toán</h2>
<p>Đơn hàng chỉ được xác nhận sau khi hệ thống ghi nhận đầy đủ thông tin giao hàng và phương thức thanh toán. PetWorld có thể liên hệ để xác minh trước khi xử lý đơn.</p>

<h2>Giá và thông tin sản phẩm</h2>
<p>Chúng tôi cố gắng đảm bảo giá, hình ảnh và mô tả sản phẩm chính xác. Khi có sai sót rõ ràng, PetWorld sẽ thông báo cho khách hàng trước khi tiếp tục thực hiện đơn hàng.</p>

<h2>Trách nhiệm sử dụng</h2>
<p>Người dùng không được can thiệp trái phép vào hệ thống, sử dụng nội dung cho mục đích gian lận hoặc thực hiện hành vi gây ảnh hưởng đến khách hàng khác.</p>
HTML,
            ],
            [
                'slug' => 'chinh-sach-bao-mat',
                'title' => 'Chính sách Bảo mật',
                'category' => 'privacy',
                'summary' => 'PetWorld cam kết bảo vệ quyền riêng tư và dữ liệu cá nhân của bạn. Chính sách này giải thích cách chúng tôi thu thập, sử dụng, bảo vệ và cho phép bạn kiểm soát dữ liệu của mình.',
                'questions' => [
                    'PetWorld thu thập những thông tin gì của tôi?',
                    'Dữ liệu cá nhân của tôi được bảo vệ như thế nào?',
                    'Tôi có thể yêu cầu xóa dữ liệu cá nhân không?',
                    'PetWorld có dùng cookie không?',
                ],
                'content' => <<<'HTML'
<p>Tại PetWorld, chúng tôi cam kết bảo vệ quyền riêng tư và dữ liệu cá nhân của bạn. Chính sách này giải thích cách chúng tôi xử lý thông tin để mang lại trải nghiệm tốt nhất cho thú cưng của bạn.</p>

<h2>Thu thập thông tin</h2>
<p>Chúng tôi thu thập các loại thông tin sau để phục vụ nhu cầu dinh dưỡng và chăm sóc thú cưng của bạn:</p>
<ul>
    <li><strong>Thông tin cá nhân:</strong> Họ tên, địa chỉ email, số điện thoại và địa chỉ giao hàng.</li>
    <li><strong>Thông tin thú cưng:</strong> Giống loài, tuổi, cân nặng và các yêu cầu dinh dưỡng đặc biệt.</li>
    <li><strong>Dữ liệu giao dịch:</strong> Lịch sử mua hàng và thông tin thanh toán (được mã hóa).</li>
</ul>

<h2>Sử dụng thông tin</h2>
<p>Thông tin của bạn được sử dụng minh bạch cho các mục đích:</p>
<ul>
    <li><strong>Xử lý đơn hàng:</strong> Đảm bảo sản phẩm được giao đúng hạn và đúng địa chỉ.</li>
    <li><strong>Cá nhân hóa:</strong> Gợi ý sản phẩm phù hợp với nhu cầu riêng của từng thú cưng.</li>
</ul>

<h2>Bảo vệ dữ liệu</h2>
<p>Chúng tôi áp dụng các công nghệ mã hóa SSL tiêu chuẩn ngành và hệ thống tường lửa đa lớp. Chỉ những nhân viên có thẩm quyền mới được tiếp cận thông tin cá nhân của khách hàng trong phạm vi công việc cần thiết.</p>

<h2>Quyền của người dùng</h2>
<p>Bạn có toàn quyền kiểm soát dữ liệu của mình:</p>
<ul>
    <li><strong>Quyền truy cập và chỉnh sửa:</strong> Bạn có thể yêu cầu xem, cập nhật hoặc chỉnh sửa thông tin cá nhân bất kỳ lúc nào thông qua trang tài khoản hoặc liên hệ bộ phận hỗ trợ.</li>
    <li><strong>Quyền yêu cầu xóa dữ liệu:</strong> Bạn có quyền yêu cầu xóa toàn bộ dữ liệu cá nhân khỏi hệ thống, trừ những thông tin bắt buộc phải lưu giữ theo quy định pháp luật.</li>
</ul>

<h2>Cookie</h2>
<p>Chúng tôi sử dụng Cookie để ghi nhớ tùy chọn của bạn và phân tích lưu lượng truy cập. Bạn có thể tắt cookie trong phần cài đặt trình duyệt, nhưng điều này có thể ảnh hưởng đến một số tính năng của trang web.</p>
HTML,
            ],
        ];

        foreach ($articles as $article) {
            KnowledgeArticle::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'category' => $article['category'],
                    'summary' => $article['summary'],
                    'questions' => $article['questions'],
                    'content' => $article['content'],
                    'status' => 'published',
                    'version' => 1,
                    'published_at' => now(),
                ],
            );
        }
    }
}