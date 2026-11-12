# MÂY HẸN — ĐÁNH GIÁ ĐỘC LẬP TRƯỚC KHI KHÓA PHẠM VI SẢN PHẨM

**Ngày thẩm định:** 12/11/2026  
**Mục đích:** Quyết định còn phải bổ sung chức năng nào trước khi ngừng phát triển tính năng mới và chuyển sang giai đoạn sửa lỗi / kiểm thử / phát hành.  
**Trạng thái kết luận:** **ĐỦ RỘNG ĐỂ KHÓA PHẠM VI CHỨC NĂNG; CHƯA ĐỦ BẰNG CHỨNG ĐỂ CHẤP NHẬN “PRODUCTION READY 100%”.**

## 0. Phạm vi và giới hạn chứng cứ

Đã mở và phân tích tài liệu người dùng cung cấp trong phiên:

- `context.gemini(4).md`: nhật ký kiến trúc và phát triển, cập nhật đến Đề án **166** ngày 12/11/2026.
- `AGENTS(2).md`: quy ước AI agent / Laravel / pure frontend, policy kiểm thử, CSS prefix, bảo vệ template.
- `docs(2).zip`: **55 Markdown, 8 CSV, 1 DOCX, 1 TXT và 116 PNG** trong các đường dẫn `docs/`, `docs/admin-vnext/`, `docs/qa/`, `docs/product/`, `docs/design/`, `docs/architecture/`, `docs/implementation/`… DOCX gốc có **28 phần được đánh số 0–27**, chứa tầm nhìn kinh doanh, PRD, Admin, partner, quy tắc bảo mật và định nghĩa hoàn thành.
- `.agents(1).zip`: **38 skill SKILL.md** (gồm skill Mây Hẹn và skill công cụ tổng quát), thêm các tài liệu tham chiếu, prompt, ví dụ và quy tắc hỗ trợ.
- Các ảnh QA lưu trong tài liệu: **116 ảnh / 29 màn hình / 4 viewport** theo báo cáo cũ, không phải bằng chứng visual mới cho Đề án 159–166.

**Không được coi đây là audit chạy trực tiếp trên mã nguồn mới nhất:** Trong các tệp đính kèm hiện tại không có đủ `app/`, `routes/`, `resources/`, `public/`, `tests/`, migrations và database tương ứng với code của Đề án 166. Chưa tự chạy `php artisan test`, MySQL, kiểm thử thanh toán nhà cung cấp, hoặc mở ứng dụng đang chạy bằng browser. Kho Google Drive từng được truy cập trước đó có snapshot cũ hơn (10/11), nên không dùng snapshot ấy để chứng minh các thay đổi 12/11 đã hoàn thành. Mọi “lỗi” bên dưới được phân loại thành **MÂU THUẪN BẰNG CHỨNG**, **RỦI RO LOGIC CẦN TÁI HIỆN**, hoặc **YÊU CẦU SẢN PHẨM CẦN XÁC NHẬN**, không gán nhãn lỗ hổng khai thác đã xác minh khi chưa có tái hiện.

## 1. Đánh giá tuyên bố 1.502/1.502 và “100% hoàn thành”

Báo cáo agent mới nhất do người dùng trích dẫn ghi **1.502 tests passed, 7.747 assertions, failed = 0**. Đây là lời báo cáo chứ chưa phải một file XML/JUnit log đầy đủ có timestamp, SHA commit, danh sách tests được chạy, environment và database. Nhật ký ở cuối Đề án 166 còn ghi **116/116 ở một cụm kiểm thử tính năng gần nhất**; điều này *không nhất thiết* mâu thuẫn với 1.502 tests toàn suite, vì phạm vi chạy khác nhau. Nhưng không thể xác minh độc lập số 1.502 bằng tập tài liệu đính kèm.

Các mâu thuẫn đo được trong tài liệu:

