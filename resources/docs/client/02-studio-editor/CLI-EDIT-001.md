---
id: CLI-EDIT-001
title: "Làm quen giao diện Studio V2: Khung soạn thảo, thanh công cụ và màn hình xem trước"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Cẩm nang hướng dẫn toàn diện về giao diện Studio Tạo Thiệp V2: Khung làm việc 3 phân vùng, thanh công cụ Topbar, Bác sĩ thiệp cưới, phím tắt hoàn tác và chế độ xem thử tương tác thời gian thực."
reader: "PER-CLI-01 / PER-CLI-02"
order: 6
updated_at: "2026-11-13"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, live-canvas, smart-sync]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Làm quen giao diện Studio V2: Khung soạn thảo, thanh công cụ và màn hình xem trước

> **Dành cho:** Cô dâu & Chú rể tự tay thiết kế và tinh chỉnh thiệp cưới  
> **Thời gian làm quen:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trình tạo thiệp `/tao-thiep` hoặc Không gian biên tập `/studio/{invitation}` (Route: `studio.workspace`)  

Studio Tạo Thiệp V2 của Mây Hẹn được thiết kế theo trường phái **Quiet Luxury & Precision Crafting** — tối giản thanh lịch, trực quan và tốc độ phản hồi tức thời. Khác với các công cụ tạo web đại trà phức tạp, Studio Mây Hẹn phân tách rõ ràng giữa việc nhập liệu nội dung và màn hình hiển thị sống động, giúp bạn an tâm tạo nên một tấm thiệp hoàn hảo mà không lo lắng về lỗi kỹ thuật.

---

## 1. Cấu trúc 3 phân vùng làm việc chính (3-Zone Studio Grid)

