---
id: ADM-INVT-003
title: "Quy trình hỗ trợ khách đổi ngày cưới (Reschedule) và cập nhật thiệp đồng loạt"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Quy trình chuẩn hóa hỗ trợ cô dâu chú rể dời ngày cưới (Wedding Reschedule): mô phỏng số lượng khách bị ảnh hưởng, cập nhật đồng loạt lịch trình và đếm ngược, cấu hình tái xác nhận RSVP và phát thông báo đính chính Zalo/SMS."
reader: "PER-ADM-01 (Quản lý vận hành / Chuyên viên hỗ trợ khách hàng)"
order: 3
updated_at: "2026-11-15"
tags: [admin, wedding-reschedule, rsvp-reconfirm, date-migration, broadcast-notice]
mapped_page: "ADM-INVT-PAGE"
mapped_route: "admin.invitations.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# Quy trình hỗ trợ khách đổi ngày cưới (Reschedule) và cập nhật thiệp đồng loạt

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `CRITICAL` | **Endpoints:** `POST /admin/reschedule` & `POST /admin/reschedule/{logId}/notify`  
> **Tác động dữ liệu lớn:** Thao tác dời ngày cưới sẽ tác động trực tiếp lên toàn bộ thiệp cưới online, khối đếm ngược, lịch trình sự kiện và hàng trăm khách mời trong danh bạ. Bắt buộc đối soát văn bản yêu cầu trước khi bấm thực thi.

---

## 1. Khi nào Cần Kích hoạt Quy trình Dời Ngày Cưới?

Trong văn hóa cưới hỏi Việt Nam, việc dời ngày cưới (Reschedule) có thể phát sinh do:
1. **Gia đình xem lại ngày hoàng đạo:** Gia đình hai bên thống nhất dời sang ngày đẹp hơn theo lịch âm.
2. **Sự cố trung tâm hội nghị tiệc cưới:** Khách sạn hoặc sảnh tiệc bị trùng lịch hoặc sửa chữa đột xuất.
3. **Lý do bất khả kháng:** Thời tiết mưa bão, dịch bệnh hoặc việc gia đình đột xuất.

Khi ngày cưới thay đổi, nếu chỉnh sửa thủ công từng nơi sẽ rất dễ bị sót (ví dụ sửa ngày trên ảnh bìa nhưng quên sửa lịch đếm ngược, quên sửa file lịch Google Calendar hoặc không thông báo kịp cho khách). Mây Hẹn cung cấp **Động cơ Dời Lịch Cưới Đồng Loạt (`AdminRescheduleService`)** để giải quyết toàn diện trong một quy trình duy nhất.

---

## 2. Các Hệ thống Được Đồng Bộ Hóa Tức Thời khi Đổi Ngày

Khi Quản trị viên cập nhật ngày cưới mới, hệ thống tự động kích hoạt cập nhật đồng thời 6 cấu phần:

```
[ LỆNH CẬP NHẬT NGÀY CƯỚI MỚI (NEW_DATE) ]
       |
       +---> [ 1. NGÀY CƯỚI CHÍNH (weddings.wedding_date) ]
       |
       +---> [ 2. KHỐI ĐẾM NGƯỢC (COUNTDOWN TIMER) ] -> Tự động tính lại số ngày, giờ, phút
       |
       +---> [ 3. BỘ LỊCH THÁNG (WEDDING CALENDAR) ] -> Tô đậm ngày mới trên lịch thiệp
       |
       +---> [ 4. FILE LỊCH (.ICS / GOOGLE CALENDAR) ] -> Cập nhật đường link Add to Calendar
       |
       +---> [ 5. LỊCH TRÌNH SỰ KIỆN (TIMELINE EVENTS) ] -> Tịnh tiến ngày tổ chức các lễ
       |
       +---> [ 6. QUAN KHÁCH & RSVP (GUEST RESPONSES) ] -> Gửi thông báo đính chính
```

---

## 3. Quy trình 4 Bước Hỗ trợ Khách hàng Dời Ngày Cưới Chuẩn SOP

### Bước 1: Tiếp nhận & Xác thực Danh tính Yêu cầu
1. Tiếp nhận yêu cầu dời ngày qua hotline hoặc ticket hỗ trợ.
2. Xác minh thông tin chủ tài khoản (Họ tên, SĐT, Email đăng ký). Tuyệt đối không nhận lệnh đổi ngày từ người tự xưng là bạn bè hoặc họ hàng nếu không có ủy quyền bằng văn bản từ chủ tài khoản.

