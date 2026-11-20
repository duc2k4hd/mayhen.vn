# Mây Hẹn Autonomous Intelligence Engine V2 — Kiến Trúc, Bảo Mật & Sổ Tay Vận Hành

> **Phiên bản:** 2.0 (Nâng cấp trực tiếp trên AI Brain của `mayhen.vn`)  
> **Nguyên tắc nền tảng:** *Mô hình AI là công cụ suy luận. Bộ não tri thức thuộc về Mây Hẹn. Admin là nguồn kiểm soát có thẩm quyền. Production Traffic > Business Jobs > AI Learning Jobs.*

---

## 1. Kiến Trúc Bộ Não Độc Lập (AI Brain Architecture)

```text
                        MÂY HẸN INTELLIGENCE ENGINE V2
                                      |
            +-------------------------+-------------------------+
            |                                                   |
      KNOWLEDGE CORE                                      AGENT ENGINE
      ├── ai_knowledge_entries (Official + Candidate)     ├── IncrementalDiscoveryService (Planner)
      ├── ai_knowledge_versions (Rollback History)        ├── AutonomousBrainWorkerService (Teacher/Critic/Verifier/Curator)
      ├── ai_knowledge_relations (Knowledge Graph L2)     ├── ProjectChatbotService (Real-Time Orchestrator)
      ├── ai_knowledge_gaps (Evidence Gaps)               ├── SecurityPolicyGate (Deterministic Code Gate)
      ├── ai_customer_memories (Scoped 0-PII Memory)      ├── AiEvaluationService (15-Case Regression Suite)
      └── ai_learning_checkpoints & ai_learning_tasks     └── AdaptiveResourceController & AiGovernanceService
```

Toàn bộ lệnh gọi LLM đi qua `App\Services\Intelligence\Contracts\LlmProviderInterface` (adapter hiện tại: `NineRouterProviderAdapter`). Khi chuyển đổi sang OpenAI, Claude, Gemini hoặc router khác, toàn bộ kho tri thức, Knowledge Graph, lịch sử phiên bản, bộ nhớ khách hàng, checkpoint và bộ kiểm thử của Mây Hẹn được giữ nguyên 100%.

---

## 2. Vòng Đời Tri Thức (Knowledge Lifecycle)

1. **Nguồn sự thật gốc nội bộ** (`plans`, `invitation_templates`, `DocumentationService` client docs, quan hệ hệ thống đã xác minh, log hợp lệ che PII).
2. **Candidate (Ứng viên)**: Sinh ra ở trạng thái `status = pending`, `is_active = false`, gắn `content_hash`, `domain_level` (0–4), `source_id`, `source_type = verified_internal`, `ownership = mayhen_internal`, `security_classification = public_customer_safe`, `evidence_refs`, `review_summary`, và `expires_at` (mặc định 30 ngày).
3. **Phê duyệt & Xuất bản (Admin Gate)**: Chỉ Admin có quyền (`admin.manager`) phê duyệt (`approve` hoặc `approveAllPending`) mới nâng trạng thái thành `approved`, `is_active = true`, `trust_score = 100` và ghi bản chụp vào `ai_knowledge_versions`.
4. **Hết hiệu lực / Lỗi thời**: Admin đánh dấu `is_outdated = true` hoặc hết khung `effective_from` / `effective_until` -> lập tức bị loại khỏi `AiKnowledgeEntry::liveApproved()`.
5. **Khôi phục (Rollback)**: Khôi phục 1 chạm về bất kỳ phiên bản nào trong `ai_knowledge_versions`, tạo số `version` kế tiếp để bảo toàn lịch sử kiểm toán.

---

## 3. Quy Trình Tự Học Từ Hẹp Đến Rộng (Progressive Domain Learning Workflow)

Hệ thống học theo 5 cấp độ có điều kiện mở khóa thực tế (định nghĩa trong `config/ai_brain.php`, tính toán trong `KnowledgeGraphService::getDomainCoverage()`):

