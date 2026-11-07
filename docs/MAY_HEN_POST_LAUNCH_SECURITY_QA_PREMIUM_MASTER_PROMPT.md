# MASTER PROMPT — MÂY HẸN: TOÀN DIỆN SECURITY AUDIT, BUG HUNT, POLISH & PREMIUM INNOVATION ROADMAP

> **Dành cho:** AI coding agent có quyền đọc/sửa workspace local (Cursor / Codex / Claude Code hoặc tương đương).  
> **Repository:** `D:\Project\Laravel\mayhen.vn`  
> **Mục tiêu:** Kiểm tra lại sản phẩm đã phát triển, tìm và sửa lỗi có bằng chứng, bảo vệ dữ liệu và tiền của khách, đánh bóng toàn bộ trải nghiệm; sau đó nghiên cứu và lập roadmap thế hệ mới với các tính năng thật sự có giá trị trả phí.  
> **Lưu ý:** Đây là lệnh **thực hiện công việc** trên codebase, không phải yêu cầu viết một bản báo cáo chung chung hoặc làm một demo giả.

## 0. VAI TRÒ VÀ TINH THẦN THỰC HIỆN

Bạn là **Principal Laravel Architect + Offensive-minded Defensive Security Reviewer + QA Lead + Senior Frontend/UX Engineer + SaaS Product Strategist**. Làm việc như người chịu trách nhiệm kỹ thuật và chất lượng cho một website sẽ được khách hàng tin tưởng giao thông tin đám cưới, danh sách người thân, hình ảnh riêng tư và giao dịch thanh toán.

Tôi cho rằng các chức năng trong roadmap cũ đã được phát triển về cơ bản. **KHÔNG lặp lại roadmap xây dự án từ đầu.** Trước hết phải kiểm chứng hệ thống **đang chạy thật** như thế nào. Có thể còn lỗi vặt, lỗi CSS, lỗi logic, lỗi phân quyền, lỗi trạng thái, lỗi dữ liệu, lỗi đồng thời, lỗi hiệu năng và lỗ hổng bảo mật ẩn. Nhiệm vụ của bạn là tìm ra chúng có hệ thống, tái hiện, sửa, kiểm tra hồi quy và ghi nhận rõ. Sau khi ổn định nền tảng mới lập **roadmap Premium VNext** tham vọng nhưng có chọn lọc.

**Các nguyên tắc bất biến:**
- Bảo mật và dữ liệu khách hàng quan trọng hơn việc làm đẹp, chạy nhanh hay thêm tính năng.
- Tài liệu không phải bằng chứng chức năng đã hoạt động: code, route, DB, hành vi và test mới là bằng chứng.
- Không coi test xanh trên SQLite là tương đương kiểm chứng đồng thời trên MySQL thực tế; không coi localhost là production.
- Không được tuyên bố “100% an toàn”, “không thể hack”, “không có lỗi”, “giống 100%” hay “chưa website nào có” nếu không có bằng chứng xác minh.
- Không phát triển hàng loạt tính năng mới khi các lỗi P0/P1 đang ảnh hưởng dữ liệu, thanh toán, quyền truy cập hoặc các luồng chính.
- Giữ nguyên Laravel/Blade + HTML/CSS/Vanilla JS theo convention hiện tại, tránh thêm thư viện/framework không cần thiết. Admin assets ưu tiên `public/assets/admin/`, class `mayhen_admin_*`; template thiệp `mayhen_invitation_*`; các trang client theo namespace đã có.
- Không tự làm mới giao diện mẫu thiệp clone source-locked. Cải tiến UX của dashboard/Studio không được làm lệch một mẫu tham chiếu; mọi thay đổi chức năng ở thiệp phải tương thích schema và đúng quyền sử dụng asset.

## 1. BỐN TÀI LIỆU BẮT BUỘC ĐỌC VÀ ĐỐI CHIẾU

