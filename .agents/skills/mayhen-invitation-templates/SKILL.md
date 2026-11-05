---
name: mayhen-invitation-templates
description: Tiêu chuẩn kỹ thuật, kiến trúc và quy trình chuẩn hóa mẫu thiệp cưới (Invitation Template Engine) cho dự án Mây Hẹn (mayhen.vn). Đảm bảo 100% mẫu thiệp hoạt động mượt mà, đồng bộ thời gian thực với Studio Tạo Thiệp và Admin.
---

# 💌 MAYHEN INVITATION TEMPLATE SPECIFICATION & ENGINE STANDARDS

> **Phạm vi áp dụng**: Mọi tác vụ tạo mới, thiết kế, chuẩn hóa hoặc vá lỗi mẫu thiệp cưới trong `resources/views/client/invitations/{slug}/`.

---

## 🏛️ 1. BỘ QUY CHUẨN CỐT LÕI (THE CORE ARCHITECTURAL CONTRACT)

Mọi mẫu thiệp cưới của Mây Hẹn (mayhen.vn) hoạt động theo mô hình **Tách Bạch Dữ Liệu & Bộ Áo (Data Contract vs Theme Skin)**:
1. **Dữ liệu & Class BEM (Bất Biến)**: Tất cả mẫu thiệp dùng chung một bộ **10 Section chuẩn** và chung một tiền tố class BEM `mayhen_invitation_*`.
2. **Bộ Áo Giao Diện (Đa Dạng)**: Mỗi mẫu tự do sáng tạo màu sắc, hoa văn, font chữ nghệ thuật (Quiet Luxury, Editorial, Vintage...), nhưng **bắt buộc xây dựng 100% bằng CSS Flexbox / Grid tự co giãn theo nội dung (Flow Layout)**. Tuyệt đối CẤM dùng layout định vị tuyệt đối `position: absolute` kéo-thả kiểu LadiPage.
3. **Tự Chứa 100% Tài Nguyên Nội Bộ (Zero External Assets)**: Không tải ảnh/font/audio từ CDN hay server bên ngoài. Toàn bộ tài nguyên phải đặt trong thư mục `assets/` của mẫu thiệp.

---

## 📦 2. CẤU TRÚC PHẦN TỬ BAO TOÀN CỤC (GLOBAL ELEMENTS)

Trước khi vào 10 Section nội dung cuộn, thiệp luôn có các phần tử bao bọc toàn cục:

| Ký hiệu | Thành phần | Class quy chuẩn bắt buộc | Live Sync (`data-sync-target`) | Vai trò |
| :---: | :--- | :--- | :--- | :--- |
| **G.1** | **Bao bọc thiệp** | `mayhen_invitation_ladi_wraper` | Không | Wrapper ngoài cùng chứa toàn bộ nội dung thiệp. |
| **G.2** | **Nút nhạc đĩa than** | `mayhen_invitation_music_dock`<br>`mayhen_invitation_music_btn` | Không | Nút quay đĩa phát/dừng nhạc cố định góc trên bên trái (`top: 16px; z-index: 1001;`). |
| **G.3** | **Thẻ Audio nền** | `mayhen_invitation_audio` | `data-sync-target="music_url"` | Thẻ `<audio>` chứa bài hát cưới MP3 nội bộ. |
| **G.4** | **Màn Mở Đầu** | `mayhen_invitation_opening` | `data-mayhen-section="opening"` | Màn chắn ban đầu (z-index 9999999), chạm để mở nhạc và lộ thiệp. Tự ẩn khi `$isEmbed = true`. |
| **G.5** | **Khung phong bì** | `mayhen_invitation_envelope` | Không | Phong bì nắp mở hoặc 2 cánh cửa hoàng gia 3D. |
| **G.6** | **Nút bấm Mở Thiệp** | `mayhen_invitation_open_btn` | Không | Nút "Chạm để mở thiệp" hoặc con dấu sáp niêm phong. |
| **G.7** | **Đường vạch ngăn cách** | `mayhen_invitation_divider` | `aria-hidden="true"` | Họa tiết ngăn cách tinh tế giữa các Section. |

---

## 📋 3. BỘ 10 SECTION CHUẨN HÓA (THE 10 STANDARD SECTIONS)

