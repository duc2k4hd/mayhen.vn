---
id: CLI-TIME-002
title: "Niêm phong hộp thời gian và cách hệ thống tự động gửi email mở khóa vào kỷ niệm 1 năm"
category: "Hộp thời gian & Kỷ niệm Ngày cưới"
summary: "Hướng dẫn vận hành Niêm phong Hộp Thời Gian và Cơ chế Tự Động Hóa Vòng Đời Cưới (Wedding Lifecycle Automation): Khóa bảo mật dữ liệu trên máy chủ, cơ chế tự động gửi email thông báo mở khóa vào đúng sáng ngày kỷ niệm 1 năm (Paper Wedding) và mở khóa sớm theo yêu cầu."
reader: "PER-CLI-02 / PER-CLI-03"
order: 3
updated_at: "2026-11-15"
tags: [mayhen, hộp-thời-gian, niem-phong, email-tu-dong, ky-niem-1-nam, wedding-lifecycle]
mapped_page: "CLI-TIME-PAGE"
mapped_route: "time_capsule.public_slug"
risk_level: "MEDIUM"
required_role: "customer"
---

# Niêm phong hộp thời gian và cách hệ thống tự động gửi email mở khóa vào kỷ niệm 1 năm

> **Dành cho:** Cô dâu & Chú rể cài đặt hòm thư kỷ niệm hôn lễ  
> **Thời gian thiết lập:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Hộp Thời Gian (Time Capsule)** (Route: `account.time_capsule.index`)  
> **Dịch vụ tự động hóa:** `App\Services\WeddingLifecycleAutomationService` & API `account.time_capsule.unseal`

Một chiếc hòm thời gian chỉ thực sự thiêng liêng khi nó được bảo vệ nguyên vẹn qua thời gian và mở ra đúng vào khoảnh khắc vàng. Bạn không cần phải đặt lịch hẹn trên điện thoại hay lo lắng 1 năm sau mình sẽ quên mất chiếc hòm kỷ vật này.

Hệ thống **Tự Động Hóa Vòng Đời Đám Cưới (Wedding Lifecycle Automation Engine)** của Mây Hẹn hoạt động 24/7 trên máy chủ: Đúng 09:00 sáng ngày kỷ niệm 1 năm ngày cưới của bạn, một bức thư điện tử trang trọng sẽ được gửi thẳng vào hộp thư của hai vợ chồng để đánh thức kho báu ký ức.

![Niêm phong hộp thời gian và tự động gửi email mở khóa](/assets/client/docs/images/client/09-time-capsule/cli-time-002-step-1.png)
*Quản lý Hộp Thời Gian: Trạng thái niêm phong an toàn và hệ thống tự động gửi email nhắc mở khóa vào ngày kỷ niệm.*

---

## 1. Hành trình Vòng Đời Hộp Thời Gian (Capsule Lifecycle)

```
[ NGÀY CƯỚI: NIÊM PHONG ]                 [ 365 NGÀY SAU: MỞ KHÓA TỰ ĐỘNG ]
Khách & Dâu Rể gửi tâm thư                 Đúng 09:00 sáng ngày kỷ niệm 1 năm
           │                                              │
           ▼                                              ▼
┌───────────────────────────────┐          ┌───────────────────────────────┐
│  🔒 TRẠNG THÁI NIÊM PHONG     │          │  📬 THƯ CHÚC MỪNG KỶ NIỆM     │
│  - Dấu sáp đỏ Ruby            │ ───────> │  "Chúc mừng Kỷ niệm 1 Năm     │
│  - Mã hóa nội dung (null)     │          │   (Đám Cưới Giấy) Hoàng & Dung│
│  - Đồng hồ đếm ngược 365 ngày │          │   Hộp thời gian đã mở khóa!"  │
└───────────────────────────────┘          └───────────────────────────────┘
                                                          │
                                                          ▼
                                           ┌───────────────────────────────┐
                                           │  ✨ BẬT KHÓA NỘI DUNG THƯ      │
                                           │  Hai vợ chồng cùng đọc lại    │
                                           │  những dòng lưu bút năm xưa   │
                                           └───────────────────────────────┘
```

