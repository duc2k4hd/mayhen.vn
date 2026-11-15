---
id: CLI-EDIT-011
title: "Chuyển đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Cẩm nang hướng dẫn chuyển đổi mẫu thiệp cưới linh hoạt trong Studio Mây Hẹn: Nguyên lý tách bạch dữ liệu Unified Schema, xem thử mẫu mới với dữ liệu thật của hai bạn và lưu ý về tỷ lệ khung ảnh bìa Smart Focal Point."
reader: "PER-CLI-01 / PER-CLI-02"
order: 16
updated_at: "2026-11-15"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, chuyen-doi-mau, template-engine, bao-toan-du-lieu]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Chuyển đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập

> **Dành cho:** Cô dâu & Chú rể muốn đổi phong cách thiết kế sau khi đã nhập thông tin  
> **Thời gian thao tác:** Khoảng 30 giây đến 1 phút  
> **Vị trí trong ứng dụng:** Thẻ mẫu thiệp hiện tại (Template Active Chip) trên thanh công cụ Studio Topbar  

Sau khi đã dành nhiều thời gian nhập đầy đủ tên họ hai bên gia đình, địa chỉ sảnh tiệc và những dòng tâm sự tình yêu, hai bạn bỗng thấy một mẫu thiệp mới ra mắt của Mây Hẹn đẹp hơn hoặc hợp với hoa cưới hơn. Ở các nền tảng thông thường, việc đổi mẫu thiệp thường đồng nghĩa với việc phải nhập lại toàn bộ từ đầu. Tại Mây Hẹn, nhờ công nghệ **Template Engine Độc Quyền**, hai bạn có thể tự do chuyển đổi giữa hàng chục mẫu thiệp khác nhau chỉ bằng một cú nhấp chuột mà **dữ liệu được bảo toàn nguyên vẹn 100%**.

---

## 1. Nguyên lý bảo toàn dữ liệu độc quyền của Mây Hẹn

Khác với các website kéo-thả thủ công dễ vỡ layout, kiến trúc Mây Hẹn tách rời tuyệt đối giữa **Dữ Liệu Nội Dung** và **Giao Diện Trình Diễn**:

```
┌────────────────────────────────────────────────────────────────────────┐
│             KIẾN TRÚC TÁCH RỜI DỮ LIỆU & GIAO DIỆN MÂY HẸN             │
├────────────────────────────────────────────────────────────────────────┤
│  KHO DỮ LIỆU BẤT BIẾN CỦA BẠN (UNIFIED WEDDING SCHEMA):                │
│  • Tên cặp đôi: Tuấn Minh & Lan Anh                                   │
│  • Ngày cưới: 28/12/2026 (Nhằm 20/11 Bính Ngọ)                        │
│  • Phụ mẫu hai bên, Sảnh tiệc, Link Google Maps                       │
│  • Album 10 ảnh cưới chất lượng cao + Smart Focal Point               │
│  • Tài khoản ngân hàng VietQR NAPAS                                   │
├────────────────────────────────────────────────────────────────────────┤
│                       ▼ CHUYỂN ĐỔI LINH HOẠT ▼                         │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  MẪU HOÀNG GIA      │  MẪU TỐI GIẢN      │  MẪU SÂN VƯỜN RUSTIC        │
│  (ROYAL CLASSIC)    │  (QUIET MINIMAL)   │  (GARDEN BOTANICAL)         │
│  Phong cách hoàng gia│  Nét chữ thanh lịch│  Hoa lá xanh mát, gần gũi   │
└─────────────────────┴────────────────────┴─────────────────────────────┘
```

Mọi mẫu thiệp chuẩn Mây Hẹn đều tuân thủ cùng một hợp đồng dữ liệu (`data-sync-target`). Vì vậy, khi chuyển mẫu, toàn bộ họ tên, ngày giờ, câu chuyện tình yêu và mã QR mừng cưới sẽ tự động "rót" vào đúng vị trí của mẫu mới mà không rơi rụng dù chỉ một ký tự.

---

## 2. Hướng dẫn từng bước chuyển đổi mẫu thiệp

### Bước 1: Mở danh mục chuyển đổi mẫu thiệp
1. Tại màn hình Studio Tạo Thiệp (`/studio/{invitation}`), nhìn lên thanh công cụ trên cùng (Studio Topbar).
2. Nhấp vào thẻ **Mẫu thiệp đang dùng** (ví dụ: *Hoàng Gia Cổ Điển* có kèm hình ảnh thu nhỏ).
3. Hộp thoại **"Thư Viện Mẫu Thiệp Mây Hẹn"** sẽ mở ra ngay trên màn hình.

