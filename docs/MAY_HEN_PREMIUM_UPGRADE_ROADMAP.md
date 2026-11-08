# MÂY HẸN (MAYHEN.VN) — LỘ TRÌNH NÂNG CẤP GIÁ TRỊ CAO CẤP CHUYÊN SÂU
# (PREMIUM VALUE UPGRADE ROADMAP)

> **Mục tiêu**: Chuyển hóa toàn bộ kết quả kiểm toán thành một lộ trình nâng cấp cụ thể, ưu tiên tối đa cho **Sự An Tâm Ngày Cưới (Wedding Confidence)**, **Trải Nghiệm Gia Đình (Family & Senior Respect)** và **Kỷ Niệm Bền Vững (Post-Wedding Keepsake)**.

---

## 1. NGUYÊN TẮC THI CÔNG BẮT BUỘC

1. **Chất lượng hơn số lượng**: Không làm thêm tính năng thừa thãi; chỉ làm những nâng cấp thực sự giải quyết nỗi lo của cô dâu chú rể và gia đình.
2. **Bảo tồn chuẩn mực kỹ thuật**: 100% Pure CSS/JS, tiền tố `mayhen_*`, zero framework/CDN bên ngoài.
3. **Bảo tồn mẫu thiệp cốt lõi**: Giữ nguyên vẹn 100% cấu trúc 4 mẫu thiệp cưới hiện có.
4. **Kiểm thử hồi quy 100%**: Mọi nâng cấp đều phải đi kèm Feature Test và Pint formatting.

---

## 2. CÁC CỘT MỐC TRIỂN KHAI CHI TIẾT (ACTIONABLE ROADMAP)

### CỘT MỐC 1: SỰ AN TÂM & CHU ĐÁO VỚI GIA ĐÌNH (WEDDING CONFIDENCE & FAMILY RESPECT) — [HOÀN TẤT 100%]

#### `TASK-VAL-01`: Chế Độ Tiếp Cận Dành Riêng Cho Người Lớn Tuổi (Senior-Friendly Guest Mode) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Thêm nút chuyển đổi font chữ lớn và giao diện tương phản cao trên thiệp cưới di động.
- **Phạm vi code**:
  * View component: `resources/views/client/components/senior-mode-widget.blade.php`.
  * Nhúng vào layout: `resources/views/client/layouts/invitation.blade.php` và `public-invitation.blade.php`.
  * Hành vi: Nút nổi [ 👓 Chữ Lớn ], phóng to font chữ 118%, tăng tương phản đen tuyền, dãn dòng 1.75, dừng hiệu ứng hoa rơi, Quick Bar gọi trực tiếp cô dâu chú rể và chỉ đường Google Maps 1 chạm, lưu `sessionStorage`.
- **Kiểm thử**: `tests/Feature/SeniorFriendlyGuestModeTest.php` (2/2 tests passed).

#### `TASK-VAL-02`: Bác Sĩ Đồng Bộ Thông Tin Trọng Yếu (Wedding Consistency Guardian) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Tự động so sánh chéo ngày giờ, sảnh tiệc giữa thiệp cưới, Google Maps và bảng tiếp tân.
- **Phạm vi code**:
  * Service: `app/Services/WeddingConsistencyGuardianService.php`.
  * Kiểm tra 6 chỉ số trọng yếu: Ngày cưới, Địa điểm sảnh tiệc, Tọa độ/Link Google Maps, Thông tin ngân hàng nhận mừng cưới, Thông tin phụ mẫu hai bên, Mã PIN tiếp tân an toàn.
  * API: `GET /api/editor/consistency-check/{id}` phân quyền an toàn `auth` và `isOwner()`.
- **Kiểm thử**: `tests/Feature/WeddingConsistencyGuardianTest.php` (3/3 tests passed).

#### `TASK-VAL-03`: Bộ Dự Phòng Ngày Cưới 1 Chạm (Wedding Day Resilience Kit) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Tạo trang tổng hợp in ấn chuẩn A4/A3 cho sảnh tiệc cưới (phòng trường hợp mất mạng Wi-Fi tại nhà hàng).
- **Phạm vi code**:
  * Route: `/tai-khoan/thiep/{id}/bo-du-phong-ngay-cuoi` (`account.invitations.resilience_kit`).
  * View: `resources/views/client/pages/account/resilience-kit.blade.php`.
  * Chức năng: Sơ đồ sức chứa bàn tiệc, Danh sách khách mời có sẵn ô tích điểm danh tay bằng bút, Thông số cứu hộ khẩn cấp và mã PIN tiếp tân. Tối ưu `@media print` ngắt trang A4 chuẩn xác.
  * Nút bấm: "Bộ Dự Phòng" trên `account/invitations.blade.php`.
- **Kiểm thử**: `tests/Feature/WeddingResilienceKitTest.php` (3/3 passed) & `tests/Feature/AccountInvitationsNavigationTest.php` (3/3 passed).

---

### CỘT MỐC 2: TỐI ƯU HÓA TRẢI NGHIỆM GIAO TIẾP & TỐC ĐỘ (SPEED & GUEST CONCIERGE) — [HOÀN TẤT 100%]

#### `TASK-VAL-04`: Tự Động Nén Ảnh Thông Minh Phía Trình Duyệt (Client-side Image Optimizer) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Tối ưu dung lượng ảnh tải lên (Photo Drop và Studio) từ 15MB xuống dưới 1MB trước khi gửi request.
- **Phạm vi code**:
  * File JS: `public/assets/client/js/common/image-optimizer.js`.
  * Tích hợp: `public/assets/client/js/pages/photo-drop.js`.
  * Sử dụng Native Canvas API của trình duyệt: Tự động resize ảnh vượt quá 2560px, chuyển đổi sang định dạng WebP/JPEG chất lượng 85%. Tiết kiệm 90% băng thông server và giúp khách mời gửi ảnh trong tiệc cưới cực nhanh dù 4G chập chờn.

