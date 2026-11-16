---
id: CLI-TIME-003
title: "Cấp Chứng nhận Ngày cưới số hóa (Digital Keepsake Certificate) lưu giữ trọn đời"
category: "Hộp thời gian & Kỷ niệm Ngày cưới"
summary: "Hướng dẫn kích hoạt Trang Lưu Giữ Kỷ Niệm Hậu Sự Kiện (Wedding Keepsake Hub): Chuyển đổi thiệp cưới thành Bảo tàng Ký ức số, ẩn form RSVP và mã QR mừng cưới, nhúng video phóng sự cưới (Highlight Film), gửi tâm thư cảm tạ và cấp Chứng nhận Ngày cưới số hóa (Digital Certificate)."
reader: "PER-CLI-02 / PER-CLI-03"
order: 4
updated_at: "2026-11-15"
tags: [mayhen, hộp-thời-gian, keepsake, chung-nhan-ngay-cuoi, highlight-video, memory-vault]
mapped_page: "CLI-TIME-PAGE"
mapped_route: "time_capsule.public_slug"
risk_level: "MEDIUM"
required_role: "customer"
---

# Cấp Chứng nhận Ngày cưới số hóa (Digital Keepsake Certificate) lưu giữ trọn đời

> **Dành cho:** Cô dâu & Chú rể chuyển đổi thiệp cưới thành Không gian Kỷ niệm sau ngày cưới  
> **Thời gian thiết lập:** Khoảng 5 phút sau khi có video phóng sự cưới  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Lưu Giữ Kỷ Niệm (Keepsake Hub)** (Route: `account.keepsake.index`)  
> **Sách Kỷ Yếu trực tuyến:** `mayhen.vn/tai-khoan/thiep/{id}/sach-ky-yeu` (Route: `memory.keepsake_book`)

Khi đám cưới đã khép lại, những chức năng như "Xác nhận tham dự (RSVP)" hay "Sơ đồ bàn tiệc" không còn cần thiết nữa. Nếu giữ nguyên, thiệp cưới sẽ trở nên lỗi thời.

Thay vì đóng lại đường link, Mây Hẹn cung cấp tính năng **Chuyển Đổi Thành Bảo Tàng Kỷ Niệm Hôn Lễ (Wedding Keepsake Mode)**: Tự động ẩn các nút tương tác cũ, biến thiệp cưới thành một trang lưu niệm lãng mạn nơi phát video phóng sự cưới (Wedding Highlight Film), hiển thị tâm thư cảm tạ và cấp **Chứng Nhận Hôn Lễ Số Hóa (Digital Keepsake Certificate)** lưu giữ vĩnh viễn trên đám mây.

![Chứng nhận ngày cưới số hóa Digital Keepsake Certificate](/assets/client/docs/images/client/09-time-capsule/cli-time-003-step-1.png)
*Chứng Nhận Hôn Lễ Số Hóa: Khắc ghi dấu mốc ngày cưới trang trọng và bảo tồn kỷ niệm vĩnh cửu.*

---

## 1. Sự Khác Biệt Giữa Thiệp Cưới Trực Tiếp & Trang Kỷ Niệm

```
TRƯỚC NGÀY CƯỚI: THIỆP MỜI TRỰC TIẾP           SAU NGÀY CƯỚI: TRANG LƯU NIỆM (KEEPSAKE)
┌─────────────────────────────────┐           ┌─────────────────────────────────┐
│  - Đếm ngược đến ngày cưới      │           │  ✨ BẢO TÀNG KỶ NIỆM HOÀNG & DUNG│
│  - Biểu mẫu RSVP xác nhận       │  ──────>  │  - Video Highlight Full HD 4K   │
│  - Bản đồ Google Maps chỉ đường │           │  - Tâm thư cảm ơn chân thành    │
│  - Hộp mừng cưới VietQR         │           │  - Toàn bộ Album ảnh & Lưu bút  │
│  - Tra cứu chỗ ngồi bàn tiệc    │           │  - Chứng nhận ngày cưới số hóa  │
└─────────────────────────────────┘           └─────────────────────────────────┘
```

---

## 2. Các bước cấu hình Trang Kỷ Niệm Hậu Cưới

Truy cập **Quản Lý Thiệp** -> Chọn thiệp cưới -> Nhấp menu **Lưu Giữ Kỷ Niệm (Keepsake)**:

