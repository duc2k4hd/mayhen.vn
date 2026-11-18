---
id: CLI-EDIT-010
title: "Xuất bản thiệp cưới chính thức, so sánh thay đổi (Publish Impact Report) và khôi phục phiên bản"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn chi tiết cách bấm Xuất Bản Thiệp từ trang /tao-thiep, cơ chế giữ nguyên thiết kế khi đăng nhập, Báo cáo phân tích tác động thay đổi (Publish Impact Report) khi sửa thiệp đã gửi khách và Khôi phục khẩn cấp."
reader: "Cô dâu & Chú rể"
order: 3
updated_at: "2026-11-18"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, xuat-ban-thiep, publish-impact, emergency-rollback]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Xuất bản thiệp cưới chính thức, so sánh thay đổi (Publish Impact Report) và khôi phục phiên bản

> **Dành cho:** Cô dâu & Chú rể đã thiết kế xong tấm thiệp ưng ý và muốn nhận đường dẫn chính thức để gửi cho khách mời (hoặc cập nhật lại thông tin tiệc an toàn)  
> **Thời gian thực hiện:** Khoảng 1 phút  
> **Vị trí thao tác:** Nút **`Xuất Bản Thiệp →`** màu vàng đồng ở góc trên bên phải thanh đầu trang Studio (`/tao-thiep`)  

Sau khi hoàn tất thông tin hôn lễ, căn chỉnh ảnh cưới sắc nét và kiểm tra điểm số cùng `🩺 Bác Sĩ Thiệp Cưới`, bước cuối cùng là bấm **Xuất Bản Thiệp** để kích hoạt đường dẫn thiệp cưới riêng của hai bạn. Đặc biệt, nếu sau khi gửi thiệp bạn cần điều chỉnh lại giờ đón khách hay tên sảnh tiệc, Mây Hẹn có cơ chế kiểm soát thay đổi thông minh giúp bảo vệ trải nghiệm của những vị khách đã nhận thiệp trước đó.

---

## 1. Các bước Xuất bản thiệp cưới lần đầu từ `/tao-thiep`

![Kiểm tra hoàn thiện trên giao diện Studio và nhấn nút Xuất Bản Thiệp ở góc trên bên phải](/assets/client/docs/images/client/02-studio-editor/cli-edit-010-step-1.png)
*Khi các thông tin và hình ảnh trên thiệp đã hoàn chỉnh, nhấp nút Xuất Bản Thiệp → ở góc phải trên cùng để hoàn tất.*

1. **Kiểm tra nhanh lần cuối:**
   - Nhìn lên nút **`🩺`** trên thanh đầu trang để chắc chắn các mục quan trọng (tên cặp đôi, ngày cưới, địa chỉ tiệc) đã đầy đủ.
   - Bấm thử nút **`Xem như khách`** để lướt kiểm tra từ đầu tới cuối tấm thiệp một lượt.
2. **Nhấp nút `Xuất Bản Thiệp →` ở góc phải trên cùng** (hoặc nút `Xuất Bản` ở thanh dưới cùng nếu bạn làm trên điện thoại):
   - **Nếu bạn đã đăng nhập tài khoản:** Hệ thống lập tức lưu toàn bộ thiết kế, kiểm tra an toàn dữ liệu và khởi tạo đường dẫn thiệp cưới chính thức của hai bạn.
   - **Nếu bạn thiết kế thử trước khi đăng nhập:** Đừng lo lắng! Toàn bộ chữ viết, bảng màu, phông chữ và nhãn dán bạn vừa kỳ công thiết kế đã được lưu giữ an toàn. Hệ thống sẽ đưa bạn qua trang Đăng nhập / Đăng ký nhanh, và ngay sau khi đăng nhập xong sẽ **tự động quay trở lại đúng mẫu thiệp đang làm dở** để bạn xuất bản ngay mà không mất bất kỳ chi tiết nào.
3. **Nhận đường dẫn thiệp & quản lý khách mời:**
   - Sau khi lưu và xuất bản, bạn có thể sao chép đường dẫn thiệp cưới (ví dụ: `mayhen.vn/thiep/minh-tien-thu-nguyet`), tải mã QR để in kèm thiệp giấy, hoặc tạo đường dẫn cá nhân hóa gắn sẵn tên từng vị khách mời để gửi qua Zalo, Messenger.

