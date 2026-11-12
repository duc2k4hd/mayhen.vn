# MÂY HẸN — TÀI LIỆU 07: CÁC BÀI VIẾT THÍ ĐIỂM & BẢN THẢO MẪU THỰC TẾ
## Docs Pilot Articles & Review — 6 Đề Cương Thí Điểm & 2 Bài Mẫu Hoàn Chỉnh

> **Mã tài liệu:** `DOCS-PILOT-07`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Nguyên tắc biên soạn:** Viết hoàn toàn bằng tiếng Việt tự nhiên, ấm áp, nhã nhặn, bám sát từng nút bấm và logic mã nguồn thực tế.  

---

## PHẦN I: DANH MỤC 6 ĐỀ CƯƠNG BÀI VIẾT THÍ ĐIỂM (6 PILOT ARTICLES OUTLINES)

### 1. Phân hệ Khách hàng (Client Pilot Suite)
1. **CLI-PILOT-01 (`CLI-START-002`):** *Cách tạo tài khoản và hoàn tất khảo sát phong cách cưới (Onboarding 4 bước).*
   - *Mục tiêu:* Hướng dẫn cô dâu chú rể chọn phong cách Quiet Luxury, Minimal hay Romantic, điền tên uyên ương và chọn mẫu thiệp ưng ý nhất trong 2 phút.
2. **CLI-PILOT-02 (`CLI-EDIT-002`):** *Cách thay đổi ảnh cưới chính và chọn tiêu điểm khuôn mặt (Smart Focal Point).*
   - *Mục tiêu:* Hướng dẫn tải ảnh lên Studio, di chuyển điểm ngắm để ảnh hiển thị hoàn hảo trên cả iPhone lẫn màn hình máy tính mà không bị mất đầu/mất chữ.
3. **CLI-PILOT-03 (`CLI-GUEST-004`):** *Hướng dẫn khách mời xác nhận tham dự (RSVP) đa sự kiện và đăng ký khẩu phần ăn.*
   - *Mục tiêu:* Hướng dẫn khách mở thiệp, chọn đi lễ/tiệc, nhập người đi kèm, báo món ăn kiêng và nhận ngay vị trí bàn tiệc.

### 2. Phân hệ Quản trị viên (Admin Pilot Suite)
4. **ADM-PILOT-01 (`ADM-USER-001`):** *Quy trình tra cứu hồ sơ khách hàng toàn diện (Customer 360 View) và hỗ trợ an toàn.*
   - *Mục tiêu:* Nhân viên CSKH mới tra cứu thông tin thiệp, đơn hàng, lịch sử chỉnh sửa của khách để trả lời ticket hỗ trợ trong 60 giây.
5. **ADM-PILOT-02 (`ADM-ORDR-002`):** *Quy trình đối soát SePay và kích hoạt đơn hàng chuyển khoản thủ công an toàn.*
   - *Mục tiêu:* Kế toán đối chiếu tiền nổi thực tế tại ngân hàng, xử lý đơn chuyển khoản sai nội dung, kích hoạt gói dịch vụ và ghi nhận Audit Log.
6. **ADM-PILOT-03 (`ADM-COCK-002`):** *Sổ tay xử lý sự cố nghẽn mạng sảnh tiệc và cứu hộ điểm danh lễ tân ngoại tuyến.*
   - *Mục tiêu:* Đội ngũ kỹ thuật hỗ trợ cô dâu chú rể kích hoạt chế độ Emergency Offline Pack khi sảnh tiệc khách sạn bị mất sóng 4G/Wi-Fi.

---

## PHẦN II: HAI BẢN THẢO HOÀN CHỈNH VIẾT NHƯ NGƯỜI THẬT

### BÀI MẪU 1 (CLIENT): CLI-EDIT-002 — CÁCH THAY ĐỔI ẢNH CƯỚI & CHỌN TIÊU ĐIỂM THÔNG MINH TRONG STUDIO

