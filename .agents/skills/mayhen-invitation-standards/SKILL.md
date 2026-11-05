---
name: mayhen-invitation-standards
description: Bộ quy chuẩn kỹ thuật toàn diện về 10 Section tiêu chuẩn và hệ thống class BEM mayhen_invitation_* cùng hợp đồng Live Sync (data-sync-target) bắt buộc cho toàn bộ mẫu thiệp cưới Mây Hẹn (mayhen.vn).
---

# 📜 BỘ QUY CHUẨN 10 SECTION & HỆ THỐNG CLASS CHUẨN TOÀN CỤC THIỆP CƯỚI MÂY HẸN

> **Phạm vi áp dụng**: Mọi file `template.blade.php`, `styles.css`, `script.js` và `schema.json` của tất cả các mẫu thiệp cưới trong `resources/views/client/invitations/{slug}/`.

---

## 🏛️ 1. NGUYÊN TẮC CỐT LÕI (CORE ARCHITECTURAL RULES)

1. **Khung Cấu Trúc Bất Biến (Immutable Contract)**:
   - Tất cả mẫu thiệp bắt buộc có đầy đủ **10 Section chuẩn** theo đúng thứ tự logic.
   - Toàn bộ tên class cốt lõi mang tiền tố `mayhen_invitation_*` là **bất biến**, không được đổi tên hay xóa bỏ.
2. **Bộ Áo Đa Dạng (Theme Skin Modifiers)**:
   - Mỗi mẫu thiệp được tự do sáng tạo màu sắc, hiệu ứng, typography, background theo chủ đề (Thiên hà, Vintage, Tạp chí, Editorial, Minimalist...).
   - Khi cần tùy biến CSS riêng cho từng mẫu, **gắn thêm class modifier** (Ví dụ: `class="mayhen_invitation_hero mayhen_invitation_hero--celestial"`), tuyệt đối không sửa đổi class cơ sở `mayhen_invitation_hero`.
3. **Hợp Đồng Live Sync Tuyệt Đối (`data-sync-target`)**:
   - Mọi phần tử hiển thị dữ liệu đều phải gắn `data-sync-target="{canonical_key}"` để Studio Tạo Thiệp (`/tao-thiep`) và Admin đồng bộ tức thời hai chiều qua handshake `editor-bridge.blade.php`.
4. **Phòng Vệ Nhúng Editor (`$isEmbed = true`)**:
   - Màn mở đầu `mayhen_invitation_opening` tự động ẩn (`display: none !important;`) khi `$isEmbed = true`.
   - Cấm gắn class khóa cuộn `no-scroll` hay `overflow: hidden` lên `html`/`body` khi nhúng trong iframe.
5. **Tự Chứa 100% Tài Nguyên Nội Bộ (Zero External Assets)**:
   - 100% font chữ, ảnh mẫu, audio MP3, icon SVG nằm trong thư mục nội bộ `assets/` của mẫu đó. Tuyệt đối không dùng URL từ bên ngoài.
6. **Quy Tắc Bất Biến Về Sổ Ký Tên & Xác Nhận RSVP (Zero Couple Photos in Signature & RSVP)**:
   - **Bắt buộc có mặt trong 100% mẫu thiệp**: Sổ Ký Tên Điện Tử (`Signature`) và Xác Nhận Tham Dự (`RSVP`) là 2 tính năng tương tác trọng tâm, mẫu nào cũng bắt buộc phải có.
   - **TUYỆT ĐỐI CẤM DÙNG ẢNH NGƯỜI / ẢNH CẶP ĐÔI**: Không bao giờ được đặt ảnh chụp cặp đôi hay ảnh stock người trong 2 phần này (cả ảnh nền canvas ký tên và ảnh minh họa RSVP). Việc để ảnh ở 2 phần này khiến khách hàng không sửa được trên Studio và làm thiệp gửi cho khách mời xuất hiện ảnh của người khác.
   - **CHỈ ĐƯỢC DÙNG HIỆU ỨNG, STICKER, TEM SEAL HOẶC HOA VĂN ĐỒ HỌA**: Dùng background canvas giấy mỹ thuật / galaxy / stardust, tem niêm phong sáp (wax seal), sticker SVG, đường viền hoa văn hoàng gia để làm đẹp thiệp.

