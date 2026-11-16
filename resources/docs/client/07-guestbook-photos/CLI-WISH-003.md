---
id: CLI-WISH-003
title: "Tính năng Photo Drop: Khách mời quét QR tải ảnh chụp tại đám cưới lên album chung"
category: "Sổ ký tên, Lời chúc & Photo Drop"
summary: "Hướng dẫn cài đặt và trải nghiệm Hộp Ảnh Kỷ Niệm (Live Photo Drop): Khách mời tại bàn tiệc quét QR tải thẳng ảnh chụp từ điện thoại lên album đám cưới, hỗ trợ định dạng iPhone HEIC/WebP/JPG tới 12MB, không cần cài app và trình chiếu tức thì lên màn hình sân khấu."
reader: "PER-CLI-03 (Khách mời & Cặp đôi)"
order: 7
updated_at: "2026-11-15"
tags: [mayhen, sổ-ký-tên, photo-drop, album-chung, upload-anh, heic-support]
mapped_page: "CLI-WISH-PAGE"
mapped_route: "photo_drop.show"
risk_level: "MEDIUM"
required_role: "customer"
---

# Tính năng Photo Drop: Khách mời quét QR tải ảnh chụp tại đám cưới lên album chung

> **Dành cho:** Quan khách dự tiệc và Cặp đôi muốn thu thập toàn bộ ảnh chụp tự nhiên trong ngày cưới  
> **Thời gian tải ảnh:** Khoảng 15 - 30 giây / lượt tải  
> **Đường dẫn tải ảnh:** `mayhen.vn/photo-drop/{slug}` (Route: `photo_drop.show`)  
> **API Xử lý:** `photo_drop.upload` (`POST /api/photo-drop/{slug}/upload`)

Trong ngày trọng đại, thợ ảnh chính (Photographer) chỉ có thể ghi lại một phần nhỏ các khoảnh khắc. Hàng trăm bức ảnh tự nhiên, vui nhộn và xúc động nhất lại nằm trong điện thoại của bạn bè, họ hàng ngồi tại các bàn tiệc. Thông thường sau đám cưới, cặp đôi phải nhắn tin hỏi từng người để "xin ảnh" qua Zalo/AirDrop rất mất thời gian và bị giảm chất lượng ảnh.

Tính năng **Photo Drop (Hộp Nhận Ảnh Trực Tiếp)** của Mây Hẹn giải quyết trọn vẹn bài toán này: Chỉ cần quét mã QR đặt trên bàn tiệc, khách mời có thể tải ngay 10 bức ảnh sắc nét từ điện thoại lên album chung của đám cưới mà không cần tạo tài khoản hay cài đặt bất kỳ ứng dụng nào.

![Tính năng Photo Drop tải ảnh chụp lên album chung](/assets/client/docs/images/client/07-guestbook-photos/cli-wish-003-step-1.png)
*Photo Drop: Quét QR tại bàn tiệc để chia sẻ khoảnh khắc ảnh cưới trực tiếp lên album kỷ niệm.*

---

## 1. Trải nghiệm của Khách Mời tại Bàn Tiệc

```
[ KHÁCH MỜI TẠI BÀN TIỆC ]                      [ MÁY CHỦ MÂY HẸN CLOUD ]
Khách quét mã QR trên Thẻ Bàn                   ┌────────────────────────────────────┐
(Table Tent Card) hoặc Màn LED                  │  ✨ ALBUM ẢNH KỶ NIỆM ĐÁM CƯỚI     │
             │                                  │     (Đã nhận: 184 bức ảnh)         │
             ▼                                  ├────────────────────────────────────┤
┌───────────────────────────────────────┐       │  [Ảnh 1] Chụp selfie bàn cấp 3     │
│ 📸 GỬI ẢNH KỶ NIỆM TỚI HOÀNG & DUNG   │       │  [Ảnh 2] Khoảnh khắc cắt bánh cưới │
├───────────────────────────────────────┤ ────> │  [Ảnh 3] Nâng ly chúc mừng         │
│  Tên bạn: [ Nguyễn Thùy Chi      ]    │       └────────────────────────────────────┘
│  Lời tựa: [ Quẩy hết mình nha! 🥂 ]   │                          │
│                                       │                          ▼
│  [ + CHỌN ẢNH TỪ ĐIỆN THOẠI ]         │         Tự động xuất hiện trên Màn LED
│  (Chọn tối đa 10 ảnh / lần tải)       │         sân khấu (Live Wall) trong 8 giây!
└───────────────────────────────────────┘
```

