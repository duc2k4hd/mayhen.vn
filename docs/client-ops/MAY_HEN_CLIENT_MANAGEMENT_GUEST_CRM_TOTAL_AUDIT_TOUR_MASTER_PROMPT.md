# MÂY HẸN — CLIENT OPERATIONS EXCELLENCE V1
## MASTER PROMPT — Kiểm toán, hoàn thiện UX & logic quản lý khách hàng, khách mời, người tham dự và Interactive Product Tour

**Workspace chính:** `D:\Project\Laravel\mayhen.vn` hoặc workspace Mây Hẹn đang mở thực tế. **Không tự đoán ổ đĩa**: kiểm tra `pwd`/workspace root rồi xác minh `artisan`, `AGENTS.md`, `context.gemini.md`.

**Bản chất nhiệm vụ:** Kiểm tra và nâng cấp **hệ thống đã có**, không xây sản phẩm mới, không tạo dashboard hoặc CRUD thứ hai, không mở rộng sang Admin trừ khi có phụ thuộc logic bắt buộc. Hãy đặt mình vào vai Product Lead + Wedding Operations Specialist + Principal Laravel Engineer + Senior UX Designer + QA Lead + Security Engineer. Đây là nhiệm vụ làm cho cô dâu, chú rể và người hỗ trợ đám cưới **dễ dùng, ít sai, không mất dữ liệu**, chứ không phải thêm menu cho nhiều.

> Tài liệu này được biên soạn sau khi đối chiếu các file trong Google Drive Mây Hẹn mà chủ dự án đã cung cấp, gồm `routes/web.php`, `GuestController.php`, `AccountController.php`, `SeatingPlannerController.php`, `CheckInController.php`, `resources/views/client/pages/guests.blade.php`, `resources/views/client/pages/account/{dashboard,invitations,...}.blade.php`, `public/assets/client/js/pages/guests.js`, `AGENTS.md`, skill `mayhen-guest-rsvp`, `mayhen-testing-quality`, các tài liệu bất biến nghiệp vụ và ma trận E2E. **Ảnh chụp và Drive có thể cũ hơn workspace hiện tại**: code hiện tại là sự thật, buộc kiểm chứng lại. Nội dung dưới đây là tiêu chí kiểm toán, không mặc định khẳng định có lỗi hoặc thiếu tính năng.

---

# 0. NHỮNG ĐIỀU KHÔNG ĐƯỢC PHÉP LÀM

1. Không tin báo cáo “100% DONE” hoặc số lượng test cũ thay cho hành vi thật. Không tự gán PASS, không xóa regression test quan trọng sau khi chạy.
2. Không viết lại hệ thống quản lý khách mời/Studio/Admin. Tái sử dụng Controller, Model, Service, Blade, JS, routes, policies, FormRequest, CSS và bảng dữ liệu có sẵn.
3. Không gộp dữ liệu không cùng bản chất: **lời mời/hộ**, **số suất dự kiến**, **số người xác nhận** và **số người đã đến** là 4 chỉ số khác nhau. Người lớn và trẻ em phải nhất quán trong mọi báo cáo.
4. Không tạo trạng thái “Đã gửi Zalo”, “Đã xem”, “Đã nhận” nếu chưa có bằng chứng nguồn. Chép link ≠ gửi; mở Zalo ≠ gửi; nhận phản hồi ≠ đã xem mọi màn hình.
5. Không can thiệp vào thông tin khách thật của production. Chỉ dùng dữ liệu thử, sandbox, fixture, staging hoặc DB test riêng. Không gửi SMS/Zalo/Email thật, không chuyển tiền thật, không xóa dữ liệu production.
6. Không tự nâng cấp Laravel, thêm Tailwind/Bootstrap/React/Vue/Alpine/jQuery/GSAP/Fabric/Intro.js/Shepherd.js hoặc gói tour bên ngoài; dùng Blade + Vanilla CSS + Native JavaScript, đúng skill. Không thêm dependency nếu chưa được duyệt.
7. Không dùng popup tour để thực thi thao tác phá dữ liệu; không tự nhấn “Thêm”, “Xóa”, “Gửi”, “Đổi RSVP”, “Check-in”, “Hoàn tiền”, “Xuất bản”. Tour **chỉ giới thiệu**.
8. Không “thiết kế lại sang trọng” bằng cách bỏ nội dung nghiệp vụ, làm nhỏ chữ quá mức, màu tương phản thấp, animation lòe loẹt hoặc modal che mất nút đang giới thiệu.
9. Không tự commit, push, deploy. Cuối mỗi wave: cập nhật `context.gemini.md` theo `AGENTS.md` và đưa commit message tiếng Việt có dấu.
10. Không triển khai ý tưởng mới nếu một chỉnh sửa điều hướng/copy/validation/empty state đã giải quyết được vấn đề. Đặt tiêu chí **lợi ích người dùng đo được** trước code.

# 1. BẮT BUỘC ĐỌC, XÁC MINH VÀ LẬP BẢN ĐỒ ĐÚNG SOURCE

