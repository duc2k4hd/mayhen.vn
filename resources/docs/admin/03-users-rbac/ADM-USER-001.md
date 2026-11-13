---
id: ADM-USER-001
title: "Tra cứu hồ sơ khách hàng toàn diện (Customer 360 View) và lịch sử giao dịch"
category: "Quản lý Tài khoản Khách & RBAC"
summary: "Hướng dẫn chi tiết về Tra cứu hồ sơ khách hàng toàn diện (Customer 360 View) và lịch sử giao dịch trên hệ thống Mây Hẹn."
reader: "PER-ADM-01 / PER-ADM-03"
order: 8
updated_at: "2026-11-12"
tags: [mayhen, quản-lý-tài-khoản-khách-&-rbac]
mapped_page: "ADM-USER-PAGE"
mapped_route: "admin.users.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# ADM-SOP-USER-001: Quy trình Tra cứu Hồ sơ Khách hàng Toàn diện (Customer 360 View) & Hỗ trợ Kỹ thuật An toàn

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên Chăm Sóc Khách Hàng (Support), Quản Lý (Manager) hoặc Quản Trị Viên Tối Cao (Super Admin).  
> **Mức độ rủi ro:** `HIGH` | **Vị trí màn hình:** `Admin Sidebar` -> `Khách Hàng & Tài Khoản` (`/admin/users`)  
> **Cam kết kiểm toán:** Mọi lượt truy vấn dữ liệu chi tiết và hành động hỗ trợ đều tự động ghi vết trong bảng `audit_logs`.  

Hồ sơ khách hàng Customer 360 là trung tâm dữ liệu hội tụ toàn bộ hành trình của một cặp đôi trên Mây Hẹn: từ thông tin tài khoản, đơn hàng thanh toán VietQR, danh sách thiệp cưới đã tạo, nhật ký chỉnh sửa bản vẽ, cho đến lịch sử ticket khiếu nại. Quy trình này hướng dẫn nhân viên tra cứu nhanh trong 60 giây để xử lý yêu cầu hỗ trợ mà vẫn đảm bảo tuyệt đối an toàn thông tin cá nhân theo Luật Bảo vệ Dữ liệu Cá nhân (Luật 91/2025/QH15).

---

## 1. Mục đích & Nguyên tắc Bảo mật Khách hàng
- **Hỗ trợ thần tốc:** Giúp nhân viên CSKH nắm bắt ngay toàn bộ bối cảnh sự cố của cô dâu chú rể mà không bắt khách hàng lặp đi lặp lại thông tin nhiều lần.
- **Tuân thủ Quyền Riêng Tư (PII Protection):** Số điện thoại và email được mã hóa hoặc ẩn bớt ký tự nhạy cảm ở màn hình danh sách, chỉ hiển thị đầy đủ khi nhân viên nhấp xem chi tiết có ghi log.
- **Nguyên tắc "Không Thể Đảo Ngược Mật Khẩu":** Nhân viên không bao giờ nhìn thấy mật khẩu dạng văn bản thô (Plaintext Password) của khách hàng. Khi khách quên mật khẩu, chỉ gửi liên kết đặt lại mật khẩu an toàn qua email/SMS đã đăng ký.

---

## 2. Các bước tra cứu hồ sơ Customer 360 View

### Bước 1: Tiếp nhận yêu cầu & Định danh khách hàng
Khi nhận được cuộc gọi hotline, tin nhắn Zalo OA hoặc ticket từ khách hàng, thu thập một trong các dữ liệu định danh:
- Địa chỉ Email đăng ký tài khoản.
- Số điện thoại liên hệ.
- Mã đơn hàng (bắt đầu bằng `MH...`, ví dụ: `MH1234567890`).
- Đường dẫn thiệp cưới của khách (ví dụ: `mayhen.vn/thiep/minh-duc-thanh-thao`).

### Bước 2: Tra cứu trên thanh tìm kiếm nhanh Admin
1. Mở Admin Cockpit tại `/admin/users`.
2. Gõ từ khóa vào ô tìm kiếm toàn diện (hệ thống hỗ trợ tìm kiếm mờ theo email, số điện thoại hoặc họ tên).
3. Nhấn `Enter` hoặc nhấp nút kính lúp. Danh sách khách hàng trùng khớp sẽ hiển thị ngay lập tức.
4. Nhấp vào tên khách hàng hoặc nút **[Xem Hồ Sơ 360°]** để mở màn hình chi tiết.

