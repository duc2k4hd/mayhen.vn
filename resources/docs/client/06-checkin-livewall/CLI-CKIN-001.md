---
id: CLI-CKIN-001
title: "Hướng dẫn vận hành cổng điểm danh lễ tân và quét mã QR tiếp đón tại sảnh cưới"
category: "Điểm danh Lễ tân & Màn chiếu"
summary: "Cẩm nang quy chuẩn dành cho đội lễ tân tiếp đón tại sảnh cưới: Quét mã QR trong 1 giây, tra cứu nhanh khách mời bằng tên/SĐT, điều chỉnh số suất thực tế, xử lý khách phát sinh (walk-in), bàn giao ca trực và vận hành ngoại tuyến khi sảnh tiệc mất mạng."
reader: "PER-CLI-02 (Đội lễ tân & MC)"
order: 11
updated_at: "2026-11-13"
tags: [mayhen, điểm-danh-lễ-tân, qr-checkin, dry-run, offline-pack]
mapped_page: "CLI-CKIN-PAGE"
mapped_route: "checkin.portal"
risk_level: "CRITICAL"
required_role: "customer"
---

# Hướng dẫn vận hành cổng điểm danh lễ tân và quét mã QR tiếp đón tại sảnh cưới

> **Dành cho:** Đội lễ tân tiếp đón tại bàn đón khách, Wedding Planner và MC tiệc cưới  
> **Thời gian làm quen:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Cổng điểm danh chuyên biệt `/diem-danh-tiec/{slug}` (Route: `checkin.portal`)  

Giây phút khách mời bước chân vào sảnh tiệc là khoảnh khắc vàng quyết định ấn tượng đầu tiên về sự chu đáo của đám cưới. **Cổng Tiếp Tân Điểm Danh** của Mây Hẹn biến chiếc điện thoại hoặc máy tính bảng (iPad/Tablet) của lễ tân thành một máy quét QR chuyên nghiệp, giúp nhận diện khách mời trong **1 giây**, thông báo chính xác số bàn tiệc và đồng bộ dữ liệu trực tiếp lên màn hình LED sảnh tiệc (Live Wall).

---

## 1. Chuẩn bị trước giờ đón khách (Trước giờ G 30 phút)

1. **Thiết bị:** 1 hoặc 2 điện thoại thông minh (iPhone/Android) hoặc máy tính bảng có camera rõ nét, kết nối 4G/Wifi ổn định.
2. **Đường dẫn truy cập:** Mở trình duyệt web (Safari trên iOS hoặc Chrome trên Android) và truy cập đường dẫn:  
   `mayhen.vn/diem-danh-tiec/{slug-thiep-cuoi}`  
   *(Ví dụ: `mayhen.vn/diem-danh-tiec/tuan-lan-2027`)*
3. **Cấp quyền Camera:** Khi trình duyệt hỏi *"Cho phép truy cập máy ảnh"*, hãy chọn **[Cho phép (Allow)]**.

---

## 2. Diễn tập làm quen trước giờ đón khách (Reception Dry Run - TASK-VAL-20)

Để đội lễ tân tự tin làm quen và kiểm tra camera trước khi khách thật tới, Mây Hẹn cung cấp chế độ **Diễn Tập Lễ Tân (Dry Run)**:
1. Nhấp nút **[🧪 Bật Chế Độ Diễn Tập Lễ Tân]** ở góc trên màn hình.
2. Màn hình sẽ hiển thị dải cờ màu tím báo hiệu đang trong môi trường diễn tập.
3. Hệ thống cung cấp sẵn **5 Kịch Bản Thử Nghiệm Nhanh 1 Chạm**:
   - *Khách VIP có bàn tiệc chuẩn:* Quét thành công, hiện số bàn tiệc màu xanh.
   - *Khách chưa xếp bàn:* Báo hiệu màu vàng để lễ tân xếp bàn tại chỗ.
   - *Khách có yêu cầu ăn kiêng (Ăn chay):* Hiện thông báo khẩu phần ăn đặc biệt.
   - *Khách đi cùng gia đình (nhiều suất):* Hiện nút xác nhận số người thực tế.
4. Bấm **[Tắt Chế Độ Diễn Tập]** trước khi mở cổng đón khách thật. Toàn bộ lượt quét thử nghiệm sẽ tự động được làm sạch, **tuyệt đối không làm ô nhiễm dữ liệu thật** của ngày cưới.

