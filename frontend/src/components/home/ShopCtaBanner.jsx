"use client";

import { useState } from "react";
import Link from "next/link";
import { formatPrice, resolveBackendImage } from "@/lib/format";

/**
 * Một dải label trôi ngang. Danh sách được lặp lại hai lần rồi dịch -50%
 * để vòng lặp nối liền, không thấy điểm nhảy. Cả hai dải chạy cùng chiều
 * (phải sang trái) và chỉ khác tốc độ nên mắt không phải đảo hướng.
 */
function MarqueeRow({ items, speed, children }) {
  return (
    <div className={`promo-marquee promo-marquee-${speed}`}>
      <div className="promo-marquee-track">
        {[0, 1].map((pass) => (
          <div className="promo-marquee-group" key={pass} aria-hidden={pass === 1}>
            {items.map((item) => (
              <span className="promo-marquee-cell" key={`${item.key}-${pass}`}>
                {children(item)}
              </span>
            ))}
          </div>
        ))}
      </div>
    </div>
  );
}

/**
 * Banner CTA ngang "Mua ngay, kẻo lỡ": logo + tiêu đề + nút, bên dưới là hai dải
 * chạy ngang gồm mã giảm giá đang chạy và nhận xét thật của khách đã mua hàng.
 */
export default function ShopCtaBanner({ vouchers = [], reviews = [] }) {
  const [copiedCode, setCopiedCode] = useState(null);

  // Dải trên: mã giảm giá thật đang chạy. Dải dưới: nhận xét thật của khách đã mua.
  const voucherItems = vouchers.map((voucher) => ({
    key: `voucher-${voucher.id}`,
    code: voucher.code,
    title: `Giảm ${formatPrice(voucher.discount_value)}`,
    note: voucher.min_order_value > 0
      ? `Đơn từ ${formatPrice(voucher.min_order_value)}`
      : "Áp dụng mọi đơn hàng",
  }));

  const reviewItems = reviews
    .filter((review) => review.comment)
    .map((review) => ({
      key: `review-${review.id}`,
      rating: review.rating,
      name: review.user_name,
      comment: review.comment,
    }));

  const copyVoucher = async (code) => {
    try {
      await navigator.clipboard.writeText(code);
      setCopiedCode(code);
      window.setTimeout(() => setCopiedCode(null), 2000);
    } catch {
      // Trình duyệt chặn clipboard (http hoặc chưa cấp quyền): bỏ qua, khách vẫn đọc được mã.
    }
  };

  return (
    <section className="shop-cta-banner">
      <img
        src={resolveBackendImage("storage/logo/Special_Offer_1-removebg-preview.png")}
        alt="PetWorld"
        className="shop-cta-logo"
      />
      <div className="shop-cta-text">
        <span className="shop-cta-tag">Ưu đãi hôm nay</span>
        <h3 className="shop-cta-title">
          Mua ngay, <span>kẻo lỡ</span>
        </h3>
      </div>

      {(voucherItems.length > 0 || reviewItems.length > 0) && (
        <div className="promo-marquees shop-cta-marquees">
          {voucherItems.length > 0 && (
            <MarqueeRow speed="slow" items={voucherItems}>
              {(item) => (
                <button
                  type="button"
                  className="promo-chip promo-chip-voucher"
                  onClick={() => copyVoucher(item.code)}
                  title={`Sao chép mã ${item.code}`}
                >
                  <span className="promo-chip-icon">🎟️</span>
                  <span className="promo-chip-body">
                    <strong className="promo-chip-code">
                      {copiedCode === item.code ? "Đã sao chép!" : item.code}
                    </strong>
                    <span className="promo-chip-note">
                      {item.title} · {item.note}
                    </span>
                  </span>
                </button>
              )}
            </MarqueeRow>
          )}

          {reviewItems.length > 0 && (
            <MarqueeRow speed="fast" items={reviewItems}>
              {(item) => (
                <span className="promo-chip promo-chip-review">
                  <span className="promo-chip-stars" aria-label={`${item.rating} trên 5 sao`}>
                    {"★".repeat(item.rating)}
                    {"☆".repeat(5 - item.rating)}
                  </span>
                  <span className="promo-chip-body">
                    <span className="promo-chip-quote">“{item.comment}”</span>
                    <span className="promo-chip-note">{item.name}</span>
                  </span>
                </span>
              )}
            </MarqueeRow>
          )}
        </div>
      )}

      <Link href="/shop" className="shop-cta-btn">
        GHÉ SHOP NGAY
      </Link>
    </section>
  );
}
