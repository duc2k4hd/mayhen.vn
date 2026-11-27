# MASTER PRODUCT SPECIFICATION
# MÂY HẸN – THE ART OF PERSONALITY (BẢN SẮC TÂM HỒN)
## Trắc Nghiệm MBTI Online, Test Tính Cách Miễn Phí (`/trac-nghiem-tinh-cach` & `/big-five`)

> **Tên concept**: Mây Hẹn – The Art of Personality (*Bản Sắc Tâm Hồn*)  
> **Tagline**: *Hiểu mình sâu hơn, hiểu nhau nhiều hơn.*  
> **Trạng thái kiểm định tâm lý học**: `pilot_beta_v1` (**Thử nghiệm minh bạch — Beta**; chưa tự tuyên bố đã kiểm định tâm lý học trên mẫu dân số lớn cho tới khi hoàn thành nghiên cứu Pilot/CFA/Test-Retest thực tế).  
> **Ngày cập nhật**: `2026-11-27`

---

## 1. RÀ SOÁT BẢN QUYỀN & NHÃN HIỆU (LICENSE & TRADEMARK REVIEW)

1. **MBTI® chính thức** là nhãn hiệu đã đăng ký thuộc sở hữu của **The Myers-Briggs Company** (`myersbriggs.org`, `themyersbriggs.com`, `mbtionline.com`), có bộ câu hỏi có bản quyền và quy trình cấp phép riêng.
2. **Trắc nghiệm 16 Nhóm Tính Cách của Mây Hẹn (`mayhen_16types_v1`)** là bộ câu hỏi tự biên soạn 100% độc lập bằng tiếng Việt dựa trên lý thuyết 4 cặp xu hướng công bố rộng rãi (E/I, S/N, T/F, J/P), **không sao chép** câu hỏi, nhân vật, hay hệ thống NERIS (`-A`/`-T`) của 16Personalities.
3. **Quyết định Title & Nhận diện**:
   - Giữ nguyên title chủ dự án yêu cầu trong tài liệu yêu cầu: **Trắc Nghiệm MBTI Online, Test Tính Cách Miễn Phí**.
   - Trên giao diện công khai & trang Phương pháp (`/trac-nghiem-tinh-cach/phuong-phap`), bắt buộc hiển thị **Tuyên bố minh bạch độc lập**: công cụ của Mây Hẹn là bài trắc nghiệm 16 nhóm tính cách độc lập (tham chiếu 4 cặp xu hướng kiểu Jung–Myers), **không phải bài đánh giá MBTI® chính thức có cấp phép**.
   - **Đề xuất Title thay thế khi cần tuân thủ nhãn hiệu chặt cho quảng cáo/phát hành rộng**: *Trắc Nghiệm 16 Nhóm Tính Cách Online Miễn Phí — Bản Sắc Tâm Hồn*.
4. **Big Five (OCEAN) — IPIP 50-Item Factor Markers (`mayhen_bigfive_ipip50_v1`)**:
   - Dựa trên **International Personality Item Pool (IPIP)** (`https://ipip.ori.org/`) — **Public Domain** (phạm vi công cộng, được phép sử dụng, dịch thuật và triển khai miễn phí với trích dẫn nguồn Goldberg et al., 2006).
   - Quy ước rõ 5 chiều: Openness (`O` / Intellect–Imagination), Conscientiousness (`C`), Extraversion (`E`), Agreeableness (`A`), Emotional Stability / Neuroticism (`N` — hiển thị rõ chiều quy đổi `Neuroticism = 100 - EmotionalStability`, không gán nhãn bệnh lý).

---

## 2. NGUỒN NGHIÊN CỨU GỐC (SOURCE REGISTRY)

