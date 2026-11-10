# MÂY HẸN — ADMIN VNEXT / ATELIER COMMAND CENTER
## MASTER PROMPT: KIỂM TOÁN 100% ADMIN HIỆN CÓ → THIẾT KẾ LẠI UX CÓ CƠ SỞ → HOÀN THIỆN CHỨC NĂNG VI MÔ → NÂNG CẤP THÔNG MINH → KIỂM THỬ THẬT

> **Workspace của agent:** `E:\Project\Laravel\mayhen.vn`.
> **Vai trò:** Principal Product Architect + Staff Laravel Engineer + Head of Operations + Staff UX/UI Designer + Security Engineer + Business Logic QA + Data/Finance Auditor.
> **Không cài bộ UI, framework, SDK, scanner hoặc công cụ ngoài mới.** Ưu tiên Laravel/Pest/JS/Blade/native và phương tiện kiểm thử **đã tồn tại trong repository/môi trường**. Không có browser thì phải báo rõ `NOT_RUN`, không nói đã xem giao diện.
> **Tuyên ngôn:** Admin không phải CRUD đẹp mắt. Admin là *buồng điều hành tin cậy*: biết có chuyện gì, vì sao, ai chịu trách nhiệm, làm gì tiếp, điều gì sẽ thay đổi, đã hoàn tất thật chưa.

---

# 0. NHIỆM VỤ, PHẠM VI, THỨ TỰ ƯU TIÊN

Người dùng không yêu cầu một prototype. **Hãy kiểm tra, hoàn thiện, refactor và mở rộng `/admin` trên mã nguồn Mây Hẹn hiện có**. Luôn bắt đầu bằng phát hiện trạng thái thực tế, sửa lỗi vận hành/bảo mật trước, rồi triển khai dần các cải tiến được chấp thuận về nghiệp vụ hoặc có thể triển khai an toàn. Đừng cố viết hàng trăm màn mới ngay trong một phiên. Làm một lát cắt nghiệp vụ hoàn chỉnh và được kiểm thử trước khi chuyển lát cắt khác.

1. **VERIFY** — chứng minh chức năng/route/permission/UI thật sự có, thật sự hoạt động; trạng thái `DONE` trong Markdown không phải bằng chứng.
2. **PROTECT** — xử lý P0/P1: truy cập trái quyền, thao tác tiền, xóa nhầm dữ liệu, rò rỉ PII, sai lệch số liệu, không khớp trạng thái, đứt quy trình.
3. **POLISH** — sửa hàng trăm lỗi nhỏ UX, layout, keyboard, modal, empty/loading/error states, thông điệp và hiệu năng.
4. **COMPLETE** — bổ sung từng chức năng nền tảng bị thiếu, kết nối thực chất với backend, các module Client/Studio/Partner.
5. **DIFFERENTIATE** — nâng cấp nghiệp vụ độc đáo, có lợi ích rõ và bằng chứng hiệu quả; đừng tạo chức năng AI mang tính trình diễn.
6. **PROVE** — mỗi case có ID, route thực, vai trò, dữ liệu test, thao tác, kết quả mong đợi, kết quả thực, log/test/screenshot và hash commit hoặc thời điểm; **không đồng loạt chuyển CSV sang PASS**.

Không tự khẳng định sản phẩm “không thể còn lỗi”, “độc nhất thế giới”, “an toàn 100%” hay “production-ready” khi chưa có kiểm định đầy đủ. Không khẳng định các tính năng chưa từng tồn tại ở sản phẩm khác; đánh dấu **ý tưởng khác biệt cần nghiên cứu**, thiết kế và kiểm chứng với người dùng.

# 1. ĐỌC TÀI LIỆU VÀ LUẬT CỦA REPOSITORY TRƯỚC KHI SỬA

Thứ tự bắt buộc:

1. `context.gemini.md` **đầu tiên** theo luật dự án. Soát phần mới nhất, phân biệt ghi chú lịch sử với trạng thái mới.
2. `AGENTS.md`, `CLAUDE.md` nếu có; `.ai/rules/index.md` và mọi rule áp dụng nếu tồn tại.
3. `.agents/skills/mayhen-admin/SKILL.md`, `mayhen-auth-security`, `mayhen-database`, `mayhen-laravel-backend`, `mayhen-blade`, `mayhen-css`, `mayhen-javascript`, `mayhen-ui-ux`, `mayhen-brand-design`, `mayhen-testing-quality`, `mayhen-code-review`, `mayhen-payments`; các skill theo chức năng tương ứng. Xung đột skill phải ưu tiên luật project `AGENTS.md` và stack thuần.
4. `docs/ADMIN_ARCHITECTURE_ROADMAP.md` — danh sách 9 phân hệ lịch sử; **không tin trạng thái DONE nếu chưa đối chiếu hiện trạng**.
5. `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`, đặc biệt Phần II (sitemap/UI), III (Admin), VII (screen contracts), IX/X (nghiệm thu/permission).
6. Các roadmap cập nhật, `docs/qa/*`, security audit, bug register và các tệp Admin VNext kèm prompt này.
7. Code sống: `routes/admin.php`, `bootstrap/app.php` (hoặc cách đăng ký route thật), `app/Http/Controllers/Admin/`, Policies, Middleware, Requests, Services, Eloquent/models/migrations, `resources/views/admin/`, `public/assets/admin/`, test hiện tại, queue/config.
8. `git status`, khác biệt chưa commit, lockfiles; giữ nguyên thay đổi người khác. Không ghi đè tùy tiện.

**Đối chiếu nguồn:** Mỗi yêu cầu trong catalog được gắn `SOURCE_EXISTING_SPEC` hay `PROPOSAL_VNEXT`. Trong repo có thể đã tồn tại chức năng tương đương dưới tên khác. Kiểm tra và **reuse**; không tạo controller/menu/table mới chỉ vì tên trong catalog không trùng code.

# 2. QUY TẮC KỸ THUẬT BẮT BUỘC

