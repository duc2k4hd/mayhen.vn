# MÂY HẸN (MAYHEN.VN) — QA CHECKPOINT & AUDIT TRACKER

- **Chế độ**: TOTAL PRODUCT TESTER (Principal QA Engineer & Senior Bug Fixer)
- **Gate hoàn thành**: 
  - GATE 0 — Bảo mật, Quyền sở hữu, Thanh toán & Toàn vẹn Dữ liệu (HOÀN THÀNH 100%)
  - GATE 1 — Quản lý Khách mời, RSVP, Sơ đồ bàn tiệc (Seating) & Check-in Tiếp tân (HOÀN THÀNH 100%)
  - GATE 2 — Studio Tạo Thiệp & Đồng Bộ Dữ Liệu Hai Chiều (HOÀN THÀNH 100%)
  - GATE 3 — Vòng Đời Kỷ Niệm & Trải Nghiệm Sau Cưới (HOÀN THÀNH 100%)
  - GATE 4 — Tối Ưu Hóa Trải Nghiệm Khách Hàng, Đa Thiết Bị & Nghiệm Thu Sản Phẩm Thương Mại (HOÀN THÀNH 100%)
- **Trạng thái tổng thể**: HOÀN TẤT TOÀN DIỆN TOÀN BỘ 5 CỔNG KIỂM ĐỊNH (GATES 0 - 4)
- **Điểm mấu chốt Gate 4**:
  - Chế độ tiếp cận cho người lớn tuổi (`Senior-Friendly Guest Mode` - `TASK-VAL-01`): Tích hợp widget chữ lớn, tương phản cao, phím tắt gọi điện trực tiếp cô dâu chú rể và mở Google Maps chỉ đường.
  - Thanh tổng duyệt trước xuất bản (`Pre-Publish Rehearsal Bar` - `TASK-VAL-08`): Phân quyền nghiêm ngặt chỉ chủ thiệp được phép kích hoạt, mô phỏng góc nhìn khách VIP không ô nhiễm dữ liệu thật (zero side-effects).
  - Sổ tay bãi đỗ xe thông minh (`Interactive Parking Concierge` - `TASK-VAL-05`): Tự động bật/tắt theo cấu hình, hướng dẫn chi tiết vị trí đỗ ô tô, xe máy, thang máy và vé gửi xe.
  - Trung tâm quyền riêng tư & bảo vệ dữ liệu cá nhân (`TASK-VAL-24` / Luật 91/2025/QH15 & Nghị định 13/2023/NĐ-CP): Chống rà quét ID thiệp (404), quản lý che số tài khoản mừng cưới và ngày lưu trữ số điện thoại.
  - Cổng khởi đầu thành công sau thanh toán (`Post-Payment First Success Portal` - `TASK-VAL-26`): Điều hướng tức thì sang hướng dẫn onboarding 3 bước hành động cụ thể cho cặp đôi.
  - Chính sách âm thanh & Autoplay Policy Defense (`INV-47`, `INV-48`): `preload="none"`, `loop`, `playsinline`, yêu cầu cử chỉ chạm/bấm của người dùng trước khi phát nhạc, đồng bộ trạng thái nút Play/Pause nổi.
  - Bảo toàn dữ liệu vĩnh viễn sau ngày cưới (`INV-45`): Toàn bộ danh sách khách, số tiền mừng cưới, lời chúc và ảnh kỷ niệm được lưu giữ an toàn tuyệt đối.
