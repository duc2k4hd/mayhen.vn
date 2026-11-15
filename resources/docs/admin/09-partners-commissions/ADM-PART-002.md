---
id: ADM-PART-002
title: "Quản lý cấu hình mức chiết khấu hoa hồng theo từng cấp độ đối tác (Tier)"
category: "Quản trị Đối tác Studio & Hoa hồng"
summary: "Quy chuẩn phân hạng đối tác 3 cấp bậc (Silver, Gold, Platinum), chính sách tỷ lệ chiết khấu hoa hồng (15% - 30%) và quy trình nâng/hạ hạng đối tác định kỳ trên Mây Hẹn."
reader: "PER-ADM-02 / PER-ADM-03 (Partnership Lead & Admin)"
order: 2
updated_at: "2026-11-15"
tags: [admin, hoa-hong-doi-tac, partner-tier, silver-gold-platinum, chiet-khau, audit-log]
mapped_page: "ADM-PART-PAGE"
mapped_route: "admin.partners.updateRate"
risk_level: "HIGH"
required_role: "manager"
---

# Quản lý cấu hình mức chiết khấu hoa hồng theo từng cấp độ đối tác (Tier)

> [!IMPORTANT]
> **Vai trò thực hiện:** `Partnership Manager`, `Operation Director`, hoặc `Super Admin`.  
> **Route cập nhật chính sách:** `POST /admin/partners/{id}/rate` (`PartnerController@updateRate`).  
> **Dịch vụ backend:** `App\Services\PartnerManagementService::updateCommissionRate`.  
> **Thang bậc phân hạng chuẩn:** Gồm 3 hạng chính: **Bạc (`silver`)**, **Vàng (`gold`)**, và **Bạch Kim (`platinum`)**.  
> **Giới hạn tỷ lệ hợp lệ:** Từ **0.0% đến 100.0%** (Tiêu chuẩn vận hành: 15% – 30%).

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Chính sách phân hạng đối tác (Partner Tiers) là công cụ quan trọng nhằm khuyến khích các Studio cưới và Wedding Planner tích cực giới thiệu các cặp đôi sử dụng thiệp cưới trực tuyến Mây Hẹn.

Đối tác tạo ra càng nhiều đơn hàng và đạt mốc doanh số cao trong quý sẽ được nâng cấp hạng để hưởng tỷ lệ chiết khấu hoa hồng hấp dẫn hơn, đồng thời nhận thêm các đặc quyền hỗ trợ kỹ thuật và marketing độc quyền từ Mây Hẹn.

---

## 2. Ma trận Phân Hạng Đối Tác 3 Cấp Độ (Partner Tiers Matrix)

| Hạng đối tác (`tier`) | Nhãn hiển thị | Tỷ lệ hoa hồng chuẩn | Điều kiện xét duyệt duy trì | Đặc quyền hỗ trợ kèm theo |
| :--- | :--- | :--- | :--- | :--- |
| `silver` | **Bạc (Silver)** | **15.0%** | Hạng mặc định khi kích hoạt; tối thiểu 1 thiệp cưới/tháng. | Cấp quyền Partner Portal, quản lý không giới hạn thiệp cưới của khách hàng. |
| `gold` | **Vàng (Gold)** | **20.0%** | Quản lý từ **10 đến 30 thiệp cưới/tháng**, hoặc doanh số tạo ra > 15.000.000đ/quý. | Huy hiệu Studio đối tác uy tín, hỗ trợ thiết kế logo watermark riêng, ưu tiên hỗ trợ 24/7. |
| `platinum` | **Bạch Kim (Platinum)** | **25.0% – 30.0%** | Quản lý **trên 30 thiệp cưới/tháng**, hoặc ký hợp đồng hợp tác chiến lược độc quyền. | Kênh hỗ trợ riêng với Senior Designer, hỗ trợ tài trợ chi phí sự kiện cưới hoặc quà tặng cưới VIP. |

---

## 3. Cơ chế Tính Toán Hoa Hồng Tự Động

Khi một cặp đôi tạo thiệp thông qua tài khoản đối tác Studio và hoàn tất thanh toán đơn hàng nâng cấp gói dịch vụ (`Order` có `status = 'paid'`):

1. **Công thức tính hoa hồng**:
   $$\text{Commission Amount} = \text{Order Amount} \times \frac{\text{Partner Commission Rate}}{100}$$
2. **Hệ thống tự động sinh bản ghi `PartnerCommission`**:
   * Trạng thái ban đầu: `available` (Khả dụng để rút tiền sau khi hết thời hạn hoàn tiền 7 ngày).
   * Tự động liên kết đúng `partner_id`, `order_id` và `wedding_id`.
   * Đối tác nhìn thấy số dư tích lũy ngay lập tức trên trang tổng quan `/doi-tac`.

---

## 4. Hướng dẫn từng bước: Điều chỉnh Tỷ Lệ Hoa Hồng & Hạng Đối Tác

### Bước 1: Tra cứu hồ sơ đối tác
1. Đăng nhập Admin Portal tại `/admin`.
2. Vào **Mạng Lưới Đối Tác** (`/admin/partners`).
3. Nhập tên Studio hoặc mã đối tác vào ô tìm kiếm.
4. Nhấp vào hồ sơ đối tác để xem chi tiết (`/admin/partners/{id}`).

