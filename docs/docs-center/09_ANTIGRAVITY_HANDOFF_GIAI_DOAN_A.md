# Bàn giao Antigravity — Docs Mây Hẹn

Ngày khảo sát: 2026-11-15. Đây là nhiệm vụ sẵn sàng giao, không phải báo cáo Antigravity đã thực hiện. Giai đoạn A chỉ khảo sát; chưa được sửa mã ứng dụng. Không cài đặt, deploy, push hay commit; không đọc bí mật, thao tác production hoặc dùng dữ liệu thật. Trước khi làm đọc `context.gemini.md`, `AGENTS.md`, các skill phù hợp và bộ tài liệu `docs/docs-center/01`–`08`. Kiểm tra `.ai/rules` nếu có. Tuân thủ chỉnh sửa minh bạch của repository khi được phép sửa ở giai đoạn sau.

## Nguồn theo dõi 108 bài

Ma trận gốc `02_DOCS_EDITORIAL_COVERAGE_MATRIX.csv` chứa 108 ID (58 Client, 50 Admin); tại thời điểm khảo sát có một tệp Markdown cho mỗi ID trong `resources/docs/`. Cột `Implementation_Status` của ma trận cũ không phải kết quả nghiệm thu. Phải tạo bảng theo dõi mới cho từng ID: ID, tiêu đề, nhóm, URL thực tế, tệp bài, nguồn mã và dòng, tình trạng nội dung, ảnh cần chụp, vai trò được xác minh, test cần chạy, trạng thái PASS/FAIL/BLOCKED/NOT_TESTED, bằng chứng và vấn đề. Giá trị chưa kiểm chứng để NOT_TESTED, không suy đoán. Xác nhận sitemap 100/108 bằng so sánh ID, không giả định.

## Cổng chung cho từng nhiệm vụ

Đối chiếu route/middleware, controller, service/model, Blade/JS và test thực; ghi tên nút và điều kiện thực tế. Kiểm tra nội dung đang xuất bản, nêu mâu thuẫn cụ thể; tách bằng chứng mã khỏi bằng chứng chạy thật. Không sửa chức năng ngoài Docs. Với dữ liệu và chức năng nhạy cảm, chỉ thử trên môi trường cô lập có tài khoản và dữ liệu giả được xác nhận; không thực hiện giao dịch thật, gửi tin hay thay đổi dữ liệu thật. Nếu không có môi trường đó, đánh BLOCKED cho bước tương ứng. Khi được phê duyệt giai đoạn B, sửa từng lô nhỏ, cập nhật context.gemini.md, chạy kiểm thử phù hợp và báo diff, lệnh, kết quả; không nhận PASS nếu chưa đủ bằng chứng.

## Sáu phiếu thí điểm

1. **CLI-START-002 — đăng ký/onboarding**: Đối chiếu route đăng ký và onboarding, form, điều kiện đăng nhập, từng bước và nhãn thực; ảnh màn đăng ký và từng bước có giá trị. Xác nhận môi trường thử và cơ chế email trước khi gửi hay tạo tài khoản. Vai trò: khách mới/tài khoản thử; kiểm thử đăng ký, xác thực và onboarding tương ứng. Không xác nhận nội dung hiện tại khi chưa chạy.
2. **CLI-EDIT-002 — đổi ảnh/tiêu điểm**: Đối chiếu route Studio thực, quyền sở hữu, UI chọn/tải ảnh và focal point, giới hạn file và lưu; ảnh màn editor, chọn ảnh và kết quả. Chỉ dùng thiệp và ảnh giả; kiểm thử upload, phân quyền, lưu, responsive. Không khẳng định giới hạn 10 MB hoặc lời hứa không cắt ảnh khi chưa xác minh.
3. **CLI-GUEST-004 — RSVP**: Đối chiếu form thiệp công khai, xác nhận đa sự kiện và dữ liệu bữa ăn trong controller, model và UI; ảnh thiệp mẫu, form, trạng thái thành công. Chỉ dùng thiệp/khách giả; kiểm thử RSVP, bảo mật link và trạng thái đa sự kiện. Không giả định vai trò khách phải đăng nhập.
4. **ADM-USER-001 — hồ sơ khách hàng**: Xác minh RBAC thực, UI Customer 360, PII masking và audit logging cho hành động đọc; chụp màn hình dữ liệu giả không nhạy cảm. Kiểm thử allow/deny và audit theo chính sách thật. Vai trò cụ thể chưa xác minh; không khẳng định mọi lượt xem được audit.
5. **ADM-ORDR-002 — đối soát/kích hoạt**: Đối chiếu UI, trạng thái đơn, cơ chế kích hoạt, quyền, nhật ký và điều kiện; kiểm tra rõ từng trường thực sự tồn tại. Chỉ dùng DB và đơn giả trên môi trường tách biệt, không gọi ngân hàng/SePay hay kích hoạt thật; nếu không có môi trường, BLOCKED. Ảnh chỉ từ dữ liệu giả; test quyền, idempotency và audit nếu tính năng có thật. Không tự sửa logic thanh toán.
6. **ADM-COCK-002 — sự cố offline**: Kiểm tra route cockpit thực, client check-in, khả năng offline, lưu cục bộ và đồng bộ, điều kiện xung đột và audit; đối chiếu khẳng định đang viết. Mô phỏng mất mạng chỉ trên môi trường an toàn, dữ liệu giả, không gửi thông báo thật. Ảnh trạng thái kết nối và kết quả đồng bộ nếu xác minh được; test luồng offline/quyền tương ứng. Chưa chứng minh tính năng thì BLOCKED/FAIL theo bằng chứng, không bịa SOP.

