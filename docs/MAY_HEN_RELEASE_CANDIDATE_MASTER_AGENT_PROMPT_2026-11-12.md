# MASTER PROMPT — MÂY HẸN RELEASE CANDIDATE 1.0
## Khóa phạm vi tính năng → kiểm toán chứng cứ → sửa từng lỗi nhỏ → nghiệm thu đủ điều kiện thương mại

**Workspace:** `E:\Project\Laravel\mayhen.vn` (nếu Git workspace thực tế khác, tự xác minh bằng `git rev-parse --show-toplevel`, không đoán đường dẫn).  
**Vai trò:** Principal QA & Release Engineer, Senior Laravel Architect, Domain Logic Reviewer, Visual UX Auditor, Data Integrity & Security Engineer.  
**Chế độ làm việc:** `SCOPE FREEZE / VERIFY / FIX / REGRESSION / REVIEW / NEXT`.  
**Quy tắc cao nhất:** **Không tự tin vào câu “100% DONE / 1.502 tests PASSED”; chỉ chấp nhận bằng chứng thu từ Git HEAD và runtime đang chạy.**

## A. ĐỌC BẮT BUỘC TRƯỚC KHI CHẠM CODE

1. `context.gemini.md` — đọc cả nguyên tắc đầu file và toàn bộ cập nhật cuối đến Đề án 166; phân biệt mốc thời gian và cập nhật thay thế.
2. `AGENTS.md`, `CLAUDE.md` nếu có, `.ai/rules/` nếu tồn tại.
3. `.agents/skills/mayhen-testing-quality/SKILL.md`, `mayhen-code-review`, `mayhen-auth-security`, `mayhen-payments`, `mayhen-guest-rsvp`, `mayhen-visual-editor`, `mayhen-invitation-standards`, `mayhen-invitation-templates`, `mayhen-admin`, `mayhen-ui-ux`, `mayhen-performance`, `mayhen-deployment` khi phạm vi liên quan; đọc các skill khác đúng theo path.
4. `docs/MASTER PROMPT MAYHEN.VN.docx`, `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`, `docs/MAY_HEN_DOMAIN_INVARIANTS_AND_STATE_MACHINES.md`, `docs/MAY_HEN_CROSS_MODULE_E2E_MATRIX.md`.
5. `docs/MAY_HEN_PREMIUM_FEATURE_CATALOG.md`, `docs/MAY_HEN_PREMIUM_UPGRADE_ROADMAP.md`, `docs/admin-vnext/*`, `docs/qa/*`, `docs/implementation/*` và mọi roadmap QA liên quan.
6. `docs/MAY_HEN_FINAL_SCOPE_FREEZE_INDEPENDENT_REVIEW_2026-11-12.md` và `docs/MAY_HEN_RELEASE_CANDIDATE_TEST_GATES.csv` nếu đã được người dùng chép vào dự án.
7. Toàn bộ `routes/web.php`, `routes/admin.php`, routes console, models, migrations, requests, policies/middleware, controllers, services, Blade, CSS, JS và tests đang có. Không tạo bài test mới trước khi kiểm tra có test tương đương.

Nếu không có một tài liệu trong repo, ghi `MISSING` và tiếp tục kiểm tra nguồn có thực; không tự tưởng tượng nội dung.

## B. QUY TẮC KHÓA PHẠM VI SẢN PHẨM

