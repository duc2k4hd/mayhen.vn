# Mây Hẹn — roadmap triển khai từ hiện trạng

Ngày lập: 06/11/2026. Đây là roadmap ban đầu dựa trên kiểm toán tĩnh từng phần trong `MAY_HEN_PROJECT_AUDIT.md`; cập nhật sau khi khảo sát đầy đủ, xác minh độc lập các rủi ro và chạy test. Không đánh dấu hoàn tất chỉ vì đã có route, giao diện hoặc tài liệu.

## Thứ tự và checklist

| ID | Ưu tiên | Công việc | Phụ thuộc | Trạng thái |
|---|---|---|---|---|
| DISC-01 | P0 | Hoàn tất bản đồ các module, database, PRD và kiểm toán bảo mật độc lập; lập baseline test an toàn | Không | Đang thực hiện |
| SEC-01 | P0 | Ràng buộc quyền xem/thêm/xóa khách với chủ wedding và session tạo thiệp | DISC-01; xác minh quyền hiện tại | Đã sửa mã, chờ chạy test và xác minh độc lập |
| PAY-01 | P0 | Đóng xác thực webhook, kiểm tra quyền gắn đơn và giao dịch lặp | DISC-01; hợp đồng SePay, cấu hình test an toàn | Đã sửa một phần, chờ test và đối soát |
| RSVP-01 | P0 | Ràng buộc token với đúng wedding; kiểm thử quyền, response và IP dùng chung | SEC-01; quyết định tương thích RSVP | Đã sửa phạm vi token, chờ kiểm thử và tích hợp mẫu |
| STU-01 | P0 | Bảo đảm autosave đồng bộ thay đổi mới, revision và trạng thái lưu đúng | DISC-01; test tái hiện có kiểm soát | Đã sửa phần đồng bộ client, chờ chạy test và kiểm tra tích hợp |
| PUB-01 | P0 | Chốt nháp/công khai, publish, expiry và an toàn dữ liệu khi đổi mẫu | STU-01; thiết kế dữ liệu | Chưa bắt đầu |
| QA-01 | P0 | Đối chiếu test 6 section với contract 10 section và template thực | DISC-01 | Chưa bắt đầu |
| OPS-01 | P0 | Kiểm tra backup/restore, secrets, log, monitoring và cấu hình phát hành | DISC-01 | Chưa bắt đầu |
| ADM-01 | P1 | Mở rộng admin theo nhu cầu thật: quyền, audit và tra cứu thiệp/đơn | SEC-01, PAY-01 | Chưa bắt đầu |
| GUEST-02 | P1 | Import/export, nhóm khách và lịch sử phân phối có consent | SEC-01, RSVP-01 | Chưa bắt đầu |
| TMP-02 | P1 | Thiết kế version mẫu và chiến lược giữ thiệp đang xuất bản | PUB-01 | Chưa bắt đầu |
| SCALE-01 | P2 | Event, phân bàn, check-in, agency và automation theo nhu cầu | P0 ổn định; mô hình quyền đã thống nhất | Chưa bắt đầu |

## Các task P0 đầu tiên

### DISC-01 — Kiểm tra nền tảng và thiết lập môi trường xác minh

- **Mục tiêu:** hoàn tất đối chiếu PRD, mã, schema và luồng người dùng; xác định trạng thái từng module dựa trên bằng chứng, không dùng dữ liệu thật.
- **Phạm vi:** toàn bộ routes, controller, model, migration, test, Blade/JS và cấu hình liên quan; xác minh các khuyến nghị bảo mật độc lập. Không chỉnh code, cài package hay chạy migration phá dữ liệu.
- **Nghiệm thu:** bản đồ module có file và điều kiện xác minh; ghi rõ những phần chưa kiểm tra; môi trường test độc lập đáp ứng giới hạn bảo mật; test baseline có kết quả và lỗi thật được ghi lại.
- **Cập nhật khảo sát:** test configuration dùng SQLite in-memory và các driver kiểm thử, nhưng chưa chứng minh sandbox bắt buộc. Chưa tìm thấy Docker/Podman, WSL báo chưa cài; lần gọi agent độc lập lỗi model kế thừa. Tiếp tục khảo sát tĩnh và chuẩn bị môi trường cách ly, không ghi nhận test đã chạy.
- **Rủi ro/rollback:** chỉ đọc; không có thay đổi database. Giữ mọi thay đổi sẵn có trong working tree.