- PHP/Laravel/Pest theo phiên bản **đã cài thực tế** (không đoán). Theo convention đang dùng. Code kiểm thử nằm trong `tests/Feature/` hoặc `tests/Unit/` theo repo, không xóa test hiện có để có màu xanh.
- Frontend **Blade + HTML/CSS/JavaScript thuần, Native ES Modules**, không Bootstrap, Tailwind, React/Vue/Angular, jQuery, GSAP, Alpine, thư viện data table mới, CDN. Đừng cài thêm dependency nếu chưa được người dùng cho phép.
- Asset admin tách riêng trong `public/assets/admin/`, lớp CSS `mayhen_admin_*`; riêng login `mayhen_admin_login_*`. Không áp CSS client vào admin hoặc ngược lại. Giữ token `--mayhen-admin-*`.
- Dùng Laravel FormRequest, Policy/Gate và quyền tại server. **Ẩn nút chỉ là UX, không phải bảo mật.** Tách read/query và write/action rõ trách nhiệm nếu codebase đã có convention.
- Chỉ query đủ cột, eager load, phân trang phía server, tìm kiếm/sort/filter có whitelist, index có căn cứ. Không tải toàn bộ users/invitations/payments để lọc trên JS; không cache để che query chậm.
- Hiển thị timestamp `Asia/Ho_Chi_Minh`, lưu UTC hoặc theo chuẩn DB hiện hành, timezone phải nhất quán; format VNĐ, dấu phân cách số, âm/dương, refund và báo cáo tài chính đúng.
- Không được lấy hình ảnh/đoạn văn dữ liệu khách có thật làm fixture demo. Không ghi log token, password, PIN, full banking, địa chỉ/số điện thoại đầy đủ.
- Thay đổi file qua công cụ edit trực tiếp, hiển thị diff theo `AGENTS.md`. Sau code cập nhật `context.gemini.md`, đưa commit message tiếng Việt có dấu; **không tự commit, push, deploy**.
- Mọi thay đổi production, reset dữ liệu thật, xóa dữ liệu hàng loạt, gửi email/SMS/Zalo thật, hoàn tiền thật, thu thập dữ liệu nhạy cảm và xoay API keys đều yêu cầu phê duyệt và môi trường an toàn.

# 3. GATE 0 — CHỤP HIỆN TRẠNG VÀ TRUY VẾT TỚI TỪNG NÚT

**Không code vội.** Lập bản đồ và điền catalog CSV theo code hiện tại:

- Liệt kê route admin thật, tên route, HTTP method, middleware, permission, action, params, request schema, response, page view, JS event, bảng/model, service, test hiện có.
- Từ tất cả Blade, partial, JS: kiểm kê mọi nút, link, dropdown, checkbox, tab, form, input, modal, drawer, menu context, batch toolbar, table column, filter, pagination, snackbar/toast và shortcut, gồm phần ẩn/conditional.
- Với **mỗi control**: nhấn làm gì, có endpoint không, ai được phép, có loading không, double click thì sao, backend 422/403/404/409/429/500 thì UI ra sao, F5 giữ trạng thái thế nào, có log/audit không, có ảnh hưởng module khác không.
- Kiểm tra tính nhất quán `DB → Service → Controller → Blade/API → UI → hành vi thực → DB sau thao tác`; tạo link tới test và evidence. Nếu control “nhìn có vẻ hoạt động” nhưng chỉ thay DOM, giữ là **BUG**, không gán PASS.
- Trạng thái chuẩn: `NOT_FOUND`, `DOC_ONLY`, `IMPLEMENTED_UNVERIFIED`, `TEST_FAILED`, `BLOCKED`, `VERIFIED_PASS`, `NOT_APPLICABLE`, `PROPOSAL`. Không biến đề xuất thành tính năng đã có.
- Kiểm toán nguồn dữ liệu, state machine, quyền, tác động dây chuyền, thuật ngữ. **Không tạo menu rỗng** cho phần chưa triển khai.

**Deliverable đầu tiên:** `docs/admin-vnext/01_RUNTIME_ADMIN_INVENTORY.md`, cập nhật CSV, bảng route→UI→API→DB→Permission→Test→Evidence. Danh sách lỗ hổng P0/P1 cần fix ngay.

# 4. ADMIN UX — DESIGN SYSTEM HIỆN ĐẠI, TINH TẾ, DỄ DÙNG

Triết lý: **Quiet Luxury for Work** — tinh tế của Mây Hẹn nhưng ít trang trí hơn marketing; giống bàn làm việc của người vận hành chuyên nghiệp, không phải một thiệp cưới phóng to. Ưu tiên mật độ thông tin hợp lý, phân cấp thị giác, không lạm dụng vàng, blur, shadow, card, animation, icon.

## 4.1. Khung shell chính

- Sidebar trái có nhóm nghiệp vụ, thu gọn/mở rộng, icon đồng nhất, tìm menu, giữ nhóm mở, trạng thái route chính xác, hiển thị quyền tương ứng. Không ẩn route cần thiết trong nhiều cấp menu. Badge chỉ khi có việc cần làm; số badge cập nhật nguồn thật.
- Topbar gồm breadcrumb nhiều tầng, ô tìm kiếm toàn hệ thống, command palette (nếu triển khai), bộ lọc workspace/tenant khi có quyền, thông báo công việc và menu hồ sơ. Search phải hiển thị scope, gợi ý theo quyền và giải thích khi không thấy dữ liệu.
- Content có tiêu đề rõ, mô tả 1 dòng, **một CTA chính**. Hành động phụ nằm trong toolbar/menu có nhãn rõ; thao tác nguy hiểm tách biệt.
- Panel chi tiết nhanh/drawer giữ filter, trang và scroll. Các modal chỉ dùng cho confirm hoặc tác vụ ngắn; quy trình dài dùng page/wizard.
- Có hotkeys có thể khám phá và tắt; không kích hoạt khi focus input/contenteditable, tránh xung đột trình duyệt, tương thích keyboard và screen-reader.
- Có global notice nhỏ cho lỗi hệ thống thực sự quan trọng; không có notification fatigue. Mọi badge/cảnh báo đi tới đối tượng cần xử lý.
- Một record phải có ID stable, link sâu, nút copy, thời điểm update, người phụ trách, trạng thái và lịch sử tối thiểu theo quyền.

## 4.2. Chuẩn bảng dữ liệu dùng chung

Áp dụng **đồng nhất** cho Users, Invitations, Templates, Orders, Payments, Wishes, Tickets, Jobs, Reports, Agencies, Events, Content, Logs:

- Keyword search có debounce, enter/search-clear, query URL hợp lệ, giữ filter sau F5/back-forward, phân biệt không có dữ liệu và không có kết quả; trường được tìm kiếm phải được ghi rõ.
- Multi-filter status/date/owner/tier/role/channel; hiển thị chip filter, Clear All, preset cá nhân, shortcut "của tôi", "cần xử lý", "hôm nay" khi hợp ngữ cảnh.
- Sort chỉ whitelist, thứ tự ổn định, tie-break theo ID, giữ sort khi paginate, reset page hợp lý khi filter đổi.
- Chọn cột/ẩn-hiện/đổi thứ tự/resize/pin, lưu preset theo admin; không để bảng lệch header/body hoặc sticky che nút. Bảng desktop mật độ vừa, mobile card/column priority; scroll ngang chỉ khi có lý do.
- Pagination server-side 10/20/50/100; stable selection khi thay page rõ ràng, select all page vs all filtered phải phân biệt; không áp bulk bằng index mảng.
- Hành động hàng chỉ hiển thị khi quyền và trạng thái cho phép, backend kiểm lại; disabled giải thích lý do qua tooltip/help text.
- Export current page / filtered set / selected rows; CSV UTF-8 BOM, chống formula injection, ẩn PII theo quyền, large export theo job. Không báo file tải xong trước khi thực sự sẵn sàng.
- Loading skeleton có chiều cao ổn định; không mất filter khi API lỗi; error with retry, request ID; empty state đúng ngữ cảnh; success toast kèm next action; validation gắn đúng field; undo chỉ khi backend hỗ trợ và còn an toàn.
- Số liệu bảng và KPI cùng nguồn logic; khi thao tác thay đổi trạng thái phải cập nhật counters đúng, không stale.