| Bằng chứng | Giá trị đọc trực tiếp | Nhận định |
|---|---:|---|
| `docs/admin-vnext/MAY_HEN_ADMIN_IMPLEMENTATION_WAVES.md` | 10/10 wave ghi `COMPLETED` | Tiến độ khai báo |
| `docs/admin-vnext/MAY_HEN_ADMIN_ATOMIC_FUNCTIONS.csv` | **534/534** ở `IMPLEMENTED_UNVERIFIED` | Chưa khớp tuyên bố nghiệm thu hết |
| `docs/admin-vnext/MAY_HEN_ADMIN_TEST_CASES.csv` | **995/995** `NOT_RUN`, evidence `PENDING_EXECUTION` | Ma trận Admin chưa được cập nhật chứng cứ |
| `docs/qa/MAY_HEN_FEATURE_INVENTORY.csv` | **782/782** `ACCEPTED` | Đây là dấu trong catalog, không thay thế test |
| `docs/qa/MAY_HEN_QA_TEST_CASES.csv` | **5.090/5.090** `PASS` | Nhiều dòng lặp lại evidence văn bản chung |
| `docs/qa/feature-traceability.csv` | **503/782** ô `route` và `controller` trống | Một số hạng mục là quy tắc/ý tưởng; không thể xem mỗi dòng là endpoint đã chạy |
| `docs/qa/MAY_HEN_VISUAL_SCREEN_MATRIX.csv` | **76/702** có `screenshot_after`; **626/702** chỉ `CODE_VERIFIED_RESPONSIVE` | Chưa đủ minh chứng “đã nhìn kỹ từng màn hình” |
| `docs/qa/bug-register.csv` | **26** dòng `RESOLVED` | Sổ lỗi cũ, chưa bao trùm lỗi mới |
| `docs/qa/test-run-ledger.csv` | **89** dòng ghi `PASS` | Không tương đương với bằng chứng 5.090 trường hợp được chạy riêng biệt |

**Kết luận:** Từ chối dùng cụm từ “100% không còn lỗi” hoặc “sẵn sàng production tuyệt đối”. Chuyển toàn bộ báo cáo sang trạng thái `REPORTED_COMPLETE / EVIDENCE_PENDING` cho đến khi có đối chiếu theo SHA commit.

## 2. Những chức năng chính đã được ghi nhận trong lịch sử phát triển

Dựa trên DOCX, PRD, lịch sử 166 đề án và các roadmap, các nhóm dưới đây **đã được agent tuyên bố có triển khai**. Chưa tự xác minh hành vi runtime mới nhất.

1. **Website thương mại và SEO:** Home, gallery, phân loại phong cách, template detail, preview, bảng giá, chính sách, FAQ, blog/marketing, metadata, sitemap, robots, OpenGraph chia sẻ.
2. **Tài khoản và quyền:** Đăng nhập/đăng ký, session thiệp cho khách vãng lai, claim ownership, dashboard khách hàng, các vai trò Admin/Manager/Staff/Partner/Customer, khóa/mở tài khoản, customer 360.
3. **Studio/Editor:** schema-driven, live iframe, color/font/monogram, editor state, autosave, undo/redo, conflict 409, template switch, upload/crop ảnh, media picker, bản nháp/xuất bản, snapshot, publish diff và rollback.
4. **AI Curated:** chọn phong cách/màu, chỉ AI viết nội dung, chọn lại **mẫu gốc** trong bốn native templates, dựng trình diễn từng dòng, chế độ thiệp rút gọn 7 section (khác với template Studio bình thường 10 section).
5. **Nghiệp vụ thiệp:** bố mẹ hai bên, nghi thức, lịch âm/dương, địa điểm, Maps, timeline, bộ đếm ngược, album 10 ảnh, chữ ký, RSVP, sổ lời chúc, ngân hàng mừng cưới tách khỏi thanh toán nền tảng.
6. **CRM khách mời:** nhóm họ nhà trai/nhà gái, phân nhóm cá nhân hóa, RSVP, link/QR theo khách, lời mời Zalo, nhập/xuất CSV, dấu vết chuyển trạng thái, lịch sử biến động RSVP.
7. **Đa sự kiện:** sự kiện riêng Vu Quy/Thành Hôn/Tiệc Cưới, hiển thị theo nhóm khách, RSVP có `event_id` mới trong Đề án 161.
8. **Ngày cưới:** Seating Planner, check-in QR/PIN, Reception Desk, Rescue Mode, Command Center, Offline Rescue Pack, Live Wall, Photo Drop, vận hành khi mất mạng.
9. **Sau cưới:** Memory Vault, Time Capsule, sách kỷ yếu in, ảnh ký tên, lời cảm ơn cá nhân hóa, báo cáo mừng cưới, album khách gửi, thẻ QR kẹp thiệp in A4 (Đề án 166).
10. **Thanh toán và gói:** VietQR/SePay, webhook, idempotency, coupon, renewal, refund workflows, entitlements, partner commissions, reconciliation, financial oversight.
11. **B2B:** Cổng Partner, bàn giao thiệp, custom domain, quản trị đối tác, hoa hồng, hợp đồng quyền sở hữu/claim.
12. **Admin:** 10 wave, 18 innovation, Customer 360, Finance, Ticket, Job/Queue, Template QA, Action Inbox, Radar, Release Control, AI Observatory, Messaging Dispatch, Resilience Cockpit, feedback/replay.
13. **Các đề án gần nhất:** 159 (lời chúc có ảnh/chữ ký), 160 (Live Wall đa phương tiện), 161 (event scope & RSVP), 162 (Visual QA Device Lab), 163 (Smart Focal Point), 164 (Publish Diff & Snapshot), 165 (RSVP Change History), 166 (Companion Card/QR A4).

