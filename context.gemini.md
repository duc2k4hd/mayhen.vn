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

---

## 11. THANH ĐIỀU HƯỚNG ĐÁY MÀN HÌNH TOÀN WEB (BOTTOM NAVIGATION BAR)

Hệ thống thanh dock điều hướng đáy trang (`mayhen_client_bottom_nav_*`) được tích hợp cố định trên toàn bộ website (`client.layouts.master`):

1. **Thứ Tự 5 Nút Thuần Icon SVG (Không viết chữ nhãn để giữ thẩm mỹ tối giản)**:
   - **Nút 1: Trang chủ** (`route('home')`) — Icon Home nét vẽ thanh mảnh.
   - **Nút 2: Mẫu thiệp** (`route('templates.index')`) — Icon Card / Grid layout.
   - **Nút 3: Tạo thiệp** (`route('invitation.create')`) — **Nút tròn lớn ở chính giữa nổi nhô cao** (`margin-top: -24px`), viền nền 3.5px `#FAF8F5`, phủ dải màu Muted Gold sang trọng, hiệu ứng animation sóng hào quang lan tỏa (`mayhenPulseGlow`) và nhịp thở (`mayhenIconFloat`).
   - **Nút 4: Yêu thích** (`route('templates.index')#yeu-thich`) — Icon Trái tim lãng mạn.
   - **Nút 5: Trợ lý AI** (`route('contact')#tro-ly-ai`) — Icon Robot AI thông minh.
2. **Quy Chuẩn Kỹ Thuật**:
   - Mỗi nút có thuộc tính `title` và `aria-label` đầy đủ để hỗ trợ tooltip và trợ năng (accessibility).
   - Tự động kích hoạt trạng thái Active chấm nhỏ dưới icon theo từng route.
   - Body tự động có khoảng đệm bù đáy `padding-bottom: calc(65px + env(safe-area-inset-bottom, 0px))` đảm bảo không bị che khuất nội dung chân trang.
   - Tương thích đa màn hình: Cố định chân màn hình trên Mobile và chuyển thành thanh Dock nổi bo tròn bo viên thuốc ở giữa đáy trên Desktop.

---

## 13. TỐI ƯU HÓA MOBILE-FIRST CHO KHO MẪU THIỆP (/mau-thiep)

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

*Tài liệu được khởi tạo và duy trì bởi Antigravity Engine — Dự án Mây Hẹn (mayhen.vn).*