---

## 2. Hướng dẫn Cô Dâu & Chú Rể tự viết Tâm Thư gửi chính mình

Bên cạnh thư của khách mời, việc chính hai bạn viết thư cho nhau vào đêm trước ngày cưới là trải nghiệm lãng mạn nhất:

1. Vào **Quản Lý Thiệp** -> Chọn thiệp cưới -> Nhấp mục **Hộp Thời Gian**.
2. Nhấp nút **[+ Viết Tâm Thư Gửi Tương Lai]** (`account.time_capsule.store`).
3. Điền thông tin phong thư:
   - **Người gửi (`sender_type`):** Chọn `Chú rể gửi Cô dâu`, `Cô dâu gửi Chú rể`, hoặc `Cặp đôi gửi Chính mình trong tương lai`.
   - **Tiêu đề:** *Ví dụ: "Gửi vợ yêu của anh vào ngày kỷ niệm 1 năm ngày cưới"*.
   - **Nội dung tâm thư:** Những cảm xúc bồi hồi trước giờ G, những lời hứa hẹn và ước mơ về mái ấm tương lai.
   - **Cột mốc mở khóa (`milestone_years`):** Chọn `1 năm (Đám Cưới Giấy)`, `3 năm`, `5 năm` hoặc `10 năm`. Hệ thống tự động điền ngày mở khóa (`unlock_date`).
   - **Màu dấu sáp (`seal_color`):** Ruby, Gold, Emerald hoặc Sapphire.
   - **Email nhận thông báo (`sender_email`):** Nhập email của bạn để nhận thư báo vào ngày này năm sau.
4. Bấm **[🔒 Đóng Dấu Sáp & Niêm Phong]**. Lá thư lập tức được đưa vào trạng thái khóa an toàn.

---

## 3. Cách thức Hệ thống Tự Động Kích Hoạt Email Mở Khóa

Khi đồng hồ hệ thống điểm ngày `wedding_date + 365 days`:

1. Tiến trình tự động hóa nền (`WeddingLifecycleAutomationService`) quét và phát hiện các phong thư đã chạm hạn mở khóa (`unlock_date <= now()`).
2. **Hệ thống đồng loạt gửi 2 luồng email:**
   - **Luồng 1 (Gửi cho Cô Dâu & Chú Rể):** Thư chúc mừng kỷ niệm ngày cưới kèm nút bấm vàng **[🔓 Mở Khóa Hộp Kỷ Vật]**. Khi nhấp vào, hai bạn sẽ được dẫn tới trang riêng tư để đọc toàn bộ thư của bạn bè và của chính đối phương gửi cho mình.
   - **Luồng 2 (Gửi cho Bạn bè đã gửi thư):** Một email báo tin vui: *"Lá thư bạn gửi cho Hoàng & Dung vào ngày cưới 1 năm trước hôm nay đã được mở khóa và trao tới tay cặp đôi!"*.
3. Trạng thái phong thư trên hệ thống chuyển từ `is_sealed = true` sang `is_sealed = false`, nội dung thư hiển thị đầy đủ kèm hiệu ứng hoa giấy rơi lãng mạn.

---

## 4. Mở khóa sớm theo yêu cầu (Manual Unseal)

Nếu hai bạn tổ chức tiệc kỷ niệm sớm hơn vài ngày cùng gia đình và muốn mở hộp thời gian trước quan khách:

1. Đăng nhập vào trang quản trị thiệp cưới.
2. Tìm phong thư cần mở -> Nhấp nút **[Mở Khóa Thủ Công]** (`account.time_capsule.unseal`).
3. Hệ thống hiển thị popup xác nhận: *"Bạn có chắc chắn muốn phá niêm phong sáp trước hạn không?"*.
4. Bấm **[Xác nhận mở khóa]**. Phong thư sẽ được giải mã ngay lập tức để hai bạn trình chiếu lên màn hình hoặc đọc to trong bữa tiệc.
