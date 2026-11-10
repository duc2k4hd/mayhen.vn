# MÂY HẸN (MAYHEN.VN) — TEST BASELINE REPORT

- **Thời gian ghi nhận**: 2026-11-10T08:50:00+07:00
- **Lệnh thực thi**: `vendor/bin/pest` / `php artisan test --compact`
- **Kết quả Hiện Tại**:
  * **Passed**: 653 tests
  * **Failed**: 0 tests
  * **Errors**: 0
  * **Assertions**: 3,364 assertions
  * **Thời gian chạy**: ~13.1s
  * **Database Engine**: SQLite `:memory:`
- **Mức độ bao phủ hiện có**:
  * Feature tests: 62 test files
  * Unit tests: 4 test files
  * Phạm vi bao phủ: Authentication, Curated Flow, CheckIn, Seating, Guest RSVP, Payment Webhook, Resilience Kit, Security Remediation, Content Moderation, Concurrency & Spike Load, Cross-module E2E Deep Scenarios (DEEP-01 to DEEP-20).
- **Tiêu chuẩn nghiệm thu**:
  * Mọi kiểm thử mới phải được thêm vào `tests/Feature` hoặc `tests/Unit`.
  * Không bao giờ được làm giảm số lượng test passing hoặc sửa assertion để che lỗi.

