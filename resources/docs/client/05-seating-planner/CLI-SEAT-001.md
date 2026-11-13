---
id: CLI-SEAT-001
title: "Khởi tạo sơ đồ bàn tiệc: Đặt tên bàn, chọn sức chứa và điều phối chỗ ngồi thông minh"
category: "Sơ đồ Bàn tiệc & Chỗ ngồi"
summary: "Cẩm nang hướng dẫn toàn diện về Seating Planner Studio: Khởi tạo bàn tiệc tròn/dài/VIP, bố trí không gian sảnh cưới, kéo thả khách vào bàn và sử dụng Bác sĩ sức chứa để dự báo số lượng mâm cỗ đặt nhà hàng."
reader: "PER-CLI-02 (Cặp đôi đang quản lý tiệc)"
order: 6
updated_at: "2026-11-13"
tags: [mayhen, sơ-đồ-bàn-tiệc-&-chỗ-ngồi, seating-planner, capacity-guardian]
mapped_page: "CLI-SEAT-PAGE"
mapped_route: "seating.index"
risk_level: "HIGH"
required_role: "customer"
---

# Khởi tạo sơ đồ bàn tiệc: Đặt tên bàn, chọn sức chứa và điều phối chỗ ngồi thông minh

> **Dành cho:** Cô dâu & Chú rể quy hoạch chỗ ngồi cho khách mời sảnh cưới  
> **Thời gian thực hiện:** Khoảng 5 - 10 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sơ Đồ Xếp Bàn Tiệc** (Route: `seating.index`)  

Việc sắp xếp chỗ ngồi trong tiệc cưới của người Việt Nam không chỉ đơn thuần là phân bổ ghế ngồi, mà còn thể hiện sự trân trọng và tinh tế với họ hàng hai bên, bạn bè thời thơ ấu và đối tác quan trọng. **Seating Planner Studio** của Mây Hẹn mang đến trải nghiệm trực quan hóa mặt bằng sảnh tiệc, hỗ trợ kéo thả khách vào bàn và tích hợp **Bác Sĩ Sức Chứa (Seating Capacity Guardian)** để tính toán chính xác số mâm cỗ cần đặt với trung tâm tiệc cưới.

---

## 1. Bảng điều khiển 4 chỉ số vàng (Seating Metric Cards)

Ngay đầu màn hình Seating Planner, bạn sẽ theo dõi được 4 thông số quan trọng thời gian thực:
1. **Tổng số bàn tiệc:** Tổng số lượng bàn tiệc đã được khởi tạo trong sảnh tiệc.
2. **Sức chứa sảnh tiệc:** Tổng số ghế ngồi tối đa dự kiến của toàn bộ các bàn cộng lại.
3. **Đã có chỗ ngồi (Màu xanh lá):** Tổng số lượng khách mời (dựa trên headcount thực tế) đã được chỉ định vào bàn cụ thể.
4. **Chưa có bàn tiệc (Màu cam cảnh báo):** Số lượng khách đã xác nhận tham dự nhưng chưa được xếp chỗ, cần kéo thả vào bàn.

---

## 2. Bác Sĩ Bàn Tiệc & Dự Báo Sức Chứa (Seating Capacity Guardian)

Hệ thống tích hợp thuật toán phân tích thông minh độc quyền đạt chuẩn **TASK-VAL-22**, tự động chấm điểm độ hoàn thiện của sơ đồ bàn tiệc (thang điểm 100) và đưa ra các khuyến nghị thiết thực:

```
┌────────────────────────────────────────────────────────────────────────┐
│   🛡️ BÁC SĨ BÀN TIỆC & DỰ BÁO SỨC CHỨA (SEATING CAPACITY GUARDIAN)      │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  DỰ BÁO BÀN CHÍNH   │  BÀN DỰ PHÒNG (10%)│      TỶ LỆ LẤP ĐẦY          │
│     25 Bàn Chuẩn    │      +3 Bàn        │          92%                │
│  (10 khách / bàn)   │  (Khách phát sinh) │  (230 / 250 Ghế có chủ)     │
└─────────────────────┴────────────────────┴─────────────────────────────┘
```

- **Dự báo bàn chính thức:** Tính toán số lượng bàn cỗ thực tế cần đặt nhà hàng dựa trên số khách đã RSVP "Sẽ Tham Dự" (chuẩn 10 người/bàn tiệc truyền thống).
- **Bàn dự phòng an toàn (10% Buffer):** Tự động gợi ý số lượng bàn dự phòng cần chuẩn bị để đón khách phát sinh (+1 hoặc khách đi kèm) vào giờ đón khách.
- **Phát hiện bàn quá tải:** Tự động gắn cờ đỏ cảnh báo khi một bàn bị xếp vượt quá sức chứa tối đa (ví dụ bàn 10 ghế nhưng xếp 12 người).
- **Phát hiện bàn quá thưa:** Cảnh báo các bàn chỉ có 3 - 4 người ngồi để cặp đôi cân nhắc gộp bàn, tránh lãng phí chi phí cỗ cưới.