| Mã nguồn | Tác giả / Tổ chức | Năm | Phương pháp / Nội dung | Phạm vi & Giới hạn | Giấy phép / URL |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `SRC-JUNG-1921` | Carl Gustav Jung | 1921 | *Psychological Types* — Lý thuyết thái độ (E/I) & chức năng nhận thức (S/N, T/F) | Nền tảng lý thuyết định tính; không phải thang đo trắc nghiệm hiện đại | Học thuật cổ điển |
| `SRC-MYERS-1980` | Isabel Briggs Myers & Peter B. Myers | 1980 | *Gifts Differing* — Mở rộng chiều J/P và khung 16 nhóm xu hướng | Lý thuyết xu hướng tự báo cáo; không đo năng lực hay IQ | `https://www.myersbriggs.org/` (checked 2026-11-27) |
| `SRC-MCCRAE-1989` | Robert R. McCrae & Paul T. Costa Jr. | 1989 | *Reinterpreting the Myers-Briggs Type Indicator From the Perspective of the Five-Factor Model* | Chỉ ra 4 chiều tương quan chặt với Big Five (E, O, A, C) và thiếu tính lưỡng cực tuyệt đối | `https://pubmed.ncbi.nlm.nih.gov/2709300/` (checked 2026-11-27) |
| `SRC-IPIP-2006` | Lewis R. Goldberg et al. | 1992 / 2006 | *International Personality Item Pool (IPIP)* — 50-item Big-Five Factor Markers | Public domain; cần bản địa hóa & kiểm định khi áp dụng tại Việt Nam | Public Domain — `https://ipip.ori.org/` (checked 2026-11-27) |
| `SRC-JOHNSON-2014` | John A. Johnson | 2014 | *Measuring thirty facets of the Five Factor Model with a 120-item public domain inventory (IPIP-NEO-120)* | Tham chiếu phát triển bản Big Five chuyên sâu 30 facet ở pha P3 | Public Domain — `https://ipip.ori.org/` |
| `SRC-ITC-2017` | International Test Commission (ITC) | 2017 | *ITC Guidelines for Translating and Adapting Tests (2nd Ed.)* | Tiêu chuẩn thích nghi đa văn hóa: dịch độc lập, dịch ngược, phỏng vấn nhận thức, kiểm định cấu trúc | `https://www.intestcom.org/page/14` (checked 2026-11-27) |

---

## 3. BENCHMARK ĐỐI THỦ & KHÁC BIỆT HÓA MÂY HẸN

| Nền tảng | Điểm mạnh UX | Giới hạn cần khắc phục tại Mây Hẹn |
| :--- | :--- | :--- |
| **16Personalities** | Thang Likert trực quan, progress mượt, artwork dễ chia sẻ | Trộn `-A/-T` (NERIS) dễ gây nhầm là MBTI chính thức; mô tả đôi khi thiên về hiệu ứng Barnum; không có nút *Xem căn cứ* từ câu trả lời thật. |
| **MBTI Online** | Quy trình Best-Fit chuyên nghiệp | Thu phí cao, chưa tối ưu bối cảnh văn hóa giao tiếp Việt Nam. |
| **Truity / BigFive Test** | Tách rõ Big Five IPIP, miễn phí trực quan | Giao diện mang tính biểu mẫu kỹ thuật, thiếu chiều sâu quan hệ cặp đôi & trải nghiệm nghệ thuật. |
| **Mây Hẹn – The Art of Personality** | **Scoring xác định 100% + Minh bạch vùng ranh giới (45–55) & Điểm hòa 50 + Best-Fit Exploration + Nút Xem Căn Cứ từng chiều + Couple 16×16 không tạo % hợp nhau giả + 16 Portraits Fine Art (SVG/3D tương tác) + Big Five IPIP độc lập + AI 12 tầng Grounded**. | Ranh giới đạo đức rõ: không chẩn đoán tâm lý, không dùng tuyển dụng, không tạo percentile giả khi chưa có mẫu chuẩn VN. |

---

## 4. KIẾN TRÚC PERSONALITY ENGINE (`App\Services\Personality\*`)

1. **`PersonalityMethodRegistry`**: Quản lý các phương pháp (`mayhen_16types_quick_v1` 32 câu, `mayhen_16types_standard_v1` 64 câu, `mayhen_16types_deep_v1` 96 câu, `mayhen_bigfive_ipip50_v1` 50 câu) với đầy đủ phiên bản, trạng thái kiểm định (`pilot_beta`), thang đo, quy tắc điểm hòa, vùng ranh giới và nguồn tham chiếu.
2. **`PersonalityTypeRegistry`**: Kho tri thức đầy đủ **16 nhóm tính cách** (tên tiếng Việt, 16 tên nghệ thuật Fine Art độc quyền Mây Hẹn, 4 xu hướng, Function Stack lý thuyết kèm ghi chú giới hạn, điểm mạnh, tình huống cần lưu ý, giao tiếp, học tập/công việc, tình yêu, gia đình, hiểu lầm phổ biến, nhóm gần kề, SVG/3D sculpture config) + **8 Chức năng nhận thức** (`Se, Si, Ne, Ni, Te, Ti, Fe, Fi`).
3. **`PersonalityQuestionBankService`**: Kho câu hỏi chuẩn hóa hoàn toàn mới cho người Việt:
   - **96 câu hỏi 16 nhóm tính cách** (24 câu/chiều, cân bằng 12 câu thuận + 12 câu đảo chiều mỗi chiều):
     - Bản **Quick (32 câu)**: 8 câu/chiều (4 thuận, 4 đảo).
     - Bản **Standard (64 câu)**: 16 câu/chiều (8 thuận, 8 đảo).
     - Bản **Deep (96 câu)**: 24 câu/chiều (12 thuận, 12 đảo).
   - **50 câu hỏi Big Five IPIP** (10 câu/chiều cho `O, C, E, A, N`, cân bằng câu thuận/đảo theo scoring key IPIP chuẩn, thang Likert 1–5).
