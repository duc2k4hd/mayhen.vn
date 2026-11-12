# MÂY HẸN — MASTER PROMPT: DOCUMENTATION CENTER PRODUCT PLAN & CONTENT ARCHITECTURE
## Trung tâm hướng dẫn sử dụng `/docs/` và `/admin/docs/` — từ bản đồ chức năng đến tài liệu ai cũng đọc hiểu

**Loại nhiệm vụ:** Khảo sát repository thật, đọc mẫu giao diện đã cung cấp, lập kế hoạch sản phẩm, kiến trúc nội dung, UX/UI, dữ liệu và roadmap triển khai CHI TIẾT; trước mắt **KHÔNG chỉnh sửa mã ứng dụng**.

**Workspace đang dùng:** ưu tiên repo thực tế được mở trong IDE và xác minh `git rev-parse --show-toplevel`.
**Đường dẫn thiết kế do chủ dự án cung cấp:** `D:\Project\Laravel\mayhen.vn\docs\templates`.
**Lưu ý đường dẫn:** các trao đổi trước từng dùng `E:\Project\Laravel\mayhen.vn`. Không được tự giả định D: và E: giống nhau hoặc chép nhầm bản cũ. Kiểm tra đường dẫn tồn tại và Git HEAD; nếu mẫu ở D: trong khi workspace ở E:, đọc D: làm **nguồn thiết kế tham chiếu** nhưng chỉ lập kế hoạch áp dụng cho workspace thực tế. Nếu không truy cập được, ghi `DESIGN_SOURCE_UNAVAILABLE` và những gì chưa đánh giá, không tự sáng tạo thiết kế thay thế rồi tuyên bố giống mẫu.

**Hai trung tâm trợ giúp bắt buộc:**
- **Client Help Center:** `/docs/`, dành cho người dùng mới, cô dâu chú rể, khách mời, người phụ trách tiệc cưới và đối tác theo đúng phạm vi chức năng thực tế.
- **Admin Knowledge Center:** `/admin/docs/`, dành cho nhân viên mới, quản lý, kế toán, chăm sóc khách hàng, quản trị viên và người tiếp nhận/chuyển giao vận hành. Phải được bảo vệ bằng các cơ chế xác thực và phân quyền quản trị hiện hữu.

**Tầm nhìn:** Bất kỳ người nào chưa từng sử dụng Mây Hẹn cũng có thể tự hiểu cách thao tác đúng, tự tìm giải pháp khi gặp lỗi; một admin mới nhận bàn giao có thể học hệ thống, xử lý công việc an toàn và hiểu hậu quả của mỗi hành động mà không cần người phát triển đứng cạnh giải thích. Văn phong phải giống **hướng dẫn do đội ngũ Mây Hẹn viết cho người thật**, KHÔNG giống nội dung AI được kéo dài bằng tính từ.

---

## 0. RANH GIỚI NHIỆM VỤ VÀ NGUYÊN TẮC BẤT BIẾN

1. **Lượt này chỉ lập kế hoạch triển khai hoàn chỉnh**: Đọc mã nguồn và tài liệu, đối chiếu danh mục tính năng, thiết kế cấu trúc tài liệu, lập hồ sơ nội dung, sitemap, component và chiến lược kiểm thử; chưa tạo route/controller/view/DB/migration, chưa sửa code website. Chỉ tạo/cập nhật các file kế hoạch trong `docs/docs-center/` bằng công cụ sửa file trực quan. Khi có chỉ thị thực hiện kế hoạch ở lượt tiếp theo, mới triển khai tuần tự.
2. Không lấy lời cam kết “166 đề án hoàn thành”, “1.502 tests PASS”, hay checkbox `[x]` làm chứng minh một chức năng đang dùng được. Kiểm tra route/controller/Blade/JS/permission/code thật và ghi trạng thái chứng cứ.
3. **Nguồn nội dung chính là bản đồ hệ thống đã lập**: tìm `docs/system-map/MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx` (đúng 2 sheet `Admin`, `Client`) và `.docx` tương ứng, hoặc vị trí tài liệu đã được AI tạo thật. Kết nối `Record_ID`, `Flow_ID`, parent-child, API, role, trạng thái, màn hình, đường dẫn liên quan. Nếu file không có hoặc quá cũ, tự khảo sát runtime routes và source rồi ghi `NEEDS_REINVENTORY`, không được tự điền giả.
4. **Đọc thiết kế mẫu thật trong `docs/templates/` trước bất kỳ quyết định UI nào**: liệt kê tên file, loại file, cấu trúc, asset, font, layout, responsive, tương tác và mức độ có thể tái sử dụng. Có thể có mẫu trang chính và trang chi tiết trong các file riêng. Không tự đổi phong cách vì chủ dự án đã cung cấp thiết kế.
5. Dùng stack đã có: Laravel 13/Blade + CSS thuần + JavaScript thuần ES Modules; không React/Vue/Tailwind/Bootstrap/jQuery/Alpine/GSAP, không CDN; không thêm dependency khi chưa được phê duyệt.
6. Namespace CSS: Client docs dùng `mayhen_docs_*` (và lớp layout `mayhen_client_*` nếu dùng shared shell), Admin docs dùng `mayhen_admin_docs_*` (đáp ứng prefix `mayhen_admin_*`). Không ảnh hưởng các trang còn lại.
7. Nội dung phải tôn trọng người đọc: ngắn, chính xác, có bước làm, không hoa mỹ, không đưa ra lời hứa không có thật, không gọi nhầm nút/nhãn. Cấm viết kiểu “Với hệ sinh thái đột phá của chúng tôi, hãy bắt đầu hành trình trải nghiệm tuyệt vời...”.
8. Không lộ dữ liệu người dùng, URL mời riêng tư, token, API key, địa chỉ/số điện thoại thật, dữ liệu ngân hàng, mã PIN lễ tân. Ảnh minh họa phải dùng tài khoản demo và dữ liệu giả.
9. **Không phát hành mô tả chức năng tương lai như thể đã có**. Nhãn phân loại: `EXISTS_VERIFIED`, `EXISTS_UNVERIFIED`, `DOC_ONLY`, `PLANNED`, `DEPRECATED`, `ROLE_RESTRICTED`.
10. Tài liệu admin tuyệt đối không truy cập được từ `/docs/` hoặc các public docs API/search/index/cache/sitemap; xác thực và authorize ở tầng server, không chỉ giấu đường dẫn bằng CSS.

---

## 1. ĐỌC ĐẦY ĐỦ CÁC NGUỒN TRONG WORKSPACE

