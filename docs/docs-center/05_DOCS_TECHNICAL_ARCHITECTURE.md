# MÂY HẸN — TÀI LIỆU 05: KIẾN TRÚC KỸ THUẬT & BẢO MẬT HỆ THỐNG DOCS
## Docs Technical Architecture & Security — Định Tuyến, Lưu Trữ, Tìm Kiếm & Bảo Vệ Dữ Liệu

> **Mã tài liệu:** `DOCS-TECH-05`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Môi trường thực thi:** PHP 8.5.10 | Laravel 13.35.0 | SQLite/MySQL | Native Fast Zero-Framework  

---

## 1. MÔ HÌNH KIẾN TRÚC TỔNG THỂ (SYSTEM ARCHITECTURE OVERVIEW)

Trung tâm Tài liệu Mây Hẹn được thiết kế theo mô hình **Tách biệt Không gian & Dùng chung Hạ tầng** (Separated Spaces, Shared Infrastructure):

```
                                  [ NGƯỜI DÙNG / TRÌNH DUYỆT ]
                                                │
                     ┌──────────────────────────┴──────────────────────────┐
                     ▼                                                     ▼
           [ TRUNG TÂM CLIENT ]                                  [ TRUNG TÂM ADMIN ]
                 /docs/                                              /admin/docs/
                     │                                                     │
           [ Middleware: 'web' ]                        [ Middleware: 'web', 'admin.auth' ]
                     │                                                     │
                     ▼                                                     ▼
           ClientDocsController                                   AdminDocsController
                     │                                                     │
                     └──────────────────────────┬──────────────────────────┘
                                                ▼
                                    DocumentationService
                                                │
                     ┌──────────────────────────┼──────────────────────────┐
                     ▼                          ▼                          ▼
            [ File-Based Engine ]       [ Search Engine ]         [ Security & RBAC ]
             resources/docs/             In-memory Trie /          Chặn rò rỉ PII &
             ├── client/*.md             Unaccent Index            Admin endpoints ra
             └── admin/*.md              (Tiếng Việt không dấu)    công cộng
```

---

## 2. QUY HOẠCH ĐỊNH TUYẾN & TÊN ROUTE (ROUTING & URL BLUEPRINT)

Tuân thủ nguyên tắc đặt tên route có cấu trúc trong Laravel:

### 2.1. Phân hệ Client Docs (`routes/web.php`)
```php
// Documentation Center - Client Space
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [ClientDocsController::class, 'index'])->name('index');
    Route::get('/search', [ClientDocsController::class, 'search'])->name('search');
    Route::get('/{category}/{slug}', [ClientDocsController::class, 'show'])->name('show');
});
```
- `/docs`: Trang chủ danh mục tài liệu khách hàng.
- `/docs/search`: Endpoint API JSON phục vụ tìm kiếm nhanh trên client modal.
- `/docs/{category}/{slug}`: Trang chi tiết bài viết, tự động phân tích Slug và Category.

### 2.2. Phân hệ Admin Docs (`routes/admin.php`)
```php
// Documentation Center - Admin Space (Protected by admin.auth)
Route::middleware(['admin.auth'])->prefix('docs')->name('admin.docs.')->group(function () {
    Route::get('/', [AdminDocsController::class, 'index'])->name('index');
    Route::get('/search', [AdminDocsController::class, 'search'])->name('search');
    Route::get('/{category}/{slug}', [AdminDocsController::class, 'show'])->name('show');
});
```
- `/admin/docs`: Trang chủ quy trình vận hành SOP nội bộ dành cho nhân viên quản trị.
- `/admin/docs/search`: Endpoint tìm kiếm nội bộ, chỉ quét trong phạm vi tài liệu Admin.
- `/admin/docs/{category}/{slug}`: Chi tiết quy trình nghiệp vụ nội bộ (SOP).

---

## 3. CHIẾN LƯỢC LƯU TRỮ NỘI DUNG (CONTENT STORAGE STRATEGY)

Để đảm bảo hiệu năng cao nhất, dễ bảo trì bằng Git, không làm nặng database và hỗ trợ versioning:
- **Lựa chọn giải pháp:** **File-Based Markdown kết hợp YAML Frontmatter**.
- **Cấu trúc thư mục:**
  ```
  resources/docs/
  ├── client/
  │   ├── 01-getting-started/
  │   │   ├── CLI-START-001.md
  │   │   └── CLI-START-002.md
  │   ├── 02-studio-editor/
  │   │   ├── CLI-EDIT-001.md
  │   │   └── ...
  └── admin/
      ├── 01-sop-onboarding/
      │   ├── ADM-SOP-001.md
      │   └── ...
      ├── 02-dashboard-analytics/
      └── ...
  ```

### 3.1. Cấu trúc Chuẩn của một Tệp Markdown (`.md`)
```markdown
---
id: CLI-EDIT-002
title: Cách thay đổi ảnh bìa thiệp cưới
category: Studio Tạo Thiệp Cưới V2
summary: Hướng dẫn tải ảnh mới, cắt chỉnh tiêu điểm khuôn mặt và lưu an toàn.
reader: Cô dâu, chú rể
order: 2
updated_at: 2026-11-12
tags: [anh-bia, studio, thay-anh, smart-focal-point]
mapped_page: CLI-STUDIO-PAGE
mapped_route: studio.workspace
risk_level: LOW
---

# Cách thay đổi ảnh bìa thiệp cưới
[Nội dung bài viết theo đúng chuẩn Style Guide]
```

