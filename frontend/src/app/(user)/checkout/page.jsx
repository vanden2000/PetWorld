import Link from "next/link";
import CheckoutView from "@/components/checkout/CheckoutView";
import { resolveBackendImage } from "@/lib/format";

export const metadata = {
  title: "Thanh toán - PetWorld",
};

export default function CheckoutPage() {
  return (
    <main className="checkout-page">
      <header className="checkout-header">
        <div className="checkout-header-inner">
          <Link href="/" className="checkout-logo-link" aria-label="Về trang chủ PetWorld">
            <img src={resolveBackendImage("logo/Special_Offer_1-removebg-preview.png")} alt="PetWorld" />
          </Link>
          <nav className="checkout-steps" aria-label="Tiến trình thanh toán">
            <span className="checkout-step is-done"><i>1</i>Giỏ hàng</span>
            <span className="checkout-step is-done"><i>2</i>Thông tin giao hàng</span>
            <span className="checkout-step is-active"><i>3</i>Thanh toán</span>
          </nav>
          <a href="tel:19001234" className="checkout-support">
            <span>Hỗ trợ 24/7</span>
            <strong>1900 1234</strong>
          </a>
        </div>
      </header>
      <div className="homepage-container">
        <CheckoutView />
      </div>
    </main>
  );
}