---

## 📦 2. CẤU TRÚC PHẦN TỬ BAO TOÀN CỤC (GLOBAL & WRAPPER ELEMENTS)

| STT | Thành phần | Class quy chuẩn bắt buộc | Thuộc tính / Key Live Sync | Ý nghĩa & Vai trò |
| :---: | :--- | :--- | :--- | :--- |
| **G.1** | **Bao bọc toàn bộ thiệp** | `mayhen_invitation_ladi_wraper` | Không | Thẻ bao ngoài cùng chứa toàn bộ nội dung cuộn của thiệp. |
| **G.2** | **Nút đĩa than bật/tắt nhạc** | `mayhen_invitation_music_dock`<br>`mayhen_invitation_music_btn` | Không | Nút điều khiển âm thanh cố định góc trên bên trái (`top: 16px; z-index: 1001;`). |
| **G.3** | **Thẻ Audio phát nhạc** | `mayhen_invitation_audio` | `data-sync-target="music_url"` | Thẻ `<audio>` chứa file nhạc nền MP3 nội bộ. |
| **G.4** | **Màn Mở Đầu (Phong bì / Cửa)** | `mayhen_invitation_opening` | `data-mayhen-section="opening"` | Màn chắn ban đầu (z-index 9999999), chạm để mở nhạc và lộ thiệp. |
| **G.5** | **Khung phong bì / Cánh cửa** | `mayhen_invitation_envelope` | Không | Khung hình chữ nhật phong bì hoặc 2 cánh cửa mở 3D. |
| **G.6** | **Nút bấm Mở Thiệp** | `mayhen_invitation_open_btn` | Không | Nút "Mở thiệp" hoặc "Chạm để mở thiệp". |
| **G.7** | **Đường vạch phân cách Section** | `mayhen_invitation_divider` | `aria-hidden="true"` | Đường kẻ hoa văn phân cách nhẹ nhàng giữa các Section. |

---

## 📋 3. BẢNG QUY CHUẨN CHI TIẾT 10 SECTION CHUẨN HÓA

---

### 🌟 SECTION 1: TRANG BÌA CHÍNH (HERO COVER)
> **Container chính**: `<section class="mayhen_invitation_hero" data-mayhen-section="couple">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_hero` | `<section>` | Không | Khung section bìa thiệp chính. |
| `mayhen_invitation_ladi_headline_ceremony_type` | `<div>` / `<h2>` | `data-sync-target="ceremony_type"` | Tiêu đề loại lễ: "Lễ Thành Hôn", "Lễ Vu Quy", "Lễ Tân Hôn", "Lễ Báo Hỷ". |
| `mayhen_invitation_hero_media`<br>`mayhen_invitation_ladi_image` | `<div>` / `<img>` | `data-sync-target="hero_photo_url"` | **Ảnh chính của đôi uyên ương** (cũng là ảnh đại diện của thiệp). |
| `mayhen_invitation_ladi_headline_name_groom` | `<h1>` / `<h2>` | `data-sync-target="groom_name"` | Họ và tên **Chú Rể**. |
| `mayhen_invitation_hero_amp` | `<span>` / `<div>` | Không | Ký tự kết nối: `&` hoặc `✦` hoặc `với`. |
| `mayhen_invitation_ladi_headline_name_bride` | `<h1>` / `<h2>` | `data-sync-target="bride_name"` | Họ và tên **Cô Dâu**. |
| `mayhen_invitation_ladi_headline_wedding_day` | `<p>` / `<div>` | `data-sync-target="wedding_date_digits"` | Ngày cưới dương lịch định dạng số (Ví dụ: `20 . 12 . 2026`). |
| `mayhen_invitation_ladi_paragraph_lunar_calendar` | `<p>` | `data-sync-target="wedding_lunar_date"` | Ngày cưới âm lịch (Ví dụ: `Nhằm ngày 12 tháng 11 năm Bính Ngọ`). |
| `mayhen_invitation_ladi_paragraph_slogan` | `<p>` | Không | Câu slogan mở màn (Ví dụ: *"Giữa muôn vàn vì sao, chúng ta đã tìm thấy nhau"*). |

