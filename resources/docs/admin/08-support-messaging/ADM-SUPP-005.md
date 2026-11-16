---
id: ADM-SUPP-005
title: "Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)"
category: "Hỗ trợ Khách hàng & Cổng Tin nhắn"
summary: "Quy trình cấu hình, điều phối hàng đợi gửi tin đa kênh (Zalo ZNS & SMS Brandname), kiểm thử kết nối Ping Gateway, xử lý rớt mạng và cơ chế chuyển tiếp dự phòng (Failover Fallback) trên Mây Hẹn."
reader: "PER-ADM-01 / PER-ADM-02 (Vận hành & Hỗ trợ Khách hàng)"
order: 5
updated_at: "2026-11-15"
tags: [admin, cong-tin-nhan, zalo-zns, sms-brandname, failover-fallback, sandbox-test, messaging-gateway]
mapped_page: "ADM-SUPP-PAGE"
mapped_route: "admin.settings.notifications.index"
risk_level: "HIGH"
required_role: "manager"
---

# Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)

> [!IMPORTANT]
> **Vai trò thực hiện:** `Technical Lead`, `Operation Manager`, hoặc `Super Admin`.  
> **Route Trung tâm Cổng Tin nhắn:** `GET /admin/settings/notifications` (`AdminNotificationGatewayController@index`).  
> **Endpoint kiểm tra kết nối:** `POST /admin/settings/notifications/{gateway}/ping` (`@ping`).  
> **Endpoint kiểm thử gửi tin:** `POST /admin/settings/notifications/test` (`@sendTest`).  
> **Dịch vụ backend:** `App\Services\NotificationGatewayConfigService`.  
> **Các kênh tích hợp:** **Zalo ZNS** (Zalo Official Account) và **SMS Brandname** (Viettel/VNPT/eSMS).


