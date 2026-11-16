---
id: ADM-COCK-001
title: "Bảng điều khiển Giám sát Lễ tân (Admin Check-in Cockpit) trực tiếp ngày cưới"
category: "Check-in Cockpit & Sơ đồ bàn"
summary: "Hướng dẫn vận hành Tổng hành dinh Giám sát Lễ tân ngày cưới: theo dõi sĩ số thực tế, tỷ lệ lấp đầy bàn tiệc, xử lý khách phát sinh (Walk-in), điều phối bàn ghế và xuất dữ liệu đối soát sự kiện."
reader: "PER-ADM-01 (Điều hành tiệc cưới & Quản lý sảnh)"
order: 1
updated_at: "2026-11-15"
tags: [admin, checkin-cockpit, live-monitor, walk-in, seating-dispatch]
mapped_page: "ADM-COCK-PAGE"
mapped_route: "admin.checkin.cockpit"
risk_level: "CRITICAL"
required_role: "manager"
---

# Bảng điều khiển Giám sát Lễ tân (Admin Check-in Cockpit) trực tiếp ngày cưới

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `CRITICAL` | **Endpoint:** `GET /admin/checkin/cockpit`  
> **Bằng chứng kiểm toán:** Mọi thao tác điều chỉnh sĩ số, chuyển bàn hoặc đón khách phát sinh đều tự động lưu vết vào nhật ký vận hành với danh tính Điều phối viên (`operator`).