#### `TASK-VAL-05`: Hướng Dẫn Vị Trí & Bãi Đỗ Xe Thông Minh (Interactive Parking Concierge) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Bổ sung mục hướng dẫn gửi xe và lối vào sảnh tiệc trong phần Sự kiện tiệc cưới.
- **Phạm vi code**:
  * Backend: `AccountController::updateSettings` lưu trữ cấu hình `custom_config.parking_guide` (vị trí đỗ ô tô, xe máy, vé xe/chi phí, lối thang máy lên sảnh, mẹo tìm đường).
  * UI Cài đặt: Khối 7 trong `resources/views/client/pages/account/settings.blade.php`.
  * UI Thiệp mời: Component `resources/views/client/components/parking-concierge-modal.blade.php` hiển thị nút bấm nổi trang trọng `[ 🚗 Bãi Đỗ Xe & Lối Vào ]` và modal chỉ dẫn tiện ích sang trọng Quiet Luxury. Tự động ẩn khi nhúng iframe Studio Editor.
- **Kiểm thử**: `tests/Feature/ParkingConciergeTest.php` (3/3 tests passed).

---

### CỘT MỐC 3: KỶ NIỆM BỀN VỮNG SAU NGÀY CƯỚI (POST-WEDDING KEEPSAKE) — [HOÀN TẤT 100%]

#### `TASK-VAL-06`: Sách Kỷ Yếu Hôn Nhân PDF (Keepsake Wedding Book PDF) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Cho phép cặp đôi tải về 1 cuốn sách kỷ yếu kỹ thuật số lưu giữ toàn bộ chữ ký, lời chúc và ảnh cưới.
- **Phạm vi code**:
  * Route: `GET /tai-khoan/thiep/{id}/sach-ky-yeu` (`memory.keepsake_book`).
  * Controller: `MemoryVaultController::keepsakeBook`.
  * View: `resources/views/client/pages/keepsake-book.blade.php`.
  * CSS: `public/assets/client/css/pages/keepsake-book.css`.
  * Bố cục: Khổ A4 ngang/dọc sang trọng, bìa da mỹ thuật, trang câu chuyện tình yêu, tâm thư tri ân, các trang lời chúc kèm chữ ký viết tay của bạn bè (chia 3 lời chúc/trang), ngắt trang in ấn `@media print` hoàn hảo.
  * Phân quyền: Kiểm soát chủ sở hữu an toàn (IDOR defense trả 404).
- **Kiểm thử**: `tests/Feature/KeepsakeWeddingBookTest.php` (3/3 tests passed).

#### `TASK-VAL-07`: Mở Niêm Phong Hộp Thời Gian Tự Động (Anniversary Email Reminder) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Gửi email thông báo nhắc nhở cặp đôi vào đúng ngày mở khóa tâm thư sau 1, 3 hoặc 5 năm.
- **Phạm vi code**:
  * Migration: `2026_11_07_184503_add_notified_at_to_wedding_time_capsules_table.php` bổ sung cột `notified_at`.
  * Model: Cập nhật `app/Models/WeddingTimeCapsule.php` `$fillable` và `$casts`.
  * Mailable: `app/Mail/TimeCapsuleAnniversaryMail.php` và view `resources/views/emails/time-capsule-anniversary.blade.php` (thiết kế Quiet Luxury sang trọng, tem sáp kỷ niệm, nút mở tâm thư trực tiếp).
  * Artisan Command: `app/Console/Commands/CheckTimeCapsuleAnniversaries.php` (`php artisan mayhen:check-time-capsules`).
  * Scheduler: Đăng ký lịch chạy tự động hàng ngày trong `routes/console.php`.
- **Kiểm thử**: `tests/Feature/TimeCapsuleAnniversaryNotificationTest.php` (1/1 test passed, 9 assertions).

---

### CỘT MỐC 4: AN TÂM XUẤT BẢN & PHÒNG VỆ SỰ CỐ DỮ LIỆU (PUBLISH PROTECTION & CONFIDENCE) — [HOÀN TẤT 100%]

#### `TASK-VAL-08`: Chế Độ Tổng Duyệt Vai Trò Trước Khi Gửi Thiệp (Pre-Publish Rehearsal & Persona Simulator) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Cho phép cặp đôi mô phỏng góc nhìn thực tế của từng nhóm khách mời (Bạn Bè, Gia Đình, Đồng Nghiệp, Khách VIP) ngay trên thiệp mà không cần tạo khách giả hay gửi token thật.
- **Phạm vi code**:
  * Controller: `InvitationEditorController@show` hỗ trợ tham số `rehearsal=1` và `rehearsal_group={tên_nhóm}`, tự động render đúng thông điệp Persona và khởi tạo `$dummyGuest` mô phỏng thẻ bàn tiệc VIP/thông thường.
  * View component: `resources/views/client/components/rehearsal-bar.blade.php` thanh top bar nổi sang trọng, dropdown chuyển vai tức thời, nút thoát chế độ tổng duyệt. Tự động ẩn an toàn trong Studio iframe embed.
  * Nhúng vào layout: `resources/views/client/layouts/invitation.blade.php` và `public-invitation.blade.php`.
  * Nút thao tác nhanh: Thêm nút `[ 🎭 Tổng Duyệt Thiệp ]` trên `account/settings.blade.php`.
- **Kiểm thử**: `tests/Feature/PrePublishRehearsalTest.php` (4/4 tests passed).