- **Level 0 — Core Identity**: Bản sắc Mây Hẹn, bảng giá & gói dịch vụ chính thức (`plans`).
- **Level 1 — Product & Feature Deep Learning**: Chuyên sâu kho mẫu thiệp (`invitation_templates`) và toàn bộ tài liệu hướng dẫn khách hàng (`docs`, `rsvp`, khách mời, bàn tiệc, QR check-in, album ảnh, hộp thời gian).
- **Level 2 — Relationships & Workflows (Knowledge Graph)**: Xây dựng mạng quan hệ có hướng trong `ai_knowledge_relations` (`Thiệp cưới → Khách mời → RSVP / Bàn tiệc / QR Check-in`, `Gói dịch vụ → VietQR SePay`...) và nạp vào ngữ cảnh truy xuất của Chatbot.
- **Level 3 — Customer Intent & Business Intelligence**: Quét tăng dần (`chunkById`, theo `last_cursor`) nhật ký chat hợp lệ (loại `blocked_security`, lọc `feedback_valid = true`, che PII) và gom cụm khoảng trống trong `ai_knowledge_gaps` có cỡ mẫu tối thiểu (`min_sample_size`).
- **Level 4 — Advanced Domain Intelligence**: Chỉ mở khóa khi L0–L2 đạt ngưỡng kiểm chứng VÀ tồn tại `ai_knowledge_gaps` thực tế chưa được giải quyết.

**Nguyên tắc dừng khi hết dữ liệu mới:** Khi `content_hash` của mọi nguồn nội bộ không đổi và không có gap/log mới, `IncrementalDiscoveryService` tạo **0 task mới** — Worker nghỉ an toàn (`idle_no_new_data`, 0 token), không tự sinh dữ liệu giả vô hạn.

---

## 4. Mô Hình Tin Cậy Dữ Liệu (Data Trust Model & Internal-Only Policy)

- **Khóa cứng bất biến bảo mật trong `AiGovernanceService`**:
  - `external_retrieval = false` (cấm crawl ngoài Internet hoặc lấy nội dung bên ngoài làm nguồn sự thật).
  - `admin_approval_required = true` (mọi ứng viên AI đều chờ duyệt).
  - `allow_unverified_promotion = false` (cấm tự quảng bá nội dung chưa xác minh).
- **Hội thoại khách hàng là tín hiệu nhu cầu, không phải sự thật**: Không tồn tại endpoint public cho khách tự đẩy văn bản vào kho tri thức.
- **Evidence Gate bằng code**: `AutonomousBrainWorkerService::verifyCandidateEvidenceDeterministically()` từ chối mọi ứng viên không có `evidence_refs` nội bộ hợp lệ (`template:*`, `doc:*`, `plan:*`, `ground_truth:*`, `table:*`, `code:*`), có URL ngoài `mayhen.vn`, hoặc có mức giá không khớp bảng giá xác thực — **kể cả khi cả Teacher AI và Critic AI cùng đồng ý**.

---

## 5. Kiến Trúc Multi-Agent

- **Real-Time Pipeline (`ProjectChatbotService`)**: Input Security Gate → Intent & Scoped Memory → Authorized Retrieval (Approved Knowledge + Templates + Docs + Plans + Knowledge Graph L2) → Sales Draft Agent → Critic Reviewer Agent (bắt buộc cho `complex`/`high_risk`) → Deterministic Output Policy Gate (chặn URL lạ, PII chưa xác thực, Verbatim Training Dump) → PII-Masked Logging + tự động ghi nhận `AiKnowledgeGap` nếu thiếu bằng chứng.
- **Background Learning Pipeline (`AutonomousBrainWorkerService`)**: Planner (`IncrementalDiscoveryService`) → Teacher AI (chỉ đọc nguồn nội bộ) → Independent Critic AI (kiểm độ chính xác & mâu thuẫn) → Deterministic Verifier & Curator (`content_hash` dedupe + Evidence Gate + Hard-Guard) → Pending Admin Approval Queue → Evaluation Agent (`AiEvaluationService`).

---

## 6. Mô Hình Mối Đe Dọa & Bảo Mật (Security Threat Model)

