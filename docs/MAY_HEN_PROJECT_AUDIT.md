# Mây Hẹn — kiểm toán dự án bước đầu

Ngày đối chiếu: 06/11/2026. Commit tham chiếu `49ae6c38527e51b1bc7b741585861235ccecef81`; working tree có thay đổi, bao gồm PRD do người dùng cung cấp. Đây là kiểm toán tĩnh bước đầu, chưa phải kiểm toán toàn bộ hoặc chứng nhận hệ thống an toàn. Chưa kiểm tra giao diện bằng trình duyệt, trạng thái database thực tế, tích hợp nhà cung cấp hay chạy test. Không suy ra tính năng đã hoàn chỉnh chỉ từ file hoặc route.

## 1. Nền tảng thực tế

`composer.json` yêu cầu PHP `^8.3`, Laravel `^13.17`; môi trường đã kiểm tra trước đợt này có PHP CLI 8.5.10 và Laravel 13.35.0. `routes/web.php` đăng ký website, editor, thiệp công khai, RSVP, khách mời và checkout; `routes/admin.php` khai báo đăng nhập, dashboard, template và orders. Các bản ghi migration không chứng minh migration đã chạy trên database đang sử dụng.

Mô hình trong mã nguồn hiện tại gồm `users`, `weddings`, `invitations`, `invitation_templates`, `guests`, `guest_responses`, `wedding_wishes`, `plans`, `orders`. `weddings.user_id` là chủ dữ liệu; thiệp gắn wedding và template; quyền của khách tạo chưa đăng nhập được ghi theo slug trong session. Không đồng nhất hệ thống hiện tại với các bảng workspace/event/entitlement chỉ mới được đề xuất trong PRD.

## 2. Bản đồ chức năng và giới hạn

| Module | Trạng thái theo mã đã đọc | Backend, giao diện và dữ liệu | Vấn đề / bước tiếp theo | Ưu tiên; rủi ro |
|---|---|---|---|---|
| Marketing và mẫu | Một phần, chưa QA | `TemplateController`, `client/pages/templates.blade.php`, `template-detail.blade.php`; `invitation_templates` | Có bộ lọc, cache và preview; cần kiểm tra SQLite/MySQL, invalidation, responsive và asset. | P1; trung bình |
| Đăng nhập | Một phần | `Client/AuthController`, `Admin/AuthController`, form Blade; `users`, `sessions` | Có đăng ký, đăng nhập, session và admin middleware; chưa đối chiếu đầy đủ reset password, verify email và quyền chi tiết. Kiểm tra redirect an toàn. | P0; cao |
| Studio và xuất bản | Một phần; sửa autosave client chưa được kiểm thử | `InvitationEditorController`, `InvitationTemplateService`, `editor.blade.php`, `public/assets/client/js/editor/`, `editor-bridge.blade.php`; `weddings`, `invitations` | Có schema, preview, autosave và revision. `saveDraftWithLock` cập nhật cả `weddings` khi autosave: cần xác minh tác động với thiệp đang công khai. Client đã bổ sung gửi tiếp thay đổi phát sinh trong request; cần chạy test và kiểm tra tích hợp. | P0; cao |
| Mẫu thiệp | Một phần | `resources/views/client/invitations/{slug}` gồm Blade, schema, CSS, JS và assets | Mã canonical dùng 10 section; `tests/Feature/CanonicalTemplateSectionsTest.php` vẫn đòi 6 section và liệt kê mẫu cũ. Không kết luận kết quả test khi chưa chạy. | P0; trung bình |
| Khách mời | Đã bổ sung kiểm tra quyền, chưa chạy test | `GuestController::index/store/destroy`, `StoreGuestRequest`, `routes/web.php:62`; `guests`, `weddings` | Trước sửa, các action truy vấn theo ID mà không kiểm tra chủ sở hữu. Hiện controller kiểm tra quyền theo wedding, gồm tài khoản và session sở hữu thiệp; cần chạy test và xác minh độc lập. | P0; cao |
| RSVP và lời chúc | Một phần; đã sửa phạm vi token và mạng dùng chung, chưa chạy test | `GuestController`, Form Requests, `guest_responses`, `wedding_wishes` | RSVP có token đối chiếu theo guest, không token vẫn giới hạn theo IP; lời chúc tạo riêng, thống kê cộng người lớn/trẻ em. Cần chạy test, kiểm thử đồng thời MySQL và thiết kế sửa phản hồi có xác thực. | P0; cao |
| Thanh toán | Đã sửa một phần, cần kiểm thử trước mở bán | `CheckoutController`, `CreateOrderRequest`, `orders`, `plans`; checkout Blade/JS | Webhook yêu cầu HMAC, đối chiếu tài khoản và số tiền, bỏ qua tiền ra; kiểm tra plan/quyền thiệp và khóa đơn trong transaction. Cần chạy test, xác minh CSRF/gateway, transaction ID và đồng thời trên MySQL; chưa kiểm tra giao dịch thật. | P0; cao |
| Admin | Một phần | `routes/admin.php`, middleware `admin.auth`, admin controllers và views | Có dashboard, danh sách mẫu/đơn và một số thao tác; chưa có bằng chứng hoàn chỉnh về RBAC theo hành động, audit log, users, support hoặc hệ thống vận hành trong PRD. | P1; cao |
| Media | Một phần | `InvitationEditorController::uploadAsset`, disk `public` | Upload hiện ghi vào `weddings/photos` qua public storage; báo cáo editor cũ nói ghi vào thư mục source là lịch sử, không nên dùng làm mô tả hiện trạng. Cần kiểm tra quyền, vòng đời và dữ liệu cá nhân. | P1; cao |
| Agency, event, seating, check-in, CMS và support | Chưa xác minh đầy đủ; chưa thấy controller/model chuyên biệt trong phạm vi khảo sát | Chủ yếu là yêu cầu mục tiêu trong PRD | Kiểm tra tiếp trước khi kết luận không tồn tại; chưa tạo bảng hay UI mới. | P1/P2; trung bình |