### 1.1 Quy tắc dự án
- `context.gemini.md` — đọc đầu và **cuối** để nắm cập nhật mới nhất; file dài không được chỉ đọc 200 dòng đầu.
- `AGENTS.md`, `CLAUDE.md`, `.ai/rules/` (nếu có).
- `.agents/skills/mayhen-admin/SKILL.md`, `mayhen-ui-ux`, `mayhen-brand-design`, `mayhen-blade`, `mayhen-css`, `mayhen-javascript`, `mayhen-laravel-backend`, `mayhen-project-architecture`, `mayhen-testing-quality`, `mayhen-auth-security`, `mayhen-code-review`, `mayhen-seo`, `mayhen-performance`, `mayhen-guest-rsvp`, `mayhen-visual-editor`, `mayhen-invitation-standards`, `mayhen-partners` và các skill cần thiết khác.

### 1.2 Tài liệu sản phẩm và kiểm kê
- Master PRD: `docs/MASTER PROMPT MAYHEN.VN.docx`, `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`.
- `docs/admin-vnext/`, `docs/qa/`, `docs/product/`, `docs/design/`, `docs/architecture/`, `docs/implementation/`, `docs/research/`, `docs/database/`.
- `docs/MAY_HEN_PREMIUM_FEATURE_CATALOG.md`, `MAY_HEN_DOMAIN_INVARIANTS_AND_STATE_MACHINES.md`, `MAY_HEN_CROSS_MODULE_E2E_MATRIX.md`, các bản kiểm toán, roadmap và tài liệu release gần nhất.
- **Bản đồ 2 sheet và Word** từ bước vừa hoàn thành. Nếu có hơn một bản, xác minh Git HEAD/timestamp và chỉ định bản được coi là baseline.

### 1.3 Mã nguồn sống — bắt buộc đọc chéo
- `php artisan route:list --json` nếu runtime hỗ trợ, kết hợp `routes/web.php`, `routes/admin.php`, route phụ, bootstrap, middleware, policies/gates.
- `app/Http/Controllers/{Admin,Client,Partner}`, Requests, Services, Models, Jobs, Events, Listeners, Notifications, Mail, Console.
- `resources/views/admin/`, `resources/views/client/`, invitation templates và schema tương ứng; `public/assets/admin/`, `public/assets/client/`.
- Forms, labels, CTA và empty/error states nằm trong Blade; các sự kiện, `fetch`, `postMessage`, autosave, polling, clipboard nằm trong JS; validation/permission nằm trong PHP.
- Tests: chỉ dùng để biết luồng dự kiến và điều kiện biên; không coi tên test là chứng minh UI đã hiện đúng.

### 1.4 Kết quả khảo sát có thể kiểm tra
Ghi snapshot: repo path, branch, HEAD, thời điểm, trạng thái working tree, số route Admin/Client, số màn hình, số action/API, số chức năng đã xác minh, số DOC_ONLY và số thiếu thông tin. Không ghi biến bí mật hoặc bản ghi khách thật. Có đường dẫn source + method cụ thể cho nhận định quan trọng.

---

## 2. PHÂN LOẠI ĐÚNG HAI KHÔNG GIAN TÀI LIỆU

### 2.1 `/docs/` — Client Help Center
- Mục tiêu: người chưa biết gì có thể tự tạo thiệp, quản lý, gửi, nhận RSVP và xử lý khó khăn.
- Chia đối tượng thực tế: người mới, cô dâu/chú rể, khách mời, lễ tân, partner/studio nếu portal thuộc Client.
- Những hướng dẫn cần login vẫn có thể được xem công khai ở mức **khái quát**, nhưng nội dung có token/URL riêng tư, screenshot dữ liệu cá nhân phải tránh; nếu bài mang tính riêng tư thì dùng cổng đăng nhập tương ứng.
- Với cùng một chức năng, một bài “Làm thế nào để...” cho người dùng thường; bài kỹ thuật cấu hình nội bộ chỉ nằm ở Admin.

### 2.2 `/admin/docs/` — Admin Knowledge Center
- Mục tiêu: đào tạo, tiếp nhận bàn giao, xử lý sự cố, kiểm soát nghiệp vụ; **không chỉ mô tả màn hình Admin**.
- Nội dung được phân quyền theo role/permission thực tế (`staff`, `manager`, `admin`, ...); không mặc định toàn bộ admin staff thấy hướng dẫn thao tác tài chính, đặt lại mật khẩu, ẩn danh dữ liệu hoặc bí mật tích hợp.
- Phân biệt bài tác nghiệp ngày thường, bài quy trình nhạy cảm và bài sự cố khẩn cấp; nêu rõ khi nào dừng, ai được phép phê duyệt, cách lưu bằng chứng và quy trình đối chiếu.
- Hướng dẫn bàn giao phải đủ để nhân sự mới tiếp nhận được: mục tiêu, phạm vi quyền, thao tác mẫu, cách đọc trạng thái, sai sót thường gặp, cách nhận hỗ trợ và checklist bàn giao.

### 2.3 Liên kết qua hai trung tâm
- Trang Client có thể có link “Liên hệ hỗ trợ” hoặc bài liên quan, tuyệt đối không dẫn người ngoài vào trang docs Admin chứa dữ liệu nội bộ.
- Một luồng tồn tại ở cả hai môi trường: bài Client mô tả thao tác người dùng; bài Admin mô tả cách kiểm tra và hỗ trợ, **không copy nguyên nội dung lặp**. Liên kết bằng `Feature_ID` và `Flow_ID`, không lộ endpoint riêng tư ra public.
- `docs/` trong source là thư mục tài liệu phát triển; URL `/docs/` là trang web. Kiểm tra xung đột route, đường dẫn asset, sitemap/robots trước khi quy hoạch.

---

## 3. PHẢI LẬP CÂY HƯỚNG DẪN CHI TIẾT CHO TỪNG TRANG, TỪNG CHỨC NĂNG

Lập sơ đồ cây **Trang → Nhóm chức năng → Hành động → Bài hướng dẫn**. Không tạo 1 bài cho một trang khổng lồ 30.000 chữ, cũng không tách mỗi nút đơn giản thành bài rỗng. Tách bài theo tác vụ có ý nghĩa mà người đọc thật sẽ tìm kiếm.

