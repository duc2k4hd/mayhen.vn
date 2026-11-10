# 🐛 ADMIN BUG & DEFECT REGISTER
## SỔ ĐĂNG KÝ VÀ THEO DÕI LỖ HỔNG HỆ THỐNG QUẢN TRỊ ADMIN MÂY HẸN (MAYHEN.VN)

> **Mục tiêu**: Ghi nhận toàn bộ các lỗi phát hiện qua kiểm toán mã nguồn thực tế, phân loại mức độ nghiêm trọng (P0/P1/P2), nguyên nhân gốc rễ và kế hoạch khắc phục có kiểm thử hồi quy.

---

## BẢNG TỔNG HỢP CÁC LỖ HỔNG ĐƯỢC XÁC MINH TRONG CODE

| Bug ID | Mức độ | Phân hệ | Tên lỗi & Mô tả | Trạng thái | File liên quan |
|---|:---:|:---:|---|:---:|---|
| `BUG-ADM-001` | **P1** | A01 / A05 | Staff role bị chặn đăng nhập tại màn hình `/admin/login` | **VERIFIED_RESOLVED** | `app/Http/Controllers/Admin/AuthController.php` |
| `BUG-ADM-002` | **P0** | A05 | Thiếu kiểm tra bảo vệ tài khoản Super Admin duy nhất khi đổi vai trò | **VERIFIED_RESOLVED** | `app/Http/Controllers/Admin/UserController.php` |
| `BUG-ADM-003` | **P0** | A09 | Duyệt đơn hàng thủ công thiếu chứng từ đối soát ngân hàng & lý do | **VERIFIED_RESOLVED** | `app/Http/Controllers/Admin/OrderController.php`, `resources/views/admin/pages/orders/show.blade.php` |
| `BUG-ADM-004` | **P1** | A25 | `AuditLogger` chưa tự động che (mask) secrets trong mảng details & thiếu log Auth | **VERIFIED_RESOLVED** | `app/Services/AuditLogger.php`, `app/Http/Controllers/Admin/AuthController.php` |

---

## CHI TIẾT TỪNG LỖ HỔNG

### 1. `BUG-ADM-001` [P1] — Staff role bị chặn đăng nhập tại màn hình `/admin/login`
- **Phân loại**: Authorization & Authentication Logic Inconsistency
- **Hiện tượng**:
  Tài khoản nhân viên nội bộ có vai trò `role = 'staff'` được hệ thống quy định là có quyền truy cập Admin để xem báo cáo (read-only), và middleware `AdminAuthenticate` cho phép thông qua `canAccessAdminPanel()`. Tuy nhiên, tại `AuthController@login`, hệ thống kiểm tra `! $user->isAdmin()`. Vì `$user->isAdmin()` chỉ trả về true cho `admin` và `manager`, nên tài khoản `staff` bị đăng xuất ngay lập tức và trả về lỗi: *"Tài khoản của bạn không có quyền truy cập không gian quản trị"*.
- **Nguyên nhân gốc rễ**:
  `AuthController.php` sử dụng phương thức `isAdmin()` thay vì `canAccessAdminPanel()`.
- **Giải pháp**:
  Cập nhật `AuthController.php` tại các hàm `index()`, `showLoginForm()` và `login()` sang `$user->canAccessAdminPanel()`.

---

### 2. `BUG-ADM-002` [P0] — Thiếu kiểm tra bảo vệ tài khoản Super Admin duy nhất khi đổi vai trò
- **Phân loại**: Privilege Escalation / Administrative Lockout Risk (`ADM-A05-009`)
- **Hiện tượng**:
  Tại `UserController@changeRole`, logic chỉ chặn tài khoản tự hạ quyền của chính mình (`$user->id === Auth::id()`). Trong trường hợp có nhiều hơn 1 quản trị viên hoặc quản trị viên thao tác nhầm trên tài khoản super admin còn lại duy nhất, hệ thống có thể rơi vào tình trạng không còn bất kỳ tài khoản super admin nào để quản trị hệ thống.
- **Nguyên nhân gốc rễ**:
  Chưa kiểm tra số lượng Super Admin còn lại trong database (`User::where('role', 'admin')->count()`).
- **Giải pháp**:
  Bổ sung điều kiện phòng vệ: Nếu tài khoản đang đổi quyền có `role === 'admin'` và vai trò mới `!== 'admin'`, nếu tổng số super admin trong hệ thống `<= 1` thì lập tức từ chối và báo lỗi rõ ràng.

---

### 3. `BUG-ADM-003` [P0] — Duyệt đơn hàng thủ công thiếu chứng từ đối soát ngân hàng & lý do
- **Phân loại**: Financial Integrity & Reconciliation Risk (`ADM-A09-020`, `ADM-A09-021`)
- **Hiện tượng**:
  Hàm `OrderController@approve` cho phép duyệt đơn hàng đang chờ thanh toán (`status = 'pending'`) thành đã thanh toán (`status = 'paid'`) chỉ bằng 1 nút bấm POST mà không yêu cầu nhập thông tin tham chiếu giao dịch ngân hàng (mã giao dịch, số tài khoản nguồn, giấy báo có) và lý do duyệt. Điều này vi phạm nghiêm trọng chuẩn kiểm toán tài chính Mây Hẹn Master Prompt.
- **Nguyên nhân gốc rễ**:
  `approve()` không validate FormRequest chứa `reference_code` và `reconciliation_note`.
- **Giải pháp**:
  Bổ sung validation bắt buộc: `reference_code` (mã tham chiếu ngân hàng tối thiểu 4 ký tự) và `reconciliation_note` (lý do đối soát); lưu các thông tin này vào metadata đơn hàng và ghi vào audit trail.

---

### 4. `BUG-ADM-004` [P1] — `AuditLogger` chưa tự động che (mask) secrets trong mảng details & thiếu log Auth
- **Phân loại**: Audit Trail Security & PII Protection (`ADM-A25-004`, `ADM-A01-018`)
- **Hiện tượng**:
  1. `AuditLogger::log` nhận mảng `$details` và ghi thẳng vào tệp `storage/logs/admin_audit.jsonl` mà không qua bước khử độc các trường nhạy cảm (`password`, `secret`, `token`, `bank_account`, `full_card`, `api_key`).
  2. Chưa có log ghi nhận sự kiện đăng nhập thành công (`admin_login`), đăng nhập thất bại và đăng xuất (`admin_logout`).
- **Nguyên nhân gốc rễ**:
  Thiếu hàm `sanitizeDetails()` trong `AuditLogger` và thiếu lời gọi log trong `AuthController`.
- **Giải pháp**:
  Tạo hàm đệ quy làm sạch các key nhạy cảm trong `AuditLogger` và bổ sung ghi log đăng nhập/đăng xuất trong `AuthController`.