- **KHÔNG thêm menu/tính năng mới theo cảm hứng AI.** Các đề án 01–166, Admin 10 wave + 18 innovation, NXT-01–NXT-15 và 60 Premium Ideas đều được đánh giá là đã có yêu cầu/triển khai ở mức nào đó. Phải đối chiếu tồn tại thật trước khi sửa hoặc mở rộng.
- Chỉ phát triển bổ sung khi phát hiện bằng bằng chứng một *khoảng trống nghiệp vụ đã nằm trong luồng khách trả phí* (ví dụ: RSVP hai sự kiện không độc lập, nhà cung cấp chưa xác nhận tin gửi nhưng đánh dấu `is_sent`).
- Không “cải tiến” bằng cách viết lại 4 template gốc hoặc thay toàn bộ UI. Giữ nguyên nghệ thuật template hiện tại, chỉ sửa bug thực tế.
- Cấm cài thêm framework CSS/JS, thư viện ngoài, Strix hay tool mới. Laravel/Pest, native browser test/tool đã được cài trong dự án có thể sử dụng nếu được phép.
- Tách biệt 10 section chuẩn của template thường với 7 section AI Curated Native; không nhầm vị trí album/dresscode do thay đổi cách đánh số giữa các tài liệu.
- Không tự commit, push, deploy, chuyển tiền thật, gửi SMS/Zalo thật hoặc chạy thao tác phá dữ liệu production.

## C. GATE 0 — THIẾT LẬP SỰ THẬT CÓ THỂ TRUY VẾT

**Làm ngay trước khi sửa:**

1. In và lưu `git rev-parse HEAD`, nhánh hiện hành, `git status --short`, `php -v`, `php artisan --version`, `composer show --direct`, database driver, APP_ENV (không tiết lộ secrets).
2. Kiểm kê route thật bằng `php artisan route:list --json` hoặc định dạng phù hợp; phân loại Admin / Client / Guest / Payment / Partner / API / Scheduled Job.
3. Kiểm kê tests bằng tên path, loại test, chức năng, database, test assertions; đánh dấu test dùng `assertTrue(true)`, chỉ grep string code, chỉ assert HTTP 200 hoặc có test trùng lặp vô nghĩa.
4. Chạy `php artisan test --compact` trong môi trường cô lập, giữ **nguyên output timestamp và test file list**. Nếu được, xuất JUnit XML. Không tự báo 1.502 nếu phiên này khác con số đó; nếu lỗi môi trường, ghi `BLOCKED` và nguyên nhân.
5. Kiểm tra thực tế các file test liên quan Đề án 153–166: `SignatureArchiveSuiteTest`, `SmartFocalPointSuiteTest`, `MultilingualStudioSuiteTest`, `InvitationPersonaStudioSuiteTest`, `PhotoWishModerationE2ETest`, `LiveWallMediaSuiteTest`, `SmartGroupEventScopeSuiteTest`, `PublishDiffSuiteTest`, `RsvpChangeHistorySuiteTest`, `CompanionCardSuiteTest`. Nếu không còn test tương đương, bổ sung regression tests bền vững. **Không xóa regression test sau khi chạy.**
6. Đối chiếu 995 dòng `NOT_RUN` trong `docs/admin-vnext/MAY_HEN_ADMIN_TEST_CASES.csv`, 534 `IMPLEMENTED_UNVERIFIED`, 782 `ACCEPTED`, 5.090 `PASS` và 702 visual. **Chỉ sửa trạng thái một case khi có chứng cứ riêng; không sửa CSV hàng loạt sang PASS.**
7. Đối chiếu sổ bug với code: mỗi bug `RESOLVED` phải có testcase tái hiện lỗi cũ hoặc kiểm tra được bản sửa tương ứng.
8. Phát hành `RELEASE_G0_EVIDENCE.md` ghi rõ cái gì VERIFIED/REPORTED/UNVERIFIED/MISSING và tỷ lệ coverage thực tế, có SHA Git và môi trường.

## D. GATE 1 — SỬA P0/P1 VỀ DỮ LIỆU, TIỀN, QUYỀN, LỊCH CƯỚI

Thực hiện **theo thứ tự** từ danh mục test gate đính kèm. Các hành vi quan trọng phải được kiểm tra ở ba tầng: backend, database, browser.

### 1. RSVP đa sự kiện — không được bỏ qua