## 3. Các mâu thuẫn và quyết định đề xuất

1. **Hiện trạng so với thiết kế đích:** Giữ mô hình wedding hiện hữu cho P0. Kiến trúc modular monolith theo ranh giới nghiệp vụ; chỉ bổ sung workspace/membership và event khi hợp đồng phân quyền, dữ liệu cũ và migration có phương án rõ ràng. Không đổi tên `weddings` hoặc `invitation_templates` chỉ theo tên bảng ở PRD.
2. **Mười section:** `InvitationTemplateService::CANONICAL_SECTION_ORDER` và skill editor mô tả 10 section; test 6 section là bằng chứng lệch quy ước. Cần đối chiếu các mẫu hiện có trước khi sửa test hay schema; không xóa test cũ chỉ để có kết quả xanh.
3. **Autosave và xuất bản:** Revision và draft tồn tại, nhưng autosave ghi `weddings` trực tiếp. Làm rõ dữ liệu nào là bản nháp và dữ liệu nào được render công khai trước khi hứa có snapshot hoặc publish revision độc lập.
4. **Thanh toán:** Đã thêm xác thực HMAC fail-closed, transaction và khóa dòng cho đơn webhook, đồng thời bỏ thao tác admin đánh dấu đã thanh toán không qua đối soát; chưa chạy kiểm thử. Cần bổ sung đối soát ID giao dịch, quy trình admin có quyền và audit, và xác minh đồng thời trên database thực tế. CSRF và reverse proxy phải được kiểm chứng trong môi trường tích hợp.
5. **Khách và RSVP:** Quyền quản lý khách và token RSVP đã giới hạn theo wedding; RSVP có token được nhận diện theo guest, lời chúc không còn ghi đè theo IP. Chưa chạy test; cần thiết kế đổi RSVP, xác thực sửa lời chúc và cách xử lý phản hồi ẩn danh chung IP tương thích dữ liệu cũ.
6. **Template version và tài nguyên:** `template_version` dạng chuỗi không đồng nghĩa với snapshot bất biến; nghiên cứu cách giữ thiệp cũ trước khi thay asset/schema. Media người dùng và tài nguyên mẫu có vòng đời và quyền truy cập khác nhau.

## 4. Tình trạng kiểm chứng

