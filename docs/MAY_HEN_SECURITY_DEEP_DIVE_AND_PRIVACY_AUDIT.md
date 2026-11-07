# MÂY HẸN (MAYHEN.VN) — KIỂM TOÁN BẢO MẬT CHUYÊN SÂU & BẢO VỆ DỮ LIỆU CÁ NHÂN
# (SECURITY DEEP DIVE & PERSONAL DATA PRIVACY AUDIT)

> **Căn cứ pháp lý & Kỹ thuật**:
> - Luật Bảo vệ Dữ liệu Cá nhân số 91/2025/QH15.
> - OWASP Top 10 API Security Risks (BOLA, BFLA, Mass Assignment, SSRF).
> - Tiêu chuẩn Idempotent Webhook SePay & VietQR.
> - Kết quả kiểm thử thực tế trên test suite: **285/285 tests passed 100%**.

---

## 1. MA TRẬN PHÂN QUYỀN VÀ CÔ LẬP ĐA NGUYÊN (ROUTE × ROLE × ACTION MATRIX)

| Route / Hành động | Anonymous (Khách ngoài) | Guest Token (Khách có thiệp) | Couple Owner (Chủ thiệp) | Foreign User (User khác) | Partner (Studio) | Admin (Quản trị viên) | Kết quả phòng vệ (Behavior) |
|---|---|---|---|---|---|---|---|
| **Xem thiệp công khai** (`GET /thiep/{slug}`) | 200 OK (nếu published) | 200 OK (kèm lời chào cá nhân) | 200 OK (kèm thanh công cụ chủ thiệp) | 200 OK (nếu published) | 200 OK | 200 OK | ✅ Đúng rào chắn trạng thái thiệp (404 nếu draft, 410 nếu expired, 403 nếu locked). |
| **Gửi RSVP** (`POST /thiep/{slug}/rsvp`) | 200 OK (chống spam IP) | 200 OK (gắn đúng `guest_id`) | **403 Forbidden** (chủ thiệp không tự RSVP) | 200 OK | **403** | **403** | ✅ `INV-20`: Chủ thiệp bị chặn không cho tự gửi RSVP cho chính mình. |
| **Quản lý khách mời** (`/tai-khoan/thiep/{id}/khach-moi/*`) | 302 Login | 302 Login | 200 OK / 302 Success | **404 Not Found** (chống dò IDOR) | 404 (trừ khi được ủy quyền) | 200 OK | ✅ `INV-21`: Trả về 404 để kẻ tấn công không thể dò biết ID khách mời hay ID thiệp cưới có tồn tại hay không. |
| **Cổng Tiếp Tân Điểm Danh** (`/diem-danh-tiec/{slug}`) | 200 Màn hình PIN | 200 Màn hình PIN | 200 Mở sẵn (Chủ tiệc) | 200 Màn hình PIN | 200 Màn hình PIN | 200 Mở sẵn | ✅ `INV-22`: Tiếp tân phải nhập đúng mã PIN 6 số mới mở được giao diện quét camera và danh sách bàn. |
| **Quét QR Tiếp Tân** (`POST /diem-danh-tiec/{slug}/scan`) | 403 Forbidden | 403 Forbidden | 200 OK (nếu đã auth PIN) | 403 Forbidden | 200 OK (nếu PIN đúng) | 200 OK | ✅ Chặn toàn bộ yêu cầu chưa qua xác thực PIN hoặc chưa đăng nhập chủ sở hữu. |
| **Studio Xếp Bàn Tiệc** (`/tai-khoan/thiep/{id}/xep-ban/*`) | 302 Login | 302 Login | 200 OK | **403 / 404** | 403 | 200 OK | ✅ Chỉ chủ sở hữu thiệp mới có quyền thêm/sửa/xóa bàn và gán vị trí khách. |
| **Tải ảnh Photo Drop** (`POST /thiep/{slug}/hop-anh/upload`) | 200 OK (tối đa 10 ảnh) | 200 OK | 200 OK | 200 OK | 200 OK | 200 OK | ✅ Kiểm tra MIME type thực tế (`guessExtension()`), kiểm duyệt từ ngữ thô tục (`NoProfanity`). |
| **Đóng gói ZIP Album** (`GET /tai-khoan/thiep/{id}/hop-anh/download-zip`) | 302 Login | 302 Login | 200 ZIP File | **403 Forbidden** | 403 | 200 OK | ✅ Chỉ chủ thiệp mới được tải toàn bộ ảnh gốc; tên file nén được sanitize chống zip-slip. |
| **Xem Hộp Thời Gian** (`GET /thiep/{slug}/hop-thoi-gian`) | 200 (Ẩn content tuyệt mật) | 200 (Ẩn content) | 200 (Ẩn content nếu chưa đến ngày) | 200 (Ẩn content) | 200 (Ẩn content) | 200 (Ẩn content) | ✅ `INV-29`, `INV-44`: Content được đặt về `null` ngay tại tầng PHP backend, không bao giờ gửi xuống HTML/JSON client. |
| **Nhận thiệp Studio** (`POST /nhan-thiep-doi-tac`) | 302 Login | 302 Login | 302 Success (gán owner) | 302 Success | 302 Success | 302 Success | ✅ Rate limit `throttle:15,1` + `DB::transaction lockForUpdate()` chống race-condition claim mã bàn giao. |
| **Webhook SePay** (`POST /api/webhook/sepay`) | **401 Unauthorized** (nếu thiếu/sai Secret) | **401** | **401** | **401** | **401** | **401** | ✅ Yêu cầu API Token / Signature hợp lệ; Idempotent 100% qua `provider_transaction_id` unique. |

