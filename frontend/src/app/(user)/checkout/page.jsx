import Link from "next/link";
import CheckoutView from "@/components/checkout/CheckoutView";
import { resolveBackendImage } from "@/lib/format";

export const metadata = {
  title: "Thanh toán - PetWorld",
};

export default function CheckoutPage() {
  return (
    <main className="checkout-page">
      <div className="homepage-container">
        <CheckoutView />
      </div>
    </main>
  );
}
