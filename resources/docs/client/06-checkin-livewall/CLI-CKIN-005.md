---
id: CLI-CKIN-005
title: "Cấu hình Màn hình Chào sảnh tiệc (Venue Welcome Kiosk) chạy tự động"
category: "Điểm danh Lễ tân & Màn chiếu"
summary: "Hướng dẫn thiết lập và trình chiếu Màn hình Chào sảnh tiệc (Venue Welcome Kiosk) cho TV/Tablet đón khách: Trình diễn album ảnh cưới tự động, hiệu ứng bật thẻ chào mừng cá nhân hóa khi khách quét QR, hiển thị số bàn tiệc và tính năng mô phỏng thử nghiệm trước giờ G."
reader: "PER-CLI-02 (Đội lễ tân & MC)"
order: 15
updated_at: "2026-11-15"
tags: [mayhen, điểm-danh-lễ-tân, welcome-kiosk, man-hinh-sanh, live-feed, tv-display]
mapped_page: "CLI-CKIN-PAGE"
mapped_route: "account.welcome_kiosk.index"
risk_level: "CRITICAL"
required_role: "customer"
---

# Cấu hình Màn hình Chào sảnh tiệc (Venue Welcome Kiosk) chạy tự động

> **Dành cho:** Cô dâu & Chú rể bố trí TV / Màn hình cảm ứng tại sảnh đón tiếp khách  
> **Thời gian thiết lập:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Màn Hình Chào Sảnh Cưới** (Route: `account.welcome_kiosk.index`)  
> **Đường link trình chiếu TV:** `mayhen.vn/man-hinh-sanh-cuoi/{slug}` (Route: `welcome_kiosk.show`)

Khu vực sảnh đón tiếp trước cửa phòng tiệc là nơi ghi lại những ấn tượng thị giác đầu tiên của quan khách. Thay vì đặt một khung ảnh cưới in gỗ cố định truyền thống, bạn có thể biến bất kỳ chiếc Smart TV 55 - 75 inch hoặc màn hình quảng cáo LED nào tại sảnh thành **Màn Hình Chào Đón Thông Minh (Venue Welcome Kiosk)**.

Màn hình sẽ tự động chạy trình chiếu (Slideshow) những khoảnh khắc đẹp nhất trong Album cưới. Đặc biệt, mỗi khi có khách mời được lễ tân điểm danh thành công bằng mã QR ngoài cửa, màn hình sẽ **tức thời bật thẻ chào mừng trang trọng**, xướng tên vị khách kèm số bàn tiệc trong sự trầm trồ của mọi người xung quanh.

![Màn hình chào sảnh tiệc Venue Welcome Kiosk](/assets/client/docs/images/client/06-checkin-livewall/cli-ckin-005-step-1.png)
*Màn hình Chào sảnh tiệc: Trình chiếu slideshow ảnh cưới và tự động xướng tên khách mời khi điểm danh.*

---

## 1. Cơ chế Hoạt động 2 Chế Độ (Idle Slideshow & Takeover Greeting)

```
CHẾ ĐỘ NGHỈ (IDLE MODE - KHI CHƯA CÓ KHÁCH QUÉT QR)
┌────────────────────────────────────────────────────────┐
│  ✨ CHÀO MỪNG QUÝ KHÁCH TỚI DỰ HÔN LỄ HOÀNG & ANH     │
│                                                        │
│       [ SLIDESHOW ẢNH CƯỚI ĐỘ NÉT CAO ULTRA-HD ]       │
│                                                        │
│  ⏳ Đếm ngược giờ làm lễ: 00 Giờ : 25 Phút : 18 Giây   │
│  🕊️ "Hạnh phúc không phải là đích đến, mà là hành trình"│
└────────────────────────────────────────────────────────┘
                           │
                           ▼ Khách quét mã QR thành công tại cửa
CHẾ ĐỘ CHÀO MỪNG (TAKEOVER GREETING - XUẤT HIỆN TRONG 8 GIÂY)
┌────────────────────────────────────────────────────────┐
│  🔔 *Tiếng chuông ngân vang*  [Hiệu ứng pháo hoa nhẹ]  │
│                                                        │
│             TRÂN TRỌNG CHÀO ĐÓN VỊ KHÁCH QUÝ           │
│              ✨ ANH NGUYỄN VĂN HOÀNG & BẠN GÁI ✨      │
│                                                        │
│     🏷️ VỊ TRÍ CHỖ NGỒI: BÀN SỐ 06 - BẠN ĐẠI HỌC         │
│     📍 KHU VỰC: Cánh Trái Sân Khấu Chính              │
│                                                        │
│  (Sau 8 giây tự động khôi phục về Slideshow ảnh cưới)  │
└────────────────────────────────────────────────────────┘
```

---

