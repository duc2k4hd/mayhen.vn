# MÂY HẸN — RÀ SOÁT ADMIN SAU VNEXT & ROADMAP TIẾP THEO

**Ngày rà soát:** 10/11/2026  
**Nguồn:** Thư mục Google Drive của người dùng: https://drive.google.com/drive/folders/1CAFtsLAuI7AbiJrHzbH8FRlxPPVLNmUj?usp=sharing  
**Phạm vi:** Kiểm tra hồ sơ `context.gemini.md`, roadmap Admin, innovation backlog, danh mục và sổ lỗi Admin; đối chiếu các file thật `routes/admin.php`, 14 controller Admin có trong Drive, trích đoạn trực tiếp của `OrderController`, `GuestController`, `UserController`, `SupportController`, `InvitationQualityPassportService`, `AdminVNextInnovationBacklogTest`, `campaign_preview.blade.php`, `rehearsal.blade.php`, và cây thư mục tests. **Đây là code/document review có chọn lọc, chưa chạy Laravel, MySQL, SePay hay trình duyệt thực tế.** Không được diễn giải thành kiểm toán toàn bộ source hoặc chứng nhận production.

## A. Trạng thái thực tế nhìn thấy

- Roadmap `docs/admin-vnext/MAY_HEN_ADMIN_IMPLEMENTATION_WAVES.md`: 10/10 waves ghi `COMPLETED`; báo cáo Wave09 ghi **707/707 tests**.
- `docs/admin-vnext/MAY_HEN_ADMIN_INNOVATION_BACKLOG.md`: 18/18 sáng kiến được đánh dấu hoàn thành.
- Phần cuối `context.gemini.md` mới hơn: lần lượt 715, 718 và **724/724 tests, 3.827 assertions**; bộ `AdminVNextInnovationBacklogTest.php` thực tế có 17 test methods. Đây là *số liệu do AI báo cáo*, chưa chạy lại ở phiên kiểm tra này.
- Cây Drive thực tế có 14 controllers Admin và các Views/Migrations liên quan tới coupon, support, guest ops, intelligence, audit, templates; không phải chỉ có mockups.
- Trong danh sách `tests/Feature/` của snapshot Drive **không thấy** các file `AdminVNextWave03UsersAndInvitationsTest.php`, `AdminVNextWave04TemplatesGovernanceTest.php`, `AdminVNextWave05GuestAndWeddingOpsTest.php`, `AdminVNextWave06CommerceAndGrowthTest.php`, `AdminVNextWave07SupportAndOpsTest.php`, `AdminVNextWave08DataAndIntelligenceTest.php`, `AdminVNextWave09HardeningAndUATTest.php` được roadmap trích dẫn. Có thể test chưa sync, được gộp/đổi tên hoặc tài liệu chưa cập nhật. **Phải đối chiếu đường dẫn Git và test discovery; không tự kết luận các test chưa từng chạy.**

## B. Các phát hiện thực tế cần xử lý trước khi mở rộng

### P0 — Cần kiểm tra/sửa trước vận hành thương mại

**ADM-NEXT-P0-01 — Duyệt đơn thủ công chưa buộc chứng từ thật.** `app/Http/Controllers/Admin/OrderController.php::approve()` khai báo `reference_code` và `reconciliation_note` là `nullable`; khi trống tự phát sinh `MANUAL-{timestamp}-{random}`, tự tạo lý do mặc định, rồi cập nhật đơn `paid` và gia hạn thiệp. Điều này mâu thuẫn yêu cầu trong `02_ADMIN_BUG_REGISTER.md` rằng mã tham chiếu và lý do đối soát phải bắt buộc. Không coi một mã tự sinh là chứng từ ngân hàng. Thêm xác nhận giao dịch đã đối soát, duy nhất theo giao dịch ngân hàng, trạng thái pending_review, chỉ cấp quyền sau bằng chứng đủ và cơ chế nguyên tử.

**ADM-NEXT-P0-02 — Hoàn tiền trên hệ thống khác hoàn tiền thật.** `OrderController::refund()` chỉ đặt `orders.status='refunded'`, đặt `expires_at=now()`, ghi audit và hiển thị thông báo **“Đã hoàn tiền”**; không nhìn thấy lệnh chuyển khoản hoàn, chứng từ hoặc xác nhận tiền về tài khoản khách trong method này. Nếu quy trình hoàn tiền là thủ công, phải thêm `refund_requested -> refund_approved -> refund_sent -> refund_confirmed`, mã tham chiếu, người duyệt và chỉ hiển thị “Đã hoàn tiền” sau xác nhận. Không tự động thu hồi quyền lợi khi mới yêu cầu hoàn tiền nếu hợp đồng không quy định.