### 3.1 Client — các nhánh PHẢI khảo sát (không coi là đã tồn tại trước khi xác minh)
1. **Bắt đầu với Mây Hẹn**: Mây Hẹn dùng để làm gì, quy trình tạo–gửi–nhận RSVP, tạo thiệp đầu tiên, dùng trên điện thoại, tình huống không cần đăng nhập/cần đăng nhập.
2. **Tài khoản và bảo mật**: đăng ký, đăng nhập, quên mật khẩu, cập nhật tài khoản, đăng xuất, quản lý phiên và quyền thiệp đã tạo ở chế độ khách.
3. **Khám phá mẫu thiệp**: tìm kiếm, lọc, xem trước, sự khác nhau Free/Plus/Pro theo quyền lợi thật, chọn đúng mẫu, chuyển mẫu, mẫu bị ẩn/ngưng hỗ trợ.
4. **Studio / thiết kế thiệp**: form theo section, nhập tên cha mẹ, ngày giờ/sảnh, ảnh, crop, thư viện ảnh, nhạc, âm lịch, bản đồ, theme, monogram, timeline, undo/redo, autosave, xung đột nhiều tab, lỗi tải ảnh, bản nháp/xuất bản, mở lại sửa.
5. **AI tạo thiệp**: 6 tên, phong cách, màu người dùng chọn, AI chỉ soạn chữ, sử dụng đúng template gốc, lựa chọn 7 section profile thực tế, xem kết quả, bỏ qua/xem lại hiệu ứng, cập nhật sau tạo, hạn chế nội dung chưa có dữ kiện.
6. **Quản lý thiệp**: danh sách, đổi tên/slug nếu có, nhân bản, khóa/quyền riêng tư, hết hạn, gia hạn, xóa, xem bản công khai, gửi link, mã QR, chế độ xem của khách.
7. **Sự kiện và khách mời**: tạo sự kiện, phân nhóm khách, thông tin từng khách, link mời cá nhân hóa, giới hạn số khách, gửi/nhắc thiệp, RSVP cho từng sự kiện, chỉnh RSVP, thống kê số người lớn/trẻ em và tình huống một khách nhiều sự kiện.
8. **Bàn tiệc và đón tiếp**: tạo/sửa/xóa bàn, xếp bàn, sức chứa, lọc khách chưa được xếp, QR check-in, PIN lễ tân, khách phát sinh, sửa nhầm check-in, làm việc nhiều thiết bị, mất mạng.
9. **Trải nghiệm khách mời**: mở thiệp, nhạc, đếm ngược, bản đồ, xác nhận tham dự, sửa phản hồi nếu có, lời chúc, chữ ký, quy định hiển thị và thông báo khi thao tác thất bại.
10. **Album và kỷ niệm**: gallery, Photo Drop, duyệt ảnh, Live Wall, ký tên, sổ lưu bút, Memory Vault, Time Capsule, cảm ơn sau cưới, xuất/đóng gói dữ liệu.
11. **Gói dịch vụ & thanh toán**: xem bảng giá, nâng gói, áp coupon, VietQR/SePay, chờ xác nhận, thanh toán thiếu/thừa, hóa đơn nếu có, gia hạn, yêu cầu hỗ trợ/hoàn tiền.
12. **Đối tác / Studio**: tạo và bàn giao thiệp, link/claim code, quản lý khách studio, quyền đại diện, custom domain, hoa hồng nếu portal có quyền.
13. **Khắc phục sự cố thường gặp**: không thấy ảnh, không nghe nhạc iPhone, không mở thiệp, mất kết nối, không lưu nháp, không nhận thông báo, đăng nhập không được, thanh toán chưa cập nhật, link bị khóa/hết hạn.
14. **Dữ liệu và quyền riêng tư**: quyền xem ảnh/RSVP/sổ mừng, chia sẻ link an toàn, cách tải dữ liệu, liên hệ yêu cầu xóa/ẩn danh tùy chức năng có thật.

### 3.2 Admin — các nhánh PHẢI khảo sát
1. **Làm quen Admin**: đăng nhập an toàn, cấu trúc menu, dashboard, thông báo, tìm kiếm, command palette/phím tắt nếu thực sự có.
2. **Người dùng**: tìm hồ sơ, role/permission, khóa/mở khóa, đặt lại quyền, tránh tự khóa admin, Customer 360, truy cập/ẩn PII, xuất dữ liệu.
3. **Thiệp cưới & dự án**: tìm thiệp, trạng thái draft/published/expired/locked, khóa và phục hồi, gia hạn, đổi mẫu, hỗ trợ không thay đổi dữ liệu khách trái quyền.
4. **Kho mẫu & chất lượng**: quản lý mẫu, activate/feature, bản địa hóa assets, xem schema, 10 section versus AI curated profile, template integrity, version, visual QA, release/rollback nếu có.
5. **Khách mời & RSVP**: lọc theo wedding, xem phản hồi, nhóm khách, xuất CSV an toàn, giải quyết trùng phản hồi, đối chiếu số người.
6. **Sảnh tiệc/Reception/Rescue**: check-in, khách chưa có bàn, bàn dự phòng, capacity, xử lý chồng ca, audit và xử lý sự cố ngày cưới.
7. **Đơn hàng & tài chính**: dashboard doanh thu, đơn hàng, trạng thái chuyển khoản, đối soát SePay/VietQR, cấp quyền, chứng từ manual, refund workflow, số dư/tổng hợp, hạn chế quyền kế toán.
8. **Sản phẩm thương mại**: plans, tier, pricing, coupons, renewal, partner commission, promise-to-proof theo thực tế.
9. **Hỗ trợ khách hàng**: tickets, SLA, nhân viên phụ trách, ghi chú riêng, trả lời, VIP 48h, SOP, chuyển ca/bàn giao, các lỗi 500 của ticket detail và cách xử lý.
10. **Công cụ điều hành**: Action Inbox, Readiness Radar, Quality Passport, Simulation/Rehearsal, Change Impact, Delight Recovery, Incident Cards, Operational Replay Sandbox nếu có.
11. **Marketing và nội dung**: SEO/sitemap, CMS, chiến dịch gửi, danh sách thông báo, template tin nhắn, kiểm tra chi phí và khả năng phát hành thực tế.
12. **Đối tác**: hồ sơ studio, bàn giao, quyền sở hữu, hoa hồng và đối soát liên quan.
13. **Hệ thống và cấu hình**: settings, hàng đợi/failed jobs, retry/purge có cảnh báo, storage, asset health, log, cron/scheduler, webhook diagnostics, backup/restore/rollback.
14. **Quyền riêng tư & an toàn**: xuất dữ liệu cá nhân, anonymize, lưu giữ/xóa theo quy định, audit log, truy vết ai làm gì, thao tác bị cấm đối với staff.
15. **Hướng dẫn vận hành và chuyển nhượng/bàn giao Admin**: checklist tiếp nhận, tạo tài khoản nhân viên, phân quyền tối thiểu, ca trực, kiểm tra cấu hình hệ thống, xác minh thanh toán, kiểm tra backup, quy trình ứng cứu P0/P1, xử lý yêu cầu của khách, SOP hậu sự cố, bàn giao cuối ca.

**Bắt buộc:** với mỗi nhánh trên, đối chiếu tất cả Page_ID, Action_ID, API_ID, Flow_ID từ system map. Nếu một nhánh không tồn tại, đánh dấu `NOT_IMPLEMENTED` trong plan và không đưa thành bài thao tác đang hoạt động. Với các chức năng chỉ ở giai đoạn đề án, tạo Backlog riêng và ẩn khỏi tài liệu người dùng.

