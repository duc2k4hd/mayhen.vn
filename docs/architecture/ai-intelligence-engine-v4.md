# Mây Hẹn Autonomous Intelligence Engine V4.0 — Kiến Trúc, Ontology, Kiểm Định 8 Cổng & Vận Hành

> Tài liệu chuẩn hóa toàn diện cho **Đề án #125 — Intelligence Engine V4.0**, mô tả chính xác 100% các thành phần đã triển khai thực tế trên kho mã nguồn `mayhen.vn` (nâng cấp trực tiếp trên nền AI Brain V1/V2/V3 hiện hữu, không tạo bộ não thứ hai, không chỉnh sửa bảng nghiệp vụ cốt lõi).

---

## 1. Bản Đồ 12 Chủ Đề Kiến Trúc & Tài Liệu Tham Chiếu

| # | Chủ đề tài liệu | Mã nguồn / Tài liệu thực tế |
| :---: | :--- | :--- |
| 1 | Kiến trúc tổng thể V4.0 | Mục 2 bên dưới (`AutonomousBrainWorkerService`, `V4/*`) |
| 2 | Mô hình Tri thức & 4 Không gian Tri thức | Mục 3 (`AiResearchClaim`, `AiKnowledgeEntry`, Migration V4) |
| 3 | Ontology & Taxonomy Ngành Cưới VN | Mục 4 (`app/Services/Intelligence/V4/OntologyAndClassificationService.php`) |
| 4 | Chiến lược Tìm kiếm & Phân cấp Nguồn | Mục 5 (`WebSearchDiscoveryService`, `SafeWebCrawlerService`) |
| 5 | 8 Cổng Chất lượng & 9 Chỉ số Đánh giá | Mục 6 (`app/Services/Intelligence/V4/QualityEvaluationGateService.php`) |
| 6 | Typed Relational Knowledge Graph (13 kiểu) | Mục 7 (`app/Services/Intelligence/V4/TypedKnowledgeGraphService.php`, `KnowledgeGraphService.php`) |
| 7 | Phối hợp Multi-Agent & Chưng cất có Kiểm soát | Mục 8 (`app/Services/Intelligence/Research/MultiAgentResearchService.php`) |
| 8 | Học chuyên sâu & Benchmark Trước/Sau (12 kịch bản) | Mục 9 (`app/Services/Intelligence/V4/KnowledgeBenchmarkService.php`, `AiBenchmarkRun`) |
| 9 | Bảo mật, Chống đầu độc & Cách ly Ý tưởng | Mục 10 (`SecurityPolicyGate`, `SafeWebCrawlerService`, `ChatbotKnowledgeService`) |
| 10 | Kiểm toán & Chuyển đổi Dữ liệu cũ An toàn | Mục 11 (`app/Services/Intelligence/V4/LegacyKnowledgeAuditService.php`, `ai:brain:audit-legacy`) |
| 11 | Vận hành Hosting yếu, CLI & Cron | [`ai-brain-commands-cheatsheet.md`](ai-brain-commands-cheatsheet.md) |
| 12 | Nghiệm thu Tự động & Giới hạn Thực tế | Mục 12 (`tests/Feature/IntelligenceEngineV4Test.php`) |

---

## 2. Những Vấn Đề Gốc Từ Dữ Liệu Thực Tế V3 Đã Được Xử Lý Triệt Để Ở V4.0

1. **Chuyển từ sinh FAQ hàng loạt sang Atomic Knowledge Claims**: Đơn vị tri thức cốt lõi là `AiResearchClaim` (`canonical_statement`, `claim_type`, `knowledge_space`, `ontology_domain`, `cultural_region`, `quality_metrics`, `approval_status`), có thể truy xuất trực tiếp từ Chatbot mà không ép mọi bài web thành FAQ.
2. **Chấm dứt phân loại sai theo chủ đề nghiên cứu**: Gỡ bỏ hoàn toàn việc kế thừa mù `$topic->category`. Mọi claim và mục tri thức đều qua `OntologyAndClassificationService::classifyKnowledgeRecord()` (*Lễ Hằng thuận* → `vietnamese_wedding_culture`, *Google Sites / Squarespace / Wix* & *Shinzen kekkon* → `product_innovation`, tách khỏi `rsvp` & `templates`).
3. **Chặn rác ngay từ Web Discovery**: `evaluateDiscoveryRelevance()` yêu cầu ít nhất 1 cụm neo chuyên ngành cưới + `>= 2` từ khóa chủ đề (`score >= 36`) và chặn dứt điểm các trang công cụ chung (`wix.com`, `squarespace.com`, `workspace.google.com`, `support.google.com`, `/wiki/Website`, `/wiki/Shinzen_kekkon`, `gui-hay-goi`) cũng như tên miền bị Admin chặn (`is_blocked = true`).
4. **Xóa bỏ điểm ảo "97–100/100" từ 1 nguồn duy nhất**: Thay bằng 8 Cổng Chất lượng (`Gate A → Gate H`) và 9 chỉ số độc lập ghi rõ nhãn `heuristic_multi_gate_v4_uncalibrated`. Nguồn `wikipedia_seed` bị giới hạn trần `source_reliability <= 65` và 1 nguồn đơn lẻ tối đa đạt `partially_supported` (CHỈ đạt `verified` khi có `>= 2` tên miền độc lập khác `content_hash` hoặc `authority_official`).
5. **Trung thực ngay cả khi Admin phê duyệt**: `AiChatbotAdminController::approve()` và `approveAllPending()` không tự nâng `trust_score` của bài `web_research` lên `100` mà giữ nguyên điểm theo bậc nguồn; các mục `product_innovation` khi được ghi nhận luôn giữ `is_active = false`.

