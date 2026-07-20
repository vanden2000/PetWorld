# Kiểm thử chatbot PetWorld

## Cách chạy tự động

Từ thư mục `backend`, chạy:

```powershell
php artisan test --filter=ChatApiTest
```

Các test này giả lập nhà cung cấp AI; không dùng API key thật. Chúng kiểm tra tạo phiên khách, lưu tin nhắn, bắt buộc mã phiên, khôi phục lịch sử đúng chủ sở hữu, ép tra catalog cho truy vấn sản phẩm và phản hồi dự phòng nếu lần gọi AI thứ hai thất bại.

## UAT trước khi phát hành

| Tình huống | Thao tác | Kỳ vọng |
| --- | --- | --- |
| Tìm sản phẩm | Hỏi: `Tìm thức ăn cho mèo dưới 200.000đ còn hàng` | Có tối đa 3 thẻ sản phẩm đúng catalog, giá/tồn kho không do AI tự bịa. |
| So sánh | Chọn `So sánh` ở hai thẻ sản phẩm | Có câu trả lời dựa trên dữ liệu hai sản phẩm đã chọn. |
| Chính sách | Hỏi về giao hàng, đổi trả, thanh toán hoặc voucher | Chỉ nêu nội dung từ bài kiến thức đã xuất bản; hiển thị nguồn. |
| Đơn hàng | Khi chưa đăng nhập hỏi `Theo dõi đơn của tôi` | Yêu cầu đăng nhập, không hiển thị hay suy đoán đơn hàng. |
| Tiếp tục phiên | Nhắn một câu, đóng rồi mở lại hộp chat | Các tin nhắn trước được nạp lại. |
| Bắt đầu lại | Nhấn `Mới` | Màn hình trở về lời chào; tin nhắn tiếp theo tạo phiên mới. |
| Mạng lỗi | Ngắt backend hoặc dùng API key sai rồi gửi | Có thông báo dễ hiểu và nút `Thử gửi lại`; không mất nội dung cần gửi lại. |
| Mobile & bàn phím | Mở trên màn hình 320px, dùng Tab/Escape | Hộp chat không tràn màn hình; focus rõ ràng; Escape đóng hộp. |

## Điều kiện cấu hình

Đặt `CHATBOT_PROVIDER`, `CHATBOT_MODEL`, `CHATBOT_BASE_URL`, `CHATBOT_TIMEOUT` và API key tương ứng trong `backend/.env`. API key chỉ nằm ở backend. Trước UAT chính sách, quản trị viên cần xuất bản ít nhất một bài kiến thức cho từng nhóm: giao hàng, thanh toán, đổi trả, voucher và liên hệ.
