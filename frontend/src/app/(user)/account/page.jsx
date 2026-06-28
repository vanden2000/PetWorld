import AccountView from "@/components/auth/AccountView";

export const metadata = {
  title: "Tài khoản - PetWorld",
};

export default function AccountPage() {
  return (
    <main className="main-content">
      <div className="homepage-container auth-page">
        <h1 className="section-title" style={{ marginBottom: 28 }}>
          Tài khoản của tôi
        </h1>
        <AccountView />
      </div>
    </main>
  );
}
