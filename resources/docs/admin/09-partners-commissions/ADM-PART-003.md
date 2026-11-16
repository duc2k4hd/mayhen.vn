---
id: ADM-PART-003
title: "Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác"
category: "Quản trị Đối tác Studio & Hoa hồng"
summary: "Hướng dẫn nhân viên tài chính và quản lý đối tác kiểm tra danh sách đám cưới, theo dõi doanh thu thanh toán từ thiệp cưới khách hàng và đối soát bảng kê hoa hồng mạng lưới Studio trên Mây Hẹn."
reader: "PER-ADM-02 / PER-ADM-03 (Partnership Lead & Admin)"
order: 3
updated_at: "2026-11-15"
tags: [admin, doi-soat-doanh-thu, hoa-hong-doi-tac, partner-network, wallet-liability, bao-cao-csv]
mapped_page: "ADM-PART-PAGE"
mapped_route: "admin.partners.commissions"
risk_level: "MEDIUM"
required_role: "finance"
---

# Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác

> [!IMPORTANT]
> **Vai trò thực hiện:** `Finance Officer (Kế toán đối soát)`, `Partnership Lead`, hoặc `Super Admin`.  
> **Route Sổ hoa hồng mạng lưới:** `GET /admin/partners/commissions` (`PartnerController@commissions`).  
> **Route chi tiết đám cưới đối tác:** `GET /admin/partners/{id}` (`@show`).  
> **Route xuất file đối soát:** `GET /admin/partners/commissions/export` (`@exportCommissionsCsv`).  
> **Dịch vụ backend:** `App\Services\PartnerManagementService`.


