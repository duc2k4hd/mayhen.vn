---
id: ADM-PART-004
title: "Phê duyệt lệnh rút tiền hoa hồng: Kiểm tra mã chuẩn chi ngân hàng trước khi chuyển trạng thái Settled"
category: "Quản trị Đối tác Studio & Hoa hồng"
summary: "Quy trình kiểm tra số dư ví đối tác, thẩm định thông tin tài khoản ngân hàng thụ hưởng, thực hiện lệnh chuyển khoản và đối soát mã chuẩn chi ngân hàng (Bank Reference Code) trước khi chốt quyết toán hoa hồng (P0)."
reader: "PER-ADM-02 / PER-ADM-03 (Kế toán & Vận hành Tài chính)"
order: 4
updated_at: "2026-11-15"
tags: [admin, rut-tien-hoa-hong, quyet-toan-partner, ma-chuan-chi, bank-reference-code, audit-log, p0]
mapped_page: "ADM-PART-PAGE"
mapped_route: "admin.partners.settle"
risk_level: "CRITICAL"
required_role: "finance"
---

# Phê duyệt lệnh rút tiền hoa hồng: Kiểm tra mã chuẩn chi ngân hàng trước khi chuyển trạng thái Settled

> [!CAUTION]
> **Vai trò thực hiện:** `Finance Officer (Kế toán chi)`, `Chief Accountant`, hoặc `Super Admin`.  
> **Route quyết toán hoa hồng:** `POST /admin/partners/commissions/{id}/settle` (`PartnerController@settle`).  
> **Dịch vụ backend:** `App\Services\PartnerManagementService::settleCommission`.  
> **Tiêu chuẩn an toàn tài chính P0:** Tuyệt đối không được chuyển trạng thái sang `paid` (Settled) nếu chưa có mã giao dịch chuẩn chi chuyển khoản ngân hàng thực tế (`bank_reference_code` tối thiểu 4 ký tự).  
> **Cơ chế chống Race Condition:** Khóa dòng dữ liệu mức database (`lockForUpdate()`) và trừ số dư ví trong giao dịch an toàn `DB::transaction`.

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Khi đối tác Studio cưới tích lũy được hoa hồng trên Partner Portal (đạt ngưỡng tối thiểu rút tiền quy định: `500.000đ`), đối tác sẽ tạo lệnh yêu cầu rút tiền về tài khoản ngân hàng đã đăng ký.

Yêu cầu này được gửi đến Admin Portal dưới dạng bản ghi hoa hồng loại `payout` ở trạng thái chờ xử lý (`pending`). Nhân viên Kế toán bắt buộc phải tuân thủ nghiêm ngặt quy trình **Kiểm tra 3 bước - Chuyển khoản thật - Đối soát mã chuẩn chi** nhằm:
1. Đảm bảo tiền hoa hồng được chuyển chính xác tới tài khoản thụ hưởng hợp pháp của Studio.
2. Trừ đúng số dư ví của đối tác (`wallet_balance`), ngăn ngừa hiện tượng rút trùng lặp (Double-Withdrawal).
3. Lưu trữ mã chuẩn chi ngân hàng (FT / Ref Code) làm bằng chứng quyết toán kế toán và chứng từ kiểm toán thuế.

---

## 2. Quy trình Vận hành Quyết toán Hoa hồng Chuẩn P0

```
[ ĐỐI TÁC STUDIO ] ──( Tạo Lệnh Rút Tiền )──> [ SỔ HOA HỒNG ADMIN ]
                                                        │ (Trạng thái: pending)
                                                        ▼
                                         [ BƯỚC 1: KIỂM TRA SỐ DƯ & STK ]
                                         - Số dư ví >= Số tiền yêu cầu rút
                                         - STK chính chủ Studio / Đại diện
                                                        │
                                                        ▼
                                         [ BƯỚC 2: CHUYỂN KHOẢN NGÂN HÀNG ]
                                         - Kế toán chuyển tiền từ VietinBank công ty
                                         - Lấy Mã Chuẩn Chi (FT code / Ref code)
                                                        │
                                                        ▼
                                         [ BƯỚC 3: QUYẾT TOÁN TRÊN ADMIN ]
                                         - Nhập Mã Chuẩn Chi vào Form Settle
                                         - Hệ thống lockForUpdate() & trừ số dư ví
                                         - Chuyển trạng thái sang 'paid'
                                         - Lưu vết Audit Log: PARTNER_COMMISSION_SETTLED
```

