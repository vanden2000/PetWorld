# PetWorld - Tiến độ phát triển

> Cập nhật lần cuối: 24/06/2026

## Quy ước làm việc

- Thực hiện lần lượt từng bảng.
- Trước mỗi bảng phải trình bày cấu trúc cột, quan hệ và dữ liệu seed.
- Chỉ tạo hoặc sửa code sau khi được xác nhận.
- Chưa chạy migration, seeder, rollback hoặc xóa dữ liệu nếu chưa được cho phép.

## Đã hoàn thành

### Brands

- Model: `backend/app/Models/Brand.php`
- Migration: `backend/database/migrations/2026_06_24_111011_create_brands_table.php`
- Seeder: `backend/database/seeders/BrandSeeder.php`
- Dữ liệu: 9 thương hiệu, có tên ảnh mặc định.
- Migration đã tồn tại trong database trước phiên làm việc và đang ở trạng thái `Ran`.

### Categories

- Model: `backend/app/Models/Category.php`
- Đã sửa tên model từ `Categories.php` thành `Category.php` để đúng PSR-4.
- Migration: `backend/database/migrations/2026_06_24_113304_create_categories_table.php`
- Seeder: `backend/database/seeders/CategorySeeder.php`
- Dữ liệu: Thức ăn hạt, Pate, Snack, Phụ kiện, Đồ chơi, Vệ sinh và chăm sóc.
- Mỗi danh mục có tên ảnh `.jpg` mặc định.
- Migration: `Pending`.

### Products

- Model: `backend/app/Models/Product.php`
- Migration: `backend/database/migrations/2026_06_24_120000_create_products_table.php`
- Seeder: `backend/database/seeders/ProductSeeder.php`
- Dữ liệu riêng: `backend/database/seeders/data/products.php`
- Dữ liệu: 12 sản phẩm thuộc 6 danh mục.
- `description` dùng `LONGTEXT`.
- Mỗi sản phẩm có mô tả HTML dài dạng bài viết.
- Hỗ trợ soft delete và không dùng timestamps theo ERD.
- Migration: `Pending`.

### Variant types

- Model: `backend/app/Models/VariantType.php`
- Migration: `backend/database/migrations/2026_06_24_120100_create_variant_types_table.php`
- Seeder: `backend/database/seeders/VariantTypeSeeder.php`
- Dữ liệu: Trọng lượng, Kích thước, Màu sắc, Quy cách đóng gói.
- Migration: `Pending`.

### Product variants

- Model: `backend/app/Models/ProductVariant.php`
- Migration: `backend/database/migrations/2026_06_24_120200_create_product_variants_table.php`
- Seeder: `backend/database/seeders/ProductVariantSeeder.php`
- Dữ liệu: 24 biến thể, gồm giá gốc, giá sale và tồn kho.
- Hỗ trợ soft delete và không dùng timestamps theo ERD.
- Migration: `Pending`.

### Product images

- Model: `backend/app/Models/ProductImage.php`
- Model ánh xạ tới bảng `images` theo ERD.
- Migration: `backend/database/migrations/2026_06_24_120300_create_images_table.php`
- Seeder: `backend/database/seeders/ProductImageSeeder.php`
- Dữ liệu: 12 đường dẫn ảnh chính, mỗi sản phẩm một ảnh.
- Đã thêm quan hệ `images()` và `primaryImage()` vào model `Product`.
- Chưa tạo các file ảnh vật lý.
- Migration: `Pending`.

### Banners

- Model: `backend/app/Models/Banner.php`
- Migration: `backend/database/migrations/2026_06_24_120400_create_banners_table.php`
- Seeder: `backend/database/seeders/BannerSeeder.php`
- Dữ liệu: 3 banner cho homepage.
- Chưa tạo các file ảnh vật lý.
- Migration: `Pending`.

### Database seeder

`backend/database/seeders/DatabaseSeeder.php` đã đăng ký theo thứ tự:

