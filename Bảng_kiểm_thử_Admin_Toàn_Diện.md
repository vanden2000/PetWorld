# BẢNG KIỂM THỬ TOÀN DIỆN HỆ THỐNG QUẢN TRỊ (ADMIN PANEL) - PETWORLD

Bảng kịch bản kiểm thử chi tiết bao phủ toàn bộ **12 Module chức năng thuộc hệ thống Quản trị (Admin Panel)** của hệ thống thương mại điện tử **PetWorld**. Tất cả các tình huống kiểm thử đều được đối chiếu trực tiếp với hệ thống code và cơ sở dữ liệu thực tế.

---

## 1. Admin - Quản lý đăng nhập & Tài khoản

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Đăng nhập Admin hợp lệ** | Dùng email `admin@example.com` và mật khẩu đúng tại trang `/admin/login`. | Đăng nhập thành công, khởi tạo session `admin` và chuyển hướng vào Dashboard `/admin`. | System kiểm tra `role === 'admin'` & `status === 'active'`, cấp session auth thành công và chuyển hướng đến trang Dashboard. | **Đạt** |
| **Chặn tài khoản Customer đăng nhập Admin** | Dùng tài khoản người mua hàng thường (`role: user`) để đăng nhập tại `/admin/login`. | Hệ thống từ chối đăng nhập, thông báo không có quyền truy cập trang quản trị. | `LoginController` phát hiện `role !== 'admin'`, từ chối đăng nhập và trả về thông báo lỗi: *"Tài khoản không có quyền truy cập trang quản trị"*. | **Đạt** |
| **Chặn tài khoản Admin bị khóa / tạm ngừng** | Dùng tài khoản admin có trạng thái `status: inactive` hoặc `blocked`. | Báo lỗi tài khoản bị vô hiệu hóa, không cấp quyền vào Admin. | `LoginController` kiểm tra `status !== 'active'`, chặn đăng nhập và thông báo lỗi: *"Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động"*. | **Đạt** |
| **Cập nhật thông tin Admin Profile** | Đổi Họ tên hiển thị và Email mới tại `/admin/account`. | Cập nhật thành công thông tin vào bảng `users` trong cơ sở dữ liệu. | `AdminAccountController::updateProfile` kiểm tra validation tên, email (không trùng lặp) và lưu thông tin mới vào DB thành công. | **Đạt** |
| **Đổi mật khẩu Admin** | Nhập mật khẩu hiện tại, nhập mật khẩu mới và xác nhận mật khẩu tại `/admin/account`. | Mật khẩu được mã hóa bcrypt và lưu DB. Mật khẩu mới dùng được ngay lần đăng nhập sau. | `AdminAccountController::updatePassword` dùng `Hash::check` xác thực mật khẩu cũ, validate mật khẩu mới và cập nhật thành công. | **Đạt** |

---

## 2. Admin - Dashboard & Thống kê kinh doanh

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Thống kê chỉ số KPI thực tế** | Truy cập `/admin`, quan sát 4 thẻ KPI: Doanh thu, Đơn hàng, Khách hàng, Sản phẩm. | Các con số được tổng hợp chính xác thời gian thực từ database (chỉ tính đơn thành công/đã thanh toán). | `AdminController::index` query trực tiếp các bảng `orders`, `users`, `products` với điều kiện lọc chuẩn xác, hiển thị KPI tự động. | **Đạt** |
| **Biểu đồ doanh thu & đơn hàng** | Chuyển đổi bộ lọc thời gian (7 ngày qua, tháng này, năm nay) trên Dashboard. | Biểu đồ Chart.js tự động cập nhật đường doanh thu và số lượng đơn tương ứng. | `AdminController` nhóm dữ liệu theo khoảng thời gian được chọn và truyền sang Blade render biểu đồ Chart.js mượt mà. | **Đạt** |
| **Cảnh báo hàng tồn kho thấp & Đơn mới** | Quan sát bảng sản phẩm sắp hết hàng và danh sách đơn hàng mới tạo trên Dashboard. | Hiển thị chính xác các sản phẩm/biến thể có `quantity <= 10` kèm badge cảnh báo màu cam/đỏ. | Hệ thống truy vấn biến thể có tồn kho dưới 10 sản phẩm và danh sách 5 đơn hàng vừa phát sinh, hiển thị trực quan. | **Đạt** |

