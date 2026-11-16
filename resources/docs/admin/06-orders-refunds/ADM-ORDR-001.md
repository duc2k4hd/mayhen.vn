---
id: ADM-ORDR-001
title: "Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Hướng dẫn nhân viên tài chính và vận hành tra cứu đơn hàng, kiểm tra vòng đời trạng thái thanh toán VietQR/SePay, xác minh tính toàn vẹn chữ ký số Webhook và đối soát mã giao dịch ngân hàng."
reader: "PER-ADM-02 (Kế toán & Vận hành Tài chính)"
order: 1
updated_at: "2026-11-15"
tags: [admin, don-hang, sepay, webhook, chu-ky-so, vietqr, doi-soat]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.orders.index"
risk_level: "CRITICAL"
required_role: "finance"
---

# Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay

> [!IMPORTANT]
> **Vai trò thực hiện:** `Finance Officer (Kế toán)`, `Operation Lead`, hoặc `Super Admin`.  
> **Route màn hình:** `GET /admin/orders` (`Admin\OrderController@index`) & `GET /admin/orders/{id}` (`@show`).  
> **Cổng thanh toán tích hợp:** SePay Hub kết nối VietinBank/MBBank tự động quét biến động số dư VietQR.  
> **Cơ chế an ninh cốt lõi:** Xác thực chữ ký số HMAC-SHA256 (`X-SePay-Signature`), phòng vệ tấn công phát lại (Replay Attack) và chống gian lận lặp chi tiêu (Double-Spending).