Không cần bổ sung lại các phân hệ trên dưới một tên mới nếu chúng đã thực sự chạy đúng. Điều cần làm là **kiểm tra độ sâu và sự kết nối giữa các tính năng**, không mở thêm 60 ý tưởng mới.

## 3. Danh sách điểm chặn / kiểm chứng ưu tiên trước khi phát hành

### F01 — Regression tests bị xóa sau khi được báo PASS [P0 — thiếu bằng chứng duy trì]

Trong `context.gemini(4).md`, tại phần các Đề án khoảng 153–166, có nhiều lần nói đã tạo và chạy test rồi **“đã dọn dẹp file test sau khi hoàn tất”**. Tên được ghi nhận bao gồm `SignatureArchiveSuiteTest.php`, `SmartFocalPointSuiteTest.php`, `MultilingualStudioSuiteTest.php`, `InvitationPersonaStudioSuiteTest.php`, `PhotoWishModerationE2ETest.php`, `LiveWallMediaSuiteTest.php`, `SmartGroupEventScopeSuiteTest.php`, `PublishDiffSuiteTest.php`, `RsvpChangeHistorySuiteTest.php`, `CompanionCardSuiteTest.php`. Không phải mọi lần xóa đều sai — test dùng một lần có thể bỏ — nhưng **mỗi hành vi sản phẩm quan trọng cần có regression test bền vững**. Việc xóa tất cả chứng cứ kiểm thử sau khi chạy và vẫn tuyên bố “bảo vệ bởi regression test” là không ổn. `AGENTS(2).md`/skill `mayhen-testing-quality` cũng yêu cầu không xóa test suite hiện hữu.  
**Khắc phục:** tái tạo các kiểm thử bền vững theo hành vi, đưa vào `tests/Feature/` hoặc `tests/Unit/`; không làm phình suite bằng test trùng/lỏng lẻo; không xóa mà không có chấp thuận.

### F02 — RSVP đa sự kiện chỉ lưu `event_id` trong một phản hồi [P0/P1 — rủi ro nghiệp vụ]

Đề án 161 thêm `guest_responses.event_id` *nullable*, mô tả UI có các **pills chọn sự kiện**. Cần xác minh mô hình hiện tại có hỗ trợ khách được mời **cả lễ Vu Quy và tiệc cưới**, khách chọn dự lễ A nhưng không dự lễ B, hoặc thay đổi quyết định riêng từng lễ, hay chỉ chọn được **một `event_id` cho cả response**. Kiểm tra CRUD, quan hệ, unique constraints, cập nhật RSVP/ghế/bếp/check-in/báo cáo theo từng event.  
**Gate:** hai sự kiện độc lập cho cùng một khách, trạng thái và headcount không được ghi đè nhau; event không được phép phải bị chặn phía server.

### F03 — Quyền nhìn thấy sự kiện phải gắn với khách được mời, không do URL tự chọn [P0 — security verification]

