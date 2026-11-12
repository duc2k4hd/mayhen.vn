# Mây Hẹn — Docs Detail

Trang chi tiết tài liệu phong cách thiệp cưới, phỏng theo **bố cục** website GitHub Docs (header, breadcrumbs, sidebar trái, nội dung, TOC phải, footer). Giao diện Mây Hẹn, nội dung ví dụ và họa tiết được xây dựng riêng.

## Tệp
- `index.html`: HTML semantic, tiếng Việt.
- `styles.css`: CSS thuần, responsive, dark mode, print stylesheet.
- `script.js`: JavaScript thuần cho các chức năng.
- `mayhen_docs_detail.html`: phiên bản HTML **một file** (CSS/JS đã nhúng; chỉ font Open Sans tải từ Google khi có Internet).

## Chạy ngay
- Mở `mayhen_docs_detail.html` bằng Chrome, Edge, Firefox hoặc Safari.
- Nếu dùng bản tách file, giữ `index.html`, `styles.css`, `script.js` chung thư mục và mở `index.html`.
- Dùng Google Fonts Open Sans: https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap

## Chức năng
1. Sidebar danh mục dạng accordion; thu gọn sidebar trên desktop, drawer trên mobile.
2. TOC cố định ở desktop, popup mục lục trên mobile; scrollspy đánh dấu phần đang đọc.
3. Thanh tiến độ đọc và nút cuộn lên đầu trang.
4. Search modal nội bộ có lọc tiếng Việt không dấu; `Ctrl/Cmd + K`, `/`, mũi tên lên/xuống, Enter.
5. Chế độ sáng / tối / theo hệ thống; độ rộng đọc, chỉnh cỡ chữ và chế độ tập trung.
6. Native `<dialog>` cho tìm kiếm, cài đặt, chia sẻ, góp ý, phím tắt, TOC.
7. Sao chép Markdown, liên kết từng mục, lời mời mẫu, URL minh họa; native share; in/PDF.
8. Lưu bài & đánh giá nội dung trên thiết bị qua localStorage; FAQ dạng accordion.
9. Tôn trọng `prefers-reduced-motion`, hỗ trợ bàn phím, print stylesheet.

## Tiền tố và biến thiết kế
- CSS class: `mayhen_docs_detail_*`.
- HTML ID: `mayhen_docs_detail_*`.
- CSS variables: `--mayhen-docs-detail-*`.
- Font body: `'Open Sans', Arial, sans-serif` (Open Sans của Google Fonts).

## Tích hợp Laravel
Chuyển `index.html` thành `resources/views/client/docs/detail.blade.php`. Đưa CSS và JavaScript vào `public/assets/client/docs/styles.css` và `public/assets/client/docs/script.js`, cập nhật các đường dẫn trong Blade bằng `asset()`. Thay nội dung bài viết tĩnh, sidebar và TOC bằng dữ liệu từ backend. Lựa chọn URL thực theo route và slug dự án. Hãy sanitize HTML từ CMS trước khi render và xác thực/giới hạn tần suất API cho form góp ý.

**Quan trọng:** đây là bản frontend mẫu hoàn chỉnh, không phải hệ thống tài liệu backend. Tìm kiếm chỉ trong các phần của bài mẫu. Form góp ý **sao chép nội dung**, không gửi email hay lưu server; đánh giá & bookmark lưu localStorage của trình duyệt. Link mẫu thiệp `/i/ten-co-dau-chu-re` là minh họa. Không giả lập thành công của API.