---

## 4. MỖI BÀI HƯỚNG DẪN PHẢI CÓ MẪU CẤU TRÚC RÕ RÀNG

Đề xuất mô hình bài viết `HelpArticle` (không nhất thiết phải tạo bảng database; lựa chọn sau khi xem code):

- `article_id` ổn định (vd `CLI-STUDIO-IMAGE-001`, `ADM-PAY-REFUND-001`), `space` (`client`/`admin`), `category_id`, `parent_id`.
- `title`: câu hỏi hoặc tác vụ thật, vd **“Cách thay ảnh bìa thiệp cưới”**, không phải “Giải pháp tối ưu hóa hình ảnh đầy cảm xúc”.
- `summary`: một câu người đọc hiểu ngay kết quả.
- `target_audience`, `required_permission`, `prerequisites`, `app_route`, `deep_link`, `related_Page_ID`, `related_Action_ID`, `related_Flow_ID`, `related_API_ID` (API ID là metadata nội bộ, không đưa lên bài public nếu không cần thiết).
- `step_by_step`: số thứ tự và **tên nút thật** trên giao diện hiện tại, kèm kết quả sau từng bước.
- `result_check`: cách biết mình đã làm đúng; nêu rõ trạng thái lưu/đang xử lý/thất bại.
- `common_problems`: dấu hiệu, nguyên nhân có thể, xử lý an toàn và link hỗ trợ.
- `related_articles`, `screenshots[]`, ảnh crop chi tiết nếu cần, `updated_at`, `verified_source_revision`, `review_owner`, `visibility`, `publish_status`, `search_keywords` (gồm từ phổ thông và từ không dấu).
- Đối với thao tác nguy hiểm: `risk_level`, `before_you_begin`, `what_changes`, `rollback_or_recovery`, `requires_approval`, `audit_trail`.

**Dàn bài hiển thị chuẩn cho bài hướng dẫn người dùng:**
1. **Tiêu đề nói đúng việc cần làm**.
2. Mô tả 1–2 câu: bạn sẽ làm gì và kết quả.
3. **Trước khi bắt đầu** (chỉ khi thật sự cần).
4. **Các bước thực hiện**, số bước vừa đủ, viết động từ rõ ràng, một thao tác chính mỗi bước.
5. **Sau khi hoàn tất** — nhìn thấy điều gì để biết thành công.
6. **Nếu gặp vấn đề** — 2–4 tình huống phổ biến theo code thật và cách xử lý.
7. **Xem thêm** — 2–5 liên kết bài liên quan, link đưa về đúng màn hình ứng dụng.

**Phần bài Admin nhạy cảm** thêm hộp cảnh báo trung tính rõ ràng: “Chỉ quản lý có quyền X mới thực hiện được”; “Thao tác này ảnh hưởng đến Y”; “Không thực hiện nếu chưa đối soát Z”. Không làm màu mè, không dùng thuật ngữ chuyên môn vô ích.

### Ví dụ TỐT (kiểu viết cần đạt)
> **Đổi ảnh bìa thiệp cưới**
>
> Mở **Thiệp của tôi**, chọn thiệp muốn chỉnh sửa, rồi bấm **Chỉnh sửa**. Trong phần **Ảnh cưới**, chọn **Ảnh bìa** và tải ảnh mới lên. Căn lại vùng ảnh nếu màn hình yêu cầu, sau đó bấm **Lưu**.
>
> **Kiểm tra kết quả:** mở **Xem trước**. Nếu ảnh chưa đổi, kiểm tra trạng thái tải ảnh đã hoàn tất trước khi lưu.
>
> *Đây chỉ là ví dụ văn phong; phải thay tên nhãn và thứ tự bước bằng đúng giao diện có thật sau khảo sát.*

### Ví dụ KHÔNG ĐƯỢC dùng
> “Bằng việc sử dụng hệ sinh thái sáng tạo không giới hạn, quý khách hàng sẽ được trải nghiệm quá trình quản trị hình ảnh tối ưu, linh hoạt, thông minh và hoàn toàn vượt trội...”

### Chuẩn biên tập tiếng Việt
- Viết như một nhân viên hướng dẫn tận tình, ngắn, lịch sự, bình thường. Hạn chế “quý khách”, “thông minh”, “đẳng cấp”, “tuyệt vời”, “đột phá”, “dễ dàng chỉ với một cú chạm” khi không mang thông tin thực tế.
- Ưu tiên **“Bạn chọn...”**, **“Bấm...”**, **“Hệ thống sẽ...”**, **“Nếu... thì...”**.
- Giải thích từ lạ một lần theo tình huống: RSVP = xác nhận tham dự; “bản nháp” = thiệp chưa xuất bản; “đối soát” = kiểm tra giao dịch với ngân hàng; không bắt người dùng biết API, webhook hay migration.
- Chỉ gọi tên nút, nhãn, menu **đúng hệt sản phẩm thật**. Dẫn vị trí rõ (“Ở thanh bên trái”, “Ở cuối trang”) nếu có kiểm tra trên browser.
- Một bài trả lời **một mục đích chính**; bài tổng quan đưa các link ngắn sang hướng dẫn chi tiết; không nhồi text dài.
- Không suy đoán thời gian xử lý, tỷ lệ thành công, giá bán, hỗ trợ 24/7 hoặc nghiệp vụ chưa có.
- Khi cần hướng dẫn thiết bị/iPhone Safari, nêu sự khác nhau chỉ khi thực tế đã được thử; không đổ lỗi cho người đọc.
- Đừng dùng đoạn code, stack trace, query hay tên class PHP trong bài khách hàng. Tài liệu nghiệp vụ Admin chỉ dùng thuật ngữ kỹ thuật khi thật sự phục vụ xử lý vấn đề.

---

## 5. KHAI THÁC ĐÚNG THƯ MỤC THIẾT KẾ `docs/templates/`

Đầu tiên thực hiện một **UI Reference Audit**, lập bảng:

| File mẫu | Loại (home/detail/asset) | Phần tử chính | HTML/CSS/JS đáng giữ | Trạng thái responsive | Cần chuyển đổi vào Blade | Ghi chú |
|---|---|---|---|---|---|---|

Kiểm tra ít nhất: thanh tìm kiếm, hero, phân loại chuyên mục, card bài, bài phổ biến, menu cây, breadcrumb, sidebar, nội dung bài, table of contents, anchor headings, callout, ảnh/video nếu có, code block nếu cần, thông báo, footer, chia sẻ/copy link, “bài này có hữu ích không?”, prev/next, related articles, mobile drawer, sticky, dark/light nếu mẫu có, keyboard, focus và reduced-motion.

