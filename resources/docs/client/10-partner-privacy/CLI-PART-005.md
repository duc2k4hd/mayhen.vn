---
id: CLI-PART-005
title: "Quyền riêng tư theo Luật 91/2025: Cách tải toàn bộ dữ liệu hoặc yêu cầu xóa tài khoản"
category: "Đối tác Studio & Bảo mật"
summary: "Hướng dẫn người dùng thực thi các quyền riêng tư theo Luật Bảo vệ Dữ liệu Cá nhân 91/2025/QH15: cấu hình hiển thị số tài khoản ngân hàng, tải tệp trích xuất dữ liệu (DSAR) và quy trình yêu cầu xóa tài khoản."
reader: "PER-CLI-04 (Studio Cưới & Đối tác)"
order: 5
updated_at: "2026-11-15"
tags: [privacy, law-91-2025, data-protection, dsar-export, account-deletion, right-to-erasure]
mapped_page: "CLI-PART-PRIVACY"
mapped_route: "privacy.show"
risk_level: "HIGH"
required_role: "customer"
---

# Quyền riêng tư theo Luật 91/2025: Cách tải toàn bộ dữ liệu hoặc yêu cầu xóa tài khoản

> [!IMPORTANT]
> **Tuân thủ pháp lý:** Luật Bảo vệ Dữ liệu Cá nhân (Luật số 91/2025/QH15) và Nghị định 13/2023/NĐ-CP của Chính phủ.  
> **Trang cấu hình quyền riêng tư:** `/thiep/{id}/trung-tam-quyen-rieng-tu` (Route: `privacy.show`).  
> **Cam kết của Mây Hẹn:** Không bán dữ liệu, không chia sẻ số điện thoại của cô dâu chú rể hay khách mời cho bên thứ ba cho mục đích quảng cáo rác.

---

## 1. Quyền Của Bạn Đối Với Dữ Liệu Cá Nhân

Trong suốt quá trình chuẩn bị ngày cưới, bạn và gia đình cung cấp nhiều thông tin nhạy cảm (hình ảnh cưới riêng tư, số tài khoản ngân hàng nhận mừng cưới, danh bạ số điện thoại khách mời). Mây Hẹn tôn trọng tuyệt đối quyền làm chủ dữ liệu của bạn thông qua 4 quyền năng cốt lõi:
1. **Quyền được biết & Minh bạch:** Biết rõ dữ liệu của mình được lưu trữ ở đâu, dùng cho mục đích gì và thời hạn lưu giữ bao lâu.
2. **Quyền hạn chế xử lý dữ liệu:** Tự do bật/tắt hiển thị số tài khoản ngân hàng, ẩn lời chúc hoặc kiểm duyệt ảnh trước khi đưa lên màn hình tiệc cưới.
3. **Quyền tiếp cận & Nhận bản sao dữ liệu (DSAR Export):** Quyền tải về trọn bộ toàn bộ dữ liệu thiệp cưới, ảnh cưới và danh sách khách mời bất kỳ lúc nào.
4. **Quyền yêu cầu xóa dữ liệu (Right to Erasure / Quyền được lãng quên):** Quyền yêu cầu xóa vĩnh viễn tài khoản và ẩn danh hóa thông tin sau khi tiệc cưới kết thúc.

![Trung tâm quyền riêng tư và bảo vệ dữ liệu cá nhân theo Luật 91/2025](/assets/client/docs/images/client/10-partner-privacy/cli-part-005-step-1.png)
*Trung Tâm Quyền Riêng Tư: Tuân thủ Luật 91/2025/QH15, hỗ trợ tải bản sao dữ liệu (DSAR) và xóa tài khoản vĩnh viễn.*

---

## 2. Bảng Cấu Hình Quyền Riêng Tư Thiệp Cưới (`privacy.show`)

Truy cập trang quản trị thiệp của bạn và chọn mục **Trung tâm Quyền riêng tư** (`/thiep/{id}/trung-tam-quyen-rieng-tu`):

```
┌────────────────────────────────────────────────────────────────────────┐
│ TRUNG TÂM MINH BẠCH QUYỀN RIÊNG TƯ & BẢO MẬT DỮ LIỆU CÁ NHÂN           │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Hiển thị thông tin mừng cưới & VietQR:                              │
│    (○) Mọi người truy cập thiệp đều xem được                           │
│    (●) Chỉ khách mời có tên đích danh trong link cá nhân mới thấy      │
│    (○) Ẩn hoàn toàn (Khách tự liên hệ nhận số tài khoản)               │
│                                                                        │
│ 2. Phạm vi hiển thị Lời chúc & Sổ ký tên:                              │
│    (○) Công khai cho toàn bộ khách mời cùng đọc                        │
│    (●) Riêng tư - Chỉ cặp đôi và gia đình đọc được                     │
│                                                                        │
│ 3. Kiểm duyệt ảnh Photo Drop:                                          │
│    (●) Phải được cô dâu chú rể duyệt mới hiển thị lên Live Wall        │
│    (○) Tự động hiển thị ngay sau khi khách tải lên                     │
│                                                                        │
│ 4. Thời hạn lưu giữ số điện thoại khách mời (Data Retention):          │
│    [ 30 ngày sau ngày cưới ▾ ] (Hệ thống tự động xóa sạch SĐT)        │
│                                                                        │
│ [ Lưu Cấu Hình Quyền Riêng Tư ]                                        │
└────────────────────────────────────────────────────────────────────────┘
```