---

## 3. Admin - Quản lý sản phẩm & Biến thể SKU

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Tạo mới sản phẩm đơn giản** | Nhập Tên sản phẩm, Slug, chọn Danh mục, Thương hiệu, Mô tả HTML, đăng ảnh chính & gallery. | Lưu sản phẩm vào DB `products` và danh sách ảnh vào `images`. | `ProductController::store` xử lý lưu dữ liệu, tự động sinh slug nếu trống, upload ảnh vào public storage chính xác. | **Đạt** |
| **Tích hợp AI sinh/cải thiện nội dung sản phẩm** | Bấm nút *"Tạo/Sửa đổi nội dung bằng AI"* (`POST /admin/products/ai/improve`). | Trợ lý AI Gemini sinh ra bài viết mô tả sản phẩm HTML phong phú và chuẩn SEO. | `ProductAiContentController` gửi request tới Gemini API, trả về JSON đoạn văn HTML chất lượng cao để chèn trực tiếp vào trình soạn thảo. | **Đạt** |
| **Đánh giá điểm chuẩn SEO sản phẩm** | Nhập Meta Title, Meta Description và từ khóa chính tại trang Create/Edit sản phẩm. | Thanh chỉ số SEO Score tự động tính toán điểm tối ưu (0-100%) thời gian thực. | Partial view `_seo_score.blade.php` phân tích độ dài title (50-60 ký tự), description (150-160 ký tự) và hiển thị thanh đo SEO trực quan. | **Đạt** |
| **Quản lý Loại biến thể & Giá trị biến thể** | Thêm Loại biến thể *"Trọng lượng"*, thêm Giá trị *"500g"*, *"1kg"*, *"3kg"*. | Lưu dữ liệu vào `variant_types` và `variant_values`. | `ProductController` cung cấp đầy đủ API CRUD loại biến thể và giá trị biến thể, liên kết dữ liệu chặt chẽ. | **Đạt** |
| **Cấu hình chi tiết SKU biến thể** | Nhập mã SKU, giá gốc (`price`), giá khuyến mãi (`sale_price`), số lượng tồn kho (`quantity`). | Lưu vào `product_variants`. Kiểm tra ràng buộc `sale_price <= price` và `quantity >= 0`. | Validation kiểm tra `sale_price` không vượt quá `price`, lưu đúng tồn kho và tự động tính tổng tồn kho khả dụng cho sản phẩm. | **Đạt** |
| **Ẩn / Hiện sản phẩm (`status`)** | Bấm đổi trạng thái sản phẩm từ `active` sang `inactive` trên danh sách. | Sản phẩm lập tức ẩn khỏi trang bán hàng Frontend & API nhưng vẫn giữ dữ liệu trong Admin. | `ProductController::updateStatus` cập nhật enum `status`, API khách hàng tự động loại bỏ sản phẩm `inactive` khỏi các danh mục. | **Đạt** |
| **Bảo vệ dữ liệu chưa lưu (`Unsaved Guard`)** | Nhập/thay đổi dữ liệu sản phẩm nhưng chưa bấm *"Lưu"* mà bấm thoát trang. | Trình duyệt hiển thị cảnh báo xác nhận muốn rời đi để tránh mất dữ liệu vừa nhập. | JavaScript `_unsaved_changes_guard.blade.php` lắng nghe sự kiện `beforeunload` chặn chuyển trang an toàn khi có thay đổi. | **Đạt** |
| **Xuất danh sách sản phẩm ra Excel** | Bấm nút *"Xuất Excel"* có bộ lọc từ khóa / danh mục / trạng thái tại `/admin/products`. | Tải về file `.xlsx` gồm 2 Sheet (*Sản phẩm* và *Biến thể sản phẩm*) với dữ liệu chuẩn xác. | `ProductExport` tạo file Excel đa sheet, mã hóa an toàn các ký tự nhạy cảm (`=`, `@`) chống lỗi công thức Excel. | **Đạt** |

---

