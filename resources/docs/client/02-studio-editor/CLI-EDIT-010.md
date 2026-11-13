---
id: CLI-EDIT-010
title: "So sánh các thay đổi (Publish Diff), đánh giá tác động và xuất bản thiệp cưới chính thức"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Cẩm nang quy chuẩn xuất bản thiệp cưới: Báo cáo phân tích tác động thay đổi (Publish Impact Report), cảnh báo rủi ro khi đổi ngày giờ sảnh tiệc đối với khách đã nhận thiệp, cơ chế chụp snapshot khôi phục khẩn cấp và kích hoạt link thiệp chính thức."
reader: "PER-CLI-01 / PER-CLI-02"
order: 3
updated_at: "2026-11-13"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, xuat-ban-thiep, publish-impact, emergency-rollback]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# So sánh các thay đổi (Publish Diff), đánh giá tác động và xuất bản thiệp cưới chính thức

> **Dành cho:** Cô dâu & Chú rể chuẩn bị xuất bản thiệp cưới lần đầu hoặc cập nhật thông tin tiệc  
> **Thời gian thao tác:** Khoảng 1 - 2 phút  
> **Vị trí trong ứng dụng:** Nút **[Xuất Bản Thiệp]** màu vàng kim ở góc trên bên phải Studio Topbar  

Sau khi đã hoàn thiện đầy đủ thông tin, chọn bản nhạc yêu thích và đạt điểm số tối đa cùng Bác Sĩ Thiệp Cưới, bước cuối cùng là **Xuất Bản Thiệp Cưới**. Đối với thiệp xuất bản lần đầu, hệ thống sẽ kích hoạt đường dẫn chính thức và mã QR để bạn bắt đầu gửi thiệp. Đối với thiệp đã từng gửi cho khách mà bạn cần chỉnh sửa lại, Mây Hẹn cung cấp công nghệ **Báo Cáo Tác Động Thay Đổi (Publish Impact Report)** độc quyền, giúp bạn kiểm soát rủi ro và không bao giờ để khách mời bị nhầm lẫn giờ giấc ngày vui.

---

## 1. Xuất bản thiệp cưới lần đầu (First-time Publish)

Nếu đây là lần đầu tiên hai bạn tạo thiệp:
1. Nhấp nút **[Xuất Bản Thiệp]** trên thanh công cụ.
2. Hệ thống sẽ tiến hành kiểm tra lần cuối (Pre-publish Validation):
   - Đảm bảo tên cô dâu, chú rể và ngày cưới đã có đầy đủ.
   - Kiểm tra đường dẫn thiệp cưới (slug) không bị trùng lặp.
3. Khi xuất bản thành công:
   - Thiệp cưới chính thức chuyển sang trạng thái **Đã Xuất Bản (Published)**.
   - Hệ thống hiển thị hộp thoại chúc mừng trang trọng kèm:
     - Đường dẫn thiệp chính thức (ví dụ: `mayhen.vn/thiep/tuan-lan-2027`).
     - Mã QR thiệp cưới chất lượng cao (hỗ trợ tải về để in lên thiệp giấy hoặc standee).
     - Nút sao chép link 1-chạm để gửi nhanh qua Zalo, Messenger hoặc SMS.

---

## 2. Sửa thiệp đã gửi cho khách & Báo Cáo Tác Động (Publish Impact Report - TASK-VAL-09)

Trong thực tế, nhiều cặp đôi sau khi gửi thiệp mới phát hiện cần đổi sảnh tiệc từ Tầng 2 lên Tầng 3, hoặc đổi giờ đón khách từ 17:30 thành 18:00. Lúc này, việc bấm lưu vội vàng có thể khiến những khách mời đã xem thiệp trước đó đến sai giờ.

Khi bạn bấm **[Xuất Bản Thiệp]** trên một tấm thiệp đã có khách mời truy cập hoặc đã có phản hồi RSVP:
Hệ thống sẽ **tạm dừng và mở ra Bảng Phân Tích Tác Động Thay Đổi (Publish Diff & Impact Report)**:

```
┌────────────────────────────────────────────────────────────────────────┐
│     ⚠️ BÁO CÁO PHÂN TÍCH TÁC ĐỘNG THAY ĐỔI (PUBLISH IMPACT REPORT)     │
├────────────────────────────────────────────────────────────────────────┤
│ • Số khách mời đã nhận link thiệp : 185 Khách                          │
│ • Số khách mời đã xác nhận tham dự: 142 Khách                          │
├────────────────────────────────────────────────────────────────────────┤
│ CHI TIẾT CÁC THAY ĐỔI TRỌNG YẾU:                                       │
│ ❌ [CRITICAL] Giờ khai tiệc: Đổi từ [ 17:30 ] ➔ [ 18:00 ]             │
│ ⚠️ [WARNING]  Sảnh tiệc    : Đổi từ [ Sảnh A ] ➔ [ Sảnh Diamond ]      │
│ ℹ️ [INFO]     Album ảnh    : Thêm mới 2 tấm ảnh cưới                   │
├────────────────────────────────────────────────────────────────────────┤
│ KHUYẾN NGHỊ: Có 142 khách đã phản hồi tham dự có thể bị nhầm giờ tiệc. │
│ Hệ thống đề xuất gửi tin nhắn nhắc nhở tế nhị sau khi lưu thay đổi.    │
└────────────────────────────────────────────────────────────────────────┘
```

- Bảng diff sẽ so sánh chi tiết từng dòng giữa **Bản đang công khai** và **Bản chỉnh sửa mới**.
- Phân loại mức độ rủi ro:
  - **Mức Nghiêm Trọng (Critical):** Thay đổi Ngày cưới, Giờ tổ chức, hoặc Địa chỉ nhà hàng.
  - **Mức Cảnh Báo (Warning):** Thay đổi Số bàn tiệc, Tên sảnh hoặc Dresscode trang phục.
  - **Mức Thông Tin (Info):** Thêm ảnh cưới, sửa câu từ lời ngỏ, thay nhạc nền.
- Sau khi kiểm tra kỹ lưỡng, bạn nhấp **[Xác Nhận & Cập Nhật Thiệp]** để áp dụng thay đổi.

---

## 3. Cơ chế Khôi Phục Khẩn Cấp (Emergency Rollback Snapshot - TASK-VAL-11)

Lỡ tay bấm sửa nhầm thông tin quan trọng sát giờ cưới và không nhớ số điện thoại nhà hàng cũ?  
Mây Hẹn tích hợp cơ chế **Bảo Hiểm Phiên Bản Khẩn Cấp**:
- Mỗi lần bạn bấm Xuất bản, hệ thống tự động lưu trữ một bản sao chụp toàn vẹn (Full Snapshot) của phiên bản trước đó.
- Nếu muốn quay lại bản cũ ngay lập tức:
  1. Vào menu **Tài khoản -> Thiệp cưới -> Cài đặt thiệp**.
  2. Tìm mục **Khôi Phục Khẩn Cấp (Emergency Rollback)**.
  3. Nhấp nút **[Khôi Phục Bản Xuất Bản Gần Nhất]**.
  4. Toàn bộ nội dung thiệp sẽ được tráo đổi (Swap) trở lại bản cũ chỉ trong **1 giây**, loại bỏ hoàn toàn nguy cơ sai sót thông tin trong ngày cưới!

---

## 4. Những điều cần lưu ý sau khi xuất bản

> [!TIP]
> **Chỉnh sửa lại sau khi đã xuất bản:**  
> Bạn hoàn toàn có thể vào Studio chỉnh sửa thiệp bất kỳ lúc nào, kể cả sau khi đã gửi link cho khách. Mọi cập nhật sẽ tự động hiển thị cho người xem tiếp theo mà đường dẫn thiệp không bị thay đổi.

> [!NOTE]
> **Thiệp chưa nâng cấp gói (Bản Free):**  
> Bản miễn phí vẫn cho phép bạn xuất bản và chia sẻ bình thường. Khi nâng cấp lên gói Pro, toàn bộ logo Mây Hẹn sẽ tự động được gỡ bỏ và mở khóa thêm các tính năng độc quyền (Két kỷ niệm số, Quản lý sơ đồ bàn tiệc, Màn hình chiếu trực tiếp Live Wall).

