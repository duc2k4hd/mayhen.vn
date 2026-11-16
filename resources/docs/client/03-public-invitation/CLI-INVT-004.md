---
id: CLI-INVT-004
title: "Hướng dẫn khách lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Hướng dẫn kích hoạt tính năng lưu lịch 1-chạm trên thiệp cưới Mây Hẹn: Tương thích hoàn hảo Apple Calendar (iOS), Google Calendar (Android), định dạng .ICS quốc tế kèm chuông thông báo nhắc giờ cưới tự động."
reader: "PER-CLI-02 / PER-CLI-03"
order: 20
updated_at: "2026-11-15"
tags: [mayhen, thiep-cuoi-cong-cong, luu-lich-ngay-cuoi, google-calendar, apple-calendar, file-ics]
mapped_page: "CLI-INVT-PAGE"
mapped_route: "invitation.show"
risk_level: "MEDIUM"
required_role: "customer"
---

# Hướng dẫn khách lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)

> **Dành cho:** Cô dâu & Chú rể muốn khách mời không bao giờ quên lịch hẹn hoặc đến muộn giờ cử hành hôn lễ  
> **Thời gian kích hoạt:** Đã được tích hợp sẵn 100% tự động trong Section 5 (Lịch cưới & Đếm ngược)  
> **Trải nghiệm phía khách mời:** Chỉ mất đúng 1 thao tác chạm (1-Tap Add to Calendar)  

Giữa guồng quay công việc bận rộn, khách mời rất dễ vô tình quên mất ngày cưới hoặc nhớ nhầm giờ tổ chức nếu chỉ xem qua tấm thiệp một lần từ cả tháng trước. Thấu hiểu điều đó, Mây Hẹn tích hợp sẵn **Bộ Công Cụ Đồng Bộ Lịch Thông Minh (Smart Calendar Integration)** trên từng tấm thiệp cưới. Quan khách chỉ cần chạm nhẹ một lần, ngày cưới của hai bạn sẽ tự động được ghi nhớ vào điện thoại kèm chuông báo nhắc nhở chu đáo.

![Lưu lịch ngày cưới vào điện thoại](/assets/client/docs/images/client/03-public-invitation/cli-invt-004-step-1.png)
*Section Lịch cưới & Đồng hồ đếm ngược: Tích hợp nút lưu lịch 1 chạm đồng bộ vào Apple Calendar và Google Calendar.*

---

## 1. Trải nghiệm lưu lịch diễn ra như thế nào trên thiệp cưới?

Tại **Section 05 (Calendar & Countdown)** trên thiệp cưới của hai bạn:
1. Màn hình hiển thị một tờ lịch cưới thiết kế thanh nhã, nổi bật ngày vui kèm đồng hồ đếm ngược từng ngày, từng giờ.
2. Ngay bên dưới là nút bấm nổi bật: **[🗓 Lưu Vào Lịch Điện Thoại]**.
3. Khi khách mời nhấp vào nút này, hệ thống sẽ nhận diện thiết bị và đưa ra các lựa chọn thuận tiện nhất:
   - **Thêm vào Apple Calendar (Dành cho iPhone / iPad / Mac)**
   - **Thêm vào Google Calendar (Dành cho điện thoại Android / Tài khoản Gmail)**
   - **Tải file tệp lịch `.ICS` (Dành cho Outlook hoặc các ứng dụng lịch khác)**

```
┌────────────────────────────────────────────────────────┐
│            SỰ KIỆN TỰ ĐỘNG ĐƯỢC TẠO TRÊN ĐIỆN THOẠI    │
├────────────────────────────────────────────────────────┤
│ 🔔 TIÊU ĐỀ : Lễ Thành Hôn: Tuấn Minh & Lan Anh         │
│ ⏰ THỜI GIAN: 11:00 – 14:00, Chủ Nhật ngày 28/12/2026 │
│ 📍 ĐỊA ĐIỂM : The Adora Dynasty, 1A Tôn Thất Tùng, Q.1 │
│ 🔗 LIÊN KẾT : https://mayhen.vn/thiep/tuan-minh-lan-anh│
│                                                        │
│ ⏰ NHẮC NHỞ:                                           │
│    • Nhắc trước 1 ngày (Chuẩn bị trang phục Dresscode) │
│    • Nhắc trước 2 giờ  (Bắt đầu di chuyển đến tiệc)    │
└────────────────────────────────────────────────────────┘
```