4. **`PersonalityScoringEngine`** *(100% Deterministic — tuyệt đối không gọi AI)*:
   - **16 nhóm (Likert 1–7)**:
     - Câu thuận (`+`): `adj = raw` (`1..7`).
     - Câu đảo (`-`): `adj = 8 - raw` (`7..1`).
     - Hỗ trợ tùy chọn `unsure` (`null`, không ép bằng `4` trung lập; nếu 1 chiều có dưới 50% câu trả lời hợp lệ → đánh dấu `insufficient_data`).
     - `mean = sum(adj) / valid_count`.
     - `positive_index = ((mean - 1) / 6) * 100` (tính bằng float chưa làm tròn cho phân loại).
     - `opposite_index = 100 - positive_index`.
     - **Phân loại**: `> 50.0` → chiều dương (`E/N/T/J`), `< 50.0` → chiều âm (`I/S/F/P`), `=== 50.0` → `undecided` (KHÔNG tự gán chữ cái mặc định; trả danh sách `possible_types` đầy đủ tổ hợp).
     - **Vùng ranh giới UX**: `[45.0, 55.0]` → gắn cờ `is_boundary = true` và sinh `alternative_types` (nhóm cần đối chiếu Best-Fit).
     - **Response Quality**: Kiểm tra tỷ lệ bỏ qua, tỷ lệ chọn `unsure`, trả lời 1 mức xuyên suốt (straight-lining), độ lệch chuẩn thấp và thời gian làm bài — **chỉ hiển thị lưu ý minh bạch, không tự sửa điểm**.
     - **Calculation Hash**: `sha256` khóa phiên bản + toàn bộ câu trả lời + điểm gốc để tái lập 100%.
   - **Big Five (IPIP 50-item, Likert 1–5)**:
     - Câu thuận: `adj = raw`; câu đảo: `adj = 6 - raw`.
     - `index = ((mean - 1) / 4) * 100`.
     - KHÔNG hiển thị Percentile giả (chỉ hiển thị điểm trên thang chuẩn hóa 0–100 kèm giải thích thận trọng).
5. **`PersonalityCoupleEngine`**:
   - So sánh 2 hồ sơ độc lập (mỗi người tự làm bài riêng hoặc mời kết nối qua mã mời).
   - Phân tích 16×16 (136 cặp không thứ tự / 256 tổ hợp) dựa trên cấu trúc 4 chiều giống/khác + độ chênh lệch điểm thực tế + câu trả lời 6 câu hỏi thấu hiểu cặp đôi.
   - **Tuyệt đối KHÔNG tạo `% hợp nhau` hay `% kết hôn/ly hôn` giả**.
6. **`PersonalityAiInterpreterService`**:
   - Nhận **AI Data Contract** đầy đủ (scores, boundary flags, tie flags, relevant responses, knowledge IDs, source IDs, limitations).
   - Sinh **Báo cáo 12 tầng có căn cứ** + **AI Personality Coach** (hỏi sâu bám sát phiên kết quả) + **Personality Growth Plan (7/14/30 ngày)**.
   - Tích hợp **AI Validation Guard** chống: sai mã nhóm, tự thêm `-A/-T`, bịa điểm Cognitive Function (`Ni 95%`), suy đoán bệnh lý, tạo `% hợp nhau`, hay khuyên chia tay. Luôn có fallback chuẩn học thuật khi AI bận/timeout.

---

## 5. DATABASE SCHEMA (`2026_11_27_100000_create_personality_assessment_tables.php`)