### SEC-01 — Quyền quản lý khách theo chủ sở hữu

- **Lý do:** action quản lý khách hiện truy vấn theo ID mà chưa thấy gọi kiểm tra `Invitation::isOwner()`.
- **Phạm vi dự kiến:** `GuestController`, route/request liên quan và feature tests trong `tests/`; không cần migration dự kiến. Giữ hỗ trợ thiệp khách tạo bằng session.
- **Quy tắc:** chủ thiệp hợp lệ được xem/thêm/xóa khách của wedding mình; tài khoản khác, session khác và khách không có quyền bị từ chối; không tiết lộ dữ liệu hoặc sửa nhầm wedding. Xác định cách xử lý khi wedding có nhiều thiệp trước khi dùng một thiệp bất kỳ để kiểm tra.
- **Lưu ý đã xác minh trong source:** trang khách hiển thị URL chứa token; thao tác xóa chỉ có guest ID. Quyền cần được kiểm tra theo wedding gắn guest, bao gồm trường hợp một wedding có nhiều thiệp và người tạo nắm một slug hợp lệ trong session.
- **Kiểm thử/nghiệm thu:** kiểm tra GET/POST/DELETE bằng hai chủ sở hữu, khách vô danh có/không session hợp lệ; kiểm tra không có tác dụng phụ với dữ liệu người khác; kiểm tra CSRF, validation và liên kết quản lý khách hiện tại.
- **Tiến độ:** `GuestController` kiểm tra user sở hữu wedding hoặc session có slug của thiệp thuộc wedding trước khi xem/thêm/xóa; trường hợp khác trả 404. Đã thêm `tests/Feature/GuestManagementAuthorizationTest.php` bao phủ chủ tài khoản, tài khoản khác, khách không session, session cùng wedding nhiều thiệp và session wedding khác. Mới kiểm tra cú pháp PHP; chưa chạy feature test hay xác minh độc lập, chưa coi task hoàn thành.
- **Rủi ro/rollback:** chọn sai cơ chế quyền có thể khóa khách tạo thiệp ẩn danh; hoàn nguyên thay đổi controller/test, không thay đổi dữ liệu.

### PAY-01 — Xác thực và đối soát thanh toán

