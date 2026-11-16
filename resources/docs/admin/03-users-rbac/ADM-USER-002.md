---
id: ADM-USER-002
title: "Quy trình khóa tài khoản vi phạm, mở khóa và đặt lại mật khẩu an toàn"
category: "Quản lý Tài khoản Khách & RBAC"
summary: "Hướng dẫn quản trị viên thực hiện quy trình khóa tài khoản người dùng vi phạm điều khoản, mở khóa sau khi giải quyết tranh chấp và cấp phát lại mật khẩu an toàn kèm cơ chế phòng vệ chống tự khóa chính mình."
reader: "PER-ADM-01 / PER-ADM-03"
order: 2
updated_at: "2026-11-15"
tags: [users, lock-account, unlock, reset-password, security, audit-log, super-admin-defense]
mapped_page: "ADM-USER-PAGE"
mapped_route: "admin.users.index"
risk_level: "HIGH"
required_role: "manager"
---

# Quy trình khóa tài khoản vi phạm, mở khóa và đặt lại mật khẩu an toàn

> [!WARNING]
> **Phân quyền thực hiện:** Quản trị viên cấp Quản lý (`manager`) hoặc Quản trị viên Cấp cao (`admin.super`).  
> **Route thao tác:** `GET /admin/users` và chi tiết người dùng `/admin/users/{id}`.  
> **Cơ chế phòng vệ:** Hệ thống tự động chặn hành vi tự khóa tài khoản của chính mình và chặn khóa Super Admin cuối cùng đang hoạt động.


