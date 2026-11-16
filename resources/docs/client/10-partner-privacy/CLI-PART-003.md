---
id: CLI-PART-003
title: "Theo dõi ví hoa hồng, lịch sử thanh toán và tạo lệnh rút tiền"
category: "Đối tác Studio & Bảo mật"
summary: "Hướng dẫn đối tác Studio tra cứu doanh thu mạng lưới, quản lý ví hoa hồng tích lũy theo thời gian thực, xem lịch sử đối soát và quy trình rút tiền về tài khoản ngân hàng."
reader: "PER-CLI-04 (Studio Cưới & Đối tác)"
order: 3
updated_at: "2026-11-15"
tags: [partner, commission, wallet, payout, earnings, bank-transfer]
mapped_page: "CLI-PART-COMMISSION"
mapped_route: "partner.commissions.index"
risk_level: "MEDIUM"
required_role: "partner"
---

# Theo dõi ví hoa hồng, lịch sử thanh toán và tạo lệnh rút tiền

> [!NOTE]
> **Dành cho:** Kế toán và Chủ Studio / Đơn vị tổ chức tiệc cưới đối tác của Mây Hẹn.  
> **Trang quản lý ví hoa hồng:** `/doi-tac/hoa-hong` (Route: `partner.commissions.index`).  
> **Chu kỳ đối soát:** Ngày 05 và ngày 20 hàng tháng (đối với đối tác Bạch Kim: giải ngân nhanh trong 2 - 4 giờ làm việc).

---

## 1. Cơ Chế Tích Lũy Hoa Hồng Mạng Lưới (Network Revenue)

Khi Studio bàn giao thiệp cưới cho cặp đôi (hoặc khách hàng đăng ký thiệp sử dụng **Mã đối tác - Partner Code** của bạn), tài khoản thiệp đó sẽ được gắn vĩnh viễn với mã đối tác của Studio. 

Mọi giao dịch thanh toán phát sinh sau đó từ cặp đôi đều tự động kích hoạt hoa hồng cho Studio:
- **Nâng cấp gói dịch vụ:** Cặp đôi mua thêm gói Thời gian lưu trữ vĩnh viễn (Everlasting Vault), gói Tên miền riêng độc bản, hoặc gói Két Kỷ Niệm dung lượng 50GB.
- **Mua gói tương tác:** Tăng số lượng tin nhắn gửi thiệp Zalo ZNS hoặc mua thêm lượt tải ảnh Photo Drop tốc độ cao.
- **Tỷ lệ chiết khấu cố định:** Tính theo cấp bậc hiện tại của Studio (**15%** đối với Hạng Bạc, **20%** đối với Hạng Vàng, và **25% - 30%** đối với Hạng Bạch Kim).

![Ví hoa hồng và lịch sử thanh toán đối tác](/assets/client/docs/images/client/10-partner-privacy/cli-part-003-step-1.png)
*Ví Hoa Hồng Đối Tác: Thống kê doanh thu mạng lưới, quản lý số dư và tạo lệnh rút tiền nhanh chóng.*

---

## 2. Các Chỉ Số Tài Chính Cốt Lõi Trên Dashboard

Khi truy cập vào `/doi-tac/hoa-hong`, phần đầu trang hiển thị 4 thẻ chỉ số tài chính minh bạch:

```
┌─────────────────────────┬─────────────────────────┬─────────────────────────┬─────────────────────────┐
│     Tổng Thu Nhập       │    Đang Chờ Đối Soát    │     Đã Rút Về Ngân Hàng │     Số Dư Khả Dụng      │
│     14,500,000 VNĐ      │      1,200,000 VNĐ      │      10,000,000 VNĐ     │      3,300,000 VNĐ      │
│  (Đã duyệt & Tích lũy)  │  (Trong thời hạn 7 ngày)│   (12 đợt giải ngân)    │  [ Rút Tiền Ngay ]      │
└─────────────────────────┴─────────────────────────┴─────────────────────────┴─────────────────────────┘
```

- **Tổng Thu Nhập (Total Earned):** Tổng toàn bộ số tiền hoa hồng đã được phê duyệt hợp lệ từ trước đến nay.
- **Đang Chờ Đối Soát (Pending Approval):** Hoa hồng từ các đơn hàng mới phát sinh trong vòng 7 ngày qua (giai đoạn bảo lưu đảm bảo khách hàng không yêu cầu hoàn tiền dịch vụ).
- **Đã Rút Về Ngân Hàng (Total Paid Out):** Tổng số tiền đã được kế toán Mây Hẹn chuyển khoản thành công vào tài khoản ngân hàng của Studio.
- **Số Dư Khả Dụng (Available Balance):** Số tiền hiện có thể rút về tài khoản ngay lập tức.

---

## 3. Lịch Sử Giao Dịch & Các Loại Bản Ghi

Bảng nhật ký hoa hồng liệt kê chi tiết từng giao dịch theo định dạng thời gian thực:

1. **Loại giao dịch (`type`):**
   - `commission` (Màu xanh): Tiền hoa hồng được cộng từ một đơn hàng cụ thể của cặp đôi. Hiển thị rõ mã đơn (`order_id`), tên cô dâu chú rể và tỷ lệ chiết khấu áp dụng.
   - `payout` (Màu tím): Lệnh rút tiền hoa hồng về tài khoản ngân hàng của Studio kèm mã giao dịch chuẩn chi.
   - `refund_clawback` (Màu cam): Khấu trừ hoa hồng tương ứng trong trường hợp hy hữu khách hàng hủy dịch vụ và được hoàn tiền.
