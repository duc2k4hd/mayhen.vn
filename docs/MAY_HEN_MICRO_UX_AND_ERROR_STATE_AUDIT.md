# MÂY HẸN (MAYHEN.VN) — KIỂM TOÁN TRẢI NGHIỆM VI MÔ & TRẠNG THÁI GIAO DIỆN
# (MICRO UX & ERROR STATE AUDIT)

> **Mục tiêu**: Rà soát từng nút bấm, form nhập liệu, phản hồi trạng thái (Loading / Empty / Error / Conflict), đảm bảo trải nghiệm người dùng đạt chuẩn Quiet Luxury: tinh tế, đáng tin cậy, không làm mất dữ liệu và hoàn hảo trên thiết bị di động (Mobile-First).

---

## 1. KIỂM TOÁN TOÀN DIỆN CÁC TRẠNG THÁI MÀN HÌNH (SCREEN STATES AUDIT)

| Trạng Thái Màn Hình | Hành Vi Tiêu Chuẩn Mây Hẹn | Đánh Giá Code Thực Tế | Hiện Trạng Nghiệp Vụ | Giải Pháp Hoàn Thiện |
|---|---|---|---|---|
| **Empty State** (Chưa có dữ liệu) | Khi chưa có khách mời, chưa có ảnh Photo Drop, chưa có bàn tiệc: hiển thị minh họa nghệ thuật trang nhã kèm nút kêu gọi hành động (CTA) hướng dẫn bước tiếp theo rõ ràng. | Các trang `guests.blade.php`, `photo-drop.blade.php`, `seating-planner.blade.php`, `invitations.blade.php` đều có khối empty state riêng biệt. |  **ĐẠT CHUẨN** | Bố cục ấm áp, văn phong thân thiện, không để màn hình trống trơn làm người dùng bối rối. |
| **Loading State** (Đang tải / Xử lý) | Khi người dùng nhấn nút "Lưu thiệp", "Xuất bản", "Tải ảnh" hoặc "Quét mã": nút bấm chuyển sang trạng thái disabled, hiển thị spinner thanh lịch, chống click đúp (Double Submission). | Đã tích hợp trên toàn bộ các form Ajax chính trong `studio-autosaver.js`, `checkin.blade.php`, `photo-drop.blade.php`. |  **ĐẠT CHUẨN** | Ngăn chặn hoàn toàn tình trạng gửi 2 request liên tiếp gây tạo trùng khách mời hoặc đơn hàng. |
| **Validation Error State** (Lỗi nhập liệu) | Khi người dùng nhập thiếu hoặc sai định dạng: trường nhập liệu viền đỏ nhẹ (`#C53030`), thông báo lỗi tiếng Việt dễ hiểu ngay dưới ô nhập, giữ nguyên dữ liệu đã gõ (`old()`), không bao giờ làm mất dữ liệu. | Áp dụng Form Requests chuẩn Laravel với thông báo tiếng Việt có dấu, Blade template dùng `@error` hiển thị trực tiếp dưới trường. |  **ĐẠT CHUẨN** | Người dùng biết chính xác cần sửa ô nào mà không phải gõ lại từ đầu. |
| **Conflict State (409)** (Xung đột Concurrency) | Khi hai người (cô dâu và chú rể) cùng mở Studio sửa thiệp cùng lúc: hệ thống không tự ý ghi đè mà cảnh báo xung đột phiên bản. | Được kiểm soát bởi `revision_id` trong `saveDraftWithLock` của `InvitationTemplateService` (trả về HTTP 409). |  **ĐẠT CHUẨN** | Bảo vệ tuyệt đối công sức chỉnh sửa của cả hai người. |
| **Offline / Timeout State** (Mất mạng / Sự cố) | Khi mạng chập chờn: Studio lưu nháp cục bộ qua `localStorage` và tự động thử lại khi có mạng trở lại; Cổng tiếp tân có cơ chế in danh sách giấy dự phòng (Resilience Kit). | `editor-autosaver.js` hỗ trợ lưu đệm trình duyệt; `seating-planner` và `guests` hỗ trợ nút in A4 chuẩn bị sẵn cho lễ tân. |  **ĐẠT CHUẨN** | Cặp đôi hoàn toàn an tâm vào ngày cưới ngay cả khi nhà hàng tiệc cưới bị mất Wi-Fi. |

