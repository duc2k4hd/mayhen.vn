---
id: CLI-TIME-001
title: "Hộp thời gian là gì? Khách gửi lời chúc tương lai hẹn ngày mở khóa"
category: "Hộp thời gian & Kỷ niệm Ngày cưới"
summary: "Giới thiệu tính năng Hộp Thời Gian (Digital Wedding Time Capsule): Khái niệm lá thư gửi tương lai, các cột mốc kỷ niệm 1 năm (Giấy), 3 năm (Da), 5 năm (Gỗ), 10 năm (Thiếc), cơ chế niêm phong sáp bảo mật nội dung tuyệt đối và cách khách mời gửi tâm thư hẹn ngày mở khóa."
reader: "PER-CLI-02 / PER-CLI-03"
order: 2
updated_at: "2026-11-15"
tags: [mayhen, hộp-thời-gian, time-capsule, tam-thu-tuong-lai, niem-phong-sap, ky-niem-ngay-cuoi]
mapped_page: "CLI-TIME-PAGE"
mapped_route: "time_capsule.public_slug"
risk_level: "MEDIUM"
required_role: "customer"
---

# Hộp thời gian là gì? Khách gửi lời chúc tương lai hẹn ngày mở khóa

> **Dành cho:** Cô dâu, Chú rể và Khách mời muốn gửi gắm những lời tâm sự đi cùng năm tháng  
> **Thời gian thiết lập:** Khoảng 2 - 3 phút  
> **Đường dẫn hộp thư công cộng:** `mayhen.vn/thiep/{slug}/hop-thoi-gian` (Route: `time_capsule.public_slug`)  
> **API Xử lý:** `time_capsule.guest.store` (`POST /thiep/{slug}/hop-thoi-gian`)

Hầu hết lời chúc đám cưới thông thường chỉ được đọc qua một lần rồi trôi vào quên lãng. Nhưng cuộc sống hôn nhân là một hành trình dài với nhiều cột mốc ý nghĩa. Sau 1 năm chung sống bận rộn hay sau 5 năm khi hai bạn đã có những đứa con đầu lòng, được cùng nhau ngồi đọc lại những bức thư viết từ ngày cưới sẽ là một trải nghiệm xúc động nghẹn ngào.

**Hộp Thời Gian (Digital Wedding Time Capsule)** của Mây Hẹn là chiếc hòm kỷ vật số độc bản: Cho phép cô dâu, chú rể và những người bạn tri kỷ viết những lá tâm thư gửi tới tương lai, được **niêm phong bằng sáp bảo mật** và chỉ mở khóa đúng vào các dịp kỷ niệm ngày cưới trong tương lai.

![Hộp Thời Gian Digital Wedding Time Capsule](/assets/client/docs/images/client/09-time-capsule/cli-time-001-step-1.png)
*Hộp Thời Gian Hôn Lễ: Gửi gắm những lá thư tương lai hẹn ngày mở khóa vào các dịp kỷ niệm đám cưới.*

---

## 1. Năm Cột Mốc Kỷ Niệm Hôn Lễ Ý Nghĩa

Hệ thống tự động tính toán chính xác ngày mở khóa dựa trên ngày cưới chính thức của hai bạn (kể cả trường hợp ngày cưới rơi vào năm nhuận `29/02`):

```
┌────────────────────────────────────────────────────────────────────────┐
│  ⏳ CÁC CỘT MỐC MỞ KHÓA HỘP THỜI GIAN THEO TRUYỀN THỐNG               │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  1 NĂM: ĐÁM CƯỚI GIẤY│ 3 NĂM: ĐÁM CƯỚI DA │ 5 NĂM: ĐÁM CƯỚI GỖ          │
│  (Paper Wedding)    │ (Leather Wedding)  │ (Wooden Wedding)            │
│  Lá thư đầu tiên sau│ Trải qua những va  │ Nền tảng vững chắc, ấm êm   │
│  năm đầu chung sống │ chạm để thấu hiểu  │ và trưởng thành cùng nhau   │
├─────────────────────┼────────────────────┴─────────────────────────────┤
│ 10 NĂM: ĐÁM CƯỚI THIẾC│ 25 NĂM: ĐÁM CƯỚI BẠC (Silver Jubilee)            │
│ (Tin Wedding)       │ Một phần tư thế kỷ son sắt bên người bạn đời     │
└─────────────────────┴──────────────────────────────────────────────────┘
```

---

## 2. Cơ chế Niêm Phong Tuyệt Đối (Zero-Knowledge Seal)

> [!IMPORTANT]
> **Nội dung được bảo vệ kín đáo 100%:**  
> Khi một phong thư được niêm phong, máy chủ Mây Hẹn sẽ mã hóa nội dung và trả về `content: null` trên giao diện web.  
> **Tuyệt đối không một ai (kể cả cô dâu chú rể hay quản trị viên) có thể đọc trộm được nội dung** cho đến đúng ngày mở khóa được ấn định! Điều này tạo nên sự hồi hộp, thiêng liêng và bất ngờ trọn vẹn khi chiếc hòm thời gian mở ra sau nhiều năm.

Mỗi phong thư được niêm phong bằng một dấu sáp màu hoàng gia:
- **Dấu Sáp Ruby (`#8B1E28`):** Tình yêu nồng nàn, bền bỉ.
- **Dấu Sáp Gold (`#D4AF37`):** Thịnh vượng, hạnh phúc vàng son.
- **Dấu Sáp Emerald (`#1C5D3A`):** Bình an, tươi mới và hy vọng.
- **Dấu Sáp Sapphire (`#1A3E6D`):** Trung thành, gắn kết thủy chung.

---

## 3. Hướng dẫn Khách Mời gửi tâm thư vào Hộp Thời Gian

Khách mời truy cập đường dẫn `mayhen.vn/thiep/{slug}/hop-thoi-gian` trên thiệp cưới:

### Bước 1: Chọn cột mốc muốn gửi gắm
- Chọn thời điểm bạn muốn cặp đôi đọc được lá thư này:
  - *Mở sau 1 năm:* Viết lời nhắn nhủ nhắc lại kỷ niệm quậy phá trong ngày cưới hôm nay.
  - *Mở sau 5 năm:* Dự đoán xem lúc đó hai bạn đã có mấy cháu bé, sống ở ngôi nhà như thế nào.

### Bước 2: Soạn tâm thư gửi tương lai
- **Họ tên & Email của bạn:** Nhập email để hệ thống gửi lời nhắc cho bạn khi đến ngày mở khóa.
- **Tiêu đề phong thư:** *Ví dụ: "Gửi vợ chồng Hoàng Dung của 3 năm sau nhé!"*.
- **Nội dung thư:** Độ dài tối đa lên đến `20,000 ký tự`, bạn có thể tha hồ viết những dòng tâm sự xúc động, những lời dặn dò hay thậm chí một bí mật hài hước thuở hẹn hò.

### Bước 3: Đóng dấu sáp niêm phong
- Chọn màu dấu sáp yêu thích và nhấp **[🔒 Niêm Phong Vào Hộp Thời Gian]**.
- Phong thư rơi nhẹ nhàng vào chiếc rương thời gian trên màn hình kèm đồng hồ đếm ngược: *"Còn 365 ngày nữa phong thư này sẽ được mở khóa"*.