### Bước 2: Mô phỏng Tác động Dữ liệu (Simulate Impact)
* Truy cập **Admin Portal** -> Chọn chuyên mục **[Dời lịch cưới (Reschedule)]** (`/admin/reschedule`).
* Chọn tiệc cưới cần xử lý. Hệ thống hiển thị bảng phân tích tác động:
  - Ngày cưới hiện tại (`current_wedding_date`).
  - Tổng số khách mời bị ảnh hưởng (`affected_guests_count`).
  - Số lượng thiệp cưới liên quan đang xuất bản.

### Bước 3: Điền Thông tin Cập nhật Ngày mới
Điền biểu mẫu đổi ngày cưới:
* **Ngày cưới mới (`new_date`):** Chọn chính xác ngày dương lịch mới.
* **Địa điểm mới (`new_venue` & `new_venue_address`):** Điền tên sảnh tiệc và địa chỉ mới (nếu có đổi địa điểm tổ chức; nếu giữ nguyên thì để trống).
* **Lý do dời lịch (`reason`):** Ghi rõ nguyên nhân (ví dụ: *Gia đình hai bên thống nhất dời ngày sang 28/12/2026 theo yêu cầu của chú rể Vũ Phong*).
* **Chính sách Tái xác nhận RSVP (`rsvp_action`):**
  - `keep_existing` (Giữ nguyên): Giữ nguyên danh sách khách đã xác nhận tham dự (thường dùng khi chỉ đổi giờ tiệc hoặc dời trong cùng tuần).
  - `request_reconfirm` (Yêu cầu tái xác nhận): Đưa trạng thái tham dự của toàn bộ khách về `Chờ tái xác nhận`, vì đổi ngày khác có thể khiến nhiều khách bị trùng lịch công tác.

Nhấp **[Xác nhận Cập nhật Ngày mới]**. Hệ thống lưu vết vào bảng `wedding_reschedule_logs`.

### Bước 4: Phát Thông báo Đính chính tới Quan khách (Broadcast Notice)
Sau khi ngày mới đã được cập nhật:
1. Nhấp nút **[Phát thông báo đính chính (Broadcast Notification)]**.
2. Hệ thống chuẩn bị mẫu tin nhắn đính chính trang trọng:
   ```
   "Kính gửi [Xưng hô] [Tên Khách],
    Do có sự thay đổi kế hoạch của hai bên gia đình, Lễ thành hôn của [Chú Rể] & [Cô Dâu] 
    xin trân trọng thông báo được dời sang ngày mới: [NGÀY CƯỚI MỚI].
    Địa điểm: [ĐỊA ĐIỂM SẢNH TIỆC].
    Anh/chị vui lòng cập nhật lại thông tin thiệp cưới và xác nhận tham dự giúp chúng em tại:
    [LINK THIỆP CƯỚI CÁ NHÂN HÓA]"
   ```
3. Nhấp **[Xác nhận Phát thông báo]**. Tác vụ được đẩy vào hàng đợi gửi tin Zalo/SMS an toàn (`POST /admin/reschedule/{logId}/notify`).

---

## 4. Theo dõi Tiến độ Tái xác nhận (Reconfirmation Tracker)

Tại bảng điều khiển `/admin/reschedule`, Quản trị viên theo dõi sát sao:
* Cột **Quan khách được thông báo (`notified_guests_count` / `affected_guests_count`)**.
* Tỷ lệ phản hồi lại sau khi dời lịch cưới.
* Cung cấp số liệu kịp thời cho gia đình cô dâu chú rể để họ chủ động liên hệ các vị khách lớn tuổi chưa mở tin nhắn.

---

## 5. Xuất File Báo cáo Nhật ký Dời lịch (CSV UTF-8 BOM)

Khi Ban Giám Đốc hoặc Kế toán yêu cầu báo cáo đối soát các tiệc cưới đã dời lịch:
1. Nhấp nút **[Xuất báo cáo CSV]** trên màn hình `/admin/reschedule`.
2. Hệ thống xuất tệp CSV UTF-8 BOM lưu trữ đầy đủ:
   - `Mã tiệc cưới`, `Tên cặp đôi`.
   - `Ngày cưới cũ` vs `Ngày cưới mới`.
   - `Địa điểm cũ` vs `Địa điểm mới`.
   - `Số lượng khách bị ảnh hưởng`.
   - `Số lượng khách đã được phát thông báo`.
   - `Chính sách RSVP đã áp dụng`.
   - `Người thực hiện (Operator)` và `Lý do dời lịch`.
