---
id: ADM-INVT-004
title: "Gia hạn thời gian lưu trữ thiệp cưới và quy trình chuyển đổi gói dịch vụ"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Quy trình chuẩn vận hành Admin để kiểm tra vòng đời thiệp, gia hạn thời gian lưu trữ (1 tháng đến trọn đời), xử lý thời gian ân hạn (Grace Period) và nâng cấp chuyển đổi gói dịch vụ thiệp cưới trên Mây Hẹn."
reader: "Admin / Operation Manager / Customer Care Lead"
order: 4
updated_at: "2026-11-15"
tags: [admin, gia-han-thiep, vong-doi-thiep, nang-cap-goi, grace-period, audit-log]
mapped_page: "ADM-INVT-PAGE"
mapped_route: "admin.invitations.extend"
risk_level: "HIGH"
required_role: "manager"
---

# Gia hạn thời gian lưu trữ thiệp cưới và quy trình chuyển đổi gói dịch vụ

> [!IMPORTANT]
> **Vai trò thực hiện:** `Operation Manager`, `Customer Support Lead`, hoặc `Super Admin`.  
> **Route kỹ thuật:** `POST /admin/invitations/{id}/extend` & `POST /admin/invitations/{id}/migrate-template`.  
> **Dịch vụ backend:** `App\Services\InvitationRenewalService` & `App\Services\InvitationLifecycleService`.  
> **Kiểm toán bắt buộc:** Mọi thao tác can thiệp ngày hết hạn hoặc chuyển đổi gói dịch vụ đều được lưu vết chi tiết vào `audit_logs` với User ID, IP, lý do gia hạn và mốc thời gian trước/sau thay đổi.

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Theo vòng đời dịch vụ Mây Hẹn, mỗi tấm thiệp cưới sau khi xuất bản sẽ có thời hạn lưu trữ và hiển thị trực tuyến tương ứng với gói dịch vụ mà cặp đôi đã thanh toán (Gói Miễn phí: 30–60 ngày sau ngày cưới; Gói Trả phí Tiêu chuẩn: 6 tháng đến 1 năm; Gói Cao cấp/VIP: Lưu trữ trọn đời).

Khi thiệp cưới sắp hết hạn hoặc đã bước vào **Thời gian ân hạn (Grace Period)**, cặp đôi có thể có nhu cầu:
1. **Gia hạn thời gian truy cập trực tuyến** để bạn bè, người thân ở xa tiếp tục xem lại album ảnh kỷ niệm, tải lời chúc và video ngày cưới.
2. **Nâng cấp gói dịch vụ** (từ Miễn phí lên Trả phí, hoặc bổ sung tính năng Màn chiếu Live Wall, Sổ ký tên công nghệ, Lưu trữ không giới hạn).
3. **Bảo tồn kỷ niệm ngày cưới (Digital Memory Vault)** theo dạng lưu trữ trọn đời (`lifetime`), không bị xóa tài nguyên hay hình ảnh.

Quy trình này hướng dẫn nhân viên vận hành thao tác an toàn, đúng thẩm quyền và minh bạch tài chính.

---

## 2. Ma trận vòng đời & Trạng thái lưu trữ thiệp cưới

Hệ thống quản lý vòng đời thiệp cưới tại Mây Hẹn (`InvitationRenewalService`) phân loại thiệp thành **5 trạng thái kỹ thuật**:

| Trạng thái (Status Code) | Nhãn hiển thị | Màu sắc nhận diện | Điều kiện kỹ thuật (`expires_at`) | Hành vi hệ thống |
| :--- | :--- | :--- | :--- | :--- |
| `active` | **Đang Hoạt Động** | Xanh lục (`#2E7D32`) | `expires_at > now() + 14 days` | Khách mở thiệp bình thường, đầy đủ tính năng tương tác. |
| `expiring_soon` | **Sắp Hết Hạn** | Vàng cam (`#F57F17`) | `now() < expires_at <= now() + 14 days` | Hệ thống gửi thông báo nhắc gia hạn qua Email/Zalo ZNS cho cặp đôi. |
| `in_grace_period` | **Đang Ân Hạn** | Cam đậm (`#E65100`) | Quá hạn từ 1 đến 15 ngày (`graceEnd`) | Thiệp vẫn mở được nhưng hiển thị banner nhắc nhở thanh nhã dành riêng cho chủ tiệc. |
| `archived` | **Đã Lưu Trữ Kỷ Niệm** | Đỏ bầm (`#B71C1C`) | Quá hạn > 15 ngày hoặc `is_archived = true` | Đóng link công khai; chuyển hướng khách sang trang kỷ niệm tĩnh hoặc thông báo lưu trữ. |
| `lifetime` | **Lưu Trữ Trọn Đời (VIP)** | Nâu vàng hoàng gia (`#8C6B37`)| `expires_at === null` | Vĩnh viễn mở trực tuyến, cam kết sao lưu dữ liệu dài hạn. |