Đọc theo thứ tự:
- `context.gemini.md` toàn bộ phần liên quan Client, Guest CRM, RSVP, Seating, Check-In, Live Coordination, Customer Journey, Product Tour; `AGENTS.md`, `CLAUDE.md`, `.ai/rules/index.md` nếu tồn tại.
- `.agents/skills/mayhen-guest-rsvp/SKILL.md`, `mayhen-testing-quality`, `mayhen-ui-ux` (nếu có), `mayhen-auth-security`, `mayhen-project-architecture`, `mayhen-laravel-backend`, `mayhen-javascript`, `mayhen-css`, `mayhen-invitation-standards` khi chạm vào thiệp.
- `docs/MAY_HEN_MASTER_PRD_ADMIN_STUDIO_V3.md` **chỉ như đặc tả, không coi là bằng chứng code**; `docs/MAY_HEN_DOMAIN_INVARIANTS_AND_STATE_MACHINES.md`; `docs/MAY_HEN_CROSS_MODULE_E2E_MATRIX.md`; `docs/MAY_HEN_MICRO_UX_AND_ERROR_STATE_AUDIT.md` và bản đồ route/flow Excel/DOCX mới nhất nếu đang tồn tại.
- `routes/web.php` và các route registration liên quan; `app/Http/Controllers/Client/*` đặc biệt Account, Guest, CheckIn, SeatingPlanner, GiftManagement, WishManagement, PhotoDrop, LiveCoordination, FamilyApproval, TimeCapsule, PrivacyConsent, CustomDomain.
- Services/Requests/Policies/Middleware/Models/Migrations liên quan `Wedding`, `Invitation`, `Guest`, `GuestResponse`, `WeddingTable`, `WeddingWish`, `WeddingGift` và sự kiện nhiều buổi nếu có.
- Views: `resources/views/client/pages/account/*`, `resources/views/client/pages/guests.blade.php`, `checkin*.blade.php`, `guest-pass.blade.php`, `guest-concierge.blade.php`, `live-coordination.blade.php`, `public-invitation.blade.php`, `family-review.blade.php`, `photo-drop.blade.php`, `keepsake-book.blade.php`.
- Assets: `public/assets/client/js/pages/guests.js`, `seating-planner.js`, `checkin.js`, `live-coordination.js`, `account-readiness.js`, các CSS tương ứng; mọi JS inline trong Blade và các event listener khác.
- `tests/Feature/*Guest*`, `*Rsvp*`, `*CheckIn*`, `*Seating*`, `*Privacy*`, `*Cross*`, `*Account*`, `*Wish*`, `*Gift*` và tests hiện có khác.

Thực hiện `php artisan route:list --json` (hoặc `route:list` nếu cần), đối chiếu toàn bộ GET/POST/PUT/PATCH/DELETE, URL có tham số, middleware, route name, action, view, JS invocation, permission và dữ liệu được thay đổi.

Tạo file `docs/client-ops/00_SOURCE_TRUTH_AND_ROUTE_INVENTORY.md`: với mỗi route/action ghi status `EXISTS`, `UNVERIFIED`, `DOC_ONLY`, `DEPRECATED` hoặc `COVERED_BY_OTHER`, file/line có thật. Không suy diễn thiếu route từ menu ẩn vì có API, modal hoặc trạng thái chỉ lộ theo quyền.

**Hợp đồng nghiệp vụ cần phân biệt (đừng đánh đồng):**
- Chủ tài khoản (user) / chủ đám cưới (wedding owner) / người được ủy quyền / guest xem thiệp công khai / lễ tân có quyền check-in / Admin là các đối tượng khác nhau.
- `invitation` không nhất thiết bằng một đám cưới và một đám cưới có thể có nhiều thiệp/nhóm khách; xác nhận thực tế relation và quyền theo wedding + invitation + event.
- Khách mời (`guests`) là bản ghi trong danh sách; `guest_responses` là phản hồi; người đi cùng là sĩ số; không dùng `count()` khi cần `sum(adults_count+children_count)`.
- Nếu phiên bản mới hỗ trợ nhiều sự kiện, kiểm tra RSVP theo từng `event_id`/invitation-event và mọi phép tính phải tách scope sự kiện. Nếu không có, đánh dấu khoảng trống tương ứng theo PRD thay vì tự thêm bảng ngay.
- Khi đổi template hoặc sửa thiệp trong Studio, các link RSVP cá nhân, giới hạn gói, dữ liệu khách mời và quyền không được mất hoặc rò rỉ.

# 2. PHẠM VI — KIỂM KÊ ĐẾN THAO TÁC NHỎ NHẤT

## 2.1 Dashboard `/tai-khoan`
- Chỉ số tổng thiệp, lời mời, **tổng suất dự kiến**, số người xác nhận, lời chúc, số người đã tới; nhãn KPI phải phản ánh chính xác phép tính.
- Khi có nhiều weddings/invitations: lọc theo thiệp đang chọn vs toàn tài khoản; trạng thái nào là scope, ghi rõ trên UI.
- Thiệp gần đây, ngày cưới, chưa đặt ngày, hết hạn, đã khóa, đã xuất bản/nháp; hành động nhanh và trạng thái không tương thích.
- Hành trình “vừa tạo thiệp” → “hoàn thiện thông tin” → “xuất bản” → “thêm khách” → “gửi lời mời” → “theo dõi RSVP” → “xếp bàn” → “đón khách” → “cảm ơn”. Gợi ý có ngữ cảnh, không bắt khách đọc dashboard phức tạp.
- Sao chép link: thành công/thất bại clipboard, HTTP/HTTPS, browser không hỗ trợ, feedback rõ; không chia sẻ link riêng tư vô tình.
- Empty / 1 thiệp / nhiều thiệp / hàng nghìn khách / đơn hết hạn / không có dữ liệu / người mới chưa đăng nhập đầy đủ.

## 2.2 Danh sách thiệp `/tai-khoan/thiep` và quản lý từng thiệp
- Xem nhanh, sửa, cài đặt, nhân bản, xuất bản/tạm ẩn, khóa/hết hạn, xóa, khôi phục, bản nháp chưa lưu, gói/quyền lợi, tên miền và link cá nhân hóa.
- Chuyển đúng `invitation_id` ở mọi CTA. Không để mở thiệp A rồi thao tác khách của thiệp B.
- Thứ tự thao tác: nút chính rõ; công cụ ít dùng trong menu phụ; hành động nguy hiểm có xác nhận 2 bước + hậu quả dễ hiểu.
- Phân biệt “xem như khách”, “xem nháp”, “thiệp đã phát hành”; khi draft/locked/expired, tuyệt đối không cho guest tương tác qua endpoint phụ.
- Nhân bản và đổi mẫu: không sao chép hoặc mất danh sách khách, token, gift, message ngoài ý muốn; xác định rõ bản sao có dữ liệu nào.
- Tên thiệp dài, hình lỗi, gói quá hạn, duyệt điện thoại, ngày cưới quá khứ, link thay đổi, trạng thái chưa được quyền phát hành.

