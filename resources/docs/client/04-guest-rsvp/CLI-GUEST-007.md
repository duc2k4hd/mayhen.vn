---
id: CLI-GUEST-007
title: "Thiết lập kế hoạch nhắc hẹn lịch sự (Gentle Reminder) gửi trước ngày cưới"
category: "Quản lý Khách mời & RSVP"
summary: "Cẩm nang giao tiếp cưới tinh tế: Lộ trình 3 mốc nhắc hẹn vàng (14 ngày, 3 ngày và sáng ngày cưới), bộ mẫu tin nhắn Gentle Reminder theo từng nhóm đối tượng và công cụ lọc an toàn PreSendValidation."
reader: "PER-CLI-02 / PER-CLI-03"
order: 28
updated_at: "2026-11-15"
tags: [mayhen, quan-ly-khach-moi, nhac-hen-cuoi, gentle-reminder, giao-tiep-tinh-te, presend-validation]
mapped_page: "CLI-GUEST-PAGE"
mapped_route: "guests.index"
risk_level: "HIGH"
required_role: "customer"
---

# Thiết lập kế hoạch nhắc hẹn lịch sự (Gentle Reminder) gửi trước ngày cưới

> **Dành cho:** Cô dâu & Chú rể muốn nhắc khách về ngày cưới một cách tinh tế, ấm áp mà không tạo cảm giác thúc giục hay phiền hà  
> **Thời gian áp dụng:** Xuyên suốt 2 tuần trước ngày cưới  
> **Vị trí trong ứng dụng:** Quản Lý Khách Mời → Công cụ **Lập Kế Hoạch Nhắc Hẹn (Gentle Reminder Planner)**  

Gửi lời nhắc đám cưới là một nghệ thuật giao tiếp vô cùng nhạy cảm. Nếu hai bạn nhắn tin quá dồn dập hoặc lời lẽ khô cứng, khách mời có thể cảm thấy bị áp lực hoặc phiền toái. Ngược lại, nếu không nhắc hẹn, khách rất dễ vô tình bị cuốn vào công việc hàng ngày và lỡ hẹn với hai bạn. Mây Hẹn thiết kế riêng tính năng **Gentle Reminder Planner**, cung cấp lộ trình 3 mốc thời gian vàng cùng bộ văn mẫu trang trọng, giúp hai bạn gửi gắm sự chăm sóc chu đáo đến từng vị khách.

---

## 1. Lộ trình 3 mốc thời gian nhắc hẹn vàng (3-Stage Golden Cadence)

```
┌────────────────────────────────────────────────────────────────────────┐
│             LỘ TRÌNH 3 MỐC NHẮC HẸN VÀNG CỦA MÂY HẸN                   │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│ MỐC 1: TRƯỚC 14 NGÀY│ MỐC 2: TRƯỚC 3 NGÀY│ MỐC 3: SÁNG NGÀY CƯỚI       │
│  (HẠN CHỐT RSVP MỀM)│  (DRESSCODE & SẢNH)│  (LỜI HẸN CHÀO ĐÓN)         │
│                     │                    │                             │
│ • Chỉ gửi cho khách │ • Gửi cho khách đã │ • Gửi tin nhắn chào mừng    │
│   chưa bấm phản hồi │   xác nhận tham dự │   ấm áp buổi sáng           │
│ • Thăm dò tế nhị để │ • Nhắc tone màu áo │ • Kèm link bản đồ dẫn đường │
│   chốt sĩ số tiệc   │   và sảnh nhà hàng │   Google Maps 1-chạm        │
└─────────────────────┴────────────────────┴─────────────────────────────┘
```

---

## 2. Bộ văn mẫu nhắc hẹn tinh tế theo từng nhóm đối tượng

Mây Hẹn chuẩn bị sẵn các mẫu tin nhắn phù hợp với từng đại từ xưng hô, bạn chỉ cần chọn và nhấp nút gửi Zalo:

