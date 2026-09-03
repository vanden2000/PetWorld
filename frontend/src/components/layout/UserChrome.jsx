"use client";

import { Suspense } from "react";
import { usePathname } from "next/navigation";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import Toaster from "@/components/ui/Toaster";
import Chatbot from "@/components/chatbot/Chatbot";
import BackToTopButton from "@/components/layout/BackToTopButton";

export default function UserChrome({ children }) {
  const pathname = usePathname();
  const isCheckout = pathname === "/checkout";

  if (isCheckout) {
    return (
      <div className="user-layout checkout-layout">
        <Suspense fallback={null}>
          <Header />
        </Suspense>
        {children}
        <Toaster />
      </div>
    );
  }

  return (
    <div className="user-layout">
      <Suspense fallback={null}>
        <Header />
      </Suspense>
      {children}
      <Footer />
      <BackToTopButton />
      <Chatbot />
      <Toaster />
    </div>
  );
}
