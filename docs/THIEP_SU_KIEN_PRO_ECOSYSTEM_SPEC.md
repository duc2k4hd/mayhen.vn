# ĐẶC TẢ KIẾN TRÚC & SẢN PHẨM: HỆ SINH THÁI THIỆP SỰ KIỆN, LỄ HỘI & THIỆP CHÚC MỪNG (PAPERLESS POST VIETNAMESE EDITION — PRO PLAN)

> **Dự án**: Mây Hẹn (`mayhen.vn`)
> **Phân hệ**: Thiệp Sự Kiện Gia Đình, Mùa Lễ Hội & Thiệp Chúc Mừng (`/thiep-su-kien` & `/loi-moi/{slug}`)
> **Định vị chiến lược**:
> - **Tuyệt đối không chứa Thiệp Cưới** (Thiệp cưới đã có kho riêng tại `/mau-thiep` và `/tao-thiep`).
> - Lấy cảm hứng từ trải nghiệm **Stationery Card + 3D Envelope + Patterned Envelope Liner + Backdrop** của `paperlesspost.com`, nhưng được **Việt hóa 100%** theo các ngày lễ, phong tục và văn hóa giao tiếp tại Việt Nam.
> - **Mở 100% SEO Google & Cho khách tạo thử / lưu nháp miễn phí** — **Xuất bản đường dẫn `/loi-moi/{slug}` là đặc quyền trọn đời của Gói Pro VIP (199.000đ)**.

---

## 1. NGHIÊN CỨU MÔ HÌNH PAPERLESS POST & CHUYỂN ĐỔI CHUẨN VĂN HÓA VIỆT NAM

### 1.1. Tinh hoa thị giác của Paperless Post được đưa vào Mây Hẹn
Trên Paperless Post, mỗi mẫu thiệp không chỉ là một trang web phẳng mà là một **bộ ấn phẩm văn phòng phẩm (Digital Stationery Suite)** gồm 5 lớp:
1. **Tờ Thiệp Chính (Stationery Card)**:
   - Có **Khung viền mỹ thuật (Card Border Motif)**: Viền sọc kẹo lễ hội (*Candy Stripe*), Vòng lá thông & nơ đỏ (*Watercolor Garland*), Viền lượn sóng (*Scalloped Wave*), Viền chỉ vàng kép (*Double Gold Foil*), Viền gấm đỏ vân mây Tết (*Lunar Brocade*), Khung vòm đèn lồng Trung Thu (*Lantern Arch*), Viền pháo giấy sinh nhật (*Pastel Confetti*), Khung tạp chí hiện đại (*Editorial Frame*).
   - Có **Tranh minh họa trung tâm (Artwork Motif)** hoặc **Khung tải ảnh riêng của khách (Custom Photo / Upload Your Own Design)**.
2. **Vỏ Phong Bì 3D (Outer Envelope)**:
   - Nằm nghiêng mở nắp phía sau tờ thiệp trên trang danh mục, và có hiệu ứng **Lật mở nắp phong bì 3D + Rút tờ thiệp trượt lên** khi khách mời mở xem.
   - Tùy chọn 8 màu vỏ phong bì: *Đỏ Rượu Vang*, *Xanh Rừng Thông*, *Kem Lụa Cổ Điển*, *Hồng Phấn Pastel*, *Tím Lavender*, *Nâu Espresso*, *Xanh Navy Hoàng Gia*, *Vàng Hoàng Kim*.
3. **Lớp Lót Trong Phong Bì (Envelope Liner Pattern)**:
   - Điểm nhấn đắt giá nhất của Paperless Post: mặt trong nắp phong bì được lót giấy hoa văn (*Nhũ Vàng Kim*, *Kẻ Sọc Đỏ Kem*, *Caro Tartan Lễ Hội*, *Lá Thông & Nơ Đỏ*, *Chấm Bi Đỏ*, *Vân Mây Hoa Mai Tết*, *Bầu Trời Sao Đêm Trăng*, *Sọc Hồng Phấn*).
4. **Phông Nền Sân Khấu (Backdrop)** & **Tem Dấu Sáp (Stamp & Wax Seal)**.
5. **Khối Chức Năng Linh Hoạt Theo Loại Thiệp (2 Chế Độ: Thiệp Mời Tiệc vs Thiệp Chúc Mừng)**:
   - **Chế độ Thiệp Mời Tiệc (Event Invitation)**: Hiển thị đầy đủ Ngày giờ, Đồng hồ đếm ngược, Nút lưu lịch Google Calendar, Bản đồ chỉ đường Google Maps 1 chạm, Kịch bản 3 mốc giờ, Dresscode, Biểu mẫu RSVP kèm câu hỏi tùy chỉnh (ăn chay/mặn, phương tiện đỗ xe) và Hộp mừng quà / Lì xì VietQR.
   - **Chế độ Thiệp Chúc Mừng / Thiệp Tết Từ Xa (Greeting Card)**: Cho phép gạt tắt khối Địa điểm/Lịch trình tiệc để biến thành tấm thiệp chúc Tết, chúc Trung Thu, chúc 8/3, 20/10, 20/11 kèm Phong bao Lì xì VietQR tinh tế.

---

## 2. HỆ THỐNG 10 DANH MỤC & 30 MẪU THIỆP VIỆT HÓA (KHÔNG LẪN THIỆP CƯỚI)