**ADM-NEXT-P0-03 — Xuất dữ liệu cá nhân quá rộng theo route.** `routes/admin.php`: `GET /admin/users/{id}/export-personal-data` chỉ nằm trong `admin.auth`; `UserController::exportPersonalData()` lấy profile, số điện thoại, tài khoản ngân hàng, thông tin khách mời, lời chúc, tickets, orders; không thấy kiểm tra `admin.super`/authorization riêng trong method. `admin.auth` cho staff truy cập. Cần ràng buộc quyền DSAR riêng, workflow phê duyệt và ghi audit, thông báo/định danh người yêu cầu, data minimization, rate limit, khóa/tải file an toàn. Rà lại các route xuất CSV tương tự.

### P1 — Sửa logic/tính nhất quán

**ADM-NEXT-P1-04 — Check-in toggle không có semantics idempotent.** `GuestController::toggleCheckIn()` đảo trạng thái hiện tại, nên một request POST gửi lặp có thể điểm danh rồi lập tức hủy; không thấy transaction/lock tại đây. API ngày cưới nên nhận action rõ `check_in` hoặc `undo_check_in` với xác nhận/permissions và idempotency key; double-click/retry không phải đảo trạng thái.

**ADM-NEXT-P1-05 — Xếp bàn nhanh chưa kiểm soát sức chứa tại thời điểm ghi.** `GuestController::quickAssignTable()` xác thực bàn thuộc wedding rồi `update(table_id, table_name)` mà không thấy kiểm tra `capacity` và tổng số suất RSVP/check-in trong transaction. Admin có thể xếp quá số ghế dù UI hiển thị ghế còn trống. Cần quy tắc oversubscription rõ, exception có lý do, lock và test song song.

**ADM-NEXT-P1-06 — Tạo bàn dự phòng dễ sinh tên/trật tự đụng nhau khi thao tác đồng thời.** `GuestController::createEmergencyTable()` tính `tables()->count()+1` và `max(display_order)+1` trước khi tạo mà không lock. Nên dùng giao dịch và unique sequencing hoặc xử lý xung đột có chủ đích, không làm hỏng việc xếp bàn khi có nhiều lễ tân.

**ADM-NEXT-P1-07 — Hộ chiếu chất lượng chưa phản ánh đầy đủ nghiệp vụ.** `InvitationQualityPassportService::inspect()` chấm sức chứa bằng số *bản ghi khách* (`guests()->count()`), không phải số suất người dự thực tế từ RSVP/children/walk-ins. Đồng thời `canonical_sections_count >= 10` kiểm tra template nguồn nhưng không chứng minh chế độ Curated 7 section đang render đúng; việc có `sync_count > 0` không chứng minh mọi dữ liệu được sync. Nâng sang kiểm thử theo loại thiệp, bản preview xuất bản, số người thực dự, assets thực sự tải được và ảnh chụp kiểm tra.

**ADM-NEXT-P1-08 — Synthetic Rehearsal chưa phải automated E2E rehearsal.** `rehearsal.blade.php` hiển thị iframe và checklist 5 giai đoạn, test `AdminVNextInnovationBacklogTest::test_synthetic_rehearsal_sandbox_screen()` chủ yếu xác nhận HTTP 200 và các chuỗi HTML. Chưa đủ bằng chứng đã tự động thực hiện RSVP, lời chúc, QR check-in giả lập, xác nhận không thay đổi dữ liệu và kiểm tra UX trong browser. Đổi tên thành hướng dẫn diễn tập nếu chưa hoàn thiện automation, hoặc triển khai môi trường sandbox thật.

### P2 — Minh bạch, tài liệu, thương mại

**ADM-NEXT-P2-09 — Các tỷ lệ marketing được hardcode.** `resources/views/admin/pages/guests/campaign_preview.blade.php` công bố tỷ lệ mở 85–92%, tiết kiệm 95%, chi phí 0đ, tương thích 100%. Không có bằng chứng đo thực tế cho các tỷ lệ này trong phần đã kiểm tra. Thay bằng dữ liệu đã thu thập, hoặc ghi rõ *ước tính minh họa/chưa đo*, và làm rõ phí thực gửi SMS/Zalo nếu có.

**ADM-NEXT-P2-10 — Sổ kiểm thử chưa đồng bộ với mã nguồn và phiên cuối.** Wave09 ghi 707 tests, context cuối 724, baseline `docs/qa/test-baseline.md` vẫn 653. Chạy lại ở snapshot repository, xuất danh sách test + kết quả và cập nhật chỉ một nguồn sự thật có commit SHA, env, DB, timestamp.