### Mẫu 1: Dành cho Họ hàng & Người lớn tuổi (Kính trọng, lễ phép)
> *"Dạ chúng cháu Tuấn Minh & Lan Anh xin phép kính gửi lời thăm hỏi đến Bác Bảy và gia đình. Ngày cưới của chúng cháu đang đến gần vào Chủ Nhật tuần sau, gia đình chúng cháu rất mong được đón tiếp Bác và gia đình đến chung vui. Bác có thể xem lại thông tin sảnh tiệc và bản đồ tại: {link_thiep}"*

### Mẫu 2: Dành cho Bạn bè thân thiết (Vui tươi, ấm áp)
> *"Ê bạn ơi, chỉ còn 3 ngày nữa là đến ngày vui của hai đứa mình rồi! Nhớ để dành bụng ăn cỗ và chọn trang phục theo tone màu be/hồng pastel xinh xắn nhé. Hẹn gặp bạn lúc 17:30 tại Sảnh Diamond nha: {link_thiep}"*

### Mẫu 3: Dành cho Đồng nghiệp & Đối tác (Lịch sự, trang trọng)
> *"Kính gửi Anh Hoàng, Tuấn Minh xin trân trọng nhắc nhẹ về buổi tiệc cưới thân mật vào 18:00 tối thứ Bảy này tại Trung tâm The Adora Dynasty. Sự hiện diện của anh là niềm vinh dự lớn của gia đình em. Anh có thể xem sơ đồ bãi đỗ xe và chỉ đường tại đây ạ: {link_thiep}"*

---

## 3. Bộ lọc kiểm tra an toàn trước khi gửi (Pre-Send Validation)

Trước khi bạn bấm gửi tin nhắn hàng loạt hoặc gửi từng nhóm, dịch vụ bảo vệ `PreSendValidationService` của Mây Hẹn sẽ tự động quét toàn bộ danh sách để loại trừ rủi ro:

1. **Loại trừ tuyệt đối khách đã báo bận (`Declined`):**  
   Hệ thống tự động bỏ qua những vị khách đã phản hồi "Rất tiếc không thể đến". Điều này tránh việc gửi tin nhắn nhắc nhở gây khó xử hoặc thiếu tinh tế cho những người đang có việc bận gia đình.
2. **Khống chế tần suất gửi (Anti-Spam Frequency Cap):**  
   Hệ thống ngăn chặn việc gửi 2 tin nhắn nhắc hẹn cho cùng một vị khách trong vòng 24 giờ, tránh làm phiền khách mời.
3. **Kiểm tra tính hợp lệ của số điện thoại và đường link:**  
   Bảo đảm mỗi tin nhắn đều chứa đúng tên và mã `guest_token` riêng biệt của vị khách đó.

---

## 4. Hướng dẫn sử dụng công cụ nhắc hẹn trong Dashboard

1. Mở trang **Quản Lý Khách Mời** (`/khach-moi`).
2. Nhấp vào nút **[💌 Lập Kế Hoạch Nhắc Hẹn]**.
3. Chọn mốc thời gian bạn muốn thực hiện (Ví dụ: *Trước ngày cưới 3 ngày*).
4. Hệ thống sẽ tự động lọc ra danh sách những khách phù hợp với mốc này.
5. Xem lại nội dung tin nhắn mẫu và bấm **[Gửi Zalo Nhắc Hẹn]** cho từng khách.
6. Cột lịch sử sẽ tự động ghi nhận: *"Đã gửi nhắc hẹn Mốc 2 lúc 10:15 ngày 25/12"*.

---

## 5. Bài viết liên quan tiếp theo

- [Sắp xếp sơ đồ bàn tiệc và chỗ ngồi thông minh](/docs/client/05-seating-planner/CLI-SEAT-001)
- [Hướng dẫn lễ tân check-in và quét mã QR tại sảnh tiệc](/docs/client/06-checkin-livewall/CLI-CHCK-001)
- [Soạn lời cảm ơn sau đám cưới gửi khách mời (Personal Thank You)](/docs/client/04-guest-rsvp/CLI-GUEST-008)
