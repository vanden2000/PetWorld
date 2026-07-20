# API chatbot mẫu — Bước 4

**Trạng thái:** Đã triển khai backend; chờ kiểm thử kết nối Gemini từ môi trường chạy ứng dụng.

## Endpoint

```http
POST /api/chat
Content-Type: application/json
```

Endpoint cho phép khách chưa đăng nhập sử dụng chatbot. Middleware giới hạn tối đa 20 yêu cầu mỗi phút cho mỗi địa chỉ IP.

## Request cho khách vãng lai

`visitor_id` là UUID do frontend sẽ tạo và lưu ở trình duyệt trong bước giao diện sau này. Khi test thủ công, có thể dùng một UUID bất kỳ.

```json
{
  "visitor_id": "d9553d05-bd29-4f6f-9dcc-bb1fd7fb9307",
  "message": "Xin chào PetWorld"
}
```

Lượt tiếp theo trong cùng hội thoại gửi thêm `conversation_id` từ phản hồi trước:

```json
{
  "visitor_id": "d9553d05-bd29-4f6f-9dcc-bb1fd7fb9307",
  "conversation_id": "uuid-do-api-tra-ve",
  "message": "Bạn có thể giúp gì cho tôi?"
}
```

## Response thành công

```json
{
  "data": {
    "conversation_id": "uuid",
    "message": "Xin chào! Tôi có thể hỗ trợ bạn..."
  }
}
```

## Quy tắc và bảo mật

- Gemini API key chỉ được đọc ở Laravel qua `config('services.gemini')`.
- Request gửi `store: false` tới Gemini; PetWorld tự lưu lịch sử chat trong database.
- Khách vãng lai chỉ được tiếp tục hội thoại có cùng `visitor_id`; người đăng nhập chỉ truy cập hội thoại gắn với chính tài khoản của họ.
- Bản mẫu chưa truy cập giá, tồn kho, đơn hàng, voucher hay chính sách PetWorld; system prompt buộc chatbot nói rõ giới hạn này.
- Khi Gemini lỗi hoặc timeout, API trả về `502` với thông báo chung và không lộ chi tiết cấu hình.

## Kiểm tra bằng Postman

1. Chạy Laravel bằng Laragon hoặc `php artisan serve` trong `backend`.
2. Gửi POST tới `<BACKEND_URL>/api/chat` với request mẫu phía trên.
3. Kỳ vọng nhận `200` cùng `conversation_id` và `message`.
4. Kiểm tra database có một dòng `chat_conversations` và hai dòng `chat_messages` (một `user`, một `assistant`).

Nếu nhận `502` sau khoảng 30 giây, kiểm tra kết nối outbound từ máy chủ tới `https://generativelanguage.googleapis.com`; đó không phải lỗi API key nếu log ghi `cURL error 28`.