---

### 💌 SECTION 2: LỜI NGỎ & CHUYỆN TÌNH YÊU (LOVE STORY & QUOTE)
> **Container chính**: `<section class="mayhen_invitation_story mayhen_invitation_quote" data-mayhen-section="couple">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_story` | `<section>` | Không | Khung section Lời ngỏ và Chuyện tình yêu. |
| `mayhen_invitation_story_kicker` | `<div>` | Không | Dòng nhãn phụ phía trên: "Love Story", "Lời Ngỏ". |
| `mayhen_invitation_ladi_headline_story` | `<h2>` | `data-sync-target="story_title"` | Tiêu đề mục: "Chuyện Tình Yêu", "Lời Ngỏ Tình Yêu". |
| `mayhen_invitation_story_img` | `<div>` / `<img>` | `data-sync-target="save_date_photo_url"` | **Ảnh cưới kỷ niệm** đặt trong khung vòm hoặc khung tranh nghệ thuật. |
| `mayhen_invitation_story_subtitle` | `<h3>` | `data-sync-target="story_subtitle"` | Tiêu đề phụ của câu chuyện (Ví dụ: *"Hành Trình Gặp Gỡ Giữa Ngân Hà"*). |
| `mayhen_invitation_quote_text` | `<blockquote>` / `<p>` | `data-sync-target="couple_quote"` | **Câu trích dẫn tâm tình lãng mạn** của cặp đôi. |
| `mayhen_invitation_story_content` | `<p>` | `data-sync-target="story_content"` | **Đoạn văn tự sự kể về tình yêu**, hành trình bên nhau và lời mời từ trái tim. |
| `mayhen_invitation_monogram` | `<div>` | Không | Biểu tượng Monogram chữ cái đầu lồng nhau (Ví dụ: `✦ M & T ✦`). |

---

### 👨‍👩‍👧‍👦 SECTION 3: SONG THÂN & ĐÔI UYÊN ƯƠNG (FAMILY & COUPLE)
> **Container chính**: `<section class="mayhen_invitation_parents mayhen_invitation_couple" data-mayhen-section="groom_family,bride_family">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_parents` | `<section>` | Không | Khung Section Hai bên gia đình & Đôi uyên ương. |
| `mayhen_invitation_family_kicker` | `<div>` | Không | Nhãn phụ: "Hai Họ", "Gia Đình". |
| `mayhen_invitation_family_title` | `<h2>` | Không | Tiêu đề: "Hai Bên Gia Đình". |
| **Nhà Trai** | | | |
| `mayhen_invitation_family_groom_title` | `<h4>` | Không | Nhãn: "Nhà Trai". |
| `mayhen_invitation_family_groom_father` | `<span>` | `data-sync-target="groom_father"` | Họ tên **Cha Chú Rể** (Ví dụ: Ông Nguyễn Văn Hùng). |
| `mayhen_invitation_family_groom_mother` | `<span>` | `data-sync-target="groom_mother"` | Họ tên **Mẹ Chú Rể** (Ví dụ: Bà Lê Thị Thu). |
| `mayhen_invitation_family_groom_address` | `<p>` | `data-sync-target="groom_parents_address"` | Địa chỉ tư gia Nhà Trai. |
| **Nhà Gái** | | | |
| `mayhen_invitation_family_bride_title` | `<h4>` | Không | Nhãn: "Nhà Gái". |
| `mayhen_invitation_family_bride_father` | `<span>` | `data-sync-target="bride_father"` | Họ tên **Cha Cô Dâu** (Ví dụ: Ông Trần Quốc Bảo). |
| `mayhen_invitation_family_bride_mother` | `<span>` | `data-sync-target="bride_mother"` | Họ tên **Mẹ Cô Dâu** (Ví dụ: Bà Phạm Thị Mai). |
| `mayhen_invitation_family_bride_address` | `<p>` | `data-sync-target="bride_parents_address"` | Địa chỉ tư gia Nhà Gái. |
| `mayhen_invitation_family_notice` | `<p>` | Không | Dòng báo tin: *"Trân trọng báo tin lễ thành hôn của con chúng tôi"*. |
| **Chân Dung & Bio Chú Rể** | | | |
| `mayhen_invitation_groom_photo` | `<div>` / `<img>` | `data-sync-target="groom_photo_url"` | Ảnh chân dung riêng của **Chú Rể**. |
| `mayhen_invitation_groom_role` | `<div>` | Không | Nhãn: "Chú Rể". |
| `mayhen_invitation_groom_name` | `<h3>` | `data-sync-target="groom_name"` | Tên Chú Rể. |
| `mayhen_invitation_groom_bio` | `<p>` | `data-sync-target="groom_bio"` | Lời tự bạch ngắn / giới thiệu về Chú Rể. |
| **Chân Dung & Bio Cô Dâu** | | | |
| `mayhen_invitation_bride_photo` | `<div>` / `<img>` | `data-sync-target="bride_photo_url"` | Ảnh chân dung riêng của **Cô Dâu**. |
| `mayhen_invitation_bride_role` | `<div>` | Không | Nhãn: "Cô Dâu". |
| `mayhen_invitation_bride_name` | `<h3>` | `data-sync-target="bride_name"` | Tên Cô Dâu. |
| `mayhen_invitation_bride_bio` | `<p>` | `data-sync-target="bride_bio"` | Lời tự bạch ngắn / giới thiệu về Cô Dâu. |

