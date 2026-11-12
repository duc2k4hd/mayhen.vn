# MÂY HẸN — TÀI LIỆU 04: BẢN THIẾT KẾ GIAO DIỆN HỢP NHẤT TỪ MẪU GỐC
## Docs UI Blueprint From Templates — Tích Hợp Header/Footer & Hợp Nhất Layout Blade

> **Mã tài liệu:** `DOCS-UI-04`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Nguồn thiết kế đối chiếu:**  
> - Trang chủ Docs: `D:\Project\Laravel\mayhen.vn\docs\templates\mayhen_docs\`  
> - Trang chi tiết bài viết: `D:\Project\Laravel\mayhen.vn\docs\templates\mayhen_docs_detail\`  

---

## 1. NGUYÊN TẮC HỢP NHẤT GIAO DIỆN (UNIFIED MASTER LAYOUT)

Theo đúng yêu cầu chỉ đạo:
> *"Lấy header và footer của giao diện làm layout chung cho tất cả các trang docs và các chức năng của 2 trang có thể đọc và tích hợp gộp vào nhau cho nhiều chức năng, tránh để lỗi logic code, đảm bảo hợp nhất thành 1 layout nhưng đảm bảo các trang chức năng khác nhau chung layout, xem kỹ càng."*

Hệ thống tài liệu Mây Hẹn sẽ được xây dựng trên một **Master Layout Blade duy nhất**:
`resources/views/docs/layout.blade.php`

Layout này tích hợp toàn vẹn các thành phần cốt lõi:
1. **Unified Sticky Header (2 tầng)**: Cung cấp nhận diện thương hiệu, thanh điều hướng nhanh, công cụ tìm kiếm toàn cục, nút chuyển chế độ Sáng/Tối, và thanh tiến độ đọc trang.
2. **Dynamic Body Layout Slot (`@yield('content')`)**:
   - Khi ở trang chủ (`/docs/` hoặc `/admin/docs/`): Render chế độ **Catalog View** (Hero search lớn, trending keywords, category grid cards, interactive tools widget).
   - Khi ở trang chi tiết (`/docs/{cat}/{slug}` hoặc `/admin/docs/{cat}/{slug}`): Render chế độ **3-Column Editorial View** (Sidebar danh mục collapsible bên trái, Nội dung bài viết ở giữa, Mục lục TOC scrollspy bám dính bên phải).
3. **Unified Footer (4 cột)**: Đồng bộ triết lý Quiet Luxury, thông tin pháp lý, phím tắt nhanh, hotline Zalo, nút Back-to-Top.
4. **Shared Modal Dialogs (`<dialog>`)**: Hộp thoại tìm kiếm nhanh (`Ctrl+K`), Cài đặt hiển thị đọc bài, Phím tắt bàn phím, Góp ý nội dung.

---

## 2. BẢNG MÃ MÀU & THIẾT KẾ TOKENS CHUẨN MÂY HẸN (DESIGN TOKENS)

Hợp nhất các biến CSS từ cả 2 template vào một bảng token chuẩn, đặt tiền tố bảo vệ `--mayhen-docs-*`:

```css
:root {
  /* Bảng màu nhận diện Quiet Luxury */
  --mayhen-docs-bg: #fffdfb;               /* Nền kem ấm sang trọng (Warm Cream) */
  --mayhen-docs-surface: #ffffff;          /* Nền thẻ nội dung trắng tinh khôi */
  --mayhen-docs-surface-soft: #fcf9f6;     /* Nền phụ dịu nhẹ */
  --mayhen-docs-rose: #805052;             /* Hồng đất Mây Hẹn (Rose Muted) */
  --mayhen-docs-rose-dark: #613b3e;        /* Hồng đất đậm cho nút bấm / hover */
  --mayhen-docs-rose-light: #f6eeec;       /* Nền hồng phớt làm nổi bật alert/tag */
  --mayhen-docs-champagne: #b58e6f;        /* Vàng Champagne hoàng gia */
  --mayhen-docs-champagne-light: #f9f5f0;  /* Vàng nhạt viền ô */
  --mayhen-docs-ink: #30292b;              /* Màu chữ chính: Mực than sẫm */
  --mayhen-docs-muted: #6b6365;            /* Màu chữ phụ: Xám tro trang nhã */
  --mayhen-docs-border: #ede4dc;           /* Đường kẻ viền tinh tế */
  
  /* Bảng màu trạng thái thông báo */
  --mayhen-docs-success: #2d6a4f;          /* Xanh lục bảo thành công */
  --mayhen-docs-warning: #b25e00;          /* Cam hổ phách cảnh báo */
  --mayhen-docs-danger: #a4161a;           /* Đỏ rượu vang cảnh báo lỗi / bảo mật */
  --mayhen-docs-info: #1d3557;             /* Xanh hải quân thông tin lưu ý */

  /* Phông chữ & Nhịp điệu không gian */
  --mayhen-docs-font: 'Open Sans', 'Segoe UI', Arial, sans-serif;
  --mayhen-docs-font-heading: 'Open Sans', -apple-system, sans-serif;
  --mayhen-docs-radius: 12px;
  --mayhen-docs-radius-sm: 8px;
  --mayhen-docs-radius-full: 9999px;
  --mayhen-docs-shadow-sm: 0 2px 8px rgba(48, 41, 43, 0.04);
  --mayhen-docs-shadow: 0 8px 24px rgba(48, 41, 43, 0.08);
  --mayhen-docs-transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Chế độ Nền Tối (Dark Mode) chuẩn điện ảnh */
[data-theme="dark"] {
  --mayhen-docs-bg: #1a1617;
  --mayhen-docs-surface: #241f20;
  --mayhen-docs-surface-soft: #2b2526;
  --mayhen-docs-ink: #f5f0ee;
  --mayhen-docs-muted: #a89f9e;
  --mayhen-docs-border: #3d3436;
  --mayhen-docs-rose: #b87c7e;
  --mayhen-docs-rose-light: #3d2c2e;
  --mayhen-docs-champagne: #d4ae8f;
  --mayhen-docs-champagne-light: #382d27;
}
```

---

## 3. KIỂM KÊ & ĐẶC TẢ CHI TIẾT CÁC COMPONENT (COMPONENT INVENTORY)

### 3.1. Header Component (`.mayhen_docs_header`)
- **Vị trí & Cơ chế:** Bám dính đỉnh màn hình (`position: sticky; top: 0; z-index: 100; backdrop-filter: blur(12px)`).
- **Thành phần:**
  - `brand-badge`: Logo Mây Hẹn + Tên không gian (`Tài liệu Hướng dẫn` / `Admin Knowledge Base`).
  - `search-pill`: Nút giả thanh tìm kiếm hiển thị phím tắt `Ctrl + K` (khi nhấp sẽ kích hoạt Search Modal).
  - `quick-nav-links`: Các liên kết điều hướng mượt mà, tự ẩn trên mobile và xuất hiện trong drawer menu.
  - `theme-toggle`: Nút xoay biểu tượng Mặt trời / Mặt trăng mượt mà, lưu trạng thái vào `localStorage`.
  - `reading-progress`: Dải băng màu Champagne cao 3px chạy sát mép dưới Header, phản ánh chính xác vị trí đọc bài.

### 3.2. Sidebar Component (`.mayhen_docs_sidebar`)
- **Desktop (>= 1024px):** Cố định bên trái (`position: sticky; top: 80px; height: calc(100vh - 80px); overflow-y: auto`).
- **Mobile (< 1024px):** Chuyển hóa thành Drawer menu trượt từ trái sang khi nhấn nút Hamburger, có lớp phủ mờ Backdrop (`position: fixed; inset: 0; z-index: 200`).
- **Cấu trúc dữ liệu:** Accordion phân cấp theo Chuyên mục -> Danh sách bài viết. Tự động mở chuyên mục chứa bài đang đọc và gắn class `is-active` kèm vạch màu Rose bên cạnh.

### 3.3. Main Content Component (`.mayhen_docs_article`)
- **Breadcrumbs:** Hiển thị đường dẫn rõ ràng: `Trang chủ > Chuyên mục > Tên bài viết`.
- **Article Header:**
  - Tiêu đề H1 lớn, cỡ chữ 28px - 34px, độ đậm 700.
  - Khối Meta: Ngày cập nhật gần nhất, Thời gian ước tính hoàn thành (ví dụ: `Đọc & thực hiện: 3 phút`), Tác giả/Người kiểm duyệt, Nút lưu bài (Bookmark).
- **Callout Blocks (Hộp ghi chú đặc biệt):**
  - `.callout-tip`: Nền xanh phớt, icon bóng đèn -> Mẹo thực hiện nhanh và tối ưu.
  - `.callout-note`: Nền vàng nhạt Champagne, icon thông tin -> Những lưu ý quan trọng về ngày giờ, định dạng.
  - `.callout-warning`: Nền cam, icon cảnh báo -> Những thao tác cần thận trọng, tránh nhầm lẫn.
  - `.callout-security`: Nền hồng đậm, icon khiên bảo vệ -> Cảnh báo quyền riêng tư, bảo mật thông tin khách mời và tài chính.
- **Numbered Steps Container (`.steps-flow`):**
  - Các bước 1, 2, 3 được đánh số lớn cách điệu trong vòng tròn màu Rose.
  - Mô tả hành động rõ ràng, tên nút bấm và menu được bọc trong thẻ `<kbd>` hoặc `<strong>`.
  - Khung hình minh họa giao diện (Screenshot Frame) có viền bo tròn và bóng đổ êm ái.
- **FAQ Accordion (`.faq-accordion`):**
  - Danh sách các câu hỏi thường gặp ở cuối bài viết, người đọc nhấp vào để mở rộng câu trả lời mà không làm dài trang.
- **Article Footer Navigation (`.article-nav`):**
  - Hai thẻ bài viết kế tiếp: `[← Bài trước]` và `[Bài tiếp theo →]` giúp người đọc học tuần tự theo một lộ trình trọn vẹn.

### 3.4. Table of Contents (TOC) Component (`.mayhen_docs_toc`)
- **Desktop (>= 1280px):** Nằm ở cột bên phải, bám dính khi cuộn trang.
- **Cơ chế hoạt động:** JavaScript dùng `IntersectionObserver` tự động lắng nghe các thẻ `<h2>`, `<h3>` trong bài viết để đánh dấu đề mục đang đọc trong thời gian thực.
- **Mobile (< 1280px):** Thu gọn thành nút nhỏ nổi góc màn hình "Mục lục bài viết", khi bấm mở popup danh sách đề mục.

### 3.5. Search Modal Component (`#mayhen_docs_search_modal`)
- **Thẻ HTML:** Sử dụng thẻ native `<dialog id="mayhen_docs_search_modal">` hiện đại và tối ưu hiệu năng.
- **Phím tắt kích hoạt:** `Ctrl + K`, `Cmd + K` (Mac), hoặc phím `/` ở bất kỳ đâu trên trang.
- **Khả năng tìm kiếm:**
  - Hỗ trợ gõ tiếng Việt có dấu (`xác nhận rsvp`) và không dấu (`xac nhan rsvp`).
  - Tự động quét theo Tiêu đề, Tóm tắt, Thẻ tag và Chuyên mục.
  - Điều hướng kết quả bằng phím Mũi tên lên / xuống (`ArrowUp` / `ArrowDown`), phím `Enter` để mở bài, phím `Esc` để đóng.
  - Phân vùng kết quả rõ ràng: Hiển thị đúng biểu tượng của từng nhóm (Trang, Thao tác, Hướng dẫn lỗi).

---

## 4. MA TRẬN ĐÁP ỨNG THIẾT BỊ (RESPONSIVE VIEWPORT MATRIX)

Hệ thống được tinh chỉnh CSS Media Queries đáp ứng mượt mà trên 5 khoảng màn hình:

| Viewport Range | Thiết bị Đại diện | Cấu trúc Hiển thị | Trạng thái Sidebar | Trạng thái TOC |
| :--- | :--- | :--- | :--- | :--- |
| **< 640px** | iPhone SE, iPhone 13/14/15, Galaxy S | 1 Cột duy nhất (Full width). Header thu gọn, Menu Hamburger. Cỡ chữ tự co 15px. | Drawer trượt ẩn (Off-canvas) | Thu gọn thành Floating Action Button |
| **640px - 1023px** | iPad Mini, iPad Air, Galaxy Tab | 1 Cột nội dung (720px căn giữa). Header hiển thị thanh tìm kiếm nhỏ. | Drawer trượt ẩn | Popup Mục lục trên đầu bài viết |
| **1024px - 1279px** | Laptop nhỏ (13 inch, 1366x768) | 2 Cột: Sidebar trái (260px) + Nội dung chính (linh hoạt). | Cố định bám dính bên trái | Tích hợp thu gọn ở đầu bài viết |
| **1280px - 1599px** | Màn hình chuẩn Desktop (FHD 1080p) | **3 Cột hoàn chỉnh**: Sidebar trái (280px) + Nội dung (840px) + TOC phải (240px). | Cố định bám dính bên trái | Cố định bám dính bên phải (Scrollspy) |
| **>= 1600px** | Màn hình lớn 2K / 4K / iMac | Khung tối đa 1440px căn giữa trang, lề hai bên màu kem ấm êm dịu, không bị loãng. | Cố định bên trái | Cố định bên phải |

---

## 5. KẾ HOẠCH HỢP NHẤT FILE TĨNH (CSS/JS PACKAGING)

Để tuân thủ triệt để nguyên tắc **Native Fast Architecture** và **Zero External Framework**:
- Toàn bộ CSS giao diện docs được đặt tại:
  `public/assets/client/docs/css/docs-main.css`
  (Hợp nhất tinh gọn từ `styles.css` của 2 template, loại bỏ trùng lặp class).
- Toàn bộ JavaScript giao diện docs được đặt tại:
  `public/assets/client/docs/js/docs-main.js`
  (Bao gồm logic: live search, collapsible tree, sticky TOC scrollspy, dialog modals, theme switcher, progress bar).
- Không thêm bất kỳ dòng code inline nào vào Blade view.
- Tự động nạp tối ưu qua thẻ `<link>` và `<script type="module">` trong Master Layout.

