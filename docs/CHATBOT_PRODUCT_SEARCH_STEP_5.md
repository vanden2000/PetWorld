# Tìm sản phẩm thật — Bước 5

**Trạng thái:** Đã triển khai backend; chờ kiểm thử kết nối Gemini từ môi trường chạy ứng dụng.

## Thành phần mới

- `app/Services/ChatbotProductService.php`: truy vấn catalog PetWorld an toàn.
- `ChatController`: khai báo Gemini function `search_products`, thực hiện yêu cầu tool và gửi dữ liệu kết quả trở lại Gemini.

## Quy tắc truy vấn catalog

- Chỉ lấy `products.status = active`.
- Chỉ lấy `product_variants.status = active`.
- Mặc định chỉ lấy biến thể `quantity > 0`.
- Giá dùng giá khuyến mãi hợp lệ (`sale_price`) nếu có; nếu không dùng `price`.
- Tối đa năm sản phẩm; chatbot mặc định yêu cầu ba sản phẩm.
- AI không nhận quyền truy vấn database, không được truyền SQL và không tự tạo giá/tồn kho.

## Luồng function calling

```text
Khách hỏi sản phẩm
    -> Gemini yêu cầu search_products(filters)
    -> Laravel tìm dữ liệu thật bằng ChatbotProductService
    -> Laravel gửi function_result cho Gemini
    -> Gemini tạo câu trả lời dựa trên danh sách nhận được
    -> API trả message + suggestions cho frontend
```

Gemini function calling yêu cầu ứng dụng thực thi hàm bên ngoài, sau đó gửi `function_result` trở lại model. Cách triển khai này theo luồng Interactions API của Google.

## Request thử nghiệm

```json
{
  "visitor_id": "d9553d05-bd29-4f6f-9dcc-bb1fd7fb9307",
  "message": "Tìm thức ăn cho mèo dưới 200 nghìn còn hàng"
}
```

Kết quả mong đợi khi kết nối Gemini hoạt động:

```json
{
  "data": {
    "conversation_id": "uuid",
    "message": "...",
    "suggestions": [
      {
        "id": 1,
        "name": "...",
        "url": "/shop/...",
        "price": { "min": 185000, "max": 185000 },
        "stock_quantity": 5
      }
    ]
  }
}
```

`suggestions` là dữ liệu có cấu trúc để Bước 6 hiển thị thẻ sản phẩm trong giao diện chat.

Nếu lượt Gemini thứ hai bị timeout sau khi Laravel đã lấy được kết quả catalog, API không trả lỗi `502`: nó trả câu phản hồi dự phòng cùng `suggestions`. Câu dự phòng chỉ dùng tên và dữ liệu sản phẩm PetWorld vừa truy vấn, không dùng thông tin do AI suy đoán.

## Ngoài phạm vi

- Chưa có lọc theo loài, tuổi, cân nặng hoặc vấn đề sức khỏe dưới dạng thuộc tính cấu trúc.
- Chưa có tra cứu đơn hàng, voucher hoặc chính sách.
- Chưa sửa giao diện chatbot.
