---
id: CLI-GIFT-002
title: "Hướng dẫn khách mừng cưới quét mã QR tự động điền đúng số tài khoản và lời chúc"
category: "Sổ Mừng cưới VietQR & Dự toán"
summary: "Cẩm nang hướng dẫn khách mời mừng cưới công nghệ: Quét mã VietQR bằng ứng dụng ngân hàng di động bất kỳ, tự động điền số tài khoản và nội dung chuyển khoản ý nghĩa, tính năng 1 chạm sao chép số tài khoản khi mở thiệp trên cùng điện thoại."
reader: "PER-CLI-02 / PER-CLI-03"
order: 11
updated_at: "2026-11-15"
tags: [mayhen, sổ-mừng-cưới, quet-ma-qr, app-ngan-hang, chuyen-khoan-mung-cuoi, napas-247]
mapped_page: "CLI-GIFT-PAGE"
mapped_route: "gifts.index"
risk_level: "HIGH"
required_role: "customer"
---

# Hướng dẫn khách mừng cưới quét mã QR tự động điền đúng số tài khoản và lời chúc

> **Dành cho:** Khách mời gửi quà mừng cưới và Cặp đôi hướng dẫn người thân thao tác  
> **Thời gian thao tác:** Khoảng 30 giây  
> **Vị trí trên thiệp cưới:** Section 10 của thiệp cưới `/thiep/{slug}#hop-mung-cuoi`  
> **Tương thích:** 100% ứng dụng ngân hàng di động (Vietcombank, MBBank, Techcombank, VPBank, TPBank, ACB, BIDV, Agribank, MoMo, ZaloPay...)

Gửi quà mừng cưới là nét đẹp văn hóa chúc phúc cho đôi bạn trẻ xây dựng tổ ấm mới. Trước đây khi mừng qua tài khoản ngân hàng, khách thường phải xin số tài khoản, ghi nhớ tên ngân hàng, cặm cụi gõ từng con số và lo lắng gõ nhầm một chữ số tiền sẽ bay sang tài khoản người khác.

Với **Hộp Mừng Cưới VietQR Chuẩn Hóa** của Mây Hẹn, việc gửi quà mừng trở nên nhẹ nhàng, tinh tế và chuẩn xác 100%. Mọi thông tin về ngân hàng, số tài khoản và cú pháp lời chúc đều được mã hóa tự động vào mã QR.

![Hộp mừng cưới VietQR tự động điền số tài khoản và lời chúc](/assets/client/docs/images/client/08-gift-budget/cli-gift-002-step-1.png)
*Hộp Mừng Cưới Thông Minh: Quét mã QR tự động điền đúng tên, số tài khoản và lời chúc ý nghĩa.*

---

## 1. Hai cách mừng cưới tiện lợi tùy theo thiết bị

```
TRƯỜNG HỢP 1: XEM THIỆP TRÊN MÁY TÍNH / THIỆP GIẤY IN MÃ QR
┌─────────────────────────────────┐
│     [ MÀN HÌNH MÁY TÍNH ]       │
│  Mở thiệp cưới -> Hiện mã QR    │
└─────────────────────────────────┘
                │  Dùng camera App Ngân Hàng trên điện thoại quét thẳng
                ▼
┌─────────────────────────────────┐
│     [ ĐIỆN THOẠI CỦA BẠN ]      │
│  App ngân hàng tự động điền:    │
│  - Ngân hàng: VCB               │
│  - STK: 001100456789            │
│  - Tên: NGUYEN MINH HOANG       │
│  - Nội dung: Mung cuoi Hoang Dung│
└─────────────────────────────────┘

TRƯỜNG HỢP 2: XEM THIỆP TRÊN CHÍNH CHIẾC ĐIỆN THOẠI ĐANG DÙNG
┌────────────────────────────────────────────────────────┐
│  📱 ĐANG ĐỌC THIỆP TRÊN ĐIỆN THOẠI                     │
│                                                        │
│  Cách A: Nhấp nút [📋 Sao chép số tài khoản]           │
│          ➔ Mở app ngân hàng dán vào chuyển khoản       │
│                                                        │
│  Cách B: Nhấp nút [📥 Tải ảnh mã QR về máy]            │
│          ➔ Mở app ngân hàng chọn "Quét QR từ Album ảnh"│
└────────────────────────────────────────────────────────┘
```

