# 🏛️ RUNTIME ADMIN INVENTORY & TRACEABILITY AUDIT
## BẢN ĐỒ KIỂM TOÁN HIỆN TRẠNG 100% HỆ THỐNG QUẢN TRỊ ADMIN MÂY HẸN (MAYHEN.VN)

> **Mục tiêu**: Khảo sát thực tế toàn bộ route, middleware, quyền hạn, controller, views, UI controls, bảng database và tests hiện có trong kho mã nguồn `mayhen.vn`.  
> **Nguyên tắc**: Xác minh mã nguồn thực tế, không tin vào các báo cáo giả định hay trạng thái lý thuyết.

---

## 1. TỔNG QUAN HỆ THỐNG ADMIN HIỆN TẠI

- **Prefix Route**: `/admin`
- **Route File**: `routes/admin.php`
- **Tập controllers**: `app/Http/Controllers/Admin/` (8 controllers)
  1. `AuthController.php`: Quản lý đăng nhập, đăng xuất, chuyển hướng thông minh.
  2. `DashboardController.php`: Bảng điều khiển KPI tổng hợp, biểu đồ doanh thu, số lượng thiệp, RSVP, cảnh báo vận hành.
  3. `InvitationController.php`: Quản lý thiệp cưới khách hàng, xem chi tiết, khóa/mở khóa thiệp, gia hạn, đổi mẫu, xuất dữ liệu khách/quà/RSVP/lời chúc.
  4. `OrderController.php`: Quản lý đơn hàng, doanh thu, đối soát thanh toán SePay/VietQR, hoàn tiền, hủy đơn, xuất CSV.
  5. `SettingController.php`: Cấu hình hệ thống, quản lý gói cước (Plans), cấu hình ngân hàng VietQR/SePay, thanh tra bộ nhớ lưu trữ (Storage Inspector), nhật ký kiểm toán (Audit Logs).
  6. `TemplateController.php`: Quản lý kho mẫu thiệp cưới, bật/tắt mẫu, ghim nổi bật, quét phát hiện mẫu mới, thanh tra tính toàn vẹn Schema (10 Section Canonical).
  7. `UserController.php`: Quản lý người dùng, phân quyền vai trò (Admin, Manager, Staff, Partner, Customer), đặt lại mật khẩu, xuất danh sách CSV.
  8. `WishController.php`: Kiểm duyệt lời chúc khách gửi, giám sát phản hồi tham dự RSVP tập trung, xuất CSV.

- **Middleware Bảo vệ Quản trị**:
  - `admin.auth` (`App\Http\Middleware\AdminAuthenticate`): Chặn khách vãng lai và người dùng customer/partner, chỉ cho phép user có `canAccessAdminPanel()` (`admin`, `manager`, `staff`).
  - `admin.manager` (`App\Http\Middleware\EnsureAdminOrManager`): Yêu cầu quyền Quản lý (`manager`) hoặc Quản trị viên (`admin`) cho các thao tác nhạy cảm (khóa/mở khóa thiệp, duyệt/hoàn tiền đơn, xóa lời chúc).
  - `admin.super` (`App\Http\Middleware\EnsureSuperAdmin`): Cưỡng chế chỉ Quản trị viên cấp cao (`admin`) mới được phân quyền user, sửa cấu hình gói cước, ngân hàng, xem audit log và quét template.

- **Stack Giao diện**:
  - 100% Pure HTML5 + Pure Vanilla CSS + Native ES Modules JS (Zero Third-Party Frameworks, Zero CDN).
  - Prefix CSS bắt buộc: `mayhen_admin_*` (trang đăng nhập: `mayhen_admin_login_*`).
  - Thư mục tài sản: `public/assets/admin/css/` và `public/assets/admin/js/`.

---

## 2. MA TRẬN 28 ENDPOINTS & TRUY VẾT KIỂM TOÁN (ROUTE TO CODE MATRIX)

