---
id: CLI-INVT-002
title: "Tạo mã QR thiệp cưới để in lên thiệp giấy hoặc thiệp kèm (Companion Card)"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Hướng dẫn tạo và tải mã QR thiệp cưới độ phân giải cao (Vector SVG & 300 DPI PNG) để in ấn thiệp giấy truyền thống, thẻ kẹp Companion Card, standee đón khách và quy chuẩn kích thước quét an toàn."
reader: "PER-CLI-02 / PER-CLI-03"
order: 18
updated_at: "2026-11-15"
tags: [mayhen, thiep-cuoi-cong-cong, ma-qr-thiep-cuoi, in-thiep-giay, companion-card, vector-svg]
mapped_page: "CLI-INVT-PAGE"
mapped_route: "invitation.show"
risk_level: "HIGH"
required_role: "customer"
---

# Tạo mã QR thiệp cưới để in lên thiệp giấy hoặc thiệp kèm (Companion Card)

> **Dành cho:** Cô dâu & Chú rể in thiệp giấy truyền thống nhưng muốn kết hợp trải nghiệm số hiện đại  
> **Thời gian thực hiện:** Khoảng 2 - 3 phút  
> **Vị trí trong ứng dụng:** Dashboard Quản lý Thiệp → Tab **[Mã QR & In Ấn]** hoặc Studio Topbar → Nút **[Tải Mã QR]**  

Xu hướng cưới hiện đại ngày nay là sự kết hợp hài hòa giữa nét trang trọng của **thiệp giấy truyền thống** gửi tận tay người lớn tuổi, và sự tiện lợi của **thiệp cưới số** dành cho giới trẻ. Bằng cách in một mã QR nhỏ nhắn, trang nhã lên phong bao hoặc một chiếc thẻ kẹp xinh xắn (Companion Card), khách mời chỉ cần bật camera điện thoại là có thể xem album ảnh cưới trọn vẹn, nghe bản tình ca lãng mạn và bấm định vị dẫn đường đến tận sảnh tiệc.

---

## 1. Hai loại mã QR hỗ trợ trên Mây Hẹn

```
┌────────────────────────────────────────────────────────────────────────┐
│                   HAI HÌNH THỨC MÃ QR TRÊN MÂY HẸN                     │
├────────────────────────────────────┬───────────────────────────────────┤
│    1. MÃ QR THIỆP CHUNG            │    2. MÃ QR CÁ NHÂN HÓA           │
│       (PUBLIC QR CODE)             │    (COMPANION CARD QR)            │
│                                    │                                   │
│ • Dẫn tới: mayhen.vn/thiep/{slug}  │ • Dẫn tới: mayhen.vn/thiep/{slug} │
│ • In hàng loạt trên phôi thiệp     │   kèm mã định danh từng khách     │
│ • Dùng cho standee, backdrop tiệc  │ • Mở ra tên riêng: "Thân gửi..."  │
│ • Quét không giới hạn số lượng     │ • Tự động nhận diện nhóm bàn tiệc │
└────────────────────────────────────┴───────────────────────────────────┘
```

1. **Mã QR Thiệp Chung (Public QR):** Dành cho việc in hàng loạt trên hàng trăm tấm thiệp giấy hoặc in trên bảng đón khách tại sảnh cưới. Bất kỳ ai quét cũng đều mở ra trang thiệp cưới chung của hai bạn.
2. **Mã QR Cá Nhân Hóa (Personalized Companion Card):** Được sinh tự động trong phân hệ *Quản lý Khách mời*. Mỗi mã QR dành riêng cho một vị khách hoặc một gia đình, khi quét sẽ hiện chính xác đại từ xưng hô tôn kính (ví dụ: *Kính mời Chú Bảy và Gia Đình*) và tự động điền form xác nhận tham dự.

---

## 2. Hướng dẫn tạo và tải mã QR chất lượng cao

### Bước 1: Mở công cụ tạo mã QR
1. Tại màn hình Studio hoặc Dashboard quản lý thiệp cưới.
2. Nhấp vào mục **[Mã QR & Bản In]** (biểu tượng mã QR).
3. Hệ thống sẽ hiển thị bản xem trước mã QR được thiết kế riêng theo phong cách của thiệp cưới bạn đang dùng.

