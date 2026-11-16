---
id: ADM-DASH-002
title: "Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn"
category: "Trung tâm Điều hành & Giám sát"
summary: "Hướng dẫn khai thác Radar Sức khỏe Ngày cưới: thang điểm 100 đánh giá độ hoàn thiện thiệp cưới, 5 trụ cột sẵn sàng, cơ chế phân cấp rủi ro (Xanh/Vàng/Đỏ) và quy trình giải cứu đám cưới nguy cơ trễ hạn."
reader: "PER-ADM-03 (Ban Giám Đốc / Trưởng Nhóm Chăm Sóc Khách Hàng)"
order: 2
updated_at: "2026-11-15"
tags: [admin, readiness-radar, risk-assessment, health-score, deadline-alert]
mapped_page: "ADM-DASH-PAGE"
mapped_route: "admin.dashboard"
risk_level: "HIGH"
required_role: "manager"
---

# Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn

> [!IMPORTANT]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `HIGH` | **Service lõi:** `App\Services\WeddingReadinessRadarService`  
> **Cửa sổ giám sát chủ động:** Quét toàn bộ các đám cưới sẽ diễn ra trong vòng **14 đến 30 ngày tới** để phát hiện sớm các cặp đôi bị tắc nghẽn khâu chuẩn bị trước khi quá muộn.