## 4.3. Chuẩn form và detail

- Input label hiện rõ, required/optional, help text, nhập có dấu và không dấu, Vietnamese Unicode, số dài, số tiền, ngày/giờ, timezone, thông báo lỗi gần ô nhập, summary lỗi.
- Save vs Save Draft vs Publish vs Approve vs Archive phải phân biệt rõ hậu quả. Dirty state cảnh báo rời trang; optimistic concurrency hoặc server lock; lỗi 409 có diff/restore, không ghi đè âm thầm.
- Password/secret masked; ô trống secret không tự xóa config cũ. Trường PII masked mặc định với quyền reveal có giải thích, timeout/audit khi hợp lý.
- Delete có xác nhận theo mức độ, quan hệ liên quan, soft-delete/restore theo chính sách; không cho delete xóa nhầm đơn đã đối soát hoặc phá bản ghi tài chính.
- Người dùng nhận message: hành động gì thành công, tác động tới bao nhiêu record, có thể undo hay không, bước tiếp theo; không chỉ toast "Thành công!".
- Trước khi lưu thay đổi tác động lớn, có **before/after diff + impact preview** (số thiệp, khách, giao dịch bị ảnh hưởng) và ghi lại lý do.

## 4.4. Responsive/accessible/visual QA

Test ít nhất 320, 360, 375, 390, 430, 768, 1024, 1366, 1440, 1920 px; chiều cao laptop thấp, iOS Safari/Android Chrome nếu công cụ/hardware có sẵn. Test zoom 200%, bàn phím, focus order, Escape, screen reader khi khả thi, prefers-reduced-motion, loading lớn, text cực dài, tên Việt, currency âm, bảng trống và bảng 10.000 dòng.

Chụp full page ở trạng thái **đã đăng nhập**, có dữ liệu test hợp lệ, side panel mở, filter đang áp, modal, validation, error, success; ghi **screenshot pair trước/sau**. Không chấp nhận ảnh login redirect làm bằng chứng màn nội bộ; không chấp nhận ảnh ở giữa animation làm bằng chứng giao diện hoàn thành. Không viết `VISUAL_PASS` nếu chưa mở browser; chỉ code scan thì ghi `CODE_REVIEW_ONLY`.

## 4.5. Ưu tiên thẩm mỹ cụ thể

- Typography rõ, đậm/nhạt có trật tự; không trộn 4–5 font. Body 14–16px phù hợp mật độ admin, đảm bảo đọc ở 200% zoom.
- Nền ivory/warm cream tiết chế; charcoal cho text; muted gold làm điểm nhấn hành động chính, không dùng cho mọi status. Semantic error/success/warning cần tương phản, text label đi kèm.
- Khoảng trắng theo rhythm (4/8/12/16/24/32), căn thẳng cột, không ép table KPI thành card quá cao; bảng có hover/focus tinh tế, animation ngắn và hữu ích.
- Mobile admin ưu tiên xử lý ticket, xem cảnh báo, kiểm đơn, tìm user, duyệt nội dung; không cố nhồi bảng desktop 20 cột vào 375px.

# 5. DANH MỤC PHÂN HỆ ADMIN — BẮT BUỘC KIỂM TRA VÀ HOÀN THIỆN

> Danh sách này đi cùng `MAY_HEN_ADMIN_ATOMIC_FUNCTIONS.csv` và `MAY_HEN_ADMIN_TEST_CASES.csv`. **Không dùng các nhóm dưới đây làm lý do tạo hết menu mới ngay lập tức**: tính năng chưa có ở repo là backlog cần duyệt về tác động và ưu tiên, không phải BUG nếu chưa có contract bắt buộc.

## A01 — Xác thực Admin, phiên, thiết bị và bảo vệ đăng nhập

Login/Logout, trạng thái user locked/disabled, role guard server-side, CSRF, session rotation, remember-me nếu có chính sách, invalid credentials/empty/Unicode, rate-limit, lockout có thời gian, reset password, expiry session, cookie flags, concurrent sessions, revoke session, sign-in history, đổi password, auth redirect, deep-link after login, root `/admin`, giới hạn quyền cả Blade và API, admin login responsive và keyboard. Bổ sung 2FA/TOTP hoặc passkey **chỉ sau khi đánh giá kiến trúc/phê duyệt**, có recovery và step-up cho thao tác tiền/secret. Không cho support đặt lại mật khẩu admin cấp cao.

## A02 — Khung Admin, sidebar, topbar, notification, tìm kiếm tổng, hồ sơ

Nav theo quyền, breadcrumb deep links, sidebar compact, theme token, dark mode tùy chọn nếu thiết kế đạt, command menu với keyboard, switch role/scope, recent items, thông báo chưa đọc/đã đọc, assign, snooze chính xác, badge từ DB, profile, timezone, user preferences, persist filter/column layout, inline help; không được có thông báo rỗng giả.

## A03 — Dashboard điều hành và Action Inbox

Doanh thu paid, net sau refund, orders pending/disputed, số cặp đôi mới, drafts/published/locked/expired, mẫu chờ QA, ticket quá SLA, job fail, SePay webhook unmatched, media broken, storage pressure, RSVP aggregate privacy-safe, top template conversion, funnel drop-off. Có kỳ hiện tại/so kỳ, timezone, freshness và click drilldown từ card. **Action Inbox** nhóm theo P0/P1, người phụ trách, deadline, next action và kết quả. Không phóng đại MRR nếu không có subscription thật.

## A04 — Tài khoản khách, danh tính, quyền dữ liệu

Tìm và lọc user, profile, verify email, contacts masked, ban/unban với lý do, resend activation, revoke sessions, signup source, projects/thiệp/orders/tickets, quotas, consent, export quyền riêng tư, download data, yêu cầu xóa đúng vòng đời. Sửa role bằng quyền riêng, kiểm tra 'last super admin', ticket hỗ trợ không tạo quyền xem PII tự do. Không bao giờ email mật khẩu plaintext.