### Bước 3: Đọc hiểu 5 phân vùng dữ liệu cốt lõi
Trang Customer 360 View được chia thành 5 khối thông tin trực quan:
1. **Thông tin định danh & Bảo mật (Identity & Security):**
   - Trạng thái tài khoản: `Active` (Bình thường), `Unverified` (Chưa kích hoạt email), hoặc `Suspended` (Đang bị tạm khóa).
   - Thời điểm đăng nhập gần nhất, địa chỉ IP và thiết bị sử dụng.
2. **Danh mục Thiệp cưới sở hữu (Owned Invitations):**
   - Tên cặp đôi, gói dịch vụ đang áp dụng (`Free`, `Plus`, `Pro VIP`).
   - Mẫu thiệp đang dùng (Template Slug), trạng thái xuất bản (`Published` / `Draft`).
   - Thời hạn sử dụng và nút xem nhanh bản xem trước (Preview).
3. **Lịch sử Giao dịch & Đơn hàng (Orders & Billing):**
   - Danh sách mã đơn hàng, số tiền đã thanh toán, thời gian giao dịch SePay.
   - Trạng thái thanh toán: `paid`, `pending`, `refunded`.
4. **Dữ liệu Khách mời & Điểm danh (Guests & RSVP Snapshot):**
   - Tổng số khách mời trong danh sách, tỷ lệ khách đã xác nhận tham dự.
   - Sơ đồ bàn tiệc đã lập, số lượt khách đã check-in qua cổng tiếp tân.
5. **Nhật ký Tác động Hỗ trợ (Support & Audit Trail):**
   - Danh sách các nhân viên quản trị từng mở hồ sơ, thời điểm mở và các thay đổi đã thực hiện.

### Bước 4: Thực hiện hành động hỗ trợ được ủy quyền
Tùy theo yêu cầu của khách, nhân viên chỉ được thực hiện các hành động trong phạm vi quyền hạn:
- **Gửi lại email kích hoạt / Đặt lại mật khẩu:** Nhấp nút **[Gửi Email Đặt Lại Mật Khẩu]** -> Hệ thống gửi token hạn 30 phút vào hộp thư của khách.
- **Kích hoạt gói dịch vụ thủ công (nếu chuyển khoản đúng mà bị trễ mạng):** Cần đối chiếu chính xác mã tham chiếu SePay theo đúng quy trình `ADM-ORDR-002`.
- **Khôi phục bản thảo nháp bị xung đột:** Hướng dẫn khách hàng chọn bản sao lưu cục bộ gần nhất hoặc hỗ trợ rollback phiên bản revision từ hệ thống.

---

## 3. Tiêu chí Kiểm chứng Thao tác Hoàn tất
- Nhân viên cung cấp câu trả lời chính xác, giải quyết thỏa đáng vướng mắc cho khách hàng trong vòng 3 phút.
- Bản ghi truy vấn và thao tác xuất hiện ngay lập tức trong bảng `audit_logs` với action tương ứng (ví dụ: `user.view_profile`, `user.send_reset_password_link`).

---

## 4. Những điều Tuyệt đối Bị cấm (Zero-Tolerance Rules)
- **CẤM đọc số thẻ ngân hàng, mật khẩu hoặc mã OTP** của khách qua điện thoại hay tin nhắn.
- **CẤM can thiệp sửa đổi trực tiếp tên cô dâu chú rể hoặc thông tin nhạy cảm trên thiệp** khi chưa có xác nhận bằng văn bản/tin nhắn chính chủ từ số điện thoại đăng ký tài khoản.
- **CẤM xuất khẩu toàn bộ danh sách khách mời (Export CSV) của người dùng** về máy tính cá nhân của nhân viên mà không có phê duyệt từ Quản Lý (Manager).
- **CẤM chia sẻ màn hình làm việc chứa dữ liệu PII của khách hàng** cho người không có thẩm quyền.
