---
id: CLI-INVT-005
title: "Cài đặt chỉ đường Google Maps chính xác đến sảnh tiệc nhà trai, nhà gái"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Hướng dẫn thiết lập địa điểm và liên kết chỉ đường Google Maps chuẩn xác cho từng sự kiện: Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới hai nhà, cùng kinh nghiệm gắn ghi chú sảnh tiệc và bãi đỗ xe chu đáo."
reader: "PER-CLI-02 / PER-CLI-03"
order: 21
updated_at: "2026-11-15"
tags: [mayhen, thiep-cuoi-cong-cong, chi-duong-google-maps, ban-do-thiep-cuoi, le-vu-quy, le-thanh-hon]
mapped_page: "CLI-INVT-PAGE"
mapped_route: "invitation.show"
risk_level: "HIGH"
required_role: "customer"
---

# Cài đặt chỉ đường Google Maps chính xác đến sảnh tiệc nhà trai, nhà gái

> **Dành cho:** Cô dâu & Chú rể muốn khách mời tìm đường thuận lợi, đến đúng sảnh tiệc mà không lo lạc đường  
> **Thời gian thực hiện:** Khoảng 2 - 4 phút  
> **Vị trí trong ứng dụng:** Studio Tạo Thiệp V2 → Tab **[Nội Dung]** → Mục **Sự Kiện & Bản Đồ Dẫn Đường**  

Trong các đám cưới tại Việt Nam, việc tổ chức nhiều nghi lễ ở các địa điểm khác nhau (như Lễ Vu Quy tại nhà gái, Lễ Tân Hôn tại nhà trai và Tiệc cưới chính tại Trung tâm Hội nghị) là điều rất phổ biến. Nếu thông tin không rõ ràng, khách mời rất dễ đi nhầm giữa hai nhà hoặc lạc trong các tòa nhà trung tâm thương mại rộng lớn. Mây Hẹn hỗ trợ cấu hình đa sự kiện độc lập, mỗi sự kiện đều có bản đồ vệ tinh và nút chỉ đường Google Maps 1-chạm chuẩn xác.

![Thẻ sự kiện cưới và nút chỉ đường Google Maps](/assets/client/docs/images/client/03-public-invitation/cli-invt-005-step-1.png)
*Section Sự kiện cưới: Tích hợp thông tin ngày giờ, địa điểm chính xác và nút bấm mở ứng dụng Google Maps.*

---

## 1. Cấu trúc hiển thị Section Sự Kiện & Bản Đồ (Section 06)

Mỗi sự kiện cưới trên thiệp Mây Hẹn được thiết kế như một thẻ lịch trình trang trọng (Event Card):

```
┌────────────────────────────────────────────────────────┐
│         THẺ SỰ KIỆN CƯỚI TRÊN THIỆP MÂY HẸN (CARD)     │
├────────────────────────────────────────────────────────┤
│  ❀ LỄ THÀNH HÔN & TIỆC CƯỚI CHÍNH                      │
│                                                        │
│  ⏰ Thời gian: 11:00 Trưa | Chủ Nhật, 28/12/2026       │
│  🏛 Địa điểm : Trung Tâm Hội Nghị The Adora Dynasty    │
│  🚪 Sảnh tiệc: Sảnh Diamond (Tầng 3)                   │
│  📍 Địa chỉ  : Số 1A Tôn Thất Tùng, P. Bến Thành, Q.1 │
│  🚗 Ghi chú  : Bãi đỗ xe ô tô miễn phí tại Tầng hầm B2 │
│                                                        │
│       [ 📍 MỞ GOOGLE MAPS CHỈ ĐƯỜNG 1-CHẠM ]           │
└────────────────────────────────────────────────────────┘
```

---

## 2. Hướng dẫn từng bước cài đặt bản đồ trong Studio

### Bước 1: Thêm hoặc chỉnh sửa Sự Kiện Cưới
1. Trong Studio Tạo Thiệp, chọn tab **[Nội Dung]** trên thanh công cụ bên trái.
2. Cuộn đến phần **"Sự Kiện & Bản Đồ"** (Events & Locations).
3. Hệ thống có sẵn các sự kiện mẫu, bạn có thể:
   - Sửa sự kiện có sẵn (Lễ Vu Quy / Lễ Thành Hôn / Tiệc Cưới).
   - Nhấp nút **[+ Thêm Sự Kiện]** nếu gia đình có tổ chức thêm Lễ Hằng Thuận tại Chùa, Lễ Phối Hôn tại Nhà Thờ, hoặc Tiệc Báo Hỷ riêng.

