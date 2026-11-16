---
id: CLI-SEAT-002
title: "Thao tác kéo thả khách vào bàn tiệc trực quan trên máy tính và iPad"
category: "Sơ đồ Bàn tiệc & Chỗ ngồi"
summary: "Hướng dẫn thao tác kéo thả khách mời vào bàn tiệc trực quan: Trải nghiệm mượt mà trên chuột máy tính và cảm ứng iPad, tự động tính toán số ghế đi kèm (+1), đồng bộ tọa độ tức thời và giải cứu khi xếp nhầm bàn."
reader: "PER-CLI-02 (Cặp đôi đang quản lý tiệc)"
order: 7
updated_at: "2026-11-15"
tags: [mayhen, sơ-đồ-bàn-tiệc-&-chỗ-ngồi, drag-and-drop, touch-gesture, seating-planner]
mapped_page: "CLI-SEAT-PAGE"
mapped_route: "seating.index"
risk_level: "HIGH"
required_role: "customer"
---

# Thao tác kéo thả khách vào bàn tiệc trực quan trên máy tính và iPad

> **Dành cho:** Cô dâu, Chú rể và Quản lý tiệc sắp xếp chỗ ngồi thực tế cho ngày vui  
> **Thời gian thao tác:** Khoảng 3 - 7 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sơ Đồ Xếp Bàn Tiệc** (Route: `seating.index`)  
> **API cốt lõi:** `seating.assignGuest` (Gán/gỡ khách) & `seating.updatePositions` (Lưu tọa độ bản đồ)

Xếp chỗ ngồi cho hàng trăm khách mời trong đám cưới từng là cơn ác mộng khi phải dùng giấy bút gạch xóa liên tục. **Seating Planner Canvas** của Mây Hẹn biến công việc này thành trải nghiệm trực quan sống động: Bạn chỉ cần kéo thả thẻ khách mời vào từng bàn tiệc tròn hoặc bàn dài. Hệ thống được tối ưu hóa chuẩn xác cho cả chuột máy tính lẫn màn hình cảm ứng iPad/máy tính bảng, tự động cộng dồn số ghế theo người đi kèm và lưu vết tức thì.

![Kéo thả khách mời vào bàn tiệc trực quan trên màn hình 2 phân vùng](/assets/client/docs/images/client/05-seating-planner/cli-seat-002-step-1.png)
*Giao diện Split Canvas: Danh sách khách chưa xếp chỗ bên trái và mặt bằng sơ đồ bàn tiệc kéo thả bên phải.*

---

## 1. Giao diện làm việc 2 phân vùng (Split Canvas Architecture)

Màn hình Seating Planner được bố trí công thái học theo tỷ lệ vàng:

```
┌───────────────────────────────┬────────────────────────────────────────────────────────┐
│  DANH SÁCH CHƯA XẾP CHỖ (30%) │           MẶT BẰNG SẢNH CƯỚI TRỰC QUAN (70%)          │
├───────────────────────────────┼────────────────────────────────────────────────────────┤
│ [🔍 Tìm khách...] [Bộ lọc ⏷] │ ┌─────────────┐   ┌─────────────┐   ┌─────────────┐    │
│                               │ │ BÀN VIP 01  │   │ BÀN VIP 02  │   │ BÀN BẠN C3  │    │
│ ⠿ Nguyễn Văn An (+1) [2 chỗ]  │ │  (10/10)    │   │   (8/10)    │   │   (6/10)    │    │
│ ⠿ Trần Thị Bích    [1 chỗ]  │ └─────────────┘   └─────────────┘   └─────────────┘    │
│ ⠿ Lê Hoàng Nam (+2) [3 chỗ]  │         Kéo thả bàn đổi tọa độ sảnh tiệc               │
│ ⠿ Phạm Thu Hà      [1 chỗ]  │                                                        │
│                               │ ┌─────────────┐   ┌─────────────┐                      │
│ 💡 Mẹo: Chạm giữ 0.2s để nhấc │ │ BÀN ĐỒNG    │   │ BÀN DỰ PHÒNG│      [ SÂN KHẤU ]    │
│ thẻ khách trên iPad           │ │ NGHIỆP CTY  │   │   (0/10)    │                      │
│                               │ └─────────────┘   └─────────────┘                      │
└───────────────────────────────┴────────────────────────────────────────────────────────┘
```

