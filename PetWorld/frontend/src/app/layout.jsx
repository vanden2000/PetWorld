import "./globals.css";
import { resolveBackendImage } from "@/lib/format";

// Use a local/system font stack so dev and build do not depend on Google Fonts.
const fontStack = '"Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

export const metadata = {
  title: "PetWorld - Siêu Thị Thú Cưng Hàng Đầu | Thức Ăn & Phụ Kiện Chính Hãng",
  description:
    "PetWorld - Hệ thống siêu thị thú cưng uy tín hàng đầu Việt Nam. Cung cấp sỉ lẻ thức ăn, pate dinh dưỡng, cát vệ sinh, vòng cổ và phụ kiện đồ chơi chó mèo chính hãng.",
  keywords:
    "petworld, siêu thị thú cưng, thức ăn chó mèo, phụ kiện thú cưng, cát vệ sinh, pate mèo",
};

export default function RootLayout({ children }) {
  return (
    <html lang="vi" style={{ "--font-inter": fontStack }}>
      <link rel="icon" type="image/x-icon" href={resolveBackendImage("logo/logo.png")} />
      <body>{children}</body>
    </html>
  );
}
