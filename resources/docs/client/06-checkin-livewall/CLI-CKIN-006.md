---
id: CLI-CKIN-006
title: "Thiết lập Màn hình Trình chiếu Sân khấu (Live Wall) hiển thị lời chúc và mã PIN bảo vệ"
category: "Điểm danh Lễ tân & Màn chiếu"
summary: "Hướng dẫn cài đặt và vận hành Màn hình Trình chiếu Sân khấu (Stage Live Wall): Hiển thị luồng lời chúc trực tiếp, trình chiếu ảnh cưới kết hợp Photo Drop của khách, mã QR góc màn hình kích thích tương tác, cơ chế kiểm duyệt nội dung an toàn và khóa bảo vệ bằng mã PIN."
reader: "PER-CLI-02 (Đội lễ tân & MC)"
order: 16
updated_at: "2026-11-15"
tags: [mayhen, điểm-danh-lễ-tân, live-wall, trinh-chieu-san-khau, ma-pin-bao-ve, loi-chuc-truc-tiep]
mapped_page: "CLI-CKIN-PAGE"
mapped_route: "live_wall.settings"
risk_level: "CRITICAL"
required_role: "customer"
---

# Thiết lập Màn hình Trình chiếu Sân khấu (Live Wall) hiển thị lời chúc và mã PIN bảo vệ

> **Dành cho:** Cô dâu, Chú rể và Đội ngũ kỹ thuật âm thanh ánh sáng (AV Tech) của hội trường cưới  
> **Thời gian thiết lập:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Màn Hình Chiếu Sân Khấu** (Route: `live_wall.settings`)  
> **Đường dẫn trình chiếu LED sân khấu:** `mayhen.vn/trinh-chieu/{slug}` (Route: `live_wall.show`)

Màn hình LED trung tâm sân khấu là tâm điểm ánh nhìn của toàn bộ hàng trăm quan khách trong suốt 2 tiếng tiệc cưới. Thay vì chỉ phát lặp lại một video clip ảnh tĩnh đơn điệu, **Màn Hình Trình Chiếu Trực Tiếp (Stage Live Wall)** của Mây Hẹn biến sân khấu thành một không gian tương tác bùng nổ cảm xúc.

Khách mời ngồi tại bàn tiệc chỉ cần quét mã QR ở góc màn hình LED để gửi lời chúc lãng mạn kèm chữ ký hoặc ảnh chụp kỷ niệm tại bàn. Những lời chúc ý nghĩa này sẽ **lập tức bay lên màn hình lớn** theo hiệu ứng bong bóng trang nhã, tạo nên bầu không khí ấm áp và kết nối trọn vẹn.

---

## 1. Kiến trúc Trực quan của Màn Chiếu Sân Khấu (Live Wall Layout)

Màn hình Live Wall được thiết kế đồ họa chuẩn điện ảnh theo tỷ lệ khung hình 16:9 (phù hợp mọi kích thước màn hình LED từ P3, P4 đến máy chiếu hội trường):

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                                                                                  │
│   ✨ TRÂN TRỌNG HÔN LỄ: NGUYỄN MINH HOÀNG & TRẦN THÙY DUNG ✨                    │
│                                                                                  │
│  ┌─────────────────────────┐   ┌──────────────────────────────────────────────┐  │
│  │                         │   │  💌 LỜI CHÚC YÊU THƯƠNG TỪ QUAN KHÁCH        │  │
│  │   [ SLIDESHOW ẢNH ]     │   │                                              │  │
│  │   Khoảnh khắc lãng mạn  │   │  💬 "Chúc hai bạn trăm năm hạnh phúc, mãi    │  │
│  │   cặp đôi & ảnh khách   │   │     giữ nụ cười rạng rỡ như ngày hôm nay!"   │  │
│  │   chụp gửi lên          │   │     — Chị Mai & Anh Hải (Bàn VIP 02)         │  │
│  │                         │   │                                              │  │
│  │                         │   │  💬 "Chúc mừng Hoàng đã tìm được bến đỗ      │  │
│  │                         │   │     bình yên của cuộc đời!"                  │  │
│  └─────────────────────────┘   │     — Hội Bạn Thân Đại Học                   │  │
│                                └──────────────────────────────────────────────┘  │
│                                                                                  │
│  ┌──────────┐                                                                    │
│  │ ┌──────┐ │  📱 QUÉT MÃ QR ĐỂ GỬI LỜI CHÚC & ẢNH LÊN MÀN HÌNH                  │
│  │ │  QR  │ │  Chụp ảnh kỷ niệm tại bàn và gửi chúc phúc tới cô dâu chú rể!     │
│  │ └──────┘ │                                                                    │
│  └──────────┘                                                 [⛶ Toàn màn hình]  │
└──────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Hướng dẫn cấu hình Live Wall trong Studio Cặp Đôi

Truy cập **Quản Lý Thiệp** -> Chọn thiệp -> **Màn Hình Chiếu**:

### Bước 1: Thiết lập Mã PIN bảo vệ (Security Access PIN)
- **Mục đích:** Đảm bảo chỉ có bạn hoặc kỹ thuật viên âm thanh ánh sáng của sảnh cưới mới có quyền mở màn hình chiếu, tránh trường hợp người ngoài tò mò truy cập làm lộ giao diện điều khiển.
- **Cách cài đặt:** Đặt mã PIN gồm 4 đến 6 chữ số (ví dụ: `2027` hoặc `8888`). Khi kỹ thuật viên mở đường link `/trinh-chieu/{slug}`, hệ thống sẽ yêu cầu nhập đúng mã PIN này mới cho phép vào màn hình lớn.

### Bước 2: Tùy biến phong cách hiển thị
- **Tông màu sân khấu:** Chọn phong cách *Dạ tiệc lãng mạn (Dark Velvet)* hoặc *Thanh lịch ngọc trai (Ivory Cream)* tùy theo concept trang trí ánh sáng của hội trường cưới.
- **Tốc độ trôi lời chúc:** Điều chỉnh thời gian xuất hiện mỗi lời chúc (mặc định từ `6 đến 10 giây`) để quan khách dưới bàn tiệc kịp đọc trọn vẹn thông điệp.
- **Mã QR góc màn hình (`qrTargetUrl`):** Hệ thống tự động sinh mã QR vector kích thước lớn, dẫn thẳng khách đến trang gửi lời chúc và tải ảnh Photo Drop.

### Bước 3: Cài đặt chế độ kiểm duyệt nội dung an toàn (Moderation Firewall)
Để ngăn ngừa rủi ro có người gửi nội dung trêu đùa quá đà hoặc từ ngữ không phù hợp lên màn hình lớn trước mặt phụ huynh và quan khách hai họ:
- Mây Hẹn áp dụng chính sách **Kiểm duyệt 2 lớp**:
  1. *Bộ lọc ngôn từ cấm tự động (`NoProfanity Rule`):* Tự động chặn các từ ngữ khiếm nhã ngay khi khách vừa nhấn nút gửi.
  2. *Chế độ Chỉ Chiếu Lời Chúc Đã Duyệt (`is_approved = true`):* Bạn có thể chỉ định người thân hoặc phụ dâu mở trang quản trị trên điện thoại để nhấp "Duyệt" trước khi lời chúc được phép xuất hiện trên màn hình LED.

---

## 3. Cách bàn giao cho Kỹ Thuật Viên LED Nhà Hàng (AV Tech Handover)

Trước giờ khai tiệc 45 phút, cô dâu chú rể chỉ cần gửi cho bộ phận kỹ thuật âm thanh ánh sáng hội trường cưới:

1. **Đường link trình chiếu:** `mayhen.vn/trinh-chieu/{slug-thiep-cuoi}`
2. **Mã PIN đăng nhập:** *(Ví dụ: `8888`)*
3. **Thao tác vận hành của Kỹ thuật viên:**
   - Mở trình duyệt Chrome/Edge trên máy tính bàn điều khiển LED.
   - Nhập đường link và mã PIN.
   - Nhấn phím `F11` để chuyển sang chế độ Toàn Màn Hình.
   - Nhấp chuột 1 lần vào màn hình để kích hoạt âm thanh chúc mừng nhẹ nhàng.
   - Không cần chạm thêm bất kỳ nút nào trong suốt buổi tiệc: Hệ thống sử dụng kết nối ngầm `liveFeed` tự động cập nhật lời chúc và hình ảnh mới sau mỗi 8 giây.

---

## 4. Xử lý các sự cố kỹ thuật thường gặp tại sân khấu

### Sự cố 1: Màn hình LED bị mất kết nối mạng giữa buổi tiệc
- **Giải pháp kiên cố:** Hệ thống Live Wall của Mây Hẹn có bộ đệm tuần hoàn (Circular Buffer). Khi mất kết nối máy chủ, màn hình sẽ tự động chạy lặp lại các lời chúc và ảnh cưới đã tải về trước đó mà không bao giờ bị hiện lỗi màn hình đen hoặc thông báo ngắt kết nối. Ngay khi có mạng lại, hệ thống sẽ âm thầm tải tiếp lời chúc mới.

### Sự cố 2: Lời chúc bị trôi quá nhanh hoặc chữ quá nhỏ
- **Giải pháp:** Sử dụng phím tắt nhanh trên bàn phím:
  - Nhấn phím `[+]` hoặc `[-]`: Để tăng / giảm kích cỡ chữ của lời chúc hiển thị trên màn LED.
  - Nhấn phím `Space (Phím Cách)`: Để tạm dừng (Pause) luồng trôi lời chúc khi MC muốn đọc to một lời chúc đặc biệt của khách VIP. Nhấn `Space` lần nữa để màn hình tiếp tục chạy tự động.
