---
id: CLI-SEAT-005
title: "Tính năng Tra cứu bàn tiệc thông minh (Table Finder) dành cho khách khi đến sảnh"
category: "Sơ đồ Bàn tiệc & Chỗ ngồi"
summary: "Hướng dẫn cài đặt và vận hành Hệ thống Tra cứu bàn tiệc thông minh (Table Finder): Tìm số bàn nhanh qua họ tên hoặc số điện thoại, tự động định vị chỗ ngồi qua link thiệp mời cá nhân hóa (?guest=token), hiển thị danh sách bạn cùng bàn (Table Mates) và hướng dẫn lễ tân đón tiếp."
reader: "PER-CLI-02 (Cặp đôi đang quản lý tiệc)"
order: 10
updated_at: "2026-11-15"
tags: [mayhen, sơ-đồ-bàn-tiệc-&-chỗ-ngồi, table-finder, tra-cuu-ban-tiec, reception-kiosk]
mapped_page: "CLI-SEAT-PAGE"
mapped_route: "account.table_finder.index"
risk_level: "HIGH"
required_role: "customer"
---

# Tính năng Tra cứu bàn tiệc thông minh (Table Finder) dành cho khách khi đến sảnh

> **Dành cho:** Cô dâu, Chú rể và Đội ngũ lễ tân đón khách tại cửa sảnh cưới  
> **Thời gian thiết lập:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Tra Cứu Bàn Tiệc (Table Finder)** (Route: `account.table_finder.index`)  
> **API Công cộng:** `invitation.table_finder.search` (`POST /thiep/{slug}/tra-cuu-ban-tiec`)

Khi quan khách bước vào sảnh cưới đông đúc hàng trăm người, khoảnh khắc bối rối nhất là **"Mình ngồi bàn nào? Ngồi cùng ai?"**. Nếu không có người hướng dẫn, khách mời sẽ phải đi quanh từng bàn để tìm bảng tên hoặc đứng chen chúc trước cửa sảnh làm nghẽn lối đón tiếp.

Tính năng **Tra cứu bàn tiệc thông minh (Guest Table Finder)** của Mây Hẹn giải quyết triệt để vấn đề này: Khách mời chỉ cần mở thiệp cưới trên điện thoại hoặc nhập tên tại máy tính bảng lễ tân (Kiosk) là biết ngay số bàn, khu vực và những người bạn quen đang ngồi cùng bàn với mình.

---

## 1. Trải nghiệm của Khách Mời khi đến Sảnh Cưới

Hệ thống hỗ trợ 2 phương thức nhận diện bàn tiệc hoàn toàn tự động:

```
PHƯƠNG THỨC 1: TỰ ĐỘNG ĐỊNH VỊ QUA LINK THIỆP MỜI CÁ NHÂN HÓA (?guest=token)
Khách bấm vào link thiệp mời trong Zalo
                │
                ▼
┌────────────────────────────────────────────────────────┐
│  ✨ XIN CHÀO ANH NGUYỄN VĂN HOÀNG & BẠN GÁI            │
│                                                        │
│  Vị trí chỗ ngồi trân trọng dành riêng cho bạn:        │
│  🏷️ BÀN SỐ: BÀN 08 - ĐỒNG NGHIỆP CTY SUNRISE           │
│  📍 KHU VỰC: Giữa sảnh (Bên trái sân khấu chính)       │
│                                                        │
│  👥 BẠN CÙNG BÀN:                                      │
│  - Trần Thu Hà           - Lê Tuấn Anh                 │
│  - Phạm Minh Đức         - Đỗ Thùy Trang               │
└────────────────────────────────────────────────────────┘

PHƯƠNG THỨC 2: TRA CỨU NHANH TẠI QUẦY LỄ TÂN (SEARCH WIDGET)
Khách gõ Tên hoặc 4 số cuối Điện thoại vào ô tìm kiếm
                │
                ▼
┌────────────────────────────────────────────────────────┐
│  🔍 Nhập họ tên: [ Hoàng               ] [Tìm bàn]     │
├────────────────────────────────────────────────────────┤
│  Kết quả (Tìm thấy 1 kết quả):                         │
│  👤 Nguyễn Văn Hoàng -> 🏷️ Bàn 08 (Zone: Đồng Nghiệp)   │
└────────────────────────────────────────────────────────┘
```

1. **Nhận diện không cần tìm kiếm (Zero-Click Identity):**
   - Nếu bạn gửi thiệp mời kèm token cá nhân hóa (`/thiep/nam-ha?guest=abc123xyz`), khi khách mở thiệp tại sảnh tiệc, banner chỗ ngồi sẽ tự động xuất hiện ngay trang chủ với lời chào nồng hậu và chỉ dẫn bàn tiệc chính xác.
2. **Widget tra cứu trực tiếp:**
   - Với khách nhận thiệp giấy hoặc thiệp gửi chung, khách chỉ cần gõ 2 - 3 chữ cái trong tên mình vào ô tìm kiếm trên thiệp cưới để hệ thống hiển thị số bàn.

---

## 2. Hướng dẫn cấu hình Table Finder trong Quản trị Thiệp

Truy cập **Quản lý Thiệp** -> Chọn thiệp cưới -> Nhấp mục **Tra Cứu Bàn Tiệc**:

### Bước 1: Kích hoạt tính năng
- Gạt công tắc **Kích hoạt Tra cứu bàn tiệc trên thiệp cưới công cộng** (`is_enabled = true`).

