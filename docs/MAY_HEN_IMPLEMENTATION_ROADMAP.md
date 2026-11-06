# Mây Hẹn — roadmap triển khai từ hiện trạng

Ngày lập: 06/11/2026. Đây là roadmap ban đầu dựa trên kiểm toán tĩnh từng phần trong `MAY_HEN_PROJECT_AUDIT.md`; cập nhật sau khi khảo sát đầy đủ, xác minh độc lập các rủi ro và chạy test. Không đánh dấu hoàn tất chỉ vì đã có route, giao diện hoặc tài liệu.

## Thứ tự và checklist

| ID | Ưu tiên | Công việc | Phụ thuộc | Trạng thái |
|---|---|---|---|---|
| DISC-01 | P0 | Hoàn tất bản đồ các module, database, PRD và kiểm toán bảo mật độc lập; lập baseline test an toàn | Không | Đang thực hiện |
| SEC-01 | P0 | Ràng buộc quyền xem/thêm/xóa khách với chủ wedding và session tạo thiệp | DISC-01; xác minh quyền hiện tại | Chưa bắt đầu |
| PAY-01 | P0 | Đóng xác thực webhook, kiểm tra quyền gắn đơn và giao dịch lặp | DISC-01; hợp đồng SePay, cấu hình test an toàn | Chưa bắt đầu |
| RSVP-01 | P0 | Ràng buộc token với đúng wedding; kiểm thử quyền, response và IP dùng chung | SEC-01; quyết định tương thích RSVP | Chưa bắt đầu |
| STU-01 | P0 | Bảo đảm autosave đồng bộ thay đổi mới, revision và trạng thái lưu đúng | DISC-01; test tái hiện có kiểm soát | Chưa bắt đầu |
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
- **Rủi ro/rollback:** chỉ đọc; không có thay đổi database. Giữ mọi thay đổi sẵn có trong working tree.

### SEC-01 — Quyền quản lý khách theo chủ sở hữu

- **Lý do:** action quản lý khách hiện truy vấn theo ID mà chưa thấy gọi kiểm tra `Invitation::isOwner()`.
- **Phạm vi dự kiến:** `GuestController`, route/request liên quan và feature tests trong `tests/`; không cần migration dự kiến. Giữ hỗ trợ thiệp khách tạo bằng session.
- **Quy tắc:** chủ thiệp hợp lệ được xem/thêm/xóa khách của wedding mình; tài khoản khác, session khác và khách không có quyền bị từ chối; không tiết lộ dữ liệu hoặc sửa nhầm wedding. Xác định cách xử lý khi wedding có nhiều thiệp trước khi dùng một thiệp bất kỳ để kiểm tra.
- **Kiểm thử/nghiệm thu:** kiểm tra GET/POST/DELETE bằng hai chủ sở hữu, khách vô danh có/không session hợp lệ; kiểm tra không có tác dụng phụ với dữ liệu người khác; kiểm tra CSRF, validation và liên kết quản lý khách hiện tại.
- **Rủi ro/rollback:** chọn sai cơ chế quyền có thể khóa khách tạo thiệp ẩn danh; hoàn nguyên thay đổi controller/test, không thay đổi dữ liệu.

### PAY-01 — Xác thực và đối soát thanh toán

- **Lý do:** mã xử lý hiện có nhánh tiếp tục khi thiếu header xác thực; quyền sở hữu thiệp ở request tạo đơn chưa được chứng minh.
- **Phụ thuộc:** tài liệu giao thức SePay hiện hành, cấu hình kiểm thử không có secret thật, phân tích đường đi CSRF/middleware và xử lý concurrent.
- **Phạm vi dự kiến:** checkout controller, request, cấu hình provider và test; schema chỉ thay đổi khi chứng minh cần lưu transaction/event để chống lặp, kèm migration tương thích và phương án rollback.
- **Quy tắc/nghiệm thu:** thiếu/sai secret bị từ chối; chỉ chấp nhận format xác thực đúng provider; đối chiếu đơn, số tiền, loại giao dịch và ID giao dịch; retries/concurrency không cấp quyền hai lần; đơn không được gắn thiệp người khác; lỗi không lộ secret. Test dùng dữ liệu giả, không gọi gateway thật.
- **Rủi ro/rollback:** thay đổi xác thực có thể ngắt callback đang vận hành; triển khai sau khi kiểm tra cấu hình thật, có giám sát và phương án xử lý giao dịch chờ, không tùy tiện sửa các đơn lịch sử.

### RSVP-01 — Token và phạm vi phản hồi

- **Lý do:** truy vấn token khách trong RSVP hiện không lọc theo wedding; quy tắc chặn bằng IP có thể ảnh hưởng khách cùng mạng.
- **Phạm vi:** `GuestController`, request và feature tests; giữ nguyên hành vi cũ cho đến khi chính sách thay đổi phản hồi được thống nhất.
- **Nghiệm thu:** token của wedding A không liên kết response của wedding B; token sai không gây lộ dữ liệu; khách hợp lệ và phản hồi không token tuân thủ chính sách; kiểm tra số người lớn/trẻ em và cạnh tranh gửi.
- **Rollback:** hoàn nguyên code nếu phát sinh regression; đánh giá dữ liệu sai đã lưu riêng, không tự xóa hoặc sửa hàng loạt.

### STU-01 — Autosave không bỏ sót thay đổi

- **Lý do:** khi có request đang chạy, `syncToServer()` hiện có thể thoát sớm; cần chứng minh hành vi với các thay đổi phát sinh trong lúc đợi.
- **Phạm vi:** autosaver, store, service và test liên quan; không thay đổi giao thức lưu nếu chưa đối chiếu người dùng hiện tại.
- **Nghiệm thu:** thao tác mới trong request được gửi sau khi request trước kết thúc; không hiển thị saved khi còn dữ liệu chưa đồng bộ; 409 giữ bản nháp; mất mạng và khôi phục không ghi đè dữ liệu mới. Kiểm thử cả SQLite và database production tương thích khi có môi trường phù hợp.
- **Rollback:** hoàn nguyên mã autosave; không xóa local draft hoặc dữ liệu server.

## Quyết định còn phải hoàn thiện

- Dữ liệu guest/session, quyền cộng tác và giới hạn truy cập dữ liệu nhạy cảm; chuyển đổi nếu sau này có workspace.
- Dữ liệu wedding chia sẻ và khác biệt từng thiệp/sự kiện; chính sách snapshot và sửa thiệp đã công khai.
- Chính sách RSVP khi nhiều người chung IP, deadline, chỉnh sửa và lịch sử; không thay đổi dữ liệu cũ thiếu migration plan.
- Hợp đồng provider, xử lý tiền chuyển thừa/thiếu, webhook trùng và khôi phục trạng thái giao dịch.
- Chỉ chọn object storage, Redis, queue, versioning hay RBAC mở rộng khi nhu cầu, chi phí và đường chuyển đổi đã được xác minh.