---

## 3. 4 Không Gian Tri Thức Độc Lập (4 Knowledge Spaces)

| Không gian (`knowledge_space`) | Mục đích | Quyền truy xuất ra Chatbot Khách |
| :--- | :--- | :--- |
| `official_mayhen` | Sản phẩm, bảng giá, gói dịch vụ, chính sách chính thức Mây Hẹn | Được phép (chỉ khi Admin duyệt & còn hiệu lực) |
| `industry_cultural` | Văn hóa cưới 3 miền VN, nghi lễ, kinh nghiệm thiệp & khách mời | Được phép (tách riêng nhãn *Tham khảo*, không gán thành cam kết Mây Hẹn) |
| `product_innovation` | Ý tưởng tính năng, nghiên cứu công cụ web (Wix/Squarespace/Google Sites), tham khảo quốc tế | **CẤM TUYỆT ĐỐI** trên mọi luồng truy xuất khách hàng |
| `customer_intelligence` | Khoảng trống tri thức (`ai_knowledge_gaps`), tín hiệu nhu cầu khách hàng đã che PII | Không phát trực tiếp; chỉ định hướng học tập & cải tiến |

---

## 4. 6 Miền Ontology Chuẩn (`ONTOLOGY_DOMAINS`)

- `mayhen_product` — Sản phẩm & Chính sách Mây Hẹn
- `vietnamese_wedding_culture` — Văn hóa & Nghi lễ Cưới Việt Nam (Bắc / Trung / Nam / Phật giáo / Hiện đại)
- `digital_invitations` — Thiệp cưới Điện tử & Trải nghiệm Mời cưới
- `wedding_planning_rsvp` — Quản lý Khách mời, RSVP, Bàn tiệc & Điều phối
- `wedding_customer_experience` — Trải nghiệm & Hành trình Khách hàng Cưới
- `wedding_product_innovation` — Nghiên cứu Công cụ & Ý tưởng Sản phẩm

---

## 5. Phân Cấp Nguồn (Source Tiers) & Giới Hạn Độ Tin Cậy

| Bậc nguồn (`source_tier`) | Ví dụ | Trần `source_reliability` |
| :--- | :--- | :---: |
| `authority_official` | `mayhen.vn`, `.gov.vn`, `.edu.vn` | 88 – 98 |
| `research_paper` | `scholar`, `researchgate`, `jstor` | 84 |
| `industry_media` | `marry.vn`, `vnexpress.net`, `elle.vn`, `vogue.com`, `theknot.com` | 78 |
| `wikipedia_seed` | `vi.wikipedia.org` (chỉ làm nguồn khám phá ban đầu) | **65** |
| `community_experience` | Blog / website cộng đồng nói chung | **58** |

---

## 6. 8 Cổng Chất Lượng (`Gate A → Gate H`) & 9 Chỉ Số Độc Lập