Đề án 161 có query rehearsal `?rehearsal=1&group=...` và service `resolveCeremonyVisibility`; đây có thể là sandbox hợp lệ cho chủ thiệp, nhưng cần xác nhận khách công khai **không thể tự thay nhóm** qua query/form/token giả để thấy nghi lễ riêng tư. Kiểm thử khách nhóm A dùng token B, token hết hạn, token được thu hồi, sự kiện xóa/ẩn, wedding unpublished/locked/expired; phát hiện IDOR/BOLA và cache cross-tenant.

### F04 — “Đã gửi” và “đã giao” tin nhắn không được đánh đồng [P0/P1 — dữ liệu vận hành]

Nhật ký NXT-07 ghi Dual Approval Dispatch đồng thời cập nhật `guests.is_sent=true`, trong khi các log chỉ có `dry_run`, `sent`, `skipped_duplicate`; cần kiểm tra **thời điểm** đánh dấu sent và có thật sự có provider callback/ACK hay chỉ phê duyệt nội bộ. Tách `approved`, `queued`, `provider_accepted`, `delivered`, `failed`, `unknown` khi có dữ liệu; không tự khẳng định “đã đọc” nếu không có receipt. Chống gửi trùng, lỗi provider, timeout/retry, số điện thoại quốc tế, Opt-out và consent. Không gửi thử qua tài khoản/nhà cung cấp thật.

### F05 — Khôi phục phải bao phủ toàn bộ dữ liệu, không chỉ snapshot vài bảng [P0 — khôi phục sau sự cố]

NXT-15 ghi `createEmergencyBackupSnapshot` trên `users`, `weddings`, `invitations`, `guests`, `orders`, có SHA-256. Đây có thể là **bản trích xuất khẩn cấp**, không phải backup có thể khôi phục trọn vẹn. Cần kiểm tra kèm `guest_responses`, `wedding_events`, `wedding_tables`, ảnh, lời chúc, signature, payments ledger, coupon usage, audit logs, partner ownership, Time Capsule, published/draft versions; khóa bí mật và tính toàn vẹn quan hệ.  
**Gate:** thực hiện một lần diễn tập *restore* trên môi trường cô lập có nhật ký RPO/RTO, ảnh/QR/link hoạt động, không chỉ xác minh hash file.

### F06 — Phân tách bản nháp/công khai và rollback phải xuyên mọi nơi [P0]

Đề án 164 có PublishDiffInspector, so sánh 25 trường, discard, rollback. Kiểm tra các dữ liệu **không nằm trong 25 trường**, đặc biệt `wedding_events`, `event_id`, album/ảnh/chữ ký, ngân hàng, hai nhà, link Maps, hiệu lực gói. Khi một người sửa bản nháp nhưng chưa publish, link công khai không được thay đổi. Rollback không được phá số liệu RSVP/event, các đơn đã thanh toán hoặc quyền sở hữu. Kiểm tra 2 tab edit, DB lock trên MySQL, F5 giữa chừng và lỗi mạng.

### F07 — Độ phủ đa mẫu và quy tắc 7/10 section [P1]

`mayhen-invitation-standards` bắt buộc 10 section cho template chuẩn. Chế độ AI Curated 7 section là **ngoại lệ nghiệp vụ chính thức** ghi trong lịch sử. Đề án 159 cập nhật render ảnh/chữ ký trên một mẫu (`giua-muon-ngan-vi-sao`); Đề án 161 cũng liệt kê tích hợp render RSVP theo nhóm tại mẫu này. Cần đối chiếu **tất cả mẫu active**, không kết luận mọi mẫu đã hỗ trợ chỉ vì service chung hoặc một template hoạt động. Kiểm tra native fidelity, assets, font, preview, public render, `?embed`, cấu hình section, mode sau F5, phiếu mời cá nhân hóa; không tự viết lại giao diện.

### F08 — UX/Visual QA chưa phản ánh thay đổi ngày 12/11 [P1]

