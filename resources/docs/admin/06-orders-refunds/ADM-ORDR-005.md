---
id: ADM-ORDR-005
title: "Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Quy trình vận hành Sổ Đối Soát Tài Chính, kiểm tra nhật ký Webhook SePay, xử lý các giao dịch bất thường (chuyển thiếu/thừa tiền, sai cú pháp, trễ mạng) và xuất báo cáo đối soát ngân hàng theo quy chuẩn kế toán."
reader: "PER-ADM-02 (Kế toán & Vận hành Tài chính)"
order: 5
updated_at: "2026-11-15"
tags: [admin, doi-soat, webhook-logs, giao-dich-treo, sai-so-tien, sepay, vietinbank]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.reconciliations.index"
risk_level: "HIGH"
required_role: "finance"
---

# Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường

> [!IMPORTANT]
> **Vai trò thực hiện:** `Finance Officer (Kế toán viên)`, `Chief Accountant (Kế toán trưởng)`, hoặc `Super Admin`.  
> **Route Sổ Đối Soát:** `GET /admin/reconciliations` (`AdminFinancialReconciliationController@index`).  
> **Dịch vụ backend:** `App\Services\FinancialReconciliationService`.  
> **Các phân hệ trực quan:** 3 Tab vận hành chuyên biệt: **Phiếu Đối Soát (`reconciliations`)**, **Nhật Ký Webhook (`webhook-logs`)**, và **Hồ Sơ Hoàn Tiền (`refunds`)**.


