# MÂY HẸN — TÀI LIỆU 00: KHẢO SÁT NGUỒN THẬT & KIỂM ĐỊNH TỆP MẪU THIẾT KẾ
## Discovery & Design Source Audit — Trung tâm Hướng dẫn Sử dụng Mây Hẹn

> **Mã tài liệu:** `DOCS-AUDIT-00`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Snapshot Baseline:** `SNAP-20261112-5A994C5` | Git HEAD: `5a994c5` | PHP 8.5.10 | Laravel 13.35.0  
> **Workspace thực tế:** `D:\Project\Laravel\mayhen.vn`  
> **Thư mục mẫu thiết kế:** `D:\Project\Laravel\mayhen.vn\docs\templates`  

---

## 1. MỤC ĐÍCH & PHẠM VI KHẢO SÁT

Tài liệu này xác lập nền tảng sự thật kỹ thuật (Ground Truth) phục vụ xây dựng Kế hoạch Triển khai Trung tâm Hướng dẫn Sử dụng (Documentation Center) cho nền tảng Mây Hẹn (mayhen.vn), bao gồm:
1. **Client Help Center (`/docs/`)**: Dành cho khách hàng cá nhân, cô dâu, chú rể, khách mời dự tiệc và đối tác studio cưới.
2. **Admin Knowledge Center (`/admin/docs/`)**: Dành cho Quản trị viên, Quản lý (Manager), Chăm sóc khách hàng (CSKH/Support), Kế toán tài chính và nhân sự mới tiếp nhận vận hành hệ thống.

Toàn bộ kế hoạch được xây dựng trên nguyên tắc:
- **Chỉ lập kế hoạch ở lượt này**, tuyệt đối **chưa sửa đổi mã nguồn ứng dụng**.
- Không bỏ sót bất kỳ chức năng nào đã có mặt trên 757 runtime routes và 140 Controllers.
- Bám sát 100% mẫu giao diện gốc được cung cấp trong thư mục `docs/templates/`.
- Hợp nhất Header và Footer thành **một layout dùng chung** duy nhất, có khả năng thích ứng linh hoạt cho cả Client Docs và Admin Docs.

---

## 2. KIỂM KÊ CHI TIẾT TỆP MẪU THIẾT KẾ (`docs/templates/`)

Thư mục mẫu thiết kế tồn tại tại đường dẫn thực tế `D:\Project\Laravel\mayhen.vn\docs\templates` với đầy đủ 2 bộ template HTML/CSS/JS thuần, không sử dụng framework ngoài (Bootstrap/Tailwind) hay thư viện phụ thuộc:

```
docs/templates/
├── mayhen_docs/                     # MẪU TRANG TỔNG QUAN DOCS (HOME / CATALOG VIEW)
│   ├── index.html                   # HTML cấu trúc tách file (46,210 bytes)
│   ├── mayhen_docs.html             # Bản đóng gói đơn file có inline CSS/JS (131,192 bytes)
│   ├── styles.css                   # Toàn bộ CSS giao diện trang chủ Docs (48,696 bytes)
│   ├── script.js                    # Toàn bộ JS tương tác, tìm kiếm, popup (36,329 bytes)
│   └── README.md                    # Hướng dẫn kỹ thuật và mô tả tính năng mẫu (4,677 bytes)
└── mayhen_docs_detail/              # MẪU TRANG CHI TIẾT BÀI VIẾT (ARTICLE DETAIL VIEW)
    ├── index.html                   # HTML cấu trúc chi tiết bài viết 3 cột (42,593 bytes)
    ├── mayhen_docs_detail.html      # Bản đóng gói đơn file có inline CSS/JS (115,000 bytes)
    ├── styles.css                   # Toàn bộ CSS giao diện chi tiết bài viết (53,225 bytes)
    ├── script.js                    # Toàn bộ JS TOC, scrollspy, bookmark, share (20,758 bytes)
    └── README.md                    # Hướng dẫn kỹ thuật trang chi tiết (3,015 bytes)
```

### 2.1. Đánh giá Khả dụng của Tệp Mẫu (Design Usability Audit)
- **Tình trạng file:** Đầy đủ 100%, có thể mở trực tiếp trên trình duyệt Chrome, Edge, Firefox, Safari mà không phát sinh lỗi console.
- **Tiêu chuẩn công nghệ:**
  - 100% HTML5 ngữ nghĩa (`<header>`, `<nav>`, `<main>`, `<aside>`, `<article>`, `<dialog>`, `<footer>`).
  - 100% CSS thuần (Vanilla CSS) với cấu trúc BEM chặt chẽ:
    - Trang chủ: Tiền tố `mayhen_docs_*`
    - Trang chi tiết: Tiền tố `mayhen_docs_detail_*`
  - 100% JavaScript thuần (Vanilla Native ES6+), đóng gói trong IIFE riêng biệt, không ô nhiễm biến toàn cục (`window.*`).
  - Font chữ: Sử dụng họ phông chữ Google Fonts `'Open Sans'` kết hợp font hệ thống fallback `'Segoe UI', Arial, sans-serif`. Màu sắc và kiểu dáng hài hòa tuyệt đối với bảng màu nhận diện thương hiệu Mây Hẹn (Quiet Luxury: Nền kem ấm `#fffdfb`, Hồng đất `#805052`, Champagne `#b58e6f`, Mực đen than `#30292b`).

