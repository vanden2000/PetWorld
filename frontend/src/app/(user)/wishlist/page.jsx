import WishlistView from "@/components/wishlist/WishlistView";

export const metadata = {
  title: "Sản phẩm yêu thích - PetWorld",
};

export default function WishlistPage() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <h1 className="section-title" style={{ marginBottom: 28 }}>
          Sản phẩm yêu thích
        </h1>
        <WishlistView />
      </div>
    </main>
  );
}
