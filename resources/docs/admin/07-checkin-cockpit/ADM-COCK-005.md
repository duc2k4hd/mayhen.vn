---
id: ADM-COCK-005
title: "Kiểm tra và hỗ trợ giải phóng hàng đợi gửi tin Zalo/SMS cho khách mời"
category: "Check-in Cockpit & Sơ đồ bàn"
summary: "Hướng dẫn chẩn đoán và xử lý sự cố tắc nghẽn hàng đợi gửi tin nhắn thiệp mời Zalo ZNS và SMS Brandname: làm mới token OA, kiểm tra lô tác vụ (Job Batches), thử lại job lỗi và giải phóng hàng đợi tồn đọng."
reader: "PER-ADM-01 (Điều hành tiệc cưới & Quản trị hệ thống)"
order: 5
updated_at: "2026-11-15"
tags: [admin, queue-monitor, zalo-zns, sms-gateway, retry-failed-jobs]
mapped_page: "ADM-COCK-PAGE"
mapped_route: "admin.checkin.cockpit"
risk_level: "CRITICAL"
required_role: "manager"
---

# Kiểm tra và hỗ trợ giải phóng hàng đợi gửi tin Zalo/SMS cho khách mời

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `CRITICAL` | **Endpoints:** `/admin/system/jobs` & `/admin/system/jobs/batches`  
> **Nguyên tắc an toàn:** Tuyệt đối không xóa trắng (`Purge`) hàng đợi khi chưa kiểm tra nguyên nhân lỗi; việc xóa nhầm sẽ làm mất danh sách khách mời đang chờ nhận thiệp cưới.