### Bước 1: Soạn tâm thư cảm ơn quan khách
- **Tiêu đề (`thank_you_title`):** *Ví dụ: "Lời cảm ơn sâu sắc từ vợ chồng Minh Hoàng & Thùy Dung"*.
- **Nội dung tâm thư (`thank_you_message`):** Bày tỏ lòng biết ơn chân thành tới gia đình hai bên và toàn thể quan khách đã dành thời gian quý báu đến chung vui và chúc phúc cho hai bạn.

### Bước 2: Nhúng Video Highlight Phóng Sự Cưới
- Dán đường dẫn video phóng sự cưới (YouTube hoặc Vimeo) do ekip quay phim bàn giao vào ô **Đường dẫn Video (`video_url`)**.
- Video sẽ tự động phát ở chế độ rạp chiếu phim (Cinema Mode) sắc nét ngay đầu trang lưu niệm.

### Bước 3: Dọn dẹp giao diện thiệp (Smart Clean-up)
Tích chọn các tùy chọn dọn dẹp giao diện:
- ☑️ **Ẩn biểu mẫu RSVP (`hide_rsvp`):** Đóng cổng đăng ký tham dự để tránh nhầm lẫn.
- ☑️ **Ẩn hộp mừng cưới VietQR (`hide_gifts`):** Tự động ẩn mã QR ngân hàng để giữ sự tế nhị sau khi tiệc đã hoàn tất.
- ☑️ **Bật trình chiếu Album ảnh Photo Drop & Sổ lưu bút:** Khách mở lại link thiệp có thể xem toàn bộ ảnh bạn bè chụp trong tiệc cưới.

---

## 3. Cấp Chứng Nhận Ngày Cưới Số Hóa (Digital Certificate)

Nằm ở vị trí danh dự trong Trang Kỷ Niệm, Chứng nhận Hôn lễ Mây Hẹn là tác phẩm đồ họa được thiết kế theo phong cách chứng chỉ hoàng gia:

```
┌────────────────────────────────────────────────────────────────────────┐
│  ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦   │
│                                                                        │
│                      CHỨNG NHẬN NGÀY HÔN PHỐI                          │
│                      (CERTIFICATE OF MARRIAGE)                         │
│                                                                        │
│         Trân trọng xác nhận sự gắn kết thiêng liêng giữa               │
│                                                                        │
│               NGUYỄN MINH HOÀNG  ❤️  TRẦN THÙY DUNG                    │
│                                                                        │
│   Đã nên duyên vợ chồng vào ngày 15 Tháng 11 Năm 2026                 │
│   Tại: Trung tâm Hội nghị Quốc tế Almaz, Hà Nội                       │
│                                                                        │
│   Mã lưu trữ độc bản: MH-2026-987654                                  │
│   Dấu niêm phong: Con Dấu Sáp Hoàng Gia Mây Hẹn Atelier               │
│                                                                        │
│   [ Chữ ký Chú Rể: M. Hoàng ]       [ Chữ ký Cô Dâu: T. Dung ]        │
│                                                                        │
│  ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦ ✦   │
└────────────────────────────────────────────────────────────────────────┘
```

- **Mã định danh lưu trữ số (Unique Serial Number):** Mỗi chứng nhận sở hữu một mã số độc bản được xác thực trên hệ thống Mây Hẹn.
- **Tải ảnh chuẩn in A4:** Bạn có thể tải file ảnh chứng nhận độ phân giải cao hoặc file PDF để in màu và lồng khung kính kỷ niệm.

---

## 4. Trải nghiệm Sách Kỷ Yếu Trực Tuyến (Interactive Keepsake Book)

Khi nhấp vào liên kết **Sách Kỷ Yếu** (`/tai-khoan/thiep/{id}/sach-ky-yeu`), hệ thống tự động biên tập toàn bộ dữ liệu hôn lễ thành một cuốn sách lật trang ảo:
1. **Trang bìa:** Ảnh cưới đẹp nhất kèm tiêu đề và ngày giờ hôn lễ.
2. **Chương 1 (Câu chuyện tình yêu):** Hành trình từ ngày đầu gặp gỡ đến khoảnh khắc cầu hôn.
3. **Chương 2 (Khoảnh khắc ngày vui):** Tuyển tập ảnh phóng sự cưới và video highlight.
4. **Chương 3 (Bút tích chúc phúc):** Toàn bộ lời chúc và chữ ký tay của từng vị khách mời.
5. **Chương 4 (Thống kê ngày cưới):** Tổng số khách tham dự, số bàn tiệc và những con số đáng nhớ.
