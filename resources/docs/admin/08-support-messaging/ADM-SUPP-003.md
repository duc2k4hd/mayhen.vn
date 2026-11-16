---
id: ADM-SUPP-003
title: "Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn, có ghi log"
category: "Hỗ trợ Khách hàng & Cổng Tin nhắn"
summary: "Quy trình thực tế dành cho nhân viên CSKH khi cần đóng vai (Impersonate) vào tài khoản cô dâu chú rể để hướng dẫn trực tiếp, giới hạn phiên làm việc 30 phút, phòng ngừa rò rỉ mật khẩu và tuân thủ Luật 91/2025/QH15."
reader: "PER-ADM-01 (Nhân viên CSKH & Vận hành Hỗ trợ)"
order: 3
updated_at: "2026-11-15"
tags: [admin, dong-vai-ho-tro, impersonation, cskh, an-toan-thong-tin, audit-log, session-ttl]
mapped_page: "ADM-SUPP-PAGE"
mapped_route: "admin.support.impersonate"
risk_level: "HIGH"
required_role: "support"
---

# Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn, có ghi log

> [!CAUTION]
> **Vai trò thực hiện:** `Customer Care Specialist (CSKH)`, `Support Lead`, hoặc `Super Admin`.  
> **Route kích hoạt Impersonation:** `POST /admin/support/impersonate/{userId}` (`SupportImpersonationController@impersonate`).  
> **Route thoát phiên:** `POST /admin/support/leave` (`@leave`).  
> **Dịch vụ backend:** `App\Services\SupportImpersonationService` & `App\Http\Middleware\SupportImpersonationMiddleware`.  
> **Giới hạn thời gian an toàn (TTL):** Tự động kết thúc sau **30 phút** không hoạt động, đưa Admin quay lại màn hình quản trị.