#### `TASK-VAL-09`: So Sánh Khác Biệt & Báo Cáo Tác Động Khi Sửa Ngày Giờ Thiệp Đã Gửi (Publish Diff & Impact Report) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Tự động phát hiện khi cặp đôi điều chỉnh thông tin trọng yếu của thiệp cưới đã phát hành (ngày cưới, giờ khai tiệc, sảnh tiệc, địa chỉ, số tài khoản ngân hàng), tính toán chính xác số lượng khách mời và số khách đã RSVP có thể bị ảnh hưởng.
- **Phạm vi code**:
  * Service: Mở rộng `WeddingConsistencyGuardianService::evaluatePublishImpact(Wedding $wedding, array $newData)`.
  * Phân loại mức độ cảnh báo: `critical` (đổi ngày/sảnh tiệc - nguy cơ khách đi nhầm ngày/sảnh), `warning` (đổi giờ tiệc/tài khoản ngân hàng), `none` (chỉ sửa câu chuyện/ảnh thông thường).
  * API endpoint: `POST /api/editor/publish-impact/{id}` (`invitation.publish_impact`) phân quyền an toàn `auth` và `isOwner()`.
- **Kiểm thử**: `tests/Feature/PublishImpactReportTest.php` (4/4 tests passed).

#### `TASK-VAL-10`: Bàn Soát Lỗi Trước Khi Gửi Thiệp & Trợ Lý Xưng Hô (Pre-Send Validation Desk & Family Etiquette Assistant) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Rà soát toàn diện danh sách khách mời trước khi gửi thiệp đồng loạt, cảnh báo khách thiếu danh xưng lễ nghi, khách chưa có nhóm, khách trùng tên/số điện thoại và gợi ý danh xưng chuẩn mực văn hóa cưới hỏi Việt Nam.
- **Phạm vi code**:
  * Service: `app/Services/PreSendValidationService.php` tính toán điểm số sẵn sàng (`readiness_score`), phân loại 5 nhóm rủi ro (chưa xuất bản, thiếu danh xưng, thiếu nhóm, trùng lặp, số suất <= 0) và cung cấp bảng gợi ý xưng hô tương ứng từng nhóm khách.
  * Backend API: `GET /tai-khoan/thiep/{id}/khach-moi/kiem-tra-truoc-gui` (`guests.pre_send_validation`) trong `GuestController.php`.
  * Giao diện Quản lý khách: Nút bấm `[ 🛡️ Soát Lỗi Trước Khi Gửi ]` và Modal Drawer sang trọng trên `resources/views/client/pages/guests.blade.php`.
  * Client JS: `public/assets/client/js/pages/guests.js` gọi API, hiển thị badge phân cấp mức độ và bảng xưng hô tương tác.
- **Kiểm thử**: `tests/Feature/PreSendValidationTest.php` (3/3 tests passed).

#### `TASK-VAL-11`: Nút Khôi Phục Bản Xuất Bản Khẩn Cấp & Phòng Vệ Snapshot (Emergency Rollback Button & Snapshot Safeguard) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Cho phép cặp đôi khôi phục lại tức thì bản xuất bản gần nhất trước đó trong 1 thao tác an toàn khi vô tình lưu nhầm hoặc đổi nhầm thông tin thiệp cưới đã gửi cho khách, không làm mất bản nháp và không phụ thuộc vào admin can thiệp thủ công.
- **Phạm vi code**:
  * Backend Logic: `InvitationTemplateService.php` tự động chụp `_previous_published_snapshot` trước mỗi lần xuất bản đè lên thiệp.
  * Rollback Handler: `AccountController::rollback` khôi phục an toàn thông tin đám cưới (`wedding`) và cấu hình giao diện (`custom_config`), tăng `revision_id` và gỡ bỏ snapshot lồng nhau.
  * Route: `POST /thiep/{id}/khoi-phuc-khan-cap` (`account.invitations.rollback`) kèm phòng vệ CSRF và kiểm soát IDOR chặt chẽ (trả về 404 cho tài khoản khác).
  * Giao diện: Khối khôi phục khẩn cấp trên `resources/views/client/pages/account/settings.blade.php` hiển thị thời điểm lưu bản trước và nút khôi phục 1 chạm có hộp thoại xác nhận.
- **Kiểm thử**: `tests/Feature/EmergencyRollbackTest.php` (3/3 tests passed).

#### `TASK-VAL-12`: Bảng Đánh Giá Độ Sẵn Sàng Ngày Cưới (Wedding Readiness Checklist) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Rà soát 7 tiêu chí sống còn trước ngày diễn ra hôn lễ (không chỉ kiểm tra mẫu thiệp đẹp, mà đánh giá tính toàn vẹn: xuất bản công khai, ngày giờ sảnh tiệc, liên kết Google Maps chỉ đường, VietQR mừng cưới, danh sách khách mời, sơ đồ xếp bàn tiệc, và bộ dự phòng sự cố A4 kèm mã PIN tiếp tân an toàn).
- **Phạm vi code**:
  * Service: Mở rộng `WeddingConsistencyGuardianService::calculateReadinessChecklist(Wedding $wedding, ?Invitation $invitation = null)` tính toán điểm số %, số bước đạt chuẩn và phân cấp trạng thái (`ready` >= 85%, `needs_attention` 50-84%, `incomplete` < 50%).
  * Backend API: `GET /api/editor/readiness/{id}` (`invitation.readiness_check`) trong `InvitationEditorController.php`.
  * Dashboard tích hợp: `AccountController::invitations` tính toán sẵn `readinessMap` cho danh sách thiệp của người dùng.
  * Giao diện Client: Thanh tiến độ trực quan trên từng card thiệp cưới (`invitations.blade.php`) và Modal chi tiết 7 bước (`resources/views/client/components/readiness-modal.blade.php`).
  * Pure Native JS: `public/assets/client/js/pages/account-readiness.js` mở modal, nạp data dynamic và điều hướng nhanh tới đúng màn hình cấu hình.
  * CSS Token: Khối `mayhen_readiness_*` trong `public/assets/client/css/pages/account.css`.
