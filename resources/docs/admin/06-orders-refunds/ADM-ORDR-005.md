---
id: ADM-ORDR-005
title: "Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo / lệch số tiền"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Quy trình vận hành Sổ Đối Soát Tài Chính (/admin/reconciliations), kiểm tra nhật ký Webhook SePay, xử lý giao dịch chuyển thiếu/thừa tiền, sai cú pháp MH..., kết hợp nút Check SePay và Cron tự động vớt đơn lỡ webhook."
reader: "PER-ADM-02 (Kế toán & Vận hành Tài chính)"
order: 5
updated_at: "2026-11-25"
tags: [admin, doi-soat, webhook-logs, giao-dich-treo, sai-so-tien, sepay, check-sepay, cron]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.reconciliations.index"
risk_level: "HIGH"
required_role: "finance"
---

# Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo / lệch số tiền

> [!IMPORTANT]
> **Vai trò thực hiện:** `Finance Officer (Kế toán viên)`, `Chief Accountant (Kế toán trưởng)`, hoặc `Super Admin`.  
> **Route Sổ Đối Soát:** `GET /admin/reconciliations` (`AdminFinancialReconciliationController@index`).  
> **Các phân hệ trực quan:** 3 Tab vận hành: **Phiếu Đối Soát (`reconciliations`)**, **Nhật Ký Webhook (`webhook-logs`)**, và **Hồ Sơ Hoàn Tiền (`refunds`)**.  

![Giao diện Sổ Đối Soát Tài Chính & Nhật Ký Webhook SePay](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-005-step-1.png)
*Sổ Đối Soát Tài Chính (/admin/reconciliations): Theo dõi tập trung phiếu đối soát tự động/thủ công, nhật ký webhook SePay và hồ sơ hoàn tiền.*

---

## 1. Cơ chế tự động phòng ngừa giao dịch treo

Trước khi kế toán phải can thiệp thủ công, hệ thống Mây Hẹn đã có **3 lớp tự động bảo vệ**:
1. **Webhook thời gian thực (`/api/payments/sepay/webhook`)**: Tự kích hoạt gói `Plus (99.000đ)` hoặc `Pro VIP (199.000đ)` trong 5–15 giây sau khi ngân hàng ghi nhận tiền vào.
2. **Cron đối soát bù mỗi 5 phút (`mayhen:sync-plan-expirations`)**: Trước khi đóng các đơn `pending` quá hạn 30 phút, Cron tự gọi 1 lượt gộp SePay API (`reconcilePendingOrdersBatch`) để tự vớt mọi đơn bị lỡ webhook do nghẽn mạng.
3. **Nút Check SePay chủ động**: Cho phép Admin (hoặc khách hàng) kích hoạt đối soát trực tiếp với SePay API theo 5 điều kiện bất biến.

---

## 2. Quy trình xử lý 4 tình huống bất thường thực tế

### Tình huống 1: Khách hàng chuyển THIẾU tiền
- **Triệu chứng:** Đơn hàng Gói Pro (`199.000đ`) nhưng khách chuyển `150.000đ`. Hệ thống **tuyệt đối không tự kích hoạt**, ghi log `mismatch` trong Tab **Nhật Ký Webhook** và hiển thị cảnh báo thiếu tiền trên trang chi tiết đơn.
- **Cách xử lý:**
  1. Kiểm tra mã giao dịch SePay và số tiền thực nhận trong Tab **Nhật Ký Webhook**.
  2. CSKH thông báo khách chuyển bổ sung đúng phần thiếu (`49.000đ`) kèm mã đơn `MH...`.
  3. Khi tiền bổ sung đã vào sao kê, Kế toán lập phiếu đối soát `amount_mismatch_resolution` (Maker) kèm đủ 2 mã giao dịch ngân hàng và trình Super Admin (Checker) phê duyệt kích hoạt.

### Tình huống 2: Khách hàng chuyển THỪA tiền
- **Triệu chứng:** Đơn hàng Gói Plus (`99.000đ`) nhưng khách chuyển `199.000đ` hoặc `120.000đ`.
- **Quy tắc an toàn:** Hệ thống **không tự động kích hoạt khi số tiền bị lệch (kể cả thừa hay thiếu)** để tránh nhầm lẫn gói dịch vụ và lệch sổ sách kế toán; đồng thời tự gắn log `mismatch`.
- **Cách xử lý:**
  1. Liên hệ xác nhận ý muốn của khách: hoàn trả phần tiền thừa (`21.000đ`) hay đổi sang gói Pro VIP (`199.000đ`).
  2. Kế toán tạo phiếu `amount_mismatch_resolution` theo xác nhận của khách và lập lệnh hoàn phần chênh lệch (nếu có) tại Tab **Hồ Sơ Hoàn Tiền**.

### Tình huống 3: Khách chuyển ĐÚNG TIỀN nhưng quên hoặc gõ sai mã đơn `MH...`
- **Triệu chứng:** Tiền đã vào tài khoản ngân hàng nhưng nội dung chuyển khoản không chứa mã `MH...` hợp lệ. Webhook ghi trạng thái `pending_review`.
- **Cách xử lý:**
  1. Đối chiếu thời gian, số tiền và ảnh biên lai của khách với sao kê ngân hàng / SePay.
  2. Tại `/admin/orders/{id}`, sử dụng khối **Đối Soát & Duyệt Thanh Toán Thủ Công** (nhập đúng mã giao dịch ngân hàng thật + lý do đối soát) theo quy trình Maker-Checker.

### Tình huống 4: Khách chuyển muộn sau khi phiên đã hủy (`cancelled`)
- **Triệu chứng:** Đơn đã quá 30 phút hoặc khách lỡ bấm Hủy phiên trước khi ngân hàng hoàn tất giao dịch.
- **Cách xử lý:** Nếu Webhook SePay tới sau hoặc Admin bấm **[Check SePay]** tại `/admin/orders/{id}`, hệ thống **tự động phục hồi `cancelled -> paid`** và kích hoạt đúng gói `Plus`/`Pro VIP` mà không cần thao tác thủ công. *(Lưu ý: chỉ chặn tuyệt đối đơn đã `refunded`.)*
