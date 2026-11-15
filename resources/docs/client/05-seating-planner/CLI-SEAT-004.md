---
id: CLI-SEAT-004
title: "Cảnh báo quá tải bàn tiệc (Capacity Guardian) và cách xếp bàn dự phòng"
category: "Sơ đồ Bàn tiệc & Chỗ ngồi"
summary: "Hướng dẫn kích hoạt và ứng dụng Bác Sĩ Bàn Tiệc (Capacity Guardian): Cơ chế chấm điểm sức khỏe sảnh tiệc 0-100, phát hiện bàn quá tải (Overfilled) và quá thưa (Underfilled), cảnh báo xung đột thế hệ và công thức tính bàn dự phòng an toàn 10% với nhà hàng."
reader: "PER-CLI-02 (Cặp đôi đang quản lý tiệc)"
order: 9
updated_at: "2026-11-15"
tags: [mayhen, sơ-đồ-bàn-tiệc-&-chỗ-ngồi, capacity-guardian, ban-du-phong, buffer-tables]
mapped_page: "CLI-SEAT-PAGE"
mapped_route: "seating.index"
risk_level: "HIGH"
required_role: "customer"
---

# Cảnh báo quá tải bàn tiệc (Capacity Guardian) và cách xếp bàn dự phòng

> **Dành cho:** Cô dâu & Chú rể chuẩn bị chốt số lượng mâm cỗ với trung tâm tiệc cưới  
> **Thời gian theo dõi:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sơ Đồ Xếp Bàn Tiệc** -> **Bác Sĩ Bàn Tiệc (Seating Capacity Guardian)** (Route: `seating.index`)  
> **Dịch vụ hậu thuẫn:** `App\Services\SeatingCapacityGuardianService`

Một trong những nỗi lo lớn nhất của các cặp đôi trong ngày cưới là **thừa mâm lãng phí** hoặc **thiếu chỗ ngồi làm khách phật lòng**. Đặt dư quá nhiều bàn cỗ sẽ khiến chi phí đám cưới đội lên hàng chục triệu đồng; ngược lại, nếu khách đi kèm ngoài dự kiến mà không có ghế ngồi, không khí ngày vui sẽ bị ảnh hưởng nghiêm trọng.

Hệ thống **Bác Sĩ Bàn Tiệc (Seating Capacity Guardian)** của Mây Hẹn hoạt động như một chuyên gia tư vấn tiệc cưới giàu kinh nghiệm, tự động phân tích toàn bộ sơ đồ sảnh, cảnh báo bàn quá tải, phát hiện bàn quá thưa và tính toán chính xác số bàn chính thức lẫn bàn dự phòng cần đặt với nhà hàng.

---

## 1. Cơ chế Chấm Điểm Sức Khỏe Bàn Tiệc (Health Score 0 - 100)

Tại thanh công cụ đầu màn hình Seating Planner, bạn sẽ thấy huy hiệu **Chỉ số Sức Khỏe Sơ Đồ** cùng nhãn đánh giá trạng thái:

```
┌────────────────────────────────────────────────────────────────────────┐
│  🛡️ CHỈ SỐ SỨC KHỎE SƠ ĐỒ BÀN TIỆC: 90/100 (Sơ đồ bàn tiệc hoàn hảo)   │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│   BÀN CHÍNH THỨC    │  BÀN DỰ PHÒNG (10%)│      TỶ LỆ LẤP ĐẦY          │
│       25 Bàn        │       +3 Bàn       │           94.2%             │
│  (Chốt đặt cỗ tiệc) │  (Khách phát sinh) │   (235 / 250 Ghế khả dụng)  │
└─────────────────────┴────────────────────┴─────────────────────────────┘
```

Thuật toán của Mây Hẹn tính toán điểm số minh bạch theo các tiêu chuẩn khắt khe:
- **Khởi điểm tối đa:** `100 điểm`.
- **Trừ 20 điểm / mỗi bàn quá tải:** Bàn có số khách thực tế lớn hơn sức chứa tối đa của bàn (`assigned > capacity`). Đây là lỗi nghiêm trọng nhất vì chắc chắn sẽ thiếu ghế khi khách ngồi vào.
- **Trừ 10 điểm / mỗi bàn quá thưa:** Bàn chỉ có dưới 50% số ghế có người ngồi (`assigned < capacity * 0.5`). Gây lãng phí chi phí thuê mặt bằng và tiền cỗ nguyên mâm.
- **Trừ điểm khách chưa xếp chỗ:** Trừ tối đa `30 điểm` tương ứng với số lượng khách đã RSVP "Sẽ Tham Dự" nhưng bạn chưa xếp vào bất kỳ bàn nào (`ceil(unassigned / 2)`).

### Phân loại trạng thái tổng thể:
- **Từ 85 - 100 điểm:** `Sơ đồ bàn tiệc hoàn hảo` (Xanh ngọc - An tâm in ấn và gửi nhà hàng).
- **Từ 60 - 84 điểm:** `Khá tốt, cần tinh chỉnh nhẹ` (Vàng kim - Cần gộp bàn hoặc xếp nốt khách lẻ).
- **Dưới 60 điểm:** `Cần xử lý quá tải / thiếu bàn gấp` (Đỏ rượu - Nguy cơ vỡ kế hoạch tiếp đón).

---

## 2. Ba cấp độ cảnh báo thông minh (Issue Diagnostic)

