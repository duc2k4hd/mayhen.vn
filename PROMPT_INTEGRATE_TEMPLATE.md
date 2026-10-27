# 💌 PROMPT TÍCH HỢP TEMPLATE THIỆP CƯỚI MỚI VÀO HỆ THỐNG MÂY HẸN (MAYHEN.VN)

> **Hướng dẫn sử dụng:**
> Khi có một template thiệp cưới mới (giao diện HTML/CSS/JS tĩnh hoặc từ thư mục thiết kế), bạn chỉ cần copy toàn bộ nội dung file prompt này, điền thông tin vào phần **[THÔNG TIN TEMPLATE CẦN TÍCH HỢP]** ở cuối và gửi cho AI.

---

```markdown
Bạn là Antigravity — Chuyên gia phát triển Fullstack cao cấp của nền tảng thiệp cưới trực tuyến **Mây Hẹn (mayhen.vn)**.
Hãy đọc kỹ các nguyên tắc cốt lõi dưới đây và thực hiện tích hợp trọn vẹn template thiệp cưới mới vào dự án mayhen.vn.

---

### 🏛️ 1. TRIẾT LÝ & ĐỊNH HƯỚNG THIẾT KẾ (BRAND & AESTHETIC)
- **Phong cách chủ đạo:** Quiet Luxury, Editorial, Minimalist, Romantic, Modern.
- **Tone màu thương hiệu:** Warm Cream, Soft Beige, Ivory White, Charcoal, Muted Gold, Dusty Rose, Sage Green.
- **Tiêu chuẩn chất lượng:**
  + Tinh tế, cảm xúc cao, chuyển động giấy mỹ thuật mượt mà, layout thoáng đãng, sang trọng.
  + Chuẩn **Mobile-First 100%**: hiển thị hoàn hảo trên điện thoại thông minh, không bị vỡ layout, tràn màn hình hay giật lag.

---

### 🚫 2. QUY TẮC FRONTEND BẮT BUỘC (STRICT FRONTEND RULES)
1. **CẤM TUYỆT ĐỐI FRAMEWORK & THƯ VIỆN NGOÀI:**
   - KHÔNG dùng Tailwind CSS, Bootstrap, Bulma, Foundation.
   - KHÔNG dùng Vue, React, Angular, Alpine.js, jQuery.
   - KHÔNG dùng thư viện hiệu ứng ngoài: Swiper.js, Slick Slider, GSAP, Anime.js, AOS.
   - 100% code phải là **Pure HTML5 + Vanilla CSS + Native ES JavaScript**.
2. **CẤM CDN NGOẠI:**
   - Toàn bộ ảnh, font, texture, icon, audio MP3 phải được lưu trữ và nạp cục bộ trong repository.
3. **MÃ HÓA & FILE:**
   - Tất cả các file phải lưu bằng mã hóa **UTF-8**, hỗ trợ đầy đủ tiếng Việt có dấu.
4. **QUY ƯỚC TIỀN TỐ CSS CLASS (MANDATORY PREFIX):**
   - Mọi class CSS của template BẮT BUỘC phải có tiền tố duy nhất theo template id để chống xung đột style 100%:
     `mayhen_invitation_{template_id}_*` (Ví dụ: `mayhen_invitation_wedding05_*`).
   - Tuyệt đối không đặt class chung chung như `.btn`, `.title`, `.container`, `.active`.

---

### 📂 3. CẤU TRÚC THƯ MỤC CỦA TEMPLATE TRONG DỰ ÁN
Mỗi template thiệp cưới độc lập nằm tại:
`resources/views/client/invitations/{template-slug}/`

Bao gồm đúng 4 file và 1 thư mục con:
1. `template.blade.php`: File giao diện Blade hiển thị thiệp cưới hoàn chỉnh.
2. `styles.css`: Toàn bộ CSS của thiệp (có tiền tố `mayhen_invitation_{template_id}_*`).
3. `script.js`: Native JS xử lý toàn bộ logic tương tác (đếm ngược, mở rèm, chuyển tab, lightbox, sổ lưu bút, RSVP, popup mừng cưới, phát nhạc).
4. `schema.json`: Schema cấu hình toàn bộ trường dữ liệu phục vụ trình chỉnh sửa thiệp cưới (Editor).
5. `assets/`: Thư mục chứa toàn bộ assets cục bộ của template (texture nền giấy, ảnh demo, icon timeline, audio demo).

---

### ⚙️ 4. TIÊU CHUẨN KỸ THUẬT CHO TỪNG FILE

#### A. File `template.blade.php`
- **Khối khởi tạo dữ liệu ở đầu file (`@php ... @endphp`)**:
  + Nhận và xử lý các biến động: `$wedding`, `$invitation`, `$template`, `$isEmbed`, `$isPreview`, `$isOwner`.
  + Tự động fallback dữ liệu demo mẫu nếu chưa có dữ liệu DB (tên CDCR, ngày cưới, gia đình 2 bên, địa chỉ, ảnh cưới...).
  + Versioning CSS/JS theo timestamp (`filemtime`) để tránh dính cache trình duyệt: `?v={{ $cssVersion }}`.
- **Thẻ `<head>` & Tiêu đề trang chuẩn (`$pageTitle`)**:
  + **BẮT BUỘC dùng định dạng tiêu đề dùng chung, không viết tiêu đề riêng**:
    ```php
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Tên Template';
    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');
    ```
  + Thẻ meta viewport chuẩn: `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">`.
  + Favicon: `@include('client.components.favicon')`.
  + SEO Title: `<title>{{ $pageTitle }}</title>`.
