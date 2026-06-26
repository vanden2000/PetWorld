# Ảnh trang trí các khối promo

Lưu ảnh vào đúng thư mục này, đúng tên bên dưới:

| Vị trí trong trang chủ                         | Tên file cần lưu     | Đường dẫn dùng trong code         |
| ---------------------------------------------- | -------------------- | --------------------------------- |
| Sidebar tối "Sản Phẩm mới" (.category-sidebar) | `sidebar-pets.png`   | `/image/promo/sidebar-pets.png`   |
| Banner cam "Phụ Kiện Cho Pet" (.promo-card)    | `accessories.png`    | `/image/promo/accessories.png`    |
| Banner ngang "Mua ngay, kẻo lỡ" (.shop-cta-banner) | `cta-pets.png`   | `/image/promo/cta-pets.png`       |

Gợi ý: PNG nền trong suốt. Ảnh sidebar nên là chó/mèo thò đầu (đặt dưới đáy sidebar),
ảnh phụ kiện là collage phụ kiện thú cưng.

Muốn đổi tên/đường dẫn: sửa thẻ `<img>` trong
`src/components/home/NewProductsSplit.jsx` (sidebar) và
`src/components/home/AccessoriesPromo.jsx` (promo).
