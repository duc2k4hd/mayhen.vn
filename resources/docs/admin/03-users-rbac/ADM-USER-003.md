---
id: ADM-USER-003
title: "Phân quyền vai trò nhân viên và phòng ngừa lỗi tự tước quyền Super Admin"
category: "Quản lý Tài khoản Khách & RBAC"
summary: "Hướng dẫn cấu hình ma trận phân quyền 6 vai trò vận hành, quản lý 9 nhóm quyền hành động (Action-Level RBAC) và cơ chế phòng vệ chống tự hạ quyền hoặc tước quyền Super Admin duy nhất."
reader: "PER-ADM-01 / PER-ADM-03"
order: 3
updated_at: "2026-11-15"
tags: [rbac, roles, permissions, super-admin, self-demotion-defense, staff-management]
mapped_page: "ADM-RBAC-PAGE"
mapped_route: "admin.rbac.index"
risk_level: "CRITICAL"
required_role: "admin.super"
---

# Phân quyền vai trò nhân viên và phòng ngừa lỗi tự tước quyền Super Admin

> [!WARNING]
> **Phân quyền thực hiện:** Chỉ dành riêng cho Quản trị viên Cấp cao (`admin.super`).  
> **Route quản trị:** `GET /admin/rbac` (`admin.rbac.index`) và `/admin/rbac/roles`.  
> **Nguyên tắc an ninh tối cao:** Tuân thủ Mục 2.1, 2.2 và Mục 67 PRD V3 về Kiểm soát Truy cập Dựa trên Vai trò (Role-Based Access Control - RBAC).


