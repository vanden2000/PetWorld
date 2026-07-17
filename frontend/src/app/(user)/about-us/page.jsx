import Link from "next/link";
import { getHomeData } from "@/lib/api";
import { resolveBrandImage } from "@/lib/format";
import "./about-us.css";

export const metadata = {
  title: "Về Chúng Tôi - PetWorld | Hệ Sinh Thái Dinh Dưỡng & Phụ Kiện Thú Cưng Hữu Cơ",
  description: "Câu chuyện thương hiệu PetWorld. Chúng tôi cam kết chất lượng thực phẩm hữu cơ sạch và phụ kiện bảo vệ khớp xương cho thú cưng.",
};

export default async function AboutUsPage() {
  const homeData = await getHomeData();
  const brands = homeData.brands ?? [];
  const companionBrands = brands.slice(0, 5);

  return (
    <main className="main-content" style={{ minHeight: "100vh", backgroundColor: "#faf8f6", color: "#2d2926", paddingBottom: "80px" }}>
      <div className="homepage-container">
        {/* Breadcrumb */}
        <nav className="shop-breadcrumb">
          <Link href="/">Trang chủ</Link>
          <span className="shop-breadcrumb-sep">›</span>
          <span className="shop-breadcrumb-current">Về chúng tôi</span>
        </nav>

        {/* Hero Section */}
        <header className="about-hero-section">
          <span className="about-badge">Câu chuyện PetWorld</span>
          <h1 className="about-main-title">
            Chúng tôi bắt đầu vì <span>tình yêu thú cưng</span>
          </h1>
          <p className="about-intro-text">
            Một hành trình nhỏ bắt nguồn từ mong muốn mang lại những sản phẩm thực sự sạch và an toàn cho chó mèo tại Việt Nam.
          </p>
        </header>

        {/* Story Section */}
        <section className="about-section founder-story-grid">
          <div className="founder-letter-text">
            <h2 className="about-serif-title">Lời ngỏ từ nhà sáng lập</h2>
            <p>Kính gửi quý khách hàng,</p>
            <p>
              Ý tưởng thành lập PetWorld xuất phát từ một trải nghiệm cá nhân của tôi vào năm 2024 khi Leo - chú chó Husky của gia đình - bị dị ứng thức ăn nghiêm trọng. Nhìn Leo rụng lông và ngứa ngáy liên tục do sử dụng các thức ăn chứa phụ gia nhân tạo không rõ nguồn gốc, tôi đã dành nhiều tháng tìm kiếm giải pháp và nhận ra sự thiếu hụt lớn của các sản phẩm thực sự sạch và lành tính tại thị trường trong nước.
            </p>
            <p>
              Tại PetWorld, chúng tôi không xem thú cưng là vật nuôi đơn thuần, chúng là gia đình. Đó là lý do tại sao chúng tôi đặt tính minh bạch của nguồn nguyên liệu lên hàng đầu. Mọi mặt hàng trước khi đưa ra thị trường đều phải trải qua quy trình kiểm soát chất lượng kỹ lưỡng bởi các chuyên gia dinh dưỡng và thú y.
            </p>
            <p>
              Chúng tôi hy vọng những nỗ lực nhỏ bé của mình sẽ giúp bé cưng của bạn luôn khỏe mạnh và hạnh phúc. Cảm ơn bạn đã tin tưởng và đồng hành cùng PetWorld.
            </p>
            
            <div className="founder-signature-box">
              <div>
                <div className="founder-signature-name">Công Trung</div>
                <div className="founder-signature-role">Sáng lập & Giám đốc Điều hành</div>
              </div>
            </div>
          </div>

          <div className="founder-photo-box">
            <img src="/founder_with_dog.png" alt="Công Trung bên chú cún Leo" className="founder-photo-img" />
            <p className="founder-photo-caption">Công Trung và chú cún Leo tại văn phòng PetWorld (2024)</p>
          </div>
        </section>

        {/* Vision & Mission Section */}
        <section className="about-section vision-mission-grid">
          <div className="vision-mission-col">
            <h3>Tầm nhìn</h3>
            <p className="about-p">
              Trở thành hệ sinh thái dinh dưỡng hữu cơ và phụ kiện bảo hộ thú cưng dẫn đầu, tiên phong trong việc chuẩn hóa chất lượng sạch và thiết kế công thái học an toàn, giúp cải thiện sức khỏe dài lâu cho chó mèo tại Việt Nam.
            </p>
          </div>
          <div className="vision-mission-col">
            <h3>Sứ mệnh</h3>
            <p className="about-p">
              Mang lại sự an tâm tuyệt đối cho chủ nuôi trong hành trình chăm sóc bé cưng thông qua:
            </p>
            <ul>
              <li>Thức ăn có nguồn gốc 100% hữu cơ, tự nhiên và sạch sẽ.</li>
              <li>Phụ kiện công thái học bảo vệ tối đa hệ xương khớp của vật nuôi.</li>
              <li>Phổ biến kiến thức chăm sóc động vật khoa học, thực tế và dễ tiếp cận.</li>
            </ul>
          </div>
        </section>

        {/* Core Values Section */}
        <section className="about-section">
          <h2 className="about-serif-title" style={{ textAlign: "center", marginBottom: "40px" }}>Triết lý sản phẩm</h2>
          <div className="values-grid">
            {/* Value 1 */}
            <div className="value-col">
              <span className="value-num">01.</span>
              <h4 className="value-title">Dinh dưỡng hữu cơ sạch</h4>
              <p className="value-desc">
                Cam kết nguồn nguyên liệu tự nhiên rõ ràng, nói không với chất bảo quản hóa học và các phụ gia nhân tạo gây hại cho sức khỏe thú cưng.
              </p>
            </div>

            {/* Value 2 */}
            <div className="value-col">
              <span className="value-num">02.</span>
              <h4 className="value-title">Công thái học bảo vệ khớp</h4>
              <p className="value-desc">
                Các phụ kiện như yếm dắt, vòng cổ và đệm ngủ được thiết kế giảm lực cản, tránh gây chấn thương xương khớp của bé khi chạy nhảy.
              </p>
            </div>

            {/* Value 3 */}
            <div className="value-col">
              <span className="value-num">03.</span>
              <h4 className="value-title">Trải nghiệm thực tế</h4>
              <p className="value-desc">
                Sản phẩm trước khi ra mắt luôn được thử nghiệm thực tế bởi chính những chú cún mèo trong văn phòng của chúng tôi để đảm bảo độ bền tối ưu.
              </p>
            </div>
          </div>
        </section>

        {/* Team Section */}
        <section className="about-section">
          <h2 className="about-serif-title" style={{ textAlign: "center", marginBottom: "45px" }}>Đội ngũ đồng hành</h2>
          <div className="team-list-grid">
            {/* CTO */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_diem.png" alt="Anh Thế Điểm" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Thế Điểm</h4>
              <div className="team-role">Đồng Sáng lập & CTO</div>
              <p className="team-desc">
                Chịu trách nhiệm phát triển hệ thống thương mại điện tử và giải pháp trải nghiệm số tại PetWorld. Anh không ngừng nghiên cứu công nghệ mới để đem lại dịch vụ tối ưu nhất cho khách hàng.
              </p>
            </div>

            {/* CEO */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_trung.png" alt="Anh Công Trung" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Công Trung</h4>
              <div className="team-role">Sáng lập & CEO</div>
              <p className="team-desc">
                Cử nhân chuyên ngành Khoa học Động vật. Anh trực tiếp định hướng chiến lược thương hiệu, kiểm định nghiêm ngặt chất lượng nguồn thức ăn hữu cơ và mở rộng hợp tác toàn cầu.
              </p>
            </div>

            {/* CPO */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_minh.png" alt="Anh Văn Minh" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Văn Minh</h4>
              <div className="team-role">Đồng Sáng lập & CPO</div>
              <p className="team-desc">
                Thạc sĩ Thiết kế Công nghiệp. Anh là người kiến tạo các dòng sản phẩm phụ kiện công thái học bảo vệ khớp xương cho thú cưng, đạt chuẩn thẩm mỹ và an toàn vượt trội.
              </p>
            </div>

            {/* COO */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_phat.png" alt="Anh Trần Phát" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Trần Phát</h4>
              <div className="team-role">Đồng Sáng lập & COO</div>
              <p className="team-desc">
                Chuyên gia quản lý chuỗi cung ứng logistics. Anh phụ trách tối ưu khâu lưu kho, bảo quản sản phẩm và phân phối hàng hóa đến tay người nuôi một cách nhanh chóng nhất.
              </p>
            </div>
          </div>
        </section>

        {/* Brand Partners & Certifications */}
        <section className="footer-trust-section">
          {companionBrands.length > 0 && (
            <div className="trust-logos">
              {companionBrands.map((brand) => (
                <Link
                  key={brand.id}
                  href={`/shop?brand=${brand.slug}`}
                  style={{ display: "block" }}
                >
                  <img
                    src={resolveBrandImage(brand.image)}
                    alt={brand.name}
                    title={brand.name}
                    className="trust-logo-img"
                  />
                </Link>
              ))}
            </div>
          )}
          
          <p className="trust-cert-text">
            Sản phẩm phân phối bởi PetWorld được cam kết chất lượng theo tiêu chuẩn <strong>USDA Organic</strong> (canh tác hữu cơ sạch), đạt kiểm định <strong>FDA</strong> của Hoa Kỳ và quy trình quản lý an toàn thực phẩm <strong>HACCP</strong>.
          </p>
        </section>

        {/* CTA section */}
        <section className="cta-box-minimal" style={{ marginBottom: "40px" }}>
          <h2 className="cta-title-minimal">Mang lại những điều tốt đẹp nhất cho bé cưng</h2>
          <p className="cta-desc-minimal">
            Khám phá các sản phẩm thức ăn hữu cơ sạch và phụ kiện công thái học bảo vệ sức khỏe cho người bạn bốn chân của bạn.
          </p>
          <Link href="/shop" className="cta-btn-minimal">
            Ghé Cửa Hàng Ngay
          </Link>
        </section>
      </div>
    </main>
  );
}
