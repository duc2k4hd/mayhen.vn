---
id: ADM-USER-004
title: "Thực thi quyền lãng quên (PII Anonymization): Ẩn danh hóa thông tin nhưng bảo toàn hóa đơn kế toán"
category: "Quản lý Tài khoản Khách & RBAC"
summary: "Quy trình thực thi quyền được lãng quên (Right to Erasure) theo Luật 91/2025/QH15: thẩm định yêu cầu DSAR, ẩn danh hóa dữ liệu cá nhân PII nhưng bảo toàn 100% chứng từ hóa đơn kế toán."
reader: "PER-ADM-01 / PER-ADM-03"
order: 4
updated_at: "2026-11-15"
tags: [privacy, law-91-2025, pii-anonymization, dsar, right-to-erasure, accounting-preservation]
mapped_page: "ADM-PRIVACY-PAGE"
mapped_route: "admin.privacy.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# Thực thi quyền lãng quên (PII Anonymization): Ẩn danh hóa thông tin nhưng bảo toàn hóa đơn kế toán

> [!WARNING]
> **Phân quyền thực hiện:** Quản lý Vận hành (`manager`) hoặc Super Admin (`admin.super`).  
> **Route quản trị:** `GET /admin/privacy` (`admin.privacy.index`).  
> **Xung đột pháp lý cốt lõi:** Cân bằng giữa *Quyền được lãng quên* (Luật Bảo vệ Dữ liệu Cá nhân số 91/2025/QH15) và *Nghĩa vụ lưu trữ chứng từ tài chính 10 năm* (Luật Kế toán Việt Nam số 88/2015/QH13).