![Giao diện quản lý Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn, có ghi log](/assets/client/docs/images/admin/08-support-messaging/adm-supp-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Trong quá trình hỗ trợ qua điện thoại hoặc Ticket, nhiều cặp đôi (đặc biệt là người lớn tuổi hoặc người không quen sử dụng máy tính) gặp khó khăn khi mô tả lỗi:
* *"Em ơi, album ảnh cưới của chị bị mất 2 tấm hình trên điện thoại Samsung mà chị không biết chỉnh lại ở đâu!"*
* *"Anh muốn đổi thứ tự phần 'Lễ Rước Dâu' lên trước 'Tiệc Chiêu Đãi' mà kéo thả mãi không được."*

Thay vì yêu cầu khách hàng đọc mật khẩu tài khoản (tiềm ẩn nguy cơ lộ lọt bảo mật nghiêm trọng), nhân viên CSKH sử dụng tính năng **Đóng vai hỗ trợ (Safe Support Impersonation)** để:
1. Đăng nhập ngay lập tức vào đúng không gian cá nhân của cặp đôi mà **không cần biết mật khẩu**.
2. Trực tiếp thực hiện các thao tác mẫu trong Studio dưới góc nhìn của khách hàng.
3. Rời khỏi phiên an toàn chỉ với 1 nhấp chuột, bảo đảm toàn bộ dữ liệu nhạy cảm được bảo vệ 100%.

---

## 2. Rào Chắn Bảo Mật & Tuyệt Đối Không Thể Xâm Phạm (Security Safeguards)

Nhân viên CSKH khi kích hoạt chế độ hỗ trợ sẽ hoạt động dưới sự giám sát nghiêm ngặt của `SupportImpersonationMiddleware`:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       CHẾ ĐỘ HỖ TRỢ KHÁCH HÀNG                              │
│                                                                             │
│   ĐƯỢC PHÉP THAO TÁC:                                                       │
│   ✔ Chỉnh sửa nội dung thiệp trong Studio (Lời ngỏ, Timeline, Dresscode)     │
│   ✔ Tải và căn chỉnh ảnh cưới trong Album                                   │
│   ✔ Hướng dẫn cấu hình Sơ đồ bàn tiệc & Sổ ký tên                           │
│                                                                             │
│   BỊ KHÓA TUYỆT ĐỐI (HTTP 403 FORBIDDEN):                                   │
│   ✖ KHÔNG THỂ đổi mật khẩu tài khoản (account.password.update)             │
│   ✖ KHÔNG THỂ đổi địa chỉ email đăng nhập (account.profile.update)          │
│   ✖ KHÔNG THỂ yêu cầu xóa vĩnh viễn dữ liệu (account.privacy.erase)         │
│   ✖ KHÔNG THỂ thực hiện thanh toán rút tiền mừng cưới                       │
└─────────────────────────────────────────────────────────────────────────────┘
```

* **Banner màu cam trên đỉnh trang**: Luôn hiển thị thanh thông báo:  
  *`"🛡️ Đang hỗ trợ khách hàng: [Tên khách] • Thời gian còn lại: XX phút • [Rời Chế Độ Hỗ Trợ]"`*.
* **Nhật ký kiểm toán kép**: Ghi lại chi tiết: Admin ID, Tên nhân viên, Customer ID, Mã Ticket hỗ trợ, Lý do hỗ trợ, IP và thời gian bắt đầu/kết thúc.

---

## 3. Hướng dẫn từng bước: Kích hoạt & Sử dụng Chế độ Đóng vai

### Bước 1: Tiếp nhận Ticket và Xác minh yêu cầu
1. Mở Ticket hỗ trợ đang xử lý tại `/admin/support/{id}`.
2. Xác nhận khách hàng đã đồng ý cho nhân viên kỹ thuật truy cập tài khoản để hỗ trợ (qua chat hoặc ghi âm cuộc gọi).
3. Sao chép **Mã Ticket** (ví dụ: `TK-2026-8812`) và ghi nhận nội dung cần xử lý.

### Bước 2: Kích hoạt chế độ Hỗ trợ
1. Nhấp vào tên khách hàng hoặc truy cập hồ sơ người dùng tại `/admin/users/{userId}`.
2. Nhấp nút màu vàng cam **[ 🛡️ Đăng Nhập Hỗ Trợ (Impersonate) ]**.
3. Biểu mẫu xác thực yêu cầu nhập 2 trường:
   * **Mã Ticket hỗ trợ (`ticket_code`)**: Dán mã Ticket đã sao chép ở Bước 1.
   * **Lý do hỗ trợ (`reason`)**: Diễn giải ngắn gọn (tối thiểu 5 ký tự, ví dụ: `Hỗ trợ căn chỉnh tiêu điểm ảnh cưới theo Ticket #TK-2026-8812`).
4. Nhấp **[ Xác Nhận Bắt Đầu Hỗ Trợ ]**.

### Bước 3: Thao tác hướng dẫn khách hàng
1. Hệ thống chuyển bạn tới trang cá nhân của cặp đôi (`/tai-khoan`).
2. Vào **Studio Tạo Thiệp Cưới**:
   * Kiểm tra giao diện hiển thị đúng như khách phản ánh.
   * Chỉnh sửa lại tiêu điểm ảnh cưới, sắp xếp lại timeline sự kiện hoặc điều chỉnh vị trí sảnh tiệc.
   * Bấm `[ Lưu bản nháp ]` trên Studio.
3. Liên hệ thông báo cho khách hàng tải lại trang trên điện thoại để kiểm tra kết quả ngay lập tức.

### Bước 4: Thoát khỏi phiên hỗ trợ an toàn
1. Khi đã giải quyết xong sự cố, nhấp nút màu đỏ **[ 🚪 Rời Chế Độ Hỗ Trợ ]** trực tiếp trên banner đầu trang.
2. Hệ thống gọi `SupportImpersonationService@stopImpersonation`:
   * Hủy các biến Session hỗ trợ.
   * Đưa bạn quay trở lại tài khoản Quản trị ban đầu tại trang `/admin/users`.
   * Ghi log kiểm toán phiên kết thúc thành công.
3. Quay trở lại trang Ticket và cập nhật trạng thái sang `resolved`.

---

## 4. Xử lý các tình huống đặc biệt trong phiên hỗ trợ

### Tình huống 1: Hết thời hạn 30 phút mà công việc chưa xong
* **Hiện tượng:** Màn hình tự động chuyển hướng về `/admin/dashboard` với cảnh báo: *"Phiên hỗ trợ khách hàng đã tự động kết thúc do hết thời hạn (30 phút)"*.
* **Xử lý:** Đây là cơ chế tự bảo vệ an toàn. Nếu cần tiếp tục, chỉ cần vào lại hồ sơ người dùng và bấm kích hoạt lại phiên mới.

### Tình huống 2: Vô tình nhấp vào trang Cài Đặt Mật Khẩu của khách
* **Hiện tượng:** Hệ thống hiển thị thông báo lỗi `403 Forbidden`: *"Hành động này bị vô hiệu hóa trong chế độ hỗ trợ khách hàng để bảo vệ quyền riêng tư"*.
* **Xử lý:** Đây là rào chắn an ninh của Mây Hẹn nhằm bảo vệ khách hàng, bạn chỉ cần quay lại trang Studio để tiếp tục thao tác hỗ trợ.

---

## 5. Những điều Tuyệt đối Bị cấm trong Vận hành Impersonation

1. **TUYỆT ĐỐI KHÔNG HỎI MẬT KHẨU CỦA KHÁCH HÀNG:** Nhân viên Mây Hẹn không bao giờ được yêu cầu khách hàng cung cấp mật khẩu tài khoản dưới bất kỳ hình thức nào.
2. **CẤM TỰ Ý KÍCH HOẠT HỖ TRỢ KHI KHÔNG CÓ TICKET GHI NHẬN:** Mọi phiên đăng nhập hỗ trợ không gắn với mã Ticket hợp lệ đều bị hệ thống phát hiện và xếp vào diện vi phạm an ninh dữ liệu cấp độ 2.
3. **CẤM XEM HOẶC SAO CHÉP DỮ LIỆU KHÁCH MỜI RIÊNG TƯ:** Số điện thoại khách mời, danh sách mừng cưới và thông tin gia đình hai họ là dữ liệu cá nhân được bảo vệ theo Luật 91/2025/QH15, nghiêm cấm tải về hoặc tiết lộ ra ngoài.

---

## 6. Bài viết liên quan
* [ADM-SUPP-001: Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)](/admin/docs/support-messaging/ADM-SUPP-001)
* [ADM-SUPP-002: Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)](/admin/docs/support-messaging/ADM-SUPP-002)
* [ADM-SUPP-004: Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client](/admin/docs/support-messaging/ADM-SUPP-004)
* [ADM-INVT-006: Can thiệp hỗ trợ thiệp khách hàng mà không làm rò rỉ hoặc mất dữ liệu khách mời](/admin/docs/public-invitation/ADM-INVT-006)
