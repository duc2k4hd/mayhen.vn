---
id: ADM-SYS-002
title: "Giám sát hàng đợi tác vụ (Job Center): Xử lý failed jobs, thử lại tác vụ an toàn"
category: "Hệ thống, Sức khỏe, Sao lưu & Nghiệm thu"
summary: "Hướng dẫn quản trị viên cấp cao giám sát các Lô công việc nền (Job Batches), tra cứu nhật ký lỗi đã lọc sạch dữ liệu nhạy cảm (Sanitized Stack Trace), thử lại tác vụ và dọn dẹp hàng đợi an toàn."
reader: "PER-ADM-03 (Quản trị viên Cấp cao)"
order: 2
updated_at: "2026-11-15"
tags: [jobs, queue, failed-jobs, batches, retry, purge, sanitize]
mapped_page: "ADM-SYS-JOBS"
mapped_route: "admin.jobs.index"
risk_level: "CRITICAL"
required_role: "admin.super"
---

# Giám sát hàng đợi tác vụ (Job Center): Xử lý failed jobs, thử lại tác vụ an toàn

> [!WARNING]
> **Phân quyền thực hiện:** Chỉ dành riêng cho Quản trị viên Cấp cao (`admin.super`).  
> **Route quản trị:** `GET /admin/system/jobs` (`admin.jobs.index`).  
> **Nguyên tắc an toàn:** Tuyệt đối không bấm Thử lại (Retry) hàng loạt khi chưa phân tích nguyên nhân lỗi và kiểm tra tính lũy kế (Idempotency).


