---
id: ADM-INVT-002
title: "Xử lý khóa thiệp khẩn cấp khi phát hiện nội dung độc hại hoặc tranh chấp bản quyền"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Quy trình chuẩn hóa nghiệp vụ khóa thiệp cưới khẩn cấp (Emergency Lockdown): tiêu chí kích hoạt, hệ quả kỹ thuật tức thời trên đường dẫn công khai và Studio, cơ chế ghi nhận lý do và quy trình mở khóa an toàn sau giải quyết khiếu nại."
reader: "PER-ADM-01 (Quản lý vận hành / Pháp chế & Kiểm duyệt)"
order: 2
updated_at: "2026-11-15"
tags: [admin, emergency-lock, copyright-dispute, content-freeze, audit-compliance]
mapped_page: "ADM-INVT-PAGE"
mapped_route: "admin.invitations.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# Xử lý khóa thiệp khẩn cấp khi phát hiện nội dung độc hại hoặc tranh chấp bản quyền

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `CRITICAL` | **Endpoints:** `POST /admin/invitations/{id}/lock` & `POST /admin/invitations/{id}/unlock`  
> **SLA Xử lý Khẩn cấp:** Dưới **5 phút** kể từ khi nhận văn bản khiếu nại bản quyền hoặc phát hiện nội dung độc hại, nhằm bảo vệ uy tín nền tảng Mây Hẹn và tránh rủi ro pháp lý liên đới.


