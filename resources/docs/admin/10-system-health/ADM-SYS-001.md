---
id: ADM-SYS-001
title: "Kiểm tra bảng sức khỏe hệ thống (System Health Check): CPU, RAM, Disk, DB Connection"
category: "Hệ thống, Sức khỏe, Sao lưu & Nghiệm thu"
summary: "Hướng dẫn quản trị viên cấp cao kiểm tra trạng thái sức khỏe hạ tầng thời gian thực, đo lường độ trễ kết nối CSDL, Cache, dung lượng đĩa và chẩn đoán nhật ký lỗi hệ thống Mây Hẹn."
reader: "PER-ADM-03 (Quản trị viên Cấp cao)"
order: 1
updated_at: "2026-11-15"
tags: [system, health, monitoring, database, redis, storage, logs]
mapped_page: "ADM-SYS-PAGE"
mapped_route: "admin.system.health.index"
risk_level: "CRITICAL"
required_role: "admin.super"
---

# Kiểm tra bảng sức khỏe hệ thống (System Health Check): CPU, RAM, Disk, DB Connection

> [!WARNING]
> **Phân quyền thực hiện:** Chỉ dành cho Quản trị viên Cấp cao (`admin.super`).  
> **Route quản trị:** `GET /admin/system/health` (`admin.system.health.index`).  
> **Tác vụ tác động nhạy cảm:** Tải nhật ký lỗi (`download-log`) và Làm sạch log (`clear-log`) có ghi vết kiểm toán toàn diện.

---

## 1. Mục đích Nghiệp vụ & Phạm vi Giám sát

Hạ tầng Mây Hẹn vận hành liên tục 24/7 để phục vụ hàng chục ngàn khách mời truy cập cùng lúc vào các dịp cao điểm mùa cưới. Bảng điều khiển Sức khỏe Hệ thống (**System Health Dashboard**) cung cấp bức tranh chẩn đoán tức thời về độ ổn định của:
- **Cơ sở dữ liệu (Database):** Độ trễ kết nối (Ping Latency tính bằng ms qua truy vấn `SELECT 1`).
- **Bộ nhớ đệm (Cache Engine):** Tốc độ ghi/đọc (`put` / `get` latency) và tính toàn vẹn của dữ liệu tạm.
- **Không gian lưu trữ (Disk Storage):** Dung lượng ổ đĩa khả dụng, tỷ lệ sử dụng thực tế và quyền ghi (`is_writable`) vào thư mục `storage/`.
- **Hàng đợi tác vụ (Queue Worker):** Số lượng tác vụ thất bại (`failed_jobs`) và hàng đợi tồn đọng (`jobs`).
- **Môi trường thực thi PHP:** Phiên bản PHP, giới hạn bộ nhớ (`memory_limit`), dung lượng upload tối đa và trạng thái các tiện ích mở rộng quan trọng (`gd`, `openssl`, `zip`, `intl`, `pdo_mysql`).

---

## 2. Điều kiện Tiên quyết

1. Tài khoản đăng nhập phải có vai trò `Super Admin` với middleware bảo vệ `admin.super`.
2. Truy cập mạng nội bộ an toàn hoặc kết nối qua VPN quản trị có bật xác thực 2 yếu tố (2FA).
3. Đã nắm rõ quy trình chẩn đoán sự cố hạ tầng và thông tin liên hệ khẩn cấp của đội ngũ Cloud Engineer.

---

## 3. Các Thẻ KPI Tổng quan (Health KPIs)

Khi truy cập vào `/admin/system/health`, phần đầu trang hiển thị ngay 4 thẻ chỉ số then chốt:

```
┌─────────────────────────┬─────────────────────────┬─────────────────────────┬─────────────────────────┐
│   Trạng Thái Dịch Vụ    │    Nhật Ký Lỗi Hôm Nay  │    Bộ Nhớ PHP RAM       │    Ổ Đĩa Khả Dụng       │
│      Hoạt Động 100%     │          0 Lỗi          │     32.4 MB / Đỉnh 45MB │     142.8 GB Trống      │
│     [● Xanh Lá Cây]     │    (Trong laravel.log)  │     (Limit: 512M)       │     (Đã dùng 34.2%)     │
└─────────────────────────┴─────────────────────────┴─────────────────────────┴─────────────────────────┘
```

- **Hoạt Động 100% (Healthy):** Tất cả dịch vụ phản hồi bình thường, độ trễ thấp, ổ đĩa dưới 80%, không có lỗi nghiêm trọng.
- **Cần Chú Ý (Warning):** Dung lượng ổ đĩa vượt quá 80%, hoặc hàng đợi có trên 5 tác vụ thất bại (`failed_jobs > 5`), hoặc Cache có dấu hiệu chậm.
- **Sự Cố Nghiêm Trọng (Critical):** Thư mục `storage/` bị khóa quyền ghi (`!is_writable`), dung lượng ổ đĩa vượt 90%, hoặc kết nối CSDL bị gián đoạn.

---

## 4. Hướng dẫn Thao tác Từng Tab Giám sát

### Tab 1: Dịch Vụ Hạ Tầng (`services-health`)

Mặc định mở khi vào trang, hiển thị chi tiết 4 phân hệ cốt lõi:
1. **Khối Cơ sở dữ liệu:**
   - Hiển thị Driver đang kích hoạt (`sqlite` hoặc `mysql`).
   - Độ trễ phản hồi: Chuẩn xanh khi `< 25ms`. Nếu độ trễ vượt quá `100ms`, hệ thống đang gặp hiện tượng thắt cổ chai I/O hoặc nghẽn pool kết nối.
