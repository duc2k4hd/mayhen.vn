---
id: CLI-CKIN-002
title: "Quét mã QR trên thiệp khách để điểm danh trong 1 giây và xem số bàn tiệc"
category: "Điểm danh Lễ tân & Màn chiếu"
summary: "Hướng dẫn thao tác quét mã QR đón khách thần tốc: Tối ưu camera điện thoại, nhận diện khách mời trong 1 giây, tra cứu số bàn tức thời, âm thanh xác nhận chào mừng và cách xử lý khi khách quét lại lần 2 (tránh trùng lặp)."
reader: "PER-CLI-02 (Đội lễ tân & MC)"
order: 12
updated_at: "2026-11-15"
tags: [mayhen, điểm-danh-lễ-tân, quét-qr, 1-second-checkin, table-assignment]
mapped_page: "CLI-CKIN-PAGE"
mapped_route: "checkin.portal"
risk_level: "CRITICAL"
required_role: "customer"
---

# Quét mã QR trên thiệp khách để điểm danh trong 1 giây và xem số bàn tiệc

> **Dành cho:** Đội ngũ tiếp tân đón khách tại cửa đại sảnh hôn lễ  
> **Tốc độ xử lý:** Dưới `1.0 giây` / mỗi lượt khách  
> **Vị trí trong ứng dụng:** Cổng điểm danh `mayhen.vn/diem-danh-tiec/{slug}` -> Tab **[Quét Mã QR]**  
> **API Xử lý:** `checkin.scan` (`POST /api/diem-danh-tiec/{slug}/quet`)

Trong các đám cưới quy mô từ 300 đến 800 khách, khung giờ cao điểm đón khách (thường là 30 phút trước giờ khai tiệc) là thời điểm lễ tân chịu áp lực lớn nhất. Khách đến dồn dập thành từng tốp đông đúc, nếu phải lật giở từng trang danh sách in giấy để tra tên và tìm bàn, sảnh tiệc chắc chắn sẽ bị ùn tắc.

Tính năng **Quét QR Điểm Danh Thần Tốc** của Mây Hẹn sử dụng thuật toán nhận diện camera độ phân giải cao kết hợp Web Worker, cho phép giải mã mã QR trên màn hình điện thoại hoặc thiệp in của khách chỉ trong **chưa đầy 1 giây**, hiển thị ngay số bàn tiệc to rõ và phát âm thanh chào mừng sinh động.

---

## 1. Cơ chế hoạt động của Mã QR Tiếp Đón

Mỗi khách mời trong danh sách của Mây Hẹn đều sở hữu một mã định danh duy nhất (Guest Unique Token) gắn liền với mã QR:
- **Khách mở thiệp online:** Mã QR hiển thị trực tiếp ở Section 10 hoặc trong Thẻ Đồng Hành (Companion Card) lưu trong ví điện thoại (Apple Wallet/Google Wallet).
- **Khách cầm thiệp in:** Mã QR vector 300 DPI được in sắc nét ở mặt sau phong bao thiệp cưới.

Khi camera cổng điểm danh quét trúng mã này:

```
[ THIỆP KHÁCH MỜI ]                          [ MÀN HÌNH LỄ TÂN ĐÓN KHÁCH ]
┌──────────────┐     Camera quét QR (1s)     ┌────────────────────────────────────┐
│ ┌──────────┐ │ ──────────────────────────> │ ✨ XIN CHÀO ANH LÊ MINH HOÀNG      │
│ │  QR CODE │ │                             ├────────────────────────────────────┤
│ └──────────┘ │                             │ 🏷️ BÀN SỐ: BÀN 06 - BẠN ĐẠI HỌC     │
│  Lê M. Hoàng │                             │ 📍 KHU VỰC: Cánh Trái Sân Khấu     │
│   (2 suất)   │                             │ 👥 ĐI CÙNG: 2 Người (Đã đăng ký)   │
└──────────────┘                             │ 🥗 ĂN KIÊNG: 1 Suất Ăn Chay        │
                                             └────────────────────────────────────┘
                                                   │
                                                   ├──> Tự động tăng sĩ số đón tiếp
                                                   └──> Bắn lời chúc lên Màn LED sân khấu
```

---

## 2. Quy trình 3 bước quét QR chuẩn chỉ

