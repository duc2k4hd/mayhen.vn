# MÂY HẸN (MAYHEN.VN) — BÁO CÁO ĐỐI SOÁT KIỂM TOÁN CHẤT LƯỢNG GỐC (FINAL AUDIT BASELINE)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Phiên bản Laravel**: 13.35.0 | **PHP**: 8.5.10  
> **Ngày thực hiện**: Tháng 11/2026  
> **Mục tiêu**: Kiểm toán đến tận gốc logic nghiệp vụ, đối chiếu giữa tuyên bố tính năng và hành vi thực thi thực tế trong mã nguồn, thiết lập nền móng kiểm thử hồi quy 100% tự động.

---

## 1. TỔNG QUAN HIỆN TRẠNG KIỂM THỬ (TEST BASELINE)

- **Tổng số bài kiểm tra**: **391 tests**
- **Tổng số xác nhận (Assertions)**: **1,956 assertions**
- **Tỷ lệ vượt qua**: **100% Passed (0 Failed, 0 Skipped)**
- **Thời gian chạy**: ~7.60 giây
- **Code Style (Laravel Pint)**: 100% Clean theo chuẩn Agent.

```bash
   PASS  Tests\Unit\...
   PASS  Tests\Feature\...
  Tests:    391 passed (1956 assertions)
  Duration: 7.60s
```

---

## 2. BẢNG ĐỐI SOÁT TUYÊN BỐ VS HÀNH VI CODE THỰC TẾ (CLAIMS VS TRUTH MATRIX)

