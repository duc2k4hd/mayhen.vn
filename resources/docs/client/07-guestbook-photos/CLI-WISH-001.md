---
id: CLI-WISH-001
title: "Sổ ký tên điện tử: Khách vẽ chữ ký chúc phúc và gửi lời chúc tốt đẹp"
category: "Sổ ký tên, Lời chúc & Photo Drop"
summary: "Hướng dẫn trải nghiệm Sổ Ký Tên Điện Tử (Interactive Guestbook & Touch Signature Canvas): Khách mời vẽ chữ ký ngón tay/bút cảm ứng mượt mà, chọn màu mực sang trọng, đóng dấu niêm phong sáp ảo (Wax Seal) và gửi lời chúc phúc lưu giữ mãi mãi."
reader: "PER-CLI-03 (Khách mời & Cặp đôi)"
order: 5
updated_at: "2026-11-15"
tags: [mayhen, sổ-ký-tên, touch-signature, loi-chuc, wax-seal, guestbook-canvas]
mapped_page: "CLI-WISH-PAGE"
mapped_route: "invitation.wish.index"
risk_level: "MEDIUM"
required_role: "customer"
---

# Sổ ký tên điện tử: Khách vẽ chữ ký chúc phúc và gửi lời chúc tốt đẹp

> **Dành cho:** Quan khách gửi lời chúc mừng và Cặp đôi quản lý sổ lưu niệm cưới  
> **Thời gian thao tác:** Khoảng 1 - 2 phút  
> **Vị trí trên thiệp cưới:** Section 10 của thiệp cưới `/thiep/{slug}#so-ky-ten`  
> **API Xử lý:** `invitation.signature.save` & `invitation.wish.store` (`POST /thiep/{slug}/wishes`)

Trong các đám cưới truyền thống, cuốn sổ ký tên bìa da đặt ở bàn lễ tân thường gặp nhiều bất tiện: Chỉ một người ký được tại một thời điểm, bút mực dễ bị tắc hoặc lem nhem, và sau ngày cưới cuốn sổ thường bị cất vào ngăn tủ phủ bụi.

**Sổ Ký Tên Điện Tử (Interactive Digital Guestbook)** của Mây Hẹn mang lại một trải nghiệm hoàn toàn mới: Dù ở xa nửa vòng trái đất hay đang ngồi tại bàn tiệc, khách mời đều có thể mở thiệp cưới trên điện thoại, tự tay **vẽ nét chữ ký mềm mại** bằng ngón tay hoặc bút cảm ứng, đính kèm lời chúc chân thành và nhận một chiếc **Tem Niêm Phong Sáp Ảo (Digital Wax Seal)** tinh xảo.

![Sổ ký tên điện tử và vẽ chữ ký chúc phúc](/assets/client/docs/images/client/07-guestbook-photos/cli-wish-001-step-1.png)
*Sổ ký tên điện tử: Trải nghiệm vẽ chữ ký cảm ứng và lưu giữ lời chúc với tem niêm phong sáp độc bản.*

---

## 1. Giao diện Vùng Ký Tên Nghệ Thuật (Signature Studio Canvas)

Nằm ở Section 10 của mọi mẫu thiệp Mây Hẹn, khung ký tên được thiết kế theo phong cách trang nhã, tối giản và ấm cúng:

```
┌────────────────────────────────────────────────────────────┐
│              💌 SỔ KÝ TÊN & GỬI LỜI CHÚC MỪNG             │
│   Hãy để lại chữ ký và những lời chúc phúc cho cặp đôi     │
├────────────────────────────────────────────────────────────┤
│  [ Ô NHẬP TÊN ]:  Nguyễn Hoàng Nam                         │
│  [ MỐI QUAN HỆ ]: Bạn Đại Học ⏷                           │
│  [ LỜI CHÚC ]:    Chúc hai bạn một đời an yên, cùng nhau   │
│                   vượt qua mọi thăng trầm và hạnh phúc!    │
├────────────────────────────────────────────────────────────┤
│  BẢNG VẼ CẢM ỨNG (TOUCH SIGNATURE CANVAS):                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │                                                      │  │
│  │               ~ Hoang Nam ~ (Nét vẽ lụa)             │  │
│  │                                                      │  │
│  └──────────────────────────────────────────────────────┘  │
│  Bảng màu mực: (● Vàng Kim)  (● Đỏ Rượu)  (● Đen Than)    │
│  Thao tác: [↩️ Hoàn tác nét]  [🗑️ Vẽ lại từ đầu]          │
├────────────────────────────────────────────────────────────┤
│      [ ✨ ĐÓNG DẤU SÁP & GỬI LỜI CHÚC VÀNG ]                │
└────────────────────────────────────────────────────────────┘
```

