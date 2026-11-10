# 🌊 MÂY HẸN — ADMIN VNEXT IMPLEMENTATION WAVES
## LỘ TRÌNH 10 LÀN SÓNG TRIỂN KHAI HỆ THỐNG QUẢN TRỊ ATELIER COMMAND CENTER

> **Triết lý**: Triển khai theo từng lát cắt nghiệp vụ (vertical slices) hoàn chỉnh, từ bảo mật/tiền bạc cốt lõi đến hoàn thiện giao diện, mở rộng nghiệp vụ và kiểm thử thực tế. Không tạo menu rỗng.

---

## TỔNG QUAN 10 LÀN SÓNG (WAVE 00 → WAVE 09)

| Wave | Tên làn sóng | Mục tiêu trọng tâm | Trạng thái |
|:---:|---|---|:---:|
| **Wave 00** | **Evidence & Inventory** | Kiểm kê 100% routes, controls, permissions, thiết lập catalog CSV, sổ bug | **COMPLETED** |
| **Wave 01** | **P0 Security & Money** | Vá 4 lỗi P0/P1, củng cố RBAC, đối soát ngân hàng bắt buộc, bảo vệ Super Admin | **COMPLETED** |
| **Wave 02** | **Admin Shell + UX Foundations** | Tối ưu hóa layout Quiet Luxury, phím tắt Escape, data tables, modals, filters | **COMPLETED** |
| **Wave 03** | **Users & Invitations** | Quản trị dự án cưới, chi tiết khách hàng 360, hỗ trợ xem thiệp an toàn | **READY** |
| **Wave 04** | **Templates & AI Curated** | Quản trị kho mẫu, Schema QA, 7 phần Curated Native, kiểm soát asset địa phương | **QUEUED** |
| **Wave 05** | **Guest & Wedding Ops** | Quản lý khách mời, RSVP, sơ đồ bàn tiệc, quầy check-in, duyệt lời chúc | **QUEUED** |
| **Wave 06** | **Commerce & Growth** | Quản trị gói cước, gia hạn, coupon khuyến mãi, SEO & nội dung marketing | **QUEUED** |
| **Wave 07** | **Support & Ops** | Trung tâm hỗ trợ Ticket SLA, quản lý Job queue, giám sát lưu trữ, cảnh báo | **QUEUED** |
| **Wave 08** | **Data & Intelligence** | Dashboard phân tích, Wedding Readiness Radar, phát hiện sai lệch báo cáo | **QUEUED** |
| **Wave 09** | **Hardening & UAT** | Kiểm thử hồi quy toàn diện cross-module, responsive đa màn hình, kiểm định phát hành | **QUEUED** |

---

## CHI TIẾT TỪNG LÀN SÓNG

### Wave 00: Evidence & Inventory (Hiện tại)
- [x] Lập bản đồ 50 endpoints và kiểm kê toàn bộ routes, controllers, middleware, permissions (`01_RUNTIME_ADMIN_INVENTORY.md`).
- [x] Thiết lập Sổ đăng ký lỗi kiểm toán (`02_ADMIN_BUG_REGISTER.md`) ghi nhận 4 lỗi P0/P1.
- [x] Tạo lập `MAY_HEN_ADMIN_IMPLEMENTATION_WAVES.md` và `MAY_HEN_ADMIN_INNOVATION_BACKLOG.md`.
- [x] Khởi tạo catalog phân rã `MAY_HEN_ADMIN_ATOMIC_FUNCTIONS.csv` và `MAY_HEN_ADMIN_TEST_CASES.csv`.

### Wave 01: P0 Security & Money
- [ ] Khắc phục `BUG-ADM-001`: Cho phép role `staff` đăng nhập qua form `/admin/login` thông qua `canAccessAdminPanel()`.
- [ ] Khắc phục `BUG-ADM-002`: Bảo vệ tài khoản Super Admin duy nhất khỏi việc bị hạ quyền.
- [ ] Khắc phục `BUG-ADM-003`: Bắt buộc nhập mã tham chiếu giao dịch ngân hàng và lý do đối soát khi duyệt đơn thủ công.
- [ ] Khắc phục `BUG-ADM-004`: Khử độc/masking mảng `details` trong `AuditLogger` và ghi nhận nhật ký đăng nhập/đăng xuất.
- [ ] Viết bộ test `tests/Feature/AdminVNextWave00AuditAndAuthTest.php` xác thực 100% các bản sửa lỗi.