---

### ⏳ SECTION 4: ĐẾM NGƯỢC & LỊCH THÁNG (COUNTDOWN & CALENDAR)
> **Container chính**: `<section class="mayhen_invitation_countdown_calendar mayhen_invitation_countdown mayhen_invitation_calendar" data-mayhen-section="couple">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_countdown_calendar` | `<section>` | Không | Khung Section Lịch cưới & Đồng hồ đếm ngược. |
| `mayhen_invitation_countdown_kicker` | `<div>` | Không | Nhãn phụ: "Save The Date", "Đếm Ngược". |
| `mayhen_invitation_countdown_title` | `<h2>` | Không | Tiêu đề: "Đếm Ngược & Lịch Cưới". |
| `mayhen_invitation_countdown_desc` | `<p>` | Không | Lời dẫn: *"Từng khoảnh khắc trôi qua là một nhịp đập tiến gần ngày chung đôi"*. |
| **Đồng Hồ Đếm Ngược** | | | |
| `mayhen_invitation_countdown` | `<div>` | `data-countdown="{ISO_DATE}"` | Container đồng hồ đếm ngược thời gian thực (JS tự tính). |
| `mayhen_invitation_cd_day` | `<div>` / `<span>` | Không | Ô hiển thị số **Ngày**. |
| `mayhen_invitation_cd_hour` | `<div>` / `<span>` | Không | Ô hiển thị số **Giờ**. |
| `mayhen_invitation_cd_min` | `<div>` / `<span>` | Không | Ô hiển thị số **Phút**. |
| `mayhen_invitation_cd_sec` | `<div>` / `<span>` | Không | Ô hiển thị số **Giây**. |
| **Tờ Lịch Tháng (Save The Date)** | | | |
| `mayhen_invitation_calendar` | `<div>` | Không | Khung tờ lịch tháng nghệ thuật. |
| `mayhen_invitation_calendar_month_year` | `<p>` | Không | Tiêu đề tháng năm: `Tháng 12 · 2026`. |
| `mayhen_invitation_calendar_selected_day` | `<div>` | Không | Ô ngày cưới được khoanh tròn / đánh dấu tỏa sáng. |
| `mayhen_invitation_add_calendar_btn` | `<a>` | Không | Nút bấm "Thêm vào lịch" (Google Calendar link). |

---

