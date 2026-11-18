# 05 — UX, Navigation & UI Review (Before vs After)

## 1. Triết lý cải tiến UX (Quiet Luxury & Operational Clarity)
Mục tiêu của đợt chuẩn hóa giao diện Client Operations là **giải tỏa quá tải thị giác (visual clutter)**, **sửa triệt để các modal bị kẹt ẩn**, **chuẩn hóa thuật ngữ số liệu khách mời** và **loại bỏ hiện tượng tràn ngang / chồng chéo thanh điều hướng** trên cả máy tính lẫn điện thoại (`320px`–`1440px`), đồng thời giữ nguyên toàn bộ kiến trúc Laravel Blade + Vanilla CSS + Native ES Modules.

---

## 2. Các vấn đề UX trọng yếu đã phát hiện & hoàn thiện

### 2.1. Trang Quản lý Khách mời (`/tai-khoan/thiep/{id}/khach-moi`)
- **Trước khi sửa (`docs/client-ops/qa/w00-before/06-guests-crm-populated-desktop.png`, `07-guests-crm-populated-mobile.png`)**:
  - `.mayhen_guest_header_actions` đặt 15 nút ngang hàng kèm `flex-shrink: 0`, ép cột tiêu đề bên trái bị gãy thành từng chữ dọc trên màn hình `1440px` và tràn khỏi khung phải; trên điện thoại `390px` tạo ra một bức tường 15 nút dài gần `1.500px` trước khi thấy số liệu.
  - 4 modal nghiệp vụ (`Soát lỗi trước khi gửi`, `Theo dõi gửi thiệp`, `Nhắc nhở tế nhị`, `Lời cảm ơn sau cưới`) gắn cứng `style="display: none;"` trong HTML khiến bấm nút không có bất kỳ phản hồi nào trên màn hình.
  - Thẻ tiến độ phản hồi có thể vọt lên `> 100%` khi khách điền qua link công khai, và nhãn số liệu trộn lẫn "tổng khách" với "số lời mời".
  - Dòng trạng thái rỗng dùng `<td colspan="9">` trong bảng 10 cột làm lệch đường viền bảng.
- **Sau khi sửa (`docs/client-ops/qa/w08-after/11-guests-crm-clean-after-desktop.png`, `13-guests-crm-clean-after-mobile.png`, `12-guests-presend-modal-open-desktop.png`)**:
  - Bố cục Header 2 tầng thoáng đãng: Hàng trên gồm tiêu đề + nút `Hướng dẫn` + `Xem Thiệp`; hàng công cụ chính giữ **6 tác vụ cốt lõi** (`Soát Lỗi Trước Khi Gửi`, `Theo Dõi Gửi Thiệp`, `Sơ Đồ Bàn Tiệc`, `Cổng Quét QR Tiếp Tân`, `Xuất CSV`, `In Danh Sách A4`); **8 công cụ chuyên sâu theo giai đoạn** xếp gọn trong ngăn mở rộng `<details id="mayhen_guest_more_tools">`.
  - Khôi phục hoạt động hiển thị mượt mà cho cả 4 modal (`.mayhen_guest_modal.is-open { display: flex !important; }`).
  - Lưới **6 thẻ KPI rõ đơn vị**: *Số Lời Mời* (nhóm/hộ) • *Suất Dự Kiến* • *Người Xác Nhận Đến* (`người lớn + trẻ em`) • *Báo Bận / Chờ Trả Lời* • *Đã Đánh Dấu Gửi / Đã Mở* • *Đã Điểm Danh Đến Tiệc*.
  - Ngôn ngữ gửi thiệp trung thực: làm rõ hệ thống hỗ trợ chép nội dung cá nhân hóa và mở nhanh ứng dụng Zalo/Messenger để chủ tiệc chủ động nhấn gửi, không gây hiểu lầm là hệ thống đã tự gửi ngầm.

### 2.2. Trang Tổng quan Tài khoản (`/tai-khoan`) & Danh sách Thiệp (`/tai-khoan/thiep`)
- **Trước khi sửa (`docs/client-ops/qa/w00-before/02-account-dashboard-desktop.png`, `04-account-invitations-desktop.png`)**:
  - Thanh `bottom-nav` tiếp thị nổi đè lên vùng nội dung quản lý tài khoản và cổng điểm danh.
  - Mỗi thẻ thiệp trong `/tai-khoan/thiep` hiển thị phẳng 41 nút công cụ làm thẻ kéo dài hơn `1.100px`.
  - Nút `"Xem 7 bước"` (`#mayhen_readiness_modal`) khóa cuộn trang (`overflow: hidden`) nhưng modal không hiện do xung đột `display: none !important` trong `account.css` và thiếu class `.mayhen_account_modal_dialog`.
  - Bấm sao chép link dùng `this.innerText = 'Đã chép!'` làm mất vĩnh viễn icon SVG trong nút; bấm `Tạm Dừng Xuất Bản` trên thiệp đang chạy thật không có hộp thoại xác nhận phòng bấm nhầm.
  - Khi đi vào các trang con của thiệp (`/so-luu-but`, `/mung-cuoi`, `/ke-hoach-cuoi`...), không có tab nào trên thanh điều hướng tài khoản được đánh dấu đang mở.