## A05 — RBAC, nhân sự nội bộ, quyền theo hành động

Role thực trong repo (admin/manager/staff/partner/customer) không tự động biến thành role mới; map permission granular: view_users, manage_roles, view_payment_masked, reconcile_payment, refund, edit_template, publish_template, lock_invitation, view_guest_pii, export_pii, edit_settings, rotate_secret, approve_critical. Bảng quyền, test cross-role GET/POST, policy ở server và per-object/tenant, không tự hạ quyền last admin, lịch sử permission, 2-person review ở thao tác cực nhạy cảm (đề xuất).

## A06 — Workspace, dự án cưới, thiệp & quyền sở hữu

Tìm thiệp theo tên cặp đôi/slug/user/ID, trạng thái nháp/public/locked/expired/protected, template/version, thời gian cưới, venue, domain, tiến độ và thống kê RSVP. Detail có live preview thật và health kiểm dữ liệu/asset; view as guest an toàn. Lock/unlock có reason, mốc hết hạn, support case, tác động phụ trợ (RSVP/wish/photo/drop/widget). Publish/unpublish, extend expiry, duplicate, đổi mẫu **chỉ qua quy trình an toàn**, preserve owner/content/asset. Khi customer bị khóa không tự quyết định public invitation biến mất nếu chưa có policy. Không tự sửa thiệp khách khi chưa có lý do/ủy quyền/audit.

## A07 — Mẫu thiệp, phiên bản, Schema/Asset QA

List, filters, slug, tier, family, style, tags, author, featured, active, usage, performance. Tạo mẫu/import zip an toàn (khi scope cho phép), kiểm manifest/schema keys/canonical targets/asset tồn tại/CSS prefix/Blade extends/embed bridge, phát hiện external URLs/traversal, 10-section contract của template thông thường, RSVP/Signature theo skill. Phiên bản immutable, diff trước publish, QA preview 320–1440, rights/license, rollback không đổi version đang dùng, không ảnh hưởng thiệp đã tạo. **Không lấy assets và HTML từ những mẫu khác rồi tự chế style nếu mục đích là reuse native.**

## A08 — AI Curated tạo thiệp từ mẫu nguồn (7 phần)

Admin cấu hình tập mẫu native hợp lệ + phong cách Mây Hẹn được định nghĩa + color palettes khách chọn; AI **chỉ viết content**, không tạo HTML/CSS/JS. Chọn đúng 1 template gốc, render qua Blade/CSS/JS/assets của template đó; curated view chỉ hiển thị bảy phần tương ứng Hero, Story, Family, Countdown, Events, Timeline, Album 10 ảnh. Cần **mapping semantic ID**, vì số section trong skill 10 phần và trong curated có cách đánh số khác; không map bằng số thô nếu gây lệch. Kiểm tra ngẫu nhiên có seed/persistence, không random lại khi F5, không phát lại hiệu ứng khi vào xem thiệp; cinematic reveal lần tạo đầu tiên trên DOM native. Admin có test QA từng template, test độ dài copy, title clash, parent names, nil events/photos, theme, replay (không gọi AI), cost/rate-limit/error fallback, consent và content review. Giữ bình thường 10 section theo contract, curated là một chế độ hiển thị riêng, **không phá schema nguồn**.

## A09 — Đơn hàng, thanh toán, SePay, refund và đối soát

Search/order details/pricing snapshot/attempts/webhook history/provider transaction, statuses pending/paid/cancelled/refunded theo state machine thật. Signature, timestamp replay, bank recipient, transferType, exact amount, idempotency, unique provider_transaction_id, transaction boundaries, no duplicate entitlement, out-of-order callbacks. Manual reconciliation: evidence of bank credit + identity + amount + reference + reviewer + reason; **cấm đơn giản sửa status paid để cấp quyền**. Approval/reject with step-up/dual control theo risk. Refund phải gọi quy trình provider/accounting thật hoặc ghi 'refund request pending' rõ, không giả là tiền đã hoàn. Order cancelled but payment arrives xử lý policy minh bạch. Tổng doanh thu không tính pending/duplicate/failed; reconcile report/download đúng quyền.

## A10 — Gói dịch vụ, entitlement, quota, gia hạn, coupon

CRUD/version plan free/plus/pro theo dữ liệu thật, khả năng publish giá mới nhưng giữ order snapshot và quyền đã cấp; duration/quota/features/limits; upgrade/downgrade theo policy; expiry, grace period, overage, renewal cumulative, restore. Coupon validity/timezone, uses/unique users, stacking, min spend, max discount, anti-abuse, attribution and campaign stats. Giao diện so sánh quyền lợi client phản ánh đúng config, không đưa lời hứa fake như forever storage/24-7 guarantee. Preview tác động trước update hàng loạt.

## A11 — Agency/Partner, bàn giao, đa tenant, custom domain

Partner list/status/onboarding/commission nếu tồn tại/được duyệt, projects per tenant, invite/member/role, studio ownership, handover code create/revoke/expiry/claim atomic, authorization isolation, audit. Domain CNAME/verification/status/TLS/conflict, wildcard/reserved slugs, cooldown domain change, collision, wrong DNS. Support phải được ủy quyền; tenant A không thể xem B qua filter/export/preview/debug.

## A12 — Guest CRM, RSVP, lời mời, phân đoạn

Admin/Support xem tình trạng tổng quan theo cấp quyền; quản lý import, dedupe chính xác, nhóm bên nội ngoại, phone masked, guest token, link cá nhân hóa, delivery attempts, consent, RSVP pending/attending/declined/uncertain, adults/children/plus-ones, dietary, response timestamps. Test NAT chung IP không chặn nhầm khách, token guess/expired/reused, sửa RSVP ảnh hưởng headcount/seat/check-in, remove guest preserving audit. Admin không mặc định có quyền xem toàn bộ danh sách khách thật.

## A13 — Seating, check-in, Wedding Day Command Center

Bàn sức chứa, phân bàn theo suất, khách cùng nhóm, trạng thái reserved/arrived/declined, reallocate after RSVP, waitlist/walk-in, QR scan/checkin double-scan idempotent, status sync, offline recovery and merge nếu đã triển khai; số khách đã đến, theo người lớn/trẻ em, theo cổng, theo thời điểm. Admin xử lý khi ticket báo sai bàn phải có lịch sử sự kiện không sửa mù dữ liệu; tránh lộ guest list trên màn hình công cộng.

## A14 — Guestbook, chữ ký, Photo Drop, Live Wall, quà mừng