- **Kiểm thử**: `tests/Feature/WeddingReadinessChecklistTest.php` (5/5 tests passed, 24 assertions).

---

### CỘT MỐC 5: TRỢ LÝ ĐÓN TIẾP VIP & HÒA HỢP GIA ĐÌNH HAI NHÀ (GUEST CONCIERGE & FAMILY COLLISION DEFENSE) — [HOÀN TẤT 100%]

#### `TASK-VAL-13`: Trang Trợ Lý Khách Mời VIP Cá Nhân Hóa (One-Page Guest Concierge) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Thay vì chỉ gửi thiệp mời dạng lật trang thông thường, cung cấp một trang Concierge 1 trang cá nhân hóa độc quyền cho từng vị khách tại `/khach-moi/{guestToken}` (không cần tạo tài khoản). Tổng hợp đầy đủ mọi tiện ích sống còn vào ngày đi ăn cưới: Tên khách & số suất mời, Thẻ bàn tiệc điện tử nổi bật, Mã QR điểm danh nhanh tại cổng đón tiếp, Thông điệp Persona riêng biệt, Nút 1 chạm thêm vào Google Calendar / Apple Calendar (.ics), Định vị Google Maps & Bãi đỗ xe thông minh (ô tô, xe máy, thang máy), Form xác nhận tham dự nhanh (Fast RSVP) và Hộp mừng cưới VietQR cá nhân hóa.
- **Phạm vi code**:
  * Controller: Mở rộng `CheckInController::guestConcierge` và `CheckInController::downloadIcs` xuất file `.ics` chuẩn iCalendar v2.0.
  * Routes: `GET /khach-moi/{guestToken}` (`guest.concierge`), `GET /khach-moi/{guestToken}/lich-ics` (`guest.concierge.ics`), đồng thời liên kết tương thích 100% với `checkin.direct`.
  * View: `resources/views/client/pages/guest-concierge.blade.php` phong cách Quiet Luxury Editorial sang trọng.
  * Liên kết ngược: Cập nhật component `guest-table-badge.blade.php` dẫn thẳng khách sang trang Concierge từ thiệp cưới.
  * CSS: Toàn bộ hệ thống class `mayhen_concierge_*` trong `public/assets/client/css/pages/checkin.css`.
- **Kiểm thử**: `tests/Feature/OnePageGuestConciergeTest.php` (5/5 tests passed, 21 assertions) & `CheckInTableCoordinationTest.php` (10/10 passed).

#### `TASK-VAL-14`: Bộ Phát Hiện Xung Đột Lịch Trình Lễ Cưới & Tiệc Cưới Hai Nhà (Dual-Ceremony Collision Detector) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Giải quyết bài toán văn hóa cưới hỏi Việt Nam (Lễ Vu Quy Nhà Gái, Lễ Thành Hôn Nhà Trai, Tiệc Cưới Nhà Hàng). Tự động phát hiện các xung đột nguy hiểm: trùng giờ làm lễ hoặc cách nhau quá ngắn (< 90 phút khiến đoàn rước dâu trễ giờ lành); lễ gia tiên diễn ra sau giờ tiệc cưới; nghi vấn lệch chi nhánh giữa Tên sảnh tiệc và Link Google Maps (các chuỗi nhà hàng tiệc cưới lớn như White Palace, Adora, Asiana...); và khách họ hàng bị gán nhầm sự kiện.
- **Phạm vi code**:
  * Service: Thêm `detectCeremonyCollisions(Wedding $wedding, ?Invitation $invitation = null)` trong `WeddingConsistencyGuardianService.php`.
  * Phân cấp mức độ xung đột: `critical` (trùng giờ làm lễ, lễ sau tiệc, link Google Maps trỏ sai chi nhánh), `warning` (giãn cách giờ quá gấp, họ hàng gán nhầm), `none` (lịch trình hoàn hảo).
  * API endpoint: `GET /api/editor/collision-check/{id}` (`invitation.collision_check`) trong `InvitationEditorController.php`.
- **Kiểm thử**: `tests/Feature/DualCeremonyCollisionDetectorTest.php` (6/6 tests passed, 18 assertions).

---

### CỘT MỐC 6: VẬN HÀNH NGÀY CƯỚI & PHỐI HỢP ĐỐI TÁC (DAY-OF RELIABILITY & VENDOR COLLABORATION) — [HOÀN TẤT 100%]

#### `TASK-VAL-15`: Bộ Tài Liệu Tác Nghiệp Tối Giản Cho MC, Nhà Hàng & Nhiếp Ảnh Gia (Vendor Brief Export) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Ngày cưới thực tế có rất nhiều bên đối tác tác nghiệp (MC đám cưới, Quản lý sảnh tiệc / Catering, Nhiếp ảnh gia / Phóng sự cưới). Cặp đôi thường phải in hoặc nhắn tin lộn xộn các thông tin riêng cho từng bên. Vendor Brief cung cấp trang tra cứu và in ấn chuẩn A4 tối giản, chia rõ 3 phần tác nghiệp:
  1. *Dành cho MC / Hôn lễ*: Tên đầy đủ cô dâu, chú rể, tên phụ mẫu hai bên kèm danh xưng trang trọng, timeline từng mốc nghi lễ (đón khách, khai tiệc, rót rượu, cắt bánh, chúc mừng tại bàn).
  2. *Dành cho Quản lý Sảnh Tiệc / Catering*: Sĩ số khách chính thức theo RSVP, số bàn tiệc đã xếp, danh sách khách có ghi chú ăn kiêng/chay/dị ứng, và sơ đồ phân bổ bàn tiệc.
  3. *Dành cho Nhiếp ảnh gia / Media*: Lịch trình di chuyển hai nhà, danh sách các bàn tiệc VIP cần ưu tiên chụp ảnh đầy đủ, và mã QR quét nhanh truy cập trang Photo Drop của ngày cưới.
