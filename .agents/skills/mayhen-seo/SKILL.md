---
name: mayhen-seo
description: Quy trình kiểm toán & triển khai Technical SEO, On-Page SEO, Image SEO, Open Graph (Zalo/Facebook/iMessage), JSON-LD và Sitemap thực chiến cho Mây Hẹn (mayhen.vn) — kết hợp thực hành kiểm toán từ claude-seo và chuẩn Google Search Central.
---

# mayhen-seo — Practical SEO Implementation & Audit Playbook

> Song song với [`mayhen-google-seo`](../mayhen-google-seo/SKILL.md) (bộ tiêu chuẩn gốc từ Google Search Central Docs). Skill này hướng dẫn **cách kiểm toán (audit) và triển khai trực tiếp trong mã nguồn Laravel + Blade + Vanilla CSS/JS của Mây Hẹn**.

## 1. Applicable Paths
- `resources/views/client/layouts/`, `resources/views/client/pages/`, `resources/views/client/components/`
- `app/Http/Controllers/Client/`, `app/Services/Seo/`, `routes/web.php`
- `public/robots.txt`, `tests/Feature/`

## 2. Quy trình Audit 6 Hạng Mục (Áp dụng trước khi triển khai hoặc khi rà soát SEO)

### A. Crawlability & Indexability Audit
- Kiểm tra `public/robots.txt`: cho phép crawl `/`, `/mau-thiep`, `/bang-gia`, `/cam-nang`, `/assets/`; chặn `/admin`, `/tai-khoan`, `/thanh-toan`, `/api/`; có dòng `Sitemap: https://mayhen.vn/sitemap.xml`.
- Kiểm tra thẻ `<meta name="robots">`:
  - Các trang public marketing/blog: **không** vô tình gắn `noindex`.
  - Mọi link thiệp riêng tư (`/thiep/{slug}`), xem thử (`/xem-thu/{slug}`), Studio (`/tao-thiep`): **bắt buộc** `noindex, nofollow`.
- Kiểm tra độ sâu liên kết nội bộ: mọi trang trọng yếu phải truy cập được trong **≤ 3 click** từ trang chủ.

### B. On-Page & Heading Audit
- Mỗi trang có `<title>` và `<meta name="description">` duy nhất, render trực tiếp từ server (Blade `@section('title')`, `@section('meta_description')`).
- Đúng 1 thẻ `<h1>` mỗi trang; không nhảy cóc heading (`h1` → `h4`).
- URL Tiếng Việt không dấu, chữ thường, nối bằng gạch ngang (`-`), ngắn gọn, không chứa tham số thừa trên bản canonical.

### C. Open Graph & Social Sharing (Zalo, Facebook, iMessage)
- Bắt buộc đủ: `og:site_name`, `og:type`, `og:title`, `og:description`, `og:url`, `og:image`, `og:image:width`, `og:image:height`, `twitter:card` (`summary_large_image`).
- Ảnh chia sẻ `og:image` chuẩn tỉ lệ **1200×630** (`1.91:1`), đường dẫn tuyệt đối (`https://...`), dung lượng gọn để Zalo/Facebook preview tải tức thì.
- Với link thiệp cá nhân hóa (`/thiep/{slug}`): vẫn giữ đầy đủ Open Graph đẹp cho cặp đôi khi gửi qua Zalo, đồng thời giữ `noindex, nofollow` để bảo vệ quyền riêng tư (Luật 91/2025/QH15).

### D. Structured Data (JSON-LD) Audit
- Quét mọi khối `<script type="application/ld+json">` trong Blade:
  - Phải là JSON hợp lệ, không ký tự lỗi hoặc escape sai (`JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`).
  - Mỗi entity có `@type` và `@context` (hoặc nằm trong `@graph` có `@context` ở gốc).
  - Giá trị trong Schema (`price`, `name`, `datePublished`, `url`) khớp 100% với nội dung hiển thị trên trang.
  - Không dùng `AggregateRating` cứng/ảo nếu chưa có đánh giá thật.

### E. Image SEO & Core Web Vitals Audit
- 100% ảnh nội dung dùng **WebP**, đặt tên file có nghĩa (ví dụ `banner-home-mobile.webp`, không dùng `IMG_1234.jpg`).
- Ảnh Hero/LCP: `loading="eager"` + `fetchpriority="high"`; ảnh dưới màn hình đầu: `loading="lazy" decoding="async"`.
- Mọi ảnh có kích thước xác định (`width`/`height` hoặc `aspect-ratio`) để giữ `CLS < 0.1`.
- Mọi ảnh có nội dung đều có `alt` Tiếng Việt tự nhiên; ảnh trang trí thuần túy dùng `alt=""` + `aria-hidden="true"`.

### F. XML Sitemap & Redirect Audit
- Mọi URL trong `sitemap.xml` phải trả về `200 OK`, không có `301`/`404`, không chứa URL có `noindex`.
- Khi đổi slug bài viết hoặc trang tĩnh, tạo bản ghi redirect `301` ngay để không mất lực SEO.

## 3. Kiểm chứng tự động bằng Pest (Definition of Done)
- Chạy test xác nhận:
  - Trang SEO trọng yếu (`/`, `/mau-thiep`, `/bang-gia`, `/cam-nang`) trả `200 OK`, có `<title>`, `<meta name="description">`, `<link rel="canonical">`, JSON-LD parse thành công và không bị `noindex`.
  - Trang riêng tư (`/thiep/{slug}`, `/xem-thu/{slug}`, `/tao-thiep`) trả `noindex, nofollow`.
  - Chạy `vendor/bin/pint --dirty --format agent` sau mọi chỉnh sửa PHP.
