---
id: CLI-GIFT-001
title: "Cài đặt thông tin tài khoản ngân hàng chú rể và cô dâu hiển thị mã VietQR chuẩn"
category: "Sổ Mừng cưới VietQR & Dự toán"
summary: "Hướng dẫn cài đặt tài khoản ngân hàng nhận mừng cưới (VietQR Setup): Cấu hình tài khoản độc lập cho Chú Rể và Cô Dâu, cơ chế xác thực mật khẩu bảo vệ chống chiếm đoạt tài khoản (ATO Defense), bật/tắt hiển thị mã QR công khai trên thiệp và định dạng NAPAS 24/7."
reader: "PER-CLI-02 / PER-CLI-03"
order: 1
updated_at: "2026-11-15"
tags: [mayhen, sổ-mừng-cưới, vietqr, banking-setup, password-confirmation, ato-defense]
mapped_page: "CLI-GIFT-PAGE"
mapped_route: "account.gifts.update_banking"
risk_level: "HIGH"
required_role: "customer"
---

# Cài đặt thông tin tài khoản ngân hàng chú rể và cô dâu hiển thị mã VietQR chuẩn

> **Dành cho:** Cô dâu & Chú rể cấu hình tài khoản nhận tiền mừng cưới  
> **Thời gian thao tác:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sổ Mừng Cưới** -> **Cài Đặt Tài Khoản Ngân Hàng** (Route: `account.gifts.index`)  
> **API Cốt lõi:** `account.gifts.update_banking` (`POST /tai-khoan/thiep/{id}/so-mung-cuoi/tai-khoan-ngan-hang`)

Trong các đám cưới thời đại số, mừng cưới không dùng tiền mặt (Cashless Wedding) qua mã VietQR đã trở thành xu hướng phổ biến được cả người trẻ lẫn khách trung niên ưa chuộng. Khách mời ở xa không thể tham dự trực tiếp hoặc khách đến dự tiệc nhưng không kịp rút tiền mặt tại cây ATM đều có thể quét mã chuyển khoản trong vòng vài giây.

Để đảm bảo dòng tiền mừng về đúng tài khoản và tuyệt đối an toàn, Mây Hẹn cung cấp hệ thống **Cấu hình Đa Tài Khoản Ngân Hàng (Multi-Account VietQR)** với lớp bảo mật xác thực mật khẩu cấp cao chống chiếm đoạt tài khoản.

---

## 1. Cơ chế Hiển thị Độc Lập cho Nhà Trai và Nhà Gái

Thay vì gộp chung một số tài khoản gây khó xử khi kiểm đếm hoặc phân chia tiền mừng sau đám cưới, Mây Hẹn hỗ trợ hiển thị song song hai tab tài khoản:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   🎁 HỘP MỪNG CƯỚI CHÚC PHÚC                           │
│  Gửi trao tình cảm và những lời chúc tốt đẹp nhất tới cô dâu chú rể    │
├────────────────────────────────────────────────────────────────────────┤
│     [ TAB 1: MỪNG CHÚ RỂ ]               [ TAB 2: MỪNG CÔ DÂU ]        │
│                                                                        │
│   ┌─────────────────────┐              ┌─────────────────────┐         │
│   │ ┌─────────────────┐ │              │ ┌─────────────────┐ │         │
│   │ │  VIETQR CHUẨN   │ │              │ │  VIETQR CHUẨN   │ │         │
│   │ │    NAPAS 24/7   │ │              │ │    NAPAS 24/7   │ │         │
│   │ └─────────────────┘ │              │ └─────────────────┘ │         │
│   │ Ngân hàng: VCB      │              │ Ngân hàng: TCB      │         │
│   │ STK: 001100456789   │              │ STK: 190345678910   │         │
│   │ Chủ TK: NGUYEN M. H │              │ Chủ TK: TRAN T. D   │         │
│   │ [📋 Sao chép STK]   │              │ [📋 Sao chép STK]   │         │
│   └─────────────────────┘              └─────────────────────┘         │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Khách của chú rể (Bạn bè, đồng nghiệp nhà trai):** Mở tab Chú Rể, quét mã chuyển thẳng vào tài khoản chú rể.
2. **Khách của cô dâu (Họ hàng ngoại, bạn bè nhà gái):** Mở tab Cô Dâu, chuyển thẳng vào tài khoản cô dâu.
3. **Nội dung chuyển khoản chuẩn hóa:** Tự động sinh cú pháp chuyển khoản ý nghĩa: `Mung cuoi Hoang Dung - [Ten Khach]`.

---

## 2. Quy trình 4 bước cấu hình tài khoản ngân hàng