### Bước 2: Xem thử mẫu mới với dữ liệu thật của chính bạn (Live Preview with My Data)
1. Lướt qua các phong cách thiết kế:
   - **Hoàng Gia & Quý Tộc:** Đậm chất lễ đường trang nghiêm, hoa văn cổ điển.
   - **Hiện Đại & Tối Giản (Quiet Luxury):** Khoảng trắng tinh tế, font chữ thời trang quốc tế.
   - **Sân Vườn & Tự Nhiên (Rustic):** Cành lá xô thơm, tông be và nâu ấm áp.
   - **Truyền Thống Á Đông:** Tông đỏ son may mắn, song hỷ hiện đại.
2. Rê chuột vào mẫu bạn thích và nhấp nút **[Xem Thử Với Dữ Liệu Của Tôi]**:
   - Màn hình mô phỏng điện thoại sẽ hiển thị mẫu thiệp mới ngay lập tức.
   - Điều đặc biệt là **trên mẫu mới đã hiển thị sẵn tên của hai bạn, ngày cưới và ảnh cưới thật** mà bạn vừa nhập, giúp bạn dễ dàng so sánh xem ảnh của mình có hợp với mẫu thiệp này hay không.

### Bước 3: Xác nhận áp dụng mẫu thiệp mới
1. Sau khi đã ưng ý, nhấp nút **[Áp Dụng Mẫu Này Cho Thiệp]** màu vàng kim.
2. Trong vòng chưa đầy **50 mili-giây**:
   - Khung xem trước Live Canvas sẽ tải giao diện mẫu thiệp mới.
   - Bảng màu và font chữ được cập nhật theo gu thẩm mỹ chuẩn mực của mẫu đó.
   - Topbar cập nhật tên mẫu thiệp mới và lưu nháp tự động.

---

## 3. Những lưu ý nhỏ sau khi đổi mẫu thiệp

> [!TIP]
> **Kiểm tra lại điểm nhìn ảnh cưới (Smart Focal Point):**  
> Một số mẫu thiệp sử dụng ảnh bìa khổ dọc (tỷ lệ 3:4 hoặc 9:16), trong khi mẫu khác lại sử dụng ảnh ngang (16:9). Sau khi đổi mẫu, bạn nên vào phần ảnh bìa và nhấp vào ảnh để kiểm tra xem điểm nhìn Smart Focal Point có đang căn đúng khuôn mặt của hai bạn hay không.

> [!NOTE]
> **Số lượng ảnh hiển thị trong Album:**  
> Đa số mẫu thiệp của Mây Hẹn hỗ trợ chuẩn 10 Section với đầy đủ 10 tấm ảnh cưới. Tuy nhiên, một số mẫu tối giản đặc biệt có thể chỉ hiển thị 6 tấm ảnh nổi bật nhất. Đừng lo lắng! Toàn bộ ảnh bạn đã tải lên vẫn nằm an toàn trong Thư viện ảnh cưới và sẽ xuất hiện đầy đủ nếu bạn chuyển lại mẫu album lớn.

---

## 4. Dấu hiệu nhận biết thành công

- Thẻ tên mẫu trên Studio Topbar đổi sang tên mẫu mới.
- Màn hình Live Canvas hiển thị diện mạo, animation mở phong thư và hoa văn trang trí mới.
- Toàn bộ thông tin cô dâu, chú rể, ngày cưới và danh sách sự kiện vẫn hiển thị đầy đủ, chính xác.
- Bác Sĩ Thiệp Cưới bảo toàn điểm số chất lượng hiện tại.

---

## 5. Bài viết liên quan tiếp theo

- [Làm quen giao diện Studio V2: Khung soạn thảo, thanh công cụ và màn hình xem trước](/docs/client/02-studio-editor/CLI-EDIT-001)
- [Căn chỉnh tiêu điểm ảnh cưới Smart Focal Point trong Studio](/docs/client/02-studio-editor/CLI-EDIT-002)
- [So sánh các thay đổi (Publish Diff) và xuất bản thiệp cưới chính thức](/docs/client/02-studio-editor/CLI-EDIT-010)
