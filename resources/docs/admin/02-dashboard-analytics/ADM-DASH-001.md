---
id: ADM-DASH-001
title: "Hướng dẫn đọc Dashboard doanh thu thực tế, biểu đồ tăng trưởng và tỷ lệ chuyển đổi"
category: "Trung tâm Điều hành & Giám sát"
summary: "Hướng dẫn Ban Giám Đốc và Quản lý khai thác Bảng điều khiển Tổng quan (Admin KPI Dashboard): đọc số liệu doanh thu thực thu, biểu đồ tài chính 7 ngày, tỷ lệ chuyển đổi gói dịch vụ và các cảnh báo tác nghiệp cần can thiệp khẩn cấp."
reader: "PER-ADM-03 (Ban Giám Đốc / Quản Lý Vận Hành)"
order: 1
updated_at: "2026-11-15"
tags: [admin, dashboard, revenue-metrics, growth-charts, conversion-rate]
mapped_page: "ADM-DASH-PAGE"
mapped_route: "admin.dashboard"
risk_level: "HIGH"
required_role: "manager"
---

# Hướng dẫn đọc Dashboard doanh thu thực tế, biểu đồ tăng trưởng và tỷ lệ chuyển đổi

> [!NOTE]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `HIGH` (Đọc dữ liệu tài chính & chiến lược) | **Endpoint:** `GET /admin/dashboard`  
> **Chu kỳ cập nhật:** Dữ liệu tài chính và đơn hàng được tính toán trực tiếp từ cơ sở dữ liệu (Real-time DB Query), phản ánh chính xác từng giao dịch VietQR SePay vừa khớp lệnh.

---

## 1. Giới thiệu Tổng quan về Admin KPI Dashboard

**Admin KPI Dashboard** (`/admin/dashboard`) là trung tâm chỉ huy số liệu cấp cao nhất của nền tảng Mây Hẹn, được xây dựng theo chuẩn mực *Quiet Luxury UI* — tối giản, sang trọng, tập trung vào bản chất dữ liệu mà không gây rối mắt bởi các biểu đồ rườm rà.

Dashboard giải quyết 3 câu hỏi sống còn của Ban Lãnh đạo mỗi ngày:
1. **Dòng tiền thực thu (Net Cash Flow):** Hôm nay và 7 ngày qua nền tảng thu về bao nhiêu tiền từ các gói dịch vụ cưới (Silver, Gold, Diamond)?
2. **Quy mô & Tốc độ Tăng trưởng:** Có bao nhiêu cặp đôi đăng ký mới, bao nhiêu thiệp cưới đã hoàn thiện xuất bản và bao nhiêu quan khách được tạo trên hệ thống?
3. **Cảnh báo Tác nghiệp Cần Giải cứu:** Có đơn hàng nào bị treo thanh toán, lời chúc nào chờ duyệt hoặc đám cưới nào sắp diễn ra mà chưa kịp hoàn thiện thiệp?

---

## 2. Bản tin Chào buổi sáng Cá nhân hóa (Role-Aware Morning Briefing)

Ngay tại đỉnh màn hình Dashboard, hệ thống kích hoạt **Sáng kiến Briefing Thông minh** tự động nhận diện thời gian và vai trò của người đăng nhập:

```
+---------------------------------------------------------------------------------------------------+
|  ✦ Chào buổi sáng, Giám đốc Nguyễn Văn Tuấn!                                                      |
|  Hôm nay hệ thống Mây Hẹn đang vận hành êm ái:                                                     |
|  - Doanh thu 24h qua: 14.850.000 đ (18 đơn hàng thanh toán thành công)                             |
|  - Đám cưới diễn ra hôm nay: 4 tiệc cưới (đang kích hoạt Check-in Cockpit)                        |
|  - Yêu cầu hỗ trợ đang mở: 2 ticket (0 ticket khẩn cấp)                                           |
+---------------------------------------------------------------------------------------------------+
```

Khối Briefing này giúp lãnh đạo nắm trọn tình hình chỉ trong **30 giây đầu ngày** mà không cần phải truy vấn từng bảng dữ liệu.

---

## 3. Thẻ 4 Chỉ số KPI Tài chính & Quy mô Cốt lõi

Các thẻ chỉ số KPI trên hàng đầu tiên phản ánh sức khỏe tài chính và năng lực mở rộng của nền tảng:

```
+-----------------------+-----------------------+-----------------------+-----------------------+
| TỔNG DOANH THU THỰC   | ĐƠN HÀNG THÀNH CÔNG   | THIỆP ĐÃ XUẤT BẢN     | QUAN KHÁCH ĐÃ TẠO     |
| 148.650.000 đ         | 342 đơn               | 524 / 680 thiệp (77%) | 128.450 người         |
| +18.5% so với tháng tr| 100% khớp lệnh VietQR | Tỷ lệ hoàn thiện cao  | Bình quân 245 khách/t |
+-----------------------+-----------------------+-----------------------+-----------------------+
```

