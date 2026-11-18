---
id: CLI-EDIT-001
title: "Làm quen giao diện Studio Tạo Thiệp: Chạm sửa trực tiếp trên thiệp, thanh công cụ và xem trước"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn chi tiết từng bước làm chủ không gian thiết kế thiệp cưới tại /tao-thiep: 4 phân vùng trực quan, chạm trực tiếp vào chữ và ảnh trên thiệp để sửa ngay, chọn 12 phông chữ Tiếng Việt, đổi mẫu giữ nguyên dữ liệu và xem thử như khách mời."
reader: "Cô dâu & Chú rể"
order: 1
updated_at: "2026-11-18"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, live-canvas, smart-sync, direct-edit]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Làm quen giao diện Studio Tạo Thiệp: Chạm sửa trực tiếp trên thiệp, thanh công cụ và xem trước

> **Dành cho:** Cô dâu & Chú rể tự tay thiết kế thiệp cưới online lần đầu  
> **Thời gian hoàn thành thiệp cơ bản:** Khoảng 3 – 5 phút  
> **Đường dẫn thực hiện:** Trang Tạo Thiệp `/tao-thiep` (ví dụ: `/tao-thiep?template=vom-troi-may-ao-cuoi`)  

Không gian **Studio Tạo Thiệp** của Mây Hẹn được thiết kế theo tinh thần **Quiet Luxury** — sáng rõ, tối giản và cực kỳ dễ thao tác. Bạn vừa có thể **nhập thông tin lần lượt theo từng bước ở cột bên trái**, vừa có thể **bấm trực tiếp vào bất kỳ dòng chữ hoặc bức ảnh nào ngay trên tấm thiệp xem trước** để chỉnh sửa tại chỗ mà không mất công đi tìm từng ô nhập liệu.

![Toàn cảnh 4 vùng làm việc của Studio Tạo Thiệp trên mẫu Vòm Trời May Áo Cưới](/assets/client/docs/images/client/02-studio-editor/cli-edit-001-step-1.png)
*Giao diện Studio Tạo Thiệp với mẫu Vòm Trời May Áo Cưới: Thanh đầu trang, cột nhập liệu từng bước bên trái, màn hình thiệp trực tiếp ở giữa và bảng phông chữ Tiếng Việt bên phải.*

---

## 1. Bốn phân vùng làm việc chính trên màn hình Studio

Khi mở trang `/tao-thiep`, màn hình máy tính được chia thành **4 khu vực rõ ràng** phối hợp nhịp nhàng với nhau:

```
┌──────────────────────────────────────────────────────────────────────────────────────┐
│ 1. THANH ĐẦU TRANG (STUDIO TOPBAR)                                                   │
│ [←] Studio Tạo Thiệp [Đổi mẫu] [Đã lưu nháp]  [↺ ↻] [📱 🖥] [75% 100% 115%]          │
│                                     [Xem như khách] [🩺 100đ] [? Hướng dẫn] [Xuất Bản]│
├────────────┬─────────────────────────┬─────────────────────────┬─────────────────────┤
│ 2. THANH   │ KHUNG NHẬP LIỆU & TAB   │ 3. MÀN HÌNH XEM TRƯỚC   │ 4. BẢNG TÙY CHỈNH   │
│ CÔNG CỤ DỌC│                         │    TRỰC TIẾP (LIVE SYNC)│    BÊN PHẢI         │
│            │ [1 Trang Bìa][2 Love..] │  ┌───────────────────┐  │                     │
│ • Nội Dung │                         │  │ Chạm 1 lần để chọn│  │ • 12 Font Tiếng Việt│
│ • Bố Cục   │ • Lời mời buổi tiệc     │  │ Nhấp đúp sửa ngay │  │ • Cỡ chữ, màu chữ   │
│ • Màu Sắc  │ • Họ và tên Chú rể      │  │                   │  │ • Căn lề, giãn dòng │
│ • Trang Trí│ • Họ và tên Cô dâu      │  │   Minh Tiến &     │  │ • Điểm tiêu cự ảnh  │
│ • Âm Nhạc  │ • Ngày cử hành (Âm lịch)│  │   Thu Nguyệt      │  │ • 10 Hiệu ứng mượt  │
│ • Trợ Lý AI│ • Ảnh bìa chính         │  └───────────────────┘  │                     │
│ • Đổi Mẫu  │ [← Quay Lại] [Tiếp Tục →]│                         │                     │
└────────────┴─────────────────────────┴─────────────────────────┴─────────────────────┘
```