### 📍 SECTION 5: THÔNG TIN SỰ KIỆN & BẢN ĐỒ (EVENTS & MAPS)
> **Container chính**: `<section class="mayhen_invitation_events mayhen_invitation_maps" data-mayhen-section="wedding_event">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_events` | `<section>` | Không | Khung Section Thông tin hôn lễ, tiệc cưới & bản đồ. |
| `mayhen_invitation_events_kicker` | `<div>` | Không | Nhãn phụ: "Events & Venue", "Thư Mời". |
| `mayhen_invitation_events_title` | `<h2>` | Không | Tiêu đề: "Thông Tin Hôn Lễ", "Trân Trọng Kính Mời". |
| **Thư Mời Tiệc Cưới Chính (Wedding Party)** | | | |
| `mayhen_invitation_event_card_party` | `<div>` | Không | Thẻ vé / phong bì chứa thư mời tiệc cưới chính. |
| `mayhen_invitation_guest_name` | `<h3>` | Không | Tên khách quý (Ví dụ: "Quý Khách", "Anh Tuấn & Bạn"). |
| `mayhen_invitation_party_photo` | `<div>` / `<img>` | `data-sync-target="save_date_photo_url"` | Ảnh kỷ niệm kèm thư mời. |
| `mayhen_invitation_party_time` | `<p>` | `data-sync-target="party_time"` | Giờ khai tiệc (Ví dụ: `18:00`). |
| `mayhen_invitation_party_date` | `<div>` | Không | Ngày tiệc cưới (Thứ, Ngày, Tháng, Năm). |
| `mayhen_invitation_party_venue` | `<p>` | `data-sync-target="party_venue"` | Tên sảnh cưới / Trung tâm tiệc cưới (Ví dụ: `Trung tâm Tiệc cưới Ngân Hà`). |
| `mayhen_invitation_party_address` | `<p>` | `data-sync-target="event_party_address"` | **Địa chỉ chính xác tổ chức tiệc cưới**. |
| **Nghi Lễ Gia Tiên (Vu Quy & Thành Hôn)** | | | |
| `mayhen_invitation_ceremony_bride_card` | `<div>` | Không | Thẻ Lễ Vu Quy (Nhà Gái). |
| `mayhen_invitation_ceremony_bride_time` | `<p>` | `data-sync-target="ceremony_time"` | Giờ Lễ Vu Quy (Ví dụ: `08:00`). |
| `mayhen_invitation_ceremony_bride_address` | `<p>` | `data-sync-target="ceremony_address"` | Địa chỉ tổ chức Lễ Vu Quy (Tư gia nhà gái). |
| `mayhen_invitation_ceremony_groom_card` | `<div>` | Không | Thẻ Lễ Thành Hôn (Nhà Trai). |
| `mayhen_invitation_ceremony_groom_time` | `<p>` | `data-sync-target="ceremony_groom_time"` | Giờ Lễ Thành Hôn (Ví dụ: `10:00`). |
| `mayhen_invitation_ceremony_groom_address` | `<p>` | `data-sync-target="ceremony_groom_address"` | Địa chỉ tổ chức Lễ Thành Hôn (Tư gia nhà trai). |
| **Bản Đồ Chỉ Đường 3D (Interactive Google Maps)** | | | |
| `mayhen_invitation_map_box` | `<div>` | Không | Khung chứa cánh cửa và bản đồ Google Maps. |
| `mayhen_invitation_map_toggle_btn` | `<button>` | Không | Nút "Mở bản đồ" hoặc cánh cửa hé mở tương tác. |
| `mayhen_invitation_map_iframe` | `<iframe>` | Không | Khung iframe Google Maps nhúng dẫn đường. |
| `mayhen_invitation_map_address_text` | `<p>` | `data-sync-target="event_party_address"` | Dòng text hiển thị lại địa chỉ dưới bản đồ để quan khách dễ tra cứu. |

---

