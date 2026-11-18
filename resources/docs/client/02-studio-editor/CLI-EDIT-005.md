---
id: CLI-EDIT-005
title: "Sắp xếp, ẩn/hiện 10 Section tiêu chuẩn, Lịch trình, Dresscode, VietQR và Sổ ký tên RSVP"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn tùy biến bố cục 10 Section Tiêu Chuẩn của thiệp cưới Mây Hẹn: kéo thả đổi thứ tự, bật/tắt hiển thị từng phần, thiết lập lịch trình hôn lễ, bảng màu Dresscode, tạo mã VietQR tự động và phần kết RSVP."
reader: "Cô dâu & Chú rể"
order: 10
updated_at: "2026-11-18"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, bo-cuc, 10-sections, flow-layout, vietqr, dresscode]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Sắp xếp, ẩn/hiện 10 Section tiêu chuẩn, Lịch trình, Dresscode, VietQR và Sổ ký tên RSVP

> **Dành cho:** Cô dâu & Chú rể muốn sắp xếp lại thứ tự các phần trên thiệp, ẩn bớt mục chưa dùng và hoàn thiện trọn vẹn 10 phần nội dung  
> **Thời gian thực hiện:** Khoảng 3 – 5 phút  
> **Vị trí thao tác:** Trang `/tao-thiep` → Tab **`Bố Cục`** và Tab **`Nội Dung`** (Bước 6, 7, 9, 10)  

Mỗi đám cưới có quy mô và phong cách tổ chức khác nhau: có cặp đôi muốn đưa Album ảnh cưới lên ngay đầu để khoe bộ hình kỷ niệm, có gia đình lại muốn ẩn phần hộp mừng cưới hoặc bảng màu trang phục để giữ sự tự nhiên cho quan khách. Nhờ kiến trúc **10 Section Tiêu Chuẩn** xây dựng trên nền **Flow Layout** tự co giãn thông minh, bạn có thể bật/tắt hoặc đổi vị trí từng phần chỉ trong vài giây mà bố cục thiệp luôn liền mạch, cân đối.

---

## 1. Kéo thả sắp xếp thứ tự & Ẩn/Hiện từng phần trong Tab Bố Cục

Trên thanh công cụ dọc bên trái, nhấp vào mục **`Bố Cục`** (biểu tượng danh sách 3 dòng ngang):

![Giao diện Sắp Xếp Bố Cục Thiệp cho phép kéo thả đổi vị trí và ẩn hiện 10 phần của thiệp cưới](/assets/client/docs/images/client/02-studio-editor/cli-edit-005-step-1.png)
*Tab Bố Cục: Giữ biểu tượng 6 chấm để kéo thả sắp xếp thứ tự, dùng mũi tên lên/xuống hoặc bấm con mắt để ẩn/hiện từng phần.*

### Cách ẩn bớt phần không sử dụng (1 chạm vào biểu tượng Con mắt)
Bên phải mỗi thẻ phần nội dung đều có biểu tượng **Con mắt (`👁`)**:
- **Muốn ẩn phần Mừng cưới ngân hàng:** Bấm vào biểu tượng con mắt ở mục **Hộp Quà Mừng Cưới & Mã VietQR** -> khối mừng cưới sẽ ẩn gọn khỏi thiệp ngay lập tức.
- **Tiệc cưới thoải mái không quy định màu trang phục:** Bấm con mắt ở mục **Quy Định Trang Phục** để ẩn phần Dresscode.
- **Không chia nhỏ kịch bản theo giờ:** Bấm con mắt ở mục **Lịch Trình Tiệc Cưới** để thiệp ngắn gọn, tập trung vào địa chỉ nhà hàng.
- Bất cứ lúc nào muốn bật lại, bạn chỉ cần bấm lại vào biểu tượng con mắt đó một lần nữa — toàn bộ dữ liệu bên trong vẫn được giữ nguyên.

### Cách đổi vị trí các phần trên thiệp
- **Cách 1 (Kéo thả chuột):** Nhấn giữ chuột vào biểu tượng 6 dấu chấm **`⠿`** bên trái tên mục, kéo lên hoặc xuống vị trí mong muốn rồi thả chuột.
- **Cách 2 (Dùng nút bấm chính xác trên điện thoại & máy tính):** Bấm nút mũi tên **Lên (`↑`)** hoặc **Xuống (`↓`)** nằm ngay cạnh biểu tượng con mắt để dịch chuyển từng bậc.

> [!NOTE]
> **Tự động co giãn mượt mà (100% Flow Layout):**  
> Toàn bộ mẫu thiệp của Mây Hẹn (như *Vòm Trời May Áo Cưới*) được dàn trang theo dòng chảy tự nhiên Flexbox & CSS Grid. Khi bạn ẩn đi một phần hoặc viết nội dung dài hơn, các phần kế tiếp sẽ tự động khép sát êm ái, tuyệt đối không để lại khoảng trống trắng hay đè chữ lên nhau.

---

## 2. Bước 6 & Bước 7: Thiết lập Lịch trình hôn lễ & Bảng màu Trang phục (Dresscode)

Quay lại mục **Nội Dung** trên thanh dọc bên trái để hoàn thiện chi tiết kịch bản tiệc và gợi ý trang phục cho khách mời:

![Thiết lập Quy định trang phục Dresscode và xem trước các dải màu vải dệt trên thiệp](/assets/client/docs/images/client/02-studio-editor/cli-edit-005-step-2.png)
*Bước 7 (Trang Phục): Nhập danh sách mã màu HEX gợi ý và lời nhắn trang phục đồng bộ trực tiếp lên thiệp.*

1. **Tại thẻ `6. Lịch Trình` (*Lịch Trình Tiệc Cưới*):**
   - Chỉnh sửa tối đa 6 mốc thời gian chính trong ngày (ví dụ: `08:00 Lễ vu quy`, `10:00 Lễ thành hôn`, `16:30 Đón khách`, `17:00 Khai tiệc`, `18:00 Cắt bánh · Rót rượu`, `19:00 Chụp ảnh lưu niệm`).
2. **Tại thẻ `7. Trang Phục` (*Quy Định Trang Phục*):**
   - Nhập chuỗi mã màu HEX cách nhau bởi dấu phẩy tại ô **Bảng màu trang phục gợi ý** (ví dụ tông xanh lá xô thơm & kem ngà: `#F6F8F2,#CFDDCB,#9DB89F,#618768`).
   - Viết lời nhắn nhẹ nhàng trong ô **Ghi chú trang phục** để khách mời hào hứng phối đồ chụp ảnh kỷ niệm cùng cô dâu chú rể.

---

## 3. Bước 9: Tạo mã VietQR Mừng cưới tự động cho Nhà Trai & Nhà Gái

Chuyển sang thẻ **`9. Mừng Cưới`** (*Hộp Quà Mừng Cưới & Mã VietQR*):

![Tạo mã VietQR tự động từ ngân hàng và số tài khoản hoặc tải ảnh QR có sẵn tại Bước 9](/assets/client/docs/images/client/02-studio-editor/cli-edit-005-step-3.png)
*Bước 9 (Mừng Cưới): Chọn ngân hàng thụ hưởng, nhập số tài khoản và tên chủ tài khoản để tạo mã VietQR chuẩn xác.*

Bạn có **2 cách linh hoạt** để cài đặt mã QR mừng cưới cho cả Chú rể và Cô dâu:
- **Cách 1 — Tự động sinh mã VietQR chuẩn ngân hàng:**
  1. Chọn ngân hàng trong danh sách thả xuống (`-- Chọn ngân hàng thụ hưởng --`, hỗ trợ đầy đủ Vietcombank, Techcombank, MB Bank, BIDV, VietinBank, ACB, TPBank...).
  2. Điền chính xác **Số tài khoản ngân hàng** và **Tên chủ tài khoản (viết hoa không dấu)**.
  3. Nhấp nút **`⚡ Cập nhật mã VietQR`** — hệ thống tự động tạo hình mã QR sắc nét hiển thị ngay trong hộp quà mừng cưới trên thiệp.
- **Cách 2 — Tải ảnh QR cá nhân có sẵn từ điện thoại:**
  - Nếu bạn đã lưu sẵn ảnh mã QR từ ứng dụng ngân hàng của mình, chỉ cần bấm nút **`📷 Tải ảnh QR có sẵn`** và chọn ảnh đó lên là xong!
- Ngoài ra, ngay đầu Bước 9 còn có nút **`✨ Viết Lời Tri Ân`** để Trợ lý AI gợi ý lời cảm ơn tinh tế gửi đến quan khách gửi quà mừng từ xa.

---

## 4. Bước 10: Lời Cảm Ơn, Xác nhận tham dự (RSVP) & Sổ Ký Tên Điện Tử

Chuyển sang thẻ cuối cùng **`10. Cảm Ơn`** (*Tương Tác, RSVP & Lời Cảm Ơn*):

![Tùy chỉnh lời tri ân kết thiệp, ảnh khung vòm cuối thiệp cùng khu vực Sổ ký tên và RSVP](/assets/client/docs/images/client/02-studio-editor/cli-edit-005-step-4.png)
*Bước 10 (Cảm Ơn): Hoàn thiện lời chào tri ân, ảnh kết thiệp cùng biểu mẫu xác nhận tham dự RSVP và Sổ ký tên lưu niệm.*

1. Điền **Tiêu đề lời tri ân kết thiệp** (ví dụ: *Thank you!* hoặc *Trân trọng cảm ơn!*) và **Thông điệp cảm ơn gửi quan khách**.
2. Chọn **Ảnh khung vòm kết thiệp** để khép lại hành trình xem thiệp thật ngọt ngào.
3. Ngay phía trên lời kết, tấm thiệp đã tích hợp sẵn **Sổ ký tên cảm ứng** (để bạn bè ký lưu niệm và gửi lời chúc) cùng biểu mẫu **Xác nhận tham dự (RSVP)** để khách báo trước số người đi cùng, giúp gia đình chủ động chuẩn bị bàn tiệc chu đáo nhất.

---

## 5. Xem tiếp bài viết liên quan

- [Cài đặt nhạc nền lãng mạn từ kho 20+ bài hát bản quyền](/docs/studio-editor/CLI-EDIT-006)
- [Sử dụng Bác Sĩ Thiệp Cưới kiểm tra 100 điểm trước khi xuất bản](/docs/studio-editor/CLI-EDIT-009)
- [Xuất bản thiệp cưới chính thức và chia sẻ link cho khách mời](/docs/studio-editor/CLI-EDIT-010)