![Giao diện quản lý Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn](/assets/client/docs/images/admin/02-dashboard-analytics/adm-dash-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Giới thiệu về Radar Sức khỏe Ngày cưới (Wedding Readiness Radar)

Đám cưới là sự kiện chỉ diễn ra một lần trong đời với áp lực thời gian cực lớn. Nhiều cặp đôi vì bận rộn công việc chuẩn bị chụp ảnh, trang phục nên đã đăng ký tài khoản Mây Hẹn nhưng quên chưa hoàn thiện thông tin thiệp, chưa nhập địa chỉ sảnh tiệc hoặc chưa xuất bản thiệp mời dù ngày cưới chỉ còn cách chưa đầy 7 ngày.

**Wedding Readiness Radar** là hệ thống AI giám sát phòng ngừa rủi ro tự động:
* Tự động quét toàn bộ đám cưới sắp diễn ra (`scan(daysAhead = 14)`).
* Tính toán **Điểm Sẵn sàng (Readiness Score)** theo thang điểm từ **0 đến 100 điểm**.
* Phân cấp rủi ro theo 3 mức màu: **Xanh (An toàn)**, **Vàng (Cần lưu ý)**, **Đỏ (Nguy cơ cao - Cần can thiệp khẩn cấp)**.
* Giúp đội ngũ CSKH chủ động "giải cứu" đám cưới trước khi cô dâu chú rể rơi vào hoảng loạn.

---

## 2. Thang điểm 100 & 5 Trụ cột Sẵn sàng Cốt lõi

Thang điểm được tính toán khoa học dựa trên 5 thành phần bắt buộc cấu thành một thiệp cưới hoàn hảo:

```
+---------------------------------------------------------------------------------------------------+
|  5 TRỤ CỘT ĐÁNH GIÁ ĐỘ SẴN SÀNG NGÀY CƯỚI (TỔNG: 100 ĐIỂM)                                       |
+---------------------------------------------------------------------------------------------------+
|  1. Địa điểm & Bản đồ sảnh tiệc  [25 ĐIỂM] : Tên sảnh, địa chỉ đón khách, tọa độ Google Maps     |
|  2. Hình ảnh đại diện & bìa thiệp [25 ĐIỂM] : Ảnh chân dung chính của Cô dâu & Chú rể             |
|  3. Khách mời & Sức chứa bàn     [20 ĐIỂM] : Đã nhập danh bạ khách, kiểm soát không tràn ghế       |
|  4. Âm nhạc nền thiệp cưới       [15 ĐIỂM] : Giai điệu lãng mạn đã được chọn và tải thành công     |
|  5. Trạng thái xuất bản trực tuyến[15 ĐIỂM] : Đã bấm nút công bố thiệp mời cho khách xem          |
+---------------------------------------------------------------------------------------------------+
```

### Chi tiết các lỗi hệ thống tự động phát hiện (`issues`):

| Mã lỗi (`code`) | Mức nghiêm trọng | Điểm trừ | Mô tả nghiệp vụ |
| :--- | :--- | :--- | :--- |
| `missing_venue` | `critical` | -25 điểm | Chưa điền tên sảnh tiệc hoặc địa chỉ tổ chức (Khách mời nhận thiệp sẽ không biết đi đến đâu). |
| `missing_cover_photo` | `critical` | -25 điểm | Thiệp cưới chưa tải ảnh đại diện chính của cặp đôi, đang để khung ảnh trống. |
| `no_published_invitation`| `critical` | -15 điểm | Cặp đôi đã chỉnh sửa xong nhưng chưa bấm nút [Xuất bản], thiệp vẫn ở trạng thái nháp. |
| `capacity_overflow` | `warning` | -10 điểm | Sĩ số khách xác nhận tham dự vượt quá tổng số ghế của các bàn tiệc đã lập. |
| `missing_music` | `warning` | -15 điểm | Thiệp chưa chọn bài hát mở đầu ngày vui. |
| `empty_guest_list` | `info` | -0 điểm | Chưa thêm bất kỳ khách mời nào vào danh bạ khách mời trên hệ thống. |

---

## 3. Phân cấp Mức độ Rủi ro (Risk Levels)

```
[ THANG PHÂN CẤP RỦI RO NGÀY CƯỚI ]

  100 Điểm +-----------------------------------------------------------+
           |  MỨC XANH (GREEN - READY)                                  | -> Sẵn sàng 100%, tự tin đón khách.
   80 Điểm +-----------------------------------------------------------+
           |  MỨC VÀNG (YELLOW - MEDIUM RISK)                           | -> Thiếu nhạc hoặc chưa hoàn tất bàn.
   60 Điểm +-----------------------------------------------------------+
           |  MỨC ĐỎ (RED - HIGH RISK / CRITICAL)                       | -> NGUY CƠ CAO: Thiếu sảnh hoặc ảnh bìa.
    0 Điểm +-----------------------------------------------------------+
```

1. **Mức Xanh (`score >= 80`):** Đám cưới ở trạng thái lý tưởng. Mọi thông tin cốt lõi đã hoàn thiện, thiệp đã xuất bản và đang vận hành ổn định.
2. **Mức Vàng (`60 <= score < 80`):** Đám cưới còn thiếu sót nhỏ (ví dụ chưa chọn nhạc nền hoặc danh sách khách RSVP đang vượt nhẹ số ghế bàn tiệc).
3. **Mức Đỏ (`score < 60`):** Đám cưới đối mặt rủi ro nghiêm trọng (thiếu địa điểm sảnh tiệc, chưa có ảnh bìa hoặc chưa từng xuất bản thiệp). Đặc biệt nếu thời gian còn lại dưới **7 ngày** (`days_remaining <= 7`), hệ thống đưa vào danh sách **Khẩn cấp Loại 1**.

---

## 4. Giao diện Radar trên Dashboard & Cách Theo dõi

Tại khối **Radar Chỉ số Sức khỏe Ngày cưới** trên Dashboard:

```
+---------------------------------------------------------------------------------------------------+
|  RADAR CHỈ SỐ SỨC KHỎE NGÀY CƯỚI (14 NGÀY TỚI)                    [Điểm bình quân: 82.4 / 100]    |
+---------------------------------------------------------------------------------------------------+
|  Tổng đám cưới sắp diễn ra: 28  |  An toàn: 20 đám cưới  |  Lưu ý: 5 đám cưới  |  Nguy cơ cao: 3   |
+---------------------------------------------------------------------------------------------------+
|  DANH SÁCH ĐÁM CƯỚI NGUY CƠ CAO CẦN CAN THIỆP GẤP:                                                |
|                                                                                                   |
|  1. Đặng Tuấn & Mai Lan (Ngày cưới: 18/11/2026 - CÒN 3 NGÀY)                 [ĐIỂM: 35/100 - ĐỎ]  |
|     - Lỗi: [missing_venue] Chưa có địa chỉ nhà hàng tiệc cưới.                                    |
|     - Lỗi: [no_published_invitation] Chưa bấm xuất bản thiệp mời.                                |
|     -> THAO TÁC: [Mở Hồ sơ Khách hàng] [Gọi hỗ trợ gấp: 0988.xxx.xxx]                            |
|                                                                                                   |
|  2. Hoàng Long & Bích Ngọc (Ngày cưới: 20/11/2026 - CÒN 5 NGÀY)              [ĐIỂM: 50/100 - ĐỎ]  |
|     - Lỗi: [missing_cover_photo] Thiếu ảnh bìa cô dâu chú rể.                                    |
|     -> THAO TÁC: [Mở Hồ sơ Khách hàng] [Nhắn tin Zalo hỗ trợ]                                    |
+---------------------------------------------------------------------------------------------------+
```

---

## 5. Quy trình 3 Bước Giải cứu Đám cưới Nguy cơ Cao (SOP)

Khi phát hiện đám cưới nằm trong danh sách Mức Đỏ:

### Bước 1: Phân tích Hồ sơ & Lỗi cụ thể
* Nhấp vào tên cặp đôi để mở chi tiết tiệc cưới.
* Quan sát khối Checklist 5 mục: Xác định chính xác lỗi đang bị trừ điểm (`venue`, `cover_photo`, `published` hay `capacity`).

### Bước 2: Liên hệ Chăm sóc Chủ động (Proactive Outreach)
* Trưởng nhóm CSKH phân công nhân viên gọi điện trực tiếp hoặc nhắn tin Zalo cho cô dâu chú rể với văn phong chu đáo, ân cần:
  *"Dạ em chào anh Tuấn, em là chuyên viên hỗ trợ tiệc cưới từ Mây Hẹn. Em thấy ngày vui của anh chị chỉ còn 3 ngày nữa (18/11) nhưng thiệp cưới online của mình hiện vẫn chưa điền tên nhà hàng tiệc cưới và chưa bấm xuất bản. Em gọi để xin phép hỗ trợ anh chị cập nhật ngay bây giờ để kịp gửi đến bạn bè ạ!"*

### Bước 3: Hỗ trợ Nhập liệu & Bấm Xuất bản Thay (Delegated Setup)
* Nếu cặp đôi đang di chuyển bận rộn: Xin phép cặp đôi nhận thông tin địa chỉ qua Zalo, nhân viên Admin mở Studio Tạo Thiệp điền địa chỉ chính xác, gắn bản đồ Google Maps và kích hoạt nút **[Xuất bản]** giúp khách hàng.
* Điểm Readiness Score lập tức nhảy từ **35** lên **85 điểm (Chuyển sang Mức Xanh)**, giải tỏa hoàn toàn nguy cơ sự cố ngày cưới.

---

## 6. Lưu vết & Đánh giá Hiệu quả Đội ngũ Hỗ trợ

Hàng tuần, Ban Lãnh đạo dựa vào chỉ số `avg_score` (Điểm bình quân toàn sàn) và tỷ lệ chuyển hóa từ Đỏ sang Xanh để đánh giá KPI của Bộ phận Chăm sóc Khách hàng:
* **Mục tiêu toàn sàn:** `avg_score >= 80 điểm`.
* **Tỷ lệ đám cưới trễ hạn:** Duy trì ở mức **0%**.