## 2.3 Quản lý danh sách khách mời `/tai-khoan/thiep/{id}/khach-moi` — ƯU TIÊN SỐ 1
### (A) Nhận biết nhanh và chỉ số
- KPI: số đầu mối mời/số hộ, suất dự kiến, số khách đã phản hồi, người tham dự xác nhận (adults+children), người từ chối, pending, số đã check-in, tỷ lệ phản hồi.
- 0 khách, 1 khách, gia đình 2–10 người, 10k khách, phát sinh người đi cùng, cùng họ tên, thiếu SĐT, trẻ em, hai sự kiện khác nhau; không phát sinh tỷ lệ >100% hoặc số ghế âm.
- Tooltip giải thích mỗi KPI bằng 1 câu: “Số khách mời” là số dòng hay số người; không đánh tráo.
### (B) Thêm khách riêng lẻ
- Tên, cách xưng hô, nhóm khách, số điện thoại, số suất dự kiến, bàn, quyền nhận lời mời, mẫu câu xưng hô, thông tin sự kiện được mời; đúng validation và mặc định.
- Gợi ý trùng an toàn, không chặn nhầm người trùng tên; lưu thành công vẫn giữ đúng bộ lọc/đúng đám cưới; focus input lỗi, không mất dữ liệu.
- Tạo token cá nhân không dự đoán được; không lộ token ra log/analytics/nguồn không cần thiết.
### (C) Nhập nhanh / CSV / hàng loạt
- Dán 1 dòng hoặc nhiều dòng; dấu phân cách `|`, `;`, tab; UTF-8 tiếng Việt, BOM Excel, xuống dòng Windows/Mac, dấu phẩy/dấu chấm phẩy bên trong tên.
- Preview dữ liệu trước khi nhập và thống kê `hợp lệ / bỏ qua / lỗi / trùng` nếu code chưa có; không ghi mù hàng nghìn dòng rồi báo thành công giả.
- Trường bắt buộc, quá dài, số điện thoại Việt, trùng tên/SĐT, ký tự có thể gây Excel Formula Injection, số suất cực lớn, nội dung độc hại, import lặp sau timeout; rollback toàn phần khi lỗi nghiêm trọng hoặc báo rõ partial success.
- Nếu trùng hoặc chưa có table_name hợp lệ, giải thích chứ không tự lập bàn giả. Batch không được bí mật đặt 2 suất khi UI khác nói mặc định 1 mà không giải thích.
### (D) Danh sách, bộ lọc, tìm kiếm
- Tìm tên có dấu/không dấu khi hạ tầng hỗ trợ, SĐT, nhóm, bàn, RSVP, đã check-in; kết hợp nhiều lọc; reset; URL query/back/forward; kết quả trống rõ.
- Thứ tự kết quả ổn định, tránh tải toàn bộ `get()` với danh sách lớn nếu chưa pagination; dùng server-side pagination & tổng kết ngoài phạm vi lọc theo semantics rõ ràng.
- Mobile: danh sách thành card/summary, hành động không bị ẩn ngoài vùng xem; desktop bảng ổn định, sticky header hợp lý; không dồn nhiều icon khó hiểu.
### (E) Mỗi dòng khách
- Xem thiệp bằng đúng token của khách, sao chép link, tạo lời mời Zalo/Messenger, thay cách xưng hô, sửa tên/SĐT/nhóm/số người/bàn, RSVP hộ, check-in, lịch sử, xóa.
- Tooltips + aria-label; click icon không nhầm dòng; thao tác xóa có xác nhận bằng tên; khôi phục nếu đã có cơ chế soft-delete.
- Khi update 1 khách không làm mất token, các lời chúc/RSVP hoặc đổi relation của wedding; check-in quá khứ và số người mới cập nhật phải nhất quán.
### (F) Gửi thiệp / nhắc nhở / cảm ơn
- Phân biệt `đã chuẩn bị lời mời`, `đã copy`, `đã mở Zalo`, `đã xác nhận gửi thủ công`, `đã được hệ thống gửi`, `đã xác nhận đọc` nếu hệ thống có tín hiệu thật; **không suy diễn gửi thành công**.
- Kiểm tra trước gửi: công khai/khóa/hạn, ngày và giờ đúng, địa điểm, bản đồ, số điện thoại, tên, guest token, phạm vi sự kiện, lịch gửi. Cảnh báo trước khi thao tác rủi ro.
- Nhắc RSVP tế nhị: target chỉ người cần nhắc, thời điểm hợp lý, mẫu nội dung tôn trọng, chống nhắc trùng, không gửi tự động khi người dùng chưa xác nhận.
- Lời cảm ơn sau cưới: phân biệt người tham dự và vắng mặt, không gửi sai đại từ hoặc nhắc chi tiết cá nhân không cần thiết.
### (G) Sửa và xóa
- Sửa tên, phone, group, salutation, table_id/table_name, expected_attendees; nếu RSVP/check-in đã có phải bảo toàn hay yêu cầu xác nhận các thay đổi ảnh hưởng sĩ số.
- Khi xóa khách có token đã chia sẻ, RSVP, check-in, bàn, gift/wish: giải thích tác động và áp dụng nghiệp vụ thống nhất. Không “xóa dòng” nếu làm mất dữ liệu lịch sử cần giữ.
- Kiểm tra chéo quyền khi người dùng chỉnh ID khách thuộc wedding khác, cả PUT/DELETE/API.

