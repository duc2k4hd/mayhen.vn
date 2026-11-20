# Mây Hẹn Autonomous Research & Intelligence Engine V3

> **Phiên bản:** 3.0 — Nâng cấp trực tiếp trên nền AI Brain V1/V2 của `mayhen.vn`  
> **Nguyên tắc nền tảng:** *Chủ động nghiên cứu nguồn web hợp lệ có kiểm chứng, phân tách tuyệt đối tri thức nghiên cứu với tri thức chính thức Mây Hẹn, tôn trọng `robots.txt`/SSRF Guard và bảo vệ tài nguyên hosting.*

---

## 1. Giải Quyết Tận Gốc Trạng Thái `idle_no_new_data`

Ở phiên bản V2, khi 5 nguồn nội bộ (`plans`, `invitation_templates`, `client_docs`, `workflow_graph`, `ai_chatbot_logs`) không đổi hash, `AutonomousBrainWorkerService` kết luận `idle_no_new_data`.

Từ V3, khi hàng đợi tác vụ nội bộ trống và `external_research_enabled = true`, bộ lập lịch `CurriculumEngineService::pickNextResearchAction()` tự động chuyển sang chuỗi nghiên cứu tự chủ:
1. `research_extract` — tải & trích xuất các nguồn web đang ở trạng thái `discovered`.
2. `research_verify` — đối chiếu bằng chứng, phản biện Critic AI & chưng cất các `hypothesis` claims.
3. `research_discover` — khám phá nguồn mở mới cho các chủ đề Curriculum còn thiếu độ phủ hoặc đến lịch `next_research_at`.
4. `research_expand` — mở rộng có kiểm soát sang chủ đề con liên quan trong phạm vi Goal/Domain Admin cho phép.
5. `idle_until_next_research` — chỉ chuyển sang trạng thái nghỉ có hẹn lịch (0 token) khi mọi chủ đề hiện tại đã hoàn thành lượt nghiên cứu và đã có `next_research_at` trong tương lai.

---

## 2. Phân Tách Tuyệt Đối 3 Lớp Tri Thức (`knowledge_layer`)

| Lớp tri thức | Mục đích | Quy tắc hiển thị & duyệt |
| :--- | :--- | :--- |
| `official_mayhen` | Tính năng thật, bảng giá hiện hành, chính sách & hướng dẫn chính thức của Mây Hẹn | Bắt buộc nguồn nội bộ xác thực + Admin phê duyệt. **Nguồn duy nhất** được khẳng định Mây Hẹn cung cấp gì. |
| `research_general` | Văn hóa cưới hỏi 3 miền Việt Nam, xu hướng thiệp cưới, kinh nghiệm quản lý tiệc & khách mời | Nguồn web hợp lệ có `source_url` & `content_hash`. Hỗ trợ tư vấn sâu, **cấm gán thành tính năng/giá của Mây Hẹn**. |
| `applied_insight` | Ý tưởng cải tiến sản phẩm/UX suy ra từ nghiên cứu thị trường | Luôn ở trạng thái `pending`, **ẩn hoàn toàn** khỏi Chatbot khách hàng, chỉ dành cho Admin tham khảo phát triển sản phẩm. |

---

## 3. Safe Web Crawler, Discovery & Anti-Poisoning

- **Đa kênh khám phá (`WebSearchDiscoveryService`)**: Search API tùy chọn (`tavily`/`serper`/`brave` nếu có `AI_SEARCH_API_KEY`), DuckDuckGo HTML tiếng Việt (`kl=vn-vi`) và Wikipedia Tiếng Việt MediaWiki API (CC BY-SA 4.0). Khử trùng URL bằng `url_hash`.
- **An toàn mạng & đạo đức thu thập (`SafeWebCrawlerService`)**:
  - Chặn SSRF (`localhost`, `127.0.0.0/8`, `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `169.254.169.254`, `.local`, `.internal`).
  - Đọc và tôn trọng `robots.txt` (cache 24h); đường dẫn bị cấm đánh dấu `robots_blocked`.
  - Không vượt CAPTCHA / Cloudflare Turnstile / trang yêu cầu đăng nhập (`captcha_or_login_wall_detected`).
  - Giới hạn tốc độ theo domain (`>= 3s`), timeout `8s`, tối đa `3` redirects, dung lượng tối đa `512KB`, không dùng Headless Browser.
  - Lọc sạch Prompt Injection nhúng trong HTML bằng `SecurityPolicyGate` trước khi đưa vào LLM; phát hiện copycat trùng `content_hash` giữa các domain khác nhau.

---

## 4. Dynamic Curriculum (L1 → L7) & Admin-Defined Goals

- **Lộ trình khởi tạo mặc định**: *Chuyên sâu Ngành Cưới & Thiệp Cưới Điện Tử Việt Nam* (16 chủ đề nền tảng thuộc 4 nhóm: Thiệp cưới điện tử, Văn hóa cưới hỏi Bắc–Trung–Nam, Quản lý đám cưới/RSVP/Bàn tiệc/Check-in, UX/UI & hành trình cặp đôi).
- **Admin tự giao mục tiêu mới không cần sửa code**: Tại `/admin/ai-chatbot?tab=learning` → bấm **`+ Giao mục tiêu học mới`** (hoặc tạo qua `CurriculumEngineService::createAdminGoal`), hệ thống tự phân rã thành các chủ đề con, tạo truy vấn tiếng Việt và đưa vào lịch nghiên cứu tự động.

---

## 5. Lệnh CLI V3 & Vận Hành

```bash
# Kiểm tra sức khỏe toàn diện (13/13 bảng AI, Crawler, SSRF/Robots Guard, Budget)
php artisan ai:brain:doctor

# Xem & khởi tạo lộ trình Ngành Cưới Việt Nam (L1-L7)
php artisan ai:brain:curriculum --seed

# Khám phá nguồn web hợp lệ mới
php artisan ai:brain:discover --limit=1

# Tải nguồn, trích xuất luận điểm & phản biện đa nguồn
php artisan ai:brain:research --max-docs=2

# Mở rộng chủ đề con liên quan trong phạm vi cho phép
php artisan ai:brain:expand

# Chạy liên tục trên máy cá nhân (Windows/Linux — dừng Ctrl+C)
php artisan ai:brain:work

# Chạy 1 chu kỳ hữu hạn cho Cron shared hosting (cPanel/AZDIGI)
php artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2
```
