---
id: CLI-WISH-004
title: "Kiểm duyệt ảnh khách gửi trước khi cho phép chiếu lên màn hình sân khấu"
category: "Sổ ký tên, Lời chúc & Photo Drop"
summary: "Hướng dẫn cặp đôi kiểm duyệt kho ảnh Photo Drop: Phân loại ảnh đã duyệt và chờ duyệt, tính năng duyệt hàng loạt 1 chạm (Approve All), ẩn hoặc xóa vĩnh viễn ảnh nhạy cảm/mờ nhòe và thiết lập chế độ kiểm duyệt bảo vệ màn LED sân khấu."
reader: "PER-CLI-03 (Khách mời & Cặp đôi)"
order: 8
updated_at: "2026-11-15"
tags: [mayhen, sổ-ký-tên, photo-drop, kiem-duyet-anh, stage-moderation, delete-photo]
mapped_page: "CLI-WISH-PAGE"
mapped_route: "photo_drop.toggleApproval"
risk_level: "MEDIUM"
required_role: "customer"
---

# Kiểm duyệt ảnh khách gửi trước khi cho phép chiếu lên màn hình sân khấu

> **Dành cho:** Cô dâu, Chú rể và Phù dâu/Phù rể được ủy quyền kiểm duyệt ảnh  
> **Thời gian thao tác:** Khoảng 1 - 2 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Album Ảnh Khách Gửi** (Route: `photo_drop.index`)  
> **API Xử lý:** `photo_drop.toggleApproval` (`POST .../duyet`) & `photo_drop.destroy` (`DELETE .../{photoId}`)

Khi kích hoạt tính năng Photo Drop tại sảnh tiệc, hàng chục vị khách sẽ thi nhau chụp ảnh và tải lên. Trong số đó, bên cạnh những bức ảnh lung linh sẽ có những ảnh bị rung mờ, chụp trúng khoảnh khắc ăn uống chưa chỉn chu, hoặc ảnh trêu đùa của bạn thân.

Để đảm bảo hình ảnh hiển thị trên Màn hình LED sân khấu (Stage Live Wall) luôn đẹp mắt, trang trọng và làm hài lòng các bậc phụ huynh lớn tuổi, Mây Hẹn cung cấp **Phân Hệ Kiểm Duyệt Ảnh Khách Gửi (Photo Moderation Desk)** với đầy đủ công cụ lọc, duyệt 1 chạm và xóa triệt để.

---

## 1. Hai chế độ vận hành Kiểm duyệt (Auto-Approve vs Manual Gate)

Trong trang cài đặt Photo Drop, bạn có thể chọn 1 trong 2 chế độ:

```
CHẾ ĐỘ 1: TỰ ĐỘNG HIỂN THỊ (AUTO-APPROVE = TRUE) - KHUYẾN NGHỊ CHO TIỆC BẠN BÈ
Khách tải ảnh ──────> Tức thời xuất hiện trên Màn LED sân khấu & Album thiệp
(Phù hợp với tiệc cưới phong cách trẻ trung, After-Party sôi động)

CHẾ ĐỘ 2: BẮT BUỘC DUYỆT (MANUAL GATE = FALSE) - AN TOÀN TUYỆT ĐỐI
Khách tải ảnh ──────> Trạng thái "Chờ Duyệt" (Pending)
                            │
                            ▼ Phù dâu / Bạn thân mở điện thoại bấm [Duyệt]
                      Mới được phép bay lên Màn LED sân khấu & Album công khai
```

---

## 2. Bảng điều khiển Studio Quản Lý Ảnh Khách

Truy cập **Quản Lý Thiệp** -> Chọn thiệp -> Nhấp menu **Album Ảnh Khách (Photo Drop)**:

```
┌────────────────────────────────────────────────────────────────────────┐
│  📸 QUẢN LÝ ẢNH KHÁCH GỬI (PHOTO MODERATION DESK)                      │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│    TỔNG SỐ ẢNH      │     ĐÃ DUYỆT       │          CHỜ DUYỆT          │
│       142 Ảnh       │      128 Ảnh       │           14 Ảnh            │
│    (Dung lượng 420MB)│ (Đang chiếu LED)   │     (Cần duyệt trước)       │
├─────────────────────┴────────────────────┴─────────────────────────────┤
│  Bộ lọc: [ Tất cả (142) ]  [ Chờ duyệt (14) ]  [ Đã ẩn (3) ]           │
│  Hành động nhanh: [ ✅ Duyệt Tất Cả Ảnh Chờ (Approve All) ]            │
└────────────────────────────────────────────────────────────────────────┘
```

Mỗi bức ảnh khách gửi sẽ được hiển thị dạng thẻ lưới (Grid Card) với các thông tin minh bạch:
- Ảnh thu nhỏ độ phân giải cao.
- **Tên người gửi:** Ví dụ: *Nguyễn Thùy Chi*.
- **Thời gian gửi:** *18:45 (cách đây 3 phút)*.
- **Kích thước file:** *3.2 MB*.
- **Trạng thái:** Nhãn màu xanh `[Đã Duyệt]` hoặc nhãn vàng cam `[Chờ Duyệt]`.

---

## 3. Thao tác kiểm duyệt từng bức ảnh

### 1. Duyệt cho phép hiển thị lên màn hình LED (`toggleApproval`)
- Nhấp vào nút **[👁️ Duyệt hiển thị]** trên thẻ ảnh.
- Nhãn chuyển sang màu xanh lá: Bức ảnh lập tức được đưa vào luồng chiếu tự động của Live Wall và xuất hiện trên album thiệp công cộng.
- Nếu muốn gỡ bức ảnh xuống sau đó, chỉ cần bấm **[Ẩn ảnh]** một lần nữa.

### 2. Duyệt hàng loạt chỉ trong 1 chạm (`approveAll`)
- Trong khung giờ đón khách đông, nếu thấy các bức ảnh chụp ở photobooth đều đẹp và an toàn, bạn không cần bấm duyệt từng cái.
- Nhấp nút **[✅ Duyệt Toàn Bộ Ảnh Chờ]** trên đầu trang. Hệ thống sẽ phê duyệt cùng lúc toàn bộ 10 - 20 bức ảnh chỉ trong 1 giây.

### 3. Xóa vĩnh viễn ảnh khỏi hệ thống (`destroy`)
- Nếu phát hiện bức ảnh bị nhòe đen hoặc hình ảnh không phù hợp thuần phong mỹ tục:
  1. Nhấp biểu tượng thùng rác **[🗑️ Xóa vĩnh viễn]**.
  2. Hệ thống bật popup xác nhận để tránh bấm nhầm.
  3. Khi xác nhận, hệ thống sẽ **xóa sạch file ảnh gốc khỏi ổ cứng máy chủ** (`Storage::disk('public')->delete()`) và xóa bản ghi trong cơ sở dữ liệu. Bức ảnh hoàn toàn biến mất vĩnh viễn.

---

## 4. Phân quyền cho Phù dâu / Bạn thân kiểm duyệt giúp cô dâu chú rể

Trong ngày cưới, cô dâu chú rể rất bận rộn chụp ảnh và làm lễ, không thể liên tục cầm điện thoại kiểm duyệt ảnh. Bạn nên ủy quyền cho 1 người bạn thân hoặc phù dâu:

1. Đăng nhập tài khoản thiệp cưới trên điện thoại của phù dâu trước giờ đón khách.
2. Mở sẵn đường link `/tai-khoan/thiep/{id}/album-khach`.
3. Phù dâu thỉnh thoảng mở tab **[Chờ duyệt]**, lướt qua các ảnh mới gửi và nhấp duyệt nhanh. Đám cưới vừa có không khí tương tác náo nhiệt, vừa đảm bảo hình ảnh trên sân khấu luôn chỉn chu nhất.