## 2.4 RSVP từ phía khách trên `/thiep/{slug}`
- Cá nhân theo token, chung không token, người mới chưa có tên, chủ thiệp tự RSVP, khách đổi ý, khách bị hủy lời mời, token sai/thiệp khóa/hết hạn.
- Trạng thái `pending`, `attending`, `declined`, người lớn/trẻ em, yêu cầu ăn kiêng, lời chúc, số người tối đa, nhiều sự kiện (nếu có).
- Không gửi trùng khi double-click, network retry, nhiều tab, đồng thời; khách đổi RSVP hợp lệ chỉ qua luồng đã định nghĩa, lưu audit trail.
- Manual RSVP từ chủ thiệp phải hiển thị rõ “chủ thiệp cập nhật hộ”, không ghi đè phản hồi của khách không dấu vết.
- Tỷ lệ phản hồi phải đo theo đầu mối/phiếu; số người tham dự theo adults+children; nhóm gia đình không được tính 1 người.
- Guest được biết ngay đã gửi thành công, mốc thời gian, cách sửa nếu thay đổi kế hoạch; nội dung tiếng Việt gần gũi.

## 2.5 Sơ đồ bàn `/tai-khoan/thiep/{id}/so-do-ban-tiec`
- Thêm/đổi tên/xóa bàn, sức chứa, loại bàn, màu, vị trí, kéo thả, phân khách, in sơ đồ, báo quá tải, healthCheck.
- Cặp đôi/gia đình không chia sai nếu không chủ ý; khách từ chối không chiếm chỗ; trẻ em có quy tắc chỗ ngồi rõ; bàn thay đổi cập nhật `table_id` và `table_name` nhất quán.
- Race 2 operator xếp cùng khách, xóa bàn có người, xếp khách vào bàn đầy, click nhanh, offline/409; rollback thao tác lỗi; kiểm tra MySQL transaction.
- Tránh màu sắc chỉ biểu thị trạng thái, có text tương ứng; mobile cho xếp bàn bằng form thay vì chỉ kéo thả.

## 2.6 Check-in và Live Coordination
- Check-in theo mã QR / token / tra cứu bằng tên; PIN, ca trực, role, trạng thái khách, thời gian check-in, số người thực tế đến, hoàn tác phải có quyền và xác nhận.
- Check-in lại không tăng người; nhiều thiết bị cùng quét không cộng đôi; check-in nhóm 4 người chỉ tới 2 phải phản ánh đúng 2.
- RSVP đổi sau check-in, khác sự kiện, người chưa mời, mã hết hạn, thiệp tạm khóa; tránh hành động ngược tràn lan.
- Cập nhật bàn và sơ đồ trực tiếp đúng wedding/event; Livestream dashboard không dùng số liệu cứng; fallback mất mạng và in giấy.
- Nếu ca trực dùng thiết bị của người lạ: session thời hạn, quyền tối thiểu, không lộ toàn bộ danh sách/SĐT.

## 2.7 Lời chúc, ký tên, Photo Drop, Live Wall, sổ mừng cưới
- Duyệt/ẩn/xóa/sửa phản hồi (nếu được phép); khi từ chối ảnh/lời chúc phải nhất quán public/Live Wall/Memory Vault.
- Phân quyền moderator, không lộ lời chúc riêng; phản hồi của cô dâu chú rể tới lời chúc; in/export; lọc phản cảm.
- Sổ mừng cưới: quyền xem riêng tư, cập nhật tiền mừng, lịch sử chỉnh sửa, nhãn đã cảm ơn; không hiển thị công khai tài chính.
- Album từ khách: dung lượng, định dạng, trùng, upload thất bại, duyệt nhiều ảnh, đóng tải sau sự kiện, link ZIP chỉ của wedding hợp lệ.

## 2.8 Những trang quản lý khác không được bỏ sót
- Tài khoản/hồ sơ/đổi mật khẩu, đơn hàng và quyền lợi gói, cài đặt thiệp, tên miền tùy chỉnh, thống kê, lời chúc, album, Memory Vault, Time Capsule, Family Review, Vendor Brief, Resilience Kit, Wedding Week Readiness, Live Wall Settings.
- **Chỉ cải thiện khi có vấn đề thực tế**: đừng tự đổi giao diện đẹp thành dashboard đa lớp vô nghĩa.
- Với từng trang: navigation → thao tác → API → service/model → state → feedback → quyền → mobile → empty/error/slow/offline → post-refresh state.

# 3. CÁC TÌNH HUỐNG NGHIỆP VỤ PHẢI XEM NHƯ BẮT BUỘC

