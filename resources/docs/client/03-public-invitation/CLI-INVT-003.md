---
id: CLI-INVT-003
title: "Cài đặt mật khẩu bảo vệ thiệp cưới riêng tư và cách người thân mở khóa"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Hướng dẫn kích hoạt chế độ bảo vệ thiệp cưới riêng tư bằng mật khẩu (Password Gate / PIN Code), thiết lập câu hỏi gợi ý thân mật, cơ chế mở khóa mượt mà và phòng vệ an toàn dữ liệu khách mời."
reader: "PER-CLI-02 / PER-CLI-03"
order: 19
updated_at: "2026-11-15"
tags: [mayhen, thiep-cuoi-cong-cong, mat-khau-thiep, bao-ve-rieng-tu, pin-code, private-wedding]
mapped_page: "CLI-INVT-PAGE"
mapped_route: "invitation.show"
risk_level: "HIGH"
required_role: "customer"
---

# Cài đặt mật khẩu bảo vệ thiệp cưới riêng tư và cách người thân mở khóa

> **Dành cho:** Cặp đôi muốn tổ chức tiệc cưới kín đáo (Intimate Wedding), bảo mật tuyệt đối ảnh cưới và địa chỉ sảnh tiệc  
> **Thời gian thực hiện:** Khoảng 1 - 2 phút  
> **Vị trí trong ứng dụng:** Quản lý thiệp cưới → Cài đặt bảo mật & Quyền riêng tư (Privacy & Security)  

Có những ngày vui mà hai bạn chỉ muốn chia sẻ trọn vẹn trong vòng tay những người thực sự thân thiết. Tính năng **Mật Khẩu Thiệp Cưới Riêng Tư (Private Password Gate)** của Mây Hẹn cho phép hai bạn khóa thiệp cưới bằng một mã PIN hoặc câu hỏi thân thương. Chỉ những ai có mật mã do hai bạn gửi tặng mới có thể mở cánh thiệp để chiêm ngưỡng album ảnh và biết chính xác địa điểm tổ chức hôn lễ.

![Màn hình phong bao niêm phong sáp và mở khóa mật mã](/assets/client/docs/images/client/03-public-invitation/cli-invt-003-step-1.png)
*Giao diện mở khóa thiệp cưới bảo mật: Bảo vệ riêng tư tối đa và trải nghiệm mở thư độc đáo.*

---

## 1. Màn hình thiệp trông như thế nào khi bật chế độ bảo mật?

Khi bất kỳ ai bấm vào link thiệp `mayhen.vn/thiep/{slug}`:
- Toàn bộ nội dung thiệp (Tên sảnh tiệc, ngày giờ, timeline, album ảnh, số tài khoản mừng cưới) đều được **ẩn giấu hoàn toàn trên máy chủ**, không thể bị soi thấy qua mã nguồn HTML (Zero Leaks Defense).
- Màn hình chỉ hiển thị phong bao thư niêm phong sáp sang trọng kèm:
  - Tên viết tắt Monogram của hai bạn.
  - Hộp nhập mật khẩu (PIN Code) 4 - 6 ký tự.
  - Câu gợi ý thân mật do hai bạn tự soạn (ví dụ: *"Nhập ngày sinh nhật của chú rể"* hoặc *"Xem mật mã in ở mặt sau thiệp giấy"*).

```
┌────────────────────────────────────────────────────────┐
│           MÀN HÌNH MỞ KHÓA THIỆP RIÊNG TƯ MÂY HẸN      │
├────────────────────────────────────────────────────────┤
│                      [ ❀ M & A ❀ ]                     │
│                                                        │
│            THIỆP CƯỚI RIÊNG TƯ CỦA TUẤN MINH & LAN ANH │
│   "Không gian ấm cúng dành riêng cho những người thân yêu" │
│                                                        │
│   Gợi ý: Mật mã gồm 4 chữ số in ở mặt sau phong bì thiệp│
│                                                        │
│               ┌───┐ ┌───┐ ┌───┐ ┌───┐                  │
│               │ 2 │ │ 8 │ │ 1 │ │ 2 │                  │
│               └───┘ └───┘ └───┘ └───┘                  │
│                                                        │
│                 [ MỞ CÁNH THIỆP CƯỚI ]                 │
└────────────────────────────────────────────────────────┘
```

