---
id: ADM-CMS-001
title: "Quản lý Cẩm Nang Cưới, Tin Tức Dự Án (Email Broadcast) & Thông báo toàn site"
category: "Nội dung, SEO & Tài liệu"
summary: "Hướng dẫn biên tập bài viết Cẩm Nang Cưới (/admin/blog), xuất bản Tin Tức Dự Án kèm tự động gửi email thông báo hàng loạt (/admin/news) và vận hành thanh thông báo toàn trang (/admin/announcements)."
reader: "PER-ADM-03 (Biên tập nội dung & Marketing)"
order: 1
updated_at: "2026-11-25"
tags: [admin, blog, cam-nang, news, tin-tuc, email-broadcast, announcements]
mapped_page: "ADM-CMS-PAGE"
mapped_route: "admin.news.index"
risk_level: "MEDIUM"
required_role: "editor"
---

# Quản lý Cẩm Nang Cưới, Tin Tức Dự Án (Email Broadcast) & Thông báo toàn site

> [!IMPORTANT]
> **Route màn hình:** `/admin/blog` (Cẩm Nang Cưới), `/admin/news` (Tin Tức Dự Án & Email Broadcast), và `/admin/announcements` (Thông báo toàn trang).  

![Giao diện Quản lý Tin Tức Dự Án & Gửi Email Thông Báo](/assets/client/docs/images/admin/12-content-seo-growth/adm-cms-001-step-1.png)
*Quản lý Tin Tức Dự Án (/admin/news): Biên tập bản tin cập nhật tính năng mới và kích hoạt gửi email hàng loạt qua hàng đợi.*

![Giao diện Quản lý Cẩm Nang Cưới trên Admin](/assets/client/docs/images/admin/12-content-seo-growth/adm-cms-001-step-2.png)
*Quản lý Cẩm Nang Cưới (/admin/blog): Soạn thảo bài viết chuẩn SEO, quản lý chuyên mục và ảnh đại diện.*

---

## 1. Quản lý Tin Tức Dự Án & Gửi Email Thông Báo Hàng Loạt (`/admin/news`)

1. Truy cập `/admin/news`, bấm **[Thêm Tin Tức Mới]**.
2. Nhập tiêu đề, tóm tắt ngắn gọn (dùng làm nội dung bản tin email), nội dung chi tiết, ảnh đại diện và trạng thái (`draft` hoặc `published`).
3. **Bản nháp (`draft`)** tuyệt đối không hiển thị ở `/tin-tuc/{slug}` và không được đưa vào Sitemap.
4. Khi chọn xuất bản kèm gửi thông báo, hệ thống tự động đẩy `ProjectNewsSummaryMail` vào hàng đợi (`queue`) để gửi email thông báo tới tệp người dùng đăng ký mà không làm chậm thao tác trên trình duyệt Admin.

---

## 2. Quản lý Cẩm Nang Cưới (`/admin/blog`) & Thông Báo Toàn Site (`/admin/announcements`)

- **Cẩm Nang Cưới (`/admin/blog`)**: Dùng cho các bài viết kiến thức chuẩn bị cưới, phong tục, lời chúc và tối ưu từ khóa SEO dài hạn (`/cam-nang-cuoi/{slug}`). Luôn điền đầy đủ Meta Title, Meta Description và ảnh đại diện WebP nhẹ dưới 200KB.
- **Thông Báo Toàn Site (`/admin/announcements`)**: Dùng để hiển thị thanh thông báo ưu đãi mùa cưới, lịch bảo trì hoặc ra mắt bộ sưu tập mới trên đầu trang chủ; hỗ trợ đặt lịch bật/tắt tự động theo khung giờ.
