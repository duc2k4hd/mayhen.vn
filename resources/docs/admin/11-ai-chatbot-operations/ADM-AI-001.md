---
id: ADM-AI-001
title: "Quản trị Trợ lý AI Mây Hẹn, Kho Tri Thức RAG/BM25 & Mở khóa thiết bị 24 giờ"
category: "Trợ lý AI & Kiểm thử Pipeline"
summary: "Hướng dẫn vận hành Trung tâm Trợ lý AI tại /admin/ai-chatbot: duyệt kho tri thức ground truth (RAG/BM25), kiểm soát chất lượng hội thoại, chặn liên kết ngoài mayhen.vn và mở khóa các thiết bị bị tạm cấm 24 giờ theo IP + Machine ID."
reader: "PER-ADM-01 (Quản trị AI & Vận hành)"
order: 1
updated_at: "2026-11-25"
tags: [admin, ai-chatbot, rag, bm25, abuse-guard, mo-khoa-24h, tri-thuc-ai]
mapped_page: "ADM-AI-CHATBOT-PAGE"
mapped_route: "admin.ai_chatbot.index"
risk_level: "HIGH"
required_role: "manager"
---

# Quản trị Trợ lý AI Mây Hẹn, Kho Tri Thức RAG/BM25 & Mở khóa thiết bị 24 giờ

> [!IMPORTANT]
> **Route màn hình:** `GET /admin/ai-chatbot` (`Admin\AiChatbotAdminController@index`).  
> **Dịch vụ lõi:** `ProjectChatbotService`, `ChatbotKnowledgeService`, `SecurityPolicyGate`, `AiChatbotAbuseGuardService`.  

![Trung tâm Quản trị Trợ lý AI Mây Hẹn](/assets/client/docs/images/admin/11-ai-chatbot-operations/adm-ai-001-step-1.png)
*Trung tâm Quản trị Trợ lý AI (/admin/ai-chatbot): Quản lý kho tri thức xác thực, tự học có kiểm duyệt, lịch sử hội thoại và bảng mở khóa thiết bị 24h.*

---

## 1. Cấu trúc các Tab vận hành trên `/admin/ai-chatbot`

1. **Tab Kiến thức đã duyệt (`knowledge`)**:
   - Quản lý các mục tri thức chuẩn hóa (`ai_knowledge_entries` với `status = approved`, `trust_score`, `priority`) về Bảng giá 4 gói, Dịch vụ In thiệp, Thiệp Sự Kiện Pro, Bàn tiệc, Check-in QR, Live Wall và Đối tác Studio.
   - Mọi thay đổi được chỉ mục hóa ngay vào động cơ tìm kiếm BM25 giúp AI trả lời đúng sự thật mà không bịa đặt.
2. **Tab Tự học & Duyệt tri thức (`learning`)**:
   - Xem các khoảng trống tri thức (`AiKnowledgeGap`) từ câu hỏi thực tế của khách để biên tập và duyệt đưa vào kho chính thức.
3. **Tab Lịch sử chat & Chất lượng (`quality`)**:
   - Theo dõi toàn bộ log hỏi đáp thực tế, phân loại ý định (`intent`), độ trễ, số token tiêu thụ và kiểm tra các câu bị cờ bảo mật.
4. **Tab Kiểm soát lạm dụng & Quản trị (`governance`)**:
   - Hiển thị danh sách thiết bị đang bị **tạm khóa chat 24 giờ (HTTP 429 — 0 token)** do vi phạm/thất bại **5 lần liên tiếp** ở các bước bảo mật (như spam tiêm chỉ thị, chèn link trang web ngoài `mayhen.vn`).

---

## 2. Quy trình Mở khóa Thiết bị bị tạm cấm 24 giờ

Cơ chế chống lạm dụng định danh bằng **2 yếu tố độc lập (IP mạng + Machine ID)** nhằm tránh chặn nhầm máy khác dùng chung Wi-Fi văn phòng/quán cà phê.

Khi khách hàng hoặc nhân sự kiểm thử bị khóa nhầm:
1. Vào `/admin/ai-chatbot?tab=governance`, tìm thẻ **Kiểm Soát Lạm Dụng & Thiết Bị Bị Khóa 24 Giờ**.
2. Kiểm tra thông tin IP, Machine ID rút gọn, số lần vi phạm (`5/5`) và thời gian còn lại.
3. Chọn 1 trong 3 cách mở khóa:
   - Bấm **[Mở khóa]** ngay trên dòng thiết bị cụ thể (`POST /admin/ai-chatbot/banned-devices/reset`).
   - Nhập thủ công cặp IP / Machine ID vào biểu mẫu mở khóa nhanh.
   - Bấm **[Mở khóa tất cả (Reset All)]** (`POST /admin/ai-chatbot/banned-devices/reset-all`) khi cần giải phóng toàn bộ sau đợt kiểm thử nội bộ.
