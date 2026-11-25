---
id: ADM-ORDR-006
title: "Cấu hình Ngân hàng, SePay API v2 & Cron tự động đồng bộ gói cước"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Hướng dẫn thiết lập tài khoản thụ hưởng VietQR tại /admin/settings/banking, tách bạch Webhook API Key và SePay User API Token (hỗ trợ API v2 & v1), kiểm tra kết nối và vận hành Cron mayhen:sync-plan-expirations."
reader: "PER-ADM-01 (Super Admin & Kỹ thuật vận hành)"
order: 6
updated_at: "2026-11-25"
tags: [admin, sepay, api-v2, banking-settings, cron, sync-plan-expirations, rate-limit]
mapped_page: "ADM-BANKING-PAGE"
mapped_route: "admin.settings.banking"
risk_level: "CRITICAL"
required_role: "super_admin"
---

# Cấu hình Ngân hàng, SePay API v2 & Cron tự động đồng bộ gói cước

> [!IMPORTANT]
> **Vai trò thực hiện:** `Super Admin` hoặc kỹ thuật vận hành được ủy quyền.  
> **Route cấu hình:** `GET /admin/settings/banking` (`Admin\SettingController@banking`) & `POST /admin/settings/banking/test-sepay` (`@testSepayApi`).  
> **Tác vụ Cron:** `php artisan mayhen:sync-plan-expirations` (lập lịch tự động mỗi **5 phút** tại `routes/console.php`).  

![Giao diện Cấu Hình Ngân Hàng & SePay API v2 trên Admin](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-006-step-1.png)
*Trang Cấu Hình Ngân Hàng (/admin/settings/banking): Tách rõ Webhook API Key, SePay User API Token (v2/v1) và nút Kiểm Tra Kết Nối SePay API.*

---

## 1. Phân biệt 2 khóa bảo mật SePay (Tuyệt đối không điền nhầm)

Trên hệ thống SePay (`my.sepay.vn`) có **2 loại khóa hoàn toàn khác nhau**:

| Khóa cấu hình | Biến `.env` tương ứng | Lấy ở đâu trên `my.sepay.vn` | Mục đích sử dụng |
| :--- | :--- | :--- | :--- |
| **1. SePay Webhook API Key** | `SEPAY_WEBHOOK_SECRET` | **Webhooks → Sửa Webhook → Tab Bảo mật** | Xác thực gói tin callback SePay bắn về `https://mayhen.vn/api/payments/sepay/webhook` qua header `Authorization: Apikey <KEY>`. |
| **2. SePay User API Token** | `SEPAY_API_TOKEN` | **Cấu hình công ty → Truy cập API (Bearer Token)** | Cho phép Mây Hẹn chủ động truy vấn giao dịch khi Admin bấm **Check SePay**, khách bấm **Kiểm tra ngay** và Cron tự vớt đơn lỡ webhook. |

---

## 2. Chọn chuẩn SePay API v2 (Khuyên dùng) hoặc v1

Tại ô **SePay API Base URL** (`SEPAY_API_BASE_URL`):
- **SePay API v2 (Khuyên dùng trên Production)**: `https://userapi.sepay.vn/v2`
  - Hỗ trợ tìm chính xác từng đơn qua `?q={orderCode}&transfer_type=in`, trả chuẩn HTTP `401`/`422`/`429`, ID chuẩn `UUID` và số tiền dạng số nguyên `integer`.
  - Hệ thống tự động tuân thủ giới hạn tốc độ **3 request/giây** của SePay (khi gặp HTTP `429` sẽ đọc `Retry-After` và dừng gọn an toàn, không spam retry).
- **SePay API v1 (Tương thích ngược)**: `https://my.sepay.vn/userapi` — vẫn hoạt động bình thường cho các token cũ.

Sau khi lưu cấu hình, bấm **[Kiểm Tra Kết Nối SePay API]** để xác nhận hệ thống kết nối thành công (màn hình sẽ báo rõ phiên bản `API v2` hoặc `API v1`, độ trễ ms và số giao dịch truy vấn được).

---

## 3. Vận hành Cron tự động (`mayhen:sync-plan-expirations`)

Tác vụ chạy tự động mỗi **5 phút** (`withoutOverlapping()`) thực hiện tuần tự 3 nhiệm vụ tài chính cốt lõi:
1. **Tự động vớt đơn lỡ webhook**: Gọi gộp **1 HTTP request** cho toàn bộ đơn `pending` chờ từ 2 phút trở lên; khớp đủ 5 điều kiện sẽ tự kích hoạt gói và ghi `AuditLogger` (`SePay-Cron`).
2. **Đóng phiên `pending` quá 30 phút**: Giải phóng khóa tạo đơn mới cho khách (chuyển `cancelled` đối với đơn thật sự chưa chuyển tiền).
3. **Tự động gỡ gói Plus/Pro hết hạn về `premium`**: Trước khi hạ luôn đối chiếu lại toàn bộ đơn `paid` còn hiệu lực, **tuyệt đối bỏ qua tài khoản Admin (Admin luôn là Pro vĩnh viễn)** và giữ nguyên thời hạn lưu trữ riêng của các thiệp cưới đã tạo.

### Lệnh kiểm tra an toàn trên server
```bash
# Xem trước số liệu cần xử lý mà không sửa CSDL và không gọi SePay API
php artisan mayhen:sync-plan-expirations --dry-run

# Chạy đồng bộ ngay lập tức
php artisan mayhen:sync-plan-expirations
```