Khi nhấp vào biểu tượng **[Chẩn đoán chi tiết]**, bảng điều khiển sẽ phân loại các vấn đề theo 3 mức độ nghiêm trọng:

```
[CRITICAL]  Bàn "VIP 02 - Họ Nhà Gái" đang bị quá tải (12/10 ghế, thừa 2 người).
            -> Gợi ý: Chuyển bớt 2 khách sang Bàn VIP 03 hoặc yêu cầu nhà hàng kê thêm ghế phụ.

[WARNING]   Bàn "Bạn Cấp 2 Tân Kỳ" xếp quá thưa (mới có 4/10 ghế).
            -> Gợi ý: Nên ghép chung với Bàn "Bạn Cấp 3" để tạo không khí đông vui và tiết kiệm mâm.

[INFO]      Bàn "Bàn Số 08" đang ghép chung nhóm Người Lớn Tuổi với nhóm Bạn Trẻ Quẩy.
            -> Gợi ý: Cân nhắc tách riêng để các bác lớn tuổi không bị ảnh hưởng bởi tiếng ồn.
```

### 1. Cảnh báo Bàn Quá Tải (Overfilled - Thẻ Đỏ Critical)
- Xuất hiện khi một gia đình có trẻ em hoặc người đi kèm (+1, +2) được kéo vào bàn đã gần đầy.
- **Giải pháp:** Hệ thống cho phép bạn nhấp nút **[Tăng sức chứa]** lên 11 hoặc 12 ghế (nếu nhà hàng hỗ trợ kê thêm đôn/ghế phụ), hoặc kéo bớt khách sang bàn bên cạnh.

### 2. Cảnh báo Bàn Quá Thưa (Underfilled - Thẻ Vàng Warning)
- Thường xảy ra khi một nhóm bạn chỉ có 3 - 4 người xác nhận tham dự.
- **Giải pháp:** Sử dụng tính năng kéo thả để gom hai nhóm bạn có độ tuổi tương đồng vào cùng một bàn 10 người.

### 3. Cảnh báo Lệch Pha Thế Hệ (Mixed Generations - Thẻ Xanh Info)
- Tính năng độc quyền của Mây Hẹn: Tự động quét từ khóa nhóm khách (`Họ Hàng`, `Gia Đình`, `Bác`, `Chú`) ghép chung với nhóm (`Bạn Cấp 3`, `Đại Học`, `Đồng Nghiệp`, `Hội Phượt`).
- Giúp bạn tránh được tình huống tế nhị khi các cụ lớn tuổi phải ngồi chung bàn với nhóm bạn trẻ nâng ly reo hò ồn ào.

---

## 3. Công thức tính Bàn Dự Phòng (10% Buffer Rule)

Theo kinh nghiệm điều phối hàng ngàn tiệc cưới Việt Nam, tỷ lệ khách mời dắt theo người yêu, bạn đời hoặc con nhỏ mà quên không đăng ký trước thường dao động từ **8% đến 12%**.

Seating Capacity Guardian tự động áp dụng công thức tiêu chuẩn:

$$\text{Bàn Chính Thức} = \left\lceil \frac{\text{Tổng khách tham dự}}{10} \right\rceil$$

$$\text{Bàn Dự Phòng (Buffer)} = \max\left(1, \left\lceil \text{Bàn Chính Thức} \times 0.1 \right\rceil\right)$$

- **Ví dụ cụ thể:**
  - Bạn có `235 khách` xác nhận tham dự.
  - Bàn chính thức = $\lceil 235 / 10 \rceil = \mathbf{24\text{ bàn}}$.
  - Bàn dự phòng an toàn (10%) = $\lceil 24 \times 0.1 \rceil = \mathbf{3\text{ bàn dự phòng}}$.
  - **Kế hoạch đặt nhà hàng:** Đặt chính thức 24 mâm cỗ cứng + thỏa thuận 3 mâm dự phòng (chỉ tính tiền thực tế nếu mở bàn phục vụ).

---

## 4. Cách thiết lập Bàn Dự Phòng trên giao diện Seating Planner

1. Nhấp **[+ Thêm Bàn Tiệc]**.
2. Đặt tên bàn rõ ràng: `Bàn Dự Phòng 01 (Buffer)`, `Bàn Dự Phòng 02 (Khách Phát Sinh)`.
3. Gán phân vùng (Zone): `Dự Phòng Nhà Trai` hoặc `Dự Phòng Nhà Gái`.
4. Để trống danh sách khách mời trong bàn này (`0/10 ghế`).
5. Kéo bàn dự phòng đặt ở góc sảnh gần lối ra vào của nhân viên phục vụ tiệc. Khi lễ tân đón khách phát hiện khách phát sinh vào giờ G, lễ tân chỉ cần dẫn khách vào các bàn dự phòng này mà không làm xáo trộn các bàn đã chỉ định tên từ trước.

---

## 5. Danh mục kiểm tra trước giờ chốt cỗ (Checklist)

- [ ] Điểm số sức khỏe sơ đồ đạt từ **85 điểm trở lên**.
- [ ] Số bàn quá tải bằng **0**.
- [ ] Toàn bộ khách đã RSVP "Sẽ Tham Dự" đều đã có vị trí bàn cụ thể (`Chưa xếp bàn = 0`).
- [ ] Đã tạo tối thiểu từ 1 đến 3 bàn dự phòng theo khuyến nghị của hệ thống.
- [ ] Đã xuất file PDF sơ đồ sảnh và gửi cho Quản lý nhà hàng tiệc cưới duyệt trước 48 tiếng.
