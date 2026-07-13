import { Suspense } from "react";
import AuthForm from "@/components/auth/AuthForm";

export const metadata = {
  title: "Đăng nhập - PetWorld",
};

export default function LoginPage() {
  return (
    <main className="main-content">
      <div className="homepage-container auth-page">
        <Suspense fallback={null}>
          <AuthForm mode="login" />
        </Suspense>
      </div>
    </main>
  );
}
