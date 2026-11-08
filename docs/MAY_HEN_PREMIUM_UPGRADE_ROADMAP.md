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