![Giao diện quản lý Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Khi cặp đôi nâng cấp gói dịch vụ hoặc gia hạn lưu trữ trên Mây Hẹn, hệ thống sinh mã VietQR động chứa số tiền chính xác và cú pháp chuyển khoản định danh (ví dụ: `MAYHEN ORD20268899`). Ngay khi tiền vào tài khoản ngân hàng của Mây Hẹn, SePay sẽ bắn một gói tin **Webhook** theo thời gian thực tới máy chủ.

Nhân viên Kế toán và Vận hành cần nắm vững quy trình này để:
1. **Tra cứu tức thì lịch sử đơn hàng**: Tìm kiếm theo mã đơn, email, số điện thoại hoặc mã thiệp cưới.
2. **Kiểm tra trạng thái thanh toán thực tế**: Phân biệt rõ giữa *Chờ thanh toán (`pending`)*, *Đã thanh toán (`paid`)*, *Đã hoàn tiền (`refunded`)* và *Đang chờ duyệt kép (`pending_approval`)*.
3. **Thanh tra chữ ký Webhook SePay**: Xác minh tính xác thực của gói tin thanh toán từ ngân hàng, kịp thời phát hiện các giao dịch bị lỗi mạng, giả mạo chữ ký hoặc trùng lặp mã giao dịch.

---

## 2. Vòng đời Trạng thái Đơn hàng (Order State Machine)

Mỗi đơn hàng trên Mây Hẹn (`Order`) chuyển dịch qua các trạng thái chuẩn hóa sau:

```
                  ┌──────────────────────┐
                  │       pending        │ (Khách vừa tạo đơn hàng, chờ chuyển khoản)
                  └──────────┬───────────┘
                             │
            ┌────────────────┴────────────────┐
            ▼                                 ▼
┌───────────────────────┐         ┌───────────────────────┐
│         paid          │         │   cancelled / failed  │
│ (Webhook hợp lệ hoặc  │         │ (Hết hạn sau 24h hoặc │
│  Admin duyệt thủ công)│         │  khách hủy giao dịch) │
└───────────┬───────────┘         └───────────────────────┘
            │
            ▼
┌───────────────────────┐
│       refunded        │ (Hoàn tiền theo quy trình 3 cấp)
└───────────────────────┘
```

| Trạng thái (`status`) | Ý nghĩa nghiệp vụ | Trạng thái quyền lợi thiệp cưới |
| :--- | :--- | :--- |
| `pending` | Đơn hàng mới tạo, đang chờ chuyển tiền VietQR. | Thiệp ở gói dùng thử hoặc chưa mở tính năng nâng cao. |
| `paid` | Đã nhận đủ tiền, webhook xác thực chữ ký hợp lệ. | **Kích hoạt ngay lập tức**: Cấp hạn dùng mới, mở khóa toàn bộ tính năng gói. |
| `refunded` | Đã hoàn trả lại tiền cho khách hàng. | Thu hồi quyền lợi gói nâng cao, chuyển thiệp về bản lưu trữ hoặc bản miễn phí. |
| `cancelled` | Khách chủ động hủy hoặc đơn quá hạn 24 giờ. | Không tác động đến dữ liệu thiệp cưới. |
| `failed` | Giao dịch bị ngân hàng từ chối hoặc sai số tiền. | Cần kế toán kiểm tra thủ công. |

---

## 3. Kiến trúc Bảo mật Webhook SePay (HMAC-SHA256)

Khi SePay gửi thông báo biến động số dư tới endpoint `POST /webhook/sepay` (`CheckoutController@webhook`):

1. **Header xác thực**:
   * `X-SePay-Timestamp`: Dấu thời gian tính theo giây epoch của SePay. Nếu chênh lệch quá **300 giây (5 phút)** so với giờ máy chủ, yêu cầu bị từ chối ngay lập tức để chống tấn công phát lại (Replay Attack).
   * `X-SePay-Signature`: Chuỗi băm HMAC-SHA256 được tính toán từ toàn bộ Payload thô và `services.sepay.webhook_secret`.
2. **Kiểm tra tính toàn vẹn (Payload Integrity)**:
   * Thuật toán: `hash_equals($signature, hash_hmac('sha256', $rawBody, $secret))`.
   * Nếu chữ ký không khớp: Trả về HTTP 401, ghi log cảnh báo xâm nhập an ninh.
3. **Cơ chế chống gian lặp chi tiêu (Double-Spending)**:
   * Trường `provider_transaction_id` (mã giao dịch ngân hàng của SePay) là duy nhất. Nếu mã này đã từng được dùng để duyệt một đơn hàng khác, hệ thống chặn đứng và cảnh báo kế toán.

---

## 4. Hướng dẫn từng bước: Tra cứu & Kiểm tra Đơn hàng trên Admin

### Bước 1: Truy cập Trung tâm Quản lý Đơn hàng
1. Đăng nhập Admin Portal với tài khoản có quyền `finance` hoặc `super_admin`.
2. Trên thanh điều hướng, chọn **Đơn hàng & Thanh toán** (`/admin/orders`).
3. Màn hình hiển thị 5 chỉ số tài chính tổng quan (KPIs):
   * **Tổng doanh thu thực nhận** (Tổng tiền các đơn `paid`).
   * **Đơn thành công** (Số lượng đơn đã thanh toán).
   * **Đơn chờ thanh toán** (`pending`).
   * **Đơn đã hoàn tiền** (`refunded`).
   * **Đơn thất bại/đã hủy**.

### Bước 2: Sử dụng bộ lọc tra cứu chuyên sâu
* **Ô tìm kiếm đa năng (`q`)**: Nhập mã đơn hàng (ví dụ `ORD-2026-9812`), tên khách hàng, email hoặc số điện thoại.
* **Bộ lọc Trạng thái**: Chọn `Đã thanh toán`, `Chờ thanh toán` hoặc `Đã hoàn tiền`.
* **Bộ lọc Gói dịch vụ**: Lọc theo gói Silver, Gold, Diamond hoặc Gói Gia hạn.
* **Bộ lọc Thời gian**: Chọn `Hôm nay`, `Tuần này` hoặc `Tháng này`.

### Bước 3: Xem Hồ sơ Chi tiết Đơn hàng
1. Nhấp vào mã đơn hàng để mở trang **Hồ sơ Đơn hàng Chi tiết** (`/admin/orders/{id}`).
2. Kiểm tra các khối thông tin then chốt:
   * **Thông tin Khách hàng**: Tên, email, số điện thoại, ngày đăng ký.
   * **Thông tin Thiệp cưới liên kết**: Mã slug thiệp, tên cặp đôi hôn lễ, mẫu thiệp đang dùng.
   * **Chi tiết Giao dịch**: Số tiền thanh toán (VNĐ), cổng thanh toán (`VIETQR / SEPAY`), mã giao dịch ngân hàng (`provider_transaction_id`).
   * **Thời gian ghi nhận**: Mốc thời gian tạo đơn (`created_at`) và mốc thời gian nhận tiền (`paid_at`).
   * **Lịch sử Đối soát (Reconciliations)**: Nhật ký ghi nhận nguồn gốc giao dịch (Tự động qua Webhook hay Duyệt thủ công bởi Admin).

---

## 5. Xử lý sự cố & Tình huống ngoại lệ thường gặp

### Tình huống 1: Khách hàng báo đã chuyển tiền nhưng đơn hàng vẫn ở trạng thái `pending`
* **Nguyên nhân phổ biến:**
  1. Khách ghi sai nội dung chuyển khoản (thiếu chữ `ORD` hoặc sai số mã đơn).
  2. Khách chuyển thiếu tiền so với giá trị đơn hàng.
  3. Lỗi mạng ngân hàng làm trễ webhook SePay quá 10 phút.
* **Cách xử lý:** 
  1. Vào cổng quản trị SePay đối soát xem có biến động số dư ngân hàng khớp với số tiền của khách không.
  2. Lấy **Mã giao dịch ngân hàng (Reference Code)** từ sao kê ngân hàng.
  3. Sử dụng chức năng **[ Duyệt Đối Soát Thủ Công ]** (chi tiết tại [ADM-ORDR-002](/admin/docs/orders-refunds/ADM-ORDR-002)) để kích hoạt quyền lợi cho cặp đôi.

### Tình huống 2: Nhận được cảnh báo "Chữ ký số Webhook không hợp lệ"
* **Nguyên nhân:** Webhook secret trên file môi trường `.env` bị lệch so với cấu hình trên SePay Dashboard, hoặc có yêu cầu giả mạo từ IP lạ.
* **Cách xử lý:** 
  1. Vào `/admin/settings` kiểm tra cấu hình `services.sepay.webhook_secret`.
  2. Kiểm tra log hệ thống `storage/logs/laravel.log` để xác minh IP gửi webhook.
  3. Tuyệt đối không tắt cơ chế kiểm tra chữ ký số để "chữa cháy".

---

## 6. Những điều Tuyệt đối Bị cấm trong Vận hành Tài chính

1. **NGHIÊM CẤM DUYỆT ĐƠN KHI CHƯA CÓ MÃ GIAO DỊCH NGÂN HÀNG THẬT:** Thao tác duyệt thanh toán thủ công bắt buộc phải có mã tham chiếu ngân hàng có thật từ sao kê VietinBank/MBBank.
2. **CẤM DUYỆT TRÙNG LẶP MỘT MÃ GIAO DỊCH:** Hệ thống đã cài đặt cơ chế khóa Anti-replay. Tuyệt đối không tìm cách lách mã để kích hoạt đơn ảo cho người quen.
3. **CẤM CHIA SẺ SECRET KEY WEBHOOK:** Chuỗi bí mật `SEPAY_WEBHOOK_SECRET` là chìa khóa tài chính bảo vệ cổng thanh toán, nghiêm cấm gửi qua chat hoặc công khai ra bên ngoài.

---

## 7. Bài viết liên quan
* [ADM-ORDR-002: Quy trình kích hoạt gói dịch vụ thủ công khi khách chuyển khoản sai nội dung](/admin/docs/orders-refunds/ADM-ORDR-002)
* [ADM-ORDR-003: Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt](/admin/docs/orders-refunds/ADM-ORDR-003)
* [ADM-ORDR-004: Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán](/admin/docs/orders-refunds/ADM-ORDR-004)
* [ADM-ORDR-005: Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường](/admin/docs/orders-refunds/ADM-ORDR-005)
