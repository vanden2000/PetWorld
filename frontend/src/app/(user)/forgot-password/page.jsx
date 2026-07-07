import AuthForm from "@/components/auth/AuthForm";

export const metadata = {
  title: "Đăng nhập - PetWorld",
};

export default function LoginPage() {
  return (
    <main className="main-content">
      <div className="homepage-container auth-page">
        <AuthForm mode="login" />
      </div>
    </main>
  );
}
