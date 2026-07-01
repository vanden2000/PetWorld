import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import Toaster from "@/components/ui/Toaster";
import AutoRefresh from "@/components/system/AutoRefresh";

export default function UserLayout({ children }) {
  return (
    <div className="user-layout">
      <AutoRefresh />
      <Header />
      {children}
      <Footer />
      <Toaster />
    </div>
  );
}