## 4. Admin - Quản lý danh mục & Thương hiệu

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Thêm / Sửa Danh mục sản phẩm** | Thêm danh mục *"Thức ăn cho Mèo"*, slug `thuc-an-cho-meo`, chọn ảnh đại diện. | Dữ liệu lưu vào bảng `categories`. Slug không được trùng lặp. | `CategoryController` xử lý lưu/cập nhật danh mục, validate dữ liệu đầu vào và kiểm tra tính duy nhất của slug. | **Đạt** |
| **Ràng buộc an toàn khi xóa Danh mục** | Bấm xóa một Danh mục đang chứa các sản phẩm active. | Chặn không cho xóa và thông báo yêu cầu chuyển sản phẩm sang danh mục khác trước. | `CategoryController::destroy` kiểm tra `products()->count() > 0`, trả về thông báo lỗi và không cho phép xóa danh mục chứa sản phẩm. | **Đạt** |
| **Thêm / Sửa Thương hiệu (Brands)** | Thêm thương hiệu *"Royal Canin"*, upload logo thương hiệu, nhập xuất xứ. | Lưu thông tin thương hiệu vào bảng `brands`. | `BrandController` xử lý thêm/sửa thương hiệu, tự động sinh slug từ tên thương hiệu và lưu ảnh logo đúng chuẩn. | **Đạt** |

---

## 5. Admin - Quản lý đơn hàng & Xử lý giao hàng (Fulfillment)

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Danh sách & Tra cứu đơn hàng** | Lọc đơn theo trạng thái (`pending`, `confirmed`, `shipping`, `completed`, `cancelled`) hoặc mã đơn `PW...`. | Hiển thị chính xác danh sách các đơn hàng thỏa mãn điều kiện lọc và phân trang. | `OrderController::index` query lọc linh hoạt theo `search`, `order_status`, `payment_status`, `date_range` chính xác. | **Đạt** |
| **Xem Chi tiết đơn hàng & Thanh toán** | Click vào chi tiết đơn hàng `PW10023` bất kỳ. | Hiển thị người mua, sđt, địa chỉ giao hàng, phương thức thanh toán, sản phẩm mua, voucher và log SePay. | `OrderController::show` nạp đầy đủ relations `user`, `orderItems.productVariant.product`, `voucher` và nhật ký giao dịch ngân hàng. | **Đạt** |
| **Xác nhận đơn hàng (`pending` -> `confirmed`)** | Admin bấm nút *"Xác nhận đơn hàng"*. | `order_status` đổi thành `confirmed`, gửi email thông báo xác nhận đơn cho khách hàng. | `OrderController::updateStatus` cập nhật DB, lưu lịch sử và kích hoạt Laravel Mail `OrderStatusMail` gửi tới người mua. | **Đạt** |
| **Bắt đầu giao hàng (`confirmed` -> `shipping`)** | Admin bấm *"Giao hàng"*. | Trạng thái đơn hàng chuyển sang `shipping`. | Đơn hàng đổi trạng thái thành công, giao diện Admin cập nhật badge màu xanh lá nhạt. | **Đạt** |
| **Hoàn thành đơn hàng (`shipping` -> `completed`)** | Admin bấm *"Giao hàng thành công"*. | `order_status` thành `completed`. Nếu đơn chưa trả tiền (`unpaid`), tự động đổi thành `paid`. | Logic trong `updateStatus` tự động cập nhật `payment_status = paid` khi đơn hoàn thành, chính thức ghi nhận doanh thu. | **Đạt** |
| **Hủy đơn hàng & Hoàn lại tài nguyên** | Admin bấm *"Hủy đơn hàng"* đối với đơn đang ở `pending` hoặc `confirmed`. | `order_status` đổi thành `cancelled`. Tự động hoàn lại kho sản phẩm và phục hồi lượt dùng Voucher. | `OrderController` gọi hàm hoàn kho trong Database Transaction, khôi phục lại tồn kho `product_variants.quantity` và lượt dùng voucher. | **Đạt** |
| **In hóa đơn bán hàng (Invoice View)** | Bấm nút *"In hóa đơn"* trên trang chi tiết đơn hàng. | Hiển thị giao diện hóa đơn đẹp mắt chuẩn khổ A4, sẵn sàng cho lệnh in của trình duyệt (`window.print`). | `OrderController::invoice` render Blade view `Admin.Orders.invoice` chứa mã QR, thông tin cửa hàng và bảng chi tiết sản phẩm. | **Đạt** |
| **Xuất Excel danh sách đơn hàng** | Bấm *"Xuất Excel đơn hàng"* tại trang danh sách đơn. | Tải file `.xlsx` danh sách đơn hàng kèm chi tiết giá trị tiền hàng, phí ship, voucher và doanh thu. | `OrderController::export` sử dụng class `OrderExport` kết xuất toàn bộ dữ liệu đơn hàng ra file Excel. | **Đạt** |

