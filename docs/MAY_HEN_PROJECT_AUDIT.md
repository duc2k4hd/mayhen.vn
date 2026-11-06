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
| Studio và xuất bản | Một phần | `InvitationEditorController`, `InvitationTemplateService`, `editor.blade.php`, `public/assets/client/js/editor/`, `editor-bridge.blade.php`; `weddings`, `invitations` | Có schema, preview, autosave và revision. `saveDraftWithLock` cập nhật cả `weddings` khi autosave: cần xác minh tác động với thiệp đang công khai. Client có nhánh bỏ qua sync khi đang lưu; kiểm tra thay đổi phát sinh trong request. | P0; cao |
| Mẫu thiệp | Một phần | `resources/views/client/invitations/{slug}` gồm Blade, schema, CSS, JS và assets | Mã canonical dùng 10 section; `tests/Feature/CanonicalTemplateSectionsTest.php` vẫn đòi 6 section và liệt kê mẫu cũ. Không kết luận kết quả test khi chưa chạy. | P0; trung bình |
| Khách mời | Một phần, cần xử lý quyền | `GuestController::index/store/destroy`, `StoreGuestRequest`, `routes/web.php:62`; `guests`, `weddings` | Các action truy vấn theo ID nhưng không thấy kiểm tra `isOwner()`; request cho phép `authorize()` trả `true`. Cần kiểm thử chủ sở hữu, người khác và người chưa đăng nhập, kể cả quyền session của khách tạo thiệp. | P0; cao |
| RSVP và lời chúc | Một phần | `GuestController`, Form Requests, `guest_responses`, `wedding_wishes` | RSVP khóa theo IP và tìm `guest_token` không kèm điều kiện wedding; cần xác minh ràng buộc giữa token và thiệp. Kiểm tra trường hợp nhiều khách chung mạng, sửa phản hồi và thống kê người lớn/trẻ em. | P0; cao |
| Thanh toán | Một phần, cần rà soát trước mở bán | `CheckoutController`, `CreateOrderRequest`, `orders`, `plans`; checkout Blade/JS | Điều kiện xác thực webhook chỉ từ chối khi header tồn tại nhưng không khớp; route trong nhóm web cần đối chiếu CSRF thực tế. Request chỉ xác nhận `invitation_id` tồn tại, chưa thấy ràng buộc quyền sở hữu. Cần xác minh gateway, số tiền, transaction ID và xử lý đồng thời. Không kiểm tra giao dịch thật. | P0; cao |
| Admin | Một phần | `routes/admin.php`, middleware `admin.auth`, admin controllers và views | Có dashboard, danh sách mẫu/đơn và một số thao tác; chưa có bằng chứng hoàn chỉnh về RBAC theo hành động, audit log, users, support hoặc hệ thống vận hành trong PRD. | P1; cao |
| Media | Một phần | `InvitationEditorController::uploadAsset`, disk `public` | Upload hiện ghi vào `weddings/photos` qua public storage; báo cáo editor cũ nói ghi vào thư mục source là lịch sử, không nên dùng làm mô tả hiện trạng. Cần kiểm tra quyền, vòng đời và dữ liệu cá nhân. | P1; cao |
| Agency, event, seating, check-in, CMS và support | Chưa xác minh đầy đủ; chưa thấy controller/model chuyên biệt trong phạm vi khảo sát | Chủ yếu là yêu cầu mục tiêu trong PRD | Kiểm tra tiếp trước khi kết luận không tồn tại; chưa tạo bảng hay UI mới. | P1/P2; trung bình |

## 3. Các mâu thuẫn và quyết định đề xuất

1. **Hiện trạng so với thiết kế đích:** Giữ mô hình wedding hiện hữu cho P0. Kiến trúc modular monolith theo ranh giới nghiệp vụ; chỉ bổ sung workspace/membership và event khi hợp đồng phân quyền, dữ liệu cũ và migration có phương án rõ ràng. Không đổi tên `weddings` hoặc `invitation_templates` chỉ theo tên bảng ở PRD.
2. **Mười section:** `InvitationTemplateService::CANONICAL_SECTION_ORDER` và skill editor mô tả 10 section; test 6 section là bằng chứng lệch quy ước. Cần đối chiếu các mẫu hiện có trước khi sửa test hay schema; không xóa test cũ chỉ để có kết quả xanh.
3. **Autosave và xuất bản:** Revision và draft tồn tại, nhưng autosave ghi `weddings` trực tiếp. Làm rõ dữ liệu nào là bản nháp và dữ liệu nào được render công khai trước khi hứa có snapshot hoặc publish revision độc lập.
4. **Thanh toán:** Xác thực fail-closed với credential theo hợp đồng provider, so khớp chính xác, xác minh giao dịch và chống xử lý trùng bằng transaction/ràng buộc phù hợp. CSRF và reverse proxy phải được kiểm chứng trong môi trường tích hợp. Không tự suy đoán giao thức SePay.
5. **Khách và RSVP:** Tái sử dụng quyền sở hữu thiệp hiện có cho khách đã đăng nhập lẫn session hợp lệ; mọi truy vấn guest token phải gắn phạm vi wedding phù hợp. Cách hỗ trợ đổi RSVP và chuyển từ IP sang token cần thiết kế tương thích dữ liệu cũ.
6. **Template version và tài nguyên:** `template_version` dạng chuỗi không đồng nghĩa với snapshot bất biến; nghiên cứu cách giữ thiệp cũ trước khi thay asset/schema. Media người dùng và tài nguyên mẫu có vòng đời và quyền truy cập khác nhau.

## 4. Tình trạng kiểm chứng

- Đã đọc tĩnh routes, controller mẫu, editor, khách, checkout, admin, các migration liên quan, một số test và phần kiến trúc/roadmap của PRD. Đây chưa phải đọc toàn bộ PRD, mọi file trong repository hay toàn bộ luồng end-to-end.
- Không dùng dữ liệu người dùng thực để thử nghiệm; không chạy migration, thanh toán, gửi tin hoặc truy cập hệ thống production.
- Kiểm toán bảo mật đầy đủ chưa thực hiện: môi trường chạy kiểm thử cô lập với toàn bộ kiểm soát bắt buộc chưa được thiết lập và chưa có xác minh độc lập. Các nhận định bảo mật ở đây là tín hiệu từ source cần kiểm chứng, không phải kết luận kiểm toán bảo mật hoàn chỉnh.
- Các task tiếp theo, dependency, test và rollback: xem `MAY_HEN_IMPLEMENTATION_ROADMAP.md`.
