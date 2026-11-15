---
id: CLI-GIFT-003
title: "Quản lý sổ mừng cưới: Ghi chép tiền mừng thực tế và đối soát minh bạch"
category: "Sổ Mừng cưới VietQR & Dự toán"
summary: "Hướng dẫn quản lý Sổ Mừng Cưới số (Digital Wedding Gift Ledger): Ghi chép phong bì tiền mặt và chuyển khoản VietQR sau tiệc, thống kê tài chính tự động (Tổng thu, trung bình/khách, tiền mặt vs chuyển khoản), quản lý trạng thái gửi lời cảm ơn và xuất file Excel/In ấn đối soát minh bạch."
reader: "PER-CLI-02 / PER-CLI-03"
order: 12
updated_at: "2026-11-15"
tags: [mayhen, sổ-mừng-cưới, doi-soat-tien-mung, ghi-chep-phong-bi, thong-ke-tai-chinh, xuat-excel]
mapped_page: "CLI-GIFT-PAGE"
mapped_route: "gifts.index"
risk_level: "HIGH"
required_role: "customer"
---

# Quản lý sổ mừng cưới: Ghi chép tiền mừng thực tế và đối soát minh bạch

> **Dành cho:** Cô dâu, Chú rể và Gia đình kiểm đếm mừng cưới sau ngày vui  
> **Thời gian thực hiện:** Khoảng 15 - 30 phút sau khi tàn tiệc  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sổ Mừng Cưới** (Route: `account.gifts.index`)  
> **API Cốt lõi:** `account.gifts.store`, `account.gifts.update`, `account.gifts.export` & `account.gifts.print`

Sau khi tiệc cưới kết thúc, giây phút hồi hộp và ấm áp nhất của hai vợ chồng là cùng nhau ngồi bóc từng chiếc phong bì mừng cưới. Việc ghi chép sổ tay bằng bút truyền thống rất dễ xảy ra nhầm lẫn, mất thời gian cộng nhẩm máy tính và khó tra cứu lại sau này khi cần "đi trả nợ miệng" đám cưới của bạn bè.

**Sổ Mừng Cưới Thông Minh (Gift Ledger)** của Mây Hẹn biến công việc kiểm đếm thành một hệ thống kế toán gia đình khoa học: Tự động tổng hợp tiền mừng chuyển khoản VietQR, hỗ trợ ghi nhanh phong bì tiền mặt, phân loại theo hai họ và tính toán chi tiết mức mừng trung bình.

---

## 1. Bảng Chỉ Số Tài Chính Hôn Lễ Toàn Diện

Ngay trên đầu trang Sổ Mừng Cưới, hệ thống trực quan hóa 6 chỉ số cốt lõi:

