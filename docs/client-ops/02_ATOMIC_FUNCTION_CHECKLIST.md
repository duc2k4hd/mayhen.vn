# 02 — ATOMIC FUNCTION CHECKLIST (KIỂM KÊ NGUYÊN TỬ TOÀN BỘ CHỨC NĂNG CLIENT OPS)

> **Quy ước trạng thái:**
> - `PASS` — Hoạt động đúng logic, quyền, bảo mật và giao diện trên cả Desktop & Mobile (có kiểm thử tự động & ảnh chụp chứng minh).
> - `FAIL` / `DEGRADED` — Trạng thái phát hiện ở vòng kiểm toán gốc `W00` trước khi khắc phục.

---

## 1. Cổng Tài Khoản & Quản Lý Thiệp (`/tai-khoan*`)

| Mã | Màn hình | Chức năng nguyên tử | Nguồn code thực tế | Trạng thái W00 | Trạng thái sau sửa (W09) |
| :--- | :--- | :--- | :--- | :---: | :---: |
| `ACC-01` | `/tai-khoan` | Hiển thị 4 thẻ KPI tổng quan đúng đơn vị (Thiệp, Suất dự kiến kèm lời mời, Người xác nhận Lớn/Trẻ, Lời chúc) | `AccountController@index`, `WeddingGuestMetricsService`, `dashboard.blade.php` | `FAIL` | **`PASS`** |
| `ACC-02` | `/tai-khoan` | Chuyển đổi thiệp đang xem khi tài khoản có >= 2 thiệp (`?invitation_id=`) | `AccountController@index`, `dashboard.blade.php` | `DEGRADED` | **`PASS`** |
| `ACC-03` | `/tai-khoan` | Sao chép URL thiệp cưới công khai (an toàn cả HTTP/WebView, giữ nguyên SVG icon) | `dashboard.blade.php`, `account-readiness.js` | `DEGRADED` | **`PASS`** |
| `ACC-04` | `/tai-khoan` | Bật / Tạm dừng xuất bản thiệp đang live có hộp thoại xác nhận rõ ràng | `AccountController@togglePublish`, `dashboard.blade.php`, `invitations.blade.php` | `DEGRADED` | **`PASS`** |
| `ACC-05` | `/tai-khoan` | Mở Modal QR & Tải ảnh QR 1024px / Thẻ in 1080×1350 | `qr-modal.blade.php`, `account-qr.js` | `PASS` | **`PASS`** |
| `ACC-06` | `/tai-khoan/thiep` | Hiển thị danh sách thiệp gọn gàng (2 nút chính + 6 công cụ vận hành + ngăn mở rộng 35 công cụ) | `invitations.blade.php`, `account.css` | `DEGRADED` | **`PASS`** |
| `ACC-07` | `/tai-khoan/thiep` | Mở Modal "Xem 7 bước sẵn sàng ngày cưới" (`Readiness Modal`) hiển thị đầy đủ & chống XSS | `readiness-modal.blade.php`, `account-readiness.js` | `FAIL` | **`PASS`** |
| `ACC-08` | `/tai-khoan/thiep` | Khôi phục bản xuất bản trước (`Rollback`) & Xuất bản bản nháp không vượt khóa `is_locked` của Admin | `AccountController@rollback`, `PublishDiffController` | `FAIL` | **`PASS`** |
| `ACC-09` | `/tai-khoan/thiep` | Xóa thiệp vĩnh viễn dọn sạch `Wedding` mồ côi, không làm lệch KPI Tổng quan | `AccountController@destroy` | `FAIL` | **`PASS`** |
| `ACC-10` | `/tai-khoan/thiep/{id}/cai-dat` | Lưu slug, mật khẩu thiệp, PIN lễ tân, hạn RSVP, đỗ xe, kiểm duyệt lời chúc đồng bộ tới Concierge | `AccountController@updateSettings`, `guest-concierge.blade.php` | `DEGRADED` | **`PASS`** |

---

## 2. Quản Lý Khách Mời & RSVP (`/tai-khoan/thiep/{id}/khach-moi`)

