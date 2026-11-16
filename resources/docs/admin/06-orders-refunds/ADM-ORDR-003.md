---
id: ADM-ORDR-003
title: "Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Quy trình chuẩn hóa 3 cấp (Maker-Checker) để tiếp nhận yêu cầu, thẩm định điều kiện chính sách, chuyển khoản chi trả và thu hồi quyền lợi gói thiệp cưới sau khi hoàn tiền trên Mây Hẹn."
reader: "PER-ADM-02 (Kế toán & Vận hành Tài chính)"
order: 3
updated_at: "2026-11-15"
tags: [admin, hoan-tien, refund-workflow, maker-checker, doi-soat, thu-hoi-quyen-loi]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.orders.requestRefund"
risk_level: "CRITICAL"
required_role: "finance"
---

# Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt

> [!CAUTION]
> **Vai trò thực hiện:** `Customer Care Specialist (Maker)`, `Finance Officer (Thẩm định & Chi trả)`, và `Operation Manager / Super Admin (Checker)`.  
> **Route tạo đề xuất hoàn tiền:** `POST /admin/orders/{id}/request-refund` (`OrderController@requestRefund`).  
> **Route xác nhận chi trả & thu hồi quyền lợi:** `POST /admin/orders/{id}/confirm-refund-payout` (`OrderController@confirmRefundPayout`).  
> **Dịch vụ backend:** `App\Services\EntitlementStateMachineService::applyRefundedOrder`.  
> **Nguyên tắc an toàn:** Không bao giờ hoàn tiền mặt hoặc chuyển khoản tùy tiện; bắt buộc có mã giao dịch chuyển khoản thực tế (`payout_reference_code`) từ ngân hàng công ty để khép hồ sơ kiểm toán.