---

## 3. Quy trình 4 bước thiết lập sơ đồ bàn tiệc hoàn chỉnh

### Bước 1: Khởi tạo danh sách bàn tiệc
1. Nhấp nút **[+ Thêm Bàn Tiệc Mới]** màu vàng kim trên thanh công cụ.
2. Trong cửa sổ pop-up hiện ra, điền thông tin:
   - **Tên bàn:** Đặt tên gợi nhớ hoặc số thứ tự (ví dụ: *Bàn 01 - Họ Nhà Trai VIP, Bàn Bạn Đại Học, Bàn Đồng Nghiệp Cty ABC*).
   - **Khu vực sảnh (Zone):** Gần sân khấu, Giữa sảnh, Cạnh cửa ra vào...
   - **Loại bàn:** Bàn tròn truyền thống (Round Table) hoặc Bàn dài phong cách phương Tây (Long Table).
   - **Sức chứa chuẩn:** Mặc định 10 người (có thể chọn 6, 8, 10 hoặc 12 người tùy theo nhà hàng).
3. Nhấp **[Tạo Bàn Tiệc]**. Bàn tiệc mới sẽ xuất hiện trên mặt phẳng sảnh tiệc.

### Bước 2: Bố trí vị trí bàn trên bản đồ sảnh cưới (Canvas Drag)
1. Giữ chuột trái (hoặc chạm tay trên màn hình cảm ứng) vào tiêu đề của bàn tiệc.
2. Kéo di chuyển bàn đến vị trí tương ứng trên sơ đồ sảnh thực tế (ví dụ: Bàn bố mẹ ở gần lối đi sân khấu, bàn bạn bè ở phía sau).
3. Khi buông chuột, tọa độ vị trí sẽ được lưu tự động lên hệ thống thông qua `data-positions-url`.

### Bước 3: Phân bổ khách mời vào từng bàn tiệc
1. Quan sát cột **Danh Sách Khách Chưa Xếp Chỗ** ở cạnh bên.
2. Kéo tên từng khách mời và thả vào ô bàn tiệc mong muốn.
3. Khi một khách mời có đi kèm người thân (ví dụ: *Anh Hoàng & Bạn gái - 2 suất*), hệ thống sẽ tự động trừ chính xác 2 ghế trống của bàn tiệc đó.
4. Nếu muốn đổi bàn cho khách, chỉ cần kéo thẻ tên của khách từ bàn cũ sang bàn mới.

### Bước 4: Xuất sơ đồ và bảng phân bàn chuẩn in ấn A4 (Print PDF)
1. Sau khi hoàn tất việc xếp bàn, nhấp nút **[🖨️ In Sơ Đồ Sảnh Tiệc (PDF)]**.
2. Hệ thống sẽ kết xuất trang in A4 tối ưu bao gồm:
   - Bản vẽ tổng thể vị trí các bàn tiệc trong sảnh tiệc.
   - Bảng tra cứu danh sách khách mời xếp theo thứ tự bảng chữ cái A-Z kèm số bàn tương ứng.
3. Bạn có thể in ra giấy và gửi cho Trưởng ban tổ chức, Quản lý nhà hàng tiệc cưới và Lễ tân đón khách trước giờ G.

---

## 4. Đồng bộ dữ liệu xuyên suốt (Cross-Module Invariants)

> [!IMPORTANT]
> **Nhất quán giữa Tên bàn (`table_name`) và Khóa bàn (`table_id`):**  
> Theo chuẩn logic **BUG-AUDIT-03**, khi bạn tạo bàn tiệc trong Seating Planner, hệ thống sẽ tự động liên kết mã ID bàn vào thông tin của khách mời. Dù bạn sửa danh sách khách bên trang Khách mời hay điều chỉnh trên Sơ đồ bàn tiệc, dữ liệu luôn là một nguồn sự thật duy nhất (Single Source of Truth).

> [!NOTE]
> **Khách hủy tham dự (Declined RSVP):**  
> Khi một khách mời gửi phản hồi RSVP thông báo không thể tham dự, hệ thống sẽ tự động giải phóng ghế ngồi tại bàn tiệc để bạn nhường chỗ cho khách khác, đảm bảo tỷ lệ lấp đầy bàn tiệc luôn chính xác 100%.

