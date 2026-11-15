---
id: CLI-WISH-005
title: "Xuất toàn bộ sổ ký tên và lời chúc thành kỷ vật lưu niệm số (Signature Archive)"
category: "Sổ ký tên, Lời chúc & Photo Drop"
summary: "Hướng dẫn xuất bản và lưu giữ Sổ Ký Tên Vàng (Signature Archive & Mosaic Wall): Trình diễn bức tranh ghép chữ ký nghệ thuật (Signature Mosaic), kết xuất file lưu niệm số CSV/PDF chuẩn in ấn và lưu trữ vĩnh viễn bút tích người thân qua năm tháng."
reader: "PER-CLI-03 (Khách mời & Cặp đôi)"
order: 9
updated_at: "2026-11-15"
tags: [mayhen, sổ-ký-tên, signature-archive, mosaic-wall, xuat-luu-niem, ky-vat-so]
mapped_page: "CLI-WISH-PAGE"
mapped_route: "signature_archive.mosaic"
risk_level: "MEDIUM"
required_role: "customer"
---

# Xuất toàn bộ sổ ký tên và lời chúc thành kỷ vật lưu niệm số (Signature Archive)

> **Dành cho:** Cô dâu & Chú rể lưu giữ kỷ vật thiêng liêng sau ngày cưới  
> **Thời gian thực hiện:** Khoảng 3 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Kho Lưu Trữ Chữ Ký (Signature Archive)** (Route: `account.signature_archive.index`)  
> **Bức tranh ghép chữ ký trực tiếp:** `mayhen.vn/thiep/{id}/tranh-ky-ten` (Route: `signature_archive.mosaic`)

Sau ngày cưới, những bông hoa tươi rồi sẽ tàn, những bàn tiệc rồi sẽ tan, nhưng những dòng lưu bút chân thành và từng nét bút ký tay của cha mẹ, người thân và bạn bè sẽ là **kỷ vật vô giá đi cùng hai bạn suốt cả cuộc đời**.

Thay vì để những chữ ký ấy nằm rải rác trên môi trường số, Mây Hẹn cung cấp **Kho Lưu Trữ Bút Tích (Signature Archive)** với công nghệ ghép tranh khảm nghệ thuật **Signature Mosaic Wall** và khả năng kết xuất tài liệu in ấn cao cấp để đóng thành sách kỷ yếu hôn lễ.

---

## 1. Tranh Khảm Chữ Ký Nghệ Thuật (Signature Mosaic Wall)

Khi truy cập vào đường dẫn `mayhen.vn/thiep/{id}/tranh-ky-ten`, toàn bộ chữ ký của quan khách sẽ được hệ thống tự động sắp xếp thành một **Bức Tranh Bút Tích Vàng Khổng Lồ**:

```
┌────────────────────────────────────────────────────────────────────────┐
│                                                                        │
│            ✨ BỨC TRANH BÚT TÍCH HÔN LỄ HOÀNG & DUNG ✨                │
│                                                                        │
│   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   ┌─────────┐ │
│   │ ~ Bác Tuấn ~ │   │  ~ Mai Anh ~ │   │ ~ Hoàng Nam ~│   │ ~ Hà P ~│ │
│   │ [Mực Vàng]   │   │  [Mực Đỏ]    │   │ [Mực Đen]    │   │         │ │
│   └──────────────┘   └──────────────┘   └──────────────┘   └─────────┘ │
│                                                                        │
│               ┌──────────────────────────────────────┐                 │
│               │   CHỮ KÝ TRUNG TÂM CỦA CẶP ĐÔI:     │                 │
│               │       Minh Hoàng   ❤️   Thùy Dung    │                 │
│               │        (Bút tích son sắt một đời)    │                 │
│               │     15 Tháng 11 Năm 2026, Hà Nội     │                 │
│               └──────────────────────────────────────┘                 │
│                                                                        │
│   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   ┌─────────┐ │
│   │ ~ Minh Đức ~ │   │ ~ Chị Lan ~  │   │ ~ Tuấn Anh ~ │   │ ~ Vy ~  │ │
│   └──────────────┘   └──────────────┘   └──────────────┘   └─────────┘ │
│                                                                        │
│    Chủ đề: [ Vàng Kim & Trắng Kem ]  [⛶ Toàn Màn Hình]  [📥 Tải Ảnh 4K]│
└────────────────────────────────────────────────────────────────────────┘
```

