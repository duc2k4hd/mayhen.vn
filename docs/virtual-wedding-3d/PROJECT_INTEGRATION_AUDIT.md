# KIỂM TOÁN MÃ NGUỒN & KIẾN TRÚC TÍCH HỢP MÂY HẸN (PROJECT INTEGRATION AUDIT — W00)

> Tài liệu này ghi nhận hiện trạng thực tế 100% từ kho mã nguồn `mayhen.vn` (`Laravel 13.35.0`, `PHP 8.5`, `timezone = Asia/Ho_Chi_Minh`) nhằm đảm bảo phân hệ **Virtual Wedding World 3D** tích hợp liền mạch, không nhân bản dữ liệu khách hàng/đơn hàng và không làm chậm bất kỳ trang thiệp cưới 2D nào đang vận hành.

---

## 1. Bảng Ánh Xạ Mô Hình Dữ Liệu Hiện Hữu (Không Tạo Trùng Hệ Thống Thứ Hai)

| Thực thể hiện có | Bảng CSDL | Khóa & Cột cốt lõi | Vai trò tái sử dụng trực tiếp trong Virtual Wedding World 3D |
| :--- | :--- | :--- | :--- |
| `App\Models\User` | `users` | `id`, `name`, `email`, `phone`, `role`, `is_locked` | Chủ sở hữu đám cưới (`customer`), đối tác (`partner`) và quản trị viên duyệt danh mục Venue 3D (`admin`, `manager`). |
| `App\Models\Wedding` | `weddings` | `id`, `user_id`, `partner_id`, `groom_name`, `bride_name`, `wedding_date`, `venue_name`, `extra_data` | Gốc định danh duy nhất của đám cưới. Mọi không gian cưới 3D bắt buộc gắn với `wedding_id` có thật — tuyệt đối không tạo bảng đám cưới song song. |
| `App\Models\WeddingEvent` | `wedding_events` | `id`, `wedding_id`, `name`, `event_type` (`ceremony_bride`, `ceremony_groom`, `banquet`...), `event_date`, `event_time`, `is_active` | Định nghĩa từng phiên lễ/tiệc cụ thể (ví dụ: Lễ Vu Quy ngày 1, Tiệc Thành Hôn ngày 2) để tính độc lập mốc mở cổng và mốc khóa cổng `+5 giờ` cho từng phiên. |
| `App\Models\Invitation` | `invitations` | `id`, `wedding_id`, `slug`, `custom_config`, `is_published`, `expires_at` | Cung cấp `slug`, kiểm tra quyền chủ sở hữu (`Invitation::isOwner()`), kiểm tra khóa quản trị (`isLocked()`) và liên kết hai chiều giữa Thiệp 2D ↔ Không gian 3D. |
| `App\Models\Guest` | `guests` | `id`, `wedding_id`, `name`, `salutation`, `group_name`, `table_id`, `guest_token`, `revoked_tokens` | Định danh khách mời chính thức qua `?token={guest_token}` (tự điền tên hiển thị gợi ý, bàn tiệc được xếp và kiểm tra thu hồi token `410 Gone`). |
| `App\Models\WeddingWish` | `wedding_wishes` | `id`, `wedding_id`, `guest_id`, `name`, `message`, `is_approved`, `is_private` | Gửi lời chúc phúc từ bên trong không gian 3D lưu thẳng vào sổ lưu bút chung của đám cưới (tôn trọng `getWishModerationMode()` và `is_private`). |
| `App\Models\WeddingGift` | `wedding_gifts` | `id`, `wedding_id`, `guest_id`, `sender_name`, `amount`, `payment_method` | Hộp mừng cưới trong rạp 3D hiển thị mã VietQR tài khoản cá nhân của cô dâu chú rể (`VietQrService::generateDataUri()`), hoàn toàn tách biệt khỏi tài khoản thu hộ nền tảng. |
| `App\Services\AuditLogger` | `storage/logs/admin_audit.jsonl` | `action`, `targetType`, `targetId`, `details` (tự che `token`, `secret`, `bank_account`) | Ghi nhật ký kiểm toán mọi thao tác nhạy cảm: xuất bản world, khóa cổng khẩn cấp, bật/dừng livestream, đối soát vé vào cổng và phát hành phiên bản GLB. |

---

## 2. Phân Tích Rủi Ro Tích Hợp & Các Chốt Chặn Kiến Trúc Quan Trọng

### 2.1. Chốt chặn Thanh toán (`Order` vs Vé vào cổng `5.000đ` & Thuê Venue 3D)
- **Phát hiện quan trọng từ mã nguồn:**
  - Bảng `orders` hiện tại có `user_id` (`NOT NULL`) và `plan_id` (`NOT NULL`).
  - Đặc biệt, `EntitlementStateMachineService::getActivePlan()` (`app/Services/EntitlementStateMachineService.php:111-118`) lấy đơn `paid` mới nhất gắn với `invitation_id` để suy ra gói cước của thiệp, còn `CheckoutController@handleWebhook` tự động gia hạn `invitation.expires_at` theo `plan->duration_days`.