```markdown
# Cách thay đổi ảnh cưới và chọn tiêu điểm khuôn mặt trong Studio

> **Dành cho:** Cô dâu, chú rể đang thiết kế thiệp cưới  
> **Thời gian thực hiện:** Khoảng 2 - 3 phút  
> **Vị trí trong ứng dụng:** Trang quản lý thiệp -> Nhấp nút **[Chỉnh sửa thiệp]** -> Tab **Hình ảnh & Album**  

Bức ảnh cưới đẹp nhất chính là linh hồn của tấm thiệp. Tuy nhiên, màn hình điện thoại của mỗi khách mời lại có tỷ lệ dài ngắn khác nhau (iPhone, Samsung, iPad). Tính năng **Tiêu điểm Thông minh (Smart Focal Point)** của Mây Hẹn sẽ giúp khuôn mặt của hai bạn luôn nằm ở vị trí trang trọng nhất, không bao giờ bị cắt mất hay che khuất bởi chữ.

---

### 1. Chuẩn bị trước khi tải ảnh
- Chọn bức ảnh cưới sắc nét, ánh sáng tự nhiên, tôn vinh nụ cười hạnh phúc của hai bạn.
- Định dạng ảnh được hỗ trợ: **JPG, PNG hoặc WebP**.
- Dung lượng mỗi ảnh: Tối đa **10MB** (hệ thống sẽ tự động tối ưu hóa giúp thiệp mở cực nhanh trên mạng 4G/5G).

---

### 2. Các bước thực hiện chi tiết

#### Bước 1: Mở trình tạo thiệp (Studio)
Từ trang quản lý tài khoản (`/tai-khoan`), bạn tìm tấm thiệp cưới đang thực hiện và nhấp vào nút **[Vào Studio chỉnh sửa]**.

#### Bước 2: Chọn vị trí ảnh muốn thay đổi
Trên thanh công cụ bên trái màn hình, nhấp vào mục **Ảnh cưới & Bìa chính**:
- **Ảnh bìa đại diện (Cover Photo):** Xuất hiện đầu tiên khi khách nhận được phong bì thiệp lụa.
- **Ảnh cổng chào (Hero Photo):** Bức ảnh lớn mở đầu khi mở cánh thiệp cưới.
- **Album 10 khoảnh khắc tình yêu:** Bộ sưu tập ảnh kỷ niệm của hai bạn.

Nhấp trực tiếp vào khung ảnh bạn muốn đổi, hoặc nhấn nút **[Tải ảnh mới]**.

#### Bước 3: Tải ảnh từ thiết bị
Chọn bức ảnh cưới từ điện thoại hoặc máy tính của bạn. Trong vòng 1-2 giây, thanh tiến trình tải ảnh sẽ hoàn tất và ảnh sẽ xuất hiện ngay trong khung chỉnh sửa.

#### Bước 4: Di chuyển điểm ngắm tiêu điểm (Smart Focal Point)
Khi ảnh hiện lên, bạn sẽ thấy một **vòng tròn tiêu điểm màu vàng Champagne** nằm trên bức ảnh:
1. Dùng chuột (hoặc ngón tay trên điện thoại) chạm và giữ vòng tròn đó.
2. Kéo vòng tròn đặt vào chính giữa khuôn mặt của cô dâu và chú rể.
3. Nhìn sang màn hình điện thoại mô phỏng bên phải: Bạn sẽ thấy dù chuyển qua lại giữa chế độ iPhone hay màn hình máy tính, khuôn mặt của hai bạn vẫn luôn được canh giữa hoàn hảo!

#### Bước 5: Lưu thay đổi
Nhấp vào nút **[Lưu thay đổi]** ở góc trên bên phải thanh công cụ.

---

### 3. Dấu hiệu nhận biết đã thành công
- Màn hình góc trên xuất hiện thông báo nhỏ trang nhã màu xanh: *"Đã lưu ảnh cưới và tiêu điểm thành công"*.
- Màn hình xem thử trực tiếp bên cạnh lập tức cập nhật bức ảnh mới.
- Mở link thiệp trên điện thoại của bạn, ảnh tải mượt mà và hiển thị đúng khuôn mặt ở vị trí trung tâm.

---

### 4. Cách xử lý các sự cố thường gặp

#### Ảnh báo lỗi "Dung lượng vượt quá giới hạn"?
- **Nguyên nhân:** File ảnh gốc từ studio xuất ra ở độ phân giải in ấn (file TIFF/RAW hoặc JPG nặng trên 15MB - 20MB).
- **Cách xử lý:** Bạn có thể gửi bức ảnh đó qua Zalo ở chế độ "HD" rồi lưu lại về máy, hoặc sử dụng trang web nén ảnh miễn phí uy tín (`tinypng.com`) để giảm nhẹ dung lượng xuống dưới 5MB trước khi tải lên.

#### Mất kết nối mạng khi đang tải ảnh?
- **Yên tâm:** Mây Hẹn có cơ chế bảo toàn dữ liệu nháp (Draft Recovery). Hãy giữ nguyên tab trình duyệt, kiểm tra lại kết nối Wi-Fi/4G và nhấp nút **[Thử lại]**. Toàn bộ các thông tin chữ viết khác bạn vừa nhập đều được giữ nguyên 100%.

---

### 5. Câu hỏi thường gặp (FAQ)
- **Tôi đã gửi thiệp cho khách mời rồi, bây giờ đổi ảnh khác có được không?**  
  *Hoàn toàn được! Mọi thay đổi trong Studio đều được đồng bộ tức thời. Khách mời mở lại link thiệp sẽ thấy bức ảnh mới nhất ngay lập tức mà bạn không cần phải gửi lại link.*
- **Có giới hạn số lần đổi ảnh không?**  
  *Không. Bạn có thể thay đổi ảnh bao nhiêu lần tùy thích trong suốt thời hạn hoạt động của thiệp.*
```

