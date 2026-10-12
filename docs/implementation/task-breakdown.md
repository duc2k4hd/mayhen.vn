# MÂY HẸN (MAYHEN.VN) — KẾ HOẠCH TRIỂN KHAI & DANH SÁCH TASK CHI TIẾT

## 1. Mục Tiêu Dự Án
Xây dựng Mây Hẹn (mayhen.vn) thành nền tảng tạo thiệp cưới online cao cấp số 1 tại Việt Nam.
- Phong cách: Quiet Luxury, Editorial, Minimal, Romantic, Modern.
- Công nghệ bắt buộc: Laravel 13, Blade, CSS thuần (Prefix `mayhen_`), JavaScript thuần ES Modules.
- Cấm tuyệt đối: CSS frameworks (Tailwind/Bootstrap), JS frameworks (Vue/React/Alpine), animation/slider libs (GSAP/Swiper/AOS).
- Trọng tâm hàng đầu: Giao diện tinh tế, thiệp mẫu đẹp xuất sắc, chuyển động (motion) mượt mà, tối ưu tuyệt đối trên mobile.

---

## 2. Đối Chiếu Hiện Trạng (Baseline Gap Analysis)

| Hạng mục | Yêu cầu Master Prompt | Hiện trạng Source Code | Trạng thái |
| :--- | :--- | :--- | :--- |
| **Hạ tầng / Framework** | Laravel 13.x, PHP 8.3+, Pest 5.x | Laravel 13.17, PHP 8.5, Pest 5.3 | Đã có base chuẩn |
| **Hệ thống AI Skills (20 skills)** | Đủ 20 skills theo Phần 18 Master Prompt | Chưa có (chỉ có skills mặc định) | **THIẾU** |
| **AGENTS.md điều phối Mây Hẹn** | Quy tắc Mây Hẹn, CSS prefix, vanillajs, routing | Chỉ có Boost default template | **THIẾU** |
| **Tài liệu Kiến trúc & Thiết kế** | docs/ (PRD, ERD, Brand, Tokens, Animation) | Chưa có thư mục docs | **THIẾU** |
| **Design System & Tokens** | CSS tokens (màu, typography, spacing, radius) | Chưa có | **THIẾU** |
| **Layout Client & Shared Components**| Master layout, Header, Footer, Button, Modal | Chỉ có welcome.blade.php mặc định | **THIẾU** |
| **Trang Client Marketing** | Home, Mau-thiep, Bang-gia, Dich-vu, Huong-dan, Lien-he, Blog... | Chưa có | **THIẾU** |
| **Template Engine & 3 Thiệp Mẫu** | Envelope Reveal, Paper Unfold, Minimal Luxury (Mobile-first, touch, music) | Chưa có | **THIẾU** |
| **Database & Migrations** | Thiết kế chuẩn weddings, templates, invitations, guests, rsvp... | Chỉ có users, cache, jobs | **THIẾU** |
| **Editor Tạo Thiệp (Quick & Advanced)**| Trình tạo thiệp trực quan, autosave, preview | Chưa có | **THIẾU** |
| **Quản lý Khách mời & RSVP** | Guest list, link cá nhân hoá, Zalo share, QR, RSVP | Chưa có | **THIẾU** |
| **Thanh toán & Gói dịch vụ** | Free, Premium, Pro; VietQR/SePay webhook idempotent | Chưa có | **THIẾU** |
| **Admin Module** | Dashboard, Templates, Invitations, Orders, Users | Chưa có | **THIẾU** |

---

## 3. Lộ Trình Triển Khai Tuần Tự (Roadmap by Priority)

### Giai đoạn 1: Foundation, AI Skills & Design System (Đang thực hiện)
- **Task 1.1**: Tạo Bộ 20 AI Skills chuẩn cho Mây Hẹn trong `.agents/skills/` và cập nhật `AGENTS.md`.
- **Task 1.2**: Lập bộ tài liệu nền tảng (`docs/design/design-system.md`, `docs/design/animation-system.md`, `docs/database/database-design.md`, `docs/product/prd.md`).
- **Task 1.3**: Xây dựng Foundation Frontend: Design Tokens (`tokens.css`, `base.css`, `layout.css`, `buttons.css`, `forms.css`), JS utilities (`dom.js`, `events.js`, `http.js`), Blade Components (Header, Footer, Navigation, Modal).

### Giai đoạn 2: Client Marketing Experience (Trang chủ & Thư viện mẫu)
- **Task 2.1**: Trang chủ `/` (Hero tương tác mô phỏng thiệp trên mobile, Quiet Luxury aesthetic, showcase phong cách, CTA tạo thiệp).
- **Task 2.2**: Thư viện mẫu thiệp `/mau-thiep` (Filter phong cách, xem trước nhanh, mobile responsive).
- **Task 2.3**: Trang chi tiết mẫu thiệp `/mau-thiep/{slug}`.
- **Task 2.4**: Trang Bảng giá `/bang-gia` & Dịch vụ `/dich-vu`.
- **Task 2.5**: Trang Giới thiệu `/ve-may-hen`, Hướng dẫn `/huong-dan`, Liên hệ `/lien-he`, FAQ `/cau-hoi-thuong-gap`, Điều khoản & Bảo mật.

### Giai đoạn 3: Invitation Design System & 3 Mẫu Thiệp Đỉnh Cao
- **Task 3.1**: Kiến trúc Template Engine cho Thiệp cưới (Data schema, layout registry, audio player controller, guest personalization).
- **Task 3.2**: Template 01 — **Envelope Reveal** (Mở phong thư hoàng gia, kéo thiệp, rơi cánh hoa, RSVP mượt).
- **Task 3.3**: Template 02 — **Editorial Scroll** (Tạp chí thời trang cưới, cuộn typography nghệ thuật, photo gallery story).
- **Task 3.4**: Template 03 — **Minimal Luxury** (Phong cách tĩnh lặng tối giản, typography tinh tế, tone kem ấm & vàng cát).
- **Task 3.5**: Xem thử công khai `/xem-thu/{slug}` & Trang thiệp chính thức `/thiep/{slug}`.

### Giai đoạn 4: Database & Core Models
- **Task 4.1**: Thiết kế và triển khai Migrations: `templates`, `weddings`, `invitations`, `guests`, `guest_responses`, `orders`, `plans`.
- **Task 4.2**: Eloquent Models, Relationships, Form Requests & Policies.

### Giai đoạn 5: Invitation Editor (Trình Tạo Thiệp) & Khách Mời
- **Task 5.1**: Trình tạo thiệp Chế độ Nhanh (Quick Creation) & Chế độ Nâng cao.
- **Task 5.2**: Quản lý khách mời, xuất/nhập Excel, link cá nhân hoá Zalo, QR cưới.
- **Task 5.3**: RSVP Dashboard & Sổ lời chúc (Guestbook).

### Giai đoạn 6: Thương Mại & Admin
- **Task 6.1**: Thanh toán VietQR / SePay, xử lý webhook idempotent.
- **Task 6.2**: Admin Dashboard quản lý thiệp, mẫu và đơn hàng.