### Vùng 1: Thanh điều khiển đầu trang (Studio Topbar)
Nằm ngang trên cùng màn hình, tập hợp các nút điều hướng và kiểm tra nhanh:
- **Nút Quay lại (`←`):** Trở về kho mẫu thiệp cưới.
- **Thẻ Mẫu đang chọn & nút `Đổi mẫu`:** Hiển thị ảnh thu nhỏ cùng tên mẫu hiện tại (ví dụ: *Vòm Trời May Áo Cưới*). Bấm vào đây để đổi sang mẫu thiệp khác bất cứ lúc nào mà **không mất nội dung đã gõ**.
- **Trạng thái lưu tự động:** Báo rõ `Đã sẵn sàng`, `Đang lưu...` hoặc `Đã lưu nháp` theo thời gian thực.
- **Hoàn tác / Làm lại (`↺` / `↻`):** Quay lui hoặc tiến tới tối đa 50 bước chỉnh sửa gần nhất (phím tắt `Ctrl + Z` và `Ctrl + Y`).
- **Chuyển khung thiết bị (`📱 Điện thoại` / `🖥 Máy tính`) & Thu phóng (`75%`, `100%`, `115%`):** Kiểm tra bố cục thiệp trên cả màn hình dọc di động lẫn màn hình ngang máy tính.
- **Nút `Xem như khách`:** Tắt tạm thời các khung viền chọn thiết kế để bạn cuộn xem thiệp, mở phong bì và nghe nhạc y hệt trải nghiệm của khách mời thật.
- **Nút `🩺 100đ` (Bác Sĩ Thiệp Cưới):** Tự động chấm điểm độ hoàn thiện trên thang 100 và chỉ rõ mục nào còn bỏ trống.
- **Nút Hướng dẫn (?):** Mở lại hướng dẫn tương tác chiếu sáng từng bước ngay trên màn hình bất cứ lúc nào mà không ảnh hưởng đến nội dung đã nhập.
- **Nút `Xuất Bản Thiệp →`:** Lưu hoàn tất và tạo đường dẫn thiệp cưới chính thức để gửi cho người thân, bạn bè.

### Vùng 2: Thanh công cụ dọc bên trái & Khung nhập liệu
Thanh dọc ngoài cùng bên trái gồm **7 nhóm công cụ thực tế**:
1. **Nội Dung:** Điền thông tin đám cưới theo 10 bước tuần tự từ *1. Trang Bìa*, *2. Love Story*, *3. Gia Đình*, *4. Đếm Ngược*, *5. Sự Kiện*, *6. Lịch Trình*, *7. Trang Phục*, *8. Ảnh Cưới*, *9. Mừng Cưới* đến *10. Cảm Ơn*.
2. **Bố Cục:** Kéo thả sắp xếp lại vị trí hoặc bấm biểu tượng con mắt để ẩn bớt những phần gia đình không sử dụng.
3. **Màu Sắc:** Chọn 1 trong 6 bảng màu *Quiet Luxury* phối sẵn, đổi màu con dấu sáp hoàng gia và ký tự dập nổi Monogram.
4. **Trang Trí:** Chèn thêm khối chữ mới, ảnh trang trí, 10 mẫu dấu sáp & sticker SVG đổi màu, 8 khung ảnh nghệ thuật và hình khối ngay giữa vùng thiệp đang xem.
5. **Âm Nhạc:** Chọn giai điệu từ kho hơn 20 bản nhạc cưới lãng mạn bản quyền.
6. **Trợ Lý AI:** Gợi ý lời mời cưới, câu chuyện tình yêu, lời cảm ơn, soát lỗi câu chữ và dịch sang Tiếng Anh, Tiếng Trung, Tiếng Hàn.
7. **Đổi Mẫu:** Mở nhanh thư viện mẫu thiệp để thử khoác giao diện mới cho dữ liệu hiện có.

### Vùng 3: Khung xem trước trực tiếp (Live Sync & Chạm sửa tại chỗ)
- Mọi thay đổi ở cột bên trái đều cập nhật sang tấm thiệp ở giữa tức thì nhờ cơ chế đồng bộ hai chiều **Live Sync** mà không cần tải lại trang.
- Đặc biệt, bạn có thể **chạm chuột trực tiếp lên thiệp**: chạm 1 lần để chọn phần tử và mở bảng chỉnh kiểu dáng; nhấp đúp vào chữ để gõ sửa nội dung tiếng Việt ngay tại chỗ, hoặc nhấp đúp vào ảnh để tải ảnh mới.

