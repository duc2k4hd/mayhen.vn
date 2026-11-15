---
id: ADM-DASH-004
title: "Xuất báo cáo kinh doanh tổng hợp định dạng CSV UTF-8 phục vụ ban giám đốc"
category: "Trung tâm Điều hành & Giám sát"
summary: "Hướng dẫn Ban Giám Đốc và Quản lý trích xuất dữ liệu kinh doanh, doanh thu đối soát, phễu chuyển đổi và hiệu suất mẫu thiệp dưới định dạng CSV UTF-8 BOM tương thích 100% với Microsoft Excel."
reader: "PER-ADM-03 (Ban Giám Đốc / Kế Toán Trưởng)"
order: 4
updated_at: "2026-11-15"
tags: [admin, csv-export, financial-report, utf8-bom, audit-compliance]
mapped_page: "ADM-DASH-PAGE"
mapped_route: "admin.dashboard"
risk_level: "HIGH"
required_role: "manager"
---

# Xuất báo cáo kinh doanh tổng hợp định dạng CSV UTF-8 phục vụ ban giám đốc

> [!NOTE]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `HIGH` | **Endpoint:** `GET /admin/analytics/export` (`admin.analytics.export`)  
> **Chuẩn mã hóa:** UTF-8 kèm ký hiệu nhận diện **Byte Order Mark (`\xEF\xBB\xBF`)**, bảo đảm mở trực tiếp trên Microsoft Excel tiếng Việt không bị lỗi font hay vỡ dấu.

---

## 1. Mục đích Nghiệp vụ của Chức năng Xuất Báo cáo

Báo cáo kinh doanh định kỳ là căn cứ tối thượng để Ban Giám Đốc Mây Hẹn:
1. **Đối soát Dòng tiền Thực nhận (Cash Reconciliation):** Khớp nối giữa tổng tiền ghi nhận trên hệ thống và sao kê ngân hàng số của cổng thanh toán VietQR SePay.
2. **Đánh giá Hiệu quả Chiến dịch Tiếp thị (Marketing ROI):** Đo lường chi phí thu hút khách hàng so với doanh thu tạo ra từ các gói nâng cấp (Silver, Gold, Diamond).
3. **Phân tích Xu hướng Thị trường Cưới:** Nhận diện các mẫu thiệp cưới đang thịnh hành, tỷ lệ khách tham dự thực tế và phân bổ địa lý của các cặp đôi.
4. **Phục vụ Kiểm toán Tài chính & Báo cáo Thuế:** Xuất dữ liệu giao dịch chuẩn chỉ, minh bạch và có đầy đủ nhật ký kiểm toán.

---

## 2. Năm Phân hệ Báo cáo Chuyên sâu Có thể Trích xuất

Hệ thống hỗ trợ xuất dữ liệu theo 5 tab nghiệp vụ chuyên biệt qua tham số `tab`:

```
+---------------------------------------------------------------------------------------------------+
|  5 PHÂN HỆ DỮ LIỆU KINH DOANH TRÍCH XUẤT (EXCEL CSV)                                             |
+---------------------------------------------------------------------------------------------------+
|  1. tab=revenue     : Báo cáo Chi tiết Doanh thu & Đơn hàng đã thanh toán                         |
|  2. tab=funnel      : Báo cáo Phễu chuyển đổi (Truy cập -> Đăng ký -> Tạo thiệp -> Mua gói)       |
|  3. tab=templates   : Báo cáo Hiệu suất & Lượt nhân bản của từng Mẫu thiệp cưới                   |
|  4. tab=rsvp        : Báo cáo Sĩ số Quan khách & Tỷ lệ Xác nhận Tham dự Toàn sàn                  |
|  5. tab=operations  : Báo cáo Vận hành Kỹ thuật, Tốc độ Phản hồi & Ticket Hỗ trợ                  |
+---------------------------------------------------------------------------------------------------+
```

### Chi tiết các trường dữ liệu xuất chuẩn của Báo cáo Doanh thu (`tab=revenue`):
* `Mã Đơn`: Mã đơn hàng duy nhất (ví dụ: `MH-202611-0428`).
* `Khách Hàng`: Họ tên chủ tài khoản cô dâu/chú rể.
* `Email` & `Số Điện Thoại`: Thông tin liên hệ phục vụ CSKH.
* `Gói Dịch Vụ`: Tên gói đăng ký (`Gói Bạc`, `Gói Vàng`, `Gói Kim Cương`).
* `Số Tiền (VNĐ)`: Giá trị thanh toán thực tế (đã trừ mã giảm giá nếu có).
* `Phương Thức`: Cổng thanh toán (mặc định `VietQR Auto-match / SePay`).
* `Ngày Thanh Toán`: Thời điểm khớp lệnh chính xác đến từng giây (`YYYY-MM-DD HH:mm:ss`).
* `Trạng Thái`: `paid` (Thành công).

---

## 3. Lựa chọn Khoảng thời gian Đối soát Linh hoạt

Hệ thống hỗ trợ 5 bộ lọc thời gian tiêu chuẩn và tùy biến qua hàm `parseDateRange`:

