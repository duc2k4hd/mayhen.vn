---
id: ADM-ORDR-004
title: "Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Quy trình lập, thẩm định thông tin mã số thuế doanh nghiệp, phát hành hóa đơn điện tử VAT (e-Invoice) và lưu trữ chứng từ kế toán số theo định dạng UTF-8 BOM cho các đơn hàng dịch vụ trên Mây Hẹn."
reader: "PER-ADM-02 (Kế toán & Vận hành Tài chính)"
order: 4
updated_at: "2026-11-15"
tags: [admin, hoa-don-vat, e-invoice, ma-so-thue, ke-toan, chung-tu, excel-bom]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.invoices.index"
risk_level: "MEDIUM"
required_role: "finance"
---

# Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán

> [!IMPORTANT]
> **Vai trò thực hiện:** `Finance Officer (Kế toán viên)`, `Chief Accountant`, hoặc `Super Admin`.  
> **Route quản lý hóa đơn:** `GET /admin/invoices` (`AdminInvoiceController@index`).  
> **Route tạo hóa đơn:** `GET /admin/invoices/create` & `POST /admin/invoices` (`@store`).  
> **Route in ấn khổ A4:** `GET /admin/invoices/{id}/print` (`@print`).  
> **Route xuất báo cáo kế toán:** `GET /admin/invoices/export` (`@exportCsv`).  
> **Dịch vụ backend:** `App\Services\InvoiceManagementService`.


