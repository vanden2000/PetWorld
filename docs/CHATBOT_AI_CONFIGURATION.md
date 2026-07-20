# Cấu hình AI chatbot — Bước 2

**Trạng thái:** Đã triển khai cấu hình, chưa kết nối API AI.

## Mục đích

Backend Laravel có một vị trí cấu hình thống nhất cho Gemini API từ Google AI Studio. Cấu hình này chưa gọi dịch vụ AI, chưa tạo endpoint chat và chưa làm lộ khóa bí mật ra frontend.

## Tệp đã thay đổi

- `backend/config/services.php`: thêm nhóm cấu hình `services.gemini`.
- `backend/.env.example`: thêm các biến môi trường AI mẫu, không chứa khóa thật.

## Biến môi trường

| Biến | Ý nghĩa | Giá trị mẫu |
|---|---|---|
| `GEMINI_API_KEY` | Khóa Gemini API từ Google AI Studio | để trống trong mã nguồn |
| `GEMINI_MODEL` | Mã model Gemini sẽ chọn trước khi kết nối API | để trống |
| `GEMINI_BASE_URL` | Địa chỉ Gemini API | `https://generativelanguage.googleapis.com/v1beta` |
| `GEMINI_TIMEOUT` | Thời gian chờ một yêu cầu, tính bằng giây | `30` |

## Thao tác cho môi trường cục bộ

Người quản trị tự thêm giá trị vào `backend/.env`; không commit tệp này:

```env
GEMINI_API_KEY=<khóa-bí-mật-từ-google-ai-studio>
GEMINI_MODEL=<mã-model-gemini-đã-chọn>
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_TIMEOUT=30
```

Sau khi cập nhật `.env`, chạy trong thư mục `backend`:

```powershell
php artisan config:clear
```

## Yêu cầu bảo mật

- Không khai báo các biến AI dưới tiền tố `NEXT_PUBLIC_`.
- Không ghi `GEMINI_API_KEY` vào repository, log ứng dụng hoặc phản hồi API.
- Chỉ backend Laravel được đọc `config('services.gemini')`.
- Bước kế tiếp sẽ kiểm tra biến cần thiết trước khi thực hiện bất kỳ yêu cầu AI nào.

## Chưa thực hiện

- Chưa cài package mới.
- Chưa gọi API AI.
- Chưa có route `/api/chat`.
- Chưa có migration, model, controller hoặc thay đổi giao diện chat.