| Mã | Màn hình | Chức năng nguyên tử | Nguồn code thực tế | Trạng thái W00 | Trạng thái sau sửa (W09) |
| :--- | :--- | :--- | :--- | :---: | :---: |
| `CRM-01` | `/khach-moi` | Hiển thị Header & Thanh công cụ không tràn ngang trên Desktop, gọn gàng trên Mobile (`320px`–`390px`) | `guests.blade.php`, `guests.css` | `FAIL` | **`PASS`** |
| `CRM-02` | `/khach-moi` | Hiển thị 6 thẻ KPI tách bạch rõ: Số lời mời, Suất dự kiến, Người xác nhận (Lớn/Trẻ), Báo bận/Chờ trả lời, Đã gửi/Đã mở, Đã điểm danh; Tỷ lệ phản hồi `0–100%` | `GuestController@index`, `WeddingGuestMetricsService`, `guests.blade.php` | `FAIL` | **`PASS`** |
| `CRM-03` | `/khach-moi` | Thêm 1 khách mời mới (lọc thẻ HTML, kiểm tra bàn tiệc & `event_ids` cùng đám cưới) | `GuestController@store`, `Guest.php` | `DEGRADED` | **`PASS`** |
| `CRM-04` | `/khach-moi` | Nhập hàng loạt bằng dán văn bản (`Batch Paste`) kèm lọc ngôn từ & chuẩn hóa thẻ HTML | `GuestController@batchStore` | `DEGRADED` | **`PASS`** |
| `CRM-05` | `/khach-moi` | Nhập danh sách từ CSV/Excel (hỗ trợ ô xuống dòng, ưu tiên nhận diện cột Bàn tiệc trước Tên khách, hoàn tác lô nhập) | `GuestController@importCsv`, `undoImportBatch` | `FAIL` | **`PASS`** |
| `CRM-06` | `/khach-moi` | Tìm kiếm & Lọc theo Nhóm / Nhà Trai-Gái / Bàn / Trạng thái + Phân trang 100 khách/trang giữ query string | `GuestController@index`, `guests.blade.php` | `DEGRADED` | **`PASS`** |
| `CRM-07` | `/khach-moi` | Sửa thông tin khách mời (`Edit Modal`) an toàn | `GuestController@update`, `guests.js` | `DEGRADED` | **`PASS`** |
| `CRM-08` | `/khach-moi` | Xóa khách mời xóa sạch mọi `responses()`, tách `guest_id = null` trên ảnh/lời chúc để không mất kỷ niệm | `GuestController@destroy`, `Guest.php` | `DEGRADED` | **`PASS`** |
| `CRM-09` | `/khach-moi` | Chủ thiệp cập nhật RSVP hộ khách (`Manual RSVP`) chặn `0+0` khi `attending`, giữ lời chúc cũ, ghi `GuestResponseLog` & `AuditLogger` | `GuestController@manualRsvp` | `FAIL` | **`PASS`** |
| `CRM-10` | `/khach-moi` | Soạn lời mời Zalo cá nhân hóa, sao chép lời mời / link riêng, theo dõi & đánh dấu đã gửi trung thực | `guests.blade.php`, `guests.js`, `InvitationDeliveryController`, `ZaloShareController` | `FAIL` | **`PASS`** |
| `CRM-11` | `/khach-moi` | Mở 4 Modal: Soát lỗi trước khi gửi, Theo dõi gửi thiệp, Nhắc nhở tế nhị, Lời cảm ơn sau cưới (hiển thị mượt & chống DOM XSS) | `guests.blade.php`, `guests.css`, `guests.js` | `FAIL` | **`PASS`** |
| `CRM-12` | `/khach-moi` | Thu hồi link cũ & cấp lại `guest_token` mới (`410 Gone` trên link cũ) | `GuestController@regenerateToken`, `InvitationEditorController@show` | `PASS` | **`PASS`** |
| `CRM-13` | `/loc-trung-khach` | Quét khách trùng SĐT/tên & Gộp 2 khách an toàn (chống IDOR `table_id`, không nhân đôi RSVP, giữ điểm danh, chống XSS/CSV Injection) | `DuplicateGuestController`, `DuplicateGuestDetectorService` | `FAIL` | **`PASS`** |
| `CRM-14` | `/ho-gia-dinh` | Xuất CSV & Bản in Báo Bếp Trưởng loại trừ chính xác khách đã từ chối (`declined`) và bọc `CsvSanitizerService` | `HouseholdRsvpService`, `household/print.blade.php` | `FAIL` | **`PASS`** |

---

## 3. Sơ Đồ Bàn Tiệc, Điểm Danh QR & Điều Phối Ngày Cưới

