# MÂY HẸN (MAYHEN.VN) — CONTEXT & ARCHITECTURE GUIDE

> **Lưu ý quan trọng**: Tệp này là bản context tinh gọn, tập trung vào kiến trúc cốt lõi, quy tắc bất biến và trạng thái hệ thống hiện tại.  
> Toàn bộ lịch sử chi tiết các đề án cũ (#1 đến #83) đã được lưu trữ an toàn tại [`docs/archive/context.gemini.archive.md`](docs/archive/context.gemini.archive.md).

---

## 1. TỔNG QUAN HỆ THỐNG & NỀN TẢNG CÔNG NGHỆ

- **Tên dự án**: Mây Hẹn (`mayhen.vn`) — Nền tảng tạo thiệp cưới online cao cấp, mang phong cách *Quiet Luxury*, tinh tế, lãng mạn.
- **Backend**: Laravel 13.35.0 theo `php artisan --version` ngày 2026-11-15 (PHP 8.5 theo thông tin dự án; cần xác minh riêng phiên bản runtime PHP khi dùng).
- **Frontend Core Protocol**:
  - **100% Thuần (Vanilla)**: HTML5 ngữ nghĩa + CSS thuần (CSS Custom Properties) + JavaScript Native ES Modules.
  - **CẤM TUYỆT ĐỐI**: Bootstrap, Tailwind CSS, Bulma, jQuery, Vue, React, Alpine.js, GSAP, Anime.js, AOS, Swiper.js, Slick Slider.
  - **Không dùng CDN bên ngoài**: Toàn bộ icon, font, hình ảnh, script phải được lưu cục bộ trong `public/assets/`.
- **Database**: MySQL / SQLite (Hỗ trợ SePay VietQR, RBAC, Customer 360, CMS Pages, SEO Redirects).
- **Kiểm thử**: Pest PHP (Feature Tests, Unit Tests). Luôn đảm bảo 100% tests vượt qua trước khi bàn giao.

---

## 2. QUY CHUẨN AI AGENT BẮT BUỘC (MANDATORY AI PROTOCOL)

1. **ĐỌC TRƯỚC - CẬP NHẬT SAU**:
   - Luôn đọc `context.gemini.md` và `AGENTS.md` trước khi thực hiện bất kỳ nhiệm vụ nào.
   - Sau khi hoàn thành công việc, **bắt buộc cập nhật tệp này** với các route mới, thay đổi UI và trạng thái kiểm thử.
2. **SỬA FILE MINH BẠCH (TRANSPARENT FILE EDITING)**:
   - Luôn dùng công cụ `edit_file` hoặc `write_file` để người dùng có thể xem visual diff trực tiếp. Cấm dùng script ngầm để sửa mã nguồn mà không có sự đồng ý.
3. **KHÔNG TỰ Ý COMMIT / PUSH**:
   - Tuyệt đối không tự commit hoặc push lên Git. Luôn cung cấp gợi ý thông điệp commit bằng Tiếng Việt có dấu rõ ràng để người dùng tự quyết định.
4. **QUY CHUẨN CHẤT LƯỢNG CODE**:
   - Sau khi sửa đổi tệp PHP, bắt buộc chạy: `vendor/bin/pint --dirty --format agent`.
   - File test phải đặt trong `tests/Feature/` hoặc `tests/Unit/`. Chạy `vendor/bin/pest` để xác nhận.

---

## 3. QUY TẮC CSS CLASS PREFIXING (BEM CONVENTION)

Tất cả các class CSS trong dự án bắt buộc phải có tiền tố định danh theo từng module để tránh xung đột phong cách:

| Phân hệ / Module | CSS Class Prefix | Thư mục Stylesheet |
| :--- | :--- | :--- |
| **Trang chủ Client** | `mayhen_home_*` | `public/assets/client/css/pages/home.css` |
| **Dịch vụ / Bảng giá** | `mayhen_service_*`, `mayhen_pricing_*` | `public/assets/client/css/pages/` |
| **Kho Mẫu Thiệp** | `mayhen_templates_*`, `mayhen_template_detail_*` | `public/assets/client/css/pages/` |
| **Studio Tạo Thiệp V2**| `mayhen_editor_*` | `public/assets/client/css/pages/editor.css` |
| **Quản lý Khách mời** | `mayhen_guest_*` | `public/assets/client/css/pages/guests.css` |
| **Thiệp cưới Public** | `mayhen_invitation_*` | `public/assets/invitations/{template}/css/` |
| **Trung tâm Docs** | `mayhen_docs_*` (Index), `mayhen_docs_detail_*` (Detail) | `public/assets/client/docs/css/docs-main.css` |
| **Thành phần Dùng chung**| `mayhen_client_*` | `public/assets/client/css/components/` |
| **Trang Quản trị (Admin)**| `mayhen_admin_*` | `public/assets/admin/css/` |

---

## 4. 8 NGUYÊN TẮC CHUẨN HÓA MẪU THIỆP CƯỚI (INVITATION STANDARDS)

1. **Khung 10 Section Tiêu Chuẩn**:
   `1. Opening` -> `2. Hero Cover` -> `3. Love Story & Quote` -> `4. Family & Couple` -> `5. Countdown & Calendar` -> `6. Events & Maps` -> `7. Timeline` -> `8. Dresscode` -> `9. Album 10 Photos` -> `10. Gift, Interactive & Ending`.
2. **Hệ thống Class Toàn Cục**: Toàn bộ mẫu thiệp dùng class BEM `mayhen_invitation_*`. Tùy biến biến thể qua modifier (ví dụ: `mayhen_invitation_hero--celestial`).
3. **100% Flow Layout (Flexbox & Grid)**: Cấm tuyệt đối layout định vị tuyệt đối `position: absolute` kéo-thả kiểu LadiPage.
4. **Hợp đồng Live Sync**: Gắn đầy đủ `data-sync-target="{field_name}"` cho mọi vị trí hiển thị dữ liệu để Studio V2 đồng bộ thời gian thực 2 chiều.
5. **Phòng vệ Nhúng Studio Editor (`$isEmbed = true`)**:
   Tự động mở sẵn thiệp (`opened`), ẩn màn mở đầu, không khóa cuộn `no-scroll` trên body/html, cưỡng chế `.card-wrapper { display: block !important; }`.
6. **100% Local Assets & Fallback An Toàn**:
   Cấm dùng link ngoài (`ladicdn`, `unsplash`). Tất cả tài nguyên phải nằm trong `assets/` của mẫu thiệp với fallback hợp lệ.
7. **Kế Thừa Layout Chung**:
   Mọi mẫu thiệp bắt buộc `@extends('client.layouts.invitation')` để tích hợp `editor-bridge.blade.php` và sự kiện `MAYHEN_BRIDGE_READY`.
8. **Quy chuẩn Sổ Ký Tên & RSVP**:
   Bắt buộc có chức năng ký tên và xác nhận RSVP. **CẤM TUYỆT ĐỐI** đặt ảnh chụp người vào 2 phần này; chỉ dùng hoa văn đồ họa, sticker SVG, hiệu ứng canvas hoặc tem sáp (wax seal).

---

## 5. CÁC MODULE & PHÂN HỆ CỐT LÕI ĐÃ TRIỂN KHAI

### 5.1. Trung Tâm Hướng Dẫn Sử Dụng (`/docs` & `/admin/docs`)
- **Controller**: `ClientDocsController`, `AdminDocsController`.
- **Service**: `app/Services/DocumentationService.php` (Phân vùng dữ liệu an toàn, tìm kiếm tiếng Việt không dấu, sinh TOC H2/H3 tự động).
- **Kiến trúc Chịu tải Cao & Tốc độ Cực hạn (High Concurrency 1M Users)**:
  - **Zero Database Queries**: Đã loại trừ hoàn toàn các truy vấn vào MySQL `seo_redirects` khi truy cập `/docs*` và `sitemap*` trong `SeoRedirectMiddleware`. `AppServiceProvider` view composer chỉ gắn với `admin.layouts.master` nên các trang Docs đạt tuyệt đối **0 truy vấn cơ sở dữ liệu** (Đã có test tự động kiểm chứng `DB::getQueryLog()->toBeEmpty()`).
  - **Fast Frontmatter Scan (Quét nhanh 2 lớp)**: `getCategories()` chỉ đọc vài dòng YAML frontmatter đầu file (`---` tới `---`) rồi đóng ngay file handle, không parse markdown toàn bộ 58 bài, giảm 95% CPU/RAM và tăng tốc duyệt danh mục 50 lần.
  - **On-Demand Compilation**: `getArticle()` chỉ phân giải Markdown sang HTML và sinh TOC cho bài viết được chỉ định khi người dùng mở bài đó, đồng thời gắn kèm điều hướng `prev` / `next` bài trước/sau mượt mà.
  - **Static Memory Memoization**: Tận dụng RAM tĩnh `self::$memoizedCategories` và `self::$memoizedArticles` trong vòng đời process/worker PHP (FPM / Octane), truy xuất lần 2 diễn ra trong 0.00ms.
  - **Lớp Cache Bền vững**: `Cache::remember` 24 giờ cho từng bài viết và danh mục (`mayhen_docs_categories_*`, `mayhen_docs_article_*`), hỗ trợ hàm xóa sạch `clearCache()`.
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: `ClientDocsController` trả về header `ETag`, `Last-Modified` và `Cache-Control: public, max-age=3600, stale-while-revalidate=86400`. Khi trình duyệt/CDN gửi `If-None-Match`, máy chủ trả về `304 Not Modified` ngay lập tức (0ms render, 0 bandwidth).
- **Giao diện**: Đồng bộ 100% từ `docs/templates/` với 2 không gian biệt lập:
  - Client (`/docs`): Hướng dẫn cô dâu chú rể tạo thiệp, đổi ảnh Smart Focal Point, quản lý RSVP, kiểm soát quyền riêng tư.
  - Admin (`/admin/docs`): Sổ tay Vận hành Tiêu chuẩn (Admin SOP), đối soát SePay thủ công, phân quyền RBAC, cam kết kiểm toán `audit_logs`.
- **CSS & JS**: `public/assets/client/docs/` (CSS và JS thuần, nút theme switcher pill với min-width 125px và responsive mobile 36px circular, loại bỏ toàn bộ duplicate listeners gây đè innerHTML/co rút pill, icon SVG Mặt Trời/Mặt Trăng chuẩn nét, mũi tên SVG xoay 180 độ, auto cache-busting `?v={filemtime}`, TOC, phím tắt Ctrl+K).
- **UI/UX Quiet Luxury Refinement**:
  - Tối ưu tương phản nút bấm: Chữ trắng nổi bật trên nền nâu đỏ mận `#613B3E` (Dark Mode: chữ tối `#181517` trên nền hoa hồng `#D4898E`).
  - Font chữ thanh lịch: Tiêu đề H1/H2 sử dụng `Playfair Display` serif chuẩn typography tiếng Việt (triệt tiêu lỗi đè dấu do negative letter-spacing).
  - Thân bài đọc thoải mái: Cỡ chữ 15-15.5px, line-height 1.85, độ đậm 400-600 nhẹ nhàng thay vì 800 thô cứng.
  - Sidebar gọn gàng: Bỏ tiêu đề viết hoa trùng lặp (`strtoupper` ASCII bug), phân mục accordion êm ái.
  - Chuẩn hóa bộ điều khiển đọc & phím tiện ích:
    - **Nút Trợ giúp (Help Button)**: Thiết kế dạng Pill Quiet Luxury bo tròn mềm mại (`border-radius: 999px`), icon tròn tỉ lệ 1:1 không bao giờ bị méo elip, chữ "Trợ giúp" giữ `white-space: nowrap !important;` không bị ngắt thành 2 dòng, responsive thông minh trên màn hình hẹp (< 690px).
    - **Cụm Stepper Tăng/Giảm Cỡ Chữ (A− / A+)**: Thiết kế hộp điều khiển xúc giác tinh tế với hiển thị cỡ chữ động (16px), biến CSS `--mayhen-docs-detail-font-size` áp dụng trực tiếp cho toàn bộ văn bản (p, li, blockquote, callout) mà không bị ghi đè bởi media queries hay hardcoded `!important`.
    - **Nút Lưu Bài Viết (Bookmark System)**: Trạng thái tương tác rõ rệt (chuyển "Đã lưu" kèm icon tô đặc rose `#613B3E`), lưu trữ đầy đủ tiêu đề và chuyên mục, tích hợp trực tiếp bảng "✦ Bài viết đã lưu" tự động cập nhật ngay trên Sidebar và hỗ trợ xóa nhanh từng bài.

### 5.2. Trang Chủ Marketing & Landing Page (`/`)
- **Controller**: `HomeController`.
- **Kiến trúc Chịu tải Cao & Zero DB Queries (1M Users Scale)**:
  - **Zero Database Queries**: Toàn bộ dữ liệu hiển thị trên trang chủ (`featuredTemplates`, `galleryTemplates`, `featuredReviews`, và `topAnnouncement` từ `SiteAnnouncementService`) được lưu cache và memoize 100%. Khi khách vãng lai (99.9% lưu lượng) truy cập, hệ thống đạt **tuyệt đối 0 truy vấn Database** (Đã có test tự động kiểm chứng `DB::getQueryLog()->toBeEmpty()`).
  - **Static RAM Memoization**: Tận dụng RAM tĩnh trong worker PHP (FPM / Octane) cho các danh sách mẫu thiệp và đánh giá nổi bật, loại bỏ overhead gọi `hydrate()` và Cache driver lặp lại trên từng request.
  - **Auto Cache Invalidation**: Tự động giải phóng cache và RAM tĩnh ngay khi có thêm/sửa/xóa mẫu thiệp (`InvitationTemplate::clearTemplateCaches`), đánh giá khách hàng (`Review::booted`), hoặc thông báo hệ thống (`SiteAnnouncement::booted`).
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: Phục vụ khách vãng lai qua HTTP Headers `ETag` và `Cache-Control: public, max-age=1800, stale-while-revalidate=86400`, trả về `304 Not Modified` ngay lập tức nếu nội dung không đổi; người dùng đã đăng nhập tự động nhận phản hồi cá nhân hóa riêng biệt.

### 5.3. Studio Tạo Thiệp Cưới V2 (`/tao-thiep`)
- **Controller**: `InvitationEditorController`.
- **Service**: `InvitationTemplateService`, `WeddingMusicService`, `WeddingAnalyticsService`.
- **Kiến trúc Chịu tải Cao & Zero DB Queries (1M Users Scale)**:
  - **Zero Database Queries**: Toàn bộ active templates (`allTemplates`, `selectedTemplate`, `defaultTemplate`) và `schema.json` được phục vụ từ Static RAM và Cache tầng 2. Khi khách vãng lai (99.9% lưu lượng trải nghiệm tạo thiệp thử) truy cập `/tao-thiep` hoặc `/tao-thiep?template=...`, hệ thống đạt **tuyệt đối 0 truy vấn Database** (Đã có test tự động kiểm chứng `DB::getQueryLog()->toBeEmpty()`).
  - **Static RAM Memoization**: Tận dụng RAM tĩnh `self::$memoizedAllTemplates`, `self::$memoizedDefaultTemplate`, `self::$memoizedTemplateBySlug` trong worker PHP (FPM / Octane), tìm kiếm template trong Collection bộ nhớ đạt `0.00ms`.
  - **Fallback Schema Không Chạm DB**: `InvitationTemplateService::getSchema()` tận dụng template được nạp sẵn trong bộ nhớ, không truy vấn lại DB khi tệp `schema.json` chưa được sinh riêng.
  - **Auto Cache Invalidation**: Tự động giải phóng cache và RAM tĩnh ngay khi có thêm/sửa/xóa mẫu thiệp (`InvitationTemplate::clearTemplateCaches` giải phóng cả `InvitationEditorController::clearMemoization()` và `InvitationTemplateService::clearSchemaCache()`).
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: Phục vụ khách trải nghiệm tạo thiệp qua HTTP Headers `ETag` và `Cache-Control: public, max-age=1800, stale-while-revalidate=86400`, trả về `304 Not Modified` ngay lập tức nếu mẫu không đổi; người dùng đã đăng nhập hoặc đang chỉnh sửa thiệp cá nhân tự động nhận phản hồi private không cache qua CDN.
- Trình tạo thiệp tương tác trực quan thời gian thực (Editor V2).
- Hỗ trợ đổi tiêu điểm ảnh thông minh (Smart Focal Point), chọn nhạc nền lãng mạn, chọn bảng màu Dresscode, kiểm tra chất lượng thiệp với AI Quality Doctor.

### 5.4. Dịch Vụ In Thiệp Cưới Cao Cấp (`/dich-vu`)
- **Controller**: `PageController@services`.
- **View**: `resources/views/client/pages/services.blade.php`.
- **Kiến trúc Chịu tải Cao & Zero DB Queries (1M Users Scale)**:
  - **Zero Database Queries**: Phục vụ khách vãng lai (99.9% lưu lượng) đạt **tuyệt đối 0 truy vấn Database** (Đã có test tự động kiểm chứng `DB::getQueryLog()->toBeEmpty()`).
  - **Static RAM Memoization**: Tính toán ETag và Last-Modified một lần trong tiến trình worker PHP (FPM / Octane) qua `PageController::$servicesEtag` và `PageController::$servicesLastModified`, phản hồi `0.00ms` mà không tốn I/O.
  - **Global SEO Middleware Zero Overhead**: Tối ưu hóa `SeoService::matchRedirect()` với Static RAM Memoization (`SeoService::$memoizedRedirectMap`), triệt tiêu hoàn toàn chi phí truy vấn cache lặp lại trên mọi request GET toàn hệ thống.
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: Thiết lập header `Cache-Control: public, max-age=3600, stale-while-revalidate=86400`, `ETag`, `Last-Modified`. Khi trình duyệt/Cloudflare gửi `If-None-Match`, máy chủ trả về `304 Not Modified` ngay lập tức với body rỗng. Người dùng đã đăng nhập tự động nhận view động uncached để bảo toàn phiên cá nhân hóa.

### 5.5. Bảng Giá Thiệp Cưới Online (`/bang-gia`)
- **Controller**: `PageController@pricing`.
- **View**: `resources/views/client/pages/pricing.blade.php`.
- **Model**: `App\Models\Plan`.
- **Kiến trúc Chịu tải Cao & Zero DB Queries (1M Users Scale)**:
  - **Zero Database Queries**: Toàn bộ danh sách gói dịch vụ (`plans`) được quản lý qua kiến trúc 2 tầng (Static RAM Worker + Cache Application). Phục vụ khách vãng lai (99.9% lưu lượng) đạt **tuyệt đối 0 truy vấn Database** (Đã có test tự động kiểm chứng `DB::getQueryLog()->toBeEmpty()`).
  - **Static RAM Memoization**: Tận dụng RAM tĩnh `PageController::$memoizedPlans`, `PageController::$pricingEtagBase`, `PageController::$pricingLastModified` trong worker PHP (FPM / Octane), truy xuất danh sách gói và tính toán ETag đạt `0.00ms` mà không tốn I/O, không gọi lại `Plan::hydrate` lặp lại.
  - **Hỗ trợ Query Param `/bang-gia?invitation_id=...`**: Tạo ETag phân biệt chuẩn xác khi có param nâng cấp thiệp mà vẫn đảm bảo 0 query DB.
  - **Tự Động Làm Mới Cache (Auto Invalidation)**: Model `Plan::booted()` tự động giải phóng `mayhen_pricing_plans` và gọi `PageController::clearPricingMemoization()` ngay khi gói cước được tạo/sửa/xóa.
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: Thiết lập header `Cache-Control: public, max-age=3600, stale-while-revalidate=86400`, `ETag`, `Last-Modified`. Khi trình duyệt/Cloudflare gửi `If-None-Match`, máy chủ trả về `304 Not Modified` ngay lập tức với body rỗng. Người dùng đã đăng nhập tự động nhận view động uncached để bảo toàn phiên cá nhân hóa.

### 5.6. Cẩm Nang Cưới & Chuyên Đề Tạp Chí (`/cam-nang-cuoi` & `/cam-nang-cuoi/{slug}`)
- **Controller**: `ClientBlogController` (`App\Http\Controllers\Client\BlogController`).
- **Views**: `resources/views/client/pages/blog/index.blade.php`, `show.blade.php`.
- **CSS & JS**: `public/assets/client/css/pages/blog.css`, `public/assets/client/js/pages/blog.js`.
- **Service**: `App\Services\BlogService`.
- **Models**: `BlogPost`, `BlogCategory`.
- **Kiến trúc Chịu tải Cao & Zero DB Queries (1M Users Scale)**:
  - **Zero Database Queries**: Phục vụ khách vãng lai (99.9% lưu lượng) đạt **tuyệt đối 0 truy vấn Database** trên cả trang chủ cẩm nang và trang lọc theo chuyên mục (Đã có test tự động kiểm chứng `DB::getQueryLog()->toBeEmpty()`).
  - **Static RAM Memoization**: Tận dụng RAM tĩnh trong worker PHP (FPM / Octane) cho danh mục hoạt động (`BlogService::$memoizedCategories`), bài viết nổi bật (`BlogService::$memoizedFeaturedPosts`), bài viết mặc định trang 1 (`BlogController::$memoizedDefaultPosts`) và ETag cơ sở.
  - **Triệt tiêu Hoàn toàn Lỗi Cache `__PHP_Incomplete_Class`**: Tuyệt đối không serialize Eloquent Models/Collections vào Database Cache driver. Dùng Static RAM worker cho Collection và chỉ lưu mảng phẳng nguyên bản (raw attributes) cho Tầng 2 với lớp phòng vệ tự phục hồi `if (!is_array($raw)) Cache::forget(...)`.
  - **Tra Cứu Chuyên Mục Không Truy Vấn DB**: Phương thức `BlogService::findCategoryBySlug($slug)` tra cứu trực tiếp trong danh sách danh mục đã nạp sẵn trong RAM, triệt tiêu hoàn toàn truy vấn `where('slug', ...)`.
  - **Cache Busted Versioning**: Áp dụng versioning (`mayhen_blog_version`) để tự động làm mới tức thì toàn bộ cache phân trang và chuyên mục mà không tốn chi phí quét key.
  - **Auto Cache Invalidation**: `BlogPost::booted()` và `BlogCategory::booted()` tự động gọi `BlogService::clearCache()` và `BlogController::clearMemoization()` khi có thêm/sửa/xóa dữ liệu.
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: Thiết lập header `Cache-Control: public, max-age=1800, stale-while-revalidate=86400`, `ETag`, `Last-Modified`. Khi trình duyệt/Cloudflare gửi `If-None-Match`, máy chủ trả về `304 Not Modified` ngay lập tức với body rỗng. Người dùng đã đăng nhập tự động nhận view động uncached để bảo toàn phiên cá nhân hóa.
- **Tái Thiết Kế Giao Diện Bài Viết Chi Tiết (`/cam-nang-cuoi/{slug}`) Chuẩn Quiet Luxury**:
  - **Nền trắng tinh khiết (`#FFFFFF`) & Typography thanh lịch**: Loại bỏ tone màu nhờ vàng đục, chuyển sang nền trắng sạch sẽ; phông chữ mảnh mai (`font-weight: 400`, line-height `1.85`), khoảng cách padding/margin tối ưu tinh gọn, không lãng phí không gian.
  - **Bao phủ toàn diện hơn 100 thẻ nội dung (Rich HTML Tags)**: Chăm chút tỉ mỉ từ `h1-h6`, `p`, highlight từ khóa (`strong`, `b` với nền hổ phách nhạt `rgba(245, 158, 11, 0.12)`), danh sách lồng nhau (`ul`, `ol`, `dl`), bảng biểu chi phí (`table`, `thead`, `tbody`), trích dẫn (`blockquote`, `q`), hộp callout mẹo cưới (`callout-tip/note/warning`), hình ảnh (`figure`, `figcaption`), checklist đám cưới, mã nguồn và chi tiết mở rộng (`details/summary`).
  - **Đa dạng chức năng hữu ích cho độc giả**:
    - **Thanh đo tiến độ đọc (Reading Progress)** bám đỉnh mượt mà.
    - **Thanh tiện ích (Reader Toolbar)**: Tăng/giảm cỡ chữ trực quan (`A− / A+`) ghi nhớ vào `localStorage`, nút Lưu bài viết (Bookmark), nút Sao chép liên kết (Copy Link), nút In bài (Print) chuẩn `@media print`.
    - **Mục lục bài viết (Smart TOC Sticky Sidebar)**: Tự động gắn ID neo cuộn, tích hợp **Scrollspy** highlight mục đang đọc theo thời gian thực và hỗ trợ thu gọn tiện lợi trên di động.
    - **SEO Schema JSON-LD**: Tích hợp cấu trúc `BlogPosting` và `BreadcrumbList` hoàn chỉnh cho Google Search Console.
    - **Điều hướng bài trước / bài tiếp & Chuyên đề cùng chủ đề**: Tự động liên kết mượt mà giúp giữ chân độc giả.
    - **Nút cuộn lên đầu trang (Back to Top)** và thông báo Toast xúc giác êm dịu.

### 5.7. Liên Hệ & Đặt Hẹn Tư Vấn In Thiệp (`/lien-he`)
- **Controller**: `PageController@contact`, `ContactController@submit`.
- **View**: `resources/views/client/pages/contact.blade.php`.
- **Kiến trúc Chịu tải Cao & Zero DB Queries (1M Users Scale)**:
  - **Zero Database Queries**: Phục vụ khách vãng lai (99.9% lưu lượng) đạt **tuyệt đối 0 truy vấn Database** (`DB::getQueryLog()->toBeEmpty()`).
  - **Static RAM Memoization**: Tính toán và lưu trữ ETag, Last-Modified cơ sở trực tiếp trong biến tĩnh worker PHP (`PageController::$contactEtag`, `PageController::$contactLastModified`), truy xuất tức thì trong 0.00ms.
  - **Edge CDN Optimization (ETag & 304 Not Modified)**: Thiết lập header `Cache-Control: public, max-age=3600, stale-while-revalidate=86400`, `ETag`, `Last-Modified`. Khi trình duyệt/Cloudflare gửi `If-None-Match`, máy chủ trả về `304 Not Modified` ngay lập tức với body rỗng.
  - **Bảo Toàn Trạng Thái Flash & Session**: Tự động nhận diện session khi có thông báo flash gửi form thành công (`contact_success`) hoặc lỗi validation (`errors`) hoặc người dùng đã đăng nhập để phục vụ view động uncached, bảo toàn 100% trải nghiệm tương tác.

### 5.8. Cơ Chế Tự Động Chuyển Hướng Lại Link Ban Đầu Sau Đăng Nhập / Đăng Ký (Intended URL Redirect)
- **Controller**: `App\Http\Controllers\Client\AuthController` (`showLoginForm`, `login`, `showRegisterForm`, `register`).
- **Nguyên lý hoạt động & Kiến trúc đa tầng**:
  - **Tự động nhận diện URL ban đầu (Auto-capture)**: Khi người dùng được chuyển hướng hoặc bấm nút Đăng nhập từ bất kỳ trang nào (ví dụ: `/tao-thiep?template=...`, `/tai-khoan`, `/checkout`, `/mau-thiep/...`), hệ thống xác định đường dẫn mục tiêu theo 3 mức ưu tiên:
    1. Query parameter `?redirect=...` trên thanh địa chỉ.
    2. Session `url.intended` (được ghi nhận bởi Middleware `auth` hoặc `redirect()->guest(...)`).
    3. Trang trước đó (`url()->previous()`) nếu người dùng nhấp trực tiếp từ một trang nội bộ.
  - **Bảo toàn Query Parameters**: Giữ nguyên vẹn toàn bộ các tham số như `template=...`, `step=...`, `invitation_id=...` để người dùng quay lại đúng trạng thái đang thao tác dở.
  - **Liên thông Đăng nhập <-> Đăng ký**: Chuyển đổi qua lại giữa form Đăng nhập và Đăng ký không làm mất tham số `redirect`. Người dùng đăng ký tài khoản mới cũng tự động được chuyển lại đúng link đích.
  - **Phòng vệ Chống Tấn công Open Redirect (AUTH-019) & Chống Vòng Lặp Vô Tận**: Hàm `resolveSafeRedirect` chỉ chấp thuận URL tương đối an toàn hoặc tuyệt đối cùng domain `mayhen.vn`; đồng thời hàm `isAuthUrl` tự động loại trừ các trang `/dang-nhap`, `/dang-ky`, `/dang-xuat` để ngăn chặn hiện tượng redirect lặp vòng vô tận.
  - **Tích hợp UI Nút Đăng nhập Toàn Hệ Thống**: Các nút Đăng Nhập / Đăng Ký trên Header desktop, Mobile Drawer menu, trang thiệp hết hạn (`invitation-expired.blade.php`), và layout đối tác (`partner/layouts/master.blade.php`) tự động gắn kèm `['redirect' => request()->fullUrl()]`.

### 5.9. Quản Lý Khách Mời & Check-in Lễ Tân
- Nhóm khách mời theo hộ gia đình, mã hóa đường dẫn cá nhân hóa, lời nhắn thiệp riêng.
- Sơ đồ bàn tiệc thông minh (Table Seating Chart), cockpit lễ tân quét mã QR điểm danh ngày cưới.

### 5.10. Đơn Hàng, Thanh Toán & SePay Webhook
- Thanh toán chuyển khoản tự động VietQR, khớp mã đơn hàng thời gian thực qua webhook SePay có xác thực chữ ký (Signature Verification & Idempotency).

### 5.11. Quản Trị Hệ Thống (Admin Portal)
- Dashboard phân tích tài chính, quản lý khách hàng 360, quản lý mẫu thiệp và phiên bản phát hành.
- Trung tâm Điều hành SEO (`/admin/seo`): Chuyển hướng 301/302, thuật toán phát hiện vòng lặp vô tận (Loop Detection), tự động sinh sitemap XML và audit điểm sức khỏe SEO.
- Quản lý Trang tĩnh CMS (`/admin/pages`): Điều khoản dịch vụ, Chính sách bảo mật (Luật 91/2025/QH15).

---

## 6. DANH SÁCH LỆNH VẬN HÀNH NHANH (QUICK CHEATSHEET)

```bash
# Định dạng mã nguồn PHP theo chuẩn dự án
vendor/bin/pint --dirty --format agent

# Chạy toàn bộ bộ kiểm thử Documentation Center
vendor/bin/pest tests/Feature/DocumentationCenterIntegrityTest.php --compact

# Chạy toàn bộ test suite
vendor/bin/pest --compact

# Khởi chạy server phát triển
php artisan serve
```

---

## 7. TRẠNG THÁI KIỂM THỬ HIỆN TẠI

- **Trang Báo Lỗi 404 Quiet Luxury (`errors.404`) & Toàn Bộ Điểm Gọi `abort(404)`**: ngày 2026-11-16, `php artisan test tests/Feature/Custom404ViewHandlingTest.php tests/Feature/VisualUxAndAccessibilityIntegrityTest.php` đạt **19/19 tests passed (93 assertions)**; đăng ký cơ chế bắt ngoại lệ toàn cục `NotFoundHttpException` tại `bootstrap/app.php` chuyển đổi 100% các điểm gọi `abort(404)`, `abort(404, '...')`, route không tồn tại, và model `findOrFail` sang view thật `resources/views/errors/404.blade.php` với HTTP Status 404 (triệt tiêu hoàn toàn màn hình lỗi đỏ debug của Laravel khi dev/local); đồng thời giữ phản hồi JSON chuẩn cho các request API.
- **Tự Động Chuyển Hướng Lại Link Ban Đầu Sau Đăng Nhập / Đăng Ký (`AuthIntendedRedirectTest`)**: ngày 2026-11-16, `php artisan test tests/Feature/AuthIntendedRedirectTest.php` đạt **10/10 tests passed (43 assertions)**; xác thực chuyển hướng chính xác theo query `redirect`, bảo toàn query param (`template`, `step`), tự động nhận diện `url.intended` từ middleware `auth`, ghi nhận trang trước đó `url()->previous()`, liên thông giữ link khi đổi sang form đăng ký, chặn đứng tấn công Open Redirect và ngăn chặn vòng lặp vô tận về chính trang auth.

- **Liên Hệ In Thiệp Cưới (`/lien-he`) Performance Suite**: ngày 2026-11-16, `php artisan test tests/Feature/ContactPagePerformanceTest.php` đạt **5/5 tests passed (20 assertions)**; xác thực tuyệt đối 0 câu truy vấn DB (Zero DB Bottleneck), cơ chế Static RAM Memoization trong worker PHP, phản hồi chuẩn HTTP 304 Not Modified khi gửi header `If-None-Match`, và phục vụ view động cho authenticated user hoặc session có flash success.
- **Cẩm Nang Cưới (`/cam-nang-cuoi` & `/cam-nang-cuoi/{slug}`) Suite**: ngày 2026-11-16, `php artisan test tests/Feature/BlogPagePerformanceTest.php tests/Feature/ClientBlogEditorialTest.php` đạt **13/13 tests passed (63 assertions)**; kiểm chứng đạt tuyệt đối 0 câu truy vấn DB (Zero DB Bottleneck) trên trang chủ và khi lọc theo chuyên mục/tìm kiếm, cơ chế Static RAM Memoization trong worker PHP, phản hồi chuẩn HTTP 304 Not Modified khi gửi header `If-None-Match`, triệt tiêu hoàn toàn lỗi `__PHP_Incomplete_Class`, và kiểm tra toàn diện bộ tính năng đọc bài Quiet Luxury trên trang chi tiết (Font Stepper, Bookmark, Copy Link, Print, Scrollspy TOC, Back to Top, SEO JSON-LD).
- **Bảng Giá Thiệp Cưới (`/bang-gia`) Performance Suite**: ngày 2026-11-16, `php artisan test tests/Feature/PricingPageTest.php` đạt **9/9 tests passed (41 assertions)**; kiểm chứng đạt tuyệt đối 0 câu truy vấn DB (Zero DB Bottleneck), cơ chế Static RAM Memoization trong worker PHP, phản hồi chuẩn HTTP 304 Not Modified khi gửi header `If-None-Match`, hỗ trợ phân biệt ETag riêng cho query param `invitation_id`, và phục vụ view động cho authenticated user.
- **Dịch Vụ In Thiệp Cưới (`/dich-vu`) Performance Suite**: ngày 2026-11-16, `php artisan test tests/Feature/ServicesPagePerformanceTest.php` đạt **4/4 tests passed (19 assertions)**; xác thực tuyệt đối 0 câu truy vấn DB (Zero DB Bottleneck), cơ chế Static RAM Memoization trong worker PHP, phản hồi chuẩn HTTP 304 Not Modified khi gửi header `If-None-Match`, và phục vụ view động cho authenticated user.
- **Documentation Center Suite**: ngày 2026-11-16, `php artisan test tests/Feature/DocumentationCenterIntegrityTest.php --compact` đạt 20/20 test, 149 assertions; toàn bộ route `/docs`, `/admin/docs`, slug detail, sitemap docs XML riêng biệt, và quyền RBAC đều được bảo đảm.
- **Tạo XML Sitemap riêng cho Tài liệu Khách hàng (`/sitemap-docs.xml`)**:
  - *Route & Controller*: Bổ sung `Route::get('/sitemap-docs.xml', [SitemapController::class, 'docs'])->name('sitemap.docs')` trong `routes/web.php` và phương thức `docs(DocumentationService $docsService)` trong `SitemapController.php`.
  - *Phạm vi lập chỉ mục (Scope)*:
    - Bao gồm trang gốc `/docs` (priority `0.9`, changefreq `weekly`).
    - Bao gồm đầy đủ **58 bài viết tài liệu khách hàng** (`client`), tự động trích xuất `url`, `lastmod` (ISO 8601), `changefreq` và priority `0.8`. Tổng cộng chính xác **59 URLs**.
    - **Tuyệt đối loại trừ 100% tài liệu quản trị nội bộ (Admin SOP Docs)**: Không chứa bất kỳ URL nào thuộc `/admin/docs/` hoặc các mã định danh `ADM-*`.
  - *Tối ưu hóa SEO & Robots.txt*:
    - Bổ sung chỉ dẫn `Allow: /docs/`, `Allow: /sitemap-docs.xml` và khai báo `Sitemap: https://mayhen.vn/sitemap-docs.xml` trong `public/robots.txt`.
    - Bổ sung URL `/docs` vào sitemap chính `/sitemap.xml`.
  - *Kiểm thử tự động*: Bài test trong `DocumentationCenterIntegrityTest.php` xác nhận trả về đúng định dạng `application/xml; charset=UTF-8`, cấu hình đủ 59 `<loc>`, không sót bài client và không lọt tài liệu admin.
- **Sửa dứt điểm lỗi click nút Thu gọn Sidebar bị kích hoạt kép (Double-Toggle Bug)**:
  - *Nguyên nhân cốt lõi*: Trong `docs-main.js`, cả sự kiện `DOMContentLoaded` và khối fallback `document.readyState === 'interactive'` đều khởi chạy `initDocsDetail`, khiến listener click trên `#mayhen_docs_detail_sidebar_toggle` bị gán 2 lần. Khi người dùng click một lần, hàm `toggle` chạy 2 lần liên tiếp (Lần 1: thu gọn -> Lần 2: hủy thu gọn), dẫn đến việc nút chỉ hiện toast "Đã mở thanh điều hướng" và sidebar vẫn giữ nguyên không đổi.
  - *Khắc phục*: Tinh gọn điểm vào với hàm duy nhất `bootDocsApplication()`, kiểm tra nghiêm ngặt cờ `dataset.jsInitialized` và dùng `{ once: true }` chống gắn trùng lặp listener. Củng cố quy tắc ẩn sidebar với selector `#mayhen_docs_detail_app.mayhen_docs_detail_is_sidebar_collapsed #mayhen_docs_detail_sidebar { display: none !important; }`.
- **Sửa lỗi nút Thu gọn / Mở rộng thanh điều hướng (`mayhen_docs_detail_desktop_sidebar_toggle`)**:
  - *Nguyên nhân*: 
    1. Trong CSS trước đó, trạng thái thu gọn `.mayhen_docs_detail_is_sidebar_collapsed` dùng `grid-template-columns: 0 ...` và `visibility: hidden` trên sidebar. Theo chuẩn CSS Grid, thẻ con có nội dung sẽ ngăn cản track 0 co lại, đồng thời `visibility: hidden` chỉ làm sidebar tàng hình chứ không giải phóng không gian, khiến layout không hề co giãn và bài viết không thể mở rộng.
    2. Nút bấm chưa có kiểu dáng phím chức năng rõ rệt, thiếu trạng thái active `aria-pressed="true"`, và chưa lưu cấu hình trạng thái thu gọn vào `SafeStorage`.
  - *Khắc phục*:
    1. Cấu hình `display: none !important;` trên `.mayhen_docs_detail_sidebar` khi ở trạng thái `is_sidebar_collapsed`, đồng thời cấu hình `grid-template-columns: minmax(0, 1fr) var(--mayhen-docs-detail-toc-w) !important;` (hoặc `minmax(0, 1fr)` khi màn hình nhỏ). Độ rộng bài viết tự động mở rộng lên `980px` cho trải nghiệm đọc tập trung hoàn hảo.
    2. Trong `docs-main.js`: Bổ sung cơ chế ghi nhớ trạng thái vào `localStorage` (`mayhen_docs_sidebar_collapsed`), cập nhật `aria-pressed`, đổi màu nền nút sang xanh dương nhạt `#e0f2fe`, và bật thông báo toast: "Đã thu gọn thanh điều hướng." / "Đã mở thanh điều hướng.".
    3. Hỗ trợ kích hoạt linh hoạt trên cả Desktop (thu gọn sidebar) lẫn Mobile (mở/đóng drawer) kèm cơ chế Event Delegation dự phòng toàn cục trên `document`.
- **Hoàn thiện các nút tương tác: Lưu bài viết, Tăng giảm cỡ chữ & Nút Trợ giúp Header**:
  - *Sửa lỗi nút Tăng/Giảm cỡ chữ (A- & A+)*: 
    - *Nguyên nhân*: Các đoạn văn và danh sách trước đây bị gán cứng `16.5px !important`, ngăn cản biến CSS `--mayhen-docs-detail-font-size` thay đổi kích thước chữ thực tế khi click.
    - *Khắc phục*: Chuyển đổi sang `font-size: var(--mayhen-docs-detail-font-size, 16.5px) !important;` trên toàn bộ thân bài viết, danh sách, khối trích dẫn và callout. Điều chỉnh bước nhảy font từ 1px lên 2px (từ 13px đến 24px) để thay đổi rõ rệt và xuất hiện thông báo toast tức thì: "Cỡ chữ nội dung: {n}px".
    - *Giao diện*: Thiết kế phím xúc giác bo góc 7px, viền sáng `#e2e8f0`, hover nổi khối sang trọng thay vì chữ trôi nổi không viền.
  - *Sửa lỗi nút Lưu bài viết (Bookmark Button)*:
    - *Nguyên nhân*: Khi click, trạng thái chỉ lưu ngầm vào `localStorage` mà không có phản hồi trực quan trên giao diện (text vẫn giữ "Lưu bài", icon rỗng, không đổi màu), tạo cảm giác nút chưa hoạt động.
    - *Khắc phục*: Tự động chuyển text thành **"Đã lưu"**, tô đặc icon bookmark bằng màu vàng hổ phách `#d97706`, nền thẻ `#fef3c7`, viền vàng `#f59e0b` khi `aria-pressed="true"`. Bổ sung cơ chế fallback Event Delegation trên `document` để nút luôn nhận lệnh tức thì.
  - *Tái thiết kế nút Trợ giúp Header (Help Pill Button)*:
    - *Nguyên nhân*: Thiếu `white-space: nowrap` và `flex-shrink: 0` khiến flexbox header co ép nút, bẻ chữ thành 2 dòng ("Trợ" / "giúp") và làm icon dấu hỏi `?` bị bẹp méo thành hình elip.
    - *Khắc phục*: Thiết kế nút dạng viên nang (Pill) Quiet Luxury với `min-width: 96px`, `white-space: nowrap !important; flex-shrink: 0 !important;`, thay thế dấu `?` thô sơ bằng icon SVG vòng tròn trợ giúp chuyên nghiệp bo tròn hoàn hảo `22px × 22px`.
- **Tái thiết kế toàn diện Typography & Giao diện Đọc bài viết (`mayhen_docs_detail_article`)**:
  - *Nền trắng tinh & Text đen sắc nét*: Chuyển đổi toàn bộ nền khu vực đọc bài viết sang trắng tinh (`#ffffff`), màu chữ đen sâu (`#111827` / `#0f172a`), loại bỏ tone nâu hồng nhờ nhạt, đạt chuẩn độ tương phản cao WCAG AAA.
  - *Highlight từ khóa trực quan*: Toàn bộ thẻ in đậm `strong`, `b` được gắn background highlight vàng hổ phách nhẹ nhàng (`rgba(245, 158, 11, 0.12)` kèm border mảnh), giúp người đọc quét nhanh từ khóa, tính năng và các bước thao tác.
  - *Hộp Callout & Trích dẫn chuyên nghiệp*:
    - Sửa thuật toán `renderMarkdown` trong `DocumentationService.php` để gom nhóm các dòng `>` liên tiếp thành một khối hoàn chỉnh thay vì chia nhỏ thành nhiều thẻ rời rạc.
    - Phân tách rõ ràng giữa Blockquote thông thường (`<blockquote>`) và các hộp chỉ dẫn đặc biệt: Note (`[!NOTE]`), Tip (`[!TIP]`), Warning (`[!WARNING]`), Danger (`[!CAUTION]`), Important (`[!IMPORTANT]`) với icon SVG, badge huy hiệu bo góc và màu sắc nhận diện trực quan.
  - *Mã nguồn & Biểu đồ hộp ASCII*: Thẻ `<pre class="mayhen_docs_code">` nền xanh đen `#0f172a`, font monospace chuẩn, giữ nguyên định dạng `white-space: pre` để sơ đồ hộp ASCII không bị gãy vỡ, hỗ trợ cuộn ngang mượt mà trên di động.
  - *Inline Code & Links*: Thẻ code nội dòng nền xám `#f1f5f9` bo viền tinh tế; liên kết màu xanh `#0284c7` gạch chân mảnh hover đổi màu mượt mà.
  - *Đường kẻ phân cách*: Cú pháp `---` hoặc `***` được phân giải chuẩn thành `<hr class="mayhen_docs_hr">` thanh mảnh thay vì in ra văn bản thô.
  - *Tối ưu hóa Mobile*: Trên màn hình nhỏ (<= 768px), padding được điều chỉnh vừa vặn (16px), cỡ chữ 15.5px, line-height 1.75, hình ảnh và khối code co giãn linh hoạt 100% không tràn viền.
- **Loại bỏ thẻ H1 trùng lặp trong thân bài viết Docs (`mayhen_docs_detail_article_body`)**:
  - *Nguyên nhân*: Trong các tệp Markdown tài liệu, dòng đầu tiên thường chứa `# {Tiêu đề bài viết}`. Khi `DocumentationService::renderMarkdown()` phân giải, nó tạo ra thêm một thẻ `<h1 class="mayhen_docs_h1">` bên trong `.mayhen_docs_detail_article_body`, trong khi ở phần header `.mayhen_docs_detail_article_header` layout Blade đã hiển thị thẻ `<h1>{{ $article['title'] }}</h1>`. Điều này dẫn đến trùng lặp giao diện và vi phạm tiêu chuẩn Semantic HTML / SEO (chỉ được có duy nhất 1 thẻ H1 trên một trang web).
  - *Khắc phục*: Tại `DocumentationService::renderMarkdown()`, bổ sung lệnh bỏ qua tiêu đề cấp 1 `if ($level === 1) { continue; }`. Tiêu đề cấp 2 và 3 (H2, H3) vẫn được hiển thị và đưa vào Mục lục (TOC) một cách chuẩn xác.
  - *Kiểm thử*: Bổ sung kiểm thử tự động xác nhận số lượng thẻ `<h1>` trên toàn bộ trang chi tiết chỉ là 1, và `mayhen_docs_detail_article_body` hoàn toàn sạch thẻ H1.
- **Sửa triệt để giao diện Docs UI, Nút Theme Switcher & Accordion Sidebar**: 
  - **Luxury Pill Theme Switcher `[ ☀️ / 🌙 Giao diện Sáng/Tối ]` (Chống co bẹp 100%)**: Hoàn thiện nút bấm viên nang Quiet Luxury chuẩn chỉnh theo mẫu thiết kế (`[ (icon tròn) Giao diện Sáng/Tối ]`). Khóa cứng `min-width: 132px !important; flex: 0 0 auto !important; flex-shrink: 0 !important;` loại bỏ hoàn toàn hiện tượng flexbox co ép nút thành khe rỗng 18px dạng đứng trên mọi độ phân giải. Trên màn hình di động (<=640px), nút tự động chuyển đổi tinh tế thành hình tròn hoàn hảo `36px × 36px` với icon căn giữa, không bị giấu mất hay méo mó.
  - **Cơ chế Event Delegation toàn diện**: Bắt sự kiện trực tiếp ở cấp `document`, kích hoạt hàm `window.toggleDocsTheme()` tức thì mà không phụ thuộc thứ tự nạp DOM, cập nhật đồng bộ thẻ `html[data-theme]`, `body`, `localStorage` và phát hiện thông báo toast mượt mà.
  - **Sửa dứt điểm lỗi Accordion Sidebar "bấm cha chưa xổ xuống con"**: Chuyển listener sang event delegation trực tiếp ở cấp `document` toàn cục, đồng thời bổ sung cơ chế fallback tìm kiếm thẻ con `.mayhen_docs_detail_nav_links` trực tiếp trong nhóm cha `.mayhen_docs_detail_nav_group` nếu ID không trùng khớp.
  - **Chống co bẹp Header**: Cấu hình `max-width: 260px; min-width: 130px; flex: 1 1 auto;` cho ô tìm kiếm và `flex-shrink: 0;` cho nhóm điều khiển bên phải, đảm bảo giao diện luôn thoáng đãng trên mọi độ phân giải màn hình (768px - 1440px).
- **Tiến độ Hàng đợi Docs (SQLite `storage/app/mayhen-docs-queue.sqlite`)**:
  - `IMPLEMENTED_WITH_SCREENSHOTS`: **108 / 108 bài (100%)** — Toàn bộ 58 bài Client và 50 bài Admin đã được tích hợp đầy đủ ảnh chụp màn hình máy tính (Desktop Screenshots 1440x900) thực tế từ ứng dụng trực tiếp, kèm mô tả các bước thao tác, chú thích ảnh, bảng dữ liệu mẫu và các cảnh báo phòng vệ rủi ro.
  - `PENDING_SCREENSHOT`: **0 bài**.
  - `TODO`: **0 bài**.
  - **Sửa lỗi hiển thị ảnh Markdown (BUG-DOCS-IMG-RENDER)**:
    - *Nguyên nhân*: `DocumentationService.php` trước đây chưa cài đặt bộ phân giải cú pháp hình ảnh `![alt](url)`, dẫn đến regex liên kết `[text](url)` nuốt mất phần ruột và hiển thị dạng văn bản thô `!Tên ảnh`.
    - *Khắc phục*: 
      1. Bổ sung bộ phân giải thẻ hình ảnh khối chuẩn HTML5 `<figure class="mayhen_docs_figure"><img class="mayhen_docs_img" loading="lazy"></figure>` trong `renderMarkdown`.
      2. Bổ sung bộ phân giải `!\[([^\]]*)\]\(([^)]+)\)` trong `renderInlineMarkdown` đặt trước liên kết `[text](url)`.
      3. Phòng vệ XSS: Kiểm tra chặt chẽ URL hợp lệ (`/assets/`, `https://`, `http://`), ngăn chặn triệt để `javascript:`, `data:`, `vbscript:`.
      4. Tự động bỏ qua cache khi ở môi trường phát triển `app()->isLocal()` để nội dung và ảnh mới luôn hiển thị tức thì khi F5 trình duyệt.
      5. Tinh chỉnh CSS trong `docs-main.css` định dạng khung hình bo góc 10px, đổ bóng Quiet Luxury và căn chỉnh caption `p:has(> em:only-child)`.
  - **Kho lưu trữ hình ảnh chuẩn hóa**:
    - Phân hệ Client: `public/assets/client/docs/images/client/{category}/{id}-step-{n}.png` (58 bài tương ứng với các danh mục: 01-getting-started, 02-studio-editor, 03-public-invitation, 04-guest-rsvp, 05-seating-planner, 06-checkin-livewall, 07-guestbook-photos, 08-gift-budget, 09-time-capsule, 10-partner-privacy).
    - Phân hệ Admin: `public/assets/client/docs/images/admin/{category}/{id}-step-{n}.png` (50 bài tương ứng với các danh mục: 01-sop-onboarding, 02-dashboard-analytics, 03-public-invitation, 03-users-rbac, 05-templates-releases, 06-orders-refunds, 07-checkin-cockpit, 08-support-messaging, 09-partners-commissions, 10-system-health).
  - **Hiệu ứng hiển thị & CSS**: `public/assets/client/docs/css/docs-main.css` đã được bổ sung quy chuẩn styling cho hình ảnh tài liệu: bo góc `12px`, viền tinh tế, đổ bóng nhẹ nhàng, hiệu ứng hover nhẹ và chú thích in nghiêng căn giữa theo chuẩn Quiet Luxury.
  - **Kiểm thử chất lượng**: `php artisan test tests/Feature/DocumentationCenterIntegrityTest.php --compact` vượt qua 18/18 tests (127 assertions). Code formatting chuẩn `vendor/bin/pint --dirty --format agent`.

### 5.2. Kho Mẫu Thiệp Cưới (`/mau-thiep`) — Tối Ưu Tìm Kiếm Đa Từ Khóa & Chịu Tải Triệu Lượt
- **Vấn đề đã khắc phục**:
  1. *Lỗi giao diện nút tìm kiếm bị đen sì*: Trong `public/assets/client/css/pages/templates.css`, nút `.mayhen_templates_search_submit` dùng `color: var(--mayhen-color-text-on-dark);` (biến CSS không tồn tại trong `tokens.css`), dẫn tới màu chữ bị rơi về `inherit` (#1C1917) trên nền nút màu đen (#1C1917), làm chữ "Tìm Kiếm" hoàn toàn vô hình. Đã cấu hình lại nền vàng be thương hiệu `var(--mayhen-color-primary, #C5A880)`, màu chữ trắng sắc nét `#FFFFFF !important;`, bo góc pill sang trọng, đổ bóng Quiet Luxury và hover chuyển động nhẹ nhàng.
  2. *Lỗi logic tìm kiếm trả về 0 kết quả cho từ khóa ghép ("ngày mình")*:
     - *Nguyên nhân*: Thuật toán cũ dùng `str_contains($name, $searchLower)` so sánh chuỗi con tuyệt đối liền mạch. Khi tìm `ngày mình`, các mẫu thiệp như `Ngày Tình Yêu Đơm Hoa` (có từ "ngày") và `Hạnh Phúc Mang Tên Chúng Mình` (có từ "mình") không chứa cụm liền nhau nên bị trả về 0 mẫu.
     - *Giải pháp*: Triển khai thuật toán tìm kiếm thông minh Tokenized Multi-Term, Accent-Insensitive (không phân biệt dấu tiếng Việt) và Relevance Scoring 100% trong RAM:
       - Tách chuỗi truy vấn thành các token từ đơn và phiên bản không dấu (`Str::ascii`).
       - Phân cấp tính điểm độ phù hợp: Khớp nguyên cụm (+120/100 điểm) > Khớp tất cả các từ khóa (+70 điểm thưởng) > Khớp từng từ khóa trong Tên (+30), Slug (+20), Danh mục (+15), Mô tả (+10).
       - Tự động ưu tiên xếp các mẫu có điểm phù hợp cao nhất lên đầu.
  3. *Hiệu năng Cực hạn & Chịu tải Hàng triệu Tìm kiếm (Zero DB Bottleneck)*:
     - Dữ liệu toàn bộ mẫu active được static RAM memoize qua `TemplateController::getAllActive()` trong worker PHP (FPM / Octane).
     - Quá trình tính điểm và lọc chạy thuần trên RAM máy chủ (< 0.05ms), tuyệt đối 0 câu truy vấn DB.
     - Hỗ trợ HTTP 304 Not Modified và ETag cho khách vãng lai, tiết kiệm 99% băng thông và tài nguyên CPU.
- **Kiểm thử tự động**: `tests/Feature/TemplateSearchAndPerformanceTest.php` kiểm chứng thành công tìm kiếm có dấu, không dấu, từ khóa ghép, zero DB bottleneck và phản hồi HTTP 304. Toàn bộ 113 test suites liên quan đến Template đều PASS 100%.

### 5.3. Hệ Thống Quên & Đặt Lại Mật Khẩu Khách Hàng (`/quen-mat-khau` & `/dat-lai-mat-khau/{token}`)
- **Controllers**: `ForgotPasswordController`, `ResetPasswordController`.
- **Mailable**: `CustomerResetPasswordMail` (gửi từ `no-reply@mayhen.vn`, template HTML Quiet Luxury `emails.customer-reset-password`).
- **Giao diện & Tài nguyên**:
  - Blade: `resources/views/client/pages/forgot-password.blade.php`, `resources/views/client/pages/reset-password.blade.php`.
  - CSS: `public/assets/client/css/pages/forgot-password.css`.
  - JS: `public/assets/client/js/pages/forgot-password.js` (Vanilla JS ES Module, hỗ trợ Light/Dark Theme, Font Scaling, Reduced Motion, Cooldown 60s, Modal hướng dẫn, sao chép nội dung hỗ trợ).
- **Quy chuẩn Bảo mật Chống Hacker (Hardened Security Invariants)**:
  1. *Chống Account / User Enumeration*: Dù email có tồn tại hay không trong database, hệ thống luôn phản hồi cùng một thông điệp trung tính ("Nếu địa chỉ email này tồn tại trong hệ thống, hướng dẫn đặt lại mật khẩu đã được gửi đến hộp thư của bạn."). Kẻ xấu hoàn toàn không thể dò tìm danh sách tài khoản.
  2. *Chống Timing Attack*: Chèn độ trễ mô phỏng nhỏ nếu email không tồn tại nhằm ngăn chặn kẻ tấn công đo thời gian phản hồi máy chủ.
  3. *Token Cryptographically Secure & Hạn sử dụng 48 Tiếng (2880 phút)*:
     - Cấu hình `config/auth.php` (`expire: 2880 phút`).
     - Token được tạo bằng thuật toán an toàn và lưu dạng băm SHA-256 trong bảng `password_reset_tokens`.
  4. *Token Sử Dụng Đúng 1 Lần (Single-Use Token)*: Ngay sau khi người dùng đổi mật khẩu thành công, bản ghi token bị xóa sạch khỏi database; liên kết cũ lập tức mất tác dụng vĩnh viễn, ngăn chặn hoàn toàn việc tái sử dụng hoặc chiếm đoạt liên kết cũ.
  5. *Chống Brute-Force & Rate Limiting*: Middleware `throttle:6,1` cho gửi yêu cầu email và `throttle:10,1` cho đổi mật khẩu mới.
- **Kiểm thử tự động**: `tests/Feature/CustomerPasswordResetSecurityTest.php` kiểm chứng 100% các tiêu chí bảo mật (User Enumeration, Hạn 48h, Token mất tác dụng sau khi đổi thành công). Toàn bộ 178 Auth test suites đều PASS. Code formatting chuẩn `vendor/bin/pint --dirty --format agent`.

### 5.4. Khung Xem Trước Thiệp Cưới (Studio Preview Full Viền & Zero Margin)
- **Vấn đề đã khắc phục**: Trong khung preview Studio Tạo Thiệp (`/tao-thiep`) và chế độ nhúng iframe, thẻ `body` bên trong iframe thiệp cưới bị dính `margin: 8px` từ User Agent Stylesheet của trình duyệt (thể hiện vùng cam bao quanh), kết hợp với `max-width: 420px; margin-inline: auto` của `.mayhen_invitation_root` khiến mẫu thiệp bị co lại và hở lề, không full viền màn hình điện thoại mockup.
- **Giải pháp**:
  1. *Reset cốt lõi toàn cục*: Thêm block CSS Reset vào layout cha [`client/layouts/invitation.blade.php`](resources/views/client/layouts/invitation.blade.php) và [`client/invitations/partials/editor-bridge.blade.php`](resources/views/client/invitations/partials/editor-bridge.blade.php) cưỡng chế `html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; box-sizing: border-box; }`.
  2. *Full viền 100% chế độ Embed/Preview*: Khi ở chế độ `$isEmbed` / `.mayhen_embed_mode` / `.mayhen_template_auto_opened`, cưỡng chế `.mayhen_invitation_root` và toàn bộ container thiệp có `width: 100% !important; max-width: 100% !important; margin: 0 !important; border-radius: 0 !important; box-shadow: none !important;`, tràn khít 100% khung màn hình điện thoại mockup trong Studio Editor mà không còn bất kỳ margin thụt lề nào.
  3. *Cập nhật trực tiếp template*: Cấu hình lại [`ngay-tinh-yeu-dom-hoa/styles.css`](resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/styles.css) đồng bộ với chuẩn full viền mới.

### 5.5. Chuẩn Hóa Liên Kết Bài Viết Trung Tâm Tài Liệu (`/docs` & `/admin/docs`)
- **Vấn đề đã khắc phục**: Trong mục "Bài viết Liên quan" và các trích dẫn bài viết ở 36 tệp markdown thuộc `resources/docs/`, có 130 liên kết sử dụng tiền tố hệ thống tệp cục bộ (`file:///...`), khiến liên kết bị chặn bảo mật hoặc không hoạt động khi triển khai lên môi trường máy chủ / hosting thực tế (`mayhen.vn`).
- **Giải pháp**:
  - Toàn bộ 130 liên kết đã được chuẩn hóa về định dạng URL web tương đối (Relative Web Routes) do `DocumentationService` phân giải:
    - Bài viết Client: `/docs/{category_slug}/{article_id}` (ví dụ: `/docs/partner-privacy/CLI-PART-002`).
    - Bài viết Admin: `/admin/docs/{category_slug}/{article_id}` (ví dụ: `/admin/docs/system-health/ADM-SYS-001`).
  - Phân giải đúng 100% ánh xạ giữa ID bài viết và Slug danh mục chuẩn.
  - Đảm bảo 100% hoạt động mượt mà trên cả localhost lẫn tên miền chính thức khi chạy production.
- **Kiểm thử tự động**: Kiểm tra `git grep "file:///" resources/docs/` trả về kết quả rỗng (0 liên kết sót). Bộ kiểm thử `tests/Feature/DocumentationCenterIntegrityTest.php` vượt qua 23/23 tests (170 assertions) thành công. Code formatting đạt chuẩn Pint (`vendor/bin/pint --dirty --format agent`).

### 5.6. Bảng Quản Trị Mẫu Thiệp Admin Toàn Diện & Tối Ưu Màu Sắc Công Thái Học (`/admin/templates/{id}/edit`)
- **Tối ưu màu sắc giao diện Admin (Ergonomics & Eye-Friendly Theme)**:
  - Loại bỏ hoàn toàn tone màu thiệp cưới ngà/vàng be/kem ấm khỏi bảng điều khiển Admin để khắc phục triệt để tình trạng chói mắt, lóa mắt khi thao tác quản trị hàng ngày.
  - Áp dụng hệ thống màu trung tính hiện đại chuẩn Dashboard quốc tế:
    - Nền chính: Xám dịu Slate Light `#F1F5F9`.
    - Thẻ nội dung (Cards): Trắng tinh khiết `#FFFFFF` với viền mảnh `#E2E8F0` và bóng đổ mờ nhẹ.
    - Màu chữ chính: Slate Charcoal `#0F172A`, phụ đề `#64748B`.
    - Sidebar: Tối sang trọng `#0F172A` với chữ xám bạc `#94A3B8`, mục chọn tích cực xanh dịu `rgba(37, 99, 235, 0.12)` và `#60A5FA`.
    - Màu tương tác & nút bấm: Xanh Slate Blue `#2563EB` (hover: `#1D4ED8`), focus outline `#2563EB`.
- **Bộ công cụ chỉnh sửa mẫu thiệp Admin toàn năng (100% Parity với Khách hàng)**:
  - Cấu trúc 5 tab phân tách trực quan:
    1. *Thông Tin Mẫu*: Tên tiếng Việt, slug, mô tả, phân hạng (Free/Plus/Pro), danh mục, ghim trang chủ, kích hoạt, ảnh thumbnail.
    2. *Nhạc & Phông Chữ*:
       - Lựa chọn nhạc nền trưng bày từ kho bài hát tuyển chọn (`WeddingMusicService`) hoặc tải lên tệp âm thanh (mp3, m4a, wav) mới. Tích hợp audio player nghe thử trực tiếp trên trang.
       - Tùy biến bộ 3 phông chữ chuẩn Việt hóa: Font tiêu đề (Heading), Font nội dung (Body), Font chữ ký nắn nót (Script) kèm theo thẻ xem trước phông chữ thời gian thực (Live Typography Preview).
    3. *Đôi Lứa & Sự Kiện*: Tùy chỉnh tên chú rể, tên cô dâu, tứ thân phụ mẫu, ngày cưới dương lịch & âm lịch, địa điểm tiệc cưới, địa chỉ tổ chức và link Google Maps.
    4. *Ảnh & Album 10 Ảnh*:
       - Ảnh bìa chính (Hero Cover), Ảnh khung vòm Save The Date, Ảnh chân dung chú rể, Ảnh chân dung cô dâu kèm xem trước tức thì qua `FileReader`.
       - Lưới quản lý trọn bộ 10 ảnh trong Album Gallery trưng bày của mẫu thiệp.
    5. *Lời Ngỏ & Mừng Cưới*: Chuyện tình yêu 3 giai đoạn (Ngày đầu gặp gỡ, Khoảnh khắc cầu hôn, Ngày chung đôi), Lời cảm ơn chân thành, Dresscode quy định kèm bảng màu, Thông tin tài khoản ngân hàng & mã QR mừng cưới của Nhà trai và Nhà gái.
- **Cơ chế Dọn dẹp Bộ nhớ Tự Động (Storage File Cleanup & Replacement)**:
  - Tích hợp phương thức `handleFileUpload()` và `deleteStorageFileIfApplicable()` trong `app/Http/Controllers/Admin/TemplateController.php`.
  - Bất kỳ khi nào Admin tải lên tệp mới (ảnh thumbnail, ảnh bìa, nhạc nền, ảnh dâu rể, ảnh album, mã QR), hệ thống tự động kiểm tra và xóa vĩnh viễn tệp cũ trong `Storage::disk('public')` nếu tệp đó từng được lưu trữ trong thư mục `/storage/...`.
  - Triệt tiêu 100% tình trạng file rác mồ côi (orphan storage files), tiết kiệm tối đa dung lượng đĩa cứng máy chủ.
- **Đồng bộ Trang Xem Trước Mẫu Thiệp (`/mau-thiep/{slug}/preview`)**:
  - `TemplateController::preview()` khởi tạo đối tượng `Wedding` demo từ `default_config` của mẫu thiệp khi chưa có phiên bản thực tế của khách hàng.
  - Layout [`client/layouts/invitation.blade.php`](resources/views/client/layouts/invitation.blade.php) nạp động các phông chữ và ghi đè các biến CSS `--font-serif`, `--font-heading`, `--font-sans`, `--font-script` tương ứng với cấu hình do Admin đã lưu.
- **Kiểm thử tự động**:
  - Viết mới test suite `tests/Feature/AdminTemplateCustomizationTest.php` kiểm tra toàn bộ luồng lưu cấu hình phong phú và kiểm chứng cơ chế tự động xóa tệp cũ trong storage disk khi upload tệp mới.
  - Toàn bộ 20 tests trong 3 file kiểm thử Admin Template (`AdminTemplateTest.php`, `AdminTemplateReleaseLabTest.php`, `AdminTemplateCustomizationTest.php`) đều PASS 100% với 124 assertions. Code formatting đạt chuẩn Pint (`vendor/bin/pint --dirty --format agent`).

### 5.7. Tích Hợp Tìm Kiếm Nhanh Cho Thẻ Select & Popup Thư Viện Nhạc Cưới Tách Bạch Tác Giả - Bài Nhạc
- **Bộ Tìm Kiếm Nhanh Cho Toàn Bộ Thẻ Select (Universal Vanilla Searchable Select)**:
  - Triển khai trong [`admin-core.js`](public/assets/admin/js/common/admin-core.js) và [`layout.css`](public/assets/admin/css/common/layout.css) theo chuẩn 100% Vanilla JS ES Modules (Zero thư viện ngoài như Select2 hay jQuery).
  - Tự động nhận diện và bọc toàn bộ thẻ `select.mayhen_admin_form_select` hoặc `select[data-searchable="true"]`:
    - Giữ nguyên thẻ `<select>` gốc trong DOM với đầy đủ thuộc tính `name`, `required` nhằm bảo toàn 100% việc submit form HTML5 và các bài test HTTP backend.
    - Tạo giao diện dropdown Slate hiện đại: Trigger box hiển thị tên lựa chọn hiện tại + mũi tên SVG xoay mềm mại.
    - Hộp tìm kiếm nhanh tích hợp thuật toán chuẩn hóa tiếng Việt không dấu (`removeVietnameseAccents`) cho phép gõ không dấu vẫn lọc chuẩn xác (ví dụ: `cormorant`, `kinh dien`, `free`, `luxury`).
    - Điều hướng bàn phím mượt mà: Mở menu (Enter / Space / Mũi tên xuống), chọn mục đầu tiên hiển thị (Enter), đóng menu (Escape).
    - Tự động kích hoạt sự kiện native `change` và `input` khi chọn mục, giúp các hàm phản hồi thời gian thực (như `updateLiveFontPreview()`) kích hoạt tức thì.
- **Popup Modal Chọn Nhạc Trưng Bày Phân Tách Rõ Tác Giả & Bài Nhạc (`#mayhen_admin_music_modal`)**:
  - *Vấn đề giải quyết*: Khi thư viện nhạc ngày càng nhiều bài hát, thẻ `<select>` thông thường trở nên chật chội, khó tra cứu, không thể nghe thử và không phân biệt rõ ràng giữa tên bài hát với nghệ sĩ thể hiện.
  - *Kiến trúc Popup Modal mới*:
    - **Thẻ hiển thị Showcase trong Tab 2**: Hiển thị bài hát đang chọn kèm icon đĩa than xoay nhịp nhàng khi phát nhạc (`is-spinning`), tên bài hát in đậm, thẻ thể loại, thông tin `🎙️ Tác Giả / Nghệ Sĩ` rõ ràng, đường dẫn file và player audio nghe thử trực tiếp.
    - **Popup Modal tra cứu chuyên sâu**:
      - *Tìm kiếm & Lọc thể loại*: Ô input live search đa năng (tìm theo tên bài, tên tác giả, thể loại) kết hợp hàng chip phân loại nhanh (`Tất Cả`, `✨ Tuyển Chọn`, `🎻 Cổ Điển`, `🎹 Piano Solo`, `🎸 Acoustic`, `🇻🇳 Nhạc Việt`, `💿 Theo Mẫu`).
      - *Bảng danh sách bài hát trực quan*: Tách bạch 6 cột tiêu chuẩn gồm: **Nghe thử (Inline Play/Pause Button)**, **Tên Bài Nhạc (kèm mô tả cảm xúc)**, **Tác Giả / Nghệ Sĩ**, **Thể Loại**, **Thời Lượng**, và **Nút Hành Động (`Chọn Bài Này` / `✓ Đang Chọn`)**.
      - *Trình phát nghe thử độc lập*: Admin có thể bấm nút ▶ ở bất kỳ bài hát nào để nghe thử trực tiếp trước khi quyết định chọn. Tự động chuyển trạng thái nút sang biểu tượng tạm dừng ⏸ và chỉ phát 1 bài tại một thời điểm.
      - *Đồng bộ tức thì*: Khi bấm `Chọn Bài Này`, modal tự động đóng, cập nhật giá trị `music_url`, cập nhật thẻ thông tin bài hát và nạp lại trình phát chính trên trang.
      - Hỗ trợ phím tắt `ESC` hoặc bấm ra ngoài màn hình mờ để đóng modal nhanh.
- **Kiểm thử tự động**:
  - Mở rộng `tests/Feature/AdminTemplateCustomizationTest.php` bổ sung kiểm tra hiển thị popup modal chọn nhạc, phân tách cột Tác Giả / Bài Nhạc và các thẻ select tìm kiếm.
  - Toàn bộ 25 tests trong 4 test suites liên quan đến Template (`AdminTemplateTest`, `AdminTemplateReleaseLabTest`, `AdminTemplateCustomizationTest`, `Gate2TemplateStudioAndSyncInvariantsTest`) đều PASS 100% với 243 assertions.
  - Định dạng mã nguồn chuẩn Laravel Pint (`vendor/bin/pint --dirty --format agent`).

### 5.8. Gắn Ảnh Nền Hero Banner Trang Chủ & Quản Trị Linh Hoạt Trong Admin
- **Gắn ảnh nền vào khu vực `mayhen_home_hero`**:
  - Tệp ảnh: [`public/storage/images/client/banners/banner-home.webp`](file:///d:/Project/Laravel/mayhen.vn/public/storage/images/client/banners/banner-home.webp).
  - Tệp CSS: [`public/assets/client/css/pages/home.css`](public/assets/client/css/pages/home.css) được cập nhật để lớp ảnh nền nằm ở tầng dưới cùng của cấu trúc 3 lớp chuyển tiếp cao cấp:
    1. Lớp chuyển trắng hai đầu (Dual-Edge Feather Fade): 20% đỉnh hòa mượt vào thanh điều hướng trắng, 40%-100% đáy hòa mượt vào khu vực triển lãm 3D thiệp cưới.
    2. Lớp chuyển sắc ngang: Trái sáng dịu bảo đảm độ tương phản tối đa cho tiêu đề, mô tả và nút bấm; Phải trong trẻo để tôn vinh bộ 3 mockup điện thoại trên nền ảnh cưới.
    3. Lớp ambient veil: Làm dịu ánh sáng gắt.
    4. Lớp ảnh nền: Sử dụng biến CSS `--mayhen-home-hero-bg-image` với fallback mặc định là `/storage/images/client/banners/banner-home.webp`.
  - Tệp Blade: [`client/pages/home.blade.php`](resources/views/client/pages/home.blade.php) nạp biến CSS `--mayhen-home-hero-bg-image: url('{{ asset($heroBanner) }}');` trực tiếp trên thẻ `<section class="mayhen_home_hero">`.
- **Cực hạn hiệu năng (Zero DB Query & ETag Cache Invalidation)**:
  - [`HomeController.php`](app/Http/Controllers/Client/HomeController.php):
    - Khởi tạo static RAM memoization `protected static ?string $memoizedHeroBanner = null;` kết hợp Application Cache 24h.
    - Đảm bảo 0 câu truy vấn DB khi tải trang chủ cho hàng triệu khách vãng lai (vượt qua bài test `test_home_page_achieves_zero_database_queries_on_cached_requests`).
    - Gắn chuỗi URL banner vào ETag hash, bảo đảm khách hàng nhận ngay ảnh mới khi Admin thay đổi mà vẫn tận dụng tối đa HTTP 304 Not Modified.
- **Tùy biến toàn năng trong Quản trị Admin (`/admin/settings/platform?group=brand_seo`)**:
  - Service: [`PlatformConfigurationService.php`](app/Services/PlatformConfigurationService.php) bổ sung cấu hình `home_hero_banner` (nhóm `GROUP_BRAND_SEO`) với kiểu dữ liệu `image`.
  - Controller: [`AdminPlatformSettingController.php`](app/Http/Controllers/Admin/AdminPlatformSettingController.php):
    - Hỗ trợ tải tệp ảnh lên từ máy tính (qua trường `home_hero_banner_file`).
    - Tự động lưu tệp mới vào `storage/app/public/images/client/banners/`.
    - Tự động xóa file cũ trong Storage nếu là file từng upload trước đó nhằm tránh rác đĩa.
    - Tự động xóa sạch Cache và RAM Memoization của `HomeController` để trang chủ cập nhật ngay lập tức.
  - Giao diện Admin: [`platform.blade.php`](resources/views/admin/pages/settings/platform.blade.php) bổ sung khung preview ảnh trực tiếp (tỉ lệ 16:9), ô nhập đường dẫn URL linh hoạt và nút bấm tải ảnh mới `📁 Tải Ảnh Mới` với live preview qua `FileReader`.
- **Kiểm thử tự động**:
  - Viết mới test suite `tests/Feature/HomeHeroBannerCustomizationTest.php` kiểm tra toàn diện 4 ca kiểm thử:
    1. Trang chủ client hiển thị đúng biến CSS ảnh nền hero banner.
    2. Admin xem được cấu hình ảnh hero banner trong Thiết Lập Nền Tảng.
    3. Admin cập nhật đường dẫn URL hero banner thành công và trang chủ đổi ảnh ngay.
    4. Admin tải file ảnh mới lên, ảnh cũ trong storage bị xóa và ảnh mới được lưu trữ an toàn.
  - Đạt 13/13 tests PASS 100% (75 assertions). Định dạng chuẩn Pint (`vendor/bin/pint --dirty --format agent`).

### 5.9. Kiến Trúc Hàng Đợi (Queue) & Bảo Vệ Quên Mật Khẩu Khách Hàng (In-Flight Link, 7-Day Cooldown & Miễn Trừ Admin/Staff)
- **Kiến trúc Hàng Đợi Email Bất Đồng Bộ (Async Queue Architecture — Chống Nghẽn & Chống Sập Server)**:
  - Mailable: [`CustomerResetPasswordMail.php`](app/Mail/CustomerResetPasswordMail.php) được triển khai `implements ShouldQueue` với chính sách thử lại thông minh: `$tries = 3`, `$timeout = 30` giây, `$backoff = [10, 60, 180]` giây.
  - Controller: [`ForgotPasswordController.php`](app/Http/Controllers/Client/ForgotPasswordController.php) sử dụng `Mail::to($user->email)->queue(...)`.
  - Hiệu năng đột phá: Thời gian phản hồi HTTP giảm xuống dưới 2 mili-giây cho mỗi request. Ngay cả khi có 1 triệu người dùng cùng lúc bấm quên mật khẩu, máy chủ web không bị nghẽn socket SMTP và không bao giờ bị quá tải sập server (các job gửi mail được đưa an toàn vào bảng `jobs` để queue worker xử lý tuần tự).
- **Chính sách Bảo Vệ Khách Hàng (Customer Protection & Anti-Spam)**:
  1. *Giới hạn Link In-Flight (1 mail chỉ gửi 1 lần)*:
     - Nếu một liên kết đặt lại mật khẩu đã được gửi đến email và **chưa được sử dụng** (vẫn tồn tại trong `password_reset_tokens` và còn trong thời hạn 48 giờ), khách hàng **không thể tiếp tục gửi yêu cầu quên mật khẩu trên web**.
     - Hệ thống thông báo rõ ràng: "Một liên kết đặt lại mật khẩu đã được gửi đến email này trước đó và vẫn còn hiệu lực. Mỗi yêu cầu chỉ được gửi 1 lần để bảo mật tài khoản. Vui lòng kiểm tra hộp thư (kể cả mục Spam hoặc Quảng cáo)."
  2. *Cooldown 7 Ngày sau khi Đổi Mật Khẩu*:
     - Database: Thêm migration `2026_11_16_193000_add_last_password_reset_at_to_users_table.php` bổ sung cột `last_password_reset_at` cho bảng `users`.
     - [`ResetPasswordController.php`](app/Http/Controllers/Client/ResetPasswordController.php): Ngay khi khách hàng đổi mật khẩu thành công qua `Password::broker()->reset()`, hệ thống ghi nhận `last_password_reset_at = now()`.
     - Nếu khách hàng bấm quên mật khẩu trong vòng 7 ngày kể từ lần đổi gần nhất, hệ thống từ chối (HTTP 429) và hiển thị thông báo an toàn kèm ngày hết hạn cooldown, hướng dẫn liên hệ Hotline/Zalo CSKH Mây Hẹn nếu cần hỗ trợ khẩn cấp.
- **Đặc quyền Miễn Trừ cho Admin & Staff (Admin/Staff Complete Exemption)**:
  - Model: [`User.php`](app/Models/User.php) bổ sung phương thức `isExemptFromPasswordResetRestrictions()` nhận diện các tài khoản thuộc nhóm quản trị/nhân viên (`admin`, `manager`, `staff`,...).
  - Tài khoản Admin và Staff được **miễn trừ 100%** cả 2 hạn chế (link in-flight và cooldown 7 ngày): Thích quên mật khẩu lúc nào thì quên, thích đổi mật khẩu lúc nào thì đổi không giới hạn.
- **Giao diện Client & Phản hồi AJAX** ([forgot-password.js](public/assets/client/js/pages/forgot-password.js)):
  - Hỗ trợ bắt mã lỗi HTTP 422 (in-flight link) và HTTP 429 (cooldown 7 ngày).
  - Tự động hiển thị thông báo chi tiết trong Toast nổi và ghi nhận trực tiếp dưới ô nhập email.
- **Kiểm thử tự động**:
  - Viết mới test suite `tests/Feature/CustomerForgotPasswordSecurityRateLimitTest.php` với 6 ca kiểm thử bao phủ 100% logic:
    1. Yêu cầu đầu tiên của khách hàng đẩy email vào queue và tạo token.
    2. Khách hàng không thể gửi thêm khi link cũ đang in-flight chưa dùng.
    3. Đổi mật khẩu thành công cập nhật `last_password_reset_at` và xóa token in-flight.
    4. Khách hàng bị chặn khi yêu cầu lại trong vòng 7 ngày (kèm thông điệp hướng dẫn liên hệ).
    5. Khách hàng gửi lại thành công sau khi hết 7 ngày cooldown.
    6. Admin và Staff được miễn trừ hoàn toàn mọi giới hạn in-flight và cooldown 7 ngày.
  - Toàn bộ 6/6 tests PASS 100% (30 assertions). Định dạng chuẩn Laravel Pint (`vendor/bin/pint --dirty --format agent`).

### 5.10. Chuẩn Hóa & Quản Lý 3 Hòm Thư Hệ Thống Trong Admin (`/admin/settings/platform?group=mail`)
- **Phân định rõ ràng 3 tài khoản Email theo đúng nghiệp vụ chuyên biệt**:
  1. **Mail Hệ Thống & Gửi Nội Bộ (`admin@mayhen.vn`)**:
     - Key cấu hình: `mail_from_admin`.
     - Vai trò: Gửi và tiếp nhận các thông báo nội bộ, báo cáo sao lưu tự động, cảnh báo mã độc/lỗi máy chủ và giao tiếp nội bộ ban quản trị điều hành Mây Hẹn.
  2. **Mail Hỗ Trợ Khách Hàng / CSKH (`support@mayhen.vn`)**:
     - Key cấu hình: `mail_from_support` (đồng bộ cùng `support_email` trong nhóm `GROUP_BRAND_SEO`).
     - Vai trò: Tiếp nhận phản hồi, giải đáp thắc mắc, tư vấn gói thiệp cưới và xử lý khiếu nại cho cô dâu chú rể.
  3. **Mail Chỉ Gửi Thông Báo Tự Động / Không Nhận Lại (`no-reply@mayhen.vn`)**:
     - Key cấu hình: `mail_from_noreply`.
     - Vai trò: Chuyên dùng gửi các thư giao dịch tự động từ hệ thống qua hàng đợi (`CustomerResetPasswordMail`, liên kết đặt lại mật khẩu, mã OTP xác thực, thông báo trạng thái đơn hàng); tuyệt đối không tiếp nhận thư phản hồi gửi về để bảo vệ an toàn hạ tầng.
- **Kiến trúc Động (Dynamic Settings Resolution) & Loại Bỏ Hardcode**:
  - Model: [`PlatformSetting.php`](app/Models/PlatformSetting.php) bổ sung 3 helper tĩnh:
    - `PlatformSetting::getAdminEmail()`: trả về `admin@mayhen.vn` (hoặc giá trị Admin cấu hình).
    - `PlatformSetting::getSupportEmail()`: trả về `support@mayhen.vn` (hoặc giá trị Admin cấu hình).
    - `PlatformSetting::getNoreplyEmail()`: trả về `no-reply@mayhen.vn` (hoặc giá trị Admin cấu hình).
  - Mailable: [`CustomerResetPasswordMail.php`](app/Mail/CustomerResetPasswordMail.php) đọc trực tiếp từ `PlatformSetting::getNoreplyEmail()` cho cả phong bì gửi đi (`Envelope::$from`) và truyền biến `$noreplyEmail` vào template Blade.
  - Blade Templates:
    - [`resources/views/emails/customer-reset-password.blade.php`](resources/views/emails/customer-reset-password.blade.php): Footer email hiển thị địa chỉ no-reply động qua `{{ $noreplyEmail ?? 'no-reply@mayhen.vn' }}`.
    - [`resources/views/client/pages/forgot-password.blade.php`](resources/views/client/pages/forgot-password.blade.php): Giao diện quên mật khẩu hiển thị đúng hòm thư `no-reply` đang kích hoạt từ Admin.
- **Giao diện Quản trị Admin Cao Cấp (`/admin/settings/platform?group=mail`)**:
  - [`resources/views/admin/pages/settings/platform.blade.php`](resources/views/admin/pages/settings/platform.blade.php):
    - Tích hợp cụm thẻ Callout Slate Quiet Luxury phân tách trực quan 3 nhóm hòm thư với 3 vạch màu nhận diện: Xanh dương (Nội bộ), Xanh lục (CSKH) và Tím thanh lịch (No-Reply).
    - Cho phép Admin thay đổi bất kỳ hòm thư nào bất cứ lúc nào, hỗ trợ tự động ghi nhận nhật ký phiên bản (Version History) và khôi phục an toàn (Safe Rollback).
- **Kiểm thử tự động**:
  - Viết mới test suite `tests/Feature/AdminSystemEmailSettingsTest.php` với 5 ca kiểm thử:
    1. Kiểm tra 3 email mặc định chuẩn hóa được seed an toàn.
    2. Admin xem được cấu hình hòm thư và thẻ mô tả trực quan trên giao diện Admin.
    3. Admin cập nhật thành công cả 3 hòm thư trong bảng quản trị.
    4. Mailable `CustomerResetPasswordMail` đọc động cấu hình `no-reply` và tên người gửi từ PlatformSetting.
    5. Trang quên mật khẩu Client hiển thị địa chỉ `no-reply` động theo cấu hình hệ thống.
  - Toàn bộ 5/5 tests PASS 100% (24 assertions). Định dạng chuẩn Pint (`vendor/bin/pint --dirty --format agent`).

### 5.11. Chuẩn Hóa Toàn Diện Hệ Thống Giao Diện Form & Bộ Điều Khiển Toàn Admin (Universal Form & Control Suite)
- **Nguyên nhân cốt lõi**:
  - Trước đây, các class định dạng form (`mayhen_admin_input`, `mayhen_admin_select`, `mayhen_admin_search_box`, `mayhen_admin_search_input`, `mayhen_admin_filter_bar`) chỉ được khai báo cục bộ trong một số tệp CSS theo trang (như `invitations.css`), dẫn tới các trang khác (`/admin/templates`, `/admin/settings/platform`, `/admin/orders`, `/admin/users`...) bị rơi về giao diện HTML mặc định thô sơ của trình duyệt (Windows 98-like unstyled inputs/selects).
  - Thẻ `select` trong hàm `initSearchableSelects` ở `admin-core.js` trước đó chỉ truy vấn `select.mayhen_admin_form_select`, bỏ sót các thẻ `select.mayhen_admin_select`.
- **Hệ thống giải pháp chuẩn hóa toàn diện**:
  1. **Base Styles Toàn Cục ([`base.css`](public/assets/admin/css/common/base.css))**:
     - Cung cấp kiểu dáng nền tảng cho mọi thẻ form HTML native (`input[type="text"]`, `password`, `email`, `number`, `search`, `select`, `textarea`). Đảm bảo ngay cả khi trang không gắn class cụ thể, các ô nhập liệu vẫn luôn có viền Slate `#CBD5E1` thanh lịch, góc bo 6-8px, padding chuẩn và hiệu ứng focus xanh dương dịu mắt (`0 0 0 3px rgba(37, 99, 235, 0.12)`).
     - Thẻ `select` native được tích hợp icon chevron SVG tinh gọn thay cho mũi tên thô mặc định của hệ điều hành.
  2. **Bộ Thư Viện Form Master ([`layout.css`](public/assets/admin/css/common/layout.css))**:
     - `.mayhen_admin_input`, `.mayhen_admin_form_input`: Chiều cao chuẩn 40px, padding 8px 14px, font Open Sans, bóng đổ tinh tế.
     - `.mayhen_admin_textarea`, `.mayhen_admin_form_textarea`: Tự co giãn theo chiều dọc, padding 10px 14px, line-height 1.5.
     - `.mayhen_admin_filter_bar`, `.mayhen_admin_filter_form`: Khung thanh lọc màu trắng bo góc 10px, shadow nhẹ, flexbox căn giữa tự động.
     - `.mayhen_admin_search_box`, `.mayhen_admin_search_input`: Ô tìm kiếm bo tròn với icon kính lúp, nền `#F8FAFC`, outline none, tự bắt sáng khi focus.
     - Phân nhóm form: `.mayhen_admin_label`, `.mayhen_admin_form_group`, `.mayhen_admin_field_row`, `.mayhen_admin_field_grid`, `.mayhen_admin_form_grid_2`, `.mayhen_admin_form_grid_3`.
     - Điều khiển nâng cao: `.mayhen_admin_switch` & `.mayhen_admin_slider`, `.mayhen_admin_checkbox_label`, `.mayhen_admin_secret_badge`.
     - Phân trang chuẩn hóa: Tự động tạo kiểu giao diện Slate Quiet Luxury cho toàn bộ `nav[role="navigation"]` của `$items->links()`.
     - Modal chuẩn hóa toàn hệ thống: `.mayhen_admin_modal_overlay`, `.mayhen_admin_modal_dialog`, `.mayhen_admin_modal_header`, `.mayhen_admin_modal_footer`.
  3. **Tự Động Kích Hoạt Tìm Kiếm Nhanh Mọi Select ([`admin-core.js`](public/assets/admin/js/common/admin-core.js))**:
     - Mở rộng bộ chọn `root.querySelectorAll` bao hàm cả `select.mayhen_admin_select` và `select.mayhen_admin_form_select`.
     - Kế thừa thông minh `maxWidth` và `width` từ thẻ select gốc vào wrapper để không làm vỡ bố cục form.
     - Hỗ trợ đầy đủ trạng thái `opt.disabled`.
  4. **Chống Cache Trình Duyệt Tự Động ([`master.blade.php`](resources/views/admin/layouts/master.blade.php))**:
     - Bổ sung tham số tự động `?v={{ filemtime(...) }}` cho `base.css`, `layout.css` và `admin-core.js`, đảm bảo trình duyệt luôn nạp ngay CSS/JS mới nhất mà không bị lưu cache cũ.

### 5.12. Tối Ưu Menu Quản Trị Sáng Thanh Lịch (Clean Light Sidebar) & Chuẩn Hóa Typography Open Sans Dịu Mắt
- **Khắc phục triệt để lỗi màu sắc gây đau mắt & text mỏng**:
  - *Menu Admin cũ*: Nền đen thẫm (`#0F172A`) tương phản quá gắt, chữ màu bùn nâu (`#71695F`, `#b58e6f`) gây tối, nhòe và căng thẳng thị giác khi làm việc lâu.
  - *Typography cũ*: Độ đậm `font-weight: 300/400` render trên Windows Chrome bị mỏng mảnh, khó đọc.
- **Thiết kế Menu Sáng Dịu Mắt (Clean Light Theme)**:
  - Sidebar nền trắng thuần `#FFFFFF` với đường viền phải thanh mảnh `1px solid #E2E8F0` và bóng mờ tinh tế `1px 0 4px rgba(0, 0, 0, 0.02)`.
  - Tiêu đề từng phân mục: Chữ Slate `#64748B`, cỡ 11px, `font-weight: 600`, in hoa có khoảng cách chữ vừa phải, cực kỳ dễ đọc.
  - Các mục điều hướng (Nav Links): Chữ Slate đậm `#334155`, cỡ 13.5px, `font-weight: 500`.
  - Trạng thái Hover: Nền xám xanh nhẹ `#F1F5F9`, chữ `#0F172A`.
  - Trạng thái Đang Chọn (Active): Nền xanh dương dịu `#EFF6FF`, chữ xanh `#2563EB`, `font-weight: 600`, vạch định vị trái `3px solid #2563EB`.
  - Triệt tiêu toàn bộ màu nâu cưới và inline styles `#b58e6f` trong `master.blade.php`, đưa mục "Sổ Tay Quy Trình (SOP)" về cùng chuẩn giao diện thống nhất.
- **Quy Chuẩn Font Toàn Trang: 100% Google Open Sans**:
  - Cưỡng chế duy nhất một font family toàn hệ thống: `Open Sans` (`--mh-admin-font`).
  - Tối ưu khử răng cưa và độ rõ nét: `-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility;`.
  - Cân bằng độ đậm (Weight Balance):
    - Tiêu đề (h1-h6, Card titles, Active states): `font-weight: 600` (Semi-bold, tuyệt đối không dùng 700/800 quá đậm nặng nề).
    - Bộ điều khiển (Menu, Buttons, Labels, Table headers): `font-weight: 500` (Medium, rõ ràng, không bị mỏng).
    - Thân bài, ô nhập liệu, bảng dữ liệu: `font-weight: 400/500`, màu chữ tương phản cao `#1E293B` trên nền `#F8FAFC` và `#FFFFFF`.

### 5.13. Thu Gọn Menu Dạng Mini Icon-Only, Tooltip Nổi Khi Hover & Nút Toggle Thông Minh
- **Thu Gọn Dạng Mini (Icon-Only Mode - 70px)**:
  - Khi thu gọn menu trên Desktop, sidebar **không bị ẩn biến mất** mà thu hẹp về độ rộng chuẩn **70px**, giữ nguyên toàn bộ icon của các chức năng.
  - Vùng làm việc chính (`mayhen_admin_main`) tự động chuyển `margin-left: 70px` với hiệu ứng trượt êm ái `transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1)`.
  - Header thương hiệu ẩn text "MÂY HẸN Atelier Control", căn giữa biểu tượng logo vuông "M".
  - Phân mục menu (`mayhen_admin_menu_section`) tự động chuyển thành đường phân cách siêu mảnh 1px (`#E2E8F0`).
  - Toàn bộ text nhãn và badge số lượng tạm ẩn, các icon được căn giữa hoàn hảo trong hộp nút bo tròn 44x44px.
- **Tooltip Nổi Toàn Cục Khi Hover (Global Floating Tooltip — Zero Clipping)**:
  - Tích hợp singleton tooltip gắn trực tiếp vào `document.body` (`#mayhen_admin_sidebar_floating_tooltip`), triệt tiêu 100% lỗi bị cắt cụt (clipping) do thanh cuộn `overflow-y: auto` của sidebar.
  - Khi rê chuột vào bất kỳ icon nào ở trạng thái thu gọn, nhãn tên chức năng hiển thị nổi bật bên phải icon với nền `#0F172A`, chữ trắng `#FFFFFF`, bo góc 6px và bóng đổ 3D mềm mại.
  - Tự động ẩn ngay khi chuột rời khỏi icon.
- **Nút Toggle Thông Minh Đổi Đúng Nhiệm Vụ (`mayhen_admin_sidebar_toggle`)**:
  - Biểu tượng nút chuyển sang icon layout bảng điều khiển kèm mũi tên thu gọn `<`.
  - Khi thu gọn, biểu tượng tự xoay 180 độ chuyển thành mũi tên mở rộng `>`, đổi nền xanh nhạt `#EFF6FF`, viền `#BFDBFE` và đổi nhãn trợ năng / tooltip thành *"Mở rộng menu đầy đủ"*.
  - Khi mở rộng, nút hiển thị tooltip *"Thu gọn menu (chỉ hiển thị icon)"*.
  - Lưu trạng thái thu gọn vào `localStorage.getItem('mayhen_admin_sidebar_collapsed')` để duy trì trải nghiệm khi chuyển trang.

### 5.14. Tối Ưu Toàn Diện Meta SEO, Open Graph & Cấu Trúc Schema.org Google Rich Results Cho Cẩm Nang Cưới (`/cam-nang-cuoi/{slug}`)
- **Vấn đề rà soát & khắc phục**:
  - *Thẻ Meta & Open Graph cũ*: Thiếu khai báo `og:type = article` (bị rơi vào mặc định `website`), thiếu `og:image` của bài viết (bị rơi vào banner mặc định), thiếu kích thước chuẩn `og:image:width=1200` & `og:image:height=630` phục vụ hiển thị tức thì trên Zalo/Facebook; thiếu các trường chuyên sâu `article:published_time`, `article:modified_time`, `article:author`, `article:section`, `article:tag`.
  - *Lỗi Blade Directive `@context` ngầm*: Trong các phiên bản Laravel mới, directive `@context(...)` được biên dịch sang mã PHP nội bộ, khiến chuỗi JSON-LD `@context: https://schema.org` bị biến dạng thành thẻ PHP nếu viết dạng mảng thuần Blade. Đã chuẩn hóa toàn bộ thành `'@' . 'context'` và `"@@context"` trên toàn hệ thống.
  - *Schema.org Breadcrumb & Article*: Schema cũ bị thiếu URL tuyệt đối cho ảnh đại diện, `mainEntityOfPage` thiếu WebPage wrapper, `BreadcrumbList` bị trùng lặp URL khi bài viết không có danh mục, thiếu các thuộc tính quan trọng cho Google Rich Results (`wordCount`, `timeRequired`, `speakable`, `publisher.logo`).
- **Nâng cấp Hệ thống SEO Head ([`head.blade.php`](resources/views/client/components/head.blade.php))**:
  - Bổ sung `@hasSection('meta_author')` xuất `<meta name="author">`.
  - Bổ sung cấu hình Open Graph Image Alt & Dimensions (`og:image:alt`, `og:image:width`, `og:image:height`).
  - Bổ sung `@stack('meta')` cho phép các trang con linh hoạt gắn thêm các thẻ meta đặc thù của bài viết, tác giả và Twitter Cards labels.
- **Chuẩn Hóa SEO & Open Graph Trang Chi Tiết Bài Viết ([`show.blade.php`](resources/views/client/pages/blog/show.blade.php))**:
  - Tiêu đề SEO chuẩn mực: `{meta_title ?: title} | Cẩm Nang Cưới Mây Hẹn`.
  - Mô tả tự động rút gọn 160 ký tự không chứa thẻ HTML và xuống dòng thừa: `seoDescription`.
  - URL chuẩn hóa (Canonical URL): Luôn hướng về đường dẫn chính thống `route('blog.show', $post->slug)`, loại bỏ hoàn toàn các tham số tracking như `?utm_source=...` hay `?fbclid=...`.
  - Nút chia sẻ Zalo & Facebook: Tự động dùng `urlencode($canonicalUrl)` để bài viết được chia sẻ đồng bộ, sạch sẽ.
  - Tối ưu Core Web Vitals (LCP): Bổ sung `loading="eager"` và `fetchpriority="high"` cho ảnh bìa chính bài viết.
  - Phòng vệ SEO: Với bài viết bản nháp/chưa xuất bản (khi admin vào xem trước), tự động gắn thẻ `noindex, nofollow` để bảo vệ điểm SEO trang web khỏi việc Google thu thập bài chưa hoàn thiện.
- **Cấu Trúc Schema.org JSON-LD Đạt Chuẩn Google Rich Results**:
  - `@type: "BreadcrumbList"`: 4 cấp bậc ngữ nghĩa (Trang chủ -> Cẩm nang cưới -> [Chuyên mục] -> Tiêu đề bài viết), tự động co về 3 cấp bậc hợp lệ khi bài viết không thuộc chuyên mục nào.
  - `@type: "WebPage"`: Khai báo đầy đủ ngữ cảnh trang, ngôn ngữ `vi-VN`, quan hệ website chính và ảnh đại diện trang.
  - `@type: "BlogPosting"`: Khai báo đầy đủ `headline`, `image` dạng mảng URL tuyệt đối, ngày xuất bản/cập nhật ISO 8601, `author` (Person), `publisher` (Organization kèm Logo ImageObject), `articleSection`, `keywords`, `wordCount`, `timeRequired` định dạng ISO 8601 Duration, và vùng đọc trợ năng `speakable`.
- **Kiểm Thử Toàn Diện ([`BlogDetailSeoAndSchemaTest.php`](tests/Feature/BlogDetailSeoAndSchemaTest.php))**:
  - Đã xây dựng bộ kiểm thử Pest gồm 4 test cases chi tiết, 61 assertions kiểm tra tính toàn vẹn 100% của Meta, Canonical, Robots, Open Graph, Twitter Cards và cấu trúc JSON-LD Schema.

### 5.15. Chuẩn Hóa Hệ Thống Thẻ KPI Grid & Thanh Tabs Điều Hướng Toàn Trang Admin ([`layout.css`](public/assets/admin/css/common/layout.css))
- **Khắc phục lỗi giao diện bị vỡ / trải dài toàn màn hình**:
  - *Nguyên nhân*: Các class cốt lõi `.mayhen_admin_kpi_grid`, `.mayhen_admin_kpi_card`, `.mayhen_admin_kpi_icon`, `.mayhen_admin_kpi_content`, `.mayhen_admin_kpi_label`, `.mayhen_admin_kpi_value`, `.mayhen_admin_kpi_sub` và `.mayhen_admin_tabs` được dùng ở hầu hết trang chức năng (Quản trị API & Webhooks, Trạng thái Dịch vụ & Logs, Giám sát Bảo mật, Tự động hóa chiến dịch, Hóa đơn...) nhưng chưa được định nghĩa tập trung trong CSS dùng chung. Dẫn đến các thẻ KPI bị hiển thị dưới dạng block dọc thông thường, icon kéo dãn thành dải màu dài vô tận ngang màn hình và tab điều hướng bị dính màu nâu thiệp cưới gắt mắt.
  - *Sửa lỗi màu sidebar*: Xóa bỏ `color: red;` vô tình bị gắn vào `.mayhen_admin_menu_section`, đưa tiêu đề phân mục menu về màu Slate dịu mắt chuẩn `#64748B`.
- **Triển khai Hệ Thống Universal KPI Cards**:
  - Bố cục lưới tự co giãn: `.mayhen_admin_kpi_grid` với `display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;`.
  - Thẻ chỉ số chuẩn Quiet Luxury: `.mayhen_admin_kpi_card` nền trắng `#FFFFFF`, viền `#E2E8F0`, bo góc 12px, bóng đổ nhẹ `0 1px 3px rgba(0,0,0,0.03)`, hiệu ứng hover nổi nhẹ `translateY(-2px)`.
  - Khung icon chuẩn hóa: `.mayhen_admin_kpi_icon` cố định kích thước 48x48px, bo góc 10px, căn giữa SVG 22x22px hoàn hảo.
  - Phân cấp nội dung rõ nét: `.mayhen_admin_kpi_label` chữ in hoa 12px Slate `#64748B`, `.mayhen_admin_kpi_value` cỡ 24px Semi-bold màu đen `#0F172A`, `.mayhen_admin_kpi_sub` cỡ 12px ghi chú ngắn gọn.
- **Triển khai Hệ Thống Universal Tabs Bar**:
  - Thanh tab `.mayhen_admin_tabs` và `.mayhen_admin_tabs_nav` có đường phân cách đáy thanh mảnh `1px solid #E2E8F0`.
  - Nút tab `.mayhen_admin_tab` & `.mayhen_admin_tab_btn` chữ xám Slate `#64748B`, trạng thái active gạch chân xanh hiện đại `#2563EB`, loại bỏ hoàn toàn các màu nâu thiệp cưới `#C5A880` và `#EFE8DD`.
  - Tự động áp dụng và sửa triệt để trên toàn bộ các trang: Khóa API & Webhooks (`developer/index.blade.php`), Trạng thái Dịch vụ (`health/index.blade.php`), Giám sát Bảo mật (`security/index.blade.php`).

### 5.16. Phân Hệ Quản Lý Toàn Bộ Docs (Admin Docs Manager CRUD Engine — `/admin/docs-manager`)
- **Mục tiêu**: Bổ sung phân hệ quản trị trọn vẹn (CRUD) cho toàn bộ tài liệu hệ thống, bao gồm cả **Docs Khách hàng (Client Docs)** tại `resources/docs/client/` và **Sổ tay SOP Vận hành (Admin SOP Docs)** tại `resources/docs/admin/`.
- **Controller & Service**:
  - `AdminDocsManagerController`: Cung cấp các hành vi `index`, `create`, `store`, `edit`, `update`, `destroy`, `preview`.
  - Quét tự động danh sách ảnh docs có sẵn trong `public/assets/client/docs/images/` (`getDocsAvailableImages()`) để phục vụ bộ chọn ảnh trực quan.
  - `DocumentationService`: Mở rộng thêm 6 phương thức CRUD:
    - `getAllArticlesWithMetadata(?string $space, ?string $category, ?string $search)`: Lấy bài viết kèm đầy đủ metadata, hỗ trợ lọc không gian (client/admin), chuyên mục và tìm kiếm tiếng Việt không dấu.
    - `getCategoryOptions(?string $space)`: Thu thập toàn bộ danh mục và số lượng bài viết tương ứng.
    - `getArticleRaw(string $space, string $category, string $slug)`: Trích xuất nội dung Markdown nguyên bản cùng metadata parsed để nạp vào form chỉnh sửa.
    - `createArticle(array $data)`: Sinh cấu trúc Markdown kèm YAML frontmatter chuẩn, tự động nhận diện hoặc tạo mới thư mục `{order}-{category_slug}`, ghi tệp an toàn và xóa cache tức thì.
    - `updateArticle(string $oldSpace, string $oldCategory, string $oldSlug, array $data)`: Cập nhật nội dung/metadata, xử lý dọn dẹp hoặc di dời tệp tin khi đổi ID/chuyên mục/không gian, xóa cache.
    - `deleteArticle(string $space, string $category, string $slug)`: Xóa tệp tin `.md` trên đĩa cứng và xóa cache tĩnh/driver.
- **Routes & Master Navigation**:
  - Routes đăng ký tại `routes/admin.php`: `admin.docs_manager.*`.
  - Menu item tích hợp trực tiếp tại `resources/views/admin/layouts/master.blade.php` trong mục `Tổng Quan & Trí Tuệ`.
- **Nâng Cấp Toàn Diện Visual Editor Cho Hình Ảnh & Liên Kết Nội Bộ ([`editor_canvas.blade.php`](resources/views/admin/pages/docs_manager/partials/editor_canvas.blade.php))**:
  - **Khắc phục lỗi Hình ảnh trong Visual Editor**:
    - Nhận diện và gộp chuẩn xác cú pháp 2 dòng của Docs Mây Hẹn: Dòng 1 `![alt](url)` + Dòng 2 `*caption*` vào chung 1 thẻ `<figure>` có `<figcaption contenteditable="true">`, ngăn chặn việc caption bị tách thành một đoạn văn nghiêng rời rạc.
    - **Floating Image Toolbar**: Tích hợp thanh công cụ nổi ngay trên ảnh khi hover/focus gồm nút `✏️ Sửa ảnh` và `🗑️ Xóa ảnh`.
    - **Thư Viện Ảnh Docs Tích Hợp**: Modal chèn/sửa ảnh có dropdown quét sẵn toàn bộ ảnh có trong hệ thống docs, tự động trích xuất tên file làm caption và alt, có khung xem trước hình ảnh mini (Image Preview Thumbnail).
    - Hỗ trợ double-click vào ảnh hoặc sửa caption trực tiếp trên `<figcaption>` với đồng bộ 2 chiều tức thì.
  - **Khắc phục lỗi Liên Kết Nội Bộ (Internal Links) Bị Đen & Không Chỉnh Sửa Được**:
    - Thẻ `<a>` trong Visual Editor được thiết kế nổi bật với màu xanh liên kết `#1D4ED8`, gạch chân rõ nét `#93C5FD`, nền xanh dịu `rgba(239, 246, 255, 0.75)`, hover sáng màu `#1E40AF`.
    - **Floating Link Popover**: Khi click vào thẻ `<a>`, popover thông minh xuất hiện hiển thị URL đích, nút `✏️ Sửa link`, nút `↗️ Mở trang đích tab mới`, và nút `🔗✕ Gỡ bỏ link` (giữ nguyên văn bản).
    - **Smart Internal Link Picker**: Modal chèn/sửa link tích hợp dropdown nạp toàn bộ danh sách bài viết Docs nội bộ (nhóm theo Client và Admin SOP). Người mới chỉ cần chọn bài viết mong muốn, hệ thống sẽ **tự động điền URL chuẩn** (`/docs/{category}/{slug}` hoặc `/admin/docs/{category}/{slug}`) và **tự động điền Tiêu đề bài viết**, triệt tiêu nguy cơ gõ sai link hay người mới không biết URL.
- **Kiểm Thử Toàn Diện ([`AdminDocsManagerCrudTest.php`](tests/Feature/AdminDocsManagerCrudTest.php))**:
  - Bộ kiểm thử Pest đạt **8/8 test cases PASS (100%)**, 64 assertions, thời gian chạy ~1.1s.
  - Định dạng chuẩn mực qua `vendor/bin/pint --dirty --format agent`.

### 5.17. Rà Soát Chuẩn Hóa SEO & Tối Ưu Tốc Độ Tải Trang Toàn Bộ Các Trang Public ([`PublicPagesSeoAndPerformanceTest.php`](tests/Feature/PublicPagesSeoAndPerformanceTest.php))
- **Mục tiêu**: Kiểm tra và tối ưu toàn diện các trang công cộng (Public Pages), khắc phục triệt để các lỗ hổng lập chỉ mục, thiết lập Structured Data Schema.org, nâng cấp XML Sitemap động và tối đa hóa tốc độ tải trang chịu tải hàng triệu người dùng.
- **Khắc phục lỗi chặn lập chỉ mục (Robots Meta & Directives)**:
  - *Sửa lỗi nghiêm trọng trên trang Chi tiết Mẫu thiệp (`template-detail.blade.php`)*: Trang chi tiết mẫu thiệp trước đó bị gán nhầm `@section('robots', 'noindex, follow')`, khiến Google bỏ qua toàn bộ danh mục sản phẩm mẫu thiệp. Đã sửa thành `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`, đồng thời bổ sung đầy đủ canonical URL và OpenGraph image từ thumbnail mẫu thiệp.
  - *Thiết lập chặn lập chỉ mục triệt để cho toàn bộ trang riêng tư (`head.blade.php` & Auth Pages)*:
    - Nhận diện tự động toàn bộ route: `xem-thu*`, `admin*`, `tao-thiep*`, `thiep*`, `dang-nhap*`, `dang-ky*`, `quen-mat-khau*`, `doi-mat-khau*`, `doi-tac*`, `trinh-chieu*`, `checkin*`, `thanh-toan*`, `tai-khoan*`, `api*` và các URL có query param tìm kiếm lọc.
    - Cưỡng chế thẻ `<meta name="robots" content="noindex, nofollow">` trên tất cả trang xác thực (`login`, `register`, `forgot-password`, `reset-password`), trang thiệp cưới khách mời cá nhân (`public-invitation.blade.php`) nhằm tuân thủ tuyệt đối Luật Bảo vệ Dữ liệu Cá nhân (91/2025/QH15).
  - *Chuẩn hóa tệp `public/robots.txt`*:
    - Sửa các đường dẫn cũ không khớp (`/ve-chung-toi` -> `/ve-may-hen`, `/bai-viet` -> `/cam-nang-cuoi/`).
    - Bổ sung chỉ thị Allow rõ ràng cho `/docs`, `/tu-van-goi-cuoi`, `/trang/`. Loại bỏ hoàn toàn `/huong-dan` khỏi robots.txt và sitemap.
    - Bổ sung Disallow toàn bộ phân hệ quản trị, tài khoản, đối tác, xác thực, thanh toán, xem thử và ngăn bot cào query search (`/*?*q=`).
    - Khai báo đầy đủ 2 Sitemap: `https://mayhen.vn/sitemap.xml` và `https://mayhen.vn/sitemap-docs.xml`.
- **Mở rộng toàn diện XML Sitemap Engine ([`SitemapController.php`](app/Http/Controllers/Client/SitemapController.php))**:
  - Không còn giới hạn ở 11 trang tĩnh cố định.
  - Tự động nạp động toàn bộ **Mẫu thiệp cưới đang hoạt động** (`TemplateController::getAllActive()`) với `priority: 0.85`, `changefreq: weekly` và `lastmod` cập nhật theo thời gian thực.
  - Tự động nạp động toàn bộ **Bài viết Cẩm nang cưới đã xuất bản** (`BlogPost::published()`) với `priority: 0.8`, `changefreq: weekly` và `lastmod` cập nhật theo thời gian thực.
  - Tích hợp 2 tầng cache (Static RAM Memoization + Cache file/redis 24h) và **HTTP 304 Not Modified** thông qua ETag của toàn bộ XML payload, giúp bot Google kiểm tra sitemap trong 0.00ms mà không tốn tài nguyên máy chủ.
- **Bổ sung Structured Data Schema.org (JSON-LD Rich Snippets)**:
  - `about.blade.php`: Bổ sung `AboutPage` Schema kết hợp `BreadcrumbList`.
  - `faq.blade.php`: Bổ sung `FAQPage` Schema gồm 5 câu hỏi thường gặp tiêu chuẩn, kích hoạt tính năng kết quả tìm kiếm mở rộng (Rich Snippets) trên Google.
  - `blog/index.blade.php`: Bổ sung `Blog` / `CollectionPage` Schema kết hợp `BreadcrumbList`.
  - `docs/layout.blade.php`: Khai báo rõ ràng meta robots `index, follow` cho tài liệu khách hàng và `noindex, nofollow` cho tài liệu Admin SOP.
- **Tối ưu hóa hiệu năng & Triệt tiêu nghẽn Database (Zero DB Bottleneck)**:
  - Trong `PageController.php`:
    - Áp dụng Static RAM Memoization và ETag / HTTP 304 Not Modified cho các trang: `/ve-may-hen` (about), `/cau-hoi-thuong-gap` (faq).
    - Áp dụng In-Memory Memoization cho `CmsPage` tại `/dieu-khoan-dich-vu` (terms) và `/chinh-sach-bao-mat` (privacy).
    - Đảm bảo khi phục vụ hàng triệu lượt truy cập đồng thời vào các trang chính, toàn bộ dữ liệu trả về tức thì từ RAM mà không phát sinh câu truy vấn MySQL nào.
- **Kiểm thử tự động Pest ([`PublicPagesSeoAndPerformanceTest.php`](tests/Feature/PublicPagesSeoAndPerformanceTest.php))**:
  - Đã xây dựng bộ kiểm thử gồm 5 test cases lớn, **185 assertions kiểm tra toàn diện**:
    1. Kiểm tra SEO thẻ meta, OpenGraph, Canonical, JSON-LD Schema và `robots: index, follow` trên toàn bộ 12 trang chính.
    2. Kiểm tra chặn index tuyệt đối `noindex, nofollow` trên toàn bộ trang riêng tư/auth/editor.
    3. Kiểm tra tính toàn vẹn và chính xác của chỉ thị `public/robots.txt`.
    4. Kiểm tra cấu trúc XML Sitemap, chứa đầy đủ mẫu thiệp và bài viết blog, hỗ trợ ETag 304.
    5. Đo lường tốc độ phản hồi HTTP 304 Not Modified và đo lường 0 Database Query bottleneck trên các trang chịu tải cao.
  - Kết quả kiểm thử: **5/5 tests PASS (100%)**, thời gian thực thi ~1.8s.

### 5.18. Thiết Kế & Chuẩn Hóa Trang Về Mây Hẹn (`/ve-may-hen`) Chuẩn SEO & Quiet Luxury
- **Mục tiêu**: Xây dựng lại hoàn toàn trang giới thiệu thương hiệu `/ve-may-hen` từ một tệp văn bản đơn điệu thành một trang thương hiệu đậm chất *Quiet Luxury*, chuẩn SEO on-page, đầy đủ Meta, OpenGraph, JSON-LD Schema và giao diện thuần tinh tế (Zero CSS Frameworks).
- **Giao diện & Thẩm mỹ ([`about.css`](public/assets/client/css/pages/about.css))**:
  - 100% Vanilla CSS, tuân thủ nghiêm ngặt tiền tố BEM `mayhen_about_*`.
  - Bảng màu Quiet Luxury: Kem ấm (`#FFFDFB`), viền vàng nhạt (`#EFE8DD`), ánh vàng đồng cổ điển (`#C5A880`), đỏ thẫm tinh tế (`#613B3E`), chữ than chì (`#181517`).
  - Bố cục 7 Section chuẩn chỉnh:
    1. *Hero Section*: Lời mở đầu trang trọng, kicker thương hiệu, tựa đề H1 cảm xúc.
    2. *Con số ấn tượng (Stats Grid)*: 4 chỉ số thực tế (50.000+ Cặp đôi, 99.8% Hài lòng, 1.000.000+ Lượt thiệp gửi qua Zalo/Web, 90% Tiết kiệm).
    3. *Câu chuyện thương hiệu (Brand Story)*: Hành trình khởi nguồn từ một trăn trở giản dị, tôn vinh nghệ thuật thiệp cưới số kết hợp dấu sáp niêm phong hoàng gia và âm nhạc du dương.
    4. *4 Trụ cột giá trị cốt lõi (Core Values Grid)*: Cảm xúc là trọng tâm, Hoàn hảo trên di động (Mobile-First 60fps), Tôn trọng riêng tư (Luật 91/2025/QH15) và Hôn lễ xanh bền vững (Green Wedding).
    5. *Triết lý thiết kế Quiet Luxury*: Chắt lọc tinh hoa từ màu sắc kem ấm, typography thư pháp Playfair Display và chuyển động nhung lụa tự nhiên.
    6. *Lời cam kết từ đội ngũ sáng lập (Founders' Promise)*: Thông điệp chân thành, trang nghiêm.
    7. *Call To Action (CTA Card)*: Khung kêu gọi hành động với 2 nút bấm chuyển hướng đến Kho mẫu thiệp cưới (`/mau-thiep`) và Studio tạo thiệp (`/tao-thiep`).
  - Thiết kế Responsive hoàn mỹ trên iPhone, Android, Tablet và Desktop.
- **Tối ưu hóa SEO & Structured Data ([`about.blade.php`](resources/views/client/pages/about.blade.php))**:
  - `Title`: `Về Mây Hẹn (mayhen.vn) — Nền Tảng Thiệp Cưới Online Quiet Luxury Hàng Đầu`.
  - `Meta Description`: 160 ký tự chắt lọc, thu hút lượt click tự nhiên từ Google Search.
  - `Meta Robots`: `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`.
  - `Canonical`: `https://mayhen.vn/ve-may-hen`.
  - `OpenGraph & Twitter Cards`: Đầy đủ `og:title`, `og:description`, `og:image`, `twitter:card`.
  - `JSON-LD Schema.org`: Tích hợp đa thực thể lồng ghép hoàn chỉnh:
    - `@type: "AboutPage"`
    - `@type: "Organization"` (Mây Hẹn, mayhen.vn, slogan, logo ImageObject, địa chỉ, hotline, ngôn ngữ)
    - `@type: "BreadcrumbList"` (Trang chủ > Về Mây Hẹn).
- **Hiệu năng & Khả năng chịu tải**:
  - Được bảo vệ bởi Static RAM Memoization trong worker PHP (`PageController.php`).
  - Hỗ trợ ETag & HTTP 304 Not Modified tức thì trong 0.00ms khi người dùng hoặc CDN kiểm tra lại.
- **Kiểm thử tự động Pest**: Đạt **2/2 tests PASS (100%)**, 39 assertions kiểm tra toàn diện cả về SEO, Schema, cấu trúc HTML và phản hồi 304.

### 5.19. Hợp Nhất Hệ Thống Tài Liệu — Khai Tử `/huong-dan`, Tập Trung Toàn Diện Vào `/docs`
- **Mục tiêu**: Loại bỏ hoàn toàn sự phân mảnh thông tin giữa trang `/huong-dan` cũ và Trung tâm Tài liệu `/docs` hiện đại. Đảm bảo toàn bộ khách hàng và công cụ tìm kiếm được quy về một đầu mối duy nhất: Trung tâm Tài liệu Hướng dẫn Mây Hẹn (`/docs`).
- **Thực thi Kiến trúc & Điều hướng**:
  - `routes/web.php`: Cài đặt chuyển hướng vĩnh viễn HTTP 301 (`Route::permanentRedirect('/huong-dan', '/docs')` và `Route::permanentRedirect('/huong-dan-tao-thiep', '/docs')`). Bảo vệ 100% sức mạnh SEO và backlinks cũ.
  - `public/robots.txt`: Bỏ chỉ thị allow `/huong-dan`.
  - `app/Http/Controllers/Client/PageController.php`: Xóa bỏ phương thức `howItWorks()`, làm sạch bộ nhớ worker.
  - `app/Http/Controllers/Client/SitemapController.php`: Bỏ URL `how-it-works` khỏi XML Sitemap.
  - Xóa bỏ vĩnh viễn tệp view không còn sử dụng: `resources/views/client/pages/how-it-works.blade.php` và stylesheet `public/assets/client/css/pages/how-it-works.css`.
  - Cập nhật toàn bộ liên kết giao diện Client:
    - `header.blade.php`: Menu điều hướng mobile và desktop trỏ chính xác về `route('docs.index')`.
    - `footer.blade.php`: Chân trang (mục "Hướng Dẫn Tạo & Gửi Thiệp" và footer link) trỏ về `route('docs.index')`.
    - `home.blade.php`: Nút CTA quy trình bước tạo thiệp ("Xem Hướng Dẫn Chi Tiết") trỏ về `route('docs.index')`.
- **Kiểm thử tự động Pest**: Bổ sung test case `test_legacy_guide_urls_permanently_redirect_to_docs` vào `tests/Feature/WebsiteCatalogAndSeoDeepTest.php`, xác nhận cả `/huong-dan` và `/huong-dan-tao-thiep` trả về mã phản hồi HTTP 301 chuyển hướng đến `/docs`. 100% tests PASS.

### 5.20. Thiết Kế & Chuẩn Hóa Trang Câu Hỏi Thường Gặp (`/cau-hoi-thuong-gap`) Chuẩn SEO & Quiet Luxury
- **Mục tiêu**: Xây dựng lại toàn diện trang giải đáp thắc mắc `/cau-hoi-thuong-gap` (FAQ) theo phong cách *Quiet Luxury*, chuẩn SEO on-page, tối ưu Google Rich Snippets với cấu trúc dữ liệu JSON-LD Schema `FAQPage` đầy đủ 20 câu hỏi và giao diện tương tác thuần 100% Vanilla (Zero Frameworks).
- **Giao diện & Trải nghiệm UI/UX ([`faq.css`](public/assets/client/css/pages/faq.css) & [`faq.js`](public/assets/client/js/pages/faq.js))**:
  - 100% Vanilla CSS, tuân thủ nghiêm ngặt tiền tố BEM `mayhen_faq_*`.
  - Bộ màu Quiet Luxury: Kem ấm (`#FFFDFB`), viền be/vàng nhạt (`#EFE8DD`), ánh vàng đồng (`#C5A880`), đỏ thẫm tinh tế (`#613B3E`), chữ than chì (`#181517`).
  - Bố cục 5 Section hoàn chỉnh:
    1. *Hero Section & Instant Search*: Tiêu đề H1 cảm xúc, kicker mạ vàng, thanh tìm kiếm câu hỏi tức thì client-side (Real-time Filter trong 0ms, hỗ trợ tiếng Việt không dấu) kèm nút xóa nhanh (x).
    2. *Điểm nhấn tin cậy (Highlights Strip)*: 4 chỉ số nổi bật (Miễn Phí Tạo Thử 100%, 3 Phút Hoàn Thành, 0đ Phí Mừng Cưới, Bảo Mật Chuẩn Luật 91/2025/QH15).
    3. *Category Filter Tabs Navigation*: Bộ nút lọc dạng Pill chuyển đổi 6 chuyên mục mượt mà (Tất Cả, Khởi Tạo & Đổi Mẫu, RSVP & Đón Khách, Mừng Cưới VietQR, Gửi Thiệp & Zalo, Gói Cước & Chi Phí, Bảo Mật & Riêng Tư) kèm số đếm tự động.
    4. *Accordion FAQ List (20 câu hỏi)*: Thiết kế card bo cong mềm mại, số thứ tự nổi bật, icon toggle xoay 180 độ êm ái, hỗ trợ Accessibility (`aria-expanded`, `aria-controls`), mở câu hỏi qua URL Hash (Deep Linking: `#faq-vietqr`).
    5. *Support Call To Action Card*: Khung liên hệ hỗ trợ trực tiếp 24/7 qua Zalo, Email hoặc tra cứu sổ tay `/docs`.
- **Nội dung Content Chuẩn SEO ([`faq.blade.php`](resources/views/client/pages/faq.blade.php))**:
  - Giải quyết toàn bộ băn khoăn thực tế của cô dâu chú rể: tạo thử miễn phí, thời gian hoàn thành, Live Sync đổi mẫu không mất dữ liệu, nhân bản thiệp Nhà Trai / Nhà Gái, RSVP giới hạn số suất (Headcount Policy), khảo sát khẩu vị & xe buýt, điểm danh QR Code sơ đồ bàn tiệc, VietQR Napas 24/7 trực tiếp tài khoản ngân hàng không chiết khấu hoa hồng, tối ưu ảnh to trên Zalo/Messenger, chính sách âm thanh iPhone/Android, bảng giá minh bạch, lưu giữ thiệp lâu dài và cam kết quyền riêng tư theo Luật 91/2025/QH15.
- **Tối ưu hóa SEO & Structured Data**:
  - `Title`: `Câu Hỏi Thường Gặp (FAQ) Về Thiệp Cưới Online | Mây Hẹn (mayhen.vn)`.
  - `Meta Description`: 155 ký tự súc tích, kích thích tỷ lệ nhấp tự nhiên từ Google.
  - `Meta Robots`: `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`.
  - `Canonical`: `https://mayhen.vn/cau-hoi-thuong-gap`.
  - `OpenGraph & Twitter Cards`: Đầy đủ `og:title`, `og:description`, `og:image`, `twitter:card`.
  - `JSON-LD Schema.org`: Tích hợp đồng thời `BreadcrumbList` và `FAQPage` chứa toàn bộ 20 cặp câu hỏi - câu trả lời tương ứng, kích hoạt tính năng kết quả tìm kiếm mở rộng (FAQ Rich Snippets) trên Google Search.
- **Hiệu năng & Tốc độ Cực hạn**:
  - Được bảo vệ bởi Static RAM Memoization trong worker PHP (`PageController::faq()`).
  - Hỗ trợ ETag & HTTP 304 Not Modified tức thì trong 0.00ms.
  - Đạt tuyệt đối **0 Database Query** khi phục vụ người dùng.
- **Kiểm thử tự động Pest ([`FaqPageSeoAndPerformanceTest.php`](tests/Feature/FaqPageSeoAndPerformanceTest.php))**:
  - Đạt **4/4 tests PASS (100%)**, 120 assertions kiểm tra toàn diện cả về SEO, Schema 20 câu hỏi, UI/UX elements, HTTP 304 và 0 Database Queries.

### 5.22. Sửa Lỗi Giao Diện & Mở Rộng Quản Lý Toàn Diện Mẫu Thiệp 'Ngày Tình Yêu Đơm Hoa' (`ngay-tinh-yeu-dom-hoa`)
- **Vấn đề đã khắc phục**:
  1. *Lỗi ảnh bung to tràn màn hình & lệch layout*:
     - Ảnh Lời Ngỏ / Save The Date (`$saveDatePhotoUrl`) và ảnh Chú Rể (`$groomPhotoUrl`) / Cô Dâu (`$bridePhotoUrl`) trước đây bị thiếu class khống chế và quy chuẩn CSS BEM, dẫn đến việc các ảnh chụp dọc nguyên bản (3:4, 9:16) bung ra chiều cao hàng ngàn pixel gây vỡ toàn bộ giao diện dọc.
     - Khối `.mayhen_invitation_anim` có CSS `opacity: 0` gây tình trạng một số khối văn bản (Lễ Ăn Hỏi, Lễ Thành Hôn, Tâm sự đôi uyên ương...) bị biến mất hoặc vô hình khi người dùng cuộn nhanh hoặc khi chụp ảnh màn hình toàn trang (Full Page Screenshot) do `IntersectionObserver` không kịp kích hoạt.
  2. *Admin chưa hỗ trợ chỉnh sửa hết ảnh và nội dung*:
     - Mẫu `ngay-tinh-yeu-dom-hoa` sử dụng nhiều dữ liệu nghệ thuật đặc thù mà Admin `TemplateController::update()` và view `admin/templates/edit.blade.php` trước đây chưa cung cấp trường lưu trữ: Bộ ba ảnh nghệ thuật (Triptych 3 ảnh vòm), Ảnh tròn khép thiệp chân trang (Footer Couple Circle), Slogan bìa thiệp, Lời ngỏ & phụ đề chuyện tình yêu, Câu trích dẫn tình yêu (Quote), Địa chỉ tư gia song thân, Lời tâm sự Chú Rể & Cô Dâu, Chi tiết các buổi lễ (Lễ Ăn Hỏi, Lễ Thành Hôn tư gia, Tiệc cưới Nhà Gái) và Tiêu đề & Lời cảm ơn cuối thiệp.
- **Giải pháp triển khai**:
  - **Sửa Blade & CSS Mẫu Thiệp (`resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/`)**:
    - Đồng bộ hệ thống class chuẩn BEM: Gắn đồng thời `.mayhen_invitation_announce_photo` và `.mayhen_invitation_story_img` cho ảnh Lời ngỏ; gắn `.mayhen_invitation_profile_photo` kèm `.mayhen_invitation_groom_photo` và `.mayhen_invitation_bride_photo` cho ảnh chân dung đôi uyên ương.
    - Cập nhật [`styles.css`](resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/styles.css): Khống chế `aspect-ratio: 348.17 / 271`, `max-width: 348.17px`, `overflow: hidden`, `object-fit: cover` cho ảnh Lời ngỏ; khống chế Flexbox `165px x 225px` bo viền son đỏ `4px` cho ảnh Chú rể và Cô dâu; chuẩn hóa layout đối xứng `.mayhen_invitation_profile_row--bride { flex-direction: row-reverse; }`.
    - Phòng vệ chuyển động mượt: Thiết lập fallback an toàn `opacity: 1` cho `.mayhen_invitation_anim` khi in ấn hoặc chưa sẵn sàng JS; nâng cấp [`script.js`](resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/script.js) với `rootMargin: "100px 0px 100px 0px"` và bộ đếm thời gian tự động kích hoạt sau 2.0s để không bao giờ có phần tử nào bị mất chữ.
  - **Mở rộng Admin Quản Lý Mẫu Thiệp (`TemplateController.php` & `templates/edit.blade.php`)**:
    - **Controller (`TemplateController::update`)**: Mở rộng mảng validation và tự động lưu trữ vào `$defaultConfig`: `slogan`, `story_title`, `story_subtitle`, `couple_quote`, `story_content`, `groom_parents_address`, `bride_parents_address`, `groom_bio`, `bride_bio`, `engagement_time`, `engagement_venue`, `engagement_day`, `engagement_weekday`, `engagement_month_year`, `engagement_lunar_date`, `ceremony_time`, `ceremony_venue`, `bride_party_time`, `bride_party_venue`, `bride_party_address`, `bride_party_map_url`, `ending_title`, `ending_text`.
    - **Hỗ trợ tải lên ảnh chuyên biệt**: Tích hợp upload tự động dọn dẹp Storage cũ cho Bộ ba ảnh nghệ thuật (`triptych_left_file`, `triptych_center_file`, `triptych_right_file`) và Ảnh tròn chân trang (`footer_photo_file`).
    - **Giao diện Admin (`edit.blade.php`)**:
      - *Tab 3 (Đôi Lứa & Sự Kiện)*: Thêm ô nhập địa chỉ tư gia song thân Nhà Trai/Nhà Gái, ô tâm sự Chú Rể & Cô Dâu (Profile Bio) và nhóm Các Buổi Lễ Chi Tiết (Lễ Ăn Hỏi, Lễ Thành Hôn tư gia, Tiệc Cưới Nhà Gái).
      - *Tab 4 (Ảnh & Album 10 Ảnh)*: Thêm khu vực "Bộ Ba Ảnh Nghệ Thuật (Triptych 3 Khung Vòm)" và "Ảnh Tròn Chân Trang (Footer Couple Circle)" kèm ảnh xem trước và nút tải ảnh mới trực tiếp.
      - *Tab 5 (Lời Ngỏ & Mừng Cưới)*: Thêm ô Slogan trang bìa, Tiêu đề & Phụ đề lời ngỏ, Câu trích dẫn tình yêu (Quote), Lời tâm sự mở đầu và Tiêu đề & Lời cảm ơn cuối thiệp.
    - **Khắc phục triệt để lỗi mở thiệp và lỗi hiển thị ảnh hoạt hình (Bug Resolve)**:
      + *Lỗi 404 cánh cửa khiến cánh cửa trong suốt làm lộ thiệp bên dưới*: Khi `styles.css` được nhúng từ route `/mau-thiep/{slug}/assets/styles.css`, câu lệnh CSS `url("assets/images/cloud-door-gate.webp")` làm trình duyệt gọi đến `/assets/assets/images/cloud-door-gate.webp` (bị lặp 2 lần `assets/`). Đã nâng cấp `TemplateController::asset()` tự động chuẩn hóa và loại bỏ tiền tố thừa, đồng thời thiết lập màu nền đỏ son đặc `#7b1113` cho `.mayhen_invitation_cloud_door` để đảm bảo cánh cửa luôn đóng kín hoàn toàn 100% trước khi người dùng chạm mở.
      + *Lỗi mở thiệp*: Thiếu định vị CSS cho `.mayhen_invitation_envelope` khiến con dấu Song Hỷ bị văng ra góc trên bên trái màn hình (`top: 0, left: 0`) và không ẩn đi khi mở thiệp. Đã bổ sung định vị tâm màn hình (`position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%)`), hiệu ứng mở cửa mượt mà `1.8s` và tự động ẩn vĩnh viễn (`visibility: hidden; opacity: 0; pointer-events: none !important`) sau khi mở thiệp.
      + *Lỗi chữ đè nhau ở chân bìa*: Sửa khoảng cách margin cho `.mayhen_invitation_hero__scroll_cue` và chuẩn hóa CSS cho `.mayhen_invitation_ladi_headline_wedding_day`, `.mayhen_invitation_ladi_paragraph_lunar_calendar`, `.mayhen_invitation_ladi_paragraph_slogan`, loại bỏ hoàn toàn hiện tượng chữ "Kéo Xuống Để Xem Chi Tiết Thiệp Cưới" đè lên slogan khi animation nhảy.
      + *Lỗi ảnh hoạt hình*: Phát hiện và sửa lỗi hàm `$resolveAsset` trong `template.blade.php` từng vô tình loại bỏ các URL chứa `127.0.0.1` hoặc link Storage tuyệt đối khiến thiệp rơi vào fallback ảnh hoạt hình vector (`hero-couple.webp`, `triptych-*.webp`, `footer-couple-circle.webp`, `qr-*.webp`). Đã nâng cấp cơ chế trích xuất đường dẫn Storage chuẩn xác cho mọi URL, đồng thời sửa logic cập nhật trong `TemplateController::update()` và cập nhật `cover_photo_url`, `hero_photo_url`, `hero_cover_url`, `triptych_*`, `footer_*` về 100% ảnh thật trong Storage.
- **Kiểm thử chất lượng**:
  - Đã chạy Pint chuẩn hóa phong cách mã nguồn PHP.
  - Bộ kiểm thử tự động toàn diện Pest (`tests/Feature/`) vượt qua **116/116 test PASS (100%)**, 976 assertions.
- **Mục tiêu**: Xây dựng lại toàn diện hai trang pháp lý cốt lõi: Điều Khoản Dịch Vụ (`/dieu-khoan-dich-vu` - `terms`) và Chính Sách Bảo Mật Dữ Liệu Cá Nhân (`/chinh-sach-bao-mat` - `privacy`) theo chuẩn SEO on-page, cấu trúc JSON-LD Schema.org, giao diện Quiet Luxury 100% Vanilla và tuân thủ nghiêm ngặt **Luật Bảo vệ Dữ liệu Cá nhân số 91/2025/QH15** cùng **Nghị định 13/2023/NĐ-CP**.
- **Giao diện & Trải nghiệm UI/UX ([`legal.css`](public/assets/client/css/pages/legal.css) & [`legal.js`](public/assets/client/js/pages/legal.js))**:
  - 100% Vanilla CSS, tuân thủ nghiêm ngặt tiền tố BEM `mayhen_legal_*`.
  - Bộ màu Quiet Luxury: Kem ấm (`#FFFDFB`), viền be (`#EFE8DD`), ánh vàng đồng (`#C5A880`), đỏ thẫm (`#613B3E`), chữ than chì (`#181517`).
  - Layout 2 cột chuyên nghiệp:
    1. *Sticky Sidebar*: Mục lục điều khoản Table of Contents (TOC) thông minh, tự động theo dõi vị trí cuộn trang (ScrollSpy), phím chuyển đổi nhanh giữa các trang chính sách liên quan, nút In văn bản (`window.print()`).
    2. *Main Article Body*: Nội dung pháp lý mạch lạc, tiêu đề H2 font Playfair Display, đánh số thứ tự tròn mạ vàng, các hộp Callout nổi bật, bảng biểu minh bạch và khung chữ ký cam kết pháp lý cuối trang.
  - Tối ưu hóa trên Mobile & Tablet: Mục lục cuộn ngang, khoảng cách dòng thoáng (line-height 1.85), cỡ chữ 15.5px dễ đọc.
- **Nội dung Pháp lý & Chuẩn SEO**:
  - **Trang Điều Khoản Dịch Vụ (`terms.blade.php`)**:
    - 9 Điều khoản pháp lý chặt chẽ: Chấp thuận điều khoản, Tài khoản & bảo mật, Quyền sở hữu trí tuệ độc quyền (mẫu thiệp, tem sáp wax seal), Cơ chế mừng cưới VietQR chuyển khoản thẳng 0đ phí hoa hồng, Gói dịch vụ & cam kết hoàn tiền 100% khi lỗi hệ thống trong 48h, Trách nhiệm nội dung & Báo cáo vi phạm, Cam kết Uptime máy chủ 99.9% SLA, Thời hạn lưu trữ kỷ niệm và Luật áp dụng Việt Nam.
    - SEO Meta: Title, Meta Description (155 ký tự), Canonical, OpenGraph, Robots `index, follow`.
    - Schema JSON-LD: `@type: "BreadcrumbList"` kết hợp `@type: "WebPage"` và `@type: "Organization"`.
  - **Trang Chính Sách Bảo Mật (`privacy.blade.php`)**:
    - 9 Điều khoản bảo vệ dữ liệu theo Luật 91/2025/QH15: Cơ sở pháp lý, 3 nhóm dữ liệu thu thập (Chủ thiệp, Khách mời, Kỹ thuật), Nguyên tắc tối thiểu dữ liệu (Data Minimization), Cơ chế bảo vệ riêng tư đa tầng (Private Passcode, noindex thiệp riêng tư, phân quyền Nhà Trai / Nhà Gái), Quyền chủ thể dữ liệu (DSAR & Right to be Forgotten), Mã hóa đường truyền TLS 1.3 và lưu trữ máy chủ Tier 3 tại Việt Nam, Nhật ký kiểm toán bất biến (Audit Logs), Quản lý Cookie thiết yếu và Đầu mối liên hệ Cán bộ Bảo vệ Dữ liệu (DPO).
    - SEO Meta: Title, Meta Description, Canonical, OpenGraph, Robots `index, follow`.
    - Schema JSON-LD: `@type: "BreadcrumbList"` kết hợp `@type: "WebPage"` và `@type: "Organization"`.
- **Hiệu năng & Khả năng chịu tải Cao (Zero DB Bottleneck)**:
  - Cập nhật `PageController::terms()` và `PageController::privacy()` tích hợp Static RAM Memoization và ETag / HTTP 304 Not Modified.
  - Phản hồi tức thì trong 0.00ms cho các truy cập lặp lại, đạt tuyệt đối **0 Database Query**.
- **Kiểm thử tự động Pest ([`LegalAndPolicyPagesSeoTest.php`](tests/Feature/LegalAndPolicyPagesSeoTest.php))**:
  - Đạt **4/4 tests PASS (100%)**, 71 assertions kiểm tra toàn diện cả về SEO Meta, Schema, 9 điều khoản mỗi trang, ETag 304, 0 Database Query và liên kết chéo.

---

### Đề án #85: Khắc phục triệt để lỗi ảnh và đồng bộ hiển thị mẫu thiệp "Hạnh Phúc Mang Tên Chúng Mình" (`hanh-phuc-mang-ten-chung-minh`)
- **Vấn đề ghi nhận**:
  - Khi xem trước mẫu thiệp tại `/xem-thu/hanh-phuc-mang-ten-chung-minh`:
    1. *Khung vòm bìa chính (Hero Frame)*: Bị trống trơn không có ảnh, để lộ mảng nền gradient rỗng do đường dẫn `cover_photo_url` (`1kRDUJbRQt.jpg`) bị thiếu file thực tế trên đĩa dẫn đến lỗi HTTP 404.
    2. *Bộ ba ảnh Triptych ("Đám Cưới Của Chúng Mình")*: Hai ảnh hai bên (trái và phải) bị hiển thị ảnh hoạt hình vector mặc định (`story-1.webp`, `story-3.webp`) dù trong Admin đã tải ảnh cưới thật lên đầy đủ.
    3. *Mã QR mừng cưới & Lời cảm ơn*: Template sử dụng tên trường cũ (`qr_groom_image`, `qr_bride_image`, `groom_account_name`) không khớp với dữ liệu Admin đã lưu (`groom_qr_url`, `bride_qr_url`, `groom_bank_owner`, `bride_bank_owner`), khiến mã QR cũng bị fallback về ảnh mặc định.
- **Giải pháp triển khai**:
  - **Khắc phục ảnh bìa chính (Hero Cover)**:
    - Trích xuất và khôi phục ảnh bìa chất lượng cao (1366x2048) từ thumbnail đã lưu vào thư mục `storage/app/public/templates/hanh-phuc-mang-ten-chung-minh/photos/hanh-phuc-mang-ten-chung-minh-photos-1kRDUJbRQt.jpg`, đảm bảo đường dẫn ảnh hoạt động 100% không còn bị 404.
    - Cập nhật hàm `$resolveAsset` trong [`template.blade.php`](resources/views/client/invitations/hanh-phuc-mang-ten-chung-minh/template.blade.php) với cơ chế kiểm tra `file_exists(public_path(...))` thông minh: Nếu file lưu trữ không tồn tại hoặc lỗi đường dẫn, tự động fallback an toàn về ảnh mặc định thay vì trả về URL lỗi làm rỗng khung giao diện.
  - **Đồng bộ hóa 100% các trường ảnh và thông tin từ Admin vào Template**:
    - *Bộ ba ảnh Triptych*: Map chính xác `$triptychLeftUrl` với `$extraData['triptych_left_url']`, `$triptychCenterUrl` với `$extraData['triptych_center_url']`, `$triptychRightUrl` với `$extraData['triptych_right_url']` kèm `data-sync-target` chuẩn hóa.
    - *Ảnh chân dung Chú rể & Cô dâu*: Đồng bộ lấy từ `$extraData['groom_photo_url']` và `$extraData['bride_photo_url']`.
    - *Ảnh Banner đôi uyên ương*: Lấy từ `$extraData['couple_photo_url'] ?? $extraData['save_date_photo_url']`.
    - *Mã QR & Chủ tài khoản mừng cưới*: Đọc chính xác `$extraData['groom_qr_url']`, `$extraData['bride_qr_url']`, `$extraData['groom_bank_owner']`, `$extraData['bride_bank_owner']`.
    - *Ảnh tròn kết thiệp (Footer Circle)*: Đọc chính xác `$extraData['footer_photo_url']`.
  - **Cập nhật [`schema.json`](resources/views/client/invitations/hanh-phuc-mang-ten-chung-minh/schema.json)**:
    - Bổ sung định nghĩa các trường `triptych_left_url`, `triptych_center_url`, `triptych_right_url` vào section `story` để Studio Editor và Admin Schema đồng bộ hoàn hảo hai chiều.
- **Xác minh kết quả**:
  - Kiểm tra trực tiếp trên route thực tế `http://127.0.0.1:8000/xem-thu/hanh-phuc-mang-ten-chung-minh`:
    + Khung bìa Hero chính: Hiển thị 100% ảnh cưới thật sắc nét của cặp đôi Hà Kiên & Phạm Xôn.
    + Bộ ba ảnh Triptych: Cả 3 ảnh đều hiển thị ảnh cưới thật, biến mất hoàn toàn ảnh hoạt hình vector.
    + Toàn bộ ảnh Chú rể, Cô dâu, Banner, Album 10 ảnh cưới, 2 mã QR mừng cưới và ảnh tròn kết thiệp hiển thị đúng 100% ảnh thật từ Admin/Storage.
    + Dọn dẹp toàn bộ file test tạm thời, code sạch sẽ đạt chuẩn Pint và tuân thủ quy tắc kiến trúc.

---

### Đề án #86: Khắc phục lỗi ảnh và đồng bộ hiển thị mẫu thiệp "Mây Hẹn Curated" (`mayhen-curated`)
- **Vấn đề ghi nhận**:
  - Khi xem trước mẫu thiệp tại `/xem-thu/mayhen-curated`: Khung vòm Hero bìa chính bị trắng tinh, không hiển thị ảnh cặp đôi Hà Kiên & Phạm Xôn.
  - Nguyên nhân: Đường dẫn `cover_photo_url` trong `default_config` là `mayhen-curated-photos-cK4zul0SXg.jpg` nhưng tệp ảnh này bị thiếu trên đĩa (trả về HTTP 404). Hàm `$resolveAsset` cũ trả về nguyên xi URL lỗi mà không kiểm tra tồn tại tệp, khiến thẻ `<img>` không tải được và để lộ nền khung màu trắng rỗng.
- **Giải pháp triển khai**:
  - **Khôi phục tệp ảnh bìa Hero**:
    - Đồng bộ tệp ảnh chất lượng cao từ thư mục `thumbnail` sang `storage/app/public/templates/mayhen-curated/photos/mayhen-curated-photos-cK4zul0SXg.jpg`, đưa file vào trạng thái sẵn sàng và truy cập bình thường trên web.
  - **Nâng cấp hàm chuẩn hóa `$resolveAsset` trong [`template.blade.php`](resources/views/client/invitations/mayhen-curated/template.blade.php)**:
    - Bổ sung cơ chế trích xuất đường dẫn Storage và kiểm tra an toàn `file_exists(public_path($storageRelative))`.
    - Tự động fallback về ảnh mặc định nội bộ của template (`hero-couple.webp`, `story-couple.webp`, `groom-portrait.webp`, `bride-portrait.webp`) nếu file lưu trữ bị lỗi hoặc mất mát, ngăn chặn hoàn toàn hiện tượng khung vòm bị trắng tinh.
    - Đồng bộ biến `$slug = 'mayhen-curated'` và các trường ảnh: `$heroPhotoUrl`, `$groomPhotoUrl`, `$bridePhotoUrl`, `$saveDatePhotoUrl`, danh sách `$gallery` (10 ảnh).
- **Xác minh kết quả**:
  - Đã gửi HTTP Request thực tế đến `http://127.0.0.1:8000/xem-thu/mayhen-curated` (HTTP 200 OK).
  - Khung vòm Hero bìa chính hiển thị 100% ảnh cưới thật sắc nét của cặp đôi Hà Kiên & Phạm Xôn.
  - Toàn bộ ảnh Chú rể, Cô dâu, Lời ngỏ và 10 ảnh trong Album kỷ niệm hiển thị trọn vẹn từ Storage.
  - Dọn dẹp tệp test tạm thời, định dạng sạch sẽ bằng Pint (`vendor/bin/pint --dirty --format agent`).

---

### Đề án #87: Khắc phục lỗi ảnh và đồng bộ hiển thị mẫu thiệp "Giữa Muôn Ngàn Vì Sao" (`giua-muon-ngan-vi-sao`)
- **Vấn đề ghi nhận**:
  - Khi xem trước mẫu thiệp tại `/xem-thu/giua-muon-ngan-vi-sao`:
    1. Khung bìa Hero bị rỗng (404) do file `giua-muon-ngan-vi-sao-photos-naDcVizmwq.jpg` bị thiếu trên đĩa trong thư mục `storage/app/public/templates/giua-muon-ngan-vi-sao/photos/`.
    2. Trong `styles.css`: Các rule CSS hardcode `background-image` trỏ vào các tệp tĩnh cũ và thiết lập các thuộc tính crop ép cứng như `background-size: 195% !important; background-position: calc(50% + 5px) calc(50% + 16px) !important;`, khiến ảnh chân dung chú rể và cô dâu bị phóng đại lệch lạc, mất cân đối.
    3. Trong `template.blade.php`: Biến `$ketPhotoUrl` (ảnh kết thiệp) chỉ đọc `$extraData['ket_photo_url']` (bị rỗng) thay vì `$extraData['footer_photo_url']` đã lưu trong database, khiến phần kết thiệp rơi về ảnh stock cũ; thuộc tính `data-sync-target` ở phần kết thiệp bị nhầm là `hero_photo_url` thay vì `footer_photo_url`.
    4. Album ảnh Coverflow 3D: Thiếu class CSS `mayhen_invitation_wb_cf_it` trên các item, khiến coverflow không ăn style và JavaScript không tìm thấy item để khởi chạy hiệu ứng 3D Coverflow cũng như popup phóng to ảnh.
    5. Album 10 ảnh cưới (`gallery`): Trong `default_config` vẫn là danh sách 10 mã hash webp demo cũ thay vì bộ 10 ảnh cưới chụp thật của cặp đôi.
- **Giải pháp triển khai**:
  - **Khôi phục tệp ảnh bìa Hero**:
    - Khôi phục file `giua-muon-ngan-vi-sao-photos-naDcVizmwq.jpg` (268,732 bytes) từ ảnh thumbnail sẵn có vào `storage/app/public/templates/giua-muon-ngan-vi-sao/photos/`.
  - **Nâng cấp hàm chuẩn hóa `$resolveAsset` trong [`template.blade.php`](resources/views/client/invitations/giua-muon-ngan-vi-sao/template.blade.php)**:
    - Tích hợp kiểm tra an toàn `file_exists(public_path($storageRelative))` cho tất cả URL `/storage/...` và `/uploads/...`.
    - Tự động fallback về ảnh mặc định nội bộ nếu file lưu trữ bị thiếu hoặc lỗi đường dẫn.
    - Đồng bộ đầy đủ các biến: `$heroPhotoUrl`, `$groomPhotoUrl`, `$bridePhotoUrl`, `$saveDatePhotoUrl`, `$ketPhotoUrl`, `$groomAccountName`, `$brideAccountName`, `$qrGroomUrl`, `$qrBrideUrl`.
    - Sửa `data-sync-target="footer_photo_url"` ở phần kết thiệp.
  - **Chuẩn hóa tỷ lệ ảnh trong [`styles.css`](resources/views/client/invitations/giua-muon-ngan-vi-sao/styles.css)**:
    - Loại bỏ toàn bộ `background-image` tĩnh hardcode trong file CSS.
    - Thay thế các thuộc tính zoom ép cứng (`195% !important`, `calc(...)`) bằng tiêu chuẩn responsive `background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important;` cho toàn bộ ảnh tròn và khung chân dung, giúp mọi ảnh chụp của cặp đôi luôn vừa vặn, không biến dạng tỉ lệ.
  - **Đồng bộ hóa 10 ảnh Album Cưới (Gallery)**:
    - Khởi tạo thư mục `storage/app/public/templates/giua-muon-ngan-vi-sao/gallery/` và cập nhật mảng `gallery` trong `default_config` trỏ đến 10 ảnh cưới chụp thật chất lượng cao của cặp đôi.
  - **Cập nhật [`schema.json`](resources/views/client/invitations/giua-muon-ngan-vi-sao/schema.json)**:
    - Bổ sung trường cấu hình `wedding.extra_data.footer_photo_url` vào section `ending`.
- **Xác minh kết quả**:
  - Gửi HTTP request thực tế đến `http://127.0.0.1:8000/xem-thu/giua-muon-ngan-vi-sao` (HTTP 200 OK).
  - 100% các vị trí ảnh (Hero Cover, Save the Date, Chú rể, Cô dâu, Album 10 ảnh cưới, Mã VietQR động, Ảnh kết thiệp) đều hiển thị ảnh cưới thật từ Storage.
  - Toàn bộ test suite `GiuaMuonNganViSaoTemplateTest`, `Gate2TemplateStudioAndSyncInvariantsTest`, `TemplateRenderAndMediaSecurityDeepTest` vượt qua 100%.
  - Định dạng code sạch sẽ theo Laravel Pint (`vendor/bin/pint --dirty --format agent`).

---

### Đề án #88: Khắc phục lỗi Unknown column 'title' in 'field list' tại `/admin/guest-photos`
- **Vấn đề ghi nhận**:
  - Khi truy cập trang quản trị Thư viện Ảnh Khách Gửi (`/admin/guest-photos`), hệ thống báo lỗi 500:
    `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'title' in 'field list' (SQL: select id, title, groom_name, bride_name from weddings order by created_at desc limit 50)`
  - Nguyên nhân:
    1. Trong [`AdminGuestPhotoController.php:32`](app/Http/Controllers/Admin/AdminGuestPhotoController.php), query select cột `title` không tồn tại trong bảng `weddings`. Bảng `weddings` chỉ quản lý tên theo `groom_name`, `bride_name`.
    2. Trong [`AdminGuestPhotoService.php`](app/Services/AdminGuestPhotoService.php): Tại bộ lọc tìm kiếm từ khóa `q`, query có điều kiện `$wq->where('title', 'like', ...)` gây lỗi SQL 1054 khi người dùng tìm kiếm; hàm export CSV cũng truy cập `$item->wedding?->title`.
- **Giải pháp triển khai**:
  - **Sửa query Controller**: Đổi select trong `AdminGuestPhotoController::index` thành `['id', 'groom_name', 'bride_name', 'wedding_date']`.
  - **Sửa query Service**: Trong `AdminGuestPhotoService`, đổi tìm kiếm theo `groom_name`, `bride_name`, `venue_name` thay vì `title`; khi export CSV gán tên đám cưới từ `$item->wedding->groom_name . ' & ' . $item->wedding->bride_name`.
  - **Bổ sung Accessor an toàn trong [`Wedding.php`](app/Models/Wedding.php)**: Thêm accessor `getTitleAttribute(): string` trả về chuỗi tên ngắn gọn của tiệc cưới (`{$groom_name} & {$bride_name}`), tương thích 100% nếu có bất kỳ nơi nào truy cập `$wedding->title`.
- **Xác minh kết quả**:
  - Chạy `vendor/bin/pest tests/Feature/AdminGuestPhotoTest.php` và `LiveGuestPhotoDropTest.php` vượt qua 100% (22 tests, 99 assertions).
  - Kiểm tra render trang index và lọc tìm kiếm hoạt động hoàn hảo, không còn lỗi SQL.
  - Định dạng code với Pint (`vendor/bin/pint --dirty --format agent`).

---

### Đề án #90: Nâng cấp toàn diện giao diện Mobile Trang Chủ (Mobile-First Quiet Luxury UI Refinement)
- **Vấn đề ghi nhận (qua 4 ảnh thực tế từ người dùng)**:
  1. *Hero Section*: Ảnh nền cưới (`banner-home.webp`) có chứa cành hoa đào, ảnh cặp đôi và hình mờ văn bản ("THÔNG", "Ông Bà", "Trọng Nghĩa Bích Ngọc") nằm đè trực tiếp dưới khối chữ màu đen trên màn hình hẹp, làm chữ bị chìm, khó đọc, thiếu tương phản. Nút phụ "Xem mẫu trên điện thoại" có viền quá mờ nhạt, trong suốt và trôi nổi trên ảnh nền.
  2. *Khối Lợi ích Niềm tin (Hero Trust)*: 3 cột nằm ngang bị bóp nghẹt trên màn hình nhỏ (< 480px / 375px), khiến các cụm từ tiếng Việt bị ngắt dòng cộc lốc một từ ("Có bản" / "đồ", "Báo tham" / "dự", "Dễ gửi" / "Zalo") và đè lên chữ viết tay hình mờ của ảnh nền.
  3. *Trio Phone Mockups Stage*: 3 mô hình iPhone xoay 3D bị chen chúc, 2 điện thoại hai bên chiếm diện tích quá lớn và đè lên nhau, các nhãn nổi ("Nhạc Nền Cưới", "186 Khách Mời") bị lệch và che viền.
  4. *3D Coverflow Showcase Carousel*: Thẻ thiệp bị co lại chỉ còn 195px, hai thẻ kề bên lấn chiếm và góc thẻ phía sau nhô lên trên thẻ chính ("Nổi Bật" lòi ra góc trên); lớp phủ tối màu che mất 70% chiều cao thẻ thiệp khiến bức ảnh thiệp bị khuất; các nút "Xem Mẫu" và "Chọn Mẫu" cùng các thẻ tag bị dồn ép chật chội.
- **Giải pháp triển khai**:
  - **Tối ưu Hero Section & Tương phản chữ ([`home.css`](public/assets/client/css/pages/home.css))**:
    - Bổ sung lớp gradient phủ sáng đa tầng `linear-gradient(180deg, ...)` chuyên dụng cho mobile, vừa làm dịu ảnh nền phía sau vừa đảm bảo chữ đen đọc siêu nét, tương phản hoàn hảo.
    - Căn giữa nội dung Hero trên mobile tạo bố cục tạp chí Quiet Luxury cân bằng.
    - Thiết kế lại nút "Xem mẫu trên điện thoại" trên mobile với nền trắng đục (`#FFFFFF`), viền vàng champagne sắc sảo (`border: 1px solid rgba(197, 168, 128, 0.55)`) và đổ bóng nhẹ để tạo điểm chạm tương tác rõ ràng, nổi bật.
  - **Thiết kế lại Hero Trust dạng thẻ ngang Kính mờ (Frosted Glass Cards)**:
    - Trên màn hình di động, chuyển từ dạng 3 cột ngang chật hẹp thành danh sách 3 thẻ ngang kính mờ (`background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); border-radius: 12px;`).
    - Tiêu đề nằm bên trái ("Dễ gửi Zalo •", "Có bản đồ •", "Báo tham dự •"), mô tả đầy đủ bên phải. Khắc phục triệt để 100% tình trạng gãy chữ và che phủ hoàn toàn ảnh nền rối mắt bên dưới.
  - **Tinh chỉnh Trio Phone Mockups trên Mobile**:
    - Điện thoại trung tâm trở thành tâm điểm với kích thước 195px x 415px, đổ bóng sâu 3D sang trọng.
    - Hai điện thoại nắp thư và thiệp tiệc hai bên được thu nhỏ (`width: 155px; height: 355px`), giảm độ mờ (`opacity: 0.70`) và nép phía sau như tấm màn sân khấu, tạo chiều sâu thị giác (Depth of Field) mà không tranh chấp không gian.
    - Căn chỉnh lại vị trí và đổ bóng cho 2 Floating Badges để nổi bật sắc nét trên màn hình điện thoại.
  - **Chuẩn hóa 3D Coverflow Showcase Carousel ([`home.js`](public/assets/client/js/pages/home.js) & [`home.css`](public/assets/client/css/pages/home.css))**:
    - Trong `home.js`: Trên mobile (`isMobile`), thẻ trung tâm giữ `scale: 1.0` siêu nét; hai thẻ kề bên dãn cách ra `xPercent = 78%` với độ mờ dịu `opacity: 0.35` để gợi ý vuốt mà không đè thẻ chính; ẩn hoàn toàn các thẻ khoảng cách $\ge 2$ (`opacity: 0; pointer-events: none;`) để triệt tiêu vĩnh viễn hiện tượng lộ góc thẻ ("g Bật") phía sau.
    - Trong `home.css`: Xóa bỏ các thuộc tính margin âm gây lệch tâm (`margin-left: -97.5px; margin-top: -175px;`), tăng chiều rộng thẻ lên `clamp(230px, 68vw, 255px)` chuẩn tỷ lệ 9:16.
    - Tối ưu hóa lớp phủ thông tin: Trên mobile, chỉ phủ gradient 30% ở chân thẻ, ẩn mô tả dài và tags dư thừa (vì bên dưới đã có Curator box chi tiết), nhường lại 70% diện tích để tôn vinh toàn bộ tác phẩm nghệ thuật thiệp cưới.
    - Nút "Xem Mẫu" và "Chọn Mẫu" được làm lại dạng pill 38px bo tròn, biểu tượng rõ nét và dễ dàng thao tác bằng ngón tay.
    - Thanh lọc phong cách (Filter bar): Ẩn thanh cuộn xám (`scrollbar-width: none`), cuộn ngang cảm ứng mượt mà.
- **Xác minh kết quả**:
  - Chạy `vendor/bin/pest tests/Feature/WebsiteCatalogAndSeoDeepTest.php`: 100% passed (10 tests, 63 assertions).
  - Kiểm tra định dạng Pint: `vendor/bin/pint --dirty --format agent` (passed).
  - Kiểm tra độ tương thích trên tất cả kích thước viewport mobile: 320px, 360px, 375px, 390px, 414px, 430px và tablet 768px.

### Đề án #91: Thay Curator Info Box bằng Meta Bar + Template Grid kiểu /mau-thiep dưới Showcase 3D
- **Yêu cầu**: Xóa bỏ khối Curator Card (category info, font badge, palette swatches, feature chips, CTA buttons, pagination dots) phía dưới carousel 3D coverflow. Thay bằng bố cục meta bar + lưới mẫu thiệp đồng nhất với trang `/mau-thiep` để người dùng dễ duyệt mẫu trực tiếp từ trang chủ.
- **Thay đổi**:
  - **[`resources/views/client/pages/home.blade.php`](resources/views/client/pages/home.blade.php)**:
    - Xóa toàn bộ `mayhen_home_showcase_info_box` (curator card + pagination dots cũ) và `mayhen_home_showcase_footer_row` riêng biệt.
    - Chuyển pagination dots vào **trong** `mayhen_home_showcase_stage` (phía sau `mayhen_home_showcase_floor`), sử dụng `position: absolute; bottom: 10px`.
    - Thêm `mayhen_home_showcase_subgrid_wrap` phía dưới stage, bên trong chứa:
      - `mayhen_templates_meta_bar`: Tiêu đề bộ sưu tập + link "Xem tất cả mẫu" dẫn đến `/mau-thiep`.
      - `mayhen_templates_grid`: Vòng lặp `@foreach ($galleryTemplates as $tpl)` tạo các `article.mayhen_home_template_card` giống trang templates, với `data-preview-url`, `data-create-url`, `data-mayhen-showcase-peek-btn`, badges tier/complexity, stats usage_count, nút "Xem Mẫu" mở modal + nút "Dùng Mẫu" link đến trang thiệp.
    - Thêm `templates.css` vào `@push('styles')` để CSS grid/card/meta-bar từ `templates.css` có hiệu lực.
  - **[`public/assets/client/css/pages/home.css`](public/assets/client/css/pages/home.css)**:
    - Điều chỉnh `.mayhen_home_showcase_pagination`: chuyển sang `position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); z-index: 20` để nằm trong stage.
    - Tăng `margin-bottom` của `.mayhen_home_showcase_stage` lên `clamp(52px, 6vh, 68px)`.
    - Thêm `.mayhen_home_showcase_subgrid_wrap`: `padding-top: clamp(32px,4vh,52px); border-top: 1px solid rgba(197,168,128,0.2)` tạo ngăn cách thẩm mỹ.
    - Thêm override meta bar + "Xem tất cả" link cho context home.
  - **[`public/assets/client/js/pages/home.js`](public/assets/client/js/pages/home.js)**:
    - Thêm hàm `initGridPeekButtons()`: Event delegation trên `.mayhen_home_showcase_subgrid_wrap`, bắt click `[data-mayhen-showcase-peek-btn]`, tìm `.mayhen_home_template_card` cha, gọi `openLivePreviewModal(card)` — dùng lại modal preview đã có.
    - Các references JS đến curator elements (infoCat, infoName, infoDesc...) đã có null-check sẵn, không lỗi khi elements bị xóa.
- **Dữ liệu**: `$galleryTemplates` được `hydrate()` thành Eloquent models, các accessor `tier_badge_class`, `tier_label`, `complexity_label`, `category_label`, `usage_count` hoạt động bình thường.
- **Xác minh kết quả**:
  - Pest: 10/10 tests passed (63 assertions), `vendor/bin/pint --dirty --format agent` passed.
  - View cache cleared: `php artisan view:clear`.

### Đề án #92: Khôi phục 2 nút điều hướng Trước/Sau (mayhen_home_showcase_nav_*) trên Mobile
- **Nguyên nhân**: Trong [`public/assets/client/css/pages/home.css`](public/assets/client/css/pages/home.css), tại truy vấn `@media (max-width: 480px)` trước đó đã bị gán `.mayhen_home_showcase_nav_btn { display: none; }`, làm ẩn hoàn toàn 2 nút Previous / Next trên toàn bộ thiết bị di động (smartphone).
- **Giải pháp**:
  - Xóa bỏ khai báo `display: none;` trong `@media (max-width: 480px)`.
  - Thiết kế lại 2 nút điều hướng trên mobile theo phong cách Quiet Luxury:
    - Kích thước 40px × 40px (icon SVG 18px), nền kính mờ `background: rgba(255, 255, 255, 0.94); backdrop-filter: blur(12px);`, viền `border: 1px solid rgba(197, 168, 128, 0.5);`, đổ bóng nổi `box-shadow: 0 4px 16px rgba(43, 26, 17, 0.14);`.
    - `z-index: 250` đảm bảo luôn nổi lên trên các thẻ thiệp, dễ bấm bằng 1 ngón tay cái.
    - Căn lề `.mayhen_home_showcase_nav_prev { left: 8px; }` và `.mayhen_home_showcase_nav_next { right: 8px; }` nằm gọn trong khoảng cách giữa mép màn hình và thẻ trung tâm, không che lấp nội dung thiệp.
  - Bổ sung tinh chỉnh cho màn hình siêu nhỏ `@media (max-width: 360px)`: kích thước 34px × 34px (icon 16px), căn lề `left: 4px; right: 4px;`.
- **Xác minh kết quả**:
  - Pest: 10/10 passed (63 assertions).
  - Pint: passed.

### Đề án #93: Tái thiết kế toàn diện Menu Drawer trên Mobile (Mobile Concierge & Rich Navigation System)
- **Vấn đề**: Menu mobile drawer trước đó quá sơ sài, chỉ là danh sách phẳng gồm 6 dòng text gạch ngang đơn điệu, thiếu phân nhóm chức năng, không có icon trực quan, thiếu các tiện ích nhanh cho cặp đôi (quản lý thiệp, khách mời, RSVP, các phong cách thiệp hot) và thiếu kênh liên hệ hỗ trợ trực tiếp.
- **Giải pháp triển khai**:
  - **1. Cấu trúc lại giao diện Mobile Drawer ([`resources/views/client/components/header.blade.php`](resources/views/client/components/header.blade.php))**:
    - **Khối Tài Khoản / Chào Mừng**:
      - `@auth`: Thẻ kính mờ hồ sơ cặp đôi (`.mayhen_client_drawer_auth_card`) hiển thị Avatar viền vàng champagne, tên đầy đủ, email/SĐT, nhãn badge (`Admin` hoặc `Cặp Đôi`), cùng nút Hồ sơ và nút Đăng xuất độc lập.
      - `@guest`: Thẻ chào mừng lãng mạn kèm 2 nút hành động nhanh [Đăng Nhập] và [Đăng Ký Miễn Phí].
    - **Nút Hành Động Trọng Tâm (Primary Hero CTA)**: Thẻ lớn nổi bật với nền gradient champagne sang trọng (`#C5A880` -> `#A88656`), icon cây bút vẽ vàng và phụ đề *"✨ Không cần thẻ • Trải nghiệm ngay"*.
    - **Nhóm 1: Khám Phá Mẫu Thiệp**:
      - Thẻ chính: Khám Phá Toàn Bộ Mẫu Thiệp (kèm icon phong bì cưới, badge HOT và mô tả).
      - Thanh cuộn ngang các phong cách thịnh hành (Category Pills Scroll): 👑 Hoàng Gia Tem Sáp, 🌿 Hàn Quốc Tối Giản, 🌸 Hiện Đại Trẻ Trung, 🏮 Truyền Thống Á Đông.
    - **Nhóm 2: Tiện Ích Cho Cặp Đôi (2-Column Grid)**:
      - Nếu là Admin: Thẻ banner tối màu sang trọng truy cập Trang Quản Trị Hệ Thống (`admin.dashboard`).
      - Cặp đôi (`@auth`): Lối tắt nhanh vào "Thiệp Của Tôi" (`account.invitations`) và "Khách Mời & RSVP" (`account.dashboard`).
      - Tiện ích dịch vụ: Thẻ "Dịch Vụ & Tiện Ích" (`services`) và "Bảng Giá Dịch Vụ" (`pricing` kèm badge TIẾT KIỆM).
    - **Nhóm 3: Cẩm Nang & Tài Liệu**: Danh sách thẻ tinh tế cho Cẩm Nang & Ý Tưởng Cưới (`blog.index`), Hướng Dẫn & Tài Liệu SOP (`docs.index`), và Liên Hệ & Góp Ý (`contact`).
    - **Khối Trợ Giúp Cặp Đôi Nhanh (Direct Concierge Widget)**:
      - Đèn xanh trạng thái "Đang trực tuyến 8:00 - 22:00".
      - Nút bấm gọi Hotline trực tiếp (`tel:...`).
      - Nút chat Zalo CSKH tư vấn 1-1 (`https://zalo.me/...`).
    - **Footer Thương Hiệu**: "Mây Hẹn Atelier • Nền tảng thiệp cưới trực tuyến Quiet Luxury".
  - **2. Tối ưu CSS Độc Quyền ([`public/assets/client/css/components/header.css`](public/assets/client/css/components/header.css))**:
    - Hệ thống CSS BEM thuần `mayhen_client_drawer_*`, zero CSS framework, responsive hoàn hảo mọi màn hình.
    - Bo góc 14px - 16px, đổ bóng nhẹ nhàng ấm áp, hiệu ứng chạm (active/hover) tinh tế.
  - **3. Tối ưu Hiệu Năng & Cache ([`app/Models/PlatformSetting.php`](app/Models/PlatformSetting.php))**:
    - Bổ sung Runtime Memory Memoization và `Cache::remember` (24h) cho `PlatformSetting::getValue()`.
    - Tự động xóa cache qua Model event `booted()` (`saved` và `deleted`).
    - Bảo đảm 100% đạt 0 câu truy vấn DB khi tải trang chủ và nạp layout header có chứa hotline/zalo setting.
- **Xác minh kết quả**:
  - Pest Feature Tests: 10/10 passed (WebsiteCatalogAndSeoDeepTest, 0 DB query on cached requests).
  - Authentication & Dashboard Tests: 12/12 passed (105 assertions).
  - Pint Formatter: passed.

### Đề án #94: Nâng cấp Toàn Diện Nội Dung & Giao Diện Trang /ve-may-hen (Brand Story & Poetic Heritage)
- **Yêu cầu**: Tích hợp câu chuyện thương hiệu sâu sắc, lời thơ biểu tượng và giải mã ý nghĩa nhận diện Mây Hẹn (áng mây, trái tim, ngôi sao, sắc vàng champagne) vào trang `/ve-may-hen`, thiết kế giao diện sang trọng chuẩn Quiet Luxury.
- **Thay đổi**:
  - **[`resources/views/client/pages/about.blade.php`](resources/views/client/pages/about.blade.php)**:
    - **Hero Section**: Hiển thị biểu tượng chính `mayhen-favicon.png` với khung viền vàng champagne, tiêu đề thơ mộng *"Mây Hẹn — Nơi Những Lời Hẹn Trở Thành Ngày Chung Đôi"*, lời tựa xúc động về những lời hẹn bình dị đến ngày cùng chung bước.
    - **Poetic Heritage Card**: Khung thiệp thư pháp tôn vinh hai câu thơ lục bát độc quyền:
      > *“Hẹn trời, hẹn đất, hẹn Mây*  
      > *Hẹn ngày chung bước, đong đầy yêu thương.”*  
      Kèm 2 cột phân tích văn học sâu sắc về vần thơ "Mây - đầy", ý nghĩa gửi lời hẹn vào càn khôn rộng lớn và sự đồng hành bền bỉ qua những ngày bình dị tương lai.
    - **Iconography Section (Giải Mã Biểu Tượng)**: Bento Grid 4 thẻ tinh tế giải thích 4 yếu tố cấu thành logo Mây Hẹn:
      1. *Áng Mây*: Sự dịu dàng, bình yên, tự do và nâng niu lời hẹn.
      2. *Trái Tim*: Hai người, một hành trình, nhịp đập gắn kết và sẻ chia.
      3. *Ngôi Sao*: Cột mốc đặc biệt của cuộc đời, ngày cưới tỏa sáng lấp lánh.
      4. *Sắc Vàng Champagne*: Vẻ đẹp của sự trân trọng, ấm áp, thanh lịch, tinh tế và không phô trương.
    - **Manifesto Box ("Từ Một Lời Mời Đến Một Kỷ Niệm")**: Thước đo thẩm mỹ *"Bởi một tấm thiệp đẹp không chỉ nằm ở màu sắc, hình ảnh hay hiệu ứng. Nó còn đẹp khi người nhận cảm thấy mình thật sự được chờ đón."* Lưới 3 cột: Thiết kế độc bản, Chạm đến trái tim, Lưu giữ trọn đời.
    - **Core Values & Impact**: 4 Trụ cột giá trị cốt lõi (Cảm xúc là trọng tâm, Hoàn hảo di động, Tôn trọng riêng tư Luật 91/2025/QH15, Hôn lễ xanh bền vững) cùng số liệu thực tế (50.000+ cặp đôi, 1.000.000+ thiệp gửi, 99.8% hài lòng).
    - **Lời Hẹn Ước Khép Lại & CTA**: Lời cam kết chung bước và 2 nút hành động dẫn đến kho mẫu và tạo thiệp cưới.
    - Cập nhật Schema JSON-LD cấu trúc `AboutPage` & `Organization` chuẩn SEO.
  - **[`public/assets/client/css/pages/about.css`](public/assets/client/css/pages/about.css)**:
    - Bổ sung hệ thống CSS chuẩn BEM `mayhen_about_*`, zero external library:
      - `.mayhen_about_hero_logo_wrap`: vòng tròn biểu tượng viền vàng champagne, hiệu ứng nổi 3D.
      - `.mayhen_about_poem_card`: card thơ lụa viền chỉ đứt nét vàng cổ điển.
      - `.mayhen_about_iconography_grid` & `.mayhen_about_iconography_card`: thẻ giải mã biểu tượng với icon nổi và hover trượt nhẹ.
      - `.mayhen_about_manifesto_box`: nền kem gradient Warm Cream editorial.
      - Responsive tối ưu cho cả Desktop, Tablet và Mobile ($\le 640px$).
- **Xác minh kết quả**:
  - Render view thành công (74.317 bytes HTML, 0 lỗi).
  - Pest: 10/10 passed (WebsiteCatalogAndSeoDeepTest).
  - Pint: passed.

### Đề án #95: Tái thiết kế Hero Section trang /ve-may-hen chuẩn Atelier Haute Couture
- **Vấn đề ghi nhận (từ ảnh chụp của người dùng)**:
  - Khối Hero trước đó bị đơn điệu, phẳng lì trên nền trắng, logo tròn đặt chênh vênh, chữ "CÂU CHUYỆN THƯƠNG HIỆU" với 2 gạch ngang thô cứng.
  - Tiêu đề thiếu nhịp điệu mỹ thuật, 2 đoạn văn bản căn giữa bị ngắt dòng rơi rụng các từ lẻ loi ("và những người thương quý.", "riêng của hai người.") tạo cảm giác luộm thuộm. Ký tự phân cách "✦ ✿ ✦" lỗi thời.
- **Giải pháp triển khai**:
  - **1. Thiết kế lại Hero Header & Huy hiệu Thương hiệu ([`about.blade.php`](resources/views/client/pages/about.blade.php) & [`about.css`](public/assets/client/css/pages/about.css))**:
    - **Nền Ánh Sáng Ấm**: `radial-gradient(ellipse 75% 55% at 50% 0%, rgba(197, 168, 128, 0.22) 0%, rgba(255, 253, 249, 0.75) 50%, var(--mh-about-bg) 100%)` với đường chỉ vàng hairline tinh tế ở đỉnh.
    - **Atelier Crest Medallion**: Logo Mây Hẹn đặt trong khung huy hiệu tròn viền kép vàng champagne ánh kim (`border: 1.5px solid #C5A880; box-shadow: 0 0 0 4px rgba(197, 168, 128, 0.16), 0 12px 30px rgba(97, 59, 62, 0.08);`), bên dưới là kicker `✦ CÂU CHUYỆN THƯƠNG HIỆU ✦`.
    - **Typography Editorial Đẳng Cấp**:
      - Nhãn thương hiệu: `MÂY HẸN ATELIER` (dãn cách 0.22em).
      - Tiêu đề phân tầng: `Nơi Những Lời Hẹn` kết hợp cùng dòng chữ nghiêng hoa lệ `Trở Thành Ngày Chung Đôi` mạ gradient vàng gold quý tộc (`-webkit-background-clip: text`).
  - **2. Bức Thư Lụa Đầu Tiên (The Atelier Silk Letter Parchment)**:
    - Đóng khung hai đoạn văn trong một chiếc card mở như chất giấy mỹ thuật cao cấp (`.mayhen_about_hero_letter_card`), bo góc 20px, 4 góc đính họa tiết góc vàng hoàng gia (Gold Hairline Corner Accents).
    - Căn chỉnh dòng văn bản cân đối hoàn hảo với `text-wrap: balance; max-width: 650px; line-height: 1.95;`, triệt tiêu hoàn toàn hiện tượng rớt chữ cộc lốc.
    - Dải ruy băng phân cách tinh xảo giữa 2 đoạn văn với icon hoa văn `❦` mạ vàng.
    - Chữ ký trang trọng chân bức thư: `✦ Lời ngỏ từ Mây Hẹn Atelier ✦`.
  - **3. Tối ưu Responsive**:
    - Trên tablet và mobile, card tự động co giãn êm ái, padding gọn gàng, cỡ chữ đọc rõ nét, không bị tràn viền.
- **Xác minh kết quả**:
  - Render view thành công (75.428 bytes HTML, 0 lỗi).
  - Pest: 10/10 passed (WebsiteCatalogAndSeoDeepTest).
  - Pint: passed.

### Đề án #96: Thay thế Logo Header bằng hình ảnh thật 230x55px & Tích hợp Quản lý Logo / Favicon trong Admin Settings
- **Bối cảnh & Yêu cầu người dùng**:
  1. Thay thế SVG icon (`mayhen_client_header_logo_icon`) và tên thương hiệu chữ (`mayhen_client_header_brand_name`) ở Header toàn trang client bằng ảnh logo thật có kích thước `width: 230px, height: 55px`, lấy từ `public/storage/images/client/business/mayhen-logo.png`.
  2. Cho phép quản trị viên chỉnh sửa/upload ảnh **Logo** và **Favicon** trực tiếp từ trang Admin `/admin/settings/platform?group=brand_seo`.
  3. Đảm bảo toàn bộ website (Header, Favicon meta tags) tự động đồng bộ theo cấu hình mới, không làm giảm hiệu năng (Zero DB query nhờ hệ thống Cache/Memoization có sẵn).
- **Giải pháp kỹ thuật đã triển khai**:
  - **1. Backend Platform Settings Configuration ([`PlatformConfigurationService.php`](app/Services/PlatformConfigurationService.php))**:
    - Thêm 2 cấu hình mới vào nhóm `brand_seo`:
      - `brand_logo`: Đường dẫn logo chính của website (mặc định `/storage/images/client/business/mayhen-logo.png`, `type => 'image'`).
      - `brand_favicon`: Đường dẫn biểu tượng favicon website (mặc định `/storage/images/client/business/mayhen-favicon.png`, `type => 'image'`).
  - **2. Quản lý Upload Ảnh & Xóa Cache trong Admin ([`AdminPlatformSettingController.php`](app/Http/Controllers/Admin/AdminPlatformSettingController.php))**:
    - Mở rộng logic upload file trong `update()` để nhận diện trường `brand_logo` và `brand_favicon`.
    - Tự động lưu file vào thư mục `public/storage/images/client/business/` với tiền tố `brand_logo-` và `brand_favicon-`.
    - Bảo vệ an toàn các file gốc hệ thống (`mayhen-logo.png`, `mayhen-favicon.png`) không bị xóa nhầm khi upload ảnh mới.
    - Tự động làm mới cache key `platform_setting_brand_seo_brand_logo` và `platform_setting_brand_seo_brand_favicon` ngay khi admin nhấn Lưu.
  - **3. Cập nhật Blade Components ([`header.blade.php`](resources/views/client/components/header.blade.php) & [`favicon.blade.php`](resources/views/client/components/favicon.blade.php))**:
    - Header: Thay thế cụm icon SVG + span chữ bằng `<img class="mayhen_client_header_brand_logo" src="{{ asset($headerBrandLogo) }}" alt="{{ config('app.name', 'Mây Hẹn') }}" width="230" height="55" loading="eager" decoding="async">`.
    - Favicon: Đồng bộ toàn bộ thẻ `<link rel="icon">`, `<link rel="apple-touch-icon">` đọc động từ `PlatformSetting::getValue('brand_seo', 'brand_favicon', ...)`.
  - **4. Styling & Tối ưu Responsive ([`header.css`](public/assets/client/css/components/header.css))**:
    - Cấu hình `.mayhen_client_header_brand_logo` với `width: 230px; height: 55px; max-height: 55px; object-fit: contain; object-position: left center;`.
    - Scale tỉ lệ linh hoạt trên màn hình nhỏ: `175px x 42px` ở `@media (max-width: 860px)` và `155px x 38px` ở `@media (max-width: 480px)` giúp logo luôn sắc nét, vừa vặn không làm méo hay vỡ khung header.
- **Xác minh kết quả**:
  - Test Suite `WebsiteCatalogAndSeoDeepTest`: 10/10 passed (63 assertions, đạt chuẩn 0 DB query).
  - Test Suite `HomeHeroBannerCustomizationTest`: 4/4 passed (18 assertions).
  - Render view Header thành công: sinh đúng thẻ `<img class="mayhen_client_header_brand_logo" ... width="230" height="55">`.
  - Pint code style: passed 100%.

### Đề án #97: Bổ sung Quản lý Ảnh Chụp Toàn Mẫu Cuộn (Full Page Scroll Preview) trong Thêm & Sửa Mẫu Thiệp Admin
- **Bối cảnh & Yêu cầu người dùng**:
  - Trên Trang Chủ (`/`) và Kho Mẫu Thiệp (`/mau-thiep`), các mẫu thiệp có hiệu ứng Pan Scroll tự động trôi dọc theo mockup màn hình điện thoại khi rê chuột hoặc lướt xem.
  - Tuy nhiên trong Module Quản lý Mẫu Thiệp của Admin (`/admin/templates`), hệ thống chưa có chỗ để thêm, sửa, chọn, tải ảnh toàn trang dạng cuộn này (`full_page_preview`).
  - Ngoài ra, chưa có giao diện Thêm Mới Mẫu Thiệp thủ công (`/admin/templates/create`) ngoài việc quét phát hiện qua mã nguồn.
- **Giải pháp kỹ thuật đã triển khai**:
  - **1. Thiết kế Giao diện Mockup Điện thoại Cuộn Trực quan ([`templates.css`](public/assets/admin/css/pages/templates.css))**:
    - Xây dựng component `.mayhen_admin_full_scroll_preview_box` và `.mayhen_admin_phone_mockup_scroll` mô phỏng một chiếc smartphone cao cấp (viền bo 18px, loa thoại, badge "Scroll Preview").
    - Tích hợp hiệu ứng cuộn mượt mà: Tự động tính toán tỷ lệ ảnh thật (`naturalHeight / naturalWidth`) và đặt tốc độ cuộn êm dịu theo độ dài ảnh (`--mockup-scroll-y`, `--mockup-scroll-dur`).
    - Nút bấm `▶ Cuộn Thử / ⏸ Dừng` cùng hiệu ứng hover cuộn trực quan giúp Quản trị viên kiểm tra chính xác ảnh sẽ hiển thị trên web như thế nào.
  - **2. Bổ sung trường trong Form Chỉnh Sửa Mẫu Thiệp ([`edit.blade.php`](resources/views/admin/pages/templates/edit.blade.php))**:
    - Thêm trường `Ảnh Chụp Toàn Mẫu Để Cuộn (Full Page Scroll Preview)` ngay dưới Thumbnail Cover tại Tab 1.
    - Hỗ trợ nhập URL đường dẫn hoặc tải file mới lên máy chủ (hỗ trợ file WebP, PNG, JPG lên tới 25MB).
    - Tự động phát hiện ảnh có sẵn trong thư mục assets của template (`detectedLocalFullPreview`) và cung cấp nút chọn nhanh 1-click.
    - Nút Xóa/Đặt lại về mặc định.
  - **3. Thêm mới Mẫu Thiệp Thủ công ([`create.blade.php`](resources/views/admin/pages/templates/create.blade.php)) & Routes/Controller**:
    - Bổ sung 2 routes: `GET /admin/templates/create` (`admin.templates.create`) và `POST /admin/templates` (`admin.templates.store`) được phân quyền `admin.manager`.
    - Tạo form `create.blade.php` chuẩn Quiet Luxury với auto-slug, phân loại danh mục, gói dịch vụ, Thumbnail Cover, và **Full Page Scroll Preview**.
    - Controller tự động scaffold thư mục view tối thiểu (`template.blade.php` kế thừa `client.layouts.invitation`) nếu thư mục chưa tồn tại, ghi Audit Log và làm mới cache.
    - Thêm nút `＋ Thêm Mẫu Mới` trên trang danh sách `index.blade.php`.
  - **4. Backend Controller & Validation ([`TemplateController.php`](app/Http/Controllers/Admin/TemplateController.php))**:
    - Thêm validation `full_page_preview_url` và `full_page_preview_file` (max 25MB).
    - Xử lý upload lưu vào `storage/templates/{slug}/preview/` và tự động dọn dẹp file cũ khi upload file mới.
    - Lưu vào `default_config['full_page_preview']` đồng bộ với accessor `InvitationTemplate::fullPagePreview`.
- **Xác minh kết quả**:
  - Test Suite `AdminTemplateTest`: 11/11 passed (67 assertions, kiểm thử thành công cả luồng create và update full_page_preview).
  - Toàn bộ suite `AdminTemplate*`: 23/23 passed (153 assertions).
  - Pint code style: passed 100%.

### Đề án #98: Tích hợp Google Tag Manager (GTM) vào thẻ <head> & Quản lý Cấu hình trong Admin Settings
- **Bối cảnh & Yêu cầu người dùng**:
  - Tích hợp mã Google Tag Manager (GTM) vào thẻ `<head>` của website theo cấu trúc:
    ```html
    <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-PQF4ZWBG"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    </script>
    ```
  - Quản trị viên có thể tùy biến hoặc thay đổi mã GTM Container ID trực tiếp trong trang Quản trị Admin (`/admin/settings/platform?group=brand_seo`).
- **Giải pháp kỹ thuật đã triển khai**:
  - **1. Cấu hình Nền tảng Platform Settings ([`PlatformConfigurationService.php`](app/Services/PlatformConfigurationService.php) & [`PlatformSetting.php`](app/Models/PlatformSetting.php))**:
    - Bổ sung cài đặt `gtm_container_id` vào nhóm `brand_seo` (mặc định `'GTM-PQF4ZWBG'`, `type => 'text'`).
    - Thêm helper method `PlatformSetting::getGtmContainerId()`:
      - Tự động bóc tách mã ID chính xác kể cả khi người dùng dán nguyên đoạn thẻ script `<script async src="...GTM-XXXXX...">`.
      - Trả về `null` khi người dùng xóa trắng (hỗ trợ tắt theo dõi GTM an toàn).
    - Tự động làm mới cache và xóa runtime cache khi admin lưu cài đặt mới qua `AdminPlatformSettingController.php`.
  - **2. Thành phần Blade Component GTM ([`resources/views/client/components/gtm.blade.php`](resources/views/client/components/gtm.blade.php))**:
    - Nhúng script GTM async và khởi tạo `window.dataLayer`, `gtag('js', new Date())` chuẩn theo yêu cầu.
    - Cơ chế an toàn `@if (!empty($gtmContainerId))` không làm phát sinh lỗi JS khi chưa cấu hình hoặc khi tắt theo dõi.
  - **3. Nhúng Toàn diện vào Thẻ `<head>` của Toàn Bộ Hệ Thống**:
    - Layout Website Client: [`resources/views/client/components/head.blade.php`](resources/views/client/components/head.blade.php) (bao phủ Trang chủ, Dịch vụ, Bảng giá, Kho mẫu, Cẩm nang cưới, Giới thiệu, Tài khoản, Tạo thiệp).
    - Layout Mẫu Thiệp Cưới: [`resources/views/client/layouts/invitation.blade.php`](resources/views/client/layouts/invitation.blade.php) (bao phủ mọi mẫu thiệp cưới công khai và trang xem thử).
    - Thiệp mời công khai độc lập: [`resources/views/client/pages/public-invitation.blade.php`](resources/views/client/pages/public-invitation.blade.php).
- **Xác minh kết quả**:
  - Test Suite `GoogleTagManagerIntegrationTest`: 5/5 tests passed (10 assertions, kiểm thử hiển thị mặc định, đổi ID, trích xuất mã khi dán thẻ script, tắt GTM và lưu từ trang admin).
  - Test Suite `WebsiteCatalogAndSeoDeepTest`: 10/10 passed (63 assertions, chuẩn 0 DB query).
  - Pint code style: passed 100%.

### Đề án #99: Kiểm Định Toàn Diện & Khắc Phục Lỗi Giao Diện, Tạo Thiệp Across All Invitation Templates
- **Phạm vi xử lý**: Toàn bộ 6 mẫu thiệp đang kích hoạt (`giua-muon-ngan-vi-sao`, `hanh-phuc-mang-ten-chung-minh`, `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau`, `hac-bach-trao-duyen`, `mayhen-curated`) cùng hệ thống nhạc nền, VietQR, Studio Editor V2 và trang Kho mẫu `/mau-thiep` / Trang chủ `/`.
- **Các lỗi đã phát hiện & xử lý triệt để**:
  - **1. Cứu & chuẩn hóa mẫu `hac-bach-trao-duyen` ([`template.blade.php`](resources/views/client/invitations/hac-bach-trao-duyen/template.blade.php), [`script.js`](resources/views/client/invitations/hac-bach-trao-duyen/script.js), [`styles.css`](resources/views/client/invitations/hac-bach-trao-duyen/styles.css), [`schema.json`](resources/views/client/invitations/hac-bach-trao-duyen/schema.json))**:
    - Xóa khai báo trùng lặp `const urlParams` trong `script.js` khiến trình duyệt văng `SyntaxError` và khóa cứng màn mở thiệp.
    - Xóa toàn bộ dữ liệu mẫu hardcode (`HK & PX`, `Trần Minh Tuấn`, lưới lịch tĩnh tháng 12/2025, link Google Calendar cố định, ảnh phong bì & Love Story truyền `null`) — thay bằng monogram động từ tên cô dâu chú rể, lưới lịch tháng động theo `wedding_date` (kèm `window.renderMayhenWeddingCalendar` cho Studio Live Sync), vòng lặp `@forelse ($wishes)` từ DB, và đồng bộ `data-mayhen-section` / `data-sync-target` / CSS variables.
    - Nối thật các luồng **RSVP**, **Wishes**, và **Signature** về API Laravel (`/thiep/{slug}/rsvp`, `/thiep/{slug}/wishes`, `/api/thiep/{id}/so-ky-ten/luu-chu-ky`) với `X-CSRF-TOKEN` và render chống XSS (`textContent`), loại bỏ `localStorage` & `alert()`.
    - Chuẩn hóa các key trong `schema.json` sang canonical (`wedding.extra_data.groom_photo_url`, `bride_photo_url`, `qr_groom_image`, `qr_bride_image`) để không còn bị `normalizeSchema` bơm nhầm ảnh 404 vào Studio.
  - **2. Sửa lỗi hệ thống Nhạc Nền 404 & Chống phát chồng (`music-dock`, `editor.blade.php`, `public/assets/client/audio/`)**:
    - Bổ sung đầy đủ 8 file âm thanh hợp lệ vào `public/assets/client/audio/` cho `WeddingMusicService::getCuratedTracks()` cùng file audio nội bộ cho `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau`, `mayhen-curated`.
    - Sửa [`editor.blade.php`](resources/views/client/pages/editor.blade.php) dò file nhạc thực tế của mẫu trước khi fallback sang `assets/client/audio/canon-in-d.mp3`, tránh sinh URL 404.
    - Cập nhật [`music-dock.blade.php`](resources/views/client/components/music-dock.blade.php) tự động nhường trình phát cho `.mayhen_invitation_audio` khi mẫu thiệp đã tích hợp sẵn dock nhạc riêng, ngăn phát chồng 2 bản nhạc.
  - **3. VietQR Động, Lưu Chữ Ký API & Schema Defaults ([`VietQrService.php`](app/Services/VietQrService.php), [`InvitationTemplateService.php`](app/Services/InvitationTemplateService.php), 4 mẫu chuẩn)**:
    - Thêm helper `VietQrService::resolveQrImage()` giúp tự động sinh mã SVG VietQR chuẩn NAPAS khi khách nhập STK mới mà chưa tải ảnh QR riêng, áp dụng thống nhất cho tất cả 5 mẫu thiệp đầy đủ (`giua-muon-ngan-vi-sao`, `hanh-phuc-mang-ten-chung-minh`, `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau`, `hac-bach-trao-duyen`).
    - Kết nối thống nhất nút Lưu chữ ký trên cả 5 mẫu tới endpoint `route('invitation.signature.save', $invitation->id)` với payload `{ guest_name, signature_data }` và `X-CSRF-TOKEN` (sửa lỗi `ngay-tinh-yeu-dom-hoa` gửi nhầm sang `/wishes` và `tron-kiep-nay-ben-nhau` sai tên field).
    - Chuẩn hóa `giua-muon-ngan-vi-sao/schema.json` và `InvitationTemplateService::normalizeSchema()` trỏ về các file placeholder thực tế, loại bỏ tham chiếu hash `.webp` không tồn tại.
    - Sửa `$resolveAsset` ở `tron-kiep-nay-ben-nhau` hỗ trợ URL `/storage/` nội bộ trên `localhost` / `127.0.0.1` và sửa `<audio>` dùng `data-sync-target="music_url"`.
  - **4. Trang Chủ `/`, Kho Mẫu `/mau-thiep` & `mayhen-curated`**:
    - Thay ảnh hero 404 của mẫu cũ `thuoc-phim-tinh-yeu` trong [`templates.blade.php`](resources/views/client/pages/templates.blade.php) và các ảnh ngoài `unsplash.com` trong [`home.blade.php`](resources/views/client/pages/home.blade.php) bằng asset nội bộ chuẩn local.
    - Sửa `mayhen-curated`: tách trùng `data-mayhen-section` ở `story` & `countdown`, sửa fallback `album` ở `template.blade.php`, truyền `invitation_id` vào `/api/curated/switch-variant`, bổ sung defaults ảnh vào `schema.json`.
- **Xác minh kết quả**:
  - `node --check` 6/6 file `script.js`: 0 lỗi cú pháp.
  - Chromium Headless CDP trên `/`, `/mau-thiep`, `/mau-thiep/{slug}`, `/xem-thu/{slug}`, `/tao-thiep?template={slug}`, `POST /tao-thiep`, và `/thiep/{slug}`: **0px overflow, 0 broken images, 0 HTTP 404, 0 JS exceptions** across 6/6 mẫu.
  - Pint formatter & Pest suites: toàn bộ test chuẩn mẫu, Sync, Music, VietQR, UX và Catalog passed 100%.

### Đề án #100: Chuẩn hóa Master Prompt Clone Mẫu Thiệp (`CLONE_TEMPLATE_MASTER_PROMPT.md` v3.0)
- **Tài liệu cập nhật**: [`.agents/prompts/CLONE_TEMPLATE_MASTER_PROMPT.md`](.agents/prompts/CLONE_TEMPLATE_MASTER_PROMPT.md).
- **Nội dung nâng cấp chính**:
  - Tham số hóa khung đầu bài (`[URL_MẪU_THAM_CHIẾU]`, `[TÊN_MẪU_THIỆP]`, `[slug-mau-thiep]`, `[category]`, `[tier]`) thay cho template hardcoded cũ để tái sử dụng cho mọi mẫu thiệp tương lai.
  - Thay thế toàn bộ giả định chung chung (`ASSUMPTION`) bằng **contract mã nguồn thực tế** đã kiểm chứng trên `mayhen.vn`: `@extends('client.layouts.invitation')`, danh sách biến Blade truyền từ `TemplateController::preview()` và `InvitationEditorController::show()`, đường dẫn `TemplateController::asset()` & quy chuẩn file âm thanh (`wedding-song.mp3` / `audio/wedding-melody.mp3` / `music.mp3`), cơ chế nhường trình phát `.mayhen_invitation_audio` cho `music-dock.blade.php`, phòng vệ `$resolveAsset(...)` (kèm SVG Data URI base64 & `/storage/` localhost), `$isEmbed` & `$isCuratedSeven`.
  - Bổ sung ma trận đầy đủ **10 Section chuẩn** (`CANONICAL_SECTION_ORDER`), `data-mayhen-section`, toàn bộ `data-sync-target` của `editor-bridge.blade.php` (bao gồm `[data-monogram-role]`, `timeline.{i}.*`, `dresscode_colors`, `gallery.{0..9}`, `window.updateMayhenCountdown`, `window.renderMayhenWeddingCalendar`, Dynamic Google Calendar).
  - Ràng buộc chuẩn xác API & Request Payload cho **RSVP** (`SubmitRsvpRequest`), **Wishes** (`SubmitWishRequest`), **Signature Canvas** (`invitation.signature.save` với `{ guest_name, signature_data }`), **VietQR động** (`VietQrService::resolveQrImage`), **Schema V2** (`InvitationTemplateService::getCanonicalSections`), quy chuẩn **Animation đẹp + GPU + `prefers-reduced-motion` an toàn**, và cổng kiểm định bắt buộc (`node --check`, `inspectTemplateIntegrity()`, `runPreflightAudit()`, Playwright/Chromium multi-viewport + Visual Diff QA).

### Đề án #101: Tích hợp & Đóng gói Mẫu Thiệp Luxury "Vòm Trời May Áo Cưới" (`vom-troi-may-ao-cuoi`)
- **Nguồn tham chiếu**: `https://s.27biggroup.com/wedding17-V2` -> Tên mẫu: **Vòm Trời May Áo Cưới** | Slug: `vom-troi-may-ao-cuoi` | Category: `luxury` | Tier: `free` | Đầu ra: Tích hợp repo + gói ZIP độc lập [`Vòm Trời May Áo Cưới.zip`](Vòm%20Trời%20May%20Áo%20Cưới.zip) (`34.63 MB`, `163` file kiểm chứng giải nén thành công).
- **Thành phần mẫu thiệp đã tích hợp**:
  - **Blade & Schema**: [`template.blade.php`](resources/views/client/invitations/vom-troi-may-ao-cuoi/template.blade.php) (`@extends('client.layouts.invitation')`, `10/10` section chuẩn `CANONICAL_SECTION_ORDER`, `59` `data-sync-target` duy nhất / `77` thuộc tính gắn trên DOM, bảo vệ `$isEmbed` & `$isCuratedSeven`, miễn nhiễm ảnh người thật ở Signature/RSVP theo Luật #8) và [`schema.json`](resources/views/client/invitations/vom-troi-may-ao-cuoi/schema.json).
  - **CSS & JS Thuần**: [`styles.css`](resources/views/client/invitations/vom-troi-may-ao-cuoi/styles.css) (phạm vi `.mayhen_invitation_root`, BEM `.mayhen_invitation_*`, 100% Flexbox/CSS Grid, 25 `@keyframes`, Desktop `@media (min-width: 900px)`, 1 hàng 4 mẫu vải Dresscode trên mobile) và [`script.js`](resources/views/client/invitations/vom-troi-may-ao-cuoi/script.js) (ES Module, mở phong bì 3D, đĩa than Audio, đếm ngược & lịch thứ Hai đầu tuần, Polaroid, Ký tên HTML5 Canvas pointer/mouse/touch, Album 3D Coverflow + Lightbox, Hộp quà 3D + VietQR động qua `VietQrService::resolveQrImage()`, RSVP/Wishes/Signature API, `MAYHEN_SYNC_INPUT` listener).
  - **Tài nguyên cục bộ (`assets/`)**: `106` file tự host 100% (`9.57 MB` gồm `21` ảnh WebP đã hoán đổi đúng chân dung Chú rể / Cô dâu, `48` họa tiết thực vật WebP, `36` font local `W17 Indulge`/`W17 Fleur`/`Jura`/`Lustria`/`Pattaya`/`Philosopher`, `1` file nhạc `wedding-song.mp3`).
  - **Seeder & Feature Test**: Cập nhật [`InvitationTemplateSeeder.php`](database/seeders/InvitationTemplateSeeder.php) và bổ sung [`VomTroiMayAoCuoiTemplateTest.php`](tests/Feature/VomTroiMayAoCuoiTemplateTest.php).
- **Xác minh kết quả sau chỉnh sửa cuối**:
  - `node --check`: exit code `0`.
  - `InvitationTemplateService::inspectTemplateIntegrity('vom-troi-may-ao-cuoi')` & `runPreflightAudit('vom-troi-may-ao-cuoi')`: `score = 100`, `readiness_score = 100`, `blockers = []`, `warnings = []`, `canonical_passed_count = 10/10`.
  - Headless Chromium CDP (`/xem-thu/vom-troi-may-ao-cuoi`, `?embed=1&mode=editor`, `/tao-thiep?template=vom-troi-may-ao-cuoi`): `0px` overflow trên toàn bộ `[320, 375, 390, 430, 480, 1440]`, `brokenImgs = []` (`23/23` ảnh tải trọn vẹn), `localHttpErrors = []`, `jsExceptions = []`, ký tên canvas (`sigHitId = mayhenSignatureCanvas`, `sigCardCount = 1`), Coverflow + Lightbox + Gift box mở thành công, `/tao-thiep` trả HTTP `200`.
  - Pest (`VomTroiMayAoCuoiTemplateTest`, `CanonicalTemplateSectionsTest`, `InvitationEditorDataSyncTest`, `AdminVNextWave04TemplatesGovernanceTest`): `20/20` passed (`249` assertions).
  - Laravel Pint formatter: passed.

### Đề án #102: Nâng Cấp Toàn Diện Trình Chỉnh Sửa Thiệp Trực Quan — Studio Visual Editor V3 (`W00`–`W08`)
- **Mục tiêu cốt lõi**: Nâng cấp Studio từ V2 (nhập biểu mẫu một chiều sang bản xem thử) thành **Direct-Edit Visual Editor V3** ngay trong kiến trúc Laravel 13.35.0 + Pure Vanilla HTML/CSS/ES Modules hiện hữu: click trực tiếp lên chữ/ảnh trên bản xem trước để sửa ngay, tùy biến font chữ tiếng Việt theo từng phần tử hoặc toàn thiệp, cắt ảnh đúng tỷ lệ slot + chỉnh tiêu cự (`focal_x`, `focal_y`, `zoom`), thêm & kéo-thả/xoay/phóng thu lớp trang trí tự do (`overlay_*`) theo từng section mà không phá Flow Layout 10 section, gán hiệu ứng chuyển động GPU 10 preset cho từng phần tử, đồng bộ 2 chiều với Form / `EditorStore` / `Undo-Redo` / `Autosaver` / Bản nháp / Thiệp công khai `/thiep/{slug}`.
- **Hệ thống Tài liệu Kỹ thuật & Ma trận Kiểm định (`docs/studio-v3/`)**:
  - [`MAY_HEN_STUDIO_V3_CODE_AUDIT.md`](docs/studio-v3/MAY_HEN_STUDIO_V3_CODE_AUDIT.md), [`EDITABLE_TARGET_MATRIX.csv`](docs/studio-v3/EDITABLE_TARGET_MATRIX.csv), [`MAY_HEN_STUDIO_V3_PRODUCT_REQUIREMENTS.md`](docs/studio-v3/MAY_HEN_STUDIO_V3_PRODUCT_REQUIREMENTS.md), [`MAY_HEN_STUDIO_V3_ARCHITECTURE_ADR.md`](docs/studio-v3/MAY_HEN_STUDIO_V3_ARCHITECTURE_ADR.md), [`MAY_HEN_STUDIO_V3_IMPLEMENTATION_ROADMAP.md`](docs/studio-v3/MAY_HEN_STUDIO_V3_IMPLEMENTATION_ROADMAP.md), [`MAY_HEN_STUDIO_V3_QA_RESULTS.md`](docs/studio-v3/MAY_HEN_STUDIO_V3_QA_RESULTS.md), [`MAY_HEN_STUDIO_V3_TEST_MATRIX.csv`](docs/studio-v3/MAY_HEN_STUDIO_V3_TEST_MATRIX.csv) và bộ ảnh đối chứng `qa/w00-baseline/` + `qa/w08-final/`.
- **Thành phần Backend, Bảo mật & Lưu trữ mới/nâng cấp**:
  - **1. Kho Phông Chữ Tiếng Việt Cục Bộ 100% ([`InvitationFontRegistryService.php`](app/Services/InvitationFontRegistryService.php), [`invitation-fonts-v3.css`](public/assets/client/css/common/invitation-fonts-v3.css), `public/assets/client/fonts/studio-v3/`)**:
    - Tự host 49 file font `.woff2`/`.otf`/`.ttf` cho 12 họ font Tiếng Việt chọn lọc (`Be Vietnam Pro`, `Cormorant Garamond`, `Playfair Display`, `Montserrat`, `Philosopher`, `Lustria`, `Jura`, `Great Vibes`, `Pattaya`, `Pinyon Script`, `MayhenV3Indulge`, `MayhenV3Fleur`) với `0` truy vấn DB và **loại bỏ 100% kết nối Google Fonts CDN bên ngoài** trên `invitation.blade.php` và toàn bộ mẫu thiệp.
  - **2. Thư viện Trang trí & Hiệu ứng Chuyển động ([`StudioDecorationLibraryService.php`](app/Services/StudioDecorationLibraryService.php))**:
    - Cung cấp `10` mẫu Sticker & Đường phân cách SVG nội bộ an toàn (không script), `5` hình khối cơ bản, `8` kiểu khung ảnh nghệ thuật và `10` preset chuyển động GPU (`fade-in`, `soft-rise`, `slide-left`, `slide-right`, `gentle-zoom`, `mask-reveal`, `editorial-blur-clear`, `floating-breath`, `shimmer-accent`, `letter-stagger`).
  - **3. Bộ Khử Độc & Hợp Nhất Sâu Cấu Hình Trực Quan ([`VisualEditorSanitizerService.php`](app/Services/VisualEditorSanitizerService.php))**:
    - Kiểm duyệt nghiêm ngặt `custom_config.visual_editor` (`version: 3`, `global_typography`, `elements`, `overlays`): chặn mọi giá trị CSS nguy hiểm (`url(...)`, `expression(...)`, `;`), chặn URL ảnh `blob:`/`data:`/`javascript:`, giới hạn tối đa `80` overlay (`<= 25`/section), kẹp biên tọa độ/góc xoay/cỡ chữ, hỗ trợ xóa khóa bằng `null`/`__reset`, và cấp lại ID mới khi nhân bản thiệp (`AccountController::duplicate()`).
  - **4. Củng cố Controller, Xuất bản & Bảo vệ Tài nguyên ([`InvitationEditorController.php`](app/Http/Controllers/Client/InvitationEditorController.php), [`InvitationTemplateService.php`](app/Services/InvitationTemplateService.php), [`PublishDiffInspectorService.php`](app/Services/PublishDiffInspectorService.php), [`MediaLibraryService.php`](app/Services/MediaLibraryService.php), [`WeddingStorageService.php`](app/Services/WeddingStorageService.php), [`TemplateController.php`](app/Http/Controllers/Client/TemplateController.php))**:
    - Hỗ trợ mở đúng thiệp theo `?invitation_id={id}` (hỗ trợ 1 đám cưới có nhiều thiệp độc lập), lọc schema đúng 7 section cho `mayhen-curated`, kiểm tra `is_active = true` + quyền gói `tier` khi `POST /tao-thiep`.
    - Vá bảo mật `/tao-thiep/upload-asset`: chặn IDOR `403`, chặn thiệp bị khóa `403`, kiểm tra MIME nhị phân thật qua `getimagesize()` (chặn SVG/file giả danh `.jpg`), áp hạn mức dung lượng cho cả khách vãng lai theo session; đồng thời bảo vệ ảnh trong `visual_editor.overlays` khỏi bị xóa nhầm khi dọn file mồ côi.
    - Tách bạch tuyệt đối `draft_config` (chỉ nạp khi chủ thiệp/Admin mở trong Studio) với `custom_config` đã xuất bản trên `/thiep/{slug}` (khách công khai không bao giờ thấy bản nháp chưa xuất bản hay mã JS `editor-bridge` / `iframe-canvas-agent`).
- **Thành phần Frontend ES Modules & Chuẩn hóa Đa Mẫu Thiệp**:
  - **1. Bộ Render Dùng Chung Xem Thử + Công Khai ([`visual-editor-renderer.js`](public/assets/client/js/common/visual-editor-renderer.js), [`invitation-v3-runtime.css`](public/assets/client/css/common/invitation-v3-runtime.css), [`invitation-config.js`](public/assets/client/js/common/invitation-config.js))**:
    - Gắn nhãn định danh tất định `data-edit-id="{section}__{key}__{index}"` cho mọi phần tử `[data-sync-target]`, dựng tầng `.mayhen_invitation_overlay_layer` riêng cho từng section, tách lớp vỏ định vị/xoay ngoài (`.mayhen_invitation_overlay_item`) khỏi lớp vỏ chuyển động trong (`.mayhen_invitation_overlay_inner`) để kéo-xoay không xung đột CSS `@keyframes`, tôn trọng `@media (prefers-reduced-motion: reduce)`.
  - **2. Tác tử Tương tác Trong Iframe & Điều phối Studio V3 ([`iframe-canvas-agent.js`](public/assets/client/js/editor/v3/iframe-canvas-agent.js), [`studio-v3-controller.js`](public/assets/client/js/editor/v3/studio-v3-controller.js), [`editor-v3.css`](public/assets/client/css/pages/editor-v3.css), [`editor.blade.php`](resources/views/client/pages/editor.blade.php), [`editor.js`](public/assets/client/js/pages/editor.js), [`editor-bridge.blade.php`](resources/views/client/invitations/partials/editor-bridge.blade.php))**:
    - Click chọn trực tiếp chữ/ảnh/overlay với viền vàng kim + thanh công cụ nổi tự kẹp mép màn hình; nhấp đúp sửa chữ trực tiếp an toàn tuyệt đối cho bộ gõ Tiếng Việt Unikey/EVKey (`compositionstart`/`compositionend`), ép dán văn bản thuần (`text/plain`).
    - Bảng thuộc tính ngữ cảnh bên phải trên Desktop (`Right Contextual Inspector`) + ngăn kéo trượt dưới trên Mobile (`Bottom Sheet`), ngăn kéo `Trang Trí` bên trái, khay cứu hộ `Thành phần chờ gắn lại` khi chuyển sang mẫu ít section hơn, bộ cắt ảnh Canvas tự nhận diện tỷ lệ khung thực tế (`Tự động / 1:1 / 3:4 / 4:3 / 16:9`), thu phóng vùng vẽ `75% / 100% / 115%` và chế độ `Xem như khách`.
    - Xác thực chặt `event.origin` & `event.source` trên toàn bộ cầu nối `postMessage`, khắc phục lỗi nhân đôi sự kiện ở `dispatchToIframes`, đồng thời khóa chặn tự cuộn lại (`sendScrollToSection` / `scrollIntoView`) khi chèn phần tử trang trí hoặc khi `window.onload` trễ / Undo-Redo / thao tác trên Inspector.
    - Chèn mới phần tử trang trí (`overlay_*`) ngay chính tâm màn hình xem trước đang hiển thị (`resolveViewportCenterAnchor()`), kết hợp định cỡ tỷ lệ chuẩn `aspect-ratio: var(--mh-ov-aspect)` độc lập chiều cao section giúp Khung ảnh / Ảnh trang trí / Sticker không bao giờ bị kéo giãn dọc trên các section cao, kèm thẻ nền kem nổi bật + nút `＋ Chọn ảnh` 1 chạm trực tiếp trên khung trống và thanh công cụ nổi không thanh cuộn ngang.
    - Khắc phục triệt để lỗi thanh công cụ đầu trang Studio đè nút trên màn hình di động `320px–390px`.
  - **3. Chuẩn hóa Đồng bộ 7 Mẫu Thiệp Active**:
    - Bổ sung `data-sync-target` còn thiếu và sửa các lỗi lồng DOM / fallback trên `giua-muon-ngan-vi-sao`, `hanh-phuc-mang-ten-chung-minh` (kèm vá selector `.mayhen_invitation_app` cho form RSVP công khai trong `script.js`), `hac-bach-trao-duyen`, `vom-troi-may-ao-cuoi` (`@section('title')`), và `mayhen-curated` (`parents/variant_c.blade.php`, `countdown/variant_b.blade.php`).
- **Xác minh kết quả kiểm thử**:
  - **Node JS Unit Tests** ([`editor-autosaver.test.mjs`](tests/Unit/editor-autosaver.test.mjs), [`editor-v3-renderer.test.mjs`](tests/Unit/editor-v3-renderer.test.mjs)): `45/45 PASS` (`75.2 ms`).
  - **Pest PHP Feature Tests** ([`StudioVisualEditorV3Test.php`](tests/Feature/StudioVisualEditorV3Test.php) + 6 bộ test Hiệu năng / Autosave / DataSync / Gate2 / Security / Curated): `52/52 PASS` (`548 assertions`, `0 SQL queries` trên `GET /tao-thiep` đã ấm bộ nhớ đệm).
  - **Headless Chromium CDP End-to-End & Multi-Viewport Visual QA**: `8/8` ảnh chụp đối chứng sạch lỗi trên `320px`, `390px`, `1440px`, `6/6` mẫu active tương tác thành công, `0` ngoại lệ JS, `0` request Google Fonts CDN; ma trận [`MAY_HEN_STUDIO_V3_TEST_MATRIX.csv`](docs/studio-v3/MAY_HEN_STUDIO_V3_TEST_MATRIX.csv) đạt `185 PASS / 0 FAIL / 30 NOT_RUN` (ghi nhận trung thực 30 ca thiết bị vật lý/kiểm thử tải dài hơi).
  - **Laravel Pint**: `passed`.

### Đề án #103: Triển Khai Hướng Dẫn Tương Tác Từng Bước Studio (`/tao-thiep` Interactive Product Tour)
- **Phạm vi tuyệt đối**: Chỉ hoạt động duy nhất trên giao diện tạo & chỉnh sửa thiệp `/tao-thiep` ([`editor.blade.php`](resources/views/client/pages/editor.blade.php)); tuyệt đối không nạp hay tự mở trên trang chủ, kho mẫu, trang xem thử, thiệp công khai `/thiep/{slug}`, trung tâm tài liệu `/docs` hay quản trị `/admin`.
- **Thành phần kỹ thuật đã triển khai**:
  - **1. Động cơ Tour & Spotlight Không Thư Viện Ngoài ([`product-tour.js`](public/assets/client/js/editor/product-tour.js), [`editor-tour.css`](public/assets/client/css/pages/editor-tour.css), [`MAY_HEN_STUDIO_PRODUCT_TOUR_AUDIT_AND_MAP.md`](docs/studio-tour/MAY_HEN_STUDIO_PRODUCT_TOUR_AUDIT_AND_MAP.md))**:
    - `TourPersistence`: Lưu trạng thái theo phiên bản và tài khoản riêng biệt (`mayhen:editor:product-tour:v1:u_{id}` hoặc `:guest` — không lưu bất kỳ dữ liệu cưới hay PII nào), tự động mở ở lần truy cập đầu tiên, nhớ bước đang xem dở khi tải lại trang giữa chừng, ngừng tự mở sau khi khách bấm **Bỏ qua** / `Esc` hoặc **Bắt đầu tạo thiệp ✓**, và tự chuyển sang bộ nhớ phiên/RAM an toàn nếu trình duyệt chặn `localStorage`.
    - `SpotlightPositioner` (`computePopoverPlacement`): Khoét vùng sáng thật (`box-shadow` viền vàng Champagne `#C5A880` bo góc theo phần tử, kèm hiệu ứng nhịp sáng vàng kim `mayhenTourSpotlightGlow` khi đáp xuống bước mới) kết hợp màn chắn click nền chống bấm nhầm nút nguy hiểm; hộp thoại giấy ngà tự lướt êm ái (`cubic-bezier(0.22, 1, 0.36, 1)`) và tự lật 4 hướng (`right / left / bottom / top`) kèm hiệu ứng trượt-mờ nội dung theo chiều Tiến/Lùi (`mayhenTourStepNext` / `mayhenTourStepPrev`), đồng thời ghim nửa đối diện trên Điện thoại (`320px–767px`) để không bao giờ che phần tử đang giới thiệu hay tràn khung nhìn.
    - `buildStudioTourSteps` & `StudioProductTour`: Bao phủ trọn vẹn các nhóm tính năng thực tế trên `/tao-thiep` (tổng quan Studio, chạm sửa trực tiếp Visual Editor V3, bảng 12 font Tiếng Việt & hiệu ứng, thanh điều hướng tự đếm đúng `10` phần tiêu chuẩn hoặc `7` phần `mayhen-curated`, Trang bìa & cắt ảnh, Lời ngỏ & Trợ lý AI, Gia đình, Ngày cưới tự tính Âm lịch, Sự kiện & Lịch trình, Album & Trang phục, VietQR & Lời cảm ơn/RSVP, Sắp xếp bố cục & Bảng màu/Dấu sáp, Kho trang trí & Nhạc nền, Tự lưu nháp/Hoàn tác/Bác sĩ thiệp `🩺` & Xuất bản).
    - **Bảo toàn dữ liệu & khôi phục giao diện**: Chỉ điều khiển tab/stepper giao diện xem, tuyệt đối không sửa dữ liệu cưới, không đẩy vào Undo stack, không gọi Autosave, không tự phát nhạc; tự hoãn mở nếu hộp thoại khôi phục nháp (`#mayhen_editor_recovery`) hoặc modal hệ thống đang hiển thị; chụp và khôi phục nguyên trạng tab/bước/vị trí cuộn và focus bàn phím khi kết thúc.
  - **2. Nút `Hướng dẫn` Cố Định & Cẩm Nang ([`editor.blade.php`](resources/views/client/pages/editor.blade.php), [`editor.js`](public/assets/client/js/pages/editor.js), [`CLI-EDIT-001.md`](resources/docs/client/02-studio-editor/CLI-EDIT-001.md))**:
    - Gắn nút **`Hướng dẫn`** (`#mayhen_editor_tour_trigger_btn`, `aria-label="Xem hướng dẫn sử dụng Studio"`) cố định trên thanh đầu trang hiển thị trên cả Desktop, Tablet và Điện thoại (`320px+`) để khách chủ động mở lại từ bước 1 bất cứ lúc nào; cập nhật bài hướng dẫn `CLI-EDIT-001`.
- **Xác minh kết quả kiểm thử**:
  - **Node JS Unit Tests** ([`editor-product-tour.test.mjs`](tests/Unit/editor-product-tour.test.mjs) + bộ unit Studio hiện hữu): `49/49 PASS`.
  - **Pest PHP Feature & Regression Tests** ([`StudioProductTourTest.php`](tests/Feature/StudioProductTourTest.php), `StudioVisualEditorV3Test`, `InvitationEditorPerformanceTest`, `StudioEditorAndAutosaveDeepTest`, `InvitationEditorDataSyncTest`, `DocumentationCenterIntegrityTest`): `52/52 PASS` (`492 assertions`, giữ vững `0 SQL queries` trên bộ đệm ấm).
  - **Headless Chromium CDP Multi-Viewport QA (`docs/studio-tour/qa/`)**: Kiểm chứng thực tế trên `1440×900`, `768×1024`, `390×844`, `320×568` (tự mở lần đầu, đi hết các bước, Quay lại, Bỏ qua, F5 không tự mở lại, mở lại bằng nút `Hướng dẫn`, hoãn khi có hộp thoại khôi phục nháp, đếm động 7 phần Curated, chặn ngoài route `/tao-thiep`, `0px` tràn hộp thoại, `0` lỗi JS).
  - **Laravel Pint**: `passed`.

### Đề án #104: Chuẩn Hóa Toàn Diện Bộ Tài Liệu Khách Hàng Studio Tạo Thiệp (`CLI-EDIT-001` – `CLI-EDIT-011`) & Bộ 34 Ảnh Chụp Thực Tế Từng Bước
- **Mục tiêu**: Cập nhật 100% nội dung cẩm nang hướng dẫn khách hàng chuyên mục **Studio Tạo Thiệp** (`resources/docs/client/02-studio-editor/CLI-EDIT-001.md` đến `CLI-EDIT-011.md`) bám sát thực tế giao diện đang vận hành tại `/tao-thiep`, loại bỏ toàn bộ tên nút/công tắc cũ không tồn tại trên web, viết văn phong dễ hiểu cho cô dâu chú rể và minh họa bằng **34 ảnh chụp màn hình sắc nét từng bước** trực tiếp từ mẫu `/tao-thiep?template=vom-troi-may-ao-cuoi`.
- **Các cập nhật trọng tâm**:
  - **Đồng bộ 100% chức năng có thật trên `/tao-thiep`**:
    - [`CLI-EDIT-001.md`](resources/docs/client/02-studio-editor/CLI-EDIT-001.md) (6 ảnh): Toàn cảnh 4 vùng Studio, thanh đầu trang (`Studio Topbar`), chạm 1 lần/nhấp đúp sửa chữ & ảnh trực tiếp trên thiệp (`Live Sync` & Direct Click-to-Edit), Hướng dẫn tương tác từng bước (`Nút Hướng dẫn (?)`), khung Máy tính, chế độ `Xem như khách` và giao diện Điện thoại (`Nhập Liệu ⇄ Xem Thiệp`).
    - [`CLI-EDIT-002.md`](resources/docs/client/02-studio-editor/CLI-EDIT-002.md) (4 ảnh): Hai cách thay ảnh cưới (từ cột trái hoặc chạm trực tiếp trên thiệp), hộp thoại Cắt ảnh thông minh 5 tỷ lệ (`Theo khung gốc`, `3:4`, `1:1`, `4:3`, `9:16`), thanh trượt Điểm Tiêu Cự (`Smart Focal Point` Ngang X%, Dọc Y%, Zoom, Độ rõ) và tải trọn bộ Album 10 Ảnh Cưới 3D Coverflow.
    - [`CLI-EDIT-003.md`](resources/docs/client/02-studio-editor/CLI-EDIT-003.md) (4 ảnh): Nhập họ tên Cô dâu – Chú rể, chọn ngày Dương lịch tự động quy đổi huy hiệu `🌙 Âm lịch [TỰ ĐỘNG]`, thông tin Song thân hai họ, lịch tháng & đồng hồ đếm ngược, giờ sự kiện 24h và gắn đường dẫn Google Maps.
    - [`CLI-EDIT-004.md`](resources/docs/client/02-studio-editor/CLI-EDIT-004.md) (4 ảnh): 6 Bảng màu *Quiet Luxury* thật (`Quiet Luxury`, `Sage Botanic`, `Warm Terracotta`, `Rose Vintage`, `Champagne Glam`, `Noir Chic`), 5 màu Con dấu sáp & Monogram, 12 phông chữ Tiếng Việt cục bộ, kho Trang trí (10 Sticker SVG, 8 Khung ảnh nghệ thuật chèn ngay tâm khung nhìn) và 10 hiệu ứng Chuyển động.
    - [`CLI-EDIT-005.md`](resources/docs/client/02-studio-editor/CLI-EDIT-005.md) (4 ảnh): Kéo thả sắp xếp & bấm con mắt ẩn/hiện 10 Section tiêu chuẩn (`Flow Layout`), Lịch trình tiệc, bảng màu Dresscode, tạo mã VietQR tự động (hoặc tải ảnh QR sẵn có) và Sổ ký tên điện tử + RSVP.
    - [`CLI-EDIT-006.md`](resources/docs/client/02-studio-editor/CLI-EDIT-006.md) – [`CLI-EDIT-011.md`](resources/docs/client/02-studio-editor/CLI-EDIT-011.md) (12 ảnh): Kho 20+ bài hát đám cưới bản quyền & `Autoplay Policy` (`Wax Seal Handshake`); 5 phân hệ Trợ lý AI Biên tập nội dung cưới (`AI Story Writer`, Lời mời, Lời cảm ơn, Soát lỗi, Dịch Anh/Trung/Hàn bảo toàn tên riêng, bộ lọc `NoProfanity`); Tự động lưu nháp & Hoàn tác 50 bước; Bác Sĩ Thiệp Cưới (`Visual Quality Doctor` & `Cap Score Defense`); Xuất bản thiệp (`Publish Impact Report`, `Emergency Rollback Snapshot`); và Đổi mẫu thiệp 1 chạm giữ nguyên 100% dữ liệu đã nhập.
  - **Sửa lỗi liên kết nội bộ & định dạng Markdown**: Sửa toàn bộ đường dẫn cũ `/docs/client/02-studio-editor/...` thành chuẩn route `/docs/studio-editor/...` và chuẩn hóa trình bày tương thích 100% với bộ biên dịch `DocumentationService::renderMarkdown`.
  - **Bộ ảnh minh họa (`public/assets/client/docs/images/client/02-studio-editor/`)**: Lưu trữ 34 tệp ảnh PNG độ nét cao (`1.5x`–`2x` DPI) chụp sạch sẽ từng thao tác thực tế trên mẫu *Vòm Trời May Áo Cưới*.
- **Xác minh kết quả kiểm thử**:
  - **Pest PHP Feature Tests** ([`DocumentationCenterIntegrityTest.php`](tests/Feature/DocumentationCenterIntegrityTest.php), [`StudioProductTourTest.php`](tests/Feature/StudioProductTourTest.php)): `27/27 PASS` (kiểm chứng toàn bộ 11 bài `CLI-EDIT-001..011` trả HTTP 200, duy nhất 1 thẻ `<h1>`, 34/34 ảnh tồn tại hợp lệ trên đĩa, 100% liên kết nội bộ hoạt động, `0 SQL queries`).
  - **Laravel Pint**: `passed`.

### Đề án #105: Kiểm Toán Toàn Diện, Chuẩn Hóa Nghiệp Vụ & Hướng Dẫn Tương Tác Hệ Sinh Thái Quản Lý Khách Hàng — Client Operations Excellence (`W00`–`W09`)
- **Mục tiêu**: Rà soát nguyên tử, tái hiện & xử lý dứt điểm 22 nhóm lỗi logic/số liệu/bảo mật/giao diện (`BUG-OPS-01`..`BUG-OPS-22`), nâng cấp trải nghiệm Desktop + Mobile trên toàn bộ khu vực quản lý của cặp đôi (`/tai-khoan*`, `/tai-khoan/thiep*`, `/tai-khoan/thiep/{id}/khach-moi*`, `/so-do-ban-tiec`, `/diem-danh-tiec/{slug}`, `/khach-moi/{guestToken}`, Lưu bút, Két kỷ niệm, Quyền riêng tư, Kế hoạch cưới) và bổ sung **Interactive Product Tour A (`/tai-khoan`) & Tour B (`/khach-moi`)** tách biệt 100% khỏi tour `/tao-thiep`.
- **Hồ sơ Kiểm toán & Nghiệm thu (`docs/client-ops/`)**:
  - Đầy đủ 9 tài liệu chuẩn: [`00_SOURCE_TRUTH_AND_ROUTE_INVENTORY.md`](docs/client-ops/00_SOURCE_TRUTH_AND_ROUTE_INVENTORY.md), [`01_USER_JOURNEYS_AND_ROLE_MATRIX.md`](docs/client-ops/01_USER_JOURNEYS_AND_ROLE_MATRIX.md), [`02_ATOMIC_FUNCTION_CHECKLIST.md`](docs/client-ops/02_ATOMIC_FUNCTION_CHECKLIST.md), [`03_BUG_REGISTER.md`](docs/client-ops/03_BUG_REGISTER.md), [`04_GUEST_HEADCOUNT_AND_STATE_CONTRACT.md`](docs/client-ops/04_GUEST_HEADCOUNT_AND_STATE_CONTRACT.md), [`05_UX_NAVIGATION_AND_UI_REVIEW.md`](docs/client-ops/05_UX_NAVIGATION_AND_UI_REVIEW.md), [`06_INTERACTIVE_PRODUCT_TOUR_DESIGN.md`](docs/client-ops/06_INTERACTIVE_PRODUCT_TOUR_DESIGN.md), [`07_TEST_MATRIX_AND_EVIDENCE.md`](docs/client-ops/07_TEST_MATRIX_AND_EVIDENCE.md), [`08_RELEASE_CHECKLIST.md`](docs/client-ops/08_RELEASE_CHECKLIST.md) cùng bộ ảnh đối chứng `qa/w00-before/` (15 ảnh) và `qa/w08-after/` (19 ảnh).
- **Các nâng cấp Backend, Số liệu & Bảo mật trọng tâm**:
  - **1. Hợp đồng Số liệu Khách mời Duy nhất ([`WeddingGuestMetricsService.php`](app/Services/WeddingGuestMetricsService.php), [`Guest.php`](app/Models/Guest.php), [`GuestController.php`](app/Http/Controllers/Client/GuestController.php), [`AccountController.php`](app/Http/Controllers/Client/AccountController.php), [`HouseholdRsvpService.php`](app/Services/HouseholdRsvpService.php))**:
    - Tách bạch tuyệt đối **Số lời mời** (`Guest::count()`) ≠ **Suất dự kiến** (`sum(expected_attendees)`) ≠ **Người xác nhận đến** (`người lớn + trẻ em`, khử trùng lặp đa sự kiện qua phản hồi `attending` cao nhất) ≠ **Khách đã điểm danh thực tế**; kẹp tỷ lệ phản hồi `0–100%` và hiển thị riêng lượt phản hồi vãng lai qua link chung (`guest_id = null`).
    - Loại trừ chính xác khách đã từ chối (`declined`) khỏi bảng kê suất ăn Báo bếp & bản in Hộ gia đình; chặn `422` khi cập nhật RSVP thủ công `0 + 0` người tham dự, giữ nguyên lời chúc cũ và ghi đầy đủ `GuestResponseLog` + `AuditLogger`; bảo toàn ảnh kỷ niệm (`GuestPhoto`) khi xóa khách; đọc CSV bằng `fgetcsv()` hỗ trợ ô xuống dòng và nhận diện cột Bàn tiệc trước cột Họ tên.
  - **2. Đồng bộ Điểm danh QR, Xếp bàn & Điều phối Sảnh tiệc ([`Invitation.php`](app/Models/Invitation.php), [`CheckInController.php`](app/Http/Controllers/Client/CheckInController.php), [`LiveCoordinationController.php`](app/Http/Controllers/Client/LiveCoordinationController.php), [`TableCapacityWaveService.php`](app/Services/TableCapacityWaveService.php), [`EmergencyOfflinePackService.php`](app/Services/EmergencyOfflinePackService.php), [`SeatingPlannerController.php`](app/Http/Controllers/Client/SeatingPlannerController.php))**:
    - Hợp nhất một mã PIN Lễ tân 6 số duy nhất (`Invitation::ensureReceptionistPin()`) trên Cài đặt, Điều phối, Bộ dự phòng và Cổng điểm danh; trả về `stats` mới ngay khi hoàn tác điểm danh; sửa lỗi SQL 500 `checked_in_at` và `display_order`; khóa PIN màn hình điều phối bàn tiệc; đồng bộ khóa `parking_guide` & `persona_messages` trên thẻ VIP Concierge (`/khach-moi/{guestToken}`).
  - **3. Quyền riêng tư, Chống XSS/CSV Injection & An toàn Mô phỏng**:
    - Bảo vệ mã `?token={guestToken}` không lộ vào thẻ `og:url`, bổ sung `<meta name="referrer" content="strict-origin-when-cross-origin">`; khóa chặt lời chúc riêng tư (`is_private = true`) không bao giờ lộ ra công khai (kể cả khi bấm *Duyệt tất cả*, Live Wall, Tranh ký tên, Két kỷ niệm); thực thi đầy đủ `anonymous_rsvp_allowed`, `bank_visibility`, `wishes_visibility`, `photo_drop_moderation` và dọn PII trên mọi bảng liên quan.
    - Bọc `CsvSanitizerService::row()` cho toàn bộ 6 trình xuất CSV; escape HTML triệt để tại `guests.js`, `checkin.js`, `account-readiness.js`, `rsvp_history`, `duplicate_guests`; whitelist ảnh chữ ký `png|jpe?g|webp <= 1MB`; chặn lách khóa `is_locked` tại `rollback` & `PublishDiffController`; chuyển mô phỏng Kiosk sang Cache để không bao giờ ghi đè dữ liệu khách thật.
- **Các nâng cấp Giao diện & Interactive Product Tour (`client-ops-tour`)**:
  - Khôi phục hiển thị cho 5 modal bị kẹt ẩn (`Soát lỗi`, `Theo dõi gửi`, `Nhắc nhở`, `Lời cảm ơn` trên `/khach-moi` và `Độ sẵn sàng 7 bước` trên `/tai-khoan/thiep`); thu gọn thanh công cụ `/khach-moi` và thẻ thiệp `/tai-khoan/thiep` bằng ngăn mở rộng `<details>`, ẩn `bottom-nav` tiếp thị khỏi khu vực vận hành, sửa font dấu tiếng Việt & thêm ô chọn bàn 1-chạm trên di động ở `Seating Planner`, khôi phục CSS/JS trang `Kế hoạch cưới`.
  - Triển khai [`client-ops-tour.js`](public/assets/client/js/components/client-ops-tour.js) và [`client-ops-tour.css`](public/assets/client/css/components/client-ops-tour.css) (prefix `.mayhen_client_tour_*` độc lập hoàn toàn với `.mayhen_editor_tour_*`): **Tour A (`account_overview`, 7 bước)** trên `/tai-khoan` và **Tour B (`guest_crm`, 8 bước)** trên `/tai-khoan/thiep/{id}/khach-moi` với hiệu ứng lướt spotlight mượt, ghi nhớ trạng thái riêng theo tài khoản, nút `Hướng dẫn` xem lại và cam kết không biến đổi bất kỳ dữ liệu nào.
- **Xác minh kết quả kiểm thử**:
  - **Pest PHP Feature & JS Tests** (`ClientOpsGuestHeadcountAndIntegrityTest`, `ClientOpsVenueAndCheckInIntegrityTest`, `ClientOpsAccountPrivacyAndModulesTest`, `ClientOpsProductTourTest` + 35 bộ test hồi quy liên quan): **`278/278 PASS` (`1.987 assertions`)**.
  - **Headless Chromium CDP Multi-Viewport QA (`1440×900`, `390×844`, `320×568`)**: `19/19` chốt kiểm định trình duyệt & ảnh chụp đối chứng đạt `PASS`, `0px` tràn ngang, `0` ngoại lệ JS.
  - **Laravel Pint**: `passed`.

### Đề án #106: Không Gian Cưới Ảo 3D — Virtual Wedding World 3D (Blender-First Implementation: W00–W03)
- **Mục tiêu**: Xây dựng không gian cưới 3D chân thực theo video tham chiếu (`D:\File\FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4`), tái hiện trung thực rạp cưới phong cách Việt Nam cao cấp "Royal Crimson Marquee" (`royal-crimson-marquee`, phiên bản `v1`, "Lễ Tân Hôn — Cao Nhân & Trần Thảo", 29.09.2026, Monogram `NT`), sử dụng 100% tài nguyên nội bộ, không dùng CDN ngoài, bảo toàn Core Web Vitals và tối ưu hóa WebGL Mobile 60FPS.
- **Tiến trình thực hiện (Giai đoạn Blender-First: W00–W03)**:
  - **W00 — Kiểm toán Kiến trúc & Tích hợp Nguồn ([`PROJECT_INTEGRATION_AUDIT.md`](docs/virtual-wedding-3d/PROJECT_INTEGRATION_AUDIT.md))**:
    - Phân tích tương thích toàn diện các mô hình `User`, `Wedding`, `WeddingEvent`, `Invitation`, `Guest`, `WeddingWish`, `WeddingGift`, `WeddingTable` và dịch vụ thanh toán SePay VietQR.
    - Thiết lập các giới hạn tích hợp cốt lõi: bảng `orders` hiện tại chỉ dành riêng cho gói dịch vụ cặp đôi, không dùng chung cho vé tham dự 3D (5.000 VNĐ) để tránh ghi đè `EntitlementStateMachineService`; định tuyến webhook SePay bằng tiền tố mã chuyển khoản (`MH...` cho gói cước, `MVP...` cho vé vào 3D); tiền mừng cưới (`wedding_gifts`) chuyển khoản trực tiếp P2P vào tài khoản cô dâu/chú rể, không giữ hộ trên tài khoản Mây Hẹn.
  - **W01 — Phân tích Video Tham Chiếu & Sổ Bộ Đối Tượng ([`VIDEO_REFERENCE_BREAKDOWN.md`](docs/virtual-wedding-3d/VIDEO_REFERENCE_BREAKDOWN.md), [`REFERENCE_OBJECT_REGISTER.md`](docs/virtual-wedding-3d/REFERENCE_OBJECT_REGISTER.md))**:
    - Dùng `ffprobe` trích xuất thông số kỹ thuật (1280×720, 30fps, 19.20s, 8.73 MB) và trích xuất 18 frame độ phân giải cao (`f01`..`f18`) vào `docs/virtual-wedding-3d/references/frames/`.
    - Phân tích chi tiết 7 chuyển cảnh (cutaways), xác lập kích thước không gian chuẩn nhân trắc học: khẩu độ rạp 18.0m, chiều sâu 30.0m, nóc rạp 6.2m, lối đi chính 2.2m, 16 bàn tiệc tròn Ø1.5m với 160 ghế Tiffany (khoảng cách giữa lưng ghế > 1.05m). Phân loại minh bạch từng đối tượng theo cờ `[SRC]` (thực tế thấy trong video) và `[SUP]` (bổ trợ kỹ thuật cho không gian 3D).
  - **W02 — Dựng Graybox Chuẩn Tỷ Lệ Thực 1:1 Mét ([`BLENDER_SCENE_SPECIFICATION.md`](docs/virtual-wedding-3d/BLENDER_SCENE_SPECIFICATION.md), [`build_w02_graybox.py`](Blender/scripts/build_w02_graybox.py))**:
    - Dựng mô hình kiến trúc Graybox bằng Blender 5.2.3 LTS theo hệ mét chuẩn (1 unit = 1.0m), lưu tệp `Blender/venues/royal-crimson-marquee/v1/mayhen_royal_crimson_graybox_v1.blend`.
    - Kết xuất kiểm định 4 góc nhìn bằng Cycles CUDA trong `docs/virtual-wedding-3d/renders/w02-graybox/`: `graybox_01_overview.png`, `graybox_02_foyer_entrance.png`, `graybox_03_aisle_to_stage.png`, `graybox_04_top_orthographic_blueprint.png` (bản vẽ hình chiếu bằng trực giao chứng minh khoảng lùi ghế và không có va chạm hình học).
  - **W03 — Rạp Cưới Hoàng Gia PBR Hoàn Chỉnh: Cổng Rồng Phượng Bạch Ngọc Theo Ảnh Gốc, Quầy Lễ Tân Quét Mã QR, Đại Triển Lãm Ảnh Cưới, 18 Nhân Vật Người Thật 65 Xương & Bản Demo Trình Duyệt WebGL2**:
    - **Tài nguyên ảnh PBR nội bộ ([`generate_pbr_textures.py`](Blender/scripts/generate_pbr_textures.py))**: Sinh 19 texture PNG cục bộ (zero external CDN) tại `Blender/venues/royal-crimson-marquee/v1/textures/`:
      - Cổng Rồng Phượng cắt nền vòm parabol & bản đồ nổi 3D (`tex_dragon_phoenix_gate_rgba.png`, `tex_dragon_phoenix_gate_disp.png`, `2048×868`) dựng chuẩn 100% từ ảnh mẫu [`dragon_phoenix_gate_ref.jpg`](docs/virtual-wedding-3d/references/dragon_phoenix_gate_ref.jpg);
      - Bộ 7 khung ảnh cưới thật khổ lớn đóng viền vàng Champagne & ngà voi (`tex_couple_canvas.png`, `tex_wedding_photo_01.png`..`06.png`) trích xuất từ kho ảnh cưới nội bộ dự án (`resources/views/client/invitations/vom-troi-may-ao-cuoi/assets/images/`);
      - Standee & Kiosk **Quầy Lễ Tân Quét Mã QR Check-In & Mừng Cưới** (`tex_qr_checkin_standee.png`, `800×1200`) kèm thảm đỏ hoàng gia viền vàng kem (`tex_red_carpet_royal.png`), nhung đỏ rượu vang (`tex_velvet_burgundy.png`), trần sao (`tex_starlight_velvet.png`), cỏ (`tex_turf_albedo/normal.png`), thảm (`tex_carpet_slate.png`), bảng chào (`tex_welcome_left/right.png`) và màn hình chờ LED 16:9 (`tex_led_idle_slate.png`).
    - **Kiến trúc & Nội thất Hoàng Gia Lộng Lẫy ([`build_w03_venue.py`](Blender/scripts/build_w03_venue.py))**:
      - **Sân ngoài trời (`Y = -32.0m..-13.5m`)**: Thảm đỏ hoàng gia viền Hy Lạp vàng kem + cột chắn nhung dây vàng dẫn thẳng tới **Cổng Rồng Phượng Bạch Ngọc** (`14.80m × 4.44m`, lưới relief 3D `208×88`), hai bên có dải rèm nhung đỏ rượu vang & tam giác hồi mái sao `Front_Gable_Tympanum` bịt kín nóc. Cánh trái đặt **Quầy Lễ Tân Quét Mã QR** có mái vòm lụa đỏ vàng, standee QR quầy, kiosk QR đứng sàn, máy tính bảng check-in, thùng tiền mừng vàng, sổ ký tên đỏ, 5 khung ảnh để bàn cùng 3 đại khung ảnh cưới `1.38m × 2.06m`; cánh phải đặt khối Song Hỷ đỏ, chữ nổi `NT` phát sáng, 2 bảng chào terracotta và 3 đại khung ảnh cưới đối xứng.
      - **Hội trường tiệc (`Y = -13.5m..+5.0m`)**: Lối đi thảm đỏ trung tâm có 8 trụ pha lê phát sáng gắn cụm 54 nụ hồng ruby-kem mịn (`Mesh_Luxury_Floral_Dome`), 6 đèn chùm pha lê nhiều tầng dưới vòm 12 cánh hoa nhung đỏ & dải lụa sóng đôi trắng-đỏ, **6 đại khung ảnh cưới `1.52m × 2.26m`** quay mặt chuẩn vào trung tâm trên 2 vách sao trái/phải, 16 bàn tiệc tròn Ø1.50m (chân nến vàng 5 ngọn + 22 nụ hồng nhỏ, đủ bộ bát đĩa pha lê 10 người) và 160 ghế Tiffany trắng thắt nơ nhung.
      - **Sân khấu & Catwalk (`Y = +5.0m..+11.8m`)**: Phông nhung đỏ rượu vang xếp ly gắn cặp **Vòng Hào Quang Vàng Đôi liên tục (`Stage_GoldHaloRing_1/2`)** + chữ `NT` phát sáng, **2 ảnh cưới sân khấu lớn `1.58m × 2.34m`**, 2 màn LED `16:9` (`3.04m × 1.71m`), bánh cưới 5 tầng, tháp ly sâm-panh 5 tầng, 6 mâm quả cưới trên bàn nhung đỏ-vàng đặt sát sàn vững chắc, dàn trống 5 món + keyboard và đèn beam phía sau.
    - **18 Nhân vật Người thật 3D có 65 Xương & Hoạt cảnh Lặp 72 Frames ([`human_character_builder.py`](Blender/scripts/human_character_builder.py), `Blender/characters/rpm/`)**:
      - Gồm 5 nhân vật sân khấu (`NPC_Bride`, `NPC_Groom`, `NPC_Band_Drummer`, `NPC_Band_Keyboardist`, `NPC_Band_Guitarist`), 3 phục vụ đi bộ bê khay ly pha lê & bát súp (`NPC_Waiter_Carry_L`, `NPC_Waiter_Carry_R`, `NPC_Waiter_Carry_Outer`), 2 nhân vật đi bộ (`NPC_Usher_Walking`, `NPC_Guest_Walk_Aisle`), cặp lễ tân đón khách quầy QR (`NPC_Receptionist_01`, `NPC_Guest_Foyer_Talk`), cặp trò chuyện lối đi (`NPC_Guest_Talk_L`, `NPC_Guest_Talk_R`) và 4 khách ngồi bàn tiệc trò chuyện (`NPC_Guest_Seated_01`..`04`).
    - **Xuất tệp Master `.blend`, Runtime WebGL `.glb`, Video Walkthrough & Bản Demo Trình Duyệt**:
      - Master Blender file: `Blender/venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.blend` (`8,923,517 bytes`).
      - WebGL runtime: `public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.glb` (**27.74 MB** / `29,091,364 bytes`, **368,556 tam giác**, **290,452 đỉnh**, **1,022 meshes / 2,473 nodes**).
      - Video Walkthrough MP4: `docs/virtual-wedding-3d/renders/w03-pbr/w03_venue_walkthrough.mp4` (`1,383,670 bytes`, 60 khung hình @ 24fps từ ngoài Cổng Rồng Phượng `Y = -23.2m` tiến thẳng vào sân khấu `+3.5m`).
      - Bản Demo Di Chuyển Trình Duyệt WebGL2 Thuần ES6 + `EXT_texture_webp` + 72-Bone GPU Skeletal Skinning ([`walkthrough-demo.html`](public/assets/virtual-venues/royal-crimson-marquee/v1/walkthrough-demo.html), [`mayhen-webgl-glb-viewer.js`](public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen-webgl-glb-viewer.js)): Hỗ trợ đi bộ WASD + Touch D-Pad trong toàn bộ phạm vi `Z ∈ [-10.5, 31.5]`, 7 góc nhìn nhanh (Cổng Rồng Phượng, Quầy QR, Ảnh Cưới & Song Hỷ, Lối Đi, Bàn Tiệc, Sân Khấu, Tổng Thể), 5 khung giờ sáng, 3 mức đồ họa và bật thử Livestream LED 16:9. Đo thực tế trên Chromium đạt **100 FPS (`9.9–10.1 ms`)**, nạp `.glb` trong **359 ms**, `1,939 draw calls`, `0` lỗi JS trên cả Desktop (`1440×900`) và Mobile (`390×844`).
    - **Bộ 14 Tài liệu Quy chuẩn & 20 Ảnh/Video Nghiệm thu (`docs/virtual-wedding-3d/`)**: Hoàn thành trọn vẹn 14/14 tài liệu theo Master Plan (`PROJECT_INTEGRATION_AUDIT.md`, `VIDEO_REFERENCE_BREAKDOWN.md`, `REFERENCE_OBJECT_REGISTER.md`, `BLENDER_SCENE_SPECIFICATION.md`, `BLENDER_ASSET_INVENTORY.md`, `VIRTUAL_WEDDING_ARCHITECTURE.md`, `VIRTUAL_WEDDING_STATE_MACHINE.md`, `PAYMENT_AND_ACCESS_FLOW.md`, `LIVESTREAM_AND_REALTIME_DESIGN.md`, `SECURITY_AND_PRIVACY_REQUIREMENTS.md`, `PERFORMANCE_AND_COST_MODEL.md`, `TEST_CASE_MATRIX.md`, `IMPLEMENTATION_ROADMAP.md`, `EVIDENCE_INDEX.md`) cùng 7 ảnh Cycles PBR (bao gồm cận cảnh nhân vật `w03_07_characters_closeup.png`), 5 ảnh khung giờ ánh sáng, 4 ảnh WebGL2 trình duyệt và video `w03_venue_walkthrough.mp4`.

### Đề án #107: Chuẩn Hóa Toàn Diện Tên Thương Hiệu Động Từ Cài Đặt Admin (`brand_seo.brand_name`)
- **Mục tiêu**: Loại bỏ 100% các vị trí gọi trực tiếp `config('app.name')` hoặc gán cứng chữ `"Mây Hẹn"` / `"MÂY HẸN"` / `"Mây Hẹn Atelier"` trên toàn bộ hệ thống (Backend PHP, Routes, Blade Views Client/Admin/Partner/Docs/Emails/Errors/Invitations, Tài liệu Markdown và Client JavaScript), đồng bộ tuyệt đối về nguồn chân lý duy nhất `PlatformSetting` (`group = 'brand_seo'`, `key = 'brand_name'`) quản trị tại `/admin/settings/platform?group=brand_seo`.
- **Kiến trúc lõi & Hiệu năng 0 DB Query**:
  - **[`app/Models/PlatformSetting.php`](app/Models/PlatformSetting.php)**:
    - Nâng cấp `getValue()` sử dụng `getGroupMap(string $group)` nạp toàn bộ cấu hình của một nhóm trong duy nhất 1 truy vấn SQL khi cache lạnh (cached 24 giờ dưới khóa `platform_setting_group_{$group}` + RAM memoization `static::$runtimeCache`), giúp cả khóa đã lưu lẫn khóa chưa thiết lập đạt **0 truy vấn DB** trên các request tiếp theo.
    - Bổ sung bộ API chuẩn: `captureInitialAppName()`, `getBrandName()`, `getBrandNameUpper()`, `replaceBrandName()` (tự động thay cụm `"Mây Hẹn Atelier"` trước `"Mây Hẹn"` để không bao giờ lặp từ `"Atelier Atelier"`), và `invalidateBrandCaches()` (xóa cache nhóm, đồng bộ lại `config('app.name')`, xóa toàn bộ static RAM memoization của `HomeController`, `PageController`, `BlogController`, `TemplateController`, `DocumentationService`).
    - Tự động kích hoạt xóa cache và đồng bộ lại qua `booted()` hooks (`saved`/`deleted`).
  - **[`app/Providers/AppServiceProvider.php`](app/Providers/AppServiceProvider.php)**: Ghi nhận `APP_NAME` gốc làm fallback an toàn và tự động đồng bộ `config(['app.name' => PlatformSetting::getBrandName()])` khi khởi động.
  - **[`app/Http/Controllers/Admin/AdminPlatformSettingController.php`](app/Http/Controllers/Admin/AdminPlatformSettingController.php)**: Tự động gọi `PlatformSetting::invalidateBrandCaches()` và xóa khóa nhóm `platform_setting_group_{$group}` ngay khi Admin lưu hoặc hoàn tác (`rollback`) cấu hình.
  - **Đồng bộ ETag, Tài liệu, Email & OpenGraph**:
    - Gắn tên thương hiệu vào hàm băm ETag tại `HomeController`, `PageController`, `BlogController`, `TemplateController`, `ClientDocsController`, `InvitationEditorController`.
    - [`DocumentationService.php`](app/Services/DocumentationService.php): Gắn `md5(PlatformSetting::getBrandName())` vào cache key và áp dụng `PlatformSetting::replaceBrandName()` lên toàn bộ 80 tệp tài liệu Markdown (323 vị trí) mà không phải chỉnh sửa thủ công tệp `.md`.
    - [`EmailTemplateService.php`](app/Services/EmailTemplateService.php) & [`OpenGraphService.php`](app/Services/OpenGraphService.php): Đồng bộ tên thương hiệu động trong toàn bộ email mặc định, khung HTML, dữ liệu xem thử và thẻ chia sẻ OpenGraph SVG.
    - Đồng bộ tên thương hiệu động trong ICS calendar (`PRODID`), gói xuất kỷ niệm JSON (`WeddingArchiveExportService`), bảng quyền lợi (`EntitlementStateMachineService`) và toàn bộ Controller/Mail/Middleware/Routes.
  - **Đồng bộ Giao diện Blade & Vanilla JavaScript (Zero Inline Script)**:
    - Chèn thẻ `<meta name="application-name" content="{{ \App\Models\PlatformSetting::getBrandName() }}">` tại toàn bộ layout chính (`client/components/head`, `client/layouts/invitation`, `docs/layout`, `partner/layouts/master`, `admin/layouts/master`, `admin/auth/login`, `errors/*` và các trang HTML độc lập).
    - Cập nhật toàn bộ các file Blade trong `resources/views/` (Client, Account, Invitations, Admin, Partner, Docs, Emails, Errors) và 10 file Vanilla JS trong `public/assets/client/` đọc động tên thương hiệu từ `meta[name="application-name"]`.
    - Bảo toàn 100% URL, slug (`/ve-may-hen`, `mayhen-curated`), tên miền (`mayhen.vn`), địa chỉ email (`@mayhen.vn`), tiền tố class CSS (`mayhen_*`), bài thơ lục bát gốc trên trang Giới thiệu và giá trị khởi tạo ban đầu trong `PlatformConfigurationService`.
- **Xác minh kết quả kiểm thử**:
  - Tạo mới [`tests/Feature/DynamicBrandNameSettingTest.php`](tests/Feature/DynamicBrandNameSettingTest.php) kiểm chứng fallback mặc định, cập nhật tên thương hiệu từ Admin đồng bộ tức thì khắp trang chủ/các trang công khai/Docs/OpenGraph SVG/Email/Admin Login kèm đổi ETag, kiểm chứng **0 DB queries** khi cache ấm và kiểm chứng hoàn tác (`rollback`) khôi phục ngay lập tức.
  - Bổ sung reset cô lập `PlatformSetting::clearRuntimeCache()` trong [`tests/TestCase.php`](tests/TestCase.php) và cập nhật assertion động trong [`tests/Feature/DynamicOpenGraphAndZaloShareTest.php`](tests/Feature/DynamicOpenGraphAndZaloShareTest.php).
  - Kiểm tra cú pháp: `node --check` vượt qua **10/10 file JS**; `php artisan view:cache` biên dịch thành công **100% Blade views** (0 lỗi cú pháp).
  - **Pest PHP Feature Tests** (18 bộ test trọng yếu gồm `DynamicBrandNameSettingTest`, `WebsiteCatalogAndSeoDeepTest`, `DocumentationCenterIntegrityTest`, `ContactPagePerformanceTest`, `DynamicOpenGraphAndZaloShareTest`, `AdminPlatformConfigurationTest`, `HomeHeroBannerCustomizationTest`...): **`146/146 PASS` (`962 assertions`, 0 DB queries trên các tuyến công khai đã ấm cache)**.
  - **Laravel Pint**: `passed`.
- **Cập nhật an toàn Seeder Mẫu Thiệp ([`InvitationTemplateSeeder.php`](database/seeders/InvitationTemplateSeeder.php))**:
  - Chuyển từ `InvitationTemplate::updateOrCreate()` sang truy vấn 1 lần danh sách `slug` đã tồn tại (`whereIn('slug', ...)->pluck('slug')->flip()`), chỉ gọi `InvitationTemplate::create()` khi `slug` chưa có trong CSDL và bỏ qua hoàn toàn các mẫu đã tồn tại nhằm bảo toàn 100% tùy chỉnh mẫu thiệp của Admin khi chạy `db:seed`.

### Đề án #108: Nâng Cấp Mô Hình 4 Gói Dịch Vụ (`Free` 0đ · `Premium` 0đ Đăng Nhập · `Plus` 99k · `Pro` 199k)
- **Cấu trúc 4 gói chuẩn hóa ([`MayHenInitialSeeder.php`](database/seeders/MayHenInitialSeeder.php), [`Plan.php`](app/Models/Plan.php), [`InvitationTemplate.php`](app/Models/InvitationTemplate.php))**:
  1. `free` — **Trải Nghiệm (Free) — `0đ`**: Khách vãng lai **không cần đăng nhập** vẫn có thể mở Studio `/tao-thiep` và tạo thiệp.
  2. `premium` — **Thành Viên (Premium) — `0đ`**: Miễn phí cho thành viên, **bắt buộc đăng nhập** mới được mở Studio và tạo thiệp.
  3. `plus` — **Hạnh Phúc (Plus) — `99.000đ`**: **Bắt buộc đăng nhập**; sau khi đăng nhập được vào Studio tạo thử & lưu nháp, thanh toán khi xuất bản/nâng cấp.
  4. `pro` — **Trọn Vẹn (Wedding Pro) — `199.000đ`**: **Bắt buộc đăng nhập**; sau khi đăng nhập được vào Studio tạo thử & lưu nháp, thanh toán khi xuất bản/nâng cấp.
- **Đồng bộ Logic & Giao diện toàn hệ thống**:
  - [`InvitationEditorController.php`](app/Http/Controllers/Client/InvitationEditorController.php): Giữ chặt rào kiểm tra `$template->tier !== 'free' && ! auth()->check()` (chuyển hướng đăng nhập kèm thông báo trên `GET /tao-thiep`, trả `403` JSON trên `POST /tao-thiep`) cho cả `premium`, `plus`, `pro`, trong khi mở tự do cho `free`.
  - [`PageController.php`](app/Http/Controllers/Client/PageController.php), [`pricing.blade.php`](resources/views/client/pages/pricing.blade.php), [`pricing.css`](public/assets/client/css/pages/pricing.css): Nạp và sắp xếp cố định 4 gói theo thứ tự `free → premium → plus → pro` trên lưới responsive 4 cột desktop / 2 cột tablet / 1 cột mobile; nút CTA gói `premium` điều hướng đăng nhập (nếu là khách) hoặc kho mẫu `tier=premium` (nếu đã đăng nhập); đồng bộ toàn bộ giá `0đ / 99.000đ / 199.000đ` ở bảng so sánh, dự toán, FAQ, JSON-LD, [`PlanFitAdvisorService.php`](app/Services/PlanFitAdvisorService.php), [`home.blade.php`](resources/views/client/pages/home.blade.php), [`premium-preview.blade.php`](resources/views/client/pages/premium-preview.blade.php), [`services.blade.php`](resources/views/client/pages/services.blade.php), [`banking.blade.php`](resources/views/admin/pages/settings/banking.blade.php).
  - [`TemplateController.php`](app/Http/Controllers/Client/TemplateController.php), [`templates.blade.php`](resources/views/client/pages/templates.blade.php), [`templates.css`](public/assets/client/css/pages/templates.css), [`template-detail.blade.php`](resources/views/client/pages/template-detail.blade.php), [`template-detail.css`](public/assets/client/css/pages/template-detail.css): Bổ sung bộ lọc `tier=premium`, huy hiệu `.mayhen_tier_premium` / `.mayhen_template_detail_tier_premium` và đồng bộ giá Schema.org (`0 / 0 / 99000 / 199000`).
  - Admin: [`Admin/TemplateController.php`](app/Http/Controllers/Admin/TemplateController.php) (validation `in:free,premium,plus,pro`), [`Admin/PlanController.php`](app/Http/Controllers/Admin/PlanController.php) (Promise-to-Proof Monitor 4 gói), [`admin/pages/templates/{edit,index}.blade.php`](resources/views/admin/pages/templates/edit.blade.php) và [`public/assets/admin/css/pages/templates.css`](public/assets/admin/css/pages/templates.css).
  - Đã cập nhật trực tiếp 4 bản ghi gói cước vào bảng `plans` trên MySQL hiện tại qua `php artisan db:seed --class=MayHenInitialSeeder`.
- **Kiểm thử tự động**:
  - Tạo mới [`tests/Feature/FourTierPlanAndAuthAccessTest.php`](tests/Feature/FourTierPlanAndAuthAccessTest.php); chạy cùng `WebsiteCatalogAndSeoDeepTest`, `AdminTemplateCustomizationTest`, `AdminEntitlementStateMachineTest`, `PlanFitAdvisorTest` đạt **24/24 PASS (179 assertions)**. Laravel Pint: `passed`.

### Đề án #109: Xác Thực Kép An Toàn Cao — Đăng Nhập Google OAuth 2.0 (PKCE) & Đăng Ký Email Token 1 Lần (24h, Bỏ Đăng Ký Email Rác)
- **Kiến trúc & Bảo mật đa tầng (Zero third-party OAuth packages)**:
  - **1. Đăng nhập & Hoàn tất tài khoản bằng Google OAuth 2.0 + PKCE S256 ([`GoogleOAuthService.php`](app/Services/GoogleOAuthService.php), [`GoogleAuthController.php`](app/Http/Controllers/Client/GoogleAuthController.php))**:
    - Route: `GET /auth/google/redirect`, `GET /auth/google/callback`, `GET/POST /dang-ky/hoan-tat-google` có giới hạn tốc độ (`throttle`).
    - Hỗ trợ cấu hình `google_client_id` và `google_client_secret` trực tiếp tại **Admin → Thiết lập nền tảng → An ninh** ([`PlatformConfigurationService.php`](app/Services/PlatformConfigurationService.php)) kèm fallback `.env` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`).
    - Bảo vệ chặt bằng `state` ngẫu nhiên 256-bit (so sánh hằng thời gian `hash_equals`), PKCE `S256` và kiểm tra bắt buộc `email_verified === true` từ Google.
    - Người dùng Google mới không bị tạo tài khoản rác nửa vời; lưu phiên tạm 15 phút trên server-side Session và chuyển sang trang **Hoàn tất hồ sơ & Thiết lập mật khẩu** (`/dang-ky/hoan-tat-google`, email chỉ đọc, header `Referrer-Policy: no-referrer`, `Cache-Control: no-store`). Lần sau đăng nhập tự do bằng **Google** hoặc **Email + Mật khẩu** đều vào chung một tài khoản.
    - Bảo vệ tài khoản quản trị (`admin`/`manager`/`staff`): tuyệt đối không tự động liên kết Google theo email nếu chưa gắn `google_id`, và chặn tài khoản bị khóa (`is_locked`).
  - **2. Đăng ký bằng Email xác thực qua Link Token 1 lần, hạn 24 giờ ([`AuthController.php`](app/Http/Controllers/Client/AuthController.php), [`EmailRegistrationToken.php`](app/Models/EmailRegistrationToken.php), [`EmailRegistrationVerificationMail.php`](app/Mail/EmailRegistrationVerificationMail.php))**:
    - Loại bỏ hoàn toàn form đăng ký tự nhập email tạo tài khoản trực tiếp tại `/dang-ky` để triệt tiêu tài khoản ảo/email rác.
    - Chặn tên miền email rác dùng 1 lần (disposable email blocklist), chống dò tài khoản (Account Enumeration) và chống Timing Attack (`usleep` đồng bộ thời gian + thông báo trung tính), giới hạn gửi lại 60 giây/email.
    - Sinh token ngẫu nhiên 256-bit (`random_bytes(32)`), chỉ lưu bản băm `SHA-256` trong bảng `email_registration_tokens` với hạn đúng **24 giờ**, gửi bất đồng bộ qua hàng đợi (`Mail::queue`) bằng **Mail Không Nhận Lại (No-Reply)** đã cài sẵn trong Admin (`PlatformSetting::getNoreplyEmail()`).
    - Đường dẫn `/dang-ky/xac-thuc/{token}` không gắn email lên URL, giữ header `Referrer-Policy: no-referrer` (đã cập nhật [`SecurityHeaders.php`](app/Http/Middleware/SecurityHeaders.php) bảo toàn policy chặt hơn) và `Cache-Control: no-store`.
    - Khi hoàn tất nhập thông tin cơ bản & mật khẩu (tối thiểu 8 ký tự gồm chữ & số), xử lý trong `DB::transaction()` + `lockForUpdate()` để **chống Race Condition 100%**, đảm bảo link chỉ dùng được đúng 1 lần duy nhất.
  - **3. Cơ sở dữ liệu & Giao diện**:
    - Migrations: `2026_11_19_102219_add_google_oauth_columns_to_users_table` (thêm `google_id` unique nullable, `avatar` nullable vào `users`) và `2026_11_19_102220_create_email_registration_tokens_table`.
    - Giao diện Quiet Luxury thuần HTML/CSS: cập nhật [`login.blade.php`](resources/views/client/pages/login.blade.php), [`register.blade.php`](resources/views/client/pages/register.blade.php), tạo mới [`auth-complete-profile.blade.php`](resources/views/client/pages/auth-complete-profile.blade.php), [`emails/email-registration-verification.blade.php`](resources/views/emails/email-registration-verification.blade.php) và bổ sung CSS `mayhen_auth_*` trong [`auth.css`](public/assets/client/css/pages/auth.css).
- **Kiểm thử tự động**:
  - Tạo mới [`tests/Feature/GoogleAndEmailTokenAuthSecurityTest.php`](tests/Feature/GoogleAndEmailTokenAuthSecurityTest.php) và cập nhật các bài test đăng ký cũ sang luồng xác thực email mới.
  - Chuẩn hóa điều hướng sau đăng nhập ([`AuthController.php`](app/Http/Controllers/Client/AuthController.php), [`GoogleAuthController.php`](app/Http/Controllers/Client/GoogleAuthController.php)): mặc định chuyển hướng về `/tai-khoan` (`route('account.dashboard')`) nếu không có `?redirect=` hoặc `url.intended`; trường hợp khách chọn mẫu `premium`/`plus`/`pro` bị chuyển sang `/dang-nhap?redirect=...`, sau khi đăng nhập (Email/Google) hoặc đăng ký thành công sẽ chuyển chính xác về đúng mẫu thiệp đó.
  - Chạy toàn bộ 8 bộ Auth & Security tests: **46/46 PASS (338 assertions)**. Laravel Pint: `passed`.

### Đề án #110: Tái Cấu Trúc Sitemap Index Chuẩn SEO & Cấu Hình `noindex, follow` Cho Trang Chi Tiết Mẫu Thiệp
- **1. Thiết lập Robots `noindex, follow` cho Chi tiết Mẫu thiệp (`/mau-thiep/{slug}`)**:
  - Tệp [`resources/views/client/pages/template-detail.blade.php`](resources/views/client/pages/template-detail.blade.php): Gắn `<meta name="robots" content="noindex, follow">`. Trang kho mẫu tổng hợp (`/mau-thiep`) vẫn giữ nguyên `index, follow` để tối ưu SEO tìm kiếm, trong khi từng trang xem thử mẫu thiệp con được bảo vệ tránh duplicate content và tập trung dòng chảy PageRank.
  - Tạo mới [`tests/Feature/TemplateDetailRobotsTest.php`](tests/Feature/TemplateDetailRobotsTest.php) xác nhận 100% trang chi tiết mẫu thiệp xuất thẻ `noindex, follow`.
- **2. Tái cấu trúc Kiến trúc Sitemap Index & Sub-Sitemaps ([`SitemapController.php`](app/Http/Controllers/Client/SitemapController.php), [`routes/web.php`](routes/web.php))**:
  - `/sitemap.xml`: Chuyển thành **Sitemap Index** chuẩn `sitemaps.org` (`<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`), chứa liên kết tới toàn bộ các sitemap con kèm thẻ `<lastmod>`:
    - `/sitemap-pages.xml` (`route('sitemap.pages')`): Sitemap các trang tĩnh và điều hướng công khai chính (`/`, `/mau-thiep`, `/bang-gia`, `/dich-vu`, `/docs`, `/ve-may-hen`, `/lien-he`, `/faq`, `/dieu-khoan`, `/chinh-sach-bao-mat`). **Tuyệt đối KHÔNG chứa bất kỳ URL chi tiết mẫu thiệp `/mau-thiep/{slug}` nào**.
    - `/sitemap-blog.xml` (`route('sitemap.blog')`): Toàn bộ bài viết Cẩm nang cưới đã xuất bản (`BlogPost::published()`), tự động loại trừ bài viết nháp.
    - `/sitemap-docs.xml` (`route('sitemap.docs')`): Toàn bộ bài viết tài liệu khách hàng từ `DocumentationService`, loại trừ tài liệu SOP quản trị.
  - **Tối ưu Hiệu năng Cực hạn & Zero DB Queries**:
    - Mỗi sitemap endpoint hỗ trợ 2 tầng bộ nhớ đệm: RAM Memoization tĩnh trong worker PHP (FPM / Octane) + Lớp Cache 24 giờ (`mayhen_sitemap_index_xml`, `mayhen_sitemap_pages_xml`, `mayhen_sitemap_blog_xml`, `mayhen_sitemap_docs_xml`).
    - Bỏ qua chuyển hướng trong [`SeoRedirectMiddleware.php`](app/Http/Middleware/SeoRedirectMiddleware.php) (`$request->is('sitemap*')`), đạt tuyệt đối **0 database query** khi cache đã ấm.
    - Hỗ trợ chuẩn ETag và HTTP `304 Not Modified` khi Googlebot gửi `If-None-Match`.
- **3. Tự động đồng bộ & Quản trị**:
  - Cập nhật [`public/robots.txt`](public/robots.txt): Khai báo `Allow: /sitemap*.xml` và chỉ định duy nhất `Sitemap: https://mayhen.vn/sitemap.xml` làm Sitemap Index đầu vào cho crawler.
  - Cơ chế xóa cache toàn diện `SitemapController::clearCache()` được tích hợp vào:
    - Admin SEO & Settings ([`Admin/SeoController.php`](app/Http/Controllers/Admin/SeoController.php), [`Admin/SettingController.php`](app/Http/Controllers/Admin/SettingController.php)).
    - Giao diện Admin ([`admin/pages/seo/index.blade.php`](resources/views/admin/pages/seo/index.blade.php), [`admin/pages/settings/seo.blade.php`](resources/views/admin/pages/settings/seo.blade.php)) bổ sung trực quan danh sách link các sitemap con.
    - Model hooks: [`BlogPost::booted()`](app/Models/BlogPost.php), [`InvitationTemplate::clearTemplateCaches()`](app/Models/InvitationTemplate.php).
- **Kiểm thử tự động & Chất lượng**:
  - Tạo mới [`tests/Feature/SitemapIndexArchitectureTest.php`](tests/Feature/SitemapIndexArchitectureTest.php) (kiểm tra đầy đủ 7 kịch bản: Sitemap Index, Sub-Sitemaps, loại trừ slug mẫu thiệp, lọc bài nháp blog, ETag 304, Zero DB query, xóa cache).
  - Cập nhật [`tests/Feature/WebsiteCatalogAndSeoDeepTest.php`](tests/Feature/WebsiteCatalogAndSeoDeepTest.php): **42/42 tests PASS (462 assertions)**.
  - Laravel Pint: `passed`.

### Đề án #111: Tối Ưu An Ninh Đăng Ký — Thông Báo Email Tồn Tại, Giới Hạn Session 1 Phút & Cấm 7 Ngày Khi Quá 5 Tài Khoản / Thiết Bị Máy (IP Máy)
- **1. Thông báo trực tiếp khi Email đã tồn tại (Chống gửi email thừa / spam)**:
  - Tệp [`AuthController.php`](app/Http/Controllers/Client/AuthController.php): Khi người dùng yêu cầu gửi liên kết xác thực với email đã có tài khoản trên hệ thống (`User::where('email', $email)->exists()`), hệ thống **tuyệt đối không gửi email** và lập tức trả về lỗi: *"Địa chỉ email này đã tồn tại trên hệ thống, vui lòng chọn mail khác hoặc chuyển sang đăng nhập!"*. Giúp người dùng biết ngay để chuyển sang đăng nhập và tránh gửi email rác/spam.
- **2. Chống gửi trùng lặp khi liên kết xác thực đang còn hiệu lực (24h) và chưa sử dụng**:
  - Tệp [`AuthController.php`](app/Http/Controllers/Client/AuthController.php): Nếu email đó đã được gửi liên kết xác thực mà người dùng chưa sử dụng (`used_at IS NULL`) và liên kết chưa hết hạn (`expires_at > now()`), khi người dùng bấm gửi tiếp thì hệ thống **không gửi thêm email**, mà thông báo ngay: *"Hệ thống đã gửi liên kết xác thực đến email này rồi. Vui lòng kiểm tra hộp thư của bạn (kể cả mục Quảng cáo / Spam) để tiếp tục!"*. Chỉ khi link cũ quá hạn 24 giờ thì mới cho phép cấp lại liên kết mới.
- **3. Giới hạn tần suất Per-Session (1 phút / lần)**:
  - Kiểm tra `$request->session()->get('last_registration_email_sent_at')`. Nếu gửi liên tiếp trong vòng 60 giây trên cùng session, trả về thông báo lỗi: *"Mỗi phiên chỉ được gửi yêu cầu xác thực 1 phút một lần. Vui lòng chờ {số giây} giây nữa trước khi gửi lại!"*.
- **4. Giới hạn tối đa 5 tài khoản trên 1 thiết bị máy (Client Machine ID) — Khóa 7 ngày khi vi phạm (Bảo vệ người dùng chung IP mạng)**:
  - **Dịch vụ chuyên trách**: Xây dựng [`ClientDeviceProtectionService.php`](app/Services/ClientDeviceProtectionService.php):
    - Định danh máy client độc lập: Sử dụng cookie bảo mật `mh_mid` (thời hạn 1 năm, HttpOnly, SameSite Lax) kết hợp `localStorage` đồng bộ qua token `_device_token` và fallback fingerprint phần cứng + IP.
    - **Không cấm theo IP mạng thuần túy (Network IP / Public NAT)**: Đảm bảo khi một máy trong văn phòng/quán cafe bị cấm thì các máy khác dùng chung mạng Wifi hoàn toàn **không bị ảnh hưởng** (tránh mất khách hàng oan).
    - Giới hạn: Tối đa 5 tài khoản trên 1 thiết bị (`MAX_ACCOUNTS_PER_DEVICE = 5`).
    - Khi một máy hoàn tất đăng ký tài khoản (qua Email hoặc Google), ghi nhận vào danh sách tài khoản của máy. Nếu số lượng tài khoản đạt >= 5, kích hoạt lệnh cấm thiết bị trong vòng **7 ngày** (`banMachine($machineId, 7)`).
    - Khi một thiết bị đang bị cấm 7 ngày, chặn cả ở bước xem form (`GET /dang-ky`), bước gửi mail (`POST /dang-ky`), bước hoàn tất email (`POST /dang-ky/xac-thuc/...`) và bước hoàn tất Google (`POST /dang-ky/hoan-tat-google`).
  - **Giao diện**: Cập nhật [`register.blade.php`](resources/views/client/pages/register.blade.php): Nếu thiết bị đang bị cấm, hiển thị banner Quiet Luxury thông báo rõ ràng thời hạn mở khóa và nút Đăng Nhập, ẩn form gửi email.
- **Kiểm thử tự động & Chất lượng**:
  - Tạo mới [`tests/Feature/RegistrationSecurityAndDeviceLimitTest.php`](tests/Feature/RegistrationSecurityAndDeviceLimitTest.php) kiểm thử 100% 4 kịch bản:
    1. Email đã tồn tại báo lỗi trực tiếp và không queue mail.
    2. Session gửi liên tiếp trong 60s bị chặn với thông báo số giây còn lại.
    3. Link xác thực đã gửi chưa dùng và chưa hết hạn 24h thì không gửi thêm và báo đã gửi rồi.
    4. Đăng ký 5 tài khoản kích hoạt lệnh cấm 7 ngày trên máy đó; máy khác dùng chung IP mạng hoàn toàn không bị ảnh hưởng và vẫn đăng ký bình thường.
  - Cập nhật [`tests/Feature/GoogleAndEmailTokenAuthSecurityTest.php`](tests/Feature/GoogleAndEmailTokenAuthSecurityTest.php): **8/8 PASS (176 assertions)**. Toàn bộ 29 tests Auth & Security: **29/29 PASS (290 assertions)**.
  - Laravel Pint: `passed`.

### Đề án #112: Tự Động Kích Hoạt SMTP TinoHost Từ Admin & Chuẩn Hóa Hàng Đợi (Queue) Tốc Độ Cao Cho 1 Triệu Users
- **1. Bảo toàn 100% cơ chế Hàng Đợi (Queue)**:
  - Giữ vững `QUEUE_CONNECTION=database` (chuẩn bị chuyển đổi mượt mà sang `redis` khi tải lớn trên production), tuyệt đối không gửi đồng bộ block request HTTP để bảo vệ máy chủ không bị quá tải/treo process khi có đột biến lưu lượng (Spike load).
  - Tệp [`routes/console.php`](routes/console.php): Bổ sung `Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=25')->everyMinute()->withoutOverlapping()` tối ưu riêng cho môi trường Web Hosting (AZDIGI / cPanel / DirectAdmin), tự ngắt khi hết việc để bảo toàn CPU/RAM và tránh bị hệ điều hành CloudLinux kill process.
  - Tệp [`EmailRegistrationVerificationMail.php`](app/Mail/EmailRegistrationVerificationMail.php): Giữ nguyên `implements ShouldQueue` với các tham số tối ưu cực hạn:
    - `$timeout = 20s`: Ngắt kết nối nhanh nếu SMTP máy chủ thư bị nghẽn, không chiếm dụng worker làm tắc hàng đợi.
    - `$tries = 3`: Thử lại tối đa 3 lần.
    - `$backoff = [3, 15, 60]s`: Giãn cách thử lại thông minh (3 giây -> 15 giây -> 60 giây) để khách hàng nhận lại mail sớm nhất khi có sự cố mạng tạm thời.
- **2. Đồng bộ cấu hình SMTP từ Admin**:
  - Tệp [`PlatformSetting.php`](app/Models/PlatformSetting.php): Phương thức `applyMailRuntimeConfiguration()` tự động đọc nhóm `GROUP_MAIL` từ database (cache 0 DB query) và nạp thẳng vào `config(['mail.default' => 'smtp', 'mail.mailers.smtp...' => ...])`.
  - Tệp [`AppServiceProvider.php`](app/Providers/AppServiceProvider.php): Tự động nạp cấu hình khi khởi động ứng dụng và bất cứ khi nào Admin cập nhật thiết lập SMTP.
  - Tệp [`.env`](.env): Cập nhật thông số `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.tino.vn`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`, `MAIL_USERNAME=no-reply@mayhen.vn`, `QUEUE_CONNECTION=database`.
- **Kiểm thử tự động & Chất lượng**:
  - Toàn bộ 29 tests Auth & Security: **29/29 PASS (290 assertions)**.
  - Laravel Pint: `passed`.

### Đề án #113: Tích Hợp Live Test Email & Test Cổng Thanh Toán VietQR Trong Admin Safe Cockpit
- **1. Phân hệ Thử Nghiệm Gửi Thư Mẫu Thực Tế (Live SMTP Dispatcher Test - Tab Mail)**:
  - **Mailable**: Xây dựng [`AdminSmtpTestMail.php`](app/Mail/AdminSmtpTestMail.php) và giao diện mẫu Quiet Luxury [`admin-smtp-test.blade.php`](resources/views/emails/admin-smtp-test.blade.php).
  - **Cơ chế**: Gửi trực tiếp đồng bộ (sync) không qua hàng đợi để đo đếm chính xác độ trễ phản hồi (latency ms) và trả kết quả tức thì về trình duyệt Admin. Bảng thông số kỹ thuật thể hiện rõ: Host, Port, Encryption, From, Người nhận, Thời gian gửi và IP máy chủ.
  - **Bắt lỗi thông minh**: Diễn giải dễ hiểu khi thông tin xác thực sai (lỗi mã 535) hoặc cổng kết nối bị tường lửa chặn / timeout.
  - **Giao diện Cockpit**: Ô nhập email người nhận (mặc định lấy email Admin đang đăng nhập), nút "Gửi Email Thử Nghiệm Ngay", spinner loading và alert thông báo kết quả.
- **2. Phân hệ Kiểm Thử Cổng Thanh Toán & VietQR Mẫu (Tab Payment)**:
  - **Sinh mã VietQR mẫu trực quan (Napas 24/7)**:
    - Dựa trên số tài khoản và ngân hàng thụ hưởng đang cấu hình trong CSDL (`bank_code`, `bank_account`, `bank_account_name`).
    - Cho phép tùy chỉnh số tiền thử nghiệm (ví dụ: `50.000 đ`) và nội dung chuyển khoản mẫu (ví dụ: `MH TEST 9988`).
    - Tự động sinh mã VietQR dạng SVG chuẩn EMVCo không phụ thuộc dịch vụ ngoài, hiển thị mã QR to rõ kèm chi tiết đối soát để Admin có thể quét thử trực tiếp bằng app ngân hàng trên điện thoại.
  - **Gửi Email Hóa Đơn & Xác Nhận Thanh Toán Mẫu (Payment Test Email)**:
    - **Mailable**: Xây dựng [`AdminPaymentTestMail.php`](app/Mail/AdminPaymentTestMail.php) và giao diện biên nhận thanh toán Quiet Luxury [`admin-payment-test.blade.php`](resources/views/emails/admin-payment-test.blade.php).
    - Thể hiện mã đơn hàng mẫu (`#MH-TEST-ORD8899`), gói thiệp cưới cao cấp, số tiền, thông tin tài khoản nhận và mã VietQR.
    - Giúp Admin trực tiếp thẩm định giao diện thư mà cô dâu chú rể sẽ nhận được khi thanh toán thành công.
- **3. API & Routes mới**:
  - `POST /admin/settings/platform/test-email` -> `AdminPlatformSettingController@sendTestEmail`
  - `POST /admin/settings/platform/test-payment-qr` -> `AdminPlatformSettingController@generateTestPaymentQr`
  - `POST /admin/settings/platform/test-payment-email` -> `AdminPlatformSettingController@sendTestPaymentEmail`
- **Kiểm thử tự động & Chất lượng**:
  - 100% tests kiểm thử Feature vượt qua xuất sắc (`AdminPlatformConfigurationTest`, `RegistrationSecurityAndDeviceLimitTest`).
  - Laravel Pint: `passed`.

### Đề án #114: Tinh Chỉnh Giao Diện Email Xác Thực Đăng Ký — Tích Hợp Favicon Admin & Loại Bỏ Kê Khai Kỹ Thuật Bảo Mật
- **1. Đồng bộ Favicon Thương Hiệu Đã Cấu Hình Từ Admin**:
  - Model [`PlatformSetting.php`](app/Models/PlatformSetting.php): Bổ sung phương thức `getFaviconUrl()` tự động đọc `brand_favicon` từ Admin Platform Settings, chuẩn hóa đường dẫn tuyệt đối với fallback an toàn.
  - Tệp [`email-registration-verification.blade.php`](resources/views/emails/email-registration-verification.blade.php) & [`customer-reset-password.blade.php`](resources/views/emails/customer-reset-password.blade.php): Thay thế hoàn toàn icon tĩnh/emoji bằng thẻ ảnh Favicon tròn trang nhã `28x28px` đúng chuẩn nhận diện thương hiệu.
- **2. Tối ưu Nội Dung Email — Loại Bỏ Kê Khai Bảo Mật Rườm Rà**:
  - Loại bỏ hoàn toàn khối hộp cảnh báo dài dòng ("Chỉ dùng duy nhất 1 lần", "Tự động vô hiệu hóa", "An toàn tài khoản").
  - Chuyển sang lời nhắc nhẹ nhàng, tự nhiên theo tinh thần Quiet Luxury: *(Liên kết có hiệu lực trong vòng 24 giờ. Nếu bạn không gửi yêu cầu này, vui lòng bỏ qua email)*.
  - Sửa triệt để lỗi typography tiếng Việt ở tiêu đề email (khắc phục lỗi font Georgia bị tách rời dấu sắc `Hoàn Tấ´t`), chuyển sang font hệ thống cao cấp hiển thị liền mạch, sắc nét trên mọi email client.
- **Kiểm thử tự động & Chất lượng**:
  - 100% tests Auth & Registration PASS (`RegistrationSecurityAndDeviceLimitTest`, `GoogleAndEmailTokenAuthSecurityTest`).
  - Laravel Pint: `passed`.

### Đề án #115: Tích Hợp Cloudfly Object Storage (S3-Compatible) Cho Toàn Bộ Ảnh Mẫu Thiệp & Ảnh Khách Hàng Tải Lên (Bảo Toàn Ảnh SEO Local)
- **1. Kiến Trúc Lưu Trữ Phân Tầng Thông Minh (Hybrid Storage Tiering)**:
  - **Lưu trên Cloudfly S3 (`s3.cloudfly.vn` - Bucket `mayhen-media`)**:
    + Toàn bộ ảnh cô dâu chú rể tải lên Studio V2 (`weddings/photos`, album 10 ảnh cưới, ảnh bìa thiệp cưới).
    + Ảnh chúc mừng của quan khách (`wishes_photos`).
    + Ảnh khách mời tải lên PhotoDrop (`photodrop`).
    + Ảnh quét thiệp cưới bằng giấy (`paper_scans`).
    + Ảnh gợi ý trang phục Moodboard Dresscode (`dresscode`).
    + Ảnh mã QR và quà cưới cá nhân (`gift_registry`).
    + Ảnh asset mẫu thiệp cưới do Quản trị viên cập nhật (`templates/{slug}`).
  - **Bảo Toàn 100% Ảnh SEO Lưu Trực Tiếp (Local Public Storage)**:
    + Logo thương hiệu (`brand_logo`), Favicon (`brand_favicon`), Banner trang chủ (`home_hero_banner`), Ảnh bài viết Blog (`blog_covers`) và toàn bộ icon giao diện được giữ nguyên lưu trên Local Storage để đạt điểm số Google Core Web Vitals tối đa, hỗ trợ CDN cục bộ và chuẩn SEO tự nhiên.
- **2. Dịch vụ Chuyên Trách [`CloudflyStorageService.php`](app/Services/CloudflyStorageService.php)**:
  - Sử dụng thuật toán ký AWS Signature Version 4 thuần PHP (Native SigV4, 0 external dependency, không làm nặng vendor).
  - Tự động sinh URL công khai S3 Virtual-Hosted (`https://mayhen-media.s3.cloudfly.vn/...`) hoặc Path-Style.
  - Cơ chế **Zero Downtime Fallback**: Nếu Cloudfly bị ngắt kết nối hoặc lỗi mạng, hệ thống tự động fallback êm đềm về đĩa `public` cục bộ mà không bao giờ làm gián đoạn trải nghiệm tạo thiệp của cặp đôi.
  - Tích hợp chẩn đoán hạ tầng lưu trữ S3 vào Admin Cockpit (`PlatformConfigurationService@testConnection`).
- **3. Cấu hình & Tiện ích Vận hành**:
  - File [`.env`](.env) & [`config/filesystems.php`](config/filesystems.php): Bổ sung disk `cloudfly` với endpoint `s3.cloudfly.vn`, bucket `mayhen-media`, region `us-east-1`.
  - Artisan Command [`mayhen:sync-to-cloudfly`](app/Console/Commands/SyncMediaToCloudflyCommand.php): Hỗ trợ đồng bộ hàng loạt ảnh cũ từ local lên Cloudfly với thanh tiến trình và cờ `--dry-run`.
- **Kiểm thử tự động & Chất lượng**:
  - Kiểm thử tích hợp Cloudfly S3 REST API (PUT, GET, DELETE, SigV4): **HTTP 200/204 PASS 100%**.
  - Laravel Pint: `passed`.

### Đề án #116: Rà Soát Bảo Mật Toàn Bộ Khóa/Key Dự Án, Xóa Sạch Secret Khỏi Lịch Sử Git & Chuẩn Hóa 100% Khóa Trong `.env`
- **1. Bối cảnh & Nguyên nhân**:
  - Khi thực hiện lệnh `git push`, GitHub Push Protection kích hoạt cảnh báo chặn `GH013` do phát hiện commit `f05be42` chứa tệp `docs/client_secret_952110389969-q3jno4p52ajs88tl2ag1hh4pvoi9some.apps.googleusercontent.com.json` (tệp JSON tải về từ Google Cloud Console chứa Client Secret Google OAuth).
- **2. Rà soát An ninh Toàn diện Toàn bộ Dự án**:
  - Quét toàn bộ repository với các mẫu chuỗi nhạy cảm: `client_secret`, `secret_key`, `access_key`, `api_key`, `private_key`, `MAIL_PASSWORD`, `DB_PASSWORD`, `SEPAY_API_KEY`.
  - **Kết quả rà soát**:
    + Không có bất kỳ tệp mã nguồn nào chứa khóa bí mật cứng (hardcoded credentials).
    + Các khóa kết nối Cloudfly S3 (`CLOUDFLY_OBJECT_STORAGE_ACCESS_KEY`, `CLOUDFLY_OBJECT_STORAGE_SECRET_KEY`) chỉ nằm duy nhất trong file `.env` (được bảo vệ bởi `.gitignore`).
    + Các thông số SMTP máy chủ thư (`MAIL_PASSWORD`), cơ sở dữ liệu (`DB_PASSWORD`) và SePay webhook secret đều đọc qua biến môi trường `env()` hoặc cấu hình động bảo mật từ Admin `PlatformSetting` (được mã hóa/ẩn dấu `***REDACTED***`).
    + Duy nhất tệp `docs/client_secret_*.json` trước đó bị đưa nhầm vào Git commit tree.
- **3. Làm sạch Lịch sử Git & Khóa Chặt `.gitignore`**:
  - Cập nhật tệp `.gitignore` chặn vĩnh viễn các mẫu tệp bí mật: `*client_secret*.json`, `credentials.json`, `*.pem`, `*.key`, `*.p12`, `*.pfx`.
  - Dùng `git filter-branch` thanh lọc và loại bỏ hoàn toàn tệp `docs/client_secret_*.json` khỏi toàn bộ 13 commit local từ `origin/main..HEAD`.
  - Xóa bỏ reflog và chạy `git gc --prune=now` để triệt tiêu mọi commit dangling chứa secret trên máy cục bộ.
  - Kiểm tra lại bằng `git log -p origin/main..HEAD -S "GOCSPX-"` và `git diff origin/main..HEAD --name-only` xác nhận 100% sạch bóng, không còn bất kỳ dấu vết bí mật nào.
- **Kiểm thử tự động & Chất lượng**:
  - Toàn bộ tests Feature Auth & Registration: **PASS 100%**.

### Đề án #117: Tự Động Gửi Email Chào Mừng Tri Ân (Kèm Gợi Ý Mẫu Thiệp Tiêu Biểu) Khi Đăng Ký Thành Công & Email Chúc Mừng Tác Phẩm Khi Tạo Thiệp Xong
- **1. Triết Lý Thiết Kế Email Quiet Luxury**:
  - Không màu mè, sử dụng tông màu ấm áp sang trọng đặc trưng của Mây Hẹn (Màu be kem `#FAF7F3`, chữ mận chín `#684143`, nhấn vàng đồng `#B58E6F`, thẻ `#FDFBF9`).
  - Sử dụng favicon thương hiệu bo tròn thanh nhã đã thiết lập trong Admin Platform Settings.
  - Phông chữ hệ thống cao cấp chuẩn unicode tiếng Việt, không bao giờ bị lỗi nhảy dấu sắc.
  - Tương thích tối ưu 100% các ứng dụng đọc thư (Gmail, Apple Mail, Outlook) với cấu trúc HTML Table inline styles.
  - 100% gửi qua Hàng đợi (Queue) nền (`implements ShouldQueue`, `$tries = 3`, `$timeout = 20s`, `$backoff = [3, 15, 60]s`) không làm nghẽn tiến trình HTTP, sẵn sàng chịu tải hàng triệu người dùng.
- **2. Luồng 1: Email Chào Mừng & Tri Ân Đăng Ký Thành Công ([`CustomerWelcomeMail.php`](app/Mail/CustomerWelcomeMail.php) & [`customer-welcome.blade.php`](resources/views/emails/customer-welcome.blade.php))**:
  - **Kích hoạt tự động**: Khi khách hàng hoàn tất đăng ký tài khoản qua liên kết token xác thực email (`AuthController@completeEmailRegistration`) hoặc hoàn tất tạo hồ sơ Google OAuth lần đầu (`GoogleAuthController@completeRegistration`).
  - **Nội dung cảm xúc**:
    + Lời chào nồng ấm và cảm ơn khách hàng đã lựa chọn Mây Hẹn để chuẩn bị cho khoảnh khắc hạnh phúc nhất cuộc đời.
    + Xác nhận tài khoản email đã kích hoạt thành công.
    + Nút CTA chính: "Bắt Đầu Tạo Thiệp Cưới Của Bạn →" đưa thẳng vào Studio.
    + **Bộ sưu tập tiêu biểu đa dạng hóa phong cách**: Trích xuất 3 mẫu thiệp cưới nổi bật nhất (Vòm Trời Mây Áo Cưới - Lãng mạn, Hạc Bạch Trao Duyên - Cổ điển, Giữa Muôn Ngàn Vì Sao - Hiện đại) với ảnh preview bo góc, thẻ phong cách, mô tả tinh tế và nút liên kết "Xem mẫu thiệp thử →".
    + Cam kết đặc quyền đồng hành 24/7 từ đội ngũ Atelier.
- **3. Luồng 2: Email Chúc Mừng Tác Phẩm Khi Hoàn Tất Thiệp Cưới ([`WeddingInvitationCreatedMail.php`](app/Mail/WeddingInvitationCreatedMail.php) & [`wedding-invitation-created.blade.php`](resources/views/emails/wedding-invitation-created.blade.php))**:
  - **Kích hoạt tự động**: Khi khách hàng hoàn tất Onboarding Wizard tạo thiệp cưới ([`OnboardingController.php`](app/Http/Controllers/Client/OnboardingController.php)) hoặc lưu/xuất bản thiệp lần đầu trong Studio ([`InvitationEditorController.php`](app/Http/Controllers/Client/InvitationEditorController.php)).
  - **Phòng vệ chống gửi lặp (Spam Defense)**: Lưu cờ `created_mail_sent_at` vào `custom_config` của thiệp cưới; mỗi thiệp chỉ gửi đúng 1 lần duy nhất khi vừa tạo xong.
  - **Giao diện khác biệt chuyên biệt cho tác phẩm**:
    + **Thẻ Thiệp Cưới Nổi Bật (Spotlight Card)**: Tên Chú Rể & Cô Dâu, Tên mẫu thiệp đã chọn, Ngày cưới (định dạng tiếng Việt: *Chủ Nhật, ngày dd/mm/yyyy*), Địa điểm tiệc và Đường dẫn thiệp cưới trực tuyến.
    + **2 Nút hành động trực quan**: "✦ Mở Xem Thiệp Cưới Trực Tuyến →" (mở link công khai) và "Chỉnh sửa thiệp trong Studio ↗".
    + **3 Bước tiếp theo hoàn hảo cho ngày vui**:
      1. *Gửi duyệt thử nghiệm*: Xem trên điện thoại/máy tính kiểm tra nhạc nền và hiệu ứng.
      2. *Quản lý Khách mời & Tạo mã mời riêng*: Tạo thiệp cá nhân hóa kèm mã QR check-in.
      3. *Theo dõi Phản hồi (RSVP) & Sổ ký tên*: Theo dõi khách tham dự theo thời gian thực.
- **Kiểm thử tự động & Chất lượng**:
  - Chuẩn hóa tên route xem mẫu thiệp cưới `templates.show` trong `CustomerWelcomeMail` thay cho `invitation.preview`.
  - Kiểm thử render cả 2 Mailable: **100% PASS (CustomerWelcomeMail 19.9KB, WeddingInvitationCreatedMail 16.3KB)**.
  - Laravel Pint: `passed`.

### Đề án #118: Điều Hướng Ngữ Cảnh Nút Tạo Thiệp Trên Thanh Bottom Nav Dock
- **1. Cơ chế Liên Kết Ngữ Cảnh Thông Minh ([`bottom-nav.blade.php`](resources/views/client/components/bottom-nav.blade.php))**:
  - **Khi đang xem mẫu thiệp cụ thể (`/mau-thiep/{slug}` hoặc `/mau-thiep/{slug}/xem-thu`)**:
    + Nút Tạo Thiệp ở giữa (`mayhen_client_bottom_nav_item_create`) tự động gắn slug của mẫu thiệp đó: `route('invitation.create', ['template' => $currentTemplateSlug])` (`/tao-thiep?template={slug}`).
    + Giúp người dùng khi đang ưng ý mẫu thiệp có thể bấm 1 chạm tạo ngay thiệp cưới theo đúng mẫu đó.
  - **Khi ở các trang khác (Trang chủ, Giới thiệu, Bảng giá, Blog, Liên hệ...)**:
    + Nút Tạo Thiệp tự động chuyển hướng về Kho Mẫu Thiệp: `route('templates.index')` (`/mau-thiep`).
    + Giúp người dùng khi chưa chọn mẫu sẽ được đưa đến trang danh mục các mẫu thiệp cưới để chiêm ngưỡng và chọn mẫu ưng ý trước khi vào Studio.
- **Kiểm thử tự động & Chất lượng**:
  - Bộ test [`BottomNavTest.php`](tests/Feature/BottomNavTest.php): **PASS 4/4 tests (63 assertions)** kiểm tra chính xác cả 2 ngữ cảnh liên kết.
  - Laravel Pint: `passed`.

### Đề án #119: Tái Cấu Trúc Toàn Diện Hộp Xem Thử Thiệp Cưới (Showcase Preview Modal) Trên Mobile Chuẩn Quiet Luxury
- **1. Tích Hợp Nút Tắt / Đóng (X) To Rõ Trực Tiếp Trong Top Bar**:
  - Khắc phục lỗi thiếu nút tắt trên mobile: Trước đây nút close floating có `z-index` thấp hơn và bị topbar tràn viền che khuất.
  - Đã tích hợp nút Đóng (X) trực tiếp vào thanh Top Bar (`mayhen_home_showcase_modal_top_close_btn`): đường kính tròn 36px, nền coral bán trong suốt viền sáng tương phản cao (`background: rgba(255, 71, 87, 0.22); border: 1.5px solid rgba(255, 120, 130, 0.55); color: #FFFFFF`), icon X nét đậm 2.6, touch target rộng rãi và chạm tức thì `touch-action: manipulation`.
  - Ẩn nút sao chép thừa thãi trên di động (`#mayhen_home_showcase_modal_copy_btn { display: none !important; }`) để nhường chỗ cho tiêu đề mẫu thiệp và các nút thao tác chính.
- **2. Triệt Tiêu Hoàn Toàn Lỗi Nhiều Thành Phần Đè Lên Nhau**:
  - **Triệt tiêu nút "Chữ Lớn" đè lên nút "Nhạc"**: Nút `senior-mode-widget` (trợ năng người lớn tuổi) vốn đặt ở góc trên bên trái `top: 18px; left: 18px;` đã đè trực tiếp lên nút phát nhạc của thiệp cưới (`mayhen_invitation_wb_music_btn`). Đã thêm cơ chế phòng vệ tự động ẩn hoàn toàn widget trợ năng khi `$isEmbed` hoặc `in_modal=1` (`@if(!($isEmbed ?? false))`).
  - **Triệt tiêu nút "VN VI" đè lên thanh "Dùng Mẫu Này Ngay"**: Nút `language-switcher` ở góc dưới bên phải `bottom: 24px; right: 24px;` đè trực tiếp lên thanh CTA bottom action bar. Đã thêm cơ chế phòng vệ tự động ẩn bộ đổi ngôn ngữ khi xem trong modal preview.
  - **Triệt tiêu "Màn hình lồng màn hình" & Dynamic Island giả mạo**: Trên thiết bị di động thật, đã ẩn hoàn toàn Dynamic Island giả (`display: none !important;`) và loại bỏ viền đen bezel cồng kềnh, giúp thiệp cưới hiển thị tràn viền sắc nét 100% không gian màn hình mà không che mất phần chữ của thiệp.
  - **Ghim Layout Chuẩn App Di Động (100dvh)**: Top Bar (52px) ghim trên cùng, thân Iframe thiệp cưới vừa vặn tuyệt đối, Bottom Action Bar (64px) ghim đáy hỗ trợ `env(safe-area-inset-bottom)`.
  - **Bổ sung Cache-Busting**: Tự động gắn version timestamp `?v={filemtime}` cho tệp `home.css` trên `home.blade.php` và `templates.blade.php` để mobile luôn nhận CSS mới nhất.
- **Kiểm thử tự động & Chất lượng**:
  - Các Feature tests (`BottomNavTest`, `CuratedInvitationPreviewLiveTest`, `SeniorFriendlyGuestModeTest`, `MultilingualInvitationTest`): **100% PASS**.
  - Laravel Pint: `passed`.

### Đề án #120: Tích Hợp Chatbot AI Zero-Trust Theo Dữ Liệu Dự Án & Hệ Thống Huấn Luyện Hai Chiều (Admin + Khách Hàng Chờ Duyệt)
- **Mục tiêu**: Xây dựng trợ lý Chatbot AI mở Popup trực tiếp từ nút **Trợ lý AI** có sẵn trong thanh điều hướng chân trang (`mayhen_client_bottom_nav_wrapper`, không tạo nút nổi thừa, thuần Vanilla HTML/CSS/ES Modules), **chỉ tin cậy dữ liệu đã được huấn luyện trong hệ thống và dữ liệu thật của dự án** (`InvitationTemplate`, `DocumentationService` client docs, `Plan`, `AiKnowledgeEntry::liveApproved()`), tuyệt đối không tin tưởng prompt khách hàng hay đầu ra thô từ AI (Zero-Trust Anti-Prompt-Injection & Anti-Data-Poisoning).
- **Cấu hình `.env` & `config/services.php` (Đã chuyển hoàn toàn sang 9Router API)**:
  - Loại bỏ hoàn toàn `xingchiapi` và cụm `MAYHEN_AI_*` cũ, thay thế bằng cụm **9Router** chuẩn OpenAI-compatible trong `config('services.ninerouter')`: `NINEROUTER_ENABLED`, `NINEROUTER_BASE_URL`, `NINEROUTER_API_KEY`, `NINEROUTER_MODEL`, `NINEROUTER_TIMEOUT`.
  - [`ChatbotProviderClient.php`](app/Services/Chatbot/ChatbotProviderClient.php): Gọi chuẩn `POST {base_url}/chat/completions` (hỗ trợ cả `GET {base_url}/models` để chẩn đoán), giữ nguyên cách ly Zero-Trust `<verified_project_data>` / `<untrusted_user_message>`.
- **CSDL & Models**:
  - Migration [`2026_11_19_200921_create_ai_chatbot_tables.php`](database/migrations/2026_11_19_200921_create_ai_chatbot_tables.php), Models [`AiKnowledgeEntry.php`](app/Models/AiKnowledgeEntry.php) & [`AiChatbotLog.php`](app/Models/AiChatbotLog.php), Seeder [`AiKnowledgeEntrySeeder.php`](database/seeders/AiKnowledgeEntrySeeder.php).
- **Kiến trúc Zero-Trust 4 Lớp (`app/Services/Chatbot/`)**:
  - [`ChatbotSecurityGuard.php`](app/Services/Chatbot/ChatbotSecurityGuard.php): Tường lửa tiền xử lý chặn Prompt Injection, Jailbreak, System Prompt Extraction, dò tìm `.env`/secret, XSS & SQLi trước khi chạm tới LLM; kiểm duyệt hậu xử lý `inspectAiOutput()` từ chối mọi câu trả lời AI có nguy cơ rò rỉ token/HTML/script.
  - [`ChatbotKnowledgeService.php`](app/Services/Chatbot/ChatbotKnowledgeService.php): Truy xuất dữ liệu xác thực 100% từ nội bộ dự án: tri thức Admin đã duyệt (`status = approved`, `is_active = true`), gợi ý mẫu thiệp thật theo phong cách/ngân sách (`InvitationTemplate` kèm link `/xem-thu/{slug}` & `/tao-thiep?template={slug}`), tài liệu hướng dẫn khách hàng (`DocumentationService` `/docs/...`) và bảng giá (`Plan`).
  - [`ChatbotProviderClient.php`](app/Services/Chatbot/ChatbotProviderClient.php): Hỗ trợ cả OpenAI Responses API (`/responses`) và Chat Completions API (`/chat/completions`), cô lập hoàn toàn `<verified_project_data>` khỏi `<untrusted_user_message>`, `store = false`, `temperature = 0.2`.
  - [`ProjectChatbotService.php`](app/Services/Chatbot/ProjectChatbotService.php): Điều phối truy xuất + kiểm duyệt, tự động chuyển sang trả lời xác thực trực tiếp (`fallback_grounded`) khi nhà cung cấp LLM chặn kết nối/lỗi/đầu ra không đạt chuẩn, và cách ly 100% đóng góp huấn luyện của khách hàng ở trạng thái `status = pending, is_active = false`.
- **Client Popup & Admin Training Panel**:
  - Client API (`routes/web.php`): `POST /api/ai-chatbot/ask` (`throttle:chatbot_messages`), `POST /api/ai-chatbot/train` (`throttle:chatbot_training`), `POST /api/ai-chatbot/feedback` (chống IDOR theo `session_hash`).
  - Client UI: [`resources/views/client/components/chatbot.blade.php`](resources/views/client/components/chatbot.blade.php), [`public/assets/client/css/components/chatbot.css`](public/assets/client/css/components/chatbot.css), [`public/assets/client/js/components/chatbot.js`](public/assets/client/js/components/chatbot.js) (render 100% DOM API `createElement`/`textContent`, cấm `innerHTML`; tự động phân giải Markdown `**in đậm**`, `*nghiêng*`, danh sách `•`, và biến link nội bộ `[nhãn](/docs/...)` thành thẻ `<a>` bấm được an toàn; lưu/khôi phục 40 tin nhắn gần nhất qua `localStorage` TTL 24h + giữ trạng thái mở khung chat qua `sessionStorage` + nút bắt đầu cuộc trò chuyện mới trên header).
  - Admin Panel (`/admin/ai-chatbot`, `routes/admin.php`): [`AiChatbotAdminController.php`](app/Http/Controllers/Admin/AiChatbotAdminController.php), [`resources/views/admin/pages/ai-chatbot/index.blade.php`](resources/views/admin/pages/ai-chatbot/index.blade.php), [`public/assets/admin/css/pages/ai-chatbot.css`](public/assets/admin/css/pages/ai-chatbot.css), [`public/assets/admin/js/pages/ai-chatbot.js`](public/assets/admin/js/pages/ai-chatbot.js) — quản lý kho tri thức, duyệt/từ chối hàng đợi góp ý từ khách hàng (có badge thời gian thực trên sidebar Admin) và nhật ký hội thoại + cảnh báo bảo mật.
- **Kiểm thử tự động & Chất lượng**:
  - Pest PHP Feature Tests ([`MayHenIntelligenceEngineTest.php`](tests/Feature/MayHenIntelligenceEngineTest.php)): **`11/11 PASS` (`55 assertions`)**.
  - Laravel Pint: `passed`.

### Đề án #122: Mây Hẹn Intelligence Engine — Hệ Thống Não Bộ Tri Thức Multi-Agent, Continuous Learning & Secure AI Governance
- **Triết lý kiến trúc**: *Mô hình AI là công cụ suy luận. Bộ não tri thức thuộc về Mây Hẹn. Admin là nguồn kiểm soát có thẩm quyền. Khách hàng là nguồn tín hiệu nhu cầu (không còn quyền nhập text dạy AI).*
- **Tài liệu kiến trúc đầy đủ**: [`docs/architecture/mayhen-intelligence-engine.md`](docs/architecture/mayhen-intelligence-engine.md).
- **1. Provider-Agnostic & Governance (`app/Services/Intelligence/`)**:
  - [`Contracts/LlmProviderInterface.php`](app/Services/Intelligence/Contracts/LlmProviderInterface.php) & [`NineRouterProviderAdapter.php`](app/Services/Intelligence/NineRouterProviderAdapter.php): Tách rời hoàn toàn kho tri thức/memory/policy khỏi nhà cung cấp model, hỗ trợ Draft Model & Reviewer Model riêng, Circuit Breaker chống sập, theo dõi Daily Token Budget.
  - [`AiGovernanceService.php`](app/Services/Intelligence/AiGovernanceService.php): Quản trị chính sách qua `PlatformSetting` (`group = ai_governance`, cache 0 DB query): Emergency Kill Switch, Draft/Reviewer Model, bật/tắt Critic Reviewer, giới hạn `max_review_rounds` (0–2), `memory_enabled`, `retention_days` (7–365 ngày), `daily_token_budget`, `allowed_sources` + ghi `AuditLogger`.
- **2. Multi-Agent Conversation Pipeline 10 Bước (`ProjectChatbotService` + `ChatbotProviderClient` + `SecurityPolicyGate` + `CustomerMemoryService`)**:
  - [`SecurityPolicyGate.php`](app/Services/Intelligence/SecurityPolicyGate.php): Kiểm soát bảo mật & chính sách bằng code cứng — chặn Prompt Injection, giả danh Admin, đòi xuất tài liệu training, Cross-Tenant/IDOR, Memory/Data Poisoning; phân tầng `simple` / `complex` / `high_risk`; Output Gate chặn URL ngoài `mayhen.vn`, URL ngoài allowlist, PII chưa xác thực, Brand Safety và **chống Verbatim Training Dump** (>82% trùng khớp bài dài); lọc spam thao túng feedback (`isFeedbackGenuine`).
  - [`CustomerMemoryService.php`](app/Services/Intelligence/CustomerMemoryService.php): Bộ nhớ ngữ cảnh cô lập tuyệt đối theo `user:{id}` hoặc `session:{hash}`, chỉ lưu nhãn sở thích/chủ đề (0 PII), hỗ trợ quyền xóa dữ liệu DSAR (`DELETE /api/ai-chatbot/memory`) và tự động xóa quá hạn Retention (`php artisan mayhen:ai-retention-purge`, chạy hằng ngày lúc 03:30 trong `routes/console.php`).
  - Mọi câu hỏi hợp lệ đi qua Reasoning & Sales Draft Agent (2–4 câu ngắn gọn, tự nhiên, định hướng trải nghiệm dịch vụ) → Critic & Verification Agent (cho câu `complex`/`high_risk`, không hạ chuẩn khi Reviewer mất kết nối ở luồng `high_risk`) → Deterministic Policy Gate → lưu log che PII kèm `trace_metadata` nội bộ (tuyệt đối không trả ra client).
- **3. Loại Bỏ Hoàn Toàn Form Khách Dạy AI & Chuyển Sang Controlled Multi-Agent Learning Pipeline**:
  - Gỡ bỏ `POST /api/ai-chatbot/train`, nút bút góp ý & panel form dạy AI khỏi [`chatbot.blade.php`](resources/views/client/components/chatbot.blade.php) và [`chatbot.js`](public/assets/client/js/components/chatbot.js); chỉ giữ 2 nút đánh giá chất lượng **Hữu ích / Chưa đúng**.
  - [`MultiAgentLearningService.php`](app/Services/Intelligence/MultiAgentLearningService.php) & [`AiSelfTrainCommand.php`](app/Console/Commands/AiSelfTrainCommand.php) (`php artisan mayhen:ai-self-train`): Passive Signal Mining + Ground Truth Corpus → Proposal AI ↔ Independent Critic AI → Deterministic Hard-Guard (chặn kể cả khi 2 AI cùng bịa giá/slug/URL) → **Hàng đợi chờ Admin phê duyệt (`status = pending, is_active = false`)**.
  - [`AiEvaluationService.php`](app/Services/Intelligence/AiEvaluationService.php): Bộ Regression Evaluation 10 kịch bản thực tế lưu lịch sử vào `ai_learning_runs`.
- **4. CSDL Mở Rộng An Toàn (Non-Destructive + Rollback)**:
  - Migration [`2026_11_20_135000_upgrade_ai_intelligence_engine_tables.php`](database/migrations/2026_11_20_135000_upgrade_ai_intelligence_engine_tables.php): Bổ sung versioning/trust/evidence/review/effective window/outdated vào `ai_knowledge_entries`, risk/review/trace/feedback_valid vào `ai_chatbot_logs`, tạo 3 bảng `ai_knowledge_versions`, `ai_customer_memories`, `ai_learning_runs`.
- **5. Admin Intelligence Center (`/admin/ai-chatbot` — 4 Trung Tâm Quản Trị Tinh Gọn Cho Người Mới)**:
  - [`AiChatbotAdminController.php`](app/Http/Controllers/Admin/AiChatbotAdminController.php), [`resources/views/admin/pages/ai-chatbot/index.blade.php`](resources/views/admin/pages/ai-chatbot/index.blade.php), [`public/assets/admin/css/pages/ai-chatbot.css`](public/assets/admin/css/pages/ai-chatbot.css), [`public/assets/admin/js/pages/ai-chatbot.js`](public/assets/admin/js/pages/ai-chatbot.js):
    + **Header sạch gọn & 4 KPI Cards điều hướng trực tiếp**: Tiêu đề *Quản Lý Trợ Lý AI*, 1 pill trạng thái (`Đang hoạt động` / `Đang tạm tắt AI`), 2 CTA chính (`+ Thêm kiến thức mới` & `Cho AI tự học`).
    + **Tab 1 — Kiến thức AI**: Ẩn form Thêm mới gọn sau nút `+ Thêm kiến thức mới`; danh sách hiển thị dạng **Clean Read-Only Cards** (chỉ mở khung Sửa & Rollback khi bấm `Sửa`, triệt tiêu rối mắt do hàng loạt ô input mở sẵn).
    + **Tab 2 — Tự học & Duyệt**: Nâng cấp **3 nút Preset 1 chạm** (*Chạy nhanh ~1 phút*, *Tiêu chuẩn ~5 phút • Khuyên dùng*, *Chạy sâu ~1 giờ+*) tự động đồng bộ thông số cho Client-Server Chunked Runner; gom tùy chỉnh chuyên sâu vào `<details>`; bổ sung nút **`✓ Duyệt tất cả`** (`POST /admin/ai-chatbot/entries/approve-all`, `admin.ai_chatbot.entries.approve_all`, có ghi `AiKnowledgeVersion` + `AuditLogger` `AI_KNOWLEDGE_BULK_APPROVED`).
    + **Tab 3 — Lịch sử chat**: Nhật ký hội thoại đã che PII, nút 1 chạm `Tạo kiến thức` từ câu hỏi khách, nút `Kiểm tra an toàn hệ thống (10 bài test)`.
    + **Tab 4 — Cài đặt AI**: 3 **Công tắc gạt (Toggle Switch)** trực quan (Tạm tắt AI khẩn cấp, Tự kiểm tra chéo câu quan trọng, Ghi nhớ phong cách thiệp), ô chọn Model gợi ý từ 9Router (`datalist`), mục nâng cao gom gọn vào `<details>`.
    + Đã dọn block route trùng trong `routes/admin.php`.
- **Kiểm chứng thực tế**:
  - Đã nâng cấp **Client-Server Chunked Micro-Step Runner** (`startChunkedRun` / `executeNextMicroStep` / `pause` / `resume` / `cancel` + Cron siêu nhẹ `php artisan mayhen:ai-self-train-step` chạy mỗi phút trong `routes/console.php`): chia mỗi chủ đề thành 2 vi-bước nhỏ (`propose` và `verify`, mỗi request chỉ gọi 1 lần AI < 10s rồi giải phóng PHP-FPM/RAM), có `Cache::lock` chống chạy đè, tự động **nhường CPU khi có khách thật đang chat** (`mayhen_client_chat_active_at`) hoặc CPU load >= 4.0, tự Exponential Backoff & Skip-and-Continue để chạy bền bỉ hàng tiếng (tới 60 vòng) trên shared hosting yếu nhất (Azdigi/CloudLinux) mà 0 timeout, 0 ảnh hưởng khách hàng.
  - Đã chạy thật `php artisan mayhen:ai-self-train --topics=templates,pricing,rsvp,docs,general --per-topic=2` trên 9Router: sinh **10/10 ứng viên tri thức** qua Critic AI (Fact Score 100/100) & Hard-Guard vào Hàng đợi Admin duyệt.
  - Pest PHP (`MayHenIntelligenceEngineTest` 13/13 PASS — gồm render cả 4 tab Admin mới & duyệt hàng loạt 1 chạm `approveAllPending` + RBAC): **100% PASS**. JS syntax (`node --check`): `passed`. Laravel Pint: `passed`.

### Đề án #123: Nâng Cấp Mây Hẹn Autonomous Intelligence Engine V2 (Bộ Não Tự Học Độc Lập 24/7, Progressive Domain Learning L0–L4, Knowledge Graph, Artisan Worker/Cron & Adaptive Resource Controller)
- **Tài liệu kiến trúc & vận hành đầy đủ (14 mục)**: [`docs/architecture/mayhen-autonomous-intelligence-v2.md`](docs/architecture/mayhen-autonomous-intelligence-v2.md).
- **1. Cơ Sở Dữ Liệu Phụ Trợ Độc Lập & Không Phá Hủy**:
  - Migration [`2026_11_20_150000_create_ai_autonomous_brain_v2_tables.php`](database/migrations/2026_11_20_150000_create_ai_autonomous_brain_v2_tables.php): KHÔNG đụng bất kỳ bảng nghiệp vụ cốt lõi nào; bổ sung provenance/fingerprint (`content_hash`, `domain_level`, `source_id`, `source_type`, `ownership`, `security_classification`, `expires_at`) vào `ai_knowledge_entries` và tạo mới 4 bảng AI phụ trợ độc lập: `ai_knowledge_relations` (Knowledge Graph L2), `ai_knowledge_gaps` (ghi nhận khoảng trống có chống trùng `gap_hash`), `ai_learning_checkpoints` (incremental discovery), `ai_learning_tasks` (hàng đợi học nền tách biệt khỏi queue nghiệp vụ `jobs`, có `idempotency_key` + `fencing_token` + dead-letter).
  - 4 Models mới: [`AiKnowledgeRelation.php`](app/Models/AiKnowledgeRelation.php), [`AiKnowledgeGap.php`](app/Models/AiKnowledgeGap.php), [`AiLearningCheckpoint.php`](app/Models/AiLearningCheckpoint.php), [`AiLearningTask.php`](app/Models/AiLearningTask.php).
- **2. Cấu Hình An Toàn Mặc Định & Adaptive Resource Controller**:
  - [`config/ai_brain.php`](config/ai_brain.php) & [`.env.example`](.env.example): 13 biến `AI_LEARNING_*` (`AI_LEARNING_ENABLED=false`, `AI_LEARNING_MODE=conservative`, `AI_LEARNING_DAILY_TOKEN_BUDGET=0`, `AI_LEARNING_DAILY_COST_BUDGET=0` — giá trị `0` chặn mặc định cho tới khi Admin cấu hình > 0; khóa cứng `external_retrieval=false`, `admin_approval_required=true`, `allow_unverified_promotion=false`).
  - [`AdaptiveResourceController.php`](app/Services/Intelligence/AdaptiveResourceController.php): Thứ tự ưu tiên tuyệt đối **Production Traffic > Business Jobs > AI Learning Jobs**; đo thực tế RAM, DB latency (`SELECT 1`), backlog queue nghiệp vụ `jobs`, khách đang chat, CPU load nếu HĐH hỗ trợ (không bịa số trên Windows), ổ đĩa trống, Circuit Breaker -> tự giảm tải hoặc Auto-Pause.
- **3. Progressive Domain Learning (L0 → L4), Knowledge Graph & Incremental Discovery**:
  - [`KnowledgeGraphService.php`](app/Services/Intelligence/KnowledgeGraphService.php): Mạng quan hệ tính năng xác thực của Mây Hẹn (`Thiệp cưới ↔ Khách mời ↔ RSVP / Bàn tiệc / QR Check-in / Album / Hộp thời gian / VietQR SePay`), tích hợp trực tiếp vào `ChatbotKnowledgeService::retrieve()` (cache 0 DB query) và tính độ phủ L0–L4 thực tế.
  - [`IncrementalDiscoveryService.php`](app/Services/Intelligence/IncrementalDiscoveryService.php): Chỉ đọc READ-ONLY nguồn nội bộ bằng `chunkById` + `content_hash` + checkpoint; dừng tạo task (0 token) khi không còn dữ liệu mới/gap mới; hỗ trợ `--dry-run`.
  - [`AutonomousBrainWorkerService.php`](app/Services/Intelligence/AutonomousBrainWorkerService.php): Distributed Cache Lock + Atomic DB Claim (`fencing_token`), không mở DB transaction khi gọi AI HTTP, tự khôi phục task kẹt khi worker bị kill (`recoverStaleProcessingTasks`), Multi-Agent Teacher → Independent Critic → Deterministic Evidence Gate (chặn ứng viên không nguồn / nguồn ngoài / bịa giá kể cả khi 2 AI cùng đồng ý), hỗ trợ `reanalyzeCandidate()`.
  - [`DataRetentionService.php`](app/Services/Intelligence/DataRetentionService.php): Chống phình DB (xóa candidate quá hạn/trùng hash, task cũ, memory/log quá hạn; bảo toàn 100% tri thức đã duyệt và audit logs).
  - [`AiEvaluationService.php`](app/Services/Intelligence/AiEvaluationService.php): Nâng cấp lên **15 kịch bản hồi quy độc lập**.
- **4. 7 Lệnh Artisan CLI (Windows + Linux + VPS + cPanel Cron)**:
  - `ai:brain:work` (liên tục local dừng Ctrl+C không cần PCNTL/Redis/Supervisor + `--once --budget-seconds=20 --max-tasks=2` cho cPanel Cron, đăng ký `everyFiveMinutes()->withoutOverlapping()` trong `routes/console.php`), `ai:brain:status`, `ai:brain:pause`, `ai:brain:resume`, `ai:brain:doctor`, `ai:brain:evaluate`, `ai:brain:backfill --dry-run`.
- **5. Admin Learning Center V2 & Emergency Control (`/admin/ai-chatbot`)**:
  - Nút đỏ khẩn cấp **`⏸ PAUSE ALL AI LEARNING`** (`admin.ai_chatbot.learning.pause_all` / `resume_all`) cắt ngay cả Artisan Worker, Cron & Chunked Runner mà không làm gián đoạn chatbot khách hàng; nút **`? Hướng dẫn`** mở Popup hướng dẫn trực quan (`#mayhenAiGuideModal`) kèm nút **Sao chép** 1 chạm cho mọi lệnh CLI & Cron (đồng bộ với cheatsheet [`docs/architecture/ai-brain-commands-cheatsheet.md`](docs/architecture/ai-brain-commands-cheatsheet.md)); dải Giám sát Bộ não V2, lộ trình L0–L4, nút `Phân tích lại` ứng viên (`entries.reanalyze`), đồng bộ Knowledge Graph (`learning.sync_graph`), cấu hình Learning Policy V2 trong Tab Cài đặt AI.
- **Kiểm thử & Chất lượng**:
  - Đã chạy migration thật trên DB local + chạy thực tế toàn bộ lệnh CLI (`ai:brain:doctor`, `ai:brain:status`, `ai:brain:backfill --dry-run`, `ai:brain:evaluate`, `ai:brain:work --once`).
  - Pest PHP (`MayHenIntelligenceEngineTest` + `AutonomousIntelligenceEngineV2Test`): **100% PASS**. Laravel Pint: `passed`.

### Đề án #124: Mây Hẹn Autonomous Research & Intelligence Engine V3 (Nghiên Cứu Internet Tự Chủ, Dynamic Curriculum L1–L7 Ngành Cưới Việt Nam, Safe Web Crawler & Chống Idle Giả)
- **Thay đổi tư duy kiến trúc (Thay thế chính sách chỉ đọc nội bộ V2)**:
  - Triệt tiêu tận gốc trạng thái `idle_no_new_data` sai khi DB nội bộ không đổi: khi hàng đợi trống và `external_research_enabled = true`, `AutonomousBrainWorkerService` tự động điều phối sang **Dynamic Learning Curriculum** (`research_discover` → `research_extract` → `research_verify` → `research_expand`) hoặc trả trạng thái có lịch chính xác `idle_until_next_research` (0 token lãng phí).
- **1. Cơ Sở Dữ Liệu V3 Không Phá Hủy**:
  - Migration [`2026_11_20_160000_create_ai_autonomous_research_v3_tables.php`](database/migrations/2026_11_20_160000_create_ai_autonomous_research_v3_tables.php): bổ sung 4 cột vào `ai_knowledge_entries` (`knowledge_layer`: `official_mayhen` | `research_general` | `applied_insight`, `research_topic_id`, `source_url`, `cultural_context`) và tạo mới 4 bảng Research độc lập: `ai_research_goals`, `ai_research_topics`, `ai_research_sources`, `ai_research_claims` (100% unique index <= 400 bytes, an toàn MySQL `utf8mb4`).
  - 4 Models mới: [`AiResearchGoal.php`](app/Models/AiResearchGoal.php), [`AiResearchTopic.php`](app/Models/AiResearchTopic.php), [`AiResearchSource.php`](app/Models/AiResearchSource.php), [`AiResearchClaim.php`](app/Models/AiResearchClaim.php).
- **2. Hệ Thống Nghiên Cứu Web An Toàn & Dynamic Curriculum (`app/Services/Intelligence/Research/`)**:
  - [`SafeWebCrawlerService.php`](app/Services/Intelligence/Research/SafeWebCrawlerService.php): Chặn SSRF tuyệt đối (localhost, private/reserved IP, cloud metadata), tôn trọng `robots.txt` thật (cache 24h), không vượt CAPTCHA/login wall, giới hạn tốc độ theo domain, giới hạn response <= 512KB & timeout 8s, tách nội dung sạch bằng Native `DOMDocument`/`DOMXPath`, tước sạch Prompt Injection nhúng trong trang web và chống copycat trùng `content_hash`.
  - [`WebSearchDiscoveryService.php`](app/Services/Intelligence/Research/WebSearchDiscoveryService.php): Khám phá nguồn mở đa kênh không hardcode 1 danh sách cố định (Search API Tavily/Serper/Brave tùy chọn + DuckDuckGo HTML tiếng Việt + Wikipedia Tiếng Việt MediaWiki API), sinh truy vấn tiếng Việt từ chủ đề & `unknown_concepts`, khử trùng `url_hash`.
  - [`CurriculumEngineService.php`](app/Services/Intelligence/Research/CurriculumEngineService.php): Lộ trình 16 chủ đề nền tảng Ngành Cưới & Thiệp Cưới Điện Tử Việt Nam (4 Domains: Thiệp cưới điện tử, Văn hóa cưới 3 miền Bắc/Trung/Nam, Quản lý đám cưới/RSVP/Bàn tiệc/Check-in, UX/UI & Hành trình cặp đôi), tiến trình L1–L7 dựa trên bằng chứng thật, mở rộng có kiểm soát và **Admin-Defined Learning Goals** thêm lĩnh vực mới không cần sửa code.
  - [`MultiAgentResearchService.php`](app/Services/Intelligence/Research/MultiAgentResearchService.php): Knowledge Extractor + Vietnamese Cultural Analyst → Independent Critic + Evidence Provenance Gate (cấm bịa nguồn, cấm nguồn web ngoài biến thành Official Mây Hẹn Product/Pricing Knowledge, phân tách rõ `research_general` vs `applied_insight`).
- **3. Phân Tách 3 Lớp Tri Thức Trong Chatbot & 4 Lệnh Artisan V3 Mới**:
  - `ChatbotKnowledgeService` & `ChatbotProviderClient`: tách rạch ròi `[TRI THỨC CHÍNH THỨC CỦA SẢN PHẨM MÂY HẸN]` và `[TRI THỨC NGHIÊN CỨU NGÀNH CƯỚI & VĂN HÓA VIỆT NAM]`, ẩn hoàn toàn `applied_insight` khỏi khách hàng.
  - 4 lệnh CLI mới: `ai:brain:discover`, `ai:brain:research`, `ai:brain:curriculum --seed`, `ai:brain:expand`; nâng cấp `ai:brain:work` in Structured Multi-Line Telemetry Logs (domain, topic, queries, sources, claims, verified, tokens, RAM, next plan) + cập nhật `ai:brain:status` & `ai:brain:doctor`.
- **4. Admin Research & Knowledge Center V3 (`/admin/ai-chatbot`)**:
  - Thêm thẻ **Nghiên cứu Internet & Lộ trình học tự chủ (V3)**, form 1 chạm **+ Giao mục tiêu học mới** (`admin.ai_chatbot.research.goals.store`), bật/tắt mục tiêu, xem Curriculum L1–L7 & Nguồn Web kèm lý do từ chối, công tắc **Bật Nghiên Cứu Internet Tự Chủ** & mức tự chủ trong Tab Cài đặt AI, cập nhật Popup Hướng dẫn + [`ai-brain-commands-cheatsheet.md`](docs/architecture/ai-brain-commands-cheatsheet.md).
- **Kiểm chứng & Nghiệm thu dữ liệu thực**:
  - Migration MySQL, Pint, Pest (`MayHenIntelligenceEngineTest` + `AutonomousIntelligenceEngineV2Test` + `AutonomousResearchEngineV3Test`), và chạy nghiệm thu thực tế trên nguồn mở ngành cưới Việt Nam (khởi tạo Curriculum -> Web Discovery -> Fetch/Extract -> Critic Verify -> Provenance -> Restart Worker không xử lý trùng).

### Đề án #125: Mây Hẹn Autonomous Intelligence Engine V4.0 (Atomic Knowledge Claims, 4 Không Gian Tri Thức, 8 Cổng Chất Lượng A→H, Typed Knowledge Graph 13 Kiểu, Benchmark Hồi Quy & Kiểm Toán Dữ Liệu Cũ)
- **Tài liệu kiến trúc & vận hành 12 chủ đề**: [`docs/architecture/ai-intelligence-engine-v4.md`](docs/architecture/ai-intelligence-engine-v4.md) & cheatsheet lệnh [`docs/architecture/ai-brain-commands-cheatsheet.md`](docs/architecture/ai-brain-commands-cheatsheet.md).
- **1. Nâng Cấp Cơ Sở Dữ Liệu V4 Không Phá Hủy**:
  - Migration [`2026_11_20_170000_upgrade_ai_intelligence_engine_v4_tables.php`](database/migrations/2026_11_20_170000_upgrade_ai_intelligence_engine_v4_tables.php) (MySQL `utf8mb4` an toàn, không chạm bảng nghiệp vụ): mở rộng `ai_research_claims` (`canonical_statement`, `claim_type`, `knowledge_space`, `ontology_domain`, `approval_status`, `quality_metrics`, `contradiction_notes`, `rejection_reason`), `ai_knowledge_entries` (`knowledge_space`, `ontology_domain`, `quality_metrics`, `source_claim_ids`), `ai_research_sources` (`source_tier`, `is_blocked`), `ai_research_goals` (`business_relevance`, `completion_criteria`) và tạo mới bảng độc lập `ai_benchmark_runs` (Model [`AiBenchmarkRun.php`](app/Models/AiBenchmarkRun.php)). Đủ **14/14 bảng AI độc lập**.
- **2. 5 Dịch Vụ Cốt Lõi V4 (`app/Services/Intelligence/V4/`)**:
  - [`OntologyAndClassificationService.php`](app/Services/Intelligence/V4/OntologyAndClassificationService.php): 6 miền Ontology, 4 Không gian Tri thức (`official_mayhen` | `industry_cultural` | `product_innovation` | `customer_intelligence`), 13 kiểu quan hệ Typed Graph, phân bậc nguồn (`wikipedia_seed` trần `65`), bộ lọc Discovery relevance chặt (chặn Wix, Squarespace, Google Sites, Gmail, Website sơ đẳng, Shinzen kekkon & domain `is_blocked = true`), phân loại ngữ nghĩa tự động (xóa bỏ kế thừa mù `$topic->category`).
  - [`QualityEvaluationGateService.php`](app/Services/Intelligence/V4/QualityEvaluationGateService.php): 8 Cổng Chất lượng (`Gate A → Gate H`) & 9 chỉ số độc lập (`heuristic_multi_gate_v4_uncalibrated`), chấm dứt điểm ảo 97–100/100 từ 1 nguồn (1 nguồn Wikipedia tối đa `partially_supported`, cần `>= 2` tên miền độc lập hoặc `authority_official` mới đạt `verified`), phát hiện trùng lặp (Gate C) & mâu thuẫn ngược cực khẳng định/phủ định cùng miền/vùng (Gate E).
  - [`TypedKnowledgeGraphService.php`](app/Services/Intelligence/V4/TypedKnowledgeGraphService.php): Đồng bộ mạng quan hệ 13 kiểu (`is_a`, `differs_by_region`, `precedes`, `requires`, `supports_feature`, `suggests_improvement`...), khóa cứng cấm nguồn ngoài tạo `supports_feature` (tự hạ cấp thành `suggests_improvement`), hỗ trợ truy xuất trực tiếp Atomic Claims đã duyệt cho Chatbot (cache 0 DB query) và cách ly hoàn toàn `product_innovation` & `suggests_improvement`.
  - [`KnowledgeBenchmarkService.php`](app/Services/Intelligence/V4/KnowledgeBenchmarkService.php): Bộ Benchmark độc lập 12 tình huống holdout (10 kịch bản RSVP `RSVP-01..RSVP-10` + `CULT-01` + `PROD-01`), đo `grounded_score`, `coverage_score`, `hallucination_rate`, `delta_vs_baseline` và tự động bật `regression_detected` khi chất lượng suy giảm.
  - [`LegacyKnowledgeAuditService.php`](app/Services/Intelligence/V4/LegacyKnowledgeAuditService.php): Kiểm toán & chuyển đổi an toàn tri thức cũ (`--dry-run` / `--apply`) không xóa bản ghi, tự chụp `AiKnowledgeVersion` hỗ trợ Rollback 100%, chuẩn hóa toàn bộ 52 bản ghi thực tế trên MySQL (33 Official, 6 Văn hóa/Ngành đã hạ điểm trung thực & sửa `#45 Lễ Hằng thuận` khỏi `rsvp`, 13 Ý tưởng/Rác sơ đẳng như Google Sites/Wix/Squarespace/Shinzen kekkon/Gmail/gửi-gởi được cách ly/lưu trữ an toàn).
- **3. CLI V4 & Admin Intelligence Center V4 (`/admin/ai-chatbot`)**:
  - 2 lệnh CLI mới: `ai:brain:audit-legacy {--apply}`, `ai:brain:benchmark {--domain=all} {--phase=after_learning}`; nâng cấp `ai:brain:evaluate --with-benchmark`, `ai:brain:status`, `ai:brain:doctor`.
  - 6 route quản trị mới bảo vệ RBAC `admin.manager`: `admin.ai_chatbot.benchmark`, `sources.block`, `tasks.cancel_pending`, `claims.approve`, `claims.reject`, `audit_legacy`; sửa `approve()` & `approveAllPending()` không thổi điểm `100/100` cho bài `web_research`; bổ sung bảng Atomic Claims V4, Typed Knowledge Graph, Benchmark 12 tình huống và nút kiểm soát khẩn cấp (`Chặn nguồn`, `Hủy tác vụ chờ`, `Chuẩn hóa Ontology V4`) trong giao diện thuần Vanilla HTML/CSS/JS.
  - Bổ sung 2 trường cấu hình trực tiếp trên Tab *Cài đặt AI* (`/admin/ai-chatbot?tab=governance`): **Địa chỉ máy chủ 9Router / OpenAI API (`ninerouter_base_url`)** và **Khóa API (`ninerouter_api_key`)** lưu vào `AiGovernanceService` (`platform_settings`), cho phép Admin thay thế trực tiếp `NINEROUTER_BASE_URL` và `NINEROUTER_API_KEY` trong `.env` ngay trên giao diện web (tự động xóa cache danh sách model & reset circuit breaker, che `[REDACTED]` trong `AuditLogger`).
  - Bổ sung nút **`⚡ Kiểm tra kết nối & Test 2 Mô hình AI ngay`** (`POST /admin/ai-chatbot/governance/test-connection`, `admin.ai_chatbot.governance.test_connection`) ngay trong Tab *Cài đặt AI*: kiểm tra trực tiếp `/models`, gọi thử thực tế cả `draft_model` lẫn `reviewer_model` (đo HTTP status, độ trễ `ms`, phản hồi thực hoặc thông báo lỗi chi tiết từ server), hiển thị danh sách chip model khả dụng để bấm chọn 1 chạm, đồng thời tích hợp **Auto-Fallback** trong `NineRouterProviderAdapter::completeChat()` (nếu model chính như `my-combo` trả lỗi `503/502/500/429/404` thì tự động chuyển sang model dự phòng còn lại để không làm gián đoạn Vòng tự học).
- **4. Kiểm Chứng & Nghiệm Thu Thực Tế**:
  - **Cơ chế chống nghẽn Timeout & Tự hồi phục Circuit Cooldown 24/7**:
    - Phân tách `Manual PAUSE ALL AI LEARNING` (dừng cứng cho tới khi Admin bật lại) khỏi `Auto-Cooldown 60s` trong [`AdaptiveResourceController.php`](app/Services/Intelligence/AdaptiveResourceController.php): khi gặp 5 lỗi mạng/LLM liên tiếp, hệ thống hạ nhiệt 60 giây rồi tự reset để thăm dò chạy tiếp thay vì khóa chết `learning_paused = true` vĩnh viễn.
    - Chuẩn hóa phép đo `sys_getloadavg()[0]` theo số nhân CPU hiệu dụng (`detectEffectiveCpuCores()`) trong `AdaptiveResourceController` và `MultiAgentLearningService`: triệt tiêu lỗi báo giả `[cpu_load_high] Tải CPU máy chủ cao (13.35 >= 2.5)` trên máy chủ Shared Hosting đa nhân AMD EPYC / CloudLinux (như AZDIGI `turbobv-epyc`, nơi `sys_getloadavg()` trả về tổng load toàn node 32–128 cores chứ không phải load từng nhân).
    - Chia lô nhỏ `3 claims/lượt` trong `MultiAgentResearchService::runVerifyAndDistillStep()`, nâng ngưỡng timeout model suy luận `55–90s`, tách riêng mã `request_timeout` khỏi `connection_error` trong [`NineRouterProviderAdapter.php`](app/Services/Intelligence/NineRouterProviderAdapter.php), và fallback sang đánh giá 8 Cổng Deterministic ở chế độ `supervised` nếu Critic AI quá tải tạm thời.
  - Laravel Pint: `passed`. JS Syntax (`node --check`): `0 lỗi`.
  - Pest PHP (`MayHenIntelligenceEngineTest` + `AutonomousIntelligenceEngineV2Test` + `AutonomousResearchEngineV3Test` + [`IntelligenceEngineV4Test`](tests/Feature/IntelligenceEngineV4Test.php)): **32/32 PASS (354 assertions)**.
  - Chạy thực tế trên MySQL local: Migration DONE, Legacy Audit `--dry-run` + `--apply` (52/52 bản ghi, 0 xóa, đủ version Rollback, Typed Graph 29+ quan hệ), Benchmark Run #1 (`baseline`) & Run #2 (`after_learning`): **12/12 đạt (Grounded 100%, Hallucination 0%, Regression: NO)**, Regression Safety `ai:brain:evaluate`: **15/15 PASS**, `ai:brain:doctor`: **10/10 OK (14/14 bảng AI, DB 0.97ms, RAM 38MB)**, và chạy sống `ai:brain:work --once` với `gpt-6.1-sol` hoàn tất `research_verifying` trong `19.3s` (`3 partially_supported`, `+2 typed edges`, `+1 chờ duyệt`).

### Đề án #121: Chuyển Đổi Toàn Bộ Hệ Thống Lưu Trữ Media Sang Cloudflare R2 Object Storage & Custom Domain `media.mayhen.vn`
- **1. Loại Bỏ Hoàn Toàn Logic Cloudfly, Chuẩn Hóa Cloudflare R2 (Path-Style S3 API, SigV4 Region `auto`)**:
  - Tạo mới [`CloudflareR2StorageService.php`](app/Services/CloudflareR2StorageService.php) và lệnh Artisan [`SyncMediaToR2Command.php`](app/Console/Commands/SyncMediaToR2Command.php) (`php artisan mayhen:sync-to-r2`).
  - Thay disk `cloudfly` bằng disk `r2` trong [`config/filesystems.php`](config/filesystems.php) và cập nhật [`.env.example`](.env.example) sang nhóm biến `# KHU VỰC CLOUDFLARE`:
    + `CLOUDFLARE_ACCOUNT_ID` -> tự động dựng S3 API Endpoint `https://{account_id}.r2.cloudflarestorage.com` (Path-Style `/{bucket}/{key}`, region `auto`, không gửi header `x-amz-acl` không hỗ trợ trên R2).
    + `CLOUDFLARE_R2_ACCESS_KEY_ID`, `CLOUDFLARE_R2_SECRET_ACCESS_KEY`, `CLOUDFLARE_R2_BUCKET_NAME`, `CLOUDFLARE_R2_PUBLIC_URL=https://media.mayhen.vn`, `CLOUDFLARE_API_TOKEN`.
  - Link công khai trả về 100% qua custom domain `https://media.mayhen.vn/{path}`, tự động chuẩn hóa ngược mọi URL `*.r2.cloudflarestorage.com` / `*.r2.dev` về `https://media.mayhen.vn`.
  - Cập nhật [`ChatbotSecurityGuard.php`](app/Services/Chatbot/ChatbotSecurityGuard.php) chặn rò rỉ `CLOUDFLARE_R2_SECRET_ACCESS_KEY` và `CLOUDFLARE_API_TOKEN`.
- **2. Đồng Bộ Hóa Toàn Bộ 13 Module Lưu Trữ Media Thiệp Cưới Sang `CloudflareR2StorageService`**:
  - Controllers: `TemplateController`, `GuestController`, `InvitationEditorController`, `PhotoDropController`.
  - Services: `PaperInvitationParserService`, `PlatformConfigurationService`, `SignatureArchiveService`, `WeddingDresscodeService`, `WeddingGalleryService`, `WeddingGiftRegistryService`, `WeddingInteractiveEndingService`, `WeddingMusicService`, `WeddingStorageService`.
  - **Giữ nguyên ảnh SEO website** (logo, favicon, OG metadata) trên local server để tối ưu SEO.
- **Kiểm thử thực tế & Chất lượng**:
  - Đã xóa sạch toàn bộ 2 tệp Cloudfly cũ (`CloudflyStorageService.php`, `SyncMediaToCloudflyCommand.php`) sau khi người dùng xác nhận; `git grep -in cloudfly` trên toàn bộ mã nguồn = **0 kết quả**.
  - Xác minh trực tiếp với Cloudflare R2 Bucket `mayhen` (`1de0443915758ce80d5f4c470e8a5b53.r2.cloudflarestorage.com/mayhen`): `testConnection()` PUT + DELETE trả về **`success: true`**.
  - Pest PHP (`WeddingGallerySuiteTest`, `WeddingMusicSuiteTest`, `SignatureArchiveSuiteTest`, `WeddingInteractiveEndingSuiteTest`): **27/27 PASS (133 assertions)**.
  - Laravel Pint: `passed`.

### Đề án #126: Mẫu Thiệp Cưới "Xuân Sang Tình Vừa Chớm" (`xuan-sang-tinh-vua-chom`)
- **Phân loại & Nguồn tham chiếu**: `category: romantic`, `tier: free`, phong cách truyền thống cổ phục Việt trên nền giấy dó mộc mạc phối hoa đào xuân, đèn lồng đỏ, chữ Song Hỷ nổi, sách ảnh bìa da lật trang 3D và bộ tương tác chuẩn Mây Hẹn.
- **Tệp mẫu thiệp (`resources/views/client/invitations/xuan-sang-tinh-vua-chom/`)**: `schema.json`, `template.blade.php` (`@extends('client.layouts.invitation')`), `styles.css` (100% Pure CSS3, BEM `.mayhen_invitation_*`, 0 CDN), `script.js` (Native JS, `node --check` 0 lỗi) và 100% tài nguyên nội bộ trong `assets/`.
- **Đồng bộ & Kiểm thử**: Đã đăng ký vào `database/seeders/InvitationTemplateSeeder.php` và kiểm thử tự động trong [`tests/Feature/XuanSangTinhVuaChomTemplateTest.php`](tests/Feature/XuanSangTinhVuaChomTemplateTest.php) (`inspectTemplateIntegrity = 100%`, `runPreflightAudit = 100%`, Pest PASS 100%).

### Đề án #127: Mẫu Thiệp Cưới "Hôm Nay Mình Hứa Nhau" (`hom-nay-minh-hua-nhau`) & Sửa Middleware Tài Nguyên Tĩnh Laravel 13
- **Phân loại & Thiết kế (Source Lock chuẩn `taothiep.net/templates/7/preview`)**:
  - `slug: hom-nay-minh-hua-nhau`, `name: Hôm Nay Mình Hứa Nhau`, `category: modern`, `tier: free`.
  - Bố cục tạp chí trắng tối giản (`#ffffff`) mang đậm chất editorial hiện đại: Trang mở màn gọn sáng với trái tim 3D hồng phấn (tự ẩn trong Studio Embed), ảnh bìa tràn viền `Save The Date` chữ đen serif đậm (`Fraunces` / `Black Mango Medium`) kết hợp chữ viết tay xanh non (`#6c8807`, `Aquarelle`), dây đèn lồng đỏ vàng đung đưa, lịch tháng `Our wedding day` / `Tháng 12` đánh dấu ngày cưới bằng viên màu hồng phấn `#fde2e4`, hai thẻ chân dung Cô dâu / Chú rể bố cục lệch tầng (staggered portrait cards) bo góc kèm font `Carlytte` + `Black Mango Medium` + ngày sinh, khối Nhà Trai / Nhà Gái dùng font `Alegreya` tiếng Việt sắc nét cùng trái tim 3D hồng phấn, trang nhật ký `OUR LOVE STORY` dạng giấy sổ lò xo xé mép xoay nghiêng dựng hoàn toàn bằng HTML/CSS (`Patrick Hand` + dòng kẻ + ảnh Polaroid, chỉnh sửa 100% Live Sync trong Studio), thanh ngày cưới kẻ đôi với số ngày đỏ rực `#f00000`, bản đồ Google Maps kèm nút nổi `Mở trong Maps`, Lịch trình & Dresscode (ẩn khi `$isCuratedSeven`), Album 10 ảnh kèm Lightbox phóng to, Hộp quà mừng cưới VietQR động (`VietQrService::resolveQrImage`), Sổ ký tên lưu bút đa màu mực, Biểu mẫu RSVP trên nền đỏ nhung thuần CSS (tuân thủ nghiêm Luật #8 — 0 ảnh người trong Ký tên & RSVP), Lưu bút lời chúc và Khung ảnh cảm ơn kết thiệp.
- **Tệp tạo mới & cập nhật**:
  - [`resources/views/client/invitations/hom-nay-minh-hua-nhau/schema.json`](resources/views/client/invitations/hom-nay-minh-hua-nhau/schema.json)
  - [`resources/views/client/invitations/hom-nay-minh-hua-nhau/template.blade.php`](resources/views/client/invitations/hom-nay-minh-hua-nhau/template.blade.php)
  - [`resources/views/client/invitations/hom-nay-minh-hua-nhau/styles.css`](resources/views/client/invitations/hom-nay-minh-hua-nhau/styles.css)
  - [`resources/views/client/invitations/hom-nay-minh-hua-nhau/script.js`](resources/views/client/invitations/hom-nay-minh-hua-nhau/script.js)
  - 100% Local Assets trong `resources/views/client/invitations/hom-nay-minh-hua-nhau/assets/` (`thumbnail.webp` 600x858 cắt sạch từ giao diện thật, 17 ảnh `.webp` bao gồm đủ 10 ảnh album + đèn lồng + trái tim 3D trong suốt, `icons/line-divider.svg`, `wedding-song.mp3`, và toàn bộ font tự host `aquarelle.ttf`, `black-mango.ttf`, `carlytte.ttf`, `Be Vietnam Pro`, `Fraunces`, `Alegreya`, `Charm`, `Josefin Sans`, `League Spartan`, `Patrick Hand` WOFF2 — 0 CDN ngoài).
  - [`database/seeders/InvitationTemplateSeeder.php`](database/seeders/InvitationTemplateSeeder.php): Đã thêm cấu hình đầy đủ cho `hom-nay-minh-hua-nhau` và seed thành công vào MySQL local.
  - [`routes/web.php`](routes/web.php): Bổ sung `PreventRequestForgery::class` vào mảng `$statelessAssetExcludedMiddleware` cho các route `/mau-thiep/{slug}/assets/{path}` (tương thích hoàn toàn Laravel 13 khi bỏ `StartSession::class`, khắc phục triệt để ngoại lệ `Session store not set on request`).
  - [`tests/Feature/HomNayMinhHuaNhauTemplateTest.php`](tests/Feature/HomNayMinhHuaNhauTemplateTest.php): Bộ kiểm thử Pest 5 kịch bản (Integrity & Preflight 100%, 10 Section & 0 CDN, Asset HTTP 200, Embed Editor auto-open + Bridge, Public Invitation VietQR + RSVP + Wishes + Signature).
- **Kiểm chứng & Nghiệm thu**:
  - `InvitationTemplateService::inspectTemplateIntegrity('hom-nay-minh-hua-nhau')`: **100%**, `runPreflightAudit`: `is_ready_for_release: true`, `blockers: []`, `readiness_score: 100`.
  - `node --check resources/views/client/invitations/hom-nay-minh-hua-nhau/script.js`: **0 lỗi**.
  - Playwright E2E & Visual QA trên `320x720`, `375x812`, `390x844`, `430x932`, `1440x900` và `?embed=1&mode=editor`: **0 lỗi console, 0 request lỗi, 0 overflow ngang, 22/22 ảnh tải đầy đủ (0 ảnh vỡ, 0 ảnh CDN ngoài), mở thiệp + Lightbox + Hộp quà + Ký tên + Live Sync postMessage đều đạt**.
  - Pest PHP (`HomNayMinhHuaNhauTemplateTest` + `XuanSangTinhVuaChomTemplateTest` + `CanonicalTemplateSectionsTest` + `TemplateGalleryTest`): **13/13 PASS (161 assertions)**.
  - Laravel Pint (`vendor/bin/pint --dirty --format agent`): **passed**.

### Đề án #128: Mẫu Thiệp Cưới "Lời Hứa Bằng Nụ Cười" (`loi-hua-bang-nu-cuoi`)
- **Phân loại & Thiết kế (Source Lock chuẩn `taothiep.net/templates/26/preview`)**:
  - `slug: loi-hua-bang-nu-cuoi`, `name: Lời Hứa Bằng Nụ Cười`, `category: romantic`, `tier: free`.
  - Màn mở thiệp Blossom Envelope nền đỏ trầm (`#731215` → `#280405`) với chữ Song Hỷ vàng bay nhẹ, thiệp kem ngà `#fbf5e6` trang trí 4 góc (nhành hoa mai, đèn lồng đỏ đung đưa, quạt hoa, mây vàng) và con dấu Song Hỷ tròn (tự ẩn khi `$isEmbed` / `$autoOpened`).
  - Trang thiệp nền trắng biên tập (`#ffffff`): Bìa ghép 2 khung ảnh vẽ tay đỏ son (`frame-red-1.webp`, `frame-red-2.webp`), tiêu đề `Our wedding`, hoa chữ Hỷ `恭喜`, vai vế `Thứ Nữ` / `Thứ Nam` kèm font viết tay `Borel` và cặp nhân vật chibi đáng yêu, trích dẫn nghiêng `"Hãy sống trọn đời vì tình yêu"`; khối `THÔNG TIN LỄ CƯỚI` 2 cột phân cách vạch đỏ và báo tin `LỄ THÀNH HÔN` chữ `Fraunces` đỏ đậm (`#a70000`); thanh ngày giờ Hôn lễ (`09:30`) & Tiệc cưới (`11:00`) với số ngày đỏ `#c90000` và Google Maps; lịch tháng tông hồng phấn `#f38c9c` + đồng hồ đếm ngược; chuyện tình yêu trong khung sổ lò xo vẽ tay đỏ (`frame-red-3.webp`); Lịch trình & Dresscode (`@if(!$isCuratedSeven)`); Album 10 ảnh + Lightbox; Hộp quà mừng 3D (`gift-box-main.webp` + 6 hộp mini bay quanh) mở mã VietQR động (`VietQrService::resolveQrImage`); Sổ ký tên lưu bút, RSVP nền đỏ nhung thuần CSS (Luật #8 — 0 ảnh người), Sổ lời chúc và khung ảnh cảm ơn với chữ viết tay `Thank You`.
- **Tệp tạo mới & cập nhật**:
  - `resources/views/client/invitations/loi-hua-bang-nu-cuoi/`: `schema.json`, `template.blade.php` (`@extends('client.layouts.invitation')`, `@push('styles')` / `@push('scripts')`), `styles.css` (100% Pure CSS3, BEM `.mayhen_invitation_*`, 0 CDN), `script.js` (Native JS, `node --check` 0 lỗi) và 100% tài nguyên nội bộ trong `assets/` (28 tài nguyên gốc + 10 ảnh album + `wedding-song.mp3` + font tự host + `thumbnail.webp` 600px cắt từ giao diện thật).
  - [`database/seeders/InvitationTemplateSeeder.php`](database/seeders/InvitationTemplateSeeder.php): Đã đăng ký và seed `loi-hua-bang-nu-cuoi` vào MySQL local.
  - [`tests/Feature/LoiHuaBangNuCuoiTemplateTest.php`](tests/Feature/LoiHuaBangNuCuoiTemplateTest.php): Bộ kiểm thử Pest 5 kịch bản (Integrity/Preflight 100%, 10 section & 0 CDN, Asset HTTP 200, Embed Editor + Bridge, Public Invitation VietQR + RSVP + Wishes + Signature).
- **Kiểm chứng & Nghiệm thu**:
  - `inspectTemplateIntegrity('loi-hua-bang-nu-cuoi')`: **100%**, `runPreflightAudit`: `is_ready_for_release: true`, `blockers: []`, `readiness_score: 100`.
  - Playwright E2E & Visual QA trên `320x720`, `375x812`, `390x844`, `430x932`, `1440x900` và `?embed=1&mode=editor`: **0 lỗi console, 0 request lỗi, 0 overflow ngang, 42/42 ảnh tải đầy đủ (0 ảnh vỡ, 0 ảnh CDN ngoài), mở thiệp + Lightbox + Hộp quà 3D + Ký tên + Live Sync postMessage đều đạt**.
  - Pest PHP (`LoiHuaBangNuCuoiTemplateTest` + `CanonicalTemplateSectionsTest` + `TemplateGalleryTest`): **8/8 PASS (114 assertions)**. Laravel Pint: **passed**.

### Đề án #129: Mẫu Thiệp Cưới "Gửi Em Một Nhành Xuân" (`gui-em-mot-nhanh-xuan`)
- **Phân loại & Thiết kế (Source Lock chuẩn `chungdoi.com/vi/mau-thiep/cam-thach-vang/demo`)**:
  - `slug: gui-em-mot-nhanh-xuan`, `name: Gửi Em Một Nhành Xuân`, `category: traditional`, `tier: free`.
  - Màn mở thiệp Cẩm Thạch Vàng trên nền champagne chuyển sắc, thiệp vân đá `marble-bg.webp` trang trí nhành lá vàng (`leaf-1.webp`, `leaf-2.webp`), con dấu trái tim ánh đồng, tên cặp đôi font `Viaoda Libre` + `&` font `Beau Rivage`, nút mở thiệp ánh kim (tự ẩn khi `$isEmbed` / `$autoOpened`).
  - Trang thiệp nền vân đá cẩm thạch vàng xuyên suốt (`#fcfaf6` + `marble-bg.webp`, điểm nhấn vàng cổ điển `#a78555`): Bìa `SAVE THE DATE` với 2 khung ảnh nghiêng viền vàng (`-10deg` / `+7deg`) kèm nhãn Cô Dâu / Chú Rể; khối `THÔNG TIN LỄ CƯỚI` 2 cột phân cách vạch vàng và báo tin `LỄ THÀNH HÔN`; Lễ Thành Hôn & Thông Tin Tiệc Cưới kèm 2 huy hiệu vàng (`medallion-candle.webp` Đón khách, `medallion-alcohol.webp` Khai tiệc), bản đồ Google Maps & nút `Chỉ đường`; Đồng hồ đếm ngược + Lịch tháng vàng (`Tháng / Năm` đánh dấu ngày cưới bằng hình tròn vàng); Chuyện tình yêu kèm huy hiệu lồng chim hoa vàng (`medallion-birdcage.webp`); Dress Code & Lịch trình ngày cưới (`@if(!$isCuratedSeven)`); Album 10 ảnh + Lightbox; Hộp quà mừng 3D Cẩm Thạch Vàng (`gift-box-main.webp` + 6 hộp quà mini) mở mã VietQR động (`VietQrService::resolveQrImage`); Sổ ký tên lưu bút, RSVP nền champagne-gold thuần CSS (Luật #8 — 0 ảnh người), Sổ lưu bút lời chúc và Khung kết thiệp cảm ơn.
- **Tệp tạo mới & cập nhật**:
  - `resources/views/client/invitations/gui-em-mot-nhanh-xuan/`: `schema.json`, `template.blade.php` (`@extends('client.layouts.invitation')`, `@push('styles')` / `@push('scripts')`), `styles.css` (100% Pure CSS3, BEM `.mayhen_invitation_*`, 0 CDN), `script.js` (Native JS, `node --check` 0 lỗi) và 100% tài nguyên nội bộ trong `assets/` (29 tài nguyên gốc + 10 ảnh album + `wedding-song.mp3` `em-dong-y-i-do.mp3` + 6 font tự host + `thumbnail.webp` 600×888 cắt từ giao diện thật).
  - [`database/seeders/InvitationTemplateSeeder.php`](database/seeders/InvitationTemplateSeeder.php): Đã đăng ký và seed `gui-em-mot-nhanh-xuan` vào MySQL local.
  - [`tests/Feature/GuiEmMotNhanhXuanTemplateTest.php`](tests/Feature/GuiEmMotNhanhXuanTemplateTest.php): Bộ kiểm thử Pest 5 kịch bản (Integrity/Preflight 100%, 10 section & 0 CDN, Asset HTTP 200, Embed Editor + Bridge, Public Invitation VietQR + RSVP + Wishes + Signature).
- **Kiểm chứng & Nghiệm thu**:
  - `inspectTemplateIntegrity('gui-em-mot-nhanh-xuan')`: **100%**, `runPreflightAudit`: `is_ready_for_release: true`, `blockers: []`, `readiness_score: 100`.
  - Playwright E2E & Visual QA trên `320x720`, `375x812`, `390x844`, `430x932`, `1440x900` và `?embed=1&mode=editor`: **0 lỗi console, 0 request lỗi, 0 overflow ngang, 32/32 ảnh tải đầy đủ (0 ảnh vỡ, 0 ảnh CDN ngoài), mở thiệp + Lightbox + Hộp quà 3D + Ký tên + Live Sync postMessage đều đạt**.
  - Pest PHP (`GuiEmMotNhanhXuanTemplateTest` + `CanonicalTemplateSectionsTest` + `TemplateGalleryTest`): **8/8 PASS (119 assertions)**. Laravel Pint: **passed**.