- Dựng test một khách A được mời hai sự kiện: Vu Quy ngày 1, Tiệc Cưới ngày 2. Khách dự Vu Quy **2 người**, Tiệc Cưới **4 người**, sau đó đổi chỉ Tiệc Cưới thành **3**. Đảm bảo không mất RSVP Vu Quy, số ghế bàn theo đúng event, report bếp và tổng sĩ số đúng.
- Dựng khách B chỉ được mời tiệc, thử giả `event_id` của Vu Quy qua API; phải chặn dù frontend đã ẩn.
- Dựng token Guest Group bị thu hồi, wedding locked/unpublished/expired, rehearsal group tùy ý; dữ liệu riêng tư không rò rỉ.
- Nếu schema 1 `event_id`/response không đáp ứng mô hình nghiệp vụ, thiết kế lại dữ liệu có migration an toàn và migration dữ liệu cũ; test MySQL unique constraints / races trước khi code live.

### 2. Payment và hoàn tiền

- Idempotent webhook SePay: cùng provider txid 20 lần, ngoài thứ tự, dư/thiếu tiền, giao dịch cancelled, trùng code, khác tài khoản thụ hưởng. Không thêm quyền mua gói hai lần.
- Hoàn tiền phải có bằng chứng ngân hàng / state chính xác. Không đổi `refunded` chỉ bằng nút bấm nếu chưa xác minh thực hiện hoàn tiền. Phân biệt đặt yêu cầu, được duyệt, xử lý, xác nhận hoàn tất.
- Tách doanh thu Mây Hẹn khỏi tiền mừng cưới của cô dâu/chú rể; giá gói và entitlements đồng bộ sau gia hạn/hủy.

### 3. Public vs Draft và rollback

- Khi khách sửa ảnh/tài khoản ngân hàng/ngày/sảnh/sự kiện trong Studio mà chưa xuất bản: thiệp public cũ giữ nguyên; link đã gửi không biến đổi.
- Khi publish: snapshot đủ trường và version, event data/album/timeline/guest scope/owner không bị ghi đè; khi rollback không làm mất RSVP/số khách/ảnh thật.
- Test nhiều tab, autosave offline/online, 409 conflict, crash giữa upload và publish.

### 4. Admin, Partner, privacy

- Ma trận Anonymous / Customer A / Customer B / Partner A / Partner B / Staff / Manager / Admin / Super Admin với từng thao tác nhạy cảm: xuất PII, xem ảnh khách gửi, chỉnh ngân hàng, phân quyền, hoàn tiền, sửa thiệp, khóa thiệp, thay đổi giá, xuất CSV.
- Đảm bảo masking AI log, SupportLens và AuditLog; không lộ password, token, số tài khoản, prompt/ảnh riêng tư.
- Kiểm tra lại lỗi ticket 500 `SupportTicketReply.user()` trên màn Client/Staff thật và ghi regression.

### 5. Khôi phục sau sự cố

- Snapshot/checksum chỉ là bước một. Tạo staging restore drill cho **toàn bộ dữ liệu cưới + media + liên kết + idempotency logs**.
- Rà soát ảnh, QR khách, RSVP, lịch sử, thanh toán, bản nháp/live, Time Capsule, bàn tiệc và Domain sau khôi phục.
- Ghi RPO/RTO đo thật; không bịa thời gian khôi phục.

## E. GATE 2 — HÀNH TRÌNH KHÁCH HÀNG VÀ NGÀY CƯỚI