---

### BÀI MẪU 2 (ADMIN): ADM-ORDR-002 — QUY TRÌNH ĐỐI SOÁT SEPAY & KÍCH HOẠT ĐƠN HÀNG THỦ CÔNG AN TOÀN

```markdown
# ADM-SOP-ORDR-002: Quy trình Đối soát SePay & Kích hoạt Đơn hàng Chuyển khoản Thủ công

> **Mã quy trình:** `ADM-SOP-ORDR-002`  
> **Đối tượng thực hiện:** Nhân viên Kế toán (Finance) & Quản lý vận hành (Manager)  
> **Mức độ rủi ro:** CAO (Ảnh hưởng đến doanh thu thực và trạng thái kích hoạt gói dịch vụ)  
> **Bằng chứng bắt buộc:** Mã giao dịch ngân hàng thực tế (FT ID / Ref Code) trong sao kê  
> **Điều kiện tiên quyết:** Khách đã chuyển khoản thực tế nhưng đơn hàng ở trạng thái "pending" quá 10 phút  

---

### 1. Mục đích nghiệp vụ
Xử lý các tình huống hy hữu khi khách hàng chuyển khoản qua VietQR nhưng hệ thống chưa tự động kích hoạt gói:
- Khách hàng vô tình sửa nội dung chuyển khoản (bỏ bớt ký tự mã đơn, điền tên cô dâu chú rể thay vì mã đơn).
- Khách chuyển khoản khác ngân hàng vào khung giờ bảo trì hệ thống NAPAS (tiền treo).
- Webhook IPN từ đối tác cổng SePay bị trễ mạng hoặc lỗi đường truyền tạm thời.

Quy trình này hướng dẫn nhân viên đối soát minh bạch, kích hoạt gói kịp thời cho khách mà không gây thất thoát tài chính.

---

### 2. Các bước thao tác an toàn (Step-by-step Execution)

#### Bước 1: Tiếp nhận yêu cầu & Xác minh thông tin đơn hàng
1. Đăng nhập trang quản trị Admin tại `/admin/login`.
2. Truy cập menu **Tài chính & Đơn hàng** -> nhấp vào **Danh sách đơn hàng** (`/admin/orders`).
3. Nhập Số điện thoại, Email hoặc Tên cô dâu/chú rể vào ô tìm kiếm.
4. Mở chi tiết đơn hàng:
   - Ghi nhận **Mã đơn hàng** (ví dụ: `MH-2026-9812`).
   - Ghi nhận **Số tiền chính xác cần thanh toán** (ví dụ: `499,000 đ`).
   - Kiểm tra tab **Webhook Logs**: Xem hệ thống có nhận được tín hiệu nào từ SePay cho đơn này chưa.

#### Bước 2: Kiểm tra dòng tiền thực tế tại Tài khoản Ngân hàng Doanh nghiệp
1. Đăng nhập App Ngân hàng Doanh nghiệp của Mây Hẹn (Vietcombank / MBBank) hoặc Dashboard cổng SePay.
2. Tìm kiếm biến động số dư trong ngày:
   - *Số tiền:* Khớp đúng 100% với giá trị đơn hàng.
   - *Thời gian chuyển:* Trong khoảng thời gian từ lúc tạo đơn đến hiện tại.
   - *Tên người gửi:* Đối chiếu tên chủ tài khoản chuyển tiền với tên khách hàng.
   - *Ghi chép:* Lưu lại **Mã chuẩn chi ngân hàng (Reference Code / FT Number)** (ví dụ: `FT263158912401`).

> [!CAUTION]
> **QUY TẮC SỐ 1:** TUYỆT ĐỐI KHÔNG kích hoạt đơn hàng chỉ dựa trên ảnh chụp màn hình ủy nhiệm chi do khách gửi qua Zalo/Email. Rất nhiều trường hợp ảnh biên lai bị làm giả bằng công cụ đồ họa. Chỉ được thao tác khi tiền ĐÃ NỔI THỰC TẾ trên tài khoản công ty.

#### Bước 3: Thao tác Kích hoạt Thủ công trên Giao diện Admin
1. Tại trang chi tiết đơn hàng `/admin/orders/{id}`, nhấp vào nút **[Kích hoạt thủ công]** (nút màu vàng hổ phách).
2. Một hộp thoại xác nhận bảo mật sẽ hiện lên, yêu cầu điền đầy đủ 3 thông tin:
   - **Mã tham chiếu ngân hàng (Bank Ref ID):** Dán mã `FT263158912401` vừa lấy từ sao kê.
   - **Số tiền thực nhận:** Nhập đúng số tiền hiển thị trên sao kê (ví dụ: `499000`).
   - **Lý do can thiệp:** Ghi rõ nguyên nhân, ví dụ: *"Khách chuyển khoản ghi nội dung 'Tung Lan cuoi' thay vi ma MH-9812, da doi soat khop so tien tren VCB luc 14:15"*.
3. Kiểm tra kỹ lại thông tin và nhấp **[Xác nhận kích hoạt]**.

---

### 3. Kết quả mong đợi & Tác động hệ thống
Ngay sau khi nhấp xác nhận:
1. **Trạng thái đơn hàng:** Tự động chuyển từ `pending` sang `paid`.
2. **Kích hoạt quyền lợi:** Gói dịch vụ của thiệp cưới tương ứng lập tức được mở khóa (khách có thể xuất bản thiệp ngay).
3. **Kiểm toán (Audit Log):** Hệ thống ghi nhận 1 bản ghi kiểm toán bất biến:
   `User [ID: 5 - Nguyen Van A] executed MANUAL_PAYMENT_OVERRIDE on Order [MH-9812] with Ref [FT263158912401]`.
4. **Thông báo khách hàng:** Hệ thống tự động gửi email chúc mừng và hóa đơn điện tử về hòm thư của cô dâu chú rể.

---

### 4. Những điều TUYỆT ĐỐI BỊ CẤM đối với Nhân viên
- **CẤM:** Không được kích hoạt đơn hàng khi số tiền thực nhận nhỏ hơn giá trị đơn hàng (trừ khi có văn bản phê duyệt mã giảm giá đặc biệt từ Giám đốc).
- **CẤM:** Không được nhập mã tham chiếu giả mạo hoặc ghi lý do qua loa như *"kích hoạt hộ", "quen biết"*. Mọi hành vi kích hoạt sai quy định sẽ bị truy thu tài chính và xử lý kỷ luật.
- **CẤM:** Không được chia sẻ mật khẩu tài khoản Admin hoặc nhờ đồng nghiệp thao tác hộ.

---

### 5. Quy trình Chuyển cấp Khẩn cấp (Escalation)
Nếu phát hiện 1 mã giao dịch ngân hàng bị dùng trùng lặp cho 2 đơn hàng khác nhau, hoặc nghi vấn khách hàng lợi dụng kẽ hở gian lận:
1. Giữ nguyên trạng thái đơn hàng ở mức `pending`.
2. Khóa tạm thời quyền chỉnh sửa của thiệp liên quan.
3. Báo cáo ngay cho **Trưởng bộ phận Vận hành & Kế toán trưởng** qua kênh khẩn cấp trong vòng **15 phút**.
```

