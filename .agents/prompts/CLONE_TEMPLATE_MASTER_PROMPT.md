# MASTER PROMPT — CLONE THIỆP CƯỚI MÂY HẸN CHUẨN MẪU GỐC & CHUẨN HỆ THỐNG (v3.1)

> **Mục tiêu:** Tái dựng **giống hệt 100%** giao diện, ảnh, màu, font, viền, bố cục, animation của trang tham chiếu và **đấu nối chuẩn xác 100% logic code Mây Hẹn (`mayhen.vn`)**.

---

## 0. THÔNG TIN CẦN THAY ĐỔI & XÁC NHẬN (GOM CHUNG 1 NHÓM DUY NHẤT Ở ĐÂY)

> **Chỉ sửa duy nhất tại đây:** Mọi thông tin thay đổi theo từng lần clone (`url_tham_chieu`, `ten_mau`, `slug`, `category`, `tier`, `dau_ra`) đều nằm gọn trong khối này. Toàn bộ phần đặc tả kỹ thuật phía dưới (Mục 1 -> Mục 13) chỉ dùng ký hiệu tham chiếu tự động (`{slug}`, `{ten_mau}`, `{tier}`), **không cần kéo xuống dưới tìm sửa thêm bất kỳ dòng nào**.

```yaml
url_tham_chieu: "https://chungdoi.com/vi/mau-thiep/thanhcung-lam/demo"   # URL mẫu cần clone
ten_mau: "Tiệc Hồng Mừng Duyên Mới"                                     # Tên mẫu hiển thị trên Mây Hẹn
slug: "tiec-hong-mung-duyen-moi"                                  # Slug thư mục & route (kebab-case)
category: "luxury"                                      # luxury (Sang trọng) | minimal (Tối giản7) | romantic (Lãng mạn) | traditional (Truyền thống) | modern (Hiện đại)
tier: "premium"                                            # free | premium | pro | vip
dau_ra: "repo"                                          # repo (tích hợp + seeder) | zip (đóng gói ZIP) | both (cả hai)
```

- **Đường dẫn tự động suy ra từ khối trên:**
  - Thư mục repo: `resources/views/client/invitations/{slug}/`
  - URL xem thử & asset: `/xem-thu/{slug}` · `/mau-thiep/{slug}/assets/`
  - File ZIP (khi `dau_ra` là `zip` hoặc `both`): `{ten_mau}.zip` (thư mục gốc `{slug}/`)

### Đóng vai & Mệnh lệnh tối cao (Source Lock + Chuẩn Mây Hẹn)

Bạn là **Senior Frontend Reverse-Engineering Engineer, Motion & Visual QA Engineer và Laravel Blade Architect** của nền tảng **Mây Hẹn (`mayhen.vn`)**.

1. **Mẫu gốc quyết định toàn bộ thẩm mỹ (Visual Fidelity Lock):** Không tự ý áp bảng màu Sage Green, Deep Forest, Antique Gold, Quiet Luxury, glassmorphism, bo góc, bóng đổ, viền vàng, họa tiết hay font mới nếu trang nguồn không có.
2. **Giữ đúng bố cục, nhịp khoảng trắng, kiểu phân section, ảnh trang trí, lớp phủ, viền, hiệu ứng chữ và chuyển động của mẫu gốc.**
3. **Animation đẹp & trung thực:** Phân tích kỹ toàn bộ animation trên URL tham chiếu (mở phong bì/cánh cửa 3D, hé lộ khi cuộn `scroll reveal`, hoa rơi/kim tuyến/stardust/canvas particle nếu có, lật thiệp/hover/cardtilt/shimmer/lightbox, nhạc đĩa quay) và tái dựng mượt mà bằng **CSS thuần + Vanilla JS ES Modules** (GPU `transform`/`opacity`), không giật khung, không che nút bấm. Nếu mẫu nguồn tĩnh ở một khối, giữ nguyên trạng thái tĩnh; không nhồi hiệu ứng thừa.
4. **Tính năng bắt buộc của Mây Hẹn (Ký tên, RSVP, Lời chúc, VietQR động, Studio Live Sync, 10 Section chuẩn):** Tích hợp hài hòa theo chính ngôn ngữ thiết kế đo từ mẫu gốc (màu nền, font, border, spacing), **không** biến thành khối "AI-style card" lạc quẻ.
5. **Trung thực tuyệt đối & không dùng ảnh chụp màn hình làm giao diện:** Mọi phần tử phải là HTML/CSS/SVG/Canvas tương tác thật. Nếu không truy cập được hoặc thiếu tài nguyên ở nguồn, báo cáo minh bạch — tuyệt đối không che giấu hoặc bịa kết quả QA.

---

## 1. GIAI ĐOẠN A — KHẢO SÁT MẪU THỰC TẾ TRƯỚC KHI CODE

**CẤM viết CSS hoặc Blade trước khi hoàn tất khảo sát thực tế trên trình duyệt.**

### A1. Khảo sát & Chụp màn hình bằng Chromium/Playwright thực tế

1. Mở `url_tham_chieu` (tại Mục 0) trong trình duyệt sạch (chưa cache, không chặn mạng).
2. Quan sát kỹ **Màn mở thiệp (Opening / Envelope / Curtain)** ở trạng thái đóng: đo kích thước, animation chờ (nếu có), nút mở, âm thanh phát khi click.
3. Click thao tác **Mở thiệp**, quay/chụp chuỗi chuyển động mở thiệp (envelope flap, 2 cánh cửa, fade/zoom…), kiểm tra trạng thái bật/tắt nhạc.
4. Cuộn chậm từ đỉnh tới đáy trang để kích hoạt mọi lazy-load và `scroll reveal`, ghi lại toàn bộ hiệu ứng, easing, delay, direction.
5. Chụp screenshot mẫu gốc tại tối thiểu các viewport: **`320×720`**, **`375×812`**, **`390×844`**, **`430×932`**, **`480×900`**, **`1440×900`** (trạng thái đóng, mở thiệp, từng section quan trọng, album lightbox, RSVP/QR modal nếu có).
6. Trích xuất `getComputedStyle` từ DOM thật: `background-color`, `background-image`, gradient stop, `font-family`, `font-size`, `font-weight`, `line-height`, `letter-spacing`, `color`, `padding`, `margin`, `gap`, `border`, `border-radius`, `box-shadow`, `z-index`, `aspect-ratio`, CSS `@keyframes`, thời lượng & timing function.
7. Quét toàn bộ Network trace: ảnh `<img>`, CSS `background-image`, SVG ornament, `@font-face` (`woff2`/`woff`/`ttf`), audio `.mp3`. Phân biệt rõ phần thuộc thiết kế thiệp vs widget/banner/quảng cáo thừa của nền tảng nguồn (bỏ widget quảng cáo/nút "Tạo thiệp miễn phí" của site tham chiếu).
8. **Tách dữ liệu riêng tư khỏi khung thiết kế:** Tên, SĐT, địa chỉ, số tài khoản, ảnh cá nhân cụ thể trên URL tham chiếu phải được thay bằng dữ liệu demo hợp lệ (hoặc dùng ảnh mẫu cho phép sử dụng) và **100% sửa được từ Studio Mây Hẹn**.

### A2. Ghi `reference-audit.md` theo đúng thứ tự nguồn

Ghi chi tiết từng block từ trên xuống dưới (đánh dấu mức tin cậy: `observed` / `measured` / `estimated`) gồm: mục đích, kích thước, màu HEX/RGBA, font thật, box-model, ảnh & tỷ lệ crop, animation trigger/duration/easing, và các tương tác JavaScript thực tế. Tuyệt đối không mô tả mơ hồ bằng tính từ chung chung ("sang trọng", "đẹp").

---

## 2. GIAI ĐOẠN B — NỘI ĐỊA HÓA 100% TÀI NGUYÊN (ZERO EXTERNAL URL)

### B1. Quy tắc tải & chuẩn hóa tài nguyên vào `assets/`

1. Mọi tài nguyên hiển thị (ảnh nền, họa tiết hoa lá/tem sáp/khung viền, ảnh bìa, ảnh gia đình/cặp đôi mẫu, album 10 ảnh, icon SVG, font `.woff2`, file nhạc `.mp3`) **BẮT BUỘC tải về và lưu 100% nội bộ** trong `resources/views/client/invitations/{slug}/assets/`.
2. **CẤM TUYỆT ĐỐI mọi URL tài nguyên ngoại / CDN** trong `template.blade.php`, `styles.css`, `script.js`, `schema.json`. Đặc biệt hệ thống kiểm định của Mây Hẹn (`InvitationTemplateService::inspectTemplateIntegrity()` & `runPreflightAudit()`) chặn cứng các domain sau — **ZERO violation**:
   `ladicdn.com`, `builder.27biggroup.com`, `unsplash.com`, `iili.io`, `imgbb.com`, `cloudinary.com` (và mọi CDN/font server bên ngoài như `fonts.googleapis.com` nếu là mẫu mới — font của mẫu **bắt buộc self-host** trong `assets/fonts/*.woff2`!).
