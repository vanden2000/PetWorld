http://127.0.0.1:8000/api

Auth
1/ đăng ký tài khoản 
Method: POST  
URL: `/register`  
Mục đích: Tạo tài khoản người dùng mới.
### Headers
Accept: application/json
Content-Type: application/json
### Body
```json
{
  "name": "Nguyen Van A",
  "email": "vana@gmail.com",
  "phone": "0909123456",
  "password": "123456",
  "password_confirmation": "123456"
}
### Response thành công
```json
{
  "message": "Đăng ký thành công",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "vana@gmail.com",
    "phone": "0909123456"
  },
  "token": "1|abcxyz..."
}
##2 Đăng nhập

**Method:** POST  
**URL:** `/login`  
**Mục đích:** Đăng nhập tài khoản và lấy token.
### Headers
```txt
Accept: application/json
Content-Type: application/json
```
### Body
```json
{
  "email": "vana@gmail.com",
  "password": "123456"
}
```
### Response thành công
```json
{
  "message": "Đăng nhập thành công",
  "token": "1|abcxyz...",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "vana@gmail.com"
  }
}
```
### Response lỗi
```json
{
  "message": "Sai email hoặc mật khẩu"
}
```
---

## 3 Lấy thông tin user đang đăng nhập

**Method:** GET  
**URL:** `/user`  
**Mục đích:** Lấy thông tin tài khoản đang đăng nhập.

### Headers

```txt
Accept: application/json
Authorization: Bearer {token}
```
### Body
Không có.
### Response thành công
```json
{
    "data": {
        "user": {
            "id": 6,
            "name": "Nguyen Van A",
            "email": "vana@gmail.com",
            "phone": "0909123456",
            "date_of_birth": null,
            "avatar": null,
            "role": "user"
        }
    }
}
```
---
## Cập nhật ảnh đại diện

**Method:** POST  
**URL:** `/user/avatar`  
**Mục đích:** Cho phép user đang đăng nhập cập nhật ảnh đại diện.

### Yêu cầu đăng nhập

API này cần token đăng nhập.

### Headers

```txt
Accept: application/json
Authorization: Bearer {token}
```

### Body

Dạng gửi dữ liệu: `form-data`

| Tên field | Kiểu dữ liệu | Bắt buộc | Mô tả |
|---|---|---|---|
| avatar | file | Có | Ảnh đại diện của user |

### Điều kiện upload ảnh
| Điều kiện | Mô tả |
|---|---|
| Bắt buộc | Phải chọn ảnh đại diện |
| Định dạng | Chỉ nhận `jpg`, `jpeg`, `png`, `webp` |
| Dung lượng tối đa | 2MB |
| Loại file | Phải là hình ảnh |
### Ví dụ gửi bằng Postman
Chọn:
```txt
Method: POST
URL: http://127.0.0.1:8000/api/profile/avatar
Authorization: Bearer token
Body: form-data
Key: avatar
Type: File
Value: chọn ảnh từ máy
```
### Response thành công
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Nguyen Van A",
      "email": "vana@gmail.com",
      "phone": "0909123456",
      "avatar": "http://127.0.0.1:8000/storage/avatars/avatar-name.webp"
    },
    "message": "Ảnh đại diện đã được cập nhật."
  }
}
```
### Response lỗi khi chưa chọn ảnh
```json
{
  "message": "Vui lòng chọn ảnh đại diện.",
  "errors": {
    "avatar": [
      "Vui lòng chọn ảnh đại diện."
    ]
  }
}
```
### Response lỗi khi file không phải hình ảnh
```json
{
  "message": "Tệp đã chọn phải là hình ảnh.",
  "errors": {
    "avatar": [
      "Tệp đã chọn phải là hình ảnh."
    ]
  }
}
```
### Response lỗi khi sai định dạng
```json
{
  "message": "Ảnh đại diện chỉ hỗ trợ JPG, PNG hoặc WebP.",
  "errors": {
    "avatar": [
      "Ảnh đại diện chỉ hỗ trợ JPG, PNG hoặc WebP."
    ]
  }
}
```
### Response lỗi khi ảnh lớn hơn 2MB
```json
{
  "message": "Ảnh đại diện không được lớn hơn 2 MB.",
  "errors": {
    "avatar": [
      "Ảnh đại diện không được lớn hơn 2 MB."
    ]
  }
}
```
### Response lỗi khi chưa đăng nhập
```json
{
  "message": "Unauthenticated."
}
```
---
## Cập nhật mật khẩu

**Method:** PUT  
**URL:** `/user/password`  
**Mục đích:** Cho phép user đang đăng nhập đổi mật khẩu tài khoản.

### Yêu cầu đăng nhập

API này cần token đăng nhập.

### Headers

```txt
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

### Body

Dạng gửi dữ liệu: `JSON`

```json
{
  "current_password": "123456",
  "password": "654321",
  "password_confirmation": "654321"
}
```

### Giải thích field

| Tên field | Kiểu dữ liệu | Bắt buộc | Mô tả |
|---|---|---|---|
| current_password | string | Có | Mật khẩu hiện tại của user |
| password | string | Có | Mật khẩu mới, tối thiểu 6 ký tự |
| password_confirmation | string | Có | Nhập lại mật khẩu mới, phải giống `password` |

### Điều kiện validate

| Field | Điều kiện |
|---|---|
| current_password | Bắt buộc, kiểu chuỗi |
| password | Bắt buộc, kiểu chuỗi, tối thiểu 6 ký tự |
| password_confirmation | Bắt buộc vì `password` dùng rule `confirmed` |
| current_password | Phải đúng với mật khẩu hiện tại của user |

### Response thành công

```json
{
  "data": {
    "message": "Mật khẩu đã được cập nhật."
  }
}
```

### Response lỗi khi mật khẩu hiện tại không đúng

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": [
      "Mật khẩu hiện tại không đúng."
    ]
  }
}
```

### Response lỗi khi thiếu mật khẩu hiện tại

```json
{
  "message": "The current password field is required.",
  "errors": {
    "current_password": [
      "The current password field is required."
    ]
  }
}
```

### Response lỗi khi mật khẩu mới dưới 6 ký tự

```json
{
  "message": "The password field must be at least 6 characters.",
  "errors": {
    "password": [
      "The password field must be at least 6 characters."
    ]
  }
}
```

### Response lỗi khi xác nhận mật khẩu không khớp

```json
{
  "message": "The password field confirmation does not match.",
  "errors": {
    "password": [
      "The password field confirmation does not match."
    ]
  }
}
```

### Response lỗi khi chưa đăng nhập

```json
{
  "message": "Unauthenticated."
}
```
II) lấy api products
**Method:** GET  
lấy tất cả sản phẩm /products
lấy chi tiết sản phẩm /product/{slug}
III) lấy api blog
**Method:** GET
lấy tất cả bài viết /blogs
lấy chi tiết bài viết/blog/{slug}