1. 1 gia đình có một link, dự kiến 4 người, RSVP 2 người lớn + 1 trẻ, đến thật 2; tổng lời mời = 1, dự kiến = 4, xác nhận = 3, check-in = 2.
2. Nhà trai có 2 buổi, khách chỉ dự buổi tiệc; không cộng vào lễ còn lại.
3. Cùng một khách bị thêm hai lần; không âm thầm gộp sai và không nhân đôi số bàn khi người dùng import lại.
4. Khách RSVP “không tham dự”, được xếp bàn trước đó; ghế phải phản ánh theo đúng quy tắc domain.
5. Khách đã check-in nhưng admin/cặp đôi sửa sĩ số; không tạo tổng ảo.
6. Khách mở link trên Zalo, gửi RSVP hai lần vì mạng chậm; đúng một bản ghi nghiệp vụ.
7. Hai lễ tân scan QR gần như cùng thời điểm; đúng một check-in.
8. Khách không có điện thoại, người già, khách đi cùng trẻ nhỏ; vẫn có luồng hỗ trợ dễ hiểu.
9. Gửi lời mời vào Zalo chỉ mới copy thành công; không đánh dấu “đã nhận”.
10. Xóa một bàn có 7 khách; không còn stale `table_name` hoặc ghế ma.
11. Hủy xuất bản thiệp: mọi POST từ form public/RSVP/ảnh/lời chúc/QR phụ đều tuân theo khóa.
12. Đổi tên cô dâu/chú rể, ngày cưới, địa điểm trong Studio: thông tin liên quan, link, thông báo gửi, sự kiện, RSVP/nhắc nhở cần nhất quán.
13. Chuyển gói hoặc hết hạn: không xóa dữ liệu khách hoặc khóa bất ngờ cổng nội bộ cần thiết để xuất dữ liệu.
14. Một tài khoản có nhiều thiệp: chọn nhầm thiệp A/B không được làm dữ liệu chéo.
15. Hai thành viên cùng quản lý một thiệp khi có permission hợp lệ: xung đột phải phát hiện, không silent overwrite.
16. Quản lý 10k–30k khách trong môi trường test: phân trang, tìm kiếm, thống kê, xuất file phải ổn định, không N+1 hoặc OOM.
17. CSV chứa chuỗi bắt đầu `=`, `+`, `-`, `@` không được bị Excel thực thi công thức; vẫn giữ nguyên giá trị người dùng khi cần.
18. Guest token ở URL, preview/thống kê, mạng xã hội và analytics: không làm lộ sang domain ngoài theo Referrer hoặc log nếu chưa cần.
19. Refresh khi modal đang mở, lọc danh sách, clipboard bị chặn, mất mạng lúc save, session hết hạn, back-forward, bấm nút liên tục.
20. Trường hợp không có wedding/thiệp/khách, account chưa đăng nhập, khách không đủ quyền, wedding bị archive, thiệp đã khóa/hết hạn.

# 4. CHỈNH UX GỌN — QUY TẮC THIẾT KẾ

**Phương pháp:** Audit hiện trạng → phác thảo thay đổi tối thiểu → sửa đúng điểm gây khó → test thực tế. Không clone dashboard đối thủ. Theo Quiet Luxury của Mây Hẹn nhưng chủ yếu trung tính, rõ ràng.

- Khi vào `Khách mời`, tiêu đề rõ; một hàng KPI có thể hiểu; top tasks “Thêm khách”, “Nhập danh sách”, “Gửi lời mời”, “Xem phản hồi”; thao tác ít dùng vào menu “Thêm”.
- Chỉ hiển thị thông tin phù hợp với trạng thái. Khi chưa có khách, thay bảng trống bằng 1 CTA “Thêm khách đầu tiên”; khi khách đã trả lời, ưu tiên RSVP và xếp bàn.
- Nút thao tác luôn có động từ và đối tượng. Nút icon-only bắt buộc có `aria-label`, tooltip; nội dung khó hiểu được giải thích bằng một câu (không thuật ngữ backend).
- Các KPI phải có nhãn, đơn vị, scope, tooltip để người dùng phân biệt người/thiệp/nhóm/suất; báo cáo tải chậm có skeleton đúng kích thước.
- Desktop: vùng đọc đủ rộng, bảng không vỡ, sticky header chỉ khi hữu ích; responsive ~320/360/375/390/430/768/1024/1366/1440/1920; không ẩn chức năng chính ở mobile.
- Mobile: CTA chính đặt vừa tầm chạm, sidebar/filters phù hợp; bảng khách có card hoặc phiên bản cuộn có nhãn rõ; không modal lớn vượt chiều cao gây mất nút Save.
- Màu đỏ chỉ dành cho nguy hiểm/cảnh báo, không rải đỏ ở mọi nút. Font rõ, chiều cao dòng thoáng, phù hợp người dùng lớn tuổi; tránh nhiều khung card lồng nhau hoặc bóng đổ đậm.
- Với mọi request: trạng thái idle/loading/success/validation/error/403/404/409/410/422/429/500/offline, retry an toàn, thông báo không làm mất dữ liệu đang nhập.
- Không thay đổi screenshot đẹp chỉ vì “modern”; có so sánh Before/After và kiểm tra bằng mắt người.

# 5. INTERACTIVE PRODUCT TOUR — CLIENT MANAGEMENT / GUEST CRM

## 5.1 Phạm vi và trải nghiệm chính

Tạo tour **độc lập với `/tao-thiep`**, cho **người quản lý thiệp** (đã đăng nhập hoặc sở hữu thiệp qua session hợp lệ), không tự bật trên thiệp gửi khách, `/thiep/{slug}`, RSVP public, check-in lễ tân trừ khi có tour riêng được yêu cầu sau.

- **Tour A — Tổng quan quản lý:** khi người dùng lần đầu vào `/tai-khoan` với UI đã sẵn sàng. 6–8 bước dự kiến: chọn thiệp, trạng thái thiệp, sửa/xem thiệp, quản lý khách, RSVP tổng quan, xếp bàn, hướng dẫn/trợ giúp, nhóm nghiệp vụ khác. Chỉ chọn những anchor tồn tại thực tế.
- **Tour B — Khách mời & RSVP:** khi lần đầu vào trang `/tai-khoan/thiep/{id}/khach-moi` (kể cả đi thẳng từ link) và tài khoản chưa hoàn thành/bỏ qua tour vùng này. Khoảng 8–12 bước ưu tiên: KPI, thêm khách, nhập hàng loạt, tìm/lọc, từng dòng khách, link mời Zalo, RSVP thủ công, kế hoạch nhắc, xuất/in, sơ đồ bàn và check-in. Không bắt buộc quá 12; có thể gộp nhóm.
- **Tour ngữ cảnh khác:** chỉ bật bằng **nút `Hướng dẫn`** tại các màn như seating, check-in, live coordination nếu thực sự cần và đã có anchor; tuyệt đối không bắt người dùng qua 30–50 bước một lượt.
- Sau khi người dùng xem xong hoặc nhấn `Bỏ qua`, lưu trạng thái **độc lập theo `tour_id`, version và user/owner scope**. Nếu dùng localStorage vì đơn giản, xác minh chính sách profile multi-user và multi-device; không đánh dấu đã xem bởi người dùng khác trên máy dùng chung. Không lưu tên/SĐT/guest token trong key/storage. Có thể dùng metadata server nếu thực sự cần, không tự tạo bảng lớn chỉ để đếm một flag.
- Luôn có nút **`Hướng dẫn`** ở header/toolbar phù hợp (dưới menu Trợ giúp khi mobile) để chủ động xem lại từ đầu, và tùy chọn `Xem bước này` ở khu vực khó.