| STT | Route Name | Method | URI | Controller@Action | Middleware | Ghi chú & Đánh giá |
|:---:|---|:---:|---|---|---|---|
| 1 | `admin.index` | `GET` | `/admin` | `AuthController@index` | Web | Chuyển hướng thông minh tới dashboard nếu đã đăng nhập đủ quyền |
| 2 | `admin.login` | `GET` | `/admin/login` | `AuthController@showLoginForm` | Web | Giao diện đăng nhập Quiet Luxury |
| 3 | `admin.login.submit` | `POST` | `/admin/login` | `AuthController@login` | Web, `throttle:auth_attempts` | Xác thực đăng nhập, rate limit 5 lần, tái tạo session token |
| 4 | `admin.logout` | `POST` | `/admin/logout` | `AuthController@logout` | Web | Đăng xuất, hủy session, thu hồi CSRF token |
| 5 | `admin.dashboard` | `GET` | `/admin/dashboard` | `DashboardController@index` | `admin.auth` | KPI 7 ngày, tổng doanh thu, số thiệp, RSVP, Action Alerts |
| 6 | `admin.invitations.index` | `GET` | `/admin/invitations` | `InvitationController@index` | `admin.auth` | Danh sách thiệp, lọc theo status, template, search cô dâu chú rể |
| 7 | `admin.invitations.show` | `GET` | `/admin/invitations/{id}` | `InvitationController@show` | `admin.auth` | Chi tiết thiệp cưới, embed live preview, thống kê RSVP, nhật ký |
| 8 | `admin.invitations.toggle_publish` | `POST` | `/admin/invitations/{id}/toggle-publish` | `InvitationController@togglePublish` | `admin.auth` | Bật/ẩn trạng thái xuất bản công khai thiệp cưới |
| 9 | `admin.invitations.extend` | `POST` | `/admin/invitations/{id}/extend` | `InvitationController@extend` | `admin.auth` | Gia hạn thời gian lưu trữ trực tuyến thêm N ngày |
| 10 | `admin.invitations.migrate_template` | `POST` | `/admin/invitations/{id}/migrate-template` | `InvitationController@migrateTemplate` | `admin.auth` | Chuyển đổi giao diện thiệp sang mẫu mới bảo toàn dữ liệu |
| 11 | `admin.invitations.export_guests` | `GET` | `/admin/invitations/{id}/export-guests` | `InvitationController@exportGuests` | `admin.auth` | Xuất CSV danh sách khách mời thiệp (UTF-8 BOM, CsvSanitizer) |
| 12 | `admin.invitations.export_gifts` | `GET` | `/admin/invitations/{id}/export-gifts` | `InvitationController@exportGifts` | `admin.auth` | Xuất CSV mừng cưới của thiệp (chống formula injection) |
| 13 | `admin.invitations.export_rsvps` | `GET` | `/admin/invitations/{id}/export-rsvps` | `InvitationController@exportRsvps` | `admin.auth` | Xuất CSV phản hồi tham dự của thiệp |
| 14 | `admin.invitations.export_wishes` | `GET` | `/admin/invitations/{id}/export-wishes` | `InvitationController@exportWishes` | `admin.auth` | Xuất CSV lời chúc phúc của thiệp |
| 15 | `admin.invitations.lock` | `POST` | `/admin/invitations/{id}/lock` | `InvitationController@lock` | `admin.auth`, `admin.manager` | Khóa thiệp vi phạm, ghi nhận lý do và thời điểm khóa |
| 16 | `admin.invitations.unlock` | `POST` | `/admin/invitations/{id}/unlock` | `InvitationController@unlock` | `admin.auth`, `admin.manager` | Mở khóa thiệp, khôi phục trạng thái hoạt động |
| 17 | `admin.templates.index` | `GET` | `/admin/templates` | `TemplateController@index` | `admin.auth` | Danh sách kho mẫu, lọc active/inactive/featured/category |
| 18 | `admin.templates.export` | `GET` | `/admin/templates/export-csv` | `TemplateController@exportCsv` | `admin.auth` | Xuất CSV danh sách mẫu thiệp |
| 19 | `admin.templates.scan_discovery` | `POST` | `/admin/templates/scan-discovery` | `TemplateController@scanDiscovery` | `admin.auth`, `admin.super` | Tự động quét và phát hiện template mới trong thư mục invitations |
| 20 | `admin.templates.edit` | `GET` | `/admin/templates/{id}/edit` | `TemplateController@edit` | `admin.auth` | Form sửa thông tin mẫu kèm bảng thanh tra 10 Section Canonical |
| 21 | `admin.templates.update` | `PUT` | `/admin/templates/{id}` | `TemplateController@update` | `admin.auth`, `admin.manager` | Lưu thông tin mẫu, danh mục, giá tier, SEO |
| 22 | `admin.templates.toggle_active` | `POST` | `/admin/templates/{id}/toggle-active` | `TemplateController@toggleActive` | `admin.auth`, `admin.manager` | Bật/tắt trạng thái mở bán của mẫu thiệp |
| 23 | `admin.templates.toggle_featured` | `POST` | `/admin/templates/{id}/toggle-featured` | `TemplateController@toggleFeatured` | `admin.auth`, `admin.manager` | Ghim/bỏ ghim mẫu nổi bật lên đầu danh sách |
| 24 | `admin.orders.index` | `GET` | `/admin/orders` | `OrderController@index` | `admin.auth` | Danh sách đơn hàng, doanh thu, lọc trạng thái, gói, ngày |
| 25 | `admin.orders.export` | `GET` | `/admin/orders/export-csv` | `OrderController@exportCsv` | `admin.auth` | Xuất CSV báo cáo đơn hàng và doanh thu |
| 26 | `admin.orders.webhooks` | `GET` | `/admin/orders/webhooks/audit` | `OrderController@webhooks` | `admin.auth` | Nhật ký webhook thanh toán VietQR / SePay |
| 27 | `admin.orders.show` | `GET` | `/admin/orders/{id}` | `OrderController@show` | `admin.auth` | Chi tiết đơn hàng, gói cước, tài khoản, thiệp liên kết |
| 28 | `admin.orders.approve` | `POST` | `/admin/orders/{id}/approve` | `OrderController@approve` | `admin.auth`, `admin.manager` | Duyệt thanh toán thủ công và tự kích hoạt bản quyền thiệp |
| 29 | `admin.orders.refund` | `POST` | `/admin/orders/{id}/refund` | `OrderController@refund` | `admin.auth`, `admin.manager` | Hoàn tiền đơn hàng, thu hồi quyền trực tuyến bảo toàn data |
| 30 | `admin.orders.cancel` | `POST` | `/admin/orders/{id}/cancel` | `OrderController@cancel` | `admin.auth`, `admin.manager` | Hủy đơn hàng đang chờ |
| 31 | `admin.users.index` | `GET` | `/admin/users` | `UserController@index` | `admin.auth` | Danh sách người dùng, lọc vai trò, tìm theo email/tên/phone |
| 32 | `admin.users.export` | `GET` | `/admin/users/export-csv` | `UserController@exportCsv` | `admin.auth` | Xuất CSV danh sách người dùng |
| 33 | `admin.users.show` | `GET` | `/admin/users/{id}` | `UserController@show` | `admin.auth` | Hồ sơ chi tiết người dùng, danh sách thiệp và lịch sử đơn |
| 34 | `admin.users.change_role` | `POST` | `/admin/users/{id}/change-role` | `UserController@changeRole` | `admin.auth`, `admin.super` | Thay đổi vai trò người dùng (Admin, Manager, Staff, Partner...) |
| 35 | `admin.users.reset_password` | `POST` | `/admin/users/{id}/reset-password` | `UserController@resetPassword` | `admin.auth`, `admin.manager` | Đặt lại mật khẩu mới cho người dùng |
| 36 | `admin.wishes.index` | `GET` | `/admin/wishes` | `WishController@index` | `admin.auth` | Kiểm duyệt lời chúc, bộ lọc spam từ khóa, lọc theo thiệp |
| 37 | `admin.wishes.export` | `GET` | `/admin/wishes/export-csv` | `WishController@exportWishesCsv` | `admin.auth` | Xuất CSV lời chúc toàn hệ thống |
| 38 | `admin.wishes.rsvps` | `GET` | `/admin/wishes/rsvps` | `WishController@rsvps` | `admin.auth` | Báo cáo phản hồi tham dự RSVP tập trung |
| 39 | `admin.wishes.rsvps.export` | `GET` | `/admin/wishes/rsvps/export-csv` | `WishController@exportRsvpsCsv` | `admin.auth` | Xuất CSV dữ liệu RSVP |
| 40 | `admin.wishes.toggle_approval` | `POST` | `/admin/wishes/{id}/toggle-approval` | `WishController@toggleApproval` | `admin.auth` | Duyệt / Tạm ẩn lời chúc hiển thị trên thiệp |
| 41 | `admin.wishes.destroy` | `DELETE` | `/admin/wishes/{id}` | `WishController@destroy` | `admin.auth`, `admin.manager` | Xóa vĩnh viễn lời chúc thô tục, vi phạm |
| 42 | `admin.settings.plans` | `GET` | `/admin/settings/plans` | `SettingController@plans` | `admin.auth`, `admin.super` | Danh sách gói dịch vụ, giá mở bán, doanh thu theo gói |
| 43 | `admin.settings.plans.edit` | `GET` | `/admin/settings/plans/{id}/edit` | `SettingController@editPlan` | `admin.auth`, `admin.super` | Form sửa gói cước, giá, thời hạn, tính năng đi kèm |
| 44 | `admin.settings.plans.update` | `PUT` | `/admin/settings/plans/{id}` | `SettingController@updatePlan` | `admin.auth`, `admin.super` | Cập nhật thông tin gói cước |
| 45 | `admin.settings.plans.toggle_active` | `POST` | `/admin/settings/plans/{id}/toggle-active` | `SettingController@togglePlanActive` | `admin.auth`, `admin.super` | Kích hoạt / Tạm dừng mở bán gói cước |
| 46 | `admin.settings.banking` | `GET` | `/admin/settings/banking` | `SettingController@banking` | `admin.auth`, `admin.super` | Cấu hình tài khoản ngân hàng thụ hưởng và VietQR SePay |
| 47 | `admin.settings.banking.update` | `POST` | `/admin/settings/banking` | `SettingController@updateBanking` | `admin.auth`, `admin.super` | Lưu cấu hình tài khoản thụ hưởng |
| 48 | `admin.settings.storage` | `GET` | `/admin/settings/storage` | `SettingController@storageInspector` | `admin.auth`, `admin.super` | Thanh tra bộ nhớ lưu trữ, phân loại dung lượng wedding/client/admin |
| 49 | `admin.settings.audit_logs` | `GET` | `/admin/settings/audit_logs` | `SettingController@auditLogs` | `admin.auth`, `admin.super` | Xem nhật ký kiểm toán quản trị tập trung |
| 50 | `admin.settings.audit_logs.export` | `GET` | `/admin/settings/audit_logs/export-csv` | `SettingController@exportAuditLogs` | `admin.auth`, `admin.super` | Xuất CSV nhật ký kiểm toán hệ thống |