- **Phòng vệ bảo mật & riêng tư (Zero Financial / PII Leak)**:
  * Không hiển thị số điện thoại cá nhân của khách mời.
  * Tuyệt đối không hiển thị số tiền mừng cưới hoặc số tài khoản ngân hàng riêng tư trong tài liệu bàn giao tác nghiệp này.
  * Phân quyền bảo mật: Chỉ chủ sở hữu thiệp mới có quyền truy cập (`isOwner()`, trả về 404 cho tài khoản khác).
- **Phạm vi code**:
  * Service: `app/Services/VendorBriefService.php`.
  * Controller: `AccountController::vendorBrief` (`GET /tai-khoan/thiep/{id}/vendor-brief`).
  * Route: `account.invitations.vendor_brief`.
  * View: `resources/views/client/pages/account/vendor-brief.blade.php`.
  * CSS/JS: `public/assets/client/css/pages/vendor-brief.css` và `public/assets/client/js/pages/vendor-brief.js` (hỗ trợ chuyển đổi linh hoạt chế độ xem All / MC / Venue / Photo và tối ưu in ấn A4 chuẩn `@media print`).
  * Giao diện: Nút bấm `[ 📋 Tài Liệu Tác Nghiệp ]` trên `resources/views/client/pages/account/invitations.blade.php`.
- **Kiểm thử**: `tests/Feature/VendorBriefExportTest.php` (4/4 tests passed, 21 assertions).

#### `TASK-VAL-16`: Trợ Lý Lên Lịch Nhắc Nhở Tế Nhị & Phân Luồng RSVP (Gentle Reminder Planner) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Giải quyết bài toán ngại ngùng của cô dâu chú rể khi phải đi giục khách chốt tham dự hoặc nhắc lịch trước ngày cưới. Tránh việc nhắn tin thô thiển hoặc giục nhầm người đã xác nhận tham dự.
- **Phạm vi code**:
  * Service: `app/Services/GentleReminderPlannerService.php` tự động tính toán số ngày còn lại đến đám cưới (`days_until_wedding`), phân tích danh sách khách thành 3 nhóm trạng thái:
    1. `pending`: Khách chưa phản hồi RSVP (cần nhắc xác nhận lịch tham dự).
    2. `attending`: Khách đã xác nhận tham dự (cần nhắc giờ & chia sẻ bãi đỗ xe/lối vào trước ngày cưới 2-3 ngày).
    3. `declined`: Khách đã báo không thể tham dự (lời tri ân và chia sẻ đường link xem kỷ niệm).
  * Mẫu tin nhắn văn hóa tế nhị: Soạn sẵn 3 phong cách (Trang trọng / Thân mật / Nhắc sát ngày cưới 3 ngày) có sẵn placeholder danh xưng và tên khách, kèm link tra cứu trợ lý 1 trang `/khach-moi/{guestToken}`.
  * Controller: `GuestController::reminderPlanner` (`GET /thiep/{id}/khach-moi/ke-hoach-nhac-nho`).
  * Giao diện: Nút bấm `[ 💌 Kế Hoạch Nhắc Lịch ]` và Modal Drawer sang trọng trên `resources/views/client/pages/guests.blade.php`.
  * Client JS: `public/assets/client/js/pages/guests.js` nạp dữ liệu thống kê dynamic, cho phép chuyển đổi nhóm khách, chọn mẫu tin nhắn và nút Copy 1 chạm thông minh kèm thông báo toast.
- **Kiểm thử**: `tests/Feature/GentleReminderPlannerTest.php` (4/4 tests passed, 15 assertions).

---

### CỘT MỐC 7: KỶ NIỆM BỀN VỮNG & TRI ÂN SAU CƯỚI (POST-WEDDING VALUE & LIFELONG KEEPSAKE) — [HOÀN TẤT 100%]

#### `TASK-VAL-17`: Trợ Lý Soạn Lời Cảm Ơn Sau Cưới Cá Nhân Hóa (Personal Thank-you Builder) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Sau đám cưới, cô dâu chú rể kiệt sức nhưng bắt buộc phải gửi lời cảm ơn đến khách dự và bạn bè đã gửi quà mừng. Công cụ phân luồng tự động dựa trên dữ liệu thật (đã check-in dự tiệc / đã mừng cưới / không đến nhưng gửi quà) và tạo sẵn mẫu tin nhắn tri ân cảm xúc chân thành, kèm link xem lại album ảnh kỷ niệm của tiệc cưới.
- **Phòng vệ tinh tế & xưng hô lễ nghi**:
  * Tự động phát hiện và xử lý hiện tượng lặp danh xưng trong văn hóa xưng hô Việt Nam (ví dụ: Salutation "Bác" + Tên "Bác Trần Văn Phú" -> Tự động chuyển thành "Bác Trần Văn Phú" thay vì "Bác Bác Trần Văn Phú").
  * Phân biệt rõ văn phong trang trọng, tôn kính dành cho bậc tiền bối / họ hàng VIP ("vợ chồng con... trân trọng gửi lời cảm ơn sâu sắc nhất... niềm vinh hạnh to lớn của gia đình hai bên") với văn phong vui tươi, nồng nhiệt dành cho bạn bè ("quẩy hết mình... bữa tiệc trọn vẹn và tràn ngập tiếng cười").
  * Đính kèm đường dẫn album ảnh trực tiếp vào tin nhắn để người thân, bạn bè mở xem ngay.
