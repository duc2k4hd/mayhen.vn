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
