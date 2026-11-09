# BÁO CÁO KHẢO SÁT & ĐÁNH GIÁ MẪU NGUỒN NATIVE (MAY HEN CURATED NATIVE TEMPLATE AUDIT)

> **Mục tiêu**: Khảo sát hiện trạng 4 mẫu thiệp cưới gốc cao cấp trong `resources/views/client/invitations/` nhằm tái sử dụng trực tiếp 100% mã nguồn (Blade, CSS, JS, Assets), phục vụ hệ thống tạo thiệp tự động Curated 7 Section theo đúng Master Prompt V3. AI chỉ đóng vai trò biên soạn nội dung cảm xúc, tuyệt đối không can thiệp hay sáng tạo bố cục HTML/CSS mới.

---

## 1. BẢNG TỔNG HỢP KHẢO SÁT 4 MẪU NGUỒN THỰC TẾ

| Mẫu gốc | View thật | CSS/JS thật | 7 section gắn selector nào | Section nào dùng chung wrapper | Asset nội bộ | Cách inject dữ liệu | Khả năng ẩn các section dư | Tương thích màu | Kết quả screenshot / Trạng thái |
|---|---|---|---|---|---|---|---|---|---|
| **Giữa Muôn Ngàn Vì Sao** (`giua-muon-ngan-vi-sao`) | `resources/views/client/invitations/giua-muon-ngan-vi-sao/template.blade.php` | `styles.css`<br>`script.js` | #1: `.mayhen_invitation_hero`<br>#2: `.mayhen_invitation_story`<br>#3: `.mayhen_invitation_parents`<br>#4: `.mayhen_invitation_countdown_calendar`<br>#5: `.mayhen_invitation_events`<br>#6: `.mayhen_invitation_timeline`<br>#7 (Skill 8): `.mayhen_invitation_gallery` | Toàn bộ 10 section nằm trong `.mayhen_invitation_ladi_wraper.mayhen_invitation_sr`. Có màn mở đầu riêng `.mayhen_invitation_opening`. | `resources/views/client/invitations/giua-muon-ngan-vi-sao/assets/` (WebP, SVG, MP3) | Trực tiếp qua Blade `$wedding`, `$invitation`, `$extraData`, `$resolveAsset` | **Rất tốt**: Dùng điều kiện `@if(!($isCuratedSeven ?? false))` bao bọc Section 7 (Dresscode), Section 9 (Gift), Section 10 (Ending/RSVP). Layout 7 section vẫn giữ nguyên 100% flow Flexbox/Grid. | Hỗ trợ cực tốt tông Hoàng gia Đỏ Ruby (`#85182A`), Vàng Antique, Xanh Đêm Navy (`#1B2A4A`). | **PILOT SẴN SÀNG** |
| **Hạnh Phúc Mang Tên Chúng Mình** (`hanh-phuc-mang-ten-chung-minh`) | `resources/views/client/invitations/hanh-phuc-mang-ten-chung-minh/template.blade.php` | `styles.css`<br>`script.js` | #1: `.mayhen_invitation_hero`<br>#2: `.mayhen_invitation_story`<br>#3: `.mayhen_invitation_parents`<br>#4: `.mayhen_invitation_countdown_calendar`<br>#5: `.mayhen_invitation_events`<br>#6: `.mayhen_invitation_timeline`<br>#7 (Skill 8): `.mayhen_invitation_gallery` | Các section độc lập với wrapper `.mayhen_invitation_section`. Phong cách Sage Green tối giản. | `resources/views/client/invitations/hanh-phuc-mang-ten-chung-minh/assets/` | Trực tiếp qua Blade `$wedding`, `$invitation`, `$extraData` | **Rất tốt**: Các thẻ `<section>` tách rời hoàn toàn, ẩn dễ dàng không ảnh hưởng các phần khác. | Tông Xanh Xô Thơm (Sage Green), Kem Tự Nhiên, Trắng Ngà Lụa. | Sẵn sàng mở rộng |
| **Ngày Tình Yêu Đơm Hoa** (`ngay-tinh-yeu-dom-hoa`) | `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php` | `styles.css`<br>`script.js` | #1: `.mayhen_invitation_hero`<br>#2: `.mayhen_invitation_story`<br>#3: `.mayhen_invitation_parents`<br>#4: `.mayhen_invitation_countdown_calendar`<br>#5: `.mayhen_invitation_events`<br>#6: `.mayhen_invitation_timeline`<br>#7 (Skill 8): `.mayhen_invitation_gallery` | Nằm trong wrapper `.mayhen_invitation_ladi_wraper`. Phong cách hoa lãng mạn lộng lẫy. | `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/assets/` | Trực tiếp qua Blade `$wedding`, `$invitation`, `$extraData` | **Rất tốt**: Cấu trúc module rõ ràng, mỗi section độc lập, có thể ẩn 3 section dư bằng cờ Blade an toàn. | Tông Hồng Tro (Dusty Rose), Đỏ Rượu Vang, Kem Lụa. | Sẵn sàng mở rộng |
| **Trọn Kiếp Này Bên Nhau** (`tron-kiep-nay-ben-nhau`) | `resources/views/client/invitations/tron-kiep-nay-ben-nhau/template.blade.php` | `styles.css`<br>`script.js` | #1: `.mayhen_invitation_hero`<br>#2: `.mayhen_invitation_story`<br>#3: `.mayhen_invitation_family`<br>#4: `.mayhen_invitation_countdown_calendar`<br>#5: `.mayhen_invitation_events`<br>#6: `.mayhen_invitation_timeline`<br>#7 (Skill 8): `.mayhen_invitation_gallery` | Wrapper `.mayhen_invitation_card__container`. Bố cục Vogue Tạp chí sắc nét. | `resources/views/client/invitations/tron-kiep-nay-ben-nhau/assets/` | Trực tiếp qua Blade `$wedding`, `$invitation`, `$extraData` | **Rất tốt**: Có menu trôi nổi (`.mayhen_invitation_floating__menu`). Khi ở chế độ 7 section, chỉ cần lọc các anchor trỏ vào section ẩn. | Tông Hổ Phách (Warm Amber), Nâu Đất, Vàng Đồng, Espresso. | Sẵn sàng mở rộng |

