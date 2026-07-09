import Link from "next/link";
import { resolveBackendImage } from "@/lib/format";

/**
 * Banner CTA ngang "Mua ngay, kẻo lỡ": logo + ảnh thú cưng + tiêu đề + nút.
 */
export default function ShopCtaBanner() {
  return (
    <section className="shop-cta-banner">
      <img
        src={resolveBackendImage("storage/logo/Special_Offer_1-removebg-preview.png")}
        alt="PetWorld"
        className="shop-cta-logo"
      />
      <div className="shop-cta-text">
        <span className="shop-cta-tag">Công thức độc quyền</span>
        <h3 className="shop-cta-title">
          Mua ngay, <span>kẻo lỡ</span>
        </h3>
      </div>
      <Link href="/shop" className="shop-cta-btn">
        GHÉ SHOP NGAY
      </Link>
    </section>
  );
}