| Chỉ số | Cách tính toán trong Hệ thống | Ý nghĩa Chiến lược |
| :--- | :--- | :--- |
| **Tổng doanh thu thực** | `Order::where('status', 'paid')->sum('amount')` | Doanh thu thực nhận sau khi tiền đã vào tài khoản ngân hàng qua cổng SePay. Không tính đơn hàng đang chờ hoặc đơn hủy. |
| **Đơn hàng thành công** | `Order::where('status', 'paid')->count()` | Số lượng giao dịch nâng cấp gói trả phí thành công (Free -> Silver/Gold/Diamond). |
| **Thiệp đã xuất bản** | `Invitation::where('is_published', true)->count()` | Số thiệp cưới đã mở liên kết công khai cho khách xem. Tỷ lệ `Xuất bản / Tổng thiệp` phản ánh mức độ hài lòng với Studio Tạo Thiệp. |
| **Tổng quan khách** | `Guest::count()` | Quy mô tiếp cận thực tế của Mây Hẹn (Mỗi khách mời nhận thiệp là một khách hàng tiềm năng tương lai). |

---

## 4. Biểu đồ Xu hướng Doanh thu 7 ngày (7-Day Revenue Trend)

Biểu đồ cột được render bằng HTML/CSS thuần (Pure Vanilla, không dùng thư viện Chart.js hay Highcharts cồng kềnh), đảm bảo tốc độ mở trang dưới **200ms**:

```
Doanh thu (VNĐ)
 20M +
     |                                           ██ [T7: 18.5M]
 15M +                                           ██
     |                           ██ [T5: 12.0M]  ██
 10M +           ██ [T3: 8.5M]   ██              ██          ██ [Hôm nay: 9.8M]
     |   ██      ██              ██              ██  ██      ██
  5M +   ██      ██              ██              ██  ██      ██
     +---+-------+---------------+---------------+---+-------+-----------------> Ngày
        T2      T3              T4              T5  T6      T7      CN
```

* **Quy luật Thứ Sáu - Thứ Bảy:** Doanh thu Mây Hẹn thường đạt đỉnh vào Thứ 6 và Thứ 7 khi các cặp đôi có thời gian nghỉ cuối tuần để cùng nhau duyệt thiệp cưới và chuyển khoản nâng cấp gói.
* **Cột "Hôm nay":** Tính toán động các khoản thu từ 00:00 của ngày hiện tại, cập nhật từng phút khi có thông báo Webhook SePay gửi về.

---

## 5. Thống kê Phản hồi RSVP & Tỷ lệ Tham dự Toàn sàn

Tại thẻ **Chỉ số Tương tác Khách mời (Engagement & RSVP Metrics)**:
* **Tổng lượt phản hồi RSVP:** Đếm từ bảng `guest_responses`.
* **Tỷ lệ xác nhận tham dự (`Attendance Rate`):**
  $$\text{Attendance Rate} = \frac{\text{Số khách chọn "Sẽ tham dự"}}{\text{Tổng lượt gửi phản hồi}} \times 100\%$$
* Con số chuẩn toàn sàn Mây Hẹn dao động từ **78% đến 84%**. Nếu một tiệc cưới có tỷ lệ tham dự dưới **60%**, hệ thống sẽ gắn nhãn cảnh báo để đội CSKH liên hệ hỗ trợ cô dâu chú rể gửi thiệp nhắc lịch.

---

## 6. Bảng Cảnh báo Tác nghiệp Cần Can thiệp (Actionable Alerts)

Cột bên phải Dashboard là khu vực tác nghiệp khẩn cấp dành cho Quản lý ca trực:

1. **Đơn hàng chờ thanh toán (`Pending Orders`):**  
   Các đơn hàng quá 15 phút chưa nhận được biến động số dư. Bấm vào để kiểm tra đối soát thủ công mã đơn hàng trong sao kê ngân hàng.
2. **Lời chúc chờ duyệt (`Pending Wishes`):**  
   Số lượng lời chúc hoặc ảnh Photo Drop khách gửi đang nằm trong hàng đợi kiểm duyệt trước khi hiển thị lên Live Wall.
3. **Thiệp bị khóa bảo vệ (`Locked Invitations`):**  
   Thiệp cưới tạm thời bị khóa do gia đình yêu cầu hoặc phát hiện nghi vấn gian lận.

---

## 7. Nhật ký Kiểm toán 5 Giao dịch Gần nhất (Recent Audit Stream)

Cuối màn hình Dashboard là danh sách 5 tác vụ vận hành quan trọng vừa diễn ra, trích xuất từ service `AuditLogger`:
* Giao dịch hoàn tiền cho khách hàng (`REFUND_PROCESSED`).
* Nâng cấp gói dịch vụ thủ công cho đối tác (`PLAN_UPGRADED`).
* Can thiệp đổi bàn tiệc trong giờ cao điểm (`TABLE_REASSIGNED`).
* Phê duyệt ảnh kiểm duyệt gắt gao (`PHOTO_APPROVED`).

Giúp Ban Lãnh đạo nắm bắt mọi hành động can thiệp dữ liệu của nhân viên mà không cần mở sâu vào module Audit Log chuyên biệt.