---

## 2. PHÂN TÍCH CHI TIẾT MẪU PILOT: `giua-muon-ngan-vi-sao`

### 2.1 Cấu trúc Section & Mapping 7 Section Curated
1. **Curated #1 = Skill Section 1 (Hero Cover)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_sr_dau.mayhen_invitation_hero`
   - Dữ liệu hiển thị: `ceremony_type` ("Lễ Thành Hôn"), `$heroPhotoUrl` (ảnh chính giữa khung tròn quỹ đạo sao), `$groomName`, `$brideName`, ngày cưới dương lịch & âm lịch.
2. **Curated #2 = Skill Section 2 (Love Story & Quote)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_story.mayhen_invitation_quote`
   - Dữ liệu hiển thị: `$storyTitle`, `$storySubtitle`, `$coupleQuote`, `$storyContent`.
3. **Curated #3 = Skill Section 3 (Family & Couple)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_parents.mayhen_invitation_couple`
   - Dữ liệu hiển thị: Nhà trai (`groom_father`, `groom_mother`, `groom_parents_address`), Nhà gái (`bride_father`, `bride_mother`, `bride_parents_address`), ảnh và lời tự bạch của dâu rể.
4. **Curated #4 = Skill Section 4 (Countdown & Calendar)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_countdown_calendar`
   - Dữ liệu hiển thị: Đồng hồ đếm ngược thời gian thực (ngày, giờ, phút, giây), lịch tháng Save The Date khoanh tròn ngày cưới.
5. **Curated #5 = Skill Section 5 (Events & Maps)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_events.mayhen_invitation_maps`
   - Dữ liệu hiển thị: Thông tin Tiệc cưới chính, Lễ Vu Quy, Lễ Thành Hôn và bản đồ Google Maps chỉ đường.
6. **Curated #6 = Skill Section 6 (Wedding Timeline)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_timeline`
   - Dữ liệu hiển thị: Lịch trình 4 mốc thời gian đón khách, cử hành hôn lễ, khai tiệc, chụp ảnh.
