# MÂY HẸN (MAYHEN.VN) — LỘ TRÌNH CHẤT LƯỢNG TOÀN DIỆN & GIÁ TRỊ THƯƠNG MẠI
# (FINAL QUALITY & COMMERCIAL VALUE ROADMAP — 5 GATES)

> **Dự án**: Nền tảng Thiệp Cưới Quiet Luxury Mây Hẹn (`mayhen.vn`)  
> **Căn cứ chỉ thị**: Mục 13 — Quy chuẩn lộ trình sau kiểm toán tại [`docs/MAY_HEN_FINAL_DEEP_LOGIC_AUDIT_AND_PREMIUM_VALUE_MASTER_PROMPT.md`](file:///d:/Project/Laravel/mayhen.vn/docs/MAY_HEN_FINAL_DEEP_LOGIC_AUDIT_AND_PREMIUM_VALUE_MASTER_PROMPT.md)  
> **Kiến trúc phân tầng**: 5 Cổng Release Gate (GATE A &rarr; GATE B &rarr; GATE C &rarr; GATE D &rarr; GATE E)  
> **Hiện trạng kiểm thử**: **407/407 Tests Passed 100% (2,044 assertions)** • Laravel Pint Agent Clean  
> **Thời điểm ban hành**: Tháng 11/2026  

---

## 1. NGUYÊN TẮC QUẢN TRỊ CHẤT LƯỢNG 5 CỔNG (THE 5-GATE RELEASE SYSTEM)

```text
┌────────────────────────────────────────────────────────────────────────┐
│ GATE A: TRUST & INTEGRITY (Bảo mật, dữ liệu, tiền & sự nhất quán)      │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ GATE B: CORRECTNESS & UX POLISH (Trạng thái giao diện & vi mô mobile)  │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ GATE C: DEEPEN EXISTING CAPABILITIES (Nâng cấp trưởng thành L1-L4)     │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ GATE D: HIGH-VALUE PREMIUM VALUE (Top tính năng cứu cánh ngày cưới)   │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ GATE E: EXPERIMENTS, METRICS & SCALING (Vận hành thương mại & SLA)     │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. CHI TIẾT 5 CỔNG NHIỆM VỤ (ACTIONABLE TICKET REGISTER)

### 🚪 GATE A — TRUST & INTEGRITY (BẢO MẬT, TOÀN VẸN DỮ LIỆU & PHÒNG VỆ SỰ CỐ)
*Mục tiêu: Đảm bảo không có bất kỳ lỗ hổng bảo mật, rò rỉ dữ liệu khách mời, xung đột tiền bạc hay rủi ro mất mát dữ liệu.*

#### `TICKET-GATE-A1`: Khắc phục triệt để sai lệch sĩ số check-in cổng & đồng bộ bàn tiệc (`BUG-AUDIT-01..03`)
- **Problem statement**: Khi khách RSVP đi 3 người, lễ tân quét QR không nhập đè thì hệ thống lấy số gốc 1 người làm lệch số cỗ; khách declined vẫn chiếm 1 ghế làm bàn tiệc quá tải ảo; sửa khách lưu tên bàn nhưng `table_id = null`.
- **User impact**: Cặp đôi bị phạt tiền cỗ hoặc thiếu bàn ăn trong tiệc cưới; lễ tân bối rối khi tìm bàn cho khách.
- **Business invariant**: Sĩ số check-in phải ưu tiên headcount RSVP thực tế; khách declined giải phóng ghế ngay lập tức; `table_name` và `table_id` luôn là một nguồn sự thật duy nhất.
- **Root cause**: `CheckInController` và `WeddingTable` truy vấn trực tiếp `expected_attendees` thay vì kiểm tra trạng thái RSVP.
- **Scope & Affected modules**: `app/Models/Guest.php`, `app/Models/WeddingTable.php`, `CheckInController.php`, `GuestController.php`.
- **Status**: **DONE (Đã có test suite `CrossModuleLogicInvariantsTest.php` 5/5 passed)**.

#### `TICKET-GATE-A2`: Phòng vệ bảo mật đa ranh giới, IDOR và chữ ký thanh toán SePay/VietQR
- **Problem statement**: Nguy cơ rò rỉ danh sách khách, album ảnh, két kỷ niệm hoặc giả mạo webhook thanh toán.
- **User impact**: Lộ thông tin cá nhân và tài chính của cô dâu chú rể.
- **Business invariant**: Tài khoản A không bao giờ truy cập được tài nguyên của tài khoản B (trả về 404 chống dò quét); Webhook SePay bắt buộc xác thực API Token/HMAC và bảo vệ giao dịch đơn hàng nguyên tử.
- **Scope & Affected modules**: `app/Policies/InvitationPolicy.php`, `CheckoutController.php`, `MemoryVaultController.php`, `TimeCapsuleController.php`.
- **Status**: **DONE (Đã có test suite `SecurityDeepDiveMatrixTest.php` 4/4 passed)**.

#### `TICKET-GATE-A3`: Bảo vệ quyền riêng tư cá nhân theo Nghị định 13/2023/NĐ-CP & Luật 91/2025/QH15 (`TASK-VAL-24`)
- **Problem statement**: Số tài khoản ngân hàng mừng cưới bị kẻ xấu lợi dụng khi link thiệp công khai trên mạng xã hội; số điện thoại khách mời lưu trữ vĩnh viễn không được kiểm soát.
- **User impact**: Khách vãng lai thấy đầy đủ STK; khách mời lo lắng số điện thoại bị thu thập cho bên tiếp thị.
- **Business invariant**: Khách vãng lai chỉ thấy 4 số cuối STK (`•••• •••• 4756`), chỉ khách có link thiệp cá nhân mới thấy STK đầy đủ; Cặp đôi có quyền đặt thời hạn tiêu hủy số điện thoại sau cưới (30/90 ngày).
- **Scope & Affected modules**: `app/Services/WeddingPrivacyConsentService.php`, `PrivacyConsentController.php`, view `privacy-consent.blade.php`.
- **Status**: **DONE (Đã có test suite `WeddingPrivacyConsentTest.php` 4/4 passed)**.

#### `TICKET-GATE-A4`: Phòng vệ toàn diện lỗ hổng CSV Formula Injection (CWE-1236 Defense)
- **Problem statement**: Khi khách mời nhập tên, lời chúc phúc hoặc ghi chú mừng cưới bắt đầu bằng các ký tự công thức (`=`, `+`, `-`, `@`, `\t`, `\r`), nếu người dùng hoặc quản trị viên xuất file CSV để mở bằng Excel / Google Sheets, phần mềm bảng tính có thể tự động thực thi mã lệnh nguy hiểm.
- **User impact**: Nguy cơ lộ thông tin máy trạm hoặc bị tấn công command injection khi mở tệp xuất khẩu.
- **Business invariant**: Toàn bộ chuỗi bắt đầu bằng ký tự công thức phải được tiền tố dấu nháy đơn `'` để vô hiệu hóa mã lệnh thành văn bản thuần; các trường số và text an toàn được giữ nguyên vẹn.
- **Scope & Affected modules**: `app/Services/CsvSanitizerService.php`, `WishManagementController.php`, `GuestController.php`, `GiftManagementController.php`, các Admin Export Controllers.
- **Status**: **DONE (Đã có test suite `CsvFormulaInjectionDefenseTest.php` 4/4 passed)**.

#### `TICKET-GATE-A5`: Đồng bộ phòng vệ trạng thái Khóa (`is_locked`) xuyên suốt mọi cổng phụ
- **Problem statement**: Khi thiệp bị Quản trị viên khóa do vi phạm hoặc hết hạn, người dùng không thể truy cập thiệp chính nhưng vẫn có thể đi đường phụ vào Memory Vault, Live Wall, Photo Drop, Check-in Concierge, Guest Concierge và file lịch ICS.
- **User impact**: Vi phạm Business Invariant #02 (không ai truy cập hoặc chuyển đổi thiệp bị khóa qua cổng phụ).
- **Business invariant**: Khi `is_locked = true`, mọi cổng phụ (HTML view) đều trả về `client.pages.invitation-locked`, mọi API nhận dữ liệu trả về JSON 403, file lịch từ chối tải.
- **Scope & Affected modules**: `MemoryVaultController.php`, `LiveWallController.php`, `PhotoDropController.php`, `CheckInController.php`.
- **Status**: **DONE (Đã có test suite `InvitationLockedConsistencyTest.php` 5/5 passed)**.

---

### 🚪 GATE B — CORRECTNESS & UX POLISH (TRẠNG THÁI GIAO DIỆN & VI MÔ TRÊN MOBILE)
*Mục tiêu: Đảm bảo giao diện 100% tinh tế, không bao giờ rơi vào màn hình trắng trơn, tương thích hoàn hảo ngón tay cái trên màn hình di động.*

#### `TICKET-GATE-B1`: 5 trạng thái màn hình bắt buộc (Empty, Loading, Error, Conflict 409, Offline)
- **Problem statement**: Khi người dùng chưa có khách mời, chưa có ảnh kỷ niệm hoặc mạng chập chờn, giao diện dễ bị vỡ hoặc hiện text báo lỗi khô khan.
- **User impact**: Cặp đôi bối rối không biết hệ thống đang lưu hay bị lỗi.
- **UX states**: Trạng thái Empty kèm hình minh họa Quiet Luxury và nút hành động gợi ý; trạng thái Loading có shimmer nhẹ nhàng; trạng thái Error giải thích rõ ràng và có nút thử lại.
- **Scope & Affected modules**: Toàn bộ Blade view trong `resources/views/client/pages/account/`.
- **Status**: **DONE (Đã kiểm toán và chuẩn hóa trong `MAY_HEN_MICRO_UX_AND_ERROR_STATE_AUDIT.md`)**.

#### `TICKET-GATE-B2`: Chuẩn hóa giao diện di động & Safe Area Insets chống che nút bấm
- **Problem statement**: Nút bấm bị che bởi thanh điều hướng Home Indicator của iPhone (Bottom Notch) hoặc touch target quá nhỏ dưới 44px khiến người lớn tuổi bấm nhầm.
- **User impact**: Khách mời khó khăn khi bấm nút RSVP hoặc mở bản đồ dẫn đường.
- **Scope & Affected modules**: `public/assets/client/css/common/base.css`, `tokens.css`.
- **Status**: **DONE (Padding-bottom hỗ trợ `env(safe-area-inset-bottom)`, 100% nút bấm min 44px)**.

---

### 🚪 GATE C — DEEPEN EXISTING CAPABILITIES (NÂNG CẤP CHIỀU SÂU & LIÊN KẾT HỆ THỐNG)
*Mục tiêu: Nâng cấp các tính năng từ mức L1/L2 lên L3/L4 (có liên kết hai chiều, phục hồi sự cố, không có nút chết).*

#### `TICKET-GATE-C1`: Nút khôi phục bản xuất bản khẩn cấp 1 chạm (`Emergency Rollback` - `TASK-VAL-11`)
- **Problem statement**: Cặp đôi lỡ tay chỉnh sửa nhầm thiệp và bấm Publish sát giờ đón khách, không biết làm sao lấy lại bản cũ.
- **User impact**: Khách mở thiệp thấy thông tin bị sai lệch trong ngày cưới.
- **Scope & Affected modules**: `AccountController::rollback`, `account/settings.blade.php`.
- **Status**: **DONE (Đã có test `EmergencyRollbackTest.php` 3/3 passed)**.

#### `TICKET-GATE-C2`: Báo cáo tác động thay đổi trước khi xuất bản (`Publish Impact Report` - `TASK-VAL-09`)
- **Problem statement**: Cặp đôi đổi giờ cưới từ 17:00 thành 18:00 nhưng không biết có bao nhiêu khách đã nhận thiệp bị ảnh hưởng.
- **User impact**: Khách đến sớm hoặc trễ giờ vì không biết có sự thay đổi.
- **Scope & Affected modules**: `app/Services/PublishImpactService.php`, `AccountController::publishImpact`.
- **Status**: **DONE (Đã có test `PublishImpactReportTest.php` 3/3 passed)**.

#### `TICKET-GATE-C3`: Rà soát & Phê duyệt dành riêng cho phụ mẫu hai bên (`Two-Family Approval` - `TASK-VAL-19`)
- **Problem statement**: Bố mẹ nhà trai và nhà gái cần kiểm tra kỹ thông tin họ tộc, vai vế và sảnh tiệc trước khi con cái gửi thiệp cho họ hàng.
- **User impact**: Tránh xung đột thế hệ và sai sót lễ nghi truyền thống.
- **Scope & Affected modules**: `app/Services/TwoFamilyApprovalService.php`, `FamilyApprovalController.php`.
- **Status**: **DONE (Đã có test `TwoFamilyApprovalTest.php` 4/4 passed)**.

---

### 🚪 GATE D — HIGH-VALUE PREMIUM VALUE (CÁC TÍNH NĂNG CỨU CÁNH NGÀY CƯỚI)
*Mục tiêu: Đưa vào những giá trị cao cấp thực tế giúp cô dâu chú rể an tâm tuyệt đối, sẵn sàng trả tiền.*

#### `TICKET-GATE-D1`: Chế độ thân thiện cho người lớn tuổi (`Senior-Friendly Guest Mode` - `TASK-VAL-01`)
- **Status**: **DONE (Đã có test `SeniorFriendlyGuestModeTest.php` 2/2 passed)**.

#### `TICKET-GATE-D2`: Bác sĩ đồng bộ dữ liệu trọng yếu (`Wedding Consistency Guardian` - `TASK-VAL-02`)
- **Status**: **DONE (Đã có test `WeddingConsistencyGuardianTest.php` 3/3 passed)**.

#### `TICKET-GATE-D3`: Bộ dự phòng sự cố mất mạng sảnh cưới (`Wedding Day Resilience Kit` - `TASK-VAL-03`)
- **Status**: **DONE (Đã có test `WeddingResilienceKitTest.php` 3/3 passed)**.

#### `TICKET-GATE-D4`: Tối ưu hóa ảnh phía Client Canvas WebP (`Client-side Image Optimizer` - `TASK-VAL-04`)
- **Status**: **DONE (Mã nguồn native canvas trong `public/assets/client/js/common/image-optimizer.js`)**.

#### `TICKET-GATE-D5`: Sổ tay hướng dẫn vị trí & bãi đỗ xe thông minh (`Interactive Parking Concierge` - `TASK-VAL-05`)
- **Status**: **DONE (Đã có test `ParkingConciergeTest.php` 3/3 passed)**.

#### `TICKET-GATE-D6`: Sách kỷ yếu hôn nhân PDF chuẩn in ấn A4 (`Keepsake Wedding Book` - `TASK-VAL-06`)
- **Status**: **DONE (Đã có test `KeepsakeWeddingBookTest.php` 3/3 passed)**.

#### `TICKET-GATE-D7`: Mở niêm phong Hộp thời gian tự động (`Anniversary Email Reminder` - `TASK-VAL-07`)
- **Status**: **DONE (Đã có test `TimeCapsuleAnniversaryNotificationTest.php` 1/1 passed)**.

#### `TICKET-GATE-D8`: Chế độ tổng duyệt vai trò khách mời (`Pre-Publish Rehearsal` - `TASK-VAL-08`)
- **Status**: **DONE (Đã có test `PrePublishRehearsalTest.php` 3/3 passed)**.

#### `TICKET-GATE-D9`: Bộ kiểm tra trước khi gửi thiệp (`Pre-Send Validation` - `TASK-VAL-10`)
- **Status**: **DONE (Đã có test `PreSendValidationTest.php` 3/3 passed)**.

#### `TICKET-GATE-D10`: Bảng kiểm tra toàn diện 100 điểm sẵn sàng (`Wedding Readiness Checklist` - `TASK-VAL-12`)
- **Status**: **DONE (Đã có test `WeddingReadinessChecklistTest.php` 4/4 passed)**.

#### `TICKET-GATE-D11`: Sổ tay khách mời 1 trang cá nhân hóa (`One-Page Guest Concierge` - `TASK-VAL-13`)
- **Status**: **DONE (Đã có test `OnePageGuestConciergeTest.php` 3/3 passed)**.

#### `TICKET-GATE-D12`: Bộ phát hiện xung đột lịch cưới hai nhà (`Dual-Ceremony Collision Detector` - `TASK-VAL-14`)
- **Status**: **DONE (Đã có test `DualCeremonyCollisionDetectorTest.php` 3/3 passed)**.

#### `TICKET-GATE-D13`: Xuất bản tóm tắt phân vai dịch vụ ngày cưới (`Vendor Brief Export` - `TASK-VAL-15`)
- **Status**: **DONE (Đã có test `VendorBriefExportTest.php` 3/3 passed)**.

#### `TICKET-GATE-D14`: Kế hoạch nhắc nhở khách mời nhẹ nhàng (`Gentle Reminder Planner` - `TASK-VAL-16`)
- **Status**: **DONE (Đã có test `GentleReminderPlannerTest.php` 4/4 passed)**.

#### `TICKET-GATE-D15`: Trình tạo thư cảm ơn sau cưới cá nhân hóa (`Personal Thank-you Builder` - `TASK-VAL-17`)
- **Status**: **DONE (Đã có test `PersonalThankYouBuilderTest.php` 4/4 passed)**.

#### `TICKET-GATE-D16`: Trích xuất gói lưu trữ độc lập kèm SHA-256 (`Portable Wedding Archive` - `TASK-VAL-18`)
- **Status**: **DONE (Đã có test `PortableWeddingArchiveTest.php` 3/3 passed)**.

#### `TICKET-GATE-D17`: Giả lập diễn tập tiếp tân không ô nhiễm database (`Reception Dry Run` - `TASK-VAL-20`)
- **Status**: **DONE (Đã có test `ReceptionDryRunSimulatorTest.php` 5/5 passed)**.

#### `TICKET-GATE-D18`: Bàn xử lý ngoại lệ ngày cưới tại sảnh (`RSVP Exception Desk` - `TASK-VAL-21`)
- **Status**: **DONE (Đã có test `ReceptionExceptionDeskTest.php` 4/4 passed)**.

#### `TICKET-GATE-D19`: Bác sĩ sức chứa bàn tiệc & cảnh báo quá tải (`Seating Capacity Guardian` - `TASK-VAL-22`)
- **Status**: **DONE (Đã có test `SeatingCapacityGuardianTest.php` 5/5 passed)**.

#### `TICKET-GATE-D20`: Phân ca trực lễ tân & biên bản bàn giao (`Staff Shift Handover` - `TASK-VAL-23`)
- **Status**: **DONE (Đã có test `StaffShiftHandoverTest.php` 4/4 passed)**.

#### `TICKET-GATE-D21`: Cố vấn khảo sát đề xuất gói dịch vụ phù hợp (`Plan Fit Advisor` - `TASK-VAL-25`)
- **Status**: **DONE (Đã có test `PlanFitAdvisorTest.php` 4/4 passed)**.

#### `TICKET-GATE-D22`: Cổng khởi đầu thành công sau thanh toán (`Post-Payment First Success` - `TASK-VAL-26`)
- **Status**: **DONE (Đã có test `PostPaymentFirstSuccessTest.php` 4/4 passed)**.

#### `TICKET-GATE-D23`: Trải nghiệm thử tính năng Pro trong Sandbox (`Premium Value Preview` - `TASK-VAL-27`)
- **Status**: **DONE (Đã có test `PremiumValuePreviewTest.php` 3/3 passed)**.

#### `TICKET-GATE-D24`: Bảng Radar sẵn sàng tuần lễ cưới & hotline 15 phút (`Wedding Week Support Radar` - `TASK-VAL-28`)
- **Status**: **DONE (Đã có test `WeddingWeekReadinessTest.php` 4/4 passed)**.

---

### 🚪 GATE E — EXPERIMENTS, METRICS & SCALING (VẬN HÀNH THƯƠNG MẠI & QUAN SÁT)
*Mục tiêu: Đảm bảo nền tảng vận hành mượt mà, sẵn sàng phục vụ hàng chục ngàn cặp đôi trong mùa cưới.*

#### `TICKET-GATE-E1`: Tích hợp Sandbox Value Preview Booster vào trang Bảng Giá (`pricing.blade.php`)
- **Problem statement**: Khách vào xem bảng giá phân vân không biết tính năng "Sơ đồ bàn tiệc" hay "Màn hình chiếu trực tiếp" hoạt động thực tế thế nào.
- **User impact**: Tăng tỷ lệ chuyển đổi (Conversion Rate) khi khách được xem trước dữ liệu mô phỏng sinh động.
- **Action**: Bổ sung khối "Xem Thử Các Tính Năng Cao Cấp Trong Gói Pro" với 4 liên kết trực tiếp tới Sandbox Preview (`/xem-thu-tinh-nang/{feature}`) kèm styling Quiet Luxury Editorial thuần trong `pricing.css`.
- **Status**: **DONE (Đã có test `PricingPageTest.php` 4/4 passed)**.

#### `TICKET-GATE-E2`: Kiểm toán tính nhất quán của bộ đếm Analytics và Scheduler hàng ngày
- **Action**: Đảm bảo lệnh `php artisan mayhen:check-time-capsules` chạy ổn định lúc 08:00 hàng ngày; không gửi email lặp lại nhờ cờ `notified_at`.
- **Status**: **PASS (Đã đăng ký trong `routes/console.php` và kiểm thử tự động)**.

---

## 3. TỔNG KẾT TIẾN TRÌNH 5 GATES

| Cổng Release | Số lượng Task | Đã hoàn thành | Đang thực thi | Trạng thái thẩm định |
|---|---|---|---|---|
| **GATE A (Trust & Integrity)** | 5 | 5 | 0 |  **PASS 100%** |
| **GATE B (Correctness & UX)** | 2 | 2 | 0 |  **PASS 100%** |
| **GATE C (Deepen Features)** | 3 | 3 | 0 |  **PASS 100%** |
| **GATE D (High-Value Premium)** | 24 | 24 | 0 |  **PASS 100%** |
| **GATE E (Scaling & Conversion)** | 2 | 2 | 0 |  **PASS 100%** |
| **TỔNG HỢP** | **36** | **36** | **0** | **HOÀN TẤT TRỌN VẸN 100%** |
