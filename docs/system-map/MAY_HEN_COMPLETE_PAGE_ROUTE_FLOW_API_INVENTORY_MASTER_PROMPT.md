# MÂY HẸN — MASTER PROMPT: COMPLETE PAGE • ROUTE • UI CONTROL • API • BUSINESS FLOW INVENTORY
## Bản đồ toàn bộ hệ thống trước khi bắt đầu sửa giao diện — một DOCX + một XLSX, mỗi tệp chỉ có 2 phạm vi ADMIN / CLIENT

> **Loại nhiệm vụ:** READ-ONLY SOFTWARE ARCHAEOLOGY, FULL TRACEABILITY INVENTORY & BUSINESS FLOW DOCUMENTATION.
>
> **Bối cảnh:** Laravel Mây Hẹn (mayhen.vn), Laravel/Blade + CSS thuần + JavaScript ES Modules thuần; Admin, Client, Studio, Guest CRM, RSVP, thanh toán, đối tác, Live Wall, AI Curated, các luồng cưới, CMS và tác vụ nền.
>
> **Workspace bắt buộc:** `E:\Project\Laravel\mayhen.vn` — trước khi làm, kiểm tra đường dẫn thực tế của workspace, không tự suy diễn ổ D cũ.
>
> **Kết quả bàn giao bắt buộc:**
> 1. `docs/system-map/MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx` — **CHỈ ĐÚNG 2 WORKSHEETS** tên `Admin` và `Client`, không có sheet thứ ba, sheet ẩn, README hay Summary riêng.
> 2. `docs/system-map/MAY_HEN_COMPLETE_SYSTEM_MAP.docx` — **CHỈ 2 PHẦN NGHIỆP VỤ CHÍNH** `PHẦN I — ADMIN` và `PHẦN II — CLIENT`, cùng toàn bộ chức năng, sơ đồ cây màn hình, trang, link, API, business flows tương ứng với Excel. Cho phép trang bìa, mục lục, quy ước thuật ngữ ở đầu, nhưng không tạo phân hệ thứ ba.
>
> **Phạm vi:** KHÔNG CODE THÊM TÍNH NĂNG, KHÔNG SỬA UI, KHÔNG CHỈNH LOGIC, KHÔNG CHẠY MIGRATION/SEEDER PHÁ DỮ LIỆU, KHÔNG TRIỂN KHAI PRODUCTION. Đây là giai đoạn vẽ bản đồ và kiểm chứng sự thật trước khi chủ dự án chỉ định từng màn hình cần sửa.

---

## 00 — VAI TRÒ, MỤC TIÊU VÀ NGUYÊN TẮC KHÔNG KHOAN NHƯỢNG

Bạn là tổ hợp của **Principal Software Architect + Product Information Architect + Senior Laravel Auditor + Business Process Analyst + Senior QA Traceability Engineer + Visual UX Auditor + Technical Writer**.

Mục tiêu KHÔNG phải liệt kê vài URL cho xong. Hãy dựng một **bản đồ hoàn chỉnh có thể truy vết hai chiều**:

`Vai trò → Menu/trang cha → trang con → component/section → control nhỏ nhất (nút, link, input, tab, modal, row action...) → event người dùng → điều kiện nghiệp vụ → request/API → middleware/authorization → controller → service/job → database → response → trạng thái UI → trang/luồng tiếp theo → failure/recovery → test/evidence`.

Phải trả lời được các câu hỏi sau chỉ bằng tài liệu bàn giao:
- Từ màn hình Admin A đi tới đâu, có bao nhiêu lối đi, từng nút làm gì và quyền của từng vai trò là gì?
- Client vừa bấm một nút trên mẫu thiệp có thể làm phát sinh request nào, thay đổi bảng/cột gì, sau đó ảnh hưởng Admin và khách mời thế nào?
- Cùng một API được gọi bởi những trang nào? Một trang sử dụng những API nào? Một chức năng trải qua những trang nào?
- Mỗi modal, panel, tab, dropdown, filter, row action, upload, export, scan QR, nút copy link, toast, checkbox nhỏ nhất nằm ở đâu và kích hoạt hành vi gì?
- Chức năng có triển khai thật, chỉ được ghi trong PRD, nằm trong code nhưng không tới được, hay đã có UI nhưng chưa thấy backend?
- Những lỗi/nút dẫn tới 403/404/422/409/429/500, offline, timeout, session hết hạn sẽ hiện ở đâu và người dùng khôi phục bằng cách nào?
- Khi sửa giao diện từng trang, chủ dự án cần biết CSS/JS/Blade nào, route/API nào và các trang liên quan nào có thể bị tác động?

**Không được: (a)** tuyên bố toàn bộ hệ thống hoàn tất 100% chỉ vì Pest pass; **(b)** tự tạo URL, flow, API hay tính năng vì PRD nhắc tới; **(c)** gom nhiều nút khác nghiệp vụ làm một dòng cho ngắn; **(d)** bỏ qua các route không có menu; **(e)** thay kết quả chạy thật bằng mô tả lý thuyết; **(f)** chỉ đọc docs rồi suy ra source; **(g)** tạo Excel ba sheet trở lên; **(h)** tạo DOCX chỉ chứa bản tóm tắt không khớp Excel.

---

## 01 — NGUỒN SỰ THẬT: PHẢI ĐỌC THỰC TẾ TRONG REPO

### 01.1 Đọc theo thứ tự trước khi kiểm kê
1. `context.gemini.md` (cả phần đầu lẫn **những cập nhật mới nhất ở cuối**, đặc biệt tiến độ đề án 1–166), `AGENTS.md`, `CLAUDE.md` nếu có.
2. `.agents/skills/mayhen-project-architecture/SKILL.md`, `mayhen-admin`, `mayhen-laravel-backend`, `mayhen-blade`, `mayhen-javascript`, `mayhen-ui-ux`, `mayhen-invitation-standards`, `mayhen-invitation-templates`, `mayhen-visual-editor`, `mayhen-guest-rsvp`, `mayhen-payments`, `mayhen-partners`, `mayhen-auth-security`, `mayhen-testing-quality`, `mayhen-code-review`; đọc skill bổ sung thực sự liên quan khi tìm ra phân hệ mới.
3. `docs/MASTER PROMPT MAYHEN.VN.docx`, `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`, `docs/ADMIN_ARCHITECTURE_ROADMAP.md`, `docs/MAY_HEN_IMPLEMENTATION_ROADMAP.md`.
4. `docs/admin-vnext/` (atomic functions, function catalog, waves, innovation, bug register), `docs/qa/` (inventory, test matrix, traceability, conflicts, visual matrix, checkpoints), `docs/implementation/`, `docs/architecture/`, `docs/database/`, `docs/design/`, `docs/product/`, `docs/research/` và tài liệu Premium/Domain Invariants/Cross-Module E2E.
5. **Sau đó phải kiểm tra code là nguồn sự thật hiện tại:** `routes/`, `bootstrap/app.php`, service providers, middleware, `app/Http/Controllers/`, Request validators, Policies/Gates, Models, Services, Jobs, Events, Listeners, Observers, Notifications, Mail, Console, scheduler, migrations, factories, seeders, `resources/views/`, `public/assets/`, `resources/js/`, `resources/css/`, `tests/`, `config/`.
6. Đọc các mẫu thiệp bản địa trong `resources/views/client/invitations/{slug}/`, gồm `template.blade.php`, `schema.json`, `styles.css`, `script.js`, README và assets. Kiểm kê từng mẫu độc lập, kể cả các section và nút tương tác được tái sử dụng.

