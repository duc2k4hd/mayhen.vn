---
id: CLI-SEAT-003
title: "Phân chia khu vực bàn tiệc theo sự kiện (Lễ Vu Quy nhà gái riêng, Lễ Thành Hôn nhà trai riêng)"
category: "Sơ đồ Bàn tiệc & Chỗ ngồi"
summary: "Hướng dẫn phân chia khu vực bàn tiệc (Zones) và quy hoạch chỗ ngồi cho đám cưới đa sự kiện: Tách biệt Lễ Vu Quy (Nhà Gái), Lễ Thành Hôn (Nhà Trai) và Tiệc Mừng, phân vùng VIP sân khấu, bạn bè thân thiết và đồng nghiệp."
reader: "PER-CLI-02 (Cặp đôi đang quản lý tiệc)"
order: 8
updated_at: "2026-11-15"
tags: [mayhen, sơ-đồ-bàn-tiệc-&-chỗ-ngồi, zones, vu-quy-thanh-hon, multi-event-seating]
mapped_page: "CLI-SEAT-PAGE"
mapped_route: "seating.index"
risk_level: "HIGH"
required_role: "customer"
---

# Phân chia khu vực bàn tiệc theo sự kiện (Lễ Vu Quy nhà gái riêng, Lễ Thành Hôn nhà trai riêng)

> **Dành cho:** Cô dâu & Chú rể tổ chức nhiều sự kiện cưới (Nhà Trai và Nhà Gái tổ chức riêng hoặc chung sảnh)  
> **Thời gian thiết lập:** Khoảng 5 - 10 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sơ Đồ Xếp Bàn Tiệc** -> **Phân Vùng Khu Vực (Zones)** (Route: `seating.index`)  
> **Thuộc tính quản lý:** Trường `zone` trong bảng `wedding_tables`

Theo phong tục cưới hỏi truyền thống Việt Nam, hôn lễ thường bao gồm nhiều nghi thức diễn ra ở các địa điểm và thời điểm khác nhau: **Lễ Vu Quy** tổ chức tại tư gia hoặc nhà hàng phía nhà gái, **Lễ Thành Hôn** tại nhà trai, và đôi khi có thêm **Tiệc Mừng Báo Hỷ** tại thành phố hai bạn đang sinh sống làm việc.

Seating Planner của Mây Hẹn tích hợp cơ chế **Phân Vùng Không Gian (Seating Zones)** thông minh, cho phép bạn quy hoạch sơ đồ bàn tiệc độc lập hoặc chia tách ranh giới rõ ràng trong cùng một sảnh đại tiệc mà không bao giờ bị lẫn lộn khách mời giữa hai họ.

---

## 1. Hai kịch bản tổ chức tiệc cưới phổ biến

```
KỊCH BẢN A: TỔ CHỨC CHUNG SẢNH TIỆC (1 ĐẠI TIỆC GỘP)
┌─────────────────────────────────┬─────────────────────────────────┐
│     KHU VỰC HỌ NHÀ GÁI (ZONE A) │     KHU VỰC HỌ NHÀ TRAI (ZONE B)│
│  - Bàn VIP 01: Bố Mẹ Nhà Gái    │  - Bàn VIP 02: Bố Mẹ Nhà Trai   │
│  - Bàn 03 - 06: Họ hàng cô dâu  │  - Bàn 07 - 10: Họ hàng chú rể  │
│  - Bàn 11 - 14: Bạn bè cô dâu   │  - Bàn 15 - 18: Bạn bè chú rể   │
├─────────────────────────────────┴─────────────────────────────────┤
│                     [ SÂN KHẤU TRUNG TÂM ]                        │
└───────────────────────────────────────────────────────────────────┘

KỊCH BẢN B: TỔ CHỨC RIÊNG BIỆT (HAI ĐỊA ĐIỂM / HAI NGÀY KHÁC NHAU)
┌─────────────────────────────────┐ ┌─────────────────────────────────┐
│     LỄ VU QUY (NHÀ GÁI)         │ │     LỄ THÀNH HÔN (NHÀ TRAI)     │
│  Địa điểm: Khách sạn A (Hà Nội) │ │  Địa điểm: Trung tâm B (Đà Nẵng)│
│  Lọc Zone: "Vu Quy - Nhà Gái"   │ │  Lọc Zone: "Thành Hôn - Nhà Trai"│
│  Khách: 150 người họ nhà gái   │ │  Khách: 200 người họ nhà trai │
└─────────────────────────────────┘ └─────────────────────────────────┘
```

---

## 2. Cách thiết lập Phân Vùng (Zone Assignment) khi tạo bàn

Khi khởi tạo hoặc chỉnh sửa từng bàn tiệc, bạn sử dụng trường **Khu vực (Zone)** để định danh không gian:

1. **Bước 1: Mở pop-up tạo bàn tiệc mới**
   - Nhấp nút **[+ Thêm Bàn Tiệc]** trên thanh công cụ Seating Planner.
2. **Bước 2: Gắn nhãn Zone chuẩn hóa**
   - Trong ô **Khu vực (Zone)**, bạn có thể nhập trực tiếp tên phân vùng mong muốn:
     - Kịch bản gộp chung: `Họ Nhà Gái`, `Họ Nhà Trai`, `Bạn Bè Chung`, `Đồng Nghiệp`.
     - Kịch bản sự kiện riêng: `Lễ Vu Quy`, `Lễ Thành Hôn`, `Tiệc Báo Hỷ`.
     - Phân tầng vị trí hội trường: `Sát Sân Khấu (VIP)`, `Giữa Sảnh`, `Gần Cửa Ra Vào`.
3. **Bước 3: Lưu bàn tiệc**
   - Nhấp **[Lưu Bàn Tiệc]**. Hệ thống sẽ tự động gán nhãn màu riêng biệt cho từng Zone trên mặt bằng sảnh tiệc.