### Bước 2: Đánh giá hiệu suất kinh doanh của đối tác
* Tại trang chi tiết, kiểm tra:
  * **Tổng số đám cưới đang quản lý**: Số lượng thiệp đã tạo.
  * **Doanh thu tích lũy tạo ra**: Tổng giá trị các đơn hàng `paid`.
  * **Tổng hoa hồng đã chi trả**: Lịch sử đối soát trước đây.
  * Đối chiếu với ma trận điều kiện ở Mục 2 để xác định hạng đối tác xứng đáng.

### Bước 3: Mở biểu mẫu Cập Nhật Chính Sách Hoa Hồng
1. Tại khối **Chính Sách & Hoa Hồng**, nhấp nút **[ ⚙️ Cập Nhật Tỷ Lệ & Hạng ]**.
2. Chọn các thông số mới:
   * **Hạng đối tác (`tier`)**: Chọn `silver`, `gold` hoặc `platinum`.
   * **Tỷ lệ hoa hồng (`commission_rate`)**: Nhập số phần trăm (ví dụ: `20.0` cho Gold hoặc `25.0` cho Platinum).
3. Nhập ghi chú thẩm định vào trường lý do (ví dụ: `Nâng hạng Gold do đạt mốc 15 đám cưới trong tháng 11/2026`).

### Bước 4: Lưu thay đổi và Lưu vết Kiểm toán
1. Nhấp **[ Xác Nhận Cập Nhật ]**.
2. Hệ thống gọi `PartnerManagementService@updateCommissionRate`:
   * Cập nhật trường `commission_rate` và `tier` trong bảng `partners`.
   * Các đơn hàng phát sinh **từ thời điểm này trở đi** sẽ tự động áp dụng tỷ lệ chiết khấu mới. Các đơn hàng cũ đã thanh toán trước đó vẫn giữ nguyên mức hoa hồng lịch sử.
   * Ghi log kiểm toán `PARTNER_RATE_UPDATED` ghi nhận rõ Admin thao tác, `old_rate`, `new_rate`, `old_tier`, và `new_tier`.
3. Kiểm tra thông báo Toast màu xanh: *"Đã cập nhật chính sách hoa hồng cho đối tác '{Tên Studio}'."*

---

## 5. Quy trình Đánh giá & Hạ hạng Định kỳ (Downgrade Policy)

Vào cuối mỗi quý tài chính (ngày 31/03, 30/06, 30/09, 31/12):
1. Bộ phận Quan hệ Đối tác (Partnership Team) rà soát lại toàn bộ mạng lưới đối tác hạng `gold` và `platinum`.
2. Nếu đối tác không phát sinh bất kỳ đám cưới mới nào trong suốt 90 ngày:
   * Gửi email cảnh báo duy trì hạng đối tác trước 15 ngày.
   * Nếu sau 15 ngày đối tác không có phản hồi: Thực hiện hạ hạng về cấp độ tương ứng với sản lượng thực tế (tối thiểu là `silver` 15%).

---

## 6. Những điều Tuyệt đối Bị cấm trong Vận hành Chính sách

1. **CẤM THAY ĐỔI TỶ LỆ HỒI TỐ (RETROACTIVE CHANGES):** Nghiêm cấm can thiệp sửa đổi tỷ lệ hoa hồng nhằm làm thay đổi số tiền hoa hồng của các giao dịch đã hoàn tất trong quá khứ. Tỷ lệ mới chỉ có hiệu lực cho các giao dịch tương lai.
2. **CẤM ĐẶT TỶ LỆ HOA HỒNG VƯỢT QUÁ 35% KHI CHƯA CÓ PHÊ DUYỆT CỦA BAN GIÁM ĐỐC:** Tỷ lệ trên 35% làm ảnh hưởng nghiêm trọng đến biên lợi nhuận của nền tảng, chỉ áp dụng cho các đối tác chuỗi toàn quốc có hợp đồng riêng.
3. **CẤM HẠ HẠNG ĐỐI TÁC MÀ KHÔNG CÓ THÔNG BÁO TRƯỚC:** Mọi quyết định hạ hạng phải có thông báo bằng văn bản hoặc email tối thiểu 7 ngày để đảm bảo tính chuyên nghiệp và tôn trọng đối tác.

---

## 7. Bài viết liên quan
* [ADM-PART-001: Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản](file:///resources/docs/admin/09-partners-commissions/ADM-PART-001.md)
* [ADM-PART-003: Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác](file:///resources/docs/admin/09-partners-commissions/ADM-PART-003.md)
* [ADM-PART-004: Phê duyệt lệnh rút tiền hoa hồng: Kiểm tra mã chuẩn chi ngân hàng](file:///resources/docs/admin/09-partners-commissions/ADM-PART-004.md)
* [CLI-PART-001: Studio nhiếp ảnh liên kết và quản lý nhiều thiệp cưới khách hàng](file:///resources/docs/client/10-partner-privacy/CLI-PART-001.md)
