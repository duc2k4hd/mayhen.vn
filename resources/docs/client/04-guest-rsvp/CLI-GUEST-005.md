---
id: CLI-GUEST-005
title: "RSVP theo hộ gia đình: Đăng ký số lượng người đi kèm và món ăn kiêng/dị ứng"
category: "Quản lý Khách mời & RSVP"
summary: "Hướng dẫn cấu hình biểu mẫu RSVP theo hộ gia đình (Household RSVP): Đăng ký số người lớn, trẻ em, ghi nhận các thói quen ăn chay, dị ứng thực phẩm và xuất báo cáo ẩm thực chuyên nghiệp gửi bếp tiệc cưới."
reader: "PER-CLI-02 / PER-CLI-03"
order: 26
updated_at: "2026-11-15"
tags: [mayhen, quan-ly-khach-moi, rsvp-ho-gia-dinh, thuc-don-chay, di-ung-thuc-pham, bao-cao-bep-tiec]
mapped_page: "CLI-GUEST-PAGE"
mapped_route: "guests.index"
risk_level: "HIGH"
required_role: "customer"
---

# RSVP theo hộ gia đình: Đăng ký số lượng người đi kèm và món ăn kiêng/dị ứng

> **Dành cho:** Cô dâu & Chú rể muốn chu toàn từng bữa ăn của khách, thấu hiểu khách ăn chay, khách dị ứng và gia đình có con nhỏ  
> **Thời gian thiết lập:** Khoảng 1 - 2 phút trong Studio hoặc Dashboard Khách Mời  
> **Vị trí trong ứng dụng:** Quản lý Khách Mời → Cài đặt biểu mẫu RSVP hoặc Section 10 trên thiệp  

Một bữa tiệc cưới đẳng cấp và ấm áp được thể hiện qua sự chu đáo đối với từng khẩu vị của quan khách. Sẽ thật áy náy nếu một người bác lớn tuổi ăn chay trường phải ngồi trước một bàn tiệc toàn sơn hào hải vị, hay một người bạn bị dị ứng đậu phộng không thể an tâm dùng bữa. Tính năng **RSVP Theo Hộ Gia Đình & Tùy Chọn Ẩm Thực (Household RSVP & Dietary Care)** của Mây Hẹn giúp hai bạn thu thập chính xác số người đi kèm và nhu cầu món ăn đặc biệt để gửi bếp tiệc nhà hàng chuẩn bị chu đáo nhất.

---

## 1. Cấu trúc biểu mẫu RSVP theo Hộ Gia Đình

Khi khách mời mở phần **Xác Nhận Tham Dự (RSVP)** trên thiệp cưới:

```
┌────────────────────────────────────────────────────────┐
│         BIỂU MẪU XÁC NHẬN THAM DỰ (HOUSEHOLD RSVP)     │
├────────────────────────────────────────────────────────┤
│  KÍNH MỜI: GIA ĐÌNH BÁC BẢY                            │
│                                                        │
│  1. Bác có thể tham dự chung vui cùng chúng cháu?      │
│     (●) Chắc chắn sẽ đến     (○) Rất tiếc không thể đến│
│                                                        │
│  2. Số lượng người trong gia đình tham dự:             │
│     • Người lớn : [ 2 ] người                          │
│     • Trẻ em    : [ 1 ] bé (cần 1 ghế trẻ em 👶)       │
│                                                        │
│  3. Thói quen ẩm thực & Lưu ý đặc biệt:                │
│     [✓] Ăn chay trường (Vegetarian)                    │
│     [ ] Dị ứng hải sản (Tôm, cua...)                   │
│     [ ] Dị ứng các loại hạt                            │
│     [✓] Ghi chú khác: "Bác gái ăn chay, 2 người ăn mặn"│
│                                                        │
│             [ GỬI XÁC NHẬN THAM DỰ ]                   │
└────────────────────────────────────────────────────────┘
```

---

## 2. Các bước cấu hình tính năng trong hệ thống Mây Hẹn