![Giao diện quản lý Bảng điều khiển Giám sát Lễ tân (Admin Check-in Cockpit) trực tiếp ngày cưới](/assets/client/docs/images/admin/07-checkin-cockpit/adm-cock-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Giới thiệu Tổng quan về Admin Check-in Cockpit

**Admin Check-in Cockpit** (`/admin/checkin/cockpit`) là Trạm chỉ huy trung tâm (Command Center) được thiết kế chuyên biệt cho Trưởng ban Điều hành tiệc cưới (Floor Manager), Quản lý sảnh tiệc và Admin kỹ thuật của Mây Hẹn trong khung giờ đón tiếp khách thực tế (thường diễn ra từ 17:00 đến 19:30).

Thay vì phải chạy qua lại giữa bàn lễ tân, khu vực màn chiếu và bếp nhà hàng, Bảng điều khiển Cockpit cung cấp góc nhìn toàn cảnh (Bird's-eye View) theo thời gian thực về:
1. **Sĩ số quan khách thực tế có mặt** so với danh sách RSVP đăng ký ban đầu.
2. **Tỷ lệ lấp đầy hội trường (`Turnout Rate`)** để Bếp tiệc quyết định thời điểm chuẩn xác bắt đầu lên món nóng.
3. **Luồng đón khách theo khung giờ (`Hourly Arrival Flow`)** phát hiện sớm khung giờ cao điểm (Peak Rush Hour).
4. **Trực quan hóa tải trọng bàn tiệc** cảnh báo tức thời các bàn bị tràn chỗ (`Over-capacity`) hoặc thiếu người.
5. **Trạm xử lý ngoại lệ cửa đón tiếp**: Tiếp nhận khách phát sinh không có thiệp (`Walk-in Desk`), đổi bàn tức thì và hủy điểm danh khi thao tác nhầm.

---

## 2. Giao diện & Các Khối Chức năng trên Bảng điều khiển

```
+---------------------------------------------------------------------------------------------------+
|  [Bộ chọn Tiệc cưới: Vũ Phong & Thảo Linh (15/11/2026)]      [Trạng thái: LIVE POLLING 5s] [Xuất CSV] |
+---------------------------------------------------------------------------------------------------+
|  [ 1. KPI TỔNG QUAN ]                                                                             |
|  - Dự kiến: 320 khách   |  - Đã đến: 248 khách (77.5%)  |  - Chưa đến: 72   |  - Phát sinh: 14     |
+---------------------------------------------------------------------------------------------------+
|  [ 2. BIỂU ĐỒ LUỒNG ĐÓN KHÁCH ]                 |  [ 3. KHÁCH MỚI VÀO CỬA (REAL-TIME STREAM) ]    |
|  17:00-17:30: ██ (18 khách)                     |  18:14 - Chú Nguyễn Văn Hùng (Bàn VIP 01 - 2 ng) |
|  17:30-18:00: ████████ (64 khách)               |  18:12 - Bạn Lê Hoàng Nam (Bàn ĐH Bách Khoa - 1) |
|  18:00-18:30: ██████████████ (142 khách - ĐỈNH) |  18:10 - Cô Trần Thị Mai (Bàn Họ Ngoại - 3 ng)   |
+---------------------------------------------------------------------------------------------------+
|  [ 4. TẢI TRỌNG BÀN TIỆC ]                      |  [ 5. TRẠM TÁC NGHIỆP DANH SÁCH QUAN KHÁCH ]     |
|  - Bàn VIP 01: 10/10 chỗ (Đầy)                  |  [Ô tìm kiếm Tên/SĐT/Bàn...] [Bộ lọc Nhóm/Bàn]   |
|  - Bàn Bạn Cấp 3: 11/10 chỗ (CẢNH BÁO QUÁ TẢI!) |  Danh sách khách: [Điểm danh] [Đổi bàn] [Hủy]    |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Điều kiện Tiên quyết & Thiết lập Đầu ca Tiếp tân

1. **Tài khoản & Thiết bị:**
   - Đăng nhập tài khoản quản trị Admin có phân quyền `Manager` trở lên.
   - Khuyến nghị sử dụng iPad Pro 11-12.9 inch hoặc Laptop đặt tại Bàn Giám sát trung tâm sảnh cưới.
   - Kết nối mạng Wi-Fi riêng của Ban tổ chức hoặc 4G/5G độc lập, tránh dùng mạng Wi-Fi công cộng của khách mời.
2. **Chọn Tiệc cưới Hoạt động (`Active Wedding`):**
   - Hệ thống tự động nhận diện tiệc cưới diễn ra vào ngày hôm nay (`wedding_date = Carbon::today()`).
   - Nếu sảnh cưới tổ chức nhiều tiệc cùng ngày hoặc cần kiểm tra tiệc trước/sau, nhấp vào menu thả xuống **[Chọn tiệc cưới]** ở góc trên cùng bên trái để chuyển đổi tức thời.
3. **Kích hoạt Live Polling:**
   - Cockpit tự động thăm dò dữ liệu (`Live Polling`) qua endpoint `/admin/checkin/cockpit/{weddingId}/live-stats` mỗi 5 giây/lần.
   - Chỉ số KPI và danh sách khách mới sẽ tự động nảy số mà không cần nhân viên nhấn `F5` tải lại trang.

---

## 4. Hướng dẫn Giám sát 5 Chỉ số KPI Cốt lõi

Tại thẻ **KPI Thống kê Tiếp tân**, người quản lý theo dõi 5 chỉ số then chốt:

| Chỉ số | Trường dữ liệu | Ý nghĩa Vận hành & Quyết định Thực tế |
| :--- | :--- | :--- |
| **Tổng dự kiến** | `total_expected_attendees` | Tổng sĩ số khách đã xác nhận tham dự qua RSVP (`attendees_count`). Là căn cứ chuẩn bị bàn tiệc và định lượng món ăn. |
| **Đã vào cửa** | `checked_in_attendees` | Tổng số lượng người thực tế đã bước qua cửa đón tiếp. Bao gồm cả người đi kèm (`+1`, `+2`) được lễ tân ghi nhận. |
| **Tỷ lệ có mặt** | `turnout_rate` | Tỷ lệ `%` lấp đầy sảnh tiệc (`checked_in_attendees / total_expected_attendees`). Khi đạt **85%**, Quản lý sảnh thông báo MC chuẩn bị nghi thức lễ cưới. |
| **Đến một phần** | `partially_checked_in_guests` | Quan khách đăng ký đi 2 hoặc 3 người nhưng thực tế check-in ít hơn (ví dụ bận con nhỏ). Cockpit đánh dấu vàng để giải phóng ghế thừa cho khách khác. |
| **Khách phát sinh** | `walk_in_guests` | Quan khách không có thiệp mời trước đó hoặc bạn bè ngoài dự kiến bước vào cửa. Cần theo dõi sát để kê thêm bàn dự phòng. |

---

## 5. Hướng dẫn Thao tác Nghiệp vụ Trực tiếp trên Cockpit

### 5.1. Điểm danh thủ công có hiệu chỉnh sĩ số (Manual Check-in with Headcount)
Áp dụng khi máy quét mã QR của lễ tân gặp trục trặc hoặc khách không mang theo điện thoại có mã QR thiệp cưới:
1. Nhập tên, số điện thoại hoặc mã thiệp của khách vào ô tìm kiếm nhanh.
2. Tại dòng kết quả của khách, nhấp nút **[Điểm danh]** màu xanh lá.
3. Hộp thoại xác nhận hiển thị:
   - **Số người thực tế:** Điều chỉnh số người thực tế đi cùng (mặc định lấy theo số đăng ký RSVP).
   - **Ghi chú lễ tân:** Điền thông tin đặc biệt nếu có (ví dụ: *Khách tặng quà riêng, yêu cầu đồ chay, mang theo xe đẩy em bé*).
   - **Bàn tiệc:** Kiểm tra bàn được chỉ định hoặc chọn lại bàn tiệc nếu bàn cũ đã hết chỗ.
4. Nhấp **[Xác nhận đón tiếp]**. Hệ thống cập nhật `checked_in_at = now()`, phát tín hiệu chào mừng lên Stage Live Wall (nếu có kết nối).

### 5.2. Tiếp nhận Khách phát sinh tại Cửa (Walk-in Desk)
Khi có khách đến dự nhưng tên không có trong hệ thống thiệp ban đầu:
1. Nhấp nút **[+ Đón khách phát sinh (Walk-in)]** ở góc trên danh sách khách.
2. Điền biểu mẫu đón tiếp khẩn cấp:
   - **Họ và tên:** Nhập tên khách (ví dụ: *Anh Đặng Minh Tuấn*).
   - **Xưng hô & Nhóm:** Chọn xưng hô (Anh/Chị/Cô/Chú) và nhóm khách (*Khách phát sinh Nhà Trai* hoặc *Bạn đối tác công ty*).
   - **Số người đi cùng:** Nhập chính xác số người thực tế cùng vào sảnh (từ 1 đến 20 người).
   - **Gán bàn tiệc:** Chọn bàn tiệc dự phòng còn đủ chỗ trống (dựa vào danh sách tải trọng bàn tiệc bên cạnh).
   - **Ghi chú:** Lý do phát sinh (*Bạn đồng nghiệp đi cùng anh Tuấn*).
3. Nhấp **[Lưu & Đón tiếp]**. Khách được tự động gắn nhãn `[WALK-IN]`, hệ thống tính toán tăng sĩ số và trừ ghế trống bàn tiệc tương ứng.

### 5.3. Điều phối Chuyển đổi Bàn tiệc tức thời (Table Reassignment)
Khi một bàn tiệc bị quá tải ghế hoặc khách muốn ngồi chung với nhóm bạn khác:
1. Tìm khách trong danh bạ Cockpit.
2. Nhấp vào tên bàn tiệc hiện tại của khách hoặc nút **[Đổi bàn]**.
3. Danh sách bàn tiệc hiện ra kèm trạng thái ghế thời gian thực:
   - `Bàn 08 - Bạn Cấp 3 (8/10 chỗ - Còn 2 ghế)` -> **Khả dụng**.
   - `Bàn 09 - Bạn Cấp 3 (10/10 chỗ - HẾT CHỖ)` -> **Khóa chọn**.
4. Chọn bàn tiệc mới và nhấp **[Cập nhật bàn]**. Hệ thống tự động chuyển nhóm khách sang bàn mới, cập nhật bảng phân bổ chỗ ngồi và màn hình tra cứu Table Finder.

### 5.4. Hủy điểm danh an toàn (Undo / Cancel Check-in)
Nếu lễ tân quét nhầm mã QR hoặc chọn nhầm tên khách mời khác:
1. Nhấp nút **[Hủy điểm danh]** (biểu tượng Undo) cạnh khách vừa điểm danh.
2. Hệ thống yêu cầu xác nhận: *"Bạn có chắc chắn muốn đưa quan khách này về trạng thái Chưa đến?"*.
3. Nhấp **[Đồng ý hủy]**. Hệ thống xóa mốc `checked_in_at`, giảm sĩ số thực tế trên KPI và trừ lượt check-in khỏi màn hình chào mừng sảnh tiệc.

---

## 6. Xuất Dữ liệu Báo cáo Đối soát Sự kiện (Attendance CSV Export)

Khi tiệc cưới bước vào giai đoạn khai tiệc (khoảng 19:15), Quản lý ca cần xuất tệp báo cáo điểm danh để bàn giao cho Gia đình Cô dâu Chú rể và Nhà hàng tiệc cưới:

1. Nhấp nút **[Xuất báo cáo CSV]** trên thanh công cụ Cockpit.
2. Tệp tin được tự động xuất dưới định dạng UTF-8 kèm mã nhận diện **BOM (`\xEF\xBB\xBF`)**, đảm bảo khi mở bằng Microsoft Excel tiếng Việt không bao giờ bị lỗi font hay vỡ dấu.
3. Nội dung tệp CSV bao gồm:
   - `Mã khách hàng`, `Họ tên`, `Xưng hô`, `Nhóm khách`.
   - `Bàn tiệc phân bổ`, `Khu vực sảnh`.
   - `Số người đăng ký RSVP` vs `Số người check-in thực tế`.
   - `Thời gian vào cửa chính xác (HH:mm:ss)`.
   - `Loại hình đón tiếp (Mã QR / Thủ công / Phát sinh Walk-in)`.
   - `Điều phối viên thực hiện (Operator)`.
   - `Ghi chú lễ tân`.

---

## 7. Các Tình huống Sự cố Thường gặp & Xử lý Khẩn cấp

| Sự cố phát sinh | Nguyên nhân | Quy trình Xử lý Khẩn cấp trên Cockpit |
| :--- | :--- | :--- |
| **Bàn tiệc bị cảnh báo tràn ghế đỏ (`is_over_capacity`)** | Khách dẫn thêm người ngoài đăng ký hoặc ngồi nhầm bàn. | Mở danh sách bàn tiệc trên Cockpit, kiểm tra số ghế dư của các bàn liền kề trong cùng phân khu (`zone`). Sử dụng chức năng **[Đổi bàn]** chuyển 1-2 khách sang bàn còn chỗ, hoặc liên hệ Quản lý sảnh kê thêm ghế phụ (ghế đôn) trước khi khách vào bàn. |
| **Mất kết nối Internet tại Sảnh đón tiếp** | Đứt cáp Wi-Fi trung tâm hội nghị. | Kích hoạt ngay **Gói cứu hộ ngoại tuyến (`Emergency Offline Pack`)**. Lễ tân tiếp tục check-in offline trên thiết bị di động, dữ liệu được lưu tạm vào IndexedDB và tự động đồng bộ trở lại Cockpit ngay khi có mạng trở lại. |
| **Khách mời báo đã check-in nhưng không có tên trên Live Wall** | Khách quét mã nhưng chưa xác nhận hoàn tất hoặc chọn ẩn danh. | Tìm tên khách trên Cockpit, kiểm tra cột `checked_in_at`. Nếu đã check-in, kiểm tra cài đặt quyền riêng tư (`privacy_mode`) của thiệp cưới để đảm bảo khách không bị đánh dấu ẩn danh. |

---

## 8. Bảng Ký duyệt Kiểm toán & Bàn giao Ca trực

```
========================================================================================
BIÊN BẢN BÀN GIAO SĨ SỐ ĐÓN TIẾP NGÀY CƯỚI (MÂY HẸN CHECK-IN COCKPIT)
Tiệc cưới: Vũ Phong & Thảo Linh | Ngày: 15/11/2026 | Sảnh: Grand Ballroom
----------------------------------------------------------------------------------------
- Tổng khách mời RSVP xác nhận : 320 khách
- Khách thực tế vào cửa        : 262 khách (Tỷ lệ: 81.9%)
- Khách phát sinh (Walk-in)    : 14 khách (đã bố trí vào Bàn Dự Phòng 01 & 02)
- Khách hủy / vắng mặt         : 58 khách
----------------------------------------------------------------------------------------
Trưởng ban Điều phối Tiệc cưới                     Đại diện Gia đình Cô dâu / Chú rể
(Ký và ghi rõ họ tên)                             (Ký và ghi rõ họ tên)
========================================================================================
```
