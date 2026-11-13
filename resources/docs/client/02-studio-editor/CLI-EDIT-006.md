---
id: CLI-EDIT-006
title: "Thêm nhạc nền lãng mạn và cấu hình tự động phát nhạc chuẩn quy định trình duyệt (Autoplay Policy)"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Cẩm nang cài đặt âm nhạc cho thiệp cưới: Tuyển tập tình ca lãng mạn bản địa, tải lên file nhạc riêng, giải pháp kích hoạt nhạc tự động chuẩn chính sách iOS/Android khi mở phong thư và nút điều khiển âm thanh 1-chạm."
reader: "PER-CLI-01 / PER-CLI-02"
order: 11
updated_at: "2026-11-13"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, am-nhac, autoplay-policy, ios-safari]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Thêm nhạc nền lãng mạn và cấu hình tự động phát nhạc chuẩn quy định trình duyệt (Autoplay Policy)

> **Dành cho:** Cô dâu & Chú rể muốn thiệp cưới ngập tràn thanh âm du dương, lãng mạn  
> **Thời gian thực hiện:** Khoảng 2 - 3 phút  
> **Vị trí trong ứng dụng:** Thanh công cụ dọc Left Rail -> Tab **Âm Nhạc (Music)**  

Âm nhạc là linh hồn đánh thức cảm xúc. Khi khách mời mở thiệp cưới, một giai điệu tình ca quen thuộc cất lên êm ái sẽ lập tức đưa người xem vào không gian hạnh phúc ngập tràn của hai bạn. Tuy nhiên, các trình duyệt di động hiện đại (đặc biệt là **Apple iOS Safari** và **Google Chrome**) áp dụng chính sách bảo mật rất nghiêm ngặt đối với âm thanh tự động. Mây Hẹn cung cấp giải pháp âm nhạc chuẩn hóa, vừa bảo đảm tuân thủ chính sách trình duyệt, vừa mang lại trải nghiệm mở thiệp thăng hoa nhất.

---

## 1. Kho nhạc tình ca tinh tuyển 100% Local Assets

Mây Hẹn chuẩn bị sẵn tuyển tập những bản tình ca đám cưới bất hủ được phối khí trang nhã, lưu trữ trực tiếp trên máy chủ nội bộ (100% Local MP3) giúp nhạc tải tức thời và không bị nghẽn:
- **Nhạc hòa tấu vĩ cầm & dương cầm (Violin & Piano Instrumental):**
  - *Canon in D Major (Pachelbel)* — Cổ điển, thiêng liêng, trang trọng hoàng gia.
  - *Until I Found You (Stephen Sanchez - Piano Cover)* — Lãng mạn, hoài niệm, sâu lắng.
  - *Beautiful in White (Shane Filan - Acoustic Version)* — Bản thánh ca ngày bước vào lễ đường.
  - *A Thousand Years (Christina Perri - Cello & Piano)* — Lời hẹn ước tình yêu ngàn năm.
- **Tải lên bản nhạc riêng của hai bạn (Custom MP3 Upload):**
  - Hỗ trợ tải tệp định dạng `.mp3`, dung lượng tối đa 10MB.
  - Khuyến nghị chất lượng âm thanh 128kbps đến 192kbps để tối ưu tốc độ tải trên mạng di động 4G.

---

## 2. Giải pháp kỹ thuật Autoplay Policy trên iOS và Android

### Vì sao điện thoại không cho phát nhạc ngay khi vừa bấm vào link?
Từ năm 2018, cả Apple và Google đều thiết lập quy định **User Gesture Interaction**: Trình duyệt di động sẽ tự động chặn toàn bộ âm thanh phát ra từ trang web nếu người dùng chưa thực hiện bất kỳ thao tác chạm (tap/click) nào trên màn hình. Mục đích là để bảo vệ người dùng không bị giật mình khi lướt web nơi công cộng.

### Cơ chế kích hoạt nhạc thông minh của Mây Hẹn (Wax Seal Handshake)
Mây Hẹn không cố tìm cách vượt rào hay gây phiền toái, mà biến rào cản kỹ thuật này thành một **nghi thức mở thiệp đầy cảm xúc**:
1. Khi khách mời bấm mở link thiệp, màn hình xuất hiện chiếc phong thư đóng dấu sáp niêm phong (Wax Seal) trang trọng.
2. Dòng chữ hướng dẫn nhẹ nhàng: *"Chạm để mở thiệp cưới"*.
3. Khi khách mời chạm ngón tay vào con dấu sáp để mở phong thư, **thao tác chạm này được trình duyệt công nhận là một User Gesture hợp lệ**.
4. Hệ thống Web Audio API của Mây Hẹn lập tức kích hoạt giai điệu âm nhạc cất lên êm ái với **hiệu ứng Fade-in (tăng âm lượng từ từ trong 1.5 giây)**, mang lại cảm giác vô cùng tinh tế và tự nhiên!

---

## 3. Các bước cài đặt âm nhạc trong Studio Tạo Thiệp

### Bước 1: Chọn bài hát trong Studio
1. Trong màn hình Studio, nhấp vào biểu tượng nốt nhạc **[Âm Nhạc]** trên thanh công cụ dọc bên trái.
2. Danh sách các bài hát tinh tuyển sẽ hiện ra kèm nút **Nghe thử (▶)** bên cạnh.
3. Bấm nghe thử các bài hát và chọn bản nhạc hai bạn yêu thích nhất.

### Bước 2: Bật/tắt chế độ tự động phát (Autoplay)
- **Tự động phát nhạc khi mở phong thư (Khuyên dùng):** Bật công tắc này để nhạc tự cất lên ngay khi khách chạm mở nắp thiệp.
- **Lặp lại bài hát (Loop Audio):** Nhạc sẽ tự động lặp lại êm ái khi khách cuộn xem hết toàn bộ 10 phần của thiệp cưới.

### Bước 3: Nút điều khiển âm thanh nổi (Floating Audio Control)
Trên thiệp cưới luôn có một nút điều khiển hình đĩa than hoặc nốt nhạc nhỏ nhắn ở góc phải màn hình:
- Khi nhạc đang phát: Biểu tượng nốt nhạc xoay nhẹ nhàng theo nhịp điệu.
- Nếu khách đang ở nơi cần yên tĩnh (phòng họp, nơi công cộng): Khách chỉ cần chạm nhẹ vào biểu tượng này là âm thanh sẽ tắt ngay lập tức. Chạm lại một lần nữa để tiếp tục nghe nhạc.

---

## 4. Kiểm tra và tối ưu trải nghiệm âm nhạc

> [!TIP]
> **Nên nghe thử trên điện thoại trước khi gửi:**  
> Sau khi cài đặt bài hát, hãy mở chế độ xem thử trên điện thoại (nhấp nút **[Xem Thiệp]** trên điện thoại) và chạm thử mở phong thư để cảm nhận chính xác độ mượt mà của âm thanh như khách mời thật.

> [!NOTE]
> **Nhạc nền có làm chậm tốc độ tải thiệp không?**  
> Hoàn toàn không. Tệp âm thanh trên Mây Hẹn được nạp theo cơ chế **Lazy Audio Streaming** — chỉ bắt đầu tải luồng âm thanh sau khi toàn bộ hình ảnh và chữ viết của thiệp đã hiển thị hoàn chỉnh, bảo đảm tốc độ hiển thị thiệp luôn đạt chuẩn Core Web Vitals dưới 1 giây.

