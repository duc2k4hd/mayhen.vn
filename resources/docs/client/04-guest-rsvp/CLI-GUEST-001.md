---
id: CLI-GUEST-001
title: "Tạo và quản lý danh sách khách mời theo nhóm (Bạn cô dâu, Bạn chú rể, Họ hàng, Đồng nghiệp)"
category: "Quản lý Khách mời & RSVP"
summary: "Hướng dẫn xây dựng danh sách khách mời chuẩn hóa trên Mây Hẹn: Phân loại 4 nhóm đối tượng, thiết lập danh xưng thân mật, quản lý sĩ số dự kiến và bộ đếm tự động phục vụ khâu đặt cỗ tiệc cưới."
reader: "PER-CLI-02 / PER-CLI-03"
order: 23
updated_at: "2026-11-15"
tags: [mayhen, quan-ly-khach-moi, danh-sach-khach, phan-nhom-khach, si-so-dat-co]
mapped_page: "CLI-GUEST-PAGE"
mapped_route: "guests.index"
risk_level: "HIGH"
required_role: "customer"
---

# Tạo và quản lý danh sách khách mời theo nhóm (Bạn cô dâu, Bạn chú rể, Họ hàng, Đồng nghiệp)

> **Dành cho:** Cô dâu & Chú rể lên kế hoạch danh sách khách mời, dự trù số lượng bàn tiệc và cá nhân hóa thiệp cưới  
> **Thời gian thực hiện:** Khoảng 3 - 5 phút cho việc làm quen và tạo nhóm  
> **Vị trí trong ứng dụng:** Dashboard Quản trị Thiệp → Menu **Quản Lý Khách Mời** (`/khach-moi` hoặc route `guests.index`)  

Danh sách khách mời là "xương sống" của toàn bộ công tác chuẩn bị đám cưới. Nếu danh sách không rõ ràng, hai bạn sẽ rất dễ gặp phải tình trạng: thiếu cỗ vào giờ cao điểm, thừa bàn tiệc gây lãng phí hàng chục triệu đồng, hoặc nhầm lẫn đại từ xưng hô khiến người lớn phật ý. Phân hệ **Quản Lý Khách Mời Thông Minh của Mây Hẹn** giúp hai bạn phân loại khoa học theo từng nhóm bạn, quản lý sĩ số chuẩn xác và tạo tiền đề để gửi thiệp cá nhân hóa 1-chạm.

---

## 1. Hệ thống 4 nhóm khách mời tiêu chuẩn (4 Core Segments)

Mây Hẹn chuẩn hóa sẵn 4 nhóm khách mời theo tập quán đám cưới Việt Nam:

```
┌────────────────────────────────────────────────────────────────────────┐
│             HỆ THỐNG PHÂN NHÓM KHÁCH MỜI MÂY HẸN                       │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  1. HỌ HÀNG HAI BÊN │  2. BẠN BÈ CHÚ RỂ  │  3. BẠN BÈ CÔ DÂU           │
│     (FAMILY & KIN)  │  (GROOM'S CIRCLE)  │  (BRIDE'S CIRCLE)           │
│                     │                    │                             │
│ • Bác, Chú, Cô, Dì  │ • Bạn cấp 3, ĐH    │ • Hội bạn thân, Bạn cũ      │
│ • Xưng hô trang trọng • Nhóm thể thao     │ • Hội chị em gái            │
│ • Ưu tiên bàn trung tâm • Không khí trẻ trung • Không khí ấm cúng     │
├─────────────────────┴────────────────────┴─────────────────────────────┤
│  4. ĐỒNG NGHIỆP & ĐỐI TÁC CÔNG SỞ (COLLEAGUES & PARTNERS)               │
│ • Đồng nghiệp công ty, Sếp, Đối tác kinh doanh                         │
│ • Cần thông tin sảnh tiệc, chỗ gửi xe và hóa đơn (nếu có)              │
└────────────────────────────────────────────────────────────────────────┘
```

> [!TIP]
> **Tạo thêm nhóm riêng tùy biến:**  
> Bạn hoàn toàn có thể tạo thêm các nhóm riêng biệt không giới hạn như: *Hội Bạn Du Học, Khách VIP Doanh Nhân, Hội Cầu Lông* để dễ dàng lọc danh sách khi gửi thiệp và sắp xếp bàn tiệc sau này.

---

## 2. Hướng dẫn từng bước thêm khách mời thủ công

Nếu hai bạn muốn thêm từng vị khách hoặc khách mời phát sinh:

### Bước 1: Mở giao diện Thêm Khách Mới
1. Truy cập vào trang **Quản Lý Khách Mời** của thiệp cưới.
2. Nhấp vào nút **[+ Thêm Khách Mới]** màu mận ở góc trên bên phải.
3. Hộp thoại thông tin khách mời xuất hiện.

