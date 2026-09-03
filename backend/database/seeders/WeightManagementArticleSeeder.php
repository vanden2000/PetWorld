<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Blog;
use Illuminate\Database\Seeder;

class WeightManagementArticleSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'bi-quyet-chon-hat-kiem-soat-can-nang-cho-thu-cung';

        $content = <<<'HTML'
<article class="article-content-wrapper">
<div class="article-section-block" style="margin-bottom: 24px;">
  <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 28px; margin-bottom: 12px; line-height: 1.4; display: flex; align-items: center;">
    <span class="heading-num-badge">1</span>
    <span>Dấu hiệu nhận biết thú cưng đang bị thừa cân, béo phì</span>
  </h2>
  <p style="font-size: 15.5px; line-height: 1.75; color: #334155; margin-top: 0; margin-bottom: 12px;">
    Nhiều người nuôi thường nghĩ chó mèo tròn trịa, mũm mĩm mới đáng yêu. Tuy nhiên, theo các bác sĩ thú y, thừa cân là nguyên nhân hàng đầu làm giảm tuổi thọ và gây ra nhiều bệnh lý nguy hiểm về xương khớp, tiểu đường và tim mạch ở thú cưng.
  </p>
  <p style="font-size: 15.5px; line-height: 1.75; color: #334155; margin-top: 0; margin-bottom: 12px;">
    Bạn có thể tự kiểm tra thể trạng của bé cưng tại nhà theo 2 cách đơn giản sau:
  </p>
  <ul style="font-size: 15.5px; line-height: 1.75; color: #334155; padding-left: 20px;">
    <li><strong>Sờ vùng xương sườn:</strong> Dùng hai lòng bàn tay vuốt dọc hai bên lườn của bé. Nếu bạn không cảm nhận được xương sườn hoặc phải ấn mạnh mới thấy do lớp mỡ dày, bé cưng của bạn đang ở mức thừa cân.</li>
    <li><strong>Quan sát từ trên xuống:</strong> Khi nhìn từ phía trên xuống lưng, thú cưng có vóc dáng chuẩn sẽ có phần eo thắt nhẹ ngay sau lồng ngực. Nếu thân hình bé tròn đều như hình bầu dục hoặc phình to, bé đang tích tụ nhiều mỡ thừa.</li>
  </ul>
</div>

<div class="article-section-block" style="margin-bottom: 24px;">
  <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 28px; margin-bottom: 12px; line-height: 1.4; display: flex; align-items: center;">
    <span class="heading-num-badge">2</span>
    <span>Tiêu chí lựa chọn hạt kiểm soát vóc dáng khoa học</span>
  </h2>
  <p style="font-size: 15.5px; line-height: 1.75; color: #334155; margin-top: 0; margin-bottom: 12px;">
    Khi giảm cân cho thú cưng, bạn <strong>tuyệt đối không nên cắt giảm đột ngột lượng thức ăn</strong> vì sẽ khiến bé bị thiếu hụt dinh dưỡng, dễ bị stress và rụng lông. Thay vào đó, giải pháp khoa học và an toàn nhất là chuyển sang dòng <em>thức ăn hạt chuyên dụng kiểm soát cân nặng (Weight Care / Light Management)</em>:
  </p>
  <ul style="font-size: 15.5px; line-height: 1.75; color: #334155; padding-left: 20px;">
    <li><strong>Giàu Protein chất lượng cao:</strong> Giúp duy trì khối lượng cơ bắp săn chắc trong suốt quá trình tiêu hao mỡ thừa.</li>
    <li><strong>Tăng cường chất xơ tự nhiên:</strong> Giúp tạo cảm giác no lâu, hạn chế thói quen đòi ăn vặt liên tục của thú cưng.</li>
    <li><strong>Giảm thiểu chất béo và calo:</strong> Giúp kiểm soát tổng năng lượng nạp vào mỗi ngày mà vẫn đảm bảo đủ khoáng chất và vitamin.</li>
    <li><strong>Bổ sung L-Carnitine:</strong> Hoạt chất sinh học hỗ trợ chuyển hóa mỡ thừa thành năng lượng vận động nhanh chóng.</li>
  </ul>
</div>

