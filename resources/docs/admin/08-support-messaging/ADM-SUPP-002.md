---
id: ADM-SUPP-002
title: "Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)"
category: "Hỗ trợ Khách hàng & Cổng Tin nhắn"
summary: "Hướng dẫn nhân viên CSKH quản lý thư viện câu trả lời nhanh, sử dụng phím tắt shortcut (:safari, :qrpin, :vietqr) và tự động thay thế biến động ({customer_name}, {wedding_title}) theo văn phong Quiet Luxury."
reader: "PER-ADM-01 (Nhân viên CSKH & Vận hành Hỗ trợ)"
order: 2
updated_at: "2026-11-15"
tags: [admin, cau-tra-loi-mau, canned-responses, shortcut, quiet-luxury, cskh, support-ticket]
mapped_page: "ADM-SUPP-PAGE"
mapped_route: "admin.canned.index"
risk_level: "MEDIUM"
required_role: "support"
---

# Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)

> [!IMPORTANT]
> **Vai trò thực hiện:** `Customer Care Specialist (CSKH)`, `Support Lead`, hoặc `Super Admin`.  
> **Route Quản lý Mẫu phản hồi:** `GET /admin/canned-responses` (`CannedResponseController@index`).  
> **Route Tạo mới / Cập nhật:** `POST /admin/canned-responses` (`@store`) & `PUT /admin/canned-responses/{id}` (`@update`).  
> **Dịch vụ backend:** `App\Services\CannedResponseService`.  
> **Đặc trưng văn phong Mây Hẹn:** *Quiet Luxury*, tinh tế, nhã nhặn, tôn trọng sự trang trọng của hôn lễ, không dùng từ ngữ công nghệ khô khan.