Truy cập **Quản Lý Thiệp** -> Chọn thiệp cưới -> Nhấp mục **Sổ Mừng Cưới** -> Cuộn đến phần **Thiết Lập Tài Khoản Nhận Mừng**:

### Bước 1: Nhập thông tin tài khoản Chú Rể & Cô Dâu
Bấm **[+ Thêm Tài Khoản Ngân Hàng]** và điền chính xác:
- **Nhãn hiển thị (`label`):** *Tài khoản Chú Rể (Nguyễn Minh Hoàng)* hoặc *Tài khoản Cô Dâu (Trần Thùy Dung)*.
- **Tên ngân hàng (`bank_name`):** Chọn trong danh mục hơn 50 ngân hàng thương mại Việt Nam (Vietcombank, MBBank, Techcombank, ACB, BIDV, VPBank, TPBank...).
- **Số tài khoản (`bank_account_number`):** Nhập chính xác dãy số tài khoản ngân hàng của bạn.
- **Tên chủ tài khoản (`account_holder`):** Nhập tên in hoa không dấu đúng theo thẻ ngân hàng (ví dụ: `NGUYEN MINH HOANG`).

### Bước 2: Tải ảnh mã QR cá nhân hóa (Tùy chọn)
- Nếu bạn có ảnh mã QR kèm logo ngân hàng hoặc khung ảnh cưới đã được thiết kế riêng, bạn có thể tải ảnh lên ở mục **Ảnh mã QR**.
- Nếu không tải ảnh, hệ thống Mây Hẹn sẽ tự động sinh mã **VietQR chuẩn quốc gia (NAPAS 24/7)** sắc nét và tương thích với 100% ứng dụng ngân hàng di động (Mobile Banking) tại Việt Nam.

### Bước 3: Xác thực mật khẩu bảo mật (Password Re-entry Defense)
> [!IMPORTANT]
> **Lớp phòng vệ chống chiếm đoạt tài khoản (ATO Firewall):**  
> Vì số tài khoản ngân hàng là thông tin tài chính cực kỳ nhạy cảm, Mây Hẹn áp dụng quy chuẩn bảo mật bắt buộc: Bạn phải nhập lại **Mật khẩu tài khoản đăng nhập hiện tại (`current_password`)** trước khi lưu. Quy tắc này đảm bảo kẻ gian không thể tự ý sửa đổi số tài khoản nhận tiền mừng cưới dù có mượn máy tính của bạn khi bạn quên chưa đăng xuất.

### Bước 4: Nhấp nút [Lưu Cấu Hình Ngân Hàng]
Hệ thống xác thực mật khẩu, ghi nhận vào nhật ký kiểm toán hệ thống (`GIFT_BANKING_UPDATED`) và lập tức cập nhật lên thiệp cưới trực tuyến.

---

## 3. Tính năng Bật / Tắt hiển thị mã QR công khai (1-Click Visibility Toggle)

Nhiều gia đình truyền thống có quan niệm không muốn công khai số tài khoản trên thiệp gửi cho các bậc tiền bối lớn tuổi, nhưng lại muốn bật lên cho nhóm bạn bè trẻ:

- Nhấp nút **[🔒 Bật / Tắt hiển thị QR Mừng Cưới]** (`account.gifts.toggle_qr`).
- **Khi Tắt:** Section Mừng Cưới trên thiệp công cộng sẽ hoàn toàn ẩn đi hoặc chỉ hiển thị hộp thư chúc mừng mà không có số tài khoản.
- **Khi Bật:** Thẻ mã VietQR sẽ hiển thị nổi bật kèm hiệu ứng lấp lánh nhẹ nhàng.

---

## 4. Xử lý các sự cố thường gặp

### Lỗi 1: Khách quét mã nhưng app ngân hàng báo "Tài khoản không hợp lệ"
- **Nguyên nhân:** Gõ nhầm số tài khoản hoặc chọn sai tên ngân hàng (ví dụ Vietcombank bị nhầm sang VietinBank).
- **Cách khắc phục:** Dùng app ngân hàng của chính bạn quét thử mã QR trên thiệp sau khi lưu. Chuyển thử `10,000đ` để kiểm tra tên người thụ hưởng hiển thị có trùng khớp với tên bạn không trước khi gửi thiệp cho khách.

### Lỗi 2: Nhập sai mật khẩu khi lưu
- **Cách khắc phục:** Đảm bảo nhập đúng mật khẩu bạn đã dùng để đăng nhập vào trang quản trị Mây Hẹn. Nếu quên mật khẩu, hãy dùng chức năng Quên mật khẩu ở trang Đăng nhập để thiết lập lại trước.