![Giao diện quản lý Phân quyền vai trò nhân viên và phòng ngừa lỗi tự tước quyền Super Admin](/assets/client/docs/images/admin/03-users-rbac/adm-user-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Cấu Trúc 6 Vai Trò Vận Hành Nội Bộ

Để đảm bảo nguyên tắc đặc quyền tối thiểu (Principle of Least Privilege), Mây Hẹn phân tách quyền hạn nội bộ thành 6 vai trò chức năng riêng biệt:

| Mã Vai Trò | Tên Vai Trò & Huy Hiệu | Mô Tả Trách Nhiệm Nghiệp Vụ | Phân Nhóm Quyền Hạn |
| :--- | :--- | :--- | :--- |
| `admin` | **Super Admin** (Hồng sẫm `#be185d`) | Toàn quyền cấu hình nền tảng, quản lý ma trận RBAC, sao lưu cơ sở dữ liệu và duyệt các tác vụ nhạy cảm P0. | **Toàn bộ 9 nhóm quyền** (Không thể bị tước quyền). |
| `manager` | **Quản Lý Vận Hành** (Xanh dương `#1d4ed8`) | Giám sát toàn bộ tiệc cưới, kiểm duyệt nội dung, xử lý khiếu nại, đóng vai hỗ trợ khách hàng. | Quản lý dự án, Khách mời, Hỗ trợ, Mẫu thiệp. |
| `designer` | **Chuyên Viên Mẫu Thiệp** (Tím `#7c3aed`) | Tạo mới mẫu thiệp cưới, chỉnh sửa mã nguồn HTML/CSS/JS, biên tập asset và cấu hình schema Studio Editor. | Xem/Sửa/Xuất bản Kho mẫu thiệp cưới. |
| `finance` | **Kế Toán & Tài Chính** (Xanh lá `#047857`) | Quản lý đơn hàng, đối soát giao dịch ngân hàng VietQR & SePay, xuất hóa đơn VAT, xét duyệt hoàn tiền. | Tài chính, Doanh thu, Cổng thanh toán, Đối soát. |
| `support` | **Chăm Sóc Khách Hàng** (Vàng hổ phách `#b45309`) | Tiếp nhận ticket, trả lời thắc mắc của cô dâu chú rể, chẩn đoán lỗi gửi thiệp và đóng vai hỗ trợ có kiểm toán. | Hỗ trợ, Khách mời, Phản hồi RSVP, Impersonate. |
| `staff` | **Nhân Viên Sự Kiện / Lễ Tân** (Xám đá `#475569`) | Đón khách tại sảnh tiệc cưới, vận hành trạm quét QR điểm danh, kiểm tra chỗ ngồi và điều phối sơ đồ bàn. | Tra cứu khách mời & Quét QR Check-in. |

---

## 2. Cây 9 Nhóm Quyền Hành Động (Action-Level Permissions)

Hệ thống quản lý chi tiết 32 quyền hành động cụ thể thuộc 9 nhóm:
1. `templates`: `templates.view`, `templates.create`, `templates.edit`, `templates.publish`, `templates.archive`.
2. `weddings`: `weddings.view`, `weddings.edit`, `weddings.publish`, `weddings.delete`.
3. `guests`: `guests.view`, `guests.import`, `guests.export`, `guests.delete`.
4. `rsvp`: `rsvp.view`, `rsvp.moderate`, `rsvp.export`.
5. `finance`: `finance.view`, `finance.refund`, `finance.reconcile`.
6. `settings`: `settings.view`, `settings.edit`, `settings.billing`.
7. `support`: `support.view`, `support.access_sensitive`, `support.impersonate`.
8. `audit`: `audit.view`, `audit.export`.
9. `security`: `security.view`, `security.manage_ip`, `security.backup`.

---

## 3. Cơ Chế Phòng Ngừa Lỗi Tự Tước Quyền (Self-Demotion Defense)

Một trong những lỗi vận hành nguy hiểm nhất trong quản trị hệ thống là quản trị viên vô tình hạ quyền của chính mình hoặc tước quyền của Super Admin duy nhất, dẫn đến hệ thống bị "mồ côi" không còn ai có quyền quản trị cấp cao.

`AdminRbacService@updateStaffRole` tích hợp hàng rào phòng thủ bất biến:

```
┌────────────────────────────────────────────────────────────────────────┐
│ KIỂM TRA ĐIỀU KIỆN AN TOÀN TRƯỚC KHI THAY ĐỔI VAI TRÒ                 │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Xác định vai trò hiện tại của nhân sự cần cập nhật                  │
│ 2. Nếu vai trò hiện tại là "admin" VÀ vai trò mới KHÁC "admin":        │
│    - Đếm số lượng Super Admin còn hoạt động: adminCount = ?            │
│    - NẾU adminCount <= 1:                                              │
│      => CHẶN LẬP TỨC (Ném DomainException)                            │
│      => Thông báo: "Không thể hạ quyền Quản trị viên cấp cao duy nhất │
│                    đang hoạt động của hệ thống."                       │
│ 3. NẾU người thao tác tự thay đổi vai trò của chính mình:              │
│    => Bắt buộc có sự xác nhận của Super Admin thứ hai                  │
└────────────────────────────────────────────────────────────────────────┘
```

Nhờ cơ chế này, hệ thống luôn đảm bảo có ít nhất **01 Super Admin đang hoạt động (`is_locked = false`)**, loại bỏ hoàn toàn nguy cơ bị khóa cửa ngoài (Lockout Risk).

---

## 4. Hướng Dẫn Điều Chỉnh Ma Trận Phân Quyền (RBAC Matrix)

1. Truy cập màn hình quản trị ma trận quyền tại `/admin/rbac/matrix`.
2. Giao diện hiển thị bảng lưới 2 chiều:
   - Các cột: 5 vai trò vận hành (`manager`, `designer`, `finance`, `support`, `staff`). (Cột `admin` luôn hiển thị dấu tích xanh cố định và bị khóa chỉnh sửa).
   - Các dòng: 9 nhóm quyền hành động.
3. Tích chọn hoặc bỏ tích các quyền muốn cấp cho từng vai trò.
4. Nhấp **[Lưu thay đổi ma trận phân quyền]** (`POST /admin/rbac/matrix`).
5. Cấu hình mới được lưu an toàn vào `PlatformSetting` (nhóm `security`, khóa `rbac_role_permissions_matrix`), tăng chỉ số `version` và ghi nhận lịch sử kiểm toán `RBAC_MATRIX_UPDATED`.

---

## 5. Quy Trình Cấp Quyền Hoặc Điều Chuyển Nhân Sự

Khi có nhân sự mới gia nhập hoặc luân chuyển bộ phận:
1. Truy cập danh sách nhân sự tại `/admin/rbac/staff`.
2. Nhấp nút **[+ Thêm nhân sự mới]** (`/admin/rbac/staff/create`).
3. Điền Họ tên, Email, Số điện thoại và chọn vai trò ban đầu phù hợp.
4. Trường hợp điều chuyển vai trò: Mở trang chỉnh sửa nhân sự, chọn vai trò mới từ danh sách thả xuống và nhấp **[Cập nhật vai trò]**.
5. Quyền hạn mới có hiệu lực ngay lập tức trong phiên làm việc tiếp theo của nhân sự đó.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** cấp vai trò `admin` (Super Admin) cho nhân viên thời vụ, thực tập sinh hoặc đối tác bên ngoài.
2. **CẤM** chia sẻ tài khoản Super Admin dùng chung giữa nhiều người. Mỗi nhân sự bắt buộc sở hữu tài khoản định danh cá nhân độc lập.
3. **CẤM** can thiệp trực tiếp vào bảng cơ sở dữ liệu `users` để sửa cột `role` bỏ qua logic kiểm tra an toàn của `AdminRbacService`.

---

## 7. Bài viết Liên quan

- [ADM-USER-001: Tra cứu hồ sơ khách hàng toàn diện (Customer 360 View)](/admin/docs/users-rbac/ADM-USER-001)
- [ADM-USER-002: Quy trình khóa tài khoản vi phạm và mở khóa an toàn](/admin/docs/users-rbac/ADM-USER-002)
- [ADM-USER-005: Kiểm tra và trích xuất nhật ký hoạt động kiểm toán (Audit Log)](/admin/docs/users-rbac/ADM-USER-005)