### 01.2 Thứ tự ưu tiên chứng cứ khi nguồn mâu thuẫn
- Mức 1: route thực nạp trong Laravel, Blade/JS/Controller/Service/DB migration đang tồn tại, browser/UI khi chạy trong môi trường cô lập.
- Mức 2: test còn tồn tại và đã được chạy với log, request/response được quan sát; ảnh chụp đúng màn hình và trạng thái.
- Mức 3: tài liệu hiện hành có liên kết code chính xác.
- Mức 4: đề án đã đánh dấu DONE trong nhật ký/PRD nhưng chưa đối chiếu source.
- Mức 5: ý tưởng, dự định, roadmap chưa triển khai.

**Tài liệu không được phép nâng trạng thái 4/5 thành 1/2.** Khi mâu thuẫn, giữ nguyên cả hai nhận định và ghi rõ: `CODE_CONFLICT`, `DOC_ONLY`, `DOC_STALE`, `UNVERIFIED`. Không ngầm sửa spec.

### 01.3 Snapshot kiểm kê
Tại thời điểm bắt đầu, ghi: timestamp Asia/Ho_Chi_Minh, đường dẫn repo, `git rev-parse HEAD`, branch, kết quả `git status --short` (không tự reset), PHP/Laravel version thật, môi trường test và database driver hiện hành. Lưu snapshot này vào phần đầu của hai phần trong DOCX và các cột `Snapshot_ID`/`Source_Revision` của cả hai sheet. Không đưa bí mật `.env`/token/PII vào tài liệu.

---

## 02 — QUY TẮC PHÂN CHIA CHÍNH XÁC 2 PHẠM VI

### Sheet `Admin` / Phần I DOCX
Bao gồm: `/admin/*`, trang đăng nhập/đăng xuất admin, dashboard, người dùng, vai trò, mẫu thiệp, thiệp khách, RSVP/quản lý khách mời ở góc Admin, bàn tiệc/Rescue, hỗ trợ, ticket, tài chính, orders/refunds/coupons/plans, đối tác do Admin quản lý, CMS/marketing/SEO/config, Job Center, logs, privacy, các công cụ cảnh báo, AI/analytics, sự cố và các tiện ích quản trị khác **nếu code thực sự có**. Mọi role `admin`, `manager`, `staff` và các action/permission của họ thuộc `Admin`.

### Sheet `Client` / Phần II DOCX
Bao gồm: website công khai, marketing/SEO, Auth khách hàng, account dashboard, Partner Portal/Agency/Studio dành cho đối tác bên ngoài, studio/editor, AI Curated, library template, xem thử, thiệp đã xuất bản, khách mời, link cá nhân hóa, RSVP, guestbook/signature, gallery, Photo Drop, Time Capsule, gifts/VietQR, seating/check-in cho chủ tiệc/lễ tân, Live Wall, callback/webhook phát sinh từ giao dịch Client, mọi trang lỗi public, và các luồng ngoài Admin. Trong `Client`, bắt buộc có cột `Actor_Role` phân tách `anonymous`, `customer`, `wedding_owner`, `partner`, `guest`, `receptionist`, `external_provider`.

### Những tình huống chồng lấn
- Một chức năng có mặt cả Admin và Client: tạo **hai record khác nhau**, mỗi record cho điểm tiếp xúc riêng, dùng `Shared_Flow_ID` và `Cross_Sheet_Refs` để nối.
- Một endpoint được gọi bởi nhiều màn hình: giữ **một record canonical cho mỗi endpoint + HTTP method + semantics + scope**, và ghi `Referenced_From`/`Callers`; mỗi luồng phải có `FLOW_STEP` tương ứng. Nếu same endpoint nhưng hành vi thay đổi theo role hoặc payload, ghi thêm nhánh/biến thể riêng.
- Webhook SePay, tác vụ thanh toán không có màn hình: đặt `Client` với `Actor_Role=external_provider`, liên kết đến các record Admin đối soát và bảng đơn hàng. Các endpoint điều khiển thuần Admin vẫn thuộc `Admin`.
- Queue/scheduler/job không có route: đặt ở sheet sở hữu nghiệp vụ hoặc sheet chứa trang điều hành chính; liên kết rõ tới tác vụ sinh ra, trang quan sát và side effects.
- Link bên ngoài (Google Maps, Zalo, mailto, tel, tên miền riêng) **cũng phải ghi** vì là điểm rời hệ thống; ghi `URL_Type=EXTERNAL` và quyền truy cập/public implications.
- Link động với `{slug}`, `{id}`, `{token}` hoặc query không có số lượng hữu hạn: **kiểm kê pattern và tất cả nhánh nghiệp vụ**, KHÔNG bịa vô số URL đã được tạo. Ghi URL ví dụ chỉ bằng dữ liệu giả an toàn.

Chỉ tạo đúng hai worksheet; không tạo `Summary`, `API`, `Flows`, `Permissions`, `README`, `Hidden`. Tất cả loại record được biểu diễn **bằng dòng và cột trong hai sheet**.

---

## 03 — KIỂM KÊ ROUTE / URL: KHÔNG BỎ SÓT BẤT KỲ CỔNG NÀO

### 03.1 Route Laravel ở runtime
- Chạy ở môi trường chỉ đọc/an toàn: `php artisan route:list --json` nếu phiên bản hiện tại hỗ trợ. Nếu không, sử dụng `php artisan route:list` và đọc đầy đủ mã nguồn route. **Không chỉ xem route `web.php`**: cần `routes/admin.php`, console/API files nếu có, bootstrap providers, groups, middleware, fallback.
- Thu thập method (GET/POST/PUT/PATCH/DELETE/OPTIONS...), URI pattern, route name, controller/action/closure, middleware stack, domain/host, throttle, signed URL, prefix, where-patterns, query params, versioning, redirects, route aliases và route dùng chung nhiều method.
- Kiểm kê route resource được khai triển thành nhiều action; đếm riêng từng method+path+name. Kiểm kê routes được đăng ký động, endpoint tĩnh phục vụ ảnh/css/js, asset proxy, download, export, stream, RSS/XML/sitemap/robots, error handler và endpoint health nếu có.
- Rà các endpoint **không được gắn vào menu** (webhook, API autosave, action batch, AJAX, iframe, API nội bộ, admin utility, upload, asset, RSVP, OTP/password reset, ký tên, moderation...)
- Rà các URL trong view/JS: `href`, `form action`, `fetch`, `XMLHttpRequest`, `window.location`, `location.assign`, `open`, `pushState`, `replaceState`, `postMessage`, `iframe src`, dynamic route generation `route(...)`, `url(...)`, `asset(...)`, JS dataset, lazy load, SSE/EventSource nếu có. Ghi cả URL được sinh trong controller/service/notification/email hoặc QR/deeplink.
- So sánh từng source link với danh mục route: phát hiện `BROKEN_LINK`, `MISSING_ROUTE`, `UNREACHABLE_PAGE`, `ORPHAN_API`, `ROUTE_COLLISION`, `UNEXPECTED_REDIRECT`, `UNREGISTERED_HANDLER`.

