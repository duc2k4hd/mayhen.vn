# MÂY HẸN (MAYHEN.VN) — LỘ TRÌNH NÂNG CẤP GIÁ TRỊ CAO CẤP CHUYÊN SÂU
# (PREMIUM VALUE UPGRADE ROADMAP)

> **Mục tiêu**: Chuyển hóa toàn bộ kết quả kiểm toán thành một lộ trình nâng cấp cụ thể, ưu tiên tối đa cho **Sự An Tâm Ngày Cưới (Wedding Confidence)**, **Trải Nghiệm Gia Đình (Family & Senior Respect)** và **Kỷ Niệm Bền Vững (Post-Wedding Keepsake)**.

---

## 1. NGUYÊN TẮC THI CÔNG BẮT BUỘC

1. **Chất lượng hơn số lượng**: Không làm thêm tính năng thừa thãi; chỉ làm những nâng cấp thực sự giải quyết nỗi lo của cô dâu chú rể và gia đình.
2. **Bảo tồn chuẩn mực kỹ thuật**: 100% Pure CSS/JS, tiền tố `mayhen_*`, zero framework/CDN bên ngoài.
3. **Bảo tồn mẫu thiệp cốt lõi**: Giữ nguyên vẹn 100% cấu trúc 4 mẫu thiệp cưới hiện có.
4. **Kiểm thử hồi quy 100%**: Mọi nâng cấp đều phải đi kèm Feature Test và Pint formatting.

---

## 2. CÁC CỘT MỐC TRIỂN KHAI CHI TIẾT (ACTIONABLE ROADMAP)

### CỘT MỐC 1: SỰ AN TÂM & CHU ĐÁO VỚI GIA ĐÌNH (WEDDING CONFIDENCE & FAMILY RESPECT)

#### `TASK-VAL-01`: Chế Độ Tiếp Cận Dành Riêng Cho Người Lớn Tuổi (Senior-Friendly Guest Mode)
- **Mục tiêu**: Thêm nút chuyển đổi font chữ lớn và giao diện tương phản cao trên thiệp cưới di động.
- **Phạm vi code**:
  * CSS: Bổ sung lớp `.mayhen_accessibility_mode` trong `public/assets/client/css/pages/invitation.css`.
  * JS: Thêm nút bấm icon [ Chữ Lớn / Dễ Đọc ] trong `public/assets/client/js/pages/invitation.js`.
  * Hành vi: Phóng to font chữ 125%, tăng độ tương phản màu sắc, dừng hiệu ứng hoa rơi, hiển thị nút gọi điện thoại nhanh cho gia đình.
- **Tiêu chí nghiệm thu**: Khách bấm nút thì chữ to rõ ngay tức thì, không bị nhảy layout, lưu trạng thái vào `sessionStorage`.

#### `TASK-VAL-02`: Bác Sĩ Đồng Bộ Thông Tin Trọng Yếu (Wedding Consistency Guardian)
- **Mục tiêu**: Tự động so sánh chéo ngày giờ, sảnh tiệc giữa thiệp cưới, Google Maps và bảng tiếp tân.
- **Phạm vi code**:
  * Mở rộng `app/Services/VisualDoctorService.php`.
  * Kiểm tra tính nhất quán giữa: `wedding_date`, địa chỉ sảnh tiệc `party_venue`, tọa độ Google Maps và lịch .ics.
  * Hiển thị cảnh báo trực quan trong Studio Editor nếu phát hiện thông tin mâu thuẫn.
- **Tiêu chí nghiệm thu**: Phát hiện chính xác nếu giờ tiệc trong văn bản lệch với giờ tiệc trong cấu hình lịch; có nút click dẫn thẳng đến ô cần sửa.

#### `TASK-VAL-03`: Bộ Dự Phòng Ngày Cưới 1 Chạm (Wedding Day Resilience Kit)
- **Mục tiêu**: Tạo trang tổng hợp in ấn chuẩn A4/A3 cho sảnh tiệc cưới (phòng trường hợp mất mạng Wi-Fi tại nhà hàng).
- **Phạm vi code**:
  * Route: `/tai-khoan/thiep/{id}/bo-du-phong-ngay-cuoi`.
  * View: `resources/views/client/pages/account/resilience-kit.blade.php`.
  * Chức năng: In danh sách khách mời có sẵn mã QR cá nhân hóa, In sơ đồ bàn tiệc kèm số ghế, In phiếu tiếp tân hướng dẫn cứu hộ.