---

## 2. Sửa thiệp sau khi đã gửi khách & Báo cáo tác động (`Publish Impact Report`)

Trong quá trình chuẩn bị đám cưới, việc thay đổi sảnh tiệc sang tầng khác hoặc dời giờ đón khách sớm hơn 30 phút là điều hoàn toàn có thể xảy ra. Với những tấm thiệp đã có khách mở xem hoặc đã xác nhận tham dự (RSVP), Mây Hẹn hỗ trợ **Báo Cáo Phân Tích Tác Động Thay Đổi** (**Publish Impact Report**):

```
┌────────────────────────────────────────────────────────────────────────┐
│     ⚠️ BÁO CÁO PHÂN TÍCH TÁC ĐỘNG THAY ĐỔI (PUBLISH IMPACT REPORT)     │
├────────────────────────────────────────────────────────────────────────┤
│ • Đối chiếu tự động giữa Bản Đang Công Khai và Bản Chỉnh Sửa Mới      │
├────────────────────────────────────────────────────────────────────────┤
│ PHÂN LOẠI MỨC ĐỘ THAY ĐỔI:                                            │
│ • [QUAN TRỌNG] Đổi Ngày cưới, Giờ khai tiệc hoặc Địa chỉ nhà hàng      │
│ • [LƯU Ý]      Đổi Tên sảnh tiệc, Lịch trình hoặc Bảng màu Dresscode   │
│ • [THÔNG TIN]  Cập nhật thêm ảnh cưới, sửa câu chữ lời ngỏ, đổi nhạc   │
└────────────────────────────────────────────────────────────────────────┘
```

- Hệ thống phân biệt rõ **Bản nháp đang sửa trong Studio** và **Bản công khai khách mời đang xem**: khi bạn đang thử nghiệm dở dang trong Studio mà chưa bấm xác nhận xuất bản cập nhật, khách mời mở link bên ngoài vẫn nhìn thấy bản ổn định trước đó, không bao giờ nhìn thấy chữ đang gõ dở!
- Khi phát hiện thay đổi vào giờ giấc hoặc địa điểm trên thiệp đã có khách xác nhận tham dự, hệ thống sẽ nhắc nhở rõ ràng để hai bạn chủ động nhắn tin thông báo lịch mới cho những khách đã phản hồi trước đó, đồng thời **đường dẫn thiệp cũ đã gửi trên Zalo vẫn giữ nguyên 100% không bị gãy link**.

---

## 3. Khôi phục phiên bản an toàn (`Emergency Rollback Snapshot`)

Nếu sát ngày cưới bạn vô tình sửa nhầm thông tin và muốn quay về ngay bản công khai chuẩn xác liền trước đó:
- Mỗi lần xuất bản thành công, hệ thống tự động tạo một bản chụp lưu trữ nguyên trạng (**Emergency Rollback Snapshot**).
- Trong phần quản lý cài đặt thiệp cưới tại tài khoản cá nhân, bạn có thể kích hoạt khôi phục về bản xuất bản ổn định gần nhất chỉ trong tích tắc mà không cần nhập lại từ đầu.

> [!TIP]
> **Mẹo gửi thiệp sang trọng qua Zalo & Facebook:**  
> Sau khi xuất bản thiệp, khi bạn dán đường dẫn vào khung chat Zalo hoặc Messenger, hệ thống thẻ xem trước (Open Graph) của Mây Hẹn sẽ tự động hiển thị tiêu đề tên Cô dâu – Chú rể cùng bức ảnh đại diện sắc nét rất trang trọng!

---

## 4. Xem tiếp bài viết liên quan

- [Đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập](/docs/studio-editor/CLI-EDIT-011)
- [Bác Sĩ Thiệp Cưới: Kiểm tra thiếu sót trước khi xuất bản](/docs/studio-editor/CLI-EDIT-009)
- [Làm quen giao diện Studio Tạo Thiệp & chạm sửa trực tiếp](/docs/studio-editor/CLI-EDIT-001)
