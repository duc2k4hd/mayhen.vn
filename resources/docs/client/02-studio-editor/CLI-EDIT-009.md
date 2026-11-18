---
id: CLI-EDIT-009
title: "Bác sĩ Kiểm tra Chất lượng (Quality Doctor): Rà soát thiếu sót và sửa nhanh 1 chạm trước khi xuất bản"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn sử dụng công cụ Bác Sĩ Thiệp Cưới (Visual Quality Doctor) trên thanh đầu trang /tao-thiep: đọc hiểu thang điểm 100, cơ chế khóa trần Cap Score Defense khi thiếu thông tin trọng yếu và nhảy 1 chạm tới đúng ô cần bổ sung."
reader: "Cô dâu & Chú rể"
order: 2
updated_at: "2026-11-18"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, quality-doctor, chẩn-đoán-thiệp]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Bác sĩ Kiểm tra Chất lượng (Quality Doctor): Rà soát thiếu sót và sửa nhanh 1 chạm trước khi xuất bản

> **Dành cho:** Cô dâu & Chú rể muốn kiểm tra chắc chắn 100% không bỏ quên ngày giờ, địa chỉ hay bản đồ trước khi gửi thiệp cho quan khách  
> **Thời gian thực hiện:** Khoảng 1 phút  
> **Vị trí thao tác:** Nút điểm số **`🩺 100đ`** ở góc trên bên phải thanh đầu trang Studio (`/tao-thiep`)  

Khi tự tay chuẩn bị thiệp cưới giữa bộn bề công việc, đôi khi hai bạn mải chăm chút hình ảnh đẹp mà vô tình để trống địa chỉ chi tiết của sảnh tiệc, quên dán đường dẫn Google Maps hoặc chưa cập nhật số tài khoản mừng cưới. Công cụ **Bác Sĩ Thiệp Cưới** (**Visual Quality Doctor**) hoạt động như một người trợ lý tỉ mỉ, tự động đối soát toàn bộ tấm thiệp theo thời gian thực và báo điểm ngay trên thanh đầu trang.

---

## 1. Ý nghĩa điểm số chất lượng trên thanh đầu trang

Ngay cạnh nút `Hướng dẫn` và `Xuất Bản Thiệp`, bạn luôn nhìn thấy nút **`🩺`** kèm con số điểm cập nhật tức thì mỗi khi bạn nhập thêm thông tin:

![Hộp thoại Trợ Lý Khám Bệnh Thiệp Cưới Visual Quality Doctor hiển thị điểm chất lượng và danh sách đối soát](/assets/client/docs/images/client/02-studio-editor/cli-edit-009-step-1.png)
*Nhấp vào nút 🩺 trên thanh đầu trang để mở bảng đối soát chi tiết từng hạng mục của Bác Sĩ Thiệp Cưới.*

- **Từ `90` đến `100` điểm (`✓ Sẵn sàng xuất bản` — Nhãn xanh lá):**  
  Tấm thiệp đã đầy đủ toàn bộ thông tin trọng yếu, có lời ngỏ, hình ảnh và chỉ dẫn rõ ràng cho khách mời. Hai bạn hoàn toàn yên tâm bấm xuất bản!
- **Từ `70` đến `89` điểm (`⚠️ Nên hoàn thiện thêm` — Nhãn vàng cam):**  
  Các thông tin bắt buộc đã đủ, nhưng bạn nên bổ sung thêm các mục tiện ích cho khách (như đường dẫn bản đồ Google Maps, lời ngỏ kỷ niệm hoặc thông tin mừng cưới) để tấm thiệp chu đáo trọn vẹn hơn.
- **Dưới `70` điểm (`❌ Cần bổ sung thông tin` — Nhãn đỏ):**  
  Thiệp đang bị trống một hoặc nhiều thông tin sống còn của hôn lễ (tên cô dâu/chú rể, ngày cưới hoặc địa chỉ tổ chức tiệc).

---

## 2. Cơ chế khóa trần điểm số an toàn (`Cap Score Defense`)

Để ngăn chặn tuyệt đối rủi ro gửi nhầm một tấm thiệp rất đẹp nhưng lại thiếu ngày giờ hoặc thiếu địa điểm đãi tiệc, Mây Hẹn trang bị thuật toán **Cap Score Defense**:
- Nếu tấm thiệp đang để trống bất kỳ **thông tin cốt lõi bắt buộc** nào (như *Họ tên Cô dâu – Chú rể*, *Ngày cử hành hôn lễ*, hoặc *Địa chỉ tổ chức tiệc*), tổng điểm chất lượng sẽ **tự động bị giới hạn trần dưới ngưỡng an toàn** dù bạn đã tải đủ 10 tấm ảnh hay chọn nhạc hay đến đâu.
- Nhờ vậy, chỉ cần nhìn thoáng qua điểm số trên thanh đầu trang, bạn luôn biết chắc thiệp đã đủ điều kiện gửi khách hay chưa.

---

## 3. Cách rà soát và nhảy 1 chạm tới đúng ô cần sửa

Khi thấy điểm số chưa đạt mức tối đa (ví dụ `75đ` hoặc `90đ`), bạn thực hiện 3 bước cực nhanh sau:

![Bác Sĩ Thiệp Cưới phát hiện mục còn trống như Địa chỉ tiệc và Đường dẫn Google Maps để nhắc bổ sung ngay](/assets/client/docs/images/client/02-studio-editor/cli-edit-009-step-2.png)
*Khi thiếu địa chỉ tổ chức tiệc hoặc link Google Maps, Bác Sĩ Thiệp Cưới lập tức hạ điểm cảnh báo và hiển thị dòng nhắc nhở rõ ràng.*

1. **Bước 1:** Nhấp vào nút **`🩺`** trên thanh đầu trang để mở cửa sổ **Trợ Lý Khám Bệnh Thiệp Cưới**.
2. **Bước 2:** Xem danh sách **Kết quả đối soát chi tiết**:
   - Các mục đã đạt chuẩn sẽ có dấu tích xanh `✓` rõ ràng.
   - Các mục còn thiếu hoặc đang dùng dữ liệu mẫu chưa đổi sẽ được đánh dấu nổi bật kèm lời nhắc cụ thể (ví dụ: *Chưa nhập địa chỉ tổ chức tiệc chi tiết*, *Chưa gắn đường dẫn Google Maps*).
3. **Bước 3:** Nhấp vào nút đi tới sửa ngay trên dòng cảnh báo đó (hoặc bấm **`Đã Hiểu & Quay Lại Sửa`**) — Studio sẽ **tự động mở đúng Bước chứa trường thông tin đó và đưa con trỏ chuột vào ngay ô cần điền**, bạn không phải tự cuộn tìm thủ công!

> [!TIP]
> **Thói quen vàng trước khi bấm Xuất Bản Thiệp:**  
> Hãy luôn mở **`🩺 Bác Sĩ Thiệp Cưới`** kiểm tra lần cuối cùng chế độ **`Xem như khách`** trên thanh đầu trang. Chỉ mất 30 giây nhưng giúp hai bạn tự tin 100% khi gửi thiệp tới ông bà, cha mẹ và những vị khách trân quý nhất!

---

## 4. Xem tiếp bài viết liên quan

- [Xuất bản thiệp cưới chính thức, Báo cáo tác động thay đổi & Khôi phục khẩn cấp](/docs/studio-editor/CLI-EDIT-010)
- [Đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập](/docs/studio-editor/CLI-EDIT-011)
- [Làm quen giao diện Studio Tạo Thiệp & chạm sửa trực tiếp](/docs/studio-editor/CLI-EDIT-001)