![Giao diện quản lý Xử lý khóa thiệp khẩn cấp khi phát hiện nội dung độc hại hoặc tranh chấp bản quyền](/assets/client/docs/images/admin/03-public-invitation/adm-invt-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Khi nào Bắt buộc phải Khóa thiệp Khẩn cấp?

Khóa thiệp cưới là biện pháp can thiệp hành chính mạnh nhất của Quản trị viên, tạm thời "đóng băng" toàn bộ hoạt động của thiệp cưới trên internet. Nhân viên chỉ được kích hoạt thao tác này khi thuộc một trong 5 trường hợp sau:

```
[ 5 TRƯỜNG HỢP KÍCH HOẠT KHÓA THIỆP KHẨN CẤP ]

1. Nội dung độc hại, lừa đảo:
   Sử dụng thiệp cưới để chèn link cờ bạc, đa cấp, số tài khoản lừa đảo mạo danh cô dâu chú rể.

2. Tranh chấp bản quyền hình ảnh (Copyright Infringement):
   Studio nhiếp ảnh hoặc tác giả ảnh gửi văn bản chứng minh cô dâu chú rể sử dụng bộ ảnh chưa trả phí.

3. Xúc phạm, quấy rối hoặc bôi nhọ danh dự:
   Thiệp cưới hoặc sổ ký tên bị kẻ xấu lợi dụng để bêu rếu, xúc phạm cá nhân hoặc dòng họ.

4. Gian lận tài chính & Tranh chấp hoàn tiền (Chargeback):
   Ngân hàng phát hành thẻ tín dụng thông báo giao dịch thanh toán gói cưới có dấu hiệu bị đánh cắp thẻ.

5. Yêu cầu khẩn cấp từ chính Cô dâu Chú rể:
   Cặp đôi gặp sự cố bất khả kháng (hoãn cưới khẩn cấp, lộ thông tin mật) yêu cầu tạm dừng hiển thị thiệp.
```

---

## 2. Hệ quả Kỹ thuật Tức thời khi Kích hoạt Khóa thiệp

Khi Quản trị viên gửi lệnh `POST /admin/invitations/{id}/lock`, hệ thống Mây Hẹn áp dụng cơ chế **Phong tỏa Toàn diện (Total Lockdown)** ngay lập tức:

```
[ HỆ THỐNG GHI NHẬN LỆNH KHÓA: is_locked = true ]
       |
       +---> [ 1. TRANG CÔNG KHAI /thiep/{slug} ] 
       |     Lập tức chuyển hướng sang màn hình thông báo trang nhã:
       |     "Thiệp cưới tạm thời được bảo lưu bởi Ban Quản Trị" (client.pages.invitation-locked).
       |     -> KHÁCH MỜI KHÔNG THỂ XEM BẤT KỲ ẢNH HAY THÔNG TIN NÀO.
       |
       +---> [ 2. TRẠM ĐÓN TIẾP & LIVE WALL ]
       |     Chặn toàn bộ API gửi lời chúc, tải ảnh Photo Drop, quét mã QR điểm danh (Trả về HTTP 403).
       |
       +---> [ 3. STUDIO TẠO THIỆP CỦA KHÁCH HÀNG ]
       |     Khóa tính năng Tự động lưu nháp (Autosave trả về {"conflict": true, "locked": true}).
       |     Khách hàng không thể chỉnh sửa hay xuất bản đè lên nội dung đang tranh chấp.
```

---

## 3. Quy trình 4 Bước Thao tác Khóa thiệp Chuẩn SOP

### Bước 1: Kiểm tra Hồ sơ & Xác minh Bằng chứng
* Mở màn hình tra cứu thiệp tại `/admin/invitations`.
* Đối soát chứng từ vi phạm (ảnh chụp màn hình, hợp đồng bản quyền của Studio, hoặc email chính thức từ chủ tài khoản).

### Bước 2: Mở Modal Khóa thiệp trên Giao diện Admin
* Tại thẻ thiệp cưới cần xử lý, nhấp nút **[Khóa thiệp khẩn cấp]** màu đỏ.
* Hệ thống hiển thị hộp thoại xác nhận yêu cầu nhập **Lý do can thiệp (`reason`)**.

### Bước 3: Điền Lý do Khóa Thiệp Rõ ràng
* Lý do này vừa được lưu vào `custom_config->lock_reason`, vừa hiển thị trong nhật ký kiểm toán.
* *Ví dụ chuẩn:*
  - *"Tạm khóa theo đơn yêu cầu tranh chấp bản quyền ảnh số 104/2026 từ Studio Hera Wedding."*
  - *"Khóa bảo vệ do nghi vấn gian lận thẻ tín dụng đơn hàng #MH-9942."*
  - *"Tạm dừng theo yêu cầu hỗ trợ hoãn cưới từ chú rể Vũ Phong (SĐT: 0988.xxx.xxx)."*

### Bước 4: Xác nhận Thực thi & Kiểm tra Trạng thái
* Nhấp **[Xác nhận Khóa]**.
* Trạng thái thiệp trên màn hình Admin đổi sang huy hiệu đỏ **[ĐÃ KHÓA]**.
* Mở một tab ẩn danh truy cập thử đường dẫn `/thiep/{slug}` để kiểm tra chắc chắn trang công khai đã được che chắn bởi màn hình bảo lưu.

---

## 4. Quy trình Mở khóa An toàn (Unlock SOP)

Sau khi tranh chấp được hòa giải thành công hoặc khách hàng đã gỡ bỏ hình ảnh vi phạm:

1. **Xác nhận Biên bản Hòa giải / Chứng từ Giải quyết:**  
   Chuyên viên CSKH nhận được xác nhận đồng thuận từ các bên liên quan.
2. **Kích hoạt Lệnh Mở khóa:**  
   - Truy cập trang chi tiết thiệp cưới tại `/admin/invitations/{id}`.
   - Nhấp nút **[Mở khóa thiệp (Unlock)]** (`POST /admin/invitations/{id}/unlock`).
3. **Hệ thống Xóa bỏ Cờ khóa:**  
   - Gỡ bỏ hoàn toàn `is_locked`, `lock_reason` và `locked_at` khỏi cấu hình `custom_config`.
   - Trạng thái thiệp phục hồi lại bình thường.
   - Khách mời có thể truy cập lại đường dẫn thiệp ngay lập tức mà không cần đổi link mới.

---

## 5. Lưu vết Kiểm toán & Tuân thủ Pháp lý (Audit Trail)

Mọi thao tác Khóa và Mở khóa thiệp đều được ghi nhận vĩnh viễn vào bảng `audit_logs` để bảo vệ nhân viên và nền tảng trước pháp luật:

```json
{
  "action": "lock_invitation",
  "target_type": "Invitation",
  "target_id": 482,
  "description": "Khóa thiệp cưới \"vu-phong-thao-linh\" với lý do: Tạm khóa theo yêu cầu tranh chấp bản quyền ảnh số 104/2026",
  "payload": {
    "slug": "vu-phong-thao-linh",
    "reason": "Tạm khóa theo yêu cầu tranh chấp bản quyền ảnh số 104/2026",
    "admin_id": 3,
    "admin_name": "Trần Thị Mai (Pháp chế)",
    "ip_address": "118.69.xxx.xxx",
    "timestamp": "2026-11-15T21:08:15+07:00"
  }
}
```
