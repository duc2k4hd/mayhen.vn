---
id: CLI-EDIT-009
title: "Bác sĩ Kiểm tra Chất lượng (Quality Doctor): Cách chẩn đoán và khắc phục thiếu sót trước khi xuất bản"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Cẩm nang hướng dẫn sử dụng công cụ Bác Sĩ Thiệp Cưới (Visual Quality Doctor): Cơ chế chấm điểm 100đ thời gian thực, thuật toán Cap Score Defense, danh mục kiểm tra trọng yếu và cách nhảy 1 chạm tới ô cần sửa."
reader: "PER-CLI-01 / PER-CLI-02"
order: 2
updated_at: "2026-11-13"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, quality-doctor, chẩn-đoán-thiệp]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Bác sĩ Kiểm tra Chất lượng (Quality Doctor): Cách chẩn đoán và khắc phục thiếu sót trước khi xuất bản

> **Dành cho:** Cô dâu & Chú rể muốn thiệp cưới hoàn hảo 100% trước khi gửi đi  
> **Thời gian thao tác:** Khoảng 1 - 2 phút  
> **Vị trí trong ứng dụng:** Nút 🩺 trên thanh công cụ Studio Topbar (`#mayhen_btn_quality_doctor`)  

Một tấm thiệp cưới gửi đi là lời thông báo trang trọng đến cả trăm bạn bè, người thân và đối tác quan trọng. Việc sơ suất thiếu địa chỉ sảnh tiệc, quên link Google Maps dẫn đường, hay chưa điền thông tin phụ mẫu hai bên có thể gây khó xử trong ngày trọng đại. **Bác Sĩ Thiệp Cưới (Visual Quality Doctor)** là tính năng độc quyền của Mây Hẹn, hoạt động như một chuyên gia thẩm định tự động rà soát từng chi tiết trên thiệp và chấm điểm độ hoàn thiện theo thời gian thực.

---

## 1. Cơ chế chấm điểm 100đ thời gian thực (Realtime Quality Score)

Khi bạn chỉnh sửa trong Studio, Bác Sĩ Thiệp Cưới liên tục theo dõi dữ liệu và hiển thị huy hiệu điểm số ngay trên góc phải thanh công cụ đỉnh cao:

| Thang điểm | Huy hiệu trạng thái | Ý nghĩa & Khuyến nghị |
| :---: | :--- | :--- |
| **90 – 100đ** | **✓ Sẵn sàng xuất bản**<br>*(Màu xanh lá `#2E7D32`)* | Thiệp cưới của hai bạn đã rất hoàn thiện, đầy đủ thông tin trang trọng và hình ảnh sắc nét. Bạn có thể tự tin xuất bản và gửi cho khách mời! |
| **70 – 89đ** | **⚠️ Nên hoàn thiện thêm**<br>*(Màu vàng cam `#E65100`)* | Thiệp khá tốt và cơ bản đầy đủ, nhưng nên bổ sung thêm các mục màu vàng (như link Google Maps hoặc Album ảnh) để khách mời thuận tiện hơn. |
| **Dưới 70đ** | **❌ Cần điền thêm thông tin**<br>*(Màu đỏ đậm `#C62828`)* | Thiệp còn thiếu các thông tin sống còn (Tên cô dâu chú rể, Ngày cưới hoặc Địa chỉ tiệc). Bạn cần điền bổ sung trước khi xuất bản. |

---

## 2. Thuật toán phòng vệ trần điểm số (Cap Score Defense - BUG-AUDIT-07)

Khác với các hệ sinh thái khác chỉ cộng dồn điểm số một cách ngây thơ, Mây Hẹn áp dụng thuật toán **Cap Score Defense**:
- Nếu thiệp cưới bị thiếu một trong các **Thông tin Cốt lõi (Critical Fields)** như: Họ tên đôi uyên ương, Ngày tổ chức hôn lễ, hoặc Địa chỉ sảnh tiệc.
- Điểm số tối đa của thiệp **bị khống chế trần không vượt quá 65 điểm**, bất kể bạn đã tải lên bao nhiêu tấm ảnh đẹp hay chọn bản nhạc du dương đến đâu.
- Điều này bảo đảm rằng không một cặp đôi nào có thể vô tình xuất bản một tấm thiệp cưới thiếu ngày cưới hoặc thiếu địa điểm tổ chức.

---

## 3. Danh mục kiểm tra toàn diện của Bác Sĩ Thiệp Cưới

Khi bạn nhấp vào biểu tượng 🩺 trên Topbar, hộp thoại chẩn đoán sẽ hiện ra với danh mục kiểm tra chi tiết theo từng nhóm:

### Nhóm 1: Thông tin pháp lý & Tôn nghiêm (Trọng yếu)
- **Họ tên Cô dâu & Chú rể:** Phải có đủ cả hai tên, không để trống hoặc dùng tên mẫu mặc định.
- **Ngày cử hành hôn lễ:** Phải chọn ngày hợp lệ, hệ thống tự động sinh ngày Âm lịch đối soát.
- **Địa chỉ sảnh tiệc cưới:** Tên nhà hàng, số nhà, quận/huyện để khách mời biết nơi đến chung vui.
- **Thông tin phụ mẫu hai bên:** Họ tên thân sinh nhà trai và nhà gái thể hiện đạo hiếu và sự trang trọng truyền thống.

### Nhóm 2: Trải nghiệm & Chỉ dẫn khách mời (Nên có)
- **Liên kết Google Maps dẫn đường:** Gắn link bản đồ chính xác giúp khách mời bấm 1-chạm là mở ứng dụng chỉ đường, tránh bị lạc đường vào giờ cao điểm.
- **Mã VietQR Mừng Cưới:** Cài đặt số tài khoản ngân hàng chính xác, giúp khách mời ở xa hoặc bạn trẻ mừng cưới thuận tiện.
- **Lời ngỏ tình yêu (Love Story / Quote):** Thông điệp chân thành gửi đến quan khách, có thể sử dụng Trợ lý AI để gợi ý câu từ lãng mạn.

### Nhóm 3: Thẩm mỹ thị giác & Hình ảnh (Nâng cao)
- **Ảnh đại diện bìa cưới (Hero Cover):** Tải ảnh cưới đẹp, chất lượng cao và đã căn chỉnh điểm nhìn Smart Focal Point.
- **Album ảnh cưới (Gallery):** Khuyến nghị tối thiểu từ 3 đến 10 tấm ảnh cưới để khách mời chiêm ngưỡng khoảnh khắc hạnh phúc của hai bạn.

---

## 4. Hướng dẫn sửa lỗi 1-chạm (One-Click Quick Fix)

Điểm tiện lợi nhất của Bác Sĩ Thiệp Cưới là bạn không cần phải tự đi tìm xem ô cần sửa nằm ở đâu trong hàng chục trường thông tin:
1. Mở bảng chẩn đoán Bác Sĩ Thiệp Cưới bằng cách nhấp vào biểu tượng 🩺 trên thanh công cụ.
2. Tìm các dòng có biểu tượng cảnh báo màu đỏ hoặc màu vàng (ví dụ: *Chưa gắn link Google Maps*).
3. Nhấp vào nút **[Điền ngay]** hoặc nhấp trực tiếp vào dòng cảnh báo đó.
4. Hệ thống sẽ **tự động đóng modal, chuyển sang đúng tab chứa thông tin đó và cuộn mượt (smooth scroll) đưa con trỏ chuột focus ngay vào ô nhập liệu cần điền**.
5. Bạn chỉ cần nhập thông tin, điểm số trên huy hiệu sẽ tự động tăng lên ngay lập tức!

