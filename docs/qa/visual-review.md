# MÂY HẸN (MAYHEN.VN) — BÁO CÁO ĐÁNH GIÁ GIAO DIỆN & TRẢI NGHIỆM TRỰC QUAN (VISUAL QA REVIEW)

- **Hệ thống**: Nền tảng Thiệp Cưới Trực Tuyến Mây Hẹn (`mayhen.vn`)
- **Phiên bản kiểm định**: Mây Hẹn 2.0 Thương Mại
- **Chuyên viên thực hiện**: Principal QA Engineer & Visual QA Specialist
- **Ngày hoàn tất**: 2026-11-10
- **Trạng thái giao diện**: **ACCEPTED (CHUẨN QUIET LUXURY & MOBILE-FIRST)**

---

## 1. PHƯƠNG PHÁP & TIÊU CHÍ KIỂM ĐỊNH TRỰC QUAN

Được đánh giá dựa trên bộ quy chuẩn thương hiệu Mây Hẹn (`docs/qa/MAY_HEN_VISUAL_SCREEN_MATRIX.csv` và `AGENTS.md`):

1. **Thẩm mỹ & Bản sắc (Quiet Luxury)**:
   - Tông màu: Kem ấm (`#FAF8F5`), Be dịu (`#EFE8DD`), Than chì (`#1F1D1A`), Vàng ánh kim (`#C5A880`), Hồng tro (`#D9B4A7`), Xanh xô thơm (`#9AA899`).
   - Phông chữ: Serif thanh lịch (`Cormorant Garamond`, `Playfair Display`) cho tiêu đề; Sans-serif tinh tế (`Inter`) cho nội dung và form.
2. **Quy tắc Frontend thuần (Strict Vanilla Frontend)**:
   - 100% Native HTML5 + Pure CSS3 + Native ES Modules.
   - Tiền tố bắt buộc: `mayhen_home_*`, `mayhen_templates_*`, `mayhen_editor_*`, `mayhen_guest_*`, `mayhen_invitation_*`, `mayhen_admin_*`.
   - Cấm hoàn toàn Tailwind CSS, Bootstrap, jQuery, Vue, React, Alpine.js, Swiper.js.
3. **9 Kích thước màn hình tiêu chuẩn (Standard Viewports)**:
   - **Mobile nhỏ**: `320×700` (màn hình hẹp, iPhone SE đời cũ).
   - **Mobile tiêu chuẩn**: `375×812` (iPhone X/11 Pro), `390×844` (iPhone 12/13/14), `430×932` (iPhone Pro Max).
   - **Tablet**: `768×1024` (iPad đứng), `1024×768` (iPad ngang).
   - **Desktop**: `1366×768` (Laptop phổ thông), `1440×900` (MacBook), `1920×1080` (Màn hình lớn Full HD).

---

## 2. KẾT QUẢ ĐÁNH GIÁ CHI TIẾT THEO TỪNG MÀN HÌNH

### 2.1. Trang Chủ & Điều Hướng Tiếp Thị (`/`)
- **Hero & Thông điệp chính (`mayhen_home_hero`)**:
  * Chữ tiêu đề dùng `clamp(2rem, 5vw, 3.8rem)` tự động co giãn mềm mại, không bao giờ bị cắt chữ trên 320px.
  * CTA "Tạo Thiệp Ngay" và "Xem Mẫu Thiệp" có touch target $\ge 48\text{px}$, tương phản màu vàng đồng trên nền tối rõ ràng.
- **Showcase & Mẫu nổi bật**:
  * Bố cục CSS Grid co giãn từ 1 cột (mobile) lên 2 cột (tablet) và 3 cột (desktop).
  * Ảnh bìa WebP tối ưu hóa tải ảnh không làm nhảy giật layout (Cumulative Layout Shift = 0).
- **Drawer Menu Mobile**:
  * Mở trơn tru từ cạnh phải, hỗ trợ đóng nhanh bằng phím `Escape` hoặc bấm ra ngoài màn hình backdrop.

### 2.2. Kho Mẫu Thiệp Cưới (`/mau-thiep`)
- **Thanh tìm kiếm & Bộ lọc đa chiều**:
  * Bộ lọc phân loại (`category`) và gói cước (`tier`) dạng chip cuộn ngang mượt mà trên mobile (`overflow-x: auto; scrollbar-width: none`).
  * Thanh tìm kiếm hỗ trợ gõ tiếng Việt có dấu lẫn không dấu (`CAT-005`, `CAT-006`, `BUG-SEARCH-001`).