| STT | Mã Danh Mục (`category_slug`) | Tên Danh Mục Chuẩn Văn Hóa Việt | Nguồn Cảm Hứng Paperless Post | Số Mẫu |
| :--- | :--- | :--- | :--- | :---: |
| 1 | `tet-nguyen-dan` | **Thiệp Chúc Tết & Tiệc Tân Niên Khai Xuân** | *Lunar New Year & Holiday Greeting* | 3 mẫu |
| 2 | `tet-trung-thu` | **Thiệp Tết Trung Thu & Đêm Hội Trăng Rằm** | *Moon Festival & Seasonal Gathering* | 3 mẫu |
| 3 | `giang-sinh-nam-moi` | **Thiệp Tiệc Giáng Sinh & Đón Năm Mới** | *Christmas Party, Holiday Party, NYE* | 3 mẫu |
| 4 | `day-thang-thoi-noi` | **Thiệp Đầy Tháng & Thôi Nôi Em Bé** | *Baby Shower, Sip & See, 1st Birthday* | 3 mẫu |
| 5 | `sinh-nhat-tre-em` | **Thiệp Sinh Nhật Trẻ Em (Bé Trai & Bé Gái)** | *Kids' Birthday Invitations* | 3 mẫu |
| 6 | `sinh-nhat-nguoi-lon` | **Thiệp Sinh Nhật Người Lớn & Lễ Mừng Thọ** | *Adult Birthday, Cocktail & Milestone* | 3 mẫu |
| 7 | `tan-gia-nha-moi` | **Thiệp Mời Tân Gia & Mừng Nhà Mới** | *Housewarming Party Invitations* | 3 mẫu |
| 8 | `tat-nien-khai-truong` | **Thiệp Tất Niên Công Ty (YEP) & Khai Trương** | *For Professionals / Corporate Events* | 3 mẫu |
| 9 | `tot-nghiep-thanh-tuu` | **Thiệp Mời Lễ Tốt Nghiệp & Vinh Danh** | *Graduation & Retirement Party* | 3 mẫu |
| 10 | `ngay-le-chuc-mung` | **Thiệp Chúc Mừng 8/3, 20/10, 20/11 & Kỷ Niệm** | *Greeting Cards, Mother's Day, Anniversary* | 3 mẫu |

---

## 3. KIẾN TRÚC STUDIO TẠO & SỬA THIỆP NÂNG CAO (5 TAB CHUYÊN SÂU)

Studio `/thiep-su-kien/tao-thu/{templateSlug}` cung cấp trải nghiệm tùy biến không thua kém Studio Thiệp Cưới:
1. **Tab 1 — Mặt Thiệp & Ảnh (`Thiệp & Minh Họa`)**:
   - Đổi **Khung viền mặt thiệp** (8 kiểu viền nghệ thuật).
   - Đổi **Phông chữ mặt thiệp** (*Cổ Điển Sang Trọng*, *Hiện Đại Tinh Gọn*, *Thư Pháp Mềm Mại*).
   - **Tải ảnh riêng lên mặt thiệp** (Ảnh bé, ảnh gia đình, ảnh công ty hoặc thiết kế riêng) kèm nút xóa ảnh để quay về tranh minh họa gốc.
2. **Tab 2 — Phong Bì, Lớp Lót & Phông Nền (`Phong Bì & Nền`)**:
   - Chọn **Màu Vỏ Phong Bì** (8 màu).
   - Chọn **Hoa Văn Lớp Lót Phong Bì (Envelope Liner)** (8 họa tiết).
   - Chọn **Phông Nền Sân Khấu (Backdrop)** (6 phông nền).
   - Nút **"▶ Xem Hiệu Ứng Mở Phong Bì 3D"** ngay trên sân khấu xem trước.
3. **Tab 3 — Nội Dung & Kho Lời Mời Mẫu (`Nội Dung & Lời Văn`)**:
   - Nhập tiêu đề, chủ nhân bữa tiệc, lời mời/lời chúc.
   - **Kho Gợi Ý Lời Văn 1 Chạm**: Mỗi danh mục có sẵn 3 mẫu lời văn chuẩn văn hóa Việt (Trang trọng / Thân mật / Ngắn gọn) để chèn nhanh.
4. **Tab 4 — Thời Gian, Bản Đồ & Lịch Trình (`Sự Kiện & Bản Đồ`)**:
   - Công tắc bật/tắt khối Thời gian & Địa điểm (hỗ trợ chuyển đổi linh hoạt giữa **Thiệp Mời Tiệc** và **Thiệp Chúc Mừng Từ Xa**).
   - Ngày giờ, ghi chú Âm lịch, địa chỉ, link Google Maps, Dresscode và 3 mốc kịch bản chương trình.
5. **Tab 5 — RSVP, Câu Hỏi Khách & Lì Xì VietQR (`RSVP & Lì Xì QR`)**:
   - Bật/tắt biểu mẫu RSVP + **Câu hỏi riêng cho khách mời** (ví dụ: *"Bạn dùng cỗ chay hay cỗ mặn?"*, *"Bạn đi ô tô hay xe máy?"*).
   - Bật/tắt **Phong Bao Lì Xì / Hộp Mừng Quà VietQR** (tự động sinh mã QR chuyển khoản VietQR chuẩn).
