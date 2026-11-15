---
id: CLI-INVT-001
title: "Cách đặt tên đường dẫn thiệp đẹp (Custom Slug) và chia sẻ lên Zalo/Facebook"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Hướng dẫn tối ưu đường dẫn thiệp cưới cá nhân hóa (Custom Slug), cấu hình ảnh hiển thị OpenGraph (OG Meta) bắt mắt trên mạng xã hội và cách làm mới bộ nhớ đệm Zalo/Facebook khi đổi thông tin thiệp."
reader: "PER-CLI-02 / PER-CLI-03"
order: 17
updated_at: "2026-11-15"
tags: [mayhen, thiep-cuoi-cong-cong, custom-slug, chia-se-zalo, facebook-og-image]
mapped_page: "CLI-INVT-PAGE"
mapped_route: "invitation.show"
risk_level: "HIGH"
required_role: "customer"
---

# Cách đặt tên đường dẫn thiệp đẹp (Custom Slug) và chia sẻ lên Zalo/Facebook

> **Dành cho:** Cô dâu & Chú rể chuẩn bị gửi thiệp cưới online đến bạn bè, người thân qua mạng xã hội  
> **Thời gian thực hiện:** Khoảng 2 - 3 phút  
> **Vị trí trong ứng dụng:** Quản lý thiệp cưới → Cài đặt chia sẻ hoặc Studio Topbar → Nút **[Chia Sẻ]**  

Đường dẫn thiệp cưới (Link thiệp) là điều đầu tiên khách mời nhìn thấy khi nhận tin nhắn mừng vui của hai bạn trên Zalo, Messenger hay tin nhắn SMS. Một đường link ngắn gọn, mang tên của hai bạn như `mayhen.vn/thiep/tuan-minh-lan-anh` kèm tấm thiệp xem trước xinh xắn sẽ thể hiện sự chu đáo, tôn trọng và để lại ấn tượng khó quên ngay từ cái nhìn đầu tiên.

---

## 1. Quy chuẩn đặt tên đường dẫn đẹp (Custom Slug)

Đường dẫn thiệp cưới tại Mây Hẹn có cấu trúc cố định:  
`https://mayhen.vn/thiep/{slug-cua-ban}`

```
┌────────────────────────────────────────────────────────┐
│             CẤU TRÚC ĐƯỜNG DẪN THIỆP ĐẸP MÂY HẸN       │
├────────────────────────────────────────────────────────┤
│  https://mayhen.vn/thiep/tuan-minh-lan-anh             │
│  https://mayhen.vn/thiep/minh-anh-wedding-2026         │
│  https://mayhen.vn/thiep/forever-minh-anh              │
└────────────────────────────────────────────────────────┘
```

### Tiêu chuẩn kỹ thuật cho Slug:
- **Chỉ sử dụng chữ cái thường không dấu (a-z), chữ số (0-9) và dấu gạch nối (-).**
- Không sử dụng dấu cách (khoảng trắng), không dùng ký tự đặc biệt như `&, %, $, @, ?, #`.
- Độ dài khuyến nghị: Từ **6 đến 30 ký tự**. Tránh đặt quá dài khiến link bị ngắt dòng trên ứng dụng nhắn tin.
- **Tính duy nhất (Unique):** Mỗi đường dẫn là duy nhất trên toàn hệ thống Mây Hẹn. Hệ thống sẽ kiểm tra tự động: nếu tên đã có người dùng trước đó, bạn có thể thêm năm cưới (ví dụ: `-2026` hoặc `-wedding`).

---

## 2. Hướng dẫn từng bước đổi đường dẫn thiệp

### Bước 1: Mở mục Cài đặt Đường dẫn
1. Tại màn hình Studio Tạo Thiệp hoặc trang Quản lý Thiệp Cưới cá nhân (`/dashboard/invitations`).
2. Chọn mục **"Cài Đặt Xuất Bản & Tên Miền Phụ"** (Settings & Custom Slug).
3. Tìm ô **"Đường dẫn tùy chỉnh (Custom Slug)"**.