### Bước 1: Khởi động ống kính đón khách
1. Mở Cổng điểm danh trên điện thoại hoặc máy tính bảng: `/diem-danh-tiec/{slug}`.
2. Chọn tab **[📷 Quét Mã QR]**. Khung ngắm quét mã sẽ xuất hiện với 4 góc định vị màu vàng champagne.
3. Nếu quét vào buổi tối hoặc sảnh đón thiếu sáng, nhấp vào biểu tượng **[🔦 Bật Đèn Flash]** trên màn hình để camera trợ sáng.

### Bước 2: Hướng ống kính về phía mã QR
- Hướng camera vuông góc với mã QR trên điện thoại của khách (khoảng cách lý tưởng: từ `15cm` đến `25cm`).
- Giữ yên tay trong nửa nhịp thở. Ngay khi mã lọt vào khung quét, hệ thống sẽ phát âm thanh chuông ngân *(Chime Sound)* nhẹ nhàng và rung nhẹ xác nhận.

### Bước 3: Xem thông tin và hướng dẫn bàn cho khách
Màn hình lập tức bật lên thẻ Chào đón với đầy đủ thông tin chi tiết:
1. **Xưng hô & Tên khách:** *Anh Lê Minh Hoàng & Bạn gái*.
2. **Số bàn & Tên bàn:** Nổi bật với kích thước font lớn màu đỏ rượu (*BÀN 06 - BẠN ĐẠI HỌC*).
3. **Khu vực sảnh (Zone):** *Gần lối đi chính giữa sảnh*.
4. **Ghi chú đặc biệt:** Hiển thị nhãn cảnh báo màu vàng nếu khách có ghi chú ăn chay, dị ứng hải sản hoặc cần ghế trẻ em.

Lễ tân chỉ cần nở nụ cười tươi và chỉ dẫn:  
*"Dạ em chào anh Hoàng, vị trí của anh chị là Bàn số 06 ở phía bên trái sân khấu ạ. Em chúc anh chị dự tiệc vui vẻ!"*.

---

## 3. Chống quét trùng lặp (Anti-Double Scan Protection)

Một tình huống thường gặp là khách vô tình quét lại mã lần 2 hoặc hai tiếp tân đứng cạnh nhau cùng quét chung một mã thiệp:

- **Khi quét lần đầu:** Hệ thống ghi nhận trạng thái `checked_in = true`, lưu thời gian chính xác tới từng giây (`checked_in_at: 18:42:15`) và cộng dồn số suất vào bộ đếm.
- **Khi quét lại lần 2:** Hệ thống **không cộng trùng suất**, màn hình chuyển sang viền màu vàng cam thông báo:
  ```
  ⚠️ KHÁCH ĐÃ ĐIỂM DANH TRƯỚC ĐÓ!
  Khách: Anh Lê Minh Hoàng
  Đã đón lúc: 18:42:15 (cách đây 4 phút)
  Vị trí: Bàn 06 - Bạn Đại Học
  ```
- Tính năng này giúp cặp đôi kiểm soát tuyệt đối sĩ số khách thực tế, không bao giờ bị báo tăng ảo số mâm cỗ với nhà hàng tiệc cưới.

---

## 4. Hủy điểm danh khi quét nhầm (1-Tap Undo)

Nếu lễ tân vô tình chọn nhầm khách hoặc khách báo có việc gấp phải về trước khi vào bàn tiệc:
1. Nhấp ngay vào nút **[↩️ Hủy đón khách này (Undo)]** trên thông báo thành công (có hiệu lực trong vòng 10 giây).
2. Hoặc cuộn xuống mục **Lịch Sử Đón Tiếp Gần Đây** (`Recent Check-ins`), tìm tên khách và nhấp **[Gỡ điểm danh]**.
3. Hệ thống gửi yêu cầu `POST /api/diem-danh-tiec/{slug}/huy/{guestId}`, tự động trừ lại sĩ số và khôi phục trạng thái về chưa vào bàn.

---

## 5. Mẹo tối ưu hóa tốc độ quét cho Lễ Tân

> [!TIP]
> **Khách để màn hình điện thoại quá tối:**  
> Mã QR cần độ tương phản cao để camera nhận diện. Nhắc khách tăng độ sáng màn hình điện thoại lên trên 70% hoặc lễ tân bật đèn Flash trợ sáng của cổng điểm danh.

> [!TIP]
> **Chế độ Tự Động Xác Nhận (Auto-Dismiss 2s):**  
> Trong giờ cao điểm khách đến đông, bạn không cần nhấn nút [Đã đón]. Sau khi quét thành công 2 giây, popup kết quả sẽ tự động đóng lại để camera sẵn sàng quét tiếp mã của vị khách tiếp theo ngay lập tức.
