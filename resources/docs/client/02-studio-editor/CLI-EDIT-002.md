---
id: CLI-EDIT-002
title: "Căn chỉnh tiêu điểm ảnh cưới và Smart Focal Point trong Studio V2"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn thay đổi ảnh cưới, căn chỉnh điểm nhìn trọng tâm (Smart Focal Point) theo tỷ lệ 9:16, 1:1, 4:3, 16:9 và tối ưu hóa hình ảnh chuẩn định dạng WebP sắc nét."
reader: "Cô dâu, chú rể"
order: 2
updated_at: "2026-11-16"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, anh-cuoi, smart-focal-point]
mapped_page: "CLI-STUDIO-PAGE"
mapped_route: "account.focal_point.index"
risk_level: "LOW"
required_role: "customer"
---

# Căn chỉnh tiêu điểm ảnh cưới và Smart Focal Point trong Studio V2

> **Dành cho:** Cô dâu & Chú rể muốn ảnh cưới hiển thị trọn vẹn, không bị cắt mất mặt trên mọi thiết bị  
> **Thời gian thực hiện:** Khoảng 1 - 2 phút  
> **Vị trí trong ứng dụng:** Studio Tạo Thiệp V2 -> Mục **Ảnh Cưới & Ảnh Cổng** hoặc công cụ **Smart Focal Point** (`/tai-khoan/thiep/{id}/tieu-cu-anh`)  

Ảnh cưới chụp ngang hay chụp dọc khi đưa lên màn hình điện thoại thường có nguy cơ bị khung hình tự động cắt cúp (*crop*), dẫn đến việc khuôn mặt cô dâu chú rể bị che khuất hoặc lệch khỏi trọng tâm. Công cụ **Smart Focal Point (Tiêu Điểm Thông Minh)** độc quyền của Mây Hẹn cho phép bạn chỉ định chính xác điểm nhìn ưu tiên của bức ảnh, đảm bảo khuôn mặt của hai bạn luôn tỏa sáng rạng ngời ở vị trí trung tâm trên mọi tỷ lệ màn hình từ điện thoại dọc đến máy tính bảng và màn hình lớn.

![Giao diện thay đổi ảnh cưới và căn chỉnh điểm nhìn Smart Focal Point](/assets/client/docs/images/client/02-studio-editor/cli-edit-002-step-1.png)
*Công cụ Smart Focal Point: Điều chỉnh điểm nhìn trọng tâm giúp ảnh cưới không bị cắt mất mặt trên mọi tỷ lệ màn hình.*

---

## 1. Cơ chế hoạt động của Smart Focal Point

Khi bạn tải một bức ảnh cưới lên hệ thống, Mây Hẹn không ép bức ảnh vào một khung cứng nhắc, mà áp dụng thuộc tính CSS `object-position: X% Y%`:
- Bạn di chuyển tâm ngắm đến vị trí khuôn mặt hoặc chi tiết thiêng liêng nhất của bức ảnh (ví dụ: khoảnh khắc trao nhẫn, nụ hôn ngọt ngào).
- Tọa độ `(X, Y)` sẽ được lưu lại vào cơ sở dữ liệu.
- Khi khách mở thiệp trên màn hình điện thoại tỷ lệ 9:16, hệ thống sẽ tự động căn theo tọa độ này để giữ trọn vẹn gương mặt cô dâu chú rể.
- Khi xem trên máy tính tỷ lệ 16:9, khung hình mở rộng sang hai bên một cách tự nhiên.

---

## 2. Các bước căn chỉnh tiêu điểm ảnh

1. Trong Studio Tạo Thiệp V2 hoặc mục quản lý thiệp, mở công cụ **Căn Chỉnh Tiêu Điểm Ảnh**.
2. Chọn bức ảnh cưới bạn muốn căn chỉnh (ảnh bìa chính hoặc ảnh cổng tiệc cưới).
3. Nhấp chuột hoặc chạm ngón tay vào vị trí khuôn mặt trên bức ảnh để di chuyển tâm ngắm màu vàng kim.
4. Quan sát 4 khung mô phỏng trực quan bên dưới tương ứng với 4 tỷ lệ phổ biến:
   - **Tỷ lệ 9:16:** Màn hình điện thoại iPhone/Android dọc.
   - **Tỷ lệ 1:1:** Khung hình vuông Instagram.
   - **Tỷ lệ 4:3:** Màn hình máy tính bảng iPad.
   - **Tỷ lệ 16:9:** Màn hình laptop và TV trình chiếu sân khấu.
5. Nhấp nút **[Lưu Tiêu Điểm]**. Để đưa tâm ngắm về chính giữa bức ảnh, bạn có thể bấm **[Đặt lại (50%, 50%)]**.
6. Mở chế độ xem thử để kiểm tra kết quả thực tế trên thiệp cưới.

---

## 3. Chuẩn hóa hình ảnh tự động (Client-side Optimizer)

> [!NOTE]
> **Tự động chuyển đổi định dạng WebP:**  
> Trình tối ưu hóa ảnh gốc của Mây Hẹn tự động nén ảnh cưới từ JPG/PNG sang định dạng WebP thế hệ mới ngay trên trình duyệt trước khi tải lên máy chủ. Dung lượng ảnh giảm đến 70% trong khi độ sắc nét được bảo toàn 100%, giúp thiệp cưới mở nhanh tức thì trên mạng di động 4G.

> [!TIP]
> **Giữ nguyên góc xoay chuẩn EXIF:**  
> Hệ thống tự động đọc siêu dữ liệu hướng chụp của điện thoại (EXIF Orientation), ngăn chặn triệt để tình trạng ảnh chụp bằng iPhone bị xoay ngang khi tải lên thiệp cưới.

---

Xem tiếp bài viết:
- [Làm quen giao diện Studio Tạo Thiệp V2](/docs/studio-editor/CLI-EDIT-001)
- [Bác sĩ kiểm tra chất lượng thiệp cưới trước khi xuất bản](/docs/studio-editor/CLI-EDIT-009)
- [Quản lý album ảnh cưới 10 tấm](/docs/studio-editor/CLI-EDIT-005)

