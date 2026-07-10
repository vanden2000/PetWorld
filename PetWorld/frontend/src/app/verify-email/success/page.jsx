import Link from "next/link";

export default function VerifyEmailSuccessPage() {
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
                {/* Success Checkmark Badge */}
                <div
                    style={{
                        width: "72px",
                        height: "72px",
                        borderRadius: "18px",
                        backgroundColor: "var(--primary-orange)",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        marginBottom: "4px",
                    }}
                >
                    <svg
                        width="32"
                        height="32"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#ffff"
                        strokeWidth="5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    >
                        <polyline points="20 6 9 17 4 12" />
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
                    Xác minh thành công
                </h1>

                {/* Description Body */}
                <div
                    style={{
                        color: "#7a6e67",
                        fontSize: "14.5px",
                        lineHeight: "1.6",
                        textAlign: "center",
                    }}
                >
                    <p style={{ margin: 0 }}>
                        Tài khoản của bạn đã được xác minh email thành công. Bây giờ bạn có thể đăng nhập để tiếp tục trải nghiệm mua sắm cùng PetWorld.
                    </p>
                </div>

                {/* Login Button */}
                <Link
                    href="/login"
                    className="auth-submit-btn"
                    style={{
                        width: "100%",
                        display: "flex",
                        justifyContent: "center",
                        alignItems: "center",
                        textDecoration: "none",
                        padding: "14px 16px",
                        fontSize: "15.5px",
                        fontWeight: "800",
                        marginTop: "10px",
                        textAlign: "center",
                    }}
                >
                    ĐĂNG NHẬP NGAY
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