### 03.2 Thành phần không có URL riêng vẫn phải kiểm kê
- Menu con, mega menu, sidebar, bottom nav, breadcrumb, phân trang, sort, filter chips, bộ lọc nâng cao, dropdown, tab, accordion, stepper, drawer, popover, tooltip có hành động, editor toolbar, action bar sticky.
- Mỗi form và **từng trường input**, toggle, checkbox, radio, select, date picker, file picker, repeater, gallery item, dynamic row, số suất RSVP; ghi `name`, validation, default, empty/invalid/disabled/loading, side effects.
- Mỗi CTA, nút lưu nháp, autosave, publish, duplicate, đổi mẫu, mở preview, mua gói, gia hạn, duyệt, hoàn tiền, gửi, hủy, xác nhận, tải về, in, copy, regenerate, retry, check-in, assign table, batch action.
- Mỗi modal/confirm dialog và hành vi close/cancel/escape/outside click; khi Submit thành công/thất bại, UI thay đổi thế nào; kiểm tra nút không có listener và listener không có nút.
- Component được tái sử dụng trên nhiều trang: ghi component canonical và `Used_By_Pages`; tại mỗi trang ghi `COMPONENT_USAGE` để xác định ảnh hưởng khi sửa.
- Trang/trạng thái không thể điều hướng bằng URL thường: màn hình loading, empty state, lỗi quyền, 404/403/410/422/429/500, offline, expired, locked, draft, maintenance, session expired, state sau publish, modal sau redirect, print preview, reader mode, responsive drawer.
- Mẫu thiệp: ghi 10 section canonical của template thường; kiểm kê **AI Curated native profile** riêng theo hợp đồng thực tế (không tự giả định profile 7 hay 10 nếu source có cấu hình khác), các nút chơi nhạc, xem ảnh, RSVP, lời chúc, quà cưới, bản đồ, countdown, mở phong bì, preview bridge, theme/style token, section visibility.
- Job/scheduler/notification/email/console command/API không có UI cũng phải nằm trong hệ cây nghiệp vụ như node nền tảng và được liên kết nguồn gọi, trạng thái, retry/failure và màn hình hiển thị nếu có.

### 03.3 Ma trận đường dẫn bắt buộc
Ở mức thấp nhất phải phát hiện cả:
1. Route đến trang thật.
2. Route API trả JSON, HTML fragment, file, stream hoặc redirect.
3. Links nội bộ và ngoài.
4. Liên kết điều hướng có điều kiện role/feature flag/tier.
5. Link/route sinh từ template theo `{slug}/{token}/{id}`.
6. Link trong email, ảnh OpenGraph, QR, thẻ in A4, notification.
7. Ảnh, script, CSS, font, audio từ route assets nếu có authorization/handler riêng.
8. Webhook nhận từ ngoài, webhook/callback gửi đi và status polling.
9. Form submit không qua AJAX.
10. Endpoint background được gọi khi autosave, lazy load, animation, polling, prefetch hoặc mở trang.

---

## 04 — THANG PHÂN RÃ CÂY CHỨC NĂNG (KHÔNG CHỈ ĐẾN TRANG)

Bắt buộc ghi theo 8 cấp khi thực sự tồn tại:

**Cấp 0:** Phạm vi `Admin`/`Client`.
**Cấp 1:** Nhóm lớn (`Commerce`, `Studio`, `Wedding Ops`, `Support` ...).
**Cấp 2:** Module (`Orders`, `Invitations`, `RSVP`, `Seating`, `AI Curated` ...).
**Cấp 3:** Màn hình/route chính (`/admin/orders`, `/tao-thiep`, `/thiep/{slug}` ...).
**Cấp 4:** Thành phần màn hình (header, toolbar, section, tab, table, form, modal ...).
**Cấp 5:** Control/field nhỏ nhất có ý nghĩa (button, input, row action, icon clickable, link, checkbox...).
**Cấp 6:** Hành động và phản ứng UI (click, input, drag, drop, scroll, blur, submit, upload, copy, scan, retry...).
**Cấp 7:** Giao dịch/side effect/logic/validation/state/next step/API/job của hành động.

Không cần ép tất cả node có đủ 8 cấp nếu không có tương ứng thật. Tuy nhiên không được nhảy từ cấp 3 đến một dòng tổng kết kiểu “trang có CRUD” — phải phân rã CRUD thành thao tác và nhánh riêng.

**Bắt buộc có `Parent_Record_ID`, `Hierarchy_Path` và `Depth`** để có thể dựng lại toàn bộ cây bằng Excel. Mọi node trừ root phải có Parent ID hợp lệ trong chính sheet đó hoặc `Cross_Sheet_Refs` nếu là điểm nối liên phạm vi.

---

## 05 — EXCEL: ĐÚNG 2 SHEET, 1 BẢNG DUY NHẤT TRÊN MỖI SHEET, KHÔNG THIẾU CỘT

### 05.1 Workbook
Tên file: `docs/system-map/MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx`.
Sheet order: **`Admin` rồi `Client`**.
Mỗi sheet dùng **chung 54 cột** dưới đây (thứ tự cố định). Không chèn sheet khác. Header duy nhất ở dòng 1; từ dòng 2 là records; Excel AutoFilter, freeze top row + tối thiểu 4 cột định danh đầu tiên, wrap-text hợp lý, màu nhẹ/đọc được trên laptop; không merge cells trong bảng dữ liệu. Phân biệt node type qua conditional formatting nhẹ, không làm rối.

**54 cột bắt buộc:**

