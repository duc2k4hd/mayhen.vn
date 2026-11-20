# Mây Hẹn Intelligence Engine — Kiến Trúc, Luồng Xử Lý & Vận Hành

> **Phiên bản:** 1.0 (Triển khai trên nền AI Brain & 9Router API của `mayhen.vn`)  
> **Nguyên tắc tối cao:** *Mô hình AI là công cụ suy luận. Bộ não tri thức thuộc về Mây Hẹn. Admin là nguồn kiểm soát có thẩm quyền. Khách hàng là nguồn tín hiệu nhu cầu, không phải người dạy hệ thống điều gì là sự thật.*

---

## 1. Tổng Quan Kiến Trúc (6 Thành Phần Độc Lập Nhà Cung Cấp)

```text
Mây Hẹn Intelligence Engine
├── Knowledge   : AiKnowledgeEntry (versioning, trust_score, evidence_refs, effective window, outdated)
│                 + AiKnowledgeVersion (snapshot & 1-click Rollback)
│                 + Ground Truth thực tế (InvitationTemplate active, Plan, Client Docs)
├── Memory      : CustomerMemoryService + ai_customer_memories (cô lập user:{id} / session:{hash}, 0 PII, TTL)
├── Reasoning   : LlmProviderInterface -> NineRouterProviderAdapter (Draft Model + Reviewer Model + Circuit Breaker)
├── Learning    : MultiAgentLearningService (Passive Signal Mining -> Proposal AI <-> Critic AI -> Hard-Guard -> Admin Queue)
├── Validation  : Critic & Verification Agent + Deterministic SecurityPolicyGate + AiEvaluationService (Regression Suite)
└── Governance  : AiGovernanceService (Kill Switch, Model Routing, Budget, Retention, Allowed Sources, AuditLogger)
```

Mọi model AI đều đi qua `App\Services\Intelligence\Contracts\LlmProviderInterface`. Khi chuyển đổi giữa **9Router, OpenAI, Claude, Gemini** hoặc provider khác, toàn bộ kho tri thức, lịch sử phiên bản, customer memory, chính sách quản trị và bộ đánh giá của Mây Hẹn đều được bảo toàn 100%.

---

## 2. Quy Trình Trả Lời Đa Tầng (10 Bước — `ProjectChatbotService`)

1. **Authentication / Rate Limit / Access Control**: Rate limiter `chatbot_messages` (25 req/phút).
2. **Input Risk Classification (`SecurityPolicyGate`)**: Chặn bằng code cứng trước khi chạm LLM đối với: Prompt Injection (Việt/Anh), giả danh Admin, đòi xuất toàn bộ tài liệu training, truy vấn Cross-Tenant/IDOR, đầu độc bộ nhớ/giá cước, XSS/SQLi/Secret fishing. Phân tầng độ phức tạp: `simple` / `complex` / `high_risk`.
3. **Intent & Scoped Memory**: Nhận diện nhu cầu + đọc `CustomerMemoryService::recall()` đúng scope hiện tại + tối đa 3 lượt hội thoại gần nhất cùng phiên (đã sanitize).
4. **Central Brain & Retrieval (`ChatbotKnowledgeService`)**: Chỉ truy xuất nguồn được Admin cho phép (`allowed_sources`), lọc tri thức `status = approved`, `is_active = true`, `is_outdated = false`, còn hiệu lực thời gian (`effective_from`/`effective_until`), đính kèm `evidence_refs`.
5. **Governance Gate**: Kiểm tra **Emergency Kill Switch** và **Daily Token Budget** trước khi gọi LLM.
6. **Reasoning & Sales Draft Agent (`ChatbotProviderClient::synthesize`)**: Diễn đạt ngắn gọn 2–4 câu tự nhiên, định hướng trải nghiệm dịch vụ (`/mau-thiep`, `/tao-thiep`, `/bang-gia`), tuyệt đối không chép nguyên văn bài training.
7. **Critic & Verification Agent (`reviewDraftWithCritic`)**: Bắt buộc chạy bằng `reviewer_model` cho yêu cầu `complex` / `high_risk`. Hỗ trợ tối đa `max_review_rounds` (0–2) vòng chỉnh sửa — cấm vòng lặp vô hạn. Nếu Reviewer không khả dụng ở luồng `high_risk`, hệ thống không hạ chuẩn kiểm duyệt mà chuyển về Safe Concise Fallback.
8. **Deterministic Security & Policy Output Gate (`inspectFinalOutput`)**: Thực thi bằng code (không phụ thuộc AI): chặn URL ngoài `mayhen.vn`, chặn đường dẫn ngoài allowlist, chặn PII/SĐT/Email không nằm trong nguồn xác thực, kiểm duyệt Brand Safety, và phát hiện **Verbatim Training Dump** (>82% trùng khớp bài nội bộ dài).
9. **Privacy-Controlled Logging**: Che giấu PII bằng `AiQualityAndCostObservatoryService::sanitizePiiForPreview()` trước khi lưu `ai_chatbot_logs` (kèm `trace_metadata` nội bộ, tuyệt đối không gửi ra trình duyệt) và `ai_usage_logs`.
10. **Safe Memory Update & Feedback**: Chỉ cập nhật nhãn sở thích/chủ đề (enum an toàn, 0 PII). Feedback **Hữu ích / Chưa đúng** đi qua `isFeedbackGenuine()` lọc spam thao túng (>5 lượt `unhelpful`/10 phút).