### Vùng 4: Bảng tùy chỉnh trực quan bên phải
- Khi chưa chọn phần tử nào: Hiển thị bộ **Phông Chữ Toàn Thiệp** gồm 12 phông chữ Tiếng Việt sắc nét cho *Font Tiêu Đề Chính*, *Font Nội Dung Đọc* và *Font Chữ Ký Nghệ Thuật*.
- Khi bạn bấm chọn một dòng chữ, bức ảnh hay nhãn dán trên thiệp: Bảng bên phải lập tức chuyển sang **Thuộc Tính & Kiểu** (đổi font riêng, cỡ chữ, màu sắc, căn lề, điểm tiêu cự ảnh) và tab **Chuyển Động** (10 hiệu ứng xuất hiện mượt mà).

---

## 2. Hướng dẫn tương tác tự động cho người mới bắt đầu

Ngay lần đầu tiên bạn truy cập `/tao-thiep`, hệ thống sẽ tự động bật **Hướng dẫn tương tác từng bước**: màn hình dịu tối nhẹ và khung sáng spotlight sẽ di chuyển mượt mà qua từng khu vực quan trọng kèm hộp giải thích ngắn gọn bằng tiếng Việt.

![Hướng dẫn tương tác từng bước chiếu sáng trực tiếp trên giao diện Studio](/assets/client/docs/images/client/02-studio-editor/cli-edit-001-step-2.png)
*Chế độ Hướng dẫn tương tác: Làm nổi bật đúng nút chức năng kèm thanh tiến độ, nút Quay lại, Tiếp theo và Bỏ qua.*

- Bạn bấm **Tiếp theo** để xem lần lượt, **Quay lại** để xem bước trước, hoặc **Bỏ qua** nếu đã quen thao tác.
- Khi bạn đã xem xong hoặc bấm **Bỏ qua**, hệ thống tự ghi nhớ để những lần mở sau không tự hiện lại gây phiền.
- Bất cứ khi nào muốn xem lại, bạn chỉ cần bấm nút **`? Hướng dẫn`** trên thanh đầu trang. Hướng dẫn chỉ minh họa giao diện, tuyệt đối không làm thay đổi chữ hay ảnh bạn đã nhập.

---

## 3. Hai cách chỉnh sửa thiệp cực nhanh (Ai cũng làm được)

### Cách 1: Chạm trực tiếp lên tấm thiệp xem trước (Nhanh và trực quan nhất)
1. Đưa chuột vào màn hình thiệp ở giữa, **bấm 1 lần** vào tên cô dâu chú rể, ngày cưới, lời ngỏ hoặc bức ảnh bất kỳ.
2. Một viền vàng mảnh sẽ bao quanh phần tử kèm thanh công cụ nổi ngay phía trên (**`✎ Sửa chữ`**, **`Kiểu chữ`**, **`Hiệu ứng`**, **`Đặt lại`**).
3. Bấm **`✎ Sửa chữ`** (hoặc nhấp đúp chuột trực tiếp vào dòng chữ) để gõ nội dung mới ngay trên thiệp. Nội dung bạn sửa sẽ tự động đồng bộ ngược về ô nhập liệu bên trái!
4. Nhìn sang cột bên phải để đổi phông chữ, tăng giảm cỡ chữ, chọn màu chữ hoặc gắn hiệu ứng chuyển động riêng cho dòng chữ đó.

![Bấm trực tiếp vào tên trên thiệp để mở thanh công cụ nổi và bảng chỉnh kiểu chữ bên phải](/assets/client/docs/images/client/02-studio-editor/cli-edit-001-step-3.png)
*Chạm trực tiếp vào chữ trên thiệp Vòm Trời May Áo Cưới: Hiện ngay thanh công cụ nổi và bảng chỉnh kiểu chữ chi tiết ở cột phải.*

### Cách 2: Điền lần lượt theo 10 bước hướng dẫn ở cột trái
Nếu bạn muốn rà soát đầy đủ từ đầu đến cuối không bỏ sót mục nào:
1. Chọn mục **Nội Dung** trên thanh dọc bên trái.
2. Điền thông tin ở **Bước 1 (Trang Bìa)**, sau đó bấm nút **`Tiếp Tục →`** ở chân cột trái (hoặc bấm trực tiếp vào các thẻ số `1`, `2`, `3`... trên thanh ngang) để đi qua lần lượt 10 phần của thiệp.
3. Mỗi khi bạn chuyển sang bước nào, màn hình xem trước ở giữa sẽ **tự động cuộn mượt đến đúng phần đó** trên tấm thiệp để bạn kiểm tra ngay lập tức.