| # | Tên cột chính xác | Phải ghi những gì |
|---|---|---|
| 01 | `Record_ID` | ID duy nhất, bền vững qua các lần cập nhật, ví dụ ADM-PG-0001 / CLI-API-0042 |
| 02 | `Record_Type` | `GROUP/PAGE/STATE/COMPONENT/CONTROL/FIELD/ACTION/API/FLOW/FLOW_STEP/JOB/EVENT/EMAIL/WEBHOOK/REDIRECT/EXTERNAL_LINK/ASSET/ERROR/COMMAND/SCHEDULE` |
| 03 | `Inventory_Status` | `RUNTIME_VERIFIED/STATIC_VERIFIED/DOC_ONLY/NOT_VERIFIED/ORPHAN/DEPRECATED/BROKEN/CONFLICT` |
| 04 | `Actor_Role` | Anonymous, customer, guest, owner, partner, receptionist, admin, manager, staff, provider... |
| 05 | `Module` | Phân hệ nghiệp vụ chủ quản |
| 06 | `Submodule` | Phân hệ con cụ thể |
| 07 | `Feature_ID` | ID tính năng, link catalog/PRD khi có |
| 08 | `Flow_ID` | ID luồng nghiệp vụ toàn hệ thống; dùng lại ở 2 sheet khi liên thông |
| 09 | `Flow_Branch` | `HAPPY/INVALID/FORBIDDEN/EXPIRED/CONFLICT/OFFLINE/RETRY/...` |
| 10 | `Step_No` | Thứ tự bước trong luồng, có thể 1, 1.1, 1.2, 2... |
| 11 | `Parent_Record_ID` | ID node cha, không đặt tên mô tả thay ID |
| 12 | `Depth` | Cấp độ cây 0–7 hoặc cao hơn nếu thực sự cần |
| 13 | `Hierarchy_Path` | Đường dẫn cây: Client > Studio > Edit > Gallery > Upload... |
| 14 | `Screen_or_Node_Name` | Tên dễ hiểu bằng tiếng Việt, không chỉ tên class |
| 15 | `Screen_Kind` | Page/Tab/Modal/Drawer/Popover/Iframe/Widget/Background/Print/Email... |
| 16 | `UI_Section` | Vùng màn hình cụ thể chứa node |
| 17 | `UI_Control_Label` | Text nhãn thật hoặc mô tả icon nếu không có label |
| 18 | `UI_Control_Type` | Link/Button/Form/Text/Select/CheckBox/RowAction... |
| 19 | `DOM_Selector_or_Field_Name` | class/id/name/data attribute để tìm lại trong Blade/JS |
| 20 | `Route_Name` | Tên route chính xác hoặc `NO_ROUTE` |
| 21 | `HTTP_Method` | GET/POST/...; ghi phân biệt các method cùng URL |
| 22 | `URI_Pattern` | URI chuẩn, giữ nguyên `{id}`, `{slug}`, `{token}` |
| 23 | `Query_Params` | Query string, enum, default, optional |
| 24 | `URL_Type` | INTERNAL/EXTERNAL/WEBHOOK/ASSET/MAILTO/TEL/DEEPLINK/NO_URL |
| 25 | `Navigation_From` | Trang/nút/link truy cập được node này; dùng Record_ID |
| 26 | `Navigation_To` | Sau action đi tới trang/state nào; dùng Record_ID |
| 27 | `UI_Trigger` | Click/submit/change/scroll/auto-load/polling/cron/webhook... |
| 28 | `Preconditions` | Login, tier, permission, published, user state, valid token... |
| 29 | `Input_Data` | Request body, form fields, headers, upload format... |
| 30 | `Validation_Constraints` | Required, max, pattern, file size, date boundaries... |
| 31 | `Permission_Middleware` | Middleware/policy/gate/role/cross-tenant checks |
| 32 | `Business_Rule` | Quy tắc nghiệp vụ chính và điều kiện quyết định |
| 33 | `Controller_Handler` | Controller@method/closure/route handler thật |
| 34 | `Service_Job_Event` | Các service, queue, listener, notification, scheduler liên quan |
| 35 | `Database_Read` | Model/table và phạm vi query được đọc |
| 36 | `Database_Write` | Model/table/field bị thay đổi, transaction/lock nếu có |
| 37 | `State_Before` | Trạng thái trước thao tác |
| 38 | `State_After` | Trạng thái sau thao tác |
| 39 | `Success_Response` | HTTP status + JSON/redirect/file + feedback UI |
| 40 | `Failure_Response` | HTTP status + error/validation + feedback UI |
| 41 | `Edge_Cases` | Cases hiếm: double click, stale revision, expired PIN, no network... |
| 42 | `External_Side_Effects` | Email/SMS/SePay notification/storage/QR/print/external callback |
| 43 | `Referenced_APIs` | API IDs liên quan, liệt kê record IDs chính xác |
| 44 | `Shared_Flow_ID` | ID luồng xuyên Admin–Client và nguồn dữ liệu liên thông |
| 45 | `Cross_Sheet_Refs` | ID ở sheet bên kia; tránh bỏ đứt luồng |
| 46 | `Used_By_Pages` | Danh sách page IDs dùng shared component/endpoint |
| 47 | `Source_Files_Lines` | Các đường dẫn thật, line ranges nếu kiểm tra được |
| 48 | `Tests_And_Evidence` | Test path + test name/log, screenshot/browser evidence hoặc `NONE` |
| 49 | `Visual_UI_Notes` | Typography, overflow, spacing, mobile, states, animation, UX issues; ghi quan sát vs suy đoán |
| 50 | `Responsive_States` | Các viewport/state đã quan sát (không bịa) |
| 51 | `Risk_Priority` | P0/P1/P2/P3 + lý do |
| 52 | `Open_Questions` | Những điểm không thể xác minh/cần xác nhận |
| 53 | `Snapshot_ID` | Commit/branch/timestamp để đối chiếu version |
| 54 | `Docx_Anchor` | Chỉ mục heading trong DOCX, ví dụ `ADMIN > Orders > FLOW-ADM-007 > ADM-API-0112` |

Lưu ý: Không được bỏ cột vì “quá nhiều”. Với trường không áp dụng, dùng `N/A` (ngữ nghĩa không liên quan), còn chưa kiểm tra dùng `NOT_VERIFIED`; không để trống lẫn lộn. Các cột có chuỗi dài có thể wrap, bảng cho phép filter/sort theo tất cả cột. **Tuyệt đối không chỉ có 1 dòng tổng cho một trang**: phải thêm dòng cho controls/API/flow steps.

### 05.2 Quy tắc ID
- `ADM-...`, `CLI-...` là định danh record, không sử dụng row number làm ID.
- `FLOW-ADM-...`, `FLOW-CLI-...`, `FLOW-X-...` là ID luồng. Một luồng xuyên Admin/Client phải dùng **cùng `FLOW-X`** trong cả hai sheets.
- Khi 1 API phục vụ nhiều luồng, `Record_ID` API canonical giữ nguyên, các `FLOW_STEP` trỏ tới nó bằng `Referenced_APIs`.
- Khi gặp chức năng được docs ghi nhưng không thấy code, tạo `DOC_ONLY` row (không bịa route), để lộ khoảng trống.
- Khi gặp code tồn tại nhưng không thấy UI/route, tạo `ORPHAN` row, nêu nơi gọi thử và lý do nghi ngờ.
- Không duplicate record theo cách dẫn tới ảo tưởng bao phủ; `GROUP`/`PAGE`/`API` canonical nhưng `FLOW_STEP` có thể lặp nhiều luồng.

