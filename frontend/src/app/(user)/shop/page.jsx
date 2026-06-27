export const metadata = {
  title: "Cửa Hàng - PetWorld",
};

export default function ShopPage() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <h1 className="section-title">Cửa Hàng</h1>
        <p style={{ marginTop: 16, color: "#666" }}>
          Trang danh sách sản phẩm đang được phát triển (sẽ dùng API
          <code> GET /api/products</code>).
        </p>
      </div>
    </main>
  );
}
