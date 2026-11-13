---
id: ADM-SOP-001
title: "Bàn giao hệ thống: Tổng quan kiến trúc Mây Hẹn, môi trường Staging/Production & Ma trận RBAC"
category: "Quy chuẩn Tiếp nhận & Vận hành (SOP)"
summary: "Quy trình vận hành chuẩn (SOP) tiếp nhận và bàn giao hệ thống Mây Hẹn: Tổng quan kiến trúc PHP 8.5/Laravel 13, cấu hình môi trường, phân quyền RBAC 3 cấp, bảo mật dữ liệu cá nhân theo Luật 91/2025/QH15 và cơ chế ghi vết kiểm toán Audit Log."
reader: "PER-ADM-01 / PER-ADM-03"
order: 11
updated_at: "2026-11-13"
tags: [mayhen, quy-chuẩn-tiếp-nhận-&-vận-hành-(sop), kiến-trúc-hệ-thống, rbac, audit-logs]
mapped_page: "ADM-SOP-PAGE"
mapped_route: "admin.dashboard"
risk_level: "CRITICAL"
required_role: "manager"
---

# Bàn giao hệ thống: Tổng quan kiến trúc Mây Hẹn, môi trường Staging/Production & Ma trận RBAC

> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`  
> **Mức độ rủi ro:** `CRITICAL` | **Phạm vi:** Toàn bộ hệ sinh thái quản trị `/admin/*`  
> **Bằng chứng kiểm toán:** Mọi thao tác bàn giao và cấp quyền đều lưu vết vĩnh viễn trong bảng `audit_logs`  

Tài liệu này là **Sổ tay Quy chuẩn Vận hành Tiêu chuẩn (SOP-01)** dành cho các kỹ sư phụ trách hạ tầng, Quản lý vận hành (Operations Manager) và nhân sự mới tiếp nhận quyền quản trị hệ thống Mây Hẹn (`mayhen.vn`). Việc nắm vững kiến trúc nền tảng và kỷ luật phân quyền là điều kiện bắt buộc nhằm bảo đảm hệ thống vận hành thông suốt 24/7 trong mọi mùa cưới.

---

## 1. Bản đồ kiến trúc kỹ thuật cốt lõi (Technical Architecture)

Hệ thống Mây Hẹn được xây dựng trên triết lý **Native High-Performance First** — tối ưu tốc độ trực tiếp từ mã nguồn, không lạm dụng cache để che giấu mã nguồn chậm:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   MÂY HẸN (MAYHEN.VN) NATIVE STACK                     │
├────────────────────────────────────────────────────────────────────────┤
│ • Backend Framework : PHP 8.5.10 / Laravel 13.35.0 (Strict Typing)     │
│ • Cơ sở dữ liệu     : MariaDB / MySQL 8.0 (Production), SQLite (Test)  │
│ • Frontend Engine   : Pure Blade Templates + Pure Vanilla CSS          │
│ • Scripting Engine  : Native Vanilla ES Modules (Zero External Libs)   │
│ • Thanh toán số     : SePay Webhook Auto-reconciliation (HMAC SHA-256) │
│ • Bản quyền & CSS   : Toàn bộ CSS có tiền tố bắt buộc: mayhen_*        │
└────────────────────────────────────────────────────────────────────────┘
```

### Các nguyên tắc bất biến (System Invariants)
1. **Zero External Frontend Frameworks:** Tuyệt đối không cài đặt hoặc nhúng Tailwind, Bootstrap, Vue, React, Alpine hay jQuery vào client. 100% giao diện dùng Native CSS Flexbox/Grid và JavaScript ES Modules thuần.
2. **100% Local Assets:** Toàn bộ ảnh nền, font chữ, hiệu ứng âm thanh và tài nguyên mẫu thiệp được lưu trữ cục bộ trong dự án; cấm tải từ các CDN rủi ro bên ngoài (`ladicdn.com`, `builder.27biggroup.com`).
3. **Atomic Financial Transactions:** Toàn bộ các thao tác liên quan đến tiền bạc, thanh toán SePay, nạp/rút hoa hồng đối tác và kích hoạt gói đều được bảo vệ bằng giao dịch nguyên tử `DB::transaction()` và khóa bi quan `lockForUpdate()`.

---

## 2. Ma trận phân quyền quản trị 3 cấp (RBAC Matrix)

Phân hệ quản trị Admin của Mây Hẹn phân tách thành 3 vai trò với ranh giới trách nhiệm rõ ràng:

| Vai trò (Role) | Ký hiệu | Thẩm quyền cho phép | Hành động bị cấm |
| :--- | :---: | :--- | :--- |
| **Nhân viên CSKH**<br>`staff` | 🛡️ L1 | • Tra cứu thông tin khách hàng (Customer 360)<br>• Hỗ trợ giải đáp Ticket CSKH<br>• Xem danh sách thiệp, tra cứu đơn hàng | • Không được hoàn tiền đơn hàng<br>• Không được phân quyền tài khoản<br>• Không được xuất bản mẫu thiệp mới<br>• Không được truy cập System Health |
| **Quản lý Vận hành**<br>`manager` | ⚡ L2 | • Toàn bộ quyền của Staff<br>• Kích hoạt đơn hàng thủ công khi có ủy nhiệm chi<br>• Phê duyệt hoàn tiền (Refund approval)<br>• Tạm khóa/mở khóa thiệp cưới vi phạm<br>• Kiểm duyệt mẫu thiệp lên Release Lab | • Không được xóa tài khoản người dùng<br>• Không được chỉnh sửa cấu hình hệ thống<br>• Không được can thiệp vào tiến trình sao lưu |
| **Quản trị viên Tối cao**<br>`super_admin` | 👑 L3 | • Toàn quyền kiểm soát hệ thống<br>• Cấu hình các gói cước và tỷ lệ hoa hồng<br>• Quản lý tài khoản Admin & phân quyền RBAC<br>• Quản trị bản sao lưu (Backup & Disaster Recovery)<br>• Thực hiện quyền lãng quên PII (Anonymize User) | Bắt buộc tuân thủ 2-Factor Authentication và không được chia sẻ tài khoản gốc. |

---

## 3. Quy trình tiếp nhận & Bàn giao tài khoản quản trị mới

Khi có nhân sự mới tham gia vào đội ngũ vận hành hoặc tiếp nhận ca trực:

### Bước 1: Khởi tạo tài khoản quản trị
1. Đăng nhập vào trang quản trị bằng tài khoản `super_admin`.
2. Truy cập menu **Người Dùng & Phân Quyền -> Danh Sách Quản Trị Viên** (`/admin/users`).
3. Nhấp nút **[+ Thêm Quản Trị Viên]**.
4. Nhập họ tên, email công vụ (ví dụ: `nv.nam@mayhen.vn`) và số điện thoại.
5. Gán vai trò (`staff` hoặc `manager`). Tuyệt đối không cấp quyền `super_admin` cho nhân sự thử việc hoặc cộng tác viên.

### Bước 2: Thiết lập mật khẩu và hướng dẫn quy chuẩn
1. Hệ thống tự động gửi liên kết kích hoạt an toàn về email công vụ.
2. Mật khẩu bắt buộc tối thiểu 10 ký tự (kết hợp chữ hoa, chữ thường, chữ số và ký tự đặc biệt).
3. Hướng dẫn nhân sự đọc kỹ các tài liệu SOP liên quan:
   - `ADM-USER-001`: Quy trình tra cứu hồ sơ khách hàng 360 & bảo mật PII.
   - `ADM-ORDR-002`: Quy trình đối soát thanh toán chuyển khoản SePay.
   - `ADM-COCK-002`: Sổ tay cứu hộ ngoại tuyến sảnh tiệc (Emergency Offline Pack).

### Bước 3: Thu hồi quyền khi nhân sự chuyển công tác
1. Ngay khi nhân sự bàn giao công việc, Quản trị viên truy cập vào hồ sơ nhân sự đó.
2. Nhấp nút **[Khóa Tài Khoản]** hoặc chuyển trạng thái sang `inactive`.
3. Toàn bộ phiên đăng nhập (Session) của tài khoản sẽ bị hủy ngay lập tức.
4. Hệ thống ghi nhật ký thu hồi quyền vào `audit_logs` để đối soát sau này.

---

## 4. Bảo vệ dữ liệu cá nhân (PII Compliance) & Luật 91/2025/QH15

> [!WARNING]
> **Quy định nghiêm ngặt về Dữ liệu Khách hàng & Khách mời:**  
> - Toàn bộ số điện thoại khách mời trong danh sách xuất Excel phải được che mờ (Masking) ở 3 số giữa (ví dụ: `091•••4756`) đối với tài khoản `staff`.  
> - Nghiêm cấm tải về danh sách khách mời hoặc sao chép thông tin cá nhân của các cặp đôi ra máy tính cá nhân ngoài mục đích hỗ trợ kỹ thuật.  
> - Khi khách hàng yêu cầu xóa dữ liệu theo **Quyền được lãng quên (Right to be Forgotten)**, Quản trị viên sử dụng chức năng `Anonymize User` trong Admin để ẩn danh hóa tên, email, SĐT nhưng vẫn bảo toàn chứng từ hóa đơn phục vụ kế toán thuế.

---

## 5. Danh bạ ứng cứu khẩn cấp (Incident Escalation Matrix)

Trong trường hợp phát sinh sự cố nghiêm trọng (mất kết nối cổng thanh toán, sảnh cưới báo lỗi diện rộng, hệ thống phản hồi chậm trên 2 giây):

1. **Cấp 1 (Lễ tân / Khách hàng báo sự cố):** Nhân viên `staff` tiếp nhận thông tin, ghi nhận mã thiệp và số điện thoại cặp đôi.
2. **Cấp 2 (Điều phối kỹ thuật):** Chuyển ngay cho `manager` trực ca. Kiểm tra màn hình **System Health & Jobs** (`/admin/system/health`).
3. **Cấp 3 (Hạ tầng khẩn cấp):** Báo cáo trực tiếp cho Trưởng ban Kỹ thuật (Lead Architect) để kích hoạt cơ chế Rollback khẩn cấp hoặc khôi phục từ bản sao lưu gần nhất.