### Bước 2: Nhập tên đường dẫn hai bạn yêu thích
1. Nhập slug mong muốn (Ví dụ: `tuan-minh-lan-anh`).
2. Quan sát biểu tượng kiểm tra thời gian thực bên cạnh:
   - **Tích xanh ✓:** Đường dẫn khả dụng, bạn có thể sử dụng ngay.
   - **Dấu chấm than màu hổ phách ⚠️:** Đường dẫn đã được một cặp đôi khác đăng ký, hệ thống sẽ gợi ý: `tuan-minh-lan-anh-2026` hoặc `minh-lan-anh`.

### Bước 3: Lưu và kích hoạt
1. Nhấp nút **[Lưu Đường Dẫn]**.
2. Đường link mới sẽ có hiệu lực tức thời trên toàn cầu. Khách mời mở link cũ (nếu có) sẽ tự động được chuyển hướng (301 Redirect) sang link mới mà không lo bị lỗi trang trắng 404.

---

## 3. Cấu hình hình ảnh xem trước (OpenGraph Meta Preview) trên Zalo/Facebook

Khi bạn dán link thiệp vào khung chat Zalo hay Facebook Messenger, ứng dụng sẽ tự động tải lên một tấm hình chữ nhật (gọi là thumbnail hay OG Image) kèm lời tựa:

1. **Ảnh đại diện OpenGraph (Kích thước vàng 1200 x 630 px):**
   - Mây Hẹn tự động tạo sẵn một banner chia sẻ sang trọng gồm: ảnh bìa cưới của hai bạn lồng ghép trong khung viền hoa văn Mây Hẹn, kèm họ tên đôi uyên ương và ngày cưới.
   - Bạn cũng có thể tự tải lên một tấm ảnh riêng tại mục **"Ảnh Xem Trước Mạng Xã Hội"**.
2. **Tiêu đề chia sẻ (OG Title):**  
   Mặc định: `Thiệp Cưới Tuấn Minh & Lan Anh — Mây Hẹn`
3. **Lời ngỏ gửi khách (OG Description):**  
   Mặc định: `Trân trọng kính mời bạn đến chung vui trong ngày trọng đại của chúng mình vào ngày 28/12/2026.`

---

## 4. Hướng dẫn gửi thiệp 1-chạm (One-Click Share)

Trong Studio hoặc Dashboard, nhấp vào nút **[Chia Sẻ Thiệp]**:
- **Nút "Sao chép link & Lời mời":** Tự động copy cả đường link và một tin nhắn mời đám cưới mẫu trang trọng vào bộ nhớ tạm để bạn dán vào tin nhắn.
- **Nút "Gửi qua Zalo":** Tự động mở ứng dụng Zalo trên điện thoại hoặc máy tính, bạn chỉ cần chọn người nhận trong danh bạ.
- **Nút "Chia sẻ Facebook":** Mở hộp thoại Facebook Share Dialog để bạn chia sẻ lên trang cá nhân hoặc Story.

---

## 5. Xử lý sự cố: Facebook/Zalo vẫn hiện ảnh cũ hoặc link cũ

Nếu bạn vừa đổi ảnh bìa cưới nhưng khi gửi link qua Zalo hoặc Facebook thì màn hình chat vẫn hiện ảnh cũ:
- **Nguyên nhân:** Zalo và Facebook lưu tạm (cache) thông tin trang web trong vòng vài ngày để tăng tốc độ tải.
- **Cách khắc phục 1-chạm:**
  1. Với **Facebook:** Truy cập công cụ chính thức [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/), dán link thiệp của bạn vào và bấm nút **"Scrape Again" (Thu thập lại thông tin)**. Facebook sẽ xóa ảnh cũ và cập nhật ngay ảnh mới trong 1 giây.
  2. Với **Zalo:** Gửi link kèm một tham số đuôi ngẫu nhiên (Ví dụ: `mayhen.vn/thiep/tuan-minh-lan-anh?v=1`). Zalo sẽ hiểu đây là một tin nhắn mới và tải lại ảnh thumbnail mới nhất.

---

## 6. Bài viết liên quan tiếp theo

- [Tạo mã QR thiệp cưới để in lên thiệp giấy hoặc thiệp kèm (Companion Card)](/docs/client/03-public-invitation/CLI-INVT-002)
- [Cài đặt mật khẩu bảo vệ thiệp cưới riêng tư và cách người thân mở khóa](/docs/client/03-public-invitation/CLI-INVT-003)
- [Hướng dẫn khách lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)](/docs/client/03-public-invitation/CLI-INVT-004)
