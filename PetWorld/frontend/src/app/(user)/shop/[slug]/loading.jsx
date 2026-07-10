export default function ProductDetailLoading() {
  return (
    <main className="main-content" aria-busy="true">
      <div className="homepage-container">
        <div className="route-loading" role="status" aria-live="polite">
          <span className="route-spinner" aria-hidden="true" />
          <p>Đang tải thông tin sản phẩm...</p>
        </div>
      </div>
    </main>
  );
}