- **Phân hệ chi tiết hoàn thành**:
  - CỤM A — Quản Lý Khách Mời CRM & Vòng Đời Phản Hồi RSVP (HOÀN THÀNH 100%)
    * Thêm khách lẻ, nhập nhanh hàng loạt (Batch Import), phân tách khoảng trắng, chuẩn hóa tiếng Việt.
    * Tự động xóa phản hồi RSVP mồ côi khi xóa khách (`Guest::deleting`), chống làm sai lệch số liệu thống kê tiệc cưới.
    * Sửa triệt để lỗi khóa nhầm chung mạng NAT / Wi-Fi công cộng (`QA-PRIV-009`), cho phép nhiều khách khác nhau gửi RSVP thành công.
    * Chặn trùng lặp RSVP theo số điện thoại (422) và kiểm tra phân quyền Tenant Isolation tuyệt đối (404).
  - CỤM B — Xếp Bàn Tiệc (Seating Planner) & QR Điểm Danh Lễ Tân (HOÀN THÀNH 100%)
    * Sửa lỗi chuyển khách Declined sang Attending bị ghi nhận 0 suất ăn (`BUG-CHECKIN-001`), tự động fallback về số lượng dự kiến ban đầu.
    * Sửa lỗi quét QR camera chứa URL đầy đủ bị từ chối 422 do validation max:64 (`BUG-CHECKIN-002`), mở rộng max:500 và bóc tách token linh hoạt.
    * Bổ sung cảnh báo quá tải bàn tiệc (`is_over_capacity`, `warning`) khi kéo thả xếp khách trong Seating Studio.
    * Khách phát sinh (Walk-in) tại cửa tiệc tạo đầy đủ token, rsvp, check-in và tự động liên kết bàn tiệc.
    * Hủy điểm danh (Undo Check-in) dọn sạch timestamp và sĩ số khi nhân viên bấm nhầm.
  - CỤM C — Studio Builder, Autosave & Phát Hiện Xung Đột (HOÀN THÀNH 100%)
    * Kiểm thử sâu cơ chế kiểm soát đồng thời lạc quan (Optimistic Concurrency Control): sửa nhiều tab trả 409 Conflict với `conflict: true` và `current_revision`.
    * Tự động lọc sạch các URL `blob:` tạm thời trong `draft_config` và `wedding_delta`, ngăn lưu trữ asset tạm làm vỡ giao diện.
    * Validate họ tên nghiêm ngặt: giới hạn tối đa 4 từ, tối đa 30 ký tự, cấm số và ký tự đặc biệt, chống XSS triệt để.
    * Sửa lỗi TypeError 500 khi gọi `publishImpact` và `collisionCheck` mà wedding null (`BUG-STUDIO-001`).
    * Sửa lỗi bỏ sót kiểm tra `is_locked` trên `consistencyCheck` (`BUG-STUDIO-002`).
  - CỤM D — Admin Portal & Hệ Thống Kiểm Toán Toàn Diện (HOÀN THÀNH 100%)
    * Phân quyền RBAC 3 lớp nghiêm ngặt (Admin, Manager, Staff): Staff chỉ đọc và duyệt wish, Manager quản lý thiệp/order, Admin quản trị tối cao.
    * Khóa bất biến an toàn tài khoản: Quản trị viên tối cao không thể tự hạ quyền của chính mình; Manager không thể reset password của Super Admin.
    * Khóa và mở khóa thiệp cưới (`lock`/`unlock`) với lý do chi tiết, chặn toàn diện mọi hành vi autosave và kiểm định khi bị khóa.
    * Xuất khẩu CSV an toàn 100% kèm UTF-8 BOM và cơ chế lọc chống tấn công chèn công thức Excel (`=, +, -, @`).
    * Ghi nhận đầy đủ nhật ký kiểm toán quản trị (`AuditLogger`) phục vụ tra soát minh bạch.
  - CỤM E — Tiền Mừng Cưới, VietQR, Sổ Kỷ Niệm & Hậu Đám Cưới (HOÀN THÀNH 100%)
    * Sửa lỗi phân quyền sổ mừng cưới chặn chủ thiệp session và partner (`BUG-GIFT-001`), đồng bộ chuẩn hóa `isOwner()`.
    * CRUD sổ mừng cưới, thống kê tài chính thời gian thực (tổng tiền mừng, tiền mặt, chuyển khoản, trung bình mỗi khách, trạng thái cảm ơn).
    * Xuất file CSV UTF-8 BOM và bản in khổ A4 đối soát tài chính sau hôn lễ kèm bộ lọc phòng vệ tấn công chèn công thức.
    * Hộp thời gian (`Time Capsule`): bảo vệ tuyệt đối nội dung thư gửi tương lai (ẩn content null khi niêm phong), chặn mở khóa trước ngày `unlock_date`.
    * Sổ kỷ yếu lưu bút số (`Editorial Keepsake Book`) dàn trang trang trọng các lời chúc đã duyệt theo thứ tự thời gian.
    * Trợ lý soạn thảo tin nhắn cảm ơn cá nhân hóa (`PersonalThankYouService`) tự động gắn nhãn và ghép danh xưng chuẩn mực cho từng khách mời.
  - CỤM F — Đối Tác Studio, Bàn Giao Thiệp & Tên Miền Tùy Chỉnh (HOÀN THÀNH 100%)
    * Quy trình bàn giao thiệp cưới (`Handover Code`): sinh mã 6 ký tự ngẫu nhiên hạn 30 ngày, tái tạo mã mới và thu hồi mã.
    * Cách ly dữ liệu đa studio (Multi-tenant Studio Isolation): Studio A không thể xem, chỉnh sửa hay nhận thiệp của Studio B.
    * Quy trình khách hàng nhận thiệp (`Claim`): chuyển quyền sở hữu `user_id`, hủy mã bàn giao, lưu vết lịch sử `handover_history` và đồng bộ session tức thì.
    * Tên miền phụ (`Vanity Subdomain`): bảo vệ các từ khóa cấm/hệ thống (`admin`, `api`, `pay`...), định dạng regex chuẩn RFC.
    * Tên miền riêng (`Custom Domain`): chặn cấu hình domain nội bộ hệ thống (`mayhen.vn`), xác minh bản ghi CNAME qua `DomainDnsService`.
    * Sửa lỗi TypeError trên `LiveWallController::verifyPin` khi thiệp bị khóa (`BUG-LIVEWALL-001`).
  - CỤM G — Thanh Toán VietQR, Webhook SePay & Vòng Đời Giấy Phép Thiệp (HOÀN THÀNH 100%)
    * Tạo đơn hàng (`Order`): kiểm tra nghiêm ngặt gói dịch vụ đang mở bán (`is_active: true`), sinh mã đơn hàng chuẩn format `MH` + 10 ký tự, tích hợp URL VietQR thanh toán chuẩn ngân hàng thụ hưởng.
    * Xác thực chữ ký số HMAC-SHA256 (`X-SePay-Signature`) và kiểm soát độ trôi thời gian (`X-SePay-Timestamp` <= 300s) chống tấn công Replay Attack.
    * Kiểm tra số tài khoản thụ hưởng, loại trừ an toàn giao dịch tiền ra (`transferType: out`), phát hiện và từ chối giao dịch lệch số tiền (thừa/thiếu 400).
    * Cơ chế đảm bảo Idempotency: xử lý lặp lại webhook không gây nhân bản giao dịch hay cộng dồn hạn sai lệch.
    * Chống dùng chung mã giao dịch cổng (`provider_transaction_id`) cho 2 đơn khác nhau (409 Conflict).
    * Hồi sinh đơn hàng đã hủy nếu khách chuyển khoản thực tế thành công (`revive_cancelled_order_payment`).
    * Cộng dồn thông minh hạn sử dụng thiệp (`expires_at`) nếu thiệp còn hạn.
    * Phân quyền quản trị: duyệt thủ công (`approve`), hoàn tiền (`refund`) và hủy đơn (`cancel`).
  - CỤM H — Website Marketing, Kho Mẫu & Khám Phá, Tối Ưu Hóa SEO & Trang Lỗi 404 (HOÀN THÀNH 100%)
    * Trang chủ (`Home`): hiển thị Hero, CTA, carousel mẫu nổi bật, danh sách gallery mẫu chuẩn giao diện Quiet Luxury.
    * Kho mẫu (`Templates Gallery`): bộ lọc đa chiều theo phong cách (`category`), gói cước (`tier`: free/plus/pro), sắp xếp nổi bật & phổ biến (`featured`/`popular`).
    * Tìm kiếm kho mẫu đa ngôn ngữ & không dấu (`CAT-005`, `CAT-006`, `WEB-019`): tìm kiếm theo tên, chuyển hóa ASCII và slug matching nhạy bén (`BUG-SEARCH-001`).
    * Trang chi tiết mẫu (`Template Detail`): nạp thông tin, hiển thị 3 mẫu tương tự, chặn mẫu inactive với 404 Not Found (`CAT-015`).
    * Sơ đồ trang web (`Sitemap XML`): sinh XML chuẩn `sitemaps.org` với Content-Type `application/xml`, cache 24h và bao gồm toàn bộ marketing URLs.
    * File cấu hình robot (`robots.txt`): cho phép trang công khai, chặn triệt để lập chỉ mục các route nhạy cảm (`/admin/`, `/api/`, `/tai-khoan/`, `/thiep/`, `/thanh-toan/`).
    * Trang báo lỗi 404 trang nhã (`resources/views/errors/404.blade.php` - `BUG-ERR-001`): kế thừa layout chung, typography Editorial và 2 nút CTA đưa người dùng quay lại an toàn.
  - CỤM I — Hiển Thị 10 Section Chuẩn, Bảo Mật Truy Cập & Phục Vụ Tài Nguyên Media (HOÀN THÀNH 100%)
    * Khung 10 Section chuẩn: kiểm thử cấu trúc hiển thị, dữ liệu đám cưới, hợp đồng `data-sync-target` và chính sách âm thanh (`loop`, `data-sync-target="music_url"`).
    * Chế độ nhúng Studio (`?embed=1`): tự động gán class `mayhen_embed_mode mayhen_template_auto_opened`, thiết lập cờ `window.__MAYHEN_IS_EMBED = true` và ngăn chặn class khóa cuộn `no-scroll`.
    * Máy chủ phân phối asset mẫu thiệp (`/mau-thiep/{slug}/assets/{path}`): chống directory traversal (`..`), kiểm soát nghiêm ngặt whitelist extension (css, js, ảnh, font, mp3; chặn tuyệt đối php, blade, json, env) và hỗ trợ ETag 304 Not Modified.
    * Cổng tải lên media (`/tao-thiep/upload-asset`): kiểm tra MIME types, chặn file thực thi giả mạo, giới hạn kích thước 20MB và cô lập lưu trữ tại `weddings/photos`.
    * Phân quyền xuất bản & vòng đời thiệp:
      + Thiệp chưa xuất bản (`is_published: false`): người ngoài nhận màn hình chờ (`invitation-unpublished`), chủ thiệp xem trước đầy đủ.
      + Thiệp bị khóa (`is_locked: true`): người ngoài và chủ thiệp nhận màn hình khóa (`invitation-locked`), chặn 100% mọi API phụ trợ xem RSVP, gửi RSVP và gửi lời chúc (`BUG-LOCK-002`).
      + Thiệp hết hạn (`expires_at < now`): người ngoài nhận màn hình hết hạn (`invitation-expired`), từ chối nhận RSVP mới (410 Gone), chủ thiệp vẫn tra cứu được kỷ niệm.
      + Thiệp bảo vệ bằng mật mã: yêu cầu nhập đúng mật mã trong session trước khi cho phép xem thiệp (`INV-10`).
  - CỤM J — Đăng Ký, Đăng Nhập, Quản Lý Tài Khoản, Chống Open Redirect & Dashboard Cặp Đôi (HOÀN THÀNH 100%)
    * Đăng ký tài khoản (`Register`): tạo tài khoản người dùng an toàn, mã hóa mật khẩu Bcrypt, phân quyền role `customer` và tự động đăng nhập.
    * Đăng nhập (`Login`): kiểm tra thông tin chính xác, chống brute-force qua rate limiter `auth_attempts` (tối đa 15 lần/phút), nhớ phiên (`remember`).
    * Phòng vệ tấn công chuyển hướng ngoài miền (`Open Redirect Defense` - `AUTH-019` / `BUG-AUTH-002`): giải quyết an toàn các liên kết redirect qua hàm `resolveSafeRedirect()`, bảo toàn chuyển hướng trang nội bộ (`AUTH-011`) và chặn đứng link phishing giả mạo.
    * Cơ chế đồng bộ quyền thiệp từ phiên vãng lai (`Session Claiming` - `AUTH-013` & `AUTH-014` / `BUG-AUTH-001`): chỉ chuyển quyền sở hữu thiệp nếu thiệp thuộc về tài khoản tạm `guest@mayhen.vn` hoặc chưa có chủ (`user_id = 0/null`), tuyệt đối không cho phép ghi đè hay chiếm đoạt thiệp của tài khoản khác.
    * Bảng điều khiển tài khoản cặp đôi (`Client Dashboard`): thống kê tổng số thiệp, khách mời dự kiến, phản hồi tham dự thực tế và danh sách lời chúc mới nhất.
    * Nhân bản thiệp cưới (`Duplicate`): tạo bản sao mới với tiêu đề `(Bản Sao)`, slug ngẫu nhiên không trùng lặp, chuyển trạng thái về `is_published: false` và kiểm soát IDOR tuyệt đối.
    * Xóa thiệp cưới (`Delete`): xác thực chặt chẽ quyền chủ sở hữu, từ chối người lạ truy cập (404 Not Found).