1. `BrandSeeder`
2. `CategorySeeder`
3. `VariantTypeSeeder`
4. `ProductSeeder`
5. `ProductVariantSeeder`
6. `ProductImageSeeder`
7. `BannerSeeder`

## Kiểm tra đã thực hiện

- Các model, migration và seeder mới đều không có lỗi cú pháp PHP.
- Các model mới đều autoload thành công.
- Chưa chạy migration hoặc seeder mới.

## Bước tiếp theo đã đề xuất

Tạo API homepage:

- Endpoint dự kiến: `GET /api/home`
- Banners
- Categories
- Brands
- Featured products
- Sale products
- Sản phẩm kèm ảnh chính, danh mục, thương hiệu và khoảng giá

API homepage chưa được tạo và đang chờ duyệt cấu trúc JSON response.

## Các bảng ERD chưa thực hiện

- Users (đang dùng migration mặc định của Laravel, chưa điều chỉnh theo ERD)
- Vouchers
- Shipping methods
- Payment methods
- Addresses
- Orders
- Order details
- Wishlists
- Blog categories
- Blogs
- Comments
- Reviews

## Cap nhat 25/06/2026

### Homepage API

- Da tao endpoint: `GET /api/home`
- Route: `backend/routes/api.php`
- Controller: `backend/app/Http/Controllers/Api/HomeController.php`
- Response gom: `banners`, `categories`, `brands`, `featured_products`, `sale_products`.
- Product trong response gom anh chinh, danh muc, thuong hieu, khoang gia va tong ton kho.
- Da them quan he `variants()` vao model `Product`.
- Da them test: `backend/tests/Feature/HomeApiTest.php`
- Da cau hinh PHPUnit dung SQLite in-memory cho database tests.

### Kiem tra da thuc hien

- `php -l` cho cac file PHP moi/sua: dat.
- `php artisan route:list --path=api/home`: da hien thi route `GET|HEAD api/home`.
- `php artisan test --testsuite=Feature`: 1 passed, 1 skipped.
- `HomeApiTest` bi skip tren may hien tai vi thieu PHP extension `pdo_sqlite`.
- Chua chay migration hoac seeder tren database that.

## Cap nhat status enum 25/06/2026

- Da tao migration: `backend/database/migrations/2026_06_25_000000_update_users_and_status_enums.php`
- Da chay migration tren database that: batch 5.
- `users` da them:
  - `phone`
  - `avatar`
  - `role enum('user', 'admin') default 'user'`
  - `status enum('active', 'inactive', 'blocked') default 'active'`
- Da doi `status` cac bang da tao sang enum:
  - `products.status enum('active', 'inactive') default 'active'`
  - `product_variants.status enum('active', 'inactive') default 'active'`
  - `variant_types.status enum('active', 'inactive') default 'active'`
- Da cap nhat model, seeder, controller va test de dung `active` thay cho boolean `true`.
- Kiem tra:
  - `php artisan migrate:status`: migration moi da `Ran`.
  - Kiem tra schema bang `SHOW COLUMNS`: enum da dung.
- `php artisan test`: 2 passed, 1 skipped do thieu `pdo_sqlite`.

## Cap nhat homepage sections 25/06/2026

- Da bo sung vao `GET /api/home`:
  - `new_accessories`
  - `recent_viewed_accessories`
  - `products_by_categories`
- `new_accessories`: lay san pham active thuoc category slug `phu-kien`, moi nhat truoc, gioi han 8 san pham.
- `recent_viewed_accessories`: nhan query `recent_product_ids`, vi du `/api/home?recent_product_ids=1,2,3`; chi tra ve san pham active thuoc `phu-kien`.
- `products_by_categories`: tra ve tat ca danh muc, moi danh muc gom toi da 5 san pham active.
- Chua them `latest_blogs` vi chua co migration/model cho `blogs` va `blog_categories`.
- Da cap nhat `backend/tests/Feature/HomeApiTest.php` theo response moi.
- Kiem tra:
  - `php -l` cho `HomeController.php` va `HomeApiTest.php`: dat.
  - `php artisan test`: 2 passed, 1 skipped do thieu `pdo_sqlite`.
  - Goi thu `/api/home` trong app voi database that: status `200`, response co 8 section.

