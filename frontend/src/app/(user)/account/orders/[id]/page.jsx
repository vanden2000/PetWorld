import OrderTrackingView from "@/components/auth/OrderTrackingView";

export const metadata = { title: "Theo dõi đơn hàng - PetWorld" };

export default async function OrderTrackingPage({ params }) {
  const { id } = await params;
  return <main className="main-content profile-page"><OrderTrackingView orderId={id} /></main>;
}
