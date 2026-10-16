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

---

*Tài liệu được khởi tạo và duy trì bởi Antigravity Engine — Dự án Mây Hẹn (mayhen.vn).*