---

## 2. KIỂM TOÁN TƯƠNG THÍCH THIẾT BỊ DI ĐỘNG (MOBILE-FIRST EXCELLENCE)

1. **Kích thước vùng chạm cảm ứng (Touch Target Sizing)**:
   - Toàn bộ nút bấm chính (CTA Buttons), ô nhập liệu, phím điều hướng đáy (Bottom Navigation) tuân thủ kích thước tối thiểu **44px × 44px** (theo chuẩn Apple Human Interface Guidelines và Google Material Design).
   - Khoảng cách giữa các nút bấm liền kề tối thiểu 8px, ngăn chặn hoàn toàn việc chạm nhầm trên màn hình cảm ứng di động.
2. **Khung nhìn kiểm thử đa dạng (Tested Viewports)**:
   - Màn hình nhỏ: **360px × 800px** (Samsung Galaxy phổ thông).
   - Màn hình tiêu chuẩn: **390px × 844px** (iPhone 13, 14, 15).
   - Màn hình lớn: **430px × 932px** (iPhone 15 Pro Max, 16 Pro Max).
   - Máy tính bảng: **768px × 1024px** (iPad Mini, iPad Air).
   - Màn hình máy tính: **1440px × 900px** và **1920px × 1080px** (MacBook, Desktop).
3. **Phòng vệ vùng an toàn (Safe Area Insets)**:
   - Các thanh điều hướng đáy (`mayhen_client_bottom_nav`) và thanh công cụ thao tác cố định đều có thuộc tính `padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px))` để không bị che khuất bởi thanh vuốt Home của iPhone / Android.

---

## 3. THẨM MỸ THƯƠNG HIỆU QUIET LUXURY & VĂN PHONG GIAO TIẾP

1. **Bảng màu nhận diện**:
   - Sử dụng các tông màu nền giấy mỹ thuật ấm áp: Warm Cream (`#FAF8F5`), Ivory White (`#FFFDF9`), phân cách bằng Soft Beige (`#EFE8DD`), chữ Charcoal (`#1F1D1A`) và điểm nhấn Muted Gold (`#C5A880`).
   - Tuyệt đối không lạm dụng hiệu ứng neon chói lóa, pop-up quảng cáo giật gân hay các banner countdown giảm giá giả tạo.
2. **Văn phong ứng xử với người dùng**:
   - Mọi thông báo thành công và thông báo lỗi đều sử dụng ngôn từ trang nhã, ấm cúng và tôn trọng:
     * Thay vì *"Lỗi 404 không tìm thấy"*, Mây Hẹn thông báo: *"Không tìm thấy thông tin đám cưới. Có thể đường link đã được cập nhật hoặc chuyển sang chế độ kỷ niệm."*
     * Thay vì *"Check-in thành công"*, Mây Hẹn hiển thị: *"Chào đón quý khách! Hân hạnh được đón tiếp bạn tại [Tên Bàn Tiệc]."*
     * Thay vì *"Khách đã bị xóa"*, Mây Hẹn thông báo: *"Đã gỡ khách mời khỏi danh sách đón tiếp thành công."*
3. **Tôn trọng văn hóa gia đình Việt Nam**:
   - Form thông tin hôn lễ phân tách rõ ràng vai vế: Phụ mẫu Nhà Trai (Ông, Bà), Phụ mẫu Nhà Gái (Ông, Bà), Hôn trường Nhà Trai (Lễ Tân Hôn), Hôn trường Nhà Gái (Lễ Vu Quy), Tiệc Báo Hỷ (Thành Hôn).
   - Không gộp chung sơ sài làm mất đi tính trang nghiêm của nghi lễ cưới truyền thống.

