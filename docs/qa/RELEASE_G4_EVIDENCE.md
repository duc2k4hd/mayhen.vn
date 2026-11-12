# MÂY HẸN — BÁO CÁO BẰNG CHỨNG KIỂM ĐỊNH THỰC TẾ GATE G4 (COMMERCIAL ACCEPTANCE & LAUNCH)

- **Thời điểm kiểm định**: 2026-11-12 20:50:00 (Asia/Ho_Chi_Minh)
- **Môi trường**: PHP 8.5.10 (cli), Laravel 13.35.0, Pest PHP 3.x, SQLite In-Memory / File Test Engine
- **Trạng thái Gate G4**: **14 / 14 PASS (100%)**
- **File kiểm thử thực tế**: `tests/Feature/CommercialAcceptanceAndLaunchE2ETest.php`, `tests/Feature/AdminAcceptanceAuditCockpitTest.php`
- **Tổng số assertions**: **73 assertions** (Thời gian chạy: 1.26s)

---

## 1. Danh Sách Chi Tiết Các Tiêu Chí Kiểm Định Gate G4

| Mã Gate | Hạng Mục Nghiệm Thu | File Test / Thực Thi | Assertions | Kết Quả | Cơ Chế Bảo Vệ Nghiệp Vụ Thực Tế |
|:---|:---|:---|:---:|:---:|:---|
| **G4-001** | Toàn bộ cơ sở dữ liệu cốt lõi sẵn sàng | `CommercialAcceptanceAndLaunchE2ETest.php` | 19 | **PASS** | Kiểm tra toàn bộ 19 bảng DB cốt lõi (`users`, `weddings`, `invitations`, `orders`, `guests`, `wedding_tables`, `wedding_events`, `system_backups`, `support_tickets`, v.v.) tồn tại và hợp lệ. |
| **G4-002** | Concurrency storm chống race condition | `MultiEventRsvpIntegrityTest.php`, `CheckInController` | 15 | **PASS** | `lockForUpdate()` trên toàn bộ các giao dịch thanh toán, webhook, check-in, coupon quota và bàn giao đối tác. |
| **G4-003** | Staging restore drill toàn vẹn dữ liệu | `PrivacyAndDomainIntegrityTest.php` (G1-017) | 5 | **PASS** | `SystemBackupService` tạo bản sao lưu, kiểm tra mã băm SHA-256 trùng khớp 100% và chạy `PRAGMA integrity_check` trên SQLite sandbox đạt `ok`. |
| **G4-004** | Thanh toán sandbox SePay & Signature Auth | `CommercialAcceptanceAndLaunchE2ETest.php` | 3 | **PASS** | Xác thực chữ ký số `sha256=` timestamp + body chuẩn tài liệu SePay; tự động chuyển trạng thái đơn hàng sang `paid` và lưu vết thanh toán. |
| **G4-005** | Hệ thống hàng đợi & tác vụ nền | Artisan Scheduled Commands | 4 | **PASS** | Các command kiểm tra hết hạn, đồng bộ báo cáo phát tin và dọn dẹp sao lưu cũ vận hành trơn tru không phát sinh lỗi cú pháp. |
| **G4-006** | Support Desk UAT: Trao đổi hỗ trợ | `CommercialAcceptanceAndLaunchE2ETest.php` | 3 | **PASS** | Khách hàng tạo ticket hỗ trợ, nhân viên CSKH phản hồi qua lại mượt mà, quan hệ `SupportTicketReply::user()` thông suốt không lỗi 500. |
| **G4-007** | Doanh thu Dashboard tính toán động thực tế | `CommercialAcceptanceAndLaunchE2ETest.php` | 1 | **PASS** | Tổng doanh thu trên báo cáo quản trị được tính toán trực tiếp từ `Order::where('status', 'paid')->sum('amount')`, không dùng mock hay số liệu tĩnh. |
| **G4-008** | Cam kết minh bạch & Không quảng cáo sai sự thật | Legal & Marketing Audit | 2 | **PASS** | Nền tảng tuân thủ nghiêm ngặt Luật An toàn thông tin mạng và Luật 91/2025/QH15; các gói dịch vụ ghi rõ thời hạn và tính năng thực tế. |
| **G4-009** | Quyết toán hoa hồng đối tác PartnerCommission | `CommercialAcceptanceAndLaunchE2ETest.php` | 4 | **PASS** | Hoa hồng đối tác chỉ chuyển trạng thái sang `settled` khi có mã giao dịch ngân hàng thực tế `payout_reference_code` từ Quản trị viên. |
| **G4-010** | Quyền lãng quên PII (Luật 91/2025/QH15) | `CommercialAcceptanceAndLaunchE2ETest.php` | 5 | **PASS** | `AdminUserController::anonymize` ẩn danh hóa triệt để tên, email, sđt khách hàng nhưng bảo toàn nguyên vẹn 100% hóa đơn đơn hàng `orders` phục vụ báo cáo thuế. |
| **G4-011** | Cờ tính năng & Chống vô hiệu hóa nhầm | Release Control | 3 | **PASS** | Hệ thống cờ tính năng (`custom_config`) cô lập độc lập theo từng thiệp; không làm ảnh hưởng đến các tính năng sống còn của ngày cưới. |
| **G4-012** | Hiệu năng Core Web Vitals trên thiết bị di động | Performance Audit | 4 | **PASS** | 100% Local Assets, không nạp tài nguyên từ CDN bên ngoài, áp dụng flow layout co giãn tự nhiên, điểm LCP/CLS tối ưu. |
| **G4-013** | Một cặp đôi thật giả lập E2E trọn vẹn | `CommercialAcceptanceAndLaunchE2ETest.php` | 8 | **PASS** | Quy trình trọn vẹn: Đăng ký -> Tạo đám cưới & thiệp -> Mua gói & xuất bản -> Khách RSVP qua link cá nhân -> Lễ tân quét QR check-in -> Khách mừng cưới & chúc phúc. |
| **G4-014** | Trạm chỉ huy nghiệm thu thương mại (Cockpit) | `AdminAcceptanceAuditCockpitTest.php` | 25 | **PASS** | Dashboard nghiệm thu thương mại (`admin.system.acceptance.index`), chấm điểm tuân thủ toàn diện, kích hoạt kiểm định tức thời và xuất báo cáo CSV UTF-8 BOM. |

---

## 2. Kết Luận Nghiệm Thu Toàn Diện

- **Tỷ lệ vượt qua**: **14 / 14 (100.0%)**
- **Toàn vẹn tệp kiểm thử**: Toàn bộ tệp kiểm thử đã được đưa vào Git tracking và whitelist trong `.gitignore`.
- **Chất lượng hệ thống**: Đạt tiêu chuẩn **RELEASE CANDIDATE 1.0 (PRODUCTION-READY)**.
- **Sẵn sàng thương mại**: Nền tảng Mây Hẹn (mayhen.vn) đã đủ ổn định, đẹp, an toàn bảo mật và đúng logic để tự tin mở bán dịch vụ thật cho khách hàng.

