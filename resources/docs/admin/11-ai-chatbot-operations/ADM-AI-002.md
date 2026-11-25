---
id: ADM-AI-002
title: "Phòng Kiểm Thử Mô Phỏng Luồng AI 7 Bước (AI Pipeline Simulator) & Tự lưu Kịch bản Test"
category: "Trợ lý AI & Kiểm thử Pipeline"
summary: "Hướng dẫn sử dụng công cụ AI Pipeline Simulator ngay trong Admin (/admin/ai-chatbot?tab=simulator): chạy thật 7 tầng xử lý trong môi trường sandbox cô lập, đọc luồng truyền năng lượng PASS/FAIL, tự lưu kịch bản và chạy toàn bộ bộ test (Run Suite)."
reader: "PER-ADM-01 (Quản trị AI & QA)"
order: 2
updated_at: "2026-11-25"
tags: [admin, ai-simulator, pipeline-7-buoc, critic, bm25, kich-ban-test, sandbox]
mapped_page: "ADM-AI-SIMULATOR-PAGE"
mapped_route: "admin.ai_chatbot.index"
risk_level: "MEDIUM"
required_role: "manager"
---

# Phòng Kiểm Thử Mô Phỏng Luồng AI 7 Bước (AI Pipeline Simulator) & Tự lưu Kịch bản Test

> [!IMPORTANT]
> **Vị trí:** `/admin/ai-chatbot` → Tab **Mô phỏng Luồng AI 7 Bước (Simulator)**.  
> **Cô lập dữ liệu 100%:** Toàn bộ phiên kiểm thử chạy qua `AiChatPipelineTracerService` trong bộ nhớ sandbox riêng, **tuyệt đối không ghi bẩn vào `AiChatbotLog`, `AiCustomerMemory` hay `AiKnowledgeGap` của khách hàng thật**.  

![Giao diện Phòng Kiểm Thử Mô Phỏng Luồng AI 7 Bước trong Admin](/assets/client/docs/images/admin/11-ai-chatbot-operations/adm-ai-002-step-1.png)
*AI Pipeline Simulator: Chạy thật từng câu hỏi qua 7 tầng xử lý, hiển thị trực quan bước nào PASS/FAIL/Bỏ qua và điểm BM25/Critic thực tế.*

---

## 1. Ý nghĩa 7 bước xử lý thực tế trên bản đồ trực quan

1. **Bước 1 — Tiếp nhận đầu vào**: Chuẩn hóa chuỗi câu hỏi và phiên ngữ cảnh sandbox.
2. **Bước 2 — Bảo mật & Phân tầng (`SecurityPolicyGate`)**: Phát hiện Prompt Injection, mã canary đa ngôn ngữ (`MAYHEN_*`), ký tự CJK ép chỉ thị và **chặn tuyệt đối liên kết ngoài `mayhen.vn` ngay tại Bước 2 (0 token)**.
3. **Bước 3 — 3 Chỉ mục BM25 (`ChatbotKnowledgeService`)**: Chấm điểm thực tế trên kho tri thức đã duyệt, tài liệu Docs và danh mục mẫu thiệp.
4. **Bước 4 — AI Tổng hợp JSON (`ChatbotProviderClient::synthesize`)**: Gọi mô hình sinh câu trả lời nháp có cấu trúc chuẩn.
5. **Bước 5 — AI Phản biện Critic (`reviewDraftWithCritic`)**: Tự động kích hoạt với mọi câu hỏi thuộc nhóm rủi ro cao (`HIGH_RISK`), nghi vấn (`SUSPICIOUS`), bảng giá (`pricing`) hoặc khi kịch bản test yêu cầu; tối ưu bỏ qua với FAQ cơ bản đã khớp điểm BM25 tuyệt đối.
6. **Bước 6 — Hậu kiểm & Allowlist (`resolveDisplayCards`)**: Kiểm tra liên kết nội bộ hợp lệ và lọc thẻ hiển thị mẫu thiệp/hướng dẫn.
7. **Bước 7 — Trả UI & Chấm Test**: Đối chiếu câu trả lời với các tiêu chí `expected_mode`, `must_contain`, `must_not_contain` và kết luận PASS/FAIL.

---

## 2. Cách tạo Kịch bản Test tự lưu & Chạy toàn bộ Suite

1. Gõ bất kỳ câu hỏi thực tế nào vào khung chat thử nghiệm bên trái và bấm **[Chạy thật]**.
2. Bấm vào từng thẻ bước từ 1 đến 7 để soi dữ liệu thật (điểm BM25, cờ bảo mật, điểm Critic, lý do PASS/FAIL) hoặc bấm **Phát lại luồng** với tốc độ `0.7x / 1x / 2x`.
3. Bật công tắc **Tự lưu câu chat** hoặc bấm **+ Lưu câu này thành kịch bản** để đưa vào danh sách kiểm thử định kỳ (trình soạn thảo tự động lưu sau 800ms khi chỉnh sửa tiêu chí).
4. Bấm **[Chạy tất cả kịch bản (Run Suite)]** sau mỗi lần cập nhật tri thức hoặc prompt để đảm bảo 100% kịch bản đều đạt PASS trước khi vận hành.