```
+---------------------------------------------------------------------------------------------------+
|  BỘ LỌC THỜI GIAN ĐỐI SOÁT TRÊN THANH CÔNG CỤ                                                     |
+---------------------------------------------------------------------------------------------------+
|  [ 7 ngày qua ]  |  [ Tháng này (11/2026) ]  |  [ Tháng trước (10/2026) ]  |  [ 90 ngày qua ]     |
|  Hoặc chọn: Từ ngày [ 01/10/2026 ] đến ngày [ 15/11/2026 ]                  [ TẢI VỀ CSV ↗ ]      |
+---------------------------------------------------------------------------------------------------+
```

* **7 ngày qua (`7_days`):** Phục vụ cuộc họp giao ban đầu tuần của Ban Điều hành.
* **Tháng này (`this_month`):** Theo dõi tiến độ doanh thu thực tế so với mục tiêu kế hoạch tháng.
* **Tháng trước (`last_month`):** Chốt số liệu kế toán và đối soát thanh toán định kỳ.
* **Tùy chỉnh (`custom`):** Cho phép chọn chính xác ngày bắt đầu (`start_date`) và ngày kết thúc (`end_date`) phục vụ các đợt thanh tra, kiểm toán bất thường.

---

## 4. Công nghệ Stream Tải về & Chống Lỗi Font Tiếng Việt

Khác với các hệ thống cũ tải toàn bộ dữ liệu vào bộ nhớ máy chủ (gây tràn RAM khi xuất hàng chục ngàn dòng), Mây Hẹn sử dụng kiến trúc **Streamed Response**:

```
[ DATABASE QUERY ] ---> [ STREAM BUFFER (FOPEN) ] ---> [ GHI THẲNG VÀO SOCKET CLIENT ]
                                |
                                +---> [ TIÊM MÃ BOM: \xEF\xBB\xBF ]
```

1. **Chống lỗi font tiếng Việt tuyệt đối:**  
   Trước khi ghi dữ liệu, hệ thống tự động tiêm chuỗi byte nhận diện **UTF-8 BOM (`\xEF\xBB\xBF`)** vào đầu luồng dữ liệu. Khi người dùng mở file `.csv` bằng Microsoft Excel, Excel sẽ tự động nhận diện bảng mã UTF-8 tiếng Việt, hiển thị hoàn hảo các nguyên âm `ă, â, đ, ê, ô, ơ, ư` mà không cần phải thực hiện thao tác Import Data phức tạp.
2. **Tên tệp tin chuẩn hóa theo thời gian thực:**  
   Tệp tin được đặt tên tự động theo cấu trúc:  
   `mayhen-report-{phân_hệ}-{năm_tháng_ngày}-{giờ_phút_giây}.csv`  
   *Ví dụ:* `mayhen-report-revenue-20261115-210530.csv`.

---

## 5. Quy trình 3 Bước Xuất Báo cáo dành cho Ban Giám Đốc

1. **Bước 1:** Truy cập **Admin Portal** -> Chọn chuyên mục **[Báo cáo & Phân tích (Analytics)]** (`/admin/analytics`) hoặc nhấp nút **[Xuất báo cáo]** tại trang Dashboard.
2. **Bước 2:** Chọn tab phân hệ dữ liệu cần xem (`Doanh thu`, `Phễu chuyển đổi`, hoặc `Kho mẫu thiệp`) và chọn khoảng thời gian cần đối soát.
3. **Bước 3:** Nhấp nút **[Xuất dữ liệu CSV ↗]** ở góc trên bên phải màn hình.
   - Trình duyệt sẽ tự động tải file về máy trong vòng 1-2 giây.
   - Mở trực tiếp bằng Microsoft Excel, Google Sheets hoặc Apple Numbers để xem báo cáo.

---

## 6. Tuân thủ Bảo mật Dữ liệu & Lưu vết Kiểm toán (Audit Trail)

Việc xuất dữ liệu kinh doanh là tác vụ có mức độ rủi ro cao (`HIGH RISK`) liên quan đến thông tin khách hàng và bí mật kinh doanh:
* **Chỉ phân quyền `Manager` trở lên** mới nhìn thấy nút xuất file CSV.
* **Tự động lưu vết kiểm toán:** Mỗi lần xuất file, service `AnalyticsReportService` lập tức tạo một bản ghi bất biến trong bảng `audit_logs`:
  ```json
  {
    "action": "ANALYTICS_REPORT_EXPORTED",
    "target_type": "AnalyticsReport",
    "description": "Xuất dữ liệu báo cáo phân hệ [revenue] từ 01/11/2026 đến 15/11/2026",
    "payload": {
      "tab": "revenue",
      "count": 342,
      "admin_id": 1,
      "admin_email": "ceo@mayhen.vn",
      "ip_address": "14.241.xxx.xxx"
    }
  }
  ```
* Ban Giám Đốc có thể kiểm tra định kỳ nhật ký này để đảm bảo dữ liệu không bị rò rỉ trái phép.