![Giao diện quản lý Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Nhiều cặp đôi (hoặc các đơn vị tài trợ tiệc cưới, công ty tổ chức sự kiện, Studio áo cưới) khi thanh toán các gói dịch vụ cao cấp trên Mây Hẹn (Gold, Diamond, Màn chiếu Live Wall) có nhu cầu **xuất hóa đơn giá trị gia tăng (VAT) hợp lệ** để quyết toán chi phí doanh nghiệp.

Quy trình này hướng dẫn nhân viên Kế toán:
1. Tiếp nhận và kiểm tra thông tin xuất hóa đơn (Tên công ty, Mã số thuế, Địa chỉ trụ sở, Email nhận hóa đơn).
2. Lập chứng từ và phát hành hóa đơn điện tử với mã số định danh tuần tự chuẩn `MH-INV-YYYYMM-XXXX`.
3. In hóa đơn định dạng chuẩn A4 hoặc tự động gửi email hóa đơn điện tử tới khách hàng.
4. Xuất bảng kê chứng từ phục vụ thanh tra thuế và lưu trữ kế toán theo quy định pháp luật Việt Nam.

---

## 2. Cấu trúc Số Hóa Đơn & Quy chuẩn Thuế VAT

* **Mã số hóa đơn tự động sinh (`generateInvoiceNumber`)**:  
  Định dạng: `MH-INV-{Năm}{Tháng}-{Số thứ tự 4 chữ số}`  
  *Ví dụ: `MH-INV-202611-0001`, `MH-INV-202611-0002`...*  
  Mã số được đánh liên tục, không ngắt quãng theo từng kỳ tính thuế.
* **Thuế suất GTGT (VAT Rate)**:
  * Áp dụng thuế suất dịch vụ phần mềm / dịch vụ số theo quy định hiện hành (8% hoặc 10%).
  * Công thức tính toán chuẩn:
    $$\text{VAT Amount} = \text{Subtotal} \times \frac{\text{VAT Rate}}{100}$$
    $$\text{Total Amount} = \text{Subtotal} + \text{VAT Amount}$$

---

## 3. Vòng đời Trạng thái Hóa đơn (Invoice Statuses)

| Trạng thái | Nhãn hiển thị | Màu sắc | Ý nghĩa nghiệp vụ |
| :--- | :--- | :--- | :--- |
| `pending` | **Chờ phát hành** | Vàng cam (`#F57F17`) | Mới lập nháp thông tin công ty, đang chờ kế toán trưởng kiểm tra MST. |
| `issued` | **Đã phát hành** | Xanh lục (`#2E7D32`) | Đã cấp số hóa đơn chính thức, ghi nhận doanh thu tính thuế và gửi email cho khách. |
| `cancelled` | **Đã hủy bỏ** | Đỏ bầm (`#B71C1C`) | Khách hàng hủy dịch vụ hoặc sai sót thông tin doanh nghiệp, có biên bản hủy kèm theo. |

---

## 4. Hướng dẫn từng bước: Lập và Phát hành Hóa đơn VAT

### Bước 1: Tra cứu đơn hàng cần xuất hóa đơn
1. Đăng nhập Admin Portal với tài khoản `finance`.
2. Vào **Hóa đơn & Chứng từ** (`/admin/invoices`).
3. Nhấp nút **[ ➕ Lập Hóa Đơn Mới ]** (`/admin/invoices/create`).
4. Chọn đơn hàng cần xuất trong danh sách các đơn đã thanh toán thành công (`status = paid`) nhưng chưa từng lập hóa đơn (`whereDoesntHave('invoice')`).

### Bước 2: Nhập thông tin doanh nghiệp thụ hưởng
1. Điền đầy đủ các trường thông tin bắt buộc:
   * **Tên cá nhân / Đơn vị mua hàng (`customer_name`)**: Tên cô dâu/chú rể hoặc người đại diện.
   * **Tên Công ty / Tổ chức (`company_name`)**: Đúng theo Giấy phép đăng ký kinh doanh (ví dụ: `CÔNG TY TNHH TRUYỀN THÔNG & SỰ KIỆN ÁNH SAO`).
   * **Mã số thuế (`tax_id`)**: Nhập đúng 10 hoặc 13 chữ số (ví dụ: `0108899882`). Hệ thống kiểm tra định dạng MST hợp lệ.
   * **Địa chỉ trụ sở (`company_address`)**: Địa chỉ xuất hóa đơn ghi trên cổng thông tin Đăng ký doanh nghiệp quốc gia.
   * **Email nhận hóa đơn điện tử (`customer_email`)**: Email của kế toán công ty khách hàng.
2. Kiểm tra phần tính toán tài chính:
   * **Tiền hàng trước thuế (`subtotal`)**: Lấy từ giá trị gói dịch vụ của đơn hàng.
   * **Thuế suất VAT (`vat_rate`)**: Chọn `8%` hoặc `10%`.
   * **Tổng tiền thanh toán (`total_amount`)**: Tự động tính toán hiển thị song song bằng chữ và số.

### Bước 3: Phát hành và Gửi hóa đơn
* **Cách 1: Lưu nháp (`Lưu Tạm Thời`)**: Dành cho trường hợp chờ khách bổ sung giấy phép kinh doanh. Hóa đơn ở trạng thái `pending`.
* **Cách 2: Phát hành ngay (`Tạo & Phát Hành Chính Thức`)**:
  1. Nhấp **[ Phát Hành Hóa Đơn ]**.
  2. Hệ thống thực thi `InvoiceManagementService@issueInvoice`:
     * Cấp số hóa đơn mới nhất `MH-INV-YYYYMM-XXXX`.
     * Chuyển trạng thái sang `issued` và ghi nhận mốc `issued_at = now()`.
     * Tự động gửi email thông báo kèm hóa đơn điện tử tới `customer_email` của khách hàng.
     * Lưu nhật ký `INVOICE_ISSUED` vào `audit_logs`.

### Bước 4: In hóa đơn hoặc Xuất file PDF/A4
1. Tại trang chi tiết hóa đơn `/admin/invoices/{id}`, nhấp nút **[ 🖨️ In Hóa Đơn ]** (`/admin/invoices/{id}/print`).
2. Giao diện xem trước in ấn mở ra với bố cục trang nhã chuẩn Quiet Luxury của Mây Hẹn:
   * Header công ty Mây Hẹn: Tên pháp nhân, MST công ty, địa chỉ, số hotline.
   * Thông tin khách hàng, bảng kê chi tiết dịch vụ gói thiệp cưới.
   * Chữ ký số và con dấu điện tử hợp lệ.
3. Nhấn `Ctrl + P` (hoặc `Cmd + P` trên macOS) để in ra máy in văn phòng hoặc chọn **Lưu dưới dạng PDF (Save as PDF)** gửi cho đối tác.

---

## 5. Quy trình Hủy bỏ Hóa đơn đã phát hành (Invoice Cancellation)

Nếu khách hàng yêu cầu hoàn tiền hoặc phát hiện sai lệch Mã số thuế sau khi hóa đơn đã phát hành:

1. Vào chi tiết hóa đơn `/admin/invoices/{id}`.
2. Nhấp nút **[ ❌ Hủy Hóa Đơn ]**.
3. Hộp thoại yêu cầu nhập **Lý do hủy hóa đơn** (bắt buộc, tối thiểu 6 ký tự, ví dụ: `Khách hàng cung cấp sai Mã số thuế công ty theo biên bản điều chỉnh #BB-01`).
4. Nhấp **[ Xác nhận Hủy ]**.
5. Hệ thống chuyển trạng thái sang `cancelled`, ghi chú lý do vào trường `notes` và lưu vết kiểm toán `INVOICE_CANCELLED`.
6. Nếu khách hàng cần xuất lại hóa đơn mới đúng thông tin, quay lại Bước 1 để tạo hóa đơn thay thế.

---

## 6. Xuất Báo Cáo Kế Toán Định Kỳ (CSV UTF-8 BOM)

Hàng tháng hoặc cuối mỗi quý tài chính, kế toán Mây Hẹn cần tổng hợp bảng kê hóa đơn:
1. Tại `/admin/invoices`, nhấp nút **[ 📥 Xuất Báo Cáo CSV ]**.
2. Hệ thống tạo file tải về `mayhen_invoices_YYYYMMDD_HHMMSS.csv` được gắn sẵn tiền tố **UTF-8 BOM (`EF BB BF`)**.
3. Kế toán mở trực tiếp trên Microsoft Excel mà không bao giờ bị lỗi font Tiếng Việt:
   * Bảng kê đầy đủ: Số hóa đơn, Mã đơn hàng, Tên công ty, MST, Doanh số trước thuế, Thuế VAT, Tổng tiền, Ngày phát hành và Trạng thái.

---

## 7. Những điều Tuyệt đối Bị cấm trong Kế toán Hóa đơn

1. **CẤM XUẤT HÓA ĐƠN CHO ĐƠN HÀNG CHƯA THANH TOÁN:** Chỉ đơn hàng đã có tiền thực nhận (`status = paid`) mới được phép tạo hóa đơn.
2. **CẤM SỬA NỘI DUNG HÓA ĐƠN ĐÃ PHÁT HÀNH:** Hóa đơn đã ở trạng thái `issued` là bất biến. Mọi sai sót phải thực hiện quy trình Hủy bỏ (`cancelled`) và lập hóa đơn mới, tuyệt đối không sửa trực tiếp vào cơ sở dữ liệu.
3. **CẤM TẠO SỐ HÓA ĐƠN TRÙNG LẶP:** Luôn sử dụng hàm sinh số tự động của hệ thống, không tự ý chèn tay số hóa đơn để tránh vi phạm quy định quản lý hóa đơn của Tổng cục Thuế.

---

## 8. Bài viết liên quan
* [ADM-ORDR-001: Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay](file:///resources/docs/admin/06-orders-refunds/ADM-ORDR-001.md)
* [ADM-ORDR-002: Quy trình kích hoạt gói dịch vụ thủ công khi khách chuyển khoản sai nội dung](file:///resources/docs/admin/06-orders-refunds/ADM-ORDR-002.md)
* [ADM-ORDR-003: Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt](file:///resources/docs/admin/06-orders-refunds/ADM-ORDR-003.md)
* [ADM-ORDR-005: Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường](file:///resources/docs/admin/06-orders-refunds/ADM-ORDR-005.md)
