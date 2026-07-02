// Tập trung toàn bộ đường dẫn (route) của site về một nơi, tránh hard-code chuỗi
// rải rác trong nhiều file. Dùng hàm cho route có tham số (slug).

export const ROUTES = {
  home: "/",
  shop: "/shop",
  shopDetail: (slug) => `/shop/${slug}`,
  services: "/services",
  news: "/news",
  newsDetail: (slug) => `/news/${slug}`,
  contact: "/contact",
  cart: "/cart",
  checkout: "/checkout",
  wishlist: "/account/wishlist",
  notifications: "/notifications",
  account: "/account",
  orders: "/account/orders",
  login: "/login",
  register: "/register",
  privacy: "/chinh-sach-bao-mat",
};

// Danh sách menu chính dùng chung cho Header (label + đường dẫn).
export const MAIN_NAV = [
  { href: ROUTES.home, label: "Trang Chủ" },
  { href: ROUTES.shop, label: "Cửa Hàng" },
  { href: ROUTES.services, label: "Dịch Vụ" },
  { href: ROUTES.news, label: "Tin Tức" },
  { href: ROUTES.contact, label: "Liên hệ" },
];