## Cap nhat blog homepage 25/06/2026

- Da tao bang `blog_categories`.
- Da tao bang `blogs`.
- Da tao model:
  - `backend/app/Models/BlogCategory.php`
  - `backend/app/Models/Blog.php`
- Da tao seeder:
  - `backend/database/seeders/BlogCategorySeeder.php`
  - `backend/database/seeders/BlogSeeder.php`
- Da dang ky seeder blog vao `DatabaseSeeder`.
- Du lieu seed gom 3 danh muc blog va 5 bai viet mau co title, slug, description, content HTML va image path.
- Da bo sung `latest_blogs` vao `GET /api/home`.
- `latest_blogs`: lay 3 bai moi nhat, `status = active`, kem category va author.
- Da them comment ngan trong `HomeController` de biet section nao goi ham nao.
- Da chay migration blog tren database that.
- Da chay rieng `BlogCategorySeeder` va `BlogSeeder` tren database that.
- Kiem tra:
  - `php -l` cac file moi/sua: dat.
  - `php artisan test`: 2 passed, 1 skipped do thieu `pdo_sqlite`.
  - Goi thu `/api/home`: status `200`, `latest_blogs` tra ve 3 bai.
- Da bo sung field `content` vao item cua `latest_blogs`.

## Cap nhat wishlists 25/06/2026

- Cart duoc chot huong lam bang session/cookie, chua tao bang cart.
- Chua tao `reviews` vi reviews phu thuoc `order_items`, ma `order_items` phu thuoc nhom bang order.
- Da tao bang `wishlists`:
  - `id`
  - `product_id`
  - `user_id`
  - unique `user_id + product_id`
- Da tao model: `backend/app/Models/Wishlist.php`
- Da them quan he:
  - `User::wishlists()`
  - `Product::wishlists()`
- Da tao seeder: `backend/database/seeders/WishlistSeeder.php`
- Da dang ky `WishlistSeeder` vao `DatabaseSeeder`.
- Da chay migration wishlist tren database that: batch 7.
- Da chay `php artisan db:seed --class=WishlistSeeder`.
- Du lieu mau: 3 user customer, 9 wishlist gan voi san pham that.
- Kiem tra:
  - `php -l` cac file moi/sua: dat.
  - `php artisan migrate:status`: migration wishlist da `Ran`.
  - Kiem tra DB: bang `wishlists` co 9 dong mau.
  - `php artisan test`: 2 passed, 1 skipped do thieu `pdo_sqlite`.

## Cap nhat orders va reviews 25/06/2026

- Da doc lai ERD moi va tao tiep nhom bang de reviews co day du khoa ngoai.
- Da tao bang:
  - `vouchers`
  - `shipping_methods`
  - `payment_methods`
  - `addresses`
  - `orders`
  - `order_items`
  - `reviews`
- Da tao model:
  - `Voucher`
  - `ShippingMethod`
  - `PaymentMethod`
  - `Address`
  - `Order`
  - `OrderItem`
  - `Review`
- Da them quan he:
  - `User::addresses()`
  - `User::orders()`
  - `User::reviews()`
  - `ProductVariant::orderItems()`
- Enum da dung:
  - `vouchers.status`: `active`, `inactive`, `expired`
  - `shipping_methods.status`: `active`, `inactive`
  - `payment_methods.status`: `active`, `inactive`
  - `addresses.status`: `active`, `inactive`
  - `orders.order_status`: `pending`, `confirmed`, `shipping`, `completed`, `cancelled`
  - `orders.payment_status`: `unpaid`, `paid`, `failed`, `refunded`
  - `reviews.status`: `pending`, `approved`, `hidden`