---

## 3. CÁC TỒN ĐẠI & LỖ HỔNG PHÁT HIỆN QUA KIỂM TOÁN (FINDINGS & BUG REGISTER)

Qua rà soát chi tiết từng dòng code và luồng thực thi:

1. **Bug P1-01 (Staff Login Lockout)**:
   - *Vị trí*: `app/Http/Controllers/Admin/AuthController.php` (dòng 23, 35, 74).
   - *Nguyên nhân*: Kiểm tra `$user->isAdmin()` (chỉ chấp nhận `admin` và `manager`) thay vì `$user->canAccessAdminPanel()` (`admin`, `manager`, `staff`).
   - *Hậu quả*: Tài khoản nhân viên `staff` hoàn toàn bị khóa ở trang đăng nhập `/admin/login`, không thể truy cập dù quyền trong hệ thống và middleware cho phép.

2. **Bug P0-02 (Super Admin Demotion Safety)**:
   - *Vị trí*: `app/Http/Controllers/Admin/UserController.php@changeRole`.
   - *Nguyên nhân*: Chỉ kiểm tra `$user->id === Auth::id()`, chưa có cơ chế kiểm tra Quản trị viên cấp cao duy nhất (`adminCount <= 1`).
   - *Hậu quả*: Nếu có thao tác đổi role vô tình, hệ thống có thể bị mất hoàn toàn Super Admin duy nhất.