**Quy tắc sử dụng thiết kế:**
1. **Giữ cấu trúc bố cục, nhịp khoảng trắng, màu sắc, typography, icon, animation và microinteraction của mẫu đã cung cấp** ở mức có thể. Không thay bằng một giao diện tự tưởng tượng.
2. Chuyển HTML tĩnh sang Blade & dữ liệu động; xóa nội dung demo/hardcode và nội dung vô nghĩa, tránh clone trùng CSS/JS. Không thay đổi đặc trưng có giá trị của mẫu.
3. Nếu có nhiều mẫu (trang chính + chi tiết) phải kế thừa thành một hệ thống dùng chung tokens/header/aside/search, vẫn giữ nét riêng của từng màn hình.
4. Làm sạch style/script global không scoped; ngăn xung đột với Client/Admin hiện có. Nội dung Admin không bị áp palette của một mẫu thiệp cưới cụ thể.
5. Đề xuất breakpoint: 320, 375, 390, 430, 768, 1024, 1366, 1440, 1920; trên mobile ưu tiên search và nội dung, sidebar chuyển drawer; không có thanh ngang bất ngờ.
6. Đảm bảo WCAG thực dụng: focus-visible, touch target cỡ hợp lý, heading đúng cấp, màu có độ tương phản đủ, aria cho nút đóng/mở, Tab/Escape, không phụ thuộc hover, hình có alt, hỗ trợ `prefers-reduced-motion`.
7. Không chèn CSS/JS inline, không dựa vào CDN/font bên ngoài. Chỉ tái sử dụng asset gốc nếu quyền sử dụng và nguồn hợp lệ.
8. **Không được kết luận “giữ nguyên 100% giao diện” nếu chưa mở thật tất cả file design.** Nếu thiếu thiết kế cho mobile/chi tiết/empty state, mô tả phần cần thiết kế bổ sung, không giả định đã có.

---

## 6. CẤU TRÚC WEBSITE DOCS CẦN LẬP PLAN

### 6.1 Các trang bắt buộc kiểm tra khả năng triển khai
- `/docs/`: trang chính, search, nhóm chủ đề, hướng dẫn bắt đầu nhanh, bài được xem nhiều (chỉ khi có số liệu), FAQ thực sự hữu ích, CTA vào app.
- `/docs/{category}` hoặc hình thức route phù hợp với mã nguồn: category listing và breadcrumb.
- `/docs/{category}/{article-slug}` hoặc tương đương: bài chi tiết, mục lục trong bài, ảnh, related, next/previous, người đọc đánh giá hữu ích.
- `/admin/docs/`: trang chính dành cho admin, đề xuất theo role, hướng dẫn bàn giao, quy trình công việc và khẩn cấp.
- `/admin/docs/{category}`, `/admin/docs/{category}/{article-slug}` hoặc tương đương với permission-aware navigation.
- 404/docs not found; 403 admin; tìm kiếm rỗng, không có quyền, mục chưa xuất bản, bài bị xóa hoặc slug đổi.

**Đây là route pattern dự kiến**, chỉ chốt sau khi đã kiểm tra sự va chạm với route hiện tại, các route wildcard và chính sách SEO. Cấm tự khai báo route catch-all cướp route `/admin/*` khác.

### 6.2 Chức năng docs index
- Search gõ tiếng Việt có dấu/không dấu; ưu tiên kết quả đúng tiêu đề, synonym (RSVP = xác nhận tham dự; Studio = chỉnh sửa thiệp), gợi ý theo nhu cầu thật.
- Sắp xếp theo nhu cầu người dùng, không theo thứ tự controller trong source.
- Card ngắn: tên chủ đề, mô tả một câu, số bài (nếu tính từ dữ liệu thật), icon phù hợp; trạng thái đang xây dựng không được giả thành có bài.
- Hành động “Tạo thiệp”, “Quản lý khách”, “Mở Admin” đặt theo ngữ cảnh và role; tránh CTA không có route.
- Giữ search query/scroll hợp lý khi chuyển qua lại; xử lý loading, zero results và lỗi hệ thống.

### 6.3 Chức năng docs detail
- Breadcrumb, nhãn người đọc/nhóm tính năng nếu cần, tiêu đề rõ, cập nhật lần cuối, thời lượng đọc chỉ khi tính theo nội dung thật.
- Left sidebar có thể thu gọn theo cây category/article; đánh dấu active đúng bài; hỗ trợ mobile.
- Right TOC (nếu bài dài), anchor section, copy direct link, prev/next, cùng chủ đề.
- Screenshot từ UI thật với highlight nhẹ để chỉ đúng vị trí cần bấm; ảnh có chú thích, có thể mở xem lớn trên điện thoại. Khi giao diện đổi, phải biết ảnh nào đã cũ.
- Thông báo “Chức năng này cần đăng nhập”, “Chỉ dành cho quản lý” (nếu bài public được phép xem), không để role bị chặn bấm xong mới báo lỗi bất ngờ.
- Feedback hữu ích/không hữu ích **không lưu PII thừa**, chống spam/trùng nếu dùng backend; nếu không có backend thì phải loại bỏ khỏi kế hoạch MVP, không làm nút giả.
- Liên kết nút **“Mở chức năng này”** đến màn hình thật, tránh giả các ID cụ thể; với deep link dữ liệu cá nhân chỉ route vào danh sách hoặc trạng thái được authorize.

### 6.4 Kiến trúc nội dung
Lập phương án so sánh:
- `file-based` versioned content (đề xuất giai đoạn đầu khi mã nguồn hướng đến ổn định, review qua Git, không cần bảng DB mới), với front matter/schema bảo đảm ID, slug, role, route references; hoặc
- database CMS (chỉ khi thực tế cần biên tập viên không đụng Git, có nhu cầu xuất bản theo quyền, version/review workflow).

Trong PLAN phải chọn **một phương án phù hợp** có lý do và đường nâng cấp, không tự thêm migration nếu chưa cần. Phải giải thích cách sanitize markdown/rich text, xử lý đường dẫn ảnh, canonical slug, slug redirect, kiểm soát cache invalidation, role auth và tránh search leak admin titles.

---

## 7. VÒNG ĐỜI NỘI DUNG: AI KHÔNG ĐƯỢC TỰ BỊA BÀI HƯỚNG DẪN

Mỗi bài đi qua trạng thái:
`DISCOVERED → ROUTE_VERIFIED → WORKFLOW_VERIFIED → DRAFTED → EDITOR_REVIEWED → UI_SCREENSHOT_VERIFIED → PUBLISHED → NEEDS_REFRESH / ARCHIVED`.