3. *(Ngoại lệ duy nhất cho link hành động)*: Chỉ các thẻ `<a href="...">` dẫn người dùng ra Google Maps (`https://maps.google.com/...`) hoặc Google Calendar (`https://calendar.google.com/...`) được dùng URL ngoài như một liên kết điều hướng.
4. Quy ước đặt tên file sạch, rõ nghĩa bên trong `assets/`:
   - Ảnh giao diện & album: `assets/images/hero-couple.webp`, `assets/images/save-date.webp`, `assets/images/groom-portrait.webp`, `assets/images/bride-portrait.webp`, `assets/images/gallery-01.webp` … `assets/images/gallery-10.webp`, `assets/images/qr-groom.webp`, `assets/images/qr-bride.webp`, kèm `assets/thumbnail.webp` (ảnh đại diện kho mẫu).
   - Font chữ: `assets/fonts/font-heading.woff2`, `assets/fonts/font-script.woff2`, `assets/fonts/font-body.woff2` …
   - Icon & vector: `assets/icons/*.svg` (phải đọc đường dẫn sạch, không chèn `<script>` độc hại trong SVG).
   - Nhạc nền: Đặt tên ưu tiên **`assets/wedding-song.mp3`** (hoặc `assets/audio/wedding-melody.mp3` / `assets/music.mp3`) — đây là các tên file mà Studio Tạo Thiệp ([`editor.blade.php`](file:///d:/Project/Laravel/mayhen.vn/resources/views/client/pages/editor.blade.php)) tự động dò trước tiên khi tạo thiệp mới! Đảm bảo file `.mp3` là file âm thanh hợp lệ phát được (tối ưu < 5MB), **tuyệt đối không tạo file rỗng 0 byte**.
5. Chuyển đổi ảnh raster phù hợp sang **WebP** giữ nguyên tỷ lệ khung gốc (không ép méo hình, không cắt sai `object-position`). Không thay ảnh họa tiết tinh xảo của bản gốc bằng emoji hoặc SVG vẽ lại sơ sài.

### B2. Manifest & Kiểm tra sau tải (`assets-manifest.json`)

- Liệt kê đầy đủ `id`, `type`, `sourceUrl`, `localPath`, `usedInSections`, kích thước, `sha256`, `downloadStatus`, `rightsStatus`, `notes`.
- Rà soát 100% file nguồn (`grep` `http://`, `https://`, `@import`, `ladicdn`, `unsplash`, `googleapis`) để đảm bảo **0 liên kết tải tài nguyên từ bên ngoài** và **0 file tham chiếu bị thiếu (404)**.

---

## 3. GIAI ĐOẠN C — CSS THUẦN, ANIMATION ĐẸP & RESPONSIVE FLOW LAYOUT

### C1. Kiến trúc Stylesheet (`styles.css`) — Pure CSS3, Không Framework

- **CẤM TUYỆT ĐỐI**: Bootstrap, Tailwind CSS, Bulma, jQuery, GSAP, Anime.js, AOS, Swiper.js, Slick Slider, Animate.css CDN, Font Awesome CDN.
- Toàn bộ style phải nằm gọn trong `styles.css` (tuyệt đối **không** nhúng `<style>` inline trong `template.blade.php` ngoại trừ CSS property cực hẹp khi cần thiết theo logic render động như màu chấm dresscode swatch — hoặc dùng SVG/CSS custom property).
- Đường dẫn bên trong `styles.css` (do file được serve qua route `route('templates.asset', ['slug' => $slug, 'path' => 'styles.css'])` tức `/mau-thiep/{slug}/assets/styles.css`):
  - Tài nguyên nằm trong thư mục `assets/` của mẫu (chỉ chấp nhận whitelist extension của `TemplateController::asset`: `.css`, `.js`, `.jpg`, `.jpeg`, `.png`, `.webp`, `.svg`, `.gif`, `.mp3`, `.woff`, `.woff2`, `.ttf`, `.otf`).
  - Trong `styles.css`, dùng đường dẫn `url('fonts/...')` / `url('images/...')` (hoặc `url('assets/fonts/...')` — controller có hỗ trợ tự bỏ tiền tố `assets/` thừa ở [`TemplateController::asset()`](file:///d:/Project/Laravel/mayhen.vn/app/Http/Controllers/Client/TemplateController.php#L438-L444)), và **bắt buộc xác minh 100% file font/ảnh trả HTTP 200 trong Network tab** (vì file font lỗi đường dẫn sẽ trả 404).

### C2. Hệ Prefix BEM `.mayhen_invitation_*` & Isolation Scope

1. Toàn bộ class tự tạo trong `template.blade.php`, `styles.css`, `script.js` **BẮT BUỘC bắt đầu bằng `mayhen_invitation_`** theo BEM (`mayhen_invitation_hero`, `mayhen_invitation_hero__title`, `mayhen_invitation_hero--opened`).
2. **Scope CSS 100% bên trong `.mayhen_invitation_root`**: Không đặt selector trần lên `body`, `h1`, `p`, `button`, `input`, `*` gây xung đột với UI banner/navigation/senior-mode của layout Mây Hẹn (`html` / `body` chỉ style nhẹ background ngoài thông qua class riêng qua `@section('body_class', '...')` nếu cần).
3. **CẤM Lạm Dụng ID & CẤM Class LadiPage cũ**: Tuyệt đối không dùng `#SECTION1`, `#POPUP8`, `.ladi-element`, `.ladi-wraper` hay dùng `.container`, `.btn`, `.active`, `.hidden`. Dùng `id` chỉ cho `label[for]` / `aria-*` / `mayhen_section_*`.

### C3. Bảng Màu Nguồn + Tương Thích Palette Studio (`invitation.blade.php` & `invitation-config.js`)

1. Khai báo `:root` bên trong `styles.css` với giá trị HEX/RGBA **đo chính xác từ trang tham chiếu**:
   ```css
   :root {
     --mayhen-invitation-bg: /* HEX nền chính từ mẫu gốc */;
     --mayhen-invitation-bg-surface: /* HEX nền card/bề mặt từ mẫu gốc */;
     --mayhen-invitation-text: /* HEX chữ chính từ mẫu gốc */;
     --mayhen-invitation-text-muted: /* HEX chữ phụ từ mẫu gốc */;
     --mayhen-invitation-accent: /* HEX màu nhấn chủ đạo từ mẫu gốc */;
     --mayhen-invitation-accent-deep: /* HEX màu nhấn đậm từ mẫu gốc */;
     --mayhen-invitation-gold: /* HEX ánh kim/phụ nếu mẫu gốc có */;
     --mayhen-invitation-heading-font: 'MayhenHeadingSerif', Georgia, serif;
     --mayhen-invitation-script-font: 'MayhenScript', cursive, serif;
     --mayhen-invitation-body-font: 'MayhenBodySans', sans-serif;
   }
   ```
2. Bằng cách sử dụng đúng tên biến `--mayhen-invitation-bg`, `--mayhen-invitation-text`, `--mayhen-invitation-accent`, `--mayhen-invitation-accent-deep`, `--mayhen-invitation-gold`:
   - Ở chế độ mặc định (`$colorTheme` trống), thiệp **hiển thị 100% chính xác màu mẫu gốc**!
   - Khi cô dâu chú rể chọn đổi Bảng Màu trong Studio (`MAYHEN_CHANGE_THEME` qua `invitation-config.js` hoặc `.mayhen_palette_*` trong `layouts/invitation.blade.php`), màu thiệp tự động đồng bộ hài hòa không vỡ tương phản.

### C4. Responsive Flow Layout & Chống Tràn Ngang 0px

- **100% Flow Layout (Flexbox & CSS Grid):** Không xếp cả trang bằng `position: absolute` tọa độ pixel cố định kiểu LadiPage. Cho phép `position: absolute` có giới hạn bên trong container `position: relative` cho họa tiết góc hoa, khung ảnh lồng lớp, tem sáp, overlay chữ.
- Khung chính mặc định khi mẫu gốc là thiệp dọc mobile-first: `.mayhen_invitation_container { width: 100%; max-width: 480px; margin-inline: auto; position: relative; overflow-x: clip; }`. Nếu mẫu gốc trên desktop (`1440px`) có nền full-bleed / khung viền / bố cục 2 cột đặc trưng, tái dựng chính xác trải nghiệm desktop + fallback co giãn xuống mobile mượt mà (`320px`, `375px`, `390px`, `430px`) với **0px tràn ngang (`document.documentElement.scrollWidth <= window.innerWidth`)**.
- Dùng `clamp()`, `%`, `aspect-ratio`, `min()`, `max-width: 100%` trên mọi ảnh/iframe/canvas.

---

## 4. QUY ĐỊNH MÃ NGUỒN MÂY HẸN — BLADE, CONTRACT CONTROLLER & BẢO MẬT

**Đây là contract thực tế đang chạy trên `mayhen.vn` — KHÔNG GIẢ ĐỊNH, PHẢI KHỚP 100%:**

### D1. Kế thừa Layout & Biến Controller Truyền Vào `template.blade.php`

1. **Dòng đầu tiên bắt buộc của `template.blade.php`**: `@extends('client.layouts.invitation')`
2. Layout `client.layouts.invitation` **đã tự động nạp sẵn** (KHÔNG tự `<link>` / `<script>` trùng lặp trong template):
   - `<head>`, CSRF meta token, GTM, Favicon, Smart Focal Point CSS overrides, Palette `.mayhen_palette_*`.
   - `client.components.music-dock`, `client.invitations.partials.editor-bridge`, `assets/client/js/common/invitation-config.js`, `native-crafting-presenter.js`.
   - Biến JS toàn cục sẵn có: `window.__MAYHEN_IS_EMBED`, `window.__MAYHEN_IS_PREVIEW`, `window.__MAYHEN_CSRF_TOKEN`.
3. **Các `@section` & `@push` hợp lệ trong `template.blade.php`:**
   - `@push('fonts')` (nếu cần preload font local `.woff2` từ `route('templates.asset', ...)`)
   - `@push('styles')`: `<link rel="stylesheet" href="{{ route('templates.asset', ['slug' => $slug, 'path' => 'styles.css']) }}?v=1.0.0">`
   - `@section('body_class', '...')` (tùy chọn, không xung đột với `mayhen_embed_mode` / `mayhen_palette_*`)
   - `@section('content')`: bọc toàn bộ thiệp trong `<div class="mayhen_invitation_root ..." ...>`
   - `@section('modals')`: đặt Lightbox / QR Zoom modal (nếu đặt ngoài `.mayhen_invitation_container`, nhưng vẫn giữ `.mayhen_invitation_*` prefix!)
   - `@push('scripts')`: **CHỈ** nạp `<script type="module" src="{{ route('templates.asset', ['slug' => $slug, 'path' => 'script.js']) }}?v=1.0.0"></script>`. **TUYỆT ĐỐI KHÔNG viết `<script>` hay `<style>` inline trong `template.blade.php`**. Mọi thông số PHP cần truyền sang JS phải gắn qua `data-*` trên `.mayhen_invitation_root`:
     ```blade
     <div
         class="mayhen_invitation_root {{ $rootStateClasses }}"
         data-is-embed="{{ $isEmbed ? 'true' : 'false' }}"
         data-is-preview="{{ $isPreview ? 'true' : 'false' }}"
         data-wedding-date="{{ $weddingDate->format('Y-m-d') }}"
         data-target-time="{{ $countdownTime }}"
         data-rsvp-endpoint="{{ $rsvpSubmitUrl ?? '' }}"
         data-wishes-endpoint="{{ $wishSubmitUrl ?? '' }}"
         data-signature-endpoint="{{ $signatureEndpoint }}"
         data-csrf-token="{{ csrf_token() }}"
         data-guest-name="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
     >
     ```
4. **Danh sách biến Blade thật truyền vào view bởi `TemplateController::preview()` và `InvitationEditorController::show()`:**
   - Luôn có: `$template`, `$wedding`, `$invitation` (`null` khi xem demo `/xem-thu/{slug}` trừ khi có `?edit=`), `$wishes`, `$wishSubmitUrl`, `$rsvpSubmitUrl`, `$isEmbed` (vẫn phòng vệ thêm bằng fallback: `$isEmbed = $isEmbed ?? request()->boolean('embed') || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);`), `$isCuratedSeven`.
   - Có thêm ở trang thiệp công khai (`/thiep/{slug}`): `$guest` (có `$guest->name`, `$guest->salutation`), `$smartEvents`, `$ceremonyScope`, `$showBrideCeremony`, `$showGroomCeremony`, `$showMainParty`, `$tailoredGroupNotice`, `$currentRsvp`, `$myWish`, `$isOwner`, `$personaMessage`, `$isRsvpClosed`, `$isRsvpLate`.
   - **Endpoint Chữ ký (Signature):** Controller KHÔNG truyền sẵn `$signatureEndpoint`, bạn tự tính trong Blade:
     ```php
     $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template)) || !isset($invitation) || !$invitation->id;
     $signatureEndpoint = (!$isPreview && isset($invitation) && $invitation->id)
         ? route('invitation.signature.save', $invitation->id)
         : '';
     ```
   - **Escape XSS 100%:** Luôn dùng `{{ ... }}` cho dữ liệu người dùng; tuyệt đối không `{!! ... !!}` lên text nhập từ người dùng.

### D2. Helper `$resolveAsset(...)` Bắt Buộc (Phòng vệ ảnh lỗi, Localhost Storage, SVG XSS & Template Fallback)

Mọi ảnh/audio từ `$wedding` / `$extraData` PHẢI đi qua `$resolveAsset`:

```php
$slug = '{slug}'; // Giá trị slug khai báo tại Mục 0
$resolveAsset = function (string $relativePath, ?string $overrideUrl = null) use ($slug): string {
    $cleanRelative = ltrim(preg_replace('#^/?mau-thiep/[^/]+/assets/#', '', $relativePath), '/');
    $cleanRelative = ltrim(preg_replace('#^assets/#', '', $cleanRelative), '/');
    $fallbackUrl   = "/mau-thiep/{$slug}/assets/{$cleanRelative}";

    $override = trim((string) ($overrideUrl ?? ''));
    if ($override === '' || str_starts_with($override, 'blob:')) {
        return $fallbackUrl;
    }

    // Cho phép Data URI ảnh an toàn (kể cả SVG base64 từ VietQrService), chặn inline HTML/script
    if (str_starts_with($override, 'data:')) {
        if (preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,[A-Za-z0-9+/=]+$#i', $override)
            || preg_match('#^data:image/svg\+xml;base64,[A-Za-z0-9+/=]+$#i', $override)) {
            return $override;
        }
        return $fallbackUrl;
    }

    // Hỗ trợ URL /storage/ hoặc /uploads/ nội bộ kể cả trên localhost / 127.0.0.1
    if (preg_match('#/(storage|uploads)/(.+)$#', $override, $storageMatch)) {
        $storageRelative = $storageMatch[1] . '/' . $storageMatch[2];
        if (file_exists(public_path($storageRelative))) {
            return '/' . $storageRelative;
        }
    }
    if (preg_match('#^/?(storage|uploads)/#', $override)) {
        $cleanStorage = ltrim($override, '/');
        if (file_exists(public_path($cleanStorage))) {
            return '/' . $cleanStorage;
        }
    }

    // Chặn toàn bộ domain bên thứ 3 bị cấm / hotlink ngoài
    if (preg_match('#^https?://#i', $override)) {
        return $fallbackUrl;
    }

    $cleanOverride = ltrim(preg_replace('#^/?mau-thiep/[^/]+/assets/#', '', $override), '/');
    $cleanOverride = ltrim(preg_replace('#^assets/#', '', $cleanOverride), '/');
    $localFile     = resource_path("views/client/invitations/{$slug}/assets/{$cleanOverride}");
    if (file_exists($localFile)) {
        return "/mau-thiep/{$slug}/assets/{$cleanOverride}";
    }

    return $fallbackUrl;
};
```

**Lưu ý khi chuẩn hóa `$extraData` từ PHP:**
Khi ép giá trị `null` sang `''`, **BẮT BUỘC loại trừ** các khóa mảng và URL:
```php
$extraData = is_array($wedding->extra_data ?? null)
    ? $wedding->extra_data
    : (json_decode((string) ($wedding->extra_data ?? '{}'), true) ?? []);
foreach ($extraData as $k => $v) {
    if ($v === null && !in_array($k, ['gallery', 'timeline', 'dresscode_colors'], true) && !str_ends_with($k, '_url') && !str_ends_with($k, '_image')) {
        $extraData[$k] = '';
    }
}
```
> **Không làm bước loại trừ này sẽ khiến `gallery`, `timeline`, `dresscode_colors` bị hỏng hoặc biến `null` ảnh thành chuỗi `''`!**

---

## 5. MÀN MỞ THIỆP, NHẠC NỀN & PHÒNG VỆ STUDIO EMBED (`$isEmbed`, `$isCuratedSeven`)

### E1. Màn Mở Thiệp (Opening Screen) & Animation Mượt Mà

- Dựng **đúng 100%** kiểu màn mở thiệp của mẫu gốc (phong bì gập nếp, hai cánh cửa khép, cánh hoa tách màn, hoặc thẻ chạm nhẹ mở thiệp…).
- Class chuẩn tối thiểu: `mayhen_invitation_opening`, `mayhen_invitation_open_btn`, `mayhen_invitation_card_wrapper`.
- **Khóa cuộn an toàn:** Chỉ khóa cuộn trên `.mayhen_invitation_root` hoặc `body` **khi `!$isEmbed`** và thiệp đang đóng; **phải gỡ khóa cuộn ngay lập tức** ở mọi nhánh: khi người dùng nhấn mở, khi chuyển cảnh kết thúc (`transitionend` + `setTimeout` an toàn), và nếu script gặp lỗi. Sau khi mở xong, màn opening PHẢI có `pointer-events: none` + `hidden` / `display: none` để không chắn thao tác chạm bên dưới.

### E2. Nhạc Nền — Tuyệt Đối Không Phát Chồng Với `music-dock.blade.php`

`resources/views/client/components/music-dock.blade.php` được layout tự động nạp ở góc trên trái (`#mayhenMusicDock`). Nó chỉ tự ẩn (`dock.style.display = 'none'`) khi tìm thấy một `<audio>` khác có class `.mayhen_invitation_audio` hoặc thuộc tính `[data-mayhen-audio]`:

- **BẮT BUỘC** thẻ `<audio>` riêng của mẫu phải có đúng class và data-sync:
  ```blade
  <audio preload="none" loop playsinline class="mayhen_invitation_audio" data-mayhen-audio data-sync-target="music_url" @if($musicUrl) src="{{ $musicUrl }}" @endif></audio>
  ```
- Đặt nút bật/tắt nhạc đúng thiết kế + vị trí trên trang gốc (nếu trang gốc không có nút nhạc, giữ nút gọn thanh lịch ở góc không che chữ/nút chính và tránh trùng tọa độ với UI layout).
- Xử lý `audio.play()` bằng Promise `.catch(() => { ... })` chuẩn ES Module (trình duyệt có thể chặn autoplay trước tương tác — **luôn bắt sự kiện chạm nút mở thiệp** để kích hoạt nhạc hợp lệ, kèm đồng bộ trạng thái icon & đĩa quay CSS).

### E3. Phòng Vệ Nhúng Studio Editor & Showcase Card (`$isEmbed = true` hoặc Curated Crafting)

Kiểm tra biến phòng vệ thống nhất trong `template.blade.php`:
```php
$invConfig = is_array($invitation->custom_config ?? null)
    ? $invitation->custom_config
    : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?? []);
$isCuratedSeven = !empty($isCuratedSeven) || !empty($invConfig['is_curated_seven']) || !empty($invConfig['is_curated']);
$autoOpened = $isEmbed || ($isCuratedSeven && request()->boolean('crafting'));
```
Khi `$autoOpened === true`:
1. **Ẩn hoàn toàn Opening:** Thêm class `--hidden` và fallback `style="display: none !important;"` lên `.mayhen_invitation_opening` (hoặc `@if(!$autoOpened)` không render opening để sạch DOM và giữ `data-mayhen-section="hero"` hiện ngay từ pixel đầu tiên!).
2. **Thẻ nội dung hiển thị tức thì:** `.mayhen_invitation_card_wrapper` ở trạng thái mở (`display: block !important; opacity: 1 !important;`). Không gắn bất kỳ class khóa cuộn nào lên `html` / `body`.
3. **Không đợi cuộn mới hiện chữ/ảnh:** Trong `script.js`, khi `isEmbed === true` (hoặc trên các phần tử đã nằm trong viewport), gắn ngay class hiển thị (`.is-visible` / `.mayhen_invitation_revealed`) để ảnh xem trước trong Studio & Showcase Card trang chủ **không bao giờ bị màn hình trắng hoặc tàng hình**.
4. **Không tự phát nhạc trong `$isEmbed`.**

### E4. Tương thích Chế Độ Tinh Tuyển 7 Section (`$isCuratedSeven`)

Để vượt qua kiểm định `curated_compatible` của `InvitationTemplateService::inspectTemplateIntegrity()`:
- Bọc 3 khối sau bằng `@if(!$isCuratedSeven) ... @endif`:
  1. Section 7 (`dresscode` — Quy Định Trang Phục)
  2. Section 9 (`gift` — Hộp Mừng Cưới & VietQR)
  3. Phần chân trang kết thúc đầy đủ (`ending` footer) — kèm một block `@if($isCuratedSeven)` ngắn gọn tri ân cuối thiệp khi bật chế độ 7 section (hoặc ẩn phần ending footer dư thừa).

---

## 6. TÍCH HỢP CHỨC NĂNG BẮT BUỘC ĐÚNG LOGIC BACKEND MÂY HẸN

**Mọi mẫu thiệp PHẢI có đủ 4 khối chức năng sau chạy thật 100% với Laravel Backend, kế thừa nguyên ngôn ngữ thiết kế đo từ mẫu gốc:**

### F1. Nguyên Tắc Hình Ảnh Tuyệt Đối Cho Ký Tên (Signature) & RSVP (Luật #8)

- **CẤM TUYỆT ĐỐI** chèn ảnh chụp cặp đôi hoặc ảnh stock người vào bất kỳ phần nào của **Sổ Ký Tên (`Signature`)** và **Xác Nhận Tham Dự (`RSVP`)** (cả `<img ...>`, CSS `background-image`, pseudo-element `::before`/`::after`, sticker người, hay hình nền canvas ký tên).
- Chỉ dùng nền giấy/màu thuần, đường viền thanh mảnh, SVG line-art hoa văn/tem niêm phong sáp, monogram chữ cái tên cô dâu chú rể, hoặc hiệu ứng hạt/vệt sáng CSS thuần.
- Nếu trang tham chiếu có đặt ảnh người bên trong khung RSVP: bắt buộc lược bỏ bức ảnh người đó trong vùng RSVP/Signature để khách mở thiệp của chính họ không bị thấy ảnh người lạ, và ghi nhận ngoại lệ này vào `visual-audit.md`.

### F2. Form Xác Nhận Tham Dự — RSVP (`SubmitRsvpRequest`)

- Action tới endpoint `data-rsvp-endpoint` (`$rsvpSubmitUrl`).
- Nếu `$isRsvpClosed` là `true`: hiển thị thông báo đóng RSVP (`$rsvpClosedNotice ?? 'Đã hết thời gian xác nhận tham dự.'`) và vô hiệu hóa submit.
- **Exact JSON Payload** khớp 100% quy tắc validate của [`App\Http\Requests\SubmitRsvpRequest`](file:///d:/Project/Laravel/mayhen.vn/app/Http/Requests/SubmitRsvpRequest.php):
  ```js
  const payload = {
    guest_name: guestName, // required|string|max:100
    attending_status: status, // required|in:attending,declined,uncertain
    adults_count: status === 'attending' ? Number(adultsCount) : 0, // integer 0..10
    children_count: status === 'attending' ? Number(childrenCount) : 0, // integer 0..10
    wishes: message // nullable|string|max:1000
  };
  ```
- Header bắt buộc: `'Content-Type': 'application/json'`, `'Accept': 'application/json'`, `'X-CSRF-TOKEN': csrfToken` (lấy từ `root.dataset.csrfToken` hoặc `meta[name="csrf-token"]`).
- Pre-fill ô tên từ `$guest->name` nếu có (`$guestName`). Khi gửi thành công (`res.ok && result.success === true`), hiển thị phản hồi rõ ràng (`result.message`), reset hợp lý và tránh bấm đúp (`isSubmitting`). Nếu trả về 403/422 (ví dụ chủ thiệp đang tự test `$invitation->isOwner()` hoặc validate lỗi), hiển thị nguyên văn `result.message` thật — không tự giả "Thành công!".

### F3. Sổ Lưu Bút Lời Chúc (`SubmitWishRequest`)

- Endpoint: `data-wishes-endpoint` (`$wishSubmitUrl`).
- **Exact JSON Payload** khớp [`App\Http\Requests\SubmitWishRequest`](file:///d:/Project/Laravel/mayhen.vn/app/Http/Requests/SubmitWishRequest.php):
  ```js
  const payload = {
    name: guestName, // required|string|max:100
    message: wishMessage // required|string|max:1000
  };
  ```
- Server Blade render danh sách lời chúc bằng `@foreach($wishes ?? [] as $wish)` hiển thị `{{ $wish->name }}`, `{{ $wish->message }}` và nếu `!empty($wish->reply)` thì hiển thị khối phản hồi của Cô dâu & Chú rể.
- Client DOM insert sau khi `fetch` thành công: **tuyệt đối dùng `document.createElement` + `.textContent`**, **CẤM `innerHTML` với dữ liệu khách nhập**.

### F4. Sổ Ký Tên Điện Tử Canvas (`WeddingInteractiveEndingController::saveSignature`)

- Hỗ trợ Pointer Events (`pointerdown`, `pointermove`, `pointerup`, `pointercancel`) mượt mà cho cả ngón tay mobile, chuột và bút stylus (`touch-action: none` trên `<canvas>`).
- Xử lý chính xác `devicePixelRatio` và **lưu đệm nét vẽ (`ImageData` hoặc offscreen stroke list) để không mất nét khi resize/rotate màn hình**.
- Cung cấp đủ 4 nút rõ nghĩa: **Xóa chữ ký**, **Xem trước** (hiển thị ảnh PNG xem trước + nút tải PNG), **Lưu chữ ký**.
- Khi nhấn **Lưu chữ ký**:
  - Kiểm tra canvas không rỗng (`hasDrawn`). Kiểm tra tên người ký (lấy từ ô nhập tên ký tên, fallback sang tên ô RSVP/guest nếu tiện, bắt buộc có tên trước khi gửi).
  - Xuất `const dataUrl = canvas.toDataURL('image/png')`.
  - Nếu `$signatureEndpoint === ''` (đang ở trang xem thử `/xem-thu/{slug}` chưa tạo thiệp): hiển thị bản xem trước PNG + cho phép tải ảnh PNG và thông báo trung thực: `"Chế độ xem thử: Chữ ký đã sẵn sàng tải PNG. Khi bạn tạo và xuất bản thiệp, chữ ký khách mời sẽ được lưu trực tiếp vào Sổ Ký Tên!"`.
  - Nếu có `$signatureEndpoint` (`route('invitation.signature.save', $invitation->id)` -> `/api/thiep/{id}/so-ky-ten/luu-chu-ky`):
    Gửi `fetch(signatureEndpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ guest_name: signerName, signature_data: dataUrl }) })` — **đúng 2 key `guest_name` và `signature_data`**.

### F5. Hộp Mừng Cưới & VietQR Động (`VietQrService::resolveQrImage`)

- Không nhúng ảnh QR cứng của người lạ nếu khách đã đổi số tài khoản trong Studio. Trong `template.blade.php` bắt buộc gọi:
  ```php
  $groomBankName    = $extraData['groom_bank_name'] ?? 'Vietcombank';
  $groomBankAccount = $extraData['groom_bank_account'] ?? '1029384756';
  $groomAccountName = $extraData['groom_bank_owner'] ?? ($extraData['groom_account_name'] ?? 'NGUYEN MINH TUAN');
  $groomQrRaw       = $extraData['qr_groom_image'] ?? ($extraData['groom_qr_url'] ?? null);
  $qrGroomUrl       = \App\Services\VietQrService::resolveQrImage(
      $groomQrRaw,
      $groomBankName,
      $groomBankAccount,
      'Mung cuoi ' . $groomName,
      $resolveAsset('assets/images/qr-groom.webp', $groomQrRaw),
      '1029384756'
  );
  ```
  *(Thực hiện tương tự cho Cô dâu: `bride_bank_name`, `bride_bank_account`, `bride_account_name`, `qr_bride_image`)*.
- Nút **"Sao chép STK"**: dùng `navigator.clipboard.writeText(...)` (fallback `execCommand('copy')` tạm) và đổi label nút thành `"Đã sao chép!"` trong 2 giây — **CẤM dùng `alert()`**!

### F6. Vị trí đặt các Section Mây Hẹn chưa có trong trang nguồn

- Bảo toàn thứ tự & layout tất cả section có sẵn trên trang gốc.
- Nếu trang gốc thiếu Ký tên hoặc Timeline/Dresscode: chèn bổ sung ở vị trí hợp lý nhất theo **10 Section chuẩn**, thiết kế giống hệt nền, font, viền, padding của các section liền kề và ghi rõ vào `reference-audit.md` / `visual-audit.md`.

---

## 7. KHUNG 10 SECTION CHUẨN & LIVE SYNC VỚI STUDIO EDITOR V2 (`editor-bridge.blade.php`)

### G1. Bắt buộc đủ 10 Section chuẩn (`InvitationTemplateService::CANONICAL_SECTION_ORDER`)

Mọi mẫu thiệp trong Mây Hẹn phải gắn thuộc tính `data-mayhen-section="..."` cho đủ **10 nhóm logic chuẩn** để Stepper của Studio cuộn tới chính xác, `invitation-config.js` ẩn/sắp xếp section được, và `inspectTemplateIntegrity()` chấm đạt 10/10:

| # | Canonical `id` | Giá trị `data-mayhen-section` khuyến nghị | Ý nghĩa |
|---|---|---|---|
| 1 | `hero` | `hero` (hoặc `hero,couple`) | Trang bìa chính: tên Cô dâu Chú rể, loại lễ, ngày cưới, slogan, ảnh bìa 3:4 |
| 2 | `story` | `story` | Lời ngỏ & Chuyện tình yêu (ảnh kỷ niệm, câu trích dẫn, lời tâm sự) |
| 3 | `parents` | `parents` (hoặc `parents,groom_family,bride_family`) | Song thân hai họ (ông bà nội ngoại, địa chỉ tư gia) & Chân dung + tự bạch Cô dâu / Chú rể |
| 4 | `countdown_calendar` | `countdown_calendar` | Đồng hồ đếm ngược thời gian thực + Lịch tháng (có nút Thêm vào Google Calendar) |
| 5 | `events` | `events` | Thông tin chi tiết Lễ Vu Quy / Lễ Thành Hôn / Tiệc Cưới Chính + Google Maps |
| 6 | `timeline` | `timeline` | Lịch trình các mốc thời gian hôn lễ (`timeline` repeater tối đa 6 mốc) |
| 7 | `dresscode` | `dresscode` | Quy định bảng màu trang phục (`@if(!$isCuratedSeven)`) |
| 8 | `album` | `album,gallery` | Album 10 ảnh cưới kèm Lightbox phóng to mượt mà |
| 9 | `gift` | `gift` | Hộp mừng cưới, STK & mã VietQR động của Chú rể / Cô dâu (`@if(!$isCuratedSeven)`) |
| 10 | `ending` | `ending,rsvp,wishes` | Sổ Ký Tên Canvas + Form RSVP + Sổ Lưu Bút + Lời Cảm Ơn kết thiệp (footer bọc `@if(!$isCuratedSeven)`) |

> **Giữ nguyên layout nguồn:** Bạn vẫn giữ đúng thiết kế, tỷ lệ và cách sắp xếp thị giác của trang tham chiếu; chỉ map chuẩn các `data-mayhen-section` và `data-sync-target` lên đúng phần tử HTML tương ứng. Nếu 2 khối trên nguồn nằm cạnh nhau hoặc gộp nhẹ, có thể đặt Wrapper có `data-mayhen-section="..."` lồng gọn gàng miễn không phá CSS layout gốc.

### G2. Bảng `data-sync-target` đầy đủ (Gắn lên MỌI lần xuất hiện trong DOM)

`resources/views/client/invitations/partials/editor-bridge.blade.php` đồng bộ theo `[data-sync-target="..."]` qua 2 kênh song song (`window.__mayhenSyncInput` và `MAYHEN_SYNC_INPUT` `postMessage`). **Phải gắn đủ cho mọi vị trí hiển thị (trừ màn Opening có thể đồng bộ riêng nếu hiển thị tên/ngày), không chỉ lần xuất hiện đầu tiên:**

- **Section 1 (`hero`):** `ceremony_type`, `groom_name`, `bride_name`, `couple_names` *(bên trong chứa `<span data-sync-target="groom_name">` và `<span data-sync-target="bride_name">` để khi cập nhật từng tên không ghi đè hỏng cấu trúc!)*, `wedding_date` / `wedding_date_digits`, `wedding_day`, `wedding_month`, `wedding_year`, `wedding_weekday`, `wedding_lunar_date`, `cover_photo_url` (alias `hero_photo_url`), `slogan`.
  - *Monogram chữ cái đầu:* gắn `data-monogram-role="groom"` và `data-monogram-role="bride"` lên vòng tròn/con dấu tên viết tắt — `editor-bridge.blade.php` tự động lấy chữ cái đầu viết hoa khi nhập tên!
  - *Wax Seal (nếu có):* gắn `data-mayhen-wax-seal` và `data-mayhen-wax-monogram` để `invitation-config.js` đổi màu/ký tự sáp được.
- **Section 2 (`story`):** `story_title`, `story_subtitle`, `save_date_photo_url`, `couple_quote`, `story_content`.
- **Section 3 (`parents`):** `groom_father`, `groom_mother`, `groom_parents_address`, `bride_father`, `bride_mother`, `bride_parents_address`, `groom_photo_url`, `groom_bio`, `bride_photo_url`, `bride_bio` (và `groom_parents`, `bride_parents` nếu mẫu gộp tên cha mẹ).
- **Section 4 (`countdown_calendar`):**
  - Container đếm ngược có thuộc tính `data-countdown="{{ $targetIso }}"`, các số gắn `data-sync-target="countdown_day"` (class `.mayhen_invitation_cd_day`), `countdown_hour` (`.mayhen_invitation_cd_hour`), `countdown_min` (`.mayhen_invitation_cd_min`), `countdown_sec` (`.mayhen_invitation_cd_sec`).
  - Lịch tháng gắn `data-sync-target="calendar_month_year"` ở tiêu đề tháng/năm và `data-sync-target="wedding_calendar"` (class `.mayhen_invitation_calendar_body`) ở lưới ngày.
- **Section 5 (`events`):** `party_time`, `party_venue`, `event_party_address`, `ceremony_time`, `ceremony_address`, `ceremony_groom_time`, `ceremony_groom_address`, và nút link Google Maps gắn `data-sync-target="event_party_map_link"` (cẩn thận đồng bộ `href`!).
- **Section 6 (`timeline`):** với mỗi `$idx` trong `$timelineItems` (0..5), gắn `data-sync-target="timeline.{{ $idx }}.time"` và `data-sync-target="timeline.{{ $idx }}.title"`.
- **Section 7 (`dresscode`):** gắn `data-sync-target="dresscode_colors"` lên vùng `.mayhen_invitation_dresscode_swatches` (hoặc lắng nghe `MAYHEN_SYNC_INPUT` để tự vẽ lại các chấm màu tròn `#HEX` live ngay khi gõ!) và `data-sync-target="dresscode_note"` lên dòng ghi chú.
- **Section 8 (`album`):** gắn `data-sync-target="gallery.0"` … `data-sync-target="gallery.9"` lên đúng 10 ảnh `<img>` (và nếu ảnh dùng Lightbox, lắng nghe `MAYHEN_SYNC_INPUT` hoặc cập nhật `data-lightbox-src` khi `src` thay đổi).
- **Section 9 (`gift`):** `qr_groom_image`, `groom_bank_name`, `groom_bank_account`, `groom_account_name`; `qr_bride_image`, `bride_bank_name`, `bride_bank_account`, `bride_account_name`.
- **Section 10 (`ending`):** `thank_you_title`, `thank_you_quote`, `footer_photo_url`.
- **Nhạc nền:** `<audio ... data-sync-target="music_url">`.

### G3. API Toàn Cục Ngày – Lịch – Đếm Ngược – Âm Lịch – Dresscode

`editor-bridge.blade.php` gọi trực tiếp 2 hàm sau khi người dùng đổi ngày/giờ trong Studio:

```js
window.updateMayhenCountdown = function (targetDate) {
  // Parse an toàn (YYYY-MM-DD / DD.MM.YYYY / Date) theo Asia/Ho_Chi_Minh (+07:00),
  // clearInterval cũ trước khi tạo timer mới, không hiển thị số âm (< 0 => 00),
  // cập nhật [data-sync-target="countdown_day|countdown_hour|countdown_min|countdown_sec"].
};

window.renderMayhenWeddingCalendar = function (targetDate) {
  // Tính chính xác số ngày trong tháng (xử lý năm nhuận: year % 4 === 0 && year % 100 !== 0 || year % 400 === 0),
  // thứ Hai đầu tuần (Monday = 0 ... Sunday = 6), đánh dấu ngày cưới bằng class
  // .mayhen_invitation_calendar_day .mayhen_invitation_selected .mayhen_invitation_calendar_selected_day.
};
```

**Chống xung đột với `editor-bridge.blade.php`:**
- **TUYỆT ĐỐI KHÔNG ghi đè** `window.__mayhenSyncInput`, `window.__mayhenScrollToSection` hay `window.__mayhenScrollToTarget`!
- Để cập nhật thêm các phần đặc thù của mẫu (vẽ lại chấm màu `dresscode_colors`, cập nhật lại liên kết Google Calendar khi đổi `wedding_date`/`party_time`, cập nhật `data-lightbox-src` khi đổi `gallery.{i}`, cập nhật nút bản đồ `href`), hãy dùng `window.addEventListener('message', ...)` kiểm tra `event.origin === window.location.origin` và `event.data?.type === 'MAYHEN_SYNC_INPUT'`.
- Link **"Thêm vào Google Calendar"**: phải sinh động từ `$weddingDate` + `$partyTime` (theo chuẩn UTC hoặc `+07:00`) trong PHP và tự cập nhật lại trong JS khi đổi ngày — **tuyệt đối không hardcode ngày mẫu cũ**.
- Tách module JS sạch: nạp bằng `<script type="module">`, không khai báo trùng biến toàn cục trên `window` (tuyệt đối tránh lỗi `SyntaxError: Identifier 'urlParams' has already been declared`).

---

## 8. HIỆU ỨNG & ANIMATION ĐẸP — MƯỢT, TRUNG THỰC, KHÔNG CHE NỘI DUNG

1. **Bám sát hiệu ứng mẫu gốc, thực thi chuẩn điện ảnh:**
   - Màn mở thiệp: nếu mẫu gốc mở phong bì sáp 3D, xé niêm phong, tách hai cánh cửa chữ Hỷ hay rèm mờ tan, tái dựng với `perspective`, `rotateY` / `rotateX` / `translate3d` / `clip-path` cùng easing `cubic-bezier(0.22, 1, 0.36, 1)`.
   - Scroll Reveal: dùng `IntersectionObserver` (`threshold: 0.12`, `rootMargin: '0px 0px -32px 0px'`) kích hoạt chuyển động trượt nhẹ + hiện dần (`opacity` + `transform: translateY(...) scale(...)` hoặc `clip-path`). **Luôn bật ngay lập tức cho các phần tử đã nằm trong viewport lúc tải trang và toàn bộ trang khi `isEmbed === true`.**
   - Hiệu ứng hạt trang trí (hoa rơi / nhũ vàng / đom đóm / sao băng canvas): chỉ triển khai khi trang nguồn có hoặc phù hợp với khí chất mẫu gốc; Canvas phải luôn set `pointer-events: none`, giới hạn mật độ hạt hợp lý để giữ frame ổn định trên điện thoại tầm trung.
   - Hover & Micro-interaction: nhấn nổi nút, lướt gallery Lightbox phóng to mượt, lật mở Hộp mừng cưới / bản đồ 2 cánh, bút ký nét thanh đậm theo vận tốc di chuyển con trỏ.
2. **An toàn `prefers-reduced-motion`:**
   ```css
   @media (prefers-reduced-motion: reduce) {
     .mayhen_invitation_root *,
     .mayhen_invitation_root *::before,
     .mayhen_invitation_root *::after {
       animation-duration: 0.01ms !important;
       animation-iteration-count: 1 !important;
       transition-duration: 0.01ms !important;
     }
     .mayhen_invitation_reveal {
       opacity: 1 !important;
       transform: none !important;
     }
   }
   ```
   > **Tuyệt đối không để `.mayhen_invitation_reveal` bị kẹt ở `opacity: 0` dưới `prefers-reduced-motion: reduce`!**

---

## 9. CHUẨN `schema.json` KHỚP `InvitationTemplateService` & STUDIO EDITOR V2

Tệp `schema.json` phải phản ánh đúng các field trong [`InvitationTemplateService::getCanonicalSections()`](file:///d:/Project/Laravel/mayhen.vn/app/Services/InvitationTemplateService.php) và sử dụng 100% ảnh/audio thật nằm trong `assets/` của mẫu (tuyệt đối không có key trỏ tới ảnh `.webp` hash không tồn tại hay URL ngoài!):

```json
{
  "slug": "{slug}",
  "version": "v1",
  "name": "{ten_mau}",
  "tier": "{tier}",
  "complexity": "editorial",
  "setup_time": "3 phút",
  "supported_sections": [
    "hero",
    "story",
    "parents",
    "countdown_calendar",
    "events",
    "timeline",
    "dresscode",
    "album",
    "gift",
    "ending"
  ],
  "sections": [
    {
      "id": "hero",
      "title": "Trang Bìa Chính",
      "short_title": "Trang Bìa",
      "icon": "sparkles",
      "fields": [
        { "key": "wedding.extra_data.ceremony_type", "label": "Loại hình lễ cưới", "type": "text", "required": false, "default": "Lễ Thành Hôn", "max": 50 },
        { "key": "wedding.groom_name", "label": "Họ và tên Chú rể", "type": "text", "required": true, "default": "Minh Tuấn", "max": 30 },
        { "key": "wedding.bride_name", "label": "Họ và tên Cô dâu", "type": "text", "required": true, "default": "Thu Hà", "max": 30 },
        { "key": "wedding.wedding_date", "label": "Ngày cử hành hôn lễ", "type": "date", "required": true, "default": "2026-12-20" },
        { "key": "wedding.extra_data.wedding_lunar_date", "label": "Ngày âm lịch cử hành hôn lễ", "type": "text", "required": false, "default": "Nhằm ngày 12 tháng 11 năm Bính Ngọ", "max": 100 },
        { "key": "wedding.cover_photo_url", "label": "Ảnh bìa đại diện thiệp (Tỉ lệ chuẩn 3:4)", "type": "image", "required": false, "default": "/mau-thiep/{slug}/assets/images/hero-couple.webp" },
        { "key": "wedding.extra_data.slogan", "label": "Câu slogan mở màn", "type": "text", "required": false, "default": "...", "max": 200 }
      ]
    }
  ]
}
```

**Các khóa `key` chuẩn còn lại trong 9 section tiếp theo** *(tất cả field dưới đây đều gắn tiền tố **`wedding.extra_data.*`**, ngoại trừ `wedding.wedding_date` và `wedding.cover_photo_url`)*:
- `story`: `wedding.extra_data.save_date_photo_url` (`type: "image"`), `wedding.extra_data.story_title`, `wedding.extra_data.story_subtitle`, `wedding.extra_data.couple_quote`, `wedding.extra_data.story_content`.
- `parents`: `wedding.extra_data.groom_father`, `wedding.extra_data.groom_mother`, `wedding.extra_data.groom_parents_address`, `wedding.extra_data.bride_father`, `wedding.extra_data.bride_mother`, `wedding.extra_data.bride_parents_address`, `wedding.extra_data.groom_photo_url`, `wedding.extra_data.groom_bio`, `wedding.extra_data.bride_photo_url`, `wedding.extra_data.bride_bio`. *(Lưu ý: dùng `wedding.extra_data.groom_photo_url` / `bride_photo_url`, không dùng `wedding.groom_photo_url`)*.
- `countdown_calendar`: `wedding.wedding_date`, `wedding.extra_data.event_target_time` (`type: "time"`).
- `events`: `wedding.extra_data.party_time`, `wedding.extra_data.party_venue`, `wedding.extra_data.event_party_address`, `wedding.extra_data.ceremony_time`, `wedding.extra_data.ceremony_address`, `wedding.extra_data.ceremony_groom_time`, `wedding.extra_data.ceremony_groom_address`, `wedding.extra_data.event_party_map_link`.
- `timeline`: `wedding.extra_data.timeline` (`type: "repeater"`, `"max_items": 6`, `"item_structure": [{"key": "time", "label": "Giờ", "type": "time"}, {"key": "title", "label": "Nội dung", "type": "text"}]`).
- `dresscode`: `wedding.extra_data.dresscode_colors` (`"#HEX1,#HEX2,#HEX3,#HEX4"`), `wedding.extra_data.dresscode_note`.
- `album`: `wedding.extra_data.gallery` (`type: "gallery"`, `"max_items": 10`, `"default": [...]` gồm đúng 10 đường dẫn `/mau-thiep/{slug}/assets/images/...webp` có thật trên ổ đĩa).
- `gift`: `wedding.extra_data.groom_bank_name`, `wedding.extra_data.groom_bank_account`, `wedding.extra_data.groom_account_name`, `wedding.extra_data.qr_groom_image`; `wedding.extra_data.bride_bank_name`, `wedding.extra_data.bride_bank_account`, `wedding.extra_data.bride_account_name`, `wedding.extra_data.qr_bride_image`.
- `ending`: `wedding.extra_data.thank_you_title`, `wedding.extra_data.thank_you_quote`, *(tùy chọn `wedding.extra_data.footer_photo_url` nếu mẫu có ảnh tròn chân trang)*.

**Đăng ký vào Kho Mẫu (nếu tích hợp trực tiếp vào repo `mayhen.vn`):**
- Thêm/cập nhật bản ghi trong [`database/seeders/InvitationTemplateSeeder.php`](file:///d:/Project/Laravel/mayhen.vn/database/seeders/InvitationTemplateSeeder.php) với `slug`, `name`, `category`, `thumbnail_url` (trỏ tới file local có thật trong `/mau-thiep/{slug}/assets/...`), `demo_url => '/xem-thu/{slug}'`, `is_active => true`, `default_config` gồm `tier`, `complexity`, `features`, `timeline`, `gallery`, `tags`.
- Đồng thời viết/chạy feature test kiểm tra mẫu trong `tests/Feature/`.

---

## 10. KIỂM ĐỊNH KỸ THUẬT & VISUAL REGRESSION QA (CHỐT LỖI 0%)

**Không được báo hoàn thành khi chưa chạy đủ cả 3 lớp kiểm định sau:**

### I1. Kiểm định Cú pháp & Cổng Kiểm Tra Nội Bộ Mây Hẹn (`InvitationTemplateService`)

1. Kiểm tra cú pháp JavaScript:
   ```bash
   node --check resources/views/client/invitations/{slug}/script.js
   ```
2. Kiểm tra tính toàn vẹn & điểm Preflight Quality Gate qua Artisan:
   ```bash
   php artisan tinker --execute '
   dump(App\Services\InvitationTemplateService::inspectTemplateIntegrity("{slug}"));
   dump(App\Services\InvitationTemplateService::runPreflightAudit("{slug}"));
   '
   ```
   **Điều kiện bắt buộc đạt:**
   - `is_canonical_valid === true` (đủ `10/10` Section)
   - `gallery_valid === true`
   - `has_essential_sync === true`
   - `extends_invitation === true`
   - `has_embed_guard === true`
   - `uses_bem_prefix === true`
   - `is_local_assets_only === true` (`external_violations === []`)
   - `curated_compatible === true`
   - `is_ready_for_release === true` và `blockers === []`

### I2. Kiểm thử Chức năng End-to-End bằng Chromium Headless / Playwright

Chạy kiểm thử trực tiếp trên các URL thật của ứng dụng:
1. **Trang Xem thử (`/xem-thu/{slug}`):**
   - Tải ở desktop `1440×900` và mobile `390×844` + `320×720`: màn hình mở thiệp hiển thị đúng mẫu gốc, chưa cuộn tràn.
   - Click mở thiệp -> animation chạy mượt -> hết hiệu ứng có thể cuộn trang mượt tới đáy; nhạc phát được (không chồng 2 bài với `#mayhenMusicDock`).
   - Cuộn toàn bộ trang & kiểm tra DOM: **0px tràn ngang (`scrollWidth <= innerWidth`)**, **0 ảnh gãy (`naturalWidth > 0` cho 100% ảnh hiển thị)**, **0 HTTP 404/500** (kể cả font `.woff2`, icon `.svg`, ảnh CSS `background-image`, audio `.mp3`), **0 JS console errors / uncaught pageerrors**.
   - Kiểm tra nút Copy STK (không popup `alert()`), Lightbox album mở/đóng, bản đồ, Google Calendar URL hợp lệ, Canvas ký tên vẽ được và xuất PNG được, RSVP & Wishes trả trạng thái chuẩn.
2. **Trang Studio Embed (`/xem-thu/{slug}?embed=1&mode=editor`):**
   - Màn opening ẩn 100%, nội dung hiện ngay lập tức (không màn hình trắng), không khóa cuộn body.
   - Gửi message giả lập `MAYHEN_SYNC_INPUT` (đổi `groom_name`, `bride_name`, `wedding_date`, `dresscode_colors`, `timeline.0.title`, `qr_groom_image` / ảnh mới) + `MAYHEN_SCROLL_TO_SECTION` -> xác nhận DOM & lịch tháng & countdown cập nhật ngay tức thời!
3. **Luồng Tạo Thiệp Thực Tế (`/tao-thiep?template={slug}` -> `POST /tao-thiep` -> `/thiep/{slug-moi}`):**
   - Tạo thiệp thành công với dữ liệu tùy chỉnh + đổi STK ngân hàng -> mở `/thiep/{slug-moi}` xác minh VietQR tự sinh theo STK mới, form **RSVP** (`POST /thiep/{slug}/rsvp`), **Wishes** (`POST /thiep/{slug}/wishes`), và **Signature** (`POST /api/thiep/{id}/so-ky-ten/luu-chu-ky`) lưu xuống database trả HTTP 200 JSON thành công!
   - Nếu chạy ở chế độ `?curated=1` / `$isCuratedSeven = true`: xác minh section `dresscode` & `gift` ẩn sạch, bố cục không vỡ.

### I3. Đối chiếu Visual Diff (`qa/`)

```text
qa/
  reference/ (mobile-opening.png, mobile-fullpage.png, desktop-fullpage.png)
  local/     (mobile-opening.png, mobile-fullpage.png, desktop-fullpage.png)
  comparison/(mobile-side-by-side.png, mobile-diff.png, desktop-side-by-side.png, desktop-diff.png)
```

Rà theo thứ tự: **Bố cục từng section → Ảnh & crop → Màu nền & gradient → Font chữ & cỡ chữ → Viền/Radius/Shadow → Spacing → Icon/Họa tiết → Animation**. Sửa mọi sai lệch phát hiện được rồi chụp lại kiểm chứng. Ghi rõ sai khác (nếu là ngoại lệ bắt buộc như bỏ ảnh người khỏi RSVP/Signature) vào `visual-audit.md`.

---

## 11. CẤU TRÚC THƯ MỤC CHUẨN & ĐÓNG GÓI ZIP

### Trong Repository `mayhen.vn`:
```text
resources/views/client/invitations/{slug}/
├── template.blade.php
├── styles.css
├── script.js
├── schema.json
└── assets/
    ├── thumbnail.webp
    ├── wedding-song.mp3
    ├── images/ (...webp)
    ├── fonts/  (...woff2)
    └── icons/  (...svg)
```

### Khi bàn giao file ZIP độc lập (`{ten_mau}.zip`):
```text
{ten_mau}.zip
└── {slug}/
    ├── template.blade.php
    ├── styles.css
    ├── script.js
    ├── schema.json
    ├── assets-manifest.json
    ├── reference-audit.md
    ├── visual-audit.md
    ├── README.md
    ├── assets/ (...)
    └── qa/ (...)
```
> **Không tạo file placeholder rỗng** để trông đủ cây thư mục. Mỗi file tài sản khai báo trong code và `schema.json` đều phải thực sự tồn tại và đọc được.

---

## 12. QUY TRÌNH THỰC HIỆN TUẦN TỰ (KHÔNG BỎ BƯỚC)

1. **Inspect:** Mở `url_tham_chieu` (ở Mục 0) bằng browser thật, đo DOM/styles/animations ở mọi viewport.
2. **Capture:** Lưu screenshot nguồn ở trạng thái đóng, mở, full-page mobile & desktop.
3. **Audit (`reference-audit.md`):** Lập hồ sơ thiết kế từng section đúng thứ tự nguồn.
4. **Assets:** Tải 100% ảnh, font, icon SVG, nhạc về `assets/`, chuyển WebP hợp lý, ghi `assets-manifest.json`.
5. **UI + Motion (`styles.css` + `template.blade.php`):** Dựng khung 10 section theo Source Lock + BEM `.mayhen_invitation_*` + Animation mượt.
6. **Integrate (`template.blade.php` + `script.js` + `schema.json`):** Gắn `$resolveAsset`, `VietQrService::resolveQrImage`, `MAYHEN_SYNC_INPUT`, `updateMayhenCountdown`, `renderMayhenWeddingCalendar`, RSVP/Wishes/Signature API, `$isEmbed` & `$isCuratedSeven`.
7. **Service Audit:** Chạy `node --check`, `inspectTemplateIntegrity()`, `runPreflightAudit()` đạt chuẩn 100%.
8. **E2E & Visual Regression:** Test trên `/xem-thu/{slug}`, `?embed=1`, `/tao-thiep`, `/thiep/{slug}` bằng browser thật, đối chiếu ảnh chụp `qa/`.
9. **Fix & Re-verify:** Sửa triệt để mọi lỗi CSS/JS/404/API/visual rồi chạy lại kiểm tra sau lần sửa cuối.
10. **Finalize / Package:** Cập nhật Seeder + `context.gemini.md` (nếu `dau_ra` là `repo` hoặc `both`), và đóng gói/giải nén kiểm tra `{ten_mau}.zip` (nếu `dau_ra` là `zip` hoặc `both`).

---

## 13. CHECKLIST CHỐT CUỐI TRƯỚC KHI BÁO HOÀN THÀNH

- [ ] Đã xem & chụp đủ bản gốc ở trạng thái mở/đóng và cuộn hết trang trên mobile & desktop.
- [ ] Giao diện giữ đúng 100% bảng màu, font, viền, radius, shadow, bố cục, ảnh, tỷ lệ crop và hiệu ứng đẹp của mẫu tham chiếu (không tự chế sang style AI).
- [ ] Pure Blade + Vanilla CSS (prefix `mayhen_invitation_*` scoped trong `.mayhen_invitation_root`) + ES Modules JS (không CDN, không Tailwind/Bootstrap/GSAP/Swiper/jQuery).
- [ ] **0 liên kết tài nguyên ngoại (`ladicdn`, `unsplash`, `googleapis`, ...)**; 100% ảnh, font `.woff2`, icon `.svg`, audio `.mp3` nội bộ hoạt động HTTP 200.
- [ ] Đúng 10 Section chuẩn với `data-mayhen-section`, đủ `data-sync-target` ở mọi lần xuất hiện, hỗ trợ `@if(!$isCuratedSeven)` và `$isEmbed` không trắng/không khóa cuộn.
- [ ] `<audio class="mayhen_invitation_audio" data-mayhen-audio data-sync-target="music_url">` nhường `music-dock.blade.php` không phát chồng 2 nhạc.
- [ ] Đếm ngược (`window.updateMayhenCountdown`) và Lịch tháng (`window.renderMayhenWeddingCalendar`) cập nhật live đúng múi giờ `Asia/Ho_Chi_Minh`, Google Calendar động theo ngày cưới.
- [ ] Đủ **Signature Canvas** + **RSVP** + **Wishes** + **VietQR (`VietQrService::resolveQrImage`)** hoạt động thật với API Laravel; **tuyệt đối KHÔNG có ảnh cặp đôi/ảnh người trong vùng Signature & RSVP**.
- [ ] `node --check`, `inspectTemplateIntegrity()`, `runPreflightAudit()` đạt sạch (`blockers = []`); Browser test **0px overflow, 0 ảnh vỡ, 0 HTTP 404, 0 lỗi JS**.
- [ ] Không bịa đặt đường dẫn, ảnh QA hay kết quả test; mọi hạn chế/ngoại lệ được ghi rõ trong `visual-audit.md` / báo cáo cuối.

> **NHẮC LẠI TỐI QUAN TRỌNG:** Người dùng cần một mẫu thiệp **giống hệt trang tham chiếu từ giao diện đến animation đẹp mượt**, đồng thời **chạy chuẩn xác 100% logic code Mây Hẹn (`editor-bridge`, Studio `/tao-thiep`, `schema.json`, RSVP, Wishes, Signature, VietQR, Preflight Audit) không một sai sót nhỏ**. Mọi thay đổi trực quan ngoài thiết kế gốc chỉ được phép thực hiện cho phần bắt buộc tích hợp Mây Hẹn và phải giữ nguyên hồn thẩm mỹ của mẫu gốc.