## 5.2 UI tour cần đạt

- Một overlay mờ nhẹ (~45–55%, điều chỉnh theo screenshot thật) và **spotlight thật quanh chính DOM target**; không clone target, không đổi dữ liệu, không giả tạo nút. Highlight viền champagne mảnh, bóng rất nhẹ.
- Hộp tour 260–360px desktop, tối đa `calc(100vw - 24px)` mobile, chữ ít; title khoảng 1 dòng, mô tả 1–3 câu, nếu cần tối đa 1 ví dụ. Không dài dòng “Hệ thống cho phép quý khách...”.
- Hàng điều khiển `Bỏ qua` | `Quay lại` | `Tiếp` / `Hoàn tất`; hiện `Bước X/Y` và progress mảnh, có nút đóng rõ. Step đầu không cho Quay lại; step cuối đổi thành Hoàn tất. Nút phải thực sự hoạt động cả chuột và bàn phím.
- Khi target ở ngoài viewport: tìm đúng scroll container, `scrollIntoView`/scroll có bù sticky header, chờ DOM ổn định; khi target trong accordion/tab thì **mở bằng UI state tạm thời** không submit/sửa data; nếu anchor không tồn tại theo quyền/màn hình → skip step hợp lệ, không để lỗ spotlight lạc vị trí.
- Recalculate spotlight khi resize, scroll, zoom, font load, thay đổi content, `ResizeObserver` nếu cần; không vượt viewport, tránh che target; mobile chuyển tour card dạng bottom sheet khi anchor gần mép.
- A11y: `role=dialog`, `aria-modal`, focus management/trap khi mở, ESC để bỏ qua có xác nhận nhẹ, return focus về nút ban đầu, keyboard Tab/Shift+Tab, outline rõ; `prefers-reduced-motion: reduce`, không lạm dụng animation.
- Tuyệt đối không tự click/thực hiện hành động dữ liệu. Đặc biệt không tự tạo guest, không gửi Zalo, không tự set RSVP, không check-in, không xuất/xóa bất kỳ bản ghi nào.
- Khi mở tour từ một form đang nhập dở: tạm dừng nếu có nguy cơ che form; tour không reset state, thay bộ lọc hoặc làm mất focus/dữ liệu. Khi rời route, đóng và cleanup listeners/observers/scroll locks.

## 5.3 Mẫu nội dung tour ngắn gọn, sửa theo tên UI thật

| ID | Mục tiêu | Nội dung ví dụ |
|---|---|---|
| `ACCOUNT_WELCOME` | Khu vực thiệp hiện tại | “Đây là thiệp bạn đang quản lý. Chọn thiệp khác nếu bạn có nhiều đám cưới.” |
| `ACCOUNT_EDIT` | Nút sửa thiệp | “Cần đổi ảnh, ngày cưới hay lời mời? Bấm Sửa thiệp để mở Studio.” |
| `ACCOUNT_GUESTS` | Đi đến khách mời | “Thêm người thân và bạn bè tại đây, mỗi người sẽ có link thiệp riêng.” |
| `GUEST_STATS` | Dãy KPI | “Xem số lời mời, số người dự kiến và phản hồi. Các con số này không giống nhau.” |
| `GUEST_ADD` | Form thêm khách | “Thêm tên và số suất dự kiến của một khách hoặc một gia đình.” |
| `GUEST_BATCH` | Tab nhập nhanh | “Đã có sẵn danh sách? Dán nhiều dòng để nhập một lượt.” |
| `GUEST_FILTER` | Tìm và lọc | “Tìm theo tên, nhóm, bàn hoặc tình trạng xác nhận.” |
| `GUEST_SHARE` | Mở lời mời Zalo | “Soạn lời mời và sao chép link cá nhân. Bạn chủ động gửi cho khách qua Zalo.” |
| `GUEST_RSVP` | Sửa RSVP hộ | “Nếu khách báo qua điện thoại, bạn có thể ghi nhận phản hồi giúp họ.” |
| `GUEST_SEATING` | Đi sơ đồ bàn | “Khi khách xác nhận, chuyển sang xếp bàn và kiểm tra sức chứa.” |
| `GUEST_CHECKIN` | Đi check-in | “Ngày cưới, lễ tân có thể dùng mã QR để ghi nhận khách đã đến.” |
| `GUEST_DONE` | Tùy chọn hoàn thành | “Bạn đã biết những bước chính. Khi cần, bấm Hướng dẫn để xem lại.” |

**Không hardcode 12 step nếu DOM không có đủ target.** Agent phải xây danh sách thực từ view + route + permissions và kiểm thử từng target ở trạng thái có dữ liệu/trống/mobile.

## 5.4 Kiến trúc đề xuất (phải so với source trước khi quyết định)