---

## 2. Hướng dẫn chi tiết cho khách mời theo từng dòng máy

### Đối với khách mời dùng iPhone (Apple iOS)
1. Mở link thiệp cưới trên trình duyệt Safari hoặc Zalo.
2. Cuộn đến phần Lịch cưới, bấm nút **[🗓 Lưu Vào Lịch]** → chọn **Apple Calendar**.
3. Ứng dụng Lịch mặc định của iPhone sẽ tự động mở lên với đầy đủ thông tin sự kiện.
4. Khách chỉ cần bấm nút **"Thêm" (Add)** ở góc trên bên phải màn hình là hoàn tất!

### Đối với khách mời dùng Android (Samsung, Xiaomi, OPPO, Google Pixel)
1. Bấm nút **[🗓 Lưu Vào Lịch]** → chọn **Google Calendar**.
2. Ứng dụng Google Lịch sẽ tự động mở ra biểu mẫu sự kiện đã được điền sẵn ngày, giờ và địa chỉ sảnh tiệc.
3. Khách chỉ cần bấm **"Lưu" (Save)** là lịch cưới được đồng bộ vào tài khoản Google.

---

## 3. Các thông số tự động được chuẩn hóa trong file lịch

Cặp đôi không cần phải cài đặt phức tạp, hệ thống Mây Hẹn tự động trích xuất các trường thông tin trong Studio để tạo file sự kiện chuẩn quốc tế (`RFC 5545 iCalendar`):
- **Tên sự kiện (Summary):** `Lễ Thành Hôn: {Tên Chú Rể} & {Tên Cô Dâu}`.
- **Thời gian (DTSTART / DTEND):** Bắt đầu đúng giờ khai tiệc, thời lượng sự kiện mặc định là **3 tiếng** (đủ cho tiệc cưới diễn ra trọn vẹn).
- **Múi giờ chuẩn (Timezone):** Cố định múi giờ Việt Nam `Asia/Ho_Chi_Minh` (GMT+7), giúp bạn bè ở nước ngoài khi lưu lịch sẽ tự động được chuyển đổi sang giờ địa phương chính xác.
- **Địa chỉ (Location):** Ghi rõ tên nhà hàng, số phòng/sảnh và địa chỉ đường phố.
- **Mô tả & Dẫn đường (Description):** Chứa lời cảm ơn chân thành kèm đường link mở bản đồ chỉ đường Google Maps.

---

## 4. Kiểm tra hoạt động của nút Lưu Lịch trong Studio

Để tự mình trải nghiệm tính năng này trước khi gửi thiệp cho khách:
1. Trong Studio Tạo Thiệp V2, nhìn sang màn hình xem trước **Live Canvas**.
2. Cuộn đến Section Lịch cưới & Đếm ngược.
3. Bấm thử vào nút **[Lưu Vào Lịch]**.
4. Kiểm tra xem ngày giờ và tên sảnh tiệc hiển thị trong popup có chính xác như hai bạn mong muốn hay không.

---

## 5. Bài viết liên quan tiếp theo

- [Cài đặt chỉ đường Google Maps chính xác đến sảnh tiệc nhà trai, nhà gái](/docs/client/03-public-invitation/CLI-INVT-005)
- [Hướng dẫn tạo thiệp đa ngôn ngữ (Tiếng Việt, Tiếng Anh, Song ngữ)](/docs/client/03-public-invitation/CLI-INVT-006)
- [Quản lý danh sách khách mời và gửi link cá nhân hóa](/docs/client/04-guest-rsvp/CLI-GUEST-001)
