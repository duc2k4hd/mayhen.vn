# 🌊 MÂY HẸN — ADMIN VNEXT IMPLEMENTATION WAVES
## LỘ TRÌNH 10 LÀN SÓNG TRIỂN KHAI HỆ THỐNG QUẢN TRỊ ATELIER COMMAND CENTER

> **Triết lý**: Triển khai theo từng lát cắt nghiệp vụ (vertical slices) hoàn chỉnh, từ bảo mật/tiền bạc cốt lõi đến hoàn thiện giao diện, mở rộng nghiệp vụ và kiểm thử thực tế. Không tạo menu rỗng.

---

## TỔNG QUAN 10 LÀN SÓNG (WAVE 00 → WAVE 09)

| Wave | Tên làn sóng | Mục tiêu trọng tâm | Trạng thái |
|:---:|---|---|:---:|
| **Wave 00** | **Evidence & Inventory** | Kiểm kê 100% routes, controls, permissions, thiết lập catalog CSV, sổ bug | **COMPLETED** |
| **Wave 01** | **P0 Security & Money** | Vá 4 lỗi P0/P1, củng cố RBAC, đối soát ngân hàng bắt buộc, bảo vệ Super Admin | **COMPLETED** |
| **Wave 02** | **Admin Shell + UX Foundations** | Tối ưu hóa layout Quiet Luxury, Command Palette, real-time badges, phím tắt | **COMPLETED** |
| **Wave 03** | **Users & Invitations** | Quản trị dự án cưới, Customer 360, Safe Support Lens bảo vệ PII, khóa tài khoản | **COMPLETED** |
| **Wave 04** | **Templates & AI Curated** | Quản trị kho mẫu, Schema QA, 7 phần Curated Native, kiểm soát asset địa phương | **COMPLETED** |
| **Wave 05** | **Guest & Wedding Ops** | Quản lý khách mời, RSVP, sơ đồ bàn tiệc, quầy check-in, duyệt lời chúc | **COMPLETED** |
| **Wave 06** | **Commerce & Growth** | Quản trị gói cước, gia hạn, coupon khuyến mãi, SEO & nội dung marketing | **COMPLETED** |
| **Wave 07** | **Support & Ops** | Trung tâm hỗ trợ Ticket SLA, quản lý Job queue, giám sát lưu trữ, cảnh báo | **COMPLETED** |
| **Wave 08** | **Data & Intelligence** | Dashboard phân tích, Wedding Readiness Radar, phát hiện sai lệch báo cáo | **COMPLETED** |
| **Wave 09** | **Hardening & UAT** | Kiểm thử hồi quy toàn diện cross-module, responsive đa màn hình, kiểm định phát hành | **READY** |

---

## CHI TIẾT TỪNG LÀN SÓNG

### Wave 00: Evidence & Inventory (Hiện tại)
- [x] Lập bản đồ 50 endpoints và kiểm kê toàn bộ routes, controllers, middleware, permissions (`01_RUNTIME_ADMIN_INVENTORY.md`).
- [x] Thiết lập Sổ đăng ký lỗi kiểm toán (`02_ADMIN_BUG_REGISTER.md`) ghi nhận 4 lỗi P0/P1.
- [x] Tạo lập `MAY_HEN_ADMIN_IMPLEMENTATION_WAVES.md` và `MAY_HEN_ADMIN_INNOVATION_BACKLOG.md`.
- [x] Khởi tạo catalog phân rã `MAY_HEN_ADMIN_ATOMIC_FUNCTIONS.csv` và `MAY_HEN_ADMIN_TEST_CASES.csv`.

### Wave 01: P0 Security & Money
- [x] Khắc phục `BUG-ADM-001`: Cho phép role `staff` đăng nhập qua form `/admin/login` thông qua `canAccessAdminPanel()`.
- [x] Khắc phục `BUG-ADM-002`: Bảo vệ tài khoản Super Admin duy nhất khỏi việc bị hạ quyền.
- [x] Khắc phục `BUG-ADM-003`: Bắt buộc nhập mã tham chiếu giao dịch ngân hàng và lý do đối soát khi duyệt đơn thủ công.
- [x] Khắc phục `BUG-ADM-004`: Khử độc/masking mảng `details` trong `AuditLogger` và ghi nhận nhật ký đăng nhập/đăng xuất.
- [x] Viết bộ test `tests/Feature/AdminVNextWave00AuditAndAuthTest.php` xác thực 100% các bản sửa lỗi.