- Đã đọc tĩnh routes, controller mẫu, editor, khách, checkout, admin, các migration liên quan, một số test và phần kiến trúc/roadmap của PRD. Đây chưa phải đọc toàn bộ PRD, mọi file trong repository hay toàn bộ luồng end-to-end.
- Không dùng dữ liệu người dùng thực để thử nghiệm; không chạy migration, thanh toán, gửi tin hoặc truy cập hệ thống production.
- Kiểm toán bảo mật đầy đủ chưa thực hiện: môi trường chạy kiểm thử cô lập với toàn bộ kiểm soát bắt buộc chưa được thiết lập và chưa có xác minh độc lập. Các nhận định bảo mật ở đây là tín hiệu từ source cần kiểm chứng, không phải kết luận kiểm toán bảo mật hoàn chỉnh.
- Các task tiếp theo, dependency, test và rollback: xem `MAY_HEN_IMPLEMENTATION_ROADMAP.md`.

## 5. Đối chiếu chi tiết lần hai: khách, xuất bản và môi trường test

**Cập nhật SEC-01:** Sau khảo sát ban đầu, `GuestController` đã được sửa để kiểm tra quyền sở hữu wedding hoặc session hợp lệ trước các thao tác quản lý khách. Đã bổ sung feature test, nhưng chưa chạy test hay xác minh độc lập; các nhận định dưới đây ghi lại cơ sở trước sửa và các bước cần xác minh, không mô tả nguyên trạng sau sửa.

Các điểm dưới đây được đối chiếu trực tiếp từ source, chưa được xác minh bằng request hoặc trên môi trường triển khai:

1. **Trang danh sách khách:** `routes/web.php:62-65` không gắn middleware đăng nhập cho nhóm quản lý khách; `GuestController::index` lấy thiệp theo ID rồi nạp danh sách khách và phản hồi mà không gọi kiểm tra sở hữu. View `resources/views/client/pages/guests.blade.php:109` dựng URL cá nhân chứa guest token, sau đó đưa link vào các nút sao chép. Cần xác minh độc lập toàn bộ middleware triển khai và kiểm thử hai chủ thiệp cùng session khách; ưu tiên bảo vệ thông tin và link khách trước khi mở rộng tính năng.
2. **Giới hạn của `isOwner()`:** `Invitation::isOwner()` hỗ trợ tài khoản sở hữu wedding hoặc slug nằm trong `owned_invitation_slugs` của session. Thao tác xóa dùng `guest_id`, không nhận `invitation_id`. Khi triển khai quyền xóa phải kiểm tra quyền với wedding tương ứng và xét mọi thiệp hợp lệ của wedding, không chỉ `first()` theo thứ tự ngẫu nhiên.
3. **Trạng thái công khai:** `InvitationEditorController::show` hiện truy vấn thiệp theo slug; trong phương thức đã đọc không thấy điều kiện `is_published` hoặc `expires_at`. Đây là chênh lệch cần xác minh với chính sách truy cập, các route tương tác song song và dữ liệu thiệp cũ trước khi thay đổi hành vi.
4. **Thay đổi trạng thái mẫu và cache:** admin toggle `is_active`/`is_featured` tại `app/Http/Controllers/Admin/TemplateController.php`; model `InvitationTemplate` có hook `saved` xóa cache chi tiết và tổng số. Cache danh sách theo category/tier/sort trong `Client/TemplateController.php` không nằm trong danh sách xóa của hook đã đọc. Cần kiểm chứng trạng thái danh sách sau cập nhật trước khi kết luận có regression.
5. **Cấu hình test hiện có:** `phpunit.xml` khai báo SQLite `:memory:`, cache và session `array`, queue `sync`; `tests/Pest.php` gắn `RefreshDatabase` cho Feature. Đây là cấu hình ứng dụng, không tự bảo đảm sandbox hệ điều hành, môi trường rỗng, cách ly mạng hay giới hạn tiến trình. Chưa chạy suite.
6. **Sandbox kiểm toán:** không tìm thấy Docker/Podman qua `Get-Command`; `wsl --list --quiet` báo WSL chưa cài. Chưa có bằng chứng môi trường hiện tại áp dụng đồng thời toàn bộ kiểm soát sandbox của security-audit. Không mặc định rằng không tồn tại công cụ cách ly khác trên máy.
7. **Độc lập kiểm toán:** yêu cầu khởi tạo agent khảo sát độc lập thất bại vì hệ thống trả lỗi model kế thừa không hợp lệ. Chưa có xác minh độc lập và không ghi nhận kết luận bảo mật là đã xác nhận.

Các bằng chứng này không thay thế việc đọc toàn bộ PRD, kiểm toán tất cả module, xác minh cấu hình đang triển khai hoặc test có dữ liệu giả.