- **Phạm vi code**:
  * Service: `app/Services/PersonalThankYouService.php`.
  * Controller: `GuestController::thankYouBuilder` (`GET /thiep/{id}/khach-moi/loi-cam-on-sau-cuoi`).
  * Route: `guests.thankyou_builder`.
  * Giao diện: Nút bấm `[ 💐 Lời Cảm Ơn Sau Cưới ]` và Modal Drawer `#mayhen_modal_thankyou_builder` trên `resources/views/client/pages/guests.blade.php`.
  * Client JS: `public/assets/client/js/pages/guests.js` nạp dữ liệu động, bộ lọc 3 tab (Tất cả / Đã đến / Gửi quà) và nút Copy 1 chạm thông minh.
- **Kiểm thử**: `tests/Feature/PersonalThankYouBuilderTest.php` (4/4 tests passed, 35 assertions).

#### `TASK-VAL-18`: Gói Kỷ Niệm Ngoại Tuyến Độc Lập (Portable Wedding Archive & Integrity Checksum) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Giải tỏa nỗi lo âu lớn nhất của cặp đôi: *"Trang web 10-20 năm nữa có còn không? Nếu sau này máy chủ nâng cấp thì thiệp và kỷ niệm có mất không?"*. Cho phép xuất toàn bộ thiệp cưới, ảnh cưới, lời chúc và chữ ký thành 1 file ZIP lưu trữ độc lập kèm file chứng thực toàn vẹn manifest JSON / SHA256 checksum, mở chạy trực tiếp trên máy tính cá nhân không cần internet.
- **Cấu trúc gói lưu trữ ZIP ngoại tuyến**:
  1. `index.html`: Trang thiệp tĩnh ngoại tuyến tự chứa (Standalone Self-contained Offline HTML), mở bằng mọi trình duyệt không cần internet hay máy chủ.
  2. `danh_sach_khach_moi_va_rsvp.csv`: Bảng tính Excel UTF-8 with BOM lưu danh bạ và kết quả điểm danh.
  3. `loi_chuc_va_chu_ky.json`: Toàn bộ lời chúc phúc và hồi đáp nguyên bản.
  4. `HUONG_DAN_LUU_TRU_KY_NIEM.txt`: Bức tâm thư trân quý gửi tặng cặp đôi và gia đình, hướng dẫn lưu trữ bền vững.
  5. `wedding_manifest.json`: Bảng kê khai chứng thực số ghi nhận mã băm SHA-256 của từng tệp, bảo đảm tính toàn vẹn kỹ thuật số không thể giả mạo.
- **Phạm vi code**:
  * Service: `app/Services/PortableWeddingArchiveService.php`.
  * Controller: `MemoryVaultController::offlineArchive` (`GET /thiep/{id}/xuat-ky-niem-ngoai-tuyen`).
  * Route: `memory.offline_archive` kèm rate limit `throttle:10,1` và IDOR defense (trả về 404 cho tài khoản khác).
  * Giao diện: Nút bấm `[ 📦 Gói Ngoại Tuyến ]` trên `resources/views/client/pages/account/invitations.blade.php` và `resources/views/client/pages/memory-vault.blade.php`.
- **Kiểm thử**: `tests/Feature/PortableWeddingArchiveTest.php` (3/3 tests passed, 28 assertions).

---

### CỘT MỐC 8: HÒA HỢP GIA ĐÌNH & DUYỆT THIỆP HAI NHÀ (FAMILY HARMONY & TWO-FAMILY APPROVAL) — [HOÀN TẤT 100%]

#### `TASK-VAL-19`: Chế Độ Phê Duyệt Thông Tin Cưới Hỏi Hai Nhà (Two-Family Review & Blessing Approval) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Giải quyết bài toán văn hóa cưới hỏi Việt Nam: Đám cưới là chuyện của hai họ. Rất nhiều cặp đôi tự ý làm thiệp rồi bị bố mẹ hai bên trách mắng vì sai sót tên đệm phụ mẫu, thiếu chức danh hoặc ghi nhầm giờ hoàng đạo làm lễ gia tiên nhập trạch/đón dâu. Cung cấp liên kết rà soát riêng biệt cho Bố Mẹ Nhà Trai và Bố Mẹ Nhà Gái (không cần tài khoản, font chữ to, tương phản cao, dễ đọc cho người lớn tuổi).
- **Phạm vi code**:
  * Service: `app/Services/TwoFamilyApprovalService.php`.
  * Controller: `FamilyApprovalController::review` (`GET /gia-dinh-duyet/{token}`), `submitDecision` (`POST /gia-dinh-duyet/{token}/quyet-dinh`), và `status` (`GET /api/editor/family-approval/{id}`).
  * View: `resources/views/client/pages/family-review.blade.php` phong cách Quiet Luxury Editorial sang trọng, 4 phần kiểm tra cốt lõi: Phụ mẫu & Uyên ương, Giờ làm lễ hai nhà, Sảnh tiệc chiêu đãi, và nút mở xem toàn bộ thiệp.
  * Hộp thao tác: Phụ mẫu có thể bấm `[ ⚜️ Bố Mẹ Đồng Ý & Chúc Phúc ]` kèm lời dặn dò, hoặc `[ ✏️ Có Chi Tiết Cần Sửa Lại ]` để gửi góp ý cho hai con.
  * Tích hợp Dashboard: Khối 8 trong `resources/views/client/pages/account/settings.blade.php` hiển thị trạng thái và link sao chép cho hai bên gia đình.
- **Kiểm thử**: `tests/Feature/TwoFamilyApprovalTest.php` (5/5 tests passed, 27 assertions).

---

