---
id: ADM-CMS-003
title: "Trung Tâm Quản Trị 9 Công Cụ (/admin/tools-hub), AI Insights .JSONL, Banner MXH 1200×600 & Lưu Trữ 6 Tháng"
category: "Nội dung, SEO & Tăng trưởng"
summary: "Hướng dẫn quản trị hệ sinh thái 9 công cụ văn hóa trên Mây Hẹn: theo dõi KPI & dữ liệu chi tiết từng công cụ, xuất bộ dữ liệu AI-Ready .JSONL, quản lý 23 Banner chia sẻ mạng xã hội chuẩn 1200×600px và vận hành cron dọn dữ liệu quá hạn 6 tháng."
reader: "PER-ADM-01 (Quản trị viên Hệ thống & SEO/Growth)"
order: 3
updated_at: "2026-11-26"
tags: [admin, tools-hub, ai-insights, jsonl, social-banner, og-image, retention, cookie-consent]
mapped_page: "ADM-TOOLS-HUB-PAGE"
mapped_route: "admin.tools-hub.index"
risk_level: "MEDIUM"
required_role: "admin"
---

# Trung Tâm Quản Trị 9 Công Cụ, AI Insights & Banner Mạng Xã Hội

## 1. Trung Tâm Tổng Hợp & 9 Trang Quản Trị Độc Lập
- Truy cập **`/admin/tools-hub`** để xem KPI tổng quan toàn bộ 9 công cụ, nhật ký hành vi ẩn danh (`user_activity_logs`), bằng chứng đồng ý Cookie (`user_cookie_consents`) và kho lưu trữ 6 tháng (`tool_retention_archives`).
- Mỗi công cụ có **1 trang quản trị độc lập** trên Sidebar: `/admin/family-tree`, `/admin/iching`, `/admin/tuvi`, `/admin/numerology`, `/admin/tarot`, `/admin/calendar`, `/admin/bazi`, `/admin/marriage-compatibility`, `/admin/wedding-journey` — hỗ trợ xem chi tiết dữ liệu, thu hồi link chia sẻ, xóa kèm lưu tóm tắt phân tích và xuất CSV.

## 2. Kho Dữ Liệu AI-Ready Tách Riêng Từng Loại (`tool_insight_snapshots`)
- Tại tab **AI Insights** (`/admin/tools-hub?tab=insights`), hệ thống tự động thu thập nhu cầu người dùng tách riêng theo `tool_key`, `intent_category`, `region_or_topic` và lọc sạch 100% trường rác/nhạy cảm (`_token`, mật khẩu, base64).
- Bấm **Xuất Dataset `.JSONL`** theo từng công cụ (`/admin/tools-hub/export-ai-jsonl/{toolKey}`) để phục vụ phân tích hoặc huấn luyện AI chuyên sâu.

## 3. Banner Mạng Xã Hội Chuẩn `1200×600px` (23 Vị Trí SEO)
- Quản lý tập trung tại **`/admin/seo`** hoặc trực tiếp trên từng trang `/admin/{moduleSlug}` của 9 công cụ.
- Ảnh tải lên được tự động center-crop & tối ưu chuẩn `1200×600px` (WebP/JPG), xuất đầy đủ thẻ `og:image`, `og:image:width`, `og:image:height` và `twitter:image` (0 DB query trên Client nhờ `PlatformSetting` cache).

## 4. Cron Job Lưu Trữ & Dọn Dữ Liệu 6 Tháng
- Lệnh `php artisan mayhen:prune-expired-tools --months=6` chạy tự động hằng ngày lúc **03:45 sáng**: lưu tóm tắt phân tích phi định danh vào `tool_retention_archives`, sau đó xóa dữ liệu chi tiết và vô hiệu hóa liên kết quá hạn 6 tháng theo Luật 91/2025/QH15.