### Bước 2: Điền chi tiết địa điểm và sảnh tiệc
1. **Tên sự kiện (`event_name`):** Nhập tên trang trọng (Ví dụ: *Lễ Thành Hôn & Tiệc Mừng*).
2. **Thời gian (`event_time`):** Giờ đón khách (ví dụ: `17:30`) và Giờ khai tiệc (ví dụ: `18:30`).
3. **Tên địa điểm (`venue_name`):** Tên khách sạn hoặc trung tâm tiệc cưới (Ví dụ: *The Adora Dynasty*).
4. **Tên sảnh cụ thể (`hall_name`):** Rất quan trọng! Hãy ghi rõ số tầng hoặc tên sảnh tiệc (Ví dụ: *Sảnh Grand Ballroom - Tầng 2*).
5. **Địa chỉ chi tiết (`venue_address`):** Ghi rõ số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố.

### Bước 3: Lấy Link Google Maps chính xác 100%
Để nút chỉ đường mở đúng cửa sảnh tiệc thay vì một địa chỉ chung chung ngoài đường lớn:
1. Mở ứng dụng **Google Maps** trên máy tính hoặc điện thoại.
2. Tìm kiếm chính xác tên trung tâm tiệc cưới (Ví dụ: gõ *The Adora Dynasty 1A Tôn Thất Tùng*).
3. Đảm bảo chấm đỏ (Pin) cắm đúng tòa nhà tiệc cưới.
4. Bấm nút **Chia sẻ** (Share) → chọn **Sao chép liên kết** (Copy Link). Bạn sẽ nhận được đường link dạng: `https://maps.app.goo.gl/xyz...` hoặc `https://goo.gl/maps/...`.
5. Quay lại Studio Mây Hẹn, dán link vừa sao chép vào ô **"Đường dẫn Google Maps (Map URL)"**.

### Bước 4: Thêm ghi chú bãi đỗ xe chu đáo (Parking Notes)
Điền một dòng nhắn nhủ ngắn gọn tại ô **Ghi chú di chuyển**:
- *Ví dụ:* "Trung tâm có bãi giữ xe máy và ô tô miễn phí tại tầng hầm B1 & B2, quý khách đi lối cổng phụ đường Nguyễn Du."
- Chi tiết nhỏ này sẽ nhận được sự cảm kích rất lớn từ khách mời, đặc biệt là những khách đi xe ô tô trong khung giờ cao điểm.

---

## 3. Trải nghiệm phía khách mời khi chạm nút chỉ đường

Khi khách mời nhận thiệp và bấm vào nút **[📍 Chỉ Đường Google Maps]**:
- **Nếu dùng điện thoại:** Thiết bị sẽ tự động mở ứng dụng **Google Maps** (hoặc **Apple Maps** trên iPhone) và lập tức chuyển sang chế độ **Bắt đầu điều hướng (GPS Navigation)** từ vị trí hiện tại của khách đến sảnh tiệc.
- **Nếu dùng máy tính:** Bản đồ sẽ mở ra trong tab mới hiển thị lộ trình và thời gian di chuyển dự kiến.

---

## 4. Những sai lầm thường gặp và cách phòng tránh

> [!WARNING]
> **Không copy link tọa độ tìm kiếm tạm thời:**  
> Tránh dán những đường link có chứa tọa độ lạ dạng `google.com/search?q=nha+hang...` vì khi gửi sang điện thoại khác có thể bị lỗi không mở được bản đồ. Luôn dùng nút **Chia sẻ (Share) -> Sao chép liên kết** chính thống của Google Maps.

> [!TIP]
> **Nhà riêng trong ngõ hẻm:**  
> Nếu tổ chức Lễ Gia Tiên tại nhà riêng trong ngõ nhỏ, hãy chấm sẵn điểm gửi xe ô tô đầu ngõ trên Google Maps và dán link điểm gửi xe đó để khách mời không bị mắc kẹt khi lái xe vào ngõ hẹp.

---

## 5. Bài viết liên quan tiếp theo

- [Hướng dẫn tạo thiệp đa ngôn ngữ (Tiếng Việt, Tiếng Anh, Song ngữ)](/docs/client/03-public-invitation/CLI-INVT-006)
- [Quản lý danh sách khách mời và gửi link cá nhân hóa](/docs/client/04-guest-rsvp/CLI-GUEST-001)
- [Theo dõi phản hồi tham dự RSVP và phân nhóm cỗ](/docs/client/04-guest-rsvp/CLI-GUEST-002)
