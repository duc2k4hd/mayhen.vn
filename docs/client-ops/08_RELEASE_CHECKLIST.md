# 08 — RELEASE CHECKLIST (DANH MỤC NGHIỆM THU BÀN GIAO CLIENT OPERATIONS)

## 1. Nghiệp Vụ Khách Mời, RSVP & Số Liệu Đám Cưới
- [x] Tách bạch tuyệt đối 4 khái niệm số liệu qua `WeddingGuestMetricsService`: **Số lời mời** (`Guest::count()`) ≠ **Suất dự kiến** (`sum(expected_attendees)`) ≠ **Người xác nhận đến** (`người lớn + trẻ em`, khử trùng lặp đa sự kiện) ≠ **Khách đã điểm danh thực tế**.
- [x] Tỷ lệ phản hồi danh sách mời luôn nằm trong khoảng `0–100%`; phản hồi qua link công khai (`guest_id = null`) được hiển thị minh bạch riêng.
- [x] Cập nhật RSVP thủ công hộ khách (`manualRsvp`) chặn `0` người khi chọn tham dự, giữ nguyên lời chúc cũ khi không nhập ghi chú mới, lưu đầy đủ `GuestResponseLog` và `AuditLogger`.
- [x] Bảng kê Báo bếp & Hộ gia đình loại trừ chính xác toàn bộ khách đã báo bận (`declined`).
- [x] Nhập CSV hỗ trợ ô có xuống dòng, nhận diện đúng cột Bàn tiệc trước cột Họ tên, và toàn bộ 6 trình xuất CSV đều được bảo vệ chống CSV Formula Injection (`CsvSanitizerService`).

## 2. Phân Quyền, Bảo Mật & Quyền Riêng Tư (Luật 91/2025/QH15)
- [x] Khắc phục toàn bộ điểm Stored DOM XSS trong `guests.js`, `checkin.js`, `account-readiness.js`, `rsvp_history`, `duplicate_guests` và gói HTML ngoại tuyến.
- [x] Không lộ mã bí mật `?token={guestToken}` vào thẻ `og:url` khi chia sẻ liên kết; gắn đầy đủ `<meta name="referrer" content="strict-origin-when-cross-origin">`.
- [x] Lời chúc riêng tư (`is_private = true`) tuyệt đối không lộ công khai trên Thiệp, Live Wall, Tranh ký tên, Két kỷ niệm hay khi bấm `"Duyệt tất cả"`.
- [x] Chặn lách khóa `is_locked` của Quản trị viên tại `rollback` và `publish_diff`; chặn tải tệp `.svg`/`.php` giả dạng chữ ký; khóa PIN màn hình điều phối bàn tiệc sảnh cưới.
- [x] Thực thi đầy đủ cấu hình Trung tâm Quyền riêng tư (`anonymous_rsvp_allowed`, `bank_visibility`, `wishes_visibility`, `photo_drop_moderation`) và làm sạch PII trên cả `guest_responses` / `wedding_wishes` / `guest_photos`.

## 3. Giao Diện, Đa Thiết Bị & Interactive Product Tour
- [x] Sửa dứt điểm 5 modal bị kẹt ẩn (`Soát lỗi`, `Theo dõi gửi`, `Nhắc nhở`, `Lời cảm ơn`, `Độ sẵn sàng 7 bước`).
- [x] Giải tỏa quá tải nút bấm trên `/khach-moi` và `/tai-khoan/thiep`; ẩn thanh `bottom-nav` tiếp thị khỏi các trang quản lý và điểm danh; khôi phục CSS/JS trang Kế hoạch cưới; bổ sung chọn bàn 1-chạm trên di động.
- [x] Triển khai **Interactive Product Tour A (`/tai-khoan`)** và **Tour B (`/tai-khoan/thiep/{id}/khach-moi`)** theo phong cách Quiet Luxury có hiệu ứng lướt spotlight mượt, tách biệt 100% khỏi tour `/tao-thiep`, ghi nhớ trạng thái, có nút `Hướng dẫn` mở lại và tuyệt đối không chạm vào dữ liệu cưới.

## 4. Chất Lượng Mã Nguồn & Vệ Sinh Kho Lưu Trữ
- [x] Tuân thủ 100% Laravel Blade + Vanilla CSS (`mayhen_*`) + Native ES Modules (không cài thêm bất kỳ thư viện/framework ngoài nào).
- [x] Đã chạy `php vendor/bin/pint --dirty --format agent` sạch lỗi định dạng.
- [x] Đã dọn sạch dữ liệu kiểm thử tạm trong cơ sở dữ liệu phát triển và cập nhật `context.gemini.md`.
