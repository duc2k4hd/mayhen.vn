# MÂY HẸN (MAYHEN.VN) — RUNTIME INVENTORY & QA RECONNAISSANCE

- **Thời gian lập**: 2026-11-09T20:13:00+07:00
- **Môi trường**: Local Windows (PHP 8.5.10, Laravel 13.35.0, SQLite `:memory:` for testing)
- **Git Branch**: `main` | **Git Commit**: `3810436`
- **Tổng số Route ứng dụng**: 222 routes (đã lọc trừ vendor)
- **Tổng số Test Baseline**: 521 tests / 2,518 assertions (100% Passed)

## 1. MẪU THIỆP CƯỚI NATIVE THỰC CÓ TRONG REPOSITORY
1. `giua-muon-ngan-vi-sao` (Giữa Muôn Ngàn Vì Sao)
2. `hanh-phuc-mang-ten-chung-minh` (Hạnh Phúc Mang Tên Chúng Mình)
3. `ngay-tinh-yeu-dom-hoa` (Ngày Tình Yêu Đơm Hoa)
4. `tron-kiep-nay-ben-nhau` (Trọn Kiếp Này Bên Nhau)
5. `mayhen-curated` (Mây Hẹn Curated — Khung 7 Section chọn lọc tinh tuyển)

## 2. PHÂN BỐ ROUTE THEO KHÔNG GIAN (222 ROUTES)
- **Công Khai & Khám Phá (Client Public)**: Home, Giới thiệu, Bảng giá, Kho mẫu (`/mau-thiep`), Xem thử demo (`/xem-thu/{slug}`), Tư vấn gói cưới.
- **Tạo Thiệp & AI Curated Studio**: `/tao-thiep`, `/tao-thiep-tu-dong`, `/tao-thiep-tu-dong/xem-thu/{slug}`, `/api/curated/*`.
- **Thiệp Mời Công Khai & Tương Tác Khách**: `/thiep/{slug}`, RSVP (`/thiep/{slug}/rsvp`), Sổ lưu bút (`/thiep/{slug}/wishes`), Mở khóa mật mã (`/thiep/{slug}/mo-khoa`), Hộp thời gian công khai (`/thiep/{slug}/hop-thoi-gian`).
- **Thanh Toán & Webhook SePay/VietQR**: `/thanh-toan/{planSlug}`, `/api/checkout/process`, `/api/checkout/status/{orderCode}`, `/thanh-toan/thanh-cong/{orderCode}`, `/api/payments/sepay/webhook`.
- **Cổng Điểm Danh Lễ Tân & Màn Hình Chiếu**: `/diem-danh-tiec/{slug?}`, `/diem-danh/{token}`, `/trinh-chieu/{slug?}`.
- **Không Gian Cặp Đôi (Client Account Portal - `/tai-khoan`)**:
  * Quản lý thiệp, cài đặt, xuất bản, nhân bản, khôi phục khẩn cấp.
  * CRM Khách mời: danh sách, thêm, sửa, xóa, nhập nhanh, xuất CSV, in danh sách, lập kế hoạch nhắc nhở, thư cảm ơn.
  * Điều phối ngày cưới: Live Command Center, Radar sẵn sàng tuần lễ cưới.
  * Sơ đồ xếp bàn tiệc trực quan: Seating Planner Studio, hoán đổi ghế, kiểm tra sức khỏe bàn.
  * Két lưu trữ kỷ niệm: Digital Memory Vault, Photo Drop ngày cưới, Sách kỷ yếu, Xuất ngoại tuyến.
  * Sổ mừng cưới & Tài chính: Ghi nhận mừng cưới, QR mừng cưới, xuất CSV.
  * Tên miền riêng: Vanity Subdomain, CNAME DNS verification.
  * Quyền riêng tư & Bảo mật: Trung tâm minh bạch đồng thuận (Luật 91/2025/QH15), Kiểm toán dữ liệu cá nhân.
- **Không Gian Đối Tác & Studio B2B (`/doi-tac`, `/tai-khoan/nhan-thiep-doi-tac`)**:
  * Đăng ký đối tác, quản lý danh sách thiệp bàn giao, chia sẻ hoa hồng, mã bàn giao 6 ký tự.
- **Không Gian Quản Trị Hệ Thống (`/admin`)**:
  * Dashboard KPI, Quản lý thiệp cưới khách hàng, Khóa/Mở khóa thiệp, Di trú mẫu thiệp, Xuất dữ liệu.
  * Quản lý Mẫu thiệp, Quét tự động Discovery, Bật/Tắt mẫu, Ghim nổi bật.
  * Quản lý Đơn hàng, Webhook audit, Phê duyệt thủ công, Hoàn tiền, Hủy đơn.
  * Quản lý Người dùng, Đổi vai trò, Đặt lại mật khẩu.
  * Quản lý Lời chúc & Giám sát RSVP, Duyệt/Ẩn lời chúc.
  * Cấu hình Hệ thống, Bảng giá gói cước, Tài khoản thụ hưởng VietQR, Thanh tra bộ nhớ, Audit Logs.