---

## 2. Hướng dẫn chi tiết từng bước cho Khách Mời

### Cách A: Quét mã QR trực tiếp (Khuyên dùng khi có 2 thiết bị hoặc quét từ thiệp in)
1. Mở ứng dụng ngân hàng di động (Mobile Banking) bất kỳ mà bạn thường sử dụng hàng ngày.
2. Nhấp vào biểu tượng **[Quét mã QR / QR Pay]** nằm ở vị trí trung tâm trên màn hình chính của ứng dụng ngân hàng.
3. Hướng camera về phía mã VietQR trên thiệp cưới Mây Hẹn (chọn tab Chú Rể hoặc Cô Dâu tùy theo người mời bạn).
4. **Hệ thống tự động điền:**
   - Tên ngân hàng thụ hưởng.
   - Số tài khoản người nhận.
   - Tên chủ tài khoản: *Ví dụ: NGUYEN MINH HOANG*.
   - Nội dung chuyển khoản: *Mung cuoi Hoang Dung - [Ten cua ban]*.
5. Bạn chỉ cần nhập số tiền mừng tùy ý (ví dụ: `500,000đ` hoặc `1,000,000đ`), kiểm tra lại tên chủ tài khoản và xác nhận mã PIN hoặc vân tay/FaceID để hoàn tất.

---

### Cách B: Thao tác ngay trên một chiếc điện thoại (Khi đang đọc thiệp)
Nhiều khách mời băn khoăn: *"Mã QR hiện trên màn hình điện thoại thì lấy camera đâu mà quét?"*. Mây Hẹn hỗ trợ 2 giải pháp cực kỳ đơn giản:

#### Lựa chọn 1: Sao chép số tài khoản 1 chạm (Khuyên dùng)
1. Ngay bên dưới mã QR, nhấp vào nút **[📋 Sao chép số tài khoản]**.
2. Màn hình hiện thông báo xanh: *"Đã sao chép số tài khoản thành công"*.
3. Mở app ngân hàng của bạn -> Chọn **Chuyển tiền** -> Dán số tài khoản vào ô người nhận. Tên ngân hàng và tên chủ tài khoản sẽ tự động hiện lên chính xác.

#### Lựa chọn 2: Quét ảnh từ Album ảnh điện thoại
1. Nhấp nút **[📥 Tải ảnh mã QR]** (hoặc chụp ảnh màn hình điện thoại).
2. Mở app ngân hàng -> Chọn **Quét mã QR**.
3. Nhấp vào biểu tượng **[Chọn ảnh từ Thư viện / Album]** ở góc màn hình quét.
4. Chọn bức ảnh mã QR vừa lưu. App ngân hàng sẽ tự động nhận diện và điền đầy đủ thông tin chuyển khoản.

---

## 3. Các lưu ý quan trọng để đảm bảo an toàn giao dịch

> [!IMPORTANT]
> **Kiểm tra Tên Người Thụ Hưởng trước khi bấm chuyển:**  
> Dù mã VietQR của Mây Hẹn được tạo tự động chuẩn quốc gia, trước khi nhập mã PIN thanh toán trên app ngân hàng, bạn hãy luôn kiểm tra dòng **Tên người nhận (Beneficiary Name)** có đúng là tên cô dâu hoặc chú rể không (ví dụ: `NGUYEN MINH HOANG` hoặc `TRAN THUY DUNG`).

> [!NOTE]
> **Nội dung chuyển khoản kèm tên của bạn:**  
> Đừng xóa phần tên của bạn trong nội dung chuyển khoản (ví dụ: *Chi ban co dau mung cuoi*). Điều này giúp cô dâu chú rể dễ dàng đối soát trong Sổ mừng cưới và gửi lời cảm ơn riêng tới bạn sau đám cưới.