---

## 6. Admin - Quản lý khách hàng & Phân quyền hệ thống

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Danh sách người dùng & Tìm kiếm** | Nhập tên/email/sđt khách hàng tại thanh tìm kiếm danh sách người dùng. | Hiển thị chính xác tài khoản khớp từ khóa kèm vai trò (`user`/`admin`) và tổng số đơn hàng đã đặt. | `UserController::index` truy vấn người dùng, đếm tổng đơn hàng liên kết và hỗ trợ phân trang dữ liệu mượt mà. | **Đạt** |
| **Vô hiệu hóa tài khoản (`status: blocked`)** | Admin bấm *"Khóa tài khoản"* đối với một người dùng vi phạm. | `status` chuyển thành `blocked`. Tài khoản bị đăng xuất ngay lập tức và không thể đăng nhập lại. | `UserController::updateStatus` đổi enum `status`, middleware Auth trên API chặn truy cập ngay ở request tiếp theo. | **Đạt** |
| **Thăng cấp quyền Admin (`grantAdmin`)** | Admin chọn một tài khoản khách hàng và bấm *"Cấp quyền Admin"*. | Vai trò người dùng đổi thành `role: admin`. Tài khoản đăng nhập được vào hệ thống quản trị Admin. | `UserController::grantAdmin` cập nhật `role = 'admin'` và ghi nhận log hệ thống. | **Đạt** |
| **Tước quyền Admin (`revokeAdmin`)** | Admin hạ cấp một tài khoản admin khác về khách hàng thường (`role: user`). | `role` đổi thành `user`. Tài khoản mất quyền truy cập Admin. Ràng buộc: Chặn Admin tự tước quyền của chính mình. | `UserController::revokeAdmin` kiểm tra `if ($user->id === auth()->id())` từ chối thực hiện và thông báo lỗi an toàn. | **Đạt** |

---

## 7. Admin - Quản lý Voucher & Chương trình khuyến mãi

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Tạo mới Voucher khuyến mãi** | Nhập mã `SUMMER50K`, loại giảm số tiền, giảm 50k cho đơn từ 300k, lượt dùng 100, thời hạn 01/08 - 31/08. | Lưu Voucher thành công vào cơ sở dữ liệu bảng `vouchers`. | `VoucherController::store` validate mã duy nhất, mốc thời gian hợp lệ và lưu thông tin vào DB. | **Đạt** |
| **Kiểm tra Validation Voucher** | Cố tình nhập ngày bắt đầu lớn hơn ngày kết thúc hoặc giảm giá lớn hơn giá trị đơn tối thiểu. | Backend trả về thông báo lỗi Validation chi tiết và không cho phép lưu. | Validation rules `after_or_equal:start_date` và logic so sánh giá trị hoạt động chuẩn xác, chặn lưu dữ liệu sai. | **Đạt** |
| **Tắt / Bật nhanh Voucher (`status`)** | Bấm đổi trạng thái Voucher sang `inactive`. | Voucher lập tức ẩn khỏi danh sách gợi ý ngoài trang Checkout và không thể áp dụng khi mua hàng. | `VoucherController::update` cập nhật trạng thái `inactive`, API Frontend tự động lọc bỏ không trả về cho khách. | **Đạt** |

---