![Giao diện quản lý Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)](/assets/client/docs/images/admin/08-support-messaging/adm-supp-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Trong các ngày cao điểm mùa cưới (Thứ Sáu đến Chủ Nhật), hàng trăm cặp đôi có thể cùng gửi thắc mắc về những vấn đề tương tự nhau (Ví dụ: tại sao nhạc nền không tự phát trên iPhone Safari, cách sửa địa chỉ sảnh tiệc giờ chót, hoặc đối soát kích hoạt gói VietQR).

Nếu nhân viên tự gõ phản hồi từ đầu:
1. Tốn nhiều thời gian, làm chậm chỉ số SLA cam kết.
2. Dễ xảy ra sai sót kỹ thuật hoặc văn phong không đồng bộ, thiếu đi sự ân cần và trang trọng của thương hiệu Mây Hẹn.

Hệ thống **Thư viện Câu Trả Lời Mẫu (Canned Responses)** giúp nhân viên CSKH phản hồi khách hàng trong vòng **3 giây** bằng cách gõ phím tắt shortcut hoặc chọn từ danh mục, tự động điền tên cặp đôi và mã ticket chính xác.

---

## 2. Hệ Thống Biến Động Động Tự Động (Merge Tokens)

Khi nhân viên chèn câu trả lời mẫu vào khung phản hồi ticket, hệ thống tự động quét và thay thế các biến ký hiệu trong ngoặc nhọn `{...}` bằng dữ liệu thực tế của cặp đôi:

| Biến động (`Token`) | Dữ liệu được thay thế tự động | Ví dụ hiển thị thực tế |
| :--- | :--- | :--- |
| `{customer_name}` | Tên cô dâu, chú rể hoặc người tạo ticket. | `Hoàng Long & Thanh Thảo` |
| `{wedding_title}` | Tiêu đề đám cưới lưu trong hồ sơ tiệc. | `Lễ Thành Hôn Hoàng Long & Thanh Thảo` |
| `{ticket_code}` | Mã định danh Ticket hỗ trợ hiện hành. | `TK-2026-8812` |
| `{agent_name}` | Tên nhân viên CSKH đang trực tiếp phản hồi. | `Minh Thư (Mây Hẹn Atelier)` |
| `{order_code}` | Mã đơn hàng thanh toán liên quan (nếu có). | `ORD-2026-9812` |

---

## 3. Các Phím Tắt Shortcut Chuẩn Mực Có Sẵn

Hệ thống tích hợp sẵn các mẫu phản hồi thường gặp nhất trong ngành cưới:

| Shortcut | Tiêu đề mẫu | Phân loại | Tình huống sử dụng |
| :--- | :--- | :--- | :--- |
| `:safari` | **Âm thanh không tự phát trên Safari** | `template` (Mẫu thiệp) | Khách phản ánh mở thiệp trên iPhone không nghe thấy nhạc nền do chính sách bảo mật của Apple Safari. |
| `:qrpin` | **Quên mã PIN sảnh tiệc / Lỗi điểm danh** | `technical` (Kỹ thuật) | Cặp đôi hoặc lễ tân quên mã PIN đăng nhập màn hình điểm danh tại sảnh nhà hàng. |
| `:venue` | **Đổi sảnh tiệc hoặc địa chỉ giờ chót** | `invitation` (Thiệp cưới) | Cặp đôi thay đổi phòng tiệc hoặc địa chỉ đón tiếp cần cập nhật bản đồ Google Maps gấp. |
| `:vietqr` | **Đối soát kích hoạt gói qua VietQR** | `billing` (Thanh toán) | Đã nhận được chuyển khoản ngân hàng và đã kích hoạt gói dịch vụ cho khách. |
| `:extend` | **Gia hạn thời gian lưu trữ thiệp cưới** | `billing` (Thanh toán) | Thông báo xác nhận thiệp cưới đã được gia hạn lưu trữ kỷ niệm dài hạn thành công. |

---

## 4. Hướng dẫn từng bước: Sử dụng Canned Response khi Trả Lời Ticket

### Cách 1: Sử dụng phím tắt Shortcut (Nhanh nhất - 3 giây)
1. Khi đang ở trang chi tiết Ticket `/admin/support/{id}`, nhấp chuột vào khung soạn thảo phản hồi.
2. Gõ ký tự `:` kèm theo từ khóa phím tắt (ví dụ: `:safari` hoặc `:vietqr`).
3. Khung gợi ý thông minh (Autocomplete Drawer) xuất hiện ngay dưới con trỏ chuột.
4. Nhấn phím `Enter` hoặc `Tab`:
   * Toàn bộ nội dung mẫu được chèn vào khung soạn thảo.
   * Các biến `{customer_name}`, `{wedding_title}` được tự động thay thế bằng tên thật của khách hàng.
5. Kiểm tra lại nội dung, ký tên nhân viên và nhấn **[ Gửi Phản Hồi ]**.

### Cách 2: Chọn từ Thư viện Mẫu trực quan
1. Dưới chân khung soạn thảo phản hồi Ticket, nhấp vào biểu tượng **[ 📑 Mẫu Trả Lời Nhanh ]**.
2. Hộp thoại mở ra hiển thị danh sách các mẫu sắp xếp theo mức độ phổ biến (`usage_count`):
   * Lọc theo danh mục: *Mẫu thiệp & Âm nhạc*, *Kỹ thuật & Mã PIN*, *Địa điểm tiệc*, *Thanh toán VietQR*.
   * Ô tìm kiếm từ khóa: Nhập từ khóa bất kỳ (ví dụ: `nhạc nền`, `chuyển khoản`, `sảnh tiệc`).
3. Nhấp nút **[ Chèn Mẫu Này ]**.
4. Hệ thống nạp nội dung đã được cá nhân hóa vào khung chat.

---

## 5. Hướng dẫn Tạo Mới & Chỉnh Sửa Câu Trả Lời Mẫu

Khi phát sinh một câu hỏi kỹ thuật mới lặp lại nhiều lần, Support Lead có thể bổ sung mẫu mới vào thư viện:

1. Vào menu **Quản trị CSKH** -> chọn **Thư Viện Câu Trả Lời Mẫu** (`/admin/canned-responses`).
2. Nhấp nút **[ ➕ Thêm Mẫu Mới ]**.
3. Điền các trường thông tin:
   * **Tiêu đề mẫu (`title`)**: Tên gợi nhớ ngắn gọn (ví dụ: `Hướng dẫn chèn video Youtube vào album thiệp`).
   * **Phím tắt (`shortcut`)**: Bắt đầu bằng dấu hai chấm (ví dụ: `:video` hoặc `:youtube`).
   * **Phân loại (`category`)**: Chọn đúng nhóm (`template`, `technical`, `invitation`, `billing`, `general`).
   * **Nội dung phản hồi (`body`)**: Soạn thảo văn phong Quiet Luxury, khéo léo chèn các biến động `{customer_name}`, `{wedding_title}`.
4. Bật công tắc **Kích hoạt sử dụng (`is_active = true`)**.
5. Nhấp **[ Lưu Mẫu Phản Hồi ]**. Mẫu mới lập tức có hiệu lực cho toàn bộ nhân viên trong ca trực.

---

## 6. Quy chuẩn Văn phong Bắt buộc (Tone of Voice Mây Hẹn)

Nhân viên CSKH khi tạo hoặc chỉnh sửa mẫu phản hồi bắt buộc tuân thủ **4 tiêu chí Quiet Luxury**:

1. **Lời chào ấm áp, cá nhân hóa**: Luôn mở đầu bằng:  
   *`"Chào {customer_name}, Mây Hẹn Atelier rất vui được đồng hành cùng bạn và gia đình trong dịp trọng đại này..."`*  
   Tuyệt đối không dùng cụm từ vô cảm: `"Chào bạn, chúng tôi nhận được ticket #1234"`.
2. **Giải thích thấu đáo, trấn an tinh thần**: Ngày cưới cô dâu chú rể rất dễ căng thẳng, luôn đưa ra giải pháp trước và giải thích kỹ thuật nhẹ nhàng sau.
3. **Tuyệt đối không đổ lỗi cho thiết bị của khách**: Không nói *"Do máy iPhone của bạn bị lỗi"* mà nói: *"Do chính sách bảo mật Safari của Apple, chỉ cần một chạm nhẹ của quan khách là âm nhạc sẽ vang lên du dương"*.
4. **Lời chúc phúc chân thành ở phần kết**: Kết thúc bằng lời chúc ngày vui trọn vẹn và chữ ký định danh của nhân viên hỗ trợ.

---

## 7. Những điều Tuyệt đối Bị cấm

1. **CẤM GỬI NỘI DUNG CHỨA TOKEN CHƯA RENDER:** Tuyệt đối không gửi tin nhắn khi các biến còn nguyên dạng thô `{customer_name}` mà hệ thống chưa thay thế được (dấu hiệu khách chưa có tên trong hồ sơ).
2. **CẤM XÓA CÁC MẪU ĐANG CÓ LƯỢT SỬ DỤNG CAO:** Nếu cần cải tiến mẫu phản hồi, hãy dùng chức năng Chỉnh sửa (`update`) thay vì Xóa (`delete`) để bảo toàn số liệu thống kê hiệu suất.
3. **CẤM CHÈN LINK LẠ HOẶC TÀI KHOẢN NGÂN HÀNG CÁ NHÂN VÀO MẪU:** Mọi link thanh toán hoặc hỗ trợ phải trỏ về tên miền chính thức `mayhen.vn`.

---

## 8. Bài viết liên quan
* [ADM-SUPP-001: Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-001.md)
* [ADM-SUPP-003: Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-003.md)
* [ADM-SUPP-004: Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-004.md)
* [ADM-SUPP-005: Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-005.md)