`docs/qa/screenshots/` có 116 ảnh cho 29 màn hình trên bốn viewport: 375, 430, 768, 1440. Ma trận 702 case có 76 screenshot-after. Nhiều ảnh chụp từ trước và ở trạng thái ban đầu, không có dữ liệu đủ cho những màn mới/phiên bản mới; ảnh `partner_portal_M375.png` từng cho thấy trang login (có thể do chưa auth), ảnh `preview_M375.png` có thể đang giữa animation. Không sử dụng chúng để nghiệm thu client/agency dashboard hay nội dung thiệp cuối cùng.  
**Gate:** đăng nhập thật với vai trò, ảnh chụp và tương tác trên mobile/desktop các trạng thái `empty/loading/success/error/permission/overlay`, sau animation, tab/menu, crop ảnh, form dài, bàn tiệc nhiều khách; so sánh trước/sau.

### F09 — MySQL / giao dịch đồng thời / tích hợp thật chưa có chứng cứ trong bundle [P0]

`docs/qa/test-baseline.md` ghi SQLite `:memory:` và chỉ phản ánh một baseline cũ 653 tests. SQLite và MySQL khác nhau về `FOR UPDATE`, isolation/unique, case collation, JSON, rollback, deadlock. Không thể suy luận hiệu quả idempotency/bảo mật khi 1000 khách cùng RSVP hoặc hàng trăm check-in chỉ từ SQLite.  
**Gate:** staging MySQL tương đồng production; test giao dịch cạnh tranh (double webhooks, voucher double redemption, 2 thiết bị check-in, event RSVPs) và migrate `up/down` an toàn; không làm trên production.

### F10 — Sao chép xác thực tài chính từ ghi chú vào trạng thái thật [P0]

Kiểm tra luồng `pending -> paid -> refunded` của Order độc lập với refund execution, trạng thái `refund_requested/approved/processing/settled/failed` nếu có; phân biệt bản ghi chứng từ và tiền thực về ngân hàng. Phải xác minh số tiền, mã tham chiếu, provider txid, entitlement, hóa đơn, hoa hồng, duplicate webhook, callback sai. Các bản ghi gift QR là **tiền mừng cá nhân**, không được tính vào doanh thu nền tảng.

### F11 — PII trong AI Observatory và dữ liệu nhạy cảm trong media [P1]

NXT-12 ghi `ai_usage_logs.prompt_preview` và `output_preview`. Phải xác định có chứa họ tên cặp đôi, số điện thoại, địa chỉ, lời nhắn riêng, ảnh, account ngân hàng hay không; thiết lập masking, hạn lưu, phân quyền xem, xóa theo chính sách. Đề án 159/160 hiển thị chữ ký và ảnh khách gửi tại nhiều nơi; cần kiểm tra chỉ media được duyệt xuất hiện trên thiệp/Live Wall, link ảnh gốc không vô tình công khai và thao tác thu hồi media lan đến cache/feed.

### F12 — Tổng duyệt, hiển thị thử và preview không được làm bẩn dữ liệu thật [P1]

Kiểm tra `rehearsal=1` trên mọi route public / QR scan / RSVP / wishes / money gifts / invitation views. Thao tác ở preview phải không làm thay đổi counters thật, không tạo guest checkin, không gửi tin nhắn, không tạo `Order` thật. Nếu có dữ liệu synthetic, ghi rõ trong UI và không trộn với conversion metrics.

### F13 — In thẻ kẹp A4 cần nghiệm thu quy cách xưởng in [P1/P2]

Đề án 166 ghi 90×54mm/105×148mm/100×100mm, trang A4 có crop marks và xuất SVG. Kiểm tra khổ thực, lề, chừa cắt, safe zone, bleed, chiều đọc QR sau in, font khi xuất SVG, không dùng logo hàng khác, định dạng in in browser ở 100% scale. **File SVG vector không tự động chứng minh chuẩn màu CMYK cho in offset**; cần prepress kiểm chứng với xưởng in nếu đưa ra lời hứa đó.

### F14 — Cần xác minh các lời hứa bán hàng và chứng nhận [P1]

DOCX/AGENTS và docs cũ có các ví dụ: “tương thích 100% thiết bị”, “không có lỗi”, “lưu trữ trọn đời”, “hỗ trợ 15 phút”, “tiết kiệm 80–95%”, rating 4.9, chứng nhận Bộ Công Thương, phương thức thanh toán chưa cấu hình. Những cụm này nếu hiển thị **phải có chứng cứ, điều kiện rõ ràng**; nếu chỉ là bản thảo thì không đưa vào production. Không dùng marketing metrics giả để tạo lòng tin khách hàng.