1. **Cột điều hướng bên trái (Guest Staging Drawer):**
   - Chứa toàn bộ khách đã xác nhận tham dự (RSVP "Sẽ Tham Dự") nhưng chưa được phân bổ vào bất kỳ bàn tiệc nào (`table_id IS NULL`).
   - Hiển thị rõ số suất ăn thực tế: Ví dụ thẻ khách *Anh Nam (+1)* sẽ hiển thị nhãn `[2 chỗ]` để bạn biết chính xác số ghế sẽ bị chiếm dụng khi thả vào bàn.
2. **Khu vực mặt bằng sảnh tiệc (Main Seating Canvas):**
   - Không gian vô cực (hỗ trợ cuộn ngang/dọc lên tới 3000px x 3000px).
   - Hiển thị các khối bàn tiệc tròn (Round Table), bàn chữ nhật (Long Table) kèm thanh tiến trình lấp đầy ghế (Seat Fill Bar) và danh sách tên khách đang ngồi trong bàn.

---

## 2. Thao tác trên Máy tính để bàn & Laptop (Desktop / Mouse)

### Cách 1: Kéo thả từ danh sách chờ vào bàn
1. Rê chuột vào thẻ tên khách ở cột bên trái. Biểu tượng con trỏ sẽ chuyển thành bàn tay nắm (`grab`).
2. Nhấn giữ chuột trái và kéo thẻ khách di chuyển vào vùng lòng chảo của bàn tiệc mục tiêu.
3. Khi bàn tiệc sáng viền vàng kim kèm hiệu ứng nhấp nháy đón nhận, hãy nhả chuột trái.
4. Hệ thống gửi yêu cầu không đồng bộ đến máy chủ qua route `seating.assignGuest`:
   ```json
   POST /tai-khoan/api/thiep/{id}/so-do-ban-tiec/xep-khach
   {
     "guest_id": 142,
     "table_id": 8
   }
   ```
5. Thẻ khách lập tức biến mất khỏi danh sách chờ bên trái và xuất hiện ngay ngắn trong bàn tiệc số 8. Số ghế trống của bàn tự động giảm tương ứng.

### Cách 2: Chuyển khách giữa hai bàn tiệc khác nhau
- Nếu muốn đổi chỗ cho khách từ *Bàn Bạn Cấp 3* sang *Bàn Bạn Đại Học*, bạn không cần xóa rồi xếp lại.
- Chỉ cần nhấp giữ thẻ tên khách ngay trong *Bàn Bạn Cấp 3* và kéo thẳng sang *Bàn Bạn Đại Học*, nhả chuột để hoàn tất.

### Cách 3: Gỡ khách ra khỏi bàn (Trả về danh sách chờ)
- Nhấp vào biểu tượng dấu gạch chéo **(×)** bên cạnh tên khách trong bàn, hoặc kéo thẻ khách từ bàn tiệc thả ngược lại vào cột danh sách bên trái.
- Hệ thống gửi request với `"table_id": null`, giải phóng lại số ghế trống cho bàn tiệc.

---

## 3. Thao tác mượt mà trên iPad và Màn hình Cảm ứng (Touch Gesture)

Mây Hẹn tích hợp bộ xử lý sự kiện cảm ứng chuyên sâu, khắc phục hoàn toàn nhược điểm giật lag hoặc vô tình kích hoạt thao tác cuộn trang của trình duyệt Safari trên iPad:

1. **Thao tác Chạm & Giữ nhẹ (Long-Press to Drag):**
   - Đặt đầu ngón tay hoặc bút Apple Pencil lên thẻ khách trong khoảng `200ms` (0.2 giây).
   - Thẻ khách sẽ khẽ nâng lên (hiệu ứng bóng đổ nổi 3D và rung nhẹ haptic feedback nếu thiết bị hỗ trợ).
   - Giữ nguyên ngón tay và trượt nhẹ nhàng vào ô bàn tiệc mong muốn, sau đó nhấc tay lên.
2. **Khóa cuộn trang thông minh (Scroll Lock Interceptor):**
   - Trong suốt quá trình ngón tay đang di chuyển thẻ khách, trình duyệt sẽ tạm thời vô hiệu hóa tính năng vuốt cuộn trang web để tránh hiện tượng bàn tiệc bị trôi lệch khỏi tầm nhìn.
3. **Phóng to / Thu nhỏ sảnh tiệc (Pinch to Zoom):**
   - Dùng 2 ngón tay chụm lại hoặc mở rộng trên màn hình để phóng to xem chi tiết từng vị trí ghế, hoặc thu nhỏ để có cái nhìn bao quát toàn bộ 30 - 50 bàn trong đại sảnh.

---

## 4. Tự động tính toán số ghế theo Người Đi Kèm (+1 Pax)