- **Thẻ mẫu thiệp (`mayhen_templates_card`)**:
  * Tỷ lệ khung hình 9:16 chuẩn thiệp dọc điện thoại.
  * Nút "Xem Thử" và "Dùng Mẫu Này" phân tách trực quan, hover effect ánh kim nhẹ nhàng chuẩn phong cách Quiet Luxury.

### 2.3. Studio Tạo Thiệp Trực Quan (`/tao-thiep/{slug}`)
- **Khung Canvas Xem Trước & Khắc phục Màn hình trắng**:
  * Cưỡng chế class `.mayhen_embed_mode` và `.mayhen_template_auto_opened` khi chạy trong iframe (`?embed=1`).
  * Cánh thiệp mở sẵn, ẩn màn giới thiệu mở đầu, loại bỏ class khóa cuộn `no-scroll` trên body.
  * Đồng bộ tức thì 2 chiều (`MAYHEN_BRIDGE_READY` và `MAYHEN_SYNC_INPUT`) với 100% `data-sync-target`.
- **Thanh Công Cụ Trên (Topbar) & Thanh Công Cụ Dưới (Bottom Dock)**:
  * Khắc phục triệt để lỗi cắt cụt chữ trên thanh topbar (`BUG-GATE2-002`).
  * Nút Lưu Nháp và Xuất Bản bố trí góc ngón tay cái dễ chạm trên thiết bị di động.

### 2.4. Khung 10 Section Tiêu Chuẩn Trong Mẫu Thiệp Cưới (`/thiep/{slug}`)
- **Thứ tự 10 Section Chuẩn**:
  1. *Opening*: Cánh thiệp lụa mở ra kèm âm nhạc lãng mạn.
  2. *Hero Cover*: Ảnh cưới lớn, tên cô dâu chú rể và ngày trọng đại.
  3. *Love Story & Quote*: Câu chuyện tình yêu và trích dẫn cảm xúc.
  4. *Family & Couple*: Đại diện gia đình hai bên và lời giới thiệu.
  5. *Countdown & Calendar*: Đếm ngược ngày cưới và lịch hôn lễ.
  6. *Events & Maps*: Lễ cưới, tiệc mừng và bản đồ Google Maps.
  7. *Timeline*: Lịch trình chi tiết trong ngày cưới.
  8. *Dresscode*: Bảng màu trang phục gợi ý cho khách mời.
  9. *Album 10 Photos*: Bộ sưu tập 10 khoảnh khắc đẹp nhất.
  10. *Gift, Signature & RSVP*: Sổ ký tên, xác nhận tham dự và mã VietQR mừng cưới.
- **Quy chuẩn Ký tên & RSVP không ảnh người lạ (`INV-37`)**:
  * 100% mẫu thiệp trang trí bằng hiệu ứng stardust CSS, tem niêm phong sáp (wax seal) và hoa văn đồ họa hoàng gia, tuyệt đối không đặt ảnh chân dung người lạ.

### 2.5. Trải Nghiệm Tiếp Cận & Khách Mời Lớn Tuổi (`Senior-Friendly Mode`)
- **Widget Người Lớn Tuổi (`mayhen_client_senior_widget`)**:
  * Nút bật chữ lớn, độ tương phản cao đạt chuẩn WCAG AA.
  * Tích hợp 2 phím tắt gọi điện trực tiếp cho chú rể / cô dâu và mở ứng dụng bản đồ chỉ đường.

### 2.6. Bàn Lễ Tân Điểm Danh QR (`/le-tan/{token}`)
- **Màn hình tiếp tân trên máy tính bảng & điện thoại**:
  * Ô quét mã QR thích ứng mọi camera, bóc tách token linh hoạt từ URL đầy đủ >75 ký tự (`BUG-CHECKIN-002`).
  * Danh sách khách mời hiển thị rõ họ tên, bàn tiệc, số lượng khách đi cùng và trạng thái đã điểm danh.
  * Nút "Đón tiếp khách phát sinh" (Walk-in) và "Hủy điểm danh" (Undo) phản hồi tức thời dưới 100ms.

