import CartView from "@/components/cart/CartView";

export const metadata = {
  title: "Giỏ hàng - PetWorld",
};

export default function CartPage() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <p className="cart-eyebrow">Kiểm tra lại sản phẩm trước khi thanh toán</p>
        <h1 className="cart-page-title">Giỏ hàng của bạn</h1>
        <CartView />
      </div>
    </main>
  );
}