Queue moderation wishes/signatures/media, policy public/private/rejected, appeal, profanity false positives tiếng Việt, links/stickers, owner approval, scheduled display, Live Wall PIN, photo duplicates/retry, image scanning, deletion consistency, gifts private financial ledger, CSV formula defense, thank-you status. Phân biệt quà mừng cưới (thông tin tài chính riêng chủ thiệp) với **doanh thu bán gói Mây Hẹn**; **không tổng hợp chung**.

## A15 — Sau cưới: Memory Vault / Time Capsule / Photo Album / kỷ yếu

Feature entitlements, privacy boundary, sealed/unlock date, content never revealed early, retention and deletion policies, archive/export, ownership transfer, private assets, status after expiration; audit admin support access carefully. Không có "lưu trữ trọn đời" nếu thiếu chính sách/dự toán thực tế.

## A16 — Ticket Center / CRM hỗ trợ / SLA

Ticket intake, source, severity, owner, status, queues, search, filter, tags, note nội bộ riêng, reply templates, attachment scanning, duplicate merge, reopen, escalations, SLA work-hours, reminders, link account/order/invitation/QA case, customer consent for support view. Template link can lead to diagnostic panel showing safe errors and fixes; **không impersonate âm thầm**. Support replies phải kiểm tra thật status gửi; internal note không lộ cho customer.

## A17 — CMS, SEO, marketing, lead, blog, FAQ, redirects

Content draft/review/publish/schedule, preview, history, slug/canonical, meta, OG, robots, schema, internal links, sitemap, broken links, redirects 301/302 with loop protection, banners with date and targeting, CTA placement, FAQ, contact leads, spam moderation, consent, export. SEO admin cần kiểm tra trước/sau, chặn index các URL private `/admin`, `/thiep`, `/tao-thiep`, payment, studio.

## A18 — Email/SMS/Zalo campaigns, notification & communication

Provider settings, template variables whitelist, preview with safe dummy data, test-send to approved test destinations, sender identity, consent/opt-out/quiet hours, queue, status queued/sent/delivered/failed distinct, batch progress, retry failed only, cost cap, duplicate send guard, unsubscribe and rate-limit. Mọi tin gửi thật phải được phê duyệt; không coi queued là delivered.

## A19 — Job Center, scheduler, diagnostics, integrations

Jobs/batches/queue delay/failed/stuck, progress persisted across F5, retry idempotent, scheduled jobs, logs sanitized, trace ID, webhook errors, database/storage/cache/worker indicators, SLO dashboard, alert dedupe/escalation, rate-limits, incident timeline, RCA, kill switches. Không mở chức năng "retry all" nếu rủi ro gửi SMS/email trùng.

## A20 — Media/library/storage, quota, lifecycle

Storage owners, file types/MIME, thumbnails, checksum dedupe within permission scope, orphan detection dry-run, delete quarantine/restoration, signed URLs, image derivative regeneration, broken image/link inspector, per tenant quota, sensitive assets access, antivirus strategy nếu có tooling hiện hữu, audit scans; cleanup không xóa source còn reference.

## A21 — Analytics, funnel, cohorts, attribution, trải nghiệm

Total signup, create started/completed, publish/paid conversion, gross/net revenue, refund ratio, support time, template performance, campaign ROI when source exists, retention/cohort if definition valid, reporting window/timezone, source/capture consent, low-sample explanation, drilldown and export per permission. Không dựng biểu đồ số giả khi bảng rỗng. Tránh tracking PII nhạy cảm/gu cá nhân từ người chưa consent.

## A22 — System Settings, feature flags, secrets, environment

Brand/locale/SEO, payments, mail, notification, storage, privacy, moderation, plan defaults, flags, rate-limit, integrations, backups. Settings has diff, validation, test connection, preview impact, version, rollback, audit and scope. Secret entry update empty is no-op, never expose to GET/HTML/JS/log; rotation needs controlled workflow. Feature flag gradual rollout with kill switch and measurement; don't call external APIs without confirmation.

## A23 — Audit, risk, privacy, security center

Append-only admin trail with actor/action/target/old/new masked/reason/ip/device/trace/request IDs, filter/export per role, integrity check, alerts on suspicious actions, last-admin guard, auth events, access request approvals, 2FA review, personal-data disclosure, delete/export/consent/retention, cross-tenant tests and CSV/ZIP injection protections. Logs must be tamper-evident where architecture supports it; don't fake immutability from a modifiable local JSONL file. Sensitive support view requires consent + time + scope + full audit.

## A24 — Release, schema migration, test evidence and rollback

Template deploy QA gate, versioned contract, feature-flag rollout, post-deploy smoke checks in authorized environment, changelog, rollback plan, migration reversibility, test artifact integrity, screenshot before/after, code/test coverage tied to behavior, known-limitations register and incident ownership. Đừng "100% passed" nếu chỉ check HTTP 200 hoặc screenshot login instead of internal page.

## A25 — Team workflow và cộng tác nội bộ

Task assign/claim/escalate, work queues, private notes, follow-up, scheduled reminders based on existing queue, approval chain, vacation handoff, assignment history, shift coverage, operational checklist. Không tạo thêm Slack-like chat chỉ vì mới lạ; gắn collaboration vào record thực.

# 6. 18 Ý TƯỞNG KHÁC BIỆT — PHẦN NÀY LÀ ĐỀ XUẤT, CHƯA PHẢI SẢN PHẨM ĐÃ CÓ

> **Không khẳng định "chưa ai từng có" trên thị trường.** Hãy khảo sát cạnh tranh khi được phép nghiên cứu, nêu giả thuyết khác biệt; mỗi ý tưởng phải có pain point, actor, trigger, user journey, tác động dữ liệu, cost, privacy, giải thích được và A/B test hoặc phỏng vấn. Không tự động kích hoạt thực tế nếu thiếu quyền/phê duyệt.

