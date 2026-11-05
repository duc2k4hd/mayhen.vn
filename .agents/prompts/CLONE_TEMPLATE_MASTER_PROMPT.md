# 🌟 MASTER PROMPT CHUẨN ĐỂ CLONE & TẠO MẪU THIỆP CƯỚI MÂY HẸN (MAYHEN.VN)

> **Mục đích sử dụng**: Sao chép toàn bộ nội dung prompt dưới đây và gửi cho AI (Gemini, Claude 3.7/Sonnet, GPT-4o, Cursor...) cùng với link mẫu thiệp tham khảo bất kỳ để AI tạo ra mẫu thiệp chuẩn 100% theo kiến trúc dự án Mây Hẹn.

---

```markdown
Bạn là Senior Frontend Architect & Motion Designer chuyên về nền tảng thiệp cưới trực tuyến cao cấp Mây Hẹn (mayhen.vn) theo phong cách Quiet Luxury, Editorial & Modern.

Hãy trích xuất, phân tích và lập trình lại toàn bộ mẫu thiệp cưới từ link sau thành mẫu thiệp cưới chuẩn kiến trúc dự án Mây Hẹn:
- Link tham khảo: https://hwd.happyweddingday.vn/red-vip
- Tên mẫu thiệp: "Hạnh Phúc Mang Tên Chúng Mình"
- Tông màu chủ đạo: Xanh lá nhạt & Xanh lá đậm (Sage Green & Deep Forest / Olive Cream), kết hợp vàng Antique Gold trên nền kem ấm Quiet Luxury.
- Đóng gói đầu ra: Toàn bộ mã nguồn, tài nguyên cục bộ đóng gói hoàn chỉnh thành file ZIP đặt tên là: "Hạnh Phúc Mang Tên Chúng Mình.zip".

---

### ⚠️ CÁC NGUYÊN TẮC KỸ THUẬT BẮT BUỘC (ZERO TOLERANCE):

1. **100% PURE NATIVE CODE (KHÔNG FRAMEWORK, KHÔNG CDN)**:
   - Chỉ dùng: HTML5 thuần + CSS3 thuần + JavaScript ES Modules thuần.
   - CẤM TUYỆT ĐỐI: Tailwind CSS, Bootstrap, jQuery, React, Vue, GSAP, Anime.js, AOS, Swiper.js, Slick Slider...
   - CẤM TUYỆT ĐỐI: Dùng link CDN ngoại (`cdnjs`, `jsdelivr`, `unpkg`).

2. **HỆ THỐNG CLASS CHUẨN TIỀN TỐ TOÀN CỤC (CẤM LẠM DỤNG ID)**:
   - 100% class mang tiền tố bắt buộc: `mayhen_invitation_*` (Ví dụ: `mayhen_invitation_hero`, `mayhen_invitation_story`, `mayhen_invitation_calendar`...).
   - Tùy biến kiểu dáng theo mẫu bằng Modifier (Ví dụ: `mayhen_invitation_hero--sage`).
   - CẤM lạm dụng ID (chủ yếu dùng class để dễ bảo trì và tái sử dụng).

3. **100% FLOW LAYOUT (RESPONSIVE LINH HOẠT TRÊN MỌI THIẾT BỊ)**:
   - CẤM layout định vị tuyệt đối `position: absolute` với `width`/`height` pixel cứng kiểu LadiPage làm vỡ màn hình di động của khách hàng.
   - Bắt buộc dùng Flexbox và CSS Grid tự co giãn theo nội dung.
   - Khung chứa chính: `max-width: 480px; width: 100%; margin: 0 auto; position: relative;`. Font chữ và khoảng cách dùng `rem`, `clamp()` hoặc phần trăm để hiển thị tuyệt đẹp từ iPhone SE nhỏ nhất đến màn hình lớn và Desktop.

4. **PHÂN BỔ SECTION HỢP LÝ & LINH HOẠT (KHÔNG CẦN RẬP KHUÔN)**:
   - Không cần rập khuôn cứng nhắc thứ tự các section, hãy phân bổ bố cục hợp lý, tự nhiên, sang trọng và giống với linh hồn thiết kế của mẫu gửi.
   - Có thể thêm các khối trang trí, monogram, lời chúc để mẫu thiệp phong phú, nhưng CHỈ ĐƯỢC DÙNG từ ngữ và bố cục đẹp, KHÔNG dùng dữ liệu hay ảnh của người khác.

5. **MÀN MỞ THIỆP CHUẨN HÓA (OPENING SCREEN & ENVELOPE)**:
   - Sao chép hoặc thiết kế màn mở đầu phong bì 3D / cánh cửa song hỷ trang trọng (`mayhen_invitation_opening`, `mayhen_invitation_open_btn`).
   - Tự động bật nhạc khi khách chạm mở thiệp.
   - Phòng vệ nhúng Studio Editor (`$isEmbed = true`): Tự động mở sẵn cánh thiệp, ẩn màn mở đầu (`display: none !important;`), không gắn class khóa cuộn `no-scroll` lên `html`/`body` để tránh màn hình trắng trơn.

6. **TỰ VIẾT ANIMATION CSS CHUẨN QUIET LUXURY**:
   - Nếu mẫu gốc thiếu hiệu ứng chuyển động, hãy tự viết keyframes CSS animation (fade-in khi cuộn, lá rơi nhẹ nhàng, ánh vàng phát quang nhẹ, hiệu ứng nhịp đập tim/bobbing) với tốc độ 60 FPS mượt mà.

7. **QUY TẮC BẤT BIẾN: BẮT BUỘC CÓ KÝ TÊN & RSVP — CẤM 100% ẢNH CẶP ĐÔI VÀO 2 PHẦN NÀY**:
   - Bắt buộc phải có đủ 2 tính năng: Sổ Ký Tên Điện Tử (`Signature Canvas`) và Xác Nhận Tham Dự (`RSVP Form`).
   - TUYỆT ĐỐI CẤM đặt ảnh chụp cặp đôi hay ảnh stock người vào 2 phần này (cả ảnh nền canvas ký tên và ảnh minh họa RSVP). Lý do: Khách hàng tạo thiệp không sửa được các ảnh này trong Studio, dẫn đến thiệp gửi cho khách xuất hiện ảnh của người lạ.
   - CHỈ ĐƯỢC PHÉP dùng hiệu ứng thuần (CSS canvas, stardust, giấy mỹ thuật), sticker SVG, tem niêm phong sáp (wax seal) hoặc hoa văn hoàng gia để trang trí làm đẹp.

8. **TỰ CHỨA 100% TÀI NGUYÊN NỘI BỘ (ZERO EXTERNAL ASSETS)**:
   - Toàn bộ ảnh (WebP), font chữ, icon SVG, file nhạc MP3 phải được tải về và đặt trực tiếp trong thư mục `assets/`.
   - Trong code chỉ gọi đường dẫn nội bộ cục bộ (thông qua `$resolveAsset(...)` hoặc `/mau-thiep/{slug}/assets/...`), CẤM TUYỆT ĐỐI dùng link từ server bên ngoài.
   - Loại bỏ toàn bộ các nút rác, watermark, widget không liên quan.

9. **HỢP ĐỒNG LIVE SYNC VỚI STUDIO TẠO THIỆP (`data-sync-target`)**:
   - Gắn đầy đủ `data-sync-target` cho mọi vị trí hiển thị: `groom_name`, `bride_name`, `couple_names`, `groom_father`, `groom_mother`, `bride_father`, `bride_mother`, `wedding_date_digits`, `wedding_lunar_date`, `wedding_day`, `wedding_month`, `wedding_year`, `calendar_month_year`, `wedding_calendar`, `party_time`, `party_venue`, `event_party_address`, `groom_bank_account`, `groom_bank_name`, `qr_groom_image`, `hero_photo_url`...
   - Cung cấp 2 hàm toàn cục trong script.js:
     * `window.updateMayhenCountdown(targetDate)`: Cập nhật đồng hồ đếm ngược tức thời.
     * `window.renderMayhenWeddingCalendar(targetDate)`: Tự động vẽ lại lưới lịch tháng theo đúng ngày được chọn.

---

### 📦 CẤU TRÚC BỘ FILE XUẤT RA TRONG ZIP:
Thư mục: `hanh-phuc-mang-ten-chung-minh/`
1. `template.blade.php`: File Blade hoàn chỉnh, kế thừa `@extends('client.layouts.invitation')`.
2. `styles.css`: Toàn bộ CSS thuần chuẩn BEM `mayhen_invitation_*`.
3. `script.js`: Native JS xử lý âm nhạc, mở thiệp, vẽ chữ ký canvas, gửi RSVP, countdown và calendar.
4. `schema.json`: Cấu hình danh sách các bước và trường nhập liệu chuẩn cho Studio Tạo Thiệp (`/tao-thiep`).
5. `assets/`: Chứa 100% file ảnh WebP nội bộ, tem wax seal SVG, file nhạc `music.mp3`.
Toàn bộ đóng gói thành file ZIP: **"Hạnh Phúc Mang Tên Chúng Mình.zip"**.
```

