---
id: CLI-CKIN-003
title: "Xử lý khách phát sinh (Walk-in) và cập nhật số người đi kèm tại sảnh tiệc"
category: "Điểm danh Lễ tân & Màn chiếu"
summary: "Hướng dẫn vận hành Bàn Xử Lý Ngoại Lệ (RSVP Exception Desk): Tiếp đón khách phát sinh ngoài danh sách (Walk-in), điều chỉnh số suất người đi kèm (+1/+2) tại chỗ, xử lý khách báo bận nhưng bất ngờ đến dự (Status Flip) và gán nhanh vào bàn dự phòng an toàn."
reader: "PER-CLI-02 (Đội lễ tân & MC)"
order: 13
updated_at: "2026-11-15"
tags: [mayhen, điểm-danh-lễ-tân, walk-in, adjust-attendees, exception-desk, buffer-tables]
mapped_page: "CLI-CKIN-PAGE"
mapped_route: "checkin.portal"
risk_level: "CRITICAL"
required_role: "customer"
---

# Xử lý khách phát sinh (Walk-in) và cập nhật số người đi kèm tại sảnh tiệc

> **Dành cho:** Đội lễ tân trực bàn đón khách và Trưởng ban điều phối tiệc cưới  
> **Thời gian thao tác:** Khoảng 30 giây / mỗi tình huống phát sinh  
> **Vị trí trong ứng dụng:** Cổng điểm danh `/diem-danh-tiec/{slug}` -> Nút **[⚡ Bàn Xử Lý Ngoại Lệ]**  
> **Quy chuẩn kỹ thuật:** Chuẩn tiếp đón không gián đoạn `TASK-VAL-21`

Trong thực tế tiệc cưới Việt Nam, dù cặp đôi đã chuẩn bị chu đáo đến đâu thì ngày cưới luôn có những tình huống bất ngờ xảy ra: Khách đi thêm người yêu hoặc con nhỏ mà chưa kịp báo trước, đối tác quan trọng bất ngờ thu xếp được thời gian đến chung vui dù trước đó đã báo bận, hay khách đi cùng nhóm bạn nhưng chưa có tên trong danh sách thiệp điện tử.

Nếu lễ tân lúng túng hoặc từ chối khách tại cửa, ngày vui sẽ mất đi sự ấm cúng và hiếu khách. Module **Bàn Xử Lý Ngoại Lệ (Reception Exception Desk)** của Mây Hẹn cung cấp 3 công cụ đặc nhiệm giúp giải quyết êm đẹp mọi tình huống trong vòng 30 giây.

---

## 1. Ba kịch bản phát sinh thực tế & Cách xử lý

```
┌─────────────────────────────────┬─────────────────────────────────┬─────────────────────────────────┐
│ KỊCH BẢN 1: KHÁCH PHÁT SINH     │ KỊCH BẢN 2: THAY ĐỔI SUẤT ĂN    │ KỊCH BẢN 3: BẤT NGỜ THAM DỰ     │
│          (WALK-IN)              │     (ADJUST ATTENDEES)          │         (STATUS FLIP)           │
├─────────────────────────────────┼─────────────────────────────────┼─────────────────────────────────┤
│ Khách hoàn toàn không có tên    │ Khách đăng ký 1 nhưng dắt theo  │ Khách đã bấm "Tiếc quá không    │
│ trong danh sách từ trước.       │ bạn (+1), hoặc đăng ký 3 nhưng  │ thể dự" nhưng phút chót đến     │
│                                 │ chỉ đi 1 người.                 │ chung vui trực tiếp.            │
│ ➔ Bấm [Thêm Khách Phát Sinh]    │ ➔ Bấm [Điều Chỉnh Số Suất (+/-)]│ ➔ Bấm [Chuyển Sang Tham Dự]     │
│ ➔ Gán vào Bàn Dự Phòng          │ ➔ Tự động tính lại ghế bàn      │ ➔ Khôi phục chỗ ngồi hoặc xếp bàn│
└─────────────────────────────────┴─────────────────────────────────┴─────────────────────────────────┘
```

---

## 2. Kịch bản 1: Thêm khách phát sinh hoàn toàn mới (Walk-in Guest)

Khi một vị khách đến sảnh mà lễ tân tìm kiếm cả họ tên lẫn số điện thoại đều không có trong hệ thống:

1. Trên màn hình Cổng điểm danh, nhấp nút **[⚡ Bàn Xử Lý Ngoại Lệ]** -> Chọn **[+ Thêm Khách Phát Sinh (Walk-in)]**.
2. Nhập thông tin nhanh trong popup:
   - **Xưng hô & Tên khách:** *Ví dụ: Anh Hoàng - Bạn chú rể*.
   - **Số người đi cùng:** *Mặc định là 1 (có thể tăng lên 2 hoặc 3 nếu đi cùng người thân)*.
   - **Nhóm khách:** Chọn nhóm tương ứng (*Họ Nhà Trai, Họ Nhà Gái, Bạn Bè, Đối Tác*).
   - **Chỉ định bàn tiệc:**  
     - Hệ thống tự động gợi ý các **Bàn Dự Phòng (Buffer Tables)** còn ghế trống.
     - Hoặc lễ tân có thể chọn xếp vào một bàn tiệc bạn bè đang còn thừa ghế.
   - **Ghi chú:** Nhập ngắn gọn (*Khách phát sinh bạn cấp 3*).