![Giao diện quản lý Quy trình khóa tài khoản vi phạm, mở khóa và đặt lại mật khẩu an toàn](/assets/client/docs/images/admin/03-users-rbac/adm-user-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục Đích Nghiệp Vụ & Căn Cứ Khóa Tài Khoản

Trong quá trình vận hành, nhân viên quản trị cần can thiệp tạm ngưng quyền truy cập của một tài khoản người dùng trong các trường hợp sau:
1. **Vi phạm tiêu chuẩn cộng đồng:** Tải lên hình ảnh cưới đồi trụy, vi phạm thuần phong mỹ tục hoặc spam quảng cáo rác trong nội dung thiệp cưới.
2. **Tranh chấp quyền sở hữu thiệp cưới:** Khi hai bên có tranh chấp bản quyền hoặc có yêu cầu bằng văn bản từ cơ quan chức năng.
3. **Nghi vấn bị chiếm quyền điều khiển (Account Takeover):** Phát hiện đăng nhập bất thường từ nhiều dải IP lạ trong thời gian ngắn hoặc có phản ánh từ chủ tài khoản qua hotline.
4. **Hỗ trợ khôi phục mật khẩu:** Khách hàng lớn tuổi quên mật khẩu, không thể tự nhận mã xác thực qua email và yêu cầu quản trị viên cấp lại mật khẩu tạm thời.

---

## 2. Quy Trình Khóa Tài Khoản (Lock Account SOP)

```
┌────────────────────────┐      ┌────────────────────────┐      ┌────────────────────────┐
│  Xác Định Lý Do Khóa   │ ──>  │  Nhập Form Xác Nhận    │ ──>  │   Lưu Vết Audit Log    │
│ (Vi phạm/Tranh chấp)   │      │ (Bắt buộc lý do rõ ràng)│      │  (is_locked = true)    │
└────────────────────────┘      └────────────────────────┘      └────────────────────────┘
```

1. **Bước 1:** Truy cập danh sách tài khoản tại `/admin/users`.
2. **Bước 2:** Sử dụng thanh tìm kiếm theo Email, Tên hoặc Số điện thoại để mở trang chi tiết tài khoản (`/admin/users/{id}`).
3. **Bước 3:** Tại mục *Hành động Tài khoản (Account Actions)*, nhấp nút màu đỏ **[Khóa tài khoản]** (`POST /admin/users/{id}/lock`).
4. **Bước 4:** Hộp thoại xác nhận yêu cầu nhập **Lý do khóa tài khoản** (Bắt buộc: Tối đa 255 ký tự, ghi rõ số Ticket hỗ trợ hoặc căn cứ vi phạm).
5. **Bước 5:** Nhấp **[Xác nhận Khóa]**.
6. **Hệ quả tức thời:**
   - Trường `is_locked` chuyển sang `true`, lưu vết `lock_reason` và `locked_at`.
   - Phiên đăng nhập hiện tại của người dùng bị hủy bỏ ngay lập tức (Force Logout).
   - Khi người dùng đăng nhập lại, hệ thống hiển thị thông báo lỗi màu đỏ: *"Tài khoản của bạn đã bị tạm khóa do: [Lý do]. Vui lòng liên hệ Hotline hỗ trợ để được giải quyết"*.
   - Mọi thiệp cưới thuộc tài khoản bị khóa sẽ chuyển sang chế độ không thể chỉnh sửa.

---

## 3. Cơ Chế Phòng Vệ An Toàn Cốt Lõi (Security Defenses)

Mã nguồn tại `UserController@lock` thiết lập 2 tầng phòng vệ nghiêm ngặt:
- **Tầng 1 - Chống tự sát hệ thống (Self-lock Defense):** Nếu quản trị viên vô tình hoặc cố ý chọn khóa tài khoản của chính mình (`$user->id === Auth::id()`), hệ thống trả về lỗi ngay lập tức: *"Bạn không thể tự khóa tài khoản của chính mình"*.
- **Tầng 2 - Bảo toàn Super Admin cuối cùng (Last Super Admin Defense):** Nếu tài khoản bị khóa là Super Admin:
  - Bắt buộc người thực hiện phải là Super Admin (vai trò `manager` bị chặn 403).
  - Hệ thống kiểm tra số lượng Super Admin còn hoạt động (`activeAdminCount`). Nếu chỉ còn **duy nhất 1 Super Admin**, lệnh khóa bị từ chối với thông báo: *"Không thể khóa Quản trị viên cấp cao duy nhất còn hoạt động trong hệ thống"*. Điều này ngăn ngừa hoàn toàn rủi ro hệ thống bị mất quyền quản trị tối cao.

---

## 4. Quy Trình Mở Khóa Tài Khoản (Unlock Account SOP)

Sau khi khách hàng đã xác minh danh tính, gỡ bỏ hình ảnh vi phạm hoặc giải quyết xong khiếu nại:
1. Truy cập `/admin/users?status=locked` để lọc danh sách các tài khoản đang bị khóa.
2. Mở tài khoản cần xử lý và nhấp nút màu xanh lá **[Mở khóa tài khoản]** (`POST /admin/users/{id}/unlock`).
3. Nếu tài khoản được mở khóa là Super Admin, người bấm mở khóa bắt buộc phải có vai trò Super Admin.
4. Trường `is_locked` được đưa về `false`, xóa sạch `lock_reason` và `locked_at`.
5. Người dùng có thể đăng nhập lại bình thường ngay lập tức. Hệ thống ghi nhận Audit Log `unlock_user`.

---

## 5. Quy Trình Đặt Lại Mật Khẩu An Toàn (Password Reset)

Khi người dùng yêu cầu cấp lại mật khẩu qua kênh hỗ trợ chính thức:
1. Xác minh tối thiểu 2 thông tin cá nhân: Họ tên, Số điện thoại và Mã thiệp cưới / Đơn hàng gần nhất.
2. Tại màn hình chi tiết người dùng, nhấp nút **[Đặt lại mật khẩu]** (`POST /admin/users/{id}/reset-password`).
3. Quản trị viên nhập mật khẩu tạm thời mới (Tối thiểu 8 ký tự, tuân thủ chuẩn mạnh) hoặc để hệ thống sinh chuỗi ngẫu nhiên.
4. Mật khẩu được mã hóa an toàn qua thuật toán `Hash::make()` trước khi lưu vào CSDL (tuyệt đối không lưu plain-text).
5. Gửi mật khẩu mới cho khách qua kênh liên lạc bảo mật (tin nhắn SMS hoặc email chính chủ) và yêu cầu khách đổi mật khẩu ngay lần đăng nhập đầu tiên.
6. Hệ thống ghi nhật ký kiểm toán `reset_user_password` lưu vết User ID người thực hiện.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** khóa tài khoản của khách hàng chỉ vì tranh chấp cá nhân mà không có căn cứ vi phạm quy chế hoặc yêu cầu của phụ mẫu/chủ tiệc.
2. **CẤM** đọc hoặc gửi mật khẩu của khách hàng qua các nhóm chat công khai không mã hóa.
3. **CẤM** mở khóa cho các tài khoản đang trong diện điều tra gian lận thanh toán hoặc lừa đảo chiếm đoạt tài sản tiệc cưới.

---

## 7. Bài viết Liên quan

- [ADM-USER-001: Tra cứu hồ sơ khách hàng toàn diện (Customer 360 View)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-001.md)
- [ADM-USER-003: Phân quyền vai trò nhân viên và phòng ngừa lỗi tự tước quyền Super Admin](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-003.md)
- [ADM-SUPP-001: Tiếp nhận và phân loại Ticket hỗ trợ khách hàng](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/08-support-messaging/ADM-SUPP-001.md)
