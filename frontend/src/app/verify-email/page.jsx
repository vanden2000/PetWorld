import Link from "next/link";

export default async function VerifyEmailPage({ searchParams }) {
  const params = await searchParams;

  const email =
    typeof params?.email === "string"
      ? params.email
      : "";

  return (
    <div
      style={{
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        minHeight: "100vh",
        padding: "40px 20px",
        backgroundColor: "var(--bg-body)",
      }}
    >
      <div
        style={{
          width: "100%",
          maxWidth: "480px",
          backgroundColor: "#ffffff",
          borderRadius: "24px",
          padding: "45px 40px",
          boxShadow: "0 15px 40px rgba(0, 0, 0, 0.05)",
          border: "1px solid rgba(62, 39, 26, 0.05)",
          display: "flex",
          flexDirection: "column",
          gap: "24px",
          alignItems: "center",
        }}
      >
        {/* Paw Badge Icon */}
        <div
          style={{
            width: "72px",
            height: "72px",
            borderRadius: "18px",
            backgroundColor: "#e6f2ed",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            marginBottom: "4px",
          }}
        >
          <svg
            width="32"
            height="32"
            viewBox="0 0 512 512"
            fill="var(--primary-orange)"
          >
            <path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C34.9 480 14 459.1 14 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM310.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z" />
          </svg>
        </div>

        {/* Title */}
        <h1
          style={{
            fontSize: "24px",
            fontWeight: "800",
            color: "#3e271a",
            margin: 0,
            textAlign: "center",
            letterSpacing: "-0.5px",
          }}
        >
          Kiểm tra email của bạn
        </h1>

        {/* Description Body */}
        <div
          style={{
            display: "flex",
            flexDirection: "column",
            gap: "16px",
            color: "#7a6e67",
            fontSize: "14.5px",
            lineHeight: "1.6",
            textAlign: "center",
          }}
        >
          <p style={{ margin: 0 }}>
            PetWorld đã gửi liên kết xác minh đến email:{" "}
            {email && (
              <span style={{ fontWeight: "700", color: "#3e271a" }}>{email}</span>
            )}
          </p>
          <p style={{ margin: 0 }}>
            Vui lòng mở hộp thư và nhấn vào liên kết xác minh để kích hoạt tài khoản.
          </p>
          <p style={{ margin: 0 }}>
            Nếu không thấy email, hãy kiểm tra thư mục Spam hoặc Thư rác.
          </p>
        </div>

        {/* Back Link Button */}
        <Link
          href="/login"
          className="auth-social-btn"
          style={{
            width: "100%",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            textDecoration: "none",
            padding: "13px 16px",
            fontSize: "15px",
            fontWeight: "750",
          }}
        >
          Quay lại đăng nhập
        </Link>
      </div>

      {/* Support Footer */}
      <p
        style={{
          marginTop: "24px",
          fontSize: "13.5px",
          fontWeight: "600",
          color: "#7a6e67",
          textAlign: "center",
        }}
      >
        Cần hỗ trợ?{" "}
        <Link
          href="/contact"
          style={{
            color: "var(--primary-orange)",
            textDecoration: "none",
            fontWeight: "700",
          }}
        >
          Liên hệ với đội ngũ kỹ thuật
        </Link>
      </p>
    </div>
  );
}