### Bước 2: Tùy biến kiểu dáng thẩm mỹ (Chic Customizer)
Mây Hẹn không sử dụng những khối đen vuông vức thô kệch, mà cung cấp các tùy chọn tinh tế:
- **Logo / Monogram tâm điểm:** Tự động chèn chữ lồng tên hai bạn (ví dụ: *T ♡ L*) hoặc hình con dấu sáp Mây Hẹn vào chính giữa mã QR.
- **Màu sắc mã QR:**
  - *Đen tuyền cổ điển (`#30292B`):* Dễ quét nhất trên mọi loại giấy.
  - *Nâu đỏ rượu vang (`#613B3E`):* Đồng bộ hoàn hảo với bộ nhận diện Mây Hẹn.
  - *Vàng champagne (`#B58E6F`):* Sang trọng, phù hợp thiệp giấy phong cách hoàng gia.
- **Khung viền trang nhã:** Chọn viền hoa lá nguyệt quế hoặc dòng chữ hướng dẫn uốn lượn: *"Quét để xem thiệp cưới & dẫn đường"*.

### Bước 3: Tải file chuẩn in ấn chuyên nghiệp (Print-Ready)
Hệ thống cung cấp 2 định dạng file tải về:
1. **File vector `.SVG` (Khuyến nghị cho nhà in):**
   - Định dạng vector toán học, không bao giờ bị vỡ hạt hay mờ khi phóng to.
   - Nhà in thiệp cưới có thể nhập trực tiếp vào phần mềm thiết kế như *Adobe Illustrator, CorelDraw* để ép nhũ kim (Foil Stamping) hoặc dập nổi (Embossing).
2. **File hình ảnh `.PNG` siêu nét (300 DPI):**
   - Độ phân giải cực cao 2000 x 2000 pixel, nền trong suốt (Transparent Background).
   - Bạn có thể tự chèn vào file Word, Canva hoặc Photoshop để tự in tại nhà.

---

## 3. Tiêu chuẩn kích thước in ấn an toàn (Print Safe Guidelines)

Để đảm bảo mọi khách mời (kể cả người lớn tuổi dùng điện thoại có camera đời cũ) đều quét mã nhanh chóng và dễ dàng, vui lòng tuân thủ các quy tắc sau:

| Vị trí in | Kích thước khuyến nghị | Khoảng cách quét lý tưởng |
| :--- | :---: | :---: |
| **Thẻ kẹp thiệp kèm (Companion Card)** | `2.5 cm x 2.5 cm` | 15 – 25 cm |
| **Mặt sau phong bì thiệp giấy** | `2.0 cm x 2.0 cm` (Tối thiểu 1.8 cm) | 15 – 20 cm |
| **Standee đón khách tại sảnh tiệc** | `15 cm x 15 cm` | 1 – 2 mét |
| **Bảng để bàn lễ tân / Bàn Gallery** | `6 cm x 6 cm` | 30 – 50 cm |

> [!IMPORTANT]
> **Quy tắc lề an toàn (Quiet Zone):**  
> Tuyệt đối không để hoa văn, chữ viết hay viền ren thiệp giấy lấn sát vào mép của mã QR. Luôn chừa một khoảng trống trắng (lề an toàn) tối thiểu **3mm** xung quanh cả 4 cạnh của mã QR để ống kính camera nhận diện được mã vạch.

---

## 4. Kiểm tra trước khi gửi xưởng in hàng loạt

Trước khi đặt in hàng trăm tấm thiệp giấy:
1. Tải file mã QR về và in thử **1 bản duy nhất** trên giấy thường bằng máy in văn phòng đúng kích thước thật.
2. Dùng từ 2 đến 3 chiếc điện thoại khác nhau (cả iPhone và điện thoại Android):
   - Mở ứng dụng **Camera chụp ảnh mặc định** và quét thử.
   - Thử nghiệm quét trong điều kiện ánh sáng phòng bình thường và ánh sáng vàng ấm.
3. Nếu màn hình điện thoại hiện lên link thiệp trong vòng **1 giây**, bạn có thể hoàn toàn yên tâm gửi file cho xưởng in thiệp cưới!

---

## 5. Bài viết liên quan tiếp theo

- [Cài đặt mật khẩu bảo vệ thiệp cưới riêng tư và cách người thân mở khóa](/docs/client/03-public-invitation/CLI-INVT-003)
- [Hướng dẫn khách lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)](/docs/client/03-public-invitation/CLI-INVT-004)
- [Cài đặt chỉ đường Google Maps chính xác đến sảnh tiệc nhà trai, nhà gái](/docs/client/03-public-invitation/CLI-INVT-005)
