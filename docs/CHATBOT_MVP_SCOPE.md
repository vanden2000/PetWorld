# Phạm vi MVP — PetWorld AI Assistant

**Trạng thái:** Đã duyệt Bước 1

Tài liệu này chỉ chốt phạm vi cho phiên bản đầu tiên. Chưa bao gồm thay đổi cơ sở dữ liệu, API, giao diện hoặc kết nối AI.

## 1. Mục tiêu MVP

Khách có thể dùng hộp chat hiện có để:

1. Tìm và nhận gợi ý sản phẩm đang bán tại PetWorld.
2. Hỏi các câu hỏi thường gặp về mua hàng.
3. Tra cứu trạng thái đơn hàng của chính họ sau khi đăng nhập.

MVP phải dùng dữ liệu thật của PetWorld cho giá, tồn kho, sản phẩm và đơn hàng. Chatbot không được tự suy đoán các thông tin này.

## 2. Phạm vi được thực hiện

### 2.1. Tư vấn và tìm sản phẩm

Chatbot nhận câu hỏi tự nhiên bằng tiếng Việt, ví dụ:

- "Có thức ăn cho mèo dưới 200 nghìn không?"
- "Tôi cần đồ chơi cho chó nhỏ."
- "Sản phẩm này còn hàng không?"

Chatbot có thể hỏi tối đa hai câu để làm rõ nhu cầu, sau đó đề xuất tối đa ba sản phẩm. Mỗi đề xuất gồm tên, ảnh, giá hiện hành, tình trạng tồn kho, lý do phù hợp và liên kết đến trang chi tiết.

Nguồn dữ liệu là các sản phẩm và biến thể đang hoạt động trong PetWorld. Khi khách yêu cầu hàng có sẵn, chỉ đề xuất biến thể có số lượng lớn hơn 0.

### 2.2. Câu hỏi thường gặp

Chatbot trả lời các chủ đề sau từ nội dung PetWorld đã được quản trị xác nhận:

- Giao hàng.
- Thanh toán.
- Đổi trả.
- Voucher.
- Kênh liên hệ hỗ trợ.

Nếu chưa có nội dung xác nhận, chatbot phải nói rõ chưa có thông tin và đề nghị khách liên hệ hỗ trợ; không tự tạo chính sách.

### 2.3. Tra cứu đơn hàng

Chỉ người dùng đã đăng nhập mới được hỏi về đơn hàng. Chatbot được hiển thị:

- Mã đơn.
- Trạng thái đơn hàng.
- Trạng thái thanh toán.
- Ngày tạo đơn.
- Tổng tiền.
- Liên kết xem chi tiết đơn hàng.

Chatbot chỉ được truy xuất đơn thuộc tài khoản hiện tại. Không hiển thị địa chỉ, số điện thoại hoặc thông tin đơn hàng của người khác trong câu trả lời chat.

## 3. Ngoài phạm vi MVP

Các phần dưới đây không được triển khai trong MVP:

- Chẩn đoán bệnh, kê thuốc hoặc hướng dẫn liều lượng cho thú cưng.
- Tự động hủy đơn, hoàn tiền, tạo voucher hoặc thay đổi thông tin đơn qua câu chat.
- Chat trực tiếp thời gian thực với nhân viên.
- Đề xuất cá nhân hóa từ lịch sử mua hàng/giỏ hàng.
- Nhận diện ảnh thú cưng.
- Quản trị ticket hỗ trợ chuyên biệt.

Với dấu hiệu sức khỏe nghiêm trọng, chatbot chỉ khuyến nghị khách đưa thú cưng đến bác sĩ thú y.

## 4. Quy tắc bắt buộc

1. Giá, khuyến mãi, tồn kho, chính sách và trạng thái đơn phải lấy từ backend PetWorld.
2. Không được bịa sản phẩm, giá, tồn kho, voucher hoặc chính sách.
3. Chỉ trả lời bằng tiếng Việt, ngắn gọn và dễ hiểu.
4. Nếu thiếu thông tin để tư vấn, hỏi tối đa hai câu làm rõ trong một lượt.
5. Nếu không thể trả lời tin cậy, đề nghị khách liên hệ PetWorld.
6. Không đưa ra chẩn đoán hoặc chỉ định y tế cho thú cưng.
7. Không để khóa AI hoặc dữ liệu nhạy cảm xuất hiện ở frontend.

## 5. Tiêu chí duyệt MVP

MVP đạt yêu cầu khi đáp ứng toàn bộ tiêu chí sau:

- [ ] Gửi tin nhắn từ hộp chat và nhận được phản hồi qua backend.
- [ ] Câu hỏi tìm sản phẩm trả về sản phẩm đang hoạt động với giá và tồn kho thật.
- [ ] Không gợi ý hàng hết khi khách yêu cầu sản phẩm còn hàng.
- [ ] Câu trả lời FAQ chỉ dựa trên nội dung chính sách đã duyệt.
- [ ] Khách chưa đăng nhập được yêu cầu đăng nhập khi hỏi đơn hàng.
- [ ] Khách đăng nhập chỉ thấy đơn hàng của chính mình.
- [ ] Câu hỏi về tình trạng y tế được trả lời theo giới hạn an toàn.
- [ ] Khi AI hoặc mạng lỗi, hộp chat hiển thị thông báo thân thiện và vẫn dùng được.

## 6. Câu hỏi cần duyệt trước Bước 2

1. Có giữ nguyên ba chức năng MVP ở mục 1 không?
2. Bot có được đề xuất sản phẩm hết hàng như lựa chọn thay thế khi khách không yêu cầu "còn hàng" không? Mặc định: không.
3. Bot có được hiển thị tổng tiền và trạng thái thanh toán của đơn hàng không? Mặc định: có.
4. Danh sách chính sách/FAQ nào sẽ là nguồn chính thức khi triển khai Bước 2? Mặc định: bắt đầu với giao hàng, thanh toán, đổi trả và voucher.

## 7. Bước tiếp theo sau khi duyệt

Sau khi tài liệu này được duyệt, Bước 2 chỉ chuẩn bị cấu hình kết nối AI ở backend. Chưa tạo migration hoặc thay đổi giao diện ở bước đó.