### 05.3 Trình bày Excel chuyên nghiệp
- Header tương phản cao, text trắng/ngà, không màu mè; freeze panes dạng `E2` hoặc tương đương (giữ 4 cột đầu khi cuộn ngang).
- Có Excel Table riêng trong mỗi sheet (filter hàng đầu), zebra striping nhẹ, số cột và sort/filter; không tạo thêm worksheet.
- Hyperlink nội bộ từ `Parent_Record_ID`, `Navigation_From/To`, `Cross_Sheet_Refs`, `Referenced_APIs` nếu thư viện hỗ trợ, nhưng **văn bản ID phải giữ nguyên**, không thay bằng một nhãn khó tìm.
- Không dùng macro, external links bí mật hoặc công thức chạy mạng. Với nội dung lấy từ dữ liệu người dùng, phòng chống công thức Excel injection (không để chuỗi nhập bắt đầu `=,+,-,@` trở thành formula).
- Đảm bảo Unicode tiếng Việt và tương thích Excel Windows. Cột URI, method, controller, DB để font đơn cách nếu phù hợp.
- Mỗi sheet có row `GROUP` cho root, nhưng **header dữ liệu vẫn ở Row 1**; mọi dòng thuộc bảng có Record_ID.
- Chèn link đến DOCX không bắt buộc dạng hyperlink có thể mở ngay trong Office; dùng `Docx_Anchor` ổn định để Search bằng Word.

---

## 06 — DOCX: HAI PHẦN CHÍNH, TỪ TỔNG QUAN TỚI MICRO-FLOW

Tên file: `docs/system-map/MAY_HEN_COMPLETE_SYSTEM_MAP.docx`.

**Tài liệu Word phải có đầy đủ nội dung**, không phải “xem Excel để biết chi tiết”. Trình bày chuyên nghiệp A4, tiếng Việt có dấu, heading H1/H2/H3/H4, mục lục tự cập nhật hoặc field TOC, header/footer, page number, caption cho bảng, bảng không tràn lề, đường dẫn code/route monospace, không chứa ảnh/tên/số điện thoại thật của khách.

### Trang đầu (không tính là phần nghiệp vụ thứ ba)
- Tên: `MÂY HẸN — BẢN ĐỒ TOÀN BỘ TRANG, LIÊN KẾT, API VÀ LUỒNG NGHIỆP VỤ`.
- Snapshot: repo/commit/date/branch/DB driver, phân biệt `STATIC` vs `BROWSER_VERIFIED`.
- Mục lục điều hướng.
- Nguyên tắc `Record_ID`, `Feature_ID`, `FLOW-ID`, cách tìm qua Excel.

### PHẦN I — ADMIN
1. Sơ đồ cây tổng thể tất cả nhóm và submenu Admin; route root/login/dashboard.
2. Danh sách toàn bộ các màn hình (kể cả trang ẩn, modal, error, print) phân theo module với route và role.
3. **Mỗi trang là một mục con riêng:**
   - Tên, Page ID, URI/route, entry points, breadcrumbs, menu, roles/permission, conditions/feature flags.
   - Page layout: header, sidebar, filters, stats cards, tabs, tables, item actions, dropdowns, modals, confirmations, export/import, keyboard shortcuts, empty/loading/error/mobile states.
   - **Bảng controls vi mô**: ID, nhãn, vị trí, trigger, input, request, validation, response, state changes, next nodes. Những nút cùng tên nhưng ở bối cảnh khác phải phân biệt.
   - **API map của trang**: method/path, handler, headers/auth/CSRF, payload, validation, business rules, DB read/write, response, status codes, callers, side effects.
   - Tất cả đường đi vào/ra trang, bao gồm error recovery và redirect theo role.
   - Tác động xuyên module sang Client/Partner/Guest/Finance; nêu Cross-Sheet IDs.
   - Visual inventory: UX cần kiểm tra khi làm đẹp sau này; không chủ quan khẳng định đạt nếu không có screenshot/browser.
   - Source trace: files/lines/test thực tế; TODO/gaps khi không xác minh được.
4. **Business flow catalog**: theo thứ tự `FLOW-ADM-*` và `FLOW-X-*`; mỗi flow có narrative + bảng từng bước: Actor → Trigger → Screen → API → Validation/Authorization → Data → Outcome → Alternate/Failure → Next. Đủ happy/error/edge; tạo sơ đồ bằng text mũi tên hoặc tables khi có thể. Nêu giao dịch nguyên tử, idempotency, audit trail.
5. Danh sách `ORPHAN/BROKEN/DOC_ONLY/UNVERIFIED` ở cuối phần Admin, dùng Record_ID.
6. Coverage audit của phạm vi Admin: routes/static links/controls/APIs/flows đã kiểm kê bao nhiêu; gap bao nhiêu, test/UI evidence bao nhiêu.

### PHẦN II — CLIENT
Cấu trúc **giống hệt Admin**, nhưng chia theo nhóm:
- Marketing/SEO, Home, kho mẫu, chi tiết mẫu, xem thử, trang dịch vụ/FAQ/contact/terms/privacy, error pages.
- Auth/Account, quên mật khẩu/khôi phục, đăng ký, đăng nhập, hồ sơ, quản lý thiệp.
- Studio/Editor schema-driven, Undo/Redo, autosave, history, conflict 409, upload/crop/album, mẫu thiệp, version/publish, custom domain, preview iframe, Live Sync bridge.
- AI Curated: style/palette/AI copy/native-template render, 7-section vs 10-section theo profile thực tế, save/reopen/replay; không diễn giải sai là AI sinh HTML nếu code không làm thế.
- Wedding & guest: trang thiệp công khai, câu chuyện, nhạc, thư mời, map, lịch, RSVP theo nhiều sự kiện, lời chúc, chữ ký, Photo Drop, Live Wall, seating, check-in, QR, Gift, Time Capsule, album, in.
- Partner Portal/Agency, handover/claim, agency orders, domain/branding, commission nếu có.
- Checkout/VietQR/SePay callback, payment status polling, plan tier/entitlement, promotion/coupon, customer support/ticket, email/notifications.
- Background flows thuộc Client: webhook, reminders, scheduled jobs, external callbacks, data retention, backup/restore nếu liên quan.

Đối với mọi template có thật trong `resources/views/client/invitations/`, có bảng chi tiết: slug, tier, route demo/live, path Blade/CSS/JS/schema, section manifest thực tế, `data-sync-target`, CTA/interactions, APIs, assets, breakpoints/overlay/animation, `isEmbed`, error states, roles. Không chỉ ghi “template 1, template 2... đều giống nhau”.

