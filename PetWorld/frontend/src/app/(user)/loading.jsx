// Hiển thị trong khi Server Component của route đang tải dữ liệu (Suspense của Next.js).
export default function Loading() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <div className="route-loading" role="status" aria-live="polite">
          <span className="route-spinner" aria-hidden="true" />
          <p>Đang tải dữ liệu...</p>
        </div>
      </div>
    </main>
  );
}