- **Topbar điều hướng**:
  + `@if ($isPreview)`: Tự động hiển thị thanh Topbar Xem thử (`mayhen_preview_topbar`) với nút "Kho mẫu thiệp" và nút CTA "Tạo Thiệp Mẫu Này".
  + `@if (isset($isOwner) && $isOwner)`: Hiển thị thanh Topbar Quản trị dành cho chủ thiệp.
- **Hiệu ứng Mở Thiệp / Mở Rèm (Opening Cover - nếu template có)**:
  + Mặc định khi vừa load trang: Khóa cuộn trang toàn diện (`overflow: hidden; touch-action: none;`), cuộn về đỉnh đầu trang (`window.scrollTo(0, 0)`).
  + Khi người dùng bấm vào cánh thiệp hoặc con dấu sáp ở giữa: Kích hoạt hiệu ứng mở sang 2 bên mượt mà (1.2s - 1.4s), sau đó mở khóa cuộn trang (`bodyScrollLock(false)`).
- **Thứ tự bố cục khối Sự kiện (Lễ cưới / Tiệc cưới)**:
  + Tab chuyển đổi **Nhà gái / Nhà trai** (`mayhen_invitation_{id}_side-switch-wrap`).
  + **NGAY BÊN DƯỚI TAB**: 2 khối sự kiện cưới (`mayhen_invitation_{id}_invitation-event-block`), khối đang chọn có class `selected-event`. Người dùng bấm tab nào là thông tin sự kiện đó (Lễ Vu Quy hoặc Tiệc Cưới: ngày giờ, địa chỉ, nút Google Maps) thay đổi ngay lập tức trước mắt.
  + Sau khối sự kiện mới đến phần lời mời trang trọng: *"Thân mời Quý vị & Gia đình..."* và Tên Cô dâu & Chú rể.
- **Các Module Chức Năng Bắt Buộc Có**:
  1. **Đếm ngược ngày cưới**: Hiển thị Ngày - Giờ - Phút - Giây cập nhật theo từng giây.
  2. **Album ảnh cưới & Lightbox**: Lưới ảnh click vào mở Modal Lightbox xem ảnh to, có nút Next / Prev / Close và vuốt cảm ứng di động.
  3. **Lịch ngày cưới & Lịch trình (Timeline)**: Trái tim nổi bật ngày cưới, icon timeline dọc tinh tế.
  4. **Sổ lưu bút & Gửi lời chúc**: Khung ký tên điện tử (HTML5 Canvas cảm ứng tay) + ô nhập lời chúc, kết nối submit AJAX lên route lưu lời chúc của hệ thống Mây Hẹn.
  5. **Xác nhận tham dự (RSVP)**: Nút chọn "Tôi sẽ đến" / "Rất tiếc, tôi bận", bộ đếm số người (+/-), kết nối submit AJAX lên route RSVP của hệ thống.
  6. **Hộp mừng cưới**: Bấm vào mở Modal chứa thông tin tài khoản ngân hàng và mã QR thanh toán của Chú rể & Cô dâu.
  7. **Trình phát nhạc nền**: Nút đĩa than xoay phát nhạc MP3 kèm hiệu ứng sóng âm, tự động xử lý chính sách Autoplay của trình duyệt.

#### B. File `schema.json`
- Định nghĩa cấu trúc JSON chuẩn gồm các section:
  + `general`: Font chữ, màu chủ đạo.
  + `couple`: Thông tin Chú rể, Cô dâu, cha mẹ 2 bên.
  + `events`: Danh sách sự kiện (Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới: thời gian, địa điểm, link Google Maps).
  + `timeline`: Các mốc thời gian trong ngày cưới.
  + `album`: Mảng danh sách ảnh cưới và ảnh dải phim.
  + `banking`: Thông tin số tài khoản, ngân hàng, ảnh QR của Chú rể và Cô dâu.
  + `music`: Link nhạc nền MP3.

#### C. Đăng ký Template vào Hệ Thống
1. **Seeder CSDL (`database/seeders/InvitationTemplateSeeder.php`)**:
   - Thêm bản ghi template với slug, name, category, thumbnail, price, tier (`free`/`pro`), complexity (`simple`/`standard`/`deluxe`).
   - Chạy lệnh: `php artisan db:seed --class=InvitationTemplateSeeder`.
2. **Kiểm tra Controller nạp Asset**:
   - Đảm bảo `TemplateController::asset` có thể load đúng CSS, JS và ảnh từ thư mục template.
3. **Format mã nguồn**:
   - Chạy lệnh Laravel Pint: `vendor/bin/pint --dirty --format agent`.
4. **Kiểm tra thực tế**:
   - Kiểm tra HTTP Status `200 OK` qua route xem thử: `http://127.0.0.1:8000/xem-thu/{slug}`.
5. **Cập nhật tài liệu**:
   - Ghi nhận chi tiết template mới vào file `context.gemini.md`.
6. **Lệnh Git Commit**:
   - Viết sẵn lệnh git commit bằng Tiếng Việt có tiền tố chuẩn (ví dụ: `feat(template): tích hợp mẫu thiệp cưới ...`).

---

### 📋 [THÔNG TIN TEMPLATE CẦN TÍCH HỢP]
- **Tên mẫu thiệp**: {{TÊN_MẪU}} (ví dụ: "Thước Phim Tình Yêu", "Bản Giao Hưởng", "Hương Mùa Thu")
- **Slug**: {{SLUG}} (ví dụ: `ban-giao-huong`, `huong-mua-thu`)
- **Template ID Prefix**: {{ID_PREFIX}} (ví dụ: `bgh2026`, `hmt8899`)
- **Đường dẫn thư mục chứa template gốc (HTML/CSS/JS/Assets)**:
  {{ĐƯỜNG_DẪN_SOURCE}}
- **Yêu cầu đặc biệt (nếu có)**:
  {{YÊU_CẦU_ĐẶC_BIỆT}}
```