## 6. Luồng Studio, xuất bản và thanh toán theo mã hiện tại

| Bước | Đường đi trong mã | Hiện trạng cần đối chiếu với PRD |
|---|---|---|
| Chọn mẫu | `InvitationEditorController::create` chọn template active, lấy schema qua `InvitationTemplateService::getSchema`, render `client/pages/editor.blade.php`. | Kiểm tra quyền dùng mẫu trả phí và khôi phục dữ liệu với cả khách vãng lai lẫn tài khoản. |
| Sửa và xem trước | `public/assets/client/js/pages/editor.js` cập nhật store và gửi thông điệp sang `invitations/partials/editor-bridge.blade.php`; `editor/history.js` lưu tối đa 20 snapshot client. | Có thành phần live sync và undo/redo, nhưng cần kiểm thử DOM, dữ liệu lồng nhau, thiết bị và khôi phục sau reload trước khi coi là hoàn chỉnh. |
| Chuyển mẫu | `editor/template-switcher.js` lưu dữ liệu tạm trong sessionStorage, đặt query `template` rồi chuyển `window.location.href`; dừng khi lưu thất bại, ảnh đang xử lý hoặc có xung đột. | Mã vẫn reload toàn trang. Việc khôi phục `custom_config`, dữ liệu ảnh và trường lồng nhau cần test; không khẳng định giữ đủ dữ liệu nếu chưa chạy. |
| Autosave | `editor/autosaver.js` gửi `draft_config` và `wedding_delta`; service yêu cầu revision khớp chính xác, cập nhật cả `weddings` và `invitations.draft_config`. Draft local khác revision được bảo vệ và có lựa chọn tải xuống hoặc lưu bản dự phòng. | Dữ liệu wedding công khai có thể đổi khi autosave; draft chưa là snapshot đầy đủ. Draft chuyển mẫu trong sessionStorage chưa đối chiếu revision, nhiều thiệp chung wedding chưa được bảo vệ. Test chưa chạy. |
| Lưu / xuất bản | `InvitationEditorController::store` gọi `saveInvitation`; sửa thiệp hiện có kiểm tra revision và khóa dòng, tạo mới vẫn đặt `is_published = true`, `published_at` và `expires_at` sau 60 ngày. | Hành vi tạo mới vẫn xuất bản ngay; kiểm tra revision chưa được test và chưa giải quyết dữ liệu wedding dùng chung. PRD mô tả bước nháp và xuất bản riêng là mục tiêu chưa có bằng chứng hoàn thành. |
| Đọc thiệp | `InvitationEditorController::show` tìm slug và render wedding/template. | Phương thức đã đọc không lọc `is_published`/`expires_at`; cần xác minh hợp đồng quyền riêng tư và các endpoint tương tác trước khi thiết kế trạng thái mới. |
| Checkout và admin | Checkout tạo đơn pending, phản hồi thông tin QR và có webhook; đã gỡ thao tác admin tự đánh dấu đã thanh toán không qua đối soát. | Cần xây dựng quy trình đối soát thủ công có kiểm tra, phân quyền và audit theo PRD; kiểm thử với đơn/thiệp giả trước khi dùng. |

### Khoảng trống dữ liệu và chính sách

- `InvitationTemplateService::getValidationRules()` có định nghĩa quy tắc cho trường wedding, nhưng cần xác minh đường gọi thực tế; tại `InvitationEditorController::store`, đoạn validation chung đã đọc chỉ kiểm tra `template_id`, ngoài các bước làm sạch và kiểm tra tên riêng lẻ. Không suy luận rằng tất cả trường wedding đã được validate theo schema.
- `CreateOrderRequest` kiểm tra plan tồn tại; `CheckoutController::show` lọc plan active, còn `process` dùng `findOrFail` theo ID. Cần kiểm thử tình huống plan ngừng bán sau khi trang checkout được mở.
- Thông tin ngân hàng trong checkout hiện được đặt trực tiếp trong controller; cần rà soát cấu hình thực tế và phương án chuyển sang cấu hình an toàn, không ghi lại giá trị nhạy cảm trong báo cáo.
- Các thông số `duration_days`, thời hạn 60 ngày mặc định và mô tả thời hạn gói trong UI cần được đối chiếu với chính sách kinh doanh và dữ liệu thực tế trước khi sửa.