---

## 2. Hướng dẫn từng bước cài đặt mật khẩu

### Bước 1: Mở mục Cài đặt Quyền riêng tư
1. Tại trang **Quản lý Thiệp Cưới** (`/dashboard/invitations`) hoặc thanh công cụ Studio.
2. Chọn mục **"Bảo Mật & Quyền Riêng Tư"** (Privacy Settings).
3. Gạt bật công tắc: **[Kích hoạt Mật khẩu Bảo vệ Thiệp]**.

### Bước 2: Thiết lập mật khẩu và câu gợi ý
1. **Ô Mật khẩu (`invitation_password`):**
   - Bạn có thể đặt mật khẩu dạng số (ví dụ: ngày kỷ niệm `2812`) hoặc dạng chữ ngắn gọn (ví dụ: `minhanh`).
   - Khuyến nghị: Sử dụng mã số từ 4 đến 6 ký tự để khách lớn tuổi dễ dàng thao tác trên bàn phím số điện thoại.
2. **Ô Câu hỏi gợi ý (`password_hint`):**
   - Nhập một câu đố vui hoặc chỉ dẫn nhẹ nhàng:  
     *Ví dụ 1:* "Ngày chúng mình chính thức hẹn hò (ddmm)"  
     *Ví dụ 2:* "Biệt danh thời đại học của cô dâu"  
     *Ví dụ 3:* "Mã số 4 chữ số trên thẻ kẹp thiệp giấy"

### Bước 3: Lưu cài đặt
1. Nhấp nút **[Lưu Cài Đặt Bảo Mật]**.
2. Hệ thống lập tức kích hoạt tường lửa bảo vệ. Mọi truy cập vào đường link từ thời điểm này đều bắt buộc phải nhập mật mã.

---

## 3. Trải nghiệm mở khóa thông minh của người thân (Smart Unlock)

Mây Hẹn chú trọng tối đa trải nghiệm người dùng, tránh gây phiền hà cho khách mời:
1. **Hiệu ứng mở khóa mãn nhãn:** Khi khách nhập đúng mật mã, con dấu sáp ảo sẽ phát sáng vàng kim êm ái, phong thư mở ra mượt mà và đưa khách vào trang thiệp chính thức kèm bản nhạc tình ca du dương.
2. **Ghi nhớ phiên đăng nhập (Persistent Session 30 ngày):**  
   Khách mời **chỉ cần nhập mật khẩu một lần duy nhất**. Hệ thống sẽ tự động ghi nhớ thiết bị an toàn trong vòng 30 ngày. Những lần sau khi khách mở lại link (để xem lại bản đồ chỉ đường hay xem timeline ngày cưới), thiệp sẽ mở ra ngay lập tức mà không bắt nhập lại mật mã!
3. **Phòng vệ chống đoán mò (Rate Limiting Defense):**  
   Nếu có người lạ cố tình thử mật khẩu sai quá 5 lần liên tiếp, hệ thống sẽ tạm khóa quyền nhập trong 60 giây và ghi nhận nhật ký bảo mật, bảo vệ sự riêng tư tối đa cho gia đình hai bạn.

---

## 4. Cách tắt mật khẩu sau ngày cưới

Sau khi hôn lễ đã diễn ra trọn vẹn và hai bạn muốn mở công khai thiệp cưới để bạn bè xa gần cùng ngắm nhìn album ảnh cưới và đọc lại những lời chúc phúc:
1. Quay lại mục **"Bảo Mật & Quyền Riêng Tư"** trong tài khoản.
2. Gạt tắt công tắc **[Kích hoạt Mật khẩu Bảo vệ Thiệp]**.
3. Nhấp nút **[Lưu Thay Đổi]**. Toàn bộ đường link sẽ trở lại trạng thái công khai tức thì.

---

## 5. Bài viết liên quan tiếp theo

- [Hướng dẫn khách lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)](/docs/client/03-public-invitation/CLI-INVT-004)
- [Cài đặt chỉ đường Google Maps chính xác đến sảnh tiệc nhà trai, nhà gái](/docs/client/03-public-invitation/CLI-INVT-005)
- [Hướng dẫn tạo thiệp đa ngôn ngữ (Tiếng Việt, Tiếng Anh, Song ngữ)](/docs/client/03-public-invitation/CLI-INVT-006)
