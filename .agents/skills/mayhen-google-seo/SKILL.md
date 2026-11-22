---
name: mayhen-google-seo
description: Chuẩn SEO chính thức từ Google Search Central Docs (Search Essentials, Core Web Vitals LCP/INP/CLS, JSON-LD Structured Data 2025-2026, Canonical, XML Sitemap, Crawling/Indexing & Spam Policies) áp dụng trực tiếp cho dự án Mây Hẹn (mayhen.vn).
---

# mayhen-google-seo — Google Search Central Official Standards (2025–2026)

> Nguồn chuẩn: **Google Search Central Documentation** (`developers.google.com/search/docs` — Search Essentials, SEO Starter Guide, Core Web Vitals, Structured Data Search Gallery, Spam Policies).

## 1. Phạm vi áp dụng trong Mây Hẹn
- Routes & Controllers: `routes/web.php`, `app/Http/Controllers/Client/`, `app/Http/Controllers/SitemapController.php`
- SEO Services: `app/Services/Seo/`, `app/Http/Middleware/`
- Blade Views: `resources/views/client/layouts/master.blade.php`, `resources/views/client/layouts/invitation.blade.php`, `resources/views/client/pages/`
- Static files: `public/robots.txt`, `sitemap.xml`

---

## 2. Search Essentials & Ma trận Indexation của Mây Hẹn

Mọi trang trên `mayhen.vn` phải thuộc đúng 1 trong 2 nhóm rõ ràng:

| Nhóm URL | Robots Meta | Canonical | Có trong XML Sitemap? |
| :--- | :--- | :--- | :--- |
| **Trang công khai kiếm traffic**: `/`, `/mau-thiep`, `/dich-vu`, `/bang-gia`, `/cam-nang`, `/cam-nang/{slug}`, `/gioi-thieu`, `/lien-he`, `/cau-hoi-thuong-gap`, `/docs/*` | `index, follow` | Self-referencing `https://mayhen.vn/...` (chuẩn 1 bản duy nhất, bỏ query filter) | **CÓ** |
| **Trang riêng tư / biến thể / công cụ**: `/thiep/{slug}`, `/thiep/{slug}?guest=...`, `/xem-thu/{slug}`, `/tao-thiep`, `/tao-thiep-tu-dong`, `/tai-khoan/*`, `/thanh-toan/*`, `/admin/*` | `noindex, nofollow` | Không ép index; không đưa vào sitemap | **KHÔNG** |

### Quy tắc kỹ thuật bắt buộc (Google Search Essentials)
1. **Server-Side Rendered HTML (SSR)**: Toàn bộ tiêu đề (`<h1>`), nội dung chính, liên kết nội bộ (`<a href="...">`), meta tags và JSON-LD phải có sẵn trong HTML trả về từ Laravel Blade (không phụ thuộc JS client mới render nội dung chính).
2. **Không chặn tài nguyên render trong `robots.txt`**: Tuyệt đối không `Disallow: /assets/` hay chặn CSS/JS/ảnh cần thiết để Googlebot render trang.
3. **HTTP Status Codes chuẩn**:
   - Trang hợp lệ: `200 OK`
   - Đổi URL / gộp trang: `301 Moved Permanently` (dùng bảng SEO Redirects)
   - Trang không tồn tại / đã xóa: `404 Not Found` hoặc `410 Gone` (tuyệt đối không soft-404 trả `200 OK` cho trang rỗng).

---

## 3. Core Web Vitals (Ngưỡng Good — CrUX p75 Mobile & Desktop)

| Chỉ số | Đo lường | Ngưỡng chuẩn Google (Good) | Quy chuẩn thực thi tại Mây Hẹn |
| :--- | :--- | :--- | :--- |
| **LCP** (Largest Contentful Paint) | Tốc độ tải phần tử lớn nhất | **≤ 2.5 giây** | Ảnh hero dùng định dạng **WebP**, `fetchpriority="high"`, `loading="eager"`, preload font/ảnh hero; KHÔNG đặt `loading="lazy"` lên ảnh đầu trang. |
| **INP** (Interaction to Next Paint) | Độ phản hồi tương tác (thay FID từ 03/2024) | **≤ 200 ms** | Pure Vanilla JS ES Modules nhẹ, không thư viện nặng; chia nhỏ tác vụ dài, dùng `passive: true` cho scroll/touch listeners. |
| **CLS** (Cumulative Layout Shift) | Độ ổn định bố cục | **< 0.1** | Mọi thẻ `<img>` và container ảnh bắt buộc có `width`/`height` hoặc `aspect-ratio`; self-host font với `font-display: swap`; không chèn banner đẩy layout đột ngột phía trên nội dung. |

---

## 4. Structured Data (JSON-LD) — Cập nhật Google Search Gallery 2025–2026