- Tái sử dụng `resources/views/client/pages/account/layout.blade.php` và các view hiện tại. Chỉ thêm component nhỏ (ví dụ `client.components.product-tour`) nếu chưa có tiện ích tương đương.
- CSS namespace `mayhen_account_tour_*` cho account; `mayhen_guest_tour_*` cho Guest CRM; phần dùng chung có thể `mayhen_client_tour_*`, không ô nhiễm `.modal`, `.overlay`, `.button` toàn site.
- JS module riêng `public/assets/client/js/components/account-product-tour.js` hoặc thư mục tương ứng đang tồn tại, không viết một file khổng lồ. Config step registry dạng dữ liệu `tour_id`, `target`, `title`, `description`, `showWhen`, `openUI`, `placement`; dùng `data-mayhen-tour-target` ổn định, KHÔNG query theo `nth-child`/text động nếu tránh được.
- Chỉ khởi tạo module khi route thuộc phạm vi; không làm tour hiện trên `/tao-thiep` (vì đã có tour riêng), `/admin`, `/docs`, `/thiep/{slug}` hoặc login.
- Độ trễ: đợi page thật sẵn sàng, tránh popup đè first paint; nếu user đang tương tác, tránh bật bất ngờ. Focus và scroll của trang phải phục hồi chuẩn sau khi đóng.
- `localStorage`/server metadata có migration version ở cấp flag; khi ra bản mới, chỉ auto-tour lại nếu thật sự có thay đổi mang tính bước ngoặt và được chủ sản phẩm chấp thuận; mặc định **không ép xem lại**.

# 6. ƯU TIÊN THỰC HIỆN — KHÔNG LÀM LINH TINH

**W00 — Snapshot & hiện trạng (READ ONLY):** kiểm tra code/runtime/log/DB schema, sitemap route, các luồng thật, feature flags, rủi ro. Chụp trang đã đăng nhập với ít/đủ/nhiều dữ liệu. Tạo audit có bằng chứng.

**W01 — Guest CRM correctness P0:** kiểm chứng thống kê guest/count/RSVP, bảo mật multi-wedding, token, guest manual RSVP, khóa thiệp, phân quyền, validate import và duplicate. Chỉ sửa lỗi đã tái hiện; regression test riêng.

**W02 — Guest usability P1:** sửa các điểm nghẽn thao tác thật như thêm khách, tìm/lọc, nhập nhanh, modal RSVP, chia sẻ Zalo, empty/error states; thử với người mới. Không đổi kiến trúc khi chỉ cần copy/flow.

**W03 — Seating + check-in P0/P1:** consistency, idempotency, concurrent scan, in/print/backup, cân số người, phân quyền lễ tân. Test trên MySQL nếu có giao dịch/lock.

**W04 — Account portal P1:** dashboard, invitation list, settings, orders, profile, nhiều thiệp, quyền riêng tư. Tối ưu navigation, tránh KPI sai nghĩa.

**W05 — Guest experience P1:** khách nhận link, mở thiệp mobile, RSVP, sửa phản hồi đúng quyền, bản đồ, check-in pass, lời chúc; người lớn tuổi, accessibility.

**W06 — Interactive Product Tour:** tour A tổng quan account + tour B khách mời/RSVP độc lập, nút xem lại, bỏ qua, nhớ trạng thái, responsive/a11y. Mọi bước có đối tượng thật; không thao tác dữ liệu.

**W07 — Các phân hệ phụ thuộc:** Photo Drop, Gift, Wishes, Live Wall, Memory Vault, Time Capsule, domain, recovery, readiness; ưu tiên chỗ có tác động tới khách mời và người dự.

**W08 — Visual QA + regression:** browser authenticated, desktop/mobile, test mọi modal, bảng, empty state, tour state, export/in, tương phản, tab order; manual UAT có nhân sự giả lập vai trò cô dâu/chú rể/khách/lễ tân.

**W09 — Release evidence:** chạy feature+unit, test trên DB tương đương production, tổng kết lỗi còn mở, PR checklist, kiểm chứng không trùng tool/tour, không tạo menu giả. Không đánh dấu DONE thiếu evidence.

# 7. CHECKLIST KIỂM THỬ MỖI TÍNH NĂNG NHỎ (ÍT NHẤT 15 NHÓM)

Với **mọi** route, nút, input, modal, dropdown, tab, action, API, export, form, link, tooltip, chart, cần kiểm thử:

1. Không có dữ liệu / một bản ghi / nhiều bản ghi / dữ liệu rất lớn.
2. Hợp lệ, thiếu trường, sai định dạng, quá dài, Unicode/tiếng Việt, số 0, số âm, giá trị biên.
3. Quyền: khách chưa đăng nhập; chủ wedding A/B; lễ tân; người bị thu hồi quyền; request trực tiếp bỏ qua UI.
4. Scope invitation/event/wedding; nguy cơ IDOR/tenant bleed và token replay.
5. Click một lần, double-click, click liên tục, tab đang chạy request, request retry, nhiều thiết bị.
6. Thành công với feedback đúng, dữ liệu cập nhật sau F5, back-forward, không duplicate.
7. Backend 4xx/5xx, JSON sai, timeout, mạng offline, 429, 409, hết session.
8. Phía server validation (không chỉ JS), CSRF, XSS, query injection, formula injection, PII.
9. Đồng bộ các view khác: RSVP→table→check-in→live; thu hồi/khóa→public API; invite name→Zalo preview.
10. Undo/khôi phục hoặc xác nhận tác động khi thao tác xóa/sửa dữ liệu lịch sử.
11. Trình duyệt/viewport 320/360/375/390/430/768/1024/1366; bàn phím và người dùng không rành công nghệ.
12. Loading/empty/error/success/disabled, button focus, aria-label, keyboard nav, reduced-motion.
13. Hiệu năng: DB query count, memory, pagination, CSV streaming, N+1, cache validity.
14. Analytics/audit log có cơ sở, không lưu PII quá mức; trạng thái “đã gửi” đúng chứng cứ.
15. Không tác động bất ngờ tới chức năng khác; giữ tests cũ và thêm regression test tồn tại lâu dài.

**Đặc biệt tour:** kiểm tra first visit, skip, finish, replay, storage disabled, user A/B cùng máy, viewport rotate, zoom 125–200%, scrollbar, sticky header, dialog đang mở, target chưa có hoặc bị ẩn, thiếu quyền, scroll nested, Escape/Tab/Shift+Tab, tương phản, reduced motion; không thay đổi DB trong toàn tour.