![Giao diện quản lý Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác](/assets/client/docs/images/admin/09-partners-commissions/adm-part-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Một Studio ảnh viện hoặc Wedding Planner có thể cùng lúc quản lý hàng chục đám cưới của các cặp đôi khách hàng. Khi các cặp đôi này tự nâng cấp gói dịch vụ (hoặc Studio mua gói thay cho khách), doanh thu sẽ được ghi nhận vào hệ thống và tự động trích tỷ lệ hoa hồng tương ứng vào ví đối tác.

Để bảo đảm tính minh bạch tài chính giữa Mây Hẹn và mạng lưới đối tác, kế toán định kỳ phải:
1. **Kiểm tra doanh thu thực tế**: Xác minh các đơn hàng thanh toán thành công (`paid`) bắt nguồn từ thiệp cưới của đối tác nào (`partner_id`).
2. **Theo dõi nghĩa vụ nợ hoa hồng (Wallet Liability)**: Quản lý tổng số tiền hoa hồng mà Mây Hẹn đang nợ các đối tác chưa quyết toán.
3. **Phát hiện và xử lý chênh lệch**: Đối soát các trường hợp đơn hàng bị hủy hoặc hoàn tiền để thu hồi hoa hồng đã tạm tính tương ứng.

---

## 2. Các Chỉ Số KPI Tài Chính Mạng Lưới Đối Tác

Tại trang tổng quan `/admin/partners`, hệ thống cung cấp 4 chỉ số tài chính theo thời gian thực:

* **Tổng doanh thu tạo ra (Network Revenue)**: Toàn bộ số tiền từ các đơn hàng `Order` liên kết với đám cưới thuộc quyền quản lý của đối tác (`weddings.partner_id`).
* **Tổng hoa hồng tích lũy (`total_commissions_accrued`)**: Tổng tiền hoa hồng đã ghi nhận (`approved` hoặc `paid`) sinh ra từ các đơn hàng hợp lệ.
* **Tổng hoa hồng đã chi trả (`total_commissions_paid`)**: Số tiền thực tế Mây Hẹn đã chuyển khoản thanh toán cho các đối tác qua ngân hàng.
* **Nghĩa vụ nợ ví đối tác (`total_wallet_liability`)**: Tổng số dư khả dụng hiện có trong ví của toàn bộ đối tác (`sum(wallet_balance)`), thể hiện khoản tiền công ty cần dự phòng để thanh toán khi đối tác yêu cầu rút tiền.

---

## 3. Cấu Trúc Sổ Đối Soát Hoa Hồng (`admin/partners/commissions`)

Bảng kê hoa hồng toàn mạng lưới ghi nhận từng dòng biến động số dư:

| Cột thông tin | Ý nghĩa nghiệp vụ |
| :--- | :--- |
| **Mã giao dịch (`#ID`)** | Khóa định danh của bản ghi `PartnerCommission`. |
| **Đối tác Studio** | Tên thương hiệu Studio (`business_name`) và mã đối tác (`partner_code`). |
| **Loại giao dịch (`type`)** | `commission`: Hoa hồng cộng vào ví từ đơn hàng của khách.<br>`payout`: Lệnh trừ tiền khi đối tác rút hoa hồng về tài khoản ngân hàng. |
| **Đơn hàng liên kết** | Mã đơn hàng thanh toán gốc (`order_code`) và mã thiệp cưới. |
| **Số tiền (VNĐ)** | Số tiền hoa hồng tính toán dựa theo tỷ lệ chiết khấu của đối tác tại thời điểm đó. |
| **Trạng thái (`status`)** | `pending`: Chờ qua thời gian ân hạn 7 ngày phòng trường hợp đơn bị hoàn tiền.<br>`approved` / `available`: Hoa hồng khả dụng, đã cộng vào số dư ví.<br>`paid`: Đã quyết toán chuyển khoản ngân hàng thành công. |
| **Mã chứng từ ngân hàng** | Mã giao dịch chuyển khoản thực tế (`payout_reference_code`) khi Mây Hẹn chi trả. |

---

## 4. Hướng dẫn từng bước: Đối soát Doanh thu & Hoa hồng Đối tác

### Bước 1: Tra cứu hồ sơ doanh thu từng đối tác
1. Truy cập **Mạng Lưới Đối Tác** tại `/admin/partners`.
2. Nhấp vào tên đối tác cần đối soát để mở trang **Chi tiết Đối tác** (`/admin/partners/{id}`).
3. Màn hình chia làm 2 tab con bên dưới:
   * **Tab Đám cưới đang quản lý**: Danh sách toàn bộ các đám cưới, tên cô dâu chú rể, gói dịch vụ đang dùng và ngày xuất bản.
   * **Tab Lịch sử Hoa hồng**: Chi tiết từng khoản hoa hồng nhận được từ mỗi đám cưới.

### Bước 2: Kiểm tra liên kết Đơn hàng và Thiệp cưới
1. Nhấp vào mã đơn hàng (`order_code`) để đối soát chéo với trang Quản lý Đơn hàng `/admin/orders`.
2. Kiểm tra:
   * Số tiền khách hàng đã thực chuyển qua VietQR/SePay.
   * Tỷ lệ chiết khấu của Studio tại thời điểm tạo đơn (ví dụ: Gói Diamond `1.000.000đ`, tỷ lệ 20% -> Hoa hồng nhận được: `200.000đ`).
   * Đảm bảo không có hiện tượng tính trùng hoa hồng cho cùng một đơn hàng.

### Bước 3: Rà soát các đơn hàng bị Hoàn tiền (Refund Handling)
* Nếu một đơn hàng bị hoàn tiền (`Order` chuyển sang `refunded` theo quy trình tại [ADM-ORDR-003](/admin/docs/orders-refunds/ADM-ORDR-003)):
  * Hệ thống tự động thu hồi khoản hoa hồng tương ứng.
  * Nếu hoa hồng đang ở trạng thái `pending`, hệ thống hủy bản ghi.
  * Nếu hoa hồng đã vào ví đối tác, hệ thống ghi nhận một dòng điều chỉnh giảm số dư ví (`wallet_balance`), đảm bảo Mây Hẹn không bị thất thoát tiền hoa hồng của các đơn đã hoàn trả.

---

## 5. Xuất File Báo Cáo Đối Soát Định Kỳ (CSV UTF-8 BOM)

Vào cuối mỗi tháng, kế toán xuất bảng kê phục vụ đối chiếu và lưu trữ:
1. Truy cập `/admin/partners/commissions`.
2. Sử dụng bộ lọc: Chọn đối tác cụ thể (hoặc toàn bộ) và trạng thái `approved` / `paid`.
3. Nhấp nút **[ 📥 Xuất Báo Cáo Đối Soát CSV ]**.
4. Hệ thống tải về file `mayhen_partner_commissions_YYYYMMDD_HHMMSS.csv` có gắn mã **UTF-8 BOM**.
5. Kế toán mở trực tiếp trên Excel gửi cho đại diện Studio ký biên bản đối soát doanh số tháng.

---

## 6. Những điều Tuyệt đối Bị cấm trong Đối soát Doanh thu

1. **CẤM TỰ Ý CỘNG TIỀN HOA HỒNG KHÔNG CÓ ĐƠN HÀNG GỐC:** Mọi khoản hoa hồng cộng vào ví đối tác bắt buộc phải gắn liền với một đơn hàng thanh toán thành công (`order_id`) có thật từ khách hàng.
2. **CẤM XÓA DÒNG LỊCH SỬ HOA HỒNG ĐÃ ĐỐI SOÁT:** Bảng `partner_commissions` là sổ cái kế toán số bất biến, không được xóa để đảm bảo toàn vẹn dữ liệu khi kiểm toán.
3. **CẤM CHI TRẢ HOA HỒNG KHI ĐƠN HÀNG ĐANG TRONG THỜI GIAN KHIẾU NẠI (7 NGÀY):** Hoa hồng chỉ được phép chuyển sang trạng thái khả dụng rút tiền khi đơn hàng đã vượt qua thời hạn 7 ngày bảo hành.

---

## 7. Bài viết liên quan
* [ADM-PART-001: Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản](/admin/docs/partners-commissions/ADM-PART-001)
* [ADM-PART-002: Quản lý cấu hình mức chiết khấu hoa hồng theo từng cấp độ đối tác (Tier)](/admin/docs/partners-commissions/ADM-PART-002)
* [ADM-PART-004: Phê duyệt lệnh rút tiền hoa hồng: Kiểm tra mã chuẩn chi ngân hàng](/admin/docs/partners-commissions/ADM-PART-004)
* [CLI-PART-001: Studio nhiếp ảnh liên kết và quản lý nhiều thiệp cưới khách hàng](/docs/partner-privacy/CLI-PART-001)