### F15 — Sổ hỗ trợ / liên kết model phải được test ở UI thật [P1]

Người dùng đã đưa commit sửa `SupportTicketReply.user()` sau lỗi 500 ở màn xem ticket client. Đây là bằng chứng cho thấy `Laravel Pint` và full test green không đủ che chắn lỗi liên kết model-runtime. Kiểm tra toàn bộ Ticket UI: xem/list/reply, attachments, phân quyền theo wedding, staff note, SLA, soft delete, notification, trạng thái 403/404/500; viết regression test giữ lâu dài.

### F16 — Admin authentication và phê duyệt nhạy cảm [P1]

Nhật ký có RBAC, support lens, audit log, role restrictions, nhưng không thấy triển khai 2FA/TOTP trong cập nhật `context.gemini(4).md`; *không kết luận thiếu chỉ dựa vào việc không xuất hiện trong log*. Hãy kiểm tra đăng nhập quyền cao, re-auth khi xuất dữ liệu PII/hoàn tiền/thay đổi bank, rotate session, CSRF, hoạt động nhiều tab, audit immutable, kiểm thử đăng xuất / khóa account. Nếu chưa có xác thực tăng cường cho admin tài chính và ban đầu chỉ một nhân sự dùng, tối thiểu bắt buộc re-auth + cảnh báo; 2FA là nâng cấp P1 có giá trị.

## 4. Danh sách bổ sung cuối cùng — CHỈ triển khai nếu xác minh thực sự còn thiếu

**Không đề xuất một sản phẩm thứ hai.** Đóng phạm vi Client/Studio/Admin hiện có. Chỉ chấp nhận các bổ sung sau dưới hình thức **lấp lỗ hổng chức năng / kết nối workflow**, theo thứ tự:

| Mã | Bổ sung hoặc hoàn thiện | Giá trị | Điều kiện thực hiện |
|---|---|---|---|
| FIN-01 | Nhật ký hoàn tiền có trạng thái xác nhận tiền thực, reconciliation evidence, quyền phê duyệt | Tránh thất thoát, tranh chấp | Nếu flow refund chưa theo dõi bank settlement |
| EVT-01 | RSVP độc lập theo từng sự kiện được mời, đồng bộ headcount và check-in theo event | Không xếp sai bàn/tiệc | Nếu một `event_id` hiện tại làm mất lựa chọn nhiều sự kiện |
| MSG-01 | Trạng thái gửi/tiếp nhận/giao thất bại chính xác theo provider; retry và opt-out | Tránh gửi sót, gửi trùng, thống kê ảo | Nếu hiện chỉ đánh dấu `is_sent` khi approve |
| OPS-01 | Restore drill hoàn chỉnh cho dữ liệu + media + phụ thuộc, theo RPO/RTO | Thực sự phục hồi được sau mất dữ liệu | Nếu chưa có bản diễn tập tái lập nguyên vẹn |
| SEC-01 | Step-up authentication/re-auth và hạn chế xuất PII, staff actions | Giảm rủi ro lỗi admin | Nếu quyền nhạy cảm chưa bảo vệ đủ |
| MEDIA-01 | Thu hồi/xóa chữ ký/ảnh theo quyền, kiểm duyệt cross Live Wall/Book | Bảo vệ danh dự, riêng tư | Nếu có một route hiện ảnh chưa duyệt hoặc link private lộ |
| PRINT-01 | Print preflight kiểm tra QR kích thước, safe zone, bleed, font và bố cục thật | Tránh in lỗi số lượng lớn | Nếu dịch vụ bán thẻ in giấy là tính năng trả phí |
| UX-01 | Màn xác minh thực tế “chắc chắn trước khi gửi thiệp” kết hợp bản nháp, sự kiện, khách, số suất, Maps, quyền riêng tư, kết quả test | Giảm lo âu và hỗ trợ | Chỉ bổ sung khi màn hiện có chưa nối đủ các nguồn; không tạo menu trùng |