---

## 3. Điều kiện tiên quyết & Thẩm quyền phê duyệt

1. **Ngưỡng rút tiền tối thiểu**: Lệnh rút tối thiểu từ **500.000 VNĐ** trở lên.
2. **Số dư ví khả dụng (`wallet_balance`)**: Phải lớn hơn hoặc bằng số tiền yêu cầu rút tại thời điểm bấm duyệt.
3. **Tài khoản ngân hàng đối tác đã xác minh**: Tên chủ tài khoản ngân hàng thụ hưởng phải trùng khớp với tên người đại diện pháp luật hoặc chủ Studio đăng ký trên hợp đồng hợp tác.
4. **Phân quyền thực hiện**: Chỉ tài khoản có quyền `finance` (Kế toán) hoặc `super_admin` mới có quyền truy cập nút Quyết toán (`settle`).

---

## 4. Hướng dẫn từng bước: Phê duyệt và Quyết toán Hoa hồng

### Bước 1: Tiếp nhận yêu cầu rút tiền
1. Đăng nhập Admin Portal tại `/admin`.
2. Vào **Mạng Lưới Đối Tác** -> chọn **Sổ Hoa Hồng & Quyết Toán** (`/admin/partners/commissions`).
3. Sử dụng bộ lọc:
   * **Loại giao dịch (`type`)**: Chọn `payout` (Yêu cầu rút tiền).
   * **Trạng thái (`status`)**: Chọn `pending` (Chờ xử lý).
4. Màn hình hiển thị danh sách các lệnh rút tiền kèm theo tên Studio, số tiền và thông tin tài khoản ngân hàng nhận tiền.

### Bước 2: Thẩm định thông tin & Thực hiện chuyển khoản
1. Nhấp vào mã giao dịch để xem chi tiết:
   * **Số tiền rút**: Ví dụ `2.500.000 VNĐ`.
   * **Thông tin thụ hưởng**: Ngân hàng (ví dụ: `Vietcombank`), Số tài khoản (ví dụ: `0071000889988`), Tên chủ tài khoản (ví dụ: `STUDIO ANH CUOI MINH HOANG`).
   * **Số dư ví hiện tại của đối tác**: Kiểm tra đối tác có đang bị tranh chấp hoặc phát sinh khiếu nại hoàn tiền nào không.
2. Kế toán sử dụng tài khoản Internet Banking của Công ty Mây Hẹn (VietinBank Ateliler) thực hiện lệnh chuyển khoản:
   * Chuyển chính xác số tiền `2.500.000 VNĐ` tới số tài khoản trên.
   * Nội dung chuyển khoản chuẩn hóa: `MAYHEN THANH TOAN HOA HONG PARTNER {PARTNER_CODE} GIAO DICH #{ID}`.
3. Khi giao dịch báo thành công trên app ngân hàng: Sao chép lại **Mã giao dịch / Mã chuẩn chi ngân hàng** (ví dụ: `FT2611158912903`).

### Bước 3: Xác nhận Quyết toán trên Admin Portal
1. Trên dòng giao dịch hoa hồng, nhấp nút màu xanh **[ 🏦 Quyết Toán / Chi Trả ]**.
2. Hộp thoại quyết toán mở ra với 2 trường bắt buộc:
   * **Mã chuẩn chi ngân hàng (`bank_reference_code`)**: Dán mã giao dịch ngân hàng đã sao chép ở Bước 2 (tối thiểu 4 ký tự, ví dụ: `FT2611158912903`).
   * **Ghi chú đối soát (`reconciliation_note`)**: Nhập ghi chú đối chiếu (ví dụ: `Đã chuyển khoản thành công từ VietinBank công ty lúc 16:15 ngày 15/11`).
