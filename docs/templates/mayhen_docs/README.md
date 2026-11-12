# Mây Hẹn Docs — Trang tổng quan tài liệu (GitBook-inspired)

Giao diện dành cho **mayhen.vn**, lấy bố cục tài liệu từ ảnh tham chiếu GitBook Docs nhưng thiết kế lại theo phong cách thiệp cưới Mây Hẹn. Toàn bộ **HTML, CSS và JavaScript thuần**, không sử dụng framework hay thư viện CSS/JS. Font chính là **Open Sans từ Google Fonts** (cần internet để tải font lần đầu; trình duyệt sẽ dùng Arial nếu không tải được).

## Các file

- `mayhen_docs.html`: **bản một file** có nhúng sẵn CSS + JavaScript. Có thể mở trực tiếp.
- `index.html`: HTML tách file, tham chiếu `styles.css` và `script.js`.
- `styles.css`: CSS responsive, palette kem / hồng đất / champagne, light/dark.
- `script.js`: logic frontend và thư viện hướng dẫn mẫu cục bộ.

## Chức năng có thể thử trực tiếp

1. Header 2 tầng, sidebar nhiều cấp, nhóm menu thu gọn/mở rộng và menu trượt mobile.
2. Tìm kiếm hướng dẫn bằng tiếng Việt có dấu hoặc không dấu, `Ctrl+K` / `Cmd+K` / `/`, dùng phím mũi tên và Enter để chọn kết quả.
3. Popup hướng dẫn chi tiết (16 bài mẫu), lưu bài bằng `localStorage`, sao chép nội dung.
4. Popup **Hỏi Mây Hẹn** tìm gợi ý từ dữ liệu mẫu; **không phải chatbot AI, chưa kết nối API**.
5. Popup cài đặt: Sáng/Tối/Theo hệ thống, tăng giảm font, thay đổi chiều rộng, bật/tắt chuyển động.
6. Popup checklist 7 việc chuẩn bị có trạng thái và thanh tiến độ, nhớ bằng `localStorage`.
7. Popup đếm ngược ngày cưới sử dụng ngày-giờ ở thiết bị người dùng, nhớ bằng `localStorage`.
8. Popup chia sẻ, copy link, Web Share API nếu thiết bị hỗ trợ, in/Lưu PDF của trình duyệt.
9. Popup góp ý: lưu nháp và sao chép; **không tự gửi server**.
10. Popup phím tắt, popup các bài đã lưu, FAQ accordion, carousel, thanh tiến độ đọc và nút về đầu.
11. Có CSS in ấn, phím tắt, `aria-label`, focus ring, hỗ trợ `prefers-reduced-motion`.

## Quy ước

- Tất cả class HTML/CSS: `mayhen_docs_*`
- Tất cả ID do trang tạo: `mayhen_docs_*`
- Tất cả biến CSS tùy chỉnh: `--mayhen-docs-*`
- Chạy trong một IIFE, không tạo biến global JavaScript.

### Màu sắc chính

Sửa đầu file `styles.css` ở khối `.mayhen_docs_app`:

```css
--mayhen-docs-rose: #805052;
--mayhen-docs-rose-dark: #613b3e;
--mayhen-docs-champagne: #b58e6f;
--mayhen-docs-bg: #fffdfb;
--mayhen-docs-ink: #30292b;
```

### Thêm/sửa nội dung bài hướng dẫn

Ở đầu `script.js`, chỉnh object `GUIDES`. Mỗi bài có khóa ID và trường `category`, `title`, `summary`, `tags`, `steps`, `tip`. Sử dụng nút với `data-guide="ID"` để mở bài đó.

### Gợi ý tích hợp Laravel Blade

Để có một trang độc lập ngay lập tức: đặt `index.html`, `styles.css`, `script.js` cùng thư mục tại `public/docs/` và truy cập `/docs/index.html`.

Nếu tạo Blade thực sự:

1. Tách phần HTML trong `<body>` của `index.html` sang `resources/views/client/docs/index.blade.php`.
2. Đưa CSS và JS vào `public/assets/client/docs/`, thay `href="styles.css"` và `src="script.js"` bằng `{{ asset('assets/client/docs/styles.css') }}` và `{{ asset('assets/client/docs/script.js') }}`.
3. Khai báo route và xử lý dữ liệu server theo kiến trúc hiện có. Ví dụ: `Route::view('/huong-dan', 'client.docs.index');`.
4. Đưa nội dung `GUIDES` sang API hoặc dữ liệu server, thay logic tìm kiếm mẫu bằng tìm kiếm trên tập bài thật.
5. Kết nối đăng nhập, tạo thiệp, RSVP, lưu bài theo tài khoản, gửi góp ý, v.v. theo backend thật. Không coi dữ liệu localStorage là dữ liệu đáng tin cậy.

## Giới hạn quan trọng

- Đây là **mẫu giao diện có tương tác**, không phải bản sao source code GitBook và không kết nối dịch vụ GitBook.
- Các chức năng nhạy cảm hoặc liên quan server như đăng ký, đăng nhập, xuất bản thiệp, dữ liệu khách mời, gửi góp ý và AI **chưa được kết nối**. Những nút trong bản demo hướng tới phần hướng dẫn mẫu, tránh báo thành công giả.
- Giá trị trong `localStorage` chỉ dùng cho trải nghiệm minh họa trên trình duyệt, không đồng bộ giữa máy.
- Cần kiểm tra tích hợp thực tế trên các trình duyệt/thiết bị đích trước khi đưa lên production.
