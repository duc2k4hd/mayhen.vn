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

###  TIỀN TỐ CSS CLASS BẮT BUỘC (MANDATORY CLASS PREFIX)
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

## 4. KIẾN TRÚC THƯ MỤC DỰ ÁN

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
│           ├── components/             # Header, Footer, Notification, v.v.
│           ├── pages/
│           │   ├── home.blade.php      # Landing Page trang chủ (12 sections chuẩn SEO Top 1)
│           │   ├── templates.blade.php # Danh mục kho mẫu thiệp cưới
│           │   ├── template-detail.blade.php
│           │   ├── preview.blade.php   # Trang xem thử mẫu thiệp (Desktop & Mobile)
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
│               └── thanh-manh-01/
├── public/
│   └── assets/
│       ├── client/
│       │   ├── css/
│       │   │   ├── common/             # tokens.css, base.css, reset.css
│       │   │   ├── components/         # header.css, footer.css, button.css, modal.css
│       │   │   └── pages/              # home.css, templates.css, editor.css...
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

## 5. BẢN ĐỒ ROUTES & CHỨC NĂNG HỆ THỐNG

| Method | URI | Tên Route | Controller & Action | Chức năng |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/` | `home` | `HomeController@index` | Trang chủ Landing Page chuẩn SEO Top 1 |
| **GET** | `/mau-thiep` | `templates.index` | `TemplateController@index` | Kho mẫu thiệp cưới phân loại theo phong cách |
| **GET** | `/mau-thiep/{slug}`| `templates.show` | `TemplateController@show` | Chi tiết mẫu thiệp, hình ảnh, tính năng |
| **GET** | `/xem-thu/{slug}` | `templates.preview`| `TemplateController@preview` | Xem thử thiệp live (mô phỏng màn hình điện thoại) |
| **GET** | `/tao-thiep` | `invitation.create`| `InvitationEditorController@create` | Mở trình soạn thảo tạo thiệp mới |
| **POST** | `/tao-thiep` | `invitation.store` | `InvitationEditorController@store` | Lưu dữ liệu đám cưới & cấu hình thiệp |
| **GET** | `/thiep/{slug}` | `invitation.show` | `InvitationEditorController@show` | Trang thiệp cưới công khai cho khách nhận |
| **POST** | `/thiep/{slug}/rsvp`| `rsvp.submit` | `GuestController@submitRsvp` | Khách gửi phản hồi RSVP xác nhận tham dự |
| **GET** | `/thanh-toan/{planSlug}`| `checkout.show` | `CheckoutController@show` | Trang thanh toán chuyển khoản VietQR |
| **POST** | `/api/checkout/process`| `checkout.process` | `CheckoutController@process` | Tạo đơn hàng & mã thanh toán VietQR |
| **GET** | `/api/checkout/status/{orderCode}`| `checkout.status`| `CheckoutController@checkStatus`| Polling kiểm tra trạng thái thanh toán |
| **POST** | `/api/webhook/payment`| `webhook.payment` | `CheckoutController@handleWebhook`| Webhook tự động kích hoạt gói khi nhận tiền |
| **GET** | `/tai-khoan/thiep/{id}/khach-moi`| `guests.index` | `GuestController@index` | Quản lý danh sách khách, gửi thiệp cá nhân hóa |
| **POST** | `/tai-khoan/thiep/{id}/khach-moi`| `guests.store` | `GuestController@store` | Thêm khách mời mới vào danh sách |
| **DELETE**| `/tai-khoan/khach-moi/{id}`| `guests.destroy` | `GuestController@destroy` | Xóa khách mời |
| **GET** | `/dich-vu` | `services` | `PageController@services` | Giới thiệu các dịch vụ của Mây Hẹn |
| **GET** | `/bang-gia` | `pricing` | `PageController@pricing` | Bảng giá các gói thiệp cưới online |
| **GET** | `/huong-dan` | `how-it-works` | `PageController@howItWorks` | Hướng dẫn 3 bước tạo và gửi thiệp |
| **GET** | `/ve-may-hen` | `about` | `PageController@about` | Câu chuyện thương hiệu Mây Hẹn |
| **GET** | `/lien-he` | `contact` | `PageController@contact` | Trang liên hệ hỗ trợ & tư vấn |
| **GET** | `/cau-hoi-thuong-gap`| `faq` | `PageController@faq` | Giải đáp thắc mắc thường gặp |
| **GET** | `/dieu-khoan-dich-vu`| `terms` | `PageController@terms` | Điều khoản dịch vụ |
| **GET** | `/chinh-sach-bao-mat`| `privacy` | `PageController@privacy` | Chính sách bảo vệ dữ liệu cá nhân |
| **GET/POST**| `/dang-nhap`, `/dang-ky`, `/dang-xuat` | Auth routes | `AuthController` | Xác thực người dùng |
| **ANY** | `/admin/*` | `admin.*` | Admin Controllers | Quản lý hệ thống mẫu, đơn hàng, khách hàng |

---

## 6. CHI TIẾT TRANG CHỦ & CHIẾN LƯỢC SEO TOP 1 "THIỆP CƯỚI ONLINE"

Trang chủ (`resources/views/client/pages/home.blade.php`) đã được thiết kế và tối ưu toàn diện để cạnh tranh trực tiếp vị trí **Top 1–3 Google** cho từ khóa cốt lõi: **"Thiệp cưới online"**, "Thiệp cưới điện tử", "Thiệp cưới thông minh":

### Cấu trúc 12 Sections chuẩn Semantic HTML:
1. **Hero Section (`mayhen_home_hero`)**:
   - `<h1>` chứa từ khóa chính tự nhiên: *"Thiệp cưới online tinh tế lưu giữ khoảnh khắc trăm năm"*.
   - Khẳng định giá trị Quiet Luxury, 2 nút CTA điều hướng rõ ràng ("Khám phá mẫu thiệp" & "Tạo thiệp thử miễn phí").
   - Stats xã hội: 15.000+ cặp đôi tin chọn, 99.8% hài lòng, 4.9/5 sao.
2. **Showcase Carousel & Modal (`mayhen_home_showcase`)**:
   - Trưng bày các mẫu thiệp thực tế tiêu biểu (Hoàng Gia Tem Sáp, Tối Giản Hàn Quốc, Thơ Mộng Hoa Cỏ, Cổ Điển Đông Dương, Vàng Đồng Sang Trọng...).
   - **Showcase Modal tương tác cao cấp**:
     - Xem mẫu trực tiếp qua Iframe responsive hoặc ảnh chất lượng cao.
     - Thanh điều khiển trực quan: Tab chuyển đổi Xem Iframe / Xem ảnh nét, nút phóng to/thu nhỏ, **nút tải ảnh thẳng về máy** (`download`), nút mở xem thử trang riêng.
     - Thiết kế bo góc liền lạc, không hở viền, chuẩn thẩm mỹ cao cấp.
3. **Giá trị cốt lõi / Ưu điểm vượt trội (`mayhen_home_values`)**:
   - So sánh thiệp cưới online Mây Hẹn vs Thiệp giấy truyền thống.
   - 4 trụ cột: Thiết kế Quiet Luxury, Quản lý RSVP chính xác, Gửi không giới hạn qua Zalo/Facebook, Tiết kiệm 85% chi phí.
4. **Tính năng độc quyền (`mayhen_home_features`)**:
   - Mở phong bì kèm tem sáp độc quyền, bản đồ Google Maps chỉ đường 1 chạm, hộp mừng cưới tích hợp VietQR, nhạc nền lãng mạn, album ảnh HD.
5. **Bộ sưu tập thịnh hành (`mayhen_home_collections`)**:
   - Phân loại theo phong cách: Luxury Wax Seal, Editorial Modern, Traditional Vietnam, Vintage Romance.
6. **Quy trình 3 bước đơn giản (`mayhen_home_steps`)**:
   - 1. Chọn mẫu thiệp -> 2. Điền thông tin & ảnh cưới -> 3. Nhận link & gửi thiệp tức thì.
7. **Bảng so sánh tính năng (`mayhen_home_comparison`)**:
   - Minh bạch tính năng giữa các gói: Miễn Phí (Trải nghiệm), Tiêu Chuẩn (Hạnh Phúc) và Cao Cấp (Trọn Đời).
8. **Đánh giá khách hàng thực tế (`mayhen_home_testimonials`)**:
   - Lời chia sẻ từ các cặp đôi thật, trích dẫn ảnh đại diện và địa điểm cưới thực tế.
9. **FAQ Chuẩn SEO (`mayhen_home_faq`)**:
   - 6 câu hỏi thường gặp nhất về thiệp cưới online, hỗ trợ cấu trúc Rich Snippet Google.
10. **Báo chí & Đối tác (`mayhen_home_press`)**:
    - Tăng độ tin cậy E-E-A-T (VnExpress, Marry.vn, ELLE Wedding...).
11. **CTA Cuối trang (`mayhen_home_cta_bottom`)**:
    - Kêu gọi hành động mạnh mẽ, tặng ưu đãi trải nghiệm.
12. **Cụm liên kết từ khóa SEO chân trang (`mayhen_home_seo_block`)**:
    - Internal link tự nhiên tới các chủ đề liên quan (thiệp cưới điện tử, RSVP, thiệp cưới Zalo, thiệp mừng cưới mã QR).

### Tối ưu Onpage & Schema SEO:
- Meta Title: `Thiệp Cưới Online Cao Cấp — Tạo Thiệp Cưới Điện Tử Tinh Tế | Mây Hẹn` (Dưới 65 ký tự, chứa từ khóa chính ngay đầu).
- Meta Description: Viết chuẩn 155 ký tự, hấp dẫn, có call-to-action rõ ràng.
- Canonical URL: `{{ url()->current() }}`.
- Open Graph & Twitter Card: Chuẩn kích thước 1200x630.
- Schema JSON-LD: `@type: WebSite`, `@type: Organization`, `@type: FAQPage`.
- **LƯU Ý KỸ THUẬT BLADE**: Trong file Blade, cú pháp JSON-LD phải viết là `@@context: "https://schema.org"` để tránh Blade hiểu nhầm là directive `@context`.

---

## 7. CẤU TRÚC DATABASE & CÁC MODEL CHÍNH

1. **`User` (`users`)**:
   - Quản lý tài khoản cô dâu, chú rể, studio đối tác hoặc admin.
2. **`InvitationTemplate` (`invitation_templates`)**:
   - `id`, `name`, `slug`, `category`, `price`, `thumbnail`, `preview_url`, `sections_schema` (JSON), `is_active`, `is_featured`.
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

## 8. QUY TRÌNH LÀM VIỆC DÀNH CHO AI (STANDARD WORKFLOW)

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