![Giao diện quản lý Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Điều kiện hoàn tiền theo Chính sách Mây Hẹn

Để bảo đảm trải nghiệm khách hàng cao cấp nhưng đồng thời ngăn ngừa tình trạng trục lợi dịch vụ (sử dụng xong đám cưới rồi yêu cầu hoàn tiền), Mây Hẹn áp dụng **Chính sách Hoàn tiền Minh bạch** (Mục 15.3 Điều khoản Dịch vụ):

### Điều kiện được chấp thuận hoàn tiền:
1. **Trong vòng 7 ngày kể từ khi thanh toán**: Cặp đôi chưa xuất bản thiệp cưới ra công chúng hoặc thiệp chưa phát sinh lượng truy cập thực tế từ khách mời.
2. **Sự cố kỹ thuật khách quan**: Lỗi hệ thống không thể khắc phục dẫn đến việc thiệp không mở được trong ngày cưới và đội ngũ kỹ thuật Mây Hẹn xác nhận lỗi thuộc về hạ tầng nền tảng.
3. **Thanh toán trùng lặp**: Khách hàng quét mã VietQR 2 lần cho cùng 1 đơn hàng, hoặc hệ thống trừ tiền thừa.

### Các trường hợp từ chối hoàn tiền:
* Đám cưới đã diễn ra hoặc thiệp đã gửi cho trên 10 khách mời và đã phát sinh lượt RSVP.
* Khách hàng đổi ý vì lý do cá nhân sau khi đã xuất bản và sử dụng toàn bộ tính năng cao cấp quá 7 ngày.

---

## 2. Mô hình Quy trình Hoàn tiền 3 cấp (Maker - Checker Workflow)

```
[ BƯỚC 1: TIẾP NHẬN & ĐỀ XUẤT ]
  Customer Support / Maker
  - Thu thập thông tin STK khách
  - Tạo hồ sơ Request Refund trên Admin
  - Trạng thái: refund_pending
                │
                ▼
[ BƯỚC 2: THẨM ĐỊNH & CHUYỂN KHOẢN ]
  Finance Officer / Kế toán
  - Đối soát lịch sử thanh toán & điều kiện hoàn
  - Thực hiện lệnh chuyển khoản qua Internet Banking
  - Lấy mã giao dịch chuyển tiền (FT / Ref Code)
                │
                ▼
[ BƯỚC 3: XÁC NHẬN CHI TRẢ & THU HỒI DỊCH VỤ ]
  Operation Manager / Super Admin
  - Nhập mã giao dịch chuyển khoản hoàn tiền
  - Chuyển trạng thái Order sang 'refunded'
  - Kích hoạt EntitlementStateMachineService thu hồi quyền lợi gói thiệp
  - Lưu vết toàn bộ Audit Logs
```

---

## 3. Hướng dẫn từng bước thao tác trên Admin Portal

### Cấp 1: Tiếp nhận và Lập hồ sơ Đề xuất Hoàn tiền (Maker)
1. Truy cập chi tiết đơn hàng cần hoàn tiền tại `/admin/orders/{id}`.
2. Kiểm tra trạng thái đơn: Bắt buộc đơn phải ở trạng thái `paid` (Đã thanh toán) và chưa có yêu cầu hoàn tiền nào khác đang chờ xử lý (`!$order->isRefundPending()`).
3. Nhấp nút **[ 💸 Tạo Đề Xuất Hoàn Tiền ]** ở thanh tác vụ.
4. Điền đầy đủ biểu mẫu đề xuất:
   * **Số tiền hoàn (`refund_amount`)**: Mặc định bằng 100% giá trị đơn hàng. Nếu hoàn một phần (khấu trừ phí xử lý), nhập số tiền thực tế (tối thiểu `1.000đ`, không vượt quá giá trị đơn hàng).
   * **Tên ngân hàng thụ hưởng (`customer_bank_name`)**: Ví dụ `Vietcombank`, `Techcombank`.
   * **Số tài khoản nhận lại tiền (`customer_bank_account`)**: Số tài khoản chính chủ của người đặt hàng.
   * **Tên chủ tài khoản (`customer_bank_holder`)**: Bắt buộc viết hoa không dấu (ví dụ: `NGUYEN VAN AN`).
   * **Lý do hoàn tiền (`reason`)**: Diễn giải cụ thể căn cứ hoàn tiền (tối thiểu 6 ký tự).
5. Nhấp **[ Gửi Đề Xuất Hoàn Tiền ]**. Hệ thống tạo bản ghi `OrderReconciliation` với mã `REQ-REF-{order_code}` ở trạng thái `pending`.

---

### Cấp 2: Thẩm định & Thực hiện Lệnh Chuyển tiền (Finance Officer)
1. Kế toán truy cập danh sách đơn chờ xử lý tại `/admin/orders?status=paid` với cờ `reconciliation_status = refund_pending`.
2. Mở chi tiết đơn hàng, đối soát lại:
   * Biên lai thu tiền gốc ban đầu từ SePay/VietinBank.
   * Lịch sử sử dụng thiệp cưới: Kiểm tra xem thiệp đã kích hoạt tính năng hay chưa.
3. Sử dụng tài khoản ngân hàng của Công ty Mây Hẹn (VietinBank Ateliler) thực hiện lệnh chuyển khoản hoàn tiền qua Internet Banking tới đúng số tài khoản và tên chủ tài khoản đã nêu trong đề xuất.
4. Sao chép lại **Mã giao dịch chuyển tiền thành công** từ sao kê ngân hàng (ví dụ: `FT2611158899201`).

---

### Cấp 3: Xác nhận Chi trả & Tự động Thu hồi Quyền lợi (Checker)
1. Tại trang chi tiết đơn hàng, tìm khối **Hồ Sơ Đề Nghị Hoàn Tiền**.
2. Nhấp nút **[ ✅ Xác Nhận Đã Chi Trả Hoàn Tiền ]**.
3. Điền thông tin chuyển khoản thực tế:
   * **Mã giao dịch chi trả (`payout_reference_code`)**: Dán mã giao dịch ngân hàng đã sao chép ở Cấp 2 (bắt buộc).
   * **Ghi chú đối soát (`payout_note`)**: Ví dụ: `Đã chuyển khoản hoàn 100% từ tài khoản VietinBank công ty lúc 15:30`.
4. Nhấp **[ Xác Nhận Hoàn Tất ]**.
5. Hệ thống kích hoạt chuỗi tác vụ tự động:
   * Cập nhật `orders.status = refunded` và `orders.refunded_at = now()`.
   * Cập nhật bản ghi đối soát sang `completed`.
   * Gọi `EntitlementStateMachineService::applyRefundedOrder`:
     * Hạ cấp gói dịch vụ thiệp cưới về gói cơ bản hoặc thu hồi các tính năng trả phí (Màn chiếu Live Wall, Két ký ức, Custom Domain).
     * Điều chỉnh lại thời hạn lưu trữ `expires_at` về mốc tiêu chuẩn.
   * Ghi log kiểm toán `refund_order` lưu vết đầy đủ trong `audit_logs`.

---

## 4. Kiểm tra Kết quả & Bằng chứng Đối soát

Sau khi hoàn tất, nhân viên có thể xác minh kết quả tại các khu vực:
* **Tại trang đơn hàng `/admin/orders/{id}`**: Huy hiệu trạng thái chuyển sang màu xám `Đã hoàn tiền (Refunded)`, hiển thị rõ số tiền đã hoàn, thời gian hoàn và mã giao dịch ngân hàng.
* **Tại hồ sơ thiệp cưới `/admin/invitations/{id}`**: Khối quyền lợi hiển thị trạng thái đã thu hồi quyền nâng cao.
* **Tại báo cáo doanh thu CSV**: Doanh thu ròng tự động trừ đi khoản tiền đã hoàn, đảm bảo số liệu kế toán không bị sai lệch.

---

## 5. Những điều Tuyệt đối Bị cấm trong Quy trình Hoàn tiền

1. **CẤM HOÀN TIỀN VÀO TÀI KHOẢN NGƯỜI THỨ BA:** Bắt buộc số tài khoản nhận tiền phải trùng tên với khách hàng đã đăng ký hoặc thanh toán đơn hàng ban đầu, tuyệt đối không chuyển khoản sang tài khoản lạ để phòng chống rửa tiền và lừa đảo.
2. **CẤM DUYỆT HOÀN TIỀN KHỐNG:** Không được xác nhận chi trả khi chưa có lệnh chuyển khoản thành công từ tài khoản ngân hàng của Mây Hẹn.
3. **CẤM TỰ Ý DUYỆT ĐƠN DO CHÍNH MÌNH TẠO NẾU LÀ MAKER:** Trừ Super Admin có thẩm quyền tối cao, nhân viên lập đề xuất (Maker) không được tự phê duyệt lệnh chi trả cho chính hồ sơ mình tạo ra (nguyên tắc 4 mắt - Four Eyes Principle).

---

## 6. Bài viết liên quan
* [ADM-ORDR-001: Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay](/admin/docs/orders-refunds/ADM-ORDR-001)
* [ADM-ORDR-002: Quy trình kích hoạt gói dịch vụ thủ công khi khách chuyển khoản sai nội dung](/admin/docs/orders-refunds/ADM-ORDR-002)
* [ADM-ORDR-004: Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán](/admin/docs/orders-refunds/ADM-ORDR-004)
* [ADM-ORDR-005: Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường](/admin/docs/orders-refunds/ADM-ORDR-005)