| Mã | Màn hình | Chức năng nguyên tử | Nguồn code thực tế | Trạng thái W00 | Trạng thái sau sửa (W09) |
| :--- | :--- | :--- | :--- | :---: | :---: |
| `OPS-01` | `/so-do-ban-tiec` | Hiển thị số suất khách trong bàn & ngăn kéo đồng bộ `actualAttendeesCount()`, hiển thị chuẩn dấu tiếng Việt tiêu đề | `seating-planner.blade.php`, `seating-planner.css` | `FAIL` | **`PASS`** |
| `OPS-02` | `/so-do-ban-tiec` | Xếp khách vào bàn trên Điện thoại / cảm ứng bằng ô chọn 1-chạm song song với kéo-thả Desktop | `seating-planner.blade.php`, `seating-planner.js` | `FAIL` | **`PASS`** |
| `OPS-03` | `/so-do-ban-tiec` | Xóa bàn tiệc dọn sạch cả `table_id` lẫn `table_name` còn sót | `SeatingPlannerController@deleteTable` | `DEGRADED` | **`PASS`** |
| `OPS-04` | `/si-so-ban-tiec` | Xem sĩ số lấp đầy bàn tiệc & màn hình điều phối sảnh tiệc (sửa SQL 500, trừ khách `declined`, bắt buộc chủ thiệp/PIN) | `TableCapacityWaveController`, `TableCapacityWaveService` | `FAIL` | **`PASS`** |
| `OPS-05` | `/goi-ngoai-tuyen` | Tải gói điểm danh ngoại tuyến HTML/JSON khi sảnh mất mạng (sửa SQL 500 `display_order` & escape XSS/CSV) | `EmergencyOfflinePackController`, `EmergencyOfflinePackService` | `FAIL` | **`PASS`** |
| `OPS-06` | `/diem-danh-tiec/{slug}` | Đồng bộ 100% một Mã PIN Lễ Tân 6 số duy nhất trên Cài đặt, Điều phối, Bộ dự phòng và Cổng điểm danh | `Invitation@ensureReceptionistPin`, `CheckInController`, `LiveCoordinationController`, `AccountController` | `FAIL` | **`PASS`** |
| `OPS-07` | `/diem-danh-tiec/{slug}` | Quét QR, Tìm nhanh khách, Hoàn tác điểm danh (cập nhật ngay bộ đếm, chống DOM XSS, báo rõ khi thiếu BarcodeDetector, ẩn `bottom-nav`) | `CheckInController`, `checkin.blade.php`, `checkin.js`, `master.blade.php` | `FAIL` | **`PASS`** |
| `OPS-08` | `/khach-moi/{guestToken}` | Thẻ VIP Concierge & tải lịch `.ics` kiểm tra `is_published` / `isExpired`, hiển thị đúng lời nhắn nhóm & hướng dẫn đỗ xe | `CheckInController@guestConcierge/downloadIcs`, `guest-concierge.blade.php` | `FAIL` | **`PASS`** |

---

## 4. Thiệp Công Khai, Quyền Riêng Tư, Lưu Bút, Tour & Module Mở Rộng

| Mã | Màn hình | Chức năng nguyên tử | Nguồn code thực tế | Trạng thái W00 | Trạng thái sau sửa (W09) |
| :--- | :--- | :--- | :--- | :---: | :---: |
| `PUB-01` | `/tao-thiep` (`store`) | Chủ thiệp ẩn danh (`owned_invitation_slugs`) lưu cập nhật thiệp hợp lệ không bị `403` | `InvitationEditorController@store` | `FAIL` | **`PASS`** |
| `PUB-02` | `/thiep/{slug}?token=` | Không lộ `?token=` vào thẻ `og:url` và cấu hình `Referrer-Policy: strict-origin-when-cross-origin` | `InvitationEditorController@show`, `layouts/invitation.blade.php`, `public-invitation.blade.php` | `FAIL` | **`PASS`** |
| `PUB-03` | `/thiep/{slug}` & `/trinh-chieu` | Lời chúc riêng tư (`is_private = true`) tuyệt đối không lộ công khai trên Thiệp, Live Wall, Tranh ký tên, Kỷ niệm số hay khi bấm "Duyệt tất cả" | `InvitationEditorController`, `WishManagementController`, `LiveWallController`, `MemoryVaultController`, `SignatureArchiveService` | `FAIL` | **`PASS`** |
| `PUB-04` | Public APIs (`/ky-ten`, `/nhan-qua`, `/tra-cuu-ban-tiec`, `/events`) | Kiểm tra chặt `is_published` / `is_locked` / `isExpired`, whitelist ảnh chữ ký `png|jpe?g|webp <= 1MB`, chống dò SĐT 2 số | `WeddingInteractiveEndingService`, `GuestTableFinderController`, `WeddingGiftRegistryController`, `WeddingEventController` | `FAIL` | **`PASS`** |
| `PUB-05` | `/quyen-rieng-tu` & `/ky-niem` | Thực thi thật các cấu hình Quyền riêng tư (`anonymous_rsvp_allowed`, `bank_visibility`, `wishes_visibility`, `photo_drop_moderation`) & xóa sạch PII | `InvitationEditorController`, `GuestController`, `PhotoDropController`, `MemoryVaultController` | `FAIL` | **`PASS`** |
| `PUB-06` | `/ke-hoach-cuoi` & `/man-hinh-chao` | Tải đầy đủ CSS/JS trang Kế hoạch cưới; nút "Mô phỏng" Kiosk & Bác sĩ chất lượng không ghi đè dữ liệu khách thật | `planner/index.blade.php`, `VenueWelcomeKioskService`, `QualityDoctorController` | `FAIL` | **`PASS`** |
| `TOUR-01` | `/tai-khoan` & `/khach-moi` | Interactive Product Tour A (`account_overview`, 7 bước) & Tour B (`guest_crm`, 8 bước) lướt spotlight mượt, độc lập hoàn toàn với `/tao-thiep`, an toàn dữ liệu 100% | `client-ops-tour.js`, `client-ops-tour.css`, `layout.blade.php`, `guests.blade.php` | Chưa có | **`PASS`** |