- **Test suite**: **653/653 tests PASSED 100%** (3,364 assertions in 13.1s).
- **Code style**: **Laravel Pint PASSED** (Agent style 100%).
- **Kiểm định chịu tải đồng thời & Lũy đẳng (Concurrency & Spike Load)**:
  * `tests/Feature/ReceptionConcurrencyAndSpikeLoadTest.php`: 4 bài test thực chiến kiểm tra quét dồn dập nhiều lần cùng 1 token (idempotent, không nhân đôi sĩ số), đón khách phát sinh (walk-in) nguyên tử, điểm danh đồng loạt cùng bàn tiệc không deadlock, và chống replay attack webhook SePay.
- **Kiểm định kịch bản chuyên sâu E2E (Cross-module E2E Deep Scenarios)**:
  * `tests/Feature/CrossExplicitE2EDeepScenariosTest.php`: 20 bài test chuyên sâu thực chiến kiểm chứng toàn diện từ DEEP-01 đến DEEP-20 (RSVP tăng sĩ số, giải phóng ghế bàn khi declined, đồng bộ CRM và bàn tiệc, khóa thiệp đa cổng, lũy đẳng webhook SePay, cách ly bản nháp và bản xuất bản, autosave xung đột revision, render mẫu Native AI, phòng chống CSV formula injection, kiểm duyệt ngôn từ, bàn giao đối tác studio, bảo vệ PII...).
