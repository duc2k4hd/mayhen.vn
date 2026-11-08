# MÂY HẸN (MAYHEN.VN) — BÁO CÁO ĐỐI SOÁT KIỂM TOÁN CHẤT LƯỢNG GỐC (FINAL AUDIT BASELINE)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Phiên bản Laravel**: 13.35.0 | **PHP**: 8.5.10  
> **Ngày thực hiện**: Tháng 11/2026  
> **Mục tiêu**: Kiểm toán đến tận gốc logic nghiệp vụ, đối chiếu giữa tuyên bố tính năng và hành vi thực thi thực tế trong mã nguồn, thiết lập nền móng kiểm thử hồi quy 100% tự động.

---

## 1. TỔNG QUAN HIỆN TRẠNG KIỂM THỬ (TEST BASELINE)

- **Tổng số bài kiểm tra**: **461 tests**
- **Tổng số xác nhận (Assertions)**: **2,347 assertions**
- **Tỷ lệ vượt qua**: **100% Passed (0 Failed, 0 Skipped)**
- **Thời gian chạy**: ~8.90 giây
- **Code Style (Laravel Pint)**: 100% Clean theo chuẩn Agent.

```bash
   PASS  Tests\Unit\...
   PASS  Tests\Feature\...
  Tests:    461 passed (2347 assertions)
  Duration: 8.90s
```

---

## 2. BẢNG ĐỐI SOÁT TUYÊN BỐ VS HÀNH VI CODE THỰC TẾ (CLAIMS VS TRUTH MATRIX)