- **Sau khi sửa (`docs/client-ops/qa/w08-after/05-account-dashboard-after-desktop.png`, `07-account-invitations-compact-desktop.png`, `08-account-readiness-modal-open-desktop.png`)**:
  - Ẩn hoàn toàn `bottom-nav` tiếp thị khỏi các phân khu vận hành (`/tai-khoan*`, `/diem-danh*`, `/dieu-phoi-ban-tiec*`, `/khach-moi*`), giữ nguyên cho các trang khám phá công khai.
  - Bổ sung thanh chuyển đổi nhanh thiệp đang xem (`.mayhen_account_switcher_bar`) trên `/tai-khoan` khi tài khoản quản lý nhiều thiệp (Nhà Trai / Nhà Gái / Báo Hỷ).
  - Rút gọn thẻ thiệp trên `/tai-khoan/thiep` còn 2 nút chính (`Vào Studio`, `Xem Thiệp`) + 6 công cụ vận hành hàng ngày (`Khách Mời`, `Xếp Bàn`, `Điều Phối`, `Sổ Lưu Bút`, `Sổ Mừng`, `Cài Đặt`) + ngăn mở rộng gọn gàng cho 35 công cụ nâng cao.
  - Khôi phục hoàn toàn modal **Độ Sẵn Sàng 7 Bước**, giữ nguyên icon SVG khi sao chép link (có fallback cho HTTP/WebView), và hỏi xác nhận trước khi tạm dừng xuất bản thiệp đang công khai.

### 2.3. Sơ đồ Xếp bàn Tiệc (`/tai-khoan/thiep/{id}/so-do-ban-tiec`)
- **Trước khi sửa (`docs/client-ops/qa/w00-before/09-seating-planner-desktop.png`, `10-seating-planner-mobile.png`)**:
  - Tiêu đề `.seat_title` gọi biến CSS không tồn tại `var(--font-heading)` rơi về `Georgia`, làm vỡ dấu tiếng Việt (`Sơ Đô ̀ Xê ́ p`).
  - Thao tác kéo-thả HTML5 (`dragstart`/`drop`) khó dùng trên màn hình cảm ứng điện thoại; số ghế trên thẻ khách chưa phản ánh số người thực tế xác nhận đến.
- **Sau khi sửa (`docs/client-ops/qa/w08-after/15-seating-planner-after-desktop.png`, `16-seating-planner-after-mobile.png`)**:
  - Chuyển sang `var(--mayhen-font-serif)` hiển thị dấu tiếng Việt sắc nét, sang trọng.
  - Bổ sung ô chọn bàn 1-chạm (`.mayhen_seat_quick_assign_select`) ngay trên từng khách cho thiết bị di động song song với kéo-thả trên máy tính, đồng bộ đúng số người thực tế (`actualAttendeesCount`) và chống quá tải sức chứa bàn.

### 2.4. Cổng Điểm danh QR (`/diem-danh-tiec/{slug}`), Thẻ Concierge (`/khach-moi/{guestToken}`) & Kế hoạch cưới (`/ke-hoach-cuoi`)
- **Cổng Điểm danh QR**: Đồng bộ một mã PIN Lễ tân 6 số duy nhất trên mọi trang cấu hình & bản in dự phòng; cập nhật tức thời bộ đếm người đã đến sau khi hoàn tác (Undo); bổ sung thông báo rõ ràng khi trình duyệt không hỗ trợ `BarcodeDetector` để lễ tân chuyển mượt sang ô tìm kiếm nhanh theo tên/SĐT.
- **Thẻ Đón Tiếp Cá Nhân (`/khach-moi/{guestToken}`)**: Hiển thị đầy đủ lời chào cá nhân hóa theo nhóm khách và hướng dẫn gửi xe ô tô / xe máy / sảnh đón khách theo đúng khóa cấu hình chuẩn.
- **Kế hoạch & Ngân sách cưới (`/tai-khoan/thiep/{id}/ke-hoach-cuoi`)**: Sửa lỗi `@section('styles')` / `@section('scripts')` sang `@push('styles')` / `@push('scripts')`, khôi phục toàn bộ giao diện bảng kế hoạch và biểu đồ ngân sách (`19-wedding-planner-restored-desktop.png` so với `15-planner-broken-before-desktop.png`).