![Giao diện quản lý Kiểm tra và hỗ trợ giải phóng hàng đợi gửi tin Zalo/SMS cho khách mời](/assets/client/docs/images/admin/07-checkin-cockpit/adm-cock-005-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Cơ chế Vận hành Hàng đợi Gửi tin Zalo & SMS

Khi các cặp đôi sử dụng tính năng **Gửi thiệp mời hàng loạt** hoặc **Nhắc hẹn nhẹ nhàng (Gentle Reminder)** từ trang quản lý khách mời, hệ thống Mây Hẹn không gửi trực tiếp từng tin nhắn qua HTTP đồng bộ (vì sẽ làm treo trình duyệt của người dùng). Thay vào đó, toàn bộ danh sách khách mời được đóng gói thành các **Lô tác vụ (Job Batches)** và đẩy vào hàng đợi bất đồng bộ (`Queue`).

```
[ CÔ DÂU / CHÚ RỂ ] -> Bấm [Gửi thiệp qua Zalo cho 250 khách]
       |
       v
[ LARAVEL JOB BATCHES ] -> Đóng gói Lô công việc (id: 9a8b7c...)
       |
       v
[ HÀNG ĐỢI (QUEUE: MESSAGING) ] -> Điều tiết tần suất gửi (Rate Limiting: 50 tin/giây)
       |
       +---> [ Zalo ZNS Gateway ]  ---> Gửi tin nhắn ZNS đến điện thoại khách
       |
       +---> [ SMS Brandname API ] ---> Gửi SMS dự phòng nếu khách không dùng Zalo
```

---

## 2. Các Triệu chứng & Nguyên nhân Thường gặp khi Bị Nghẽn Hàng đợi

Khi hàng đợi bị tắc nghẽn, cô dâu chú rể thường gửi ticket hỗ trợ khẩn cấp với nội dung: *"Em đã bấm gửi thiệp từ 30 phút trước nhưng bạn bè em báo vẫn chưa nhận được tin nhắn"*.

Dưới đây là 4 nguyên nhân gốc rễ và dấu hiệu nhận biết:

| Nguyên nhân Gốc rễ | Dấu hiệu trên Màn hình Quản trị | Hệ quả Kỹ thuật |
| :--- | :--- | :--- |
| **Hết hạn Zalo Access Token** | Tab `Failed Jobs` báo lỗi `401 Unauthorized` hoặc `Token expired`. | Access Token của Zalo OA có chu kỳ 25 giờ. Nếu Cronjob tự động làm mới token bị gián đoạn, các job gửi tin sẽ lập tức bị đẩy vào bảng `failed_jobs`. |
| **Hết hạn mức số dư tài khoản ZNS** | Mã lỗi `402 Payment Required` hoặc `Insufficient Balance`. | Số dư tài khoản dịch vụ ZNS hết tiền khiến cổng Zalo từ chối tiếp nhận thêm tin nhắn mới. |
| **Sai định dạng số điện thoại** | Lỗi `Invalid phone number format` (ví dụ số có chữ, thiếu số 0 đầu). | Tác vụ đơn lẻ của khách đó bị fail, nhưng lô tác vụ có thể bị dừng nếu cấu hình không bỏ qua lỗi. |
| **Queue Worker bị treo / Crash** | `Total Pending` tăng vọt nhưng số lượng xử lý không nhích trong nhiều phút. | Tiến trình worker chạy ngầm trên máy chủ bị tràn bộ nhớ (Out of Memory) hoặc mất kết nối database tạm thời. |

---

## 3. Quy trình Kiểm tra tại Trung tâm Tác vụ (`/admin/system/jobs`)

Để nắm bắt chính xác tình trạng hàng đợi, Điều phối viên truy cập **Admin Portal** -> Chọn **[Giám sát Tác vụ (Job Center)]** (`/admin/system/jobs`):

```
+---------------------------------------------------------------------------------------------------+
|  TRUNG TÂM GIÁM SÁT HÀNG ĐỢI HỆ THỐNG (JOB CENTER)                                                |
+---------------------------------------------------------------------------------------------------+
|  [KPIs] Đang chờ (Pending): 184  |  Hỏng (Failed): 12  |  Lô đang chạy: 2  |  Hỏng trong 24h: 1   |
+---------------------------------------------------------------------------------------------------+
|  TAB: [ LÔ CÔNG VIỆC (BATCHES) ]  |  TAB: [ JOB LỖI (FAILED JOBS) ]  |  TAB: [ ĐANG CHỜ (PENDING) ] |
+---------------------------------------------------------------------------------------------------+
|  MÃ LÔ #     | TÊN TIỆC CƯỚI          | TỔNG SỐ | HOÀN THÀNH | THẤT BẠI | TIẾN ĐỘ | THAO TÁC     |
|  9e66-f028   | Vũ Phong & Thảo Linh   | 250 tin | 180 tin    | 12 tin   | 72%     | [THỬ LẠI LÔ] |
|  8b44-a112   | Tuấn Anh & Mai Phương  | 120 tin | 120 tin    | 0 tin    | 100%    | [CHI TIẾT]   |
+---------------------------------------------------------------------------------------------------+
```

1. **Kiểm tra Tab Batches:**
   - Tìm kiếm mã lô hoặc tên cặp đôi gửi tin.
   - Quan sát cột **Tiến độ (`progress`)** và số lượng **Thất bại (`failed_jobs`)**.
2. **Kiểm tra Tab Failed Jobs:**
   - Nhấp vào mã UUID của job bị lỗi để xem chi tiết.
   - Hệ thống tự động kích hoạt bộ lọc bảo mật `SENSITIVE_PATTERNS`, che mờ toàn bộ mật khẩu, access token và API key thành chuỗi `********` để đảm bảo an toàn thông tin tuyệt đối.

---

## 4. Quy trình 4 Bước Giải phóng Hàng đợi Chuẩn SOP

### Bước 1: Khắc phục Nguyên nhân Cốt lõi (Gateway / Token)
* **Nếu lỗi do Zalo Token:** Truy cập Cài đặt Tích hợp -> Nhấp **[Làm mới Token Zalo OA (Refresh Token)]**. Kiểm tra thông báo kết nối thành công.
* **Nếu lỗi do Hết số dư:** Báo ngay cho Kế toán viên nạp bổ sung tiền vào ví Zalo Cloud Account (ZCA).

### Bước 2: Thử lại Job Lỗi Đơn lẻ (Single Job Retry)
Trước khi bấm thử lại hàng loạt, nhân viên nên thử lại 1 job để kiểm chứng đường truyền:
1. Tại danh sách **Failed Jobs**, nhấp nút **[Thử lại (Retry)]** ở dòng đầu tiên.
2. Endpoint `POST /admin/system/jobs/{uuid}/retry` được gọi.
3. Nếu job biến mất khỏi danh sách lỗi và khách nhận được tin nhắn -> Cổng gửi tin đã hoạt động bình thường trở lại.

### Bước 3: Thử lại Toàn bộ Lô Công việc (Batch Retry / Retry All)
Khi cổng gửi tin đã thông suốt:
1. Quay lại tab **Lô công việc (Batches)**, nhấp nút **[Thử lại Lô tác vụ (Retry Batch)]** của tiệc cưới tương ứng.
2. Hoặc nhấp **[Thử lại tất cả Job hỏng (Retry All Failed)]** ở góc trên màn hình.
3. Toàn bộ các tin nhắn bị tạm dừng trước đó sẽ tự động được xếp lại vào hàng đợi và gửi đi ngay lập tức.

### Bước 4: Khởi động lại Queue Worker nếu Bị Nghẽn
Nếu hàng đợi `Pending` vẫn không giảm sau 2 phút:
1. Mở terminal quản trị máy chủ hoặc nhấp nút **[Khởi động lại Worker (Restart Queue)]** trên giao diện Admin.
2. Lệnh `php artisan queue:restart` sẽ ra tín hiệu cho Supervisor kết thúc các worker cũ và sinh ra worker mới tinh tươm để giải tỏa toàn bộ hàng đợi tồn đọng.

---

## 5. Phương án Dự phòng Khẩn cấp (Emergency Fallback)

Trong trường hợp bất khả kháng khi Zalo ZNS hoặc SMS Gateway gặp sự cố diện rộng từ phía nhà mạng:

```
[ QUY TRÌNH CHỮA CHÁY KHẨN CẤP CHO CÔ DÂU CHÚ RỂ ]

1. Hướng dẫn Cô dâu Chú rể mở trang "Quản lý Khách mời" (/khach-moi).
2. Nhấp nút [Xuất danh sách có kèm Link cá nhân hóa] (CSV).
3. Sử dụng mẫu tin nhắn nhanh Mây Hẹn soạn sẵn:
   "Dạ anh/chị ơi, do hệ thống tổng đài Zalo hôm nay bị nghẽn mạng toàn quốc,
    em xin phép gửi trực tiếp link thiệp cưới riêng của gia đình mình tại đây ạ:
    [LINK THIỆP CƯỚI CÁ NHÂN HÓA]"
4. Gửi trực tiếp qua Zalo cá nhân / Facebook Messenger cho nhóm bạn bè thân thiết.
```

---

## 6. Biên bản Lưu vết Tác vụ (Audit Trail)

Mọi thao tác can thiệp hàng đợi của Quản trị viên (`Retry`, `Purge`, `Cancel Batch`) đều tự động lưu vết vào nhật ký vận hành với các thông số:
* `admin_id`: ID tài khoản Quản trị viên.
* `action`: `JOB_BATCH_RETRIED` hoặc `FAILED_JOB_PURGED`.
* `target_uuid`: UUID của Lô tác vụ hoặc Job cụ thể.
* `timestamp`: Thời điểm thực hiện chính xác phục vụ báo cáo đối soát dịch vụ.