### Wave 02: Admin Shell & UX Foundations
- [x] Hoàn thiện trải nghiệm bàn phím (Command Palette `Ctrl+K` / `Cmd+K` / `/`, Escape đóng modal/drawer, phím tắt tìm kiếm).
- [x] Hệ thống huy hiệu vận hành thời gian thực (real-time badge counts: đơn chờ duyệt, lời chúc chờ duyệt) tích hợp View Composer.
- [x] Cải thiện độ tương phản bảng dữ liệu, trạng thái rỗng (empty state) giàu thông điệp hướng dẫn.
- [x] Tối ưu hóa responsive trên laptop màn hình thấp và thiết bị di động (375px, 430px, 768px).

### Wave 03: Users & Invitations Operations
- [x] Hoàn thiện thông tin Customer 360 (kết nối hai chiều đơn hàng, thiệp cưới, chi tiêu và nhật ký kiểm toán).
- [x] Triển khai Safe Support Lens (Innovation #7): Chế độ xem thiệp cưới khách hàng che giấu PII (STK ngân hàng, SĐT) tuân thủ Luật 91/2025/QH15, ghi nhận audit log khi mở che.
- [x] Quản lý chu kỳ khóa / mở khóa tài khoản người dùng (`ADM-A04-014` / `ADM-A04-015`) với cơ chế phòng vệ chống tự khóa và bảo vệ Super Admin duy nhất.
- [x] Viết bộ test `tests/Feature/AdminVNextWave03UsersAndInvitationsTest.php` (7 tests, 49 assertions pass 100%).

### Wave 04: Templates Engine & Native Curated Governance
- [x] Nâng cấp bảng thanh tra Schema & Code Integrity Inspector hiển thị trạng thái của 10 Canonical Sections và tính điểm QA tự động.
- [x] Kiểm soát 100% tài sản cục bộ (Local Assets Defense), phát hiện và cảnh báo link ngoài (ladicdn, unsplash, 27biggroup...) hoặc file bị thiếu.
- [x] Kiểm tra hợp đồng Live Sync (`data-sync-target`), kế thừa layout chung (`@extends('client.layouts.invitation')`) và tiền tố BEM `mayhen_invitation_*`.
- [x] Bảo toàn nguyên lý AI Curated: Kiểm tra tương thích 7 phần Curated Native (`curated_compatible`).
- [x] Viết bộ test `tests/Feature/AdminVNextWave04TemplatesGovernanceTest.php` (5 tests, 41 assertions pass 100%).

### Wave 05: Guest CRM, Seating & Wedding Day Ops
- [x] Tạo phân hệ quản trị khách mời tập trung [Admin/GuestController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/GuestController.php) và giao diện [guests/index.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/guests/index.blade.php).
- [x] Đồng bộ dữ liệu phản hồi RSVP, sĩ số tham dự thực tế và phân bổ bàn tiệc thời gian thực.
- [x] Cơ chế điểm danh đón tiếp khách mời ngày cưới (Check-in Desk Toggle) hỗ trợ cả Web form và Tablet QR scanning API kèm audit log.
- [x] Nâng cấp kiểm duyệt lời chúc trong [WishController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/WishController.php) với tính năng duyệt/xóa hàng loạt (`batchApprove`, `batchDelete`).
- [x] Xuất danh bạ khách mời toàn hệ thống dạng CSV UTF-8 BOM khử độc dữ liệu chống CSV Injection.
- [x] Viết bộ test `tests/Feature/AdminVNextWave05GuestAndWeddingOpsTest.php` (4 tests, 35 assertions pass 100%).

### Wave 06: Commerce, Subscriptions & SEO
- [x] Tạo phân hệ quản trị Gói cước [Admin/PlanController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/PlanController.php) và giao diện [plans/index.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/plans/index.blade.php), [plans/edit.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/plans/edit.blade.php).
- [x] Bản chụp Snapshot Lịch Sử: Bảo toàn 100% số tiền kế toán đối soát của các đơn hàng cũ khi giá gói dịch vụ được điều chỉnh.
- [x] Thiết lập bảng `coupons`, model `Coupon.php` và phân hệ Quản lý Mã Giảm Giá [Admin/CouponController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/CouponController.php) với quy tắc chuẩn hóa chữ hoa, giới hạn lượt dùng, hạn dùng và chống giảm vượt trần (cap).
- [x] Tích hợp áp dụng mã giảm giá thời gian thực trong [CheckoutController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Client/CheckoutController.php) (`/api/checkout/apply-coupon`).
- [x] Phân hệ Thanh tra SEO & Sitemap XML [admin/pages/settings/seo.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/settings/seo.blade.php) và hành động xóa cache sitemap thời gian thực.
- [x] Xuất danh sách Coupon dạng CSV UTF-8 BOM khử độc dấu formula injection.
- [x] Viết bộ test `tests/Feature/AdminVNextWave06CommerceAndGrowthTest.php` (9 tests, 55 assertions pass 100%).

### Wave 07: Support Center, Jobs & System Diagnostics
- [x] Khung tiếp nhận và xử lý yêu cầu hỗ trợ (Support Ticket) theo thời hạn cam kết SLA ([SupportController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/SupportController.php), views: [support/index.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/support/index.blade.php), [support/show.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/support/show.blade.php)).
- [x] Máy trạng thái chuyển đổi trạng thái ticket an toàn (`canTransitionTo`), ghi nhận thời điểm phản hồi đầu tiên SLA và cờ ghi chú nội bộ (`is_internal_note`).
- [x] Theo dõi và xử lý các tác vụ nền (Queue Jobs) bị lỗi ([JobMonitorController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/JobMonitorController.php), view: [system/jobs.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/system/jobs.blade.php)) hỗ trợ thử lại đơn lẻ, thử lại tất cả (`queue:retry`) và dọn sạch hàng loạt (`purge`).
- [x] Cảnh báo huy hiệu số lượng ticket chờ xử lý và tác vụ lỗi theo thời gian thực trên Sidebar Menu và Command Palette `Ctrl+K`.
- [x] Thanh tra bộ nhớ lưu trữ công khai và phân loại dung lượng ([SettingController.php](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Admin/SettingController.php#L186), [settings/storage.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/settings/storage.blade.php)).
- [x] Viết bộ test `tests/Feature/AdminVNextWave07SupportAndOpsTest.php` (11 tests, 54 assertions pass 100%).

### Wave 08: Intelligence & Wedding Readiness Radar
- [x] Tích hợp công cụ Radar sẵn sàng ngày cưới: Tự động quét và chấm điểm sẵn sàng đa chiều ([WeddingReadinessRadarService.php](file:///d:/Project/Laravel/mayhen.vn/app/Services/WeddingReadinessRadarService.php), view: [radar.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/intelligence/radar.blade.php)) phát hiện thiệp sắp cưới thiếu địa điểm, thiếu ảnh, thiếu nhạc, quá tải bàn tiệc hoặc chưa xuất bản.
- [x] Action Inbox tập trung ([inbox.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/intelligence/inbox.blade.php)): Tự động gom tụ các việc quan trọng P0/P1/P2 cần giải quyết trong ngày từ khắp các phân hệ.
- [x] Thám tử đối soát doanh thu ([reconciliation.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/intelligence/reconciliation.blade.php)): Đối chiếu tự động số liệu đơn hàng và sao kê webhook SePay/VietQR, phát hiện chuyển thiếu/thừa tiền.
- [x] Widget Radar trực tiếp trên Dashboard ([dashboard.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/admin/pages/dashboard.blade.php)) cảnh báo ngay các đám cưới rủi ro cao trong 14 ngày tới.
- [x] Viết bộ test `tests/Feature/AdminVNextWave08DataAndIntelligenceTest.php` (6 tests, 41 assertions pass 100%).

### Wave 09: Hardening, Parity & Production Release Readiness
- [ ] Kiểm thử toàn diện 100% test cases với Pest.
- [ ] Kiểm định trực quan với ảnh chụp thực tế trên các breakpoint tiêu chuẩn.
- [ ] Hoàn thiện hồ sơ phát hành và bàn giao phiên bản sẵn sàng thương mại.