Giao diện Studio được chia thành 3 phân vùng chuyên biệt, tối ưu cho cả màn hình máy tính lớn và thiết bị di động:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        1. STUDIO TOPBAR V2                             │
├──────────────┬───────────────────────────┬─────────────────────────────┤
│  LEFT RAIL   │      FORM EDITOR PANEL    │      LIVE PREVIEW CANVAS    │
│ (Công Cụ Dọc)│     (Nhập Liệu & Tabs)    │  (Khung Điện Thoại / PC)    │
│              │                           │                             │
│  [Nội dung]  │ • Tên cô dâu chú rể       │ ┌─────────────────────────┐ │
│  [Bố cục]    │ • Ngày cưới & Âm lịch     │ │                         │ │
│  [Kiểu chữ]  │ • Thông tin phụ mẫu 2 nhà │ │   Thiệp cưới chuyển     │ │
│  [Âm nhạc]   │ • Địa điểm tiệc cưới      │ │   động mượt mà 60fps    │ │
│  [Mừng cưới] │ • Chỉnh điểm nhìn ảnh     │ │                         │ │
│  [Bác sĩ]    │ • Cài đặt âm nhạc/QR      │ └─────────────────────────┘ │
└──────────────┴───────────────────────────┴─────────────────────────────┘
```

### Phân vùng 1: Thanh điều khiển đỉnh cao (Studio Topbar)
- **Nút Trở về (←):** Quay lại danh mục mẫu thiệp hoặc trang quản lý thiệp cá nhân.
- **Thẻ mẫu thiệp hiện tại (Template Active Chip):** Hiển thị tên và ảnh đại diện mẫu đang dùng (ví dụ: *Hoàng Gia Cổ Điển, Mây Hoang Sơ, Bình Minh*). Bạn có thể bấm vào thẻ này bất kỳ lúc nào để đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập.
- **Pill Trạng thái Lưu nháp tự động (Autosave Live Pill):**
  - `Đang lưu...`: Hệ thống đang ghi nhận các chỉnh sửa vào bộ nhớ đệm an toàn.
  - `Đã lưu nháp`: Mọi thay đổi của bạn đã được bảo vệ tuyệt đối trên máy chủ.
  - `Đã sẵn sàng`: Trạng thái ổn định, sẵn sàng đón nhận thao tác mới.
- **Lịch sử Hoàn tác (Undo / Redo):** Phím tắt `Ctrl + Z` để hoàn tác bước vừa làm và `Ctrl + Y` để làm lại.
- **Chuyển đổi kích thước xem thử (Device Switcher):** Chuyển đổi linh hoạt giữa khung hình Điện thoại di động (Mobile 390px) và Màn hình máy tính (Desktop rộng).
- **Bác Sĩ Thiệp Cưới (Visual Quality Doctor):** Huy hiệu điểm số thời gian thực (ví dụ: `100đ`), tự động chấm điểm độ hoàn thiện và nhắc nhở các mục còn thiếu.
- **Nút Trợ Giúp (?):** Mở cẩm nang hướng dẫn sử dụng chi tiết này trong một tab riêng.
- **Nút Xuất Bản Thiệp:** Lưu bản thiệp chính thức, sẵn sàng để gửi link cho quan khách.

### Phân vùng 2: Thanh công cụ dọc & Khung nhập liệu (Left Rail & Form Panel)
Thanh công cụ dọc bên trái (Left Rail) cho phép bạn truy cập nhanh vào từng nhóm cài đặt:
1. **Nội Dung (Content):** Họ tên cô dâu chú rể, thời gian hôn lễ (Dương lịch & Âm lịch), tên phụ mẫu hai bên gia đình, địa chỉ trung tâm tiệc cưới và lời ngỏ tình yêu.
2. **Bố Cục (Sections):** Bật/tắt hiển thị hoặc kéo thả thay đổi thứ tự 10 Section tiêu chuẩn của thiệp cưới (Mở đầu, Câu chuyện tình yêu, Lịch cưới & Đếm ngược, Sự kiện & Bản đồ dẫn đường, Timeline ngày cưới, Dresscode, Album 10 ảnh, Mừng cưới, Sổ ký tên và RSVP).
3. **Phong Cách & Kiểu Chữ (Design & Typography):** Tùy chỉnh màu sắc chủ đạo, font chữ tiêu đề (Cormorant Garamond, Great Vibes, Playfair Display) và font chữ nội dung.
4. **Âm Nhạc (Music):** Chọn bản tình ca du dương làm nhạc nền cho thiệp cưới, hỗ trợ tự động phát tinh tế khi khách chạm mở thiệp.
5. **Mừng Cưới VietQR:** Cài đặt số tài khoản ngân hàng nhà trai và nhà gái, hệ thống tự động sinh mã VietQR chuẩn NAPAS kèm cú pháp mừng cưới trang nhã.
6. **Lời Ngỏ Theo Nhóm (Persona):** Tùy biến thông điệp riêng cho từng nhóm khách (Bạn bè, Gia đình, Đồng nghiệp, Khách VIP).

### Phân vùng 3: Khung xem trước trực quan (Live Canvas Preview)
- Màn hình xem trước chạy trong một khung mô phỏng điện thoại di động cao cấp với tỷ lệ màn hình chuẩn mực.
- **Công nghệ Live Sync 2 chiều (Handshake Bridge):** Mỗi khi bạn gõ một chữ cái, thay đổi ngày cưới hay chọn một bức ảnh mới bên khung nhập liệu, khung xem trước sẽ cập nhật tức thời trong vòng **16 mili-giây** mà không cần tải lại trang.
- Bạn có thể cuộn trang, bấm thử mở cánh thiệp phong thư, nghe thử đoạn nhạc nền và xem trước hiệu ứng đếm ngược đúng như trải nghiệm của khách mời.

---

## 2. Các bước thao tác chuẩn hóa trên Studio

### Bước 1: Nhập thông tin trọng yếu của ngày vui
1. Chọn tab **[Nội Dung]** trên thanh công cụ bên trái.
2. Điền chính xác họ tên Cô dâu và Chú rể.
3. Chọn ngày tổ chức hôn lễ. Hệ thống sẽ tự động đối soát và chuyển đổi sang ngày Âm lịch truyền thống Việt Nam.
4. Nhập địa chỉ tổ chức tiệc cưới và gắn link Google Maps để khách mời dễ dàng tìm đường.

### Bước 2: Tùy chỉnh ảnh cưới và Smart Focal Point
1. Cuộn đến phần Album ảnh cưới hoặc ảnh bìa chính.
2. Tải ảnh chất lượng cao từ máy tính hoặc điện thoại của bạn.
3. Sử dụng công cụ **Smart Focal Point (Điểm nhìn trọng tâm)** để chọn khuôn mặt hoặc góc chụp đẹp nhất. Dù khách mở thiệp trên màn hình dọc hay ngang, ảnh vẫn luôn được căn chỉnh hoàn hảo mà không bị cắt mất mặt.

### Bước 3: Kiểm tra với Bác Sĩ Thiệp Cưới (Visual Quality Doctor)
1. Quan sát huy hiệu điểm số ở góc trên bên phải thanh công cụ.
2. Nếu điểm số chưa đạt 100đ, hãy nhấp vào biểu tượng 🩺 **Bác Sĩ Thiệp Cưới**.
3. Bảng chẩn đoán sẽ liệt kê chi tiết các mục cần hoàn thiện (ví dụ: *Chưa nhập số tài khoản mừng cưới*, *Chưa gắn bản đồ Google Maps*, *Album ảnh còn dưới 3 tấm*).
4. Nhấp vào từng mục gợi ý để hệ thống tự động đưa bạn đến đúng ô cần điền.

### Bước 4: Xem trước trên cả hai kích thước màn hình
1. Bấm nút xem trên **Điện thoại** (Mobile) để kiểm tra bố cục ngón tay cái, cỡ chữ và tốc độ lướt thiệp.
2. Bấm nút xem trên **Máy tính** (Desktop) để đảm bảo thiệp hiển thị trang trọng khi bạn bè mở xem tại văn phòng làm việc.

### Bước 5: Xuất bản và hoàn tất
1. Nhấp nút **[Xuất Bản Thiệp]** màu vàng kim ở góc phải.
2. Hệ thống sẽ kiểm tra bảo mật lần cuối và tạo đường dẫn chính thức cho thiệp cưới của bạn (ví dụ: `mayhen.vn/thiep/tuan-lan-2027`).
3. Bạn có thể chuyển ngay sang phân hệ **Quản lý Khách mời** để lập danh sách gửi thiệp.

---

## 3. Danh sách phím tắt hữu ích trong Studio

| Phím tắt | Chức năng tương ứng |
| :--- | :--- |
| `Ctrl + Z` (hoặc `Cmd + Z` trên Mac) | Hoàn tác thao tác chỉnh sửa vừa thực hiện |
| `Ctrl + Y` (hoặc `Cmd + Shift + Z`) | Làm lại thao tác vừa hoàn tác |
| `Ctrl + S` | Kích hoạt lưu nháp ngay lập tức |
| `Esc` | Đóng các hộp thoại pop-up hoặc bảng chẩn đoán Bác Sĩ Thiệp Cưới |

---

## 4. Xử lý các tình huống thường gặp

> [!TIP]
> **Khi đang sửa mà mất kết nối mạng:**  
> Đừng lo lắng và không cần bấm F5 tải lại trang. Toàn bộ nội dung bạn vừa nhập được lưu an toàn trong bộ nhớ tạm trình duyệt (Local Cache). Khi mạng Internet ổn định trở lại, nút lưu sẽ tự động đồng bộ lên máy chủ.

> [!NOTE]
> **Đổi mẫu thiệp có bị mất thông tin đã điền không?**  
> Hoàn toàn không. Toàn bộ tên cô dâu chú rể, phụ mẫu, ngày cưới, địa chỉ tiệc, tài khoản ngân hàng và album ảnh đều dùng chung bộ khóa dữ liệu chuẩn hóa (`schema.json`). Khi bạn chuyển từ mẫu này sang mẫu khác, toàn bộ dữ liệu của bạn sẽ tự động truyền sang mẫu mới một cách mượt mà.

> [!IMPORTANT]
> **Ảnh cưới bị xoay ngang khi tải lên từ điện thoại:**  
> Trình tối ưu ảnh gốc của Mây Hẹn (`image-optimizer.js`) tự động đọc siêu dữ liệu EXIF Orientation và xoay ảnh về đúng chiều chuẩn trước khi nén sang định dạng WebP nhẹ và sắc nét. Nếu cần chỉnh lại điểm nhìn, hãy dùng công cụ Smart Focal Point.

