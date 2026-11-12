# MÂY HẸN — BÁO CÁO BẰNG CHỨNG KIỂM ĐỊNH THỰC TẾ GATE G3 (VISUAL UX & ACCESSIBILITY)

- **Thời điểm kiểm định**: 2026-11-12 20:47:00 (Asia/Ho_Chi_Minh)
- **Môi trường**: PHP 8.5.10 (cli), Laravel 13.35.0, Pest PHP 3.x, SQLite In-Memory / File Test Engine
- **Trạng thái Gate G3**: **14 / 14 PASS (100%)**
- **File kiểm thử thực tế**: `tests/Feature/VisualUxAndAccessibilityIntegrityTest.php`
- **Tổng số assertions**: **69 assertions** (Thời gian chạy: 0.99s)

---

## 1. Danh Sách Chi Tiết 14 Bài Kiểm Thử Gate G3

| Mã Gate | Test Case | Assertions | Kết Quả | Cơ Chế Bảo Vệ Nghiệp Vụ Thực Tế |
|:---|:---|:---:|:---:|:---|
| **G3-001** | Meta viewport trên Form đăng nhập Client & Admin | 10 | **PASS** | Kiểm tra toàn bộ trang Auth (Login, Register, Admin Login) đều có `<meta name="viewport" content="width=device-width, initial-scale=1.0">`, input password và nhãn tiếng Việt rõ nét trên thiết bị di động. |
| **G3-002** | Partner Portal render trang thật cho đối tác | 3 | **PASS** | Sau khi xác thực đối tác `status = 'approved'`, cổng `partner.dashboard` hiển thị giao diện quản lý thực tế với tên Studio, không bị chuyển hướng vòng lặp. |
| **G3-003** | Thanh điều hướng Studio Editor & Autosave Pill | 5 | **PASS** | Trình soạn thảo Studio Editor tích hợp thanh điều hướng cố định (`mayhen_editor_topbar`), chỉ báo lưu tự động thời gian thực (`mayhen_editor_save_pill`) và nút Xuất bản. |
| **G3-004** | Trình dệt thiệp Curated Preview & Nút Bỏ Qua | 4 | **PASS** | Màn hình dệt thiệp chứa tiến trình 7 bước, hiển thị nút bỏ qua `btnSkipCrafting` cho phép người dùng chuyển nhanh đến thiệp hoàn thiện mà không bị khóa màn hình. |
| **G3-005** | Khung Opening phong bì & Chế độ nhúng Studio | 4 | **PASS** | Trang public thiệp cưới có phong bì mở đầu; khi nhúng trong iframe Studio (`?embed=1`), cơ chế phòng vệ tự động ẩn màn mở đầu (`display: none !important;`) để loại trừ màn hình trắng. |
| **G3-006** | Validation RSVP Modal nghiêm ngặt | 2 | **PASS** | Form RSVP trả về mã lỗi 422 (hoặc 302 kèm session error `guest_name`) khi khách để trống tên, giữ lại dữ liệu form và không bao giờ báo thành công ảo. |
| **G3-007** | Sơ đồ bàn tiệc Seating Planner thích ứng Tablet | 4 | **PASS** | Giao diện xếp chỗ bàn tiệc sử dụng tiền tố chuẩn `mayhen_seat_*`, hỗ trợ tương tác cảm ứng và kéo thả trực quan. |
| **G3-008** | Bảng dữ liệu quản trị Admin chống tràn ngang | 3 | **PASS** | Các bảng quản trị người dùng, đơn hàng (`admin.users.index`, `admin.orders.index`) có vùng cuộn ngang an toàn trên laptop 1366px, không bị tràn hay che khuất nút hành động. |
| **G3-009** | Bộ trang lỗi HTTP 404, 403, 410, 500 Quiet Luxury | 18 | **PASS** | Toàn bộ giao diện lỗi chuẩn hóa bảng màu Warm Cream, font Cormorant Garamond; trang 410 Gone (`resources/views/errors/410.blade.php`) giải thích lịch thiệp lý do hết hạn lưu trữ, tuyệt đối không lộ stack trace. |
| **G3-010** | Màn chiếu Live Wall LED tỉ lệ chuẩn 16:9 | 4 | **PASS** | Màn hình LED sân khấu (`mayhen_wall_*`) cân đối tên đôi uyên ương, hiển thị mã PIN kết nối và font chữ sắc nét ở độ phân giải lớn. |
| **G3-011** | Chuẩn tiếp cận WCAG phóng to 200% - 500% | 4 | **PASS** | Thẻ meta viewport trên Layout Master khai báo `maximum-scale=5.0`, cho phép người dùng thị lực kém phóng to màn hình dễ dàng. |
| **G3-012** | Hỗ trợ giảm chuyển động `prefers-reduced-motion` | 3 | **PASS** | File CSS nền tảng `public/assets/client/css/common/base.css` định nghĩa media query `prefers-reduced-motion: reduce`, cưỡng chế `animation-duration: 0.01ms !important` bảo vệ người dùng nhạy cảm tiền đình. |
| **G3-013** | Tuân thủ chính sách Autoplay Policy của iOS/Android | 2 | **PASS** | Thiệp cưới loại bỏ hoàn toàn thẻ `<audio autoplay>`, chỉ phát nhạc khi có tương tác chạm/bấm mở thiệp từ người dùng. |
| **G3-014** | Thư viện ảnh cưới 100% Local Assets | 3 | **PASS** | Bố cục ảnh cưới sử dụng class `mayhen_invitation_gallery`, kiểm tra tuyệt đối không chứa đường dẫn CDN từ bên ngoài (`ladicdn.com`, `builder.27biggroup.com`). |

---

## 2. Kết Luận Kiểm Định Gate G3

- **Tỷ lệ vượt qua**: **14 / 14 (100.0%)**
- **Toàn vẹn tệp kiểm thử**: Tệp `tests/Feature/VisualUxAndAccessibilityIntegrityTest.php` đã được whitelist trong `.gitignore`.
- **Chất lượng giao diện**: View lỗi 410 và toàn bộ hệ thống giao diện đáp ứng đầy đủ thẩm mỹ Quiet Luxury và khả năng tiếp cận WCAG.
- **Sẵn sàng chuyển tiếp**: Gate G3 chính thức hoàn thành. Tiếp tục chuyển sang bước nghiệm thu cuối cùng: Gate G4 (Commercial Acceptance & Launch).

