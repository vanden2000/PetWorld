import CheckoutView from "@/components/checkout/CheckoutView";

export const metadata = {
  title: "Thanh toán - PetWorld",
};

export default function CheckoutPage() {
  return (
    <main className="main-content">
      <div className="homepage-container">
        <CheckoutView />
      </div>
    </main>
  );
}
