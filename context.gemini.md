# 🕊️ CONTEXT & KIẾN TRÚC TOÀN DIỆN DỰ ÁN MÂY HẸN (MAYHEN.VN)

> **⚠️ BẮT BUỘC ĐỐI VỚI MỌI AI AGENT (GEMINI, CLAUDE, GPT, CURSOR, COPILOT...):**
> 1. **ĐỌC FILE NÀY ĐẦU TIÊN** trước khi phân tích hoặc viết bất kỳ dòng code nào trong repository này!
> 2. **CẬP NHẬT FILE NÀY SAU MỖI LẦN CODE**: Bất kỳ khi nào hoàn thành hoặc thay đổi tính năng, cấu trúc, route, UI/UX hay database, AI **bắt buộc phải cập nhật lại file này** để giữ context luôn mới nhất trên Git.
> 3. **TUÂN THỦ 100% CÁC NGUYÊN TẮC CỐT LÕI**: Không tự ý thêm thư viện ngoài, không phá vỡ quy ước đặt tên CSS prefix và kiến trúc pure vanilla.

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
| **GET** | `/xem-thu/{slug}` | `templates.preview`| `TemplateController@preview` | Xem thử thiệp live mô phỏng smartphone (`noindex, nofollow`) |
| **GET** | `/tao-thiep` | `invitation.create`| `InvitationEditorController@create` | Mở trình soạn thảo tạo thiệp mới (`noindex, nofollow`, chặn khách chưa login nếu chọn mẫu Plus/Pro) |
| **POST** | `/tao-thiep` | `invitation.store` | `InvitationEditorController@store` | Lưu dữ liệu đám cưới & cấu hình thiệp (sinh slug hậu tố kép chống trùng) |
| **GET** | `/thiep/{slug}` | `invitation.show` | `InvitationEditorController@show` | Trang thiệp cưới công khai cho khách nhận (`noindex, nofollow`) |
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

## 8. CẤU TRÚC DATABASE & CÁC MODEL CHÍNH

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

*Tài liệu được khởi tạo và duy trì bởi Antigravity Engine — Dự án Mây Hẹn (mayhen.vn).*
