---
id: ADM-ORDR-001
title: "Tra cứu đơn hàng, đối soát chủ động Check SePay 5 điểm & bảo mật Webhook SePay"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Hướng dẫn quản trị viên và kế toán tra cứu đơn hàng tại /admin/orders, dùng nút Check SePay đối soát chủ động 5 điều kiện bất biến qua SePay API v1/v2, hiểu vòng đời đơn 30 phút và xác thực Webhook Authorization: Apikey."
reader: "PER-ADM-02 (Kế toán & Vận hành Tài chính)"
order: 1
updated_at: "2026-11-25"
tags: [admin, don-hang, sepay, check-sepay, webhook, apikey, api-v2, vietqr, doi-soat]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.orders.index"
risk_level: "CRITICAL"
required_role: "finance"
---

# Tra cứu đơn hàng, đối soát chủ động Check SePay 5 điểm & bảo mật Webhook SePay

> [!IMPORTANT]
> **Vai trò thực hiện:** `Finance Officer (Kế toán)`, `Operation Lead`, hoặc `Super Admin`.  
> **Route màn hình:** `GET /admin/orders` (`Admin\OrderController@index`), `GET /admin/orders/{id}` (`@show`) và `POST /admin/orders/{id}/check-sepay` (`@checkSepay`).  
> **URL Webhook chính thức:** `POST /api/payments/sepay/webhook` (`CheckoutController@handleWebhook`).  
> **Dịch vụ lõi:** `App\Services\Payments\SepayGatewayService` & `App\Services\OrderSettlementService`.  

![Giao diện quản lý Đơn hàng & nút Check SePay trên Admin Mây Hẹn](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-001-step-1.png)
*Trung tâm Quản lý Đơn hàng (/admin/orders): Theo dõi doanh thu, lọc trạng thái và bấm nút Check SePay đối soát trực tiếp với ngân hàng.*

---

## 1. Vòng đời Trạng thái Đơn hàng (Chuẩn Production mới nhất)

Mỗi đơn hàng được gắn cố định với tài khoản đã đăng nhập (`user_id`), có mã đơn định dạng **`MH` + 10 ký tự** (ví dụ `MHNTYIBGOV6Y`), `checkout_token` 256-bit riêng và thời hạn phiên chờ **30 phút** (`checkout_expires_at`).

| Trạng thái (`status`) | Ý nghĩa nghiệp vụ | Tác động & Quyền đối soát Check SePay |
| :--- | :--- | :--- |
| `pending` | Khách vừa tạo mã VietQR, đang chờ chuyển khoản (hiệu lực 30 phút). | Hệ thống khóa không cho user tạo đơn thứ 2 song song. **Được phép bấm Check SePay.** |
| `paid` | Đã khớp chính xác tiền vào qua Webhook SePay, Check SePay hoặc Cron. | **Kích hoạt ngay đúng gói trên đơn** (`Plus` 6 tháng / 10 thiệp hoặc `Pro VIP` 1 năm / 30 thiệp). |
| `cancelled` | Chính chủ tự bấm Hủy phiên hoặc Cron đóng sau 30 phút chưa có tiền. | Link thanh toán hộ tự vô hiệu hóa ngay. Nếu khách lỡ chuyển muộn, **Check SePay & Webhook vẫn phục hồi an toàn `cancelled -> paid`**. |
| `failed` | Giao dịch lỗi hoặc bị gián đoạn trước đó. | **Được phép bấm Check SePay** để kiểm tra lại và tự phục hồi nếu ngân hàng đã nhận tiền. |
| `refunded` | Đã hoàn tiền theo quy trình 3 cấp và thu hồi quyền lợi. | **Chặn tuyệt đối (`409`)**: Không bao giờ cho phép Check SePay hay Webhook kích hoạt lại. |

---

## 2. Kiến trúc Bảo mật Webhook SePay (`/api/payments/sepay/webhook`)

1. **Xác thực chuẩn SePay chính thức**: Header `Authorization: Apikey <SEPAY_WEBHOOK_SECRET>` (kèm dự phòng `X-SePay-Api-Key` khi proxy ẩn `Authorization`), đồng thời giữ tương thích ngược chữ ký HMAC-SHA256 (`X-SePay-Timestamp` trong ±300s + `X-SePay-Signature`).
2. **Xác minh 5 điều kiện bất biến tài chính** (dùng chung cho Webhook, Admin Check SePay, Khách kiểm tra ngay và Cron 5 phút):
   - Giao dịch tiền vào thật (`transferType = in`, `amount_in > 0`, `amount_out = 0`).
   - Khớp đúng số tài khoản thụ hưởng (`account_number` hoặc tài khoản ảo `sub_account`/`va`).
   - Bóc tách đúng mã đơn `MH...` theo ranh giới từ nghiêm ngặt.
   - **Khớp chính xác từng đồng (`amount_in === order.amount`)** — chuyển thiếu hoặc thừa tuyệt đối không tự kích hoạt.
   - Chống tái sử dụng mã giao dịch (`provider_transaction_id`) bên trong `DB::transaction()` + `lockForUpdate()`.
3. **Ghi nhật ký kép**: Mọi sự kiện được lưu đồng thời vào bảng `payment_webhook_logs` và tệp `storage/logs/payment_webhooks.jsonl`.

---

## 3. Thao tác đối soát chủ động bằng nút **Check SePay** trên Admin

Khi khách báo đã chuyển khoản nhưng đơn vẫn đang ở trạng thái `pending`, `failed` hoặc `cancelled`:

1. Mở **Đơn Hàng & Doanh Thu** tại `/admin/orders` (hoặc mở trang chi tiết `/admin/orders/{id}`).
2. Bấm nút **[Check SePay]** trên dòng đơn hàng tương ứng.
3. Hệ thống lập tức gọi trực tiếp SePay API (hỗ trợ cả **API v2** `GET https://userapi.sepay.vn/v2/transactions?q={orderCode}` lẫn **v1** `GET /transactions/list`) và tự kiểm tra đủ 5 điều kiện:
   - **Nếu khớp đủ 5 điều kiện**: Tự động chuyển đơn sang `paid`, kích hoạt **đúng gói trên đơn hàng** (`Plus` hoặc `Pro VIP`), tạo phiếu đối soát `sepay_auto_reconcile` và ghi `AuditLogger`.
   - **Nếu khách chuyển thiếu/thừa tiền**: Từ chối kích hoạt, hiển thị số tiền thực nhận so với giá trị đơn và ghi log `mismatch` để kế toán xử lý theo [ADM-ORDR-005](/admin/docs/orders-refunds/ADM-ORDR-005).
   - **Nếu chưa có tiền vào**: Cập nhật thời điểm kiểm tra gần nhất (`last_sepay_check_at`) và thông báo rõ số giao dịch đã quét.

---

Xem tiếp tài liệu:
- [Cấu hình Ngân hàng, SePay API v2 & Cron tự động đồng bộ gói cước](/admin/docs/orders-refunds/ADM-ORDR-006)
- [Đối soát số dư định kỳ & xử lý giao dịch lệch tiền / treo mạng](/admin/docs/orders-refunds/ADM-ORDR-005)