![Giao diện quản lý Thực thi quyền lãng quên (PII Anonymization): Ẩn danh hóa thông tin nhưng bảo toàn hóa đơn kế toán](/assets/client/docs/images/admin/03-users-rbac/adm-user-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Nguyên Tắc Cốt Lõi: Ẩn Danh Hóa Thay Vì Xóa Cứng (Soft Anonymization)

Khi người dùng gửi yêu cầu thực thi quyền lãng quên (**Right to be Forgotten**), một sai lầm chết người của lập trình viên là chạy lệnh xóa cứng `DELETE FROM users WHERE id = ...`. Hành vi này sẽ:
- Phá vỡ khóa ngoại (Foreign Keys) liên kết với bảng đơn hàng `orders`.
- Xóa mất doanh thu đối soát thuế của doanh nghiệp, vi phạm nghiêm trọng Luật Kế toán.
- Gây lỗi sập trang (Crash 500) khi các báo cáo tài chính cố gắng truy vấn người thanh toán.

**Giải pháp chuẩn hóa của Mây Hẹn (Mục NXT-10 PRD V3):** Thực thi tiến trình **Ẩn Danh Hóa Toàn Diện (PII Anonymization)**:
1. Xóa sạch mọi thông tin định danh cá nhân (PII: Họ tên, Email, Số điện thoại, Địa chỉ, Danh bạ khách mời).
2. Thay thế bằng mã định danh ẩn danh không thể phục hồi ngược.
3. **Bảo toàn nguyên vẹn 100% hồ sơ đơn hàng, mã giao dịch SePay, số tiền và hóa đơn điện tử** phục vụ thanh tra thuế.

---

## 2. Bảng Điều Khiển Quản Trị Quyền Riêng Tư (`/admin/privacy`)

Giao diện trạm chỉ huy quyền riêng tư quản lý 3 khu vực chính:
1. **Danh sách yêu cầu xóa dữ liệu (Erasure Requests):** Liệt kê các yêu cầu do khách hàng gửi lên với mã `REQ-YYYYMMDD-XXXX`.
2. **Quét dữ liệu quá hạn lưu trữ (Data Retention Scanner):** Tự động phát hiện các tiệc cưới đã diễn ra quá 180 ngày cần dọn dẹp số điện thoại của quan khách.
3. **Cấp phát token tải hồ sơ DSAR:** Quản lý các liên kết trích xuất hồ sơ cá nhân có mã hóa một lần.

---

## 3. Quy Trình 3 Bước Phê Duyệt & Thực Thi Quyền Lãng Quên

```
┌─────────────────────────┐      ┌─────────────────────────┐      ┌─────────────────────────┐
│ Bước 1: Tiếp Nhận & Soi │ ──>  │ Bước 2: Quản Lý Phê     │ ──>  │ Bước 3: Thực Thi Ẩn     │
│ Tra Cứu Yêu Cầu (REQ)   │      │ Duyệt (Approve/Reject)  │      │ Danh Hóa (Execute)      │
└─────────────────────────┘      └─────────────────────────┘      └─────────────────────────┘
```

### Bước 1: Tiếp nhận & Kiểm tra điều kiện
1. Truy cập `/admin/privacy`.
2. Mở yêu cầu xóa dữ liệu ở trạng thái `Pending`.
3. Kiểm tra các điều kiện:
   - Khách hàng không còn khoản nợ hoặc tranh chấp thanh toán chưa giải quyết.
   - Tài khoản đã hết thời hạn ân hạn 7 ngày kể từ khi bấm gửi yêu cầu.

### Bước 2: Phê duyệt yêu cầu (`approveErasureRequest`)
- Nhấp **[Phê duyệt yêu cầu]** (`POST /admin/privacy/requests/{id}/approve`).
- Trạng thái yêu cầu chuyển sang `Approved`.
- (Trường hợp từ chối): Nhấp **[Từ chối]**, bắt buộc nhập lý do từ chối (Ví dụ: *"Đơn hàng đang trong quá trình khiếu nại bồi hoàn"*).

### Bước 3: Thực thi ẩn danh hóa (`executeErasure`)
Khi nhấp nút **[Thực thi ẩn danh hóa]** (`POST /admin/privacy/requests/{id}/execute`), `PrivacyRetentionService::anonymizeUserAccount` thực thi nguyên tử trong một Transaction:
1. **Dọn dẹp thiệp cưới & khách mời:**
   - Cột `phone` trong bảng `guests` được cập nhật thành `null`.
   - Cột `dietary_requirements` trong `guest_responses` được gán chuỗi `[ANONYMIZED]`.
   - Cột `ip_address` đưa về địa chỉ tĩnh `127.0.0.1`.
2. **Ẩn danh hóa tài khoản User:**
   - Cột `name` đổi thành: `[Tài khoản đã ẩn danh]`.
   - Cột `email` đổi thành chuỗi ngẫu nhiên không tồn tại: `anonymized_{user_id}_{rand}@deleted.mayhen.vn`.
   - Cột `phone` gán `null`.
   - Khóa tài khoản vĩnh viễn: `is_locked = true`, ghi rõ lý do khóa.
3. **Bảo toàn hồ sơ tài chính:**
   - Bảng `orders` giữ nguyên vẹn liên kết `user_id`. Kế toán vẫn tra cứu được số tiền, ngày thanh toán và mã chuẩn chi.
4. **Ghi nhật ký kiểm toán:** Tạo bản ghi Audit Log `privacy.dsar_user_account_anonymized` lưu trữ số lượng thiệp, số lượng khách mời đã xử lý và số đơn hàng được bảo toàn.

---

## 4. Quét & Xử Lý Dữ Liệu Quá Hạn Lưu Trữ Hàng Loạt (Batch Retention)

Theo nguyên tắc Giảm thiểu Dữ liệu (Data Minimization) của Luật 91/2025/QH15:
- Sau ngày cưới **180 ngày**, hệ thống xem xét dữ liệu số điện thoại của khách mời đã hoàn thành mục đích sử dụng.
- Quản trị viên nhấp nút **[Quét dữ liệu quá hạn]** để xem danh sách các tiệc cưới đủ điều kiện dọn dẹp.
- Hỗ trợ chế độ **Chạy thử an toàn (Dry-Run)**: Báo cáo trước số lượng bản ghi PII sẽ bị ảnh hưởng mà chưa ghi đè dữ liệu.
- Sau khi kiểm tra số liệu, nhấp **[Thực thi dọn dẹp hàng loạt (Batch Retention)]** để tự động gỡ bỏ số điện thoại của hàng chục ngàn khách mời cũ, giải phóng áp lực lưu trữ và tuân thủ tuyệt đối quy định pháp luật.

---

## 5. Cấp Token Trích Xuất Dữ Liệu DSAR An Toàn

Khi khách hàng yêu cầu nhận bản sao dữ liệu cá nhân theo Điều 9 & 10 Luật 91/2025/QH15:
1. Super Admin vào chi tiết tài khoản, chọn **[Xuất dữ liệu cá nhân DSAR]**.
2. Nhập lý do trích xuất (Tối thiểu 10 ký tự, phục vụ kiểm toán).
3. `PrivacyRetentionService::generateSecureDownloadToken` sinh mã token bảo mật:
   - Dạng chuỗi: `dsar_tok_{random40}`.
   - Thời hạn hiệu lực: Tối đa **24 giờ**.
   - Giới hạn số lần tải: **Tối đa 03 lần** (vượt quá 3 lần token tự động bị vô hiệu hóa).
4. Khách hàng tải file qua đường dẫn: `/admin/privacy/dsar-download/{token}`.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM TUYỆT ĐỐI** xóa các dòng bản ghi trong bảng `orders` của người dùng đã yêu cầu xóa tài khoản. Mọi hành vi làm mất chứng từ thanh toán đều vi phạm pháp luật kế toán.
2. **CẤM** thực thi ẩn danh hóa mà không có yêu cầu bằng văn bản hoặc mã Ticket xác thực từ khách hàng. Thao tác này là **BẤT KHẢ NGHỊCH** (không thể khôi phục lại dữ liệu gốc).
3. **CẤM** chia sẻ đường link tải DSAR của khách hàng cho bất kỳ bên thứ ba nào.

---

## 7. Bài viết Liên quan

- [ADM-USER-002: Quy trình khóa tài khoản vi phạm và mở khóa an toàn](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-002.md)
- [ADM-USER-005: Kiểm tra và trích xuất nhật ký hoạt động kiểm toán (Audit Log)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/03-users-rbac/ADM-USER-005.md)
- [CLI-PART-005: Hướng dẫn quyền riêng tư và yêu cầu xóa tài khoản (Dành cho khách hàng)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-005.md)