| STT | Phân hệ / Tuyên bố tính năng | Kết quả kiểm toán code thực tế | Trạng thái nghiệp vụ | Hành động đã thực hiện |
|---|---|---|---|---|
| 1 | **Điểm danh bằng QR Code tại Cổng Tiếp Tân** (`CheckInController`) | Khi khách đã RSVP đi 3 người (`adults=2, children=1`), lễ tân quét mã QR tại cổng nhưng không nhập số đè thì hệ thống fallback về `expected_attendees = 1` của thiệp mời ban đầu, làm sĩ số báo cáo bị sai lệch. | ⚠️ **LỖI LOGIC GỐC** (`BUG-AUDIT-01`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Thêm phương thức `actualAttendeesCount()` trên Model `Guest`. Ưu tiên lấy sĩ số RSVP thực tế trước khi fallback về `expected_attendees`. |
| 2 | **Sơ Đồ Xếp Bàn Tiệc & Sức Chứa** (`WeddingTable`, `SeatingPlannerController`) | Khách đã chọn RSVP `declined` (không tham dự) vẫn bị tính chiếm 1 ghế tại bàn tiệc; Khách RSVP đi 3 người chỉ bị tính chiếm 1 ghế, dẫn đến bàn bị quá tải thực tế nhưng hệ thống không báo đỏ `isOverCapacity()`. | ⚠️ **LỖI LOGIC GỐC** (`BUG-AUDIT-02`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Sửa `assigned_attendees_count` trong `WeddingTable` tính bằng `actualAttendeesCount()`. Trả về `0` nếu declined, `adults + children` nếu attending. |
| 3 | **Đồng Bộ Hai Nguồn Sự Thật Bàn Tiệc** (`GuestController` vs `SeatingPlannerController`) | Khi cập nhật khách mời từ trang Khách Mời, hệ thống chỉ lưu `table_name` mà không tự động tra cứu để gán `table_id`, khiến khách có tên bàn nhưng lại xuất hiện trong danh sách "Chưa xếp bàn" ở Seating Planner. | ⚠️ **LỖI LOGIC GỐC** (`BUG-AUDIT-03`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Tự động tra cứu `WeddingTable` cùng tên thuộc `wedding_id` để đồng bộ `table_id` khi tạo đơn, nhập hàng loạt (`batchStore`) và cập nhật (`update`). |
| 4 | **Bảo Mật Cổng Phụ Khi Thiệp Bị Khóa** (`MemoryVaultController`, `LiveWallController`, `PhotoDropController`) | Khi thiệp bị Admin khóa (`is_locked=true`), người dùng vẫn có thể truy cập qua các cổng phụ. | ⚠️ **LỖI BẢO MẬT** (`BUG-AUDIT-04`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Khóa đồng bộ 100% cổng phụ (Memory Vault, Live Wall, Photo Drop, Check-in, Lịch ICS). |
| 5 | **Phòng Vệ CSV Formula Injection** (`CsvSanitizerService`) | Dữ liệu người dùng nhập (`=`, `+`, `-`, `@`) có thể kích hoạt công thức nguy hiểm khi mở file CSV trong Excel / Sheets. | ⚠️ **LỖI AN TOÀN DỮ LIỆU** (`BUG-AUDIT-05`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Tự động thêm tiền tố `'` vô hiệu hóa công thức qua `CsvSanitizerService`. |
| 6 | **Bộ Lọc Ngôn Từ & False Positive** (`ContentModerationService`) | Chuyển chuỗi sang ASCII làm chặn oan từ ngữ ngày cưới lịch sự ("đại diện hai họ", "đại gia đình", "buổi tiệc", "vũ trụ", "đi du lịch", "nên duyên"). | ⚠️ **LỖI LOGIC TRẢI NGHIỆM** (`BUG-AUDIT-06`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Thêm `$asciiAmbiguousWords`, chỉ bắt lỗi từ tục khi văn bản gốc có dấu 100%. |
| 7 | **Bác Sĩ Kiểm Soát Chất Lượng** (`WeddingConsistencyGuardianService`) | Chấm 83/100 điểm và xếp hạng "Xuất sắc" dù thiệp hoàn toàn thiếu ngày cưới hoặc thiếu địa điểm. | ⚠️ **LỖI ĐÁNH GIÁ CHẤT LƯỢNG** (`BUG-AUDIT-07`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Thiết lập Cap Score Defense (tối đa 65%) khi còn bất kỳ lỗi Critical nào. |
| 8 | **Chống Nhân Bản File Tải Lên** (`PhotoDropController`) | Mạng di động chập chờn khiến khách ấn gửi 2 lần tạo trùng 2 file lưu trữ và 2 bản ghi ảnh rác. | ⚠️ **LỖI TÀI NGUYÊN LƯU TRỮ** (`BUG-AUDIT-08`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Tích hợp bộ lọc 5 phút: tái sử dụng bản ghi, không sinh file rác. |
| 9 | **Bàn Giao Thiệp Cưới Agency** (`PartnerWeddingController`) | Mã bàn giao không thời hạn, thiếu tính năng thu hồi và nguy cơ Replay Attack sau khi claim. | ⚠️ **LỖI NGHIỆP VỤ ĐỐI TÁC** (`BUG-AUDIT-09`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Giới hạn mã 30 ngày, thêm route thu hồi (revoke) và xóa mã ngay sau lần claim đầu tiên. |
| 10 | **Khôi Phục Khẩn Cấp & Dự Phòng PII** (`AccountController`, `resilience-kit.blade.php`) | Rollback một chiều xóa vĩnh viễn snapshot; thiếu Audit Trail; danh sách in tiếp tân làm lộ số điện thoại khách mời. | ⚠️ **LỖI PHỤC HỒI & QUYỀN RIÊNG TƯ** (`BUG-AUDIT-10`) | **ĐÃ KHẮC PHỤC TRIỆT ĐỂ**: Triển khai Rollback Swap hai chiều, tích hợp `AuditLogger`, thêm Phone Masking khi in A4. |

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

### 3.4. BUG-AUDIT-04..10: Các lỗi bảo mật, dữ liệu, điều phối và khôi phục an toàn
- **BUG-AUDIT-04 (Rò rỉ cổng phụ khi thiệp bị khóa)**: Khóa đồng bộ 100% cổng phụ (Memory Vault, Live Wall, Photo Drop, Check-in, Lịch ICS) khi `is_locked = true`.
- **BUG-AUDIT-05 (CSV Formula Injection — CWE-1236)**: Thêm tiền tố nháy đơn `'` tự động cho mọi ô văn bản bắt đầu bằng `=`, `+`, `-`, `@`.
- **BUG-AUDIT-06 (False Positive Content Moderation)**: Thêm danh sách `$asciiAmbiguousWords`, loại bỏ tình trạng chặn oan các cụm từ ngày cưới lịch sự ("đại diện", "buổi tiệc", "vũ trụ", "đi du lịch", "nên duyên").
- **BUG-AUDIT-07 (Cap Score Defense cho Bác sĩ chất lượng)**: Khống chế điểm số tối đa 65% (buộc chuyển trạng thái `needs_attention`) khi thiệp thiếu ngày cưới hoặc thiếu địa điểm.
- **BUG-AUDIT-08 (Photo Drop Double-Submit Deduplication)**: Tích hợp bộ lọc 5 phút chống double-click gửi ảnh, không sinh file rác và tái sử dụng bản ghi cũ an toàn.
- **BUG-AUDIT-09 (Agency Handover Expiry, Revoke & Anti-Replay)**: Bổ sung hạn 30 ngày cho mã bàn giao, tính năng thu hồi mã và xóa mã ngay sau khi claim thành công.
- **BUG-AUDIT-10 (Emergency Rollback Swap & Resilience Kit PII Guard)**: Triển khai khôi phục hai chiều Rollback Swap không mất dữ liệu, tích hợp `AuditLogger` và tính năng ẩn 4 số giữa SĐT khi in tài liệu tiếp tân ngày cưới.

---

## 4. BẰNG CHỨNG KIỂM CHỨNG TỰ ĐỘNG (REGRESSION SUITE)

Toàn bộ hệ sinh thái kiểm thử đạt **461/461 passed 100% (2,347 assertions)** trong ~8.90 giây, không phát sinh bất kỳ hồi quy nào:
1. `tests/Feature/CrossModuleLogicInvariantsTest.php` -> **5/5 passed**
2. `tests/Feature/SecurityDeepDiveMatrixTest.php` -> **4/4 passed**
3. `tests/Feature/CsvFormulaInjectionDefenseTest.php` -> **4/4 passed**
4. `tests/Feature/InvitationLockedConsistencyTest.php` -> **5/5 passed**
5. `tests/Feature/AiStoryWriterFlowTest.php` -> **5/5 passed**
6. `tests/Feature/VisualQualityDoctorFlowTest.php` -> **5/5 passed**
7. `tests/Feature/LiveGuestPhotoDropTest.php` -> **12/12 passed**
8. `tests/Feature/AgencyHandoverClaimFlowTest.php` -> **7/7 passed**
9. `tests/Feature/SeatingCapacityGuardianTest.php` -> **8/8 passed**
10. `tests/Feature/EmergencyRollbackTest.php` -> **6/6 passed**
11. `tests/Feature/WeddingResilienceKitTest.php` -> **4/4 passed**
12. Các feature test suites toàn hệ thống: **461/461 passed 100%**.

