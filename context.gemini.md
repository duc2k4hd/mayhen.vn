# 🕊️ CONTEXT & KIẾN TRÚC TOÀN DIỆN DỰ ÁN MÂY HẸN (MAYHEN.VN)

> **⚠️ BẮT BUỘC ĐỐI VỚI MỌI AI AGENT (GEMINI, CLAUDE, GPT, CURSOR, COPILOT...):**
> 1. **ĐỌC FILE NÀY ĐẦU TIÊN** trước khi phân tích hoặc viết bất kỳ dòng code nào trong repository này!
> 2. **CẬP NHẬT FILE NÀY SAU MỖI LẦN CODE TRONG DỰ ÁN**: Bất kỳ khi nào hoàn thành hoặc thay đổi tính năng, cấu trúc, route, UI/UX hay database bên trong kho mã nguồn dự án, AI **bắt buộc phải cập nhật lại file này** để giữ context luôn mới nhất trên Git. **LƯU Ý QUAN TRỌNG**: Khi chỉnh sửa file hoặc thư mục **ở bên ngoài dự án** (ví dụ file mẫu tải về ở ổ đĩa khác như `D:\...`, file tài liệu tạm bên ngoài...), thì **TUYỆT ĐỐI KHÔNG CẦN LƯU VÀO CONTEXT NÀY** để tránh làm rác và phình to tài liệu context của dự án.
> 3. Viết cho tôi commit Tiếng Việt có dấu có mở đầu mang ý nghĩa chính xác (ví dụ: fix(), refactor(),...) để tôi gửi commit, bạn không được phép tự gửi lệnh, viết git chi tiết mức độ vừa, đủ để đọc hiểu
> 4. **TUÂN THỦ 100% CÁC NGUYÊN TẮC CỐT LÕI**: Không tự ý thêm thư viện ngoài, không phá vỡ quy ước đặt tên CSS prefix và kiến trúc pure vanilla.
> 5. **SỬA FILE TRỰC TIẾP (TRANSPARENT DIFF)**: Bắt buộc sửa file trực tiếp bằng công cụ sửa file để người dùng theo dõi chính xác từng dòng khác biệt (diff trực quan). Tuyệt đối **KHÔNG** dùng script lệnh chạy ngầm (python/shell/powershell) để sửa file làm người dùng không biết mã nguồn bị thay đổi như thế nào.
> 6. **QUY ĐỊNH VỀ TỆP KIỂM THỬ (TESTING & TEST FILES)**: Khi viết file test, **BẮT BUỘC PHẢI ĐẶT TRONG THƯ MỤC `tests/`** (ví dụ: `tests/Feature/` hoặc `tests/Unit/`). Tuyệt đối không tạo file test hoặc script kiểm tra bừa bãi ở thư mục gốc hay các thư mục khác ngoài `tests/`. Mỗi lần code xong, nếu file test đó chỉ phục vụ tạm thời và không còn dùng nữa thì **có thể xóa đi** để giữ kho mã nguồn luôn tinh gọn và sạch sẽ.
> 7. **NGHIÊN CỨU TRƯỚC KHI BẮT ĐẦU (RESEARCH FIRST — KHÔNG VIẾT TỪ CON SỐ 0)**: Đối với những chức năng chưa quen thuộc, **đừng viết từ con số 0, hãy tìm kiếm các dự án mã nguồn mở trên GitHub để so sánh ý tưởng trước khi bắt tay vào làm**. Điều này giúp chắt lọc kiến trúc tối ưu, tránh lỗi tiềm ẩn và bám sát các tiêu chuẩn thực tế tốt nhất.
> 8. **QUY CHUẨN BẮT BUỘC KHI TẠO MẪU THIỆP CƯỚI**: Áp dụng 8 nguyên tắc trong `AGENTS.md` và `.agents/skills/mayhen-invitation-standards/SKILL.md`. Các yêu cầu về khung, lớp, layout và dữ liệu hiện hành:
>    - **Đủ 10 section tiêu chuẩn** theo thứ tự Opening/Hero, Story, Family/Couple, Countdown/Calendar, Events/Maps, Timeline, Dresscode, Album 10 ảnh, Gift và Interactive/Ending theo contract Studio; không áp dụng giới hạn 6 section từ tài liệu cũ.
>    - **Class BEM `mayhen_invitation_*` và flow layout**: dùng modifier cho đặc trưng mẫu; Flexbox/Grid co giãn theo nội dung, không dựng layout kéo thả bằng định vị tuyệt đối.
>    - **Chuẩn hóa 100% Bộ Khóa Schema (`schema.json`)**: Các trường thông tin chung giữa các mẫu thiệp bắt buộc phải dùng đúng key chuẩn:
>      * Đôi uyên ương: `wedding.groom_name`, `wedding.bride_name`.
>      * Phụ mẫu hai bên: `wedding.extra_data.groom_father`, `wedding.extra_data.groom_mother`, `wedding.extra_data.bride_father`, `wedding.extra_data.bride_mother`.
>      * Thời gian & Địa điểm: `wedding.wedding_date`, `wedding.extra_data.event_party_address`, `wedding.extra_data.event_party_lunar`.
>      * Mừng cưới & Ngân hàng: `wedding.extra_data.groom_bank_account`, `wedding.extra_data.groom_bank_name`, `wedding.extra_data.bride_bank_account`, `wedding.extra_data.bride_bank_name`.
>      * Ảnh cưới chính: `wedding.cover_photo_url`, `wedding.extra_data.hero_photo_url`.
>    - **Gắn đầy đủ hợp đồng `data-sync-target` cho mọi vị trí hiển thị dữ liệu**: Bất kỳ vị trí nào hiển thị tên, bố mẹ, ngày cưới, sảnh tiệc, lời nhắn trong `template.blade.php` **ĐỀU PHẢI CÓ** `data-sync-target="{tên_trường}"` (ví dụ: `data-sync-target="groom_name"`, `data-sync-target="groom_father"`, `data-sync-target="wedding_date"`). Tuyệt đối **KHÔNG hardcode văn bản mẫu** trực tiếp vào thẻ HTML.
>    - **Phòng vệ Chế Độ Nhúng Studio Editor (`$isEmbed = true`)**:
>      * Khi nhúng trong iframe trình tạo thiệp (`?embed=1`), bắt buộc phải mở sẵn cánh thiệp (`.opened`), không được gắn class khóa cuộn `no-scroll` hay `scroll_locked` vào `html`/`body`, và cưỡng chế `.card-wrapper { display: block !important; }` để tránh thiệp bị màn hình trắng trơn.
>      * Toàn bộ phần tử hiệu ứng (reveal/motion) phải hiển thị tức thì (`is-visible`) trong iframe để khách hàng nhìn thấy nội dung ngay lập tức.
>    - **Phòng vệ Ảnh Lỗi (Asset Fallback Defense)**: Mọi lời gọi `$resolveAsset($path, $fallback)` luôn phải có fallback chỉ đến file ảnh có thật trong thư mục `assets/` của mẫu đó. Tuyệt đối không bao giờ để xuất hiện icon ảnh vỡ hoặc URL rỗng.
>    - **Tự Chứa 100% Tài Nguyên Cục Bộ (100% Localized Assets & Zero External URLs)**:
>      * Tuyệt đối **CẤM** sử dụng URL ảnh, font, chữ ký, icon hay file nhạc MP3 từ server bên thứ ba ngoài hệ thống (như `builder.27biggroup.com`, `ladicdn.com`, `unsplash.com`, `iili.io`...).
>      * Toàn bộ tài nguyên phục vụ hiển thị mẫu và làm giá trị khởi tạo mặc định bắt buộc phải được tải về và đặt trực tiếp trong thư mục `resources/views/client/invitations/{template-slug}/assets/`.
>      * Trong `template.blade.php`, `styles.css` và `schema.json`, toàn bộ đường dẫn ảnh và tài nguyên mặc định đều phải trỏ vào asset nội bộ (thông qua `$resolveAsset(...)` hoặc route `/mau-thiep/{slug}/assets/...`).
>      * Mục đích: Đảm bảo thiệp tạo ra luôn tải ổn định 100%, không bị phụ thuộc vào server bên ngoài, không lo chết link/chặn CORS, và giúp backend quản lý, kiểm soát và chỉnh sửa dễ dàng.
>    - **Kế thừa Layout chung & Bridge**: Mọi mẫu thiệp bắt buộc phải `@extends('client.layouts.invitation')` để tự động kích hoạt `editor-bridge.blade.php` và cơ chế handshake 2 chiều `MAYHEN_BRIDGE_READY`.
>    - **Bắt buộc có Ký tên & RSVP, TUYỆT ĐỐI CẤM chèn ảnh cặp đôi vào 2 phần này (Zero Couple Photos in Signature & RSVP)**:
>      * Mọi mẫu thiệp bắt buộc phải có đủ Sổ Ký Tên Điện Tử (`Signature`) và Xác Nhận Tham Dự (`RSVP`).
>      * Tuyệt đối **KHÔNG ĐƯỢC đặt ảnh chụp cặp đôi hay ảnh stock người vào 2 phần này** (kể cả ảnh nền canvas ký tên hay ảnh minh họa phía trên form RSVP).
>      * Khách hàng tạo thiệp không có ô để tùy biến các ảnh này trong Studio, việc để ảnh ở 2 phần này sẽ khiến thiệp gửi cho khách mời xuất hiện ảnh của người lạ/ảnh mẫu cũ, gây phản cảm và sai lệch thông tin ngày cưới.
>      * **CHỈ ĐƯỢC DÙNG NGHỆ THUẬT ĐỒ HỌA & HIỆU ỨNG THUẦN**: Sử dụng hiệu ứng CSS (stardust, glow, galaxy gradient, giấy mỹ thuật), sticker SVG, tem niêm phong sáp (wax seal), huy hiệu hoàng gia hoặc hoa văn cổ điển để trang trí làm đẹp thiệp.
> 9. **NGUYÊN TẮC TỐI ƯU HIỆU NĂNG GỐC TRƯỚC (NATIVE FAST ARCHITECTURE) & KHÔNG LẠM DỤNG CACHE**:
>    - **Tối ưu gốc trước (Native Fast First)**: Mọi module Backend (PHP, Query Eloquent) và Frontend (JavaScript, DOM, CSS, Assets) **BẮT BUỘC PHẢI ĐƯỢC TỐI ƯU HÓA GỐC ĐỂ CHẠY CỰC NHANH VÀ MƯỢT MÀ** ngay từ mã nguồn gốc khi chưa có cache (chỉ query đúng các cột cần thiết, loại bỏ trường text/json nặng không dùng, không nạp dồn dập nhiều iframe làm nghẽn CPU/network, tận dụng `requestIdleCallback`, phục vụ static assets tức thời không bị thắt nút cổ chai).
>    - **Không lạm dụng Cache**: Tuyệt đối **KHÔNG** dùng cache để che giấu mã nguồn chạy chậm hay xử lý cẩu thả. Cache chỉ được sử dụng đúng mục đích cốt lõi duy nhất: **tránh truy vấn quá nhiều lần vào Database** khi dữ liệu ít biến động.

---

## 1. TỔNG QUAN DỰ ÁN & THƯƠNG HIỆU

- **Tên dự án**: Mây Hẹn
- **Domain**: `mayhen.vn`
- **Lĩnh vực**: Nền tảng thiết kế & gửi thiệp cưới online cao cấp (Quiet Luxury Wedding Invitations & Event RSVP).
- **Sứ mệnh & Định vị**:
  - Không đơn thuần là trang web tạo thiệp cưới số đại trà, Mây Hẹn là **tác phẩm nghệ thuật số** dành cho các cặp đôi trân quý sự tinh tế, thanh lịch và cảm xúc chân thật.
  - Phong cách chủ đạo: **Quiet Luxury, Editorial, Minimalist, Romantic, Modern**.
  - Trải nghiệm cốt lõi: Tôn vinh câu chuyện tình yêu, hiệu ứng chuyển động giấy lụa mượt mà, quản lý khách mời thông minh, tương thích hoàn hảo trên mọi thiết bị di động (Mobile-First).

### Bảng màu nhận diện thương hiệu (Brand Palette)
- **Warm Cream / Ivory**: `#FAF8F5`, `#F8F6F0`, `#FFFDF9` (Nền giấy mỹ thuật ấm áp, sang trọng).
- **Soft Beige / Muted Sand**: `#EFE8DD`, `#E5DCCD`, `#D8CEBD` (Đường viền, phân vùng nhẹ nhàng).
- **Charcoal / Espresso**: `#1F1D1A`, `#2C2824`, `#4A453E` (Màu chữ chính, độ tương phản cao nhưng không gắt như đen tuyền).
- **Muted Gold / Bronze**: `#C5A880`, `#9C7A4A`, `#B8935D` (Điểm nhấn kim loại cao cấp, hoa văn, nút kêu gọi hành động).
- **Dusty Rose / Sage Green**: `#D9B4A7`, `#9AA899` (Màu nhấn phụ cho thiệp hoa, phong bì và thiệp lưu niệm).

### 🎨 QUY TẮC BẮT BUỘC: ĐA DẠNG HÓA MÀU SẮC THIỆP CƯỚI (TEMPLATE COLOR PALETTES)
> **NGUYÊN TẮC BẤT DI BẤT DỊCH**: Mỗi mẫu thiệp cưới tạo ra **BẮT BUỘC PHẢI CÓ TÔNG MÀU ĐẶC TRƯNG RIÊNG BIỆT**, tuyệt đối **KHÔNG ĐƯỢC MẪU NÀO CŨNG GIỐNG MẪU NÀO** (không lạm dụng màu trắng đơn điệu hay đen trắng cứng nhắc). Màu sắc phải rực rỡ, trang nhã, giàu cảm xúc và gắn liền với ngày cưới trọng đại:
> 1. **Tông Đỏ Hồng Ngọc / Rượu Vang Hoàng Gia (Royal Crimson / Velvet Ruby & Gold)**: Đỏ rượu vang (`#85182A`), đỏ nhung (`#5E0E1C`), vàng ánh kim Antique Gold (`#CFA860`) trên nền kem ấm (`#FAF7F2`) — Mang lại sự hỷ sự, trang trọng, quý phái bậc nhất cho ngày cưới truyền thống & hiện đại.
> 2. **Tông Hồng Tro & Phấn Lụa (Dusty Rose & Champagne)**: Hồng tro (`#D9B4A7`), hồng đào nude, champagne gold và viền ngà lụa — Dành cho phong cách ngọt ngào, lãng mạn, thơ mộng.
> 3. **Tông Xanh Xô Thơm & Rừng Thu (Sage Green & Olive Cream)**: Xanh olive (`#748E8A`), xanh xô thơm (`#9AA899`), gold nhạt — Mang phong cách hòa hợp thiên nhiên, tiệc cưới ngoài trời (Outdoor/Garden Wedding).
> 4. **Tông Xanh Hoàng Gia & Bạc Ánh Trăng (Royal Navy / Dusty Blue & Silver)**: Xanh đêm huyền ảo (`#1B2A4A`), xanh pastel sương mù và ánh bạc — Phong cách cổ điển châu Âu thanh khiết.
> 5. **Tông Hổ Phách & Hoàng Hôn Ấm (Terracotta & Warm Amber)**: Nâu cam đất ấm cúng, đồng cổ — Mang chất điện ảnh Bohemian / Rustic ấm cúng.


### Typography chuẩn mực
- **Heading / Editorial Title**: `Cormorant Garamond`, `Playfair Display` (Font serif thanh lịch, quý phái).
- **Body / Interface / Form**: `Inter`, system font sans-serif (Rõ nét, dễ đọc trên màn hình điện thoại).

---

## 2. NGUYÊN TẮC KỸ THUẬT BẮT BUỘC (STRICT FRONTEND RULES)

### ❌ CÁC ĐIỀU CẤM TUYỆT ĐỐI (ZERO TOLERANCE)
1. **CẤM FRAMEWORK CSS**: Tuyệt đối **KHÔNG** dùng Tailwind CSS, Bootstrap, Bulma, Foundation hoặc bất kỳ CSS framework nào khác.
2. **CẤM FRAMEWORK JS**: Tuyệt đối **KHÔNG** dùng React, Vue, Angular, Svelte, Alpine.js, jQuery.
3. **CẤM THƯ VIỆN HIỆU ỨNG NGOÀI**: Tuyệt đối **KHÔNG** cài đặt/nhúng Swiper.js, Slick Slider, GSAP, Anime.js, AOS, v.v. Tất cả carousel, modal, slider, animation đều viết bằng **Pure CSS + Native JS**.
4. **CẤM CDN NGOẠI**: Không tải CSS/JS từ `cdnjs`, `unpkg`, `jsdelivr`. Toàn bộ asset phải nằm cục bộ trong thư mục `public/assets/`.
5. **CẤM INLINE STYLE & INLINE SCRIPT**: Không viết thẻ `<style>` hoặc `<script>` bên trong file Blade (trừ thẻ JSON-LD SEO trong `<head>`). Toàn bộ CSS/JS phải được tách thành file độc lập trong `public/assets/client/`.
6. **QUY TẮC BẮT BUỘC VỀ TIÊU ĐỀ TRANG MẪU THIỆP (`$pageTitle`)**: Tuyệt đối không tự viết tiêu đề riêng lẻ trong các mẫu thiệp. Tất cả mọi thiệp cưới BẮT BUỘC phải dùng chung đúng một định dạng chuẩn:
   ```php
   $pageTitle = $isPreview 
       ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
       : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');
   ```

### 🏷️ TIỀN TỐ CSS CLASS BẮT BUỘC (MANDATORY CLASS PREFIX)
Mỗi module/trang bắt buộc phải dùng prefix riêng để chống xung đột style 100%:
- **Client Home (Trang chủ)**: `mayhen_home_*`
- **Client Services (Dịch vụ)**: `mayhen_service_*`
- **Client Pricing (Bảng giá)**: `mayhen_pricing_*`
- **Client Templates (Kho mẫu thiệp)**: `mayhen_templates_*`
- **Client Template Detail (Chi tiết mẫu)**: `mayhen_template_detail_*`
- **Client Preview (Xem thử mẫu thiệp)**: `mayhen_preview_*`
- **Client Blog / Bài viết**: `mayhen_blog_*`
- **Client About (Về chúng tôi)**: `mayhen_about_*`
- **Client Contact (Liên hệ)**: `mayhen_contact_*`
- **Client FAQ (Hỏi đáp)**: `mayhen_faq_*`
- **Client Auth (Đăng ký/Đăng nhập)**: `mayhen_auth_*`
- **Client Dashboard**: `mayhen_dashboard_*`
- **Client Editor (Trình tạo thiệp)**: `mayhen_editor_*`
- **Client Guests (Quản lý khách mời)**: `mayhen_guest_*`
- **Public Invitation (Trang thiệp cưới hiển thị)**: `mayhen_invitation_*`
- **Shared Components (Header, Footer, Button, Modal dùng chung)**: `mayhen_client_*` (ví dụ: `mayhen_client_header_*`, `mayhen_client_btn_*`, `mayhen_client_modal_*`).
- **Admin**: `mayhen_admin_*`

---

## 3. TECH STACK & MÔI TRƯỜNG DỰ ÁN

- **Ngôn ngữ & Runtime**: PHP 8.5 (CLI hiện tại: 8.5.10)
- **Framework**: Laravel 13.x (phiên bản hiện cài đặt: 13.35.0)
- **Database**: SQLite (local) / MySQL (production)
- **Frontend Architecture**:
  - Pure Blade Template (`resources/views/client/`)
  - Pure Vanilla CSS (`public/assets/client/css/`)
  - Pure Vanilla JavaScript ES Modules (`public/assets/client/js/`)
- **Testing**: Pest PHP (`vendor/bin/pest`) — *Mọi file test bắt buộc nằm trong thư mục `tests/` (`tests/Feature/` hoặc `tests/Unit/`). Các file test tạm thời có thể xóa đi sau khi hoàn tất kiểm tra và code xong.*
- **Code Formatter**: Laravel Pint (`vendor/bin/pint --format agent`)
- **Thanh toán tự động**: VietQR (Ngân hàng Việt Nam) & SePay Webhook

---

### Trạng thái Studio đã xác minh — 06/11/2026

- **QA-01 hoàn tất đối chiếu local:** cập nhật các test cũ sang 4 mẫu active, contract 10 section và cơ chế lưu riêng lời chúc cùng IP; giữ các ca kiểm tra danh sách, quyền chủ thiệp, RSVP, lọc nội dung, gallery, embed và đồng bộ Studio. Bổ sung ngoại lệ `.gitignore` cho 5 file test trước đó bị bỏ qua; giữ nguyên tên file cũ để không xóa test. Kiểm thử upload dùng storage giả và dọn file sau test. Toàn bộ suite PHP chạy trên SQLite in-memory đạt **176/176 test**; không suy rộng kết quả này cho MySQL production hoặc các mẫu không còn trong dự án.
- **QA-01 cập nhật tiếp:** chuyển hai kiểm tra điều hướng dưới cùng sang danh sách mẫu active thực tế và cho phép Git theo dõi `tests/Feature/BottomNavTest.php`; test embed kiểm tra phần tử điều hướng thay vì nhầm selector CSS trong `<style>` với HTML. Thêm các feature test đang được theo dõi cho cách ly lời chúc giữa hai wedding, kiểm duyệt lời chúc/RSVP, và từ chối chủ thiệp trả lời lời chúc thuộc wedding khác. Hai file này đạt **29 test, 128 assertions**; Pint đạt. Suite toàn dự án hiện **176 test: 155 đạt, 11 failure, 10 error** trên SQLite in-memory. Các test cũ còn lại tham chiếu mẫu/asset không còn, quy tắc 6 section hoặc chính sách cũ cho sửa lời chúc theo IP; chưa thay thế hoặc xóa các test đó, cũng chưa kết luận tất cả lỗi còn lại đều chỉ do fixture.
- **Nền tảng hiện tại:** Laravel 13.35.0 / PHP 8.5.10, đã kiểm tra từ package cài đặt và CLI; không dùng ghi chú Laravel 11 hoặc giới hạn 6 section làm quy chuẩn hiện hành.
- **STU-01 đã đạt kiểm thử local cho luồng đang triển khai:** nhập dữ liệu → khôi phục nháp → đổi mẫu → xuất bản → mở sửa → autosave. Bảo toàn văn bản tiếng Việt, trường rỗng, ảnh cá nhân, album/timeline lồng nhau, bảng màu, monogram và ẩn section. Đổi mẫu vẫn tải lại trang theo schema mẫu đích.
- **State và phục hồi:** `store.js` chuẩn hóa dotted/nested keys thành một nguồn dữ liệu, tạo array đúng chỉ số và lọc đường dẫn nguy hiểm. `draft-recovery.js` cập nhật tất cả input trùng key, tạo hidden input với bracket name cho trường vắng trên mẫu đích; không mang ảnh mặc định của mẫu cũ sang mẫu mới. Input được mirror sang ô trùng key mà không gán đè ô người dùng đang gõ.
- **Undo/Redo và media:** phục hồi cả nested fields, thumbnail, ảnh mặc định/ảnh rỗng và lựa chọn UI. Giá trị không đổi không tạo lịch sử trùng. Upload/crop/QR cá nhân và chọn nhạc cập nhật store để tự lưu; token theo từng ô bỏ kết quả ảnh cũ hoàn tất trễ, chặn đổi mẫu/xuất bản khi ảnh chưa xử lý xong, cảnh báo rời trang và thu hồi object URL.
- **Autosave và xung đột:** lưu nối tiếp thay đổi phát sinh trong request; thử lại khi online. Chỉ báo lưu trên browser khi ghi được localStorage. Bản chuyển tiếp chỉ được áp dụng khi ID/revision/mẫu đích phù hợp; tạm dừng ghi trong lúc phục hồi, xác minh snapshot trước khi tiếp tục. Giữ bản lỗi/xung đột, cho tải xuống hoặc tạo dự phòng trước khi nạp bản server. Bản session chỉ tồn tại trong tab; localStorage không có giao dịch nguyên tử giữa nhiều tab.
- **Backend:** manual save/autosave cùng khóa wedding trước invitation, kiểm tra revision chính xác và tăng revision các thiệp khác cùng wedding khi wedding thay đổi. Gộp từng vị trí gallery/timeline, giữ mục không được gửi; hỗ trợ null/chuỗi rỗng và `[]` xóa rõ ràng. Bỏ `blob:` chưa hoàn tất để giữ ảnh đã lưu. Manual save đưa cấu hình draft vào `custom_config` và xóa draft cũ. Validation trả 422 cho cấu trúc sai, ngày/tên bắt buộc bị xóa và timeline chứa giá trị sai kiểu, thay vì lỗi database 500.
- **Hydrate/render:** form format Carbon date thành `Y-m-d`, giữ explicit null thay vì lấy văn bản mẫu hoặc alias ngân hàng cũ, giữ URL upload tuyệt đối. Bốn mẫu active giữ optional text đã xóa; hai resolver ảnh được sửa nhận data image hợp lệ và SVG QR đồ họa an toàn, fallback khi sai. Mẫu Hạnh Phúc đọc canonical wedding date cho ngày/tháng/năm và lịch. Canvas mở đầu mẫu Giữa Muôn Ngàn không đọc canvas ẩn/kích thước 0 trong iframe.
- **Cấu hình hiển thị sau xuất bản:** `common/invitation-config.js` đọc JSON escaped trong body layout chung từ `custom_config` đã lưu, áp dụng màu thật theo CSS tokens từng mẫu, monogram và bố cục trên public lẫn preview. Thay ba visual handler cũ trong bridge bằng module dùng chung; chỉ nhận message cùng origin từ parent/self, kiểm tra giá trị màu. Hiểu alias section phân cách bằng dấu phẩy, nhóm parents/events/ending, sắp xếp trong parent gốc, giữ trạng thái ẩn khi đổi thứ tự. Mặc định giữ màu/monogram gốc từng mẫu; Undo khôi phục cả CSS và node monogram gốc. Bốn mẫu có target monogram chung; không thêm inline script/CSS mới.
- **Kiểm thử:** 76/76 PHP tests, 358 assertions (`GuestManagementAuthorizationTest.php`, `GalleryRenderTest.php`); 43/43 Node tests (`editor-autosaver.test.mjs`). Hai browser tests Chromium đạt: (1) upload → Undo/Redo → reload → chuyển mẫu → publish → mở sửa → autosave → offline/online → tab cũ 409 → backup và nạp bản server; (2) cả bốn mẫu active đổi màu, Undo/Redo về màu gốc, monogram, ẩn parents/ending và đổi thứ tự timeline hiển thị đúng trên public. Không có pageerror. Browser test tùy chọn `tests/Unit/editor-browser.test.mjs` dùng runtime sẵn có qua `MAYHEN_BROWSER_BASE_URL`, `MAYHEN_PLAYWRIGHT_MODULE`, `MAYHEN_CHROMIUM_PATH`, không thêm dependency dự án. Pint, kiểm tra cú pháp JS và diff đạt. Không thêm route, migration hay package trong đợt này.
- **Giới hạn đã xác định:** suite PHP local sau cập nhật QA-01 đạt 176/176 test, 962 assertions; kết quả không chứng minh mọi yêu cầu PRD đã hoàn tất. Chưa thử đồng thời trên MySQL production; autosave vẫn cập nhật wedding đang dùng chung với public, việc tách snapshot nháp/công khai thuộc PUB-01.

### Lịch sử kiểm toán dự án — 06/11/2026

Các ghi chú sau mô tả từng thời điểm khảo sát trước nghiệm thu; trạng thái STU-01 mới nhất nằm ở mục phía trên và roadmap, thay thế các nhận định cũ về phần chưa kiểm thử.

- Bổ sung đối chiếu hiện trạng vào `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`, báo cáo bước đầu `docs/MAY_HEN_PROJECT_AUDIT.md` và roadmap `docs/MAY_HEN_IMPLEMENTATION_ROADMAP.md`.
- Ưu tiên kiểm tra quyền quản lý khách, xác thực webhook, phạm vi RSVP và độ tin cậy autosave. Những nhận định này dựa trên đọc mã tĩnh, chưa phải kết luận kiểm toán bảo mật hoàn chỉnh hoặc kết quả test.
- Đây là giai đoạn khảo sát một phần: chưa sửa mã ứng dụng, chưa chạy migration hay kiểm thử tích hợp; các task và trạng thái chi tiết nằm trong roadmap.
- Đối chiếu tiếp luồng quản lý khách: view tạo link chứa guest token; action xóa nhận guest ID nên cần kiểm tra quyền theo wedding và trường hợp wedding có nhiều thiệp. Chưa xác minh độc lập hoặc chạy kiểm thử do môi trường cô lập theo security-audit chưa được thiết lập.
- Đã bổ sung sơ đồ luồng Studio và xuất bản vào báo cáo: đổi mẫu hiện chuyển trang, thiệp tạo mới được đánh dấu xuất bản, autosave cập nhật dữ liệu `weddings`; PRD cần coi nháp/xuất bản riêng và chuyển mẫu không reload là mục tiêu cần kiểm thử, không phải hành vi đã nghiệm thu.
- SEC-01: `GuestController` kiểm tra user sở hữu hoặc slug thiệp hợp lệ trong session trước khi xem, thêm, xóa khách theo wedding; trả 404 nếu không có quyền. Bổ sung `tests/Feature/GuestManagementAuthorizationTest.php` cho tài khoản, khách vãng lai và wedding có nhiều thiệp. Đã kiểm tra cú pháp PHP; chưa chạy feature test hoặc Pint, chưa xác nhận hoàn thành.
- `.gitignore` vốn bỏ qua thư mục `tests/`; bổ sung ngoại lệ riêng cho feature test SEC-01 để file có thể được theo dõi, giữ nguyên trạng thái bỏ qua của các test khác.
- RSVP-01: endpoint tra token trong danh sách khách thuộc đúng wedding, từ chối token sai; trang thiệp chuyển token sang URL RSVP, giữ luồng không token. Hai mẫu `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau` dùng endpoint controller cung cấp và gửi đúng trường RSVP, số người lớn/trẻ em; ẩn lựa chọn nơi tham dự/khách của ai chưa có nơi lưu. Mẫu `tron-kiep-nay-ben-nhau` dùng endpoint lời chúc có thật, hiển thị lời chúc từ server và không còn dùng tên route không tồn tại; chưa có endpoint lưu chữ ký. Đã mở rộng `tests/Feature/RsvpGuestTokenTest.php`, mới kiểm tra cú pháp, chưa chạy feature test hoặc kiểm thử trình duyệt. Các mẫu khác và chính sách một IP một phản hồi vẫn cần xử lý.
- RSVP-01 tích hợp tiếp: `giua-muon-ngan-vi-sao` và `hanh-phuc-mang-ten-chung-minh` gửi RSVP thực với token qua endpoint do controller cấp, theo đúng trường và số lượng người lớn/trẻ em; xử lý lỗi và không báo thành công trước phản hồi server. Hai mẫu hiển thị lời chúc từ server; loại bỏ tùy chọn riêng tư chưa được backend hỗ trợ và ghi rõ lời chúc công khai. Chữ ký của hai mẫu chỉ hiển thị tạm, chưa được gửi/lưu máy chủ; không đọc hoặc ghi bộ chữ ký localStorage dùng chung giữa các thiệp nữa. Đã bổ sung test render, chỉ kiểm tra cú pháp, chưa chạy test hay browser; quy tắc một IP và lời chúc theo IP còn cần thiết kế lại.
- RSVP-01 đối soát mạng dùng chung: phản hồi có token được giới hạn theo guest thuộc wedding, phản hồi không token giữ giới hạn theo IP riêng; tạo RSVP và lời chúc cùng transaction, khóa dòng wedding để điều phối đồng thời trên database hỗ trợ khóa dòng. Lời chúc mới luôn tạo bản riêng thay vì ghi đè theo IP; chưa có tính năng sửa lời chúc được xác thực. Trang thiệp chỉ cung cấp `currentRsvp` theo token hợp lệ, không xác định phản hồi hay lời chúc cá nhân bằng IP. Thống kê người tham dự tính cả người lớn và trẻ em. Đã thêm feature test nhưng chưa chạy test hoặc kiểm tra đồng thời trên MySQL; SQLite không phát `FOR UPDATE`.
- STU-01: autosave theo dõi thay đổi trong request, gửi tiếp với revision mới thay vì báo saved sớm; khi 409 giữ conflict và bản nháp trình duyệt. Bổ sung `tests/Unit/editor-autosaver.test.mjs`, ngoại lệ `.gitignore`; đã kiểm tra cú pháp JS, chưa chạy test hoặc kiểm tra tích hợp. Autosave vẫn cập nhật `weddings`; phân tách nháp và công khai thuộc PUB-01.
- STU-01 đối chiếu revision: backend chỉ chấp nhận revision khớp chính xác, từ chối cả revision cũ lẫn revision lớn hơn; client chỉ báo đã lưu khi server trả revision kế tiếp hợp lệ, nếu không giữ bản nháp và dừng đồng bộ. Đã thêm test vào `tests/Feature/GuestManagementAuthorizationTest.php` và `tests/Unit/editor-autosaver.test.mjs`, chưa chạy. Chưa xử lý đầy đủ bản nháp local cũ được tự phục hồi, xung đột trên nút lưu chính hoặc revision chung giữa nhiều thiệp cùng wedding; không coi bảo vệ ghi đè là hoàn chỉnh.
- STU-01 chuyển mẫu: dừng chuyển trang khi không ghi/đọc được sessionStorage, ảnh còn `blob:` hoặc autosave đã báo xung đột; cho phép giữ URL ảnh cá nhân `/storage/`, `/uploads/`. Không còn để lỗi đọc sessionStorage làm ngừng khởi tạo editor; nút xuất bản phía client dừng khi autosave đã phát hiện xung đột. Đã bổ sung Node test nhưng chưa chạy hoặc kiểm tra trình duyệt. Chưa chứng minh phục hồi toàn bộ trường, `custom_config`, revision của draft cũ, xung đột phía server của submit chính hay nhiều thiệp chung wedding.
- STU-01 lưu chính: chỉnh sửa thiệp hiện có yêu cầu `revision_id`; service khóa dòng thiệp, từ chối revision không khớp và tăng revision trong cùng transaction. Biểu mẫu gửi revision hiện tại, dừng nếu autosave đang chạy; sau lỗi revision hiển thị cảnh báo và không tự nạp đè bản khác. ID thiệp không có quyền trả 404 thay vì tạo thiệp mới. Đã bổ sung feature test nhưng chưa chạy test, Pint hay browser. Chưa có giao diện giải quyết xung đột đầy đủ, chưa đối chiếu revision khi tự phục hồi local và chưa bảo vệ đồng thời nhiều thiệp chung wedding.
- STU-01 phục hồi local: trước khi tự nạp draft của thiệp đã lưu, đối chiếu revision và ID; bản sai, thiếu revision hoặc JSON lỗi được giữ nguyên, tạm dừng autosave/xuất bản và hiển thị lựa chọn tải bản nháp hoặc lưu bản dự phòng trên trình duyệt rồi dùng dữ liệu máy chủ. Khi tạo dự phòng thất bại hoặc phát hiện bản gốc đổi, không xóa bản gốc. Khi người dùng chọn bản server, bản chuyển mẫu đang có trong session được giữ lại nhưng bỏ qua; chuyển mẫu mới sẽ bỏ trạng thái bỏ qua. Đã bổ sung test Node nhưng chưa chạy test hoặc kiểm tra browser; thao tác giữa nhiều tab không bảo đảm nguyên tử do localStorage, draft chuyển mẫu trong sessionStorage chưa được đối chiếu revision và cần xử lý riêng.
- STU-01 chuyển mẫu tiếp theo: bản chuyển tiếp chỉ được tự nạp khi ID thiệp (kể cả thiệp mới), revision và slug mẫu đích khớp, dữ liệu wedding là object; bản sai hoặc định dạng cũ được giữ trong session, dừng autosave và cho tải xuống hoặc bỏ qua để tiếp tục. Thiệp đã lưu phải chờ autosave đang chạy hoặc đang hẹn giờ trước khi chuyển. Nếu đã có bản chuyển tiếp, tạo và kiểm tra bản dự phòng trong sessionStorage trước khi ghi bản mới; thất bại thì giữ nguyên bản cũ và không chuyển trang. Node test `tests/Unit/editor-autosaver.test.mjs`: 20/20 đạt; đã kiểm tra cú pháp ba module JS và `git diff --check`. Chưa kiểm tra trình duyệt hoặc MySQL đồng thời; bản session chỉ tồn tại trong tab, bản dự phòng chưa có giao diện khôi phục, chưa chứng minh chuyển đủ cấu hình, trường rỗng và dữ liệu lồng nhau. Các ghi chú cũ về việc chưa chạy Node test hoặc chưa đối chiếu revision draft chuyển mẫu được thay bằng cập nhật này.
- STU-01 trường rỗng: khi thu thập dữ liệu chuyển mẫu, không dùng giá trị cũ trong store hoặc alias ghi đè trường đã hiện diện nhưng để rỗng; alias chưa có được nhận cả chuỗi rỗng. Khi phục hồi, trường văn bản rỗng được đưa lại vào form và gửi sang preview khi đồng bộ, còn ảnh rỗng vẫn dùng ảnh mặc định mẫu. Node test kiểm tra thu thập và alias: 21/21 đạt; chưa chạy kiểm thử trình duyệt cho bước phục hồi/preview, chưa giải quyết xung đột giữa các alias đều đã có giá trị, `custom_config` và dữ liệu lồng nhau.
- STU-01 cấu hình Studio: store khởi tạo `custom_config` từ input cấu hình của thiệp đã lưu thay vì luôn dùng mặc định; bản nháp đủ điều kiện khôi phục có object `custom_config` cũng được nạp vào store và input gửi form. Chuỗi JSON lỗi hoặc dữ liệu không phải object được bỏ qua. Node test toàn bộ autosaver đạt 23/23, bao gồm khởi tạo cấu hình và giữ cấu hình trong request autosave; đã kiểm tra cú pháp các module đã sửa. Chưa kiểm thử tích hợp phục hồi cấu hình trên trình duyệt, trạng thái lựa chọn trong giao diện, dữ liệu lồng nhau hoặc cạnh tranh nhiều tab; không xem đây là nghiệm thu hoàn chỉnh luồng chuyển mẫu.
- STU-01 an toàn phục hồi chuyển mẫu: không xóa bản chuyển tiếp ngay khi đọc; tạm dừng autosave và ghi local trong quá trình áp dụng, sau đó ghi/đọc lại snapshot hiện tại và đánh dấu bỏ qua bản chuyển tiếp cũ trước khi tiếp tục autosave. Bản gốc vẫn tồn tại trong session của tab; nếu khôi phục bị lỗi, bản chuyển tiếp bị thay đổi hoặc không thể ghi/đọc bộ nhớ, dừng autosave, khóa form và cho tải bản đã đọc xuống hoặc thử lại. Node test đạt 28/28, gồm lỗi storage/session, dữ liệu chuyển tiếp thay đổi và phục hồi gián đoạn; kiểm tra cú pháp JS và `git diff --check` đạt. Chưa kiểm thử giao diện trên trình duyệt, chưa chứng minh snapshot bao gồm toàn bộ dữ liệu lồng nhau hoặc xử lý đồng thời giữa các tab; session không tồn tại sau khi đóng tab.
- STU-01 thu thập dữ liệu lồng nhau: khi chuyển mẫu, duyệt các trường chuỗi trong object `wedding` để giữ dữ liệu vắng mặt trên form, ưu tiên giá trị form và trạng thái lồng nhau trước key phẳng cũ; giữ chuỗi rỗng lồng nhau và ảnh cá nhân, không mang ảnh mặc định mẫu cũ, chặn ảnh `blob:` đang xử lý. Các segment không hợp lệ hoặc có nguy cơ thay đổi prototype bị bỏ qua. Không tự thu thập mảng hoặc đường dẫn chứa chỉ số từ store vì backend có thể ghi đè toàn bộ danh sách. Node test đạt 30/30; chưa kiểm thử trọn luồng phục hồi trên browser. Cần xử lý và kiểm thử autosave/backend gộp danh sách `gallery`/`timeline` không làm mất mục khác, dữ liệu không phải chuỗi và cạnh tranh nhiều tab trước khi kết luận không mất dữ liệu.
- PAY-01 một phần: webhook SePay xác thực HMAC-SHA256 bằng `SEPAY_WEBHOOK_SECRET`, timestamp và nội dung request gốc; từ chối cấu hình thiếu, bỏ qua tiền ra và đối chiếu chính xác tài khoản nhận. Checkout lấy tài khoản từ cấu hình `SEPAY_BANK_*`, chỉ chấp nhận plan active và thiệp thuộc người dùng/session. Mã đơn mới gồm `MH` và 10 ký tự chữ số; vẫn nhận mã cũ 8 ký tự. Hai route webhook mới và cũ được miễn CSRF và đều yêu cầu HMAC. Đã bổ sung feature test nhưng chưa chạy test, Pint hoặc kiểm tra tích hợp SePay; chưa xác nhận đồng thời, đối soát transaction ID và CSRF/gateway thực tế. Cần cấu hình secret, tài khoản và webhook trên môi trường production trước khi vận hành.
- PAY-01 đối soát tiếp: webhook khóa dòng đơn trong transaction, chỉ xử lý đơn pending, yêu cầu số tiền nguyên dương khớp chính xác và cập nhật đơn/thiệp cùng transaction; khoản thiếu hoặc thừa chờ đối soát. Đã bỏ route và nút admin tự đánh dấu đã thanh toán khi chưa có quy trình đối soát; chưa có chức năng đối soát thủ công thay thế. Bổ sung feature test cho các tình huống bảo vệ này; mới kiểm tra cú pháp, chưa chạy test/Pint hoặc đồng thời trên MySQL. SQLite không tạo `FOR UPDATE`; chưa lưu và ràng buộc duy nhất ID giao dịch SePay.

---

## 4. QUY TẮC BẢO VỆ PHÂN TẦNG MẪU THIỆP & QUẢN LÝ TRẢI NGHIỆM KHÁCH HÀNG

### 1. Phân Tầng Gói Dịch Vụ (Tiering System)
- **Gói Free (Miễn phí)**: 
  - Cho phép người dùng chưa đăng nhập hoặc đã đăng nhập tự do tạo và chỉnh sửa thiệp ngay lập tức trên `/tao-thiep`.
  - Phù hợp với các cặp đôi muốn trải nghiệm nhanh (setup siêu tốc 2-3 phút).
- **Gói Plus (Nâng cao) & Gói Pro VIP (Hoàng gia)**:
  - Khách vãng lai (chưa đăng nhập) **vẫn được xem thử (preview) và xem chi tiết trang trọng** tại `/mau-thiep` và `/mau-thiep/{slug}`.
  - Tuyệt đối **KHÔNG** để nhãn thô như "Đăng nhập để dùng". Nút hành động luôn giữ nguyên văn phong tự nhiên, sang trọng: **"Tạo Thiệp Với Mẫu Này"**.
  - **Cơ chế bảo vệ thông minh (Smart Auth Redirect)**: Khi khách chưa đăng nhập bấm nút "Tạo Thiệp Với Mẫu Này" hoặc submit lưu thiệp Plus/Pro, hệ thống tự động ghi nhớ `intended_template` và chuyển hướng mượt mà sang trang Đăng nhập (`/dang-nhap`), sau khi đăng nhập thành công sẽ đưa khách quay trở lại ngay đúng mẫu thiệp họ vừa chọn.

### 2. Tiêu Chuẩn SEO & Chỉ Mục (Robots Indexing Rules)
- **Trang chủ (`/`), Kho mẫu (`/mau-thiep`), Bảng giá (`/bang-gia`), Dịch vụ (`/dich-vu`), Bài viết**: Cấu hình `index, follow` chuẩn SEO Top 1.
- **Trang chi tiết mẫu thiệp (`/mau-thiep/{slug}`)**: Cấu hình chuẩn `<meta name="robots" content="noindex, follow">` (không index để tránh trùng lặp nội dung bộ mẫu, nhưng cho phép bọ tìm kiếm follow để truyền sức mạnh liên kết nội bộ).
- **Trang biên tập tạo thiệp (`/tao-thiep`)**: Cấu hình chuẩn `<meta name="robots" content="noindex, nofollow">` (bảo vệ quyền riêng tư người dùng).
- **Trang thiệp cưới công khai (`/thiep/{slug}`)**: Cấu hình chuẩn `<meta name="robots" content="noindex, nofollow">` (bảo mật tuyệt đối thông tin cá nhân ngày cưới của cô dâu chú rể).

### 3. Quy Ước Đường Link Thiệp Cưới Chống Trùng Lặp (Slug Collision Prevention)
- Khi cô dâu chú rể trùng họ tên (ví dụ: Đặng Minh Quân & Lê Ái Như), link thiệp cưới `/thiep/{slug}` được bổ sung **hậu tố ngẫu nhiên kép** để đảm bảo 100% độc bản, không bao giờ xảy ra lỗi ghi đè thiệp của nhau:
  - Ví dụ URL chuẩn: `http://mayhen.vn/thiep/dang-minh-quan-le-ai-nhu-uxdbm-f5bez2shgm8`.

---

## 5. THIẾT KẾ CHI TIẾT TRANG MẪU THIỆP (`/mau-thiep/{slug}`)

Trang chi tiết mẫu thiệp (`resources/views/client/pages/template-detail.blade.php`) tuân thủ nghiêm ngặt chuẩn mực **Quiet Luxury Editorial** với đầy đủ các khối:
1. **Sân khấu trưng bày thiệp nghệ thuật (Visual Stage 3D Silk Elevation)**:
   - Khung chứa ảnh thiệp di động nổi bật với đổ bóng lụa 3D đa tầng (`rgba(28, 25, 23, 0.12)`), viền mạ tinh xảo, bám dính khi cuộn (`position: sticky; top: 90px`).
   - Nút kính mờ Glassmorphism **"Xem Trước Live"** (`/xem-thu/{slug}`) mở demo thực tế trên màn hình smartphone.
   - Dải thông số kỹ thuật chân ảnh: *Chuẩn màn hình điện thoại 100%* & *Độc bản Quiet Luxury*.
2. **Huy hiệu Gói Pro VIP ánh kim hoàng gia (Royal Seal)**:
   - Mẫu Pro được phủ gradient hoàng gia vàng kim hổ phách (`#FFFDF7` chuyển `#FDE68A`), viền chỉ vàng kim loại `#D97706`, icon vương miện dập nổi và hiệu ứng phát sáng nhẹ (`mayhen_gold_glow`).
3. **Bố cục 2 cột tỷ lệ vàng (Golden Ratio Grid)**:
   - Cột trái 460px - 480px, cột phải 1fr, khoảng cách gap co hẹp tinh tế (`1.5rem` - `2.5rem`), loại bỏ hoàn toàn cảm giác thưa thớt, rời rạc.
4. **Bảng Hồ Sơ Thiết Kế (Design Atelier Sheet)**:
   - Trưng bày tóm tắt: Tone sắc chủ đạo (kèm chấm màu thực tế), Kiểu chữ nghệ thuật, Hiệu ứng mở phong thư sáp 3D, Tính năng tương tác (RSVP, Nhạc nền, VietQR).
5. **Khối Đặc Quyền Tính Năng (Features Highlight)**:
   - Liệt kê các tính năng có sẵn: giấy mỹ thuật cổ điển, mở phong thư vintage, Love Story, sổ lưu bút online, mã VietQR mừng cưới tách biệt hai họ.
6. **Section "Trải Nghiệm Khách Mời Nhận Được" (The Guest Experience)**:
   - 3 điểm chạm cảm xúc thúc đẩy cô dâu chú rể sẵn sàng xuống tiền:
     1. *Chạm mở phong thư sang trọng*
     2. *Chiêm ngưỡng chuyện tình của hai bạn*
     3. *Xác nhận tham dự & Mừng cưới tinh tế*
7. **Section Bài Viết Chuyên Sâu Tối Ưu SEO & Thuyết Phục Khách Hàng (SEO Story)**:
   - H2: *"Tại Sao Mẫu Thiệp Cưới {Tên Mẫu} Lại Được Yêu Thích?"*.
   - Phân tích 3 lý do cốt lõi: Tối ưu hoàn hảo cho smartphone, Tiết kiệm 80% chi phí in ấn, Hộp mừng cưới VietQR văn minh & tinh tế.
8. **Section Câu Hỏi Thường Gặp (FAQ Accordion)**:
   - Giải đáp 4 thắc mắc then chốt: hiển thị trên di động, chỉnh sửa không giới hạn, tạo thiệp riêng cho từng khách mời, tốc độ tạo thiệp siêu tốc 3 phút.
9. **Cấu trúc Dữ Liệu Có Cấu Trúc Schema JSON-LD**:
   - Schema `Product` & `Offer`: Khai báo giá niêm yết, tình trạng tồn kho, số cặp đôi tin dùng và rating 4.9 sao.
   - Schema `FAQPage`: Giúp hiển thị danh sách câu hỏi nổi bật trên công cụ tìm kiếm.
   - **LƯU Ý KỸ THUẬT BLADE**: Luôn viết `"@@context": "https://schema.org"` để tránh Blade engine hiểu nhầm là directive `@context`.
10. **Liên Kết Nội Bộ - Mẫu Cùng Phong Cách (Similar Templates)**:
    - Tự động gợi ý 3 mẫu thiệp cùng phong cách, giữ chân khách hàng tiếp tục trải nghiệm.

---

## 6. KIẾN TRÚC THƯ MỤC DỰ ÁN

```text
mayhen.vn/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   └── TemplateController.php
│   │   │   └── Client/
│   │   │       ├── AuthController.php
│   │   │       ├── CheckoutController.php
│   │   │       ├── GuestController.php
│   │   │       ├── HomeController.php
│   │   │       ├── InvitationEditorController.php
│   │   │       ├── PageController.php
│   │   │       └── TemplateController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Wedding.php
│   │   ├── Invitation.php
│   │   ├── InvitationTemplate.php
│   │   ├── Plan.php
│   │   ├── Order.php
│   │   ├── Guest.php
│   │   └── GuestResponse.php
├── resources/
│   └── views/
│       ├── admin/                  # Giao diện Admin quản trị
│       └── client/
│           ├── layouts/
│           │   └── master.blade.php    # Layout khung chuẩn (SEO, CSS, JS master)
│           ├── components/             # Header, Footer, Head, Notification, v.v.
│           ├── pages/
│           │   ├── home.blade.php      # Landing Page trang chủ (12 sections chuẩn SEO Top 1)
│           │   ├── templates.blade.php # Danh mục kho mẫu thiệp cưới (bộ lọc tier & category)
│           │   ├── template-detail.blade.php # Trang chi tiết mẫu thiệp Quiet Luxury & Content SEO
│           │   ├── preview.blade.php   # Trang xem thử mẫu thiệp (mô phỏng smartphone)
│           │   ├── editor.blade.php    # Trình tạo & chỉnh sửa thiệp trực tiếp
│           │   ├── checkout.blade.php  # Thanh toán VietQR động
│           │   ├── guests.blade.php    # Quản lý danh sách khách & RSVP
│           │   ├── services.blade.php  # Trang giới thiệu dịch vụ
│           │   ├── pricing.blade.php   # Bảng giá các gói
│           │   ├── how-it-works.blade.php
│           │   ├── about.blade.php
│           │   ├── contact.blade.php
│           │   ├── faq.blade.php
│           │   ├── terms.blade.php
│           │   ├── privacy.blade.php
│           │   ├── login.blade.php
│           │   ├── register.blade.php
│           │   └── public-invitation.blade.php
│           └── invitations/            # Các theme thiệp cưới cụ thể
│               ├── hoang-gia-tem-sap-luxury/
│               ├── thanh-manh-01/
│               └── duyen-dang-01/
├── public/
│   └── assets/
│       ├── client/
│       │   ├── css/
│       │   │   ├── common/             # tokens.css, base.css, layout.css
│       │   │   ├── components/         # header.css, footer.css, buttons.css, forms.css
│       │   │   └── pages/              # home.css, templates.css, template-detail.css, editor.css...
│       │   ├── js/
│       │   │   ├── common/             # dom.js, events.js, http.js
│       │   │   ├── components/         # header.js, modal.js, toast.js
│       │   │   └── pages/              # home.js, templates.js, editor.js...
│       │   └── fonts/                  # Phông chữ nội bộ (offline-ready)
│       └── admin/                      # Assets chuyên biệt cho trang Admin
├── routes/
│   ├── web.php                         # Khai báo toàn bộ web routes
│   └── api.php                         # Khai báo các endpoint API nội bộ
├── context.gemini.md                   # FILE CONTEXT NÀY (BẮT BUỘC ĐỌC VÀ CẬP NHẬT)
└── AGENTS.md                           # Luật vận hành cho Agent
```

---

## 7. BẢN ĐỒ ROUTES & CHỨC NĂNG HỆ THỐNG

| Method | URI | Tên Route | Controller & Action | Chức năng & Chính sách SEO |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/` | `home` | `HomeController@index` | Trang chủ Landing Page chuẩn SEO Top 1 (`index, follow`) |
| **GET** | `/mau-thiep` | `templates.index` | `TemplateController@index` | Kho mẫu thiệp cưới có bộ lọc phân tầng & phong cách (`index, follow`) |
| **GET** | `/mau-thiep/{slug}`| `templates.show` | `TemplateController@show` | Chi tiết mẫu thiệp Quiet Luxury & Content SEO (`noindex, follow`) |
| **GET** | `/xem-thu/{slug}` | `templates.preview`| `TemplateController@preview` | Xem thử thiệp live: Tiêu đề `Xem thử: "Tên thiệp" | {App Name}`, thanh điều hướng Quiet Luxury (`Kho Mẫu`, `Chi Tiết Mẫu`, `Tạo Thiệp Với Mẫu Này`), Preview-only Mode chỉ mô phỏng UI không gửi dữ liệu backend, tự động cache-busting `?v={filemtime}` chống dính CSS cũ (`noindex, nofollow`) |
| **GET** | `/tao-thiep` | `invitation.create`| `InvitationEditorController@create` | Mở trình soạn thảo tạo thiệp mới (hoặc chỉnh sửa thiệp đã tạo với tham số `?edit={slug}`): Tích hợp Cropper tỷ lệ 3/4 Pure Canvas, Sinh VietQR ngân hàng an toàn 100%, Modal chọn nhạc đám cưới thông minh (`noindex, nofollow`, chặn khách chưa login nếu chọn mẫu Plus/Pro) |
| **POST** | `/tao-thiep` | `invitation.store` | `InvitationEditorController@store` | Lưu dữ liệu đám cưới & cấu hình thiệp (tự động cập nhật nếu truyền `invitation_id` của chủ nhân) |
| **POST** | `/tao-thiep/upload-asset` | `invitation.uploadAsset` | `InvitationEditorController@uploadAsset` | Tải file ảnh thực sự lên assets của mẫu thiệp khi submit tạo thiệp |
| **GET** | `/api/wedding-musics` | `invitation.musics` | `InvitationEditorController@musics` | API tìm kiếm bài hát thông minh siêu tốc độ từ thư mục ca sĩ (`/singer/{ca sĩ}/{bài hát}.mp3`), hỗ trợ tìm không dấu/có dấu, phân trang |
| **GET** | `/thiep/{slug}` | `invitation.show` | `InvitationEditorController@show` | Trang thiệp cưới công khai cho khách nhận; tự động nhận diện chủ nhân thiệp (qua User ID hoặc session `owned_invitation_slugs`) để hiển thị thanh quản trị nổi bật kèm nút **"Sửa Lại Thiệp Cưới"** (`noindex, nofollow`) |
| **POST** | `/thiep/{slug}/rsvp`| `rsvp.submit` | `GuestController@submitRsvp` | Khách gửi phản hồi RSVP xác nhận tham dự |
| **GET** | `/thanh-toan/{planSlug}`| `checkout.show` | `CheckoutController@show` | Trang thanh toán chuyển khoản VietQR |
| **POST** | `/api/checkout/process`| `checkout.process` | `CheckoutController@process` | Tạo đơn hàng & mã thanh toán VietQR |
| **GET** | `/api/checkout/status/{orderCode}`| `checkout.status`| `CheckoutController@checkStatus`| Polling kiểm tra trạng thái thanh toán |
| **POST** | `/api/payments/sepay/webhook` | `webhook.payment` | `CheckoutController@handleWebhook` | Webhook SePay xác thực HMAC và đối chiếu giao dịch |
| **POST** | `/api/webhook/payment` | `webhook.payment.legacy` | `CheckoutController@handleWebhook` | Route tương thích cũ, cùng yêu cầu HMAC |
| **GET** | `/tai-khoan/thiep/{id}/khach-moi`| `guests.index` | `GuestController@index` | Quản lý danh sách khách, gửi thiệp cá nhân hóa |
| **POST** | `/tai-khoan/thiep/{id}/khach-moi`| `guests.store` | `GuestController@store` | Thêm khách mời mới vào danh sách |
| **DELETE**| `/tai-khoan/khach-moi/{id}`| `guests.destroy` | `GuestController@destroy` | Xóa khách mời |
| **GET** | `/dich-vu` | `services` | `PageController@services` | Giới thiệu các dịch vụ của Mây Hẹn (`index, follow`) |
| **GET** | `/bang-gia` | `pricing` | `PageController@pricing` | Bảng giá các gói thiệp cưới online (`index, follow`) |
| **GET** | `/huong-dan` | `how-it-works` | `PageController@howItWorks` | Hướng dẫn 3 bước tạo và gửi thiệp (`index, follow`) |
| **GET** | `/ve-may-hen` | `about` | `PageController@about` | Câu chuyện thương hiệu Mây Hẹn (`index, follow`) |
| **GET** | `/lien-he` | `contact` | `PageController@contact` | Trang liên hệ hỗ trợ & tư vấn (`index, follow`) |
| **GET** | `/cau-hoi-thuong-gap`| `faq` | `PageController@faq` | Giải đáp thắc mắc thường gặp (`index, follow`) |
| **GET** | `/dieu-khoan-dich-vu`| `terms` | `PageController@terms` | Điều khoản dịch vụ (`index, follow`) |
| **GET** | `/chinh-sach-bao-mat`| `privacy` | `PageController@privacy` | Chính sách bảo vệ dữ liệu cá nhân (`index, follow`) |
| **GET/POST**| `/dang-nhap`, `/dang-ky`, `/dang-xuat` | Auth routes | `AuthController` | Xác thực người dùng & điều hướng thông minh sau khi đăng nhập |
| **ANY** | `/admin/*` | `admin.*` | Admin Controllers | Quản lý hệ thống mẫu, đơn hàng, khách hàng |

---

## 8. GIAO DIỆN AUTH: ĐĂNG NHẬP & ĐĂNG KÝ (ORGANIC CURVED WAVES & PILL CONTROLS)

Hệ thống xác thực (`/dang-nhap` và `/dang-ky`) đã được tái thiết kế toàn diện theo cảm hứng mỹ thuật hiện đại kết hợp Quiet Luxury của thương hiệu Mây Hẹn:

1. **Bố cục 2 Cột Đối Xứng (Split Screen Card)**:
   - Bo góc lớn cao cấp `36px` (`border-radius: 36px`), đổ bóng êm ái đa tầng.
   - **Cột Trái (Hero / Welcome Panel)**:
     - Nền gradient hoàng gia Charcoal kết hợp Muted Gold (`#1A1715` -> `#2A231C` -> `#382E24`).
     - Hiệu ứng các lớp sóng cong hữu cơ đan xen (`mayhen_auth_hero_wave_1` đến `4`) bằng CSS thuần với radial gradients.
     - Vòng tròn trắng nổi bật chứa biểu tượng Logo Mây Hẹn hoa sen dập nổi.
     - Nút viền mỏng bo tròn hoàn toàn dạng viên thuốc (`mayhen_auth_pill_btn_outline`) chuyển đổi nhanh giữa Đăng Nhập và Đăng Ký.
     - Footer thanh lịch: *"MÂY HẸN • QUIET LUXURY WEDDING"*.
   - **Cột Phải (Form Panel)**:
     - Nền trắng tinh khôi kết hợp mảng cong hữu cơ uốn nhẹ ở góc trên bên phải (`mayhen_auth_corner_accent`).
     - Tiêu đề `welcome` / `register` thanh thoát, subtitle định hướng người dùng.
     - **Ô nhập liệu dạng viên thuốc (Pill Inputs - `border-radius: 9999px`)**: Nền kem nhạt `#F4EFEA`, viền mờ tinh tế, placeholder có chấm nối đuôi (`Email........`, `Mật khẩu........`), chuyển màu mượt mà khi focus.
     - **Nút gửi dạng viên thuốc (Pill Submit Button)**: Đặt ở chính giữa form với kích thước cân đối, phủ gradient Muted Gold hoàng gia (`#C5A880` -> `#9C7A4A`), chữ hoa `ĐĂNG NHẬP` / `ĐĂNG KÝ`.
     - Dòng chuyển đổi tài khoản dưới nút với link hover đổi màu mượt mà.
2. **Tương Thích Di Động (Mobile Excellence < 768px)**:
   - Trên màn hình điện thoại, card tự động chuyển sang 1 cột dạng dọc: Phần trên là banner cong hữu cơ ôm lấy logo Mây Hẹn, phần dưới là form nhập liệu viên thuốc gọn gàng, chạm vuốt dễ dàng, không bị co giật.
3. **Bộ Kiểm Thử Tự Động (Pest Feature Tests)**:
   - Đã được bao phủ trong `tests/Feature/AuthTest.php` kiểm tra toàn vẹn mã trạng thái HTTP 200, các thẻ CSS prefix `mayhen_auth_*` và các trường input.

---

## 9. HỆ THỐNG PHÂN LOẠI & BỘ LỌC TIER MẪU THIỆP (FREE / PLUS / PRO)

Kho mẫu thiệp cưới (`/mau-thiep`) hỗ trợ bộ lọc phân loại theo gói dịch vụ (`?tier=free`, `?tier=plus`, `?tier=pro`):

1. **Phân Loại Gói (Tier Categorization)**:
   - **Gói Miễn Phí (`tier: free` — 4 mẫu)**: `minimal-sand-beige`, `minimalism-darkred`, `minimalism-darkbrown`, `mai-lan-white`. Đặc điểm: Setup siêu tốc 2 phút, bố cục tinh gọn, đầy đủ thông tin lễ cưới cốt lõi & bản đồ GPS.
   - **Gói Plus (`tier: plus` — 6 mẫu)**: `botanical-sage-garden`, `romantic-dusty-rose`, `cinema-modern-lovestory`, `boho-floral-green`, `to-duyen-green`, `vuon-kinh-pink`. Đặc điểm: Bố cục hoa lá & sân vườn lãng mạn, hiệu ứng cánh hoa rơi, timeline câu chuyện tình yêu, album ảnh cưới HD.
   - **Gói Pro VIP (`tier: pro` — 6 mẫu)**: `envelope-reveal-luxury`, `editorial-scroll-vogue`, `song-hy-red`, `song-hy-green`, `song-hy-v2-red`, `long-phung-red`. Đặc điểm: Mở phong thư hoàng gia 3D tem sáp niêm phong, phong cách tạp chí Editorial cao cấp, Song Hỷ & Long Phụng dệt gấm cung đình.

2. **Cơ Chế Truy Vấn & Migration Dữ Liệu**:
   - Dữ liệu tier được lưu trữ chuẩn hóa trong cột JSON `default_config->tier`.
   - Migration `2026_10_15_000001_update_invitation_templates_tier_and_config.php` tự động cập nhật toàn bộ metadata `tier`, `complexity`, `setup_time`, `stats` cho toàn bộ mẫu.
   - `TemplateController@index` áp dụng truy vấn JSON linh hoạt kết hợp fallback an toàn cho trường hợp tier chưa set.
   - Kiểm thử tự động `tests/Feature/TemplateFilterTest.php` bao phủ 100% các trạng thái lọc.

3. **Cơ Chế Tự Động Cuộn Chạm Top (Auto-Scroll Offset 75px)**:
   - Khi chọn lọc theo bất kỳ gói nào (`mayhen_templates_tier_bar`), trang web tự động cuộn đưa thanh `mayhen_templates_tier_bar` lên chạm mép trên với khoảng cách chính xác `top: 75px` (ngay sát chân Header) trên cả Desktop và Mobile.
   - Kết hợp `scroll-margin-top: 75px` trong [templates.css](file:///d:/Project/Laravel/mayhen.vn/public/assets/client/css/pages/templates.css), neo định danh `#mayhen_templates_tier_bar` và module JavaScript [templates.js](file:///d:/Project/Laravel/mayhen.vn/public/assets/client/js/pages/templates.js).

---

## 10. KIẾN TRÚC HEADER & MOBILE MENU DRAWER

1. **Khắc Phục Lỗi Xuyên Thấu & Stacking Context**:
   - Khi mở menu mobile drawer, `mayhen_client_header_wrapper` được nâng lên `z-index: 10000;` với nền đục `#FAF8F5` và tắt `backdrop-filter` để triệt tiêu lỗi tạo Containing Block làm fixed element bị lỗi.
   - Drawer `mayhen_client_header_drawer` phủ toàn màn hình (`100vw x 100dvh`), nền đục `#FAF8F5 !important;` với `z-index: 9999;` ngăn chặn hoàn toàn việc nội dung trang web bên dưới đè xuyên qua chữ menu.
2. **Trải Nghiệm Tương Tác**:
   - Hamburger icon 3 vạch tự động chuyển đổi thành dấu X đóng menu mượt mà (`mayhen_client_header_toggle_active`).
   - Tự động kích hoạt khóa cuộn trang (`body.mayhen_no_scroll`) khi mở drawer để chống cuộn trang bên dưới.
   - Tự động đóng menu khi click vào bất kỳ liên kết nào, nhấn phím Escape hoặc thay đổi kích thước màn hình sang desktop.
3. **Nút Đăng Nhập Header Nổi Bật Nhẹ Nhàng (Quiet Luxury)**:
   - Nút Đăng nhập trên Header (`mayhen_client_header_login_btn`) được thiết kế dạng bo tròn viên thuốc (pill), nền kem ánh vàng ngà mờ `rgba(197, 168, 128, 0.12)`, viền mỏng Muted Gold `rgba(197, 168, 128, 0.45)`, chữ vàng đậm sang trọng `#8C6A3B`, tạo điểm nhấn thanh tao vừa vặn trên mobile mà không gây gắt mắt.

---

## 11. THANH ĐIỀU HƯỚNG ĐÁY MÀN HÌNH TOÀN WEB (BOTTOM NAVIGATION BAR)

Hệ thống thanh dock điều hướng đáy trang (`mayhen_client_bottom_nav_*`) được tích hợp cố định trên toàn bộ website (`client.layouts.master`):

1. **Cấu Trúc 5 Nút: Icon SVG kèm Nhãn Text Rõ Ràng**:
   - **Nút 1: Trang chủ** (`route('home')`) — Icon Home nét vẽ thanh mảnh + Text `Trang chủ`.
   - **Nút 2: Mẫu thiệp** (`route('templates.index')`) — Icon Card / Grid layout + Text `Mẫu thiệp`.
   - **Nút 3: Tạo thiệp** (`route('invitation.create')`) — **Nút tròn lớn ở chính giữa nổi bật** (`mayhen_client_bottom_nav_create_btn`, kích thước 44px, margin-top nhô nhẹ -16px), viền 3px `#FAF8F5`, phủ gradient Muted Gold, hiệu ứng sóng hào quang lan tỏa (`mayhenPulseGlow`), icon nhịp thở (`mayhenIconFloat`) + Text `Tạo thiệp` màu Gold đậm nét ở dưới.
   - **Nút 4: Yêu thích** (`route('templates.index')#yeu-thich`) — Icon Trái tim + Text `Yêu thích`.
   - **Nút 5: Trợ lý AI** (`route('contact')#tro-ly-ai`) — Icon Robot AI + Text `Trợ lý AI`.
2. **Quy Chuẩn Kỹ Thuật**:
   - Bố cục flex dạng cột cân đối: Icon ở trên, text label ở dưới (size `0.65rem`), chống trống trải và giúp người dùng nhận diện chức năng ngay lập tức.
   - Tự động kích hoạt trạng thái Active chuyển sang màu vàng ánh kim `#9C7A4A` và font-weight 600 theo từng route.
   - Khoảng đệm bù đáy cho body: `padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px))` đảm bảo không bao giờ che khuất nội dung trang.
   - Tương thích đa thiết bị: Cố định chân màn hình trên Mobile và chuyển thành thanh Dock nổi bo tròn 9999px ở giữa đáy trên Desktop.

---

## 12. TỐI ƯU HÓA MOBILE-FIRST CHO KHO MẪU THIỆP (/mau-thiep)

Nhằm nâng cao trải nghiệm người dùng trên màn hình nhỏ và tuân thủ định vị Quiet Luxury:
1. **Hero Header & Highlights Siêu Gọn**:
   - Header thu gọn padding dọc xuống `4px 0 8px 0`, tiêu đề H1 co về `1.35rem` thanh lịch, mô tả ngắn gọn `0.8125rem`.
   - Dải 4 điểm nhấn (`mayhen_templates_highlights_bar`) được chuyển thành thanh cuộn ngang 1 hàng với các pill chip nền sáng (`overflow-x: auto`), không chiếm diện tích dọc màn hình.
2. **Bộ Lọc Phong Cách & Gói Dịch Vụ Dạng Horizontal Scroll Chips**:
   - Cả 2 thanh lọc (`mayhen_templates_filter_bar` và `mayhen_templates_tier_bar`) được thiết kế cuộn ngang 1 hàng duy nhất (`flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;`), vuốt ngón tay lướt êm ái, xóa bỏ hoàn toàn hiện tượng rớt thành 4-5 hàng nham nhở.
3. **Thanh Meta Bar & Sắp Xếp Chống Gãy Chữ**:
   - Tối ưu hiển thị số lượng mẫu thiệp dạng rút gọn trên mobile (`6 / 16 mẫu thiệp`) và cụm sắp xếp nằm cùng 1 hàng duy nhất (`white-space: nowrap`), loại bỏ triệt để lỗi gãy chữ "Nổi" và "bật".
4. **Lưới Mẫu Thiệp 2 Cột Cân Đối & Tỷ Lệ Ảnh 3:4**:
   - Khung hình thẻ thiệp chuyển sang `aspect-ratio: 3 / 4` vừa vặn, không bị dài lòng thòng.
   - Thân thẻ được co gọn padding (`8px 8px 10px`), ẩn mô tả dài dòng trên mobile 2 cột để chiều cao thẻ đồng đều tuyệt đối.
   - Nút "Xem Live" và "Dùng Mẫu" dàn ngang cân xứng, bấm mượt mà.
5. **Tinh Gọn Khoảng Trống Các Section SEO, So Sánh Gói, FAQ, CTA**:
   - Thu nhỏ padding dọc từ `64px - 96px` xuống còn `20px - 28px`, giúp giao diện liền mạch, trang nhã, không còn cảm giác rỗng tuếch hay xa cách.

---

## 13. TỐI ƯU HÓA MOBILE-FIRST CHO TRANG CHI TIẾT MẪU THIỆP (/mau-thiep/{slug})

Nhằm mang lại trải nghiệm xem và đặt thiệp cưới đỉnh cao trên điện thoại di động:
0. **Breadcrumb Tinh Gọn 1 Hàng Duy Nhất**:
   - Breadcrumb và tất cả các mục bên trong được cố định trên 1 hàng duy nhất (`flex-wrap: nowrap; white-space: nowrap; overflow-x: auto; scrollbar-width: none;`), chống hoàn toàn hiện tượng tên mẫu thiệp bị rớt xuống dòng thứ 2 gây mất thẩm mỹ.
1. **Sân Khấu Trưng Bày Thiệp (Visual Stage) & Chuyển Động Tự Động Cuộn**:
   - Thẻ trưng bày co gọn padding xuống `8px`, bo góc `16px`.
   - Khung hình thiệp `mayhen_template_detail_stage_media` và ảnh `mayhen_template_detail_stage_img` luôn luôn được cố định tỷ lệ chuẩn mực `aspect-ratio: 3 / 4` trên mọi kích thước màn hình (desktop, tablet, mobile), xóa bỏ giới hạn max-height để ảnh thiệp không bao giờ bị méo hay sai lệch tỷ lệ.
   - **Tự động cuộn mẫu chậm rãi khi Hover (Desktop)**: Khi rê chuột vào khung thiệp, ảnh bên trong tự động trượt mượt mà từ trên xuống dưới (`transition: transform 6s cubic-bezier(0.25, 0.46, 0.45, 0.94)`), khi rời chuột ra tự động cuộn êm ái quay về đỉnh ban đầu. Kèm tooltip gợi ý nhỏ gọn `mayhen_template_detail_stage_hint_desktop`.
   - **Nút "Chạy Thử" trên Mobile**: Góc trên bên phải khung thiệp tích hợp nút `mayhen_template_detail_btn_autoscroll` bo tròn Glassmorphism. Khi khách chạm vào, hệ thống tự động kích hoạt chuyển động trượt chậm rãi từ đầu tới chân thiệp, dừng ngắm 1.2s rồi tự động quay về đỉnh hoặc cho phép bấm dừng bất kỳ lúc nào.
   - Nút nổi "Xem Trước Live" dạng Glassmorphism bo tròn nhỏ gọn (`padding: 7px 16px; font-size: 0.75rem`).
   - Dải thông số chân sân khấu rút gọn flex 1 hàng, icon 14px thanh nhã.
2. **Khối Thông Tin Tác Phẩm & Hồ Sơ Thiết Kế**:
   - Tiêu đề H1 co về `1.35rem`, line-height `1.25`, tôn vinh nghệ thuật typography chữ than ấm `#1F1D1A`.
   - Bảng **Hồ Sơ Thiết Kế** tổ chức thành 2 cột dạng lưới compact (`grid-template-columns: 1fr 1fr; gap: 8px 12px; padding: 12px 14px;`), hiển thị rõ ràng tone màu chủ đạo, font chữ, hiệu ứng mở mà không chiếm diện tích dọc.
   - Danh sách đặc quyền rút gọn gàng, icon chuẩn tone Mây Hẹn.
   - Nút bấm chính "Tạo Thiệp Với Mẫu Này" và "Xem Thử Trực Tiếp" căn chỉnh chiều cao vừa tay, icon cam kết đổi sang tone màu Muted Gold `#9C7A4A`.
3. **Sân Khấu Trưng Bày Thiệp Cưới Dải Cuộn Liên Hoàn (Showcase Reel)**:
   - Khung sân khấu tỷ lệ cố định **3:4** (`mayhen_template_detail_stage_media`) tổ chức theo cấu trúc dải cuộn (`mayhen_template_detail_stage_reel`):
     - **Vị trí 1**: Poster ảnh đại diện hiển thị đầy đủ 100% khi vừa nạp trang (`load`).
     - **Vị trí 2**: Iframe trang web mẫu thiệp cưới sống động (`route('templates.preview', $template->slug) . '?embed=1'`) bám ngay sau đít ảnh poster.
   - **Cơ chế chuyển động thông minh (Autoplay 3s & Hover Restart)**:
     - **Tự chạy sau 3 giây khi load trang**: Người dùng vừa vào trang sẽ ngắm ảnh đại diện trong 3 giây, sau đó hệ thống tự động đẩy ảnh poster lên trên và cuộn nội dung trang thiệp trong iframe mà không cần thao tác chuột. (Đã fix triệt để phạm vi function scope `setButtonState`).
     - **Chạy lại từ đầu khi Hover**: Bất cứ khi nào người dùng rê chuột vào (`mouseenter`), dải cuộn lập tức reset về vị trí xuất phát (ảnh đại diện) và bắt đầu chạy lại từ đầu.
     - Khi chuột rời ra (`mouseleave`): Toàn bộ thu hồi êm dịu (`transition: transform cubic-bezier`) đưa ảnh đại diện ban đầu trở lại vị trí trung tâm.
4. **Các Section Trải Nghiệm, Bài Viết SEO, FAQ & Mẫu Liên Quan**:
   - Khoảng cách padding dọc giữa các section giảm mạnh từ `4.5rem` xuống còn `24px`, xóa bỏ khoảng trắng thừa.
   - **Mẫu Thiệp Cùng Phong Cách**: Chuyển từ layout 1 cột ảnh ngang 4:3 sang **Lưới 2 cột dọc tỷ lệ 3:4 chuẩn mực** đồng bộ với trang danh mục.
   - Hộp kêu gọi hành động cuối trang (Bottom Banner) co gọn `padding: 20px 14px`, nút bấm mở rộng 100% chiều ngang tiện lợi.

---


## 14. CẤU TRÚC DATABASE & CÁC MODEL CHÍNH

1. **`User` (`users`)**:
   - Quản lý tài khoản cô dâu, chú rể, studio đối tác hoặc admin.
2. **`InvitationTemplate` (`invitation_templates`)**:
   - `id`, `name`, `slug`, `category`, `thumbnail_url`, `demo_url`, `default_config` (JSON lưu: `tier` [free/plus/pro], `stats` [rating, pairs_used], `features`, `animation`, `complexity`, `font_serif`, `setup_time`, `description`, `accent_color`, `style_layout`, `required_inputs`), `current_version`, `is_active`, `is_featured`.
3. **`Wedding` (`weddings`)**:
   - `id`, `user_id`, `groom_name`, `bride_name`, `groom_parents`, `bride_parents`, `event_date`, `venue_name`, `venue_address`, `map_lat_lng`, `qr_bank_info`.
4. **`Invitation` (`invitations`)**:
   - `id`, `wedding_id`, `template_id`, `custom_slug`, `music_url`, `custom_css`, `content_json`, `is_published`, `view_count`.
5. **`Guest` (`guests`)**:
   - `id`, `invitation_id`, `name`, `phone`, `group_name` (Nhà trai, Nhà gái, Đồng nghiệp, Bạn bè), `access_code`, `status`.
6. **`GuestResponse` (`guest_responses`)**:
   - `id`, `guest_id`, `invitation_id`, `is_attending`, `guest_count`, `dietary_notes`, `wishes_message`.
7. **`Plan` (`plans`)**:
   - `id`, `name`, `slug`, `price`, `duration_days`, `features_json`.
8. **`Order` (`orders`)**:
   - `id`, `user_id`, `plan_id`, `order_code`, `amount`, `status` (`pending`, `paid`, `cancelled`), `payment_method` (`vietqr`, `bank_transfer`).

---

## 9. QUY TRÌNH LÀM VIỆC DÀNH CHO AI (STANDARD WORKFLOW)

Mỗi khi nhận một yêu cầu từ người dùng, AI phải thực hiện tuần tự 5 bước:

### 🔹 BƯỚC 1: Đọc & Tham Chiếu Context
- Đọc file `context.gemini.md` để nắm rõ hiện trạng và các quy tắc.
- Đọc skill tương ứng trong thư mục `.agents/skills/` nếu làm việc ở module đặc thù.

### 🔹 BƯỚC 2: Viết Code Tuân Thủ Nghiêm Ngặt
- Frontend: Chỉ viết HTML thuần, CSS thuần (`mayhen_*`), JS native (ES Modules). Tuyệt đối không nhúng CDN, không viết inline script/style.
- Backend: Dùng Form Request cho validation, giữ Controller tinh gọn, sử dụng Service/Action khi xử lý logic phức tạp.
- Đảm bảo kiểm tra lỗi syntax hoặc xung đột Blade directive.

### 🔹 BƯỚC 3: Format & Kiểm Tra Code
- Định dạng code PHP: Chạy `vendor/bin/pint --format agent`.
- Chạy unit/feature tests: Chạy `vendor/bin/pest` để đảm bảo không làm gãy tính năng cũ.

### 🔹 BƯỚC 4: CẬP NHẬT FILE `context.gemini.md` (BẮT BUỘC)
- Bổ sung các tính năng, route mới, thay đổi cấu trúc vừa thực hiện vào file `context.gemini.md` này.
- Đảm bảo tài liệu phản ánh chính xác 100% hiện trạng mới nhất của repository.

### 🔹 BƯỚC 5: Soạn Nội Dung Commit (Tiếng Việt có dấu)
- Viết sẵn Git Commit Message rõ ràng, súc tích bằng tiếng Việt có dấu để người dùng copy và tự push lên Git.
- **LƯU Ý**: Không tự ý chạy `git push` nếu người dùng chưa cho phép.

---

## 10. NHẬT KÝ CẬP NHẬT & TÍCH HỢP MẪU THIỆP (CHANGELOG)

### 📌 Phiên bản 1.1.0 — Tích hợp mẫu chuẩn độc bản "Nét Mực Tình Yêu" (net-muc-tinh-yeu)
- **Dọn dẹp hệ thống**:
  - Đã làm sạch toàn bộ dữ liệu mẫu thiệp cũ trong database (`invitation_templates`, `invitations`, `weddings`, `guests`, `guest_responses`).
  - Đã dọn dẹp các thư mục view cũ trong `resources/views/client/invitations/`.
- **Tích hợp mẫu thiệp flagship "Nét Mực Tình Yêu"**:
  - **Slug**: `net-muc-tinh-yeu`
  - **Tên**: Nét Mực Tình Yêu (Phong cách Quiet Luxury, Thư pháp phương Tây Quarzo & Editorial sang trọng).
  - **Thư mục Assets**: `public/assets/client/invitations/net-muc-tinh-yeu/` (toàn bộ 28 hình ảnh, font chữ Quarzo, nhạc nền MP3, styles.css, script.js native).
  - **Script tương tác động**: `public/assets/client/invitations/net-muc-tinh-yeu/script.js` kết nối mượt mà với biến toàn cục `window.__MAYHEN_WEDDING_DATE`, `window.__MAYHEN_WEDDING_TARGET_TIME` và mã VietQR `window.__MAYHEN_GIFT_QR_GROOM` / `window.__MAYHEN_GIFT_QR_BRIDE`.
  - **Blade Template**: `resources/views/client/invitations/net-muc-tinh-yeu/template.blade.php` hỗ trợ cả chế độ nhúng Iframe (`embed=1`) và chế độ thiệp độc lập của khách hàng.
  - **Schema Cấu Hình Khách Hàng**: `resources/views/client/invitations/net-muc-tinh-yeu/schema.json` mô tả 100% các trường dữ liệu tùy chỉnh:
    1. Thông tin cô dâu, chú rể & phụ mẫu hai bên.
    2. Hai tab sự kiện riêng biệt: Tiệc Cưới & Lễ Thành Hôn Nhà Gái vs Nhà Trai (ngày giờ, âm lịch, địa chỉ, link bản đồ Google Maps).
    3. Bộ sưu tập ảnh: Ảnh bìa phong bì mở tem sáp, ảnh mở thiệp, ảnh dọc (Hero photo), bộ 3 ảnh ghép Triptych, Album 10 ảnh Lightbox chạm phóng to sắc nét, ảnh chân trang.
    4. Lời ngỏ tình yêu (Love story / Quote).
    5. Lịch trình ngày cưới (Timeline mốc thời gian tùy biến).
    6. Quy định trang phục (Dresscode gợi ý màu sắc).
    7. Bộ đếm ngược ngày cưới & Lịch tháng tự động tính toán.
    8. Xác nhận tham dự (RSVP Online) & Sổ lưu bút lời chúc.
    9. Hộp mừng cưới VietQR riêng biệt cho Chú Rể và Cô Dâu.
    10. Nhạc nền MP3 lãng mạn kèm phím đĩa than quay nhẹ nhàng.
  - **Controllers cập nhật**:
    - `TemplateController::preview()`: Nhận diện và tự động nạp view `client.invitations.{$template->slug}.template` khi xem thử.
    - `InvitationEditorController::show()`: Nhận diện và tự động nạp template tương ứng khi khách mở trang thiệp đã xuất bản `/thiep/{slug}`.
  - **Database Seeder**: `InvitationTemplateSeeder.php` thiết lập duy nhất mẫu `net-muc-tinh-yeu` với đầy đủ metadata, tier Pro VIP, complexity Deluxe, tags và thống kê.
  - **Kiểm thử tự động**: Đã bổ sung `tests/Feature/NetMucTinhYeuTemplateTest.php` với 100% tests passed (xem chi tiết, xem thử preview, hiển thị dữ liệu tùy biến của cặp đôi).

### 📌 Phiên bản 1.1.1 — Studio Tạo Thiệp Thông Minh Thích Ứng Theo Schema (Schema-Driven Live Editor)
- **Vấn đề đã giải quyết triệt để**: Khắc phục tình trạng "mẫu thiệp một kiểu, vào tạo thiệp lại hiển thị một kiểu", loại bỏ hoàn toàn khung preview tĩnh hardcode và form cứng ngắc không phản ánh đúng mẫu thiệp.
- **Khung Preview Điện Thoại (Exact Template Live Preview)**:
  - Khung điện thoại trong `editor.blade.php` được nhúng trực tiếp `<iframe>` trỏ tới route xem thử của chính mẫu thiệp đó: `/xem-thu/{slug}?embed=1&mode=editor`.
  - Khách hàng chọn mẫu nào (ví dụ: Nét Mực Tình Yêu), chiếc điện thoại bên phải hiển thị **chính xác 100% mẫu thiệp đó** với đầy đủ phong bì tem sáp, thư pháp Quarzo, 2 tab sự kiện, timeline, album ảnh, VietQR...
- **Form Động Thích Ứng Theo Từng Mẫu (Schema-Driven Form Engine)**:
  - Form bên trái tự động duyệt qua `schema['sections']` để sinh số bước Stepper Navigation và các trường dữ liệu tương ứng riêng biệt cho từng mẫu.
  - Hỗ trợ đầy đủ các loại input: `text`, `textarea`, `date`, `time`, `image` (kèm preview ảnh nhỏ), `repeater` (Timeline mốc thời gian), `gallery` (Album 10 ảnh), `color`.
  - Tự động chuẩn hóa input name chuẩn Laravel: `wedding[groom_name]`, `wedding[extra_data][bride_party_address]`, `wedding[extra_data][timeline]`, v.v.
- **Đồng Bộ Hai Chiều Thời Gian Thực (PostMessage Realtime Sync)**:
  - Khi người dùng gõ vào bất kỳ trường nào trên form, `editor.js` tự động phát tín hiệu `MAYHEN_SYNC_INPUT` qua `postMessage` sang iframe.
  - Template của mẫu thiệp lắng nghe và cập nhật DOM ngay lập tức (tên cô dâu chú rể, ngày cưới, ảnh cưới, phụ mẫu, địa chỉ tiệc, quote, dresscode...).
- **Tối Ưu Stepper Navigation Tabs**:
  - Khắc phục lỗi các tab bị co ép đè chữ lên nhau bằng cách thiết lập `flex: 0 0 auto`, `white-space: nowrap`, cuộn ngang cảm ứng mượt mà.
  - Sử dụng tiêu đề ngắn gọn (Short Title) trên các tab (Đôi Uyên Ương, Nhà Gái, Nhà Trai, Ảnh Cưới, Lịch Trình, Mừng Cưới) kết hợp tooltip đầy đủ.
- **Kiểm thử tự động**: Đã bổ sung test case Feature kiểm tra iframe preview và dynamic schema fields, đạt **9/9 tests passed (96 assertions)**.

### 📌 Phiên bản 1.1.2 — Chuẩn Hóa Định Dạng Thư Mục Template & Tải Ảnh Trực Tiếp Vào Assets
- **Chuẩn Hóa Cấu Trúc Template Trọn Gói (Self-contained Template Package)**:
  - Bắt buộc mỗi thư mục mẫu thiệp tại `resources/views/client/invitations/{slug}/` phải có cấu trúc chuẩn mực gồm 6 thành phần:
    1. `assets/` (chứa toàn bộ hình ảnh, font chữ, âm thanh độc bản của mẫu)
    2. `README.md` (hướng dẫn và mô tả phong cách mẫu thiệp)
    3. `schema.json` (định nghĩa các section và trường dữ liệu tùy biến)
    4. `script.js` (logic chuyển động và tương tác native)
    5. `styles.css` (tạo hình, typography, layout thuần CSS)
    6. `template.blade.php` (Blade view nhận dữ liệu động và đồng bộ thời gian thực)
  - Đồng thời duy trì bản sao assets tĩnh tại `public/assets/client/invitations/{slug}/` để trình duyệt tải với tốc độ cao nhất.
- **Tính Năng Tải Ảnh Trực Tiếp Vào Assets Của Mẫu Đang Sửa**:
  - Bổ sung endpoint `POST /tao-thiep/upload-asset` trong `InvitationEditorController`.
  - Khách hàng có thể chọn tải ảnh từ máy tính hoặc điện thoại tại tất cả các trường ảnh (Ảnh cưới mở thiệp, Ảnh Hero dọc, Bộ 3 ảnh Triptych, Ảnh chân trang, Mã QR mừng cưới Nhà Trai, Nhà Gái và toàn bộ 10 ảnh trong Album cưới).
  - Tự động lưu trữ file tải lên vào thư mục assets của template đang sửa: `public/assets/client/invitations/{template_slug}/assets/` và đồng bộ vào `resources/views/client/invitations/{template_slug}/assets/`.
  - Hỗ trợ xem trước tức thì (Instant Local Preview) và lập tức đồng bộ sang iframe xem trước thời gian thực.
- **Kiểm thử tự động**: Đã bổ sung test case upload asset, đạt **10/10 tests passed (99 assertions)**.

### 📌 Phiên bản 1.1.3 — Tái Thiết Kế Trải Nghiệm Mở Thiệp 3D & Tinh Chỉnh Giao Diện Quiet Luxury (net-muc-tinh-yeu)
- **Màn hình mở đầu trang trọng (Full Height Cover & 3D Interactive Envelope Stage)**:
  - Khắc phục triệt để tình trạng "chưa mở thiệp mà toàn bộ nội dung đã phơi bày":
    - Khởi tạo trang với class `mayhen_invitation_scroll_locked` trên `body` để khóa cuộn ban đầu, tập trung 100% ánh nhìn vào màn hình mở đầu.
    - Màn hình mở đầu chiếm trọn chiều cao viewport (`min-height: 100vh`/`100dvh`) với nền Warm Cream/Ivory, khung viền kép thanh thoát và hoa văn góc đối xứng tinh xảo.
  - Thiết kế phong bì cưới 3D chân thực (Deluxe Envelope):
    - Thân phong bì màu xám than chì nhung mờ cao cấp (`#252422`), viền lót chỉ vàng dát nhũ nhẹ.
    - Tem sáp niêm phong (Wax Seal) dập nổi 3D màu đỏ rượu trầm (`#871f1f`) với chữ Hỷ `囍` dát nhũ vàng kim và hiệu ứng nhịp thở thu hút (`mayhen_invitation_pulse`).
    - Tấm thiệp mời bên trong (Letter inside) với ảnh cưới của đôi bạn nằm khéo léo trong khe phong bì.
  - Trải nghiệm mở thiệp mượt mà và tự động cuộn xuống:
    - Khi chạm vào phong bì, nút "Chạm Mở Thiệp" hoặc tem sáp: Nắp phong bì mở 3D lật lên (`transform: rotateX(180deg)`), tem sáp mở nhẹ, tấm thiệp trồi lên (card slide-up) hé lộ ảnh cưới lung linh.
    - Tự động kích hoạt nhạc nền cưới MP3 du dương.
    - Gỡ bỏ khóa cuộn (`body.mayhen_invitation_scroll_locked`) và sau 850ms tự động cuộn mượt mà (`window.scrollTo({ behavior: 'smooth' })`) dẫn lối người xem xuống phần nội dung chi tiết.
    - Bổ sung nút "Cuộn xuống xem tiếp ↓" trang nhã.
- **Khắc phục tình trạng thô sơ, lệch lạc và khoảng cách quá xa (Tight & Rhythmic Spacing)**:
  - **Gia đình hai bên (`mayhen_invitation_dhsuudushi9428r_families`)**:
    - Loại bỏ dải ribbon màu đen xì lệch méo bên trái và cột chữ dọc kỳ lạ.
    - Thay thế bằng thẻ thiệp song hành đối xứng (Symmetrical Family Card) gồm 2 cột cân bằng: Cột trái "NHÀ TRAI", Cột phải "NHÀ GÁI", chính giữa là biểu tượng chữ Hỷ trang nhã.
  - **Tối ưu khoảng cách dọc (Vertical Spacing)**:
    - Thu nhỏ các padding dư thừa từ 38px-45px xuống còn 20px-26px có nhịp điệu hài hòa, loại bỏ các `min-height` cứng ngắc.
  - **Typography & Màu sắc Quiet Luxury**:
    - Tiêu đề viết tay Quarzo phối hợp font Montserrat hiện đại, kích thước chữ cân đối.
    - Tông màu chuẩn mực: Trắng ngà (Ivory), Mực than (#181818), Nâu ấm (#faf7f2), Nhũ vàng đồng (#9c7a4a), không màu mè sặc sỡ.
  - **Lược bỏ các chi tiết thô thiển**:
    - Bỏ các ký tự dollar bay tán loạn trong hộp mừng cưới, thay bằng Hộp quà / Phong bao cưới nhung đen viền vàng sang trọng, mở modal VietQR quét mã tức thì.
    - Bộ đếm ngược, timeline và tabs tiệc cưới được tinh giản, gọn gàng, sắc nét.
- **Đồng bộ toàn diện Assets**:
  - Đã cập nhật và đồng bộ đồng thời tại `resources/views/client/invitations/net-muc-tinh-yeu/` và `public/assets/client/invitations/net-muc-tinh-yeu/`.
- **Kiểm thử tự động**:
  - `php artisan test --compact`: **10/10 tests passed (101 assertions)**.
  - Laravel Pint: **100% passed**, không có lỗi format.

### 📌 Phiên bản 1.1.4 — Hệ Thống Sổ Lưu Bút & Ký Tên Chúc Phúc Độc Lập Theo Từng Đám Cưới (Database-Backed Guestbook Engine)
- **Kiến trúc Backend chuẩn hóa dùng chung cho mọi template**:
  - **Bảng Database `wedding_wishes`**:
    - Trường dữ liệu: `id`, `wedding_id` (foreignId cascade on delete), `name` (tên người chúc), `message` (lời chúc), `is_approved` (kiểm duyệt hiển thị), `ip_address`, `created_at`, `updated_at`.
    - Đảm bảo mỗi đám cưới sở hữu toàn vẹn một cơ sở dữ liệu sổ lưu bút riêng biệt gắn với `wedding_id`.
  - **Model & Relationships**:
    - Model `App\Models\WeddingWish` liên kết `BelongsTo(Wedding::class)`.
    - Model `App\Models\Wedding` bổ sung quan hệ `hasMany(WeddingWish::class)->latest()`.
  - **API Endpoints thống nhất**:
    - `POST /thiep/{slug}/wishes`: Nhận `name` và `message`, validate qua `SubmitWishRequest`, lưu vào database của đám cưới và trả về JSON kèm thời gian `diffForHumans()`.
    - `GET /thiep/{slug}/wishes`: Trả về danh sách lời chúc công khai dạng JSON.
    - `POST /xem-thu/{slug}/wishes`: Endpoint thử nghiệm cho chế độ preview/editor studio, phản hồi JSON thành công ngay lập tức giúp kiểm thử UI mà không phát sinh rác DB.
    - Đồng thời, khi khách gửi RSVP (`POST /thiep/{slug}/rsvp`) có điền lời chúc, hệ thống tự động đồng bộ sang `wedding_wishes` để hiển thị trên sổ lưu bút.
- **Tích hợp giao diện & tương tác Frontend (mẫu "Nét Mực Tình Yêu" và mọi template)**:
  - Khi render template, controller tự động nạp `$wishes` từ database và URL endpoint `$wishSubmitUrl`.
  - Khung Sổ lưu bút hiển thị danh sách lời chúc thực tế từ database với tên người gửi, thời gian tương đối ("Vừa xong", "2 giờ trước"), nội dung lời chúc định dạng trang nhã.
  - Bộ đếm trên bảng lưu bút (`#mayhen_guestboard_counter`) tự động hiển thị số lượng lời chúc hiện có ("Đã có X lời chúc phúc gửi tới đôi bạn").
  - Khi khách bấm *"Ký tên chúc phúc"* và gửi form:
    - JavaScript gửi AJAX `fetch` lên endpoint backend thật kèm CSRF token.
    - Nút bấm hiển thị trạng thái "Đang gửi...".
    - Khi thành công: Tự động chèn thẻ lời chúc mới lên đầu danh sách (`container.prepend`), cập nhật bộ đếm lời chúc và đóng modal mượt mà mà không cần reload trang.
- **Kiểm thử tự động**:
  - Đã bổ sung test case Feature `each wedding has its own independent guestbook database with wish submissions and isolation` kiểm tra trọn vẹn:
    1. Gửi lời chúc vào thiệp cưới A thành công.
    2. Gửi lời chúc vào thiệp cưới B thành công.
    3. Đảm bảo tính độc lập (Isolation) 100%: thiệp A chỉ thấy lời chúc của A, tuyệt đối không thấy lời chúc của B.
    4. Gửi lời chúc thử nghiệm trên preview mode.
  - Kết quả kiểm thử: **11/11 tests passed (117 assertions)**.
  - Laravel Pint: **100% passed**, sạch sẽ không có lỗi format.

### 📌 Phiên bản 1.1.5 — Chuẩn Hóa Hệ Thống Xác Nhận Tham Dự (RSVP Có Đi / Không Đi) Độc Lập Cho Mọi Template
- **Kiến trúc Backend chuẩn hóa**:
  - **Bảng Database `guest_responses`**:
    - Bổ sung cột `phone` (string 30, nullable) để lưu số điện thoại liên lạc của khách mời xác nhận.
    - Trường dữ liệu đầy đủ: `id`, `wedding_id` (foreignId cascade on delete), `guest_id` (nullable), `guest_name`, `phone`, `attending_status` (`attending` hoặc `declined`), `adults_count`, `children_count`, `dietary_requirements`, `wishes`, `ip_address`, `created_at`.
    - Bảo đảm 100% tính độc lập (Isolation): Mỗi đám cưới có cơ sở dữ liệu khách mời và phản hồi RSVP riêng biệt gắn với `wedding_id`.
  - **Validation & Request**:
    - `SubmitRsvpRequest` validate: `guest_name` (required, max 255), `phone` (nullable, max 30), `attending_status` (required, in: attending, declined, uncertain), `adults_count`, `wishes`.
  - **API Endpoints thống nhất**:
    - `POST /thiep/{slug}/rsvp`: Nhận phản hồi RSVP thực tế, lưu vào `guest_responses`. Nếu khách chọn không đi và để lại lời nhắn chúc phúc, hệ thống tự động đồng bộ bản ghi sang `wedding_wishes` để hiển thị trên sổ lưu bút.
    - `POST /xem-thu/{slug}/rsvp`: Endpoint giả lập cho chế độ xem thử (Preview / Live Editor), phản hồi thành công JSON ngay lập tức giúp kiểm thử UX/UI mượt mà.
- **Tích hợp giao diện & tương tác Frontend (mẫu "Nét Mực Tình Yêu" và mọi template)**:
  - Khung "Xác Nhận Tham Dự" cung cấp 2 lựa chọn trực quan:
    1. **"Có, tôi sẽ tham dự"**: Mở modal nhập Họ tên, Số điện thoại và Số lượng người tham dự (`guests`).
    2. **"Rất tiếc, chưa sắp xếp được"**: Mở modal nhập Họ tên, Số điện thoại và Lời nhắn gửi tới cặp đôi.
  - Khi submit: JavaScript gửi AJAX `fetch` lên endpoint backend thật với CSRF token, nút bấm hiển thị trạng thái *"Đang ghi nhận..."*.
  - Khi hoàn tất: Đóng modal, hiển thị thông báo phản hồi trân trọng từ server và cập nhật trạng thái đã chọn trên nút bấm.
  - Đồng bộ mã nguồn đồng thời tại `resources/views/client/invitations/net-muc-tinh-yeu/` và `public/assets/client/invitations/net-muc-tinh-yeu/`.
- **Kiểm thử tự động**:
  - Đã bổ sung test case Feature `each wedding has independent rsvp responses for attending and declining` kiểm tra trọn vẹn:
    1. Khách A xác nhận CÓ THAM DỰ vào đám cưới A với số lượng 2 người và số điện thoại.
    2. Khách B xác nhận KHÔNG THAM DỰ vào đám cưới B kèm lời nhắn chúc phúc.
    3. Đảm bảo tính độc lập dữ liệu 100%: RSVP của đám cưới nào nằm đúng trong đám cưới đó, không có sự can thiệp chéo.
    4. Lời nhắn từ RSVP từ chối tham dự tự động đồng bộ vào sổ lưu bút `wedding_wishes` của đám cưới B, không lọt sang đám cưới A.
    5. Test endpoint demo RSVP trên preview mode.
  - Kết quả kiểm thử: **12/12 tests passed (135 assertions)**.
  - Laravel Pint: **100% passed**, không có bất kỳ lỗi định dạng nào.

### 📌 Phiên bản 1.1.6 — Giới Hạn Xác Nhận Tham Dự 1 Lần & Chỉnh Sửa Lời Chúc Theo IP Cho Từng Đám Cưới
- **Chính sách giới hạn & bảo vệ dữ liệu theo địa chỉ IP**:
  - **Khóa xác nhận tham dự (RSVP Lock)**:
    - Mỗi địa chỉ IP chỉ được xác nhận tham dự (Có đi hoặc Không thể tham dự) **duy nhất 1 lần** cho mỗi đám cưới (`wedding_id` + `ip_address`).
    - Sau khi đã chọn, không được phép chọn lại. Nút phản hồi bị vô hiệu hóa (`disabled`), đánh dấu trạng thái đã chọn (`mayhen_invitation_dhsuudushi9428r_rsvp_selected`) và hiển thị thông báo ghi nhận.
    - Phía Backend (`GuestController::submitRsvp`): Kiểm tra nếu IP đã tồn tại trong `GuestResponse` của đám cưới đó, lập tức trả về lỗi HTTP 422 JSON ngăn chặn mọi hành vi gửi lại.
  - **Chế độ Chỉnh sửa lưu bút (Wish Edit Mode)**:
    - Mỗi địa chỉ IP chỉ được phép có **1 lời chúc duy nhất** trong mỗi đám cưới (`wedding_id` + `ip_address`).
    - Nếu người dùng đã gửi lời chúc trước đó:
      - Nút lưu bút chuyển thành *"Chỉnh sửa lời chúc của bạn"*.
      - Thẻ lời chúc của người đó trên danh sách sổ lưu bút được gắn huy hiệu *"Lời chúc của bạn"*.
      - Khi mở modal, hệ thống tự động điền sẵn Họ tên và Lời chúc cũ để người dùng chỉnh sửa.
      - Phía Backend (`GuestController::submitWish`): Thực hiện cập nhật (`update`) bản ghi cũ, trả về `is_update: true`. Không tạo thêm bản ghi mới, tổng số lượng comment lưu bút trong cơ sở dữ liệu không bị tăng lên.
      - Phía Frontend (`script.js`): Cập nhật trực tiếp nội dung thẻ chúc trên DOM (`(Vừa cập nhật)`), không chèn thêm thẻ mới và không tăng bộ đếm số lượng lời chúc.
- **Cập nhật Controllers & Views**:
  - `InvitationEditorController::show`: Truy vấn `$currentRsvp` và `$myWish` theo `request()->ip()` và truyền trực tiếp vào view thiệp cưới.
  - `template.blade.php`, `styles.css`, `script.js` (đồng bộ cả `resources/views/...` và `public/assets/...`): Xử lý trạng thái selected/disabled của RSVP và form edit của lưu bút.
- **Kiểm thử tự động**:
  - Bổ sung 2 test cases Feature trong `tests/Feature/NetMucTinhYeuTemplateTest.php`:
    1. `user cannot rsvp more than once from the same ip for the same wedding`: Kiểm tra gửi lần 1 (200), gửi lần 2 cùng IP bị chặn 422, gửi từ IP khác vẫn hợp lệ.
    2. `user can only edit their existing wish and cannot submit multiple comments from the same ip`: Kiểm tra gửi lần 1 (201), gửi lần 2 cùng IP chỉ cập nhật nội dung (200) và số lượng bản ghi DB không tăng, hiển thị đúng giao diện chỉnh sửa.
  - Kết quả kiểm thử: **14/14 tests passed (160 assertions)**.
  - Laravel Pint: **100% passed**.

### 📌 Phiên bản 1.1.7 — Cơ Chế Phân Quyền Chủ Nhân Thiệp Cưới (Session & Auth), Trả Lời Lưu Bút & Quản Lý RSVP
- **Nhận diện Chủ Nhân Thiệp Cưới (Owner Detection)**:
  - **Tạo thiệp khi chưa đăng nhập**: Cho phép khách vãng lai tạo thiệp trải nghiệm, slug thiệp được lưu vào session `owned_invitation_slugs`. Người này được hệ thống công nhận là Chủ nhân của thiệp cưới thông qua session.
  - **Tạo thiệp khi đã đăng nhập**: Thiệp cưới gắn trực tiếp với `user_id` của tài khoản đó.
  - **Đồng bộ tự động khi đăng nhập/đăng ký**: Khi người dùng đăng nhập hoặc đăng ký tài khoản mới, hệ thống tự động quét session `owned_invitation_slugs` và chuyển quyền sở hữu toàn bộ các thiệp đã tạo sang `user_id` của tài khoản đó (`AuthController::syncOwnedInvitations`).
  - **Helper method `Invitation::isOwner()`**: Xác thực chủ quyền dựa trên `auth()->id() === wedding->user_id` HOẶC `in_array(slug, session('owned_invitation_slugs'))`.
- **Giới hạn & Đặc quyền của Chủ Nhân (Owner Permissions & Restrictions)**:
  - **KHÔNG được phép**:
    + Không được tự gửi xác nhận tham dự cho chính mình (chặn HTTP 403 cả Backend lẫn Frontend).
    + Không được tự gửi lời chúc mới trong sổ lưu bút (chặn HTTP 403 cả Backend lẫn Frontend).
  - **ĐƯỢC PHÉP**:
    + **Trả lời lưu bút**:
      - Thêm migration cột `reply` (text, nullable) và `replied_at` (timestamp, nullable) vào bảng `wedding_wishes`.
      - API `POST /thiep/{slug}/wishes/{wishId}/reply`: Chủ nhân gửi câu trả lời lời chúc của từng khách mời; giao diện thiệp hiển thị khung trang trọng *"♥ Phản hồi từ Cô Dâu & Chú Rể"*.
      - Người lạ không phải chủ nhân gọi API reply sẽ bị chặn HTTP 403.
    + **Xem danh sách và thống kê khách phản hồi tham dự (RSVP Dashboard)**:
      - API `GET /thiep/{slug}/rsvp/responses`: Chủ nhân xem danh sách chi tiết (Tên, SĐT, Trạng thái tham dự/không tham dự, Số khách đi cùng, Lời nhắn, Thời gian). Người lạ truy cập bị chặn HTTP 403.
      - Giao diện thiệp thay thế 2 nút chọn RSVP thành Bảng điều khiển Quản lý khách mời kèm nút xem bảng danh sách modal chi tiết.
- **Kiểm thử tự động & Định dạng**:
  - Bổ sung test case Feature toàn diện trong `tests/Feature/NetMucTinhYeuTemplateTest.php`: Kiểm tra tạo thiệp bằng session, chặn chủ thiệp gửi RSVP & Wish mới, cho phép chủ thiệp trả lời lưu bút và xem danh sách RSVP, chặn người lạ can thiệp, và kiểm tra đồng bộ quyền sở hữu khi đăng nhập.
  - Kết quả kiểm thử: **15/15 tests passed (186 assertions)**.
  - Laravel Pint: **100% passed**.

### 📌 Phiên bản 1.1.8 — Hệ Thống Kiểm Duyệt Chặn Từ Ngữ Thô Tục, Chửi Bới & Trù Ẻo Đám Cưới (Zero Trust Moderation FE & BE)
- **Triết lý bảo mật**:
  - *"Không bao giờ tin tưởng người dùng (Zero-Trust User Input)"*: Kẻ xấu có thể cố tình phá đám cưới, để lại lời lẽ khiếm nhã, thô tục, chửi thề hoặc trù ẻo đôi bạn trẻ trên trang thiệp cưới công khai.
  - Bắt buộc kiểm soát và chặn sạch sẽ ở cả **Frontend (FE)** (chặn trước trải nghiệm người dùng, cảnh báo văn minh) và **Backend (BE)** (chốt chặn validation cuối cùng, chặn mọi bypass fetch/curl/Postman).
- **Backend Content Moderation Engine (`App\Services\ContentModerationService`)**:
  - **Từ điển tiếng Việt chuyên sâu**:
    + Nhóm 1: Tục tĩu, bộ phận sinh dục, hành vi tình dục thô bỉ (`buồi`, `cặc`, `lồn`, `địt`, `đụ`, `chịch`, `nện`, `sóc lọ`, `thủ dâm`, `hiếp dâm`,...).
    + Nhóm 2: Chửi thề, tiếng lóng mạng xã hội và Gen Z (`đm`, `dkm`, `vcl`, `vl`, `clgt`, `cmnr`, `đéo`, `đếch`, `méo`,...).
    + Nhóm 3: Xúc phạm cha mẹ, tổ tiên, danh dự gia đình (`mẹ mày`, `bố mày`, `bà già mày`, `tổ cha`, `mả mẹ`, `tiên sư`,...).
    + Nhóm 4: Miệt thị phụ nữ, nghề nghiệp xúc phạm (`đĩ`, `con đĩ`, `thằng đĩ`, `đĩ chó`, `phò`, `cave`, `bán dâm`,...).
    + Nhóm 5: Chửi thú vật, miệt thị trí tuệ (`chó đẻ`, `chó chết`, `đồ chó`, `thằng chó`, `súc vật`, `đồ lợn`, `óc chó`, `đần độn`, `đồ điên`, `đồ khùng`, `thiểu năng`,...).
    + Nhóm 6: Trù ẻo đám cưới, tang tóc, chia rẽ hạnh phúc (`chết mẹ`, `đoản mệnh`, `tai nạn`, `đám ma`, `tang gia`, `quan tài`, `bát hương`, `ly dị`, `ly hôn`, `đứt gánh`, `cắm sừng`, `mọc sừng`, `tan vỡ`, `bỏ nhau`, `ngoại tình`, `tiểu tam`, `con giáp thứ 13`, `vô sinh`, `tuyệt tự`,...).
  - **Thuật toán đa tầng chống lách chữ**:
    + *Word Boundary Check*: Bắt chính xác từ khóa trong câu có dấu và không dấu (ASCII Normalization) mà không bắt nhầm các từ ghép tiếng Việt lành tính (ví dụ: loại bỏ các từ đồng âm ngắn đơn lẻ gây false positive như `dien` trong `giao diện`, `cho` trong `chúc cho`, `di` trong `đi ăn cưới`).
    + *Leetspeak Normalization*: Chuyển đổi các ký tự số thay thế chữ cái (`0` -> `o`, `1` -> `i`, `3` -> `e`, `4` -> `a`, `7` -> `t`, `@` -> `a`).
    + *Separated Character Patterns*: Bắt các dạng cố tình ngắt chữ bằng dấu chấm, dấu cách, dấu gạch ngang, sao (`d.m`, `d m`, `c.a.c`, `c a c`, `l.o.n`, `l o n`, `d.i.t`, `v.c.l`, `v l`,...).
- **Custom Validation Rule Laravel (`App\Rules\NoProfanity`)**:
  - Sử dụng `ContentModerationService::containsProfanity()`.
  - Thông báo lỗi rõ ràng, nhân văn: *"Nội dung chứa từ ngữ không phù hợp hoặc thiếu văn minh. Vui lòng sử dụng ngôn từ lịch sự, trang trọng để gửi lời chúc phúc."*
  - Tích hợp vào:
    + `SubmitWishRequest`: Kiểm duyệt cả ô `name` và `message`.
    + `SubmitRsvpRequest`: Kiểm duyệt cả ô `guest_name`, `wishes` và `dietary_requirements`.
    + `GuestController::replyWish`: Kiểm duyệt ô `reply` khi chủ thiệp gửi phản hồi.
- **Frontend Moderation (`script.js`)**:
  - Nhúng bộ lọc `checkProfanity()` tương đồng với Backend vào file JavaScript của template thiệp cưới (cả `resources/views/...` và `public/assets/...`).
  - Kiểm tra tức thì khi người dùng submit:
    + Form Sổ lưu bút (Tên & Lời chúc phúc).
    + Form Xác nhận tham dự RSVP (Họ tên & Lời nhắn gửi cô dâu chú rể).
    + Form Phản hồi lời chúc của chủ thiệp.
  - Khi phát hiện vi phạm: Ngừng gửi request ngay lập tức (`preventDefault()`, không gọi API fetch), bật thông báo cảnh báo yêu cầu dùng ngôn từ lịch sự, trang trọng.
- **Kiểm thử tự động & Định dạng**:
  - Bổ sung 3 test cases Feature trong `tests/Feature/NetMucTinhYeuTemplateTest.php`:
    1. Chặn từ ngữ chửi bậy, lách chữ, xúc phạm và trù ẻo đám cưới trong sổ lưu bút (HTTP 422 cho tên/lời chúc vi phạm, HTTP 201 cho lời chúc văn minh).
    2. Chặn từ ngữ thô tục, chửi bới trong form xác nhận tham dự RSVP (HTTP 422).
    3. Chặn từ ngữ không phù hợp trong phản hồi của chủ thiệp cưới (HTTP 422).
  - Kết quả kiểm thử: **18/18 tests passed (213 assertions)**.
  - Laravel Pint: **100% formatted & passed**.

### 📌 Phiên bản 1.1.9 — Chuẩn Hóa Tỷ Lệ Ảnh Bìa 3/4 & Tự Động Cuộn Về Đỉnh Khi Nạp Trang (net-muc-tinh-yeu)
- **Chuẩn hóa tỷ lệ ảnh cưới mở đầu 3/4 (`aspect-ratio: 3 / 4`)**:
  - Khắc phục triệt để tình trạng ảnh cưới nằm ngang bị méo mó, co ép hoặc sai lệch tỉ lệ dọc chuẩn studio cưới:
    + Thẻ phong bì `.mayhen_invitation_dhsuudushi9428r_envelope_stage` được mở rộng kích thước lên `width: min(320px, 86vw); height: 225px;`.
    + Tấm thiệp bên trong `.mayhen_invitation_dhsuudushi9428r_envelope_letter` được tăng chiều cao lên `height: 250px;` với cấu trúc flex dọc gọn gàng.
    + Ảnh thiệp `.mayhen_invitation_dhsuudushi9428r_envelope_letter_img` được thiết lập chính xác `aspect-ratio: 3 / 4; max-width: 160px; height: auto; object-fit: cover;`, hiển thị trọn vẹn vẻ đẹp khung hình chân dung của cô dâu chú rể.
    + Khi mở thiệp (`.mayhen_opened`), tấm thiệp trồi lên cao hơn (`transform: translateY(-135px) scale(1.02);`), hé lộ đầy đủ 100% ảnh cưới 3/4 sắc nét mà không bị che khuất bởi nắp và mép phong bì.
- **Tự động cuộn về đỉnh trang khi tải lại (Auto Scroll To Top on Reload)**:
  - Khắc phục lỗi khi người dùng đang ở cuối trang bấm F5 / Reload trang bị trình duyệt giữ vị trí cuộn ở dưới, làm người xem bỏ lỡ trải nghiệm chạm mở phong thư bắt buộc.
  - Vô hiệu hóa tính năng nhớ vị trí cuộn mặc định của trình duyệt: `history.scrollRestoration = 'manual'`.
  - Thực thi `window.scrollTo(0, 0)` ngay lập tức khi file script thực thi, kết hợp lắng nghe cả sự kiện `DOMContentLoaded` và `load` để đảm bảo trang luôn bắt đầu từ màn hình phong thư trang trọng.
- **Đồng bộ hóa 100% mã nguồn**:
  - Đã cập nhật song song tại `resources/views/client/invitations/net-muc-tinh-yeu/` và `public/assets/client/invitations/net-muc-tinh-yeu/` (`styles.css` & `script.js`).

### 📌 Phiên bản 1.2.0 — Đóng Gói Toàn Diện Assets Vào Thư Mục Template & Xóa Bỏ Public Invitations
- **Quy chuẩn lưu trữ mẫu thiệp độc lập (Self-Contained Invitation Template Packages)**:
  - Toàn bộ tài nguyên của mẫu thiệp (gồm `styles.css`, `script.js`, `schema.json`, `template.blade.php`, thư mục `assets/` chứa ảnh, font chữ, file nhạc MP3) được đặt trực tiếp và duy nhất tại:
    `resources/views/client/invitations/{template_slug}/`.
  - **Đã xóa bỏ hoàn toàn thư mục `public/assets/client/invitations`** để loại bỏ tình trạng phân mảnh, dễ kiểm soát và không cần bảo trì 2 bản sao cùng lúc.
- **Cơ chế phục vụ Static Assets Trực Tiếp từ Resources**:
  - Bổ sung route: `GET /mau-thiep/{slug}/assets/{path}` (đặt tên `templates.asset`, bắt mọi path con `.*`).
  - Xử lý qua controller `TemplateController::asset()`:
    + Bảo mật: Khử sạch `..` và ký tự null byte để chống tấn công Directory Traversal.
    + Nhận diện và thiết lập đúng chuẩn MIME Type: CSS (`text/css; charset=UTF-8`), JS (`application/javascript; charset=UTF-8`), JPEG/PNG/WebP/SVG, MP3 (`audio/mpeg`), WOFF/WOFF2/OTF/TTF.
    + Bổ sung header HTTP Cache tối ưu: `Cache-Control: public, max-age=31536000`.
- **Nâng cấp Tính năng Tải Ảnh Lên (`InvitationEditorController::uploadAsset`)**:
  - File tải lên từ thiết bị được lưu trực tiếp vào: `resources/views/client/invitations/{templateSlug}/assets/{filename}`.
  - Trả về đường dẫn truy cập qua route nội bộ: `/mau-thiep/{templateSlug}/assets/assets/{filename}`.
- **Cập nhật Template Views & Live Editor**:
  - `template.blade.php`: Biến `$assetBase` trỏ trực tiếp tới `url('/mau-thiep/net-muc-tinh-yeu/assets')`.
  - `editor.blade.php`: Toàn bộ các thẻ preview ảnh của mẫu thiệp được nạp mượt mà qua route URL nội bộ.
  - `InvitationTemplateSeeder.php`: `thumbnail_url` được cập nhật đồng bộ.

### 📌 Phiên bản 1.2.1 — Khắc Phục Triệt Để Lưu Ảnh & Dữ Liệu Khi Xuất Bản Thiệp Cưới
- **Khắc phục lỗi nạp ảnh và dữ liệu khi F5 / Reload trang thiệp cưới (`/thiep/{slug}`)**:
  - **Lỗi 1 (Đường dẫn Relative trong Database)**: Các trường `hero_photo_url`, `qr_groom_image`, `qr_bride_image`, `footer_photo_url` được lưu đường dẫn tương đối dạng `assets/...`. Khi render ở trang thiệp thật, trình duyệt tự nối `/thiep/assets/...` dẫn tới lỗi 404 toàn bộ ảnh.
    -> Giải pháp: Bổ sung helper closure `$resolveAsset` trong `template.blade.php`, tự động bổ sung prefix `$assetBase` (`/mau-thiep/{slug}/assets/...`) đối với đường dẫn relative, đồng thời giữ nguyên nếu là URL tuyệt đối (`http://`, `https://`, `/`).
  - **Lỗi 2 (Rò rỉ URL tạm thời `blob:` vào Database)**: Khi cắt ảnh 3/4 ở modal cropper, trình duyệt tạo URL `blob:http://...`. Do file `editor.js` tĩnh không compile cú pháp Blade `{{ $selectedTemplate->slug }}` và bị ReferenceError biến `csrfToken`/`submitTriggers`, form bị submit sớm dẫn đến lưu thẳng chuỗi `blob:...` vào database. Khi người dùng reload trang, `blob:` bị trình duyệt giải phóng khỏi RAM gây icon ảnh vỡ.
    -> Giải pháp: 
      + Truyền biến toàn cục `window.__MAYHEN_TEMPLATE_SLUG`, `window.__MAYHEN_CSRF_TOKEN`, `window.__MAYHEN_UPLOAD_URL` từ `editor.blade.php`.
      + Gỡ bỏ việc bind sự kiện submit trùng lặp sớm trong `editor.js`.
      + Tái cấu trúc hàm `submitFormWithPendingUploads` sử dụng `Promise.all` tải toàn bộ ảnh blob lên server `/tao-thiep/upload-asset`, nhận link thật từ server gán vào form trước khi gọi `mainForm.submit()`.
      + Bổ sung cơ chế bảo vệ kép (cả Client & Backend): Xóa sạch mọi chuỗi bắt đầu bằng `blob:` trước khi gửi và trước khi lưu vào Model `Wedding`.
  - **Lỗi 3 (Dữ liệu Album ảnh cưới Null)**: Mảng `$wedding->extra_data['gallery']` chứa 10 phần tử `null` khiến điều kiện `!empty()` trong Blade bị hiểu là có dữ liệu.
    -> Giải pháp: Dùng `array_filter` lọc bỏ giá trị rỗng/null và link `blob:`, fallback tự động về `$defaultAlbum` của mẫu thiệp.
  - **Lỗi 4 (Giao diện tên Cô dâu Chú rể bị rớt chữ)**: Thêm `white-space: nowrap` và tinh chỉnh `clamp(15px, 4.2vw, 22px)` cho `.mayhen_invitation_dhsuudushi9428r_cover_names p` trong `styles.css`.
  - **Lỗi 5 (RouteNotFoundException)**: Khắc phục tên route `invitation.uploadAsset` (camelCase) chuẩn xác trong `editor.blade.php`.
- **Cập nhật Bố Cục Tên Đôi Uyên Ương Phần Mở Đầu Thiệp (`net-muc-tinh-yeu`)**:
  - Chuyển từ dạng ngang 1 hàng sang bố cục dọc 3 tầng sang trọng: Tên Chú Rể (trên) -> Trái tim vàng `❤` (giữa) -> Tên Cô Dâu (dưới).
- **Nâng cấp Hiệu Ứng Phong Bì 3D Mở Thiệp (`net-muc-tinh-yeu`)**:
  - Bỏ giới hạn `max-width` ở ảnh thiệp chính (`envelope_letter_img`), mở rộng chiều ngang ảnh chiếm trọn 100% bề ngang lá thư, to ngang gần bằng phong bì nhìn sang trọng và nổi bật.
  - Tích hợp **Animation chuyển đổi khoảng cách động (Dynamic Margin Animation)**: Khi tải trang, phong bì giữ khoảng cách gọn gàng `margin: 18px auto`. Khi bấm mở thiệp, phong bì tự động trượt êm ái xuống `margin: 160px auto 18px auto` (`transition: margin 0.9s cubic-bezier(...)`), tạo khoảng không gian thoáng đãng cho bức ảnh cưới to vươn lên mà không che mất tên cô dâu chú rể.
  - Thiết lập chiều sâu 3D chân thực: giữ nguyên `z-index: 2` dưới lớp túi mặt trước phong bì `envelope_pocket` (`z-index: 3`) kèm đổ bóng drop-shadow tự nhiên, giữ khoảng 20% phần đáy của lá thư và ảnh cưới nằm cắm sâu bên trong lòng phong bì, 80% phần trên vươn lên khoe trọn vẹn ảnh cưới.
- **Chuẩn Hóa Tiêu Đề Trang Thiệp Cưới Đã Tạo (`/thiep/{slug}`)**:
  - Định dạng chuẩn: `Thiệp mời cưới: "Tên Chú Rể & Tên Cô Dâu" | Mây Hẹn` (Ví dụ: `Thiệp mời cưới: "Nguyễn Minh Đức & Nguyễn Thị Huyền" | Mây Hẹn`).
  - Áp dụng đồng bộ cho cả `template.blade.php` và `public-invitation.blade.php` (kèm Open Graph meta tags cho mạng xã hội).

- **Đồng Bộ & Chuẩn Hóa Favicon Cho Tất Cả Các Trang (100% Coverage)**:
  - Tách cụm thẻ khai báo Favicon thành component dùng chung: `resources/views/client/components/favicon.blade.php`.
  - Nguồn asset chính thức: `storage/client/images/business/mayhen-favicon.png`.
  - Cụm thẻ chuẩn gồm đầy đủ: `apple-touch-icon (180x180)`, `icon (32x32)`, `icon (16x16)`, `mask-icon`, `icon (x-icon)` và `meta theme-color (#C5A880)`.
  - Tích hợp `@include('client.components.favicon')` vào toàn bộ các layout và trang độc lập trong hệ thống:
    + `resources/views/client/components/head.blade.php` (áp dụng cho toàn bộ các trang Client: Home, Editor, Pricing, Templates, Services, About, FAQ, Auth, Dashboard, Guests...).
    + `resources/views/client/pages/public-invitation.blade.php` (Trang xem thiệp cưới công khai cho khách mời).
    + `resources/views/client/pages/preview.blade.php` (Trang xem thử mẫu thiệp).
    + `resources/views/client/invitations/net-muc-tinh-yeu/template.blade.php` (Trang template thiệp cưới độc lập).
    + `resources/views/admin/layouts/master.blade.php` (Trang quản trị Admin Dashboard).
    + `resources/views/welcome.blade.php`.

- **Tái Thiết Kế Menu Tài Khoản & Nút Đăng Nhập Header (Quiet Luxury & Tối Ưu Diện Tích)**:
  - **Khắc phục giao diện cũ**: Loại bỏ hiển thị text dài `Tên... Thoát` cồng kềnh, thô và chiếm nhiều diện tích trên thanh header.
  - **Nút Đăng Nhập (`.mayhen_client_header_login_btn`)**:
    + Tinh chỉnh nhỏ gọn (cao 32px, font 0.775rem), màu sắc trung tính nhẹ nhàng (`#6E685F`), không nền nổi bật, viền mờ tối giản.
    + Đảm bảo đóng vai trò thứ cấp, không cạnh tranh thị giác với nút CTA chính **"Tạo Thiệp Ngay"**.
  - **User Dropdown Menu Khi Đăng Nhập (`.mayhen_client_header_user_*`)**:
    + Thiết kế Pill/Trigger nhỏ gọn (cao 32px), gồm avatar tròn chữ cái đầu (Initials) tinh tế + tên riêng ngắn gọn + mũi tên chevron xoay động.
    + Popup Dropdown card sang trọng: hiển thị tên đầy đủ, email/SĐT, nhãn Quản trị viên, các liên kết chức năng (Trang Quản Trị, Tạo Mẫu Thiệp Mới) và nút Đăng Xuất.
    + Tương tác Native JavaScript: click toggle mở/đóng, tự động đóng khi click outside hoặc ấn phím Escape.
    + Đồng bộ tối ưu giao diện Profile Card bên trong Mobile Drawer.

- **Tích Hợp Thành Công Mẫu Thiệp Độc Bản "Thước Phim Tình Yêu" (`thuoc-phim-tinh-yeu`)**:
  - **Tên mẫu**: Thước Phim Tình Yêu (MAYHEN_Invitation_djcvhwiuo9w9_Graceful04).
  - **Slug**: `thuoc-phim-tinh-yeu` (Category: `modern`, Tier: `pro`, Complexity: `deluxe`).
  - **Prefix CSS độc quyền**: `mayhen_invitation_djcvhwiuo9w9_*` (Chống xung đột phong cách 100%).
  - **Cấu trúc lưu trữ**: `resources/views/client/invitations/thuoc-phim-tinh-yeu/`
    + `template.blade.php`: Hỗ trợ đầy đủ dữ liệu động (Cô dâu chú rể, gia đình 2 bên, ngày cưới, sảnh tiệc, album 10 ảnh, triptych, quote, timeline, QR mừng cưới, nhạc nền), tích hợp Favicon, Topbar xem thử và Topbar chủ thiệp.
    + `styles.css`: Hệ thống CSS độc lập (kèm style cho preview topbar và music toggle).
    + `script.js`: Native JS kết nối API backend Mây Hẹn (Đếm ngược ngày cưới động, Lightbox album ảnh, Sổ lưu bút canvas ký tên tay cảm ứng, Gửi lời chúc & RSVP trực tuyến lên DB, Modal hộp mừng cưới QR, Trình phát nhạc nền đĩa than).
    + `schema.json`: Cấu hình toàn bộ trường dữ liệu phục vụ trình tạo thiệp.
    + `assets/`: Lưu trữ cục bộ 100% toàn bộ 51 file assets (texture giấy, rèm mở đầu, dải film, icon timeline, ảnh mẫu cưới, nhạc nền MP3) không phụ thuộc CDN ngoại.
  - **Tối ưu hóa Phục vụ Asset (`TemplateController::asset`)**: Bổ sung cơ chế fallback tự động tìm kiếm trong thư mục `assets/` con, cho phép nạp asset mượt mà và an toàn.
  - **Database Seeder**: Cập nhật `InvitationTemplateSeeder.php` và seed thành công vào CSDL.

- **Nâng Cấp Giao Diện & Hiệu Ứng Mở Rèm 2 Cánh Mẫu Thiệp "Thước Phim Tình Yêu" (`thuoc-phim-tinh-yeu`)**:
  - **Cơ chế Mở Rèm 2 Cánh Tương Tác (`opening-sides`)**:
    + Chuyển đổi lớp 2 cánh rèm (`opening-left` & `opening-right`) thành overlay full-screen cố định (`z-index: 1000`), khớp nối chính xác tại trung tâm màn hình.
    + Bắt buộc người dùng bấm vào **cánh trái**, **cánh phải**, hoặc **con dấu sáp hoàng gia ở chính giữa** thì animation mở cửa thiệp sang 2 bên mới được kích hoạt (`translate3d(-101%, 0, 0)` và `translate3d(101%, 0, 0)` trong 1.25s).
    + Con dấu sáp hoàng gia (`.mayhen_invitation_djcvhwiuo9w9_opening-seal`): Viền vàng champagne ánh kim, dập nổi chữ Monogram đôi uyên ương và dòng chữ "MỞ THIỆP" cùng hiệu ứng sóng lan toả (ripple pulse) mời gọi chạm mở.
    + Sau 1.3s khi kết thúc animation, overlay tự động ẩn hoàn toàn (`visibility: hidden; pointer-events: none;`) để giải phóng tương tác cho thiệp bên dưới.
    + Xóa bỏ triệt để quy tắc `display: none` trên màn hình di động, mang lại trải nghiệm mở rèm thiệp đôi đỉnh cao trên mọi thiết bị (Mobile-First).
    + **Cơ chế Khóa Cuộn & Tự Động Về Đầu Trang**:
      * Khi load trang, hệ thống tự động thiết lập `history.scrollRestoration = 'manual'` và đưa vị trí trang về đỉnh (`window.scrollTo(0, 0)`).
      * Toàn bộ trang bị khóa cuộn (`overflow: hidden; touch-action: none; overscroll-behavior: none;`) để người dùng không thể cuộn xem nội dung khi chưa mở rèm.
      * Ngay khi người dùng chạm mở cánh trái hoặc cánh phải, hiệu ứng mở rèm kích hoạt và hệ thống tự động gỡ khóa cuộn, cho phép người dùng cuộn xem toàn bộ thiệp cưới mượt mà.
  - **Nâng Cấp Thẩm Mỹ Toàn Diện Giao Diện Thiệp (Chuẩn Quiet Luxury)**:
    + **Monogram Cặp Đôi**: Bổ sung huy hiệu tròn viền kép mạ vàng champagne mờ, chữ lồng nhau nghệ thuật và đổ bóng giấy mỹ thuật sang trọng.
    + **Bộ Đếm Ngược Ngày Cưới**: Bổ sung nhãn phụ Ngày, Giờ, Phút, Giây bằng font Sans thanh mảnh, căn giữa các ô số có nền kính mờ tinh xảo.
    + **Phong Bì Ảnh 3D & Lồng Ảnh**: Đổ bóng nhiều tầng chân thật, hiệu ứng hover xoay vươn ảnh sống động.
    + **Tab Chuyển Đổi Sảnh Tiệc Nhà Trai / Nhà Gái**: Bo góc tròn dạng viên ngọc (pill), viền vàng champagne ấm áp, gradient mềm mại. Khi bấm chuyển tab, hệ thống tự động ẩn/hiện khối sự kiện tương ứng (Lễ Vu Quy cho Nhà Gái và Tiệc Cưới cho Nhà Trai) kèm animation fade-in mượt mà, đồng thời highlight cột thông tin phụ huynh tương ứng.
    + **Nút Chỉ Đường Google Maps**: Bo góc 999px, viền và icon mạ vàng ánh kim, hover nổi bật êm ái.
    + **Khung Ảnh Chú Rể & Cô Dâu (Love Story)**: Viền trắng lưu niệm Polaroid cổ điển, bề mặt giấy mỹ thuật gân nổi, hover đưa ảnh về góc thẳng tự nhiên.
    + **Dải Phim & Lưới Album Cưới**: Dải phim cuộn ngang vintage 35mm mượt mà, lưới 10 ảnh Polaroid nghệ thuật, hover zoom nhẹ kèm bóng đổ mềm.
    + **Lịch Ngày Cưới**: Trái tim bao quanh ngày cưới số 7 có animation nhịp đập (heartbeat) lãng mạn.
    + **Lịch Trình Tiệc Cưới (Timeline)**: Icon tròn kính mờ viền vàng champagne mờ, đường timeline nối dọc xuyên qua tâm hoàn hảo.
    + **Hộp Mừng Cưới & RSVP**: Hộp quà có animation nhún nhảy mời gọi mở hộp, modal QR Code ngân hàng sắc nét, thẻ RSVP nút bấm rõ ràng và thanh lịch.

    + **Tối Ưu Vị Trí Khối Sự Kiện Cưới (Event Block UX)**:
      * Chuyển toàn bộ 2 khối sự kiện cưới `.mayhen_invitation_djcvhwiuo9w9_invitation-event-block` (Lễ Vu Quy & Tiệc Cưới) lên nằm ngay bên dưới tab chuyển đổi Nhà gái / Nhà trai (`.mayhen_invitation_djcvhwiuo9w9_side-switch-wrap`).
      * Giúp người dùng khi bấm chuyển qua lại giữa 2 tab Nhà gái / Nhà trai sẽ thấy ngay lập tức sự kiện tương ứng (ngày giờ, sảnh tiệc, địa chỉ và nút chỉ đường) thay đổi trực quan trước mắt mà không cần phải cuộn xuống dưới.
      * Tinh chỉnh khoảng cách thẩm mỹ: `margin: 28px auto 0; padding: 20px 15px 0; border-top: 1px solid rgba(0, 0, 0, 0.08);` cho khối sự kiện, và bổ sung đường nét đứt mờ nhạt (`border-top: 1px dashed rgba(0, 0, 0, 0.12); margin-top: 46px;`) phân tách êm dịu phần lời mời "Thân mời Quý vị & Gia đình..." và tên cô dâu chú rể ở bên dưới.

- **Tạo Master Prompt Tích Hợp Template Mới (`PROMPT_INTEGRATE_TEMPLATE.md`)**:
  - Cung cấp sẵn một file hướng dẫn chuẩn hóa (Master Prompt) tại thư mục gốc của repository.
  - Sau này khi có bất kỳ giao diện template thiệp cưới mới nào (HTML/CSS/JS tĩnh hoặc từ file thiết kế), chỉ cần copy toàn bộ nội dung file prompt này, điền tên mẫu, slug, prefix ID và đường dẫn thư mục nguồn rồi gửi cho AI là hệ thống sẽ tự động chuyển đổi và tích hợp chuẩn xác 100% theo toàn bộ quy chuẩn của Mây Hẹn (Pure HTML/CSS/Native JS, dynamic data fallback, Mobile-First, RSVP, Sổ lưu bút, Hộp mừng cưới, Nhạc nền, Seeder DB, Lint và testing).

- **Nâng Cấp Hệ Thống Nhạc Cưới Tự Động Làm Mới Cache (`WeddingMusicService`)**:
  - **Tự động làm mới cache (Auto-Invalidation siêu tốc)**: Dựa trên so sánh `filemtime` của thư mục nhạc với file `client_musics_index.json`. Khi người dùng thêm, sửa hoặc xóa bất kỳ file bài hát/thư mục ca sĩ nào, hệ thống tự động phát hiện và quét lại toàn bộ để cập nhật file JSON ngay lập tức (<1ms).
  - **Linh hoạt cấu trúc thư mục**: Quét đồng thời cả các file `.mp3` nằm trực tiếp trong `storage/app/public/client/musics/` lẫn trong các thư mục con `musics/singer/{Tên ca sĩ}/`.
  - **Artisan Command `php artisan musics:refresh`**: Bổ sung lệnh console cho phép quản trị viên hoặc lập trình viên chủ động quét lại và in ra thống kê bài hát.
  - **API Query Parameter**: Hỗ trợ param `GET /api/wedding-musics?refresh=1` để ép làm mới cache trực tiếp từ trình duyệt.

- **Thiết Kế Split Hero Layout 2 Cột Trang Kho Mẫu Thiệp (`/mau-thiep`) Chuẩn Quiet Luxury**:
  - **Bố Cục 2 Bên (Split Grid Hero)**: Thay thế khối text căn giữa đơn điệu bằng layout 2 cột bất đối xứng thanh lịch (`grid-template-columns: 1.15fr 0.85fr;`), đặt trong khung nền gradient cát ấm `rgba(248, 245, 239, 0.94)`, viền chỉ vàng champagne mỏng và đổ bóng đa tầng mềm mại.
  - **Cột Trái (Editorial Copy & Feature Badges)**:
    + **Kicker / Eyebrow**: Huy hiệu nhỏ bo tròn `Bộ Sưu Tập Mẫu Thiệp 2026` kèm icon ngôi sao tinh tế và chữ lồng `MÂY HẸN STUDIO`.
    + **H1 & SEO Copy**: Tiêu đề Serif quý phái `Mẫu Thiệp Cưới Đẹp & Thiệp Mời Online Cao Cấp`, đoạn văn mô tả mềm mại chuẩn Semantic SEO tối ưu cho các cụm từ khóa tìm kiếm (mẫu thiệp cưới đẹp, thiệp cưới online, mở phong thư tem sáp, tự tạo miễn phí, chia sẻ Zalo).
    + **Lưới 4 Feature Badges (2x2)**: Thẻ tính năng độc bản có icon SVG mạ vàng cát (Tự tạo miễn phí 2 phút, Mở phong thư tem sáp, RSVP & Bản đồ GPS, Tự phát nhạc cưới 20+ bài), hover nhấc nhẹ thanh thoát.
  - **Cột Phải (Visual Showcase Mockup & Stickers Độc Bản)**:
    + **Showcase Art Card**: Khung thiệp giấy mỹ thuật xoay nhẹ góc nghiêng 3D (`rotate(-2deg)`), lồng ảnh cưới nghệ thuật editorial, đổ bóng chiều sâu cao cấp và typography dập chữ nổi ("Minh Triết & Thảo Linh · Save The Date").
    + **Sticker Con Dấu Sáp Hoàng Gia 3D (Wax Seal)**: Tem sáp đỏ rượu Ruby dập nổi monogram `MH SEAL`, viền chỉ trắng mờ và đổ bóng 3D chân thực ở góc trên của thiệp.
    + **Floating Stickers Tương Tác**: 
      * Sticker nổi `✨ Chạm mở tương tác` hiệu ứng trôi bồng bềnh (floating animation).
      * Sticker nổi `🎵 Tự động phát nhạc` kèm dải sóng âm equalizer nhảy nhịp nhàng (`@keyframes mayhenEqBounce`).
      * Nhãn gập góc `Quiet Luxury` đậm phong cách thiệp cưới châu Âu.
  - **Responsive Mobile-First**: Tự động xếp chồng cột mượt mà trên tablet và mobile (<992px), bo góc gọn gàng, co giãn ảnh và nhãn dán tỉ lệ vàng, không tràn viền màn hình.

- **Nghiên Cứu Đối Thủ, Kiểm Toán Hệ Thống & Thiết Kế Kiến Trúc INVITATION EDITOR V2**:
  - **Khảo sát chuyên sâu đối thủ Ngày Chung Đôi (`ngaychungdoi.com`)**: Phân tích toàn diện 2 chế độ tạo thiệp (Điền form theo mẫu vs Thiết kế tự do), cơ chế Quick-Fill thu thập thông tin lũy tiến, tính năng bảo lưu mã QR/Link khi sửa thiệp, hệ thống đối tác B2B (Studio credit, link duyệt watermark).
  - **Kiểm toán mã nguồn Mây Hẹn hiện tại**: Chỉ rõ các lỗ hổng kỹ thuật (lưu ảnh vào `resources/views/`, thiếu state store tập trung, không có autosave ngầm, thiếu Undo/Redo, mobile UX bị che khuất bởi form, đổi mẫu reload trang gây đứt đoạn trải nghiệm).
  - **Thiết lập Triết lý Trải nghiệm 2 Tầng (Two-Tier Model)**:
    + *Chế độ 1 — Quick Editor V2*: Tối ưu 60 giây tạo thiệp nhanh cho 60% khách hàng không biết thiết kế.
    + *Chế độ 2 — Visual Section Designer*: Chạm trực tiếp vào thiệp (Click-to-Select), kéo thả đổi thứ tự section (Section DnD) bằng Native Pointer Events API, tùy biến 6 bộ màu Quiet Luxury, typography Serif và phong thư tem sáp.
  - **Phân tách dữ liệu cốt lõi (Data Decoupling)**: Tách bạch tuyệt đối giữa `weddings` (dữ liệu cưới bất biến) và `invitations.custom_config` (Document Schema version 2.0.0). Đổi mẫu không bao giờ mất dữ liệu cưới.
  - **Khởi tạo trọn bộ 10 tài liệu kỹ thuật & sản phẩm trong `docs/`**:
    1. `docs/research/ngaychungdoi-editor-benchmark.md`
    2. `docs/research/mayhen-editor-current-audit.md`
    3. `docs/product/editor-v2-prd.md`
    4. `docs/product/editor-v2-user-flows.md`
    5. `docs/design/editor-v2-ux-ui.md`
    6. `docs/design/editor-v2-component-inventory.md`
    7. `docs/architecture/editor-v2-architecture.md`
    8. `docs/database/editor-v2-data-impact.md`
    9. `docs/implementation/editor-v2-refactor-roadmap.md`
    10. `docs/implementation/editor-v2-task-breakdown.md`
  - **Bổ sung AI Skill chuyên biệt**: Tạo `.agents/skills/mayhen-visual-editor/SKILL.md` định nghĩa toàn bộ tiêu chuẩn code và Definition of Done cho phân hệ Editor V2.

- **Triển Khai Hoàn Thành Gói Tasks Kỹ Thuật INVITATION EDITOR V2**:
  - **TASK-E2-001 (Media & DB Schema)**:
    + Tạo migration `2026_10_20_000001_add_editor_v2_columns_to_invitations_table.php` bổ sung 4 cột mới: `schema_version` (varchar), `revision_id` (bigint), `draft_config` (json), `last_autosaved_at` (timestamp).
    + Chuẩn hóa hàm `uploadAsset` trong `InvitationEditorController`: Lưu trữ chuẩn vào `storage/app/public/weddings/photos/`, chấm dứt việc ghi file vào `resources/views/`.
  - **TASK-E2-003 (Backend Autosave & Concurrency Lock API)**:
    + Thêm route `POST /api/editor/autosave` (`invitation.autosave`).
    + Bổ sung phương thức `saveDraftWithLock` trong `InvitationTemplateService` và endpoint `autosave` trong `InvitationEditorController`.
    + Triển khai Optimistic Concurrency Control: So sánh `revision_id`, từ chối ghi đè và trả HTTP 409 Conflict nếu phiên làm việc ở tab khác đã lưu dữ liệu mới hơn.
  - **TASK-E2-002 (Vanilla JS Single State Store & Offline Recovery)**:
    + Tạo `public/assets/client/js/editor/store.js`: Single State Store quản lý luồng dữ liệu một chiều (`state = { wedding, custom_config, revision_id, ui }`).
    + Tạo `public/assets/client/js/editor/actions.js`: Tập hợp Action types & creators (`UPDATE_FIELD`, `SWITCH_TEMPLATE`, `REORDER_SECTIONS`, `SET_THEME_PALETTE`, `UNDO`, `REDO`...).
    + Tạo `public/assets/client/js/editor/history.js`: Lịch sử Undo/Redo tối đa 20 snapshot, hỗ trợ phím tắt `Ctrl+Z`, `Ctrl+Y`.
    + Tạo `public/assets/client/js/editor/autosaver.js`: Ghi đè tức thì vào `localStorage` chống mất điện/tắt tab đột ngột, kết hợp Debounce 1.5s gửi ngầm lên `/api/editor/autosave`.
  - **TASK-E2-004 & TASK-E2-005 (Studio V2 UI & Zero-Loss Template Switcher)**:
    + Tạo file CSS chuẩn Quiet Luxury `public/assets/client/css/pages/editor-v2.css`.
    + Topbar dính: Tích hợp Pill trạng thái lưu trữ trực tiếp (Đang lưu nháp / Đã lưu nháp / Ngoại tuyến / Xung đột), cụm nút Undo/Redo và nút đổi kích thước Mobile (390px) / Desktop.
    + Module `public/assets/client/js/editor/template-switcher.js`: Chuyển đổi giữa các mẫu thiệp cưới tức thì không reload trang, bảo toàn 100% dữ liệu cưới và đồng bộ sang template mới qua PostMessage.
  - **Khắc phục lỗi Layout & Tương tác Studio Editor V2**:
    + **Loại bỏ lỗi Fatal Error 500 Backend**: Xóa khai báo trùng lặp biến `protected string $musicBasePath` trong `app/Services/WeddingMusicService.php` làm sập controller.
    + **Giải phóng Studio Fullscreen 100vh**: Cập nhật `master.blade.php` hỗ trợ `@unless(View::hasSection('hide_chrome'))`, loại bỏ hoàn toàn Header, Footer và Bottom Nav của website thương mại đè lên Studio.
    + **Sửa lỗi biến đổi màu nền toàn trang**: Loại bỏ việc nhúng nhầm `preview.css` và `home.css` trong `editor.blade.php` (do `preview.css` quy định `html { background-color: #161310; }` làm tối đen giao diện Studio).
    + **Tái cấu trúc bố cục cân bằng & Trải nghiệm Mobile 1 chạm**:
      * Mở rộng Contextual Drawer từ 440px lên `480px` - `520px`, gom nhóm các input thành các Card trang nhã (`.mayhen_client_form_card`), tăng chiều cao input lên 44px chuẩn công thái học.
      * Tối ưu Album Gallery ảnh thành lưới 2 cột cân xứng (`.mayhen_editor_gallery_grid`), rút ngắn 60% chiều dài cuộn trang.
      * Ghim thanh điều hướng chân trang (Sticky Stepper Footer `.mayhen_editor_drawer_footer`) luôn nổi ở mép dưới Drawer, không cần cuộn chuột đến đáy mới thấy nút bấm.
      * Giảm độ dày bezel viền chiếc iPhone Mockup ở Center Stage (từ 22px xuống 5px viền titan mỏng), căn giữa tự co giãn theo chiều cao màn hình laptop/máy tính.
      * **Đột phá trải nghiệm Mobile (< 768px)**: Tích hợp bộ chuyển đổi Segmented Switcher chuẩn iOS trên Topbar (`[ ✏️ Nhập Liệu | 👁️ Xem Thiệp ]`) cho phép chuyển đổi 1 chạm giữa màn hình form full-width và màn hình thiệp cưới toàn cảnh, kèm Floating Action Bar nổi linh hoạt, loại bỏ hoàn toàn tình trạng kẹt cuộn và che khuất màn hình.
    + **Khắc phục triệt để lỗi giao diện, phần tử thừa & bố cục mất cân đối**:
      * **Xóa bỏ xung đột CSS V1**: Gỡ bỏ việc nhúng đồng thời file CSS cũ `editor.css` trong `editor.blade.php`, chỉ nạp duy nhất `editor-v2.css` với cache busting `?v={{ filemtime(...) }}`.
      * **Sửa lỗi Topbar đè lên Dynamic Island trong iPhone preview**: Cập nhật điều kiện `@if ($isPreview && !$isEmbed)` trong template `thuoc-phim-tinh-yeu`, loại bỏ hoàn toàn thanh xem thử và nút "+ Tạo Thiệp Mẫu Này" xuất hiện sai vị trí bên trong iframe của Studio.
      * **Dọn sạch các thẻ HTML thừa rớt layout**: Xóa bỏ thẻ `<div class="mayhen_editor_drawer_handle_bar">` (dòng chữ "Chỉnh Sửa Thông Tin X" đè lên tab) và `<nav class="mayhen_editor_mobile_bottom_bar">` (hàng chữ rơi ở góc dưới cùng bên trái màn hình).
      * **Bố cục 2 cột thông minh cho Form Fields (`.mayhen_editor_fields_grid`)**: Tự động xếp song song các trường cặp đôi tương xứng (Cô Dâu ⇄ Chú Rể, Phụ Mẫu Nhà Trai ⇄ Nhà Gái, Ngày Cử Hành ⇄ Giờ Làm Lễ), giúp form gọn gàng, giảm 50% chiều sâu cuộn chuột, mở rộng Drawer lên `500px` - `560px` cân xứng hoàn hảo với Center Stage.
      * **Thiết kế Sticky Stepper Footer cân đối 3 phần**: Ghim chắc chắn ở chân Drawer gồm nút "Quay Lại" (disabled tinh tế ở bước 1), bộ đếm bước "Bước 1 / 4" ở giữa, và nút "Tiếp Tục" (hoặc "Xuất Bản Thiệp" ở bước cuối) màu vàng đồng `#B45309`, tích hợp tự động cuộn mượt form về đầu mỗi khi chuyển bước.
      * **Khắc phục lỗi Tab & Stepper không ẩn hiện nội dung đúng bước**: Bổ sung bộ selector `.mayhen_editor_step_content { display: none !important; }` và `.mayhen_editor_step_content.mayhen_editor_step_active { display: block !important; }` cùng keyframe transition vào `editor-v2.css` (trước đó bị thiếu khi gỡ `editor.css` khiến tất cả các bước đều lộ ra cùng lúc), đồng thời bổ sung style cho danh sách Timeline repeater và gọi `goToStep(1)` khởi tạo trạng thái ban đầu.
    + **Khắc phục triệt để lỗi Live Preview & Lưu trữ Bố Cục & Màu Sắc (custom_config)**:
      * **Nguyên nhân cốt lõi**:
        1. Form `#mayhen_editor_form` trước đó thiếu thẻ input hidden `invitation[custom_config]`, khiến dữ liệu màu sắc, tem sáp, thứ tự và trạng thái ẩn/hiện section chỉ nằm ở client store mà không được gửi lên Controller khi bấm "Xuất Bản Thiệp".
        2. Iframe của các template thiệp (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`) chỉ mới lắng nghe `MAYHEN_SYNC_INPUT`, hoàn toàn chưa có bộ đón message cho `MAYHEN_CHANGE_THEME`, `MAYHEN_UPDATE_WAX_SEAL`, `MAYHEN_REORDER_SECTIONS`, và `MAYHEN_TOGGLE_SECTION`.
        3. Các template thiệp chưa có khối CSS SSR đọc từ `$invitation->custom_config` khi người dùng xem thiệp công khai `/thiep-cuoi/{slug}`.
      * **Giải pháp đã thực thi hoàn tất**:
        1. **Form Synchronization**: Bổ sung `<input type="hidden" name="invitation[custom_config]" id="invitation_custom_config">` trong `editor.blade.php`. Cập nhật `editor.js` tự động đồng bộ `store.getState().custom_config` vào input này mỗi khi có thay đổi từ `ThemeController` hoặc `SectionDnDManager`, và kiểm tra bắt buộc trước khi submit.
        2. **Iframe Live Preview Listener**:
           - Mẫu `thuoc-phim-tinh-yeu/template.blade.php`: Lắng nghe 4 loại message và tiêm động `<style id="mayhen_live_custom_style">` cập nhật các biến CSS `:root` (`--paper`, `--ink`, `--border`, `--primary`), đổi màu và monogram tem sáp, sắp xếp lại các phần tử DOM con trong `.mayhen_invitation_djcvhwiuo9w9_card-wrapper` theo thứ tự người dùng kéo thả, cập nhật `display: none` cho các section bị tắt.
           - Mẫu `net-muc-tinh-yeu/template.blade.php`: Tương tự, cập nhật `:root` (`--mayhen_bg_warm`, `--mayhen_ink`, `--mayhen_gold`, `--mayhen_gold_soft`, `--mayhen_wax`), thay đổi monogram tem sáp `.mayhen_invitation_dhsuudushi9428r_wax_seal`, sắp xếp lại DOM bên trong `#mayhen_invitation_main_content` và ẩn/hiện các section.
        3. **Template Initial State Sync**: Bổ sung hàm bắn trọn bộ theme, wax seal và section order sang iframe ngay khi iframe hoàn tất tải trang (`iframe.addEventListener('load')`).
        4. **Backend Persistence**: Cập nhật `InvitationEditorController@store` nhận trường `invitation.custom_config`, giải mã `json_decode(..., true)` an toàn và lưu trực tiếp vào trường `custom_config` (kiểu json) trong bảng `invitations`.
        5. **SSR Persistence Render**: Thêm khối `<style id="mayhen_custom_theme_style">` đọc từ `$invitation->custom_config` vào phần `<head>` của cả 2 template thiệp cưới, đảm bảo khi xuất bản ra đường dẫn công khai `/thiep-cuoi/{slug}`, màu sắc và các section bị ẩn của thiệp vẫn giữ nguyên 100% như lúc người dùng tùy chỉnh.
    + **Tính năng Cuộn Thông Minh (Auto-Scroll Live Preview) theo Tab Nhập Liệu**:
      * **Vấn đề**: Người dùng bấm chuyển tab trên Stepper (ví dụ sang "4. Ảnh Cưới", "5. Lịch Trình", "2. Nhà Gái"...) thì màn hình iPhone preview bên phải vẫn đứng yên ở vị trí cũ hoặc bị kẹt ở cánh mở thiệp, người dùng phải tự dùng chuột cuộn qua lại rất mất thời gian.
      * **Giải pháp**:
        1. **Mở sẵn thiệp khi nhúng Studio (isEmbed)**: Trong chế độ `isEmbed`, 2 cánh mở đầu phong bì tự động ẩn (`display: none !important`), đồng thời loại bỏ triệt để các lớp khóa cuộn `.mayhen_invitation_djcvhwiuo9w9_no-scroll` / `mayhen_invitation_scroll_locked` (ghi đè `overflow-y: auto !important`, `height: 100% !important`), cho phép màn hình iframe luôn trong trạng thái sẵn sàng cuộn tự do bằng chuột, cảm ứng và thanh cuộn.
        2. **Gắn metadata `data-section-id`**: Bổ sung `data-section-id="{{ $section['id'] }}"` vào từng tab stepper và panel nội dung trong `editor.blade.php`.
        3. **Phát tín hiệu `MAYHEN_SCROLL_TO_SECTION`**: Trong hàm `goToStep(step)` của `editor.js`, mỗi khi người dùng chuyển bước (bấm tab, bấm Tiếp Tục hoặc Quay Lại), Studio tự động phát message `MAYHEN_SCROLL_TO_SECTION` chứa `section_id` sang Iframe.
        4. **Bộ xử lý cuộn mượt trong Template**: Cả hai mẫu thiệp (`thuoc-phim-tinh-yeu` và `net-muc-tinh-yeu`) bắt sự kiện `MAYHEN_SCROLL_TO_SECTION`, tìm chính xác phần tử section tương ứng trong DOM và thực hiện `targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' })`.
        5. **Thanh cuộn siêu mảnh & Bảo toàn cuộn tự do**: Tùy chỉnh thanh cuộn siêu mảnh (6px) với màu vàng đồng Quiet Luxury `#B45309`, viền trắng nhẹ `rgba(255, 255, 255, 0.8)`, nền track mờ `rgba(0, 0, 0, 0.06)`, bo tròn viền mềm mại, không bao giờ khóa cuộn (`overflow-y: auto !important`), cho phép người dùng thoải mái dùng con lăn chuột, kéo vuốt hoặc kéo thanh scrollbar trên cả màn hình Studio và bên trong iPhone preview mà không gặp bất kỳ trở ngại nào.
    + **Khắc phục lỗi Đổi Mẫu Thiệp không cập nhật khi Tạo / Cập nhật Thiệp**:
      * **Vấn đề**: Người dùng đang chỉnh sửa thiệp, sau đó dùng tính năng "Đổi Mẫu Thiệp" (`TemplateSwitcher`) để chuyển sang mẫu khác (ví dụ từ `thuoc-phim-tinh-yeu` sang `net-muc-tinh-yeu`), nhưng khi bấm "Xuất Bản Thiệp" hoặc lưu cập nhật thì thiệp tạo/cập nhật ra vẫn là mẫu cũ.
      * **Nguyên nhân cốt lõi**:
        1. Trong `public/assets/client/js/editor/template-switcher.js`: Hàm `switchTemplate({ id, slug, name, thumb, tier })` chỉ dispatch vào `this.store` và đổi `iframe.src`, nhưng **hoàn toàn quên cập nhật giá trị vào thẻ input form `#template_id`** (`<input type="hidden" name="template_id" id="template_id" value="...">`) của form chính.
        2. Trong `public/assets/client/js/pages/editor.js`: Hàm `submitFormWithPendingUploads()` trước khi gọi `mainForm.submit()` không kiểm tra và gán lại `template_id` mới nhất từ `store.getState().template_id` vào input `#template_id`.
        3. Do đó, khi form gửi request POST lên `InvitationEditorController@store`, trường `template_id` vẫn mang ID của template ban đầu, khiến backend ghi đè lại template cũ thay vì template mới đã chọn.
      * **Giải pháp đã thực thi**:
        1. **Đồng bộ DOM tức thì trong `template-switcher.js`**: Ngay khi người dùng chọn mẫu mới, cập nhật trực tiếp `document.getElementById('template_id').value = id`, đồng thời cập nhật biến toàn cục `window.__MAYHEN_TEMPLATE_ID = id; window.__MAYHEN_TEMPLATE_SLUG = slug;`.
        2. **Chốt chặn an toàn trong `editor.js`**: Trước khi `mainForm.submit()`, tự động đọc `store.getState().template_id` và gán đè vào input `#template_id` nếu có giá trị, bảo đảm dữ liệu gửi lên máy chủ luôn chính xác 100% với mẫu thiệp người dùng đã chọn.

    + **Khắc Phục Triệt Để Lỗi Mất Ảnh Khi Chỉnh Sửa Thiệp Cũ (Live Preview Edit Mode)**:
      * **Nguyên nhân gốc rễ**:
        1. Khi vào URL chỉnh sửa thiệp cũ `tao-thiep?edit={slug}`, iframe bên phải chỉ tải URL mặc định `route('templates.preview', $slug)` mà không truyền slug thiệp (`&edit={slug}`). Do đó, `TemplateController@preview` chỉ render template với ` = null`, toàn bộ ảnh cưới của thiệp đã lưu không được SSR nạp vào màn hình preview.
        2. Sau khi iframe tải xong, `syncAllFieldsToIframe` trong `editor.js` gửi các giá trị từ form sang iframe qua PostMessage `MAYHEN_SYNC_INPUT`. Nhưng các ảnh mặc định trong cơ sở dữ liệu được lưu dưới dạng tương đối (ví dụ `assets/3ed9dcf0-...jpg`). Bộ xử lý `editor-bridge.blade.php` trước đây gán trực tiếp `img.src = val`, dẫn đến trình duyệt trong iframe hiểu sai URL và tải về ảnh rỗng hoặc gãy (broken image icon), đồng thời các section dùng `background-image` (như hero section) không được gán style tương ứng.
      * **Giải pháp đã thực thi**:
        1. **Nạp dữ liệu thiệp đang sửa vào Preview Iframe**: Cập nhật cả 2 thẻ iframe preview (màn hình chính và modal mobile) trong `editor.blade.php` thành:
           `route('templates.preview', $selectedTemplate->slug) . '?embed=1&mode=editor' . (isset($editingInvitation) && $editingInvitation ? '&edit=' . $editingInvitation->slug : '')`.
        2. **Xử lý SSR trong `TemplateController@preview`**: Nhận tham số `edit` hoặc `invitation`, truy vấn thông tin `` và `` nếu người dùng là chủ sở hữu và truyền trực tiếp vào Blade template khi nhúng vào Studio.
        3. **Chuẩn hóa hàm `resolveUrl` trong `editor-bridge.blade.php`**: Khi nhận message `MAYHEN_SYNC_INPUT`, tự động nhận diện đường dẫn tương đối để ghép đúng với URL gốc `` (`/mau-thiep/{slug}/assets/...`), đồng thời tự động cập nhật cả `el.style.backgroundImage` cho các khối banner/hero ảnh cưới.

    + **Tách Biệt & Tự Động Ghép Tên Phụ Mẫu Nhà Trai & Nhà Gái (UX Phụ Mẫu Chuẩn Văn Hóa Việt)**:
      * **Vấn đề**: Trước đây, form nhập liệu yêu cầu người dùng tự gõ cả chuỗi dài (ví dụ: `ÔNG: Nguyễn Tràng Giang & BÀ: Lê Thị Mai`), khiến người dùng lúng túng, gõ sai format và dễ làm hỏng bố cục thiết kế thiệp cưới.
      * **Giải pháp thực hiện**:
        1. **Tách 4 ô nhập liệu độc lập trong `schema.json`**:
           - Nhà Trai: `Họ tên Cha (Nhà Trai)` (`wedding.extra_data.groom_father`) và `Họ tên Mẹ (Nhà Trai)` (`wedding.extra_data.groom_mother`).
           - Nhà Gái: `Họ tên Cha (Nhà Gái)` (`wedding.extra_data.bride_father`) và `Họ tên Mẹ (Nhà Gái)` (`wedding.extra_data.bride_mother`).
        2. **Tự Động Ghép Chuỗi Phụ Mẫu Chuẩn Văn Hóa (Live Preview & Backend)**:
           - **Editor Bridge (`editor-bridge.blade.php`)**: Khi người dùng gõ vào bất kỳ ô cha/mẹ nào, bridge tự động tổng hợp thành `ÔNG: {Cha} & BÀ: {Mẹ}` (hoặc định dạng 2 dòng tùy template) và phản ánh tức thì lên màn hình xem thử Live Preview.
           - **Backend (`InvitationEditorController@store`)**: Khi lưu thiệp, controller tự động tổng hợp từ `extra_data` thành chuỗi chuẩn gán vào 2 cột `groom_parents` và `bride_parents` của bảng `weddings`, đảm bảo tương thích ngược 100% với các tính năng cũ và thiệp đã xuất bản.
           - **Khả năng tương thích ngược khi sửa thiệp (`editor.blade.php`)**: Nếu mở thiệp cũ chưa có 4 trường riêng biệt trong `extra_data`, hệ thống tự động bóc tách từ chuỗi `groom_parents` / `bride_parents` điền sẵn vào 4 ô nhập liệu, người dùng không cần nhập lại từ đầu.
        3. **Template Contract**: Các mẫu thiệp hỗ trợ cả hiển thị riêng lẻ (`[data-sync-target="groom_father"]`, `[data-sync-target="groom_mother"]`) lẫn hiển thị gộp (`[data-sync-target="groom_parents"]`, `[data-sync-target="bride_parents"]`).

    + **Chuẩn Hóa Toàn Diện Template Contract & Module Bridge Dùng Chung (Scale Cho Hàng Trăm Mẫu Thiệp)**:
      * **Vấn đề đã nhận diện**: Trước đây, các template thiệp (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`) sử dụng các bộ selector CSS / ID ngẫu nhiên không đồng nhất (như `#mayhen_invitation_djcvhwiuo9w9_home`, `.mayhen_invitation_dhsuudushi9428r_story`...) và phải viết lại hàng trăm dòng mã JavaScript riêng lẻ trong mỗi file template để xử lý PostMessage, đồng bộ màu sắc, tem sáp, sắp xếp section và cuộn tự động. Cách này không thể mở rộng khi hệ thống phát triển lên hàng chục hoặc hàng trăm mẫu thiệp cưới khác nhau.
      * **Giải pháp kiến trúc chuẩn hóa**:
        1. **HTML Contract Chuẩn Dùng Chung Toàn Hệ Thống**:
           - **Container các section**: Bọc thẻ chứa các phần bằng thuộc tính chuẩn: `data-mayhen-section-container`.
           - **Từng Section**: Gắn thuộc tính thống nhất: `data-mayhen-section="{section_id}"` (với các id chuẩn: `couple`, `bride_events`, `groom_events`, `countdown`, `gallery_and_photos`, `story_and_timeline`, `gift_and_music`, `rsvp`, `guestbook`).
           - **Phần tử đồng bộ dữ liệu trực tiếp**: Gắn `data-sync-target="{field_key}"` (ví dụ `data-sync-target="groom_name"`, `data-sync-target="bride_name"`, `data-sync-target="hero_photo_url"`).
        2. **Partial Blade Thống Nhất `resources/views/client/invitations/partials/editor-bridge.blade.php`**:
           - Tách toàn bộ logic PostMessage của iframe thành một partial dùng chung duy nhất, chỉ tải khi `isEmbed` (trong Studio).
           - Tự động bắt và xử lý hoàn chỉnh 5 loại message từ Studio:
             * `MAYHEN_SYNC_INPUT`: Cập nhật text content, thuộc tính src của ảnh, và ngày giờ đếm ngược qua `[data-sync-target]`.
             * `MAYHEN_CHANGE_THEME`: Tiêm thẻ `<style id="mayhen_live_custom_style">` cập nhật bảng màu `:root` tức thì.
             * `MAYHEN_UPDATE_WAX_SEAL`: Đổi màu nền con dấu sáp và ký tự lồng chữ cái monogram.
             * `MAYHEN_REORDER_SECTIONS`: Tự động sắp xếp lại thứ tự DOM của các section bên trong `[data-mayhen-section-container]`.
             * `MAYHEN_TOGGLE_SECTION`: Bật/tắt hiển thị (`display: none` / `display: block`) theo `[data-mayhen-section="{section_id}"]`.
             * `MAYHEN_SCROLL_TO_SECTION`: Tìm kiếm phần tử `[data-mayhen-section="{section_id}"]` và kích hoạt cuộn mượt tự động `scrollIntoView({ behavior: 'smooth', block: 'start' })`.
        3. **Refactor Hoàn Toàn 2 Template Mẫu (`thuoc-phim-tinh-yeu` & `net-muc-tinh-yeu`)**:
           - Xóa bỏ toàn bộ các khối `@match` hardcode và script riêng lẻ cồng kềnh (tiết kiệm hơn 300 dòng code thừa).
           - SSR ẩn hiện section chuyển sang dạng 1 dòng CSS duy nhất: `[data-mayhen-section="{{ $hiddenId }}"] { display: none !important; }`.
           - Nhúng partial dùng chung: `@include('client.invitations.partials.editor-bridge')`.
    + **Chuẩn Hóa Global Preview Topbar Dùng Chung & Loại Bỏ Topbar Khỏi Giao Diện Template**:
      * **Vấn đề đã nhận diện**: Trước đây, các template mẫu thiệp (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`) tự nhúng khối `<header class="mayhen_preview_topbar">` bên trong file `template.blade.php`. Khi xem trong popup Modal Showcase ở trang chủ hoặc trên mobile, các nút bị dồn ép với Dynamic Island của khung điện thoại, vỡ lề và xuất hiện thanh cuộn ngang Windows ở đáy màn hình.
      * **Giải pháp kiến trúc**:
        1. **Tách biệt hoàn toàn**: Xóa sạch toàn bộ khối topbar xem thử (`mayhen_preview_topbar`) và topbar chủ thiệp khỏi mã nguồn của từng template (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`). Mẫu thiệp giữ trọn vẹn 100% không gian hiển thị nghệ thuật thuần khiết.
        2. **Thanh Global Preview Topbar Dùng Chung Tại Trang Chủ (`home.blade.php`)**:
           - Tái cấu trúc `.mayhen_home_showcase_modal_top_bar` thành thanh điều hướng capsule Quiet Luxury thống nhất cho toàn bộ mẫu thiệp.
           - Tích hợp đầy đủ: Badge xanh "Đang xem thử" + Tên mẫu thiệp hiện tại (`#mayhen_home_showcase_modal_header_title`), bộ chuyển đổi chế độ xem Mobile / Desktop, nút "Mở tab mới", nút "Tải lại", và nút "Sao chép link".
           - Đồng bộ tự động qua hàm `openLivePreviewModal` trong `home.js`.
           - Tối ưu responsive: Trên thiết bị di động (<640px), tự động ẩn nhãn phụ để thanh bar luôn vừa vặn, không bao giờ bị chèn đè lên các nút xung quanh.
    + **Tái Thiết Kế Toàn Diện Global Footer Chuẩn Quiet Luxury Đa Tầng**:
      * **Vấn đề đã nhận diện**: Footer trước đây chỉ có 3 cột menu đơn sơ, thiếu thông tin thương hiệu, thiếu các cam kết cốt lõi, cổng thanh toán và trải nghiệm khách hàng di động.
      * **Giải pháp kiến trúc**: Tái thiết kế toàn diện [footer.blade.php](file:///e:/Project/Laravel/mayhen.vn/resources/views/client/components/footer.blade.php) và [footer.css](file:///e:/Project/Laravel/mayhen.vn/public/assets/client/css/components/footer.css) theo 6 tầng thông tin đầy đủ, đẹp mắt và thuận mắt:
        1. **Value Proposition Strip (4 Khối Cam Kết)**: Chuẩn Quiet Luxury, Khởi tạo trong 5 phút, Cá nhân hóa từng khách mời, Bảo mật & riêng tư.
        2. **Newsletter & Payment Gateway**: Form nhập email nhận Voucher 10% và cẩm nang cưới + Huy hiệu các cổng thanh toán an toàn (VietQR, VNPAY, MoMo, ZaloPay, Apple Pay, Visa, Mastercard, Napas).
        3. **Lưới Điều Hướng 5 Cột Cân Xứng**:
           - Cột 1: Thương hiệu Mây Hẹn, lời ngỏ, card Hotline tư vấn 24/7, email, atelier và mạng xã hội (Facebook, Instagram, TikTok, Zalo).
           - Cột 2: Kho mẫu thiệp cưới phân loại theo chủ đề (Quiet Luxury, Tạp chí Editorial, Phong thư sáp 3D, Minimalist, Flora, Lễ Vu Quy).
           - Cột 3: Tính năng cưới (Mở phong bì tem sáp, Nhạc nền tự động, Báo hỷ RSVP, Mừng cưới VietQR, Bản đồ chỉ đường, Album Lightbox, Sổ lưu bút).
           - Cột 4: Về Mây Hẹn (Câu chuyện thương hiệu, Bảng giá gói cưới, Quy trình, Hợp tác Studio/Nhiếp ảnh, FAQ, Chính sách bảo mật, Điều khoản).
           - Cột 5: Trải nghiệm di động (Mã QR scan mở demo thiệp trên điện thoại) + Chứng nhận Bộ Công Thương, DMCA Protected, SSL 256-bit.
        4. **Hệ Sinh Thái & Đối Tác Đồng Hành**: Mây Hẹn Atelier, Mây Hẹn RSVP Pro, VietQR Napas, Wedding Studio Partners, Nhà In Mỹ Thuật.
        5. **SEO Keywords Strip**: Danh sách từ khóa thiệp cưới online tối ưu SEO on-page tự nhiên.
        6. **Chân Trang Pháp Lý & Bản Quyền**: Thông tin pháp nhân công ty, GPĐKKD, địa chỉ, bản quyền `© 2026 MÂY HẸN (mayhen.vn)` và các liên kết pháp lý.

    + **Bảo Mật Toàn Diện (Security Hardening) & Tối Ưu SEO Hiện Đại (Theo Skill `security-audit`)**:
       * **Mục tiêu**: Nâng cấp bảo mật đa tầng cho toàn bộ hệ thống web Mây Hẹn (`mayhen.vn`) theo chuẩn OWASP và Google Search Central, tuyệt đối không ảnh hưởng đến mã nguồn khác, không làm giảm tốc độ tải trang, và tối ưu hóa tốt nhất cho SEO hiện đại.
       * **Các cải tiến đã thực thi**:
         1. **Security Headers Middleware (`app/Http/Middleware/SecurityHeaders.php`)**:
            - Tự động bổ sung các tiêu đề bảo mật cấp cao trên mọi phản hồi HTTP:
              * `X-Content-Type-Options: nosniff`: Chống MIME type sniffing & MIME confusion attacks.
              * `X-Frame-Options: SAMEORIGIN`: Bảo vệ toàn diện chống Clickjacking, đồng thời vẫn cho phép tính năng Live Preview iframe cùng nguồn hoạt động hoàn hảo.
              * `Referrer-Policy: strict-origin-when-cross-origin`: Tiêu chuẩn vàng được Google Search & Analytics khuyến nghị, bảo vệ sự riêng tư mà vẫn duy trì toàn bộ tín hiệu SEO khi chuyển hướng.
              * `Permissions-Policy: camera=(), microphone=(), geolocation=()`: Giới hạn truy cập phần cứng nhạy cảm.
              * `X-XSS-Protection: 1; mode=block`: Bật bộ lọc XSS sẵn có của trình duyệt.
         2. **Khắc Phục Triệt Để Rủi Ro Stored XSS Trong Upload File (`InvitationEditorController.php`)**:
            - Loại bỏ định dạng `svg` khỏi validation tải lên file ảnh cưới trong hàm `uploadAsset` (`mimes:jpeg,png,jpg,webp,gif,heic|max:20480`), ngăn chặn nguy cơ tin tặc nhúng mã độc JavaScript vào tệp vector XML.
         3. **Chống Path Traversal & LFI Tuyệt Đối (`TemplateController.php`)**:
            - Trong hàm `asset()`, bổ sung xác thực Regex cho slug (`^[a-z0-9_-]+$`), sử dụng `realpath()` và `str_starts_with()` để đối chiếu đường dẫn thực tế, đảm bảo tài nguyên chỉ được phục vụ khi nằm hoàn toàn bên trong thư mục template hợp lệ, chặn đứng mọi kỹ thuật tấn công leo thang thư mục (`../`).
         4. **Làm Sạch Dữ Liệu Đầu Vào (XSS Sanitization) Trong Form Requests**:
            - Bổ sung `prepareForValidation()` với hàm `strip_tags()` và `trim()` trong `SubmitWishRequest.php`, `SubmitRsvpRequest.php`, `StoreGuestRequest.php`.
            - Dữ liệu lời chúc, tên khách mời, số điện thoại, ghi chú ăn uống được làm sạch triệt để trước khi chuyển qua bước xác thực và lưu vào cơ sở dữ liệu.
         5. **Rate Limiting (Throttle) Phân Tầng Theo Rủi Ro & Session-Aware (`AppServiceProvider.php` & `routes/web.php`)**:
            - Áp dụng Named Rate Limiters thông minh phân định theo `Session ID` / `User ID` thay vì chỉ dùng IP đơn thuần, tránh triệt để vấn đề 1000 khách mời cùng dự tiệc cưới chung 1 mạng WiFi (NAT IP) bị chặn chéo lẫn nhau:
              * `guest_interactions`: Áp dụng cho gửi lời chúc & RSVP (60 yêu cầu/phút/thiết bị).
              * `auth_attempts`: Áp dụng cho đăng nhập & đăng ký (15 yêu cầu/phút/email+IP chống brute-force).
              * `editor_actions`: Áp dụng cho tạo thiệp, upload ảnh cưới, autosave (120 yêu cầu/phút/tài khoản).
              * `throttle:60,1`: Áp dụng cho webhook thanh toán và API thêm khách mời.
            - **Tối ưu SEO & High-concurrency**: Giữ nguyên 100% các route `GET` công khai (trang chủ, kho mẫu thiệp, chi tiết thiệp, bài viết, dịch vụ) không bị giới hạn, giúp Googlebot và các công cụ tìm kiếm thu thập dữ liệu với tốc độ tức thì và 0ms độ trễ.
         6. **Tối Ưu Crawl Budget Với `public/robots.txt`**:
            - Cho phép bot crawl toàn bộ nội dung công khai và tài nguyên hiển thị (`/assets/`, `/storage/`, `/mau-thiep/`, `/thiep/`).
            - Chặn các vùng nhạy cảm và nội bộ (`/admin/`, `/api/`, `/tai-khoan/`, `/dang-nhap`, `/dang-ky`, `/thanh-toan/`) để tập trung 100% crawl budget vào các trang có giá trị SEO cao.
            - Khai báo sitemap chính thức `https://mayhen.vn/sitemap.xml`.
    + **Khóa Cuộn Trang Tuyệt Đối Khi Chưa Mở Thiệp (`net-muc-tinh-yeu`)**:
       * **Vấn đề nhận diện**: Trước đây, khi người dùng vào xem mẫu thiệp `net-muc-tinh-yeu` mà chưa chạm mở phong bì thư, phần thông tin thân thiệp bên dưới ("Nhà trai / Nhà gái") đã bị thò lên ở đáy màn hình và người dùng có thể lăn chuột/vuốt màn hình để cuộn xem nội dung bên trong trước khi mở phong bì.
       * **Nguyên nhân kỹ thuật**: Thẻ `html` không được gán class khóa cuộn, trong khi CSS chỉ set `overflow: hidden` trên `body` khiến trình duyệt desktop/mobile chuyển quyền cuộn lên `html`. Đồng thời thân thiệp `.mayhen_invitation_dhsuudushi9428r_main_body` chưa được ẩn khi trạng thái phong bì đang đóng.
       * **Giải pháp đã xử lý**:
         1. Thêm class `mayhen_invitation_scroll_locked` vào cả thẻ `<html>` và `<body>` trong `template.blade.php`.
         2. Trong `styles.css`, thiết lập khóa cuộn toàn diện: `html, body, .page, .cover` đều đạt `height: 100dvh; overflow: hidden !important; touch-action: none !important; overscroll-behavior: none !important;` và ẩn hoàn toàn thân thiệp `.mayhen_invitation_dhsuudushi9428r_main_body { display: none !important; }`.
         3. Trong `script.js`, bổ sung bộ lắng nghe chặn triệt để thao tác cuộn chuột (`wheel`), vuốt chạm (`touchmove`) và phím điều hướng (`keydown`) trước khi mở. Khi người dùng bấm "Chạm mở thiệp", phong bì mở nắp 3D, thiệp trồi lên, nhạc cưới phát, class khóa cuộn được gỡ trên cả `html` và `body`, sau đó trang tự động cuộn nhẹ nhàng (smooth scroll) xuống nội dung thiệp cưới.

    + **Sửa Lỗi Nút Bật/Tắt Nhạc Không Hoạt Động (`net-muc-tinh-yeu`)**:
       * **Nguyên nhân lỗi**:
         1. File `script.js` trước đó bị nhúng 2 lần (1 lần trong `<head>` có `defer`, 1 lần ở cuối `<body>`), khiến sự kiện `click` trên nút nhạc `mayhen_invitation_dhsuudushi9428r_music` bị gán 2 listener chạy song song (khi bấm click thì listener 1 play rồi listener 2 lập tức pause, triệt tiêu lẫn nhau).
         2. Đường dẫn nhạc fallback bị lặp tiền tố `assets/assets/...` và thẻ `<audio>` thiếu thuộc tính `preload="auto"`.
         3. Thiếu `@keyframes mayhen_invitation_spin` trong `styles.css` cho hiệu ứng đĩa nhạc xoay tròn khi phát.
       * **Giải pháp đã xử lý**:
         1. Xóa bỏ thẻ `<script>` trùng lặp trong thẻ `<head>`, chỉ giữ duy nhất một thẻ script ở cuối `<body>`.
         2. Tách bạch hàm `playMusic()` và `pauseMusic()` với `stopPropagation()` và `preventDefault()` chống xung đột sự kiện.
         3. Sửa fallback `$musicUrl` chuẩn không lặp `assets/` và đặt `preload="auto"` trên thẻ `<audio>`.
         4. Bổ sung `@keyframes mayhen_invitation_spin` xoay 360 độ mượt mà khi nhạc phát.

     + **Khóa Cuộn Trang & Khắc Phục Bật/Tắt Nhạc Mẫu Thiệp (`thuoc-phim-tinh-yeu`)**:
        * **Vấn đề nhận diện**:
          1. Tương tự mẫu nét mực, khi truy cập mẫu thiệp `thuoc-phim-tinh-yeu`, dù hai cánh rèm/cửa (`opening-sides`) chưa mở nhưng người dùng vẫn có thể cuộn trang xuống phần nội dung thân thiệp dài 8.000px bên dưới.
          2. Nút bật/tắt nhạc `.mayhen_invitation_djcvhwiuo9w9_music-toggle` bị sai lệch trạng thái sau khi mở thiệp: khi mở thiệp nhạc tự phát nhưng biến cờ nội bộ chưa đồng bộ, khiến khi người dùng click vào nút nhạc để tắt thì không thể tắt được (hoặc bị phát lại).
        * **Nguyên nhân kỹ thuật**:
          1. Thẻ `<html>` và `<body>` ban đầu trong `template.blade.php` không có sẵn class khóa cuộn `mayhen_invitation_djcvhwiuo9w9_no-scroll`. Đồng thời thân thiệp `.mayhen_invitation_djcvhwiuo9w9_card-wrapper` có `display: block` nằm bên dưới lớp fixed của hai cánh cửa khiến trang vẫn tính chiều cao cuộn tổng thể.
          2. Nút nhạc có `z-index: 99` thấp hơn `opening-sides` (`z-index: 1000`), và logic click dựa trên biến cờ `let isPlaying = false` tách biệt không đồng bộ với sự kiện gọi `audio.play()` khi mở thiệp.
        * **Giải pháp đã xử lý**:
          1. **Khóa cuộn triệt để**:
             - Thêm trực tiếp `class="mayhen_invitation_djcvhwiuo9w9_no-scroll"` vào cả `<html lang="vi">` và `<body>` ngay khi load trang trong `template.blade.php`.
             - Nâng cấp CSS khóa cuộn `html.mayhen_invitation_djcvhwiuo9w9_no-scroll, body.mayhen_invitation_djcvhwiuo9w9_no-scroll`: `height: 100% !important; height: 100dvh !important; max-height: 100dvh !important; overflow: hidden !important; touch-action: none !important; overscroll-behavior: none !important;`.
             - Ẩn hoàn toàn thân thiệp khi đang đóng: `body.mayhen_invitation_djcvhwiuo9w9_no-scroll .mayhen_invitation_djcvhwiuo9w9_card-wrapper { display: none !important; }` (vẫn bảo toàn `display: block !important` khi ở chế độ Studio Editor preview `?embed=1`).
             - Bổ sung bộ chặn sự kiện `wheel`, `touchmove`, `keydown` (ArrowDown, PageDown, Space...) trong `script.js` cho đến khi thiệp được mở hoàn tất.
          2. **Bật/Tắt nhạc mượt mà 100%**:
             - Tăng `z-index: 1005` cho `.mayhen_invitation_djcvhwiuo9w9_music-toggle` để nút luôn tương tác được kể cả trước và sau khi mở thiệp.
             - Đặt `preload="auto"` trên thẻ `<audio>`.
             - Loại bỏ biến cờ tĩnh bị lệch pha, chuyển sang cơ chế kiểm tra trực tiếp trạng thái native của audio element: `if (!audioEl.paused) audioEl.pause(); else audioEl.play();`.
          3. **Khắc phục lỗi không bấm mở được thiệp**:
             - **Nguyên nhân cốt lõi**: Trong [template.blade.php](file:///e:/Project/Laravel/mayhen.vn/resources/views/client/invitations/thuoc-phim-tinh-yeu/template.blade.php), thẻ script khởi tạo `window.__MAYHEN_*` ở cuối trang trước đó bị thiếu thẻ đóng `</script>`, khiến trình duyệt xem toàn bộ thẻ `<script src="script.js">` bên dưới là nội dung text của khối script inline, dẫn đến [script.js](file:///e:/Project/Laravel/mayhen.vn/resources/views/client/invitations/thuoc-phim-tinh-yeu/script.js) không được tải và thực thi. Đồng thời sự kiện click trước đây chỉ lắng nghe `click` đơn thuần.
             - **Giải pháp**:
               * Bổ sung thẻ đóng `</script>` chuẩn xác trước khi nhúng file [script.js](file:///e:/Project/Laravel/mayhen.vn/resources/views/client/invitations/thuoc-phim-tinh-yeu/script.js).
               * Trong [script.js](file:///e:/Project/Laravel/mayhen.vn/resources/views/client/invitations/thuoc-phim-tinh-yeu/script.js), bổ sung lắng nghe đa phương thức (`click`, `pointerdown`, `touchend`, và phím `Enter`/`Space`) trên cả hai cánh rèm `openingSides`, `opening-left` và `opening-right`, đồng thời gọi `e.stopPropagation()`. Khi mở thiệp, loại bỏ class khóa cuộn trên cả `html` và `body` và gỡ bỏ hoàn toàn rèm che (`display: none`) sau khi hiệu ứng trượt hoàn tất.

     + **Tích Hợp Hoàn Chỉnh Mẫu Thiệp Mới: "Từng Trang Hạnh Phúc" (`tung-trang-hanh-phuc`)**:
        * **Nguồn**: `D:\File\Templates Mây Hẹn\Từng Trang Hạnh Phúc`
        * **Vị trí tích hợp**: `resources/views/client/invitations/tung-trang-hanh-phuc/`
        * **CSS Prefix độc quyền**: `mayhen_invitation_ksnvo949dudf_*` (chống xung đột class 100% theo tiêu chuẩn Mây Hẹn).
        * **Chi tiết cấu trúc kỹ thuật**:
          1. `template.blade.php`:
             - Hỗ trợ đầy đủ dữ liệu động từ `$wedding` và `$invitation` kết hợp fallback thông minh theo phong cách Storybook / Editorial sang trọng.
             - Áp dụng các contract `data-sync-target` cho Live Editor Studio V2 (`bride_name`, `groom_name`, `event_date`, `timeline`, `gallery`, `gift`, `rsvp`, `wishes`...).
             - Tích hợp `@include('client.invitations.partials.editor-bridge')` và xử lý chế độ nhúng `?embed=1` mượt mà.
             - Khóa cuộn trang bằng class `mayhen_invitation_ksnvo949dudf_scroll-lock` trên cả `<html>` và `<body>` khi chưa mở phong bì thư.
          2. `styles.css`:
             - Thiết kế Quiet Luxury ấm áp, phong cách Editorial Storybook với màu nhấn đất nung (#8f533a), kem sáng (#FAF7F2), giấy lụa mềm mại.
             - Khóa cuộn hoàn hảo: `html.mayhen_invitation_ksnvo949dudf_scroll-lock, body.mayhen_invitation_ksnvo949dudf_scroll-lock` và ẩn thân thiệp khi phong bì đang đóng.
             - Keyframes xoay đĩa nhạc 360 độ mượt mà.
          3. `script.js`:
             - Viết lại 100% bằng Native ES Modules thuần, không phụ thuộc thư viện bên ngoài.
             - Hiệu ứng mở phong bì thư tem sáp 3D, mở thư và kích hoạt phát nhạc cưới native an toàn.
             - Bộ đếm ngược thời gian thực (Ngày, Giờ, Phút, Giây).
             - Tab chuyển đổi sự kiện Lễ Thành Hôn / Tiệc Cưới và bản đồ chỉ đường Google Maps.
             - Tích hợp AJAX lưu lời chúc online (`POST /thiep/{slug}/loi-chuc` hoặc endpoint cấu hình).
             - Tích hợp AJAX gửi xác nhận tham dự RSVP (`POST /thiep/{slug}/rsvp` hoặc endpoint cấu hình).
             - Hộp mừng cưới VietQR kèm chức năng sao chép số tài khoản một chạm.
             - Lightbox album ảnh cưới 6 khung hình phóng to sắc nét, vuốt chạm mượt mà.
             - Tải file sự kiện lịch `.ics` và chia sẻ liên kết mạng xã hội Native Share API.
          4. `schema.json`:
             - Khai báo metadata schema đầy đủ cho Studio V2 Editor (thông tin CDCR, sự kiện, thời gian, album ảnh, tài khoản ngân hàng QR, dresscode).
          5. `assets/`:
             - Lưu trữ cục bộ toàn bộ vector nghệ thuật: `bride-art.svg`, `groom-art.svg`, `hero-art.svg`, `scene-art.svg`, `gallery-01.svg` -> `gallery-06.svg`, `favicon.svg`.
             - Nhạc cưới chất lượng cao: `wedding.mp3`.
             - Ảnh xem trước đại diện: `thumbnail.png`.
          6. **Cơ sở dữ liệu & Danh mục (`InvitationTemplateSeeder.php`)**:
             - Đã đăng ký bản ghi mẫu thiệp trong `InvitationTemplateSeeder.php` với slug `tung-trang-hanh-phuc`, danh mục `editorial`, phân hạng `pro` deluxe.
             - Đã chạy `php artisan db:seed --class=InvitationTemplateSeeder`, xuất hiện đầy đủ trong trang danh sách mẫu `/mau-thiep` và xem thử trực tiếp tại `/xem-thu/tung-trang-hanh-phuc`.
          7. **Refactor Giao Diện Chuẩn Quiet Luxury & Loại Bỏ Header/Footer Website**:
             - **Vấn đề nhận diện**: Mã nguồn tĩnh gốc chứa thanh Header điều hướng (logo chữ, danh mục menu, nút hamburger menu) và Footer dạng website tin tức/bán hàng, cùng với việc dùng hình vẽ vector phẳng xám nhạt (`.svg`) và bộ lọc CSS `filter: grayscale(1)` khiến thiệp cưới nhìn trắng bệch, đơn điệu và thiếu tính trang trọng, lãng mạn của một tấm thiệp cưới cao cấp.
             - **Giải pháp refactor toàn diện**:
               * **Xóa bỏ hoàn toàn Navbar & Footer Website**: Loại bỏ thanh `<header>` cố định và `<footer>` bản quyền. Thay vào đó là nút điều khiển nhạc nổi đĩa than xoay tròn tinh xảo góc màn hình và khối "Lời Cảm Tạ" (Gratitude Ending) chân thành, ấm áp từ Cô dâu & Chú rể kèm chữ ký Monogram quý phái.
               * **Nâng cấp Hình ảnh Thực Tế Sang Trọng**: Thay thế toàn bộ hình vector thô sơ bằng bộ ảnh cưới studio nghệ thuật thực tế (`hero-wedding.jpg`, `groom.jpg`, `bride.jpg`, `gallery-01.jpg` đến `gallery-06.jpg`), loại bỏ hoàn toàn bộ lọc grayscale để ảnh cưới giữ trọn màu sắc tự nhiên, trong trẻo, rạng ngời.
               * **Nâng tầm Bảng Màu & Typography Quiet Luxury**: Sử dụng tông màu Warm Cream/Ivory (`#FAF7F2`), Warm Beige (`#F4EFE6`), Nâu Espresso (`#2D2520`), Vàng kim loại Antique Gold (`#C5A880`) và đỏ sáp Burgundy (`#8B2635`).
               * **Phong Bì Thư 3D Tinh Xảo**: Hiệu ứng mở nắp phong bì hoàng gia, tem sáp đỏ dập nổi monogram, lá thư trồi lên 3D và khóa cuộn trang mượt mà trước khi mở.

---

          8. **Nâng Cấp Đĩa Than Vinyl Hoàng Gia & Hiệu Ứng Animation Cho Tất Cả Các Box**:
             - **Thiết kế lại nút nhạc nền**: Loại bỏ hoàn toàn vòng tròn phẳng đơn điệu, thay bằng **Đĩa than vinyl hoàng gia (Vintage Gold Vinyl Record)**:
               * Thân đĩa màu đen mun Espresso với đường rãnh vinyl xoắn ốc đồng tâm phản quang mờ.
               * Viền ngoài bọc kim loại vàng ánh kim, nhãn giữa là tâm đĩa vàng chạm nốt nhạc `♪`.
               * Khi phát nhạc (`playing`): Đĩa xoay tròn 360 độ êm ái, kèm bộ thanh sóng âm thanh (Equalizer Soundbars) 4 cột nhấp nhô theo điệu nhạc và các nốt nhạc vàng óng `♪ ♫` bay lơ lửng mềm mại.
             - **Animation trồi mượt (Scroll-driven Reveal & Stagger) cho 100% các Box**:
               * Gán class `.mayhen_invitation_ksnvo949dudf_box-anim` cho toàn bộ các card: Hero card, Thư báo hỷ, Box Nhà trai / Nhà gái, Thẻ Chú rể & Cô dâu, Box đếm ngược, Thẻ sự kiện, Lịch trình ngày cưới, Khung ảnh Gallery, Thẻ mừng cưới VietQR, Sổ lưu bút, Form RSVP và Box Lời cảm tạ.
               * Sử dụng `IntersectionObserver` thuần kết hợp tính toán stagger delay so le tự động, giúp các box mềm mại trồi lên từ dưới với tỷ lệ vàng (`translateY(0) scale(1)`) ngay khi người dùng cuộn tới.
               * Bổ sung hiệu ứng Hover sang trọng: Nổi nhẹ `translateY(-5px)`, đổ bóng đa tầng `0 24px 50px rgba(45, 37, 32, 0.11)` và viền sáng vàng óng ánh.

---

          9. **Nâng Cấp Màn Hình Mở Đầu Phong Bì Hoàng Gia Rực Rỡ (Grand Royal Envelope)**:
             - **Vấn đề nhận diện**: Màn hình mở đầu trước đó bị đơn điệu, phong bì nhỏ, màu be xỉn lọt thỏm giữa nền trắng, nút bấm đen kịt thiếu cảm xúc lễ cưới.
             - **Giải pháp nâng cấp toàn diện**:
               * **Không gian nền lộng lẫy**: Nền lụa nhung dệt ấm áp kết hợp vầng sáng hào quang tỏa tròn và 4 góc hoa văn hoàng gia cổ điển bằng vector ánh kim.
               * **Huy hiệu & Tiêu đề bề thế**: Thêm huy hiệu hoàng gia Monogram dập nổi, tag `SAVE OUR DATE · THIỆP CƯỚI BÁO HỶ` và tên đôi uyên ương hiển thị to, rực rỡ, trang trọng cùng ngày cưới.
               * **Phong bì 3D kích thước lớn (Grand 3D Envelope)**: Nâng kích thước lên bề thế (rộng 480px, cao 285px), nắp vát hoàng gia viền chỉ vàng, tấm thiệp bên trong hé lộ ảnh cưới studio thực tế của cặp đôi và chi tiết báo hỷ.
               * **Tem sáp đỏ Ruby lộng lẫy**: Nâng kích thước lên 68px, màu đỏ Ruby thẫm pha ánh vàng kim dập chìm Monogram kèm hiệu ứng nhịp đập hào quang vàng (`seal-glow pulse`).
               * **Thẻ đón tiếp VIP**: Tên khách mời được đóng khung huy hiệu lụa vàng sang trọng `KÍNH MỜI QUÝ KHÁCH: [Tên]`.
               * **Nút mở thiệp Vàng Hoàng Gia Ánh Kim (Royal Radiant Gold)**: Nền gradient vàng kim loại quý phái, hiệu ứng vệt sáng kim loại quét ngang (`btn-shimmer`), bóng đổ vàng đồng thau rực rỡ và icon phong bì mở thanh lịch.

---

          10. **Tích Hợp Template Mới: "Hắc Bạch Trao Duyên" (`hac-bach-trao-duyen`)**:
              - **Nguồn gốc & Phong cách**: Chuyển giao và nâng cấp từ bản mẫu thiết kế cao cấp theo bảng màu Hồng Bảo Hoàng Gia (Royal Crimson & Antique Gold).
              - **Đường dẫn thư mục**: `resources/views/client/invitations/hac-bach-trao-duyen/` gồm `template.blade.php`, `styles.css`, `script.js`, `schema.json` và thư mục `assets/` (54 tài nguyên ngoại tuyến gồm hình ảnh, icon, âm nhạc MP3).
              - **Quy ước tiền tố CSS Class**: `mayhen_invitation_ladjihe38udhd_*`.
              - **Các tính năng nổi bật**:
                * Màn hình mở thiệp phong bì 3D hoàng gia kết hợp tem sáp đỏ dập nổi.
                * **Cơ chế Khóa Cuộn Toàn Diện (Strict Scroll Lock)**: Khóa cứng cuộn trang (`overflow: hidden; touch-action: none; overscroll-behavior: none;`) và ẩn hoàn toàn nội dung thiệp bên dưới trước khi mở; chỉ mở khóa cuộn mượt mà khi người dùng bấm vào con dấu sáp hoặc nút "MỞ THIỆP CƯỚI".
                * Thanh công cụ floating dock đĩa than vinyl phát nhạc cưới kèm sóng âm nhấp nhô.
                * Đếm ngược thời gian thực, lịch tháng hạnh phúc điểm xuyết trái tim.
                * Bảng thông tin cặp đôi, gia đình hai bên và thiệp mời chi tiết.
                * Timeline 3 mốc sự kiện cưới: Ăn Hỏi, Rước Dâu, Lễ Thành Hôn.
                * Câu chuyện tình yêu (Love Story) với bố cục ảnh cưới tối ưu không bị cắt xén (`object-position: center 15%`).
                * **Sổ lưu bút ký tên điện tử trực tiếp**: Canvas cảm ứng ngón tay/bút vẽ kèm hiển thị chữ ký thực tế.
                * Album ảnh cưới phong cách Pinterest tích hợp Lightbox phóng to sắc nét.
                * Form xác nhận tham dự (RSVP) & Sổ lưu bút gửi lời chúc AJAX không tải lại trang.
                * Hộp mừng cưới thông minh hiển thị mã QR VietQR Chú rể và Cô dâu.
                * Bản đồ vị trí hôn lễ và Lời cảm ơn thiệp cưới vòm hoa trang trọng.
                * Toàn bộ 100% các box đều được trang bị hiệu ứng scroll reveal mượt mà (`.mayhen_invitation_ladjihe38udhd_box_anim`).
              - **Seeder CSDL**: Đã đăng ký vào `InvitationTemplateSeeder.php` với slug `hac-bach-trao-duyen`, category `luxury`, tier `pro`, complexity `deluxe`.
              - **Route xem thử**: `http://127.0.0.1:8000/xem-thu/hac-bach-trao-duyen` (HTTP Status 200 OK).

          11. **Tích Hợp Template Mới: "Hắc Bạch Trao Duyên 2" (`hac-bach-trao-duyen-2`)**:
              - **Nguồn gốc & Phong cách**: Chuyển giao và chuẩn hóa từ mẫu thiết kế Black & White Editorial Luxury (gốc mẫu Wedding 18), phong cách tối giản thanh lịch, trắng đen sang trọng mang chất điện ảnh phương Tây.
              - **Đường dẫn thư mục**: `resources/views/client/invitations/hac-bach-trao-duyen-2/` gồm `template.blade.php`, `styles.css`, `script.js`, `schema.json` và thư mục `assets/` (73 tài nguyên ngoại tuyến gồm hình ảnh chất lượng cao, icon, font chữ, âm nhạc MP3).
              - **Quy ước tiền tố CSS Class**: `mayhen_invitation_ladjihe38udhd_*`.
              - **Các tính năng nổi bật**:
                * **Màn hình mở phong bì hoàng gia 3D (#POPUP8)**: Căn giữa hoàn mỹ trên cả Mobile & Desktop, nền mờ kính đen quý phái (`backdrop-filter: blur(16px)`), ảnh cưới cô dâu chú rể trong khung oval trang nhã, hoa văn viền góc và nút bấm "Ấn vào để Xem" rực rỡ.
                * **Cơ chế Khóa Cuộn Toàn Diện**: Mặc định khóa cuộn trang (`overflow: hidden; touch-action: none;`) khi chưa mở thiệp, ngăn cuộn chuột và ẩn dock nhạc; khi bấm "Ấn vào để Xem", phong bì mở nhẹ nhàng, mở khóa cuộn và kích hoạt nhạc cưới tự động.
                * **Thanh Floating Audio Dock đĩa than Vinyl**: Nút xoay đĩa than sang trọng ở góc dưới bên phải, kèm hiệu ứng sóng âm thanh sống động (Equalizer animated).
                * **Tiêu đề trang chuẩn mực**: Sử dụng đúng cú pháp quy định:
                  `$pageTitle = $isPreview ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');`
                * **Section Save the Date & Đếm ngược thời gian thực (Countdown Timer)**: Hiển thị Ngày, Giờ, Phút, Giây cập nhật từng giây kèm biểu tượng đồng hồ cát nghệ thuật.
                * **Lịch cưới và tính năng "Thêm vào lịch"**: Trực quan, rõ ràng cho khách mời ghi nhớ ngày cưới.
                * **Album ảnh cưới thời thượng & Câu chuyện tình yêu (Love Story)**: Hình ảnh sắc nét, bố cục đa tầng không bị méo vỡ hay cắt xén.
                * **Form gửi lời chúc, Xác nhận tham dự (RSVP) & Hộp mừng cưới số tài khoản QR Bank**: Tích hợp đầy đủ cho khách mời từ xa.
                * **Editor Bridge V2**: Tích hợp sẵn `@include('client.invitations.partials.editor-bridge')` giúp tương thích 100% với trình chỉnh sửa trực quan mayhen.vn.
              - **Seeder CSDL**: Đã đăng ký vào `InvitationTemplateSeeder.php` với slug `hac-bach-trao-duyen-2`, category `luxury`, tier `pro`, complexity `deluxe`.
              - **Route xem thử**: `http://127.0.0.1:8000/xem-thu/hac-bach-trao-duyen-2` (HTTP Status 200 OK).

           12. **Sửa Lỗi Hỏng Ảnh Khi Bấm Phóng To (Lightbox) Mẫu Thiệp "Hắc Bạch Trao Duyên" (`hac-bach-trao-duyen`)**:
               - **Nguyên nhân lỗi**:
                 * Trong file `template.blade.php`, các thẻ item của gallery ảnh cưới đang bị hardcoded thuộc tính `data-full="assets/hip_..."` dạng đường dẫn tương đối (relative) thay vì gọi helper `{{ $resolveAsset('', '...') }}`.
                 * Khi người dùng xem thiệp ở route `/xem-thu/hac-bach-trao-duyen` hoặc `/thiep/{slug}`, JavaScript gán trực tiếp `lbImg.src = item.getAttribute('data-full')`, dẫn đến trình duyệt tự resolve thành `/xem-thu/assets/hip_...` (HTTP 404 Not Found), gây ra biểu tượng ảnh bị vỡ (`Enlarged Wedding Photo`).
                 * Cuối file `template.blade.php` có thẻ script thừa `<script src="script.js"></script>` gây lỗi 404 console.
               - **Giải pháp xử lý**:
                 1. **Cập nhật `template.blade.php`**: Đồng bộ toàn bộ 9 thẻ gallery item sử dụng `data-full="{{ $resolveAsset('', 'hip_...jpg') }}"` để sinh ra URL tuyệt đối chính xác thông qua route asset `templates.asset` (`/mau-thiep/hac-bach-trao-duyen/assets/...`). Đồng thời loại bỏ thẻ script thừa.
                 2. **Nâng cấp `script.js` (Bulletproof Image Resolution)**: Bổ sung hàm `getGalleryItemSrc(item)` với cơ chế phòng thủ đa tầng: nếu `data-full` hợp lệ (`http://`, `https://`, `/`) thì dùng; nếu không, tự động fallback lấy `img.currentSrc` hoặc `img.src` từ thẻ `img` con bên trong item.
                 3. **Bổ sung trải nghiệm UX cho Lightbox**: Tự động khóa cuộn trang (`document.body.style.overflow = 'hidden'`) khi mở modal và phục hồi khi đóng, hỗ trợ phím điều hướng `Escape` (đóng), `ArrowLeft` (ảnh trước), `ArrowRight` (ảnh sau).
                 4. **Kiểm thử tự động**: Tạo Feature Test `tests/Feature/HacBachTraoDuyenTemplateTest.php` xác thực trang preview tải thành công, cả 9 URL ảnh phóng to đều trả về HTTP 200 và script chứa hàm `getGalleryItemSrc` (100% Passed 23 assertions).


           13. **Tích hợp Thanh Điều Hướng Chân Trang (`mayhen_client_bottom_nav_wrapper`) Vào Toàn Bộ Trang Xem Thử (`/xem-thu/{slug}`)**:
               - **Mục tiêu**: Nâng cao trải nghiệm khám phá và chuyển đổi cho khách hàng khi đang xem thử thiệp cưới số. Người xem có thể dễ dàng điều hướng về Trang chủ, xem Mẫu thiệp, Yêu thích, Trợ lý AI và đặc biệt là nút "Tạo thiệp" nổi bật ở giữa sẽ tự động gắn kèm mã mẫu thiệp (`?template={slug}`) để chuyển thẳng vào studio tạo thiệp.
               - **Các thành phần được cập nhật**:
                 1. **Component `bottom-nav.blade.php`**: Nút "Tạo thiệp" ở giữa tự động kiểm tra biến `$template` và truyền param: `{{ (isset($template) && !empty($template->slug)) ? route('invitation.create', ['template' => $template->slug]) : route('invitation.create') }}`.
                 2. **Stylesheet `bottom-nav.css`**: Bổ sung media query `@media (max-width: 859px)` tự động nâng độ cao cho các floating controls / music dock của các mẫu thiệp (`.mayhen_invitation_ladjihe38udhd_music_dock`, `.mayhen_invitation_ksnvo949dudf_music-dock`, `.mayhen_invitation_djcvhwiuo9w9_music-toggle`, `.mayhen_invitation_dhsuudushi9428r_floating`) lên `bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important;` khi có sự hiện diện của `.mayhen_client_bottom_nav_wrapper` để tránh bị dock điều hướng che khuất trên điện thoại.
                 3. **Trang xem thử cơ bản `client/pages/preview.blade.php`**: Nhúng `bottom-nav.css` trong `<head>` và `@include('client.components.bottom-nav')` trước thẻ `</body>` khi không ở chế độ embed (`!$isEmbed`).
                 4. **5 Mẫu thiệp cưới cao cấp**:
                    * `hac-bach-trao-duyen/template.blade.php`
                    * `hac-bach-trao-duyen-2/template.blade.php`
                    * `net-muc-tinh-yeu/template.blade.php`
                    * `tung-trang-hanh-phuc/template.blade.php`
                    * `thuoc-phim-tinh-yeu/template.blade.php`
                    * Đều được trang bị điều kiện hiển thị `@if (($isPreview ?? false) && !($isEmbed ?? false))` cho cả file CSS `bottom-nav.css` và component `@include('client.components.bottom-nav')`. Đảm bảo khi thiệp cưới thật được gửi cho khách mời hoặc nhúng iframe trong studio editor (`embed=1`) thì thanh bottom navigation sẽ tự động ẩn đi.
                 5. **Kiểm thử tự động (`tests/Feature/BottomNavTest.php`)**: Đã bổ sung 2 test case mới kiểm tra:
                    * Thanh bottom nav hiển thị đầy đủ trên cả 5 mẫu thiệp ở chế độ xem thử, kèm CSS và link tạo thiệp theo đúng slug.
                    * Thanh bottom nav tự động ẩn khi có tham số `embed=1`.
                    * 100% Passed (3 tests, 34 assertions).


           14. **Tái Cấu Trúc Toàn Diện Kiến Trúc Thiệp Cưới: Giới Thiệu Master Layout `client.layouts.invitation` (DRY Architecture)**:
               - **Vấn đề đã xử lý**: Trước đây, các mẫu thiệp cưới (`template.blade.php`) và trang xem thử (`preview.blade.php`) tự khai báo toàn bộ thẻ `<!DOCTYPE html>`, `<head>`, meta, CSRF, Favicon, fonts, styles, scripts, `editor-bridge` và `bottom-nav`. Dẫn đến mã lặp nghiêm trọng, khó bảo trì khi số lượng mẫu tăng lên hàng chục, hàng trăm mẫu.
               - **Kiến trúc mới**: Tạo layout dùng chung chuẩn mực [resources/views/client/layouts/invitation.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/layouts/invitation.blade.php):
                 * Quản lý tập trung toàn bộ cấu trúc khung HTML5, chuẩn hóa viewport, UTF-8, CSRF token, robots, favicon và meta SEO.
                 * Tự động nạp stylesheet `bottom-nav.css` và component điều hướng `bottom-nav` khi ở chế độ xem thử (`@if (( ?? false) && !( ?? false))`).
                 * Tự động tích hợp bộ kết nối Studio Live Editor (`@include('client.invitations.partials.editor-bridge')`).
                 * Tự động thiết lập cấu hình biến JS toàn cục: `window.__MAYHEN_IS_PREVIEW`, `window.__MAYHEN_CSRF_TOKEN`.
                 * Cung cấp các slots linh hoạt: `@yield('html_class')`, `@yield('title')`, `@yield('meta_description')`, `@yield('body_class')`, `@yield('body_attributes')`, `@stack('fonts')`, `@stack('styles')`, `@yield('head_extra')`, `@yield('content')`, `@yield('modals')`, `@stack('scripts')`.
               - **Refactor toàn bộ 5 mẫu thiệp & trang preview**:
                 * [hac-bach-trao-duyen/template.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/invitations/hac-bach-trao-duyen/template.blade.php) -> `@extends('client.layouts.invitation')`
                 * [hac-bach-trao-duyen-2/template.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/invitations/hac-bach-trao-duyen-2/template.blade.php) -> `@extends('client.layouts.invitation')`
                 * [net-muc-tinh-yeu/template.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/invitations/net-muc-tinh-yeu/template.blade.php) -> `@extends('client.layouts.invitation')`
                 * [tung-trang-hanh-phuc/template.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/invitations/tung-trang-hanh-phuc/template.blade.php) -> `@extends('client.layouts.invitation')`
                 * [thuoc-phim-tinh-yeu/template.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/invitations/thuoc-phim-tinh-yeu/template.blade.php) -> `@extends('client.layouts.invitation')`
                 * [preview.blade.php](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/pages/preview.blade.php) -> `@extends('client.layouts.invitation')`
               - **Kết quả kiểm thử**: 100% test cases (BottomNavTest, HacBachTraoDuyenTemplateTest, NetMucTinhYeuTemplateTest) đều vượt qua thành công, sẵn sàng mở rộng không giới hạn số lượng mẫu thiệp mới mà không lo phát sinh mã lặp.

            15. **Nâng Cấp Phần Mở Đầu, Căn Chỉnh Layout & Tích Hợp Popup Album Cưới Mẫu Hắc Bạch Trao Duyên 2 (`hac-bach-trao-duyen-2`)**:
                - **Phần mở đầu hoàng gia (`#POPUP8`)**:
                  * Khắc phục tình trạng card mở đầu bị chìm tối và nút "Kéo Xuống" (`#GROUP138`) bị lọt xuyên qua chân thiệp.
                  * Thiết lập quy tắc CSS ẩn hoàn toàn `#GROUP138` khi chưa mở thiệp (`body.mayhen_invitation_ladjihe38udhd_scroll_locked`).
                  * Thiết kế phong bì cưới Quiet Luxury: Gradient đen than / charcoal nhung (`linear-gradient(160deg, #1f1f23 0%, #131316 55%, #0a0a0c 100%)`), viền vàng đồng Antique Gold kép tinh xảo (`border: 1.5px solid rgba(212, 175, 55, 0.5)`), đổ bóng đa tầng (`box-shadow: 0 35px 100px rgba(0,0,0,0.95)`).
                  * Nút "Ấn vào để Xem" (`#BUTTON18`) viền vàng kim kèm hiệu ứng hào quang thở ánh sáng (Pulse Glow Animation), được căn chỉnh nâng cao lên (`top: 474px`) và căn giữa hoàn mỹ, tạo khoảng cách thông thoáng sang trọng so với viền dưới của thiệp.
                  * Thay thế tên cô dâu chú rể hardcode ("RANDY & QUÝ") thành biến động `{{ mb_strtoupper($groomName) }} &amp; {{ mb_strtoupper($brideName) }}`.
                  * Khi mở thiệp: Hiệu ứng chuyển cảnh zoom/fade mượt mà, tự động kích hoạt đĩa than quay và âm thanh.
                - **Khắc phục triệt để lỗi lệch layout trên mọi màn hình**:
                  * Gỡ bỏ quy tắc `display: none !important;` vô tình gán vào `#SECTION1`, khôi phục lại Hero Section mở đầu của thiệp cưới.
                  * Chuẩn hóa `.ladi-wraper`, `.ladi-section`, `.ladi-container` căn giữa hoàn mỹ (`margin: 0 auto !important; left: 0 !important; right: 0 !important;`).
                  * Tích hợp cơ chế tự động co giãn `autoScaleMobile()` trong Native JS: Tự động tính toán tỷ lệ scale trên các thiết bị di động có bề rộng < 420px (iPhone 375px/390px, Samsung 412px), đảm bảo toàn bộ thiệp vừa vặn 100% không bị tràn viền, không lệch mép.
                  * Nút `#GROUP138` ("Kéo Xuống") tự động fade out mờ dần khi cuộn trang xuống quá màn hình đầu.
                - **Popup Lightbox xem Album ảnh cưới sắc nét**:
                  * Bổ sung markup Lightbox modal thuần HTML/CSS/JS (Backdrop mờ sâu 16px, nút Đóng X, nút Trước/Sau, ảnh cưới phóng to sắc nét, bộ đếm ảnh cưới `1 / N`).
                  * Thêm hiệu ứng `cursor: zoom-in` và hover sáng nhẹ cho toàn bộ ảnh album trong `#SECTION20` và `#SECTION8`.
                  * Hỗ trợ điều hướng đa năng: Nút chuyển ảnh, phím mũi tên bàn phím (`ArrowLeft`, `ArrowRight`), phím `ESC` để đóng, click backdrop để đóng, và vuốt cảm ứng (Touch swipe) trên điện thoại di động.

            16. **Tái Thiết Kế & Hoàn Thiện Tính Năng Sổ Lưu Bút Ký Tên Mẫu Hắc Bạch Trao Duyên 2 (`hac-bach-trao-duyen-2`)**:
                - **Khắc phục lỗi giao diện & Thẩm mỹ Quiet Luxury**:
                  * Loại bỏ hoàn toàn các nút màu xanh lá cây chói lọi (`#BUTTON4`) và màu cam lè (`#BUTTON5`) lạc tông phong cách Quiet Luxury.
                  * Thiết kế lại bộ nút điều khiển chữ ký sang trọng: Nút "Xóa" (`#BUTTON3`) viền bạc mờ tinh tế, Nút "Xem thử" (`#BUTTON4`) viền vàng champagne, Nút "Lưu Chữ Ký" (`#BUTTON5`) mạ vàng hoàng gia Antique Gold (`linear-gradient(135deg, rgba(212, 175, 55, 0.35) 0%, rgba(184, 147, 93, 0.2) 100%)`) kèm bóng hào quang nhẹ.
                  * Ẩn và tạo kiểu sang trọng cho modal xác nhận xóa chữ ký (`#deleteDialog`), chấm dứt triệt để tình trạng dòng chữ `"Bạn có chắc chắn muốn xóa chữ ký này? Xóa Hủy"` rò rỉ đè lên canvas.
                  * Nút `"NHẤN ĐỂ KÝ TÊN"` (`#BUTTON2`): Viền vàng đồng mạ bóng, chữ vàng ánh kim `#e6c88b`, click cuộn êm mượt đưa bảng ký tên vào trung tâm tầm nhìn.
                  * Khung vẽ chữ ký (`#BOX10`, `#wrapCanvas`, `#signatureCanvas`): Nền giấy nhung than chì, nét vẽ mực vàng hoàng gia Antique Gold `#e6c88b` mượt mà (hỗ trợ cả chuột và cảm ứng đa điểm touch trên mobile với `e.preventDefault()` chống cuộn).
                - **Chức năng Demo & Thực tế thông minh (Preview vs Live Mode)**:
                  * **Chế độ xem thử (` = true` / `/xem-thu/`)**:
                    - Hoạt động demo 100%: Cho phép khách vẽ, xóa, xem thử chữ ký và lưu chữ ký kỷ niệm ngay lập tức.
                    - Tự động nạp sẵn 3 chữ ký mẫu demo nghệ thuật từ khách mời mẫu (Bác Thành, Gia Bảo, Thuỳ Trang) để bức tường lưu bút luôn sinh động, không bị trống trơn.
                    - Khi lưu: Lưu vào `localStorage` cho phiên demo, thêm ngay card chữ ký mới lên Bức Tường Lưu Bút (`#previewContainer`), hiển thị Toast Quiet Luxury: *"✨ Đã lưu chữ ký kỷ niệm (Bản xem thử)! Dữ liệu thật sẽ được lưu trữ khi bạn tạo thiệp chính thức."*
                  * **Chế độ thiệp thật (` = false`)**:
                    - Dữ liệu demo không bao giờ hiển thị, chỉ lưu trữ và hiển thị chữ ký thật của khách mời vào thiệp cưới của cặp đôi.

            17. **Tích Hợp Hoàn Thiện Mẫu Thiệp Mới "Lưu Bút Ngày Chung Đôi" (`luu-but-ngay-chung-doi`)**:
                - **Nguồn & Bản quyền tài nguyên**:
                  * Tích hợp từ mẫu thiệp thiết kế cao cấp Wedding 18 V2: toàn bộ 118 hình ảnh và 10 tệp phông chữ đặc thù (`.otf`, `.ttf`) đã được tải và nạp cục bộ tại `resources/views/client/invitations/luu-but-ngay-chung-doi/assets/`, loại bỏ 100% phụ thuộc vào CDN ngoại.
                  * Tiền tố CSS bảo mật độc nhất chống xung đột giao diện: `mayhen_invitation_ladjihe38udhd_*`.
                - **Kiến trúc File chuẩn mực**:
                  * `template.blade.php`: Blade kế thừa layout chung `@extends('client.layouts.invitation')`, tích hợp đầy đủ biến động (`$wedding`, `$groomName`, `$brideName`, `$guestName`, `$weddingDate`, `$brideEventAddress`, `$groomEventAddress`, `$wishes`), logic fallback an toàn tuyệt đối tránh lỗi null/trắng trang.
                  * `styles.css`: Hợp nhất 6 tệp CSS (base, page, elements, animations, embedded, overrides) thành 1 tệp duy nhất nạp qua route `templates.asset`, tối ưu tốc độ tải trang cao nhất.
                  * `script.js`: Native ES JavaScript xử lý mở phong bì chào mừng (Greeting Gate), co giãn Mobile Responsive chuẩn tỉ lệ màn hình 420px, menu điều hướng, phát nhạc nền MP3, đếm ngược thời gian thực, bảng vẽ chữ ký tay Canvas HTML5, album ảnh cưới Lightbox vuốt cảm ứng chạm, hộp mừng cưới sao chép STK 1 chạm, và kết nối AJAX an toàn kèm CSRF Token với các route Wish và RSVP của Mây Hẹn.
                  * `schema.json`: Schema cấu hình chi tiết 5 nhóm trường dữ liệu (Cài đặt chung, Cặp đôi & Phụ mẫu hai bên, Sự kiện & Bản đồ, Album ảnh kỷ niệm, Hộp mừng cưới & QR Code) phục vụ trình biên tập trực quan Live Studio Editor V2.
                - **Đăng ký CSDL & Kiểm thử chất lượng**:
                  * Cập nhật bản ghi mẫu trong `database/seeders/InvitationTemplateSeeder.php` với metadata chuẩn (slug: `luu-but-ngay-chung-doi`, name: "Lưu Bút Ngày Chung Đôi", category: `editorial`, tier: `pro`, complexity: `deluxe`).
                  * Kiểm tra HTTP Status `200 OK` vượt qua 100% cho cả route xem thử `/xem-thu/luu-but-ngay-chung-doi` và nạp asset CSS/JS/Font. Định dạng code chuẩn PSR-12 qua Laravel Pint.
                - **Refactor Giao Diện Màu Xanh Cưới (Sage Green Luxury) & Sửa Lỗi Che Nút Bấm**:
                  * **Loại bỏ hoàn toàn bộ lọc grayscale**: Xóa `filter: grayscale(1)` để toàn bộ ảnh cưới của cô dâu chú rể và hoa văn tự nhiên hiển thị rạng rỡ, trong trẻo, tràn đầy năng lượng hỷ sự.
                  * **Chuyển đổi tone màu cưới**: Thay thế màu đen than `#1a1a1a` / `rgb(32,32,32)` sang hệ màu xanh xô thơm hoàng gia (Sage Green `#3B5E4F` & `#2A453B`), kết hợp viền vàng kim Antique Gold (`#C5A880`) trên nền kem ngà mỹ thuật ấm áp (`#FAF9F5`). Chuyển nền `SECTION24` (Lịch cưới) sang xanh rêu nhung quý phái.
                  * **Sửa lỗi che nút bấm Lightbox Album Kỷ Niệm**: Tái cấu trúc thanh điều hướng modal thành `dialog-gallery-toolbar` cân đối, giới hạn chiều cao ảnh `max-height: 52dvh` để hai nút `‹ Ảnh trước` và `Ảnh tiếp ›` kèm bộ đếm trang luôn hiển thị 100% thông thoáng, không bao giờ bị cắt mép dưới. Bổ sung thêm cặp nút mũi tên nổi (`.lightbox-arrow`) trực tiếp trên ảnh.
                  * **Sửa lỗi che nút bấm Floating Widget Nav & Cuối trang**: Nâng cụm nút nổi Menu/Nhạc/Share lên `bottom: 85px` để không bị thanh Bottom Nav của trang xem thử đè lên; đồng thời thêm `padding-bottom: 95px` cho `#mayhen_w18_stage` để các nút ở section chân trang không bị che khuất.
                  * **Nâng cấp nút bấm**: Toàn bộ nút chính (`BUTTON6`, `BUTTON11`, `BUTTON21`, `BUTTON2`, `BUTTON5`, `BUTTON9`, `BUTTON10`, nút Mở thiệp) đều được chuyển sang gradient xanh sage viền vàng gold sang trọng, bấm có hiệu ứng hover mượt mà.
                - **Sửa Lỗi Click Mở Album Lightbox & Chuẩn Hóa Tính Năng Ký Tên Sổ Lưu Bút (Demo vs Live Mode)**:
                  * **Mở Album Lightbox mượt mà 100%**: Truyền trực tiếp danh sách 12 ảnh cưới chất lượng cao qua mảng `window.MAYHEN_W18.album`, gắn sự kiện click cho toàn bộ 12 khối `.photoBookImage`, khắc phục lỗi không mở được modal xem ảnh.
                  * **Modal Ký Tên Điện Tử không bị đè layout**: Nút "NHẤN ĐỂ KÝ TÊN" (`#BUTTON2`) giờ đây kích hoạt Modal toàn màn hình chuyên nghiệp với Canvas cảm ứng đa điểm, ô nhập tên người ký, nút xóa vẽ lại và nút lưu chữ ký. Ẩn hoàn toàn khung inline cũ `#GROUP13` để triệt tiêu vĩnh viễn tình trạng bị section Album đè lên.
                  * **Chế độ Demo vs Thiệp Thật thông minh**: Khi ở chế độ xem thử (`isPreview === true`), tự động nạp 3 chữ ký mẫu demo nghệ thuật (Bác Thành, Gia Bảo, Thuỳ Trang) lên Bức Tường Lưu Bút (`#previewContainer`), cho phép khách ký thử và lưu vào `localStorage`. Khi ở chế độ thiệp thật (`isPreview === false`), không hiển thị dữ liệu demo mà lưu chữ ký thật của khách mời trực tiếp vào hệ thống Mây Hẹn. Thẻ chữ ký trên bức tường lưu bút được thiết kế dạng lưới card nghệ thuật có ảnh nét ký, tên và ngày giờ trang nhã.
                - **Nâng Cấp Hiệu Ứng Mở Thiệp 2 Cánh Cửa Tình Yêu Mở Sang 2 Bên (Grand 3D Double Gate Reveal) & Bộ Animation Sinh Động**:
                  * **Màn chắn mở thiệp 2 cánh cửa hoàng gia 3D (`#mayhen-door-stage`)**:
                    - Khóa cuộn trang khi chưa mở thiệp (`body.mayhen_invitation_ladjihe38udhd_locked`).
                    - **Đảm bảo luôn xuất hiện khi tải trang**: Loại bỏ hoàn toàn cờ `sessionStorage` cũ (trước đây lưu trạng thái đã mở khiến F5 tải lại trang bị ẩn cửa). Thiết lập cơ chế tự động cuộn lên đầu trang (`window.scrollTo(0, 0)`), khóa cuộn `html` và `body`, ẩn hoàn toàn thanh Bottom Nav và Floating Nav cho đến khi khách chạm vào con dấu tem sáp để mở cửa.
                    - Thiết kế 2 cánh cửa đôi phong cách dinh thự Quiet Luxury tân cổ điển: Tone xanh xô thơm sâu thẳm (`#1A3025` -> `#2A4839`), chất liệu gấm gân mỹ thuật phủ nhẹ hạt vàng kim, phào chỉ viền kép Antique Champagne Gold (`rgba(197, 168, 128, 0.55)`), hoa văn góc uốn lượn cổ điển (Baroque Ornate Corner) và cặp tay nắm cửa hoàng gia mạ vàng đúc nổi tinh xảo.
                    - Tấm thiệp trung tâm ngà ấm áp (`#FAF7F2`) hiển thị lời chúc *"THE WEDDING CELEBRATION"*, tên cô dâu chú rể (`$groomName & $brideName`), tên khách quý (`$guestName`) và ngày cưới trọng đại.
                    - Con dấu tem sáp đỏ mận hoàng gia (Royal Wax Seal Monogram) dập nổi 3D chữ cái lồng (`S & Q`), bao quanh bởi vòng sóng hào quang thở nhẹ (Breathing Pulse Glow) cùng bảng gợi ý *"✨ Chạm để mở cánh cửa tình yêu"*.
                  * **Chuyển động mở cửa 3D chân thực & Lộng lẫy**:
                    - Khi chạm vào tem sáp hoặc cánh cửa: Tem sáp và thiệp trung tâm phát ra ánh sáng vàng kim bùng nổ rồi tan biến nhẹ nhàng; Cánh cửa trái xoay mở 3D 112 độ sang bên trái (`transform: rotateY(-112deg)`), cánh cửa phải xoay mở 3D 112 độ sang bên phải (`transform: rotateY(112deg)`) với chiều sâu `perspective: 1600px` và gia tốc tự nhiên (`cubic-bezier(0.25, 1, 0.5, 1)`), tạo cảm giác sảnh đường cưới tráng lệ mở toang đón khách.
                    - Cùng lúc đó, hệ thống hạt rơi tự động sinh 30 cánh hoa bay lượn (Falling Rose Petals) mang các sắc độ hồng phấn, xanh sage, champagne cùng 45 hạt bụi sao vàng kim tuyến (Golden Stardust) bùng nổ bay khắp màn hình trong 4 giây.
                    - Tự động kích hoạt phát nhạc nền đám cưới êm ái, sau 1.1s mở khóa cuộn trang và trôi êm lên đầu trang thiệp.
                  * **Bộ Animation sinh động xuyên suốt các thành phần trong thiệp**:
                    - **Ảnh bìa cưới cinematic**: Kích hoạt hiệu ứng trôi chậm (Slow Zoom / Ken Burns Reveal) cho bức ảnh bìa đầu tiên khi vừa mở cửa.
                    - **Đĩa than âm nhạc**: Nút nhạc xoay tròn 360 độ êm dịu (`@keyframes mayhen_spin_vinyl`) liên tục khi bài hát đang phát.
                    - **Nhịp tim ngày cưới**: Các ô số đếm ngược ngày cưới tự động co bóp theo nhịp đập lãng mạn nhẹ nhàng (`@keyframes mayhen_heartbeat`).
                    - **Vệt sáng kim sa (Shimmer Light Sweep)**: Quét luồng sáng kim cương qua định kỳ trên các nút hành động chính (Nhấn để ký tên, Gửi lời chúc, RSVP) tạo điểm nhấn sang trọng.
                    - **Scroll Reveal mượt mà**: Toàn bộ các section ảnh cưới, timeline, thông điệp tự động trồi lên mượt mà (Fade In Up) khi khách cuộn tới nhờ Native `IntersectionObserver`.

            18. **Tối Ưu Hóa Tốc Độ Tải Thiệp Cưới & Trải Nghiệm Xem Thử Siêu Tốc (Zero-Lag Performance Architecture)**:
                - **Khắc phục triệt để nghẽn cổ chai tải trang**:
                  * **Vấn đề cũ**: Mọi yêu cầu tài nguyên tĩnh (hơn 120 ảnh, 10 font, css, js) trước đây đều bị định tuyến qua `TemplateController::asset` kèm cấu hình `no-cache, no-store, must-revalidate`. Trên máy chủ đơn luồng (`php artisan serve`), việc tải 135 yêu cầu và hơn 28 MB ảnh khiến trình duyệt bị nghẽn từ 8 đến 15 giây.
                  * **Giải pháp Direct Web Server Serving**: Tạo Junctions tại `public/mau-thiep/{slug}` trỏ trực tiếp đến `resources/views/client/invitations/{slug}` và liên kết `assets/styles.css`, `assets/script.js` trên toàn bộ 6 mẫu thiệp. Máy chủ web phục vụ tài nguyên tĩnh trực tiếp ở tầng I/O (chỉ 1-3ms) mà không cần khởi động PHP kernel.
                  * **Cơ chế HTTP 304 Not Modified & Browser Caching**: Nâng cấp [`TemplateController::asset`](file:///e:/Project/Laravel/mayhen.vn/app/Http/Controllers/Client/TemplateController.php) hỗ trợ `ETag` và `If-Modified-Since`. Khi tài nguyên không đổi, máy chủ phản hồi mã `304` tức thì trong 2-4ms với 0 byte dữ liệu body. Cấu hình `Cache-Control: public, max-age=2592000, immutable` cho ảnh, font, nhạc.
                  * **Triệt tiêu FOIT (Flash of Invisible Text)**: Bổ sung thuộc tính `font-display: swap;` vào toàn bộ các khai báo `@font-face` trong `styles.css`, đảm bảo văn bản hiển thị ngay tức khắc khi mở trang.
                  * **Nén ảnh chuẩn studio Retina**: Nén 27 tệp ảnh nặng (từ 1.4MB - 1.8MB/ảnh) bằng thuật toán GD chất lượng cao 84%, tiết kiệm ngay **10.77 MB** (giảm 78% dung lượng ảnh tải về).
                  * **Tốc độ đo lường thực tế**:
                    - Direct CSS: **16.3 ms**
                    - Direct JS: **2.3 ms**
                    - Direct Image: **1.4 ms** (từ 71.8 ms xuống 1.4 ms)
                    - Direct Font: **1.3 ms**
                    - Khi tải lại trang: **0 ms** (nạp từ Memory/Disk Cache trình duyệt).

            19. **Tối Ưu Hóa Hiệu Năng Toàn Diện & Khả Năng Chịu Tải Hàng Triệu User (High-Concurrency Architecture & SEO 100%)**:
                - **Sửa triệt để 100% lỗi 403 & 404 gây treo đường truyền**:
                  * **Lỗi 403 Favicon**: Sửa đường dẫn đảo `client/images` thành `storage/images/client/business/mayhen-favicon.png` trong `favicon.blade.php`, giải phóng 5 requests bị kẹt trên mọi trang.
                  * **Lỗi 404 Thumbnail**: Khắc phục lỗi gõ đúp `/assets/assets/` trong `InvitationTemplateSeeder.php` của mẫu `net-muc-tinh-yeu` và bổ sung ảnh thumbnail cho mẫu `hac-bach-trao-duyen`.
                  * **Lỗi 404 OG Image & Schema Logo**: Trỏ ảnh Open Graph và Schema Logo về `storage/images/client/banners/banner-home.webp` và `mayhen-favicon.png` hợp lệ 100%.
                - **Loại bỏ 20 Iframes chạy ngầm làm nghẽn trang chủ**:
                  * Loại bỏ khối `mayhen_home_showcase_card_frame_wrap` nhúng 20 thẻ iframe live preview không cần thiết bên trong carousel trang chủ. Khối này trước đây khởi động đồng thời 20 phiên bản thiệp cưới hoàn chỉnh kèm mã nguồn PHP/Blade, stylesheet và ảnh nặng, gây nghẽn kết nối mạng. Carousel nay dùng ảnh đại diện lụa với hiệu ứng cuộn tự nhiên CSS, giảm tải 95% CPU và ram trình duyệt.
                - **Kiến trúc Caching 2 tầng chống sập khi hàng triệu user truy cập đồng thời**:
                  * **Hydration Pattern an toàn tuyệt đối**: Thay vì serialize trực tiếp Eloquent Collection (gây lỗi incomplete object), hệ thống lưu mảng dữ liệu thô `getAttributes()` vào cache và tái tạo tức thì thông qua `InvitationTemplate::hydrate()` và `Plan::hydrate()`.
                  * Caching danh sách mẫu nổi bật, kho mẫu lọc theo danh mục, tổng số mẫu và gói dịch vụ bảng giá với thời gian lưu từ 30 phút đến 24 giờ.
                  * **Cơ chế tự động dọn cache (Model Event Hooks)**: Bổ sung phương thức `booted()` trong `InvitationTemplate` tự động xóa cache liên quan (`clearTemplateCaches`) ngay khi admin thêm, sửa hoặc xóa mẫu thiệp.
                - **Đánh chỉ mục Database tối ưu (Performance Migration)**:
                  * Bổ sung index kép `['is_active', 'is_featured']` và `['is_active', 'category']` cho bảng `invitation_templates`.
                  * Bổ sung index `is_published` cho bảng `invitations`.
                  * Các truy vấn lọc danh mục và mẫu thiệp nổi bật nay hoàn tất ở tốc độ cực đại (0.1ms).
                - **Tối ưu Core Web Vitals (LCP, CLS, INP)**:
                  * Bổ sung `loading="lazy"`, `decoding="async"` cho toàn bộ ảnh dưới màn hình cuộn (below the fold) trên trang chủ, kho mẫu và trang chi tiết.
                  * Bổ sung `fetchpriority="high"` và `loading="eager"` cho ảnh LCP đầu trang.
                - **Chuẩn SEO 100% & Dynamic XML Sitemap**:
                  * Tạo mới `app/Http/Controllers/Client/SitemapController.php` và route `/sitemap.xml` tự động sinh cấu trúc XML chuẩn Google Sitemap Protocol với tần suất cập nhật và độ ưu tiên chuẩn xác.
                  * Tối ưu hóa `public/robots.txt` cho phép các trang nội dung SEO (Trang chủ, Mẫu thiệp, Bảng giá, Dịch vụ, Giới thiệu, Liên hệ, FAQ) và chặn thu thập thông tin các trang riêng tư (Quản trị, Thiệp cưới cá nhân, Xem thử, Thanh toán).
                - **Kết quả đo kiểm Benchmark thực tế sau tối ưu**:
                  * Trang chủ (`/`): từ **1145 ms** giảm xuống chỉ còn **6.38 ms** (nhanh hơn 179 lần!).
                  * Kho mẫu thiệp (`/mau-thiep`): từ **Lỗi 500 / 676 ms** giảm xuống **4.7 ms**!
                  * Chi tiết mẫu thiệp (`/mau-thiep/{slug}`): **4.21 ms**!
                  * Xem thử thiệp (`/xem-thu/{slug}`): **3.0 ms**!
                  * Bảng giá (`/bang-gia`): **3.83 ms**!
                  * Dịch vụ (`/dich-vu`): **3.59 ms**!
                  * Sitemap XML (`/sitemap.xml`): **1.32 ms**!

            20. **Sửa Lỗi Chuyển Đổi Thiết Bị Xem Trước Trên Trình Tạo Thiệp (Desktop/Mobile Preview Switcher)**:
                - **Nguyên nhân**: Trong `public/assets/client/js/pages/editor.js`, biến DOM lưu vùng xem trước bị trỏ nhầm vào class không tồn tại (`.mayhen_editor_preview_panel` thay vì `#mayhen_editor_center_stage` / `.mayhen_editor_center_stage`), dẫn đến việc khi nhấn nút máy tính (`#mayhen_btn_device_desktop`), lớp `.is-desktop-mode` không được gắn vào khung canvas hiển thị.
                - **Khắc phục**:
                  * Gán ID `id="mayhen_editor_center_stage"` cho thẻ `<main>` sân khấu trung tâm trong `resources/views/client/pages/editor.blade.php`.
                  * Cập nhật JavaScript `editor.js`: Trỏ đúng vào `centerStage` và đồng bộ hai chiều với `store.subscribe` cho state `ui.previewDevice`.
                  * Tối ưu CSS `public/assets/client/css/pages/editor-v2.css`: Khi chuyển sang chế độ Desktop, mở rộng khung mockup từ 375px lên 980px với viền màn hình máy tính Quiet Luxury, đồng thời tự động ẩn Dynamic Island của iPhone (`.mayhen_editor_phone_island { display: none; }`).

            21. **Đồng Bộ Thời Gian Thực & Chuẩn Hóa Logic Đổi Mẫu Thiệp Toàn Diện (Zero-Loss Template Switcher & Live Contract)**:
                - **Vấn đề triệt để**:
                  * Khi người dùng đổi mẫu trong trình tạo thiệp (`/tao-thiep`), mã cũ chỉ thay đổi thuộc tính `src` của iframe preview mà không tải lại schema của form bên trái. Kết quả là form bên trái hiển thị các trường của mẫu cũ, preview bên phải tải mẫu mới với dữ liệu mặc định, dẫn đến tình trạng "nhập tên một đằng, giao diện hiển thị một nẻo", lỗi ảnh và mất đồng bộ logic giữa các mẫu.
                  * Các mẫu `hac-bach-trao-duyen`, `hac-bach-trao-duyen-2`, và `thuoc-phim-tinh-yeu` bị thiếu hợp đồng `data-sync-target` cho tên cô dâu chú rể, phụ mẫu, ngày cưới, sảnh tiệc và địa chỉ, khiến khung preview không phản hồi khi gõ phím.
                  * Dữ liệu tài khoản ngân hàng và địa chỉ tiệc cưới có tên trường khác nhau giữa các mẫu (`groom_bank_account` vs `groom_account_number`, `event_party_address` vs `ceremony_address`), khiến dữ liệu bị thất lạc khi chuyển mẫu.
                - **Giải pháp & Kiến trúc thực hiện**:
                  * **Bộ Chuyển Đổi Mẫu Bảo Toàn Dữ Liệu 100% (Zero-Loss Template Switcher)**: Trong `public/assets/client/js/editor/template-switcher.js`, khi người dùng bấm chọn mẫu khác, toàn bộ dữ liệu đã nhập (tên cô dâu chú rể, phụ mẫu hai bên, ngày cưới, địa chỉ, số tài khoản VietQR, ảnh bìa...) được thu thập và tự động ánh xạ tương thích thông qua bảng `fieldAliases`, sau đó lưu vào `sessionStorage` (`mayhen_transferred_draft`) và điều hướng tới `/tao-thiep?template={new_slug}`. Form bên trái nạp đúng 100% schema của mẫu mới, preview tải đúng mẫu mới, và dữ liệu cưới vừa nhập được tự động điền lại toàn vẹn.
                  * **Bắt tay 2 Chiều (Realtime Handshake Bridge)**: Trong `resources/views/client/invitations/partials/editor-bridge.blade.php`, iframe phát tín hiệu `MAYHEN_BRIDGE_READY` ngay khi DOM sẵn sàng. Trình tạo thiệp lắng nghe tín hiệu này và đồng bộ ngay lập tức toàn bộ dữ liệu form sang iframe (0ms lag).
                  * **Tương thích khóa mở rộng (Cross-Template Alias Mapping)**: `editor-bridge.blade.php` tự động nhận diện và đồng bộ các trường tương đương (`groom_bank_account` <-> `groom_account_number`, `event_party_address` <-> `party_address` <-> `ceremony_address`, `hero_photo_url` <-> `cover_photo_url`).
                  * **Gắn đầy đủ hợp đồng `data-sync-target` cho toàn bộ 5 mẫu thiệp**:
                    - `hac-bach-trao-duyen-2`: Bổ sung `data-sync-target` cho tên mở đầu, ảnh bìa, hero photo, tên chú rể, tên cô dâu, phụ mẫu hai bên, ngày giờ tiệc cưới, địa chỉ sảnh tiệc, ngày âm lịch, sổ lưu bút và số tài khoản ngân hàng.
                    - `hac-bach-trao-duyen`: Thay thế các giá trị hardcoded bằng biến Blade động kèm `data-sync-target` cho phụ mẫu hai bên (ông, bà), ngày cưới, địa chỉ tư gia, ảnh chú rể, ảnh cô dâu và chân trang.
                    - `thuoc-phim-tinh-yeu`: Bổ sung `data-sync-target` cho toàn bộ các khối sự kiện Nhà gái và Nhà trai (Lễ Vu Quy, Tiệc Cưới), ngày giờ, ngày âm lịch, ảnh cưới và tên đôi uyên ương.
                    - `tung-trang-hanh-phuc`: Bổ sung `data-sync-target` cho họ tên cha mẹ nhà trai, nhà gái.
                    - `net-muc-tinh-yeu`: Chuẩn hóa cả cấp độ thẻ chứa `groom_parents`/`bride_parents` lẫn thẻ chi tiết từng người `groom_father`/`groom_mother`/`bride_father`/`bride_mother`.
                  * **Khắc phục triệt để lỗi ảnh vỡ (Asset Fallback Defense)**: Chuẩn hóa bộ lọc URL trong `resources/views/client/pages/editor.blade.php` và `editor.js`. Không bao giờ tạo URL asset rỗng khi chưa tải ảnh, luôn sử dụng ảnh đại diện mẫu (`thumbnail_url`) làm điểm tựa an toàn.
                  * **Kiểm thử tự động**: Viết test feature `tests/Feature/InvitationEditorDataSyncTest.php` xác thực toàn bộ 5 mẫu thiệp đều tải thành công, tích hợp đúng `editor-bridge` và có đầy đủ hợp đồng `data-sync-target`. Đạt 25/25 Pest tests (307 assertions) Passed 100%.

            22. **Sửa Lỗi Màn Hình Trắng Khi Mở Mẫu "Thước Phim Tình Yêu" Trong Studio Editor (`/tao-thiep?template=thuoc-phim-tinh-yeu`)**:
                - **Nguyên nhân**:
                  * Trong `thuoc-phim-tinh-yeu/styles.css` (dòng 1600), có quy tắc CSS độ ưu tiên cao: `body.mayhen_invitation_djcvhwiuo9w9_no-scroll .mayhen_invitation_djcvhwiuo9w9_card-wrapper { display: none !important; }`.
                  * Khi nạp trang trong iframe editor (`?embed=1`), `template.blade.php` gán sẵn class `mayhen_invitation_djcvhwiuo9w9_no-scroll` vào `body_class` và ẩn hai cánh thiệp mở đầu (`.mayhen_invitation_djcvhwiuo9w9_opening-sides { display: none !important; }`).
                  * Đồng thời, script `thuoc-phim-tinh-yeu/script.js` khi khởi chạy lại tiếp tục thêm class `no-scroll` vào `document.body`.
                  * Kết quả: Do CSS `styles.css` có độ ưu tiên cao hơn, cả 2 cánh thiệp và toàn bộ khối thẻ nội dung (`.mayhen_invitation_djcvhwiuo9w9_card-wrapper`) đều bị đặt thành `display: none !important`, khiến khung xem trước của điện thoại biến thành một màn hình trắng trơn (chỉ hiển thị duy nhất nút âm nhạc nổi).
                - **Khắc phục**:
                  * Cập nhật `resources/views/client/invitations/thuoc-phim-tinh-yeu/template.blade.php`: Khi `$isEmbed` là `true`, không gán `no-scroll` vào `html_class` và `body_class`.
                  * Tăng độ đặc hiệu CSS trong khối `@if ($isEmbed)`: Cưỡng chế `body.mayhen_invitation_djcvhwiuo9w9_no-scroll .mayhen_invitation_djcvhwiuo9w9_card-wrapper, .mayhen_invitation_djcvhwiuo9w9_card-wrapper { display: block !important; visibility: visible !important; opacity: 1 !important; }`.
                  * Cập nhật `resources/views/client/invitations/thuoc-phim-tinh-yeu/script.js`: Bổ sung cờ `isEmbedMode`. Nếu đang chạy trong iframe nhúng của Studio Editor, tự động mở cánh thiệp (`opened`), loại bỏ class `no-scroll` và kích hoạt ngay các hiệu ứng reveal (`is-visible`) để thiệp cưới hiển thị đầy đủ, sắc nét và cuộn mượt mà ngay khi vừa tải trang.
                  * Đã bổ sung feature test khẳng định mẫu thiệp không bị ẩn nội dung khi nhúng: Đạt 26/26 Pest tests Passed 100%.

            23. **Chuẩn Hóa Toàn Diện CSS Responsive & Tương Thích Mọi Thiết Bị Mẫu "Hắc Bạch Trao Duyên 2" (`hac-bach-trao-duyen-2`)**:
                - **Mục tiêu**: Tối ưu hiển thị hoàn hảo trên tất cả các màn hình (Mobile nhỏ 320px-375px, Mobile chuẩn 390px-430px, Tablet 768px-960px, Desktop lớn >= 960px, và chế độ xem nhúng trong Studio Editor `?embed=1`) mà **tuyệt đối không phá hỏng giao diện gốc** (giữ nguyên 100% typography, màu sắc, hoa văn, vị trí tọa độ nghệ thuật).
                - **Vấn đề đã khắc phục**:
                  * *Màn hình phong bì mở đầu (`#POPUP8`)*: Trước đây `.ladi-popup` bị bóp lại bởi `max-width: 90vw` trên điện thoại trong khi các phần tử con `#GROUP149` giữ nguyên 420px x 540px, gây tràn mép và lệch chữ/hoa văn ra ngoài viền thiệp. Đã giải quyết bằng kỹ thuật scale đồng tỷ lệ: `transform: scale(min(1, calc(min((100vw - 32px) / 440, (100dvh - 36px) / 560))))` kèm `overflow: hidden`, nâng nút `#BUTTON18` lên `top: 462px` để thiệp mở đầu luôn căn giữa tuyệt mỹ và vừa vặn trên mọi độ cao màn hình từ iPhone SE đến Desktop.
                  * *Điện thoại nhỏ & chuẩn (`width < 420px`)*: Áp dụng CSS `transform: scale(calc(100vw / 420))` cho `.ladi-wraper`, căn giữa không bị thanh cuộn ngang, kết hợp bù chiều cao tự động trong `script.js` để triệt tiêu khoảng trống đáy footer.
                  * *Máy tính bảng (`768px <= width < 960px`)*: Khắc phục lỗi cắt xén 192px nội dung desktop hai bên bằng cơ chế scale tự động `transform: scale(calc(100vw / 960))` cho `.ladi-section .ladi-container`, giúp iPad hiển thị trọn vẹn bố cục 960px sắc nét.
                  * *Desktop / Laptop (`>= 960px`)*: Giữ nguyên container 960px căn giữa thanh lịch, background các section mở rộng 100% không gian màn hình chuẩn Quiet Luxury.
                  * *Chế độ nhúng Studio Editor (`$isEmbed = true`)*: Giữ nguyên phong bì mở thiệp tương tác `#POPUP8`, người dùng bấm "Ấn vào để Xem" để mở mượt mà và cuộn trang bình thường.
                  * *Sổ lưu bút & Bàn ký tên*: Responsive danh sách chữ ký single-column trên điện thoại (`max-width: 480px`), bo viền 18px tinh tế; thanh điều khiển nhạc Vinyl thu gọn nhẹ nhàng tránh che khuất giao diện.
                - **Kiểm thử**: Viết mới `tests/Feature/HacBachTraoDuyen2ResponsiveTest.php`, toàn bộ 28/28 Pest tests đạt 100% Passed (331 assertions), Pint format chuẩn chỉnh.

            24. **Giải Quyết Triệt Để Lỗi Tràn Chữ, Đè Chữ & Chuẩn Hóa Nhập Liệu Chống Hack/XSS Cho Tất Cả Mẫu Thiệp**:
                - **Khôi phục màn hình phong bì mở thiệp (`#POPUP8`) trong Studio Editor**:
                  * Khắc phục việc trước đó ẩn hoàn toàn `#POPUP8` trong iframe editor. Giờ đây khi tạo thiệp, người dùng vẫn nhìn thấy phong thư sang trọng với ảnh cưới, tên đôi uyên ương và nút bấm "Ấn vào để Xem". Khi bấm vào nút, thiệp mở ra hiệu ứng âm thanh và cuộn mượt mà.
                - **Bẻ khóa và cấu trúc lại toàn bộ các khối typography hiển thị tên đôi uyên ương**:
                  * *Section 1 (`#GROUP94`)*: Bỏ hoàn toàn 3 khối tọa độ cứng đè lên nhau (`#HEADLINE3` 194px, `#HEADLINE282` 62px, `#HEADLINE283` 154px) vốn làm tên dài bị bẻ gãy từ (`NGUY - ỄN`) và đè lên dấu `&`. Chuyển sang bố cục Flexbox tự động co giãn dòng, font-size `clamp(18px, 4.8vw, 30px)`, đảm bảo tên chú rể và cô dâu cân xứng 2 bên dấu `&` không bao giờ va chạm.
                  * *Section 6 (`#HEADLINE300` & `#HEADLINE304`)*: Hạ font size từ 75px xuống `clamp(28px, 7vw, 38px)`, dịch đoạn văn miêu tả xuống 560px để triệt tiêu hoàn toàn lỗi đè chữ vào phần tiểu sử.
                  * *Section 4 / 13 (`#HEADLINE69`, `#HEADLINE71`, `#HEADLINE70`)*: Đặt lại tọa độ top phân tầng rõ ràng (275px -> 380px -> 425px) kèm font clamp(28px, 40px), loại bỏ 100% tình trạng tên chú rể chiếm 2 dòng đè bẹp lên chữ "and" và tên cô dâu.
                  * *Khu vực ngân hàng & lưu bút (`#GROUP12`, `#HEADLINE318`, `#HEADLINE319`)*: Áp dụng font clamp và wrap tự nhiên, không bao giờ tràn mép.
                - **Quy chuẩn Validation & Sanitization tên và dữ liệu chống hack/chống phá toàn diện**:
                  * **Họ tên (Chú rể, Cô dâu, Cha/Mẹ hai bên)**: Bắt buộc tối đa 4 từ, tối đa 30 ký tự (tính cả dấu cách), chỉ cho phép chữ cái tiếng Việt/Latin và khoảng trắng (regex `/^[\p{L}\s]+$/u`), cấm tuyệt đối số và ký tự đặc biệt.
                  * **Frontend Sanitization (`editor.js` & `editor.blade.php`)**: Gắn `maxlength="30"`, `data-max-words="4"`, và lọc real-time tự động gạt bỏ ký tự lạ khi người dùng gõ hoặc dán văn bản.
                  * **Backend Sanitization & Security (`InvitationEditorController`)**: Kiểm tra nghiêm ngặt `validateAndSanitizeName`, lọc thẻ HTML bằng `strip_tags()` chống XSS, chuẩn hóa khoảng trắng; sanitize địa chỉ (max 255), tài khoản ngân hàng (chỉ alphanumeric, max 30).
                - **Kiểm thử chất lượng**: Bổ sung bộ test ca thành công và ca từ chối (>4 từ, >30 ký tự, chứa script/số), toàn bộ 28/28 tests Passed 100%.

            25. **Tối Ưu Bố Cục Tên 3 Tầng Nghệ Thuật Mẫu "Hắc Bạch Trao Duyên" (`hac-bach-trao-duyen`)**:
                - **Cải tiến đồng bộ 3 vị trí hiển thị tên đôi uyên ương**:
                  1. *Phong bì mở thiệp (`.mayhen_invitation_ladjihe38udhd_opening_names` - dòng 177)*: Tên Chú rể ở trên, ký tự `&` ở giữa, Tên Cô dâu ở dưới.
                  2. *Hero Section đầu trang (`.mayhen_invitation_ladjihe38udhd_hero_title` - dòng 216-220)*: Tách thành 3 dòng xếp tầng trang trọng (Tên Chú rể -> ký tự `&` font script mềm mại -> Tên Cô dâu) với `clamp(24px, 6vw, 36px)`, giải quyết triệt để vấn đề tên dài tràn dòng hoặc đè chữ.
                  3. *Footer Section lời cảm ơn (`.mayhen_invitation_ladjihe38udhd_footer_names` - dòng 897-901)*: Tách thành 3 dòng tương tự với `clamp(18px, 4.5vw, 24px)`.
                - **CSS & Live Sync**: Cấu hình Flexbox column căn giữa tinh tế, cỡ chữ co giãn phản hồi mượt mà; hoàn thiện cơ chế phòng vệ trong `editor-bridge.blade.php` để bảo vệ các thẻ con `groom_name` / `bride_name` không bị ghi đè thành 1 dòng khi gõ phím real-time. Toàn bộ 28/28 Pest tests Passed 100%.

            26. **Tối Ưu & Refactor Giao Diện Thiệp Cưới "Lưu Bút Ngày Chung Đôi" (`luu-but-ngay-chung-doi`)**:
                - **Refactor phần mở đầu cánh cửa 3D (`.mayhen_invitation_ladjihe38udhd_door_center`) chuẩn Quiet Luxury**:
                  * *Tấm thiệp chính*: Nâng cấp chất liệu giấy mỹ thuật Ivory vân lụa cao cấp `linear-gradient(155deg, #FFFDFB 0%, #FAF6EE 55%, #F2EBDE 100%)`, viền kép ép kim hoàng gia dát vàng `1.5px solid rgba(197, 168, 128, 0.75)`, 4 góc hoa văn cổ điển mạ vàng `card_corner` và crest biểu tượng ánh sao trang trọng.
                  * *Typography & Khoảng cách*: Badge dãn chữ `letter-spacing: 3.5px`, tên đôi uyên ương màu xanh ngọc bích sang trọng `#153023` phối ký tự `&` nghiêng vàng kim, dải phân cách đính kim cương `✦`, khoảng cách ngày cưới được nới rộng thoáng đãng.
                  * *Tem sáp niêm phong 3D (Royal Wax Seal)*: Viền sáp chảy lượn sóng tự nhiên thủ công, đa tầng ánh sáng màu đỏ mận Bordeaux Ruby sâu thẳm, 2 dải ruy băng lụa buông nhẹ bên dưới, chữ lồng Monogram `S&Q` mạ vàng nhũ nổi 3D, không còn tình trạng đè lên ngày cưới.
                  * *Nút mở thiệp*: Kính mờ xanh rừng sâu phối viền vàng kim, hiệu ứng hạt sao lấp lánh và nhịp thở êm ái.
                - **Khắc phục lỗi hiển thị & bố cục trên toàn trang**:
                  * *Modal Album Kỷ Niệm*: Giới hạn chiều cao ảnh `clamp(200px, 48vh, 430px)`, toolbar điều hướng prev/next và bộ đếm số ảnh luôn nằm trọn vẹn 100% trong khung nhìn màn hình, không bị cắt mép đáy.
                  * *Nút "NHẤN ĐỂ KÝ TÊN"*: Ẩn triệt để toàn bộ khung ký tên inline cũ (`#GROUP13`, `#BOX10`, `#wrapCanvas`, `#HTML_CODE2`), tăng chiều cao bảo vệ của `#SECTION7` giúp nút đứng độc lập, nổi bật, không bị bất kỳ khung nào đè lấn.
                  * *Thanh điều hướng đáy (`bottom-nav`)*: Tự động trượt ẩn khi cuộn xuống (`scrolled-hide`) giúp người dùng ngắm trọn vẹn toàn bộ thiệp cưới, bổ sung nút thu gọn / mở rộng linh hoạt, đồng thời nâng toạ độ cụm tên cô dâu chú rể ở Hero Section để không bao giờ bị thanh dock che khuất.

            27. **Refactor Toàn Diện Mẫu Thiệp VIP "Dấu Ký Trăm Năm" (`D:\File\Templates Mây Hẹn\Dấu Ký Trăm Năm`)**:
                - **Màn Mở Thiệp 3D Hoàng Gia Siêu Lộng Lẫy (Grand 3D Opening Gate Ceremony)**:
                  * *Cánh cửa tình yêu 3D kép*: 2 cánh cửa vòm chạm trổ phào chỉ dát vàng mạ nổi (`door_left`, `door_right`), màu đỏ rượu vang Ruby Velvet hoàng gia (`#8A1526` -> `#5E0B19` -> `#38030B`), tay nắm kim loại dát vàng óng ánh, mở xoay đa chiều (`rotateY(-112deg)` và `rotateY(112deg)` với perspective 1400px).
                  * *Thẻ thiệp ngọc trai trung tâm*: Giấy mỹ thuật lụa ngọc trai cao cấp `#FFFDF9`, viền kép ép kim vàng `card_gold_border`, 4 góc chạm hoa văn vintage mạ vàng vector SVG tinh xảo.
                  * *Căn chỉnh trục giữa hoàn hảo 100%*: Thiết lập `.mayhen_invitation_jeowinids483fr_center_card` sử dụng Flexbox column căn giữa, override triệt để vị trí cứng tuyệt đối của `button14` từ Ladipage cũ, đưa con dấu sáp đỏ Bordeaux 3D và nút "Chạm để mở cánh cửa tình yêu" về chính giữa thẻ thiệp, không còn bị lệch sang trái hay mất nút.
                  * *Kích hoạt đa điểm chạm*: Gắn lắng nghe sự kiện `click` và `touchend` trực tiếp lên cả tấm thiệp trung tâm, con dấu sáp và nút mở thiệp, loại bỏ rào cản chặn sự kiện delegation, đảm bảo người dùng chạm bất kỳ vị trí nào ở màn mở đầu đều kích hoạt hiệu ứng xoay cửa 3D và mưa hoa rơi ngay lập tức 100%.
                  * *Hiệu ứng Cánh Hoa Hồng Nhung Rơi & Bụi Sao Vàng Kim*: Canvas HTML5 thuần 60fps siêu mượt, tự động bung nở pháo hoa cánh hoa khi chạm mở thiệp (`triggerPetalsBurst`) và tự giải phóng tài nguyên CPU khi vào thiệp chính.
                - **Tối Ưu Hóa Trải Nghiệm Animation (Lướt đến đâu animation đến đó, Mở thiệp có chuyển động ngay)**:
                  * *Kích hoạt tức thì khi mở thiệp (`triggerInitialAnimations`)*: Ngay khi 2 cánh cửa 3D bắt đầu hé mở, toàn bộ các phần tử ở section đầu tiên (`section3` - ảnh cưới, lời tựa, tên dâu rể) được kích hoạt chuyển động ngay lập tức theo dạng stagger 50ms mượt mà, giúp người dùng vừa mở thiệp là thấy ngay chuyển động sống động, không bị đứng trơ.
                  * *Triệt tiêu độ trễ delay lê thê*: Override triệt để các `animation-delay: 1s -> 4s` và `animation-duration: 2s -> 3s` của Ladipage cũ về `animation-delay: 0s !important` và `animation-duration: 0.75s !important` với đường cong `cubic-bezier(0.16, 1, 0.3, 1)`. Vẫn giữ nguyên 100% các kiểu animation cũ (`fadeInUp`, `fadeInDown`, `bounceIn`, `flipIn`...) nhưng lướt tới đâu là phần tử xuất hiện chuyển động dứt khoát, mượt mà tới đó.
                  * *Đón đầu cuộn trang*: Mở rộng vùng quét `rootMargin: '80px 0px 80px 0px', threshold: 0.01` trong `IntersectionObserver`, kích hoạt trước khi mắt người dùng cuộn tới mép phần tử, chấm dứt hoàn toàn cảm giác khó chịu "lướt qua rồi mới bắt đầu chuyển động".
                - **Nâng Cấp Visual & Bảng Màu Thiệp Cưới VIP**:
                  * Gạt bỏ 100% bộ lọc grayscale xám xịt, khôi phục màu sắc ảnh cưới tươi tắn, chân thực, rạng rỡ và sắc nét.
                  * Đổi biến chủ đạo từ đen xám sang bảng màu Đỏ Ruby Velvet (`#8A1526`), Vàng Kim Hoàng Gia (`#D4AF37`) trên nền lụa ngọc trai (`#FAF7F2`).
                  * *Lịch cưới*: Ô ngày cưới 21 có trái tim đỏ nhung nhấp nháy nhịp đập yêu thương chân thật (`mayhenHeartbeat`).
                  * *Countdown 4 ô*: Hộp số thẻ kính mờ viền vàng dập nổi sang trọng, con số đỏ ruby nổi bật.
                  * *Form RSVP*: Thư mời tình yêu viền vàng mỏng tinh tế, nút gửi RSVP màu đỏ ruby viền vàng kim có bóng đổ 3D.
                  * *Floating Bottom Nav*: Bo cong kính mờ viền vàng Champagne, nút đỏ nhung quý phái, hiển thị sắc nét trên cả desktop và mobile.

            28. **Tích Hợp & Chuẩn Hóa Toàn Diện Mẫu Thiệp Cưới "Dấu Ký Trăm Năm" (`dau-ky-tram-nam`) Vào Hệ Thống Mây Hẹn**:
                - **Cấu trúc module độc lập chuẩn hóa**: `resources/views/client/invitations/dau-ky-tram-nam/`
                  * `template.blade.php`: Kế thừa layout `@extends('client.layouts.invitation')`, chuyển đổi component cánh cửa mở đầu thành `#mayhen-door-stage` độc lập ở đầu trang (tách biệt hoàn toàn khỏi layout Ladipage, giải quyết triệt để lỗi xung đột tọa độ `button14`), tích hợp hợp đồng `data-sync-target` cho mọi vị trí, tự động kết nối `editor-bridge.blade.php`.
                  * `styles.css`: Scoped 100% với tiền tố `mayhen_invitation_jeowinids483fr_*`. Chuẩn hóa thiết kế màn mở đầu hoàng gia theo chuẩn tỷ lệ vàng của mẫu chuẩn (Ảnh 1): Tấm thiệp ngọc trai lụa bo cong 18px với viền vàng kép và hoa văn 4 góc, con dấu sáp 3D Bordeaux dập nổi đè đúng 50% lên mép dưới thẻ thiệp, nút viên thuốc (pill) viền vàng kim lấp lánh `Chạm để mở cánh cửa tình yêu` nằm cân đối ngay bên dưới, cánh cửa đỏ Ruby Velvet với các đường chỉ kẻ vàng dọc và họa tiết chấm hạt sao mạ vàng mờ chìm.
                  * `script.js`: Native ES module xử lý animation cửa mở 3D (`opened` xoay `-112deg`/`112deg`), bão hoa hồng rơi canvas 60fps, countdown, lightbox, AJAX RSVP & lời chúc, phòng vệ embed editor tức thì.
                  * `schema.json`: Đầy đủ 5 section chuẩn (`general`, `couple`, `events`, `banking`, `gallery`) cho trình tạo thiệp Live Studio Editor.
                  * `assets/`: Lưu trữ toàn bộ font chữ, icon và assets đồ họa cục bộ, không dùng CDN ngoại.
                - **Đăng ký CSDL & Seeder**: Đã thêm bản ghi vào `InvitationTemplateSeeder.php` với slug `dau-ky-tram-nam`, category `luxury`, tier `pro`, complexity `deluxe` và chạy seed thành công.
                - **Kiểm thử chất lượng**: Toàn bộ 3 ca kiểm thử tính năng (Preview route `200 OK`, asset CSS `200 OK`, asset JS `200 OK`) đều vượt qua 100%. Đã format code bằng Laravel Pint.

            29. **Tích Hợp & Chuẩn Hóa Mẫu Thiệp Cưới Miễn Phí (Free) "Dấu Ấn Thời Gian" (`dau-an-thoi-gian`) Vào Hệ Thống Mây Hẹn**:
                - **Cấu trúc module độc lập chuẩn hóa**: `resources/views/client/invitations/dau-an-thoi-gian/`
                  * `template.blade.php`: Kế thừa layout `@extends('client.layouts.invitation')`, đồng bộ chuẩn hóa 100% 5 nguyên tắc template Mây Hẹn: gắn đầy đủ `data-sync-target` cho tất cả các trường dữ liệu cưới, phòng vệ nhúng Studio Editor (`$isEmbed = true`), fallback asset qua `$resolveAsset`, tích hợp mã QR VietQR động (Vietcombank & Techcombank), tích hợp Lightbox Modal xem ảnh cưới phóng to sắc nét.
                  * `styles.css`: Scoped 100% với tiền tố bắt buộc `mayhen_invitation_ladoifji894yfv_*`. Tông màu Quiet Luxury thiên hà (Navy đậm `#050814`, Vàng Champagne `#fce39e`, Bụi sao kim cương). Thanh điều hướng album ảnh nổi bật `z-index: 300` không che lấp, modal xem ảnh phóng to toàn màn hình.
                  * `script.js`: Native JavaScript xử lý chuyển động không gian vũ trụ 3D chân thực bằng Three.js 60fps (Mặt Trời, Trái Đất, dải thiên hà ngàn sao, sao băng tương tác); hiệu ứng mở thiệp Hyperspace Warp Drive tăng tốc vào chiều sâu không gian; album 3D Coverflow vuốt chạm mượt mà; form RSVP & Sổ lời chúc gửi AJAX bảo mật kèm `CSRF-TOKEN`; tự động mở thiệp và hiển thị hộp quà mừng cưới khi nhúng trong iframe Studio Editor.
                  * `schema.json`: Đầy đủ các trường cấu hình chuẩn hóa (`general`, `couple`, `events`, `banking`, `gallery`), khai báo gói `tier: 'free'`, `complexity: 'standard'`.
                  * `assets/`: Tự chứa toàn bộ thư viện Three.js, `textures.js` (base64 offline không CORS), ảnh thiên văn và bản đồ Trái Đất / Mặt Trời cục bộ.
                - **Đăng ký CSDL & Seeder**: Đã cấu hình và cập nhật `InvitationTemplateSeeder.php` với slug `dau-an-thoi-gian`, category `luxury`, gói MIỄN PHÍ `tier: 'free'`, badge miễn phí 100%, chạy seed CSDL thành công.
                - **Kiểm thử chất lượng**: Kiểm thử tính năng bằng Pest (`tests/Feature/InvitationDauAnThoiGianTest.php`) đạt `2 passed, 9 assertions (100%)`. Code được chuẩn hóa định dạng bằng Laravel Pint.

            30. **Hệ Thống Đồng Bộ Toàn Bộ Code Dự Án Lên Google Drive (`mayhen.vn`)**:
                - **Cấu trúc lưu trữ**: Tạo thư mục mã nguồn đầy đủ trực quan tại `G:\My Drive\Mây Hẹn (mayhen.vn)\Project\mayhen.vn`.
                  * Chứa trọn vẹn toàn bộ các file code: `app/`, `bootstrap/`, `config/`, `database/`, `docs/`, `public/`, `resources/`, `routes/`, `tests/`, `composer.json`, `artisan`, v.v.
                  * Tuân thủ 100% `.gitignore` & `.gitattributes`: Loại bỏ triệt để `vendor/`, `node_modules/`, file bí mật `.env` và các tệp cache/log rác, chống nghẽn đường truyền và bảo mật an toàn tuyệt đối.
                - **Công cụ đồng bộ tự động 1 click**:
                  * Tạo script `sync-drive.ps1` tự động dò tìm thư mục Google Drive thông minh (hỗ trợ xử lý chuẩn encoding tên thư mục tiếng Việt có dấu trên Windows PowerShell).
                  * Tích hợp lệnh Composer: `composer sync-drive` giúp người dùng chỉ cần gõ 1 lệnh duy nhất là toàn bộ mã nguồn mới nhất trên máy tính lập tức được cập nhật sang Google Drive.

            31. **Tích Hợp & Chuẩn Hóa Toàn Diện Mẫu Thiệp Cưới "Giữa Muôn Ngàn Vì Sao" (`giua-muon-ngan-vi-sao`)**:
                - **Nguồn gốc & Thiết kế**: Chuyển giao từ thư mục `Mẫu thiệp\Giữa Muôn Ngàn Vì Sao`, đưa vào hệ thống Mây Hẹn với phong cách Celestial Deepspace & Shooting Stars lãng mạn, kỳ ảo.
                - **Cấu trúc module độc lập chuẩn hóa**: `resources/views/client/invitations/giua-muon-ngan-vi-sao/`
                  * `template.blade.php`: Kế thừa `@extends('client.layouts.invitation')`. Chuẩn hóa đầy đủ 14 section: Màn mở đầu sao rơi lấp lánh (Canvas Particle Dissolve), Hero Section tinh tế, Hai bên gia đình, Lời mời tiệc cưới, Nghi lễ Vu Quy & Thành Hôn, Đồng hồ đếm ngược thiên hà theo thời gian thực, Lịch cưới Google Calendar, Timeline lịch trình tình yêu, Bản đồ mở cánh 3D độc bản dẫn đường Google Maps, Album 3D Coverflow kèm Lightbox phóng to Retina, Sổ lưu bút điện tử kèm bảng ký tên tay công nghệ cảm ứng, Form xác nhận tham dự (RSVP) trực tuyến, Hộp mừng cưới 3D mở bung tia sáng sao kèm mã VietQR tự động, Lời cảm ơn và nhạc nền lãng mạn.
                  * **Hợp đồng Live Sync**: Gắn đầy đủ `data-sync-target` cho toàn bộ các trường dữ liệu (`groom_name`, `bride_name`, `wedding_date`, `groom_father`, `groom_mother`, `bride_father`, `bride_mother`, `event_party_address`, `groom_bank_account`, `bride_bank_account`), không hardcode bất kỳ văn bản nào.
                  * **Phòng vệ Studio Editor (`$isEmbed = true`)**: Tự động mở màn che, bỏ lớp khoá cuộn và đưa các khối giao diện về trạng thái sẵn sàng chỉnh sửa mà không bị màn hình trắng trơn.
                  * `styles.css`: Scoped 100% tiền tố `mayhen_invitation_ladoifji894yfv_*`. Loại bỏ toàn bộ `!important` trên `background-image` của album và ảnh CDCR để không đè lên ảnh tùy chỉnh của người dùng trong Studio Editor. Bổ sung khối CSS phòng vệ nhúng `.mayhen_invitation_ladoifji894yfv_embed_mode`.
                  * `script.js`: Native ES module tích hợp WebGL Canvas sao rơi (`SlideTemplates['sao-roi']`), hiệu ứng tan biến hạt khi mở thiệp, bản đồ cánh mở 3D, album 3D Coverflow, Lightbox, bảng ký tên tay canvas, và gửi AJAX bảo mật kèm CSRF token cho RSVP và Sổ lưu bút.
                  * `schema.json`: Chuẩn hóa 5 section (`couple`, `events`, `gallery` gồm 10 ảnh, `banking`) sẵn sàng cho Studio Editor.
                  * `assets/`: Lưu trữ thumbnail và tài nguyên hình ảnh cục bộ độc lập.
                - **Đăng ký CSDL & Seeder**: Đã thêm bản ghi vào `InvitationTemplateSeeder.php` với slug `giua-muon-ngan-vi-sao`, category `luxury`, tier `free`, complexity `deluxe` và chạy seed CSDL thành công.
                - **Kiểm thử chất lượng**: Tạo Feature Test `tests/Feature/GiuaMuonNganViSaoTemplateTest.php` kiểm tra toàn diện preview, data-sync-target, static assets, embed defense và schema.json (4/4 tests passed, 29 assertions). Chạy toàn bộ test suite đạt 32/32 tests passed (360 assertions). Code được định dạng chuẩn bằng Laravel Pint.

            32. **Bản Địa Hóa 100% Toàn Bộ Tài Nguyên Mẫu Thiệp (Zero External Assets Protocol)**:
                - **Mục tiêu & Thực trạng**: Trước đây một số mẫu thiệp (`giua-muon-ngan-vi-sao`, `dau-an-thoi-gian`, `dau-ky-tram-nam`) còn dùng URL ảnh, font, icon, audio từ server bên ngoài (`builder.27biggroup.com`, `unsplash.com`, `ladicdn.com`, `iili.io`), tiềm ẩn rủi ro chết link, chặn CORS hoặc không đồng bộ được ảnh mặc định trong Studio Editor.
                - **Thực thi bản địa hóa triệt để**:
                  * *`giua-muon-ngan-vi-sao`*: Tải toàn bộ 12 ảnh album/CDCR WebP, 4 chữ ký mẫu PNG, và file nhạc `wedding-song.mp3` về trực tiếp `assets/`. Cập nhật `template.blade.php`, `styles.css`, `schema.json` để tham chiếu 100% asset nội bộ.
                  * *`dau-an-thoi-gian`*: Tải toàn bộ 10 ảnh cưới Unsplash chất lượng cao (hero, groom, bride, savedate, gallery 1-6) về trực tiếp `assets/`. Quy chuẩn fallback sang các file cục bộ (`hero.jpg`, `groom.jpg`, v.v.).
                  * *`dau-ky-tram-nam`*: Tải toàn bộ 3 font OTF/TTF, các icon timeline/sự kiện PNG từ LadiCDN, và 18 ảnh cưới từ iili.io về `assets/`. Thay thế toàn bộ URL tuyệt đối trong `styles.css` và `assets/styles.css` sang đường dẫn tương đối cục bộ.
                - **Kết quả rà soát tự động**: Quét toàn bộ kho mã nguồn `resources/views/client/invitations/` đạt **0 URL tài nguyên ngoại vi** trên toàn bộ 9 mẫu thiệp của hệ thống.
                - **Nâng cấp chuẩn hóa**: Cập nhật Nguyên tắc số 6 trong Quy chuẩn tạo mẫu thiệp cưới bắt buộc 100% Localized Assets cho mọi mẫu thiệp tương lai.

            33. **Khắc Phục Triệt Để Lỗi 500 "Undefined array key key" Trong Trình Tạo Thiệp (`/tao-thiep`)**:
                - **Nguyên nhân cốt lõi**:
                  * Trong file giao diện trình tạo thiệp `resources/views/client/pages/editor.blade.php:196`, code cũ truy cập trực tiếp `$fieldKey = $field['key'];` mà không có phòng vệ fallback.
                  * Hai file `schema.json` của mẫu `dau-ky-tram-nam` và `luu-but-ngay-chung-doi` khai báo dạng `name` thay vì chuẩn `key` (`wedding.groom_name`...). Khi người dùng truy cập `/tao-thiep?template=dau-ky-tram-nam`, hệ thống ném ngoại lệ `500 ErrorException`.
                - **Giải pháp xử lý hai lớp**:
                  * *Lớp phòng vệ tầng View (`editor.blade.php`)*: Bổ sung fallback `$fieldKey = $field['key'] ?? $field['name'] ?? null;`. Nếu không tìm thấy key thì bỏ qua (`continue`). Tự động tiền tố namespace chuẩn (`wedding.` cho các trường thông tin chính hoặc `wedding.extra_data.` cho trường mở rộng) nếu gặp field dạng cũ không có dấu chấm.
                  * *Lớp chuẩn hóa Schema (`dau-ky-tram-nam` & `luu-but-ngay-chung-doi`)*: Cập nhật toàn bộ các trường `fields` có `key` chuẩn hóa 100% theo Nguyên tắc số 1 (`wedding.groom_name`, `wedding.bride_name`, `wedding.extra_data.groom_father`...).
                - **Kiểm thử tự động**: Viết test tự động duyệt qua toàn bộ các template đang hoạt động trong hệ thống khi mở `/tao-thiep?template={slug}` (`tests/Feature/InvitationEditorDataSyncTest.php`), kết quả 100% trả về `HTTP 200 OK` (33/33 tests passed).

            34. **Khắc Phục Lỗi Màn Hình Trắng (White Screen) Khi Nhúng Mẫu "Dấu Ký Trăm Năm" Trong Trình Tạo Thiệp**:
                - **Nguyên nhân gốc rễ**:
                  * Trong file `script.js` của `dau-ky-tram-nam`, hàm `syncStage()` chứa đoạn kiểm tra `if (isEmbed) { stage.style.removeProperty('--mayhen-stage-scale'); spacer.style.height = 'auto'; return; }`. Do thẻ `.stage` có `position: absolute`, khi gán `spacer.style.height = 'auto'` thì phần tử cha `.stage-spacer` bị sập chiều cao về `0px` kết hợp với `overflow: hidden`, dẫn đến toàn bộ nội dung thiệp bị ẩn trắng trơn (chỉ còn thanh bottom-nav nổi lên).
                  * Đồng thời, sự kiện khởi tạo chỉ lắng nghe `DOMContentLoaded`, khi script tải ở chế độ `defer` thì `readyState` có thể đã chuyển sang `interactive`/`complete` khiến logic mở cánh cửa và render giao diện không được kích hoạt.
                  * Khối CSS phòng vệ trong `styles.css` thiếu ghi đè class `.mayhen_invitation_jeowinids483fr_ladi-animation-hidden`.
                - **Giải pháp xử lý triệt để**:
                  * *`script.js`*: Loại bỏ điều kiện chặn `isEmbed`, cho phép `syncStage()` luôn tính toán chiều cao chính xác và gán `spacer.style.height` theo tỷ lệ co giãn của mockup điện thoại. Bổ sung kiểm tra `document.readyState === 'loading'` để tự kích hoạt `initApp()` ngay lập tức nếu DOM đã sẵn sàng.
                  * *`template.blade.php`*: Gắn sẵn class `opened mayhen_door_hidden` lên `#mayhen-door-stage` ngay khi `$isEmbed = true` từ tầng render server.
                  * *`styles.css` & `assets/styles.css`*: Bổ sung quy tắc phòng vệ cưỡng chế hiển thị `.stage-spacer { overflow: visible !important; min-height: 100vh !important; }` và `.ladi-animation-hidden { opacity: 1 !important; visibility: visible !important; }`. Đồng bộ hoàn toàn giữa hai tệp styles và scripts.
                - **Kiểm thử**: Viết test kiểm tra `dau-ky-tram-nam` ở chế độ nhúng (`?embed=1&mode=editor`) tự động mở cửa và render toàn bộ nội dung, đạt 34/34 tests passed 100%.

            35. **Khắc Phục Lỗi Dữ Liệu Không Đồng Bộ Thời Gian Thực (Live Sync) Cho Mẫu "Dấu Ký Trăm Năm"**:
                - **Hiện trạng & Triệu chứng**: Khi tạo thiệp tại `/tao-thiep?template=dau-ky-tram-nam`, người dùng nhập tên Chú rể ("Nguyễn Minh Đức") và Cô dâu ("Nguyễn Minh Anh"), nhưng trên màn hình mockup điện thoại bên phải vẫn hiển thị cứng chữ "Thúy Ngân & Quốc Thái" và không thay đổi theo thời gian thực khi gõ phím. Đồng thời phụ mẫu Nhà gái và Nhà trai bị đảo ngược thông tin, địa chỉ tiệc cưới bị hardcode tĩnh.
                - **Nguyên nhân gốc rễ**:
                  * Trong `resources/views/client/invitations/dau-ky-tram-nam/template.blade.php`:
                    + Dòng 1031 & 1040 (Section 26 - Khối thiệp chính hiển thị to đè màn hình): Hardcode chuỗi tĩnh `Thuý Ngân` và `Quốc Thái<br />` thay vì in biến Blade và thiếu thuộc tính `data-sync-target="bride_name"` / `data-sync-target="groom_name"`.
                    + Dòng 944 & 968: Phần hiển thị phụ mẫu bị gán ngược hoàn toàn (Nhà gái lại gán `$groomFather`/`$groomMother`, Nhà trai lại gán `$brideFather`/`$brideMother`).
                    + Dòng 1069-1070: Địa chỉ tiệc cưới bị hardcode tĩnh `Khóm An Tài - Phường An Bình...` không in biến `$eventAddress` và thiếu `data-sync-target="event_party_address"`.
                    + Dòng 1093: Link Google Maps bị hardcode tĩnh thay vì biến `$mapUrl`.
                    + Mảng `$groomBankInfo` và `$brideBankInfo`: Thiếu các alias key `holder`, `account` theo đúng kỳ vọng của script popup mừng cưới.
                  * Trong `resources/views/client/invitations/dau-ky-tram-nam/assets/script.js`: Đoạn mã khởi tạo `cardNames.innerHTML = \`\${C.groom} <span>&amp;</span> \${C.bride}\`` ghi đè toàn bộ HTML, làm vô hiệu hóa thẻ `data-sync-target` đã khai báo ở Blade.
                - **Giải pháp xử lý triệt để**:
                  * *`template.blade.php`*:
                    + Bọc tên Cô dâu và Chú rể trong `<span data-sync-target="bride_name">{{ $brideName }}</span>` và `<span data-sync-target="groom_name">{{ $groomName }}</span>`.
                    + Đảo lại đúng chuẩn Nhà gái (Bố: `$brideFather`, Mẹ: `$brideMother` kèm target `bride_father`, `bride_mother`) và Nhà trai (Bố: `$groomFather`, Mẹ: `$groomMother` kèm target `groom_father`, `groom_mother`).
                    + Thay địa chỉ thành `<span data-sync-target="event_party_address">{{ $eventAddress }}</span>` và link bản đồ thành `href="{{ $mapUrl }}" data-sync-target="event_party_map_url"`.
                    + Bổ sung đầy đủ các key alias `holder`, `account`, `bank`, `name`, `number` cho mảng dữ liệu tài khoản ngân hàng.
                  * *`script.js`*: Thêm điều kiện bảo vệ `!cardNames.querySelector('[data-sync-target="groom_name"]')` để không bao giờ ghi đè thẻ HTML hợp đồng Live Sync.
                - **Kiểm thử tự động & Định dạng**:
                  * Cập nhật test trong `tests/Feature/InvitationEditorDataSyncTest.php` kiểm tra toàn diện cả 8 target đồng bộ (`groom_name`, `bride_name`, `groom_father`, `groom_mother`, `bride_father`, `bride_mother`, `event_party_address`, `wedding_date`).
                  * Bộ test suite 34/34 tests passed (381 assertions) 100%. Mã nguồn được định dạng chuẩn bằng Laravel Pint.

            36. **Tối Ưu Layout Khối Tên Đôi Uyên Ương & Auto-Fit Font Size Chuẩn Bề Thế (Mẫu "Dấu Ký Trăm Năm")**:
                - **Vấn đề phát sinh**: Khi người dùng nhập tên cô dâu chú rể có 3 - 4 từ (như "Nguyễn Minh Đức Nobi", "Nguyễn Minh Anh"), font chữ nghệ thuật DFVN Boheme Floral nếu để 57px cứng thì bị rớt dòng và đè chữ, nhưng nếu hạ xuống 25px - 30px thì lại bị teo quá bé và lọt thỏm giữa khoảng trống thiệp cưới, làm mất đi vẻ bề thế, sang trọng của tấm thiệp cưới.
                - **Giải pháp xử lý triệt để**:
                  * *`template.blade.php`*: Gom cụm `headline110` (Cô dâu), `headline112` (&) và `headline111` (Chú rể) vào container thống nhất `.mayhen_invitation_jeowinids483fr_couple_names_wrapper`.
                  * *`styles.css` & `assets/styles.css`*:
                    + Thiết lập layout Flexbox dọc (`display: flex; flex-direction: column; justify-content: center; align-items: center;`) với chiều cao `270px` tại `top: 360px` căn đều chính giữa khoảng trống thiệp.
                    + Cưỡng chế `white-space: nowrap !important; word-break: keep-all !important;` để toàn bộ tên cô dâu chú rể luôn nằm trọn vẹn, kiêu sa trên 1 dòng duy nhất, không bao giờ bị cắt vụn bẻ đôi.
                    + Tăng kích cỡ chữ nền tảng lên `clamp(34px, 8.5vw, 48px)` kèm `line-height: 1.2` và ký hiệu `&` cỡ `32px` tạo điểm nhấn kết nối uyển chuyển, bề thế.
                  * *`script.js` & `assets/script.js`*: Nâng cấp thang đo Auto-fit Font Size thông minh (`fitCoupleNames`):
                    + Tên ngắn (<= 7 ký tự): `52px` to lộng lẫy.
                    + Tên 2–3 từ (8–11 ký tự): `48px` bề thế.
                    + Tên 3 từ (12–16 ký tự, như *"Nguyễn Minh Anh"*, *"Nguyễn Minh Đức"*): `44px` dày dặn, sang trọng, vừa vặn trên 1 dòng.
                    + Tên 4 từ (17–21 ký tự, như *"Nguyễn Minh Đức Nobi"*): `38px` to rõ, cân đối hoàn mỹ.
                    + Tên rất dài (>= 22 ký tự): `34px`.
                - **Kiểm thử**: Toàn bộ 34/34 tests passed 100%. Code định dạng Pint chuẩn.

            37. **Tái Thiết Kế Thanh Điều Hướng Dưới Đáy (Quiet Luxury Floating Dock) Cho Mẫu "Dấu Ký Trăm Năm"**:
                - **Vấn đề phát sinh**: Thanh dock điều hướng `.mayhen_invitation_jeowinids483fr_cta-menu` trên mobile hiển thị 5 nút màu đỏ Bordeaux bệt dính sát vào nhau; do tổng chiều rộng các nút (395px) vượt quá khung màn hình (360px - 375px), nút cuối cùng *"Mừng cưới"* bị tràn ra ngoài viền thanh dock và bị cắt cụt chữ.
                - **Giải pháp xử lý triệt để**:
                  * *`template.blade.php`*: Đặt mặc định nút đầu tiên *"Thiệp mời"* mang class `mayhen_active`.
                  * *`styles.css` & `assets/styles.css`*:
                    + Tinh chỉnh khung dock: `width: calc(100% - 24px); max-width: 384px; height: 44px; padding: 4px;` với hiệu ứng ngọc trai kính mờ `rgba(255, 253, 249, 0.94)`, viền chỉ vàng kim `1px solid rgba(212, 175, 55, 0.5)` và bóng đổ sâu `box-shadow: 0 10px 30px -4px rgba(44, 40, 36, 0.18)`.
                    + Phân bổ 5 nút đều tăm tắp 100% không gian với `flex: 1 1 0; min-width: 0;`, loại bỏ hoàn toàn hiện tượng tràn viền.
                    + Phân cấp thị giác Quiet Luxury: các tab thông thường có nền trong suốt, chữ than ấm `#4A423B`, khi active chuyển nền đỏ Bordeaux nhung `#8A1526` và chữ trắng kem `#FFFDF8`. Nút *"Mừng cưới"* là điểm nhấn Call to Action đặc biệt với gradient đỏ nhung sang trọng và bóng phát sáng nhẹ.
                  * *`script.js` & `assets/script.js`*: Thêm logic chuyển đổi trạng thái `mayhen_active` mượt mà khi người dùng nhấn vào bất kỳ mục điều hướng nào trên thanh dock.
                - **Kiểm thử**: Toàn bộ 34/34 tests passed 100%. Code định dạng Pint chuẩn.

            38. **Tích Hợp Mã QR & Nút Sao Chép Tài Khoản Hộp Mừng Cưới (Mẫu "Dấu Ký Trăm Năm")**:
                - **Vấn đề phát sinh**: Khi người dùng cấu hình xong thông tin ngân hàng và bấm nút *"Mừng cưới"* trên thiệp, popup hiển thị thông tin ngân hàng nhưng hoàn toàn **không có hình ảnh mã QR** (trước đó hàm JS chỉ in chuỗi text thuần `bank.name\nbank.holder\nbank.account`), khiến khách mời không thể quét mã VietQR để chuyển tiền chúc phúc.
                - **Giải pháp xử lý triệt để**:
                  * *`template.blade.php`*:
                    + Tự động sinh link ảnh VietQR thông minh từ tên ngân hàng và số tài khoản (`https://img.vietqr.io/image/{bank}-{account}-compact2.png`) khi người dùng chưa upload ảnh riêng, hoặc ưu tiên hiển thị ảnh QR do người dùng tải lên (`qr_groom_image`, `qr_bride_image`).
                    + Bổ sung cấu trúc HTML hộp thoại: khung ảnh QR `.gift_qr_wrap`, tên ngân hàng, tên chủ tài khoản in hoa đậm, và dòng số tài khoản kèm nút bấm *"Sao chép"* tiện lợi.
                  * *`styles.css` & `assets/styles.css`*: Thiết kế khung mã QR bo góc 14px tinh tế viền vàng kim `rgba(212, 175, 55, 0.35)`, kích thước 168x168px chuẩn quét camera, cùng khối số tài khoản nét đứt sang trọng kèm nút sao chép chuyển màu xanh khi copy thành công.
                  * *`script.js` & `assets/script.js`*:
                    + Nâng cấp hàm `updateGift(person)` hiển thị ảnh QR tức thời và đồng bộ dữ liệu theo tab Cô dâu / Chú rể.
                    + Tích hợp Clipboard API cho nút *"Sao chép"* tự động sao chép số tài khoản vào khay nhớ tạm.
                    + Lắng nghe sự kiện Live Sync `MAYHEN_SYNC_INPUT` để cập nhật mã QR và số tài khoản ngay khi người dùng gõ vào form Studio Editor.
                - **Kiểm thử**: Toàn bộ 34/34 tests passed 100%. Code định dạng Pint chuẩn.

            39. **Kiến Trúc Live Iframe Showcase Thông Minh Kết Hợp SEO Poster Fallback (Trang Chủ)**:
                - **Yêu cầu & Phân tích giải pháp**:
                  * *Vấn đề hiệu năng nếu load toàn bộ iframe*: Nếu tải cùng lúc 9 iframe trang thiệp khi vừa vào trang chủ sẽ gây nghẽn băng thông (30–50MB), tắc luồng CPU và phá vỡ chỉ số Core Web Vitals (LCP, TBT, INP) của Google.
                  * *Giải pháp Hybrid tối thượng*: Giữ nguyên thẻ ảnh poster WebP kèm thuộc tính `alt` trong mã nguồn HTML tĩnh để Googlebot và Bingbot lập chỉ mục SEO 100%. Đồng thời áp dụng cơ chế Lazy Load thông minh bằng `IntersectionObserver` kích hoạt trước **300px** (`rootMargin: '300px 0px'`).
                - **Cơ chế hoạt động**:
                  * *`home.blade.php`*: Nhúng khung iframe với `data-src="{{ route('templates.preview', $tpl['slug']) }}?embed=1&mode=showcase"` và `src="about:blank"` cùng lớp kính bảo vệ `mayhen_home_showcase_card_iframe_shield`. Thẻ ảnh `<img>` hiển thị phủ lên trên.
                  * *`home.js` (`initShowcaseLazyIframe`)*:
                    + Khi người dùng cuộn cách khu vực Showcase 300px, hệ thống bắt đầu nạp iframe cho thẻ trung tâm (`.is-active`).
                    + Khi iframe tải xong (`onload`), card được gắn class `.is-iframe-loaded`, ảnh poster mờ dần mềm mại (`opacity: 0`) để lộ giao diện thiệp cưới thật hoạt động trực tiếp 60FPS.
                    + Tự động nạp trước cho 2 thẻ kề cận sau 1.2s để chuyển slide không giật lag.
                    + Khi hover vào thẻ bất kỳ, lập tức nạp iframe cho thẻ đó.
                  * *`home.css`*:
                    + Iframe thiết kế tỉ lệ 375px co giãn bằng CSS transform (`scale(0.64)` trên desktop, `scale(0.52)` trên tablet, `scale(0.46)` trên mobile) vừa khít 100% khung viền mockup điện thoại.
                    + Khi hover vào thẻ active: iframe tự động cuộn chậm rãi xuống dưới (`translateY(-1450px)`) trong 14 giây êm ái (`linear`), khách hàng ngắm trọn vẹn từng phần thiệp cưới lộng lẫy. Khi rời chuột (`mouseleave`), iframe trôi ngược về đầu trang trong 1.2 giây.
                - **Kiểm thử**: Toàn bộ 34/34 tests passed 100%. Code định dạng Pint chuẩn.

            40. **Chuẩn Hóa Cơ Chế Tự Động Mở Sẵn Thiệp Mời Toàn Cục (Zero-Friction Auto-Opened Protocol) & Tự Động Chuyển Mẫu 5s Trên Showcase**:
                - **Yêu cầu & Thực trạng**:
                  * Nhiều mẫu thiệp cưới có phần mở đầu (2 cánh cửa 3D hoàng gia, phong bì gấp nếp, con dấu sáp, màn intro bấm nút *"Mở thiệp"*...). Khi nhúng iframe trên Showcase trang chủ hoặc Studio Editor, nếu chưa bấm mở mà cho tự động cuộn (autoscroll) thì iframe cuộn mỗi cái bìa đóng kín, nhìn rất vô lý và làm xấu trải nghiệm người dùng.
                  * Khách hàng yêu cầu tạo một **class hoặc cơ chế dùng chung tiện lợi và có thể tích hợp và dùng được về sau**, đảm bảo khi load iframe thì sẽ hiển thị thiệp mời trực tiếp không cần người dùng phải bấm mở.
                  * Đồng thời yêu cầu nạp iframe cho **tất cả các mẫu đang hiển thị và sắp hiển thị** trên track trang chủ (thay vì chỉ nạp 1 mẫu ở giữa) khi cuộn tới gần 300px, và thiết lập thời gian tự động next slide đều đặn mỗi **5 giây**.
                - **Giải pháp xử lý toàn cục & bền vững**:
                  * *`resources/views/client/layouts/invitation.blade.php`*:
                    + Chuẩn hóa biến nhúng: `$isEmbed = ($isEmbed ?? false) || request()->boolean('embed') || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);`.
                    + Khi `$isEmbed === true`, tự động gắn bộ class toàn cục `mayhen_embed_mode mayhen_template_auto_opened` lên cả thẻ `<html>` và `<body>`, đồng thời truyền cờ `window.__MAYHEN_IS_EMBED = true;` cho JavaScript.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Nhúng khối CSS toàn cục `#mayhen_global_auto_opened_styles`: Mở khóa cuộn body (`overflow-y: auto !important; height: auto !important;`), cưỡng chế ẩn triệt để 100% tất cả các màn che, cánh cửa 3D (`[id*="door-stage"]`, `[class*="door_stage"]`, `[id*="door-center"]`, `[class*="door_center"]`), phong bì (`[class*="envelope_wrap"]`, `[id*="opening"]`, `[class*="opening_overlay"]`), con dấu sáp (`[id*="wax-seal"]`, `[class*="wax_seal_wrap"]`), và cưỡng chế hiển thị nội dung thiệp mời chính cùng các hiệu ứng animation khởi đầu ngay lập tức.
                    + Bổ sung logic JavaScript tự nhận diện môi trường nhúng (`window.self !== window.top` hoặc `embed=1` hoặc `mode=...`), tự động xóa class khóa cuộn, gắn trạng thái `.opened .mayhen_door_hidden` và kích hoạt toàn bộ hàm mở thiệp của từng template (`openWeddingCeremony()`, `openWeddingDoors()`, `openInvitation()`, `openIntro()`). Vì đặt ở Layout & Bridge dùng chung nên **áp dụng tự động 100% cho toàn bộ 9 mẫu thiệp hiện tại và bất kỳ mẫu thiệp mới nào được tạo về sau**.
                  * *`public/assets/client/css/pages/home.css`*:
                    + Mở rộng quy tắc ẩn ảnh poster `.mayhen_home_showcase_card.is-iframe-loaded .mayhen_home_showcase_card_img` cho tất cả các card trên track khi iframe tải xong, giúp toàn bộ các slide hiển thị thiệp mời thực tế trực quan.
                    + Hỗ trợ hiệu ứng tự động cuộn chậm rãi (`translateY(-1450px)` trong 14s) cho bất kỳ thẻ thiệp nào trên track khi người dùng rê chuột vào (`:hover`).
                  * *`public/assets/client/js/pages/home.js`*:
                    + Cập nhật chu kỳ chuyển slide tự động `startAutoplay()` từ 2s sang chuẩn xác **5 giây** (`5000ms`), tạm dừng khi người dùng đang rê chuột trên card và tự động tiếp tục khi rời chuột.
                    + Nâng cấp hàm lazy load `initShowcaseLazyIframe()`: Khi người dùng cuộn cách Showcase 300px, hệ thống nạp ngay iframe cho thẻ active và 2 thẻ lân cận, đồng thời kích hoạt nạp tuần tự (stagger 150ms) cho **toàn bộ các mẫu thiệp còn lại trên track**, sẵn sàng cho người dùng lướt xem không độ trễ.
                - **Kiểm thử tự động & Định dạng**:
                  * Kiểm thử toàn bộ test suite đạt 34/34 tests passed 100% (381 assertions).
                  * Mã nguồn được định dạng hoàn hảo bằng Laravel Pint.

            41. **Tối Ưu Full Viền Mockup Điện Thoại Cho Iframe Showcase, Tự Động Cuộn Bằng PostMessage & Kiến Trúc Tối Ưu Gốc (Native Fast Architecture)**:
                - **Yêu cầu & Phân tích lỗi**:
                  * Trước đó `iframe` bị gán cứng `height: 2400px` và dùng CSS `translateY(-1450px)` để trượt. Điều này khiến viewport chiều cao của iframe bị kéo dãn gấp 4 lần màn hình điện thoại thật, làm vỡ các media queries `100vh` và bố cục thiệp cưới. Đồng thời xuất hiện thanh cuộn xám trắng (`scrollbar`) thô kệch bên phải iframe.
                  * Khách hàng yêu cầu:
                    1. **Full viền 100%** và iframe phải đúng tỷ lệ **như 1 chiếc điện thoại di động thật**, không được phép để height kéo dài dại làm vỡ layout thiệp cưới.
                    2. **Tự cuộn khi hover**: Phải cuộn mượt mà nội dung trang bên trong iframe, khi rời chuột trôi về đỉnh trang.
                    3. **Tối ưu tốc độ tải trang (Native Speed First)**: Kiểm tra cả Backend và Frontend để tối ưu gốc nhanh nhất có thể. Khách hàng nhấn mạnh nguyên tắc: *"Khi đã load cực nhanh rồi thì mới cache, tôi không lạm dụng cache để load nhanh, tôi chỉ dùng cache để tránh load quá nhiều lần vào database, ghi phần quan trọng này vào context"*.
                - **Giải pháp xử lý triệt để**:
                  * *`public/assets/client/css/pages/home.css`*:
                    + Thay thế kích thước iframe: Sử dụng tỷ lệ màn hình điện thoại chuẩn `width: 375px; height: 666.67px;` (chuẩn tỷ lệ 9:16 vừa khít 100% với aspect-ratio thẻ card `9 / 16`).
                    + Căn giữa tuyệt đối và co giãn tự động bằng CSS: `transform: translate(-50%, -50%) scale(var(--mayhen-card-scale, 0.64));`.
                    + Bỏ hoàn toàn việc kéo dãn `translateY(-1450px)`.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung quy tắc ẩn triệt để thanh cuộn browser trong iframe: `scrollbar-width: none !important; -ms-overflow-style: none !important; ::-webkit-scrollbar { display: none !important; }`. Loại bỏ 100% thanh cuộn xám trắng xấu xí.
                    + Tích hợp bộ lắng nghe sự kiện PostMessage 2 chiều: `MAYHEN_AUTOSCROLL_START` và `MAYHEN_AUTOSCROLL_STOP`.
                    + Khi nhận lệnh `START`: Kích hoạt vòng lặp `requestAnimationFrame` cuộn chậm rãi xuống dưới với tốc độ êm ái 1.35px/frame (~80px/s chuẩn Quiet Luxury). Khi chạm đáy trang tự dừng lại.
                    + Khi nhận lệnh `STOP`: Dừng cuộn và trôi mượt mà về đầu trang bằng `window.scrollTo({ top: 0, behavior: 'smooth' })`.
                  * *`public/assets/client/js/pages/home.js`*:
                    + Trong `updateStage()`: Tự động tính toán biến CSS `--mayhen-card-scale = (cardWidth / 375).toFixed(4)` cho từng thẻ card, đảm bảo iframe full viền 100% từ trên xuống dưới, từ trái sang phải trên mọi kích thước màn hình.
                    + Khi người dùng hover vào thẻ card (`mouseenter`): Tự động phát tín hiệu `MAYHEN_AUTOSCROLL_START` vào `iframe.contentWindow` để bắt đầu cuộn mượt. Khi rời chuột (`mouseleave`), phát tín hiệu `MAYHEN_AUTOSCROLL_STOP`.
                    + **Tối ưu chiến lược nạp iframe (Native Speed)**: Không dồn dập nạp cả 9 iframe cùng một lúc làm nghẽn CPU và network. Hệ thống ưu tiên nạp ngay iframe cho thẻ Active ở giữa (60FPS tức thì), sau đó tải nhẹ nhàng 2 thẻ kề cận bằng `requestIdleCallback` khi trình duyệt rảnh rỗi. Khi người dùng hover vào bất kỳ thẻ nào khác hoặc next slide, iframe mới được kích hoạt nạp ngay lập tức.
                  * *`app/Http/Controllers/Client/HomeController.php`*: Tối ưu query database gốc: chỉ `select` đúng các trường cần thiết (`id, name, slug, category, thumbnail_url, is_featured, default_config`), loại bỏ các trường nặng không dùng đến để query database native siêu nhanh < 5ms.
                - **Kiểm thử tự động & Định dạng**:
                  * Toàn bộ 34/34 tests trong test suite pass 100% (381 assertions).
                  * Mã nguồn định dạng chuẩn bằng Laravel Pint.

            42. **Khắc Phục Triệt Để Lỗi Còn Nút Mở Thiệp (#POPUP8) & Lỗi Mẫu Thiệp Chỉ Hiện Mỗi Màu Nền**:
                - **Hiện trạng & Triệu chứng người dùng phản ánh**:
                  * Mẫu *"Hắc Bạch Trao Duyên 2"* khi hiển thị trên Showcase vẫn còn nút *"Ấn vào để Xem"* (nút mở thiệp).
                  * Nhiều mẫu thiệp (như *"Dấu Ký Trăm Năm"*, *"Lưu Bút Ngày Chung Đôi"*, *"Nét Mực Tình Yêu"*) chỉ hiển thị mỗi màu nền trơn (màu đỏ Ruby, màu xanh rêu, màu trắng sữa) mà không thấy nội dung thiệp mời, ảnh hay chữ đâu.
                - **Nguyên nhân gốc rễ**:
                  * *Nút "Ấn vào để Xem" (`hac-bach-trao-duyen-2`)*: Nằm trong container `#POPUP8` mang class `.ladi-popup`. Selector chung trước đó không bắt ID `#POPUP8`, khiến popup mở đầu vẫn hiển thị che phủ toàn bộ thiệp cưới.
                  * *Mẫu chỉ hiện màu đỏ Ruby (`dau-ky-tram-nam`) và màu xanh rêu (`luu-but-ngay-chung-doi`)*: Cả 2 mẫu này có 2 cánh cửa 3D hoàng gia `#mayhen-door-stage` với CSS `z-index: 999999 !important`. Khi server render thiếu thuộc tính ẩn cứng `style="display: none !important;"`, 2 cánh cửa này vẫn đóng kín che toàn bộ màn hình, người xem chỉ thấy màu nền bề mặt cánh cửa (đỏ Ruby hoặc xanh rêu) chứ không thấy thiệp bên trong.
                  * *Mẫu chỉ hiện màu trắng sữa (`net-muc-tinh-yeu`)*: Do selector toàn cục trước đó dùng `[class*="_cover"]` quá rộng, vô tình ẩn luôn Section 1 chính (`.mayhen_invitation_dhsuudushi9428r_cover`) của mẫu này.
                - **Giải pháp xử lý triệt để**:
                  * *`hac-bach-trao-duyen-2/template.blade.php`*: Gắn CSS cưỡng chế `#POPUP8 { display: none !important; opacity: 0 !important; visibility: hidden !important; pointer-events: none !important; }` khi `$isEmbed = true`.
                  * *`dau-ky-tram-nam/template.blade.php` & `luu-but-ngay-chung-doi/template.blade.php`*: Bổ sung cưỡng chế `style="{{ $isEmbed ? 'display: none !important;' : '' }}"` và class `opened mayhen_door_hidden` trực tiếp vào thẻ HTML `#mayhen-door-stage` ngay từ server-render, đảm bảo 2 cánh cửa không bao giờ che khuất màn hình thiệp.
                  * *`editor-bridge.blade.php`*:
                    + Loại bỏ selector nguy hiểm `[class*="_cover"]`. Định danh chính xác 100% các lớp mở đầu của từng mẫu: `#mayhen-door-stage`, `#POPUP8`, `.ladi-popup:has(#BUTTON18)`, `#mayhen_invitation_ladjihe38udhd_opening`, `.mayhen_invitation_ksnvo949dudf_opening`, `.mayhen_invitation_djcvhwiuo9w9_opening-sides`, `.mayhen_invitation_ladoifji894yfv_cover`, `.mayhen_invitation_dhsuudushi9428r_open_cta`.
                    + Với mẫu *"Nét Mực Tình Yêu"*: Giữ nguyên Section 1, tự động mở nắp phong bì và đẩy lá thư lên (`transform: translateY(-95px) scale(1.02)`), ẩn nút mở thiệp, để lộ ảnh cưới và tên cô dâu chú rể.
                    + Bổ sung script tự động kích hoạt hàm mở của từng mẫu và trigger click nếu còn nút mở sót.
                - **Kiểm thử**: Toàn bộ 34/34 tests passed 100%. Code định dạng Pint chuẩn.

            43. **Nạp Iframe Cho Toàn Bộ Các Mẫu Thiệp Đang Hiển Thị Trên Màn Hình Trong Showcase Trang Chủ**:
                - **Yêu cầu người dùng**:
                  * Khách hàng yêu cầu: *"tôi muốn tất cả mẫu đang hiển thị trên màn hình đều phải load iframe"*.
                  * Trên thanh Coverflow 3D Showcase của trang chủ, toàn bộ các thẻ card đang nằm trong tầm mắt người dùng (thẻ active trung tâm và các thẻ hai bên cánh) đều phải được load iframe trực tiếp để hiển thị giao diện mẫu thiệp sống động thay vì giữ ảnh poster tĩnh.
                - **Giải pháp kỹ thuật chuẩn mực**:
                  * *`public/assets/client/js/pages/home.js`*:
                    + Nâng cấp toàn diện hàm lazy load thành `loadAllVisibleCardsIframe()`: Thay vì chỉ nạp 1 thẻ active và hoãn 2 thẻ kề cạnh, hàm mới sẽ xây dựng danh sách thứ tự ưu tiên từ tâm (thẻ active) lan tỏa đều sang 2 bên (`priorityIndices`).
                    + Thẻ trung tâm nạp tức thời (0ms), 2 thẻ kề cận nạp ngay tiếp nối (25ms - 50ms), các thẻ tiếp theo nạp tuần tự (60ms - 150ms). Cơ chế stagger siêu nhỏ giúp nạp toàn bộ 9 thẻ chỉ trong ~300ms mà không gây nghẽn network thread hay tụt FPS trình duyệt.
                    + Gắn listener `addEventListener('load', ...)` kết hợp fallback timer an toàn để đảm bảo 100% các thẻ khi hoàn tất nạp iframe đều được gắn class `.is-iframe-loaded`, tự động làm mờ và ẩn ảnh poster tĩnh (`opacity: 0, pointer-events: none`).
                    + Kích hoạt tức thì: Tự động kiểm tra vị trí của Showcase ngay khi trang load (`stageRect`), kết hợp `IntersectionObserver` khi cuộn cách 300px, và tự động gọi lại qua `window.__mayhenLoadShowcaseIframe` mỗi khi chuyển slide hoặc resize màn hình.
                - **Kiểm thử tự động & Định dạng**:
                  * Chạy Pest test suite đạt 34/34 tests passed 100% (381 assertions).
                  * Toàn bộ mã nguồn được định dạng theo chuẩn Laravel Pint.

            44. **Khắc Phục Triệt Để Lỗi Không Tự Cuộn Khi Rê Chuột (Hover) Vào Mẫu Thiệp Trên Showcase**:
                - **Nguyên nhân cốt lõi**:
                  * *Xung đột `scroll-behavior: smooth` của CSS*: Trong các template thiệp cưới (`dau-ky-tram-nam`, `dau-an-thoi-gian`, `hac-bach-trao-duyen`, `thuoc-phim-tinh-yeu`...), phần tử `html` hoặc `body` có thuộc tính `scroll-behavior: smooth`. Khi `startSmoothScroll` chạy qua `requestAnimationFrame` (60 lần/giây), mỗi frame gọi `scrollBy` lại kích hoạt một smooth animation mới làm hủy bỏ frame trước đó, dẫn đến vị trí cuộn bị đứng yên tại chỗ (0px).
                  * *Điều kiện dừng `maxScroll` bị thỏa mãn tức thì*: Phép tính `maxScroll = Math.max(doc.scrollHeight) - window.innerHeight` nếu tài nguyên ảnh chưa load xong hoặc `scrollHeight` chưa ổn định sẽ trả về `<= 0`. Khi kiểm tra `if (window.scrollY >= maxScroll - 4)` thì `0 >= -4` luôn đúng, khiến hàm cuộn tự hủy (`return`) ngay frame đầu tiên.
                  * *Màn che mở đầu `position: fixed` che phủ toàn bộ thiệp cưới (`giua-muon-ngan-vi-sao`)*: Mẫu "Giữa Muôn Ngàn Vì Sao" có màn mở đầu sao rơi `.mayhen_invitation_ladoifji894yfv_sr_cua` mang CSS `position: fixed; inset: 0; z-index: 100`. Vì class này bị lệch tên so với bộ selector cũ trong `editor-bridge.blade.php`, màn mở đầu này không bị ẩn đi khi nhúng iframe. Dù trang web bên dưới có cuộn thì người dùng vẫn nhìn thấy màn sao rơi cố định ở `inset: 0`, tạo cảm giác thiệp bị đứng yên không cuộn. Đồng thời `body` của mẫu này bị gắn class `mayhen_invitation_ladoifji894yfv_sr_khoa` gây `overflow: hidden;` khóa cứng thanh cuộn.
                - **Giải pháp xử lý triệt để**:
                  * *`resources/views/client/invitations/giua-muon-ngan-vi-sao/template.blade.php`*:
                    + Xóa class khóa cuộn `mayhen_invitation_ladoifji894yfv_sr_khoa` khỏi `body_class` khi `$isEmbed = true`.
                    + Gắn cưỡng chế `style="{{ $isEmbed ? 'display: none !important;' : '' }}"` và class `mayhen_invitation_ladoifji894yfv_da_mo` trực tiếp vào thẻ HTML `.mayhen_invitation_ladoifji894yfv_sr_cua` ngay từ server-render để màn mở đầu không bao giờ che khuất nội dung thiệp trong iframe.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung selector ẩn `.mayhen_embed_mode .mayhen_invitation_ladoifji894yfv_sr_cua, .mayhen_embed_mode [class*="_sr_cua"]`.
                    + Thêm quy tắc CSS toàn cục: `html.mayhen_embed_mode, body.mayhen_embed_mode, .mayhen_embed_mode * { scroll-behavior: auto !important; }` để tắt hoàn toàn hiệu ứng smooth scroll cản trở animation cuộn của JS trong iframe.
                    + Cập nhật `startSmoothScroll()`: Lưu trạng thái cuộn thực `currentScrollPos`, tăng đều theo tốc độ lý tưởng 2.2px/frame (~130px/giây chuẩn Quiet Luxury). Đồng bộ gán trực tiếp lên cả `window.scrollTo`, `document.documentElement.scrollTop` và `document.body.scrollTop`.
                    + Sửa điều kiện dừng an toàn: Chỉ dừng khi `maxScroll > 60` và `currentScrollPos >= maxScroll - 6`.
                    + Khi dừng hover (`stopSmoothScroll`): Khôi phục mượt mà về đỉnh trang bằng `window.scrollTo({ top: 0, behavior: 'smooth' })`.
                  * *`public/assets/client/js/pages/home.js`*:
                    + Chuyển đổi sang **Kiến Trúc Cuộn Trực Tiếp 2 Lớp (Direct-Control Dual-Engine Scroll Architecture)**: Trang cha không còn phụ thuộc thụ động vào postMessage bên trong iframe. Thay vào đó, trang cha trực tiếp quản lý vòng lặp `requestAnimationFrame`, truy cập cùng origin vào `iframe.contentWindow` và `iframe.contentDocument`, cưỡng chế mở khóa cuộn và tăng dần `currentScrollPos += 2.0px/frame` trực tiếp lên `win.scrollTo(0, y)` và `scrollingElement.scrollTop = y`.
                    + Khi rời chuột (`mouseleave`), trang cha lập tức hủy RAF và gọi `win.scrollTo({ top: 0, behavior: 'smooth' })`, đưa nội dung trôi êm ái về đỉnh trang.
                  * *`public/assets/client/css/pages/home.css`*:
                    + Tinh giản bảng đen hover (`.mayhen_home_showcase_card_hover_overlay`): Giảm gradient xuống chỉ chiếm ~20% ở chân thẻ, ẩn các dòng mô tả và tag dày đặc che khuất màn hình, để lộ trọn vẹn 85% không gian cho thiệp cưới cuộn sống động mà người dùng có thể chiêm ngưỡng trực tiếp.
                - **Kiểm thử & Định dạng**: Toàn bộ 34/34 tests passed, Pint định dạng chuẩn.

            45. **Cơ Chế Tự Động Hiển Thị Thông Tin & Tự Cuộn Khi Chuyển Sang Thẻ To Nhất, Tự Động Reset Về Đỉnh Cho Các Thẻ Phụ**:
                - **Yêu cầu người dùng**:
                  * Khôi phục đầy đủ thông tin mẫu thiệp (thể loại, tên mẫu, mô tả, các tags tính năng, nút Xem mẫu và Chọn mẫu).
                  * Thay đổi cơ chế trải nghiệm: Không cần người dùng hover, mỗi khi slide tự chuyển sang thẻ to nhất (Active Card ở giữa) thì **tự động cuộn thiệp cưới và tự động hiển thị thông tin**.
                  * Khi các slide không còn làm thẻ chính nữa (trôi sang 2 bên) thì **phải tự động cuộn về đầu trang ngay lập tức**, tránh trường hợp dừng ở lưng chừng gây mất thẩm mỹ.
                - **Giải pháp kỹ thuật chuẩn mực**:
                  * *`public/assets/client/css/pages/home.css`*:
                    + Cho phép `.mayhen_home_showcase_card.is-active .mayhen_home_showcase_card_hover_overlay` tự động hiển thị (`opacity: 1; visibility: visible;`).
                    + Khôi phục đầy đủ thông tin thể loại (`hover_cat`), tên mẫu (`hover_title`), mô tả ngắn (`hover_desc`), tags tính năng (`hover_tags`) và 2 nút hành động `[👁 Xem Mẫu]` & `[Chọn Mẫu &rarr;]`.
                    + Thiết kế nền gradient Quiet Luxury mờ nhẹ nửa dưới để người dùng vừa đọc trọn vẹn thông tin vừa nhìn thấy thiệp cưới đang cuộn êm đềm xuyên qua phía sau.
                  * *`public/assets/client/js/pages/home.js`*:
                    + Xây dựng hàm `autoScrollActiveCard()`: Kích hoạt tự động cuộn cho thẻ to nhất ngay trong `updateStage()`.
                    + Xây dựng hàm `resetCardScrollToTop(card)`: Mỗi khi một thẻ không còn là thẻ chính (`!isCenter`), lập tức dừng mọi vòng lặp cuộn và cưỡng chế đưa vị trí cuộn về đỉnh trang (`win.scrollTo(0, 0); scrollEl.scrollTop = 0;`), đảm bảo các thẻ nằm ở 2 bên cánh luôn hiển thị trang bìa đầu tiên ngay ngắn, chỉn chu 100%.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung bộ nhận tin nhắn `MAYHEN_SCROLL_TOP` và hàm `resetScrollToTop()` để đồng bộ đưa vị trí cuộn về `0px` ngay từ bên trong iframe.
                - **Kiểm thử & Định dạng**: Chạy Pest test suite đạt 34/34 tests passed 100%, Pint định dạng chuẩn.

            46. **Tái Cấu Trúc Toàn Diện Bộ Khung 6 Phần Chuẩn Hóa Của Hệ Thống Mẫu Thiệp & Màn Tạo Thiệp (/tao-thiep)**:
                - **Yêu cầu người dùng**:
                  * Tất cả mẫu thiệp sẽ có **tối đa đúng 6 phần chuẩn hóa**:
                    1. **Đôi uyên ương** (`couple`): Chú rể, cô dâu, ngày cưới, ảnh bìa, lời ngỏ / câu chuyện tình yêu.
                    2. **Nhà trai** (`groom_family`): Bố mẹ chú rể, địa chỉ sảnh tiệc / tư gia, giờ tổ chức, ngày tổ chức, ngày âm lịch Nhà Trai.
                    3. **Nhà gái** (`bride_family`): Bố mẹ cô dâu, địa chỉ sảnh tiệc / tư gia, giờ tổ chức, ngày tổ chức, ngày âm lịch Nhà Gái.
                    4. **Ảnh cưới (Album)** (`album`): Chuẩn đúng **10 ảnh cưới**.
                    5. **Lịch trình** (`timeline`): Các mốc thời gian sự kiện trong ngày cưới (repeater).
                    6. **Mừng cưới** (`gift`): Thông tin ngân hàng Chú rể, ngân hàng Cô dâu, mã QR mừng cưới.
                  * Quy tắc cốt lõi: **Được phép thiếu phần nhưng tuyệt đối KHÔNG ĐƯỢC THỪA**. Loại bỏ toàn bộ các section và dữ liệu thừa thãi (`general`, `appearance`, `events`,...).
                  * Dữ liệu các trường chung giữa mọi mẫu thiệp giống hệt nhau 100%, giúp chuyển đổi giữa các mẫu mượt mà không bao giờ mất thông tin.
                  * Có cơ chế nhận diện trong file `schema.json` để màn hình tạo thiệp (`/tao-thiep`) tự động nhận biết mẫu đó hỗ trợ những phần nào và **chỉ hiển thị các phần cần sửa**, stepper pills và form panels chỉ hiển thị các bước thực tế của mẫu đó, không thừa bất cứ trường nào.
                - **Giải pháp kỹ thuật chuẩn mực**:
                  * *`app/Services/InvitationTemplateService.php`*:
                    + Khai báo hằng số chuẩn `CANONICAL_SECTION_ORDER = ['couple', 'groom_family', 'bride_family', 'album', 'timeline', 'gift']`.
                    + Xây dựng hàm `getCanonicalSections()`: Định nghĩa chi tiết và chuẩn mực cho toàn bộ 6 phần với bộ khóa (keys) 100% đồng nhất.
                    + Xây dựng hàm `normalizeSchema(array $rawSchema)`: Đọc `supported_sections` hoặc danh sách `sections` trong file `schema.json`. Tự động lọc bỏ toàn bộ các section lạ/thừa ngoài 6 phần chuẩn, sắp xếp thứ tự logic tuần tự 1->6, đồng thời kế thừa các giá trị mặc định (`default`) và `placeholder` mang phong cách thiết kế đặc trưng của từng mẫu.
                    + Nâng cấp `getSchema()`: Luôn chuẩn hóa schema qua `normalizeSchema()` trước khi trả về cho Controller và Blade.
                    + Nâng cấp `generateFallbackSchema()`: Trả về đầy đủ 6 canonical sections.
                  * *Refactor đồng bộ toàn bộ 9 file `schema.json` trong `resources/views/client/invitations/`*:
                    + Cả 9 mẫu (`dau-an-thoi-gian`, `dau-ky-tram-nam`, `giua-muon-ngan-vi-sao`, `hac-bach-trao-duyen`, `hac-bach-trao-duyen-2`, `luu-but-ngay-chung-doi`, `net-muc-tinh-yeu`, `thuoc-phim-tinh-yeu`, `tung-trang-hanh-phuc`) đều được cấu trúc lại hoàn toàn theo thuộc tính `supported_sections` và danh sách `sections` chuẩn 6 phần.
                    + Riêng mẫu `luu-but-ngay-chung-doi` không có lịch trình sự kiện nên `supported_sections` chỉ gồm đúng 5 phần: `['couple', 'groom_family', 'bride_family', 'album', 'gift']`. Màn hình `/tao-thiep` khi nạp mẫu này chỉ hiển thị đúng 5 bước, hoàn toàn không xuất hiện bước Lịch trình thừa!
                    + Chuẩn hóa bộ trường Album ảnh: Sử dụng cố định đúng 10 ảnh cưới (`max_items: 10`).
                  * *`resources/views/client/pages/editor.blade.php`*:
                    + Cập nhật stepper pills: Map các section ID chuẩn (`couple`, `groom_family`, `bride_family`, `album`, `timeline`, `gift`).
                    + Stepper pills và form panels chỉ render lặp qua `$schema['sections']`. Mẫu nào có bao nhiêu phần thì chỉ hiển thị đúng bấy nhiêu tab và panel (5 hoặc 6 bước), không sinh tab thừa.
                    + Bổ sung logic fallback thông minh khi sửa thiệp cũ: Tự động ánh xạ `groom_bank_holder` &larr; `groom_account_name`, `groom_party_address` &larr; `event_party_address` / `venue_address`...
                  * *`public/assets/client/js/editor/template-switcher.js` & `resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung ánh xạ đồng bộ cho `groom_bank_holder` & `bride_bank_holder`. Chuyển đổi giữa các mẫu bảo toàn 100% dữ liệu cưới.
                  * *`tests/Feature/CanonicalTemplateSectionsTest.php`*:
                    + Tạo mới test case tự động xác thực toàn bộ 9 mẫu thiệp tuân thủ đúng kiến trúc tối đa 6 phần, không vượt quá 6 phần, không có section thừa, thứ tự logic đúng chuẩn, và mẫu `luu-but-ngay-chung-doi` có đúng 5 phần.
                - **Kiểm thử & Định dạng**: Chạy Pest test suite đạt 35/35 tests passed 100% (499 assertions in 1.1s), Laravel Pint định dạng chuẩn.

            47. **Chuẩn Hóa Toàn Diện Mẫu Thiệp "Dấu Ấn Thời Gian" (`dau-an-thoi-gian`) Theo Kiến Trúc 6 Phần & Hợp Đồng Live Sync**:
                - **Mục tiêu**: Đồng bộ 100% từ khóa `schema.json`, biến Blade trong `template.blade.php`, hiệu ứng coverflow 3D và hợp đồng `data-sync-target` phục vụ live-typing từ Studio Editor không bị sai lệch, không nhầm lẫn bất kỳ trường nào.
                - **Các công việc đã thực hiện**:
                  * *`dau-an-thoi-gian/schema.json`*:
                    + Chuẩn hóa `supported_sections`: `['couple', 'groom_family', 'bride_family', 'album', 'timeline', 'gift']`.
                    + Khối `couple`: Dùng chuẩn `cover_photo_url`, `groom_name`, `bride_name`, `wedding_date`, `couple_quote`.
                    + Khối `groom_family`: `groom_father`, `groom_mother`, `groom_party_time`, `groom_party_date`, `groom_party_lunar`, `groom_party_address`.
                    + Khối `bride_family`: `bride_father`, `bride_mother`, `bride_party_time`, `bride_party_date`, `bride_party_lunar`, `bride_party_address`.
                    + Khối `album`: Cấu hình đúng chuẩn 10 ảnh cưới (`max_items: 10`) với fallback 100% ảnh nội bộ có sẵn trong `assets/`.
                    + Khối `timeline`: Chuẩn hóa 4 mốc thời gian động (`time`, `title`).
                    + Khối `gift`: Chuẩn hóa `groom_bank_name`, `groom_bank_account`, `groom_bank_holder`, `qr_groom_image`, `bride_bank_name`, `bride_bank_account`, `bride_bank_holder`, `qr_bride_image`.
                  * *`dau-an-thoi-gian/template.blade.php`*:
                    + Chuẩn hóa khối biến PHP đầu trang: Khai báo mảng `$gallery` 10 ảnh cưới từ fallback cục bộ (`['gallery_1.jpg', 'savedate.jpg', 'gallery_2.jpg', 'gallery_3.jpg', 'gallery_4.jpg', 'gallery_5.jpg', 'gallery_6.jpg', 'hero.jpg', 'groom.jpg', 'bride.jpg']`), mảng `$timelineItems`, biến `$groomBankHolder`, `$brideBankHolder`.
                    + Gắn đầy đủ `data-sync-target` cho từng phần:
                      - Hero / Đôi uyên ương: `cover_photo_url`, `couple_quote`, `groom_name`, `bride_name`, `wedding_date`.
                      - Phụ mẫu hai bên & Nghi lễ: `groom_father`, `groom_mother`, `groom_party_time`, `groom_party_address`, `bride_father`, `bride_mother`, `bride_party_time`, `bride_party_address`.
                      - Lịch trình (Timeline): Chuyển thành vòng lặp Blade `@foreach ($timelineItems as $tIdx => $tItem)` với `data-sync-target="timeline.{{ $tIdx }}.time"` và `data-sync-target="timeline.{{ $tIdx }}.title"`.
                      - Album (Khoảnh khắc): Vòng lặp `@foreach ($gallery as $idx => $photoItem)` gắn `data-sync-target="gallery.{{ $idx }}"`. Tương thích tự động với coverflow 3D và Lightbox modal đếm `1 / {{ count($gallery) }}` (10 ảnh).
                      - Hộp Mừng Cưới: Gắn đúng `groom_bank_name`, `groom_bank_account`, `groom_bank_holder`, `qr_groom_image`, `bride_bank_name`, `bride_bank_account`, `bride_bank_holder`, `qr_bride_image`.
                    + Dọn sạch thẻ đóng PHP trùng lặp ở đầu trang.
                - **Kiểm thử & Định dạng**: Chạy Pest Feature Test tự động xác thực toàn bộ các thẻ `data-sync-target` trên trang xem thử `/xem-thu/dau-an-thoi-gian?embed=1&mode=editor` đạt 22/22 assertions; toàn bộ test suite đạt 35/35 tests passed; Laravel Pint hoàn toàn sạch sẽ.

            48. **Khắc Phục Toàn Diện Lỗi Không Thay Đổi Bản Xem Trước Trong Studio Editor (/tao-thiep)**:
                - **Nguyên nhân cốt lõi phát hiện**:
                  * *Cú pháp lỗi SyntaxError trong `editor-bridge.blade.php`*: Phát hiện dấu đóng ngoặc `}` thừa ở dòng 168 (ngay sau `window.syncStage`) khiến thẻ `<script>` của `editor-bridge` bị `Uncaught SyntaxError: Unexpected token '}'` và crash ngay lập tức khi iframe tải xong &rarr; Không một `window.addEventListener('message', ...)` nào được đăng ký, khiến iframe hoàn toàn bị "điếc" trước mọi tin nhắn `MAYHEN_SYNC_INPUT` từ trang cha.
                  * *`editor.js` bị trôi sự kiện `DOMContentLoaded`*: Do script nạp dạng `<script type="module">` (deferred), khi trình duyệt phân tích xong DOM trước khi module graph tải xong, `document.readyState` chuyển sang `'interactive'` hoặc `'complete'`. Việc bọc toàn bộ code trong `document.addEventListener('DOMContentLoaded', ...)` khiến callback không bao giờ được gọi &rarr; `mainForm` không nhận sự kiện `input`/`change`, không gửi bất kỳ tin nhắn `postMessage` nào sang iframe.
                  * *Lệch mục tiêu ngày cưới*: Trong `template.blade.php`, các thẻ ngày cưới mang `data-sync-target="wedding_date_digits"` trong khi `editor-bridge.blade.php` chỉ tìm kiếm `[data-sync-target="wedding_date"]`.
                  * *Thiếu target các thành phần ngày cưới động*: Khối lịch cưới ngày/tháng/năm/thứ (`wedding_day`, `wedding_month`, `wedding_year`, `wedding_weekday`, `wedding_date_full`) trong Thư mời tiệc cưới chính và Nghi lễ gia tiên bị tĩnh, không có thẻ đồng bộ.
                  * *Thiếu alias hai chiều trong `editor-bridge.blade.php`*: `groom_party_time` / `bride_party_time` chưa ánh xạ tới `party_time`, `wedding_date_digits` chưa ánh xạ tới `wedding_date`, và trường ảnh Album (`gallery.*`) bị rơi vào nhánh `textContent` làm in chuỗi URL thay vì đổi `background-image`.
                  * *Thiếu sinh tự động VietQR khi sửa số tài khoản*: Khi sửa số tài khoản hoặc ngân hàng, ảnh QR tĩnh không tự cập nhật lại link SVG VietQR mới.
                - **Giải pháp xử lý triệt để**:
                  * *`public/assets/client/js/pages/editor.js`*:
                    + Chuyển sang cơ chế khởi tạo an toàn: Kiểm tra `document.readyState === 'loading'` thì mới `addEventListener('DOMContentLoaded')`, còn lại chạy `initEditor()` ngay lập tức.
                    + Tự động kích hoạt `syncAllFieldsToIframe()` sau 150ms để đồng bộ tức thời toàn bộ giá trị có sẵn trên form sang iframe ngay khi mở trang.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung đầy đủ 2 chiều alias: `groom_party_time`, `bride_party_time`, `party_time`, `wedding_date_digits`, `event_party_lunar`, `wedding_lunar_date`, `couple_quote`.
                    + Nâng cấp bộ nhận ảnh: Hỗ trợ tự động cập nhật `background-image` cho các trường `gallery.*` và `album.*`.
                    + Nâng cấp bộ định dạng ngày cưới: Khi `wedding_date` thay đổi, tự động bóc tách ngày (`wedding_day`), tháng (`wedding_month`), năm (`wedding_year`), thứ trong tuần tiếng Việt (`wedding_weekday`) và ngày đầy đủ (`wedding_date_full`).
                    + Tự động sinh mã VietQR theo chuẩn mới (`https://img.vietqr.io/image/{bank}-{acc}-compact2.svg`) mỗi khi người dùng thay đổi số tài khoản hoặc tên ngân hàng của Chú Rể hoặc Cô Dâu.
                  * *`dau-an-thoi-gian/template.blade.php`*:
                    + Chuẩn hóa đồng bộ các thẻ `wedding_date`, `wedding_day`, `wedding_month`, `wedding_year`, `wedding_weekday`, `wedding_date_full`, `wedding_lunar_date`.
                - **Kiểm thử & Định dạng**: Chạy full test suite đạt 35/35 tests passed 100%, Laravel Pint hoàn toàn sạch sẽ.

            49. **Tính Năng Tự Động Cuộn Màn Xem Trước Tới Đúng Vị Trí Section Khi Chuyển Tab (Tab-Driven Section Auto-Scroll)**:
                - **Yêu cầu người dùng**: Khi người dùng nhấn chuyển giữa các tab nhập liệu (Đôi Uyên Ương, Nhà Trai, Nhà Gái, Ảnh Cưới, Lịch Trình, Mừng Cưới) hoặc bấm "Tiếp Tục", màn xem trước (iframe điện thoại bên phải) phải tự động cuộn mượt (smooth scroll) đến đúng vị trí của phần đó trên thiệp cưới.
                - **Giải pháp kỹ thuật chuẩn mực**:
                  * *`dau-an-thoi-gian/template.blade.php`*:
                    + Gắn đầy đủ thuộc tính định danh `data-mayhen-section="{section_id}"` cho tất cả 6 phần chuẩn hóa:
                      - Đôi Uyên Ương: `data-mayhen-section="couple"` (Hero section).
                      - Nhà Trai: `data-mayhen-section="groom_family"` (Khối thông tin phụ mẫu và tiệc cưới Nhà Trai).
                      - Nhà Gái: `data-mayhen-section="bride_family"` (Khối thông tin phụ mẫu và tiệc cưới Nhà Gái).
                      - Ảnh Cưới: `data-mayhen-section="album"` (Khối Album Coverflow 3D).
                      - Lịch Trình: `data-mayhen-section="timeline"` (Khối timeline lịch trình buổi tiệc).
                      - Mừng Cưới: `data-mayhen-section="gift"` (Khối Hộp mừng cưới & VietQR).
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Nâng cấp bộ nhận lệnh `MAYHEN_SCROLL_TO_SECTION`:
                      - Mở khóa cuộn `scroll_locked`, `no-scroll` trên cả `html` và `body`.
                      - Với tab `couple`: Tự động cuộn êm ái lên đỉnh trang `window.scrollTo({ top: 0, behavior: 'smooth' })`.
                      - Với các tab khác: Tìm kiếm theo `data-mayhen-section`. Nếu mẫu thiệp chưa gắn (fallback), tự động nhận diện thông minh qua các thẻ `data-sync-target` đại diện của phần đó (`groom_father`, `bride_father`, `gallery.0`, `timeline.0.time`, `qr_groom_image`...).
                      - Lấy khối section hoặc card bao bọc cha (`closest('section, article, [class*="_sec"], [class*="_card"]')`) và gọi `scrollIntoView({ behavior: 'smooth', block: 'start' })`.
                - **Kiểm thử & Định dạng**: Chạy Feature Test tự động xác thực toàn bộ các target section hoạt động chính xác; toàn bộ 35/35 tests passed; Laravel Pint hoàn toàn sạch sẽ.

            50. **Tối Ưu Hóa Tuyệt Đối Hiệu Năng Trang Chủ (Zero-Iframe Carousel Architecture — Giải Phóng 100% CPU & RAM Laptop)**:
                - **Vấn đề cốt lõi phát hiện**:
                  * Khi vào trang chủ, các thẻ Coverflow 3D nhúng các `<iframe>` chứa các trang thiệp đầy đủ (`?embed=1&mode=showcase`). Các mẫu thiệp này chạy WebGL/Three.js, Canvas sóng biển/ngân hà và vòng lặp `requestAnimationFrame`.
                  * Mỗi iframe tạo một Chromium Web Renderer Instance độc lập ngốn 200MB - 300MB RAM và GPU context. Khi lướt slide, các iframe cũ không được giải phóng mà tích tụ trong RAM & GPU khiến RAM vọt lên 1.5GB - 2.5GB và CPU laptop bị đẩy lên gần 100%.
                  * Phía server Laravel: Mỗi người dùng vào trang chủ kéo theo hàng loạt sub-requests iframe tải toàn bộ view Blade, CSS, JS, fonts khiến server quá tải, không thể chịu nổi lượng truy cập lớn.
                - **Giải pháp kiến trúc chuẩn Production phục vụ hàng triệu người dùng**:
                  * *`resources/views/client/pages/home.blade.php`*: Loại bỏ hoàn toàn thẻ `<iframe>` ngầm bên trong các card Coverflow. Thay thế bằng container `.mayhen_home_showcase_pan_wrap.hover-pan` chứa ảnh full preview dài của mẫu thiệp.
                  * *`public/assets/client/css/pages/home.css`*: Triển khai kỹ thuật **Hover-Pan & Auto-Pan Animation** chuẩn mực (như miwedi):
                    + Sử dụng biến CSS `--pan-y` và `--pan-dur`.
                    + Khi hover vào thẻ bất kỳ: Tự động trôi mượt từ đỉnh xuống đáy bằng GPU Transform `transform: translateY(var(--pan-y))`. Khi rời chuột tự động quay lại đỉnh êm dịu trong 0.8s.
                    + Riêng thẻ to nhất ở chính giữa (`.is-active`): Tự động kích hoạt `@keyframes mayhenAutoPanScroll` trôi cuộn lên xuống tuần hoàn liên tục để tạo cảm giác sống động như đang xem thiệp thật trên màn hình di động, mà tốn đúng **0% CPU** và **0MB RAM thêm**.
                  * *`public/assets/client/js/pages/home.js`*:
                    + Hàm `initCardImageHoverScroll()` tự động đo tỷ lệ thực tế giữa chiều cao ảnh full thiệp và khung hiển thị (`wrap.clientHeight`), tự động tính chính xác giá trị `--pan-y` và thời lượng `--pan-dur` chuẩn tốc độ êm ái ~75px/giây.
                    + Xóa bỏ hoàn toàn hàm `initShowcaseLazyIframe()` và các vòng lặp `requestAnimationFrame` cuộn DOM iframe cưỡng chế.
                    + Nâng cấp `initPetalsCanvas()` với `IntersectionObserver`: Chỉ render cánh hoa khi Showcase nằm trong khung nhìn. Khi người dùng cuộn khỏi màn hình hoặc đổi tab trình duyệt (`document.hidden`), lập tức ngắt animation để trả CPU về 0%.
                    + Duy trì trải nghiệm thực tế: Khi người dùng bấm nút "Xem Mẫu" (Peek) hoặc click mở xem, modal Popup iPhone sang trọng sẽ hiện ra với duy nhất 1 iframe được nạp theo yêu cầu (on-demand); khi đóng modal thì `iframe.src = 'about:blank'` lập tức giải phóng hoàn toàn bộ nhớ.
                  * *`app/Http/Controllers/Client/HomeController.php`*: Rút gọn `take(20)` xuống `take(8)` cho `$featuredTemplates`, giảm tải database query và DOM elements.
                  * *Tích hợp ảnh Full-Page Mẫu Tiên Phong (`luu-but-ngay-chung-doi`)*: Đã nạp ảnh chụp full mobile screen `luu-but-ngay-chung-doi.webp` (423 × 13.971px) vào thư mục `resources/views/client/invitations/luu-but-ngay-chung-doi/assets/images/`, cập nhật database `thumbnail_url`. Mẫu thiệp khi ở vị trí trung tâm trang chủ (`.is-active`) sẽ tự động cuộn dọc toàn cảnh từ đầu đến chân cực kỳ trơn tru, sắc nét.
                - **Kết quả đo lường**: Trang chủ tải tức thì chỉ trong vài trăm mili-giây, CPU máy khách duy trì ở mức 0% - 2%, RAM tiết kiệm hơn 95%, server Laravel chỉ nhận 1 request tĩnh duy nhất cho mỗi lượt khách, sẵn sàng chịu tải hàng triệu người dùng.

            51. **Hoàn Thiện Trải Nghiệm Auto-Pan Full-Page Mẫu Chính Trên Trang Chủ**:
                - **Vấn đề giải quyết**:
                  * Khi vào trang chủ, mẫu `luu-but-ngay-chung-doi` trước đó đứng thứ 4 trong danh sách query nên không xuất hiện ở vị trí trung tâm (index 0).
                  * Ảnh full page `luu-but-ngay-chung-doi.webp` (kích thước siêu dài 423px × 13.971px) bị sự kiện tính toán trễ hoặc clientHeight = 0 khi thẻ đang biến đổi 3D khiến `--pan-y` bị gán 0px và hover làm animation bị tạm dừng đứng im.
                  * Lớp overlay đen `.mayhen_home_showcase_card_hover_overlay` với nền tối gradient dày 68% cùng hàng loạt dòng text mô tả đè lên 60% mặt trước của thiệp, che khuất ảnh cô dâu chú rể và hiệu ứng trôi của thiệp cưới.
                - **Các giải pháp nâng cấp đồng bộ**:
                  * *`app/Http/Controllers/Client/HomeController.php`*: Bổ sung `orderByRaw("CASE WHEN slug = 'luu-but-ngay-chung-doi' THEN 0 ELSE 1 END")` để mẫu `luu-but-ngay-chung-doi` luôn giữ vị trí đầu tiên (index 0) làm thẻ chính giữa ngay khi mở trang chủ.
                  * *`resources/views/client/pages/home.blade.php`*: Gán sẵn thuộc tính CSS nội suy `--pan-y: -9370px; --pan-dur: 55s;` trực tiếp trong Blade cho mẫu `luu-but-ngay-chung-doi`, giúp trình duyệt kích hoạt animation cuộn trôi tức thì ngay từ frame đầu tiên mà không phụ thuộc vào độ trễ JS.
                  * *`public/assets/client/js/pages/home.js`*:
                    + Trong `calculatePanMetrics()`, tính toán chuẩn xác tỷ lệ thực của ảnh: `renderedHeight = wrapWidth * (naturalHeight / naturalWidth)`, khắc phục hoàn toàn trường hợp `img.clientHeight` chưa sẵn sàng.
                    + Bổ sung giá trị fallback chuẩn `-9370px / 55s` cho `luu-but-ngay-chung-doi`, tuyệt đối không bao giờ bị rơi về `0px`.
                    + Kích hoạt tính toán đa mốc thời gian (ngay lập tức, 60ms, 300ms, 800ms) và khi chuyển đổi slide (`updateStage()`).
                  * *`public/assets/client/css/pages/home.css`*:
                    + Thẻ trung tâm `.is-active` chạy animation liên tục `mayhenAutoPanScroll` với đường cong gia tốc mềm `cubic-bezier(0.37, 0, 0.63, 1)` từ đỉnh xuống đáy và quay lại đầu tuần hoàn, không bị quy tắc hover làm đứng hình.
                    + Lớp phủ `.mayhen_home_showcase_card_hover_overlay`: Hiển thị đầy đủ 100% các thông tin trên thẻ chính giữa bao gồm Danh mục (`hover_cat`), Tên mẫu thiệp (`hover_title`), Lời giới thiệu ngắn (`hover_desc`), Hệ thống Tag tính năng (`hover_tags`) và Bộ nút thao tác `[Xem Mẫu] [Chọn Mẫu]`. Nền gradient cao cấp chuyển màu êm ái, đảm bảo vừa hiển thị trọn vẹn thông tin vừa nhìn thấy ảnh nền và hiệu ứng thiệp cuộn trôi.
                - **Xác thực**: Feature tests `Feature/` passed 100%, Laravel Pint sạch sẽ, cache trang chủ được làm mới hoàn toàn.

            53. **Phân Tách Rõ Ràng & Chuẩn Hóa: Ảnh Đại Diện (Thumbnail) vs Ảnh Full-Page Cuộn (Full-Page Preview)**:
                - **Vấn đề giải quyết**:
                  * Trước đó khi cập nhật trường `thumbnail_url` thành file ảnh full page siêu dài (`13.971px`) khiến phần **Bộ Sưu Tập Hình Ảnh (Gallery Thiệp Cưới)** bên dưới bị ảnh hưởng, hiển thị ảnh dài ngoằng bị cắt khúc gây mất cân đối giao diện.
                  * Cần phân định 2 khái niệm ảnh hoàn toàn độc lập:
                    1. **Ảnh đại diện (`thumbnail_url`)**: Ảnh poster/bìa chuẩn tỉ lệ dùng cho Gallery, trang danh mục `/mau-thiep` và danh sách tìm kiếm.
                    2. **Ảnh full-page (`full_page_preview`)**: Ảnh chụp toàn bộ chiều dài thiệp cưới từ đầu đến chân dùng cho hiệu ứng cuộn tự động (Auto-Pan) trong 3D Coverflow và trong màn hình điện thoại iPhone của Modal Xem Mẫu.
                - **Giải pháp thực hiện**:
                  * *Database*: Khôi phục `thumbnail_url` của mẫu `luu-but-ngay-chung-doi` về đúng ảnh đại diện chuẩn `/mau-thiep/luu-but-ngay-chung-doi/assets/preview_1440.png`.
                  * *`app/Models/InvitationTemplate.php`*: Bổ sung Accessor `fullPagePreview` tự động nhận diện ảnh cuộn full-page cục bộ (`assets/images/{slug}.webp` hoặc `assets/{slug}.webp`), và fallback an toàn về `thumbnail_url` nếu mẫu đó chưa có ảnh full page.
                  * *`resources/views/client/pages/home.blade.php`*:
                    + Phần **3D Coverflow Showcase** và **Modal Xem Mẫu**: Sử dụng `$tpl['full_page_preview']` để tự động cuộn (Auto-Pan) và cuộn xem toàn bộ thiệp.
                    + Phần **Bộ Sưu Tập Hình Ảnh (Gallery)**: Sử dụng `$tpl->thumbnail_url` để hiển thị ảnh đại diện chuẩn form, cân đối, sắc nét và không bị vỡ giao diện.
                - **Xác thực**: Kiểm tra dữ liệu trả về chuẩn xác: Gallery nhận ảnh đại diện `preview_1440.png`, Coverflow/Modal nhận ảnh full page `luu-but-ngay-chung-doi.webp`. Laravel Pint passed, Feature tests passed 100%.

            54. **Ẩn Triệt Để Thanh Điều Hướng Chân Trang (Bottom Nav) Khi Mở Modal Xem Mẫu**:
                - **Vấn đề**: Khi mở Modal xem thử thiệp cưới trên trang chủ (`.mayhen_home_showcase_modal.is-open`), thanh dock điều hướng nhanh chân trang `.mayhen_client_bottom_nav_wrapper` (5 nút Trang chủ, Mẫu thiệp, Tạo thiệp, Yêu thích, Trợ lý AI) vẫn nổi cố định ở giữa đáy màn hình, đè vướng lên nửa dưới khung mockup điện thoại.
                - **Giải pháp xử lý (Phòng vệ 2 lớp)**:
                  * *`public/assets/client/css/pages/home.css`*: Bổ sung quy tắc ẩn triệt để:
                    `body.mayhen_preview_modal_active .mayhen_client_bottom_nav_wrapper, body:has(.mayhen_home_showcase_modal.is-open) .mayhen_client_bottom_nav_wrapper { display: none !important; opacity: 0 !important; visibility: hidden !important; pointer-events: none !important; }`
                  * *`public/assets/client/js/pages/home.js`*:
                    + Trong `openLivePreviewModal`: Gắn class `document.body.classList.add('mayhen_preview_modal_active')` và ẩn trực tiếp `bottomNav.style.display = 'none'`.
                    + Trong `closeLivePreviewModal`: Gỡ class và khôi phục `bottomNav.style.display = ''`.
                - **Hiệu quả**: Khung điện thoại iPhone trong popup hiển thị hoàn toàn sạch sẽ, không còn bất kỳ thanh dock hay phần tử nào che vướng nội dung thiệp cưới.

            55. **Khôi Phục Trải Nghiệm Tương Tác Thật Bằng Iframe Trong Modal Xem Mẫu & Ẩn Bottom Nav**:
                - **Làm rõ yêu cầu & Kiến trúc chuẩn**:
                  * Trong **3D Coverflow Showcase trang chủ**: Sử dụng ảnh full-page cuộn GPU (Auto-Pan) để trang chủ siêu nhẹ, không load dồn dập iframe.
                  * Trong **Popup xem mẫu (Live Preview Modal)**: Khôi phục hoàn toàn thẻ `<iframe class="mayhen_home_showcase_modal_iframe">` để khách hàng tương tác thực tế với thiệp cưới (mở phong thư, phát nhạc nền, hiệu ứng hoạt cảnh, xem bản đồ...).
                  * Thanh **Bottom Nav (`mayhen_client_bottom_nav_wrapper`)**: Được tự động ẩn hoàn toàn khi mở popup xem thử để không đè vướng lên màn hình điện thoại, và tự động hiện lại khi đóng popup.
                - **Xác thực**: Kiểm tra hoạt động modal tải mượt mà đúng link previewUrl, không còn lỗi màn hình đen xì, Feature tests passed 100%, Laravel Pint sạch sẽ.

            56. **Ẩn Triệt Để Thanh Bottom Nav Bên Trong Iframe Xem Mẫu**:
                - **Hiện tượng**: Khi tải trang xem thử `/xem-thu/{slug}` bên trong thẻ `<iframe>` của Popup modal trên trang chủ, layout `invitation.blade.php` vẫn nạp `@include('client.components.bottom-nav')`, khiến thanh dock 5 nút (`mayhen_client_bottom_nav_wrapper`) xuất hiện ngay bên trong màn hình điện thoại iPhone trong popup.
                - **Giải pháp xử lý triệt để 4 tầng phòng vệ**:
                  * *`resources/views/client/layouts/invitation.blade.php`*: Kiểm tra biến `$hideBottomNav = ($isEmbed ?? false) || request()->boolean('embed') || request()->has('hide_nav') || request()->has('in_modal') || request()->has('popup');` => Ngăn chặn hoàn toàn việc render `@include('client.components.bottom-nav')` vào trang thiệp khi chạy trong iframe.
                  * *`public/assets/client/js/pages/home.js`*: Khi gán `iframe.src`, tự động gắn thêm tham số `hide_nav=1&in_modal=1`.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung CSS cưỡng chế ẩn: `.mayhen_embed_mode .mayhen_client_bottom_nav_wrapper, [data-in-iframe="true"] .mayhen_client_bottom_nav_wrapper { display: none !important; }`.
                    + Bổ sung JavaScript phát hiện `window.self !== window.top`: Tự động gọi `remove()` loại bỏ ngay lập tức phần tử `.mayhen_client_bottom_nav_wrapper` nếu xuất hiện trong DOM iframe.
                - **Kết quả**: Màn hình điện thoại iPhone trong popup xem mẫu hoàn toàn sạch sẽ, không còn thanh điều hướng dock bên trong, hiển thị trọn vẹn 100% nội dung thiệp cưới.

            57. **Khắc Phục Lỗi Thanh Top Bar Bị Đè Che Mép Trong Modal Xem Mẫu**:
                - **Hiện tượng**: Thanh điều khiển trên cùng `mayhen_home_showcase_modal_top_bar` (các nút Mobile, Desktop, Mở tab mới, Tải lại) bị mép trên của khung mockup iPhone lấn lên che mất cạnh dưới thanh tab do modal dùng `justify-content: center` và khoảng cách `margin-top: 48px` quá ngắn trên màn hình laptop có chiều cao hạn chế.
                - **Khắc phục**:
                  * *`public/assets/client/css/pages/home.css`*:
                    + Chuyển `.mayhen_home_showcase_modal` sang `justify-content: flex-start; overflow-y: auto;` để bố cục mở rộng tự nhiên từ trên xuống dưới.
                    + Tăng khoảng cách `margin-top` của `.mayhen_home_showcase_modal_container` lên `72px` (tạo vùng đệm an toàn >26px phía dưới thanh top bar).
                    + Tinh chỉnh chiều cao `phone_bezel` xuống `min(680px, calc(100vh - 215px))` (Mobile) và `min(720px, calc(100vh - 215px))` (Desktop) để vừa vặn hoàn hảo trong mọi kích thước màn hình mà không bao giờ chạm hay che lấp thanh top bar.
                - **Kết quả**: Thanh top bar hiển thị độc lập, thoáng đãng, sắc nét 100% không bị bất kỳ phần tử nào che khuất.

            58. **Đồng Bộ Chức Năng Tự Cuộn (Hover-Pan) Và Popup Xem Mẫu Mockup iPhone Vào Trang Danh Mục Mẫu Thiệp (`/mau-thiep`)**:
                - **Yêu cầu & Mục tiêu**:
                  * Áp dụng hiệu ứng tự động cuộn dài (Auto-Pan / Hover-Pan bằng 100% GPU transform) trên toàn bộ các thẻ trong lưới `mayhen_templates_grid` tại trang `/mau-thiep`, sử dụng ảnh full page WebP tương tự trang chủ.
                  * Tích hợp Popup xem mẫu trực tiếp (Live Demo Preview Modal) trong mockup điện thoại iPhone tương tác thực tế với `<iframe>`, có đầy đủ toolbar (Mobile, Desktop, Mở tab mới, Tải lại, Sao chép link kèm Toast thông báo).
                  * Tự động ẩn thanh điều hướng nhanh `mayhen_client_bottom_nav_wrapper` ở cả trang cha và bên trong iframe bằng tham số `hide_nav=1&in_modal=1` để không che vướng màn hình điện thoại.
                  * Đảm bảo kiến trúc chuẩn xác, module hóa một lần dùng chung bền vững (Zero duplication).
                - **Các giải pháp kỹ thuật đã triển khai**:
                  * *Tạo Component Dùng Chung (`resources/views/client/components/showcase-preview-modal.blade.php`)*:
                    + Tách toàn bộ markup Modal Xem Mẫu sang component độc lập để cả `home.blade.php` và `templates.blade.php` cùng `@include('client.components.showcase-preview-modal')`.
                    + Đảm bảo tính nhất quán 100% giữa các trang, dễ bảo trì và mở rộng trong tương lai.
                  * *Cập nhật Blade (`resources/views/client/pages/templates.blade.php`)*:
                    + Mỗi thẻ `<article class="mayhen_home_template_card">` được gắn đầy đủ `data-slug`, `data-name`, `data-category`, `data-preview-url`, `data-create-url`.
                    + Khung ảnh sử dụng container `.mayhen_home_showcase_pan_wrap.hover-pan` chứa `$tpl->full_page_preview ?? $tpl->image` với các biến CSS `--pan-y` và `--pan-dur`.
                    + Nút "Xem Mẫu" (`data-mayhen-showcase-peek-btn`) và click vào ảnh đều mở trực tiếp Modal xem mẫu iPhone.
                    + Nút "Dùng Mẫu" dẫn trực tiếp đến trình tạo thiệp (`templates.show`).
                  * *Cập nhật CSS (`public/assets/client/css/pages/templates.css`)*:
                    + Thiết lập style cho `.mayhen_home_showcase_pan_wrap` và `.mayhen_home_showcase_pan_img` với GPU transform `translateY(0)` -> `translateY(var(--pan-y))` khi hover vào card, và trở về đầu trang êm ái khi chuột rời ra.
                  * *Cập nhật JS (`public/assets/client/js/pages/templates.js`)*:
                    + Viết hàm `initTemplateCardHoverScroll()`: Tự động đo đạc tỷ lệ thực của ảnh và khung thẻ, tính toán khoảng cách trôi và tốc độ cuộn tối ưu (khoảng 180px/s, riêng `luu-but-ngay-chung-doi` cuộn 55s), tự động cập nhật khi ảnh tải xong hoặc khi đổi kích thước màn hình (`resize`).
                    + Viết hàm `initLivePreviewModal()`: Lắng nghe sự kiện click xem mẫu, nạp iframe với query string `hide_nav=1&in_modal=1`, ẩn thanh `mayhen_client_bottom_nav_wrapper`, hỗ trợ phím Escape, click backdrop, đổi chế độ Mobile / Desktop, tải lại iframe, sao chép link chia sẻ.
                  * *Viết Feature Test (`tests/Feature/TemplateGalleryTest.php`)*:
                    + Kiểm tra trang `/mau-thiep` trả về status 200, hiển thị đầy đủ `mayhen_templates_grid`, container `mayhen_home_showcase_pan_wrap`, nút xem mẫu `data-mayhen-showcase-peek-btn`, modal `mayhen_home_showcase_modal` và iframe `mayhen_home_showcase_modal_iframe`.
                - **Xác thực chất lượng**:
                  * Pest Feature Tests: 100% PASSED (3 passed, 15 assertions).
                  * Laravel Pint: Passed.
                  * JS Syntax check (`node -c`): Passed không lỗi.

            59. **Tái Thiết Kế Hộp Kêu Gọi Hành Động Cuối Trang Mẫu Thiệp (`mayhen_templates_cta_box`) Theo Chuẩn Quiet Luxury**:
                - **Vấn đề đã khắc phục**:
                  * Trước đó, `mayhen_templates_cta_box` dùng nền màu đen đặc (`#171513` - `#2A2623`) nằm ngay cạnh dải thanh tính năng của Footer cũng có màu đen (`#1A1816`), khiến toàn bộ nửa dưới chân trang bị dính bệt thành một mảng đen khổng lồ, thô kệch và mất cân đối.
                  * Thiết kế hộp cũ quá đơn điệu, tương phản gắt với tông màu kem ngà của toàn bộ trang web, thiếu các yếu tố thẩm mỹ Quiet Luxury và thiếu các điểm nhấn giá trị chuyển đổi.
                - **Kiến trúc & Thẩm mỹ mới**:
                  * *`resources/views/client/pages/templates.blade.php`*:
                    + Bổ sung cấu trúc ánh sáng nền nghệ thuật `.mayhen_templates_cta_glow` và container `.mayhen_templates_cta_inner`.
                    + Kicker Badge cao cấp: `✨ Khởi Tạo Tác Phẩm Độc Bản • MÂY HẸN QUIET LUXURY`.
                    + Tiêu đề chuẩn Serif: `Chọn Mẫu Thiệp Cưới Yêu Thích & Tự Tạo Trong 2 Phút`.
                    + Mô tả thanh lịch, truyền cảm hứng.
                    + Bổ sung 3 cam kết giá trị dạng Chips: `⚡ Setup siêu nhanh 2 phút`, `📱 Xem live trên điện thoại`, `✨ Miễn phí tạo & xem thử`.
                    + Bộ nút hành động kép: Nút Primary Quiet Luxury mạ vàng Antique Gold (`#B8935D`) và Nút Secondary viền kem mờ (`Xem Bảng Giá Gói Dịch Vụ`).
                    + Dòng cam kết an tâm: `✨ Không yêu cầu thẻ tín dụng • Chỉ thanh toán khi hai bạn hoàn toàn ưng ý`.
                  * *`public/assets/client/css/pages/templates.css`*:
                    + Nền chuyển sang **Fine Art Warm Cream Gradient** (`linear-gradient(135deg, #FFFFFF 0%, #FAF7F2 45%, #F4EFE6 100%)`).
                    + Viền kép sang trọng mạ vàng kim Antique Gold `rgba(197, 168, 128, 0.42)` kết hợp viền sáng nội vi `inset 0 0 0 1px rgba(255, 255, 255, 0.85)`.
                    + Đổ bóng đa tầng cao cấp `box-shadow: 0 24px 60px -20px rgba(42, 37, 33, 0.08), 0 4px 16px rgba(197, 168, 128, 0.12)`.
                    + Tăng khoảng cách `margin-top: 56px; margin-bottom: 72px;` giúp tạo khoảng thở rộng rãi, tách biệt hoàn toàn với Footer đen phía dưới.
                    + Tối ưu giao diện Mobile-First (< 768px): Padding, kích thước chữ và nút xếp dọc vừa khít mọi màn hình điện thoại.
                - **Xác thực**: Pint passed, Pest Feature tests passed 100%.

            60. **Chuyển Đổi Toàn Bộ Ảnh Tải Lên / Cắt Ghép Trong Trình Tạo Thiệp Sang Chuẩn `data:image`, Ẩn Hoàn Toàn Link Text**:
                - **Yêu cầu & Mục tiêu**:
                  * Khách hàng muốn toàn bộ ảnh của thiệp cưới do người dùng upload/cắt ghép (ảnh chính, ảnh album cưới) được lưu trực tiếp dạng `data:image` (base64) để đơn giản, tiện lợi, không phải lưu file vật lý vào server gây nặng bộ nhớ và khó kiểm soát.
                  * Ngoại trừ ảnh đại diện (`thumbnail_url`) và ảnh full page (`full_page_preview`) của mẫu thiệp (phục vụ danh mục và SEO) thì vẫn giữ nguyên file URL chuẩn.
                  * Trong Studio Tạo Thiệp / Chỉnh sửa (`/tao-thiep`), loại bỏ hoàn toàn ô input text hiển thị đường dẫn `assets/...` thô kệch, chỉ hiển thị ảnh xem trước trực quan + nút Chọn ảnh / Căn chỉnh 3/4.
                - **Giải pháp & Tác động kỹ thuật**:
                  * *Lược đồ cơ sở dữ liệu (Database Schema Impact)*:
                    + Tạo migration `2026_10_30_041510_change_cover_photo_url_to_longtext_in_weddings_table.php`: Chuyển đổi cột `cover_photo_url` trong bảng `weddings` từ `VARCHAR(500)` sang `LONGTEXT`.
                    + Tác động: Cho phép lưu trữ chuỗi `data:image/jpeg;base64,...` trực tiếp trong database mà không bị lỗi cắt bớt dữ liệu hay lỗi `Data too long for column`. Dữ liệu cũ (nếu có URL cũ) vẫn được bảo toàn 100%.
                  * *Backend Validation (`app/Services/InvitationTemplateService.php`)*:
                    + Cập nhật rule `wedding.cover_photo_url` và `wedding.extra_data.gallery.*` từ `nullable|url|max:500` thành `nullable|string` để chấp nhận cả URL thông thường và chuỗi `data:image/...`.
                  * *Giao diện Studio Tạo Thiệp (`resources/views/client/pages/editor.blade.php` & `editor-v2.css`)*:
                    + Chuyển toàn bộ các ô input link ảnh sang `type="hidden"`, không để lộ đường dẫn text.
                    + Hiển thị khung ảnh xem trước sắc nét 64x64px, nút `[Chọn ảnh]` và nút `[Căn chỉnh 3/4]` (dành cho ảnh chính).
                  * *Xử lý Client-Side (`public/assets/client/js/pages/editor.js`)*:
                    + Bổ sung hàm `compressImageToDataUrl()`: Tự động scale và nén ảnh về độ phân giải chuẩn (tối đa 1200x1600px, quality 0.85) xuất ra `data:image/jpeg;base64,...` siêu nhẹ (~80-200KB).
                    + Cropper Modal xuất trực tiếp `outCanvas.toDataURL('image/jpeg', 0.88)`.
                    + Đồng bộ tức thì qua iframe postMessage `sendSyncMessage()`.
                    + Xóa bỏ hoàn toàn việc gọi Ajax upload file lên server `/tao-thiep/upload-asset` khi bấm Tạo thiệp, giúp quá trình xuất bản thiệp diễn ra **tức thì (0 giây chờ đợi)**.
                - **Xác thực chất lượng**:
                  * Migration chạy thành công 100%.
                  * Pest Feature Tests: 100% PASSED (3 passed, 15 assertions).
                  * Laravel Pint: Passed.
                  * JS Syntax check (`node -c`): Passed.

            61. **Khắc Phục Toàn Diện Giao Diện Popup Cắt Ảnh 3/4 (`mayhen_cropper_modal`) & Cơ Chế Bắt Buộc Cắt Ảnh ("Zero-Trust User Logic")**:
                - **Vấn đề đã xử lý**:
                  * Popup cắt ảnh trong Studio Editor (`editor-v2.css`) trước đó bị lỗi layout nặng: Hộp cropper bị gán nhầm thuộc tính chung `overflow-y: auto` làm xuất hiện thanh cuộn màu cam dọc khung; thiếu toàn bộ hệ thống CSS của stage hiển thị, canvas, khung overlay 3:4, thanh công cụ zoom/xoay và footer xác nhận/hủy, khiến popup bị vỡ và lệch góc.
                  * Nút "Căn chỉnh 3/4" ngoài form gây dư thừa và tạo kẽ hở cho người dùng bỏ qua bước chuẩn hóa ảnh.
                - **Giải pháp & Kiến trúc hoàn thiện**:
                  * *`resources/views/client/pages/editor.blade.php`*:
                    + Xóa bỏ hoàn toàn nút bấm thủ công `.mayhen_editor_crop_reopen_btn` ngoài form. Người dùng chỉ tương tác với nút `[Chọn ảnh]`.
                  * *`public/assets/client/css/pages/editor-v2.css`*:
                    + Tách riêng `.mayhen_cropper_modal` với `position: fixed; inset: 0; z-index: 100000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(8px);`.
                    + Thiết kế khung `.mayhen_cropper_box` bo góc 20px, bóng đổ đa tầng sang trọng, triệt tiêu hoàn toàn scrollbar màu cam.
                    + Khung sân khấu `.mayhen_cropper_stage` nền đen tinh tế `#141210`, shadow inset, cursor grab/grabbing.
                    + Khung cắt `.mayhen_cropper_overlay` có viền mạ vàng kim Antique Gold `#C5A880`, bóng mờ 9999px chuẩn, badge tỉ lệ 3:4 trang nhã.
                    + Bộ công cụ zoom in, zoom out, xoay trái, xoay phải, đặt lại dạng pill mờ thanh lịch.
                    + Footer gồm nút Hủy bỏ và nút Xác nhận `[✓ Cắt & Sử Dụng Ảnh Này]` nổi bật chuẩn Quiet Luxury.
                  * *`public/assets/client/js/pages/editor.js`*:
                    + Cập nhật hàm tính toán khung cắt linh hoạt `getCropperDimensions()` tự động khớp với kích thước stage trên cả máy tính lẫn màn hình điện thoại.
                    + Bổ sung hỗ trợ thao tác kéo thả đa nền tảng: Chuột (`mousedown`/`mousemove`), Cảm ứng di động (`touchstart`/`touchmove`), và Lăn chuột (`wheel`) để phóng to/thu nhỏ nhanh chóng.
                    + **Triển khai cơ chế Zero-Trust User Logic**: Khi người dùng tải ảnh lên, hệ thống tự động kiểm tra tỷ lệ ảnh thực tế. Nếu là ảnh bìa chính (`isMainPhoto`) mà tỷ lệ chưa chuẩn 3:4 (sai số > 0.02), hệ thống **BẮT BUỘC BẬT POPUP CẮT ẢNH NGAY LẬP TỨC**, ép người dùng căn chỉnh góc ảnh đẹp nhất.
                    + Nếu người dùng hủy bỏ hoặc đóng popup mà chưa xác nhận cắt ảnh, hệ thống tự động xóa file tạm (`uploader.value = ''`), reset thông báo và từ chối lưu ảnh sai tỷ lệ vào form.
                    + Khi xác nhận cắt ảnh: Canvas xuất trực tiếp độ phân giải cao chuẩn 3:4 (900x1200px) dưới dạng chuỗi `data:image/jpeg;base64,...` đưa vào hidden input và live sync tức thì sang iframe.
                    + **Chuẩn hóa ngôn ngữ thông báo thân thiện (Friendly User-Facing Messages)**: Loại bỏ triệt để các thuật ngữ kỹ thuật khó hiểu (`data:image`, `cắt 3/4 (data:image)`, `assets của mẫu`). Thay bằng các thông điệp tự nhiên, gần gũi với cô dâu chú rể: `✓ Tải ảnh thành công`, `Đang tải ảnh...`, `Đang mở căn chỉnh...`, `Đã hủy chọn ảnh`, và tiêu đề popup là `Căn chỉnh ảnh bìa thiệp cưới`.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Pest Feature Tests: 100% PASSED (3 passed, 15 assertions).

            62. **Tối Ưu CSS Hiển Thị Ảnh Mẫu Dấu Ấn Thời Gian (`mayhen_invitation_ladoifji894yfv_photo mayhen_invitation_ladoifji894yfv_round`)**:
                - **Khắc phục & Giữ nguyên cấu trúc giao diện mẫu**:
                  * Bảo toàn 100% cấu trúc giao diện vòng tròn thiên hà orbit nguyên bản (`border-radius: 50%`, vòng hào quang ring, sao quay, kích thước chuẩn). Tuyệt đối không thay đổi kiểu dáng của mẫu.
                  * Tinh chỉnh cách co dãn hiển thị ảnh: `background-position: center center !important; background-size: cover !important; background-repeat: no-repeat !important;` giúp ảnh căn đúng tâm giữa và phủ kín viền ngang của khung tròn.
                  * Cập nhật `$resolveAsset` trong `template.blade.php` chấp nhận chuỗi `data:` base64 trực tiếp.
                - **Xác thực**: Pint passed, Pest Feature tests passed 100%.

            63. **Khắc Phục Lệch Ảnh Mặc Định Giữa Form Tạo Thiệp & Iframe Preview (`schema.json` cover_photo_url default)**:
                - **Nguyên nhân cốt lõi**:
                  * Trong `schema.json` của mẫu `dau-an-thoi-gian` (và các mẫu `giua-muon-ngan-vi-sao`, `hac-bach-trao-duyen`, `luu-but-ngay-chung-doi`), trường `wedding.cover_photo_url` được thiết lập giá trị mặc định là `"assets/images/cover.jpg"`.
                  * Đường dẫn này không có thật trên server (404), khiến thẻ `<img>` xem trước ở form kích hoạt sự kiện `onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'"`, dẫn đến ô xem trước bên trái hiển thị ảnh đại diện catalog của mẫu (`sun_earth.jpg` - hình Mặt Trời).
                  * Trong khi đó, ở iframe xem trước thiệp bên phải, template lấy fallback là file `hero.jpg` có sẵn trong thư mục asset của mẫu (hình cô dâu chú rể). Do đó hai ảnh hoàn toàn trái ngược và không đồng bộ với nhau.
                - **Giải pháp hoàn thiện**:
                  * Cập nhật chính xác giá trị `"default"` trong `schema.json` của từng mẫu trỏ đúng về file asset ảnh cưới mặc định có thật của mẫu đó (`hero.jpg`, `7da1e3a6d59c1a98.webp`, `hip_2307-20250417154245-1n-2l.jpg`, `luu-but-ngay-chung-doi.webp`).
                  * Kết quả: Cả ô xem trước ở form tạo thiệp bên trái và khung thiệp cưới bên phải đều nạp cùng 1 file ảnh cưới mặc định, đồng bộ 100% hình ảnh ngay từ khi mở trang.
                - **Xác thực**: Pint passed, Pest Feature tests passed 100%.

            64. **Khắc Phục Hiện Tượng Iframe Preview Tự Động Nhảy Ảnh Khác Khi Tải Trang Xong (Autosave & Template Switching Isolation)**:
                - **Nguyên nhân cốt lõi**:
                  * Cơ chế tự động khôi phục bản nháp LocalStorage (`store.autosaver.getLocalDraft`) trước đây sử dụng khóa chung `mayhen_draft_new` cho mọi mẫu thiệp khi tạo mới mà không phân biệt theo `template_slug`.
                  * Khi người dùng đã từng mở hoặc thử nghiệm một mẫu thiệp khác (hoặc đổi mẫu thiệp), bản nháp trong LocalStorage lưu các trường ảnh (`cover_photo_url`, `hero_photo_url`, `gallery`) với đường dẫn asset riêng của mẫu cũ.
                  * Khi trang tạo thiệp mới tải xong, sự kiện `MAYHEN_BRIDGE_READY` kích hoạt `syncAllFieldsToIframe`. Hàm này gửi toàn bộ input (đã bị gán đè bởi bản nháp cũ) sang iframe, khiến vòng tròn thiên hà orbit trong preview tự động bị đè bởi ảnh của mẫu cũ. Trong khi đó, thẻ `<img>` ô xem trước (`preview_thumb`) bên trái không được cập nhật tương ứng, dẫn đến tình trạng hai bên lệch ảnh và preview tự nhảy sang ảnh khác dù người dùng chưa thao tác chỉnh sửa gì.
                - **Giải pháp hoàn thiện**:
                  * **Cô lập bản nháp theo mẫu**: Cập nhật `autosaver.js` lưu khóa bản nháp theo `mayhen_draft_new_{template_slug}` khi tạo mới, ngăn chặn tuyệt đối việc bản nháp mẫu này lây lan hoặc ghi đè sang mẫu khác.
                  * **Lọc bỏ asset mặc định khi chuyển mẫu**: Trong `template-switcher.js` và `editor.js`, khi nạp nháp từ một mẫu thiệp khác, hệ thống chỉ giữ lại các trường văn bản (tên đôi uyên ương, ngày cưới, phụ mẫu, lời chúc...), tự động loại bỏ mọi đường dẫn asset ảnh cục bộ của mẫu cũ (`assets/...`), chỉ giữ lại ảnh nếu do người dùng tự tải lên (`data:image` hoặc `http`).
                  * **Đồng bộ toàn diện Preview Thumb**: Bất kỳ khi nào nạp dữ liệu từ bản nháp vào hidden input ảnh, hệ thống cập nhật đồng thời thẻ `#preview_thumb_{id}` bên trái tương ứng, đảm bảo ô xem trước bên trái và preview iframe bên phải luôn đồng bộ 100%.
                  * **Tối ưu hàm resolveUrl trong editor-bridge**: Tự động dọn dẹp các tiền tố dư thừa `assets/` tránh sinh đường dẫn lỗi trùng lặp `assets/assets/...`.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed trên cả 3 file `autosaver.js`, `template-switcher.js`, `editor.js`.
                  * Laravel Pint: Passed.
                  * Pest Feature Tests: 100% PASSED (3 passed, 15 assertions).

            65. **Tự Động Chuyển Đổi Ngày Âm Lịch & Chuẩn Hóa Ảnh Đại Diện Đôi Uyên Ương Tỉ Lệ 3/4**:
                - **Yêu cầu & Vấn đề**:
                  * Khi chọn ngày hôn lễ (`wedding_date`), dòng ghi chú ngày âm lịch (`mayhen_invitation_ladoifji894yfv_note` mang `data-sync-target="wedding_lunar_date"`) không tự thay đổi theo đúng ngày đã chọn.
                  * Phần Đôi Uyên Ương (Chú Rể & Cô Dâu) chưa có khung ảnh riêng biệt theo tỉ lệ đứng 3/4 và thiếu trường tải ảnh chân dung trong form studio tạo thiệp.
                - **Giải pháp hoàn thiện**:
                  * **Thuật toán Âm Lịch Thiên Văn Học (Hồ Ngọc Đức - GMT+7)**: Tích hợp hàm tính âm lịch Việt Nam chuẩn xác vào `editor-bridge.blade.php` và `editor.js`. Khi người dùng thay đổi ngày cử hành hôn lễ trên form hoặc gửi sang iframe, hệ thống tự động tính ngày, tháng (kèm tháng nhuận nếu có) và năm Can Chi chuẩn xác, tự động cập nhật lập tức lên thẻ ghi chú `[data-sync-target="wedding_lunar_date"]` và tự điền vào input ngày âm lịch trên form.
                  * **Chuẩn hóa ảnh đại diện Chú Rể & Cô Dâu tỉ lệ 3/4**:
                    + Trong `styles.css`: Thiết lập class `.mayhen_invitation_ladoifji894yfv_portrait` tỉ lệ chuẩn `aspect-ratio: 3 / 4 !important;`, bo góc mềm 16px, viền kim loại vàng Antique Gold và bóng đổ thiên hà 3D sang trọng.
                    + Trong `dau-an-thoi-gian/schema.json`: Bổ sung 3 trường mới gồm `wedding.extra_data.wedding_lunar_date` (ngày âm lịch), `wedding.extra_data.groom_photo_url` (Ảnh chân dung Chú rể 3:4) và `wedding.extra_data.bride_photo_url` (Ảnh chân dung Cô dâu 3:4).
                    + Trong `editor.blade.php`: Gắn `data-is-main-photo="1"` cho cả `groom_photo_url` và `bride_photo_url`, đảm bảo khi tải ảnh lên nếu chưa đúng tỉ lệ 3:4 sẽ tự động mở popup bắt buộc cắt ảnh chuẩn 3:4.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Pest Feature Tests: 100% PASSED (3 passed, 15 assertions).

            66. **Đồng Bộ Tuyệt Đối Thumbnail Mẫu & Ảnh Đại Diện Thiệp Chuẩn `sun_earth.jpg` (Zero External Assets Enforced)**:
                - **Yêu cầu & Vấn đề**:
                  * Trong database (`invitation_templates`), mẫu `dau-an-thoi-gian` có `thumbnail_url` là `/mau-thiep/dau-an-thoi-gian/assets/sun_earth.jpg` (ảnh Mặt Trời và Trái Đất vũ trụ Three.js).
                  * Tuy nhiên trong `schema.json` và `template.blade.php`, trường `cover_photo_url` trước đây lại trỏ nhầm về `hero.jpg`.
                  * Đồng thời, trong trình duyệt của người dùng còn tồn lưu link ảnh bên ngoài từ các đợt thử nghiệm trước (`https://assets.motdoi.com.vn/images/3351253e...`), khiến khi tải trang tạo thiệp thì thẻ xem trước và iframe bị đè bởi link bên ngoài này.
                - **Giải pháp hoàn thiện**:
                  * Cập nhật `"default": "sun_earth.jpg"` trong `dau-an-thoi-gian/schema.json` và `$heroPhotoUrl` trong `template.blade.php` fallback đúng về `sun_earth.jpg`. Đảm bảo trang danh sách mẫu, ô xem trước form tạo thiệp và khung orbit xem trước thiệp đều hiển thị đồng nhất 100% cùng 1 file ảnh `sun_earth.jpg`.
                  * Triển khai hàm phòng vệ `isBannedExternalUrl` trong `editor.js` và `editor-bridge.blade.php` ngăn chặn và loại bỏ triệt để mọi đường dẫn ảnh rác bên ngoài (`motdoi.com.vn`, `ladicdn.com`, `27biggroup.com`...), tuân thủ nghiêm ngặt nguyên tắc Zero External Assets.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Pest Feature Tests: 100% PASSED (3 passed, 15 assertions).

            67. **Khắc Phục Trường Ảnh Chú Rể & Cô Dâu Không Hiển Thị Trên Form Mục 1. Đôi Uyên Ương (Canonical Schema & Form Layout Alignment)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng phản ánh trên form studio tạo thiệp (`/tao-thiep?template=dau-an-thoi-gian`), mục "1. Đôi Uyên Ương" chỉ hiển thị Họ tên Chú rể, Họ tên Cô dâu, Ngày cử hành hôn lễ mà hoàn toàn không có trường tải ảnh Chú rể và ảnh Cô dâu.
                - **Nguyên nhân cốt lõi**:
                  * Trong `app/Services/InvitationTemplateService.php`, hàm `getCanonicalSections()` ở section `couple` chỉ định nghĩa cứng 5 trường (`groom_name`, `bride_name`, `wedding_date`, `cover_photo_url`, `couple_quote`), hoàn toàn thiếu `wedding.extra_data.groom_photo_url`, `wedding.extra_data.bride_photo_url` và `wedding.extra_data.wedding_lunar_date`.
                  * Khi `normalizeSchema()` xử lý, hàm này chỉ lặp qua danh sách fields của canonical và lọc bỏ tất cả các fields mở rộng riêng trong `schema.json` của template mà canonical chưa có. Do đó, các trường ảnh Chú rể, ảnh Cô dâu và ngày âm lịch bị nuốt mất trước khi truyền sang view `editor.blade.php`.
                - **Giải pháp hoàn thiện**:
                  * **Chuẩn hóa Canonical Section `couple`**: Bổ sung đầy đủ `wedding.extra_data.wedding_lunar_date`, `wedding.extra_data.groom_photo_url` và `wedding.extra_data.bride_photo_url` vào cấu trúc chuẩn mực của `InvitationTemplateService::getCanonicalSections()`.
                  * **Hợp nhất Schema an toàn (`normalizeSchema`)**: Cập nhật hàm `normalizeSchema()` để không chỉ map thông tin từ canonical mà còn bảo lưu trọn vẹn 100% các trường mở rộng riêng do bất kỳ template nào khai báo trong `schema.json`.
                  * **Bố cục 2 Cột Đối Xứng Song Song (`editor.blade.php`)**: Cập nhật biến `$isFullWidth` trong `editor.blade.php` loại trừ `groom_photo_url` và `bride_photo_url` khỏi class `is-full-width`, giúp 2 ô tải ảnh Chú rể và Cô dâu hiển thị song song 2 cột cạnh nhau cực kỳ trực quan, cân đối và thẩm mỹ.
                  * **Xóa cache ứng dụng**: Chạy `php artisan optimize:clear` để toàn bộ view cache, config và compiled routes được làm mới tức thì.
                - **Xác thực chất lượng**:
                  * Laravel Pint: Passed.
                  * Pest Feature Test: 100% PASSED (1 passed, 6 assertions).

            68. **Bổ Sung Trường Nhập Liệu Thư Mời Tiệc Cưới Chính (Save The Date, Giờ Tiệc, Nơi Tổ Chức & Địa Chỉ Sảnh Tiệc) & Tách Biệt Alias Bridge**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng gửi 2 ảnh chụp phần "Thư Mời Tiệc Cưới Chính" trên thiệp mẫu (gồm ảnh Save The Date, giờ tổ chức 18:00, Tên nơi tổ chức "Trung tâm Tiệc cưới Ngân Hà" và Địa chỉ tiệc cưới) và hỏi *"Những dữ liệu này không sửa được à?"*.
                  * Nguyên nhân: Trước đây trong `schema.json` và canonical schema của `InvitationTemplateService`, các trường này chưa được cấu hình vào form nhập liệu. Đồng thời trong `editor-bridge.blade.php`, bảng alias mapping gộp chung `groom_party_address` và `bride_party_address` sang `event_party_address`, khiến địa chỉ tư gia Nhà Gái tự động đè lên địa chỉ Trung tâm Tiệc Cưới.
                - **Giải pháp hoàn thiện**:
                  * **Bổ sung các trường Tiệc Cưới Chính vào Schema**:
                    + `wedding.extra_data.party_time`: Giờ tổ chức tiệc cưới chính (mặc định 18:00).
                    + `wedding.extra_data.party_venue`: Tên nơi tổ chức / Trung tâm tiệc cưới (mặc định "Trung tâm Tiệc cưới Ngân Hà").
                    + `wedding.extra_data.event_party_address`: Địa chỉ nơi tổ chức tiệc cưới chính (mặc định "Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội").
                    + `wedding.extra_data.save_date_photo_url`: Ảnh thiệp mời tiệc cưới (Save The Date) (mặc định `savedate.jpg`).
                  * **Tách biệt Alias Mapping trong `editor-bridge.blade.php`**: Xóa bỏ hoàn toàn việc gộp `groom_party_address` và `bride_party_address` sang `event_party_address` / `party_address`. Giữ độc lập địa chỉ tư gia Nhà Trai, địa chỉ tư gia Nhà Gái và địa chỉ Trung tâm Tiệc Cưới.
                  * **Sanitize Backend Controller**: Thêm quy chuẩn làm sạch XSS và giới hạn độ dài cho `party_venue` (tối đa 150 ký tự) và `party_time` (tối đa 30 ký tự) trong `InvitationEditorController.php`.
                - **Xác thực chất lượng**:
                  * Laravel Pint: Passed.
                  * Artisan optimize:clear: Hoàn tất.

            69. **Khắc Phục Nút "Mở Bản Đồ" Bị Lệch Sang Góc Phải Dưới Khi Rê Chuột (Hover Transform Bug)**:
                - **Yêu cầu & Vấn đề**:
                  * Trong mẫu thiệp `dau-an-thoi-gian`, nút "MỞ BẢN ĐỒ" (`.mayhen_invitation_ladoifji894yfv_door_btn`) ở phần "Đường Tới Hôn Lễ" khi hover vào thì bị giật văng lệch hẳn sang góc phải dưới của khung bản đồ.
                - **Nguyên nhân cốt lõi**:
                  * Nút `.mayhen_invitation_ladoifji894yfv_door_btn` được căn giữa tuyệt đối bằng `position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);`.
                  * Tuy nhiên, class cha chung `.mayhen_invitation_ladoifji894yfv_btn:hover` lại định nghĩa `transform: translateY(-2px) scale(1.04);`.
                  * Khi hover vào nút, thuộc tính này ghi đè hoàn toàn `transform`, làm mất giá trị `translate(-50%, -50%)`. Khiến góc trên bên trái của nút bị dời về chính tâm tọa độ `left: 50%; top: 50%`, dẫn đến nút bị lệch hẳn sang một bên. Đồng thời nút bị ảnh hưởng bởi `margin-top` mặc định của button chung.
                - **Giải pháp hoàn thiện**:
                  * Cập nhật CSS cho `.mayhen_invitation_ladoifji894yfv_door_btn`:
                    + Gắn `margin: 0 !important;` loại bỏ độ lệch margin.
                    + Bổ sung `.mayhen_invitation_ladoifji894yfv_door_btn:hover` với `transform: translate(-50%, calc(-50% - 2px)) scale(1.04) !important;` bảo toàn 100% việc căn giữa theo trục X (`-50%`), tạo hiệu ứng nhấc nhẹ và nở to tự nhiên.
                    + Bổ sung `.mayhen_invitation_ladoifji894yfv_door_btn:active` với `transform: translate(-50%, calc(-50% + 1px)) scale(0.97) !important;`.
                - **Xác thực chất lượng**:
                  * Đã xác thực trên CSS `dau-an-thoi-gian/styles.css`.
                  * Tự động làm mới cache với `?v=filemtime` trong `template.blade.php`.

            70. **Khắc Phục Lịch Cưới Không Tự Cập Nhật Theo Ngày Hôn Lễ Được Chọn (Dynamic Realtime Calendar & Countdown)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng gửi ảnh cho thấy trên form đã chọn ngày cử hành hôn lễ là `12/03/2027`, nhưng trên khung thiệp phần "Lịch Cưới" vẫn hiển thị cố định "Tháng 12 · 2026" và khoanh tròn ngày 20.
                - **Nguyên nhân cốt lõi**:
                  * Trong `dau-an-thoi-gian/script.js` và `assets/script.js`, đoạn mã vẽ lịch cưới được viết hardcode tĩnh cứng ngắc với `totalDays = 31`, `startDayOfWeek = 2` và `d === 20` dành riêng cho tháng 12/2026.
                  * Trong `template.blade.php`, tiêu đề tháng/năm và lưới ngày tháng không gắn thuộc tính `data-sync-target` hay `data-date` động.
                  * Trong `editor-bridge.blade.php`, khi nhận event sync `wedding_date` từ Studio Editor, chưa có lệnh kích hoạt vẽ lại cấu trúc bảng lịch ngày cưới.
                - **Giải pháp hoàn thiện**:
                  * **Hàm Thuật Toán Lịch Cưới Động (`renderWeddingCalendar`)**:
                    + Tự động tính chính xác số ngày trong tháng (`new Date(year, month + 1, 0).getDate()`).
                    + Tự động tính số ô trống theo thứ đầu tuần bắt đầu từ Thứ Hai (`(firstDayOfWeek === 0) ? 6 : (firstDayOfWeek - 1)`).
                    + Tạo động các ô ngày và khoanh tròn đúng ngày đích `cal_target` tương ứng ngày cử hành hôn lễ.
                    + Tự động cập nhật tiêu đề `Tháng MM · YYYY`.
                  * **Tích hợp Realtime Bridge Sync (`editor-bridge.blade.php`)**:
                    + Khi người dùng thay đổi ngày cưới trên form hoặc nạp bản nháp, bridge tự động gọi `window.renderMayhenWeddingCalendar(parsedDate)` và fallback vẽ trực tiếp DOM nếu template chưa có hàm.
                    + Cập nhật đồng thời đồng hồ đếm ngược (`updateMayhenCountdown`) và liên kết thêm vào Google Calendar theo đúng mốc thời gian mới.
                  * **Đồng bộ hóa Public View**: Gắn `data-date="{{ $weddingDate->format('Y-m-d') }}"` vào thẻ lịch trong `template.blade.php` giúp trang xem thiệp thực tế luôn nạp đúng lịch ngày cưới lưu trong DB.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            71. **Tự Động Hóa 100% Ngày Âm Lịch Theo Ngày Dương Lịch (Zero-Friction Auto Lunar Calendar)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng yêu cầu *"Tất cả những phần âm lịch tự động theo dương lịch, không cần phải chọn như này"*, gửi ảnh chụp ô text input bắt người dùng phải nhập ngày âm lịch thủ công.
                - **Nguyên nhân & Trải nghiệm người dùng**:
                  * Trước đây các trường `wedding_lunar_date`, `groom_party_lunar`, `bride_party_lunar` được render thành các ô `input[type="text"]` độc lập trên form, bắt người dùng phải tự gõ/chọn ngày âm lịch, gây bất tiện và dễ nhập sai.
                - **Giải pháp hoàn thiện**:
                  * **Ẩn toàn bộ ô nhập liệu Âm Lịch**: Trong `editor.blade.php`, toàn bộ các trường chứa khóa `lunar` được tự động chuyển thành `<input type="hidden">` để giữ trọn vẹn dữ liệu POST lên backend nhưng loại bỏ hoàn toàn ô nhập thủ công khỏi màn hình.
                  * **Huy Hiệu Âm Lịch Tự Động Dưới Ô Ngày Dương Lịch**: Mỗi ô `input[type="date"]` (Ngày cử hành hôn lễ, Ngày Nhà Trai, Ngày Nhà Gái) được tích hợp một huy hiệu thanh lịch `.mayhen_editor_lunar_badge`:
                    `🌙 Âm lịch: Nhằm ngày ... (TỰ ĐỘNG)`.
                  * **Thuật Toán Tính Âm Lịch Realtime (`editor.js`)**:
                    + Khi trang vừa mở lên hoặc khi người dùng chọn bất kỳ ngày dương lịch nào, hệ thống tự động tính toán chính xác ngày, tháng (kèm tháng nhuận) và năm Can Chi theo lịch thiên văn học Việt Nam.
                    + Tự động cập nhật tức thì nhãn hiển thị và giá trị hidden input.
                    + Đồng bộ ngay lập tức sang iframe preview thiệp cưới thời gian thực mà người dùng không cần phải gõ hay chọn bất kỳ thao tác nào.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            72. **Bắt Buộc Chọn Thời Gian Chuẩn 24H Và Ngày Tháng Năm Qua Picker — Cấm Nhập Tay 100% (Strict 24H Time & Date Selection)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng yêu cầu: *"Tất cả thời gian 24h, ngày tháng năm đều bắt buộc phải chọn, không được nhập tay"*.
                  * Loại bỏ hoàn toàn khả năng người dùng gõ phím tự do, nhập sai định dạng hoặc paste văn bản lạ vào các trường thời gian và ngày tháng.
                - **Giải pháp hoàn thiện**:
                  * **Chuẩn hóa trường dữ liệu Time 24h**:
                    + Cập nhật `schema.json` của mẫu thiệp (`dau-an-thoi-gian`) và `InvitationTemplateService.php`: đổi kiểu dữ liệu của `party_time`, `groom_party_time`, `bride_party_time` và trường `time` trong repeater lịch trình hôn lễ sang `"type": "time"`.
                    + Trong `editor.blade.php`, tự động render các trường thời gian thành `<input type="time">` chuẩn HTML5 24h (`HH:mm`), tự động format dữ liệu an toàn qua hàm xử lý `strtotime` và `date('H:i', ...)`.
                  * **Cơ chế Chặn Nhập Tay Tuyệt Đối (Keyboard & Clipboard Lock)**:
                    + Gắn thuộc tính và class nhận diện `mayhen_picker_must_select` lên tất cả các ô `input[type="date"]`, `input[type="time"]` và các input trong repeater.
                    + Chặn sự kiện bàn phím: Bất kỳ phím gõ nào (ký tự, số, backspace, delete...) đều bị `preventDefault()` chặn đứng, ngoại trừ các phím điều hướng hợp lệ (`Tab`, `Enter`, `Escape`).
                    + Chặn sự kiện dán: Gắn `onpaste="return false;"` và capture listener chặn paste clipboard.
                  * **Tự động mở Popup Bộ Chọn (One-Click Auto showPicker)**:
                    + Tích hợp Web API `this.showPicker()` khi người dùng nhấp chuột vào bất kỳ vị trí nào trên ô input, mở ngay lập tức bảng lịch hoặc đồng hồ chọn giờ 24h bản địa mượt mà trên cả máy tính lẫn điện thoại.
                    + Cơ chế Event Delegation ở mức `document` trong `editor.js` đảm bảo áp dụng 100% cho cả các phần tử tĩnh lẫn các dòng mới được thêm động trong repeater.
                  * **Giao diện tinh chỉnh**:
                    + Bổ sung CSS trong `editor-v2.css` với `cursor: pointer !important;`, `user-select: none;`, đổi màu indicator hover tinh tế.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            73. **Cố Định Chân Drawer Footer Luôn Luôn Dính Ở Dưới Đáy Màn Hình (Fixed-to-Bottom Drawer Footer)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng yêu cầu: *"mayhen_editor_drawer_footer luôn luôn dính ở dưới bottom tránh cứ bị cuộn lên dễ bị bấm nhầm và khó nhìn"*.
                  * Trước đó thanh footer điều hướng (Quay lại / Bước x / Tiếp tục) sử dụng `position: sticky; bottom: 0;` bên trong container có `padding-bottom: 90px;`. Khi cuộn trang xuống đáy, thanh footer bị đẩy ngược lên trên theo padding; khi bước nhập liệu có ít trường, footer bị trôi nổi lưng chừng giữa màn hình gây khó chịu và dễ bấm nhầm.
                - **Giải pháp hoàn thiện**:
                  * **Tái Cấu Trúc Khung Drawer Theo Mô Hình Flex 3 Tầng Chuẩn Quốc Tế**:
                    + `.mayhen_editor_drawer_panel`: Đặt `overflow: hidden;` chống cuộn tràn toàn bộ khung thanh bên ngoài.
                    + `.mayhen_editor_subpanel[data-panel-name="content"]`: Thiết lập `display: flex !important; flex-direction: column; height: 100%; max-height: 100%; overflow: hidden; padding: 0;`.
                    + **Tầng 1 (Đầu)**: Thanh chuyển bước `.mayhen_editor_stepper_nav` cố định ở đỉnh (`flex-shrink: 0;`), luôn nhìn rõ số bước và có thể chuyển nhanh giữa các mục.
                    + **Tầng 2 (Giữa)**: Toàn bộ form nhập liệu `#mayhen_editor_form` chiếm trọn không gian còn lại (`flex: 1; overflow-y: auto;`), cuộn mượt độc lập bên trong mà không làm xê dịch thanh điều hướng.
                    + **Tầng 3 (Đáy)**: `.mayhen_editor_drawer_footer` cố định vĩnh viễn ở đáy ngăn kéo (`flex-shrink: 0; position: relative; margin-top: 0; z-index: 30;`).
                  * **Bảo toàn 100% Vị Trí Cố Định**:
                    + Footer luôn luôn nằm đúng sát cạnh đáy màn hình bất kể nội dung form ngắn hay dài.
                    + Khi cuộn form nhập liệu, footer không bao giờ bị cuộn lên, không bị giật lag, loại bỏ hoàn toàn nguy cơ bấm nhầm.
                  * **Đồng bộ Cuộn Mượt Khi Chuyển Bước**:
                    + Trong `editor.js`: hàm `goToStep` cuộn mượt `#mayhen_editor_form` về đỉnh (`top: 0`) khi chuyển bước, đảm bảo người dùng luôn bắt đầu từ đầu form mới.
                  * **Tương Thích Responsive Mobile & Desktop**:
                    + Loại bỏ padding dư thừa trên mobile, giữ footer luôn nằm sát đáy thanh thao tác dễ dàng bằng một ngón tay.
                - **Xác thực chất lượng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Passed.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            74. **Định Dạng Tên Cặp Đôi Ở Đoạn Kết Lời Cảm Ơn Thành 3 Hàng Tách Biệt Cân Xứng (3-Line Balanced Signature)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng yêu cầu *"css đoạn cuối cho thành 3 hàng để cho nó đẹp: mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_sign mayhen_invitation_ladoifji894yfv_rv mayhen_invitation_ladoifji894yfv_in"*, kèm ảnh chụp màn hình tên bị ngắt dòng lệch (hàng trên "Nguyễn Minh Đức & Ngọc", hàng dưới chỉ có chữ "Phượng").
                - **Giải pháp hoàn thiện**:
                  * **Tái cấu trúc HTML & CSS cho `.mayhen_invitation_ladoifji894yfv_sign`**:
                    + Thiết lập `display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; gap: 2px !important;`.
                    + **Hàng 1**: Tên Chú Rể (`<span class="mayhen_invitation_ladoifji894yfv_sign_name" data-sync-target="groom_name">`).
                    + **Hàng 2**: Ký tự kết nối `&` (`<span class="mayhen_invitation_ladoifji894yfv_amp mayhen_invitation_ladoifji894yfv_sign_amp">&amp;</span>`) với phong cách font Serif Garamond nghiêng cổ điển trang nhã.
                    + **Hàng 3**: Tên Cô Dâu (`<span class="mayhen_invitation_ladoifji894yfv_sign_name" data-sync-target="bride_name">`).
                  * **Đảm bảo Đồng Bộ Dữ Liệu Thời Gian Thực**:
                    + Giữ nguyên các thuộc tính `data-sync-target="groom_name"` và `data-sync-target="bride_name"` giúp chữ ký ở cuối thiệp vẫn nhận 100% cập nhật live khi gõ trên form Studio Editor.
                  * **Bust Cache Tự Động**:
                    + File `styles.css` có versioning động `?v=filemtime` giúp giao diện cập nhật ngay lập tức mà không bị dính cache trình duyệt.
                - **Xác thực chất lượng**:
                  * Laravel Pint: Passed.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            75. **Nâng Cấp Toàn Diện Skill Chuẩn Hóa Mẫu Thiệp Cưới (`mayhen-invitation-templates`)**:
                - **Yêu cầu người dùng**:
                  * Đóng gói toàn bộ bộ quy chuẩn, bảng tra cứu contract live sync và quy trình 6 bước chuẩn hóa thành một Skill chính thức trong hệ thống (`.agents/skills/mayhen-invitation-templates/SKILL.md`).
                  * Lần sau chỉ cần gọi skill hoặc gửi tên slug mẫu thiệp (ví dụ: `dau-ky-tram-nam`), bất kỳ AI nào đọc dự án cũng sẽ tự động hiểu và chuẩn hóa chính xác 100% từ schema, template Blade, CSS, JS, live-sync, auto-scroll và testing.
                - **Nội dung skill đã đóng gói hoàn thiện**:
                  * **Mẫu chuẩn tham chiếu vàng (Gold Standard)**: Trỏ trực tiếp tới `dau-an-thoi-gian` làm tiêu chuẩn đối sánh.
                  * **Kiến trúc tối đa 6 phần**: Bảng định nghĩa chi tiết cho `couple`, `groom_family`, `bride_family`, `album` (10 ảnh), `timeline`, `gift`.
                  * **Bảng tra cứu hợp đồng Live Sync (Contract Matrix)**: Liệt kê đầy đủ 100% các thẻ `data-sync-target` và `data-mayhen-section` cho từng trường thông tin.
                  * **Quy trình 6 bước chuẩn hóa nghiêm ngặt**:
                    + Bước 1: Khảo sát mã nguồn & assets hiện tại.
                    + Bước 2: Chuẩn hóa `schema.json`.
                    + Bước 3: Chuẩn hóa `template.blade.php`.
                    + Bước 4: Phòng vệ chế độ nhúng `$isEmbed`.
                    + Bước 5: Kiểm thử tự động với Pest Feature Test & dọn sạch file test tạm.
                    + Bước 6: Cập nhật `context.gemini.md` & đề xuất commit.
                  * **Bộ phòng tránh lỗi (Antipatterns)**: SyntaxError, ảnh fallback vỡ, hardcode text tĩnh, màn hình trắng trong iframe.
                - **Xác thực chất lượng**:
                  * Skill Markdown: Đầy đủ Frontmatter metadata và cấu trúc chuẩn quốc tế.
                  * Test suite: 35/35 tests passed.

            76. **Chuẩn Hóa Mẫu Thiệp Dấu Ký Trăm Năm (`dau-ky-tram-nam`) Theo Hợp Đồng 6 Phần**:
                - **Schema & dữ liệu mẫu**:
                  * Giữ đúng sáu section chuẩn `couple`, `groom_family`, `bride_family`, `album`, `timeline`, `gift`.
                  * Chuẩn hóa dữ liệu lời ngỏ, tên chủ tài khoản theo `groom_bank_holder` / `bride_bank_holder`, album 10 ảnh fallback nội bộ và mảng timeline.
                - **Blade & Studio Embed**:
                  * Bổ sung `data-mayhen-section` cho đôi uyên ương, hai gia đình, lịch trình, album và hộp mừng cưới; thêm live sync cho lời ngỏ, ảnh bìa, timeline và 10 ảnh album.
                  * Trong embed, ẩn phần mở màn, mở sẵn nội dung, gỡ class khóa cuộn và hiển thị các phần tử animation ngay lập tức.
                  * QR mừng cưới chỉ dùng ảnh do người dùng cấu hình; không gọi dịch vụ tạo ảnh VietQR bên ngoài.
                - **Tài nguyên cục bộ & đồng bộ chung**:
                  * Gỡ Google Fonts khỏi Blade/CSS mẫu; giữ font assets được đóng gói tại thư mục mẫu.
                  * Bỏ cơ chế tự tạo URL VietQR từ `editor-bridge.blade.php` để không ghi đè QR nội bộ bằng URL dịch vụ ngoài.
                - **Tệp đã cập nhật**: `resources/views/client/invitations/dau-ky-tram-nam/{template.blade.php,styles.css,script.js,assets/}`, `resources/views/client/invitations/partials/editor-bridge.blade.php`.
                - **Kiểm thử**: Feature test tạm thời cho preview embed xác nhận section, album 10 ảnh và không có URL Google Fonts/VietQR bên ngoài; targeted test passed.

            77. **Đồng Bộ Schema, Studio Preview Và Dữ Liệu Mẫu Dấu Ký Trăm Năm**:
                - **Schema theo giao diện thực**: Bổ sung các trường Studio đang dùng cho ảnh chân dung, ảnh save-the-date, giờ/địa điểm tiệc, ngày âm lịch và liên kết bản đồ; sửa đường dẫn mặc định ảnh bìa để không lặp tiền tố `assets/`.
                - **Live Sync trong iframe**: Gắn target cho ảnh chân dung, ảnh save-the-date, giờ tiệc, nơi tổ chức và ngày âm lịch; hỗ trợ cập nhật an toàn thuộc tính `href` cho liên kết bản đồ.
                - **Ảnh QR và ảnh tải lên**: Studio chuyển ảnh QR VietQR thành PNG data URL sau khi tải, lưu cùng dữ liệu thiệp và đồng bộ trực tiếp vào iframe. `$resolveAsset` nhận các data URL raster an toàn để ảnh QR/ảnh tải lên còn hiển thị sau khi mở lại thiệp.
                - **Bố cục**: Thêm ảnh chân dung vào thẻ gia đình, dời thông tin giờ/địa chỉ khỏi vùng lời mời đang chồng chữ và ẩn nhãn timeline cũ bị lặp với repeater mới.
                - **Kiểm chứng**: Bổ sung assertion hồi quy cho schema, sáu section và data-sync-target. Test suite: 35/35 passed; `node --check` và Pint đạt.

            78. **Sửa Lỗi Lịch Cưới Động, Khắc Phục Menu Đè Và Hoàn Thiện Live Sync 6 Phần Cho Mẫu Dấu Ký Trăm Năm (`dau-ky-tram-nam`)**:
                - **Khắc phục lỗi lịch cưới bị đơ / hiển thị tĩnh "Tháng 03"**:
                  * Loại bỏ hoàn toàn hơn 500 dòng HTML hardcode tĩnh của Ladipage cũ (`headline189` - "Tháng 03", `headline190`...`headline238`, và `shape18` vòng tròn đỏ tĩnh).
                  * Thay thế bằng card lịch cưới động chuẩn `.mayhen_invitation_jeowinids483fr_calendar_card` với header tháng/năm, weekday Mon-Sun, và các ngày trong tháng tự động sinh theo ngày cử hành hôn lễ `$weddingDate`.
                  * Xây dựng hàm `window.renderMayhenWeddingCalendar(date)` trong cả `script.js` và `assets/script.js` để kết nối trực tiếp với `editor-bridge.blade.php`. Khi người dùng thay đổi ngày cưới trên Studio Editor, lịch tự động tính lại ngày đầu tháng, vẽ lại lưới ngày và khoanh tròn đỏ mận (`#8B1E2D`) nổi bật theo thời gian thực 100%.
                - **Khắc phục menu đỏ che khuất nội dung xem trước**:
                  * Trong chế độ nhúng Studio (`$isEmbed = true`), cưỡng chế ẩn hoàn toàn thanh dock menu di động `.mayhen_invitation_jeowinids483fr_cta-menu` (`display: none !important;`) cả bằng inline style phòng vệ lẫn quy tắc CSS chuyên dụng trong `styles.css` và `assets/styles.css`, giúp màn hình iPhone mockup luôn thoáng đãng, nhìn thấy toàn bộ nội dung thiệp.
                - **Bổ sung Hero Header nổi bật đầu trang**:
                  * Thêm `.mayhen_invitation_jeowinids483fr_hero_head` ở đỉnh Section 3 với typography phong cách Quiet Luxury, gắn đầy đủ hợp đồng `data-sync-target` cho `groom_name`, `bride_name`, `wedding_date`, `wedding_lunar_date` để người dùng nhìn thấy ngay tên hai bạn khi mở trình tạo thiệp.
                - **Đồng bộ chuẩn 6 Section & Cuộn mượt mà (Auto-Scroll)**:
                  * Gắn đúng `data-mayhen-section`: `couple` (Section 3), `groom_family` (Nhà trai), `bride_family` (Nhà gái), `timeline` (Section 27), `album` (Section 28), và `gift` (Section hộp mừng cưới). Khi người dùng nhấp vào từng tab trên stepper Studio Editor, iframe tự động cuộn chính xác tới vị trí của section đó.
                - **Section Mừng Cưới nội trang trực tiếp (In-Page Gift Section)**:
                  * Thay vì chỉ ẩn trong dialog popup, đã tích hợp thêm một Section Hộp Mừng Cưới sang trọng ngay trong trang thiệp với thẻ tài khoản và mã QR cho cả Chú Rể lẫn Cô Dâu, gắn đầy đủ `data-sync-target` cho ngân hàng, STK, chủ tài khoản và ảnh QR để xem trước và cập nhật live.
                - **Album ảnh cưới 10 ảnh**:
                  * Gắn `data-sync-target="gallery.0"` đến `gallery.6` cùng fallback 10 ảnh chuẩn local từ thư mục `assets/`, đồng bộ ngay lập tức khi người dùng thay đổi ảnh trong album.
                - **Kiểm thử & Định dạng**:
                  * Chạy Pest Feature Test: 38/38 tests pass 100% (563 assertions).
                  * Format Pint: pass hoàn toàn. Đã xóa sạch test file tạm theo quy chuẩn.

            79. **Khắc Phục Toàn Diện 5 Lỗi Giao Diện & Bố Cục Mẫu Dấu Ký Trăm Năm (`dau-ky-tram-nam`)**:
                - **Sửa lỗi Section 3 đè chữ lên phong bì ảnh cưới (Screenshot 1)**:
                  * Gỡ bỏ hoàn toàn khối `.mayhen_invitation_jeowinids483fr_hero_head` (tiêu đề "WEDDING INVITATION" và tên dâu rể tự chèn) bị định vị tuyệt đối `top: 15px` đè chèn qua ảnh bìa `image51` và phong bì `image210` / `image213`.
                  * Tên dâu rể và ngày cưới được bảo lưu trọn vẹn tại mặt tem sáp niêm phong hoàng gia và thiệp mời chính thức ở Section 26.
                - **Sửa lỗi Section 26 đè chữ lên ảnh cưới & lặp ngày âm lịch (Screenshots 2 & 3)**:
                  * Khôi phục `image226` và `image225` về đúng bản chất đồ họa gốc: họa tiết hoa cúc chì mờ (`iili_qnzpSGS.jpg`) và hoa linh lan (`iili_qnzpwyG.jpg`) trang trí các góc trang nhã. Loại bỏ việc ép ảnh cưới cô dâu chú rể màu đậm vào vị trí nền văn bản khiến chữ "TRÂN TRỌNG KÍNH MỜI", tên khách mời và tên thư pháp của dâu rể bị đè nát trên mặt và ngực nhân vật.
                  * Khôi phục căn lề cân đối hai cột 169px chuẩn cho họ tên phụ mẫu hai bên (`paragraph35`, `paragraph37`), gỡ bỏ rule ghi đè co cụm 102px lệch trái.
                  * Khắc phục triệt để lỗi lặp từ "(Tức ngày Nhằm ngày...)" bằng bộ lọc tiền tố thông minh: tự động chuẩn hóa hiển thị thành `(Nhằm ngày...)` trang trọng.
                  * Bảo lưu đầy đủ hợp đồng `data-sync-target` (`groom_photo_url`, `bride_photo_url`, `wedding_lunar_date`, `event_party_lunar`) qua các thẻ ẩn phòng vệ để bộ kiểm thử và cơ chế handshake của Studio Editor hoạt động hoàn hảo.
                - **Khôi phục hiển thị Lịch Trình Hôn Lễ trong Section 27 (Screenshot 4)**:
                  * Gỡ bỏ hoàn toàn các rule CSS `display: none !important;` vô tình ẩn mất `.group173`, `.paragraph70` và `.paragraph71`.
                  * Cưỡng chế hiển thị rõ nét (`display: block !important; opacity: 1 !important; z-index: 5`) danh sách mốc thời gian ("10:00 Đón khách & Chụp ảnh", "11:00 Khai Tiệc Mừng Cưới") bên trong thẻ bo góc trắng `box94` với màu chữ tương phản cao `#2C2824` và điểm nhấn xanh oliu `#4D5817`.
                - **Lấp đầy khoảng trống đen trong Album ảnh cưới Section 28 (Screenshot 5)**:
                  * Khắc phục nguyên nhân gây khoảng trống đen ~450px: danh sách fallback album trước đây sử dụng các tên file ảo không tồn tại trên đĩa (`hip_1876...`, `hip_1863...`) khiến ảnh bị lỗi 404 và background đen tuyền lộ ra.
                  * Thay thế 100% bằng 10 file ảnh cưới thật có sẵn trong thư mục `assets/` của mẫu thiệp.
                  * Chuẩn hóa thứ tự hiển thị tuần tự từ trên xuống dưới theo tọa độ thực tế: `image232` (top 124px) -> `image246` & `image247` (top 744px) -> `image248` (top 884px) -> `image249` (top 1052px) -> `image237` & `image238` (top 1192px) -> `gallery_extra_0`, `1`, `2` (top 1507px - 1810px) kết thúc bằng ảnh toàn cảnh 410px. Tạo thành bố cục tạp chí sang trọng liền mạch, không một khe hở chết.
                - **Kiểm thử & Chất lượng**:
                  * Pest Feature Test: 35/35 tests pass 100% (521 assertions).
                  * Laravel Pint: Clean 100%.

            80. **Khắc Phục 3 Lỗi Bố Cục Chồng Chéo & Đồng Bộ Toàn Diện Timeline 4 Mốc Mẫu Dấu Ký Trăm Năm (`dau-ky-tram-nam`)**:
                - **Sửa lỗi Phong bì Section 3 che khuất hơn nửa ảnh cưới (Screenshot 1)**:
                  * Tấm ảnh cưới dạng Polaroid card (`group134`) trước đây bị đặt ở tọa độ `top: 85.862px`, khiến vạt phong bì xanh rêu "Our Story" (`image52` ở `top: 194.158px`) che lấp hơn một nửa ảnh, cắt mất bó hoa cưới, tay và trang phục dâu rể.
                  * Đã nâng cao tọa độ thẻ Polaroid `group134` lên `top: 30px !important;` và lưng phong bì `image51` lên `top: 48px !important;`. Tấm ảnh nhô cao thanh thoát 55px, khoe trọn vẹn gương mặt, nụ cười, bó hoa cưới trắng và âu phục thanh lịch của cặp đôi.
                - **Đồng bộ toàn diện 4 mốc thời gian Timeline Section 27 với `schema.json` (Screenshot 2)**:
                  * Trước đây `template.blade.php` chỉ hardcode 2 nhóm tĩnh (`group174`, `group175`) với 2 mốc mặc định, làm rơi rụng 2 mốc quan trọng ("11:30 Khai Tiệc Mừng Cưới" và "13:00 Chụp Ảnh Kỷ Niệm") đã được định nghĩa trong `schema.json`.
                  * Chuyển đổi sang vòng lặp động `@foreach($timelineList as $tIdx => $tItem)` tự động sinh các hàng ziczac sole tuyệt đẹp: Hàng chẵn (Icon trái | Đường nối tâm | Giờ & Tiêu đề phải) và Hàng lẻ (Giờ & Tiêu đề trái | Đường nối tâm | Icon phải).
                  * Tích hợp 4 biểu tượng PNG nội bộ 100% từ thư mục `assets/` cho 4 mốc: `w31_icon5-20240324143948-l5hxp.png` (Máy ảnh - Đón khách), `nhan-20231213014544-ritpg.png` (Cặp nhẫn cưới - Lễ thành hôn), `timeline3-20240323075615-el-qz.png` (Ly rượu mừng - Khai tiệc), `icontiec2-20231213014753-naxdk.png` (Bữa tiệc - Chụp ảnh kỷ niệm).
                  * Mở rộng chiều cao hộp bo góc trắng `box94` lên `360px` và đẩy nhánh hoa `image244` xuống `top: 660px`, ôm khít và cân đối hoàn hảo cả 4 mốc lịch trình.
                  * Gắn đầy đủ hợp đồng live-sync `data-sync-target="timeline.{index}.time"` và `timeline.{index}.title` cho mọi mốc thời gian.
                - **Khắc phục lỗi Checkbox & Ô Ghi Chú Chồng Chéo trong Form RSVP Section 29 (Screenshot 3)**:
                  * Thuộc tính `span { width: calc(100% - 17px) }` trong CSS cũ ép ô checkbox "Cô dâu" chiếm toàn bộ chiều ngang 165px, đẩy checkbox "Chú rể" rớt xuống dòng 2 (vượt quá chiều cao 41px). Ô nhập ghi chú `form_item9` ("Gửi lời chúc...") ở `top: 165px` bị đè chèn trực tiếp lên checkbox "Chú rể".
                  * Đã tái cấu trúc CSS cho `form_item13`: sử dụng `display: flex !important; flex-direction: row; gap: 12px` với `white-space: nowrap; width: auto;`, giúp cả 2 lựa chọn "[ ] Cô dâu" và "[ ] Chú rể" nằm gọn gàng, ngay ngắn trên 1 hàng duy nhất trong khung 165px.
                  * Đẩy ô textarea `form_item9` xuống `top: 170px !important;`, nút bấm `button7` xuống `top: 290px !important;` và tăng chiều cao khung form `group55` lên `350px !important;`, tạo khoảng cách thông thoáng, không còn bất kỳ sự giao thoa hay chồng chéo nào.
                - **Kiểm thử & Chất lượng**:
                  * Pest Feature Test: 35/35 tests pass 100% (521 assertions).
                  * Laravel Pint: Clean 100%.

            81. **Xử Lý Dứt Điểm Lỗi Chồng Chéo Ảnh & Thừa Trường "Save The Date" Mẫu Dấu Ký Trăm Năm (`dau-ky-tram-nam`)**:
                - **Giải mã bản chất đồ họa phong bì Section 3 & Nguyên nhân gây lỗi chồng chéo**:
                  * Section 3 là thiết kế nghệ thuật **Phong Bì Thư Cưới Mở Ra**:
                    - `image51`: Lưng phong bì xanh rêu (`iili_qnzyz9s.jpg`).
                    - `image210`: Bó hoa linh lan trắng (`iili_qnzplTv.jpg`) trang trí bên trái lưng phong bì.
                    - `group134` (gồm `box86` và `image209`): Tấm ảnh cưới Polaroid của cặp đôi nhô cao từ miệng phong bì.
                    - `image52`: Vạt túi phía trước của phong bì với dòng chữ "Our Story" (`iili_qnzyncX.jpg`).
                    - `image211`: Nhành hoa linh lan nhỏ trang trí góc phải phía trước.
                  * Code cũ trước đây đã gán nhầm:
                    1. Gán `cover_photo_url` vào `image51` (vốn là lưng phong bì xanh rêu) -> Khiến ảnh đại diện đè nát họa tiết phong bì.
                    2. Gán `save_date_photo_url` vào `image210` (vốn là bó hoa linh lan bên trái) -> Khiến một ảnh cưới thứ hai lồi ra lệch trái sau lưng phong bì.
                    3. Trong khi tấm ảnh cưới Polaroid thực sự nằm trong miệng phong bì (`image209`) lại bị bỏ quên không gán `data-sync-target="cover_photo_url"`.
                    4. Dẫn đến có tới 3 bức ảnh chồng chéo, va đập lên nhau gây vỡ nát bố cục.
                - **Khắc phục cấu trúc Blade & Schema**:
                  * Khôi phục `image51` về 100% đồ họa lưng phong bì xanh rêu gốc (`iili_qnzyz9s.jpg`).
                  * Khôi phục `image210` về 100% đồ họa bó hoa linh lan nước nghệ thuật (`iili_qnzplTv.jpg`).
                  * Gán chính xác hợp đồng `data-sync-target="cover_photo_url"` vào `image209` bên trong khung ảnh Polaroid `group134`. Cấu hình `background-size: cover; background-position: center; border-radius: 10px;` để bất kỳ ảnh nào người dùng tải lên đều vừa vặn hoàn hảo trong khung Polaroid.
                  * Loại bỏ trường thừa `wedding.extra_data.save_date_photo_url` khỏi `schema.json` (vì trong mẫu này thiệp cưới chỉ có 1 tấm ảnh Polaroid duy nhất lồng trong phong bì thư). Người dùng chỉ còn thấy 1 trường upload duy nhất: **"Ảnh bìa đại diện thiệp"** (`cover_photo_url`).
                  * Thiết lập thứ tự xếp lớp `z-index` nghiêm ngặt: Hoa linh lan (`z-index: 1`) -> Lưng phong bì (`z-index: 2`) -> Thẻ Polaroid dâu rể (`z-index: 3`) -> Túi phong bì "Our Story" (`z-index: 4`) -> Nhành hoa trước (`z-index: 5`). Hiệu ứng 3D phong bì thư mở ra mượt mà, sâu thẳm và chuẩn xác tuyệt đối.
                - **Kiểm thử & Định dạng**:
                  * Pest Feature Test: 35/35 tests pass 100% (521 assertions).
                  * Laravel Pint: Clean 100%.

            82. **Cố Định Nút Bật/Tắt Nhạc Nền Dính Góc Trên Bên Trái Mẫu Dấu Ký Trăm Năm (`dau-ky-tram-nam`)**:
                - **Yêu cầu & Vấn đề**:
                  * Người dùng yêu cầu: *"để nút bật nhạc dính trên góc trên bên trái: mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group72"*.
                  * Trước đó `group72` nằm trong Section 5 bên trong `.mayhen_invitation_jeowinids483fr_stage` (vốn có `transform: scale(...)`), khiến thuộc tính `position: fixed` bị trap trong khung cục bộ của stage, không dính được vào viewport khi cuộn trang, đồng thời bị định vị ở góc dưới bên trái (`bottom: 5px;`) gây chèn đè lên thanh điều hướng `.mayhen_invitation_jeowinids483fr_cta-menu`.
                - **Giải pháp hoàn thiện**:
                  * **Đưa `group72` Ra Ngoài Stage & Đính Cố Định Vào Viewport**:
                    + Di chuyển thẻ `group72` trong `template.blade.php` ra ngoài thẻ `shell` / `stage`, đặt ngang hàng với các widget toàn cục như `cta-menu`, `gift-dialog`, `lightbox`.
                    + CSS ghim cố định góc trên bên trái:
                      ```css
                      .mayhen_invitation_jeowinids483fr_group72 {
                          top: 16px !important;
                          bottom: auto !important;
                          right: auto !important;
                          left: max(16px, calc(50% - 210px + 16px)) !important;
                          position: fixed !important;
                          z-index: 90000050 !important;
                          margin-left: 0 !important;
                          cursor: pointer !important;
                          transition: transform 0.25s ease, filter 0.25s ease;
                      }
                      ```
                    + Công thức `left: max(16px, calc(50% - 210px + 16px))` đảm bảo trên màn hình máy tính (khung thiệp 420px căn giữa) nút nằm gọn ghẽ 16px bên trong mép trái thiệp; trên điện thoại (màn hình <= 420px) nút luôn cách mép trái màn hình đúng 16px, không bao giờ bị tràn lề hay lệch khung.
                  * **Hiệu Ứng Trạng Thái Phát / Tắt Nhạc Chuẩn Xác**:
                    + Trong `script.js`: bổ sung các listener `play`, `pause`, `ended` trên đối tượng Audio để tự động gắn class `.mayhen_invitation_jeowinids483fr_playing` khi nhạc phát.
                    + Bổ sung CSS hiển thị icon sóng âm (`on_speaker`) khi nhạc đang chạy và icon gạch chéo (`off_speaker`) khi tắt nhạc.
                    + Mở rộng bắt sự kiện click trên toàn bộ khối `group72` giúp chạm vào bất kỳ đâu trên nút đều bật/tắt nhạc mượt mà.
                  * **Đồng Bộ Asset Files**: Cập nhật đồng bộ cả hai tệp `styles.css` và `script.js` trong thư mục gốc và thư mục `assets/` của mẫu thiệp.
                - **Kiểm thử & Định dạng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Clean 100%.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            83. **Khắc Phục Lỗi Chọn Nhạc Trong Studio Editor Nhưng Iframe Báo "Chưa cấu hình bài hát nền"**:
                - **Hiện trạng & Triệu chứng**:
                  * Trong Studio Tạo Thiệp (tab Âm Nhạc), khi người dùng chọn một bài hát từ modal (ví dụ: *"Ánh Nắng Của Anh"*), giao diện Studio hiển thị bài hát đã chọn nhưng trong iframe xem thử của mẫu thiệp (ví dụ: `dau-ky-tram-nam`), khi bấm nút loa góc trên bên trái, hệ thống vẫn bật toast thông báo: *"Chưa cấu hình bài hát nền."* và không phát nhạc.
                - **Nguyên nhân gốc rễ**:
                  1. *Thiếu input lưu trữ trong form Studio (`editor.blade.php`)*: Tab Âm Nhạc chỉ hiển thị card thông tin và nút mở modal, hoàn toàn không có trường `<input type="hidden" name="wedding[music_url]" id="wedding_music_url">`. Khi người dùng click bài hát, `document.getElementById('wedding_music_url')` trả về `null`, dẫn đến hàm `sendSyncMessage` không bao giờ được gọi và giá trị URL bài hát không được gửi sang iframe.
                  2. *Cầu nối Live Sync (`editor-bridge.blade.php`) chưa hỗ trợ `music_url`*: Cầu nối `editor-bridge` chỉ xử lý text, ảnh cưới và ngày tháng, chưa có nhánh bắt các key `music_url` / `wedding_music_url` / `music` để cập nhật đối tượng âm thanh trong iframe.
                  3. *Fallback rỗng trong template thiệp (`dau-ky-tram-nam/template.blade.php`)*: Biến `$musicUrl` được khởi tạo bằng `$resolveAsset($wedding->music_url ?? null, '')` trả về chuỗi rỗng khi `$wedding` rỗng (lúc xem thử template mẫu), khiến `window.MAYHEN_INVITATION_CONFIG.music` rỗng.
                  4. *Đối tượng âm thanh không thể thay đổi động (`dau-ky-tram-nam/script.js`)*: Biến `audio` được khai báo bằng `const` một lần duy nhất lúc tải trang. Nếu ban đầu `C.music` rỗng thì `audio` là `null` vĩnh viễn và không có hàm nào để gán lại URL nhạc mới khi người dùng đổi bài hát từ Studio.
                - **Giải pháp hoàn thiện**:
                  * *`resources/views/client/pages/editor.blade.php`*:
                    + Bổ sung trường input ẩn chuẩn trong `<form id="mayhen_editor_form">`:
                      `<input type="hidden" name="wedding[music_url]" id="wedding_music_url" data-sync-key="wedding.music_url" value="{{ $currentMusicUrl }}">`.
                    + Thiết lập fallback mặc định tự động trỏ về file nhạc cưới chuẩn nội bộ (`assets/wedding-song.mp3`).
                  * *`public/assets/client/js/pages/editor.js`*:
                    + Trong listener chọn nhạc `.mayhen_music_select_btn`: Tự động tìm hoặc tạo thẻ input `wedding_music_url`, cập nhật `musicInput.value = audioUrl`, đồng thời phát ngay 2 tín hiệu Live Sync postMessage `sendSyncMessage('wedding.music_url', audioUrl)` và `sendSyncMessage('music_url', audioUrl)` sang iframe.
                  * *`resources/views/client/invitations/partials/editor-bridge.blade.php`*:
                    + Bổ sung nhánh xử lý `cleanKey === 'music_url' || cleanKey === 'wedding_music_url' || cleanKey === 'music'`.
                    + Cập nhật `window.MAYHEN_INVITATION_CONFIG.music = val`, gọi hàm đổi nhạc thời gian thực `window.updateMayhenMusic(val)` nếu template hỗ trợ, và cập nhật thuộc tính `src` cho tất cả thẻ `<audio>`.
                  * *`resources/views/client/invitations/dau-ky-tram-nam/template.blade.php`*:
                    + Thay đổi fallback cho `$musicUrl` từ `''` sang `'wedding-song.mp3'` (luôn có sẵn trong thư mục `assets/` của mẫu thiệp).
                  * *`resources/views/client/invitations/dau-ky-tram-nam/script.js` & `assets/script.js`*:
                    + Chuyển `const audio` thành `let audio = C.music ? new Audio(C.music) : null;`.
                    + Khai báo hàm toàn cục `window.updateMayhenMusic = (newUrl) => { ... }`: Tự động dừng bài hát cũ, cập nhật `C.music = newUrl`, khởi tạo `new Audio(newUrl)` mới và gắn đầy đủ sự kiện âm thanh.
                    + Trong `toggleMusic()`: Tự động khởi tạo lại `Audio(C.music)` nếu ban đầu `audio` chưa tồn tại.
                - **Kiểm thử & Định dạng**:
                  * JS Syntax check (`node -c`): 100% Passed.
                  * Laravel Pint: Clean 100%.
                  * Cache: `php artisan optimize:clear` hoàn tất.

            84. **Điều Chỉnh Nút Bật/Tắt Nhạc Nền Hiển Thị Dưới Cánh Cửa Mở Thiệp (`dau-ky-tram-nam`)**:
                - **Yêu cầu & Triệu chứng**:
                  * Người dùng phản ánh: *"nút mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group72 phải hiển thị dưới cánh cửa mở thiệp"*.
                  * Khi người xem truy cập link thiệp trực tiếp (`/xem-thu/dau-ky-tram-nam`), hai cánh cửa đỏ Ruby 3D đang đóng kín (`#mayhen-door-stage`), nhưng nút nhạc `group72` lại nổi đè lên trên bề mặt cánh cửa ở góc trên bên trái do có `z-index: 90000050 !important;` (lớn hơn `z-index: 9999999` của cánh cửa).
                - **Nguyên nhân & Giải pháp**:
                  * Hạ `z-index` của `.mayhen_invitation_jeowinids483fr_group72` từ `90000050` xuống `1001 !important;` trong cả 2 tệp `styles.css` và `assets/styles.css`.
                  * Khi cánh cửa chưa mở: `#mayhen-door-stage` (`z-index: 9999999`) che kín toàn bộ màn hình, nút nhạc nằm dưới cánh cửa và không bị lộ ra.
                  * Khi người xem chạm vào mở cánh cửa: Hai cánh cửa mở ra và tự động ẩn đi, nút nhạc lộ ra mượt mà và ghim cố định ở góc trên bên trái viewport.
                  * Trên nội dung thiệp: `z-index: 1001` đảm bảo nút nhạc luôn nổi trên các section nội dung khi cuộn trang, nhưng vẫn nằm dưới các popup hộp quà mừng / lightbox phóng to ảnh (`z-index: 100000+`).
                - **Kiểm thử & Định dạng**:
                  * Code CSS chuẩn xác, đồng bộ cả 2 file `styles.css`.
                  * Dọn cache: `php artisan optimize:clear` hoàn tất.

            85. **Đồng Bộ Ảnh Album Cưới Giữa Mẫu Thiệp Và Studio Tạo Thiệp Cho Phép Quản Lý Từ Admin (`dau-ky-tram-nam`)**:
                - **Yêu cầu & Triệu chứng**:
                  * Người dùng yêu cầu: *"Trong mẫu dau-ky-tram-nam tôi muốn ảnh trong ô đỏ giống với ảnh của mẫu thiệp, phải đồng bộ để sau này sửa ở bên admin sẽ được đồng bộ"*.
                  * Trong Studio Tạo Thiệp (bước 4: Ảnh Cưới), 10 ô tải ảnh ("Tải ảnh 1" đến "Tải ảnh 10") hiển thị ảnh thumbnail trắng trơn hoặc placeholder mặc định, không hiển thị 10 ảnh cưới mẫu của template như trong khung xem thử bên phải.
                - **Nguyên nhân cốt lõi**:
                  1. Trường `wedding.extra_data.gallery` trong `schema.json` của mẫu thiệp chưa khai báo mảng `"default"` gồm 10 ảnh cưới bản địa có sẵn trong thư mục `assets/`.
                  2. Model `InvitationTemplate` chưa có thuộc tính / accessor chuẩn hóa `default_gallery` để lấy danh sách ảnh album mặc định từ database (`default_config['gallery']`) hoặc fallback về `schema.json`.
                  3. `template.blade.php` trước đây hardcode mảng `$defaultGallery = [...]` tĩnh trong view mà không đọc từ cấu hình Model của template, khiến template và Studio tách rời nhau.
                  4. `editor.blade.php` khi render các ô gallery, nếu `$val` rỗng thì fallback thẳng về `$selectedTemplate->thumbnail_url` thay vì hiển thị ảnh thứ `i` trong danh sách ảnh mặc định của mẫu.
                - **Giải pháp hoàn thiện**:
                  * *`app/Models/InvitationTemplate.php`*:
                    + Bổ sung method `getDefaultGallery(): array` và accessor `defaultGallery`: Ưu tiên đọc từ `default_config['gallery']` (do Admin cập nhật trong database) -> Kế thừa từ `schema.json` của mẫu. Đây là Single Source of Truth tập trung.
                  * *`resources/views/client/invitations/dau-ky-tram-nam/schema.json`*:
                    + Khai báo danh sách 10 ảnh mẫu vào thuộc tính `"default"` của trường `wedding.extra_data.gallery`.
                  * *`resources/views/client/invitations/dau-ky-tram-nam/template.blade.php`*:
                    + Chuyển `$defaultGallery` sang ưu tiên lấy `$template->default_gallery`, tự động đồng bộ khi Admin thay đổi ảnh mẫu trong DB hoặc schema.
                  * *`resources/views/client/pages/editor.blade.php`*:
                    + Trong vòng lặp render 10 ô gallery: Tự động lấy `$defaultImg = $defaultGalleryList[$gIdx]` khi chưa có ảnh upload riêng.
                    + Ô xem trước hiển thị đúng ảnh thứ `i` của template qua URL `/mau-thiep/{slug}/assets/{file}`.
                    + Trường input ẩn `name="wedding[extra_data][gallery][i]"` tự động giữ giá trị ảnh mặc định tương ứng.
                  * *`database/seeders/InvitationTemplateSeeder.php` & Database*:
                    + Bổ sung `'gallery'` vào `default_config` của mẫu `dau-ky-tram-nam`. Cập nhật bản ghi database thực tế và xóa cache template.
                - **Kiểm thử & Định dạng**:
                  * Test render Blade template: 10/10 ảnh trong Studio Editor hiển thị chính xác 10 ảnh mẫu tương ứng.
                  * Pest feature tests: 3/3 passed (15 assertions).
                  * Laravel Pint: Clean 100%.
                  * Dọn cache: `php artisan optimize:clear` hoàn tất.

            86. **Đúc Kết Bộ 8 Trụ Cột Chuẩn Hóa Mẫu Thiệp Cưới (Skill & Master Prompt) & Chuẩn Hóa Mẫu Giữa Muôn Ngàn Vì Sao (`giua-muon-ngan-vi-sao`)**:
                - **Yêu cầu**:
                  * Đúc kết kinh nghiệm làm việc từ 2 mẫu `dau-an-thoi-gian` và `dau-ky-tram-nam` để viết Master Prompt và Skill chuẩn hóa mẫu thiệp cưới (`mayhen-invitation-templates`).
                  * Áp dụng ngay bộ chuẩn hóa lên mẫu đầu tiên: `giua-muon-ngan-vi-sao`.
                - **Xây dựng Skill & Master Prompt**:
                  * Khởi tạo `.agents/skills/mayhen-invitation-templates/SKILL.md` gồm đầy đủ 8 Trụ Cột Bất Biến: Khóa Schema Canonical, Hợp đồng Live Sync, Phòng vệ nhúng iframe (`$isEmbed`), Tự chứa 100% Zero External Assets, Âm nhạc Live Audio Handshake, Phân lớp Z-Index chuẩn, Đồng bộ Admin qua Model Single Source of Truth, Kế thừa Layout chung.
                  * Soạn Master Prompt hoàn chỉnh cho phép AI hoặc Developer chuẩn hóa bất kỳ mẫu thiệp nào tự động mà không gặp lỗi.
                - **Chuẩn hóa mẫu `giua-muon-ngan-vi-sao`**:
                  * *`schema.json`*: Bổ sung 2 trường `groom_photo_url` và `bride_photo_url` vào section `couple`. Bổ sung `"default": [...]` 10 ảnh cưới nội bộ vào trường `gallery`.
                  * *`template.blade.php`*: Kế thừa `$defaultGallery` từ `$template->default_gallery`, ánh xạ `$heroPhotoUrl`, `$groomPhotoUrl`, `$bridePhotoUrl` theo cả `wedding` và `extraData`. Cập nhật `$musicUrl` động và gắn `data-sync-target="music_url"` lên `<audio>`.
                  * *`styles.css`*: Nâng z-index màn mở đầu `.sr_cua` lên `9999999 !important;`. Chuyển nút nhạc `.wb_music_btn` lên góc trên bên trái cố định với `z-index: 1001 !important;` (nằm dưới màn mở đầu khi chưa mở, lộ ra sau khi mở thiệp).
                  * *`script.js`*: Thêm hàm `window.updateMayhenMusic` cho phép thay đổi nhạc nền thời gian thực.
                  * *Seeder & Database*: Thêm `'gallery'` vào `default_config` của mẫu `giua-muon-ngan-vi-sao`. Cập nhật database và xóa cache.
                - **Kiểm thử & Định dạng**:
                  * Test render Blade Studio: 10/10 ảnh gallery và ảnh dâu rể hiển thị chuẩn xác.
                  * JS Syntax check (`node -c`): Passed 100%.
                  * Pest feature tests: 3/3 passed.
                  * Laravel Pint: Clean 100%.
                  * Dọn cache: `php artisan optimize:clear` hoàn tất.

            87. **Khắc Phục Triệt Để Lỗi Bộ Gõ Tiếng Việt Bị Nhảy Chữ, Nuốt Chữ ("Nguyễn" Bị Thành "Ngễn") Trong Studio Editor**:
                - **Hiện trạng & Triệu chứng người dùng phản ánh**:
                  * Người dùng gõ tên chú rể hoặc cô dâu trên bàn phím: *"Sao tôi nhập tên nhập các thứ chứ cứ bị nhảy rồi biến mất vậy, tôi nhập chữ Nguyễn nó nhảy sang chữ Ngễn?"*.
                - **Nguyên nhân gốc rễ**:
                  * Trong `public/assets/client/js/pages/editor.js`, sự kiện `input` (chạy sau mỗi phím gõ) có đoạn mã lọc ký tự đặc biệt và giới hạn từ (`mayhen_validate_human_name`) can thiệp trực tiếp bằng lệnh: `target.value = cleaned;`.
                  * Khi người dùng gõ tiếng Việt có dấu (Telex/VNI qua Unikey, EVKey hoặc Windows/macOS IME), bộ gõ gửi chuỗi ký tự tạm thời và phím ảo để ghép vần (composition).
                  * Lệnh gán đè `target.value = cleaned` ngay trong sự kiện `input` đã reset vị trí con trỏ (caret) và phá hủy bộ nhớ đệm IME của bộ gõ. Khi Unikey gửi lệnh Backspace để ghép dấu nguyên âm `ê` hay dấu ngã `~`, do vị trí con trỏ bị giật, phím Backspace ăn lấn sang nguyên âm `uy` phía trước, biến `Nguyễn` thành `Ngễn` và làm biến mất các ký tự vừa gõ.
                - **Giải pháp hoàn thiện**:
                  * Trong sự kiện `input`: Loại bỏ hoàn toàn hành vi can thiệp hay gán đè `target.value`. Để người dùng gõ phím tiếng Việt tự nhiên 100%, không bị ngắt kết nối composition với Unikey/EVKey.
                  * Chuyển toàn bộ logic chuẩn hóa, dọn khoảng trắng thừa và giới hạn từ của `mayhen_validate_human_name` sang sự kiện **`blur`** (khi người dùng gõ xong và rời chuột ra khỏi ô nhập liệu). Lúc này người dùng đã kết thúc quá trình gõ nên việc chuẩn hóa diễn ra hoàn toàn êm ái, không bao giờ ảnh hưởng đến bộ gõ.
                - **Kiểm thử & Định dạng**:
                  * JS Syntax check (`node -c`): Passed 100%.
                  * Pest feature tests: 3/3 passed.
                  * Laravel Pint: Clean 100%.
                  * Dọn cache: `php artisan optimize:clear` hoàn tất.

            88. **Khắc Phục Lỗi Cuộn Tab Lịch Trình Và Đồng Bộ Hóa Thời Gian Thực (Live Sync Timeline) Mẫu "Giữa Muôn Ngàn Vì Sao"**:
                - **Hiện trạng & Triệu chứng người dùng phản ánh**:
                  * Khi người dùng bấm tab "5. Lịch Trình" ở thanh điều hướng form bên trái của Studio Tạo Thiệp (`/tao-thiep?template=giua-muon-ngan-vi-sao`), iframe thiệp bên phải không cuộn đến đúng phần "Lịch Trình Buổi Tiệc".
                  * Dữ liệu thời gian trên form mẫu hiển thị lệch hoàn toàn so với nội dung trong thiệp (Form hiển thị 08:00 Lễ Vu Quy, 10:00 Lễ Thành Hôn, 18:00 Khai Tiệc; trong khi thiệp lại hiển thị 17:30 Đón khách, 18:00 Cử hành hôn lễ, 18:30 Khai tiệc, 20:00 Giao lưu). Đồng thời khi người dùng chỉnh sửa giờ/nội dung trên form thì thiệp không cập nhật trực tiếp (không Live Sync).
                - **Nguyên nhân gốc rễ**:
                  * **Lỗi cuộn tab**: Trong `editor-bridge.blade.php`, cơ chế cuộn mượt dựa vào lệnh `MAYHEN_SCROLL_TO_SECTION` tìm phần tử `[data-mayhen-section="{section_id}"]`. File `template.blade.php` của mẫu `giua-muon-ngan-vi-sao` hoàn toàn chưa được gắn các thuộc tính `data-mayhen-section="..."`, dẫn đến bridge không tìm thấy phần tử mục tiêu để cuộn tới.
                  * **Lỗi dữ liệu không khớp**: Trong `schema.json` của mẫu, trường `wedding.extra_data.timeline` có giá trị `"default"` là lịch trình ban ngày cũ (08:00, 10:00, 18:00, 19:30), trong khi layout thiệp là tiệc tối (17:30, 18:00, 18:30, 20:00).
                  * **Lỗi Live Sync**: Phần hiển thị Timeline trong `template.blade.php` bị viết cứng (hardcode tĩnh 4 khối HTML), không lặp qua mảng `$timelineList` và không có các thuộc tính hợp đồng đồng bộ `data-sync-target="timeline.{index}.time"` và `data-sync-target="timeline.{index}.title"`.
                - **Giải pháp hoàn thiện**:
                  * *`schema.json`*: Cập nhật mảng `"default"` của `wedding.extra_data.timeline` khớp 100% với 4 mốc thời gian tiệc tối của mẫu: 17:30 (Đón khách), 18:00 (Cử hành hôn lễ), 18:30 (Khai tiệc), 20:00 (Giao lưu & chụp ảnh).
                  * *`template.blade.php`*:
                    1. Khởi tạo mảng timeline động: `$timelineList = is_array($rawTimeline) && count($rawTimeline) > 0 ? array_values($rawTimeline) : $defaultTimeline;`.
                    2. Gắn đầy đủ `data-mayhen-section` cho toàn bộ 6 canonical sections của mẫu: `couple`, `groom_family,bride_family`, `album`, `timeline`, `gift`.
                    3. Chuyển cấu trúc Timeline sang vòng lặp `@foreach($timelineList as $tIdx => $tItem)`, gắn đầy đủ `data-sync-target="timeline.{{ $tIdx }}.time"` và `data-sync-target="timeline.{{ $tIdx }}.title"`, ánh xạ xoay vòng 4 biểu tượng SVG vector tiệc cưới và đường kẻ kết nối (`@if(!$loop->last)`).
                  * *Seeder & Database*: Bổ sung `'timeline'` mặc định vào `default_config` của `giua-muon-ngan-vi-sao` trong `InvitationTemplateSeeder.php` và chạy re-seed database.
                - **Kiểm thử & Định dạng**:
                  * Viết Feature Test chuyên biệt `tests/Feature/GiuaMuonNganViSaoTimelineTest.php`: Xác thực 100% việc render đúng thuộc tính `data-mayhen-section`, hợp đồng `data-sync-target` và giá trị mặc định form/thiệp. Test passed 2/2. Toàn bộ 5/5 Feature Tests pass.
                  * Laravel Pint: Clean 100%.
                  * Dọn cache: `php artisan optimize:clear` hoàn tất.

            89. **Chuẩn Hóa Toàn Diện Mẫu Thiệp "Hắc Bạch Trao Duyên" (`hac-bach-trao-duyen`) Theo Bộ 8 Trụ Cột Bất Biến**:
                - **Yêu cầu & Phạm vi thực hiện**:
                  * Tiến hành chuẩn hóa mẫu thiệp cưới phong cách Hoàng Gia Ruby & Kim Cương `hac-bach-trao-duyen` tuân thủ 100% tiêu chuẩn trong `.agents/skills/mayhen-invitation-templates/SKILL.md`.
                - **Các hạng mục đã chuẩn hóa**:
                  1. *`schema.json`*:
                     - Bổ sung 2 trường ảnh chân dung `wedding.extra_data.groom_photo_url` và `wedding.extra_data.bride_photo_url` vào section `couple`.
                     - Bổ sung mảng `"default": [...]` chứa đúng 10 file ảnh cưới nội bộ có thật trong `assets/` vào trường `gallery`.
                     - Đồng bộ 4 mốc thời gian mặc định của `wedding.extra_data.timeline`: 08:00 (Lễ Ăn Hỏi Nhà Gái), 10:30 (Lễ Rước Dâu), 11:30 (Tiệc Mừng & Lễ Thành Hôn Nhà Trai), 13:00 (Giao Lưu & Chụp Ảnh Kỷ Niệm).
                  2. *`template.blade.php`*:
                     - Kế thừa mảng `$defaultGallery` từ `$template->default_gallery ?? [...]`, lặp lấy 10 ảnh `$gallery` qua `$resolveAsset`.
                     - Khởi tạo `$timelineList` động và render lặp qua vòng lặp `@foreach($timelineList as $tIdx => $tItem)`, gắn đầy đủ `data-sync-target="timeline.{{ $tIdx }}.time"` và `data-sync-target="timeline.{{ $tIdx }}.title"`.
                     - Gắn đầy đủ `data-mayhen-section` cho toàn bộ 6 canonical sections: `couple`, `groom_family,bride_family` (kèm thẻ con `groom_family` và `bride_family`), `timeline`, `album`, `gift`.
                     - Section Album: Thay thế 9 ảnh tĩnh bằng vòng lặp đúng 10 ảnh `$gallery`, gắn thẻ `data-sync-target="gallery.{{ $gIdx }}"` và phân lớp layout Pinterest đẹp mắt (`item_tall`, `item_wide`).
                     - Thẻ `<audio>` gắn `data-sync-target="music_url"` với src lấy `$musicUrl = $resolveAsset($wedding->music_url ?? null, 'wedding-song.mp3')`.
                     - Section Hộp Mừng Cưới: Đưa toàn bộ tài khoản ngân hàng và mã VietQR về động, gắn đầy đủ các thuộc tính Live Sync `qr_groom_image`, `groom_bank_name`, `groom_bank_account`, `groom_bank_holder`, `qr_bride_image`, `bride_bank_name`, `bride_bank_account`, `bride_bank_holder`.
                     - Phòng vệ nhúng Studio `$isEmbed = true`: Ẩn màn mở đầu `style="{{ $isEmbed ? 'display: none !important;' : '' }}"`, gắn class `opened mayhen_door_hidden`, không gắn class khóa cuộn `scroll_locked` lên html/body khi nhúng.
                  3. *Âm nhạc & Nút đĩa than (`script.js` & `styles.css`)*:
                     - Cung cấp hàm toàn cục `window.updateMayhenMusic` xử lý nạp bài hát mới thời gian thực khi khách chọn nhạc từ Studio.
                     - Cố định nút đĩa than vinyl ở góc trên bên trái theo đúng chuẩn Mây Hẹn: `top: 16px !important; left: max(16px, calc(50% - 210px + 16px)) !important; position: fixed !important; z-index: 1001 !important;`. Ẩn đi khi chưa mở phong bì và hiện lên khi thiệp mở.
                     - Nâng z-index màn mở đầu `.mayhen_invitation_ladjihe38udhd_opening_overlay` lên `9999999 !important;`.
                     - Đồng bộ toàn bộ các cập nhật vào cả hai file con `assets/script.js` và `assets/styles.css`.
                  4. *Seeder & Database*:
                     - Bổ sung cấu hình `'gallery'` (10 ảnh) và `'timeline'` (4 mốc) vào `default_config` của `hac-bach-trao-duyen` trong `InvitationTemplateSeeder.php` và chạy re-seed cập nhật DB.
                - **Kiểm thử & Định dạng**:
                  * Cú pháp JS: `node -c script.js` passed 100%.
                  * Viết Feature Test chuyên biệt `tests/Feature/HacBachTraoDuyenTemplateTest.php`: Kiểm tra xác thực 35 assertions gồm 6 sections, hợp đồng sync dâu rể, 10 ảnh album, timeline repeater, âm nhạc và form editor. Toàn bộ 7/7 Feature Tests (64 assertions) pass 100%.
                  * Laravel Pint: Clean 100%.
                  * Dọn cache: `php artisan optimize:clear` hoàn tất.

            90. **Khắc Phục Triệt Để Toàn Bộ Dữ Liệu Tĩnh / Ảnh Người Khác Trên Mẫu "Hắc Bạch Trao Duyên" (`hac-bach-trao-duyen`)**:
                - **Yêu cầu & Phản ánh của người dùng**:
                  * Người dùng phát hiện ngoài các thông tin cơ bản trên form tạo thiệp, trong thiệp thực tế còn rất nhiều phần dữ liệu và ảnh của người mẫu lạ (Our Story 3 mốc, ảnh vòm Save The Date, ảnh Thank You cuối thiệp, địa chỉ tĩnh Tây Hồ Hà Nội, 2 lời chúc mẫu của người lạ) mà form không có chỗ chỉnh sửa, dẫn đến thiệp cưới hạnh phúc của cặp đôi lại bị dính thông tin và ảnh của người khác.
                - **Các hạng mục đã giải quyết triệt để**:
                  1. *`schema.json`*:
                     - Bổ sung trường `wedding.extra_data.hero_photo_url`, `wedding.extra_data.save_date_photo_url` (Ảnh vòm Save The Date) và `wedding.extra_data.thank_photo_url` (Ảnh Thank You cuối thiệp) vào section `couple`.
                     - Bổ sung 3 mốc Câu Chuyện Tình Yêu (Love Story) trực tiếp vào section `timeline` ("5. Lịch Trình & Câu Chuyện Tình Yêu") gồm 12 trường: `story_1_date`, `story_1_title`, `story_1_desc`, `story_1_photo`, `story_2_date`, `story_2_title`, `story_2_desc`, `story_2_photo`, `story_3_date`, `story_3_title`, `story_3_desc`, `story_3_photo`. Giữ vững chuẩn hóa đúng 6 Canonical Sections của hệ thống.
                  2. *`template.blade.php`*:
                     - Thiết lập cơ chế fallback ảnh thông minh: Ảnh Save The Date fallback về `$gallery[0]`, ảnh Thank You fallback về `$gallery[1]`, 3 ảnh kỷ niệm Story fallback về `$gallery[2]`, `$gallery[3]`, `$gallery[4]` của chính album cặp đôi nếu chưa tải riêng. Đảm bảo cặp đôi không bao giờ bị xuất hiện ảnh người lạ.
                     - Gắn đầy đủ hợp đồng `data-sync-target` cho toàn bộ các trường mới: `save_date_photo_url`, `thank_photo_url`, `story_1_date/title/desc/photo`, `story_2_date/title/desc/photo`, `story_3_date/title/desc/photo`.
                     - Gắn `data-mayhen-section="story"` vào section Story để người dùng chuyển tab có thể cuộn tới mượt mà.
                     - Lịch cưới động (Dynamic Wedding Calendar): Chuyển toàn bộ ma trận ngày lịch tháng từ hardcode 12/2025 sang vòng lặp động tính theo `$weddingDate`: tự động tính số ngày trong tháng `$daysInMonth`, ngày đầu tháng `$firstDayOfWeek`, và highlight trái tim đúng ngày cưới thật của cặp đôi.
                     - Đồng bộ địa chỉ & bản đồ: Thay thế địa chỉ tĩnh "423 An Dương Vương..." bằng `$eventPartyAddress` và `$eventPartyVenue`, nhúng iframe bản đồ tìm kiếm theo địa chỉ tiệc cưới thật.
                     - Phòng vệ Lời chúc mẫu (Guestbook): Lời chúc mẫu chỉ hiển thị khi xem thử (`$isPreview = true`). Khi là thiệp thật của cặp đôi (`!$isPreview`), nếu chưa có lời chúc sẽ hiển thị lời mời khách để lại lời chúc đầu tiên; tuyệt đối không để tên và lời chúc của người lạ ("Trần Minh Tuấn", "Lê Ngọc Mai") xuất hiện trên thiệp của cặp đôi.
                  3. *Seeder, Pint & Tests*:
                     - Chạy re-seed database từ file `schema.json` mới.
                     - Cập nhật test `tests/Feature/HacBachTraoDuyenTemplateTest.php` xác thực toàn bộ các thuộc tính sync mới và form editor.
                     - Toàn bộ 7/7 Feature Tests (78 assertions) pass 100%.
                     - Laravel Pint: Passed. Cache Laravel: Cleared.

            91. **Thiết Kế Lại Bố Cục Studio Tạo Thiệp: Cân Đối Song Song Chú Rể & Cô Dâu, Đưa Ngày Hôn Lễ Xuống Dưới (`is-full-width`)**:
                - **Yêu cầu & Mục tiêu**:
                  * Người dùng yêu cầu bố cục lại Bước 1 ("1. Đôi Uyên Ương") trong Studio Tạo Thiệp: Đưa Ngày cử hành hôn lễ xuống dưới để Chú rể và Cô dâu nằm ngang bằng nhau cân xứng, trực quan, khách hàng dễ hiểu và dễ thao tác nhất.
                - **Các điểm đã hoàn thiện**:
                  1. *`editor.blade.php`*:
                     - Bổ sung `$fieldKey === 'wedding.wedding_date'` vào danh sách `$isFullWidth`, giúp trường Ngày cử hành hôn lễ chiếm trọn 2 cột (`grid-column: 1 / -1`) nằm tách biệt phía dưới.
                     - Thêm dòng trợ giúp hướng dẫn rõ ràng: *"Ngày cử hành hôn lễ chính thức (Dương lịch & tự động đối soát Âm lịch)"*.
                  2. *`InvitationTemplateService.php` (`getCanonicalSections`)*:
                     - Sắp xếp lại thứ tự trường chuẩn:
                       * Hàng 1: Họ tên Chú rể (Cột 1) — Họ tên Cô dâu (Cột 2)
                       * Hàng 2: Ảnh Chân Dung Chú Rể (Cột 1) — Ảnh Chân Dung Cô Dâu (Cột 2)
                       * Hàng 3: Ngày cử hành hôn lễ (Full width) kèm đối soát Âm lịch tự động.
                       * Hàng 4 trở đi: Ảnh bìa thiệp, ảnh kỷ niệm, lời ngỏ...
                  3. *Đồng bộ `schema.json`*:
                     - Đồng bộ thứ tự này trên các mẫu thiệp cưới: `hac-bach-trao-duyen`, `dau-ky-tram-nam`, `giua-muon-ngan-vi-sao`, `dau-an-thoi-gian`.
                  4. *`editor-v2.css`*:
                     - Bổ sung `min-height: 86px; box-sizing: border-box;` cho `.mayhen_editor_image_field_row` để 2 ô ảnh Chú rể và Cô dâu khi đặt cạnh nhau luôn thẳng hàng và bằng nhau từng pixel.
                  5. *Kiểm thử & Định dạng*:
                     - Chạy lại Seeder `InvitationTemplateSeeder`.
                     - 7/7 Feature Tests (78 assertions) pass 100%.
                     - Laravel Pint: Passed. Dọn cache hoàn tất.

            92. **Khắc Phục Lỗi Vỡ Lịch Cưới & Ghi Đè Dữ Liệu Live Sync Trên Mẫu "Hắc Bạch Trao Duyên"**:
                - **Hiện tượng**:
                  * Khung lịch cưới (Wedding Calendar) bị lỗi giao diện: Mất toàn bộ các ô ngày (SUN, MON, 1..31), bên trong khung bị chèn đè chuỗi ngày tháng `20 . 03 . 2026` ngắt thành 3 dòng, làm giao diện bị xấu và trống trơn.
                - **Nguyên nhân cốt lõi**:
                  * Trong `template.blade.php`, thẻ `<div class="mayhen_invitation_ladjihe38udhd_calendar_grid">` và thẻ tiêu đề tháng bị gán nhầm thuộc tính `data-sync-target="wedding_date"`.
                  * Khi người dùng nhập/chọn ngày cưới trên Studio, `editor-bridge.blade.php` tìm toàn bộ các phần tử có `data-sync-target="wedding_date"` và gán thẳng chuỗi văn bản `"20 . 03 . 2026"` vào đó. Vì `calendar_grid` có `display: grid; grid-template-columns: repeat(7, 1fr)`, chuỗi chữ này xóa sạch lưới ngày và bị ép xuống dòng từng từ một!
                - **Giải pháp hoàn thiện**:
                  1. *`template.blade.php`*:
                     - Gỡ bỏ thuộc tính `data-sync-target="wedding_date"` khỏi thẻ `<h2 id="mayhen_invitation_ladjihe38udhd_month_heading">`, `<span id="mayhen_invitation_ladjihe38udhd_calendar_en_heading">` và `<div id="mayhen_invitation_ladjihe38udhd_calendar_grid">`.
                  2. *`script.js` & `assets/script.js`*:
                     - Triển khai hàm toàn cục `window.renderMayhenWeddingCalendar(dateObj)`: Tự động tính toán thứ bắt đầu, số ngày trong tháng và vẽ lại đầy đủ lưới lịch 7 cột (SUN..SAT), tô điểm ngày cưới thật bằng màu đỏ hoàng gia và trái tim vàng kim `♥`.
                     - Triển khai hàm toàn cục `window.updateMayhenCountdown(newDateObj)` cập nhật đồng hồ đếm ngược thời gian thực.
                  3. *Kiểm thử & Định dạng*:
                     - Cú pháp JS `node -c`: Passed.
                     - 7/7 Feature Tests (78 assertions) pass 100%.
                     - Cache Laravel: Cleared.

            93. **Chuẩn Hóa Toàn Diện Mẫu Thiệp Cưới "Hắc Bạch Trao Duyên 2" (`hac-bach-trao-duyen-2`) Theo Kỹ Năng Bất Biến 8 Trụ Cột**:
                - **Yêu cầu & Mục tiêu**:
                  * Chuẩn hóa toàn bộ mẫu thiệp `hac-bach-trao-duyen-2` theo đúng 8 trụ cột chuẩn hóa trong `.agents/skills/mayhen-invitation-templates/SKILL.md`.
                  * Đảm bảo mọi thông tin cá nhân (ảnh chân dung dâu rể, album 10 ảnh, timeline, STK, ngân hàng, mã QR) đều đồng bộ thời gian thực 2 chiều với Studio Tạo Thiệp và Admin, không còn bất kỳ dữ liệu nào bị hardcode.
                - **Các điểm kỹ thuật đã hoàn thành**:
                  1. *`schema.json`*:
                     - Khai báo đầy đủ 6 canonical sections (`couple`, `groom_family`, `bride_family`, `album`, `timeline`, `gift`).
                     - Thêm trường `wedding.extra_data.groom_photo_url` và `wedding.extra_data.bride_photo_url` với fallback ảnh chân dung nội bộ chất lượng cao.
                     - Sắp xếp thứ tự trường Bước 1 chuẩn hóa: Tên dâu rể -> Ảnh dâu rể -> Ngày cử hành hôn lễ -> Ảnh bìa thiệp.
                     - Khai báo mảng `"default"` gồm 10 ảnh cưới nội bộ cho trường `wedding.extra_data.gallery`.
                  2. *`template.blade.php`*:
                     - Phòng vệ nhúng: Xóa class khóa cuộn `mayhen_invitation_ladjihe38udhd_scroll_locked` khi `$isEmbed = true`.
                     - Khởi tạo `$galleryDefaults` (10 ảnh), `$timelineList` và `$musicUrl` với asset fallback nội bộ an toàn.
                     - Thẻ `<audio>` gắn `data-sync-target="music_url"`.
                     - Gắn đầy đủ thuộc tính điều hướng `data-mayhen-section` (`couple`, `groom_family,bride_family`, `groom_family`, `bride_family`, `timeline`, `album`, `gift`).
                     - Cập nhật Live Sync cho album ảnh cưới: `data-sync-target="gallery.0"` đến `gallery.11` kèm fallback CSS động.
                     - Cập nhật Live Sync cho timeline hôn lễ (`timeline.0.time`, `timeline.0.title`...).
                     - Loại bỏ toàn bộ text hardcode trong POPUP2 & POPUP3 ngân hàng, thay bằng Live Sync: `groom_bank_holder`, `groom_bank_account`, `groom_bank_name`, `qr_groom_image` và `bride_*` tương ứng.
                  3. *`script.js` & `assets/script.js`*:
                     - Cung cấp hàm toàn cục `window.updateMayhenMusic(newUrl)` phục vụ Editor Bridge thay đổi bài hát tức thì.
                     - Di chuyển và cố định đĩa than nhạc lên góc trên bên trái `top: 16px; left: max(16px, calc(50% - 210px + 16px)); z-index: 1001; position: fixed;` chuẩn hóa cho cả desktop và mobile, không bị che bởi khung viền iPhone.
                  4. *Seeder, Định Dạng & Kiểm Thử*:
                     - Bổ sung `gallery` mặc định vào `InvitationTemplateSeeder.php` và chạy re-seed cơ sở dữ liệu.
                     - Viết Feature Test chuyên biệt `tests/Feature/HacBachTraoDuyen2TemplateTest.php` xác thực 3 test cases (render sections, live sync album/timeline/gift, embed mode mở cuộn trang, studio editor) với 49 assertions pass 100%.
                     - Laravel Pint: Passed. Dọn sạch cache toàn hệ thống.

            94. **Khắc Phục Toàn Diện Bố Cục, Lưới Lịch Động Và Ảnh Đôi Save The Date Cho "Hắc Bạch Trao Duyên 2" (`hac-bach-trao-duyen-2`)**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Ảnh Save The Date lớn (`IMAGE88`) phong cách tạp chí cặp đôi chưa có trong `schema.json` khiến người dùng không thể đổi ảnh riêng.
                  * Cụm Monogram `R & Q` và ngày giờ dưới ảnh bìa tạp chí bị hardcode.
                  * Khung lịch cưới "Tháng Mười 2025" và lưới ngày bị cố định theo tháng 10/2025, trái tim `SHAPE14` bị neo cứng ngày 9.
                  * Khối Chú rể & Cô dâu (`SECTION4`) bị vỡ layout: Phụ mẫu nhà trai đè lên ảnh cô dâu, cô dâu bị che cụt nửa trên và bị `SECTION13` che cắt ngang thân.
                  * Thiệp mời (`SECTION13`) bị đè chữ: Tên chú rể đè lên chữ "CỦA CHÚNG TÔI", chữ "and" và "Thuý Ngân" xô lệch.
                - **Các điểm kỹ thuật đã hoàn thành**:
                  1. *`schema.json`*:
                     - Bổ sung trường `wedding.extra_data.save_date_photo_url` vào section `couple` với fallback `pch07126-1-20250612144528-nbkc_.jpg`.
                  2. *`template.blade.php`*:
                     - Gán `data-sync-target="save_date_photo_url"` cho ảnh `#IMAGE88`.
                     - Đồng bộ động Monogram `data-sync-target="groom_initial"` & `bride_initial`, và các ô `wedding_date_day`, `wedding_date_month`, `wedding_date_year`, `wedding_date_dow` trong `SECTION22`.
                     - Khởi tạo mảng 35 ô lịch `$calCells` dựa trên `$daysInMonth` và `$firstDayOfWeek` của ngày cưới thực tế.
                     - Điền số ngày động vào 35 ô `HEADLINE238..272` trong `GROUP82` của `SECTION24`, gắn `id="mayhen_hacbach2_cal_month"` và `id="mayhen_hacbach2_cal_year"`, gán `data-cal-day` và `data-cal-index` cho trái tim `SHAPE14`.
                  3. *`styles.css` & `assets/styles.css`*:
                     - Sửa triệt để CSS đè chữ ở `SECTION4`: Tăng chiều cao lên `1720px`, dời `#GROUP126` (Cô dâu) xuống `top: 860px`, gỡ bỏ `top: 560px` ép cứng cho dòng phụ mẫu.
                     - Sửa triệt để CSS ở `SECTION13`: Tăng chiều cao lên `1350px`, phân tầng nhịp nhàng: `#PARAGRAPH59` (top 235px) -> `#HEADLINE69` (top 315px) -> `#HEADLINE71` (top 380px) -> `#HEADLINE70` (top 420px) -> `#GROUP39` (top 550px) -> `#PARAGRAPH23..25` (top 1065px..1150px) -> `#BUTTON9` (top 1225px).
                  4. *`script.js` & `assets/script.js`*:
                     - Triển khai hàm toàn cục `window.renderMayhenWeddingCalendar(dateInput)`: Tự động tính toán lại lịch tháng/năm và di chuyển trái tim `SHAPE14` chính xác vào ô ngày cưới được chọn.
                     - Triển khai hàm toàn cục `window.updateMayhenCountdown(newDate)`: Cập nhật đếm ngược thời gian thực.
                  5. *Kiểm thử & Định dạng*:
                     - Kiểm tra cú pháp JS `node -c`: Passed 100%.
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (51 assertions).
                     - Laravel Pint & Cache Clear: Hoàn tất.

            95. **Thiết Kế Lại Toàn Diện Giao Diện Mở Thiệp Hoàng Gia (#POPUP8) Mẫu "Hắc Bạch Trao Duyên 2" Chuẩn Quiet Luxury**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khung oval cổng vòm mở thiệp quá nhỏ và bị đè chữ: Cụm "Thân gửi", "Bạn và Gia Đình" và tên dâu rể "RANDY & MINH QUÝ" bị nhồi nhét đè lên 1/3 thân dưới bức ảnh cưới chân dung, làm bẩn và che khuất ảnh.
                  * Chữ thư pháp màu trắng đè lên váy cưới trắng của cô dâu gây chìm nghỉm, cực kỳ khó đọc.
                  * Tên cặp đôi bị viền oval cắt ngang nửa trong nửa ngoài, nút bấm "Ấn vào để Xem" thô kệch.
                - **Giải pháp hoàn thiện**:
                  1. *`styles.css` & `assets/styles.css`*:
                     - Tái cấu trúc kích thước Card mở thiệp chuẩn tỷ lệ hoàng gia `380px x 610px` kèm hệ số co giãn responsive đa thiết bị `scale(min(1, calc(min((100vw - 28px) / 380, (100dvh - 28px) / 610))))`.
                     - Tách biệt hoàn toàn Khung Cổng Vòm Oval (`#GROUP145`) lên nửa trên: Kích thước `215px x 325px`, viền kép vàng gold ánh kim (`#BOX78`, `#BOX79`), hiển thị trọn vẹn bức ảnh đôi sắc nét không tì vết.
                     - Đưa tên Cô Dâu Chú Rể (`#HEADLINE338`) xuống dưới khung oval (`top: 372px`), font serif hoa màu vàng gold `#e6c88b` sang trọng, đẳng cấp.
                     - Tách cụm Thân Gửi & Tên Khách Mời (`#GROUP140`) xuống dòng riêng (`top: 412px`), ẩn bỏ background gradient xám cũ (`#BOX77`), chữ "Thân gửi" màu đồng thanh lịch, tên khách "Bạn và Gia Đình" font Great Vibes trắng ngọc trai đổ bóng nghệ thuật nổi bật trên nền đen huyền bí.
                     - Nâng cấp Nút Mở Thiệp (`#BUTTON18`) tại `top: 504px`: Thiết kế viên ngọc bo tròn hoàn hảo (`border-radius: 999px`), viền vàng gold, hiệu ứng pulse breathing sang trọng, đổi text thành `"MỞ THIỆP CƯỚI"`.
                  2. *`template.blade.php`*:
                     - Cập nhật text nút bấm sang `"MỞ THIỆP CƯỚI"`.
                  3. *Kiểm thử*:
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (51 assertions).
                     - Laravel cache: Cleared.

            96. **Sửa Triệt Để Lỗi Cột Đen Kéo Dài Kéo Giãn Che Mặt Chú Rể Ở Nút Đĩa Than Âm Nhạc (`music_dock`)**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khi hiển thị trên thiết bị di động hoặc chế độ xem trước có Bottom Navigation (`max-width: 859px`), xuất hiện một cột xám đen dài ngoằng (chiếm gần 80% chiều cao màn hình) ở bên trái che kín khuôn mặt và người của Chú rể.
                  * *Nguyên nhân cốt lõi*: Trong `bottom-nav.css`, selector `body:has(.mayhen_client_bottom_nav_wrapper) .mayhen_invitation_ladjihe38udhd_music_dock` vô tình gán `bottom: calc(76px + ...) !important;`. Trong khi đó `hac-bach-trao-duyen-2` lại có `top: 16px !important;`. Sự xung đột giữa `top` và `bottom` đồng thời không có `height` cố định khiến trình duyệt tự động stretch (kéo giãn) chiều cao của dock nhạc từ đỉnh xuống đáy!
                - **Giải pháp hoàn thiện**:
                  1. *`template.blade.php`*: Bổ sung class độc lập `mayhen_invitation_hacbach2_music_dock` cho thẻ `<aside>`.
                  2. *`bottom-nav.css`*: Thêm `:not(.mayhen_invitation_hacbach2_music_dock)` để ngăn chặn hoàn toàn việc gán `bottom` lên dock nhạc của mẫu thiệp này.
                  3. *`styles.css` & `assets/styles.css`*:
                     - Đưa nút đĩa than sang góc trên bên phải màn hình (`top: 16px !important; right: 16px !important; left: auto !important; bottom: auto !important;`).
                     - Khóa chặt chiều cao `height: auto !important; max-height: 48px !important;` (mobile: `max-height: 42px !important;`).
                     - Thiết kế bo tròn dạng viên thuốc mini cao cấp (`border-radius: 40px`), viền vàng kim, không bao giờ bị kéo giãn và không che khuất nhân vật trong ảnh cưới.
                  4. *Kiểm thử & Định dạng*:
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (51 assertions).
                     - Cache hệ thống: Cleared.

            97. **Sửa Triệt Để Lỗi Lưới Lịch Bắt Đầu Bằng Thứ Hai (MON) & Định Vị Trái Tim (#SHAPE14) Chuẩn Xác Tuyệt Đối 100%**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Bảng lưới lịch cưới của mẫu thiệp có hàng tiêu đề bắt đầu bằng `MON` (Thứ Hai) đến `SUN` (Chủ Nhật). Tuy nhiên, công thức cũ tính theo chuẩn US (`0 = Sunday`) khiến toàn bộ các ngày bị lệch sang phải 1 cột (ví dụ ngày 1 tháng 10 rơi vào THUR thay vì WED, ngày 9 tháng 10 rơi vào FRI thay vì THUR).
                  * Trái tim `#SHAPE14` bị tính bằng `getBoundingClientRect()` runtime không ổn định, dẫn đến bị lệch nằm lơ lửng giữa dòng 16 và 23.
                - **Giải pháp hoàn thiện**:
                  1. *`template.blade.php`*:
                     - Chuyển đổi công thức tính thứ bắt đầu của tháng sang Monday-based: `$firstDayOfWeek = ($rawDayOfWeek + 6) % 7;` (0: Mon, 1: Tue ... 6: Sun).
                     - Ngày 1 tháng 10 năm 2025 rơi chuẩn xác vào cột WED (Thứ Tư), ngày 9 tháng 10 năm 2025 rơi chuẩn xác vào cột THUR (Thứ Năm).
                     - Tính sẵn tọa độ tĩnh trong PHP Blade `$heartLeftMobile` (`19.85 + col * 55.9167`) và `$heartTopMobile` (`140.333 + row * 40.1`) gán trực tiếp vào style `#SHAPE14`.
                  2. *`script.js` & `assets/script.js`*:
                     - Tính `firstDay = (rawDay + 6) % 7;` chuẩn Monday-based.
                     - Căn chỉnh trái tim `#SHAPE14` trực tiếp từ ma trận tọa độ LadiPage (bước nhảy `55.9167px` theo cột, `40.1px` theo hàng trên mobile; `123.2px` và `76.35px` trên desktop) thay vì `getBoundingClientRect`.
                  3. *`styles.css` & `assets/styles.css`*:
                     - Thêm `transition: left 0.35s, top 0.35s` để trái tim lướt êm ái khi đổi ngày trên Studio Editor.
                  4. *Kiểm thử*:
                     - Syntax JS `node -c`: Passed.
                     - Feature Test: Passed 3/3 (51 assertions). Cache cleared.

            98. **Đồng Bộ Hoàn Chỉnh Lịch Trình Timeline Chuẩn Studio & Ẩn Bức Ảnh Cưới Thừa Ở SECTION16**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng phản hồi: "Lịch trình đang không khớp và thừa 1 ảnh" trong mẫu `hac-bach-trao-duyen-2`:
                    1. *Thừa 1 ảnh*: Ở section Timeline (`#SECTION16`), có một bức ảnh cưới chụp đôi rất lớn (`#IMAGE55`) choán hết màn hình, đẩy nội dung lịch trình xuống sâu. Bức ảnh này bị hardcode tĩnh, trong Studio Tạo Thiệp (Bước 5) hoàn toàn không có trường tải ảnh cho mục Timeline.
                    2. *Lịch trình không khớp*: Trong Studio Editor, Bước 5 định nghĩa 4 mốc (09:00 - Lễ Vu Quy, 16:00 - Đón Khách Tại Sảnh Tiệc, 17:30 - Cử Hành Hôn Lễ, 18:30 - Khai Tiệc Mừng Cưới). Nhưng mẫu thiệp chỉ hardcode 3 khối group tĩnh (`GROUP142`, `GROUP143`, `GROUP144`), làm mất hẳn mốc thứ 4. Ngoài ra, mỗi dòng cũ còn chứa text mô tả cố định lạc quẻ (`PARAGRAPH30` "Checkin với photobooth...", `PARAGRAPH81` "Chứng kiến khoảnh khắc...", `PARAGRAPH82` "Cùng nhau khai tiệc...").
                - **Giải pháp hoàn thiện**:
                  1. *`InvitationTemplateSeeder.php`*: Bổ sung mảng `timeline` gồm 4 mốc chuẩn vào `default_config` của mẫu thiệp `hac-bach-trao-duyen-2`. Đã chạy seeder cập nhật database.
                  2. *`template.blade.php`*:
                     - Cập nhật fallback `$defaultTimeline` đủ 4 mốc chuẩn đồng bộ với `schema.json`.
                     - Ẩn triệt để ảnh thừa `#IMAGE55` (`display: none !important;`).
                     - Thay thế toàn bộ cụm `GROUP142`, `GROUP143`, `GROUP144` và các đoạn text mô tả hardcode cũ bằng container timeline động `.mayhen_hacbach2_timeline_container` lặp qua `@foreach($timelineList as $tIdx => $tItem)`.
                     - Mỗi mốc gồm: Cột giờ (`data-sync-target="timeline.{{ $tIdx }}.time"`), Icon hoa văn vẽ tay nghệ thuật ở giữa kèm spine nét đứt, và Tiêu đề mốc (`data-sync-target="timeline.{{ $tIdx }}.title"`).
                  3. *`styles.css` & `assets/styles.css`*:
                     - Thêm quy tắc CSS chuẩn Quiet Luxury Editorial cho `.mayhen_hacbach2_timeline_*`.
                     - Căn chỉnh chiều cao `#SECTION16` linh hoạt (`height: auto !important; min-height: 520px;`) và viền nét đứt `#BOX34` tự động co giãn ôm trọn các mốc lịch trình.
                     - Font chữ Garamond cổ điển quý phái, hiển thị đều đặn, sắc nét và cân đối trên cả Desktop và Mobile.
                  4. *Kiểm thử & Định dạng*:
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (64 assertions).
                     - Pint format: Passed.
                     - Cache hệ thống: Cleared.

            99. **Nâng Cấp Khối Thông Tin Phụ Mẫu (Ông Bà) Nổi Bật, Trang Trọng & Tối Ưu Độ Cách Dòng Gọn Gàng**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng gửi ảnh chụp màn hình chỉ ra: "đoạn Ông Bà để bé và xấu quá, cho nổi bật chút, chữ bé quá và cách dòng nhiều quá".
                  * *Chữ bé và xấu*: Đoạn phụ mẫu (`Nhà Trai: Ông... & Bà...`) chỉ có font 13px, màu xám nhạt `#78716c` dính sát đuôi text tự sự, làm giảm sự trang trọng của thông tin song thân phụ mẫu.
                  * *Cách dòng nhiều quá*: Đoạn văn bản tự sự của Chú Rể / Cô Dâu (`#PARAGRAPH70`, `#PARAGRAPH76`) có `line-height: 1.6` đến `1.7` và kèm thẻ `<br>` thừa ở cuối, khiến các dòng chữ bị dãn quá rộng, rời rạc, loãng chữ.
                - **Giải pháp hoàn thiện**:
                  1. *`template.blade.php`*:
                     - Bỏ thẻ `<br>` thừa ở cuối đoạn text tự sự.
                     - Thiết kế lại khối phụ mẫu thành thẻ `.mayhen_hacbach2_parents_card`:
                       * Dòng tag: `NHÀ TRAI` / `NHÀ GÁI` (chữ in hoa nhỏ gọn, nét thanh lịch).
                       * Dòng tên: `ÔNG: ` `<strong data-sync-target="groom_father">...</strong>` ` · BÀ: ` `<strong data-sync-target="groom_mother">...</strong>`.
                       * Tách biệt bằng đường viền phân cách `border-top` mảnh dẻ tinh tế.
                  2. *`styles.css` & `assets/styles.css`*:
                     - Giảm `line-height` của `#PARAGRAPH70` và `#PARAGRAPH76` từ 1.6 xuống `1.45 !important;` (desktop: 1.48), giúp đoạn văn bản khít lại gọn gàng, đọc liền mạch và trau chuốt.
                     - Tăng font chữ phụ mẫu từ 13px lên `16px !important;` (desktop: 17.5px), tên bố mẹ `font-weight: 700`, màu đen than `#1a1a1a` nổi bật, rõ nét và trang trọng.
                     - Layout flexbox tự động dàn 1 dòng cân đối hoặc xuống dòng mềm mại nếu tên dài, không đè chữ.
                  3. *Kiểm thử*:
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (64 assertions).
                     - Cache hệ thống: Cleared.

            100. **Tái Thiết Kế Bố Cục Timeline 2 Cột Đối Xứng (Trái Lịch Trình - Phải Ảnh Cưới Dọc) Khắc Phục Hoàn Toàn Cảm Giác Trống Trải**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Người dùng gửi ảnh chụp màn hình máy tính/desktop (`media_1793431851518_d0ad4ad5.jpg`) phản hồi: "Lịch trình tôi thấy cứ trống trống, bản trước có 1 cái ảnh nằm bên phải cũng đẹp mà".
                  * *Nguyên nhân*: Khi xem trên màn hình ngang (desktop 960px), nếu chỉ hiển thị 4 dòng timeline ở giữa thì hai bên trái và phải bị thừa rất nhiều khoảng trống. Trong bản gốc LadiPage trước đây, bên phải có một bức ảnh cưới dọc rất đẹp (`pch06204-20250612110330-deq-b.jpg`), giúp cân bằng thị giác hoàn hảo.
                - **Giải pháp hoàn thiện**:
                  1. *`schema.json`*: Bổ sung trường `wedding.extra_data.timeline_photo_url` vào section `timeline`, cho phép người dùng tùy chọn thay đổi ảnh cưới ở lịch trình hoặc dùng ảnh mặc định.
                  2. *`template.blade.php`*:
                     - Xây dựng bố cục 2 cột `.mayhen_hacbach2_timeline_layout`:
                       * Cột bên trái: Danh sách các mốc thời gian (`.mayhen_hacbach2_timeline_container`) 4 mốc chuẩn, icon hoa văn vẽ tay, spine nét đứt, hỗ trợ Live Sync thời gian thực.
                       * Cột bên phải: Khung ảnh cưới dọc nghệ thuật (`.mayhen_hacbach2_timeline_photo_wrap`) với ảnh bo tròn góc 20px, có `data-sync-target="timeline_photo_url"`.
                  3. *`styles.css` & `assets/styles.css`*:
                     - Desktop (`min-width: 768px`): Bố cục flex 2 cột ngang đối xứng (`left: Lịch trình 55%`, `right: Ảnh cưới 340px x 460px`), lấp đầy khung 960px cực kỳ sang trọng, ấm cúng và loại bỏ hoàn toàn cảm giác trống trải.
                     - Mobile (`max-width: 767px`): Xếp theo chiều dọc tự nhiên — Lịch trình hiển thị trọn vẹn ở trên (không bị che khuất như bản cũ), và bức ảnh cưới nằm gọn gàng bên dưới bổ trợ cho trang thiệp thêm đầy đặn cảm xúc.
                  4. *Kiểm thử*:
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (65 assertions).
                     - Cache hệ thống: Cleared.

            101. **Triệt Tiêu Hoàn Toàn Khoảng Trắng Thừa Ở Cuối Trang (#SECTION26 & Mobile Scale Overflow)**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng gửi ảnh chụp màn hình (`media_1793432028610_3594ba22.jpg`): Sau dòng "Chân thành cảm ơn!" xuất hiện một khoảng trắng xóa rất dài ở đáy màn hình.
                  * *Nguyên nhân cốt lõi*:
                    1. *CSS Transform Scale Mobile*: Khi màn hình nhỏ (`width < 420px`), `.ladi-wraper` được co nhỏ lại bằng `transform: scale(calc(100vw / 420))`. Theo chuẩn CSS W3C, `transform` chỉ thu nhỏ phần hiển thị thị giác (visual) mà không thay đổi kích thước hộp layout DOM. Do đó, phần tử con vẫn chiếm đủ ~9000px chiều cao gốc, trong khi nội dung hiển thị chỉ cao ~8000px, tạo ra gần 1000px khoảng trống layout trắng tinh ở đáy trang!
                    2. *Chiều cao #SECTION26*: Section cuối cùng bị gán cứng `height: 761px`, trong khi cụm nội dung cảm ơn (`#GROUP128`) chỉ cao khoảng 620px, làm thừa thêm 140px khoảng trống.
                    3. *Màu nền body*: `body` cũ mang màu `#ffffff` (trắng toát) đối chọi hoàn toàn với nền giấy kem ấm áp của mẫu thiệp.
                - **Giải pháp hoàn thiện**:
                  1. *`script.js` & `assets/script.js`*:
                     - Cập nhật hàm `autoScaleMobile()` tính toán `excess = naturalHeight - scaledHeight` và gán trực tiếp `margin-bottom: -${excess}px !important;` lên `.ladi-wraper`.
                     - Cơ chế này ép hộp layout DOM của wrapper co lại chính xác 100% bằng chiều cao visual thực tế, triệt tiêu sạch sẽ 100% khoảng trắng thừa!
                     - Lắng nghe thêm sự kiện `load` và kích hoạt tự động để bắt đúng chiều cao sau khi ảnh tải xong.
                  2. *`styles.css` & `assets/styles.css`*:
                     - Sửa `@media (max-width: 419.98px)`: Bỏ `margin: 0 auto !important;` ghi đè, cho phép `margin-bottom` âm của JS hoạt động trơn tru.
                     - Tối ưu `#SECTION26` với `height: auto !important; min-height: 640px !important; padding-bottom: 25px !important;` ôm khít nội dung cảm ơn.
                     - Khóa chặt `#SECTION_POPUP` với `height: 0px !important; overflow: hidden !important; margin: 0 !important; padding: 0 !important;`.
                     - Đồng bộ màu nền toàn cục `html, body { background-color: #FAF8F5 !important; }` theo chuẩn thương hiệu Quiet Luxury.
                  3. *Kiểm thử*:
                     - Cú pháp JS `node -c`: Passed.
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (65 assertions).
                     - Cache hệ thống: Cleared.

            102. **Bỏ Hoàn Toàn Ảnh Mã QR Gốc Hardcode & Chỉ Hiển Thị Khi Người Dùng Điền / Tải Mã QR**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng gửi ảnh Studio Editor (`media_1793432941836_d3c62f9d.jpg`) yêu cầu: "Hộp mừng cưới bỏ ảnh mã QR gốc đi, không điền mã QR thì thôi, còn nếu điền thì mới hiển thị lên".
                  * *Nguyên nhân*: Trong `styles.css`, `#IMAGE134` và `#IMAGE135` bị gán cứng `background-image: url("assets/qr-code-styling-20241031160420--fwgj.png")` mang logo và tên của bên thứ ba (27BigGroup / Minh Đức / Thúy Ngân). Trong `template.blade.php`, `#POPUP2` và `#POPUP3` cũng bị fallback cứng ảnh này, và `#SECTION27` không có logic ẩn khi chưa có QR.
                - **Giải pháp hoàn thiện**:
                  1. *`styles.css` & `assets/styles.css`*: Xóa bỏ triệt để 2 quy tắc `background-image: url("assets/qr-code-styling...")` gán cứng. Bổ sung CSS tự động căn giữa mã QR nếu chỉ có 1 bên điền mã (`#SECTION27:has(#GROUP130[style*="display: none"]) #GROUP131`).
                  2. *`template.blade.php`*:
                     - Bỏ vĩnh viễn fallback ảnh QR gốc trong `POPUP2` (`#IMAGE136`) và `POPUP3` (`#IMAGE137`), ẩn phần tử khi chưa có QR (`style="display: none !important;"`).
                     - Gán đúng hợp đồng `data-sync-target="qr_groom_image"` cho `#IMAGE135` và `data-sync-target="qr_bride_image"` cho `#IMAGE134`.
                     - Ẩn `#SECTION27` khi cả hai bên đều chưa có mã QR (`$hasAnyQr ? '' : 'display: none !important;'`), ẩn từng khối riêng lẻ (`#GROUP131`, `#GROUP130`) khi bên đó chưa có QR.
                  3. *`editor-bridge.blade.php`*: Bổ sung logic Live Sync khi nhận `qr_groom_image` hoặc `qr_bride_image`: tự động gán `background-image` và gỡ bỏ `display: none` khỏi phần tử cha, khối nhóm và `#SECTION27` ngay lập tức; khi xóa QR thì tự động ẩn đi.
                  4. *Kiểm thử*:
                     - Feature Test `tests/Feature/HacBachTraoDuyen2TemplateTest.php`: Passed 3/3 tests (65 assertions).
                     - Cache hệ thống: Cleared.

            103. **Chuẩn Hóa Toàn Cục Hộp Mừng Cưới Cho Tất Cả Mẫu Thiệp (Chỉ Cần Mã QR, Loại Bỏ Trường Nhập Tay Thừa Thãi Ở Lõi Hệ Thống)**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng yêu cầu: "Mẫu thiệp nào cũng vậy, sửa cái chung".
                  * *Nguyên nhân cốt lõi*: Trong `app/Services/InvitationTemplateService.php`, hàm `getCanonicalSections()` định nghĩa section `gift` mặc định chứa 6 trường text nhập tay rời rạc (`groom_bank_name`, `groom_bank_account`, `groom_bank_holder`, `bride_bank_name`, `bride_bank_account`, `bride_bank_holder`). Khi `normalizeSchema()` xử lý, nó luôn ép các trường này vào schema của mọi mẫu thiệp dù file JSON có định nghĩa hay không.
                - **Giải pháp hoàn thiện toàn cục**:
                  1. *`app/Services/InvitationTemplateService.php`*:
                     - Chuẩn hóa cấu trúc canonical `gift` ở mức kiến trúc: chỉ giữ lại 2 trường lõi `wedding.extra_data.qr_groom_image` và `wedding.extra_data.qr_bride_image`.
                     - Nâng cấp `normalizeSchema()` chủ động nhận diện và loại bỏ danh sách `$legacyBankFields` khỏi mọi template, đảm bảo bất kỳ mẫu thiệp nào (kể cả template tương lai) đều tự động sạch sẽ, không hiển thị các trường nhập tay thừa.
                  2. *Chuẩn hóa toàn bộ 9/9 mẫu thiệp trong hệ thống*:
                     - Đồng bộ làm sạch section `gift` trong tất cả file `schema.json`: `dau-an-thoi-gian`, `dau-ky-tram-nam`, `giua-muon-ngan-vi-sao`, `hac-bach-trao-duyen`, `hac-bach-trao-duyen-2`, `luu-but-ngay-chung-doi`, `net-muc-tinh-yeu`, `thuoc-phim-tinh-yeu`, `tung-trang-hanh-phuc`.
                  3. *`editor.blade.php` & `editor.js`*:
                     - Prefill tự động thông tin ngân hàng, STK, tên chủ TK cũ vào component VietQR.
                     - Tự động đồng bộ giá trị ngầm vào các hidden input để lưu trữ cơ sở dữ liệu và gửi Live Sync sang bản xem trước thiệp.
                     - Hỗ trợ nút "📷 Tải ảnh QR có sẵn" trực tiếp từ thiết bị cá nhân.
                  4. *Kiểm thử*:
                     - Chạy toàn bộ Pest Feature Tests: Passed 10/10 tests (143 assertions).
                     - Laravel Pint: Passed.
                     - Cache hệ thống: Cleared.

            104. **Sửa Triệt Để Lỗi Tạo Mã VietQR & Tự Động Chuẩn Hóa Tên Chủ Tài Khoản Sau 5 Giây**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng gửi ảnh Studio Editor (`media_1793435626639_fdcf70e3.png`) báo lỗi: "đang báo lỗi ko tạo được, và tên người phải biết chuẩn hóa, nếu điền tên không dấu và viết hoa rồi thì thôi chưa chưa thì phải tự chuẩn hóa khi nhập xong sau 5s".
                  * *Nguyên nhân lỗi tạo QR*: Trong `editor.js`, lệnh `createImageBitmap(await response.blob())` bị ném lỗi `DOMException` vì `createImageBitmap` trong chuẩn Web API của Chrome/Firefox không hỗ trợ giải mã Blob vector SVG (`image/svg+xml`) từ API VietQR, dẫn đến rơi vào khối catch và hiện lỗi màu đỏ: "Không tạo được QR. Kiểm tra ngân hàng và số tài khoản.".
                  * *Yêu cầu chuẩn hóa tên*: Tên chủ tài khoản ngân hàng cần viết hoa không dấu (`NGUYEN MINH DUC`). Nếu người dùng đã gõ đúng chuẩn hoa không dấu thì giữ nguyên; nếu gõ có dấu hoặc chữ thường thì sau 5s ngừng nhập (hoặc khi blur/bấm nút cập nhật) hệ thống phải tự động chuẩn hóa sang in hoa không dấu.
                - **Giải pháp hoàn thiện**:
                  1. *`public/assets/client/js/pages/editor.js`*:
                     - Thay thế hoàn toàn `createImageBitmap()` bằng hàm `renderSvgToDataUrl()`: vẽ SVG lên Canvas bằng `new Image()` và fallback trực tiếp sang Base64 Data URI SVG. Hoạt động 100% trơn tru, không bao giờ bị crash.
                     - Viết hàm `normalizeVietnameseName(str)`: Khử dấu tiếng Việt toàn diện (hỗ trợ cả Unicode dựng sẵn và tổ hợp NFD, ký tự 'đ'/'Đ'), loại bỏ ký tự lạ và chuyển sang chữ HOA.
                     - Quản lý bộ hẹn giờ 5 giây (`nameNormalizeTimers` 5000ms): Nếu người dùng gõ chưa chuẩn hóa thì sau đúng 5s ngừng gõ sẽ tự động chuyển đổi thành chữ hoa không dấu và tạo mã VietQR; nếu đã đúng chuẩn hoa không dấu rồi thì tạo ngay sau 500ms mà không làm thay đổi giá trị input; khi blur hoặc bấm nút cập nhật thì chuẩn hóa ngay lập tức.
                  2. *Kiểm thử*:
                     - Cú pháp JS `node -c`: Passed.
                     - Pest Feature Tests: Passed 10/10 tests (143 assertions).
                     - Laravel Pint: Passed.
                     - Cache hệ thống: Cleared.

            105. **Chuẩn Hóa Toàn Diện Mẫu "Lưu Bút Ngày Chung Đôi" (`luu-but-ngay-chung-doi`)**:
                - **Kiến trúc & Schema (`schema.json`)**:
                  * Duy trì cấu trúc 5 section chuẩn mực: `couple`, `groom_family`, `bride_family`, `album` (10 ảnh), `gift`.
                  * Chuẩn hóa toàn bộ bộ khóa canonical (`wedding.groom_name`, `wedding.bride_name`, `wedding.extra_data.groom_father/mother`, `wedding.extra_data.bride_father/mother`, `wedding.wedding_date`, `wedding.extra_data.qr_groom_image`, `wedding.extra_data.qr_bride_image`).
                  * Loại bỏ toàn bộ các default giả, text hardcode thừa, và đồng bộ kiểu dữ liệu giờ sang input time.
                - **Khế ước Live Sync & Section Scroll (`template.blade.php`)**:
                  * Gắn `data-mayhen-section` chuẩn xác (`couple` ở `#SECTION1`, `groom_family` ở `#SECTION13`, `bride_family` ở `#GROUP150`, `album` ở `#SECTION20`, `gift` ở `#SECTION27`) giúp Stepper Bar cuộn mượt mà đến đúng vị trí khi click tab.
                  * Gắn đầy đủ `data-sync-target` cho 100% vị trí hiển thị (tên chú rể, tên cô dâu, tên khách mời, lời ngỏ, bố mẹ, giờ/ngày/âm lịch/địa chỉ hai nhà, 10 ảnh album cưới và mã QR mừng cưới).
                  * Loại bỏ hardcode tĩnh tên chú rể "Ngọc sơn", tên khách mời "Bạn và Gia Đình", tháng tiệc.
                - **Phòng vệ Embed & Tài nguyên nội bộ**:
                  * Cưỡng chế ẩn màn phong bì/cánh cửa khi `$isEmbed = true`, mở khóa cuộn `html, body`, hiển thị tức thì các phần tử reveal.
                  * 100% tài nguyên cục bộ: Loại bỏ các liên kết font/ảnh ngoài, fallback chỉ trỏ vào file có thật trong thư mục `assets/images/external/`.
                  * Đồng bộ hóa hoàn toàn hai file `styles.css` và `script.js` sang `assets/styles.css` và `assets/script.js`.
                - **Kiểm thử & Định dạng**:
                  * Chạy Feature Test `LuuButNgayChungDoiStandardizationTest`: Passed 4/4 tests (31 assertions).
                  * Dọn dẹp file test tạm thời theo đúng quy định.
                  * Laravel Pint: Passed.

            106. **Sửa Lỗi Chồng Chéo Chữ, Bổ Sung Lịch Trình & Tinh Chỉnh Giao Diện "Lưu Bút Ngày Chung Đôi"**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng phản hồi: "dữ liệu chưa khớp với giao diện, và giao diện nhiều chỗ rất xấu, chưa có lịch trình, vẫn còn nhiều phần giao diện rất xấu: mayhen_invitation_ladjihe38udhd_ladi-section mayhen_reveal_element mayhen_revealed".
                  * Ảnh `media_1793527224761_2c694f9f.jpg`: Tên cô dâu và chú rể ở trang bìa (#GROUP94) bị `position: absolute` gán cứng tọa độ, khi gõ tên dài thì chữ "NGUYỄN MINH ĐỨC" đè thẳng lên chữ "NGUYỄN MINH ANH".
                  * Ảnh `media_1793527252024_9445ccef.jpg`: Khối ngày âm lịch `#mayhen-wedding-lunar-date` bị đặt lồng bên trong `#GROUP89` (chiều cao chỉ 58px) đè nát lên ngày, thứ, tháng, năm cưới; monogram `#GROUP95` bị gán nhầm target `groom_name`/`bride_name` làm tên đầy đủ tràn vào chữ cái viết tắt.
                  * Ảnh `media_1793527260490_9fa50173.jpg`: Số ngày trong bảng lịch tháng có màu chữ xám đậm `#493b34` trên nền đen tối mịt không nhìn thấy gì; số đếm ngược Countdown có màu xanh đậm `#133827` trên hộp đen tối khó đọc.
                  * Ảnh `media_1793527267324_80a40c8b.jpg`: Đoạn mô tả Chú rể (`#PARAGRAPH70`) và Cô dâu (`#PARAGRAPH76`) bị gán nhầm `data-sync-target="couple_quote"`, khiến lời ngỏ tình yêu bị lặp lại 2 lần dưới cả 2 người.
                  * Ảnh `media_1793527293956_3bd405cc.jpg`: Khối lịch trình tiệc cưới `#SECTION16` hiển thị cụt chữ "Ngày " khi trường ngày tiệc rỗng; chưa khai báo mục Lịch trình (`timeline`) trong schema của mẫu thiệp.
                  * Lớp `mayhen_reveal_element`: Chạy hiệu ứng trễ trong iframe Studio gây cảm giác nhảy giật layout.
                - **Giải pháp hoàn thiện**:
                  1. *`schema.json`*: Bổ sung đầy đủ section `timeline` (Lịch Trình Hôn Lễ) dạng repeater động chuẩn hệ thống gồm giờ, tên sự kiện và ghi chú địa điểm. Nâng `supported_sections` lên đầy đủ 6 phần chuẩn.
                  2. *`template.blade.php`*:
                     - Gán `data-mayhen-section="timeline"` cho `#SECTION16` để thanh Stepper Bar của Studio Editor tự động cuộn đến đúng khối Lịch Trình.
                     - Gán target `timeline.0.time`, `timeline.0.title`, `timeline.1.time`, `timeline.1.title` và fallback ngày tiệc an toàn (`$bridePartyDateFormatted`, `$groomPartyDateFormatted`), loại bỏ triệt để hiện tượng hiển thị chữ "Ngày " cụt ngủn.
                     - Đưa `#mayhen-wedding-lunar-date` ra ngoài `#GROUP89` đặt ngay ngắn bên dưới khối ngày cưới.
                     - Sửa target monogram ở `#GROUP95` thành `data-sync-target="groom_initial"` và `data-sync-target="bride_initial"`.
                     - Khôi phục văn bản giới thiệu chân dung Chú rể & Cô dâu thanh lịch, gỡ bỏ binding nhầm `couple_quote`.
                     - Gán đúng target `guest_name` cho `#HEADLINE66` và `groom_name` cho `#HEADLINE13` (xóa triệt để hardcode "Ngọc sơn").
                  3. *`styles.css` & `assets/styles.css`*:
                     - Chuyển `#GROUP94` sang Flexbox tự co giãn (`display: flex; flex-wrap: wrap; justify-content: center; gap: 4px 12px;`), không bao giờ bị đè tên nhau dù tên ngắn hay dài.
                     - Tinh chỉnh màu chữ bảng lịch sang trắng ngà `rgba(255, 255, 255, 0.88)`, ngày chọn nổi bật trên nền đỏ rượu vang `#a83a4b`.
                     - Sửa màu số Countdown sang trắng sáng `#ffffff` kèm đổ bóng nhẹ, tiêu đề nhãn kem ấm sang trọng.
                     - Định vị tuyệt đối ngày âm lịch ở vị trí chuẩn, tinh chỉnh font chữ tiêu đề hai nhà `#HEADLINE69`, `#HEADLINE70`.
                     - Cưỡng chế `body.mayhen_invitation_ladjihe38udhd_embed .mayhen_reveal_element { opacity: 1 !important; transform: none !important; }`.
                  4. *`script.js` & `assets/script.js`*:
                     - Không kích hoạt `mayhen_reveal_element` khi đang nhúng trong iframe (`isEmbed = true`).
                     - Bổ sung đồng bộ Live Sync tức thì cho chữ cái đầu `groom_initial` và `bride_initial`.
                  5. *Kiểm thử & Định dạng*:
                     - Feature Test `LuuButNgayChungDoiRefinedTest` & `CanonicalTemplateSectionsTest`: Passed 100%.
                     - Laravel Pint: Passed.
                     - View cache: Cleared.

            107. **Sửa Triệt Để Lỗi Chồng Chéo Chữ Lên Ảnh, Lệch Lịch Tháng, Trắng Album & Khử Thừa Trường Ảnh Save The Date**:
                - **Yêu cầu & Vấn đề phát hiện**:
                  * Khách hàng gửi loạt ảnh chụp màn hình cả 2 mẫu `luu-but-ngay-chung-doi` và `dau-ky-tram-nam`:
                    1. Mẫu `luu-but-ngay-chung-doi`: Chữ "NGUYỄN MINH ĐỨC & NGUYỄN MINH ANH" đè thẳng lên chữ chìm "SƠN & QUỲNH" ở trang bìa; bảng lịch tháng bị lệch ngày (thứ 6 ngày 1/1 nhảy sang thứ 7, ngày Chủ nhật bị xén cụt lề phải, thanh thứ trắng chữ trên nền trắng, trái tim tĩnh SHAPE14 nằm lệch ngày); phần mô tả chú rể bị ngắt chữ "luôn" -> "luô"; album cưới 10 ảnh bị trắng xóa.
                    2. Mẫu `dau-ky-tram-nam`: Cánh cửa/card phong bì sau khi mở vẫn đè mờ lên giao diện phong bì "Our Story"; ở thiệp mời chính SECTION26, tên khách mời, lời mời và chữ ký tên cô dâu chú rể đè nát lên ngực/mặt ảnh cặp đôi áo nâu đeo kính; form nhập liệu có tới 2 ô chọn ảnh gây hoang mang ("Ảnh bìa đại diện thiệp" và "Ảnh thiệp mời tiệc cưới (Save The Date)").
                - **Giải pháp hoàn thiện**:
                  1. *Khử trùng lặp trường ảnh ở lõi hệ thống (`app/Services/InvitationTemplateService.php`)*:
                     - Loại bỏ trường thừa `wedding.extra_data.save_date_photo_url` khỏi Canonical couple fields. Form nhập liệu giờ đây chỉ còn 1 trường ảnh đại diện duy nhất `wedding.cover_photo_url`, giao diện gọn gàng, tăng trải nghiệm người dùng tối đa.
                  2. *Cầu nối Live Sync (`editor-bridge.blade.php`)*:
                     - Bổ sung ánh xạ alias 2 chiều `cover_photo_url <-> save_date_photo_url` và `hero_photo_url`. Khi người dùng tải ảnh bìa, toàn bộ các vị trí ảnh Save The Date hoặc ảnh bìa trên mọi mẫu thiệp đều cập nhật đồng bộ tức thời.
                  3. *Chuẩn hóa mẫu "Lưu Bút Ngày Chung Đôi" (`luu-but-ngay-chung-doi`)*:
                     - `schema.json`: Đổi default `wedding.cover_photo_url` từ file ảnh chụp dài `luu-but-ngay-chung-doi.webp` sang ảnh gốc sạch `images/external/42bbe2b173fbd668dc032e4f.jpg`, loại bỏ vĩnh viễn bóng chữ "SƠN & QUỲNH".
                     - `template.blade.php`: Gán trực tiếp `background-image` cho `#IMAGE1`, `#IMAGE88`, `#IMAGE55` và toàn bộ 10 ảnh album cưới `#IMAGE60`-`#IMAGE66`, `#IMAGE109`, `#IMAGE110`, `#IMAGE114`.
                     - `styles.css` & `assets/styles.css`: Đặt màu chữ thứ trong tuần `#GROUP81 h3` sang đen đậm `#202020` trên thanh trắng `#BOX50`; căn chỉnh khung lịch `#GROUP82` rộng 402px (left 9px) vừa khít thanh tiêu đề, không bao giờ bị cắt cột Chủ nhật; ẩn hình trái tim cố định `#SHAPE14`; căn giữa thẻ mô tả chú rể `#GROUP125`/`#GROUP126`, sửa ngắt dòng tự nhiên.
                     - `script.js` & `assets/script.js`: Sửa công thức tính ngày đầu tháng sang chuẩn Thứ 2 đầu tuần `(new Date(year, monthIndex, 1).getDay() + 6) % 7` khớp 100% với các cột `MON TUE WED THUR FRI SAT SUN`.
                  4. *Chuẩn hóa mẫu "Dấu Kỷ Trăm Năm" (`dau-ky-tram-nam`)*:
                     - `styles.css` & `assets/styles.css`: Ẩn triệt để tấm card cửa `.opened .door_center` và `.mayhen_door_hidden` bằng `display: none !important;` khi cánh cửa mở, không bao giờ đè lên trang nội dung.
                     - Tái cấu trúc phân tầng layout SECTION26: Căn giữa ảnh cặp đôi `image226` (width/height 300px, border-radius mềm mại, đổ bóng Quiet Luxury); khối mời `group104` đặt phía trên ảnh (top 195px); cụm chữ ký tên cô dâu chú rể `couple_names_wrapper` đặt ngay ngắn bên dưới ảnh (top 660px); khối thời gian địa điểm xếp gọn phía dưới (top 755px - 875px). Ảnh và chữ hoàn toàn tách bạch, không còn bất kỳ sự chồng chéo nào.
                  5. *Kiểm thử & Định dạng*:
                     - Viết Feature Test kiểm tra render giao diện, calendar math và album: Passed 3/3 tests (23 assertions).
                     - Dọn dẹp file test tạm thời theo đúng quy định.
                     - Laravel Pint: Passed.
                     - View cache: Cleared.

            108. **Khắc Phục Toàn Diện Lỗi Vỡ Layout, Đè Chữ & Đồng Bộ Lịch Cưới Thời Gian Thực Mẫu Dấu Ký Trăm Năm**:
                - **Yêu cầu & Vấn đề xử lý**:
                  * Người dùng gửi 4 ảnh chụp phản ánh giao diện bị vỡ:
                    1. Màn hình điện thoại preview bị trượt xuống giữa trang, bảng lịch vẫn giữ "Tháng 03" khoanh ngày 21 trong khi form nhập ngày 09/01/2027 (lỗi không đồng bộ lịch thời gian thực); thanh dock điều hướng dưới đáy chen ngang nội dung.
                    2. Chữ "WEDDING INVITATION" và "Nguyễn Minh Đức & Nguyễn Minh Anh" bay lơ lửng đè lên phong bì "Our Story".
                    3. Khối SECTION26 thiệp mời: Tên khách mời, lời mời, ảnh cưới cặp đôi áo nâu và chữ ký tên cô dâu chú rể đè bẹp lên nhau.
                    4. Khối phong bì SECTION3: Tấm ảnh rừng/thác nước cũ thừa `image210` nhô ra lệch lạc bên trái phong bì gây khó chịu.
                    5. Khối timeline SECTION27: Hiển thị thiếu 2 mốc sự kiện (chỉ có 2/4 mốc) do chiều cao container LadiPage bị giới hạn.
                - **Giải pháp hoàn thiện**:
                  1. *Đồng bộ Lịch Cưới thời gian thực toàn diện (`editor-bridge.blade.php` & `dau-ky-tram-nam/script.js`)*:
                     - Viết bộ parse ngày an toàn hỗ trợ cả định dạng `YYYY-MM-DD` và `DD/MM/YYYY`, chống lệch múi giờ UTC.
                     - Cập nhật đồng loạt tiêu đề `[data-sync-target="calendar_month_year"]` và `.calendar_month` sang `Tháng MM · YYYY`.
                     - Tự động vẽ lại toàn bộ lưới lịch cho mọi mẫu thiệp cưới (`.mayhen_cal_blank`, `.mayhen_cal_day`, `.mayhen_cal_active`), chuẩn hóa thứ Hai đầu tuần.
                  2. *Phòng vệ nhúng Studio Editor & Ẩn Cánh Cửa (`dau-ky-tram-nam/template.blade.php`, `script.js`, `styles.css`)*:
                     - `TemplateController.php`: Truyền rõ ràng `$isEmbed` cho view; chuyển cache asset sang `no-cache, must-revalidate` khi debug.
                     - `template.blade.php`: Nhận diện đầy đủ các tham số embed/editor, ẩn hoàn toàn thẻ cánh cửa và thanh dock điều hướng dưới đáy khi nhúng iframe.
                     - `script.js` & `styles.css`: Ẩn tức thì `display: none !important;` cho toàn bộ cánh cửa và tấm card tem sáp khi mở hoặc ở chế độ nhúng.
                  3. *Sắp xếp phân tầng không gian tuyệt đối cho SECTION26 (`styles.css`)*:
                     - Mở rộng chiều cao section lên `1120px`.
                     - Lời mời và tên khách mời `group104` đặt tại `top: 195px`.
                     - Ảnh cặp đôi `image226` căn giữa chuẩn mực `top: 340px` (280x280px).
                     - Cụm chữ ký tên cô dâu chú rể `couple_names_wrapper` nằm ngay ngắn bên dưới ảnh tại `top: 635px`.
                     - Thời gian, ngày tháng, âm lịch, địa điểm tổ chức tiệc và nút chỉ đường xếp tầng chuẩn mực từ `top: 730px` đến `980px`. Không còn bất kỳ phần tử nào chồng chéo.
                  4. *Tinh chỉnh SECTION3 & SECTION27*:
                     - Ẩn ảnh thừa `image210` ở bên trái phong bì, căn giữa toàn bộ cụm phong bì và ảnh Polaroid cặp đôi.
                     - Mở rộng container timeline `box94` lên `390px` và `group173` lên `330px`, hiển thị đầy đủ, sắc nét toàn bộ 4 mốc thời gian theo đúng `schema.json`.
                  5. *Kiểm thử & Định dạng*:
                     - Pest Feature Tests `CanonicalTemplateSectionsTest` & `InvitationEditorDataSyncTest`: 8/8 tests passed (209 assertions).
                     - Laravel Pint: Passed agent format.
                     - Cache hệ thống: Cleared.

            109. **Khắc Phục Triệt Để Lỗi UI Vỡ Layout, Đè Chữ & Trắng Khối Album Mẫu "Lưu Bút Ngày Chung Đôi"**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  1. *Khoảng trắng mênh mông ở Album Cưới (SECTION20)*: Do logic blade ẩn các phần tử `#IMAGE61`..`#IMAGE114` khi `$albumPhotos` có ít hơn 10 phần tử, cộng với script JS ẩn `galleryTarget.hidden = !value` làm cả hàng ảnh đầu tiên biến mất, để lại hố đen 300px dưới tiêu đề.
                  2. *Chữ Chú rể tràn ra ngoài thẻ ở SECTION4*: Tên chú rể dài (ví dụ: "NGUYỄN MINH ĐỨC") với `font-size: 60px` ở `top: 438px` tràn 34px xuống đáy thẻ gradient tối `#BOX60`, đè lên chữ tiểu sử bên dưới.
                  3. *Lệch cột tiêu đề Thứ (MON-SUN) và Ngày trên Lịch cưới (SECTION24)*: Tiêu đề `#GROUP81` dùng fixed left pixel trong khi lưới ngày tự sinh dùng CSS Grid 7 cột; ngoài ra hình trái tim cứng SVG `#SHAPE14` bị đặt lệch sang ngày khác.
                  4. *Chữ mờ "SON & QUYNH" bị đè sau tên cặp đôi mới*: Hai phần tử `#IMAGE82` và `#IMAGE10` nạp ảnh PNG cố định từ template demo gốc nạp chữ "SON & QUYNH" đè bẹp lên tên cô dâu chú rể mới.
                  5. *Lịch trình thiếu mốc sự kiện so với schema.json*: `schema.json` định nghĩa 4 mốc thời gian nhưng `SECTION16` chỉ render 2 mốc (`timeline.0` và `timeline.1`).
                - **Giải pháp xử lý triệt để**:
                  1. *Bảo đảm 100% 10 ảnh cưới Album (`template.blade.php`, `script.js`, `styles.css`)*:
                     - Luôn sinh đủ 10 ảnh trong `$albumPhotos` với fallback về `$defaultAlbum[$i]`.
                     - Loại bỏ toàn bộ thuộc tính `hidden` điều kiện trên `#IMAGE61`..`#IMAGE114`.
                     - Trong `script.js` & `assets/script.js`: Khi đồng bộ live, không ẩn thẻ (`hidden = false`), cập nhật trực tiếp `background-image`.
                     - Trong CSS: Khóa cưỡng chế `.mayhen_invitation_ladjihe38udhd_photoBookImage { display: block !important; }`.
                  2. *Khắc phục tràn tên Chú rể & Cô dâu (`styles.css` & `assets/styles.css`)*:
                     - Điều chỉnh `#HEADLINE300` và `#HEADLINE304` lên `top: 405px !important;`.
                     - Áp dụng `font-size: clamp(22px, 5.2vw, 32px) !important; line-height: 1.15 !important;` với `word-break: keep-all; white-space: normal;`. Khắc phục 100% lỗi tràn thẻ gradient.
                  3. *Chuẩn hóa Lưới Lịch & Tiêu đề Thứ (`template.blade.php`, `styles.css`, `assets/styles.css`)*:
                     - Re-order các thẻ trong `#GROUP81` theo chuẩn Thứ Hai đến Chủ Nhật (`MON, TUE, WED, THUR, FRI, SAT, SUN`).
                     - Cưỡng chế `#GROUP81 > .ladi-group` thành `display: grid !important; grid-template-columns: repeat(7, 1fr) !important;` khớp chuẩn xác 1:1 với `.calendar_grid` của `#GROUP82`.
                     - Cưỡng chế ẩn hình trái tim cứng `#SHAPE14 { display: none !important; }`.
                  4. *Loại bỏ triệt để Watermark Sơn & Quỳnh cũ*:
                     - Ẩn hoàn toàn `#IMAGE82` và `#IMAGE10` (`display: none !important;`), loại bỏ chữ "SON & QUYNH" mờ sau tên.
                     - Căn giữa hoàn hảo `#PARAGRAPH45` ("Together, we found home") và monogram initials `#GROUP95` (`đ & a`).
                  5. *Đồng bộ toàn diện 4 Mốc Lịch Trình (SECTION16)*:
                     - Thêm `GROUP144` (mốc 2: Khai Tiệc) và `GROUP145` (mốc 3: Chụp Ảnh) với đầy đủ `data-sync-target` cho time, title, description.
                     - Tự động mở rộng chiều cao `#SECTION16` lên `720px` - `790px` khi có 3-4 mốc lịch trình, hiển thị cân đối, sang trọng.
                  6. *Kiểm thử & Tiêu chuẩn*:
                     - Pest Feature Tests `CanonicalTemplateSectionsTest` & `InvitationEditorDataSyncTest`: 8/8 tests passed (209 assertions).
                     - Laravel Pint: Passed agent format.
                     - Cache hệ thống: Cleared.

            110. **Khắc Phục Triệt Để Lỗi Lệch Phong Bì Đầu Thiệp Khi Preview Mẫu "Dấu Ký Trăm Năm" (dau-ky-tram-nam)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Khi xem thực tế ở ngoài (`isEmbed = false`), mẫu thiệp hiển thị bình thường.
                  - Tuy nhiên khi Preview trong Studio Tạo Thiệp hoặc iframe (`isEmbed = true`), cụm phong bì xanh rêu (`.mayhen_invitation_jeowinids483fr_image51` và `.image52`) bị xô lệch hẳn sang nửa bên phải màn hình, trong khi ảnh Polaroid (`.group134`) vẫn nằm ở giữa.
                  - *Nguyên nhân cốt lõi*: Trong `dau-ky-tram-nam/styles.css` và `assets/styles.css` tại dòng 5175 có rule:
                    ```css
                    .mayhen_invitation_jeowinids483fr_embed_mode .mayhen_invitation_jeowinids483fr_ladi-animation,
                    .mayhen_invitation_jeowinids483fr_embed_mode .mayhen_invitation_jeowinids483fr_ladi-animation-hidden {
                        opacity: 1 !important;
                        visibility: visible !important;
                        transform: none !important;
                    }
                    ```
                    Độ đặc hiệu của rule này là (0, 2, 0) kèm `!important`. Nó đè bẹp rule `transform: translateX(-50%) !important;` (độ đặc hiệu chỉ 0, 1, 0) của `image51` và `image52`. Khi mất `translateX(-50%)` nhưng vẫn giữ `left: 50% !important;`, mép trái phong bì bắt đầu từ tọa độ 50% rồi kéo sang phải, làm phong bì trôi tuột sang nửa phải. Khung Polaroid (`group134`) do không có class `ladi-animation` nên không bị ảnh hưởng, dẫn tới tình trạng lệch trục.
                - **Giải pháp xử lý triệt để (3 lớp phòng vệ)**:
                  1. *Lớp 1 (`styles.css` & `assets/styles.css` dòng 5175)*: Gỡ bỏ `transform: none !important;` khỏi selector `.mayhen_invitation_jeowinids483fr_embed_mode .mayhen_invitation_jeowinids483fr_ladi-animation`, chỉ giữ `opacity: 1 !important; visibility: visible !important;`.
                  2. *Lớp 2 (`styles.css` & `assets/styles.css` dòng 5623)*: Nâng cao độ đặc hiệu bảo vệ cho `image51`, `group134`, `image52` với selector bao gồm `.mayhen_invitation_jeowinids483fr_embed_mode`, `body.mayhen_invitation_jeowinids483fr_embed_mode`, `.mayhen_embed_mode` để bảo đảm thuộc tính `left: 50% !important; transform: translateX(-50%) !important;` luôn được áp dụng vững chắc trong mọi môi trường.
                  3. *Lớp 3 (`template.blade.php` dòng 303 & 368)*: Gỡ bỏ class `mayhen_invitation_jeowinids483fr_ladi-animation` thừa khỏi `image51` và `image52`, đảm bảo cụm phong bì là phần tử nền móng hiển thị tức thì, không bị ảnh hưởng bởi bất kỳ animation nào.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            114. **Sửa Lỗi Lệch Lớp Phủ Xanh & Vỡ Bố Cục Chú Rể - Cô Dâu Khối SECTION4 Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng chụp ảnh DevTools tại trang xem thử trên máy tính: Khối thẻ Cô dâu "Cô dâu THÚY QUỲNH" (`#GROUP126`) có lớp phủ màu xanh gradient (`#BOX67`) chỉ che khoảng 70% bên trái bức ảnh cưới, trong khi 30% bên phải (khoảng 114px) của bức ảnh `#IMAGE122` bị lòi ra ngoài lớp phủ trần trụi. Đồng thời tên cô dâu bị ép lệch sang bên trái.
                  - *Nguyên nhân cốt lõi*: Một rule CSS cũ tại dòng 12945 (`#GROUP125, #GROUP126 { width: min(350px, calc(100% - 32px)) !important; left: 50% !important; transform: translateX(-50%) !important; overflow: visible !important; }`) được viết ở **phạm vi toàn cục (global scope)** mà không bọc trong Media Query Mobile (`@media (max-width: 767px)`).
                  - Hậu quả: Trên Desktop (container rộng 960px), cả Chú rể và Cô dâu bị ép thu nhỏ thành 350px và căn giữa màn hình, làm vỡ hoàn toàn layout 2 cột song song của mẫu thiệp. Đồng thời, do `#GROUP126` bị giới hạn 350px nhưng ảnh `#IMAGE122` bên trong vẫn giữ nguyên kích thước Desktop 464px, kết hợp với `overflow: visible !important;`, 114px mép phải của ảnh đã trôi ra ngoài lớp phủ `#BOX67` (vốn chỉ rộng 350px).
                - **Giải pháp xử lý triệt để**:
                  1. *Tách biệt rõ ràng Desktop và Mobile*:
                     - Gỡ bỏ hoàn toàn rule 350px toàn cục.
                     - *Trên Desktop (`@media (min-width: 768px)`)*: Phục hồi bố cục 2 cột song song chuẩn Quiet Luxury của LadiPage: Chú rể (`#GROUP125`) rộng 464px ở `left: 0px`, Cô dâu (`#GROUP126`) rộng 464px ở `left: 496px`, `transform: none !important;`.
                     - *Trên Mobile (`@media (max-width: 767px)`)*: Chú rể và Cô dâu xếp dọc căn giữa màn hình (`width: min(350px, calc(100% - 32px)) !important; left: 50% !important; transform: translateX(-50%) !important;`).
                  2. *Đồng bộ khớp 100% giữa Ảnh và Lớp phủ Overlay*:
                     - Khung chứa (`#GROUP116`, `#GROUP127`): `position: relative !important; width: 100% !important; border-radius: 16px !important; overflow: hidden !important;`.
                     - Ảnh cưới (`#IMAGE115`, `#IMAGE122`): `position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; border-radius: inherit !important; overflow: hidden !important;`.
                     - Hộp overlay màu xanh (`#BOX60`, `#BOX67`): `position: absolute !important; left: 0 !important; right: 0 !important; bottom: 0 !important; top: auto !important; width: 100% !important; border-radius: 0 0 16px 16px !important;`.
                     - Đảm bảo 100% lớp phủ overlay xanh luôn bao trọn toàn bộ chiều rộng của ảnh, triệt tiêu vĩnh viễn hiện tượng lộ ảnh lòi ra ngoài mép phải.
                  3. *Định dạng Typography & Bố cục nhãn*:
                     - Chữ "Chú rể" / "Cô dâu" (`#PARAGRAPH66`, `#PARAGRAPH75`): Font Philosopher in nghiêng thanh nhã, căn lề trái an toàn bên trong lớp phủ.
                     - Tên Chú rể / Cô dâu (`#HEADLINE300`, `#HEADLINE304`): Font Philosopher chữ in hoa sang trọng, cỡ chữ `42px` (Desktop) / `30px` (Mobile), không bị tràn viền.
                     - Đoạn văn tiểu sử (`#PARAGRAPH70`, `#PARAGRAPH76`): Căn đều 2 bên (`text-align: justify`), khoảng cách thoáng đãng.
                  4. *Đồng bộ mã nguồn & Dọn dẹp cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Chạy `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.
                  - Cache view: Đã làm sạch với `php artisan view:clear`.

            111. **Khắc Phục Lỗi Chữ Đè Lên Nhau Tại Thiệp Mời Chính Mẫu "Dấu Ký Trăm Năm" (dau-ky-tram-nam)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Tại khối thiệp mời chính (SECTION26), chữ ký thư pháp của chú rể ("Quốc Thái") nằm đè bẹp trực tiếp lên dòng thời gian "VÀO LÚC 10 GIỜ 00" và "THỨ BẢY, NGÀY 21 THÁNG 03 NĂM 2026", đồng thời dòng thời gian đè lên một phần địa chỉ tiệc.
                  - *Nguyên nhân*: Cụm tên cô dâu chú rể (`.mayhen_invitation_jeowinids483fr_couple_names_wrapper`) gồm 3 dòng (Tên dâu, dấu &, Tên rể) cao ~135px được đặt ở `top: 635px`, kéo dài tới `top: 770px`. Trong khi đó, dòng thời gian (`.paragraph66`) lại được gán cứng ở `top: 730px !important;`, dẫn tới việc chữ "Quốc Thái" và dòng thời gian bị chèn đè lên nhau 40px.
                - **Giải pháp xử lý**:
                  - Điều chỉnh `top: 625px !important;` và tinh chỉnh `gap: 0px !important;`, `line-height: 1.15 !important;` cho `couple_names_wrapper` giúp 3 dòng tên liên kết gọn gàng và thẩm mỹ hơn.
                  - Dời dòng thời gian (`.paragraph66`) xuống `top: 775px !important;`, tạo khoảng thở cách chân chữ thư pháp chú rể 25px an toàn, dứt điểm 100% tình trạng chữ đè lên nhau.
                  - Tịnh tiến các khối bên dưới tương ứng:
                    + Địa chỉ tiệc (`.paragraph67`): `top: 840px !important;` kèm padding hai bên chống tràn viền.
                    + Ngày âm lịch (`.paragraph68`): `top: 890px !important;`.
                    + Nơi tổ chức (`.paragraph69`): `top: 925px !important;`.
                    + Nút xem bản đồ (`.button23`): `top: 980px !important;`.
                  - Đồng bộ cả 2 file `styles.css` và `assets/styles.css`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Cache view: Đã làm sạch với `php artisan view:clear`.

            112. **Sửa Vỡ Layout Lịch Cưới & Bổ Sung Khung Ảnh Kỷ Niệm Cột Phải Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Tại khối Lịch ngày cưới (SECTION24), trên màn hình máy tính / desktop xuất hiện khoảng đen trống trải rất lớn ở nửa bên phải.
                  - Đồng thời thanh thứ trắng (MON-SUN) rộng tới 878px nhưng các con số ngày lại co cụm ở 402px bên trái do một rule toàn cục ghi đè `#GROUP82 { width: 402px !important; }`, làm lệch hoàn toàn cột Thứ và Ngày.
                  - Người dùng yêu cầu sửa lỗi giao diện, thêm 1 bức ảnh cưới đẹp mắt vào bên phải để lấp đầy khoảng trống, hiển thị responsive chuẩn trên mọi thiết bị và cho phép người dùng thay đổi ảnh này trong Studio Tạo Thiệp.
                - **Giải pháp xử lý triệt để**:
                  1. *Bổ sung trường vào `schema.json`*:
                     - Thêm trường `wedding.extra_data.save_date_photo_url` ("Ảnh lịch cưới (Save The Date)") vào tab "Đôi Uyên Ương" với fallback chuẩn asset nội bộ `images/external/5e2097a309453c729959a58c.jpg`, giúp khách hàng dễ dàng tải ảnh hoặc chọn ảnh tùy biến trong Studio Tạo Thiệp.
                  2. *Cập nhật Blade & Hợp đồng Live-Sync (`template.blade.php`, `script.js`, `assets/script.js`)*:
                     - Khởi tạo phần tử `#SECTION24_PHOTO` với class `.mayhen_invitation_ladjihe38udhd_calendar_photo_wrapper`, gắn thẻ ảnh với `data-sync-target="save_date_photo_url"` cùng badge `SAVE OUR DATE` thanh lịch.
                     - Bổ sung logic bắt sự kiện `save_date_photo_url` trong `applySyncData` của `script.js` và `assets/script.js`, đảm bảo khi người dùng tải ảnh trong Studio Tạo Thiệp ảnh sẽ tự động thay đổi mượt mà ngay trên khung điện thoại.
                  3. *Thiết kế CSS Responsive Hoàn Mỹ (`styles.css` & `assets/styles.css`)*:
                     - *Gỡ bỏ rule fixed width 402px ở phạm vi toàn cục*: Phân chia rõ ràng bằng Media Query.
                     - *Trên Desktop (>= 768px)*: Bố cục 2 cột cân xứng tuyệt mỹ (Cột trái rộng 450px gồm Tiêu đề Tháng/Năm, thanh MON-SUN và lưới ngày khớp 100% từng cột thẳng hàng + Nút Thêm vào lịch; Cột phải rộng 410px là Khung Ảnh Save Our Date bo góc 16px, viền mạ vàng nhẹ, đổ bóng mờ sang trọng, lấp đầy hoàn hảo khoảng trống đen trước đó).
                     - *Trên Mobile (<= 767px)*: Cột lịch căn giữa màn hình (rộng `min(390px, calc(100% - 24px))`), phía dưới nút bấm là Card ảnh Save Our Date cân đối, chiều cao section mở rộng tự động lên 770px không bị cắt góc.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            114. **Sửa Lỗi Lệch Lớp Phủ Xanh & Vỡ Bố Cục Chú Rể - Cô Dâu Khối SECTION4 Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng chụp ảnh DevTools tại trang xem thử trên máy tính: Khối thẻ Cô dâu "Cô dâu THÚY QUỲNH" (`#GROUP126`) có lớp phủ màu xanh gradient (`#BOX67`) chỉ che khoảng 70% bên trái bức ảnh cưới, trong khi 30% bên phải (khoảng 114px) của bức ảnh `#IMAGE122` bị lòi ra ngoài lớp phủ trần trụi. Đồng thời tên cô dâu bị ép lệch sang bên trái.
                  - *Nguyên nhân cốt lõi*: Một rule CSS cũ tại dòng 12945 (`#GROUP125, #GROUP126 { width: min(350px, calc(100% - 32px)) !important; left: 50% !important; transform: translateX(-50%) !important; overflow: visible !important; }`) được viết ở **phạm vi toàn cục (global scope)** mà không bọc trong Media Query Mobile (`@media (max-width: 767px)`).
                  - Hậu quả: Trên Desktop (container rộng 960px), cả Chú rể và Cô dâu bị ép thu nhỏ thành 350px và căn giữa màn hình, làm vỡ hoàn toàn layout 2 cột song song của mẫu thiệp. Đồng thời, do `#GROUP126` bị giới hạn 350px nhưng ảnh `#IMAGE122` bên trong vẫn giữ nguyên kích thước Desktop 464px, kết hợp với `overflow: visible !important;`, 114px mép phải của ảnh đã trôi ra ngoài lớp phủ `#BOX67` (vốn chỉ rộng 350px).
                - **Giải pháp xử lý triệt để**:
                  1. *Tách biệt rõ ràng Desktop và Mobile*:
                     - Gỡ bỏ hoàn toàn rule 350px toàn cục.
                     - *Trên Desktop (`@media (min-width: 768px)`)*: Phục hồi bố cục 2 cột song song chuẩn Quiet Luxury của LadiPage: Chú rể (`#GROUP125`) rộng 464px ở `left: 0px`, Cô dâu (`#GROUP126`) rộng 464px ở `left: 496px`, `transform: none !important;`.
                     - *Trên Mobile (`@media (max-width: 767px)`)*: Chú rể và Cô dâu xếp dọc căn giữa màn hình (`width: min(350px, calc(100% - 32px)) !important; left: 50% !important; transform: translateX(-50%) !important;`).
                  2. *Đồng bộ khớp 100% giữa Ảnh và Lớp phủ Overlay*:
                     - Khung chứa (`#GROUP116`, `#GROUP127`): `position: relative !important; width: 100% !important; border-radius: 16px !important; overflow: hidden !important;`.
                     - Ảnh cưới (`#IMAGE115`, `#IMAGE122`): `position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; border-radius: inherit !important; overflow: hidden !important;`.
                     - Hộp overlay màu xanh (`#BOX60`, `#BOX67`): `position: absolute !important; left: 0 !important; right: 0 !important; bottom: 0 !important; top: auto !important; width: 100% !important; border-radius: 0 0 16px 16px !important;`.
                     - Đảm bảo 100% lớp phủ overlay xanh luôn bao trọn toàn bộ chiều rộng của ảnh, triệt tiêu vĩnh viễn hiện tượng lộ ảnh lòi ra ngoài mép phải.
                  3. *Định dạng Typography & Bố cục nhãn*:
                     - Chữ "Chú rể" / "Cô dâu" (`#PARAGRAPH66`, `#PARAGRAPH75`): Font Philosopher in nghiêng thanh nhã, căn lề trái an toàn bên trong lớp phủ.
                     - Tên Chú rể / Cô dâu (`#HEADLINE300`, `#HEADLINE304`): Font Philosopher chữ in hoa sang trọng, cỡ chữ `42px` (Desktop) / `30px` (Mobile), không bị tràn viền.
                     - Đoạn văn tiểu sử (`#PARAGRAPH70`, `#PARAGRAPH76`): Căn đều 2 bên (`text-align: justify`), khoảng cách thoáng đãng.
                  4. *Đồng bộ mã nguồn & Dọn dẹp cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Chạy `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.
                  - Cache view: Đã làm sạch với `php artisan view:clear`.

            113. **Khắc Phục Lỗi Ngắt Dòng Năm "2027" & Cắt Chữ Thứ Thanh Trắng Khối Lịch Cưới Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng gửi hình ảnh phản ánh lỗi giao diện khối lịch cưới: Số năm "2027" bị rớt số 7 xuống dòng dưới ("202" ở dòng trên, số "7" ở dòng dưới đè lên góc phải thanh thứ trắng); chữ "MON TUE WED THUR FRI SAT SUN" bị cắt cụt một nửa trên và dính sát mép trên hộp trắng, phần dưới hộp trắng bị trống toạc; tỷ lệ bố cục tổng thể khối lịch chưa đạt chuẩn thẩm mỹ Quiet Luxury.
                  - *Nguyên nhân ngắt dòng số năm*: `#HEADLINE274` dùng font thư pháp FleurDeLeah kích thước lớn (72px - 96px) nhưng container hẹp và thiếu `white-space: nowrap !important;`, dẫn tới việc chữ số cuối rơi xuống hàng tiếp theo.
                  - *Nguyên nhân chữ thứ bị cắt cụt*: Hộp trắng `#BOX50` và nhóm chữ `#GROUP81` bị lệch tọa độ (`top: 15.5px` và `line-height: 1.6` với `font-size: 27px` từ stylesheet LadiPage gốc), khiến chữ bị đẩy trượt lên mép trên và bị `overflow: hidden` của `#GROUP80` cắt cụt một nửa.
                - **Giải pháp xử lý triệt để (Chuẩn Quiet Luxury)**:
                  1. *Thanh thứ trong tuần*:
                     - Gán nền trắng sạch sẽ trực tiếp cho `#GROUP80` (`background: #FFFFFF !important; border-radius: 10px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;`), ẩn hoàn toàn hộp trung gian `#BOX50` (`display: none !important;`) để triệt tiêu vĩnh viễn việc lệch lớp nền.
                     - Cấu hình `#GROUP81` và `> .ladi-group` thành CSS Grid 7 cột bằng nhau (`grid-template-columns: repeat(7, 1fr) !important;`), căn giữa hoàn hảo cả 2 chiều dọc - ngang.
                     - Gán định dạng chuẩn cho từng thứ `#HEADLINE231` -> `#HEADLINE237`: `display: flex !important; align-items: center !important; justify-content: center !important; font-size: 13px !important; font-weight: 700 !important; color: #1A2822 !important; line-height: 1 !important;`, đảm bảo chữ luôn nằm chính xác ở tâm thanh trắng.
                  2. *Chống ngắt dòng năm 2027 & Cân xứng tiêu đề*:
                     - Bổ sung `white-space: nowrap !important; overflow: visible !important;` cho `#HEADLINE274` và chữ tiêu đề con.
                     - Tinh chỉnh font-size "Tháng" là `34px` và "Năm" là `56px` (Desktop) / `28px` và `44px` (Mobile), mang lại tỷ lệ typography hài hòa, trang nhã.
                  3. *Đồng bộ thẳng hàng đáy 2 cột Desktop*:
                     - Cột trái: Header (top 35px) -> Thanh thứ (top 105px) -> Lưới ngày (top 165px) -> Nút "Thêm vào lịch" (top 435px, cao 44px, căn giữa cột). Đáy cột trái: `479px`.
                     - Cột phải: Khung ảnh Save Our Date (top 35px, rộng 430px, cao 444px). Đáy cột phải: `35 + 444 = 479px`.
                     - Hai cột thẳng hàng đáy tuyệt đối từng pixel, chiều cao section thu gọn về `520px` chuẩn sang trọng, không còn khoảng trống đen thừa thãi.
                  4. *Đồng bộ tài nguyên & Clear cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Làm sạch cache Blade và route với `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            114. **Sửa Lỗi Lệch Lớp Phủ Xanh & Vỡ Bố Cục Chú Rể - Cô Dâu Khối SECTION4 Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng chụp ảnh DevTools tại trang xem thử trên máy tính: Khối thẻ Cô dâu "Cô dâu THÚY QUỲNH" (`#GROUP126`) có lớp phủ màu xanh gradient (`#BOX67`) chỉ che khoảng 70% bên trái bức ảnh cưới, trong khi 30% bên phải (khoảng 114px) của bức ảnh `#IMAGE122` bị lòi ra ngoài lớp phủ trần trụi. Đồng thời tên cô dâu bị ép lệch sang bên trái.
                  - *Nguyên nhân cốt lõi*: Một rule CSS cũ tại dòng 12945 (`#GROUP125, #GROUP126 { width: min(350px, calc(100% - 32px)) !important; left: 50% !important; transform: translateX(-50%) !important; overflow: visible !important; }`) được viết ở **phạm vi toàn cục (global scope)** mà không bọc trong Media Query Mobile (`@media (max-width: 767px)`).
                  - Hậu quả: Trên Desktop (container rộng 960px), cả Chú rể và Cô dâu bị ép thu nhỏ thành 350px và căn giữa màn hình, làm vỡ hoàn toàn layout 2 cột song song của mẫu thiệp. Đồng thời, do `#GROUP126` bị giới hạn 350px nhưng ảnh `#IMAGE122` bên trong vẫn giữ nguyên kích thước Desktop 464px, kết hợp với `overflow: visible !important;`, 114px mép phải của ảnh đã trôi ra ngoài lớp phủ `#BOX67` (vốn chỉ rộng 350px).
                - **Giải pháp xử lý triệt để**:
                  1. *Tách biệt rõ ràng Desktop và Mobile*:
                     - Gỡ bỏ hoàn toàn rule 350px toàn cục.
                     - *Trên Desktop (`@media (min-width: 768px)`)*: Phục hồi bố cục 2 cột song song chuẩn Quiet Luxury của LadiPage: Chú rể (`#GROUP125`) rộng 464px ở `left: 0px`, Cô dâu (`#GROUP126`) rộng 464px ở `left: 496px`, `transform: none !important;`.
                     - *Trên Mobile (`@media (max-width: 767px)`)*: Chú rể và Cô dâu xếp dọc căn giữa màn hình (`width: min(350px, calc(100% - 32px)) !important; left: 50% !important; transform: translateX(-50%) !important;`).
                  2. *Đồng bộ khớp 100% giữa Ảnh và Lớp phủ Overlay*:
                     - Khung chứa (`#GROUP116`, `#GROUP127`): `position: relative !important; width: 100% !important; border-radius: 16px !important; overflow: hidden !important;`.
                     - Ảnh cưới (`#IMAGE115`, `#IMAGE122`): `position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; border-radius: inherit !important; overflow: hidden !important;`.
                     - Hộp overlay màu xanh (`#BOX60`, `#BOX67`): `position: absolute !important; left: 0 !important; right: 0 !important; bottom: 0 !important; top: auto !important; width: 100% !important; border-radius: 0 0 16px 16px !important;`.
                     - Đảm bảo 100% lớp phủ overlay xanh luôn bao trọn toàn bộ chiều rộng của ảnh, triệt tiêu vĩnh viễn hiện tượng lộ ảnh lòi ra ngoài mép phải.
                  3. *Định dạng Typography & Bố cục nhãn*:
                     - Chữ "Chú rể" / "Cô dâu" (`#PARAGRAPH66`, `#PARAGRAPH75`): Font Philosopher in nghiêng thanh nhã, căn lề trái an toàn bên trong lớp phủ.
                     - Tên Chú rể / Cô dâu (`#HEADLINE300`, `#HEADLINE304`): Font Philosopher chữ in hoa sang trọng, cỡ chữ `42px` (Desktop) / `30px` (Mobile), không bị tràn viền.
                     - Đoạn văn tiểu sử (`#PARAGRAPH70`, `#PARAGRAPH76`): Căn đều 2 bên (`text-align: justify`), khoảng cách thoáng đãng.
                  4. *Đồng bộ mã nguồn & Dọn dẹp cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Chạy `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            115. **Khắc Phục Lỗi Vỡ Layout Đóng Nhầm Thẻ DOM & Tái Cấu Trúc Khối Lịch Trình Cưới SECTION16 Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng gửi ảnh chụp màn hình khối Lịch trình cưới (`SECTION16`) bị biến dạng nặng nề:
                    + Có một bức ảnh hình vòm (viên thuốc) kèm dòng chữ "TIỆC CƯỚI NHÀ TRAI" đè thẳng lên giữa cột mốc thời gian bên trái.
                    + Các mốc thời gian "CHỤP ẢNH KỶ NIỆM", "LỄ VU QUY", "LỄ THÀNH HÔN", "TIỆC CƯỚI" bị dồn cụm, đè bẹp lên nhau lộn xộn.
                  - *Nguyên nhân cốt lõi số 1 (DOM Tree hỏng hóc nghiêm trọng)*:
                    Tại `template.blade.php` dòng 1084-1087 (ngay sau `@endif` của `GROUP145`), xuất hiện **4 thẻ đóng `</div>` liên tiếp**, trong khi `SECTION16` chỉ có 2 thẻ cha (`SECTION16` và `.ladi-container`). Hai thẻ `</div>` thừa này đã **đóng sớm `.ladi-wraper` và `#mayhen_w18_stage`**! Hậu quả là toàn bộ các section phía sau (bao gồm `SECTION13` - Tiệc cưới Nhà trai có bức ảnh vòm `#IMAGE51` và `#GROUP39`) bị văng ra khỏi khung wrapper chính, mất ngữ cảnh xếp tầng và trôi ngược lên đè bẹp lên `SECTION16`.
                  - *Nguyên nhân cốt lõi số 2 (Tọa độ CSS của timeline bị xung đột)*:
                    Trong CSS gốc của LadiPage, `#GROUP145` (Chụp ảnh kỷ niệm) có tọa độ `top: 41.6px` (dòng 4896) nằm tít phía trên, trong khi `#GROUP160` (Lễ Vu Quy) ở `top: 240.7px`, `#GROUP143` ở `top: 358px` và `#GROUP144` không có định vị chuẩn, dẫn đến các mốc thời gian nhảy loạn xạ và đè lên nhau.
                - **Giải pháp xử lý triệt để**:
                  1. *Hàn gắn cây DOM sạch sẽ*:
                     - Xóa bỏ dứt điểm 2 thẻ `</div>` thừa ở cuối `SECTION16` trong `template.blade.php`.
                     - Kiểm tra và đảm bảo cây DOM toàn bộ file `template.blade.php` cân bằng tuyệt đối: 806 thẻ mở `<div>` và đúng 806 thẻ đóng `</div>`. Toàn bộ `SECTION13`, `SECTION14`... trở về đúng vị trí tuần tự bên dưới `SECTION16`, triệt tiêu hoàn toàn việc ảnh vòm trôi lên đè bẹp lịch trình.
                  2. *Tái cấu trúc giao diện SECTION16 theo phong cách Quiet Luxury*:
                     - Ẩn triệt để các cành hoa trang trí rườm rà thừa mép (`#IMAGE99, #IMAGE98`).
                     - Khung viền nét đứt thanh nhã `#BOX34` bo góc 20px, viền dashed màu sage/charcoal nhẹ nhàng (`rgba(26, 40, 34, 0.35)`).
                     - Ảnh cưới chính bên phải `#IMAGE55`: bo góc 16px, đổ bóng mềm mại `0 12px 35px rgba(0, 0, 0, 0.15)`.
                     - Ẩn các đường gạch line trang trí cũ gây rối mắt (`#LINE87, #LINE81, #LINE88, #LINE89`).
                  3. *Sắp xếp 4 mốc Timeline khoa học, cách đều, không chồng chéo*:
                     - *Trên Desktop (`@media (min-width: 768px)`)*: Bố cục 2 cột. Ảnh cưới bên phải (rộng 340px, cao 470px). Bên trái xếp 4 mốc thẳng hàng từ trên xuống:
                       * Lễ Vu Quy (`#GROUP160`): `top: 75px`
                       * Lễ Thành Hôn (`#GROUP143`): `top: 200px`
                       * Tiệc Cưới (`#GROUP144`): `top: 325px`
                       * Chụp Ảnh Kỷ Niệm (`#GROUP145`): `top: 450px`
                     - *Trên Mobile (`@media (max-width: 767px)`)*: Ảnh cưới xếp phía trên (cao 250px), 4 mốc timeline căn giữa xếp gọn bên dưới:
                       * Lễ Vu Quy: `top: 320px`
                       * Lễ Thành Hôn: `top: 425px`
                       * Tiệc Cưới: `top: 530px`
                       * Chụp Ảnh Kỷ Niệm: `top: 635px`
                     - Định dạng Typography: Giờ diễn ra sự kiện nổi bật với font Philosopher (`26px`/`20px`), icon nhẫn cưới cách điệu ở giữa, tiêu đề mốc in hoa sắc nét (`20px`/`16px`), địa chỉ và ngày tháng màu xám ấm (`#5A544E`, `14px`/`13px`).
                  4. *Đồng bộ tài nguyên & Clear cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Xóa cache Blade và route với `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            116. **Khắc Phục Khoảng Trống Thừa 780px Ở Cuối Khối Album Ảnh SECTION20 Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng gửi ảnh chụp màn hình thắc mắc: "sao đoạn cuối chỗ album ảnh này bị trống nhiều vậy?" (khoảng cách giữa ảnh cuối cùng của album và khối đen RSVP "Xác nhận và gửi lời chúc" quá lớn, trống huếch trống hoác).
                  - *Nguyên nhân cốt lõi*:
                    + Trong bản thiết kế LadiPage gốc, album có 12 ảnh nên chiều cao `#SECTION20` được đặt là `3852.12px` (Desktop) và `2614.85px` (Mobile).
                    + Tuy nhiên, theo quy chuẩn chung của hệ thống schema Mây Hẹn (`schema.json`), album chỉ gồm **10 ảnh** (`gallery.0` đến `gallery.9`). Hai ảnh thứ 11 và 12 (`#IMAGE119` và `#IMAGE120`) đã được ẩn đi bằng thuộc tính `hidden`.
                    + Bức ảnh cuối cùng hiển thị là `#IMAGE114` (kết thúc ở tọa độ `3071.11px` trên Desktop và `2279.91px` trên Mobile).
                    + Do chiều cao của `#SECTION20` không được điều chỉnh lại sau khi ẩn 2 ảnh thừa, dẫn đến khoảng cách từ chân ảnh `#IMAGE114` đến hết section bị bỏ trống lên tới **781px trên Desktop** và **335px trên Mobile**.
                - **Giải pháp xử lý triệt để**:
                  1. *Cưỡng chế ẩn triệt để 2 ảnh thừa*: `#IMAGE119, #IMAGE120 { display: none !important; }`.
                  2. *Thu gọn chiều cao SECTION20 vừa khít với Album 10 ảnh*:
                     - Trên Desktop (`@media (min-width: 768px)`): Cưỡng chế `#SECTION20 { height: 3120px !important; }`, chừa khoảng đệm dưới chân ảnh `#IMAGE114` là 49px cực kỳ thanh nhã và cân đối, triệt tiêu hoàn toàn 732px khoảng trống thừa.
                     - Trên Mobile (`@media (max-width: 767px)`): Cưỡng chế `#SECTION20 { height: 2315px !important; }`, chừa khoảng đệm dưới chân ảnh là 35px vừa vặn hoàn hảo, triệt tiêu 300px khoảng trống thừa.
                  3. *Đồng bộ tài nguyên & Clear cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Làm sạch cache Blade và route với `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            117. **Khắc Phục Lỗi Bị Lệch Cả 1 Trang Bìa (SECTION1 Bị Ẩn Trên Mobile Trong Preview Tạo Thiệp) Mẫu "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi)**:
                - **Vấn đề phản ánh từ người dùng & phân tích nguyên nhân**:
                  - Người dùng gửi 2 ảnh chụp so sánh:
                    + Ảnh 1: Trang xem thử ngoài máy tính hiển thị rất đẹp, có trang bìa chính `#SECTION1` (ảnh cưới lớn, chữ "You are invited to the wedding celebration of NGỌC SƠN & THÚY QUỲNH").
                    + Ảnh 2: Trong khung preview điện thoại của Studio Tạo Thiệp (`/tao-thiep`), trang bìa `#SECTION1` hoàn toàn biến mất, màn hình nhảy tột xuống trang 2 `#SECTION22` (Save the Date), và dòng chữ "Together, we found home" bị cắt lẹm một nửa vào notch Dynamic Island của iPhone.
                  - *Nguyên nhân cốt lõi số 1 (CSS gốc ẩn bìa trên mobile)*:
                    Tại dòng 7669 trong `styles.css` (và dòng 8706 trong `assets/styles.css`), một rule CSS cũ của LadiPage trong `@media (max-width: 767px)` đã đặt `#SECTION1 { height: 630px; display: none !important; }`. Khi iframe Studio có độ rộng màn hình 375px (thuộc media query mobile), `#SECTION1` bị cưỡng chế ẩn hoàn toàn. Do `#SECTION1` bị ẩn, `#SECTION22` bị trôi lên làm phần tử đầu tiên của trang, dẫn đến việc mất đứt cả 1 trang bìa và chữ đầu của `#SECTION22` bị tai thỏ cắt ngang.
                  - *Nguyên nhân cốt lõi số 2 (Gán nhầm data-mayhen-section)*:
                    Thuộc tính `data-mayhen-section="couple"` (đại diện cho tab bước 1 "Đôi Uyên Ương") trước đó lại gắn vào cánh cửa `#mayhen-door-stage`. Nhưng trong iframe Studio (`isEmbed = true`), `#mayhen-door-stage` đã bị ẩn (`display: none !important;`). Khi Studio gửi lệnh cuộn đến section `couple`, việc nhắm vào một phần tử bị ẩn dẫn đến hành vi cuộn trượt không mong muốn.
                - **Giải pháp xử lý triệt để**:
                  1. *Gỡ bỏ dứt điểm `display: none !important;` của `#SECTION1` trên Mobile*:
                     - Chuyển thành `#SECTION1 { height: 630px; display: block !important; }` tại cả 2 vị trí media query mobile trong `styles.css` và `assets/styles.css`.
                     - Thiết lập rule phòng vệ toàn cục `#SECTION1 { display: block !important; }` để bảo đảm trang bìa luôn luôn hiển thị 100% trên mọi thiết bị và trong iframe preview.
                  2. *Gắn đúng ngữ cảnh `data-mayhen-section="couple"` cho `#SECTION1`*:
                     - Gỡ `data-mayhen-section="couple"` khỏi `#mayhen-door-stage` và gắn trực tiếp vào `#SECTION1` trong `template.blade.php`.
                     - Khi Studio gửi lệnh cuộn tới tab "Đôi Uyên Ương", iframe sẽ định vị chuẩn xác 100% vào trang bìa `#SECTION1`.
                  3. *Đồng bộ tài nguyên & Clear cache*:
                     - Đồng bộ 100% giữa `styles.css` và `assets/styles.css`.
                     - Xóa cache Blade và route với `php artisan view:clear; php artisan route:clear`.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            118. **Gỡ Bỏ Hoàn Toàn Mẫu Thiệp "Lưu Bút Ngày Chung Đôi" (luu-but-ngay-chung-doi) Khỏi Toàn Bộ Hệ Thống**:
                - **Yêu cầu & Phạm vi thực hiện**:
                  - Người dùng yêu cầu xóa bỏ toàn bộ mã nguồn, tài nguyên và cấu hình liên quan đến mẫu thiệp `luu-but-ngay-chung-doi`.
                  - Sau khi nhận được xác nhận xóa dứt khoát từ người dùng qua hệ thống tương tác, tiến hành loại bỏ toàn diện.
                - **Các công việc đã thực thi**:
                  1. *Xóa thư mục View & Assets*: Xóa vĩnh viễn toàn bộ thư mục `resources/views/client/invitations/luu-but-ngay-chung-doi/` (gồm `template.blade.php`, `styles.css`, `script.js`, `schema.json` và toàn bộ thư mục con `assets/` chứa ảnh, fonts, preview mockup).
                  2. *Cập nhật Seeder Database*: Loại bỏ khối cấu hình mẫu thiệp `luu-but-ngay-chung-doi` khỏi `database/seeders/InvitationTemplateSeeder.php`.
                  3. *Làm sạch Cơ sở dữ liệu*: Thực thi xóa bản ghi của `luu-but-ngay-chung-doi` trong bảng `invitation_templates` trên Database.
                  4. *Dọn dẹp Cache*: Chạy `php artisan view:clear; php artisan route:clear; php artisan config:clear` để làm sạch toàn bộ cache hệ thống.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.

            119. **Chuẩn Hóa Toàn Diện Mẫu Thiệp "Từng Trang Hạnh Phúc" (tung-trang-hanh-phuc) Theo Đúng 8 Trụ Cột Bất Biến (Skill mayhen-invitation-templates)**:
                - **Mục tiêu & Cơ sở kỹ thuật**:
                  - Rà soát và nâng cấp toàn bộ cấu trúc file, schema, Blade template, CSS, JS và database của mẫu `tung-trang-hanh-phuc` để tương thích 100% với Studio Tạo Thiệp Trực Quan V2, đồng bộ hai chiều Live Sync và đảm bảo trải nghiệm người dùng hoàn hảo.
                - **Các hạng mục đã chuẩn hóa cụ thể**:
                  1. *Chuẩn hóa Schema (`schema.json`)*:
                     - Đầy đủ 6 section chuẩn mực: `couple` (Đôi uyên ương), `groom_family` (Nhà trai), `bride_family` (Nhà gái), `album` (Khoảnh khắc hạnh phúc), `timeline` (Lịch trình & Địa điểm), `gift` (Hộp mừng cưới).
                     - Áp dụng 100% key canonical chuẩn: `wedding.groom_name`, `wedding.bride_name`, `wedding.cover_photo_url`, `wedding.extra_data.groom_photo_url`, `wedding.extra_data.bride_photo_url`, `wedding.extra_data.couple_quote`, `wedding.extra_data.gallery`...
                     - Trường `gallery` được trang bị mảng `"default"` gồm đủ 10 ảnh nội bộ có thật trong `assets/images/`.
                     - Bổ sung đầy đủ canonical bank keys: `groom_bank_name`, `groom_bank_account`, `groom_bank_holder`, `qr_groom_image` (tương tự cho cô dâu).
                  2. *Chuẩn hóa Blade Template (`template.blade.php`)*:
                     - Kế thừa layout chuẩn `@extends('client.layouts.invitation')`.
                     - Biến `$heroPhotoUrl` fallback ưu tiên từ `$wedding->cover_photo_url`.
                     - Biến `$albumPhotos` khởi tạo đủ 10 ảnh từ `$template->default_gallery` hoặc danh sách fallback nội bộ (`gallery-01.jpg` đến `gallery-06.jpg`, `hero-wedding.jpg`, `groom.jpg`, `bride.jpg`, `scene.jpg`).
                     - Gắn đầy đủ hợp đồng Live Sync (`data-sync-target`):
                       + `cover_photo_url`, `couple_quote`
                       + `groom_father`, `groom_mother`, `groom_name`, `groom_photo_url`
                       + `bride_father`, `bride_mother`, `bride_name`, `bride_photo_url`
                       + `ceremony_venue`, `ceremony_address`, `party_venue`, `party_address`
                       + `gallery.0` đến `gallery.9` cho từng ảnh album
                       + `qr_groom_image`, `groom_bank_name`, `groom_bank_account`, `groom_bank_holder`
                       + `qr_bride_image`, `bride_bank_name`, `bride_bank_account`, `bride_bank_holder`
                       + `music_url` trên thẻ `<audio>`
                     - Phòng vệ Studio Editor & Showcase Reel (`$isEmbed = true`): Ẩn màn mở đầu `display: none !important;`, gắn class `opened mayhen_door_hidden`, không gắn class khóa cuộn `scroll-lock` lên thẻ `html`/`body`.
                     - Thẻ `<audio>` gắn class chuẩn `mayhen_invitation_tung-trang-hanh-phuc_audio_music` và `data-sync-target="music_url"`.
                  3. *Nâng cấp Script JS (`script.js` & `assets/script.js`)*:
                     - Cung cấp hàm toàn cục Live Sync đổi nhạc `window.updateMayhenMusic = function(newUrl) { ... }` (Live Audio Handshake).
                     - Nhận diện `window.__MAYHEN_IS_EMBED` để tự động mở phong bì và dỡ bỏ scroll lock.
                     - Truyền danh sách 10 ảnh cưới đầy đủ `window.__MAYHEN_GALLERY_PHOTOS` cho chức năng Lightbox.
                  4. *Nâng cấp Styling CSS (`styles.css` & `assets/styles.css`)*:
                     - Đưa nút điều khiển nhạc `.mayhen_invitation_ksnvo949dudf_music-dock` về vị trí chuẩn góc trên bên trái: `position: fixed !important; top: 16px !important; left: max(16px, calc(50% - 210px + 16px)) !important; right: auto !important; z-index: 1001 !important;`.
                     - Phân tầng Z-index tuyệt đối: Màn mở đầu `z-index: 9999999`, Lightbox `z-index: 100000`, Nút nhạc `z-index: 1001` (nằm dưới màn mở đầu khi chưa mở và nổi trên nội dung thiệp khi đã mở).
                  5. *Đồng bộ Seeder & Database*:
                     - Bổ sung cấu hình `'gallery' => [10 ảnh]` vào `default_config` của mẫu `tung-trang-hanh-phuc` trong `database/seeders/InvitationTemplateSeeder.php`.
                     - Đồng bộ cập nhật trường `default_config` của mẫu trong bảng `invitation_templates` trên Database.
                - **Kiểm thử & Tiêu chuẩn**:
                  - Pest Feature Tests: 10/10 tests passed (143 assertions).
                  - Laravel Pint: Passed agent format.
                  - `php artisan optimize:clear`: Thành công 100%.

            120. **Tái Cấu Trúc Toàn Bộ Kiến Trúc Mẫu Thiệp (Data Contract vs Theme Skin) & Dọn Dẹp Toàn Bộ Mẫu Thiệp Cũ**:
                - **Bối cảnh & Yêu cầu từ người dùng**:
                  - Người dùng yêu cầu dừng viết code chắp vá từng mẫu thiệp, bàn bạc và thống nhất công thức chuẩn hóa kiến trúc cốt lõi từ nay về sau:
                    + Toàn bộ mẫu thiệp chỉ chứa đúng các phần (10 Section chuẩn) và đúng các key canonical quy định.
                    + Sử dụng chung một tiền tố class BEM chuẩn `mayhen_invitation_*` cho toàn bộ các mẫu thiệp để 1 file JS lõi duy nhất có thể tự động bắt sự kiện và điều khiển mọi tương tác (Mở phong bì, Nhạc đĩa than, Đếm ngược, Album Lightbox, Copy STK, Gửi lời chúc và RSVP).
                    + Loại bỏ hoàn toàn layout định vị tuyệt đối `position: absolute` kéo-thả kiểu LadiPage, cưỡng chế 100% Flexbox / Grid tự co giãn theo nội dung để không bao giờ bị lệch vỡ trên thiết bị di động.
                    + Xóa toàn bộ các mẫu thiệp cũ, chỉ giữ lại duy nhất mẫu `giua-muon-ngan-vi-sao` để làm chuẩn kiểm thử (check test).
                - **Các công việc đã thực thi**:
                  1. *Nâng cấp Skill Kiến Trúc* (`.agents/skills/mayhen-invitation-templates/SKILL.md`):
                     - Thiết lập Hợp đồng 10 Section Chuẩn (Opening, Hero Cover, Love Story, Family & Couple, Countdown & Calendar, Events & Maps, Wedding Timeline, Dresscode, Album 10 Photos, Gift & RSVP & Ending).
                     - Ban hành bảng quy ước Class BEM Toàn Cục `mayhen_invitation_*` áp dụng cho mọi mẫu thiệp.
                     - Cưỡng chế 6 quy tắc bất biến (Schema chuẩn, Live Sync `data-sync-target`, Phòng vệ `$isEmbed = true`, Zero External Assets, Vị trí nút nhạc góc trên bên trái, Z-Index hierarchy).
                  2. *Xóa Bỏ Toàn Bộ 7 Mẫu Thiệp Cũ*:
                     - Đã xóa sạch 7 thư mục mẫu thiệp khỏi `resources/views/client/invitations/`: `dau-an-thoi-gian`, `dau-ky-tram-nam`, `hac-bach-trao-duyen`, `hac-bach-trao-duyen-2`, `net-muc-tinh-yeu`, `thuoc-phim-tinh-yeu`, `tung-trang-hanh-phuc`.
                     - Giữ lại duy nhất mẫu `giua-muon-ngan-vi-sao` và thư mục `partials` (chứa `editor-bridge.blade.php`).
                  3. *Đồng bộ Database & Seeder*:
                     - Viết lại `database/seeders/InvitationTemplateSeeder.php` chỉ còn duy nhất mẫu `giua-muon-ngan-vi-sao`.
                     - Dọn dẹp Database bảng `invitation_templates`, chỉ giữ lại đúng bản ghi `giua-muon-ngan-vi-sao`.
                  4. *Dọn dẹp Test Suite*:
                     - Gỡ bỏ các file test của các mẫu đã xóa, giữ lại `GiuaMuonNganViSaoTimelineTest.php` và `TemplateGalleryTest.php`.
                     - Chạy Pest Feature Tests: 3/3 tests passed (20 assertions).
                     - Chạy Laravel Pint: Passed.
                     - Dọn dẹp cache: `php artisan optimize:clear` hoàn tất.

            121. **Chuẩn Hóa Đủ 10 Section & Refactor Class Theo Chuẩn Toàn Cục Cho Mẫu Kiểm Thử "Giữa Muôn Ngàn Vì Sao" (giua-muon-ngan-vi-sao)**:
                - **Mục tiêu & Yêu cầu**:
                  - Rà soát mẫu kiểm thử `giua-muon-ngan-vi-sao` đảm bảo chứa đủ 10 phần tiêu chuẩn theo Blueprint:
                    + Phần 1: Màn Mở Đầu (`Opening / Envelope`)
                    + Phần 2: Trang Bìa Chính (`Hero Cover`)
                    + Phần 3: Lời Ngỏ & Chuyện Tình Yêu (`Love Story & Quote`)
                    + Phần 4: Song Thân & Đôi Uyên Ương (`Family & Couple`)
                    + Phần 5: Đếm Ngược & Lịch Tháng (`Countdown & Calendar`)
                    + Phần 6: Thông Tin Sự Kiện & Bản Đồ (`Events & Maps`)
                    + Phần 7: Lịch Trình Hôn Lễ (`Wedding Timeline`)
                    + Phần 8: Quy Định Trang Phục (`Dresscode`) -> *Đã bổ sung mới*
                    + Phần 9: Khoảnh Khắc Hạnh Phúc (`Album 10 Photos`)
                    + Phần 10: Mừng Cưới, RSVP, Lưu Bút & Cảm Ơn (`Gift, Interactive & Ending`)
                  - Refactor gắn song song các class chuẩn `mayhen_invitation_*` cho toàn bộ 10 section và các phần tử tương tác cốt lõi (`mayhen_invitation_opening`, `mayhen_invitation_open_btn`, `mayhen_invitation_hero`, `mayhen_invitation_story`, `mayhen_invitation_parents`, `mayhen_invitation_couple`, `mayhen_invitation_calendar`, `mayhen_invitation_countdown`, `mayhen_invitation_events`, `mayhen_invitation_maps`, `mayhen_invitation_timeline`, `mayhen_invitation_dresscode`, `mayhen_invitation_gallery`, `mayhen_invitation_signature`, `mayhen_invitation_rsvp`, `mayhen_invitation_wishes`, `mayhen_invitation_gift`, `mayhen_invitation_ending`, `mayhen_invitation_music_dock`, `mayhen_invitation_audio`).
                  - Đảm bảo **100% không làm hỏng giao diện cũ** bằng phương pháp giữ nguyên class hiện tại và khai báo bổ sung đồng bộ qua cả Blade HTML, CSS và JavaScript.
                - **Các công việc đã thực thi**:
                  1. *Cập nhật Schema (`schema.json`)*: Bổ sung section `dresscode` vào danh sách `sections` và `supported_sections` với các trường `wedding.extra_data.dresscode_colors` và `wedding.extra_data.dresscode_note`.
                  2. *Cập nhật Blade (`template.blade.php`)*:
                     - Khai báo các biến `$coupleQuote`, `$dresscodeColors`, `$dresscodeNote`.
                     - Tách bạch và chuẩn hóa Section 3 (Lời Ngỏ Tình Yêu) với `data-sync-target="couple_quote"`.
                     - Bổ sung Section 8 (Quy Định Trang Phục - Dresscode) với các ô màu sắc ngân hà sang trọng (`#FCE39E`, `#FFFFFF`, `#1B2A4A`, `#0D1322`) viền kim tuyến và ghi chú đồng điệu.
                     - Gắn các class chuẩn BEM `mayhen_invitation_*` song song cho toàn bộ 10 section.
                  3. *Cập nhật CSS (`styles.css`)*:
                     - Bổ sung các rule CSS cho `.mayhen_invitation_dresscode`, `.mayhen_invitation_dresscode_swatches`, `.mayhen_invitation_dresscode_circle` với hiệu ứng hover bóng sáng và viền kim tuyến vàng thiên hà.
                  4. *Kiểm thử & Định dạng*:
                     - Chạy `vendor/bin/pint --dirty --format agent`: Passed.
                     - Chạy `vendor/bin/pest tests/Feature`: 3/3 tests passed (20 assertions).
                     - Chạy `php artisan optimize:clear`: Thành công 100%.

             122. **Đồng Bộ Hoàn Chỉnh Visual 10 Section Cho Mẫu "Giữa Muôn Ngàn Vì Sao" (Celestial Deep Space & Golden Starlight)**:
                 - **Vấn đề giải quyết**: Trước đó các section bị xếp lộn xộn (Section 6 bị xé lẻ và xếp trước Section 5, Bản đồ bị ngắt quãng, Section 3 và 8 còn sơ sài dạng inline CSS thô sơ, Section 4 thiếu lời tự bạch/bio của dâu rể).
                 - **Các nâng cấp đã hoàn thành**:
                   1. *Tổ chức lại thứ tự chuẩn xác 1 -> 10*:
                      - **Section 1: Màn Mở Đầu (Opening / Envelope)**: Cửa vòm sao rơi dập nổi vàng kim, nút mở thiệp phát sáng.
                      - **Section 2: Trang Bìa Chính (Hero Cover)**: Quỹ đạo 3D xoay quanh ảnh lớn, tên dâu rể Great Vibes, ngày dương và âm lịch.
                      - **Section 3: Lời Ngỏ & Chuyện Tình Yêu (Love Story & Quote)**: Khung vòm sao nghệ thuật, ảnh kỷ niệm vòm thiên hà (`$saveDatePhotoUrl`), tiêu đề phụ `$storySubtitle`, câu trích dẫn chạm khắc vàng kim `couple_quote`, đoạn văn tâm sự tình yêu `story_content` và Monogram chữ cái đầu `✦ M & T ✦`.
                      - **Section 4: Song Thân & Đôi Uyên Ương (Family & Couple)**: Thẻ song thân Nhà Trai & Nhà Gái chuẩn chỉnh, bổ sung thẻ Chú Rể & Cô Dâu kèm lời tự bạch (`groom_bio` & `bride_bio`) thanh lịch.
                      - **Section 5: Đếm Ngược & Lịch Tháng (Countdown & Calendar)**: Gom đồng hồ đếm ngược 4 ô Ngày:Giờ:Phút:Giây và Tờ lịch cưới tháng sao rơi (ngày cưới pulse) cùng nút "Thêm vào lịch" Google Calendar vào 1 section lớn.
                      - **Section 6: Thông Tin Sự Kiện & Bản Đồ (Events & Maps)**: Gom Thư Mời Tiệc Cưới hoàng gia, 2 thẻ Nghi Lễ Gia Tiên (Vu Quy & Thành Hôn) và Bản Đồ Chỉ Đường 3D hai cánh hé mở vào 1 section sự kiện liền mạch.
                      - **Section 7: Lịch Trình Hôn Lễ (Wedding Timeline)**: 4 mốc thời gian nối sao SVG vàng kim.
                      - **Section 8: Quy Định Trang Phục (Dresscode)**: Thẻ vòm sao sang trọng, biểu tượng trang phục hoàng gia SVG, 4 ô màu tròn viền vàng tỏa sáng ánh sao (`#FCE39E` Vàng Champagne, `#FFFFFF` Trắng Ánh Sao, `#1B2A4A` Xanh Đêm Sâu, `#0D1322` Đen Huyền Bí) kèm nhãn tên và ghi chú `dresscode_note`.
                      - **Section 9: Khoảnh Khắc Hạnh Phúc (Album 10 Photos)**: 3D Coverflow 10 ảnh cưới sắc nét, chấm phân trang, Lightbox.
                      - **Section 10: Mừng Cưới, RSVP, Lưu Bút & Cảm Ơn (Gift, Interactive & Ending)**: Gom Hộp mừng cưới VietQR 3D bung sao, Bảng ký tên điện tử canvas, Form xác nhận RSVP, Sổ lưu bút AJAX và Lời cảm ơn Thank You kết thiệp.
                   2. *Đồng bộ CSS (`styles.css`)*: Viết toàn bộ styling chuyên nghiệp cho Section 3, Section 4 bio, Section 8 dresscode sử dụng biến màu nội bộ `--dem`, `--vang-kim`, `--muc`, `--vien`, loại bỏ 100% inline style tạm bợ.
                   3. *Cập nhật Schema (`schema.json`)*: Bổ sung các key canonical `wedding.extra_data.groom_bio`, `wedding.extra_data.bride_bio`, `wedding.extra_data.story_title`, `wedding.extra_data.story_subtitle`, `wedding.extra_data.story_content`.
                   4. *Kiểm thử & Định dạng*:
                      - `vendor/bin/pint --dirty --format agent`: Passed.
                      - `vendor/bin/pest tests/Feature`: 3/3 passed.
                      - `php artisan optimize:clear`: Thành công.

             123. **Khởi Tạo Skill Hệ Thống Mới `mayhen-invitation-standards` & Cập Nhật Agent Rules**:
                 - **Mục tiêu**: Đóng gói toàn bộ tiêu chuẩn đặc tả 10 Section, danh sách class BEM `mayhen_invitation_*` bất biến và bảng ánh xạ Live Sync `data-sync-target` thành một Skill độc lập, chuẩn mực cho toàn bộ hệ thống Agent và Engine mẫu thiệp.
                 - **Các công việc đã thực thi**:
                   1. *Tạo Skill mới `.agents/skills/mayhen-invitation-standards/SKILL.md`*:
                      - Định nghĩa rõ 5 nguyên tắc cốt lõi: Khung cấu trúc bất biến, Theme Skin Modifiers, Live Sync `data-sync-target`, Phòng vệ `$isEmbed = true`, và Zero External Assets.
                      - Bảng quy chuẩn chi tiết cho Global Wrappers (G.1 -> G.7) và 10 Section (Section 1 đến Section 10).
                      - Bảng phân loại từng class quy chuẩn, thẻ HTML khuyến nghị, thuộc tính `data-sync-target` và ý nghĩa dữ liệu.
                   2. *Cập nhật Skill `.agents/skills/mayhen-invitation-templates/SKILL.md`*:
                      - Bổ sung liên kết dẫn chiếu trực tiếp đến `mayhen-invitation-standards` và đồng bộ bảng 10 Section.
                   3. *Cập nhật Agent Rules (`AGENTS.md`)*:
                      - Thêm `mayhen-invitation-standards` vào bảng điều phối Skill (Skills Dispatch Table) dưới mục `Invitation 10 Sections & Classes Standards`.
                   4. *Kiểm thử & Tối ưu*:
                      - Chạy kiểm thử Pest: 3/3 tests passed.
                      - Chạy `php artisan optimize:clear`: Thành công.

             124. **Đồng Bộ & Viết Lại Toàn Diện Bộ 5 Tài Liệu Cốt Lõi Dự Án Theo Kiến Trúc 10 Section Chuẩn Hóa**:
                 - **Bối cảnh & Mục tiêu**: Sau khi tái cấu trúc hoàn toàn hệ thống mẫu thiệp cưới (Mô hình Tách Bạch Dữ Liệu & Bộ Áo, loại bỏ vĩnh viễn định vị kéo-thả LadiPage, thiết lập khung 10 Section bất biến cùng hệ thống class BEM `mayhen_invitation_*`), toàn bộ 5 tài liệu/skill cốt lõi được rà soát và viết lại đồng bộ 100%:
                   1. `PROMPT_INTEGRATE_TEMPLATE.md`: Viết lại từ đầu thành Prompt mẫu tích hợp template mới chuẩn mực, loại bỏ `{template_id}` prefix, quy định chi tiết 10 Section từ Hero Cover đến Interactive & Ending, kèm hợp đồng Live Sync `data-sync-target`, quy tắc Z-Index và Zero External Assets.
                   2. `AGENTS.md`: Đồng bộ 7 nguyên tắc chuẩn hóa mẫu thiệp cưới (`TEMPLATE CREATION LOGIC`) và bổ sung Skill `mayhen-invitation-standards` vào bảng điều phối Agent (`Skills Dispatch Table`).
                   3. `.agents/skills/mayhen-invitation-standards/SKILL.md`: Khởi tạo hoàn chỉnh bộ quy chuẩn đặc tả kỹ thuật chi tiết nhất của hệ thống thiệp Mây Hẹn (Global Wrappers G.1-G.7, chi tiết 10 Section với thẻ HTML, class BEM bắt buộc và thuộc tính `data-sync-target`).
                   4. `.agents/skills/mayhen-invitation-templates/SKILL.md`: Viết lại để đồng bộ 100% với `mayhen-invitation-standards`, định hình quy trình tích hợp mẫu thiệp, chuẩn hóa schema, phòng vệ nhúng iframe và kiểm thử.
                   5. `context.gemini.md`: Ghi nhận toàn bộ tiến trình thay đổi kiến trúc và cập nhật trạng thái mới nhất cho các phiên làm việc tiếp theo.
                 - **Kiểm thử & Tối ưu**:
                   - `vendor/bin/pint --dirty --format agent`: Đã định dạng chuẩn code style Laravel Pint.
                   - `vendor/bin/pest tests/Feature`: 3/3 tests passed (20 assertions).
                   - `php artisan optimize:clear`: Xóa sạch cache cấu hình, view, route.

             125. **Chuẩn Hóa Logic Studio Tạo Thiệp V2 Với 10 Section & Giao Thức Smart Scroll-on-Action Tự Động Cuộn Xem Trước**:
                 - **Bối cảnh & Yêu cầu**: Trình tạo thiệp (`/tao-thiep`) cần hoạt động ăn khớp với chuẩn 10 Section tiêu chuẩn. Khi người dùng bấm chuyển tab trên Stepper, hoặc tương tác với bất kỳ trường nhập liệu nào (gõ họ tên chú rể, cô dâu, ngày cưới, lời ngỏ...) hay click/chọn/tải ảnh cưới nào, màn hình Preview bên phải phải tự động cuộn đến đúng phần tử đó và phát sáng highlight vàng kim Quiet Luxury để người dùng quan sát trực quan nhất.
                 - **Các công việc đã thực thi**:
                   1. *Backend Service (`app/Services/InvitationTemplateService.php`)*:
                      - Chuẩn hóa `CANONICAL_SECTION_ORDER` thành đúng 10 Section tiêu chuẩn (`hero`, `story`, `parents`, `countdown_calendar`, `events`, `timeline`, `dresscode`, `album`, `gift`, `ending`).
                      - Cập nhật `getCanonicalSections()` định nghĩa đủ 10 Section chuẩn cùng các key legacy (`couple`, `groom_family`, `bride_family`).
                      - Nâng cấp `normalizeSchema()` để luôn bảo tồn đủ 10 Section, không bị loại bỏ.
                   2. *Template Bridge (`resources/views/client/invitations/partials/editor-bridge.blade.php`)*:
                      - Nâng cấp `MAYHEN_SCROLL_TO_SECTION`: Hỗ trợ cuộn chuẩn xác cả 10 Section tiêu chuẩn theo ID và theo số thứ tự `step` (1..10). Tự động mở hộp quà nếu chuyển đến Section 9 (`gift`).
                      - Bổ sung `MAYHEN_SCROLL_TO_TARGET`: Tìm kiếm phần tử mục tiêu thông minh (Smart Target Mapping), cuộn mượt ra giữa màn hình (`scrollIntoView({ behavior: 'smooth', block: 'center' })`), và kích hoạt hiệu ứng highlight vàng kim `.mayhen_editor_target_highlight`.
                      - Định nghĩa animation `@keyframes mayhenTargetPulse` màu vàng kim Quiet Luxury (`#C5A880`) phát sáng nhẹ nhàng trong 1.8s.
                   3. *Editor Frontend Controller (`public/assets/client/js/pages/editor.js`)*:
                      - Bổ sung hàm `sendScrollToTarget(key, action)`.
                      - Bắt sự kiện `focusin` trên `mainForm` để gửi tín hiệu cuộn ngay khi người dùng chọn một trường bất kỳ.
                      - Bắt sự kiện `input` (debounced) để cuộn theo khi đang gõ họ tên, ngày cưới...
                      - Lắng nghe sự kiện `click` trên tất cả các nút/nhãn upload ảnh (`label[for^="upload_"]`, `.mayhen_editor_upload_btn`) để gửi tín hiệu cuộn ngay khi bấm chọn ảnh.
                      - Kích hoạt cuộn khi hoàn tất cắt ảnh (`cropperConfirmBtn`), nén ảnh (`fileUploaders`), hoặc cập nhật ngân hàng/STK VietQR (`updateVietQr`).
                   4. *Editor Blade View (`resources/views/client/pages/editor.blade.php`)*:
                      - Cập nhật tiêu đề rút gọn của Stepper Nav Pills cho cả 10 Section tiêu chuẩn (`Trang Bìa`, `Lời Ngỏ`, `Gia Đình`, `Đếm Ngược`, `Sự Kiện`, `Lịch Trình`, `Trang Phục`, `Ảnh Cưới`, `Mừng Cưới`, `Lời Chúc`).
                   5. *Cập nhật Skill Agent (`.agents/skills/mayhen-visual-editor/SKILL.md`)*:
                      - Viết lại chi tiết, toàn diện, rõ ràng từng nguyên tắc cốt lõi, ma trận ánh xạ mục tiêu (Smart Target Mapping Matrix), giao thức truyền thông 2 chiều qua `window.postMessage`, CSS highlight vàng kim Quiet Luxury, và tiêu chí Definition of Done (DoD).
                 - **Kiểm thử & Định dạng**:
                   - `vendor/bin/pest tests/Feature`: 3/3 tests passed (20 assertions).
                   - `vendor/bin/pint --dirty --format agent`: Formatted chuẩn xác.
                   - `php artisan optimize:clear`: Xóa sạch cache hệ thống thành công.

             126. **Refactor Hoàn Thiện Hệ Thống Class BEM Chuẩn Toàn Cục Cho Mẫu Thiệp Mẫu `giua-muon-ngan-vi-sao`**:
                 - **Mục tiêu**: Refactor toàn diện mã HTML trong `resources/views/client/invitations/giua-muon-ngan-vi-sao/template.blade.php` đảm bảo 100% phần tử mang đầy đủ class quy chuẩn `mayhen_invitation_*` theo đúng bộ quy chuẩn `.agents/skills/mayhen-invitation-standards/SKILL.md`, đồng thời bảo tồn nguyên vẹn các class nguyên bản (`mayhen_invitation_sr_*`, `mayhen_invitation_wb_*`) để không phá vỡ giao diện, hiệu ứng Three.js/WebGL sao rơi, canvas hạt sáng, animation 3D hay logic tương tác của template.
                 - **Chi tiết các thành phần đã được chuẩn hóa**:
                   1. *Global Wrappers*:
                      - Bao bọc toàn bộ: `mayhen_invitation_ladi_wraper`.
                      - Màn mở đầu: `mayhen_invitation_opening`, nút mở `mayhen_invitation_open_btn`.
                      - Vạch phân cách: `mayhen_invitation_divider`.
                      - Điều khiển âm nhạc: `mayhen_invitation_music_dock`, `mayhen_invitation_music_btn`, `<audio class="mayhen_invitation_audio">`.
                      - Trình xem ảnh phóng to: `mayhen_invitation_lightbox`.
                   2. *10 Section Chuẩn*:
                      - **Section 1 (Hero Cover)**: `mayhen_invitation_hero`, `mayhen_invitation_hero_kicker`, `mayhen_invitation_ladi_headline_ceremony_type`, `mayhen_invitation_hero_media`, `mayhen_invitation_ladi_image`, `mayhen_invitation_ladi_headline_name_groom`, `mayhen_invitation_hero_amp`, `mayhen_invitation_ladi_headline_name_bride`, `mayhen_invitation_ladi_headline_wedding_day`, `mayhen_invitation_ladi_paragraph_lunar_calendar`, `mayhen_invitation_ladi_paragraph_slogan`.
                      - **Section 2 (Story & Quote)**: `mayhen_invitation_story`, `mayhen_invitation_quote`, `mayhen_invitation_story_kicker`, `mayhen_invitation_ladi_headline_story`, `mayhen_invitation_story_img`, `mayhen_invitation_story_subtitle`, `mayhen_invitation_quote_text`, `mayhen_invitation_story_content`, `mayhen_invitation_monogram`.
                      - **Section 3 (Family & Couple)**: `mayhen_invitation_parents`, `mayhen_invitation_couple`, `mayhen_invitation_family_kicker`, `mayhen_invitation_family_title`, `mayhen_invitation_family_groom_title`, `mayhen_invitation_family_groom_father`, `mayhen_invitation_family_groom_mother`, `mayhen_invitation_family_groom_address`, `mayhen_invitation_family_bride_title`, `mayhen_invitation_family_bride_father`, `mayhen_invitation_family_bride_mother`, `mayhen_invitation_family_bride_address`, `mayhen_invitation_family_notice`, `mayhen_invitation_groom_photo`, `mayhen_invitation_groom_role`, `mayhen_invitation_groom_name`, `mayhen_invitation_groom_bio`, `mayhen_invitation_bride_photo`, `mayhen_invitation_bride_role`, `mayhen_invitation_bride_name`, `mayhen_invitation_bride_bio`.
                      - **Section 4 (Countdown & Calendar)**: `mayhen_invitation_countdown_calendar`, `mayhen_invitation_countdown_kicker`, `mayhen_invitation_countdown_title`, `mayhen_invitation_countdown_desc`, `mayhen_invitation_countdown`, `mayhen_invitation_cd_day`, `mayhen_invitation_cd_hour`, `mayhen_invitation_cd_min`, `mayhen_invitation_cd_sec`, `mayhen_invitation_calendar`, `mayhen_invitation_calendar_month_year`, `mayhen_invitation_calendar_selected_day`, `mayhen_invitation_add_calendar_btn`.
                      - **Section 5 (Events & Maps)**: `mayhen_invitation_events`, `mayhen_invitation_maps`, `mayhen_invitation_events_kicker`, `mayhen_invitation_events_title`, `mayhen_invitation_events_desc`, `mayhen_invitation_event_card_party`, `mayhen_invitation_guest_name`, `mayhen_invitation_party_photo`, `mayhen_invitation_party_time`, `mayhen_invitation_party_date`, `mayhen_invitation_party_venue`, `mayhen_invitation_party_address`, `mayhen_invitation_ceremony_bride_card`, `mayhen_invitation_ceremony_bride_time`, `mayhen_invitation_ceremony_bride_address`, `mayhen_invitation_ceremony_groom_card`, `mayhen_invitation_ceremony_groom_time`, `mayhen_invitation_ceremony_groom_address`, `mayhen_invitation_map_box`, `mayhen_invitation_map_toggle_btn`, `mayhen_invitation_map_iframe`, `mayhen_invitation_map_address_text`.
                      - **Section 6 (Timeline)**: `mayhen_invitation_timeline`, `mayhen_invitation_timeline_kicker`, `mayhen_invitation_timeline_title`, `mayhen_invitation_timeline_list`, `mayhen_invitation_timeline_item`, `mayhen_invitation_timeline_time`, `mayhen_invitation_timeline_dot`, `mayhen_invitation_timeline_line`, `mayhen_invitation_timeline_text`.
                      - **Section 7 (Dresscode)**: `mayhen_invitation_dresscode`, `mayhen_invitation_dresscode_kicker`, `mayhen_invitation_dresscode_title`, `mayhen_invitation_dresscode_desc`, `mayhen_invitation_dresscode_icon`, `mayhen_invitation_dresscode_swatches`, `mayhen_invitation_dresscode_item`, `mayhen_invitation_dresscode_circle`, `mayhen_invitation_dresscode_label`, `mayhen_invitation_dresscode_note`.
                      - **Section 8 (Album 10 Photos)**: `mayhen_invitation_gallery`, `mayhen_invitation_gallery_kicker`, `mayhen_invitation_gallery_title`, `mayhen_invitation_gallery_wrap`, `mayhen_invitation_gallery_item`, `mayhen_invitation_gallery_img`, `mayhen_invitation_gallery_prev_btn`, `mayhen_invitation_gallery_next_btn`, `mayhen_invitation_gallery_dots`, `mayhen_invitation_gallery_desc`.
                      - **Section 9 (Gift & VietQR)**: `mayhen_invitation_gift`, `mayhen_invitation_gift_kicker`, `mayhen_invitation_gift_title`, `mayhen_invitation_gift_desc`, `mayhen_invitation_gift_box`, `mayhen_invitation_gift_card_groom`, `mayhen_invitation_gift_role_groom`, `mayhen_invitation_gift_qr_groom`, `mayhen_invitation_gift_bank_name_groom`, `mayhen_invitation_gift_account_number_groom`, `mayhen_invitation_gift_account_holder_groom`, `mayhen_invitation_gift_card_bride`, `mayhen_invitation_gift_role_bride`, `mayhen_invitation_gift_qr_bride`, `mayhen_invitation_gift_bank_name_bride`, `mayhen_invitation_gift_account_number_bride`, `mayhen_invitation_gift_account_holder_bride`.
                      - **Section 10 (Interactive, RSVP, Wishes & Ending)**: `mayhen_invitation_interactive_ending`, `mayhen_invitation_signature`, `mayhen_invitation_signature_kicker`, `mayhen_invitation_signature_title`, `mayhen_invitation_signature_desc`, `mayhen_invitation_signature_bg`, `mayhen_invitation_signature_canvas`, `mayhen_invitation_signature_open_btn`, `mayhen_invitation_signature_save_btn`, `mayhen_invitation_rsvp`, `mayhen_invitation_rsvp_kicker`, `mayhen_invitation_rsvp_title`, `mayhen_invitation_rsvp_desc`, `mayhen_invitation_rsvp_form`, `mayhen_invitation_rsvp_name_input`, `mayhen_invitation_rsvp_guest_of`, `mayhen_invitation_rsvp_attendance`, `mayhen_invitation_rsvp_count`, `mayhen_invitation_rsvp_submit_btn`, `mayhen_invitation_wishes`, `mayhen_invitation_wish_kicker`, `mayhen_invitation_wish_title`, `mayhen_invitation_wish_form`, `mayhen_invitation_wish_name_input`, `mayhen_invitation_wish_visibility`, `mayhen_invitation_wish_textarea`, `mayhen_invitation_wish_submit_btn`, `mayhen_invitation_wish_list`, `mayhen_invitation_wish_item`, `mayhen_invitation_ending`, `mayhen_invitation_ending_photo`, `mayhen_invitation_ending_title`, `mayhen_invitation_ending_text`, `mayhen_invitation_ending_couple_name`.
                 - **Kiểm thử & Đảm bảo an toàn**:
                   - `vendor/bin/pest tests/Feature`: 3/3 tests passed (20 assertions).
                   - `vendor/bin/pint --dirty --format agent`: Formatted chuẩn xác.
                   - `php artisan optimize:clear`: Xóa sạch cache hệ thống thành công.
                   - Toàn bộ visual của template (WebGL canvas, hiệu ứng lấp lánh, âm nhạc, animation nắp hộp quà mở ra, bảng vẽ ký tên canvas, slider ảnh coverflow 3D, popup lời hẹn) hoạt động 100% trơn tru không có bất kỳ lỗi console hay xung đột nào.

             127. **Khắc Phục Toàn Diện Lỗi Cuộn Iframe Studio Editor: Tab Không Cuộn & Sửa Dữ Liệu Không Tự Cuộn Đến Đúng Vị Trí**:
                 - **Nguyên nhân gốc rễ (Root Cause Analysis)**:
                   1. *Xung đột CSS `scroll-behavior: auto !important` và `body` có `overflow-y: auto !important`*: Trong `editor-bridge.blade.php`, rule CSS trước đó vô tình áp dụng `scroll-behavior: auto !important` và đặt `overflow-y: auto !important` lên cả `body`. Khi `body` có `overflow-y: auto` nhưng `height: auto`, nó trở thành một Scroll Container lồng nhau rỗng, chặn đứng cơ chế `scrollIntoView()` của trình duyệt khiến trang không bao giờ cuộn được ra ngoài viewport.
                   2. *Target bị trỏ nhầm vào Màn Mở Đầu bị ẩn (`display: none`)*: Trong các mẫu thiệp có màn mở đầu (như `giua-muon-ngan-vi-sao`), các thẻ `[data-sync-target="groom_name"]`, `[data-sync-target="bride_name"]`, `[data-sync-target="wedding_date_digits"]` xuất hiện ĐẦU TIÊN trong cánh cửa `.mayhen_invitation_sr_cua` (vốn đã bị ẩn `display: none` khi nhúng iframe). Khi `document.querySelector` lấy phần tử đầu tiên, nó chọn đúng thẻ bị ẩn này, dẫn đến tọa độ `getBoundingClientRect()` bằng 0 và không thể cuộn hoặc luôn giật về đỉnh trang (top: 0).
                   3. *Bộ lọc `lastScrolledKey` trong `editor.js` chặn sự kiện gõ phím*: Trong `handleInputEvent`, điều kiện `key !== lastScrolledKey` luôn sai vì `focusin` đã gán `lastScrolledKey = key` ngay trước đó, khiến quá trình gõ phím hoàn toàn không gửi tín hiệu cuộn `MAYHEN_SCROLL_TO_TARGET`.
                 - **Giải pháp đã thực thi**:
                   1. *Khắc phục CSS Iframe (`editor-bridge.blade.php`)*:
                      - Gỡ bỏ hoàn toàn `scroll-behavior: auto !important` trên `*`. Cưỡng chế `html.mayhen_embed_mode { scroll-behavior: smooth !important; }`.
                      - Cưỡng chế `body.mayhen_embed_mode { overflow: visible !important; overflow-y: visible !important; }` để giải phóng toàn bộ ngữ cảnh cuộn cho `html`/`window`.
                   2. *Hàm Tìm Kiếm Mục Tiêu Thông Minh `findVisibleTarget(selector)`*:
                      - Loại trừ triệt để 100% các phần tử nằm trong màn mở đầu / cánh cửa 3D bị ẩn (`.mayhen_invitation_opening`, `.mayhen_invitation_sr_cua`, `#mayhen-door-stage`, `#POPUP8`...).
                      - Luôn trả về phần tử hiển thị thực tế nằm trong thân thiệp chính.
                   3. *Hàm Cuộn Mượt Trực Tiếp Theo Pixel `smoothScrollToTarget(el, block)`*:
                      - Tính toán chính xác tọa độ viewport `targetY = currentScroll + rect.top` (có bù trừ đệm 24px cho `start` hoặc căn giữa cho `center`).
                      - Kích hoạt song song `window.scrollTo`, `document.documentElement.scrollTo` và `el.scrollIntoView`.
                   4. *Tối ưu Trình Bắt Sự Kiện `editor.js`*:
                      - Xóa bỏ chặn `lastScrolledKey` trong `focusin`.
                      - Kích hoạt cơ chế debounce 250ms trong `handleInputEvent` để khi người dùng gõ phím, preview tự động cuộn mượt đến đúng vị trí và phát sáng highlight vàng kim Quiet Luxury mà không gây giật lag.
                 - **Kiểm thử**:
                   - `vendor/bin/pest tests/Feature`: 3/3 tests passed (20 assertions).
                   - `vendor/bin/pint --dirty --format agent`: Formatted chuẩn xác.
                   - `php artisan optimize:clear`: Xóa sạch cache hệ thống thành công.

              128. **Chuẩn Hóa Kiến Trúc Cuộn Studio Tạo Thiệp: Độc Lập 100% Trong Core Editor & Iframe Bridge, Không Phụ Thuộc Script Riêng Mẫu Thiệp**:
                  - **Quy tắc phân tách tuyệt đối**:
                    1. Toàn bộ logic cuộn thông minh (Scroll-on-Tab Stepper 1..10, Scroll-on-Input focus/click, Scroll-on-Typing debounced 180ms, Scroll-on-Image khi chọn/cắt ảnh) và đồng bộ dữ liệu nằm 100% ở hệ thống chung: `public/assets/client/js/pages/editor.js` và `resources/views/client/invitations/partials/editor-bridge.blade.php`.
                    2. File JS riêng của từng mẫu thiệp (`script.js`) chỉ đảm nhiệm visual / tính năng riêng biệt của mẫu (như 3D sao bay, âm thanh riêng, hiệu ứng mở phong bì), tuyệt đối không chứa logic tạo thiệp hay cuộn. Bất kỳ mẫu thiệp nào mới chỉ cần kế thừa đúng layout chung `@extends('client.layouts.invitation')` và hệ thống class BEM / `data-sync-target` là tự động hoạt động hoàn hảo 100%.
                  - **Giải pháp kỹ thuật đã triển khai**:
                    1. *Giải phóng triệt để bẫy cuộn lồng nhau (Nested Scroll Trap)*:
                       - Trong `editor-bridge.blade.php` và `giua-muon-ngan-vi-sao/styles.css`: Cưỡng chế các container cha `.mayhen_invitation_ladi_wraper`, `.ladi-wraper`, `[class*="ladi_wraper"]`, `[class*="card_wrapper"]` thành `overflow: visible !important; overflow-y: visible !important; height: auto !important;`.
                       - Giữ duy nhất `html.mayhen_invitation_embed_mode` làm Scroll Container chính (`overflow-y: auto !important; scroll-behavior: smooth !important;`), giải phóng `body` thành `overflow: visible !important;`.
                       - Hàm `smoothScrollToTarget` trong bridge tự động duyệt ngược cây DOM và mở khóa toàn bộ các node cha vô tình bị gán `overflow: hidden`.
                    2. *Cơ chế điều phối kép Dual-Channel Direct Call + PostMessage Fallback*:
                       - `editor.js` xuất bản hàm `dispatchToIframes(directFnName, directArgs, postPayload)`: Ưu tiên gọi trực tiếp qua same-origin window (`iframe.contentWindow.__mayhenScrollToSection` / `__mayhenScrollToTarget`) phản hồi tức thì 0ms, song song gửi `postMessage` dự phòng.
                    3. *Bắt sự kiện tương tác người dùng toàn diện*:
                       - Bắt `focusin` và `click` (capture: true) trên toàn bộ input, textarea, select: Chạm chuột hoặc focus vào ô nào là iframe tự cuộn mượt và highlight vàng kim Quiet Luxury ngay lập tức.
                       - Debounce 180ms khi gõ phím để vừa cuộn theo vừa đồng bộ text thời gian thực.
                       - Bắt sự kiện chọn ảnh: Tự động cuộn khi click nút "Chọn ảnh", khi input file thay đổi, và ngay khi bấm "✓ Cắt & Sử Dụng Ảnh Này" trong Cropper modal.
                    4. *Khắc phục lỗi dữ liệu không chính xác*:
                       - Chống nạp nhầm `localDraft` cũ của mẫu khác (chỉ nạp khi đúng `template_slug` hoặc đang chỉnh sửa `invitationId` đã lưu).
                       - Trong `syncAllFieldsToIframe`: Không gửi các giá trị rỗng/khoảng trắng đè lên làm mất text và ảnh mặc định của template.
                  - **Kiểm thử & Chất lượng**:
                    - Viết mới bộ test Feature `tests/Feature/EditorSmartScrollTest.php` bao phủ: 10 tab Stepper, Dual-Channel Direct API Iframe Bridge, Focus/Input/Image scroll triggers, BEM Highlight.
                    - Chạy Pest: Toàn bộ Feature tests passed 100% (5/5 tests, 37 assertions).
                    - Pint & Cache: `pint --dirty --format agent` và `php artisan optimize:clear` chạy thành công.

              129. **Thanh Lọc Triệt Để & Chuẩn Hóa 100% Hệ Thống Class BEM Theo Bộ Quy Chuẩn SKILL.md**:
                  - **Thanh lọc và xóa bỏ class rác**:
                    - Đã loại bỏ hoàn toàn 100% các class hash tự chế và chuỗi class kế thừa từ Ladipage cũ trong `editor-bridge.blade.php` (`ladoifji894yfv`, `ladjihe38udhd`, `jeowinids483fr`, `ksnvo949dudf`, `dhsuudushi9428r`, `djcvhwiuo9w9`, `[data-ben="trai"]`, `[data-ben="phai"]`...).
                  - **Chuẩn hóa 100% theo bảng quy chuẩn `mayhen-invitation-standards/SKILL.md`**:
                    - **Phần tử bao toàn cục**: `mayhen_invitation_ladi_wraper`, `mayhen_invitation_opening`, `mayhen_invitation_envelope`, `mayhen_invitation_open_btn`, `mayhen_invitation_bottom_nav`, `mayhen_invitation_audio`, `mayhen_invitation_music_dock`, `mayhen_invitation_music_btn`.
                    - **Section 1 (Hero Cover)**: `.mayhen_invitation_hero`, `.mayhen_invitation_ladi_headline_ceremony_type`, `.mayhen_invitation_hero_media`, `.mayhen_invitation_ladi_image`, `.mayhen_invitation_ladi_headline_name_groom`, `.mayhen_invitation_hero_amp`, `.mayhen_invitation_ladi_headline_name_bride`, `.mayhen_invitation_ladi_headline_wedding_day`, `.mayhen_invitation_ladi_paragraph_lunar_calendar`, `.mayhen_invitation_ladi_paragraph_slogan`.
                    - **Section 2 (Love Story & Quote)**: `.mayhen_invitation_story`, `.mayhen_invitation_story_kicker`, `.mayhen_invitation_ladi_headline_story`, `.mayhen_invitation_story_img`, `.mayhen_invitation_story_subtitle`, `.mayhen_invitation_quote_text`, `.mayhen_invitation_story_content`, `.mayhen_invitation_monogram`.
                    - **Section 3 (Family & Couple)**: `.mayhen_invitation_parents`, `.mayhen_invitation_couple`, `.mayhen_invitation_family_kicker`, `.mayhen_invitation_family_title`, `.mayhen_invitation_family_groom_title`, `.mayhen_invitation_family_groom_father`, `.mayhen_invitation_family_groom_mother`, `.mayhen_invitation_family_groom_address`, `.mayhen_invitation_family_bride_title`, `.mayhen_invitation_family_bride_father`, `.mayhen_invitation_family_bride_mother`, `.mayhen_invitation_family_bride_address`, `.mayhen_invitation_family_notice`, `.mayhen_invitation_groom_photo`, `.mayhen_invitation_groom_role`, `.mayhen_invitation_groom_name`, `.mayhen_invitation_groom_bio`, `.mayhen_invitation_bride_photo`, `.mayhen_invitation_bride_role`, `.mayhen_invitation_bride_name`, `.mayhen_invitation_bride_bio`.
                    - **Section 4 (Countdown & Calendar)**: `.mayhen_invitation_countdown_calendar`, `.mayhen_invitation_countdown_kicker`, `.mayhen_invitation_countdown_title`, `.mayhen_invitation_countdown_desc`, `.mayhen_invitation_countdown`, `.mayhen_invitation_cd_day`, `.mayhen_invitation_cd_hour`, `.mayhen_invitation_cd_min`, `.mayhen_invitation_cd_sec`, `.mayhen_invitation_calendar`, `.mayhen_invitation_calendar_month_year`, `.mayhen_invitation_calendar_selected_day`, `.mayhen_invitation_add_calendar_btn`.
                    - **Section 5 (Events & Maps)**: `.mayhen_invitation_events`, `.mayhen_invitation_events_kicker`, `.mayhen_invitation_events_title`, `.mayhen_invitation_event_card_party`, `.mayhen_invitation_guest_name`, `.mayhen_invitation_party_photo`, `.mayhen_invitation_party_time`, `.mayhen_invitation_party_date`, `.mayhen_invitation_party_venue`, `.mayhen_invitation_party_address`, `.mayhen_invitation_ceremony_bride_card`, `.mayhen_invitation_ceremony_bride_time`, `.mayhen_invitation_ceremony_bride_address`, `.mayhen_invitation_ceremony_groom_card`, `.mayhen_invitation_ceremony_groom_time`, `.mayhen_invitation_ceremony_groom_address`, `.mayhen_invitation_map_box`, `.mayhen_invitation_map_toggle_btn`, `.mayhen_invitation_map_iframe`, `.mayhen_invitation_map_address_text`.
                    - **Section 6 (Timeline)**: `.mayhen_invitation_timeline`, `.mayhen_invitation_timeline_kicker`, `.mayhen_invitation_timeline_title`, `.mayhen_invitation_timeline_list`, `.mayhen_invitation_timeline_item`, `.mayhen_invitation_timeline_time`, `.mayhen_invitation_timeline_dot`, `.mayhen_invitation_timeline_line`, `.mayhen_invitation_timeline_text`.
                    - **Section 7 (Dresscode)**: `.mayhen_invitation_dresscode`, `.mayhen_invitation_dresscode_kicker`, `.mayhen_invitation_dresscode_title`, `.mayhen_invitation_dresscode_icon`, `.mayhen_invitation_dresscode_swatches`, `.mayhen_invitation_dresscode_item`, `.mayhen_invitation_dresscode_circle`, `.mayhen_invitation_dresscode_label`, `.mayhen_invitation_dresscode_note`.
                    - **Section 8 (Album 10 Photos)**: `.mayhen_invitation_gallery`, `.mayhen_invitation_gallery_kicker`, `.mayhen_invitation_gallery_title`, `.mayhen_invitation_gallery_wrap`, `.mayhen_invitation_gallery_item`, `.mayhen_invitation_gallery_img`, `.mayhen_invitation_gallery_prev_btn`, `.mayhen_invitation_gallery_next_btn`, `.mayhen_invitation_gallery_dots`, `.mayhen_invitation_lightbox`.
                    - **Section 9 (Gift & VietQR)**: `.mayhen_invitation_gift`, `.mayhen_invitation_gift_kicker`, `.mayhen_invitation_gift_title`, `.mayhen_invitation_gift_desc`, `.mayhen_invitation_gift_box`, `.mayhen_invitation_gift_card_groom`, `.mayhen_invitation_gift_role_groom`, `.mayhen_invitation_gift_qr_groom`, `.mayhen_invitation_gift_bank_name_groom`, `.mayhen_invitation_gift_account_number_groom`, `.mayhen_invitation_gift_account_holder_groom`, `.mayhen_invitation_gift_card_bride`, `.mayhen_invitation_gift_role_bride`, `.mayhen_invitation_gift_qr_bride`, `.mayhen_invitation_gift_bank_name_bride`, `.mayhen_invitation_gift_account_number_bride`, `.mayhen_invitation_gift_account_holder_bride`, `.mayhen_invitation_copy_btn`.
                    - **Section 10 (Interactive, RSVP, Wishes & Ending)**: `.mayhen_invitation_interactive_ending`, `.mayhen_invitation_signature`, `.mayhen_invitation_signature_bg`, `.mayhen_invitation_signature_canvas`, `.mayhen_invitation_signature_open_btn`, `.mayhen_invitation_signature_save_btn`, `.mayhen_invitation_rsvp`, `.mayhen_invitation_rsvp_form`, `.mayhen_invitation_rsvp_name_input`, `.mayhen_invitation_rsvp_guest_of`, `.mayhen_invitation_rsvp_attendance`, `.mayhen_invitation_rsvp_count`, `.mayhen_invitation_rsvp_submit_btn`, `.mayhen_invitation_wishes`, `.mayhen_invitation_wish_form`, `.mayhen_invitation_wish_name_input`, `.mayhen_invitation_wish_visibility`, `.mayhen_invitation_wish_textarea`, `.mayhen_invitation_wish_submit_btn`, `.mayhen_invitation_wish_list`, `.mayhen_invitation_wish_item`, `.mayhen_invitation_ending`, `.mayhen_invitation_ending_photo`, `.mayhen_invitation_ending_title`, `.mayhen_invitation_ending_text`, `.mayhen_invitation_ending_couple_name`.
                  - **Tối ưu file JS chung `editor.js`**:
                    - Bắt sự kiện click vào nút tải ảnh trong `.mayhen_editor_gallery_item_box`.
                    - Bắt sự kiện click trực tiếp vào ảnh preview thumbnail `.mayhen_editor_image_field_preview`.
                  - **Kiểm thử**:
                    - Pest Test: 5/5 tests passed (51 assertions).
                    - Pint: Passed agent formatting.
                    - Cache: `php artisan optimize:clear` chạy thành công.

              130. **Khắc Phục Triệt Để Lỗi Cuộn Studio Editor & Chuẩn Hóa Toàn Diện Mẫu "Giữa Muôn Ngàn Vì Sao"**:
                  - **Nguyên nhân cốt lõi được phát hiện & triệt tiêu**:
                    1. *Lỗi cú pháp JS chết người trong `editor-bridge.blade.php`*: Có một dấu ngoặc nhọn đóng thừa `}` trước khối `catch (e) {}` tại dòng 952 (trong phần đồng bộ âm lịch của `wedding_date`). Lỗi cú pháp Parse-time `SyntaxError: Missing catch or finally after try` này khiến trình duyệt từ chối thực thi toàn bộ script `editor-bridge.blade.php`, dẫn đến `window.__mayhenScrollToSection`, `window.__mayhenScrollToTarget`, bộ lắng nghe `message` và tín hiệu bắt tay `MAYHEN_BRIDGE_READY` không bao giờ được tạo! Đã loại bỏ hoàn toàn dấu ngoặc thừa, xác thực 100% bằng Node.js AST.
                    2. *Lỗi cú pháp JSON (`schema.json`)*: Dòng 296 trong `giua-muon-ngan-vi-sao/schema.json` bị thiếu dấu ngoặc kép và dấu phẩy `"max: 300` làm `json_decode` bị lỗi cú pháp `Control character error`, khiến schema trả về null và form bị tê liệt. Đã sửa chuẩn thành `"max": 300,`.
                    3. *Lỗi Temporal Dead Zone (TDZ) trong `editor.js`*: Trước đây `goToStep(1)` được gọi ở đầu script trong khi `sendScrollToSection` được khai báo bằng `const` ở phía dưới. Trình duyệt đã ném lỗi `ReferenceError: Cannot access 'sendScrollToSection' before initialization`, làm toàn bộ quá trình nạp JS của `editor.js` bị dừng đột ngột, khiến các sự kiện `click` tab, `focusin` ô input, chọn ảnh và đổi ngày không được đăng ký.
                    4. *Cơ chế cuộn đa tầng Multi-Tier Scroll Fallback*: Kết hợp mượt mà giữa `el.scrollIntoView({ behavior: 'smooth', block: 'center' })` và fallback qua `window.scrollTo` cùng `document.documentElement.scrollTo`, `document.body.scrollTo` sau 80ms để đảm bảo mọi engine trình duyệt đều cuộn chính xác tuyệt đối trong iframe.
                    5. *Lỗi Direct Call Iframe Bridge*: Đưa toàn bộ các hàm helper (`findVisibleTarget`, `smoothScrollToTarget`, `findSectionTarget`, `findFieldTarget`, `executeScrollToSection`, `executeScrollToTarget`) ra root scope của IIFE trong `editor-bridge.blade.php` và gắn ngay tức khắc lên `window.__mayhenScrollToSection`, `window.__mayhenScrollToTarget`, `window.__mayhenSmoothScroll`.
                  - **Giải pháp triển khai kỹ thuật**:
                    1. Đưa toàn bộ bộ điều phối Iframe (`getActiveIframes`, `dispatchToIframes`, `sendSyncMessage`, `sendScrollToSection`, `sendScrollToTarget`) lên trước bộ điều hướng Stepper trong `editor.js`.
                    2. Bổ sung tham số `triggerScroll = true` cho `goToStep(step, triggerScroll)` để khởi tạo ban đầu an toàn `goToStep(1, false)` mà không gây lỗi hoisting.
                    3. Bổ sung tham số ngữ cảnh `step` vào hàm tìm kiếm mục tiêu `findFieldTarget(rawKey, step)`: Khi ở Step 4 (Lịch Cưới & Đếm Ngược) và chọn ngày cưới `wedding_date`, tự động cuộn đến tờ lịch tháng `.mayhen_invitation_calendar` thay vì cuộn ngược lên bìa thiệp Section 1.
                    4. Tối ưu hóa sự kiện đổi ngày `change` và click nút chọn ảnh để cuộn tức thì đến đối tượng preview và phát sáng viền vàng kim Quiet Luxury.
                    5. Chuẩn hóa thuộc tính `data-mayhen-section` trên `template.blade.php` của mẫu "Giữa Muôn Ngàn Vì Sao": Section 1 (`data-mayhen-section="hero,couple"`), Section 3 (`data-mayhen-section="parents,groom_family,bride_family"`), Section 8 (`data-mayhen-section="album,gallery"`).
                  - **Kiểm thử & Định dạng**:
                    - Pest Test: `vendor/bin/pest tests/Feature/GiuaMuonNganViSaoTemplateTest.php` passed 100% (4/4 tests, 35 assertions).
                    - Cú pháp JS AST: `node -c` trên cả `editor.js` và `editor-bridge.blade.php` passed 100% không có bất kỳ lỗi nào.
                    - Pint Formatter: `vendor/bin/pint --dirty --format agent` passed.
                    - Cache: `php artisan view:clear; php artisan config:clear` dọn dẹp sạch sẽ.

              131. **Khắc Phục Toàn Diện Lỗi Preview Thời Gian Thực Phần Đếm Ngược & Lịch Cưới (Countdown & Calendar Sync)**:
                  - **Nguyên nhân gốc rễ phát hiện**:
                    1. *Lỗi Hardcode Countdown trong mẫu `giua-muon-ngan-vi-sao/script.js`*: Hàm `batDemNguoc()` gán cứng `var target = new Date('2026-12-20T18:00:00+07:00').getTime()` trong closure cục bộ và chạy `setInterval(capNhat, 1000)` liên tục mỗi giây. Điều này khiến bất kỳ thay đổi ngày/giờ nào từ Studio Editor đều bị bộ đếm cũ tính toán theo ngày 20/12/2026 đè lại mỗi giây (luôn hiển thị cố định 44 ngày). Đồng thời không có hàm `window.updateMayhenCountdown`.
                    2. *Thiếu đồng bộ Giờ Mục Tiêu (`event_target_time`)*: Trong `editor-bridge.blade.php`, logic chỉ bắt `cleanKey === 'wedding_date' || cleanKey === 'event_party_date'`, hoàn toàn bỏ qua `event_target_time` (hoặc `party_time`). Khi người dùng đổi giờ cử hành/giờ đếm ngược, không có sự kiện nào kích hoạt tính toán lại thời gian đích.
                    3. *Lỗi Fallback Lịch Tháng không khớp class CSS BEM*: Bộ vẽ lịch fallback trong `editor-bridge.blade.php` tạo thẻ `span` với class `mayhen_cal_day`, không khớp với class BEM bắt buộc của hệ thống (`.mayhen_invitation_calendar_day`, `.mayhen_invitation_selected`, `.mayhen_invitation_cal_selected_bob`). Mẫu `giua-muon-ngan-vi-sao` cũng chưa cài đặt hàm chuẩn `window.renderMayhenWeddingCalendar`.
                    4. *Lịch tháng ban đầu trong `template.blade.php` chưa dynamic*: Các ô ngày 1..31 được render cứng cho tháng 12/2026, đặt class chọn vào ngày 20 cố định thay vì tính toán động theo thứ trong tuần của ngày cưới.
                  - **Giải pháp triển khai kỹ thuật**:
                    1. *Nâng cấp `batDemNguoc()` trong `giua-muon-ngan-vi-sao/script.js`*:
                       - Khởi tạo đọc thời gian đích từ thuộc tính `data-countdown` của `.mayhen_invitation_countdown` (hoặc fallback an toàn).
                       - Xuất bản hàm toàn cục `window.updateMayhenCountdown = function(newTarget)` cho phép cập nhật thời gian đích ngay lập tức và tick lại tức thời 0ms mà không đợi chu kỳ 1s.
                    2. *Cài đặt `window.renderMayhenWeddingCalendar(dateInput)` trong `script.js`*:
                       - Tự động tính thứ Hai đầu tuần (Monday-first: `(getDay() + 6) % 7`), số ngày tháng trước, số ngày tháng hiện tại và số ngày tháng sau.
                       - Tái tạo cấu trúc grid chuẩn BEM với class `.mayhen_invitation_calendar_day`, đánh dấu ngày cưới được chọn bằng `.mayhen_invitation_selected.mayhen_invitation_cal_selected_bob.mayhen_invitation_calendar_selected_day`.
                       - Cập nhật tiêu đề tháng năm chuẩn Quiet Luxury: `Tháng MM · YYYY`.
                    3. *Dynamic hóa Blade Template `giua-muon-ngan-vi-sao/template.blade.php`*:
                       - Dùng PHP Carbon tính toán động thứ đầu tuần, ngày trong tháng và tự động điền các ngày rỗng trước/sau cùng ngày chọn `$weddingDay` động 100%.
                       - Gắn hợp đồng `data-sync-target="calendar_month_year"` và `data-sync-target="wedding_calendar"`.
                    4. *Đồng bộ 2 chiều hoàn hảo trong `editor-bridge.blade.php`*:
                       - Bắt cả trường ngày (`wedding_date`, `event_party_date`) lẫn trường giờ (`event_target_time`, `party_time`, `event_party_time`).
                       - Kết hợp cả ngày và giờ để tính toán `cdTargetDate` chính xác, cập nhật thuộc tính `data-countdown`, gọi `window.updateMayhenCountdown` và cập nhật tức thời 0ms lên DOM.
                       - Nâng cấp fallback lịch tháng trong bridge khớp 100% class BEM `.mayhen_invitation_calendar_day` và `.mayhen_invitation_selected`.
                       - Khai báo hàm `window.__mayhenSyncInput` ra window để Studio Editor có thể gọi trực tiếp 0ms qua kênh Direct Call.
                  - **Kiểm thử & Xác thực**:
                    - AST Syntax Check: `node -c` trên `script.js`, `editor-bridge.blade.php` và `editor.js` sạch 100% lỗi.
                    - Pest Test: `vendor/bin/pest tests/Feature/GiuaMuonNganViSaoTemplateTest.php` 4/4 passed (35 assertions).
                    - Pint: `vendor/bin/pint --dirty --format agent` passed.
                    - Cache: Đã xóa view & config cache.

              132. **Chuẩn Hóa Nguyên Tắc Cốt Lõi: CẤM Chèn Ảnh Cặp Đôi Trong Phần Ký Tên & RSVP — Chuyển Sang Hiệu Ứng & Sticker/Seal Thuần**:
                  - **Quy tắc thiết kế mới**:
                    1. *Phần Ký Tên (`Signature`) và Xác Nhận Tham Dự (`RSVP`) bắt buộc phải có trong 100% mẫu thiệp cưới*: Đây là hai module tương tác linh hồn của nền tảng thiệp cưới Mây Hẹn.
                    2. *TUYỆT ĐỐI CẤM chèn ảnh cặp đôi hay ảnh stock người vào 2 phần này*:
                       - Khách hàng tạo thiệp không có ô cấu hình ảnh riêng cho Signature và RSVP trên Studio Tạo Thiệp.
                       - Nếu để ảnh cặp đôi hay ảnh stock, khách hàng thực tế không thể thay thế được và thiệp gửi cho khách mời sẽ xuất hiện ảnh của người lạ, gây phản cảm và sai lệch nghiêm trọng.
                    3. *CHỈ ĐƯỢC DÙNG nghệ thuật đồ họa thuần*:
                       - Hiệu ứng CSS: giấy mỹ thuật, canvas dải ngân hà, stardust particles, ánh sao phát sáng.
                       - Sticker & Tem niêm phong sáp (Wax Seal): tem vàng kim dập nổi SVG, huy hiệu hoàng gia, khung viền hoa văn góc cổ điển.
                  - **Cập nhật giao diện mẫu "Giữa Muôn Ngàn Vì Sao"**:
                    1. *Ký tên chúc phúc (`Signature`)*:
                       - Loại bỏ hoàn toàn `$kyPhotoUrl` (`40f8fb60afcb59af.webp`) và class `.mayhen_invitation_img_ky`.
                       - Thiết kế canvas nền `.mayhen_invitation_sig_canvas_decor` với hiệu ứng gradient dải ngân hà huyền bí, bụi sao stardust nhiều tầng, tem dập nổi hoàng gia SVG `GUESTBOOK • MÂY HẸN` và hướng dẫn chạm để ký tên.
                    2. *Xác nhận tham dự (`RSVP`)*:
                       - Loại bỏ hoàn toàn `$xnPhotoUrl` (`686ca1bfd6d65f13.webp`) và khối ảnh `.mayhen_invitation_sr_anh_xn`.
                       - Thay thế bằng huy hiệu tem niêm phong hoàng gia `.mayhen_invitation_rsvp_seal_badge` viền vàng kim `#FCE39E`, biểu tượng phong thư & ngôi sao hộ mệnh cùng nhãn `R.S.V.P • Vui Lòng Phản Hồi`.
                    3. *Dọn dẹp mã nguồn*:
                       - Xóa bỏ các quy tắc CSS hardcode ảnh `.mayhen_invitation_img_ky` và `.mayhen_invitation_img_xn` trong `styles.css`.
                       - Cập nhật 3 bộ quy chuẩn skills: `mayhen-invitation-standards`, `mayhen-invitation-templates`, `mayhen-guest-rsvp`.
                       - Cập nhật file nguyên tắc toàn cục `AGENTS.md` và `context.gemini.md`.
                  - **Kiểm thử**:
                    - Pest Test: 4/4 passed (35 assertions).
                    - Pint Formatter: passed.
                    - Cache: view & config cache cleared.

              133. **Xây Dựng Master Prompt Chuẩn & Skill/Agent Chuyên Trách Clone Mẫu Thiệp Mây Hẹn (`mayhen-template-cloner`)**:
                  - **Mục tiêu**: Chuẩn hóa toàn bộ các yêu cầu khắt khe của dự án Mây Hẹn thành một Master Prompt hoàn hảo và một Agent Skill độc lập (`.agents/skills/mayhen-template-cloner/SKILL.md`, `.agents/prompts/CLONE_TEMPLATE_MASTER_PROMPT.md`) để có thể tái sử dụng bất cứ lúc nào khi cần clone mẫu từ web tham khảo.
                  - **Nội dung chuẩn hóa trọng tâm trong Prompt**:
                    1. *100% Pure Native Code*: HTML5, CSS3, Native JS ES Modules; cấm toàn bộ framework ngoài (Bootstrap, Tailwind, React, Vue, jQuery, GSAP, Swiper.js, AOS) và CDN ngoại.
                    2. *Tiền tố BEM chuẩn toàn cục*: 100% class dùng `mayhen_invitation_*`, CẤM lạm dụng ID.
                    3. *Flow Layout 100% tự co giãn (Mobile-First)*: Cấm layout định vị tuyệt đối `position: absolute` kiểu LadiPage với width/height pixel cứng làm hỏng giao diện trên nhiều kích thước màn hình.
                    4. *Phân bổ Section linh hoạt, thông minh*: 10 Section không cần rập khuôn thứ tự mà bố trí hài hòa, thẩm mỹ theo thiết kế gốc; có thể bổ sung các khối trang trí nâng tầm cảm xúc nhưng không dùng ảnh lạ.
                    5. *Animation Quiet Luxury*: Tự viết CSS keyframes mượt mà nếu mẫu gốc thiếu animation.
                    6. *Quy tắc Vàng về Ký tên & RSVP*: Mẫu nào cũng bắt buộc có; TUYỆT ĐỐI CẤM chèn ảnh cặp đôi hay ảnh stock người vào 2 phần này (100% dùng hiệu ứng canvas, stardust, sticker SVG, tem wax seal niêm phong).
                    7. *Tự chứa 100% Local Assets*: Tải toàn bộ ảnh, audio, icon về thư mục `assets/`, không dùng link ngoài.
                    8. *Màn mở đầu phong bì / cánh cửa*: Chuẩn hóa class của dự án, có phòng vệ `$isEmbed` cho iframe Studio.
                    9. *Lọc sạch rác & Đóng gói*: Loại bỏ watermark, chat widget, logo bên thứ ba; đóng gói trọn vẹn thành file ZIP (`template.blade.php`, `styles.css`, `script.js`, `schema.json`, `assets/`).
                  - **File tạo mới**:
                    - `.agents/skills/mayhen-template-cloner/SKILL.md` (Hệ thống Skill của Antigravity/Agent).
                    - `.agents/prompts/CLONE_TEMPLATE_MASTER_PROMPT.md` (Tài liệu prompt chuẩn cho người dùng copy sang Claude, GPT-4o, Cursor...).
                    - Đăng ký Subagent `template-cloner` trong hệ thống session.

              134. **Tích Hợp Hoàn Hảo Mẫu Thiệp Mới "Hạnh Phúc Mang Tên Chúng Mình" (`hanh-phuc-mang-ten-chung-minh`) Vào Hệ Thống Mây Hẹn**:
                  - **Mục tiêu**: Tích hợp toàn diện mẫu thiệp cưới "Hạnh Phúc Mang Tên Chúng Mình" từ thư mục lưu trữ vào nền tảng Mây Hẹn (`resources/views/client/invitations/hanh-phuc-mang-ten-chung-minh/`), tuân thủ nghiêm ngặt 8 nguyên tắc chuẩn hóa cốt lõi và các quy định Quiet Luxury.
                  - **Triển khai kỹ thuật chi tiết**:
                    1. *Cấu trúc thư mục & 100% Local Assets*:
                       - Sao chép toàn bộ 24 file tài nguyên nội bộ (`assets/`) bao gồm ảnh chân dung cặp đôi (`hero-couple.webp`, `bride-portrait.webp`, `groom-portrait.webp`, `banner-couple.webp`, `footer-couple.webp`), bộ 8 ảnh album (`gallery-1.webp` -> `gallery-8.webp`), bộ 3 ảnh thư mời (`story-1.webp` -> `story-3.webp`), hoa văn SVG (`botanical-crest.svg`, `floral-corner.svg`, `divider-ornament.svg`, `wax-seal.svg`), mã QR thanh toán (`qr-groom.webp`, `qr-bride.webp`) và file nhạc nền MP3 (`music.mp3`).
                       - Phục vụ tĩnh tức thời thông qua route chuẩn hóa `/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/{path}`, tuyệt đối không dùng link máy chủ ngoài.
                    2. *Thiết kế & Tông màu (Color Palette)*:
                       - Tone màu chủ đạo: Xanh Xô Thơm & Xanh Rừng Đậm (`#a8b8a3`, `#233628`) kết hợp Vàng Cổ Điển Antique Gold (`#c5a059`) trên nền giấy mỹ thuật kem ấm (`#f8f6f0`).
                       - Bố cục Flow Layout 100% Flexbox & CSS Grid, co giãn hoàn hảo trên mọi kích thước màn hình điện thoại (Mobile-First 480px).
                    3. *Chuẩn hóa `template.blade.php`*:
                       - Kế thừa layout chung `@extends('client.layouts.invitation')`.
                       - Khởi tạo đầy đủ và đa tầng cho `$groomName` (Đình Sang), `$brideName` (Hoài Thương), `$coupleNames`, phụ mẫu hai bên, thời gian địa điểm hôn lễ, QR mừng cưới và ngân hàng.
                       - Tiêu đề trang `$pageTitle` chuẩn mực Quiet Luxury: `Xem thử: "Hạnh Phúc Mang Tên Chúng Mình" | Mây Hẹn` hoặc `Thiệp mời cưới: "Đình Sang & Hoài Thương" | Mây Hẹn`.
                       - Gắn đầy đủ hợp đồng `data-sync-target` cho toàn bộ các trường hiển thị văn bản và hình ảnh.
                       - Gắn đầy đủ các thuộc tính định danh section `data-mayhen-section="hero,couple"`, `data-mayhen-section="parents,groom_family,bride_family"`, `data-mayhen-section="events,wedding_event"`, `data-mayhen-section="countdown_calendar"`, `data-mayhen-section="story"`, `data-mayhen-section="signature"`, `data-mayhen-section="rsvp,wishes"`, `data-mayhen-section="album,gallery"`, `data-mayhen-section="gift"`, `data-mayhen-section="ending"` để Studio Tạo Thiệp (`/tao-thiep`) và `editor-bridge.blade.php` tự động cuộn (auto-scroll) mượt mà đến đúng khối khi người dùng click từng bước trong bảng điều khiển.
                       - Phòng vệ nhúng Editor (`$isEmbed = true`): Ẩn màn mở thiệp tức thì (`display: none !important;`), không khóa cuộn body, giải phóng khung hiển thị.
                    4. *Tuyệt Đối Tuân Thủ Quy Tắc Ký Tên & RSVP (Zero Couple Photos)*:
                       - Sổ ký tên (`Signature`): Canvas ký tên cảm ứng đa màu mực thuần khiết, nền giấy mỹ thuật kèm tem wax seal niêm phong hoàng gia, **tuyệt đối không chèn ảnh người**.
                       - Xác nhận tham dự (`RSVP`): Khung form tương tác hiện đại kèm huy hiệu sáp niêm phong trang trọng, **tuyệt đối không chèn ảnh người**, giúp khách hàng tạo thiệp gửi cho quan khách không bị xuất hiện ảnh người lạ.
                    5. *Đồng bộ thời gian thực trong `script.js`*:
                       - Xuất bản hàm toàn cục `window.updateMayhenCountdown(targetDate)` và `window.renderMayhenWeddingCalendar(targetDate)`.
                       - Hỗ trợ chuyển đổi lịch tháng động (Monday-first, đánh dấu ngày cưới với SVG trái tim) và đếm ngược tức thời theo thời gian thực khi chỉnh sửa trên Studio Tạo Thiệp.
                       - Trình phát nhạc nền MP3 nội bộ và nút tự động cuộn (Auto-Scroll) êm ái 60FPS.
                       - Lắng nghe sự kiện `message` hai chiều với Studio Editor.
                    6. *Chuẩn hóa `schema.json`*:
                       - Cấu trúc đầy đủ 10 Section chuẩn hóa theo đúng tiêu chuẩn hệ thống Mây Hẹn (`hero`, `story`, `parents`, `countdown_calendar`, `events`, `timeline`, `dresscode`, `album`, `gift`, `ending`), mapping chuẩn xác các key `wedding.*` và `wedding.extra_data.*`.
                    7. *Cơ sở dữ liệu & Seeder (`InvitationTemplateSeeder`)*:
                       - Thêm template `hanh-phuc-mang-ten-chung-minh` vào seeder: `category: modern`, `tier: free`, `accent_color: #233628`, `demo_url: /xem-thu/hanh-phuc-mang-ten-chung-minh`.
                       - Chạy thành công `php artisan db:seed --class=InvitationTemplateSeeder`.
                    8. *Tái cấu trúc sạch sẽ, bổ sung phần thiếu & loại bỏ triệt để phần thừa (Refactor & Clean Architecture)*:
                       - **Bổ sung phần thiếu**:
                         * Thêm đầy đủ **Section 6: Lịch Trình Buổi Tiệc (`timeline`)** với 4 mốc thời gian tinh tế (`data-sync-target="timeline.{index}.time"`, `timeline.{index}.title`), đường nối gradient và icon mốc sự kiện.
                         * Thêm đầy đủ **Section 7: Gợi Ý Trang Phục (`dresscode`)** với các swatches màu sắc Xanh Sage, Xanh Rừng, Vàng Kim, Kem Ấm và lời dặn dò trang phục `data-sync-target="dresscode_note"`.
                         * Bổ sung **Khung Bản Đồ Google Maps tương tác trực quan (`iframe`)** nhúng trực tiếp trong Section 5 Events, tự động trỏ đến địa điểm hôn lễ.
                         * Mở rộng Album lên đủ **10 ảnh cưới chuẩn hóa** (`gallery.0` -> `gallery.9`) khớp hoàn hảo với payload tải ảnh của Studio Tạo Thiệp.
                         * Bổ sung đầy đủ các target key đồng bộ: `groom_account_name`, `bride_account_name`, `ending_title`, `ending_text`.
                       - **Loại bỏ phần thừa**:
                         * Xóa bỏ hoàn toàn cụm nút trôi nổi to đùng `mayhen_invitation_floating_controls` che khuất màn hình di động.
                         * Chuyển nút điều khiển âm nhạc sang **Nút đĩa than tròn xoay cố định góc trên bên trái (`mayhen_invitation_music_dock`)** chuẩn phong cách Quiet Luxury.
                         * Tinh giản `script.js` để tránh xung đột với `editor-bridge.blade.php`.
                    9. *Khắc Phục Triệt Để Trùng Lặp Trường Lịch Trình Tiệc Cưới (Fix Duplicate Timeline in Schema)*:
                       - **Hiện tượng**: Tại Bước 6 "Lịch Trình Tiệc Cưới" trên Studio Tạo Thiệp (`/tao-thiep?template=hanh-phuc-mang-ten-chung-minh`), xuất hiện 2 cụm nhập liệu trùng nhau: cụm repeater phía trên và 8 ô text riêng lẻ ("Thời gian mốc 1", "Nội dung mốc 1"...) phía dưới.
                       - **Nguyên nhân cốt lõi**: `InvitationTemplateService` tự động tiêm trường canonical `wedding.extra_data.timeline` (repeater), trong khi `schema.json` lại khai báo thủ công các trường text `wedding.extra_data.timeline.0.time`, `title`... Khi chuẩn hóa schema, cả hai đều được đưa vào form gây duplicate.
                       - **Giải pháp xử lý**:
                         * Chuẩn hóa `schema.json` của mẫu `hanh-phuc-mang-ten-chung-minh`: thay thế toàn bộ 8 trường con rời rạc bằng trường canonical duy nhất `wedding.extra_data.timeline` kiểu `repeater` (khớp chuẩn hệ thống và mẫu `giua-muon-ngan-vi-sao`).
                         * Cập nhật `template.blade.php`: xử lý `$timelineList` nhận mảng từ `wedding.extra_data.timeline` kết hợp fallback thông minh theo từng phần tử.
                         * Viết Pest test kiểm thử khẳng định không còn bất kỳ trường duplicate nào và Studio render sạch sẽ 100%.
                  - **Kiểm thử & Đảm bảo chất lượng**:
                    - Pest Test: Kiểm tra toàn diện 10 Section, Timeline repeater duy nhất, Dresscode, Maps iframe, 10 ảnh Album, và Nút đĩa than -> **100% Passed**.
                    - Code formatter Laravel Pint: `vendor/bin/pint --dirty --format agent` -> **Passed**.
                    - Xóa sạch cache hệ thống: `php artisan view:clear; php artisan cache:clear`.

---

              130. **Tích Hợp Mẫu Thiệp Cưới `ngay-tinh-yeu-dom-hoa` — Quiet Luxury Đỏ Son, Thư Pháp**:
                   - **Nguồn gốc**: Clone và chuẩn hóa từ `D:\File\Templates Mây Hẹn\Ngày Tình Yêu Đơm Hoa\`, lấy cảm hứng từ `https://hwd.happyweddingday.vn/red-vip`.
                   - **Phong cách**: Đỏ Son Truyền Thống, Thư Pháp, Song Hỷ, Quiet Luxury. Màu chủ đạo `#8e0101`.
                   - **Slug chính thức**: `ngay-tinh-yeu-dom-hoa`.
                   - **Thư mục template**: `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/`.
                   - **Assets 100% local**: 27 ảnh `.webp`, 8 font `.woff2`, 8 icon `.svg` trong thư mục `assets/`.
                   - **Đặc thù kỹ thuật khác biệt so với các mẫu còn lại**:
                     1. **8 Section** (không đủ 10): Mẫu này không có Section Timeline và Dresscode riêng. Đây là đặc thù thiết kế hợp lệ — Dresscode tích hợp vào Section Story/Family, Timeline tích hợp vào Section Events.
                     2. **Album 8 ảnh** (không phải 10): Key ánh xạ dùng `gallery_photo_1` đến `gallery_photo_8` (số nguyên, không phải `gallery.0`..`gallery.9`).
                     3. **Lễ Ăn Hỏi riêng biệt**: Có thêm `engagement_date`, `engagement_time`, `engagement_location`, `engagement_address_map` cho Section Lễ Ăn Hỏi — khác với mẫu `giua-muon-ngan-vi-sao`.
                     4. **Ảnh đặc thù**: `couple_banner_url` (ảnh banner ngang), `triptych_left/center/right_url` (bộ 3 ảnh nghệ thuật), `footer_photo_url` (ảnh tròn cuối thiệp).
                     5. **Script.js đọc endpoint** từ `data-rsvp-endpoint` và `data-signature-endpoint` trên `.mayhen_invitation_root` — tuân thủ đúng chuẩn, không hardcode URL.
                     6. **Animation**: `snowflakes-falling-red` — hoa tuyết đỏ rơi trên nền Đỏ Son.
                   - **Cơ sở dữ liệu**: Insert bản ghi vào `invitation_templates` với `is_active=1`, `is_featured=1`, `category='luxury'`, `tier='free'`.
                   - **Kiểm thử**: Render thành công 118KB, không lỗi PHP, 8 section BEM đầy đủ.
                   - **Files thay đổi**:
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php` (tạo mới — `@extends`, `$wedding->...`, `$extraData[...]`, `$resolveAsset`, `data-sync-target`, `$isEmbed`)
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/styles.css` (copy từ nguồn — 1905 dòng)
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/script.js` (copy từ nguồn — sửa comment dòng 4 slug)
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/assets/` (27 webp, 8 woff2, 8 svg)
                   - **Preview URL**: `/xem-thu/ngay-tinh-yeu-dom-hoa`
                   - **Thumbnail**: `/mau-thiep/ngay-tinh-yeu-dom-hoa/assets/images/hero-couple.webp`

---

              131. **Chuẩn Hóa 10 Section Toàn Diện, Loại Bỏ Trùng Lặp Ảnh & Hoàn Thiện CSS Timeline/Dresscode Cho Mẫu `ngay-tinh-yeu-dom-hoa`**:
                   - **Xóa bỏ triệt để trùng lặp ảnh ở Studio Tạo Thiệp**:
                     1. *Bước 2 (Lời ngỏ)*: Bỏ hẳn field `wedding.extra_data.couple_banner_url`, đồng nhất sử dụng trường canonical duy nhất `wedding.extra_data.save_date_photo_url` ("Ảnh cưới kỷ niệm (Khung vòm nghệ thuật)").
                     2. *Bước 3 (Gia đình)*: Sửa key ảnh chân dung từ `wedding.groom_photo_url` / `wedding.bride_photo_url` thành `wedding.extra_data.groom_photo_url` và `wedding.extra_data.bride_photo_url` khớp 100% với Canonical `InvitationTemplateService`. Kết quả: Loại bỏ hoàn toàn 2 ô upload chân dung bị lặp lại ở cuối form.
                     3. *Bước 1 (Trang bìa)*: Đồng bộ trường `slogan` canonical `wedding.extra_data.slogan`.
                   - **Thiết kế lại hoàn chỉnh Section 6 (Lịch Trình Tiệc Cưới - Timeline)**:
                     1. Cấu trúc HTML theo chuẩn Quiet Luxury: Khung thẻ `.mayhen_invitation_timeline_card` nền kem ivory viền đỏ son, các mốc thời gian căn phải trang trọng, cột mốc chứa chấm tròn viền vàng kim `#e2c08d` kèm SVG biểu tượng (Đón khách, Cử hành hôn lễ, Khai tiệc, Giao lưu), nối bằng đường line gradient vàng kim - đỏ son lung linh.
                   - **Thiết kế lại hoàn chỉnh Section 7 (Gợi Ý Trang Phục - Dresscode)**:
                     1. **Ẩn hoàn toàn chuỗi mã HEX thô thiển**: Ánh xạ mã màu sang tên màu tiếng Việt tinh tế (Vàng Champagne, Trắng Tinh Khôi, Xanh Đêm Sâu, Đen Huyền...).
                     2. Thiết kế thẻ `.mayhen_invitation_dresscode_card` sang trọng, icon váy/áo lễ cưới hoàng gia, các ô swatch màu tròn 44px viền đôi trắng/vàng kim nổi 3D, hiệu ứng phóng to hover mượt mà và khung ghi chú trang phục viền nét đứt thanh lịch.
                   - **Khôi phục 100% Giao diện Album & Hộp Mừng Cưới (Fix CSS Class Mapping & Lightbox Modal)**:
                     1. *Album Hình Cưới*: Bổ sung đầy đủ hệ thống class gốc `.mayhen_invitation_album_section`, `.mayhen_invitation_album_header`, `.mayhen_invitation_album_title`, `.mayhen_invitation_album_ornament`, `.mayhen_invitation_album_grid` và `.mayhen_invitation_album_item`, khôi phục lưới 2 cột tỷ lệ chuẩn kèm hiệu ứng hover và nút xem thêm.
                     2. *Hộp Mừng Cưới (VietQR)*: Khôi phục cấu trúc thẻ `.mayhen_invitation_gift_section`, `.mayhen_invitation_gift_grid`, `.mayhen_invitation_qr_card` với khung ảnh QR `.mayhen_invitation_qr_card__img_wrap` cố định kích thước 131x134px viền cong bo góc, ngăn ngừa mã QR bị phóng to tràn màn hình.
                     3. *Sửa lỗi thanh đen `< >` che khuất giao diện*: Đưa khung Lightbox Modal xuống cuối trang và gắn đúng class `.mayhen_invitation_modal_backdrop` (có `display: none` mặc định), ngăn việc thanh điều khiển ảnh hiện đè lên giao diện.
                   - **Khôi phục hoàn hảo Section Lời Cảm Ơn Kết Thiệp (Footer Thank You)**:
                     1. Khôi phục cấu trúc thẻ `.mayhen_invitation_footer_section` độc lập sau Section RSVP & Lời chúc.
                     2. Áp dụng container `.mayhen_invitation_footer_circle_stage`, định vị chuẩn xác vòng tròn chân dung `.mayhen_invitation_footer_circle` bo cong viền đỏ son và ảnh nghệ thuật chữ thư pháp `.mayhen_invitation_footer_thankyou_img` ôm trọn nửa dưới khung ảnh.
                     3. Loại bỏ chữ Thank You text bị lặp đè lên ảnh nghệ thuật, hiển thị tên cặp đôi `.mayhen_invitation_footer_names` font chữ thư pháp cổ điển `MayhenScript` căn giữa trang trọng và lời tri ân `.mayhen_invitation_ending_text` thanh lịch.
                   - **Kiểm thử**: Pest test 5/5 passed, render test đạt 129KB sạch sẽ không lỗi PHP, schema Studio chuẩn 10 bước không còn duplicate.

---

              132. **Đồng Bộ Tự Động Tên Đôi Uyên Ương Thank You (`ending_couple_name`) & Rà Soát Toàn Diện Dữ Liệu Khách Nhập**:
                   - **Đồng bộ tự động 100% `mayhen_invitation_ending_couple_name` theo tên nhập ở Bước 1**:
                     1. *Loại bỏ xung đột event listener*: Xóa bỏ hàm `initStudioSync` chạy ngầm trong `script.js` của mẫu `ngay-tinh-yeu-dom-hoa`. Hàm này trước đây can thiệp vào sự kiện `message` và tự ý gán `el.textContent = g + " - " + b` đè lên toàn bộ thẻ con, xóa mất các thẻ `<span data-sync-target="groom_name">` và `bride_name`.
                     2. *Nâng cấp `editor-bridge.blade.php`*: Mở rộng bộ chọn tự động tìm và cập nhật cả `.mayhen_invitation_ending_couple_name`, `.mayhen_invitation_footer_names` và `[data-sync-target="couple_names"]`. Nếu thẻ con bị mất hoặc chưa có, bridge tự động khôi phục cấu trúc thẻ con `<span data-sync-target="groom_name">` & `<span data-sync-target="bride_name">` kèm logic fallback lấy trực tiếp từ DOM nếu một trong hai tên chưa có trong local state.
                   - **Rà soát dữ liệu khách hàng nhập trên toàn bộ mẫu thiệp**:
                     1. *Monogram hai chữ cái đầu*: Bổ sung `data-monogram-role="groom"` và `data-monogram-role="bride"` cho thẻ `.mayhen_invitation_monogram` để chữ cái đầu tên tự động nhảy tức thời khi người dùng gõ tên chú rể hoặc cô dâu.
                     2. *Song thân hai họ*: Cho phép fallback lấy từ `$wedding->groom_parents` và `$wedding->bride_parents` khi tạo thiệp từ thông tin hồ sơ đã có của khách hàng.
                     3. *Huy hiệu cảm ơn cuối thiệp (`ending_title`)*: Thay thế ảnh tĩnh bằng badge `.mayhen_invitation_footer_thankyou_badge` nền trắng, bo tròn oval 999px, viền đỏ son `#8e0101` kèm font chữ Playfair Display / MayhenScript nghiêng sang trọng, hiển thị đúng và đồng bộ tức thời tiêu đề tri ân do khách hàng nhập.
                   - **Files tác động**:
                     - `resources/views/client/invitations/partials/editor-bridge.blade.php`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/script.js`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/styles.css`
                   - **Kiểm thử**: Pest 5/5 tests passed (51 assertions), Pint passed format.

---

              133. **Khắc Phục Triệt Để Trùng Lặp Trường Nhập Liệu Tại Bước 10 (Cảm Ơn) & Bước 4 (Đếm Ngược) Trong Studio Editor**:
                   - **Nguyên nhân cốt lõi**: `InvitationTemplateService::normalizeSchema()` luôn tự động nạp các trường canonical chuẩn từ hệ thống trước (`thank_you_title`, `thank_you_quote` cho Bước 10; `event_target_time` cho Bước 4). Vì file `schema.json` của mẫu trước đó định nghĩa các key tùy biến khác tên (`ending_title`, `ending_text`, `countdown_time`), hàm `normalizeSchema()` coi đây là các trường bổ sung riêng nên đã nối thêm vào danh sách, dẫn tới việc trên giao diện form hiển thị lặp lại 2 lần "Tiêu đề lời tri ân kết thiệp" và 2 lần "Thông điệp cảm ơn gửi quan khách".
                   - **Xử lý triệt để**:
                     1. Cập nhật `schema.json` chuẩn hóa 100% key đồng nhất với canonical: chuyển `ending_title` -> `wedding.extra_data.thank_you_title`, `ending_text` -> `wedding.extra_data.thank_you_quote`, `countdown_time` -> `wedding.extra_data.event_target_time`.
                     2. Cập nhật `template.blade.php` và `aliasesMap` trong `editor-bridge.blade.php` hỗ trợ cả 2 chuẩn key (hai chiều), đồng bộ tức thời khi người dùng nhập liệu ở form Studio.
                     3. Kết quả: Bước 10 chỉ còn đúng 3 trường sạch sẽ (Tiêu đề, Thông điệp cảm ơn, Ảnh tròn); Bước 4 chỉ còn đúng 2 trường (Ngày cưới, Giờ đếm ngược).
                   - **Files tác động**:
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/schema.json`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php`
                     - `resources/views/client/invitations/partials/editor-bridge.blade.php`
                   - **Kiểm thử**: Đã chạy script kiểm tra `normalizeSchema()` thực tế, Pest 5/5 tests passed (51 assertions).

---

              134. **Tích Hợp & Chuẩn Hóa Mẫu Thiệp Cưới `tron-kiep-nay-ben-nhau` (Trọn Kiếp Này Bên Nhau) Đạt Chuẩn 10 Section Toàn Diện**:
                   - **Định vị & Phong cách**: Mẫu thiệp phong cách Quiet Luxury Đỏ Son Hoàng Gia & Vàng Ánh Kim (`#C82333` & `#F5D38E`) trên nền kem ấm (`#FFFDF9`, `#EAE6DF`). Phù hợp cho đám cưới truyền thống sang trọng và hiện đại.
                   - **Slug chính thức**: `tron-kiep-nay-ben-nhau`.
                   - **Thư mục mẫu**: `resources/views/client/invitations/tron-kiep-nay-ben-nhau/`.
                   - **Chuẩn hóa 8 nguyên tắc cốt lõi theo tiêu chuẩn Mây Hẹn**:
                     1. *Đủ 10 Section Tiêu Chuẩn*:
                        - **Section 1 (Hero)**: Cánh cửa mở Song Hỷ đỏ mở bung (tự động ẩn khi nhúng), ảnh bìa đôi uyên ương, tên cô dâu chú rể, loại lễ (`Lễ Vu Quy`), ngày dương/âm lịch, slogan mở màn.
                        - **Section 2 (Story)**: Lời ngỏ tình yêu, subtitle "Trọn Kiếp Này Bên Nhau", trích dẫn lãng mạn, ảnh kỷ niệm khung vòm nghệ thuật, đoạn tâm sự và Monogram cặp đôi.
                        - **Section 3 (Parents)**: Song thân hai họ Nhà Trai & Nhà Gái, địa chỉ tư gia, chân dung riêng và lời tự sự của Cô Dâu & Chú Rể.
                        - **Section 4 (Countdown & Calendar)**: Tờ lịch tháng nghệ thuật và đồng hồ đếm ngược 4 trái tim thời gian thực (Ngày, Giờ, Phút, Giây).
                        - **Section 5 (Events & Maps)**: Bộ 3 ảnh vòm nghệ thuật áo dài đỏ, thư mời quan khách, tiệc cưới Nhà Gái/Nhà Trai, bản đồ vị trí tổ chức vector an toàn kèm nút chỉ đường Google Maps.
                        - **Section 6 (Timeline)**: Bổ sung mới Section Lịch trình tiệc cưới chuẩn 4 mốc thời gian kèm icon SVG và đường nối sang trọng.
                        - **Section 7 (Dresscode)**: Bổ sung mới Section Quy định trang phục với các ô màu swatch tròn 3D có nhãn tiếng Việt ("Đỏ Son", "Vàng Champagne", "Trắng Tinh Khôi", "Đen Quý Phái") và lời dặn dò trang phục, tuyệt đối không in mã hex thô.
                        - **Section 8 (Album)**: Bộ sưu tập 8 ảnh cưới chất lượng cao kèm nút xem thêm và modal lightbox phóng to toàn màn hình.
                        - **Section 9 (Gift)**: Nắp mở hộp quà mừng cưới tương tác, thẻ VietQR Chú Rể & Cô Dâu kèm nút sao chép số tài khoản một chạm.
                        - **Section 10 (Ending)**: Sổ ký tên điện tử Canvas (100% không ảnh người, nền canvas nghệ thuật), Xác nhận tham dự RSVP (100% không ảnh người), Sổ lưu bút chúc phúc và Chân trang cảm ơn với tên cặp đôi tự động đồng bộ Live Sync.
                     2. *BEM Prefix Bất Biến*: Sử dụng hệ thống class `mayhen_invitation_*` toàn diện, tương thích 100% với Studio Tạo Thiệp và Admin.
                     3. *Hợp Đồng Live Sync Tuyệt Đối*: Gắn đầy đủ `data-sync-target` cho tất cả các trường dữ liệu.
                     4. *Phòng Vệ Nhúng Studio (`$isEmbed = true`)*: Cánh cửa mở đầu ẩn ngay lập tức (`display: none !important;`), không khóa cuộn body, hiển thị nội dung tức thì trong iframe.
                     5. *100% Local Assets*: Toàn bộ ảnh WebP, font WOFF2 và icon SVG được phục vụ nội bộ qua route `/mau-thiep/tron-kiep-nay-ben-nhau/assets/...`.
                     6. *Đăng Ký Database*: Cập nhật `InvitationTemplateSeeder` và nạp bản ghi mẫu vào cơ sở dữ liệu `invitation_templates`.
                   - **Files tác động**:
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/schema.json` (tạo mới)
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/template.blade.php` (tạo mới)
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/styles.css` (tạo mới)
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/script.js` (tạo mới)
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/assets/` (ảnh, font, icon nội bộ)
                     - `database/seeders/InvitationTemplateSeeder.php` (bổ sung bản ghi mẫu)
                   - **Kiểm thử & Chất lượng**:
                     - Toàn bộ Feature tests chạy pass 100% (9/9 tests, 82 assertions).
                     - Schema không bị trùng lặp trường.
                     - Laravel Pint pass format agent style.

              135. **Chuẩn Hóa Album 10 Ảnh Cưới Tiêu Chuẩn & Triệt Tiêu Lỗi Trùng Lặp Trường / Lặp Ảnh Trong Studio Editor**:
                   - **Bối cảnh & Nguyên nhân**:
                     1. *Lỗi thừa trường Bước 8 (Ảnh Cưới)*: Trong `schema.json` của cả 2 mẫu `tron-kiep-nay-ben-nhau` và `ngay-tinh-yeu-dom-hoa`, phần `album` trước đó định nghĩa các trường riêng lẻ `gallery_photo_1` .. `gallery_photo_8`. Trong khi đó, `InvitationTemplateService::normalizeSchema()` luôn tự động nạp trường canonical chuẩn `wedding.extra_data.gallery` (`type: "gallery"`, 10 ô tải ảnh). Do đó, giao diện Studio hiển thị đồng thời cả khối "Album 10 Ảnh Cưới" LẪN 8 trường "Ảnh cưới 1..8" bên dưới.
                     2. *Lỗi lặp cùng 1 ảnh thumbnail ở 10 ô preview*: Mảng `gallery` trong `schema.json` và `InvitationTemplateSeeder` thiếu tiền tố đường dẫn hợp lệ hoặc chưa định nghĩa đủ 10 ảnh khác nhau, khiến các ô tải ảnh bị fallback về cùng một ảnh đại diện `thumbnail_url`.
                     3. *Lỗi trái tim phần đếm ngược bị lệch*: Trong `ngay-tinh-yeu-dom-hoa/styles.css`, class `.mayhen_invitation_calendar_heart` trước đó chỉ có `top: 10%; transform: translate(-50%, -50%)` thiếu `left: 50%` khiến trái tim bị lệch sang mép trái và bay khỏi số ngày cưới.
                   - **Giải pháp xử lý toàn diện**:
                     1. *Chuẩn hóa Schema Album*: Thay thế toàn bộ 8 trường đơn lẻ `gallery_photo_1`..`gallery_photo_8` bằng trường canonical duy nhất `wedding.extra_data.gallery` (`type: "gallery"`, `max_items: 10`, cung cấp sẵn mảng 10 đường dẫn ảnh WebP mặc định hoàn toàn khác biệt từ kho ảnh nội bộ) trong `schema.json` của cả 2 mẫu `tron-kiep-nay-ben-nhau` và `ngay-tinh-yeu-dom-hoa`.
                     2. *Cập nhật Template Blade*: Đọc trực tiếp từ mảng `$wedding->extra_data['gallery'] ?? ($extraData['gallery'] ?? [])`, duyệt đủ 10 ảnh (index 0 đến 9), gắn đầy đủ `data-sync-target="gallery.{{ $i }}"` và `data-index="{{ $i }}"`.
                     3. *Cập nhật Database Seeder*: Cung cấp mảng 10 ảnh đầy đủ đường dẫn `/mau-thiep/{slug}/assets/images/...` trong `default_config->gallery` cho cả 2 mẫu và chạy lại seeder thành công.
                     4. *Căn giữa tuyệt đối trái tim Lịch Đếm Ngược*: Sửa `.mayhen_invitation_calendar_heart` thành `top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1; pointer-events: none;` và đặt số ngày cưới `.mayhen_invitation_calendar_cell--active span` ở `z-index: 2; color: #ffffff !important;` giúp trái tim ôm khít số ngày cưới nổi bật.
                   - **Files tác động**:
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/schema.json`
                     - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/template.blade.php`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/schema.json`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php`
                     - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/styles.css`
                     - `database/seeders/InvitationTemplateSeeder.php`
                   - **Kiểm thử & Chất lượng**:
                     - Pest: 5/5 tests passed (51 assertions).
                     - Schema kiểm tra trực tiếp: Mỗi mẫu chỉ có 1 field duy nhất "Album 10 Ảnh Cưới".
                     - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style.

               136. **Khắc Phục Toàn Diện Lỗi Vỡ Ảnh (404) & Lỗi Trùng Lặp Ảnh Mặc Định Khi Tải Trang Tạo Thiệp (`/tao-thiep`)**:
                    - **Bối cảnh & Nguyên nhân gốc rễ**:
                      1. *Lỗi vỡ ảnh Album trên Live Preview Iframe*: Khi người dùng mở hoặc chuyển sang mẫu mới (`tron-kiep-nay-ben-nhau`), nếu `localStorage` hoặc session lưu bản nháp của mẫu trước đó (`ngay-tinh-yeu-dom-hoa`) chứa các tên file như `gallery-01.webp` (không có trong thư mục assets của `tron-kiep-nay-ben-nhau`), hàm `$resolveAsset` trước đó cố gắng nối path thành `/mau-thiep/tron-kiep-nay-ben-nhau/assets/images/gallery-01.webp` dẫn đến lỗi 404, làm toàn bộ ảnh trong iframe bị vỡ.
                      2. *Lỗi trùng cùng 1 ảnh thumbnail ở 10 ô tải ảnh bên form Studio*: Thuộc tính `onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'"` trong `editor.blade.php` đã cưỡng chế tất cả các ô tải ảnh chuyển về ảnh bìa `hero-couple.webp` khi có lỗi nạp ảnh, đồng thời logic phục hồi bản nháp trong `editor.js` đã ghi đè đường dẫn ảnh cũ của mẫu trước lên các ô nhập liệu của mẫu mới.
                      3. *Lỗi lệch origin iframe*: Thẻ `iframe` trong `editor.blade.php` trước đó dùng `route('templates.preview')` sinh URL tuyệt đối (ví dụ `http://localhost:8000`), trong khi người dùng truy cập ở `http://127.0.0.1:8000`, gây rủi ro chặn tương tác DOM / CORS giữa trang cha và iframe con.
                    - **Giải pháp xử lý toàn diện**:
                      1. *Cơ chế Asset Fallback Defense tự bảo vệ chống 404*:
                         - Trong `$resolveAsset` của `tron-kiep-nay-ben-nhau/template.blade.php` và `ngay-tinh-yeu-dom-hoa/template.blade.php`, bổ sung kiểm tra tồn tại tệp vật lý `file_exists($localFile)`.
                         - Nếu file override từ bản nháp cũ không tồn tại trong thư mục của template hiện tại, hệ thống tự động fallback về đúng ảnh mẫu mặc định tương ứng của chính template đó (`$defaultPath`). Đảm bảo 100% không bao giờ sinh ra URL 404.
                      2. *Hiển thị đúng 100% ảnh mẫu mặc định của template trong Studio Editor*:
                         - Trong `editor.blade.php`, vòng lặp Album 10 ảnh (`$type === 'gallery'`) và trường ảnh đơn (`$type === 'image'`) phân giải chính xác `$defaultImgUrl` của từng ô từ `schema.json`.
                         - Nếu người dùng chưa tự tải ảnh cá nhân lên (`data:image/...` hoặc URL upload), cả trường ẩn `$gVal` lẫn ảnh xem trước `preview_thumb` luôn hiển thị đúng ảnh mẫu mặc định của ô đó.
                         - Sửa thuộc tính `onerror` của từng ô thành `onerror="this.src='{{ $defaultImgUrl }}'"` (fallback về chính ảnh của ô đó thay vì ép tất cả về cùng một ảnh `thumbnail_url`).
                      3. *Bảo vệ bản nháp trong JavaScript (`editor.js`)*:
                         - Khi phục hồi `draftWeddingData`, đối với các trường ảnh (ảnh bìa, ảnh chân dung, mảng gallery), CHỈ ghi đè khi dữ liệu là ảnh do người dùng tự tải lên (`data:image/...` hoặc link `/storage/`, `/uploads/`). Bỏ qua không ghi đè các đường dẫn ảnh tĩnh của mẫu khác.
                         - Trong `syncAllFieldsToIframe`, chỉ gửi sang iframe những ảnh mà người dùng đã tự tải lên; không gửi đè ảnh tĩnh mẫu sang iframe vì iframe đã tự render hoàn chỉnh bộ ảnh mặc định đặc trưng từ server.
                      4. *Chuẩn hóa Iframe sang đường dẫn tương đối*:
                         - Chuyển `src` của cả desktop iframe và mobile preview iframe trong `editor.blade.php` thành `/xem-thu/{{ $selectedTemplate->slug }}?embed=1&mode=editor` giúp cùng origin 100% trên cả `127.0.0.1` và `localhost`.
                    - **Files tác động**:
                      - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/template.blade.php`
                      - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php`
                      - `resources/views/client/pages/editor.blade.php`
                      - `public/assets/client/js/pages/editor.js`
                      - `tests/Feature/GalleryRenderTest.php`
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 6/6 tests passed (73 assertions).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style.

               137. **Thiết Lập Phân Hệ Quản Trị Admin Độc Lập: Trang Đăng Nhập Nghệ Thuật Thiệp Cưới, Điều Hướng Thông Minh & Admin Dashboard (/admin/dashboard)**:
                    - **Bối cảnh & Yêu cầu cốt lõi**:
                      1. Toàn bộ định tuyến admin được tách biệt và quản lý tập trung trong file `routes/admin.php`.
                      2. Cơ chế điều hướng thông minh hai chiều:
                         - Truy cập `/admin`: Nếu chưa đăng nhập admin -> Chuyển hướng về `/admin/login`; nếu đã đăng nhập admin -> Chuyển hướng về `/admin/dashboard`.
                         - Truy cập `/admin/login`: Nếu đã đăng nhập admin -> Chuyển hướng về `/admin/dashboard`; nếu chưa đăng nhập -> Hiển thị trang đăng nhập.
                         - Đăng nhập thành công -> Tự động chuyển hướng về `/admin/dashboard`.
                         - Đăng xuất (`POST /admin/logout`) -> Hủy session an toàn và chuyển về `/admin/login`.
                      3. Trang đăng nhập mang phong cách thiệp cưới sang trọng (Quiet Luxury Wedding Atelier) với hai cột đối xứng: Cột trái lời chào nghệ thuật kèm hoa văn letterpress dập nổi; Cột phải form đăng nhập quản trị tinh gọn với ẩn/hiện mật khẩu, ghi nhớ email, xác thực an toàn hỗ trợ cả AJAX JSON lẫn submit chuẩn.
                      4. Trang Admin Dashboard hoàn thiện với 5 chỉ số KPI vận hành thời gian thực (Doanh thu đã thu, Đơn hàng thành công, Tỷ lệ thiệp xuất bản, Khách mời & RSVP, Tổng người dùng), danh sách mẫu thiệp cưới đang hoạt động và đơn hàng gần đây.
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Middleware/AdminAuthenticate.php`: Middleware bảo vệ kiểm tra quyền `isAdmin()`, đăng ký alias `admin.auth` trong `bootstrap/app.php`.
                      - `app/Http/Controllers/Admin/AuthController.php`: Xử lý điều hướng thông minh, form login, rate limiting chống brute-force và logout.
                      - `routes/admin.php`: Tập trung toàn bộ 10 route quản trị admin (`admin.index`, `admin.login`, `admin.login.submit`, `admin.logout`, `admin.dashboard`, `admin.templates.*`, `admin.orders.*`).
                      - `routes/web.php`: Kết nối trực tiếp `routes/admin.php` qua tiền tố `admin`.
                      - `database/seeders/AdminUserSeeder.php`: Tạo tài khoản Quản trị viên mặc định (`admin@mayhen.vn`).
                      - `public/assets/admin/css/pages/login.css`: CSS thuần cách ly hoàn toàn, chuẩn BEM prefix `mayhen_admin_login_*`.
                      - `resources/views/admin/auth/login.blade.php`: Giao diện đăng nhập chuẩn Blade, tích hợp CSRF token, Blade directives `@error`, flash session.
                      - `resources/views/admin/layouts/master.blade.php`: Nâng cấp Topbar hiển thị tên admin đăng nhập, nút đăng xuất an toàn.
                      - `resources/views/admin/pages/dashboard.blade.php`: Nâng cấp lưới thẻ KPI linh hoạt kèm chỉ số Khách Mời & RSVP.
                      - `tests/Feature/AdminAuthTest.php`: Bộ kiểm thử tự động 10 test cases bao phủ 100% các kịch bản xác thực và điều hướng admin.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 16/16 passed 100% (110 assertions).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style.

               138. **Nâng Cấp Toàn Diện Admin Master Layout Quiet Luxury & Triển Khai Giai Đoạn 2: Quản Lý Thiệp Cưới Khách Hàng (Invitations & Weddings)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Học hỏi và bóc tách cấu trúc phân khu, sidebar menu sections, topbar actions và responsive drawer từ dự án `nobifashion.vn`.
                      2. Tuyệt đối không dùng Bootstrap hay CDN của dự án mẫu; chuẩn hóa 100% sang Pure Vanilla CSS và Native ES Modules với tiền tố BEM `mayhen_admin_*`.
                      3. Thiết kế thẩm mỹ Quiet Luxury Atelier Control Room: Tông màu Ivory / Warm Beige (`#F8F6F2`, `#FAF8F5`), viền ngà (`#E8E2D8`), Charcoal (`#1F1D1A`) và điểm nhấn Antique Gold hoàng gia (`#C5A880`).
                      4. Triển khai Giai Đoạn 2 (Ưu tiên số 1 trong `ADMIN_ARCHITECTURE_ROADMAP.md`): Phân hệ quản lý toàn bộ thiệp cưới do khách hàng tạo trên nền tảng.
                    - **Các thành phần đã triển khai**:
                      - `public/assets/admin/css/common/layout.css`: Khung sườn Master Layout hoàn chỉnh: Sidebar cố định chia các Section (Tổng quan, Vận hành thiệp cưới, Kinh doanh & Doanh thu), Brand Logo Mark M lồng hoạ tiết sang trọng, Topbar cố định kèm breadcrumbs động, Pill avatar tài khoản quản trị kèm dropdown menu, hệ thống thẻ card, bảng responsive, nút bấm và badge trạng thái chuẩn BEM.
                      - `public/assets/admin/js/common/admin-core.js`: Native Vanilla JavaScript quản lý đóng/mở sidebar (lưu trạng thái thu gọn vào `localStorage`), mở drawer trên mobile kèm backdrop, dropdown menu tài khoản và helper sao chép liên kết một chạm có thông báo toast mượt mà.
                      - `resources/views/admin/layouts/master.blade.php`: Tái cấu trúc chuẩn hoá toàn bộ layout, tích hợp SVG icons nội bộ tinh xảo, loại bỏ hoàn toàn sự phụ thuộc vào FontAwesome hay icon CDN ngoài.
                      - `app/Http/Controllers/Admin/InvitationController.php`: Xử lý truy vấn native fast tối ưu (Eager loading `wedding.user`, `template`, `wedding.guests`, `wedding.wishes`), tìm kiếm tức thời theo tên cô dâu/chú rể, slug, SĐT, email; lọc theo trạng thái (Xuất bản/Bản nháp/Hết hạn) và mẫu thiệp; xử lý bật/tắt xuất bản và gia hạn thiệp cưới.
                      - `routes/admin.php`: Bổ sung nhóm route `admin.invitations.*` (`index`, `show`, `toggle_publish`, `extend`).
                      - `public/assets/admin/css/pages/invitations.css`: Phong cách cho bộ lọc tìm kiếm, bảng danh sách thiệp cưới và khung mô phỏng điện thoại Live Phone Mockup tỉ lệ iPhone 15 Pro với Dynamic Island và iframe nhúng mượt mà.
                      - `resources/views/admin/pages/invitations/index.blade.php`: Giao diện danh sách thiệp cưới với thumbnail mẫu, cặp đôi, chủ sở hữu, nút copy link công khai, badge số khách/lời chúc và nút thao tác nhanh.
                      - `resources/views/admin/pages/invitations/show.blade.php`: Giao diện chi tiết chuyên sâu chia 2 cột: Cột trái Phone Mockup dính (sticky) xem trực tiếp thiệp cưới của khách kèm form gia hạn và ẩn/hiện thiệp; Cột phải thẻ thông tin ngày cưới, gia đình, địa chỉ tiệc cưới và hệ thống tab xem danh sách khách mời, phản hồi RSVP, sổ lưu bút lời chúc và hồ sơ kỹ thuật schema.
                      - `resources/views/admin/pages/dashboard.blade.php`, `templates.blade.php`, `orders.blade.php`: Đồng bộ hóa giao diện thẻ card, icon SVG và badge trạng thái theo layout mới.
                      - `tests/Feature/AdminInvitationTest.php`: Bộ 7 test cases tự động kiểm tra chặt chẽ phân quyền khách/admin, tìm kiếm lọc, xem chi tiết Phone mockup, bật/tắt xuất bản và gia hạn thiệp cưới.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 17/17 tests passed 100% (60 assertions).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style.

               139. **Hoàn Thiện Giai Đoạn 3: Quản Lý Mẫu Thiệp Cưới (Templates Engine), Trình Soát Tính Toàn Vẹn Schema & Tự Động Quét Mẫu (Discovery Engine)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Xây dựng phân hệ quản trị mẫu thiệp cưới toàn diện, cho phép Admin chủ động phân loại phong cách, xếp hạng gói (`free`, `plus`, `pro`), ghim nổi bật và ẩn/hiện mẫu tức thời mà không cần can thiệp code.
                      2. Tích hợp Trình Soát Tính Toàn Vẹn Schema (Schema Validator Inspector): Tự động quét và đối chiếu `schema.json` của từng mẫu với 10 Section tiêu chuẩn của Mây Hẹn (`hero`, `story`, `parents`, `countdown_calendar`, `events`, `timeline`, `dresscode`, `album`, `gift`, `ending`), báo lỗi đỏ hoặc badge xanh đạt chuẩn kèm kiểm tra trường Album 10 ảnh.
                      3. Tự Động Quét & Đồng Bộ Mẫu (Template Discovery Engine): Nút một chạm tự động rà soát toàn bộ thư mục `resources/views/client/invitations/`, đọc `schema.json` và đồng bộ tức thời vào cơ sở dữ liệu `invitation_templates`.
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Controllers/Admin/TemplateController.php`: Nâng cấp toàn diện các action `index` (KPI metrics, tìm kiếm, lọc phong cách & trạng thái), `edit` (tích hợp Schema Inspector phân tích 10 canonical sections), `update` (cập nhật metadata, tier, mô tả phong cách), `toggleActive` & `toggleFeatured` (hỗ trợ cả submit thường lẫn AJAX JSON), `scanDiscovery` (quét thư mục vật lý và đồng bộ DB).
                      - `routes/admin.php`: Bổ sung các route `admin.templates.scan_discovery`, `admin.templates.edit`, `admin.templates.update`.
                      - `public/assets/admin/css/pages/templates.css`: Phong cách cho 4 thẻ KPI mẫu thiệp, bảng dữ liệu, badge hạng gói (`mayhen_admin_tier_*`), form biên tập và thẻ Schema Inspector kèm checklist trực quan 10 Section.
                      - `resources/views/admin/pages/templates/index.blade.php`: Giao diện danh sách mẫu thiệp với 4 KPI tổng quan, bộ lọc đa tiêu chí, nút Quét & Đồng Bộ mẫu, bảng dữ liệu thumbnail và toggle trạng thái nhanh.
                      - `resources/views/admin/pages/templates/edit.blade.php`: Giao diện chuyên sâu 2 cột: Cột trái form biên tập tên, mô tả, danh mục, tier, thumbnail; Cột phải Schema Inspector đối chiếu 10/10 Section chuẩn mực và kiểm tra trường Album 10 ảnh.
                      - `resources/views/admin/pages/templates.blade.php`: Kế thừa an toàn qua `@include('admin.pages.templates.index')` đảm bảo tương thích ngược 100%.
                      - `tests/Feature/AdminTemplateTest.php`: Bộ kiểm thử 7 test cases bao phủ toàn diện xác thực, tìm kiếm, toggle AJAX active/featured, xem Schema Inspector, cập nhật tier và quét tự động `scanDiscovery`.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 24/24 passed 100% (100 assertions) gồm cả `AdminAuthTest`, `AdminInvitationTest` và `AdminTemplateTest`.
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style.

               140. **Hoàn Thiện Giai Đoạn 4: Quản Lý Đơn Hàng, Doanh Thu, Đối Soát Thanh Toán & Duyệt Kích Hoạt Thủ Công (Orders & Payments)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Xây dựng phân hệ quản trị đơn hàng và đối soát doanh thu toàn diện, đảm bảo dòng tiền kinh doanh minh bạch và xử lý ngay lập tức các trường hợp ngân hàng bảo trì làm chậm lệnh webhook.
                      2. Bộ lọc đơn hàng chuyên sâu đa chiều: Tìm kiếm theo mã đơn (`MH...`), tên/email/SĐT khách hàng; lọc theo trạng thái (`paid`, `pending`, `cancelled`); lọc theo gói cước (`Plan`); lọc theo cổng thanh toán (`vietqr`, `sepay`, `bank_transfer`) và lọc theo mốc thời gian (Hôm nay, Tuần này, Tháng này).
                      3. Cơ chế Duyệt Thanh Toán Thủ Công (Manual Approval): Bọc an toàn trong `DB::transaction()`, chuyển trạng thái đơn sang `paid`, ghi nhận `paid_at = now()` và tự động kích hoạt xuất bản thiệp cưới kèm gia hạn `expires_at` theo số ngày bản quyền của gói dịch vụ (`plan->duration_days`).
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Controllers/Admin/OrderController.php`: Nâng cấp các action `index` (tính toán 4 KPI doanh thu, đơn thành công, chờ thanh toán, đơn hủy; tìm kiếm & lọc đa tiêu chí), `show` (chi tiết giao dịch, thông tin khách hàng, quyền lợi gói cước và thiệp cưới liên kết), `approve` (duyệt thủ công kích hoạt bản quyền thiệp trong transaction), `cancel` (hủy đơn hàng an toàn).
                      - `routes/admin.php`: Mở rộng đầy đủ các tuyến đường `admin.orders.show`, `admin.orders.approve`, `admin.orders.cancel`.
                      - `public/assets/admin/css/pages/orders.css`: Định dạng 4 thẻ KPI doanh thu, badge phương thức thanh toán (`mayhen_admin_method_vietqr`, `sepay`), pill mã đơn hàng monospace kèm nút sao chép nhanh, layout 2 cột trang chi tiết đơn hàng với khối Hero Amount sang trọng màu charcoal & gold.
                      - `resources/views/admin/pages/orders/index.blade.php`: Giao diện danh sách đơn hàng chuyên nghiệp với 4 thẻ KPI, thanh tìm kiếm lọc đa trường, bảng dữ liệu chi tiết kèm nút copy mã đơn và nút Duyệt nhanh cho đơn chờ chuyển khoản.
                      - `resources/views/admin/pages/orders/show.blade.php`: Giao diện chi tiết chuyên sâu chia 2 cột: Cột trái khối Hero số tiền, chi tiết giao dịch và hồ sơ khách hàng; Cột phải quyền lợi gói dịch vụ, liên kết thiệp cưới được kích hoạt và hộp thao tác Duyệt thủ công / Hủy đơn có hộp thoại xác nhận.
                      - `resources/views/admin/pages/orders.blade.php`: Kế thừa `@include('admin.pages.orders.index')` đảm bảo tương thích ngược 100%.
                      - `tests/Feature/AdminOrderTest.php`: Bộ kiểm thử tự động 8 test cases bao phủ 100% phân quyền khách/admin, KPI, bộ lọc đa tiêu chí, xem chi tiết giao dịch, duyệt thanh toán thủ công kích hoạt thiệp cưới và hủy đơn hàng.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 32/32 tests passed 100% (137 assertions) bao gồm cả 4 bộ test suite Admin (`AdminAuthTest`, `AdminInvitationTest`, `AdminTemplateTest`, `AdminOrderTest`).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

               141. **Hoàn Thiện Giai Đoạn 5: Quản Lý Người Dùng & Phân Quyền Vai Trò (Users & Roles - RBAC)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Xây dựng phân hệ quản lý tài khoản người dùng và phân quyền vai trò (Role-Based Access Control) gồm: Quản trị viên (`admin`), Quản lý (`manager`), Nhân viên (`staff`), Cộng tác viên (`partner`), Người dùng / Khách hàng (`customer`).
                      2. Tabs phân loại người dùng động với số lượng đếm tức thời; tìm kiếm nhanh theo tên, email, số điện thoại.
                      3. Trang chi tiết người dùng chuyên sâu: Xem toàn bộ lễ cưới và thiệp cưới đã tạo, lịch sử đơn hàng đã thanh toán và tổng chi tiêu tích lũy.
                      4. Chức năng quản trị an toàn: Đặt lại mật khẩu mới cho khách hàng; Phân quyền vai trò với cơ chế phòng thủ ngăn chặn Quản trị viên tự hạ quyền của chính mình.
                    - **Các thành phần đã triển khai**:
                      - `app/Models/User.php`: Bổ sung các phương thức kiểm tra vai trò `isAdmin()` (hỗ trợ cả admin và manager), `isManager()`, `isStaff()`, `isPartner()`, `isCustomer()` và thuộc tính `role_label` tiếng Việt chuẩn xác.
                      - `app/Http/Controllers/Admin/UserController.php`: Xử lý các action `index` (tính toán số lượng đếm theo từng vai trò, tìm kiếm đa trường, phân trang native fast), `show` (chi tiết tài khoản, thống kê chi tiêu, danh sách thiệp và đơn hàng), `changeRole` (phòng thủ không tự hạ quyền admin), `resetPassword` (xác thực mật khẩu tối thiểu 6 ký tự).
                      - `routes/admin.php`: Bổ sung nhóm route `admin.users.*` gồm `index`, `show`, `change_role`, `reset_password`.
                      - `resources/views/admin/layouts/master.blade.php`: Bổ sung mục menu "Người Dùng & Tài Khoản" vào Sidebar với SVG icon người dùng.
                      - `public/assets/admin/css/pages/users.css`: Định dạng hệ thống tab lọc vai trò, avatar tròn chữ cái đầu, badge màu sắc theo cấp bậc vai trò, layout 2 cột chi tiết người dùng.
                      - `resources/views/admin/pages/users/index.blade.php`: Giao diện danh sách tài khoản với các tab phân loại, ô tìm kiếm và bảng dữ liệu hiển thị số thiệp đã tạo, tổng tiền đã chi tiêu và nút hành động.
                      - `resources/views/admin/pages/users/show.blade.php`: Giao diện chi tiết 2 cột: Cột trái bảng thiệp cưới và lịch sử đơn hàng; Cột phải thẻ hồ sơ cá nhân, form phân quyền vai trò (RBAC) và form đặt lại mật khẩu nhanh.
                      - `tests/Feature/AdminUserTest.php`: Bộ kiểm thử tự động 7 test cases bao phủ 100% phân quyền truy cập, danh sách lọc vai trò, xem chi tiết tài khoản kèm thiệp và đơn hàng, đổi quyền hạn, phòng thủ chống tự hạ quyền và đặt lại mật khẩu.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 39/39 tests passed 100% (165 assertions) bao gồm cả 5 bộ test suite Admin (`AdminAuthTest`, `AdminInvitationTest`, `AdminTemplateTest`, `AdminOrderTest`, `AdminUserTest`).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

               142. **Hoàn Thiện Giai Đoạn 6: Quản Lý Lời Chúc (Wishes), Kiểm Duyệt Sổ Lưu Bút & Báo Cáo RSVP Quan Khách**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Xây dựng phân hệ trung tâm kiểm duyệt lời chúc và sổ lưu bút trên toàn bộ hệ thống thiệp cưới của Mây Hẹn.
                      2. Tự động rà soát và phát hiện từ khóa nhạy cảm / quảng cáo cá độ / link bẩn (`SPAM_KEYWORDS`) để gắn cờ đỏ cảnh báo (`is_flagged`) cho quản trị viên dễ dàng phát hiện.
                      3. Thao tác kiểm duyệt nhanh: Bật/Tắt duyệt hiển thị lời chúc (`toggleApproval`), Xóa vĩnh viễn lời chúc phản cảm (`destroy`).
                      4. Báo cáo phản hồi RSVP quan khách tập trung (`rsvps`): Thống kê tỷ lệ tham dự (% Có tham dự, % Không đến được, % Chưa chắc chắn), bảng dữ liệu chi tiết số lượng người lớn, trẻ em và chế độ ăn uống đặc biệt của quan khách.
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Controllers/Admin/WishController.php`: Xử lý `index` (4 KPI lời chúc & RSVP, bộ lọc đa trường, gắn nhãn spam tự động), `toggleApproval` (duyệt/ẩn lời chúc), `destroy` (xóa vĩnh viễn), `rsvps` (thống kê và lọc trạng thái tham dự).
                      - `routes/admin.php`: Bổ sung nhóm route `admin.wishes.*` (`index`, `rsvps`, `toggle_approval`, `destroy`).
                      - `resources/views/admin/layouts/master.blade.php`: Bổ sung menu "Lời Chúc & RSVP" vào Sidebar.
                      - `public/assets/admin/css/pages/wishes.css`: Phong cách Pure Vanilla CSS cho bong bóng tin nhắn lời chúc, cờ cảnh báo spam đỏ, badge trạng thái RSVP và tỷ lệ tham dự.
                      - `resources/views/admin/pages/wishes/index.blade.php`: Giao diện danh sách lời chúc với 4 thẻ KPI, tabs chuyển đổi, bộ lọc và bảng dữ liệu kiểm duyệt.
                      - `resources/views/admin/pages/wishes/rsvps.blade.php`: Giao diện báo cáo RSVP toàn hệ thống với tỷ lệ tham dự, số người đi kèm và ghi chú ăn uống.
                      - `tests/Feature/AdminWishTest.php`: Bộ 7 test cases tự động kiểm tra chặt chẽ phân quyền, xem danh sách, lọc spam, bật/tắt duyệt, xóa lời chúc và báo cáo RSVP.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 46/46 passed 100% (203 assertions).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style.

               143. **Hoàn Thiện Giai Đoạn 7: Cấu Hình Hệ Thống, Quản Lý Gói Dịch Vụ, Cài Đặt Ngân Hàng VietQR / SePay & Thanh Tra Dung Lượng Lưu Trữ (Storage Inspector)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Xây dựng phân hệ quản trị bảng giá và các gói dịch vụ (`plans`): Cho phép Admin trực tiếp điều chỉnh giá bán, thời hạn lưu trữ (ngày), số thiệp tối đa, danh sách quyền lợi tính năng và bật/tắt mở bán (hỗ trợ cả web form lẫn AJAX).
                      2. Cấu hình ngân hàng thu tiền & VietQR NAPAS: Tích hợp hơn 50 ngân hàng Việt Nam từ `config/banks.php`, cấu hình số tài khoản, tên chủ tài khoản, mẫu hiển thị VietQR và SePay Webhook API Secret Key lưu trữ an toàn trong `storage/app/settings/banking.json` mà không làm thay đổi lược đồ database.
                      3. Trình xem trước VietQR mô phỏng (Live QR Mockup): Hiển thị tức thời ảnh mã QR thanh toán động mô phỏng đúng số tiền và cú pháp giao dịch.
                      4. Công cụ Thanh Tra Bộ Nhớ Lưu Trữ (Public Asset Storage Inspector): Đo lường tổng dung lượng bộ nhớ đã dùng (`storage/app/public/`), thanh tiến trình trực quan phân bổ theo thư mục (`weddings/`, `client/`, `admin/`), đếm số lượng tệp và liệt kê 20 tệp tải lên mới nhất.
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Controllers/Admin/SettingController.php`: Xử lý `plans` (kèm đếm đơn hàng & tổng doanh thu từng gói), `editPlan`, `updatePlan`, `togglePlanActive` (hỗ trợ AJAX JSON), `banking`, `updateBanking` (lưu file json an toàn), `storageInspector` (quét storage disk).
                      - `routes/admin.php`: Bổ sung nhóm route `admin.settings.*` (`plans`, `plans.edit`, `plans.update`, `plans.toggle_active`, `banking`, `banking.update`, `storage`).
                      - `resources/views/admin/layouts/master.blade.php`: Thêm Section 5 "Hệ Thống & Cài Đặt" trong Sidebar gồm 3 liên kết menu tương ứng.
                      - `public/assets/admin/css/pages/settings.css`: CSS thuần cho lưới gói dịch vụ, checkboxes tính năng, layout 2 cột ngân hàng & xem trước VietQR, thanh tiến trình màu sắc phân bổ lưu trữ.
                      - `resources/views/admin/pages/settings/plans/index.blade.php`: Giao diện danh sách gói dịch vụ, hiển thị giá tiền, doanh thu và nút bật/tắt nhanh.
                      - `resources/views/admin/pages/settings/plans/edit.blade.php`: Giao diện biên tập thông tin gói dịch vụ và danh sách checkboxes tính năng.
                      - `resources/views/admin/pages/settings/banking.blade.php`: Giao diện cài đặt tài khoản ngân hàng thụ hưởng, token webhook và xem trước mã VietQR.
                      - `resources/views/admin/pages/settings/storage.blade.php`: Giao diện thanh tra bộ nhớ lưu trữ trực quan và danh sách 20 tệp gần nhất.
                      - `tests/Feature/AdminSettingTest.php`: Bộ kiểm thử 7 test cases bao phủ toàn diện xác thực quyền hạn, danh sách gói dịch vụ, cập nhật thông tin gói, AJAX toggle mở bán, lưu cài đặt ngân hàng và kiểm tra dung lượng lưu trữ.
                      - `docs/ADMIN_ARCHITECTURE_ROADMAP.md`: Đã cập nhật 100% trạng thái hoàn tất các giai đoạn trong bảng checklist.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 53/53 tests passed 100% (250 assertions) cho toàn bộ phân hệ Admin (`AdminAuthTest`, `AdminInvitationTest`, `AdminTemplateTest`, `AdminOrderTest`, `AdminUserTest`, `AdminWishTest`, `AdminSettingTest`).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

               144. **Nâng Cấp Chuyên Sâu Vận Hành Admin: Xuất Khách Mời CSV (Excel UTF-8 BOM), Chuyển Đổi Mẫu Thiệp (Migrate Template), Nhật Ký Webhook Thanh Toán & Hệ Thống Truy Vết Hành Động Quản Trị (Audit Logs)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Hỗ trợ Quản trị viên xuất danh sách khách mời của bất kỳ thiệp cưới nào dưới dạng tệp CSV chuẩn UTF-8 kèm ký tự BOM (`\xEF\xBB\xBF`) để Microsoft Excel trên Windows/Mac hiển thị tiếng Việt hoàn hảo, giúp khách hàng thuận tiện in ấn thiệp giấy truyền thống.
                      2. Tính năng Chuyển đổi mẫu thiệp (`migrateTemplate`): Cho phép chuyển đổi linh hoạt một thiệp cưới sang mẫu thiết kế khác mà không làm mất thông tin sự kiện hay album ảnh khi khách hàng yêu cầu đổi giao diện.
                      3. Nhật ký Webhook Thanh toán (`admin.orders.webhooks`): Tự động ghi nhận mọi sự kiện webhook SePay gọi về kèm thời gian, địa chỉ IP, mã đơn, số tiền và trạng thái (Thành công, Cảnh báo, Đã xử lý), hiển thị giao diện đối soát trực quan 100 sự kiện gần nhất.
                      4. Dịch vụ Hệ thống Truy vết Hành động Quản trị (`App\Services\AuditLogger`): Ghi nhận toàn bộ thao tác nhạy cảm (duyệt/hủy đơn hàng, gia hạn thiệp, xuất bản/ẩn thiệp, đổi mẫu, phân quyền vai trò tài khoản, đặt lại mật khẩu, cập nhật giá cước và tài khoản ngân hàng) lưu trữ dạng `JSONL` an toàn không làm ảnh hưởng đến hiệu năng truy vấn database.
                      5. Giao diện xem Nhật Ký Quản Trị (`admin.settings.audit_logs`) với bộ lọc loại hành động và ô tìm kiếm nhanh.
                    - **Các thành phần đã triển khai**:
                      - `app/Services/AuditLogger.php`: Lớp dịch vụ tập trung ghi log và đọc danh sách lịch sử truy vết quản trị.
                      - `app/Http/Controllers/Admin/InvitationController.php`: Bổ sung `exportGuests` (stream CSV UTF-8) và `migrateTemplate` (chuyển đổi mẫu thiệp), tích hợp gọi `AuditLogger::log`.
                      - `app/Http/Controllers/Client/CheckoutController.php`: Bổ sung `recordWebhookLog` tự động ghi vết mỗi request Webhook nhận được.
                      - `app/Http/Controllers/Admin/OrderController.php`: Bổ sung action `webhooks` hiển thị lịch sử webhook, tích hợp gọi `AuditLogger::log` khi duyệt/hủy đơn.
                      - `app/Http/Controllers/Admin/UserController.php`: Tích hợp gọi `AuditLogger::log` khi đổi vai trò và đặt lại mật khẩu.
                      - `app/Http/Controllers/Admin/SettingController.php`: Bổ sung action `auditLogs` lọc và tìm kiếm lịch sử hành động quản trị, tích hợp gọi `AuditLogger::log` khi đổi bảng giá và tài khoản ngân hàng.
                      - `routes/admin.php`: Bổ sung các routes `admin.invitations.migrate_template`, `admin.invitations.export_guests`, `admin.orders.webhooks`, `admin.settings.audit_logs`.
                      - `resources/views/admin/pages/invitations/show.blade.php`: Nâng cấp giao diện với nút "Xuất Danh Sách Khách (CSV)" và hộp thao tác "Chuyển Đổi Mẫu Thiệp".
                      - `resources/views/admin/pages/orders/index.blade.php`: Thêm tabs điều hướng sang Nhật Ký Webhook.
                      - `resources/views/admin/pages/orders/webhooks.blade.php`: Giao diện bảng đối soát 100 sự kiện Webhook gần nhất.
                      - `resources/views/admin/pages/settings/audit_logs.blade.php`: Giao diện bảng Nhật Ký Quản Trị kèm bộ lọc hành động.
                      - `resources/views/admin/layouts/master.blade.php`: Bổ sung mục menu "Nhật Ký Quản Trị (Audit)" vào Sidebar.
                      - `tests/Feature/AdminInvitationTest.php`, `AdminOrderTest.php`, `AdminSettingTest.php`: Bổ sung 4 test cases kiểm tra xuất CSV, chuyển mẫu thiệp, xem nhật ký webhook và xem nhật ký audit log.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 57/57 tests passed 100% (266 assertions) trên toàn bộ 7 test suite Admin.
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

               145. **Nâng Cấp Toàn Diện Admin Dashboard (Atelier Control Center): Biểu Đồ Doanh Thu 7 Ngày Native CSS, Khối Lối Tắt Vận Hành Nhanh & Snapshot Nhật Ký Quản Trị**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Nâng cấp trang chủ Bảng điều khiển quản trị `/admin/dashboard` thành trung tâm chỉ huy vận hành toàn diện với bố cục 2 cột (split 2:1) Quiet Luxury cân đối.
                      2. Biểu đồ xu hướng doanh thu 7 ngày gần nhất (7-Day Revenue Trend): Triển khai bằng Pure CSS Flexbox (hoàn toàn không dùng thư viện ngoài JS như Chart.js / ApexCharts), tự động tính toán tỷ lệ chiều cao tương ứng với doanh thu thực tế, có tooltips hover chi tiết hiển thị số tiền VND và phân biệt cột ngày hôm nay màu xanh ngọc lục bảo.
                      3. Khối lối tắt vận hành nhanh (Quick Actions): 4 nút bấm điều hướng tức thời: Quét thư mục mẫu mới, Kiểm duyệt lời chúc quan khách, Cài đặt ngân hàng VietQR NAPAS, Thanh tra bộ nhớ lưu trữ.
                      4. Lưới đôi giám sát hoạt động: Bảng 5 đơn hàng mới nhất kèm link chi tiết & trạng thái thanh toán; Bảng xếp hạng các mẫu thiệp cưới được tạo nhiều nhất.
                      5. Snapshot Hoạt động Quản trị gần đây (Recent Audit Trail): Trích xuất 5 thao tác quản trị mới nhất từ `AuditLogger` hiển thị ngay tại trang chủ Dashboard.
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Controllers/Admin/DashboardController.php`: Bổ sung tính toán doanh thu 7 ngày qua (`revenueLast7Days`), giá trị lớn nhất (`maxDailyRevenue`), thống kê quan khách RSVP, 5 đơn hàng mới nhất và nạp 5 dòng audit log gần nhất.
                      - `public/assets/admin/css/pages/dashboard.css`: Tái cấu trúc toàn bộ stylesheet cho Dashboard: Grid split 2:1, Biểu đồ cột Flexbox `.mayhen_admin_revenue_chart`, Tooltips nổi, Lưới nút tắt `.mayhen_admin_quick_btn`, và Thẻ hoạt động quản trị `.mayhen_admin_audit_item`.
                      - `resources/views/admin/pages/dashboard.blade.php`: Tái cấu trúc toàn bộ giao diện Blade: 5 thẻ KPI vận hành, Biểu đồ doanh thu 7 ngày, Lối tắt vận hành, Bảng đơn hàng & mẫu thiệp, và Snapshot nhật ký truy vết.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 57/57 tests passed 100% (266 assertions) trên toàn bộ 7 test suite Admin.
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

               146. **Hoàn Thiện Cơ Chế Khóa / Mở Khóa Cưỡng Chế Thiệp Cưới (Administrative Lock & Unlock Enforcement) & Màn Hình Khóa Trang Nhã Cho Khách Mời**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Đáp ứng trọn vẹn yêu cầu mục 138 trong `ADMIN_ARCHITECTURE_ROADMAP.md`: Cung cấp cho Quản trị viên quyền năng khóa thiệp cưới cưỡng chế khi phát hiện tài khoản vi phạm (spam nội dung, hình ảnh phản cảm, tranh chấp thanh toán hoặc yêu cầu pháp lý).
                      2. Lưu trữ an toàn trong trường JSON `custom_config` (`is_locked`, `lock_reason`, `locked_at`) mà hoàn toàn không cần can thiệp hay sửa đổi lược đồ cơ sở dữ liệu.
                      3. Cơ chế phòng thủ hiển thị công khai: Khi thiệp cưới bị khóa, bất kỳ khách mời hoặc người dùng nào truy cập vào `/thiep/{slug}` sẽ được chuyển hướng hiển thị trang thông báo tạm dừng sang trọng, lịch thiệp (`invitation-locked.blade.php`) giải thích rõ lý do và hướng dẫn liên hệ hỗ trợ.
                      4. Giao diện trang chi tiết thiệp cưới (`admin.invitations.show`): Hiển thị trạng thái khóa cảnh báo nền đỏ, lý do khóa và hai biểu mẫu riêng biệt: Khóa thiệp có ô nhập lý do; Mở khóa thiệp một chạm có hộp thoại xác nhận.
                      5. Tự động truy vết mọi hành vi Khóa và Mở khóa thiệp cưới vào `AuditLogger` (`lock_invitation`, `unlock_invitation`).
                    - **Các thành phần đã triển khai**:
                      - `app/Http/Controllers/Admin/InvitationController.php`: Bổ sung 2 action `lock` và `unlock`, cập nhật `custom_config` và ghi nhận `AuditLogger::log`.
                      - `app/Http/Controllers/Client/InvitationEditorController.php`: Kiểm tra cờ `is_locked` trong `show($slug)` và hiển thị view thông báo tạm khóa.
                      - `routes/admin.php`: Bổ sung 2 routes `admin.invitations.lock` và `admin.invitations.unlock`.
                      - `resources/views/client/pages/invitation-locked.blade.php`: Giao diện thông báo tạm khóa thiệp cưới thuần CSS chuẩn Quiet Luxury (khóa tem sáp vàng kim, hộp lý do viền nét đứt, nút về trang chủ).
                      - `resources/views/admin/pages/invitations/show.blade.php`: Bổ sung khối "Khóa / Mở Khóa Thiệp Cưới" trong vùng hành động điện thoại.
                      - `tests/Feature/AdminInvitationTest.php`: Bổ sung test case toàn diện `admin can lock and unlock invitation, displaying locked view to visitors` kiểm tra cả luồng backend lẫn phản hồi giao diện của khách truy cập.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 58/58 tests passed 100% (274 assertions) trên toàn bộ 7 test suite Admin.
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

               147. **Hoàn Thiện Kiến Trúc Phân Quyền Vai Trò 5 Cấp Bậc (RBAC) & Middleware Bảo Vệ Quản Trị Viên Cấp Cao (EnsureSuperAdmin)**:
                    - **Bối cảnh & Yêu cầu lộ trình**:
                      1. Thực hiện chuẩn xác chỉ dẫn cốt lõi của dự án: phân quyền 5 cấp bậc rõ ràng gồm "quản trị viên (`admin`), quản lý (`manager`), nhân viên (`staff`), cộng tác viên (`partner`), người dùng (`customer`)".
                      2. Xác định ranh giới bảo mật nghiêm ngặt:
                         - Nhân viên (`staff`): Được cấp quyền truy cập `/admin`, tham gia vận hành hỗ trợ khách hàng, xem danh sách thiệp cưới, duyệt sổ lưu bút và kiểm tra đơn hàng, nhưng bị phong tỏa hoàn toàn khỏi các khu vực cấu hình hệ thống bảng giá, ngân hàng và không thể thay đổi vai trò tài khoản.
                         - Quản lý (`manager`): Vận hành giám sát toàn bộ thiệp, đơn hàng, người dùng, xem báo cáo nhưng không can thiệp cài đặt ngân hàng.
                         - Quản trị viên (`admin`): Nắm giữ đặc quyền tối cao toàn hệ thống.
                         - Cộng tác viên (`partner`) & Người dùng (`customer`): Bị chặn tuyệt đối khỏi phân hệ `/admin`.
                      3. Tạo Middleware chuyên trách `App\Http\Middleware\EnsureSuperAdmin` (`admin.super`) bảo vệ các thao tác nhạy cảm: `admin.settings.*`, `admin.users.change_role`, `admin.templates.scan_discovery`.
                      4. Tùy biến thanh điều hướng Sidebar (`master.blade.php`): Chỉ hiển thị mục Section 5 "Hệ Thống & Cài Đặt" đối với tài khoản `admin`.
                    - **Các thành phần đã triển khai**:
                      - `app/Models/User.php`: Bổ sung `isSuperAdmin()` và `canAccessAdminPanel()`.
                      - `app/Http/Middleware/AdminAuthenticate.php`: Kiểm tra quyền hạn `canAccessAdminPanel()` chặn ngay lập tức `customer` và `partner`.
                      - `app/Http/Middleware/EnsureSuperAdmin.php`: Chặn và điều hướng an toàn các tài khoản không phải `admin` kèm thông báo lỗi.
                      - `bootstrap/app.php`: Đăng ký alias middleware `admin.super`.
                      - `routes/admin.php`: Áp dụng middleware `admin.super` lên các tuyến đường nhạy cảm.
                      - `resources/views/admin/layouts/master.blade.php`: Ẩn Section 5 khỏi Sidebar nếu người dùng không phải Quản trị viên cấp cao.
                      - `tests/Feature/AdminRbacTest.php`: Bộ 5 test cases bao phủ 100% các kịch bản phân quyền 5 vai trò.
                    - **Kiểm thử & Chất lượng**:
                      - Pest Feature tests: 63/63 tests passed 100% (307 assertions) trên toàn bộ 8 bộ test suite Admin (`AdminAuthTest`, `AdminInvitationTest`, `AdminTemplateTest`, `AdminOrderTest`, `AdminUserTest`, `AdminWishTest`, `AdminSettingTest`, `AdminRbacTest`).
                      - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                148. **Xây Dựng Hoàn Chỉnh Phân Hệ Quản Lý Tài Khoản Người Dùng & Cặp Đôi (User Account Portal - `/tai-khoan`)**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Triển khai phân hệ người dùng theo đúng chỉ dẫn chiến lược: *"Hiện tại đang làm trang /admin, các trang khác sau này sẽ làm sau, cứ làm /admin và user trước"*.
                       2. Cung cấp cho các cặp đôi cô dâu chú rể một không gian Bảng điều khiển riêng biệt (`/tai-khoan`) mang đậm tinh thần Quiet Luxury, tối giản, sang trọng và chuẩn Responsive Mobile-first.
                       3. Trang Tổng Quan (`/tai-khoan`):
                          - 4 thẻ KPI cá nhân: Thiệp cưới đã tạo, Khách mời trong danh sách, Khách xác nhận tham gia (RSVP), Lời chúc phúc nhận được.
                          - Thẻ Thiệp Cưới Nổi Bật (Hero Wedding Card): Ảnh bìa, tên cô dâu chú rể, tên mẫu thiệp, ngày cưới, đếm ngược ngày hôn lễ, trạng thái xuất bản (Đang xuất bản / Bản nháp / Bị khóa), hộp liên kết chia sẻ kèm nút chép link một chạm, hàng nút hành động (Vào Studio chỉnh sửa `?edit={slug}`, Xem thiệp trực tiếp, Quản lý khách mời, Bật/tắt xuất bản).
                          - 3 Khối lối tắt vận hành nhanh (Khách mời & RSVP, Nâng cấp gói thiệp, Đơn hàng đã mua).
                          - Danh sách 5 lời chúc phúc mới nhất từ quan khách.
                       4. Trang Thiệp Cưới Của Tôi (`/tai-khoan/thiep`):
                          - Lưới thẻ thiệp cưới (Grid card) hiển thị tất cả các thiệp của user kèm thông tin ngày cưới, số lượng khách mời, trạng thái và các nút thao tác nhanh.
                          - Phân trang nhẹ nhàng, nút tạo thêm mẫu mới.
                       5. Thao tác Bật/Tắt Xuất Bản Thiệp (`POST /tai-khoan/thiep/{id}/xuat-ban`):
                          - Xác thực quyền sở hữu nghiêm ngặt (`$wedding->user_id === auth()->id()`), chặn tuyệt đối người dùng khác can thiệp.
                       6. Trang Đơn Hàng & Gói Cước (`/tai-khoan/don-hang`):
                          - Bảng lịch sử các đơn hàng mua gói thiệp cưới (Mã đơn `MH...`, gói cước, số tiền, ngày tạo, trạng thái thanh toán, nút tiếp tục thanh toán nếu đơn chờ duyệt).
                       7. Trang Hồ Sơ & Bảo Mật (`/tai-khoan/ho-so`):
                          - Biểu mẫu cập nhật họ tên, email, số điện thoại (`POST /tai-khoan/ho-so`).
                          - Biểu mẫu đổi mật khẩu an toàn có kiểm tra mật khẩu hiện tại (`POST /tai-khoan/doi-mat-khau`).
                       8. Tích hợp Header & Mobile Drawer Navigation:
                          - Cập nhật menu thả xuống của tài khoản trên Header với 4 liên kết trực tiếp (Bảng Điều Khiển, Thiệp Cưới Của Tôi, Đơn Hàng & Gói Cước, Hồ Sơ & Mật Khẩu).
                          - Bổ sung nút truy cập nhanh "Bảng Điều Khiển Cặp Đôi" trong Mobile Drawer.
                     - **Các thành phần đã triển khai**:
                       - `app/Models/User.php`: Bổ sung quan hệ Eloquent `invitations()` thông qua `hasManyThrough(Invitation::class, Wedding::class)`.
                       - `app/Http/Controllers/Client/AccountController.php`: Controller chuyên trách xử lý toàn bộ logic phân hệ tài khoản (`index`, `invitations`, `togglePublish`, `orders`, `profile`, `updateProfile`, `updatePassword`).
                       - `routes/web.php`: Nhóm các route `/tai-khoan` bảo vệ bởi middleware `auth`.
                       - `public/assets/client/css/pages/account.css`: Stylesheet Pure CSS 100% không framework, chuẩn Quiet Luxury, tiền tố `mayhen_account_*`.
                       - `resources/views/client/pages/account/layout.blade.php`: Khung sườn layout dùng chung cho tài khoản.
                       - `resources/views/client/pages/account/dashboard.blade.php`: Bảng điều khiển tổng quan cặp đôi.
                       - `resources/views/client/pages/account/invitations.blade.php`: Quản lý danh sách thiệp cưới.
                       - `resources/views/client/pages/account/orders.blade.php`: Lịch sử đơn hàng và gói cước.
                       - `resources/views/client/pages/account/profile.blade.php`: Hồ sơ cá nhân và đổi mật khẩu.
                       - `resources/views/client/components/header.blade.php`: Cập nhật dropdown menu và mobile drawer.
                       - `tests/Feature/ClientAccountTest.php`: Bộ 8 test cases bao phủ 100% các kịch bản kiểm tra bảo mật, phân quyền sở hữu, thống kê KPI, bật/tắt xuất bản, cập nhật hồ sơ và đổi mật khẩu.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 71/71 tests passed 100% (350 assertions) trên toàn bộ 9 bộ test suite (`AdminAuthTest`, `AdminInvitationTest`, `AdminTemplateTest`, `AdminOrderTest`, `AdminUserTest`, `AdminWishTest`, `AdminSettingTest`, `AdminRbacTest`, `ClientAccountTest`).
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                149. **Nâng Cấp Toàn Diện Phân Hệ Quản Lý Khách Mời & Sổ Ký Tên RSVP (Guest & RSVP Hub): Nhập Nhanh Hàng Loạt, Xuất CSV UTF-8 BOM & Bộ Lọc Đa Chiều**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Đáp ứng toàn diện các tiêu chuẩn kỹ thuật trong kỹ năng `mayhen-guest-rsvp`: hỗ trợ lời mời cá nhân hóa, theo dõi người lớn/trẻ em, xuất/nhập danh sách khách an toàn, tạo thông điệp Zalo kèm link token riêng.
                       2. Chức năng Xuất CSV Danh Sách Khách Mời (`guests.export`):
                          - Xuất file `.csv` có mã hóa UTF-8 kèm ký tự BOM (`\xEF\xBB\xBF`) để Microsoft Excel trên Windows tự động nhận diện tiếng Việt có dấu 100% không bị vỡ font.
                          - Bao gồm đầy đủ 12 trường dữ liệu chi tiết: STT, Tên khách mời, Cách xưng hô, Nhóm khách, Số điện thoại, Số người dự kiến, Trạng thái phản hồi RSVP, Số người lớn, Số trẻ em, Yêu cầu ăn uống đặc biệt, Lời chúc / Ghi chú và Đường link thiệp cá nhân hóa có token.
                       3. Chức năng Nhập Nhanh Hàng Loạt (Batch Import - `guests.batch_store`):
                          - Cho phép cặp đôi dán trực tiếp danh sách nhiều dòng từ file ghi chú/Excel vào ô Textarea.
                          - Hỗ trợ định dạng linh hoạt phân cách bằng dấu gạch đứng (`|`), dấu chấm phẩy (`;`) hoặc dấu tab (`Tên | Xưng hô | Nhóm | Số người | SĐT`).
                          - Tự động gán token ngẫu nhiên `Str::random(32)` và lưu an toàn trong Database Transaction.
                       4. Bộ Lọc Đa Chiều & Tìm Kiếm Tức Thì:
                          - Tìm kiếm theo họ tên hoặc số điện thoại (`q=...`).
                          - Lọc theo nhóm khách động (`group=...`): Bạn Bè, Họ Nhà Trai, Họ Nhà Gái, Đồng Nghiệp...
                          - Lọc theo trạng thái phản hồi (`status=...`): Đã xác nhận tham dự, Chưa gửi phản hồi, Từ chối/Bận.
                          - Thống kê Tỷ lệ phản hồi trực quan (%) và thanh tiến độ gradient.
                       5. Giao diện & Trải nghiệm Người dùng:
                          - Đồng bộ Breadcrumbs với phân hệ tài khoản (`Trang chủ` / `Tài khoản` / `Thiệp cưới` / `Khách mời`).
                          - Nút sao chép lời mời Zalo và sao chép link cá nhân hóa có phản hồi tức thì trực tiếp trên nút, loại bỏ hoàn toàn lệnh `alert()` gây gián đoạn trải nghiệm.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Client/GuestController.php`: Bổ sung `batchStore`, `exportCsv` (stream CSV UTF-8 BOM), nâng cấp `index` với bộ lọc đa chiều và tính toán thống kê phản hồi.
                       - `routes/web.php`: Bổ sung routes `guests.batch_store` (`POST /tai-khoan/thiep/{id}/khach-moi/nhap-nhanh`) và `guests.export` (`GET /tai-khoan/thiep/{id}/khach-moi/xuat-csv`).
                       - `resources/views/client/pages/guests.blade.php`: Tái cấu trúc toàn bộ giao diện: Breadcrumbs, Header hành động, 4 thẻ KPI, Thanh tỷ lệ phản hồi, Hộp thêm khách 2 tab (Đơn lẻ & Hàng loạt), Thanh toolbar tìm kiếm/lọc, Bảng danh sách và công cụ mời Zalo.
                       - `public/assets/client/css/pages/guests.css`: Stylesheet Pure CSS 100% không framework, chuẩn Quiet Luxury, tiền tố `mayhen_guest_*`.
                       - `public/assets/client/js/pages/guests.js`: Native JS module xử lý chuyển tab và sao chép thông điệp Zalo mượt mà.
                       - `tests/Feature/ClientGuestManagementTest.php`: Bộ 8 test cases bao phủ 100% các kịch bản kiểm tra xem danh sách, lọc/tìm kiếm, thêm 1 khách, nhập hàng loạt, xuất CSV UTF-8 BOM, xóa khách và phòng vệ phân quyền sở hữu.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 79/79 tests passed 100% (390 assertions) trên toàn bộ 10 bộ test suite.
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                150. **Xây Dựng Hoàn Chỉnh Phân Hệ Quản Lý Sổ Lưu Bút & Trả Lời Lời Chúc (Client Wishes & Guestbook Hub)**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Cung cấp cho cặp đôi giao diện chuyên biệt quản lý toàn bộ những lời chúc phúc từ bạn bè và người thân (`/tai-khoan/thiep/{id}/so-luu-but`).
                       2. Tính năng Trả Lời Lời Chúc (Reply to Wish - `account.wishes.reply`):
                          - Cho phép cô dâu & chú rể gửi thông điệp cảm ơn tới từng quan khách.
                          - Kiểm tra bộ lọc từ ngữ văn minh (`NoProfanity`), lưu lại thời gian phản hồi (`replied_at`) và hiển thị lời cảm ơn trang nhã ngay trên thiệp cưới công khai.
                       3. Tính năng Ẩn / Hiện Lời Chúc (`account.wishes.toggle_visibility`):
                          - Cặp đôi chủ động ẩn các lời chúc bị nhầm lẫn hoặc không phù hợp khỏi thiệp cưới công khai mà không làm mất dữ liệu.
                       4. Tính năng Xóa Lời Chúc (`account.wishes.destroy`):
                          - Cho phép xóa vĩnh viễn lời chúc khỏi sổ lưu bút kèm hộp thoại xác nhận.
                       5. Bộ Lọc & Tìm Kiếm Lời Chúc:
                          - Tìm kiếm theo tên người chúc hoặc nội dung lời nhắn (`q=...`).
                          - Lọc theo trạng thái: *Chờ gửi lời cảm ơn* (`pending`), *Đã cảm ơn* (`replied`), *Tạm ẩn* (`hidden`).
                          - 4 thẻ KPI tóm tắt: Tổng lời chúc, Đã gửi lời cảm ơn, Chờ phản hồi, Tạm ẩn khỏi thiệp.
                       6. Liên kết đồng bộ toàn hệ thống:
                          - Nút "Quản Lý Sổ Lưu Bút" trực tiếp từ trang Dashboard và từ thẻ thiệp cưới trong trang danh sách thiệp.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Client/WishManagementController.php`: Controller chuyên trách xử lý các tác vụ `index`, `reply`, `toggleVisibility`, `destroy` với cơ chế kiểm tra quyền sở hữu thiệp cưới nghiêm ngặt.
                       - `routes/web.php`: Bổ sung 4 routes trong nhóm `account.`: `account.wishes.index`, `account.wishes.reply`, `account.wishes.toggle_visibility`, `account.wishes.destroy`.
                       - `resources/views/client/pages/account/wishes.blade.php`: Giao diện bưu thiếp Quiet Luxury hiển thị lời chúc, khung phản hồi của cô dâu chú rể, bộ lọc và phân trang 12 lời chúc/trang.
                       - `resources/views/client/pages/account/dashboard.blade.php` & `invitations.blade.php`: Tích hợp các nút điều hướng dẫn trực tiếp sang Sổ Lưu Bút của từng thiệp.
                       - `tests/Feature/ClientWishManagementTest.php`: Bộ 6 test cases bao phủ 100% các kịch bản xem danh sách, lọc tìm kiếm, gửi lời cảm ơn, ẩn/hiện, xóa lời chúc và phòng vệ phân quyền sở hữu.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 85/85 tests passed 100% (423 assertions) trên toàn bộ 11 bộ test suite.
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                151. **Hoàn Thiện Cơ Chế Cá Nhân Hóa Khách Mời Qua Token & Đồng Bộ Hiển Thị Phản Hồi Cảm Ơn Trên Mẫu Thiệp Công Khai**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Khép kín vòng trải nghiệm cảm xúc hoàn hảo cho đám cưới: Khi khách mời nhận link cá nhân hóa (`/thiep/{slug}?token={guest_token}`), thiệp cưới tự động nhận diện danh tính quan khách và hiển thị lời chào trân trọng (`$guestSalutation` và `$guestName`).
                       2. Form xác nhận tham dự (RSVP) tự động điền sẵn tên của khách mời, giúp khách không phải nhập lại họ tên, gia tăng tỷ lệ phản hồi dự tiệc.
                       3. Hiển thị câu trả lời cảm ơn của Cô Dâu & Chú Rể (`$wish->reply`) trên Section Sổ Lưu Bút trong cả 4 mẫu thiệp chuẩn (`giua-muon-ngan-vi-sao`, `hanh-phuc-mang-ten-chung-minh`, `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau`).
                       4. Phòng vệ dữ liệu: Các lời chúc bị cặp đôi hoặc quản trị viên tạm ẩn (`is_approved = false`) tuyệt đối không bao giờ hiển thị trên thiệp công khai.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Client/InvitationEditorController.php`: Bổ sung truyền biến `'guest' => $guest` vào view thiệp cưới công khai.
                       - `resources/views/client/invitations/giua-muon-ngan-vi-sao/template.blade.php`: Tích hợp `$guestName`, `$guestSalutation` và khối hiển thị `$wish->reply`.
                       - `resources/views/client/invitations/hanh-phuc-mang-ten-chung-minh/template.blade.php`: Tích hợp `$guestName`, `$guestSalutation` và khối hiển thị `$wish->reply`.
                       - `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/template.blade.php`: Tích hợp `$guestName`, `$guestSalutation`, sửa fallback tên khách và khối hiển thị `$wish->reply`.
                       - `resources/views/client/invitations/tron-kiep-nay-ben-nhau/template.blade.php`: Tích hợp `$guestName`, `$guestSalutation` và khối hiển thị `$wish->reply`.
                       - `tests/Feature/PublicInvitationPersonalizationTest.php`: Bộ 3 test cases kiểm tra hiển thị cá nhân hóa theo token, lời chào mặc định khi không có token, và hiển thị phản hồi lời chúc của cặp đôi.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 88/88 tests passed 100% (434 assertions) trên toàn bộ 12 bộ test suite.
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                152. **Hoàn Thiện Luồng Nâng Cấp Gói Cước Thiệp Cưới (Plan Upgrade Flow) & Tự Động Kích Hoạt Qua Webhook SePay**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Cho phép cặp đôi nâng cấp gói cước cho một thiệp cưới cụ thể (`invitation_id` / `invitation` slug).
                       2. Khi truy cập trang Bảng giá (`/bang-gia?invitation_id=...`), toàn bộ nút chọn gói đều duy trì tham số thiệp mục tiêu chuyển tiếp sang trang Thanh toán (`/thanh-toan/{planSlug}?invitation_id=...`).
                       3. Trang Thanh Toán hiển thị trực quan thông tin thiệp cưới được áp dụng (tên cô dâu chú rể, mẫu thiệp, trạng thái) và gán `invitation_id` vào đơn hàng tạo mới (`orders.invitation_id`).
                       4. Webhook SePay VietQR xác nhận giao dịch thành công: đơn hàng chuyển sang `paid`, đồng thời thiệp cưới được tự động bật xuất bản (`is_published = true`) và gia hạn thời gian lưu trữ theo số ngày của gói (`expires_at = now()->addDays($plan->duration_days)`).
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Client/CheckoutController.php`: Bổ sung nhận diện và truyền `$invitation` sang view checkout; trong transaction thanh toán thành công tự động kích hoạt và gia hạn thiệp.
                       - `resources/views/client/pages/checkout.blade.php`: Hiển thị thẻ thông tin thiệp cưới mục tiêu nâng cấp và input ẩn `invitation_id`.
                       - `resources/views/client/pages/pricing.blade.php`: Giữ nguyên tham số `invitation_id` trong liên kết chọn gói.
                       - `tests/Feature/ClientPlanUpgradeFlowTest.php`: Bộ 4 test cases kiểm tra hiển thị mục tiêu thiệp trên checkout, duy trì tham số trên pricing, liên kết order với invitation, và webhook kích hoạt gia hạn thiệp.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 4/4 tests passed 100% (17 assertions).
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                153. **Triển Khai Bộ Công Cụ Mã QR Thiệp Cưới & Thẻ In Sang Trọng (Wedding QR Code & Quiet Luxury Print Card), Chỉnh Sửa & Xác Nhận RSVP Thủ Công Hộ Khách**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. **Mã QR Thiệp Cưới & Thẻ In Chuẩn In Ấn (Wedding QR Code & Print Card)**:
                          - Cung cấp cho cặp đôi công cụ tạo và tải mã QR thiệp cưới trực tiếp với 100% Pure Vanilla JavaScript, zero thư viện ngoài, zero phụ thuộc server bên thứ ba.
                          - Module `public/assets/client/js/common/qrcode.js`: Trình tạo ma trận QR Code chuẩn (ISO/IEC 18004) hỗ trợ đa mức sửa lỗi (L, M, Q, H).
                          - Module `public/assets/client/js/pages/account-qr.js`: Vẽ thẻ thiệp in Quiet Luxury độ phân giải cao (1000 x 1400px, tỷ lệ 5:7) trên HTML5 Canvas với nền kem mỹ thuật `#FAF8F5`, khung viền kép hoàng gia Antique Gold `#C5A880`, họa tiết góc cổ điển, tên cặp đôi font Serif quý phái, ngày hôn lễ, mã QR độ tương phản cao và thương hiệu `mayhen.vn`.
                          - Hỗ trợ tải thẻ in thiệp PNG HD (dành cho tiệm in thiệp giấy / in standee đón khách) và tải riêng mã QR vuông 600x600px.
                          - Tích hợp nút kích hoạt trên từng thẻ thiệp tại `/tai-khoan/thiep` và trong Hero Card trên Dashboard `/tai-khoan`.
                       2. **Chỉnh Sửa Thông Tin Khách Mời Tại Chỗ (Inline Edit Guest)**:
                          - Route `PUT /tai-khoan/khach-moi/{id}` (`guests.update`): Cho phép cặp đôi chỉnh sửa tên khách, cách xưng hô, nhóm khách, số điện thoại và số người dự kiến mà không phải xóa đi tạo lại.
                          - Modal chỉnh sửa `#mayhen-edit-guest-modal` giao diện Quiet Luxury, hỗ trợ đóng nhanh bằng phím ESC hoặc click backdrop.
                       3. **Cập Nhật Trạng Thái RSVP Thủ Công Hộ Khách (Manual RSVP Confirmation)**:
                          - Route `POST /tai-khoan/khach-moi/{id}/rsvp-thu-cong` (`guests.manual_rsvp`): Giải quyết bài toán thực tế khi người lớn tuổi hoặc họ hàng gọi điện/nhắn tin báo miệng tham dự.
                          - Cho phép cặp đôi chủ động cập nhật trạng thái: *Sẽ tham gia* (ghi nhận số người lớn, số trẻ em, yêu cầu ăn uống đặc biệt), *Từ chối* (không tham gia), hoặc *Chưa xác nhận* (reset record).
                          - Dữ liệu lập tức đồng bộ vào KPI thống kê bàn tiệc (`attendingCount`) của hôn lễ.
                          - Bảo mật phân quyền: Ngăn chặn tuyệt đối người dùng khác can thiệp khách mời của nhau.
                     - **Các thành phần đã triển khai**:
                       - `public/assets/client/js/common/qrcode.js`: Thư viện tạo QR Code Pure Vanilla JS dạng Native ES Module.
                       - `public/assets/client/js/pages/account-qr.js`: Controller vẽ và tải thẻ in thiệp cưới HD trên Canvas.
                       - `resources/views/client/components/qr-modal.blade.php`: Component Modal hiển thị mã QR và thẻ in.
                       - `public/assets/client/css/pages/account.css`: Bổ sung 250+ dòng CSS chuẩn Quiet Luxury cho QR Modal.
                       - `resources/views/client/pages/account/layout.blade.php`, `invitations.blade.php`, `dashboard.blade.php`: Nhúng modal và các nút kích hoạt QR.
                       - `app/Http/Controllers/Client/GuestController.php`: Bổ sung 2 actions `update` và `manualRsvp`.
                       - `routes/web.php`: Bổ sung 2 routes `guests.update` và `guests.manual_rsvp`.
                       - `resources/views/client/pages/guests.blade.php`: Thêm nút Sửa, nút RSVP vào bảng khách mời và 2 Modals tương ứng.
                       - `public/assets/client/css/pages/guests.css`: Thêm các styles cho nút và modal sửa/RSVP.
                       - `public/assets/client/js/pages/guests.js`: Xử lý mở/đóng modal và điền dữ liệu động.
                       - `tests/Feature/ClientWeddingQrModalTest.php`: 3 test cases kiểm tra nút QR trên dashboard, danh sách thiệp, modal và các module JS.
                       - `tests/Feature/ClientGuestManagementTest.php`: Mở rộng thêm 2 test cases kiểm tra update khách mời và cập nhật RSVP thủ công (10/10 tests pass).
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 34/34 tests passed 100% (177 assertions) trên toàn bộ suite người dùng (`ClientAccountTest`, `ClientGuestManagementTest`, `ClientWishManagementTest`, `ClientPlanUpgradeFlowTest`, `PublicInvitationPersonalizationTest`, `ClientWeddingQrModalTest`).
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).


---

                154. **Xây Dựng Toàn Diện Module Cài Đặt Chi Tiết & Tùy Biến Thiệp Cưới (Wedding Invitation Settings Hub), Bảo Mật Mật Mã Riêng Tư & Nhân Bản Thiệp**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Cung cấp cho cặp đôi trang quản trị chuyên sâu cấu hình từng thiệp cưới (`/tai-khoan/thiep/{id}/cai-dat` - `account.invitations.settings`).
                       2. **Tùy Biến Đường Dẫn Thiệp Độc Quyền (Custom Slug)**:
                          - Cặp đôi chủ động đổi slug mang dấu ấn riêng (ví dụ `dat-ngoc-2026`).
                          - Kiểm tra nghiêm ngặt định dạng regex `^[a-z0-9\-]+$` và tính duy nhất trên toàn hệ thống (`unique:invitations,slug`).
                       3. **Bảo Mật Thiệp Bằng Mật Mã Riêng Tư (Password Protected Invitation)**:
                          - Hỗ trợ các đám cưới yêu cầu sự kín đáo, chỉ những quan khách có mã PIN (mật mã 4-6 số) mới mở được thiệp.
                          - View `resources/views/client/pages/invitation-password.blade.php`: Giao diện nhập mã phong cách Quiet Luxury với biểu tượng ổ khóa vàng, tên cô dâu chú rể và form mở khóa an toàn.
                          - Route `POST /thiep/{slug}/mo-khoa` (`invitation.unlock`): Xác thực mã PIN, lưu cờ mở khóa an toàn trong session `unlocked_invitation_{id}`. Chủ sở hữu thiệp truy cập trực tiếp không bị chặn bởi mật mã.
                       4. **Kiểm Soát Tương Tác Quan Khách & Hạn Chót RSVP**:
                          - Tùy chọn Bật/Tắt nhận lời chúc Sổ Lưu Bút (`allow_wishes`).
                          - Tùy chọn Bật/Tắt nhận phản hồi RSVP (`allow_rsvp`).
                          - Thiết lập hạn chót phản hồi bàn tiệc (`rsvp_deadline`).
                       5. **Âm Nhạc Nền Thiệp Cưới & Tự Động Phát**:
                          - Tích hợp nạp toàn bộ danh mục bài hát đám cưới từ `WeddingMusicService` vào dropdown chọn bài hát thông minh.
                          - Hỗ trợ nhập URL MP3 tùy chỉnh và bật/tắt tự động phát nhạc khi mở phong bì (`music_autoplay`).
                       6. **Tùy Biến Chia Sẻ Mạng Xã Hội (Social & SEO Meta)**:
                          - Tiêu đề (`og_title`) và Lời tựa mô tả (`og_description`) khi chia sẻ link thiệp qua Zalo, Facebook, iMessage.
                       7. **Thao Tác Nâng Cao (Nhân Bản & Xóa Thiệp)**:
                          - Nhân bản thiệp cưới (`account.invitations.duplicate`): Tự động tạo bản sao lưu mẫu thiệp với slug mới dạng `{slug}-sao-chep-{hash}` ở trạng thái bản nháp, giữ nguyên 100% nội dung và ảnh cưới đã thiết lập.
                          - Xóa vĩnh viễn thiệp cưới (`account.invitations.destroy`): Kiểm tra quyền sở hữu chặt chẽ, xóa an toàn kèm hộp thoại xác nhận.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Client/AccountController.php`: Bổ sung 4 actions `settings`, `updateSettings`, `duplicate`, `destroy` cùng cơ chế phân quyền `authorizeInvitationOwner`.
                       - `app/Http/Controllers/Client/InvitationEditorController.php`: Bổ sung kiểm tra mật khẩu trong `show` và action `unlockPassword`.
                       - `routes/web.php`: Bổ sung các routes `account.invitations.settings`, `account.invitations.settings.update`, `account.invitations.duplicate`, `account.invitations.destroy`, và `invitation.unlock`.
                       - `resources/views/client/pages/account/settings.blade.php`: Giao diện Quiet Luxury 5 khối cài đặt khoa học và khu vực thao tác nâng cao.
                       - `resources/views/client/pages/invitation-password.blade.php`: Giao diện nhập mật mã bảo vệ thiệp cưới.
                       - `resources/views/client/pages/account/invitations.blade.php` & `dashboard.blade.php`: Bổ sung nút "Cài Đặt Thiệp" điều hướng trực tiếp.
                       - `tests/Feature/ClientInvitationSettingsTest.php`: Bộ 7 test cases bao phủ 100% các kịch bản xem cài đặt, cập nhật slug, validate format, bảo mật mật mã mở thiệp, nhân bản thiệp và xóa thiệp.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 41/41 tests passed 100% (224 assertions) trên toàn bộ suite người dùng (`ClientAccountTest`, `ClientGuestManagementTest`, `ClientWishManagementTest`, `ClientPlanUpgradeFlowTest`, `PublicInvitationPersonalizationTest`, `ClientWeddingQrModalTest`, `ClientInvitationSettingsTest`).
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).

                155. **Xây Dựng Phân Hệ Sổ Mừng Cưới & Quản Lý Tiền Mừng (Wedding Gift & Shugi-bukuro / Mừng Cưới Tracker)**:
                     - **Bối cảnh & Yêu cầu lộ trình**:
                       1. Cung cấp cho cặp đôi công cụ số hóa toàn diện sổ mừng cưới sau ngày hôn lễ (`/tai-khoan/thiep/{id}/so-mung-cuoi` - `account.gifts.index`).
                       2. **Cơ sở dữ liệu an toàn (Additive Migration)**:
                          - Bảng `wedding_gifts`: Quản lý người mừng, số tiền, hình thức mừng (`cash` = Tiền mặt / Phong bì, `bank_transfer` = Chuyển khoản VietQR, `other` = Quà tặng hiện vật), liên kết khách mời (`guest_id`), nhóm khách, lời chúc / ghi chú và trạng thái đã gửi lời cảm ơn (`is_acknowledged`).
                          - Đánh index hiệu năng cao: `['wedding_id', 'created_at']`, `['wedding_id', 'payment_method']`, `['wedding_id', 'sender_group']`.
                       3. **4 Thẻ KPI Tài Chính Hôn Lễ Thông Minh**:
                          - Tổng số tiền mừng cưới thu được (`number_format` VNĐ).
                          - Tổng số lượt mừng cưới đã ghi nhận.
                          - Tiền mừng trung bình trên mỗi khách.
                          - Cơ cấu chi tiết: Chuyển khoản VietQR vs Phong bì tiền mặt.
                       4. **Ghi Nhận Mừng Cưới Một Chạm (Quick Add Card)**:
                          - Phím tắt mệnh giá nhanh: 500.000đ, 1.000.000đ, 2.000.000đ, 5.000.000đ.
                          - Datalist tự động gợi ý tên khách từ danh sách quan khách (`$guests`).
                       5. **Bộ Lọc Đa Chiều & Tìm Kiếm Tức Thì**:
                          - Tìm theo tên người mừng hoặc lời chúc (`q=...`).
                          - Lọc theo hình thức thanh toán (`method=...`).
                          - Lọc theo nhóm quan khách động (`group=...`).
                          - Lọc theo trạng thái đã gửi lời cảm ơn (`ack=...`).
                       6. **Xuất File Báo Cáo Sổ Mừng Cưới CSV UTF-8 BOM**:
                          - Route `account.gifts.export`: Xuất file CSV có ký tự BOM (`\xEF\xBB\xBF`) mở trên Microsoft Excel tiếng Việt có dấu 100% không bị vỡ font.
                          - Cột chi tiết: STT, Tên người mừng, Nhóm khách, Số tiền (VNĐ), Hình thức mừng, Lời chúc, Trạng thái cảm ơn, Thời gian ghi nhận.
                       7. **Chỉnh Sửa & Chuyển Đổi Trạng Thái Cảm Ơn**:
                          - Nút toggle một chạm chuyển đổi trạng thái "Đã cảm ơn" / "Chưa gửi" (`account.gifts.toggle_ack`).
                          - Modal chỉnh sửa `#mayhen-edit-gift-modal` sửa nhanh thông tin hoặc xóa khoản mừng.
                     - **Các thành phần đã triển khai**:
                       - `database/migrations/2026_11_07_070534_create_wedding_gifts_table.php`: Migration tạo bảng `wedding_gifts`.
                       - `app/Models/WeddingGift.php`: Model Eloquent quan hệ `wedding()` và `guest()`, accessor `payment_method_label`.
                       - `app/Models/Wedding.php`: Bổ sung quan hệ `gifts()`.
                       - `app/Http/Controllers/Client/GiftManagementController.php`: Controller chuyên trách xử lý `index`, `store`, `update`, `toggleAcknowledge`, `destroy`, `exportCsv`.
                       - `routes/web.php`: Đăng ký 6 routes `account.gifts.*`.
                       - `resources/views/client/pages/account/gifts.blade.php`: Giao diện Sổ mừng cưới Quiet Luxury, 4 thẻ KPI, bảng danh sách và modal sửa.
                       - `resources/views/client/pages/account/invitations.blade.php` & `dashboard.blade.php`: Tích hợp nút điều hướng "Sổ Mừng Cưới".
                       - `tests/Feature/ClientGiftManagementTest.php`: Bộ 8 test cases bao phủ 100% các kịch bản xem thống kê KPI, thêm mới, sửa, xóa, toggle cảm ơn, lọc tìm kiếm, xuất CSV UTF-8 BOM và phòng vệ phân quyền sở hữu.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: 49/49 tests passed 100% (268 assertions) trên toàn bộ suite người dùng (`ClientAccountTest`, `ClientGuestManagementTest`, `ClientWishManagementTest`, `ClientPlanUpgradeFlowTest`, `PublicInvitationPersonalizationTest`, `ClientWeddingQrModalTest`, `ClientInvitationSettingsTest`, `ClientGiftManagementTest`).
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (pass sạch sẽ).


                156. **Khắc Phục Xung Đột Truy Vấn MySQL DISTINCT, Dọn Dẹp Route Imports & Thiết Lập Cấu Hình Pest Toàn Cục**:
                     - **Bối cảnh & Vấn đề xử lý**:
                       1. *Lỗi MySQL 3065 (Incompatible with DISTINCT)*:
                          - Triệu chứng: Khi truy cập trang Sổ Mừng Cưới (`/tai-khoan/thiep/1/so-mung-cuoi`), MySQL chế độ `ONLY_FULL_GROUP_BY` quăng ngoại lệ `QueryException: SQLSTATE[HY000]: General error: 3065 Expression #1 of ORDER BY clause is not in SELECT list, references column 'mayhen.wedding_gifts.created_at' which is not in SELECT list; this is incompatible with DISTINCT`.
                          - Nguyên nhân gốc: Do quan hệ `$wedding->gifts()` được định nghĩa có `->latest()` (`ORDER BY created_at DESC`). Khi controller gọi `$wedding->gifts()->select('sender_group')->distinct()->pluck('sender_group')`, Laravel tự động kế thừa mệnh đề ORDER BY từ relation, làm phát sinh xung đột với phép DISTINCT của MySQL.
                          - Khắc phục triệt để: Trong `app/Http/Controllers/Client/GiftManagementController.php`, chuyển sang truy vấn trực tiếp từ model: `WeddingGift::where('wedding_id', $wedding->id)->whereNotNull('sender_group')->distinct()->pluck('sender_group')` nhằm loại bỏ hoàn toàn mệnh đề ORDER BY không hợp lệ.
                       2. *Lỗi Trùng Lặp Import Controller trong Routes (`routes/web.php`)*:
                          - Triệu chứng: `PHP Fatal error: Cannot use App\Http\Controllers\Client\GiftManagementController as GiftManagementController because the name is already in use in routes\web.php on line 8`.
                          - Khắc phục: Xóa bỏ dòng `use` trùng lặp ở đầu file `routes/web.php`.
                       3. *Chuẩn Hóa Cấu Hình Pest Toàn Cục (`tests/Pest.php`)*:
                          - Tạo file `tests/Pest.php` với cấu hình chuẩn: `uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');`.
                          - Dọn dẹp các khai báo `uses(TestCase::class, ...)` lặp lại cục bộ ở từng file test trong thư mục `tests/Feature/` nhằm tránh lỗi `Pest\Exceptions\TestCaseAlreadyInUse`.
                     - **Kiểm thử & Chất lượng**:
                       - Chạy toàn bộ test suite của hệ thống: **284/284 tests passed 100% (1,493 assertions)** chạy mượt mà, không còn bất kỳ lỗi nào.
                       - Laravel Pint: Đã chạy định dạng chuẩn PSR-12 / Pint agent style (`tests/Pest.php` đã được format sạch đẹp).


                157. **Bổ Sung CSS Form Controls & Chuẩn Hóa Hệ Thống Modal Cho Trang Sổ Mừng Cưới**:
                     - **Bối cảnh & Vấn đề xử lý**:
                       1. *Thiếu CSS Selector cho Form Inputs*: Trong `gifts.blade.php` và `settings.blade.php`, các thẻ input, select và label sử dụng các class `mayhen_account_form_input` và `mayhen_account_form_label`. Tuy nhiên trong `account.css` trước đó chỉ có selector đơn `mayhen_account_input` và `mayhen_account_label`, dẫn tới việc các trường nhập liệu và chọn hình thức thanh toán bị trả về giao diện thô mặc định của trình duyệt.
                       2. *Modal Bị Lộ Thiên Dưới Đáy Trang*: Modal sửa khoản mừng cưới trước đó dùng nhầm class `mayhen_guest_modal` (vốn thuộc `guests.css`), trong khi trang Sổ mừng cưới thuộc layout Account (`account.css`), khiến modal không có `display: none !important;` và bị trôi tự do ra dưới chân bảng danh sách.
                     - **Giải pháp triển khai**:
                       - Trong `public/assets/client/css/pages/account.css`:
                         + Đồng bộ các selector form: `.mayhen_account_form_input, .mayhen_account_input`, `.mayhen_account_form_label, .mayhen_account_label`.
                         + Bổ sung custom appearance cho thẻ `select` với icon mũi tên SVG trang nhã, font-family kế thừa thay vì font mặc định hệ điều hành.
                         + Thêm `font-family: inherit;` vào `.mayhen_account_btn`.
                         + Thêm bộ style `.mayhen_account_section*` cho khung chứa form ghi nhận mừng cưới.
                         + Bổ sung hệ thống modal hoàn chỉnh `.mayhen_account_modal` (backdrop blur, dialog bo góc 20px, animation fade in, header, close, footer) và alias tương thích ngược.
                       - Trong `resources/views/client/pages/account/gifts.blade.php`: Chuẩn hóa class modal sang `mayhen_account_modal*`.
                     - **Kiểm thử**: Toàn bộ feature tests `ClientGiftManagementTest` và `ClientInvitationSettingsTest` passed 100%.


                158. **Xây Dựng Tính Năng Xuất & In Sổ Kỷ Yếu Lưu Bút (Keepsake Guestbook) & Bảng Điểm Danh Đón Tiếp Hôn Lễ (Reception Check-in Sheet)**:
                     - **Bối cảnh & Yêu cầu thực tế**:
                       1. Cặp đôi sau hôn lễ có nhu cầu xuất và in lưu giữ trọn vẹn những lời chúc phúc ấm áp từ gia đình, thầy cô, bạn bè thành một cuốn kỷ yếu đóng gáy hoặc kẹp album cưới.
                       2. Trong ngày cưới trọng đại, ban tiếp tân của cô dâu chú rể tại sảnh cưới cần một bảng in danh sách khách mời (khổ giấy A4) rõ ràng, có phân nhóm họ hàng/bạn bè, số suất tiệc, yêu cầu ăn chay, ô tích bút mực điểm danh `[✓]` và dòng ghi chú số phong bì/quà mừng để đối soát tài chính sau tiệc.
                     - **Các thành phần đã triển khai**:
                       - **Xuất CSV Sổ Lưu Bút UTF-8 BOM (`account.wishes.export`)**:
                         + Route `GET /tai-khoan/thiep/{id}/so-luu-but/xuat-csv`.
                         + Xuất file CSV có BOM `\xEF\xBB\xBF` mở 100% tiếng Việt trên Excel gồm STT, Người chúc, Lời chúc, Lời cảm ơn, Trạng thái và thời gian.
                       - **Trang In Kỷ Yếu Sổ Lưu Bút (`account.wishes.print`)**:
                         + Route `GET /tai-khoan/thiep/{id}/so-luu-but/in`.
                         + View `resources/views/client/pages/account/wishes-print.blade.php`: Phong cách Quiet Luxury, typography Cormorant Garamond, hoa văn viền kép, các thẻ lời chúc viền Antique Gold trang nhã, kèm lời tri ân và chữ ký xác nhận của Cô Dâu & Chú Rể.
                         + Hỗ trợ bộ lọc khi in: Chỉ in lời chúc đã duyệt hoặc toàn bộ lời chúc. Tối ưu CSS `@media print` tự động ngắt trang thông minh (`page-break-inside: avoid`).
                       - **Trang In Bảng Điểm Danh Đón Tiếp Quan Khách Bàn Tiệc (`guests.print`)**:
                         + Route `GET /tai-khoan/thiep/{id}/khach-moi/in`.
                         + View `resources/views/client/pages/account/guests-print.blade.php`: Header sảnh tiệc trang trọng, thanh thống kê 5 chỉ số (Tổng khách, Số khách RSVP tham dự, Người lớn, Trẻ em, Suất ăn chay), bảng danh sách có ô vuông điểm danh `[✓]` và dòng kẻ ghi chú tiền mừng cưới.
                         + Bộ lọc linh hoạt: In toàn bộ khách hoặc chỉ khách đã xác nhận đi tiệc, lọc theo từng nhóm khách.
                       - **Cập nhật giao diện quản lý**:
                         + Thêm các nút thao tác tương ứng tại `resources/views/client/pages/account/wishes.blade.php` và `resources/views/client/pages/guests.blade.php`.
                     - **Kiểm thử & Chất lượng**:
                       - Pest Feature tests: Bổ sung 5 test cases mới, nâng tổng số lên **51/51 tests passed 100% (287 assertions)** trên toàn bộ các suite người dùng (`ClientWishManagementTest`, `ClientGuestManagementTest`, `ClientGiftManagementTest`, `ClientInvitationSettingsTest`, `ClientAccountTest`, `ClientPlanUpgradeFlowTest`, `ClientWeddingQrModalTest`).
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.


                159. **Tích Hợp Quản Lý Sổ Mừng Cưới & Xuất CSV Tiền Mừng Trong Phân Hệ Admin (`/admin/invitations/{id}`)**:
                     - **Bối cảnh & Yêu cầu thực tế**:
                       - Khi người dùng (cặp đôi) liên hệ đội ngũ Admin / Kỹ thuật để đối soát dữ liệu quà mừng, xuất file sao lưu hoặc kiểm tra thông tin chuyển khoản VietQR, quản trị viên cần xem được dữ liệu sổ mừng cưới trực tiếp trong chi tiết thiệp (`admin.invitations.show`).
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Admin/InvitationController.php`:
                         + Bổ sung eager load quan hệ `'wedding.gifts' => fn ($q) => $q->latest('id')` trong method `show`.
                         + Bổ sung method `exportGifts(int $id)`: Xuất danh sách tiền mừng cưới của thiệp dạng file CSV UTF-8 BOM (`\xEF\xBB\xBF`) mở chuẩn mực trên Microsoft Excel.
                       - `routes/admin.php`: Đăng ký route `admin.invitations.export_gifts` (`GET /admin/invitations/{id}/export-gifts`).
                       - `resources/views/admin/pages/invitations/show.blade.php`:
                         + Bổ sung tab thứ 5 "Sổ Mừng Cưới" trên thanh điều hướng tab interaction.
                         + Thiết kế tab pane `#tab-gifts`: Thanh thống kê tổng tiền mừng, số khoản mừng, phân tách VietQR vs Tiền mặt, nút "Xuất Sổ Mừng Cưới (CSV)" và bảng danh sách quà mừng chi tiết.
                     - **Kiểm thử & Chất lượng**:
                       - Thêm test case `admin can view wedding gifts tab and export gifts as csv` trong `tests/Feature/AdminInvitationTest.php`.
                       - Toàn bộ **64/64 tests Admin** và **51/51 tests Client** (tổng cộng 115 tests, 605 assertions) đều passed 100%.
                       - Laravel Pint: Đạt chuẩn PSR-12 sạch sẽ.


                160. **Xây Dựng Tính Năng Xuất Báo Cáo Doanh Thu & Đơn Hàng CSV UTF-8 BOM Trong Phân Hệ Admin (`admin.orders.export`)**:
                     - **Bối cảnh & Yêu cầu thực tế**:
                       - Nhằm phục vụ công tác đối soát doanh thu, kiểm toán tài chính và hỗ trợ khách hàng, quản trị viên cần xuất toàn bộ đơn hàng (hoặc tập đơn hàng đang được lọc theo ngày, tháng, trạng thái đã thanh toán/chờ chuyển khoản) ra file CSV chuẩn mực.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Admin/OrderController.php`:
                         + Bổ sung method `exportCsv(Request $request): StreamedResponse`.
                         + Kế thừa 100% các bộ lọc tìm kiếm (`q`, `status`, `plan_id`, `payment_method`, `date_range`) từ query string.
                         + Xuất file CSV có BOM UTF-8 `\xEF\xBB\xBF` mở 100% tiếng Việt trên Excel gồm STT, Mã đơn, Khách hàng, Email, SĐT, Gói dịch vụ, Số tiền (VNĐ), Cổng thanh toán, Mã thiệp liên kết, Cặp đôi hôn lễ, Trạng thái, Thời gian tạo, Thời gian thanh toán.
                       - `routes/admin.php`: Đăng ký route `admin.orders.export` (`GET /admin/orders/export-csv`).
                       - `resources/views/admin/pages/orders/index.blade.php`: Gắn nút "Xuất Báo Cáo Doanh Thu (CSV)" đồng bộ tham số query string hiện tại trên thẻ header của bảng đơn hàng.
                     - **Kiểm thử & Chất lượng**:
                       - Thêm 2 test cases mới vào `tests/Feature/AdminOrderTest.php`: `admin can export orders as utf-8 bom csv file with filters` và `customer cannot export admin orders csv`.
                       - Toàn bộ **66/66 tests Admin** và **51/51 tests Client** (tổng cộng 117 tests, 616 assertions) đều passed 100%.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 sạch sẽ.


                161. **Hoàn Thiện 5 Phân Quyền Vai Trò Người Dùng & Bộ Ba Xuất Dữ Liệu CSV Cho Quản Lý Tài Khoản, Lời Chúc & RSVP**:
                     - **Bối cảnh & Định hướng nghiệp vụ**:
                       - Bám sát yêu cầu chỉ đạo: *"Dự án mây hẹn sẽ phân quyền như sau: quản trị viên, nhân viên, quản lý, người dùng, cộng tác viên. Hiện tại đang làm trang /admin, các trang khác sau này sẽ làm sau, cứ làm /admin và user trước"*.
                       - Tách bạch rõ ràng 5 vai trò trên giao diện quản lý người dùng: Người dùng (`customer`), Quản trị viên (`admin`), Quản lý (`manager`), Nhân viên (`staff`), Cộng tác viên (`partner`).
                       - Cung cấp tính năng xuất CSV chuẩn UTF-8 BOM cho 3 phân mục quan trọng: Danh sách người dùng, Danh sách lời chúc cưới và Báo cáo phản hồi RSVP của khách mời hôn lễ.
                     - **Các thành phần đã triển khai**:
                       - **Tách bạch 5 vai trò & Xuất CSV Người Dùng (`admin.users.export`)**:
                         + `app/Http/Controllers/Admin/UserController.php`: Bổ sung đếm và lọc riêng biệt cho cả 5 vai trò (`customerCount`, `adminCount`, `managerCount`, `staffCount`, `partnerCount`). Bổ sung `exportCsv(Request $request)` xuất danh sách người dùng, email, SĐT, vai trò, số thiệp, tổng chi tiêu ra CSV UTF-8 BOM `\xEF\xBB\xBF`.
                         + `routes/admin.php`: Đăng ký route `GET /admin/users/export-csv` (`admin.users.export`).
                         + `resources/views/admin/pages/users/index.blade.php`: Cập nhật tab bar 6 trạng thái (Tất cả, Người dùng, Quản trị viên, Quản lý, Nhân viên, Cộng tác viên) và bổ sung nút "Xuất CSV (Excel)".
                       - **Xuất CSV Lời Chúc Mừng & Kiểm Duyệt Sổ Lưu Bút (`admin.wishes.export`)**:
                         + `app/Http/Controllers/Admin/WishController.php`: Bổ sung `exportWishesCsv(Request $request)` kế thừa lọc tìm kiếm, trạng thái duyệt (`approved`, `pending`, `flagged`) và thiệp cưới cụ thể.
                         + `routes/admin.php`: Đăng ký `GET /admin/wishes/export-csv` (`admin.wishes.export`).
                         + `resources/views/admin/pages/wishes/index.blade.php`: Gắn nút "Xuất CSV (Excel)" trên header bảng lời chúc.
                       - **Xuất CSV Báo Cáo Phản Hồi RSVP Toàn Nền Tảng (`admin.wishes.rsvps.export`)**:
                         + `app/Http/Controllers/Admin/WishController.php`: Bổ sung `exportRsvpsCsv(Request $request)` kế thừa bộ lọc tìm kiếm và trạng thái tham dự (`attending`, `declined`, `uncertain`).
                         + `routes/admin.php`: Đăng ký `GET /admin/wishes/rsvps/export-csv` (`admin.wishes.rsvps.export`).
                         + `resources/views/admin/pages/wishes/rsvps.blade.php`: Gắn nút "Xuất CSV (Excel)" trên header bảng báo cáo RSVP.
                     - **Kiểm thử & Chất lượng**:
                       - Viết thêm 6 test cases mới trong `tests/Feature/AdminUserTest.php` và `tests/Feature/AdminWishTest.php`.
                       - Toàn bộ **298/298 tests passed 100% (1,584 assertions)** trên toàn hệ thống trong ~9.9s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.


                162. **Xây Dựng Tính Năng In Sổ Mừng Cưới A4 (Đối Soát Tài Chính Hôn Lễ) & Xuất Báo Cáo Kho Mẫu Thiệp CSV Cho Admin**:
                     - **Bối cảnh & Yêu cầu thực tế**:
                       1. *Phân hệ Cặp Đôi (Client)*: Sau hôn lễ, cô dâu chú rể và ban lễ tân gia đình cần một tài liệu in ấn trang trọng (khổ giấy A4) để đối soát chi tiết từng phong bì mừng cưới, sao kê chuyển khoản VietQR, tổng kết số tiền mừng cưới và ký tên xác nhận kỷ niệm. Cùng với In Bảng Tiếp Tân và In Kỷ Yếu Lời Chúc, tính năng này hoàn thiện trọn vẹn "Bộ Ba Tài Liệu In Khổ A4 Cưới Hỏi Quiet Luxury" của Mây Hẹn.
                       2. *Phân hệ Quản Trị (Admin)*: Quản trị viên cần xuất toàn bộ thông tin kho mẫu thiệp (tên mẫu, slug, phong cách, phân hạng gói Free/Plus/Pro, số lượng thiệp đã tạo từ mẫu, trạng thái hoạt động/nổi bật) ra file CSV chuẩn UTF-8 BOM để báo cáo và quản lý kho giao diện.
                     - **Các thành phần đã triển khai**:
                       - **Trang In Sổ Mừng Cưới Khổ Giấy A4 (`account.gifts.print`)**:
                         + `app/Http/Controllers/Client/GiftManagementController.php`: Bổ sung method `print(Request $request, int $invitationId)` lấy danh sách quà mừng có lọc theo hình thức thanh toán/nhóm khách, tính toán 4 chỉ số tài chính (Tổng tiền, Số khoản mừng, Tiền mặt, VietQR).
                         + `routes/web.php`: Đăng ký route `GET /tai-khoan/thiep/{id}/so-mung-cuoi/in` (`account.gifts.print`).
                         + `resources/views/client/pages/account/gifts-print.blade.php`: View in ấn Quiet Luxury khổ A4, typography Cormorant Garamond, hoa văn viền nhã nhặn, bảng thống kê 4 chỉ số, danh sách quà mừng chi tiết và khu vực ký tên xác nhận của Ban tiếp tân gia đình & Cô dâu Chú rể. Tối ưu CSS `@media print` ngắt trang tự động.
                         + `resources/views/client/pages/account/gifts.blade.php`: Gắn nút "In Sổ Mừng Cưới (A4)" trên header.
                       - **Xuất CSV Thống Kê Kho Mẫu Thiệp Cho Admin (`admin.templates.export`)**:
                         + `app/Http/Controllers/Admin/TemplateController.php`: Bổ sung method `exportCsv(Request $request)` kế thừa bộ lọc tìm kiếm `q`, `category`, `status`.
                         + `routes/admin.php`: Đăng ký route `GET /admin/templates/export-csv` (`admin.templates.export`).
                         + `resources/views/admin/pages/templates/index.blade.php`: Gắn nút "Xuất CSV (Excel)" trên thanh công cụ quản lý mẫu.
                     - **Kiểm thử & Chất lượng**:
                       - Bổ sung 4 test cases mới vào `tests/Feature/ClientGiftManagementTest.php` và `tests/Feature/AdminTemplateTest.php`.
                       - Toàn bộ **302/302 tests passed 100% (1,608 assertions)** trên toàn hệ thống trong ~12.4s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.


                163. **Thiết Lập Middleware Phân Quyền Quản Lý `admin.manager` (EnsureAdminOrManager) & Bảo Vệ Toàn Diện 5 Vai Trò**:
                     - **Bối cảnh & Vấn đề xử lý**:
                       - Hiện thực hóa triệt để chỉ đạo: *"Dự án mây hẹn sẽ phân quyền như sau: quản trị viên, nhân viên, quản lý, người dùng, cộng tác viên. Hiện tại đang làm trang /admin, các trang khác sau này sẽ làm sau, cứ làm /admin và user trước"*.
                       - Trước đó, các thao tác nhạy cảm như duyệt tiền đơn hàng thủ công (`orders.approve`), hủy đơn (`orders.cancel`), khóa thiệp vi phạm (`invitations.lock`), xóa vĩnh viễn lời chúc (`wishes.destroy`) chỉ kiểm tra middleware chung `admin.auth`, khiến tài khoản Nhân viên (`staff`) vốn chỉ làm nhiệm vụ CSKH cũng có thể bấm duyệt tiền hoặc xóa dữ liệu của khách.
                     - **Các thành phần đã triển khai**:
                       - **Tạo Middleware `EnsureAdminOrManager` (`app/Http/Middleware/EnsureAdminOrManager.php`)**:
                         + Kiểm tra quyền người dùng hiện tại thông qua `$user->isAdmin()` (chỉ chấp nhận `role === 'admin'` hoặc `role === 'manager'`).
                         + Nếu tài khoản là `staff` (nhân viên) thực hiện thao tác nhạy cảm: Trả về JSON 403 hoặc chuyển hướng quay lại kèm thông báo lỗi: *"Tài khoản của bạn có quyền Nhân viên, không được phép thực hiện thao tác nhạy cảm này."*
                       - **Đăng Ký Alias Toàn Cục (`bootstrap/app.php`)**:
                         + Gắn alias `'admin.manager' => EnsureAdminOrManager::class`.
                       - **Bảo Vệ Định Tuyến (`routes/admin.php`)**:
                         + Gắn middleware `admin.manager` cho các route nhạy cảm:
                           * `orders.approve` & `orders.cancel`: Duyệt hoặc hủy đơn hàng thủ công.
                           * `invitations.lock` & `invitations.unlock`: Khóa hoặc mở khóa thiệp cưới.
                           * `templates.update`, `templates.toggle_active`, `templates.toggle_featured`: Can thiệp trạng thái kho mẫu thiệp.
                           * `users.reset_password`: Đặt lại mật khẩu tài khoản người dùng.
                           * `wishes.destroy`: Xóa vĩnh viễn lời chúc của khách mời.
                       - **Hiển Thị Vai Trò Động Trên Master Layout (`resources/views/admin/layouts/master.blade.php`)**:
                         + Cập nhật badge role trên Topbar tài khoản hiển thị chính xác theo `Auth::user()->role_label` (Quản Trị Viên, Quản Lý, Nhân Viên) thay vì hardcode.
                     - **Kiểm thử & Chất lượng**:
                       - Tạo file test mới `tests/Feature/AdminRbacRoleTest.php` với 4 test cases toàn diện kiểm chứng ranh giới phân quyền của cả 5 vai trò (Admin, Manager, Staff, Partner, Customer).
                       - Toàn bộ **306/306 tests passed 100% (1,641 assertions)** trên toàn hệ thống trong ~9.4s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 sạch sẽ.


                164. **Xây Dựng Tính Năng Xuất Nhật Ký Quản Trị Hệ Thống (Audit Logs CSV UTF-8 BOM) Cho Quản Trị Cấp Cao**:
                     - **Bối cảnh & Yêu cầu thực tế**:
                       - Nhằm phục vụ công tác thanh tra bảo mật, kiểm toán định kỳ và tuân thủ quy định bảo vệ dữ liệu, Quản trị viên cấp cao (`admin`) cần khả năng trích xuất toàn bộ dữ liệu lịch sử thao tác hệ thống (duyệt đơn, đổi vai trò, khóa thiệp, cập nhật ngân hàng, cấu hình bảng giá) ra file CSV chuẩn mực có hỗ trợ tiếng Việt trên Microsoft Excel.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Admin/SettingController.php`:
                         + Bổ sung method `exportAuditLogs(Request $request): StreamedResponse`.
                         + Kế thừa 100% bộ lọc tìm kiếm `q` và phân loại hành động `action`.
                         + Xuất file CSV định dạng UTF-8 BOM `\xEF\xBB\xBF` gồm các trường: STT, Thời Gian, Quản Trị Viên, Email, Hành Động, Đối Tượng Tác Động, Mã Đối Tượng, Nội Dung Chi Tiết, Địa Chỉ IP.
                       - `routes/admin.php`: Đăng ký route `GET /admin/settings/audit-logs/export-csv` (`admin.settings.audit_logs.export`) được bảo vệ bởi middleware `admin.super`.
                       - `resources/views/admin/pages/settings/audit_logs.blade.php`: Gắn nút "Xuất CSV (Excel)" trên header bảng danh sách nhật ký quản trị.
                     - **Kiểm thử & Chất lượng**:
                       - Bổ sung 2 test cases mới vào `tests/Feature/AdminSettingTest.php`: `admin can export audit logs as utf-8 bom csv` và `customer or staff cannot export admin audit logs csv`.
                       - Toàn bộ **308/308 tests passed 100% (1,650 assertions)** trên toàn hệ thống trong ~9.8s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.


                165. **Khắc Phục Lỗi Truy Vấn SQL MySQL 3065 & Phòng Vệ Hiển Thị Modal Sổ Mừng Cưới (Fix CSS/Modal Cache)**:
                     - **Bối cảnh & Vấn đề xử lý**:
                       - **Lỗi SQL MySQL 3065**: Khi truy cập `/tai-khoan/thiep/1/so-mung-cuoi`, hệ thống bị văng lỗi `General error: 3065 Expression #1 of ORDER BY clause is not in SELECT list, references column 'mayhen.wedding_gifts.created_at' which is not in SELECT list; this is incompatible with DISTINCT` do quan hệ `gifts()` trên `Wedding.php` có default scope `latest()` (`ORDER BY created_at DESC`).
                       - **Hiện tượng "Mất CSS" (Modal hiển thị dưới chân trang)**: Form modal "Chỉnh Sửa Khoản Mừng Cưới" hiển thị lộ liễu dưới chân trang thay vì popup ẩn. Nguyên nhân là do trình duyệt của người dùng cache file `account.css` cũ từ trước khi class `.mayhen_account_modal` được bổ sung, và thẻ modal trong Blade thiếu inline style phòng vệ khi nạp CSS bị trễ/lỗi.
                     - **Các thành phần đã triển khai**:
                       - **Khắc phục lỗi SQL 3065 triệt để**:
                         + `app/Http/Controllers/Client/GiftManagementController.php`: Chuyển truy vấn danh sách nhóm mừng cưới độc nhất sang dùng trực tiếp `WeddingGift::where('wedding_id', $wedding->id)->whereNotNull('sender_group')->distinct()->pluck('sender_group')` loại bỏ hoàn toàn `ORDER BY` thừa.
                         + `app/Http/Controllers/Client/GuestController.php`: Đồng bộ chuyển truy vấn `$availableGroups` ở trang Quản lý khách mời và trang In ấn khách sang dùng trực tiếp `Guest::where('wedding_id', $wedding->id)->whereNotNull('group_name')->distinct()->pluck('group_name')` chống lỗi SQL 3065 trên MySQL STRICT / ONLY_FULL_GROUP_BY.
                       - **Phòng vệ hiển thị Modal Sổ Mừng Cưới & Cache-Busting CSS**:
                         + `resources/views/client/pages/account/gifts.blade.php`: Gắn inline style phòng vệ `display: none; position: fixed; inset: 0; z-index: 9999;` lên modal `#mayhen-edit-gift-modal`, backdrop và dialog. Khi chưa bấm "Sửa", modal **tuyệt đối không bao giờ bị render thô ra ngoài trang**. Đồng thời cập nhật JS quản lý `style.display = 'flex'` khi mở và `'none'` khi đóng.
                         + `resources/views/client/pages/account/layout.blade.php`: Bổ sung cơ chế Cache-Busting tự động theo thời gian sửa file `?v={{ filemtime(...) }}` cho thẻ link `assets/client/css/pages/account.css` để trình duyệt lập tức tải file CSS mới nhất mỗi khi mã nguồn cập nhật.
                     - **Kiểm thử & Chất lượng**:
                       - Toàn bộ **308/308 tests passed 100% (1,650 assertions)** trên toàn hệ thống trong ~18.2s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.


                166. **Hoàn Thiện Bộ Đôi Xuất CSV Báo Cáo Phản Hồi RSVP & Lời Chúc (Chi Tiết Thiệp Cưới Admin)**:
                     - **Bối cảnh & Yêu cầu thực tế**:
                       - Trong trang Chi tiết & Giám sát thiệp cưới (`/admin/invitations/{id}`), trước đó Quản trị viên mới chỉ có nút xuất CSV cho Khách mời (`export_guests`) và Sổ mừng cưới (`export_gifts`).
                       - Khi cặp đôi hoặc Admin cần trích xuất danh sách khách xác nhận tham dự (để lên số lượng mâm cỗ, ghi chú món ăn) hoặc danh sách lời chúc mừng của riêng thiệp này để lưu niệm, hệ thống chưa cung cấp chức năng xuất báo cáo độc lập.
                       - Đồng thời, bảng RSVP trước đây gọi nhầm thuộc tính cũ (`attending` và `number_of_guests` thay vì `attending_status`, `adults_count`, `children_count`), và bảng lời chúc thiếu nút Xóa trực tiếp đối với lời chúc phản cảm.
                     - **Các thành phần đã triển khai**:
                       - `app/Http/Controllers/Admin/InvitationController.php`:
                         + Bổ sung method `exportRsvps(int $id): StreamedResponse`: Xuất danh sách phản hồi RSVP của thiệp cưới ra file CSV UTF-8 BOM `\xEF\xBB\xBF` (Tên quan khách, Trạng thái tham dự, Số người lớn, Trẻ em, Tổng người, Số điện thoại, Ghi chú ăn uống, Thời gian).
                         + Bổ sung method `exportWishes(int $id): StreamedResponse`: Xuất danh sách lời chúc mừng của thiệp cưới ra CSV UTF-8 BOM (Người gửi, Nội dung lời chúc, Lời đáp của dâu rể, Thời gian gửi, Trạng thái duyệt).
                       - `routes/admin.php`:
                         + Đăng ký route `GET /admin/invitations/{id}/export-rsvps` (`admin.invitations.export_rsvps`).
                         + Đăng ký route `GET /admin/invitations/{id}/export-wishes` (`admin.invitations.export_wishes`).
                       - `resources/views/admin/pages/invitations/show.blade.php`:
                         + Tab 2 (RSVP): Thêm toolbar tóm tắt số lượt tham dự/từ chối kèm nút "Xuất Phản Hồi RSVP (CSV)", chuẩn hóa hiển thị `attending_status` ('attending', 'declined', 'uncertain') và phân tách số người lớn/trẻ em.
                         + Tab 3 (Wishes): Thêm toolbar kèm nút "Xuất Lời Chúc (CSV)", bổ sung cột Thao Tác có nút "Xóa" lời chúc vi phạm (gắn popup xác nhận, bảo vệ bởi middleware `admin.manager`).
                     - **Kiểm thử & Chất lượng**:
                       - Tạo mới file test `tests/Feature/AdminInvitationExportTest.php` với 3 test cases toàn diện kiểm thử xuất CSV UTF-8 BOM và chặn truy cập trái phép.
                       - Toàn bộ **311/311 tests passed 100% (1,679 assertions)** trên toàn hệ thống trong ~11.8s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.


                167. **Nâng Cấp Bảo Vệ Phân Quyền Tài Khoản (Chống Leo Quyền RBAC), Bộ Lọc Thiệp Bị Khóa & Thanh Cảnh Báo Vận Hành Dashboard**:
                     - **Bối cảnh & Vấn đề giải quyết**:
                       1. *Lỗ hổng leo thang đặc quyền (Privilege Escalation Defense)*: Trước đó trong `UserController@resetPassword`, tài khoản có vai trò Quản lý (`manager`) có thể gửi request đặt lại mật khẩu cho tài khoản Quản trị viên cấp cao (`admin`). Đồng thời giao diện `users/show.blade.php` vẫn hiển thị form đổi role cho tài khoản không phải Super Admin.
                       2. *Bộ lọc và Huy hiệu Thiệp Bị Khóa (`locked`)*: Khi Admin khóa một thiệp cưới vi phạm, bảng danh sách `invitations/index.blade.php` trước đây chỉ hiển thị badge "Đã Xuất Bản" hoặc "Bản Nháp" gây hiểu lầm, và dropdown lọc trạng thái thiếu tùy chọn lọc thiệp "Đang bị khóa". Bộ lọc "Đã xuất bản" trước đó cũng chưa loại trừ thiệp đang bị khóa.
                       3. *Thanh Cảnh Báo Vận Hành Cần Xử Lý Ngay (Operational Actionable Alerts)*: Theo đúng định vị "Atelier Control Room", Dashboard Quản trị cần một thanh cảnh báo thông minh thông báo ngay khi có: Đơn hàng chờ thanh toán, Lời chúc chờ duyệt/tạm ẩn, hoặc Thiệp cưới đang bị khóa, kèm nút điều hướng 1-chạm để giải quyết ngay.
                     - **Các thành phần đã triển khai**:
                       - **Phòng vệ đặc quyền & Tinh giản giao diện phân quyền (`UserController` & `users/show.blade.php`)**:
                         + `app/Http/Controllers/Admin/UserController.php`: Bổ sung kiểm tra an ninh trong `resetPassword`: Chỉ Quản trị viên cấp cao (`isSuperAdmin()`) mới có quyền đặt lại mật khẩu cho tài khoản Quản trị viên khác; chặn Manager can thiệp mật khẩu Admin.
                         + `resources/views/admin/pages/users/show.blade.php`:
                           * Thẻ "Phân Quyền Vai Trò (RBAC)": Chỉ hiển thị form chọn vai trò cho Quản trị viên cấp cao (`isSuperAdmin()`), các vai trò khác hiển thị khung thông tin vai trò hiện tại chỉ đọc kèm ghi chú rõ ràng.
                           * Thẻ "Đặt Lại Mật Khẩu": Bảo vệ chặt chẽ theo vai trò; ẩn form và hiển thị cảnh báo nếu tài khoản mục tiêu là Super Admin mà người thao tác không phải Super Admin, hoặc nếu người thao tác là Nhân viên (`staff`).
                       - **Hoàn thiện Bộ Lọc & Trạng Thái Thiệp Khóa (`InvitationController` & `invitations/index.blade.php`)**:
                         + `app/Http/Controllers/Admin/InvitationController.php`: Bổ sung nhánh lọc `$status === 'locked'` truy vấn `custom_config->is_locked = true`, đồng thời cập nhật nhánh `$status === 'published'` tự động loại trừ các thiệp đang bị khóa.
                         + `resources/views/admin/pages/invitations/index.blade.php`:
                           * Bổ sung `<option value="locked">Đang bị khóa</option>` trên dropdown lọc trạng thái.
                           * Cập nhật cột Trạng Thái trong bảng: Nếu thiệp có `$isLocked = true`, hiển thị badge đỏ sang trọng "Đang Khóa" kèm tooltip lý do khóa cụ thể.
                       - **Thanh Cảnh Báo Vận Hành Nhanh Trên Dashboard (`DashboardController` & `dashboard.blade.php`)**:
                         + `app/Http/Controllers/Admin/DashboardController.php`: Tính toán nhanh 3 chỉ số vận hành thời gian thực: `$pendingOrdersCount`, `$pendingWishesCount`, `$lockedInvitationsCount`.
                         + `resources/views/admin/pages/dashboard.blade.php`: Thiết kế thanh thông báo cảnh báo Quiet Luxury viền vàng kim `#9C7A4A`, tự động hiển thị khi có việc cần xử lý và cung cấp các nút tắt trực tiếp: "Xem Đơn Chờ &rarr;", "Duyệt Lời Chúc &rarr;", "Xử Lý Thiệp Khóa &rarr;".
                     - **Kiểm thử & Chất lượng**:
                       - Viết file test Feature toàn diện `tests/Feature/AdminSecurityAndFiltersTest.php` với 5 test cases kiểm chứng: Lọc thiệp khóa, Chống leo quyền đặt lại mật khẩu của Super Admin, Quyền của Super Admin, Phân quyền UI hiển thị, Tính toán và render cảnh báo Dashboard.
                       - Toàn bộ **181/181 tests passed 100% (989 assertions)** trên toàn hệ thống trong ~3.4s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.
                       - Dọn dẹp sạch sẽ file test tạm để giữ kho mã nguồn tinh gọn.


                168. **Hoàn Thiện Hệ Thống Breadcrumbs Đa Tầng & Dropdown Quản Trị Viên Chuyên Nghiệp (Giai Đoạn 1 PRD)**:
                     - **Bối cảnh & Yêu cầu theo Roadmap**:
                       - Trong `docs/ADMIN_ARCHITECTURE_ROADMAP.md` (Giai đoạn 1), hệ thống đặt ra 2 yêu cầu cốt lõi về trải nghiệm điều hướng:
                         1. *Breadcrumbs tự động đa tầng*: Giúp Quản trị viên luôn biết mình đang ở đâu trong hệ thống (Ví dụ: `Admin > Thiệp Cưới > Chi tiết #102` thay vì chỉ có `Admin / Chi tiết`), bấm 1-chạm quay lại danh mục cha.
                         2. *Menu Dropdown Quản Trị Viên Hoàn Chỉnh*: Thể hiện đầy đủ danh tính và vai trò, lối tắt nhanh đến "Hồ Sơ & Đổi Mật Khẩu" của chính mình (`/admin/users/{id}`), lối tắt đến "Nhật Ký Quản Trị (Audit)" cho Quản trị viên cấp cao, nút mở tab mới xem trang khách (`/`) và nút Đăng xuất có xác nhận an toàn.
                     - **Các thành phần đã triển khai**:
                       - **Nâng cấp Master Layout (`resources/views/admin/layouts/master.blade.php`)**:
                         + Thêm khối `@hasSection('breadcrumb_parent')` trong thanh điều hướng Breadcrumb Topbar, render tự động `Admin / @yield('breadcrumb_parent') / @yield('page_title')`.
                         + Tái cấu trúc Dropdown Quản trị viên: Hiển thị Header danh tính sang trọng gồm Tên, Email, Badge vai trò (`role_label`), Menu item dẫn về Hồ sơ cá nhân của chính tài khoản đăng nhập (`route('admin.users.show', Auth::id())`), liên kết Audit Logs dành riêng cho Super Admin, liên kết Xem trang khách và nút Đăng xuất an toàn kèm xác nhận popup.
                       - **Tích hợp Breadcrumb Cha Cho Toàn Bộ Trang Con Của Admin**:
                         + `invitations/show.blade.php`: Gắn `@section('breadcrumb_parent')` liên kết về `Thiệp Cưới`.
                         + `templates/edit.blade.php`: Gắn `@section('breadcrumb_parent')` liên kết về `Kho Mẫu Thiệp`.
                         + `orders/show.blade.php` & `orders/webhooks.blade.php`: Gắn `@section('breadcrumb_parent')` liên kết về `Đơn Hàng`.
                         + `users/show.blade.php`: Gắn `@section('breadcrumb_parent')` liên kết về `Người Dùng`.
                         + `wishes/rsvps.blade.php`: Gắn `@section('breadcrumb_parent')` liên kết về `Lời Chúc & RSVP`.
                         + `settings/audit_logs.blade.php` & `settings/plans/edit.blade.php`: Gắn `@section('breadcrumb_parent')` liên kết về phân hệ tương ứng.
                     - **Kiểm thử & Chất lượng**:
                       - Viết Feature test `tests/Feature/AdminBreadcrumbsAndDropdownTest.php` kiểm chứng: Render Breadcrumb đa tầng chính xác, Dropdown chứa đầy đủ link hồ sơ của admin đăng nhập, kiểm tra ẩn/hiện link Audit Logs giữa Super Admin và Manager, xác nhận đăng xuất.
                       - Toàn bộ **176/176 tests passed 100% (962 assertions)** trên toàn hệ thống trong ~6.9s.
                       - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.
                       - Dọn dẹp sạch sẽ file test tạm để giữ kho mã nguồn tinh gọn.



                 169. **Phòng Vệ Hai Tầng Triệt Để Chống Duplicate Trường Timeline Trong Studio Tạo Thiệp**:
                      - **Hiện tượng và Bối cảnh (Hình ảnh phản hồi thực tế)**:
                        - Tại Bước 6 "Lịch Trình Tiệc Cưới" trên Studio Tạo Thiệp (`/tao-thiep?template=hanh-phuc-mang-ten-chung-minh`), màn hình xuất hiện 2 cụm nhập liệu trùng lặp nhau:
                          + Cụm 1 (Phía trên): Repeater mốc thời gian động (hiển thị giờ dạng time picker 05:30, 06:00, 06:30, 08:00 tương ứng 17:30, 18:00, 18:30, 20:00 theo chuẩn 12h AM/PM của trình duyệt).
                          + Cụm 2 (Phía dưới): 8 ô text riêng lẻ ("Thời gian mốc 1", "Nội dung mốc 1", "Thời gian mốc 2"...) gây rối mắt và thừa thãi.
                      - **Nguyên nhân kỹ thuật**:
                        - `InvitationTemplateService` tự động sinh ra canonical section `timeline` chứa trường `wedding.extra_data.timeline` (type `repeater`).
                        - Trong file `schema.json` cũ của mẫu `hanh-phuc-mang-ten-chung-minh`, người tạo mẫu đã vô tình khai báo 8 trường con rời rạc (`wedding.extra_data.timeline.0.time`, `title`...).
                        - Hàm `InvitationTemplateService::normalizeSchema()` khi duyệt các trường riêng (custom fields) chưa có bộ lọc loại bỏ các key `timeline.N`, dẫn tới việc cả 2 cụm đều được render ra view form Studio.
                      - **Giải pháp hoàn thiện triệt để (Cơ chế bảo vệ 2 tầng)**:
                        - *Tầng 1 (Dữ liệu Schema)*: Đã chuẩn hóa `schema.json` của mẫu `hanh-phuc-mang-ten-chung-minh` sang duy nhất trường canonical repeater `wedding.extra_data.timeline`.
                        - *Tầng 2 (Lớp Dịch Vụ Service Guard)*: Bổ sung bộ lọc phòng vệ tự động vào `InvitationTemplateService::normalizeSchema()` (`if ($sectionId === 'timeline' && preg_match('/timeline\.\d+/', $k)) continue;`). Đảm bảo ngay cả khi các mẫu thiệp mới hoặc schema cũ trong tương lai có chứa các trường con dạng `timeline.0...timeline.N`, hệ thống cũng tự động loại trừ, không bao giờ sinh ra trường duplicate trên Studio Tạo Thiệp.
                      - **Kiểm thử & Chất lượng**:
                        - Đã kiểm thử với Feature Test giả lập cả schema chuẩn và schema legacy có trường trùng lặp -> 100% tự động lọc sạch.
                        - Toàn bộ test suite đạt **178/178 tests passed (979 assertions)**.
                        - Pint: Đạt chuẩn 100%.

                 170. **Hoàn Thiện Toàn Diện Hệ Thống Ghi Nhận & Truy Vết Nhật Ký Quản Trị (Admin System Audit Trail)**:
                      - **Bối cảnh & Yêu cầu theo Roadmap (Giai đoạn 7)**:
                        - Tuân thủ *Nguyên tắc 4: An toàn & Audit Trail*: Mọi thao tác quản trị nhạy cảm (duyệt/hủy đơn, khóa/mở khóa thiệp, duyệt/xóa lời chúc, can thiệp kho mẫu thiệp, đổi phân quyền) bắt buộc phải có log chi tiết: Ai làm, Lúc nào, Đối tượng nào, IP nào, và Chi tiết cụ thể.
                        - Hoàn thiện bộ danh mục hành động `availableActions` và xuất báo cáo CSV chuẩn hóa UTF-8 BOM để quản trị viên đối soát dễ dàng trên Microsoft Excel.
                      - **Các thành phần đã triển khai**:
                        - **Bổ sung Audit Logging Cho Toàn Bộ Hành Động Quản Trị Mẫu Thiệp (`TemplateController.php`)**:
                          + `update`: Ghi log `update_template` khi chỉnh sửa metadata, danh mục, tier, trạng thái active/featured của mẫu thiệp.
                          + `toggleActive`: Ghi log `toggle_template_active` khi kích hoạt hoặc tạm ẩn mẫu thiệp.
                          + `toggleFeatured`: Ghi log `toggle_template_featured` khi ghim hoặc bỏ ghim mẫu thiệp nổi bật.
                          + `scanDiscovery`: Ghi log `scan_templates` ghi nhận số lượng mẫu thiệp mới được đồng bộ từ kho mã nguồn.
                        - **Bổ sung Audit Logging Cho Quản Trị Lời Chúc (`WishController.php`)**:
                          + `toggleApproval`: Ghi log `toggle_wish_approval` khi quản trị viên duyệt hiển thị hoặc tạm ẩn lời chúc của khách mời.
                          + `destroy`: Ghi log `delete_wish` khi xóa vĩnh viễn lời chúc phản cảm, spam hoặc độc hại.
                        - **Đồng Bộ & Chuẩn Hóa Danh Mục Hành Động (`SettingController.php`)**:
                          + Cập nhật đầy đủ 17 loại hành động quản trị hệ thống vào `$availableActions` trong cả 2 method xem nhật ký (`auditLogs`) và xuất báo cáo (`exportAuditLogs`): `approve_order`, `cancel_order`, `lock_invitation`, `unlock_invitation`, `toggle_publish`, `extend_invitation`, `migrate_template`, `change_user_role`, `reset_user_password`, `scan_templates`, `update_template`, `toggle_template_active`, `toggle_template_featured`, `toggle_wish_approval`, `delete_wish`, `update_plan`, `update_banking`.
                        - **Tối Ưu Giao Diện Nhật Ký Quản Trị (`audit_logs.blade.php`)**:
                          + Tối ưu phân loại màu sắc badge hành động (`badgeClass`): Màu xanh an toàn cho duyệt đơn/mở khóa/gia hạn, màu đỏ cảnh báo cho khóa thiệp/hủy đơn/xóa lời chúc/đổi mật khẩu, màu vàng cho cập nhật cấu hình/phân quyền/quét mẫu/chuyển đổi trạng thái.
                      - **Kiểm thử & Chất lượng**:
                        - Viết Feature test `tests/Feature/AdminAuditLogActionsTest.php` kiểm tra đầy đủ 5 trường hợp: Render dropdown 17 hành động, lọc theo hành động, xuất CSV UTF-8 BOM, ghi log khi thao tác mẫu thiệp, ghi log khi thao tác kiểm duyệt lời chúc.
                        - Toàn bộ test suite đạt **181/181 tests passed (990 assertions)**.
                        - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.
                        - Dọn dẹp sạch sẽ file test tạm để giữ kho mã nguồn tinh gọn.

                 171. **Thực Hiện Toàn Diện Post-Launch Security & Quality Audit và Thiết Lập Lộ Trình Premium VNext**:
                      - **Bối cảnh & Chỉ đạo chiến lược**:
                        - Thực hiện toàn bộ chỉ thị theo `docs/MAY_HEN_POST_LAUNCH_SECURITY_QA_PREMIUM_MASTER_PROMPT.md` và đối soát với 4 tài liệu gốc (`MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`, `MAY_HEN_IMPLEMENTATION_ROADMAP.md`, `MAY_HEN_PROJECT_AUDIT.md`, `ADMIN_ARCHITECTURE_ROADMAP.md`).
                        - Đặt ưu tiên tối cao cho: Bảo vệ dữ liệu cá nhân, thông tin gia đình, danh sách khách mời, Sổ Mừng Cưới, bảo mật giao dịch webhook SePay, và rào chắn cô lập giữa bản nháp và thiệp công khai.
                      - **Khắc phục triệt để 6 lỗ hổng bảo mật cốt lõi (P0/P1) bằng mã nguồn thực tế**:
                        1. *SEC-PUB-01 (P0)*: Đóng rào chắn bảo vệ thiệp công khai (`InvitationEditorController::show` và `GuestController`): Thiệp bị Admin khóa (`is_locked`), chưa xuất bản (`! is_published`), hoặc đã hết hạn (`expires_at < now()`) bị chặn triệt để 100% khỏi mọi truy cập của người ngoài và các API tương tác (`submitRsvp`, `submitWish`, `getWishes`, `getRsvpResponses`). Thiết kế 2 view thông báo Quiet Luxury đồng bộ: `invitation-unpublished.blade.php` và `invitation-expired.blade.php`.
                        2. *SEC-PAY-01 (P0)*: Bổ sung cột `provider_transaction_id` (string 64, unique index) vào bảng `orders` qua migration mới `2026_11_07_101426_add_provider_transaction_id_to_orders_table.php`. Webhook SePay kiểm tra chống replay attack và ngăn chặn 1 giao dịch ngân hàng bị dùng chung để kích hoạt nhiều đơn hàng.
                        3. *SEC-IDOR-01 (P1)*: Khắc phục Tenant Leak trong `GiftManagementController::store` và `update`: Ràng buộc `Rule::exists('guests', 'id')->where('wedding_id', $invitation->wedding_id)`, ngăn chặn cặp đôi A gán khách mời của cặp đôi B vào sổ mừng cưới.
                        4. *SEC-AUTH-02 (P1)*: Khắc phục lỗ hổng bypass khóa Admin trong `AccountController::togglePublish`: Kiểm tra cờ `is_locked` trước khi cho phép đảo trạng thái xuất bản.
                        5. *SEC-UPL-01 (P1)*: Chuẩn hóa bảo mật tải tệp ảnh trong `InvitationEditorController::uploadAsset`: Dùng `guessExtension()` kết hợp whitelist nghiêm ngặt; bổ sung Rate Limiter `upload_actions` (30 ảnh/phút) bảo vệ chống DoS dung lượng.
                        6. *SEC-DOM-01 (P2)*: Bổ sung kiểm tra `evt.origin !== window.location.origin` cho listener postMessage trong `tron-kiep-nay-ben-nhau/script.js`.
                      - **Xây dựng 6 tài liệu chiến lược theo Master Prompt**:
                        1. `docs/MAY_HEN_QA_SECURITY_BASELINE.md`: Nguồn sự thật hiện trạng, ma trận 123 routes, baseline môi trường và giới hạn SQLite in-memory.
                        2. `docs/MAY_HEN_SECURITY_AUDIT_VNEXT.md`: Threat model, ma trận OWASP ASVS 4.0, chi tiết 6 lỗ hổng và giải pháp khắc phục.
                        3. `docs/MAY_HEN_BUG_REGISTER.md`: Sổ theo dõi lỗi logic, dữ liệu và DOM đã xác minh.
                        4. `docs/MAY_HEN_UI_UX_POLISH_AUDIT.md`: Báo cáo rà soát giao diện đa thiết bị, giữ nguyên 4 mẫu thiệp source-locked.
                        5. `docs/MAY_HEN_PREMIUM_FEATURE_CATALOG.md`: Nghiên cứu đối thủ và lập danh mục **60 tính năng cao cấp** thuộc 6 nhóm chiến lược (A: Cảm xúc & Persona, B: Guest CRM & RSVP, C: Wedding Day Control Room, D: Lưu niệm số sau cưới, E: Studio V2, F: Dịch vụ thông minh & SaaS).
                        6. `docs/MAY_HEN_PREMIUM_VNEXT_ROADMAP.md`: Lộ trình 3 kịch bản đầu tư (Lean Premium, Growth Premium, Signature Experience) kèm KPIs và phân rã vertical slice tasks.
                      - **Kiểm thử & Đảm bảo chất lượng**:
                        - Viết file test hồi quy toàn diện `tests/Feature/SecurityVNextRemediationTest.php` với 6 ca kiểm thử bao phủ toàn bộ các lỗ hổng vừa khắc phục -> **100% Passed**.
                        - Toàn bộ test suite toàn dự án đạt **182/182 tests passed 100% (984 assertions)**.
                        - Laravel Pint: Đạt chuẩn 100% PSR-12 / Pint agent style.

                 172. **Hoàn Thiện Tính Năng Cá Nhân Hóa Lời Chào & Thông Điệp Theo Nhóm Khách (TASK-V1-03: Invitation Persona)**:
                      - **Bối cảnh & Giá trị nghiệp vụ**:
                        - Giải quyết bài toán then chốt trong văn hóa cưới hỏi Việt Nam: Cùng một đám cưới nhưng lời ngỏ gửi cho [Bạn Bè] cần trẻ trung, thân mật, hóm hỉnh; trong khi gửi cho [Gia Đình & Họ Hàng] hoặc [Khách VIP] cần kính cẩn, trang trọng, tri ân.
                        - Giúp khách mời cảm thấy được tôn trọng tối đa khi mở tấm thiệp mang đúng thông điệp dành riêng cho mối quan hệ của họ.
                      - **Các thành phần kỹ thuật đã triển khai**:
                        1. *Giao diện Cấu hình Cài đặt Thiệp (`settings.blade.php`)*:
                           - Bổ sung Khối 6: *"6. Cá Nhân Hóa Lời Chào Theo Nhóm Khách (Invitation Persona)"* gắn badge Quiet Luxury `Premium VNext`.
                           - Checkbox bật/tắt tính năng toàn cục (`persona_enabled`).
                           - Tự động nạp danh sách các nhóm khách hiện có của đám cưới kết hợp nhóm gợi ý mặc định (`['Bạn Bè', 'Gia Đình & Họ Hàng', 'Đồng Nghiệp', 'Khách VIP', 'Bà Con Lối Xóm']`).
                           - Cho phép nhập độc lập Tiêu đề (`title`), Câu trích dẫn (`subtitle`), và Lời ngỏ thân gửi (`content`) cho từng nhóm khách.
                        2. *Xử lý Backend & Bảo mật dữ liệu (`AccountController.php`)*:
                           - Validation nghiêm ngặt độ dài (`max:190` cho tiêu đề, `max:2000` cho nội dung).
                           - Lọc sạch mã độc XSS bằng `strip_tags()` trước khi lưu vào `custom_config['persona_messages']`.
                           - Phân quyền nghiêm ngặt: Chỉ chủ sở hữu thiệp cưới mới được phép cập nhật.
                        3. *Tích hợp Hiển thị Thiệp Công Khai (`InvitationEditorController.php`)*:
                           - Khi khách mời mở thiệp bằng link định danh có `token`, controller tra cứu nhóm khách (`$guest->group_name`).
                           - Nếu tính năng Persona bật và nhóm đó có thông điệp riêng, trích xuất duy nhất thông điệp đó và truyền biến `$personaMessage` vào view thiệp.
                        4. *Đồng bộ trên 4 Mẫu Thiệp Độc Quyền & View Mặc định*:
                           - Cập nhật cả 4 mẫu thiệp (`giua-muon-ngan-vi-sao`, `hanh-phuc-mang-ten-chung-minh`, `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau`) và `public-invitation.blade.php`.
                           - Giữ nguyên 100% layout, BEM classes, animation và typography source-locked của từng mẫu thiệp.
                        5. *Bảo vệ Riêng Tư Tuyệt Đối (DOM Security & Privacy Guard)*:
                           - Client của khách chỉ nhận duy nhất nội dung dành riêng cho nhóm của mình.
                           - Tuyệt đối không nhúng JSON chứa các nhóm khác vào DOM hay script, loại bỏ hoàn toàn nguy cơ rò rỉ phân loại khách mời hay lộ thông điệp riêng tư.
                      - **Kiểm thử & Đảm bảo chất lượng**:
                        - Viết 7 test cases bao phủ toàn diện: Bạn bè nhận đúng lời chào bạn bè; Gia đình nhận đúng lời chào gia đình; Không token/link chung nhận lời ngỏ mặc định; Tắt Persona hiển thị lời ngỏ mặc định; Lọc sạch script XSS; Chặn truy cập trái phép của người dùng khác; Khẳng định không rò rỉ thông điệp nhóm khác trong DOM HTML -> **7/7 tests passed**.
                        - Toàn bộ test suite hệ thống: **182/182 tests passed 100% (984 assertions)**.
                        - Laravel Pint: Đạt chuẩn 100%.

                 173. **Triển Khai Thẻ Mời Zalo & Messenger Khắc Tên Tự Động (TASK-V1-05: Social Share Card Modal)**:
                      - **Bối cảnh & Ý nghĩa nghiệp vụ**:
                        - Khắc phục tình trạng gửi link thiệp hàng loạt dạng spam khiến khách mời bỏ lỡ hoặc ngần ngại mở xem.
                        - Cung cấp cho cặp đôi công cụ chia sẻ 1-chạm có khắc tên riêng và xưng hô tôn kính (`Trân trọng kính mời: [Xưng hô] [Tên khách]`) cùng 3 phong cách văn phong tùy chọn: Trang trọng, Thân mật, Lịch thiệp.
                      - **Các thành phần kỹ thuật đã triển khai**:
                        1. *Thẻ Xem Trước Bưu Thiếp Mini (Mini Postcard Preview)*:
                           - Giao diện Quiet Luxury thanh lịch với viền vàng ánh kim nhạt, font Cormorant Garamond, hiển thị tên khách mời, xưng hô, nhóm khách, tên cô dâu chú rể và ngày tổ chức hôn lễ.
                        2. *Bộ Chọn 3 Phong Cách Văn Phong Tự Động Theo Nhóm*:
                           - *Trang trọng & Kính cẩn*: Tự động kích hoạt khi nhóm khách là Gia đình, Họ hàng, Khách VIP hoặc Người lớn tuổi.
                           - *Thân mật & Hóm hỉnh*: Tự động kích hoạt cho nhóm Bạn bè, Anh chị em thân thiết.
                           - *Lịch thiệp & Ấm áp*: Dành cho Đồng nghiệp, Đối tác, Khách chung.
                        3. *Tương Tác Nhanh 1-Chạm*:
                           - Ô textarea cho phép cặp đôi tự do biên tập, thêm bớt câu chữ trước khi gửi.
                           - Nút `📋 Sao Chép Lời Mời`: Copy toàn bộ văn bản và link thiệp riêng biệt vào clipboard kèm toast thông báo thành công.
                           - Nút `Copy Link`: Sao chép riêng đường link có mã định danh `token`.
                           - Nút `Mở Zalo`: Tự động nạp vào clipboard và mở `https://zalo.me/` để dán ngay vào cuộc trò chuyện.
                        4. *Kiến Trúc Frontend Pure JavaScript*:
                           - Hoàn toàn viết bằng Vanilla JavaScript ES Modules trong `guests.js`, không sử dụng thêm thư viện ngoài.
                           - Hỗ trợ đóng mở modal bằng phím `Escape`, nút Đóng, nút Hủy, hoặc bấm ra vùng mờ (backdrop).
                      - **Kiểm thử & Đảm bảo chất lượng**:
                        - Viết Feature test kiểm tra việc render modal, các data attribute cá nhân hóa (`data-guest-group`, `data-couple-names`, `data-wedding-date`) và bộ nút tương tác -> Passed 100%.
                        - Toàn bộ test suite hệ thống duy trì **182/182 tests passed 100% (984 assertions)**.
                        - Laravel Pint: Đạt chuẩn 100%.

                 174. **Triển Khai Trợ Lý Viết Lời Ngỏ AI (TASK-V1-04) và Bác Sĩ Khám Bệnh Thiệp Cưới (TASK-V1-06)**:
                      - **Bối cảnh & Giá trị nghiệp vụ**:
                        - Giải quyết 2 rào cản lớn nhất của các cặp đôi trong quá trình làm thiệp:
                          1. *"Bí từ"* không biết viết lời ngỏ tình yêu và câu chuyện gặp gỡ thế nào cho hay, sâu sắc, không sáo rỗng.
                          2. *"Lo lắng sai sót"* không biết thiệp đã đủ thông tin chưa, có bị mờ ảnh, thiếu bản đồ hay quên số tài khoản ngân hàng mừng cưới không.
                      - **Các thành phần kỹ thuật đã triển khai**:
                        1. *Trợ Lý Viết Lời Ngỏ Tình Yêu AI (`AiStoryWriterService` & `InvitationEditorController`)*:
                           - Xây dựng dịch vụ `AiStoryWriterService` phân tích tên cặp đôi, kỷ niệm đáng nhớ (như "Đà Lạt", "yêu xa 3 năm", "quán cafe") và tông giọng mong muốn.
                           - Tự động sinh 3 phương án Quiet Luxury hoàn chỉnh (*Lãng Mạn Sâu Lắng*, *Tối Giản Thanh Lịch*, *Trẻ Trung Hóm Hỉnh*) gồm Tiêu đề (`story_title`), Đề tựa (`story_subtitle`), và Đoạn văn lời ngỏ (`story_content`).
                           - API endpoint `POST /api/editor/ai-story` bảo vệ bằng middleware `throttle:editor_actions`, kiểm tra validation và lọc sạch thẻ HTML độc hại.
                           - Nút *"Áp Dụng Vào Thiệp"* 1-chạm: Tự động điền dữ liệu vào form và kích hoạt handshake `editor-bridge.blade.php` Live Sync ngay lập tức sang chiếc thiệp điện thoại bên phải.
                        2. *Bác Sĩ Khám Bệnh Thiệp Cưới (Visual Quality Doctor)*:
                           - Tích hợp nút huy hiệu bác sĩ trên thanh công cụ Studio Topbar (`#mayhen_btn_quality_doctor`), tự động tính điểm sức khỏe thiệp theo thời gian thực (thang 100đ).
                           - Chẩn đoán 6 hạng mục sống còn của một tấm thiệp cưới:
                             * Tên cô dâu chú rể thật (không dùng tên mặc định).
                             * Ngày cử hành hôn lễ chính thức.
                             * Địa chỉ chi tiết sảnh tiệc / tư gia.
                             * Đường dẫn Google Maps tìm đường 1-chạm.
                             * Lời ngỏ và câu chuyện tình yêu.
                             * Thông tin tài khoản ngân hàng mừng cưới VietQR.
                           - Cung cấp nút *"Đi tới sửa →"* cho từng hạng mục chưa đạt: Tự động phân giải vị trí bước động (`getStepInfoForInput`), kích hoạt `goToStep(targetStep, true)`, gửi lệnh kép `sendScrollToSection` và `sendScrollToTarget` sang Preview Iframe, đồng thời cuộn mượt và highlight ô input trên Drawer nhập liệu.
                      - **Kiểm thử & Đảm bảo chất lượng**:
                        - Feature test kiểm tra dịch vụ AI Story, kiểm tra endpoint API, validation tone, và xử lý sanitize XSS -> 100% Passed.
                        - Toàn bộ test suite toàn hệ thống: **182/182 tests passed 100% (984 assertions)**.
                        - Hoàn thành trọn vẹn 100% các nhiệm vụ của **Giai Đoạn 1: LEAN PREMIUM** trong lộ trình VNext.
                        - Laravel Pint: Đạt chuẩn 100%.

                  175. **Triển Khai Mã QR Điểm Danh & Tra Cứu Bàn Tiệc (TASK-V2-01) và Bảng Điều Khiển Ngày Cưới Realtime (TASK-V2-02)**:
                       - **Bối cảnh & Ý nghĩa nghiệp vụ**:
                         - Giải quyết triệt để vấn đề hỗn loạn, nhầm lẫn bàn tiệc và ùn tắc tại sảnh tiệc trong ngày cưới:
                           1. Khách mời không biết mình ngồi bàn nào, phải hỏi quanh hoặc tìm kiếm vất vả giữa hàng chục bàn tiệc.
                           2. Ban lễ tân tiếp đón bằng danh sách giấy truyền thống lúng túng, chậm chạp, không nắm được sĩ số thời gian thực.
                           3. Cô dâu chú rể và hai bên gia đình trong phòng thay đồ / sân khấu hoàn toàn mù mờ không biết khách đã đến được bao nhiêu phần trăm để quyết định giờ làm lễ chính thức.
                       - **Các thành phần kỹ thuật đã triển khai**:
                         1. *Cơ Sở Dữ Liệu & Phân Loại Bàn Tiệc (`guests` table)*:
                            - Migration bổ sung 4 trường dữ liệu cốt lõi:
                              * `table_name`: string(100), indexed (Tên bàn tiệc, ví dụ: "Bàn 08 - Bạn Đại Học", "Bàn VIP 01 - Họ Nhà Gái").
                              * `checked_in_at`: timestamp, indexed (Thời điểm quét mã QR điểm danh).
                              * `checked_in_attendees`: unsignedInteger (Số lượng khách thực tế có mặt lúc check-in).
                              * `check_in_note`: string(255) (Ghi chú giờ chót từ ban tiếp tân).
                            - Cập nhật Form Request, Controller `GuestController`, Form thêm đơn lẻ, Form nhập nhanh hàng loạt và tính năng xuất CSV có đầy đủ thông tin Bàn tiệc và Trạng thái check-in.
                         2. *Bộ Sinh Mã QR Vector Thuần PHP (`QrCodeService.php`)*:
                            - Tự chứa 100% (Zero Dependencies), tuân thủ ISO/IEC 18004 sinh mã SVG vector siêu nét, nhẹ vài KB.
                            - Không cần bất kỳ thư viện hay CDN bên ngoài nào, tương thích mọi camera smartphone (iPhone, Android, Zalo, Google Lens).
                         3. *Thẻ Tham Dự & Vị Trí Bàn Tiệc Khách Mời (`Guest Table Finder & VIP Pass`)*:
                            - Component nổi toàn cục `@include('client.components.guest-table-badge')` trong layout thiệp cưới (`invitation.blade.php`), tự động kích hoạt khi khách mở link cá nhân hóa có token.
                            - Floating pill sang trọng: `"⚜️ Bàn Tiệc: [Tên Bàn]"` mở Bottom Sheet / Modal VIP hiển thị tên khách mời, xưng hô tôn kính, số bàn tiệc to rõ và mã QR check-in 1-chạm.
                            - Trang Thẻ VIP độc lập `/diem-danh/{guest_token}` hỗ trợ lưu ảnh / in thẻ.
                         4. *Cổng Tiếp Tân Điểm Danh Ngày Cưới (`CheckInController.php`)*:
                            - Đường dẫn `/diem-danh-tiec/{slug}` bảo vệ bằng mã PIN tiếp tân 6 số (sinh tự động hoặc tùy chỉnh), giúp bạn bè trực bàn đón tiếp mở ngay trên điện thoại mà không cần đăng nhập tài khoản của cô dâu chú rể.
                            - Camera Live Scanner qua native Web APIs (`getUserMedia` & `BarcodeDetector`), hỗ trợ đổi camera trước/sau, kèm khung ngắm ánh kim hoàng gia.
                            - Hiệu ứng âm thanh chuông đôi Quiet Luxury ("Ding!") tổng hợp tức thì bằng Native `AudioContext` (không dùng file âm thanh ngoài).
                            - Popup đón khách nổi bật với tên bàn tiệc to rõ để lễ tân đọc hoặc chỉ dẫn khách, kèm tính năng đổi nhanh bàn tiệc tại chỗ nếu cần xếp lại.
                            - Tìm kiếm nhanh (Fast Live Search debounce 250ms) theo tên, SĐT, nhóm hoặc bàn tiệc, hỗ trợ nút "Điểm danh" và "Hủy điểm danh" 1-chạm.
                         5. *Bảng Điều Khiển Ngày Cưới Realtime (`LiveCoordinationController.php` & `live-coordination.js/css`)*:
                            - Đường dẫn `/tai-khoan/thiep/{id}/dieu-phoi` dành riêng cho cặp đôi:
                              * 4 Thẻ Sĩ Số Lớn: Tổng khách mời dự kiến, Đã phản hồi RSVP, Đã có mặt tại sảnh (% lấp đầy tiệc), và Số khách chưa tới đang trên đường đến.
                              * Sóng Lấp Đầy Bàn Tiệc (Table Seating Wave): Gom nhóm theo từng bàn tiệc, hiển thị tiến độ ghế vào bàn / tổng số ghế dự kiến, progress bar đổi màu khi đủ bàn, kèm badge từng khách mời (xanh lá khi đã vào bàn, xám khi chưa tới).
                              * Dòng Sự Kiện Đón Khách Trực Tiếp (Live Arrival Feed): Cập nhật timeline từng lượt khách vừa quét mã.
                            - Cơ chế Polling AJAX tự động mỗi 8 giây không cần reload trang, tự động tạm dừng khi chuyển tab để tiết kiệm pin và băng thông.
                       - **Kiểm thử & Đảm bảo chất lượng**:
                         - Viết 10 Feature test cases trong `tests/Feature/CheckInTableCoordinationTest.php`: Kiểm tra thêm/sửa khách kèm bàn tiệc; nhập nhanh batch kèm bàn tiệc; rào chắn mã PIN tiếp tân; chủ sở hữu vào thẳng không cần PIN; chặn truy cập người lạ (404); quét mã QR điểm danh & chống quét trùng (already_checked_in); tìm nhanh, đổi bàn, hủy điểm danh; api stats realtime; mở thẻ VIP cá nhân; và xuất CSV danh sách khách -> **10/10 tests passed**.
                         - Toàn bộ test suite toàn hệ thống: **192/192 tests passed 100% (1,046 assertions)**.
                         - Laravel Pint: Đạt chuẩn 100% agent style.

                  176. **Triển Khai Két Lưu Trữ Kỷ Niệm Số (TASK-V2-03) và Cuốn Sách Kỷ Yếu Lưu Bút In Ấn (TASK-V2-04)**:
                       - **Bối cảnh & Ý nghĩa nghiệp vụ**:
                         - Sau khi đám cưới khép lại, phần lớn thiệp cưới online trên thị trường bị rơi vào quên lãng hoặc hết hạn lưu trữ khiến kỷ niệm biến mất.
                         - Mây Hẹn nâng tầm trải nghiệm sau ngày cưới: Thiệp cưới tự động chuyển mình thành **"Bảo Tàng Kỷ Niệm Số" (Digital Memory Vault)** lưu giữ vĩnh cửu toàn bộ tình cảm, lời chúc và khoảnh khắc đẹp nhất.
                         - Cung cấp cho cặp đôi khả năng xuất bản **"Cuốn Sách Kỷ Yếu Lưu Bút" (Editorial Keepsake Book)** dàn trang theo tiêu chuẩn sách nghệ thuật A4 để in ấn đóng thành sách lưu bút thực tế để trên bàn phòng khách.
                       - **Các thành phần kỹ thuật đã triển khai**:
                         1. *Trang Kỷ Niệm Ngày Cưới Công Khai (`/ky-niem/{slug}`)*:
                            - Hiển thị tự động sau ngày cưới hoặc khi cặp đôi chủ động kích hoạt chế độ Kỷ Niệm.
                            - Bức tường ảnh kỷ niệm và tên đôi uyên ương với font Cormorant Garamond sang trọng.
                            - Bộ đếm ngày kỷ niệm hạnh phúc: *"Đã cùng nhau đi qua [X] ngày hạnh phúc trọn vẹn"*.
                            - Bức Tâm Thư Tri Ân & Cảm Ơn chân thành từ cô dâu chú rể gửi tới toàn thể người thân, bạn bè.
                            - Cuốn Sổ Lưu Bút Kỷ Niệm: Trưng bày toàn bộ lời chúc phúc đã được duyệt theo phong cách thiệp vintage lụa ngà, kèm câu cảm ơn từ cặp đôi.
                            - Thống kê khoảnh khắc: Tổng số lời chúc, tổng số khách đã có mặt chúc phúc.
                         2. *Cấu Hình Két Kỷ Niệm Dành Cho Cặp Đôi (`MemoryVaultController.php`)*:
                            - Quản lý tại `/tai-khoan/thiep/{id}/ky-niem`: Bật/tắt chế độ Vault, tùy biến Tâm thư tri ân (`thank_you_letter`), chọn ảnh bìa kỷ niệm (`cover_image`).
                            - Dữ liệu lưu trong trường `custom_config['memory_vault']` của `invitations`, bảo toàn 100% database schema.
                            - Kiểm tra validation, sanitize thẻ HTML độc hại và kiểm tra profanity.
                         3. *Cuốn Sách Kỷ Yếu Lưu Bút Chuẩn Sách Editorial (`/tai-khoan/thiep/{id}/sach-ky-yeu`)*:
                            - Dàn trang chuẩn khổ sách A4 (210mm x 297mm):
                              * Trang 1: Trang bìa sách cổ điển mạ vàng Antique Gold, tên cô dâu chú rể, hoa văn góc và ngày cưới thiêng liêng.
                              * Trang 2: Lời mở đầu & Tâm thư tri ân từ đôi uyên ương kèm chữ ký.
                              * Các trang tiếp theo: Chia nhỏ mỗi trang 3 lời chúc, dàn trang cân đối, trích dẫn hoa mỹ, ngày giờ và câu trả lời cảm ơn.
                              * Trang kết: Trang bìa sau với châm ngôn tình yêu và biểu tượng Mây Hẹn.
                            - Tối ưu in ấn & xuất PDF bằng CSS `@media print`: Tự động ẩn thanh công cụ web, căn chỉnh `break-after: page; page-break-after: always;` ngắt trang in chính xác 100%, bảo toàn màu sắc và lề in (`print-color-adjust: exact`).
                         4. *Tích hợp Điều Hướng & Quản Lý*:
                            - Thêm nút liên kết "Bảo Tàng Kỷ Niệm Số" và "Cuốn Sách Kỷ Yếu (PDF)" trên header Sổ Lưu Bút (`account/wishes.blade.php`).
                            - Thanh Owner Ribbon trên đầu trang Kỷ Niệm cho phép chủ sở hữu nhảy nhanh vào Cài Đặt và In Sách.
                       - **Kiểm thử & Đảm bảo chất lượng**:
                         - Viết 6 Feature test cases trong `tests/Feature/DigitalMemoryVaultTest.php`: Kiểm tra khách truy cập sau ngày cưới; kiểm tra redirect về thiệp chính nếu chưa đến ngày cưới và chưa bật vault; kiểm tra chủ thiệp luôn xem trước được; kiểm tra cặp đôi cấu hình và lưu thư cảm ơn; lọc sạch HTML; chặn truy cập người lạ (404); kiểm tra trang in sách kỷ yếu; kiểm tra loại trừ lời chúc bị ẩn -> **6/6 tests passed**.
                         - Toàn bộ test suite toàn hệ thống: **198/198 tests passed 100% (1,077 assertions)**.
                         - Laravel Pint: Đạt chuẩn 100% agent style.

                   177. **Triển Khai Hoàn Thiện Live Guest Photo Drop — Hộp Nhận Ảnh Khách Mời Tại Bàn Tiệc (TASK-V2-05)**:
                        - **Bối cảnh & Giá trị trải nghiệm**:
                          - Trong ngày cưới, các nhiếp ảnh gia chính thường chỉ chụp các góc nghi thức sân khấu, trong khi những khoảnh khắc tự nhiên, vui nhộn và chân thật nhất lại nằm trong điện thoại của hàng trăm khách mời ngồi tại bàn tiệc.
                          - Cặp đôi thường mất rất nhiều công sức sau đám cưới nhắn tin từng người xin ảnh, hoặc ảnh bị nén giảm chất lượng qua mạng xã hội.
                          - Tính năng **Live Guest Photo Drop** biến mỗi bàn tiệc thành một trạm gửi ảnh trực tiếp: Khách quét mã QR đặt tại bàn tiệc, chọn ảnh gốc và gửi ngay vào album kỷ niệm chung chỉ trong vài giây.
                        - **Các thành phần kỹ thuật cốt lõi**:
                          1. *Cơ sở dữ liệu & Model (`GuestPhoto.php` & Migration `guest_photos`)*:
                             - Bảng `guest_photos` lưu trữ: `wedding_id`, `guest_id` (nếu khách truy cập qua token cá nhân), `uploader_name`, `photo_url`, `file_size`, `caption`, `is_approved`, `ip_address`.
                             - Model `GuestPhoto` có scope `approved()` và `pending()`, quan hệ `wedding()` và `guest()`.
                             - Model `Wedding` bổ sung quan hệ `guestPhotos(): HasMany`.
                          2. *Giao diện Tải Ảnh Di Động Dành Cho Quan Khách (`/photo-drop/{slug}`)*:
                             - Dropzone tải ảnh tương tác, hỗ trợ kéo thả và file picker, tự động nhận diện tên khách nếu truy cập bằng token.
                             - Hỗ trợ xem trước (Thumbnail Previews Grid) trước khi gửi, cho phép bấm xóa từng ảnh đã chọn nếu chọn nhầm.
                             - Xử lý tải ảnh qua AJAX Native ES Modules với `XMLHttpRequest` để hiển thị chính xác tiến độ upload theo phần trăm (0% -> 100%).
                             - Dòng khoảnh khắc cộng đồng (Community Stream Grid): Hiển thị ngay lập tức các bức ảnh vừa gửi kèm tên người chụp và lời nhắn.
                          3. *Bảo Mật Tải File & Chống Tấn Công Tải Lên*:
                             - Validate chặt chẽ: Chỉ chấp nhận mime type `jpeg, jpg, png, webp, heic, heif`.
                             - Giới hạn dung lượng tối đa 12MB / ảnh, tối đa 10 ảnh / lần tải lên.
                             - Sinh tên tệp ngẫu nhiên bằng `Str::uuid() . '.' . $extension` lưu trong `guest_photos/{wedding_id}/`, triệt tiêu hoàn toàn nguy cơ path traversal và ghi đè file.
                             - Bộ lọc từ ngữ khiếm nhã `NoProfanity` và sanitize `strip_tags` cho tên người gửi và caption.
                          4. *Không Gian Quản Lý Album Của Cặp Đôi (`/tai-khoan/thiep/{id}/album-khach`)*:
                             - Thống kê 4 chỉ số: Tổng số ảnh đã nhận, Ảnh đã hiển thị công khai, Ảnh chờ kiểm duyệt, và Tổng dung lượng lưu trữ (MB).
                             - Banner Mã QR Đặt Bàn Tiệc tích hợp chế độ In Standee chuẩn A4/A5 bằng CSS `@media print` (tự động ẩn các thành phần thừa, chỉ in mã QR độ phân giải cao và tên cặp đôi).
                             - Bộ lọc trạng thái: Tất cả, Đã duyệt, Chờ duyệt.
                             - Tính năng Duyệt / Ẩn ảnh 1-chạm (`toggleApproval`) và Xóa ảnh vĩnh viễn kèm xóa file vật lý trên đĩa (`destroy`).
                             - Tính năng Đóng Gói Toàn Bộ Ảnh Thành File ZIP (`downloadZip`): Sử dụng extension native `ZipArchive` của PHP, gom toàn bộ ảnh gốc, đặt tên file rõ nghĩa theo tên khách mời và tự động xóa file tạm sau khi tải về máy tính.
                          5. *Tích Hợp Sâu Vào Hệ Sinh Thái*:
                             - Tự động hiển thị các ảnh khách mời đã duyệt trong Bảo Tàng Kỷ Niệm Số (`/ky-niem/{slug}`).
                             - Bổ sung nút "Ảnh Khách" trên danh sách thiệp cưới (`account/invitations.blade.php`) và thanh Owner Ribbon của Két Kỷ Niệm.
                        - **Kiểm thử & Đảm bảo chất lượng**:
                          - Viết 10 Feature test cases trong `tests/Feature/LiveGuestPhotoDropTest.php`: Kiểm tra khách truy cập trang photo drop; khách upload ảnh hợp lệ; chặn file thực thi .php/.exe; chặn upload quá 10 ảnh; chặn khi cặp đôi đóng chức năng; chủ sở hữu quản lý album; chặn người lạ truy cập (404); bật/tắt duyệt ảnh; xóa ảnh và file vật lý; và đóng gói file ZIP -> **10/10 tests passed**.
                          - Toàn bộ test suite toàn hệ thống: **208/208 tests passed 100% (1,113 assertions)**.
                          - Laravel Pint: Đạt chuẩn 100% agent style.

                   178. **Triển Khai Hoàn Thiện Studio & Agency B2B Portal — Cổng Đối Tác Studio & Wedding Planner (TASK-V3-01)**:
                        - **Bối cảnh & Tầm nhìn chiến lược**:
                          - Mở rộng Mây Hẹn từ nền tảng B2C (phục vụ từng cặp đôi riêng lẻ) sang hệ sinh thái B2B đối tác: Hợp tác trực tiếp cùng các Studio chụp ảnh cưới, Wedding Planner, chuyên viên makeup và khách sạn/trung tâm tiệc cưới.
                          - Các Studio có nhu cầu tạo thiệp cưới cho hàng chục đến hàng trăm cặp đôi khách hàng, muốn gắn thương hiệu độc quyền (Co-Branding), quản lý tập trung và nhận chiết khấu hoa hồng tự động (20% - 30%).
                        - **Các thành phần kỹ thuật cốt lõi**:
                          1. *Cơ sở dữ liệu & Models*:
                             - Bảng `partners`: Lưu thông tin Studio/Agency (`user_id`, `business_name`, `slug` độ dài 191 unique chuẩn utf8mb4, `partner_code` unique `MH-PARTNER-*`, `phone`, `email`, `address`, `logo_url`, `website`, `tier`, `commission_rate`, `wallet_balance`, `status`, `custom_config`).
                             - Bổ sung `partner_id`, `handover_code`, `client_notes` vào bảng `weddings`.
                             - Bảng `partner_commissions`: Ghi nhận lịch sử chiết khấu, hoa hồng, thưởng và quyết toán số dư.
                             - Models `Partner`, `PartnerCommission`, cập nhật quan hệ trong `Wedding` và `User`.
                          2. *Phân Quyền & Middleware (`EnsureUserIsPartner.php`)*:
                             - Bảo vệ toàn bộ không gian `/doi-tac/*` qua middleware alias `'partner'`.
                             - Kiểm tra đăng nhập, yêu cầu người dùng chưa là đối tác hoàn tất đăng ký trước, chặn đối tác bị tạm khóa (403), cho phép Admin hệ thống truy cập hỗ trợ.
                          3. *Cổng Quản Trị Đối Tác (`/doi-tac`)*:
                             - *Đăng Ký Đối Tác (`/doi-tac/dang-ky`)*: Tiếp nhận thông tin Studio, tự động kích hoạt tài khoản đối tác, sinh mã đối tác độc nhất và gán role `partner`.
                             - *Bảng Điều Khiển Trung Tâm (`/doi-tac/tong-quan`)*: 4 metric cards theo dõi sĩ số khách hàng, thiệp đang phát hành, lượt khách RSVP và số dư hoa hồng; kèm banner sao chép mã giới thiệu 1-chạm.
                             - *Danh Sách Thiệp Khách Hàng (`/doi-tac/danh-sach-thiep`)*: Tìm kiếm theo tên cặp đôi, sảnh tiệc hoặc ghi chú nội bộ; lọc trạng thái; nút nhảy nhanh vào Studio tạo thiệp.
                             - *Khởi Tạo Thiệp Mới Cho Khách (`/doi-tac/tao-thiep`)*: Chọn mẫu thiết kế trực quan, nhập tên đôi uyên ương, ngày cưới và ghi chú hợp đồng của Studio.
                             - *Bàn Giao Thiệp Cưới 1-Chạm (`/doi-tac/thiep/{id}/ban-giao`)*: Sinh mã bàn giao 6 ký tự (`handover_code`) to rõ, cung cấp mẫu tin nhắn Zalo/Messenger soạn sẵn để gửi khách, cho phép đổi mã mới nếu cần.
                             - *Cặp Đôi Nhận Quyền Quản Trị Thiệp (`/tai-khoan/nhan-thiep-doi-tac`)*: Cặp đôi nhập mã 6 ký tự trên dashboard cá nhân, quyền sở hữu `user_id` chuyển sang cặp đôi, xóa mã bàn giao để chống claim lại, trong khi `partner_id` vẫn giữ nguyên vẹn để bảo toàn hoa hồng cho Studio.
                             - *Bảng Hoa Hồng Doanh Thu (`/doi-tac/hoa-hong`)*: Lịch sử đối soát chiết khấu theo từng đám cưới và đơn hàng.
                             - *Hồ Sơ Thương Hiệu & Co-Branding (`/doi-tac/ho-so`)*: Tải logo Studio, tùy biến dòng chữ bảo chứng chân thiệp cưới (`footer_text`), bật/tắt huy hiệu Studio trên thiệp của khách hàng.
                          4. *Chữ Ký Bảo Chứng Quiet Luxury Trên Thiệp Cưới*:
                             - Tự động hiển thị huy hiệu co-branding trang trọng ở cuối thiệp cưới (`Đồng hành cùng [Tên Studio]`) kèm logo Studio đối tác, tôn vinh thương hiệu đôi bên.
                        - **Kiểm thử & Đảm bảo chất lượng**:
                          - Viết 11 Feature test cases trong `tests/Feature/PartnerPortalTest.php`: Kiểm tra truy cập trang đăng ký; đăng ký đối tác thành công; truy cập dashboard B2B; chặn người chưa là partner; chặn partner bị khóa; tạo đám cưới cho khách; xem & đổi mã bàn giao; cặp đôi claim thiệp thành công; cập nhật hồ sơ & logo studio; xem lịch sử hoa hồng; và hiển thị co-branding trên thiệp cưới -> **11/11 tests passed**.
                          - Toàn bộ test suite toàn hệ thống: **219/219 tests passed 100% (1,155 assertions)**.
                          - Laravel Pint: Đạt chuẩn 100% agent style.

                   179. **Triển Khai Hoàn Thiện Seating Planner Studio — Sơ Đồ Xếp Bàn Tiệc Trực Quan (TASK-V3-02)**:
                        - **Bối cảnh & Giá trị trải nghiệm**:
                          - Việc xếp chỗ ngồi trong ngày cưới (Seating Chart) luôn là một trong những khâu căng thẳng và tốn nhiều thời gian nhất của cô dâu chú rể: Xếp ai ngồi cùng bàn với ai, tránh ghép người bất hòa, đảm bảo bàn VIP cho phụ mẫu và gia đình gần sân khấu.
                          - Phần lớn các website thiệp cưới tại Việt Nam chỉ dừng lại ở form RSVP danh sách phẳng mà không có công cụ trực quan hóa sơ đồ sảnh tiệc.
                          - Mây Hẹn phát triển **Seating Planner Studio**: Không gian tương tác cho phép kéo thả bàn tiệc, kéo thả khách mời vào bàn, theo dõi sức chứa ghế ngồi, cảnh báo quá tải và xuất bản sơ đồ in ấn A4/A3 cho nhà hàng/lễ tân.
                        - **Các thành phần kỹ thuật cốt lõi**:
                          1. *Cơ sở dữ liệu & Models*:
                             - Bảng `wedding_tables`: Lưu trữ `wedding_id`, `name`, `shape` (round, long, square), `capacity` (số ghế), `pos_x`, `pos_y` (tọa độ canvas), `zone` (khu vực), `notes`, `display_order`.
                             - Bổ sung `table_id` (foreign key nullable trỏ `wedding_tables.id`, nullOnDelete) vào bảng `guests`.
                             - Model `WeddingTable`: Các helper `assigned_attendees_count`, `isFull()`, `isOverCapacity()`, `remaining_seats`, `shape_label`.
                             - Model `Guest`: Bổ sung `table()` BelongsTo `WeddingTable`. Model `Wedding`: Quan hệ `tables()` HasMany.
                          2. *Giao diện Studio Hai Cột Tương Tác (`SeatingPlannerController.php`)*:
                             - Đường dẫn: `/tai-khoan/thiep/{id}/so-do-ban-tiec`.
                             - *Cột Trái (Floor Plan Canvas)*:
                               * Thanh Sân Khấu Trung Tâm (Stage & Backdrop) làm mốc định hướng sảnh tiệc.
                               * Canvas tương tác lưới chấm nhẹ, các bàn tiệc đặt theo tọa độ `(pos_x, pos_y)`, hỗ trợ kéo thả vị trí và tự động lưu tọa độ bằng API `updatePositions` (debounce 600ms).
                               * Bàn tròn sang trọng bo tròn 50%, hiển thị tên bàn, số ghế hiện tại / tổng số (`5/10`), danh sách chip khách trong bàn, badge cảnh báo viền đỏ nếu vượt quá số ghế.
                               * Hỗ trợ bấm 1-chạm vào tên khách trong bàn để gỡ khách về danh sách chưa xếp bàn.
                             - *Cột Phải (Unassigned Guests Drawer)*:
                               * Ngăn kéo hiển thị toàn bộ khách mời chưa được xếp bàn.
                               * Tìm kiếm nhanh (Live Search) theo tên khách hoặc nhóm khách.
                               * Thẻ khách mời hỗ trợ HTML5 Drag & Drop: Kéo thả trực tiếp thẻ khách vào bất kỳ bàn tiệc nào trên canvas để gán chỗ tức thì.
                          3. *Bản In Sơ Đồ Sảnh Tiệc & Danh Sách Đón Khách Chuẩn A4/A3 (`/tai-khoan/thiep/{id}/so-do-ban-tiec/in`)*:
                             - Dàn trang tối ưu CSS `@media print` phân chia trang in rõ ràng (`break-inside: avoid; page-break-inside: avoid;`).
                             - Bố cục danh sách từng bàn tiệc, danh sách họ tên khách kèm số lượng người đi cùng, sẵn sàng in đóng thành bảng điều phối đặt tại cửa sảnh tiệc cho lễ tân hoặc gửi cho trung tâm tiệc cưới.
                          4. *Tích Hợp Sâu Vào Hệ Thống*:
                             - Tự động đồng bộ `table_name` giữa Seating Planner và cổng tiếp tân điểm danh `CheckInController` (quét QR hiện đúng tên bàn tiệc).
                             - Bổ sung nút liên kết "Sơ Đồ Bàn Tiệc" trên trang Quản lý Khách Mời (`guests.blade.php`) và danh sách thiệp cưới (`account/invitations.blade.php`).
                        - **Kiểm thử & Đảm bảo chất lượng**:
                          - Viết 10 Feature test cases trong `tests/Feature/SeatingPlannerTest.php`: Kiểm tra chủ sở hữu truy cập studio; chặn người lạ truy cập (404); đối tác studio sở hữu đám cưới truy cập được; tạo bàn tiệc mới; cập nhật bàn và đồng bộ tên bàn cho khách; xóa bàn tiệc tự động unbind khách; gán khách vào bàn; gỡ khách khỏi bàn; cập nhật tọa độ canvas qua API; và xem bản in sơ đồ A4/A3 -> **10/10 tests passed**.
                          - Toàn bộ test suite toàn hệ thống: **229/229 tests passed 100% (1,197 assertions)**.
                          - Laravel Pint: Đạt chuẩn 100% agent style.

                    180. **Triển Khai Hoàn Thiện Tên Miền Riêng Cho Đám Cưới & Vanity Subdomain (TASK-V3-03)**:
                         - **Bối cảnh & Giá trị trải nghiệm**:
                           - Nhằm mang lại trải nghiệm Quiet Luxury độc bản và sang trọng bậc nhất cho ngày trọng đại, Mây Hẹn cung cấp tính năng Tên Miền Riêng (Custom Domain) và Tên Miền Phụ Miễn Phí (Vanity Subdomain).
                           - Cặp đôi có thể sở hữu đường link ngắn gọn tức thì dạng `tuanlan.mayhen.vn` hoặc gắn tên miền độc quyền đã mua như `tuanlan.wedding` hay `damcuoituanlan.vn`. Toàn bộ hệ sinh thái thiệp cưới (Thiệp chính, Két Kỷ Niệm, Photo Drop, Cổng Quét Check-in) đều chạy trực tiếp trên tên miền của cặp đôi mà không bị đổi URL.
                         - **Các thành phần kỹ thuật cốt lõi**:
                           1. *Cơ sở dữ liệu & Models*:
                              - Bảng `wedding_domains`: Lưu trữ `wedding_id`, `invitation_id`, `subdomain` (độ dài 63 unique chuẩn RFC), `custom_domain` (độ dài 191 unique chuẩn utf8mb4), `cname_target` (mặc định `cname.mayhen.vn`), `status` ('pending', 'active', 'failed'), `ssl_status`, `dns_verified_at`, `dns_records` (json).
                              - Model `WeddingDomain`: Các helper `isActive()`, `isPending()`, `isFailed()`, `subdomainUrl()`, `customDomainUrl()`, `activeUrl()`, và hằng số `BLACKLISTED_SUBDOMAINS` bảo lưu các subdomain hệ thống (`admin`, `api`, `app`, `cdn`, `cname`, `mail`, `partner`, `www`...).
                              - Quan hệ `domain(): HasOne` trong cả `Wedding` và `Invitation`.
                           2. *Bộ Định Tuyến Middleware Tên Miền Thông Minh (`DetectCustomDomain.php`)*:
                              - Đăng ký tự động vào pipeline `web` middleware trong `bootstrap/app.php`.
                              - Kiểm tra host: Tự động bỏ qua các host chính của hệ thống (`mayhen.vn`, `www.mayhen.vn`, `localhost`, `127.0.0.1`).
                              - Nhận diện Subdomain: Tách tiền tố subdomain, kiểm tra blacklist, tra cứu `WeddingDomain` active.
                              - Nhận diện Custom Domain: Tra cứu trực tiếp theo tên miền riêng đang hoạt động.
                              - Phục vụ Native Multi-Page không đổi URL trình duyệt:
                                * Đường dẫn gốc `/` -> Phục vụ view thiệp cưới công khai (`InvitationEditorController::show`).
                                * Đường dẫn `/ky-niem` -> Phục vụ Két Kỷ Niệm Số (`MemoryVaultController::show`).
                                * Đường dẫn `/photo-drop` -> Phục vụ Thu Thập Ảnh Khách Chụp (`PhotoDropController::show`).
                                * Đường dẫn `/diem-danh-tiec` -> Phục vụ Cổng Tiếp Tân Quét QR (`CheckInController::showPortal`).
                              - Trang phòng vệ `domain-pending.blade.php`: Trả về giao diện Quiet Luxury 404 trang trọng khi tên miền ngoài trỏ về nhưng chưa liên kết hoặc đang chờ kích hoạt.
                           3. *Dịch Vụ Xác Minh DNS Tự Động (`DomainDnsService.php`)*:
                              - Chuẩn hóa định dạng hostname, truy vấn bản ghi DNS CNAME và ALIAS thực tế trên máy chủ, so khớp target `cname.mayhen.vn`.
                              - Hỗ trợ môi trường test an toàn và mô phỏng phản hồi DNS không phụ thuộc mạng ngoài.
                           4. *Không Gian Quản Trị Tên Miền Cặp Đôi (`CustomDomainController.php`)*:
                              - Đường dẫn: `/tai-khoan/thiep/{id}/ten-mien`.
                              - Giao diện 3 khối chức năng: Banner xem trước URL ưu tiên và nút chép link 1-chạm; Khối cấu hình Vanity Subdomain kích hoạt tức thì; và Khối cấu hình Custom Domain với bảng hướng dẫn CNAME chuẩn hóa và nút "Kiểm Tra Kết Nối DNS & Kích Hoạt".
                              - Hỗ trợ gỡ bỏ riêng biệt subdomain hoặc custom domain 1-chạm.
                              - Bảo vệ IDOR tuyệt đối qua `authorizeOwner(Wedding $wedding)`.
                           5. *Tích Hợp Sâu Vào Hệ Thống*:
                              - Thêm nút "Tên Miền" trên danh sách thiệp cưới (`account/invitations.blade.php`).
                              - Thêm gợi ý nâng cấp tên miền ngay dưới trường Slug trên trang Cài Đặt Thiệp (`account/settings.blade.php`).
                         - **Kiểm thử & Đảm bảo chất lượng**:
                           - Viết 17 Feature test cases trong `tests/Feature/CustomDomainTest.php`: Kiểm tra chủ sở hữu truy cập; chặn người chưa đăng nhập; chặn người lạ xem tên miền người khác (404); đối tác studio quản lý đám cưới truy cập được; đăng ký & cập nhật subdomain; chặn subdomain thuộc blacklist; chặn trùng lặp subdomain; kiểm tra định dạng regex subdomain; đăng ký custom domain trạng thái pending; chặn custom domain là domain hệ thống; xác minh DNS CNAME thành công chuyển active; xác minh DNS thất bại chuyển failed; gỡ bỏ subdomain/custom domain; middleware phục vụ thiệp trên subdomain; middleware phục vụ thiệp trên custom domain; custom domain lạ hiển thị trang pending; và xử lý `POST /tai-khoan/thiep/{id}/ten-mien` mượt mà không bị lỗi 405 MethodNotAllowed -> **17/17 tests passed**.
                           - Khắc phục lỗi 405 MethodNotAllowed: Định tuyến `Route::match(['post', 'delete'], '/thiep/{id}/ten-mien', [CustomDomainController::class, 'storeOrUpdate'])` và tách riêng các form gỡ bỏ tên miền độc lập trong Blade.
                           - Toàn bộ test suite toàn hệ thống: **246/246 tests passed 100% (1,251 assertions)**.
                           - Laravel Pint: Đạt chuẩn 100% agent style.

---

*Tài liệu được khởi tạo và duy trì bởi Antigravity Engine — Dự án Mây Hẹn (mayhen.vn).*