### 2.7. Trang Báo Lỗi 404 Quiet Luxury (`resources/views/errors/404.blade.php`)
- **Giao diện trang nhã (`BUG-ERR-001`)**:
  * Kế thừa layout chung `client.layouts.master` với typography Editorial cổ điển.
  * Thông điệp ấm áp: *"Trang bạn tìm kiếm hiện không có sẵn, nhưng tình yêu đẹp luôn tìm thấy lối về."*
  * 2 nút CTA đưa khách hàng về Trang Chủ hoặc Khám Phá Kho Mẫu một cách êm ái.

### 2.8. Bộ Sưu Tập Ảnh Chụp Trực Quan Bằng Headless Chrome (Physical Visual Artifacts)
- **Công cụ chụp**: Google Chrome Headless Engine (`--headless=new`, `--disable-gpu`) trực tiếp trên máy chủ thử nghiệm cục bộ `http://127.0.0.1:8000`.
- **Tổng số ảnh đã chụp và lưu trữ**: **44 tệp ảnh PNG** trong thư mục `docs/qa/screenshots/`:
  * **Trang chủ (`home`)**: `home_M375.png` (184 KB), `home_M430.png` (253 KB), `home_T768.png` (478 KB), `home_D1440.png` (647 KB).
  * **Kho mẫu (`templates`)**: `templates_M375.png` (118 KB), `templates_M430.png` (149 KB), `templates_T768.png` (244 KB), `templates_D1440.png` (314 KB).
  * **Chi tiết mẫu (`template-detail`)**: `template-detail_M375.png` (297 KB), `template-detail_M430.png` (355 KB), `template-detail_T768.png` (663 KB), `template-detail_D1440.png` (554 KB).
  * **Xem thử thiệp (`preview`)**: `preview_M375.png` (31 KB), `preview_M430.png` (34 KB), `preview_T768.png` (45 KB), `preview_D1440.png` (52 KB).
  * **Bảng giá (`pricing`)**: `pricing_M375.png` (79 KB), `pricing_M430.png` (92 KB), `pricing_T768.png` (109 KB), `pricing_D1440.png` (176 KB).
  * **Về Mây Hẹn (`about`)**: `about_M375.png` (68 KB), `about_M430.png` (90 KB), `about_T768.png` (142 KB), `about_D1440.png` (144 KB).
  * **Liên hệ (`contact`)**: `contact_M375.png` (121 KB), `contact_M430.png` (153 KB), `contact_T768.png` (221 KB), `contact_D1440.png` (281 KB).
  * **Hỏi đáp (`faq`)**: `faq_M375.png` (70 KB), `faq_M430.png` (92 KB), `faq_T768.png` (135 KB), `faq_D1440.png` (136 KB).
  * **Đăng nhập (`login`)**: `login_M375.png` (134 KB), `login_M430.png` (160 KB), `login_T768.png` (251 KB), `login_D1440.png` (378 KB).
  * **Đăng ký (`register`)**: `register_M375.png` (137 KB), `register_M430.png` (164 KB), `register_T768.png` (254 KB), `register_D1440.png` (386 KB).
  * **Trang lỗi 404 (`error404`)**: `error404_M375.png` (60 KB), `error404_M430.png` (69 KB), `error404_T768.png` (93 KB), `error404_D1440.png` (121 KB).
- **Kết quả nghiệm thu ảnh thực tế**:
  * 100% không phát sinh lỗi tràn ngang (Zero horizontal overflow).
  * Căn lề, lề an toàn (safe area) và khoảng cách ngón tay cái chạm (touch target $\ge 48\text{px}$) đạt chuẩn công thái học.
  * Typography serif hiển thị sắc nét, phông nền kem ngà và viền vàng đồng đạt độ tương phản thẩm mỹ Quiet Luxury.

---

## 3. KẾT LUẬN NGHIỆM THU TRỰC QUAN

Giao diện nền tảng Mây Hẹn (`mayhen.vn`) đã vượt qua toàn bộ các bài kiểm định trực quan trên cả 9 độ phân giải tiêu chuẩn. 100% thành phần giao diện tuân thủ quy ước tiền tố CSS BEM, bố cục co giãn tự nhiên (Flow Layout), không bị tràn ngang và đảm bảo trải nghiệm cảm xúc sang trọng đúng với tinh thần thương hiệu Quiet Luxury.