> **Chi tiết tham chiếu**: Xem toàn bộ danh sách class chi tiết tại [mayhen-invitation-standards](file:///e:/Project/Laravel/mayhen.vn/.agents/skills/mayhen-invitation-standards/SKILL.md).

Mọi mẫu thiệp bắt buộc tuân thủ đúng khung cấu trúc 10 Section tiêu chuẩn sau:

| STT | Tên Section | Mục đích & Trải nghiệm | Các Key Canonical Bắt Buộc | Class / Container Quy Chuẩn |
| :---: | :--- | :--- | :--- | :--- |
| **1** | **Trang Bìa Chính (Hero Cover)** | Bìa thiệp chính: Ảnh cưới lớn nhất của đôi uyên ương, tên dâu rể nghệ thuật, loại lễ, ngày cưới dương & âm, câu slogan mở màn. | `wedding.groom_name`<br>`wedding.bride_name`<br>`wedding.wedding_date`<br>`wedding.cover_photo_url` | `.mayhen_invitation_hero`<br>`.mayhen_invitation_hero_media`<br>`.mayhen_invitation_ladi_headline_ceremony_type`<br>`.mayhen_invitation_ladi_headline_name_groom`<br>`.mayhen_invitation_ladi_headline_name_bride` |
| **2** | **Lời Ngỏ & Chuyện Tình Yêu (Love Story & Quote)** | Trích dẫn tâm tình lãng mạn, ảnh cưới kỷ niệm trong khung tranh nghệ thuật, câu chuyện tình yêu hoặc thông điệp ý nghĩa gửi tới khách quý. | `wedding.extra_data.couple_quote`<br>`wedding.extra_data.story_title`<br>`wedding.extra_data.story_content`<br>`wedding.extra_data.save_date_photo_url` | `.mayhen_invitation_story`<br>`.mayhen_invitation_story_img`<br>`.mayhen_invitation_quote_text`<br>`.mayhen_invitation_story_content`<br>`.mayhen_invitation_monogram` |
| **3** | **Song Thân & Đôi Uyên Ương (Family & Couple)** | Phụ mẫu Nhà Trai - Nhà Gái, ảnh chân dung Chú Rể & Cô Dâu cùng lời tự bạch thân thương. | `wedding.extra_data.groom_father / groom_mother`<br>`wedding.extra_data.bride_father / bride_mother`<br>`wedding.extra_data.groom_photo_url / groom_bio`<br>`wedding.extra_data.bride_photo_url / bride_bio` | `.mayhen_invitation_parents`<br>`.mayhen_invitation_couple`<br>`.mayhen_invitation_groom_bio`<br>`.mayhen_invitation_bride_bio` |
| **4** | **Đếm Ngược & Lịch Tháng (Countdown & Calendar)** | Đồng hồ đếm ngược thời gian thực (Ngày, Giờ, Phút, Giây) + Ô lịch tháng hoa văn đánh dấu ngày cưới. | `wedding.wedding_date`<br>`wedding.extra_data.event_target_time`<br>`wedding.extra_data.lunar_date` | `.mayhen_invitation_countdown_calendar`<br>`.mayhen_invitation_countdown`<br>`.mayhen_invitation_calendar`<br>`.mayhen_invitation_add_calendar_btn` |
| **5** | **Thông Tin Sự Kiện & Bản Đồ (Events & Maps)** | Chi tiết Lễ Vu Quy, Lễ Thành Hôn & Tiệc Cưới: Thời gian, địa điểm, bản đồ Google Maps và nút chỉ đường. | `wedding.extra_data.ceremony_time / venue / address`<br>`wedding.extra_data.party_time / venue / address`<br>`wedding.extra_data.event_party_map_link` | `.mayhen_invitation_events`<br>`.mayhen_invitation_event_card_party`<br>`.mayhen_invitation_maps`<br>`.mayhen_invitation_map_link_btn` |
| **6** | **Lịch Trình Hôn Lễ (Wedding Timeline)** | Các mốc sự kiện trong ngày hôn lễ: Đón khách, Làm lễ, Khai tiệc, Nâng ly, Chụp ảnh kỷ niệm. | `wedding.extra_data.timeline` (mảng `time`, `title`, `desc`) | `.mayhen_invitation_timeline`<br>`.mayhen_invitation_timeline_item`<br>`.mayhen_invitation_timeline_time` |
| **7** | **Quy Định Trang Phục (Dresscode)** | Bảng màu sắc gợi ý trang phục cho khách mời đồng điệu và trang nhã. | `wedding.extra_data.dresscode_colors`<br>`wedding.extra_data.dresscode_note` | `.mayhen_invitation_dresscode`<br>`.mayhen_invitation_dresscode_swatches`<br>`.mayhen_invitation_dresscode_circle` |
| **8** | **Khoảnh Khắc Hạnh Phúc (Album 10 Photos)** | Đúng **10 ảnh cưới** đẹp nhất xếp dạng lưới hoặc so le kèm Lightbox phóng to ảnh khi nhấp. | `wedding.extra_data.gallery.0`<br>... đến ...<br>`wedding.extra_data.gallery.9` | `.mayhen_invitation_gallery`<br>`.mayhen_invitation_gallery_item`<br>`.mayhen_invitation_gallery_img`<br>`.mayhen_invitation_lightbox` |
| **9** | **Mừng Cưới & Hộp Mừng (Wedding Gift & VietQR)** | Hộp mừng cưới hiện đại gồm thông tin ngân hàng & mã VietQR Chú Rể, Cô Dâu cùng nút bấm sao chép STK 1-chạm. | `wedding.extra_data.groom_bank_* / qr_groom_image`<br>`wedding.extra_data.bride_bank_* / qr_bride_image` | `.mayhen_invitation_gift`<br>`.mayhen_invitation_gift_box`<br>`.mayhen_invitation_qr_groom_img`<br>`.mayhen_invitation_copy_btn` |
| **10** | **Tương Tác, RSVP & Lời Chúc (Interactive, Wishes & Ending)** | - Xác nhận tham dự (RSVP AJAX)<br>- Sổ lưu bút chúc phúc AJAX<br>- Lời cảm tạ chân thành cuối thiệp. | Endpoints AJAX: `wishes`, `rsvp`<br>`wedding.extra_data.guest_name` | `.mayhen_invitation_interactive`<br>`.mayhen_invitation_rsvp`<br>`.mayhen_invitation_wishes`<br>`.mayhen_invitation_ending` |

---

## ⚡ 4. 7 QUY TẮC BẤT DI BẤT DỊCH KHI TẠO & SỬA MẪU THIỆP

1. **Khóa Schema chuẩn (`schema.json`)**: Đủ 10 sections, 100% canonical keys, mảng `gallery` có `"default": [10 ảnh nội bộ có thật]`.
2. **Hợp đồng Live Sync (`data-sync-target`)**: Mọi phần tử hiển thị dữ liệu đều phải gắn `data-sync-target="{tên_trường}"`.
3. **Phòng vệ nhúng Studio Editor (`$isEmbed = true`)**:
   - Ẩn màn mở đầu `style="{{ $isEmbed ? 'display: none !important;' : '' }}"`.
   - Gắn class `opened mayhen_door_hidden`.
   - Tuyệt đối không gắn class khóa cuộn `no-scroll` hoặc `overflow: hidden` lên `html`/`body` khi nhúng trong iframe.
4. **Phòng vệ ảnh lỗi (Asset Fallback)**: Mọi hàm `$resolveAsset` phải có fallback chỉ đến file ảnh có thật trong thư mục `assets/` của mẫu.
5. **Tự chứa 100% tài nguyên cục bộ (Zero External Assets)**: CẤM tuyệt đối dùng URL ảnh, font, icon, audio từ server bên ngoài (`builder.27biggroup.com`, `ladicdn.com`, `unsplash.com`, `iili.io`). Toàn bộ phải tải về và đặt trực tiếp trong `assets/` của mẫu đó; mọi tham chiếu trong Blade, CSS và schema đều trỏ về asset nội bộ.
6. **Vị trí nút nhạc & phân lớp Z-Index**:
   - Nút nhạc cố định góc trên bên trái: `top: 16px !important; left: max(16px, calc(50% - 210px + 16px)) !important; position: fixed !important; z-index: 1001 !important;`.
   - Màn mở đầu: `z-index: 9999999`.
   - Lightbox / Modal: `z-index: 100000`.
   - Nút nhạc: `z-index: 1001` (dưới màn mở đầu khi chưa mở, trên nội dung khi đã mở).
7. **Kế thừa Layout chung**: Mọi mẫu thiệp bắt buộc `@extends('client.layouts.invitation')` để tích hợp tự động cầu nối `editor-bridge.blade.php`.