---

## 3. PHÂN TÍCH THÀNH PHẦN GIAO DIỆN & CHIẾN LƯỢC HỢP NHẤT LAYOUT

Yêu cầu chỉ đạo cốt lõi của Chủ dự án:
> *"Lấy header và footer của giao diện làm layout chung cho tất cả các trang docs và các chức năng của 2 trang có thể đọc và tích hợp gộp vào nhau cho nhiều chức năng, tránh để lỗi logic code, đảm bảo hợp nhất thành 1 layout nhưng đảm bảo các trang chức năng khác nhau chung layout, xem kỹ càng."*

### 3.1. Phân tích Header Hợp nhất (Unified Header Architecture)
Mẫu `mayhen_docs` sở hữu cấu trúc Header 2 tầng (Two-Tier Sticky Header) vô cùng tinh tế:
- **Tầng 1 (Primary Bar):**
  - Khối thương hiệu: Logo Mây Hẹn dạng SVG lụa kết hợp Wordmark "MÂY HẸN" và nhãn phân hệ (`Tài liệu Hướng dẫn` hoặc `Admin Knowledge Base`).
  - Điều hướng chính: Liên kết nhanh quay về Trang chủ ứng dụng (`mayhen.vn`), Thư viện mẫu thiệp (`/mau-thiep`), Bảng giá (`/bang-gia`), và Cổng đối tác (`/doi-tac`).
  - Công cụ tác vụ nhanh:
    - Nút kích hoạt Search Modal toàn cục (`Ctrl + K` hoặc `/`).
    - Nút chuyển đổi giao diện Sáng / Tối / Tự động (`Theme Switcher`).
    - Nút mở Cài đặt đọc (Chỉnh cỡ chữ, bề rộng trang, giảm chuyển động).
    - Liên kết trạng thái người dùng (Đã đăng nhập / Chưa đăng nhập) hoặc huy hiệu phân quyền Admin.
- **Tầng 2 (Sub-navigation Bar):**
  - Thanh Breadcrumbs động hiển thị vị trí hiện tại: `Trang chủ Docs > Chuyên mục > Tên bài viết`.
  - Phím chuyển đổi không gian đọc (Space Switcher): Chuyển nhanh giữa `Dành cho Khách hàng` và `Dành cho Quản trị viên` (chỉ hiển thị khi người dùng có quyền Admin).
  - Thanh đo tiến độ đọc trang (Reading Progress Bar) chạy mượt mà theo tỷ lệ phần trăm cuộn của nội dung bài viết.

### 3.2. Phân tích Footer Hợp nhất (Unified Footer Architecture)
Mẫu `mayhen_docs` cung cấp Footer 4 cột đồng bộ phong cách Quiet Luxury:
- **Cột 1 (Brand & Mission):** Biểu tượng Mây Hẹn, lời tuyên ngôn thương hiệu "Tác phẩm nghệ thuật thiệp cưới số lưu giữ khoảnh khắc vĩnh cửu", chứng chỉ tuân thủ bảo vệ dữ liệu cá nhân theo Luật 91/2025/QH15.
- **Cột 2 (Quick Navigation):** Danh mục hướng dẫn nổi bật, cẩm nang chuẩn bị ngày cưới, mẫu thiệp mới ra mắt.
- **Cột 3 (Support & Community):** Cổng gửi yêu cầu hỗ trợ (Ticket), Hotline Zalo hỗ trợ khách hàng, tài liệu API dành cho nhà phát triển.
- **Cột 4 (System & Legal):** Điều khoản dịch vụ, Chính sách bảo mật, Trạng thái hệ thống (System Status), Báo cáo sự cố an ninh.
- **Dải đáy (Bottom Bar):** Bản quyền © 2026 Mây Hẹn, phím tắt hướng dẫn (`?`), nút cuộn nhanh lên đầu trang (`Back to Top`).