- Gửi thiệp cho nhóm nhà trai/nhà gái có hai lịch cưới khác nhau, xem thiệp qua token, Maps/.ics/RSVP/QR theo đúng sự kiện.
- Khi đổi lịch sát ngày cưới: tạo impact report, chọn danh sách cần thông báo, dry-run tin nhắn, duyệt và gửi an toàn, không ghi “đã gửi/đã giao” nếu provider chưa chứng thực.
- Tại quầy lễ tân: check-in cùng token từ hai thiết bị, retry, xung đột 2 ca trực, offline/resync, khách walk-in, bàn hết ghế; không nhân đôi headcount.
- Nội dung ảnh/chữ ký/lời chúc: chỉ công khai sau duyệt (khi policy yêu cầu), xóa/ẩn phải lan tới thiệp, Live Wall, album và lưu bút; không giữ link public lộ ảnh riêng tư.
- Thẻ kẹp thiệp A4: bản in đúng khổ, crop, QR đọc tốt sau in, guest token độc lập, in lại không mất liên kết; không tự nhận đã hỗ trợ CMYK nếu chưa có.
- AI Curated: AI chỉ viết content, chọn template native thật, lưu ổn định 7 section, F5 không phát lại animation, văn bản ngắn đẹp, màu người dùng chọn.

## F. GATE 3 — VISUAL QA THEO TỪNG MÀN HÌNH

Kiểm tra bằng trình duyệt thực, không chỉ đọc CSS:

- Viewport 320×700, 375×812, 390×844, 430×932, 768×1024, 1024×768, 1366×768, 1440×900, 1920×1080.
- Trên các nhóm: Home, Templates, Pricing, Detail/Preview, Register/Login, Dashboard, Studio, Curated Wizard, Public invitation (4 native), RSVP, Guest List, Seating, Reception, Live Wall, Gift, Photo Drop, Keepsake, Print A4, Partner Portal, Admin Dashboard, Users, Orders, Refunds, Support, Radar, Rescue, Messaging, System Resilience.
- Mỗi trang phải test ít nhất: mặc định với dữ liệu thật của staging, không có dữ liệu, dữ liệu dài tiếng Việt, loading, validation lỗi, forbidden/404, network error, modal/overlay, cuộn/đổi tab, F5 và slow network nếu liên quan.
- Với form, thao tác thực từ đầu đến cuối, không chỉ screenshot. Có login thật theo role staging; không dùng ảnh login để nghiệm thu partner dashboard hoặc Admin bên trong.
- Đánh giá aesthetics Quiet Luxury: tỉ lệ ảnh, typography, độ dài paragraph, khoảng trắng, bố cục mobile, cỡ vùng chạm, focus keyboard, màu sắc và hiệu ứng. Thử reduce-motion, tăng cỡ chữ, text zoom.
- Chụp BEFORE/AFTER tại cùng viewport, cùng dữ liệu; lưu đường dẫn, commit SHA, giờ và lỗi console/network; bỏ qua screenshot chụp giữa animation nếu dùng làm bằng chứng nghiệm thu layout cuối.
- Không thay đổi theme gốc chỉ vì một screenshot xấu: xác định nguyên nhân CSS/backend/missing image trước.

## G. GATE 4 — UAT THƯƠNG MẠI VÀ RELEASE GO/NO-GO

1. Ba hành trình cô dâu/chú rể giả lập: Free → Plus/Pro; chọn mẫu → thiết kế → trả tiền sandbox → publish → gửi link → nhận RSVP → check-in; sau cưới: album/lời chúc/sách kỷ yếu.
2. Một hành trình studio đối tác: tạo thiệp, bàn giao, đổi owner, hoàn tiền hoa hồng hoặc khiếu nại, audit log.
3. Một hành trình nhân viên Admin: nhận ticket mới, điều tra đúng quyền, hồi đáp, sửa sự cố, xác nhận khách hài lòng.
4. Một hành trình nhà hàng: đa sự kiện, check-in số lượng cao, khách phát sinh, offline fallback, xuất danh sách bàn.
5. Một diễn tập restore trên staging, một thử nghiệm rollback bản phát hành và một thử nghiệm webhook trùng.
6. Đối chiếu mọi tuyên bố marketing: gói giá, rating, “trọn đời”, khuyến mãi, dịch vụ hỗ trợ 15 phút, chứng nhận Bộ Công Thương, phương thức thanh toán, khả năng tương thích. Gỡ/điều chỉnh nếu chưa có chứng cứ.
7. Báo cáo GO/NO-GO kèm P0/P1 còn lại, người chịu trách nhiệm và giới hạn kiểm thử; không tự tuyên bố an toàn tuyệt đối.

