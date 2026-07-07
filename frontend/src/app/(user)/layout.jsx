import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import Toaster from "@/components/ui/Toaster";
import Chatbot from "@/components/chatbot/Chatbot";

export default function UserLayout({ children }) {
  return (
    <div className="user-layout">
      <Header />
      {children}
      <Footer />
      <Chatbot />
      <Toaster />
    </div>
  );
}