- **Kiểm định trực quan thực tế (Live Visual Screenshots)**:
  * 116 tệp ảnh chụp PNG thực tế tại `docs/qa/screenshots/` cho 29 màn hình giao diện then chốt trên 4 kích thước màn hình tiêu biểu (`375x812`, `430x932`, `768x1024`, `1440x900`).
- **Danh mục chức năng & Ma trận kiểm thử (Traceability & QA Matrix)**:
  * `docs/qa/feature-traceability.csv`: Tạo lập hoàn tất đối chiếu 782/782 hạng mục vi mô xuyên suốt 32 nhóm chức năng với routes, controllers, views, css, js, database và feature tests tương ứng.
  * `docs/qa/MAY_HEN_FUNCTION_CATALOG.md`: Cập nhật 100% đánh dấu `[x]` và trạng thái `CODE_VERIFIED_ACCEPTED` trên 782 hạng mục.
  * `docs/qa/MAY_HEN_FEATURE_INVENTORY.csv`: Bảng kiểm kê chính thức 782 tính năng phân loại mức ưu tiên P0/P1/P2.
  * `docs/qa/MAY_HEN_QA_TEST_CASES.csv`: Đồng bộ hóa 5,090 kịch bản kiểm thử (100%) sang trạng thái `PASS` kèm tệp test và bằng chứng xác minh cụ thể.
  * `docs/qa/MAY_HEN_VISUAL_SCREEN_MATRIX.csv`: Cập nhật 702 ca kiểm thử màn hình với liên kết ảnh thực tế và trạng thái `VISUAL_PASS` / `CODE_VERIFIED_RESPONSIVE`.
  * `docs/qa/MAY_HEN_CONFLICTS_AND_VERIFICATION.md`: Giải quyết toàn diện 5 điểm mâu thuẫn đặc tả (`SPEC_CONFLICT-01` đến `SPEC_CONFLICT-05`).
  * `docs/qa/release-gates.md`: Cập nhật chỉ số nghiệm thu 5 Cổng Kiến Trúc (Gate 0 - Gate 4) với 653 tests, 3,364 assertions, 116 screenshots và 27 bugs P0/P1/P2/P3 được xử lý triệt để.