### CỘT MỐC 9: TỔNG DIỄN TẬP ĐÓN TIẾP & MÔ PHỎNG SỰ KIỆN (WEDDING DAY DRY RUN & RECEPTION SIMULATOR) — [HOÀN TẤT 100%]

#### `TASK-VAL-20`: Mô Phỏng Check-in & Kiểm Thử Tổng Diễn Tập Đón Tiếp (Wedding Day Dry Run & Reception Simulator) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Giải tỏa nỗi lo âu lớn nhất của ban lễ tân tiệc cưới (bạn thân, anh chị em họ nhận việc sát giờ): Không biết máy quét hoạt động thế nào, khách đi kèm 3 người thì thao tác ra sao, khách chưa xếp bàn thì tìm bàn ở đâu. Nếu thử bằng khách thật thì làm hỏng số liệu check-in thật; nếu không thử thì lúng túng gây ùn tắc cổng đón tiếp vào giờ cao điểm.
- **Phòng vệ cách ly dữ liệu tuyệt đối (Zero Database Pollution)**:
  * Nạp 5 kịch bản khách mẫu mô phỏng kinh điển trong ngày cưới (`Bác Trần Văn Tuấn` - VIP có bàn, `Gia đình anh Lê Hoàng Nam` - headcount 4 suất, `Bạn Nguyễn Thu Hà` - chưa xếp bàn / ăn chay, `Chị Phạm Mai Hương` - mô phỏng quét trùng lần 2, `Anh Đỗ Minh Khang` - khách tiêu chuẩn).
  * Mọi thao tác quét mã, tìm kiếm nhanh và xếp bàn trong chế độ diễn tập chỉ lưu trong Session tạm thời, tuyệt đối **KHÔNG ghi vào bảng `guests` hay `check_ins` trong database**.
- **Phạm vi code**:
  * Service: `app/Services/ReceptionDryRunService.php`.
  * Controller: Mở rộng `CheckInController::showPortal`, `processScan`, `quickSearch`, `updateTable`, bổ sung `enableDryRun`, `disableDryRun`, `resetDryRun`.
  * Routes: `POST /diem-danh-tiec/{slug}/dien-tap/bat`, `/tat`, `/lam-moi`.
  * Giao diện: Banner tím Quiet Luxury nổi bật trên `resources/views/client/pages/checkin.blade.php` kèm 5 thẻ quét nhanh 1 chạm và `public/assets/client/js/pages/checkin.js`.
- **Kiểm thử**: `tests/Feature/ReceptionDryRunSimulatorTest.php` (5/5 tests passed, 26 assertions).

---

### CỘT MỐC 10: XỬ LÝ NGOẠI LỆ NGÀY CƯỚI & SỨC CHỨA BÀN TIỆC (RSVP EXCEPTION DESK & SEATING CAPACITY DEFENSE) — [HOÀN TẤT 100%]

#### `TASK-VAL-21`: Bàn Xử Lý Ngoại Lệ Khách Mời Ngày Cưới (RSVP Exception Desk) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Thực tế ngày cưới luôn có khách đi kèm phát sinh không báo trước (Walk-in), khách mang thêm người thân vượt suất dự kiến, hoặc khách từng báo bận (declined) nhưng vào phút chót thu xếp đến dự. Bàn lễ tân cần một công cụ xử lý 1 chạm ngay tại sảnh đón tiếp để tạo khách phát sinh, điều chỉnh sĩ số và gán bàn tiệc tức thời mà không làm gián đoạn luồng quét QR thông thường.
- **Phạm vi code**:
  * Controller: `CheckInController::walkInGuest`, `adjustAttendees`, `convertDeclined`.
  * Routes:
    - `POST /api/diem-danh-tiec/{slug}/ngoai-le/khach-phat-sinh` (`checkin.exception.walk_in`).
    - `POST /api/diem-danh-tiec/{slug}/ngoai-le/dieu-chinh-suat/{guestId}` (`checkin.exception.adjust_attendees`).
    - `POST /api/diem-danh-tiec/{slug}/ngoai-le/chuyen-tham-du/{guestId}` (`checkin.exception.convert_declined`).
  * Giao diện: Nút bấm `[ ⚡ Bàn Xử Lý Ngoại Lệ ]` màu vàng kim và Modal `#modal_exception_desk` trên `resources/views/client/pages/checkin.blade.php`.
  * Client JS: Logic nạp form AJAX và phản hồi tức thời trong `public/assets/client/js/pages/checkin.js`.
  * Hỗ trợ Dry Run: Hoạt động trơn tru trong cả chế độ thật và chế độ Diễn Tập (không ghi DB).
- **Kiểm thử**: `tests/Feature/ReceptionExceptionDeskTest.php` (4/4 tests passed, 22 assertions).

#### `TASK-VAL-22`: Bác Sĩ Bàn Tiệc & Dự Báo Sức Chứa Sảnh Cưới (Seating Capacity Guardian & Overfill Defense) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Ngăn ngừa thảm họa ngày cưới: Bàn tiệc bị nhồi nhét quá tải (10 ghế nhưng gán 13 người làm khách chen chúc ngột ngạt), bàn tiệc bị quá thưa (bàn 10 người nhưng chỉ có 2 khách làm lãng phí mâm cỗ), hoặc ghép nhầm thế hệ (người lớn tuổi / các bác họ hàng ngồi chung bàn với bạn bè trẻ gây ồn ào lệch nhịp). Tự động tính toán số bàn chính thức và số bàn dự phòng (buffer 10%) khuyên đặt nhà hàng.
- **Phạm vi code**:
  * Service: `app/Services/SeatingCapacityGuardianService.php` chấm điểm sức khỏe bàn tiệc (Health Score 0-100%), phát hiện overfilled, underfilled, mixed generations, tính tỷ lệ lấp đầy sảnh tiệc.
  * Controller: Cập nhật `SeatingPlannerController::index` và bổ sung API `SeatingPlannerController::healthCheck`.
  * Routes: `GET /api/thiep/{id}/so-do-ban-tiec/suc-khoe` (`seating.health_check`).
  * Giao diện: Thẻ chẩn đoán `mayhen_seat_guardian_card` trên `resources/views/client/pages/account/seating-planner.blade.php` hiển thị điểm số, dự báo số bàn chính thức + bàn dự phòng, danh sách vấn đề cần lưu ý và badge cảnh báo trên từng bàn tiệc trong canvas.
  * CSS: Bổ sung nhóm class `.mayhen_seat_guardian_*` vào `public/assets/client/css/pages/seating-planner.css`.