# 8. YÊU CẦU TÀI LIỆU VÀ BẰNG CHỨNG (KHÔNG CHỈ BÁO CÁO)

Trong `docs/client-ops/` tạo và duy trì:

- `00_SOURCE_TRUTH_AND_ROUTE_INVENTORY.md`: route → action → view → JS → Service/Model → DB → quyền → test → trạng thái chứng cứ.
- `01_USER_JOURNEYS_AND_ROLE_MATRIX.md`: cô dâu, chú rể, người hỗ trợ, khách mời, lễ tân, gia đình; luồng từ tạo thiệp đến sau cưới.
- `02_ATOMIC_FUNCTION_CHECKLIST.md`: mọi nút/input/form/API/export, ID cố định, các trạng thái, file nguồn, expected behavior, không bỏ sót.
- `03_BUG_REGISTER.md`: P0/P1/P2, bước tái hiện, expected/actual, root cause, file/line, test đỏ trước sửa, trạng thái sau sửa.
- `04_GUEST_HEADCOUNT_AND_STATE_CONTRACT.md`: từ điển số liệu, trạng thái Guest/RSVP/Check-in/multi-event, bất biến, query tiêu chuẩn.
- `05_UX_NAVIGATION_AND_UI_REVIEW.md`: ảnh before/after, mobile, thời gian thao tác, số click, vấn đề thực tế, quyết định giữ/sửa/bỏ.
- `06_INTERACTIVE_PRODUCT_TOUR_DESIGN.md`: step registry tour A/B, target selector, copy tiếng Việt, state persistence, viewport, a11y, restart, evidence.
- `07_TEST_MATRIX_AND_EVIDENCE.md`: case ID, module, type, expected, actual, runtime command, DB engine, screenshot/log, PASS/FAIL/BLOCKED/NOT_RUN.
- `08_RELEASE_CHECKLIST.md`: điều kiện Go/No-go, nợ kỹ thuật, chưa test, không tự nâng tỷ lệ coverage.

**Một case PASS khi:** có code path + test/đường thao tác thật + expected/actual + evidence. `php artisan test` xanh không thay thế browser, load test, MySQL, privacy review, manual guest UX. Các case chưa thể test phải ghi `BLOCKED` hoặc `NOT_RUN` và lý do cụ thể. Không dùng “Test passed 100%” hàng loạt.

# 9. TRIỂN KHAI THEO VÒNG LẶP CHẶT CHẼ

1. Đọc và xác minh mã nguồn hiện tại; cập nhật inventory; thiết lập test DB cô lập.
2. Xác định **một** chức năng hoặc lỗi ưu tiên; viết mini-spec hành vi mong đợi, không đoán.
3. Tái hiện lỗi hoặc xác định UX pain bằng kịch bản thực tế; ghi bằng chứng.
4. Viết regression test (Feature/Unit), nếu UI thì thêm browser evidence; **không xóa test sau sửa**.
5. Sửa nhỏ nhất có thể; bảo toàn dữ liệu và interface; tránh thay schema nếu không cần.
6. Test cục bộ, test liên thông, cross-tenant, mobile; xác nhận không phát sinh lỗi P0 khác.
7. Cập nhật tài liệu, `context.gemini.md`, phần task đã hoàn tất và thứ tự tiếp theo. Chỉ khi có evidence mới đánh dấu DONE.
8. Tự tiếp tục task an toàn kế tiếp trong phiên. Nếu gặp quyết định sản phẩm/financial/legal không chắc chắn, dừng đúng task và báo các lựa chọn, không tự phát minh nghiệp vụ.

**Tại cuối mỗi wave, báo cáo ngắn:** Đã đọc file nào; hành vi thật; lỗi/UX bất tiện có tái hiện; file code đã thay; tests thực chạy với kết quả; ảnh UI đã xem; danh sách chưa kiểm thử; công việc kế tiếp; commit tiếng Việt có dấu. Không cam kết công việc chạy nền sau khi kết thúc phiên.

# 10. LỆNH KHỞI ĐỘNG DÀNH CHO AI LẬP TRÌNH

**Không hỏi lại yêu cầu đã rõ. Không chỉ lên plan.** Bắt đầu kiểm tra workspace và `context.gemini.md`, đọc đầy đủ nguồn thật, chạy baseline trong môi trường thử; tạo các tài liệu 00–08 sau khi đã rà soát. **Ưu tiên tuyệt đối W01–W03 về Guest/RSVP/Seating/Check-in; sau đó UX và Tour**. Khi hoàn thành từng hạng mục phải kiểm chứng được. Tài liệu không thay thế source. Quy tắc triển khai: sửa đúng nghiệp vụ hiện hữu và làm dễ sử dụng, sạch sẽ, đẹp, hoàn thiện; không thêm thứ linh tinh ngoài yêu cầu.

## Definition of Done cuối cùng

- Mỗi route/action/control trong account & guest management có ID và trạng thái kiểm chứng, không còn endpoint không biết thuộc luồng nào.
- KPI phản ánh đúng suất, người, hộ, phản hồi, check-in; thống nhất theo wedding/event.
- Chủ thiệp quản lý khách với ít thao tác, không cần hiểu khái niệm kỹ thuật; mobile dùng được.
- Tour tự hiện hợp lý **lần đầu** ở portal và Guest CRM, có Tiếp/Quay lại/Bỏ qua/Hoàn tất/Xem lại; không giả dữ liệu, không tự chạy thao tác; không trùng Tour Studio.
- Các thao tác của khách public và lễ tân bảo mật; không rò rỉ dữ liệu khách khác/đám cưới khác; replay và concurrency được xử lý.
- Tất cả thay đổi tồn tại sau F5, giữ được bản cũ, không làm hỏng template/RSVP/check-in/export/Studio.
- Test phù hợp đã chạy và bằng chứng UI + backend đi kèm; không có blocker P0 chưa giải quyết trước khi phát hành.
