# 🕊️ CONTEXT & KIẾN TRÚC TOÀN DIỆN DỰ ÁN MÂY HẸN (MAYHEN.VN)

> **⚠️ BẮT BUỘC ĐỐI VỚI MỌI AI AGENT (GEMINI, CLAUDE, GPT, CURSOR, COPILOT...):**
> 1. **ĐỌC FILE NÀY ĐẦU TIÊN** trước khi phân tích hoặc viết bất kỳ dòng code nào trong repository này!
> 2. **CẬP NHẬT FILE NÀY SAU MỖI LẦN CODE**: Bất kỳ khi nào hoàn thành hoặc thay đổi tính năng, cấu trúc, route, UI/UX hay database, AI **bắt buộc phải cập nhật lại file này** để giữ context luôn mới nhất trên Git.
> 3. Viết cho tôi commit Tiếng Việt có dấu có mở đầu mang ý nghĩa chính xác (ví dụ: fix(), refactor(),...) để tôi gửi commit, bạn không được phép tự gửi lệnh, viết git chi tiết mức độ vừa, đủ để đọc hiểu
> 4. **TUÂN THỦ 100% CÁC NGUYÊN TẮC CỐT LÕI**: Không tự ý thêm thư viện ngoài, không phá vỡ quy ước đặt tên CSS prefix và kiến trúc pure vanilla.

---

## 1. TỔNG QUAN DỰ ÁN & THƯƠNG HIỆU

- **Tên dự án**: Mây Hẹn
- **Domain**: `mayhen.vn`
- **Lĩnh vực**: Nền tảng thiết kế & gửi thiệp cưới online cao cấp (Quiet Luxury Wedding Invitations & Event RSVP).
- **Sứ mệnh & Định vị**:
  - Không đơn thuần là trang web tạo thiệp cưới số đại trà, Mây Hẹn là **tác phẩm nghệ thuật số** dành cho các cặp đôi trân quý sự tinh tế, thanh lịch và cảm xúc chân thật.
  - Phong cách chủ đạo: **Quiet Luxury, Editorial, Minimalist, Romantic, Modern**.
  - Trải nghiệm cốt lõi: Tôn vinh câu chuyện tình yêu, hiệu ứng chuyển động giấy lụa mượt mà, quản lý khách mời thông minh, tương thích hoàn hảo trên mọi thiết bị di động (Mobile-First).

### Bảng màu nhận diện thương hiệu (Brand Palette)
- **Warm Cream / Ivory**: `#FAF8F5`, `#F8F6F0`, `#FFFDF9` (Nền giấy mỹ thuật ấm áp, sang trọng).
- **Soft Beige / Muted Sand**: `#EFE8DD`, `#E5DCCD`, `#D8CEBD` (Đường viền, phân vùng nhẹ nhàng).
- **Charcoal / Espresso**: `#1F1D1A`, `#2C2824`, `#4A453E` (Màu chữ chính, độ tương phản cao nhưng không gắt như đen tuyền).
- **Muted Gold / Bronze**: `#C5A880`, `#9C7A4A`, `#B8935D` (Điểm nhấn kim loại cao cấp, hoa văn, nút kêu gọi hành động).
- **Dusty Rose / Sage Green**: `#D9B4A7`, `#9AA899` (Màu nhấn phụ cho thiệp hoa, phong bì và thiệp lưu niệm).

### Typography chuẩn mực
- **Heading / Editorial Title**: `Cormorant Garamond`, `Playfair Display` (Font serif thanh lịch, quý phái).
- **Body / Interface / Form**: `Inter`, system font sans-serif (Rõ nét, dễ đọc trên màn hình điện thoại).

---

## 2. NGUYÊN TẮC KỸ THUẬT BẮT BUỘC (STRICT FRONTEND RULES)

### ❌ CÁC ĐIỀU CẤM TUYỆT ĐỐI (ZERO TOLERANCE)
1. **CẤM FRAMEWORK CSS**: Tuyệt đối **KHÔNG** dùng Tailwind CSS, Bootstrap, Bulma, Foundation hoặc bất kỳ CSS framework nào khác.
2. **CẤM FRAMEWORK JS**: Tuyệt đối **KHÔNG** dùng React, Vue, Angular, Svelte, Alpine.js, jQuery.
3. **CẤM THƯ VIỆN HIỆU ỨNG NGOÀI**: Tuyệt đối **KHÔNG** cài đặt/nhúng Swiper.js, Slick Slider, GSAP, Anime.js, AOS, v.v. Tất cả carousel, modal, slider, animation đều viết bằng **Pure CSS + Native JS**.
4. **CẤM CDN NGOẠI**: Không tải CSS/JS từ `cdnjs`, `unpkg`, `jsdelivr`. Toàn bộ asset phải nằm cục bộ trong thư mục `public/assets/`.
5. **CẤM INLINE STYLE & INLINE SCRIPT**: Không viết thẻ `<style>` hoặc `<script>` bên trong file Blade (trừ thẻ JSON-LD SEO trong `<head>`). Toàn bộ CSS/JS phải được tách thành file độc lập trong `public/assets/client/`.

### 🏷️ TIỀN TỐ CSS CLASS BẮT BUỘC (MANDATORY CLASS PREFIX)
Mỗi module/trang bắt buộc phải dùng prefix riêng để chống xung đột style 100%:
- **Client Home (Trang chủ)**: `mayhen_home_*`
- **Client Services (Dịch vụ)**: `mayhen_service_*`
- **Client Pricing (Bảng giá)**: `mayhen_pricing_*`
- **Client Templates (Kho mẫu thiệp)**: `mayhen_templates_*`
- **Client Template Detail (Chi tiết mẫu)**: `mayhen_template_detail_*`
- **Client Preview (Xem thử mẫu thiệp)**: `mayhen_preview_*`
- **Client Blog / Bài viết**: `mayhen_blog_*`
- **Client About (Về chúng tôi)**: `mayhen_about_*`
- **Client Contact (Liên hệ)**: `mayhen_contact_*`
- **Client FAQ (Hỏi đáp)**: `mayhen_faq_*`
- **Client Auth (Đăng ký/Đăng nhập)**: `mayhen_auth_*`
- **Client Dashboard**: `mayhen_dashboard_*`
- **Client Editor (Trình tạo thiệp)**: `mayhen_editor_*`
- **Client Guests (Quản lý khách mời)**: `mayhen_guest_*`
- **Public Invitation (Trang thiệp cưới hiển thị)**: `mayhen_invitation_*`
- **Shared Components (Header, Footer, Button, Modal dùng chung)**: `mayhen_client_*` (ví dụ: `mayhen_client_header_*`, `mayhen_client_btn_*`, `mayhen_client_modal_*`).
- **Admin**: `mayhen_admin_*`

---

## 3. TECH STACK & MÔI TRƯỜNG DỰ ÁN

- **Ngôn ngữ & Runtime**: PHP 8.5
- **Framework**: Laravel 11.x
- **Database**: SQLite (local) / MySQL (production)
- **Frontend Architecture**:
  - Pure Blade Template (`resources/views/client/`)
  - Pure Vanilla CSS (`public/assets/client/css/`)
  - Pure Vanilla JavaScript ES Modules (`public/assets/client/js/`)
- **Testing**: Pest PHP (`vendor/bin/pest`)
- **Code Formatter**: Laravel Pint (`vendor/bin/pint --format agent`)
- **Thanh toán tự động**: VietQR (Ngân hàng Việt Nam) & SePay Webhook

---

## 4. QUY TẮC BẢO VỆ PHÂN TẦNG MẪU THIỆP & QUẢN LÝ TRẢI NGHIỆM KHÁCH HÀNG

### 1. Phân Tầng Gói Dịch Vụ (Tiering System)
- **Gói Free (Miễn phí)**: 
  - Cho phép người dùng chưa đăng nhập hoặc đã đăng nhập tự do tạo và chỉnh sửa thiệp ngay lập tức trên `/tao-thiep`.
  - Phù hợp với các cặp đôi muốn trải nghiệm nhanh (setup siêu tốc 2-3 phút).
- **Gói Plus (Nâng cao) & Gói Pro VIP (Hoàng gia)**:
  - Khách vãng lai (chưa đăng nhập) **vẫn được xem thử (preview) và xem chi tiết trang trọng** tại `/mau-thiep` và `/mau-thiep/{slug}`.
  - Tuyệt đối **KHÔNG** để nhãn thô như "Đăng nhập để dùng". Nút hành động luôn giữ nguyên văn phong tự nhiên, sang trọng: **"Tạo Thiệp Với Mẫu Này"**.
  - **Cơ chế bảo vệ thông minh (Smart Auth Redirect)**: Khi khách chưa đăng nhập bấm nút "Tạo Thiệp Với Mẫu Này" hoặc submit lưu thiệp Plus/Pro, hệ thống tự động ghi nhớ `intended_template` và chuyển hướng mượt mà sang trang Đăng nhập (`/dang-nhap`), sau khi đăng nhập thành công sẽ đưa khách quay trở lại ngay đúng mẫu thiệp họ vừa chọn.

### 2. Tiêu Chuẩn SEO & Chỉ Mục (Robots Indexing Rules)
- **Trang chủ (`/`), Kho mẫu (`/mau-thiep`), Bảng giá (`/bang-gia`), Dịch vụ (`/dich-vu`), Bài viết**: Cấu hình `index, follow` chuẩn SEO Top 1.
- **Trang chi tiết mẫu thiệp (`/mau-thiep/{slug}`)**: Cấu hình chuẩn `<meta name="robots" content="noindex, follow">` (không index để tránh trùng lặp nội dung bộ mẫu, nhưng cho phép bọ tìm kiếm follow để truyền sức mạnh liên kết nội bộ).
- **Trang biên tập tạo thiệp (`/tao-thiep`)**: Cấu hình chuẩn `<meta name="robots" content="noindex, nofollow">` (bảo vệ quyền riêng tư người dùng).
- **Trang thiệp cưới công khai (`/thiep/{slug}`)**: Cấu hình chuẩn `<meta name="robots" content="noindex, nofollow">` (bảo mật tuyệt đối thông tin cá nhân ngày cưới của cô dâu chú rể).

### 3. Quy Ước Đường Link Thiệp Cưới Chống Trùng Lặp (Slug Collision Prevention)
- Khi cô dâu chú rể trùng họ tên (ví dụ: Đặng Minh Quân & Lê Ái Như), link thiệp cưới `/thiep/{slug}` được bổ sung **hậu tố ngẫu nhiên kép** để đảm bảo 100% độc bản, không bao giờ xảy ra lỗi ghi đè thiệp của nhau:
  - Ví dụ URL chuẩn: `http://mayhen.vn/thiep/dang-minh-quan-le-ai-nhu-uxdbm-f5bez2shgm8`.

---

## 5. THIẾT KẾ CHI TIẾT TRANG MẪU THIỆP (`/mau-thiep/{slug}`)

Trang chi tiết mẫu thiệp (`resources/views/client/pages/template-detail.blade.php`) tuân thủ nghiêm ngặt chuẩn mực **Quiet Luxury Editorial** với đầy đủ các khối:
1. **Sân khấu trưng bày thiệp nghệ thuật (Visual Stage 3D Silk Elevation)**:
   - Khung chứa ảnh thiệp di động nổi bật với đổ bóng lụa 3D đa tầng (`rgba(28, 25, 23, 0.12)`), viền mạ tinh xảo, bám dính khi cuộn (`position: sticky; top: 90px`).
   - Nút kính mờ Glassmorphism **"Xem Trước Live"** (`/xem-thu/{slug}`) mở demo thực tế trên màn hình smartphone.
   - Dải thông số kỹ thuật chân ảnh: *Chuẩn màn hình điện thoại 100%* & *Độc bản Quiet Luxury*.
2. **Huy hiệu Gói Pro VIP ánh kim hoàng gia (Royal Seal)**:
   - Mẫu Pro được phủ gradient hoàng gia vàng kim hổ phách (`#FFFDF7` chuyển `#FDE68A`), viền chỉ vàng kim loại `#D97706`, icon vương miện dập nổi và hiệu ứng phát sáng nhẹ (`mayhen_gold_glow`).
3. **Bố cục 2 cột tỷ lệ vàng (Golden Ratio Grid)**:
   - Cột trái 460px - 480px, cột phải 1fr, khoảng cách gap co hẹp tinh tế (`1.5rem` - `2.5rem`), loại bỏ hoàn toàn cảm giác thưa thớt, rời rạc.
4. **Bảng Hồ Sơ Thiết Kế (Design Atelier Sheet)**:
   - Trưng bày tóm tắt: Tone sắc chủ đạo (kèm chấm màu thực tế), Kiểu chữ nghệ thuật, Hiệu ứng mở phong thư sáp 3D, Tính năng tương tác (RSVP, Nhạc nền, VietQR).
5. **Khối Đặc Quyền Tính Năng (Features Highlight)**:
   - Liệt kê các tính năng có sẵn: giấy mỹ thuật cổ điển, mở phong thư vintage, Love Story, sổ lưu bút online, mã VietQR mừng cưới tách biệt hai họ.
6. **Section "Trải Nghiệm Khách Mời Nhận Được" (The Guest Experience)**:
   - 3 điểm chạm cảm xúc thúc đẩy cô dâu chú rể sẵn sàng xuống tiền:
     1. *Chạm mở phong thư sang trọng*
     2. *Chiêm ngưỡng chuyện tình của hai bạn*
     3. *Xác nhận tham dự & Mừng cưới tinh tế*
7. **Section Bài Viết Chuyên Sâu Tối Ưu SEO & Thuyết Phục Khách Hàng (SEO Story)**:
   - H2: *"Tại Sao Mẫu Thiệp Cưới {Tên Mẫu} Lại Được Yêu Thích?"*.
   - Phân tích 3 lý do cốt lõi: Tối ưu hoàn hảo cho smartphone, Tiết kiệm 80% chi phí in ấn, Hộp mừng cưới VietQR văn minh & tinh tế.
8. **Section Câu Hỏi Thường Gặp (FAQ Accordion)**:
   - Giải đáp 4 thắc mắc then chốt: hiển thị trên di động, chỉnh sửa không giới hạn, tạo thiệp riêng cho từng khách mời, tốc độ tạo thiệp siêu tốc 3 phút.
9. **Cấu trúc Dữ Liệu Có Cấu Trúc Schema JSON-LD**:
   - Schema `Product` & `Offer`: Khai báo giá niêm yết, tình trạng tồn kho, số cặp đôi tin dùng và rating 4.9 sao.
   - Schema `FAQPage`: Giúp hiển thị danh sách câu hỏi nổi bật trên công cụ tìm kiếm.
   - **LƯU Ý KỸ THUẬT BLADE**: Luôn viết `"@@context": "https://schema.org"` để tránh Blade engine hiểu nhầm là directive `@context`.
10. **Liên Kết Nội Bộ - Mẫu Cùng Phong Cách (Similar Templates)**:
    - Tự động gợi ý 3 mẫu thiệp cùng phong cách, giữ chân khách hàng tiếp tục trải nghiệm.

---

## 6. KIẾN TRÚC THƯ MỤC DỰ ÁN

```text
mayhen.vn/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   └── TemplateController.php
│   │   │   └── Client/
│   │   │       ├── AuthController.php
│   │   │       ├── CheckoutController.php
│   │   │       ├── GuestController.php
│   │   │       ├── HomeController.php
│   │   │       ├── InvitationEditorController.php
│   │   │       ├── PageController.php
│   │   │       └── TemplateController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Wedding.php
│   │   ├── Invitation.php
│   │   ├── InvitationTemplate.php
│   │   ├── Plan.php
│   │   ├── Order.php
│   │   ├── Guest.php
│   │   └── GuestResponse.php
├── resources/
│   └── views/
│       ├── admin/                  # Giao diện Admin quản trị
│       └── client/
│           ├── layouts/
│           │   └── master.blade.php    # Layout khung chuẩn (SEO, CSS, JS master)
│           ├── components/             # Header, Footer, Head, Notification, v.v.
│           ├── pages/
│           │   ├── home.blade.php      # Landing Page trang chủ (12 sections chuẩn SEO Top 1)
│           │   ├── templates.blade.php # Danh mục kho mẫu thiệp cưới (bộ lọc tier & category)
│           │   ├── template-detail.blade.php # Trang chi tiết mẫu thiệp Quiet Luxury & Content SEO
│           │   ├── preview.blade.php   # Trang xem thử mẫu thiệp (mô phỏng smartphone)
│           │   ├── editor.blade.php    # Trình tạo & chỉnh sửa thiệp trực tiếp
│           │   ├── checkout.blade.php  # Thanh toán VietQR động
│           │   ├── guests.blade.php    # Quản lý danh sách khách & RSVP
│           │   ├── services.blade.php  # Trang giới thiệu dịch vụ
│           │   ├── pricing.blade.php   # Bảng giá các gói
│           │   ├── how-it-works.blade.php
│           │   ├── about.blade.php
│           │   ├── contact.blade.php
│           │   ├── faq.blade.php
│           │   ├── terms.blade.php
│           │   ├── privacy.blade.php
│           │   ├── login.blade.php
│           │   ├── register.blade.php
│           │   └── public-invitation.blade.php
│           └── invitations/            # Các theme thiệp cưới cụ thể
│               ├── hoang-gia-tem-sap-luxury/
│               ├── thanh-manh-01/
│               └── duyen-dang-01/
├── public/
│   └── assets/
│       ├── client/
│       │   ├── css/
│       │   │   ├── common/             # tokens.css, base.css, layout.css
│       │   │   ├── components/         # header.css, footer.css, buttons.css, forms.css
│       │   │   └── pages/              # home.css, templates.css, template-detail.css, editor.css...
│       │   ├── js/
│       │   │   ├── common/             # dom.js, events.js, http.js
│       │   │   ├── components/         # header.js, modal.js, toast.js
│       │   │   └── pages/              # home.js, templates.js, editor.js...
│       │   └── fonts/                  # Phông chữ nội bộ (offline-ready)
│       └── admin/                      # Assets chuyên biệt cho trang Admin
├── routes/
│   ├── web.php                         # Khai báo toàn bộ web routes
│   └── api.php                         # Khai báo các endpoint API nội bộ
├── context.gemini.md                   # FILE CONTEXT NÀY (BẮT BUỘC ĐỌC VÀ CẬP NHẬT)
└── AGENTS.md                           # Luật vận hành cho Agent
```

