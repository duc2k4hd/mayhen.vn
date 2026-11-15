# MÂY HẸN (MAYHEN.VN) — CONTEXT & ARCHITECTURE GUIDE

> **Lưu ý quan trọng**: Tệp này là bản context tinh gọn, tập trung vào kiến trúc cốt lõi, quy tắc bất biến và trạng thái hệ thống hiện tại.  
> Toàn bộ lịch sử chi tiết các đề án cũ (#1 đến #83) đã được lưu trữ an toàn tại [`docs/archive/context.gemini.archive.md`](docs/archive/context.gemini.archive.md).

---

## 1. TỔNG QUAN HỆ THỐNG & NỀN TẢNG CÔNG NGHỆ

- **Tên dự án**: Mây Hẹn (`mayhen.vn`) — Nền tảng tạo thiệp cưới online cao cấp, mang phong cách *Quiet Luxury*, tinh tế, lãng mạn.
- **Backend**: Laravel 12 (PHP 8.5).
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
   - Tuyệt đối không tự commit hoặc push lên Git. Luôn cung cấp gợi ý thông điệp commit rõ ràng để người dùng tự quyết định.
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
- **CSS & JS**: `public/assets/client/docs/` (CSS và JS thuần, hỗ trợ Dark/Light Mode failsafe với cơ chế xử lý trực tiếp không phụ thuộc cache, auto cache-busting `?v={filemtime}`, chuyển đổi icon Mặt Trăng/Mặt Trời và lưu SafeStorage, TOC, phím tắt Ctrl+K).
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

- **Documentation Center Suite**: 16/16 tests PASSED 100% (94 assertions).
- **Toàn bộ hệ thống**: Mã nguồn tuân thủ nghiêm ngặt PSR-12 qua Laravel Pint, không có lỗi tiềm ẩn.
- **Lưu trữ tài liệu**: Đầy đủ 108 bài viết Markdown tại `resources/docs/client/` và `resources/docs/admin/`.