### Wave 02: Admin Shell & UX Foundations
- [ ] Hoàn thiện trải nghiệm bàn phím (Escape đóng modal/drawer, phím tắt tìm kiếm).
- [ ] Cải thiện độ tương phản bảng dữ liệu, trạng thái rỗng (empty state) giàu thông điệp hướng dẫn.
- [ ] Tối ưu hóa responsive trên laptop màn hình thấp và thiết bị di động (375px, 430px, 768px).

### Wave 03: Users & Invitations Operations
- [ ] Hoàn thiện thông tin Customer 360 (kết nối đơn hàng, thiệp cưới và nhật ký).
- [ ] Bổ sung cơ chế xem thiệp khách (Safe Support View) dưới quyền read-only, che dấu PII.
- [ ] Quản lý chu kỳ khóa / mở khóa và gia hạn thiệp cưới kèm audit trail.

### Wave 04: Templates Engine & Native Curated Governance
- [ ] Nâng cấp bảng thanh tra Schema Inspector hiển thị trạng thái của 10 Canonical Sections.
- [ ] Kiểm soát 100% tài sản cục bộ (Local Assets), phát hiện link ngoài hoặc file bị thiếu.
- [ ] Bảo toàn nguyên lý AI Curated: Tái dùng nguyên vẹn Blade/CSS/JS mẫu nguồn, chỉ hiển thị 7 phần tiêu chuẩn.

### Wave 05: Guest CRM, Seating & Wedding Day Ops
- [ ] Đồng bộ dữ liệu phản hồi RSVP và sức chứa bàn tiệc thời gian thực.
- [ ] Kiểm duyệt lời chúc có bộ lọc ngôn từ thông minh, tránh chặn nhầm từ ngữ chúc phúc trang nhã.
- [ ] Giám sát quầy lễ tân tiếp đón và quét mã QR không bị trùng lặp sĩ số.

### Wave 06: Commerce, Subscriptions & SEO
- [ ] Cấu hình gói dịch vụ và bảng giá với bản chụp snapshot lịch sử cho đơn cũ.
- [ ] Cơ chế quản lý mã giảm giá (Coupon) chống gian lận và cộng dồn sai quy định.
- [ ] Tối ưu hóa các trang nội dung tĩnh, SEO meta tags và sơ đồ sitemap công khai.

### Wave 07: Support Center, Jobs & System Diagnostics
- [ ] Khung tiếp nhận và xử lý yêu cầu hỗ trợ (Support Ticket) theo thời hạn cam kết SLA.
- [ ] Theo dõi và xử lý các tác vụ nền (Queue Jobs) bị lỗi, không retry dồn dập.
- [ ] Báo cáo dung lượng lưu trữ công khai và cảnh báo sớm tài nguyên.

### Wave 08: Intelligence & Wedding Readiness Radar
- [ ] Tích hợp công cụ Radar sẵn sàng ngày cưới: Tự động phát hiện thiệp sắp cưới nhưng chưa có địa điểm, thiếu nhạc, ảnh lỗi hoặc lệch ngày.
- [ ] Action Inbox tập trung: Tự động gom các việc quan trọng P0/P1 cần giải quyết trong ngày.
- [ ] Đối soát doanh thu khớp 100% giữa số liệu đơn hàng và sổ cái thanh toán.

### Wave 09: Hardening, Parity & Production Release Readiness
- [ ] Kiểm thử toàn diện 100% test cases với Pest.
- [ ] Kiểm định trực quan với ảnh chụp thực tế trên các breakpoint tiêu chuẩn.
- [ ] Hoàn thiện hồ sơ phát hành và bàn giao phiên bản sẵn sàng thương mại.