---

## 7. BẢN ĐỒ ROUTES & CHỨC NĂNG HỆ THỐNG

| Method | URI | Tên Route | Controller & Action | Chức năng & Chính sách SEO |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/` | `home` | `HomeController@index` | Trang chủ Landing Page chuẩn SEO Top 1 (`index, follow`) |
| **GET** | `/mau-thiep` | `templates.index` | `TemplateController@index` | Kho mẫu thiệp cưới có bộ lọc phân tầng & phong cách (`index, follow`) |
| **GET** | `/mau-thiep/{slug}`| `templates.show` | `TemplateController@show` | Chi tiết mẫu thiệp Quiet Luxury & Content SEO (`noindex, follow`) |
| **GET** | `/xem-thu/{slug}` | `templates.preview`| `TemplateController@preview` | Xem thử thiệp live: Tiêu đề `Xem thử: "Tên thiệp" | {App Name}`, thanh điều hướng Quiet Luxury (`Kho Mẫu`, `Chi Tiết Mẫu`, `Tạo Thiệp Với Mẫu Này`), Preview-only Mode chỉ mô phỏng UI không gửi dữ liệu backend, tự động cache-busting `?v={filemtime}` chống dính CSS cũ (`noindex, nofollow`) |
| **GET** | `/tao-thiep` | `invitation.create`| `InvitationEditorController@create` | Mở trình soạn thảo tạo thiệp mới (hoặc chỉnh sửa thiệp đã tạo với tham số `?edit={slug}`): Tích hợp Cropper tỷ lệ 3/4 Pure Canvas, Sinh VietQR ngân hàng an toàn 100%, Modal chọn nhạc đám cưới thông minh (`noindex, nofollow`, chặn khách chưa login nếu chọn mẫu Plus/Pro) |
| **POST** | `/tao-thiep` | `invitation.store` | `InvitationEditorController@store` | Lưu dữ liệu đám cưới & cấu hình thiệp (tự động cập nhật nếu truyền `invitation_id` của chủ nhân) |
| **POST** | `/tao-thiep/upload-asset` | `invitation.uploadAsset` | `InvitationEditorController@uploadAsset` | Tải file ảnh thực sự lên assets của mẫu thiệp khi submit tạo thiệp |
| **GET** | `/api/wedding-musics` | `invitation.musics` | `InvitationEditorController@musics` | API tìm kiếm bài hát thông minh siêu tốc độ từ thư mục ca sĩ (`/singer/{ca sĩ}/{bài hát}.mp3`), hỗ trợ tìm không dấu/có dấu, phân trang |
| **GET** | `/thiep/{slug}` | `invitation.show` | `InvitationEditorController@show` | Trang thiệp cưới công khai cho khách nhận; tự động nhận diện chủ nhân thiệp (qua User ID hoặc session `owned_invitation_slugs`) để hiển thị thanh quản trị nổi bật kèm nút **"Sửa Lại Thiệp Cưới"** (`noindex, nofollow`) |
| **POST** | `/thiep/{slug}/rsvp`| `rsvp.submit` | `GuestController@submitRsvp` | Khách gửi phản hồi RSVP xác nhận tham dự |
| **GET** | `/thanh-toan/{planSlug}`| `checkout.show` | `CheckoutController@show` | Trang thanh toán chuyển khoản VietQR |
| **POST** | `/api/checkout/process`| `checkout.process` | `CheckoutController@process` | Tạo đơn hàng & mã thanh toán VietQR |
| **GET** | `/api/checkout/status/{orderCode}`| `checkout.status`| `CheckoutController@checkStatus`| Polling kiểm tra trạng thái thanh toán |
| **POST** | `/api/webhook/payment`| `webhook.payment` | `CheckoutController@handleWebhook`| Webhook tự động kích hoạt gói khi nhận tiền |
| **GET** | `/tai-khoan/thiep/{id}/khach-moi`| `guests.index` | `GuestController@index` | Quản lý danh sách khách, gửi thiệp cá nhân hóa |
| **POST** | `/tai-khoan/thiep/{id}/khach-moi`| `guests.store` | `GuestController@store` | Thêm khách mời mới vào danh sách |
| **DELETE**| `/tai-khoan/khach-moi/{id}`| `guests.destroy` | `GuestController@destroy` | Xóa khách mời |
| **GET** | `/dich-vu` | `services` | `PageController@services` | Giới thiệu các dịch vụ của Mây Hẹn (`index, follow`) |
| **GET** | `/bang-gia` | `pricing` | `PageController@pricing` | Bảng giá các gói thiệp cưới online (`index, follow`) |
| **GET** | `/huong-dan` | `how-it-works` | `PageController@howItWorks` | Hướng dẫn 3 bước tạo và gửi thiệp (`index, follow`) |
| **GET** | `/ve-may-hen` | `about` | `PageController@about` | Câu chuyện thương hiệu Mây Hẹn (`index, follow`) |
| **GET** | `/lien-he` | `contact` | `PageController@contact` | Trang liên hệ hỗ trợ & tư vấn (`index, follow`) |
| **GET** | `/cau-hoi-thuong-gap`| `faq` | `PageController@faq` | Giải đáp thắc mắc thường gặp (`index, follow`) |
| **GET** | `/dieu-khoan-dich-vu`| `terms` | `PageController@terms` | Điều khoản dịch vụ (`index, follow`) |
| **GET** | `/chinh-sach-bao-mat`| `privacy` | `PageController@privacy` | Chính sách bảo vệ dữ liệu cá nhân (`index, follow`) |
| **GET/POST**| `/dang-nhap`, `/dang-ky`, `/dang-xuat` | Auth routes | `AuthController` | Xác thực người dùng & điều hướng thông minh sau khi đăng nhập |
| **ANY** | `/admin/*` | `admin.*` | Admin Controllers | Quản lý hệ thống mẫu, đơn hàng, khách hàng |

---

## 8. GIAO DIỆN AUTH: ĐĂNG NHẬP & ĐĂNG KÝ (ORGANIC CURVED WAVES & PILL CONTROLS)

Hệ thống xác thực (`/dang-nhap` và `/dang-ky`) đã được tái thiết kế toàn diện theo cảm hứng mỹ thuật hiện đại kết hợp Quiet Luxury của thương hiệu Mây Hẹn:

1. **Bố cục 2 Cột Đối Xứng (Split Screen Card)**:
   - Bo góc lớn cao cấp `36px` (`border-radius: 36px`), đổ bóng êm ái đa tầng.
   - **Cột Trái (Hero / Welcome Panel)**:
     - Nền gradient hoàng gia Charcoal kết hợp Muted Gold (`#1A1715` -> `#2A231C` -> `#382E24`).
     - Hiệu ứng các lớp sóng cong hữu cơ đan xen (`mayhen_auth_hero_wave_1` đến `4`) bằng CSS thuần với radial gradients.
     - Vòng tròn trắng nổi bật chứa biểu tượng Logo Mây Hẹn hoa sen dập nổi.
     - Nút viền mỏng bo tròn hoàn toàn dạng viên thuốc (`mayhen_auth_pill_btn_outline`) chuyển đổi nhanh giữa Đăng Nhập và Đăng Ký.
     - Footer thanh lịch: *"MÂY HẸN • QUIET LUXURY WEDDING"*.
   - **Cột Phải (Form Panel)**:
     - Nền trắng tinh khôi kết hợp mảng cong hữu cơ uốn nhẹ ở góc trên bên phải (`mayhen_auth_corner_accent`).
     - Tiêu đề `welcome` / `register` thanh thoát, subtitle định hướng người dùng.
     - **Ô nhập liệu dạng viên thuốc (Pill Inputs - `border-radius: 9999px`)**: Nền kem nhạt `#F4EFEA`, viền mờ tinh tế, placeholder có chấm nối đuôi (`Email........`, `Mật khẩu........`), chuyển màu mượt mà khi focus.
     - **Nút gửi dạng viên thuốc (Pill Submit Button)**: Đặt ở chính giữa form với kích thước cân đối, phủ gradient Muted Gold hoàng gia (`#C5A880` -> `#9C7A4A`), chữ hoa `ĐĂNG NHẬP` / `ĐĂNG KÝ`.
     - Dòng chuyển đổi tài khoản dưới nút với link hover đổi màu mượt mà.
2. **Tương Thích Di Động (Mobile Excellence < 768px)**:
   - Trên màn hình điện thoại, card tự động chuyển sang 1 cột dạng dọc: Phần trên là banner cong hữu cơ ôm lấy logo Mây Hẹn, phần dưới là form nhập liệu viên thuốc gọn gàng, chạm vuốt dễ dàng, không bị co giật.
3. **Bộ Kiểm Thử Tự Động (Pest Feature Tests)**:
   - Đã được bao phủ trong `tests/Feature/AuthTest.php` kiểm tra toàn vẹn mã trạng thái HTTP 200, các thẻ CSS prefix `mayhen_auth_*` và các trường input.

---

## 9. HỆ THỐNG PHÂN LOẠI & BỘ LỌC TIER MẪU THIỆP (FREE / PLUS / PRO)

Kho mẫu thiệp cưới (`/mau-thiep`) hỗ trợ bộ lọc phân loại theo gói dịch vụ (`?tier=free`, `?tier=plus`, `?tier=pro`):

1. **Phân Loại Gói (Tier Categorization)**:
   - **Gói Miễn Phí (`tier: free` — 4 mẫu)**: `minimal-sand-beige`, `minimalism-darkred`, `minimalism-darkbrown`, `mai-lan-white`. Đặc điểm: Setup siêu tốc 2 phút, bố cục tinh gọn, đầy đủ thông tin lễ cưới cốt lõi & bản đồ GPS.
   - **Gói Plus (`tier: plus` — 6 mẫu)**: `botanical-sage-garden`, `romantic-dusty-rose`, `cinema-modern-lovestory`, `boho-floral-green`, `to-duyen-green`, `vuon-kinh-pink`. Đặc điểm: Bố cục hoa lá & sân vườn lãng mạn, hiệu ứng cánh hoa rơi, timeline câu chuyện tình yêu, album ảnh cưới HD.
   - **Gói Pro VIP (`tier: pro` — 6 mẫu)**: `envelope-reveal-luxury`, `editorial-scroll-vogue`, `song-hy-red`, `song-hy-green`, `song-hy-v2-red`, `long-phung-red`. Đặc điểm: Mở phong thư hoàng gia 3D tem sáp niêm phong, phong cách tạp chí Editorial cao cấp, Song Hỷ & Long Phụng dệt gấm cung đình.

2. **Cơ Chế Truy Vấn & Migration Dữ Liệu**:
   - Dữ liệu tier được lưu trữ chuẩn hóa trong cột JSON `default_config->tier`.
   - Migration `2026_10_15_000001_update_invitation_templates_tier_and_config.php` tự động cập nhật toàn bộ metadata `tier`, `complexity`, `setup_time`, `stats` cho toàn bộ mẫu.
   - `TemplateController@index` áp dụng truy vấn JSON linh hoạt kết hợp fallback an toàn cho trường hợp tier chưa set.
   - Kiểm thử tự động `tests/Feature/TemplateFilterTest.php` bao phủ 100% các trạng thái lọc.

3. **Cơ Chế Tự Động Cuộn Chạm Top (Auto-Scroll Offset 75px)**:
   - Khi chọn lọc theo bất kỳ gói nào (`mayhen_templates_tier_bar`), trang web tự động cuộn đưa thanh `mayhen_templates_tier_bar` lên chạm mép trên với khoảng cách chính xác `top: 75px` (ngay sát chân Header) trên cả Desktop và Mobile.
   - Kết hợp `scroll-margin-top: 75px` trong [templates.css](file:///d:/Project/Laravel/mayhen.vn/public/assets/client/css/pages/templates.css), neo định danh `#mayhen_templates_tier_bar` và module JavaScript [templates.js](file:///d:/Project/Laravel/mayhen.vn/public/assets/client/js/pages/templates.js).

---

## 10. KIẾN TRÚC HEADER & MOBILE MENU DRAWER

1. **Khắc Phục Lỗi Xuyên Thấu & Stacking Context**:
   - Khi mở menu mobile drawer, `mayhen_client_header_wrapper` được nâng lên `z-index: 10000;` với nền đục `#FAF8F5` và tắt `backdrop-filter` để triệt tiêu lỗi tạo Containing Block làm fixed element bị lỗi.
   - Drawer `mayhen_client_header_drawer` phủ toàn màn hình (`100vw x 100dvh`), nền đục `#FAF8F5 !important;` với `z-index: 9999;` ngăn chặn hoàn toàn việc nội dung trang web bên dưới đè xuyên qua chữ menu.
2. **Trải Nghiệm Tương Tác**:
   - Hamburger icon 3 vạch tự động chuyển đổi thành dấu X đóng menu mượt mà (`mayhen_client_header_toggle_active`).
   - Tự động kích hoạt khóa cuộn trang (`body.mayhen_no_scroll`) khi mở drawer để chống cuộn trang bên dưới.
   - Tự động đóng menu khi click vào bất kỳ liên kết nào, nhấn phím Escape hoặc thay đổi kích thước màn hình sang desktop.
3. **Nút Đăng Nhập Header Nổi Bật Nhẹ Nhàng (Quiet Luxury)**:
   - Nút Đăng nhập trên Header (`mayhen_client_header_login_btn`) được thiết kế dạng bo tròn viên thuốc (pill), nền kem ánh vàng ngà mờ `rgba(197, 168, 128, 0.12)`, viền mỏng Muted Gold `rgba(197, 168, 128, 0.45)`, chữ vàng đậm sang trọng `#8C6A3B`, tạo điểm nhấn thanh tao vừa vặn trên mobile mà không gây gắt mắt.

---

## 11. THANH ĐIỀU HƯỚNG ĐÁY MÀN HÌNH TOÀN WEB (BOTTOM NAVIGATION BAR)

Hệ thống thanh dock điều hướng đáy trang (`mayhen_client_bottom_nav_*`) được tích hợp cố định trên toàn bộ website (`client.layouts.master`):

1. **Cấu Trúc 5 Nút: Icon SVG kèm Nhãn Text Rõ Ràng**:
   - **Nút 1: Trang chủ** (`route('home')`) — Icon Home nét vẽ thanh mảnh + Text `Trang chủ`.
   - **Nút 2: Mẫu thiệp** (`route('templates.index')`) — Icon Card / Grid layout + Text `Mẫu thiệp`.
   - **Nút 3: Tạo thiệp** (`route('invitation.create')`) — **Nút tròn lớn ở chính giữa nổi bật** (`mayhen_client_bottom_nav_create_btn`, kích thước 44px, margin-top nhô nhẹ -16px), viền 3px `#FAF8F5`, phủ gradient Muted Gold, hiệu ứng sóng hào quang lan tỏa (`mayhenPulseGlow`), icon nhịp thở (`mayhenIconFloat`) + Text `Tạo thiệp` màu Gold đậm nét ở dưới.
   - **Nút 4: Yêu thích** (`route('templates.index')#yeu-thich`) — Icon Trái tim + Text `Yêu thích`.
   - **Nút 5: Trợ lý AI** (`route('contact')#tro-ly-ai`) — Icon Robot AI + Text `Trợ lý AI`.
2. **Quy Chuẩn Kỹ Thuật**:
   - Bố cục flex dạng cột cân đối: Icon ở trên, text label ở dưới (size `0.65rem`), chống trống trải và giúp người dùng nhận diện chức năng ngay lập tức.
   - Tự động kích hoạt trạng thái Active chuyển sang màu vàng ánh kim `#9C7A4A` và font-weight 600 theo từng route.
   - Khoảng đệm bù đáy cho body: `padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px))` đảm bảo không bao giờ che khuất nội dung trang.
   - Tương thích đa thiết bị: Cố định chân màn hình trên Mobile và chuyển thành thanh Dock nổi bo tròn 9999px ở giữa đáy trên Desktop.

---

## 12. TỐI ƯU HÓA MOBILE-FIRST CHO KHO MẪU THIỆP (/mau-thiep)

Nhằm nâng cao trải nghiệm người dùng trên màn hình nhỏ và tuân thủ định vị Quiet Luxury:
1. **Hero Header & Highlights Siêu Gọn**:
   - Header thu gọn padding dọc xuống `4px 0 8px 0`, tiêu đề H1 co về `1.35rem` thanh lịch, mô tả ngắn gọn `0.8125rem`.
   - Dải 4 điểm nhấn (`mayhen_templates_highlights_bar`) được chuyển thành thanh cuộn ngang 1 hàng với các pill chip nền sáng (`overflow-x: auto`), không chiếm diện tích dọc màn hình.
2. **Bộ Lọc Phong Cách & Gói Dịch Vụ Dạng Horizontal Scroll Chips**:
   - Cả 2 thanh lọc (`mayhen_templates_filter_bar` và `mayhen_templates_tier_bar`) được thiết kế cuộn ngang 1 hàng duy nhất (`flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;`), vuốt ngón tay lướt êm ái, xóa bỏ hoàn toàn hiện tượng rớt thành 4-5 hàng nham nhở.
3. **Thanh Meta Bar & Sắp Xếp Chống Gãy Chữ**:
   - Tối ưu hiển thị số lượng mẫu thiệp dạng rút gọn trên mobile (`6 / 16 mẫu thiệp`) và cụm sắp xếp nằm cùng 1 hàng duy nhất (`white-space: nowrap`), loại bỏ triệt để lỗi gãy chữ "Nổi" và "bật".
4. **Lưới Mẫu Thiệp 2 Cột Cân Đối & Tỷ Lệ Ảnh 3:4**:
   - Khung hình thẻ thiệp chuyển sang `aspect-ratio: 3 / 4` vừa vặn, không bị dài lòng thòng.
   - Thân thẻ được co gọn padding (`8px 8px 10px`), ẩn mô tả dài dòng trên mobile 2 cột để chiều cao thẻ đồng đều tuyệt đối.
   - Nút "Xem Live" và "Dùng Mẫu" dàn ngang cân xứng, bấm mượt mà.
5. **Tinh Gọn Khoảng Trống Các Section SEO, So Sánh Gói, FAQ, CTA**:
   - Thu nhỏ padding dọc từ `64px - 96px` xuống còn `20px - 28px`, giúp giao diện liền mạch, trang nhã, không còn cảm giác rỗng tuếch hay xa cách.

---

## 13. TỐI ƯU HÓA MOBILE-FIRST CHO TRANG CHI TIẾT MẪU THIỆP (/mau-thiep/{slug})

Nhằm mang lại trải nghiệm xem và đặt thiệp cưới đỉnh cao trên điện thoại di động:
0. **Breadcrumb Tinh Gọn 1 Hàng Duy Nhất**:
   - Breadcrumb và tất cả các mục bên trong được cố định trên 1 hàng duy nhất (`flex-wrap: nowrap; white-space: nowrap; overflow-x: auto; scrollbar-width: none;`), chống hoàn toàn hiện tượng tên mẫu thiệp bị rớt xuống dòng thứ 2 gây mất thẩm mỹ.
1. **Sân Khấu Trưng Bày Thiệp (Visual Stage) & Chuyển Động Tự Động Cuộn**:
   - Thẻ trưng bày co gọn padding xuống `8px`, bo góc `16px`.
   - Khung hình thiệp `mayhen_template_detail_stage_media` và ảnh `mayhen_template_detail_stage_img` luôn luôn được cố định tỷ lệ chuẩn mực `aspect-ratio: 3 / 4` trên mọi kích thước màn hình (desktop, tablet, mobile), xóa bỏ giới hạn max-height để ảnh thiệp không bao giờ bị méo hay sai lệch tỷ lệ.
   - **Tự động cuộn mẫu chậm rãi khi Hover (Desktop)**: Khi rê chuột vào khung thiệp, ảnh bên trong tự động trượt mượt mà từ trên xuống dưới (`transition: transform 6s cubic-bezier(0.25, 0.46, 0.45, 0.94)`), khi rời chuột ra tự động cuộn êm ái quay về đỉnh ban đầu. Kèm tooltip gợi ý nhỏ gọn `mayhen_template_detail_stage_hint_desktop`.
   - **Nút "Chạy Thử" trên Mobile**: Góc trên bên phải khung thiệp tích hợp nút `mayhen_template_detail_btn_autoscroll` bo tròn Glassmorphism. Khi khách chạm vào, hệ thống tự động kích hoạt chuyển động trượt chậm rãi từ đầu tới chân thiệp, dừng ngắm 1.2s rồi tự động quay về đỉnh hoặc cho phép bấm dừng bất kỳ lúc nào.
   - Nút nổi "Xem Trước Live" dạng Glassmorphism bo tròn nhỏ gọn (`padding: 7px 16px; font-size: 0.75rem`).
   - Dải thông số chân sân khấu rút gọn flex 1 hàng, icon 14px thanh nhã.
2. **Khối Thông Tin Tác Phẩm & Hồ Sơ Thiết Kế**:
   - Tiêu đề H1 co về `1.35rem`, line-height `1.25`, tôn vinh nghệ thuật typography chữ than ấm `#1F1D1A`.
   - Bảng **Hồ Sơ Thiết Kế** tổ chức thành 2 cột dạng lưới compact (`grid-template-columns: 1fr 1fr; gap: 8px 12px; padding: 12px 14px;`), hiển thị rõ ràng tone màu chủ đạo, font chữ, hiệu ứng mở mà không chiếm diện tích dọc.
   - Danh sách đặc quyền rút gọn gàng, icon chuẩn tone Mây Hẹn.
   - Nút bấm chính "Tạo Thiệp Với Mẫu Này" và "Xem Thử Trực Tiếp" căn chỉnh chiều cao vừa tay, icon cam kết đổi sang tone màu Muted Gold `#9C7A4A`.
3. **Sân Khấu Trưng Bày Thiệp Cưới Dải Cuộn Liên Hoàn (Showcase Reel)**:
   - Khung sân khấu tỷ lệ cố định **3:4** (`mayhen_template_detail_stage_media`) tổ chức theo cấu trúc dải cuộn (`mayhen_template_detail_stage_reel`):
     - **Vị trí 1**: Poster ảnh đại diện hiển thị đầy đủ 100% khi vừa nạp trang (`load`).
     - **Vị trí 2**: Iframe trang web mẫu thiệp cưới sống động (`route('templates.preview', $template->slug) . '?embed=1'`) bám ngay sau đít ảnh poster.
   - **Cơ chế chuyển động thông minh (Autoplay 3s & Hover Restart)**:
     - **Tự chạy sau 3 giây khi load trang**: Người dùng vừa vào trang sẽ ngắm ảnh đại diện trong 3 giây, sau đó hệ thống tự động đẩy ảnh poster lên trên và cuộn nội dung trang thiệp trong iframe mà không cần thao tác chuột. (Đã fix triệt để phạm vi function scope `setButtonState`).
     - **Chạy lại từ đầu khi Hover**: Bất cứ khi nào người dùng rê chuột vào (`mouseenter`), dải cuộn lập tức reset về vị trí xuất phát (ảnh đại diện) và bắt đầu chạy lại từ đầu.
     - Khi chuột rời ra (`mouseleave`): Toàn bộ thu hồi êm dịu (`transition: transform cubic-bezier`) đưa ảnh đại diện ban đầu trở lại vị trí trung tâm.
4. **Các Section Trải Nghiệm, Bài Viết SEO, FAQ & Mẫu Liên Quan**:
   - Khoảng cách padding dọc giữa các section giảm mạnh từ `4.5rem` xuống còn `24px`, xóa bỏ khoảng trắng thừa.
   - **Mẫu Thiệp Cùng Phong Cách**: Chuyển từ layout 1 cột ảnh ngang 4:3 sang **Lưới 2 cột dọc tỷ lệ 3:4 chuẩn mực** đồng bộ với trang danh mục.
   - Hộp kêu gọi hành động cuối trang (Bottom Banner) co gọn `padding: 20px 14px`, nút bấm mở rộng 100% chiều ngang tiện lợi.

---


## 14. CẤU TRÚC DATABASE & CÁC MODEL CHÍNH

1. **`User` (`users`)**:
   - Quản lý tài khoản cô dâu, chú rể, studio đối tác hoặc admin.
2. **`InvitationTemplate` (`invitation_templates`)**:
   - `id`, `name`, `slug`, `category`, `thumbnail_url`, `demo_url`, `default_config` (JSON lưu: `tier` [free/plus/pro], `stats` [rating, pairs_used], `features`, `animation`, `complexity`, `font_serif`, `setup_time`, `description`, `accent_color`, `style_layout`, `required_inputs`), `current_version`, `is_active`, `is_featured`.
3. **`Wedding` (`weddings`)**:
   - `id`, `user_id`, `groom_name`, `bride_name`, `groom_parents`, `bride_parents`, `event_date`, `venue_name`, `venue_address`, `map_lat_lng`, `qr_bank_info`.
4. **`Invitation` (`invitations`)**:
   - `id`, `wedding_id`, `template_id`, `custom_slug`, `music_url`, `custom_css`, `content_json`, `is_published`, `view_count`.
5. **`Guest` (`guests`)**:
   - `id`, `invitation_id`, `name`, `phone`, `group_name` (Nhà trai, Nhà gái, Đồng nghiệp, Bạn bè), `access_code`, `status`.
6. **`GuestResponse` (`guest_responses`)**:
   - `id`, `guest_id`, `invitation_id`, `is_attending`, `guest_count`, `dietary_notes`, `wishes_message`.
7. **`Plan` (`plans`)**:
   - `id`, `name`, `slug`, `price`, `duration_days`, `features_json`.
8. **`Order` (`orders`)**:
   - `id`, `user_id`, `plan_id`, `order_code`, `amount`, `status` (`pending`, `paid`, `cancelled`), `payment_method` (`vietqr`, `bank_transfer`).

---

## 9. QUY TRÌNH LÀM VIỆC DÀNH CHO AI (STANDARD WORKFLOW)

Mỗi khi nhận một yêu cầu từ người dùng, AI phải thực hiện tuần tự 5 bước:

### 🔹 BƯỚC 1: Đọc & Tham Chiếu Context
- Đọc file `context.gemini.md` để nắm rõ hiện trạng và các quy tắc.
- Đọc skill tương ứng trong thư mục `.agents/skills/` nếu làm việc ở module đặc thù.

### 🔹 BƯỚC 2: Viết Code Tuân Thủ Nghiêm Ngặt
- Frontend: Chỉ viết HTML thuần, CSS thuần (`mayhen_*`), JS native (ES Modules). Tuyệt đối không nhúng CDN, không viết inline script/style.
- Backend: Dùng Form Request cho validation, giữ Controller tinh gọn, sử dụng Service/Action khi xử lý logic phức tạp.
- Đảm bảo kiểm tra lỗi syntax hoặc xung đột Blade directive.

### 🔹 BƯỚC 3: Format & Kiểm Tra Code
- Định dạng code PHP: Chạy `vendor/bin/pint --format agent`.
- Chạy unit/feature tests: Chạy `vendor/bin/pest` để đảm bảo không làm gãy tính năng cũ.

### 🔹 BƯỚC 4: CẬP NHẬT FILE `context.gemini.md` (BẮT BUỘC)
- Bổ sung các tính năng, route mới, thay đổi cấu trúc vừa thực hiện vào file `context.gemini.md` này.
- Đảm bảo tài liệu phản ánh chính xác 100% hiện trạng mới nhất của repository.

### 🔹 BƯỚC 5: Soạn Nội Dung Commit (Tiếng Việt có dấu)
- Viết sẵn Git Commit Message rõ ràng, súc tích bằng tiếng Việt có dấu để người dùng copy và tự push lên Git.
- **LƯU Ý**: Không tự ý chạy `git push` nếu người dùng chưa cho phép.

---

## 10. NHẬT KÝ CẬP NHẬT & TÍCH HỢP MẪU THIỆP (CHANGELOG)

### 📌 Phiên bản 1.1.0 — Tích hợp mẫu chuẩn độc bản "Nét Mực Tình Yêu" (net-muc-tinh-yeu)
- **Dọn dẹp hệ thống**:
  - Đã làm sạch toàn bộ dữ liệu mẫu thiệp cũ trong database (`invitation_templates`, `invitations`, `weddings`, `guests`, `guest_responses`).
  - Đã dọn dẹp các thư mục view cũ trong `resources/views/client/invitations/`.
- **Tích hợp mẫu thiệp flagship "Nét Mực Tình Yêu"**:
  - **Slug**: `net-muc-tinh-yeu`
  - **Tên**: Nét Mực Tình Yêu (Phong cách Quiet Luxury, Thư pháp phương Tây Quarzo & Editorial sang trọng).
  - **Thư mục Assets**: `public/assets/client/invitations/net-muc-tinh-yeu/` (toàn bộ 28 hình ảnh, font chữ Quarzo, nhạc nền MP3, styles.css, script.js native).
  - **Script tương tác động**: `public/assets/client/invitations/net-muc-tinh-yeu/script.js` kết nối mượt mà với biến toàn cục `window.__MAYHEN_WEDDING_DATE`, `window.__MAYHEN_WEDDING_TARGET_TIME` và mã VietQR `window.__MAYHEN_GIFT_QR_GROOM` / `window.__MAYHEN_GIFT_QR_BRIDE`.
  - **Blade Template**: `resources/views/client/invitations/net-muc-tinh-yeu/template.blade.php` hỗ trợ cả chế độ nhúng Iframe (`embed=1`) và chế độ thiệp độc lập của khách hàng.
  - **Schema Cấu Hình Khách Hàng**: `resources/views/client/invitations/net-muc-tinh-yeu/schema.json` mô tả 100% các trường dữ liệu tùy chỉnh:
    1. Thông tin cô dâu, chú rể & phụ mẫu hai bên.
    2. Hai tab sự kiện riêng biệt: Tiệc Cưới & Lễ Thành Hôn Nhà Gái vs Nhà Trai (ngày giờ, âm lịch, địa chỉ, link bản đồ Google Maps).
    3. Bộ sưu tập ảnh: Ảnh bìa phong bì mở tem sáp, ảnh mở thiệp, ảnh dọc (Hero photo), bộ 3 ảnh ghép Triptych, Album 10 ảnh Lightbox chạm phóng to sắc nét, ảnh chân trang.
    4. Lời ngỏ tình yêu (Love story / Quote).
    5. Lịch trình ngày cưới (Timeline mốc thời gian tùy biến).
    6. Quy định trang phục (Dresscode gợi ý màu sắc).
    7. Bộ đếm ngược ngày cưới & Lịch tháng tự động tính toán.
    8. Xác nhận tham dự (RSVP Online) & Sổ lưu bút lời chúc.
    9. Hộp mừng cưới VietQR riêng biệt cho Chú Rể và Cô Dâu.
    10. Nhạc nền MP3 lãng mạn kèm phím đĩa than quay nhẹ nhàng.
  - **Controllers cập nhật**:
    - `TemplateController::preview()`: Nhận diện và tự động nạp view `client.invitations.{$template->slug}.template` khi xem thử.
    - `InvitationEditorController::show()`: Nhận diện và tự động nạp template tương ứng khi khách mở trang thiệp đã xuất bản `/thiep/{slug}`.
  - **Database Seeder**: `InvitationTemplateSeeder.php` thiết lập duy nhất mẫu `net-muc-tinh-yeu` với đầy đủ metadata, tier Pro VIP, complexity Deluxe, tags và thống kê.
  - **Kiểm thử tự động**: Đã bổ sung `tests/Feature/NetMucTinhYeuTemplateTest.php` với 100% tests passed (xem chi tiết, xem thử preview, hiển thị dữ liệu tùy biến của cặp đôi).

### 📌 Phiên bản 1.1.1 — Studio Tạo Thiệp Thông Minh Thích Ứng Theo Schema (Schema-Driven Live Editor)
- **Vấn đề đã giải quyết triệt để**: Khắc phục tình trạng "mẫu thiệp một kiểu, vào tạo thiệp lại hiển thị một kiểu", loại bỏ hoàn toàn khung preview tĩnh hardcode và form cứng ngắc không phản ánh đúng mẫu thiệp.
- **Khung Preview Điện Thoại (Exact Template Live Preview)**:
  - Khung điện thoại trong `editor.blade.php` được nhúng trực tiếp `<iframe>` trỏ tới route xem thử của chính mẫu thiệp đó: `/xem-thu/{slug}?embed=1&mode=editor`.
  - Khách hàng chọn mẫu nào (ví dụ: Nét Mực Tình Yêu), chiếc điện thoại bên phải hiển thị **chính xác 100% mẫu thiệp đó** với đầy đủ phong bì tem sáp, thư pháp Quarzo, 2 tab sự kiện, timeline, album ảnh, VietQR...
- **Form Động Thích Ứng Theo Từng Mẫu (Schema-Driven Form Engine)**:
  - Form bên trái tự động duyệt qua `schema['sections']` để sinh số bước Stepper Navigation và các trường dữ liệu tương ứng riêng biệt cho từng mẫu.
  - Hỗ trợ đầy đủ các loại input: `text`, `textarea`, `date`, `time`, `image` (kèm preview ảnh nhỏ), `repeater` (Timeline mốc thời gian), `gallery` (Album 10 ảnh), `color`.
  - Tự động chuẩn hóa input name chuẩn Laravel: `wedding[groom_name]`, `wedding[extra_data][bride_party_address]`, `wedding[extra_data][timeline]`, v.v.
- **Đồng Bộ Hai Chiều Thời Gian Thực (PostMessage Realtime Sync)**:
  - Khi người dùng gõ vào bất kỳ trường nào trên form, `editor.js` tự động phát tín hiệu `MAYHEN_SYNC_INPUT` qua `postMessage` sang iframe.
  - Template của mẫu thiệp lắng nghe và cập nhật DOM ngay lập tức (tên cô dâu chú rể, ngày cưới, ảnh cưới, phụ mẫu, địa chỉ tiệc, quote, dresscode...).
- **Tối Ưu Stepper Navigation Tabs**:
  - Khắc phục lỗi các tab bị co ép đè chữ lên nhau bằng cách thiết lập `flex: 0 0 auto`, `white-space: nowrap`, cuộn ngang cảm ứng mượt mà.
  - Sử dụng tiêu đề ngắn gọn (Short Title) trên các tab (Đôi Uyên Ương, Nhà Gái, Nhà Trai, Ảnh Cưới, Lịch Trình, Mừng Cưới) kết hợp tooltip đầy đủ.
- **Kiểm thử tự động**: Đã bổ sung test case Feature kiểm tra iframe preview và dynamic schema fields, đạt **9/9 tests passed (96 assertions)**.

### 📌 Phiên bản 1.1.2 — Chuẩn Hóa Định Dạng Thư Mục Template & Tải Ảnh Trực Tiếp Vào Assets
- **Chuẩn Hóa Cấu Trúc Template Trọn Gói (Self-contained Template Package)**:
  - Bắt buộc mỗi thư mục mẫu thiệp tại `resources/views/client/invitations/{slug}/` phải có cấu trúc chuẩn mực gồm 6 thành phần:
    1. `assets/` (chứa toàn bộ hình ảnh, font chữ, âm thanh độc bản của mẫu)
    2. `README.md` (hướng dẫn và mô tả phong cách mẫu thiệp)
    3. `schema.json` (định nghĩa các section và trường dữ liệu tùy biến)
    4. `script.js` (logic chuyển động và tương tác native)
    5. `styles.css` (tạo hình, typography, layout thuần CSS)
    6. `template.blade.php` (Blade view nhận dữ liệu động và đồng bộ thời gian thực)
  - Đồng thời duy trì bản sao assets tĩnh tại `public/assets/client/invitations/{slug}/` để trình duyệt tải với tốc độ cao nhất.
- **Tính Năng Tải Ảnh Trực Tiếp Vào Assets Của Mẫu Đang Sửa**:
  - Bổ sung endpoint `POST /tao-thiep/upload-asset` trong `InvitationEditorController`.
  - Khách hàng có thể chọn tải ảnh từ máy tính hoặc điện thoại tại tất cả các trường ảnh (Ảnh cưới mở thiệp, Ảnh Hero dọc, Bộ 3 ảnh Triptych, Ảnh chân trang, Mã QR mừng cưới Nhà Trai, Nhà Gái và toàn bộ 10 ảnh trong Album cưới).
  - Tự động lưu trữ file tải lên vào thư mục assets của template đang sửa: `public/assets/client/invitations/{template_slug}/assets/` và đồng bộ vào `resources/views/client/invitations/{template_slug}/assets/`.
  - Hỗ trợ xem trước tức thì (Instant Local Preview) và lập tức đồng bộ sang iframe xem trước thời gian thực.
- **Kiểm thử tự động**: Đã bổ sung test case upload asset, đạt **10/10 tests passed (99 assertions)**.

### 📌 Phiên bản 1.1.3 — Tái Thiết Kế Trải Nghiệm Mở Thiệp 3D & Tinh Chỉnh Giao Diện Quiet Luxury (net-muc-tinh-yeu)
- **Màn hình mở đầu trang trọng (Full Height Cover & 3D Interactive Envelope Stage)**:
  - Khắc phục triệt để tình trạng "chưa mở thiệp mà toàn bộ nội dung đã phơi bày":
    - Khởi tạo trang với class `mayhen_invitation_scroll_locked` trên `body` để khóa cuộn ban đầu, tập trung 100% ánh nhìn vào màn hình mở đầu.
    - Màn hình mở đầu chiếm trọn chiều cao viewport (`min-height: 100vh`/`100dvh`) với nền Warm Cream/Ivory, khung viền kép thanh thoát và hoa văn góc đối xứng tinh xảo.
  - Thiết kế phong bì cưới 3D chân thực (Deluxe Envelope):
    - Thân phong bì màu xám than chì nhung mờ cao cấp (`#252422`), viền lót chỉ vàng dát nhũ nhẹ.
    - Tem sáp niêm phong (Wax Seal) dập nổi 3D màu đỏ rượu trầm (`#871f1f`) với chữ Hỷ `囍` dát nhũ vàng kim và hiệu ứng nhịp thở thu hút (`mayhen_invitation_pulse`).
    - Tấm thiệp mời bên trong (Letter inside) với ảnh cưới của đôi bạn nằm khéo léo trong khe phong bì.
  - Trải nghiệm mở thiệp mượt mà và tự động cuộn xuống:
    - Khi chạm vào phong bì, nút "Chạm Mở Thiệp" hoặc tem sáp: Nắp phong bì mở 3D lật lên (`transform: rotateX(180deg)`), tem sáp mở nhẹ, tấm thiệp trồi lên (card slide-up) hé lộ ảnh cưới lung linh.
    - Tự động kích hoạt nhạc nền cưới MP3 du dương.
    - Gỡ bỏ khóa cuộn (`body.mayhen_invitation_scroll_locked`) và sau 850ms tự động cuộn mượt mà (`window.scrollTo({ behavior: 'smooth' })`) dẫn lối người xem xuống phần nội dung chi tiết.
    - Bổ sung nút "Cuộn xuống xem tiếp ↓" trang nhã.
- **Khắc phục tình trạng thô sơ, lệch lạc và khoảng cách quá xa (Tight & Rhythmic Spacing)**:
  - **Gia đình hai bên (`mayhen_invitation_dhsuudushi9428r_families`)**:
    - Loại bỏ dải ribbon màu đen xì lệch méo bên trái và cột chữ dọc kỳ lạ.
    - Thay thế bằng thẻ thiệp song hành đối xứng (Symmetrical Family Card) gồm 2 cột cân bằng: Cột trái "NHÀ TRAI", Cột phải "NHÀ GÁI", chính giữa là biểu tượng chữ Hỷ trang nhã.
  - **Tối ưu khoảng cách dọc (Vertical Spacing)**:
    - Thu nhỏ các padding dư thừa từ 38px-45px xuống còn 20px-26px có nhịp điệu hài hòa, loại bỏ các `min-height` cứng ngắc.
  - **Typography & Màu sắc Quiet Luxury**:
    - Tiêu đề viết tay Quarzo phối hợp font Montserrat hiện đại, kích thước chữ cân đối.
    - Tông màu chuẩn mực: Trắng ngà (Ivory), Mực than (#181818), Nâu ấm (#faf7f2), Nhũ vàng đồng (#9c7a4a), không màu mè sặc sỡ.
  - **Lược bỏ các chi tiết thô thiển**:
    - Bỏ các ký tự dollar bay tán loạn trong hộp mừng cưới, thay bằng Hộp quà / Phong bao cưới nhung đen viền vàng sang trọng, mở modal VietQR quét mã tức thì.
    - Bộ đếm ngược, timeline và tabs tiệc cưới được tinh giản, gọn gàng, sắc nét.
- **Đồng bộ toàn diện Assets**:
  - Đã cập nhật và đồng bộ đồng thời tại `resources/views/client/invitations/net-muc-tinh-yeu/` và `public/assets/client/invitations/net-muc-tinh-yeu/`.
- **Kiểm thử tự động**:
  - `php artisan test --compact`: **10/10 tests passed (101 assertions)**.
  - Laravel Pint: **100% passed**, không có lỗi format.

### 📌 Phiên bản 1.1.4 — Hệ Thống Sổ Lưu Bút & Ký Tên Chúc Phúc Độc Lập Theo Từng Đám Cưới (Database-Backed Guestbook Engine)
- **Kiến trúc Backend chuẩn hóa dùng chung cho mọi template**:
  - **Bảng Database `wedding_wishes`**:
    - Trường dữ liệu: `id`, `wedding_id` (foreignId cascade on delete), `name` (tên người chúc), `message` (lời chúc), `is_approved` (kiểm duyệt hiển thị), `ip_address`, `created_at`, `updated_at`.
    - Đảm bảo mỗi đám cưới sở hữu toàn vẹn một cơ sở dữ liệu sổ lưu bút riêng biệt gắn với `wedding_id`.
  - **Model & Relationships**:
    - Model `App\Models\WeddingWish` liên kết `BelongsTo(Wedding::class)`.
    - Model `App\Models\Wedding` bổ sung quan hệ `hasMany(WeddingWish::class)->latest()`.
  - **API Endpoints thống nhất**:
    - `POST /thiep/{slug}/wishes`: Nhận `name` và `message`, validate qua `SubmitWishRequest`, lưu vào database của đám cưới và trả về JSON kèm thời gian `diffForHumans()`.
    - `GET /thiep/{slug}/wishes`: Trả về danh sách lời chúc công khai dạng JSON.
    - `POST /xem-thu/{slug}/wishes`: Endpoint thử nghiệm cho chế độ preview/editor studio, phản hồi JSON thành công ngay lập tức giúp kiểm thử UI mà không phát sinh rác DB.
    - Đồng thời, khi khách gửi RSVP (`POST /thiep/{slug}/rsvp`) có điền lời chúc, hệ thống tự động đồng bộ sang `wedding_wishes` để hiển thị trên sổ lưu bút.
- **Tích hợp giao diện & tương tác Frontend (mẫu "Nét Mực Tình Yêu" và mọi template)**:
  - Khi render template, controller tự động nạp `$wishes` từ database và URL endpoint `$wishSubmitUrl`.
  - Khung Sổ lưu bút hiển thị danh sách lời chúc thực tế từ database với tên người gửi, thời gian tương đối ("Vừa xong", "2 giờ trước"), nội dung lời chúc định dạng trang nhã.
  - Bộ đếm trên bảng lưu bút (`#mayhen_guestboard_counter`) tự động hiển thị số lượng lời chúc hiện có ("Đã có X lời chúc phúc gửi tới đôi bạn").
  - Khi khách bấm *"Ký tên chúc phúc"* và gửi form:
    - JavaScript gửi AJAX `fetch` lên endpoint backend thật kèm CSRF token.
    - Nút bấm hiển thị trạng thái "Đang gửi...".
    - Khi thành công: Tự động chèn thẻ lời chúc mới lên đầu danh sách (`container.prepend`), cập nhật bộ đếm lời chúc và đóng modal mượt mà mà không cần reload trang.
- **Kiểm thử tự động**:
  - Đã bổ sung test case Feature `each wedding has its own independent guestbook database with wish submissions and isolation` kiểm tra trọn vẹn:
    1. Gửi lời chúc vào thiệp cưới A thành công.
    2. Gửi lời chúc vào thiệp cưới B thành công.
    3. Đảm bảo tính độc lập (Isolation) 100%: thiệp A chỉ thấy lời chúc của A, tuyệt đối không thấy lời chúc của B.
    4. Gửi lời chúc thử nghiệm trên preview mode.
  - Kết quả kiểm thử: **11/11 tests passed (117 assertions)**.
  - Laravel Pint: **100% passed**, sạch sẽ không có lỗi format.

### 📌 Phiên bản 1.1.5 — Chuẩn Hóa Hệ Thống Xác Nhận Tham Dự (RSVP Có Đi / Không Đi) Độc Lập Cho Mọi Template
- **Kiến trúc Backend chuẩn hóa**:
  - **Bảng Database `guest_responses`**:
    - Bổ sung cột `phone` (string 30, nullable) để lưu số điện thoại liên lạc của khách mời xác nhận.
    - Trường dữ liệu đầy đủ: `id`, `wedding_id` (foreignId cascade on delete), `guest_id` (nullable), `guest_name`, `phone`, `attending_status` (`attending` hoặc `declined`), `adults_count`, `children_count`, `dietary_requirements`, `wishes`, `ip_address`, `created_at`.
    - Bảo đảm 100% tính độc lập (Isolation): Mỗi đám cưới có cơ sở dữ liệu khách mời và phản hồi RSVP riêng biệt gắn với `wedding_id`.
  - **Validation & Request**:
    - `SubmitRsvpRequest` validate: `guest_name` (required, max 255), `phone` (nullable, max 30), `attending_status` (required, in: attending, declined, uncertain), `adults_count`, `wishes`.
  - **API Endpoints thống nhất**:
    - `POST /thiep/{slug}/rsvp`: Nhận phản hồi RSVP thực tế, lưu vào `guest_responses`. Nếu khách chọn không đi và để lại lời nhắn chúc phúc, hệ thống tự động đồng bộ bản ghi sang `wedding_wishes` để hiển thị trên sổ lưu bút.
    - `POST /xem-thu/{slug}/rsvp`: Endpoint giả lập cho chế độ xem thử (Preview / Live Editor), phản hồi thành công JSON ngay lập tức giúp kiểm thử UX/UI mượt mà.
- **Tích hợp giao diện & tương tác Frontend (mẫu "Nét Mực Tình Yêu" và mọi template)**:
  - Khung "Xác Nhận Tham Dự" cung cấp 2 lựa chọn trực quan:
    1. **"Có, tôi sẽ tham dự"**: Mở modal nhập Họ tên, Số điện thoại và Số lượng người tham dự (`guests`).
    2. **"Rất tiếc, chưa sắp xếp được"**: Mở modal nhập Họ tên, Số điện thoại và Lời nhắn gửi tới cặp đôi.
  - Khi submit: JavaScript gửi AJAX `fetch` lên endpoint backend thật với CSRF token, nút bấm hiển thị trạng thái *"Đang ghi nhận..."*.
  - Khi hoàn tất: Đóng modal, hiển thị thông báo phản hồi trân trọng từ server và cập nhật trạng thái đã chọn trên nút bấm.
  - Đồng bộ mã nguồn đồng thời tại `resources/views/client/invitations/net-muc-tinh-yeu/` và `public/assets/client/invitations/net-muc-tinh-yeu/`.
- **Kiểm thử tự động**:
  - Đã bổ sung test case Feature `each wedding has independent rsvp responses for attending and declining` kiểm tra trọn vẹn:
    1. Khách A xác nhận CÓ THAM DỰ vào đám cưới A với số lượng 2 người và số điện thoại.
    2. Khách B xác nhận KHÔNG THAM DỰ vào đám cưới B kèm lời nhắn chúc phúc.
    3. Đảm bảo tính độc lập dữ liệu 100%: RSVP của đám cưới nào nằm đúng trong đám cưới đó, không có sự can thiệp chéo.
    4. Lời nhắn từ RSVP từ chối tham dự tự động đồng bộ vào sổ lưu bút `wedding_wishes` của đám cưới B, không lọt sang đám cưới A.
    5. Test endpoint demo RSVP trên preview mode.
  - Kết quả kiểm thử: **12/12 tests passed (135 assertions)**.
  - Laravel Pint: **100% passed**, không có bất kỳ lỗi định dạng nào.

### 📌 Phiên bản 1.1.6 — Giới Hạn Xác Nhận Tham Dự 1 Lần & Chỉnh Sửa Lời Chúc Theo IP Cho Từng Đám Cưới
- **Chính sách giới hạn & bảo vệ dữ liệu theo địa chỉ IP**:
  - **Khóa xác nhận tham dự (RSVP Lock)**:
    - Mỗi địa chỉ IP chỉ được xác nhận tham dự (Có đi hoặc Không thể tham dự) **duy nhất 1 lần** cho mỗi đám cưới (`wedding_id` + `ip_address`).
    - Sau khi đã chọn, không được phép chọn lại. Nút phản hồi bị vô hiệu hóa (`disabled`), đánh dấu trạng thái đã chọn (`mayhen_invitation_dhsuudushi9428r_rsvp_selected`) và hiển thị thông báo ghi nhận.
    - Phía Backend (`GuestController::submitRsvp`): Kiểm tra nếu IP đã tồn tại trong `GuestResponse` của đám cưới đó, lập tức trả về lỗi HTTP 422 JSON ngăn chặn mọi hành vi gửi lại.
  - **Chế độ Chỉnh sửa lưu bút (Wish Edit Mode)**:
    - Mỗi địa chỉ IP chỉ được phép có **1 lời chúc duy nhất** trong mỗi đám cưới (`wedding_id` + `ip_address`).
    - Nếu người dùng đã gửi lời chúc trước đó:
      - Nút lưu bút chuyển thành *"Chỉnh sửa lời chúc của bạn"*.
      - Thẻ lời chúc của người đó trên danh sách sổ lưu bút được gắn huy hiệu *"Lời chúc của bạn"*.
      - Khi mở modal, hệ thống tự động điền sẵn Họ tên và Lời chúc cũ để người dùng chỉnh sửa.
      - Phía Backend (`GuestController::submitWish`): Thực hiện cập nhật (`update`) bản ghi cũ, trả về `is_update: true`. Không tạo thêm bản ghi mới, tổng số lượng comment lưu bút trong cơ sở dữ liệu không bị tăng lên.
      - Phía Frontend (`script.js`): Cập nhật trực tiếp nội dung thẻ chúc trên DOM (`(Vừa cập nhật)`), không chèn thêm thẻ mới và không tăng bộ đếm số lượng lời chúc.
- **Cập nhật Controllers & Views**:
  - `InvitationEditorController::show`: Truy vấn `$currentRsvp` và `$myWish` theo `request()->ip()` và truyền trực tiếp vào view thiệp cưới.
  - `template.blade.php`, `styles.css`, `script.js` (đồng bộ cả `resources/views/...` và `public/assets/...`): Xử lý trạng thái selected/disabled của RSVP và form edit của lưu bút.
- **Kiểm thử tự động**:
  - Bổ sung 2 test cases Feature trong `tests/Feature/NetMucTinhYeuTemplateTest.php`:
    1. `user cannot rsvp more than once from the same ip for the same wedding`: Kiểm tra gửi lần 1 (200), gửi lần 2 cùng IP bị chặn 422, gửi từ IP khác vẫn hợp lệ.
    2. `user can only edit their existing wish and cannot submit multiple comments from the same ip`: Kiểm tra gửi lần 1 (201), gửi lần 2 cùng IP chỉ cập nhật nội dung (200) và số lượng bản ghi DB không tăng, hiển thị đúng giao diện chỉnh sửa.
  - Kết quả kiểm thử: **14/14 tests passed (160 assertions)**.
  - Laravel Pint: **100% passed**.

### 📌 Phiên bản 1.1.7 — Cơ Chế Phân Quyền Chủ Nhân Thiệp Cưới (Session & Auth), Trả Lời Lưu Bút & Quản Lý RSVP
- **Nhận diện Chủ Nhân Thiệp Cưới (Owner Detection)**:
  - **Tạo thiệp khi chưa đăng nhập**: Cho phép khách vãng lai tạo thiệp trải nghiệm, slug thiệp được lưu vào session `owned_invitation_slugs`. Người này được hệ thống công nhận là Chủ nhân của thiệp cưới thông qua session.
  - **Tạo thiệp khi đã đăng nhập**: Thiệp cưới gắn trực tiếp với `user_id` của tài khoản đó.
  - **Đồng bộ tự động khi đăng nhập/đăng ký**: Khi người dùng đăng nhập hoặc đăng ký tài khoản mới, hệ thống tự động quét session `owned_invitation_slugs` và chuyển quyền sở hữu toàn bộ các thiệp đã tạo sang `user_id` của tài khoản đó (`AuthController::syncOwnedInvitations`).
  - **Helper method `Invitation::isOwner()`**: Xác thực chủ quyền dựa trên `auth()->id() === wedding->user_id` HOẶC `in_array(slug, session('owned_invitation_slugs'))`.
- **Giới hạn & Đặc quyền của Chủ Nhân (Owner Permissions & Restrictions)**:
  - **KHÔNG được phép**:
    + Không được tự gửi xác nhận tham dự cho chính mình (chặn HTTP 403 cả Backend lẫn Frontend).
    + Không được tự gửi lời chúc mới trong sổ lưu bút (chặn HTTP 403 cả Backend lẫn Frontend).
  - **ĐƯỢC PHÉP**:
    + **Trả lời lưu bút**:
      - Thêm migration cột `reply` (text, nullable) và `replied_at` (timestamp, nullable) vào bảng `wedding_wishes`.
      - API `POST /thiep/{slug}/wishes/{wishId}/reply`: Chủ nhân gửi câu trả lời lời chúc của từng khách mời; giao diện thiệp hiển thị khung trang trọng *"♥ Phản hồi từ Cô Dâu & Chú Rể"*.
      - Người lạ không phải chủ nhân gọi API reply sẽ bị chặn HTTP 403.
    + **Xem danh sách và thống kê khách phản hồi tham dự (RSVP Dashboard)**:
      - API `GET /thiep/{slug}/rsvp/responses`: Chủ nhân xem danh sách chi tiết (Tên, SĐT, Trạng thái tham dự/không tham dự, Số khách đi cùng, Lời nhắn, Thời gian). Người lạ truy cập bị chặn HTTP 403.
      - Giao diện thiệp thay thế 2 nút chọn RSVP thành Bảng điều khiển Quản lý khách mời kèm nút xem bảng danh sách modal chi tiết.
- **Kiểm thử tự động & Định dạng**:
  - Bổ sung test case Feature toàn diện trong `tests/Feature/NetMucTinhYeuTemplateTest.php`: Kiểm tra tạo thiệp bằng session, chặn chủ thiệp gửi RSVP & Wish mới, cho phép chủ thiệp trả lời lưu bút và xem danh sách RSVP, chặn người lạ can thiệp, và kiểm tra đồng bộ quyền sở hữu khi đăng nhập.
  - Kết quả kiểm thử: **15/15 tests passed (186 assertions)**.
  - Laravel Pint: **100% passed**.

### 📌 Phiên bản 1.1.8 — Hệ Thống Kiểm Duyệt Chặn Từ Ngữ Thô Tục, Chửi Bới & Trù Ẻo Đám Cưới (Zero Trust Moderation FE & BE)
- **Triết lý bảo mật**:
  - *"Không bao giờ tin tưởng người dùng (Zero-Trust User Input)"*: Kẻ xấu có thể cố tình phá đám cưới, để lại lời lẽ khiếm nhã, thô tục, chửi thề hoặc trù ẻo đôi bạn trẻ trên trang thiệp cưới công khai.
  - Bắt buộc kiểm soát và chặn sạch sẽ ở cả **Frontend (FE)** (chặn trước trải nghiệm người dùng, cảnh báo văn minh) và **Backend (BE)** (chốt chặn validation cuối cùng, chặn mọi bypass fetch/curl/Postman).
- **Backend Content Moderation Engine (`App\Services\ContentModerationService`)**:
  - **Từ điển tiếng Việt chuyên sâu**:
    + Nhóm 1: Tục tĩu, bộ phận sinh dục, hành vi tình dục thô bỉ (`buồi`, `cặc`, `lồn`, `địt`, `đụ`, `chịch`, `nện`, `sóc lọ`, `thủ dâm`, `hiếp dâm`,...).
    + Nhóm 2: Chửi thề, tiếng lóng mạng xã hội và Gen Z (`đm`, `dkm`, `vcl`, `vl`, `clgt`, `cmnr`, `đéo`, `đếch`, `méo`,...).
    + Nhóm 3: Xúc phạm cha mẹ, tổ tiên, danh dự gia đình (`mẹ mày`, `bố mày`, `bà già mày`, `tổ cha`, `mả mẹ`, `tiên sư`,...).
    + Nhóm 4: Miệt thị phụ nữ, nghề nghiệp xúc phạm (`đĩ`, `con đĩ`, `thằng đĩ`, `đĩ chó`, `phò`, `cave`, `bán dâm`,...).
    + Nhóm 5: Chửi thú vật, miệt thị trí tuệ (`chó đẻ`, `chó chết`, `đồ chó`, `thằng chó`, `súc vật`, `đồ lợn`, `óc chó`, `đần độn`, `đồ điên`, `đồ khùng`, `thiểu năng`,...).
    + Nhóm 6: Trù ẻo đám cưới, tang tóc, chia rẽ hạnh phúc (`chết mẹ`, `đoản mệnh`, `tai nạn`, `đám ma`, `tang gia`, `quan tài`, `bát hương`, `ly dị`, `ly hôn`, `đứt gánh`, `cắm sừng`, `mọc sừng`, `tan vỡ`, `bỏ nhau`, `ngoại tình`, `tiểu tam`, `con giáp thứ 13`, `vô sinh`, `tuyệt tự`,...).
  - **Thuật toán đa tầng chống lách chữ**:
    + *Word Boundary Check*: Bắt chính xác từ khóa trong câu có dấu và không dấu (ASCII Normalization) mà không bắt nhầm các từ ghép tiếng Việt lành tính (ví dụ: loại bỏ các từ đồng âm ngắn đơn lẻ gây false positive như `dien` trong `giao diện`, `cho` trong `chúc cho`, `di` trong `đi ăn cưới`).
    + *Leetspeak Normalization*: Chuyển đổi các ký tự số thay thế chữ cái (`0` -> `o`, `1` -> `i`, `3` -> `e`, `4` -> `a`, `7` -> `t`, `@` -> `a`).
    + *Separated Character Patterns*: Bắt các dạng cố tình ngắt chữ bằng dấu chấm, dấu cách, dấu gạch ngang, sao (`d.m`, `d m`, `c.a.c`, `c a c`, `l.o.n`, `l o n`, `d.i.t`, `v.c.l`, `v l`,...).
- **Custom Validation Rule Laravel (`App\Rules\NoProfanity`)**:
  - Sử dụng `ContentModerationService::containsProfanity()`.
  - Thông báo lỗi rõ ràng, nhân văn: *"Nội dung chứa từ ngữ không phù hợp hoặc thiếu văn minh. Vui lòng sử dụng ngôn từ lịch sự, trang trọng để gửi lời chúc phúc."*
  - Tích hợp vào:
    + `SubmitWishRequest`: Kiểm duyệt cả ô `name` và `message`.
    + `SubmitRsvpRequest`: Kiểm duyệt cả ô `guest_name`, `wishes` và `dietary_requirements`.
    + `GuestController::replyWish`: Kiểm duyệt ô `reply` khi chủ thiệp gửi phản hồi.
- **Frontend Moderation (`script.js`)**:
  - Nhúng bộ lọc `checkProfanity()` tương đồng với Backend vào file JavaScript của template thiệp cưới (cả `resources/views/...` và `public/assets/...`).
  - Kiểm tra tức thì khi người dùng submit:
    + Form Sổ lưu bút (Tên & Lời chúc phúc).
    + Form Xác nhận tham dự RSVP (Họ tên & Lời nhắn gửi cô dâu chú rể).
    + Form Phản hồi lời chúc của chủ thiệp.
  - Khi phát hiện vi phạm: Ngừng gửi request ngay lập tức (`preventDefault()`, không gọi API fetch), bật thông báo cảnh báo yêu cầu dùng ngôn từ lịch sự, trang trọng.
- **Kiểm thử tự động & Định dạng**:
  - Bổ sung 3 test cases Feature trong `tests/Feature/NetMucTinhYeuTemplateTest.php`:
    1. Chặn từ ngữ chửi bậy, lách chữ, xúc phạm và trù ẻo đám cưới trong sổ lưu bút (HTTP 422 cho tên/lời chúc vi phạm, HTTP 201 cho lời chúc văn minh).
    2. Chặn từ ngữ thô tục, chửi bới trong form xác nhận tham dự RSVP (HTTP 422).
    3. Chặn từ ngữ không phù hợp trong phản hồi của chủ thiệp cưới (HTTP 422).
  - Kết quả kiểm thử: **18/18 tests passed (213 assertions)**.
  - Laravel Pint: **100% formatted & passed**.

### 📌 Phiên bản 1.1.9 — Chuẩn Hóa Tỷ Lệ Ảnh Bìa 3/4 & Tự Động Cuộn Về Đỉnh Khi Nạp Trang (net-muc-tinh-yeu)
- **Chuẩn hóa tỷ lệ ảnh cưới mở đầu 3/4 (`aspect-ratio: 3 / 4`)**:
  - Khắc phục triệt để tình trạng ảnh cưới nằm ngang bị méo mó, co ép hoặc sai lệch tỉ lệ dọc chuẩn studio cưới:
    + Thẻ phong bì `.mayhen_invitation_dhsuudushi9428r_envelope_stage` được mở rộng kích thước lên `width: min(320px, 86vw); height: 225px;`.
    + Tấm thiệp bên trong `.mayhen_invitation_dhsuudushi9428r_envelope_letter` được tăng chiều cao lên `height: 250px;` với cấu trúc flex dọc gọn gàng.
    + Ảnh thiệp `.mayhen_invitation_dhsuudushi9428r_envelope_letter_img` được thiết lập chính xác `aspect-ratio: 3 / 4; max-width: 160px; height: auto; object-fit: cover;`, hiển thị trọn vẹn vẻ đẹp khung hình chân dung của cô dâu chú rể.
    + Khi mở thiệp (`.mayhen_opened`), tấm thiệp trồi lên cao hơn (`transform: translateY(-135px) scale(1.02);`), hé lộ đầy đủ 100% ảnh cưới 3/4 sắc nét mà không bị che khuất bởi nắp và mép phong bì.
- **Tự động cuộn về đỉnh trang khi tải lại (Auto Scroll To Top on Reload)**:
  - Khắc phục lỗi khi người dùng đang ở cuối trang bấm F5 / Reload trang bị trình duyệt giữ vị trí cuộn ở dưới, làm người xem bỏ lỡ trải nghiệm chạm mở phong thư bắt buộc.
  - Vô hiệu hóa tính năng nhớ vị trí cuộn mặc định của trình duyệt: `history.scrollRestoration = 'manual'`.
  - Thực thi `window.scrollTo(0, 0)` ngay lập tức khi file script thực thi, kết hợp lắng nghe cả sự kiện `DOMContentLoaded` và `load` để đảm bảo trang luôn bắt đầu từ màn hình phong thư trang trọng.
- **Đồng bộ hóa 100% mã nguồn**:
  - Đã cập nhật song song tại `resources/views/client/invitations/net-muc-tinh-yeu/` và `public/assets/client/invitations/net-muc-tinh-yeu/` (`styles.css` & `script.js`).

### 📌 Phiên bản 1.2.0 — Đóng Gói Toàn Diện Assets Vào Thư Mục Template & Xóa Bỏ Public Invitations
- **Quy chuẩn lưu trữ mẫu thiệp độc lập (Self-Contained Invitation Template Packages)**:
  - Toàn bộ tài nguyên của mẫu thiệp (gồm `styles.css`, `script.js`, `schema.json`, `template.blade.php`, thư mục `assets/` chứa ảnh, font chữ, file nhạc MP3) được đặt trực tiếp và duy nhất tại:
    `resources/views/client/invitations/{template_slug}/`.
  - **Đã xóa bỏ hoàn toàn thư mục `public/assets/client/invitations`** để loại bỏ tình trạng phân mảnh, dễ kiểm soát và không cần bảo trì 2 bản sao cùng lúc.
- **Cơ chế phục vụ Static Assets Trực Tiếp từ Resources**:
  - Bổ sung route: `GET /mau-thiep/{slug}/assets/{path}` (đặt tên `templates.asset`, bắt mọi path con `.*`).
  - Xử lý qua controller `TemplateController::asset()`:
    + Bảo mật: Khử sạch `..` và ký tự null byte để chống tấn công Directory Traversal.
    + Nhận diện và thiết lập đúng chuẩn MIME Type: CSS (`text/css; charset=UTF-8`), JS (`application/javascript; charset=UTF-8`), JPEG/PNG/WebP/SVG, MP3 (`audio/mpeg`), WOFF/WOFF2/OTF/TTF.
    + Bổ sung header HTTP Cache tối ưu: `Cache-Control: public, max-age=31536000`.
- **Nâng cấp Tính năng Tải Ảnh Lên (`InvitationEditorController::uploadAsset`)**:
  - File tải lên từ thiết bị được lưu trực tiếp vào: `resources/views/client/invitations/{templateSlug}/assets/{filename}`.
  - Trả về đường dẫn truy cập qua route nội bộ: `/mau-thiep/{templateSlug}/assets/assets/{filename}`.
- **Cập nhật Template Views & Live Editor**:
  - `template.blade.php`: Biến `$assetBase` trỏ trực tiếp tới `url('/mau-thiep/net-muc-tinh-yeu/assets')`.
  - `editor.blade.php`: Toàn bộ các thẻ preview ảnh của mẫu thiệp được nạp mượt mà qua route URL nội bộ.
  - `InvitationTemplateSeeder.php`: `thumbnail_url` được cập nhật đồng bộ.

### 📌 Phiên bản 1.2.1 — Khắc Phục Triệt Để Lưu Ảnh & Dữ Liệu Khi Xuất Bản Thiệp Cưới
- **Khắc phục lỗi nạp ảnh và dữ liệu khi F5 / Reload trang thiệp cưới (`/thiep/{slug}`)**:
  - **Lỗi 1 (Đường dẫn Relative trong Database)**: Các trường `hero_photo_url`, `qr_groom_image`, `qr_bride_image`, `footer_photo_url` được lưu đường dẫn tương đối dạng `assets/...`. Khi render ở trang thiệp thật, trình duyệt tự nối `/thiep/assets/...` dẫn tới lỗi 404 toàn bộ ảnh.
    -> Giải pháp: Bổ sung helper closure `$resolveAsset` trong `template.blade.php`, tự động bổ sung prefix `$assetBase` (`/mau-thiep/{slug}/assets/...`) đối với đường dẫn relative, đồng thời giữ nguyên nếu là URL tuyệt đối (`http://`, `https://`, `/`).
  - **Lỗi 2 (Rò rỉ URL tạm thời `blob:` vào Database)**: Khi cắt ảnh 3/4 ở modal cropper, trình duyệt tạo URL `blob:http://...`. Do file `editor.js` tĩnh không compile cú pháp Blade `{{ $selectedTemplate->slug }}` và bị ReferenceError biến `csrfToken`/`submitTriggers`, form bị submit sớm dẫn đến lưu thẳng chuỗi `blob:...` vào database. Khi người dùng reload trang, `blob:` bị trình duyệt giải phóng khỏi RAM gây icon ảnh vỡ.
    -> Giải pháp: 
      + Truyền biến toàn cục `window.__MAYHEN_TEMPLATE_SLUG`, `window.__MAYHEN_CSRF_TOKEN`, `window.__MAYHEN_UPLOAD_URL` từ `editor.blade.php`.
      + Gỡ bỏ việc bind sự kiện submit trùng lặp sớm trong `editor.js`.
      + Tái cấu trúc hàm `submitFormWithPendingUploads` sử dụng `Promise.all` tải toàn bộ ảnh blob lên server `/tao-thiep/upload-asset`, nhận link thật từ server gán vào form trước khi gọi `mainForm.submit()`.
      + Bổ sung cơ chế bảo vệ kép (cả Client & Backend): Xóa sạch mọi chuỗi bắt đầu bằng `blob:` trước khi gửi và trước khi lưu vào Model `Wedding`.
  - **Lỗi 3 (Dữ liệu Album ảnh cưới Null)**: Mảng `$wedding->extra_data['gallery']` chứa 10 phần tử `null` khiến điều kiện `!empty()` trong Blade bị hiểu là có dữ liệu.
    -> Giải pháp: Dùng `array_filter` lọc bỏ giá trị rỗng/null và link `blob:`, fallback tự động về `$defaultAlbum` của mẫu thiệp.
  - **Lỗi 4 (Giao diện tên Cô dâu Chú rể bị rớt chữ)**: Thêm `white-space: nowrap` và tinh chỉnh `clamp(15px, 4.2vw, 22px)` cho `.mayhen_invitation_dhsuudushi9428r_cover_names p` trong `styles.css`.
  - **Lỗi 5 (RouteNotFoundException)**: Khắc phục tên route `invitation.uploadAsset` (camelCase) chuẩn xác trong `editor.blade.php`.
- **Cập nhật Bố Cục Tên Đôi Uyên Ương Phần Mở Đầu Thiệp (`net-muc-tinh-yeu`)**:
  - Chuyển từ dạng ngang 1 hàng sang bố cục dọc 3 tầng sang trọng: Tên Chú Rể (trên) -> Trái tim vàng `❤` (giữa) -> Tên Cô Dâu (dưới).
- **Nâng cấp Hiệu Ứng Phong Bì 3D Mở Thiệp (`net-muc-tinh-yeu`)**:
  - Bỏ giới hạn `max-width` ở ảnh thiệp chính (`envelope_letter_img`), mở rộng chiều ngang ảnh chiếm trọn 100% bề ngang lá thư, to ngang gần bằng phong bì nhìn sang trọng và nổi bật.
  - Tích hợp **Animation chuyển đổi khoảng cách động (Dynamic Margin Animation)**: Khi tải trang, phong bì giữ khoảng cách gọn gàng `margin: 18px auto`. Khi bấm mở thiệp, phong bì tự động trượt êm ái xuống `margin: 160px auto 18px auto` (`transition: margin 0.9s cubic-bezier(...)`), tạo khoảng không gian thoáng đãng cho bức ảnh cưới to vươn lên mà không che mất tên cô dâu chú rể.
  - Thiết lập chiều sâu 3D chân thực: giữ nguyên `z-index: 2` dưới lớp túi mặt trước phong bì `envelope_pocket` (`z-index: 3`) kèm đổ bóng drop-shadow tự nhiên, giữ khoảng 20% phần đáy của lá thư và ảnh cưới nằm cắm sâu bên trong lòng phong bì, 80% phần trên vươn lên khoe trọn vẹn ảnh cưới.
- **Chuẩn Hóa Tiêu Đề Trang Thiệp Cưới Đã Tạo (`/thiep/{slug}`)**:
  - Định dạng chuẩn: `Thiệp mời cưới: "Tên Chú Rể & Tên Cô Dâu" | Mây Hẹn` (Ví dụ: `Thiệp mời cưới: "Nguyễn Minh Đức & Nguyễn Thị Huyền" | Mây Hẹn`).
  - Áp dụng đồng bộ cho cả `template.blade.php` và `public-invitation.blade.php` (kèm Open Graph meta tags cho mạng xã hội).

- **Đồng Bộ & Chuẩn Hóa Favicon Cho Tất Cả Các Trang (100% Coverage)**:
  - Tách cụm thẻ khai báo Favicon thành component dùng chung: `resources/views/client/components/favicon.blade.php`.
  - Nguồn asset chính thức: `storage/client/images/business/mayhen-favicon.png`.
  - Cụm thẻ chuẩn gồm đầy đủ: `apple-touch-icon (180x180)`, `icon (32x32)`, `icon (16x16)`, `mask-icon`, `icon (x-icon)` và `meta theme-color (#C5A880)`.
  - Tích hợp `@include('client.components.favicon')` vào toàn bộ các layout và trang độc lập trong hệ thống:
    + `resources/views/client/components/head.blade.php` (áp dụng cho toàn bộ các trang Client: Home, Editor, Pricing, Templates, Services, About, FAQ, Auth, Dashboard, Guests...).
    + `resources/views/client/pages/public-invitation.blade.php` (Trang xem thiệp cưới công khai cho khách mời).
    + `resources/views/client/pages/preview.blade.php` (Trang xem thử mẫu thiệp).
    + `resources/views/client/invitations/net-muc-tinh-yeu/template.blade.php` (Trang template thiệp cưới độc lập).
    + `resources/views/admin/layouts/master.blade.php` (Trang quản trị Admin Dashboard).
    + `resources/views/welcome.blade.php`.

- **Tái Thiết Kế Menu Tài Khoản & Nút Đăng Nhập Header (Quiet Luxury & Tối Ưu Diện Tích)**:
  - **Khắc phục giao diện cũ**: Loại bỏ hiển thị text dài `Tên... Thoát` cồng kềnh, thô và chiếm nhiều diện tích trên thanh header.
  - **Nút Đăng Nhập (`.mayhen_client_header_login_btn`)**:
    + Tinh chỉnh nhỏ gọn (cao 32px, font 0.775rem), màu sắc trung tính nhẹ nhàng (`#6E685F`), không nền nổi bật, viền mờ tối giản.
    + Đảm bảo đóng vai trò thứ cấp, không cạnh tranh thị giác với nút CTA chính **"Tạo Thiệp Ngay"**.
  - **User Dropdown Menu Khi Đăng Nhập (`.mayhen_client_header_user_*`)**:
    + Thiết kế Pill/Trigger nhỏ gọn (cao 32px), gồm avatar tròn chữ cái đầu (Initials) tinh tế + tên riêng ngắn gọn + mũi tên chevron xoay động.
    + Popup Dropdown card sang trọng: hiển thị tên đầy đủ, email/SĐT, nhãn Quản trị viên, các liên kết chức năng (Trang Quản Trị, Tạo Mẫu Thiệp Mới) và nút Đăng Xuất.
    + Tương tác Native JavaScript: click toggle mở/đóng, tự động đóng khi click outside hoặc ấn phím Escape.
    + Đồng bộ tối ưu giao diện Profile Card bên trong Mobile Drawer.

- **Tích Hợp Thành Công Mẫu Thiệp Độc Bản "Thước Phim Tình Yêu" (`thuoc-phim-tinh-yeu`)**:
  - **Tên mẫu**: Thước Phim Tình Yêu (MAYHEN_Invitation_djcvhwiuo9w9_Graceful04).
  - **Slug**: `thuoc-phim-tinh-yeu` (Category: `modern`, Tier: `pro`, Complexity: `deluxe`).
  - **Prefix CSS độc quyền**: `mayhen_invitation_djcvhwiuo9w9_*` (Chống xung đột phong cách 100%).
  - **Cấu trúc lưu trữ**: `resources/views/client/invitations/thuoc-phim-tinh-yeu/`
    + `template.blade.php`: Hỗ trợ đầy đủ dữ liệu động (Cô dâu chú rể, gia đình 2 bên, ngày cưới, sảnh tiệc, album 10 ảnh, triptych, quote, timeline, QR mừng cưới, nhạc nền), tích hợp Favicon, Topbar xem thử và Topbar chủ thiệp.
    + `styles.css`: Hệ thống CSS độc lập (kèm style cho preview topbar và music toggle).
    + `script.js`: Native JS kết nối API backend Mây Hẹn (Đếm ngược ngày cưới động, Lightbox album ảnh, Sổ lưu bút canvas ký tên tay cảm ứng, Gửi lời chúc & RSVP trực tuyến lên DB, Modal hộp mừng cưới QR, Trình phát nhạc nền đĩa than).
    + `schema.json`: Cấu hình toàn bộ trường dữ liệu phục vụ trình tạo thiệp.
    + `assets/`: Lưu trữ cục bộ 100% toàn bộ 51 file assets (texture giấy, rèm mở đầu, dải film, icon timeline, ảnh mẫu cưới, nhạc nền MP3) không phụ thuộc CDN ngoại.
  - **Tối ưu hóa Phục vụ Asset (`TemplateController::asset`)**: Bổ sung cơ chế fallback tự động tìm kiếm trong thư mục `assets/` con, cho phép nạp asset mượt mà và an toàn.
  - **Database Seeder**: Cập nhật `InvitationTemplateSeeder.php` và seed thành công vào CSDL.

- **Nâng Cấp Giao Diện & Hiệu Ứng Mở Rèm 2 Cánh Mẫu Thiệp "Thước Phim Tình Yêu" (`thuoc-phim-tinh-yeu`)**:
  - **Cơ chế Mở Rèm 2 Cánh Tương Tác (`opening-sides`)**:
    + Chuyển đổi lớp 2 cánh rèm (`opening-left` & `opening-right`) thành overlay full-screen cố định (`z-index: 1000`), khớp nối chính xác tại trung tâm màn hình.
    + Bắt buộc người dùng bấm vào **cánh trái**, **cánh phải**, hoặc **con dấu sáp hoàng gia ở chính giữa** thì animation mở cửa thiệp sang 2 bên mới được kích hoạt (`translate3d(-101%, 0, 0)` và `translate3d(101%, 0, 0)` trong 1.25s).
    + Con dấu sáp hoàng gia (`.mayhen_invitation_djcvhwiuo9w9_opening-seal`): Viền vàng champagne ánh kim, dập nổi chữ Monogram đôi uyên ương và dòng chữ "MỞ THIỆP" cùng hiệu ứng sóng lan toả (ripple pulse) mời gọi chạm mở.
    + Sau 1.3s khi kết thúc animation, overlay tự động ẩn hoàn toàn (`visibility: hidden; pointer-events: none;`) để giải phóng tương tác cho thiệp bên dưới.
    + Xóa bỏ triệt để quy tắc `display: none` trên màn hình di động, mang lại trải nghiệm mở rèm thiệp đôi đỉnh cao trên mọi thiết bị (Mobile-First).
    + **Cơ chế Khóa Cuộn & Tự Động Về Đầu Trang**:
      * Khi load trang, hệ thống tự động thiết lập `history.scrollRestoration = 'manual'` và đưa vị trí trang về đỉnh (`window.scrollTo(0, 0)`).
      * Toàn bộ trang bị khóa cuộn (`overflow: hidden; touch-action: none; overscroll-behavior: none;`) để người dùng không thể cuộn xem nội dung khi chưa mở rèm.
      * Ngay khi người dùng chạm mở cánh trái hoặc cánh phải, hiệu ứng mở rèm kích hoạt và hệ thống tự động gỡ khóa cuộn, cho phép người dùng cuộn xem toàn bộ thiệp cưới mượt mà.
  - **Nâng Cấp Thẩm Mỹ Toàn Diện Giao Diện Thiệp (Chuẩn Quiet Luxury)**:
    + **Monogram Cặp Đôi**: Bổ sung huy hiệu tròn viền kép mạ vàng champagne mờ, chữ lồng nhau nghệ thuật và đổ bóng giấy mỹ thuật sang trọng.
    + **Bộ Đếm Ngược Ngày Cưới**: Bổ sung nhãn phụ Ngày, Giờ, Phút, Giây bằng font Sans thanh mảnh, căn giữa các ô số có nền kính mờ tinh xảo.
    + **Phong Bì Ảnh 3D & Lồng Ảnh**: Đổ bóng nhiều tầng chân thật, hiệu ứng hover xoay vươn ảnh sống động.
    + **Tab Chuyển Đổi Sảnh Tiệc Nhà Trai / Nhà Gái**: Bo góc tròn dạng viên ngọc (pill), viền vàng champagne ấm áp, gradient mềm mại. Khi bấm chuyển tab, hệ thống tự động ẩn/hiện khối sự kiện tương ứng (Lễ Vu Quy cho Nhà Gái và Tiệc Cưới cho Nhà Trai) kèm animation fade-in mượt mà, đồng thời highlight cột thông tin phụ huynh tương ứng.
    + **Nút Chỉ Đường Google Maps**: Bo góc 999px, viền và icon mạ vàng ánh kim, hover nổi bật êm ái.
    + **Khung Ảnh Chú Rể & Cô Dâu (Love Story)**: Viền trắng lưu niệm Polaroid cổ điển, bề mặt giấy mỹ thuật gân nổi, hover đưa ảnh về góc thẳng tự nhiên.
    + **Dải Phim & Lưới Album Cưới**: Dải phim cuộn ngang vintage 35mm mượt mà, lưới 10 ảnh Polaroid nghệ thuật, hover zoom nhẹ kèm bóng đổ mềm.
    + **Lịch Ngày Cưới**: Trái tim bao quanh ngày cưới số 7 có animation nhịp đập (heartbeat) lãng mạn.
    + **Lịch Trình Tiệc Cưới (Timeline)**: Icon tròn kính mờ viền vàng champagne mờ, đường timeline nối dọc xuyên qua tâm hoàn hảo.
    + **Hộp Mừng Cưới & RSVP**: Hộp quà có animation nhún nhảy mời gọi mở hộp, modal QR Code ngân hàng sắc nét, thẻ RSVP nút bấm rõ ràng và thanh lịch.

    + **Tối Ưu Vị Trí Khối Sự Kiện Cưới (Event Block UX)**:
      * Chuyển toàn bộ 2 khối sự kiện cưới `.mayhen_invitation_djcvhwiuo9w9_invitation-event-block` (Lễ Vu Quy & Tiệc Cưới) lên nằm ngay bên dưới tab chuyển đổi Nhà gái / Nhà trai (`.mayhen_invitation_djcvhwiuo9w9_side-switch-wrap`).
      * Giúp người dùng khi bấm chuyển qua lại giữa 2 tab Nhà gái / Nhà trai sẽ thấy ngay lập tức sự kiện tương ứng (ngày giờ, sảnh tiệc, địa chỉ và nút chỉ đường) thay đổi trực quan trước mắt mà không cần phải cuộn xuống dưới.
      * Tinh chỉnh khoảng cách thẩm mỹ: `margin: 28px auto 0; padding: 20px 15px 0; border-top: 1px solid rgba(0, 0, 0, 0.08);` cho khối sự kiện, và bổ sung đường nét đứt mờ nhạt (`border-top: 1px dashed rgba(0, 0, 0, 0.12); margin-top: 46px;`) phân tách êm dịu phần lời mời "Thân mời Quý vị & Gia đình..." và tên cô dâu chú rể ở bên dưới.

- **Tạo Master Prompt Tích Hợp Template Mới (`PROMPT_INTEGRATE_TEMPLATE.md`)**:
  - Cung cấp sẵn một file hướng dẫn chuẩn hóa (Master Prompt) tại thư mục gốc của repository.
  - Sau này khi có bất kỳ giao diện template thiệp cưới mới nào (HTML/CSS/JS tĩnh hoặc từ file thiết kế), chỉ cần copy toàn bộ nội dung file prompt này, điền tên mẫu, slug, prefix ID và đường dẫn thư mục nguồn rồi gửi cho AI là hệ thống sẽ tự động chuyển đổi và tích hợp chuẩn xác 100% theo toàn bộ quy chuẩn của Mây Hẹn (Pure HTML/CSS/Native JS, dynamic data fallback, Mobile-First, RSVP, Sổ lưu bút, Hộp mừng cưới, Nhạc nền, Seeder DB, Lint và testing).

- **Nâng Cấp Hệ Thống Nhạc Cưới Tự Động Làm Mới Cache (`WeddingMusicService`)**:
  - **Tự động làm mới cache (Auto-Invalidation siêu tốc)**: Dựa trên so sánh `filemtime` của thư mục nhạc với file `client_musics_index.json`. Khi người dùng thêm, sửa hoặc xóa bất kỳ file bài hát/thư mục ca sĩ nào, hệ thống tự động phát hiện và quét lại toàn bộ để cập nhật file JSON ngay lập tức (<1ms).
  - **Linh hoạt cấu trúc thư mục**: Quét đồng thời cả các file `.mp3` nằm trực tiếp trong `storage/app/public/client/musics/` lẫn trong các thư mục con `musics/singer/{Tên ca sĩ}/`.
  - **Artisan Command `php artisan musics:refresh`**: Bổ sung lệnh console cho phép quản trị viên hoặc lập trình viên chủ động quét lại và in ra thống kê bài hát.
  - **API Query Parameter**: Hỗ trợ param `GET /api/wedding-musics?refresh=1` để ép làm mới cache trực tiếp từ trình duyệt.

- **Thiết Kế Split Hero Layout 2 Cột Trang Kho Mẫu Thiệp (`/mau-thiep`) Chuẩn Quiet Luxury**:
  - **Bố Cục 2 Bên (Split Grid Hero)**: Thay thế khối text căn giữa đơn điệu bằng layout 2 cột bất đối xứng thanh lịch (`grid-template-columns: 1.15fr 0.85fr;`), đặt trong khung nền gradient cát ấm `rgba(248, 245, 239, 0.94)`, viền chỉ vàng champagne mỏng và đổ bóng đa tầng mềm mại.
  - **Cột Trái (Editorial Copy & Feature Badges)**:
    + **Kicker / Eyebrow**: Huy hiệu nhỏ bo tròn `Bộ Sưu Tập Mẫu Thiệp 2026` kèm icon ngôi sao tinh tế và chữ lồng `MÂY HẸN STUDIO`.
    + **H1 & SEO Copy**: Tiêu đề Serif quý phái `Mẫu Thiệp Cưới Đẹp & Thiệp Mời Online Cao Cấp`, đoạn văn mô tả mềm mại chuẩn Semantic SEO tối ưu cho các cụm từ khóa tìm kiếm (mẫu thiệp cưới đẹp, thiệp cưới online, mở phong thư tem sáp, tự tạo miễn phí, chia sẻ Zalo).
    + **Lưới 4 Feature Badges (2x2)**: Thẻ tính năng độc bản có icon SVG mạ vàng cát (Tự tạo miễn phí 2 phút, Mở phong thư tem sáp, RSVP & Bản đồ GPS, Tự phát nhạc cưới 20+ bài), hover nhấc nhẹ thanh thoát.
  - **Cột Phải (Visual Showcase Mockup & Stickers Độc Bản)**:
    + **Showcase Art Card**: Khung thiệp giấy mỹ thuật xoay nhẹ góc nghiêng 3D (`rotate(-2deg)`), lồng ảnh cưới nghệ thuật editorial, đổ bóng chiều sâu cao cấp và typography dập chữ nổi ("Minh Triết & Thảo Linh · Save The Date").
    + **Sticker Con Dấu Sáp Hoàng Gia 3D (Wax Seal)**: Tem sáp đỏ rượu Ruby dập nổi monogram `MH SEAL`, viền chỉ trắng mờ và đổ bóng 3D chân thực ở góc trên của thiệp.
    + **Floating Stickers Tương Tác**: 
      * Sticker nổi `✨ Chạm mở tương tác` hiệu ứng trôi bồng bềnh (floating animation).
      * Sticker nổi `🎵 Tự động phát nhạc` kèm dải sóng âm equalizer nhảy nhịp nhàng (`@keyframes mayhenEqBounce`).
      * Nhãn gập góc `Quiet Luxury` đậm phong cách thiệp cưới châu Âu.
  - **Responsive Mobile-First**: Tự động xếp chồng cột mượt mà trên tablet và mobile (<992px), bo góc gọn gàng, co giãn ảnh và nhãn dán tỉ lệ vàng, không tràn viền màn hình.

- **Nghiên Cứu Đối Thủ, Kiểm Toán Hệ Thống & Thiết Kế Kiến Trúc INVITATION EDITOR V2**:
  - **Khảo sát chuyên sâu đối thủ Ngày Chung Đôi (`ngaychungdoi.com`)**: Phân tích toàn diện 2 chế độ tạo thiệp (Điền form theo mẫu vs Thiết kế tự do), cơ chế Quick-Fill thu thập thông tin lũy tiến, tính năng bảo lưu mã QR/Link khi sửa thiệp, hệ thống đối tác B2B (Studio credit, link duyệt watermark).
  - **Kiểm toán mã nguồn Mây Hẹn hiện tại**: Chỉ rõ các lỗ hổng kỹ thuật (lưu ảnh vào `resources/views/`, thiếu state store tập trung, không có autosave ngầm, thiếu Undo/Redo, mobile UX bị che khuất bởi form, đổi mẫu reload trang gây đứt đoạn trải nghiệm).
  - **Thiết lập Triết lý Trải nghiệm 2 Tầng (Two-Tier Model)**:
    + *Chế độ 1 — Quick Editor V2*: Tối ưu 60 giây tạo thiệp nhanh cho 60% khách hàng không biết thiết kế.
    + *Chế độ 2 — Visual Section Designer*: Chạm trực tiếp vào thiệp (Click-to-Select), kéo thả đổi thứ tự section (Section DnD) bằng Native Pointer Events API, tùy biến 6 bộ màu Quiet Luxury, typography Serif và phong thư tem sáp.
  - **Phân tách dữ liệu cốt lõi (Data Decoupling)**: Tách bạch tuyệt đối giữa `weddings` (dữ liệu cưới bất biến) và `invitations.custom_config` (Document Schema version 2.0.0). Đổi mẫu không bao giờ mất dữ liệu cưới.
  - **Khởi tạo trọn bộ 10 tài liệu kỹ thuật & sản phẩm trong `docs/`**:
    1. `docs/research/ngaychungdoi-editor-benchmark.md`
    2. `docs/research/mayhen-editor-current-audit.md`
    3. `docs/product/editor-v2-prd.md`
    4. `docs/product/editor-v2-user-flows.md`
    5. `docs/design/editor-v2-ux-ui.md`
    6. `docs/design/editor-v2-component-inventory.md`
    7. `docs/architecture/editor-v2-architecture.md`
    8. `docs/database/editor-v2-data-impact.md`
    9. `docs/implementation/editor-v2-refactor-roadmap.md`
    10. `docs/implementation/editor-v2-task-breakdown.md`
  - **Bổ sung AI Skill chuyên biệt**: Tạo `.agents/skills/mayhen-visual-editor/SKILL.md` định nghĩa toàn bộ tiêu chuẩn code và Definition of Done cho phân hệ Editor V2.

- **Triển Khai Hoàn Thành Gói Tasks Kỹ Thuật INVITATION EDITOR V2**:
  - **TASK-E2-001 (Media & DB Schema)**:
    + Tạo migration `2026_10_20_000001_add_editor_v2_columns_to_invitations_table.php` bổ sung 4 cột mới: `schema_version` (varchar), `revision_id` (bigint), `draft_config` (json), `last_autosaved_at` (timestamp).
    + Chuẩn hóa hàm `uploadAsset` trong `InvitationEditorController`: Lưu trữ chuẩn vào `storage/app/public/weddings/photos/`, chấm dứt việc ghi file vào `resources/views/`.
  - **TASK-E2-003 (Backend Autosave & Concurrency Lock API)**:
    + Thêm route `POST /api/editor/autosave` (`invitation.autosave`).
    + Bổ sung phương thức `saveDraftWithLock` trong `InvitationTemplateService` và endpoint `autosave` trong `InvitationEditorController`.
    + Triển khai Optimistic Concurrency Control: So sánh `revision_id`, từ chối ghi đè và trả HTTP 409 Conflict nếu phiên làm việc ở tab khác đã lưu dữ liệu mới hơn.
  - **TASK-E2-002 (Vanilla JS Single State Store & Offline Recovery)**:
    + Tạo `public/assets/client/js/editor/store.js`: Single State Store quản lý luồng dữ liệu một chiều (`state = { wedding, custom_config, revision_id, ui }`).
    + Tạo `public/assets/client/js/editor/actions.js`: Tập hợp Action types & creators (`UPDATE_FIELD`, `SWITCH_TEMPLATE`, `REORDER_SECTIONS`, `SET_THEME_PALETTE`, `UNDO`, `REDO`...).
    + Tạo `public/assets/client/js/editor/history.js`: Lịch sử Undo/Redo tối đa 20 snapshot, hỗ trợ phím tắt `Ctrl+Z`, `Ctrl+Y`.
    + Tạo `public/assets/client/js/editor/autosaver.js`: Ghi đè tức thì vào `localStorage` chống mất điện/tắt tab đột ngột, kết hợp Debounce 1.5s gửi ngầm lên `/api/editor/autosave`.
  - **TASK-E2-004 & TASK-E2-005 (Studio V2 UI & Zero-Loss Template Switcher)**:
    + Tạo file CSS chuẩn Quiet Luxury `public/assets/client/css/pages/editor-v2.css`.
    + Topbar dính: Tích hợp Pill trạng thái lưu trữ trực tiếp (Đang lưu nháp / Đã lưu nháp / Ngoại tuyến / Xung đột), cụm nút Undo/Redo và nút đổi kích thước Mobile (390px) / Desktop.
    + Module `public/assets/client/js/editor/template-switcher.js`: Chuyển đổi giữa các mẫu thiệp cưới tức thì không reload trang, bảo toàn 100% dữ liệu cưới và đồng bộ sang template mới qua PostMessage.
  - **Khắc phục lỗi Layout & Tương tác Studio Editor V2**:
    + **Loại bỏ lỗi Fatal Error 500 Backend**: Xóa khai báo trùng lặp biến `protected string $musicBasePath` trong `app/Services/WeddingMusicService.php` làm sập controller.
    + **Giải phóng Studio Fullscreen 100vh**: Cập nhật `master.blade.php` hỗ trợ `@unless(View::hasSection('hide_chrome'))`, loại bỏ hoàn toàn Header, Footer và Bottom Nav của website thương mại đè lên Studio.
    + **Sửa lỗi biến đổi màu nền toàn trang**: Loại bỏ việc nhúng nhầm `preview.css` và `home.css` trong `editor.blade.php` (do `preview.css` quy định `html { background-color: #161310; }` làm tối đen giao diện Studio).
    + **Tái cấu trúc bố cục cân bằng & Trải nghiệm Mobile 1 chạm**:
      * Mở rộng Contextual Drawer từ 440px lên `480px` - `520px`, gom nhóm các input thành các Card trang nhã (`.mayhen_client_form_card`), tăng chiều cao input lên 44px chuẩn công thái học.
      * Tối ưu Album Gallery ảnh thành lưới 2 cột cân xứng (`.mayhen_editor_gallery_grid`), rút ngắn 60% chiều dài cuộn trang.
      * Ghim thanh điều hướng chân trang (Sticky Stepper Footer `.mayhen_editor_drawer_footer`) luôn nổi ở mép dưới Drawer, không cần cuộn chuột đến đáy mới thấy nút bấm.
      * Giảm độ dày bezel viền chiếc iPhone Mockup ở Center Stage (từ 22px xuống 5px viền titan mỏng), căn giữa tự co giãn theo chiều cao màn hình laptop/máy tính.
      * **Đột phá trải nghiệm Mobile (< 768px)**: Tích hợp bộ chuyển đổi Segmented Switcher chuẩn iOS trên Topbar (`[ ✏️ Nhập Liệu | 👁️ Xem Thiệp ]`) cho phép chuyển đổi 1 chạm giữa màn hình form full-width và màn hình thiệp cưới toàn cảnh, kèm Floating Action Bar nổi linh hoạt, loại bỏ hoàn toàn tình trạng kẹt cuộn và che khuất màn hình.
    + **Khắc phục triệt để lỗi giao diện, phần tử thừa & bố cục mất cân đối**:
      * **Xóa bỏ xung đột CSS V1**: Gỡ bỏ việc nhúng đồng thời file CSS cũ `editor.css` trong `editor.blade.php`, chỉ nạp duy nhất `editor-v2.css` với cache busting `?v={{ filemtime(...) }}`.
      * **Sửa lỗi Topbar đè lên Dynamic Island trong iPhone preview**: Cập nhật điều kiện `@if ($isPreview && !$isEmbed)` trong template `thuoc-phim-tinh-yeu`, loại bỏ hoàn toàn thanh xem thử và nút "+ Tạo Thiệp Mẫu Này" xuất hiện sai vị trí bên trong iframe của Studio.
      * **Dọn sạch các thẻ HTML thừa rớt layout**: Xóa bỏ thẻ `<div class="mayhen_editor_drawer_handle_bar">` (dòng chữ "Chỉnh Sửa Thông Tin X" đè lên tab) và `<nav class="mayhen_editor_mobile_bottom_bar">` (hàng chữ rơi ở góc dưới cùng bên trái màn hình).
      * **Bố cục 2 cột thông minh cho Form Fields (`.mayhen_editor_fields_grid`)**: Tự động xếp song song các trường cặp đôi tương xứng (Cô Dâu ⇄ Chú Rể, Phụ Mẫu Nhà Trai ⇄ Nhà Gái, Ngày Cử Hành ⇄ Giờ Làm Lễ), giúp form gọn gàng, giảm 50% chiều sâu cuộn chuột, mở rộng Drawer lên `500px` - `560px` cân xứng hoàn hảo với Center Stage.
      * **Thiết kế Sticky Stepper Footer cân đối 3 phần**: Ghim chắc chắn ở chân Drawer gồm nút "Quay Lại" (disabled tinh tế ở bước 1), bộ đếm bước "Bước 1 / 4" ở giữa, và nút "Tiếp Tục" (hoặc "Xuất Bản Thiệp" ở bước cuối) màu vàng đồng `#B45309`, tích hợp tự động cuộn mượt form về đầu mỗi khi chuyển bước.
      * **Khắc phục lỗi Tab & Stepper không ẩn hiện nội dung đúng bước**: Bổ sung bộ selector `.mayhen_editor_step_content { display: none !important; }` và `.mayhen_editor_step_content.mayhen_editor_step_active { display: block !important; }` cùng keyframe transition vào `editor-v2.css` (trước đó bị thiếu khi gỡ `editor.css` khiến tất cả các bước đều lộ ra cùng lúc), đồng thời bổ sung style cho danh sách Timeline repeater và gọi `goToStep(1)` khởi tạo trạng thái ban đầu.
    + **Khắc phục triệt để lỗi Live Preview & Lưu trữ Bố Cục & Màu Sắc (custom_config)**:
      * **Nguyên nhân cốt lõi**:
        1. Form `#mayhen_editor_form` trước đó thiếu thẻ input hidden `invitation[custom_config]`, khiến dữ liệu màu sắc, tem sáp, thứ tự và trạng thái ẩn/hiện section chỉ nằm ở client store mà không được gửi lên Controller khi bấm "Xuất Bản Thiệp".
        2. Iframe của các template thiệp (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`) chỉ mới lắng nghe `MAYHEN_SYNC_INPUT`, hoàn toàn chưa có bộ đón message cho `MAYHEN_CHANGE_THEME`, `MAYHEN_UPDATE_WAX_SEAL`, `MAYHEN_REORDER_SECTIONS`, và `MAYHEN_TOGGLE_SECTION`.
        3. Các template thiệp chưa có khối CSS SSR đọc từ `$invitation->custom_config` khi người dùng xem thiệp công khai `/thiep-cuoi/{slug}`.
      * **Giải pháp đã thực thi hoàn tất**:
        1. **Form Synchronization**: Bổ sung `<input type="hidden" name="invitation[custom_config]" id="invitation_custom_config">` trong `editor.blade.php`. Cập nhật `editor.js` tự động đồng bộ `store.getState().custom_config` vào input này mỗi khi có thay đổi từ `ThemeController` hoặc `SectionDnDManager`, và kiểm tra bắt buộc trước khi submit.
        2. **Iframe Live Preview Listener**:
           - Mẫu `thuoc-phim-tinh-yeu/template.blade.php`: Lắng nghe 4 loại message và tiêm động `<style id="mayhen_live_custom_style">` cập nhật các biến CSS `:root` (`--paper`, `--ink`, `--border`, `--primary`), đổi màu và monogram tem sáp, sắp xếp lại các phần tử DOM con trong `.mayhen_invitation_djcvhwiuo9w9_card-wrapper` theo thứ tự người dùng kéo thả, cập nhật `display: none` cho các section bị tắt.
           - Mẫu `net-muc-tinh-yeu/template.blade.php`: Tương tự, cập nhật `:root` (`--mayhen_bg_warm`, `--mayhen_ink`, `--mayhen_gold`, `--mayhen_gold_soft`, `--mayhen_wax`), thay đổi monogram tem sáp `.mayhen_invitation_dhsuudushi9428r_wax_seal`, sắp xếp lại DOM bên trong `#mayhen_invitation_main_content` và ẩn/hiện các section.
        3. **Template Initial State Sync**: Bổ sung hàm bắn trọn bộ theme, wax seal và section order sang iframe ngay khi iframe hoàn tất tải trang (`iframe.addEventListener('load')`).
        4. **Backend Persistence**: Cập nhật `InvitationEditorController@store` nhận trường `invitation.custom_config`, giải mã `json_decode(..., true)` an toàn và lưu trực tiếp vào trường `custom_config` (kiểu json) trong bảng `invitations`.
        5. **SSR Persistence Render**: Thêm khối `<style id="mayhen_custom_theme_style">` đọc từ `$invitation->custom_config` vào phần `<head>` của cả 2 template thiệp cưới, đảm bảo khi xuất bản ra đường dẫn công khai `/thiep-cuoi/{slug}`, màu sắc và các section bị ẩn của thiệp vẫn giữ nguyên 100% như lúc người dùng tùy chỉnh.
    + **Tính năng Cuộn Thông Minh (Auto-Scroll Live Preview) theo Tab Nhập Liệu**:
      * **Vấn đề**: Người dùng bấm chuyển tab trên Stepper (ví dụ sang "4. Ảnh Cưới", "5. Lịch Trình", "2. Nhà Gái"...) thì màn hình iPhone preview bên phải vẫn đứng yên ở vị trí cũ hoặc bị kẹt ở cánh mở thiệp, người dùng phải tự dùng chuột cuộn qua lại rất mất thời gian.
      * **Giải pháp**:
        1. **Mở sẵn thiệp khi nhúng Studio (isEmbed)**: Trong chế độ `isEmbed`, 2 cánh mở đầu phong bì tự động ẩn (`display: none !important`), đồng thời loại bỏ triệt để các lớp khóa cuộn `.mayhen_invitation_djcvhwiuo9w9_no-scroll` / `mayhen_invitation_scroll_locked` (ghi đè `overflow-y: auto !important`, `height: 100% !important`), cho phép màn hình iframe luôn trong trạng thái sẵn sàng cuộn tự do bằng chuột, cảm ứng và thanh cuộn.
        2. **Gắn metadata `data-section-id`**: Bổ sung `data-section-id="{{ $section['id'] }}"` vào từng tab stepper và panel nội dung trong `editor.blade.php`.
        3. **Phát tín hiệu `MAYHEN_SCROLL_TO_SECTION`**: Trong hàm `goToStep(step)` của `editor.js`, mỗi khi người dùng chuyển bước (bấm tab, bấm Tiếp Tục hoặc Quay Lại), Studio tự động phát message `MAYHEN_SCROLL_TO_SECTION` chứa `section_id` sang Iframe.
        4. **Bộ xử lý cuộn mượt trong Template**: Cả hai mẫu thiệp (`thuoc-phim-tinh-yeu` và `net-muc-tinh-yeu`) bắt sự kiện `MAYHEN_SCROLL_TO_SECTION`, tìm chính xác phần tử section tương ứng trong DOM và thực hiện `targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' })`.
        5. **Thanh cuộn siêu mảnh & Bảo toàn cuộn tự do**: Tùy chỉnh thanh cuộn siêu mảnh (6px) với màu vàng đồng Quiet Luxury `#B45309`, viền trắng nhẹ `rgba(255, 255, 255, 0.8)`, nền track mờ `rgba(0, 0, 0, 0.06)`, bo tròn viền mềm mại, không bao giờ khóa cuộn (`overflow-y: auto !important`), cho phép người dùng thoải mái dùng con lăn chuột, kéo vuốt hoặc kéo thanh scrollbar trên cả màn hình Studio và bên trong iPhone preview mà không gặp bất kỳ trở ngại nào.
    + **Khắc phục lỗi Đổi Mẫu Thiệp không cập nhật khi Tạo / Cập nhật Thiệp**:
      * **Vấn đề**: Người dùng đang chỉnh sửa thiệp, sau đó dùng tính năng "Đổi Mẫu Thiệp" (`TemplateSwitcher`) để chuyển sang mẫu khác (ví dụ từ `thuoc-phim-tinh-yeu` sang `net-muc-tinh-yeu`), nhưng khi bấm "Xuất Bản Thiệp" hoặc lưu cập nhật thì thiệp tạo/cập nhật ra vẫn là mẫu cũ.
      * **Nguyên nhân cốt lõi**:
        1. Trong `public/assets/client/js/editor/template-switcher.js`: Hàm `switchTemplate({ id, slug, name, thumb, tier })` chỉ dispatch vào `this.store` và đổi `iframe.src`, nhưng **hoàn toàn quên cập nhật giá trị vào thẻ input form `#template_id`** (`<input type="hidden" name="template_id" id="template_id" value="...">`) của form chính.
        2. Trong `public/assets/client/js/pages/editor.js`: Hàm `submitFormWithPendingUploads()` trước khi gọi `mainForm.submit()` không kiểm tra và gán lại `template_id` mới nhất từ `store.getState().template_id` vào input `#template_id`.
        3. Do đó, khi form gửi request POST lên `InvitationEditorController@store`, trường `template_id` vẫn mang ID của template ban đầu, khiến backend ghi đè lại template cũ thay vì template mới đã chọn.
      * **Giải pháp đã thực thi**:
        1. **Đồng bộ DOM tức thì trong `template-switcher.js`**: Ngay khi người dùng chọn mẫu mới, cập nhật trực tiếp `document.getElementById('template_id').value = id`, đồng thời cập nhật biến toàn cục `window.__MAYHEN_TEMPLATE_ID = id; window.__MAYHEN_TEMPLATE_SLUG = slug;`.
        2. **Chốt chặn an toàn trong `editor.js`**: Trước khi `mainForm.submit()`, tự động đọc `store.getState().template_id` và gán đè vào input `#template_id` nếu có giá trị, bảo đảm dữ liệu gửi lên máy chủ luôn chính xác 100% với mẫu thiệp người dùng đã chọn.

    + **Khắc Phục Triệt Để Lỗi Mất Ảnh Khi Chỉnh Sửa Thiệp Cũ (Live Preview Edit Mode)**:
      * **Nguyên nhân gốc rễ**:
        1. Khi vào URL chỉnh sửa thiệp cũ `tao-thiep?edit={slug}`, iframe bên phải chỉ tải URL mặc định `route('templates.preview', $slug)` mà không truyền slug thiệp (`&edit={slug}`). Do đó, `TemplateController@preview` chỉ render template với ` = null`, toàn bộ ảnh cưới của thiệp đã lưu không được SSR nạp vào màn hình preview.
        2. Sau khi iframe tải xong, `syncAllFieldsToIframe` trong `editor.js` gửi các giá trị từ form sang iframe qua PostMessage `MAYHEN_SYNC_INPUT`. Nhưng các ảnh mặc định trong cơ sở dữ liệu được lưu dưới dạng tương đối (ví dụ `assets/3ed9dcf0-...jpg`). Bộ xử lý `editor-bridge.blade.php` trước đây gán trực tiếp `img.src = val`, dẫn đến trình duyệt trong iframe hiểu sai URL và tải về ảnh rỗng hoặc gãy (broken image icon), đồng thời các section dùng `background-image` (như hero section) không được gán style tương ứng.
      * **Giải pháp đã thực thi**:
        1. **Nạp dữ liệu thiệp đang sửa vào Preview Iframe**: Cập nhật cả 2 thẻ iframe preview (màn hình chính và modal mobile) trong `editor.blade.php` thành:
           `route('templates.preview', $selectedTemplate->slug) . '?embed=1&mode=editor' . (isset($editingInvitation) && $editingInvitation ? '&edit=' . $editingInvitation->slug : '')`.
        2. **Xử lý SSR trong `TemplateController@preview`**: Nhận tham số `edit` hoặc `invitation`, truy vấn thông tin `` và `` nếu người dùng là chủ sở hữu và truyền trực tiếp vào Blade template khi nhúng vào Studio.
        3. **Chuẩn hóa hàm `resolveUrl` trong `editor-bridge.blade.php`**: Khi nhận message `MAYHEN_SYNC_INPUT`, tự động nhận diện đường dẫn tương đối để ghép đúng với URL gốc `` (`/mau-thiep/{slug}/assets/...`), đồng thời tự động cập nhật cả `el.style.backgroundImage` cho các khối banner/hero ảnh cưới.

    + **Tách Biệt & Tự Động Ghép Tên Phụ Mẫu Nhà Trai & Nhà Gái (UX Phụ Mẫu Chuẩn Văn Hóa Việt)**:
      * **Vấn đề**: Trước đây, form nhập liệu yêu cầu người dùng tự gõ cả chuỗi dài (ví dụ: `ÔNG: Nguyễn Tràng Giang & BÀ: Lê Thị Mai`), khiến người dùng lúng túng, gõ sai format và dễ làm hỏng bố cục thiết kế thiệp cưới.
      * **Giải pháp thực hiện**:
        1. **Tách 4 ô nhập liệu độc lập trong `schema.json`**:
           - Nhà Trai: `Họ tên Cha (Nhà Trai)` (`wedding.extra_data.groom_father`) và `Họ tên Mẹ (Nhà Trai)` (`wedding.extra_data.groom_mother`).
           - Nhà Gái: `Họ tên Cha (Nhà Gái)` (`wedding.extra_data.bride_father`) và `Họ tên Mẹ (Nhà Gái)` (`wedding.extra_data.bride_mother`).
        2. **Tự Động Ghép Chuỗi Phụ Mẫu Chuẩn Văn Hóa (Live Preview & Backend)**:
           - **Editor Bridge (`editor-bridge.blade.php`)**: Khi người dùng gõ vào bất kỳ ô cha/mẹ nào, bridge tự động tổng hợp thành `ÔNG: {Cha} & BÀ: {Mẹ}` (hoặc định dạng 2 dòng tùy template) và phản ánh tức thì lên màn hình xem thử Live Preview.
           - **Backend (`InvitationEditorController@store`)**: Khi lưu thiệp, controller tự động tổng hợp từ `extra_data` thành chuỗi chuẩn gán vào 2 cột `groom_parents` và `bride_parents` của bảng `weddings`, đảm bảo tương thích ngược 100% với các tính năng cũ và thiệp đã xuất bản.
           - **Khả năng tương thích ngược khi sửa thiệp (`editor.blade.php`)**: Nếu mở thiệp cũ chưa có 4 trường riêng biệt trong `extra_data`, hệ thống tự động bóc tách từ chuỗi `groom_parents` / `bride_parents` điền sẵn vào 4 ô nhập liệu, người dùng không cần nhập lại từ đầu.
        3. **Template Contract**: Các mẫu thiệp hỗ trợ cả hiển thị riêng lẻ (`[data-sync-target="groom_father"]`, `[data-sync-target="groom_mother"]`) lẫn hiển thị gộp (`[data-sync-target="groom_parents"]`, `[data-sync-target="bride_parents"]`).

    + **Chuẩn Hóa Toàn Diện Template Contract & Module Bridge Dùng Chung (Scale Cho Hàng Trăm Mẫu Thiệp)**:
      * **Vấn đề đã nhận diện**: Trước đây, các template thiệp (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`) sử dụng các bộ selector CSS / ID ngẫu nhiên không đồng nhất (như `#mayhen_invitation_djcvhwiuo9w9_home`, `.mayhen_invitation_dhsuudushi9428r_story`...) và phải viết lại hàng trăm dòng mã JavaScript riêng lẻ trong mỗi file template để xử lý PostMessage, đồng bộ màu sắc, tem sáp, sắp xếp section và cuộn tự động. Cách này không thể mở rộng khi hệ thống phát triển lên hàng chục hoặc hàng trăm mẫu thiệp cưới khác nhau.
      * **Giải pháp kiến trúc chuẩn hóa**:
        1. **HTML Contract Chuẩn Dùng Chung Toàn Hệ Thống**:
           - **Container các section**: Bọc thẻ chứa các phần bằng thuộc tính chuẩn: `data-mayhen-section-container`.
           - **Từng Section**: Gắn thuộc tính thống nhất: `data-mayhen-section="{section_id}"` (với các id chuẩn: `couple`, `bride_events`, `groom_events`, `countdown`, `gallery_and_photos`, `story_and_timeline`, `gift_and_music`, `rsvp`, `guestbook`).
           - **Phần tử đồng bộ dữ liệu trực tiếp**: Gắn `data-sync-target="{field_key}"` (ví dụ `data-sync-target="groom_name"`, `data-sync-target="bride_name"`, `data-sync-target="hero_photo_url"`).
        2. **Partial Blade Thống Nhất `resources/views/client/invitations/partials/editor-bridge.blade.php`**:
           - Tách toàn bộ logic PostMessage của iframe thành một partial dùng chung duy nhất, chỉ tải khi `isEmbed` (trong Studio).
           - Tự động bắt và xử lý hoàn chỉnh 5 loại message từ Studio:
             * `MAYHEN_SYNC_INPUT`: Cập nhật text content, thuộc tính src của ảnh, và ngày giờ đếm ngược qua `[data-sync-target]`.
             * `MAYHEN_CHANGE_THEME`: Tiêm thẻ `<style id="mayhen_live_custom_style">` cập nhật bảng màu `:root` tức thì.
             * `MAYHEN_UPDATE_WAX_SEAL`: Đổi màu nền con dấu sáp và ký tự lồng chữ cái monogram.
             * `MAYHEN_REORDER_SECTIONS`: Tự động sắp xếp lại thứ tự DOM của các section bên trong `[data-mayhen-section-container]`.
             * `MAYHEN_TOGGLE_SECTION`: Bật/tắt hiển thị (`display: none` / `display: block`) theo `[data-mayhen-section="{section_id}"]`.
             * `MAYHEN_SCROLL_TO_SECTION`: Tìm kiếm phần tử `[data-mayhen-section="{section_id}"]` và kích hoạt cuộn mượt tự động `scrollIntoView({ behavior: 'smooth', block: 'start' })`.
        3. **Refactor Hoàn Toàn 2 Template Mẫu (`thuoc-phim-tinh-yeu` & `net-muc-tinh-yeu`)**:
           - Xóa bỏ toàn bộ các khối `@match` hardcode và script riêng lẻ cồng kềnh (tiết kiệm hơn 300 dòng code thừa).
           - SSR ẩn hiện section chuyển sang dạng 1 dòng CSS duy nhất: `[data-mayhen-section="{{ $hiddenId }}"] { display: none !important; }`.
           - Nhúng partial dùng chung: `@include('client.invitations.partials.editor-bridge')`.
    + **Chuẩn Hóa Global Preview Topbar Dùng Chung & Loại Bỏ Topbar Khỏi Giao Diện Template**:
      * **Vấn đề đã nhận diện**: Trước đây, các template mẫu thiệp (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`) tự nhúng khối `<header class="mayhen_preview_topbar">` bên trong file `template.blade.php`. Khi xem trong popup Modal Showcase ở trang chủ hoặc trên mobile, các nút bị dồn ép với Dynamic Island của khung điện thoại, vỡ lề và xuất hiện thanh cuộn ngang Windows ở đáy màn hình.
      * **Giải pháp kiến trúc**:
        1. **Tách biệt hoàn toàn**: Xóa sạch toàn bộ khối topbar xem thử (`mayhen_preview_topbar`) và topbar chủ thiệp khỏi mã nguồn của từng template (`thuoc-phim-tinh-yeu`, `net-muc-tinh-yeu`). Mẫu thiệp giữ trọn vẹn 100% không gian hiển thị nghệ thuật thuần khiết.
        2. **Thanh Global Preview Topbar Dùng Chung Tại Trang Chủ (`home.blade.php`)**:
           - Tái cấu trúc `.mayhen_home_showcase_modal_top_bar` thành thanh điều hướng capsule Quiet Luxury thống nhất cho toàn bộ mẫu thiệp.
           - Tích hợp đầy đủ: Badge xanh "Đang xem thử" + Tên mẫu thiệp hiện tại (`#mayhen_home_showcase_modal_header_title`), bộ chuyển đổi chế độ xem Mobile / Desktop, nút "Mở tab mới", nút "Tải lại", và nút "Sao chép link".
           - Đồng bộ tự động qua hàm `openLivePreviewModal` trong `home.js`.
           - Tối ưu responsive: Trên thiết bị di động (<640px), tự động ẩn nhãn phụ để thanh bar luôn vừa vặn, không bao giờ bị chèn đè lên các nút xung quanh.
        3. **Chống tràn ngang triệt để**: Bổ sung `overflow-x: hidden` trên `html, body` và chuyển đổi chiều rộng `100vw` của các cánh rèm thiệp sang `100%`, loại bỏ hoàn toàn hiện tượng thanh cuộn ngang xuất hiện trong iframe.

---

*Tài liệu được khởi tạo và duy trì bởi Antigravity Engine — Dự án Mây Hẹn (mayhen.vn).*