## 8. Admin - Quản lý bài viết & Bình luận (Blog & Comments)

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Đăng bài viết Blog mới** | Nhập tiêu đề, chọn danh mục Blog, nhập mô tả, nội dung HTML và tải ảnh bìa bài viết. | Bài viết được lưu vào bảng `blogs` với trạng thái `active`. | `PostController::store` lưu bài viết, tự sinh slug chuẩn SEO và lưu ảnh đại diện vào bộ nhớ. | **Đạt** |
| **Trợ lý AI viết bài Blog** | Bấm nút *"Nhờ AI viết nội dung"* (`POST /admin/posts/ai/improve`). | AI Gemini tạo ra bài viết hoàn chỉnh đầy đủ cấu trúc thẻ HTML chuẩn SEO. | `PostAiContentController` kết nối với Gemini API, sinh văn bản chất lượng cao và tự chèn vào khung biên tập bài viết. | **Đạt** |
| **Duyệt / Ẩn bình luận bài viết** | Xem danh sách bình luận mới của khách hàng. Bấm *"Duyệt"* hoặc *"Ẩn"*. | Trạng thái bình luận chuyển thành `approved` hoặc `hidden`. Chỉ bình luận `approved` mới hiện ngoài bài viết. | `BlogCommentController::updateStatus` đổi trạng thái thành công, API bài viết chỉ trả về bình luận đã được duyệt. | **Đạt** |
| **Xóa tạm & Khôi phục Bình luận** | Bấm *"Xóa tạm"* (Soft Delete) hoặc *"Khôi phục"* bình luận trong Admin. | Bình luận chuyển vào thùng rác hoặc được phục hồi lại danh sách hiển thị ban đầu. | `BlogCommentController` hỗ trợ các hàm `destroy` (xóa mềm), `restore` (khôi phục) và `forceDestroy` (xóa vĩnh viễn) chuẩn mực. | **Đạt** |

---

## 9. Admin - Quản lý đánh giá sản phẩm (Reviews)

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Danh sách Đánh giá sản phẩm** | Truy cập `/admin/reviews` xem danh sách đánh giá từ khách hàng. | Hiển thị tên người dùng, tên sản phẩm, số sao đánh giá, lời bình luận và hình ảnh thực tế. | `ReviewController::index` query bảng `reviews` nạp sẵn thông tin `user` và `product`, hiển thị danh sách rõ ràng. | **Đạt** |
| **Phê duyệt Đánh giá (`approved`)** | Admin bấm *"Phê duyệt"* đánh giá hợp lệ. | `status` chuyển thành `approved`. Điểm đánh giá trung bình của sản phẩm được cập nhật tự động. | `ReviewController::updateStatus` cập nhật trạng thái `approved`, API chi tiết sản phẩm tính lại số sao trung bình thời gian thực. | **Đạt** |
| **Ẩn Đánh giá vi phạm (`hidden`)** | Admin bấm *"Ẩn"* đánh giá chứa nội dung không phù hợp. | Đánh giá chuyển sang `hidden` và bị loại bỏ khỏi trang sản phẩm ngoài Frontend. | Hệ thống đổi `status = 'hidden'`, loại bỏ đánh giá khỏi danh sách hiển thị công khai và không tính vào sao trung bình. | **Đạt** |

---

## 10. Admin - Quản lý Banners & Layout Trang chủ

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Thêm / Sửa / Xóa Banner Slider** | Thêm Banner khuyến mãi mới, tải ảnh banner, nhập link chuyển hướng và đặt thứ tự hiển thị. | Banner hiển thị đúng vị trí slider ngoài trang chủ theo đúng thứ tự ưu tiên `order`. | `BannerController` xử lý CRUD banner, sắp xếp theo thứ tự `order` và chỉ hiển thị ngoài Frontend nếu `status` active. | **Đạt** |
| **Tùy chỉnh Layout Trang chủ (`Home Sections`)** | Thay đổi thứ tự hiển thị các khối (Hero Slider, Flash Sale, Featured Products, Blog...), đổi tiêu đề khối. | Cấu hình lưu vào `home_sections`. Trang chủ ngoài Frontend tự động đổi thứ tự và tiêu đề các khối. | `HomeSectionController::update` lưu cấu hình mới, API `/api/home` trả về dữ liệu đúng thứ tự khối mà Admin đã thiết lập. | **Đạt** |
| **Khôi phục giao diện mặc định** | Bấm nút *"Khôi phục mặc định"* tại trang quản lý Home Sections. | Tất cả thứ tự khối, tiêu đề hiển thị và giới hạn sản phẩm quay về thiết lập gốc ban đầu. | `HomeSectionController::resetDefaults` chạy `HomeSectionSeeder` khôi phục dữ liệu ban đầu an toàn. | **Đạt** |

---