### ⏱️ SECTION 6: LỊCH TRÌNH HÔN LỄ (WEDDING TIMELINE)
> **Container chính**: `<section class="mayhen_invitation_timeline" data-mayhen-section="timeline">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_timeline` | `<section>` | Không | Khung Section Lịch trình buổi tiệc. |
| `mayhen_invitation_timeline_kicker` | `<div>` | Không | Nhãn phụ: "Timeline", "Chương Trình". |
| `mayhen_invitation_timeline_title` | `<h2>` | Không | Tiêu đề: "Lịch Trình Buổi Tiệc". |
| `mayhen_invitation_timeline_list` | `<div>` | Không | Khung chứa danh sách các mốc thời gian. |
| `mayhen_invitation_timeline_item` | `<div>` | Không | Từng dòng mốc thời gian trong danh sách. |
| `mayhen_invitation_timeline_time` | `<div>` | `data-sync-target="timeline.{index}.time"` | Giờ diễn ra (Ví dụ: `17:30`, `18:00`, `18:30`, `20:00`). |
| `mayhen_invitation_timeline_dot` | `<div>` | Không | Chấm tròn hoặc biểu tượng SVG mốc phát sáng. |
| `mayhen_invitation_timeline_line` | `<div>` | Không | Đường kẻ thẳng nối các mốc thời gian. |
| `mayhen_invitation_timeline_text` | `<div>` | `data-sync-target="timeline.{index}.title"` | Nội dung mốc (Ví dụ: "Đón khách", "Cử hành hôn lễ", "Khai tiệc", "Chụp ảnh kỷ niệm"). |

---

### 👗 SECTION 7: QUY ĐỊNH TRANG PHỤC (DRESSCODE)
> **Container chính**: `<section class="mayhen_invitation_dresscode" data-mayhen-section="dresscode">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_dresscode` | `<section>` | Không | Khung Section Quy định trang phục khách mời. |
| `mayhen_invitation_dresscode_kicker` | `<div>` | Không | Nhãn phụ: "Dresscode", "Trang Phục". |
| `mayhen_invitation_dresscode_title` | `<h2>` | Không | Tiêu đề: "Gợi Ý Trang Phục". |
| `mayhen_invitation_dresscode_icon` | `<div>` | Không | Biểu tượng thời trang / váy cưới & tuxedo SVG. |
| `mayhen_invitation_dresscode_swatches` | `<div>` | Không | Hàng ngang chứa các mẫu màu trang phục. |
| `mayhen_invitation_dresscode_item` | `<div>` | Không | Khối bao bọc 1 ô màu và nhãn tên màu. |
| `mayhen_invitation_dresscode_circle` | `<div>` | Không | Vòng tròn hiển thị màu sắc tương ứng (hex code). |
| `mayhen_invitation_dresscode_label` | `<span>` | Không | Nhãn tên màu (Ví dụ: "Vàng Champagne", "Trắng Ánh Sao", "Xanh Đêm Sâu", "Đen"). |
| `mayhen_invitation_dresscode_note` | `<p>` | `data-sync-target="dresscode_note"` | Lời dặn dò, nhắn nhủ về màu sắc trang phục dự tiệc. |

---

### 📸 SECTION 8: KHOẢNH KHẮC HẠNH PHÚC (ALBUM 10 PHOTOS)
> **Container chính**: `<section class="mayhen_invitation_gallery" data-mayhen-section="album">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_gallery` | `<section>` | Không | Khung Section Album 10 ảnh cưới. |
| `mayhen_invitation_gallery_kicker` | `<div>` | Không | Nhãn phụ: "Album", "Khoảnh Khắc". |
| `mayhen_invitation_gallery_title` | `<h2>` | Không | Tiêu đề: "Khoảnh Khắc Hạnh Phúc". |
| `mayhen_invitation_gallery_wrap` | `<div>` | Không | Khung trình diễn ảnh (Coverflow 3D hoặc Grid). |
| `mayhen_invitation_gallery_item` | `<div>` | `data-index="{0..9}"` | Khung của từng bức ảnh trong album. |
| `mayhen_invitation_gallery_img` | `<div>` / `<img>` | `data-sync-target="gallery.{0..9}"` | **Từng bức ảnh cưới từ 0 đến 9** (Bắt buộc đủ 10 ảnh). |
| `mayhen_invitation_gallery_prev_btn` | `<button>` | Không | Nút trượt xem ảnh trước. |
| `mayhen_invitation_gallery_next_btn` | `<button>` | Không | Nút trượt xem ảnh tiếp theo. |
| `mayhen_invitation_gallery_dots` | `<div>` | Không | Dải chấm tròn phân trang ảnh (10 dots). |
| `mayhen_invitation_lightbox` | `<div>` | Không | Cửa sổ phóng to ảnh toàn màn hình khi người dùng click vào ảnh. |

