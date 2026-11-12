# MÂY HẸN — TÀI LIỆU 03: QUY CHUẨN BIÊN TẬP & PHONG CÁCH NỘI DUNG
## Docs Content Style Guide — Viết Cho Người Thật Đọc Hiểu & Tự Thực Hiện

> **Mã tài liệu:** `DOCS-STYLE-03`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Triết lý biên tập:** Tinh tế, lịch thiệp, thực tế và dễ hiểu (Tone of Voice Mây Hẹn: Quiet Luxury)  

---

## 1. NGUYÊN TẮC CỐT LÕI: "VIẾT CHO NGƯỜI THẬT, KHÔNG VIẾT KIỂU AI"

Tài liệu Mây Hẹn hướng tới những con người thật đang chuẩn bị cho sự kiện trọng đại nhất đời mình, hoặc những nhân viên đang chịu trách nhiệm trước sự thành bại của một đám cưới. Do đó, phong cách viết phải tuân thủ nghiêm ngặt 5 nguyên tắc vàng:

### 1.1. Cấm tuyệt đối văn phong AI sáo rỗng
- **CẤM:** Dùng các cụm từ mở đầu khuôn mẫu như: *"Chào mừng bạn đến với kỷ nguyên số hóa...", "Trong bối cảnh công nghệ 4.0...", "Mây Hẹn tự hào mang đến giải pháp tối ưu và đột phá..."*.
- **CẤM:** Kéo dài bài viết bằng các tính từ khen ngợi vô nghĩa: *"tuyệt vời", "thần thánh", "hoàn mỹ", "vô tiền khoáng hậu"*.
- **HÃY:** Đi thẳng vào bản chất công việc: *"Để thay ảnh bìa thiệp cưới, bạn vào Studio, nhấp vào ảnh bìa và chọn ảnh có sẵn trong máy tính."*

### 1.2. Trả lời trọn vẹn 5 câu hỏi của độc giả
Mỗi bài hướng dẫn bắt buộc phải giúp độc giả trả lời ngay:
1. **Chức năng này dùng để làm gì?** (Lợi ích thực tế cho ngày cưới).
2. **Tìm chức năng ở đâu trong ứng dụng?** (Đường dẫn, menu, vị trí nút bấm chính xác từ mã nguồn).
3. **Thao tác từng bước thế nào?** (Hướng dẫn tuần tự 1, 2, 3 không nhảy bước).
4. **Làm sao biết đã thành công?** (Dấu hiệu nhận biết: thông báo xanh, ảnh hiển thị, thiệp cập nhật).
5. **Nếu xảy ra lỗi thì xử lý ra sao?** (Ảnh quá nặng, mất mạng, trùng lặp khách, lỗi thanh toán).

### 1.3. Đồng bộ thuật ngữ giao diện 100% với Codebase
- Không tự suy diễn tên nút bấm. Nếu trong Blade view ghi `[Lưu thay đổi]`, tài liệu phải ghi đúng `Nhấp vào nút "Lưu thay đổi"`, không được viết `Nhấp vào nút "Save"` hoặc `Ghi lại`.
- Khi hướng dẫn nhập dữ liệu, ghi rõ định dạng cho phép: *"Ảnh định dạng JPG, PNG hoặc WebP, dung lượng dưới 10MB"*.

---

## 2. BẢNG SO SÁNH VÍ DỤ: CỰC TỐT vs TỒI TỆ