![Giao diện quản lý Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)](/assets/client/docs/images/admin/08-support-messaging/adm-supp-005-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Trong hệ sinh thái Mây Hẹn, việc chuyển phát tin nhắn thông báo kịp thời là cầu nối quan trọng giữa cặp đôi và khách mời:
1. **Gửi thiệp mời cá nhân hóa**: Cặp đôi gửi hàng loạt thiệp mời số kèm link riêng qua Zalo cho từng người thân, bạn bè.
2. **Nhắc lịch đám cưới (Wedding Reminder)**: Tự động gửi tin nhắc trước ngày cưới 1 ngày kèm mã QR đón tiếp tại sảnh.
3. **Mã xác thực OTP & Bảo mật**: Gửi mã xác thực khi đăng ký, đổi mật khẩu hoặc xác minh danh tính.
4. **Biên lai thanh toán thành công**: Gửi tin xác nhận kích hoạt gói dịch vụ sau khi quét VietQR.

Để đảm bảo tỷ lệ phát tin thành công đạt **99.9%**, Mây Hẹn xây dựng bộ điều phối **Reliable Messaging Gateway Dispatcher** với cơ chế chuyển tiếp dự phòng thông minh (Failover Fallback).

---

## 2. Kiến trúc Chuyển Tiếp Dự Phòng Đa Kênh (Failover Fallback)

Hệ thống hoạt động theo nguyên tắc bậc thang ưu tiên tối ưu chi phí và tỷ lệ đọc:

```
[ BƯỚC 1: ZALO ZNS ] ────────( Thất bại: Không có Zalo / Lỗi Token )───────┐
  - Chi phí thấp, hiển thị đẹp                                              │
  - Tỷ lệ mở đọc > 85%                                                     ▼
                                                                [ BƯỚC 2: SMS BRANDNAME ]
                                                                  - Tên định danh: "MAYHEN"
                                                                  - Gửi trực tiếp tới SIM điện thoại
                                                                  - Chi phí chuẩn telco
                                                                            │
                                                                            │ (Thất bại: Thuê bao khóa / Chặn tin nhắn)
                                                                            ▼
                                                                [ BƯỚC 3: DỰ PHÒNG KHẨN CẤP ]
                                                                  - Sinh mã link web ngắn gọn
                                                                  - Thông báo cho cặp đôi sao chép gửi thủ công
```

---

## 3. Quản lý Cấu Hình Cổng Tin Nhắn (`/admin/settings/notifications`)

Giao diện gồm 3 tab chức năng:

### Tab 1: Cấu hình Zalo ZNS (`tab=zalo`)
* **Zalo OA ID (`zalo_oa_id`)**: Mã định danh tài khoản Zalo Official Account chính thức của Mây Hẹn.
* **App ID & Secret Key (`zalo_app_id` & `zalo_secret_key`)**: Khóa API cấp từ Zalo Developers Portal.
* **Access Token & Refresh Token**: Chuỗi mã thông báo ủy quyền. Hệ thống tự động làm mới (Auto-refresh) mỗi 24 giờ.
* **Che giấu chuỗi bí mật (`maskSecrets = true`)**: Toàn bộ Secret Key hiển thị dưới dạng dấu sao `********` để phòng ngừa quay phim/chụp trộm màn hình văn phòng.

### Tab 2: Cấu hình SMS Brandname (`tab=sms`)
* **Tên thương hiệu Brandname (`sms_brandname`)**: Nhãn gửi tin đã đăng ký với Cục An toàn thông tin (mặc định: `MAYHEN`).
* **Cổng nhà mạng API (`sms_api_url`)**: Điểm kết nối nhà mạng (eSMS / Viettel / VNPT).
* **API Key & Secret Key**: Mã định danh ký quỹ viễn thông.

### Tab 3: Hộp cát Kiểm Thử (Sandbox & Dispatch Test)
* Môi trường an toàn cho phép kỹ thuật viên bắn tin thử nghiệm tới số điện thoại nội bộ trước khi áp dụng cho toàn bộ khách hàng thật.
* Hiển thị bảng nhật ký 25 lượt bắn tin gần nhất: Thời gian, Kênh gửi, Số điện thoại, Mã phản hồi và Tình trạng thành công/thất bại.

---

## 4. Hướng dẫn từng bước: Kiểm tra & Vận hành Cổng Tin Nhắn

### Bước 1: Kiểm tra kết nối nhanh (Ping Gateway)
1. Đăng nhập Admin Portal tại `/admin`.
2. Vào **Cài Đặt Hệ Thống** -> chọn **Cổng Tin Nhắn (Notifications)** (`/admin/settings/notifications`).
3. Tại tab Zalo ZNS hoặc SMS Brandname, nhấp nút **[ ⚡ Kiểm Tra Kết Nối (Ping) ]**.
4. Hệ thống gọi `NotificationGatewayConfigService@testConnection`:
   * Gửi gói tin rỗng kiểm tra tình trạng sống còn của máy chủ Zalo/SMS.
   * Nếu thành công: Xuất hiện thông báo xanh *"Kết nối cổng Zalo ZNS hoạt động hoàn hảo (Độ trễ: 120ms)"*.
   * Nếu thất bại: Hiển thị nguyên nhân chi tiết (Token hết hạn, sai Secret Key, tài khoản viễn thông hết tiền).

### Bước 2: Thử nghiệm gửi tin nhắn mẫu trên Sandbox
1. Chuyển sang tab **Hộp Cát Kiểm Thử (`tab=sandbox`)**.
2. Điền thông tin thử nghiệm:
   * **Kênh gửi (`channel`)**: Chọn `zalo_zns` hoặc `sms_brandname`.
   * **Số điện thoại nhận tin (`recipient`)**: Nhập số điện thoại cá nhân của kỹ thuật viên (ví dụ: `0909123456`).
   * **Mẫu tin nhắn (`template_type`)**: Chọn loại nội dung cần kiểm thử:
     * `invitation_link`: Mẫu gửi thiệp mời cưới kèm tên khách.
     * `wedding_reminder`: Mẫu nhắc lịch đám cưới sát giờ.
     * `payment_receipt`: Mẫu biên lai VietQR thành công.
3. Nhấp **[ Gửi Thử Nghiệm ]**.
4. Mở điện thoại kiểm tra:
   * Tin nhắn ZNS đến trong vòng 3 – 5 giây với logo Mây Hẹn và các nút bấm tương tác.
   * Nội dung hiển thị đúng font chữ Tiếng Việt có dấu, link mở thiệp hoạt động chuẩn xác.

### Bước 3: Cập nhật thông số cấu hình mới
Khi đổi mới Access Token hoặc gia hạn hợp đồng Brandname:
1. Nhập thông số mới vào các ô nhập liệu.
2. Nhấp **[ Lưu Thiết Lập ]**.
3. Hệ thống kiểm tra hợp lệ, mã hóa dữ liệu và lưu vết `GATEWAY_CONFIG_UPDATED` vào bảng `audit_logs`.

---

## 5. Xử lý các sự cố thường gặp (Troubleshooting)

### Tình huống 1: Zalo ZNS báo lỗi "Access Token Expired"
* **Nguyên nhân:** Refresh Token của Zalo bị mất hiệu lực do đổi mật khẩu tài khoản quản trị Zalo OA.
* **Cách xử lý:** 
  1. Truy cập `developers.zalo.me`, đăng nhập tài khoản Zalo OA chủ tiệc.
  2. Tạo mã Authorization Code mới.
  3. Dán mã vào tab Zalo trên Admin và bấm **[ Cấp lại Token ]**.

### Tình huống 2: Hàng đợi gửi tin bị ùn tắc vào ngày cưới cao điểm (Chủ Nhật)
* **Nguyên nhân:** Cặp đôi gửi đồng loạt 50.000 thiệp mời cùng lúc lúc 09:00 sáng.
* **Cách xử lý:** 
  1. Vào `/admin/jobs` kiểm tra trạng thái hàng đợi `queue:work`.
  2. Kích hoạt thêm worker xử lý song song (Scale queue workers) để giải phóng hàng đợi dưới 5 phút.

---

## 6. Những điều Tuyệt đối Bị cấm trong Vận hành Tin nhắn

1. **CẤM DÙNG KÊNH SMS/ZNS ĐỂ SPAM QUẢNG CÁO:** Kênh Zalo ZNS và SMS Brandname chỉ được dùng cho các thông báo giao dịch có sự đồng ý của cặp đôi và khách mời. Gửi tin quảng cáo vi phạm chính sách của Bộ TT&TT có thể dẫn đến việc Brandname `MAYHEN` bị nhà mạng khóa vĩnh viễn.
2. **CẤM ĐỂ LỘ ACCESS TOKEN TRÊN FILE LOG CÔNG KHAI:** Hệ thống đã cài đặt cơ chế ẩn token tự động, nghiêm cấm in token thô ra console hoặc chia sẻ qua chat.
3. **CẤM GỬI THỬ NGHIỆM TỚI SỐ ĐIỆN THOẠI KHÁCH HÀNG THẬT:** Khi kiểm thử trên Sandbox, chỉ được nhập số điện thoại nội bộ của nhân viên Mây Hẹn.

---

## 7. Bài viết liên quan
* [ADM-SUPP-001: Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)](/admin/docs/support-messaging/ADM-SUPP-001)
* [ADM-SUPP-002: Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)](/admin/docs/support-messaging/ADM-SUPP-002)
* [ADM-SUPP-004: Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client](/admin/docs/support-messaging/ADM-SUPP-004)
* [ADM-COCK-005: Xử lý sự cố hàng đợi gửi tin Zalo ZNS & SMS Brandname](/admin/docs/checkin-cockpit/ADM-COCK-005)