7. **Curated #7 = Skill Section 8 (Album 10 Photos)**:
   - Selector: `section.mayhen_invitation_sr_phan.mayhen_invitation_gallery`
   - Dữ liệu hiển thị: Đúng 10 ảnh kỷ niệm được dàn trang tinh tế, lightbox xem ảnh phóng to.

### 2.2 Các Section Bị Loại Trừ Trong Chế Độ Curated 7 Section
- **Section 7 (Skill Section 7 - Dresscode)**: `section.mayhen_invitation_sr_phan.mayhen_invitation_dresscode` (dòng 772-821).
- **Section 9 (Skill Section 9 - Wedding Gift & VietQR)**: `section.mayhen_invitation_sr_phan.mayhen_invitation_gift` (dòng 872-958).
- **Section 10 (Skill Section 10 - Interactive, RSVP, Wishes & Ending)**: `section.mayhen_invitation_sr_phan.mayhen_invitation_interactive_ending` (dòng 963-1246).

### 2.3 Chiến Lược Adapter An Toàn
- Trong `giua-muon-ngan-vi-sao/template.blade.php`:
  ```php
  $isCuratedSeven = (bool) ($isCuratedSeven ?? ($invitation->custom_config['is_curated_seven'] ?? ($invitation->custom_config['is_curated'] ?? false)));
  ```
- Dùng cú pháp `@if(! $isCuratedSeven)` bao bọc xung quanh 3 section trên.
- Đảm bảo khi `$isCuratedSeven = false` (chế độ xem thử mẫu `/xem-thu/giua-muon-ngan-vi-sao` hoặc thiệp cưới truyền thống đầy đủ), toàn bộ 10 section hoạt động trọn vẹn 100%.

### 2.4 Phòng Vệ Màn Mở Đầu (Opening Overlay) Trong Trình Diễn Curated
- Màn mở đầu `mayhen_invitation_opening` của mẫu gốc:
  - Khi xem công khai thông thường: Hiển thị cánh thiệp sao rơi, nút "Mở thiệp" và nhạc nền lãng mạn.
  - Khi xem trong chế độ nhúng Studio (`$isEmbed = true`) hoặc chế độ trình diễn trực tiếp Live Crafting (`$isCuratedSeven && request()->boolean('crafting')`):
    Tự động mở sẵn cánh thiệp (`opened`), không khóa cuộn trang (`body_class` không gắn `mayhen_invitation_sr_khoa`), giúp khách hàng chiêm ngưỡng quá trình xuất hiện từng dòng chữ và khối ảnh mỹ thuật mượt mà.

---

## 3. MAPPING PHONG CÁCH VÀ MẪU GỐC (STYLE REGISTRY)

Hệ thống thiết lập bảng liên kết chuẩn mực do Mây Hẹn Art-Direct kiểm duyệt:

| Mã Phong Cách (`style`) | Tên Phong Cách | Mẫu Gốc Chính (`primary_template_slug`) | Mẫu Gốc Dự Phòng (`alternative_slug`) | Bảng Màu Khuyên Dùng |
|---|---|---|---|---|
| `romantic` | Lãng Mạn & Bay Bổng | `giua-muon-ngan-vi-sao` | `ngay-tinh-yeu-dom-hoa` | `dusty-rose`, `ruby` |
| `minimal` | Tối Giản & Hiện Đại | `hanh-phuc-mang-ten-chung-minh` | *(sắp bổ sung)* | `sage-green`, `royal-navy` |
| `editorial` | Tạp Chí & Thời Thượng | `tron-kiep-nay-ben-nhau` | *(sắp bổ sung)* | `warm-amber`, `royal-navy` |
| `traditional` | Truyền Thống & Chuẩn Mực | `giua-muon-ngan-vi-sao` | `ngay-tinh-yeu-dom-hoa` | `ruby` |

Khi người dùng nhấn **"Phối lại"**:
Hệ thống chuyển đổi sang mẫu gốc hợp lệ khác trong cùng phong cách (nếu có), tuyệt đối **KHÔNG trộn vụn vặt các section của các mẫu khác nhau**, đảm bảo 100% tính toàn vẹn nghệ thuật của bản thiết kế gốc.

