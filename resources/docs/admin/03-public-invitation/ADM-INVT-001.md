---
id: ADM-INVT-001
title: "Tra cứu thiệp theo mã, số điện thoại, tên cặp đôi hoặc tên miền phụ"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Hướng dẫn nhân viên quản trị và CSKH sử dụng bộ công cụ tìm kiếm đa chiều tại Trung tâm Quản lý Thiệp cưới: tra cứu siêu tốc qua mã thiệp, số điện thoại, tên cô dâu chú rể, email và lọc theo trạng thái xuất bản."
reader: "PER-ADM-02 (Chuyên viên CSKH / Điều phối viên vận hành)"
order: 1
updated_at: "2026-11-15"
tags: [admin, invitation-lookup, multi-search, slug-query, phone-lookup]
mapped_page: "ADM-INVT-PAGE"
mapped_route: "admin.invitations.index"
risk_level: "MEDIUM"
required_role: "manager"
---

# Tra cứu thiệp theo mã, số điện thoại, tên cặp đôi hoặc tên miền phụ

> [!NOTE]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `MEDIUM` | **Endpoint:** `GET /admin/invitations` (`admin.invitations.index`)  
> **Bộ điều khiển:** `App\Http\Controllers\Admin\InvitationController@index`  
> **Tốc độ tra cứu chuẩn:** Dưới **150 mili-giây** nhờ tối ưu hóa Eloquent Eager Loading (`with(['wedding.user', 'template'])`) và chỉ mục tìm kiếm thông minh.