---

## 4. Kiểm tra thiệp trên màn hình Máy tính, Chế độ Khách mời & Điện thoại

Trước khi bấm xuất bản, bạn nên dành 30 giây kiểm tra trải nghiệm thực tế bằng 3 chế độ có sẵn:

### Xem thử giao diện Máy tính rộng
Bấm biểu tượng **Màn hình máy tính** bên cạnh biểu tượng điện thoại trên thanh đầu trang để mở rộng khung xem trước, giúp kiểm tra bố cục thiệp khi bạn bè, đồng nghiệp mở link trên laptop văn phòng.

![Chế độ xem trước khung màn hình Máy tính rộng trên Studio](/assets/client/docs/images/client/02-studio-editor/cli-edit-001-step-4.png)
*Chuyển sang khung xem trước Máy tính để kiểm tra độ cân đối của ảnh bìa vòm lá và chữ viết trên màn hình lớn.*

### Trải nghiệm thật với nút "Xem như khách"
Bấm nút **`Xem như khách`** trên thanh đầu trang. Toàn bộ khung viền chỉnh sửa sẽ ẩn đi để bạn tự tay cuộn thiệp, bấm mở bản đồ, xem hiệu ứng chuyển động và kiểm tra sổ ký tên đúng 100% như quan khách nhận thiệp. Bấm lại nút này một lần nữa để quay về chế độ chỉnh sửa.

![Bật chế độ Xem như khách trên thanh đầu trang Studio](/assets/client/docs/images/client/02-studio-editor/cli-edit-001-step-5.png)
*Chế độ Xem như khách giúp bạn kiểm tra trọn vẹn cảm xúc lướt thiệp mà không vướng khung chọn thiết kế.*

### Thiết kế thiệp ngay trên Điện thoại di động
Nếu bạn mở `/tao-thiep` bằng điện thoại, giao diện tự động thu gọn thông minh với thanh chuyển đổi **`Nhập Liệu ⇄ Xem Thiệp`** ở đầu trang và thanh tác vụ nhanh ở cạnh dưới (**`Sửa Thiệp`**, **`Đổi Mẫu`**, **`Xuất Bản`**), giúp ngón tay thao tác thoải mái không bị chật màn hình.

![Giao diện Studio Tạo Thiệp tối ưu gọn gàng trên màn hình Điện thoại di động](/assets/client/docs/images/client/02-studio-editor/cli-edit-001-step-6.png)
*Giao diện Studio trên điện thoại di động: Chuyển mượt giữa màn hình Nhập Liệu và Xem Thiệp chỉ với 1 chạm.*

---

## 5. Phím tắt tiện lợi khi thiết kế trên máy tính

- **`Ctrl + Z`** (hoặc `Cmd + Z` trên Mac): Hoàn tác thao tác vừa chỉnh sửa.
- **`Ctrl + Y`** (hoặc `Cmd + Shift + Z`): Làm lại thao tác vừa hoàn tác.
- **`Delete` / `Backspace`**: Xóa nhanh nhãn dán trang trí hoặc khung ảnh phụ đang chọn (khi không ở chế độ gõ chữ).
- **`Esc`**: Bỏ chọn phần tử đang sửa trên thiệp hoặc đóng hộp thoại đang mở.

> [!TIP]
> **Mẹo thiết kế thiệp đẹp và nhanh nhất:**  
> Hãy điền xong họ tên, ngày cưới, địa chỉ tiệc ở tab **Nội Dung** trước, sau đó bấm **`🩺 100đ`** để Bác Sĩ Thiệp Cưới kiểm tra không sót thông tin, rồi mới tinh chỉnh bảng màu, phông chữ và nhãn dán trang trí theo sở thích của hai bạn.

---

## 6. Xem tiếp các hướng dẫn chi tiết từng phần

- [Thay ảnh cưới, cắt ảnh theo khung và căn chỉnh điểm tiêu cự Smart Focal Point](/docs/studio-editor/CLI-EDIT-002)
- [Điền thông tin cô dâu chú rể, phụ mẫu hai bên, ngày Âm lịch tự động và địa điểm sự kiện](/docs/studio-editor/CLI-EDIT-003)
- [Phối bảng màu Quiet Luxury, 12 phông chữ Tiếng Việt, dấu sáp và hiệu ứng chuyển động](/docs/studio-editor/CLI-EDIT-004)
- [Sắp xếp bố cục 10 Section, cài đặt mừng cưới VietQR và sổ ký tên RSVP](/docs/studio-editor/CLI-EDIT-005)
