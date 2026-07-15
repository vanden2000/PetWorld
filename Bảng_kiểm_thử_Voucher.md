# BẢNG KIỂM THỬ CHỨC NĂNG VOUCHER - PETWORLD

Dưới đây là bảng kiểm thử chi tiết chức năng **Voucher / Mã giảm giá** đã được cập nhật sau khi hoàn tất phát triển cả Frontend và Backend. Tất cả các kịch bản kiểm thử lỗi trước đây đều đã được khắc phục hoàn toàn và chuyển sang trạng thái **Đạt**.

---

## Bảng kết quả kiểm thử (Voucher / Mã giảm giá)

| Tình huống | Dữ liệu mẫu | Kết quả mong đợi | Kết quả thực tế (Hiện tại) | Tình trạng |
| :--- | :--- | :--- | :--- | :---: |
| **Áp dụng voucher hợp lệ** | Chọn một mã giảm giá bất kỳ đang hoạt động trong danh sách hiển thị (Ví dụ: chọn mã `GIAM50K`). | Giảm số tiền tương ứng trên tổng đơn hàng. | Hệ thống kiểm tra và trừ đi số tiền tương ứng (`discount_value`, ví dụ: 50.000đ) trực tiếp vào tổng tiền thanh toán. | **Đạt** |
| **Voucher không tồn tại** | Khách hàng không thể tự nhập mã mà chỉ chọn mã trong danh sách hiển thị. Hoặc gửi `voucher_id` không tồn tại trực tiếp qua request API (Ví dụ: `99999`). | Giao diện không hiển thị mã không có thực. Nếu cố tình gửi ID không tồn tại qua API, Backend sẽ chặn và báo lỗi validation. | Giao diện Checkout lấy danh sách voucher từ API nên không hiển thị mã không tồn tại. Ở Backend, validation `exists:vouchers,id` hoạt động chính xác và từ chối xử lý đơn hàng. | **Đạt** |
| **Voucher hết hạn** | Không hiển thị trên danh sách chọn. Hoặc voucher cũ đã lưu trong LocalStorage đã quá hạn sử dụng. Hoặc cố tình gửi `voucher_id` hết hạn qua API. | Giao diện không hiển thị voucher hết hạn. Nếu voucher cũ đã lưu bị quá hạn, hệ thống tự động gỡ bỏ khi tải trang. Backend sẽ chặn và báo lỗi nếu gửi mã hết hạn qua API. | API chỉ trả về các mã nằm trong thời hạn (`start_date` & `end_date`). Nếu dùng mã đã lưu trong LocalStorage đã hết hạn, Frontend tự động gỡ và thông báo lỗi. Backend kiểm tra và từ chối áp dụng qua hàm `canBeApplied`. | **Đạt** |
| **Voucher đã hết lượt sử dụng** | Không hiển thị trên danh sách chọn. Hoặc voucher cũ đã lưu trong LocalStorage đã hết lượt sử dụng. Hoặc cố tình gửi `voucher_id` hết lượt qua API. | Giao diện không hiển thị voucher đã hết lượt. Nếu voucher cũ đã lưu bị hết lượt, hệ thống tự động gỡ bỏ khi tải trang. Backend chặn và báo lỗi nếu gửi qua API. | API chỉ trả về mã còn lượt dùng. Nếu voucher lưu trong LocalStorage hết lượt, hệ thống tự động gỡ bỏ và thông báo toast lỗi. Backend kiểm tra bằng hàm `canBeApplied` để từ chối và khôi phục trạng thái voucher nếu đơn hàng bị hủy. | **Đạt** |
| **Đơn hàng chưa đạt giá trị tối thiểu** | Áp dụng voucher hợp lệ, sau đó người dùng giảm số lượng hoặc xóa sản phẩm trong giỏ hàng khiến tổng giá trị đơn hàng nhỏ hơn giá trị tối thiểu của voucher (`min_order_value`). | Hệ thống tự động gỡ bỏ voucher và thông báo lỗi/yêu cầu điều kiện tối thiểu. | Frontend tự động phát hiện sự thay đổi giá trị đơn hàng, gỡ voucher khỏi state/LocalStorage và hiển thị thông báo toast. Backend chặn tạo đơn hàng và báo lỗi nếu nhận được request không đủ điều kiện. | **Đạt** |
| **Hủy áp dụng voucher** | Click nút "Hủy" (hoặc nút gỡ bỏ) bên cạnh thông tin voucher đang được áp dụng trên giao diện Checkout. | Gỡ bỏ voucher đang áp dụng, tổng tiền thanh toán tính lại và khôi phục về giá trị ban đầu. | Giao diện Checkout hiển thị nút "Hủy" trực quan bên cạnh voucher đang áp dụng. Khi click, voucher được gỡ bỏ ngay lập tức và tổng tiền thanh toán được hoàn về giá trị ban đầu. | **Đạt** |
| **Tính lại tổng tiền sau khi áp dụng voucher** | Sau khi áp dụng voucher. | Tổng tiền = Tạm tính + Phí vận chuyển - Giảm giá. | React tự động tính toán lại Tổng tiền thanh toán chính xác theo công thức thời gian thực trên cả Frontend và được đồng bộ kiểm tra chặt chẽ trên Backend. | **Đạt** |

---

## Chi tiết kỹ thuật triển khai đã đối chiếu

### 1. Phía Backend (Laravel PHP)
- **Model `Voucher`**: Đã định nghĩa hàm `canBeApplied($orderValue, $at)` kiểm tra toàn diện các điều kiện: Trạng thái kích hoạt (`active`), thời hạn sử dụng (`start_date`/`end_date`), giá trị đơn hàng tối thiểu (`min_order_value`), và giới hạn số lượt sử dụng (`usage_limit`).
- **`OrderController`**:
  - Khi tạo đơn hàng (`store`): Thực hiện khóa bản ghi voucher (`lockForUpdate`) để tránh lỗi race condition khi nhiều người dùng cùng áp dụng. Đảm bảo tính toán lại giá từ database trước khi trừ voucher.
  - Khi hủy đơn hàng (`cancel`): Phục hồi lại tồn kho biến thể sản phẩm, đồng thời khôi phục lại trạng thái hoạt động cho voucher nếu đơn hàng bị hủy giúp giải phóng lượt dùng.
  - **API `/api/vouchers`**: Chỉ trả về danh sách các voucher đang active và trong thời hạn sử dụng kèm thông tin số tiền còn thiếu để kích hoạt voucher nhằm tăng trải nghiệm người dùng.

### 2. Phía Frontend (Next.js React)
- **`CheckoutView.jsx`**:
  - Tích hợp Modal chọn voucher tiện lợi từ API.
  - Tự động gỡ voucher và hiển thị thông báo toast khi giỏ hàng thay đổi giá trị dẫn đến không đủ điều kiện tối thiểu áp dụng mã.
  - Hỗ trợ cơ chế lưu voucher đang áp dụng vào `localStorage` cho từng luồng (giỏ hàng thông thường và luồng mua ngay `Buy Now`). Khi tải lại trang, hệ thống tự động kiểm tra tính hợp lệ của mã đã lưu với API trước khi tự động áp dụng lại.
  - Cung cấp nút hủy áp dụng voucher trực quan.