Mọi Atomic Claim qua `QualityEvaluationGateService::evaluateAtomicClaim()`:
- **Gate A (`Source Eligibility`)**: Nguồn không bị chặn (`is_blocked = false`), không lỗi `robots.txt`, đủ `canonical_url` & `content_hash`.
- **Gate B (`Evidence Grounding`)**: Tỉ lệ trùng khớp từ khóa có nghĩa giữa phát biểu và trích dẫn nguồn `>= 0.15`, trích dẫn dài `>= 15` ký tự.
- **Gate C (`Semantic Deduplication`)**: Đối chiếu `similar_text >= 78%` với 80 claims gần nhất -> trùng gán `duplicate`.
- **Gate D (`Topic & Ontology Classification`)**: Phân loại tự động 6 miền ontology, 4 không gian, vùng miền VN.
- **Gate E (`Contradiction Detection`)**: Phát hiện phát biểu khẳng định/phủ định trái ngược nhau trong cùng `ontology_domain` + `cultural_region` -> gán `contradicted` & `needs_review`.
- **Gate F (`Business Relevance`)**: Loại bỏ nội dung sơ đẳng/chính tả/công cụ phổ thông (`is_trivial_or_off_topic = true` hoặc `business_relevance < 35`).
- **Gate G (`Actionability & Risk Classification`)**: Xếp loại rủi ro `low | medium | high` (mọi nội dung liên quan giá, pháp lý, cam kết, thanh toán = `high`).
- **Gate H (`Publication Policy`)**: Chỉ `industry_cultural` đạt `verified` (`>= 2` nguồn độc lập) + `low` risk + `business_relevance >= 65` + chế độ `autonomous` mới được `approved_reference`; mọi trường hợp khác giữ `needs_review`.

---

## 7. Typed Relational Knowledge Graph (13 Kiểu Quan Hệ)

13 kiểu quan hệ cho phép: `is_a`, `part_of`, `requires`, `related_to`, `used_for`, `precedes`, `depends_on`, `differs_by_region`, `supports_feature`, `answers_intent`, `contradicts`, `supersedes`, `suggests_improvement`.

**Quy tắc khóa cứng**:
- Nguồn nghiên cứu bên ngoài **tuyệt đối không được tạo cạnh `supports_feature`** — hệ thống tự động hạ cấp sang `suggests_improvement`.
- Cạnh `suggests_improvement` bị loại khỏi `KnowledgeGraphService::findRelatedContext()` khi trả lời khách hàng.

---

## 8. Benchmark Học Tập Độc Lập & Phát Hiện Regression (`ai_benchmark_runs`)

`KnowledgeBenchmarkService` đo 12 kịch bản holdout độc lập (10 kịch bản RSVP thực tế `RSVP-01..RSVP-10`, 1 kịch bản văn hóa vùng miền `CULT-01`, 1 kịch bản bảng giá chính thức `PROD-01`):
- Đo `grounded_score`, `coverage_score`, `hallucination_rate`, `avg_latency_ms`.
- So sánh với mốc `baseline` gần nhất của cùng domain để tính `delta_vs_baseline`.
- Tự động bật cờ `regression_detected = true` khi `delta_vs_baseline < -5.0%` hoặc `hallucination_rate` tăng so với baseline.

---

## 9. An Toàn Chuyển Đổi Dữ Liệu Cũ (`LegacyKnowledgeAuditService`)

- Lệnh xem trước (0 ghi DB): `php artisan ai:brain:audit-legacy`
- Lệnh áp dụng an toàn: `php artisan ai:brain:audit-legacy --apply`
- **Không xóa bất kỳ dòng nào**. Luôn chụp bản lưu `AiKnowledgeVersion` trước và sau khi chuyển đổi để hỗ trợ Rollback 1-chạm trong Admin.

---

## 10. Kiểm Soát Khẩn Cấp Trong Admin (`/admin/ai-chatbot`)

- `PAUSE ALL AI LEARNING` / `Bật lại AI học nền` (dừng học nền, khách chat bình thường).
- `Chặn nguồn` trên từng tên miền web (`POST /admin/ai-chatbot/sources/{source}/block`).
- `Hủy tác vụ chờ` trong hàng đợi (`POST /admin/ai-chatbot/tasks/cancel-pending`).
- `Duyệt / Loại Atomic Claims`, `Chuẩn hóa Ontology V4`, `Chạy Benchmark 12 tình huống V4`.

---

## 11. Giới Hạn Thực Tế Hiện Tại (Minh Bạch Kỹ Thuật)

1. Điểm trong `quality_metrics` hiện sử dụng bộ chấm đa chiều theo luật thực nghiệm (`heuristic_multi_gate_v4_uncalibrated`), chưa có bộ nhãn hiệu chuẩn do con người đánh dấu hàng nghìn mẫu.
2. Kiểm tra trùng nghĩa (`Gate C`) và mâu thuẫn (`Gate E`) chạy nhẹ trên PHP (`similar_text` + đối chiếu phủ định cùng miền/vùng) để tương thích 100% shared hosting yếu mà không cần Vector DB ngoài; các mâu thuẫn ngữ nghĩa tinh vi vẫn cần Critic AI & Admin xem xét ở trạng thái `needs_review`.
3. `KnowledgeBenchmarkService` đo trực tiếp năng lực Grounded Retrieval + Security Policy Gate trên 12 kịch bản holdout (0 tốn token LLM); khi cần chấm câu trả lời sinh cuối cùng bằng LLM vẫn có thể mở rộng thêm pha đánh giá online có ngân sách riêng.