Được kiểm soát đa lớp bằng code cứng (`ChatbotSecurityGuard` + `SecurityPolicyGate`):
- **Prompt Injection & Jailbreak**: Chặn cả Tiếng Việt/Tiếng Anh, chuẩn hóa ký tự zero-width trước khi quét.
- **Giả danh Admin / Developer & Chế độ Debug**: Chặn bằng mẫu nhận diện; quyền quản trị chỉ xác minh qua Laravel Auth & middleware `admin.manager`.
- **Trích xuất kho training / System Prompt / Bí mật `.env`**: Chặn cả ở Input Gate lẫn Output Gate (quét rò rỉ token/secret/internal tags).
- **Cross-Tenant / IDOR**: Chặn truy vấn dữ liệu tài khoản khác; bộ nhớ khách hàng (`ai_customer_memories`) cô lập cứng theo `user:{id}` hoặc `session:{hash}` và chỉ lưu nhãn enum an toàn (0 PII).
- **Feedback Manipulation & Data Poisoning**: `isFeedbackGenuine()` phát hiện và đánh dấu `feedback_valid = false` khi có >5 đánh giá `unhelpful` trong 10 phút từ cùng phiên/IP.
- **Anti-Verbatim Dump**: Chặn sao chép nguyên văn bài nội bộ dài (>82% trùng khớp), tự động rút gọn tự nhiên 2–4 câu.

---

## 7. Ngân Sách Tài Nguyên & Chế Độ Bảo Vệ Hosting (Resource Budget)

Các biến môi trường trong `.env` / `config/ai_brain.php` (mặc định **TẮT** và **ngân sách = 0** sau khi cài đặt mới):

```dotenv
AI_LEARNING_ENABLED=false
AI_LEARNING_MODE=conservative
AI_LEARNING_MAX_CONCURRENCY=1
AI_LEARNING_MAX_TASKS_PER_CYCLE=2
AI_LEARNING_CYCLE_BUDGET_SECONDS=20
AI_LEARNING_MAX_MEMORY_MB=128
AI_LEARNING_REQUEST_TIMEOUT_SECONDS=10
AI_LEARNING_DAILY_TOKEN_BUDGET=0
AI_LEARNING_DAILY_COST_BUDGET=0
AI_LEARNING_AUTO_PAUSE=true
AI_LEARNING_ADMIN_APPROVAL_REQUIRED=true
AI_LEARNING_EXTERNAL_RETRIEVAL=false
AI_LEARNING_ALLOW_UNVERIFIED_PROMOTION=false
```

- **Quy ước giá trị `0`**: `AI_LEARNING_DAILY_TOKEN_BUDGET=0` hoặc `AI_LEARNING_DAILY_COST_BUDGET=0` có nghĩa là **CHẶN gọi LLM học nền** cho tới khi Admin cấu hình mức ngân sách dương (`> 0`).
- **4 Chế độ vận hành**: `conservative` (Shared Hosting yếu: tối đa 2 task/20s, RAM 128MB, DB latency <= 120ms), `balanced` (VPS nhỏ), `performance` (Server riêng), `paused` (tạm dừng toàn bộ học nền, không ảnh hưởng chatbot).
- **Adaptive Throttling & Auto-Pause (`AdaptiveResourceController`)**:
  - Tự động nhường 100% tài nguyên nếu có khách thật vừa chat (`mayhen_client_chat_active_at` trong 4s) hoặc hàng đợi nghiệp vụ (`jobs`) tồn đọng.
  - Tự động giảm tốc/tạm dừng khi RAM tiến trình chạm ngưỡng, độ trễ DB cao, đĩa trống dưới 150MB hoặc lỗi liên tiếp chạm ngưỡng Circuit Breaker (5 lần).
  - Không bịa số liệu CPU nếu hệ điều hành (Windows/Shared Hosting) không hỗ trợ `sys_getloadavg()`.

---

## 8. Danh Sách Lệnh Artisan CLI