**ADM-NEXT-P2-11 — Thiếu bằng chứng browser Admin đầy đủ ở phiên rà soát này.** Có một số báo cáo visual QA nhưng chưa thể kết luận mỗi màn hình Admin đã được trải nghiệm thật tại 320–1920px, trạng thái lỗi/loading, ngôn ngữ dài, bàn phím, focus, mobile/tablet. Tạo bộ visual regression baseline cho tất cả màn hình sau đăng nhập và các trạng thái biểu mẫu.

## C. Hướng triển khai mới — không lặp lại 18/18 Innovation Backlog

| ID | Đề án | Vấn đề giải quyết | Ưu tiên | Tiêu chí nghiệm thu tối thiểu |
|---|---|---|---|---|
| NXT-01 | **Finance Evidence Ledger & Dual Approval** | Duyệt thủ công, hoàn tiền, đối soát sai | P0 | Bằng chứng giao dịch thật, maker-checker, mã đối soát duy nhất, chống replay, audit bất biến |
| NXT-02 | **Fine-Grained Access Policy Center** | Staff có quyền quá rộng, xuất PII không chốt | P0 | Quyền theo hành động/phạm vi; deny-by-default; test matrix role×resource×action |
| NXT-03 | **Wedding-Day Consistency Transaction Engine** | Check-in đảo trạng thái, quá tải bàn, race condition | P0 | Set-state idempotent; capacity atomic; check-in và RSVP đồng bộ khi nhiều request |
| NXT-04 | **Template Release Lab & Visual Regression** | Mẫu mới sửa có thể vỡ cả trăm thiệp | P1 | Diff ảnh gốc–mới 375/430/768/1440, version, staged rollout, revert |
| NXT-05 | **Actual Guest Journey E2E Runner** | Rehearsal hiện chỉ là hướng dẫn trong UI | P1 | Mở thiệp–RSVP–sổ lời chúc–QR–album–đổi mạng hoàn chỉnh trên DB test cô lập |
| NXT-06 | **Real Funnel & Delivery Analytics** | Tỷ lệ mở thiệp/hiệu quả quảng cáo chỉ là ước tính | P1 | Event consent-aware, privacy safe, phân tích ghé thăm→tạo→trả tiền→gửi→RSVP |
| NXT-07 | **Reliable Messaging Dispatch** | Chưa quản lý đầy đủ quy trình gửi link, thất bại, xác nhận nhận | P1 | Opt-in, phân đoạn, preview, queue, retry, dedupe, gửi thử, báo cáo chi phí thực |
| NXT-08 | **Entitlements & Subscription State Machine** | Gói, coupon, gia hạn, refund, tính năng premium không đồng bộ | P1 | 1 nguồn sự thật, trạng thái bất biến, proration nếu áp dụng, test giao thoa |
| NXT-09 | **Support Escalation & Incident Response** | Ticket/SLA có nhưng chưa khép kín phản hồi–điều tra–khắc phục | P1 | Tự leo thang SLA, lịch trực, điều phối incident, postmortem, xác nhận khách |
| NXT-10 | **Privacy Operations / Retention Jobs** | Export và anonymize cần kiểm soát đời thực | P1 | DSAR workflow, 2-person review, tránh xóa dữ liệu kế toán phải lưu, export có hạn |
| NXT-11 | **Release & Rollback Control Center** | Thiếu lớp kiểm soát rollout mã nguồn/cấu hình | P1 | Preflight, migration dry-run, feature flags, rollback plan, verification, audit |
| NXT-12 | **AI Quality & Cost Observatory** | AI Content có nhưng cần quản lý chi phí và kết quả | P2 | Đo chi phí/token, fallback an toàn, kiểm duyệt câu chuyện không bịa, phiên bản lời viết |
| NXT-13 | **Customer Delight Feedback Loop** | Không biết khách thật hài lòng ở đâu | P2 | Feedback ngắn sau milestone, rating và lý do, liên kết ticket và action plan |
| NXT-14 | **Read-Only Operations Replay** | Khó tái hiện lỗi từ chuỗi thao tác nhiều phân hệ | P2 | Event timeline có thể replay ở sandbox, che PII, không ghi đè dữ liệu thật |
| NXT-15 | **MySQL / Load / Chaos Validation** | SQLite local chưa chứng minh concurrency production | P0/P1 | Test MySQL cô lập, parallel webhook/check-in, queue failure, tải cao, restore backup |

### Ưu tiên triển khai

