import OrdersView from "@/components/auth/OrdersView";

export const metadata = { title: "Đơn hàng của tôi - PetWorld" };

export default function OrdersPage() {
  return <main className="main-content profile-page"><OrdersView /></main>;
}