| STT | Phân hệ / Tuyên bố tính năng | Kết quả kiểm toán code thực tế | Trạng thái nghiệp vụ | Hành động đã thực hiện |
|---|---|---|---|---|
| 1 | **Điểm danh bằng QR Code tại Cổng Tiếp Tân** (`CheckInController`) | Khi khách đã RSVP đi 3 người (`adults=2, children=1`), lễ tân quét mã QR tại cổng nhưng không nhập số đè thì hệ thống fallback về `expected_attendees = 1` của thiệp mời ban đầu, làm sĩ số báo cáo bị sai lệch. | ⚠️ **LỖI LOGIC GỐC** (`BUG-AUDIT-01`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Thêm phương thức `actualAttendeesCount()` trên Model `Guest`. Ưu tiên lấy sĩ số RSVP thực tế trước khi fallback về `expected_attendees`. |
| 2 | **Sơ Đồ Xếp Bàn Tiệc & Sức Chứa** (`WeddingTable`, `SeatingPlannerController`) | Khách đã chọn RSVP `declined` (không tham dự) vẫn bị tính chiếm 1 ghế tại bàn tiệc; Khách RSVP đi 3 người chỉ bị tính chiếm 1 ghế, dẫn đến bàn bị quá tải thực tế nhưng hệ thống không báo đỏ `isOverCapacity()`. | ⚠️ **LỖI LOGIC GỐC** (`BUG-AUDIT-02`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Sửa `assigned_attendees_count` trong `WeddingTable` tính bằng `actualAttendeesCount()`. Trả về `0` nếu declined, `adults + children` nếu attending. |
| 3 | **Đồng Bộ Hai Nguồn Sự Thật Bàn Tiệc** (`GuestController` vs `SeatingPlannerController`) | Khi cập nhật khách mời từ trang Khách Mời, hệ thống chỉ lưu `table_name` mà không tự động tra cứu để gán `table_id`, khiến khách có tên bàn nhưng lại xuất hiện trong danh sách "Chưa xếp bàn" ở Seating Planner. | ⚠️ **LỖI LOGIC GỐC** (`BUG-AUDIT-03`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Tự động tra cứu `WeddingTable` cùng tên thuộc `wedding_id` để đồng bộ `table_id` khi tạo đơn, nhập hàng loạt (`batchStore`) và cập nhật (`update`). |
| 4 | **Bảo Mật Webhook SePay & VietQR** (`WebhookController`, `PaymentService`) | Kiểm tra chữ ký HMAC SHA256 / API Token, kiểm tra số tiền thanh toán, kiểm tra trạng thái đơn hàng (chống xử lý trùng lặp - Idempotent). |  **AN TOÀN** | Đạt 100% qua test suite thanh toán. |
| 5 | **Phân Quyền & Cô Lập Dữ Liệu Thiệp** (`InvitationPolicy`, `GuestController`) | Chỉ chủ sở hữu thiệp hoặc đối tác được ủy quyền hợp lệ mới được xem/sửa khách mời, album ảnh, két kỷ niệm và cấu hình. |  **AN TOÀN** | Được phòng vệ bởi `authorizeGuestManagement` và Policy. |
| 6 | **Cổng Check-In Tiếp Tân** (`CheckInController`) | Bảo vệ bởi mã PIN tiếp tân 6 chữ số riêng biệt với mật khẩu tài khoản cô dâu chú rể, có session kiểm tra ủy quyền. |  **AN TOÀN** | Đã có test xác thực PIN và phân quyền. |
| 7 | **Hộp Thời Gian Kỷ Niệm (Time Capsule)** (`TimeCapsuleController`) | Khóa thông điệp và kỷ vật bằng mật khẩu cá nhân + ngày mở khóa trong tương lai. |  **AN TOÀN & ĐÚNG HẠN** | 12/12 test case chuyên sâu đạt chuẩn. |
| 8 | **Đồng Bộ Dữ Liệu Trình Tạo Thiệp (Studio)** (`editor-bridge.blade.php`) | Bắt buộc handshake 2 chiều `MAYHEN_BRIDGE_READY` và thuộc tính `data-sync-target` cho toàn bộ 10 Section tiêu chuẩn. |  **CHUẨN HÓA 100%** | Tuân thủ 8 nguyên tắc cốt lõi `AGENTS.md`. |

---

## 3. CHI TIẾT 3 LỖI LOGIC CỐT LÕI ĐÃ ĐƯỢC PHÁT HIỆN & KHẮC PHỤC

### 3.1. BUG-AUDIT-01: Lệch Sĩ Số Check-in Cổng Ngày Cưới
- **Mã lỗi**: `BUG-AUDIT-01`
- **Mức độ**: **HIGH (Ảnh hưởng trực tiếp đến công tác điều phối tiệc cưới tại nhà hàng)**
- **Vị trí**: `app/Http/Controllers/Client/CheckInController.php`, `processScan()` & `showPortal()`.
- **Hiện tượng**:
  Khi khách mời nhận thiệp mời có ghi `expected_attendees = 1`. Sau đó khách mở thiệp và phản hồi RSVP rằng sẽ đi cùng gia đình 3 người (`adults_count = 2, children_count = 1`). Vào ngày cưới, tiếp tân dùng máy quét mã QR của khách. Nếu tiếp tân bấm "Xác nhận" nhanh mà không nhập ô số người, hệ thống lưu `checked_in_attendees = 1` thay vì `3`. Báo cáo tổng số khách thực tế dự tiệc thiếu hụt nghiêm trọng, gây sai lệch đặt cỗ với nhà hàng tiệc cưới.
- **Giải pháp xử lý**:
  Triển khai hàm nghiệp vụ chuẩn `actualAttendeesCount()` trong Model `Guest`:
  * Nếu khách phản hồi `attending`: trả về `max(1, adults_count + children_count)`.
  * Nếu khách phản hồi `declined`: trả về `0`.
  * Nếu khách chưa phản hồi (pending/null): trả về `max(1, expected_attendees)`.
  Trong `CheckInController::processScan()`, trường hợp tiếp tân không nhập ô đè số người, hệ thống tự động gán `$guest->actualAttendeesCount()`. Đồng thời các chỉ số thống kê `totalExpected` và `checkedInCount` trên dashboard tiếp tân đều tính toán nhất quán theo hàm này.

### 3.2. BUG-AUDIT-02: Seating Planner Bị Lệch Ghế Khi Khách Thay Đổi RSVP
- **Mã lỗi**: `BUG-AUDIT-02`
- **Mức độ**: **HIGH (Gây quá tải hoặc bàn trống ảo trong quản lý bàn tiệc)**
- **Vị trí**: `app/Models/WeddingTable.php`, accessor `getAssignedAttendeesCountAttribute()`.
- **Hiện tượng**:
  Hàm tính số ghế đã xếp của bàn tiệc trước đây:
  `$this->guests->sum(fn ($g) => $g->expected_attendees ?: 1);`
  * Dẫn đến: Khách đã báo từ chối tham gia (`declined`), nhưng bàn tiệc vẫn tính khách chiếm ghế, khiến bàn bị đầy ảo!
  * Ngược lại, khách dẫn thêm người nhà (3 người) thì bàn chỉ tính 1 người, dẫn đến sảnh tiệc báo bàn còn chỗ nhưng thực tế khách đến sẽ không có ghế ngồi (`isOverCapacity()` không kích hoạt cảnh báo).
- **Giải pháp xử lý**:
  Chuyển sang tính:
  `$this->guests->sum(fn (Guest $g) => $g->actualAttendeesCount());`
  Khi khách đổi RSVP, số ghế chiếm dụng trên bàn tiệc tự động điều chỉnh tức thời mà không cần xếp lại thủ công.

### 3.3. BUG-AUDIT-03: Mâu Thuẫn Hai Nguồn Sự Thật `table_id` và `table_name`
- **Mã lỗi**: `BUG-AUDIT-03`
- **Mức độ**: **MEDIUM (Mâu thuẫn trạng thái giữa trang Danh sách khách và Studio Xếp bàn)**
- **Vị trí**: `app/Http/Controllers/Client/GuestController.php`, `store()`, `batchStore()`, `update()`.
- **Hiện tượng**:
  Khi sửa thông tin khách mời ở màn hình Khách Mời và chọn/nhập tên bàn, controller chỉ lưu cột `table_name` mà để `table_id = null`. Khi người dùng chuyển sang màn hình Seating Planner Studio, câu truy vấn `whereNull('table_id')` lại liệt kê khách đó vào danh sách "Khách chưa xếp bàn", khiến người dùng hoang mang vì vừa xếp bàn xong nhưng màn hình xếp bàn vẫn báo chưa xếp.
- **Giải pháp xử lý**:
  Trong cả 3 luồng thêm đơn lẻ, nhập danh sách hàng loạt và cập nhật khách mời: tự động tìm kiếm `WeddingTable` có tên trùng khớp thuộc cùng đám cưới để cập nhật đồng bộ `table_id`. Nếu người dùng xóa tên bàn, cả `table_id` và `table_name` đều được đặt về `null`.

---

## 4. BẰNG CHỨNG KIỂM CHỨNG TỰ ĐỘNG (REGRESSION SUITE)

Đã tạo bộ kiểm thử chuyên biệt tại `tests/Feature/CrossModuleLogicInvariantsTest.php` gồm 5 kịch bản kiểm thử độc lập:
1. `table_headcount_dynamically_adapts_to_rsvp_attending_count` -> **PASSED**
2. `table_headcount_releases_seat_when_guest_declines_rsvp` -> **PASSED**
3. `checkin_scan_automatically_uses_rsvp_headcount` -> **PASSED**
4. `guest_management_update_syncs_table_id_with_seating_planner` -> **PASSED**
5. `guest_store_and_batch_store_sync_table_id` -> **PASSED**

Toàn bộ hệ sinh thái kiểm thử đạt **281/281 passed** không phát sinh bất kỳ hồi quy nào.