### Cross-report parity
- Mọi `Record_ID` ở Excel có **dòng giải thích tương ứng** hoặc một **bảng đầy đủ** trong phần DOCX cùng phạm vi, không được chỉ nói “xem file Excel”. Với số lượng records rất lớn, được phép dùng bảng tổng hợp các node trong từng trang/luồng với mô tả đủ chức năng và source; vẫn giữ `Record_ID` để tra cứu 1:1.
- Mọi `Flow_ID` xuất hiện trong Excel phải có entry trong DOCX. Nếu dùng chung 2 sheet, DOCX mô tả ở cả hai phần với góc nhìn vai trò riêng và cross-reference rõ.
- Mọi `API` phải xuất hiện trong phần API map của ít nhất một trang hoặc một background flow.
- Tổng số trang/route/API/controls/flows trong hai file phải khớp nhau. Nếu không khớp, **chưa được nộp**.

---

## 07 — ĐẶC TẢ CÁCH MÔ TẢ MỘT LUỒNG NGHIỆP VỤ

Mỗi flow phải có:
1. ID, tên tiếng Việt rõ ràng, phạm vi Admin/Client/Cross, owner, actors, business objective.
2. Điểm bắt đầu thực tế (URL, UI trigger hoặc webhook/job).
3. Tiền điều kiện: auth, role, plan/tier, trạng thái wedding/invitation/order, session, guest token, publication status, network.
4. Dữ liệu vào (form field/API schema), bắt buộc/không bắt buộc, type/length/enum, valid/invalid.
5. **Từng bước theo thứ tự thời gian**: màn hình nào, control nào, request nào (method/URI/handler), thao tác DB nào, trạng thái UI nào, điểm rẽ nhánh.
6. Dữ liệu ra, redirect/next page, các bản ghi được sinh/sửa/xóa và người nhận được thông báo.
7. Chi nhánh `happy`, `validation error`, `permission denied`, `not found`, `expired`, `duplicate submit`, `stale data`, `partial failure`, `offline`, `retry`, `timeout` và nhánh khác có trong code.
8. Invariants: multi-tenant isolation, exact amounts, no duplicate check-in, RSVP seats, draft/published separation, immutable ledger, user consent, etc. Chỉ mô tả đúng code đã có; chỗ thiếu phải ghi risk.
9. Concurrency/replay/idempotency: khóa transaction nào, unique index nào, key nào, browser tab conflict.
10. Bên Admin có quan sát hoặc can thiệp gì, có audit trail/ticket/notification/rollback không?
11. Test evidence: tên file/test, không chỉ “test passed”.
12. Kết luận `RUNTIME_VERIFIED` / `STATIC_VERIFIED` / `NOT_VERIFIED` và rủi ro cần QA trước khi sửa UI.

**Không được vẽ sơ đồ tuyến tính giả** cho flow có nhánh rẽ: phải có decision points và trường hợp quay lại.

---

## 08 — NHỮNG LUỒNG MẪU BẮT BUỘC PHẢI TÌM VÀ ĐỐI CHIẾU (KHÔNG TỰ GIẢ ĐỊNH ĐÃ CÓ)

### Admin
- Truy cập `/admin` → login/throttle → role-based dashboard → command palette/global search → logout/session expired.
- Users list → search/filter/sort → customer 360 → lock/unlock/reset role/PII lens/DSAR/anonymize → audit trail.
- Templates list → scan/import/activate/deactivate/featured → schema/assets integrity → template quality passport → preview → release/version/rollback nếu có.
- Invitations list → details → preview/lock/unlock/extend/change template → publish/impact simulation/rehearsal → staff/customer notifications.
- Orders → bank webhook reconciliation → approve manual, refunds, cancel, renew, disputed transactions → ledger/audit; đừng nhầm `refund_requested` với `refunded`.
- Plans & Coupons → edit → applicable entitlements → checkout pricing → existing-order price snapshot.
- Guests/Seating/RSVP → filter/count → assign seat → reception rescue/check-in → capacity and live dashboards.
- Support → ticket list → assign/SLA/priority/internal note/customer replies → resolve/reopen → customer sees correct state; check relation `SupportTicketReply.user` and 500 regression.
- Jobs/Media/Storage → queue failed/retry/purge → reports/errors/logs.
- Analytics/Action Inbox/Readiness Radar/Finance Detective → data source → drill down → action confirmation → feedback.
- Content/SEO/Newsletter/Privacy/Marketing/AI costs nếu thực tế có.

### Client/Public/Partner
- Home → gallery → filters/tier → details → live preview → use template → Free/premium auth gate → Studio.
- Register/login/password reset/account → claim guest-created wedding/session token → ownership controls.
- Studio create/edit → schema fields → upload/rename image/crop/select music → live bridge → local autosave → server autosave → 409 conflict → switch template → publish → review actual public invitation.
- AI Curated native templates: 6 names/style/palette → AI copy → canonical template reuse → progressive reveal only first generate → save/reopen → 7-section profile vs normal 10 section; verify current implementation, no fabricated claims.
- Template published/locked/expired/password-protected → owners vs strangers → guest token → RSVP available vs unavailable.
- Personalized invitation → sharing via Zalo/SMS/email/link/QR → delivery lifecycle and actual `sent` status.
- Multi-event invitations → guest group scoping → RSVP by event → change responses → guest count by event → seating and check-in propagation.
- Seating planning → drag/drop seats → capacity → check-in QR/scan/web camera → double-scan/retry/offline → live wall data.
- Wishes/signature/comment moderation → photo drop/gallery → live wall render → owner moderation and deletion propagation.
- VietQR gifts/order payment/SePay webhook → entitlement activation → renew/refund/expiry/notification.
- Photo album, keepsake, time capsule, anniversary reminder, thank-you, downloadable archive and recovery.
- Partner Portal agency onboarding → make invitations → handover code → client claim → reassigned ownership/commission/privacy.
- Print companion card/A4 QR → PDF/print output/page settings/scanability if implemented.
- Support ticket client create → upload/receive response → staff reply → client view; check role, PII and activity log.
- Scheduled emails and links generated by background jobs; open actual deep link and expiry branches.

Các đề án 159–166 và `IDEA-*/TASK-VAL-*` trong nhật ký **chỉ là checklist khám phá**: mỗi mục phải tìm ra route/handler/UI/DB thật hoặc đánh `DOC_ONLY`.

---

## 09 — MA TRẬN API RIÊNG BÊN TRONG 2 SHEET

Không được tạo sheet API riêng. Với **từng API**, `Record_Type=API/WEBHOOK`, đầy đủ:
- Identity: `Record_ID`, `HTTP_Method`, `URI_Pattern`, `Route_Name`, `Controller_Handler`, `Service_Job_Event`.
- Source of request: UI control IDs + page IDs + background job IDs + webhook external provider.
- Dữ liệu vào: route parameters, query parameters, headers, JSON/form-data, CSRF/signature, auth token/PIN, file uploads.
- Middleware/Auth: role, owner, entitlement, wedding visibility, request rate limit, idempotency/replay guard.
- Validation: field types, defaults, max length/file size, nested arrays, date/time handling, optional vs required.
- Read/write: models/tables, transaction scope, locks/unique keys, related effects.
- Output: HTTP status, JSON schema/redirect/file response, UI mapping, cache headers.
- All errors: 401/403/404/409/410/422/429/500... **chỉ ghi HTTP code được code/contract hỗ trợ**.
- Action side effect: queue/email/storage/notifications/external API, and DB rollback/retry.
- Every caller: navigation/control/flow IDs; nếu không xác định được caller → mark `ORPHAN_API`/`NOT_VERIFIED` chứ không giấu.
- Dữ liệu cá nhân: payload nào nhạy cảm, xem/mask/log/retention/authorization.
- Test evidence và source chính xác.