## Ảnh và nghiệm thu

Client: đề xuất assets công khai theo cấu trúc Docs hiện có, tên theo ID/bước, WebP khi tương thích, kiểm tra màn hình desktop/mobile và nội dung ảnh; không lưu thông tin thật. Admin: không đặt ảnh nhạy cảm vào `public/`; dùng lưu trữ riêng ngoài public và route có middleware/authorization tương ứng nếu ảnh cần hạn chế truy cập; trước tiên kiểm tra cơ chế hiện có. Mỗi ảnh cần nguồn màn hình, role, dữ liệu giả, ngày, đường dẫn, người duyệt, kích thước và chứng cứ tải thành công. Xác minh trên trang thật, zoom/phím, mobile, light/dark nếu hỗ trợ. Chạy test Docs và test liên quan trong môi trường an toàn; lưu kết quả mới, kiểm tra leak client/admin, 404, tìm kiếm, XSS, console. Mỗi bài phải có chứng cứ riêng; bước thiếu ghi BLOCKED hoặc NOT_TESTED, tuyệt đối không kế thừa PASS cũ.

## Rủi ro ưu tiên P0 — cần xác minh và xử lý trong phạm vi Docs

- `app/Http/Controllers/Admin/AdminDocsController.php`: `show()` kiểm tra vai trò, nhưng `index()` và `search()` lấy dữ liệu tất cả bài admin; sidebar cũng nhận toàn bộ categories. Nguy cơ lộ tiêu đề, metadata hoặc kết quả tìm kiếm cho vai trò không được đọc. Quy tắc `default => true` cho role không nhận diện là fail-open. Đây là kết luận đọc mã, chưa kiểm thử response theo từng vai trò. Bổ sung kiểm thử cách ly danh mục, sidebar, search và bài chi tiết; chỉ sửa sau khi có bằng chứng và trong phạm vi Docs.
- `app/Services/DocumentationService.php:410–431`: escape `ENT_NOQUOTES` rồi đưa URL Markdown vào `href` mà chưa thấy whitelist scheme; nội dung render được xuất dưới dạng HTML. Nguy cơ XSS tùy quyền sửa Markdown, cần kiểm thử các URL và dấu nháy an toàn, không sử dụng dữ liệu thật.
- Service cache danh mục 3.600 giây; xác minh invalidation sau sửa nội dung và quyền. Không suy đoán trạng thái cache.

## Ghi chú điều phối

Antigravity IDE có CLI `antigravity-ide.cmd chat` nhưng chưa chứng minh được OpenClaw có thể nhận kết quả từ phiên chat hoặc agent đã thực thi. Chỉ giao qua tích hợp được xác minh; nếu không, chủ dự án có thể đưa trực tiếp tệp này vào Antigravity. Cần xác minh quyền môi trường local và tài khoản thử trước khi tương tác với dữ liệu. `composer.lock` ghi Laravel 13.35.0 trong khi context ghi Laravel 12; xác minh phiên bản cài thực tế trước dùng API.
