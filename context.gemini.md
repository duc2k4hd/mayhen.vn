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
- **Giao diện**: Đồng bộ 100% từ `docs/templates/` với 2 không gian biệt lập:
  - Client (`/docs`): Hướng dẫn cô dâu chú rể tạo thiệp, đổi ảnh Smart Focal Point, quản lý RSVP, kiểm soát quyền riêng tư.
  - Admin (`/admin/docs`): Sổ tay Vận hành Tiêu chuẩn (Admin SOP), đối soát SePay thủ công, phân quyền RBAC, cam kết kiểm toán `audit_logs`.
- **CSS & JS**: `public/assets/client/docs/` (CSS và JS thuần, nút theme switcher pill với min-width 125px và responsive mobile 36px circular, loại bỏ toàn bộ duplicate listeners gây đè innerHTML/co rút pill, icon SVG Mặt Trời/Mặt Trăng chuẩn nét, mũi tên SVG xoay 180 độ, auto cache-busting `?v={filemtime}`, TOC, phím tắt Ctrl+K).
- **UI/UX Quiet Luxury Refinement**:
  - Tối ưu tương phản nút bấm: Chữ trắng nổi bật trên nền nâu đỏ mận `#613B3E` (Dark Mode: chữ tối `#181517` trên nền hoa hồng `#D4898E`).
  - Font chữ thanh lịch: Tiêu đề H1/H2 sử dụng `Playfair Display` serif chuẩn typography tiếng Việt (triệt tiêu lỗi đè dấu do negative letter-spacing).
  - Thân bài đọc thoải mái: Cỡ chữ 15-15.5px, line-height 1.85, độ đậm 400-600 nhẹ nhàng thay vì 800 thô cứng.
  - Sidebar gọn gàng: Bỏ tiêu đề viết hoa trùng lặp (`strtoupper` ASCII bug), phân mục accordion êm ái.

### 5.2. Studio Tạo Thiệp Cưới V2 (`/tao-thiep`)
- Trình tạo thiệp tương tác trực quan thời gian thực (Editor V2).
- Hỗ trợ đổi tiêu điểm ảnh thông minh (Smart Focal Point), chọn nhạc nền lãng mạn, chọn bảng màu Dresscode, kiểm tra chất lượng thiệp với AI Quality Doctor.

### 5.3. Quản Lý Khách Mời & Check-in Lễ Tân
- Nhóm khách mời theo hộ gia đình, mã hóa đường dẫn cá nhân hóa, lời nhắn thiệp riêng.
- Sơ đồ bàn tiệc thông minh (Table Seating Chart), cockpit lễ tân quét mã QR điểm danh ngày cưới.

### 5.4. Đơn Hàng, Thanh Toán & SePay Webhook
- Thanh toán chuyển khoản tự động VietQR, khớp mã đơn hàng thời gian thực qua webhook SePay có xác thực chữ ký (Signature Verification & Idempotency).

### 5.5. Quản Trị Hệ Thống (Admin Portal)
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

- **Documentation Center Suite**: ngày 2026-11-15, `php artisan test tests/Feature/DocumentationCenterIntegrityTest.php --compact` đạt 18/18 test, 126 assertions; toàn bộ route `/docs`, `/admin/docs`, slug detail, và quyền RBAC đều được bảo đảm.
- **Sửa triệt để giao diện Docs UI & Theme Switcher**: 
  - Khắc phục lỗi co dẹp thành viên nang đứng của `.mayhen_docs_theme_switcher` bằng cách cố định `flex: 0 0 auto !important; flex-shrink: 0 !important;` và responsive hình tròn 36px trên di động.
  - Loại bỏ hoàn toàn xung đột duplicate event listeners giữa `layout.blade.php` và `docs-main.js` (Accordion `.mayhen_docs_detail_nav_group_toggle` và `.mayhen_docs_side_group`), giải quyết triệt để lỗi "bấm cha chưa xổ xuống con".
  - Bỏ toàn bộ `text-transform: uppercase` trên các tiêu đề tiếng Việt để dứt điểm lỗi font nham nhở `BẮT đầU VớI MÂY HẹN`.
  - Thay thế toàn bộ ký tự `⌄` bằng SVG Chevron xoay mượt 180 độ.
  - Cưỡng chế tương phản nút CTA (`#FFFFFF` trên `#613B3E`).
- **Tiến độ Hàng đợi Docs (SQLite `storage/app/mayhen-docs-queue.sqlite`)**:
  - `IMPLEMENTED`: 11 bài (`CLI-START-002`, `CLI-EDIT-001`, `CLI-EDIT-002`, `CLI-EDIT-003`, `CLI-EDIT-004`, `CLI-EDIT-005`, `CLI-EDIT-006`, `CLI-GUEST-004`, `ADM-USER-001`, `ADM-ORDR-002`, `ADM-COCK-002`).
  - `PENDING_SCREENSHOT`: 9 bài (`CLI-START-001`, `CLI-START-003`, `CLI-START-004`, `CLI-START-005`, `ADM-SOP-001` đến `ADM-SOP-005`).
  - `TODO`: 88 bài còn lại đang tiếp tục triển khai theo lộ trình.