| Lệnh | Mục đích |
| :--- | :--- |
| `php artisan ai:brain:work` | Chạy vòng lặp nền liên tục trên máy local (Windows/Linux), dừng mượt bằng `Ctrl+C`. |
| `php artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2` | Chạy đúng 1 chu kỳ hữu hạn rồi thoát ngay — chuẩn dành cho cPanel Cron hosting yếu. |
| `php artisan ai:brain:status` | Xem trạng thái vận hành, ngân sách, RAM/DB latency, hàng đợi task, độ phủ L0–L4 và cảnh báo dung lượng. |
| `php artisan ai:brain:pause --reason="..."` | Kích hoạt **PAUSE ALL AI LEARNING** tức thì ở backend. |
| `php artisan ai:brain:resume` | Gỡ tạm dừng và đặt lại bộ đếm lỗi. |
| `php artisan ai:brain:doctor` | Kiểm tra sức khỏe DB, 9 bảng AI, Cache Lock, Queue Isolation, Security Invariants, Budget & Provider. |
| `php artisan ai:brain:evaluate` | Chạy bộ kiểm thử hồi quy độc lập 15 kịch bản bảo mật/chính sách/bằng chứng. |
| `php artisan ai:brain:backfill --dry-run` | Xem trước kế hoạch quét incremental nguồn nội bộ mà không ghi thay đổi vào DB. |
| `php artisan mayhen:ai-retention-purge` | Dọn dẹp định kỳ bộ nhớ, log, candidate hết hạn/trùng lặp và task đã hoàn tất cũ. |

---

## 9. Thiết Lập Chạy Local (Windows / Linux Development)

1. Chạy migration (an toàn, không đụng bảng nghiệp vụ):
   ```bash
   php artisan migrate
   ```
2. Kiểm tra sức khỏe toàn diện:
   ```bash
   php artisan ai:brain:doctor
   ```
3. Xem trước kế hoạch quét nội bộ (không ghi DB):
   ```bash
   php artisan ai:brain:backfill --dry-run
   ```
4. Khi sẵn sàng thử nghiệm học nền trên local, bật cờ & cấu hình ngân sách dương trong `.env` (hoặc trên `/admin/ai-chatbot?tab=governance`):
   ```dotenv
   AI_LEARNING_ENABLED=true
   AI_LEARNING_DAILY_TOKEN_BUDGET=100000
   AI_LEARNING_DAILY_COST_BUDGET=50000
   ```
5. Khởi chạy worker nền trên local (hỗ trợ Ctrl+C trên cả Windows lẫn Linux, không cần Supervisor/Redis/PCNTL):
   ```bash
   php artisan ai:brain:work --sleep=5
   ```

---

## 10. Triển Khai Cron Job Trên Shared Hosting (cPanel / AZDIGI)

Trên shared hosting tài nguyên thấp, **tuyệt đối không chạy daemon vô hạn**.

- **Cách 1 (Khuyên dùng — dùng chung Laravel Scheduler đã đăng ký trong `routes/console.php`)**:
  `routes/console.php` đã đăng ký sẵn `ai:brain:work --once --budget-seconds=20 --max-tasks=2` chạy mỗi 5 phút (`everyFiveMinutes()->withoutOverlapping()`). Chỉ cần giữ 1 dòng Cron duy nhất cho Laravel Scheduler:
  ```cron
  * * * * * /usr/local/bin/php /home/CPANEL_USER/mayhen.vn/artisan schedule:run >> /dev/null 2>&1
  ```
- **Cách 2 (Cron độc lập kèm `flock` nếu không dùng `schedule:run`)**:
  Bắt đầu bảo thủ mỗi 5 phút, sau khi theo dõi RAM/DB latency ổn định mới cân nhắc giảm xuống mỗi phút:
  ```cron
  */5 * * * * /usr/bin/flock -n /home/CPANEL_USER/ai-brain.lock /usr/local/bin/php /home/CPANEL_USER/mayhen.vn/artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2 >> /home/CPANEL_USER/mayhen.vn/storage/logs/ai-brain-cron.log 2>&1
  ```
  *Ghi chú:* Kể cả khi hosting không hỗ trợ `flock`, hệ thống vẫn có **Distributed Cache Lock** (`mayhen_ai_brain_worker_cycle_lock_v2`) kết hợp **Atomic Database Claim + Fencing Token** chống chạy trùng 100%.

---

## 11. Khôi Phục Sự Cố & Rollback (Recovery & Rollback)