1. **Hỗ trợ định dạng ảnh gốc iPhone (HEIC/HEIF):**  
   - Đa số khách dùng iPhone chụp ảnh ở định dạng HEIC chất lượng cao. Bộ giải mã của Mây Hẹn tự động tương thích 100% với HEIC, JPG, PNG và WebP, không bao giờ báo lỗi định dạng không hỗ trợ.
2. **Dung lượng tối đa lên đến 12MB / ảnh:**  
   - Giữ nguyên độ sắc nét và màu sắc gốc của bức ảnh, không bị nén vỡ hạt như khi gửi qua mạng xã hội thông thường.
3. **Chống gửi trùng lặp (Double-submit Protection):**  
   - Nếu khách vô tình bấm gửi 2 lần khi mạng sảnh tiệc chập chờn, thuật toán kiểm tra kích thước và chữ ký ảnh trong 5 phút sẽ tự động loại bỏ bản ghi trùng, tiết kiệm dung lượng lưu trữ.

---

## 2. Các bước tải ảnh dành cho Khách Mời

### Bước 1: Quét mã QR tại sảnh hoặc bàn tiệc
- Dùng camera điện thoại quét mã QR in trên Thẻ đặt bàn (Table Card) hoặc quét mã QR ở góc Màn hình chiếu sân khấu (Live Wall).
- Trình duyệt sẽ mở ngay trang `/photo-drop/{slug}`.

### Bước 2: Nhập tên & Lời nhắn kỷ niệm
- Nhập tên của bạn (ví dụ: *Chi bạn cô dâu*) để cặp đôi biết ai là tác giả của những bức ảnh đáng yêu này.
- Thêm một câu chú thích vui vẻ (Caption) ngắn gọn.

### Bước 3: Chọn ảnh từ Thư viện (Photo Library) hoặc Chụp trực tiếp
- Bấm nút **[+ Chọn ảnh kỷ niệm]**.
- Chọn từ 1 đến 10 bức ảnh đẹp nhất vừa chụp trong sảnh tiệc.
- Bấm **[Tải ảnh lên ngay]**. Thanh tiến trình (Upload Progress) sẽ chạy từ 0% đến 100% và hiện thông báo xác nhận thành công.

---

## 3. Cách Cặp Đôi quản lý và chia sẻ Photo Drop

Trong trang **Quản Lý Thiệp** -> Chọn thiệp -> **Album Ảnh Khách Gửi**:

1. **Tải mã QR Photo Drop in đặt bàn tiệc:**
   - Hệ thống cung cấp sẵn file thiết kế mã QR chuẩn in ấn vector A6/A5. Bạn có thể in ra và lồng vào khung mica đặt tại từng bàn tiệc trước giờ đón khách.
2. **Bật / Tắt nhận ảnh linh hoạt:**
   - Sau khi đám cưới kết thúc vài tuần, bạn có thể gạt công tắc **Đóng hộp nhận ảnh** (`enabled = false`) để chốt album kỷ niệm.
3. **Tải toàn bộ ảnh gốc về máy tính (Download All as ZIP):**
   - Chỉ với 1 nhấp chuột vào nút **[📦 Tải file ZIP toàn bộ ảnh]**, hệ thống sẽ nén tất cả hàng trăm bức ảnh khách chụp vào một file nén duy nhất để bạn lưu trữ vào ổ cứng trọn đời.

---

## 4. Xử lý các tình huống thường gặp

> [!TIP]
> **Nhắc MC thông báo trong tiệc cưới:**  
> Trước khi cô dâu chú rể đi từng bàn nâng ly, hãy nhờ MC nhắc nhẹ: *"Mời quý quan khách quét mã QR trên bàn để gửi những bức ảnh selfie rực rỡ nhất lên màn hình LED sân khấu nhé!"*. Bàn tiệc sẽ trở nên vô cùng sôi động và tràn ngập tiếng cười.