### Bước 2: Tùy biến tiêu đề & Lời nhắn chào đón
- **Tiêu đề Widget:** Ví dụ: *Tra cứu chỗ ngồi danh dự của Quý Khách*.
- **Phụ đề hướng dẫn:** *Vui lòng nhập họ tên hoặc số điện thoại để tìm số bàn tiệc của bạn.*
- **Thông điệp chào mừng:** Lời cảm ơn trang trọng xuất hiện khi khách tìm thấy bàn.
- **Thông điệp khi không tìm thấy:** Hướng dẫn nhẹ nhàng: *Chưa tìm thấy thông tin của bạn. Vui lòng liên hệ bàn Lễ tân để được đón tiếp chu đáo nhất.*

### Bước 3: Cài đặt quyền riêng tư & Hiển thị thông tin
Mây Hẹn tuân thủ nghiêm ngặt chuẩn bảo vệ dữ liệu cá nhân theo luật định:
- **Hiển thị Tên Bàn (`show_table_name`):** Bật để hiển thị tên/số bàn (ví dụ: *Bàn VIP 01*).
- **Hiển thị Khu Vực Sảnh (`show_table_zone`):** Bật để hiển thị hướng dẫn vị trí (*Gần sân khấu, Giữa sảnh*).
- **Hiển thị Bạn Cùng Bàn (`show_table_mates`):**  
  - Khi bật: Khách sẽ thấy tên những người ngồi chung mâm với mình. Tính năng này được quan khách cực kỳ yêu thích vì họ biết trước mình sẽ hội ngộ những người bạn nào để chủ động lại chào hỏi.
  - Khi tắt: Chỉ hiển thị tên khách và số bàn tiệc, bảo mật danh tính những khách còn lại trong bàn.
- **Cho phép tra cứu bằng Số Điện Thoại (`allow_search_by_phone`):**  
  - Giúp phân biệt chính xác khi trong tiệc có nhiều người trùng tên (ví dụ có 3 khách cùng tên *Nguyễn Văn Tuấn*). Khách chỉ cần nhập 4 số cuối điện thoại là hệ thống xác định đúng ngay lập tức.

### Bước 4: Nhấp nút [Lưu Cấu Hình]
Hệ thống ghi nhận vào nhật ký kiểm toán hệ thống (`TABLE_FINDER_CONFIG_UPDATED`) và kích hoạt ngay trên giao diện thiệp online.

---

## 3. Ứng dụng Kiosk Lễ Tân Đón Khách tại Cửa Sảnh (Reception Mode)

Thay vì in danh sách giấy khổ A0 cồng kềnh, bạn có thể biến một chiếc iPad hoặc máy tính bảng đặt tại bàn lễ tân thành **Quầy Tra Cứu Tự Phục Vụ (Self-Service Kiosk)**:

1. Mở trình duyệt trên máy tính bảng và truy cập đường link thiệp cưới của bạn.
2. Cuộn đến phần **Tra Cứu Bàn Tiệc** và để ở chế độ toàn màn hình.
3. Khi khách đến ký tên và chụp ảnh check-in, đội ngũ lễ tân hoặc chính khách mời có thể gõ nhanh tên mình để biết ngay số bàn.
4. Đội ngũ bưng khay đón tiếp (Usher) chỉ cần nhìn số bàn trên màn hình để dẫn khách vào đúng vị trí một cách chuyên nghiệp như khách sạn 5 sao.

---

## 4. Xử lý các tình huống thực tế thường gặp

### Tình huống 1: Khách tìm không thấy tên mình
- **Nguyên nhân:** Khách là người đi kèm chưa đăng ký trước, hoặc lúc nhập danh sách ban đầu bạn viết tắt tên khách (ví dụ lưu tên danh bạ là *Tuấn Còi* thay vì *Nguyễn Anh Tuấn*).
- **Cách xử lý của lễ tân:** Nhập số điện thoại của người mời chính; nếu vẫn không có, lễ tân linh hoạt dẫn khách vào các **Bàn Dự Phòng (Buffer Tables)** đã chuẩn bị sẵn theo hướng dẫn tại bài `CLI-SEAT-004`.

### Tình huống 2: Lo ngại lộ thông tin khách mời khác
- **Giải pháp:** Trong trang cấu hình Table Finder, tắt tùy chọn **[Hiển thị bạn cùng bàn]** và tắt **[Tra cứu bằng số điện thoại]**. Khi đó, hệ thống chỉ hiển thị số bàn của đúng người đang tìm kiếm, không để lộ bất kỳ số điện thoại hay thông tin cá nhân của người khác.

---

## 5. Tổng kết Hành trình Quản lý Bàn Tiệc

Đến đây, bạn đã hoàn thiện trọn vẹn bộ công cụ Seating Planner của Mây Hẹn:
1. **Khởi tạo bàn & sức chứa 10-12 chỗ:** `CLI-SEAT-001`
2. **Kéo thả khách trực quan trên PC/iPad:** `CLI-SEAT-002`
3. **Phân vùng Zone Vu Quy / Thành Hôn:** `CLI-SEAT-003`
4. **Bác sĩ sức chứa & Bàn dự phòng 10%:** `CLI-SEAT-004`
5. **Tra cứu bàn tiệc thông minh cho khách:** `CLI-SEAT-005`

Tiếp theo, hãy cùng khám phá hệ thống **Điểm danh Lễ tân & Màn chiếu Trực tiếp (Live Wall)** tại chuyên mục tiếp theo!
