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

