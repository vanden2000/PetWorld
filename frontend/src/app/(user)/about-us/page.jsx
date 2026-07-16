import Link from "next/link";
import { getHomeData } from "@/lib/api";
import { resolveBrandImage } from "@/lib/format";
import "./about-us.css";

export const metadata = {
  title: "Câu Chuyện Của Chúng Tôi - PetWorld",
  description: "Tìm hiểu về nguồn cảm hứng sáng lập PetWorld - Cửa hàng phụ kiện và thức ăn hữu cơ cao cấp cho chó mèo. Chúng tôi hướng tới sự minh bạch và chất lượng thật sự cho thú cưng.",
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
          <span className="about-badge">
            <span className="about-badge-dot" />
            Về PetWorld
          </span>
          <h1 className="about-main-title">
            Chăm sóc bé cưng bằng <span>cả trái tim</span>
          </h1>
          <p className="about-intro-text">
            Chúng tôi tin rằng thú cưng không chỉ là động vật nuôi trong nhà. Chúng là gia đình, là niềm vui vô điều kiện, và xứng đáng nhận được những điều tốt đẹp nhất.
          </p>
        </header>

        {/* Stats Section */}
        <section className="about-stats-section">
          <div className="stat-card">
            <div className="stat-number">5.000+</div>
            <div className="stat-label">Bé Cưng Hạnh Phúc</div>
            <div className="stat-desc">Được chăm sóc bằng nguồn thực phẩm chất lượng.</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">100%</div>
            <div className="stat-label">Hữu Cơ Sạch</div>
            <div className="stat-desc">Không chất bảo quản nhân tạo hay phụ gia độc hại.</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">15+</div>
            <div className="stat-label">Đối Tác Uy Tín</div>
            <div className="stat-desc">Đồng hành cùng các thương hiệu dinh dưỡng lớn.</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">24/7</div>
            <div className="stat-label">Tư Vấn Thú Y</div>
            <div className="stat-desc">Đội ngũ chuyên viên tận tâm hỗ trợ sức khỏe bé.</div>
          </div>
        </section>

        {/* Story Section */}
        <section className="story-section">
          <div className="story-text-container">
            <h2 className="story-heading">Bắt đầu từ Bơ - Chú chó bị dị ứng nặng</h2>
            <div className="story-paragraphs">
              <p>
                Mọi chuyện bắt đầu vào năm 2024 khi Bơ - chú chó Golden Retriever của tôi bị dị ứng thức ăn nghiêm trọng. Lông rụng từng mảng lớn, da nổi đỏ và ngứa ngáy liên tục.
              </p>
              <p>
                Khi đi khắp các siêu thị lớn nhỏ tại Sài Gòn, tôi chỉ thấy những bao thức ăn đóng gói chứa đầy chất bảo quản không rõ nguồn gốc, hoặc những món phụ kiện nhựa thô kệch, sắc nhọn dễ gây chấn thương khi cắn gặm. Tôi tự hỏi:
              </p>
              
              <div className="story-quote-card">
                <svg className="quote-svg" width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M11.192 15.757c0-.962-.186-1.61-.557-1.943-.371-.333-1.118-.5-2.242-.5H8c.095-1.577.619-2.822 1.571-3.738C10.524 8.66 11.952 8.2 13.857 8V6C10.714 6.2 8.238 7.37 6.429 9.512 4.619 11.654 3.714 14.53 3.714 18.143v1.857h7.478v-4.243zm8 0c0-.962-.186-1.61-.556-1.943-.371-.333-1.119-.5-2.243-.5h-.393c.095-1.577.619-2.822 1.571-3.738.952-.916 2.38-1.376 4.285-1.576V6c-3.143.2-5.619 1.37-7.428 3.512-1.81 2.154-2.714 5.03-2.714 8.643v1.857h7.478v-4.243z"/>
                </svg>
                <div className="story-quote-text">
                  "Tại sao chúng ta lại cho những người bạn bốn chân ăn những thứ chúng ta không hiểu rõ và mặc những chiếc vòng cổ thô ráp?"
                </div>
              </div>

              <p>
                PetWorld ra đời từ chính trăn trở đó. Chúng tôi cam kết mang lại sự minh bạch, an toàn và các sản phẩm chất lượng đích thực để mỗi chú chó, chú mèo đều được lớn lên khỏe mạnh và thời trang nhất.
              </p>
            </div>
          </div>

          <div className="story-image-box">
            <img src="/founder_with_dog.png" alt="Người sáng lập PetWorld bên chú chó Golden cưng" className="story-img" />
          </div>
        </section>

        {/* Development Timeline Section */}
        <section className="timeline-section-container">
          <div className="timeline-header">
            <h2 className="story-heading">Hành trình phát triển</h2>
            <p style={{ color: "#5b5550", fontSize: "1.05rem", lineHeight: "1.6" }}>
              Từ một ý tưởng nhỏ xuất phát từ tình thương, PetWorld đã từng bước lớn mạnh để đồng hành cùng hàng ngàn gia đình nuôi thú cưng.
            </p>
          </div>

          <div className="timeline-vertical">
            {/* 2024 */}
            <div className="timeline-item left">
              <div className="timeline-dot" />
              <div className="timeline-content-box">
                <span className="timeline-year">2024</span>
                <h3 className="timeline-title">Nguồn Cảm Hứng & Khởi Đầu</h3>
                <p className="timeline-desc">
                  Sự cố dị ứng thức ăn nghiêm trọng của chú cún Bơ đã thôi thúc chúng tôi nghiên cứu sâu về thành phần dinh dưỡng động vật và bắt đầu hành trình tìm kiếm nguồn thực phẩm hữu cơ sạch.
                </p>
              </div>
            </div>

            {/* 2025 */}
            <div className="timeline-item right">
              <div className="timeline-dot" />
              <div className="timeline-content-box">
                <span className="timeline-year">2025</span>
                <h3 className="timeline-title">Ra Mắt Cửa Hàng Đầu Tiên</h3>
                <p className="timeline-desc">
                  Thành lập siêu thị PetWorld đầu tiên tại TP. Hồ Chí Minh với hơn 500 mặt hàng hạt và pate hữu cơ được tuyển chọn và kiểm định nghiêm ngặt, mở ra chương mới cho dinh dưỡng thú cưng.
                </p>
              </div>
            </div>

            {/* 2026 */}
            <div className="timeline-item left">
              <div className="timeline-dot" />
              <div className="timeline-content-box">
                <span className="timeline-year">2026</span>
                <h3 className="timeline-title">Mở Rộng Hệ Sinh Thái</h3>
                <p className="timeline-desc">
                  Ra mắt dòng phụ kiện công thái học độc quyền thiết kế riêng cho tầm vóc thú cưng Đông Nam Á, song song với việc số hóa nền tảng mua sắm và chăm sóc thú cưng trực tuyến.
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Philosophy Section */}
        <section className="philosophy-section-container">
          <div className="philosophy-layout">
            <div className="story-image-box">
              <img src="/clean_pet_food_and_toys.png" alt="Sản phẩm dinh dưỡng và phụ kiện chất lượng cao tại PetWorld" className="story-img" />
            </div>

            <div style={{ paddingLeft: "10px" }}>
              <h2 className="story-heading">Triết lý sản phẩm chuẩn mực</h2>
              <p style={{ color: "#5b5550", fontSize: "1.05rem", lineHeight: "1.7" }}>
                Chúng tôi không bán bất cứ thứ gì mà các "trợ lý" chó mèo của chính chúng tôi chưa từng ăn thử hoặc đeo thử. Ba tiêu chuẩn khắt khe tại PetWorld bao gồm:
              </p>

              <div className="philosophy-grid">
                {/* Value 1 */}
                <div className="philosophy-card">
                  <div className="philosophy-icon-container">
                    {/* SVG Leaf */}
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 1 9.8a7 7 0 0 1-9 8.2z" />
                      <path d="M9 22v-4" />
                    </svg>
                  </div>
                  <div className="philosophy-content">
                    <h4>Dinh dưỡng hữu cơ sạch 100%</h4>
                    <p>Các sản phẩm hạt khô, pate và thực phẩm chức năng luôn có nguồn gốc tự nhiên rõ ràng, nói không với phụ gia nhân tạo gây hại gan thận thú cưng.</p>
                  </div>
                </div>

                {/* Value 2 */}
                <div className="philosophy-card">
                  <div className="philosophy-icon-container">
                    {/* SVG Shield / Safety */}
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                  </div>
                  <div className="philosophy-content">
                    <h4>Phụ kiện công thái học (Ergonomics)</h4>
                    <p>Mỗi mẫu vòng cổ, yếm dắt hay chuồng đệm đều được nghiên cứu kỹ để tránh làm hằn đau, trầy xước da hay bí lông của bé cưng khi chạy nhảy hoạt động.</p>
                  </div>
                </div>

                {/* Value 3 */}
                <div className="philosophy-card">
                  <div className="philosophy-icon-container">
                    {/* SVG Paw Print */}
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                      <circle cx="12" cy="5" r="2.5" />
                      <circle cx="5.5" cy="9.5" r="2.5" />
                      <circle cx="18.5" cy="9.5" r="2.5" />
                      <path d="M12 11c-2.5 0-4.5 2-4.5 5s1.5 4 4.5 4 4.5-1 4.5-4-1.5-5-4.5-5z" />
                    </svg>
                  </div>
                  <div className="philosophy-content">
                    <h4>Thử nghiệm thực tế bởi thú cưng</h4>
                    <p>Sản phẩm trước khi lên kệ sẽ qua khâu trải nghiệm thực tế về độ bền, độ ngon miệng và tính ứng dụng bởi chính những chú cún mèo trong văn phòng của chúng tôi.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Companion Brands Section */}
        {companionBrands.length > 0 && (
          <section className="press-section">
            <h3 className="press-title">Đồng hành cùng các nhãn hàng</h3>
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

        {/* Founder Team Section */}
        <section className="team-container">
          <div className="section-title-wrapper" style={{ marginBottom: "10px" }}>
            <h2 className="story-heading" style={{ textAlign: "center" }}>Đội ngũ sáng lập</h2>
            <p style={{ color: "#5b5550", fontSize: "1.05rem", maxWidth: "600px", margin: "0 auto 45px auto", lineHeight: "1.6" }}>
              Những con người đứng sau từng gói hạt dinh dưỡng và những thiết kế phụ kiện tỉ mỉ trao đến tay các bé cưng.
            </p>
          </div>

          <div className="team-grid">
            {/* Member 1 */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_trung.png" alt="Anh Trần Trung - Sáng lập PetWorld" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Anh Trần Trung</h4>
              <div className="team-member-role">Sáng lập & Điều hành</div>
              <p className="team-member-bio">
                Chịu trách nhiệm chính trong việc thiết lập định hướng thương hiệu, vận hành hệ thống và tuyển chọn các nguồn nguyên liệu hữu cơ nhập khẩu an toàn.
              </p>
              <div className="team-member-pet dog">
                <span>🐕</span> Đồng hành: Cún Bơ (Golden Retriever)
              </div>
            </div>

            {/* Member 2 */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_linh.png" alt="Chị Khánh Linh - Bác sĩ dinh dưỡng" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Chị Khánh Linh</h4>
              <div className="team-member-role">Bác sĩ cố vấn dinh dưỡng</div>
              <p className="team-member-bio">
                Kiểm định nghiêm ngặt hàm lượng dinh dưỡng động vật, đảm bảo các dòng thức ăn khô và pate an toàn tuyệt đối với hệ tiêu hóa nhạy cảm của thú cưng.
              </p>
              <div className="team-member-pet cat">
                <span>🐈</span> Đồng hành: Mèo Mun (Mèo ta)
              </div>
            </div>

            {/* Member 3 */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_minh.png" alt="Anh Hoàng Minh - Giám đốc thiết kế" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Anh Hoàng Minh</h4>
              <div className="team-member-role">Giám đốc thiết kế phụ kiện</div>
              <p className="team-member-bio">
                Kiến tạo các bộ sưu tập vòng cổ da cao cấp, yếm ngực công thái học giảm áp lực xương và đồ chơi kích thích trí thông minh vận động của thú cưng.
              </p>
              <div className="team-member-pet dog">
                <span>🐕</span> Đồng hành: Cún Bánh Bao (Corgi)
              </div>
            </div>
          </div>

          {/* Chief Pet Officers Section */}
          <div className="team-cpo-header">
            <span className="cpo-badge">Gặp gỡ ban cố vấn đặc biệt</span>
            <h3 className="story-heading" style={{ textAlign: "center", marginTop: "10px" }}>Đội Ngũ "Cựu Ước" Bốn Chân</h3>
            <p style={{ color: "#5b5550", fontSize: "1.05rem", maxWidth: "600px", margin: "0 auto 40px auto", lineHeight: "1.6" }}>
              Những "trợ lý" bốn chân luôn làm việc chăm chỉ nhất để thử nghiệm sản phẩm và tiếp thêm năng lượng hạnh phúc cho văn phòng PetWorld.
            </p>
          </div>

          <div className="team-grid" style={{ justifyContent: "center", maxWidth: "800px", margin: "0 auto" }}>
            {/* CPO Bơ */}
            <div className="team-member">
              <div className="team-member-img-wrapper" style={{ filter: "brightness(0.95)" }}>
                <img src="/founder_with_dog.png" alt="Cún Bơ - Chief Treats Officer" className="team-member-img" style={{ objectPosition: "65% 30%", transform: "scale(1.2)" }} />
              </div>
              <h4 className="team-member-name">Cún Bơ (Golden Retriever)</h4>
              <div className="team-member-role" style={{ background: "linear-gradient(135deg, #ff782d 0%, #9b51e0 100%)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>
                Chief Treats Officer (CTO)
              </div>
              <p className="team-member-bio">
                Chuyên viên kiểm định độ ngon miệng của hạt dinh dưỡng và pate hữu cơ mới. Có 2 năm kinh nghiệm xin ăn và luôn ngủ trưa dưới chân bàn của CEO.
              </p>
              <div className="team-member-pet dog">
                <span>🍖</span> Sở thích: Pate gan & bóng tennis
              </div>
            </div>

            {/* CPO Mèo Mun */}
            <div className="team-member">
              <div className="team-member-img-wrapper" style={{ filter: "brightness(0.95)" }}>
                <img src="/clean_pet_food_and_toys.png" alt="Mèo Mun - Head of Nap Operations" className="team-member-img" style={{ objectPosition: "50% 50%", transform: "scale(1.1)" }} />
              </div>
              <h4 className="team-member-name">Mèo Mun (Mèo mướp ta)</h4>
              <div className="team-member-role" style={{ background: "linear-gradient(135deg, #ff782d 0%, #9b51e0 100%)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>
                Head of Nap Operations (HNO)
              </div>
              <p className="team-member-bio">
                Trưởng phòng thử nghiệm độ êm ái của đệm nằm công thái học và kiểm thử độ bền của dây dắt bằng cách cào móng. Thích tắm nắng và phớt lờ mọi người.
              </p>
              <div className="team-member-pet cat">
                <span>🐟</span> Sở thích: Cá hồi sấy & ngủ 18 tiếng/ngày
              </div>
            </div>
          </div>
        </section>

        {/* CTA section */}
        <section className="cta-box">
          <h2 className="cta-title">Mang lại sự chăm sóc tốt nhất cho bé cưng</h2>
          <p className="cta-desc">
            Hãy khám phá ngay các bộ sưu tập phụ kiện bền bỉ và nguồn dinh dưỡng hữu cơ an toàn được tuyển chọn cho chó mèo của bạn.
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
