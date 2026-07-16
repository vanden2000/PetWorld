import Link from "next/link";
import { getHomeData } from "@/lib/api";
import { resolveBrandImage } from "@/lib/format";
import "./about-us.css";

export const metadata = {
  title: "Về Chúng Tôi - PetWorld | Hệ Sinh Thái Dinh Dưỡng & Phụ Kiện Thú Cưng Hữu Cơ",
  description: "Tìm hiểu về hành trình sáng lập, tầm nhìn phát triển và đội ngũ chuyên gia dinh dưỡng thú cưng tại PetWorld. Chúng tôi cam kết chất lượng chuẩn FDA, USDA Organic.",
};

export default async function AboutUsPage() {
  const homeData = await getHomeData();
  const brands = homeData.brands ?? [];
  const companionBrands = brands.slice(0, 5);

  return (
    <main className="main-content" style={{ minHeight: "100vh", backgroundColor: "#faf8f6", color: "#2d2926", paddingBottom: "80px" }}>
      <div className="about-wrapper">
        {/* Breadcrumb */}
        <nav className="shop-breadcrumb" style={{ padding: "24px 0", marginBottom: "10px" }}>
          <Link href="/">Trang chủ</Link> /
          <span className="shop-breadcrumb-sep">
            <span className="shop-breadcrumb-current" style={{ marginLeft: "6px" }}>Về chúng tôi</span>
          </span>
        </nav>

        {/* Hero Section */}
        <header className="about-hero-section">
          <span className="about-badge">Hệ sinh thái PetWorld</span>
          <h1 className="about-main-title">
            Chăm sóc thú cưng bằng sự <span>chuyên nghiệp & tận tâm</span>
          </h1>
          <p className="about-intro-text">
            Chúng tôi kiến tạo giải pháp chăm sóc thú cưng toàn diện, cam kết chất lượng dinh dưỡng minh bạch và thiết kế phụ kiện an toàn chuẩn y khoa.
          </p>
        </header>

        {/* Founder Letter Section */}
        <section className="founder-letter-section">
          <div className="letter-content">
            <h2 className="about-serif-title">Lời ngỏ từ nhà sáng lập</h2>
            <div className="letter-body">
              <p>Kính gửi quý khách hàng và những người bạn có cùng tình yêu thương động vật,</p>
              <p>
                Ý tưởng thành lập PetWorld bắt đầu từ một trải nghiệm cá nhân của tôi vào năm 2024 khi Bơ - chú chó Golden Retriever của gia đình - bị dị ứng thức ăn nghiêm trọng. Chứng kiến Bơ phải vật lộn với các cơn ngứa và rụng lông do sử dụng thức ăn chứa phụ gia không rõ nguồn gốc, tôi đã dành nhiều tháng nghiên cứu và nhận ra sự thiếu hụt lớn của các sản phẩm thực sự sạch và an toàn cho thú cưng tại Việt Nam.
              </p>
              <p>
                Tại PetWorld, chúng tôi không xem thú cưng là vật nuôi đơn thuần, chúng là gia đình. Đó là lý do tại sao chúng tôi đặt tính minh bạch của nguồn nguyên liệu và sự khoa học trong thiết kế lên hàng đầu. Mọi mặt hàng trước khi lên kệ đều phải trải qua quy trình kiểm soát chất lượng chặt chẽ bởi các chuyên gia dinh dưỡng và thú y.
              </p>
              <p>
                Chúng tôi tin rằng, mỗi sự lựa chọn đúng đắn của bạn hôm nay sẽ mang lại những năm tháng khỏe mạnh, hạnh phúc dài lâu bên bé cưng. Cảm ơn bạn đã tin tưởng và đồng hành cùng PetWorld trên hành trình ý nghĩa này.
              </p>
            </div>
            <div className="letter-footer">
              <div className="founder-info">
                <h5>Trần Trung</h5>
                <p>Sáng lập & Giám đốc Điều hành</p>
              </div>
              <div className="founder-signature">Tran Trung</div>
            </div>
          </div>

          <div className="founder-image-wrapper">
            <div className="founder-image-box">
              <img src="/founder_with_dog.png" alt="Nhà sáng lập Trần Trung bên chú cún Bơ" />
            </div>
            <div className="founder-image-badge">
              <strong>Bơ & Trần Trung</strong>
              <p style={{ fontSize: "0.8rem", opacity: 0.9, marginTop: "4px" }}>Nguồn cảm hứng sáng lập PetWorld (2024)</p>
            </div>
          </div>
        </section>

        {/* Vision & Mission Section */}
        <section className="vision-mission-section">
          <div className="vision-mission-grid">
            <div className="vision-mission-card">
              <h3>Tầm nhìn</h3>
              <p>
                Trở thành hệ sinh thái siêu thị và dịch vụ thú cưng hữu cơ dẫn đầu tại Việt Nam, tiên phong trong việc chuẩn hóa chất lượng dinh dưỡng sạch và bảo hộ công thái học cho động vật nuôi, góp phần nâng cao tuổi thọ và chất lượng sống cho các bé cưng.
              </p>
            </div>
            <div className="vision-mission-card">
              <h3>Sứ mệnh</h3>
              <p>
                Kiến tạo cuộc sống khỏe mạnh toàn diện cho thú cưng và đem lại sự an tâm tuyệt đối cho chủ nuôi thông qua:
              </p>
              <ul>
                <li>Cung cấp 100% dòng thực phẩm có nguồn gốc hữu cơ và tự nhiên sạch.</li>
                <li>Nghiên cứu phụ kiện công thái học bảo vệ cột sống và hệ xương khớp của vật nuôi.</li>
                <li>Phổ biến kiến thức chăm sóc động vật khoa học, đáng tin cậy đến cộng đồng.</li>
              </ul>
            </div>
          </div>
        </section>

        {/* Left-aligned Timeline & History Section */}
        <section className="timeline-section-container">
          <div className="timeline-layout">
            <div className="timeline-intro">
              <span className="about-badge">Cột mốc lịch sử</span>
              <h2 className="about-serif-title" style={{ marginTop: "10px" }}>Hành trình xây dựng niềm tin</h2>
              <p style={{ color: "#5b5550", fontSize: "1.05rem", lineHeight: "1.7" }}>
                Từng bước đi của PetWorld đều gắn liền với những cải tiến về mặt chất lượng sản phẩm và nâng cao trải nghiệm chăm sóc thú cưng của khách hàng.
              </p>
            </div>

            <div className="timeline-list">
              {/* Year 2024 */}
              <div className="timeline-node-item">
                <div className="timeline-node-dot" />
                <span className="timeline-node-year">2024</span>
                <h3 className="timeline-node-title">Khởi nghiệp & Nghiên cứu dinh dưỡng</h3>
                <p className="timeline-node-desc">
                  Thành lập văn phòng nghiên cứu tại Quận 7, TP. Hồ Chí Minh. Chúng tôi tập trung tìm kiếm các nông trại canh tác hữu cơ và liên kết với các nhà sản xuất thực phẩm đạt tiêu chuẩn an toàn thực phẩm của châu Âu.
                </p>
              </div>

              {/* Year 2025 */}
              <div className="timeline-node-item">
                <div className="timeline-node-dot" />
                <span className="timeline-node-year">2025</span>
                <h3 className="timeline-node-title">Khai trương siêu thị & Chuẩn hóa chất lượng</h3>
                <p className="timeline-node-desc">
                  Ra mắt cửa hàng flagship đầu tiên tại TP.HCM. Thiết lập quy trình kiểm định chất lượng đầu vào ngặt nghèo và nhận chứng nhận phân phối chính ngạch từ 15 thương hiệu thức ăn hữu cơ hàng đầu thế giới.
                </p>
              </div>

              {/* Year 2026 */}
              <div className="timeline-node-item">
                <div className="timeline-node-dot" />
                <span className="timeline-node-year">2026</span>
                <h3 className="timeline-node-title">Số hóa hệ sinh thái & Phụ kiện Công thái học</h3>
                <p className="timeline-node-desc">
                  Phát triển kênh thương mại điện tử, ra mắt dòng sản phẩm bảo hộ khớp xương tự thiết kế độc quyền, đồng thời tích hợp dịch vụ hỗ trợ tư vấn sức khỏe từ xa cùng bác sĩ thú y 24/7.
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Philosophy Section */}
        <section className="philosophy-section-container">
          <div style={{ textAlign: "center", marginBottom: "20px" }}>
            <span className="about-badge">Triết lý của chúng tôi</span>
            <h2 className="about-serif-title" style={{ marginTop: "10px" }}>Chất lượng là nền tảng cốt lõi</h2>
          </div>

          <div className="philosophy-grid">
            {/* Value 1 */}
            <div className="philosophy-card">
              <div className="philosophy-icon-wrapper">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 1 9.8a7 7 0 0 1-9 8.2z" />
                  <path d="M9 22v-4" />
                </svg>
              </div>
              <h4>Hữu cơ sạch 100%</h4>
              <p>Thức ăn và hạt khô cam kết có nguồn gốc hoàn toàn tự nhiên, không chứa hormone tăng trưởng, hóa chất tạo màu hoặc chất bảo quản nhân tạo.</p>
            </div>

            {/* Value 2 */}
            <div className="philosophy-card">
              <div className="philosophy-icon-wrapper">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
              </div>
              <h4>Thiết kế y khoa</h4>
              <p>Mỗi món đồ chơi, dây dắt hay chuồng đệm đều được nghiên cứu dưới góc độ vật lý trị liệu để tránh gây hằn đau, cong vẹo khớp hoặc chấn thương da.</p>
            </div>

            {/* Value 3 */}
            <div className="philosophy-card">
              <div className="philosophy-icon-wrapper">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="5" r="2.5" />
                  <circle cx="5.5" cy="9.5" r="2.5" />
                  <circle cx="18.5" cy="9.5" r="2.5" />
                  <path d="M12 11c-2.5 0-4.5 2-4.5 5s1.5 4 4.5 4 4.5-1 4.5-4-1.5-5-4.5-5z" />
                </svg>
              </div>
              <h4>Trải nghiệm thực tế</h4>
              <p>Mọi dòng sản phẩm mới đều được thử nghiệm bởi các chú cún, chú mèo trong văn phòng của chúng tôi để bảo đảm độ bền tối ưu và khẩu vị ngon miệng nhất.</p>
            </div>
          </div>
        </section>

        {/* Certifications Section */}
        <section className="certifications-section">
          <div className="cert-header">
            <span className="about-badge">Tiêu chuẩn kiểm nghiệm</span>
            <h2 className="about-serif-title" style={{ marginTop: "10px" }}>Chứng nhận & Cam kết an toàn</h2>
            <p style={{ color: "#5b5550", fontSize: "0.98rem", lineHeight: "1.6" }}>
              Các sản phẩm do PetWorld sản xuất và phân phối luôn tuân thủ nghiêm ngặt các hệ thống tiêu chuẩn kiểm nghiệm quốc tế.
            </p>
          </div>

          <div className="cert-grid">
            <div className="cert-card">
              <div className="cert-badge-wrapper">USDA</div>
              <h4>USDA Organic</h4>
              <p>Nguyên liệu được gieo trồng hữu cơ 100%, không sử dụng phân bón hóa học và thuốc trừ sâu nông nghiệp.</p>
            </div>
            <div className="cert-card">
              <div className="cert-badge-wrapper">FDA</div>
              <h4>FDA Standard</h4>
              <p>Nhà máy sản xuất đóng gói đạt tiêu chuẩn kiểm định an toàn vệ sinh của Cục Quản lý Thực phẩm Hoa Kỳ.</p>
            </div>
            <div className="cert-card">
              <div className="cert-badge-wrapper">HACCP</div>
              <h4>HACCP Certified</h4>
              <p>Quy trình kiểm soát mối nguy hại từ nguyên liệu thô đến chế biến, đảm bảo thực phẩm an toàn tuyệt đối.</p>
            </div>
          </div>
        </section>

        {/* Companion Brands Section */}
        {companionBrands.length > 0 && (
          <section className="press-section">
            <h3 className="press-title">Đồng hành cùng các thương hiệu</h3>
            <div className="press-logos">
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
                    className="brand-partner-logo"
                  />
                </Link>
              ))}
            </div>
          </section>
        )}

        {/* Expert Team Section */}
        <section className="team-container">
          <div className="team-header">
            <span className="about-badge">Đội ngũ chuyên gia</span>
            <h2 className="about-serif-title" style={{ marginTop: "10px" }}>Những người đứng sau PetWorld</h2>
            <p style={{ color: "#5b5550", fontSize: "1.05rem", lineHeight: "1.6" }}>
              Đội ngũ có trình độ chuyên môn cao và tình yêu động vật sâu sắc, cam kết mang đến những giá trị thật cho thú cưng.
            </p>
          </div>

          <div className="team-grid">
            {/* Founder */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_trung.png" alt="Anh Trần Trung - Sáng lập PetWorld" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Trần Trung</h4>
              <div className="team-member-role">Sáng lập & Giám đốc điều hành</div>
              <div className="team-member-education">Cử nhân Khoa học Động vật (Đại học Nông Lâm TP.HCM)</div>
              <p className="team-member-bio">
                Hơn 8 năm hoạt động trong lĩnh vực vận hành dịch vụ thú cưng và nhập khẩu dinh dưỡng sạch. Anh là người định hình triết lý sản phẩm hữu cơ và trực tiếp chọn lọc các nhà phân phối.
              </p>
              <div className="team-member-pet-tag dog">
                <span>🐕</span> Cún Bơ (Golden Retriever)
              </div>
            </div>

            {/* Nutrition Expert */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_linh.png" alt="Chị Khánh Linh - Bác sĩ Thú y" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Dr. Khánh Linh</h4>
              <div className="team-member-role">Bác sĩ Thú y - Cố vấn dinh dưỡng</div>
              <div className="team-member-education">Bác sĩ Thú y (DVM) - Đại học Chulalongkorn (Thái Lan)</div>
              <p className="team-member-bio">
                Chuyên gia có 6 năm kinh nghiệm điều trị da liễu và dinh dưỡng động vật tại các bệnh viện quốc tế. Chị chịu trách nhiệm kiểm định thành phần dinh dưỡng lâm sàng cho dòng thực phẩm.
              </p>
              <div className="team-member-pet-tag cat">
                <span>🐈</span> Mèo Mun (Mèo mướp ta)
              </div>
            </div>

            {/* Design Expert */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_minh.png" alt="Anh Hoàng Minh - Nhà thiết kế" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Hoàng Minh</h4>
              <div className="team-member-role">Giám đốc Thiết kế sản phẩm</div>
              <div className="team-member-education">Thạc sĩ Thiết kế Công nghiệp (Đại học Kiến trúc TP.HCM)</div>
              <p className="team-member-bio">
                Có đam mê sâu sắc với hành vi động vật học. Anh nghiên cứu các giải pháp công thái học để thiết kế các loại yếm dắt trợ lực chống sặc, vòng cổ êm ái và đồ chơi giáo dục trí tuệ cho vật nuôi.
              </p>
              <div className="team-member-pet-tag dog">
                <span>🐕</span> Cún Bánh Bao (Corgi)
              </div>
            </div>
          </div>
        </section>

        {/* CTA section */}
        <section className="cta-box">
          <h2 className="cta-title">Mang lại sự chăm sóc xứng đáng cho bé cưng</h2>
          <p className="cta-desc">
            Khám phá ngay các bộ sưu tập dinh dưỡng hữu cơ và phụ kiện công thái học bảo vệ sức khỏe cho người bạn bốn chân của bạn.
          </p>
          <Link href="/shop" className="cta-button">
            Ghé Cửa Hàng Ngay
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </Link>
        </section>
      </div>
    </main>
  );
}