![Giao diện quản lý Giám sát hàng đợi tác vụ (Job Center): Xử lý failed jobs, thử lại tác vụ an toàn](/assets/client/docs/images/admin/10-system-health/adm-sys-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích Nghiệp vụ & Kiến trúc Hàng đợi

Các tác vụ tốn thời gian hoặc phụ thuộc vào bên thứ ba (gửi tin nhắn Zalo ZNS, tạo tệp nén ảnh cưới ZIP, đồng bộ hóa đơn VAT điện tử, kiểm tra đối soát ngân hàng) đều được Mây Hẹn xử lý bất đồng bộ thông qua hàng đợi (`jobs` và `job_batches`). 

Khi dịch vụ bên thứ ba gặp sự cố hoặc máy chủ bị gián đoạn mạng, tác vụ sẽ rơi vào bảng `failed_jobs`. Trung tâm Chỉ huy Tác vụ Nền (**Job Center Cockpit**) giúp quản trị viên:
1. Theo dõi trực quan tiến độ phần trăm (%) của các Lô công việc nền (Batch Processing).
2. Tra cứu chi tiết dấu vết lỗi (Stack Trace) đã được tự động che giấu các khóa bảo mật bí mật (Sanitized Data Protection).
3. Thử lại (Retry) an toàn cho các tác vụ bị lỗi mạng tạm thời mà không làm trùng lặp giao dịch của khách hàng.
4. Hủy bỏ (Cancel) hoặc Xóa dọn dẹp (Purge) các tác vụ không còn giá trị thực thi.

---

## 2. Các Chỉ số Hàng đợi Then chốt (Queue KPIs)

Khi truy cập vào `/admin/system/jobs`, đầu trang hiển thị 5 thẻ chỉ số:
- **Tác vụ lỗi (Total Failed):** Tổng số bản ghi đang tồn tại trong bảng `failed_jobs`.
- **Hàng đợi chờ (Total Pending):** Số lượng job đang nằm trong bảng `jobs` chờ worker bốc xử lý.
- **Lô công việc đang chạy (Active Batches):** Số lô batch đang thực thi đồng thời.
- **Lô hoàn tất 24h qua (Completed 24h):** Số lượng batch hoàn thành trọn vẹn không lỗi.
- **Lô có lỗi 24h qua (Failed Batches 24h):** Số batch có ít nhất 1 tác vụ con bị lỗi (`failed_jobs > 0`).

---

## 3. Quản lý Lô Công Việc (Tab "Lô công việc - Batches")

Mỗi khi người dùng tải về toàn bộ Album ảnh cưới hoặc quản trị viên kích hoạt tiến trình sao lưu, hệ thống sẽ gom các công việc vào một `Batch` có mã UUID duy nhất:

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│ Lô #9b4e18ac - Sao lưu kho ảnh cưới khách hàng (Tháng 11/2026)                                    │
│ Tiến độ: [██████████████████████████████░░░░░░░░░░] 74.5% (Hoàn tất 149/200, 2 lỗi, 49 chờ)      │
│ Thời gian tạo: 15/11/2026 14:20:00 | Trạng thái: Đang xử lý                                      │
│ Thao tác: [Xem chi tiết]  [Thử lại job lỗi]  [Hủy bỏ lô]                                         │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

- **Hủy bỏ Lô (`batches.cancel`):** Khi phát hiện một batch đang bị lặp vô tận hoặc ngốn cạn băng thông máy chủ, nhấp **[Hủy bỏ lô]**. Các job con còn lại chưa chạy sẽ tự động bị hủy, tránh gây nghẽn toàn bộ hệ thống.
- **Thử lại job lỗi trong Lô (`batches.retry`):** Tự động lọc ra đúng các job con bị lỗi trong lô đó và đẩy lại vào hàng đợi mà không làm chạy lại các job đã thành công trước đó.

---

## 4. Phân tích & Xử lý Tác vụ Thất bại (Tab "Tác vụ lỗi - Failed")

### A. Cơ chế Bảo vệ Bí mật (Sanitized Stack Trace)
Để tuân thủ nghiêm ngặt Luật An ninh mạng và Luật Bảo vệ Dữ liệu Cá nhân (Nghị định 13/2023/NĐ-CP & Luật 91/2025/QH15), `JobCenterService` tự động quét và lọc sạch các thông tin nhạy cảm trước khi hiển thị ra giao diện quản trị:
- Mọi mật khẩu: `password=********`
- Khóa bí mật API: `secret=********`, `api_key=********`, `Bearer ********`
- Token cổng thanh toán: `sepay_token=********`, `vietqr_key=********`
- Token mạng xã hội: `zalo_token=********`

Nhờ cơ chế này, nhân viên kỹ thuật có thể tra cứu nguyên nhân lỗi mà không lo bị lộ chứng thư kết nối hệ thống.

### B. Quy trình Thử lại (Retry) An toàn
Khi gặp một bản ghi lỗi trong bảng:
1. Nhấp **[Xem chi tiết]** để đọc thông điệp ngoại lệ (`Exception Message`) và tên Job (ví dụ: `SendZaloRsvpNotificationJob`).
2. Xác định nguyên nhân lỗi:
   - **Lỗi mạng tạm thời (Timeout, Connection Refused, HTTP 504):** Đủ điều kiện để thử lại. Nhấp **[Thử lại tác vụ]** (`admin.jobs.retry`).
   - **Lỗi logic mã nguồn hoặc dữ liệu không hợp lệ (Type Error, Missing Column):** **KHÔNG ĐƯỢC RETRY** ngay. Phải bàn giao lập trình viên vá lỗi code trước.
3. Trường hợp sự cố diện rộng từ phía đối tác (ví dụ cổng Zalo ZNS bảo trì trong 1 giờ) khiến hàng trăm job bị dồn lại: Sau khi phía đối tác thông báo đã mở lại dịch vụ, nhấp **[Thử lại toàn bộ]** (`admin.jobs.retry_all`) và có thể chọn lọc theo tên hàng đợi (chỉ retry queue `notifications`).

### C. Xuất Báo Cáo & Xóa Dọn Dẹp (Purge)
1. Trước khi dọn dẹp hàng đợi, bắt buộc nhấp **[Xuất báo cáo lỗi (CSV)]** (`admin.jobs.export_errors`). Tệp CSV xuất ra có định dạng UTF-8 kèm mã BOM hiển thị chuẩn tiếng Việt trong Excel, lưu giữ mã UUID, tên job, tên queue và thời điểm xảy ra lỗi.
2. Sau khi đã lưu trữ báo cáo, nhấp **[Dọn sạch tất cả]** (`admin.jobs.purge`) để xóa trắng bảng `failed_jobs`, đưa chỉ số KPI lỗi về 0.

---

## 5. Bảng Ma trận Xử lý Các Loại Job Thất bại

| Tên Lớp Tác Vụ (Job Class) | Queue | Nguy cơ Trùng lặp (Idempotency) | Hướng Dẫn Xử Lý |
| :--- | :--- | :--- | :--- |
| `SendZaloRsvpNotificationJob` | `notifications` | Thấp (Khách nhận 2 tin nhắn) | Kiểm tra số dư tin ZNS. Nếu đủ, nhấp Retry ngay. |
| `ProcessSepayWebhookJob` | `webhooks` | **CỰC KỲ CAO** (Cộng tiền trùng) | **CẤM RETRY VỘI**. Tra cứu bảng `orders` xem đơn đã ở trạng thái `PAID` chưa. Nếu đã `PAID`, chỉ xóa job (`destroy`). |
| `GenerateWeddingZipArchiveJob` | `exports` | Không có (Ghi đè file tạm) | Kiểm tra dung lượng ổ đĩa khả dụng. Nếu đủ, nhấp Retry. |
| `SyncInvoiceElectronicJob` | `integrations` | Trung bình (Tạo 2 số HĐ) | Kiểm tra portal hóa đơn điện tử xem đã cấp số chưa trước khi retry. |

---

## 6. Những điều Tuyệt đối Bị cấm

1. **TUYỆT ĐỐI CẤM** nhấp `Retry All` vào ban ngày khi chưa lọc theo Queue hoặc chưa kiểm tra nguyên nhân gốc rễ. Thao tác này có thể làm nghẽn nghẹt đường truyền kết nối đến máy chủ CSDL chính.
2. **CẤM** bấm `Purge` khi chưa tải tệp CSV xuất lỗi về lưu trữ kiểm toán.
3. **CẤM** chỉnh sửa thủ công câu lệnh cập nhật trực tiếp vào cơ sở dữ liệu `failed_jobs` bỏ qua controller quản trị.

---

## 7. Bài viết Liên quan

- [ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-001.md)
- [ADM-SYS-003: Quy trình sao lưu toàn diện và diễn tập khôi phục thảm họa](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-003.md)
- [ADM-ORDR-001: Tra cứu đơn hàng và kiểm tra chữ ký Webhook SePay](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/06-orders-payments/ADM-ORDR-001.md)