- **Lý do:** mã xử lý hiện có nhánh tiếp tục khi thiếu header xác thực; quyền sở hữu thiệp ở request tạo đơn chưa được chứng minh.
- **Phụ thuộc:** tài liệu giao thức SePay hiện hành, cấu hình kiểm thử không có secret thật, phân tích đường đi CSRF/middleware và xử lý concurrent.
- **Phạm vi dự kiến:** checkout controller, request, cấu hình provider và test; schema chỉ thay đổi khi chứng minh cần lưu transaction/event để chống lặp, kèm migration tương thích và phương án rollback.
- **Quy tắc/nghiệm thu:** thiếu/sai secret bị từ chối; chỉ chấp nhận format xác thực đúng provider; đối chiếu đơn, số tiền, loại giao dịch và ID giao dịch; retries/concurrency không cấp quyền hai lần; đơn không được gắn thiệp người khác; lỗi không lộ secret. Test dùng dữ liệu giả, không gọi gateway thật.
- **Đối chiếu bổ sung:** kiểm tra plan vẫn active khi tạo đơn; thống nhất cách tính hạn dùng giữa webhook và thao tác admin; chuyển thông tin nhận thanh toán sang cấu hình sau khi xác định hợp đồng vận hành, không làm thay đổi giao dịch lịch sử.
- **Tiến độ:** xác thực HMAC-SHA256 với `SEPAY_WEBHOOK_SECRET` cho `/api/payments/sepay/webhook` và route cũ, kiểm tra timestamp, tài khoản nhận và loại giao dịch; cấu hình tài khoản QR qua `SEPAY_BANK_*`. Mã mới có tiền tố `MH` và 10 ký tự, vẫn nhận mã cũ 8 ký tự. Hai route được miễn CSRF trong cấu hình; còn phải xác minh đường đi thực tế. Đã bổ sung `tests/Feature/CheckoutAuthorizationTest.php`, chưa chạy feature test hoặc Pint. Chưa triển khai đối soát transaction ID, xác nhận concurrency/idempotency hay kiểm tra dịch vụ thật; không coi PAY-01 đã hoàn thành. Cần cấu hình cùng secret và tài khoản ở production, lưu cấu hình webhook tương ứng tại SePay.
- **Cập nhật đối soát:** webhook khóa đơn bằng `lockForUpdate()` trong transaction, chỉ chuyển `pending` sang `paid`, cập nhật đơn và thiệp cùng transaction và yêu cầu số tiền nguyên dương khớp chính xác. Đã gỡ thao tác admin đánh dấu đã thanh toán không qua đối soát theo PRD; cần xây dựng quy trình đối soát thủ công có quyền và audit trước khi khôi phục. Đã bổ sung ca test gửi lại, số tiền thiếu/thừa/không hợp lệ, trạng thái không hợp lệ, rollback và chặn thao tác admin; chưa chạy test. Khóa dòng cần được kiểm thử với MySQL thực tế; SQLite không biên dịch `FOR UPDATE`. Chưa lưu hoặc ràng buộc duy nhất ID giao dịch SePay nên chưa khẳng định idempotency toàn diện.
- **Rủi ro/rollback:** thay đổi xác thực có thể ngắt callback đang vận hành; triển khai sau khi kiểm tra cấu hình thật, có giám sát và phương án xử lý giao dịch chờ, không tùy tiện sửa các đơn lịch sử.

### RSVP-01 — Token và phạm vi phản hồi

- **Lý do:** truy vấn token khách trong RSVP hiện không lọc theo wedding; quy tắc chặn bằng IP có thể ảnh hưởng khách cùng mạng.
- **Phạm vi:** `GuestController`, request và feature tests; giữ nguyên hành vi cũ cho đến khi chính sách thay đổi phản hồi được thống nhất.
- **Nghiệm thu:** token của wedding A không liên kết response của wedding B; token sai không gây lộ dữ liệu; khách hợp lệ và phản hồi không token tuân thủ chính sách; kiểm tra số người lớn/trẻ em và cạnh tranh gửi.
- **Tiến độ:** RSVP có token được kiểm tra theo khách thuộc wedding, không token vẫn giới hạn mỗi IP trong nhóm không token; trang thiệp chỉ cung cấp phản hồi cá nhân khi có token hợp lệ. Tạo RSVP và lời chúc trong cùng transaction, khóa wedding khi kiểm tra trùng; lời chúc mới tạo bản riêng, không ghi đè theo IP. Thống kê người tham dự cộng cả người lớn và trẻ em. Đã bổ sung `tests/Feature/RsvpGuestTokenTest.php` nhưng chưa chạy test hoặc kiểm thử đồng thời trên MySQL; SQLite không phát `FOR UPDATE`. Chưa có sửa RSVP/lời chúc với xác thực hoặc cơ chế riêng cho nhiều khách không token dùng chung IP.
- **Tích hợp mẫu:** `tron-kiep-nay-ben-nhau` và `ngay-tinh-yeu-dom-hoa` dùng URL RSVP do controller cung cấp (gồm token khi có), gửi trường theo `SubmitRsvpRequest` và phân biệt người lớn/trẻ em; không tiếp tục hiển thị các lựa chọn khách của ai/nơi tham dự chưa được lưu. Mẫu đầu tiên cũng dùng URL lời chúc có thật, render dữ liệu từ server và chỉ thêm lời chúc sau khi gửi thành công; chữ ký chưa có endpoint lưu. Đã thêm feature test render hai mẫu và lưu số lượng, chưa chạy test hoặc trình duyệt. Các mẫu khác vẫn cần kiểm tra; chưa kết luận RSVP cá nhân hóa hoạt động toàn hệ thống.
- **Tích hợp tiếp:** `giua-muon-ngan-vi-sao` và `hanh-phuc-mang-ten-chung-minh` dùng endpoint RSVP do controller cấp, kiểm tra kết quả server, gửi số người lớn/trẻ em và hiển thị lời chúc được lưu. Mẫu giữa các vì sao đã gỡ tùy chọn riêng tư không được backend hỗ trợ. Chữ ký hai mẫu chỉ xem tạm, chưa được lưu trên server; không còn tự nạp bộ chữ ký localStorage dùng chung. Đã bổ sung test render hai mẫu và lời chúc, chưa chạy test hay browser.
- **Rollback:** hoàn nguyên code nếu phát sinh regression; đánh giá dữ liệu sai đã lưu riêng, không tự xóa hoặc sửa hàng loạt.