---

## 3. Điều kiện tiên quyết & Thẩm quyền can thiệp

Trước khi thực hiện gia hạn hoặc đổi gói cho khách hàng trên Admin Portal:
1. **Kiểm tra chứng từ thanh toán**: Cặp đôi đã hoàn tất đơn hàng gia hạn qua cổng VietQR/SePay, hoặc có phê duyệt miễn phí (chăm sóc khách hàng đặc biệt, đối tác Studio giới thiệu).
2. **Xác minh chính xác ID thiệp**: Sử dụng `slug`, số điện thoại cô dâu/chú rể hoặc mã đơn hàng SePay để định vị đúng bản ghi trong bảng `invitations`.
3. **Phân quyền vai trò**:
   - `Customer Support`: Được quyền gia hạn theo đơn hàng đã thanh toán hợp lệ (gói 1 tháng, 3 tháng, 6 tháng, 1 năm).
   - `Operation Manager` / `Super Admin`: Được quyền cấp quyền **Lưu trữ trọn đời (`lifetime`)** hoặc gia hạn ân hạn đặc biệt (miễn phí bảo hành sự cố).

---

## 4. Hướng dẫn từng bước: Gia hạn thời gian hiển thị thiệp cưới

### Bước 1: Tra cứu thiệp cần gia hạn
1. Đăng nhập Admin Portal tại `/admin`.
2. Trên thanh điều hướng bên trái, chọn **Quản lý Thiệp cưới** (`/admin/invitations`).
3. Nhập `slug` hoặc tên cặp đôi vào ô tìm kiếm (ví dụ: `hoang-long-thanh-thao`).
4. Nhấp vào tên thiệp để mở trang **Hồ sơ Thiệp cưới Chi tiết**.

### Bước 2: Kiểm tra hạn dùng hiện tại
* Tại khối **Thông tin Lưu trữ & Vòng đời**:
  * Kiểm tra mốc `expires_at` hiện tại.
  * Nếu thiệp còn hạn: Mốc gia hạn mới sẽ được cộng dồn tiếp nối từ `expires_at` cũ.
  * Nếu thiệp đã hết hạn (`isPast`): Mốc gia hạn mới sẽ tính bắt đầu từ thời điểm thao tác (`now()`).

### Bước 3: Mở hộp thoại Gia hạn thời gian
1. Nhấp nút **[ ⏳ Gia Hạn Lưu Trữ ]** ở góc trên bên phải thanh công cụ.
2. Chọn gói thời gian gia hạn trong danh sách:
   * **+ 30 ngày (1 tháng)**: Gói bổ sung ngắn hạn phục vụ hậu đám cưới.
   * **+ 90 ngày (3 tháng)**: Gói phổ biến để thu thập đủ ảnh ngày cưới từ bạn bè.
   * **+ 180 ngày (6 tháng)**: Gói nửa năm cho các cặp đôi đi trăng mật dài ngày.
   * **+ 365 ngày (1 năm)**: Gói kỷ niệm tròn 1 năm ngày cưới (Paper Anniversary).
   * **Lưu trữ trọn đời (Vĩnh viễn)**: Đặt `expires_at = null`, dành cho gói Diamond/VIP.
3. Nhập **Lý do gia hạn**: Bắt buộc nhập mã đơn hàng thanh toán (ví dụ: `Đơn hàng ORD-2026-8899 qua SePay`) hoặc lý do phê duyệt đặc biệt.

### Bước 4: Xác nhận và lưu vết kiểm toán
1. Nhấp **[ Xác nhận Gia hạn ]**.
2. Hệ thống thực thi qua `Admin/InvitationController@extend` hoặc `InvitationRenewalService@extendDuration`:
   * Cập nhật trường `expires_at` mới trong database.
   * Nếu thiệp đang ở trạng thái `archived`, tự động bỏ cờ `is_archived` và kích hoạt lại `is_published = true`.
   * Ghi log `INVITATION_EXTENDED` vào bảng `audit_logs`.
3. Kiểm tra thông báo Toast màu xanh: *"Đã gia hạn thời gian hiển thị thiệp cưới thêm X ngày."*

---

## 5. Hướng dẫn chuyển đổi mẫu thiệp & Nâng cấp gói dịch vụ