3. **Bug P0-03 (Manual Order Reconciliation Lacks Bank Reference & Proof)**:
   - *Vị trí*: `app/Http/Controllers/Admin/OrderController.php@approve`.
   - *Nguyên nhân*: Hàm `approve` duyệt đơn trực tiếp mà không yêu cầu nhập lý do đối soát và mã tham chiếu giao dịch ngân hàng / giấy báo có.
   - *Hậu quả*: Trái với nguyên tắc đối soát tài chính bắt buộc của Master Prompt (`ADM-A09-020`, `ADM-A09-021`), có thể dẫn đến việc nhân viên duyệt đơn bừa bãi không căn cứ.

4. **Bug P1-04 (Audit Logger Missing Sensitive Masking & Auth Events)**:
   - *Vị trí*: `app/Services/AuditLogger.php`.
   - *Nguyên nhân*: Tham số `$details` chưa có bộ lọc tự động che giấu (masking) các trường nhạy cảm (`password`, `token`, `secret`, `full_card`, `api_key`), và `AuthController` chưa ghi log khi đăng nhập/đăng xuất.

---

## 4. KẾT LUẬN & HƯỚNG TRIỂN KHAI

Hệ thống Admin Mây Hẹn đã có một bộ khung 50 endpoints vận hành thực tế vững chắc, giao diện thuần túy không phụ thuộc framework ngoài. Bước tiếp theo là hoàn tất bộ tài liệu theo dõi (Wave 00), sau đó vá triệt để 4 lỗi trên trong Wave 01 kèm kiểm thử tự động toàn diện.