**Những gì KHÔNG nên thêm trước lần ra mắt đầu tiên:** social network cưới, marketplace nhà cung cấp, livestream server-hosting riêng, generative template HTML/CSS do AI tự viết, chatbot khổng lồ, trang Analytics chỉ có số mẫu, đa vai trò quản trị phức tạp không phục vụ việc bán hàng. Chuyển chúng sang backlog sau khi có khách trả phí thật.

## 5. Nguyên tắc khóa phạm vi

1. **Scope Freeze**: Không thêm features/menu trừ 8 mục bên trên nếu có bug/gap xác nhận.
2. **No more PASS by document**: Mỗi kiểm thử cần có `commit SHA + environment + test name + actual result + evidence`, map về route/function.
3. **Test parity**: SQL/HTTP tests không thay browser interaction; mobile screenshot không thay thao tác người dùng; mock provider không thay staging webhook.
4. **Data safety**: Không tạo/xóa giao dịch thật, không gọi SMS/Zalo thật khi thử nghiệm, không migrate:fresh production; không upload dữ liệu khách thật vào hệ thống thử nghiệm.
5. **Low risk fixing**: Một lần chỉ sửa một lát cắt nghiệp vụ; test lại module liên đới trước khi chuyển việc.
6. **Visual sign-off**: Ký duyệt từng trang Client/Studio/Admin trong browser với ảnh đủ các trạng thái, không dùng code inspection thay ảnh.
7. **Retain regression**: Bộ test cho tính năng quan trọng phải còn trong repo, không xóa chỉ để giữ repo 'sạch'.
8. **Real QA**: Chứng minh Restore + MySQL + E2E + quyền riêng tư + payment sandbox + product acceptance trước khi mở bán.

## 6. Chia 5 Gate sửa từng phần

| Gate | Mục tiêu | Tiêu chí thoát |
|---|---|---|
| **G0 — Evidence Truth** | Chụp SHA Git, toàn route, inventory, các test đang tồn tại, đối chiếu 995 Admin cases, 5.090 generic cases, screenshot; khôi phục test regression bị xóa | Báo cáo không còn tuyên bố 100% thiếu chứng cứ, tất cả case có trạng thái `PASS/FAIL/NOT_RUN/BLOCKED` đúng thực tế |
| **G1 — Core Data & Security** | Bảo vệ tiền, role/PII, tenant, guest token, event RSVP, draft/publish, backup/restore | Không còn P0; MySQL integration + concurrency chứng minh; restore drill qua |
| **G2 — Wedding Day & Customer Journeys** | Kiểm thử kịch bản khách nhiều sự kiện, lịch đổi phút chót, check-in offline, Zalo dispatch, album/live media, in thẻ QR | Các hành trình E2E có video/log/DB assertions; không có ghi trùng, thất thoát hoặc nhầm ngày |
| **G3 — Visual & Micro-UX** | Mở từng trang trên 320/375/390/430/768/1024/1366/1440/1920; khác vai trò; ảnh, modal, error, F5, mạng yếu | Không có lỗi P1 giao diện, điều hướng/cắt chữ/chồng modal; hình chụp từ browser phiên bản hiện tại |
| **G4 — Business UAT & Release** | Khách giả lập thực tế, checkout sandbox, customer support, thông báo, pháp lý/privacy, performance, prepress | Đủ hồ sơ release, có rollback plan, đã phê duyệt chính sách và người chịu trách nhiệm |

## 7. Bảng ưu tiên khi bắt đầu sửa từng phần

**Tuần sửa đầu tiên (không phải ước tính thời lượng):**