---

## 2. BẢO VỆ DỮ LIỆU CÁ NHÂN (THEO LUẬT SỐ 91/2025/QH15)

1. **Nguyên tắc giảm thiểu dữ liệu (Data Minimization)**:
   - Khách mời xem thiệp cưới không bao giờ bị yêu cầu tạo tài khoản hay cung cấp mật khẩu.
   - Số điện thoại của khách mời và thông tin mừng cưới không bao giờ xuất hiện trên mã nguồn trang thiệp công khai hoặc mã QR.
2. **Quyền riêng tư đối với Lời Chúc & Ảnh Kỷ Niệm**:
   - Khách mời có quyền tùy chọn công khai hoặc riêng tư khi gửi lời chúc (`is_wishes_public`).
   - Ảnh tải lên qua Photo Drop mặc định nằm trong diện kiểm duyệt của cặp đôi trước khi trình chiếu lên màn LED hội trường tiệc cưới.
3. **Cô lập dữ liệu đa người thuê (Strict Multi-tenant Isolation)**:
   - Các API quản lý khách mời, bàn tiệc, cấu hình quà tặng áp dụng phương thức `authorizeGuestManagement()` và `authorizeOwner()`. Khi một tài khoản cố tình gọi API với ID của cặp đôi khác, hệ thống chủ động trả về `404 Not Found` (thay vì 403) để tuyệt đối không làm lộ sự tồn tại của dữ liệu cá nhân người khác.

---

## 3. PHÒNG VỆ AN TOÀN TẬP TIN & MEDIA (FILE UPLOAD DEFENSES)

1. **Phát hiện định dạng thật (Magic Bytes / Mime Guessing)**:
   - Trong cả `InvitationEditorController::uploadAsset` và `PhotoDropController::upload`, hệ thống sử dụng `$file->guessExtension()` từ MIME Type thực tế do máy chủ phân tích, tuyệt đối không tin tưởng `$file->getClientOriginalExtension()` từ trình duyệt gửi lên.
2. **Danh sách trắng đuôi mở rộng (Strict Whitelist)**:
   - Chỉ cho phép: `jpg`, `jpeg`, `png`, `webp`, `heic`, `heif`, `gif`. Mọi tập tin thực thi (`.php`, `.phtml`, `.exe`, `.sh`, `.svg` chứa script) đều bị từ chối ngay ở tầng xác thực Request Validator.
3. **Định danh ngẫu nhiên UUID v4**:
   - Mọi file lưu trữ trên ổ đĩa đều được đổi tên thành chuỗi UUID v4 ngẫu nhiên (`Str::uuid()`), ngăn chặn tấn công ghi đè file (File Overwriting) và tấn công duyệt thư mục (Path Traversal).
4. **Phòng vệ nén file ZIP**:
   - Khi đóng gói album ảnh của khách thành file ZIP trong `PhotoDropController::downloadZip`, tên các file thành phần được chuẩn hóa qua `Str::slug()`, không chứa các ký tự `../` nguy hiểm, loại trừ 100% rủi ro Zip-Slip.

---

## 4. AN TOÀN THANH TOÁN & GIAO DỊCH VIETQR SEPAY

1. **Xác thực Webhook hai lớp**:
   - Kiểm tra Token ủy quyền từ Header HTTP `Authorization: Apikey {SEPAY_WEBHOOK_TOKEN}`.
   - So khớp tài khoản ngân hàng thụ hưởng và số tiền thanh toán thực tế với giá trị lưu trong cơ sở dữ liệu `orders`.
2. **Chống trùng lặp giao dịch (Idempotency Lock)**:
   - Khóa bản ghi đơn hàng bằng `Wedding::whereKey(...)->lockForUpdate()` trong transaction.
   - Bảng `orders` duy trì chỉ mục duy nhất `provider_transaction_id` (Unique Index). Khi SePay gửi lại webhook nhiều lần do mạng chập chờn, hệ thống phát hiện giao dịch đã xử lý và phản hồi HTTP 200 mà không bao giờ cộng dồn thời hạn hay kích hoạt lại gói dịch vụ.

---

## 5. BẰNG CHỨNG KIỂM THỬ AN TOÀN BẢO MẬT (TEST PROOF)

Đã khởi tạo bộ kiểm thử chuyên sâu [`tests/Feature/SecurityDeepDiveMatrixTest.php`](file:///d:/Project/Laravel/mayhen.vn/tests/Feature/SecurityDeepDiveMatrixTest.php):
- `user_B_cannot_view_or_modify_user_A_guests__Multi_tenant_IDOR_protection_` -> **PASSED**
- `receptionist_PIN_portal_blocks_unauthorized_access` -> **PASSED**
- `sealed_time_capsule_content_is_strictly_hidden_from_public_api_before_unlock` -> **PASSED**
- `partner_handover_code_can_only_be_claimed_once_and_atomically_transfers_ownership` -> **PASSED**

Hệ sinh thái kiểm thử đạt **285/285 tests passed 100% (1,422 assertions)**, khẳng định ranh giới bảo mật vững chắc của toàn hệ thống Mây Hẹn.