![Giao diện quản lý Tra cứu thiệp theo mã, số điện thoại, tên cặp đôi hoặc tên miền phụ](/assets/client/docs/images/admin/03-public-invitation/adm-invt-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích Nghiệp vụ của Chức năng Tra cứu Thiệp

Mỗi ngày, bộ phận Chăm sóc Khách hàng (CSKH) và Vận hành Mây Hẹn tiếp nhận hàng chục cuộc gọi hotline, tin nhắn Zalo và email yêu cầu hỗ trợ từ các cặp đôi. Khách hàng thường chỉ cung cấp một mảnh thông tin rời rạc, ví dụ:
* *"Em tên là Thảo Linh, em tạo thiệp cưới tuần trước mà giờ quên mất link..."*
* *"Anh kiểm tra giúp số điện thoại 0988.123.456 của anh xem gói dịch vụ đã kích hoạt chưa..."*
* *"Thiệp cưới đường dẫn vu-phong-thao-linh của mình bị lỗi không mở được..."*

Module **Quản lý Thiệp cưới (`/admin/invitations`)** được trang bị động cơ tìm kiếm hợp nhất (Unified Multi-Search Engine), cho phép nhân viên gõ bất kỳ từ khóa nào để truy xuất chính xác hồ sơ thiệp cưới của khách hàng chỉ trong 1 lần nhấn phím `Enter`.

---

## 2. Các Tiêu chí Tra cứu Đa chiều Hỗ trợ

Hệ thống tự động phân tích chuỗi tìm kiếm và khớp nối đồng thời trên nhiều bảng dữ liệu liên quan:

```
+---------------------------------------------------------------------------------------------------+
|  ĐỘNG CƠ TÌM KIẾM HỢP NHẤT MÂY HẸN (QUERY ENGINE)                                                 |
+---------------------------------------------------------------------------------------------------+
|  [ Ô NHẬP TỪ KHÓA TÌM KIẾM: "0988123456" HOẶC "vu-phong" ]                       [ TÌM KIẾM 🔍 ]  |
+---------------------------------------------------------------------------------------------------+
|  1. Đường dẫn phụ (Slug)      : slug LIKE '%vu-phong%' (ví dụ: mayhen.vn/thiep/vu-phong-thao-linh) |
|  2. Tiêu đề thiệp cưới        : title LIKE '%...'                                                 |
|  3. Tên Cô dâu & Chú rể       : wedding.groom_name HOẶC wedding.bride_name                        |
|  4. Tên chủ tài khoản         : wedding.user.name                                                 |
|  5. Số điện thoại đăng ký     : wedding.user.phone (loại bỏ tự động dấu chấm, dấu cách)           |
|  6. Email đăng ký             : wedding.user.email                                                |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Bộ lọc Trạng thái & Mẫu thiệp Chuyên sâu

Bên cạnh thanh tìm kiếm tự do, nhân viên có thể kết hợp các bộ lọc chuyên biệt để khoanh vùng dữ liệu:

```
+---------------------------------------------------------------------------------------------------+
|  BỘ LỌC DANH SÁCH THIỆP CƯỚI                                                                      |
+---------------------------------------------------------------------------------------------------+
|  [Trạng thái: Tất cả ▼]  |  [Mẫu thiệp: Tất cả mẫu ▼]  |  [Sắp xếp: Mới nhất ▼]   [ ĐẶT LẠI ]     |
+---------------------------------------------------------------------------------------------------+
```

### 3.1. Lọc theo Trạng thái Thiệp (`status`):
* **Đã xuất bản (`status=published`):** Các thiệp cưới đã hoàn thành chỉnh sửa, đã bấm xuất bản trực tuyến và đang mở công khai cho quan khách truy cập.
* **Bản nháp (`status=draft`):** Thiệp cưới mới tạo, cô dâu chú rể đang chỉnh sửa dở dang, chưa công bố liên kết chính thức.
* **Đã hết hạn (`status=expired`):** Thiệp cưới đã qua ngày cưới và vượt quá thời hạn lưu trữ theo gói dịch vụ (`expires_at < now()`).
* **Đang bị khóa (`status=locked`):** Thiệp cưới bị tạm khóa bảo vệ (`custom_config->is_locked = true`) do phát hiện nghi vấn gian lận hoặc gia đình yêu cầu tạm dừng.

### 3.2. Lọc theo Mẫu thiệp (`template_id`):
Cho phép chọn một mẫu cụ thể trong kho giao diện Mây Hẹn (ví dụ: *Celestial Love*, *Dusty Romance*, *Minimalist Ivory*, *Royal Velvet*). Hữu ích khi đội ngũ kỹ thuật cần rà soát toàn bộ các thiệp đang dùng một mẫu nhất định để cập nhật bản sửa lỗi CSS.

---

## 4. Giao diện Danh sách & Các Hành động Nhanh

Mỗi thiệp cưới trong danh sách được hiển thị dưới dạng thẻ thông tin trực quan:

```
+---------------------------------------------------------------------------------------------------+
| [Ảnh Bìa]  Vũ Phong & Thảo Linh (Ngày cưới: 15/11/2026 - Còn 3 ngày)            [ĐÃ XUẤT BẢN]     |
|            Mẫu thiệp: Celestial Love | Gói: Kim Cương (Diamond trọn đời)                           |
|            Chủ tài khoản: Nguyễn Vũ Phong | SĐT: 0988.123.456 | Email: phong.nv@gmail.com          |
|            Link: mayhen.vn/thiep/vu-phong-thao-linh (Lượt xem: 1.420 | 250 khách | 185 RSVP)      |
|                                                                                                   |
|            THAO TÁC: [Xem Chi tiết & Mockup] [Mở thiệp thật ↗] [Gia hạn] [Đổi mẫu] [Khóa thiệp]   |
+---------------------------------------------------------------------------------------------------+
```

### Các nút tác vụ nhanh dành cho nhân viên:
1. **[Xem Chi tiết & Mockup] (`show`):** Mở màn hình giám sát toàn diện kèm khung giả lập điện thoại iPhone 16 Pro thời gian thực.
2. **[Mở thiệp thật ↗]:** Mở tab mới truy cập trực tiếp đường dẫn công khai `/thiep/{slug}` để kiểm tra giao diện khách mời nhìn thấy.
3. **[Khóa thiệp khẩn cấp] (`lock`):** Tạm dừng hiển thị thiệp ngay lập tức nếu phát hiện vi phạm bản quyền hoặc ảnh nhạy cảm.
4. **[Bật/Tắt Xuất bản] (`togglePublish`):** Đổi nhanh trạng thái giữa Draft và Published.
5. **[Sao chép liên kết]:** Copy link thiệp cưới gửi ngay cho khách hàng qua cửa sổ chat.

---

## 5. Quy trình 3 Bước Tra cứu & Xử lý Yêu cầu Khách hàng

Khi có khách hàng liên hệ hotline:

* **Bước 1 (Hỏi thông tin định danh):** Lễ tân hỏi nhẹ nhàng: *"Dạ anh/chị cho em xin số điện thoại đăng ký tài khoản hoặc tên cô dâu chú rể trên thiệp cưới ạ?"*.
* **Bước 2 (Gõ vào ô tìm kiếm):** Nhập chuỗi số điện thoại hoặc tên vào ô tìm kiếm tại `/admin/invitations` và nhấn Enter.
* **Bước 3 (Xác thực an toàn):** Đối chiếu 4 số cuối số điện thoại hoặc địa chỉ email hiển thị trên màn hình với thông tin khách hàng đang cung cấp. Khi trùng khớp 100%, nhân viên mới bắt đầu thực hiện các hỗ trợ chuyên sâu (đổi mẫu, gia hạn, mở khóa).

---

## 6. Bảo mật Dữ liệu & Tuân thủ Luật 91/2025/QH15

* Nhân viên chỉ được tra cứu phục vụ mục đích hỗ trợ kỹ thuật và chăm sóc khách hàng chính đáng.
* Nghiêm cấm xuất khẩu trái phép dữ liệu số điện thoại, danh sách khách mời của cô dâu chú rể sang thiết bị cá nhân.
* Mọi lượt tìm kiếm và truy cập hồ sơ thiệp cưới đều được ghi nhận vào nhật ký kiểm toán hệ thống (`audit_logs`).