- **Bảo vệ tài khoản ngân hàng (`bank_visibility`):** Chọn chế độ *Chỉ khách mời có tên đích danh* để ngăn chặn các đối tượng thu thập dữ liệu tự động ngoài ý muốn.
- **Tự động xóa danh bạ khách mời (`phone_retention_days`):** Lựa chọn thời hạn tự động dọn sạch số điện thoại của khách sau tiệc cưới (**Xóa ngay**, **30 ngày**, **90 ngày** hoặc **180 ngày**).

---

## 3. Cách Tải Về Trọn Bộ Dữ Liệu Cá Nhân (DSAR Export)

Trước khi đóng tiệc cưới, bạn có quyền tải về máy tính toàn bộ kỷ niệm của ngày trọng đại:
1. Tại Trung tâm Quyền riêng tư, nhấp nút **[Yêu cầu xuất toàn bộ dữ liệu (DSAR Export)]**.
2. Hệ thống tạo một tác vụ nền gom dữ liệu:
   - Toàn bộ Album ảnh cưới chất lượng gốc (Full Resolution).
   - Tệp danh sách khách mời và trạng thái tham dự RSVP (định dạng Excel/CSV).
   - Toàn bộ lời chúc phúc và chữ ký kỷ niệm của bạn bè.
   - Lịch sử đối soát tiền mừng VietQR và hóa đơn dịch vụ.
3. Khi tệp nén sẵn sàng, hệ thống gửi liên kết tải về bảo mật vào email của bạn. Đường dẫn có mã token dùng một lần và hết hạn sau 48 giờ để chống tải trộm.

---

## 4. Quy Trình Yêu Cầu Xóa Tài Khoản & Ẩn Danh Hóa

Khi hôn lễ đã hoàn tất viên mãn và bạn muốn dọn sạch mọi thông tin cá nhân khỏi máy chủ:

### Bước 1: Gửi yêu cầu xóa dữ liệu
- Tại trang quản lý tài khoản (`/tai-khoan/cai-dat`), kéo xuống mục **Vùng Nguy Hiểm (Danger Zone)**.
- Nhấp **[Yêu cầu xóa tài khoản và dữ liệu cá nhân]**.
- Nhập mật khẩu hiện tại để xác thực chính chủ và chọn lý do (tùy chọn).
- Nhấp **[Xác nhận yêu cầu xóa]**. Hệ thống tạo một yêu cầu xóa dữ liệu mã hóa (Ví dụ: `ERASURE-2026-XXXX`).

### Bước 2: Thời gian chờ ân hạn (Grace Period 7 ngày)
- Để tránh việc bấm nhầm hoặc kẻ gian chiếm đoạt tài khoản bấm xóa, hệ thống áp dụng thời gian ân hạn **07 ngày**.
- Trong vòng 7 ngày này, bạn có thể đăng nhập lại và nhấp **[Hủy yêu cầu xóa]** bất kỳ lúc nào để khôi phục tài khoản nguyên vẹn.

### Bước 3: Thực thi ẩn danh hóa vĩnh viễn (Anonymization Execution)
Sau 7 ngày, hệ thống kích hoạt tiến trình tự động theo quy chuẩn:
1. **Xóa vĩnh viễn:** Mật khẩu, địa chỉ email, số điện thoại, ảnh đại diện, ảnh cưới trong thư mục `storage/`.
2. **Ẩn danh hóa thiệp cưới:** Tên cô dâu chú rể được thay thế bằng chuỗi ngẫu nhiên không thể phục hồi (Ví dụ: `User_Deleted_#9812`).
3. **Bảo toàn hồ sơ kế toán bắt buộc:** Theo Luật Kế toán và Luật Thuế Việt Nam, các thông tin đơn hàng và hóa đơn điện tử được lưu giữ ẩn danh trong sổ cái kiểm toán và không thể bị sửa đổi.

---

## 5. Những điều Tuyệt đối Bị cấm Đối với Quản trị viên & Đối tác

1. **CẤM** nhân viên hoặc đối tác Studio xuất khẩu dữ liệu số điện thoại khách mời của cặp đôi để bán hoặc cung cấp cho các dịch vụ bảo hiểm, bất động sản, tiệc cưới khác.
2. **CẤM** từ chối yêu cầu xóa tài khoản hợp lệ của người dùng khi đã hết thời gian ân hạn 7 ngày.
3. **CẤM** truy cập trái phép vào các kho ảnh cưới hoặc sổ ký tên riêng tư khi cặp đôi đã chọn chế độ `couple_only`.

---

## 6. Bài viết Liên quan

- [CLI-PART-004: Cổng duyệt thiệp dành cho hai bên gia đình phụ mẫu](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-004.md)
- [CLI-GUEST-001: Quản lý danh sách khách mời và gửi thiệp cá nhân hóa](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/04-guest-rsvp/CLI-GUEST-001.md)
- [ADM-SOP-004: Quy trình xử lý yêu cầu quyền riêng tư và xóa dữ liệu (Dành cho Quản trị viên)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/01-sop-onboarding/ADM-SOP-004.md)