<div class="article-section-block" style="margin-bottom: 24px;">
  <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 28px; margin-bottom: 12px; line-height: 1.4; display: flex; align-items: center;">
    <span class="heading-num-badge">3</span>
    <span>Gợi ý các dòng hạt dinh dưỡng kiểm soát cân nặng hàng đầu tại PetWorld</span>
  </h2>
  <p style="font-size: 15.5px; line-height: 1.75; color: #334155; margin-top: 0; margin-bottom: 12px;">
    Để giúp sen dễ dàng lựa chọn đúng dòng thức ăn cho bé, PetWorld đề xuất các sản phẩm hạt dinh dưỡng cân bằng bán chạy nhất:
  </p>
  <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 18px; margin: 16px 0;">
    <h3 style="color: #ea580c; margin-top: 0; margin-bottom: 8px; font-size: 17px;">✨ Dành cho Chó: Royal Canin Mini Adult</h3>
    <p style="font-size: 14.5px; color: #334155; line-height: 1.6; margin-bottom: 12px;">Công thức cân bằng năng lượng tối ưu cho chó giống nhỏ, giàu đạm dễ tiêu hóa và bổ sung L-Carnitine hỗ trợ chuyển hóa chất béo.</p>
    <a href="/shop/royal-canin-mini-adult" style="display: inline-block; background: #ff782d; color: #fff; padding: 8px 18px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 13.5px;">Xem sản phẩm ngay</a>
  </div>
  <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 18px; margin: 16px 0;">
    <h3 style="color: #ea580c; margin-top: 0; margin-bottom: 8px; font-size: 17px;">✨ Dành cho Mèo: Thức ăn hạt Whiskas Adult vị cá biển</h3>
    <p style="font-size: 14.5px; color: #334155; line-height: 1.6; margin-bottom: 12px;">Đậm đà hương vị cá biển kích thích vị giác nhưng kiểm soát calo chuẩn mực, bảo vệ đường tiết niệu và hỗ trợ vóc dáng thon gọn cho mèo nuôi trong nhà.</p>
    <a href="/shop/whiskas-adult-vi-ca-bien" style="display: inline-block; background: #ff782d; color: #fff; padding: 8px 18px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 13.5px;">Xem sản phẩm ngay</a>
  </div>
</div>

<div class="article-section-block" style="margin-bottom: 24px;">
  <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 28px; margin-bottom: 12px; line-height: 1.4; display: flex; align-items: center;">
    <span class="heading-num-badge">4</span>
    <span>Dịch vụ tư vấn chọn hạt miễn phí theo từng bé tại PetWorld</span>
  </h2>
  <p style="font-size: 15.5px; line-height: 1.75; color: #334155; margin-top: 0; margin-bottom: 12px;">
    Mỗi bé cún hay mèo đều có độ tuổi, mức độ vận động và thể trạng khác nhau. Nếu bạn chưa biết bé nhà mình nên ăn loại hạt nào với khẩu phần bao nhiêu gam mỗi ngày, hãy liên hệ ngay với PetWorld để được đội ngũ chuyên viên dinh dưỡng tư vấn hoàn toàn miễn phí!
  </p>
  <div style="text-align: center; margin: 24px 0;">
    <a href="/contact" style="display: inline-block; background: linear-gradient(135deg, #ff782d 0%, #ea580c 100%); color: #fff; padding: 12px 28px; border-radius: 30px; font-weight: 800; font-size: 15px; text-decoration: none; box-shadow: 0 4px 14px rgba(255, 120, 45, 0.35);">Đăng ký tư vấn chọn hạt miễn phí ngay</a>
  </div>
</div>
</article>
HTML;

        $blog = Blog::updateOrCreate(
            ['slug' => $slug],
            [
                'blog_category_id' => 2, // Dinh dưỡng
                'user_id' => 1,
                'title' => 'Cách nhận biết thú cưng thừa cân và bí quyết chọn hạt kiểm soát vóc dáng hiệu quả',
                'seo_title' => 'Bí quyết chọn hạt kiểm soát cân nặng cho thú cưng | PetWorld',
                'description' => 'Hướng dẫn chi tiết từ chuyên gia dinh dưỡng PetWorld giúp bạn nhận biết dấu hiệu thừa cân ở chó mèo, thiết lập khẩu phần ăn khoa học và lựa chọn thức ăn hạt dinh dưỡng cân bằng giúp bé giữ dáng khỏe mạnh mỗi ngày.',
                'content' => $content,
                'image' => 'blogs/chon-thuc-an-hat-cho-meo-truong-thanh.jpg',
                'status' => 'active',
                'published_at' => now(),
            ]
        );

        // Cập nhật banner số 1 liên kết tới bài viết cẩm nang này
        $banner = Banner::find(1);
        if ($banner) {
            $banner->link = '/news/' . $slug;
            $banner->description = 'Giữ dáng khỏe - Nâng lượng trọn ngày: Bí quyết chọn hạt kiểm soát cân nặng';
            $banner->save();
        }
    }
}
