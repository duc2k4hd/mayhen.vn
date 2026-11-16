---
id: ADM-SYS-003
title: "Quy trình sao lưu toàn diện (System Backup) và diễn tập khôi phục thảm họa (Disaster Recovery)"
category: "Hệ thống, Sức khỏe, Sao lưu & Nghiệm thu"
summary: "Hướng dẫn chi tiết quy trình tạo bản sao lưu cơ sở dữ liệu, kiểm tra tính toàn vẹn SHA-256 Checksum, chạy kiểm thử phục hồi độc lập (OPS-01) và diễn tập khôi phục hệ thống khi gặp thảm họa."
reader: "PER-ADM-03 (Quản trị viên Cấp cao)"
order: 3
updated_at: "2026-11-15"
tags: [backup, disaster-recovery, restore, checksum, ops-01, integrity]
mapped_page: "ADM-SYS-BACKUP"
mapped_route: "admin.settings.backups.index"
risk_level: "CRITICAL"
required_role: "admin.super"
---

# Quy trình sao lưu toàn diện (System Backup) và diễn tập khôi phục thảm họa (Disaster Recovery)

> [!WARNING]
> **Phân quyền thực hiện:** Chỉ dành riêng cho Quản trị viên Cấp cao (`admin.super`).  
> **Route quản trị:** `GET /admin/settings/backups` (`admin.settings.backups.index`).  
> **Tiêu chuẩn kiểm thử:** Tuân thủ nghiệm thu cổng kỹ thuật G1-017 & G4-003 (OPS-01 Verification Gate).