### STU-01 — Autosave không bỏ sót thay đổi

- **Lý do:** khi có request đang chạy, `syncToServer()` hiện có thể thoát sớm; cần chứng minh hành vi với các thay đổi phát sinh trong lúc đợi.
- **Phạm vi:** autosaver, store, service và test liên quan; không thay đổi giao thức lưu nếu chưa đối chiếu người dùng hiện tại.
- **Nghiệm thu:** thao tác mới trong request được gửi sau khi request trước kết thúc; không hiển thị saved khi còn dữ liệu chưa đồng bộ; 409 giữ bản nháp; mất mạng và khôi phục không ghi đè dữ liệu mới. Kiểm thử cả SQLite và database production tương thích khi có môi trường phù hợp.
- **Tiến độ:** `autosaver.js` theo dõi thay đổi phát sinh trong request, lưu tiếp với revision mới và chỉ báo saved khi không còn thay đổi; sau 409 hoặc phản hồi revision kế tiếp không hợp lệ giữ conflict và bản nháp tại trình duyệt. Backend yêu cầu revision khớp chính xác, kể cả khi client gửi revision lớn hơn server. Đã bổ sung test Node và feature test cho các nhánh này. Mới kiểm tra cú pháp; chưa chạy test hoặc kiểm tra browser/backend, chưa xác nhận task hoàn thành.
- **Lưu chính:** với thiệp đã có, biểu mẫu gửi revision hiện tại; service khóa dòng, từ chối revision không khớp và tăng revision sau khi ghi. ID không thuộc chủ thiệp trả 404; client chặn submit trong lúc autosave chạy và khi đã biết xung đột. Sau lỗi revision, editor hiển thị thông báo và không tự nạp đè bản nháp khác. Đã thêm feature test, chưa chạy test, Pint hoặc browser.
- **Phục hồi local:** trước khi tự nạp draft của thiệp đã lưu, client kiểm tra ID và revision; draft lỗi hoặc khác revision được giữ, khóa autosave và cho tải xuống hoặc tạo bản dự phòng trong browser trước khi mở lại bản server. Đã chạy bộ test Node; cần kiểm tra UI và thao tác nhiều tab, vì localStorage không cung cấp giao dịch nguyên tử.
- **Còn thiếu:** sau lỗi revision từ server chưa có luồng đối chiếu tự động; nhiều thiệp có thể cập nhật chung một wedding mà revision chỉ gắn theo từng thiệp. Cần kiểm thử browser và MySQL đồng thời trước khi kết luận không mất dữ liệu.
- **Chuyển mẫu:** dừng chuyển trang nếu sessionStorage không lưu/đọc lại được dữ liệu, ảnh đang dùng `blob:`, đã có xung đột hoặc autosave thiệp đang chạy/đang hẹn giờ; giữ đường dẫn ảnh `/storage/` và `/uploads/`. Chỉ tự nạp bản chuyển tiếp khi ID thiệp, revision, mẫu đích và cấu trúc wedding hợp lệ. Bản không khớp được giữ để tải xuống hoặc bỏ qua; nếu chuyển lần nữa, bản cũ được sao lưu và kiểm tra trước khi ghi đè, thất bại thì không chuyển. Bản trong session chỉ tồn tại theo tab và bản dự phòng chưa có giao diện khôi phục. Cần xác minh browser và khôi phục đầy đủ, đặc biệt config, dữ liệu lồng nhau và xử lý đồng thời giữa các tab.
- **Trường rỗng:** đã giữ trường rỗng khi thu thập dữ liệu và tạo alias chưa tồn tại; phục hồi trường văn bản rỗng vào form và đồng bộ preview. Node test hiện đạt 21/21, bao gồm thu thập và alias; chưa có kiểm thử trình duyệt cho phục hồi/preview, chưa xử lý trường hợp hai alias cùng có giá trị khác nhau.
- **Cấu hình:** khởi tạo store từ `custom_config` đã lưu; nạp object cấu hình từ bản nháp đủ điều kiện vào store và input form. Bộ test Node hiện đạt 23/23, bao gồm khởi tạo và giữ cấu hình trong request autosave; cần kiểm thử phục hồi trên trình duyệt và đồng bộ các trạng thái lựa chọn trên giao diện.
- **An toàn phục hồi:** giữ bản chuyển tiếp trong session, tạm dừng autosave và ghi local trong lúc áp dụng; chỉ đánh dấu bỏ qua bản chuyển tiếp sau khi ghi và đọc lại snapshot. Nếu phục hồi hoặc bộ nhớ thất bại, autosave dừng, form bị khóa và người dùng có thể tải bản đã đọc xuống hoặc thử lại. Bộ test Node hiện đạt 28/28; chưa kiểm thử giao diện trình duyệt hoặc xác minh snapshot đầy đủ với mọi dữ liệu lồng nhau.
- **Rollback:** hoàn nguyên mã autosave; không xóa local draft hoặc dữ liệu server.