---

### 🎁 SECTION 9: HỘP MỪNG CƯỚI (WEDDING GIFT & VIETQR)
> **Container chính**: `<section class="mayhen_invitation_gift" data-mayhen-section="gift">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync (`data-sync-target`) | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| `mayhen_invitation_gift` | `<section>` | Không | Khung Section Hộp mừng cưới & QR ngân hàng. |
| `mayhen_invitation_gift_kicker` | `<div>` | Không | Nhãn phụ: "Mừng Cưới", "Wedding Gift". |
| `mayhen_invitation_gift_title` | `<h2>` | Không | Tiêu đề: "Hộp Mừng Cưới". |
| `mayhen_invitation_gift_desc` | `<p>` | Không | Lời tri ân tấm lòng của quan khách. |
| `mayhen_invitation_gift_box` | `<div>` | Không | Hộp quà 3D có nắp mở, chạm để bung mã QR. |
| **Mừng Cưới Chú Rể** | | | |
| `mayhen_invitation_gift_card_groom` | `<div>` | Không | Thẻ thông tin mừng cưới Nhà Trai / Chú Rể. |
| `mayhen_invitation_gift_role_groom` | `<div>` | Không | Nhãn: "Chú Rể". |
| `mayhen_invitation_gift_qr_groom` | `<div>` / `<img>` | `data-sync-target="qr_groom_image"` | Ảnh mã QR VietQR Chú Rể. |
| `mayhen_invitation_gift_bank_name_groom` | `<span>` | `data-sync-target="groom_bank_name"` | Tên ngân hàng Chú Rể (Ví dụ: `Vietcombank`). |
| `mayhen_invitation_gift_account_number_groom` | `<b>` | `data-sync-target="groom_bank_account"` | Số tài khoản ngân hàng Chú Rể. |
| `mayhen_invitation_gift_account_holder_groom` | `<span>` | `data-sync-target="groom_account_name"` | Tên chủ tài khoản Chú Rể (Ví dụ: `NGUYEN MINH TUAN`). |
| `mayhen_invitation_copy_btn` | `<button>` | `data-copy="{STK}"` | Nút "Sao chép số tài khoản". |
| **Mừng Cưới Cô Dâu** | | | |
| `mayhen_invitation_gift_card_bride` | `<div>` | Không | Thẻ thông tin mừng cưới Nhà Gái / Cô Dâu. |
| `mayhen_invitation_gift_role_bride` | `<div>` | Không | Nhãn: "Cô Dâu". |
| `mayhen_invitation_gift_qr_bride` | `<div>` / `<img>` | `data-sync-target="qr_bride_image"` | Ảnh mã QR VietQR Cô Dâu. |
| `mayhen_invitation_gift_bank_name_bride` | `<span>` | `data-sync-target="bride_bank_name"` | Tên ngân hàng Cô Dâu (Ví dụ: `Techcombank`). |
| `mayhen_invitation_gift_account_number_bride` | `<b>` | `data-sync-target="bride_bank_account"` | Số tài khoản ngân hàng Cô Dâu. |
| `mayhen_invitation_gift_account_holder_bride` | `<span>` | `data-sync-target="bride_account_name"` | Tên chủ tài khoản Cô Dâu (Ví dụ: `TRAN THU HA`). |
| `mayhen_invitation_copy_btn` | `<button>` | `data-copy="{STK}"` | Nút "Sao chép số tài khoản". |

---

### ✍️ SECTION 10: TƯƠNG TÁC, RSVP, LƯU BÚT & CẢM ƠN (INTERACTIVE, RSVP, WISHES & ENDING)
> **Container chính**: `<section class="mayhen_invitation_interactive_ending mayhen_invitation_rsvp mayhen_invitation_wishes mayhen_invitation_ending" data-mayhen-section="rsvp,wishes">`

| Class quy chuẩn bắt buộc | Thẻ HTML khuyến nghị | Thuộc tính Live Sync | Ý nghĩa & Dữ liệu hiển thị |
| :--- | :---: | :--- | :--- |
| **10.1 Bảng Ký Tên Điện Tử (Signature Board)** | | | |
| `mayhen_invitation_signature` | `<div>` | Không | Khung bảng ký tên điện tử. Bắt buộc có trong mọi mẫu thiệp. |
| `mayhen_invitation_signature_bg` | `<div>` | Không | Khung nền canvas nghệ thuật (dùng pure CSS gradient, stardust, sticker tem niêm phong seal, tuyệt đối CẤM chèn ảnh cặp đôi). |
| `mayhen_invitation_signature_canvas` | `<canvas>` | Không | Bảng vẽ chữ ký trực tiếp bằng ngón tay / chuột. |
| `mayhen_invitation_signature_open_btn` | `<button>` | Không | Nút "Ký tên của bạn". |
| `mayhen_invitation_signature_save_btn` | `<button>` | Không | Nút "Lưu chữ ký". |
| **10.2 Xác Nhận Tham Dự (RSVP Form)** | | | |
| `mayhen_invitation_rsvp` | `<div>` | Không | Khung form xác nhận tham dự. |
| `mayhen_invitation_rsvp_form` | `<form>` | Không | Form gửi thông tin tham dự qua AJAX. |
| `mayhen_invitation_rsvp_name_input` | `<input>` | `name="name"` | Ô nhập tên khách mời. |
| `mayhen_invitation_rsvp_guest_of` | `<div>` | `data-name="guestOf"` | Tùy chọn "Khách của Cô dâu" hoặc "Khách của Chú rể". |
| `mayhen_invitation_rsvp_attendance` | `<div>` | `data-name="attendance"` | Tùy chọn "Sẽ tham dự" hoặc "Tiếc quá không đến được". |
| `mayhen_invitation_rsvp_count` | `<div>` | Không | Bộ đếm số lượng khách tham dự cùng. |
| `mayhen_invitation_rsvp_submit_btn` | `<button>` | `type="button"` / `submit` | Nút bấm "Xác Nhận Tham Dự". |
| **10.3 Sổ Lưu Bút Chúc Phúc (Guest Book / Wishes)** | | | |
| `mayhen_invitation_wishes` | `<div>` | Không | Khung sổ lưu bút chúc phúc. |
| `mayhen_invitation_wish_form` | `<form>` | Không | Form gửi lời chúc qua AJAX. |
| `mayhen_invitation_wish_name_input` | `<input>` | `name="name"` | Ô nhập họ tên người gửi lời chúc. |
| `mayhen_invitation_wish_visibility` | `<div>` | `data-name="visibility"` | Tùy chọn "Tất cả mọi người" hoặc "Chỉ cô dâu, chú rể". |
| `mayhen_invitation_wish_textarea` | `<textarea>` | `name="message"` | Khung viết lời chúc phúc. |
| `mayhen_invitation_wish_submit_btn` | `<button>` | `type="button"` / `submit` | Nút bấm "Gửi Lời Chúc". |
| `mayhen_invitation_wish_list` | `<div>` | Không | Danh sách hiển thị các lời chúc đã gửi. |
| `mayhen_invitation_wish_item` | `<div>` | Không | Từng ô lời chúc hiển thị tên khách và lời nhắn. |
| **10.4 Lời Cảm Ơn Kết Thiệp (Ending & Thank You)** | | | |
| `mayhen_invitation_ending` | `<div>` | Không | Khung kết thiệp trang trọng cuối trang. |
| `mayhen_invitation_ending_photo` | `<div>` / `<img>` | `data-sync-target="hero_photo_url"` | Ảnh cưới tròn hoặc oval khép lại thiệp. |
| `mayhen_invitation_ending_title` | `<h2>` | Không | Tiêu đề kết: "Thank You" hoặc "Cảm Ơn". |
| `mayhen_invitation_ending_text` | `<p>` | Không | Lời tri ân sâu sắc: *"Sự hiện diện của Quý khách là niềm vinh hạnh cho gia đình chúng tôi"*. |
| `mayhen_invitation_ending_couple_name` | `<p>` | Không | Chữ ký tên đôi uyên ương: `<span data-sync-target="groom_name"></span> & <span data-sync-target="bride_name"></span>`. |