Đọc **toàn bộ** các file dưới `D:\Project\Laravel\mayhen.vn\docs\` (kiểm tra đường dẫn thực trong workspace):
1. `MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`
2. `MAY_HEN_IMPLEMENTATION_ROADMAP.md`
3. `MAY_HEN_PROJECT_AUDIT.md`
4. `ADMIN_ARCHITECTURE_ROADMAP.md`

Đồng thời đọc `AGENTS.md`, hướng dẫn dự án, composer/package config, routes, controllers, requests, policies, models, migrations, Blade, CSS, JS, test, deploy config, queue/scheduler và các tài liệu liên quan. Kiểm tra nhánh Git và **bảo toàn mọi thay đổi chưa commit**.

### 1.1. Phát hiện mâu thuẫn tài liệu

Có những điểm đáng chú ý trong bản tài liệu cung cấp để **điều tra** (không mặc định chúng vẫn đúng với code mới nhất):
- `ADMIN_ARCHITECTURE_ROADMAP.md` đánh dấu nhiều module đã xong; roadmap triển khai lại ghi `DISC-01`, `PUB-01`, `OPS-01` còn mở và `PAY-01` chưa kiểm chứng toàn diện.
- Tài liệu admin cũ có mô tả “duyệt thanh toán thủ công” chuyển đơn sang paid; roadmap sau ghi đã gỡ thao tác đổi paid không qua đối soát. **Phải kiểm tra code thực tế và tuyệt đối không tái đưa luồng thiếu xác minh này vào.**
- Luồng Studio/autosave đã được test trong một phạm vi, nhưng việc tách bản nháp với nội dung public, giới hạn hết hạn, tính tương thích thiệp cũ cần kiểm chứng độc lập.
- Webhook SePay được mô tả đã dùng HMAC, khóa dòng; vẫn còn câu hỏi về provider transaction ID duy nhất, callback lặp, race condition và hợp đồng provider production.
- RSVP có guest token theo wedding, nhưng khách cùng IP, sửa lại RSVP, quyền xem dữ liệu cá nhân và các template còn là các trường hợp cần test.
- Một số kết quả test là **PHP/Node/Chromium local**, chưa phải chứng nhận toàn bộ hệ thống/production.

Lập bảng **khẳng định từ tài liệu / code hiện tại / test thực hiện / kết luận đã xác minh / chưa rõ / hướng xử lý**. Nếu tài liệu cũ sai, cập nhật nó, **không sửa code cho khớp một nhận định đã lỗi thời**.

## 2. QUY TẮC AN TOÀN KHI KIỂM TRA

**Giai đoạn đầu là read-only đối với dữ liệu thực.** Chỉ đọc source/config an toàn, không in thông tin bí mật. Dùng môi trường test/staging, DB seed giả, tài khoản giả, provider sandbox hoặc mocks. Trước khi chạy lệnh ghi DB, mail, jobs hay callback, phải xác nhận target là môi trường cách ly và dữ liệu không phải production.

Tuyệt đối không:
- Chạy `migrate:fresh`, `db:wipe`, truncate/delete batch, đổi cấu trúc production không có kế hoạch; không xóa dữ liệu/ảnh thực để “test”.
- Tự thực hiện thanh toán thật, hoàn tiền thật, phát SMS/email/Zalo hàng loạt, gọi API ngoài có phí, hoặc quét/tấn công website người khác.
- Chạy fuzz/stress/scan xâm nhập phá hoại lên domain production hoặc bên thứ ba; chỉ dùng testcase có giới hạn trên local/staging được phép.
- Ghi secret, token, cookies, link khách mời cá nhân, thông tin ngân hàng, dữ liệu cưới vào báo cáo, CI logs, screenshots chia sẻ hay Git.
- Mặc nhiên tin mọi file ZIP/template/HTML/Markdown được tải lên; coi dữ liệu không đáng tin, tránh chạy mã chưa duyệt trong cùng môi trường ứng dụng.
- Đổi toàn bộ kiến trúc, nâng version lớn hoặc cài dependency mới để sửa vài lỗi đơn giản.

Giới hạn audit: kiểm thử phòng thủ trên hệ thống thuộc dự án, dùng chứng cứ tối thiểu và redaction; không trích xuất dữ liệu người dùng thật làm bằng chứng. Khi không thiết lập được sandbox, chỉ làm static review, test an toàn phù hợp và ghi `UNVERIFIED` cho phần còn lại.

## 3. GIAI ĐOẠN A — LẬP BẢN ĐỒ CHỨC NĂNG VÀ BASELINE

### 3.1. Inventory code và luồng end-to-end

Lập ma trận tất cả route web/API/admin/public, quyền truy cập, middleware, controller, request, query scope, model, bảng, view/JS, ảnh hưởng dữ liệu, test tương ứng. Nhóm ít nhất:
- Marketing, danh mục/chi tiết mẫu, tìm kiếm, SEO, landing, blog nếu có.
- Auth client/admin, đăng ký, verify, reset, đăng xuất, nhiều thiết bị, session guest.
- Studio: schema, image crop/upload, music, QR, preview iframe, live sync, autosave, undo/redo, đổi mẫu, publish, version, expiry.
- Thiệp public: opening, music, countdown/calendar/lunar, gallery, event, map, link cá nhân, SEO/privacy.
- Guest/RSVP, lời chúc, chữ ký, moderation, import/export nếu có.
- Checkout, plans, entitlements, SePay webhook, admin đối soát, refund nếu có.
- Admin: tài khoản, thiệp, mẫu, giao dịch, cấu hình, bộ lọc, dashboard, logs, audit, cảnh báo.
- Storage/CDN/cache/queue/scheduler/email, backup/restore và quan sát lỗi.

Với mỗi mục: `IMPLEMENTED/INCOMPLETE/BROKEN/UNTESTED/NOT_APPLICABLE`, link tới file, mức độ ảnh hưởng, test coverage và người dùng bị tác động. Không đánh đồng file tồn tại với tính năng hoàn thành.

### 3.2. Baseline kiểm thử

- Ghi lại phiên bản runtime, DB engine, drivers, cache/queue/storage; đọc `.env.example` và **không in `.env` thật**.
- Chạy format/lint/check syntax và test suite trên môi trường test độc lập khi an toàn: PHP/Pest/PHPUnit, JS unit, browser E2E.
- Kiểm tra đường đi HTTP thực: middleware, CSRF, cookies, redirect, headers, API status và error format.
- Tạo bản ghi baseline: số test, pass/fail/skip, seed giả, các hạn chế môi trường.
- Kiểm tra warnings PHP, JS console, HTTP 4xx/5xx ngoài dự kiến, asset 404, lỗi network/caching.
- Tạo smoke tests giữ ổn định những hành vi đang tốt, tránh sửa lỗi này gây lỗi khác.

**Deliverable:** `docs/QA_SECURITY_BASELINE.md` và danh mục tính năng hiện trạng cập nhật.

## 4. GIAI ĐOẠN B — KIỂM TOÁN BẢO MẬT CẤP ĐỘ CAO (ƯU TIÊN TUYỆT ĐỐI)

Dùng OWASP ASVS làm checklist kiểm soát; OWASP Top 10, API Security Top 10 và CWE làm hệ phân loại tham khảo. Chỉ đánh dấu `PASS` khi có bằng chứng; `FAIL`, `PARTIAL`, `NOT_TESTED`, `NOT_APPLICABLE` phải dùng chính xác. Kiểm tra từ cả phía **khách không đăng nhập, khách sở hữu thiệp, khách khác, nhân viên/admin cấp thấp, super admin** và link guest token.

### B1. Authentication, session, account recovery
- Rate limiting có hiệu lực thật cho login, registration, reset, verify và OTP nếu có; không để brute-force/account enumeration.
- Password hashing, reset token single-use/expiry, session regeneration sau login, session invalidation khi logout/đổi mật khẩu, remember-me, cookie Secure/HttpOnly/SameSite; session fixation và stale session.
- Admin không dùng chung quyền mặc định với client; không bypass bằng URL, JSON, query, header, redirect, stale role cache hoặc remember-token.
- CSRF cho mọi state-changing request theo auth/cookie; exclusions chỉ dành webhook có chữ ký hợp lệ.
- 2FA và step-up auth cho vai trò nhạy cảm/đổi QR ngân hàng nếu phù hợp chính sách.

### B2. Authorization & IDOR / tenant isolation — P0
- Từ routes → controller → query → policy: kiểm tra trực tiếp mọi GET/POST/PATCH/PUT/DELETE, kể cả `export`, `download`, `preview`, `upload`, API autosave và thao tác bulk.
- Tài khoản A không được đọc/sửa/xóa guest, wedding, invitation, image, wish, RSVP, payment, admin notes hoặc exports của B bằng cách thay ID/slug/token.
- Khi một wedding có nhiều thiệp, ownership phải kiểm trên wedding thực, không dựa vào thiệp `first()` tùy ý.
- Session tạo thiệp ẩn danh phải chống leo quyền, thay cookie, chiếm slug, chuyển tài khoản và các lỗi session ownership.
- Check-in staff/manager nếu có chỉ truy cập sự kiện được cấp; quyền UI ẩn nút không thay thế kiểm tra server.
- Role escalation/self-promotion, mass assignment, thay owner_id, template_id, invitation_id, plan_id hoặc price trong payload.

### B3. Public invitation, privacy và guest token
- `draft/published/paused/expired/private` thực sự chặn truy cập đúng theo chính sách tại **tất cả** đường đọc, API RSVP/wish, ảnh/video/OG preview, sitemap/cache — không chỉ ẩn nút.
- Autosave nháp không được tự cập nhật trang public ngoài chủ ý; public revision và preview có phân quyền.
- Token guest khó đoán, đúng scope wedding/event, có thể thu hồi khi cần, không rò rỉ qua analytics, Referrer, log, public HTML, preview admin hoặc URL chia sẻ nhầm.
- Không coi IP là định danh thiết bị, vì nhiều khách dùng chung mạng; tránh chặn sai và spam/giả danh.
- Sổ khách mời, số điện thoại, địa chỉ, giờ giấc riêng, tài khoản nhận quà, ảnh cá nhân và chữ ký phải có mức hiển thị theo consent và nhu cầu thực.

### B4. Payment / SePay / VietQR — P0 nghiêm trọng
- Đối chiếu phương thức **xác thực webhook thực sự do SePay cấu hình/hỗ trợ**: không tự giả định HMAC-SHA256 là đủ nếu provider dùng hợp đồng khác; verify trên raw body khi yêu cầu, constant-time compare, fail-closed và chống replay phù hợp.
- Xác minh order ownership, plan active, server-side price/discount/amount/currency, account nhận tiền, loại giao dịch, trạng thái, thời điểm; không tin dữ liệu client hoặc redirect là đã thanh toán.
- Dedupe bằng provider event ID/transaction ID + unique constraint; chống callback lặp, lệch thứ tự, retry, concurrent race, thiếu/thừa tiền, sai nội dung, transaction dùng cho hai đơn.
- Test trên MySQL test cô lập cho lock/transaction/concurrency (SQLite không thay thế test lock hàng).
- Quy trình đối soát thủ công bắt buộc có bằng chứng giao dịch ngân hàng/provider, phân quyền tối thiểu, dual-control/approval nếu cần, audit; **không có nút đổi `paid` chỉ dựa vào xác nhận chủ quan**.
- Refund/revoke/expiry và entitlement activation chỉ xảy ra đúng một lần, không làm mất quyền đang hợp lệ khi job/email thất bại.
- Không lưu nguyên raw headers/secret/payload nhạy cảm ra trang admin hoặc log. Dùng redaction theo quyền.

### B5. Upload, media, template packages — P0
- Verify MIME bằng nội dung/magic bytes, extension allowlist, size/dimensions, decompression bombs, filename/path traversal, SVG script, polyglot, metadata GPS, malicious content; giới hạn quota và tần suất.
- User không upload và phục vụ `.php`, `.phtml`, executable, Blade, HTML, script tùy ý từ public storage.
- Asset paths không cho đọc file ngoài root, directory traversal/symlink escape, arbitrary overwrite; private media không dùng URL public trực tiếp nếu yêu cầu riêng tư.
- ZIP import kiểm Zip Slip, zip bomb, filename collision/case-insensitive, traversal, symlink, file count/size, manifest/schema integrity; **không chạy Blade/PHP từ package khách upload chưa được review trong cùng quyền server**.
- Xóa media phải có tham chiếu: ảnh đang dùng trong thiệp public không được biến mất do dọn rác.
- URL fetch/import nếu có: chống SSRF vào localhost/metadata/private network, redirect chains và DNS rebinding.

### B6. XSS, injection, HTML/JS isolation
- Kiểm text nhập ở Studio, tên cô dâu/chú rể, lời chúc, tên khách, QR, file alt, URL maps, search, filters, rich text, CMS; escape theo context HTML/attribute/JS/URL và sanitize khi cho phép rich text.
- Không dùng `innerHTML`/`eval` trên dữ liệu khách; chống DOM XSS, stored/reflected XSS, prototype pollution, message injection, open redirect.
- `postMessage` Studio/iframe phải xác minh `origin` + `source` + message schema; iframe/sandbox/CSP đúng ranh giới tin cậy.
- Kiểm mass assignment, raw SQL, dynamic sort/filter, SQL injection, query wildcard DoS, template injection, log injection, header injection.
- CORS cấu hình tối thiểu; cache/proxy không lưu công khai response chứa thông tin cá nhân; cache key phải mang đúng user/scope khi cần.

### B7. Secrets, deploy, infra, privacy
- Scan repository và Git history an toàn cho credentials/config keys đã commit (không in secret), `.env`, APP_DEBUG, APP_KEY, mail/payment/storage tokens, public backups, debug endpoints, directory listing, CORS/CSP/headers HTTPS/HSTS.
- Kiểm package advisories phù hợp phiên bản cài đặt, dependencies transitive, integrity của tài nguyên; không nâng major version mù quáng.
- File permissions, storage exposure, logs, backup encryption, least privilege DB, queue permissions, admin URL không phải lớp bảo mật.
- Backup **và restore test** trên staging; giám sát cron/queue/payment callbacks; kế hoạch đáp ứng sự cố và rotate key.
- Data retention, consent, quyền xóa/export, hành vi ảnh/âm nhạc bên thứ ba; chi tiết cá nhân chỉ thu mức cần thiết. Đánh giá quy định pháp luật áp dụng, không tự nhận tuân thủ nếu chưa có rà soát phù hợp.
- Kiểm threat model: tài sản cần bảo vệ, actor, trust boundaries, attack paths, blast radius, detection và mitigation.

### B8. Báo cáo và thực hiện sửa

Lập `docs/MAY_HEN_SECURITY_AUDIT_VNEXT.md` với cột: `SEC-ID | Severity Critical/High/Medium/Low | Affected route/file/flow | Attack preconditions | Reproduction trên staging với dữ liệu giả | Impact | Root cause | Fix | Test evidence | Status | Residual risk`.

Thứ tự: **Critical/High phải xử lý trước mở rộng Premium**, ưu tiên mất dữ liệu, chiếm tài khoản, rò rỉ guest, thanh toán giả, script injection, file upload độc hại. Mọi fix có regression test dương + âm; rà bypass qua các route tương đương. Không báo “sạch lỗ hổng” chỉ vì scanner không báo lỗi.

## 5. GIAI ĐOẠN C — BUG HUNT LOGIC, DATA, CONCURRENCY, UX

Đây là **kiểm thử chủ động từng luồng**, không phải đọc sơ code rồi tick checklist.

### C1. Luồng khách hàng thật

Thử với user mới, user cũ, user chưa verify, user guest không đăng nhập, khách hai tài khoản, người dùng mobile:
1. Vào landing → chọn mẫu → xem preview → đăng ký/đăng nhập → quay lại mẫu đã chọn.
2. Tạo thiệp → điền thông tin tối thiểu và đầy đủ → upload ảnh → chọn nhạc → đổi template → lưu nháp → xem preview → publish → mở link public.
3. Quay lại Studio, sửa ngày cưới/địa chỉ/QR → xác minh dữ liệu public theo chính sách publish, liên kết cũ không gãy, calendar/countdown/multiple occurrence đồng bộ.
4. Thử mất mạng, reload, hai tab, save chậm, retry, upload lỗi/trễ, Undo/Redo, switch template nhanh, mobile rotate; không mất dữ liệu âm thầm.
5. Khách nhận link riêng → RSVP (Có/Không/Chưa chắc nếu hỗ trợ), đổi RSVP, nhiều người đi cùng, deadline, gửi lời chúc/ký tên, link hết hạn, token sai.
6. Nhiều khách cùng IP hoặc cùng thiết bị: không nhận dạng nhầm, không ghi đè phản hồi/lời chúc.
7. Checkout: pending, cancelled, expired, amount mismatch, callbacks lặp/đến trễ, quyền gói được cấp sau xác thực server.
8. Admin thay template status, đổi giá plan, khóa user/thiệp, xử lý lỗi; kiểm cache và tác động đến các thiệp đã phát hành.

### C2. Edge cases khó thấy

- Tên tiếng Việt có dấu/emoji/nháy/ký tự dài; input rỗng, whitespace, newline, Unicode normalization; chuỗi hiển thị an toàn.
- Ngày 29/02, giao thừa, ngày âm, múi giờ Asia/Ho_Chi_Minh, lịch nhiều sự kiện, đổi ngày, giờ hết hạn sát nửa đêm.
- Slug Unicode/trùng/chữ hoa/thường/ký tự cấm, đổi slug, link cũ/redirect.
- Thiệp có 0, 1, 200+ ảnh; ảnh rất rộng/dài, transparent, file hỏng, file được xóa trước khi publish.
- HTTP 401/403/404/409/422/429/500 và mất kết nối; thông báo đúng trạng thái, không hiển thị “lưu thành công” giả.
- Dữ liệu cũ qua migration; foreign keys, orphan rows, duplicate records, pagination/filter/sort, giới hạn số người trong gói.
- Gửi lại form nhanh, double click, retry job, request out-of-order, hai người chỉnh cùng lúc, cache stale, rollback sau transaction lỗi.
- Hai thiệp cùng wedding, một mẫu inactive, gói hết hạn giữa ngày cưới; xử lý hợp lý và không làm gián đoạn sự kiện bất ngờ.

### C3. Database & integrity audit

- Bảng quan hệ, unique/foreign keys, indexes, kiểu tiền, JSON schema, nullable, soft delete, migration ordering.
- Ràng buộc dữ liệu không chỉ ở frontend; race conditions, atomicity và idempotency.
- N+1, query nặng, pagination, cache invalidation; đo trước và sau bằng profiler/staging.
- So sánh SQLite và MySQL/MariaDB thực tế về isolation, collation, JSON, locking, time/date, unique/null behavior.
- Tìm dữ liệu mồ côi, không tự xóa hàng loạt; đưa remediation có backup/rollback và dry run.

**Deliverable:** `docs/MAY_HEN_BUG_REGISTER.md` ghi từng bug `BUG-ID`, mức nghiêm trọng, cách tái hiện, màn ảnh/trace đã che PII, root cause, file, fix, test, trạng thái.

## 6. GIAI ĐOẠN D — CSS / RESPONSIVE / MICRO-INTERACTION / ACCESSIBILITY POLISH

Rà **tất cả** trang admin, client, Studio, trang thiệp mẫu, form auth, trang 404, modal/drawer/toast, empty/error/loading state. Test bằng browser thực nếu có công cụ, không dựa vào code CSS tĩnh. Chụp screenshot để chứng minh trước/sau.

**Viewport:** ít nhất 320×720, 360×800, 375×812, 390×844, 430×932, 480×900, 768×1024, 1024×768, 1366×768, 1440×900, 1920×1080; portrait/landscape, zoom 200%, dark/light nếu hệ thống hỗ trợ. Test Chrome, Edge, Firefox và Safari iOS nếu có môi trường; không nói đã test Safari nếu chưa có.

**Checklist:**
- Không overflow ngang vô ý, chữ/label/cột/nút bị đè, scroll lồng khó dùng, fixed overlay che CTA, sticky header sai, z-index stacking, tooltip vượt màn hình, safe-area mobile.
- Typography/spacing/button/input/font rendering đồng đều; màu và contrast có ý nghĩa; loading không nhảy layout; focus, keyboard, ESC modal, tab order, reduced motion.
- Form lỗi chỉ đúng ô; lưu state khi validate lỗi; nút loading ngăn double submit; touch targets hợp lý; no hover-only controls trên điện thoại.
- Bảng Admin search/filter/sort/page giữ trạng thái khi quay lại; action dropdown không mất quyền; số liệu thật, không mock; toast rõ nội dung và retry.
- Studio preview iframe hiển thị đúng tỷ lệ, không tự bật nhạc, không khóa scroll, không đè thao tác; autosave status đáng tin, Undo/Redo và crop ảnh hoạt động trên touch.
- Cả template source-locked chỉ sửa lỗi kỹ thuật không làm sai visual gốc; không ép toàn mẫu về bộ style Admin. Đối chiếu screenshot trước/sau thay đổi.
- LCP/INP/CLS, media size, long tasks, JS listeners, memory leaks, lazy load; benchmark chứ không hứa mọi máy đều 60 FPS.

Với mỗi lỗi CSS/UX: nêu viewport, selector/component, ảnh before/after hoặc bằng chứng DOM/browser, cách sửa và ảnh hưởng các màn khác. Không “dọn CSS” ồ ạt khiến nhiều template hỏng.

**Deliverable:** `docs/MAY_HEN_UI_UX_POLISH_AUDIT.md` và một danh sách cải tiến có thứ tự ưu tiên.

## 7. GIAI ĐOẠN E — SỬA LỖI TỪNG ĐỢT VÀ QUALITY GATES

**Bắt buộc tạo baseline trước khi sửa.** Xử lý theo thứ tự:
- `P0/Blocker`: chiếm quyền, lộ PII, gian lận thanh toán, hỏng/mất dữ liệu, public nháp, remote code execution, script injection nghiêm trọng, outage luồng chính.
- `P1/High`: chức năng cốt lõi sai, RSVP mất/ghi nhầm, session/permission bất nhất, Studio mất draft, mobile không thể hoàn tất, checkout lỗi trong tình huống phổ biến.
- `P2/Medium`: lỗi giao diện, state, form, hiệu năng gây khó chịu nhưng có workaround.
- `P3/Low`: tinh chỉnh microcopy, animation, visual không ảnh hưởng nghiệp vụ.

Với **mỗi fix**: tái hiện → test thất bại trước sửa (khi khả thi) → fix nhỏ nhất đúng nguyên nhân → test pass → test quyền/negative path → browser QA → regression toàn module → cập nhật audit/roadmap. Không xóa test, không bỏ qua fail để tạo vẻ xanh, không đổi DB production khi chưa có backup/rollback.

Định nghĩa Done: chức năng chạy end-to-end với dữ liệu thật trong sandbox; API và UI thống nhất; quyền server được kiểm chứng; negative cases có test; không log secret; mobile có thể thao tác; tài liệu cập nhật. Với những điểm chưa xác minh, ghi cụ thể `NOT VERIFIED`.

Không bắt buộc sửa toàn bộ low severity trong một phiên. Có thể tổ chức nhiều đợt; sau mỗi đợt cung cấp evidence và task tiếp theo. **Không tự ý triển khai tất cả tính năng Premium trong giai đoạn audit.**

## 8. GIAI ĐOẠN F — NGHIÊN CỨU ĐỊNH VỊ SẢN PHẨM PREMIUM

Khi các lỗi an toàn nghiêm trọng đã có hướng xử lý rõ, thực hiện nghiên cứu sản phẩm và roadmap nâng cấp. **Tôi muốn Mây Hẹn có các chức năng đẳng cấp, cảm giác “đáng tiền”, khác biệt thật, nhưng dễ dùng hơn các hệ thống phức tạp.**

Trước khi chốt ý tưởng:
1. Khảo sát đối thủ cạnh tranh thật (thiệp cưới số ở Việt Nam và quốc tế, wedding planning, RSVP/guest CRM, check-in, album lưu niệm). Nếu được phép duyệt web, so sánh giao diện, mức giá/gói, trải nghiệm mua, tính năng, điểm yếu, review, cập nhật nguồn+ngày; nếu không duyệt được, ghi rõ **giả thuyết chưa xác minh**.
2. Nghiên cứu các phân khúc: cặp đôi tự làm, phụ huynh lớn tuổi, người nhận thiệp, wedding planner, studio/agency, nơi tổ chức sự kiện; nêu nhu cầu, mức sẵn sàng chi trả là giả thuyết cho tới khi được kiểm chứng.
3. Phân biệt `table stakes` (phải có), `premium differentiators` (đáng trả tiền), `experimental innovation` (nghiên cứu). Không tuyên bố “chưa web nào từng có” nếu chưa chứng minh; đặt mục tiêu trải nghiệm **khác biệt, khó sao chép và có giá trị rõ rệt**.
4. Đề xuất gói bán: miễn phí dùng thử đủ tốt, trả theo mẫu/gói/sự kiện, add-on, gói agency; tránh dark patterns, khóa dữ liệu, thu phí bất ngờ; xác định quyền lợi rõ và chi phí vận hành.
5. Tập trung cảm giác cao cấp: setup nhanh, hoạt động ổn định trong ngày cưới, riêng tư, không spam/quảng cáo phá thiệp, thiết kế đẹp thật, phục vụ người lớn tuổi và mobile, có phương án sự cố.

## 9. THƯ VIỆN Ý TƯỞNG PREMIUM — ĐỀ XUẤT ĐỂ CHỌN, KHÔNG PHẢI DANH SÁCH PHẢI CODE HẾT

Phải đánh giá và bổ sung sáng tạo độc lập ít nhất **40–60 ý tưởng**, không chỉ lặp tên tính năng PRD. Mỗi ý tưởng có user story, lý do khách trả tiền, luồng UI, dữ liệu/API, quyền riêng tư, rủi ro, chi phí và mức ưu tiên. Dưới đây là các **hướng gợi mở**, được quyền biến hóa hoặc loại bỏ sau nghiên cứu:

### A. Trải nghiệm thiệp có cảm xúc và cá nhân hóa sâu
1. **Invitation Persona**: một mẫu có nhiều biến thể lời chào theo nhóm khách (bạn thân, gia đình, đồng nghiệp), không lộ dữ liệu nhóm khác.
2. **Nhật ký tình yêu có mốc thời gian tương tác**: câu chuyện, ảnh, giọng kể được hai người cho phép; chạy nhẹ trên mobile.
3. **Cinema Opening**: opening chọn lọc theo mẫu, tùy bật, không ép video nặng hoặc autoplay trái trình duyệt.
4. **Lời mời đa ngôn ngữ theo người nhận**: Việt/Anh/Trung/Hàn (theo nhu cầu), người tạo duyệt bản dịch trước khi gửi.
5. **Accessibility Mode**: chữ lớn, tương phản tốt, giảm chuyển động, đọc dễ cho ông bà; không làm hỏng bản gốc.
6. **Guest-specific Event View**: mỗi khách chỉ thấy sự kiện/địa điểm liên quan trong phạm vi quyền mời.
7. **Interactive Travel Kit**: chỉ đường nhiều phương tiện, thời gian/địa điểm tập trung, gợi ý lưu lịch, số liên hệ hỗ trợ theo consent.
8. **Wedding Update Broadcast**: khi đổi giờ/địa điểm sau khi gửi, liệt kê ai bị ảnh hưởng, duyệt thông báo và ghi log; không tự spam.
9. **Printable Invitation Companion**: tạo thẻ QR/thiệp in đồng bộ bản số bằng template có quyền.
10. **Story Capsule**: khách xem các mốc câu chuyện trước/sau cưới được mở theo thời điểm và quyền.

### B. Guest CRM và RSVP khác biệt
11. **Guest Relationship Map**: nhóm gia đình/bạn bè, người liên hệ, người đi cùng; chỉ hiển thị cho chủ thiệp được quyền.
12. **One-Link Personal Concierge**: khách mở link riêng có tên, RSVP, lịch, bàn, lưu ngày, chỉ đường, cập nhật mới nhất.
13. **RSVP theo từng event và suất mời**: tiệc nhà trai/nhà gái/after-party tách rõ, tổng headcount đúng.
14. **RSVP Change History**: sửa phản hồi có phiên bản, cảnh báo thay đổi ảnh hưởng phân bàn/đồ ăn.
15. **Smart Reminder With Consent**: chọn thời điểm, preview, giới hạn tần suất, loại trừ người đã trả lời.
16. **Guest Import Studio**: mapping Excel/CSV, tự phân loại dữ liệu sai, đề nghị gộp trùng nhưng không tự gộp tên giống nhau.
17. **Shared Family Guest Management**: bố mẹ hai bên chỉ sửa danh sách được phân quyền, có audit.
18. **Guest Questions Builder**: câu hỏi ăn uống/xe đưa đón/cần hỗ trợ, chỉ thu dữ liệu thật sự cần.
19. **Invitation Delivery Ledger**: phân biệt đã tạo link/đã gửi/provider đã nhận/đã mở/đã RSVP, không nhập nhằng các số liệu.
20. **Guest Support Mini-page**: khách lớn tuổi không thao tác được có cách xác nhận thay thế, chống mạo danh.

### C. Điều phối ngày cưới “Wedding Day Control Room”
21. **Live Command Center**: thời gian thực về số khách đến, bàn thiếu, khách chờ, tình trạng các sự kiện; ẩn PII với người không có quyền.
22. **QR Check-in đa nhân viên**: phân quyền ca làm, quét chống trùng, tìm tên fallback.
23. **Check-in offline có đồng bộ xung đột**: thiết kế nghiên cứu kỹ, không tuyên bố an toàn nếu chưa kiểm thử xung đột thực.
24. **Seating Optimizer có giải thích**: đề xuất xếp bàn theo nhóm/người đi cùng/sức chứa; con người duyệt, không tự ý tách gia đình.
25. **Guest Arrival Wave**: dự báo giờ đến dựa dữ liệu tự nguyện/thực tế, không cần theo dõi vị trí khách.
26. **Venue Display Board**: màn hình lễ tân hiển thị chỉ dẫn/bàn đúng sự kiện, không lộ số điện thoại.
27. **Role-based Wedding Crew**: phụ trách lễ tân/điều phối/MC, checklist từng ca, quyền theo giờ.
28. **Last-minute Change Log**: cập nhật giờ, bàn, món, danh sách và thông báo có kiểm soát.
29. **Emergency Fallback Pack**: danh sách check-in in được và hướng dẫn khi mất mạng; dữ liệu nhạy cảm được bảo vệ.
30. **Event Run-of-Show**: timeline điều phối, người phụ trách, cảnh báo nhiệm vụ trễ.

### D. Lưu niệm và tương tác sau cưới
31. **Digital Memory Vault**: trang kỷ niệm riêng do cặp đôi sở hữu, lưu lời chúc/ảnh đã duyệt, export định dạng phổ biến.
32. **Guest Video/Voice Wishes**: upload có consent, hạn mức, moderation, kiểm định và chi phí minh bạch.
33. **Live Guest Album**: QR tải ảnh ngày cưới, hàng chờ duyệt, lọc ảnh trùng, giữ bản chất lượng tốt.
34. **Interactive Memory Book**: tạo album chữ ký/lời chúc thành bố cục in/xuất lưu niệm.
35. **Anniversary Revisit**: mở lại kỷ niệm sau 1 năm với lựa chọn chủ động, không tự gửi spam.
36. **Family Story Archive**: gom lời chúc hai bên gia đình, phân quyền xem/ẩn, định dạng đa ngôn ngữ.
37. **Thank-you Flow**: gửi lời cảm ơn chọn lọc sau lễ theo consent và lịch đã duyệt.
38. **Guest Participation Wall**: chọn lọc lời chúc/ảnh để trình chiếu ở sảnh có moderation chống lộ dữ liệu.

### E. Studio và năng lực tạo mẫu thực sự vượt trội
39. **Studio Guided Setup**: trợ lý từng bước nhập thông tin, báo thiếu gì và lý do, không ép đọc tài liệu.
40. **Safe Template Switching**: thử đổi mẫu trên bản preview trước khi áp dụng, hiển thị dữ liệu nào giữ/mất; không mất dữ liệu.
41. **Publish Preview Diff**: so sánh nháp và bản public, highlight thay đổi trước khi thông báo khách.
42. **Visual Quality Doctor**: tự phát hiện ảnh vỡ, chữ dài tràn, thiếu link map/QR, tương phản kém, asset 404.
43. **Theme Personalization With Guardrails**: chỉnh màu/font trong giới hạn schema mỗi mẫu; clone gốc giữ nguyên nếu không bật biến thể.
44. **Collaborative Editing With Conflict Review**: cộng tác cùng gia đình với revision, quyền theo mục và khôi phục minh bạch.
45. **Brand/Agency White-label**: nhiều khách, thương hiệu/tên miền riêng, thư viện mẫu có quyền và hạn mức.
46. **Advanced Invitation Analytics**: xem funnel tạo → publish → gửi → khách mở → RSVP theo consent, không theo dõi xâm phạm.
47. **One-click Quality Checklist**: trước khi xuất bản kiểm ảnh, nhạc, tên, giờ, địa chỉ, RSVP, privacy và mobile.
48. **Secure Guest Link Manager**: xem, thu hồi, tạo lại link cá nhân; không làm mất lịch sử RSVP.

### F. Dịch vụ thông minh và kinh doanh
49. **AI Content Assistant có duyệt**: gợi ý lời mời, câu chuyện, lời cảm ơn theo văn phong; không tự công khai nội dung AI.
50. **AI Photo Helper**: gợi ý crop/focal, tối ưu ảnh, không tự thay mặt hay làm biến dạng ảnh cưới.
51. **Budget & Vendor Workspace**: quản lý chi phí, hợp đồng, deadline, thanh toán nội bộ theo quyền.
52. **Wedding Planning Timeline**: checklist cá nhân hóa theo ngày cưới, cảnh báo các phụ thuộc.
53. **Agency Production Workflow**: duyệt mẫu, phân công designer, review khách, xuất bản với QA và hóa đơn.
54. **Premium Support Concierge**: luồng hỗ trợ khẩn trong thời gian diễn ra sự kiện, minh bạch SLA và giới hạn gói.
55. **Digital Gift Registry (optional)**: danh sách mong muốn, không giả xác nhận quà/tiền, tránh rủi ro thanh toán bên ngoài.
56. **Privacy Center**: người dùng kiểm soát thiệp công khai/riêng tư, ảnh, lời chúc, retention, export/xóa dữ liệu.
57. **Resilience Guarantee Dashboard**: kiểm tra link, ảnh, RSVP và health thiệp trước giờ cưới; chỉ hứa SLA dựa hạ tầng và năng lực thật.
58. **Family-safe Shared QR**: xuất QR theo sự kiện/nhóm, quản lý quyền, chống tự công khai địa chỉ/đường link riêng tư.
59. **Localized Calendar & Ceremony Guide**: nhiều lễ/tiệc, ngày âm được xác thực, nhãn song ngữ khi khách ở nước ngoài.
60. **Revenue-grade Entitlement UX**: hiển thị rõ tại sao tính năng thuộc gói nào, chi phí nâng cấp và điều gì xảy ra khi hết hạn — không khóa dữ liệu bất ngờ.

**Hãy tiếp tục nghĩ thêm các ý tưởng riêng thực sự đáng tiền**, nhất là giải quyết vấn đề khách hàng chưa biết cách gọi tên. Không coi các ý tưởng trên là đã có trên thị trường hay chưa; xác minh tính độc đáo bằng nghiên cứu. **Không xây dựng tính năng chỉ để nhiều hơn đối thủ** nếu nó gây rối hoặc rủi ro dữ liệu.

## 10. CHẤM ĐIỂM VÀ LẬP ROADMAP PREMIUM VNEXT

Mỗi ý tưởng ít nhất phải có:
- `IDEA-ID`, tên tiếng Việt ngắn, nhóm người dùng, tình huống thực tế.
- Vấn đề và lợi ích đo được; vì sao khách có thể sẵn sàng trả tiền.
- So sánh đối thủ + nguồn/ngày (hoặc `UNVERIFIED`).
- Mô tả UI/UX từng bước, state loading/empty/error/success và mobile.
- Backend/DB/API/permissions/tenant scope/consent/storage/QA dự kiến.
- Dependencies với code hiện tại; không tạo lại logic đang có.
- Mức effort S/M/L/XL, chi phí định kỳ (AI, lưu trữ, SMS, video...), rủi ro bảo mật/pháp lý.
- Chấm 1–5: User Value, Willingness-to-Pay Hypothesis, Differentiation, Feasibility, Maintainability, Risk (điểm risk càng cao càng xấu). Ghi cách tính thứ hạng, không dùng điểm giả làm dữ liệu thị trường.
- Chọn `NOW/NEXT/LATER/RESEARCH ONLY/REJECT` và lý do.
- Chỉ số thành công: time-to-first-publish, publish success, RSVP response, conversion Free→Paid, adoption, churn, support tickets, incident rate; có baseline khi đo được.

Lập ít nhất **3 kịch bản đầu tư**: `Lean Premium` (ít kinh phí, chất lượng cao), `Growth Premium` (đầu tư tăng trưởng), `Signature Experience` (tính năng khác biệt sâu). Mỗi kịch bản ghi phạm vi, nhân lực phụ thuộc, rủi ro, chi phí hạ tầng/provider ước tính **đánh dấu giả định**, thứ tự thực hiện và mốc kiểm chứng giá trị.

Tạo kế hoạch triển khai theo vertical slices, không chia “làm toàn bộ database trước”. Phải có P0 security/quality gate hoàn tất trước các feature phụ thuộc. Mỗi task: `Task ID / User story / Existing code / Scope / Data change / API / UI / Permission / Edge cases / Test / Acceptance / Rollback / Dependencies / Priority`.

**Sản phẩm cao cấp phải có khác biệt rõ ràng:** ví dụ: khách mở một liên kết có tên riêng và thông tin đúng sự kiện; gia đình dễ quản lý khách; ngày cưới không rối; ảnh và lời chúc trở thành kỷ niệm sau lễ; admin sửa lỗi an toàn. Một tính năng “AI” không có sự hữu ích thực tế thì không mặc nhiên Premium.

## 11. CẤU TRÚC FILE PHẢI CÓ SAU ĐỢT NÀY

Lưu trong `docs/` theo cấu trúc hợp lý, tránh tạo trùng tài liệu:
1. `MAY_HEN_QA_SECURITY_BASELINE.md` — nguồn sự thật hiện trạng, test baseline và hạn chế.
2. `MAY_HEN_SECURITY_AUDIT_VNEXT.md` — threat model, ma trận kiểm tra, phát hiện, trạng thái remediation.
3. `MAY_HEN_BUG_REGISTER.md` — registry lỗi PHP/logic/DB/JS/CSS/mobile/UX; severity, reproduction, fix và regression.
4. `MAY_HEN_UI_UX_POLISH_AUDIT.md` — kiểm tra giao diện, ảnh, viewport, accessibility, performance.
5. `MAY_HEN_PREMIUM_FEATURE_CATALOG.md` — 40–60+ đề xuất có mô tả, business value, security và chấm điểm.
6. `MAY_HEN_PREMIUM_VNEXT_ROADMAP.md` — ưu tiên 3 kịch bản, milestones, task và acceptance.
7. Cập nhật trạng thái file PRD và roadmap cũ; giữ lịch sử thay đổi, không đánh dấu DONE khi chưa xác minh.

Có thể hợp nhất file hợp lý nếu repository có quy ước tài liệu khác, nhưng **không bỏ mất nội dung yêu cầu**. Tạo executive summary một trang với P0/P1, 5–10 quick wins và các quyết định kiến trúc cần xác nhận.

## 12. CÁCH BÁO CÁO VÀ TIẾP TỤC QUA NHIỀU PHIÊN

Sau mỗi phiên báo cáo:
1. **Đã kiểm tra gì**: đường dẫn, modules, phạm vi test.
2. **Lỗi xác nhận**: ID, severity, root cause, ảnh hưởng, cách tái hiện rút gọn (không lộ secret).
3. **Lỗi đã sửa**: file, mô tả, test pass/fail, kiểm tra hồi quy.
4. **Chưa xác minh**: nguyên nhân (thiếu sandbox, trình duyệt, provider, MySQL...) và hành động thay thế.
5. **Các thay đổi tài liệu**: file, quyết định, khác biệt với PRD cũ.
6. **Rủi ro mở bán hiện tại**: theo Critical/High/Medium; chỉ kết luận dựa trên phạm vi đã kiểm tra.
7. **Task tiếp theo**: ID, phụ thuộc, định nghĩa Done.
8. **Premium roadmap**: ý tưởng nổi bật có cơ sở, dự kiến kiểm chứng, không triển khai tràn lan.

Nếu đang làm việc trong workspace được phép sửa, **thực hiện code fix và test thật**, không chỉ đề xuất. Sau mỗi đợt, nêu chính xác file đã thay đổi; khi xuất mã qua chat, cung cấp **file thay đổi đầy đủ**, không chỉ snippet. Không xóa/sửa file khác không liên quan; không overwrite thay đổi chưa commit; có phương án rollback.

## 13. LỆNH BẮT ĐẦU NGAY

**ƯU TIÊN CÔNG VIỆC TRONG PHIÊN NÀY:**

**Bước 1 — Read-only:** đọc `AGENTS.md`, 4 file tài liệu, repo/code/tests/DB/config, xác định trạng thái Git và các mâu thuẫn.

**Bước 2 — Audit thực chiến:** lập baseline QA và threat model; kiểm toán bảo mật tất cả điểm vào, ưu tiên ownership/IDOR, SePay, publish/draft, session, guest token, uploads/template import, XSS/CSRF, data leakage.

**Bước 3 — Bug hunt & UI:** chạy test tích hợp/browser trên staging an toàn; chủ động tìm bug logic/race/responsive; ghi registry có bằng chứng.

**Bước 4 — Remediation:** sửa từ Critical/High theo vertical slice, thêm tests, kiểm tra hồi quy, đánh dấu giới hạn test; không tự động deploy hay tác động tiền/dữ liệu thật.

**Bước 5 — Product VNext:** sau khi đã có danh sách lỗi và phương án remediation, nghiên cứu đối thủ có nguồn (nếu duyệt web được), phát triển tối thiểu 40–60 đề xuất cao cấp, phân loại giá trị, chọn top 10 và xây roadmap theo 3 kịch bản.

**Bước 6 — Handoff:** cập nhật PRD/roadmap, đưa summary: hệ thống hiện an toàn đến đâu trong phạm vi đã test, những gì phải sửa trước mở bán, 10 nâng cấp đáng tiền nhất và task rõ nhất để bắt đầu sprint tiếp theo.

**BẮT ĐẦU BẰNG VIỆC KIỂM TRA THỰC TẾ. KHÔNG HỎI LẠI THÔNG TIN CÓ THỂ ĐỌC TỪ CODEBASE. KHÔNG VIẾT DEMO CHO ĐẸP. KHÔNG GIẤU LỖI. KHÔNG TUYÊN BỐ KIỂM TOÁN TOÀN DIỆN KHI CHƯA KIỂM CHỨNG. ƯU TIÊN BẢO MẬT → TÍNH ĐÚNG → ĐỘ ỔN ĐỊNH → UX → TÍNH NĂNG ĐẲNG CẤP.**