### 4 Chủ đề màu sắc mỹ thuật (Mosaic Themes):
1. **Cream & Gold (Trắng kem & Vàng kim):** Thanh lịch, trang nhã, đúng tinh thần Quiet Luxury của Mây Hẹn.
2. **Ivory & Charcoal (Ngọc trai & Than chì):** Hiện đại, tối giản mang phong cách tạp chí nghệ thuật (Editorial).
3. **Obsidian Night (Đêm Huyền Bí):** Nền đen ánh sao, các chữ ký phát sáng vàng kim như bầu trời đầy sao.
4. **Dusty Rose (Hồng tro & Rượu vang):** Nồng nàn, lãng mạn và ngập tràn tình yêu.

---

## 2. Hướng dẫn thêm Chữ Ký của Cô Dâu & Chú Rể vào Trung Tâm Bức Tranh

Để bức tranh ký tên trở nên hoàn mỹ nhất, chính hai bạn sẽ đặt bút ký vào vị trí trái tim của bức tranh:

1. Vào **Quản Lý Thiệp** -> **Kho Lưu Trữ Chữ Ký**.
2. Cuộn đến mục **Chữ Ký Trọng Tâm Cặp Đôi (Centerpiece Signatures)**.
3. Chú rể dùng ngón tay hoặc bút cảm ứng ký vào ô **Chữ Ký Chú Rể**.
4. Cô dâu ký vào ô **Chữ Ký Cô Dâu**.
5. Nhập Lời tựa hôn ước (Wedding Vow Quote): *Ví dụ: "Hạnh phúc là được già đi cùng nhau"*.
6. Bấm **[Lưu Chữ Ký Cặp Đôi]**. Bức tranh Mosaic sẽ tự động tái cấu trúc, đưa chữ ký của hai bạn vào vị trí trang trọng nhất giữa muôn ngàn chữ ký chúc phúc của bạn bè.

---

## 3. Xuất Sổ Ký Tên thành File Lưu Trữ (CSV & PDF Print)

Mây Hẹn hỗ trợ bạn lưu trữ kỷ vật dưới 2 định dạng linh hoạt:

### Cách 1: Xuất file Excel / CSV đầy đủ dữ liệu
1. Nhấp nút **[📥 Xuất Dữ Liệu Sổ Ký Tên (CSV)]**.
2. File tải về sử dụng chuẩn `UTF-8 with BOM` chống lỗi font tiếng Việt, bao gồm:
   - Họ tên và xưng hô của từng khách.
   - Nhóm khách mời (Họ hàng, Bạn bè, Đồng nghiệp).
   - Nội dung lời chúc nguyên văn.
   - Phản hồi cảm ơn của cô dâu chú rể.
   - Đường link tải file ảnh chữ ký gốc độ nét cao.

### Cách 2: In ấn thành Sách Kỷ Yếu A4 (Keepsake Photobook Print)
1. Mở màn hình Tranh Ký Tên Mosaic -> Nhấp nút **[🖨️ In Khổ Lớn / Tải Ảnh 4K]**.
2. Hệ thống kết xuất file ảnh siêu nét 4K (3840 x 2160 pixels) hoặc file PDF A3/A4.
3. Bạn có thể mang file này ra các xưởng in ảnh chuyên nghiệp để in lụa, ép gỗ tráng gương hoặc lồng khung kính treo trong phòng khách tân hôn như một bức tranh nghệ thuật độc bản.

---

## 4. Bảo tồn dữ liệu Vĩnh Viễn (Digital Immortality)

> [!TIP]
> **Không bao giờ bị thất lạc:**  
> Khác với những cuốn sổ giấy có thể bị ố vàng, ẩm mốc hoặc thất lạc qua những lần chuyển nhà, Signature Archive trên Mây Hẹn được mã hóa và lưu trữ an toàn trên đám mây. Cứ mỗi dịp kỷ niệm 1 năm, 5 năm hay 10 năm ngày cưới, hai bạn đều có thể mở lại để cùng nhau đọc lại những dòng chúc phúc thiêng liêng thuở ban đầu!