---

## 3. Quy trình tiếp đón & điểm danh khách mời thực tế

### Trường hợp 1: Khách đưa mã QR trên thiệp cưới (Ưu tiên - 1 Giây)
1. Chọn tab **[📷 Quét Mã QR Khách Mời]**.
2. Đưa camera điện thoại về phía mã QR trên màn hình điện thoại hoặc thiệp in của khách (khoảng cách 15 - 25cm).
3. Ống kính bắt mã tức thì, màn hình sẽ hiển thị thẻ chào đón trang nhã:
   - **Tên khách mời:** *Anh Hoàng & Bạn gái*
   - **Số bàn tiệc:** **BÀN 06 - BẠN ĐẠI HỌC** *(Hiện to, rõ ràng)*
   - **Sĩ số:** 2 người
4. Lễ tân mỉm cười chào đón và hướng dẫn: *"Dạ em mời anh chị vào Bàn số 06 ạ!"*.
5. Bấm **[Xác nhận đón vào bàn]** (hoặc để hệ thống tự động xác nhận sau 2 giây). Thẻ đếm sĩ số sẽ tự động nhảy lên.

### Trường hợp 2: Khách không mang điện thoại hoặc quên mã QR
1. Chuyển sang tab **[🔍 Tìm Tên / SĐT Điểm Danh]**.
2. Gõ vài chữ cái trong tên khách hoặc 3 số cuối số điện thoại (ví dụ: gõ `hoang` hoặc `678`).
3. Danh sách khách mời hiển thị tức thì theo bộ lọc không dấu tiếng Việt.
4. Bấm chọn đúng tên khách, hệ thống sẽ hiện thông tin số bàn tiệc và nút **[Điểm danh ngay]**.

---

## 4. Xử lý các tình huống ngoại lệ tại sảnh tiệc (RSVP Exception Desk - TASK-VAL-21)

Khi gặp các tình huống phát sinh ngoài dự kiến, bấm nút **[⚡ Bàn Xử Lý Ngoại Lệ]** để can thiệp nhanh:
- **Khách dẫn thêm người (+1 vượt dự kiến):** Bấm nút điều chỉnh suất (ví dụ từ 1 người thành 2 người). Sĩ số cỗ thực tế sẽ được cập nhật ngay để báo cho nhà bếp.
- **Khách báo bận nhưng bất ngờ đến dự (Walk-in / Status Flip):** Hệ thống cho phép chuyển trạng thái từ `Declined` sang `Checked-in` trong 1 chạm và gợi ý các bàn tiệc còn ghế trống.
- **Đổi bàn tiệc tại chỗ:** Nếu khách muốn ngồi cùng nhóm bạn khác, lễ tân có thể chọn đổi sang bàn tiệc mới trực tiếp trên popup.

---

## 5. Bàn giao ca trực lễ tân (Staff Shift Handover - TASK-VAL-23)

Trong các đám cưới đông khách thường chia thành 2 ca tiếp tân (Ca 1: Đón họ hàng; Ca 2: Đón bạn bè, đối tác):
1. Khi hết ca, lễ tân trưởng bấm nút **[📋 Bàn Giao Ca Trực]**.
2. Hệ thống xuất biên bản tổng kết tự động:
   - Tổng số khách đã đón trong ca.
   - Số trường hợp phát sinh ngoại lệ hoặc đổi bàn.
   - Danh sách khách VIP chưa tới để ca sau lưu ý.
3. Nhập tên người nhận ca mới và bấm **[Xác nhận bàn giao]**.

---

## 6. Bộ cứu hộ ngoại tuyến khi sảnh tiệc mất mạng (Wedding Day Resilience Kit - TASK-VAL-03)

> [!WARNING]
> **Khi sảnh tiệc bị nghẽn sóng 4G hoặc mất kết nối Wifi:**  
> Đừng hoang mang! Cổng điểm danh Mây Hẹn có tích hợp **Local Service Worker Storage**. Khi mất mạng, camera vẫn quét QR và lưu lượt điểm danh trực tiếp vào bộ nhớ máy điện thoại của lễ tân.  
> Ngay khi điện thoại có sóng trở lại, toàn bộ lượt khách điểm danh ngoại tuyến sẽ tự động đồng bộ lên máy chủ và màn hình LED mà không mất bất kỳ bản ghi nào!