3. Nhấp nút **[Xác Nhận & Đón Vào Bàn]**.
4. **Kết quả:**  
   - Hệ thống tự động kích hoạt API `POST /api/diem-danh-tiec/{slug}/ngoai-le/khach-phat-sinh`.
   - Khách được tạo mới, đánh dấu đã điểm danh ngay lập tức, số suất thực tế cộng dồn vào tổng đón tiếp.
   - Lễ tân có thể đọc ngay số bàn cho khách mà không làm gián đoạn dòng người đang tiến vào sảnh.

---

## 3. Kịch bản 2: Điều chỉnh số suất người đi kèm tại chỗ (Adjust Attendees)

Tình huống rất phổ biến: Khách mời thiệp ghi 1 suất nhưng khi đến mang theo người yêu (+1), hoặc một gia đình đăng ký 4 người nhưng chỉ có 2 vợ chồng đi dự:

1. Quét mã QR hoặc tìm tên khách trên màn hình tiếp đón.
2. Tại popup thông tin đón tiếp, bấm nút **[✏️ Điều chỉnh suất]** (hoặc nhấp trực tiếp vào biểu tượng `[+]` / `[-]` cạnh số lượng người).
3. Chọn số người thực tế đang đứng tại sảnh (ví dụ chuyển từ `1` thành `2`).
4. Hệ thống gọi API `POST /api/diem-danh-tiec/{slug}/ngoai-le/dieu-chinh-suat/{guestId}`:
   - Nếu bàn tiệc của khách vẫn còn ghế: Hệ thống trừ thêm 1 ghế trống của bàn.
   - Nếu bàn tiệc bị đầy ghế: Hệ thống hiện cảnh báo viền vàng *"Bàn đã đủ 10 người. Bạn có muốn kê thêm ghế đôn hay chuyển sang bàn khác?"*. Lễ tân có thể quyết định nhanh chóng.
5. Tổng số mâm cỗ thực tế của nhà hàng được cập nhật tức thời để Quản lý tiệc thông báo cho bếp trưởng.

---

## 4. Kịch bản 3: Xử lý khách báo bận nhưng bất ngờ đến dự (Status Flip)

Nhiều khách mời trước ngày cưới có lịch công tác nên đã chọn "Rất tiếc không thể tham dự", nhưng sau đó đổi lịch và đến chung vui bất ngờ:

1. Tại ô tìm kiếm, gõ tên khách. Khách sẽ xuất hiện với nhãn xám: `[Đã Báo Bận]`.
2. Bấm chọn vị trí thẻ khách. Popup hiện thông báo:  
   *"Khách này trước đó đã báo không tham dự. Bạn có muốn chuyển sang trạng thái tham dự và xếp bàn ngay không?"*.
3. Nhấp nút màu xanh: **[✨ Chuyển Sang Tham Dự & Đón Khách]**.
4. Hệ thống gọi API `POST /api/diem-danh-tiec/{slug}/ngoai-le/chuyen-tham-du/{guestId}`:
   - Tự động lật trạng thái RSVP từ `Declined` sang `Attending`.
   - Nếu trước đó khách đã từng có bàn tiệc cũ và bàn đó còn chỗ: Tự động đưa khách về bàn cũ.
   - Nếu bàn cũ đã bị lấp đầy bởi khách khác: Gợi ý lễ tân xếp khách vào **Bàn Dự Phòng**.
5. Ghi nhận thời gian điểm danh thực tế và bắn lời chúc mừng lên Màn chiếu sân khấu.

---

## 5. Quy tắc ứng xử thanh lịch dành cho Lễ Tân

> [!TIP]
> **Tuyệt đối không hỏi vặn khách:**  
> Dù khách đi thêm người hay không có tên trong danh sách, lễ tân luôn giữ thái độ niềm nở, không bao giờ nói: *"Sao anh không đăng ký trước?"*. Hãy mỉm cười: *"Dạ vâng, em rất vui được đón tiếp anh chị ạ, em đã xếp bàn chu đáo cho anh chị rồi đây ạ!"* và âm thầm dùng tính năng Walk-in trên điện thoại.

> [!IMPORTANT]
> **Báo cáo chênh lệch suất ăn với Bếp Trưởng trước giờ khai tiệc:**  
> Đúng 15 phút trước giờ làm lễ, Lễ tân trưởng bấm xem tổng số khách phát sinh tại Bàn Ngoại Lệ. Nếu số khách phát sinh vượt quá 1 bàn (trên 10 người), lập tức thông báo Quản lý sảnh tiệc để bếp mở thêm mâm cỗ dự phòng, đảm bảo mọi vị khách đều được thưởng thức trọn vẹn thực đơn tiệc cưới.
