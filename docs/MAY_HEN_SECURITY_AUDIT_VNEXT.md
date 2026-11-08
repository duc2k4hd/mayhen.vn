# MÂY HẸN (MAYHEN.VN) — BÁO CÁO KIỂM TOÁN BẢO MẬT CHUYÊN SÂU (SECURITY AUDIT VNEXT)

> **Tuân thủ chuẩn mực:** OWASP ASVS 4.0, OWASP Top 10:2021, API Security Top 10.  
> **Phạm vi kiểm toán:** Toàn bộ Controller, Request, Middleware, Policy, Model, Webhook và View.  
> **Thời điểm xác lập:** 07/11/2026.

---

## 1. MÔ HÌNH NGUY CƠ & RANH GIỚI TIN CẬY (THREAT MODEL)

1. **Tài sản cần bảo vệ tối cao (Crown Jewels):**
   - Thông tin cá nhân cô dâu chú rể, cha mẹ hai bên, số điện thoại, địa chỉ tư gia (Luật 91/2025/QH15).
   - Danh sách khách mời, mối quan hệ, số điện thoại, số lượng người tham dự, ghi chú chế độ ăn uống.
   - Sổ mừng cưới, số tiền mừng, phương thức nhận tiền và lịch sử tài chính của đám cưới.
   - Giao dịch thanh toán SePay / VietQR, tránh giả mạo webhook hoặc kích hoạt gói cước trái phép.
   - Mã token cá nhân hóa của khách mời (`guest_token`), ngăn chặn việc khách mạo danh nhau hoặc truy cập chéo.
2. **Các tác nhân đe dọa (Threat Actors):**
   - Kẻ tấn công ẩn danh trên Internet (quét IDOR, brute-force form, inject XSS, spam RSVP/lời chúc).
   - Khách mời ác ý (thay đổi guest token, spam link cá nhân, khai thác DOM postMessage).
   - Khách hàng trả phí khác (thử đổi `guest_id`, `invitation_id`, `wedding_id` để đọc dữ liệu cặp đôi khác).
   - Kẻ gian lận thanh toán (thử phát lại webhook SePay, gửi thiếu tiền hoặc giả mạo chữ ký).

---

## 2. BẢNG TỔNG HỢP LỖ HỔNG BẢO MẬT XÁC NHẬN (VULNERABILITY REGISTER)

| Mã lỗi | Mức độ | Lớp ảnh hưởng | Tên lỗ hổng & Hành vi | Tác động thực tế (Impact) | Trạng thái |
|---|---|---|---|---|---|
| **SEC-PUB-01** | **CRITICAL** | `Client\InvitationEditorController`, `Client\GuestController` | **Thiệp chưa xuất bản, hết hạn hoặc bị khóa vẫn mở công khai và nhận tương tác** | Người ngoài xem được bản nháp chưa công bố của cặp đôi; thiệp bị Admin khóa vẫn tiếp tục nhận RSVP và lời chúc. | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-PAY-01** | **CRITICAL** | `Client\CheckoutController`, Migration `orders` | **Thiếu Provider Transaction ID & Unique Constraint trong đơn hàng** | Nguy cơ dùng 1 giao dịch ngân hàng để đối soát hoặc kích hoạt nhiều đơn; thiếu dấu vết giao dịch SePay. | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-IDOR-01** | **HIGH** | `Client\GiftManagementController@store`, `@update` | **Lỗ hổng Tenant Leak: Gán `guest_id` thuộc đám cưới khác vào Sổ Mừng Cưới** | Cặp đôi A có thể liên kết và xem thông tin khách mời của cặp đôi B thông qua việc gửi `guest_id` tùy ý. | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-AUTH-02** | **HIGH** | `Client\AccountController@togglePublish` | **Bỏ qua trạng thái Khóa của Admin khi Bật lại Xuất bản** | Cặp đôi có thiệp bị Admin khóa vi phạm vẫn có thể tự ý mở lại xuất bản thông qua endpoint `/xuat-ban`. | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-UPL-01** | **HIGH** | `Client\InvitationEditorController@uploadAsset` | **Upload tin cậy Extension Client gửi lên (`getClientOriginalExtension`)** | Tiềm ẩn rủi ro lưu file với extension không mong muốn nếu header bị giả mạo; rate limit quá rộng (120 req/phút). | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-DOM-01** | **MEDIUM** | `resources/views/.../tron-kiep-nay-ben-nhau/script.js` | **PostMessage listener không kiểm tra `event.origin`** | Bất kỳ trang web nào nhúng iframe đều có thể gửi message giả mạo để can thiệp DOM thiệp cưới. | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-LOK-02** | **HIGH** | `Client\MemoryVaultController`, `LiveWallController`, `PhotoDropController`, `CheckInController` | **Lỗ hổng cổng phụ: Truy cập thiệp bị khóa qua đường dẫn thứ cấp** | Thiệp bị Admin khóa nhưng người ngoài vẫn có thể xem ảnh, trình chiếu, tải ảnh, check-in và tải lịch ICS qua các cổng phụ. | **ĐÃ XỬ LÝ (RESOLVED)** |
| **SEC-CSV-01** | **HIGH** | `Client\WishManagementController`, `GuestController`, `GiftManagementController`, các Admin Controllers | **Lỗ hổng CSV Formula Injection (CWE-1236)** | Kẻ xấu nhập ký tự công thức (`=`, `+`, `-`, `@`) vào tên/lời chúc để tấn công máy trạm người dùng khi mở tệp xuất CSV bằng Excel/Sheets. | **ĐÃ XỬ LÝ (RESOLVED)** |

---

## 3. CHI TIẾT TỪNG LỖ HỔNG & PHƯƠNG ÁN KHẮC PHỤC TRIỆT ĐỂ

