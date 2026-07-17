# Dữ liệu chatbot — Bước 3

**Trạng thái:** Chờ kiểm thử migration.

## Bảng được thêm

| Bảng | Mục đích |
|---|---|
| `chat_conversations` | Một cuộc hội thoại của khách đăng nhập hoặc khách vãng lai. |
| `chat_messages` | Các tin nhắn thuộc một cuộc hội thoại. |
| `knowledge_articles` | FAQ và chính sách đã được kiểm duyệt để chatbot tham chiếu. |

## Nguyên tắc dữ liệu

- `chat_conversations.id` là UUID; `user_id` rỗng với khách chưa đăng nhập.
- `session_id` chỉ dùng để nối phiên của khách vãng lai, không phải dữ liệu định danh nhạy cảm.
- Xóa một hội thoại sẽ xóa các tin nhắn của hội thoại đó.
- `metadata` trong tin nhắn dùng cho dữ liệu có cấu trúc như ID sản phẩm gợi ý hoặc nguồn FAQ; nội dung chat luôn nằm ở `content`.
- Chỉ bài viết `knowledge_articles.status = published` được phép làm nguồn trả lời cho chatbot.

## Kiểm thử Bước 3

Chạy trong thư mục `backend`:

```powershell
php artisan migrate
php artisan migrate:status
```

Kết quả mong đợi: ba migration `2026_07_16_000000`, `000100`, `000200` có trạng thái `Ran`.

## Ngoài phạm vi

Bước này không tạo model, controller, API, seeder, giao diện hoặc bất kỳ yêu cầu Gemini nào.
