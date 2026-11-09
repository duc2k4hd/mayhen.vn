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
- **Test suite**: **620/620 tests PASSED** (3,164 assertions).
- **Code style**: **Laravel Pint PASSED**.