- **Kiểm thử**: `tests/Feature/SeatingCapacityGuardianTest.php` (5/5 tests passed, 39 assertions).

---

### CỘT MỐC 11: BÀN GIAO CA LỄ TÂN & MINH BẠCH QUYỀN RIÊNG TƯ (STAFF SHIFT HANDOVER & PRIVACY CONSENT) — [HOÀN TẤT 100%]

#### `TASK-VAL-23`: Bàn Giao Ca Trực & Phân Quyền Nhân Sự Đón Tiếp (Staff Shift Handover & Reception PIN Tokens) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Lễ tân ngày cưới thường chia ca hoặc chia cổng (Ca 1: Nhà Trai / Ca 2: Nhà Gái & Bạn Bè). Cặp đôi có thể cấp mã PIN riêng cho từng ca/nhân sự mà không cần chia sẻ mã PIN tổng. Khi hết ca, lễ tân bấm "Bàn Giao Ca Trực" để hệ thống tự động xuất biên bản bàn giao sĩ số: số khách đã đón trong ca, số người thực tế vào bàn, số khách phát sinh và số khách chưa đến, đồng thời lưu lịch sử bàn giao ca trực có ghi nhận tên nhân sự.
- **Phạm vi code**:
  * Service: `app/Services/StaffShiftHandoverService.php` (quản lý ca trực, xác thực mã PIN ca, tổng hợp snapshot số liệu ca, ghi nhận biên bản bàn giao).
  * Controller: Mở rộng `CheckInController::verifyPin`, `showPortal`, `processScan` (tự động gắn tiền tố tên nhân sự trực ca vào ghi chú điểm danh), và bổ sung `getShifts`, `saveShift`, `deleteShift`, `getHandoverSummary`, `submitShiftHandover`.
  * Routes:
    - Quản lý ca của cặp đôi: `GET /api/thiep/{id}/ca-truc-le-tan`, `POST /api/thiep/{id}/ca-truc-le-tan`, `DELETE /api/thiep/{id}/ca-truc-le-tan/{shiftId}`.
    - Bàn giao ca của lễ tân: `GET /api/diem-danh-tiec/{slug}/bien-ban-ban-giao`, `POST /api/diem-danh-tiec/{slug}/ban-giao-ca`.
  * Giao diện: Badge ca trực, nút `[ 📋 Bàn Giao Ca Trực ]` và Modal Drawer `#modal_shift_handover` trên `resources/views/client/pages/checkin.blade.php` cùng logic JavaScript trong `public/assets/client/js/pages/checkin.js`.
- **Kiểm thử**: `tests/Feature/StaffShiftHandoverTest.php` (4/4 tests passed, 23 assertions).

#### `TASK-VAL-24`: Trung Tâm Minh Bạch Quyền Riêng Tư & Kiểm Soát Dữ Liệu Khách Mời (Privacy & Consent Center - Nghị định 13/2023/NĐ-CP & Luật 91/2025/QH15) — [ĐÃ HOÀN THÀNH]
- **Mục tiêu**: Giúp cô dâu chú rể và khách mời hoàn toàn an tâm về dữ liệu cá nhân. Cặp đôi có quyền kiểm soát: che 4 số cuối tài khoản ngân hàng mừng cưới đối với khách vãng lai (chỉ hiển thị đủ cho khách có thiệp định danh cá nhân), kiểm duyệt ảnh khách tải lên trước khi chiếu, đặt thời hạn tự động tiêu hủy số điện thoại sau cưới (30 ngày / 90 ngày) tuân thủ "Quyền được lãng quên", và cam kết 100% không thu thập vị trí GPS ngầm của khách.
- **Phạm vi code**:
  * Service: `app/Services/WeddingPrivacyConsentService.php` (lưu trữ cấu hình bảo mật, lọc che dữ liệu nhạy cảm `filterSensitiveWeddingData`, lập báo cáo kiểm toán tuân thủ `generatePrivacyAuditReport`).
  * Controller: `PrivacyConsentController::show`, `update`, `auditReport`.
  * Routes: `GET /thiep/{id}/trung-tam-quyen-rieng-tu` (`privacy.show`), `POST /thiep/{id}/trung-tam-quyen-rieng-tu` (`privacy.update`), `GET /api/thiep/{id}/quyen-rieng-tu/kiem-toan` (`privacy.audit`).
  * Giao diện: Trang `resources/views/client/pages/account/privacy-consent.blade.php` phong cách Quiet Luxury Editorial kèm điểm số tuân thủ bảo vệ dữ liệu (Privacy Score 0-100%), bảng checklist 5 tiêu chí và CSS `public/assets/client/css/pages/privacy-consent.css`.
  * Tích hợp Settings: Nút `[ 🛡️ Quyền Riêng Tư ]` trên `resources/views/client/pages/account/settings.blade.php`.
- **Kiểm thử**: `tests/Feature/WeddingPrivacyConsentTest.php` (4/4 tests passed, 30 assertions).




