import ForgotPasswordForm from "@/components/auth/ForgotPasswordForm";

export const metadata = {
  title: "Quên mật khẩu - PetWorld",
};

export default function ForgotPasswordPage() {
  return (
    <main className="main-content">
      <div className="homepage-container auth-page">
        <ForgotPasswordForm />
      </div>
    </main>
  );
}