### Bước 2: Điền thông tin cá nhân hóa
1. **Họ và tên khách (`name`):** Nhập họ tên đầy đủ (Ví dụ: `Nguyễn Văn Hoàng`).
2. **Đại từ xưng hô thiệp mời (`salutation`):**
   - Rất quan trọng! Đây là chữ sẽ xuất hiện trang trọng trên đầu thiệp cưới của khách.
   - *Gợi ý chuẩn mực:* `Anh Hoàng & Chị Mai`, `Gia đình Bác Bảy`, `Bạn thân mến`, `Em Hoàng`.
3. **Số điện thoại (`phone`):** Nhập số di động (Ví dụ: `0912345678`). Số điện thoại dùng để gửi link thiệp qua Zalo và nhận diện khi khách tự check-in tại lễ tân.
4. **Chọn Nhóm khách (`group_id`):** Chọn 1 trong các nhóm đã tạo (Ví dụ: *Bạn Chú Rể*).
5. **Thuộc phía gia đình (`side`):** Chọn *Nhà Trai*, *Nhà Gái* hoặc *Chung cả hai*.
6. **Số lượng khách dự kiến (`expected_pax`):**
   - Điền số người dự kiến sẽ đi cùng tấm thiệp này (Ví dụ: khách đi một mình ghi `1`, khách đi cùng người thương ghi `2`, cả gia đình ghi `3` hoặc `4`).
   - Con số này sẽ được hệ thống cộng dồn tự động vào **Bộ đếm tổng số bàn tiệc dự kiến**.
7. **Ghi chú riêng (`notes`):** Ghi lại thói quen đặc biệt (Ví dụ: *Ăn chay trường*, *Có em bé nhỏ cần ghế trẻ em*).

### Bước 3: Lưu và nhận Link thiệp cá nhân
- Nhấp **[Lưu Khách Mời]**.
- Hệ thống sẽ tạo ngay một mã định danh duy nhất (Guest Token) và tạo sẵn một đường link riêng biệt cho vị khách này.

---

## 3. Bảng điều khiển phân tích sĩ số thời gian thực (Real-time Metric Bar)

Ngay trên đầu màn hình Quản lý Khách mời, Mây Hẹn cung cấp 4 thẻ thống kê số liệu giúp hai bạn kiểm soát chặt chẽ ngân sách cỗ:

1. **Tổng số khách mời (Total Invited):** Tổng số lượng người trong danh sách (Ví dụ: `350 Khách`).
2. **Đã gửi thiệp (Sent):** Số lượng khách hai bạn đã bấm gửi link qua Zalo/SMS.
3. **Đã xác nhận tham dự (RSVP Confirmed):** Số lượng khách đã bấm "Chắc chắn sẽ đến" (Kèm tổng số người đi cùng thực tế).
4. **Số bàn tiệc ước tính (Estimated Tables):** Hệ thống tự động lấy tổng số người tham dự chia cho 10 (mâm 10 người), giúp hai bạn chốt số mâm cỗ chính xác với nhà hàng trước 3 ngày cưới.

---

## 4. Công cụ chống trùng lặp khách mời (Duplicate Detector)

Khi cả hai bên gia đình cùng lập danh sách, rất dễ xảy ra tình trạng: Chú rể đã thêm một người bạn đại học, nhưng Cô dâu hoặc Mẹ chú rể lại tiếp tục thêm người đó lần thứ hai.
- Mây Hẹn tích hợp **Bộ Quét Trùng Lặp Tự Động (Duplicate Detection)**.
- Khi bạn nhập một vị khách có **cùng số điện thoại** hoặc **trùng họ tên và nhóm bạn**, hệ thống sẽ lập tức hiện cảnh báo:  
  *"Phát hiện khách mời Nguyễn Văn Hoàng đã có trong danh sách nhóm Bạn Cấp 3. Bạn có muốn xem lại không?"*
- Tính năng này giúp loại trừ hoàn toàn việc một vị khách nhận 2 tin nhắn mời cưới từ hai nguồn khác nhau.

---

## 5. Bài viết liên quan tiếp theo

- [Hướng dẫn tải tệp mẫu Excel/CSV và nhập hàng loạt 500+ khách mời trong 1 phút](/docs/client/04-guest-rsvp/CLI-GUEST-002)
- [Cách tạo link thiệp cá nhân hóa hiển thị đúng danh xưng từng vị khách](/docs/client/04-guest-rsvp/CLI-GUEST-003)
- [RSVP theo hộ gia đình: Đăng ký số lượng người đi kèm và món ăn kiêng/dị ứng](/docs/client/04-guest-rsvp/CLI-GUEST-005)
