# MÂY HẸN — BÁO CÁO BẰNG CHỨNG KIỂM ĐỊNH THỰC TẾ GATE G1 (CORE DOMAIN, PRIVACY, FINANCIAL INTEGRITY)

- **Thời điểm kiểm định**: 2026-11-12 20:33:00 (Asia/Ho_Chi_Minh)
- **Môi trường**: PHP 8.5.10 (cli), Laravel 13.35.0, Pest PHP 3.x, SQLite In-Memory / File Test Engine
- **Trạng thái Gate G1**: **21 / 21 PASS (100%)**
- **Tổng số assertions**: **94 assertions** (Thời gian chạy: 13.08s)

---

## 1. Danh Sách Chi Tiết 21 Bài Kiểm Thử Gate G1

| Mã Gate | Test Case | File Kiểm Thử Thực Tế | Assertions | Kết Quả | Bằng Chứng / Cơ Chế Bảo Vệ Nghiệp Vụ |
|:---|:---|:---|:---:|:---:|:---|
| **G1-001** | Multi-event RSVP độc lập | `tests/Feature/MultiEventRsvpIntegrityTest.php` | 7 | **PASS** | Khách mời có thể tham dự Lễ Vu Quy nhưng từ chối Tiệc Cưới; trạng thái và sĩ số người đi kèm được lưu vết độc lập theo từng `event_id`. |
| **G1-002** | Khách cập nhật RSVP nhiều lần | `tests/Feature/MultiEventRsvpIntegrityTest.php` | 4 | **PASS** | Mỗi lần khách đổi số người hoặc trạng thái, hệ thống ghi bản ghi mới vào `guest_response_logs` (thay đổi `old_data` -> `new_data`), không ghi đè mất lịch sử. |
| **G1-003** | Khách ngoài nhóm không thấy sự kiện nội bộ | `tests/Feature/MultiEventRsvpIntegrityTest.php` | 3 | **PASS** | `WeddingEvent::forGuestScope` chỉ hiển thị sự kiện public hoặc sự kiện gán đúng `smart_group_id` của khách, chặn tuyệt đối việc rò rỉ sự kiện kín. |
| **G1-004** | Query `?rehearsal` không bypass scope | `tests/Feature/MultiEventRsvpIntegrityTest.php` | 5 | **PASS** | Tham số preview duyệt trước `?rehearsal=1` chỉ có hiệu lực với chủ thiệp đã đăng nhập; khách ngoài truy cập sẽ bị từ chối và không thấy sự kiện nội bộ. |
| **G1-005** | Thiệp locked / unpublished / expired chặn tương tác | `tests/Feature/InvitationLifecycleAndConcurrencySecurityTest.php` | 3 | **PASS** | Endpoint phụ bị chặn chuẩn xác theo state machine: 403 Forbidden khi thiệp bị khóa (`is_locked`), 404 khi chưa xuất bản, 410 Gone khi hết hạn. |
| **G1-006** | Draft chỉnh ngân hàng / chỗ cưới không rò rỉ | `tests/Feature/InvitationLifecycleAndConcurrencySecurityTest.php` | 3 | **PASS** | Khi sửa ngân hàng hoặc địa điểm trong Studio autosave, dữ liệu lưu trong `draft_config`; trang public chỉ đọc cấu hình live từ DB/snapshot, không rò rỉ bản nháp. |
| **G1-007** | Rollback phiên bản cũ bảo toàn dữ liệu phụ | `tests/Feature/InvitationLifecycleAndConcurrencySecurityTest.php` | 5 | **PASS** | `PublishDiffInspectorService::rollbackToRevision` khôi phục draft từ snapshot cũ nhưng bảo toàn nguyên vẹn 100% dữ liệu Khách mời, RSVP, Sự kiện và Chủ sở hữu. |
| **G1-008** | Concurrency autosave trả về HTTP 409 Conflict | `tests/Feature/InvitationLifecycleAndConcurrencySecurityTest.php` | 3 | **PASS** | Khi 2 tab cùng lưu nháp, tab gửi `revision_id` cũ hơn bị từ chối với HTTP 409 Conflict kèm `current_revision`, loại trừ race-condition ghi đè dữ liệu. |
| **G1-009** | Webhook SePay trùng transaction ID (Replay Attack) | `tests/Feature/FinancialAndPaymentIntegrityTest.php` | 5 | **PASS** | Webhook lặp lại cho cùng đơn hàng đạt Idempotent 200; nếu mã giao dịch ngân hàng đã dùng cho đơn này bị tái sử dụng cho đơn khác lập tức trả về 409 Conflict. |
| **G1-010** | Webhook tiền thiếu hoặc giao dịch chiều 'out' | `tests/Feature/FinancialAndPaymentIntegrityTest.php` | 7 | **PASS** | Chuyển thiếu tiền bị từ chối 400 Amount Mismatch; giao dịch chiều ra (`transferType: out`) trả về 200 xác nhận nhưng không kích hoạt gói phần mềm. |
| **G1-011** | Nút duyệt refund yêu cầu mã chuyển khoản thực tế | `tests/Feature/FinancialAndPaymentIntegrityTest.php` | 5 | **PASS** | Manager/Admin duyệt hoàn tiền bắt buộc phải nhập `payout_reference` từ ngân hàng thực tế; thiếu mã sẽ bị chặn validate ngay tại tầng Form Request. |
| **G1-012** | Tách biệt doanh thu Order với tiền mừng WeddingGift | `tests/Feature/FinancialAndPaymentIntegrityTest.php` | 3 | **PASS** | Doanh thu nền tảng Mây Hẹn (bảng `orders`) và tiền mừng của cô dâu chú rể (bảng `wedding_gifts`) được cô lập tuyệt đối trên cơ sở dữ liệu, không gộp lẫn kế toán. |
| **G1-013** | Concurrency Coupon dùng `lockForUpdate` | `tests/Feature/FinancialAndPaymentIntegrityTest.php` | 11 | **PASS** | Áp dụng coupon được bọc trong `DB::transaction` với `Coupon::lockForUpdate()`; khi hết hạn mức `usage_limit`, request song song tiếp theo trả về nguyên giá. |
| **G1-014** | Role Staff xuất PII bị 403; Super Admin bắt buộc `dsar_reason` | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 4 | **PASS** | Nhân viên Staff bị chặn 403 Forbidden; Quản trị viên cấp cao muốn xuất hồ sơ dữ liệu cá nhân (DSAR Luật 91/2025/QH15) bắt buộc nhập lý do >= 10 ký tự và ghi audit log. |
| **G1-015** | Tenant A cố can thiệp khách mời Tenant B | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 1 | **PASS** | `GuestController::authorizeGuestManagement` kiểm tra chặt chẽ chủ sở hữu qua `wedding->user_id` và session; người dùng khác cố truy cập bị `abort(404)`. |
| **G1-016** | Partner handover claim song song với atomic lock | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 5 | **PASS** | Quá trình nhận bàn giao đám cưới từ Studio diễn ra trong transaction với `lockForUpdate()`; claim thành công xóa mã ngay lập tức; mã tái sử dụng bị từ chối. |
| **G1-017** | Sao lưu và kiểm thử phục hồi toàn vẹn (OPS-01) | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 5 | **PASS** | `SystemBackupService::createDatabaseBackup` tạo snapshot DB, xác minh SHA-256 Checksum trùng khớp 100% và chạy `PRAGMA integrity_check` trên SQLite sandbox đạt `ok`. |
| **G1-018** | SupportTicketReply quan hệ `user()` BelongsTo | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 3 | **PASS** | Khắc phục triệt để quan hệ `user()` trên `SupportTicketReply`, đảm bảo hiển thị phản hồi hỗ trợ qua lại giữa khách hàng và nhân viên không phát sinh lỗi 500. |
| **G1-019** | AI usage preview che giấu PII nhạy cảm | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 5 | **PASS** | `AiQualityAndCostObservatoryService::sanitizePiiForPreview` tự động làm mờ số điện thoại Việt Nam (`090****4567`), email (`kha***@gmail.com`), số tài khoản ngân hàng. |
| **G1-020** | Đổi cấu hình ngân hàng nền tảng yêu cầu Super Admin | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 2 | **PASS** | Tuyến cấu hình tài khoản thu hộ VietQR/SePay được bảo vệ bởi middleware `admin.super`; nhân viên Staff bị chặn 403; Super Admin cập nhật được ghi log có che mờ secret. |
| **G1-021** | Chống CSV Formula Injection và chuẩn hóa UTF-8 | `tests/Feature/PrivacyAndDomainIntegrityTest.php` | 5 | **PASS** | `CsvSanitizerService::sanitize` tự động nhận diện và escape dấu nháy đơn (`'`) trước các tiền tố công thức nguy hiểm (`=`, `+`, `-`, `@`), bảo vệ người dùng Excel/Sheets. |

---

## 2. Kết Luận Kiểm Định Gate G1

- **Tỷ lệ vượt qua**: **21 / 21 (100.0%)**
- **Toàn vẹn tệp kiểm thử**: Cả 4 tệp Feature Test đã được ghi nhận trực tiếp vào mã nguồn và khóa whitelist trong `.gitignore`.
- **Chất lượng mã nguồn**: Toàn bộ đã được định dạng và kiểm tra cú pháp bằng công cụ Pint của Laravel.
- **Sẵn sàng chuyển tiếp**: Gate G1 chính thức hoàn thành và đạt tiêu chuẩn Release Candidate. Tiếp tục chuyển sang Gate G2 (Wedding-Day E2E & Customer Lifecycle: G2-001 đến G2-013).