> [!IMPORTANT]
> **Nguyên tắc thẩm mỹ số 8 của Mây Hẹn (Zero Photo Pollution):**  
> Tuyệt đối không chèn ảnh chụp cặp đôi hay ảnh stock người vào khu vực Sổ Ký Tên. Khung vẽ sử dụng hoàn toàn hiệu ứng đồ họa thuần (CSS Canvas, hoa văn hoàng gia, ánh kim Stardust) để bảo đảm sự tôn nghiêm và biến mỗi chữ ký thành một tác phẩm nghệ thuật độc lập.

---

## 2. Các bước ký tên và gửi lời chúc trên điện thoại (Dành cho Khách)

### Bước 1: Mở phần Sổ Ký Tên trên thiệp cưới
- Khách mời cuộn xuống cuối thiệp hoặc nhấp vào biểu tượng **[Sổ Ký Tên]** trên thanh điều hướng nhanh.
- Nếu khách mở thiệp từ link cá nhân hóa (`?guest=token`), hệ thống sẽ **tự động điền sẵn Họ Tên và Xưng Hô** của khách, không cần gõ lại.

### Bước 2: Soạn lời chúc từ trái tim
- Nhập lời nhắn nhủ gửi đến cô dâu chú rể.
- Bạn có thể chọn các lời gợi ý có sẵn (*"Trăm năm tình viên mãn"*, *"Sớm sinh quý tử"*, *"Mãi yêu như ngày đầu"*...) nếu đang vội.

### Bước 3: Vẽ nét chữ ký bằng ngón tay (Touch Drawing)
1. Đặt đầu ngón tay lên khung vẽ màu kem sáng.
2. Vẽ tên hoặc chữ ký của bạn. Công nghệ nội suy đường cong Bézier của Mây Hẹn tự động làm mịn nét vẽ, tạo cảm giác êm ái như đang viết bằng bút mực máy cao cấp trên giấy mỹ thuật.
3. Tùy chọn màu mực:
   - **Mực Vàng Kim (Royal Gold - `#D4AF37`):** Sang trọng, quý phái.
   - **Mực Đỏ Rượu (Burgundy Merlot - `#613B3E`):** Nồng nàn, lãng mạn.
   - **Mực Đen Than (Charcoal Black - `#2F2A2C`):** Truyền thống, mực thước.
4. Nếu lỡ vẽ lệch, chỉ cần bấm **[↩️ Hoàn tác nét]** hoặc **[🗑️ Vẽ lại]** để làm sạch canvas trong tích tắc.

### Bước 4: Nhấp nút [Đóng Dấu Sáp & Gửi Lời Chúc]
- Một hoạt ảnh đóng dấu sáp đỏ với con dấu dập nổi Monogram tên viết tắt của cặp đôi sẽ hiện lên kèm âm thanh đóng dấu trang trọng.
- Chữ ký được đóng gói dưới dạng ảnh vector trong suốt (Transparent PNG/SVG) và lưu trữ vĩnh viễn trên hệ thống.

---

## 3. Dòng thời gian Lời chúc trực tiếp (Live Wishes Feed)

Sau khi gửi thành công:
1. Lời chúc của bạn sẽ xuất hiện trên **Dòng Lưu Bút Trực Tiếp (Wishes Stream)** ngay bên dưới sổ ký tên.
2. Khách mời khác có thể thả tim **(❤️)** tán thưởng cho những lời chúc hay và hài hước.
3. Nếu cặp đôi đang kết nối màn hình LED sân khấu (Stage Live Wall), lời chúc và chữ ký của bạn sẽ bay lên màn hình lớn trong sảnh tiệc để cả hội trường cùng chiêm ngưỡng.

---

## 4. Xử lý các sự cố thường gặp của khách mời

### Lỗi 1: Vẽ trên màn hình điện thoại nhưng không ra nét mực
- **Nguyên nhân:** Khách chạm ngón tay quá nhẹ hoặc màn hình điện thoại đang bật tính năng cử chỉ vuốt toàn màn hình (Gesture Navigation) của Android.
- **Cách khắc phục:** Đặt ngón tay dứt khoát vào giữa ô vẽ và di chuyển nhẹ; hệ thống đã tích hợp thuộc tính CSS `touch-action: none` để ngăn chặn trình duyệt cuộn trang khi đang ký.

### Lỗi 2: Nhấn gửi nhưng thông báo "Nội dung chứa từ ngữ chưa phù hợp"
- **Nguyên nhân:** Hệ thống kích hoạt Bộ lọc bảo vệ gia phong (`NoProfanity Rule`) để ngăn chặn các từ lóng tục tĩu hoặc từ ngữ tiêu cực trong ngày cưới.
- **Cách khắc phục:** Kiểm tra lại lời chúc và thay thế bằng những từ ngữ trang nhã, lịch sự.