- Da tao seeder:
  - `VoucherSeeder`
  - `ShippingMethodSeeder`
  - `PaymentMethodSeeder`
  - `AddressSeeder`
  - `OrderSeeder`
  - `ReviewSeeder`
- Da dang ky cac seeder moi vao `DatabaseSeeder`.
- Da chay migration tren database that: batch 8.
- Da seed du lieu mau:
  - 3 vouchers
  - 3 shipping methods
  - 3 payment methods
  - 3 addresses
  - 3 orders
  - 6 order_items
  - 4 reviews approved tu cac order completed
- Luu y: lan seed dau `OrderSeeder` chay song song voi `AddressSeeder` nen thieu address; da rerun tuan tu `AddressSeeder`, `OrderSeeder`, `ReviewSeeder` thanh cong.
- Kiem tra:
  - `php -l` migrations, models, seeders moi/sua: dat.
  - `php artisan migrate:status`: cac migration moi da `Ran`.
  - Kiem tra DB count: dung so luong mau ben tren.
  - `php artisan test`: 2 passed, 1 skipped do thieu `pdo_sqlite`.

## Cap nhat API san pham 25/06/2026

- Da tao endpoint: `GET /api/products`
- Route: `backend/routes/api.php`
- Controller: `backend/app/Http/Controllers/Api/ProductController.php`
- API ho tro query params:
  - `search`
  - `category`
  - `brand`
  - `min_price`
  - `max_price`
  - `sort`
  - `page`
  - `per_page`
  - `user_id`
- `sort` ho tro:
  - `newest`
  - `price_asc`
  - `price_desc`
  - `popular`
  - `sale`
  - `rating`
- Response gom:
  - `breadcrumb`
  - `title`
  - `total`
  - `filters`
  - `sort_options`
  - `products`
  - `pagination`
- Product card gom:
  - anh chinh
  - danh muc
  - thuong hieu
  - rating that tu `reviews` approved
  - price range theo gia thuc te `COALESCE(sale_price, price)`
  - tong ton kho
  - `wishlist_count`
  - `is_wishlisted` neu truyen `user_id`
- Da them test: `backend/tests/Feature/ProductApiTest.php`
- Kiem tra:
  - `php -l` cho `ProductController.php`, `api.php`, `ProductApiTest.php`: dat.
  - `php artisan route:list --path=api/products`: da hien route `GET|HEAD api/products`.
  - Goi thu `/api/products?per_page=4&sort=rating`: status `200`, co product va rating that.
  - Goi thu filter/search/price/wishlist: status `200`, tra ve dung product va `is_wishlisted = true`.
  - `php artisan test`: 2 passed, 2 skipped do thieu `pdo_sqlite`.

## Cap nhat API chi tiet san pham 26/06/2026

- Da tao endpoint: `GET /api/products/{slug}`
- Route: `backend/routes/api.php`
- Controller: `backend/app/Http/Controllers/Api/ProductController.php`
- Response gom:
  - `breadcrumb`
  - `product`
  - `reviews`
  - `related_products`
- `product` gom du lieu card nhu API danh sach, them:
  - `description`
  - `view_count`
  - `images`
  - `variants`
- `reviews`: lay toi da 10 review `approved`, kem user va bien the da mua.
- `related_products`: lay toi da 4 san pham active cung danh muc, sap xep theo view_count va id.
- Da them test: `backend/tests/Feature/ProductApiTest.php`
- Kiem tra:
  - `php -l` cho `ProductController.php`, `api.php`, `ProductApiTest.php`: dat.
  - `php artisan route:list --path=api/products`: da hien route `GET|HEAD api/products/{slug}`.
  - `php artisan test --testsuite=Feature`: 1 passed, 3 skipped do thieu `pdo_sqlite`.
  - Goi thu `/api/products/royal-canin-mini-adult` bang Laravel kernel voi database that: status `200`, tra ve dung slug.
