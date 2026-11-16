---
id: ADM-USER-005
title: "Kiểm tra và trích xuất nhật ký hoạt động kiểm toán (Audit Log) truy vết thao tác"
category: "Quản lý Tài khoản Khách & RBAC"
summary: "Hướng dẫn quản trị viên tra cứu nhật ký kiểm toán quản trị (Audit Logs), cơ chế che giấu dữ liệu nhạy cảm (PII Masking), kỹ thuật phân tích sự cố và xuất báo cáo CSV UTF-8 BOM phục vụ thanh tra."
reader: "PER-ADM-01 / PER-ADM-03"
order: 5
updated_at: "2026-11-15"
tags: [audit-log, jsonl, pii-masking, compliance, security, csv-export, forensics]
mapped_page: "ADM-AUDIT-PAGE"
mapped_route: "admin.settings.audit_logs"
risk_level: "HIGH"
required_role: "manager"
---

# Kiểm tra và trích xuất nhật ký hoạt động kiểm toán (Audit Log) truy vết thao tác

> [!WARNING]
> **Phân quyền thực hiện:** Quản lý Vận hành (`manager`) hoặc Super Admin (`admin.super`).  
> **Route quản trị:** `GET /admin/settings/audit-logs` (`admin.settings.audit_logs`).  
> **Tính toàn vẹn kiểm toán:** Nhật ký được lưu trữ theo cơ chế Ghi một lần Đọc nhiều lần (WORM: Write-Once-Read-Many) tại `storage/logs/admin_audit.jsonl`, không thể chỉnh sửa hay làm sai lệch lịch sử.


