---
id: CLI-GUEST-003
title: "Cách tạo link thiệp cá nhân hóa hiển thị đúng danh xưng từng vị khách"
category: "Quản lý Khách mời & RSVP"
summary: "Hướng dẫn tạo và gửi link thiệp cưới cá nhân hóa (Personalized Guest Token): Tự động hiển thị đúng danh xưng trang trọng trên phong bì điện tử, cố định số suất mời (+1 Policy) và quy trình gửi Zalo 1-chạm tự động đánh dấu trạng thái."
reader: "PER-CLI-02 / PER-CLI-03"
order: 25
updated_at: "2026-11-15"
tags: [mayhen, quan-ly-khach-moi, link-ca-nhan-hoa, guest-token, danh-xung-thiep, gui-zalo-1-cham]
mapped_page: "CLI-GUEST-PAGE"
mapped_route: "guests.index"
risk_level: "HIGH"
required_role: "customer"
---

# Cách tạo link thiệp cá nhân hóa hiển thị đúng danh xưng từng vị khách

> **Dành cho:** Cô dâu & Chú rể muốn mỗi vị khách khi mở thiệp đều cảm nhận được sự tôn trọng và trân quý riêng biệt  
> **Thời gian thực hiện:** Hoàn toàn tự động khi thêm khách hoặc nhập file CSV  
> **Vị trí trong ứng dụng:** Trang Quản lý Khách mời (`/khach-moi`) → Cột **Link Thiệp & Thao Tác**  

Điểm khác biệt lớn nhất giữa một tin nhắn gửi link đại trà và một lời mời cưới chân thành nằm ở **sự chu đáo trong danh xưng**. Khi một người bạn mở link và nhìn thấy dòng chữ: *"Thân gửi: Anh Hoàng & Bạn gái"* hoặc người lớn tuổi thấy chữ: *"Kính mời: Bác Hai và Gia đình"* lồng ghép trang trọng trên chiếc phong thư mạ vàng, họ sẽ lập tức cảm nhận được sự hiếu lễ và chuẩn bị kỹ lưỡng của gia đình hai bạn.

---

## 1. Cơ chế hoạt động của Link thiệp cá nhân hóa

Khi bạn thêm một vị khách vào danh sách, Mây Hẹn sẽ tự động sinh ra một mã định danh ngẫu nhiên bảo mật (gọi là `Guest Token`). Đường link gửi riêng cho khách có dạng:  
`https://mayhen.vn/thiep/tuan-lan?guest=a8f9c2d1`

```
┌────────────────────────────────────────────────────────┐
│            TRẢI NGHIỆM KHÁCH KHI MỞ LINK CÁ NHÂN HÓA   │
├────────────────────────────────────────────────────────┤
│                       [ ❀ WAX SEAL ❀ ]                 │
│                                                        │
│                    KÍNH MỜI GIA ĐÌNH BÁC BẢY           │
│   "Đến chung vui cùng ngày hạnh phúc của Tuấn & Lan"   │
│                                                        │
│   • Biểu mẫu RSVP: Tự động điền sẵn tên "Bác Bảy"      │
│   • Giới hạn suất: Khóa tối đa 2 người theo thiệp mời  │
│   • Sơ đồ bàn tiệc: Tự động chỉ đúng "Bàn Họ Nhà Trai" │
└────────────────────────────────────────────────────────┘
```

### Những lợi ích vượt trội so với link thiệp chung:
1. **Hiển thị đúng danh xưng:** Tên và lời xưng hô của khách được render trực tiếp lên bìa phong thư, thiệp mời chính và phần mở đầu của câu chuyện tình yêu.
2. **Khóa số suất tham dự (+1 Policy):** Tránh trường hợp thiệp mời 1 người nhưng khách lại đăng ký dắt theo 3-4 người gây vỡ kế hoạch bàn tiệc. Hệ thống sẽ khóa giới hạn số người tối đa đúng theo cài đặt của bạn.
3. **Tiện lợi tối đa khi gửi lời chúc và RSVP:** Khách không cần phải gõ lại họ tên hay số điện thoại, hệ thống đã tự động nhận diện và lưu phản hồi vào đúng hồ sơ của vị khách đó.
4. **Hiển thị sơ đồ bàn tiệc cá nhân:** Khi khách bước vào tiệc cưới, thiệp sẽ tự động chỉ dẫn: *"Vị trí của bạn được trân trọng xếp tại Bàn 08 - Nhóm Bạn Cấp 3"*.