## H. VÒNG LẶP CHỈNH SỬA BẮT BUỘC (MỖI BUG/TASK)

1. Ghi `ID`, phạm vi, actor, expected và current behavior.
2. Tái hiện trên môi trường test, lưu evidence ban đầu; không dựng lỗi giả.
3. Kiểm tra nguyên nhân từ controller/service/model/view/JS/DB và tương tác module khác.
4. Viết regression test **trước** bản sửa nếu tái hiện được.
5. Sửa trực tiếp từng file bằng công cụ edit file hiển thị diff. Không sửa file gián tiếp bằng script vô hình.
6. Chạy targeted tests + test liên quan, `vendor/bin/pint --dirty --format agent` nếu PHP đổi.
7. Chạy browser test khi UI bị ảnh hưởng; chụp before/after.
8. Kiểm tra quyền và trạng thái dữ liệu sau rollback, F5, hai tab.
9. Cập nhật case evidence, bug register, roadmap, `context.gemini.md` (bắt buộc cho thay đổi code) và commit message tiếng Việt có dấu, không tự commit/push.
10. Nếu còn P0/P1 trong module, không đánh dấu DONE. Sau khi PASS thật, tự sang case ưu tiên tiếp theo trong cùng phiên.

**Trạng thái hợp lệ:** `NOT_RUN`, `BLOCKED`, `REPRODUCED_FAIL`, `FIXED_AWAITING_TEST`, `PASS_VERIFIED`, `NOT_APPLICABLE`. Không tạo `PASS` từ việc file tồn tại.

## I. FILE BÁO CÁO MONG MUỐN

- `docs/qa/release-candidate/G0_BASELINE_EVIDENCE.md`
- `docs/qa/release-candidate/FEATURE_CROSSWALK.md` (so DOCX/PRD/context/route/test, phân loại historical / implemented / not verifiable)
- `docs/qa/release-candidate/RELEASE_CANDIDATE_TEST_GATES.csv` (sao chép và cập nhật từ file đính kèm)
- `docs/qa/release-candidate/BUG_FIX_REGISTER.md`
- `docs/qa/release-candidate/VISUAL_QA_SIGNOFF.md`
- `docs/qa/release-candidate/MYSQL_PROVIDER_RESTORE_PROOF.md`
- `docs/qa/release-candidate/GO_NO_GO_RELEASE_REPORT.md`

Chỉ tạo những file này khi cần giữ bằng chứng và theo quy tắc tài liệu dự án; không tạo tài liệu trùng với file hiện hữu.

## J. KHỞI ĐỘNG NGAY

**Bắt đầu G0 hoàn toàn đọc-only trước.** Không code tính năng mới trước khi biết chính xác tình trạng test và route hiện tại. Sau G0, xử lý bug P0/P1 đã tái hiện theo thứ tự danh mục `MAY_HEN_RELEASE_CANDIDATE_TEST_GATES.csv`. Khi chặn bởi môi trường/secret/production, báo rõ và chuyển sang task độc lập khác. Không hỏi lại mỗi lần chuyển task nếu phạm vi và điều kiện đã rõ. Không hứa hoàn thành ngoài phiên hiện tại; trước khi phiên kết thúc cập nhật checkpoint và ID task kế tiếp.

**Kết quả mong muốn:** Không phải “1.502 test xanh”. Kết quả là một nền tảng có thể **đáng tin cậy cho một đám cưới thực**, không gửi nhầm người, không làm sai lịch, không nhân đôi khách, không lộ dữ liệu, không tính sai tiền và không có trải nghiệm khiến khách trả tiền thất vọng.