![Giao diện quản lý Kiểm tra và trích xuất nhật ký hoạt động kiểm toán (Audit Log) truy vết thao tác](/assets/client/docs/images/admin/03-users-rbac/adm-user-005-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục Đích & Ý Nghĩa Của Hệ Thống Nhật Ký Kiểm Toán

Mọi can thiệp nghiệp vụ của nhân viên trong trang quản trị Mây Hẹn (duyệt đơn hàng, mở khóa thiệp, thay đổi vai trò phân quyền, đặt lại mật khẩu, hoàn tiền) đều tiềm ẩn rủi ro sai sót hoặc lạm quyền. Hệ thống **Nhật Ký Kiểm Toán (Audit Trail)** được xây dựng nhằm mục đích:
1. **Truy vết trách nhiệm cá nhân:** Biết chính xác ai đã thực hiện thao tác (User ID, Tên, Email), vào thời điểm nào (Timestamp ISO 8601) và từ địa chỉ IP nào.
2. **Điều tra sự cố an ninh (Digital Forensics):** Tái hiện chuỗi hành vi khi xảy ra nghi vấn lộ lọt dữ liệu hoặc tranh chấp tài chính.
3. **Tuân thủ quy chuẩn kiểm toán an ninh mạng:** Phục vụ thanh tra độc lập và báo cáo tuân thủ an toàn thông tin theo Nghị định 13/2023/NĐ-CP và Luật 91/2025/QH15.

---

## 2. Cơ Chế Khử Dữ Liệu Nhạy Cảm (Sensitive Key Masking)

Để đảm bảo chính nhật ký kiểm toán không trở thành nguồn rò rỉ thông tin mật, `AuditLogger::sanitizeDetails` tự động duyệt đệ quy và che giấu toàn bộ các khóa nhạy cảm:

```
┌────────────────────────┬─────────────────────────────────────────────────────────────┐
│  Khóa Nhạy Cảm Bị Che  │  Giá Trị Lưu Trữ Thực Tế Trong admin_audit.jsonl            │
├────────────────────────┼─────────────────────────────────────────────────────────────┤
│  password              │  "password": "***REDACTED***"                               │
│  token / api_key       │  "token": "***REDACTED***", "api_key": "***REDACTED***"     │
│  webhook_secret        │  "webhook_secret": "***REDACTED***"                         │
│  card_number / cvv     │  "card_number": "***REDACTED***", "cvv": "***REDACTED***"   │
│  bank_account          │  "bank_account": "***REDACTED***"                           │
└────────────────────────┴─────────────────────────────────────────────────────────────┘
```

Nhờ cơ chế này, ngay cả khi nhật ký kiểm toán được trích xuất cho bên thứ ba, toàn bộ mật khẩu và mã khóa ngân hàng đều được bảo vệ tuyệt đối.

---

## 3. Các Hành Động Được Ghi Nhận Tự Động

Giao diện `/admin/settings/audit-logs` phân loại 17 nhóm hành động quản trị cốt lõi:
- **Tài chính & Đơn hàng:** `approve_order` (Duyệt đơn hàng thủ công), `cancel_order` (Hủy đơn hàng), `update_banking` (Cập nhật tài khoản thụ hưởng), `refund_order` (Xét duyệt hoàn tiền).
- **Thiệp cưới & Khách hàng:** `lock_invitation` (Khóa thiệp), `unlock_invitation` (Mở khóa thiệp), `toggle_publish` (Bật/Ẩn thiệp), `extend_invitation` (Gia hạn thời gian lưu trữ thiệp).
- **Phân quyền & Tài khoản:** `change_user_role` (Nâng/Hạ vai trò nhân sự), `reset_user_password` (Đặt lại mật khẩu), `lock_user` / `unlock_user` (Khóa/Mở tài khoản khách).
- **Kho Mẫu & Cấu hình:** `scan_templates` (Quét mẫu thiệp mới), `update_template` (Cập nhật mã nguồn mẫu), `toggle_template_active` (Bật/Tắt mẫu), `update_plan` (Điều chỉnh giá gói cước).

---

## 4. Hướng Dẫn Tra Cứu & Điều Tra Nhật Ký

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [15/11/2026 14:25:10] Quản trị viên: Trần Hoàng Nam (namth@mayhen.vn) | IP: 14.162.180.12        │
│ Hành động: [ Duyệt Đơn Hàng ] - Đối tượng: Order #ORD-2026-9812                                   │
│ Chi tiết: Duyệt đơn hàng gói Everlasting Vault trị giá 499,000 VNĐ qua chuyển khoản SePay        │
│ Dữ liệu kèm theo: {"order_id": 9812, "amount": 499000, "token": "***REDACTED***"}                │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

1. **Bước 1:** Truy cập `/admin/settings/audit-logs`.
2. **Bước 2:** Sử dụng bộ lọc:
   - **Lọc theo loại hành động (`action`):** Chọn hành động nghi vấn (Ví dụ: *Đặt Lại Mật Khẩu* hoặc *Duyệt Đơn Hàng*).
   - **Tìm kiếm theo từ khóa (`q`):** Nhập mã đơn hàng, email nhân viên, mã thiệp cưới hoặc địa chỉ IP.
3. **Bước 3:** Quan sát chi tiết từng dòng bản ghi: Thời gian thực hiện, tên người thao tác, đối tượng bị tác động và nội dung payload can thiệp.

---

## 5. Xuất Báo Cáo Kiểm Toán CSV UTF-8 BOM

Khi phục vụ các đợt thanh tra tài chính nội bộ hoặc yêu cầu giải trình của Ban Giám đốc:
1. Tại trang `/admin/settings/audit-logs`, nhấp nút **[Xuất báo cáo CSV]** (`GET /admin/settings/audit-logs/export-csv`).
2. Hệ thống trích xuất tối đa **2,000 bản ghi mới nhất** phù hợp với bộ lọc hiện hành.
3. Tệp CSV được tạo dưới dạng Streamed Response, nhúng sẵn mã UTF-8 BOM (`\xEF\xBB\xBF`) hiển thị trọn vẹn dấu tiếng Việt trong Microsoft Excel.
4. Báo cáo bao gồm 9 cột thông tin: `STT`, `Thời Gian`, `Quản Trị Viên`, `Email`, `Hành Động`, `Đối Tượng Tác Động`, `Mã Đối Tượng`, `Nội Dung Chi Tiết`, `Địa Chỉ IP`.

---

## 6. Chính Sách Lưu Trữ & An Toàn Tệp JSONL

- Tệp nhật ký `admin_audit.jsonl` được ghi theo cơ chế nối tiếp (Append-only).
- Định kỳ vào ngày đầu tiên của mỗi tháng, tác vụ nền tự động nén tệp log cũ thành định dạng `.tar.gz`, chuyển vào thư mục lưu trữ an toàn `storage/logs/archive/` và giữ lại tối thiểu **24 tháng**.
- Không có bất kỳ giao diện hay API nào cho phép sửa đổi hoặc xóa từng dòng bản ghi kiểm toán.

---

## 7. Những điều Tuyệt đối Bị cấm

1. **CẤM TUYỆT ĐỐI** xóa trắng hoặc can thiệp sửa đổi nội dung tệp `storage/logs/admin_audit.jsonl`. Mọi hành vi xóa vết kiểm toán đều bị xử lý kỷ luật mức cao nhất.
2. **CẤM** bỏ qua việc ghi nhận Audit Log khi bổ sung các chức năng quản trị mới trong Controller.
3. **CẤM** gửi tệp CSV nhật ký kiểm toán cho người không có thẩm quyền hoặc chia sẻ ra bên ngoài tổ chức.

---

## 8. Bài viết Liên quan

- [ADM-USER-002: Quy trình khóa tài khoản vi phạm và mở khóa an toàn](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-002.md)
- [ADM-USER-003: Phân quyền vai trò nhân viên và phòng ngừa lỗi tự tước quyền Super Admin](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-003.md)
- [ADM-USER-004: Thực thi quyền lãng quên (PII Anonymization) theo Luật 91/2025/QH15](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-004.md)