- `ROUTE_VERIFIED`: tìm thấy route và code thật.
- `WORKFLOW_VERIFIED`: kiểm tra tên nút, điều kiện, các bước và kết quả qua source, ưu tiên UI thật.
- `DRAFTED`: văn phong người dùng, không phải notes kỹ thuật.
- `EDITOR_REVIEWED`: người viết nội dung/QA xem lại tính dễ hiểu và chính xác.
- `UI_SCREENSHOT_VERIFIED`: nếu có screenshot, ảnh đúng phiên bản và che dữ liệu cá nhân.
- `PUBLISHED`: bài trong menu/search công khai đúng phân quyền.
- `NEEDS_REFRESH`: route/label/flow đổi trong commit mới.
- `ARCHIVED`: chức năng ngừng cung cấp; nếu có link cũ thì redirect hoặc hiển thị thông báo phù hợp.

Tuyệt đối không đánh dấu `PUBLISHED` chỉ vì AI đã sinh đủ nội dung. Không tự đưa screenshot giả, ví dụ số đơn tiền thật hoặc địa chỉ khách. Với API không trực tiếp phục vụ người dùng: đặt trong **metadata/traceability nội bộ** hoặc tài liệu vận hành Admin tương ứng, không đẻ bài hướng dẫn endpoint kỹ thuật cho người dùng phổ thông.

### 7.1 Tự đồng bộ với thay đổi ứng dụng (đề xuất plan, chưa code)
- `Page_ID/Action_ID/API_ID` của System Map → `Article_ID` → đường dẫn hiện tại → file/view/JS/controller ảnh hưởng.
- Khi route đổi, tên CTA đổi, permission đổi, validation đổi hoặc tính năng bị xóa: đánh dấu những bài tương ứng `NEEDS_REFRESH`.
- Cơ chế kiểm tra link nội bộ, ảnh 404, anchor rỗng, role leak, docs stale trước release.
- Không cho AI tự viết lại toàn bộ bài khi có diff nhỏ; chỉ cập nhật đúng hướng dẫn bị ảnh hưởng và review.

---

## 8. PHÂN TÍCH HÀNH TRÌNH HỌC THEO PERSONA

Thiết kế các lộ trình ngắn, đi từ nhu cầu thật, không phải từ cấu trúc database:

**Client A — Người tạo thiệp lần đầu:** Chọn mẫu → nhập tên/ngày giờ → chọn ảnh → xem trước → lưu → xuất bản → chia sẻ → kiểm tra từ điện thoại người nhận.

**Client B — Chủ tiệc chuẩn bị ngày cưới:** Nhập danh sách khách → gửi link cá nhân hóa → theo dõi RSVP → cập nhật số suất → xếp bàn → giao mã lễ tân → đối soát check-in.

**Client C — Khách mời:** Mở link → đọc địa điểm/ngày giờ → xác nhận tham dự → tìm bản đồ → để lại lời chúc / gửi ảnh nếu thiệp bật quyền đó.

**Client D — Đối tác Studio:** Tạo thiệp cho khách → duyệt → bàn giao quyền → xử lý khi khách nhận thiệp → quản lý quyền truy cập sau bàn giao.

**Admin A — Nhân viên hỗ trợ mới:** Đăng nhập → tìm hồ sơ → đọc ticket → xác minh quyền → xem Safe Support Lens (nếu có) → trả lời → cập nhật tình trạng → chuyển ca.

**Admin B — Quản lý:** Xem Action Inbox → ưu tiên đám cưới 48h → xử lý ticket escalated → kiểm tra Rescue → phê duyệt các tác vụ được phép → đọc audit.

**Admin C — Kế toán:** Tìm đơn → đọc bằng chứng webhook/ngân hàng → đối soát → xử lý ngoại lệ hoàn tiền theo đúng trạng thái thực tế → lưu dấu vết; không được hướng dẫn “đánh dấu paid bằng tay” nếu code yêu cầu chứng từ.

**Admin D — Người mới nhận bàn giao toàn hệ thống:** kiểm tra quyền quản trị → thiết lập nhân viên → đối chiếu gói/giá → kiểm tra cấu hình webhook và queue **không lộ secret** → kiểm tra backup thật → chạy checklist vận hành → hiểu quy trình sự cố và liên hệ phụ trách.

Mỗi journey phải có **danh sách bài theo thứ tự**, thời điểm mở bài (entry point), việc có thể làm, xác định kết quả và bài xử lý khó khăn.

---

## 9. MA TRẬN NỘI DUNG & ĐỘ BAO PHỦ (KHÔNG ĐƯỢC CHỈ LẬP SITEMAP)

Tạo bảng editorial coverage có tối thiểu những cột:

`Article_ID`, `Space`, `Category`, `Subcategory`, `Title`, `Reader_Persona`, `Article_Type` (`HOW_TO`, `OVERVIEW`, `TROUBLESHOOT`, `FAQ`, `POLICY_EXPLANATION`, `ADMIN_SOP`, `HANDOVER`), `Priority`, `Parent_Article_ID`, `App_Page_ID`, `Action_IDs`, `Flow_IDs`, `API_IDs_Internal`, `App_Route`, `Doc_URL_Proposed`, `Preconditions`, `Expected_Result`, `Error_Cases`, `Permission`, `Screenshots_Required`, `Source_Files`, `Source_Revision`, `Verification_Status`, `Content_Status`, `Reviewer`, `Dependencies`, `Est_Complexity`, `Notes`.

**Nguyên tắc atomic coverage:**
- Mỗi feature/action người dùng có thể chủ động thực hiện phải có **ít nhất một bài** giải thích hoặc một mục rõ ràng trong bài tổng hợp hợp lý.
- Hành động có ảnh hưởng tài chính/PII/khách mời/ngày cưới phải có bài riêng hoặc SOP, có nhánh lỗi và nguyên tắc khôi phục.
- Mỗi route/page phải có chỗ trong cây `Page → Docs category/article` hoặc ghi `NO_USER_DOC_NEEDED` với lý do (ví dụ callback/webhook/asset/status API) và link đến bài nghiệp vụ gián tiếp.
- API không tạo bài public riêng nhưng phải liên kết `API_ID → Action/Flow → Article` trong ma trận.
- Các thao tác có hai role khác nhau phải có hướng dẫn và quyền tương ứng; Client và Admin không gộp chung vì “cùng tên tính năng”.
- Nếu System Map chứa 500 chức năng, không mặc định phải có đúng 500 bài: ưu tiên **bao phủ tác vụ có ý nghĩa**, tránh tạo nội dung rỗng chỉ để khớp số lượng.

Tạo báo cáo Coverage gồm: `Total_Pages`, `Total_User_Actions`, `Total_API_Calls`, `Total_Flows`, `Docs_Mapped`, `Docs_Not_Needed_With_Reason`, `Missing_Doc_Mapping`, `Unverified_UI_Labels`, `Role_Restriction_Conflicts`, `Broken_Deep_Links`, `Design_References_Reviewed`.