3. Nhấp **[ Xác Nhận Quyết Toán ]**.

### Bước 4: Kiểm chứng hệ thống tự động
Sau khi nhấn xác nhận, hệ thống xử lý qua `PartnerManagementService@settleCommission`:
* Khóa dòng dữ liệu chống Race Condition (`lockForUpdate()`).
* Trừ số dư ví đối tác: `$lockedPartner->decrement('wallet_balance', $payoutAmount)`.
* Cập nhật bản ghi hoa hồng:
  * `status = 'paid'`.
  * `payout_reference_code = 'FT2611158912903'`.
  * `reconciled_at = now()`.
  * `reconciled_by = {Admin_ID}`.
* Ghi log kiểm toán `PARTNER_COMMISSION_SETTLED` vào `audit_logs`.
* Trên Partner Portal của Studio, trạng thái lệnh rút tiền lập tức chuyển sang huy hiệu màu xanh: **Đã thanh toán (Settled)** kèm mã chứng từ để đối tác theo dõi.

---

## 5. Xử lý các tình huống bất thường

### Tình huống 1: Chuyển khoản ngân hàng bị lỗi (Sai số tài khoản, tài khoản khóa)
* **Xử lý:**
  1. Tuyệt đối **KHÔNG** bấm nút Quyết toán trên Admin.
  2. Để nguyên trạng thái `pending`.
  3. Liên hệ với đại diện Studio yêu cầu cập nhật lại số tài khoản ngân hàng chính xác.
  4. Sau khi đối tác cập nhật xong, tiến hành chuyển khoản lại từ đầu.

### Tình huống 2: Đối tác bấm rút tiền nhiều lần liên tiếp
* **Xử lý:** Cơ chế khóa `lockForUpdate()` và kiểm tra số dư ví thời gian thực tại `PartnerManagementService:202` sẽ tự động chặn đứng các lệnh trùng lặp nếu số dư ví không đủ, ngăn ngừa tuyệt đối nguy cơ chi vượt số dư.

---

## 6. Những điều Tuyệt đối Bị cấm trong Quyết toán Hoa hồng

1. **NGHIÊM CẤM ĐIỀN MÃ CHUẨN CHI GIẢ HOẶC CHỮ CÁI BẤT KỲ ĐỂ "DỌN DẸP LIST":** Nhập các chuỗi như `test`, `1234`, `done`, `ok` mà không có giao dịch ngân hàng thật là hành vi gian lận tài chính nghiêm trọng và bị kỷ luật sa thải ngay lập tức.
2. **CẤM DUYỆT RÚT TIỀN CHO TÀI KHOẢN ĐỐI TÁC ĐANG BỊ ĐÌNH CHỈ (`suspended`):** Đối tác đang bị đình chỉ do vi phạm chính sách bắt buộc phải được giải quyết xong khiếu nại trước khi mở lại quyền rút tiền.
3. **CẤM CHUYỂN KHOẢN VÀO TÀI KHOẢN KHÁC VỚI THÔNG TIN ĐÃ ĐĂNG KÝ TRÊN HỆ THỐNG:** Nếu đối tác muốn đổi số tài khoản nhận tiền, đối tác phải cập nhật trên Partner Portal và có xác nhận bằng văn bản/email từ chủ Studio.

---

## 7. Bài viết liên quan
* [ADM-PART-001: Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản](file:///resources/docs/admin/09-partners-commissions/ADM-PART-001.md)
* [ADM-PART-002: Quản lý cấu hình mức chiết khấu hoa hồng theo từng cấp độ đối tác (Tier)](file:///resources/docs/admin/09-partners-commissions/ADM-PART-002.md)
* [ADM-PART-003: Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác](file:///resources/docs/admin/09-partners-commissions/ADM-PART-003.md)
* [CLI-PART-001: Studio nhiếp ảnh liên kết và quản lý nhiều thiệp cưới khách hàng](file:///resources/docs/client/10-partner-privacy/CLI-PART-001.md)