| Tình huống Hướng dẫn | Cách viết TỒI TỆ (Kiểu AI sáo rỗng) | Cách viết CỰC TỐT (Chuẩn Mây Hẹn cho người thật) |
| :--- | :--- | :--- |
| **Thay ảnh bìa thiệp cưới** | *"Ảnh bìa là cánh cổng thần kỳ mở ra câu chuyện tình yêu diệu kỳ của hai bạn. Hãy biến bức ảnh thành một kiệt tác nghệ thuật để khách mời phải trầm trồ thán phục trước tình yêu bất diệt bằng công cụ tối tân của Mây Hẹn."* | *"Ảnh bìa là hình ảnh đầu tiên khách mời nhìn thấy khi mở thiệp cưới.<br>1. Tại trang chỉnh sửa thiệp, bạn cuộn đến mục **Ảnh Bìa**.<br>2. Nhấp vào nút **[Tải ảnh mới]**.<br>3. Chọn bức ảnh cưới đẹp nhất từ điện thoại hoặc máy tính (nên dùng ảnh dọc, rõ nét, dung lượng dưới 10MB).<br>4. Kéo vòng tròn tiêu điểm vào giữa khuôn mặt của hai bạn để ảnh không bị cắt trên điện thoại.<br>5. Nhấp **[Lưu thay đổi]**. Ảnh sẽ cập nhật ngay lập tức trên màn hình xem thử."* |
| **Khách mời xác nhận RSVP** | *"Một trải nghiệm đỉnh cao giúp kết nối tâm giao giữa chủ tiệc và quan khách thông qua nền tảng đám mây siêu việt."* | *"Khi nhận được link thiệp cưới, khách mời chỉ cần:<br>1. Mở link thiệp trên điện thoại.<br>2. Cuộn xuống phần **Xác nhận tham dự (RSVP)**.<br>3. Chọn **[Sẽ tham dự]** hoặc **[Rất tiếc không thể đến]**.<br>4. Nhập số người đi cùng (nếu có) và ghi chú món ăn kiêng (ví dụ: ăn chay).<br>5. Nhấp **[Gửi phản hồi]**. Màn hình sẽ hiện thông báo 'Cảm ơn bạn đã xác nhận' và gửi mã QR bàn tiệc về màn hình."* |
| **Admin đối soát SePay** | *"Hệ thống fintech tiên tiến tự động kích hoạt mọi giao dịch trong tích tắc mang lại sự an tâm tuyệt đối."* | *"Khi khách chuyển khoản nhưng đơn hàng chưa tự động kích hoạt do sai nội dung chuyển khoản:<br>1. Vào menu **Đơn hàng & Doanh thu** -> chọn **Đơn hàng chờ thanh toán**.<br>2. Tìm mã đơn hàng hoặc số điện thoại của khách.<br>3. Đối chiếu số tiền thực nhận trên App ngân hàng hoặc cổng SePay.<br>4. Nhấp vào nút **[Kích hoạt thủ công]**, nhập mã giao dịch ngân hàng và lý do can thiệp.<br>5. Nhấp **[Xác nhận]**. Hệ thống sẽ chuyển đơn hàng sang trạng thái **Đã thanh toán (Paid)** và tự động mở khóa gói dịch vụ cho khách."* |

---

## 3. CẤU TRÚC CHUẨN CỦA MỘT BÀI HƯỚNG DẪN CLIENT (HOW-TO ARTICLE TEMPLATE)

Mọi bài viết trong `/docs/` bắt buộc phải tuân theo cấu trúc 6 phần:

```markdown
# [Tiêu đề bắt đầu bằng hành động, ví dụ: Cách thay đổi ảnh bìa thiệp cưới]

> **Tóm tắt nhanh:** [1-2 câu giải thích tính năng này giúp gì và làm mất bao lâu]
> **Đối tượng:** Cô dâu, chú rể đang tạo thiệp
> **Vị trí trong ứng dụng:** Menu Quản lý thiệp -> [Chỉnh sửa thiệp] -> Phần Ảnh bìa

## 1. Trước khi thực hiện (Chuẩn bị)
- Chuẩn bị sẵn file ảnh trên thiết bị (định dạng JPG, PNG hoặc WebP).
- Đảm bảo bạn đã đăng nhập tài khoản Mây Hẹn sở hữu thiệp cưới này.

## 2. Các bước thực hiện chi tiết
1. **Bước 1:** Mở trang Studio tạo thiệp tại đường dẫn `/studio/{id}`.
2. **Bước 2:** Tại thanh công cụ bên trái, chọn mục **Nội dung & Hình ảnh**.
3. **Bước 3:** Nhấp vào khung **Ảnh bìa chính** và chọn ảnh từ máy tính.
4. **Bước 4:** Dùng chuột di chuyển điểm ngắm **Smart Focal Point** vào khuôn mặt để ảnh luôn hiển thị trọn vẹn trên cả iPhone lẫn máy tính.
5. **Bước 5:** Nhấp nút **[Lưu thay đổi]** ở góc phải màn hình.

## 3. Dấu hiệu thành công
- Màn hình góc trên hiện thông báo xanh: *"Đã lưu thành công"*.
- Màn hình điện thoại xem thử bên phải lập tức hiển thị ảnh mới.

## 4. Xử lý các lỗi thường gặp
- **Lỗi ảnh báo quá dung lượng:** Nén ảnh xuống dưới 10MB bằng công cụ nén ảnh trước khi tải lên.
- **Lỗi mất mạng khi đang lưu:** Mây Hẹn có tính năng tự động lưu nháp cục bộ (Draft Recovery). Bạn chỉ cần giữ nguyên tab, kiểm tra lại Wi-Fi và nhấp lưu lại.

## 5. Câu hỏi thường gặp (FAQ)
- *Tôi có thể đổi ảnh bìa sau khi đã gửi thiệp cho khách không?* -> Hoàn toàn được. Ảnh mới sẽ cập nhật tức thì, khách mở lại link thiệp sẽ thấy ảnh mới ngay.
```