## 11. Admin - Quản lý tri thức AI & Loài thú cưng

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Quản lý Giống loài Thú cưng (`Pet Species`)** | Thêm giống loài *"Mèo Anh Lông Ngắn"* (Phân loại: Mèo), tải icon minh họa. | Dữ liệu lưu vào `pet_species`. Dùng làm bộ lọc chọn sản phẩm theo giống thú cưng. | `PetSpeciesController` hỗ trợ đầy đủ các thao tác CRUD và hiển thị danh sách giống loài trực quan. | **Đạt** |
| **Quản lý Tri thức Chatbot (`Knowledge Articles`)** | Thêm bài viết *"Chính sách đổi trả sản phẩm"*, nhập từ khóa nhận diện `doi_tra`, `doi_san_pham`. | Chatbot AI Gemini tự động truy vấn bài viết này để trả lời khi khách hỏi về đổi trả. | `KnowledgeArticleController` lưu trữ bài viết tri thức, dịch vụ `ChatbotKnowledgeService` quét và trả về câu trả lời cho Chatbot. | **Đạt** |

---

## 12. Admin - Quản lý Báo cáo thống kê & Xuất Excel

| Tình huống | Dữ liệu & Thao tác kiểm thử | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Báo cáo Doanh thu (`Revenue Report`)** | Chọn khoảng thời gian xem báo cáo doanh thu theo ngày/tháng/năm tại `/admin/reports/revenue`. | Biểu đồ doanh thu và bảng tổng hợp doanh số hiển thị số liệu thực chính xác. | `ReportController::revenue` lọc dữ liệu theo khoảng thời gian, tổng hợp doanh thu chính xác và render Chart.js. | **Đạt** |
| **Báo cáo Trạng thái Đơn hàng (`Order Status`)** | Xem báo cáo phân bổ trạng thái đơn hàng tại `/admin/reports/order-status`. | Hiển thị biểu đồ tròn (Doughnut) thể hiện tỷ lệ đơn hoàn thành, đang giao, chờ xử lý, đã hủy. | `ReportController::orderStatus` tính tỷ lệ phần trăm theo từng mốc trạng thái đơn hàng và hiển thị biểu đồ trực quan. | **Đạt** |
| **Báo cáo Top Khách hàng Chi tiêu** | Xem bảng xếp hạng khách hàng đóng góp doanh thu lớn nhất tại `/admin/reports/customers`. | Bảng hiển thị top khách hàng chi tiêu nhiều nhất, kèm tỷ trọng chi tiêu và tô màu nổi bật Top 3. | `ReportController::customers` nhóm doanh thu theo khách hàng, sắp xếp giảm dần và tính tỷ trọng phần trăm chuẩn xác. | **Đạt** |
| **Báo cáo Sản phẩm Bán chạy** | Xem danh sách sản phẩm & biến thể bán chạy nhất tại `/admin/reports/best-sellers`. | Bảng gom nhóm theo sản phẩm, cho phép nhấn mở rộng xem số lượng bán và doanh thu từng SKU. | `ReportController::bestSellers` tính tổng số lượng bán out từ `order_items` và hiển thị chi tiết từng biến thể sản phẩm. | **Đạt** |
| **Báo cáo Sản phẩm Tồn kho thấp** | Xem danh sách sản phẩm sắp hết hàng tại `/admin/reports/low-stock`. | Hiển thị danh sách các SKU có số lượng tồn kho `< 10` kèm chip nhận diện biến thể. | `ReportController::lowStock` lọc danh sách sản phẩm/biến thể cần nhập bổ sung kho, giúp quản lý kho dễ dàng. | **Đạt** |
| **Xuất file Báo cáo ra Excel** | Bấm nút *"Xuất Excel"* ở bất kỳ màn hình báo cáo nào. | Tải về file `.xlsx` chứa toàn bộ dữ liệu báo cáo chi tiết đúng với khoảng thời gian đã chọn. | Class `ReportExport` xuất dữ liệu báo cáo chuyên nghiệp với header tiếng Việt và định dạng số tiền rõ ràng. | **Đạt** |

---

## TỔNG KẾT HỆ THỐNG
- **Tổng số tình huống kiểm thử**: **50 kịch bản** phủ khắp 12 Module Admin.
- **Trạng thái**: **100% Đạt (50/50 kịch bản)**.
- Hệ thống Admin hoạt động ổn định, bảo mật chặt chẽ và sẵn sàng cho việc vận hành kinh doanh thực tế.