1. **Wedding Readiness Radar** — radar tự phân loại đám cưới sắp diễn ra có rủi ro: chưa nhập venue, bản đồ sai, RSVP đông hơn ghế, ảnh lỗi, domain chưa xác minh, nhạc lỗi. Mỗi cảnh báo có bằng chứng, mức độ, owner, CTA một chạm. Không đưa phán đoán sức khỏe/tình cảm.
2. **Invitation Quality Passport** — hộ chiếu chất lượng cho từng thiệp: asset health, đủ dữ liệu, mobile snapshot, link live, privacy, invitation date consistency, accessibility, publish readiness; pass chỉ khi test thật, cho chủ thiệp xem báo cáo được lược bỏ bí mật.
3. **Pre-Wedding Change Impact Simulator** — trước khi admin đổi giờ tiệc/hủy venue/gỡ mẫu/thay gói, mô phỏng phạm vi ảnh hưởng tới link đã gửi, RSVP, seating, maps, QR, cache và quyền; diff và kế hoạch rollback.
4. **Guest Journey Synthetic Rehearsal** — bot kiểm thử nội bộ tạo khách giả cách ly: mở thiệp native trên máy nhỏ, nhìn đường đi, RSVP, quét QR giả lập, xác minh dòng dữ liệu; không spam hoặc tạo dấu vết trong data thật.
5. **One-Case Timeline** — hợp nhất lịch sử ticket, payment, invitation revisions, uploads, emails, webhook, consent vào *một dòng thời gian có thể kiểm chứng*, masked theo quyền.
6. **Promise-to-Proof Monitor** — đối chiếu lời hứa ngoài trang giá/marketing với feature entitlements, quota, SLA thực tế; cảnh báo mismatch trước khi khách khiếu nại.
7. **Safe Support Lens** — xem vấn đề dưới góc nhìn khách **read-only, che PII**, cần ủy quyền thời hạn và audit, không bao giờ silently impersonate.
8. **Wedding Day Rescue Mode** — bảng sự cố ngày cưới: QR hỏng, mạng yếu, check-in quá tải, khách chưa thấy bàn; có phương án dự phòng được phê duyệt, checklist khôi phục, history.
9. **Finance Reconciliation Detective** — ghép payment callback với order bằng bằng chứng, phát hiện double credit, thừa/thiếu, hủy rồi chuyển, refund pending; đề xuất cách xử lý, không tự sửa `paid`.
10. **Template Visual DNA** — lưu dấu nhận diện art direction của từng mẫu (font/spacing/assets/layout), QA ảnh trước/sau version để phát hiện vô tình làm mất thẩm mỹ hoặc lọt ảnh mẫu lạ.
11. **Native Template Fidelity Guard** — kiểm AI Curated thực sự tái dùng Blade/CSS/JS/assets mẫu nguồn, tuyệt đối không render `mayhen-curated/variant_*.blade.php` như sản phẩm cuối cùng.
12. **Issue-to-Action Cockpit** — từ lỗi 500/ảnh hỏng/email fail dẫn tới ticket, owner, root cause, test bổ sung, triển khai fix và kiểm chứng customer outcome, không chỉ đóng bug khi test xanh.
13. **Delight Recovery Planner** — khi khách kẹt ở Studio/đổi mẫu/đăng nhập, dùng aggregate funnel (nếu consent) để đề xuất cải tiến bước hoặc hướng dẫn; hạn chế theo dõi cá nhân.
14. **Privacy & Retention Map** — sơ đồ dữ liệu cặp đôi: dữ liệu nào lưu ở đâu, ai có quyền, khi nào phải xóa, bản sao backup; cho admin thực hiện request export/delete có kiểm soát.
15. **Zero-Surprise Campaign Launch** — preview cùng một campaign trên mobile, quota, segment, consent, chi phí, sample send, khả năng rollback, xác nhận 2 bước khi gửi hàng loạt.
16. **Proactive VIP Wedding Concierge Queue** — tự động ưu tiên hỗ trợ theo ngày cưới sắp tới và sự cố thật, không chỉ dựa trên gói trả nhiều tiền; không hứa hỗ trợ 24/7 nếu không có đội ngũ.
17. **Operational Memory / Incident Knowledge Cards** — ghi quyết định hỗ trợ trước đây và bản sửa tương ứng, gợi ý SOP có nguồn và độ tin cậy; không tự động sửa thiệp khách.
18. **Role-Aware Morning Brief** — sáng mở admin, mỗi vai trò thấy 5 việc cần xử lý nhất, số liệu được giải thích, chênh lệch với hôm qua và deep-link; không spam thông báo.

**Với mỗi ý tưởng:** lập bảng Expected Value, effort, dependency, risks, measurable KPI, security/privacy guardrails, prototype, acceptance, confidence, feature flag, rollback. Triển khai **từ 1–3 ý tưởng mạnh nhất trước**, không tạo 18 menu rỗng.

# 7. QA CHỨC NĂNG VI MÔ: TEST KHÔNG CHỈ HAPPY PATH

Với MỖI atomic feature trong catalog, agent phải tự bổ sung test case thực tế tương ứng:

- **Positive:** người dùng đúng role + state hợp lệ → response, DB, UI, audit khớp.
- **Invalid/Boundary:** null, missing, empty, whitespace, Unicode Việt/emoji, chars dài, số âm/0/max, ngày sai/chuyển năm, file sai định dạng/kích cỡ, query tampered → trả 422/404 phù hợp, không 500.
- **Authorization:** guest, customer, staff, manager, admin, partner, resource của tenant khác → không đọc/sửa/xóa/export/nhận dữ liệu riêng; test server không chỉ ẩn nút.
- **State and idempotency:** double click, request retry, back navigation, F5, multi tabs, race, out-of-order webhook, repeated upload/job check-in → không nhân đôi side effect.
- **Persistence/consistency:** trước/sau DB, caches, counts, audit, dashboard, exports, public/customer views và data state đồng nhất.
- **UX/accessibility:** disabled/hover/focus/loading/empty/error/success, mobile 320+, keyboard, modal overflow, selected filters after reload, long texts, requests chậm/offline.
- **Performance/security:** pagination large datasets, query count, PII masking, CSV injection, XSS, CSRF, mass assignment, path traversal, SSRF khi scope có liên quan, secrets, dangerous actions.
- **Failure recovery:** DB transaction rollback, job fail/retry, provider timeout, network timeout, 409 conflict, duplicate operation, copy link permission, export timeout.

Đừng tạo 6 test giống hệt nhau cho mọi item chỉ để tăng con số. **Mỗi test phải gắn assertion quan sát được**, dựa trên contract thực. Case generic nếu không áp dụng thì `NOT_APPLICABLE + lý do`.

## 7.1. Luồng E2E bắt buộc