**Nếu một API không có screen, vẫn phải có record, owner module và parent group.** Nếu một screen gọi API không có route, ghi `BROKEN` kèm source.

---

## 10 — BỘ KIỂM ĐỊNH ĐỘ BAO PHỦ: KHÔNG CHẤP NHẬN “KHÔNG SÓT” BẰNG LỜI NÓI

Phải lập phép đối chiếu cơ học (dùng công cụ đọc code, không sửa source):

1. **Route parity:** `R = số route METHOD+URI+NAME ở runtime`, `I = số route canonical đã vào workbook`, `U = route chưa gắn Record_ID`. Chỉ đạt nếu `U=0`; những vendor routes cũng phải phân loại (internal/external/irrelevant với lý do).
2. **Endpoint parity:** tất cả action nhận HTTP/GET/POST/PUT/PATCH/DELETE, webhook, asset, export, signed URL; không có API nào không được gắn module/flow hoặc đánh orphan rõ ràng.
3. **UI navigation parity:** tất cả href/form action/JS navigate/iframe/deep link trong files active có record/link ID hoặc một lý do loại trừ có nguồn; không phải regex thô là đủ, cần xem xét JS build dynamic routes.
4. **Control parity:** mỗi clickable/interactive element, submit input, keyboard shortcut, delegated listener active phải có `CONTROL/FIELD/ACTION` record hoặc component canonical + usage record.
5. **Flow parity:** mỗi controller mutation/API state-changing phải có ít nhất một flow consumer hoặc ghi `ORPHAN/UNREACHABLE`. Các flow trong DOCX phải có record trong Excel.
6. **Ownership parity:** mọi `Admin/Manager/Staff/Partner/Owner/Guest` action quan trọng có mô tả quyền/403/404 và đường điều hướng tương ứng.
7. **Template parity:** số mẫu thiệp active trong DB/seeder/runtime vs thư mục source, schema, asset URL, sections; chênh lệch phải ghi `DOC_STALE`/`BROKEN`/`UNVERIFIED`.
8. **Data parity:** business mutations phải map DB read/write, transaction, source API và UI feedback; không để “lưu vào DB” vô nghĩa.
9. **Test parity:** mỗi core flow có test path hoặc `NO_TEST`; không nhận câu “1.502 tests passed” làm bằng chứng riêng cho từng node.
10. **DOCX/XLSX parity:** every `Record_ID`, `Flow_ID`, `API_ID` cross-file hợp lệ, không có ID orphan trong tài liệu.
11. **Cross-sheet parity:** tất cả `Cross_Sheet_Refs` tồn tại ở đúng sheet; mỗi flow liên thông cần mô tả ở cả hai phần Word.
12. **Evidence honesty:** số `STATIC_VERIFIED`, `RUNTIME_VERIFIED`, `DOC_ONLY`, `BROKEN`, `UNVERIFIED` được thống kê rõ; phải đánh nhãn `NOT_RUNTIME_VERIFIED` nếu không chạy browser/DB được.

Lập bảng coverage ở **cuối mỗi phần DOCX**, có các số trên. Trong Excel, ghi nhóm `GROUP` coverage summary records ở cuối sheet để không cần tạo sheet tổng hợp.

**NGƯỠNG NGHIỆM THU:** Route unaccounted `0`; source links unaccounted `0`; API unowned `0` (or explicitly ORPHAN); DOCX/XLSX ID mismatch `0`. Không bắt buộc mọi chức năng phải PASS; bắt buộc mọi điểm không xác minh được phải **được liệt kê trung thực**.

---

## 11 — QUY TRÌNH QUÉT THEO TỪNG VÙNG, CÓ CHỐT KIỂM SOÁT

**GATE 0 — Snapshot & Discovery**
- Đọc văn bản, skill, cấu hình runtime. Lấy commit, branch, route inventory, page tree ban đầu, modules. Phân loại roles.

**GATE 1 — Admin Deep Crawl**
- Đi từ admin login/sidebar rồi từng module. Với mỗi page: kiểm kê route, controls, APIs, validators, middleware, services, DB, failure and navigation. Đánh giá tất cả nested pages/hidden features.

**GATE 2 — Client Deep Crawl**
- Marketing → auth → account → partner → studio → AI curated → invitation/public → guests/RSVP → seating/check-in/live wall → payment/support → memory/print. Kiểm kê templates riêng từng slug.

**GATE 3 — Backend-only Discovery**
- Bổ sung API/webhook/assets/console/jobs/notification/background flows không có link hiển thị. Nối sang các record đã có, không tạo sheet thứ ba.

**GATE 4 — Cross-module Flow Reconstruction**
- Thẩm định 20 luồng E2E gốc, 50 bất biến, các đề án mới và mọi flow đã phát hiện thêm; ghi các bước có thể phục nguyên từ code, nhánh lỗi và điểm nối Admin/Client.

**GATE 5 — UI & Behavior Reality Check**
- Nếu có môi trường local/sandbox, mở trang thật dưới tài khoản giả ở từng vai trò, kiểm tra menu/link/iframe/modal/forms một cách không phá dữ liệu và quan sát Network/Console; desktop/mobile representative breakpoints. **Không được thao tác payment/refund/send/production**, dùng test fixtures và mock provider hợp lệ.
- Ảnh chụp phải đúng màn hình và trạng thái; login redirect không được coi là ảnh trang dashboard/admin sau đăng nhập. Không dùng browser screenshot để thay route/source audit.

**GATE 6 — Produce XLSX + DOCX**
- Tạo workbook 2 sheets, DOCX 2 parts theo schema; dữ liệu được sinh từ inventory đã xác minh, không tô xanh hàng loạt. Dùng công cụ soạn tài liệu và bảng tính có sẵn; không thêm thư viện dự án. Nếu thiếu capability tạo .docx/.xlsx, báo chặn, không làm file giả (CSV đổi đuôi).

**GATE 7 — Machine & Human Validation**
- Mở và đọc lại cả hai file, xác nhận XLSX có đúng 2 sheets và số records, DOCX có đúng 2 phần chính, UTF-8 tiếng Việt, heading, tables, hyperlinks, mapping IDs, không lỗi cắt bảng/tràn trang.
- Báo cáo bằng những con số định lượng: routes, active page patterns, UI controls, API calls, jobs, workflows, flows, unverified, broken/orphan, documented-only; ghi rõ nguồn đếm.

---