Tuân thủ tuyệt đối chuẩn production MySQL InnoDB của Mây Hẹn (`$table->engine = 'InnoDB'`, `unsignedBigInteger('user_id')->nullable()->index()`, không dùng FK cứng sang bảng `users` legacy):

1. `personality_sessions`: Lưu phiên làm bài (`CREATED → IN_PROGRESS → SUBMITTED → SCORED → REPORT_READY`, `session_token`, `slug`, `share_token`, `model_code`, `method_version`, `question_order_json`, `responses_json`, `scores_json`, `suggested_type`, `best_fit_type`, `possible_types_json`, `boundary_flags_json`, `quality_flags_json`, `calculation_hash`, `ai_report_json`, `ai_status`, `growth_plan_json`, `user_notes`, `is_shared`, `started_at`, `submitted_at`, `expires_at`).
2. `personality_couple_reports`: Lưu hồ sơ kết nối cặp đôi độc lập (`slug`, `invite_code`, `user_a_id`, `user_b_id`, `session_a_id`, `session_b_id`, `couple_answers_a_json`, `couple_answers_b_json`, `share_consent_a`, `share_consent_b`, `comparison_json`, `ai_synthesis_json`).
3. `personality_followups`: Lưu lịch sử hỏi sâu **AI Personality Coach** theo từng phiên (`session_id`, `topic_code`, `question`, `answer`, `evidence_refs_json`).

---

## 6. ROUTES, SEO, QUYỀN RIÊNG TƯ & TÍCH HỢP HỆ SINH THÁI

- **Namespace công khai**:
  - `GET /trac-nghiem-tinh-cach` (Landing Page Editorial + Chọn 3 phiên bản bài test + Triển lãm 16 hình tượng + Phương pháp + FAQ)
  - `GET /trac-nghiem-tinh-cach/bat-dau` & `POST /trac-nghiem-tinh-cach/phien` (Phiên làm bài tập trung, 1 câu/màn hoặc nhóm 4 câu, phím tắt `1–7`, Auto-save chống mất bài khi refresh)
  - `GET /trac-nghiem-tinh-cach/ket-qua/{slug}` (Trang kết quả như sách nghệ thuật 13 chương + Reveal Animation 2.5s có nút bỏ qua + Biểu đồ 2 phía Spectrum + Nút **Xem căn cứ** + Best-Fit Exploration + AI 12 tầng + Coach + Growth Plan + Share Card PNG `1:1`, `4:5`, `9:16` — `noindex` tuyệt đối)
  - `GET /trac-nghiem-tinh-cach/16-nhom` (Triển lãm 16 Hình Tượng Interactive 3D/SVG + chuyển chế độ 2D/3D)
  - `GET /trac-nghiem-tinh-cach/tinh-duyen` & `GET /trac-nghiem-tinh-cach/cap-doi` (Mây Hẹn Personality for Couples + Couple Portrait + 6 câu hỏi thấu hiểu)
  - `GET /trac-nghiem-tinh-cach/chuc-nang-nhan-thuc` (8 Cognitive Functions & Function Stack lý thuyết)
  - `GET /trac-nghiem-tinh-cach/huong-noi-huong-ngoai` (Chuyên trang chiều E/I chuẩn tâm lý học)
  - `GET /trac-nghiem-tinh-cach/phuong-phap` (Báo cáo minh bạch phương pháp, nguồn nghiên cứu & giới hạn)
  - `GET /trac-nghiem-tinh-cach/lich-su` (Personality History — `auth`, `noindex`)
  - `GET /trac-nghiem-tinh-cach/{typeCode}` (16 trang SEO chuyên sâu độc lập cho `intj`, `infj`, `infp`, `enfp`, ..., `esfp`)
  - `GET /big-five` (Trắc nghiệm Big Five OCEAN độc lập — IPIP 50-item Public Domain)
- **Tích hợp Mây Hẹn**:
  - `ToolsGovernanceService` (`personality` tool #10 → `/admin/personality` + Psychometric Dashboard thực tế từ dữ liệu DB).
  - `SocialBannerService` (`personality` & `big_five` slots chuẩn `1200×600px`).
  - `SitemapGeneratorService` (đưa toàn bộ trang SEO công khai + 16 trang mã nhóm + `/big-five` vào `sitemap-pages.xml`, TUYỆT ĐỐI không đưa `/ket-qua/{slug}`, `/lich-su`, `/cap-doi/{slug}` vào sitemap).
  - `header.blade.php` & `footer.blade.php` + Docs `CLI-SVC-015.md` + AI Curriculum.
