# Thư viện bổ sung của dự án

File này ghi lại các thư viện được cài thêm ngoài bộ dependency ban đầu của dự án. Khi cài thêm thư viện mới, cần cập nhật tài liệu này cùng `composer.json` hoặc `package.json` tương ứng.

## Backend

### Laravel Excel

- Package: `maatwebsite/excel`
- Constraint trong dự án: `~3.1.0`
- Phiên bản đang khóa: `3.1.69`
- Dependency chính đi kèm: `phpoffice/phpspreadsheet` phiên bản `1.30.5`
- Ngày thêm: `2026-07-11`
- Phạm vi: backend Laravel
- Mục đích: tạo và tải file Excel `.xlsx`, hỗ trợ nhiều sheet cho dữ liệu sản phẩm và biến thể.
- Lý do lựa chọn: tích hợp trực tiếp với Laravel 10, có API dành cho export và hỗ trợ định dạng workbook nhiều sheet.
- PHP extension cần thiết: `zip`, `xml`, `xmlreader`, `xmlwriter`, `dom`, `gd`, `mbstring`. Môi trường hiện tại đã có đầy đủ.
- Cài đặt:

  ```bash
  cd backend
  composer require maatwebsite/excel:~3.1.0
  ```

- Gỡ cài đặt nếu không còn sử dụng:

  ```bash
  cd backend
  composer remove maatwebsite/excel
  ```

- Các file quản lý dependency bị tác động: `backend/composer.json`, `backend/composer.lock`.
- Vị trí dự kiến sử dụng: các lớp export trong `backend/app/Exports` và chức năng xuất dữ liệu tại Admin Product.

### Quill

- Package: `quill`
- Constraint trong dự án: `^2.0.3`
- Phiên bản đang khóa: `2.0.3`
- Ngày thêm: `2026-07-11`
- Phạm vi: giao diện Admin của backend Laravel
- Giấy phép: BSD-3-Clause
- Mục đích: trình soạn thảo rich text cho mô tả sản phẩm.
- Chức năng đang dùng: tiêu đề, chữ đậm/nghiêng/gạch chân, trích dẫn, danh sách, căn chỉnh, liên kết và xóa định dạng.
- Asset được đóng gói cục bộ bằng Vite, không phụ thuộc CDN khi vận hành.
- Cài đặt:

  ```bash
  cd backend
  npm install quill@^2.0.3 --save
  npm run build
  ```

- Gỡ cài đặt nếu không còn sử dụng:

  ```bash
  cd backend
  npm uninstall quill
  ```

- Các file quản lý dependency bị tác động: `backend/package.json`, `backend/package-lock.json`.
- Vị trí sử dụng: `backend/resources/js/admin/product-description-editor.js` và trang sửa Product.

## Quy ước cập nhật

Mỗi dependency mới cần ghi rõ package, constraint, phiên bản khóa, ngày thêm, mục đích, nơi sử dụng, lệnh cài đặt/gỡ bỏ và các yêu cầu môi trường liên quan.