**Pass Gate:** không bỏ sót action quan trọng, số liệu có căn cứ; mọi item thiếu bằng chứng phải lộ rõ, không bị ép thành 100%.

---

## 10. KẾ HOẠCH TRIỂN KHAI THEO TỪNG ĐỢT VÀ CỔNG NGHIỆM THU

Lập roadmap đủ chi tiết để agent khác tiếp tục làm mà không cần đoán:

### W00 — Discovery & Source Validation
- Đọc source, System Map, AGENTS, skills, design templates, kiểm kê từng file thiết kế; đối chiếu các route docs có sẵn và va chạm. Không sửa app.
- Deliverable: Source Audit và trạng thái nguồn.

### W01 — Content IA + Editorial Plan
- Cây toàn bộ categories/subcategories/articles cho Client/Admin; ưu tiên công việc theo nhu cầu, mapping source, persona, glossary và phong cách văn phong.
- Deliverable: Editorial Coverage Matrix và content style guide.

### W02 — Design-to-Blueprint
- Đọc đúng giao diện home/detail trong `docs/templates`; mô tả từng component cần chuyển sang Blade, CSS prefix, responsive, microinteractions, search, TOC, screenshot behavior, link to app, empty/error/loading states.
- Deliverable: UI Blueprint đối chiếu chính xác file thiết kế, không vẽ lại từ trí nhớ.

### W03 — Technical Blueprint
- Quyết định content storage, router & route naming, policies, search, indexing, caching, slugs, anchors, images, sanitization, security, SEO, page metadata, role-based admin docs, shared components.
- Deliverable: Technical Architecture + file impact plan.

### W04 — Pilot Content & UAT Design
- Lập kế hoạch **6 bài pilot**: Client (tạo thiệp lần đầu, sửa ảnh/Studio, khách xác nhận RSVP), Admin (tra cứu khách/ticket, đối soát thanh toán, xử lý sự cố ngày cưới). Viết **bản nháp nội dung thực tế** cho ít nhất 2 bài, dựa trên source; nếu chưa xác minh nhãn/flow ghi `NEEDS_UI_VERIFICATION` ngay tại bước đó.
- Deliverable: Sample Articles + review checklist + screenshot shot-list.

### W05 — Implementation Waves (chỉ trong kế hoạch ở lượt hiện tại)
- Đợt 1: route + layout + content renderer + homepage/detail + quyền + search.
- Đợt 2: nội dung Client theo luồng quan trọng nhất, ảnh/screenshot.
- Đợt 3: nội dung Admin + phân quyền + bàn giao/SOP.
- Đợt 4: toàn bộ bài còn lại + liên kết ngữ cảnh trong màn hình ứng dụng.
- Đợt 5: QA content, UI visual, role leakage, broken links, search quality, mobile, docs versioning + review.
- Mỗi work item có `Task_ID`, file dự kiến, phụ thuộc, tiêu chí đạt, test case, trạng thái mặc định `NOT_STARTED`.

**Không tự đánh dấu COMPLETE ở W05 khi chưa triển khai thực tế và kiểm thử.**

---

## 11. ĐỊNH NGHĨA ĐÃ HOÀN THÀNH (DEFINITION OF DONE) CHO LÚC TRIỂN KHAI THẬT

Một bài docs chỉ đạt khi:
1. Liên kết tới một chức năng đã xác minh tồn tại.
2. Tiêu đề đúng vấn đề người đọc cần giải quyết.
3. Nội dung nói đúng tên nút/menu và đúng thứ tự thao tác.
4. Có kết quả kiểm chứng được; với nhánh lỗi chính có cách xử lý.
5. Không chứa hứa hẹn/tính năng giả hoặc thuật ngữ kỹ thuật vô ích.
6. Có category, breadcrumbs, related, link vào sản phẩm thật khi phù hợp.
7. Được hiển thị đúng quyền Client/Admin và phân quyền nhỏ hơn nếu cần.
8. Ảnh (nếu có) lấy từ UI bản demo thật, được che PII, alt và viewport chính xác.
9. Xem tốt ở viewport mobile/desktop, không bị cắt chữ/che nút, dùng Tab và screen reader được ở mức cần thiết.
10. Tìm được bằng tiếng Việt có dấu/không dấu qua từ khóa phổ thông.
11. Có mã liên kết System Map và người kiểm duyệt, revision cập nhật.
12. Có test link/permission/route/render và kiểm tra browser thực tế khi môi trường cho phép.

Một `Admin SOP` cần thêm: actor/role, điều kiện được thao tác, tác động nghiệp vụ, bằng chứng phải lưu, trường hợp phải chuyển cấp, hành vi tuyệt đối không làm.

Một category docs chỉ đạt khi: không có link 404, bài liên kết đúng, tên nhóm người dùng hiểu, không lặp hoặc trống vô nghĩa. Hai home docs chỉ đạt khi người mới tìm được hướng dẫn thao tác đầu tiên trong vài nhịp điều hướng, không phải đoán danh mục kỹ thuật.

### Ma trận QA đề xuất sau implementation
- **Functional:** link, search, synonym, breadcrumb, TOC anchor, prev/next, deep link, clipboard, expand/collapse, role navigation, no-results, stale slug.
- **Security:** unauthorized `/admin/docs` 302/403 phù hợp, staff không đọc tài liệu nhạy cảm, public search không trả title/snippet của Admin, không leak qua endpoint JSON/cache, XSS trong rich content, CSRF trên feedback, no PII trong analytics.
- **Accessibility:** h1 duy nhất, heading hierarchy, keyboard, focus, `aria-expanded`, Escape, skip link, alt, contrast, zoom 200%, reduced-motion.
- **Responsive:** 320/375/390/430/768/1024/1366/1440/1920, long title, long table, font tiếng Việt, ảnh dọc/ngang, sidebar drawer, sticky TOC.
- **Content:** mỗi step còn đúng với app code, role, ngày cập nhật và source revision; tuyệt đối không copy API token/secret sang docs.
- **SEO:** `/docs` public index/canonical hợp lý; `/admin/docs` luôn auth + noindex + không trong sitemap; trang chức năng bí mật không được lộ qua meta/OpenGraph/public search.
- **Performance:** docs home và detail không tạo nhiều query nặng, chỉ assets cần thiết, native fast first.

---

## 12. CÁC DELIVERABLES LẬP KẾ HOẠCH PHẢI TẠO THẬT

Trong `docs/docs-center/` (hoặc thư mục tài liệu được AGENTS cho phép; không tạo thư mục trùng nếu đã có), tạo:

1. `00_DISCOVERY_AND_DESIGN_SOURCE_AUDIT.md` — nguồn thật, source revision, danh sách file `docs/templates`, phân loại Admin/Client, route collisions, trạng thái.
2. `01_DOCS_SITEMAP_AND_READER_JOURNEYS.md` — **đầy đủ cây hai trung tâm** và bài theo persona + journey; không chỉ 10 ví dụ.
3. `02_DOCS_EDITORIAL_COVERAGE_MATRIX.csv` — **một bảng tổng có cột Space Admin/Client**, mapping Page_ID/Action_ID/Flow_ID/API_ID; không bỏ sót API backend ảnh hưởng user flow. Cấm đánh dấu toàn bộ COMPLETE.
4. `03_DOCS_CONTENT_STYLE_GUIDE.md` — quy tắc viết dễ hiểu, ví dụ tốt/xấu, glossary, cấu trúc how-to, troubleshooting, admin SOP và tiêu chuẩn đặt tiêu đề.
5. `04_DOCS_UI_BLUEPRINT_FROM_TEMPLATES.md` — đối chiếu hai mẫu home/detail có thật, inventory component, mobile, CSS prefix, visual QA, chi tiết hành vi từng control.
6. `05_DOCS_TECHNICAL_ARCHITECTURE.md` — routing, storage choice, search, authorization, SEO, performance, versioning, lifecycle, sitemap, source-of-truth.
7. `06_DOCS_IMPLEMENTATION_ROADMAP.md` — work items ưu tiên, dependency, file target, test acceptance, checkpoint; **chỉ kế hoạch**, không điền DONE giả.
8. `07_DOCS_PILOT_ARTICLES_AND_REVIEW.md` — ít nhất 2 bài nội dung mẫu bám UI source, 6 đề cương pilot và checklist chuyên biên tập.
9. `08_DOCS_VERIFICATION_GATES.csv` — phân loại test cho Client/Admin, độ ưu tiên, điều kiện, kết quả mong đợi, `NOT_RUN` cho các test chưa chạy.

Tham chiếu tới bản đồ XLSX/DOCX đã có bằng file path và ID, không cần sinh lại chúng. Chỉ tạo file DOCX/XLSX mới cho docs project nếu người dùng yêu cầu riêng ở lượt khác. Tránh các tài liệu phát sinh không cần thiết.

Nếu quy định AGENTS hiện hành cấm tạo doc không được yêu cầu, đây là trường hợp **người dùng đã yêu cầu lên kế hoạch và tài liệu plan** nên 9 deliverables ở trên là hợp lệ.

---

## 13. CÁCH BÁO CÁO CHO CHỦ DỰ ÁN

Khi hoàn tất bản plan, trả lời ngắn gọn nhưng có số liệu thật:

1. Đã đọc bao nhiêu file thiết kế, tên file mẫu index/detail, có sử dụng được không.
2. Số nhóm chuyên mục/bài hướng dẫn đề xuất trong `/docs/` và `/admin/docs/`, nêu nguồn thống kê (ma trận editorial).
3. Bao nhiêu Page_ID/Action_ID/API_ID/Flow_ID đã mapping, bao nhiêu chưa thể xác minh; lý do.
4. Bản đồ hai trung tâm + những journey mới cần được viết ngay.
5. Lựa chọn kỹ thuật, phạm vi file dự kiến, các rủi ro và cách tránh rò dữ liệu Admin.
6. 6 bài pilot; đính kèm 2 bài mẫu viết như người thật.
7. Thứ tự triển khai, gate và task tiếp theo rõ ràng. **Không triển khai code ứng dụng trước khi kế hoạch được duyệt.**
8. Không tự commit/push/deploy. Sau khi có code ở một phiên sau mới cập nhật `context.gemini.md` theo quy tắc; với phiên plan chỉ cập nhật nếu AGENTS yêu cầu cho việc thêm tài liệu trong repo, ghi đúng phạm vi và không nhận là đã code.
9. Viết sẵn **commit message tiếng Việt có dấu** (không tự chạy Git) nếu có file tài liệu plan mới.

---

## 14. MẪU ĐỊNH DẠNG KẾ HOẠCH THEO BÀI (BẮT BUỘC DÙNG)

```text
ARTICLE: CLI-EDITOR-IMAGE-001
SPACE: Client
TITLE: Cách thay ảnh bìa thiệp cưới
READER: Cô dâu/chú rể lần đầu dùng Studio
WHERE IN APP: [path/route thực tế đã xác minh]
FEATURE_MAPPING: [Page_ID, Action_ID, Flow_ID, API_ID]
PREREQUISITES: Có thiệp cần chỉnh sửa; người đọc có quyền sửa
SUCCESS: Ảnh mới hiển thị đúng trong bản xem trước / bản xuất bản theo logic thật
MAIN STEPS:
  1. [Tên menu/nút đúng từ mã nguồn + ảnh minh họa nếu có]
  2. [Tên trường và cách thao tác]
  3. [Chờ ảnh upload hoàn tất rồi lưu]
  4. [Cách xác nhận thành công]
ERROR BRANCHES:
  - Ảnh sai định dạng hoặc vượt dung lượng: [thông báo thật]
  - Mất mạng / upload lỗi: [cách xử lý thật]
  - Nháp xung đột tab: [quy trình bảo toàn dữ liệu thật]
RELATED: [article IDs]
SCREENSHOT_PLAN: [screen/viewport/state cần chụp]
VERIFICATION: [SOURCE_VERIFIED / NEEDS_UI_VERIFICATION]
CONTENT_STATE: NOT_STARTED
```

Tạo tương tự cho Admin có `required_role`, `impact`, `audit_evidence`, `approval`, `rollback`, `escalation`.

---

## 15. LỆNH THỰC THI CỐT LÕI

**BẮT ĐẦU KHẢO SÁT VÀ LẬP PLAN NGAY.** Đọc đầy đủ `context.gemini.md`, `AGENTS.md`, skills, DOCX/PRD, hai bản System Map đã tạo và thiết kế trong `docs/templates`. Bản map là baseline, code thật là sự thật cuối cùng. Bắt đầu xác định tất cả bài cần có cho hai URL `/docs/` và `/admin/docs/`, viết bản kế hoạch có thể giao cho agent khác triển khai được, và tạo đủ chín deliverables.

**KHÔNG CHỈ LIỆT KÊ TÊN BÀI.** Với mỗi bài phải biết người đọc là ai, họ làm được gì, cách làm, thành công ra sao, lỗi gì có thể gặp, link đến chức năng nào, nguồn code nào chứng minh và bài liên quan nào. Ưu tiên tính dễ đọc, khả năng tự học và giá trị bàn giao hệ thống. Giữ giao diện mẫu gốc; chưa code ứng dụng trong lượt này.