1. Khách signup → tạo thiệp → admin thấy → duyệt hỗ trợ → sửa draft → publish → chi tiết admin phản ánh đúng.
2. Customer mua Plus/Pro → webhook hợp lệ → entitlement → email/receipt → dashboard/admin đồng bộ; webhook lặp/thiếu/thừa tiền không phát sinh extra credit.
3. Khách báo giao dịch thành công nhưng order pending → finance triage → đối soát có bằng chứng → cấp quyền chính xác 1 lần và audit.
4. Admin gỡ mẫu đang bán → thiệp cũ vẫn render native đúng version; khách mới không chọn mẫu inactive.
5. Admin khóa thiệp → mọi đường public/RSVP/wish/photo/live wall/preview token xử lý theo policy → mở khóa khôi phục đúng.
6. Customer đổi ngày cưới → calendar/countdown/map/timeline/QR/RSVP reminder/campaign đồng bộ.
7. RSVP từ 2 lên 4 → bàn đủ/thiếu chỗ → check-in → counters trên dashboard admin và chủ thiệp đều đúng.
8. Hai khách chung IP RSVP vào 2 thiệp hoặc 1 thiệp có token độc lập → không chặn nhầm.
9. Partner A claim/revoke handover → Admin xem audit → Partner B không truy cập được.
10. Admin đổi bảng giá → đơn cũ không thay giá, order mới lấy đúng version; landing pricing/copy trung thực.
11. Admin xử lý ticket báo ảnh mất → tìm đúng owner/asset path → sửa file tham chiếu → xem public thành phẩm → đóng ticket có evidence.
12. Admin moderate một lời chúc → public/guestbook/live wall/published snapshot đồng bộ, event khác không bị ảnh hưởng.
13. Bulk export khách/payments → filter đúng; role không quyền bị chặn, CSV safe, export event audit.
14. Dashboard KPI doanh thu bằng tổng ledger đúng timezone/đơn đã paid, refund net tương ứng.
15. AI Curated chọn style + palette + parents → native template → 7 phần đúng → reload giữ nguyên, content không giả dữ kiện.
16. Đổi role manager→staff trong phiên đang mở → thao tác nhạy cảm bị chặn ngay theo chính sách session/cache.
17. Admin sửa setting payment/mail thất bại connection test → config live cũ còn nguyên.
18. Upload ảnh trùng/failed/retry → không leak dữ liệu wedding khác và không tạo orphan vĩnh viễn.
19. Hai admin cùng sửa record → 409/merge/diff hoặc lock hợp lệ; không âm thầm ghi đè.
20. Widget alert click ra đúng danh sách đã lọc; xử lý xong thì cảnh báo hết đúng lúc, không mất history.
21. Customer gửi request xóa dữ liệu → privacy flow xác minh danh tính → tác động DB/file/cache/backups theo policy, giữ ledger bắt buộc theo quy định nếu có.
22. Incident job bulk send fail giữa chừng → retry chỉ fail, không gửi lại cho khách đã thành công.
23. Feature flag bật cho 1 nhóm thử → không leak sang nhóm khác, kill switch rollback an toàn.
24. Tài khoản admin bị vô hiệu hóa → cookie/sessions/privileged API xử lý theo policy, audit đầy đủ.

# 8. BẢO MẬT ADMIN — GATE KHÔNG ĐƯỢC BỎ QUA

- Kiểm tra authentication, authorization (IDOR/BOLA), tenant isolation, staff vs manager vs admin, route GET/POST/PUT/DELETE/exports/background jobs, CSRF, session fixation, brute-force, password reset, user lock, MFA/step-up nếu có.
- Secrets/webhook/bank info chỉ hiện masked; audit không chứa password/token/full card/bank. Không hiển thị raw unredacted webhook cho role không được phép.
- Ngăn spreadsheet formula injection, HTML injection, upload disguised, ZIP traversal/bomb nếu import, unsafe file serving, open redirect, SSRF ở URL test/image fetch, email header injection, log injection, stored XSS ở nội dung AI/notes/tickets.
- Thao tác không thể đảo: delete, refund, rotate secret, force publish, ownership transfer, data export cần audit, reason, proper approvals và confirmation.
- Báo cáo security bằng bằng chứng tái hiện trong **môi trường được phép/test DB**, không pentest website thật hay tải dữ liệu khách sang máy riêng.

# 9. QUẢN TRỊ TIỀN TỆ — STATE MACHINE VÀ GIAO DỊCH PHẢI CHÍNH XÁC

Một payment có thể pending/received/unmatched/verified/paid/refund_requested/refunding/refunded/failed tùy schema thật; **không tự thêm trạng thái vào DB khi chưa thiết kế migration và backward compatibility**. Xây bảng map chính xác trạng thái hiện hữu. Tách order state, payment event, entitlement state, refund state và reconciliation evidence. Số tiền gross, discount, refund, net, gifted money thuộc hai domain khác nhau. Không cho admin bấm `paid` chỉ vì checkbox đã confirm. Tất cả operations chịu double-submit và webhook concurrent. Khi report analytics, dùng cùng công thức và timezone giữa các trang; test ngoại lệ số tiền âm/0, underpay/overpay, wrong recipient, cancelled-then-paid, duplicate provider ref.

# 10. KHÔNG LỪA CHẤT LƯỢNG BẰNG DEMO, CSS VÀ SỐ LIỆU

- Thực sự render page của role có quyền. Ảnh `partner_portal` đang redirect login không chứng minh màn partner; tương tự admin page.
- Không render thông báo "AI đã phân tích" khi chỉ chạy template static; mọi AI/heuristic phải ghi đầu vào, model/cost/latency/status nếu có sử dụng AI thật, và kết quả qua human review trước hành động nguy hiểm.
- Tất cả chart số liệu thật, fallback "chưa có dữ liệu", không giả số người dùng, rating, doanh thu, SLA.
- Nút "Xem báo cáo" phải dẫn tới báo cáo thật, filter đúng. Nút export phải xuất file thật theo quyền. Nút test connection phải kiểm tra sandbox/credential hợp lệ, không gọi provider thật gây phí nếu chưa cho phép.

# 11. ĐỘ TIN CẬY, HIỆU NĂNG VÀ QUY TRÌNH PHÁT HÀNH

- Local SQLite tests chỉ chứng minh trong SQLite; nghiệp vụ race/transactions/webhook/query cần xác minh thêm trên **MySQL staging** nếu có, không suy rộng sang production.
- Performance baseline TTFB, query count p95 nếu môi trường đo được, payload list, paginate 100k rows simulation, asset size, JS events/handlers, heavy charts; cải thiện có before/after.
- Phân biệt data loading, stale, stopped, unknown. Đồng hồ kiểm tra trạng thái hoạt động dựa vào log thực, không dựa vào `200` của trang shell.
- Job/notification phải idempotent, retry có backoff và DLQ/quarantine khi phù hợp; backup/restore diễn tập trên staging.
- Rollout tính năng mới bằng feature flags khi có rủi ro; migrate có kế hoạch rollback và dữ liệu backfill an toàn. Không deploy/push khi chưa được phép.

# 12. LỘ TRÌNH TRIỂN KHAI — LÀM DẦN, KHÔNG THÊM MENU RỖNG