---

## PHẦN III: BẢNG KIỂM TRA CHUYÊN MÔN DÀNH CHO BIÊN TẬP VIÊN (EDITORIAL REVIEW CHECKLIST)

Trước khi bất kỳ bài viết nào trong số 100 bài được đưa vào hoạt động chính thức, biên tập viên nội dung phải tự kiểm tra và tích đủ 8 tiêu chí:

| Tiêu Chí Đánh Giá | Mô Tả Kiểm Tra | Trạng Thái Đạt |
| :--- | :--- | :--- |
| **1. Tính chính xác nút bấm** | Tên các nút, menu, trường dữ liệu trong bài viết trùng khớp 100% với mã nguồn Blade/JS thật của trang đó. | [ ] ĐẠT |
| **2. Giọng điệu con người** | Không có câu mở đầu AI sáo rỗng; văn phong ân cần, lịch thiệp, dễ hiểu cho người lớn tuổi lẫn bạn trẻ. | [ ] ĐẠT |
| **3. Trọn vẹn 5 câu hỏi** | Nêu rõ: Chức năng làm gì, tìm ở đâu, các bước thao tác, cách nhận biết thành công, và cách khắc phục lỗi. | [ ] ĐẠT |
| **4. Nhánh xử lý sự cố** | Luôn có ít nhất 2 tình huống lỗi thực tế (ví dụ: ảnh quá nặng, mạng yếu, trùng lặp) kèm giải pháp cụ thể. | [ ] ĐẠT |
| **5. Bảo mật PII & Dữ liệu** | Ảnh chụp màn hình (nếu có) phải che mờ tên thật, số điện thoại, số tài khoản ngân hàng và địa chỉ nhà. | [ ] ĐẠT |
| **6. Chuẩn ngữ nghĩa Heading** | Chỉ có duy nhất 1 thẻ `<h1>`, các đề mục tuần tự `<h2>`, `<h3>` để mục lục TOC cuộn mượt mà. | [ ] ĐẠT |
| **7. Hộp lưu ý Callouts** | Sử dụng đúng màu sắc: Xanh (Tip), Vàng (Note), Cam (Warning), Đỏ hồng (Security). Không lạm dụng quá nhiều hộp. | [ ] ĐẠT |
| **8. Liên kết bài liên quan** | Có phần "Bài tiếp theo" dẫn dắt người đọc đi tiếp một mạch trải nghiệm trọn vẹn. | [ ] ĐẠT |

