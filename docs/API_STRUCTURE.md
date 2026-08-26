# Cấu trúc API PetWorld

Tài liệu này tóm tắt các API đang được khai báo tại `backend/routes/api.php`.

## Quy ước chung

| Hạng mục | Giá trị |
|---|---|
| Base URL (local) | `http://127.0.0.1:8000/api` |
| Định dạng trao đổi | JSON, trừ các API tải tệp dùng `multipart/form-data` |
| Header khuyến nghị | `Accept: application/json` |
| API cần đăng nhập | Thêm `Authorization: Bearer {token}` (Laravel Sanctum) |
| Tham số đường dẫn | Đặt trong ngoặc nhọn, ví dụ `{slug}`, `{order}` |
| Giới hạn yêu cầu | Một số endpoint có throttle theo phút, được nêu ở bảng bên dưới |

> Đường dẫn trong các bảng là tương đối với Base URL. Ví dụ `GET /products` tương ứng `GET http://127.0.0.1:8000/api/products`.

## Tổng quan endpoint

### 1. Xác thực và tài khoản

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| POST | `/register` | Không | Đăng ký tài khoản mới |
| POST | `/login` | Không | Đăng nhập, nhận token Sanctum |
| POST | `/logout` | Có | Đăng xuất, thu hồi phiên/token hiện tại |
| GET | `/user` | Có | Lấy thông tin người dùng hiện tại |
| PUT | `/user` | Có | Cập nhật hồ sơ người dùng |
| POST | `/user/avatar` | Có | Tải lên ảnh đại diện (`multipart/form-data`) |
| PUT | `/user/password` | Có | Đổi mật khẩu |
| GET | `/email/verify/{id}/{hash}` | Link đã ký | Xác minh email; giới hạn 6 yêu cầu/phút |

### 2. Khôi phục mật khẩu

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| POST | `/forgot-password/send-otp` | Không | Gửi OTP khôi phục mật khẩu; giới hạn 5 yêu cầu/phút |
| POST | `/forgot-password/verify-otp` | Không | Xác minh OTP |
| POST | `/forgot-password/reset-password` | Không | Đặt lại mật khẩu sau khi xác minh OTP |

### 3. Sản phẩm, trang chủ và nội dung

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| GET | `/home` | Không | Lấy dữ liệu các khu vực hiển thị trang chủ |
| GET | `/products` | Không | Danh sách/tìm kiếm/lọc sản phẩm |
| GET | `/products/recent` | Không | Danh sách sản phẩm đã xem gần đây |
| GET | `/products/{slug}` | Không | Chi tiết sản phẩm theo slug |
| GET | `/pet-species` | Không | Danh sách loài thú cưng |
| GET | `/blogs` | Không | Danh sách bài viết |
| GET | `/blogs/sitemap` | Không | Dữ liệu sitemap bài viết |
| GET | `/blogs/{slug}` | Không | Chi tiết bài viết theo slug |
| POST | `/blogs/{slug}/comments` | Có | Gửi bình luận cho bài viết |
| POST | `/reviews` | Có | Gửi đánh giá sản phẩm |

### 4. Danh sách yêu thích và voucher

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| GET | `/wishlist` | Có | Lấy danh sách yêu thích của người dùng |
| POST | `/wishlist/{product}` | Có | Thêm sản phẩm vào danh sách yêu thích |
| DELETE | `/wishlist/{product}` | Có | Xóa sản phẩm khỏi danh sách yêu thích |
| GET | `/vouchers` | Có | Lấy voucher khả dụng cho người dùng |

### 5. Địa chỉ và giao hàng

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| GET | `/addresses` | Có | Danh sách địa chỉ giao hàng |
| POST | `/addresses` | Có | Tạo địa chỉ giao hàng |
| PUT/PATCH | `/addresses/{address}` | Có | Cập nhật địa chỉ giao hàng |
| DELETE | `/addresses/{address}` | Có | Xóa địa chỉ giao hàng |
| GET | `/shipping/ghn/provinces` | Có | Danh sách tỉnh/thành từ GHN |
| GET | `/shipping/ghn/districts` | Có | Danh sách quận/huyện từ GHN |
| GET | `/shipping/ghn/wards` | Có | Danh sách phường/xã từ GHN |
| POST | `/shipping/quote` | Có | Báo giá vận chuyển; giới hạn 20 yêu cầu/phút |
| POST | `/shipping/ghtk/quote` | Có | Báo giá vận chuyển GHTK; giới hạn 20 yêu cầu/phút |
| GET | `/checkout-options` | Không | Lấy các lựa chọn thanh toán và giao hàng khi checkout |

### 6. Đơn hàng và thanh toán

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| GET | `/orders` | Có | Danh sách đơn hàng của người dùng |
| POST | `/orders` | Có | Tạo đơn hàng |
| GET | `/orders/{order}` | Có | Chi tiết đơn hàng |
| PATCH | `/orders/{order}/cancel` | Có | Hủy đơn hàng |
| POST | `/orders/{order}/renew-payment` | Có | Tạo lại/yêu cầu thanh toán lại |
| GET | `/orders/{order}/payment-status` | Có | Kiểm tra trạng thái thanh toán |
| POST | `/orders/{order}/check-sepay-payment` | Có | Đối soát thanh toán SePay theo yêu cầu |
| POST | `/webhooks/sepay` | Không* | Webhook nhận giao dịch từ SePay |
| POST | `/webhooks/ghn` | Không* | Webhook nhận trạng thái giao hàng từ GHN |

\* Webhook không dùng token người dùng; nhà cung cấp cần được xác thực theo cơ chế riêng của webhook.

### 7. Chat, liên hệ và thông báo

| Method | Endpoint | Xác thực | Mô tả |
|---|---|---:|---|
| POST | `/contact` | Không | Gửi yêu cầu hỗ trợ qua email; giới hạn 5 yêu cầu/phút |
| POST | `/chat` | Không | Gửi tin nhắn chatbot; giới hạn 20 yêu cầu/phút |
| GET | `/chat/{conversationId}` | Không | Lấy lịch sử hội thoại; giới hạn 30 yêu cầu/phút |
| GET | `/notifications` | Có | Danh sách thông báo của người dùng |
| GET | `/notifications/unread-count` | Có | Số thông báo chưa đọc |
| POST | `/notifications/{id}/read` | Có | Đánh dấu một thông báo đã đọc |
| POST | `/notifications/read-all` | Có | Đánh dấu tất cả thông báo đã đọc |

## Phân quyền và luồng sử dụng

| Nhóm client | API chính |
|---|---|
| Khách vãng lai | Sản phẩm, bài viết, trang chủ, chatbot, liên hệ, đăng ký/đăng nhập, lựa chọn checkout |
| Người dùng đã đăng nhập | Hồ sơ, địa chỉ, wishlist, voucher, đơn hàng, đánh giá, bình luận, thông báo và báo giá vận chuyển |
| Đối tác hệ thống | Webhook SePay và GHN |

## Ghi chú bảo trì

- Khi thêm/sửa route, cập nhật tài liệu này cùng với `backend/routes/api.php`.
- Mỗi endpoint nên có tài liệu chi tiết riêng về request body, query parameter, response mẫu và mã lỗi nếu frontend hoặc đối tác cần tích hợp.
- Tài liệu chi tiết cũ tại `backend/docs/api.md` chỉ bao phủ một phần endpoint; bảng này là danh mục route tổng quan theo mã nguồn hiện tại.