---

## 4. CẤU TRÚC CHUẨN CỦA QUY TRÌNH QUẢN TRỊ ADMIN (ADMIN SOP TEMPLATE)

Mọi bài viết trong `/admin/docs/` phải được viết theo chuẩn Quy trình Vận hành Tiêu chuẩn (Standard Operating Procedure):

```markdown
# [Mã SOP] — [Tên Quy trình, ví dụ: ADM-SOP-ORDR-002: Quy trình Xử lý Kích hoạt Đơn hàng Thủ công]

> **Phân quyền thực hiện:** Nhân viên Kế toán (Finance) hoặc Quản lý (Manager)  
> **Mức độ rủi ro:** CAO (Ảnh hưởng trực tiếp đến tài chính và trạng thái thiệp)  
> **Điều kiện tiên quyết:** Khách hàng đã chuyển khoản thực tế nhưng Webhook SePay không khớp nội dung  
> **Bằng chứng kiểm toán:** Bắt buộc nhập Mã tham chiếu ngân hàng (Bank Ref No.)  

## 1. Mục đích nghiệp vụ
Xử lý các trường hợp khách hàng chuyển khoản sai cú pháp mã đơn, thiếu ký tự hoặc hệ thống ngân hàng bị trễ thông báo IPN, đảm bảo khách được mở thiệp kịp thời mà không bị gián đoạn ngày cưới.

## 2. Các bước thao tác an toàn
1. **Bước 1:** Truy cập `/admin/orders`, nhập Số điện thoại hoặc Mã đơn của khách vào ô tìm kiếm.
2. **Bước 2:** Nhấp vào chi tiết đơn hàng để kiểm tra số tiền cần thanh toán và lịch sử webhook logs.
3. **Bước 3:** Đăng nhập ứng dụng ngân hàng doanh nghiệp hoặc cổng SePay, tìm giao dịch có số tiền tương ứng và đối chiếu tên người gửi.
4. **Bước 4:** Tại giao diện Admin Mây Hẹn, nhấp vào nút **[Kích hoạt đơn hàng thủ công]**.
5. **Bước 5:** Điền đầy đủ thông tin vào Form can thiệp:
   - *Mã giao dịch ngân hàng (FT... / Ref ID):* Bắt buộc.
   - *Số tiền thực nhận:* Bắt buộc đúng với hóa đơn.
   - *Ghi chú lý do:* Ví dụ: "Khách chuyển nhầm nội dung thành tên chú rể, đã đối soát sao kê 14:30".
6. **Bước 6:** Nhấp **[Xác nhận kích hoạt]**.

## 3. Kết quả mong đợi & Tác động hệ thống
- Trạng thái đơn hàng chuyển ngay sang `paid`.
- Gói dịch vụ của thiệp cưới được kích hoạt tự động.
- Hệ thống ghi lại 1 dòng Audit Log: `[ADMIN_MANUAL_ACTIVATE]` gắn với User ID của nhân viên thực hiện.
- Email xác nhận thanh toán tự động gửi đến hòm thư của khách hàng.

## 4. Rủi ro & Những điều TUYỆT ĐỐI KHÔNG ĐƯỢC LÀM
- **CẤM:** Không được kích hoạt đơn hàng khi chưa nhìn thấy tiền nổi trên tài khoản ngân hàng thực tế (kể cả khi khách gửi ảnh chụp màn hình ủy nhiệm chi).
- **CẤM:** Không được chia sẻ tài khoản Admin cho người khác thao tác thay.

## 5. Quy trình chuyển cấp khi có sự cố (Escalation)
Nếu phát hiện giao dịch có dấu hiệu giả mạo, trùng lặp mã đối soát hoặc có tranh chấp gian lận thẻ: Lập tức chuyển thông tin cho Trưởng bộ phận Vận hành qua kênh khẩn cấp nội bộ trong vòng 15 phút.
```