---

## 3. Controlled Multi-Agent Learning Pipeline (Giai Đoạn A → H)

- **Giai đoạn A–B (Passive Learning)**: `MultiAgentLearningService::getCustomerInsights()` phân tích câu hỏi phổ biến, khoảng trống `no_context_fallback`, câu đánh giá `unhelpful` hợp lệ (`feedback_valid = true`). Khách hàng **không còn** form tự nhập text dạy AI (`POST /api/ai-chatbot/train` đã bị gỡ bỏ).
- **Giai đoạn C (Proposal AI)**: Kết hợp vai Mô phỏng Khách hàng + Kiến trúc sư Tri thức Bán hàng, chỉ trích xuất từ Ground Truth Corpus thật của Mây Hẹn.
- **Giai đoạn D (Independent Critic AI + Hard-Guard)**: Reviewer AI phản biện chéo chấm `fact_score` (yêu cầu `>= 85`), sau đó **Hard-Guard bằng code** kiểm tra lần cuối: xác minh `template_slug` & `doc_url` tồn tại thật, chặn bịa mức giá không có trong bảng `plans` kể cả khi 2 AI cùng đồng ý, chống trùng tiêu đề.
- **Giai đoạn E (Admin Approval Queue)**: Toàn bộ ứng viên đạt chuẩn đều vào `ai_knowledge_entries` với `status = pending, is_active = false`. Chỉ Admin phê duyệt mới được xuất bản.
- **Giai đoạn F–H (Evaluation, Versioning & Rollback)**: Chạy `AiEvaluationService::runRegressionSuite()`, xuất bản tạo `AiKnowledgeVersion`, hỗ trợ 1 chạm **Rollback** hoặc **Đánh dấu lỗi thời** ngay trong `/admin/ai-chatbot`.

---

## 4. Vận Hành & Khôi Phục (Operations & Rollback)

- **Admin Intelligence Center**: Truy cập `/admin/ai-chatbot` với 4 trung tâm:
  1. *Kho Tri Thức & Phiên Bản* (CRUD, Hiệu lực, Outdated, Rollback).
  2. *AI Learning Center & Duyệt* (Customer Insights, chạy Multi-Agent Learning, phê duyệt ứng viên).
  3. *Quality Center & Nhật Ký* (Regression Benchmark, Learning Runs, Trace Logs).
  4. *AI Governance & Chính Sách* (Kill Switch khẩn cấp, chọn Draft/Reviewer Model từ 9Router, Budget, Retention, Allowed Sources).
- **Lệnh CLI & Lịch Tự Động**:
  - Chạy Multi-Agent Learning thủ công/CLI: `php artisan mayhen:ai-self-train --topics=all --per-topic=2`
  - **Client-Server Chunked Micro-Step Runner (Chống Timeout & Tối ưu Shared Hosting yếu)**:
    + Trên `/admin/ai-chatbot?tab=learning`, Admin khởi tạo phiên chạy chia nhỏ từ 1 đến 60 vòng (có thể chạy liên tục nhiều giờ).
    + Mỗi HTTP request chỉ thực thi **đúng 1 vi-bước** (`propose` hoặc `verify`, chỉ 1 lần gọi AI < 10s rồi giải phóng RAM/PHP-FPM ngay), có `Cache::lock` single-concurrency, tự động nghỉ nhịp (`cooldown` 2–8s), tự động **nhường CPU khi có khách thật đang chat** (`mayhen_client_chat_active_at`) hoặc tải CPU máy chủ cao, tự retry Exponential Backoff + Skip-and-Continue khi mạng chập chờn, hỗ trợ Pause/Resume/Cancel và tự khôi phục nếu F5.
    + Kể cả khi Admin đóng trình duyệt, Cron hosting mỗi phút (`php artisan mayhen:ai-self-train-step` trong `routes/console.php`) sẽ tự chạy nối tiếp đúng 1 vi-bước/phút rồi thoát ngay, đảm bảo 0 timeout và 0 ảnh hưởng tốc độ khách hàng trên hosting yếu nhất.
  - Dọn dẹp dữ liệu quá hạn Retention: `php artisan mayhen:ai-retention-purge` (đã đăng ký chạy hằng ngày lúc `03:30` trong `routes/console.php`).
- **Kế hoạch Rollback Migration**:
  - Migration `2026_11_20_135000_upgrade_ai_intelligence_engine_tables` chỉ bổ sung cột nullable/default và 3 bảng độc lập; có đầy đủ phương thức `down()` gỡ sạch mà không làm mất dữ liệu `ai_knowledge_entries` và `ai_chatbot_logs` trước đó.