2. **Khối Bộ nhớ đệm (Cache):**
   - Kiểm tra vòng quay ghi - đọc - xóa của khóa tạm `system_health_check_{timestamp}`.
   - Độ trễ tiêu chuẩn: `< 5ms`.
3. **Khối Lưu trữ & Đĩa cứng (Storage):**
   - Tổng dung lượng (Total GB) và dung lượng còn trống (Free GB).
   - Thanh tiến trình phần trăm sử dụng (Used %).
   - Chỉ báo an toàn: Xanh lá (`< 80%`), Vàng cảnh báo (`80% - 90%`), Đỏ nguy cấp (`> 90%`).
4. **Khối Hàng đợi (Queue):**
   - Đếm số lượng `pending_jobs` đang chờ xử lý và `failed_jobs`.
   - Nếu `failed_jobs > 5`, thẻ chuyển sang trạng thái cảnh báo vàng.

### Tab 2: Nhật Ký Lỗi Thời Gian Thực (`error-logs`)

Hiển thị 50 dòng log mới nhất được phân tích trực tiếp từ tệp `storage/logs/laravel.log`:
- **Bộ lọc cấp độ (Level Filter):** Cho phép lọc theo chuẩn RFC 5424: `EMERGENCY`, `ALERT`, `CRITICAL`, `ERROR`, `WARNING`, `NOTICE`, `INFO`.
- **Ô tìm kiếm nội dung (Log Search):** Tra cứu nhanh theo từ khóa như `SePay`, `Zalo`, `SQLSTATE`, `Timeout`, `Class not found`.
- **Nút "Tải log gốc" (`download-log`):** Tải toàn bộ tệp `laravel.log` về máy tính phục vụ điều tra sâu.
- **Nút "Làm sạch log" (`clear-log`):** Xóa trắng nội dung tệp log hiện tại để giảm dung lượng đĩa và bắt đầu chu kỳ giám sát mới (bắt buộc nhập xác nhận).

### Tab 3: Thông Số Môi Trường Máy Chủ (`server-metrics`)

Cung cấp thông tin chi tiết về cấu hình Runtime:
- Phiên bản PHP hiện hành (Ví dụ: `8.5.x` hoặc `8.4.x`).
- Kiểu SAPI (`fpm-fcgi`, `cli`, `caddy-frankenphp`).
- Giới hạn tài nguyên: `memory_limit`, `max_execution_time`, `upload_max_filesize`, `post_max_size`.
- Danh sách tiện ích mở rộng (PHP Extensions): Xác nhận trạng thái Đã nạp (`Loaded`) của `pdo_sqlite`, `pdo_mysql`, `curl`, `gd`, `mbstring`, `openssl`, `zip`, `intl`.

---

## 5. Quy trình Ứng phó Cảnh báo Sức khỏe (Incident Playbook)

| Hiện tượng cảnh báo | Ngưỡng vi phạm | Nguyên nhân gốc rễ | Hành động khắc phục chuẩn |
| :--- | :--- | :--- | :--- |
| **Ổ đĩa Vàng cảnh báo** | `used_percent > 80%` | Ảnh cưới upload kích thước lớn hoặc tệp backup tích tụ lâu ngày. | Vào tab *Sao lưu* xóa các bản backup cũ quá 30 ngày, hoặc dọn dẹp thư mục tạm `storage/app/temp`. |
| **Ổ đĩa Đỏ nguy cấp** | `used_percent > 90%` | Tệp `laravel.log` phình to bất thường do vòng lặp lỗi. | Nhấp **[Tải log gốc]** về lưu trữ, sau đó nhấp **[Làm sạch log]** để giải phóng ngay dung lượng khẩn cấp. |
| **Queue có lỗi thất bại** | `failed_jobs > 5` | Token Zalo ZNS hết hạn hoặc webhook ngân hàng SePay bị nghẽn mạng. | Chuyển sang module `admin.system.health` tab Job Center (`ADM-SYS-002`) để kiểm tra payload lỗi và ấn thử lại. |
| **Độ trễ CSDL cao** | `db_latency > 100ms` | Khách truy cập đồng thời đột biến hoặc có truy vấn quét toàn bảng (Full Table Scan). | Kiểm tra các chỉ mục (Indexes) và bật chế độ bảo trì tạm thời nếu cần thiết. |

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** bấm **[Làm sạch log]** khi hệ thống đang xảy ra sự cố nghiêm trọng mà chưa nhấp **[Tải log gốc]** về lưu trữ. Mất log đồng nghĩa với mất bằng chứng điều tra sự cố.
2. **CẤM** can thiệp trực tiếp vào file cấu hình máy chủ hoặc khởi động lại worker hàng đợi nếu chưa có sự phê duyệt của Trưởng ban Kỹ thuật.
3. **CẤM** bỏ qua các cảnh báo màu vàng kéo dài quá 24 giờ.

---

## 7. Bài viết Liên quan

- [ADM-SYS-002: Giám sát hàng đợi tác vụ (Job Center) và xử lý failed jobs](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-002.md)
- [ADM-SYS-003: Quy trình sao lưu toàn diện và diễn tập khôi phục thảm họa](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-003.md)
- [ADM-SYS-004: Bảng điều khiển Nghiệm thu Thương mại (Acceptance Audit Cockpit)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-004.md)
