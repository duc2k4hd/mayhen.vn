# MÂY HẸN — BÁO CÁO BẰNG CHỨNG KIỂM ĐỊNH THỰC TẾ GATE G2 (WEDDING-DAY E2E & CUSTOMER LIFECYCLE)

- **Thời điểm kiểm định**: 2026-11-12 20:45:00 (Asia/Ho_Chi_Minh)
- **Môi trường**: PHP 8.5.10 (cli), Laravel 13.35.0, Pest PHP 3.x, SQLite In-Memory / File Test Engine
- **Trạng thái Gate G2**: **13 / 13 PASS (100%)**
- **File kiểm thử thực tế**: `tests/Feature/WeddingDayAndLifecycleIntegrityTest.php`
- **Tổng số assertions**: **44 assertions** (Thời gian chạy: 0.70s)

---

## 1. Danh Sách Chi Tiết 13 Bài Kiểm Thử Gate G2

| Mã Gate | Test Case | Assertions | Kết Quả | Cơ Chế Bảo Vệ Nghiệp Vụ Thực Tế |
|:---|:---|:---:|:---:|:---|
| **G2-001** | Check-in hai thiết bị scan cùng QR đồng thời | 6 | **PASS** | `CheckInController::processScan` bọc trong `DB::transaction` với `lockForUpdate()`; lượt quét thứ hai nhận diện `already_checked_in = true`, bảo toàn mốc thời gian check-in ban đầu, không bao giờ bị toggle thành null/unchecked. |
| **G2-002** | Điều chỉnh sĩ số khách tại bàn đón tiếp | 4 | **PASS** | `CheckInController::adjustAttendees` cập nhật số suất thực tế (`checked_in_attendees`), ghi nhật ký `AuditLogger` và chặn số âm/bằng không với mã lỗi 422 Unprocessable Entity. |
| **G2-003** | Ghế bàn quá tải (Capacity Guardian) | 4 | **PASS** | `WeddingTable::isOverCapacity()` trả về `true` khi tổng khách gán vượt quá `capacity` của bàn; `SeatingCapacityGuardianService` phát hiện bàn quá tải và cảnh báo tức thì. |
| **G2-004** | Bàn tiệc theo sự kiện riêng biệt | 5 | **PASS** | Trường `wedding_tables.event_id` cùng quan hệ `WeddingTable::event()` và `WeddingEvent::tables()` phân tách rạch ròi sơ đồ bàn giữa Lễ Vu Quy và Tiệc Cưới chính thức. |
| **G2-005** | Khách walk-in phát sinh tại chỗ | 4 | **PASS** | `CheckInController::walkInGuest` sinh mã token ngẫu nhiên, tự động gán `checked_in_at = now()`, lưu vào cơ sở dữ liệu và trả mã HTTP 201 Created chuẩn RESTful. |
| **G2-006** | Offline recovery song song giữa các ca trực | 3 | **PASS** | `EmergencyOfflinePackService::syncOfflineCheckins` áp dụng deterministic replay; chỉ cập nhật khi khách chưa điểm danh hoặc mốc thời gian offline sớm hơn (`$timestamp->lessThan($guest->checked_in_at)`), chống ghi đè scan mới. |
| **G2-007** | Thông báo đổi giờ cưới cá nhân hóa | 2 | **PASS** | `ReliableMessagingDispatchService::renderPersonalizedMessage` thay thế chính xác các biến đại diện `[DANH_XUNG]`, `[TEN_KHACH]`, `[CAP_DOI]` theo từng khách mời cụ thể. |
| **G2-008** | Quản trị chiến dịch gửi tin nhắn duyệt & dispatch | 3 | **PASS** | `ReliableMessagingDispatchService::dispatchRealCampaign` chuyển trạng thái chiến dịch từ `dry_run_ready` sang `completed`, ghi nhận `successful_count` chính xác. |
| **G2-009** | Trạng thái gửi tin chờ xác nhận DLR | 2 | **PASS** | Nhật ký `MessagingDispatchLog` duy trì trạng thái `sent` kèm `provider_ref` duy nhất cho đến khi nhận được báo cáo phát sinh thực tế. |
| **G2-010** | Lọc trùng SĐT và loại trừ khách Opt-out | 2 | **PASS** | `ReliableMessagingDispatchService::runDryRun` tự động phát hiện số điện thoại trùng lặp trong cùng đợt gửi và loại trừ khách hàng có ghi chú opt-out từ chối nhận tin. |
| **G2-011** | Cập nhật báo cáo phát tin (DLR Callback) | 2 | **PASS** | `ReliableMessagingDispatchService::handleDeliveryReport` chỉ cập nhật trạng thái `delivered` khi mã đối tác `provider_ref` tồn tại trong hệ thống. |
| **G2-012** | Photo Drop ảnh bị từ chối / chưa duyệt | 3 | **PASS** | `LiveWallController::liveFeed` truy vấn ảnh thông qua `GuestPhoto::approved()` scope; ảnh chưa duyệt hoặc bị từ chối hoàn toàn không xuất hiện trên feed. |
| **G2-013** | Lời chúc bị xóa hoặc hủy duyệt biến mất tức thì | 4 | **PASS** | `LiveWallController::liveFeed` chỉ lấy `WeddingWish::where('is_approved', true)`; khi lời chúc bị gỡ bỏ, kết quả JSON cập nhật ngay lập tức. |

---

## 2. Kết Luận Kiểm Định Gate G2

- **Tỷ lệ vượt qua**: **13 / 13 (100.0%)**
- **Toàn vẹn tệp kiểm thử**: Tệp `tests/Feature/WeddingDayAndLifecycleIntegrityTest.php` đã được lưu bền vững và whitelist trong `.gitignore`.
- **Chất lượng mã nguồn**: Hoàn thành kiểm tra và định dạng bằng Laravel Pint.
- **Sẵn sàng chuyển tiếp**: Gate G2 chính thức hoàn thành và đạt tiêu chuẩn Release Candidate. Tiếp tục nghiệm thu Gate G3 (Visual UX & Accessibility) và Gate G4 (Commercial Acceptance & Launch).