**Wave 00: Evidence & Inventory** — inventory route/control, bảo mật baseline, screenshots admin thực và catalog/status thật; chuẩn hóa quyền và xung đột tài liệu.
**Wave 01: P0 Security & Money** — RBAC, auth session, privacy, SePay webhook, order/refund/reconcile, admin audit, export PII.
**Wave 02: Admin Shell + UX Foundations** — sidebar/topbar/breadcrumb/search, data table/form/modals/drawers/responsive states, design tokens.
**Wave 03: Users & Invitations** — tài khoản, owner, wedding projects, statuses, support preview, logs, expiry.
**Wave 04: Templates & AI Curated** — native template governance, schema/asset QA, publish/version/rollback, curated 7-part invariants.
**Wave 05: Guest & Wedding Ops** — RSVP/guest/seating/check-in, moderation, gifts, time capsule, Live Wall, photo drop.
**Wave 06: Commerce & Growth** — plan/entitlement, coupons, marketing CMS, SEO, performance data; avoid unapproved sales claims.
**Wave 07: Support & Ops** — tickets, comms, jobs, incident center, backups, settings, notification, diagnostics.
**Wave 08: Data & Intelligence** — analytics, action inbox, readiness radar, quality passport, impact simulator, evidence-based insights.
**Wave 09: Hardening & UAT** — every role/screen/viewport, regression, MySQL parity when possible, UAT with task completion and independent release gates.

Mỗi wave gồm những vertical slices 1–3 ngày công (tùy codebase). Kế hoạch ưu tiên phải từ evidence/risk/value, không viết lại mọi thứ chỉ vì muốn “hiện đại”. Nếu thiếu điều kiện cho một phần, `BLOCKED + lý do + next safe task`.

# 13. DEFINITION OF DONE — CHUẨN CỐ ĐỊNH CHO TỪNG CHỨC NĂNG

Một mục **chỉ đạt VERIFIED_PASS** khi có đủ:

1. Contract hành vi/permission/state documented chính xác theo code và nghiệp vụ được duyệt.
2. Có đường thực thi thật UI → backend → DB/external mock → UI; mọi CTA và row action hoạt động.
3. Test success + invalid + unauthorized + cross-tenant + critical edge + idempotency tùy liên quan. Có case ID và assertion; không chỉ có code/route.
4. Error/loading/empty/success/confirm và persistence sau F5 được kiểm tra.
5. Audit với hành động sensitive, không leak secrets/PII, không phá data/tenant khác.
6. Visual QA đã thực hiện trên browser và viewport phù hợp; ảnh **đúng màn** và đúng role, không dùng redirect/login thay thế.
7. Không làm gãy module phụ thuộc; cross-module integration test và regression pass.
8. Code tuân thủ native, CSS prefix, perf, Pint, naming/structure, no extra dependency.
9. Trạng thái catalog cập nhật và evidence hợp lệ; docs, `context.gemini.md` cập nhật; commit message VN có dấu, **không tự commit**.
10. `Not tested` phải nói thẳng và giữ nguyên `UNVERIFIED/BLOCKED`; không tự gán 100%.

# 14. BÁO CÁO CHO NGƯỜI DÙNG SAU MỖI MODULE / PHIÊN

Báo ngắn gọn có thể dùng để ra quyết định:

- `Module/Atomic IDs`; Scope; phiên bản code/branch; bằng chứng thực.
- `Existing verified`, `Existing but defective`, `Missing required`, `Proposed only`, `Blocked`.
- Bug severity, nguyên nhân gốc, ảnh hưởng và cách fix, file diff thật, test chạy/test fail/test chưa chạy.
- Kết quả UI **so trước/sau** trên desktop/mobile và các trạng thái quan trọng.
- Ảnh hưởng security/data/payment, migration/release risk và rollback.
- Roadmap checkpoint ID kế tiếp, điều kiện làm tiếp, nội dung commit tiếng Việt.
- **Không được báo hoàn tất 100% chỉ vì mọi test hiện tại đều xanh.**

# 15. FILE CẦN DUY TRÌ TRONG REPOSITORY

Giữ tài liệu trong `docs/admin-vnext/` (khi người dùng đưa pack vào):

- `MAY_HEN_ADMIN_ATOMIC_FUNCTIONS.csv` — catalog có unique IDs, source/priority/status/ownership/permission/view/API/test/evidence.
- `MAY_HEN_ADMIN_TEST_CASES.csv` — case ID, feature ID, prerequisites, actor, steps, expected, actual, result, evidence, last verified code.
- `MAY_HEN_ADMIN_IMPLEMENTATION_WAVES.md` — kế hoạch nhỏ theo dependencies, cập nhật trạng thái từng module.
- `MAY_HEN_ADMIN_INNOVATION_BACKLOG.md` — đề xuất khác biệt, giá trị, đo lường và rủi ro.
- `01_RUNTIME_ADMIN_INVENTORY.md`, `02_ADMIN_BUG_REGISTER.md`, `03_VISUAL_QA_EVIDENCE.md`, `04_SECURITY_PERMISSION_MATRIX.md`, `05_RELEASE_CHECKPOINT.md` — tạo khi thực thi, không điền sẵn PASS.

# 16. CHỈ THỊ BẮT ĐẦU NGAY TRONG AGENT

**HÃY BẮT ĐẦU, KHÔNG DỪNG Ở LẬP KẾ HOẠCH.**

1. Đọc `context.gemini.md` trước, sau đó rules/skills/docs/CSV hiện hành.
2. Kiểm kê **toàn bộ `/admin` và code phía sau**, truy vết từng nút/form/status/permission; xác định đã triển khai gì thực chất, còn thiếu gì; không tự tạo tính năng chỉ vì catalog có dòng.
3. Xác định & sửa lỗi P0/P1 có bằng chứng trong môi trường local/staging an toàn (viết regression test trước hoặc cùng fix), rồi chạy test liên quan.
4. Kiểm tra trực tiếp **ít nhất 1 luồng admin từ đầu đến cuối trên browser** nếu môi trường sẵn; gồm đăng nhập đúng role, tìm record, mở detail, thực hiện action an toàn, F5, ảnh trước/sau. Nếu không có browser, minh bạch hạn chế.
5. Hoàn thiện lần lượt từng vertical slice của Wave 01 rồi Wave 02… ưu tiên highest risk/impact/dependency, không phá hoạt động hiện tại.
6. Sau mỗi slice, cập nhật catalog/checkpoint/`context.gemini.md`, báo test + screenshot + diff + commit message VN. Tự chọn slice kế tiếp trong phiên; khi hết điều kiện, checkpoint chính xác.

**ĐỊNH NGHĨA THÀNH CÔNG:** Admin giỏi tới mức một nhân viên mới vẫn biết việc cần làm, hiểu rủi ro, thao tác nhanh mà không sợ làm sai; Super Admin kiểm soát được dữ liệu, tiền, khách hàng và chất lượng thiệp; khách hàng cảm nhận chất lượng đó qua trải nghiệm ít lỗi hơn, hỗ trợ nhanh hơn, minh bạch hơn.