### PUB-01 — Quy định nháp, công khai và hết hạn

- **Lý do:** luồng tạo hiện đặt thiệp mới là đã xuất bản; autosave có thể cập nhật `weddings`, còn route công khai đã đọc không kiểm tra trạng thái xuất bản hoặc hết hạn.
- **Phụ thuộc:** STU-01, quyết định nghiệp vụ về thời điểm công khai, quyền xem của chủ thiệp, thời hạn gói và tương thích thiệp đã có; kiểm tra mọi endpoint RSVP/lời chúc cùng route hiển thị.
- **Phạm vi dự kiến:** `InvitationEditorController`, `InvitationTemplateService`, model/Blade liên quan và feature tests. Chỉ đề xuất migration khi thật sự cần snapshot hoặc trạng thái mới, sau khi có phương án dữ liệu và rollback.
- **Nghiệm thu:** người khác không xem/ghi thiệp chưa được phép công khai theo chính sách; chủ thiệp vẫn preview an toàn; thay đổi nháp không làm đổi nội dung public nếu đã chọn mô hình snapshot; thiệp đã phát hành không mất link hoặc dữ liệu; kiểm tra expiry và tất cả endpoint tương tác liên quan.
- **Rủi ro/rollback:** áp điều kiện mới lên dữ liệu cũ có thể khóa thiệp khách. Cần phân tích bản ghi hiện hữu và thử migration trên bản sao an toàn trước khi phát hành; không tự sửa hàng loạt hoặc xóa dữ liệu.

## Quyết định còn phải hoàn thiện

- Dữ liệu guest/session, quyền cộng tác và giới hạn truy cập dữ liệu nhạy cảm; chuyển đổi nếu sau này có workspace.
- Dữ liệu wedding chia sẻ và khác biệt từng thiệp/sự kiện; chính sách snapshot và sửa thiệp đã công khai.
- Chính sách RSVP khi nhiều người chung IP, deadline, chỉnh sửa và lịch sử; không thay đổi dữ liệu cũ thiếu migration plan.
- Hợp đồng provider, xử lý tiền chuyển thừa/thiếu, webhook trùng và khôi phục trạng thái giao dịch.
- Chỉ chọn object storage, Redis, queue, versioning hay RBAC mở rộng khi nhu cầu, chi phí và đường chuyển đổi đã được xác minh.