- **Tiêu chí nghiệm thu**: File in trình duyệt hiển thị sắc nét khổ A4/A3 chuẩn @media print, không bị vỡ trang.

---

### CỘT MỐC 2: TỐI ƯU HÓA TRẢI NGHIỆM GIAO TIẾP & TỐC ĐỘ (SPEED & GUEST CONCIERGE)

#### `TASK-VAL-04`: Tự Động Nén Ảnh Thông Minh Phía Trình Duyệt (Client-side Image Optimizer)
- **Mục tiêu**: Tối ưu dung lượng ảnh tải lên (Photo Drop và Studio) từ 15MB xuống dưới 1MB trước khi gửi request.
- **Phạm vi code**:
  * File JS: `public/assets/client/js/common/image-optimizer.js`.
  * Sử dụng Native Canvas API của trình duyệt: Tự động resize ảnh vượt quá 2560px, chuyển đổi sang định dạng WebP chất lượng 85%.
  * Giảm tải 90% băng thông server và giúp khách mời gửi ảnh trong tiệc cưới cực nhanh dù 4G chập chờn.
- **Tiêu chí nghiệm thu**: Ảnh chụp iPhone 48MP được nén trong ~0.3 giây trên điện thoại, tải lên thành công 100%.

#### `TASK-VAL-05`: Hướng Dẫn Vị Trí & Bãi Đỗ Xe Thông Minh (Interactive Parking Concierge)
- **Mục tiêu**: Bổ sung mục hướng dẫn gửi xe và lối vào sảnh tiệc trong phần Sự kiện tiệc cưới.
- **Phạm vi code**:
  * Cấu hình trong `schema.json` và `custom_config.parking_guide`.
  * Giao diện Section 6 (Events & Maps): Cho phép hiển thị ảnh chụp lối vào bãi đỗ xe ô tô/xe máy, lưu ý về chi phí gửi xe hoặc biển chỉ dẫn thang máy.
- **Tiêu chí nghiệm thu**: Khách mời xem thiệp có thể mở tab "Bãi đỗ xe" để xem ảnh hướng dẫn chi tiết.

---

### CỘT MỐC 3: KỶ NIỆM BỀN VỮNG SAU NGÀY CƯỚI (POST-WEDDING KEEPSAKE)

#### `TASK-VAL-06`: Sách Kỷ Yếu Hôn Nhân PDF (Keepsake Wedding Book PDF)
- **Mục tiêu**: Cho phép cặp đôi tải về 1 cuốn sách kỷ yếu kỹ thuật số lưu giữ toàn bộ chữ ký, lời chúc và ảnh cưới.
- **Phạm vi code**:
  * Controller: `MemoryVaultController::downloadKeepsakeBook`.
  * View: `resources/views/client/pages/account/keepsake-book.blade.php`.
  * Bố cục: Khổ A4 ngang, bìa da mỹ thuật, trang câu chuyện tình yêu, các trang lời chúc kèm chữ ký viết tay của bạn bè.
- **Tiêu chí nghiệm thu**: Xuất file hoặc in trình duyệt chuẩn xác 100% không lệch trang, lưu giữ trọn đời.

#### `TASK-VAL-07`: Mở Niêm Phong Hộp Thời Gian Tự Động (Anniversary Email Reminder)
- **Mục tiêu**: Gửi email thông báo nhắc nhở cặp đôi vào đúng ngày mở khóa tâm thư sau 1, 3 hoặc 5 năm.
- **Phạm vi code**:
  * Artisan Command: `php artisan mayhen:check-time-capsules`.
  * Mail: `App\Mail\TimeCapsuleUnlockedNotification`.
  * Quét các bản ghi `wedding_time_capsules` đến hạn mở khóa hôm nay và gửi email kèm đường link trang trọng mở niêm phong sáp.
- **Tiêu chí nghiệm thu**: Đã viết test kiểm tra lệnh console gửi đúng email và đúng ngày hẹn mở.