- **Worker bị kill giữa chừng / Mất điện / Timeout**: Mọi task kẹt ở trạng thái `processing` quá `stale_lock_timeout_seconds` (120s) được `recoverStaleProcessingTasks()` tự động đưa về `queued` kèm exponential backoff (hoặc chuyển `dead_letter` nếu vượt quá 3 lần thử), không mất checkpoint.
- **Khôi phục phiên bản tri thức**: Vào `/admin/ai-chatbot?tab=approved` → bấm **Sửa** trên mục tri thức → mở mục **Khôi phục phiên bản cũ** → bấm **Khôi phục bản này**.
- **Rollback Migration V2** (chỉ thực hiện sau khi đã backup CSDL): Migration `2026_11_20_150000_create_ai_autonomous_brain_v2_tables` có đầy đủ `down()` gỡ sạch 4 bảng phụ trợ và 7 cột bổ sung mà không đụng bất kỳ bảng nghiệp vụ nào.

---

## 12. An Toàn Cơ Sở Dữ Liệu (Database Safety & Anti-Bloat)

- **Tách biệt hoàn toàn khỏi queue nghiệp vụ**: Tác vụ học AI nằm trong bảng riêng `ai_learning_tasks`, không dùng chung bảng `jobs` để không bao giờ cản trở gửi email, webhook thanh toán SePay hay RSVP.
- **Không khóa bảng khi gọi AI**: Atomic Claim chỉ giữ transaction `< 5ms`; toàn bộ HTTP call tới AI provider diễn ra hoàn toàn ngoài `DB::transaction()`.
- **Batch nhỏ (`chunkById`)**: Không sử dụng `Model::all()` trong discovery.
- **Chống phình DB (`DataRetentionService`)**: Tự động xóa candidate quá hạn (`expires_at`), gộp trùng `content_hash`, dọn task hoàn tất cũ quá 14 ngày, và giới hạn `max_pending_candidates = 250`.

---

## 13. Đánh Giá Chất Lượng Tự Động (Learning Quality Evaluation)

- Bộ kiểm thử độc lập `AiEvaluationService::runRegressionSuite()` gồm **15 kịch bản xác định** (bảo mật đầu vào/đầu ra, chống giả danh Admin/Developer, chống trích xuất training/secret, chống Cross-Tenant/IDOR, chống Verbatim Dump, bất biến Governance Internal-Only, PII Masking, Allowlist đường dẫn, định tuyến High-Risk và truy xuất bằng chứng).
- Bộ kiểm thử tích hợp Pest PHP:
  - `tests/Feature/MayHenIntelligenceEngineTest.php` (13 test nền tảng).
  - `tests/Feature/AutonomousIntelligenceEngineV2Test.php` (bộ test phủ 25 Quality Gates của kiến trúc V2).

---

## 14. Hướng Dẫn Vận Hành Dành Cho Admin (`/admin/ai-chatbot`)

1. **Dừng khẩn cấp học nền**: Bấm nút đỏ **`⏸ PAUSE ALL AI LEARNING`** ở góc trên phải — toàn bộ worker & cron học nền dừng tức thì, Chatbot khách hàng vẫn trả lời bình thường.
2. **Duyệt tri thức mới (Tab *Tự học & Duyệt*)**:
   - Xem dải **Giám sát Bộ não Tự học V2** (RAM, DB latency, tác vụ chờ/lỗi, cấp độ bao phủ L0–L4).
   - Xem bằng chứng (`evidence_refs`) và nhận xét kiểm định trên từng thẻ chờ duyệt.
   - Thao tác 1 chạm: **`✓ Duyệt`**, **`Sửa`**, **`Phân tích lại`** (yêu cầu AI Critic kiểm tra lại), **`Bỏ qua`**, hoặc **`✓ Duyệt tất cả`**.
3. **Cấu hình ngân sách & chế độ tài nguyên (Tab *Cài đặt AI*)**:
   - Bật công tắc **AI Tự Học Nền 24/7**, chọn chế độ **Bảo thủ (Shared Hosting yếu)**, nhập **Ngân sách Token & Chi phí > 0** trong phần Cài đặt nâng cao rồi bấm **Lưu cài đặt**.
   - Bấm **Dọn ngay** bất kỳ lúc nào để chạy `DataRetentionService::runCleanup()`.
