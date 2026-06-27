import { Inter } from "next/font/google";
import "./globals.css";

// Font Inter có subset tiếng Việt để hiển thị đầy đủ dấu.
const inter = Inter({
  subsets: ["latin", "vietnamese"],
  variable: "--font-inter",
  display: "swap",
});

export const metadata = {
  title: "PetWorld - Siêu Thị Thú Cưng Hàng Đầu | Thức Ăn & Phụ Kiện Chính Hãng",
  description:
    "PetWorld - Hệ thống siêu thị thú cưng uy tín hàng đầu Việt Nam. Cung cấp sỉ lẻ thức ăn, pate dinh dưỡng, cát vệ sinh, vòng cổ và phụ kiện đồ chơi chó mèo chính hãng.",
  keywords:
    "petworld, siêu thị thú cưng, thức ăn chó mèo, phụ kiện thú cưng, cát vệ sinh, pate mèo",
};

export default function RootLayout({ children }) {
  return (
    <html lang="vi" className={inter.variable}>
      <body>{children}</body>
    </html>
  );
}
