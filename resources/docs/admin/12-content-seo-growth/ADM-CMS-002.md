---
id: ADM-CMS-002
title: "Quản trị SEO, Sitemap XML từng nhóm & Trình quản lý Tài liệu (Docs Manager)"
category: "Nội dung, SEO & Tài liệu"
summary: "Hướng dẫn vận hành bảng điều khiển SEO & Sitemap XML (/admin/seo) để bật/tắt và xuất lại từng file sitemap chuyên biệt, cùng cách quản lý kho tài liệu Client/Admin trực tiếp tại /admin/docs-manager."
reader: "PER-ADM-01 (Super Admin & SEO Lead)"
order: 2
updated_at: "2026-11-25"
tags: [admin, seo, sitemap-xml, docs-manager, google-search-console, tai-lieu]
mapped_page: "ADM-SEO-PAGE"
mapped_route: "admin.settings.seo"
risk_level: "MEDIUM"
required_role: "manager"
---

# Quản trị SEO, Sitemap XML từng nhóm & Trình quản lý Tài liệu (Docs Manager)

> [!IMPORTANT]
> **Route màn hình:** `GET /admin/settings/seo` (SEO & Sitemap XML) và `GET /admin/docs-manager` (Trình quản lý tài liệu Markdown Client & Admin).  

![Giao diện Quản trị SEO & Sitemap XML trên Admin](/assets/client/docs/images/admin/12-content-seo-growth/adm-cms-002-step-1.png)
*Trang Quản trị SEO & Sitemap (/admin/settings/seo): Kiểm soát cấu hình Open Graph, thẻ meta chuẩn Google và xuất lại từng nhóm Sitemap XML.*

![Giao diện Trình Quản Lý Tài Liệu Docs Manager trên Admin](/assets/client/docs/images/admin/12-content-seo-growth/adm-cms-002-step-2.png)
*Trình Quản Lý Tài Liệu (/admin/docs-manager): Tra cứu, rà soát và cập nhật nội dung hướng dẫn Client & Admin tập trung.*

---

## 1. Quản trị SEO & Sitemap XML Từng Nhóm (`/admin/settings/seo`)

Hệ thống tách Sitemap thành Sitemap Index `/sitemap.xml` và **4 tệp Sitemap con độc lập**:
1. `/sitemap-pages.xml`: Trang chính, Kho Mẫu Thiệp Cưới, 10 danh mục Thiệp Sự Kiện và Dịch Vụ In.
2. `/sitemap-blog.xml`: Toàn bộ bài viết Cẩm Nang Cưới đã xuất bản.
3. `/sitemap-news.xml`: Toàn bộ bài Tin Tức Dự Án đã xuất bản.
4. `/sitemap-docs.xml`: Toàn bộ bài Hướng dẫn sử dụng Client Docs.

Tại `/admin/settings/seo`, quản trị viên có thể bật/tắt từng nhóm Sitemap, xem dung lượng/thời gian cập nhật gần nhất của từng file `.xml` trong `/public` và bấm nút **Xuất lại Sitemap** ngay sau khi thêm nhiều bài viết hoặc mẫu thiệp mới.

---

## 2. Trình Quản Lý Tài Liệu (`/admin/docs-manager`)

- Quản lý tập trung cả 2 không gian tài liệu: **Client Docs** (`resources/docs/client/`) và **Admin Docs** (`resources/docs/admin/`).
- Hệ thống `DocumentationService` tự động đọc YAML Frontmatter (tên bài, thứ tự, chuyên mục, thẻ tìm kiếm) và biên dịch Markdown kèm mục lục (TOC) + tìm kiếm tiếng Việt không dấu dưới 1ms.
- Sau khi chỉnh sửa bài viết trên production, xóa cache tài liệu (hoặc lưu qua Docs Manager) để bài viết cập nhật tức thì trên `/docs` và `/admin/docs`.