- **Quyết định kiến trúc bắt buộc (Áp dụng ở W07):**
  - **Tuyệt đối không chèn giao dịch vé `5.000đ` của khách mời vào bảng `orders`** để tránh làm hỏng gói `Plus`/`Pro` của chủ tiệc.
  - Tách lớp xác thực chữ ký HMAC-SHA256 + chống replay `300s` của `CheckoutController@handleWebhook` thành `SePayWebhookService` dùng chung một endpoint `/api/payments/sepay/webhook`, phân luồng theo tiền tố mã chuyển khoản:
    - `MH...` → Đơn mua/gia hạn gói thiệp (`orders` hiện hữu — giữ nguyên 100%).
    - `MVL...` → Phí mua/thuê mẫu không gian 3D của chủ tiệc (`virtual_venue_orders`).
    - `MVP...` → Vé vào cổng `5.000đ` của khách tham dự (`virtual_access_passes`, kiểm tra chống trùng chéo `provider_transaction_id` trên toàn hệ thống).
    - **Mừng cưới:** Chuyển khoản trực tiếp P2P tới STK cô dâu chú rể qua `VietQrService`, không đi qua SePay của Mây Hẹn.

### 2.2. Chốt chặn Lưu trữ File 3D & Bảo vệ Tốc độ Website Hiện Tại
- **Phát hiện quan trọng từ mã nguồn:**
  1. `MediaLibraryService::detectOrphanedFiles()` (`app/Services/MediaLibraryService.php:269-292`) quét `storage/app/public/` và sẽ **xóa vĩnh viễn** các tệp không nằm trong `client/musics/`, `client/images/`, `admin/` khi Admin bấm dọn tệp mồ côi!
  2. Các trang công khai (`/`, `/dich-vu`, `/bang-gia`, `/tao-thiep`, `/docs`) đang tuân thủ ngân sách **Zero DB Queries** và Core Web Vitals nghiêm ngặt (`LCP < 2.5s`), không dùng CDN ngoài.
- **Quyết định kiến trúc bắt buộc (Áp dụng ngay từ W02–W03):**
  - **Tệp nguồn thiết kế (`.blend`, scripts dựng hình, textures gốc):** Lưu hoàn toàn ngoài `public/` tại `D:\Project\Laravel\mayhen.vn\Blender\venues\{venue-slug}\v{n}\` để bảo mật tài sản gốc và không phát tán qua HTTP.
  - **Tệp phân phối Web (`.glb` nén tối ưu & runtime Three.js nội bộ):** Đặt cô lập tại `public/assets/virtual-venues/{venue-slug}/v{n}/` và `public/assets/virtual-venues/vendor/` (hoàn toàn tách rời `public/assets/client/`).
    - Không bị `MediaLibraryService::purgeOrphans()` xóa nhầm.
    - Không tính lẹm vào hạn mức dung lượng ảnh cưới của cặp đôi (`WeddingStorageService`).
    - Đi qua nhánh whitelist `assets*` của `SeoRedirectMiddleware` (`0` truy vấn DB) và chỉ tải động (`dynamic import`) khi người dùng thực sự mở trang không gian cưới 3D.

### 2.3. Chốt chặn Ngoại lệ Thư viện 3D (`Three.js` Local Self-Hosted)
- Theo quy chuẩn Mây Hẹn (`AGENTS.md` & Phần II.2.1 Master Prompt):
  - **Giữ nguyên 100%** kiến trúc Laravel Blade + Vanilla CSS (prefix `.mayhen_vworld_*`) + Native ES Modules cho toàn bộ giao diện bao quanh thế giới 3D. Tuyệt đối không cài React, Vue, Tailwind hay Bootstrap.
  - **Đánh giá ngoại lệ có kiểm soát cho `Three.js`:**
    - *Lý do:* Quản lý WebGL2 scene graph, PBR Principled BSDF/glTF 2.0, skinned/hierarchical animation, frustum culling, shadow map và `VideoTexture` cho màn hình LED trên đa trình duyệt.
    - *Phiên bản & Giấy phép:* `three` `v0.170.0` (Giấy phép **MIT** — mã nguồn mở thương mại tự do, không copyleft).
    - *Bảo mật & Tự chủ Local:* Lưu trữ tĩnh trực tiếp `three.module.min.js` và `GLTFLoader.js` tại `public/assets/virtual-venues/vendor/` (**0 kết nối CDN bên ngoài**, không rò rỉ IP khách mời sang `unpkg`/`jsdelivr`, miễn nhiễm sự cố đứt cáp CDN).

### 2.4. Chốt chặn Header Bảo mật Camera/Micro & Realtime
- `app/Http/Middleware/SecurityHeaders.php` hiện đặt `Permissions-Policy: camera=(self), microphone=(), geolocation=()`.
  - Với khách tham dự xem đám cưới 3D: giữ nguyên `camera=(), microphone=()` để bảo vệ quyền riêng tư tuyệt đối (khách không bao giờ bị bật cam/mic).
  - Ở `W08` (Studio phát Livestream của chủ tiệc): chỉ mở `camera=(self), microphone=(self)` có điều kiện trên đúng route phát sóng đã xác thực chủ tiệc.
- Hệ thống hiện tại chưa cài WebSocket server độc lập (`config/broadcasting.php` chưa có). Vì vậy lộ trình tuân thủ đúng Phần X: không nhồi tọa độ 3D vào MySQL hay biến PHP-FPM thành game server; chuẩn bị hợp đồng Gateway phân phòng (`wedding:{id}:zone:{z}`) cho `W09`.