---

## 3. Bộ lọc phân vùng thời gian thực (Interactive Zone Switcher)

Ngay phía trên mặt bằng sảnh tiệc, hệ thống tự động sinh ra thanh điều hướng lọc Zone:

```
[ Tất cả bàn (30) ]  [ Họ Nhà Trai (15) ]  [ Họ Nhà Gái (12) ]  [ Bạn Bè (3) ]
```

- Khi nhấp chọn tab **[Họ Nhà Gái]**:
  - Màn hình Canvas sẽ làm mờ các bàn thuộc họ nhà trai và chỉ hiển thị nổi bật 12 bàn thuộc khu vực nhà gái.
  - Cột danh sách khách chưa xếp chỗ bên trái tự động lọc và ưu tiên đẩy các khách mời thuộc phân khúc họ nhà gái lên trên đầu.
- Khi nhấp chọn tab **[Tất cả bàn]**:
  - Toàn bộ sơ đồ đại sảnh được phục hồi hiển thị đầy đủ, giúp bạn có cái nhìn tổng thể về lối đi, khoảng cách giữa các bàn và sân khấu.

---

## 4. Quy tắc bài trí vị trí bàn tiệc theo nghi lễ truyền thống

Để hôn lễ diễn ra trang trọng, tôn nghiêm và đúng chuẩn mực phép tắc người Việt, bạn nên áp dụng thứ tự phân tầng Zone như sau:

| Vị trí Zone | Đối tượng khách mời | Loại bàn khuyến nghị | Khoảng cách đến sân khấu |
| :--- | :--- | :--- | :--- |
| **Zone 1: VIP Danh Dự** | Bố mẹ hai bên, ông bà, cô dì chú bác ruột thịt | Bàn tròn lớn (10 - 12 ghế), trải khăn hoa lụa cao cấp | Hàng đầu tiên, hai bên cánh gà đối xứng qua lối đi chính |
| **Zone 2: Họ Hàng Thân Tộc** | Anh chị em họ, họ hàng nội ngoại xa | Bàn tròn 10 ghế chuẩn | Hàng thứ 2 và 3 ngay sau bàn VIP |
| **Zone 3: Đối Tác & Khách VIP Bố Mẹ** | Lãnh đạo cơ quan của bố mẹ, đối tác kinh doanh quan trọng | Bàn tròn 10 ghế, không gian thoáng đãng | Khu vực trung tâm sảnh, âm thanh vừa phải để tiện trò chuyện |
| **Zone 4: Đồng Nghiệp & Công Ty** | Đồng nghiệp cô dâu & chú rể | Bàn tròn 10 ghế hoặc bàn dài liên hoàn | Khu vực giữa và sau sảnh tiệc |
| **Zone 5: Bạn Thân & Giới Trẻ** | Bạn đại học, bạn cấp 3, hội bạn thân | Bàn tròn / Bàn cocktail trẻ trung | Gần quầy bar, photobooth hoặc dàn loa âm thanh |
| **Zone 6: Bàn Dự Phòng (Buffer)** | Khách đi kèm phát sinh đột xuất | Bàn tròn 10 ghế chuẩn bị sẵn cỗ | Bố trí góc kín đáo gần cửa bếp nhà hàng |

---

## 5. Xuất bản sơ đồ phân bàn riêng biệt cho từng sự kiện (Print & PDF)

Khi nhà trai và nhà gái tổ chức ở hai nhà hàng khác nhau, bạn không thể đưa một bản in chung lẫn lộn cho đội ngũ điều phối tiệc. Mây Hẹn hỗ trợ xuất PDF theo từng Zone:

1. Nhấp nút **[🖨️ In Sơ Đồ]** trên thanh công cụ.
2. Trong hộp thoại tùy chọn in, tại mục **Chọn phân vùng muốn in**:
   - Chọn `Chỉ in Zone: Lễ Vu Quy (Nhà Gái)`: Hệ thống sẽ tạo file PDF chỉ gồm sơ đồ và danh sách khách của tiệc nhà gái để gửi cho nhà hàng bên ngoại.
   - Chọn `Chỉ in Zone: Lễ Thành Hôn (Nhà Trai)`: Hệ thống tạo file PDF danh sách khách nhà trai gửi cho bên nội.
   - Chọn `In Toàn Bộ Hội Trường`: Dành cho đám cưới gộp chung một đại sảnh.
3. Bản in kết xuất chuẩn khổ giấy A4 ngang, có sẵn logo Mây Hẹn, tiêu đề hôn lễ, ngày giờ và số điện thoại liên hệ của Trưởng ban đón tiếp.

---

## 6. Lưu ý an toàn & Mẹo thực chiến

> [!TIP]
> **Tách bạch nhóm khách "ngại ồn ào" và "thích tiệc tùng":**  
> Âm thanh tại các đám cưới hiện đại thường rất lớn ở gần sân khấu trong tiết mục âm nhạc. Hãy xếp người lớn tuổi (Zone Họ Hàng) ở các vị trí có âm lượng vừa phải, còn nhóm bạn trẻ (Zone Bạn Thân) có thể ngồi gần sân khấu hoặc lối đi để cùng cô dâu chú rể nhảy mở màn (First Dance) sôi động.

> [!WARNING]
> **Tránh đổi tên Zone tùy tiện sau khi đã in thiệp:**  
> Nếu bạn đã gửi thiệp mời điện tử kèm thông tin số bàn (ví dụ: *Bàn 05 - Khu Vu Quy*), hạn chế đổi tên phân vùng vào phút chót sát ngày cưới để khách mời không bị bỡ ngỡ khi bước vào sảnh tiệc.
