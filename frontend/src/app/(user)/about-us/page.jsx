import Link from "next/link";
import { getHomeData } from "@/lib/api";
import { resolveBrandImage } from "@/lib/format";

export const metadata = {
  title: "Câu Chuyện Của Chúng Tôi - PetWorld",
  description: "Tìm hiểu về nguồn cảm hứng sáng lập PetWorld - Cửa hàng phụ kiện và thức ăn hữu cơ cao cấp cho chó mèo. Chúng tôi hướng tới sự minh bạch và chất lượng thật sự cho thú cưng.",
};

export default async function AboutUsPage() {
  const homeData = await getHomeData();
  const brands = homeData.brands ?? [];
  const companionBrands = brands.slice(0, 5);
  return (
    <main className="main-content" style={{ minHeight: "100vh", backgroundColor: "#faf8f6", color: "#2d2926", fontFamily: "var(--font-inter), sans-serif", paddingBottom: "80px" }}>
      {/* CSS Stylesheet cho trang About Us kiểu DTC hiện đại */}
      <style dangerouslySetInnerHTML={{ __html: `
        .about-wrapper {
          max-width: 1140px;
          margin: 0 auto;
          padding: 0 24px;
        }

        /* Banner tối giản sang trọng */
        .about-header-section {
          text-align: center;
          padding: 80px 0 60px 0;
          border-bottom: 1px solid #ebdcd0;
          margin-bottom: 60px;
        }
        .about-tagline {
          font-size: 0.85rem;
          text-transform: uppercase;
          letter-spacing: 2.5px;
          color: #ff782d;
          font-weight: 700;
          margin-bottom: 16px;
          display: block;
        }
        .about-main-title {
          font-size: 3rem;
          font-weight: 800;
          line-height: 1.2;
          letter-spacing: -1px;
          color: #1e1e24;
          margin-bottom: 24px;
        }
        .about-intro-text {
          font-size: 1.25rem;
          color: #5b5550;
          max-width: 720px;
          margin: 0 auto;
          line-height: 1.6;
          font-weight: 400;
        }

        /* Khung lưới bất đối xứng cho Story */
        .story-section {
          display: grid;
          grid-template-columns: 1.1fr 0.9fr;
          gap: 60px;
          align-items: center;
          margin-bottom: 80px;
        }
        .story-text-container {
          padding-right: 20px;
        }
        .story-heading {
          font-size: 2rem;
          font-weight: 800;
          color: #1e1e24;
          margin-bottom: 24px;
          line-height: 1.3;
        }
        .story-paragraphs {
          font-size: 1.05rem;
          color: #5b5550;
          line-height: 1.75;
        }
        .story-paragraphs p {
          margin-bottom: 20px;
        }
        .story-quote {
          font-size: 1.2rem;
          font-style: italic;
          color: #ff782d;
          font-weight: 600;
          border-left: 3px solid #ff782d;
          padding-left: 20px;
          margin: 30px 0;
          line-height: 1.6;
        }
        .story-image-box {
          border-radius: 20px;
          overflow: hidden;
          box-shadow: 0 15px 35px rgba(91, 46, 17, 0.08);
          background-color: #ffebe0;
          border: 1px solid #ebdcd0;
          position: relative;
        }
        .story-img {
          width: 100%;
          height: auto;
          display: block;
          object-fit: cover;
          transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .story-image-box:hover .story-img {
          transform: scale(1.03);
        }

        /* Grid Triết lý / Quy trình */
        .philosophy-section {
          display: grid;
          grid-template-columns: 0.9fr 1.1fr;
          gap: 60px;
          align-items: center;
          margin-bottom: 80px;
          padding-top: 40px;
          border-top: 1px solid #ebdcd0;
        }
        .standard-list {
          list-style: none;
          padding: 0;
          margin-top: 30px;
        }
        .standard-item {
          display: flex;
          gap: 20px;
          margin-bottom: 35px;
        }
        .standard-num {
          font-size: 1.25rem;
          font-weight: 800;
          color: #ff782d;
          background: rgba(255, 120, 45, 0.1);
          width: 44px;
          height: 44px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }
        .standard-content h4 {
          font-size: 1.15rem;
          font-weight: 700;
          color: #1e1e24;
          margin-bottom: 8px;
        }
        .standard-content p {
          font-size: 0.98rem;
          color: #5b5550;
          line-height: 1.6;
        }

        /* Press/Media Trust Bar */
        .press-section {
          text-align: center;
          padding: 40px 0;
          border-top: 1px solid #ebdcd0;
          border-bottom: 1px solid #ebdcd0;
          margin-bottom: 80px;
        }
        .press-title {
          font-size: 0.8rem;
          text-transform: uppercase;
          letter-spacing: 2px;
          color: #8c857e;
          font-weight: 700;
          margin-bottom: 30px;
        }
        .press-logos {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 60px;
          flex-wrap: wrap;
        }
        .press-logo-item {
          font-size: 1.25rem;
          font-weight: 800;
          color: #b5acaf;
          letter-spacing: -0.5px;
          user-select: none;
          transition: color 0.3s ease;
        }
        .press-logo-item:hover {
          color: #ff782d;
        }

        /* Team Section */
        .team-container {
          text-align: center;
          margin-bottom: 80px;
        }
        .team-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
          gap: 30px;
          margin-top: 40px;
        }
        .team-member {
          background: #ffffff;
          border: 1px solid #ebdcd0;
          border-radius: 20px;
          padding: 20px;
          text-align: left;
          transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1);
          display: flex;
          flex-direction: column;
        }
        .team-member:hover {
          transform: translateY(-6px);
          box-shadow: 0 20px 40px rgba(91, 46, 17, 0.08);
          border-color: rgba(255, 120, 45, 0.25);
        }
        .team-member-img-wrapper {
          border-radius: 14px;
          overflow: hidden;
          aspect-ratio: 1 / 1;
          margin-bottom: 20px;
          background-color: #ffebe0;
          border: 1px solid #f3e9e2;
          position: relative;
        }
        .team-member-img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          display: block;
          transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .team-member:hover .team-member-img {
          transform: scale(1.05);
        }
        .team-member-name {
          font-size: 1.3rem;
          font-weight: 800;
          color: #1e1e24;
          margin-bottom: 4px;
        }
        .team-member-role {
          font-size: 0.82rem;
          text-transform: uppercase;
          color: #ff782d;
          font-weight: 700;
          letter-spacing: 1px;
          margin-bottom: 12px;
        }
        .team-member-bio {
          font-size: 0.95rem;
          color: #5b5550;
          line-height: 1.6;
          margin-bottom: 20px;
          flex-grow: 1;
        }
        .team-member-pet {
          font-size: 0.82rem;
          padding: 6px 12px;
          border-radius: 30px;
          font-weight: 600;
          display: inline-flex;
          align-items: center;
          gap: 6px;
          margin-top: auto;
          width: fit-content;
          transition: transform 0.2s ease;
        }
        .team-member-pet:hover {
          transform: scale(1.05);
        }
        .team-member-pet.dog {
          background: rgba(255, 120, 45, 0.08);
          color: #ff782d;
          border: 1px solid rgba(255, 120, 45, 0.15);
        }
        .team-member-pet.cat {
          background: rgba(155, 81, 224, 0.08);
          color: #9b51e0;
          border: 1px solid rgba(155, 81, 224, 0.15);
        }

        /* Modern CTA */
        .cta-box {
          background-color: #1e1e24;
          color: #ffffff;
          border-radius: 24px;
          padding: 80px 40px;
          text-align: center;
          box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .cta-title {
          font-size: 2.5rem;
          font-weight: 800;
          margin-bottom: 20px;
          letter-spacing: -0.5px;
        }
        .cta-desc {
          font-size: 1.15rem;
          max-width: 600px;
          margin: 0 auto 35px auto;
          opacity: 0.85;
          line-height: 1.6;
        }
        .cta-button {
          background-color: #ff782d;
          color: #ffffff;
          padding: 16px 36px;
          font-size: 1.05rem;
          font-weight: 700;
          border-radius: 30px;
          text-decoration: none;
          display: inline-block;
          transition: background-color 0.3s ease, transform 0.3s ease;
          box-shadow: 0 4px 15px rgba(255, 120, 45, 0.3);
        }
        .cta-button:hover {
          background-color: #e9661c;
          transform: translateY(-2px);
        }
        .brand-partner-logo {
          height: 45px;
          max-width: 130px;
          object-fit: contain;
          filter: grayscale(100%) opacity(60%);
          transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
          cursor: pointer;
        }
        .brand-partner-logo:hover {
          filter: grayscale(0%) opacity(100%);
          transform: scale(1.08);
        }

        /* Responsive */
        @media (max-width: 868px) {
          .story-section, .philosophy-section {
            grid-template-columns: 1fr;
            gap: 40px;
          }
          .about-main-title {
            font-size: 2.25rem;
          }
          .about-header-section {
            padding: 50px 0 40px 0;
          }
          .philosophy-section {
            padding-top: 30px;
          }
          .cta-box {
            padding: 50px 20px;
          }
        }
      ` }} />

      <div className="about-wrapper">
        {/* Breadcrumb */}
        <nav className="shop-breadcrumb" style={{ padding: "24px 0", marginBottom: "10px" }}>
          <Link href="/">Trang chủ</Link> /
          <span className="shop-breadcrumb-sep">
            <span className="shop-breadcrumb-current" style={{ marginLeft: "6px" }}>Về chúng tôi</span>
          </span>
        </nav>

        {/* Hero Section */}
        <header className="about-header-section">
          <span className="about-tagline">Chúng tôi là PetWorld</span>
          <h1 className="about-main-title">Chăm sóc bé cưng bằng cả trái tim</h1>
          <p className="about-intro-text">
            Chúng tôi tin rằng thú cưng không chỉ là động vật nuôi trong nhà. Chúng là gia đình, là niềm vui vô điều kiện, và xứng đáng được nhận những điều tốt đẹp nhất.
          </p>
        </header>

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
              <div className="story-quote">
                "Tại sao chúng ta lại cho những người bạn bốn chân ăn những thứ chúng ta không hiểu rõ và mặc những chiếc vòng cổ thô ráp?"
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

        {/* Philosophy Section */}
        <section className="philosophy-section">
          <div className="story-image-box">
            <img src="/clean_pet_food_and_toys.png" alt="Sản phẩm dinh dưỡng và phụ kiện chất lượng cao tại PetWorld" className="story-img" />
          </div>

          <div style={{ paddingLeft: "10px" }}>
            <h2 className="story-heading">Triết lý sản phẩm chuẩn mực</h2>
            <p style={{ color: "#5b5550", fontSize: "1.05rem", lineHeight: "1.7" }}>
              Chúng tôi không bán bất cứ thứ gì mà các "trợ lý" chó mèo của chính chúng tôi chưa từng ăn thử hoặc đeo thử. Ba tiêu chuẩn khắt khe tại PetWorld bao gồm:
            </p>

            <ul className="standard-list">
              <li className="standard-item">
                <span className="standard-num">01</span>
                <div className="standard-content">
                  <h4>Dinh dưỡng hữu cơ sạch 100%</h4>
                  <p>Các sản phẩm hạt khô, pate và thực phẩm chức năng luôn có nguồn gốc hữu cơ tự nhiên rõ ràng, nói không với phụ gia nhân tạo gây hại gan thận thú cưng.</p>
                </div>
              </li>
              <li className="standard-item">
                <span className="standard-num">02</span>
                <div className="standard-content">
                  <h4>Phụ kiện công thái học (Ergonomics)</h4>
                  <p>Mỗi mẫu vòng cổ, yếm dắt hay chuồng đệm đều được nghiên cứu kỹ để tránh làm hằn đau, trầy xước da hay bí lông của bé cưng khi chạy nhảy hoạt động.</p>
                </div>
              </li>
              <li className="standard-item">
                <span className="standard-num">03</span>
                <div className="standard-content">
                  <h4>Thử nghiệm thực tế bởi thú cưng</h4>
                  <p>Sản phẩm trước khi lên kệ sẽ qua khâu trải nghiệm thực tế về độ bền, độ ngon miệng và tính ứng dụng bởi chính những chú cún mèo trong văn phòng của chúng tôi.</p>
                </div>
              </li>
            </ul>
          </div>
        </section>

        {/* Companion Brands Section */}
        {companionBrands.length > 0 && (
          <section className="press-section">
            <h3 className="press-title">Đồng hành cùng các nhãn hàng</h3>
            <div className="press-logos" style={{ display: "flex", justifyContent: "center", alignItems: "center", gap: "50px", flexWrap: "wrap" }}>
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
            <p style={{ color: "#5b5550", fontSize: "1.05rem", maxWidth: "600px", margin: "0 auto 40px auto", lineHeight: "1.6" }}>
              Những người đứng sau từng gói hạt dinh dưỡng và những thiết kế phụ kiện tỉ mỉ trao đến tay các bé cưng.
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
                Chịu trách nhiệm chính trong việc thiết lập triết lý thương hiệu và tuyển chọn khắt khe các nguồn nguyên liệu hữu cơ nhập khẩu an toàn.
              </p>
              <div className="team-member-pet dog">
                <span>🐕</span> Đồng hành: Cún Bơ (Golden Retriever)
              </div>
            </div>

            {/* Member 2 */}
            <div className="team-member">
              <div className="team-member-img-wrapper">
                <img src="/team_linh.png" alt="Chị Khánh Linh - Cố vấn dinh dưỡng" className="team-member-img" />
              </div>
              <h4 className="team-member-name">Chị Khánh Linh</h4>
              <div className="team-member-role">Bác sĩ cố vấn dinh dưỡng</div>
              <p className="team-member-bio">
                Chịu trách nhiệm kiểm định hàm lượng dinh dưỡng động vật, đảm bảo các dòng thức ăn khô và pate an toàn tuyệt đối với hệ tiêu hóa thú cưng.
              </p>
              <div className="team-member-pet cat">
                <span>🐈</span> Đồng hành: Mèo Mun (Mèo mướp ta)
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
                Là nhà thiết kế đứng sau các bộ sưu tập vòng cổ da, yếm ngực công thái học thời thượng và đồ chơi kích thích trí tuệ thú cưng.
              </p>
              <div className="team-member-pet dog">
                <span>🐕</span> Đồng hành: Cún Bánh Bao (Corgi)
              </div>
            </div>
          </div>
        </section>

        {/* CTA section */}
        <section className="cta-box">
          <h2 className="cta-title">Mang lại sự chăm sóc tốt nhất cho bé cưng</h2>
          <p className="cta-desc">
            Hãy khám phá ngay các bộ sưu tập phụ kiện bền bỉ và nguồn dinh dưỡng sạch an toàn được tuyển chọn cho chó mèo của bạn.
          </p>
          <Link href="/shop" className="cta-button">
            Ghé Cửa Hàng Ngay →
          </Link>
        </section>
      </div>
    </main>
  );
}