Khi khách hàng nâng cấp từ gói cơ bản lên gói cao cấp và muốn đổi sang mẫu thiệp sang trọng hơn (hoặc mẫu có hỗ trợ nhiều hiệu ứng chuyển động hơn):

### Thao tác trên Admin:
1. Tại trang chi tiết thiệp, tìm khối **Thông tin Giao diện & Mẫu thiệp**.
2. Nhấp nút **[ 🔄 Chuyển Đổi Mẫu Thiệp ]** (`migrateTemplate`).
3. Chọn mẫu thiệp mục tiêu trong danh sách mẫu đang phát hành (ví dụ: chuyển từ mẫu *Minimal Cream* sang mẫu *Celestial Gold*).
4. Hệ thống kiểm tra tính tương thích dữ liệu JSON (`custom_config`):
   * Tự động ánh xạ 10 Section chuẩn hóa (`mayhen_invitation_*`).
   * Bảo toàn nguyên vẹn toàn bộ dữ liệu cô dâu chú rể, timeline tiệc cưới, album ảnh và danh sách khách mời.
5. Nhấp **[ Xác nhận Chuyển Mẫu ]**.

---

## 6. Xử lý sự cố & Tình huống ngoại lệ thường gặp

### Tình huống 1: Khách hàng chuyển khoản nhưng link thiệp vẫn báo hết hạn
* **Nguyên nhân:** Webhook SePay bị trễ mạng hoặc khách ghi sai cú pháp nội dung chuyển khoản, khiến đơn hàng chưa tự động khớp với ID thiệp.
* **Cách xử lý:** 
  1. Vào `/admin/orders` kiểm tra biến động số dư SePay.
  2. Đối soát đúng số tiền và thời gian giao dịch.
  3. Bấm **Duyệt thủ công đơn hàng** -> hệ thống tự động kích hoạt gia hạn thiệp.
  4. Nếu cần xử lý khẩn cấp trước khi đối soát kế toán: Dùng chức năng Gia hạn Admin thêm 3 ngày ân hạn khẩn cấp.

### Tình huống 2: Thiệp đã chuyển sang `archived` nhưng khách muốn lấy lại ảnh kỷ niệm
* **Cách xử lý:** 
  1. Vào trang chi tiết thiệp trên Admin.
  2. Bấm gia hạn tạm thời `+ 30 ngày`.
  3. Thiệp ngay lập tức được phục hồi trực tuyến, khách hàng có thể vào trang cá nhân tải toàn bộ ảnh và sổ lời chúc về máy tính.

---

## 7. Những điều Tuyệt đối Bị cấm trong Vận hành

1. **CẤM TỰ Ý GIA HẠN MIỄN PHÍ TRỌN ĐỜI:** Thao tác chuyển thiệp sang trọn đời (`lifetime`) tiêu tốn chi phí lưu trữ đám mây vĩnh viễn, bắt buộc phải có mã đơn hàng hợp lệ hoặc văn bản phê duyệt từ Giám đốc vận hành.
2. **CẤM RÚT NGẮN THỜI HẠN LƯU TRỮ CỦA KHÁCH:** Tuyệt đối không chỉnh sửa lùi ngày `expires_at` về quá khứ để ép khách hết hạn sớm khi chưa có khiếu nại vi phạm pháp luật hoặc lệnh từ cơ quan chức năng.
3. **CẤM XÓA DỮ LIỆU THIỆP HẾT HẠN:** Thiệp hết hạn chỉ chuyển sang cờ `archived`. Toàn bộ dữ liệu khách mời, sổ mừng và ảnh kỷ niệm phải tuân thủ chính sách lưu trữ bảo mật (Retention Policy) theo Luật 91/2025/QH15, không được xóa cứng (`HARD DELETE`) trực tiếp từ database.

---

## 8. Bài viết liên quan
* [ADM-INVT-001: Tìm kiếm & Tra cứu thông tin thiệp cưới nâng cao](file:///resources/docs/admin/03-public-invitation/ADM-INVT-001.md)
* [ADM-INVT-002: Quy trình khóa thiệp cưới khẩn cấp](file:///resources/docs/admin/03-public-invitation/ADM-INVT-002.md)
* [ADM-INVT-003: Xử lý thay đổi ngày cưới, dời lịch tiệc & đồng bộ dữ liệu](file:///resources/docs/admin/03-public-invitation/ADM-INVT-003.md)
* [ADM-INVT-005: Khôi phục phiên bản thiệp cũ từ lịch sử bản sửa đổi](file:///resources/docs/admin/03-public-invitation/ADM-INVT-005.md)