> [!IMPORTANT]
> **Quy tắc an toàn sức chứa (Household Attendees Weight):**  
> Trong tiệc cưới Việt Nam, một lời mời thường dành cho cả cặp đôi hoặc gia đình nhỏ. Seating Planner của Mây Hẹn **không đếm theo số dòng khách** mà **tính toán chính xác theo tổng số người thực tế tham dự** (`actualAttendeesCount`):

- **Ví dụ thực tế:**
  - Khách *Anh Tuấn* xác nhận đi cùng vợ (+1 người đi kèm) -> Tổng số suất ăn là **2 người**.
  - Bàn tiệc *VIP 03* có sức chứa chuẩn là 10 ghế và hiện đang có 9 người ngồi (còn 1 ghế trống).
  - Khi bạn kéo *Anh Tuấn* vào bàn này, hệ thống sẽ tự động phát hiện số người thêm vào (2) vượt quá số ghế còn lại (1).
  - Bàn tiệc sẽ chuyển sang trạng thái cảnh báo **Quá tải ghế (Over-capacity - 11/10 ghế)**, viền bàn chuyển sang màu đỏ rượu kèm thông báo nhắc nhở nhẹ để cặp đôi chủ động điều chỉnh.

---

## 5. Bố trí vị trí bàn trên Canvas & Tự động lưu tọa độ

Không chỉ xếp người vào bàn, bạn còn có thể tự do sắp đặt vị trí thực tế của từng bàn tiệc sao cho trùng khớp với sơ đồ hội trường của trung tâm tiệc cưới:

1. **Di chuyển bàn tiệc:**
   - Nhấp giữ vào thanh tiêu đề màu xám kem của bàn tiệc (phần hiển thị tên bàn).
   - Kéo bàn tiệc đến vị trí tương ứng trên sảnh (ví dụ kéo Bàn 1 và Bàn 2 về sát lối đi chính giữa sân khấu).
2. **Cơ chế Debounce Autosave:**
   - Khi bạn dừng kéo bàn, hệ thống kích hoạt cơ chế lưu vị trí sau `500ms`:
   ```json
   POST /tai-khoan/api/thiep/{id}/so-do-ban-tiec/vi-tri
   {
     "positions": [
       { "id": 1, "pos_x": 120, "pos_y": 80 },
       { "id": 2, "pos_x": 380, "pos_y": 80 }
     ]
   }
   ```
   - Góc trên màn hình sẽ xuất hiện biểu tượng đám mây màu xanh: *"Đã lưu tọa độ sảnh tiệc"*. Dù bạn tải lại trang hay mở bằng thiết bị khác, sơ đồ sảnh tiệc vẫn giữ nguyên vị trí hoàn hảo.

---

## 6. Xử lý sự cố thường gặp (Troubleshooting)

### Lỗi 1: Thẻ khách bị bật ngược về vị trí cũ khi thả tay
- **Nguyên nhân:** Tọa độ nhả chuột nằm chệch ra ngoài khu vực tiếp nhận của bàn tiệc, hoặc kết nối mạng Wi-Fi của sảnh cưới bị ngắt quãng giữa chừng.
- **Cách khắc phục:** Đảm bảo kéo thẻ khách vào đúng khung viền tròn/chữ nhật của bàn cho đến khi bàn đổi màu viền; nếu mạng mất kết nối, hệ thống sẽ thông báo lỗi và tự động khôi phục vị trí cũ để không làm mất dữ liệu.

### Lỗi 2: Trên iPad kéo thẻ khách làm cả trang web bị trượt lên trượt xuống
- **Nguyên nhân:** Chạm và vuốt quá nhanh (dưới 0.1 giây), trình duyệt Safari hiểu nhầm là cử chỉ lướt trang web thông thường.
- **Cách khắc phục:** Đặt ngón tay lên thẻ khách, dừng lại một nhịp thở (khoảng 0.2 giây) cho đến khi thẻ khách nổi lên nhẹ rồi mới bắt đầu di chuyển.

### Lỗi 3: Khách có trong danh sách nhưng không tìm thấy ở cột bên trái
- **Nguyên nhân:** Khách mời đó đã được xếp vào một bàn nào đó từ trước, hoặc trong danh sách khách mời chưa phản hồi RSVP / đã báo từ chối.
- **Cách khắc phục:** Gõ tên khách vào thanh tìm kiếm trên đỉnh cột trái; nếu khách đã có bàn, hệ thống sẽ tự động cuộn màn hình và nhấp nháy sáng bàn tiệc mà khách đang ngồi.