```
┌────────────────────────────────────────────────────────────────────────┐
│  📊 TỔNG KẾT TÀI CHÍNH MỪNG CƯỚI                                       │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│   TỔNG TIỀN MỪNG    │     SỐ LƯỢT MỪNG   │     MỨC MỪNG TRUNG BÌNH     │
│   285,500,000 đ     │      312 Lượt      │        915,000 đ / Khách    │
├─────────────────────┼────────────────────┼─────────────────────────────┤
│ 💵 TIỀN MẶT (PHONG BÌ)│ 💳 CHUYỂN KHOẢN QR│ 🎁 HIỆN VẬT / KHÁC          │
│   195,000,000 đ     │    88,500,000 đ    │    2,000,000 đ (Chỉ vàng)   │
│   (68.3% tổng thu)  │    (31.0% tổng thu)│    (0.7% tổng thu)          │
├─────────────────────┴────────────────────┴─────────────────────────────┤
│  Trạng thái tri ân: 245 / 312 Khách đã gửi lời cảm ơn (78.5%)          │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Tổng tiền mừng (`totalAmount`):** Toàn bộ số tiền mừng thu về từ mọi kênh.
2. **Tiền mặt vs Chuyển khoản:** Tách bạch rõ ràng số tiền mặt trong thùng phong bì và số tiền chuyển khoản vào tài khoản ngân hàng để đối soát số dư cuối ngày.
3. **Mức mừng trung bình / khách (`avgAmount`):** Thước đo tham chiếu hữu ích cho kế hoạch dự toán tài chính.
4. **Bộ đếm Tri ân (`acknowledgedCount`):** Theo dõi số người đã được bạn nhắn tin cảm ơn.

---

## 2. Quy trình Ghi Chép Phong Bì Tiền Mặt Nhanh Chóng

Khi hai vợ chồng cùng ngồi bóc phong bì sau tiệc:

1. Nhấp nút **[+ Ghi Nhận Mừng Cưới]** (`account.gifts.store`).
2. Khung nhập liệu thông minh hiện lên:
   - **Tên người mừng:** Nhập tên khách (hoặc gõ tên để hệ thống gợi ý từ danh bạ khách mời có sẵn).
   - **Nhóm khách:** Chọn nhóm tương ứng (*Họ Nhà Trai, Họ Nhà Gái, Bạn Cấp 3, Đồng Nghiệp Công Ty...*).
   - **Hình thức mừng (`payment_method`):**
     - `Tiền mặt (Cash)`: Phong bì truyền thống.
     - `Chuyển khoản (Bank Transfer)`: Khách mừng qua VietQR.
     - `Khác (Other)`: Quà tặng hiện vật, nhẫn vàng, kiềng cưới...
   - **Số tiền mừng (`amount`):** Nhập số tiền (ví dụ: `500000`, `1000000`, `2000000`).
   - **Ghi chú (`notes`):** Lời chúc ghi trên phong bì hoặc mô tả hiện vật (*Ví dụ: 1 chỉ vàng SJC 9999*).
3. Bấm **[Lưu vào sổ]** (hoặc nhấn phím `Enter` để lưu và chuyển ngay sang phong bì tiếp theo). Tốc độ ghi chép chỉ mất khoảng **5 đến 10 giây / phong bì**.

---

## 3. Quản lý trạng thái "Đã gửi lời cảm ơn" (Acknowledgment Tracker)

Sau đám cưới vài ngày, việc gửi lời cảm ơn riêng tới những người đã mừng cưới là phép lịch sự tối quan trọng:

- Dưới mỗi dòng mừng cưới trong danh sách có công tắc **[💌 Đã cảm ơn]** (`account.gifts.toggle_ack`).
- Khi bạn nhắn tin cảm ơn khách xong, nhấp nút này để chuyển trạng thái sang màu xanh lá.
- Bạn có thể dùng bộ lọc **[Chưa cảm ơn]** để hiển thị danh sách những người còn sót lại, đảm bảo 100% người thân bạn bè đều nhận được lời tri ân chu đáo từ hai bạn.

---

## 4. In ấn Biên Bản Đối Soát & Xuất File Excel (Print & CSV)

Sau khi nhập xong, nếu cần ngồi họp gia đình với bố mẹ hai bên để đối soát tiền cỗ và tiền mừng:

### Cách 1: Xuất file Excel chuẩn hóa (Export CSV)
- Nhấp **[📥 Xuất File Excel (CSV)]** (`account.gifts.export`).
- File kết xuất sử dụng bộ mã hóa `UTF-8 with BOM` chống lỗi font tiếng Việt, sắp xếp gọn gàng theo cột: *STT, Họ tên, Nhóm khách, Hình thức, Số tiền, Ghi chú, Trạng thái cảm ơn, Ngày ghi nhận*.
- Bạn có thể lưu vào Google Drive hoặc gửi qua Zalo cho bố mẹ lưu trữ.

### Cách 2: In Trang Bảng Kê A4 (Print-ready)
- Nhấp **[🖨️ In Sổ Mừng Cưới]** (`account.gifts.print`).
- Giao diện in ấn tối ưu khổ giấy A4 dọc trắng đen thanh lịch, có sẵn phần ký tên xác nhận của Trưởng ban kiểm ngân, Bố Mẹ và Cô Dâu Chú Rể, giúp việc bàn giao tài chính gia đình minh bạch và êm ấm.

---

## 5. Mẹo giữ an toàn dữ liệu sau ngày cưới

> [!TIP]
> **Dữ liệu được lưu trữ vĩnh viễn:**  
> Dù bạn không dùng thiệp cưới online nữa, toàn bộ dữ liệu trong Sổ Mừng Cưới vẫn được bảo lưu an toàn trọn đời trong tài khoản Mây Hẹn của bạn. Sau 1 - 2 năm khi nhận được thiệp cưới của bạn bè, bạn chỉ cần mở lại Sổ Mừng Cưới gõ tên bạn mình để xem trước đây họ đi mừng mình bao nhiêu để đi mừng lại tương xứng, vô cùng tiện lợi!
