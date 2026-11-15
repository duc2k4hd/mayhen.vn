---
id: CLI-GUEST-006
title: "Theo dõi sĩ số khách phản hồi thời gian thực và quản lý lịch sử thay đổi RSVP"
category: "Quản lý Khách mời & RSVP"
summary: "Cẩm nang quản trị phản hồi cưới: Theo dõi biểu đồ sĩ số thời gian thực, cơ chế ghi đè RSVP thủ công (Manual Override), lịch sử thay đổi phản hồi (Audit Log) và in danh sách đối soát lễ tân khổ A4."
reader: "PER-CLI-02 / PER-CLI-03"
order: 27
updated_at: "2026-11-15"
tags: [mayhen, quan-ly-khach-moi, theo-doi-rsvp, lich-su-rsvp, manual-override, in-danh-sach-a4]
mapped_page: "CLI-GUEST-PAGE"
mapped_route: "guests.index"
risk_level: "MEDIUM"
required_role: "customer"
---

# Theo dõi sĩ số khách phản hồi thời gian thực và quản lý lịch sử thay đổi RSVP

> **Dành cho:** Cô dâu & Chú rể theo dõi tiến độ phản hồi thiệp để chốt số lượng mâm cỗ chính xác với nhà hàng  
> **Thời gian thao tác:** Kiểm tra hàng ngày hoặc định kỳ trước đám cưới 1-2 tuần  
> **Vị trí trong ứng dụng:** Dashboard Quản trị Thiệp → Menu **Quản Lý Khách Mời** (`/khach-moi`)  

Trong khâu tổ chức tiệc cưới, giai đoạn "đau đầu" nhất của các cặp đôi là chốt số lượng bàn tiệc trước 3 - 5 ngày diễn ra hôn lễ. Nếu đặt dư 5 bàn, hai bạn có thể lãng phí từ 20 đến 40 triệu đồng. Nếu đặt thiếu bàn, quan khách đến đông đủ sẽ phải đứng chờ hoặc ghép bàn chật chội. Tính năng **Bảng Điều Khiển Sĩ Số Thời Gian Thực (Live RSVP Dashboard)** của Mây Hẹn giúp hai bạn luôn nắm chắc trong tay từng con số chính xác đến từng người.

---

## 1. Hệ thống 4 trạng thái phản hồi chuẩn mực

Trong danh sách khách mời, mỗi vị khách luôn gắn liền với một trạng thái màu sắc trực quan:

```
┌────────────────────────────────────────────────────────────────────────┐
│             4 TRẠNG THÁI PHẢN HỒI RSVP TRÊN MÂY HẸN                    │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  ✓ THAM DỰ          │  ✕ KHÔNG THỂ ĐẾN   │  ⏳ CHƯA PHẢN HỒI           │
│  (CONFIRMED - XANH) │  (DECLINED - XÁM)  │  (PENDING - HỔ PHÁCH)       │
│                     │                    │                             │
│ • Khách xác nhận đến│ • Bận việc gửi lời │ • Đã gửi nhưng khách chưa   │
│ • Hiển thị rõ số suất│   chúc từ xa       │   bấm chọn                  │
│ • Tính vào mâm cỗ   │ • Không tính mâm cỗ│ • Cần gửi tin nhắn nhắc hẹn │
├─────────────────────┴────────────────────┴─────────────────────────────┤
│  ✎ ĐÁNH DẤU THỦ CÔNG (MANUAL RSVP OVERRIDE)                             │
│ • Cặp đôi tự cập nhật khi người lớn gọi điện thoại báo hỷ trực tiếp     │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Các thao tác quản trị trên Bảng Điều Khiển Khách Mời

### 1. Lọc nhanh danh sách theo trạng thái
Trên thanh công cụ tìm kiếm, bạn có thể nhấp chọn các bộ lọc:
- **Tất cả (`All`):** Xem toàn bộ danh sách 300+ khách.
- **Chắc chắn đến (`Attending`):** Xem riêng những ai đã nhận lời, phục vụ việc xếp bàn tiệc.
- **Chưa phản hồi (`Pending`):** Lọc ra những người cần gửi tin nhắn nhắc nhở tế nhị.
- **Không thể đến (`Declined`):** Xem lại những lời chúc phương xa gửi về.

### 2. Cập nhật trạng thái thủ công (Manual Override)
Nhiều bác lớn tuổi hoặc người thân quen thường không bấm link xác nhận trên điện thoại mà sẽ gọi điện trực tiếp: *"Tuần sau hai bác với anh Nam sẽ bay vào chung vui nhé!"*. Lúc này:
1. Bạn tìm tên vị khách đó trên thanh tìm kiếm.
2. Nhấp vào cột trạng thái RSVP.
3. Chọn: **[Tham dự]** → Nhập số người đi cùng: `3 người`.
4. Hệ thống sẽ ghi nhận trạng thái và tính ngay 3 suất ăn vào tổng sĩ số bàn tiệc.

---

## 3. Lịch sử thay đổi phản hồi (RSVP Audit History)

Đám cưới có thể kéo dài thời gian chuẩn bị trong 1 - 2 tháng, nhiều vị khách lúc đầu báo bận nhưng sau đó sắp xếp được lại báo đi, hoặc ban đầu báo đi 2 người nhưng sau đó lại chỉ đi 1 mình:
- Mây Hẹn tự động ghi nhận **Nhật Ký Lịch Sử Phản Hồi (Audit Log)**.
- Khi nhấp vào chi tiết vị khách, bạn sẽ thấy dòng thời gian minh bạch:
  - *Ngày 10/11 - 09:30:* Khách mở thiệp xem lần đầu.
  - *Ngày 12/11 - 15:45:* Khách bấm xác nhận tham dự 2 người.
  - *Ngày 20/11 - 18:20:* Khách cập nhật lại thành 1 người (ghi chú: *Chồng đi công tác*).
- Điều này giúp hai bên gia đình hoàn toàn an tâm, không bao giờ xảy ra tình trạng tranh cãi hay nhớ nhầm thông tin.

---

## 4. In danh sách đón khách khổ A4 cho Đội Lễ Tân (Print Layout)

Vào ngày diễn ra hôn lễ, một bản danh sách in bằng giấy trang nhã đặt tại bàn đón khách (Bàn Gallery) là vô cùng cần thiết để đội ngũ phù dâu, phù rể đón tiếp và đối soát khách mời:
1. Tại trang Quản Lý Khách Mời, nhấp nút **[🖨 In Danh Sách A4]** (Route `guests.print`).
2. Màn hình in ấn chuyên dụng sẽ mở ra:
   - Tự động ẩn các nút bấm, tối ưu font chữ đen trên nền trắng sắc nét.
   - Bố cục danh sách theo thứ tự bảng chữ cái ABC, chia sẵn các cột: *Họ tên, Nhóm khách, Số người dự kiến, Số bàn tiệc đã xếp, Ô tích chọn đã đến*.
   - Phân trang chuẩn xác, không bị cắt ngang dòng giữa các trang giấy.
3. Nhấn `Ctrl + P` để in trực tiếp ra giấy hoặc lưu thành file PDF gửi cho đội lễ tân.

---

## 5. Xuất dữ liệu ra Excel/CSV (Full Export)

Nếu muốn tự phân tích số liệu trên Microsoft Excel:
- Nhấp nút **[📥 Xuất File Excel/CSV]** trên thanh công cụ.
- Hệ thống sẽ sinh tệp tải về chứa đầy đủ: *Họ tên, SĐT, Nhóm bạn, Trạng thái RSVP, Số người lớn, Số trẻ em, Món ăn kiêng, Bàn tiệc, Ngày phản hồi*.

---

## 6. Bài viết liên quan tiếp theo

- [Thiết lập kế hoạch nhắc hẹn lịch sự (Gentle Reminder) gửi trước ngày cưới](/docs/client/04-guest-rsvp/CLI-GUEST-007)
- [Sắp xếp sơ đồ bàn tiệc và chỗ ngồi thông minh](/docs/client/05-seating-planner/CLI-SEAT-001)
- [Hướng dẫn lễ tân check-in và quét mã QR tại sảnh tiệc](/docs/client/06-checkin-livewall/CLI-CHCK-001)
