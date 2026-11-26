---
id: ADM-AI-003
title: "Autonomous Intelligence Engine V4: Giáo Án 18 Học Phần (144 Chủ Đề), Tự Học Liên Tục & Duyệt Atomic Claims"
category: "Vận hành AI & Chatbot"
summary: "Hướng dẫn quản trị Bộ não AI Tự học V4 (/admin/ai-knowledge): cơ chế học liên tục mỗi phút theo ngân sách token/tiền ngày, giáo án chuẩn 18 Học phần (144 Chủ đề) phủ kín 9 công cụ văn hóa & ngành cưới, kiểm định 8 cổng chống sai công thức và quy trình Admin duyệt tri thức."
reader: "PER-ADM-01 (Quản trị viên Hệ thống & Vận hành AI)"
order: 3
updated_at: "2026-11-26"
tags: [admin, ai-brain-v4, autonomous-learning, curriculum, atomic-claims, research-promote, cron-job]
mapped_page: "ADM-AI-KNOWLEDGE-PAGE"
mapped_route: "admin.ai-knowledge.index"
risk_level: "MEDIUM"
required_role: "admin"
---

# Autonomous Intelligence Engine V4 — Tự Học Liên Tục & Giáo Án 18 Học Phần

## 1. Nguyên Tắc Vận Hành: AI Tự Học — Admin Chỉ Duyệt Thành Phẩm
- Hệ thống chạy tự động qua cron `ai:brain:work --once --budget-seconds=40 --max-tasks=3` **mỗi phút 1 lần** (`withoutOverlapping(2)`), tự nối chuỗi đủ 5 bước: `discover → extract → verify (8 Gates) → promote → expand`.
- **Chế độ Continuous Learning**: Chừng nào ngân sách Token & Chi phí ngày (cấu hình tại `/admin/ai-knowledge`) vẫn còn, hệ thống tự xoay vòng học đều toàn bộ các chủ đề theo `last_researched_at` mà không ngủ chờ `next_research_at`. Khi chạm hạn mức ngày, worker tự dừng an toàn đến ngày hôm sau.
- Các **Atomic Claims** đạt chuẩn (`verified` hoặc `partially_supported`) tự động được bước `research_promote` đóng gói thành bài hỏi–đáp hoàn chỉnh đưa vào hàng chờ Admin duyệt (`status = pending`). Nếu bật chế độ `autonomous`, các tri thức tham khảo đa nguồn đạt chuẩn thấp rủi ro mới được phép tự xuất bản.

## 2. Giáo Án Chuẩn 18 Học Phần (144 Chủ Đề)
- **Học phần 1–8 (64 chủ đề)**: Thiệp cưới điện tử, Phong tục 3 miền, Tổ chức đám cưới, Ngân sách/Nhà cung cấp, Trang phục/Nhiếp ảnh, Quà mừng/VietQR, UX khách mời & Luật Hôn nhân Gia đình.
- **Học phần 9–18 (80 chủ đề mở rộng & nội bộ)**: Gia Phả, Kinh Dịch (64 quẻ), Tử Vi Đẩu Số Nam Phái, Thần Số Học Pythagoras, Tarot 78 lá RWS, Lịch Âm Dương 24 Tiết Khí (UTC+7 Hồ Ngọc Đức), Bát Tự (Tứ Trụ) & Nạp Âm, Thiệp Sự Kiện Gia Đình, Phong Thủy Bát Trạch và Vận hành Nội bộ / Đối tác Studio Mây Hẹn.
- **Khóa độ chính xác 100%**: Independent Critic bắt buộc bác bỏ (`reject`) mọi luận điểm sai công thức, sai bảng quy đổi hoặc trộn hệ thống học thuật.

## 3. Lệnh SSH Kiểm Tra & Điều Khiển Nhanh
- Kiểm tra sức khỏe toàn diện: `php artisan ai:brain:status`
- Chạy bồi thủ công nhiều chu kỳ: `php artisan ai:brain:work --max-cycles=15 --budget-seconds=35 --max-tasks=4 --sleep=1`
- Tạm dừng khẩn cấp toàn bộ học nền (không ảnh hưởng Chatbot khách hàng): bật nút **PAUSE ALL AI LEARNING** trong `/admin/ai-knowledge`.