---

## 2. Hướng dẫn quy trình gửi thiệp cá nhân hóa 1-chạm

Tại màn hình **Quản Lý Khách Mời** (`/khach-moi`):

### Cách 1: Gửi nhanh qua Zalo (Khuyên dùng)
1. Trong danh sách khách, tìm vị khách bạn muốn gửi thiệp.
2. Nhấp vào biểu tượng **[Zalo]** màu xanh bên cạnh tên khách.
3. Hệ thống sẽ tự động kích hoạt ứng dụng Zalo trên điện thoại hoặc máy tính của bạn và soạn sẵn tin nhắn mẫu trang trọng:
   > *"Dạ em Tuấn Minh thân gửi Anh Hoàng & Chị Mai tấm thiệp cưới của hai đứa em. Rất mong anh chị bớt chút thời gian đến chung vui cùng gia đình em tại: https://mayhen.vn/thiep/tuan-lan?guest=a8f9c2d1"*
4. Bạn chỉ cần nhấn **Gửi**.
5. Cùng lúc đó, cột trạng thái trên hệ thống Mây Hẹn sẽ **tự động chuyển sang màu xanh: [Đã gửi thiệp]**, giúp hai bạn không bao giờ bị quên hoặc gửi sót ai!

### Cách 2: Sao chép link gửi qua Messenger, iMessage, SMS
1. Nhấp vào nút **[Sao Chép Link]** (biểu tượng hai trang giấy 📋).
2. Đường link cá nhân hóa đã nằm trong bộ nhớ tạm.
3. Dán vào bất kỳ ứng dụng nhắn tin nào bạn đang trò chuyện cùng bạn bè.

---

## 3. Quản lý trạng thái và Cấp lại link mới (Regenerate Token)

### Đánh dấu gửi thủ công
Nếu bạn gửi thiệp giấy tận tay hoặc nhờ người thân trao hộ:
- Nhấp vào cột trạng thái và chọn **[Đánh dấu đã gửi]** để đồng bộ vào báo cáo sĩ số chung.

### Xử lý khi lỡ gửi nhầm link thiệp của người này cho người khác
Nếu bạn vô tình gửi nhầm link của *Bác Bảy* cho *Bạn Cấp 3*:
1. Nhấp vào dấu 3 chấm `...` bên cạnh tên khách.
2. Chọn **[Tạo Lại Mã Link Mới] (Regenerate Token)**.
3. Hệ thống sẽ ngay lập tức vô hiệu hóa mã link cũ và cấp một mã link hoàn toàn mới. Người mở link cũ sẽ chỉ thấy thông báo thiệp chung, bảo vệ sự riêng tư tuyệt đối!

---

## 4. Bảo mật an toàn: Chống rà quét dữ liệu khách (Zero ID Enumeration)

> [!NOTE]
> **Tiêu chuẩn bảo mật riêng tư:**  
> Mây Hẹn tuyệt đối không sử dụng các số thứ tự đơn giản (như `?guest=1`, `?guest=2`) để tránh việc người ngoài có thể tự ý đổi số để xem danh sách khách mời của gia đình bạn. Mỗi mã `guest_token` là một chuỗi ngẫu nhiên chuẩn mật mã học, chỉ người nhận được link mới có thể mở xem thông tin của chính mình.

---

## 5. Bài viết liên quan tiếp theo

- [Hướng dẫn khách xác nhận tham dự (RSVP) đa sự kiện](/docs/client/04-guest-rsvp/CLI-GUEST-004)
- [RSVP theo hộ gia đình: Đăng ký số lượng người đi kèm và món ăn kiêng/dị ứng](/docs/client/04-guest-rsvp/CLI-GUEST-005)
- [Theo dõi sĩ số khách phản hồi thời gian thực và quản lý lịch sử thay đổi RSVP](/docs/client/04-guest-rsvp/CLI-GUEST-006)