![Giao diện quản lý Quy trình sao lưu toàn diện (System Backup) và diễn tập khôi phục thảm họa (Disaster Recovery)](/assets/client/docs/images/admin/10-system-health/adm-sys-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích Nghiệp vụ & Tiêu chuẩn RPO / RTO

Dữ liệu thiệp cưới, danh sách khách mời, quà mừng và lịch sử thanh toán là tài sản vô giá của người dùng Mây Hẹn. Để bảo vệ dữ liệu trước các nguy cơ lỗi phần cứng, sự cố trung tâm dữ liệu hoặc tấn công mạng, hệ thống áp dụng tiêu chuẩn vận hành khắt khe:
- **RPO (Recovery Point Objective):** Không quá 24 giờ đối với sao lưu định kỳ tự động và 0 phút đối với sao lưu snapshot trước mỗi đợt bảo trì nâng cấp.
- **RTO (Recovery Time Objective):** Dưới 30 phút để khôi phục toàn bộ dịch vụ hoạt động bình thường trên máy chủ dự phòng.
- **OPS-01 Verification:** 100% bản sao lưu trước khi được lưu kho đều phải trải qua kiểm thử nạp sandbox và xác minh mã băm SHA-256.

---

## 2. Bảng Điều khiển Sao lưu (Disaster Recovery Cockpit)

Khi truy cập `/admin/settings/backups`, quản trị viên nắm bắt nhanh 5 chỉ số:
1. **Tổng số bản sao lưu (Total Backups):** Số lượng tệp snapshot hiện có trên đĩa cứng.
2. **Tổng dung lượng chiếm dụng (Formatted Total Size):** Tính bằng MB hoặc GB giúp chủ động quản lý ổ đĩa máy chủ.
3. **Bản sao lưu gần nhất (Latest Backup):** Tên tệp và thời điểm tạo bản backup mới nhất.
4. **Tỷ lệ kiểm thử thành công (% Verified):** Tỷ lệ phần trăm các bản backup đã vượt qua bài test phục hồi độc lập.
5. **Số bản đã xác minh (Verified Count):** Số lượng bản sao lưu sẵn sàng phục hồi ngay lập tức khi xảy ra sự cố.

---

## 3. Quy trình Tạo Bản Sao lưu Mới (Snapshot Creation)

Bắt buộc thực hiện trước mọi đợt deploy phiên bản mới, chạy migration cơ sở dữ liệu lớn hoặc can thiệp hàng loạt dữ liệu:

1. **Bước 1:** Truy cập `/admin/settings/backups`.
2. **Bước 2:** Tại khung *Tạo bản Snapshot Cơ sở dữ liệu*, nhập ghi chú mô tả vào ô `note` (Ví dụ: *"Backup trước khi phát hành tính năng Sơ đồ bàn tiệc V2"*).
3. **Bước 3:** Nhấp **[Tạo bản sao lưu ngay]** (`POST /admin/settings/backups/create`).
4. **Bước 4:** `SystemBackupService` sẽ tiến hành:
   - Khóa đọc nhất thời hoặc xuất bản dump dữ liệu nguyên tử (Atomic Database Dump).
   - Đặt tên tệp theo quy chuẩn: `mayhen-backup-YYYY-MM-DD-His-{random}.sqlite` (hoặc `.sql`).
   - Tính toán mã băm SHA-256 (`hash_file('sha256', ...)`) và lưu vào bảng `system_backups`.
   - Ghi nhật ký kiểm toán `BACKUP_CREATED` kèm User ID của quản trị viên khởi tạo.
5. **Bước 5:** Màn hình thông báo thành công hiển thị tên tệp và dung lượng vừa tạo.

---

## 4. Kiểm Thử Phục Hồi Độc Lập (OPS-01 Verification SOP)

Một bản sao lưu chưa được kiểm thử phục hồi là một bản sao lưu vô giá trị. Tính năng **Kiểm thử độc lập** (`POST /admin/settings/backups/{id}/verify`) thực hiện tự động trong sandbox:

```
┌────────────────────────────────────────────────────────────────────────┐
│   TIẾN TRÌNH KIỂM THỬ PHỤC HỒI ĐỘC LẬP (OPS-01 VERIFICATION)           │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Xác minh file vật lý trên đĩa cứng: TỒN TẠI                          │
│ 2. Tính toán lại mã băm SHA-256: TRÙNG KHỚP 100%                       │
│ 3. Khởi tạo kết nối PDO Sandbox độc lập                                │
│ 4. Chạy câu lệnh kiểm tra: PRAGMA integrity_check => OK                │
│ 5. Đếm số lượng bảng và đối chiếu bảng lõi:                            │
│    - users: HỢP LỆ                                                     │
│    - invitations: HỢP LỆ                                               │
│    - orders: HỢP LỆ                                                    │
│    - platform_settings: HỢP LỆ                                         │
│ 6. Cập nhật verified_at, lưu Verification Log & ghi Audit Log          │
└────────────────────────────────────────────────────────────────────────┘
```

- **Thao tác:** Trong danh sách bản sao lưu, tìm dòng backup cần kiểm tra và nhấp nút **[Kiểm thử phục hồi]**.
- **Kết quả mong đợi:** Cột trạng thái hiển thị huy hiệu xanh lá `Đã xác minh (Verified)` kèm dấu thời gian và nút xem chi tiết nhật ký kiểm tra (`Verification Log`).

---

## 5. Kịch Bản Diễn Tập Khôi Phục Thảm Họa (DR Drill Playbook)

Định kỳ mỗi quý 1 lần, đội ngũ kỹ thuật thực hiện diễn tập khôi phục trên môi trường Staging:

### Bước 1: Chuẩn bị tệp khôi phục
- Đăng nhập vào trang quản trị Staging, truy cập `/admin/settings/backups`.
- Chọn bản sao lưu có trạng thái `Verified` gần nhất và nhấp **[Tải về]** (`admin.settings.backups.download`).

### Bước 2: Bật chế độ bảo trì máy chủ
```bash
php artisan down --secret="mayhen-recovery-access-token"
```

### Bước 3: Nạp dữ liệu vào cơ sở dữ liệu dự phòng
- Đối với SQLite:
  ```bash
  cp storage/app/backups/mayhen-backup-target.sqlite database/database.sqlite
  chmod 664 database/database.sqlite
  ```
- Đối với MySQL:
  ```bash
  mysql -u mayhen_user -p mayhen_db < storage/app/backups/mayhen-backup-target.sql
  ```

### Bước 4: Kiểm tra tính toàn vẹn ứng dụng (Integrity Verification)
```bash
php artisan test tests/Feature/PrivacyAndDomainIntegrityTest.php --compact
```
- Kiểm tra đăng nhập tài khoản quản trị.
- Kiểm tra hiển thị thiệp cưới ngẫu nhiên của 3 khách hàng.
- Kiểm tra danh sách đơn hàng và số dư ví hoa hồng đối tác.

### Bước 5: Mở lại hệ thống cho người dùng
```bash
php artisan up
```

---

## 6. Chính Sách Lưu Trữ & Dọn Dẹp Bản Cũ (Retention Policy)

Để tránh cạn kiệt dung lượng ổ cứng máy chủ:
- Hệ thống áp dụng chính sách lưu trữ tối đa **10 bản sao lưu gần nhất**.
- Quản trị viên có thể chủ động nhấp nút **[Dọn dẹp bản cũ]** (`POST /admin/settings/backups/clean-old`).
- Lệnh sẽ tự động xóa các bản sao lưu cũ vượt quá số lượng cho phép, đồng thời xóa tệp vật lý tương ứng trên đĩa cứng và lưu lại vết kiểm toán `BACKUP_CLEANED_OLD`.

---

## 7. Những điều Tuyệt đối Bị cấm

1. **CẤM** tải tệp sao lưu về máy tính cá nhân nếu máy tính chưa bật mã hóa ổ đĩa (BitLocker / FileVault) hoặc gửi tệp qua các ứng dụng chat không bảo mật (Telegram, Facebook Messenger, Zalo cá nhân).
2. **CẤM** xóa bản sao lưu duy nhất đang có trạng thái `Verified` trong hệ thống.
3. **CẤM** thực hiện lệnh khôi phục (Restore) trực tiếp lên máy chủ Production mà không có sự hiện diện và giám sát của Lead Engineer.

---

## 8. Bài viết Liên quan

- [ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-001.md)
- [ADM-SYS-002: Giám sát hàng đợi tác vụ (Job Center) và xử lý failed jobs](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-002.md)
- [ADM-SYS-004: Bảng điều khiển Nghiệm thu Thương mại (Admin Acceptance Audit Cockpit)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-004.md)