![Giao diện quản lý Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường](/assets/client/docs/images/admin/06-orders-refunds/adm-ordr-005-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Trong quá trình thanh toán tự động qua cổng VietQR/SePay, mặc dù tỷ lệ khớp lệnh thành công đạt hơn 98%, thực tế vận hành vẫn phát sinh các **giao dịch bất thường hoặc giao dịch treo**:
* Khách hàng chuyển khoản sai số tiền (Ví dụ: Đơn hàng `399.000đ` nhưng khách chỉ chuyển `390.000đ` hoặc chuyển thừa `400.000đ`).
* Khách hàng gõ sai cú pháp định danh hoặc dùng app ngân hàng không tự điền nội dung QR.
* Mạng ngân hàng bị nghẽn (Bank Outage) khiến thông báo biến động số dư bị trễ nhiều giờ so với thời điểm khách thực chuyển.

Quy trình này hướng dẫn nhân viên Kế toán đối chiếu số liệu định kỳ giữa **Sao kê thực tế ngân hàng VietinBank/MBBank**, **Nhật ký Webhook SePay** và **Đơn hàng trên hệ thống Mây Hẹn**, bảo đảm không thất thoát doanh thu và quyền lợi khách hàng được kích hoạt kịp thời.

---

## 2. Các Phân Hệ Trên Sổ Đối Soát Tài Chính (`/admin/reconciliations`)

Giao diện Trung tâm Đối Soát được tổ chức thành 3 tab chức năng:

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│  [ TAB 1: PHIẾU ĐỐI SOÁT ]    [ TAB 2: NHẬT KÝ WEBHOOK ]    [ TAB 3: HOÀN TIỀN ] │
└──────────────────────────────────────────────────────────────────────────────────┘
```

1. **Tab 1: Phiếu Đối Soát (`reconciliations`)**:
   * Quản lý các phiếu đề xuất khớp lệnh thủ công do nhân viên lập (Maker) và chờ Kế toán trưởng phê duyệt (Checker).
   * Ghi nhận loại xử lý: `manual_approval` (Khớp lệnh thủ công) hoặc `amount_mismatch_resolution` (Xử lý chênh lệch số tiền).
2. **Tab 2: Nhật Ký Webhook (`webhook-logs`)**:
   * Lưu trữ toàn bộ các gói tin Webhook thô mà cổng SePay bắn sang máy chủ Mây Hẹn.
   * Hiển thị chi tiết: Thời gian nhận, Địa chỉ IP nguồn, Cổng thanh toán (`SePay`), Trạng thái (`success`, `error`, `ignored`), Số tiền, Mã giao dịch ngân hàng và Nội dung phản hồi.
3. **Tab 3: Hồ Sơ Hoàn Tiền (`refunds`)**:
   * Theo dõi danh sách các khoản tiền cần hoàn trả lại cho khách hàng kèm theo tiến độ chuyển khoản chi trả.

---

## 3. Quy trình Xử lý 4 Tình huống Bất thường Thường gặp

### Tình huống 1: Khách hàng chuyển THIẾU tiền so với đơn hàng
* **Triệu chứng:** Cổng SePay bắn webhook nhưng hệ thống trả về trạng thái `error` với ghi chú: *"Số tiền chuyển khoản (350.000đ) nhỏ hơn giá trị đơn hàng (399.000đ)"*. Đơn hàng vẫn ở trạng thái `pending`.
* **Cách xử lý:**
  1. Vào Tab **Nhật ký Webhook** (`tab=webhook-logs`), sao chép Mã giao dịch SePay (`provider_transaction_id`) và số tiền thực nhận.
  2. Nhân viên CSKH liên hệ với cặp đôi thông báo việc chuyển thiếu và gửi link thanh toán bổ sung phần chênh lệch (`49.000đ`).
  3. Khi khách hàng chuyển nốt phần còn thiếu: Kế toán vào Tab **Phiếu Đối Soát** bấm **[ ➕ Tạo Phiếu Khớp Lệnh ]**:
     * Chọn loại: `amount_mismatch_resolution`.
     * Nhập số tiền gộp đủ `399.000đ`.
     * Nhập mã tham chiếu cả 2 lần chuyển khoản vào trường ghi chú đối soát.
  4. Trình Kế toán trưởng phê duyệt để kích hoạt thiệp cưới.

### Tình huống 2: Khách hàng chuyển THỪA tiền
* **Triệu chứng:** Khách hàng chuyển `500.000đ` cho đơn hàng `399.000đ`.
* **Cách xử lý:**
  1. Webhook vẫn ghi nhận và tự động kích hoạt gói dịch vụ cho khách hàng để không làm gián đoạn việc gửi thiệp cưới.
  2. Hệ thống gắn cờ cảnh báo chênh lệch số dư (`amount_mismatch`).
  3. Kế toán lập phiếu hoàn trả phần tiền thừa (`101.000đ`) theo đúng số tài khoản khách đã chuyển tới và chuyển sang Tab Hoàn Tiền để giải ngân.

### Tình huống 3: Khách hàng chuyển ĐÚNG TIỀN nhưng SAI CÚ PHÁP nội dung
* **Triệu chứng:** Tiền đã vào tài khoản ngân hàng công ty nhưng SePay không tìm thấy mã đơn hàng `ORD-...` trong nội dung chuyển tiền (ví dụ khách ghi: `Minh Duc mung cuoi`).
* **Cách xử lý:**
  1. Mở sao kê ngân hàng VietinBank/MBBank kiểm tra thời gian và số tiền chuyển.
  2. Khách hàng cung cấp ảnh chụp màn hình biên lai chuyển tiền thành công.
  3. Kế toán vào Tab **Phiếu Đối Soát** (`/admin/reconciliations?tab=reconciliations`):
     * Nhấp **[ Tạo Phiếu Khớp Lệnh Thủ Công ]**.
     * Chọn mã đơn hàng của khách hàng trong danh sách đơn `pending`.
     * Nhập Mã giao dịch ngân hàng (`reference_code`), Tên ngân hàng và Tên chủ tài khoản chuyển.
     * Nhập lý do: `Khách chuyển đúng 399.000đ lúc 10:15 nhưng ghi sai cú pháp "Minh Duc mung cuoi"`.
  4. Bấm **[ Lưu Phiếu Đối Soát ]** -> Kế toán trưởng duyệt -> Đơn hàng chuyển sang `paid` ngay lập tức.

### Tình huống 4: Giao dịch bị treo do nghẽn mạng SePay/Ngân hàng
* **Triệu chứng:** Khách hàng đã bị trừ tiền trên app ngân hàng nhưng SePay chưa nhận được thông báo biến động số dư.
* **Cách xử lý:**
  1. Kiểm tra trạng thái máy chủ SePay trên cổng đối tác `my.sepay.vn`.
  2. Nếu SePay gặp sự cố mạng: Sử dụng chức năng **Duyệt Tạm Thời Khẩn Cấp** để không làm lỡ giờ lành gửi thiệp của cô dâu chú rể.
  3. Khi đường truyền phục hồi: Đối soát lại toàn bộ biến động số dư cuối ngày, đảm bảo số dư sao kê khớp 100% với báo cáo doanh thu.

---

## 4. Chu kỳ Đối soát Số dư Định kỳ (Hàng Ngày / Hàng Tháng)

Vào lúc **17:30 hàng ngày** và **ngày cuối cùng của tháng**, Bộ phận Tài chính thực hiện quy trình đối soát 3 bên:

1. **Bước 1 (Lấy sao kê ngân hàng)**: Xuất file sao kê biến động số dư tài khoản thụ hưởng VietinBank/MBBank của công ty.
2. **Bước 2 (Xuất báo cáo SePay)**: Tải danh sách giao dịch thành công trên cổng SePay.
3. **Bước 3 (Xuất bảng kê Mây Hẹn)**: Nhấp nút **[ 📥 Xuất Báo Cáo Đối Soát CSV ]** trên màn hình `/admin/reconciliations`.
4. **Bước 4 (So khớp 3 bên)**:
   * Tổng tiền ghi có trên Ngân hàng == Tổng tiền giao dịch SePay == Tổng doanh thu các đơn hàng `paid`.
   * Mọi chênh lệch (nếu có) phải được lập biên bản giải trình nguyên nhân (phí chuyển khoản liên ngân hàng, giao dịch đang chờ hoàn tiền).

---

## 5. Những điều Tuyệt đối Bị cấm trong Vận hành Đối soát

1. **CẤM TỰ Ý DUYỆT KHỚP LỆNH KHI TIỀN CHƯA VÀO TÀI KHOẢN NGÂN HÀNG:** Tuyệt đối không chấp nhận ảnh chụp màn hình biên lai chuyển tiền làm căn cứ duy nhất mà không đối chiếu với sao kê thực tế tài khoản công ty (đề phòng biên lai giả mạo).
2. **CẤM TỪ CHỐI PHIẾU ĐỐI SOÁT MÀ KHÔNG GHI LÝ DO:** Khi Kế toán trưởng từ chối phiếu đối soát của nhân viên, bắt buộc phải nhập lý do rõ ràng để Maker biết hướng xử lý lại với khách hàng.
3. **CẤM XÓA HOẶC LÀM SAI LỆCH NHẬT KÝ WEBHOOK:** Toàn bộ bảng `sepay_webhook_logs` là bằng chứng kỹ thuật bất biến, không được xóa để đảm bảo tính toàn vẹn khi thanh tra tài chính.

---

## 6. Bài viết liên quan
* [ADM-ORDR-001: Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay](/admin/docs/orders-refunds/ADM-ORDR-001)
* [ADM-ORDR-002: Quy trình kích hoạt gói dịch vụ thủ công khi khách chuyển khoản sai nội dung](/admin/docs/orders-refunds/ADM-ORDR-002)
* [ADM-ORDR-003: Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt](/admin/docs/orders-refunds/ADM-ORDR-003)
* [ADM-ORDR-004: Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán](/admin/docs/orders-refunds/ADM-ORDR-004)