### 3.1. [SEC-PUB-01] Bảo Vệ Nghiêm Ngặt Trạng Thái Thiệp Công Khai (Draft, Expired, Locked)
- **Điểm yếu**: `InvitationEditorController::show`, `GuestController::submitRsvp`, `submitWish`, `getWishes`, `getRsvpResponses` chỉ tìm kiếm thiệp theo `slug` mà không kiểm tra:
  1. Thiệp bị khóa (`custom_config->is_locked = true`): Phải chặn hoàn toàn mọi hiển thị và API tương tác.
  2. Thiệp chưa xuất bản (`is_published = false`): Nếu không phải chủ sở hữu (`! $invitation->isOwner()`), từ chối truy cập (trả về trang 404 hoặc thông báo bản nháp riêng tư).
  3. Thiệp đã hết hạn (`expires_at < now()`): Trả về màn hình thông báo thiệp cưới đã hết thời hạn lưu trữ.
- **Khắc phục**:
  - Tạo hàm dùng chung `checkInvitationPublicAccess(Invitation $invitation, bool $isOwner = false)` để áp dụng đồng bộ cho cả trang hiển thị thiệp lẫn 4 API tương tác khách mời.

### 3.2. [SEC-PAY-01] Bổ Sung `provider_transaction_id` & Unique Constraint Cho Bảng Đơn Hàng
- **Điểm yếu**: Bảng `orders` chỉ có `order_code` mà không lưu mã giao dịch từ SePay (`id` hoặc `referenceCode`), dẫn tới nguy cơ replay transaction hoặc không thể truy vết đối soát ngân hàng.
- **Khắc phục**:
  - Tạo migration bổ sung cột `provider_transaction_id` (nullable, string 64, unique index) vào bảng `orders`.
  - Trong `CheckoutController::handleWebhook`, trích xuất `$transactionId = $request->input('id')` từ SePay, kiểm tra nếu `provider_transaction_id` này đã được sử dụng cho đơn hàng khác thì từ chối ngay lập tức; khi thanh toán thành công, ghi nhận `$order->update(['provider_transaction_id' => $transactionId, 'status' => 'paid'])`.

### 3.3. [SEC-IDOR-01] Ngăn Chặn Rò Rỉ Tenant Khách Mời Trong Sổ Mừng Cưới
- **Điểm yếu**: Trong `GiftManagementController::store` và `update`, validation chỉ kiểm tra `'guest_id' => ['nullable', 'exists:guests,id']`.
- **Khắc phục**:
  - Đổi thành: `Rule::exists('guests', 'id')->where('wedding_id', $invitation->wedding_id)`.

### 3.4. [SEC-AUTH-02] Chặn Bỏ Qua Khóa Quản Trị Viên Khi Toggle Publish
- **Điểm yếu**: `AccountController::togglePublish` chỉ kiểm tra quyền sở hữu mà quên kiểm tra `custom_config->is_locked`.
- **Khắc phục**:
  - Kiểm tra nếu `$invitation->custom_config['is_locked'] == true` thì trả về lỗi 403: "Thiệp cưới của bạn hiện đang bị khóa bởi Quản trị viên. Vui lòng liên hệ hỗ trợ."

### 3.5. [SEC-UPL-01] Chuẩn Hóa Bảo Mật Tải Tệp Ảnh
- **Điểm yếu**: `InvitationEditorController::uploadAsset` dùng `$file->getClientOriginalExtension()`.
- **Khắc phục**:
  - Sử dụng `$ext = $file->guessExtension() ?? 'jpg'` kết hợp kiểm tra danh sách trắng nghiêm ngặt `['jpeg', 'png', 'jpg', 'webp', 'gif', 'heic']`.
  - Bổ sung Rate Limiter chuyên biệt `upload_actions` (tối đa 30 lần tải ảnh/phút).

### 3.6. [SEC-DOM-01] Phòng Vệ Origin Trong Script Mẫu Thiệp
- **Điểm yếu**: `tron-kiep-nay-ben-nhau/script.js:770` lắng nghe postMessage không kiểm tra `evt.origin`.
- **Khắc phục**:
  - Thêm `if (evt.origin !== window.location.origin) return;`.

### 3.7. [SEC-LOK-02] Đồng Bộ Rào Chắn Trạng Thái Khóa Xuyên Suốt Mọi Cổng Phụ
- **Điểm yếu**: `MemoryVaultController`, `LiveWallController`, `PhotoDropController`, `CheckInController` chỉ kiểm tra tồn tại của thiệp theo slug mà không kiểm tra cờ `custom_config['is_locked']`.
- **Khắc phục**:
  - Tự động kiểm tra cờ khóa, trả về view `client.pages.invitation-locked` cho toàn bộ các giao diện HTML và trả về JSON 403 cho các API tiếp nhận tải ảnh/quét check-in/feed trình chiếu.

### 3.8. [SEC-CSV-01] Vệ Sinh An Toàn Tệp Xuất CSV Chống Formula Injection (CWE-1236)
- **Điểm yếu**: Dữ liệu tên khách mời, lời chúc, yêu cầu ăn uống hoặc ghi chú tiền mừng được ghi trực tiếp vào `fputcsv()`, khiến Excel/Sheets tự động thực thi công thức nếu chuỗi bắt đầu bằng `=`, `+`, `-`, `@`.
- **Khắc phục**:
  - Xây dựng `CsvSanitizerService` tự động kiểm tra và thêm tiền tố nháy đơn `'` cho bất kỳ chuỗi công thức nguy hiểm nào, đồng thời giữ nguyên vẹn các trường số và chuỗi an toàn. Áp dụng cho 100% các hàm xuất CSV Client và Admin.