## 12 — MẪU PHÂN RÃ MỘT TRANG (CHỈ LÀ VÍ DỤ MINH HỌA, KHÔNG PHẢI KẾT LUẬN SOURCE)

Ví dụ giả định trang Client `Quản lý RSVP`:

```
CLI-GRP-0001   Client > Thiệp cưới > RSVP
  CLI-PG-0100  Trang thiệp /thiep/{slug}
    CLI-CMP-0101  Section RSVP
      CLI-CTL-0102  Nút "Có tham dự"
        CLI-ACT-0103  Chọn attending → mở form
          CLI-FLD-0104  Tên khách mời
          CLI-FLD-0105  Người lớn
          CLI-FLD-0106  Trẻ em
          CLI-CTL-0107 Nút xác nhận
            CLI-STEP-0108 Validate
            CLI-STEP-0109 POST /thiep/{slug}/rsvp
            CLI-STEP-0110 Check guest token + event scope
            CLI-STEP-0111 DB transaction/response
            CLI-STATE-0112 Success/422/403/409/410
    CLI-CTL-0113  Nút "Không tham dự"
    CLI-CTL-0114  Nút xem thông tin bàn tiệc
```

Với `CLI-API-0042` canonical cho `POST /thiep/{slug}/rsvp`, map `Referenced_From=[CLI-CTL-0107, ...]` và trong flow `FLOW-X-RSVP-001` ghi ít nhất happy/validation/expired/duplicate/owner-not-allowed tùy code thật.

**Ví dụ không được tự ghi vào file bàn giao** nếu route/UI/fields khác với source; mẫu chỉ minh họa mức chi tiết và cấu trúc Record_ID/Parent_ID.

---

## 13 — CÁC LỖI THƯỜNG GẶP CẦN CHỦ ĐỘNG PHÒNG TRÁNH

- “Đã scan 100 routes” nhưng bỏ 1 API route nằm trong closure/registerServiceProvider.
- Chỉ lấy sidebar và bỏ `route('...')` trong view/JS, link preview iframe, link email, URL QR.
- Một route GET/POST chung URL bị đếm thành 1.
- Bỏ qua các feature bị giấu bằng role/tier/draft/locked/expired status.
- Ghi `user can update` nhưng không phân biệt GET edit form, POST update, autosave and publish action.
- Bỏ qua các client page dành cho khách vãng lai, guest và Partner Portal vì nhầm tất cả thuộc Admin.
- API tồn tại nhưng không có UI consumer mà được đánh `PASS`.
- UI button nhìn đẹp nhưng JS không gắn listener vẫn bị đánh “đã hoạt động”.
- Coi webhook processing như một trang UI, không chỉ ra được order/wedding state mutation.
- Chỉ kiểm tra thiệp ở trạng thái tĩnh, bỏ qua mở phong bì, iframe embed, interactive gallery, 7 vs 10 sections, re-open/reload.
- Coi ảnh chụp 403/login là ảnh trang Admin đang được test.
- Chỉ chụp 375px rồi khẳng định 320px/430px/iPad/desktop đã chuẩn.
- Trích xuất thông tin khách thật vào DOCX/Excel hoặc log token/API keys.
- Đánh `RUNTIME_VERIFIED` từ việc đọc file source; đây chỉ là `STATIC_VERIFIED`.
- Thay UI bằng suy đoán, thay đổi code trong khi nhiệm vụ chỉ là tạo inventory.
- Đánh `VERIFIED` bằng log test cũ thuộc commit khác.
- Phát sinh file `.xlsx` chỉ có danh sách route mà không có `FLOW_STEP`, `CONTROL`, `API`.
- Tạo tài liệu Word dài nhưng không có link ID tương ứng từng dòng Excel.
- Dừng ở 95% mà báo 100%; đừng đánh lừa. Khi thời gian/giới hạn phiên kết thúc, ghi rõ segment đã quét và phần chưa quét, đừng hoàn tất giả.

---

## 14 — OUTPUT CONTRACT / BÁO CÁO CUỐI CÙNG

Bắt buộc tạo và đọc lại **chính xác hai tệp**:

```
E:\Project\Laravel\mayhen.vn\docs\system-map\MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx
E:\Project\Laravel\mayhen.vn\docs\system-map\MAY_HEN_COMPLETE_SYSTEM_MAP.docx
```

Dữ liệu workbook duy nhất:

```
Workbook: MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx
  Sheet 1: Admin
  Sheet 2: Client
```

Word:

```
MAY_HEN_COMPLETE_SYSTEM_MAP.docx
  Trang bìa • Mục lục • Quy ước mã định danh
  PHẦN I — ADMIN
    Cây hệ thống > Toàn bộ trang > Controls > API maps > Flows > Gaps/Coverage
  PHẦN II — CLIENT
    Cây hệ thống > Toàn bộ trang > Controls > API maps > Flows > Gaps/Coverage
```

Khi bàn giao, báo cáo:
1. `Snapshot_ID`, branch/commit, môi trường thực sự khảo sát.
2. Số lượng `Admin` và `Client`: page patterns, URL patterns, routes, APIs, active UI controls, forms/fields, modals/tabs, jobs/webhooks, business flows, branch paths, error states, link ngoài, template count.
3. Số `RUNTIME_VERIFIED`, `STATIC_VERIFIED`, `DOC_ONLY`, `NOT_VERIFIED`, `ORPHAN`, `BROKEN` và điểm mâu thuẫn giữa PRD/code.
4. Các kiểm tra parity: route unaccounted, API unowned, links unaccounted, broken ID, DOCX↔XLSX mismatch, cross-sheet broken links — phấn đấu tất cả đều bằng 0, nếu không phải công khai lý do.
5. Cơ sở khẳng định kiểm kê hoàn chỉnh: những nguồn đã quét, những nguồn không truy cập được, browser run đã/không chạy.
6. **Không sửa mã nguồn ứng dụng**. Nếu quy tắc dự án bắt buộc cập nhật `context.gemini.md` chỉ khi sửa code trong repo, không tự phình context bằng transcript quét tài liệu; báo cáo artifact paths nếu cần theo quy tắc.
7. Không tự `git commit`, `git push`, `deploy`.
8. Gợi ý thứ tự sửa UI sau này dựa trên rủi ro và mức độ sử dụng **nhưng chỉ là roadmap hậu kiểm kê, chưa triển khai**.

### Định nghĩa DONE
Chỉ được nói DONE khi **hai tệp thực tồn tại, mở được, đúng cấu trúc, mọi route/API/link/UI action đã được đưa vào inventory hoặc đánh gap có truy vết**, và Word/Excel dùng chung hệ định danh. Nếu không đủ thời gian để kiểm kê toàn bộ, nộp bản tiến độ trung thực với `NOT_VERIFIED`, không dùng từ “100%”.

**BẮT ĐẦU NGAY: thực hiện GATE 0 → GATE 1 → GATE 2 → GATE 3 → GATE 4 → GATE 5 → GATE 6 → GATE 7. Không chỉ viết kế hoạch rồi dừng.**
