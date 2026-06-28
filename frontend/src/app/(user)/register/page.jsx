import AuthForm from "@/components/auth/AuthForm";

export const metadata = {
  title: "Đăng ký - PetWorld",
};

export default function RegisterPage() {
  return (
    <main className="main-content">
      <div className="homepage-container auth-page">
        <AuthForm mode="register" />
      </div>
    </main>
  );
}