### Định dạng & Nguyên tắc cốt lõi
- Bắt buộc dùng **JSON-LD** (`<script type="application/ld+json">`).
- **Tính trung thực & hiển thị (Visibility)**: 100% dữ liệu khai báo trong JSON-LD phải **trùng khớp nội dung người dùng nhìn thấy** trên trang. Không khai báo đánh giá giả (`AggregateRating` ảo) hoặc nội dung ẩn — vi phạm sẽ bị **Structured Data Manual Action** (mất toàn bộ Rich Results).
- **Quy tắc `@graph`**: Container `@graph` giữ `"@context": "https://schema.org"` ở cấp gốc và **không cần `@type`** ở container; các phần tử con bên trong kế thừa `@context` và bắt buộc có `@type` hợp lệ.

### Schema nên dùng tại Mây Hẹn (Google đang hỗ trợ mạnh)
- **`Organization` + `WebSite`**: Đặt tại trang chủ `/` (khai báo tên thương hiệu, logo URL tuyệt đối, `sameAs` mạng xã hội, thông tin liên hệ).
- **`BreadcrumbList`**: Đặt trên các trang danh mục/chi tiết (`/mau-thiep`, `/bang-gia`, `/cam-nang/{slug}`, `/docs/{slug}`) với URL tuyệt đối.
- **`Article` / `BlogPosting`**: Đặt trên bài viết `/cam-nang/{slug}` với `headline`, `image` (≥ 1200px), `datePublished`, `dateModified` (ISO-8601 `+07:00`), `author` (`Person`/`Organization`), `publisher`.
- **`Product` / `Offer`**: Đặt trên `/bang-gia` với giá VND khớp 100% con số hiển thị trên giao diện (`0`, `99000`, `199000`), không bịa số lượng đánh giá nếu chưa có dữ liệu thật.

### Schema đã bị Google thu hẹp / ngừng Rich Results (2025–2026)
- **`FAQPage`** & **`HowTo`**: Google đã loại bỏ/thu hẹp hiển thị Rich Results cho hầu hết website thương mại thông thường. Không phụ thuộc vào `FAQPage` để kỳ vọng rich snippet; nếu giữ lại chỉ phục vụ ngữ nghĩa, nội dung câu hỏi/trả lời vẫn phải hiển thị công khai đầy đủ trên trang.
- Không dùng các loại đã khai tử: `SpecialAnnouncement`, `ClaimReview`, `EstimatedSalary`, `CourseInfo`, `VehicleListing`.

---

## 5. Canonical & XML Sitemap Hygiene

### Canonical (`rel="canonical"`)
- Mọi trang indexable phải có `<link rel="canonical" href="...">` dạng URL tuyệt đối (`https://mayhen.vn/...`), không chứa `utm_*`, `?page=1`, `?sort=`, `?tier=`.
- Không tạo xung đột tín hiệu: không đặt `noindex` trên trang được trỏ làm canonical, không trỏ canonical sang URL `301`/`404`.

### XML Sitemap
- Chỉ liệt kê URL trả `200 OK`, là URL canonical và có `index, follow`.
- Tự động cập nhật `<lastmod>` theo `updated_at` thực tế của bài viết/trang (không gán `now()` giả cho mọi URL — Google chỉ tin `<lastmod>` khi phản ánh thay đổi thật).
- Tối đa 50.000 URL / 50MB mỗi tệp sitemap; khai báo đường dẫn tuyệt đối trong `public/robots.txt` (`Sitemap: https://mayhen.vn/sitemap.xml`).

---

## 6. Helpful Content, E-E-A-T & Spam Policies
- **People-first content**: Bài viết Cẩm Nang Cưới (`/cam-nang`) phải hữu ích cho cặp đôi Việt Nam (nghi thức lễ ăn hỏi, chọn ngày cưới, soạn lời mời, kế hoạch ngân sách), tránh nhồi nhét từ khóa (`keyword stuffing`) hoặc sinh hàng loạt bài rác SEO (`scaled content abuse`).
- **On-page cơ bản**:
  - Mỗi trang đúng **1 thẻ `<h1>`** duy nhất, phân cấp `<h2>` → `<h3>` logic.
  - `<title>` duy nhất mỗi trang (50–60 ký tự), chứa từ khóa chính tự nhiên + tên thương hiệu.
  - `<meta name="description">` duy nhất mỗi trang (140–160 ký tự), mô tả đúng giá trị trang.
  - Mọi ảnh nội dung có thuộc tính `alt` Tiếng Việt mô tả ngữ cảnh tự nhiên (không nhồi từ khóa).
  - Liên kết nội bộ dùng anchor text có nghĩa, độ sâu crawl các trang quan trọng **≤ 3 click** từ trang chủ.

---

## 7. Checklist nghiệm thu SEO trước khi release
1. Kiểm tra HTTP status (`200`/`301`/`404`), `robots` meta, `canonical` bằng Pest Feature Test.
2. Xác nhận JSON-LD parse hợp lệ (`json_decode` không lỗi), đủ `@context` + `@type`, khớp nội dung hiển thị.
3. Xác nhận `/thiep/{slug}`, `/xem-thu/{slug}`, `/tao-thiep` có `noindex, nofollow` và không lọt vào `sitemap.xml`.
4. Kiểm tra Core Web Vitals & Mobile-first (0px overflow, ảnh WebP có kích thước rõ ràng, không lỗi console/404).