### Bước 1: Kích hoạt các trường khảo sát ẩm thực
1. Tại màn hình **Quản Lý Khách Mời**, nhấp vào nút **[Cài Đặt Biểu Mẫu RSVP]**.
2. Bật các tùy chọn khảo sát phù hợp với gói tiệc của bạn:
   - **Tách riêng số lượng Người lớn & Trẻ em:** Giúp nhà hàng tính đúng số lượng suất ăn chính và số ghế phụ cho em bé.
   - **Khảo sát món ăn kiêng & Dị ứng (Dietary Preferences):** Tự động hiển thị các nút chọn nhanh: *Ăn chay, Dị ứng hải sản, Dị ứng đậu phộng, Dị ứng sữa/gluten*.
   - **Yêu cầu ghế trẻ em (High Chair):** Rất hữu ích cho các gia đình có con nhỏ dưới 3 tuổi.

### Bước 2: Thiết lập hạn mức số người tối đa (+1 Policy)
Để không bị vượt quá sức chứa sảnh tiệc:
1. Trong danh sách khách mời, bạn có thể đặt cột `max_attendees` cho từng vị khách:
   - Khách bạn bè đi một mình: Đặt giới hạn `1 người`.
   - Khách mời kèm người thương: Đặt giới hạn `2 người`.
   - Khách hộ gia đình: Đặt giới hạn `3` hoặc `4 người`.
2. Trên màn hình thiệp của khách, danh sách chọn số người tham dự sẽ chỉ hiển thị tối đa đúng con số bạn cho phép, ngăn chặn tình trạng khách chọn quá số lượng vé mời.

---

## 3. Báo cáo ẩm thực tự động gửi Bếp tiệc (Kitchen Dietary Summary)

Trước ngày cưới từ 3 đến 5 ngày, khi chốt số lượng món ăn với Quản lý Nhà hàng hoặc Bếp trưởng:
1. Vào trang **Quản Lý Khách Mời** → Nhấp nút **[📊 Báo Cáo Ẩm Thực]**.
2. Hệ thống Mây Hẹn sẽ tự động tổng hợp một bảng báo cáo xúc tích, phân loại khoa học:
   - **Tổng số khách xác nhận tham dự:** Ví dụ `280 Khách` (255 Người lớn, 25 Trẻ em).
   - **Số suất cỗ chay đặc biệt:** Ví dụ `12 Suất` (Bàn số 04 có 6 suất, Bàn số 09 có 6 suất).
   - **Danh sách dị ứng nghiêm trọng:** Ví dụ `3 Khách` dị ứng tôm cua (ghi rõ tên khách và số bàn tiệc).
   - **Số ghế trẻ em cần kê thêm:** Ví dụ `8 Ghế`.
3. Bạn có thể in file PDF hoặc xuất file Excel gửi trực tiếp qua Zalo/Email cho Quản lý Trung tâm Tiệc cưới.

---

## 4. Những điều cặp đôi cần lưu ý

> [!TIP]
> **Nhắc nhở khách ăn chay ngồi cùng bàn:**  
> Khi xếp sơ đồ bàn tiệc (Seating Planner), hãy gom những vị khách có cùng nhu cầu ăn chay vào chung 1 hoặc 2 bàn tiệc chuyên biệt. Nhà hàng sẽ phục vụ một mâm cỗ chay trọn vẹn, ấm cúng và thuận tiện hơn rất nhiều so với việc phục vụ từng đĩa chay rải rác ở khắp các bàn ăn mặn.

> [!NOTE]
> **Trường hợp khách thay đổi ý định phút chót:**  
> Nếu một vị khách bận việc đột xuất và muốn sửa lại số người tham dự từ 2 xuống 1: họ chỉ cần mở lại link thiệp cưới và bấm cập nhật lại biểu mẫu RSVP. Hệ thống Mây Hẹn sẽ ghi nhận phiên bản mới nhất và tự động trừ sĩ số trên bảng điều khiển của bạn.

---

## 5. Bài viết liên quan tiếp theo

- [Theo dõi sĩ số khách phản hồi thời gian thực và quản lý lịch sử thay đổi RSVP](/docs/client/04-guest-rsvp/CLI-GUEST-006)
- [Thiết lập kế hoạch nhắc hẹn lịch sự (Gentle Reminder) gửi trước ngày cưới](/docs/client/04-guest-rsvp/CLI-GUEST-007)
- [Sắp xếp sơ đồ bàn tiệc và chỗ ngồi thông minh](/docs/client/05-seating-planner/CLI-SEAT-001)
