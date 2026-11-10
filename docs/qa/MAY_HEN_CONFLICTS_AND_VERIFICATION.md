# MÂY HẸN (MAYHEN.VN) — SPEC CONFLICTS & VERIFICATION REGISTER

Tài liệu này ghi nhận toàn bộ các mâu thuẫn giữa Đặc tả (Specs/Docs) và Mã nguồn thực tế (Codebase), kèm theo quyết định xử lý an toàn nhất.

---

### SPEC_CONFLICT-01: Quy Tắc So Khớp Số Tiền Webhook Thanh Toán (`>=` vs `===`)
- **Vị trí tài liệu**: `docs/MAY_HEN_DOMAIN_INVARIANTS_AND_STATE_MACHINES.md:L71` (`INV-01`)
  * *Tài liệu mô tả*: Webhook chấp nhận khi số tiền `>= order.amount`.
- **Vị trí mã nguồn**: `app/Http/Controllers/Client/CheckoutController.php:L225`
  * *Code thực tế*: `if ($amount !== $order->amount) { ... return 400; }`
- **Phân tích rủi ro kế toán**:
  * Nếu dùng `===`: Khách chuyển thừa dù chỉ 1 đồng hoặc làm tròn số tiền thì hệ thống từ chối kích hoạt, dẫn đến khiếu nại khách hàng.
  * Nếu dùng `>=`: Khách chuyển thừa vẫn được kích hoạt gói, số tiền thừa được ghi nhận trong audit log để kế toán xử lý.
- **Khuyến nghị & Xử lý**:
  * Giữ nguyên `===` theo code hiện hành nếu yêu cầu hạch toán khớp 100% hóa đơn.
  * Bổ sung log cảnh báo rõ ràng khi số tiền chuyển thừa và cho phép Admin phê duyệt thủ công (`OrderController@approve`).

---

### SPEC_CONFLICT-02: Số Lượng Section Tiêu Chuẩn Trong Mẫu Thiệp (6 hoặc 7 Section Cũ vs 10 Section Chuẩn Hóa)
- **Vị trí tài liệu cũ**: `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md:L112` và một số tài liệu thiết kế bản đầu mô tả thiệp gồm 6-7 section.
- **Vị trí tài liệu chuẩn & Mã nguồn hiện hành**: `AGENTS.md` (Nguyên tắc tạo mẫu thiệp số 1), `.agents/skills/mayhen-invitation-standards/SKILL.md` và `tests/Feature/CanonicalTemplateSectionsTest.php`.
- **Thực tế mã nguồn**: 100% template mẫu mới bắt buộc có đủ 10 Section tiêu chuẩn:
  1. Opening -> 2. Hero Cover -> 3. Love Story & Quote -> 4. Family & Couple -> 5. Countdown & Calendar -> 6. Events & Maps -> 7. Timeline -> 8. Dresscode -> 9. Album 10 Photos -> 10. Gift, Interactive & Ending.
- **Quyết định xử lý**: Áp dụng chuẩn 10 Section bất biến cho toàn hệ thống để đảm bảo tương thích 100% với Studio Builder và chuyển đổi mẫu (Template Switcher) không bị mất dữ liệu.

---

### SPEC_CONFLICT-03: Ảnh Chân Dung Cặp Đôi Trong Phần Ký Tên & Xác Nhận Tham Dự (RSVP)
- **Vị trí tài liệu cũ**: Một số bản mock-up đồ họa cũ đặt ảnh cưới làm hình nền cho canvas ký tên hoặc ảnh minh họa trong form RSVP.
- **Vị trí quy tắc chuẩn & Mã nguồn**: `AGENTS.md` (Nguyên tắc số 8), `context.gemini.md` (Dòng 31-35) và `tests/Feature/Gate2TemplateStudioAndSyncInvariantsTest.php` (`INV-37`).
- **Phân tích nghiệp vụ**: Khách hàng tạo thiệp không có ô để tùy biến các ảnh này trong Studio Editor; việc đặt ảnh mẫu người lạ trong thiệp gửi cho khách mời gây phản cảm và sai lệch thông tin ngày cưới.
- **Quyết định xử lý**: CẤM TUYỆT ĐỐI đặt ảnh người trong 2 phần này. 100% mẫu thiệp chỉ được dùng hiệu ứng CSS thuần (stardust, glow), sticker SVG hoặc tem niêm phong sáp (wax seal) trang nhã.

---

### SPEC_CONFLICT-04: Khóa IP Khách Gửi RSVP Trên Mạng Wi-Fi / NAT Công Cộng
- **Vị trí tài liệu**: `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md:L412` đề xuất khóa theo địa chỉ IP sau 3 lần gửi phản hồi liên tiếp để chống spam.
- **Vị trí mã nguồn & Phát hiện lỗi**: `app/Http/Controllers/Client/GuestController.php` (`BUG-RSVP-001`).
- **Phân tích nghiệp vụ**: Tại tiệc cưới, trung tâm hội nghị hoặc hộ gia đình, hàng chục khách mời cùng dùng chung một mạng Wi-Fi công cộng (chung địa chỉ IP NAT). Nếu khóa cứng theo IP thì chỉ vị khách đầu tiên gửi được RSVP, các khách sau bị khóa oan (422) gây bất tiện lớn.
- **Quyết định xử lý**: Điều chỉnh cơ chế chống spam kết hợp kiểm tra `guest_token`, số điện thoại và tên khách mời thay vì chỉ dựa vào IP đơn thuần.

---

### SPEC_CONFLICT-05: Giới Hạn Chiều Dài Token Điểm Danh QR Khi Quét Bằng Camera
- **Vị trí mã nguồn ban đầu**: `app/Http/Controllers/Client/CheckInController.php` giới hạn rule validation `'token' => ['nullable', 'string', 'max:64']`.
- **Thực tế sử dụng tại cổng tiệc cưới**: Ứng dụng Camera điện thoại thông minh (iPhone/Android) khi quét mã QR sẽ trả về toàn bộ liên kết URL đầy đủ (`https://mayhen.vn/le-tan/diem-danh?token=abcdef...`), chuỗi này thường dài từ 75 đến 120 ký tự.
- **Phát hiện lỗi & Giải pháp**: Lỗi `BUG-CHECKIN-002` đã nâng giới hạn validation của `token` lên `max:500` và áp dụng logic Regex bóc tách token linh hoạt từ chuỗi URL, đảm bảo điểm danh mượt mà 100% cho mọi loại máy quét và camera.