1. `G0-01` — Chạy lại tất cả test ở SHA hiện tại, lưu danh sách thật, không dùng số 1.502 trong hội thoại làm kết quả thay thế.
2. `G0-02` — Liệt kê test bị xóa sau Đề án 153–166, bổ sung lại regression tối thiểu theo hành vi, đối chiếu khả năng lặp lại.
3. `G0-03` — Đồng bộ trạng thái danh mục 534 chức năng Admin, 995 test case, 782 chức năng, 5.090 test case và 702 visual case theo evidence thật. Không tự chuyển hàng loạt sang PASS.
4. `G1-01` — Test RSVP một khách chọn tham gia 2 sự kiện với headcount khác nhau; đánh dấu và sửa nếu sai.
5. `G1-02` — Test dữ liệu thiệp chưa xuất bản không rò ra thiệp public khi sửa ngày/Maps/ngân hàng/sự kiện và khi rollback.
6. `G1-03` — Test xung đột 2 lễ tân check-in cùng guest/seat trên MySQL.
7. `G1-04` — Tái hiện và kiểm tra toàn bộ Ticket flows sau fix `SupportTicketReply.user()`.
8. `G1-05` — Test webhook/đối soát và hoàn tiền end-to-end trong sandbox.
9. `G2-01` — Test dispatch chiến dịch Approved nhưng provider timeout/failed, kiểm tra `is_sent` và retry.
10. `G2-02` — Test media lời chúc/ảnh/chữ ký được duyệt, xóa, thu hồi trong Sổ lưu bút, Live Wall, Keepsake.
11. `G2-03` — Test bản in thẻ kẹp A4 và QR cá nhân với ảnh chụp file PDF render 100% khổ A4.
12. `G3-01` — Chụp lại Studio, AI Curated và Admin role-based khi đã đăng nhập thật ở nhiều viewport, không lấy screenshot cũ làm bằng chứng.

## 8. Tiêu chuẩn quyết định phát hành cuối cùng (Go / No-Go)

**GO** khi đồng thời đạt:
- Không có lỗi P0; lỗi P1 ảnh hưởng tiền, dữ liệu, đăng nhập, link thiệp hoặc ngày cưới đều đã xử lý.
- Chứng minh chính xác khách mời được mời sự kiện nào; RSVP và check-in không có nhầm lẫn khi đa sự kiện.
- Có khả năng khôi phục dữ liệu thật trên bản staging; chưa có bằng chứng thì không hứa “trọn đời/an toàn tuyệt đối”.
- Kết quả MySQL/SePay/SMS/Zalo (nếu tích hợp thực) được kiểm chứng tương ứng mức cam kết thương mại.
- Browser E2E sạch ở những đường mua gói/tạo thiệp/đổi mẫu/xuất bản/chia sẻ/RSVP/điểm danh.
- Thông tin marketing/giá/cấp quyền không mâu thuẫn database.
- Chủ dự án duyệt các ảnh UI thực tế Client, Studio, Admin và thiệp.
- Có hướng dẫn rollback/incident và người chịu trách nhiệm giám sát sau release.

**NO-GO** nếu vẫn cần giả định kết quả một trong các bước trên chỉ vì “Pest pass 100%”.

## 9. Tài liệu và skill đã phân tích

Nguồn chuẩn nghiệp vụ: DOCX `MASTER PROMPT MAYHEN.VN.docx` phần 0–27; `MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md`; `MAY_HEN_DOMAIN_INVARIANTS_AND_STATE_MACHINES.md`; `MAY_HEN_CROSS_MODULE_E2E_MATRIX.md`; `MAY_HEN_PREMIUM_FEATURE_CATALOG.md`; `MAY_HEN_PREMIUM_UPGRADE_ROADMAP.md`; `MAY_HEN_60_PREMIUM_FEATURE_DEEP_EVALUATION.md`; `MAY_HEN_FINAL_*`; `MAY_HEN_ADMIN_*`; `docs/qa/*`; `context.gemini(4).md`; `AGENTS(2).md`.

Skill có tính ràng buộc trực tiếp: `mayhen-invitation-standards`, `mayhen-invitation-templates`, `mayhen-visual-editor`, `mayhen-admin`, `mayhen-payments`, `mayhen-auth-security`, `mayhen-guest-rsvp`, `mayhen-testing-quality`, `mayhen-code-review`, `mayhen-performance`, `mayhen-ui-ux`, `mayhen-database`, `mayhen-deployment`, các skill Blade/CSS/JavaScript/architecture/partners; có 38 skill tổng cộng. Trong zip cũng có skill `tailwindcss-development` và nhóm Strix tổng quát: **không phải yêu cầu được phép cài Tailwind/Strix** vì quy ước Mây Hẹn ưu tiên pure vanilla và người dùng không muốn tool ngoài.

**Tuyên bố này là audit tài liệu, không phải bảo đảm code thực tế đã hết lỗi.**
