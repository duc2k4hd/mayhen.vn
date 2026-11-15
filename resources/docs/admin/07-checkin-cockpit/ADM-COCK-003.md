---
id: ADM-COCK-003
title: "Hỗ trợ cô dâu chú rể giải quyết xung đột sức chứa bàn tiệc trong giờ cao điểm"
category: "Check-in Cockpit & Sơ đồ bàn"
summary: "Quy trình chuẩn hóa xử lý xung đột sức chứa bàn tiệc trong khung giờ cao điểm đón khách (18:00 - 18:45): chẩn đoán bàn quá tải, điều phối bàn đệm lân cận, kích hoạt bàn dự phòng và giao tiếp tinh tế với quan khách."
reader: "PER-ADM-01 (Điều hành tiệc cưới & Quản lý sảnh)"
order: 3
updated_at: "2026-11-15"
tags: [admin, table-conflict, capacity-wave, rush-hour, buffer-table]
mapped_page: "ADM-COCK-PAGE"
mapped_route: "admin.checkin.cockpit"
risk_level: "CRITICAL"
required_role: "manager"
---

# Hỗ trợ cô dâu chú rể giải quyết xung đột sức chứa bàn tiệc trong giờ cao điểm

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `CRITICAL` | **Endpoint:** `POST /admin/checkin/guests/{guestId}/update-table`  
> **Thời điểm vàng xử lý:** Khung giờ cao điểm đón tiếp (18:00 - 18:45). Mọi quyết định điều phối bàn ghế phải được hoàn thành trong vòng **60 đến 90 giây** để tránh gây ùn tắc tại sảnh tiệc.

---

## 1. Các Nguyên nhân Gây Xung đột Sức chứa Giờ Cao điểm

Trong thực tế tổ chức đám cưới tại Việt Nam, khung giờ từ **18:00 đến 18:45** là lúc 70% quan khách cùng đổ dồn về sảnh tiệc. Xung đột sức chứa bàn tiệc thường phát sinh từ 3 kịch bản phổ biến:

```
[ KỊCH BẢN XUNG ĐỘT SỨC CHỨA BÀN TIỆC PHỔ BIẾN ]

1. Dắt thêm người không đăng ký (+1, +2):
   Khách xác nhận 1 người nhưng dẫn theo bạn gái hoặc con nhỏ -> Bàn 10 chỗ bị đội lên 12-13 khách.

2. Ngồi chéo nhóm bạn bè tự phát:
   Khách nhóm "Bạn Cấp 3" nhìn thấy bạn thân ở nhóm "Đồng nghiệp Cũ" -> Tự ý kéo sang ngồi cùng khiến bàn bị tràn.

3. Khách VIP / Người thân đến muộn:
   Bàn danh dự VIP đã bị khách khác ngồi nhầm chỗ -> Khách quan trọng đến nơi không còn ghế trống.
```

---

## 2. Dấu hiệu Nhận biết Bàn Quá tải trên Cockpit

Trên màn hình **Admin Check-in Cockpit** (`/admin/checkin/cockpit`), danh sách bàn tiệc thời gian thực được hệ thống tính toán liên tục qua service `AdminCheckInCockpitService`:

* **Cờ hiệu cảnh báo đỏ rực (`is_over_capacity = true`):** Khi tổng số người thực tế có mặt (`occupied_seats`) vượt quá sức chứa thiết kế (`capacity`).  
  *Ví dụ:* `Bàn 07 - Bạn Đại học: 12/10 chỗ (VƯỢT 2 GHẾ)`.
* **Ghế trống âm (`available_seats = 0`):** Nút xếp thêm khách vào bàn này bị khóa tự động trên giao diện Lễ tân để ngăn tình trạng dồn khách tiếp diễn.
* **Tỷ lệ lấp đầy phân khu (`Zone Occupancy`):** Hiển thị tổng quan phân khu (Nhà Trai, Nhà Gái, Đồng Nghiệp) để người quản lý nhận biết phân khu nào còn bàn trống lân cận.

---

## 3. Quy trình 4 Bước Xử lý Xung đột Bàn tiệc Chuẩn SOP

Khi phát hiện bàn tiệc bị quá tải trên Cockpit, Điều phối viên sảnh thực hiện tuần tự theo quy trình 4 bước:

### Bước 1: Quét Phân khu & Định vị Bàn đệm Lân cận (Neighbor Buffer Table)
1. Trên Cockpit, lọc danh sách bàn tiệc theo cùng **Khu vực (`zone`)** của bàn bị quá tải (ví dụ: *Khu Bạn Bè - Tầng 1*).
2. Tìm kiếm các bàn tiệc có cùng tính chất quan hệ xã hội nhưng đang còn ghế trống (`available_seats >= 2`).
   - *Ví dụ:* Bàn 07 (Bạn ĐH Khoa Luật) đang bị 12/10 chỗ; Bàn 08 (Bạn ĐH Khoa Kinh Tế) đang 7/10 chỗ (còn 3 ghế trống).

### Bước 2: Kích hoạt Bàn tiệc Dự phòng (Buffer Table Activation)
Nếu tất cả các bàn trong cùng phân khu đều đã đầy (>= 10 chỗ):
1. Quản lý sảnh kích hoạt **Bàn tiệc Dự phòng (Floating / Reserve Table)** đã được thỏa thuận trước với nhà hàng (thường mỗi tiệc 300 khách luôn bố trí sẵn 2 bàn dự phòng chưa lên món).
2. Trên Cockpit, xác định mã bàn dự phòng (ví dụ: `Bàn DP-01` hoặc `Bàn 28`).
3. Thông báo cho Đội phục vụ sảnh (F&B Waiters) bật đèn bàn và đặt khăn lạnh, đồ uống khai vị sẵn sàng.

