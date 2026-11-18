# 07 — TEST MATRIX & EVIDENCE (MA TRẬN KIỂM THỬ & BẰNG CHỨNG XÁC THỰC)

## 1. Bộ Kiểm Thử Hồi Quy Tự Động Mới (Pest Feature & JS Unit Tests)

| Tệp kiểm thử | Số bài test | Phạm vi nghiệp vụ & lỗi bảo vệ (`BUG-OPS-*`) | Kết quả |
| :--- | :---: | :--- | :---: |
| `tests/Feature/ClientOpsGuestHeadcountAndIntegrityTest.php` | 7 | `WeddingGuestMetricsService` (tách biệt Lời mời / Suất dự kiến / Người xác nhận đa sự kiện / Walk-in / Tỷ lệ `<=100%`), `manualRsvp` (`0+0` 422, giữ lời chúc cũ, ghi `GuestResponseLog`), `submitRsvp` (`event_id` chéo đám cưới 422, reset `0` khi `declined`), `Guest::deleting` bảo toàn ảnh kỷ niệm, CSV multiline + ưu tiên cột `"Tên Bàn Tiệc"`, Báo bếp loại trừ `declined` + `CsvSanitizerService`, Gộp khách trùng chống IDOR `table_id` & nhân đôi RSVP, `InvitationDeliveryController@preview` đúng tham số | **PASS** |
| `tests/Feature/ClientOpsVenueAndCheckInIntegrityTest.php` | 5 | Đồng bộ 1 mã PIN lễ tân 6 số duy nhất (`CheckInController`, `LiveCoordinationController`, `resilienceKit`), `TableCapacityWaveService` sửa SQL 500 `checked_in_at` + khóa PIN màn hình sảnh tiệc, `EmergencyOfflinePackService` sửa SQL 500 `display_order` + escape XSS/CSV, `CheckInController` (`guestConcierge`/`downloadIcs` chặn thiệp nháp, `convertDeclined` reset `children_count`, `undo` trả `stats` mới), `SeatingPlanner` xóa bàn dọn `table_name` + 1-chạm mobile select | **PASS** |
| `tests/Feature/ClientOpsAccountPrivacyAndModulesTest.php` | 5 | Dashboard `/tai-khoan` không cộng dồn `Wedding` mồ côi khi xóa thiệp, chặn lách khóa `is_locked` qua `rollback` & `PublishDiffController`, chống lộ `?token=` qua `og:url` + `Referrer-Policy`, bảo mật tuyệt đối lời chúc `is_private = true` (kể cả khi bấm `Approve All` / Mosaic / Live Wall / Memory Vault), whitelist ảnh chữ ký `png|jpe?g|webp <= 1MB` (chặn `.svg`/`.php`), thực thi `anonymous_rsvp_allowed` + xóa sạch PII, khôi phục CSS/JS `planner`, mô phỏng Kiosk & Bác sĩ chất lượng không ghi đè DB thật | **PASS** |
| `tests/Feature/ClientOpsProductTourTest.php` | 3 | Render đầy đủ 7 anchor thực tế cho Tour A (`/tai-khoan` rỗng & có dữ liệu) và 8 anchor cho Tour B (`/khach-moi`), cô lập 100% khỏi tài nguyên Studio Tour (`/tao-thiep`), kiểm thử ES Module bằng Node.js cho khóa lưu trữ riêng từng user/tour, không lưu PII và khởi tạo `ClientOpsProductTour` an toàn | **PASS** |

---

## 2. Kết Quả Chạy Liên Thông 38 Bộ Test Hồi Quy Dự Án
- **Nhóm 26 bộ test Client Ops, Account, Guest CRM, Seating, Check-in, Concierge, OpenGraph, Privacy & Gate 0–1**: **`196 passed, 0 failed` (`1.159 assertions`)**.
- **Nhóm 13 bộ test mở rộng (CSV Formula Defense, Pre-Send, Reminder, Thank-You, Readiness, Multi-Event RSVP, Gate 2–4, Docs Center, Studio Visual Editor V3)**: **`82 passed, 0 failed` (`825 assertions`)**.
- **Định dạng mã nguồn PHP (`vendor/bin/pint --dirty --format agent`)**: **`passed`**.