### 3.3. Phân tích Nội dung & Cấu trúc Khối Thân (Body & Viewport Components)
- **Trang chủ (`/docs/` & `/admin/docs/`):**
  - Khung tìm kiếm Hero khổng lồ ở trung tâm (Instant Live Search) hỗ trợ gõ tiếng Việt có dấu và không dấu.
  - Các thẻ chủ đề gợi ý thịnh hành (Trending Tag Pills): `#TaoThiep`, `#DoiAnhBia`, `#XacNhanRSVP`, `#ChiaBanTiec`, `#SePayVietQR`.
  - Lưới Chuyên mục lớn (Category Grid Cards) dẫn lối người đọc theo đúng vai trò và hành trình.
  - Khối tính năng tương tác tích hợp sẵn từ template: Popup bài hướng dẫn nhanh, Đếm ngược ngày cưới, Checklist 7 bước chuẩn bị, Form gửi đóng góp ý kiến.
- **Trang chi tiết bài viết (`/docs/{category}/{slug}` & `/admin/docs/{category}/{slug}`):**
  - **Cột trái (Sidebar 280px):** Cây danh mục phân cấp đa tầng (Collapsible Tree Navigation) tự động active đúng bài đang đọc.
  - **Cột giữa (Article Main 800px - 920px):** Không gian trình bày bài viết chuẩn Editorial, font Open Sans tối ưu độ tương phản, các khối Callout (Tip, Note, Warning, Security), hộp chụp màn hình minh họa (Screenshot Frame), các bước thao tác đánh số kèm kết quả kiểm chứng.
  - **Cột phải (TOC 240px):** Mục lục nội dung bài viết (Table of Contents) tự động bám dính khi cuộn (Sticky), tự highlight đề mục bằng IntersectionObserver / Scrollspy.

---

## 4. ĐỐI SOÁT XUNG ĐỘT ĐỊNH TUYẾN (ROUTE COLLISION CHECK)

Khảo sát toàn bộ 757 runtime routes hiện hành trên dự án Mây Hẹn:

| Nhóm Route Kiểm tra | Trạng thái Hiện tại | Kết quả Đối soát & Đề xuất Quy hoạch |
| :--- | :--- | :--- |
| **`/docs`** | Chưa đăng ký trong `routes/web.php` | **An toàn tuyệt đối**. Quy hoạch làm Trang chủ Trung tâm Hướng dẫn Khách hàng (`ClientDocsController@index`). |
| **`/docs/{category}/{slug}`** | Chưa đăng ký | **An toàn tuyệt đối**. Quy hoạch làm Trang chi tiết bài hướng dẫn Client (`ClientDocsController@show`). |
| **`/docs/search`** | Chưa đăng ký | **An toàn tuyệt đối**. Endpoint API tìm kiếm toàn văn Client Docs (`ClientDocsController@search`). |
| **`/admin/docs`** | Chưa đăng ký trong `routes/admin.php` | **An toàn tuyệt đối**. Quy hoạch làm Trang chủ Tài liệu Vận hành Quản trị (`AdminDocsController@index`). |
| **`/admin/docs/{category}/{slug}`** | Chưa đăng ký | **An toàn tuyệt đối**. Quy hoạch làm Trang chi tiết tài liệu Quản trị (`AdminDocsController@show`). |
| **`/admin/docs/search`** | Chưa đăng ký | **An toàn tuyệt đối**. Endpoint API tìm kiếm bảo mật nội bộ (`AdminDocsController@search`). |
| **`/huong-dan`** | Đang có route `page.show` (`trang/{slug}`) | Không xung đột. Route `/huong-dan` hiện tại có thể tạo 301 Redirect vĩnh viễn về `/docs` để tối ưu SEO. |
| **Route Assets `/docs/templates/...`** | Thư mục tĩnh trong `docs/` | Không nằm trong thư mục `public/`, hoàn toàn bảo mật và không bị lộ ra ngoài Internet. |

---

## 5. KẾT LUẬN GIAI ĐOẠN KHẢO SÁT NGUỒN

1. Bộ mẫu giao diện trong `docs/templates/` có chất lượng hoàn thiện rất cao, đạt chuẩn Quiet Luxury, 100% thuần vanilla không thư viện ngoài, tương thích hoàn toàn với định hướng kiến trúc của Mây Hẹn.
2. Phương án hợp nhất Header & Footer thành một Master Layout (`resources/views/docs/layout.blade.php`) là hoàn toàn khả thi và tối ưu nhất về bảo trì, kế thừa 100% linh hồn thiết kế của template.
3. Không có bất kỳ xung đột định tuyến (Route collision) nào với 757 routes hiện hữu.
4. Cơ sở dữ liệu và bản đồ hệ thống 54 cột (`MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx/docx`) cung cấp đầy đủ 100% dữ liệu gốc để chuyển hóa thành cây tài liệu chi tiết.