### Bước 3: Điều chuyển Khách trên Hệ thống Cockpit (Fast Reassignment)
Thực hiện chuyển bàn ngay trên Cockpit bằng API điều phối:
1. Nhập tên khách cần chuyển vào ô tìm kiếm nhanh.
2. Nhấp nút **[Đổi bàn]** cạnh tên khách.
3. Chọn bàn tiệc mới (`table_id`) từ danh sách bàn còn chỗ.
4. Nhấp **[Cập nhật bàn]**. Endpoint `POST /admin/checkin/guests/{guestId}/update-table` thực thi ngay lập tức:
   - Sĩ số bàn cũ giảm xuống hợp lệ (về 10/10 chỗ).
   - Sĩ số bàn mới tăng tương ứng.
   - Màn hình tra cứu **Table Finder** và **Lễ tân QR** tự động nhận bàn mới nếu khách mở lại link thiệp trên điện thoại.

### Bước 4: Kê thêm Ghế phụ (Auxiliary Stools - Phương án Cuối cùng)
Nếu nhóm khách nhất quyết muốn ngồi cùng nhau (ví dụ: gia đình có 2 em bé nhỏ không chịu tách rời):
1. Điều phối viên sảnh ra hiệu cho Trưởng nhóm Phục vụ kê thêm tối đa **1 đến 2 ghế đôn phụ (ghế không tay vịn)** vào bàn tròn.
2. Ghi chú vào Cockpit: `[KÊ 1 GHẾ PHỤ - EM BÉ]`.
3. Lưu ý: Tuyệt đối không kê quá 12 ghế đối với bàn tròn đường kính tiêu chuẩn 1.8m để tránh làm vướng lối đi của nhân viên lên món súp nóng.

---

## 4. Nghệ thuật Giao tiếp Tinh tế với Khách mời (Quiet Luxury Etiquette)

Trong không khí trang trọng của tiệc cưới, việc yêu cầu khách chuyển bàn nếu không khéo léo sẽ dễ khiến khách mời cảm thấy bị làm phiền hoặc bị phân biệt đối xử. Nhân viên Mây Hẹn luôn tuân thủ nguyên tắc **3 Không - 3 Nên**:

```
[ QUY TẮC ỨNG XỬ KHI ĐIỀU PHỐI BÀN TIỆC ]

3 KHÔNG (TUYỆT ĐỐI TRÁNH):
- KHÔNG nói lớn tiếng: "Bàn này đông quá rồi, mời anh/chị sang bàn khác!"
- KHÔNG hỏi dồn dập: "Ai là người đi kèm không đăng ký trước?"
- KHÔNG yêu cầu khách đứng dậy khi khách đã bắt đầu dùng món ăn.

3 NÊN (CHUẨN QUIET LUXURY):
- NÊN mỉm cười, tiến lại gần và nói chuyện ở cao độ vừa đủ nghe.
- NÊN giải thích theo hướng chăm sóc sự thoải mái của khách:
  "Dạ em chào anh Nam, để nhóm mình có không gian trò chuyện thoải mái nhất và không bị chật chội,
   cô dâu Thảo Linh có chuẩn bị thêm Bàn số 08 ngay cạnh bên rất thoáng đãng..."
- NÊN chủ động mang đồ uống khai vị hoặc quà cảm ơn sang bàn mới cho khách.
```

---

## 5. Bảng Tóm tắt Kịch bản Phản ứng Nhanh

| Tình huống thực tế | Mức độ nghiêm trọng | Hành động Cockpit | Phối hợp Hiện trường |
| :--- | :--- | :--- | :--- |
| **Bàn thừa 1 khách (+1 đi cùng)** | Trung bình (Vàng) | Kiểm tra bàn kế bên, nếu không có ai tách thì cho kê thêm 1 ghế phụ. | Kê ghế đôn cùng màu khăn trải bàn. |
| **Bàn thừa từ 3 đến 5 khách** | Cao (Đỏ) | Mở bàn tiệc dự phòng mới (`Reserve Table`), chuyển cả nhóm 3-5 khách sang bàn mới. | Nhân viên dẫn đường đưa khách đến tận bàn mới. |
| **Khách VIP bị ngồi nhầm chỗ** | Rất cao (Tím) | Tìm khách ngồi nhầm (thường là bạn bè), tế nhị mời sang bàn bạn bè phù hợp hơn. | Mời khách sang bàn đúng hội bạn thân, giữ trọn vẹn vị trí VIP cho gia đình. |

---

## 6. Lưu vết Kiểm toán Vận hành (Audit Compliance)

Mọi thao tác thay đổi bàn tiệc trong giờ cao điểm đều tự động tạo một bản ghi trong bảng `audit_logs` với các thông tin bắt buộc:
* `user_id`: ID tài khoản Quản trị viên thực hiện.
* `action`: `GUEST_TABLE_REASSIGNED`.
* `payload`: `{ guest_id: 1042, old_table_id: 7, new_table_id: 8, reason: "OVER_CAPACITY_RUSH_HOUR", operator: "Nguyễn Văn Tuấn" }`.
* `timestamp`: Thời gian chính xác đến từng giây phục vụ đối soát dịch vụ với gia đình cô dâu chú rể sau tiệc.