---

## 3. Bằng Chứng Trình Duyệt Đa Khung Hình (Headless Chromium CDP — `W00 Before` vs `W08 After`)

### 3.1. Ảnh Chụp Hiện Trạng Ban Đầu (`docs/client-ops/qa/w00-before/`)
- `01-account-dashboard-empty-desktop.png` -> `15-planner-broken-before-desktop.png` (15 ảnh xác thực thực tế ở `1440×900` và `390×844` ghi lại lỗi tràn 15 nút `/khach-moi`, 41 nút `/tai-khoan/thiep`, `bottom-nav` đè giao diện, lỗi font dấu tiếng Việt `Seating Planner` và mất CSS trang `Kế hoạch cưới`).

### 3.2. Ảnh Chụp & Kiểm Thử Tương Tác Sau Hoàn Thiện (`docs/client-ops/qa/w08-after/`)
Toàn bộ **19 ảnh chụp & 19 chốt kiểm định tự động trên trình duyệt thật** đạt `PASS` với **0 lỗi Console / Uncaught JS Exception**:
1. `01-account-empty-tour-a-desktop.png` & `02-account-empty-clean-desktop.png`: Tour A tự mở mượt trên tài khoản mới chưa có thiệp, thích ứng trạng thái rỗng.
2. `03-account-populated-tour-a-step1-desktop.png` & `04-account-populated-tour-a-step3-glide-desktop.png`: Tour A lướt spotlight êm giữa các bước, hiển thị thanh chọn thiệp đa đám cưới, không còn `bottom-nav` che khuất.
3. `05-account-dashboard-after-desktop.png` & `06-account-dashboard-after-mobile.png`: Ghi nhớ trạng thái sau khi bấm *Bỏ qua hướng dẫn* (F5 không tự bật lại), không tràn ngang trên Mobile `390×844`.
4. `07-account-invitations-compact-desktop.png` & `08-account-readiness-modal-open-desktop.png`: Thẻ thiệp gọn gàng và modal **Độ Sẵn Sàng 7 Bước** mở hiển thị sắc nét.
5. `09-guests-crm-tour-b-step1-desktop.png`, `10-guests-crm-tour-b-step2-kpis-desktop.png`, `11-guests-crm-clean-after-desktop.png`: Header `/khach-moi` thoáng đẹp, 6 thẻ KPI rõ đơn vị, Tour B hoạt động chuẩn xác.
6. `12-guests-presend-modal-open-desktop.png`: Xác nhận cả 4 modal (`Soát lỗi`, `Theo dõi gửi`, `Nhắc nhở`, `Lời cảm ơn`) mở hiển thị đầy đủ trên trình duyệt thật.
7. `13-guests-crm-clean-after-mobile.png` & `14-guests-crm-tour-b-mobile.png`: Giao diện `/khach-moi` và hộp thoại Tour B vừa khít màn hình di động `390×844` (và kiểm tra ép hẹp `320×568` đạt `overflowX = 0`).
8. `15-seating-planner-after-desktop.png` & `16-seating-planner-after-mobile.png`: Sửa dứt điểm lỗi dấu tiếng Việt tiêu đề và bổ sung ô chọn bàn 1-chạm trên di động.
9. `17-checkin-portal-after-desktop.png` & `18-guest-concierge-after-mobile.png`: Cổng điểm danh QR sạch thoáng và thẻ VIP Concierge hiển thị đầy đủ lời ngỏ nhóm + chỉ dẫn gửi xe.
10. `19-wedding-planner-restored-desktop.png`: Khôi phục hoàn chỉnh giao diện bảng kế hoạch & ngân sách cưới.