## 2. Hướng dẫn 4 bước cấu hình trong Studio Cặp Đôi

Truy cập **Quản Lý Thiệp** -> Chọn thiệp cưới -> Nhấp menu **Màn Hình Sảnh Cưới (Venue Kiosk)**:

### Bước 1: Kích hoạt màn hình sảnh tiệc
- Gạt công tắc **Bật Màn Hình Chào Mừng Lễ Tân** (`enabled = true`).

### Bước 2: Tùy biến thông điệp chào đón
- **Tiêu đề sảnh cưới (`kiosk_title`):** *Chào Mừng Quý Khách Tới Dự Lễ Thành Hôn Minh Hoàng & Thùy Dung*.
- **Thời gian hiển thị thẻ chào (`welcome_duration_seconds`):** Mặc định `8 giây` (có thể tinh chỉnh từ 4 đến 30 giây tùy theo mật độ khách đến).
- **Âm thanh chào đón (`sound_enabled`):** Bật chuông ngân du dương mỗi khi có khách mới vào sảnh.
- **Hiển thị số bàn (`show_table_name`):** Bật để khách vừa bước vào đã biết ngay mình ngồi bàn nào mà không cần hỏi lại lễ tân.
- **Đồng hồ đếm ngược (`show_countdown`):** Đếm ngược chính xác đến thời khắc cô dâu chú rể bước lên lễ đường.
- **Lời tựa tri ân (`welcome_quote`):** Lời chúc lãng mạn hiển thị ở chân màn hình.

### Bước 3: Chọn danh sách ảnh chiếu Slideshow
- Hệ thống tự động gom các bức ảnh đẹp nhất từ Album cưới của bạn.
- Bạn có thể tải lên thêm các ảnh hậu trường hoặc ảnh chụp cùng bạn bè để tăng tính gần gũi.

### Bước 4: Kiểm tra bằng tính năng Mô Phỏng Thử Nghiệm (Simulate Test)
- Nhấp nút **[🧪 Thử Nghiệm Mô Phỏng Chào Mừng]**.
- Hệ thống gửi lệnh giả lập qua API `account.welcome_kiosk.simulate`. Bạn sẽ thấy thẻ chào đón xuất hiện ngay trên khung xem trước, giúp kiểm tra màu sắc, font chữ và căn chỉnh bố cục trước giờ G.

---

## 3. Cách kết nối và trình chiếu lên Smart TV tại Trung tâm Tiệc cưới

1. **Chuẩn bị thiết bị:**
   - Một chiếc Smart TV (Samsung Tizen, LG webOS, Sony Android TV) có kết nối mạng Wi-Fi của nhà hàng.
   - Hoặc một chiếc laptop kết nối dây cáp HDMI / cáp không dây lên TV.
2. **Mở đường dẫn trình chiếu:**
   - Mở trình duyệt web trên TV hoặc laptop và nhập địa chỉ:
     `mayhen.vn/man-hinh-sanh-cuoi/{slug-thiep-cuoi}`
3. **Bật chế độ Toàn Màn Hình (Fullscreen):**
   - Nhấn phím `F11` (trên máy tính) hoặc nhấp vào biểu tượng **[⛶ Toàn Màn Hình]** ở góc dưới cùng bên phải.
   - Nhấp một điểm bất kỳ trên màn hình để cấp quyền tự động phát âm thanh (Audio Autoplay Policy của trình duyệt).
4. **Vận hành tự động:**
   - Màn hình sử dụng công nghệ kết nối thời gian thực `Server-Sent Events (SSE)` kết hợp cơ chế tự phục hồi (`Auto-Reconnect`). Dù sảnh cưới bị ngắt mạng tạm thời, màn hình vẫn chiếu ảnh bình thường và tự động kết nối lại ngay khi mạng ổn định.

---

## 4. Xử lý các tình huống thực tế thường gặp

### Tình huống 1: Màn hình không phát âm thanh chào mừng
- **Nguyên nhân:** Các trình duyệt hiện đại (Chrome, Safari, Edge) chặn tự động phát âm thanh nếu người dùng chưa tương tác lần đầu tiên.
- **Cách khắc phục:** Sau khi mở trang web lên TV hoặc laptop, chỉ cần dùng chuột nhấp 1 lần vào bất kỳ vị trí nào trên màn hình để mở khóa âm thanh.

### Tình huống 2: Nhiều khách cùng quét mã QR một lúc
- **Cơ chế Hàng Đợi Thông Minh (Greeting Queue):** Nếu có 3 - 4 khách quét QR liên tiếp trong cùng vài giây, hệ thống không làm gián đoạn màn hình mà xếp vào hàng đợi; từng thẻ chào đón sẽ lần lượt xuất hiện nối tiếp nhau theo thứ tự đến trước hiển thị trước.
