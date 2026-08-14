import Link from "next/link";
import { getHomeData } from "@/lib/api";
import { resolveBrandImage } from "@/lib/format";
import "./about-us.css";

export const metadata = {
  title: "Đề Tài Đồ Án Tốt Nghiệp - PetWorld | Hệ Thống Thương Mại Điện Tử Thú Cưng",
  description: "Giới thiệu đề tài Đồ án Tốt nghiệp xây dựng hệ thống thương mại điện tử PetWorld bởi nhóm sinh viên CNTT.",
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
          <span className="about-badge">Đồ án tốt nghiệp công nghệ thông tin ngành Lập Trình Web</span>
          <h1 className="about-main-title">
            Hệ thống thương mại điện tử <span>PetWorld</span>
          </h1>
          <p className="about-intro-text">
            Đề tài tập trung nghiên cứu và xây dựng <strong>hệ thống thương mại điện tử PetWorld</strong>, nhằm cung cấp các sản phẩm đồ ăn và nhu yếu phẩm dành cho thú cưng. Website giúp người dùng dễ dàng tìm kiếm, lựa chọn và mua các sản phẩm phù hợp cho chó, mèo thông qua hình thức mua sắm trực tuyến.
          </p>
        </header>

        {/* Story Section */}
        <section className="about-section founder-story-grid">
          <div className="founder-letter-text">
            <h2 className="about-serif-title">Giới thiệu đề tài đồ án</h2>
            <p>Kính gửi Quý Hội Đồng,</p>
            <p>
              Đề tài <strong>“Xây dựng hệ thống thương mại điện tử PetWorld”</strong> được thực hiện nhằm đáp ứng nhu cầu mua sắm các sản phẩm dành cho thú cưng ngày càng phổ biến hiện nay. PetWorld là một website thương mại điện tử được xây dựng với mục đích cung cấp các sản phẩm, đặc biệt là <strong>đồ ăn và thức ăn dinh dưỡng dành cho chó, mèo</strong>, giúp người dùng dễ dàng tìm kiếm, lựa chọn và đặt mua sản phẩm trực tuyến.
            </p>
            <p>
              Mục tiêu của đề tài là xây dựng một hệ thống bán hàng trực tuyến có giao diện thân thiện, dễ sử dụng và đáp ứng được các chức năng cơ bản của một website thương mại điện tử. Hệ thống cho phép khách hàng xem danh sách sản phẩm, tìm kiếm và lọc sản phẩm, xem thông tin chi tiết, thêm sản phẩm vào giỏ hàng, sử dụng voucher, đặt hàng và theo dõi đơn hàng. Bên cạnh đó, hệ thống còn có trang quản trị giúp quản lý sản phẩm, danh mục, thương hiệu, người dùng, đơn hàng và các chương trình khuyến mãi.
            </p>
            <p>
              Về công nghệ, hệ thống được xây dựng theo mô hình <strong>Frontend – Backend</strong>, trong đó <strong>Next.js (React Framework)</strong> được sử dụng để phát triển giao diện người dùng, mang lại trải nghiệm tương tác và tốc độ xử lý tốt. <strong>Laravel Framework</strong> được sử dụng để xây dựng RESTful API, xử lý các nghiệp vụ của hệ thống và kết nối với cơ sở dữ liệu <strong>MySQL</strong>. Việc phân tách Frontend và Backend giúp hệ thống có cấu trúc rõ ràng, dễ quản lý và thuận tiện cho việc phát triển thêm các chức năng trong tương lai.
            </p>
            <p>
              Thông qua đề tài <strong>PetWorld</strong>, nhóm có cơ hội vận dụng những kiến thức đã học về lập trình web, cơ sở dữ liệu, xây dựng API và phát triển hệ thống thương mại điện tử vào một sản phẩm thực tế. Qua đó, nhóm mong muốn xây dựng được một website bán đồ ăn cho thú cưng có tính ứng dụng, đáp ứng được những nhu cầu mua sắm cơ bản của người dùng.
            </p>
            
            <div className="founder-signature-box">
              <div>
                <div className="founder-signature-name">Thế Điểm</div>
                <div className="founder-signature-role">Trưởng nhóm - backend</div>
              </div>
            </div>
          </div>

          <div className="founder-photo-box">
            <img src="/founder_with_dog.png" alt="Nhóm phát triển dự án PetWorld" className="founder-photo-img" />
            <p className="founder-photo-caption">Đại diện nhóm phát triển và chú cún Leo thử nghiệm sản phẩm (2026)</p>
          </div>
        </section>

        {/* Vision & Mission Section */}
        <section className="about-section vision-mission-grid">
          <div className="vision-mission-col">
            <h3>Mục tiêu đề tài</h3>
            <p className="about-p">
              Nghiên cứu và phát triển hệ thống thương mại điện tử PetWorld hướng tới các mục tiêu cụ thể:
            </p>
            <ul>
              <li><strong>Hiệu năng và giao diện</strong>: Xây dựng website có tốc độ tải ổn định, giao diện dễ sử dụng và có khả năng hiển thị tốt trên nhiều thiết bị.</li>
              <li><strong>Quản lý vận chuyển</strong>: Tích hợp API Giao Hàng Nhanh (GHN) để hỗ trợ tính phí vận chuyển và cập nhật thông tin đơn hàng.</li>
              <li><strong>Trải nghiệm mua sắm</strong>: Xây dựng các chức năng giỏ hàng, đặt hàng, thanh toán và sử dụng mã giảm giá nhằm giúp quá trình mua sắm thuận tiện hơn.</li>
              <li><strong>Hỗ trợ khách hàng</strong>: Xây dựng chức năng hỗ trợ và tư vấn khách hàng, giúp người dùng dễ dàng tìm hiểu sản phẩm và sử dụng website.</li>
            </ul>
          </div>
          <div className="vision-mission-col">
            <h3>Nhiệm vụ nghiên cứu</h3>
            <p className="about-p">
              Hoàn thành các mục tiêu về kỹ thuật và xây dựng phần mềm thông qua các nội dung chính:
            </p>
            <ul>
              <li>Thiết kế cơ sở dữ liệu phù hợp để quản lý sản phẩm, danh mục, thương hiệu, đơn hàng và các thông tin liên quan đến sản phẩm.</li>
              <li>Xây dựng <strong>RESTful API</strong> bằng Laravel để xử lý các chức năng của hệ thống và đảm bảo việc trao đổi dữ liệu giữa Frontend và Backend.</li>
              <li>Thiết kế giao diện <strong>Responsive</strong>, giúp website hiển thị và sử dụng thuận tiện trên máy tính, điện thoại và các thiết bị có kích thước màn hình khác nhau.</li>
              <li>Tích hợp <strong>API Giao Hàng Nhanh (GHN)</strong> để hỗ trợ tính phí vận chuyển và cập nhật thông tin liên quan đến đơn hàng.</li>
            </ul>
          </div>
        </section>

        {/* Core Values Section */}
        <section className="about-section">
          <h2 className="about-serif-title" style={{ textAlign: "center", marginBottom: "40px" }}>Giải pháp kỹ thuật nổi bật</h2>
          <div className="values-grid">
            {/* Value 1 */}
            <div className="value-col">
              <span className="value-num">01.</span>
              <h4 className="value-title">Next.js & Giao diện người dùng</h4>
              <p className="value-desc">
                Sử dụng <strong>Next.js</strong> để xây dựng giao diện website, giúp trang web hoạt động ổn định, tốc độ tải tốt và hiển thị phù hợp trên nhiều thiết bị như máy tính, máy tính bảng và điện thoại.
              </p>
            </div>

            {/* Value 2 */}
            <div className="value-col">
              <span className="value-num">02.</span>
              <h4 className="value-title">Laravel RESTful API & Bảo mật</h4>
              <p className="value-desc">
                Sử dụng <strong>Laravel</strong> để xây dựng hệ thống Backend và RESTful API, giúp xử lý các chức năng như sản phẩm, giỏ hàng, đơn hàng, người dùng và quản lý hệ thống. Việc tách Frontend và Backend giúp mã nguồn dễ quản lý và thuận tiện cho việc phát triển thêm các chức năng sau này.
              </p>
            </div>

            {/* Value 3 */}
            <div className="value-col">
              <span className="value-num">03.</span>
              <h4 className="value-title">Tích hợp dịch vụ vận chuyển</h4>
              <p className="value-desc">
                Tích hợp <strong>API Giao Hàng Nhanh (GHN)</strong> để hỗ trợ lựa chọn khu vực giao hàng và tính phí vận chuyển dựa trên thông tin địa chỉ của khách hàng. Hệ thống cũng hỗ trợ cập nhật thông tin vận chuyển và trạng thái đơn hàng.
              </p>
            </div>
          </div>
        </section>

        {/* Team Section */}
        <section className="about-section">
          <h2 className="about-serif-title" style={{ textAlign: "center", marginBottom: "45px" }}>Thành viên thực hiện đồ án</h2>
          <div className="team-list-grid">
            {/* Diem */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_diem.png" alt="Thế Điểm" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Thế Điểm</h4>
              <div className="team-role">Trưởng nhóm - backend</div>
              <p className="team-desc">
                Nhóm trưởng điều phối dự án. Tập trung code Backend Laravel, API đăng nhập, giỏ hàng, kết nối cổng thanh toán tự động và GHN.
              </p>
            </div>

            {/* Trung */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_trung.png" alt="Công Trung" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Công Trung</h4>
              <div className="team-role">Front-end</div>
              <p className="team-desc">
                Hỗ trợ code Frontend Next.js, xây dựng các component tương tác và tối ưu giao diện website.
              </p>
            </div>

            {/* Minh */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_minh.png" alt="Văn Minh" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Văn Minh</h4>
              <div className="team-role">Front-end</div>
              <p className="team-desc">
                Chuyên trách giao diện Frontend Next.js. Chăm chút trải nghiệm người dùng, làm giao diện responsive trên điện thoại và đồng bộ font chữ hệ thống.
              </p>
            </div>

            {/* Phat */}
            <div>
              <div className="team-avatar-box">
                <img src="/team_phat.png" alt="Trần Phát" className="team-avatar-img" />
              </div>
              <h4 className="team-name">Trần Phát</h4>
              <div className="team-role">Front-end</div>
              <p className="team-desc">
                Hỗ trợ xây dựng giao diện Next.js, tối ưu hóa các trang chức năng và đảm bảo giao diện responsive mượt mà.
              </p>
            </div>
          </div>
        </section>

        {/* Tech Stack Section */}
        <section className="about-section">
          <h2 className="about-serif-title" style={{ textAlign: "center", marginBottom: "45px" }}>Công nghệ sử dụng trong đề tài</h2>
          <div className="tech-stack-grid">
            {/* Frontend */}
            <div className="tech-stack-card">
              <h4>Công nghệ Frontend</h4>
              <ul>
                <li><strong>Framework:</strong> Next.js (React)</li>
                <li><strong>Styling:</strong> CSS Variables, Flexbox/Grid</li>
                <li><strong>Quản lý trạng thái:</strong> SWR Client State</li>
                <li><strong>Tương tác Client:</strong> JavaScript ES6+</li>
              </ul>
            </div>

            {/* Backend */}
            <div className="tech-stack-card">
              <h4>Công nghệ Backend</h4>
              <ul>
                <li><strong>Ngôn ngữ chính:</strong> PHP (phiên bản &gt;= 8.1)</li>
                <li><strong>Framework:</strong> Laravel RESTful API</li>
                <li><strong>Xác thực bảo mật:</strong> Laravel Sanctum</li>
                <li><strong>Dịch vụ email:</strong> Gmail SMTP</li>
              </ul>
            </div>

            {/* Database & Server */}
            <div className="tech-stack-card">
              <h4>Cơ sở dữ liệu & Máy chủ</h4>
              <ul>
                <li><strong>Database chính:</strong> MySQL</li>
                <li><strong>Cơ chế Caching:</strong> Laravel Cache system</li>
                <li><strong>Lưu trữ hình ảnh:</strong> Laravel Storage</li>
                <li><strong>Môi trường Local:</strong> Laragon / Apache</li>
              </ul>
            </div>

            {/* Tools & Integrations */}
            <div className="tech-stack-card">
              <h4>Tích hợp & Công cụ</h4>
              <ul>
                <li><strong>Vận chuyển:</strong> API Giao Hàng Nhanh (GHN)</li>
                <li><strong>Báo cáo thống kê:</strong> Xuất báo cáo Excel</li>
                <li><strong>Quản lý nguồn:</strong> Git & GitHub</li>
                <li><strong>Trình quản lý gói:</strong> Composer & NPM</li>
              </ul>
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
            Đề tài được phát triển nghiêm túc nhằm phục vụ việc bảo vệ Đồ án Tốt nghiệp. Hệ thống cam kết đáp ứng các tiêu chuẩn kỹ thuật về hiệu năng, bảo mật và tính khả dụng của một nền tảng thương mại điện tử thực tế.
          </p>
        </section>

        {/* CTA section */}
        <section className="cta-box-minimal" style={{ marginBottom: "40px" }}>
          <h2 className="cta-title-minimal">Trải nghiệm hệ thống thử nghiệm</h2>
          <p className="cta-desc-minimal">
            Khám phá đầy đủ các tính năng đặt hàng, áp dụng voucher, chọn địa lý vận chuyển GHN và công cụ hỗ trợ thông minh khác.
          </p>
          <Link href="/shop" className="cta-btn-minimal">
            Ghé Cửa Hàng Ngay
          </Link>
        </section>
      </div>
    </main>
  );
}