### 3.2. Trình phân giải Nội dung (Documentation Parser Service)
- Xây dựng Service `App\Services\DocumentationService`:
  - Đọc file Markdown bằng PHP native.
  - Tách phần YAML Frontmatter (metadata) và phần Markdown Body.
  - Chuyển đổi Markdown sang HTML an toàn, tự động lọc XSS (HTML Sanitization).
  - Tự động trích xuất các thẻ Heading `<h2>`, `<h3>` để tạo cây Table of Contents (TOC) dạng JSON.

---

## 4. BẢO MẬT & CHỐNG RÒ RỈ DỮ LIỆU ADMIN (SECURITY & DATA LEAKAGE PREVENTION)

Để đảm bảo tài liệu nội bộ, thông tin tài chính và quy trình nhạy cảm của Admin không bao giờ bị lộ ra ngoài:

1. **Cô lập Endpoint Tìm kiếm (Search Isolation):**
   - Bộ tìm kiếm `/docs/search` tuyệt đối chỉ được quét thư mục `resources/docs/client/`. Không bao giờ nạp các file trong `resources/docs/admin/`.
   - Ngăn chặn hoàn toàn tình huống tìm kiếm ở trang công cộng mà hiển thị gợi ý hoặc tiêu đề của quy trình nội bộ Admin.
2. **Kiểm soát Truy cập Nghiêm ngặt (Strict Access Control):**
   - Mọi URL bắt đầu bằng `/admin/docs/*` bắt buộc phải đi qua middleware `admin.auth`.
   - Khi người dùng chưa đăng nhập quản trị truy cập `/admin/docs/*`, hệ thống trả về HTTP 302 chuyển hướng về `/admin/login`, lưu lại intended URL an toàn.
   - Khi tài khoản nhân viên không đủ quyền đối với một bài SOP nhạy cảm (ví dụ: Staff thường cố đọc quy trình Hoàn tiền của Kế toán), hệ thống trả về HTTP 403 Forbidden trang nhã.
3. **Phòng chống Lộ diện trên Công cụ Tìm kiếm (SEO Security):**
   - Mọi trang thuộc `/admin/docs/*` bắt buộc có thẻ meta:
     `<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">`
   - File `robots.txt` khai báo `Disallow: /admin/` và `Disallow: /admin/docs/`.
   - File `sitemap.xml` chỉ chứa các URL thuộc `/docs/*` của Client, tuyệt đối không xuất hiện bất kỳ URL `/admin/*` nào.

---

## 5. CÔNG CỤ TÌM KIẾM BẢN ĐỊA HỖ TRỢ TIẾNG VIỆT (NATIVE SEARCH ENGINE)

Tuân thủ nguyên tắc không cài đặt thư viện nặng:
- **Cơ chế Indexing:**
  - `DocumentationService` xây dựng mảng chỉ mục tĩnh được tính toán trước (Pre-compiled Search Index) dạng JSON:
    `[ { id, title, title_unaccent, summary, tags, url, category } ]`
  - Hàm chuẩn hóa tiếng Việt không dấu (Unaccent String Normalizer) chuyển đổi tức thì:
    `"Xác nhận tham dự"` -> `"xac nhan tham du"`.
- **Cơ chế Tìm kiếm Phía Máy khách (Client-side Fast Search):**
  - File JS `public/assets/client/docs/js/docs-main.js` tải chỉ mục JSON siêu nhẹ (~45KB).
  - Khi người dùng gõ từ khóa vào Search Modal, thuật toán so khớp chuỗi (Sub-string & Levenshtein matching) lọc kết quả ngay lập tức trong vòng 2-5 mili-giây mà không cần gửi request liên tục về server, loại bỏ hiện tượng giật lag.

---

## 6. CHIẾN LƯỢC TỐI ƯU HIỆU NĂNG GỐC (NATIVE FAST PERFORMANCE & CACHING)

1. **Bộ nhớ đệm Nội dung (Content In-Memory Cache):**
   - Trong môi trường `production`, toàn bộ danh mục bài viết và metadata đã phân giải được lưu vào Laravel Cache với thời gian sống vĩnh viễn (hoặc xóa cache khi có release mới `php artisan cache:clear`).
   - Việc hiển thị một bài viết chỉ mất **1 lần đọc cache (dưới 1.5ms)**, hoàn toàn không truy vấn Database, đạt điểm Core Web Vitals tối ưu (LCP < 0.8s, CLS = 0).
2. **Tối ưu Tải Tài nguyên (Zero Render-Blocking):**
   - CSS giao diện docs được nạp tối ưu, không có framework nặng.
   - Script JS nạp dạng module không chặn luồng vẽ giao diện.
   - Hình ảnh minh họa sử dụng định dạng WebP nén sẵn, kích thước co giãn theo thuộc tính `loading="lazy"`.