**Wave A — Integrity First (P0):** NXT-01, NXT-02, NXT-03, NXT-15 (phần MySQL liên quan tiền/quyền/check-in). **Cấm đánh dấu hoàn tất nếu chưa có test âm tính, song song và bằng chứng trên môi trường an toàn.**

**Wave B — Trusted Operations:** NXT-04, NXT-05, NXT-08, NXT-09, NXT-10, NXT-11. Đảm bảo nâng tính năng không phá bản thiệp khách đang dùng.

**Wave C — Actual Growth & Premium Value:** NXT-06, NXT-07, NXT-12, NXT-13, NXT-14. Chỉ đưa con số conversion, deliverability hoặc chi phí lên UI khi thực sự đo được.

## D. MASTER PROMPT GỬI AI CODING AGENT

> **Workspace:** `E:\Project\Laravel\mayhen.vn`  
> **Đầu vào:** Đọc `context.gemini.md` trước tiên, `AGENTS.md`, `.agents/skills/mayhen-admin/SKILL.md`, skill testing/bảo mật/thanh toán liên quan, `docs/admin-vnext/`, `docs/qa/`, routes và code thật. Đọc báo cáo này sau khi chép vào `docs/admin-vnext/`.

Bạn đã báo hoàn tất 10/10 wave Admin và 18/18 sáng kiến. **Không được tiếp tục sáng tạo menu chỉ để tăng số lượng tính năng.** Trước khi thêm tính năng, hãy kiểm định lại bốn vấn đề có bằng chứng từ code:

1. `OrderController::approve`: mã đối soát và lý do đang nullable, có mã giả `MANUAL-...`, rủi ro duyệt đơn chưa có bằng chứng hoặc race; định nghĩa workflow tài chính có chứng từ và test MySQL.
2. `OrderController::refund`: chỉ cập nhật database nhưng UI báo đã hoàn tiền; thiết kế luồng chứng từ hoàn tiền thật hoặc đánh dấu đúng là *đề nghị hoàn tiền/chờ thực hiện*.
3. `UserController::exportPersonalData`: route cho mọi admin.auth nhưng xuất PII rộng; thiết kế quyền dữ liệu riêng, audit và kiểm soát xuất.
4. `GuestController::toggleCheckIn`, `quickAssignTable`, `createEmergencyTable`: đảm bảo check-in không đảo khi retry, không quá sức chứa, xử lý đồng thời an toàn.

Sau đó kiểm tra Quality Passport (đếm số suất thay vì số Guest record, Curated 7 section vs Native 10), Synthetic Rehearsal (không chỉ hiển thị checklist), dữ liệu ước tính cứng trong campaign preview, và chênh lệch test file/summary trên Drive.

Lập backlog có các trạng thái `IN_CODE`, `TESTED`, `BROWSER_VERIFIED`, `PRODUCTION_EXTERNAL_UNVERIFIED`; không dùng `DONE` nếu chỉ có route/view. Mỗi phát hiện ghi đường dẫn file, thao tác tái hiện, mức độ, vì sao logic sai, test thất bại trước sửa và test xanh sau sửa. Không sửa assertion để che lỗi.

Triển khai theo NXT-01 → NXT-15, ưu tiên P0. Với mỗi nhiệm vụ, kiểm tra liên kết Controller/Service/DB/View/Frontend/Permissions/Tests và chất lượng UX ở 375/430/768/1366/1440px. Chỉ dùng Laravel, Blade, CSS và JS thuần; không thêm dependency nếu chưa được duyệt; sửa file qua diff rõ ràng; tôn trọng tenant isolation và quyền riêng tư.

Đừng chạm production, giao dịch ngân hàng thật, gửi SMS/Zalo thật, xoá dữ liệu thật hoặc tự deploy/commit/push. Dùng database và tài khoản test cô lập. Hết phiên ghi checkpoint rõ ràng và cập nhật `context.gemini.md`, gửi commit message tiếng Việt có dấu.

**Mục tiêu:** chuyển từ “Admin có nhiều chức năng” thành “Admin vận hành đúng, an toàn, minh bạch và giải quyết được sự cố thực tế ngay từ lần đầu”.

## E. Giới hạn xác minh

Không có phiên Laravel/Chrome/MySQL/SePay đang chạy trong cuộc kiểm tra này. Các file đã xem là **snapshot Google Drive**, không được coi là live production. Việc số lượng test tăng từ 653 → 707 → 724 là lịch sử báo cáo, chưa phải kết quả tự chạy của người kiểm tra. Các mục trong phần B có trích logic từ mã nguồn thật; vẫn cần lập test để xác minh impact và triển khai bản vá an toàn.