2. **Trạng thái giao dịch (`status`):**
   - `Pending`: Đang chờ hết thời hạn bảo đảm 7 ngày.
   - `Approved`: Đã qua kiểm tra tự động, tiền được cộng vào số dư khả dụng.
   - `Paid`: Đã hoàn tất chuyển khoản vào tài khoản ngân hàng của Studio.
   - `Rejected`: Lệnh rút tiền bị từ chối do sai lệch thông tin tài khoản ngân hàng.

---

## 4. Quy Trình Rút Tiền Về Tài Khoản Ngân Hàng

### Bước 1: Kiểm tra thông tin tài khoản ngân hàng thụ hưởng
- Truy cập menu **Cài đặt hồ sơ Studio** (`/doi-tac/ho-so`).
- Kiểm tra chính xác:
  - Tên ngân hàng (Vietcombank, MB Bank, Techcombank, ACB...).
  - Số tài khoản ngân hàng.
  - Họ và tên chủ tài khoản (bắt buộc viết hoa không dấu, trùng khớp với tên pháp nhân hoặc chủ sở hữu Studio).
- Nhấp **[Lưu hồ sơ]** để hệ thống ghi nhớ.

### Bước 2: Tạo yêu cầu rút tiền (Payout Request)
- Tại trang `/doi-tac/hoa-hong`, kiểm tra *Số dư khả dụng* (yêu cầu tối thiểu từ **500,000 VNĐ** trở lên).
- Nhấp nút **[Tạo lệnh rút tiền]**.
- Nhập số tiền muốn rút (mặc định có thể chọn rút toàn bộ số dư khả dụng).
- Nhấp **[Xác nhận gửi yêu cầu]**.

### Bước 3: Đối soát & Nhận tiền chuyển khoản
- Yêu cầu rút tiền được chuyển đến bộ phận Kế toán Mây Hẹn kiểm tra.
- **Thời gian xử lý:**
  - *Kỳ chi trả định kỳ:* Ngày **05** và ngày **20** hàng tháng (tiền về tài khoản trong ngày làm việc).
  - *Đối tác Bạch Kim (Platinum Tier):* Được ưu tiên kênh chi trả khẩn cấp trong vòng **2 - 4 giờ làm việc**.
- Khi chuyển khoản thành công, hệ thống gửi thông báo kèm **Mã chuẩn chi ngân hàng (FT Code)** để đối soát sổ sách kế toán.

---

## 5. Những Quy Định Quan Trọng Về Thuế & Minh Bạch Tài Chính

1. **Số dư tối thiểu:** Mức rút tiền tối thiểu cho mỗi lần tạo lệnh là **500,000 VNĐ**.
2. **Khấu trừ thuế TNCN / Thuế TNDN:** Mây Hẹn thực hiện khấu trừ và xuất chứng từ khấu trừ thuế điện tử định kỳ theo đúng quy định của Tổng cục Thuế Việt Nam đối với đối tác cá nhân và doanh nghiệp.
3. **Chống gian lận (Anti-Fraud Policy):** Hệ thống sử dụng thuật toán kiểm tra IP và thiết bị, tự động chặn các hành vi tự tạo tài khoản giả để nhận hoa hồng khống (Self-referral fraud).

---

## 6. Xử Lý Vướng Mắc Thường Gặp

| Thắc mắc | Nguyên nhân | Hướng dẫn giải quyết |
| :--- | :--- | :--- |
| **Khách hàng đã nâng cấp gói nhưng ví chưa cộng tiền** | Khách thanh toán qua chuyển khoản thủ công chậm ghi nhận, hoặc giao dịch đang trong thời gian bảo lưu 7 ngày. | Kiểm tra cột *Đang chờ đối soát*. Tiền sẽ tự động chuyển sang số dư khả dụng sau 7 ngày kể từ khi đơn hàng thanh toán thành công. |
| **Lệnh rút tiền bị từ chối (Rejected)** | Số tài khoản ngân hàng không đúng hoặc tên chủ tài khoản không trùng khớp. | Cập nhật lại số tài khoản chính xác tại `/doi-tac/ho-so` và tạo lại yêu cầu rút tiền. |
| **Cần xuất hóa đơn VAT hoa hồng cho công ty** | Studio là pháp nhân doanh nghiệp cần đối soát hợp đồng đại lý. | Gửi email yêu cầu đối soát về `ke-toan@mayhen.vn` kèm mã số thuế để nhận biên bản đối soát và hướng dẫn xuất hóa đơn điện tử. |

---

## 7. Bài viết Liên quan

- [CLI-PART-001: Hướng dẫn đăng ký trở thành Đối tác Studio Cưới Mây Hẹn](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-001.md)
- [CLI-PART-002: Tạo và bàn giao thiệp cưới cho các cặp đôi khách hàng của studio](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-002.md)
- [ADM-PART-004: Phê duyệt lệnh rút tiền hoa hồng đối tác (Dành cho Quản trị viên)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/09-partners-commissions/ADM-PART-004.md)
