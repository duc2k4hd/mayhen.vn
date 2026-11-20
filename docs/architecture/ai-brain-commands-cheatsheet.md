# Hướng Dẫn Nhanh Chạy Lệnh AI Brain V2 (Mây Hẹn)

> Bản tra cứu siêu gọn dành cho quản trị viên — copy là chạy được ngay trên Windows, Linux, VPS hoặc cPanel Shared Hosting.

---

## 1. Bắt Đầu Nhanh Lần Đầu (4 Bước)

1. **Chạy tạo bảng AI mới (nếu vừa cập nhật code):**
   ```bash
   php artisan migrate
   ```
2. **Kiểm tra sức khỏe hệ thống (DB, Lock, RAM, Model):**
   ```bash
   php artisan ai:brain:doctor
   ```
3. **Bật tự học & cấp ngân sách dương (> 0):**
   Vào trang quản trị **`/admin/ai-chatbot?tab=governance`**, bật công tắc **Bật AI Tự Học Nền 24/7**, chọn chế độ **Bảo thủ** và đặt **Ngân sách Token & Chi phí > 0** trong mục *Cài đặt nâng cao*, rồi bấm **Lưu cài đặt**.
   > *Lưu ý:* Mặc định ngân sách bằng `0` có nghĩa là **chặn chi tiêu an toàn**, AI sẽ không gọi API tự học cho tới khi bạn điền số lớn hơn `0`.
4. **Duyệt kiến thức mới:**
   Vào **`/admin/ai-chatbot?tab=learning`** để xem các câu hỏi AI đề xuất và bấm **`✓ Duyệt`** (hoặc **`✓ Duyệt tất cả`**). AI chỉ dùng tri thức sau khi bạn duyệt.

---

## 2. Lệnh Chạy Trên Máy Cá Nhân (Local Windows / Linux)

| Nhu cầu | Câu lệnh |
| :--- | :--- |
| **Chạy tự học liên tục** (tự nghỉ khi hết dữ liệu mới, bấm `Ctrl+C` để dừng an toàn) | `php artisan ai:brain:work` |
| **Chạy đúng 1 lượt ngắn rồi thoát** (test nhanh 20 giây, tối đa 2 tác vụ) | `php artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2` |
| **Xem trước dữ liệu sẽ quét** (chỉ đọc, không ghi gì vào DB) | `php artisan ai:brain:backfill --dry-run` |
| **Xem trạng thái, RAM, hàng đợi & cấp độ học L0–L4** | `php artisan ai:brain:status` |

---

## 3. Cài Đặt Chạy Tự Động Trên Hosting (cPanel / AZDIGI)

Trên hosting chia sẻ, **không chạy lệnh liên tục vô hạn**, chỉ cần chạy theo Cron hữu hạn:

- **Cách 1 (Khuyên dùng — dùng chung lịch Laravel có sẵn trong `routes/console.php`):**
  Hệ thống đã tự lên lịch chạy `ai:brain:work --once --budget-seconds=20 --max-tasks=2` **mỗi 5 phút**. Bạn chỉ cần giữ 1 dòng Cron duy nhất:
  ```cron
  * * * * * /usr/local/bin/php /home/USER_CPANEL/mayhen.vn/artisan schedule:run >> /dev/null 2>&1
  ```
- **Cách 2 (Cài Cron riêng có khóa `flock` chống chạy chồng):**
  ```cron
  */5 * * * * /usr/bin/flock -n /home/USER_CPANEL/ai-brain.lock /usr/local/bin/php /home/USER_CPANEL/mayhen.vn/artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2 >> /home/USER_CPANEL/mayhen.vn/storage/logs/ai-brain-cron.log 2>&1
  ```
  *(Thay `/usr/local/bin/php` và `/home/USER_CPANEL/mayhen.vn` đúng theo đường dẫn hosting của bạn.)*

---

## 4. Lệnh Cứu Hộ, Kiểm Tra An Toàn & Dọn Rác

| Tác vụ | Câu lệnh |
| :--- | :--- |
| **Tạm dừng khẩn cấp toàn bộ AI tự học** (khách hàng vẫn chat bình thường) | `php artisan ai:brain:pause` |
| **Cho AI tự học chạy lại & xóa bộ đếm lỗi** | `php artisan ai:brain:resume` |
| **Chạy 15 bài test kiểm tra an toàn & chống bịa** | `php artisan ai:brain:evaluate` |
| **Kiểm tra cấu hình, DB, khóa lock & tài nguyên** | `php artisan ai:brain:doctor` |
| **Dọn rác chống phình Database** (memory/log/đề xuất hết hạn/trùng lặp) | `php artisan mayhen:ai-retention-purge` |