---

## 5. TỪ ĐIỂN THUẬT NGỮ MÂY HẸN (GLOSSARY)

Để đảm bảo tính nhất quán trên toàn bộ tài liệu hướng dẫn:

| Thuật ngữ Tiếng Anh / Kỹ thuật | Thuật ngữ Tiếng Việt Chuẩn mực trong Docs Mây Hẹn | Định nghĩa & Ngữ cảnh Sử dụng |
| :--- | :--- | :--- |
| **Studio Editor V2** | **Trình tạo thiệp cưới trực quan** | Không gian nơi cô dâu chú rể trực tiếp chỉnh sửa nội dung, hình ảnh và âm nhạc của thiệp cưới. |
| **Live Sync** | **Đồng bộ thời gian thực** | Cơ chế tự động phản ánh mọi thay đổi từ khung soạn thảo sang màn hình xem trước tức thì mà không cần tải lại trang. |
| **10 Standard Sections** | **10 Phần tiêu chuẩn của thiệp cưới** | Khung cấu trúc hoàn chỉnh của thiệp Mây Hẹn (Mở đầu, Câu chuyện tình yêu, Phụ mẫu & Uyên ương, Đếm ngược, Sự kiện & Bản đồ, Lịch trình, Dresscode, Album 10 ảnh, Mừng cưới VietQR & Sổ ký tên, Lời kết). |
| **RSVP** | **Xác nhận tham dự** | Tính năng cho phép khách mời báo trước việc có mặt hay vắng mặt, số người đi cùng và sở thích ăn uống. |
| **Seating Planner** | **Sơ đồ xếp bàn tiệc** | Công cụ sắp xếp chỗ ngồi cho từng vị khách vào các bàn tiệc 10-12 người. |
| **Check-in Portal** | **Cổng điểm danh tiệc cưới** | Giao diện dành riêng cho đội lễ tân tại sảnh tiệc để quét mã QR và đánh dấu khách đã có mặt. |
| **Live Wall** | **Màn hình chiếu sân khấu** | Giao diện hiển thị trực tiếp lời chúc và ảnh cưới của cặp đôi lên màn hình LED lớn tại sảnh tiệc. |
| **Time Capsule** | **Hộp thời gian ngày cưới** | Nơi lưu giữ những lời chúc và kỷ vật tương lai, tự động mở khóa vào các dịp kỷ niệm ngày cưới (1 năm, 5 năm...). |
| **Quality Doctor** | **Bác sĩ kiểm tra chất lượng thiệp** | Tính năng tự động rà soát ảnh mờ, đường link hỏng hoặc thiếu sót thông tin trước khi cặp đôi gửi thiệp cho quan khách. |
| **SePay Webhook** | **Hệ thống thanh toán tự động SePay** | Cổng tiếp nhận thông báo chuyển khoản ngân hàng tự động, giúp kích hoạt thiệp ngay lập tức sau 3-5 giây. |
| **Audit Log** | **Nhật ký kiểm toán hệ thống** | Bản ghi không thể xóa ghi lại ai đã làm gì, vào lúc nào trên hệ thống quản trị Admin để bảo vệ an toàn dữ liệu. |

